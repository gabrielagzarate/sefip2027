<?php
/**
 * Description of ControladorClaveFinanciamiento
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 02-Sep-2019
 * Comment: -----
 * Objective:
 * Regla de Negocio:
Solo puede cambiar el nombre de la Sub-Fuente, Origen, Sub-Origen y Clave financiamiento
si se cumple las reglas;
1.-Si una clave de financiamiento ya esta presupuestalmente afectada (clc), ya no se puede cambiar el nombre.
2.-O si fue afectada con un folio de adecuaciones presupuestales.
Si un usuario tiene los permisos de TESORERIA y ESCRITURA, y desea cambiar la clave de financiamiento a PDP pero
esta ya se encuentra vinculada a una CLC o Folio de adecuaciones presupuestales, debe permitir actualizar
solo este campo, !nadamas¡, los demas campos no deben actualizarse.
 * Permits:
Si tiene permisos de LECTURA no puede agregar ni editar.
Si tiene permisos de LECTURA y ESCRITURA, puede agregar y editar, pero no puede ver los campos poa, división ni pdp.
Si tiene permisos de LECTURA y PRESUPUESTO, no puede agregar, ni eliminar CLAVE FINANCIAMIENTO, pero si puede editar y solo visualizar el campo poa y división, para poder editarlos.
Si tiene permisos de LECTURA, ESCRITURA y TESORERIA, puede agregar, editar y visualizar y editar el campo PDP.

 * */
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/Fuente.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/Subfuente.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/OrigenRecurso.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/SuborigenRecurso.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/SinpresClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/SinpresEncuadreClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/CtrlEncuadreClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../lib/tes/catalogo/funcionesClaveFinanciamiento.php';

define('CIRCLE', 'fa-solid fa-circle-left me-2'); //Constante para el botón Regresar dado que se repite muchas veces

class ControladorClaveFinanciamiento extends WebResponse
{
    protected $lectura;
    protected $escritura;
    protected $validar;
    protected $presupuesto;
    protected $tesoreria;
    protected $ip;
    protected $bit_usr_id;
    protected $prm2;
    protected $prm3;
    protected $cfEstaVinculadoAClc;
    protected $cfEstaVinculadoAfolio;

    public function __construct()
    {
        $this->lectura = (tienePermiso("T.6.3.1") || tienePermiso("S.6.4.1") || tienePermiso("C.2.11.1"));
        $this->escritura = (tienePermiso("T.6.3.2") || tienePermiso("S.6.4.2") || tienePermiso("C.2.11.2"));
        $this->presupuesto = (tienePermiso("T.6.3.99") || tienePermiso("S.6.4.99") || tienePermiso("C.2.11.99"));
        $this->tesoreria = (tienePermiso("T.6.3.100") || tienePermiso("S.6.4.100") || tienePermiso("C.2.11.100"));

        parent::__construct();

        $this->pantalla = "tes/catalogo/ControladorClaveFinanciamiento.php";

        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ?
        $_SESSION[_S]['ses_ip'] :
        '127.0.0.1';
        $this->bit_usr_id = $_SESSION[_S]['usr_id'];

        switch ($this->metodo) {
            case 'altaFuente':
                $this->altaFuente();
                break;
            case 'nuevaFuente':
                $this->nuevaFuente();
                break;
            case 'llenaTablaFuente':
                $this->llenaTablaFuente();
                break;
            case 'editaFuente':
                $this->editaFuente();
                break;
            case 'actualizarFuente':
                $this->actualizarFuente();
                break;
            case 'validarFuente':
                $this->validarFuente();
                break;
            case 'guardarFuente':
                $this->guardarFuente();
                break;
            case 'altaSubfuente':
                $this->altaSubfuente();
                break;
            case 'nuevaSubfuente':
                $this->nuevaSubfuente();
                break;
            case 'llenaTablaSubfuente':
                $this->llenaTablaSubfuente();
                break;
            case 'editaSubFuente':
                $this->editaSubFuente();
                break;
            case 'actualizarSubfuente':
                $this->actualizarSubfuente();
                break;
            case 'validarSubfuente':
                $this->validarSubfuente();
                break;
            case 'altaOrigen':
                $this->altaOrigen();
                break;
            case 'nuevoOrigen':
                $this->nuevoOrigen();
                break;
            case 'llenaTablaOrigen':
                $this->llenaTablaOrigen();
                break;
            case 'editaOrigen':
                $this->editaOrigen();
                break;
            case 'actualizarOrigen':
                $this->actualizarOrigen();
                break;
            case 'validarOrigen':
                $this->validarOrigen();
                break;
            case 'altaSubOrigen':
                $this->altaSubOrigen();
                break;
            case 'nuevoSubOrigen':
                $this->nuevoSubOrigen();
                break;
            case 'llenaTablaSubOrigen':
                $this->llenaTablaSubOrigen();
                break;
            case 'editaSubOrigen':
                $this->editaSubOrigen();
                break;
            case 'actualizarSubOrigen':
                $this->actualizarSubOrigen();
                break;
            case 'validarSubOrigen':
                $this->validarSubOrigen();
                break;
            case 'guardarOrigen':
                $this->guardarOrigen();
                break;
            case 'guardarSubfuente':
                $this->guardarSubfuente();
                break;
            case 'guardarSubOrigen':
                $this->guardarSubOrigen();
                break;
            case 'altaClaveF':
                $this->altaClaveF();
                break;
            case 'nuevaClaveF':
                $this->nuevaClaveF();
                break;
            case 'llenaTablaClaveF':
                $this->llenaTablaClaveF();
                break;
            case 'editaClaveF':
                $this->editaClaveF();
                break;
            case 'actualizarClaveF':
                $this->actualizarClaveF();
                break;
            case 'guardarClaveF':
                $this->guardarClaveF();
                break;
            case 'showEliminarClaveF':
                $this->showEliminarClaveF();
                break;
            case 'eliminarClaveF':
                $this->eliminarClaveF();
                break;
            default:
                $this->menuAltaCF();
                break;
        }
        $this->returnJson();
    }
    public function menuAltaCF()
    {
        $cards = [];

        $c = new stdClass();
        $c->id = 'fuente';
        $c->titulo = 'Fuente';
        $c->icon = 'fa-solid fa-hand-holding-dollar';
        $c->iconColor = '#806f3d';
        $c->label = 'Fuente';
        $c->title = 'Fuente de financiamiento';
        $c->attrs = ['titpp' => 'fuente'];
        $cards[] = $c;

        $c = new stdClass();
        $c->id = 'sfuente';
        $c->titulo = 'Subfuente';
        $c->icon = 'fa-solid fa-layer-group';
        $c->iconColor = '#806f3d';
        $c->label = 'Subfuente';
        $c->title = 'Subfuente de financiamiento';
        $c->attrs = ['titpp' => 'subfuente'];
        $cards[] = $c;

        $c = new stdClass();
        $c->id = 'origen';
        $c->titulo = 'Origen';
        $c->icon = 'fa-solid fa-compass';
        $c->iconColor = '#806f3d';
        $c->label = 'Origen';
        $c->title = 'Origen del recurso';
        $c->attrs = ['titpp' => 'origen'];
        $cards[] = $c;

        $c = new stdClass();
        $c->id = 'so';
        $c->titulo = 'Suborigen';
        $c->icon = 'fa-solid fa-sitemap';
        $c->iconColor = '#806f3d';
        $c->label = 'Suborigen';
        $c->title = 'Suborigen del recurso';
        $c->attrs = ['titpp' => 'suborigen'];
        $cards[] = $c;

        $c = new stdClass();
        $c->id = 'altaCF';
        $c->titulo = 'Clave F.';
        $c->icon = 'fa-solid fa-key';
        $c->iconColor = '#806f3d';
        $c->label = 'Clave financiamiento';
        $c->title = 'Clave de financiamiento';
        $c->attrs = ['titpp' => 'altaCF'];
        $cards[] = $c;

        $this->mixHtml($this->ponCards($cards));

        $this->mixScript('
            $("#toolsPantalla").html("");
            $("#btnregresarindex").removeClass("btn-default");
            $("#fuente").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"altaFuente"
                });
            });
            $("#sfuente").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"altaSubfuente"
                });
            });

            $("#origen").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"altaOrigen"
                });
            });

            $("#so").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"altaSubOrigen"
                });
            });

            $("#altaCF").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"altaClaveF"
                });
            });
        ');
    }
    private function altaClaveF()
    {
        $this->prm->suborigenSeleccionado = isset($this->prm->suborigenSeleccionado) ? $this->prm->suborigenSeleccionado : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "menuAltaCF"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnNuevaaltaClaveF = new stdClass();
        $btnNuevaaltaClaveF->id = 'btnNuevaaltaCF';
        $btnNuevaaltaClaveF->name = 'btnNuevaaltaCF';
        $btnNuevaaltaClaveF->text = 'Nuevo';
        $btnNuevaaltaClaveF->value = 'Nuevo';
        $btnNuevaaltaClaveF->type = 'button';
        $btnNuevaaltaClaveF->class = 'me-1';
        $btnNuevaaltaClaveF->color = 'azul';
        $btnNuevaaltaClaveF->icon = 'solid fa-plus me-2';
        $btnNuevaaltaClaveF->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "nuevaClaveF",
                suborigen: $("#suboriR option:selected").attr("claveFD")
            });
        ';
        $btnPDF = new stdClass();
        $btnPDF->id = 'btnPrintPdf';
        $btnPDF->name = 'btnPrintPdf';
        $btnPDF->text = 'PDF';
        $btnPDF->value = 'PDF';
        $btnPDF->type = 'button';
        $btnPDF->class = 'me-1';
        $btnPDF->color = 'rojo';
        $btnPDF->icon = 'solid fa-file-pdf me-2';
        $btnPDF->click = '
            var form = $("<form>")
            .attr("action", "rep/doc/tes/catalogo/rep_ClaveFinanciamiento.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 4))
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#suboriR option:selected").attr("claveFD")))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $btnExcel = new stdClass();
        $btnExcel->id = 'btnPrintExcel';
        $btnExcel->name = 'btnPrintExcel';
        $btnExcel->text = 'EXCEL';
        $btnExcel->value = 'Excel';
        $btnExcel->type = 'button';
        $btnExcel->class = 'me-1';
        $btnExcel->color = 'verde';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->click = '
            var form = $("<form>")
            .attr("action", "rep/excel/tes/catalogo/exp_ClaveFinanciamientoClaveF.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#suboriR option:selected").attr("claveFD")))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $juntarBotones = '';
        if ($this->lectura && $this->escritura) {
            $juntarBotones = '' . $this->ponBoton($btnNuevaaltaClaveF) . '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        } elseif ($this->lectura) {
            $juntarBotones = '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        }
        $this->agregaTools($juntarBotones);

        $inputSubOrigenR = new StdClass();
        $inputSubOrigenR->id = 'suboriR';
        $inputSubOrigenR->name = 'suboriR';
        $inputSubOrigenR->enDiv = true;
        $inputSubOrigenR->label = 'Sub-Origen del recurso disponibles';
        $params = [APP_EJERCICIO];
        $inputSubOrigenR->sql = "SELECT CONCAT(cff_id, fte_id, sft_id, ori_id, sor_id) claveFD, sor_nombre FROM "
            . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " WHERE sor_ejercicio = ?;";
        $inputSubOrigenR->params = $params;
        $inputSubOrigenR->camposVal = 'claveFD';
        $inputSubOrigenR->camposText = 'sor_nombre';
        $inputSubOrigenR->comoAtr = 'claveFD';
        $inputSubOrigenR->search = true;
        if (!empty($this->prm->suborigenSeleccionado)) {
            $inputSubOrigenR->selecciona = $this->prm->suborigenSeleccionado;
        }

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tablaClaveF';
        $tabla->titulo = 'Lista de Clave Financiamiento';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClaveF';

        $tr = array();

        if (($this->lectura && $this->escritura) || ($this->lectura && $this->presupuesto)) {
            $th = new stdClass();
            $th->texto = 'EDITAR';
            array_push($tr, $th);
        }
        if ($this->lectura && $this->escritura) {
            $th = new stdClass();
            $th->texto = 'ELIMINAR';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'cff_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'fte_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'sft_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ori_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'sor_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'cfi_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'cfi_anio';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de clave de Financiamiento';
        array_push($tr, $th);

        $trbody = array();
        if (($this->lectura && $this->escritura) || ($this->lectura && $this->presupuesto)) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-pencil';
            $td->class = 'Editar';
            $td->name = 'editar';
            $td->id = 'editar';
            $td->attr = new stdClass();
            $td->attr->llave = 'llave';
            $td->attr->cfi_nombre = 'cfi_nombre';
            $td->attr->cfi_descripcion = 'cfi_descripcion';
            $td->attr->cfi_abreviatura = 'cfi_abreviatura';
            array_push($trbody, $td);
        }
        if ($this->lectura && $this->escritura) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-trash-can';
            $td->class = 'btn btn-outline-danger Eliminar';
            $td->name = 'eliminar';
            $td->id = 'eliminar';
            $td->attr = new stdClass();
            $td->attr->llave = 'llave';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'cff_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fte_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sft_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ori_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sor_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_anio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $inputBuscarClave = new StdClass();
        $inputBuscarClave->id = 'inputBuscarClave';
        $inputBuscarClave->name = 'inputBuscarClave';
        $inputBuscarClave->placeholder = 'Clave Financiamiento';
        $inputBuscarClave->enDiv = true;
        $inputBuscarClave->label = 'Clave Financiamiento';
        $inputBuscarClave->maxlength = 4;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'buscar';
        $btnBuscar->name = 'buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"llenaTablaClaveF",
                suborigen:$("#suboriR option:selected").attr("claveFD"),
                clave:$("#inputBuscarClave").val()
            });
        ';

        $this->mixHTML('
            <div class="row mb-4">
                <fieldset class="col-md-7 col-xs-7" id="">
                    ' . $this->ponCombo($inputSubOrigenR) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 " id="">
                    ' . $this->ponInput($inputBuscarClave) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1 mt-3" style="display: flex; justify-content: right;" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                ' . $this->wrPonTabla($tabla) . '
            </div>
            <div id ="dialogEliminarClaveF"></div>
        ');

        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            if($("#suboriR option:selected").attr("claveFD")!=""){
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaTablaClaveF",
                    suborigen:$("#suboriR option:selected").attr("claveFD"),
                    clave:$("#inputBuscarClave").val()
                });
            }
            var permitirCaracteresAlfanumericos = /^[\d]/;
            $("#btnregresarindex").removeClass("btn-default");
            $("#inputBuscarClave").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
        ');

        $this->llenaTablaClaveF();
    }
    private function nuevaClaveF()
    {
        $this->prm->suborigen = isset($this->prm->suborigen) ? $this->prm->suborigen : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "altaClaveF",
                suborigenSeleccionado: "' . $this->prm->suborigen . '"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnaltaClaveF = new stdClass();
        $btnaltaClaveF->id = 'btnaltaCF';
        $btnaltaClaveF->name = 'btnaltaCF';
        $btnaltaClaveF->text = 'Guardar';
        $btnaltaClaveF->value = 'Guardar';
        $btnaltaClaveF->type = 'button';
        $btnaltaClaveF->class = 'me-1';
        $btnaltaClaveF->color = 'verde';
        $btnaltaClaveF->icon = 'solid fa-floppy-disk me-2';
        $btnaltaClaveF->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "guardarClaveF",
                CFD: $("#suboriR option:selected").attr("claveFD"),
                nombre: $("#cfnombre").val(),
                ejercicio: $("#cfiejercicio").val(),
                descripcion: $("#cfdescripcion").val(),
                abreviatura: $("#cfabreviatura").val(),
                referencia: $("#referencia").val(),
                ECF_POA: ($("#ECF_POA").val() == undefined)? "" : $("#ECF_POA").val(),
                ECF_DIVISION: ($("#ECF_DIVISION").val() == undefined) ? "" : $("#ECF_DIVISION").val(),
                PDP_ID: ($("#PDP_ID").val() == undefined) ? "" : $("#PDP_ID").val()
            });
        ';
        $this->agregaTools('' . $this->ponBoton($btnaltaClaveF));

        $inputSubOrigenR = new StdClass();
        $inputSubOrigenR->id = 'suboriR';
        $inputSubOrigenR->name = 'suboriR';
        $inputSubOrigenR->enDiv = true;
        $inputSubOrigenR->label = 'Sub-Origen del recurso disponibles';
        $params = [APP_EJERCICIO];
        $inputSubOrigenR->sql = " SELECT CONCAT(cff_id, fte_id, sft_id, ori_id, sor_id) AS suborigen,CONCAT(cff_id, fte_id,
                sft_id, ori_id, sor_id) claveFD, sor_nombre
            FROM " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO .
            " WHERE sor_ejercicio = ? ";
        if (!empty($this->prm->suborigen)) {
            array_push($params, '%' . $this->prm->suborigen . '%');
            $inputSubOrigenR->sql .= "HAVING suborigen LIKE ?";
        }
        $inputSubOrigenR->params = $params;
        $inputSubOrigenR->camposVal = 'claveFD';
        $inputSubOrigenR->camposText = 'sor_nombre';
        $inputSubOrigenR->comoAtr = 'claveFD';
        $inputSubOrigenR->search = true;

        $inputCFNombre = new StdClass();
        $inputCFNombre->id = 'cfnombre';
        $inputCFNombre->name = 'cfnombre';
        $inputCFNombre->placeholder = 'Nombre de la Clave de Financiamiento';
        $inputCFNombre->enDiv = true;
        $inputCFNombre->label = 'Nombre de la Clave de Financiamiento';
        $inputCFNombre->maxlength = 250;
        $inputCFNombre->rows = 2;
        $inputCFNombre->cols = 12;

        $inputReferencia = new StdClass();
        $inputReferencia->id = 'referencia';
        $inputReferencia->name = 'referencia';
        $inputReferencia->placeholder = '01';
        $inputReferencia->enDiv = true;
        $inputReferencia->label = 'Referencia';
        $inputReferencia->maxlength = 2;
        $inputReferencia->formato = 'entero';

        $inputEjercicio = new StdClass();
        $inputEjercicio->id = 'cfiejercicio';
        $inputEjercicio->name = 'cfiejercicio';
        $inputEjercicio->placeholder = APP_EJERCICIO;
        $inputEjercicio->enDiv = true;
        $inputEjercicio->label = 'Ejercicio';
        $inputEjercicio->maxlength = 4;
        $inputEjercicio->formato = 'entero';

        $inputCFDescripcion = new StdClass();
        $inputCFDescripcion->id = 'cfdescripcion';
        $inputCFDescripcion->name = 'cfdescripcion';
        $inputCFDescripcion->placeholder = '--';
        $inputCFDescripcion->enDiv = true;
        $inputCFDescripcion->label = 'Descripción de la Clave de Financiamiento';
        $inputCFDescripcion->maxlength = 250;
        $inputCFDescripcion->rows = 2;
        $inputCFDescripcion->cols = 12;

        $inputCFAbreviatura = new StdClass();
        $inputCFAbreviatura->id = 'cfabreviatura';
        $inputCFAbreviatura->name = 'cfabreviatura';
        $inputCFAbreviatura->placeholder = '--';
        $inputCFAbreviatura->enDiv = true;
        $inputCFAbreviatura->label = 'Abreviatura de la Clave de Financiamiento';
        $inputCFAbreviatura->maxlength = 25;
        $inputCFAbreviatura->rows = 2;
        $inputCFAbreviatura->cols = 12;

        $this->mixHTML('
            <div class="row">
                <fieldset class="col-md-4 col-xs-4 " id="">
                    ' . $this->ponCombo($inputSubOrigenR) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2 " id="">
                    ' . $this->ponInput($inputReferencia) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 " id="">
                    ' . $this->ponInput($inputEjercicio) . '
                </fieldset>
            </div>
            <div class="mb-4">
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12 " id="">
                    ' . $this->ponTextarea($inputCFNombre) . '
                </fieldset>
            </div>
            <div class="mb-4">
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12 " id="" >
                    ' . $this->ponTextarea($inputCFAbreviatura) . '
                </fieldset>
            </div>
            <div class="mb-4">
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12 " id="">
                    ' . $this->ponTextarea($inputCFDescripcion) . '
                </fieldset>
            </div>
            <hr>
            ' . (($this->presupuesto) ? '
            <div class="row">
                <fieldset class="col-md-3 col-xs-3 " id="">
                    <label class="container">POA
                        <input type="checkbox" name="ECF_POA" id="ECF_POA" value="2">
                        <span class="checkmark"></span>
                    </label>
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 " id="">
                    <label class="container">DIVISIÓN
                        <input type="checkbox" name="ECF_DIVISION" id="ECF_DIVISION" value="2">
                        <span class="checkmark"></span>
                    </label>
                </fieldset>
            </div>
            ' : '') . '
            ' . (($this->tesoreria) ? '
            <div class="row">
                <fieldset class="col-md-3 col-xs-3 " id="">
                    <label class="container">PDP
                        <input type="checkbox" name="PDP_ID" id="PDP_ID" value="2">
                        <span class="checkmark"></span>
                    </label>
                </fieldset>
            </div>
            ' : '') . '
        ');

        // @PCAN=Permitir caracteres alfa numericos
        $this->mixScript('
            $("#PDP_ID").on("click",function(e){
                if($(this).is(":checked")){
                    // Hacer algo si el checkbox ha sido seleccionado
                    $(this).attr("value", 1);
                }else{
                    $(this).attr("value", 2);
                }
            });
            $("#ECF_POA").on("click",function(e){
                if($(this).is(":checked")){
                    // Hacer algo si el checkbox ha sido seleccionado
                    $(this).attr("value", 1);
                }else{
                    $(this).attr("value", 2);
                }
            });

            $("#ECF_DIVISION").on("click",function(e){
                if($(this).is(":checked")){
                    // Hacer algo si el checkbox ha sido seleccionado
                    $(this).attr("value", 1);
                }else{
                    $(this).attr("value", 2);
                }
            });

            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#btnregresarindex").removeClass("btn-default");
            $("#cfnombre").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#cfnombre").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });

            $("#cfdescripcion").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 45) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#cfdescripcion").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });

            $("#cfabreviatura").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 45) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#cfabreviatura").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function llenaTablaClaveF()
    {
        $this->prm->suborigen = isset($this->prm->suborigen) ? $this->prm->suborigen : "";
        $this->prm->clave = isset($this->prm->clave) ? $this->prm->clave : "";
        $params = [APP_EJERCICIO];
        $sqlExtra = "";
        if (!empty($this->prm->suborigen)) {
            if (!empty($this->prm->clave)) {
                array_push($params, '%' . $this->prm->suborigen . $this->prm->clave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } else {
                array_push($params, '%' . $this->prm->suborigen . '%');
                $sqlExtra = "HAVING suborigen LIKE ? ";
            }
        } elseif (!empty($this->prm->clave)) {
            array_push($params, '%' . $this->prm->clave . '%');
            $sqlExtra = "HAVING clave LIKE ? ";
        }

        $sql = "SELECT CONCAT(cff_id, fte_id, sft_id, ori_id, sor_id) AS suborigen,
                CONCAT(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio) AS llave,
                cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio, cfi_ejercicio2, cfi_ejercicio, cfi_nombre,
                cfi_descripcion, cfi_abreviatura,
                CONCAT(cfi_id, cfi_anio) AS clave
                FROM " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . "
                WHERE cfi_ejercicio2 = ?
                $sqlExtra
                ORDER BY llave ASC";
        $this->setSql($sql . "; ");
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaClaveF";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".Editar").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"editaClaveF",
                    llave: $(this).attr("llave"),
                    nombreClaveF: $(this).attr("cfi_nombre"),
                    cfi_descripcion: $(this).attr("cfi_descripcion"),
                    cfi_abreviatura: $(this).attr("cfi_abreviatura"),
                    suborigenSeleccionado: $("#suboriR option:selected").attr("claveFD"),
                    Edit: "editar"
                });
            });
            $(".Eliminar").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"showEliminarClaveF",
                    llave: $(this).attr("llave"),
                    suborigenSeleccionado: $("#suboriR").val()
                });
            });
        ');
    }
    private function editaClaveF()
    {
        $this->prm->llave = isset($this->prm->llave) ? $this->prm->llave : "";
        $this->prm->nombreClaveF = isset($this->prm->nombreClaveF) ? $this->prm->nombreClaveF : "";
        $this->prm->cfi_descripcion = isset($this->prm->cfi_descripcion) ? $this->prm->cfi_descripcion : "";
        $this->prm->cfi_abreviatura = isset($this->prm->cfi_abreviatura) ? $this->prm->cfi_abreviatura : "";
        $this->prm->Edit = (isset($this->prm->Edit)) ? $this->prm->Edit : "";
        $this->prm->suborigenSeleccionado = (isset($this->prm->suborigenSeleccionado)) ? $this->prm->suborigenSeleccionado : "";

        if ((!$this->escritura) && !$this->presupuesto) {
            $this->mixScript('notify("No tiene permitido editar las cuentas", "error");');
        }

        $btnRegresarBusqueda = new stdClass();
        $btnRegresarBusqueda->id = 'btnregresarbusqueda';
        $btnRegresarBusqueda->name = 'btnregresarbusqueda';
        $btnRegresarBusqueda->text = 'Regresar';
        $btnRegresarBusqueda->value = 'Regresar';
        $btnRegresarBusqueda->type = 'button';
        $btnRegresarBusqueda->color = 'gris';
        $btnRegresarBusqueda->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnRegresarBusqueda->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo:"altaClaveF",
                suborigenSeleccionado: "' . $this->prm->suborigenSeleccionado . '"
            });
        ';

        $btnEditar = new stdClass();
        $btnEditar->id = 'editar';
        $btnEditar->name = 'editar';
        $btnEditar->text = 'Guardar';
        $btnEditar->value = 'Guardar';
        $btnEditar->type = 'button';
        $btnEditar->class = 'me-1';
        $btnEditar->color = 'verde';
        $btnEditar->icon = 'solid fa-floppy-disk me-2';
        $btnEditar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"actualizarClaveF",
                cfi_id:$("#clabeCF").val(),
                nombre:$("#NombreClaveF").val(),
                descripcion:$("#CFdescripcion").val(),
                abreviatura:$("#CFabreviatura").val(),
                ECF_POA: ($("#ECF_POA").val() == undefined)?"":$("#ECF_POA").val(),
                ECF_DIVISION: ($("#ECF_DIVISION").val() == undefined)?"":$("#ECF_DIVISION").val(),
                PDP_ID: ($("#PDP_ID").val()==undefined)?"":$("#PDP_ID").val(),
                suborigenSeleccionado: "' . $this->prm->suborigenSeleccionado . '"
            });
        ';

        $vEditar = $this->prm->Edit;
        if ($vEditar == "editar") {
            $this->agregaTools($this->ponBoton($btnRegresarBusqueda));
            if ($this->escritura || $this->presupuesto) {
                $this->agregaTools($this->ponBoton($btnEditar));
            }
        }

        $inputClavesFinanciamiento = new StdClass();
        $inputClavesFinanciamiento->id = 'clabeCF';
        $inputClavesFinanciamiento->name = 'clabeCF';
        $inputClavesFinanciamiento->value = $this->prm->llave;
        $inputClavesFinanciamiento->enDiv = true;
        $inputClavesFinanciamiento->label = 'Clave de Financiamiento';
        $inputClavesFinanciamiento->disabled = true;

        $inputNombreClaveF = new StdClass();
        $inputNombreClaveF->id = 'NombreClaveF';
        $inputNombreClaveF->name = 'NombreClaveF';
        $inputNombreClaveF->text = $this->prm->nombreClaveF;
        $inputNombreClaveF->enDiv = true;
        $inputNombreClaveF->maxlength = 250;
        $inputNombreClaveF->label = 'Nombre de la clave de financiamiento';

        $inputCFDescripcion = new StdClass();
        $inputCFDescripcion->id = 'CFdescripcion';
        $inputCFDescripcion->name = 'CFdescripcion';
        $inputCFDescripcion->text = $this->prm->cfi_descripcion;
        $inputCFDescripcion->enDiv = true;
        $inputCFDescripcion->maxlength = 250;
        $inputCFDescripcion->label = 'Descripcion de la Clave de Financiamiento';

        $inputCFAbreviatura = new StdClass();
        $inputCFAbreviatura->id = 'CFabreviatura';
        $inputCFAbreviatura->name = 'CFabreviatura';
        $inputCFAbreviatura->text = $this->prm->cfi_abreviatura;
        $inputCFAbreviatura->enDiv = true;
        $inputCFAbreviatura->maxlength = 25;
        $inputCFAbreviatura->label = 'Abreviatura de la Clave de Financiamiento';

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT ccf.ecf_poa, ccf.ecf_division, ccf.pdp_id
            FROM " . BD_CTRL . ".enc_ClaveFinanciamiento ccf
            WHERE ccf.ecf_clave = ?;
        ");
        $conexion->consultar([$this->prm->llave]);
        $datos = $conexion->extraer();

        $html = '
            <fieldset>
                <legend>Editar</legend>
                <div class="row mb-4">
                    <fieldset class="col-md-6 col-xs-6" id="">
                        ' . $this->ponInput($inputClavesFinanciamiento) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6 mb-4" id="">
                        ' . $this->ponTextarea($inputNombreClaveF) . '
                    </fieldset>
                </div>
                <div class="row mb-4">
                    <fieldset class="col-md-6 col-xs-6 mb-4" id="">
                        ' . $this->ponTextarea($inputCFDescripcion) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="">
                        ' . $this->ponTextarea($inputCFAbreviatura) . '
                    </fieldset>
                </div>
                ' . (($this->presupuesto) ? '
                <div class="row">
                    <fieldset class="col-md-3 col-xs-3 " id="">
                        <label class="container">POA
                            <input type="checkbox" name="ECF_POA" id="ECF_POA" ' . (($datos['ecf_poa'] == 1) ? 'value="1" checked="checked"' : 'value="2"') . '>
                            <span class="checkmark"></span>
                        </label>
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3 " id="">
                        <label class="container">DIVISIÓN
                            <input type="checkbox" name="ECF_DIVISION" id="ECF_DIVISION" ' . (($datos['ecf_division'] == 1) ? 'value="1" checked="checked"' : 'value="2"') . '>
                            <span class="checkmark"></span>
                        </label>
                    </fieldset>
                </div>
                ' : '') . '
                ' . (($this->tesoreria) ? '
                <div class="row">
                    <fieldset class="col-md-4 col-xs-3 " id="">
                        <label class="container">PDP
                            <input type="checkbox" name="PDP_ID" id="PDP_ID" ' . (($datos['pdp_id'] == 1) ? 'value="1" checked="checked"' : 'value="2"') . '>
                            <span class="checkmark"></span>
                        </label>
                    </fieldset>
                </div>
            ' : '') . '
            </fieldset>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarbusqueda").removeClass("btn-default");
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;

            $("#PDP_ID").on("click",function(e){
                if($(this).is(":checked")){
                    // Hacer algo si el checkbox ha sido seleccionado
                    $(this).attr("value", 1);
                }else{
                    $(this).attr("value", 2);
                }
            });
            $("#ECF_POA").on("click",function(e){
                if($(this).is(":checked")){
                    // Hacer algo si el checkbox ha sido seleccionado
                    $(this).attr("value", 1);
                }else{
                    $(this).attr("value", 2);
                }
            });

            $("#ECF_DIVISION").on("click",function(e){
                if($(this).is(":checked")){
                    // Hacer algo si el checkbox ha sido seleccionado
                    $(this).attr("value", 1);
                }else{
                    $(this).attr("value", 2);
                }
            });

            $("#NombreClaveF").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#NombreClaveF").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
            $("#CFdescripcion").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 45) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#CFdescripcion").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
            $("#CFabreviatura").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 45) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#CFabreviatura").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function actualizarClaveF()
    {
        $this->prm->cfi_id = (isset($this->prm->cfi_id)) ? $this->prm->cfi_id : "";
        $this->prm->nombre = (isset($this->prm->nombre)) ? $this->prm->nombre : "";
        $this->prm->descripcion = (isset($this->prm->descripcion)) ? $this->prm->descripcion : "";
        $this->prm->abreviatura = (isset($this->prm->abreviatura)) ? $this->prm->abreviatura : "";
        $this->prm->ECF_POA = (isset($this->prm->ECF_POA) && !empty($this->prm->ECF_POA)) ? $this->prm->ECF_POA : "";
        $this->prm->ECF_DIVISION = (isset($this->prm->ECF_DIVISION) && !empty($this->prm->ECF_DIVISION)) ? $this->prm->ECF_DIVISION : "";
        $this->prm->PDP_ID = (isset($this->prm->PDP_ID) && !empty($this->prm->PDP_ID)) ? $this->prm->PDP_ID : "";
        $this->prm->suborigenSeleccionado = (isset($this->prm->suborigenSeleccionado) && !empty($this->prm->suborigenSeleccionado)) ? $this->prm->suborigenSeleccionado : "";
        $this->cfEstaVinculadoAClc = false;
        $this->cfEstaVinculadoAfolio = false;

        $cff_id = substr($this->prm->cfi_id, -9, 1);
        $fte_id = substr($this->prm->cfi_id, -8, 1);
        $sft_id = substr($this->prm->cfi_id, -7, 1);
        $ori_id = substr($this->prm->cfi_id, -6, 1);
        $sor_id = substr($this->prm->cfi_id, -5, 1);
        $cfi_id = substr($this->prm->cfi_id, -4, 2);
        $cfi_anio = substr($this->prm->cfi_id, -2);
        $cfi_ejercicio2 = APP_EJERCICIO;
        $params2 = [
            'cff_id' => $cff_id,
            'fte_id' => $fte_id,
            'sft_id' => $sft_id,
            'ori_id' => $ori_id,
            'sor_id' => $sor_id,
            'cfi_id' => $cfi_id,
            'cfi_anio' => $cfi_anio,
            'cfi_ejercicio2' => $cfi_ejercicio2,
        ];

        if ($this->prm->nombre == "") {
            $this->mixScript("notify('Falta el Nombre del Sub-Origen', 'warning')");
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        if ($this->existePresupuestalyDetalleMod()) {
            $this->cfEstaVinculadoAClc = (claveFinanciamientoAfectadaPorClc($params2) > 0) ? true : false;

            if ($this->cfEstaVinculadoAClc && !$this->tesoreria && !$this->presupuesto) {
                $conexion->desconectar();
                $this->mixScript("notify('El nombre de la Clave de Financiamiento no se puede cambiar porque ya esta afectada por una CLC.', 'warning')");
                return 0;
            } else {
                $this->cfEstaVinculadoAfolio = (claveFinanciamientoAfectadaPorAdecuacion($params2) > 0) ? true : false;

                if ($this->cfEstaVinculadoAfolio && !$this->tesoreria && !$this->presupuesto) {
                    $conexion->desconectar();
                    $this->mixScript("notify('El nombre de la Clave de Financiamiento no se puede cambiar porque ya esta afectada por un folio de adecuación presupuestal.', 'warning')");
                    return 0;
                } else {
                    $this->prm2 = new stdClass();
                    $this->prm2->cff_id = $cff_id;
                    $this->prm2->fte_id = $fte_id;
                    $this->prm2->sft_id = $sft_id;
                    $this->prm2->ori_id = $ori_id;
                    $this->prm2->sor_id = $sor_id;
                    $this->prm2->cfi_id = $cfi_id;
                    $this->prm2->anio = $cfi_anio;
                    $this->prm2->descripcion = $this->prm->descripcion;
                    $this->prm2->abreviatura = $this->prm->abreviatura;
                    $this->prm2->nombre = $this->prm->nombre;
                    $this->prm2->ejercicio2 = APP_EJERCICIO;
                    $this->prm2->abc = 'c';
                    $this->updateClaveF();
                }
            }
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = $cff_id;
            $this->prm2->fte_id = $fte_id;
            $this->prm2->sft_id = $sft_id;
            $this->prm2->ori_id = $ori_id;
            $this->prm2->sor_id = $sor_id;
            $this->prm2->cfi_id = $cfi_id;
            $this->prm2->anio = $cfi_anio;
            $this->prm2->descripcion = $this->prm->descripcion;
            $this->prm2->abreviatura = $this->prm->abreviatura;
            $this->prm2->nombre = $this->prm->nombre;
            $this->prm2->ejercicio2 = APP_EJERCICIO;
            $this->prm2->abc = 'c';
            $this->updateClaveF();
        }
    }
    private function updateClaveF()
    {
        if (
            ($this->escritura || $this->tesoreria || $this->presupuesto)
            && ($this->cfEstaVinculadoAClc == false || $this->cfEstaVinculadoAfolio == false)
        ) {
            SinpresClaveFinanciamiento::abc($this->prm2);
        }
        if (!empty($this->prm->ECF_POA) || !empty($this->prm->ECF_DIVISION) || !empty($this->prm->PDP_ID)) {
            $this->prm3 = new stdClass();
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $params = [$this->ip, $this->bit_usr_id, 'ACTUALIZAR REGISTRO'];
            $conexion->setSql(' SET @usr_ip = INET_ATON(?), @usr_id = ? , @bit_observacion = ?;');
            $conexion->consultar($params);

            $sqlUpdate = '';
            $params = [];
            if (!empty($this->prm->ECF_POA)) {
                array_push($params, $this->prm->ECF_POA);
                $sqlUpdate .= 'ecf_poa = ?';
            }
            if (!empty($this->prm->ECF_DIVISION)) {
                array_push($params, $this->prm->ECF_DIVISION);
                $sqlUpdate .= (empty($sqlUpdate) ? '' : ', ');
                $sqlUpdate .= 'ecf_division = ?';
            }
            if (!empty($this->prm->PDP_ID)) {
                array_push($params, $this->prm->PDP_ID);
                $sqlUpdate .= (empty($sqlUpdate) ? '' : ', ');
                $sqlUpdate .= 'pdp_id = ?';
            }

            array_push($params, $this->prm2->cff_id);
            array_push($params, $this->prm2->fte_id);
            array_push($params, $this->prm2->sft_id);
            array_push($params, $this->prm2->ori_id);
            array_push($params, $this->prm2->sor_id);
            array_push($params, $this->prm2->cfi_id);
            array_push($params, $this->prm2->anio);
            $sql = "UPDATE " . BD_CTRL . ".enc_ClaveFinanciamiento SET $sqlUpdate
                WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?
                AND ori_id = ?
                AND sor_id = ?
                AND cfi_id = ?
                AND cfi_anio = ? ";
            $conexion->setSql($sql);
            $conexion->consultar($params);
        }
        $this->mixScript("notify('Se ha actualizado la Clave de Financiamiento " . $this->prm->cfi_id . ".', 'success');");
        return $this->altaClaveF();
    }
    //@suborigenSeleccionado=Al guardar correctamente se llama al metodo AltaClaveF y debemos saber si esta seccionado para poder seccionar el Sub-Origen.
    private function guardarClaveF()
    {
        $this->prm->CFD = isset($this->prm->CFD) ? $this->prm->CFD : "";
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        $this->prm->nombre = isset($this->prm->nombre) ? $this->prm->nombre : "";
        $this->prm->abreviatura = isset($this->prm->abreviatura) ? $this->prm->abreviatura : "";
        $this->prm->descripcion = isset($this->prm->descripcion) ? $this->prm->descripcion : "";
        $this->prm->referencia = isset($this->prm->referencia) ? $this->prm->referencia : "";
        $this->prm->ECF_POA = "1";
        $this->prm->ECF_DIVISION = (isset($this->prm->ECF_DIVISION) && !empty($this->prm->ECF_DIVISION)) ? $this->prm->ECF_DIVISION : "2";
        $this->prm->PDP_ID = (isset($this->prm->PDP_ID) && !empty($this->prm->PDP_ID)) ? $this->prm->PDP_ID : "2";
        $this->prm->suborigenSeleccionado = $this->prm->CFD;

        if ($this->prm->CFD == "") {
            $this->mixScript('notify("Seleccione una Clave de Financiamiento para dar de Alta", "warning")');
            return 0;
        }
        if ($this->prm->ejercicio == "") {
            $this->mixScript('notify("Ingrese el Ejercicio", "warning")');
            return 0;
        }
        if ($this->prm->ejercicio > APP_EJERCICIO) {
            $this->mixScript('notify("El año del ejercicio debe ser menor o igual a ' . APP_EJERCICIO . '", "warning")');
            return 0;
        }
        if ($this->prm->nombre == "") {
            $this->mixScript('notify("Ingresar el Nombre", "warning")');
            return 0;
        }
        if ($this->prm->abreviatura == "") {
            $this->mixScript('notify("Introduzca una Abreviatura", "warning")');
            return 0;
        }
        if ($this->prm->descripcion == "") {
            $this->mixScript('notify("Introduzca una Descripción", "warning")');
            return 0;
        }
        if ($this->prm->referencia == "") {
            $this->mixScript('notify("Introduzca una referencia", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->CFD, 0, 1),
            substr($this->prm->CFD, 1, 1),
            substr($this->prm->CFD, 2, 1),
            substr($this->prm->CFD, 3, 1),
            substr($this->prm->CFD, 4, 1),
            $this->prm->referencia,
            substr($this->prm->ejercicio, -2),
        ];
        $conexion->setSql(" SELECT cfi_clave
                FROM " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . "
                WHERE cff_id = ?
                and fte_id = ?
                and sft_id = ?
                and ori_id = ?
                and sor_id = ?
                and cfi_id = ?
                and cfi_anio = ?
                for update;");
        $conexion->consultar($params);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() == 0) {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = substr($this->prm->CFD, 0, 1);
            $this->prm2->fte_id = substr($this->prm->CFD, 1, 1);
            $this->prm2->sft_id = substr($this->prm->CFD, 2, 1);
            $this->prm2->ori_id = substr($this->prm->CFD, 3, 1);
            $this->prm2->sor_id = substr($this->prm->CFD, 4, 1);
            $this->prm2->cfi_id = $this->prm->referencia;
            $this->prm2->anio = substr($this->prm->ejercicio, -2);
            $this->prm2->clave = substr($this->prm->CFD, 0, 1) . substr($this->prm->CFD, 1, 1) .
            substr($this->prm->CFD, 2, 1) . substr($this->prm->CFD, 3, 1) . substr($this->prm->CFD, 4, 1) .
            $this->prm->referencia . substr($this->prm->ejercicio, -2);
            $this->prm2->nombre = $this->prm->nombre;
            $this->prm2->ejercicio = $this->prm->ejercicio;
            $this->prm2->descripcion = $this->prm->descripcion;
            $this->prm2->abreviatura = $this->prm->abreviatura;
            $this->prm2->ejercicio2 = APP_EJERCICIO;
            $this->prm2->abc = "a";
            SinpresClaveFinanciamiento::abc($this->prm2);
            $this->prm2->abc = "c";
            $this->prm2->poa = "1";

            $conexion = new AccesoDatos();
            $conexion->conectar();
            array_push($params, APP_EJERCICIO);
            $conexion->setSql(" SELECT ecf_id from " . BD_SINPRES . ".enc_ClaveFinanciamiento
                WHERE cff_id = ?
                and fte_id = ?
                and sft_id = ?
                and ori_id = ?
                and sor_id = ?
                and cfi_id = ?
                and cfi_anio = ?
                AND ecf_ejercicio = ?;
                ");
            $conexion->consultar($params);
            $conexion->desconectar();
            $valor = $conexion->extraer();
            $ecfID = $valor['ecf_id'];

            $this->prm2->ecf_id = $ecfID;

            //Ya no se inserta porque se creo un disparador que hace esta tarea.
            if (!empty($this->prm->ECF_POA) || !empty($this->prm->ECF_DIVISION) || !empty($this->prm->PDP_ID)) {
                $this->prm3 = new stdClass();
                $this->prm3->ecf_id = $ecfID;
                $this->prm3->cff_id = $this->prm2->cff_id;
                $this->prm3->fte_id = $this->prm2->fte_id;
                $this->prm3->sft_id = $this->prm2->sft_id;
                $this->prm3->ori_id = $this->prm2->ori_id;
                $this->prm3->sor_id = $this->prm2->sor_id;
                $this->prm3->cfi_id = $this->prm2->cfi_id;
                $this->prm3->anio = $this->prm2->anio;
                $this->prm3->poa = $this->prm->ECF_POA;
                $this->prm3->division = $this->prm->ECF_DIVISION;
                $this->prm3->clave = $this->prm2->clave;
                $this->prm3->techoFinanciero = '0.00';
                $this->prm3->claveAnterior = ' ';
                $this->prm3->pdp_id = $this->prm->PDP_ID;
                $this->prm3->sts_id = 1;

                $this->prm3->abc = 'c';
                CtrlEncuadreClaveFinanciamiento::abc($this->prm3);
            }
            $this->mixScript("notify('Se ha dado de alta la Clave de Financiamiento.', 'success');");
            $this->altaClaveF();
        } else {
            $this->mixScript("notify('La Clave de Financiamiento que intenta dar de alta ya se encuentra registrada.', 'warning')");
            return 0;
        }
    }
    private function showEliminarClaveF()
    {
        $this->prm->llave = isset($this->prm->llave) ? $this->prm->llave : "";
        $this->setContenedor('dialogEliminarClaveF');

        $form = new stdClass();
        $form->id = 'frmEliminarClaveF';
        $form->controlador = $this->pantalla;
        $form->metodo = 'eliminarClaveF';
        $form = new form($form);

        $form->mixHtml('
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        ¿Esta seguro de eliminar la Clave de Financiamiento ' . $this->prm->llave . '?
                        <input type="hidden" name="llave" value="' . $this->prm->llave . '" />
                        <input type="hidden" name="suborigenSeleccionado" value="' . $this->prm->suborigenSeleccionado . '" />
                    </fieldset>
                </div>
            ');
        $this->mixHtml('
                <div id="myModalMeta" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                    <div class="modal-dialog modal-lg" >
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Eliminar Clave Financiamiento</h4>
                                <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">
                                ' . $form->ponForm() . '
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-outline-secondary" id="btnSiEliminarClaveF" form="frmEliminarClaveF">Si</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
        $this->mixScript('
            $(".cerrar").click(function(){ $("#myModalMeta").remove(); });

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                $(".cerrar").click();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); e.stopPropagation(); });
                e.stopPropagation();
            });
        ');
    }
    private function eliminarClaveF()
    {
        $this->prm->llave = isset($this->prm->llave) ? $this->prm->llave : "";
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->llave, -9, 1),
            substr($this->prm->llave, -8, 1),
            substr($this->prm->llave, -7, 1),
            substr($this->prm->llave, -6, 1),
            substr($this->prm->llave, -5, 1),
            substr($this->prm->llave, -4, 2),
            substr($this->prm->llave, -2, 2),
        ];
        $conexion->setSql("
            SELECT  ecf.ecf_id
            FROM " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cfi
            INNER JOIN " . BD_SINPRES . ".enc_ClaveFinanciamiento ecf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            INNER JOIN " . BD_CTRL . ".poa_OrigenRecurso ore USING(ecf_id)
            WHERE cfi.cff_id = ?
            AND cfi.fte_id = ?
            AND cfi.sft_id = ?
            AND cfi.ori_id = ?
            AND cfi.sor_id = ?
            AND cfi.cfi_id = ?
            AND cfi.cfi_anio = ?
            FOR UPDATE;
        ");
        $conexion->consultar($params);
        $conexion->desconectar();
        if ($conexion->getNumeroRegistros() == 0) {
            $sql = '
            SELECT ecf.ecf_id, vrf.cbs_id 
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cfi
            INNER JOIN ' . BD_SINPRES . '.enc_ClaveFinanciamiento ecf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento vrf USING(ecf_id)
            WHERE cfi.cff_id = ?
            AND cfi.fte_id = ?
            AND cfi.sft_id = ?
            AND cfi.ori_id = ?
            AND cfi.sor_id = ?
            AND cfi.cfi_id = ?
            AND cfi.cfi_anio = ?
            ';

            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);

            if ($this->getNumeroRegistros() > 0) {
                $this->mixScript("
                $('#dialogEliminarClaveF').remove(); 
                notify('La Clave de Financiamiento que intenta eliminar no puede eliminarse porque ya se encuentra vinculada a una cuenta.', 'warning')");
                return 0;
            }

            $this->prm2 = new stdClass();
            // 'BFXYZ0125'
            $this->prm2->cff_id = substr($this->prm->llave, -9, 1);
            $this->prm2->fte_id = substr($this->prm->llave, -8, 1);
            $this->prm2->sft_id = substr($this->prm->llave, -7, 1);
            $this->prm2->ori_id = substr($this->prm->llave, -6, 1);
            $this->prm2->sor_id = substr($this->prm->llave, -5, 1);
            $this->prm2->cfi_id = substr($this->prm->llave, -4, 2);
            $this->prm2->anio = substr($this->prm->llave, -2, 2);
            $this->prm2->ejercicio2 = APP_EJERCICIO;
            $this->prm2->abc = 'b';
            SinpresClaveFinanciamiento::abc($this->prm2);

            // Consultar ECF_ID
            $conexion->conectar();
            array_push($params, APP_EJERCICIO);
            $conexion->setSql(" SELECT ecf_id FROM " . BD_SINPRES . ".enc_ClaveFinanciamiento
                WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?
                AND ori_id = ?
                AND sor_id = ?
                AND cfi_id = ?
                AND cfi_anio = ?
                AND ecf_ejercicio = ?;
            ");
            $conexion->consultar($params);
            $valor = $conexion->extraer();
            $ecfID = $valor['ecf_id'];

            // Eliminar ECF_ID de BD_SINPRES.enc_ClaveFinanciamiento
            unset($this->prm2->ejercicio2);
            $this->prm2->ecf_id = $ecfID;
            $this->prm2->ecf_ejercicio = APP_EJERCICIO;
            $this->prm2->abc = "b";
            SinpresEncuadreClaveFinanciamiento::abc($this->prm2);
            // Eliminar ECF_ID de BD_CTRL.enc_ClaveFinanciamiento
            CtrlEncuadreClaveFinanciamiento::abc($this->prm2);

            $this->mixScript("notify('Se ha eliminado la Clave de Financiamiento " . $this->prm->llave . ".', 'success');");
            $this->altaClaveF();
        } else {
            $this->mixScript("notify('La Clave de Financiamiento que intenta eliminar no puede eliminarse porque ya se encuentra en POA.', 'warning')");
            return 0;
        }
    }
    private function altaFuente()
    {
        $this->prm->clasificadorFuenteSeleccionado = isset($this->prm->clasificadorFuenteSeleccionado) ? $this->prm->clasificadorFuenteSeleccionado : '';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "menuAltaCF"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnNuevaFuente = new stdClass();
        $btnNuevaFuente->id = 'btnNuevaFuente';
        $btnNuevaFuente->name = 'btnNuevaFuente';
        $btnNuevaFuente->text = 'Nuevo';
        $btnNuevaFuente->value = 'Nueva';
        $btnNuevaFuente->type = 'button';
        $btnNuevaFuente->class = 'me-1';
        $btnNuevaFuente->color = 'azul';
        $btnNuevaFuente->icon = 'solid fa-plus me-2';
        $btnNuevaFuente->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "nuevaFuente",
                clasificadorFuente: $("#clasificadorFF").val()
            });
        ';

        $btnPDF = new stdClass();
        $btnPDF->id = 'btnPrintPdf';
        $btnPDF->name = 'btnPrintPdf';
        $btnPDF->text = 'PDF';
        $btnPDF->value = 'PDF';
        $btnPDF->type = 'button';
        $btnPDF->class = 'me-1';
        $btnPDF->color = 'rojo';
        $btnPDF->icon = 'solid fa-file-pdf me-2';
        $btnPDF->click = '
            var form = $("<form>")
            .attr("action", "rep/doc/tes/catalogo/rep_ClaveFinanciamiento.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 0))
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#clasificadorFF option:selected").attr("fuente")))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $btnExcel = new stdClass();
        $btnExcel->id = 'btnPrintExcel';
        $btnExcel->name = 'btnPrintExcel';
        $btnExcel->text = 'EXCEL';
        $btnExcel->value = 'Excel';
        $btnExcel->type = 'button';
        $btnExcel->class = 'me-1';
        $btnExcel->color = 'verde';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->click = '
            var form = $("<form>")
            .attr("action", "rep/excel/tes/catalogo/exp_ClaveFinanciamientoFuente.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#clasificadorFF option:selected").attr("fuente")))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $juntarBotones = '';
        if ($this->lectura && $this->escritura) {
            $juntarBotones = '' . $this->ponBoton($btnNuevaFuente) . '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        } elseif ($this->lectura) {
            $juntarBotones = '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        }
        $this->agregaTools($juntarBotones);

        $inputClasificadorFF = new StdClass();
        $inputClasificadorFF->id = 'clasificadorFF';
        $inputClasificadorFF->name = 'clasificadorFF';
        $inputClasificadorFF->enDiv = true;
        $inputClasificadorFF->label = 'Clasificador Fuente Financiamiento';
        $inputClasificadorFF->sql = "SELECT concat(cff_id) fuente, cff_nombre FROM " . BD_SINPRES . ".cat_ClasificadorFuenteFinanciamiento";
        $inputClasificadorFF->camposVal = 'fuente';
        $inputClasificadorFF->camposText = 'cff_nombre';
        $inputClasificadorFF->comoAtr = 'fuente';
        $inputClasificadorFF->search = true;
        !empty($this->prm->clasificadorFuenteSeleccionado) ? $inputClasificadorFF->selecciona = $this->prm->clasificadorFuenteSeleccionado : '';

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tablaFuente';
        $tabla->titulo = 'Lista de Fuentes';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaFuente';

        $tr = array();

        if ($this->lectura && $this->escritura) {
            $th = new stdClass();
            $th->texto = 'EDITAR';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'cff_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'fte_id';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre Fuente';
        array_push($tr, $th);

        $trbody = array();

        if ($this->lectura && $this->escritura) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-pencil';
            $td->class = 'Editar';
            $td->name = 'editar';
            $td->id = 'editar';
            $td->attr = new stdClass();
            $td->attr->llave = 'llave';
            $td->attr->fte_nombre = 'fte_nombre';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'cff_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fte_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fte_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $inputBuscarClave = new StdClass();
        $inputBuscarClave->id = 'inputBuscarClave';
        $inputBuscarClave->name = 'inputBuscarClave';
        $inputBuscarClave->placeholder = 'Fuente';
        $inputBuscarClave->enDiv = true;
        $inputBuscarClave->label = 'Fuente';
        $inputBuscarClave->maxlength = 1;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'buscar';
        $btnBuscar->name = 'buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"llenaTablaFuente",
                clasificadorFuente:$("#clasificadorFF").val(),
                clave:$("#inputBuscarClave").val()
            });
        ';

        $this->mixHTML('
            <div class="row mb-4">
                <fieldset class="col-md-7 col-xs-7 " id="">
                    ' . $this->ponCombo($inputClasificadorFF) . '
                </fieldset>

                <fieldset class="col-md-4 col-xs-4" id="">
                    ' . $this->ponInput($inputBuscarClave) . '
                </fieldset>
                <fieldset class=" col-md-1 col-xs-1 mt-3" style="display: flex; justify-content: right;" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');
        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            if($("#clasificadorFF").val()!="") {
                comunica("' . $this->pantalla . '", {
                    metodo:"llenaTablaFuente",
                    clasificadorFuente:$("#clasificadorFF").val(),
                    clave:$("#inputBuscarClave").val()
                });
            }
        ');
        $this->llenaTablaFuente();
    }
    private function nuevaFuente()
    {
        $this->prm->clasificadorFuente = isset($this->prm->clasificadorFuente) ? $this->prm->clasificadorFuente : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "altaFuente",
                clasificadorFuenteSeleccionado: "' . $this->prm->clasificadorFuente . '"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnFuente = new stdClass();
        $btnFuente->id = 'btnFuente';
        $btnFuente->name = 'btnFuente';
        $btnFuente->text = 'Guardar';
        $btnFuente->value = 'Guardar';
        $btnFuente->type = 'button';
        $btnFuente->class = 'me-1';
        $btnFuente->color = 'verde';
        $btnFuente->icon = 'solid fa-floppy-disk me-2';
        $btnFuente->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "guardarFuente",
                CFF:$("#clasificadorFF option:selected").attr("fuent"),
                F: $("#fuente").val(),
                FN: $("#fnombre").val(),
                FE: ' . APP_EJERCICIO . '
            });
        ';
        $this->agregaTools('' . $this->ponBoton($btnFuente));

        $inputClasificadorFF = new StdClass();
        $inputClasificadorFF->id = 'clasificadorFF';
        $inputClasificadorFF->name = 'clasificadorFF';
        $inputClasificadorFF->enDiv = true;
        $inputClasificadorFF->label = 'Clasificador Fuente Financiamiento';
        $params = [];
        $sql = "SELECT concat(cff_id) fuent, cff_nombre FROM " . BD_SINPRES . ".cat_ClasificadorFuenteFinanciamiento ";
        if (!empty($this->prm->clasificadorFuente)) {
            array_push($params, '%' . $this->prm->clasificadorFuente . '%');
            $sql .= "HAVING fuent LIKE ? ";
        }
        $inputClasificadorFF->sql = $sql;
        $inputClasificadorFF->params = $params;
        $inputClasificadorFF->camposVal = 'fuent';
        $inputClasificadorFF->camposText = 'cff_nombre';
        $inputClasificadorFF->comoAtr = 'fuent';
        $inputClasificadorFF->search = true;

        $inputFuente = new StdClass();
        $inputFuente->id = 'fuente';
        $inputFuente->name = 'fuente';
        $inputFuente->placeholder = 'Fuente';
        $inputFuente->enDiv = true;
        $inputFuente->label = 'Fuente';
        $inputFuente->maxlength = 1;

        $inputFNombre = new StdClass();
        $inputFNombre->id = 'fnombre';
        $inputFNombre->name = 'fnombre';
        $inputFNombre->placeholder = 'Nombre Fuente';
        $inputFNombre->enDiv = true;
        $inputFNombre->label = 'Nombre Fuente';
        $inputFNombre->maxlength = 55;
        $inputFNombre->rows = 2;
        $inputFNombre->cols = 6;

        $this->mixHTML('
            <div class="row">
                <fieldset class="col-md-4 col-xs-4 mb-4" id="">
                    ' . $this->ponCombo($inputClasificadorFF) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-2 col-xs-2 " id="">
                    ' . $this->ponInput($inputFuente) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6 " id="">
                    ' . $this->ponTextarea($inputFNombre) . '
                </fieldset>
            </div>
        ');
        $this->mixScript('
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#btnregresarindex").removeClass("btn-default");
            $("#fuente").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                var Charcode=String.fromCharCode(keynum);
                if (keynum == 8 || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                if (keynum == 95)
                    return false;
                if (/\d/.test(Charcode))
                    return false;
                return /\w/.test(Charcode);
            });

            $("#fuente").keyup(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"validarFuente",
                    CFF:$("#clasificadorFF option:selected").attr("fuent"),
                    F: $("#fuente").val()
                });
                $(this).val($(this).val().toUpperCase());
            });

            $("#fnombre").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#fnombre").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function llenaTablaFuente()
    {
        $this->prm->clasificadorFuente = isset($this->prm->clasificadorFuente) ? $this->prm->clasificadorFuente : "";
        $this->prm->clave = isset($this->prm->clave) ? $this->prm->clave : "";
        $params = [];
        $sqlExtra = "";
        if (!empty($this->prm->clasificadorFuente)) {
            if (!empty($this->prm->clave)) {
                array_push($params, '%' . $this->prm->clasificadorFuente . $this->prm->clave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } else {
                array_push($params, '%' . $this->prm->clasificadorFuente . '%');
                $sqlExtra = "HAVING clasificadorFF LIKE ? ";
            }
        } elseif (!empty($this->prm->clave)) {
            array_push($params, '%' . $this->prm->clave . '%');
            $sqlExtra = "HAVING fte_id LIKE ? ";
        }
        array_unshift($params, APP_EJERCICIO);
        $sql = "SELECT CONCAT(cff_id) AS clasificadorFF, CONCAT(cff_id, fte_id) AS llave,
                cff_id, fte_id, fte_nombre FROM " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . "
                WHERE fte_ejercicio = ?
                $sqlExtra
                ORDER BY llave ASC";
        $this->setSql($sql . "; ");
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaFuente";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".Editar").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"editaFuente",
                    llave: $(this).attr("llave"),
                    nombreFuente: $(this).attr("fte_nombre"),
                    clasificadorFuenteSeleccionado: $("#clasificadorFF").val(),
                    Edit: "editar"
                });
            });
        ');
    }
    private function editaFuente()
    {
        $this->prm->llave = isset($this->prm->llave) ? $this->prm->llave : "";
        $this->prm->nombreFuente = isset($this->prm->nombreFuente) ? $this->prm->nombreFuente : "";
        $this->prm->Edit = (isset($this->prm->Edit)) ? $this->prm->Edit : "";
        $this->prm->clasificadorFuenteSeleccionado = (isset($this->prm->clasificadorFuenteSeleccionado)) ? $this->prm->clasificadorFuenteSeleccionado : "";
        if (!$this->escritura) {
            $this->mixScript('notify("No tiene permitido editar las cuentas", "error");');
        }

        $btnRegresarBusqueda = new stdClass();
        $btnRegresarBusqueda->id = 'btnregresarbusqueda';
        $btnRegresarBusqueda->name = 'btnregresarbusqueda';
        $btnRegresarBusqueda->text = 'Regresar';
        $btnRegresarBusqueda->value = 'Regresar';
        $btnRegresarBusqueda->type = 'button';
        $btnRegresarBusqueda->color = 'gris';
        $btnRegresarBusqueda->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnRegresarBusqueda->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo:"altaFuente",
                clasificadorFuenteSeleccionado: "' . $this->prm->clasificadorFuenteSeleccionado . '"
            });
        ';

        $btnEditar = new stdClass();
        $btnEditar->id = 'editar';
        $btnEditar->name = 'editar';
        $btnEditar->text = 'Guardar';
        $btnEditar->value = 'Guardar';
        $btnEditar->type = 'button';
        $btnEditar->class = 'me-1';
        $btnEditar->color = 'verde';
        $btnEditar->icon = 'solid fa-floppy-disk me-2';
        $btnEditar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"actualizarFuente",
                fte_id:$("#clabeF").val(),
                nombre:$("#nombreFuente").val(),
                clasificadorFuenteSeleccionado: "' . $this->prm->clasificadorFuenteSeleccionado . '"
            });';

        $vEditar = $this->prm->Edit;
        if ($vEditar == "editar") {
            $this->agregaTools($this->ponBoton($btnRegresarBusqueda));
            if ($this->escritura) {
                $this->agregaTools($this->ponBoton($btnEditar));
            }
        }

        $inputClavesFinanciamiento = new StdClass();
        $inputClavesFinanciamiento->id = 'clabeF';
        $inputClavesFinanciamiento->name = 'clabeF';
        $inputClavesFinanciamiento->value = $this->prm->llave;
        $inputClavesFinanciamiento->enDiv = true;
        $inputClavesFinanciamiento->label = 'Fuente';
        $inputClavesFinanciamiento->disabled = true;

        $inputNombreSubFuente = new StdClass();
        $inputNombreSubFuente->id = 'nombreFuente';
        $inputNombreSubFuente->name = 'nombreFuente';
        $inputNombreSubFuente->text = $this->prm->nombreFuente;
        $inputNombreSubFuente->enDiv = true;
        $inputNombreSubFuente->maxlength = 55;
        $inputNombreSubFuente->label = 'Nombre de la Fuente';

        $html = '
            <fieldset>
                <legend>Editar</legend>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 " id="">
                        ' . $this->ponInput($inputClavesFinanciamiento) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="">
                        ' . $this->ponTextarea($inputNombreSubFuente) . '
                    </fieldset>
                </div>
            </fieldset>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarbusqueda").removeClass("btn-default");
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#nombreFuente").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#nombreFuente").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function actualizarFuente()
    {
        $this->prm->fte_id = (isset($this->prm->fte_id)) ? $this->prm->fte_id : "";
        $this->prm->nombre = (isset($this->prm->nombre)) ? $this->prm->nombre : "";

        $this->prm->clasificadorFuenteSeleccionado = (isset($this->prm->clasificadorFuenteSeleccionado)) ?
        $this->prm->clasificadorFuenteSeleccionado
        : "";

        $cff_id = substr($this->prm->fte_id, -2, 1);
        $fte_id = substr($this->prm->fte_id, -1);
        $fte_ejercicio = APP_EJERCICIO;
        $params2 = [
            'cff_id' => $cff_id,
            'fte_id' => $fte_id,
            'cfi_ejercicio2' => $fte_ejercicio,
        ];
        if ($this->prm->nombre == "") {
            $this->mixScript("notify('Falta el Nombre de la Fuente', 'warning')");
            return 0;
        }

        if ($this->existePresupuestalyDetalleMod()) {
            if (claveFinanciamientoAfectadaPorClc($params2) > 0) {
                $this->mixScript("notify('El nombre de la Fuente no se puede cambiar porque ya esta afectada por una CLC.', 'warning')");
                return 0;
            } else {
                if (claveFinanciamientoAfectadaPorAdecuacion($params2) > 0) {
                    $this->mixScript('notify("El nombre de la Fuente no se puede cambiar porque ya esta afectada por un folio de adecuación presupuestal.", "warning");');
                    return 0;
                } else {
                    $this->prm2 = new stdClass();
                    $this->prm2->cff_id = $cff_id;
                    $this->prm2->fte_id = $fte_id;
                    $this->prm2->nombre = $this->prm->nombre;
                    $this->prm2->ejercicio = APP_EJERCICIO;
                    $this->prm2->abc = 'c';
                    $this->updateFuente();
                }
            }
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = $cff_id;
            $this->prm2->fte_id = $fte_id;
            $this->prm2->nombre = $this->prm->nombre;
            $this->prm2->ejercicio = APP_EJERCICIO;
            $this->prm2->abc = 'c';
            $this->updateFuente();
        }
    }
    private function updateFuente()
    {
        Fuente::abc($this->prm2);
        $this->mixScript("notify('Se ha actualizado la Fuente " . $this->prm->fte_id . ".', 'success');");
        return $this->altaFuente();
    }
    private function validarFuente()
    {
        $this->prm->CFF = isset($this->prm->CFF) ? $this->prm->CFF : "";
        $this->prm->F = isset($this->prm->F) ? $this->prm->F : "";

        if ($this->prm->CFF == "") {
            $this->mixScript('notify("Seleccione un Clasificador de Fuente de Financiamiento", "warning")');
            return 0;
        }
        if ($this->prm->F == "") {
            $this->mixScript('notify("Ingrese la Fuente", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT cff_id, fte_id FROM " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . "
                WHERE cff_id = ?
                AND fte_id = ? ");
        $conexion->consultar([$this->prm->CFF, $this->prm->F]);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La Fuente ya existe.', 'warning');");
            return 0;
        } else {
            $this->mixScript("notify('Fuente disponible.', 'warning');");
            return 0;
        }
    }
    private function guardarFuente()
    {
        $this->prm->CFF = isset($this->prm->CFF) ? $this->prm->CFF : "";
        $this->prm->F = isset($this->prm->F) ? $this->prm->F : "";
        $this->prm->FN = isset($this->prm->FN) ? $this->prm->FN : "";
        $this->prm->FE = isset($this->prm->FE) ? $this->prm->FE : "";

        $this->prm->clasificadorFuenteSeleccionado = $this->prm->CFF;

        if ($this->prm->CFF == "") {
            $this->mixScript('notify("Seleccione un Clasificador de Fuente de Financiamiento", "warning");');
            return 0;
        }
        if ($this->prm->F == "") {
            $this->mixScript('notify("Ingresar la Fuente", "warning")');
            return 0;
        }
        if ($this->prm->FN == "") {
            $this->mixScript('notify("Ingresar el Nombre Fuente", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(" SELECT cff_id, fte_id FROM " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . "
            WHERE cff_id = ?
                AND fte_id = ? ");
        $conexion->consultar([$this->prm->CFF, $this->prm->F]);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La Fuente ya existe.', 'warning')");
            return 0;
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = $this->prm->CFF;
            $this->prm2->fte_id = $this->prm->F;
            $this->prm2->nombre = $this->prm->FN;
            $this->prm2->descripcion = "--";
            $this->prm2->ejercicio = $this->prm->FE;
            $this->prm2->clave = $this->prm->CFF . $this->prm->F;
            $this->prm2->abc = 'a';
            Fuente::abc($this->prm2);
            $this->mixScript("notify('Se ha dado de alta la Fuente.', 'success');");
            $this->altaFuente();
        }
    }
    private function altaSubfuente()
    {
        $this->prm->fuenteSeleccionado = isset($this->prm->fuenteSeleccionado) ? $this->prm->fuenteSeleccionado : '';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "menuAltaCF"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnNuevaSubfuente = new stdClass();
        $btnNuevaSubfuente->id = 'btnNuevasubfuente';
        $btnNuevaSubfuente->name = 'btnNuevasubfuente';
        $btnNuevaSubfuente->text = 'Nuevo';
        $btnNuevaSubfuente->value = 'Nueva';
        $btnNuevaSubfuente->type = 'button';
        $btnNuevaSubfuente->class = 'me-1';
        $btnNuevaSubfuente->color = 'azul';
        $btnNuevaSubfuente->icon = 'solid fa-plus me-2';
        $btnNuevaSubfuente->click = '
            comunica("' . $this->pantalla . '",{
                metodo: "nuevaSubfuente",
                fuente: $("#fuent").val()
            });
        ';

        $btnPDF = new stdClass();
        $btnPDF->id = 'btnPrintPdf';
        $btnPDF->name = 'btnPrintPdf';
        $btnPDF->text = 'PDF';
        $btnPDF->value = 'PDF';
        $btnPDF->type = 'button';
        $btnPDF->class = 'me-1';
        $btnPDF->color = 'rojo';
        $btnPDF->icon = 'solid fa-file-pdf me-2';
        $btnPDF->click = '
            var form = $("<form>")
            .attr("action", "rep/doc/tes/catalogo/rep_ClaveFinanciamiento.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 1))
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#fuent").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $btnExcel = new stdClass();
        $btnExcel->id = 'btnPrintExcel';
        $btnExcel->name = 'btnPrintExcel';
        $btnExcel->text = 'EXCEL';
        $btnExcel->value = 'Excel';
        $btnExcel->type = 'button';
        $btnExcel->class = 'me-1';
        $btnExcel->color = 'verde';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->click = '
            var form = $("<form>")
            .attr("action", "rep/excel/tes/catalogo/exp_ClaveFinanciamientoSubFuente.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#fuent").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $juntarBotones = '';
        if ($this->lectura && $this->escritura) {
            $juntarBotones = '' . $this->ponBoton($btnNuevaSubfuente) . '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        } elseif ($this->lectura) {
            $juntarBotones = '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        }
        $this->agregaTools($juntarBotones);

        $inputFuente = new StdClass();
        $inputFuente->id = 'fuent';
        $inputFuente->name = 'fuent';
        $inputFuente->enDiv = true;
        $inputFuente->label = 'Fuente';
        $inputFuente->sql = "SELECT concat(cff_id, fte_id) fuente, fte_nombre FROM " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . " WHERE fte_ejercicio = " . APP_EJERCICIO;
        $inputFuente->camposVal = 'fuente';
        $inputFuente->camposText = 'fte_nombre';
        $inputFuente->comoAtr = 'fuente';
        $inputFuente->search = true;
        if (!empty($this->prm->fuenteSeleccionado)) {
            $inputFuente->selecciona = $this->prm->fuenteSeleccionado;
        }

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tablaSubfuente';
        $tabla->titulo = 'Lista de Sub-Fuente';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaSubfuente';

        $tr = array();

        if ($this->lectura && $this->escritura) {
            $th = new stdClass();
            $th->texto = 'EDITAR';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'cff_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'fte_id';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'sft_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de Sub-Fuente';
        array_push($tr, $th);

        $trbody = array();
        if ($this->lectura && $this->escritura) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-pencil';
            $td->class = 'Editar';
            $td->name = 'editar';
            $td->id = 'editar';
            $td->attr = new stdClass();
            $td->attr->llave = 'llave';
            $td->attr->sft_nombre = 'sft_nombre';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'cff_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fte_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sft_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sft_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $inputBuscarClave = new StdClass();
        $inputBuscarClave->id = 'inputBuscarClave';
        $inputBuscarClave->name = 'inputBuscarClave';
        $inputBuscarClave->placeholder = 'Sub-Fuente';
        $inputBuscarClave->enDiv = true;
        $inputBuscarClave->label = 'Sub-Fuente';
        $inputBuscarClave->maxlength = 1;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'buscar';
        $btnBuscar->name = 'buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"llenaTablaSubfuente",
                fuente:$("#fuent").val(),
                clave:$("#inputBuscarClave").val()
            });
        ';

        $this->mixHTML('
            <div class="row mb-4">
                <fieldset class="col-md-7 col-xs-7" id="">
                    ' . $this->ponCombo($inputFuente) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 " id="">
                    ' . $this->ponInput($inputBuscarClave) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1 mt-3" style="display: flex; justify-content: right;" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');
        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            if($("#fuent").val() != ""){
                comunica("' . $this->pantalla . '", {
                    metodo:"llenaTablaSubfuente",
                    fuente:$("#fuent").val(),
                    clave:$("#inputBuscarClave").val()
                });
            }
        ');
        $this->llenaTablaSubfuente();
    }
    private function nuevaSubfuente()
    {
        $this->prm->fuente = isset($this->prm->fuente) ? $this->prm->fuente : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "altaSubfuente",
                fuenteSeleccionado: "' . $this->prm->fuente . '"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnSubfuente = new stdClass();
        $btnSubfuente->id = 'btnsubfuente';
        $btnSubfuente->name = 'btnsubfuente';
        $btnSubfuente->text = 'Guardar';
        $btnSubfuente->value = 'Guardar';
        $btnSubfuente->type = 'button';
        $btnSubfuente->class = 'me-1';
        $btnSubfuente->color = 'verde';
        $btnSubfuente->icon = 'solid fa-floppy-disk me-2';
        $btnSubfuente->click = '
            comunica("' . $this->pantalla . '",{
                metodo: "guardarSubfuente",
                SF: $("#subfuente").val(),
                F:$("#fuent option:selected").attr("fuente"),
                FN: $("#sfnombre").val(),
                FE: ' . APP_EJERCICIO . '
            });
        ';
        $this->agregaTools('' . $this->ponBoton($btnSubfuente));

        $inputFuente = new StdClass();
        $inputFuente->id = 'fuent';
        $inputFuente->name = 'fuent';
        $inputFuente->enDiv = true;
        $inputFuente->label = 'Fuente';
        $params = [APP_EJERCICIO];
        $inputFuente->sql = "SELECT concat(cff_id, fte_id) fuente, fte_nombre FROM " .
            BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . " WHERE fte_ejercicio = ? ";
        if (!empty($this->prm->fuente)) {
            array_push($params, '%' . $this->prm->fuente . '%');
            $inputFuente->sql .= "HAVING fuente LIKE ?";
        }
        $inputFuente->params = $params;
        $inputFuente->camposVal = 'fuente';
        $inputFuente->camposText = 'fte_nombre';
        $inputFuente->comoAtr = 'fuente';
        $inputFuente->search = true;

        $inputSubFuente = new StdClass();
        $inputSubFuente->id = 'subfuente';
        $inputSubFuente->name = 'subfuente';
        $inputSubFuente->placeholder = 'Sub-Fuente';
        $inputSubFuente->enDiv = true;
        $inputSubFuente->label = 'Sub-Fuente';
        $inputSubFuente->maxlength = 1;

        $inputSFNombre = new StdClass();
        $inputSFNombre->id = 'sfnombre';
        $inputSFNombre->name = 'sfnombre';
        $inputSFNombre->placeholder = 'Nombre Sub-Fuente';
        $inputSFNombre->enDiv = true;
        $inputSFNombre->label = 'Nombre de la Sub-Fuente';
        $inputSFNombre->maxlength = 60;
        $inputSFNombre->rows = 2;
        $inputSFNombre->cols = 6;

        $this->mixHTML('
            <div class="row">
                <fieldset class="col-md-4 col-xs-4 mb-4" id="">
                    ' . $this->ponCombo($inputFuente) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-2 col-xs-2 " id="">
                    ' . $this->ponInput($inputSubFuente) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6 " id="">
                    ' . $this->ponTextarea($inputSFNombre) . '
                </fieldset>
            </div>
        ');

        $this->mixScript('
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#btnregresarindex").removeClass("btn-default");
            $("#subfuente").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8 || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                if (keynum == 95)
                    return false;
                if (/\d/.test(String.fromCharCode(keynum)))
                    return false;
                return /\w/.test(String.fromCharCode(keynum));
            });

            $("#subfuente").keyup(function() {
                comunica("' . $this->pantalla . '", {
                    metodo:"validarSubfuente",
                    SF:$("#subfuente").val(),
                    F:$("#fuent option:selected").attr("fuente")
                });
                $(this).val($(this).val().toUpperCase());
            });

            $("#sfnombre").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#sfnombre").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function llenaTablaSubfuente()
    {
        $this->prm->fuente = isset($this->prm->fuente) ? $this->prm->fuente : "";
        $this->prm->clave = isset($this->prm->clave) ? $this->prm->clave : "";
        $params = [APP_EJERCICIO];
        $sqlExtra = "";
        if (!empty($this->prm->fuente)) {
            if (!empty($this->prm->clave)) {
                array_push($params, '%' . $this->prm->fuente . $this->prm->clave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } else {
                array_push($params, '%' . $this->prm->fuente . '%');
                $sqlExtra = "HAVING fuente LIKE ? ";
            }
        } elseif (!empty($this->prm->clave)) {
            array_push($params, '%' . $this->prm->clave . '%');
            $sqlExtra = "HAVING sft_id LIKE ? ";
        }

        $sql = "SELECT CONCAT(cff_id, fte_id) AS fuente, CONCAT(cff_id, fte_id, sft_id) AS llave,
                cff_id, fte_id, sft_id, sft_nombre
                FROM " . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . "
                WHERE sft_ejercicio = ?
                $sqlExtra
                ORDER BY llave ASC";
        $this->setSql($sql . "; ");
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaSubfuente";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".Editar").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"editaSubFuente",
                    llave:$(this).attr("llave"),
                    nombreSubFuente:$(this).attr("sft_nombre"),
                    fuenteSeleccionado:$("#fuent").val(),
                    Edit: "editar"
                });
            });
        ');
    }
    private function editaSubFuente()
    {
        $this->prm->llave = isset($this->prm->llave) ? $this->prm->llave : "";
        $this->prm->nombreSubFuente = isset($this->prm->nombreSubFuente) ? $this->prm->nombreSubFuente : "";
        $this->prm->Edit = (isset($this->prm->Edit)) ? $this->prm->Edit : "";
        $this->prm->fuenteSeleccionado = (isset($this->prm->fuenteSeleccionado)) ? $this->prm->fuenteSeleccionado : "";
        if (!$this->escritura) {
            $this->mixScript('notify("No tiene permitido editar las cuentas", "error");');
        }

        $btnRegresarBusqueda = new stdClass();
        $btnRegresarBusqueda->id = 'btnregresarbusqueda';
        $btnRegresarBusqueda->name = 'btnregresarbusqueda';
        $btnRegresarBusqueda->text = 'Regresar';
        $btnRegresarBusqueda->value = 'Regresar';
        $btnRegresarBusqueda->type = 'button';
        $btnRegresarBusqueda->color = 'gris';
        $btnRegresarBusqueda->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnRegresarBusqueda->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo:"altaSubfuente",
                fuenteSeleccionado: "' . $this->prm->fuenteSeleccionado . '"
            });
        ';

        $btnEditar = new stdClass();
        $btnEditar->id = 'editar';
        $btnEditar->name = 'editar';
        $btnEditar->text = 'Guardar';
        $btnEditar->value = 'Guardar';
        $btnEditar->type = 'button';
        $btnEditar->class = 'me-1';
        $btnEditar->color = 'verde';
        $btnEditar->icon = 'solid fa-floppy-disk me-2';
        $btnEditar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"actualizarSubfuente",
                sft_id:$("#clabeSF").val(),
                nombre:$("#nombreSubFuente").val(),
                fuenteSeleccionado: "' . $this->prm->fuenteSeleccionado . '"
            });';

        $vEditar = $this->prm->Edit;
        if ($vEditar == "editar") {
            $this->agregaTools($this->ponBoton($btnRegresarBusqueda));
            if ($this->escritura) {
                $this->agregaTools($this->ponBoton($btnEditar));
            }
        }

        $inputClavesFinanciamiento = new StdClass();
        $inputClavesFinanciamiento->id = 'clabeSF';
        $inputClavesFinanciamiento->name = 'clabeSF';
        $inputClavesFinanciamiento->value = $this->prm->llave;
        $inputClavesFinanciamiento->enDiv = true;
        $inputClavesFinanciamiento->label = 'Sub-Fuente';
        $inputClavesFinanciamiento->disabled = true;

        $inputNombreSubFuente = new StdClass();
        $inputNombreSubFuente->id = 'nombreSubFuente';
        $inputNombreSubFuente->name = 'nombreSubFuente';
        $inputNombreSubFuente->text = $this->prm->nombreSubFuente;
        $inputNombreSubFuente->enDiv = true;
        $inputNombreSubFuente->maxlength = 60;
        $inputNombreSubFuente->label = 'Nombre del Sub-Fuente';

        $html = '
            <fieldset>
                <legend>Editar</legend>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 " id="">
                        ' . $this->ponInput($inputClavesFinanciamiento) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="">
                        ' . $this->ponTextarea($inputNombreSubFuente) . '
                    </fieldset>
                </div>
            </fieldset>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarbusqueda").removeClass("btn-default");
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#nombreSubFuente").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#nombreSubFuente").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function actualizarSubfuente()
    {
        $this->prm->sft_id = (isset($this->prm->sft_id)) ? $this->prm->sft_id : "";
        $this->prm->nombre = (isset($this->prm->nombre)) ? $this->prm->nombre : "";
        $this->prm->fuenteSeleccionado = (isset($this->prm->fuenteSeleccionado)) ? $this->prm->fuenteSeleccionado : "";

        $cff_id = substr($this->prm->sft_id, -3, 1);
        $fte_id = substr($this->prm->sft_id, -2, 1);
        $sft_id = substr($this->prm->sft_id, -1);
        $cfi_ejercicio2 = APP_EJERCICIO;
        $params2 = [
            'cff_id' => $cff_id,
            'fte_id' => $fte_id,
            'sft_id' => $sft_id,
            'cfi_ejercicio2' => $cfi_ejercicio2,
        ];
        if ($this->prm->nombre == "") {
            $this->mixScript("notify('Falta el Nombre de la Sub-Fuente', 'warning')");
            return 0;
        }
        if ($this->existePresupuestalyDetalleMod()) {
            if (claveFinanciamientoAfectadaPorClc($params2) > 0) {
                $this->mixScript("notify('El nombre del Sub-Fuente no se puede cambiar porque ya esta afectada por una CLC.', 'warning')");
                return 0;
            } else {
                if (claveFinanciamientoAfectadaPorAdecuacion($params2) > 0) {
                    $this->mixScript('notify("El nombre de la Sub-Fuente no se puede cambiar porque ya esta afectada por un folio de adecuación presupuestal.", "warning");');
                    return 0;
                } else {
                    $this->prm2 = new stdClass();
                    $this->prm2->cff_id = $cff_id;
                    $this->prm2->fte_id = $fte_id;
                    $this->prm2->sft_id = $sft_id;
                    $this->prm2->nombre = $this->prm->nombre;
                    $this->prm2->ejercicio = APP_EJERCICIO;
                    $this->prm2->abc = 'c';
                    $this->updateSubfuente();
                }
            }
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = $cff_id;
            $this->prm2->fte_id = $fte_id;
            $this->prm2->sft_id = $sft_id;
            $this->prm2->nombre = $this->prm->nombre;
            $this->prm2->ejercicio = APP_EJERCICIO;
            $this->prm2->abc = 'c';
            $this->updateSubfuente();
        }
    }
    private function updateSubfuente()
    {
        Subfuente::abc($this->prm2);
        $this->mixScript("notify('Se ha actualizado la Sub-Fuente " . $this->prm->sft_id . ".', 'success');");
        return $this->altaSubfuente();
    }
    private function validarSubfuente()
    {
        $this->prm->SF = isset($this->prm->SF) ? $this->prm->SF : "";
        $this->prm->F = isset($this->prm->F) ? $this->prm->F : "";

        if ($this->prm->SF == "") {
            $this->mixScript('notify("Seleccione una Sub-Fuente", "warning")');
            return 0;
        }
        if ($this->prm->F == "") {
            $this->mixScript('notify("Seleccione una Fuente y vuelva a ingresar la Sub-Fuente", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->F, 0, 1),
            substr($this->prm->F, 1, 1),
            $this->prm->SF,
        ];
        $conexion->setSql("SELECT cff_id, fte_id, sft_id
            FROM " . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . "
            WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?;");
        $conexion->consultar($params);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La Sub-Fuente ya existe.', 'warning');");
            return 0;
        } else {
            $this->mixScript("notify('Sub-Fuente disponible.', 'warning');");
            return 0;
        }
    }
    private function guardarSubfuente()
    {
        $this->prm->SF = isset($this->prm->SF) ? $this->prm->SF : "";
        $this->prm->F = isset($this->prm->F) ? $this->prm->F : "";
        $this->prm->FE = isset($this->prm->FE) ? $this->prm->FE : "";
        $this->prm->FN = isset($this->prm->FN) ? $this->prm->FN : "";
        $this->prm->fuenteSeleccionado = $this->prm->F;

        if ($this->prm->F == "") {
            $this->mixScript('notify("Seleccione una Fuente y vuelva a ingresar la Sub-Fuente", "warning")');
            return 0;
        }
        if ($this->prm->SF == "") {
            $this->mixScript('notify("Ingresar la Sub-Fuente", "warning")');
            return 0;
        }
        if ($this->prm->FE == "") {
            $this->mixScript('notify("Ingresar el Ejercicio", "warning")');
            return 0;
        }
        if ($this->prm->FN == "") {
            $this->mixScript('notify("Ingresar el Nombre", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->F, 0, 1),
            substr($this->prm->F, 1, 1),
            $this->prm->SF,
        ];
        $conexion->setSql(" SELECT cff_id, fte_id, sft_id
            FROM " . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . "
            WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?;
        ");
        $conexion->consultar($params);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La Sub-Fuente ya existe.', 'warning')");
            return 0;
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = substr($this->prm->F, 0, 1);
            $this->prm2->fte_id = substr($this->prm->F, 1, 1);
            $this->prm2->sft_id = $this->prm->SF;
            $this->prm2->nombre = $this->prm->FN;
            $this->prm2->descripcion = "--";
            $this->prm2->ejercicio = $this->prm->FE;
            $this->prm2->clave = substr($this->prm->F, 0, 1) . substr($this->prm->F, 1, 1) . $this->prm->SF;
            $this->prm2->abc = "a";
            Subfuente::abc($this->prm2);
            $this->mixScript("notify('Se ha dado de alta la Sub-Fuente.', 'success');");
            $this->altaSubfuente();
        }
    }
    private function altaOrigen()
    {
        $this->prm->subfuenteSeleccionado = isset($this->prm->subfuenteSeleccionado) ? $this->prm->subfuenteSeleccionado : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "menuAltaCF"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnNuevoOrigen = new stdClass();
        $btnNuevoOrigen->id = 'btnNuevoorigen';
        $btnNuevoOrigen->name = 'btnNuevoorigen';
        $btnNuevoOrigen->text = 'Nuevo';
        $btnNuevoOrigen->value = 'Nuevo';
        $btnNuevoOrigen->type = 'button';
        $btnNuevoOrigen->class = 'me-1';
        $btnNuevoOrigen->color = 'azul';
        $btnNuevoOrigen->icon = 'solid fa-plus me-2';
        $btnNuevoOrigen->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "nuevoOrigen",
                subfuente: $("#subfuent").val(),
            });
        ';

        $btnPDF = new stdClass();
        $btnPDF->id = 'btnPrintPdf';
        $btnPDF->name = 'btnPrintPdf';
        $btnPDF->text = 'PDF';
        $btnPDF->value = 'PDF';
        $btnPDF->type = 'button';
        $btnPDF->class = 'me-1';
        $btnPDF->color = 'rojo';
        $btnPDF->icon = 'solid fa-file-pdf me-2';
        $btnPDF->click = '
            var form = $("<form>")
            .attr("action", "rep/doc/tes/catalogo/rep_ClaveFinanciamiento.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 2))
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#subfuent").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $btnExcel = new stdClass();
        $btnExcel->id = 'btnPrintExcel';
        $btnExcel->name = 'btnPrintExcel';
        $btnExcel->text = 'EXCEL';
        $btnExcel->value = 'Excel';
        $btnExcel->type = 'button';
        $btnExcel->class = 'me-1';
        $btnExcel->color = 'verde';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->click = '
            var form = $("<form>")
            .attr("action", "rep/excel/tes/catalogo/exp_ClaveFinanciamientoOrigen.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#subfuent").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $juntarBotones = '';
        if ($this->lectura && $this->escritura) {
            $juntarBotones = '' . $this->ponBoton($btnNuevoOrigen) . '' . $this->ponBoton($btnPDF) . ''
            . $this->ponBoton($btnExcel);
        } elseif ($this->lectura) {
            $juntarBotones = '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        }
        $this->agregaTools($juntarBotones);

        $inputOrigen = new StdClass();
        $inputOrigen->id = 'Origen';
        $inputOrigen->name = 'Origen';
        $inputOrigen->placeholder = 'Origen';
        $inputOrigen->enDiv = true;
        $inputOrigen->label = 'Origen';
        $inputOrigen->maxlength = 1;

        $inputOriNombre = new StdClass();
        $inputOriNombre->id = 'orinombre';
        $inputOriNombre->name = 'orinombre';
        $inputOriNombre->placeholder = 'Nombre Origen';
        $inputOriNombre->enDiv = true;
        $inputOriNombre->label = 'Nombre del Origen';
        $inputOriNombre->maxlength = 100;
        $inputOriNombre->rows = 2;
        $inputOriNombre->cols = 6;

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tablaOrigen';
        $tabla->titulo = 'Lista de Origen';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaOrigen';

        $tr = array();

        if ($this->lectura && $this->escritura) {
            $th = new stdClass();
            $th->texto = 'EDITAR';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'cff_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'fte_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'sft_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ori_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de Origen';
        array_push($tr, $th);

        $trbody = array();

        if ($this->lectura && $this->escritura) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-pencil';
            $td->class = 'Editar';
            $td->name = 'editar';
            $td->id = 'editar';
            $td->attr = new stdClass();
            $td->attr->llave = 'llave';
            $td->attr->ori_nombre = 'ori_nombre';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'cff_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fte_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sft_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ori_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ori_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $inputSubFuente = new StdClass();
        $inputSubFuente->id = 'subfuent';
        $inputSubFuente->name = 'subfuent';
        $inputSubFuente->enDiv = true;
        $inputSubFuente->label = 'Sub-Fuente';
        $params = [APP_EJERCICIO];
        $inputSubFuente->sql = "SELECT concat(cff_id, fte_id, sft_id) subfuente, sft_nombre FROM "
            . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . " WHERE sft_ejercicio = ?;";
        $inputSubFuente->params = $params;
        $inputSubFuente->camposVal = 'subfuente';
        $inputSubFuente->camposText = 'sft_nombre';
        $inputSubFuente->comoAtr = 'subfuente';
        $inputSubFuente->search = true;
        if (!empty($this->prm->subfuenteSeleccionado)) {
            $inputSubFuente->selecciona = $this->prm->subfuenteSeleccionado;
        }

        $inputBuscarClave = new StdClass();
        $inputBuscarClave->id = 'inputBuscarClave';
        $inputBuscarClave->name = 'inputBuscarClave';
        $inputBuscarClave->placeholder = 'Origen';
        $inputBuscarClave->enDiv = true;
        $inputBuscarClave->label = 'Origen';
        $inputBuscarClave->maxlength = 1;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'buscar';
        $btnBuscar->name = 'buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"llenaTablaOrigen",
                subfuente:$("#subfuent").val(),
                clave:$("#inputBuscarClave").val()
            });
        ';

        $this->mixHTML('
            <div class="row mb-4">
                <fieldset class="col-md-7 col-xs-7" id="">
                    ' . $this->ponCombo($inputSubFuente) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 " id="">
                    ' . $this->ponInput($inputBuscarClave) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1 mt-3" style="display: flex; justify-content: right;" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');

        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            if($("#subfuent").val() != ""){
                comunica("' . $this->pantalla . '", {
                    metodo:"llenaTablaOrigen",
                    subfuente:$("#subfuent").val(),
                    clave:$("#inputBuscarClave").val()
                });
            }
        ');
        $this->llenaTablaOrigen();
    }
    private function nuevoOrigen()
    {
        $this->prm->subfuente = isset($this->prm->subfuente) ? $this->prm->subfuente : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "altaOrigen",
                subfuenteSeleccionado: "' . $this->prm->subfuente . '"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnGuardarOrigen = new stdClass();
        $btnGuardarOrigen->id = 'btnguardarorigen';
        $btnGuardarOrigen->name = 'btnguardarorigen';
        $btnGuardarOrigen->text = 'Guardar';
        $btnGuardarOrigen->value = 'Guardar';
        $btnGuardarOrigen->type = 'button';
        $btnGuardarOrigen->class = 'me-1';
        $btnGuardarOrigen->color = 'verde';
        $btnGuardarOrigen->icon = 'solid fa-floppy-disk me-2';
        $btnGuardarOrigen->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "guardarOrigen",
                O: $("#Origen").val(),
                SF:$("#subfuent option:selected").attr("subfuente"),
                ON: $("#orinombre").val(),
                OE: ' . APP_EJERCICIO . '
            });
        ';
        $this->agregaTools('' . $this->ponBoton($btnGuardarOrigen));

        $inputSubFuente = new StdClass();
        $inputSubFuente->id = 'subfuent';
        $inputSubFuente->name = 'subfuent';
        $inputSubFuente->enDiv = true;
        $inputSubFuente->label = 'Sub-Fuente';
        $params = [APP_EJERCICIO];
        $inputSubFuente->sql = "SELECT concat(cff_id, fte_id, sft_id) subfuente, sft_nombre FROM " .
            BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . " WHERE sft_ejercicio = ? ";
        if (!empty($this->prm->subfuente)) {
            array_push($params, '%' . $this->prm->subfuente . '%');
            $inputSubFuente->sql .= "HAVING subfuente LIKE ?";
        }
        $inputSubFuente->params = $params;
        $inputSubFuente->camposVal = "subfuente";
        $inputSubFuente->camposText = "sft_nombre";
        $inputSubFuente->comoAtr = "subfuente";
        $inputSubFuente->search = true;

        $inputOrigen = new StdClass();
        $inputOrigen->id = 'Origen';
        $inputOrigen->name = 'Origen';
        $inputOrigen->placeholder = 'Origen';
        $inputOrigen->enDiv = true;
        $inputOrigen->label = 'Origen';
        $inputOrigen->maxlength = 1;

        $inputOriNombre = new StdClass();
        $inputOriNombre->id = 'orinombre';
        $inputOriNombre->name = 'orinombre';
        $inputOriNombre->placeholder = 'Nombre Origen';
        $inputOriNombre->enDiv = true;
        $inputOriNombre->label = 'Nombre del Origen';
        $inputOriNombre->maxlength = 100;
        $inputOriNombre->rows = 2;
        $inputOriNombre->cols = 6;

        $this->mixHTML('
            <div class="row">
                <fieldset class="col-md-4 col-xs-4 mb-4" id="">
                    ' . $this->ponCombo($inputSubFuente) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-2 col-xs-2 " id="">
                    ' . $this->ponInput($inputOrigen) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6 " id="">
                    ' . $this->ponTextarea($inputOriNombre) . '
                </fieldset>
            </div>
        ');

        $this->mixScript('
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#btnregresarindex").removeClass("btn-default");
            $("#Origen").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8 || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                if (keynum == 95)
                    return false;
                if (/\d/.test(String.fromCharCode(keynum)))
                    return false;
                return /\w/.test(String.fromCharCode(keynum));
            });

            $("#Origen").keyup(function() {
                comunica("' . $this->pantalla . '", {
                    metodo:"validarOrigen",
                    O: $("#Origen").val(),
                    SF:$("#subfuent option:selected").attr("subfuente")
                });
                $(this).val($(this).val().toUpperCase());
            });

            $("#orinombre").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#orinombre").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function llenaTablaOrigen()
    {
        $this->prm->subfuente = isset($this->prm->subfuente) ? $this->prm->subfuente : "";
        $this->prm->clave = isset($this->prm->clave) ? $this->prm->clave : "";

        $params = [APP_EJERCICIO];
        $sqlExtra = "";
        if (!empty($this->prm->subfuente)) {
            if (!empty($this->prm->clave)) {
                array_push($params, '%' . $this->prm->subfuente . $this->prm->clave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } else {
                array_push($params, '%' . $this->prm->subfuente . '%');
                $sqlExtra = "HAVING subfuente LIKE ? ";
            }
        } elseif (!empty($this->prm->clave)) {
            array_push($params, '%' . $this->prm->clave . '%');
            $sqlExtra = "HAVING ori_id LIKE ? ";
        }

        $sql = "SELECT CONCAT(cff_id, fte_id, sft_id) AS subfuente, CONCAT(cff_id, fte_id, sft_id, ori_id) AS llave,
                cff_id, fte_id, sft_id, ori_id, ori_nombre
                FROM " . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . "
                WHERE ori_ejercicio = ?
                $sqlExtra
                ORDER BY llave ASC";
        $this->setSql($sql . "; ");
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaOrigen";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".Editar").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"editaOrigen",
                    llave: $(this).attr("llave"),
                    nombreOrigen: $(this).attr("ori_nombre"),
                    subfuenteSeleccionado: $("#subfuent").val(),
                    Edit: "editar"
                });
            });
        ');
    }
    private function editaOrigen()
    {
        $this->prm->llave = isset($this->prm->llave) ? $this->prm->llave : "";
        $this->prm->nombreOrigen = isset($this->prm->nombreOrigen) ? $this->prm->nombreOrigen : "";
        $this->prm->Edit = (isset($this->prm->Edit)) ? $this->prm->Edit : "";
        $this->prm->subfuenteSeleccionado = (isset($this->prm->subfuenteSeleccionado)) ? $this->prm->subfuenteSeleccionado : "";

        if (!$this->escritura) {
            $this->mixScript('notify("No tiene permitido editar las cuentas", "error");');
        }

        $btnRegresarBusqueda = new stdClass();
        $btnRegresarBusqueda->id = 'btnregresarbusqueda';
        $btnRegresarBusqueda->name = 'btnregresarbusqueda';
        $btnRegresarBusqueda->text = 'Regresar';
        $btnRegresarBusqueda->value = 'Regresar';
        $btnRegresarBusqueda->type = 'button';
        $btnRegresarBusqueda->color = 'gris';
        $btnRegresarBusqueda->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnRegresarBusqueda->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo:"altaOrigen",
                subfuenteSeleccionado: "' . $this->prm->subfuenteSeleccionado . '"
            });
        ';

        $btnEditar = new stdClass();
        $btnEditar->id = 'editar';
        $btnEditar->name = 'editar';
        $btnEditar->text = 'Guardar';
        $btnEditar->value = 'Guardar';
        $btnEditar->type = 'button';
        $btnEditar->class = 'me-1';
        $btnEditar->color = 'verde';
        $btnEditar->icon = 'solid fa-pencil me-2';
        $btnEditar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"actualizarOrigen",
                ori_id:$("#clabeO").val(),
                nombre:$("#NombreOrigen").val(),
                subfuenteSeleccionado: "' . $this->prm->subfuenteSeleccionado . '"
            });';

        $vEditar = $this->prm->Edit;
        if ($vEditar == "editar") {
            $this->agregaTools($this->ponBoton($btnRegresarBusqueda));
            if ($this->escritura) {
                $this->agregaTools($this->ponBoton($btnEditar));
            }
        }

        $inputClavesFinanciamiento = new StdClass();
        $inputClavesFinanciamiento->id = 'clabeO';
        $inputClavesFinanciamiento->name = 'clabeO';
        $inputClavesFinanciamiento->value = $this->prm->llave;
        $inputClavesFinanciamiento->enDiv = true;
        $inputClavesFinanciamiento->label = 'Origen';
        $inputClavesFinanciamiento->disabled = true;

        $inputNombreSubOrigen = new StdClass();
        $inputNombreSubOrigen->id = 'NombreOrigen';
        $inputNombreSubOrigen->name = 'NombreOrigen';
        $inputNombreSubOrigen->text = $this->prm->nombreOrigen;
        $inputNombreSubOrigen->enDiv = true;
        $inputNombreSubOrigen->maxlength = 100;
        $inputNombreSubOrigen->label = 'Nombre del Origen';

        $html = '
            <fieldset>
                <legend>Editar</legend>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 " id="">
                        ' . $this->ponInput($inputClavesFinanciamiento) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="">
                        ' . $this->ponTextarea($inputNombreSubOrigen) . '
                    </fieldset>
                </div>
            </fieldset>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarbusqueda").removeClass("btn-default");
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#NombreOrigen").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#NombreOrigen").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function actualizarOrigen()
    {
        $this->prm->ori_id = (isset($this->prm->ori_id)) ? $this->prm->ori_id : "";
        $this->prm->nombre = (isset($this->prm->nombre)) ? $this->prm->nombre : "";
        $this->prm->subfuenteSeleccionado = (isset($this->prm->subfuenteSeleccionado)) ? $this->prm->subfuenteSeleccionado : "";

        $cff_id = substr($this->prm->ori_id, -4, 1);
        $fte_id = substr($this->prm->ori_id, -3, 1);
        $sft_id = substr($this->prm->ori_id, -2, 1);
        $ori_id = substr($this->prm->ori_id, -1);
        $cfi_ejercicio2 = APP_EJERCICIO;
        $params2 = [
            'cff_id' => $cff_id,
            'fte_id' => $fte_id,
            'sft_id' => $sft_id,
            'ori_id' => $ori_id,
            'cfi_ejercicio2' => $cfi_ejercicio2,
        ];

        if ($this->prm->nombre == "") {
            $this->mixScript('notify("Falta el Nombre del Origen", "warning")');
            return 0;
        }

        if ($this->existePresupuestalyDetalleMod()) {
            if (claveFinanciamientoAfectadaPorClc($params2) > 0) {
                $this->mixScript('notify("El nombre del Origen no se puede cambiar porque ya esta afectada por una CLC.", "warning")');
                return 0;
            } else {
                if (claveFinanciamientoAfectadaPorAdecuacion($params2) > 0) {
                    $this->mixScript('notify("El nombre del Origen no se puede cambiar porque ya esta afectada por un folio de adecuación presupuestal.", "warning")');
                    return 0;
                } else {
                    $this->prm2 = new stdClass();
                    $this->prm2->cff_id = $cff_id;
                    $this->prm2->fte_id = $fte_id;
                    $this->prm2->sft_id = $sft_id;
                    $this->prm2->ori_id = $ori_id;
                    $this->prm2->nombre = $this->prm->nombre;
                    $this->prm2->ejercicio = APP_EJERCICIO;
                    $this->prm2->abc = 'c';
                    $this->updateOrigen();
                }
            }
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = $cff_id;
            $this->prm2->fte_id = $fte_id;
            $this->prm2->sft_id = $sft_id;
            $this->prm2->ori_id = $ori_id;
            $this->prm2->nombre = $this->prm->nombre;
            $this->prm2->ejercicio = APP_EJERCICIO;
            $this->prm2->abc = 'c';
            $this->updateOrigen();
        }
    }
    private function updateOrigen()
    {
        OrigenRecurso::abc($this->prm2);
        $this->mixScript("notify('Se ha actualizado el Origen " . $this->prm->ori_id . ".', 'success');");
        return $this->altaOrigen();

    }
    private function validarOrigen()
    {
        $this->prm->SF = isset($this->prm->SF) ? $this->prm->SF : "";
        $this->prm->O = isset($this->prm->O) ? $this->prm->O : "";

        if ($this->prm->O == "") {
            $this->mixScript('notify("Seleccione un Origen", "warning")');
            return 0;
        }
        if ($this->prm->SF == "") {
            $this->mixScript('notify("Seleccione una Sub-Fuente y vuelva a ingresar el Origen", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->SF, 0, 1),
            substr($this->prm->SF, 1, 1),
            substr($this->prm->SF, 2, 1),
            $this->prm->O,
        ];
        $conexion->setSql(" SELECT cff_id, fte_id, sft_id, ori_id
            FROM " . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . "
            WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?
                AND ori_id = ? ;");
        $conexion->consultar($params);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('El Origen ya existe.', 'warning');");
            return 0;
        } else {
            $this->mixScript("notify('Origen disponible.', 'warning');");
        }
    }
    private function guardarOrigen()
    {
        $this->prm->SF = isset($this->prm->SF) ? $this->prm->SF : "";
        $this->prm->O = isset($this->prm->O) ? $this->prm->O : "";
        $this->prm->OE = isset($this->prm->OE) ? $this->prm->OE : "";
        $this->prm->ON = isset($this->prm->ON) ? $this->prm->ON : "";
        $this->prm->subfuenteSeleccionado = $this->prm->SF;

        if ($this->prm->SF == "") {
            $this->mixScript('notify("Seleccione una Sub-Fuente", "warning")');
            return 0;
        }
        if ($this->prm->O == "") {
            $this->mixScript('notify("Ingresar el Origen", "warning")');
            return 0;
        }
        if ($this->prm->OE == "") {
            $this->mixScript('notify("Ingresar el Ejercicio", "warning")');
            return 0;
        }
        if ($this->prm->ON == "") {
            $this->mixScript('notify("Ingresar el Nombre", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->SF, 0, 1),
            substr($this->prm->SF, 1, 1),
            substr($this->prm->SF, 2, 1),
            $this->prm->O,
        ];
        $conexion->setSql(" SELECT cff_id, fte_id, sft_id, ori_id
            FROM " . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . "
            WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?
                AND ori_id = ?;
        ");
        $conexion->consultar($params);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('El Origen ya existe.', 'warning')");
            return 0;
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = substr($this->prm->SF, 0, 1);
            $this->prm2->fte_id = substr($this->prm->SF, 1, 1);
            $this->prm2->sft_id = substr($this->prm->SF, 2, 1);
            $this->prm2->ori_id = $this->prm->O;
            $this->prm2->nombre = $this->prm->ON;
            $this->prm2->descripcion = "--";
            $this->prm2->ejercicio = $this->prm->OE;
            $this->prm2->clave = substr($this->prm->SF, 0, 1) . substr($this->prm->SF, 1, 1) . substr($this->prm->SF, 2, 1) . $this->prm->O;
            $this->prm2->abc = 'a';
            OrigenRecurso::abc($this->prm2);
            $this->mixScript("notify('Se ha dado de alta el Origen.', 'success');");
            $this->altaOrigen();
        }
    }
    private function altaSubOrigen()
    {
        $this->prm->origenSeleccionado = isset($this->prm->origenSeleccionado) ? $this->prm->origenSeleccionado : "";
        $this->prm->tipoRecursoSeleccionado = isset($this->prm->tipoRecursoSeleccionado) ? $this->prm->tipoRecursoSeleccionado : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "menuAltaCF"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnNuevoSubOrigen = new stdClass();
        $btnNuevoSubOrigen->id = 'btnNuevosuborigen';
        $btnNuevoSubOrigen->name = 'btnNuevosuborigen';
        $btnNuevoSubOrigen->text = 'Nuevo';
        $btnNuevoSubOrigen->value = 'Nuevo';
        $btnNuevoSubOrigen->type = 'button';
        $btnNuevoSubOrigen->class = 'me-1';
        $btnNuevoSubOrigen->color = 'azul';
        $btnNuevoSubOrigen->icon = 'solid fa-plus me-2';
        $btnNuevoSubOrigen->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "nuevoSubOrigen",
                origen: $("#origenR").val(),
                tipoRecurso: $("#Rec").val(),
            });
        ';

        $btnPDF = new stdClass();
        $btnPDF->id = 'btnPrintPdf';
        $btnPDF->name = 'btnPrintPdf';
        $btnPDF->text = 'PDF';
        $btnPDF->value = 'PDF';
        $btnPDF->type = 'button';
        $btnPDF->class = 'me-1';
        $btnPDF->color = 'rojo';
        $btnPDF->icon = 'solid fa-file-pdf me-2';
        $btnPDF->click = '
            var form = $("<form>")
            .attr("action", "rep/doc/tes/catalogo/rep_ClaveFinanciamiento.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 3))
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#origenR").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "tipoRecurso").attr("value", $("#Rec").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $btnExcel = new stdClass();
        $btnExcel->id = 'btnPrintExcel';
        $btnExcel->name = 'btnPrintExcel';
        $btnExcel->text = 'EXCEL';
        $btnExcel->value = 'Excel';
        $btnExcel->type = 'button';
        $btnExcel->class = 'me-1';
        $btnExcel->color = 'verde';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->click = '
            var form = $("<form>")
            .attr("action", "rep/excel/tes/catalogo/exp_ClaveFinanciamientoSubOrigen.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "primeraParteClave").attr("value", $("#origenR").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "tipoRecurso").attr("value", $("#Rec").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "segundaParteClave").attr("value", $("#inputBuscarClave").val()))
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $juntarBotones = '';
        if ($this->lectura && $this->escritura) {
            $juntarBotones = '' . $this->ponBoton($btnNuevoSubOrigen) . '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        } elseif ($this->lectura) {
            $juntarBotones = '' . $this->ponBoton($btnPDF) . '' . $this->ponBoton($btnExcel);
        }
        $this->agregaTools($juntarBotones);

        $inputOrigenRecurso = new StdClass();
        $inputOrigenRecurso->id = 'origenR';
        $inputOrigenRecurso->name = 'origenR';
        $inputOrigenRecurso->enDiv = true;
        $inputOrigenRecurso->label = 'Origen';
        $params = [APP_EJERCICIO];
        $inputOrigenRecurso->sql = "SELECT concat(cff_id, fte_id, sft_id, ori_id) origen, ori_nombre FROM "
            . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . " WHERE ori_ejercicio = ?;";
        $inputOrigenRecurso->params = $params;
        $inputOrigenRecurso->camposVal = 'origen';
        $inputOrigenRecurso->camposText = 'ori_nombre';
        $inputOrigenRecurso->comoAtr = 'origen';
        $inputOrigenRecurso->search = true;
        if (!empty($this->prm->origenSeleccionado)) {
            $inputOrigenRecurso->selecciona = $this->prm->origenSeleccionado;
        }

        $inputRecurso = new StdClass();
        $inputRecurso->id = 'Rec';
        $inputRecurso->name = 'Rec';
        $inputRecurso->enDiv = true;
        $inputRecurso->label = 'Tipo de Recurso';
        $inputRecurso->sql = "SELECT ter_id, ter_nombre FROM " . BD_SINPRES . ".cat_TipoRecurso;";
        $inputRecurso->camposVal = 'ter_id';
        $inputRecurso->camposText = 'ter_nombre';
        $inputRecurso->comoAtr = 'ter_id';
        $inputRecurso->search = true;
        if (!empty($this->prm->tipoRecursoSeleccionado)) {
            $inputRecurso->selecciona = $this->prm->tipoRecursoSeleccionado;
        }

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tablaSubOrigen';
        $tabla->titulo = 'Lista de Sub-Origen';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaSubOrigen';

        $tr = array();

        if ($this->lectura && $this->escritura) {
            $th = new stdClass();
            $th->texto = 'EDITAR';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'cff_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'fte_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'sft_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ori_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'sor_id';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Tipo de recurso';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de Sub-Origen';
        array_push($tr, $th);

        $trbody = array();

        if ($this->lectura && $this->escritura) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-pencil';
            $td->class = 'Editar';
            $td->name = 'editar';
            $td->id = 'editar';
            $td->attr = new stdClass();
            $td->attr->llave = 'llave';
            $td->attr->sor_nombre = 'sor_nombre';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'cff_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fte_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sft_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ori_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sor_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ter_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sor_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $inputBuscarClave = new StdClass();
        $inputBuscarClave->id = 'inputBuscarClave';
        $inputBuscarClave->name = 'inputBuscarClave';
        $inputBuscarClave->placeholder = 'Sub-Origen';
        $inputBuscarClave->enDiv = true;
        $inputBuscarClave->label = 'Sub-Origen';
        $inputBuscarClave->maxlength = 1;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'buscar';
        $btnBuscar->name = 'buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"llenaTablaSubOrigen",
                origen:$("#origenR").val(),
                tipoRecurso:$("#Rec").val(),
                clave:$("#inputBuscarClave").val()
            });
        ';

        $this->mixHTML('
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4" id="">
                    ' . $this->ponCombo($inputOrigenRecurso) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 " id="">
                    ' . $this->ponCombo($inputRecurso) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 " id="">
                    ' . $this->ponInput($inputBuscarClave) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1 mt-3" style="display: flex; justify-content: right;" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');

        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            if($("#origenR").val()!=""){
                comunica("' . $this->pantalla . '", {
                    metodo:"llenaTablaSubOrigen",
                    origen:$("#origenR").val(),
                    tipoRecurso:$("#Rec").val(),
                    clave:$("#inputBuscarClave").val()
                });
            }
        ');
        $this->llenaTablaSubOrigen();
    }
    private function nuevoSubOrigen()
    {
        $this->prm->origen = isset($this->prm->origen) ? $this->prm->origen : "";
        $this->prm->tipoRecurso = isset($this->prm->tipoRecurso) ? $this->prm->tipoRecurso : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "altaSubOrigen",
                origenSeleccionado: "' . $this->prm->origen . '",
                tipoRecursoSeleccionado: "' . $this->prm->tipoRecurso . '"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnGuardarSubOrigen = new stdClass();
        $btnGuardarSubOrigen->id = 'btnguardarsuborigen';
        $btnGuardarSubOrigen->name = 'btnguardarsuborigen';
        $btnGuardarSubOrigen->text = 'Guardar';
        $btnGuardarSubOrigen->value = 'Guardar';
        $btnGuardarSubOrigen->type = 'button';
        $btnGuardarSubOrigen->class = 'me-1';
        $btnGuardarSubOrigen->color = 'verde';
        $btnGuardarSubOrigen->icon = 'solid fa-floppy-disk me-2';
        $btnGuardarSubOrigen->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "guardarSubOrigen",
                SO: $("#suborigen").val(),
                O: $("#origenR option:selected").attr("origen"),
                SON: $("#suborinombre").val(),
                SOE: ' . APP_EJERCICIO . ',
                SOTR: $("#Rec option:selected").attr("ter_id")
            });
        ';
        $this->agregaTools('' . $this->ponBoton($btnGuardarSubOrigen));

        $inputOrigenRecurso = new StdClass();
        $inputOrigenRecurso->id = 'origenR';
        $inputOrigenRecurso->name = 'origenR';
        $inputOrigenRecurso->enDiv = true;
        $inputOrigenRecurso->label = 'Origen';
        $params = [APP_EJERCICIO];
        $inputOrigenRecurso->sql = "SELECT concat(cff_id, fte_id, sft_id, ori_id) origen, ori_nombre FROM " .
            BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . " WHERE ori_ejercicio = ? ";
        if (!empty($this->prm->origen)) {
            array_push($params, '%' . $this->prm->origen . '%');
            $inputOrigenRecurso->sql .= "HAVING origen LIKE ?";
        }
        $inputOrigenRecurso->params = $params;
        $inputOrigenRecurso->camposVal = 'origen';
        $inputOrigenRecurso->camposText = 'ori_nombre';
        $inputOrigenRecurso->comoAtr = 'origen';
        $inputOrigenRecurso->search = true;

        $inputRecurso = new StdClass();
        $inputRecurso->id = 'Rec';
        $inputRecurso->name = 'Rec';
        $inputRecurso->enDiv = true;
        $inputRecurso->label = 'Tipo de Recurso';
        $params = [];
        $inputRecurso->sql = "SELECT ter_id, ter_nombre FROM " . BD_SINPRES . ".cat_TipoRecurso ";
        if (!empty($this->prm->tipoRecurso)) {
            array_push($params, '%' . $this->prm->tipoRecurso . '%');
            $inputRecurso->sql .= " WHERE ter_id LIKE ?";
            $inputRecurso->params = $params;
        }
        $inputRecurso->camposVal = 'ter_id';
        $inputRecurso->camposText = 'ter_nombre';
        $inputRecurso->comoAtr = 'ter_id';
        $inputRecurso->search = true;

        $inputSubOrigen = new StdClass();
        $inputSubOrigen->id = 'suborigen';
        $inputSubOrigen->name = 'suborigen';
        $inputSubOrigen->placeholder = 'Sub-Origen';
        $inputSubOrigen->enDiv = true;
        $inputSubOrigen->label = 'Sub-Origen';
        $inputSubOrigen->maxlength = 1;

        $inputSubOriNombre = new StdClass();
        $inputSubOriNombre->id = 'suborinombre';
        $inputSubOriNombre->name = 'suborinombre';
        $inputSubOriNombre->placeholder = 'Nombre del Sub-Origen';
        $inputSubOriNombre->enDiv = true;
        $inputSubOriNombre->label = 'Nombre del Sub-Origen';
        $inputSubOriNombre->maxlength = 100;
        $inputSubOriNombre->rows = 2;
        $inputSubOriNombre->cols = 5;

        $this->mixHTML('
            <div class="row">
                <fieldset class="col-md-4 col-xs-4 mb-4" id="">
                    ' . $this->ponCombo($inputOrigenRecurso) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-5 col-xs-5 " id="">
                    ' . $this->ponCombo($inputRecurso) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2 " id="">
                    ' . $this->ponInput($inputSubOrigen) . '
                </fieldset>
                <fieldset class="col-md-5 col-xs-5 " id="">
                    ' . $this->ponTextarea($inputSubOriNombre) . '
                </fieldset>
            </div>
        ');

        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#suborigen").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8 || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                if (keynum == 95)
                    return false;
                if (/\d/.test(String.fromCharCode(keynum)))
                    return false;
                return /\w/.test(String.fromCharCode(keynum));
            });

            $("#suborigen").keyup(function() {
                comunica("' . $this->pantalla . '", {
                    metodo:"validarSubOrigen",
                    SO: $("#suborigen").val(),
                    O:$("#origenR option:selected").attr("origen")
                });
                $(this).val($(this).val().toUpperCase());
            });

            $("#suborinombre").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0)) {
                    return true;
                }
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#suborinombre").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function llenaTablaSubOrigen()
    {
        $this->prm->origen = isset($this->prm->origen) ? $this->prm->origen : "";
        $this->prm->clave = isset($this->prm->clave) ? $this->prm->clave : "";
        $this->prm->tipoRecurso = isset($this->prm->tipoRecurso) ? $this->prm->tipoRecurso : "";

        $params = [APP_EJERCICIO];
        $sqlExtra = "";
        if (!empty($this->prm->origen)) {
            if (!empty($this->prm->clave)) {
                array_push($params, '%' . $this->prm->origen . $this->prm->clave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } else {
                array_push($params, '%' . $this->prm->origen . '%');
                $sqlExtra = "HAVING origen LIKE ? ";
            }
        } elseif (!empty($this->prm->clave)) {
            array_push($params, '%' . $this->prm->clave . '%');
            $sqlExtra = "HAVING sor_id LIKE ? ";
        }
        if (!empty($this->prm->tipoRecurso)) {
            $sqlExtra .= (empty($sqlExtra)) ? 'HAVING ' : 'AND ';
            $sqlExtra .= "ter_id = ? ";
            if ($this->prm->tipoRecurso == "A") {
                array_push($params, 'ESTATAL');
            } else {
                array_push($params, 'FEDERAL');
            }
        }

        $sql = " SELECT CONCAT(cff_id, fte_id, sft_id, ori_id) AS origen,
            CONCAT(cff_id, fte_id, sft_id, ori_id, sor_id) AS llave, cff_id, fte_id, sft_id, ori_id, sor_id,
            IF(ter_id = 'A', 'ESTATAL', 'FEDERAL') AS ter_id, sor_nombre
            FROM " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . "
            WHERE sor_ejercicio = ?
            $sqlExtra
            ORDER BY llave ASC";
        $this->setSql($sql . "; ");
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaSubOrigen";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".Editar").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"editaSubOrigen",
                    llave: $(this).attr("llave"),
                    nombreSubOrigen: $(this).attr("sor_nombre"),
                    origenSeleccionado: $("#origenR").val(),
                    tipoRecursoSeleccionado: $("#Rec").val(),
                    Edit: "editar"
                });
            });
        ');
    }
    private function editaSubOrigen()
    {
        $this->prm->llave = isset($this->prm->llave) ? $this->prm->llave : "";
        $this->prm->nombreSubOrigen = isset($this->prm->nombreSubOrigen) ? $this->prm->nombreSubOrigen : "";
        $this->prm->Edit = (isset($this->prm->Edit)) ? $this->prm->Edit : "";
        $this->prm->origenSeleccionado = (isset($this->prm->origenSeleccionado)) ? $this->prm->origenSeleccionado : "";
        $this->prm->tipoRecursoSeleccionado = (isset($this->prm->tipoRecursoSeleccionado)) ? $this->prm->tipoRecursoSeleccionado : "";
        if (!$this->escritura) {
            $this->mixScript('notify("No tiene permitido editar las cuentas", "error");');
        }

        $btnRegresarBusqueda = new stdClass();
        $btnRegresarBusqueda->id = 'btnregresarbusqueda';
        $btnRegresarBusqueda->name = 'btnregresarbusqueda';
        $btnRegresarBusqueda->text = 'Regresar';
        $btnRegresarBusqueda->value = 'Regresar';
        $btnRegresarBusqueda->type = 'button';
        $btnRegresarBusqueda->color = 'gris';
        $btnRegresarBusqueda->icon = CIRCLE;
        // Regresa a la pantalla anterior
        $btnRegresarBusqueda->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo:"altaSubOrigen",
                origenSeleccionado: "' . $this->prm->origenSeleccionado . '",
                tipoRecursoSeleccionado: "' . $this->prm->tipoRecursoSeleccionado . '"
            });
        ';

        $btnEditar = new stdClass();
        $btnEditar->id = 'editar';
        $btnEditar->name = 'editar';
        $btnEditar->text = 'Guardar';
        $btnEditar->value = 'Guardar';
        $btnEditar->type = 'button';
        $btnEditar->class = 'me-1';
        $btnEditar->color = 'verde';
        $btnEditar->icon = 'solid fa-floppy-disk me-2';
        $btnEditar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"actualizarSubOrigen",
                sor_id:$("#clabeCF").val(),
                nombre:$("#NombreSubOrigen").val(),
                origenSeleccionado: "' . $this->prm->origenSeleccionado . '",
                tipoRecursoSeleccionado: "' . $this->prm->tipoRecursoSeleccionado . '"
            });';

        $vEditar = $this->prm->Edit;
        if ($vEditar == "editar") {
            $this->agregaTools($this->ponBoton($btnRegresarBusqueda));
            if ($this->escritura) {
                $this->agregaTools($this->ponBoton($btnEditar));
            }
        }

        $inputClavesFinanciamiento = new StdClass();
        $inputClavesFinanciamiento->id = 'clabeCF';
        $inputClavesFinanciamiento->name = 'clabeCF';
        $inputClavesFinanciamiento->value = $this->prm->llave;
        $inputClavesFinanciamiento->enDiv = true;
        $inputClavesFinanciamiento->label = 'Sub-Origen';
        $inputClavesFinanciamiento->disabled = true;

        $inputNombreSubOrigen = new StdClass();
        $inputNombreSubOrigen->id = 'NombreSubOrigen';
        $inputNombreSubOrigen->name = 'NombreSubOrigen';
        $inputNombreSubOrigen->text = $this->prm->nombreSubOrigen;
        $inputNombreSubOrigen->enDiv = true;
        $inputNombreSubOrigen->maxlength = 100;
        $inputNombreSubOrigen->label = 'Nombre del Sub-Origen';

        $html = '
            <fieldset>
                <legend>Editar</legend>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 mb-4" id="">
                        ' . $this->ponInput($inputClavesFinanciamiento) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="">
                        ' . $this->ponTextarea($inputNombreSubOrigen) . '
                    </fieldset>
                </div>
            </fieldset>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarbusqueda").removeClass("btn-default");
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ,:;.()\u00f1\u00d1\d]/;
            $("#NombreSubOrigen").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#NombreSubOrigen").keyup(function(e) {
                $(this).val($(this).val().toUpperCase());
            });
        ');
    }
    private function actualizarSubOrigen()
    {
        $this->prm->sor_id = (isset($this->prm->sor_id)) ? $this->prm->sor_id : "";
        $this->prm->nombre = (isset($this->prm->nombre)) ? $this->prm->nombre : "";

        $cff_id = substr($this->prm->sor_id, -5, 1);
        $fte_id = substr($this->prm->sor_id, -4, 1);
        $sft_id = substr($this->prm->sor_id, -3, 1);
        $ori_id = substr($this->prm->sor_id, -2, 1);
        $sor_id = substr($this->prm->sor_id, -1);
        $cfi_ejercicio2 = APP_EJERCICIO;
        $params2 = [
            'cff_id' => $cff_id,
            'fte_id' => $fte_id,
            'sft_id' => $sft_id,
            'ori_id' => $ori_id,
            'sor_id' => $sor_id,
            'cfi_ejercicio2' => $cfi_ejercicio2,
        ];

        if ($this->prm->nombre == "") {
            $this->mixScript("notify('Falta el Nombre del Sub-Origen', 'warning')");
            return 0;
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();
        if ($this->existePresupuestalyDetalleMod()) {
            if (claveFinanciamientoAfectadaPorClc($params2) > 0) {
                $this->mixScript("notify('El nombre del Sub-Origen no se puede cambiar porque ya esta afectada por una CLC.', 'warning')");
                return 0;
            } else {
                if (claveFinanciamientoAfectadaPorAdecuacion($params2) > 0) {
                    $this->mixScript("notify('El nombre del Sub-Origen no se puede cambiar porque ya esta afectada por un folio de adecuación presupuestal.', 'warning')");
                    return 0;
                } else {
                    $this->prm2 = new stdClass();
                    $this->prm2->cff_id = $cff_id;
                    $this->prm2->fte_id = $fte_id;
                    $this->prm2->sft_id = $sft_id;
                    $this->prm2->ori_id = $ori_id;
                    $this->prm2->sor_id = $sor_id;
                    $this->prm2->nombre = $this->prm->nombre;
                    $this->prm2->ejercicio = APP_EJERCICIO;
                    $this->prm2->abc = 'c';
                    $this->updateSubOrigen();
                }
            }
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = $cff_id;
            $this->prm2->fte_id = $fte_id;
            $this->prm2->sft_id = $sft_id;
            $this->prm2->ori_id = $ori_id;
            $this->prm2->sor_id = $sor_id;
            $this->prm2->nombre = $this->prm->nombre;
            $this->prm2->ejercicio = APP_EJERCICIO;
            $this->prm2->abc = 'c';
            $this->updateSubOrigen();
        }
    }
    private function updateSubOrigen()
    {
        SuborigenRecurso::abc($this->prm2);
        $this->mixScript("notify('Se ha actualizado el Sub-Origen " . $this->prm->sor_id . ".', 'success');");
        return $this->altaSubOrigen();
    }
    private function validarSubOrigen()
    {
        $this->prm->SO = isset($this->prm->SO) ? $this->prm->SO : "";
        $this->prm->O = isset($this->prm->O) ? $this->prm->O : "";

        if ($this->prm->SO == "") {
            $this->mixScript('notify("Seleccione un Sub-Origen", "warning")');
            return 0;
        }
        if ($this->prm->O == "") {
            $this->mixScript('notify("Seleccione un Origen y vuelva a ingresar el Sub-Origen", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->O, 0, 1),
            substr($this->prm->O, 1, 1),
            substr($this->prm->O, 2, 1),
            substr($this->prm->O, 3, 1),
            $this->prm->SO,
        ];
        $conexion->setSql(" SELECT cff_id, fte_id, sft_id, ori_id, sor_id FROM " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . "
                WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?
                AND ori_id = ?
                AND sor_id = ?;");
        $conexion->consultar($params);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('El Sub-Origen ya existe.', 'warning');");
            return 0;
        } else {
            $this->mixScript("notify('Sub-Origen disponible.', 'warning');");
        }
    }
    private function guardarSubOrigen()
    {
        $this->prm->SO = isset($this->prm->SO) ? $this->prm->SO : "";
        $this->prm->O = isset($this->prm->O) ? $this->prm->O : "";
        $this->prm->SOE = isset($this->prm->SOE) ? $this->prm->SOE : "";
        $this->prm->SON = isset($this->prm->SON) ? $this->prm->SON : "";
        $this->prm->SOTR = isset($this->prm->SOTR) ? $this->prm->SOTR : "";
        $this->prm->origenSeleccionado = $this->prm->O;
        $this->prm->tipoRecursoSeleccionado = $this->prm->SOTR;

        if ($this->prm->O == "") {
            $this->mixScript('notify("Seleccione un Origen", "warning")');
            return 0;
        }
        if ($this->prm->SOTR == "") {
            $this->mixScript('notify("Seleccione un Tipo de Recurso", "warning")');
            return 0;
        }
        if ($this->prm->SO == "") {
            $this->mixScript('notify("Ingresar el Sub-Origen", "warning")');
            return 0;
        }
        if ($this->prm->SOE == "") {
            $this->mixScript('notify("Ingresar el Ejercicio", "warning")');
            return 0;
        }
        if ($this->prm->SON == "") {
            $this->mixScript('notify("Ingresar el Nombre", "warning")');
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [
            substr($this->prm->O, 0, 1),
            substr($this->prm->O, 1, 1),
            substr($this->prm->O, 2, 1),
            substr($this->prm->O, 3, 1),
            $this->prm->SO,
        ];
        $conexion->setSql(" SELECT cff_id, fte_id, sft_id, ori_id, sor_id
            FROM " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . "
            WHERE cff_id = ?
                AND fte_id = ?
                AND sft_id = ?
                AND ori_id = ?
                AND sor_id = ?;
        ");
        $conexion->consultar($params);
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('El Sub-Origen ya existe.', 'warning')");
            return 0;
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cff_id = substr($this->prm->O, 0, 1);
            $this->prm2->fte_id = substr($this->prm->O, 1, 1);
            $this->prm2->sft_id = substr($this->prm->O, 2, 1);
            $this->prm2->ori_id = substr($this->prm->O, 3, 1);
            $this->prm2->sor_id = $this->prm->SO;
            $this->prm2->nombre = $this->prm->SON;
            $this->prm2->descripcion = "--";
            $this->prm2->ejercicio = $this->prm->SOE;
            $this->prm2->recurso = $this->prm->SOTR;
            $this->prm2->clave = substr($this->prm->O, 0, 1) . substr($this->prm->O, 1, 1) . substr($this->prm->O, 2, 1) . substr($this->prm->O, 3, 1) . $this->prm->SO;
            $this->prm2->abc = 'a';
            SuborigenRecurso::abc($this->prm2);
            $this->mixScript("notify('Se ha dado de alta el Sub-Origen.', 'success');");
            $this->altaSubOrigen();
        }
    }
    public function existePresupuestalyDetalleMod()
    {
        $sql = 'SELECT ' . BD_SINPRES . '.fn_tableExists("scp_Presupuestal","' . BD_CTRL . '") AS existeScpPresupuestal, '
            . BD_SINPRES . '.fn_tableExists("scp_DetalleModificacion","' . BD_CTRL . '") AS existeScpDetalleModificacion';
        $this->setSql($sql);
        $this->conectar();
        $this->consultar();
        $tablas = $this->extraer();
        return ($tablas["existeScpPresupuestal"] == 1 && $tablas["existeScpDetalleModificacion"] == 1);
    }
}

if (tienePermiso("T.6.3.1") || tienePermiso("S.6.4.1") || tienePermiso("C.2.11.1")) {
    new ControladorClaveFinanciamiento();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
