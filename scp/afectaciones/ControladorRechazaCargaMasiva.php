<?php

/**
 * Description of ControladorRechazaCargaMasiva
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 17-ene-2019
 * Collaborators: Rojas Garcia Juan Carlos <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 19-Dic-2019
 * Comment: 
 * 
 * Objective: Rechazar Cargas Masivas
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 
 * Eduardo Venegas; Migracion para 2022 - Fecha: 20/10/2022
 * */

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelCargaMasiva.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaCargaMasiva.php';
ini_set('memory_limit', '500M');
set_time_limit(0);
class ControladorRechazaCargaMasiva extends WebResponse
{
    public $model;
    public $vista;
    public $escritura;
    public $gpo_id;
    public $ur_id;
    public $nvj_id;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/afectaciones/ControladorRechazaCargaMasiva.php';
        $this->model = new modelCargaMasiva();
        $this->vista = new vistaCargaMasiva();
        $this->escritura = isset($_SESSION[_S]['prm']['S']['2']['21']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $this->nvj_id = $_SESSION[_S]['nvj_id'];

        switch ($this->nvj_id) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                switch ($this->metodo) {
                    case 'llenaDetalleCarga':
                        $this->llenaDetalleCarga();
                        break;
                    case 'DetalleCarga':
                        $this->DetalleCarga();
                        break;
                    case 'VerificarCargaMasiva':
                        $this->VerificarCargaMasiva();
                        break;
                    case 'CapturaCarga':
                        $this->CapturaCarga();
                        break;
                    case 'rechazaCargaMasiva':
                        $this->rechazaCargaMasiva();
                        break;
                    case 'rechaza':
                        $this->rechaza();
                        break;
                    case 'libera':
                        $this->libera();
                        break;
                    case 'analiza':
                        $this->analiza();
                        break;
                    case 'sp_libera':
                        $this->sp_libera();
                        break;
                    case 'verDetalle':
                        $this->verDetalle();
                        break;
                    case 'verDetalleCarga':
                        $this->verDetalleCarga();
                        break;
                    case 'llenaDetalleRegistros':
                        $this->llenaDetalleRegistros();
                        break;
                    case 'verDetalleProgreso':
                        $this->verDetalleProgreso();
                        break;
                    case 'download':
                        $this->downloadCM();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools('');

        $cardRechaza = new stdClass();
        $cardRechaza->id = 'btn_rechaza';
        $cardRechaza->titulo = 'Rechazar';
        $cardRechaza->icon = 'fa-solid fa-ban';
        $cardRechaza->iconColor = '#c62828';
        $cardRechaza->label = 'Carga Masiva Por Solicitud';
        $cardRechaza->title = 'Carga Masiva';

        $cardLibera = new stdClass();
        $cardLibera->id = 'btn_libera';
        $cardLibera->titulo = 'Liberar';
        $cardLibera->icon = 'fa-solid fa-unlock';
        $cardLibera->iconColor = '#2e7d32';
        $cardLibera->label = 'Carga Masiva Por Error';
        $cardLibera->title = 'Ministracion de documentos';

        $cardConsulta = new stdClass();
        $cardConsulta->id = 'btn_consulta';
        $cardConsulta->titulo = 'Consultar';
        $cardConsulta->icon = 'fa-solid fa-magnifying-glass';
        $cardConsulta->iconColor = '#1565c0';
        $cardConsulta->label = 'Carga Masiva';
        $cardConsulta->title = 'Consultar carga masiva';

        $this->mixHtml($this->ponCards([$cardRechaza, $cardLibera, $cardConsulta]));
        $this->mixScript('
            $("#btn_rechaza").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"rechaza"});
            });
            $("#btn_libera").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"libera"});
            });
            $("#btn_consulta").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"DetalleCarga"});
            });
        ');
    }

    public function libera()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btn_reglibera';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';
        $btnregresarindex->click = ' comunica("' . $this->pantalla . '")';

        $btnregresarConsulta = new stdClass();
        $btnregresarConsulta->id = 'btnRegresaConsulta';
        $btnregresarConsulta->value = 'Regresar a Consulta';
        $btnregresarConsulta->type = 'button';
        $btnregresarConsulta->class = 'me-1';
        $btnregresarConsulta->color = 'gris';
        $btnregresarConsulta->icon = 'search me-2';
        $btnregresarConsulta->click = ' comunica("' . $this->pantalla . '",{metodo:"DetalleCarga"})';

        $btnAnaliza = new stdClass();
        $btnAnaliza->value = 'Buscar Carga';
        $btnAnaliza->id = 'btn_analiza';
        $btnAnaliza->type = 'button';
        $btnAnaliza->diabled = 'disabled';
        $btnAnaliza->class = 'mt-3 me-2';
        $btnAnaliza->color = 'gris';
        $btnAnaliza->icon = 'search me-2';
        $btnAnaliza->click = ' 
            if ($("#carga_id").val() !="") {
                $("#carga_id").attr("disabled");
                comunica("' . $this->pantalla . '",{
                    metodo:"analiza",idc:$("#carga_id").val(),
                    idc:$("#carga_id").val()
                });
            }
        ';
        $btnAnaliza = $this->ponBoton($btnAnaliza);
        $tools = $this->ponBoton($btnregresarindex);
        $tools .= isset($this->prm->carga) ? $this->ponBoton($btnregresarConsulta) : '';
        $this->agregaTools($tools);

        $carga_id = new stdClass();
        $carga_id->id = 'carga_id';
        $carga_id->label = 'CAPTURE LA CARGA MASIVA A LIBERAR';
        $carga_id->enDiv = true;
        $carga_id->placeholder = ' ';
        $carga_id->maxlength = 30;
        $carga_id->value = isset($this->prm->carga) ? $this->prm->carga : '';

        $this->mixHtml('
            <div class= "row">
                <div class="col-md-3">
                    ' . $this->ponInput($carga_id) . '
                </div>
                <div class="col-md-2 offset-md-0">
                    ' . $btnAnaliza . '
                </div>
                <div id ="div_acciones" class="col-md-7" >
                </div>
            </div>
            <div id = "analiza_res" style = "border:1px dashed #CCC">
            </div>
        ');

        $this->mixScript('
            document.getElementById("carga_id").addEventListener("bind",ctrlv);
            $("#carga_id").blur(function(){
                ctrlv();
            });
            function ctrlv(){
                val = $("#carga_id").val();
                if (val =="" || val == undefined) {
                    $("#btn_analiza").prop("disabled",1);
                    return false;
                }
                carga = val.split("-");
                if (carga.length !=4){
                    notify("Error en el identificador de carga","warning")
                    $("#btn_analiza").prop("disabled",1);
                    return false;
                }
                carga.forEach(function(item,index){
                    if (parseInt(item) == NaN){
                        notify("Error en el identificador de carga","warning");
                        $("#btn_analiza").prop("disabled",1);
                        return false;
                    }
                });
                $("#btn_analiza").removeAttr("disabled");
            }
        ');
        if (isset($this->prm->carga) && strlen($this->prm->carga) > 20) {
            $this->mixScript('comunica("' . $this->pantalla . '",{metodo:"analiza",idc:"' . $this->prm->carga . '"});');
        }
    }

    public function analiza()
    {
        $gpo_id = $_SESSION[_S]['gpo_id'];
        $ur_id = $_SESSION[_S]['ur_id'];
        $carga = $this->prm->idc;
        $detalleCarga = explode('-', $carga);
        $usr_id = $_SESSION[_S]['usr_id'];
        $nivelUsuario = $_SESSION[_S]['nvj_id'];

        if (count($detalleCarga) != 4) {
            $this->mixScript(' notify("Error en el Identificador de carga","warning");');
            return false;
        }

        if ($gpo_id . $ur_id != $detalleCarga[0] && $nivelUsuario < 3) {
            $this->mixScript('notify("E2:La carga ' . $carga . ' no corresponde a su UR","error");');
            return false;
        }

        if (!$this->model->cargaExiste($carga)) {
            $this->mixScript('notify("E3: La carga  ' . $carga . ' no existe","warning");');
            return false;
        }

        $this->setContenedor('analiza_res');
        $obs = new stdClass();
        $obs->id = 'bit_observacion';
        $obs->label = 'ERROR GENERADO POR LA CARGA';
        $obs->enDiv = true;
        $obs->placeholder = 'MOTIVO DE LIBERACI&Oacute;N';

        $btnLibera = new stdClass();
        $btnLibera->value = 'Liberar Registros';
        $btnLibera->id = 'btn_libera';
        $btnLibera->type = 'button';
        $btnLibera->diabled = 'disabled';
        $btnLibera->class = 'me-1';
        $btnLibera->color = 'verde';
        $btnLibera->click = ' 
            observa = $("#bit_observacion").val();
            if( observa ==""){
                notify("La observacion es necesaria","warning");
                return false;
            }
            if( observa.length < 20 ){
                notify("La observacion es muy corta","warning");
                return false;
            }
            comunica("' . $this->pantalla . '",{
                metodo:"sp_libera",
                observacion:observa
            });
        ';

        $btnLibera = $this->escritura == true ? $this->ponBoton($btnLibera) : '<div class="danger">Sin permiso de Escritura</div>';
        $html = '';

        /*  INFORMACIÓN DE LA CARGA MASIVA  */
        $datosCarga = $this->model->getInfoCarga($carga);

        if ($datosCarga['registros'] == 0) {
            $this->setContenedor('div_acciones');
            $html = $this->vista->fn_validaciones(4);
            $this->mixHtml($html);
            $this->mixScript('notify("La carga ' . $carga . ' no existe","error");$("#analiza_res").html("");');
            return;
        }

        $html = $this->vista->fn_pintaTabla($datosCarga);
        $estadoCarga = $datosCarga['datos'][0]['Estado'];
        $registros = $datosCarga['datos'][0]['compromisos'];
        $registros = $registros + $datosCarga['datos'][0]['afectaciones'];
        $registros = $registros + $datosCarga['datos'][0]['clc'];
        $registros = intval($registros);

        /* DETALLE DE LA CARGA EN PESTANAS (CLC / Afectaciones / Compromisos) con wrPonTabla */
        $esClc = intval($datosCarga['datos'][0]['clc']) > 0;
        $tieneAnomalia = $this->cargaTieneAnomalias($carga);
        $html .= $this->tabsDetalleCarga($carga, $esClc);
        $this->mixHtml($html);

        $this->setContenedor('div_acciones');
        $html = '';

        if ($nivelUsuario < 2 && $usr_id != $detalleCarga[2]) {
            $this->mixHtml('<div class="warning">
                E2:EL usuario no es propietario de la carga</div>
            ');
        } elseif ($nivelUsuario > 2 && !$this->escritura) {
            $html = $this->vista->fn_validaciones(1);
        } elseif ($estadoCarga == 'Completada' && !$tieneAnomalia) {
            $html = $this->vista->fn_validaciones(2);
        } else {
            $html = '
                <div class = "row  mb-4">
                    <div class = "col-md-8">
                    ' . $this->ponInput($obs) .
                '</div>
                    <div class= "col-md-4 mt-3">' .
                $btnLibera .
                '</div>
                </div>';
            $_SESSION[_S]['id_carga'] = $carga;
        }

        $this->mixHtml($html);
        $this->mixScript('
            ' . $this->scriptTablasDetalle($esClc) . '
        ');
    }

    public function rechaza()
    {
        $gpo_id = $_SESSION[_S]['gpo_id'];
        $ur_id = $_SESSION[_S]['ur_id'];
        $nivelUsuario = $_SESSION[_S]['nvj_id'];

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btn_regrecha';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-2';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';
        $btnregresarindex->click = ' comunica("' . $this->pantalla . '")';
        $btnregresarindex = $this->ponBoton($btnregresarindex);
        $this->agregaTools($btnregresarindex);

        $tipo = new stdClass();
        $tipo->id = 'tipo';
        $tipo->class = 'limpiar';
        if ($gpo_id . $ur_id == '118' || $nivelUsuario > 2) {
            $tipo->sql = '
                SELECT 1 cmv_id,"RECHAZAR CLC,CANCELAR AFECTACIONES Y COMPROMISOS" AS nom_rechazo
                UNION ALL
                SELECT 2  cmv_id,"CANCELAR AFECTACIONES Y COMPROMISOS" AS nom_rechazo
                UNION ALL
                SELECT 3  cmv_id,"CANCELAR COMPROMISOS" AS nom_rechazo
            ';
        } else {
            $tipo->sql = '
                SELECT 2  cmv_id,"CANCELAR AFECTACIONES Y COMPROMISOS" AS nom_rechazo
                UNION ALL
                SELECT 3  cmv_id,"CANCELAR COMPROMISOS" AS nom_rechazo
            ';
        }
        $tipo->label = 'SELECCIONA EL TIPO DE RECHAZO';
        $tipo->enRow = 1;
        $tipo->change = '
            if( $(this).val() != "" ){
                comunica("' . $this->pantalla . '",{
                metodo:"CapturaCarga",
                tipo: $("#tipo").val(),
                });
            }
        ';

        $html = '';
        $html .= '
            <div class="row col-md-12 col-xs-12" >
                <div class="col-md-12 col-xs-12" >
                    ' . $this->ponCombo($tipo) . '
                </div>
            </div>';
        $this->mixHtml($html);
    }

    private function CapturaCarga()
    {
        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : 0;

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';
        $btnregresarindex->click = ' comunica("' . $this->pantalla . '")';

        $btnregresarConsulta = new stdClass();
        $btnregresarConsulta->id = 'btnRegresaConsulta';
        $btnregresarConsulta->value = 'Regresar a Consulta';
        $btnregresarConsulta->type = 'button';
        $btnregresarConsulta->class = 'me-2';
        $btnregresarConsulta->color = 'gris';
        $btnregresarConsulta->icon = 'search me-2';
        $btnregresarConsulta->click = ' comunica("' . $this->pantalla . '",{metodo:"DetalleCarga"})';


        $this->agregaTools($this->ponBoton($btnregresarindex));
        $this->agregaTools($this->ponBoton($btnregresarConsulta));
        if ($this->prm->tipo != 0) {
            $carga_id = new stdClass();
            $carga_id->id = 'carga_id';
            $carga_id->label = 'CAPTURE LA CARGA MASIVA A RECHAZAR';
            $carga_id->enDiv = true;
            if (isset($this->prm->carga) && $this->prm->carga != '') {
                $carga_id->value = $this->prm->carga;
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo:"VerificarCargaMasiva",
                        carga_id: "' . $this->prm->carga . '",
                        tipo: "' . $this->prm->tipo . '"
                    });
                ');
            }

            $btnCarga = new stdClass();
            $btnCarga->id = 'btnCarga';
            $btnCarga->class = 'btn btn-test';
            $btnCarga->icon = 'eye';
            $btnCarga->color = 'verde';
            $btnCarga->value = 'Detalle Carga';

            /* Boton para rechazar toda la carga masiva */
            $btnCarga2 = new stdClass();
            $btnCarga2->id = 'btnCarga2';
            $btnCarga2->class = 'btn btn-test me-1';
            $btnCarga2->icon = 'bolt me-2';
            $btnCarga2->color = 'rojo';
            $btnCarga2->value = 'Rechazar Carga';

            $htm = '';
            $htm .= '
                <div class="row  mb-4">
                    <div class="col-md-4 col-xs-4">
                        ' . $this->ponInput($carga_id) . '
                    </div>
                    <div class="col-md-2 col-xs-2">
                    </div>
                    <div class="col-md-2 col-xs-2">
                        <br>
                        ' . $this->ponBoton($btnCarga2) . '
                    </div>
                </div>
                <div class="row" >
                    <div id="divcarga" class="col-md-12 col-xs-12">
                    </div>
                </div>
            ';

            $this->mixHtml($htm);
            $this->mixScript('
                var dialog;
                $("#btnCarga2").hide();
                $("#carga_id").blur(function(){
                    if( $("#carga_id").val()==""  || $("#"+ $("#carga_id").val()).length ==1)
                        return false;
                    $("#btnCarga").show();
                    comunica("' . $this->pantalla . '",{
                        metodo:"VerificarCargaMasiva",
                        carga_id: $("#carga_id").val(),
                        tipo: "' . $this->prm->tipo . '"
                    });
                });

                    $("#btnCarga2").click(function () {
                        alertify.confirm("RECHAZAR CARGA", "Confirme el rechazo de la Carga " + $("#carga_id").val(),
                            function () {
                                comunica("' . $this->pantalla . '", {
                                    metodo: "rechazaCargaMasiva",
                                    tipo: "' . $this->prm->tipo . '",
                                    carga_id: $("#carga_id").val()
                                });
                            },
                            function () { }
                        ).set("labels", { ok: "Aceptar", cancel: "Cancelar" });
                    });

            ');
        } else {
            $this->mixScript(' notify("Error en la Selección ","error"); ');
        }
    }

    private function VerificarCargaMasiva()
    {
        $this->prm->carga_id = (isset($this->prm->carga_id)) ? $this->prm->carga_id : 0;
        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : 0;
        /* VALIDAR CARGA  */

        $dat = $this->model->validaCarga($this->prm->carga_id);
        if (is_string($dat)) {
            $this->mixScript('notify("' . $dat . '","error");');
            return false;
        }

        $clcs = 0;
        $afectaciones = 0;
        $compromisos = 0;

        $this->setContenedor('divcarga');
        $html = '';
        $datosCarga = $this->model->getInfoCarga($this->prm->carga_id);
        $attr = [];
        $attr['idtabla'] = $this->prm->carga_id;
        $this->mixHtml($this->model->fn_pintaTabla($datosCarga, $attr));

        // Pestanas segun el tipo de rechazo (1: CLC+Afe+Cmp, 2: Afe+Cmp, 3: Cmp)
        // para no apilar las tablas verticalmente (se hacen muy extensas).
        $tabsDef = array();
        if ((int) $this->prm->tipo == 1) $tabsDef['clc'] = 'CLC';
        if ((int) $this->prm->tipo <= 2) $tabsDef['afe'] = 'Afectaciones';
        $tabsDef['cmp'] = 'Compromisos';
        $navRech = '';
        $paneRech = '';
        $primera = true;
        foreach ($tabsDef as $k => $lbl) {
            $navRech .= '<li class="nav-item" role="presentation"><button class="nav-link' . ($primera ? ' active' : '') . '" data-bs-toggle="tab" data-bs-target="#pane_rech_' . $k . '" type="button" role="tab">' . $lbl . '</button></li>';
            $paneRech .= '<div class="tab-pane fade' . ($primera ? ' show active' : '') . '" id="pane_rech_' . $k . '" role="tabpanel" style="max-height:60vh;overflow:auto"></div>';
            $primera = false;
        }
        $this->mixHtml('
            <style>
                #divcarga .tab-pane table th.success { position: sticky; top: 0; z-index: 3; }
            </style>
            <ul class="nav nav-tabs mt-3" role="tablist">' . $navRech . '</ul>
            <div class="tab-content pt-3">' . $paneRech . '</div>
        ');

        switch ($this->prm->tipo) {
            case 1:
                $this->setContenedor("pane_rech_clc");
                $datos = $this->model->getClcCancelables($this->prm->carga_id, false);
                $attr = [];
                $attr['ids'] = ['CLC'];
                $attr['idtabla'] = 'clc_' . $this->prm->carga_id;
                $attr['alineaciones'] = ['C', 'L', 'L', 'L', 'R', 'R', 'C'];
                $attr['formatos'] = ['ENTERO', 'ENTERO', 'TEXTO', 'TEXTO', 'MONEDA', 'MONEDA', 'TEXTO', 'TEXTO'];

                if ($datos['registros'] > 0) {
                    $html = $this->model->fn_pintaTabla($datos, $attr);
                    $this->mixHtml($html);

                    foreach ($datos['datos'] as $row) {
                        if ($row['Acción'] == 'CANCELABLE') {
                            $clcs++;
                        }
                    }

                    if ($clcs > 0) {
                        $this->mixScript('
                            $("#clc_' . $this->prm->carga_id . '").append(
                                $("<tr/>").append(
                                    $("<td/>").attr("colspan","' . count($datos['cabecera']) . '")
                                    .html("EXISTEN ' . $clcs . ' CLCS CANCELABLES").addClass("alert alert-success"))
                            );
                        ');
                    } else {
                        $this->mixScript('
                            $("#clc_' . $this->prm->carga_id . '").append(
                                $("<tr/>").append(
                                    $("<td/>").attr("colspan","' . count($datos['cabecera']) . '")
                                    .html("NO EXISTEN CLCS CANCELABLES").addClass("alert alert-danger"))
                            );
                        ');
                    }
                } else {
                    $this->mixScript('notify("No existen CLCs con el identificardor de carga ' . $this->prm->carga_id . ' ","error");');
                }

                // no break
            case 2:
                $this->setContenedor("pane_rech_afe");
                $datos = $this->model->getAfectacionesCancelables($this->prm->carga_id, false);
                $attr = [];
                $attr['ids'] = ['Afectación', 'CLC'];
                $attr['idtabla'] = 'afe_' . $this->prm->carga_id;
                $attr['alineaciones'] = ['C', 'C', 'C', 'R', 'R', 'C'];
                $attr['formatos'] = ['ENTERO', 'ENTERO', 'ENTERO', 'MONEDA', 'MONEDA', 'TEXTO', 'TEXTO'];


                if ($datos['registros'] > 0) {
                    $html = $this->model->fn_pintaTabla($datos, $attr);
                    $this->mixHtml($html);
                    foreach ($datos['datos'] as $row) {
                        if ($row['Acción'] == 'CANCELABLE') {
                            $afectaciones++;
                        }
                    }
                    if ($afectaciones == 0) {
                        $this->mixScript('
                            $("#afe_' . $this->prm->carga_id . '").append(
                            $("<tr/>").append(
                                $("<td/>").attr("colspan","' . count($datos['cabecera']) . '")
                                .html("NO EXISTEN AFECTACIONES CANCELABLES").addClass("alert alert-danger")));
                        ');
                    } else {
                        $this->mixScript('
                            $("#afe_' . $this->prm->carga_id . '").append(
                            $("<tr/>").append(
                                $("<td/>").attr("colspan","' . count($datos['cabecera']) . '")
                                .html("EXISTEN ' . $afectaciones . ' AFECTACIONES CANCELABLES").addClass("alert alert-success")));
                        ');
                    }
                } else {
                    $this->mixScript('
                        notify("No existen Afectaciones con el identificardor de carga ' . $this->prm->carga_id . ' ","error");
                    ');
                }

                // no break
            case 3:
                $this->setContenedor("pane_rech_cmp");
                $datos = $this->model->getCompromisosCancelables($this->prm->carga_id, false);
                $attr = [];
                $attr['ids'] = ['Compromiso', 'Acción'];
                $attr['idtabla'] = 'cmp_' . $this->prm->carga_id;



                if ($datos['registros'] > 0) {
                    $html = $this->model->fn_pintaTabla($datos, $attr);
                    $this->mixHtml($html);
                    foreach ($datos['datos'] as $row) {
                        if ($row['Acción'] == 'CANCELABLE') {
                            $compromisos++;
                        }
                    }

                    if ($compromisos > 0) {
                        $this->mixScript(' 
                            $("#cmp_' . $this->prm->carga_id . '").append(
                                $("<tr/>").append(
                                $("<td/>").attr("colspan","' . count($datos['cabecera']) . '")
                                .html("EXISTEN ' . $compromisos . ' COMPROMISOS CANCELABLES").addClass("alert alert-success")
                            ));
                        ');
                    } else {
                        $this->mixScript('
                            $("#cmp_' . $this->prm->carga_id . '").append(
                                $("<tr/>").append(
                                    $("<td/>").attr("colspan","' . count($datos['cabecera']) . '")
                                    .html("NO EXISTEN COMPROMISOS CANCELABLES").addClass("alert alert-danger")
                                )
                            );
                        ');
                    }
                } else {
                    $this->mixScript('notify("No existen Compromisos con el identificardor de carga ' . $this->prm->carga_id . ' ","error");');
                }
                break;
        }

        if (($clcs + $afectaciones + $compromisos) > 0) {
            $this->mixScript('
                $("#btnCarga2").show();
                $("#carga_id").prop("readonly", true).addClass( "alert-success" );
            ');
        } else {
            $this->mixScript('
                $("#carga_id").prop("readonly", true).addClass( "alert-danger" );
            ');
        }
    }

    private function DetalleCarga()
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl_Cargas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaDetalleCarga';
        $tabla->comunica->gpo = '{$(\"#combour\").find(\"option:selected\").attr(\"gpo_id\")}';
        $tabla->comunica->ur = '{$(\"#combour\").find(\"option:selected\").attr(\"ur_id\")}';
        $tabla->comunica->fecha = '{$(\"#fecha\").val()}';
        $tabla->comunica->estado = '{$(\"#estado\").find(\"option:selected\").val()}';

        $tabla->filasAmostrar = [10, 50, 100, 250, 500];
        $tabla->numFilas = 250;
        $tr = [];

        $th = new stdClass();
        $th->width = '5%';
        $th->texto = 'Liberar';
        array_push($tr, $th);

        $th = new stdClass();
        $th->width = '5%';
        $th->texto = 'Detalle';
        array_push($tr, $th);

        if ($this->gpo_id . $this->ur_id == '118') {
            $th = new stdClass();
            $th->width = '5%';
            $th->texto = 'Rechazar CLC';
            array_push($tr, $th);
        }
        $th = new stdClass();
        $th->width = '5%';
        $th->texto = 'Rechazar Afectacion';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '5%';
        $th->texto = 'Rechazar Compromiso';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CARGA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'USUARIO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '5%';
        $th->texto = 'Descarga FICHERO';
        array_push($tr, $th);
        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->width = '3%';
        $td->tipo = 'boton';
        $td->attr = new stdClass();
        $td->attr->crm_id = 'crm_id';
        $td->attr->tem_carga = 'tem_carga';
        $td->class = 'edit_carga btn-xs';
        $td->icono = 'eye';
        array_push($trbody, $td);


        // Detalle: siempre visible. Una carga "Completada" tambien puede tener
        // registros descuadrados, por eso se debe poder consultar su detalle.
        $td = new stdClass();
        $td->width = '5%';
        $td->tipo = 'boton';
        $td->attr = new stdClass();
        $td->attr->crm_id = 'crm_id';
        $td->attr->tem_carga = 'tem_carga';
        $td->class = 'ver_estado btn-xs';
        $td->icono = 'eye';
        array_push($trbody, $td);
        if ($this->gpo_id . $this->ur_id == '118') {
            $td = new stdClass();
            $td->width = '5%';
            $td->tipo = 'boton';
            $td->attr = new stdClass();
            $td->attr->crm_id = 'crm_id';
            $td->attr->tem_carga = 'tem_carga';
            $td->attr->tipo = 'tipo1';
            $td->class = 'rech_carga btn-xs';
            $td->icono = 'times fa-solid';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->width = '5%';
        $td->attr = new stdClass();
        $td->attr->crm_id = 'crm_id';
        $td->attr->tem_carga = 'tem_carga';
        $td->attr->tipo = 'tipo2';
        $td->class = 'rech_carga btn-xs';
        $td->icono = 'times fa-solid';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->width = '5%';
        $td->attr = new stdClass();
        $td->attr->crm_id = 'crm_id';
        $td->attr->tem_carga = 'tem_carga';
        $td->attr->tipo = 'tipo3';
        $td->class = 'rech_carga btn-xs';
        $td->icono = 'times';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'tem_carga';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Estado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Usuario';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Fecha';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->attr = new stdClass();
        $td->attr->crm_id = 'crm_id';
        $td->attr->tem_carga = 'tem_carga';
        $td->class = 'download_carga btn-outline-success';
        $td->icono = 'file-excel';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-2 ';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';
        $btnregresarindex->click = 'comunica("' . $this->pantalla . '");';
        $btnregresarindex = $this->ponBoton($btnregresarindex);

        $fecha = new stdClass();
        $fecha->id = 'fecha';
        $fecha->enDiv = true;
        $fecha->formato = 'fecha';
        $fecha = $this->ponInput($fecha);

        $estado = new stdClass();
        $estado->id = 'estado';
        $estado->enDiv = true;
        $estado->opciones = [0 => 'TODOS', 1 => 'ERROR', 2 => 'COMPLETADA'];
        $estado = $this->ponCombo($estado);

        $btnFiltra = new stdClass();
        $btnFiltra->id = 'btnFiltra';
        $btnFiltra->value = 'Filtrar';
        $btnFiltra->type = 'button';
        $btnFiltra->icon = 'search me-2';
        $btnFiltra->color = 'gris';
        $btnFiltra = $this->ponBoton($btnFiltra);

        $this->agregaTools(
            $btnregresarindex  . $fecha . ' ' . $estado . ' ' . $btnFiltra
        );
        $this->mixScript('
             $(".form-group").addClass("me-2");
            $("#combour").change(function(){
                ' . $this->wrLlamaLlenaTabla('tbl_Cargas') . '
            });
            $("#btnFiltra").click(function(){
                ' . $this->wrLlamaLlenaTabla('tbl_Cargas') . '
            });
            
            ' . $this->wrLlamaLlenaTabla('tbl_Cargas') . '
        ');
    }

    private function llenaDetalleCarga()
    {
        $this->prm->carga_id = (isset($this->prm->carga_id)) ? $this->prm->carga_id : '';
        $wf = '';
        $params = [
            'grupo' => $this->gpo_id,
            'ur' => $this->ur_id,
        ];
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $params['fecha'] = $this->prm->fecha;
            $wf .= ' AND DATE_FORMAT(c.usr_fecha,"%d/%m/%Y")= :fecha ';
        }

        if (isset($this->prm->fecha) && is_numeric($this->prm->estado)) {
            switch ($this->prm->estado) {
                case 1:
                    $wf .= ' AND ecr_id NOT IN(99,100) ';
                    break;
                case 2:
                    $wf .= ' AND ecr_id IN(99,100) ';
                    break;
            }
        }

        // $this->conectar();
        $sql = '
            SELECT
                crm_id,
                CONCAT(c.gpo_id,c.ur_id) AS ur,
                tem_carga,
                CASE ecr_id
                    WHEN 99  THEN  "Liberada"
                    WHEN 100 THEN  "Completada"
                    ELSE "ERROR"
                END  AS Estado,
                usr_usuario  AS Usuario,
                DATE_FORMAT(c.usr_fecha,"%d/%m/%Y") AS Fecha,
                "1" AS tipo1,
                "2" AS tipo2,
                "3" AS tipo3
            FROM cat_CargaMasiva c
            INNER JOIN cat_Usuario USING(gpo_id,ur_id, usr_id)
            WHERE c.gpo_id = :grupo AND c.ur_id = :ur
            ' . $wf . ' ORDER BY c.usr_fecha DESC';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 250;
        $nav->idTabla = 'tbl_Cargas';
        $this->setParams($params);
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        } else {
            $this->mixScript('
                function download(c){
                    var f = $("<form/>")
                    .attr("action","scp/afectaciones/downloadcarga.php")
                    .attr("target","_blank")
                    .attr("method","POST")
                    .append(
                        $("<input/>").attr("type","hidden").attr("name","carga").val(c)
                    ).appendTo("body").submit().remove();
                }

                $(".edit_carga").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"libera",
                        carga:$(this).attr("tem_carga")
                    });
                });
                $(".ver_estado").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"verDetalleCarga",
                        carga:$(this).attr("tem_carga")
                    });
                });

                $(".download_carga").click(function(){
                    download($(this).attr("tem_carga"));
                });

                $(".rech_carga").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"CapturaCarga",
                        tipo:$(this).attr("tipo"),
                        carga:$(this).attr("tem_carga")
                    });
                });
            ');
        }
    }

    public function rechazaCargaMasiva()
    {
        $this->setContenedor('divcarga');
        $html = '';
        /*  VALIDAR LOS PERMISOS Y POLITCAS PARA RECHAZAR LA CARGA MASIVA
            EN CASO QUE NO SIMPLEMENTE REGRESAMOS UN HTML 
            CON UN MENSAJE SIMPLE
            La Dirección de Presupuesto solicita que quien puede rechazar la Carga Masiva
            es el usuario que tenga el permisos X_0_0 y podrá rechazar cualquier carga masiva de su UR
            El analista podrá rechazar cualquier carga masiva de cualquier UR  
        */
        $idCarga = $this->prm->carga_id;
        $dat = $this->model->validaCarga($idCarga);
        if (is_string($dat)) {
            $this->mixScript('notify("' . $dat . '","error"); dialog.remove();');
            return false;
        }
        if (!$this->model->cargaExiste($idCarga)) {
            $this->mixScript('notify("La carga no existe","error");dialog.remove();');
            return false;
        }

        $detalleCarga = explode('-', $idCarga);

        switch ($this->nvj_id) {
            case 1:
            case 2:
                if ($this->gpo_id . $this->ur_id != '118' && $this->prm->tipo == 1) {
                    $this->mixHtml('<div class="alert alert-danger">NO ES POSIBLE RECHAZAR CARGAS DE ESTE TIPO</div>');
                    $this->mixScript('dialog.remove();');
                    return false;
                }
                if ($this->gpo_id . $this->ur_id != $detalleCarga[0]) {
                    $this->mixHtml('<div class="alert alert-danger">LA CARGA NO ES DE LA UR</div>');
                    $this->mixScript('dialog.remove();');
                    return false;
                }

                break;
            case 4:
                break;
            default:
                $this->mixHtml('<div class="alert alert-danger">NO ESTA AUTORIZADO A RECHAZAR CARGAS MASIVAS</div>');
                $this->mixScript('dialog.remove(); $(".modal-backdrop").remove();');

                return false;
                break;
        }

        switch ($this->prm->tipo) {
            case 1:
                $html .= $this->RechazarClc($this->prm->carga_id);
                // no break
            case 2:
                $html .= $this->CancelarAfectaciones($this->prm->carga_id);
                // no break
            case 3:
                $html .= $this->CancelarCompromisos($this->prm->carga_id);
                break;
        }
        $detalle = [];

        if ($this->gpo_id . $this->ur_id == '118' && $detalle[0] == '118') {
            $this->model->fn_InhabilitaClcCM($this->prm->carga_id);
        }
        $btnr = new stdClass();
        $btnr->id = 'btnr';
        $btnr->name = 'btnr';
        $btnr->text = 'Regresar';
        $btnr->value = 'Regresar';
        $btnr->type = 'button';
        $btnr->class = 'me-1';
        $btnr->color = 'gris';
        $btnr->icon = 'circle-left me-2';
        $btnr->click = ' comunica("' . $this->pantalla . '")';
        $this->agregaTools($this->ponBoton($btnr));
        $this->mixHtml($html);
        $this->mixScript('dialog.remove(); $("#btnCarga2").remove(); $(".modal-backdrop").remove();');
    }

    private function RechazarClc($carga)
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = $_SESSION[_S]['usr_id'];
        $sql = '
            SELECT CONCAT(gpo_id, ur_id) AS Ur, gpo_id, ur_id, clc_id, clc_concepto, clc_percepcion, clc_retencion
            FROM scp_Clc
            WHERE tem_carga = ? AND ecl_id = 3';

        $datos = $this->model->getDatos($sql, false, [$carga]);
        $errs = [];
        $conClc = 0;
        if ($datos['datos'][0] != '') {
            foreach ($datos['datos'] as $r) {
                $this->conectar();
                $this->setSql('SET @usr_id = ?, @usr_ip = INET_ATON(?),@bit_observacion="RECHAZO DE LA CARGA MASIVA CLC";');
                $this->logRechazo($this->getSql());
                $this->consultar([$_SESSION[_S]['usr_id'], $ip]);
                $this->setSql('CALL sp_RechazarCLC' . APP_EJERCICIO . '(?,?,?,INET_ATON(?),?)');
                $params = [
                    $r['gpo_id'],
                    $r['ur_id'],
                    $r['clc_id'],
                    $ip,
                    $usr_id,
                ];
                $this->consultar($params);
                $this->logRechazo($this->getSql());
                $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
                $this->consultar();
                $rowmensaje = $this->extraer();
                if ($rowmensaje['bandera'] != 1) {
                    $er = $r;
                    $er['motivo'] = $rowmensaje['mensaje'];
                    array_push($errs, $er);
                } else {
                    $conClc++;
                }
                $this->desconectar();
            }

            $titulo = 'DETALLE DE LAS CLCS CANCELADAS DE LA CARGA MASIVA CON IDENTIFICADOR ';
            $html = $this->vista->tbl_ResultadoCancela($carga, $datos['registros'], $conClc, $titulo, 'CLC');
            if (count($errs) > 0) {
                $html .= $this->vista->tbl_ClcNoCancelada($errs);
            }
            return $html;
        }
    }

    private function CancelarAfectaciones($carga)
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = $_SESSION[_S]['usr_id'];
        $this->model->fb_LiberaAfectacion($carga);
        $sql = '
            SELECT CONCAT(gpo_id, ur_id) AS Ur, gpo_id, ur_id, afe_id, afe_percepcion, afe_retencion, tcl_nombre
                FROM scp_Afectacion
            INNER JOIN cat_TipoClc USING ( tcl_id )
                WHERE tem_carga= ? AND clc_id = 0 AND eaf_id ="A"';
        $datos = $this->model->getDatos($sql, false, [$carga]);
        $afes = $datos['datos'];
        $afeCancel = 0;
        $errs = [];

        if ($datos['registros'] > 0) {

            if (!file_exists('log')) {
                mkdir('log', 0777, true);
            }
            if (!file_exists('log/carga')) {
                mkdir('log/carga', 0777, true);
            }

            foreach ($afes  as $r) {
                $this->conectar();
                $this->setSql('SET @usr_id = ?, @usr_ip = INET_ATON(?),@bit_observacion="RECHAZO DE LA CARGA MASIVA AFECTACION";');
                $this->consultar([$usr_id, $ip]);
                $rutalog = 'log/carga/RechazoCarga' . $carga . '.log';
                $file = fopen($rutalog, 'a');
                fwrite($file, 'L:' . $this->getSql() . PHP_EOL);
                fclose($file);
                $sql = 'CALL sp_CancelarAfectacion' . APP_EJERCICIO . '(?,?,?,INET_ATON(?),?);';
                $this->setSql($sql);
                $params = [
                    $r['gpo_id'],
                    $r['ur_id'],
                    $r['afe_id'],
                    $ip,
                    $usr_id,
                ];
                $this->consultar($params);


                $rutalog = 'log/carga/RechazoCarga' . $this->prm->carga_id . '.log';
                $file = fopen($rutalog, 'a');
                fwrite($file, 'L:' . $sql . PHP_EOL);
                fclose($file);

                $this->setSql('SELECT @banderaPresupuesto AS bandera,@mensajePresupuesto AS mensaje, @banderaContable as bc, @mensajeContable AS mc');
                $this->consultar();
                $men = $this->extraer();

                if ($men['bandera'] != 1) {
                    $er = $r;
                    $er['motivo'] = $men['mensaje'];
                    array_push($errs, $er);
                } else {
                    $afeCancel++;
                }
                $this->desconectar();
            }
        }

        $titulo = 'DETALLE DE LAS AFECTACIONES CANCELADAS DE LA CARGA MASIVA CON IDENTIFICADOR';
        $html = $this->vista->tbl_ResultadoCancela($carga, $datos['registros'], $afeCancel, $titulo, 'Afectaciones');
        if (count($errs) > 0) {
            $html .= $this->vista->tbl_AfectacionNoCancelada($errs);
        }
        return $html;
    }

    private function CancelarCompromisos($carga)
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $this->model->fb_LiberaCompromisos($carga);
        $sql = '
            SELECT CONCAT(gpo_id,ur_id) AS Ur,
            gpo_id,ur_id,cmp_id,cmp_concepto
            FROM scp_Compromiso 
            WHERE tem_carga = ?
            AND ecm_id="A"
            GROUP BY gpo_id,ur_id,cmp_id';
        $res = $this->model->getDatos($sql, false, [$carga]);
        $comCancel = 0;
        $errs = [];
        if ($res['registros'] > 0) {
            foreach ($res['datos'] as  $datos) {
                $this->conectar();
                $sql = 'SET @usr_id = ?, @usr_ip = INET_ATON(?),@bit_observacion="RECHAZO DE LA CARGA MASIVA COMPROMISO";';
                $this->setSql($sql);
                $this->consultar([$_SESSION[_S]['usr_id'], $ip]);

                $sql = 'CALL sp_CancelarCompromiso' . APP_EJERCICIO . '(?,?,?,INET_ATON(?),?);';
                $this->setSql($sql);
                $params = [
                    $datos['gpo_id'],
                    $datos['ur_id'],
                    $datos['cmp_id'],
                    $ip,
                    $user,
                ];

                $this->consultar($params);

                $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
                $this->consultar();
                $this->desconectar();
                $rowmensaje = $this->extraer();

                if ($rowmensaje['bandera'] != 1) {
                    $er = $datos;
                    $er['motivo'] = $rowmensaje['mensaje'];
                    array_push($errs, $er);
                } else {
                    $comCancel++;
                }
            }
        }

        $titulo = 'DETALLE DEL LOS COMPROMISOS CANCELADOS DE LA CARGA ';
        $tableErrores = '';
        $html = $this->vista->tbl_ResultadoCancela($carga, $res['registros'], $comCancel, $titulo, 'Compromisos');
        if (count($errs) > 0) {
            $html .= $this->vista->tbl_CompromisosNoCancelados($errs);
        }

        return $html . $tableErrores;
    }

    private function verDetalle()
    {
        if (!isset($this->prm->nivel)) {
            return false;
        }
        $data = null;
        switch ($this->prm->nivel) {
            case 1:
                $this->setContenedor('det_com');
                $data = $this->model->getCompromisoCarga($this->prm->carga);
                break;
            case 2:
                $this->setContenedor('det_afe');
                $data = $this->model->getAfectacionCarga($this->prm->carga);
                break;
            case 3:
                $this->setContenedor('det_clc');
                $data = $this->model->getClcCarga($this->prm->carga);
                break;
            default:
                return false;
                break;
        }
        $html = $this->vista->fn_pintaTabla($data);
        $this->mixHtml($html);
    }

    /**
     * Modal de detalle de la carga: en lugar del avance por fases, muestra
     * las CLC, Afectaciones y Compromisos que se cargaron, para poder revisar
     * si una carga (incluso "Completada") quedo con registros descuadrados.
     * Cada pestana es una wrPonTabla que se llena via llenaDetalleRegistros.
     */
    private function verDetalleCarga()
    {
        $carga = $this->prm->carga;

        // Estado de la carga (ecr_id) + analisis de anomalias.
        // Una carga bien cargada: CLC enviadas a finanzas (salieron de la UR, ecl_id NO en 1/2 = generada/captura),
        // afectaciones AUTORIZADAS (eaf_id = "A") y compromisos DEVENGADOS (ecm_id = "D").
        // Cualquier registro generado o en otro estado se considera anomalia -> la carga se debe rechazar.
        // La pestana de CLC solo aplica a cargas de CLCS (tcr_id = 2); las de AFECTACIONES (tcr_id = 1) no tienen CLC.
        $this->conectar();
        $this->setSql('
            SELECT c.tcr_id, ec.ecr_nombre,
                (SELECT COUNT(*) FROM scp_Clc        WHERE tem_carga = c.tem_carga) AS totClc,
                (SELECT COUNT(*) FROM scp_Afectacion WHERE tem_carga = c.tem_carga) AS totAfe,
                (SELECT COUNT(*) FROM scp_Compromiso WHERE tem_carga = c.tem_carga) AS totCmp
            FROM cat_CargaMasiva c
            LEFT JOIN cat_EstadoCarga ec USING (ecr_id)
            WHERE c.tem_carga = ? LIMIT 1');
        $this->consultar([$carga]);
        $this->desconectar();
        $rowTipo = $this->extraer();
        $esClc = (isset($rowTipo['tcr_id']) && (int) $rowTipo['tcr_id'] == 2);
        $estadoCarga = (isset($rowTipo['ecr_nombre']) && $rowTipo['ecr_nombre'] != '') ? $rowTipo['ecr_nombre'] : 'DESCONOCIDO';
        $totClc = isset($rowTipo['totClc']) ? (int) $rowTipo['totClc'] : 0;
        $totAfe = isset($rowTipo['totAfe']) ? (int) $rowTipo['totAfe'] : 0;
        $totCmp = isset($rowTipo['totCmp']) ? (int) $rowTipo['totCmp'] : 0;
        $tieneAnomalia = $this->cargaTieneAnomalias($carga);

        // Tabla resumen del estado de la carga + boton de rechazo (solo si hay anomalias; sin click por ahora).
        if ($tieneAnomalia) {
            $celdaAnomalia = '<span class="text-danger fw-bold">La carga tiene registros inconclusos, la carga falló, favor de rechazar y cargar de nuevo.</span>';
            $celdaBoton = '<button type="button" id="btnRechazarAnomalia" class="btn btn-danger btn-sm"><i class="fa-solid fa-ban me-1"></i>Rechazar</button>';
        } else {
            $celdaAnomalia = '<span class="text-muted fst-italic">Ninguna</span>';
            $celdaBoton = '';
        }
        $thClc = $esClc ? '<th>CLC</th>' : '';
        $tdClc = $esClc ? '<td>' . $totClc . '</td>' : '';
        $tblEstado = '
            <table class="table table-bordered mb-3">
                <thead><tr>
                    <th>Estado de la carga</th>' . $thClc . '<th>Afectaciones</th><th>Compromisos</th><th>Anomalías</th><th></th>
                </tr></thead>
                <tbody><tr>
                    <td>' . $estadoCarga . '</td>' . $tdClc . '<td>' . $totAfe . '</td><td>' . $totCmp . '</td><td>' . $celdaAnomalia . '</td><td>' . $celdaBoton . '</td>
                </tr></tbody>
            </table>';

        $this->mixHtml('
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Detalle de la carga masiva: ' . $carga . '</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . $tblEstado . '
                            ' . $this->tabsDetalleCarga($carga, $esClc) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        ');
        $this->mixScript('
            $("#staticBackdrop").modal("show");
            ' . $this->scriptTablasDetalle($esClc) . '
            // Reconstruir la lista al cerrar por cualquier via (X, Cerrar, ESC).
            $("#staticBackdrop").on("hidden.bs.modal", function() {
                comunica("' . $this->pantalla . '",{metodo:"DetalleCarga"});
            });
            // Rechazar: cierra el modal y va a la pantalla de liberar con el id ya pintado
            // (igual que el boton Liberar de la lista principal).
            $("#btnRechazarAnomalia").click(function() {
                $("#staticBackdrop").off("hidden.bs.modal").one("hidden.bs.modal", function() {
                    comunica("' . $this->pantalla . '",{metodo:"libera",carga:"' . $carga . '"});
                });
                $("#staticBackdrop").modal("hide");
            });
        ');
    }

    /**
     * Bloque de pestanas (CLC / Afectaciones / Compromisos) con wrPonTabla.
     * Se reutiliza en el modal de la lista y en la pantalla de liberar (analiza).
     * La pestana CLC solo se incluye cuando la carga es de CLCS ($esClc).
     */
    private function tabsDetalleCarga($carga, $esClc)
    {
        $tAfe = $this->wrShellTablaDetalle('tbl_det_afe', 'afe', $carga, array(
            'UR' => 'UR', 'Tipo' => 'Tipo', 'Subtipo' => 'Subtipo', 'Numero' => 'Numero',
            'Percepción' => 'Percepcion', 'Retención' => 'Retencion',
            'Estado' => 'Estado'
        ), array('Percepcion', 'Retencion'));
        $tCmp = $this->wrShellTablaDetalle('tbl_det_cmp', 'cmp', $carga, array(
            'UR' => 'UR', 'Numero' => 'Numero', 'Importe' => 'Importe',
            'Clave Presupuestal' => 'ClavePresupuestal', 'Estado' => 'Estado'
        ), array('Importe'));

        $navClc = '';
        $paneClc = '';
        if ($esClc) {
            $tClc = $this->wrShellTablaDetalle('tbl_det_clc', 'clc', $carga, array(
                'UR' => 'UR', 'Tipo' => 'Tipo', 'Subtipo' => 'Subtipo',
                'Numero' => 'Numero', 'Concepto' => 'Concepto',
                'Percepción' => 'Percepcion', 'Retención' => 'Retencion',
                'Estado' => 'Estado'
            ), array('Percepcion', 'Retencion'));
            $navClc = '
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-clc" data-bs-toggle="tab" data-bs-target="#pane-clc" type="button" role="tab">CLC</button>
                </li>';
            $paneClc = '<div class="tab-pane fade show active" id="pane-clc" role="tabpanel" style="max-height:60vh;overflow:auto">' . $tClc . '</div>';
        }

        // Si no hay CLC, la pestana activa por defecto es Afectaciones.
        $activaAfe = $esClc ? '' : ' active';
        $paneActivaAfe = $esClc ? '' : ' show active';

        return '
            <style>td.Percepcion, td.Retencion, td.Importe { text-align: right; }</style>
            <ul class="nav nav-tabs" id="detalleTab" role="tablist">
                ' . $navClc . '
                <li class="nav-item" role="presentation">
                    <button class="nav-link' . $activaAfe . '" id="tab-afe" data-bs-toggle="tab" data-bs-target="#pane-afe" type="button" role="tab">Afectaciones</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-cmp" data-bs-toggle="tab" data-bs-target="#pane-cmp" type="button" role="tab">Compromisos</button>
                </li>
            </ul>
            <div class="tab-content pt-3">
                ' . $paneClc . '
                <div class="tab-pane fade' . $paneActivaAfe . '" id="pane-afe" role="tabpanel" style="max-height:60vh;overflow:auto">' . $tAfe . '</div>
                <div class="tab-pane fade" id="pane-cmp" role="tabpanel" style="max-height:60vh;overflow:auto">' . $tCmp . '</div>
            </div>';
    }

    /**
     * JS que dispara la carga de las tablas del detalle (solo las que aplican).
     */
    private function scriptTablasDetalle($esClc)
    {
        return ($esClc ? $this->wrLlamaLlenaTabla('tbl_det_clc') : '') . '
            ' . $this->wrLlamaLlenaTabla('tbl_det_afe') . '
            ' . $this->wrLlamaLlenaTabla('tbl_det_cmp');
    }

    /**
     * Regla unica de anomalias: una carga esta "bien cargada" solo si TODOS sus
     * registros llegaron a su estado final:
     *   - CLC enviadas a finanzas (ecl_id NO en 1/2 = generada/captura),
     *   - Afectaciones AUTORIZADAS (eaf_id = "A"),
     *   - Compromisos DEVENGADOS (ecm_id = "D").
     * Si algun registro quedo generado / sin enviar / en otro estado -> hay anomalia
     * y la carga se debe poder rechazar aunque este marcada como "Completada".
     */
    private function cargaTieneAnomalias($carga)
    {
        $this->conectar();
        $this->setSql('
            SELECT
                (SELECT COUNT(*) FROM scp_Clc
                WHERE tem_carga = :carga_id AND ecl_id IN (1,2)) +
                (SELECT COUNT(*) FROM scp_Afectacion WHERE tem_carga = :carga_id AND eaf_id <> "A") +
                (SELECT COUNT(*) FROM scp_Compromiso WHERE tem_carga = :carga_id AND ecm_id <> "D") AS anomalias');
        $this->consultar([':carga_id' => $carga]);
        $this->desconectar();
        $row = $this->extraer();
        return isset($row['anomalias']) && (int) $row['anomalias'] > 0;
    }

    /**
     * Arma el shell de una wrPonTabla del detalle. $cols: [titulo => campoTxt].
     */
    private function wrShellTablaDetalle($id, $tipo, $carga, $cols, $derecha = array())
    {
        $tabla = new stdClass();
        $tabla->id = $id;
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaDetalleRegistros';
        $tabla->comunica->tipo = $tipo;
        $tabla->comunica->carga = $carga;
        $tabla->filasAmostrar = array(10, 50, 100, 250, 500);
        $tabla->numFilas = 50;

        $tr = array();
        $trbody = array();
        foreach ($cols as $titulo => $campo) {
            $th = new stdClass();
            $th->texto = $titulo;
            // El plugin de tabla alinea el encabezado con th.align (text-align).
            if (in_array($campo, $derecha)) {
                $th->align = 'right';
            }
            array_push($tr, $th);

            $td = new stdClass();
            $td->campoTxt = $campo;
            array_push($trbody, $td);
        }
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        return $this->wrPonTabla($tabla);
    }

    /**
     * Alimenta cada tabla del detalle (CLC / Afectaciones / Compromisos)
     * segun el parametro tipo. Reusa las mismas consultas del modelo.
     */
    private function llenaDetalleRegistros()
    {
        $carga = isset($this->prm->carga) ? $this->prm->carga : '';
        $tipo  = isset($this->prm->tipo) ? $this->prm->tipo : '';

        switch ($tipo) {
            case 'clc':
                $id = 'tbl_det_clc';
                $sql = '
                    SELECT
                        CONCAT(gpo_id,ur_id) AS UR,
                        tcl_nombre AS Tipo,
                        stc_nombre AS Subtipo,
                        clc_id AS Numero,
                        clc_concepto AS Concepto,
                        FORMAT(clc_percepcion, 2) AS Percepcion,
                        FORMAT(clc_retencion, 2) AS Retencion,
                        IF(ecl_id NOT IN (1,2,15,17),"En Finanzas",ecl_nombre) AS Estado
                    FROM scp_Clc
                    INNER JOIN cat_EstadoClc USING (ecl_id)
                    INNER JOIN cat_TipoClc USING (tcl_id)
                    INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                    WHERE tem_carga = :carga';
                break;
            case 'afe':
                $id = 'tbl_det_afe';
                $sql = '
                    SELECT
                        CONCAT(gpo_id,ur_id) AS UR,
                        tcl_nombre AS Tipo,
                        stc_nombre AS Subtipo,
                        afe_id AS Numero,
                        FORMAT(afe_percepcion, 2) AS Percepcion,
                        FORMAT(afe_retencion, 2) AS Retencion,
                        eaf_nombre AS Estado
                    FROM scp_Afectacion
                    INNER JOIN cat_EstadoAfectacion USING (eaf_id)
                    INNER JOIN cat_TipoClc USING (tcl_id)
                    INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                    WHERE tem_carga = :carga';
                break;
            case 'cmp':
                $id = 'tbl_det_cmp';
                $sql = '
                    SELECT
                        CONCAT(gpo_id,ur_id) AS UR,
                        cmp_id AS Numero,
                        FORMAT(cmp_importe, 2) AS Importe,
                        pre_claveCompleta AS ClavePresupuestal,
                        ecm_nombre AS Estado
                    FROM scp_Compromiso
                    INNER JOIN presupuesto USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN cat_EstadoCompromiso USING (ecm_id)
                    WHERE tem_carga = :carga';
                break;
            default:
                return false;
        }

        $this->setSql($sql);
        $this->setParams(array('carga' => $carga));
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 250;
        $nav->idTabla = $id;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("Sin registros","warning");');
        }
    }

    private function sp_libera()
    {
        $mensaje = '';
        $tipo = '';

        if (!$this->escritura) {
            $this->mixScript('notify("Sin permisos para este proceso","error") ');
            return;
        }

        $mensaje = '';
        $tipo = '';
        $carga = isset($_SESSION[_S]['id_carga']) ? $_SESSION[_S]['id_carga'] : false;
        if (!$carga) {
            $mensaje = 'Error en el proceso, intente nuevamente';
            $tipo = 'error';
        } elseif (!isset($this->prm->observacion) || $this->prm->observacion == '') {
            $mensaje = 'la observación es necesaria';
            $tipo = 'warning';
        } else {
            $prm = ['tem_carga' => $carga, 'observacion' => $this->prm->observacion];
            /* LIBERAR LA CARGA MASIVA */
            if ($this->model->fn_LiberaCarga($prm)) {
                $mensaje = 'se actualizo la carga de manera correcta';
                $tipo = 'success';
                unset($_SESSION[_S]['id_carga']);
                $this->mixScript(' $("#btn_libera").remove();');
                // Recargar las tablas del detalle para que reflejen el rechazo (registros cancelados
                // o eliminados). Antes quedaban con el estado previo (GENERADO) y parecia que no paso
                // nada. Solo se redisparan las tablas que estan en pantalla (la de CLC puede no existir).
                $this->mixScript('
                    if ($("#tbl_det_clc").length) { ' . $this->wrLlamaLlenaTabla('tbl_det_clc') . ' }
                    if ($("#tbl_det_afe").length) { ' . $this->wrLlamaLlenaTabla('tbl_det_afe') . ' }
                    if ($("#tbl_det_cmp").length) { ' . $this->wrLlamaLlenaTabla('tbl_det_cmp') . ' }
                ');
            } else {
                $mensaje = 'error';
                $tipo = 'warning';
            }
            /* LIBERAR CLAVES PRESUPUESTALES */
        }
        $this->mixScript('notify("' . $mensaje . '","' . $tipo . '") ');
    }

    public function downloadCM()
    {
        $carga = $this->prm->carga;
        $fichero = '';
        $base    = '';
        $this->prm->lnk = 'afectaciones/' . date('m') . '/Carga_Masiva/';

        if (isset($this->prm->lnk)) {
            $tmp = explode('/', $this->prm->lnk);
            $base = $tmp[0] . '/' . $tmp[1] . '/' . $tmp[2];
            $fichero = $tmp[3];
        }

        $r = new RutaArchivoSefip();
        $r->setBase('SCP');
        $r->setPath($base);
        $r->setAnioFichero(APP_EJERCICIO);
        $r->setUrFichero($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur']);
        $ruta = $r->getRutaMod();

        $fichero = $ruta . $carga;
        $rutatmp = './../../archivos/tmpm/';
        $tipo = '';
        $contenttype = 'application/octet-stream';

        if (file_exists($fichero . '.xlsx')) {
            $tipo = 'xlsx';
            $contenttype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        if (file_exists($fichero . '.xls')) {
            $tipo = 'xls';
            $contenttype = 'application/vnd.ms-excel';
        }
        if ($tipo == '') {
            $this->mixScript('notify("Archivo de Carga no existe","warning");');
            return;
        }
        copy($fichero . '.' . $tipo, $rutatmp . $carga . '.' . $tipo);
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contenttype . ' ' . $tipo);
        header('Content-Disposition:file-transfer;filename="' . $carga . '.' . $tipo . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($rutatmp . $carga . '.' . $tipo));
        readfile($rutatmp . $carga . '.' . $tipo);
        unlink($rutatmp . $carga . '.' . $tipo);
        die();
    }

    private function logRechazo($data)
    {
        /* ----- LOG DE ERROROES DE CONEXION -------- */
        if (!file_exists('./log')) {
            mkdir('./log', 0777, true);
        }

        $rutalog = './log/logRechazoCarga' . $_SESSION[_S]['usr_id'];
        $texto = date('d-m-Y H:i:s')
            . 'CLASE[' . get_class($this) . ']||'
            . 'CLIENTE[' . $_SERVER['REMOTE_ADDR'] . ']||'
            . 'OBSERVACION[' . $data . ']';

        $file = fopen($rutalog . '.log', 'a');
        fwrite($file, $texto . PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog . '.log');
        $peso = intval(($peso / 1024) / 1024);
        $hoy = date('d-m-Y');
        if ($peso > 8) {
            rename($rutalog . '.log', $rutalog . $hoy . '.txt');
            $file = fopen($rutalog . '.log', 'a');
            fwrite($file, 'CREADO:' . $hoy . PHP_EOL);
            fclose($file);
        }
        unset($file);
    }
    public function verDetalleProgreso($validabtn = false)
    {
        $tem_carga = $this->prm->carga;
        $sql = "SELECT
            -- VAlidacion en copiar Archivo
        CASE
                when (
                SELECT
                    IFNULL(ecr_id, 0)
                FROM
                    " . BD_BITACORA . ".bit_CargaMasiva
                WHERE
                    tem_carga = :tem_carga
                    and ecr_id = 35
                    and bit_observacion = 'FINALIZA EL COPIADO DEL FICHERO'
        ) <> 0 then 'success'
                ELSE (
        CASE
                    when
        (
                    SELECT
                        count(*)
                    FROM
                        " . BD_BITACORA . ".bit_CargaMasiva a
                    where
                        bit_id <= (
                        SELECT
                            IFNULL((SELECT bit_id FROM " . BD_BITACORA . ".bit_CargaMasiva   
        WHERE tem_carga = a.tem_carga
        and ecr_id = 1
        and bit_observacion = 'FINALIZA EL COPIADO DEL FICHERO'
        limit 1) , (
            SELECT
                IFNULL(bit_id, 0)
            FROM
                " . BD_BITACORA . ".bit_CargaMasiva
            WHERE
                tem_carga = a.tem_carga
                and ecr_id = 69
                and bit_observacion = 'SE CREA LA CARGA MASIVA'
            limit 1)))
                        AND bit_id >= (
                        SELECT
                            IFNULL(bit_id, 0)
                        FROM
                            " . BD_BITACORA . ".bit_CargaMasiva
                        WHERE
                            tem_carga = a.tem_carga
                            and ecr_id = 1
                            and bit_observacion = 'SE CREA LA CARGA MASIVA'
                        limit 1)
                        and tem_carga = :tem_carga
        )= 0 then 'defaualt'
                    ELSE 'danger'
                END
        )
            END as fase1,
            -- Validacion en Preparacion de Archivo
        CASE
                when (
                SELECT
                    IFNULL(ecr_id, 0)
                FROM
                    " . BD_BITACORA . ".bit_CargaMasiva
                WHERE
                    tem_carga =:tem_carga
                    and ecr_id = 70
                    and bit_observacion = 'FINALIZA LA PREPARACION DE DATOS SIN CONTRATIEMPOS'
        ) <> 0 then 'success'
                ELSE 
        (
        CASE
                    when
        (
                    SELECT
                        count(*)
                    FROM
                        " . BD_BITACORA . ".bit_CargaMasiva b
                    where
                        bit_id <= (
                        SELECT
                            IFNULL((SELECT bit_id FROM " . BD_BITACORA . ".bit_CargaMasiva   
        WHERE tem_carga = b.tem_carga
        and ecr_id = 70 
        and bit_observacion = 'FINALIZA LA PREPARACION DE DATOS SIN CONTRATIEMPOS'
        limit 1) , (
            SELECT
                IFNULL(bit_id, 0)
            FROM
                " . BD_BITACORA . ".bit_CargaMasiva
            WHERE
                tem_carga = b.tem_carga
                and ecr_id = 69
                and bit_observacion = 'INICIA LA PREPARACION DE DATOS- LINEA 1643'
            limit 1)))
                        AND bit_id >= (
                        SELECT
                            IFNULL(bit_id, 0)
                        FROM
                            " . BD_BITACORA . ".bit_CargaMasiva
                        WHERE
                            tem_carga = b.tem_carga
                            and ecr_id = 69
                            and bit_observacion = 'INICIA LA PREPARACION DE DATOS- LINEA 1643'
                        limit 1)
                        and tem_carga = :tem_carga
        )= 0 then 'defaualt'
                    ELSE 'danger'
                END
        )
            END as fase2,
            -- Validacion en revision de datos 
        CASE
                when (
                SELECT
                    IFNULL(ecr_id, 0)
                FROM
                    " . BD_BITACORA . ".bit_CargaMasiva
                WHERE
                    tem_carga = :tem_carga
                    and ecr_id = 6
                    and bit_observacion = 'FIN DE LA REVISION'
        ) <> 0 then 'success'
                ELSE (
        CASE
                    when (
                    SELECT
                        count(*)
                    FROM
                        " . BD_BITACORA . ".bit_CargaMasiva c
                    where
                        bit_id <= (
                        SELECT
                            IFNULL((SELECT bit_id FROM " . BD_BITACORA . ".bit_CargaMasiva   
        WHERE tem_carga = c.tem_carga
        and ecr_id = 6 
        and bit_observacion = 'FIN DE LA REVISION'
        limit 1) , (
            SELECT
                IFNULL(bit_id, 0)
            FROM
                " . BD_BITACORA . ".bit_CargaMasiva
            WHERE
                tem_carga = c.tem_carga
                and ecr_id = 5
                and bit_observacion = 'INICIA LA REVISION DE DATOS'
            limit 1)))
                        AND bit_id >= (
                        SELECT
                            IFNULL(bit_id, 0)
                        FROM
                            " . BD_BITACORA . ".bit_CargaMasiva
                        WHERE
                            tem_carga = c.tem_carga
                            and ecr_id = 5
                            and bit_observacion = 'INICIA LA REVISION DE DATOS'
                        limit 1)
                        and tem_carga = :tem_carga
        ) = 0 then 'defaualt'
                    ELSE 'danger'
                END)
            END as fase3,
            -- Validacion en Cargar datos
        CASE
                when (
                SELECT
                    IFNULL(ecr_id, 0)
                FROM
                    " . BD_BITACORA . ".bit_CargaMasiva
                WHERE
                    tem_carga = :tem_carga
                    and ecr_id = 100
                    and bit_observacion = 'CARGA EXITOSA'
        ) <> 0 then 'success'
                ELSE (
        CASE
                    when
        (
                    SELECT
                        count(*)
                    FROM
                        " . BD_BITACORA . ".bit_CargaMasiva d
                    where
                        bit_id <= (
                        SELECT
                            IFNULL((SELECT bit_id FROM " . BD_BITACORA . ".bit_CargaMasiva   
        WHERE tem_carga = d.tem_carga
        and ecr_id = 100 
        and bit_observacion = 'CARGA EXITOSA'
        limit 1) , (
            SELECT
                IFNULL(bit_id, 0)
            FROM
                " . BD_BITACORA . ".bit_CargaMasiva
            WHERE
                tem_carga = d.tem_carga
                and ecr_id = 69
                and bit_observacion = 'INICIA LA CARGA DE DATOS TIPO DE CARGA1'
            limit 1)))
                        AND bit_id >= (
                        SELECT
                            IFNULL(bit_id, 0)
                        FROM
                            " . BD_BITACORA . ".bit_CargaMasiva
                        WHERE
                            tem_carga = d.tem_carga
                            and ecr_id = 69
                            and bit_observacion = 'INICIA LA CARGA DE DATOS TIPO DE CARGA1'
                        limit 1)
                        and tem_carga = :tem_carga
        )= 0 then 'defaualt'
                    ELSE 'danger'
                END)
            END as fase4
            -- FIN DE LAS VALIDACIONES
            ";
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([':tem_carga' => $tem_carga]);
        $this->desconectar();
        $row = $this->extraer();
        $copiar = $row['fase1'] ?? '';
        $prepar = $row['fase2'] ?? '';
        $revision = $row['fase3'] ?? '';
        $carga = $row['fase4'] ?? '';
        if (!$validabtn) {

            $td1 = $copiar == 'success' ? '<th class="table-' . $copiar . '"><i class="fa-solid fa-circle-check"></i></th>' : '<th class="table-' . $copiar . '"><i class="fa-solid fa-ban"></i></th>';
            $td2 = $prepar == 'success' ? '<th class="table-' . $prepar . '"><i class="fa-solid fa-circle-check"></i></th>' : '<th class="table-' . $prepar . '"><i class="fa-solid fa-ban"></i></th>';
            $td3 = $revision == 'success' ? '<th class="table-' . $revision . '"><i class="fa-solid fa-circle-check"></i></th>' : '<th class="table-' . $revision . '"><i class="fa-solid fa-ban"></i></th>';
            $td4 = $carga == 'success' ? '<th class="table-' . $carga . '"><i class="fa-solid fa-circle-check"></i></th>' : '<th class="table-' . $carga . '"><i class="fa-solid fa-ban"></i></th>';
            $this->mixHtml('
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">Progreso carga masiva: ' . $tem_carga . '</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Copiar Archivo</th>
                                            <th scope="col">Preparacion de Carga</th>
                                            <th scope="col">Revision de Carga</th>
                                            <th scope="col">Carga de Datos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            ' . $td1 . '
                                            ' . $td2 . '
                                            ' . $td3 . '
                                            ' . $td4 . '
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" onclick="RegresaDetalle()" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            ');
            $this->mixScript('
        $("#staticBackdrop").modal("show");
        function RegresaDetalle() {
            comunica("' . $this->pantalla . '",{metodo:"DetalleCarga"});
        }
        ');
        } else {
            if ($copiar &&  $prepar && $revision && $carga) {
                return true;
            } else {
                return false;
            }
        }
    }
}

// Blindaje de salida JSON: los require (WebResponse/FicheroSefip) reactivan display_errors/E_ALL,
// y en PHP 8 cualquier aviso (Deprecated, Notice o Warning como "Undefined array key") se
// imprimiria antes del JSON de comunica y romperia JSON.parse en el cliente. Como este endpoint
// siempre devuelve JSON, se apaga la impresion de errores (siguen registrandose en el log).
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

if (isset($_SESSION[_S]['prm']['S']['2']['21']['1']) || isset($_SESSION[_S]['prm']['S']['2']['1']['1'])) {
    new ControladorRechazaCargaMasiva();
} else {
    WebResponse::sinPermisos();
}
