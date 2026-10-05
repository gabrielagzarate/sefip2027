<?php
/**
 * Description of ControladorRegistroModificaciones
 *
 * @author ?
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 16-Oct-2019
 * Comment: -----
 * Objective:
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de lectura.
 **/
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../models/clcs2026.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../lib/tes/consulta/consultaEstadoClc.php';
require_once dirname(__FILE__) . '/../../lib/tes/consulta/consultaLote.php';

class ControladorConsultaTesoreria extends WebResponse
{
    public $nivelJerarquico;
    public $usr_id;
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'tes/consulta/ControladorConsultaTesoreria.php';

        //variables de session
        $this->nivelJerarquico = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : "";
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : "";

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
                    case 'pintaTabla':
                        $this->pintaTabla();
                        break;
                    case 'limpiarCampos':
                        $this->limpiarCampos();
                        break;
                    case 'mainProgramacion':
                        $this->mainProgramacion();
                        break;
                    case 'consultaClc':
                        $this->consultaClc();
                        break;
                    case 'pintaTablaConsultaCLC':
                        $this->pintaTablaConsultaCLC();
                        break;
                    case 'llenaTablaConsultaClc':
                        $this->llenaTablaConsultaClc();
                        break;
                    case 'consultaLotes':
                        $this->consultaLotes();
                        break;
                    case 'pintaTablaConsultaLotes':
                        $this->pintaTablaConsultaLotes();
                        break;
                    case 'llenaTablaConsultaLOTES':
                        $this->llenaTablaConsultaLOTES();
                        break;
                    case 'ponerDetallePropuesta':
                        $this->ponerDetallePropuesta();
                        break;
                    case 'ponerDetalleLote':
                        $this->ponerDetalleLote();
                        break;
                    case 'llenaTablaDetProp':
                        $this->llenaTablaDetProp();
                        break;
                    case 'eligeTipo':
                        $this->eligeTipo();
                        break;
                    case 'pintaTablaConsultaManual':
                        $this->pintaTablaConsultaManual();
                        break;
                    case 'ponerDetalleLoteMANUAL':
                        $this->ponerDetalleLoteMANUAL();
                        break;
                    case 'pintaTablaConsultaFederal':
                        $this->pintaTablaConsultaFederal();
                        break;
                    case 'llenaTablaConsultaFederal':
                        $this->llenaTablaConsultaFederal();
                        break;
                    case 'pintaConsultaLotes':
                        $this->pintaConsultaLotes();
                        break;
                    case 'llenaTablaLotes':
                        $this->llenaTablaLotes();
                        break;
                    case 'pintaDetalleLote':
                        $this->pintaDetalleLote();
                        break;
                    case 'llenaTablaDetalleLote':
                        $this->llenaTablaDetalleLote();
                        break;
                    case 'pintaDetallePropuesta':
                        $this->llenaTablaDetalleLote();
                        break;
                    case 'llenaTablaDetallePropuesta':
                        $this->llenaTablaDetalleLote();
                        break;
                    case 'pintaTablaDetalleClc':
                        $this->pintaTablaDetalleClc();
                        break;
                    case 'llenaTablaDetalleClc':
                        $this->llenaTablaDetalleClc();
                        break;
                    case 'pintaDetalleMunicipios':
                        $this->pintaDetalleMunicipios();
                        break;
                    case 'llenaDetalleMunicipios':
                        $this->llenaDetalleMunicipios();
                        break;
                    case 'pintaDetallePDP':
                        $this->pintaDetallePDP();
                        break;
                    case 'llenaDetallePDP':
                        $this->llenaDetallePDP();
                        break;
                    case 'recargaEstadoClc':
                        $this->recargaEstadoClc();
                        break;
                    default:
                        $this->main();
                        break;

                }
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
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

        $this->mixHtml('
            <div class="container">
                <div class="row mb-4">
                    <div class="col-md-4 "><legend>CONSULTAS</legend></div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-2 ">
                        <div titpp="CONSULTA DE PROPUESTAS" class="boton_opcion" id="consulta_Propuesta">
                            <div class="boton_opcion_titulo" >CONSULTA PROPUESTA</div>
                            <strong><img height="60" width="60" src="img/Alta_Folio.png"><br/></strong>
                            <small>Autorizadas </small>
                        </div>
                    </div>
                    <div class="col-2">
                        <div titpp="CONSULTA CLC" class="boton_opcion" id="consulta_Clc">
                            <div class="boton_opcion_titulo" >CONSULTA DE ESTADO CLCS</div>
                            <strong><img  height="60" width="60" src="img/Autoriza_Folio.png"><br/></strong>
                            <small>Consulta </small>
                        </div>
                    </div>
                    <div class="col-2">
                        <div titpp="CONSULTA LOTES" class="boton_opcion" id="consulta_Lotes">
                            <div class="boton_opcion_titulo" >CONSULTA DE LOTES</div>
                            <strong><img  height="60" width="60" src="img/Autoriza_Folio.png"><br/></strong>
                            <small>Consulta </small>
                        </div>
                    </div>
                </div>
            </div>
            ');

        $this->mixScript('
                    $("#consulta_Propuesta").click(function(){
                        $("#SitemaActivo").hide();
                        comunica("' . $this->pantalla . '",{
                            metodo:"mainProgramacion"
                        });
                    });

                    $("#consulta_Clc").click(function(){
                        $("#SitemaActivo").hide();
                        comunica("' . $this->pantalla . '",{
                            metodo:"consultaClc"
                        });
                    });

                    $("#consulta_Lotes").click(function(){
                        $("#SitemaActivo").hide();
                        comunica("' . $this->pantalla . '",{
                            metodo:"pintaConsultaLotes"
                        });
                    });

                ');
    }

    private function ponerDetalleLoteMANUAL()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $lot_tipo = isset($this->prm->lot_tipo) ? $this->prm->lot_tipo : "";
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $inputFechaMin = isset($this->prm->inputFechaMin) ? $this->prm->inputFechaMin : "";

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->class = "btn";
        $btnRegresar->color = "gris";
        $btnRegresar->icon = "fa-solid fa-circle-left";
        $btnRegresar->click = '
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                // Regresa a la pantalla anterior
                comunica("' . $this->pantalla . '",{
                    metodo: "consultaLotes",
                    inputFechaMin:"' . $this->prm->inputFechaMin . '",
                    tipo:"' . $this->prm->tipo . '"
                });
                    comunica("' . $this->pantalla . '",{
                    metodo: "ponerDetalleLoteMANUAL",
                    inputFechaMin:"' . $this->prm->inputFechaMin . '",
                    tipo:"' . $this->prm->tipo . '"
                });
            ';
        $this->agregaTools($this->ponBoton($btnRegresar));

        $btnExcel = new stdClass();
        $btnExcel->id = "ListadoExcel";
        $btnExcel->color = "verde";
        $btnExcel->class = 'btn me-1';
        $btnExcel->value = "Exportar Excel";
        $btnExcel->icon = "fa-solid fa-file-excel me-2";
        $btnExcel->name = "prueba";
        $btnExcel->click = 'printCmp(); ';
        $this->mixScript('
                function printCmp(){
                    var form = $("<form>")
                    .attr("action", "rep/excel/tes/consulta/ExcelReporteManual.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .attr("id", "enviaPost")
                    .append($("<input>").attr("type", "hidden").attr("name", "fpa_id").val(' . $fpa_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "msc_id").val(' . $msc_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_ejercicio").val(' . $lot_ejercicio . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_id").val(' . $lot_id . '))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ');

        $btnPDF = new stdClass();
        $btnPDF->id = "ListadoPDF";
        $btnPDF->color = "naranja";
        $btnPDF->class = 'btn';
        $btnPDF->value = "Exportar PDF";
        $btnPDF->icon = "fa-solid fa-file-pdf me-2";
        $btnPDF->name = "prueba";
        $btnPDF->click = 'printPDF();';
        $this->mixScript('
                function printPDF(){
                    var form = $("<form>")
                    .attr("action", "rep/doc/tes/consulta/PDFReportePropuestaManual.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .attr("id", "enviaPost")
                    .append($("<input>").attr("type", "hidden").attr("name", "fpa_id").val(' . $fpa_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "msc_id").val(' . $msc_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_ejercicio").val(' . $lot_ejercicio . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_id").val(' . $lot_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "usr_id").val(' . $this->usr_id . '))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ');

        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $tabla = new stdClass();
        $tabla->id = 'tblDetLoteManual';
        $tabla->titulo = 'DETALLE DEL LOTE MANUAL ' . $lot_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetLoteMANUAL';

        /* Array para dibujar la tabla /*/
        $tr = array();

        /* Encabezados /*/
        $th = new stdClass();
        $th->texto = 'FECHA PAGO';
        $th->align = 'center';
        array_push($tr, $th);

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
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        array_push($tr, $th);
        $th = new stdClass();

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);
        $th = new stdClass();

        $th = new stdClass();
        $th->texto = 'CTA NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();

        $th->texto = 'CTA DESTINO';
        array_push($tr, $th);
        $th = new stdClass();

        $th->texto = 'CVE FIN';
        array_push($tr, $th);
        $th = new stdClass();

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla /*/
        $trbody = array();

        /*Cuerpo de la tabla /*/
        $td = new stdClass();
        $td->campoTxt = 'lot_fechaPago';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
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
        $td->campoTxt = 'lom_importe';
        $td->formato = 'moneda';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'iba_abreviatura';
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
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ecf_clave';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_id';
        $td->align = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
                <div class="row">
                    <fieldset class="col-md-2 col-xs-2" id="">
                        ' . $this->ponBoton($btnExcel) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2" id="">
                        ' . $this->ponBoton($btnPDF) . '
                    </fieldset>
                </div>
                <div class="row">
                    <legend>DETALLE MANUALES</legend>
                </div>
                <div class="row">
                    <fieldset class="col-xs-12 col-md-12">
                        ' . $this->wrPonTabla($tabla) . '
                    </fieldset>
                </div>
            ');
        $this->llenaTablaDetLoteMANUAL();
    }

    private function llenaTablaDetLoteMANUAL()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $lot_tipo = isset($this->prm->lot_tipo) ? $this->prm->lot_tipo : "";

        $sql = '';

        $sql .= "
            SELECT
                LO.fpa_id,
                LO.msc_id,
                LO.lot_id,
                LO.lot_ejercicio,
                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                LO.lom_importe,
                LO.cbs_id,
                LO.cbb_id,
                LO.lom_concepto,
                LO.lom_beneficiario,
                CB.cbs_nombre,
                IB.iba_abreviatura,
                LT.lot_fechaPago,
                CF.ecf_clave,
                CONCAT(gpo_id,ur_id) as ur,
                clc_id
            From " . BD_TESORERIA . ".lot_PropuestaManual LO
            INNER JOIN " . BD_TESORERIA . ".lot_Lote as LT using(fpa_id,msc_id,lot_id,lot_ejercicio)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
            INNER JOIN " . BD_TESORERIA . ".vin_PropuestaManualClaveFinanciamiento as VM using(lom_id)
            INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento as CF using(ecf_id)
            WHERE
                LO.lot_id = ?
                AND LO.fpa_id= ?
                AND LO.msc_id= ?
                AND LO.lot_ejercicio= ?
                and LT.lot_ejr= ? ;"
        ;

        /* Hacemos la consulta y llenamos la tabla con el resultado /*/
        $this->conectar();
        $this->setSql($sql);
        $this->setParams(array($lot_id, $fpa_id, $msc_id, $lot_ejercicio, APP_EJERCICIO));
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tblDetLoteManual";
        $this->wrLlenaTabla($nav);
    }

    private function ponerDetalleLote()
    {
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $lot_tipo = isset($this->prm->lot_tipo) ? $this->prm->lot_tipo : "";
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $inputFechaMin = isset($this->prm->inputFechaMin) ? $this->prm->inputFechaMin : "";
        $total = isset($this->prm->total) ? $this->prm->total : "";

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = '&nbsp;&nbsp;Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->class = "btn";
        $btnRegresar->icon = "glyphicon glyphicon-hand-left";
        $btnRegresar->click = '
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                // Regresa a la pantalla anterior
                comunica("' . $this->pantalla . '",{
                    metodo: "consultaLotes",
                    inputFechaMin:"' . $this->prm->inputFechaMin . '",
                    tipo:"' . $this->prm->tipo . '"
                });
                if (' . $this->prm->tipo . '==2){
                    comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaConsultaFederal",
                    inputFechaMin:"' . $this->prm->inputFechaMin . '",
                    tipo:"' . $this->prm->tipo . '"
                });
                } else{
                    comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaConsultaLotes",
                    inputFechaMin:"' . $this->prm->inputFechaMin . '",
                    tipo:"' . $this->prm->tipo . '"
                });
                }

            ';
        $this->agregaTools($this->ponBoton($btnRegresar));

        $btnExcel = new stdClass();
        $btnExcel->id = "ListadoExcel";
        $btnExcel->color = "verde";
        $btnExcel->class = 'btn';
        $btnExcel->value = "&nbsp;Exportar Excel";
        $btnExcel->icon = "th";
        $btnExcel->name = "prueba";
        $btnExcel->click = 'printCmp();';
        $this->mixScript('
                function printCmp(){
                    var form = $("<form>")
                    .attr("action", "rep/excel/tes/consulta/ExcelReporteLotes.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .attr("id", "enviaPost")
                    .append($("<input>").attr("type", "hidden").attr("name", "fpa_id").val(' . $fpa_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "msc_id").val(' . $msc_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_ejercicio").val(' . $lot_ejercicio . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_id").val(' . $lot_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_id").val(' . $lot_tipo . '))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ');

        $btnPDF = new stdClass();
        $btnPDF->id = "ListadoPDF";
        $btnPDF->color = "naranja";
        $btnPDF->class = 'btn';
        $btnPDF->value = "&nbsp;Exportar PDF";
        $btnPDF->icon = "print";
        $btnPDF->name = "prueba";
        $btnPDF->click = 'printPDF();';
        $this->mixScript('
                function printPDF(){
                    var form = $("<form>")
                    .attr("action", "rep/doc/tes/consulta/PDFReportePropuesta.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .attr("id", "enviaPost")
                    .append($("<input>").attr("type", "hidden").attr("name", "fpa_id").val(' . $fpa_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "msc_id").val(' . $msc_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_ejercicio").val(' . $lot_ejercicio . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_id").val(' . $lot_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "lot_id").val(' . $lot_tipo . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "usr_id").val(' . $this->usr_id . '))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ');

        $tabla = new stdClass();
        $tabla->id = 'tblDetLote';
        $totalD = (double) $total;
        $tabla->titulo = 'DETALLE DEL LOTE ' . $lot_id . " TOTAL $" . number_format($totalD, 2);
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetLote';

        /* Array para dibujar la tabla /*/
        $tr = array();

        /* Encabezados /*/
        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '6rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE EGRESO';
        $th->width = '10rem';
        $th->align = 'center';
        // array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'T';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
        $th->width = '4rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA NOMBRE';
        array_push($tr, $th);

        $th->texto = 'DESTINO';
        array_push($tr, $th);
        $th = new stdClass();
        // }

        $th->texto = 'CVE_FIN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla /*/
        $trbody = array();

        /*Cuerpo de la tabla /*/

        $td = new stdClass();
        $td->campoTxt = 'fecha_Pago';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_egreso';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_clc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cere';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'Folio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_importe';
        $td->formato = 'moneda';
        $td->align = 'center';
        array_push($trbody, $td);
        // }
        $td = new stdClass();
        $td->campoTxt = 'responsable';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre_banco';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre_cuenta';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'destino';
        $td->align = 'center';
        array_push($trbody, $td);
        // }

        $td = new stdClass();
        $td->campoTxt = 'cve_fin';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_id';
        $td->align = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
                <div class="row">
                    <legend>Detalle del Lote</legend>
                </div>
                <div class="row">
                    <fieldset class="col-md-2 col-xs-2" id="">
                        ' . $this->ponBoton($btnExcel) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2" id="">
                        ' . $this->ponBoton($btnPDF) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-xs-12 col-md-12">
                        ' . $this->wrPonTabla($tabla) . '
                    </fieldset>
                </div>
            ');
        $this->llenaTablaDetLote();
    }

    private function llenaTablaDetLote()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $lot_tipo = isset($this->prm->lot_tipo) ? $this->prm->lot_tipo : '';
        $params = [];
        switch ($lot_tipo) {
            case 6: //caso Manual
                $prams = [];
                $sql = 'SELECT
                            l.fpa_id AS lotDet_fpa,
                            l.msc_id AS lotDet_msc,
                            l.lot_ejercicio AS lotDet_ejercicio,
                            l.lot_id AS lotDet,
                            dl.gpo_id AS lotDet_gpo,
                            dl.ur_id AS lotDet_ur,
                            dl.clc_id AS lotDet_clc,
                            dl.lom_importe AS lotDet_importe,
                            dl.lom_beneficiario AS lotDet_beneficiario,
                            dl.lom_concepto AS lotDet_concepto,
                            l.lot_fechaGeneracion AS lotDet_fecha
                        FROM
                            ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS dl USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                        LEFT JOIN ctrl' . $lot_ejercicio . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
                        WHERE
                                l.lot_ejr = :lotEjr
                            AND l.lot_id = :lotId
                            AND dl.fpa_id= :fpa
                            AND dl.msc_id= :msc
                            AND dl.lot_ejercicio= :lot ;';
                $params = [
                    ":lotEjr" => APP_EJERCICIO,
                    ":lotId" => $lot_id,
                    ":fpa" => $fpa_id,
                    ":msc" => $msc_id,
                    ":lot" => $lot_ejercicio,
                ];
                break;
            case 1: //normales
            default:

                //consulta de lineas
                if ($lot_tipo == 9) { //tipo federal
                    $sqlLineas = "LEFT JOIN " . BD_TESORERIA . ".vin_LoteClcLineaCaptura VL using (fpa_id,msc_id,lot_ejercicio,lot_id,gpo_id,ur_id,clc_id)";
                } else {
                    $sqlLineas = "LEFT JOIN " . BD_TESORERIA . ".vin_LoteDocumentoLineaCaptura VL using (fpa_id,msc_id,lot_ejercicio,lot_id,gpo_id,ur_id,clc_id)";
                }

                //separamos por año
                switch ($lot_ejercicio) {
                    case 2017:
                        $params = [];
                        $sql = "SELECT
                                    LO.fpa_id AS lotDet_fpa,
                                    LO.msc_id AS lotDet_msc,
                                    LO.lot_ejercicio AS lotDet_ejercicio,
                                    LO.lot_id AS lotDet,
                                    LC.gpo_id AS lotDet_gpo,
                                    LC.ur_id AS lotDet_ur,
                                    LC.clc_id AS lotDet_clc,
                                    LC.lot_importeClc AS lotDet_importe,
                                    CL.clc_beneficiario AS lotDet_beneficiario,
                                    CL.clc_concepto AS lotDet_concepto,
                                    concat(CL.gpo_id,CL.ur_id) ur,
                                    IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                    CX.clc_cereCo as Folio,
                                    CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                    CL.bur_id as destino,
                                    BN.bsf_id as cargo,
                                    CV.ecf_clave as cve_fin,
                                    UR.ur_nombre as responsable,
                                    LO.lot_fechaGeneracion AS lotDet_fecha,
                                    LE.loe_clave as lote_egreso,
                                    LO.lot_fechaPago as fecha_Pago,
                                    CB.cbs_nombre as nombre_cuenta,
                                    IB.iba_abreviatura as nombre_banco,
                                    LIC.lca_lineaCaptura,
                                    LIC.lca_clabeAbono,
                                    LIC.lca_importe
                                FROM
                                    " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                                $sqlLineas
                                LEFT JOIN " . BD_TESORERIA . ".lca_LineaCaptura LIC using(lca_id)
                                INNER JOIN " . BD_CTRL_2017 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                                left join " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                                left join " . BD_CTRL_2017 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                                INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN USING (ecf_id)
                                INNER JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                                inner join " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                                inner join " . BD_TESORERIA . ".cat_InstitucionBancaria IB on (CB.iba_id=IB.iba_id)
                                INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                                WHERE
                                        LO.lot_ejr = :lotEjr
                                    AND LO.fpa_id = :fpa
                                    and LO.msc_id= :msc
                                    and LO.lot_ejercicio = :lot
                                    and LO.lot_id= :lotId ;";
                        $params = [
                            ":lotEjr" => APP_EJERCICIO,
                            ":lotId" => $lot_id,
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                        ];
                        break;
                    case 2018:
                        //case lineas de captura
                        $params = [];
                        $sql = "SELECT
                                    LO.fpa_id AS lotDet_fpa,
                                    LO.msc_id AS lotDet_msc,
                                    LO.lot_ejercicio AS lotDet_ejercicio,
                                    LO.lot_id AS lotDet,
                                    LC.gpo_id AS lotDet_gpo,
                                    LC.ur_id AS lotDet_ur,
                                    LC.clc_id AS lotDet_clc,
                                    LC.lot_importeClc AS lotDet_importe,
                                    CL.clc_beneficiario AS lotDet_beneficiario,
                                    CL.clc_concepto AS lotDet_concepto,
                                    concat(CL.gpo_id,CL.ur_id) ur,
                                    IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                    CX.clc_cereCo as Folio,
                                    CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                    CL.bur_id as destino,
                                    CF.cbs_id as cargo,
                                    CV.ecf_clave as cve_fin,
                                    UR.ur_nombre as responsable,
                                    LO.lot_fechaGeneracion AS lotDet_fecha,
                                    LO.lot_fechaPago AS fecha_Pago,
                                    LE.loe_clave as lote_egreso,
                                    CB.cbs_nombre as nombre_cuenta,
                                    IB.iba_abreviatura as nombre_banco
                                FROM
                                    " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                                LEFT JOIN " . BD_CTRL_2018 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                                LEFT JOIN " . BD_CTRL_2018 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                                INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                                INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                                INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                                WHERE
                                    LO.lot_ejr= " . APP_EJERCICIO . "
                                and LO.fpa_id = :fpa
                                and LO.msc_id= :msc
                                and LO.lot_ejercicio = :lot
                                and LO.lot_id= :lot_id ;";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2019:
                        $params = [];
                        $sql = "
                                    SELECT
                                        LO.fpa_id AS lotDet_fpa,
                                        LO.msc_id AS lotDet_msc,
                                        LO.lot_ejercicio AS lotDet_ejercicio,
                                        LO.lot_id AS lotDet,
                                        LC.gpo_id AS lotDet_gpo,
                                        LC.ur_id AS lotDet_ur,
                                        LC.clc_id AS lotDet_clc,
                                        LC.lot_importeClc AS lotDet_importe,
                                        CL.clc_beneficiario AS lotDet_beneficiario,
                                        CL.clc_concepto AS lotDet_concepto,
                                        concat(CL.gpo_id,CL.ur_id) ur,
                                        IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                        CX.clc_cereCo as Folio,
                                        CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                        CL.bur_id as destino,
                                        CF.cbs_id as cargo,
                                        CV.ecf_clave as cve_fin,
                                        UR.ur_nombre as responsable,
                                        LO.lot_fechaGeneracion AS lotDet_fecha,
                                        LO.lot_fechaPago AS fecha_Pago,
                                        '' as lote_egreso,
                                        CB.cbs_nombre as nombre_cuenta,
                                        IB.iba_abreviatura as nombre_banco
                                    FROM
                                        " . BD_TESORERIA . ".lot_Lote AS LO
                                    INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                                    INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                    INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                                    INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                                    INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                                    INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                                    INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                                    WHERE
                                        LO.lot_ejr= " . APP_EJERCICIO . "
                                    and LO.fpa_id = :fpa
                                    and LO.msc_id= :msc
                                    and LO.lot_ejercicio = :lot
                                    and LO.lot_id= :lot_id ;";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2020:
                        $params = [];
                        $sql = "SELECT
                                    LO.fpa_id AS lotDet_fpa,
                                    LO.msc_id AS lotDet_msc,
                                    LO.lot_ejercicio AS lotDet_ejercicio,
                                    LO.lot_id AS lotDet,
                                    LC.gpo_id AS lotDet_gpo,
                                    LC.ur_id AS lotDet_ur,
                                    LC.clc_id AS lotDet_clc,
                                    LC.lot_importeClc AS lotDet_importe,
                                    CL.clc_beneficiario AS lotDet_beneficiario,
                                    CL.clc_concepto AS lotDet_concepto,
                                    concat(CL.gpo_id,CL.ur_id) ur,
                                    IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                    CX.clc_cereCo as Folio,
                                    CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                    CL.bur_id as destino,
                                    CF.cbs_id as cargo,
                                    CV.ecf_clave as cve_fin,
                                    UR.ur_nombre as responsable,
                                    LO.lot_fechaGeneracion AS lotDet_fecha,
                                    LO.lot_fechaPago AS fecha_Pago,
                                    '' as lote_egreso,
                                    CB.cbs_nombre as nombre_cuenta,
                                    IB.iba_abreviatura as nombre_banco
                                FROM
                                    " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                                INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                                INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                                INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                                WHERE
                                    LO.lot_ejr= " . APP_EJERCICIO . "
                                and LO.fpa_id = :fpa
                                and LO.msc_id= :msc
                                and LO.lot_ejercicio = :lot
                                and LO.lot_id= :lot_id ;";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2021:
                        $params = [];
                        $sql = "SELECT
                                    LO.fpa_id AS lotDet_fpa,
                                    LO.msc_id AS lotDet_msc,
                                    LO.lot_ejercicio AS lotDet_ejercicio,
                                    LO.lot_id AS lotDet,
                                    LC.gpo_id AS lotDet_gpo,
                                    LC.ur_id AS lotDet_ur,
                                    LC.clc_id AS lotDet_clc,
                                    LC.lot_importeClc AS lotDet_importe,
                                    CL.clc_beneficiario AS lotDet_beneficiario,
                                    CL.clc_concepto AS lotDet_concepto,
                                    concat(CL.gpo_id,CL.ur_id) ur,
                                    IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                    CX.clc_cereCo as Folio,
                                    CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                    CL.bur_id as destino,
                                    CF.cbs_id as cargo,
                                    CV.ecf_clave as cve_fin,
                                    UR.ur_nombre as responsable,
                                    LO.lot_fechaGeneracion AS lotDet_fecha,
                                    LO.lot_fechaPago AS fecha_Pago,
                                    '' as lote_egreso,
                                    CB.cbs_nombre as nombre_cuenta,
                                    IB.iba_abreviatura as nombre_banco
                                FROM
                                    " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                                INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                                INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                                INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                                WHERE
                                    LO.lot_ejr= " . APP_EJERCICIO . "
                                and LO.fpa_id =  :fpa
                                and LO.msc_id= :msc
                                and LO.lot_ejercicio =  :lot
                                and LO.lot_id= :lot_id ,";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2022:
                        $params = [];
                        $sql = "SELECT
                                    LO.fpa_id AS lotDet_fpa,
                                    LO.msc_id AS lotDet_msc,
                                    LO.lot_ejercicio AS lotDet_ejercicio,
                                    LO.lot_id AS lotDet,
                                    LC.gpo_id AS lotDet_gpo,
                                    LC.ur_id AS lotDet_ur,
                                    LC.clc_id AS lotDet_clc,
                                    LC.lot_importeClc AS lotDet_importe,
                                    CL.clc_beneficiario AS lotDet_beneficiario,
                                    CL.clc_concepto AS lotDet_concepto,
                                    concat(CL.gpo_id,CL.ur_id) ur,
                                    IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                    CX.clc_cereCo as Folio,
                                    CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                    CL.bur_id as destino,
                                    CF.cbs_id as cargo,
                                    CV.ecf_clave as cve_fin,
                                    UR.ur_nombre as responsable,
                                    LO.lot_fechaGeneracion AS lotDet_fecha,
                                    LO.lot_fechaPago AS fecha_Pago,
                                    '' as lote_egreso,
                                    CB.cbs_nombre as nombre_cuenta,
                                    IB.iba_abreviatura as nombre_banco
                                FROM
                                    " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                                INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                                INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                                INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                                WHERE
                                    LO.lot_ejr= " . APP_EJERCICIO . "
                                and LO.fpa_id =  :fpa
                                and LO.msc_id= :msc
                                and LO.lot_ejercicio =  :lot
                                and LO.lot_id= :lot_id";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2023:
                        $params = [];
                        $sql = "SELECT
                                    LO.fpa_id AS lotDet_fpa,
                                    LO.msc_id AS lotDet_msc,
                                    LO.lot_ejercicio AS lotDet_ejercicio,
                                    LO.lot_id AS lotDet,
                                    LC.gpo_id AS lotDet_gpo,
                                    LC.ur_id AS lotDet_ur,
                                    LC.clc_id AS lotDet_clc,
                                    LC.lot_importeClc AS lotDet_importe,
                                    CL.clc_beneficiario AS lotDet_beneficiario,
                                    CL.clc_concepto AS lotDet_concepto,
                                    concat(CL.gpo_id,CL.ur_id) ur,
                                    IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                    CX.clc_cereCo as Folio,
                                    CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                    CL.bur_id as destino,
                                    CF.cbs_id as cargo,
                                    CV.ecf_clave as cve_fin,
                                    UR.ur_nombre as responsable,
                                    LO.lot_fechaGeneracion AS lotDet_fecha,
                                    LO.lot_fechaPago AS fecha_Pago,
                                    '' as lote_egreso,
                                    CB.cbs_nombre as nombre_cuenta,
                                    IB.iba_abreviatura as nombre_banco
                                FROM
                                    " . BD_TESORERIA . ".lot_Lote AS LO
                                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                                INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                                INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                                INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                                INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                                WHERE
                                    LO.lot_ejr= " . APP_EJERCICIO . "
                                and LO.fpa_id = :fpa
                                and LO.msc_id= :msc
                                and LO.lot_ejercicio = :lot
                                and LO.lot_id= :lot_id ;";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2024:
                        $params = [];
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr= " . APP_EJERCICIO . "
                            and LO.fpa_id =  :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio =  :lot
                            and LO.lot_id= :lot_id ;";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2025:
                        $params = [];
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr= " . APP_EJERCICIO . "
                            and LO.fpa_id =  :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio =  :lot
                            and LO.lot_id= :lot_id ;";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                    case 2026:
                    default:
                        $params = [];
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable UR ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr= " . APP_EJERCICIO . "
                            and LO.fpa_id =  :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio =  :lot
                            and LO.lot_id= :lot_id ;";
                        $params = [
                            ":fpa" => $fpa_id,
                            ":msc" => $msc_id,
                            ":lot" => $lot_ejercicio,
                            ":lot_id" => $lot_id,
                        ];
                        break;
                }
                break;

        }
        /* Hacemos la consulta y llenamos la tabla con el resultado /*/
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tblDetLote";
        $this->wrLlenaTabla($nav);
    }

    private function llenaTablaDetProp()
    {
        $det_prop = isset($this->prm->det_prop) ? $this->prm->det_prop : "";

        if (empty($det_prop)) {
            return;
        }

        $sql = "
                SELECT p.pro_ejercicio
                FROM
                    " . BD_TESORERIA . ".pro_Propuesta AS p
                WHERE
                    p.pro_id =  ?;";

        $pro_ejercicio = '';
        $this->conectar();
        $this->setSql($sql);
        $this->setParams(array($det_prop));
        $this->consultar();

        if ($r = $this->extraer()) {
            $pro_ejercicio = $r['pro_ejercicio'];
        }
        $par = [];
        /* Se arma la consulta, hay que tomar en cuenta que se hará un join con los CLC */
        switch ($pro_ejercicio) {
            case 2017:
                $par = [];
                $sql = "
                        SELECT
                            p.pro_id AS proDet_propuesta,
                            p.pro_ejercicio,
                            dp.gpo_id AS proDet_gpo,
                            dp.ur_id AS proDet_ur,
                            dp.clc_id AS proDet_clc,
                            dp.pro_importeClc AS proDet_importe,
                            clc.clc_beneficiario AS proDet_beneficiario,
                            clc.clc_concepto AS proDet_concepto,
                            clc.clc_fechaAutorizacion AS proDet_fecha,
                            clc.ecf_id,
                            BN.bsf_id as cbs_id,
                            clc.bur_id,
                            CV.ecf_clave as cve_fin,
                            concat(clc.gpo_id,clc.ur_id) ur,
                            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as folio,
                            UR.ur_nombre as responsable,
                            LE.loe_clave as lote_egreso,
                            CB.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                        FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                        INNER JOIN " . BD_CTRL_2017 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2017 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                        INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN using (ecf_id)
                        INNER JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria IB using (iba_id)
                        INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR USING(gpo_id,ur_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                        WHERE
                            p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2018:
                $par = [];
                $sql = "
                        SELECT
                            p.pro_id AS proDet_propuesta,
                            p.pro_ejercicio,
                            dp.gpo_id AS proDet_gpo,
                            dp.ur_id AS proDet_ur,
                            dp.clc_id AS proDet_clc,
                            dp.pro_importeClc AS proDet_importe,
                            clc.clc_beneficiario AS proDet_beneficiario,
                            clc.clc_concepto AS proDet_concepto,
                            clc.clc_fechaAutorizacion AS proDet_fecha,
                            BS.cbs_id,
                            clc.bur_id,

                            CV.ecf_clave as cve_fin,
                            concat(clc.gpo_id,clc.ur_id) ur,
                            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as folio,
                            UR.ur_nombre as responsable,
                            LE.loe_clave as lote_egreso,
                            BS.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                        FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                        INNER JOIN " . BD_CTRL_2018 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc USING(gpo_id,ur_id,clc_id,ecl_id)
                        INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2018 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2018 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                        INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                        INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                        WHERE
                            p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2019:
                $par = [];
                $sql = "
                        SELECT
                            p.pro_id AS proDet_propuesta,
                            p.pro_ejercicio,
                            dp.gpo_id AS proDet_gpo,
                            dp.ur_id AS proDet_ur,
                            dp.clc_id AS proDet_clc,
                            dp.pro_importeClc AS proDet_importe,
                            clc.clc_beneficiario AS proDet_beneficiario,
                            clc.clc_concepto AS proDet_concepto,
                            clc.clc_fechaAutorizacion AS proDet_fecha,
                            BS.cbs_id,
                            clc.bur_id,
                            CV.ecf_clave as cve_fin,
                            concat(clc.gpo_id,clc.ur_id) ur,
                            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as folio,
                            UR.ur_nombre as responsable,
                            '' as lote_egreso,
                            BS.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                        FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                        INNER JOIN " . BD_CTRL_2019 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                        INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                        INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                        WHERE
                            p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2020:
                $par = [];
                $sql = "
                        SELECT
                            p.pro_id AS proDet_propuesta,
                            p.pro_ejercicio,
                            dp.gpo_id AS proDet_gpo,
                            dp.ur_id AS proDet_ur,
                            dp.clc_id AS proDet_clc,
                            dp.pro_importeClc AS proDet_importe,
                            clc.clc_beneficiario AS proDet_beneficiario,
                            clc.clc_concepto AS proDet_concepto,
                            clc.clc_fechaAutorizacion AS proDet_fecha,
                            BS.cbs_id,
                            clc.bur_id,
                            CV.ecf_clave as cve_fin,
                            concat(clc.gpo_id,clc.ur_id) ur,
                            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as folio,
                            UR.ur_nombre as responsable,
                            '' as lote_egreso,
                            BS.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                        FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                        INNER JOIN " . BD_CTRL_2020 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                        INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                        INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                        WHERE
                            p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2021:
                $par = [];
                $sql = "
                        SELECT
                            p.pro_id AS proDet_propuesta,
                            p.pro_ejercicio,
                            dp.gpo_id AS proDet_gpo,
                            dp.ur_id AS proDet_ur,
                            dp.clc_id AS proDet_clc,
                            dp.pro_importeClc AS proDet_importe,
                            clc.clc_beneficiario AS proDet_beneficiario,
                            clc.clc_concepto AS proDet_concepto,
                            clc.clc_fechaAutorizacion AS proDet_fecha,
                            BS.cbs_id,
                            clc.bur_id,
                            CV.ecf_clave as cve_fin,
                            concat(clc.gpo_id,clc.ur_id) ur,
                            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as folio,
                            UR.ur_nombre as responsable,
                            '' as lote_egreso,
                            BS.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                        FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                        INNER JOIN " . BD_CTRL_2021 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                        INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                        INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                        WHERE
                            p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2022:
                $sql = "
                        SELECT
                            p.pro_id AS proDet_propuesta,
                            p.pro_ejercicio,
                            dp.gpo_id AS proDet_gpo,
                            dp.ur_id AS proDet_ur,
                            dp.clc_id AS proDet_clc,
                            dp.pro_importeClc AS proDet_importe,
                            clc.clc_beneficiario AS proDet_beneficiario,
                            clc.clc_concepto AS proDet_concepto,
                            clc.clc_fechaAutorizacion AS proDet_fecha,
                            BS.cbs_id,
                            clc.bur_id,
                            CV.ecf_clave as cve_fin,
                            concat(clc.gpo_id,clc.ur_id) ur,
                            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as folio,
                            UR.ur_nombre as responsable,
                            '' as lote_egreso,
                            BS.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                        FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                        INNER JOIN " . BD_CTRL_2022 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                        INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                        INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                        WHERE
                            p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2023:
                $sql = "
                        SELECT
                            p.pro_id AS proDet_propuesta,
                            p.pro_ejercicio,
                            dp.gpo_id AS proDet_gpo,
                            dp.ur_id AS proDet_ur,
                            dp.clc_id AS proDet_clc,
                            dp.pro_importeClc AS proDet_importe,
                            clc.clc_beneficiario AS proDet_beneficiario,
                            clc.clc_concepto AS proDet_concepto,
                            clc.clc_fechaAutorizacion AS proDet_fecha,
                            BS.cbs_id,
                            clc.bur_id,
                            CV.ecf_clave as cve_fin,
                            concat(clc.gpo_id,clc.ur_id) ur,
                            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as folio,
                            UR.ur_nombre as responsable,
                            '' as lote_egreso,
                            BS.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                        FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                        INNER JOIN " . BD_CTRL_2023 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                        INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                        INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                        WHERE
                            p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2024:
                $par = [];
                $sql = "
                            SELECT
                                p.pro_id AS proDet_propuesta,
                                p.pro_ejercicio,
                                dp.gpo_id AS proDet_gpo,
                                dp.ur_id AS proDet_ur,
                                dp.clc_id AS proDet_clc,
                                dp.pro_importeClc AS proDet_importe,
                                clc.clc_beneficiario AS proDet_beneficiario,
                                clc.clc_concepto AS proDet_concepto,
                                clc.clc_fechaAutorizacion AS proDet_fecha,
                                BS.cbs_id,
                                clc.bur_id,
                                CV.ecf_clave as cve_fin,
                                concat(clc.gpo_id,clc.ur_id) ur,
                                IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as folio,
                                UR.ur_nombre as responsable,
                                '' as lote_egreso,
                                BS.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                            FROM
                                " . BD_TESORERIA . ".pro_Propuesta AS p
                            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                            INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                            WHERE
                                p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2025:
                $par = [];
                $sql = "
                            SELECT
                                p.pro_id AS proDet_propuesta,
                                p.pro_ejercicio,
                                dp.gpo_id AS proDet_gpo,
                                dp.ur_id AS proDet_ur,
                                dp.clc_id AS proDet_clc,
                                dp.pro_importeClc AS proDet_importe,
                                clc.clc_beneficiario AS proDet_beneficiario,
                                clc.clc_concepto AS proDet_concepto,
                                clc.clc_fechaAutorizacion AS proDet_fecha,
                                BS.cbs_id,
                                clc.bur_id,
                                CV.ecf_clave as cve_fin,
                                concat(clc.gpo_id,clc.ur_id) ur,
                                IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as folio,
                                UR.ur_nombre as responsable,
                                '' as lote_egreso,
                                BS.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                            FROM
                                " . BD_TESORERIA . ".pro_Propuesta AS p
                            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                            INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                            WHERE
                                p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
            case 2026:
            default:
                $par = [];
                $sql = "
                            SELECT
                                p.pro_id AS proDet_propuesta,
                                p.pro_ejercicio,
                                dp.gpo_id AS proDet_gpo,
                                dp.ur_id AS proDet_ur,
                                dp.clc_id AS proDet_clc,
                                dp.pro_importeClc AS proDet_importe,
                                clc.clc_beneficiario AS proDet_beneficiario,
                                clc.clc_concepto AS proDet_concepto,
                                clc.clc_fechaAutorizacion AS proDet_fecha,
                                BS.cbs_id,
                                clc.bur_id,
                                CV.ecf_clave as cve_fin,
                                concat(clc.gpo_id,clc.ur_id) ur,
                                IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as folio,
                                UR.ur_nombre as responsable,
                                '' as lote_egreso,
                                BS.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                            FROM
                                " . BD_TESORERIA . ".pro_Propuesta AS p
                            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                            INNER JOIN " . BD_CTRL . ".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF using(ecf_id)
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin BS using(cbs_id)
                            INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                            WHERE
                                p.pro_id = :pro ;";
                $par = [":pro" => $this->prm->det_prop];
                break;
        }

        /* Hacemos la consulta y llenamos la tabla con el resultado /*/
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($par);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = "tblDetallePropuesta";
        $this->wrLlenaTabla($nav);
    }

    private function ponerDetallePropuesta()
    {
        $det_prop = isset($this->prm->det_prop) ? $this->prm->det_prop : "";
        $total = isset($this->prm->total) ? $this->prm->total : "";

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = '&nbsp;&nbsp;Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->class = "btn";
        $btnRegresar->icon = "glyphicon glyphicon-hand-left";
        $btnRegresar->click = '
                // Regresa a la pantalla anterior consultaLotes
                comunica("' . $this->pantalla . '",{
                    metodo: "consultaLotes",
                    inputFechaMin:"' . $this->prm->inputFechaMin . '",
                    tipo:"' . $this->prm->tipo . '"
                });
                comunica("' . $this->pantalla . '",{
                    metodo: "eligeTipo",
                    inputFechaMin:"' . $this->prm->inputFechaMin . '",
                    tipo:"' . $this->prm->tipo . '"
                });
            ';
        $this->agregaTools($this->ponBoton($btnRegresar));

        $btnExcel = new stdClass();
        $btnExcel->id = "ListadoExcel";
        $btnExcel->color = "verde";
        $btnExcel->class = 'btn';
        $btnExcel->value = "&nbsp;Exportar Excel";
        $btnExcel->icon = "th";
        $btnExcel->name = "prueba";
        $btnExcel->click = 'printCmp();';
        $this->mixScript('
                function printCmp(){
                    var form = $("<form>")
                    .attr("action", "rep/excel/tes/consulta/ExcelReportePropuesta.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .attr("id", "enviaPost")
                    .append($("<input>").attr("type", "hidden").attr("name", "det_prop").val(' . $det_prop . '))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ');
        $btnPDF = new stdClass();
        $btnPDF->id = "ListadoPDF";
        $btnPDF->color = "naranja";
        $btnPDF->class = 'btn';
        $btnPDF->value = "&nbsp;Exportar PDF";
        $btnPDF->icon = "print";
        $btnPDF->name = "prueba";
        $btnPDF->click = 'print();';
        $this->mixScript('
                function print() {
                    var form = $("<form>")
                    .attr("action", "rep/doc/tes/consulta/reportePropuesta.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .attr("id", "enviaPost")
                    .append($("<input>").attr("type", "hidden").attr("name", "det_prop").val(' . $det_prop . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "usr_id").val(' . $this->usr_id . '))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ');
        $tabla = new stdClass();
        $tabla->id = 'tblDetallePropuesta';
        $tabla->titulo = 'DETALLE DE LA PROPUESTA ' . $det_prop;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetProp';
        $tabla->comunica->det_prop = $det_prop;
        /* Array para dibujar la tabla /*/
        $tr = array();
        /* Encabezados /*/
        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE EGRESO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'T';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA NOMBRE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA DESTINO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CVE FIN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla /*/
        $trbody = array();

        /*Cuerpo de la tabla /*/
        $td = new stdClass();
        $td->campoTxt = 'proDet_fecha';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_egreso';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_clc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cere';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'folio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_importe';
        $td->align = 'center';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'responsable';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proDet_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre_banco';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre_cuenta';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cve_fin';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote';
        $td->align = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
                <div class="row">
                <fieldset class="col-md-2 col-xs-2" id="">
                    ' . $this->ponBoton($btnExcel) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" id="">
                    ' . $this->ponBoton($btnPDF) . '
                </fieldset>
                <legend>Propuestas</legend>
                </div>
                <div class="row">
                    <fieldset class="col-xs-12 col-md-12">
                        ' . $this->wrPonTabla($tabla) . '
                    </fieldset>
                </div>
            ';
        $this->mixHtml($html);
        $this->llenaTablaDetProp();
    }

    private function eligeTipo()
    {
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        if ($tipo == "") {
            $this->mixScript(" notify('FALTAN DATOS','ERROR') ");
            return 0;
        }
        switch ($tipo) {
            case 1: //manual
                $this->pintaTablaConsultaManual();
                break;
            case 2: //federales
                $this->pintaTablaConsultaFederal();
                break;
            case 3: //Municipios
                $this->pintaTablaConsultaMunicipios();
                break;
            case 0: //normal
            default:
                $this->pintaTablaConsultaLotes();
                break;
        }
    }

    private function pintaTablaConsultaManual()
    {
        $this->setContenedor('divTabla');
        $tabla = new stdClass();

        $tabla->id = 'tbl-ConsultaMANUAL';
        $tabla->titulo = 'Listado de Propuestas Manuales';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConsultaMANUAL';
        $tabla->comunica->inputFechaMin = '{$("#fechaMin").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'MODO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DETALLE LOTE';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'modo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'elo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = "search";
        $td->class = "detLot";
        $td->name = "IDCLC";
        $td->id = "IDCLC";
        $td->attr = new stdClass();
        $td->attr->lot_id = "lot_id";
        $td->attr->fpa_id = "fpa_id";
        $td->attr->msc_id = "msc_id";
        $td->attr->lot_ejercicio = "lot_ejercicio";
        $td->attr->lot_tipo = "lot_tipo";

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaConsultaMANUAL();
    }

    private function pintaTablaConsultaMunicipios()
    {
        $this->setContenedor('divTabla');
        $tabla = new stdClass();

        $tabla->id = 'tbl-ConsultaFederal';
        $tabla->titulo = 'Listado de Lotes Federales';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConsultaMunicipios';
        $tabla->comunica->inputFechaMin = '{$("#fechaMin").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'MODO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC\'s';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DETALLE';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'modo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'elo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NumeroClc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = "search";
        $td->class = "detLot";
        $td->name = "IDCLC";
        $td->id = "IDCLC";
        $td->attr = new stdClass();
        $td->attr->lot_id = "lot_id";
        $td->attr->fpa_id = "fpa_id";
        $td->attr->msc_id = "msc_id";
        $td->attr->lot_ejercicio = "ejercicio";
        $td->attr->lot_tipo = "lot_tipo";
        $td->attr->TOTAL = "total";

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaConsultaMunicipios();
    }

    private function pintaTablaConsultaFederal()
    {
        $this->setContenedor('divTabla');
        $tabla = new stdClass();

        $tabla->id = 'tbl-ConsultaFederal';
        $tabla->titulo = 'Listado de Lotes Federales';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConsultaFederal';
        $tabla->comunica->inputFechaMin = '{$("#fechaMin").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'MODO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC\'s';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DETALLE';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'modo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'elo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NumeroClc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = "search";
        $td->class = "detLot";
        $td->name = "IDCLC";
        $td->id = "IDCLC";
        $td->attr = new stdClass();
        $td->attr->lot_id = "lot_id";
        $td->attr->fpa_id = "fpa_id";
        $td->attr->msc_id = "msc_id";
        $td->attr->lot_ejercicio = "ejercicio";
        $td->attr->lot_tipo = "lot_tipo";
        $td->attr->TOTAL = "total";

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaConsultaFederal();
    }

    private function pintaTablaConsultaLotes()
    {
        $this->setContenedor('divTabla');
        $tabla = new stdClass();

        $tabla->id = 'tbl-ConsultaLOTES';
        $tabla->titulo = 'Listado de Propuestas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConsultaLOTES';
        $tabla->comunica->inputFechaMin = '{$("#fechaMin").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'MODO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROPUESTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC PROPUESTA';

        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DETALLE PROPUESTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DETALLE LOTE';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'modo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pro_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'elo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NumeroClc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = "search";
        $td->class = "detProp";
        $td->name = "IDCLC";
        $td->id = "IDCLC";
        $td->attr = new stdClass();
        $td->attr->ID = "pro_id";
        $td->attr->TOTAL = "total";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'existeLote';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = "search";
        $td->casos->verdadero->class = "detLot";
        $td->casos->verdadero->name = "IDCLC";
        $td->casos->verdadero->id = "IDCLC";
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->lot_id = "lot_id";
        $td->casos->verdadero->attr->fpa_id = "fpa_id";
        $td->casos->verdadero->attr->msc_id = "msc_id";
        $td->casos->verdadero->attr->lot_ejercicio = "lot_ejercicio";
        $td->casos->verdadero->attr->lot_tipo = "lot_tipo";
        $td->casos->verdadero->attr->TOTAL = "total";

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaConsultaLOTES();
    }

    private function llenaTablaConsultaLOTES()
    {
        $params = [];
        $fechaMin = isset($this->prm->inputFechaMin) ? $this->prm->inputFechaMin : "";

        if ($fechaMin != "") {
            $arrFecha = explode("/", $fechaMin);
            $fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        if ($fechaMin == "") {
            $fechaMin = date('Y-m-d');
        }
        $sqlExtra = "";

        $sqlExtra .= " WHERE PR.pro_fechaGeneracion =  :fmin ";
        $params[':fmin'] = $fechaMin;
        $sql = "
                SELECT
                    if(LT.tlo_id is null ,TL.tlo_nombre,TLL.tlo_nombre) as modo,
                    LT.tlo_id,
                    LT.lot_id,
                    LT.fpa_id,
                    LT.msc_id,
                    LT.lot_ejercicio,
                    LT.lot_id,
                    PR.pro_id,
                    PR.pro_ejercicio as ejercicio,
                    CONCAT( IF ( VL.fpa_id = 1, 'T', IF (VL.fpa_id = 2, 'C', 'L') ), LPAD(VL.msc_id, 2, '0'), LPAD(VL.lot_id, 4, '0'), SUBSTR(VL.lot_ejercicio, 3) ) AS lote_id,
                    EL.elo_nombre,
                    IF (VL.pro_id is null ,(SELECT count(clc_id) FROM " . BD_TESORERIA . ".pro_PropuestaClc WHERE pro_id=PR.pro_id ),(SELECT count(fpa_id) FROM " . BD_TESORERIA . ".lot_LoteClc WHERE fpa_id = LT.fpa_id AND msc_id=LT.msc_id AND lot_ejercicio=LT.lot_ejercicio AND lot_id=LT.lot_id )) as NumeroClc,
                    IF (VL.pro_id is null ,(SELECT sum(pro_importeClc) FROM " . BD_TESORERIA . ".pro_PropuestaClc WHERE pro_id=PR.pro_id ),(SELECT sum(lot_importeClc) FROM " . BD_TESORERIA . ".lot_LoteClc WHERE fpa_id = LT.fpa_id AND msc_id=LT.msc_id AND lot_ejercicio=LT.lot_ejercicio AND lot_id=LT.lot_id )) as total,
                    IF (VL.lot_id is null ,0,1) as existeLote
                FROM " . BD_TESORERIA . ".pro_Propuesta as PR
                INNER JOIN " . BD_TESORERIA . ".cat_TipoLote TL using(tlo_id)
                LEFT JOIN " . BD_TESORERIA . ".vin_PropuestaLote as VL using(pro_id)
                LEFT JOIN " . BD_TESORERIA . ".lot_Lote as LT on VL.fpa_id = LT.fpa_id AND VL.msc_id=LT.msc_id AND VL.lot_id=LT.lot_id AND VL.lot_ejercicio=LT.lot_ejercicio AND LT.lot_ejr = " . APP_EJERCICIO . "
                LEFT JOIN " . BD_TESORERIA . ".cat_EstadoLote EL on LT.elo_id=EL.elo_id
                LEFT JOIN " . BD_TESORERIA . ".cat_TipoLote TLL on TLL.tlo_id = LT.tlo_id
                    " . $sqlExtra;

        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-ConsultaLOTES";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $(".detProp").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "ponerDetallePropuesta",
                        det_prop: $(this).attr("ID"),
                        tipo:' . $this->prm->tipo . ',
                        inputFechaMin:"' . $this->prm->inputFechaMin . '",
                        total: $(this).attr("TOTAL")
                    });
                });

                $(".detLot").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "ponerDetalleLote",
                        lot_id: $(this).attr("lot_id"),
                        fpa_id: $(this).attr("fpa_id"),
                        msc_id: $(this).attr("msc_id"),
                        lot_ejercicio: $(this).attr("lot_ejercicio"),
                        lot_tipo: $(this).attr("lot_tipo"),
                        tipo:' . $this->prm->tipo . ',
                        inputFechaMin:"' . $this->prm->inputFechaMin . '",
                        total: $(this).attr("TOTAL")
                    });
                });
            ');
    }

    private function llenaTablaConsultaMANUAL()
    {
        $fechaMin = isset($this->prm->inputFechaMin) ? $this->prm->inputFechaMin : "";
        $params = [];
        $sql = '
                SELECT
                    "MANUAL" AS modo,
                    ET.elo_nombre,
                    LO.fpa_id,
                    LO.msc_id,
                    LO.lot_id,
                    LO.lot_ejercicio,
                    LO.lot_ejercicio AS ejercicio,
                    CONCAT( IF ( LO.fpa_id = 1, "T", IF (LO.fpa_id = 2, "C", "L") ), LPAD(LO.msc_id, 2, "0"), LPAD(LO.lot_id, 4, "0"), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                    LO.lot_fechaGeneracion,LO.lot_importe
                FROM ' . BD_TESORERIA . '.lot_Lote AS LO
                INNER JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS LM using (fpa_id,msc_id,lot_ejercicio,lot_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote ET ON (ET.elo_id = LO.elo_id)
            ';

        if (!empty($fechaMin)) {
            $arrFecha = explode("/", $fechaMin);
            $fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
            $params[':ejer'] = APP_EJERCICIO;
            $params[':fmin'] = $fechaMin;
            $sql .= 'WHERE LO.lot_ejr = :ejer and LO.lot_fechaGeneracion = :fmin ';
        }

        $sql .= 'GROUP BY LO.fpa_id,LO.msc_id,LO.lot_ejercicio,LO.lot_id
            ';

        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-ConsultaMANUAL";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $(".detLot").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "ponerDetalleLoteMANUAL",
                        lot_id: $(this).attr("lot_id"),
                        fpa_id: $(this).attr("fpa_id"),
                        msc_id: $(this).attr("msc_id"),
                        lot_ejercicio: $(this).attr("lot_ejercicio"),
                        lot_tipo: $(this).attr("lot_tipo"),
                        tipo:' . $this->prm->tipo . ',
                        inputFechaMin:"' . $this->prm->inputFechaMin . '",
                        total: $(this).attr("TOTAL")
                    });
                });
            ');
    }

    private function llenaTablaConsultaMunicipios()
    {
        $fechaMin = isset($this->prm->inputFechaMin) ? $this->prm->inputFechaMin : "";
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : "";
        $params = [];
        $sql = "
                SELECT TL.tlo_nombre as modo,
                    LT.tlo_id,
                    LT.lot_id,
                    LT.fpa_id,
                    LT.msc_id,
                    LT.lot_ejercicio AS ejercicio,
                    CONCAT( IF ( LT.fpa_id = 1, 'T', IF (LT.fpa_id = 2, 'C', 'L') ), LPAD(LT.msc_id, 2, '0'), LPAD(LT.lot_id, 4, '0'), SUBSTR(LT.lot_ejercicio, 3) ) AS lote_id,
                    EL.elo_nombre,
                    LT.lot_importe AS total,
                    COUNT(LT.lot_id) AS NumeroClc
                FROM " . BD_TESORERIA . ".lot_Lote AS LT
                INNER JOIN " . BD_TESORERIA . ".cat_TipoLote AS TL USING(tlo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_EstadoLote AS EL USING(elo_id)
                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS DL USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                WHERE tlo_id = :tlo and LT.lot_ejr = :ejr
                ";
        $params[':tlo'] = 4;
        $params[':ejr'] = APP_EJERCICIO;

        if (!empty($fechaMin)) {
            $arrFecha = explode("/", $fechaMin);
            $fechaMin = $arrFecha[2] . "-" . $arrFecha[1] . "-" . $arrFecha[0];
        } else {
            $fechaMin = date('Y-m-d');
        }

        $params[':fmin'] = $fechaMin;
        $sql .= " AND LT.lot_fechaGeneracion = :fmin
                    GROUP BY fpa_id, msc_id, lot_ejercicio, lot_id
                ";

        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-ConsultaFederal";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $(".detLot").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "ponerDetalleLote",
                        lot_id: $(this).attr("lot_id"),
                        fpa_id: $(this).attr("fpa_id"),
                        msc_id: $(this).attr("msc_id"),
                        lot_ejercicio: $(this).attr("lot_ejercicio"),
                        lot_tipo: $(this).attr("lot_tipo"),
                        tipo:' . $this->prm->tipo . ',
                        inputFechaMin:"' . $this->prm->inputFechaMin . '",
                        total: $(this).attr("TOTAL")
                    });
                });
            ');
    }

    private function llenaTablaConsultaFederal()
    {
        $fechaMin = isset($this->prm->inputFechaMin) ? $this->prm->inputFechaMin : "";
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : "";
        $params = [];
        $sql = "
                SELECT TL.tlo_nombre as modo,
                    LT.tlo_id,
                    LT.lot_id,
                    LT.fpa_id,
                    LT.msc_id,
                    LT.lot_ejercicio AS ejercicio,
                    CONCAT( IF ( LT.fpa_id = 1, 'T', IF (LT.fpa_id = 2, 'C', 'L') ), LPAD(LT.msc_id, 2, '0'), LPAD(LT.lot_id, 4, '0'), SUBSTR(LT.lot_ejercicio, 3) ) AS lote_id,
                    EL.elo_nombre,
                    LT.lot_importe AS total,
                    COUNT(LT.lot_id) AS NumeroClc
                FROM " . BD_TESORERIA . ".lot_Lote AS LT
                INNER JOIN " . BD_TESORERIA . ".cat_TipoLote AS TL USING(tlo_id)
                INNER JOIN " . BD_TESORERIA . ".cat_EstadoLote AS EL USING(elo_id)
                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS DL USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                WHERE tlo_id = :tlo and LT.lot_ejr = :ejr
                ";
        $params[':tlo'] = 5;
        $params[':ejr'] = APP_EJERCICIO;
        if (!empty($fechaMin)) {
            $arrFecha = explode("/", $fechaMin);
            $fechaMin = $arrFecha[2] . "-" . $arrFecha[1] . "-" . $arrFecha[0];
        } else {
            $fechaMin = date('Y-m-d');
        }
        $params[':fmin'] = $fechaMin;
        $sql .= " AND LT.lot_fechaGeneracion = :fmin
                    GROUP BY fpa_id, msc_id, lot_ejercicio, lot_id
                ";

        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-ConsultaFederal";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $(".detLot").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "ponerDetalleLote",
                        lot_id: $(this).attr("lot_id"),
                        fpa_id: $(this).attr("fpa_id"),
                        msc_id: $(this).attr("msc_id"),
                        lot_ejercicio: $(this).attr("lot_ejercicio"),
                        lot_tipo: $(this).attr("lot_tipo"),
                        tipo:' . $this->prm->tipo . ',
                        inputFechaMin:"' . $this->prm->inputFechaMin . '",
                        total: $(this).attr("TOTAL")
                    });
                });
            ');
    }

    private function consultaLotes()
    {
        $this->mixScript('
                DesactivaAtajosMenu();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

                $("#homeMenu").click( function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
                });
            ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = '&nbsp;&nbsp;Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-test ";
        $btnregresarindex->icon = "glyphicon glyphicon-hand-left";
        $btnregresarindex->click = '
                // Regresa a la pantalla anterior
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
            ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $inputFechaMin = new StdClass();
        $inputFechaMin->id = 'fechaMin';
        $inputFechaMin->name = 'fechaMin';
        $inputFechaMin->placeholder = 'FECHA';
        $inputFechaMin->enDiv = true;
        $inputFechaMin->label = 'FECHA';
        $inputFechaMin->formato = 'fecha';
        if (isset($this->prm->inputFechaMin) and $this->prm->inputFechaMin != "") {
            $inputFechaMin->value = $this->prm->inputFechaMin;
        }

        $inputEstado = new StdClass();
        $estados = array();
        array_push($estados, 'NORMAL');
        array_push($estados, 'MANUALES');
        array_push($estados, 'FEDERALES');
        array_push($estados, 'MUNICIPIOS');
        $inputEstado->opciones = $estados;
        $inputEstado->id = 'estado';
        $inputEstado->name = 'estado';
        $inputEstado->required = true;
        $inputEstado->mensaje = 'SELECCIONE EL TIPO';
        $inputEstado->label = 'TIPO';
        if (isset($this->prm->tipo) and $this->prm->tipo != "") {
            $inputEstado->selecciona = $this->prm->tipo;
        }

        $btnBuscar = new stdClass();
        $btnBuscar->id = "buscar";
        $btnBuscar->name = "buscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->icon = "glyphicon glyphicon-search";
        $btnBuscar->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"eligeTipo",
                    inputFechaMin:$("#fechaMin").val(),
                    tipo:$("#estado").val()
                })';

        $html = '
                <div class="row class="col-md-12 col-xs-12 "">
                    <legend>CONSULTA LOTES Y PROPUESTAS
                    </legend>

                    <fieldset class="col-md-3 col-xs-3" id="">
                        ' . $this->ponInput($inputFechaMin) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3" id="">
                        ' . $this->ponCombo($inputEstado) . '
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
    }

    private function recargaEstadoClc()
    {
        $anioEjercicio = [2026,2025,2024,2023,2022,2021,2020,2019,2018,2017];
        $ejercicio = $anioEjercicio[$this->prm->idEjercicio];
        $estadoClc = new stdClass();
        $estadoClc->id = 'estadoClc';
        $estadoClc->sql = '
            SELECT ecl_id,ecl_nombre FROM ctrl' . $ejercicio . '.cat_EstadoClc
        ';
        $this->recargaCombo($estadoClc);
    }

    private function consultaClc()
    {
        $buscar = isset($this->prm->buscar) ? $this->prm->buscar : false;
        $busqueda = isset($this->prm->busqueda) ? $this->prm->busqueda : '';

        //crea el boton de regreso
        $this->mixScript('
                DesactivaAtajosMenu();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation();
                });

                $("#homeMenu").click( function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
                });
            ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "me-1";
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "fa-solid fa-circle-left me-1";
        $btnregresarindex->click = '
                // Regresa a la pantalla anterior
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
            ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $inputEjercicio = new StdClass();
        $datos = array();
        array_push($datos, APP_EJERCICIO);
        array_push($datos, "2025");
        array_push($datos, "2024");
        array_push($datos, "2023");
        array_push($datos, "2022");
        array_push($datos, "2021");
        array_push($datos, "2020");
        if ($this->serverProduccion) {
            array_push($datos, "2019");
            array_push($datos, "2018");
            array_push($datos, "2017");
        }
        $inputEjercicio->opciones = $datos;
        $inputEjercicio->id = 'ejercicio';
        $inputEjercicio->name = 'ejercicio';
        $inputEjercicio->required = true;
        $inputEjercicio->mensaje = 'SELECCIONE EL EJERCICIO';
        $inputEjercicio->label = 'EJERCICIO';
        $inputEjercicio->change = '
            comunica("' . $this->pantalla . '", {
                metodo: "recargaEstadoClc",
                idEjercicio: $(this).val()
            });
        ';

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
        $inputClaveBancaria->search = true;

        $INPUTCuentaDestino = new StdClass();
        $INPUTCuentaDestino->id = 'cuentaDestino';
        $INPUTCuentaDestino->name = 'cuentaDestino';
        $INPUTCuentaDestino->placeholder = 'CTA DESTINO';
        $INPUTCuentaDestino->enDiv = true;
        $INPUTCuentaDestino->label = 'CTA DESTINO';

        $inputClaveFinanciamiento = new StdClass();
        $inputClaveFinanciamiento->sql = '
                SELECT cfi.cfi_clave, cfi.cfi_nombre
                FROM ' . BD_CTRL . '.enc_ClaveFinanciamiento enc
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
                    cfi USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio);
            ';
        $inputClaveFinanciamiento->id = 'claveFinanciamiento';
        $inputClaveFinanciamiento->name = 'claveFinanciamiento';
        $inputClaveFinanciamiento->placeholder = 'CLAVE FINANCIAMIENTO';
        $inputClaveFinanciamiento->enDiv = true;
        $inputClaveFinanciamiento->label = 'CLAVE FINANCIAMIENTO';
        $inputClaveFinanciamiento->search = true;

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
        $inputNombreUR->sql = "SELECT concat(gpo_id,ur_id),ur_nombre FROM ctrl2024.cat_UnidadResponsable";
        $inputNombreUR->id = 'nombreUR';
        $inputNombreUR->name = 'nombreUR';
        $inputNombreUR->placeholder = 'NOMBRE UR';
        $inputNombreUR->enDiv = true;
        $inputNombreUR->label = 'NOMBRE UR';
        $inputNombreUR->search = true;

        $inputEstadoClc = new StdClass();
        $inputEstadoClc->sql = "SELECT ecl_id,ecl_nombre FROM ctrl2024.cat_EstadoClc";
        $inputEstadoClc->id = 'estadoClc';
        $inputEstadoClc->name = 'estadoClc';
        $inputEstadoClc->placeholder = 'ESTADO CLC';
        $inputEstadoClc->enDiv = true;
        $inputEstadoClc->label = 'ESTADO CLC';
        $inputEstadoClc->search = true;

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
        $inputRFC->id = "rfcFiltro";
        $inputRFC->label = "R.F.C del proveedor";
        $inputRFC->required = true;
        $inputRFC->tabla = 'cat_Proveedor';
        $inputRFC->name = "control";
        $inputRFC->tablas = " INNER JOIN sinpres.cat_Proveedores USING ( rfc_id )";
        $inputRFC->maxlength = 20;
        $inputRFC->class = "form-control";

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
        $inputProveedor->enDiv = true;
        $inputProveedor->tablas = " INNER JOIN sinpres.cat_Proveedores USING ( rfc_id )";

        $btnBuscar = new stdClass();
        $btnBuscar->id = "buscar";
        $btnBuscar->name = "buscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->color = "gris";
        $btnBuscar->icon = "fa fa-search me-1";
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
                rfc: $("#rfcFiltro").val()
            })';

        $btnLimpiarCampo = new stdClass();
        $btnLimpiarCampo->id = "limpiarCampos";
        $btnLimpiarCampo->name = "limpiarCampos";
        $btnLimpiarCampo->value = "limpiar Campos";
        $btnLimpiarCampo->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"consultaClc",
            })';

        $btnExcel = new stdClass();
        $btnExcel->id = "ListadoExcel";
        $btnExcel->color = "verde";
        $btnExcel->class = 'me-1';
        $btnExcel->value = "Exportar Excel";
        $btnExcel->icon = "fa-solid fa-file-excel me-2";
        $btnExcel->name = "prueba";
        $btnExcel->click = 'printCmp();';
        $this->mixScript('
            function printCmp(){
                var form = $("<form>")
                .attr("action", "rep/excel/tes/consulta/ExcelReporteEstadoClc.php")
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
                form.remove();
            }
            ');

        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
                <div class="row class="col-md-12 col-xs-12 "">
                    <fieldset class="col-md-3 col-xs-3 mb-4" id="">
                        ' . $this->ponBoton($btnExcel) . '
                    </fieldset>
                </div>
                <div class="row class="col-md-12 col-xs-12 "">
                    <div class="mb-3">
                    <legend>CONSULTA ESTADO CLC
                    </legend>
                    </div>
                    <fieldset class="col-md-3 col-xs-3" id="">
                        ' . $this->ponCombo($inputEjercicio) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3" id="">
                        ' . $this->ponCombo($inputDocumento) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3" id="">
                        ' . $this->ponInput($clc) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3 mb-3" id="">
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
                    <fieldset class="col-md-3 col-xs-3 mb-3" id="">
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
                    <fieldset class="col-md-3 col-xs-3 mb-3" id="">
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
                    <fieldset class="col-md-3 col-xs-3 mb-3" id="">
                        ' . $this->ponCombo($inputClaveFinanciamiento) . '
                    </fieldset>
                    <!--<fieldset class="col-md-3 col-xs-3 mb-3" id="">
                        ' . $this->ponInput($inputLoteEgreso) . '
                    </fieldset>-->
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
                    <fieldset class="col-md-3 col-xs-3 mb-3" id="">
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

                    /*comunica("' . $this->pantalla . '",{
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
                        estatal:$("#estatal").val()
                    });*/
                ');
        }

        // Dibujamos la pantalla que permite buscar un proveedor
        $this->mixHtml(DibujaPantallaBuscarProveedorHTML());
        $this->mixScript(DibujaPantallaBuscarProveedorJS());
        $this->mixScript('
           ' . $this->ponF1Proveedores('rfcFiltro', [], 'nombreFiltro') . '
        ');
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

        $tabla->accion = new stdClass;
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/rep_clc.php';

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
        $th->texto = 'FECHA GENERACIÓN CERE/CERECO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CAPITULO GASTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SUBTIPO';
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
        $td->campoTxt = 'clc_generacionCereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'capitulo_gasto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
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
        $params = [];
        $sqlCtrlAnterior = "";
        $estado2018 = "";
        $anio = isset($this->prm->anio) ? $this->prm->anio : "";
        $estado = ($anio == APP_EJERCICIO) ? " WHERE ant.ecl_id > 9 " : " WHERE ant.ecl_id > 9"; //cambiar al ejercico a migrar
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
            $sqlExtra .= " AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) in (" . $nuevoLote . ") ";
        }
        if ($claveBancaria != '') {
            $params[':cveBan'] = $claveBancaria;
            $sqlExtra .= " AND CB.cbs_id = :cveBan ";
        }
        if ($cuentaDestino != '') {
            $params[':ctaDes'] = $cuentaDestino;
            $sqlExtra .= " AND C.bur_id = :ctaDes";
        }
        if ($claveFinanciamiento != '') {
            $params[':cveFin'] = $claveFinanciamiento;
            $sqlExtra .= " AND CF.ecf_clave = :cveFin ";
        }
        if ($loteEgreso != '') {
            $params[':loe'] = $loteEgreso;
            $sqlExtra .= " AND LE.loe_clave = :loe ";
        }
        if ($folio != '') {
            $sqlExtra .= " AND CX.clc_cereCo IN (" . $folio . ") ";
        }
        if ($importeMin != '' && $importeMax == '') {
            $params[':impM'] = $importeMin;
            $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) >= :impM ";
        }

        if ($importeMin == '' && $importeMax != '') {
            $params[':impMax'] = $importeMax;
            $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) <= :impMax ";
        }

        if ($importeMin != '' && $importeMax != '') {
            $sqlExtra .= " AND ((C.clc_percepcion - C.clc_retencion) BETWEEN $importeMin AND $importeMax) ";
        }

        if ($fechaMin != '' && $fechaMax == '') {
            $params[':fmin'] = $fechaMin;
            $sqlExtra .= " AND C.clc_fechaElaboracion >= :fmin ";
        }

        if ($fechaMin == '' && $fechaMax != '') {
            $params[':fmax'] = $fechaMax;
            $sqlExtra .= " AND C.clc_fechaElaboracion <= :fmax ";
        }

        if ($fechaMin != '' && $fechaMax != '') {
            $sqlExtra .= " AND (C.clc_fechaElaboracion BETWEEN '$fechaMin 00:00:00' AND '$fechaMax 23:59:59') ";
            if ($anio != APP_EJERCICIO) { //cambiar al ejercico a migrar
                $params[':cFin'] = $claveFinanciamiento;
                $sqlExtra .= " AND CF.ecf_clave = :cFin ";
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= " (C.clc_fechaElaboracion BETWEEN '$fechaMin 00:00:00' AND '$fechaMax 23:59:59')";
            }
        }

        if ($clc != "") {
            $sqlExtra .= " AND C.clc_id in (" . $clc . ") ";
            if ($anio != APP_EJERCICIO) { //cambiar al ejercico a migrar
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= " C.clc_id in (" . $clc . ") ";
            }
        }
        if ($numerour != "") {
            $params[':nume'] = $numerour;
            $sqlExtra .= " AND CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) = :nume ";
        }
        if ($documento != 0) {
            $params[':docto'] = $documento;
            $sqlExtra .= " AND clc_tipoCereCo= :docto ";
        }
        if ($nombreUR != "") {
            $sqlExtra .= " AND  CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =" . $nombreUR;
            if ($anio != APP_EJERCICIO) { //cambiar al ejercico a migrar
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= ' CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =' . $nombreUR;
            }
        }
        if ($estadoClc != "") {
            if ($anio == APP_EJERCICIO) { //cambiar al ejercico a migrar
                $params[':estClc'] = $estadoClc;
                $sqlExtra .= " AND  C.ecl_id = :estClc ";
                $estado = ' WHERE ant.ecl_id = :estClc';
            } else {
                $params[':estClc'] = $estadoClc;
                $sqlCtrlAnterior = " AND IF(ant.ecl_id IS NOT NULL, ant.ecl_id, C.ecl_id) = :estClc ";
                $estado = 'WHERE ant.ecl_id = :estClc ';
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= 'C.ecl_id = :estClc ';
            }
        }
        if ($rfc != "") {
            $params[':rfc'] = $rfc;
            $sqlExtra .= " AND  doc.rfc_id= :rfc ";
        }

        switch ($estatal) {
            case 1:
                $sqlExtra .= " AND  CB.ter_id ='A'";
                break;
            case 2:
                $sqlExtra .= " AND  CB.ter_id ='B'";
                break;
        }

        $sql = consultaEstadoClc($anio, $sqlExtra, $estado, $sqlCtrlAnterior, $estado2018, $estadoCtrl2018);

        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-ConsultaClcs";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $(".detalleClc").attr("busqueda", "' . $busqueda . '");
                $(".detalleClc").click(function(){
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

    private function mainProgramacion()
    {
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

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = "gris";
        $btnregresarindex->class = "me-2";
        $btnregresarindex->icon = "fa-solid fa-circle-left me-1";
        $btnregresarindex->click = '
                // Regresa a la pantalla anterior
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
            ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $inputEjercicio = new StdClass();
        $datos = array();
        array_push($datos, APP_EJERCICIO); //se agrega el ejercicio que se iniciara
        array_push($datos, "2025");
        array_push($datos, "2024");
        array_push($datos, "2023");
        array_push($datos, "2022");
        array_push($datos, "2021");
        if ($this->serverProduccion) {
            array_push($datos, "2020");
            array_push($datos, "2019");
            array_push($datos, "2018");
            array_push($datos, "2017");
        }
        $inputEjercicio->opciones = $datos;
        $inputEjercicio->id = 'ejercicio';
        $inputEjercicio->name = 'ejercicio';
        $inputEjercicio->required = true;
        $inputEjercicio->mensaje = 'SELECCIONE EL EJERCICIO';
        $inputEjercicio->label = 'EJERCICIO';

        $inputModo = new StdClass();
        $inputModo->sql = "Select elo_id,elo_observacion FROM " . BD_TESORERIA . ".cat_EstadoLote";
        $inputModo->id = 'estado';
        $inputModo->name = 'estado';
        $inputModo->required = true;
        $inputModo->mensaje = 'SELECCIONE EL ESTADO';
        $inputModo->label = 'ESTADO';

        $clc = new StdClass();
        $clc->id = 'clc';
        $clc->name = 'clc';
        $clc->placeholder = 'NUMERO CLC';
        $clc->enDiv = true;
        $clc->label = 'NUMERO CLC';
        $clc->formato = 'entero';

        $inputNumeroUR = new StdClass();
        $inputNumeroUR->id = 'numerour';
        $inputNumeroUR->name = 'numerour';
        $inputNumeroUR->placeholder = 'NUMERO UR';
        $inputNumeroUR->enDiv = true;
        $inputNumeroUR->label = 'NUMERO UR';
        $inputNumeroUR->formato = 'entero';
        $inputNumeroUR->maxlength = '3';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "buscar";
        $btnBuscar->name = "buscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->color = "gris";
        $btnBuscar->icon = "fa fa-search me-1";
        $btnBuscar->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaTabla",
                    estado:$("#estado option:selected").text().split(" ")[0].trim(),
                    anio:$("#ejercicio option:selected").text().split(" ")[2].trim(),
                    clc:$("#clc").val(),
                    numerour:$("#numerour").val()
                })';

        $btnLimpiarCampo = new stdClass();
        $btnLimpiarCampo->id = "limpiarCampos";
        $btnLimpiarCampo->name = "limpiarCampos";
        $btnLimpiarCampo->value = "Limpiar Campos";
        $btnLimpiarCampo->icon = "trash me-2";
        $btnLimpiarCampo->color = "amarillo";
        $btnLimpiarCampo->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"mainProgramacion",
                })';

        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
            <div class="row class="col-md-12 col-xs-12 "">
                <div class="mb-4">
                <legend>ESTADOS CLC EN TESORER&Iacute;A
                </legend>
                </div>
                <fieldset class="col-md-3 col-xs-3 mb-4" id="">
                    ' . $this->ponCombo($inputEjercicio) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputModo) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($clc) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputNumeroUR) . '
                </fieldset>
            </div>

            <div class="row">
                <fieldset class="col-md-2 col-xs-2" id="">
                ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" id="">
                ' . $this->ponBoton($btnLimpiarCampo) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="divTabla" class="col-md-12 col-xs-12">
            </div>';

        $this->mixHtml($html);
    }

    private function pintaTabla()
    {
        $this->setContenedor('divTabla');
        $tabla = new stdClass();
        $tabla->id = 'tbl-clcs';
        $tabla->titulo = 'Listado de CLCs';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClc';
        $tabla->comunica->anio = '{$("#ejercicio option:selected").text().split(" ")[2].trim()}';
        $tabla->comunica->estado = '{$("#estado option:selected").text().split(" ")[0].trim()}';
        $tabla->comunica->clc = '{$("#clc").val()}';
        $tabla->comunica->numerour = '{$("#numerour").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FE. ELA.';
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
        $th->texto = 'BENEFICIARIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA DESTINO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'elo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_fechaElaboracion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mpc_nombre';
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
        $td->campoTxt = 'clc_tipoCereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_beneficiario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaClc();
    }

    private function llenaTablaClc()
    {
        $anio = isset($this->prm->anio) ? $this->prm->anio : "";
        $estado = isset($this->prm->estado) ? $this->prm->estado : "";
        $clc = isset($this->prm->clc) ? $this->prm->clc : "";
        $numerour = isset($this->prm->numerour) ? $this->prm->numerour : "";
        $sqlExtra = "";
        $params = [];
        if ($estado == "" || $anio == "" || strcmp($estado, "SELECCIONE") == 0) {
            $this->mixScript(' notify("FALTAN DATOS","error");');
            return 0;
        }
        if ($clc != "") {
            $params[':clc'] = $clc;
            $sqlExtra .= " AND C.clc_id in ( :clc ) ";
        }
        if ($numerour != "") {
            $params[':num'] = $numerour;
            $sqlExtra .= " AND CONCAT(C.gpo_id, C.ur_id) = :num ";
        }

        //separamos la consulta en propuestas, lotes
        switch ($estado) {
            case 1:
            case 2:
                //buscamos en propuesta
                $sql = "
                        SELECT L.elo_nombre ,PC.gpo_id,PC.ur_id,PC.clc_id,PC.bur_id,
                        PC.pro_importeClc AS importe,C.clc_concepto,CX.clc_cereCo ,
                        CONCAT(PC.gpo_id,PC.ur_id) as ur,
                        clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo ,C.clc_beneficiario,
                        tcl_nombre,stc_id,stc_nombre
                        FROM " . BD_TESORERIA . ".pro_Propuesta P
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc PC using(pro_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_EstadoLote L using(elo_id)
                        INNER JOIN ctrl" . $anio . ".scp_Clc C using(gpo_id,ur_id,clc_id)
                        INNER JOIN ctrl" . $anio . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN ctrl" . $anio . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN ctrl" . $anio . ".cat_TipoClc tipo using(tcl_id)
                        INNER JOIN ctrl" . $anio . ".cat_SubtipoClc subtipo using(stc_id)
                        WHERE P.elo_id = :edo " . $sqlExtra . '
                        AND P.pro_ejr = :pro_ejr ';
                $params[':pro_ejr'] = APP_EJERCICIO;
                $params[':edo'] = $estado;
                break;
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            default:
                //buscamos en lote
                $sql = "
                        SELECT
                            L.elo_nombre ,PC.gpo_id,PC.ur_id,PC.clc_id,PC.bur_id,
                            PC.lot_importeClc  AS importe,C.clc_concepto,CX.clc_cereCo ,
                            CONCAT(PC.gpo_id,PC.ur_id) as ur,
                            clc_fechaElaboracion,
                            MP.mpc_nombre ,
                            if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo ,C.clc_beneficiario,
                            tcl_nombre,stc_id,stc_nombre
                        FROM " . BD_TESORERIA . ".lot_Lote P
                        INNER JOIN " . BD_TESORERIA . ".lot_LoteClc PC using(fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_EstadoLote L using(elo_id)
                        INNER JOIN ctrl" . $anio . ".scp_Clc C using(gpo_id,ur_id,clc_id)
                        INNER JOIN ctrl" . $anio . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN ctrl" . $anio . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN ctrl" . $anio . ".cat_TipoClc tipo using(tcl_id)
                        INNER JOIN ctrl" . $anio . ".cat_SubtipoClc subtipo using(stc_id)
                        WHERE P.elo_id = :edo " . $sqlExtra . "
                        AND P.lot_ejr = :lot AND P.lot_ejercicio= :lot_ejr ";
                $params[':edo'] = $estado;
                $params[':lot'] = APP_EJERCICIO;
                $params[':lot_ejr'] = $anio;
                break;
        }

        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-clcs";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    private function limpiarCampos()
    {
        $this->mainProgramacion();
    }

    /**
     * FUNCIONES NUEVAS PARA CONSULTA DE LOTES
     */
    private function pintaConsultaLotes()
    {
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->lot_tipoCereCo = isset($this->prm->lot_tipoCereCo) ? $this->prm->lot_tipoCereCo : '';
        $this->prm->tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';
        $this->prm->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : '';
        $this->prm->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : '';
        $this->prm->lote = isset($this->prm->lote) ? $this->prm->lote : '';
        $this->prm->pro_id = isset($this->prm->pro_id) ? $this->prm->pro_id : '';
        $this->prm->buscar = isset($this->prm->buscar) ? $this->prm->buscar : false;

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnregresarindex';
        $btnRegresarIndex->name = 'btnregresarindex';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->type = 'button';
        $btnRegresarIndex->class = 'me-2';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresarIndex->click = '
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
            ';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar';
        $btnBuscar->name = 'btnBuscar';
        $btnBuscar->text = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->type = 'button';
        $btnBuscar->class = 'me-2';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'fa fa-search me-1';
        $btnBuscar->click = '
                comunica("' . $this->pantalla . '",{
                    metodo: "llenaTablaLotes",
                    lot_ejercicio : $("#comboEjercicio").val(),
                    fpa_id : $("#comboFormaPago").val(),
                    lot_tipoCereCo : $("#comboDocumento").val(),
                    tlo_id : $("#comboTipoLote").val(),
                    fechaMin : $("#inputFechaMin").val(),
                    fechaMax : $("#inputFechaMax").val(),
                    lote : $("#inputLote").val(),
                    pro_id : $("#inputPropuesta").val()
                });
            ';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = 'me-2';
        $btnLimpiar->color = 'amarillo';
        $btnLimpiar->icon = 'trash me-1';
        $btnLimpiar->click = '
                $("#comboEjercicio").val("");
                $("#comboFormaPago").val("");
                $("#comboDocumento").val("");
                $("#comboTipoLote").val("");
                $("#inputFechaMin").val("");
                $("#inputFechaMax").val("");
                $("#inputLote").val("");
                $("#inputPropuesta").val("");
                $(".selectpicker").selectpicker("refresh");
            ';

        $btnReporteExcel = new stdClass();
        $btnReporteExcel->id = 'btnReporteExcel';
        $btnReporteExcel->name = 'btnReporteExcel';
        $btnReporteExcel->text = 'Exportar Excel';
        $btnReporteExcel->value = 'Exportar Excel';
        $btnReporteExcel->type = 'button';
        $btnReporteExcel->class = 'me-2';
        $btnReporteExcel->color = 'verde';
        $btnReporteExcel->icon = 'fa-solid fa-file-excel me-2';
        //$btnReporteExcel->disabled = true;
        $btnReporteExcel->click = '
                $("#loteRep").prop("action","rep/excel/tes/consulta/ExcelReporteLote.php");
                $("#loteRep").submit();
            ';

        $btnReporteExcelD = new stdClass();
        $btnReporteExcelD->id = 'btnReporteExcelD';
        $btnReporteExcelD->name = 'btnReporteExcelD';
        $btnReporteExcelD->text = 'Exportar Excel Detallado';
        $btnReporteExcelD->value = 'Exportar Excel Detallado';
        $btnReporteExcelD->type = 'button';
        $btnReporteExcelD->class = 'me-2';
        $btnReporteExcelD->color = 'verde';
        $btnReporteExcelD->icon = 'fa-solid fa-file-excel me-2';
        //$btnReporteExcelD->disabled = true;
        $btnReporteExcelD->click = '
                $("#loteRep").prop("action","rep/excel/tes/consulta/ExcelReporteLoteDetallado.php");
                $("#loteRep").submit();
            ';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $this->agregaTools($this->ponBoton($btnBuscar));
        $this->agregaTools($this->ponBoton($btnLimpiar));
        $this->agregaTools($this->ponBoton($btnReporteExcel));
        $this->agregaTools($this->ponBoton($btnReporteExcelD));

        $datos = array();
        $datos[0] = APP_EJERCICIO;
        $datos[1] = '2025';
        $datos[2] = '2024';
        $datos[3] = '2023';
        $datos[4] = '2022';
        $datos[5] = "2021"; //se agrega el nuevo ejercicio
        if ($this->serverProduccion) {
            $datos[6] = '2020';
            $datos[7] = '2019';
            $datos[8] = '2018';
            $datos[9] = '2017';
        }
        $comboEjercicio = new StdClass();
        $comboEjercicio->opciones = $datos;
        $comboEjercicio->id = 'comboEjercicio';
        $comboEjercicio->name = 'comboEjercicio';
        $comboEjercicio->required = true;
        $comboEjercicio->mensaje = 'SELECCIONE EL EJERCICIO';
        $comboEjercicio->label = 'EJERCICIO';

        $datos = array();
        $datos[0] = 'TODOS';
        $datos[1] = 'OTB-CERE';
        $datos[2] = 'OP-CERECO';
        $comboDocumento = new StdClass();
        $comboDocumento->opciones = $datos;
        $comboDocumento->id = 'comboDocumento';
        $comboDocumento->name = 'comboDocumento';
        $comboDocumento->required = true;
        $comboDocumento->mensaje = 'SELECCIONE EL DOCUMENTO';
        $comboDocumento->label = 'DOCUMENTO';

        $sql = '
                SELECT 0 AS fpa_id, "TODOS" AS fpa_nombre
                UNION
                SELECT fpa_id, fpa_nombre FROM ' . BD_TESORERIA . '.cat_FormaPago
            ';
        $comboFormaPago = new StdClass();
        $comboFormaPago->sql = $sql;
        $comboFormaPago->id = 'comboFormaPago';
        $comboFormaPago->name = 'comboFormaPago';
        $comboFormaPago->required = true;
        $comboFormaPago->mensaje = 'SELECCIONE LA FORMA DE PAGO';
        $comboFormaPago->label = 'FORMA DE PAGO';

        $sql = '
                SELECT 0 AS tlo_id, "TODOS" AS tlo_nombre
                UNION
                SELECT tlo_id, tlo_nombre FROM ' . BD_TESORERIA . '.cat_TipoLote
                WHERE tlo_id IN (1,2,4,5,6,7)
            ';
        $comboTipoLote = new StdClass();
        $comboTipoLote->sql = $sql;
        $comboTipoLote->id = 'comboTipoLote';
        $comboTipoLote->name = 'comboTipoLote';
        $comboTipoLote->required = true;
        $comboTipoLote->mensaje = 'SELECCIONE EL TIPO DE LOTE';
        $comboTipoLote->label = 'TIPO DE LOTE';

        $inputFechaMin = new StdClass();
        $inputFechaMin->id = 'inputFechaMin';
        $inputFechaMin->name = 'inputFechaMin';
        $inputFechaMin->enDiv = true;
        $inputFechaMin->label = 'FECHA MÍNIMA';
        $inputFechaMin->placeholder = 'FECHA MÍNIMA';
        $inputFechaMin->formato = 'fecha';
        $inputFechaMin->readonly = true;

        $inputFechaMax = new StdClass();
        $inputFechaMax->id = 'inputFechaMax';
        $inputFechaMax->name = 'inputFechaMax';
        $inputFechaMax->enDiv = true;
        $inputFechaMax->label = 'FECHA MÁXIMA';
        $inputFechaMax->placeholder = 'FECHA MÁXIMA';
        $inputFechaMax->formato = 'fecha';
        $inputFechaMax->readonly = true;

        $inputLote = new StdClass();
        $inputLote->id = 'inputLote';
        $inputLote->name = 'inputLote';
        $inputLote->enDiv = true;
        $inputLote->label = 'LOTE';
        $inputLote->placeholder = 'LOTE';

        $inputPropuesta = new StdClass();
        $inputPropuesta->id = 'inputPropuesta';
        $inputPropuesta->name = 'inputPropuesta';
        $inputPropuesta->enDiv = true;
        $inputPropuesta->label = 'PROPUESTA';
        $inputPropuesta->placeholder = 'PROPUESTA';

        // * TABLA
        $tabla = new stdClass();
        $tabla->id = 'tablaLotes';
        $tabla->titulo = 'LISTADO DE LOTES';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaLotes';
        $tabla->comunica->lot_ejercicio = '{$("#comboEjercicio").val()}';
        $tabla->comunica->fpa_id = '{$("#comboFormaPago").val()}';
        $tabla->comunica->lot_tipoCereCo = '{$("#comboDocumento").val()}';
        $tabla->comunica->lot_flujoEfectivo = '{$("#comboFlujoEfectivo").val()}';
        $tabla->comunica->tlo_id = '{$("#comboTipoLote").val()}';
        $tabla->comunica->fechaMin = '{$("#inputFechaMin").val()}';
        $tabla->comunica->fechaMax = '{$("#inputFechaMax").val()}';
        $tabla->comunica->lote = '{$("#inputLote").val()}';
        $tabla->comunica->pro_id = '{$("#inputPropuesta").val()}';

        // * CABECERA DE LA TABLA
        $tr = array();

        $th = new stdClass();
        $th->texto = 'MODO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO LOTE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC\'s';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROPUESTA';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DETALLE LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        // *CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'modo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tipo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'elo_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaGeneracion';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_importe';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'elementos';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'propuesta_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'elo_id';
        $td->casos->valor = '1,2,3,4,5,6,7,8';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'fa fa-eye';
        $td->casos->verdadero->class = 'detalleLote center-block';
        $td->casos->verdadero->name = 'detalleLote';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->lot_id = 'lot_id';
        $td->casos->verdadero->attr->fpa_id = 'fpa_id';
        $td->casos->verdadero->attr->msc_id = 'msc_id';
        $td->casos->verdadero->attr->lot_ejercicio = 'lot_ejercicio';
        $td->casos->verdadero->attr->tlo_id = 'tlo_id';
        $td->casos->verdadero->attr->lot = 'lote';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
                <div class="row mb-4">
                    <legend>Consulta de lotes</legend>
                </div>
                <div class="row">
                    <form method="post" target="_blank" id="loteRep">
                    <div class="container-fluid">
                        <div class="row mb-3">
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboEjercicio) . '</fieldset>
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboDocumento) . '</fieldset>
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboFormaPago) . '</fieldset>
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboTipoLote) . '</fieldset>
                        </div>
                        <div class="row mb-4">
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFechaMin) . '</fieldset>
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFechaMax) . '</fieldset>
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputLote) . '</fieldset>
                            <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputPropuesta) . '</fieldset>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</fieldset>
                </div>
            ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#inputPropuesta").on("input", function () {
                this.value = this.value
                    .replace(/[^0-9]/g, "")
                    .replace(/^0+/, "");
            });
        ');
        if (isset($this->prm->busqueda)) {
            $this->mixScript('
                    $("#comboEjercicio").val(' . $this->prm->lot_ejercicio . ');
                    $("#comboDocumento").val(' . $this->prm->lot_tipoCereCo . ');
                    $("#comboFormaPago").val(' . $this->prm->fpa_id . ');
                    $("#comboTipoLote").val(' . $this->prm->tlo_id . ');
                    $("#inputFechaMin").val("' . $this->prm->fechaMin . '");
                    $("#inputFechaMax").val("' . $this->prm->fechaMax . '");
                    $("#inputLote").val("' . $this->prm->lote . '");
                    $("#inputPropuesta").val("' . $this->prm->pro_id . '");
                    $(".selectpicker").selectpicker("refresh");
                ');
            $this->llenaTablaLotes();
        }
    }

    private function llenaTablaLotes()
    {
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->lot_tipoCereCo = isset($this->prm->lot_tipoCereCo) ? $this->prm->lot_tipoCereCo : '';
        $this->prm->tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';
        $this->prm->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : '';
        $this->prm->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : '';
        $this->prm->lote = isset($this->prm->lote) ? $this->prm->lote : '';
        $this->prm->pro_id = isset($this->prm->pro_id) ? $this->prm->pro_id : '';

        $busqueda = $this->prm->lot_ejercicio . '|' . $this->prm->fpa_id . '|' . $this->prm->lot_tipoCereCo . '|' . $this->prm->tlo_id . '|' . $this->prm->fechaMin . '|' . $this->prm->fechaMax . '|' . $this->prm->lote . '|' . $this->prm->pro_id;

        $busquedaArr = explode('|', $busqueda); //Guardamos los valores de la busqueda para el boton regresar de la pantalla anterior

        // Se separa la consulta para no duplicar, se reliaza el llamdado del archivo que contiene la consulta
        $sql = consultaLote($this->prm->lot_ejercicio, $this->prm->fpa_id, $this->prm->lot_tipoCereCo, $this->prm->tlo_id, $this->prm->fechaMin, $this->prm->fechaMax, $this->prm->lote, $this->prm->pro_id);
        $this->setSql($sql);
        // echo  $this->getSql();
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaLotes";
        //$nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 5;
        $nav->nf = -1;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $("#btnReporteExcel").removeAttr("disabled");
                $("#btnReporteExcelD").removeAttr("disabled");

                $(".detalleLote").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "pintaDetalleLote",
                        fpa_id: $(this).attr("fpa_id"),
                        msc_id: $(this).attr("msc_id"),
                        lot_ejercicio: $(this).attr("lot_ejercicio"),
                        lot_id: $(this).attr("lot_id"),
                        tlo_id: $(this).attr("tlo_id"),
                        lot: $(this).attr("lot"),
                        busquedaArr: ' . json_encode($busquedaArr) . '
                    });
                });

                $(".detallePropuesta").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "pintaDetallePropuesta",
                        pro_id: $(this).attr("pro_id"),
                        busquedaArr: ' . json_encode($busquedaArr) . '
                    });
                });
            ');
    }

    private function pintaDetalleLote()
    {
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : '';
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : '';
        $this->prm->tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';
        $this->prm->lot = isset($this->prm->lot) ? $this->prm->lot : '';
        $this->prm->busquedaArr = isset($this->prm->busquedaArr) ? $this->prm->busquedaArr : '';

        $busqueda = '';
        if (!empty($this->prm->busquedaArr)) {
            $busqueda .= ',
                    lot_ejercicio: "' . $this->prm->busquedaArr[0] . '",
                    fpa_id: "' . $this->prm->busquedaArr[1] . '",
                    lot_tipoCereCo: "' . $this->prm->busquedaArr[2] . '",
                    tlo_id: "' . $this->prm->busquedaArr[3] . '",
                    fechaMin: "' . $this->prm->busquedaArr[4] . '",
                    fechaMax: "' . $this->prm->busquedaArr[5] . '",
                    lote: "' . $this->prm->busquedaArr[6] . '",
                    propuesta: "' . $this->prm->busquedaArr[7] . '"
                ';
        }

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnregresarindex';
        $btnRegresarIndex->name = 'btnregresarindex';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->type = 'button';
        $btnRegresarIndex->class = 'me-2';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresarIndex->click = '
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaConsultaLotes",
                    busqueda: true' . $busqueda . '
                });
            ';

        $btnReporteExcel = new stdClass();
        $btnReporteExcel->id = 'btnReporteExcel';
        $btnReporteExcel->name = 'btnReporteExcel';
        $btnReporteExcel->text = 'Exportar Excel';
        $btnReporteExcel->value = 'Exportar Excel';
        $btnReporteExcel->type = 'button';
        $btnReporteExcel->class = 'me-2';
        $btnReporteExcel->color = 'verde';
        $btnReporteExcel->icon = 'fa-solid fa-file-excel me-2';

        $btnReporteExcelMun = new stdClass();
        $btnReporteExcelMun->id = 'btnReporteExcelMun';
        $btnReporteExcelMun->name = 'btnReporteExcelMun';
        $btnReporteExcelMun->text = 'Exportar Excel';
        $btnReporteExcelMun->value = 'Exportar por documentos';
        $btnReporteExcelMun->type = 'button';
        $btnReporteExcelMun->class = 'me-2';
        $btnReporteExcelMun->color = 'verde';
        $btnReporteExcelMun->icon = 'fa-solid fa-file-excel me-2';

        $btnReporteExcelPDP = new stdClass();
        $btnReporteExcelPDP->id = 'btnReporteExcelPDP';
        $btnReporteExcelPDP->name = 'btnReporteExcelPDP';
        $btnReporteExcelPDP->text = 'Exportar Excel';
        $btnReporteExcelPDP->value = 'Exportar por documentos';
        $btnReporteExcelPDP->type = 'button';
        $btnReporteExcelPDP->class = 'me-2';
        $btnReporteExcelPDP->color = 'verde';
        $btnReporteExcelPDP->icon = 'fa-solid fa-file-excel me-2';

        $btnReportePDF = new stdClass();
        $btnReportePDF->id = 'btnReportePDF';
        $btnReportePDF->name = 'btnReportePDF';
        $btnReportePDF->text = 'Exportar Excel';
        $btnReportePDF->value = 'Exportar PDF';
        $btnReportePDF->type = 'button';
        $btnReportePDF->class = 'me-2';
        $btnReportePDF->color = 'rojo';
        $btnReportePDF->icon = 'fa fa-file-pdf me-2';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $this->agregaTools($this->ponBoton($btnReporteExcel));
        if ($this->prm->tlo_id == 4) {
            $this->agregaTools($this->ponBoton($btnReporteExcelMun));
        } elseif ($this->prm->tlo_id == 2) {
            $this->agregaTools($this->ponBoton($btnReporteExcelPDP));
        }
        $this->agregaTools($this->ponBoton($btnReportePDF));

        /** TABLA **/
        $tabla = new stdClass();
        $tabla->id = 'tablaDetalleLote';
        $tabla->titulo = 'DETALLE DEL LOTE ' . $this->prm->lot;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetalleLote';
        $tabla->comunica->fpa_id = $this->prm->fpa_id;
        $tabla->comunica->msc_id = $this->prm->msc_id;
        $tabla->comunica->lot_ejercicio = $this->prm->lot_ejercicio;
        $tabla->comunica->lot_id = $this->prm->lot_id;
        $tabla->comunica->tlo_id = $this->prm->tlo_id;

        /** CABECERA DE LA TABLA **/
        $tr = array();

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA GENERACIÓN';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA PAGO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA GENERACIÓN CERE/CERECO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '4rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->width = '4rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CERE/CERECO';
        $th->width = '4rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
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
        $th->texto = 'CTA ORIGEN';
        $th->width = '14rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        $th->align = 'center';
        array_push($tr, $th);

        if ($this->prm->tlo_id == 5 && $this->prm->fpa_id == 3) {
            $th = new stdClass();
            $th->texto = 'LINEA DE CAPTURA';
            $th->align = 'center';
            array_push($tr, $th);
        } else {
            $th = new stdClass();
            $th->texto = 'CTA DESTINO';
            $th->width = '14rem';
            $th->align = 'center';
            array_push($tr, $th);
        }

        if ($this->prm->tlo_id != 6 && $this->prm->tlo_id != 7) {
            $th = new stdClass();
            $th->texto = 'LOTE EGRESO';
            $th->align = 'center';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'CVE FIN';
        $th->align = 'center';
        array_push($tr, $th);

        if ($this->prm->tlo_id == 6 && $this->prm->tlo_id == 7) {
            $th = new stdClass();
            $th->texto = 'CUENTA CONTABLE';
            $th->align = 'center';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'ESTADO';
        $th->align = 'center';
        array_push($tr, $th);

        /**  CUERPO DE LA TABLA **/
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'lote';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaGeneracion';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaPago';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_generacionCereCo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'beneficiario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'origen';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'banco';
        $td->align = 'center';
        array_push($trbody, $td);

        if ($this->prm->tlo_id == 5 && $this->prm->fpa_id == 3) {
            $td = new stdClass();
            $td->campoTxt = 'linea';
            $td->align = 'center';
            array_push($trbody, $td);
        } else {
            $td = new stdClass();
            $td->campoTxt = 'destino';
            $td->align = 'center';
            array_push($trbody, $td);
        }

        if ($this->prm->tlo_id != 6 && $this->prm->tlo_id != 7) {
            $td = new stdClass();
            $td->campoTxt = 'lote_egreso';
            $td->align = 'center';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'ecf_clave';
        $td->align = 'center';
        array_push($trbody, $td);

        if ($this->prm->tlo_id == 6 && $this->prm->tlo_id == 7) {
            $td = new stdClass();
            $td->campoTxt = 'contable';
            $td->align = 'center';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'estado';
        $td->align = 'center';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        ' . $this->wrPonTabla($tabla) . '
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12" id="contenedorDetalle"></fieldset>
                </div>
            ';

        $this->mixHtml($html);
        $this->llenaTablaDetalleLote();

        if ($this->prm->tlo_id == 4) {
            $this->pintaDetalleMunicipios();
        } elseif ($this->prm->tlo_id == 2) {
            $this->pintaDetallePDP();
        }
    }

    private function llenaTablaDetalleLote()
    {
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : '';
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : '';
        $this->prm->tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';

        if (empty($this->prm->fpa_id) || empty($this->prm->msc_id) || empty($this->prm->lot_ejercicio) || empty($this->prm->lot_id) || empty($this->prm->tlo_id)) {
            $this->mixScript('
                    notify("Faltan datos", "error");
                ');
            return;
        }

        $sql = '';
        $sqlGroupBy = '';
        switch ($this->prm->tlo_id) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 8:
                if ($this->prm->lot_ejercicio == APP_EJERCICIO) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,"" lote_egreso,cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } elseif ($this->prm->lot_ejercicio == 2025) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,"" lote_egreso,cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2025 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2025 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant on (ant.ejr_id_clc = 2025 and ant.gpo_id = c.gpo_id and ant.ur_id = c.ur_id AND ant.clc_id = c.clc_id)
                            INNER JOIN ' . BD_CTRL_2025 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)';
                } elseif ($this->prm->lot_ejercicio == 2024) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,"" lote_egreso,cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2024 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2024 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant on (ant.ejr_id_clc = 2024 and ant.gpo_id = c.gpo_id and ant.ur_id = c.ur_id AND ant.clc_id = c.clc_id)
                            INNER JOIN ' . BD_CTRL_2024 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)';
                } elseif ($this->prm->lot_ejercicio == 2023) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,"" lote_egreso,cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2023 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2023 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant on (ant.ejr_id_clc = 2023 and ant.gpo_id = c.gpo_id and ant.ur_id = c.ur_id AND ant.clc_id = c.clc_id)
                            INNER JOIN ' . BD_CTRL_2023 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)';

                } elseif ($this->prm->lot_ejercicio == 2022) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,"" lote_egreso,cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2022 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2022 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL_2022 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } elseif ($this->prm->lot_ejercicio == 2021) {
                    $sql .= '
                            SELECT
                            l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                            min(iba.iba_nombre) as banco,
                            CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                LPAD(l.msc_id, 2, "0"),
                                LPAD(l.lot_id, 4, "0"),
                                SUBSTR(l.lot_ejercicio, 3)
                            ) AS lote,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,"" lote_egreso,cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } elseif ($this->prm->lot_ejercicio == 2020) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,"" lote_egreso,cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2020 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2020 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL_2020 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } elseif ($this->prm->lot_ejercicio == 2019) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                                cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2019 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2019 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL_2019 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                            #LEFT JOIN ' . BD_CTRL_2019 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                            #LEFT JOIN ' . BD_CTRL_2019 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            ';
                } elseif ($this->prm->lot_ejercicio == 2018) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, min(cbs.cbs_id) AS origen,
                                min(iba.iba_nombre) as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                                cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                            LEFT JOIN ' . BD_CTRL_2018 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2018 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
                } elseif ($this->prm->lot_ejercicio == 2017) {
                    $sql .= '
                            SELECT
                                l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.bsf_id AS origen,
                                iba.iba_nombre as banco,
                                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                                    LPAD(l.msc_id, 2, "0"),
                                    LPAD(l.lot_id, 4, "0"),
                                    SUBSTR(l.lot_ejercicio, 3)
                                ) AS lote,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                                    cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.enc_BancosSefinCveFin AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_BancosSefin AS cbs USING (bsf_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.cat_InstitucionesBancarias AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
                            LEFT JOIN ' . BD_CTRL_2017 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2017 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
                }

                $sqlGroupBy .= 'GROUP BY c.gpo_id, c.ur_id, c.clc_id';
                break;
            case 6:
            case 7:
                $sql = '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, "" AS clc_generacionCereCo,
                            CONCAT(lc.gpo_id, LPAD(lc.ur_id,2,"0")) AS ur, lc.clc_id AS clc,
                            lc.lom_beneficiario AS beneficiario, lc.lom_concepto AS concepto, lc.lom_importe AS importe,
                            lc.cbs_id AS origen, iba.iba_abreviatura AS banco, e.elo_nombre AS estado,
                            CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                                LPAD(l.msc_id, 2, "0"),
                                LPAD(l.lot_id, 4, "0"),
                                SUBSTR(l.lot_ejercicio, 3)
                            ) AS lote,
                            lc.cbb_id AS destino, ecf.ecf_clave, "" AS lote_egreso, pc.cta_cuenta AS contable,
                            "" AS clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        LEFT JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        LEFT JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING (cbs_id)
                        LEFT JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        LEFT JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS e ON(e.elo_id = l.elo_id)
                        LEFT JOIN ' . BD_TESORERIA . '.vin_PropuestaManualClaveFinanciamiento AS vpcf USING (lom_id)
                        LEFT JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento AS ecf USING(ecf_id)
                        LEFT JOIN ' . BD_TESORERIA . '.vin_PropuestaManualPlanCuenta AS vlom USING (lom_id)
                        LEFT JOIN ' . BD_CTRL . '.cg_PlanCuentas AS pc USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)';

                $sqlGroupBy .= 'GROUP BY lc.lom_id';
                break;
        } //fin switch
        $params = [];
        if (!empty($sql)) {
            $sql .= '
                        WHERE l.lot_ejr = :lot_ejr AND lc.fpa_id = :fpa
                        AND lc.msc_id = :msc
                        AND lc.lot_ejercicio = :lot_ejercicio
                        AND lc.lot_id =  :lot_id
                        ' . $sqlGroupBy . '
                        ORDER BY importe ASC
                ';
            $params = [
                ':lot_ejr' => APP_EJERCICIO,
                ':fpa' => $this->prm->fpa_id,
                ':msc' => $this->prm->msc_id,
                ':lot_ejercicio' => $this->prm->lot_ejercicio,
                ':lot_id' => $this->prm->lot_id,
            ];
            $this->setSql($sql);
            $this->setParams($params);
            $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
            $nav->idTabla = "tablaDetalleLote";
            $nav->nf = -1;
            $this->wrLlenaTabla($nav);

            $this->mixScript('
                    $("#btnReporteExcel").click(function() {
                        imprimeReporteDetLote();
                    });

                    $("#btnReportePDF").click(function() {
                        imprimeReporteDetLotePDF();
                    });

                    $("#btnReporteExcelMun").click(function() {
                        imprimeReporteDetLoteMunicipio();
                    });

                    $("#btnReporteExcelPDP").click(function() {
                        imprimeReporteDetLotePDP();
                    });

                    function imprimeReporteDetLote() {
                        var form = $("<form>")
                            .attr("action", "rep/excel/tes/consulta/ExcelReporteDetalleLote.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .attr("id", "formDetLote")
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "fpa_id")
                            .val("' . $this->prm->fpa_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "msc_id")
                            .val("' . $this->prm->msc_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_ejercicio").
                            val("' . $this->prm->lot_ejercicio . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_id")
                            .val("' . $this->prm->lot_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "tlo_id")
                            .val("' . $this->prm->tlo_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lote")
                            .val("' . $this->prm->lot . '"))
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    }

                    function imprimeReporteDetLotePDF(){
                        var form = $("<form>")
                            .attr("action", "rep/doc/tes/consulta/PDFReportePropuesta.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .attr("id", "formDetLotePDF")
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "fpa_id")
                            .val("' . $this->prm->fpa_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "msc_id")
                            .val("' . $this->prm->msc_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_ejercicio")
                            .val("' . $this->prm->lot_ejercicio . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_id")
                            .val("' . $this->prm->lot_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_tipo")
                            .val("' . $this->prm->tlo_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lote")
                            .val("' . $this->prm->lot . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "usr_id")
                            .val("' . $this->usr_id . '"))
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    }

                    function imprimeReporteDetLoteMunicipio(){
                        var form = $("<form>")
                            .attr("action", "rep/excel/tes/consulta/ExcelDetalleMunicipiosDocumentos.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .attr("id", "formDetLoteMun")
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "fpa_id")
                            .val("' . $this->prm->fpa_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "msc_id")
                            .val("' . $this->prm->msc_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_ejercicio").
                            val("' . $this->prm->lot_ejercicio . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_id")
                            .val("' . $this->prm->lot_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lote")
                            .val("' . $this->prm->lot . '"))
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    }

                    function imprimeReporteDetLotePDP(){
                        var form = $("<form>")
                            .attr("action", "rep/excel/tes/consulta/ExcelDetallePDPDocumentos.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .attr("id", "formDetLotePDP")
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "fpa_id")
                            .val("' . $this->prm->fpa_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "msc_id")
                            .val("' . $this->prm->msc_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_ejercicio").
                            val("' . $this->prm->lot_ejercicio . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lot_id")
                            .val("' . $this->prm->lot_id . '"))
                        .append($("<input>")
                            .attr("type", "hidden")
                            .attr("name", "lote")
                            .val("' . $this->prm->lot . '"))
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    }
                ');
        } //fin if
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
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->type = 'button';
        $btnRegresarIndex->class = 'me-1';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->icon = 'fa-solid fa-circle-left me-1';
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
        $btnReporteExcel->value = 'Exportar Excel';
        $btnReporteExcel->type = 'button';
        $btnReporteExcel->class = 'me-1';
        $btnReporteExcel->color = 'verde';
        $btnReporteExcel->icon = 'fa fa-file-excel me-1';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $this->agregaTools($this->ponBoton($btnReporteExcel));

        /** TABLA **/
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

        /** CABECERA DE LA TABLA **/
        $tr = array();

        $th = new stdClass();
        $th->texto = 'AFECTACION';
        $th->width = '4rem';
        $th->align = 'center';

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

        /** CUERPO DE LA TABLA **/
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        $td->align = 'center';

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

        $this->llenaTablaDetalleClc();
    }

    private function llenaTablaDetalleClc()
    {
        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : '';
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : '';
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $params = [];
        if (empty($this->prm->gpo_id) || empty($this->prm->ur_id) || empty($this->prm->clc_id) || empty($this->prm->ejercicio)) {
            $this->mixScript('
                    notify("Faltan datos", "Error");
                ');
            return;
        }

        $sql = '
                SELECT s.gpo_id, s.ur_id, s.clc_id, s.bdo_id, max(s.afe_id)afe_id, max(s.id)id, max(s.concepto)concepto, max(s.referencia)referencia, max(s.fechaReferencia)fechaReferencia, s.rfc_id,
                    SUM(s.importe)importe, SUM(s.iva)iva, SUM(s.neto)neto,
                    max(s.cuenta)cuenta, max(s.proveedor)proveedor, max(s.bdo_referencia)bdo_referencia
                FROM (';

        switch ($this->prm->ejercicio) {
            case 2017:
                $params = [];
                $sql .= '
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id)rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, SUM(ncc_percepcion - ncc_retencion) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2017 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2017 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2017 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND clc_id = :clc
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
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
                        INNER JOIN ' . BD_CTRL_2017 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND clc_id = :clc ';
                $params[':gpo'] = $this->prm->gpo_id;
                $params[':ur'] = $this->prm->ur_id;
                $params[':clc'] = $this->prm->clc_id;
                break;
            case 2018:
                $params = [];
                $sql .= '
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, SUM(ncc_percepcion - ncc_retencion) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2018 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2018 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND clc_id = :clc
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
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
                        INNER JOIN ' . BD_CTRL_2018 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND clc_id = :clc
                ';
                $params[':gpo'] = $this->prm->gpo_id;
                $params[':ur'] = $this->prm->ur_id;
                $params[':clc'] = $this->prm->clc_id;
                break;
            case 2019:
                $params = [];
                $sql .= '
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id)rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, SUM(ncc_percepcion - ncc_retencion) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2019 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2019 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2019 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND clc_id = :clc
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                        UNION ALL
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                            ncp_concepto AS concepto, ncp_referencia AS referencia,
                            ncp_fechaReferencia AS fechaReferencia,
                            /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                            pre_claveCompleta AS cuenta,
                            CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2019 . '.scp_Presupuestal AS c
                        INNER JOIN ' . BD_CTRL_2019 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2019 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND clc_id = :clc';
                $params[':gpo'] = $this->prm->gpo_id;
                $params[':ur'] = $this->prm->ur_id;
                $params[':clc'] = $this->prm->clc_id;
                break;
            case 2020:
                $sql .= '
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, SUM(ncc_percepcion - ncc_retencion) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2020 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2020 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2020 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                        UNION ALL
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                            ncp_concepto AS concepto, ncp_referencia AS referencia,
                            ncp_fechaReferencia AS fechaReferencia,
                            /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
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
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, SUM(ncc_percepcion - ncc_retencion) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2021 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2021 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                        UNION ALL
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                            ncp_concepto AS concepto, ncp_referencia AS referencia,
                            ncp_fechaReferencia AS fechaReferencia,
                            /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
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
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(CAST(ncc_percepcion AS SIGNED)- CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, SUM(CAST(ncc_percepcion  AS SIGNED)- CAST(ncc_retencion AS SIGNED)) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2022 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2022 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2022 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                        UNION ALL
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                            ncp_concepto AS concepto, ncp_referencia AS referencia,
                            ncp_fechaReferencia AS fechaReferencia,
                            /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
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
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(CAST(ncc_percepcion AS SIGNED)- CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, SUM(CAST(ncc_percepcion  AS SIGNED)- CAST(ncc_retencion AS SIGNED)) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2023 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2023 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2023 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                        UNION ALL
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                            ncp_concepto AS concepto, ncp_referencia AS referencia,
                            ncp_fechaReferencia AS fechaReferencia,
                            /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
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
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, SUM(CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2024 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2024 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2024 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                        UNION ALL
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                            ncp_concepto AS concepto, ncp_referencia AS referencia,
                            ncp_fechaReferencia AS fechaReferencia,
                            /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
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
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                            MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                            MAX(ncc_fechaReferencia) AS fechaReferencia,
                            SUM(CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, SUM(CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS neto,
                            MAX(cta_cuenta) AS cuenta,
                            MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2025 . '.scp_Contable AS c
                        INNER JOIN ' . BD_CTRL_2025 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2025 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                        UNION ALL
                        SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                            ncp_concepto AS concepto, ncp_referencia AS referencia,
                            ncp_fechaReferencia AS fechaReferencia,
                            /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                            pre_claveCompleta AS cuenta,
                            CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                            "" AS bdo_referencia
                        FROM ' . BD_CTRL_2025 . '.scp_Presupuestal AS c
                        INNER JOIN ' . BD_CTRL_2025 . '.cat_Proveedor USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                        INNER JOIN ' . BD_CTRL_2025 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2026:
                $sql .= '
                            SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, MAX(rfc_id) rfc_id, MAX(ncc_id) AS id,
                                MAX(ncc_concepto) AS concepto, MAX(ncc_referencia) AS referencia,
                                MAX(ncc_fechaReferencia) AS fechaReferencia,
                                SUM(CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, SUM(CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS neto,
                                MAX(cta_cuenta) AS cuenta,
                                MAX(CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno)) AS proveedor,
                                "" AS bdo_referencia
                            FROM ' . BD_CTRL . '.scp_Contable AS c
                            INNER JOIN ' . BD_CTRL . '.cat_Proveedor USING (rfc_id)
                            INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                            INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                            GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
                            UNION ALL
                            SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                                ncp_concepto AS concepto, ncp_referencia AS referencia,
                                ncp_fechaReferencia AS fechaReferencia,
                                /*ncp_percepcionBruto*/ ncp_percepcionNeto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                                pre_claveCompleta AS cuenta,
                                CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                                "" AS bdo_referencia
                            FROM ' . BD_CTRL . '.scp_Presupuestal AS c
                            INNER JOIN ' . BD_CTRL . '.cat_Proveedor USING (rfc_id)
                            INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                            INNER JOIN ' . BD_CTRL . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
        } // fin switch

        $sql .= '
                ) AS s
                WHERE gpo_id = :gpo_id
                AND ur_id = :ur_id
                AND clc_id = :clc_id
                GROUP BY s.bdo_id, s.rfc_id
                ORDER BY afe_id, id';
        $params[':gpo_id'] = $this->prm->gpo_id;
        $params[':ur_id'] = $this->prm->ur_id;
        $params[':clc_id'] = $this->prm->clc_id;
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tablaDetalleClc";
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $("#btnReporteExcel").click(function(){
                    imprimeReporteClc();
                });

                function imprimeReporteClc(){
                    var form = $("<form>")
                            .attr("action", "rep/excel/tes/consulta/ExcelReporteDetalleClc.php")
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

    private function pintaDetalleMunicipios()
    {
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : '';
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : '';

        if (empty($this->prm->fpa_id) || empty($this->prm->msc_id) || empty($this->prm->lot_ejercicio) || empty($this->prm->lot_id)) {
            $this->mixScript('
                    notify("Faltan datos", "Error");
                ');
            return;
        }

        $this->setContenedor('contenedorDetalle');
        $tabla = new stdClass();
        $tabla->id = 'tblDetalleMunicipios';
        $tabla->titulo = 'Detalle Municipios';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaDetalleMunicipios';
        $tabla->comunica->fpa_id = $this->prm->fpa_id;
        $tabla->comunica->msc_id = $this->prm->msc_id;
        $tabla->comunica->lot_ejercicio = $this->prm->lot_ejercicio;
        $tabla->comunica->lot_id = $this->prm->lot_id;

        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA DESTINO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DOCUMENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ctaCargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ctaAbono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'orden';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'benef';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaDetalleMunicipios();
    }

    private function llenaDetalleMunicipios()
    {
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : '';
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : '';
        $params = [];
        $sql = '
                SELECT
                    concat(lc.gpo_id, lc.ur_id) AS ur, lc.clc_id, ecf_id,
                    m.cbb_id AS ctaAbono, tlo_id, sf.cbs_id AS ctaCargo,
                    ( ( TRUNCATE (d.bdo_percepcionPresupuestal,2) + TRUNCATE(d.bdo_percepcionContable,2) ) - TRUNCATE(d.bdo_retencionContable,2) ) AS importe,
                    SUBSTR(lot_ejercicio, 3) AS anio, 3 AS tipo, doc.bdo_id AS orden, CURDATE() AS fecha, d.bdo_referencia AS concepto,
                    pr.mun_nombre AS benef, elo_id, doc.ue AS ue, cb.tipoF
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_CTRL . '.scp_Clc AS c ON (lc.gpo_id = c.gpo_id AND lc.ur_id = c.ur_id AND lc.clc_id = c.clc_id)
                INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar AS lca ON (lca.gpo_id = c.gpo_id AND lca.ur_id = c.ur_id AND lca.clc_id = c.clc_id)
                INNER JOIN (
                    SELECT gpo_id, ur_id, clc_id, bdo_id, pr.mun_id AS ue, rfc_id
                    FROM ' . BD_CTRL . '.scp_Presupuestal AS p
                    JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor AS pr USING (rfc_id)
                    WHERE p.gpo_id = :pgpo
                    AND p.ur_id = :pur
                    AND p.clc_id > :pclc
                    UNION
                    SELECT gpo_id, ur_id, clc_id, bdo_id, pr.mun_id AS ue, rfc_id
                    FROM ' . BD_CTRL . '.scp_Contable AS c
                    JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor AS pr USING (rfc_id)
                    WHERE c.gpo_id = :cgpo
                    AND c.ur_id = :cur
                    AND c.clc_id > :cclc
                ) AS doc ON (doc.gpo_id = lc.gpo_id AND doc.ur_id = lc.ur_id AND doc.clc_id = lc.clc_id)
                INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS sf USING (ecf_id)
                INNER JOIN (
                    SELECT IF ( tfn_id > 4 && tfn_id < 9, 2, tfn_id ) AS tipoF, cbs_id
                    FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
                    WHERE tfn_id != :tfn
                ) AS cb USING (cbs_id)
                INNER JOIN ' . BD_CTRL . '.cat_BancoDocumentos AS d ON (doc.gpo_id = d.gpo_id AND doc.ur_id = d.ur_id AND doc.bdo_id = d.bdo_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS m ON (m.mun_id = doc.ue AND m.tfn_id = cb.tipoF)
                INNER JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor AS pr ON (pr.rfc_id = doc.rfc_id)
                WHERE l.lot_ejr = :lot_e and l.fpa_id = :fpa
                AND l.msc_id = :msc
                AND l.lot_ejercicio = :lot_ejer
                AND l.lot_id = :lot_id ;';
        $params = [
            ':pgpo' => 9,
            ':pur' => 01,
            ':pclc' => 0,
            ':cgpo' => 9,
            ':cur' => 01,
            ':cclc' => 0,
            ':tfn' => 1,
            ':lot_e' => APP_EJERCICIO,
            ':fpa' => $this->prm->fpa_id,
            ':msc' => $this->prm->msc_id,
            ':lot_ejer' => $this->prm->lot_ejercicio,
            ':lot_id' => $this->prm->lot_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tblDetalleMunicipios";
        $this->wrLlenaTabla($nav);
    }

    private function pintaDetallePDP()
    {
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : '';
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : '';

        if (empty($this->prm->fpa_id) || empty($this->prm->msc_id) || empty($this->prm->lot_ejercicio) || empty($this->prm->lot_id)) {
            $this->mixScript('
                    notify("Faltan datos", "Error");
                ');
            return;
        }

        $this->setContenedor('contenedorDetalle');
        $tabla = new stdClass();
        $tabla->id = 'tblDetallePDP';
        $tabla->titulo = 'Detalle PDP';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaDetallePDP';
        $tabla->comunica->fpa_id = $this->prm->fpa_id;
        $tabla->comunica->msc_id = $this->prm->msc_id;
        $tabla->comunica->lot_ejercicio = $this->prm->lot_ejercicio;
        $tabla->comunica->lot_id = $this->prm->lot_id;

        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA DESTINO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DOCUMENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ctaCargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ctaAbono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'documento_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rfc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'benef';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaDetallePDP();
    }

    private function llenaDetallePDP()
    {
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : '';
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : '';

        $sql = '';

        if ($this->prm->lot_ejercicio == APP_EJERCICIO) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2025) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2025 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2025 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2025 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2025 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2024) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2024 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2024 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2024 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2024 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2023) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2023 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2023 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2023 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2023 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2022) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2022 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2022 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2022 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2022 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2021) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2021 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2021 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2021 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2021 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2020) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2020 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2020 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2020 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2020 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2019) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2019 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2019 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2019 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2019 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2018) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden, CURDATE() AS fecha,
                    SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo, ld.bdo_id AS documento_id, doc.bdo_referencia AS documento,
                    bsf.cbs_id AS ctaCargo, doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )
                INNER JOIN ' . BD_CTRL_2018 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2018 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2018 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2018 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';

        } elseif ($this->prm->lot_ejercicio == 2017) {
            $sql = '
                SELECT
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden,
                    CURDATE() AS fecha, SUBSTR(l.lot_ejercicio, 3) AS anio, 3 AS tipo,
                    ld.bdo_id AS documento_id, doc.bdo_referencia AS documento, bsf.bsf_id AS ctaCargo,
                    doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    doc.bdo_concepto AS concepto
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2017 . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)
                INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2017 . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2017 . '.cat_UnidadResponsable USING (gpo_id, ur_id)
                INNER JOIN ' . BD_CTRL_2017 . '.enc_BancosSefinCveFin bsf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2017 . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)';
        }

        $sql .= '
            WHERE l.lot_ejr = :ejer AND l.fpa_id = :fpa
            AND l.msc_id = :msc
            AND l.lot_ejercicio = :lot_ejr
            AND l.lot_id = :lot_id ;';
        $params = [
            ':ejer' => APP_EJERCICIO,
            ':fpa' => $this->prm->fpa_id,
            ':msc' => $this->prm->msc_id,
            ':lot_ejr' => $this->prm->lot_ejercicio,
            ':lot_id' => $this->prm->lot_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tblDetallePDP";
        $this->wrLlenaTabla($nav);
    }
} //Fin Class

if (isset($_SESSION[_S]['prm']['T']['1']['1']['1'])) {
    new ControladorConsultaTesoreria();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
