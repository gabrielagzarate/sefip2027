<?php

/**
 * Description of ControladorTesCadenas
 *
 * @author Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 06-Ene-2019
 * Update: 10-Nov-2022
 * Comment: -----
 * Objective: Autorizar o cancelar lotes de cadenas productivas en su totalidad.
 * Regla de Negocio:
 * Permits: Solicita lectura, escritura y validacion
 * */
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/Lote.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDetalle.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDocumento.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_AutorizarLoteCadenaProductiva.php';

class ControladorTesCadenas extends WebResponse
{
    private $lectura;
    private $escritura;
    private $validacion;
    private $nvj_id;
    private $usr_id;
    private $ip;
    private $fechaMin;
    private $fechaMax;
    private $lot_tipoCereCo;
    private $lote;
    private $pro_id;
    private $ejer;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "tes/tesoreria/ControladorTesCadenas.php";
        $this->lectura = isset($_SESSION[_S]['prm']['T']['3']['4']['1']);
        $this->escritura = isset($_SESSION[_S]['prm']['T']['3']['4']['2']);
        $this->validacion = isset($_SESSION[_S]['prm']['T']['3']['4']['3']);
        $this->nvj_id = isset($_SESSION[_S]['nvj_id']);

        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] :
            '127.0.0.1';

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
                    case 'llenaTablaLotes':
                        $this->llenaTablaLotes();
                        break;
                    case 'pintaDetalleLote':
                        $this->pintaDetalleLote();
                        break;
                    case 'llenaTablaDetalleLote':
                        $this->llenaTablaDetalleLote();
                        break;
                    case 'cambiaEstadoLote':
                        $this->cambiaEstadoLote();
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
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->lot_tipoCereCo = isset($this->prm->lot_tipoCereCo) ? $this->prm->lot_tipoCereCo : '';
        $this->prm->tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';
        $this->prm->elo_id = isset($this->prm->elo_id) ? $this->prm->elo_id : '';
        $this->prm->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : '';
        $this->prm->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : '';
        $this->prm->lote = isset($this->prm->lote) ? $this->prm->lote : '';
        $this->prm->pro_id = isset($this->prm->pro_id) ? $this->prm->pro_id : '';
        $this->prm->buscar = isset($this->prm->buscar) ? $this->prm->buscar : false;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar';
        $btnBuscar->name = 'btnBuscar';
        $btnBuscar->text = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->type = 'button';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'search me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaLotes",
                lot_ejercicio : $("#comboEjercicio").val(),
                fpa_id : $("#comboFormaPago").val(),
                lot_tipoCereCo : $("#comboDocumento").val(),
                tlo_id : $("#comboTipoLote").val(),
                elo_id : $("#comboEstadoLote").val(),
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
        $btnLimpiar->color = 'amarillo';
        $btnLimpiar->icon = 'trash me-2';
        $btnLimpiar->class = 'me-1';
        $btnLimpiar->click = '
            $("#comboEjercicio").val("1");
            $("#comboDocumento").val("0");
            $("#comboFormaPago").val("4");
            $("#comboTipoLote").val("3");
            $("#comboEstadoLote").val("");
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
        $btnReporteExcel->value = 'Excel';
        $btnReporteExcel->type = 'button';
        $btnReporteExcel->color = 'verde';
        $btnReporteExcel->icon = 'file-excel me-2';
        $btnReporteExcel->class = 'me-1';
        $btnReporteExcel->disabled = true;
        $btnReporteExcel->click = '
            $("#loteRep").prop("action","rep/excel/tes/tesoreria/exp_CadenasReporteLotes.php");
            $("#loteRep").submit();
        ';

        $btnReporteExcelD = new stdClass();
        $btnReporteExcelD->id = 'btnReporteExcelD';
        $btnReporteExcelD->name = 'btnReporteExcelD';
        $btnReporteExcelD->text = 'Exportar Excel Detallado';
        $btnReporteExcelD->value = 'Excel Detallado';
        $btnReporteExcelD->type = 'button';
        $btnReporteExcelD->color = 'verde';
        $btnReporteExcelD->icon = 'fa-solid fa-file-excel me-2';
        $btnReporteExcelD->class = 'me-1';
        $btnReporteExcelD->disabled = true;
        $btnReporteExcelD->click = '
            $("#loteRep").prop("action","rep/excel/tes/tesoreria/exp_CadenasReporteLotesDetallado.php");
            $("#loteRep").submit();
        ';

        $this->agregaTools($this->ponBoton($btnBuscar));
        $this->agregaTools($this->ponBoton($btnLimpiar));
        $this->agregaTools($this->ponBoton($btnReporteExcel));
        $this->agregaTools($this->ponBoton($btnReporteExcelD));

        $datos = array();
        $datos[1] = '2026';
        $datos[2] = '2025';
        $datos[3] = '2024';
        $datos[4] = '2023';
        $datos[5] = '2022';
        $datos[6] = '2021';
        $datos[7] = '2020';
        if ($this->serverProduccion) {
            $datos[8] = '2019';
            $datos[9] = '2018';
            $datos[10] = '2017';
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
            SELECT fpa_id, fpa_nombre FROM ' . BD_TESORERIA . '.cat_FormaPago
            WHERE fpa_id = 4
        ';
        $comboFormaPago = new StdClass();
        $comboFormaPago->sql = $sql;
        $comboFormaPago->id = 'comboFormaPago';
        $comboFormaPago->name = 'comboFormaPago';
        $comboFormaPago->required = true;
        $comboFormaPago->mensaje = 'SELECCIONE LA FORMA DE PAGO';
        $comboFormaPago->label = 'FORMA DE PAGO';

        $sql = '
            SELECT tlo_id, tlo_nombre FROM ' . BD_TESORERIA . '.cat_TipoLote
            WHERE tlo_id = 3
        ';
        $comboTipoLote = new StdClass();
        $comboTipoLote->sql = $sql;
        $comboTipoLote->id = 'comboTipoLote';
        $comboTipoLote->name = 'comboTipoLote';
        $comboTipoLote->required = true;
        $comboTipoLote->mensaje = 'SELECCIONE EL TIPO DE LOTE';
        $comboTipoLote->label = 'TIPO DE LOTE';

        $sql = '
            SELECT elo_id,elo_nombre FROM ' . BD_TESORERIA . '.cat_EstadoLote
        ';
        $comboEstadoLote = new StdClass();
        $comboEstadoLote->sql = $sql;
        $comboEstadoLote->id = 'comboEstadoLote';
        $comboEstadoLote->name = 'comboEstadoLote';
        $comboEstadoLote->required = true;
        $comboEstadoLote->mensaje = 'SELECCIONE EL ESTADO DE LOTE';
        $comboEstadoLote->label = 'ESTADO DE LOTE';

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

        /**
         * TABLA
         */
        $tabla = new stdClass();
        $tabla->id = 'tablaLotes';
        $tabla->titulo = 'LISTADO DE LOTES';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaLotes';
        $tabla->numFilas = 50;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);
        $tabla->comunica->lot_ejercicio = '{$("#comboEjercicio").val()}';
        $tabla->comunica->fpa_id = '{$("#comboFormaPago").val()}';
        $tabla->comunica->lot_tipoCereCo = '{$("#comboDocumento").val()}';
        $tabla->comunica->lot_flujoEfectivo = '{$("#comboFlujoEfectivo").val()}';
        $tabla->comunica->tlo_id = '{$("#comboTipoLote").val()}';
        $tabla->comunica->fechaMin = '{$("#inputFechaMin").val()}';
        $tabla->comunica->fechaMax = '{$("#inputFechaMax").val()}';
        $tabla->comunica->lote = '{$("#inputLote").val()}';
        $tabla->comunica->pro_id = '{$("#inputPropuesta").val()}';

        /**
         * CABECERA DE LA TABLA
         */
        $tr = array();

        $th = new stdClass();
        $th->texto = 'DETALLE LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

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
        $th->texto = 'AUTORIZAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);
        /**
         * CUERPO DE LA TABLA
         */
        $trbody = array();

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'elo_id';
        $td->casos->valor = '1,2,3,4,5,6,7,8';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'eye';
        $td->casos->verdadero->color = 'gris';
        $td->casos->verdadero->name = 'detalleLote';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->lot_id = 'lot_id';
        $td->casos->verdadero->attr->fpa_id = 'fpa_id';
        $td->casos->verdadero->attr->msc_id = 'msc_id';
        $td->casos->verdadero->attr->lot_ejercicio = 'lot_ejercicio';
        $td->casos->verdadero->attr->tlo_id = 'tlo_id';
        $td->casos->verdadero->attr->lot = 'lote';
        array_push($trbody, $td);

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
        $td->casos->valor = '4';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'check';
        $td->casos->verdadero->color = 'cielo';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->lotAutorizar = 'lot_id';
        $td->casos->verdadero->attr->lotFpa = 'fpa_id';
        $td->casos->verdadero->attr->lotMsc = 'msc_id';
        $td->casos->verdadero->attr->lotEje = 'lot_ejercicio';
        $td->casos->verdadero->attr->lotMod = 'lot_tipo';
        $td->casos->verdadero->attr->tlo_id = 'tlo_id';
        /* Verificamos permisos de Autorización para dibujar el elemento/*/
        if (isset($this->validacion)) {
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'elo_id';
        $td->casos->valor = '4';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'trash';
        $td->casos->verdadero->color = 'rojo';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->lotEliminar = 'lot_id';
        $td->casos->verdadero->attr->lotFpa = 'fpa_id';
        $td->casos->verdadero->attr->lotMsc = 'msc_id';
        $td->casos->verdadero->attr->lotEje = 'lot_ejercicio';
        $td->casos->verdadero->attr->lotMod = 'lot_tipo';
        $td->casos->verdadero->attr->tlo_id = 'tlo_id';
        /* Verificamos permisos de Escritura para dibujar el elemento/*/
        if (isset($this->escritura)) {
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <legend>Cadenas Productivas</legend>
            </div>
            <div class="row">
                <form method="post" target="_blank" id="loteRep">
                    <div class="row mb-4">
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboEjercicio) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboDocumento) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboFormaPago) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboTipoLote) . '</fieldset>
                    </div>
                    <div class="row mb-4">
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($comboEstadoLote) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFechaMin) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFechaMax) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputLote) . '</fieldset>
                    </div>
                    <div class="row mb-4">
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputPropuesta) . '</fieldset>
                    </div>
                </form>
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</fieldset>
            </div>
        ';

        $this->mixHtml($html);

        if (isset($this->prm->busqueda)) {
            $this->mixScript('
                $("#comboEjercicio").val(' . $this->prm->lot_ejercicio . ');
                $("#comboDocumento").val(' . $this->prm->lot_tipoCereCo . ');
                $("#comboFormaPago").val(' . $this->prm->fpa_id . ');
                $("#comboTipoLote").val(' . $this->prm->tlo_id . ');
                $("#comboEstadoLote").val(' . $this->prm->comboEstadoLote . ');
                $("#inputFechaMin").val("' . $this->prm->fechaMin . '");
                $("#inputFechaMax").val("' . $this->prm->fechaMax . '");
                $("#inputLote").val("' . $this->prm->lote . '");
                $("#inputPropuesta").val("' . $this->prm->pro_id . '");
                $(".selectpicker").selectpicker("refresh");
            ');
            $this->llenaTablaLotes();
        }
    }

    /**
     * FUNCIONES NUEVAS PARA CONSULTA DE LOTES
     */
    private function llenaTablaLotes()
    {
        $this->prm->lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : '';
        $this->prm->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : '';
        $this->prm->lot_tipoCereCo = isset($this->prm->lot_tipoCereCo) ? $this->prm->lot_tipoCereCo : '';
        $this->prm->tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';
        $this->prm->elo_id = isset($this->prm->elo_id) ? $this->prm->elo_id : '';
        $this->prm->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : '';
        $this->prm->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : '';
        $this->prm->lote = isset($this->prm->lote) ? $this->prm->lote : '';
        $this->prm->pro_id = isset($this->prm->pro_id) ? $this->prm->pro_id : '';

        $busqueda = $this->prm->lot_ejercicio . '|' . $this->prm->fpa_id . '|' . $this->prm->lot_tipoCereCo . '|' . $this->prm->tlo_id . '|' . $this->prm->fechaMin . '|' . $this->prm->fechaMax . '|' . $this->prm->lote . '|' . $this->prm->pro_id;

        $busquedaArr = explode('|', $busqueda); //Guardamos los valores de la busqueda para el boton regresar de la pantalla anterior
        $params = [];
        $sql = '
            SELECT
                CASE WHEN l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7 THEN
                    "FEDERAL"
                ELSE
                    "ESTATAL"
                END AS modo,
                tlo.tlo_nombre AS tipo,
                CASE WHEN l.tlo_id = 4 OR l.tlo_id = 5 OR l.tlo_id = 6 OR l.tlo_id = 7 OR l.elo_id = 9 THEN
                    2
                ELSE
                    1
                END AS propuesta,
                CASE WHEN l.tlo_id = 4 OR l.tlo_id = 5 OR l.tlo_id = 6 OR l.tlo_id = 7 OR l.elo_id = 9 THEN
                    ""
                ELSE
                    pl.pro_id
                END AS propuesta_id,
                CASE WHEN l.tlo_id = 6 OR l.tlo_id = 7 THEN
                    (CASE WHEN l.elo_id <> 9 THEN
                        (SELECT COUNT(*)
                        FROM ' . BD_TESORERIA . '.lot_PropuestaManual
                        WHERE fpa_id = l.fpa_id
                        AND msc_id = l.msc_id
                        AND lot_id = l.lot_id
                        AND lot_ejercicio = l.lot_ejercicio)
                    ELSE
                        0
                    END
                    )
                ELSE
                    (SELECT COUNT(*)
                    FROM ' . BD_TESORERIA . '.lot_LoteClc
                    WHERE fpa_id = l.fpa_id
                    AND msc_id = l.msc_id
                    AND lot_ejercicio = l.lot_ejercicio
                    AND lot_id = l.lot_id)
                END AS elementos,
                l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id, l.tlo_id,
                l.lot_fechaGeneracion, elo_nombre, l.elo_id,
                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L"))),
                    LPAD(l.msc_id, 2, "0"),
                    LPAD(l.lot_id, 4, "0"),
                    SUBSTR(l.lot_ejercicio, 3)
                ) AS lote,
                CASE WHEN l.lot_importe = 0 AND l.elo_id <> 9 THEN
                    (CASE WHEN l.tlo_id = 6 OR l.tlo_id = 7 THEN
                        (SELECT SUM(lom_importe)
                        FROM ' . BD_TESORERIA . '.lot_PropuestaManual
                        WHERE fpa_id = l.fpa_id AND msc_id = l.msc_id
                        AND lot_ejercicio = l.lot_ejercicio AND lot_id = l.lot_id)
                    ELSE
                        (SELECT SUM(lot_importeClc)
                        FROM ' . BD_TESORERIA . '.lot_LoteClc
                        WHERE fpa_id = l.fpa_id AND msc_id = l.msc_id
                        AND lot_ejercicio = l.lot_ejercicio AND lot_id = l.lot_id)
                    END)
                WHEN l.lot_importe <> 0 AND l.elo_id = 9 THEN
                    0.00
                ELSE
                    l.lot_importe
                END AS lot_importe
            FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS elo USING (elo_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
            LEFT JOIN ' . BD_TESORERIA . '.vin_PropuestaLote AS pl USING (fpa_id, msc_id, lot_ejercicio, lot_id)
        ';

        $sqlExtra = '';

        if (!empty($this->prm->lot_ejercicio)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            if ($this->prm->lot_ejercicio == 10) {
                $sqlExtra .= 'l.lot_ejercicio = 2017 ';
            } elseif ($this->prm->lot_ejercicio == 9) {
                $sqlExtra .= 'l.lot_ejercicio = 2018 ';
            } elseif ($this->prm->lot_ejercicio == 8) {
                $sqlExtra .= 'l.lot_ejercicio = 2019 ';
            } elseif ($this->prm->lot_ejercicio == 7) {
                $sqlExtra .= 'l.lot_ejercicio = 2020 ';
            } elseif ($this->prm->lot_ejercicio == 6) {
                $sqlExtra .= 'l.lot_ejercicio = 2021 ';
            } elseif ($this->prm->lot_ejercicio == 5) {
                $sqlExtra .= 'l.lot_ejercicio = 2022 ';
            } elseif ($this->prm->lot_ejercicio == 4) {
                $sqlExtra .= 'l.lot_ejercicio = 2023 ';
            } elseif ($this->prm->lot_ejercicio == 3) {
                $sqlExtra .= 'l.lot_ejercicio = 2024 ';
            } elseif ($this->prm->lot_ejercicio == 2) {
                $sqlExtra .= 'l.lot_ejercicio = 2025 ';
            } elseif ($this->prm->lot_ejercicio == 1) {
                $sqlExtra .= 'l.lot_ejercicio = 2026 ';
            }
        }

        if (!empty($this->prm->fpa_id)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= 'l.fpa_id = :fpa ';
            $params['fpa'] = $this->prm->fpa_id;
        }

        if (!empty($this->prm->lot_tipoCereCo)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= 'l.lot_tipoCereCo = :tipoCereCo ';
            $params['tipoCereCo'] = $this->prm->lot_tipoCereCo;
        }

        if (!empty($this->prm->tlo_id)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= 'l.tlo_id = :tlo ';
            $params['tlo'] = $this->prm->tlo_id;
        }

        if (!empty($this->prm->elo_id)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= 'l.elo_id = :elo ';
            $params['elo'] = $this->prm->elo_id;
        }

        if (!empty($this->prm->fechaMin)) {
            $fecha = DateTime::createFromFormat('d/m/Y', $this->prm->fechaMin);
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= 'l.lot_fechaGeneracion >= :fechaMin ';
            $params['fechaMin'] = $fecha->format('Y-m-d');
        }

        if (!empty($this->prm->fechaMax)) {
            $fecha = DateTime::createFromFormat('d/m/Y', $this->prm->fechaMax);
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= 'l.lot_fechaGeneracion <= :fechaMax ';
            $params['fechaMax'] = $fecha->format('Y-m-d');
        }

        if (!empty($this->prm->lote)) {
            $lotesArr = array();
            foreach (explode(',', $this->prm->lote) as $lote) {
                array_push($lotesArr, '"' . trim($lote) . '"');
            }
            $lotes = implode(',', $lotesArr);

            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= '(CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"),
                SUBSTR(l.lot_ejercicio, 3) )) IN (' . $lotes . ')
            ';
        }

        if (!empty($this->prm->pro_id)) {
            $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
            $sqlExtra .= 'pl.pro_id IN (:pro)
            ';
            $params['pro'] = $this->prm->pro_id;
        }

        $sqlExtra .= (strlen($sqlExtra) == 0) ? ' WHERE l.lot_ejr = :ejr ' : ' AND l.lot_ejr = :ejr ';
        $params['ejr'] = APP_EJERCICIO;
        $sqlExtra .= 'GROUP BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id, l.elo_id, pl.pro_id
                      ORDER BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.elo_id, l.lot_id ';

        $this->setSql($sql . $sqlExtra);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = "tablaLotes";
        $this->setParams($params);
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
                    elo_id: $(this).attr("elo_id"),
                    lot: $(this).attr("lot"),
                    busquedaArr: ' . json_encode($busquedaArr) . '
                });
            });

           $(".lotAutorizar").click(function(){
                alertify.confirm(
                    "<h5> AUTORIZAR LOTE </h5>",
                    "¿Realmente desea autorizar el lote?",
                    function(){
                        //Mandamos a llamar al metodo para cambiar el estado del lote a Autorizado
                        comunica("' . $this->pantalla . '",{
                            metodo: "cambiaEstadoLote",
                            accionLote: "autorizar",
                            lot_id: $(this).attr("lotAutorizar"),
                            fpa_id: $(this).attr("lotFpa"),
                            msc_id: $(this).attr("lotMsc"),
                            lot_ejercicio: $(this).attr("lotEje"),
                            lot_tipo: $(this).attr("lotMod"),
                            tlo_id: $(this).attr("tlo_id"),
                            elo_id: "' . $this->prm->elo_id . '",
                            lot_tipoCereCo: "' . $this->prm->lot_tipoCereCo . '",
                            fechaMin: "' . $this->prm->fechaMin . '",
                            fechaMax: "' . $this->prm->fechaMax . '",
                            lote:"' . $this->prm->lote . '",
                            pro_id:"' . $this->prm->pro_id . '"
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".lotEliminar").click(function(){
                alertify.confirm(
                    "<h5> CANCELAR LOTE </h5>",
                    "¿Realmente desea cancelar el lote?",
                    function(){
                        //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
                        comunica("' . $this->pantalla . '",{
                            metodo: "cambiaEstadoLote",
                            accionLote: "cancelar",
                            lot_id: $(this).attr("lotEliminar"),
                            fpa_id: $(this).attr("lotFpa"),
                            msc_id: $(this).attr("lotMsc"),
                            lot_ejercicio: $(this).attr("lotEje"),
                            tlo_id: $(this).attr("tlo_id"),
                            elo_id: "' . $this->prm->elo_id . '",
                            lot_tipoCereCo: "' . $this->prm->lot_tipoCereCo . '",
                            fechaMin: "' . $this->prm->fechaMin . '",
                            fechaMax: "' . $this->prm->fechaMax . '",
                            lote:"' . $this->prm->lote . '",
                            pro_id:"' . $this->prm->pro_id . '",
                            ejercicio:"' . $this->prm->lot_ejercicio . '"
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
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
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->class = 'me-1';
        $btnRegresarIndex->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresarIndex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main",
                busqueda: true' . $busqueda . '
            });
        ';

        $btnReporteExcel = new stdClass();
        $btnReporteExcel->id = 'btnReporteExcel';
        $btnReporteExcel->name = 'btnReporteExcel';
        $btnReporteExcel->text = 'Excel';
        $btnReporteExcel->value = 'Excel';
        $btnReporteExcel->type = 'button';
        $btnReporteExcel->color = 'verde';
        $btnReporteExcel->class = 'me-1';
        $btnReporteExcel->icon = 'file-excel me-2';

        $btnReportePDF = new stdClass();
        $btnReportePDF->id = 'btnReportePDF';
        $btnReportePDF->name = 'btnReportePDF';
        $btnReportePDF->text = 'Exportar PDF';
        $btnReportePDF->value = 'PDF';
        $btnReportePDF->type = 'button';
        $btnReportePDF->color = 'rojo';
        $btnReportePDF->class = 'me-1';
        $btnReportePDF->icon = 'file-pdf me-2';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $this->agregaTools($this->ponBoton($btnReporteExcel));
        $this->agregaTools($this->ponBoton($btnReportePDF));

        /**
         * TABLA
         */
        $tabla = new stdClass();
        $tabla->id = 'tablaDetalleLote';
        $tabla->titulo = 'DETALLE DEL LOTE ' . $this->prm->lot;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetalleLote';
        $tabla->numFilas = 50;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);
        $tabla->comunica->fpa_id = $this->prm->fpa_id;
        $tabla->comunica->msc_id = $this->prm->msc_id;
        $tabla->comunica->lot_ejercicio = $this->prm->lot_ejercicio;
        $tabla->comunica->lot_id = $this->prm->lot_id;
        $tabla->comunica->tlo_id = $this->prm->tlo_id;

        /**
         * CABECERA DE LA TABLA
         */
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

        /**
         * CUERPO DE LA TABLA
         */
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
        $this->mixScript('$("#btnregresarindex").removeClass("btn-default");');
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
        $andSql = '';
        switch ($this->prm->lot_ejercicio) {
            case 2017:
                $sql .= '
                            SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.bsf_id) AS origen,
                                MAX(iba.iba_nombre) as banco,
                                l.lot_clave AS lote,
                                MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                                    cx.clc_cereCo
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING (gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.enc_BancosSefinCveFin AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_BancosSefin AS cbs USING (bsf_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.cat_InstitucionesBancarias AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                            LEFT JOIN ' . BD_CTRL_2017 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2017 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
                $andSql = ' AND ant.ejr_id_clc = 2017 ';
                break;
            case 2018:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        LEFT JOIN ' . BD_CTRL_2018 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                        LEFT JOIN ' . BD_CTRL_2018 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
                $andSql = ' AND ant.ejr_id_clc = 2018 ';
                break;
            case 2019:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2019 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2019 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2019 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        ';
                $andSql = ' AND ant.ejr_id_clc = 2019 ';
                break;
            case 2020:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2020 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2020 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2020 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        ';
                $andSql = ' AND ant.ejr_id_clc = 2020 ';
                break;
            case 2021:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2021 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        ';
                $andSql = ' AND ant.ejr_id_clc = 2021 ';
                break;
            case 2022:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2022 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2022 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2022 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        ';
                $andSql = ' AND ant.ejr_id_clc = 2022 ';
                break;
            case 2023:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2023 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2023 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2023 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        ';
                $andSql = ' AND ant.ejr_id_clc = 2023 ';
                break;
            case 2024:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2024 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2024 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2024 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        ';
                $andSql = ' AND ant.ejr_id_clc = 2024 ';
                break;
            case 2025:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2025 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2025 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2025 . '.cat_EstadoClc AS e ON (ant.ecl_id = e.ecl_id)
                        ';
                $andSql = ' AND ant.ejr_id_clc = 2025 ';
                break;
            case 2026:
                $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                            MAX(iba.iba_nombre) as banco,
                            l.lot_clave AS lote,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                            cx.clc_cereCo
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                break;
        }
        $sqlGroupBy = 'GROUP BY c.gpo_id, c.ur_id, c.clc_id';
        if (!empty($sql)) {
            $params = [$this->prm->fpa_id, $this->prm->msc_id, $this->prm->lot_ejercicio, $this->prm->lot_id];
            $sql .= '
                    WHERE lc.fpa_id = ?
                    AND lc.msc_id = ?
                    AND lc.lot_ejercicio = ?
                    AND lc.lot_id = ?
                    ' . $andSql . '
                    ' . $sqlGroupBy . '
                    ORDER BY importe ASC
            ';

            $this->setSql($sql);
            $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
            $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
            $nav->idTabla = "tablaDetalleLote";
            $this->setParams($params);
            $this->wrLlenaTabla($nav);

            $this->mixScript('
                $("#btnReporteExcel").click(function(){
                    imprimeReporteDetLote();
                });

                $("#btnReportePDF").click(function(){
                    imprimeReporteDetLotePDF();
                });

                function imprimeReporteDetLote(){
                    var form = $("<form>")
                        .attr("action", "rep/excel/tes/tesoreria/exp_CadenasReporteLoteDetalle.php")
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
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }

                function imprimeReporteDetLotePDF(){
                    var form = $("<form>")
                        .attr("action", "rep/doc/tes/tesoreria/rep_CadenasProd.php")
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
                        .attr("name", "usr_id")
                        .val("' . $this->usr_id . '"))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ');
        }
    }

    private function cambiaEstadoLote()
    {
        /*  Se hace el cambio de estado mandando a llamar a la Entidad Lote, hay que tomar en cuenta
         *  que tambien se hará el cambio de estado de la CLC (En cuanto esté la BD)
         */

        $lot = new stdClass();
        $detLot = new stdClass();
        $docLot = new stdClass();
        $clc = new stdClass();

        $fechaActual = '';

        $lot->abc = "c";
        $detLot->abc = $docLot->abc = "b";

        $this->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : '';
        $this->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : '';
        $this->lot_tipoCereCo = isset($this->prm->lot_tipoCereCo) ? $this->prm->lot_tipoCereCo : '';
        $this->lote = isset($this->prm->lote) ? $this->prm->lote : '';
        $this->pro_id = isset($this->prm->pro_id) ? $this->prm->pro_id : '';
        $this->ejer = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $lot->lot_id = $detLot->lot_id = $docLot->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $lot->fpa_id = $detLot->fpa_id = $docLot->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $lot->msc_id = $detLot->msc_id = $docLot->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot->ejercicio = $detLot->ejercicio = $docLot->ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $tlo_id = isset($this->prm->tlo_id) ? $this->prm->tlo_id : '';
        $lot->lot_ejr = APP_EJERCICIO;

        $this->setSql('SELECT CURDATE() as fecha');
        $this->conectar();
        $this->consultar();
        if ($row = $this->extraer()) {
            $fechaActual = $row['fecha'];
        }

        switch ($this->prm->accionLote) {
            case 'autorizar':

                /**
                 * Cambia el estado del lote a 8 (Enviado a Banca Electronica)
                 */
                $ip = $this->ip;

                $datosSP = new stdClass();
                $datosSP->fpa_id = $lot->fpa_id;
                $datosSP->msc_id = $lot->msc_id;
                $datosSP->lot_ejercicio = $lot->ejercicio;
                $datosSP->lot_id = $lot->lot_id;
                $datosSP->bit_observacion = 'AUTORIZADO DESDE SISTEMA T_3_4';
                $datosSP->sis_id = "TES";
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $params = [$this->usr_id, $ip];
                $sql = "set @usr_id = ?, @usr_ip=INET_ATON(?) , @bit_observacion='AUTORIZADO DESDE SISTEMA T_3_4'";
                $this->setSql($sql);
                $this->consultar($params);
                $respuesta = sp_AutorizarLoteCadenaProductiva::call($datosSP);
                $this->mixScript('
                notify("' . $respuesta->mensaje . '","'
                    . ($respuesta->bandera ? 'success' : 'error') . '")
                comunica("' . $this->pantalla . '",{metodo:"llenaTablaLotes",
                        fechaMax:"' . $this->fechaMin . '",
                        fechaMin:"' . $this->fechaMin . '",
                        fpa_id:"' . $lot->fpa_id . '",
                        lot_ejercicio:"' . $lot->ejer . '",
                        lot_tipoCereCo:"' . $this->lot_tipoCereCo . '",
                        lote:"' . $this->lote . '",
                        pro_id:"' . $this->pro_id . '",
                        tlo_id:"' . $tlo_id . '"
                    });
                ');
                break;

            case 'cancelar':
                /* Cambia el estado a 9 (Lote cancelado)/*/
                $lot->elo_id = 9;
                $lot->importe = 0;
                $lot->fechaCancelacion = $fechaActual;

                /**
                 * Consultamos los registros del detalle.
                 */

                $params = [$detLot->lot_id, $detLot->fpa_id, $detLot->msc_id, $detLot->ejercicio];
                $sql = '
                    SELECT
                        dl.lot_id,
                        dl.fpa_id,
                        dl.msc_id,
                        dl.lot_ejercicio,
                        dl.gpo_id,
                        dl.ur_id,
                        dl.clc_id,
                        l.lot_fechaPago
                    FROM
                        ' . BD_TESORERIA . '.lot_LoteClc AS dl
                    INNER JOIN ' . BD_TESORERIA . '.lot_Lote AS l USING(fpa_id, msc_id, lot_ejercicio, lot_id)
                    WHERE
                        lot_id = ?
                        AND fpa_id =?
                        AND msc_id = ?
                        AND lot_ejercicio = ? ';

                $con = new AccesoDatos();
                $con->setSql($sql);
                $con->conectar();
                $con->consultar($params);

                /**
                 * Por cada elemento del detalle, mandamos a llamar el metodo para eliminar
                 */
                while ($row = $con->extraer()) {
                    $detLot->fpa_id = $docLot->fpa_id = $row['fpa_id'];
                    $detLot->msc_id = $docLot->msc_id = $row['msc_id'];
                    $detLot->ejercicio = $docLot->ejercicio = $row['lot_ejercicio'];
                    $clc->gpo_id = $detLot->gpo_id = $docLot->gpo_id = $row['gpo_id'];
                    $clc->ur_id = $detLot->ur_id = $docLot->ur_id = $row['ur_id'];
                    $clc->clc_id = $detLot->clc_id = $docLot->clc_id = $row['clc_id'];
                    $params = [
                        $docLot->lot_id,
                        $docLot->fpa_id,
                        $docLot->msc_id,
                        $docLot->ejercicio,
                        $docLot->gpo_id,
                        $docLot->ur_id,
                        $docLot->clc_id
                    ];
                    $sql = "
                        SELECT
                            bdo_id
                        FROM " . BD_TESORERIA . ".lot_LoteDocumento
                        WHERE lot_id = ?
                            AND fpa_id = ?
                            AND msc_id = ?
                            AND lot_ejercicio = ?
                            AND gpo_id = ?
                            AND ur_id = ?
                            AND clc_id = ?";

                    $con2 = new AccesoDatos();
                    $con2->setSql($sql);
                    $con2->conectar();
                    $con2->consultar($params);

                    while ($row = $con2->extraer()) {
                        $docLot->bdo_id = $row['bdo_id'];
                        $docLot->abc = 'b';
                        LoteDocumento::abc($docLot);
                    }

                    LoteDetalle::abc($detLot);
                    $sqlActualizaEstado = '';
                    $params = [$clc->gpo_id, $clc->ur_id, $clc->clc_id];
                    switch ($detLot->ejercicio) {
                        case 2017:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2017
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2018:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2018
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2019:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2019
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2020:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2020
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2021:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2021
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2022:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2022
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2023:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2023
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2024:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2024
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2025:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRLANT . '.scp_Clc
                                SET ecl_id = 10
                                WHERE ejr_id_clc = 2025
                                AND gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                        case 2026:
                            $sqlActualizaEstado = '
                                UPDATE ' . BD_CTRL . '.scp_ClcAuxiliar
                                SET ecl_id = 10
                                WHERE gpo_id = ?
                                AND ur_id = ?
                                AND clc_id = ?';
                            break;
                    }

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $ip = $this->ip;
                    $parametros = [$this->usr_id, $ip];
                    $sqlObservacion = "set @usr_id = ?, @usr_ip=INET_ATON(?) ,
                         @bit_observacion='Cancelación total del lote desde T_3_4'";
                    $conexionCLC->setSql($sqlObservacion);
                    $conexionCLC->consultar($parametros);
                    $conexionCLC->setSql($sqlActualizaEstado);
                    $conexionCLC->consultar($params);
                    $conexionCLC->desconectar();
                }
                Lote::abc($lot);
                break;
        }
        $this->main();
    }
}

if (isset($_SESSION[_S]['prm']['T']['3']['4']['1'])) {
    new ControladorTesCadenas();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
