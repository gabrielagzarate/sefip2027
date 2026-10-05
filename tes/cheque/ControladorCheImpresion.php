<?php

/**
 * Description of Controlador
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 5-Nov-2018
 * Update: 14-Nov-2022
 * Comment: -----
 * Objective: Impresión de cheques
 * Regla de Negocio: -----
 * Permits: validación
 **/

session_start();
require_once dirname(__FILE__) . "/../../lib/constantes.php";
require_once dirname(__FILE__) . '/../../lib/funciones.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/tes/getTipoMinistracion.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_AutorizarMinistrarClc.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_CancelarMinistrarClc.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_AutorizarMinistrarDocumento.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_CancelarMinistrarDocumento.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_AutorizarMinistrarClc2024.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_CancelarMinistrarClc2024.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_AutorizarMinistrarClc2025.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_CancelarMinistrarClc2025.php';

class ControladorCheImpresion extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "tes/cheque/ControladorCheImpresion.php";
        switch ($this->metodo) {
            case 'getPropuesta':
                $this->getPropuesta(1);
                break;
            case 'getPropuestaMunis':
                $this->getPropuesta(4);
                break;
            case 'getPropuestaManual':
                $this->getPropuesta(2);
                break;
            case 'getPropuestaImss':
                $this->getPropuesta(3);
                break;
            case 'imprimirCheque':
                $this->imprimirCheque();
                break;
            case 'getMunicipios':
                $this->getMunicipios();
                break;
            case 'registrarChequeImpreso':
                $this->registrarChequeImpreso();
                break;
            case 'modalCancelarCheque':
                $this->modalCancelarCheque();
                break;
            case 'registrarCancelacion':
                $this->registrarCancelacion();
                break;
            case 'enviarABanca':
                $this->enviarABanca();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $btnImprimir = new stdClass();
        $btnImprimir->id = 'btnImprimir';
        $btnImprimir->name = 'btnImprimir';
        $btnImprimir->text = 'Imprimir Programacion';
        $btnImprimir->value = 'Imprimir Programacion';
        $btnImprimir->color = 'verde';
        $btnImprimir->icon = "print me-2";
        $btnImprimir->click = '
            $("#modalBuscarPropuesta").modal("show");
            $("#txtProp").focus();
        ';

        $this->agregaTools($this->ponBoton($btnImprimir));

        $this->mixHtml('
            <div id="modalBuscarPropuesta" class="modal"  role="dialog" data-backdrop="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Buscar programación</h4>
                            <button type="button" class="close btnCancelarBusqueda btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </div>
                        <div class="modal-body">
                            <fieldset>
                                 <label for="txtProp">Programacion: </label>
                                 <input id="txtProp" type="text" class="form-control" value="" placeholder="Propuesta de CLC">
                            </fieldset>
                            <br>
                            <div id="divPropManual" style="display:none">
                                <fieldset>
                                    <label for="txtPropManual">Propuesta Manual: </label>
                                    <input id="txtPropManual" type="text" class="form-control" value="" placeholder="Propuesta Manual">
                                </fieldset>
                                <br>
                            </div>
                            <fieldset>
                                 <label for="cbxAnio">Año: </label>
                                 <select id="cbxAnio" type="text" class="form-control" value="" >
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <!--<option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                    <option value="2021">2021</option>
                                    <option value="2020">2020</option>
                                    <option value="2019">2019</option>
                                    <option value="2018">2018</option>
                                    <option value="2017">2017</option>
                                    <option value="2016">2016</option>
                                    <option value="2015">2015</option>
                                    <option value="2014">2014</option>
                                    <option value="2013">2013</option> -->
                                </select>
                            </fieldset>
                            <br>
                            <fieldset>
                                 <input id="chkImss" type="checkbox" value="imss"> IMSS</input>
                                 <input id="chkMunis" type="checkbox" value="munis"> Municipios</input>
                                 <input id="chkManual" type="checkbox" value="manual"> Manual</input>
                            </fieldset>
                            <br>
                        </div>
                        <div class="modal-footer">
                            <button id="btnAceptarBusqueda" type="button" class="btn btn-outline-success">Aceptar</button>
                            <button type="button" class="btn btn-outline-danger btnCancelarBusqueda">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->mixScript('
            $(".btnCancelarBusqueda").click(function() {
                $("#modalBuscarPropuesta").modal("hide");
            });

            $("#chkImss").click(function() {
                if ($(this).is(":checked")) {
                    $("#divPropManual").show("fast");
                    $("#chkMunis").prop("checked", false);
                    $("#chkManual").prop("checked", false);
                }
                else {
                    $("#divPropManual").hide("fast");
                }
            });
            $("#chkMunis").click(function() {
                if ($(this).is(":checked")) {
                    $("#divPropManual").hide("fast");
                    $("#chkImss").prop("checked", false);
                    $("#chkManual").prop("checked", false);
                }
            });
            $("#chkManual").click(function() {
                if ($(this).is(":checked")) {
                    $("#divPropManual").hide("fast");
                    $("#chkImss").prop("checked", false);
                    $("#chkMunis").prop("checked", false);
                }
            });
            $("#btnAceptarBusqueda").click(function() {
                if ($("#txtProp").val() != "") {
                    if ($("#chkImss").is(":checked")) {
                        if ($("#txtPropManual").val() == "") {
                            notify("Introduzca propuesta manual", "warning");

                            return false;
                        }
                        metodo = "getPropuestaImss";
                    }
                    else if ($("#chkMunis").is(":checked")) {
                        metodo = "getPropuestaMunis";
                    }
                    else if ($("#chkManual").is(":checked")) {
                        metodo = "getPropuestaManual";
                    }
                    else {
                        metodo = "getPropuesta";
                    }

                    $("#modalBuscarPropuesta").modal("hide");
                    comunica("' . $this->pantalla . '", {
                            metodo: metodo,
                            propuesta: $("#txtProp").val(),
                            propManual: $("#txtPropManual").val(),
                            anio: $("#cbxAnio").val()
                    });
                }
                else {
                    notify("Introduzca un número de propuesta", "warning");
                }
            });
        ');
    }

    private function getPropuesta($tipo)
    {
        $this->prm->propuesta = strtoupper($this->prm->propuesta);
        if (isset($this->prm->propManual)) {
            $this->prm->propManual = strtoupper($this->prm->propManual);
        }

        if ($this->prm->anio == 2017) {
            $this->mixScript('
                notify("LA IMPRESIÓN DE CHEQUES DE 2017 ESTA EN CONSTRUCCIÓN", "warning");
            ');
            return false;
        }

        $res = $this->verificarEstadoLote($this->prm->propuesta);
        if (strlen($res) > 0) {
            $this->mixScript('
                notify("' . $res . '", "warning");
            ');
            return false;
        }
        if (isset($this->prm->propManual)) {
            $res = $this->verificarEstadoLote($this->prm->propManual);
            if (strlen($res) > 0) {
                $this->mixScript('
                    notify("' . $res . '", "warning");
                ');
                return false;
            }
        }

        switch ($tipo) {
            case 2: //Manual
                $sql = $this->consultaManual2026($this->prm->propuesta);
                break;
            case 3: //IMSS
                $importeManual = $this->getImporteManual($this->prm->propManual);
                if ($importeManual == null) {
                    return;
                }

                switch ($this->prm->anio) {
                    case '2017':
                        $sql = $this->consultaImss2017($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2018':
                        $sql = $this->consultaImss2018($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2019':
                        $sql = $this->consultaImss2019($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2020':
                        $sql = $this->consultaImss2020($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2021':
                        $sql = $this->consultaImss2021($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2022':
                        $sql = $this->consultaImss2022($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2023':
                        $sql = $this->consultaImss2023($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2024':
                        $sql = $this->consultaImss2024($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2025':
                        $sql = $this->consultaImss2025($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                    case '2026':
                        $sql = $this->consultaImss2026($this->prm->propuesta, $this->prm->propManual, $importeManual);
                        break;
                }
                break;
            case 1: //Normal
            case 4: //Municipios
                switch ($this->prm->anio) {
                    case '2017':
                        $sql = $this->consulta2017($this->prm->propuesta, $tipo);
                        break;
                    case '2018':
                        $sql = $this->consulta2018($this->prm->propuesta, $tipo);
                        break;
                    case '2019':
                        $sql = $this->consulta2019($this->prm->propuesta, $tipo);
                        break;
                    case '2020':
                        $sql = $this->consulta2020($this->prm->propuesta, $tipo);
                        break;
                    case '2021':
                        $sql = $this->consulta2021($this->prm->propuesta, $tipo);
                        break;
                    case '2022':
                        $sql = $this->consulta2022($this->prm->propuesta, $tipo);
                        break;
                    case '2023':
                        $sql = $this->consulta2023($this->prm->propuesta, $tipo);
                        break;
                    case '2024':
                        $sql = $this->consulta2024($this->prm->propuesta, $tipo);
                        break;
                    case '2025':
                        $sql = $this->consulta2025($this->prm->propuesta, $tipo);
                        break;
                    case '2026':
                        $sql = $this->consulta2026($this->prm->propuesta, $tipo);
                        break;
                }
                break;
        }
        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->color = "gris";
        $btnRegresar->icon = "circle-left me-2";
        $btnRegresar->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '");
        ';

        $btnEnviar = new stdClass();
        $btnEnviar->id = 'btnEnviar';
        $btnEnviar->name = 'btnEnviar';
        $btnEnviar->text = 'Enviar a Banca';
        $btnEnviar->value = 'Enviar a Banca';
        $btnEnviar->type = 'button';
        $btnEnviar->color = "cielo";
        $btnEnviar->icon = "paper-plane me-2";
        $btnEnviar->click = '
            alertify.confirm(
                "<h5> Enviar a Banca </h5>",
                "¿Confirma que desea enviar el lote a Banca?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "enviarABanca",
                        lote: "' . $this->prm->propuesta . '"
                    });
                },
                function(){},
            ).set("defaultFocus", "cancel").set("labels", {ok:"Enviar", cancel:"Cancelar"});
        ';

        $this->agregaTools($this->ponBoton($btnRegresar));

        $tabla = $this->generarTabla($tipo);
        $tabla->titulo = 'Ordenes de la Propuesta: ' . $this->prm->propuesta;
        $this->mixHtml('
            <div id="alertaImss" class="alert alert-warning alert-dismissable" align="center" style="display:none">
                <strong>Este registro corresponde a la SUMA DE determinadas ORDENES, ¡Verifique que sea REALMENTE una programación para el IMSS!</strong>
            </div>
        ');
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixHtml('
            <div><label id="lblNumRegistros"></label></div>
            <div id="divModalMunicipios"></div>
            <div id="divModalImprimirCheque"></div>
            <div id="divModalCancelarCheque"></div>
        ');
        $this->mixScript('
            $("#tablaOrdenes .tabla-footer").hide();
        ');
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaOrdenes";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : -1;
        $numRegs = $this->wrLlenaTabla($nav);

        if ($numRegs > 0) {
            if ($tipo == 4) {
                $this->agregaTools($this->ponBoton($btnEnviar));
            }

            $this->mixScript('
                $("#lblNumRegistros").html("' . $numRegs . ' registro' . ($numRegs == 1 ? '' : 's') . '");
            ');

            switch ($tipo) {
                case 1: //Normal
                    $this->mixScript('
                        $(document).off("click", ".imprimirCheque");
                        $(document).on("click", ".imprimirCheque", function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "imprimirCheque",
                                tipo: 1,
                                id: $(this).attr("id"),
                                anio: ' . $this->prm->anio . ',
                                orden: $(this).attr("orden"),
                                clabe: $(this).attr("clabe"),
                                lote: $(this).attr("lote"),
                                gpo: $(this).attr("gpo"),
                                ur: $(this).attr("ur"),
                                clc: $(this).attr("clc"),
                                importe: $(this).attr("importe"),
                                concepto: $(this).attr("concepto"),
                                benef: $(this).attr("benef")
                            });
                        });

                        $(document).off("click", ".cancelarCheque");
                        $(document).on("click", ".cancelarCheque", function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "modalCancelarCheque",
                                tipo: 1,
                                id: $(this).attr("id"),
                                anio: ' . $this->prm->anio . ',
                                lote: $(this).attr("lote"),
                                gpo: $(this).attr("gpo"),
                                ur: $(this).attr("ur"),
                                clc: $(this).attr("clc"),
                                clabe: $(this).attr("clabe"),
                                cheque: $(this).attr("cheque"),
                                chequera: $(this).attr("chequera"),
                            });
                        });
                    ');
                    break;
                case 2: //Manual
                    $this->mixScript('
                        $(document).off("click", ".imprimirCheque");
                        $(document).on("click", ".imprimirCheque", function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "imprimirCheque",
                                tipo: 2,
                                id: $(this).attr("id"),
                                anio: ' . $this->prm->anio . ',
                                orden: $(this).attr("orden"),
                                clabe: $(this).attr("clabe"),
                                lote: $(this).attr("lote"),
                                importe: $(this).attr("importe"),
                                concepto: $(this).attr("concepto"),
                                benef: $(this).attr("benef")
                            });
                        });

                        $(document).off("click", ".cancelarCheque");
                        $(document).on("click", ".cancelarCheque", function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "modalCancelarCheque",
                                tipo: 2,
                                id: $(this).attr("id"),
                                anio: ' . $this->prm->anio . ',
                                lote: $(this).attr("lote"),
                                clabe: $(this).attr("clabe"),
                                cheque: $(this).attr("cheque"),
                                chequera: $(this).attr("chequera")
                            });
                        });
                    ');
                    break;
                case 3: //IMSS
                    $this->mixScript('
                        $("#alertaImss").show();
                        $(document).off("click", ".imprimirCheque");
                        $(document).on("click", ".imprimirCheque", function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "imprimirCheque",
                                tipo: 3,
                                id: $(this).attr("id"),
                                anio: ' . $this->prm->anio . ',
                                programacion: $(this).attr("programacion"),
                                manual: $(this).attr("manual"),
                                clabe: $(this).attr("clabe"),
                                importe: $(this).attr("importe"),
                                concepto: $(this).attr("concepto"),
                                benef: $(this).attr("benef")
                            });
                        });

                        $(document).off("click", ".cancelarCheque");
                        $(document).on("click", ".cancelarCheque", function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "modalCancelarCheque",
                                tipo: 3,
                                id: $(this).attr("id"),
                                anio: ' . $this->prm->anio . ',
                                clabe: $(this).attr("clabe"),
                                cheque: $(this).attr("cheque"),
                                chequera: $(this).attr("chequera"),
                                programacion: $(this).attr("programacion"),
                                manual: $(this).attr("manual")
                            });
                        });
                    ');
                    break;
                case 4: //Municipios
                    $this->mixScript('
                        $(document).off("click", ".imprimirCheque");
                        $(document).on("click", ".imprimirCheque", function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "getMunicipios",
                                tipo: 4,
                                anio: ' . $this->prm->anio . ',
                                orden: $(this).attr("orden"),
                                clabe: $(this).attr("clabe"),
                                lote: $(this).attr("lote"),
                                gpo: $(this).attr("gpo"),
                                ur: $(this).attr("ur"),
                                clc: $(this).attr("clc"),
                                importe: $(this).attr("importe"),
                                concepto: $(this).attr("concepto"),
                                benef: $(this).attr("benef")
                            });
                        });
                    ');
                    break;
            }
        } else {
            $this->mixScript('
                notify("No existen registros asociados a la propuesta ' . $this->prm->propuesta . '", "warning");
            ');
        }
    }

    private function generarTabla($tipo)
    {
        $tabla = new stdClass();
        $tabla->id = 'tablaOrdenes';

        if ($tipo == 3) { //Si es de tipo IMSS las columnas cambian ligeramente
            $tr = array();
            $th = new stdClass();
            $th->texto = 'Programacion';
            $th->width = '5%';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'Manual';
            $th->width = '5%';
            array_push($tr, $th);
        } else {
            $tr = array();
            $th = new stdClass();
            $th->texto = 'Tipo';
            $th->width = '5%';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'Orden';
            $th->width = '5%';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'Importe';
        $th->align = 'right';
        $th->width = '10%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Cuenta Cargo';
        $th->width = '10%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Concepto';
        $th->width = '30%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Beneficiario';
        $th->width = '30%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Banco';
        $th->width = '6%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'B1';
        $th->width = '4%';
        array_push($tr, $th);

        $trbody = array();

        if ($tipo == 3) {
            $td = new stdClass();
            $td->campoTxt = 'programacion';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'manual';
            array_push($trbody, $td);
        } else {
            $td = new stdClass();
            $td->campoTxt = 'tipo';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'orden';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ctaban_mostrar';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'benef';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'banco';
        $td->casos->cases = array();

        $caso = new stdClass();
        $caso->valor = '002';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/banamex.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '012';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/bancomer.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '014';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/santander.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '021';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/hsbc.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '037';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/interacciones.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '044';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/scotia.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '072';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/banorte.png';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '132';
        $caso->tipo = 'img';
        $caso->img = 'img/bancos/multiva.png';
        array_push($td->casos->cases, $caso);

        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'puedeImprimirse';
        if ($tipo == 4) { //Si es de tipo Municipios siempre debe mostrar el boton imprimir
            $td->casos->valor = '0,1';
        } else {
            $td->casos->valor = '1';
        }

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'botoncito';
        $td->casos->verdadero->icono = "print";
        $td->casos->verdadero->class = "btn-outline-success imprimirCheque";
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->id = "id";
        $td->casos->verdadero->attr->clabe = "ctaban_cargo";
        if ($tipo == 3) {
            $td->casos->verdadero->attr->programacion = "programacion";
            $td->casos->verdadero->attr->manual = "manual";
        } else {
            $td->casos->verdadero->attr->orden = "orden";
            $td->casos->verdadero->attr->lote = "tra_lote";
            $td->casos->verdadero->attr->gpo = "gpo";
            $td->casos->verdadero->attr->ur = "ur";
            $td->casos->verdadero->attr->clc = "clc";
        }
        $td->casos->verdadero->attr->importe = "importe";
        $td->casos->verdadero->attr->concepto = "concepto";
        $td->casos->verdadero->attr->benef = "benef";
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'botoncito';
        $td->casos->falso->icono = "times";
        $td->casos->falso->class = "btn-outline-danger cancelarCheque";
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->id = "id";
        $td->casos->falso->attr->cheque = "chq_id";
        $td->casos->falso->attr->chequera = "chr_id";
        $td->casos->falso->attr->clabe = "ctaban_cargo";
        if ($tipo == 3) {
            $td->casos->falso->attr->programacion = "programacion";
            $td->casos->falso->attr->manual = "manual";
        } else {
            $td->casos->falso->attr->orden = "orden";
            $td->casos->falso->attr->lote = "tra_lote";
            $td->casos->falso->attr->gpo = "gpo";
            $td->casos->falso->attr->ur = "ur";
            $td->casos->falso->attr->clc = "clc";
        }
        $td->casos->falso->attr->importe = "importe";
        $td->casos->falso->attr->concepto = "concepto";
        $td->casos->falso->attr->benef = "benef";
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        return $tabla;
    }

    private function generarTablaMunicipios()
    {
        $tabla = new stdClass();
        $tabla->id = 'tablaMunicipios';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'No. Mun.';
        $th->width = '15%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Municipio';
        $th->width = '55%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Importe';
        $th->align = 'right';
        $th->width = '20%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = '';
        $th->width = '10%';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'mun_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mun_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'imp';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'puedeImprimirse';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'botoncito';
        $td->casos->verdadero->icono = "print";
        $td->casos->verdadero->class = "btn-outline-success imprimirChequeMuni";
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->id = "id";
        $td->casos->verdadero->attr->orden = "orden";
        $td->casos->verdadero->attr->impMuni = "imp";
        $td->casos->verdadero->attr->concepMuni = "concep";
        $td->casos->verdadero->attr->nombreMuni = "mun_nombre";
        $td->casos->verdadero->attr->bdo = "bdo_id";
        $td->casos->verdadero->attr->afe = "afe_id";
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'botoncito';
        $td->casos->falso->icono = "remove";
        $td->casos->falso->class = "btn-outline-danger cancelarChequeMuni";
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->id = "id";
        $td->casos->falso->attr->cheque = "chq_id";
        $td->casos->falso->attr->chequera = "chr_id";
        $td->casos->falso->attr->orden = "orden";
        $td->casos->falso->attr->impMuni = "imp";
        $td->casos->falso->attr->concepMuni = "concep";
        $td->casos->falso->attr->nombreMuni = "mun_nombre";
        $td->casos->falso->attr->bdo = "bdo_id";
        $td->casos->falso->attr->afe = "afe_id";

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        return $tabla;
    }

    private function getMunicipios()
    {
        switch ($this->prm->anio) {
            case '2017':
                $sql = $this->consultaMunicipios2017($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2018':
                $sql = $this->consultaMunicipios2018($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2019':
                $sql = $this->consultaMunicipios2019($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2020':
                $sql = $this->consultaMunicipios2020($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2021':
                $sql = $this->consultaMunicipios2021($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2022':
                $sql = $this->consultaMunicipios2022($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2023':
                $sql = $this->consultaMunicipios2023($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2024':
                $sql = $this->consultaMunicipios2024($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2025':
                $sql = $this->consultaMunicipios2025($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
            case '2026':
                $sql = $this->consultaMunicipios2026($this->prm->gpo, $this->prm->ur, $this->prm->clc);
                break;
        }

        $tabla = $this->generarTablaMunicipios();
        $tabla->titulo = 'Municipios:';

        $this->setContenedor('divModalMunicipios');

        $this->mixHtml('
            <div id="modalMunicipios" class="modal fade in"  role="dialog" data-backdrop="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Datos para impresión</h4>
                            <button type="button" class="close btnCancelarMunis btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </div>
                        <div class="modal-body">
                            ' . $this->wrPonTabla($tabla) . '
                            <div><label id="lblNumRegistrosMunis"></label></div>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaMunicipios";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : -1;
        $numRegs = $this->wrLlenaTabla($nav);

        $this->mixScript('
            $("#modalMunicipios").modal("show");
            $("#tablaMunicipios .tabla-footer").hide();
            $("#lblNumRegistrosMunis").html("' . $numRegs . ' registro' . ($numRegs == 1 ? '' : 's') . '");

            $(".btnCancelarMunis").click(function () {
                $("#modalMunicipios").modal("hide");
            });

            $(document).off("click", ".imprimirChequeMuni");
            $(document).on("click", ".imprimirChequeMuni", function () {
                comunica("' . $this->pantalla . '", {
                    metodo: "imprimirCheque",
                    tipo: 4,
                    id: $(this).attr("id"),
                    anio: ' . $this->prm->anio . ',
                    orden: $(this).attr("orden"),
                    clabe: "' . $this->prm->clabe . '",
                    lote: "' . $this->prm->lote . '",
                    gpo: ' . $this->prm->gpo . ',
                    ur: "' . $this->prm->ur . '",
                    clc: ' . $this->prm->clc . ',
                    importeClc: ' . $this->prm->importe . ',
                    afe: $(this).attr("afe"),
                    bdo: $(this).attr("bdo"),
                    importe: $(this).attr("impMuni"),
                    concepto: $(this).attr("concepMuni"),
                    benef: $(this).attr("nombreMuni")
                });
            });

            $(document).off("click", ".cancelarChequeMuni");
            $(document).on("click", ".cancelarChequeMuni", function () {
                comunica("' . $this->pantalla . '", {
                    metodo: "modalCancelarCheque",
                    tipo: 4,
                    id: $(this).attr("id"),
                    anio: ' . $this->prm->anio . ',
                    clabe: "' . $this->prm->clabe . '",
                    cheque: $(this).attr("cheque"),
                    chequera: $(this).attr("chequera"),
                    gpo: ' . $this->prm->gpo . ',
                    ur: "' . $this->prm->ur . '",
                    clc: ' . $this->prm->clc . ',
                    afe: $(this).attr("afe"),
                    bdo: $(this).attr("bdo")
                });
            });
        ');
    }

    private function imprimirCheque()
    {
        $imprimeCheque = false;
        $chequeraAsociada = false;
        $this->conectar();
        $this->setSql('
            SELECT cbs_id, chr_numeroActual, chr_folioFinal, chr_id FROM ' . BD_TESORERIA . '.chq_Chequera
            WHERE eca_id = 1 AND cbs_id = ? LIMIT 1;
        ');
        $this->consultar([$this->prm->clabe]);
        $row = $this->extraer();
        $this->desconectar();
        //Valida que hayan números de cheques disponibles
        if ($this->getNumeroRegistros() > 0) {
            $chequeraAsociada = true;
            $numeroActualCh = $row['chr_numeroActual'];
            $numeroFinalCh = $row['chr_folioFinal'];
            if ($numeroActualCh <= $numeroFinalCh) {
                $imprimeCheque = true;
            }
        }

        if ($this->getNumeroRegistros() > 0 && $imprimeCheque) {
            $chequera = $row['chr_id'];
            $cheque = $row['chr_numeroActual'];

            $this->setContenedor('divModalImprimirCheque');
            $this->mixHtml('
                <div id="modalImprimirCheque" class="modal fade in"  role="dialog" data-backdrop="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Datos para impresión</h4>
                                <button type="button" class="close btnCancelarImpChq btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </div>
                            <div class="modal-body">
                                <fieldset>
                                     <label for="txtNumCta">Numero de Cuenta: </label>
                                     <input id="txtNumCta" type="text" class="form-control" disabled="true" value="' .
                substr($row['cbs_id'], 6, 11) . '">
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="txtNumCheque">Numero de Cheque: </label>
                                    <input id="txtNumCheque" type="text" class="form-control" disabled="true" value="' .
                $row['chr_numeroActual'] . '">
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="txtImporteCheque">Importe del Cheque: </label>
                                    <input id="txtImporteCheque" type="text" class="form-control" disabled="true" value="$ ' .
                number_format($this->prm->importe, 2) . '">
                                </fieldset>
                                <br>
                            </div>
                            <div class="modal-footer">
                                <button id="btnImprimirCheque" type="button" class="btn btn-outline-primary">Imprimir</button>
                                <button type="button" class="btn btn-outline-danger btnCancelarImpChq">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            ');

            if ($this->prm->tipo == 3) { //Si es de tipo IMSS concatenar la propuestas
                $this->prm->orden = $this->prm->programacion . ',' . $this->prm->manual;
            } else if ($this->prm->tipo == 1 || $this->prm->tipo == 4) {
                $this->prm->orden = $this->prm->clc . '-' . $this->prm->orden;
            }
            $this->prm->lote =  isset($this->prm->lote) ? $this->prm->lote : "";
            $this->mixScript('
                $("#modalImprimirCheque").modal("show");

                $(".btnCancelarImpChq").click(function () {
                    $("#modalImprimirCheque").modal("hide");
                });

                $("#btnImprimirCheque").click(function () {

                    $("#page-loader").removeClass("hide");
                    callbackComunica = function() {
                        $("#page-loader").addClass("hide");
                    }

                    comunica("' . $this->pantalla . '", {
                        metodo: "registrarChequeImpreso",
                        orden: "' . $this->prm->orden . '",
                        concepto: "' . str_replace(array("\n", "\r", "\t", "\""), '', $this->prm->concepto) . '",
                        loteMunicipio: "' . $this->prm->lote . '",
                        benef: "' . $this->prm->benef . '",
                        tipo: ' . $this->prm->tipo . ',
                        id: "' . $this->prm->id . '",
                        anio: ' . $this->prm->anio . ',
                        clabe: "' . $this->prm->clabe . '",
                        chequera: ' . $chequera . ',
                        cheque: ' . $cheque . ',
                        importe: ' . $this->prm->importe . ($this->prm->tipo == 4 || $this->prm->tipo == 1 ? ',
                        gpo: ' . $this->prm->gpo . ',
                        ur: "' . $this->prm->ur . '",
                        clc: ' . $this->prm->clc : '') . ($this->prm->tipo == 4 ? ',
                        bdo: ' . $this->prm->bdo . ',
                        afe: ' . $this->prm->afe . ',
                        importeClc: ' . $this->prm->importeClc : '') . ($this->prm->tipo == 3 ? ',
                        programacion: "' . $this->prm->programacion . '",
                        manual: "' . $this->prm->manual . '"' : '') . ($this->prm->tipo == 2 || $this->prm->tipo == 1 ? ',
                        lote: "' . $this->prm->lote . '"' : '') . '
                    });
                });
            ');
        }
        if (!$chequeraAsociada) {
            $this->mixScript('
                notify("No existe una chequera asociada a la cuenta bancaria ' .
                substr($this->prm->clabe, 6, 11) . ', no es posible imprimir cheques.");
            ');
        }
        if ($chequeraAsociada && !$imprimeCheque) {
            $this->mixScript('
                notify("No existe número de cheque disponible para la cuenta bancaria ' .
                substr($this->prm->clabe, 6, 11) . ', no es posible imprimir cheques.");
            ');
        }
    }

    private function registrarChequeImpreso()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $this->conectar();
        $this->setSql('SET @usr_ip = INET_ATON(?);');
        $this->consultar([$ip]);
        $this->setSql('SET @usr_id = ? ;');
        $this->consultar([$_SESSION[_S]['usr_id']]);
        $this->setSql('SET @bit_observacion = "CHEQUE REGISTRADO DESDE IMPRESION.";');
        $this->consultar();

        switch ($this->prm->tipo) {
            case 1: //Normal
                switch ($this->prm->anio) {
                    case 2017:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 12 WHERE ecl_id = 11 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2017"
                        );
                        $this->consultar();
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2017);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2017::call($datosSP);

                        break;
                    case 2018:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2018"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2018);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2018::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2019:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2019"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2019);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2019::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2020:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2020"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2020);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2020::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2021:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2021"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2021);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2021::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2022:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2022"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2022);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2022::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2023:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2023"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2023);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2023::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2024:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2024"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2024);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2024::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2025:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc . " AND ejr_id_clc=2025"
                        );

                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2025);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc2025::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                    case 2026:
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 16 WHERE ecl_id = 15 AND gpo_id = " . $this->prm->gpo . "
                            AND ur_id = " . $this->prm->ur . " AND clc_id = " . $this->prm->clc
                        );
                        $this->consultar();

                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->tdf_id = 'CH';
                        $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                        $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                        $tmi = new getTipoMinistracion();
                        $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, 2026);
                        $datosSP->tmi_id = $tmiRes->tmi_id;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_AutorizarMinistrarClc::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        break;
                }
                break;
            case 2: //Manual
                $params = ['lom' => substr($this->prm->id, 7)];
                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_PropuestaManual SET elo_id = 8
                    WHERE lom_id = :lom ;
                ");
                $this->consultar($params);
                break;
            case 3: //IMSS
                $lotes = $this->ponerComillas($this->prm->programacion);
                switch ($this->prm->anio) {
                    case 2017:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 12
                            WHERE CL.ecl_id = 11 AND CL.ejr_id_clc=2017 CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2017 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();
                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2017);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2017::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2018:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2018
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2018);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2018::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2019:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2019
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2019 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2019);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2019::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2020:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2020
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2020 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2020);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2020::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2021:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2021
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2021 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2021);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2021::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2022:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2022
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2022 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2022);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2022::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2023:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2023
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2023);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2023::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2024:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2024
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2024);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2024::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2025:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ANT USING (gpo_id, ur_id, clc_id)
                            SET ANT.ecl_id = 16
                            WHERE ANT.ecl_id = 15 AND ANT.ejr_id_clc=2025
                            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], 2025);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc2025::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2026:
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar CA USING (gpo_id, ur_id, clc_id)
                            SET CA.ecl_id = 16
                            WHERE CA.ecl_id = 15 AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND LO.lot_ejr = " . APP_EJERCICIO . ";
                        ");
                        $this->consultar();

                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ecl_id = 16;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->tdf_id = 'CH';
                            $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                            $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                            $tmi = new getTipoMinistracion();
                            $tmiRes = $tmi->validaTMI($row['gpo_id'], $row['ur_id'], $row['clc_id'], APP_EJERCICIO);
                            $datosSP->tmi_id = $tmiRes->tmi_id;
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'MINISTRACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_AutorizarMinistrarClc::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                }

                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 8
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($lotes)
                    AND lot_ejr = " . APP_EJERCICIO . ";
                ");
                $this->consultar();

                $manuales = $this->ponerComillas($this->prm->manual);
                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_PropuestaManual SET elo_id = 8
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($manuales);
                ");
                $this->consultar();
                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 8
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($manuales)
                    AND lot_ejr = " . APP_EJERCICIO . ";
                ");
                $this->consultar();
                break;
            case 4: //Municipios
                if ($this->prm->anio == APP_EJERCICIO) {

                    $params = [
                        'gpo' => $this->prm->gpo,
                        'ur' => $this->prm->ur,
                        'clc' => $this->prm->clc,
                    ];

                    //Obtiene el número de documentos y el número de documentos ministrados
                    $this->setSql("
                        SELECT COUNT(bdo_id) AS bdo
                        FROM (
                            SELECT bdo_id
                                FROM  " . BD_CTRL . ".scp_Presupuestal AS p
                                JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr USING(rfc_id)
                            WHERE p.gpo_id = :gpo AND p.ur_id = :ur AND p.clc_id = :clc
                            GROUP BY bdo_id
                                UNION
                            SELECT bdo_id
                                FROM  " . BD_CTRL . ".scp_Contable AS c
                                JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr USING(rfc_id)
                            WHERE c.gpo_id = :gpo AND c.ur_id = :ur AND c.clc_id = :clc
                            GROUP BY bdo_id
                        ) inf;
                    ");
                    $this->consultar($params);
                    if ($r = $this->extraer()) {
                        $numDocumentos = $r['bdo'];
                    }

                    $this->setSql("
                        SELECT COUNT(bdo_id) AS bdo
                        FROM (
                            SELECT bdo_id
                                FROM " . BD_TESORERIA . ".min_BancoDocumentoMinistrado_2026
                            WHERE gpo_id = :gpo AND ur_id = :ur AND clc_id = :clc
                            AND bdm_estado = 'A'
                            GROUP BY bdo_id
                        )bdm
                        ;
                    ");
                    $this->consultar($params);
                    if ($r = $this->extraer()) {
                        $numDocMarcados = $r['bdo'];
                    }
                    if (($numDocumentos - $numDocMarcados) == 1) {
                        $this->setSql('SET @usr_ip = INET_ATON(?);');
                        $this->consultar([$ip]);
                        $this->setSql('SET @usr_id = ?;');
                        $this->consultar([$_SESSION[_S]['usr_id']]);
                        $this->setSql('SET @bit_observacion = "SE ACTUALIZA EL ESTADO A 16 PARA EL MINISTRADO";');
                        $this->consultar();
                        //Actualiza el estado de la CLC en 16
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 16
                            WHERE gpo_id = :gpo
                            AND ur_id = :ur AND clc_id = :clc ;"
                        );
                        $this->consultar();
                        $this->setSql(
                            "
                            UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 8
                            WHERE CONCAT( IF ( fpa_id = 1, 'T', IF (fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3) ) IN  (?)
                            AND lot_ejr = " . APP_EJERCICIO . ";"
                        );
                        $this->consultar([$this->prm->loteMunicipio]);
                    }
                    //Valida el número de documentos ministrados
                    $datosSP = new stdClass();
                    $datosSP->gpo_id = $this->prm->gpo;
                    $datosSP->ur_id = $this->prm->ur;
                    $datosSP->clc_id = $this->prm->clc;
                    $datosSP->afe_id = $this->prm->afe;
                    $datosSP->bdo_id = $this->prm->bdo;
                    $datosSP->tdf_id = 'CH';
                    $datosSP->bdm_referenciaMinistrado = $this->prm->cheque;
                    $datosSP->bdm_fechaMinistrado = date("d/m/Y");
                    $tmi = new getTipoMinistracion();
                    $tmiRes = $tmi->validaTMI($this->prm->gpo, $this->prm->ur, $this->prm->clc, APP_EJERCICIO);
                    $datosSP->tmi_id = $tmiRes->tmi_id;
                    $datosSP->ejercicio = APP_EJERCICIO;
                    $datosSP->bit_observacion = 'MINISTRACION DE DOCUMENTO REALIZADA DESDE IMPRESION DE CHEQUES.';
                    $datosSP->sis_id = "TES";
                    $respuesta = sp_AutorizarMinistrarDocumento::call($datosSP);

                    if ($respuesta->bandera != 1) {
                        if (($numDocumentos - $numDocMarcados) == 1) {
                            $this->setSql('SET @usr_ip = INET_ATON(?);');
                            $this->consultar([$ip]);
                            $this->setSql('SET @usr_id = ?;');
                            $this->consultar([$_SESSION[_S]['usr_id']]);
                            $this->setSql('SET @bit_observacion = "OCURRO UN ERROR EN EL MINISTRADO";');
                            $this->consultar();
                            //Actualiza el estado de la CLC en 15
                            $this->setSql(
                                "
                                UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 19
                                WHERE ecl_id = 16 AND gpo_id = :gpo
                                AND ur_id = :ur AND clc_id = :clc ;"
                            );
                            $this->consultar();
                            $this->setSql(
                                "
                                UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 10
                                WHERE ecl_id = 19 AND gpo_id = :gpo
                                AND ur_id = :ur AND clc_id = :clc ;"
                            );
                            $this->consultar();
                            $this->setSql(
                                "
                                UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 13
                                WHERE ecl_id = 10 AND gpo_id = :gpo
                                AND ur_id = :ur AND clc_id = :clc ;"
                            );
                            $this->consultar();
                            $this->setSql(
                                "
                                UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 15
                                WHERE ecl_id = 13 AND AND gpo_id = :gpo
                                AND ur_id = :ur AND clc_id = :clc ;"
                            );
                            $this->consultar();
                            $this->setSql(
                                "
                                UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 7
                                WHERE CONCAT( IF ( fpa_id = 1, 'T', IF (fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3) ) IN  (?)
                                AND lot_ejr = " . APP_EJERCICIO . ";"
                            );
                            $this->consultar([$this->prm->loteMunicipio]);
                        }
                        $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                        return false;
                    }
                }
                break;
        }

        //cambiamos el estado del cheque para nomal ech_id= 2, municipios ech_id=2,imss ech_id=2, manuales =1
        if ($this->prm->tipo == 2) {
            $varEch_id = 1;
        } else { // cheque ministrado
            $varEch_id = 2;
        }

        $this->setSql("
            INSERT INTO " . BD_TESORERIA . ".chq_Cheque (cbs_id, chr_id, chq_id, chq_importe, chq_fechaExpedicion, ech_id, tch_id, usr_id)
            VALUES('" . $this->prm->clabe . "', " . $this->prm->chequera . ", " . $this->prm->cheque . ", " .
            $this->prm->importe . ", CURDATE(), " . $varEch_id . ", " . $this->prm->tipo . ", " . $_SESSION[_S]['usr_id'] . ");
        ");
        $this->consultar();

        $this->setSql("SELECT chr_folioFinal FROM " . BD_TESORERIA . ".chq_Chequera 
            WHERE chr_id = " . $this->prm->chequera . " AND cbs_id = " . $this->prm->clabe);
        $this->consultar();
        $row = $this->extraer();
        if ($this->prm->cheque < $row['chr_folioFinal']) {
            $this->setSql("UPDATE " . BD_TESORERIA . ".chq_Chequera SET chr_numeroActual = chr_numeroActual + 1 
                WHERE chr_id = " . $this->prm->chequera . " AND cbs_id = " . $this->prm->clabe);
            $this->consultar();
        } else {
            $this->setSql("UPDATE " . BD_TESORERIA . ".chq_Chequera SET eca_id = 2 
                WHERE chr_id = " . $this->prm->chequera . " AND cbs_id = " . $this->prm->clabe);
            $this->consultar();

            $this->setSql("
                UPDATE " . BD_TESORERIA . ".chq_Chequera SET eca_id = 1
                WHERE cbs_id = " . $this->prm->clabe . " AND chr_id IN (
                    SELECT chr_id FROM (
                        SELECT chr_id FROM " . BD_TESORERIA . ".chq_Chequera 
                        WHERE eca_id = 4 AND  cbs_id = " . $this->prm->clabe . "
                        ORDER BY chr_folioInicial LIMIT 1
                    ) tmp
                )            
            ");
            $this->consultar();
        }


        switch ($this->prm->tipo) {
            case 1: //Normal
                $params = [
                    $this->prm->clabe,
                    $this->prm->chequera,
                    $this->prm->cheque,
                    $this->prm->anio,
                    $this->prm->gpo,
                    $this->prm->ur,
                    $this->prm->clc,
                    $this->prm->importe,
                    $_SESSION[_S]['usr_id']
                ];
                $this->setSql("
                    INSERT INTO " . BD_TESORERIA . ".chq_ChequeClc (cbs_id, chr_id, chq_id, chq_ejercicio, gpo_id, ur_id, clc_id, chq_ImporteClc, usr_id)
                    VALUES(?,?,?,?,?,?,?,?,?);
                ");
                $this->consultar($params);

                $this->setSql("
                    SELECT COUNT(*) INTO @chequesFaltantes FROM " . BD_TESORERIA . ".lot_LoteClc
                    WHERE (gpo_id, ur_id, clc_id) NOT IN (
                    SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".chq_ChequeClc
                    INNER JOIN " . BD_TESORERIA . ".chq_Cheque USING (cbs_id, chr_id, chq_id)
                    WHERE ech_id BETWEEN 1 AND 2) AND CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN (?);
                ");
                $this->consultar([$this->prm->lote]);
                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 8
                    WHERE @chequesFaltantes = 0 AND CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN (?)
                    AND lot_ejr = " . APP_EJERCICIO . ";
                ");
                $this->consultar([$this->prm->lote]);

                break;
            case 2: //Manual
                $lom_id = substr($this->prm->id, 7);
                $params = [$lom_id,$this->prm->clabe,$this->prm->chequera,$this->prm->cheque,$_SESSION[_S]['usr_id']];
                $this->setSql("
                    INSERT INTO " . BD_TESORERIA . ".vin_PropuestaManualCheque (lom_id, cbs_id, chr_id, chq_id, usr_id)
                    VALUES(?,?,?,?,?);
                ");
                $this->consultar($params);

                $this->setSql("
                    SELECT COUNT(*) INTO @chequesFaltantes FROM " . BD_TESORERIA . ".lot_PropuestaManual
                    WHERE lom_id NOT IN (
                    SELECT lom_id FROM " . BD_TESORERIA . ".vin_PropuestaManualCheque
                    INNER JOIN " . BD_TESORERIA . ".chq_Cheque USING (cbs_id, chr_id, chq_id)
                    WHERE ech_id BETWEEN 1 AND 2) AND CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN (?);
                ");
                $this->consultar([$this->prm->lote]);
                $this->setSQL("
                    UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 8
                    WHERE @chequesFaltantes = 0 AND CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN (?)
                    AND lot_ejr = " . APP_EJERCICIO . ";
                ");
                $this->consultar([$this->prm->lote]);

                break;
            case 3: //IMSS
                $lotes = $this->ponerComillas($this->prm->programacion);
                $params = [
                    $this->prm->clabe,
                    $this->prm->chequera,
                    $this->prm->cheque,
                    $this->prm->anio,
                    $_SESSION[_S]['usr_id']
                ];
                $this->setSql("
                    INSERT INTO " . BD_TESORERIA . ".chq_ChequeClc (cbs_id, chr_id, chq_id, chq_ejercicio, gpo_id, ur_id, clc_id, chq_ImporteClc, usr_id)
                    SELECT ?, ?, ?, ?, gpo_id, ur_id, clc_id, lot_importeClc, ? FROM " . BD_TESORERIA . ".lot_LoteClc
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($lotes);
                ");
                $this->consultar($params);

                $manuales = $this->ponerComillas($this->prm->manual);
                $params = [
                    $this->prm->clabe,
                    $this->prm->chequera,
                    $this->prm->cheque,
                    $_SESSION[_S]['usr_id']
                ];
                $this->setSql("
                    INSERT INTO " . BD_TESORERIA . ".vin_PropuestaManualCheque (lom_id, cbs_id, chr_id, chq_id, usr_id)
                    SELECT lom_id, ?, ?, ?, ? FROM " . BD_TESORERIA . ".lot_PropuestaManual
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($manuales);
                ");
                $this->consultar($params);

                break;
            case 4: //Municipios
                $params = [
                    $this->prm->clabe,
                    $this->prm->chequera,
                    $this->prm->cheque,
                    $this->prm->anio,
                    $this->prm->gpo,
                    $this->prm->ur,
                    $this->prm->clc,
                    $this->prm->importeClc,
                    $_SESSION[_S]['usr_id']
                ];
                $this->setSql("
                    INSERT INTO " . BD_TESORERIA . ".chq_ChequeClc (cbs_id, chr_id, chq_id, chq_ejercicio, gpo_id, ur_id, clc_id, chq_ImporteClc, usr_id)
                    VALUES(?,?,?,?,?,?,?,?,?);
                ");
                $this->consultar($params);

                $params = [
                    $this->prm->clabe,
                    $this->prm->chequera,
                    $this->prm->cheque,
                    $this->prm->anio,
                    $this->prm->gpo,
                    $this->prm->ur,
                    $this->prm->clc,
                    $this->prm->bdo,
                    $this->prm->importe,
                    $_SESSION[_S]['usr_id']
                ];
                $this->setSql("
                    INSERT INTO " . BD_TESORERIA . ".chq_ChequeDocumento(cbs_id, chr_id, chq_id, chq_ejercicio, gpo_id, ur_id, clc_id, bdo_id, chq_ImporteBdo, usr_id)
                    VALUES(?,?,?,?,?,?,?,?,?,?);
                ");
                $this->consultar($params);

                break;
        }

        if ($this->prm->tipo == 4) {
            $this->mixScript("
                    $('#" . $this->prm->id . "').html('<span class=\"fa fa-remove\"/>');
                    $('#" . $this->prm->id . "').removeClass('btn-outline-success imprimirChequeMuni');
                    $('#" . $this->prm->id . "').addClass('btn-outline-warning cancelarChequeMuni');
                    $('#" . $this->prm->id . "').attr('chequera', " . $this->prm->chequera . ");
                    $('#" . $this->prm->id . "').attr('cheque', " . $this->prm->cheque . ");
                    $('#modalImprimirCheque').modal('hide');
                ");
        } else {
            $this->mixScript("
                    $('#" . $this->prm->id . "').html('<span class=\"fa fa-remove\"/>');
                    $('#" . $this->prm->id . "').removeClass('btn-outline-success imprimirCheque');
                    $('#" . $this->prm->id . "').addClass('btn-outline-warning cancelarCheque');
                    $('#" . $this->prm->id . "').attr('chequera', " . $this->prm->chequera . ");
                    $('#" . $this->prm->id . "').attr('cheque', " . $this->prm->cheque . ");
                    $('#modalImprimirCheque').modal('hide');
                    var div = document.getElementById(\"divModalMunicipios\");
                    div.innerHTML = '<div id=\"divModalCancelarCheque\"></div>';
                ");
        }

        $js = '
            var form = $("<form>")
                    .attr("action", "tes/reporte/doc/ChequeMunicipios.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "orden").attr("value", "' .
            $this->prm->orden . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "consec").attr("value", "' .
            $this->prm->cheque . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "importe").attr("value", "' .
            $this->prm->importe . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "concepto").attr("value", "' .
            $this->prm->concepto . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "cuenta").attr("value", "' .
            $this->prm->clabe . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "benef").attr("value", "' .
            $this->prm->benef . '"));
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $js = str_replace(array("\n", "\r", "\t"), '', $js);
        $this->mixScript($js);

        $this->desconectar();
    }

    private function modalCancelarCheque()
    {
        $this->setContenedor('divModalCancelarCheque');
        $this->mixHtml('
            <div id="modalCancelarCheque" class="modal"  role="dialog" data-backdrop="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Datos de Cancelación</h4>
                            <button type="button" class="close btnCancelarCancChq btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </div>
                        <div class="modal-body">
                            <fieldset>
                                 <label for="txtNumCta">Numero de Cuenta: </label>
                                 <input id="txtNumCta" type="text" class="form-control" disabled="true" value="' .
            substr($this->prm->clabe, 6, 11) . '">
                            </fieldset>
                            <br>
                            <fieldset>
                                <label for="txtNumCheque">Numero de Cheque: </label>
                                <input id="txtNumCheque" type="text" class="form-control" disabled="true" value="' .
            $this->prm->cheque . '">
                            </fieldset>
                            <br>
                            <fieldset>
                                <label for="txtRazonCanc">Razón de Cancelación: </label>
                                <textarea id="txtRazonCanc" rows="4" style="width:100%" required="true" maxlength="600"></textarea>
                            </fieldset>
                            <br>
                        </div>
                        <div class="modal-footer">
                            <button id="btnAceptarCancChq" type="button" class="btn btn-outline-primary">Aceptar</button>
                            <button type="button" class="btn btn-outline-danger btnCancelarCancChq">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->mixScript('
            $("#modalCancelarCheque").modal("show");

            $(".btnCancelarCancChq").click(function () {
                $("#modalCancelarCheque").modal("hide");
            });

            $("#btnAceptarCancChq").click(function () {
                if ($("#txtRazonCanc").val().length < 20) {
                    notify("Debe capturar al menos 20 caracteres.", "warning");
                    $("#txtRazonCanc").focus();

                    return false;
                }

                $("#page-loader").removeClass("hide");
                callbackComunica = function() {
                    $("#page-loader").addClass("hide");
                }

                comunica("' . $this->pantalla . '", {
                    metodo: "registrarCancelacion",
                    tipo: ' . $this->prm->tipo . ',
                    anio: ' . $this->prm->anio . ',
                    id: "' . $this->prm->id . '",
                    clabe: "' . $this->prm->clabe . '",
                    chequera: ' . $this->prm->chequera . ',
                    cheque: ' . $this->prm->cheque . ',
                    razon: $("#txtRazonCanc").val()' . ($this->prm->tipo == 1 || $this->prm->tipo == 4 ? ',
                    gpo: ' . $this->prm->gpo . ',
                    ur: ' . $this->prm->ur . ',
                    clc: ' . $this->prm->clc : '') . ($this->prm->tipo == 4 ? ',
                    afe: ' . $this->prm->afe . ',
                    bdo: ' . $this->prm->bdo : '') . ($this->prm->tipo == 3 ? ',
                    programacion: "' . $this->prm->programacion . '",
                    manual: "' . $this->prm->manual . '"' : '') . ($this->prm->tipo == 2 || $this->prm->tipo == 1 ? ',
                    lote: "' . $this->prm->lote . '"' : '') . '
                });

            });
        ');
    }

    private function registrarCancelacion()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $this->conectar();
        $this->setSql('SET @usr_ip = INET_ATON(?);');
        $this->consultar([$ip]);
        $this->setSql('SET @usr_id = ?;');
        $this->consultar([$_SESSION[_S]['usr_id']]);
        $this->setSql('SET @bit_observacion = ?;');
        $this->consultar([$this->prm->razon]);

        switch ($this->prm->tipo) {
            case 1: //Normal
                switch ($this->prm->anio) {
                    case 2017:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2017::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 11 
                            WHERE ecl_id = 12 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2017;"
                        );
                        $this->consultar($params);
                        break;
                    case 2018:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2018::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . APP_EJERCICIO . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2018;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . APP_EJERCICIO . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2018;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . APP_EJERCICIO . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2018;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . APP_EJERCICIO . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2018;"
                        );
                        $this->consultar($params);
                        break;
                    case 2019:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2019::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }
                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2019;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2019;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2019;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2019;"
                        );
                        $this->consultar($params);
                        break;
                    case 2020:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2020::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2020;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2020;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2020;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2020;"
                        );
                        $this->consultar($params);
                        break;
                    case 2021:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2021::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2021;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2021;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2021;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2021;"
                        );
                        $this->consultar($params);
                        break;
                    case 2022:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2022::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2022;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2022;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2022;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2022;"
                        );
                        $this->consultar($params);
                        break;
                    case 2023:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2023::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2023;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2023;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2023;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2023;"
                        );
                        $this->consultar($params);
                        break;
                    case 2024:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2024::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2024;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2024;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2024;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2024;"
                        );
                        $this->consultar($params);
                        break;
                    case 2025:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc2025::call($datosSP);
                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2025;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2025;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 13 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2025;"
                        );
                        $this->consultar($params);

                        $this->setSql(
                            "
                            UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id = 15 
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ? AND ejr_id_clc=2025;"
                        );
                        $this->consultar($params);
                        break;
                    case 2026:
                        $datosSP = new stdClass();
                        $datosSP->gpo_id = $this->prm->gpo;
                        $datosSP->ur_id = $this->prm->ur;
                        $datosSP->clc_id = $this->prm->clc;
                        $datosSP->ejercicio = APP_EJERCICIO;
                        $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                        $datosSP->sis_id = "TES";
                        $respuesta = sp_CancelarMinistrarClc::call($datosSP);

                        if ($respuesta->bandera != 1) {
                            $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                            return false;
                        }

                        $params = [$this->prm->gpo,$this->prm->ur,$this->prm->clc];
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 19 
                            WHERE ecl_id = 16 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ?;"
                        );
                        $this->consultar($params);
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 10 
                            WHERE ecl_id = 19 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ?;"
                        );
                        $this->consultar($params);
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 11 
                            WHERE ecl_id = 10 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ?;"
                        );
                        $this->consultar($params);
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 12 
                            WHERE ecl_id = 11 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ?;"
                        );
                        $this->consultar($params);
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 13 
                            WHERE ecl_id = 12 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ?;"
                        );
                        $this->consultar($params);
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 14
                            WHERE ecl_id = 13 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ?;"
                        );
                        $this->consultar($params);
                        $this->setSql(
                            "
                            UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 15 
                            WHERE ecl_id = 14 AND gpo_id = ?
                            AND ur_id = ? AND clc_id = ?;"
                        );
                        $this->consultar($params);
                        break;
                }
            case 2: //Manual
                $this->setSQL("
                    UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 7
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN (?) AND lot_ejr = " . APP_EJERCICIO . ";
                ");
                $this->consultar([$this->prm->lote]);

                $this->mixScript("
                    $('#" . $this->prm->id . "').html('<span class=\"fa fa-print\"/>');
                    $('#" . $this->prm->id . "').removeClass('btn-outline-warning cancelarCheque');
                    $('#" . $this->prm->id . "').addClass('btn-outline-success imprimirCheque');
                ");
                break;
            case 3: //IMSS
                $lotes = $this->ponerComillas($this->prm->programacion);

                switch ($this->prm->anio) {
                    case 2017:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2017 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();
                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2017::call($datosSP);
                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }
                        break;
                    case 2018:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ant.ejr_id_clc = 2018;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2018::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2018;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2018;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2018;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2018;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2018;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2018;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2018;
                        ");
                        $this->consultar();

                        break;
                    case 2019:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2019 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ant.ejr_id_clc = 2019;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2019::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2019;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2019;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2019;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2019;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2019;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2019;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2019;
                        ");
                        $this->consultar();
                        break;
                    case 2020:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2020 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ant.ejr_id_clc = 2020;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2020::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2020;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2020;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2020;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2020;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2020;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2020;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2020;
                        ");
                        $this->consultar();
                        break;
                    case 2021:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2021 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ant.ejr_id_clc = 2021;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2021::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2021;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2021;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2021;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2021;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2021;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2021;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2021;
                        ");
                        $this->consultar();
                        break;
                    case 2022:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2022 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ant.ejr_id_clc = 2022;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2022::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2022;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2022;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2022;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2022;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2022;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2022;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2022;
                        ");
                        $this->consultar();
                        break;
                    case 2023:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN (?)
                            AND ant.ejr_id_clc = 2023;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2023::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2023;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2023;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2023;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2023;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2023;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2023;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2023;
                        ");
                        $this->consultar();
                        break;
                    case 2024:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ant.ejr_id_clc = 2024;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2024::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2024;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2024;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2024;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2024;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2024;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2024;
                        ");
                        $this->consultar();
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2024;
                        ");
                        $this->consultar();
                        break;
                    case 2025:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            AND ant.ejr_id_clc = 2025;
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc2025::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 19
                            WHERE ant.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2025;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 10
                            WHERE ant.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2025;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 11
                            WHERE ant.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2025;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 12
                            WHERE ant.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2025;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 13
                            WHERE ant.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2025;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 14
                            WHERE ant.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2025;
                        ");
                        $this->consultar();
                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                                INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant UISNG (gpo_id, ur_id, clc_id)
                            SET ant.ecl_id = 15
                            WHERE ant.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                AND ant.ejr_id_clc=2025;
                        ");
                        $this->consultar();
                        break;
                    case 2026:
                        $this->setSql("
                            SELECT gpo_id, ur_id, clc_id FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            INNER JOIN " . BD_CTRL . ".scp_Clc USING (gpo_id, ur_id, clc_id)
                            WHERE CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        while ($row = $this->extraer()) {
                            $datosSP = new stdClass();
                            $datosSP->gpo_id = $row['gpo_id'];
                            $datosSP->ur_id = $row['ur_id'];
                            $datosSP->clc_id = $row['clc_id'];
                            $datosSP->ejercicio = APP_EJERCICIO;
                            $datosSP->bit_observacion = 'CANCELACION REALIZADA DESDE IMPRESION DE CHEQUES.';
                            $datosSP->sis_id = "TES";
                            $respuesta = sp_CancelarMinistrarClc::call($datosSP);

                            if ($respuesta->bandera != 1) {
                                $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                                return false;
                            }
                        }

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 19
                            WHERE CL.ecl_id = 16 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                ;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 10
                            WHERE CL.ecl_id = 19 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                ;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 11
                            WHERE CL.ecl_id = 10 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                ;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 12
                            WHERE CL.ecl_id = 11 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                ;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 13
                            WHERE CL.ecl_id = 12 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                                ;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 14
                            WHERE CL.ecl_id = 13 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes)
                            ;
                        ");
                        $this->consultar();

                        $this->setSql("
                            UPDATE " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CL USING (gpo_id, ur_id, clc_id)
                            SET CL.ecl_id = 15
                            WHERE CL.ecl_id = 14 AND LO.lot_ejr = " . APP_EJERCICIO . " AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
                        ");
                        $this->consultar();

                        break;
                }

                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 7
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($lotes);
                ");
                $this->consultar();

                $manuales = $this->ponerComillas($this->prm->manual);
                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 7
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($manuales);
                ");
                $this->consultar();
                $this->setSql("
                    UPDATE " . BD_TESORERIA . ".lot_PropuestaManual SET elo_id = 7
                    WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($manuales);
                ");
                $this->consultar();

                $this->mixScript("
                    $('#" . $this->prm->id . "').html('<span class=\"fa fa-print\"/>');
                    $('#" . $this->prm->id . "').removeClass('btn-outline-warning cancelarCheque');
                    $('#" . $this->prm->id . "').addClass('btn-outline-success imprimirCheque');
                ");
                break;
            case 4: //Municipios
                if ($this->prm->anio == APP_EJERCICIO) {
                    $datosSP = new stdClass();
                    $datosSP->gpo_id = $this->prm->gpo;
                    $datosSP->ur_id = $this->prm->ur;
                    $datosSP->clc_id = $this->prm->clc;
                    $datosSP->afe_id = $this->prm->afe;
                    $datosSP->bdo_id = $this->prm->bdo;
                    $datosSP->ejercicio = APP_EJERCICIO;
                    $datosSP->bit_observacion = 'CANCELACION DE DOCUMENTO REALIZADA DESDE IMPRESION DE CHEQUES.';
                    $datosSP->sis_id = "TES";
                    $respuesta = sp_CancelarMinistrarDocumento::call($datosSP);

                    if ($respuesta->bandera != 1) {
                        $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                        return false;
                    }
                }

                $this->mixScript("
                    $('#" . $this->prm->id . "').html('<span class=\"fa fa-print\"/>');
                    $('#" . $this->prm->id . "').removeClass('btn-outline-warning cancelarChequeMuni');
                    $('#" . $this->prm->id . "').addClass('btn-outline-success imprimirChequeMuni');
                ");
                break;
        }
        $conexion2 = new Accesodatos();
        $conexion2->conectar();
        $conexion2->setSql("select CURDATE() as fecha ;");
        $conexion2->consultar();
        $fechaCancelado = "";
        while ($row2 = $conexion2->extraer()) {
            $fechaCancelado = $row2['fecha'];
        }
        $params = [$fechaCancelado,$this->prm->clabe,$this->prm->chequera,$this->prm->cheque];
        $this->setSql("
            UPDATE " . BD_TESORERIA . ".chq_Cheque SET ech_id = 4, chq_fechaCancelado=?  WHERE cbs_id = ?
            AND chr_id = ? AND chq_id = ?;
        ");
        $this->consultar($params);

        $this->desconectar();

        $this->mixScript("
            $('#modalCancelarCheque').modal('hide');
            var div = document.getElementById(\"divModalMunicipios\");
            div.innerHTML = '<div id=\"divModalImprimirCheque\"></div>';
            notify('Cheque cancelado exitosamente.', 'success');
        ");
    }

    private function enviarABanca()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        //Obtiene las CLC´s del lote
        $this->conectar();
        $this->setSql("
                SELECT LC.gpo_id, LC.ur_id, LC.clc_id
                FROM " . BD_TESORERIA . ".lot_Lote LO
                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc LC USING(fpa_id,msc_id,lot_ejercicio,lot_id)
                WHERE CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN ('" . $this->prm->lote . "')
                AND LO.lot_ejr = " . APP_EJERCICIO . ";
            ");
        $this->consultar();
        //Verifica cada una de las CLC´s
        if ($r = $this->extraer()) {
            $gpo_id = $r['gpo_id'];
            $ur_id = $r['ur_id'];
            $clc_id = $r['clc_id'];

            $params = [
                'gpo' => $gpo_id,
                'ur' => $ur_id,
                'clc' => $clc_id,
            ];
            //Verifica si hay documentos pendientes por ministrar
            $this->setSql("
                    SELECT COUNT(bdo_id) AS bdo
                    FROM (
                        SELECT bdo_id
                            FROM " . BD_CTRL . ".scp_Presupuestal AS p
                            JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr USING(rfc_id)
                        WHERE p.gpo_id = :gpo AND p.ur_id = :ur AND p.clc_id = :clc
                        GROUP BY bdo_id
                            UNION
                        SELECT bdo_id
                            FROM " . BD_CTRL . ".scp_Contable AS c
                            JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr USING(rfc_id)
                        WHERE c.gpo_id = :gpo AND c.ur_id = :ur AND c.clc_id = :clc
                        GROUP BY bdo_id
                    ) inf;
                ");
            $this->consultar($params);
            if ($r = $this->extraer()) {
                $numDocumentos = $r['bdo'];
            }
            //Obtiene el número de documentos ministrados
            $this->setSql("
                SELECT COUNT(bdo_id) AS bdo
                FROM (
                    SELECT bdo_id
                        FROM " . BD_TESORERIA . ".min_BancoDocumentoMinistrado_2026
                    WHERE gpo_id = :gpo AND ur_id = :ur AND clc_id = :clc
                    AND bdm_estado = 'A'
                    GROUP BY bdo_id
                )bdm
                ;
            ");
            $this->consultar($params);
            if ($r = $this->extraer()) {
                $numDocMarcados = $r['bdo'];
            }

            if (($numDocumentos - $numDocMarcados) >= 1) {
                $this->setSql('SET @usr_ip = INET_ATON(?);');
                $this->consultar([$ip]);
                $this->setSql('SET @usr_id = ? ;');
                $this->consultar([$_SESSION[_S]['usr_id']]);
                $this->setSql('SET @bit_observacion = "ENVIADO A BANCA DESDE IMPRESION DE CHEQUES";');
                $this->consultar();

                //Consulta el estado de CLC
                $this->setSql(
                    "
                    SELECT ecl_id
                    FROM " . BD_CTRL . ".scp_ClcAuxiliar
                    WHERE gpo_id = :gpo
                    AND ur_id = :ur
                    AND clc_id = :clc ;"
                );
                $this->consultar([$params]);
                if ($row = $this->extraer()) {
                    $edoCLC = $row['ecl_id'];
                }
                if ($edoCLC == 16) {
                    //Actualiza el estado de la CLC en 15
                    $this->setSql(
                        "
                        UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 19
                        WHERE ecl_id = 16 AND gpo_id = :gpo
                        AND ur_id = :ur AND clc_id = :clc ;"
                    );
                    $this->consultar([$params]);

                    $this->setSql(
                        "
                        UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 10
                        WHERE ecl_id = 19 AND gpo_id = :gpo
                        AND ur_id = :ur AND clc_id = :clc ;"
                    );
                    $this->consultar([$params]);

                    $this->setSql(
                        "
                        UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 13
                        WHERE ecl_id = 10 AND gpo_id = :gpo
                        AND ur_id = :ur AND clc_id = :clc ;"
                    );
                    $this->consultar([$params]);

                    $this->setSql(
                        "
                        UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id = 15
                        WHERE ecl_id = 13 AND gpo_id = :gpo
                        AND ur_id = :ur AND clc_id = :clc ;"
                    );
                    $this->consultar([$params]);
                }
            }
        }

        $this->setSql('SET @usr_ip = INET_ATON(?);');
        $this->consultar([$ip]);
        $this->setSql('SET @usr_id = ?;');
        $this->consultar([$_SESSION[_S]['usr_id']]);
        $this->setSql('SET @bit_observacion = "ENVIADO A BANCA DESDE IMPRESION DE CHEQUES.";');
        $this->consultar();

        $this->setSql("
            UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id = 7, tlo_id = 4
            WHERE CONCAT( IF ( fpa_id = 1, 'T', IF (fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3) ) IN ('" . $this->prm->lote . "')
            AND lot_ejr = " . APP_EJERCICIO . ";
        ");
        $this->consultar();
        $this->desconectar();

        $this->mixScript("
            notify('Lote enviado exitosamente a Banca.', 'success');
            comunica('" . $this->pantalla . "');
        ");
    }

    public function getImporteManual($idPropManual)
    {
        $lotes = $this->ponerComillas($idPropManual);
        $this->conectar();
        $this->setSql("
            SELECT COUNT(*) AS numCheques FROM " . BD_TESORERIA . ".vin_PropuestaManualCheque v
            INNER JOIN " . BD_TESORERIA . ".lot_PropuestaManual USING (lom_id)
            INNER JOIN " . BD_TESORERIA . ".chq_Cheque c ON v.cbs_id = c.cbs_id AND v.chr_id = c.chr_id AND v.chq_id = c.chq_id
            WHERE ech_id BETWEEN 1 AND 2
            AND CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L')), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) IN ($lotes);
        ");
        $this->consultar();
        $row = $this->extraer();
        $this->desconectar();
        if ($row['numCheques'] > 0) {
            $this->mixScript('
                notify("Ya se han impreso cheques de la propuesta manual, favor de verificarla.", "warning");
            ');
        }

        $this->conectar();
        $this->setSql("
            SELECT
            SUM(LO.lom_importe) AS importeManual
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_PropuestaManual AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            WHERE LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);

        ");

        $this->consultar();
        $row = $this->extraer();

        if ($row['importeManual'] == null) {
            $this->mixScript('
                notify("La propuesta manual no existe.", "warning");
            ');
        }

        return $row['importeManual'];
    }

    public function ponerComillas($lotesConComas)
    {
        $alotes = explode(',', $lotesConComas);
        $lotes = '';
        foreach ($alotes as $lote) {
            $lotes .= ($lotes != '' ? ',' : '') . "'" . trim($lote) . "'";
        }
        return $lotes;
    }

    private function consulta2017($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "SELECT CONCAT('clc_', A.gpo_id, A.ur_id, A.clc_id) AS id, A.gpo_id AS gpo,A.ur_id AS ur,A.clc_id AS clc,
            (IF(A.clc_tipoCereCo=1,'OTB','OP')) AS tipo, A.clc_tipoCereCo,
            CONCAT(LE.loe_mes,LE.loe_dia,LE.loe_anio,LE.loe_cereCo) AS lotePropuestaPago,
            CLA.clc_cereCo AS orden, (A.clc_percepcion-A.clc_retencion) AS importe,
            B.bsf_id AS ctaban_cargo, A.bur_id AS ctaban,
            CONCAT(D.sft_id,D.ori_id,D.sor_id,D.cso_id,D.cfi_id,D.cfi_anio) AS cvefin,
            CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
            A.clc_concepto AS concepto,
            IF (NOT A.clc_beneficiario IS NULL AND A.clc_beneficiario <> '', A.clc_beneficiario, C.ur_nombre) AS benef,
            C.ur_clave AS ur_clave, '' AS ur_siglas,
            A.ue_id AS clc_ue_id,
            SUBSTR(B.bsf_id, 1, 3) AS banco, SUBSTR(B.bsf_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
              chr_id, chq_id,
              IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING (fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2017 . ".scp_Clc AS A USING (gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar AS CLA USING (gpo_id, ur_id, clc_id)
            LEFT JOIN " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos DLE USING (gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2017 . ".scp_LoteEgresos AS LE USING (loe_dia, loe_mes, loe_anio, loe_cereCo, loe_id)
            INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin B USING (ecf_id)
            INNER JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento D USING (ecf_id)
            INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable C USING (gpo_id, ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                    FROM " . BD_TESORERIA . ".chq_Cheque
                    INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                    WHERE ech_id BETWEEN 1 AND 2
                )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        if ($tipo == 4) {
            $sql .= "AND (A.tcl_id='C' OR A.tcl_id='M') LIMIT 1";
        }

        return $sql;
    }

    private function consultaMunicipios2017($gpo, $ur, $clc)
    {
        $sql = "
            SELECT
            CONCAT('ncc_muni_', a.id) AS id,
            a.clc_cereCo AS orden,
            a.clc_concepto AS concep,
            B.bsf_id AS bansf_id,
            a.clc_fechaAutorizacion AS tra_fecha,
            a.clave AS clave,
            a.importe AS imp,
            a.tipomov AS tipomov,
            m.mun_nombre,
            m.mun_id,
            a.gpo_id AS gpo,
            a.ur_id AS ur,
            a.clc_id AS clc,
            a.bdo_id,
            m.mun_rfc,
            IF(C.bdm_estado <> 'A' OR C.bdm_referencia IS NULL OR C.bdm_referencia = '', 1, 0) AS puedeImprimirse,

            C.bdm_estado,bdm_fechaAutorizado
            FROM
             (
                SELECT a.gpo_id,a.ur_id,a.clc_id,d.bdo_id,
             d.ncp_percepcionNeto AS importe,
                b.clc_tipoCereCo,
                c.clc_cereCo,
              d.ue_id AS ue,
                d.ncp_id AS id,
                b.clc_concepto,
                b.ecf_id,
                b.clc_fechaAutorizacion,
            '+' tipomov,
            ncp_id AS clave
                FROM " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria AS a
                INNER JOIN " . BD_CTRL_2017 . ".scp_Clc AS b USING (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar AS c USING (gpo_id,ur_id,clc_id,ecl_id)
                INNER JOIN " . BD_CTRL_2017 . ".scp_Presupuestal AS d USING (gpo_id,ur_id,clc_id)
                WHERE b.gpo_id = $gpo AND b.ur_id = $ur AND b.clc_id = $clc
                GROUP BY a.gpo_id,a.ur_id,a.clc_id,d.bdo_id
               UNION
               SELECT a.gpo_id,a.ur_id,a.clc_id,d.bdo_id,
                (ncc_percepcion-ncc_retencion) AS importe,
               b.clc_tipoCereCo,
               c.clc_cereCo,
               d.cta_ue_id AS ue,
                ncc_id AS id,
              b.clc_concepto,
                b.ecf_id,
                b.clc_fechaAutorizacion,
                IF((ncc_percepcion - ncc_retencion)<0,'-','+')  tipomov,
                ncc_id AS clave
                FROM " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria AS a
                INNER JOIN " . BD_CTRL_2017 . ".scp_Clc AS b USING (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar AS c USING (gpo_id,ur_id,clc_id,ecl_id)
                INNER JOIN " . BD_CTRL_2017 . ".scp_Contable AS d USING (gpo_id,ur_id,clc_id)
                WHERE b.gpo_id = $gpo AND b.ur_id = $ur AND b.clc_id = $clc
                GROUP BY a.gpo_id,a.ur_id,a.clc_id,d.bdo_id
            )AS a
            INNER JOIN " . BD_CTRL_2017 . ".cat_Municipio AS m ON a.ue=m.mun_id
            INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin B USING (ecf_id)
            LEFT JOIN (SELECT *
            FROM " . BD_CTRL_2017 . ".tes_BancoDocumentoMinistrado t
                INNER JOIN (
                    SELECT MAX(bdm_id) AS bdm_id
                    FROM " . BD_CTRL_2017 . ".tes_BancoDocumentoMinistrado
                    INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar AS c USING (gpo_id,ur_id,clc_id)
                    WHERE b.gpo_id = $gpo AND b.ur_id = $ur AND b.clc_id = $clc
                    GROUP BY gpo_id, ur_id, bdo_id, afe_id
                )X USING(bdm_id)
            )AS  C USING  (gpo_id,ur_id,clc_id,bdo_id)
            WHERE a.importe>0    ORDER BY m.mun_id
        ";

        return $sql;
    }

    private function consultaImss2017($idPropuesta, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT 'IMSSUNICO' AS id, '$idPropuesta' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(A.clc_percepcion - A.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,
                BS.bsf_id ctaban_cargo, A.bur_id AS ctaban,
                B.lot_id AS tra_lote,
                A.clc_concepto AS concepto,
                SUBSTR(BS.bsf_id, 1, 3) AS banco, SUBSTR(BS.bsf_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_CTRL_2017 . ".scp_Clc A
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS Y ON A.gpo_id = Y.gpo_id AND A.ur_id = Y.ur_id AND A.clc_id = Y.clc_id
            INNER JOIN " . BD_TESORERIA . ".lot_Lote AS B ON Y.fpa_id = B.fpa_id AND Y.msc_id = B.msc_id
            AND Y.lot_ejercicio = B.lot_ejercicio AND Y.lot_id = B.lot_id
            INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BS USING(ecf_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq ON Y.gpo_id = chq.gpo_id AND Y.ur_id = chq.ur_id AND Y.clc_id = chq.clc_id
            WHERE B.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(B.fpa_id = 1, 'T', IF(B.fpa_id = 2, 'C', 'L') ), LPAD(B.msc_id, 2, '0'), LPAD(B.lot_id, 4, '0'), SUBSTR(B.lot_ejercicio, 3)) IN ($lotes)
            ORDER BY importe;
        ";

        return $sql;
    }

    private function consulta2018($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2018
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2019($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2019
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2019
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2020($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2020
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2020
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2021($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2021
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2021
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2022($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2022
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2022
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2023($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2023
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2023
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2024($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2024
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2024
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2025($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2025
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE ant.ecl_id >= 15
            AND ant.ejr_id_clc = 2025
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consulta2026($idPropuesta, $tipo)
    {
        $lotes = $this->ponerComillas($idPropuesta);
        $sql = "
            SELECT CONCAT('clc_', CL.gpo_id, CL.ur_id, CL.clc_id) AS id, CL.gpo_id AS gpo,
                  CL.ur_id AS ur,
                  CL.clc_id AS clc,
                  CX.clc_cereCo AS orden,
                  (IF(CL.clc_tipoCereCo=1,'OTB','OP')) AS tipo,
                  CL.clc_tipoCereCo,
                  LT.lot_fechaPago,
                  (CL.clc_percepcion-CL.clc_retencion) AS importe,
                  BS.cbs_id AS ctaban_cargo,
                  CL.bur_id AS ctaban,
                  CF.ecf_id AS cvefin,
                  CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                  CL.clc_concepto AS concepto,
                  IF (NOT CL.clc_beneficiario IS NULL AND CL.clc_beneficiario <> '', CL.clc_beneficiario, UR.ur_nombre) AS benef,
                  UR.ur_clave,
                  '' AS ur_siglas,
                  CL.ue_id AS clc_ue_id,
                  SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
        ";

        if ($tipo == 4) {
            $sql .= "
                NULL AS chr_id, NULL AS chq_id, 1 AS puedeImprimirse
            ";
        } else {
            $sql .= "
                  chr_id, chq_id,
                  IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            ";
        }

        $sql .= "
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable AS UR USING(gpo_id,ur_id)
        ";

        if ($tipo != 4) {
            $sql .= "
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2026
            )chq USING (gpo_id, ur_id, clc_id)
            ";
        }

        $sql .= "
            WHERE LT.lot_ejr = " . APP_EJERCICIO . " AND CL.ecl_id >= 15
            AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ($lotes)
        ";

        return $sql;
    }

    private function consultaManual2026($idPropuesta)
    {
        $sql = "
            SELECT
                CONCAT('manual_', LO.lom_id) AS id,
                '0' AS orden,
                (IF(LT.lot_tipoCereCo=1,'OTB','OP')) AS tipo,
                LT.lot_tipoCereCo AS clc_tipoCereCo,
                LT.lot_fechaPago,
                '0' AS lotePropuestaPago,
                LO.lom_importe AS importe,
                LO.cbs_id AS ctaban_cargo,
                LO.cbb_id AS ctaban,
                '0' AS cvefin,
                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS tra_lote,
                LO.lom_concepto AS concepto,
                LO.lom_beneficiario AS benef,
                SUBSTR(LO.cbs_id, 1, 3) AS banco, SUBSTR(LO.cbs_id, 7, 11) AS ctaban_mostrar,
                chq_id, chr_id,
                IF(chq_id IS NULL, 1, 0) puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_PropuestaManual AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT lom_id, cbs_id, chr_id, chq_id FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".vin_PropuestaManualCheque USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (lom_id)
            WHERE CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) IN  ('" . $idPropuesta . "')
            AND LT.lot_ejr = " . APP_EJERCICIO . ";
        ";

        return $sql;
    }

    private function consultaMunicipios2018($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2018 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2018 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2018 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id 
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE ech_id BETWEEN 1 AND 2
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2018 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15 AND ant.ejr_id_clc = 2018 AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";

        return $sql;
    }

    private function consultaMunicipios2019($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2019 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2019 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2019 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id 
                    FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE ech_id BETWEEN 1 AND 2
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2019 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15
                AND ant.ejr_id_clc = 2019
                AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }

    private function consultaMunicipios2020($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2020 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2020 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2020 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE chq_ejercicio = 2020 AND (ech_id BETWEEN 1 AND 2)
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2020 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15
                AND ant.ejr_id_clc = 2020
                AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }

    private function consultaMunicipios2021($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2021 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2021 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2021 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE chq_ejercicio = 2021 AND (ech_id BETWEEN 1 AND 2)
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2021 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15
                AND ant.ejr_id_clc = 2021
                AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }

    private function consultaMunicipios2022($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2022 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2022 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2022 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id 
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE chq_ejercicio = 2022 AND (ech_id BETWEEN 1 AND 2)
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2022 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15
                AND ant.ejr_id_clc = 2022
                AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }

    private function consultaMunicipios2023($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2023 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2023 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2023 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id 
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE chq_ejercicio = 2023 AND (ech_id BETWEEN 1 AND 2)
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15
                AND ant.ejr_id_clc = 2023
                AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }

    private function consultaMunicipios2024($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2024 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2024 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2024 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id 
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE chq_ejercicio = 2024 AND (ech_id BETWEEN 1 AND 2)
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2024 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15
                AND ant.ejr_id_clc = 2024
                AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }
    private function consultaMunicipios2025($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL_2025 . ".scp_Clc AS c
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2025 . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL_2025 . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id 
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE chq_ejercicio = 2025 AND (ech_id BETWEEN 1 AND 2)
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL_2025 . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE ant.ecl_id >= 15
                AND ant.ejr_id_clc = 2025
                AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }
    private function consultaMunicipios2026($gpo, $ur, $clc)
    {
        $sql = "SELECT
                    CONCAT('ncc_muni_', pr.mun_id) AS id,
                    lca.clc_cereCo AS orden,
                    c.clc_concepto AS concep,
                    bs.cbs_id AS bansf_id,
                    c.clc_fechaAutorizacion AS tra_fecha,
                    pr.mun_id AS clave,
                    ((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE(d.bdo_percepcionContable,2))- TRUNCATE (d.bdo_retencionContable,2)) as imp ,
                    if(((TRUNCATE (d.bdo_percepcionPresupuestal,2)+ TRUNCATE (d.bdo_percepcionContable,2))- TRUNCATE(d.bdo_retencionContable,2)) > 0 ,'+','-') AS tipomov,
                    pr.mun_nombre,
                    pr.mun_id,
                    c.gpo_id AS gpo,
                    c.ur_id AS ur,
                    c.clc_id AS clc,
                    doc.afe_id,
                    d.bdo_id,
                    pr.rfc_id,
                    chq_id, chr_id,
                    IF(chq_id IS NULL, 1, 0) puedeImprimirse
                FROM " . BD_CTRL . ".scp_Clc AS c
                INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS lca using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL . ".scp_Presupuestal AS p
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr using(rfc_id)
                        WHERE p.gpo_id=$gpo and p.ur_id=$ur and p.clc_id=$clc
                    UNION
                    SELECT gpo_id,ur_id,clc_id,afe_id,bdo_id,pr.mun_id AS ue,pr.rfc_id
                        FROM  " . BD_CTRL . ".scp_Contable AS c
                        JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on c.cta_id = pr.mun_id
                        WHERE c.gpo_id=$gpo and c.ur_id=$ur and c.clc_id=$clc
                ) AS doc using( gpo_id,ur_id,clc_id)
                                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, chr_id, chq_id 
                        FROM " . BD_TESORERIA . ".chq_Cheque
                        INNER JOIN " . BD_TESORERIA . ".chq_ChequeDocumento USING (cbs_id, chr_id, chq_id)
                        WHERE chq_ejercicio = 2026 AND (ech_id BETWEEN 1 AND 2)
                )chq USING (gpo_id, ur_id, clc_id, bdo_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS sf using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin bs USING (cbs_id)
                INNER JOIN " . BD_CTRL . ".cat_BancoDocumentos AS d USING (gpo_id,ur_id,bdo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor AS pr on pr.rfc_id=doc.rfc_id
                WHERE c.ecl_id >= 15 AND c.gpo_id=$gpo AND c.ur_id=$ur AND c.clc_id=$clc
                ORDER BY pr.mun_id
        ";
        return $sql;
    }

    private function consultaImss2018($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2018
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3))
            IN ($lotes);
        ";

        return $sql;
    }

    private function consultaImss2019($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2019
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";
        return $sql;
    }

    private function consultaImss2020($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2020
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";
        return $sql;
    }

    private function consultaImss2021($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2021
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";
        return $sql;
    }

    private function consultaImss2022($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2022
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";
        return $sql;
    }

    private function consultaImss2023($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2023
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";
        return $sql;
    }

    private function consultaImss2024($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2024
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";

        return $sql;
    }

    private function consultaImss2025($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
            INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id
                FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE cda.ecl_id >= 15
            AND cda.ejr_id_clc = 2025
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";

        return $sql;
    }

    private function consultaImss2026($lote, $manual, $importeManual)
    {
        $lotes = $this->ponerComillas($lote);
        $sql = "SELECT 'IMSSUNICO' AS id, '$lote' AS programacion, '$manual' AS manual,
                ($importeManual + SUM(CL.clc_percepcion - CL.clc_retencion)) AS importe,
                'INSTITUTO MEXICANO DEL SEGURO SOCIAL' AS benef,CL.clc_concepto AS concepto,
                BS.cbs_id AS ctaban_cargo,
                SUBSTR(BS.cbs_id, 1, 3) AS banco, SUBSTR(BS.cbs_id, 7, 11) AS ctaban_mostrar,
                chr_id, chq_id,
                IF(chq_id IS NULL, 1, 0) AS puedeImprimirse
            FROM " . BD_TESORERIA . ".lot_Lote AS LT
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LO USING(fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL . ".scp_Clc AS CL USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar AS CX USING(gpo_id,ur_id,clc_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS CF USING(ecf_id)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS BS USING(cbs_id)
            LEFT JOIN (
                SELECT gpo_id, ur_id, clc_id, chr_id, chq_id FROM " . BD_TESORERIA . ".chq_Cheque
                INNER JOIN " . BD_TESORERIA . ".chq_ChequeClc USING (cbs_id, chr_id, chq_id)
                WHERE ech_id BETWEEN 1 AND 2
                AND chq_ejercicio = 2026
            )chq USING (gpo_id, ur_id, clc_id)
            WHERE CL.ecl_id >= 15
            AND LT.lot_ejr = " . APP_EJERCICIO . "
            AND CONCAT(IF(LO.fpa_id = 1, 'T', IF(LO.fpa_id = 2, 'C', 'L')), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) IN ($lotes);
        ";

        return $sql;
    }

    private function verificarEstadoLote($lote)
    {
        $lotes = $this->ponerComillas($lote);
        $this->conectar();
        $this->setSQL("
            SELECT CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3)) AS lote, elo_id FROM " . BD_TESORERIA . ".lot_Lote
            WHERE CONCAT(IF(fpa_id = 1, 'T', IF(fpa_id = 2, 'C', 'L') ), LPAD(msc_id, 2, '0'), LPAD(lot_id, 4, '0'), SUBSTR(lot_ejercicio, 3))
            IN ($lotes)
            AND lot_ejr = " . APP_EJERCICIO . ";
        ");
        $this->consultar();

        $lotesErroneos = '';
        while ($row = $this->extraer()) {
            if ($row['elo_id'] < 7) {
                $lotesErroneos .= (strlen($lotesErroneos) > 0 ? ', ' : '');
                $lotesErroneos .= 'El estado del lote ' . $row['lote'] . ' (' . $row['elo_id'] . ') es incorrecto';
            }
        }

        $this->desconectar();

        return $lotesErroneos;
    }
}

if (isset($_SESSION[_S]['prm']['T']['5']['1']['1'])) {
    new ControladorCheImpresion();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
