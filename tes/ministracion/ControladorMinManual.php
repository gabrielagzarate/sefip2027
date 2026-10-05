<?php
ini_set('memory_limit', '300M');
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/PropuestaManual.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/PropuestaManualTmp.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/Lote.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/PropuestaManualClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/PropuestaManualPlanCuenta.php';
require_once dirname(__FILE__) . '/../../lib/tes/pantallasModalesPropManual.php';

class ControladorMinManual extends WebResponse
{

    public $escritura;
    public function __construct()
    {

        parent::__construct();

        $this->escritura = isset($_SESSION[_S]['prm']['T']['4']['3']['2']);

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                switch ($this->metodo) {
                    case 'pintaCapturaManual':
                        $this->pintaCapturaManual();
                        break;
                    case 'pintaTablaCapturaManual':
                        $this->pintaTablaCapturaManual();
                        break;
                    case 'llenaTablaCapturaManual':
                        $this->llenaTablaCapturaManual();
                        break;
                    case 'agregaManual':
                        $this->agregaManual();
                        break;
                    case 'eliminaCapturaManual':
                        $this->eliminaCapturaManual();
                        break;
                    case 'pintaAutorizaManual':
                        $this->pintaAutorizaManual();
                        break;
                    case 'generaLoteManual':
                        $this->generaLoteManual();
                        break;
                    case 'validarCSV':
                        $this->validarCSV();
                        break;
                    case 'eliminaTodo':
                        $this->eliminaTodo();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }

        $this->returnJson();
    }

    public function main()
    {

        //Crea el boton de regreso
        $this->mixScript('
            $("#toolsPantalla").html("");
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $this->agregaTools('');

        $this->mixHtml('
            <div class="row class="col-md-2 col-xs-2 col-md-offset-4"">
                <legend class="mb-2">CAPTURA MANUAL</legend>

                    <fieldset class="col-md-2 col-xs-2">
                        <div titpp="Captura Manual" class="boton_opcion" id="capturaManual">
                            <div class="boton_opcion_titulo">Captura</div>
                            <strong><img height="60" width="60" src="img/Alta_Folio.png" alt=""><br></strong>
                            <small>Propuesta Manual</small>
                        </div>
                    </fieldset>
        ');

        if (isset($_SESSION[_S]['prm']['T']['4']['3']['3'])) {
            $this->mixHtml('
                    <fieldset class="col-md-2 col-xs-2">
                        <div titpp="Autorizar Captura Manual" class="boton_opcion" id="autorizaManual">
                            <div class="boton_opcion_titulo">Autorización</div>
                            <strong><img height="60" width="60" src="img/Validacion_Folio.png"><br></strong>
                            <small>Propuesta Manual</small>
                        </div>
                    </fieldset>
            ');
        }

        $this->mixHtml('

            </div>
        ');

        //Ejecuación de las funciones
        $this->mixScript('
            $("#toolsPantalla").html("");

            $("#capturaManual").click(function(){
                $("#SitemaActivo").hide();
                $("#contenedor").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaCapturaManual"
                });
            });

            $("#autorizaManual").click(function(){
                $("#SitemaActivo").hide();
                $("#contenedor").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaAutorizaManual"
                });
            });
        ');
    }

    private function pintaCapturaManual()
    {

        $editar = isset($this->prm->editar) ? $this->prm->editar : false;
        $lom_id = isset($this->prm->lom_id) ? $this->prm->lom_id : '';
        $accion = 'agregar';

        if ($editar) {
            $accion = 'editar';
            if (empty($lom_id)) {
                $this->mixScript('
                    notify("No se ha especificado la captura a editar");
                ');
                return;
            }
        }

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->class = 'btn btn-outline-secondary me-1';
        $btnRegresar->icon = 'solid fa-circle-left me-2';
        $btnRegresar->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            $("div[aria-describedby=cargaDatosCuentas]").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnAgregar = new stdClass();
        $btnAgregar->id = 'btnAgregar';
        $btnAgregar->name = 'btnAgregar';
        $btnAgregar->text = 'Agregar';
        $btnAgregar->value = '' . ucfirst($accion);
        $btnAgregar->type = 'button';
        $btnAgregar->class = 'btn btn-outline-success me-1';
        $btnAgregar->icon = 'solid fa-floppy-disk me-2';
        $btnAgregar->click = '
            if ( $("#inputBeneficiario").val() == "" ){
                notify("Por favor, ingrese un beneficiario", "warning");
            } else if ( $("#inputConcepto").val() == "" ){
                notify("Por favor, ingrese un concepto", "warning");
            } else if ( $("#comboCuentaCargo").val() == "" ){
                notify("Por favor, seleccione una cuenta de cargo", "warning");
            } else if ( $("#inputNombrecuentacargo").val() == "" ){
                notify("Por favor, seleccione una cuenta de cargo", "warning");
            } else if ( $("#comboCuentaCargo").val().length != 18 ){
                notify("La cuenta cargo debe tener 18 digitos", "warning");
            } else if ( $("#comboClaveFinanciamiento").val() == "" ){
                notify("Por favor, seleccione una clave de financiamiento", "warning");
            } else if ( $("#inputCuentaContableTesoreria").val() == "" ){
                notify("Por favor, seleccione la cuenta contable", "warning");
            } else if ( $("#inputCuentaContableCompleta").val() == "" ){
                notify("Por favor, seleccione la cuenta contable", "warning");
            } else if ( $("#inputCuentaDestino").val() == "" ){
                notify("Por favor, ingrese la cuenta destino", "warning");
            } else if ( $("#inputCuentaDestino").val().length != 18 ){
                notify("La cuenta destino debe tener 18 digitos", "warning");
            } else if ( $("#inputImporte").val() == "" ){
                notify("Por favor, ingrese el importe", "warning");
            } else if ( $("#inputLineaCaptura").val() == "" && $("#checkLineaCaptura").attr("checked") == true){
                notify("Por favor, ingrese la línea de captura", "warning");
            } else {
                alertify.confirm("' . ucfirst($accion) . ' registro", "¿Confirma que desea ' . $accion . ' el registro?",
                        function(){
                            $("div[aria-describedby=cargaDatosCuentas]").html("");
                                comunica("' . $this->pantalla . '",{
                                    metodo: "agregaManual",
                                    editar: "' . $editar . '",
                                    lom_id: "' . $lom_id . '",
                                    ur: $("#inputUR").val().trim(),
                                    clc: $("#inputCLC").val(),
                                    beneficiario: $("#inputBeneficiario").val(),
                                    concepto: $("#inputConcepto").val(),
                                    cuentaCargo: $("#comboCuentaCargo").val(),
                                    nombreCuentaCargo:$("#inputNombreCuentaCargo").val(),
                                    cuentaDestino: $("#inputCuentaDestino").val(),
                                    claveFinanciamiento: $("#comboClaveFinanciamiento").val(),
                                    cuentaContable: $("#inputCuentaContableTesoreria").val(),
                                    cuentaCompleta: $("#inputCuentaContableCompleta").val(),
                                    importe: $("#inputImporte").val(),
                                    lineaCaptura: $("#inputLineaCaptura").val()
                                });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            }
        ';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = "btnLimpiar";
        $btnLimpiar->name = "btnLimpiar";
        $btnLimpiar->class = "btn btn-outline-info me-1";
        $btnLimpiar->value = "Limpiar";
        $btnLimpiar->color = "amarillo";
        $btnLimpiar->icon = "trash me-2";
        $btnLimpiar->click = '
            /*$("div[aria-describedby=cargaDatosCuentas]").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaCapturaManual",
                editar: false
            });*/

            $("#inputUR").val("");
            $("#inputCLC").val("");
            $("#inputBeneficiario").val("");
            $("#inputConcepto").val("");
            $("#comboCuentaCargo").val("");
            $("#inputNombreCuentaCargo").val("");
            $("#inputCuentaDestino").val("");
            $("#comboClaveFinanciamiento").val("");
            $("#inputCuentaContableTesoreria").val("");
            $("#inputCuentaContableCompleta").val("");
            $("#inputImporte").val("");
            $("#inputLineaCaptura").val("");
            $(".selectpicker").selectpicker("refresh");
        ';

        $btnEliminaTodo = new stdClass();
        $btnEliminaTodo->id = "btnEliminaTodo";
        $btnEliminaTodo->name = "btnEliminaTodo";
        $btnEliminaTodo->class = "btn btn-outline-danger me-1";
        $btnEliminaTodo->value = "Eliminar todo";
        $btnEliminaTodo->icon = "solid fa-trash me-2";
        $btnEliminaTodo->click = '
            alertify.confirm("Eliminar registros", "¿Confirma que desea eliminar todos los registros?",
                        function(){
                            $("div[aria-describedby=cargaDatosCuentas]").html("");
                            comunica("' . $this->pantalla . '",{
                                metodo: "eliminaTodo"
                            });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        $btnInfo = new stdClass();
        $btnInfo->id = "btnInfo";
        $btnInfo->name = "btnInfo";
        $btnInfo->class = "btn btn-outline-warning me-1";
        $btnInfo->disabled = true;

        /**
         * FORMULARIO DE CAPTURA DE DATOS
         */

        $inputCLC = new StdClass();
        $inputCLC->id = 'inputCLC';
        $inputCLC->name = 'inputCLC';
        $inputCLC->placeholder = 'NÚMERO DE CLC';
        $inputCLC->enDiv = true;
        $inputCLC->label = 'CLC';
        $inputCLC->maxlength = 10;
        $inputCLC->formato = 'entero';

        $inputUR = new StdClass();
        $inputUR->id = 'inputUR';
        $inputUR->name = 'inputUR';
        $inputUR->placeholder = 'NÚMERO DE UR';
        $inputUR->enDiv = true;
        $inputUR->label = 'UR';
        $inputUR->maxlength = 3;
        $inputUR->formato = 'entero';

        $inputBeneficiario = new StdClass();
        $inputBeneficiario->id = 'inputBeneficiario';
        $inputBeneficiario->name = 'inputBeneficiario';
        $inputBeneficiario->placeholder = 'NOMBRE DEL BENEFICIARIO DE PROPUESTA';
        $inputBeneficiario->enDiv = true;
        $inputBeneficiario->label = 'BENEFICIARIO';

        $inputConcepto = new StdClass();
        $inputConcepto->id = 'inputConcepto';
        $inputConcepto->name = 'inputConcepto';
        $inputConcepto->placeholder = 'CONCEPTO DE PROPUESTA';
        $inputConcepto->enDiv = true;
        $inputConcepto->label = 'CONCEPTO';

        $comboCuentaCargo = new StdClass();
        $comboCuentaCargo->id = 'comboCuentaCargo';
        $comboCuentaCargo->name = 'comboCuentaCargo';
        $comboCuentaCargo->enDiv = true;
        $comboCuentaCargo->search = true;
        $comboCuentaCargo->sql = 'SELECT cbs_id, cbs_nombre FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin';
        $comboCuentaCargo->formato = 'entero';
        $comboCuentaCargo->maxlength = 18;
        $comboCuentaCargo->label = 'CUENTA CARGO';
        $comboCuentaCargo->change = '
            if ($(this).val() != "") {
                var cuenta = $("#comboCuentaCargo option:selected").text().split("-")[0];
                var nombre= $("#comboCuentaCargo option:selected").text().split("-")[1];
                $("#inputNombreCuentaCargo").val(nombre);
            }else{
                $("#inputNombreCuentaCargo").val("");
            }
        ';

        $inputNombreCuentaCargo = new StdClass();
        $inputNombreCuentaCargo->id = 'inputNombreCuentaCargo';
        $inputNombreCuentaCargo->name = 'inputNombreCuentaCargo';
        $inputNombreCuentaCargo->enDiv = true;
        $inputNombreCuentaCargo->label = 'NOMBRE CUENTA CARGO';
        $inputNombreCuentaCargo->readonly = true;

        $inputCuentaDestino = new StdClass();
        $inputCuentaDestino->id = 'inputCuentaDestino';
        $inputCuentaDestino->name = 'inputCuentaDestino';
        $inputCuentaDestino->placeholder = 'NÚMERO DE CUENTA DESTINO';
        $inputCuentaDestino->enDiv = true;
        $inputCuentaDestino->label = 'CUENTA DESTINO';
        $inputCuentaDestino->formato = 'entero';
        $inputCuentaDestino->maxlength = 18;

        $comboClaveFinanciamiento = new StdClass();
        $comboClaveFinanciamiento->id = 'comboClaveFinanciamiento';
        $comboClaveFinanciamiento->name = 'comboClaveFinanciamiento';
        $comboClaveFinanciamiento->placeholder = 'CAPTURA CLAVE FINANCIAMIENTO';
        $comboClaveFinanciamiento->enDiv = true;
        $comboClaveFinanciamiento->search = true;
        $comboClaveFinanciamiento->label = 'CLAVE FINANCIAMIENTO';
        $comboClaveFinanciamiento->sql = '
            SELECT DISTINCT ecf_id, CONCAT(cfi_clave, " - ", cfi_nombre) AS NOMBRE
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento e USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
        ';

        $inputCuentaContableTesoreria = new StdClass();
        $inputCuentaContableTesoreria->id = 'inputCuentaContableTesoreria';
        $inputCuentaContableTesoreria->name = 'inputCuentaContableTesoreria';
        $inputCuentaContableTesoreria->placeholder = 'F1: Catalogo';
        $inputCuentaContableTesoreria->enDiv = true;
        $inputCuentaContableTesoreria->label = 'CUENTA CONTABLE TESORERIA';
        $inputCuentaContableTesoreria->formato = 'entero';
        $inputCuentaContableTesoreria->class = 'F1Catalogo';
        $inputCuentaContableTesoreria->readonly = true;

        $inputCuentaContableCompleta = new stdClass();
        $inputCuentaContableCompleta->id = 'inputCuentaContableCompleta';
        $inputCuentaContableCompleta->name = 'inputCuentaContableCompleta';
        $inputCuentaContableCompleta->type = 'hidden';

        $inputImporte = new StdClass();
        $inputImporte->id = 'inputImporte';
        $inputImporte->name = 'inputImporte';
        $inputImporte->placeholder = 'CAPTURA EL IMPORTE';
        $inputImporte->enDiv = true;
        $inputImporte->label = 'IMPORTE';
        $inputImporte->formato = 'moneda';

        $checkLineaCaptura = new stdClass();
        $checkLineaCaptura->id = 'checkLineaCaptura';
        $checkLineaCaptura->name = 'checkLineaCaptura';
        $checkLineaCaptura->titulo = 'LINEA DE CAPTURA';
        $checkLineaCaptura->enDiv = true;
        $checkLineaCaptura->label = 'LINEA DE CAPTURA';

        $inputLineaCaptura = new stdClass();
        $inputLineaCaptura->id = 'inputLineaCaptura';
        $inputLineaCaptura->name = 'inputLineaCaptura';
        $inputLineaCaptura->placeholder = 'LINEA DE CAPTURA';
        $inputLineaCaptura->enDiv = true;
        $inputLineaCaptura->label = 'LINEA DE CAPTURA';
        $inputLineaCaptura->disabled = true;

        if ($editar) {
            $lom_id = isset($this->prm->lom_id) ? $this->prm->lom_id : '';

            if (empty($lom_id)) {
                $this->mixScript('
                    notify("No se ha especificado el registro a editar", "error");
                ');
                return;
            }
            $parametro = [
                'lom_id'=> $lom_id, 
            ];
            $sql = '
                SELECT
                    lom_id, gpo_id, ur_id,  CONCAT(gpo_id,LPAD(ur_id,2,"0")) AS ur, clc_id, lom_beneficiario, lom_concepto, lom_importe, cbs_id, cbs_nombre, cbb_id, ecf_id, cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id, cta_cuenta
                FROM ' . BD_TESORERITEM . '.tem_PropuestaManual AS tmp
                INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas AS cgpc USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin USING (cbs_id)
                WHERE lom_id = :lom_id';

            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql($sql);
            $con->consultar($parametro);

            if ($fila = $con->extraer()) {
                $ur_id = $fila['ur'];
                $clc_id = $fila['clc_id'];
                $lom_beneficiario = $fila['lom_beneficiario'];
                $lom_concepto = $fila['lom_concepto'];
                $lom_importe = $fila['lom_importe'];
                $cbs_id = $fila['cbs_id'];
                $cbs_nombre = $fila['cbs_nombre'];
                $cbb_id = $fila['cbb_id'];
                $ecf_id = $fila['ecf_id'];
                $cta_gpo_id = $fila['cta_gpo_id'];
                $cta_ur_id = $fila['cta_ur_id'];
                $cta_ue_id = $fila['cta_ue_id'];
                $ger_id = $fila['ger_id'];
                $cgp_id = $fila['cgp_id'];
                $rub_id = $fila['rub_id'];
                $cgcu_id = $fila['cgcu_id'];
                $cgsc_id = $fila['cgsc_id'];
                $cgc_id = $fila['cgc_id'];
                $cta_id = $fila['cta_id'];
                $cta_cuenta = $fila['cta_cuenta'];
            }

            $con->desconectar();

            $inputUR->value = $ur_id;
            $inputCLC->value = $clc_id;
            $inputBeneficiario->value = $lom_beneficiario;
            $inputConcepto->value = $lom_concepto;
            $inputImporte->value = $lom_importe;
            $inputCuentaDestino->value = $cbb_id;
            $inputCuentaContableTesoreria->value = $cta_cuenta;
            $inputCuentaContableCompleta->value = $cta_gpo_id . '-' . $cta_ur_id . '-' . $cta_ue_id . '-' . $ger_id . '-' . $cgp_id . '-' . $rub_id . '-' . $cgcu_id . '-' . $cgsc_id . '-' . $cgc_id . '-' . $cta_id;
        }

        $this->agregaTools($this->ponBoton($btnRegresar));

        if (isset($_SESSION[_S]['prm']['T']['4']['3']['2'])) {
            $this->agregaTools($this->ponBoton($btnAgregar));
        }

        $this->agregaTools($this->ponBoton($btnLimpiar));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnEliminaTodo));
        }

        /**
         * Calcular el total de la captura manual
         */
        $paramTotal = [
            'usr_id' => $_SESSION[_S]['usr_id'],
        ];
        $sqlTotal = '
            SELECT SUM(lom_importe) AS total_manual
            FROM ' . BD_TESORERITEM . '.tem_PropuestaManual
            WHERE usr_id = :usr_id';

        $conT = new AccesoDatos();
        $conT->conectar();
        $conT->setSql($sqlTotal);
        $conT->consultar($paramTotal);

        if ($fila = $conT->extraer()) {
            $totalConFormato = number_format($fila['total_manual'], 2, '.', ',');
            $btnInfo->value = 'Total de la captura manual:  $' . $totalConFormato;
            $this->agregaTools($this->ponBoton($btnInfo));
        }

        $conT->desconectar();
        //formulario para cargar el archivo
        $form = new stdClass();
        $form->id = "validar";
        $form->controlador = $this->pantalla;
        $form->metodo = "validarCSV";
        $formulario = new form($form);
        if ($this->escritura) {
            $html = '
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Archivo CSV</legend>
                        <input type="file" name="arch" id="arch">
                    </fieldset>
                </div>
                <br />
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <input id="cargarValidar" type="submit" value="Cargar y Validar" class="btn btn-outline-secondary ladda-button" data-style="slide-left">
                    </fieldset>
                </div>
                <br />
                <div id="resultadoValidacion"></div>';
            $formulario->mixHtml($html);
        }
        $this->mixhtml($formulario->ponForm());
        $this->mixScript('
            //$("#myModalInfo").modal();
            $("#cargarValidar").click( function(e) {
                // Como no muestra el indicador de avance, cambiamos el texto
                $("#cargarValidar").val("Cargando ...");
            });
            $("#arch").click( function(e) {
                try {
                    window.espera.stop();
                    $("#cargarValidar").val("Cargar y Validar");
                } catch (e) {
                    // nada
                }
            });
        ');

        $html = '
            <div class="row mb-2">
                <legend>Captura la siguiente información</legend>

                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputUR) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputCLC) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 ">
                    ' . $this->ponInput($inputBeneficiario) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputConcepto) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboCuentaCargo) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 ">
                    ' . $this->ponInput($inputNombreCuentaCargo) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboClaveFinanciamiento) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 ">
                    ' . $this->ponInput($inputCuentaContableTesoreria) . '
                    ' . $this->ponInput($inputCuentaContableCompleta) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputCuentaDestino) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputImporte) . '
                </fieldset>
            </div>
            <!--div class="row mb-2">
                <fieldset class="col-md-4 col-xs-4">
                    ' /*. $this->ponCheck2($checkLineaCaptura)*/ . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-4 col-xs-4">
                    ' /*. $this->ponInput($inputLineaCaptura)*/ . '
                </fieldset>
            </div-->
        ';

        if ($editar) {

            $cvefin = str_pad($ecf_id, 6, '0', STR_PAD_LEFT);

            $scr = '
                $("#comboCuentaCargo").val("' . $cbs_id . '");
                $("#comboClaveFinanciamiento").val("' . $cvefin . '");
                $(".selectpicker").selectpicker("refresh");
                var nombre= $("#comboCuentaCargo option:selected").text().split("-")[1];
                $("#inputNombreCuentaCargo").val(nombre);
            ';

            $this->mixScript($scr);
        }

        $this->mixHtml($html);
        $this->pintaTablaCapturaManual();
        $this->mixHtml(DibujaPantallaBuscarCuentaHTML());
        $this->mixScript(DibujaPantallaBuscarCuentaJS());
    }

    private function pintaTablaCapturaManual()
    {

        /**
         * TABLA TEMPORAL DE PROPUESTAS MANUALES AGREGADAS
         */
        $tabla = new stdClass();

        $tabla->id = 'tablaCapturaManual';
        $tabla->titulo = 'INFORMACIÓN DE CAPTURA MANUAL';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaCapturaManual';

        /* Array para dibujar la tabla /*/
        $tr = array();

        //Encabezados
        $th = new stdClass();
        $th->texto = 'UR';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA. CARGO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA. DESTINO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RECURSO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EDITAR';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->campoTxt = 'ur_numero';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lom_beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lom_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbb_id';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ter_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lom_importe';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'edit';
        $td->class = 'lomEditar';
        $td->attr = new stdClass();
        $td->attr->lom_id = 'lom_id';

        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if (isset($_SESSION[_S]['prm']['T']['4']['3']['2'])) {
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->class = 'lomEliminar btn btn-outline-danger me-1';
        $td->attr = new stdClass();
        $td->attr->lom_id = 'lom_id';

        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if (isset($_SESSION[_S]['prm']['T']['4']['3']['2'])) {
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div class="row">
                <fieldset class="col-md-12 col-xs-12" id="contenedorManual">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ');

        $this->llenaTablaCapturaManual();
    }

    private function llenaTablaCapturaManual()
    {

        $parametro =[
            'usr_id' => $_SESSION[_S]['usr_id']
        ];
        $sql = '
            SELECT
                tmp.lom_id,
                CONCAT(tmp.gpo_id, LPAD(tmp.ur_id,2,"0")) AS ur_numero,
                tmp.clc_id,
                tmp.lom_beneficiario,
                tmp.lom_concepto,
                tmp.cbs_id,
                cbs.cbs_nombre,
                tmp.cbb_id,
                tmp.lom_importe,
                ter.ter_nombre
            FROM
                ' . BD_TESORERITEM . '.tem_PropuestaManual AS tmp
            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING (cbs_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoRecurso AS ter USING (ter_id)
            WHERE
                tmp.usr_id = :usr_id';

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametro);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tablaCapturaManual";
        $this->wrLlenaTabla($nav);

        $this->mixScript('

             $("#cargarValidar").val("Cargar y Validar");

            /* En el campo cuenta contable, si escriben PUNTO ponemos grupo + ur + ue */
            $("#inputCuentaContableTesoreria").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                /* F1 llama a la pantalla para mostrar el catalogo de cuentas */
                if ( key == 112 ) {
                    e.preventDefault();
                    dlgC.dialog("open");
                }
            });

            $("#inputCuentaContableTesoreria").focus( function(e) {
                ctaCuenta = "inputCuentaContableTesoreria";
                ctaNombre = "nombreCuenta";
                ctaCompleta = "inputCuentaContableCompleta";
            });

            /*$("#div_checkLineaCaptura").click(function(){
                if ( $(this).attr("checked") ){
                    $("#cuentadestino").val("000000000000000000");
                    $("#cuentadestino").prop("disabled", true);
                    $("#inputLineaCaptura").prop("disabled", false);
                }else{
                    $("#cuentadestino").prop("disabled", false);
                    $("#cuentadestino").val("");
                    $("#inputLineaCaptura").prop("disabled", true);
                    $("#inputLineaCaptura").val("");
                }
            });*/

            $(".lomEditar").click(function(){
                $("div[aria-describedby=cargaDatosCuentas]").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaCapturaManual",
                    editar: true,
                    lom_id: $(this).attr("lom_id")
                });
            });

            $(".lomEliminar").click(function(){
                var lom = $(this).attr("lom_id");
                alertify.confirm("Eliminar registro", "¿Confirma que desea eliminar el registro?",
                        function(){
                            $("div[aria-describedby=cargaDatosCuentas]").html("");
                                comunica("' . $this->pantalla . '",{
                                    metodo: "eliminaCapturaManual",
                                    lom_id: lom,
                                    redirigeAutorizar: false
                                });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    private function agregaManual()
    {

        $editar = isset($this->prm->editar) ? $this->prm->editar : false;

        $lom = new stdClass();
        $lom->abc = 'a';

        if ($editar) {
            $lom->abc = 'c';
            $lom->lom_id = isset($this->prm->lom_id) ? $this->prm->lom_id : '';
        }

        $simbolos = array('$', ',');
        $lom->gpo_id = isset($this->prm->ur) ? substr($this->prm->ur, 0, 1) : '';
        $lom->ur_id = isset($this->prm->ur) ? substr($this->prm->ur, 1, 2) : '';
        $lom->clc_id = isset($this->prm->clc) ? $this->prm->clc : '';
        $lom->beneficiario = isset($this->prm->beneficiario) ? $this->prm->beneficiario : '';
        $lom->concepto = isset($this->prm->concepto) ? $this->prm->concepto : '';
        $lom->importe = isset($this->prm->importe) ? str_replace($simbolos, '', $this->prm->importe) : '';
        $lom->cbs_id = isset($this->prm->cuentaCargo) ? $this->prm->cuentaCargo : '';
        $lom->cbb_id = isset($this->prm->cuentaDestino) ? $this->prm->cuentaDestino : '';
        $lom->ecf_id = isset($this->prm->claveFinanciamiento) ? $this->prm->claveFinanciamiento : '';
        $cuentaCompleta = isset($this->prm->cuentaCompleta) ? $this->prm->cuentaCompleta : '';
        $cuentaContable = explode('-', $cuentaCompleta);
        $lom->cta_gpo_id = $cuentaContable[0];
        $lom->cta_ur_id = $cuentaContable[1];
        $lom->cta_ue_id = $cuentaContable[2];
        $lom->ger_id = $cuentaContable[3];
        $lom->cgp_id = $cuentaContable[4];
        $lom->rub_id = $cuentaContable[5];
        $lom->cgcu_id = $cuentaContable[6];
        $lom->cgsc_id = $cuentaContable[7];
        $lom->cgc_id = $cuentaContable[8];
        $lom->cta_id = $cuentaContable[9];

        if (empty($this->prm->ur)) {
            unset($lom->gpo_id);
            unset($lom->ur_id);
        }

        if (empty($this->prm->clc)) {
            unset($lom->clc_id);
        }

        if (empty($lom->beneficiario) || empty($lom->concepto) || empty($lom->importe) || empty($lom->cbs_id) || empty($lom->cbb_id) || empty($lom->ecf_id) || empty($lom->cta_gpo_id) || empty($lom->cta_ur_id) || empty($lom->cta_ue_id) || empty($lom->ger_id) || empty($lom->cgp_id) || empty($lom->rub_id) || empty($lom->cgcu_id) || empty($lom->cgsc_id) || empty($lom->cgc_id) || empty($lom->cta_id)) {
            $this->mixScript('
                notify("Faltan datos", "error");
            ');
            return;
        }

        PropuestaManualTmp::abc($lom);

        if ($editar) {
            unset($this->prm->editar);
            unset($this->prm->lom_id);
        }

        $this->pintaCapturaManual();
    }

    private function eliminaCapturaManual()
    {
        $lom_id = isset($this->prm->lom_id) ? $this->prm->lom_id : '';
        $redirigeAutorizar = isset($this->prm->redirigeAutorizar) ? $this->prm->redirigeAutorizar : false;

        $capturaManualTmp = new stdClass();
        $capturaManualTmp->abc = 'b';
        $capturaManualTmp->lom_id = $lom_id;

        PropuestaManualTmp::abc($capturaManualTmp);

        if ($redirigeAutorizar) {
            $this->pintaAutorizaManual();
        } else {
            $this->pintaCapturaManual();
        }
    }

    private function pintaAutorizaManual()
    {

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnAutorizarManual = new stdClass();
        $btnAutorizarManual->id = 'btnAutorizarManual';
        $btnAutorizarManual->name = 'btnAutorizarManual';
        $btnAutorizarManual->text = 'Autorizar';
        $btnAutorizarManual->value = 'Autorizar';
        $btnAutorizarManual->type = 'button';
        $btnAutorizarManual->class = 'btn btn-outline-success me-1';
        $btnAutorizarManual->icon = 'solid fa-check me-2';
        $btnAutorizarManual->click = '
            if ($("#inputFechaPago").val().trim()==""){
                notify("Por favor seleccione una fecha", "warning");
            }else if ($("#comboFormaPago").val().trim()==""){
                notify("Por favor seleccione una forma de pago", "warning");
            }else if ($("#comboCereCo").val().trim()==""){
                notify("Por favor seleccione un orden", "warning");
            }else if ($("#comboFlujoEfectivo").val().trim()==""){
                notify("Por favor seleccione si hay flujo de efectivo", "warning");
            }else{
                comunica("' . $this->pantalla . '",{
                    metodo: "generaLoteManual",
                    fechaPago: $("#inputFechaPago").val(),
                    formaPago: $("#comboFormaPago").val(),
                    cereCo: $("#comboCereCo").val(),
                    flujoEfectivo: $("#comboFlujoEfectivo").val()
                });
            }
        ';

        $btnInfo = new stdClass();
        $btnInfo->id = "btnInfo";
        $btnInfo->name = "btnInfo";
        $btnInfo->class = "btn btn-outline-success me-1";
        $btnInfo->disabled = true;

        //Dibuja el boton regresar
        $this->agregaTools($this->ponBoton($btnregresarindex));
        if (isset($_SESSION[_S]['prm']['T']['4']['3']['3'])) {
            $this->agregaTools($this->ponBoton($btnAutorizarManual));
        }

        /**
         * Calcular el total de la captura manual
         */
        $paramTotal = [
            'usr_id' => $_SESSION[_S]['usr_id'],
        ];
        $sqlTotal = '
            SELECT SUM(lom_importe) AS total_manual
            FROM ' . BD_TESORERITEM . '.tem_PropuestaManual
            WHERE usr_id = :usr_id';

        $conT = new AccesoDatos();
        $conT->conectar();
        $conT->setSql($sqlTotal);
        $conT->consultar($paramTotal);

        if ($fila = $conT->extraer()) {
            $totalConFormato = number_format($fila['total_manual'], 2, '.', ',');
            $btnInfo->value = 'Total de la captura manual:  $' . $totalConFormato;
            $this->agregaTools($this->ponBoton($btnInfo));
        }

        $conT->desconectar();

        /* Declaramos los elementos a utilizar/*/
        $inputFechaPago = new StdClass();
        $inputFechaPago->id = 'inputFechaPago';
        $inputFechaPago->name = 'inputFechaPago';
        $inputFechaPago->enDiv = true;
        $inputFechaPago->formato = 'fecha';
        $inputFechaPago->label = 'FECHA DE PAGO';
        $inputFechaPago->value = date("d/m/Y");

        $comboFormaPago = new StdClass();
        $comboFormaPago->sql = 'SELECT fpa_id, fpa_nombre FROM ' . BD_TESORERIA . '.cat_FormaPago';
        $comboFormaPago->id = 'comboFormaPago';
        $comboFormaPago->name = 'comboFormaPago';
        $comboFormaPago->enDiv = true;
        $comboFormaPago->search = true;
        $comboFormaPago->mensaje = 'SELECCIONE';
        $comboFormaPago->label = 'FORMA DE PAGO';

        $comboCereCo = new StdClass();
        $opcCereCo = array();
        $opcCereCo[1] = 'CERE';
        $opcCereCo[2] = 'CERECO';
        $comboCereCo->id = 'comboCereCo';
        $comboCereCo->name = 'comboCereCo';
        $comboCereCo->enDiv = true;
        $comboCereCo->search = true;
        $comboCereCo->mensaje = 'SELECCIONE';
        $comboCereCo->label = 'ORDEN';
        $comboCereCo->opciones = $opcCereCo;

        $comboFlujoEfectivo = new StdClass();
        $opcFlujo = array();
        $opcFlujo[1] = 'CON FLUJO DE EFECTIVO';
        $opcFlujo[2] = 'SIN FLUJO DE EFECTIVO';
        $comboFlujoEfectivo->id = 'comboFlujoEfectivo';
        $comboFlujoEfectivo->name = 'comboFlujoEfectivo';
        $comboFlujoEfectivo->enDiv = true;
        $comboFlujoEfectivo->search = true;
        $comboFlujoEfectivo->mensaje = 'SELECCIONE';
        $comboFlujoEfectivo->label = 'FLUJO DE EFECTIVO';
        $comboFlujoEfectivo->opciones = $opcFlujo;

        //Crea el boton de regreso
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $html = '
          <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                ' . $this->ponInput($inputFechaPago) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                ' . $this->ponCombo($comboFormaPago) . '
                </fieldset>
          </div>
          <div class="row mb-2">
                <fieldset class="col-xs-3 col-md-3">
                ' . $this->ponCombo($comboCereCo) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                ' . $this->ponCombo($comboFlujoEfectivo) . '
                </fieldset>
          </div>
        ';

        $this->mixHtml($html);

        $this->pintaTablaCapturaManual();
    }

    private function generaLoteManual()
    {
        $this->prm->fechaPago = isset($this->prm->fechaPago) ? $this->prm->fechaPago : '';
        $this->prm->formaPago = isset($this->prm->formaPago) ? $this->prm->formaPago : '';
        $this->prm->cereCo = isset($this->prm->cereCo) ? $this->prm->cereCo : '';
        $this->prm->flujoEfectivo = isset($this->prm->flujoEfectivo) ? $this->prm->flujoEfectivo : '';

        $fecha = DateTime::createFromFormat('d/m/Y', $this->prm->fechaPago);
        $fechaActual = '';

        $this->setSql('SELECT CURDATE() as fecha');
        $this->conectar();
        $this->consultar();
        if ($row = $this->extraer()) {
            $fechaActual = $row['fecha'];
        }

        $tipoRecurso = '';

        $parametro = [
            'usr_id' => $_SESSION[_S]['usr_id']
        ];
        $sql = '
            SELECT
                ter.ter_nombre
            FROM
                ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs
            INNER JOIN ' . BD_TESORERITEM . '.tem_PropuestaManual AS tmp USING (cbs_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoRecurso AS ter USING (ter_id)
            WHERE
                tmp.lom_id = (
                    SELECT
                        MAX(lom_id) AS lom_id
                    FROM
                        ' . BD_TESORERITEM . '.tem_PropuestaManual AS sb
                    WHERE
                        sb.usr_id = :usr_id
                );
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametro);

        if ($fila = $this->extraer()) {
            $tipoRecurso = trim($fila['ter_nombre']);
        }

        /**
         * Inserta en lot_Lote
         */
        $lote = new stdClass();
        $lote->abc = 'a';
        $lote->fpa_id = $this->prm->formaPago;
        $lote->msc_id = $fecha->format('m');
        $lote->ejercicio = $fecha->format('Y');
        $lote->fechaGeneracion = $fechaActual;
        $lote->fechaPago = $fecha->format('Y-m-d');
        $lote->tipoCereCo = $this->prm->cereCo;
        $lote->flujoEfectivo = $this->prm->flujoEfectivo;
        $lote->elo_id = 3;
        $lote->importe = '0';
        $lote->lot_ejr = APP_EJERCICIO;

        if ($tipoRecurso == 'FEDERAL') {
            $lote->tlo_id = 7;
        } else {
            $lote->tlo_id = 6;
        }

        $sql = '
            SELECT
                lom_id,
                gpo_id,
                ur_id,
                clc_id,
                lom_beneficiario,
                lom_concepto,
                lom_importe,
                cbs_id,
                cbb_id,
                ecf_id,
                cta_gpo_id,
                cta_ur_id,
                cta_ue_id,
                ger_id,
                cgp_id,
                rub_id,
                cgcu_id,
                cgsc_id,
                cgc_id,
                cta_id
            FROM
                ' . BD_TESORERITEM . '.tem_PropuestaManual AS tmp
            WHERE
                tmp.usr_id = :usr_id';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametro);

        $numReg = $this->getNumeroRegistros();

        if ($numReg > 0) {
            Lote::abc($lote);
        }

        $sql = '
            SELECT MAX(lot_id) AS lot_id
            FROM ' . BD_TESORERIA . '.lot_Lote
            WHERE fpa_id = ' . $lote->fpa_id . '
                AND msc_id = ' . $lote->msc_id . '
                AND lot_ejercicio = ' . $lote->ejercicio . '
                AND usr_id = :usr_id';

        $nuevaCon = new AccesoDatos();
        $nuevaCon->conectar();
        $nuevaCon->setSql($sql);
        $nuevaCon->consultar($parametro);

        if ($fila = $nuevaCon->extraer()) {
            $lote->lot_id = $fila['lot_id'];
        }

        /**
         * Insertamos en lot_PropuestaManual y eliminanos de tem_PropuestaManual
         */
        while ($fila = $this->extraer()) {
            $loteManual = new stdClass();
            $loteManual->abc = 'a';
            $loteManual->fpa_id = $lote->fpa_id;
            $loteManual->msc_id = $lote->msc_id;
            $loteManual->lot_id = $lote->lot_id;
            $loteManual->ejercicio = $lote->ejercicio;
            $loteManual->elo_id = $lote->elo_id;
            $loteManual->gpo_id = $fila['gpo_id'];
            $loteManual->ur_id = $fila['ur_id'];
            $loteManual->clc_id = $fila['clc_id'];
            $loteManual->beneficiario = $fila['lom_beneficiario'];
            $loteManual->concepto = $fila['lom_concepto'];
            $loteManual->importe = $fila['lom_importe'];
            $loteManual->cbs_id = $fila['cbs_id'];
            $loteManual->cbb_id = $fila['cbb_id'];

            $lom_id = PropuestaManual::abc($loteManual);

            $vinManualFinanciamiento = new stdClass();
            $vinManualFinanciamiento->abc = 'a';
            $vinManualFinanciamiento->lom_id = $lom_id;
            $vinManualFinanciamiento->ecf_id = $fila['ecf_id'];

            PropuestaManualClaveFinanciamiento::abc($vinManualFinanciamiento);

            $vinManualPlanCuenta = new stdClass();
            $vinManualPlanCuenta->abc = 'a';
            $vinManualPlanCuenta->lom_id = $lom_id;
            $vinManualPlanCuenta->cta_gpo_id = $fila['cta_gpo_id'];
            $vinManualPlanCuenta->cta_ur_id = $fila['cta_ur_id'];
            $vinManualPlanCuenta->cta_ue_id = $fila['cta_ue_id'];
            $vinManualPlanCuenta->ger_id = $fila['ger_id'];
            $vinManualPlanCuenta->cgp_id = $fila['cgp_id'];
            $vinManualPlanCuenta->rub_id = $fila['rub_id'];
            $vinManualPlanCuenta->cgcu_id = $fila['cgcu_id'];
            $vinManualPlanCuenta->cgsc_id = $fila['cgsc_id'];
            $vinManualPlanCuenta->cgc_id = $fila['cgc_id'];
            $vinManualPlanCuenta->cta_id = $fila['cta_id'];

            PropuestaManualPlanCuenta::abc($vinManualPlanCuenta);

            $loteManualTmp = new stdClass();
            $loteManualTmp->abc = 'b';
            $loteManualTmp->lom_id = $fila['lom_id'];

            PropuestaManualTmp::abc($loteManualTmp);

        }

        /*
         * Actualizamos el importe del lote
         */
        $paramImporte = [
            'fpa_id' => $lote->fpa_id,
            'msc_id' => $lote->msc_id,
            'lot_ejercicio' => $lote->ejercicio,
            'lot_id' => $lote->lot_id,
        ];
        $sql = '
            SELECT
                SUM(m.lom_importe) AS lot_importe
            FROM ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS m USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            WHERE
                l.fpa_id = :fpa_id
                AND l.msc_id = :msc_id
                AND l.lot_ejercicio = :lot_ejercicio
                AND l.lot_id = :lot_id';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($paramImporte);

        $lot = new stdClass();
        $lot->abc = 'c';
        $lot->fpa_id = $lote->fpa_id;
        $lot->msc_id = $lote->msc_id;
        $lot->ejercicio = $lote->ejercicio;
        $lot->lot_id = $lote->lot_id;
        $lot->importe = '0';
        $lot->lot_ejr = APP_EJERCICIO;

        if ($fila = $this->extraer()) {
            $lot->importe =  $fila['lot_importe'] ;
        }

        Lote::abc($lot);

        $this->mixScript('
            notify("Propuesta manual generada exitosamente", "success");
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ');

    }
    private function validarCSV()
    {
        //cargamos los datos del formulario

        if (trim($_FILES['arch']['name']) == "") {
            $this->mixScript('
                notify( "Especifica el archivo a cargar", "warning" );
                                $("#cargarValidar").val("Cargar y Validar");
                window.espera.stop();
            ');
            return false;
        }
        $extension = explode(".", $_FILES['arch']['name']);
        $var_puntos = count($extension);

        if ((trim($extension[$var_puntos - 1]) != "csv")) {
            $this->mixScript('
                notify( "El archivo debe ser en formato CSV ", "warning" );
                                $("#cargarValidar").val("Cargar y Validar");
                window.espera.stop();
            ');
            return false;
        }

        if ($_FILES['arch']['error'] == 1 || $_FILES['arch']['size'] == 0) {
            $this->mixScript('
                notify("El tamaño del archivo excede límite máximo permitido", "error");
                                $("#cargarValidar").val("Cargar y Validar");
                window.espera.stop();
            ');
            return false;
        }

        //cargamos los datos del archivo
        $arch = $_FILES['arch']['tmp_name'];
        $csv = file_get_contents($arch);
        //convertimos los datos csv en arreglo
        $lines = explode(PHP_EOL, $csv);
        $array = array();
        $titulos = 0;

        foreach ($lines as $line) {
            if ($titulos == 0) {
                $titulos = 1;
            } else {
                $array[] = str_getcsv($line);
            }
        }
        //recorremos el arreglo
        foreach ($array as $key => $value) {
            if (isset($value[1])) {
                $ArrBeneficiario = $value[0];
                $ArrCuentaDestino = $value[1];
                $ArrImporte = $value[2];
                $ArrCuentaCargo = $value[3];
                $ArrConcepto = $value[4];
                $ArrCuentaConta = $value[5];
                $ArrClaveFin = $value[6];
                if ($ArrBeneficiario == '' || $ArrCuentaDestino == '' || $ArrClaveFin == '' ||
                    $ArrImporte == '' || $ArrConcepto == '' || $ArrCuentaCargo == '' || $ArrCuentaConta == '') {

                    $this->mixScript('
                                        notify("No pueden ir campos vacios en el formato", "error");
                                        $("#cargarValidar").val("Cargar y Validar");
                                        window.espera.stop();
                                        ');
                    return false;
                }

                //validacion de la cuenta cargo
                $paramCuentaC = [
                    'cbs_id' => $ArrCuentaCargo,
                ];
                $sql = "SELECT cbs_alias,cbs_id FROM " . BD_TESORERIA . ".ban_CuentaBancariaSefin where cbs_id= :cbs_id";
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($paramCuentaC);
                $row = $conexion->extraer();
                if ($conexion->getNumeroRegistros() == 1) {
                    $cbsAlias = $row['cbs_alias'];

                } else {
                    $this->mixScript(" notify('No se encontro la cuenta $ArrCuentaCargo'); $('#cargarValidar').val('Cargar y Validar');");

                    return;
                }
                //validacion de la clave de financiamiento
                $paramClaveFin = [
                    'ecf_clave' => $ArrClaveFin,
                ];
                $sql = "SELECT ecf_clave,ecf_id FROM " . BD_CTRL . ".enc_ClaveFinanciamiento where ecf_clave= :ecf_clave";
                $conexion->setSql($sql);
                $row = $conexion->consultar($paramClaveFin);
                $row = $conexion->extraer();
                if ($conexion->getNumeroRegistros() == 1) {
                    $ecf_id = $row['ecf_id'];
                } else {
                    $this->mixScript(" notify('No se encontro la clave $ArrClaveFin');
                $('#cargarValidar').val('Cargar y Validar');
                                window.espera.stop(); ");
                    return;
                }
                //validacion de la clave de financiamiento
                $sql = "SELECT cvf.ecf_clave,cvf.ecf_id
                FROM " . BD_SINPRES . ".enc_ClaveFinanciamiento cvf
                INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento enc USING(ecf_id)
                WHERE enc.ecf_clave= :ecf_clave";
                $conexion->setSql($sql);
                $row = $conexion->consultar($paramClaveFin);
                $row = $conexion->extraer();
                if ($conexion->getNumeroRegistros() == 1) {
                    $ecf_id = $row['ecf_id'];
                } else {
                    $this->mixScript(" notify('No se encontro la CLAVE $ArrClaveFin');
                                    $('#cargarValidar').val('Cargar y Validar');
                                window.espera.stop();");
                    return;
                }
                //valicacion de la cuenta contable No se encontro la clave AAA
                $cta_tmp = explode('.', $ArrCuentaConta);
                $ger_id = substr($cta_tmp[0], 0, 1);
                $cgp_id = substr($cta_tmp[0], 1, 1);
                $rub_id = substr($cta_tmp[0], 2, 1);
                $cgcu_id = substr($cta_tmp[0], 3, 1);
                $cgsc_id = substr($cta_tmp[0], 4, 1);
                $cta_gpo_id = substr($cta_tmp[1], 0, 1);
                $cta_ur_id = substr($cta_tmp[1], 1, 2);
                $cta_ue_id = $cta_tmp[2];
                $cgc_id = $cta_tmp[3];
                $cta_id = $cta_tmp[4];
                $paramPlanC = [
                    'gpo_id' => $cta_gpo_id,
                    'ur_id' => $cta_ur_id,
                    'ue_id' => $cta_ue_id,
                    'ger_id' => $ger_id,
                    'cgp_id' => $cgp_id,
                    'rub_id' => $rub_id,
                    'cgcu_id' => $cgcu_id,
                    'cgsc_id' => $cgsc_id,
                    'cgc_id' => $cgc_id,
                    'cta_id' => $cta_id,
                ];
                $sql = "SELECT cta_cuenta,cta_nombre FROM " . BD_CTRL . ".cg_PlanCuentas
                    WHERE cta_gpo_id = :gpo_id AND
                        cta_ur_id = :ur_id AND
                        cta_ue_id = :ue_id AND
                        ger_id = :ger_id AND
                        cgp_id = :cgp_id AND
                        rub_id = :rub_id AND
                        cgcu_id = :cgcu_id AND
                        cgsc_id = :cgsc_id AND
                        cgc_id= :cgc_id AND
                        cta_id = :cta_id";
                $conexion->setSql($sql);

                $row = $conexion->consultar($paramPlanC);
                $row = $conexion->extraer();
                if ($conexion->getNumeroRegistros() == 1) {
                    $cta_nombre = $row['cta_nombre'];
                } else {
                    $this->mixScript(" notify('No se encontro la Cuenta Contable $ArrCuentaConta');"
                        . "$('#cargarValidar').val('Cargar y Validar'); ");
                    return;
                }
                //insertamos en la temporal
                $this->prm->abc = 'a';
                //aplica el sistema utf8_decode y modifica los acentos se antepone un encode para correcion
                $this->prm->beneficiario = utf8Encode($ArrBeneficiario);
                $this->prm->concepto = utf8Encode($ArrConcepto);
                $this->prm->importe = $ArrImporte;
                $this->prm->cbs_id = $ArrCuentaCargo;
                $this->prm->cbb_id = $ArrCuentaDestino;
                $this->prm->ecf_id = $ecf_id;
                $this->prm->cta_gpo_id = $cta_gpo_id;
                $this->prm->cta_ur_id = $cta_ur_id;
                $this->prm->cta_ue_id = $cta_ue_id;
                $this->prm->ger_id = $ger_id;
                $this->prm->cgp_id = $cgp_id;
                $this->prm->rub_id = $rub_id;
                $this->prm->cgcu_id = $cgcu_id;
                $this->prm->cgsc_id = $cgsc_id;
                $this->prm->cgc_id = $cgc_id;
                $this->prm->cta_id = $cta_id;
                PropuestaManualTmp::abc($this->prm);
                $this->llenaTablaCapturaManual();
            }
        }
    }

    private function eliminaTodo()
    {
        $parametro = [
            'usr_id' => $_SESSION[_S]['usr_id'],
        ];
        $sql = '
            DELETE FROM ' . BD_TESORERITEM . '.tem_PropuestaManual
            WHERE usr_id = :usr_id';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($parametro);
        $con->desconectar();

        $this->pintaCapturaManual();
    }

}

if (isset($_SESSION[_S]['prm']['T']['4']['3']['1'])) {
    new ControladorMinManual();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
