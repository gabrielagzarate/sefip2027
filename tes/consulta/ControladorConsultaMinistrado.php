<?php

/**
 * Description of ControladorConsultaMinistrado
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Migrated: 30-01-2019
 * Comment: -----
 * Objective:
 * Regla de Negocio:
 * Permits:
 * */

session_start();
ini_set('max_execution_time', 300);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2026Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2024Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025ClcSefip2025.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2023Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2022Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2021Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2020Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2019Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2018Clc.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2018LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2019LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2020LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2021LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2022LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2023LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2024LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2026LCF.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025LCFSefip2025.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2018LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2019LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2020LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2021LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2022LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2023LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2024LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2026LCT.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025LCTSefip2025.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2018DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2019DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2020DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2021DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2022DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2023DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2024DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2026DocMun.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2018DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2019DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2020DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2021DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2022DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2023DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2024DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025DocLCSefip2025.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2026DocLC.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2018Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2019Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2020Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2021Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2022Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2023Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2024Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2026Pdp.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2025PdpSefip2025.php';
require_once dirname(__FILE__) . '/../../lib/tes/reportes/consultaTesPendientesMin2017Clc.php';

class ControladorConsultaMinistrado extends WebResponse
{

    public $tipo;
    public $fechaMin;
    public $fechaMax;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "tes/consulta/ControladorConsultaMinistrado.php";
        $this->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '' ? $this->prm->tipo : "");
        $this->fechaMin = (isset($this->prm->fechaMin) && $this->prm->fechaMin != '' ? $this->prm->fechaMin : "");
        $this->fechaMax = (isset($this->prm->fechaMax) && $this->prm->fechaMax != '' ? $this->prm->fechaMax : "");
        $this->metodo = (isset($this->prm->metodo) && $this->prm->metodo != '' ? $this->prm->metodo : "");

        switch ($this->metodo) {
            case 'pendientesMinistrar':
                $this->pendientesMinistrar();
                break;
            case 'pintaTabla':
                $this->pintaTabla();
                break;
            case 'llenaTabla':
                $this->llenaTabla();
                break;
            case "cargaReporte":
                $this->cargaReporte();
                break;
            default:
                $this->main();
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
            <div class="row class="col-md-12 col-xs-12 "">
                <legend>CONSULTAS PENDIENTES DE MINISTRAR</legend>
                <fieldset class="col-md-2 col-xs-2 col-md-offset-2"  id="">
                    <div titpp="CONSULTA PENDIENTES" class="d-flex flex-column boton_opcion" id="consulta_PendientesMinistrar">
                        <div class="boton_opcion_titulo" >PENDIENTES DE CONCILIAR</div>
                        <strong><img height="60" width="60" src="img/Autoriza_Folio.png"></strong>
                        <small>Consulta </small>
                    </div>
                </fieldset>
            </div>
        ');

        $this->mixScript('
                $("#consulta_PendientesMinistrar").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"pendientesMinistrar",
                        tipo:1
                    });
                });
                $("#consulta_Conciliadas").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"pendientesMinistrar",
                        tipo:2
                    });
                });
            ');
    }
    private function pendientesMinistrar()
    {
        //crea el boton de regreso
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation();});
            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
            $("#btnRegresar").removeClass("btn-default");
        ');
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
        // Regresa a la pantalla anterior
        $("#contenedor").html("");
        $("#toolsPantalla").html("");
        comunica("' . $this->pantalla . '",{
        metodo: "main"
        });
        ';
        // boten de excel
        $btnExcel = new stdClass();
        $btnExcel->id = "exbExcel";
        $btnExcel->color = "verde";
        $btnExcel->value = "Excel";
        $btnExcel->icon = "solid fa-file-excel me-2";
        $btnExcel->name = "prueba";
        $btnExcel->click = '
            if($("#fechaMin").val()!="" && $("#fechaMax").val()!=""){
                var nameFileToCall="";
                if($("#ejercicio").val()==0){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin' . APP_EJERCICIO . 'Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin' . APP_EJERCICIO . 'LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin' . APP_EJERCICIO . 'LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin' . APP_EJERCICIO . 'DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin' . APP_EJERCICIO . 'DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin' . APP_EJERCICIO . 'Pdp";
                    }
                } else if($("#ejercicio").val()==1){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2025Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2025LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2025LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2025DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2025DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2025Pdp";
                    }else if($("#modo").val()==7){
                        nameFileToCall="exp_PendientesMin2025ClcEnSefip2025";
                    }else if($("#modo").val()==8){
                        nameFileToCall="exp_PendientesMin2025LCFEnSefip2025";
                    }else if($("#modo").val()==9){
                        nameFileToCall="exp_PendientesMin2025LCTEnSefip2025";
                    }else if($("#modo").val()==10){
                        nameFileToCall="exp_PendientesMin2025DocLCSefip2025";
                    }else if($("#modo").val()==11){
                        nameFileToCall="exp_PendientesMin2025PdpEnSefip2025";
                    }
                } else if($("#ejercicio").val()==1){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2024Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2024LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2024LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2024DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2024DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2024Pdp";
                    }
                } else if($("#ejercicio").val()==2){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2023Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2023LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2023LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2023DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2023DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2023Pdp";
                    }
                }else if($("#ejercicio").val()==3){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2022Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2022LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2022LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2022DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2022DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2022Pdp";
                    }
                }else if($("#ejercicio").val()==4){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2021Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2021LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2021LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2021DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2021DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2021Pdp";
                    }
                }else if($("#ejercicio").val()==5){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2020Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2020LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2020LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2020DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2020DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2020Pdp";
                    }
                }else if($("#ejercicio").val()==6){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2019Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2019LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2019LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2019DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2019DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2019Pdp";
                    }
                }else if($("#ejercicio").val()==7){
                    if($("#modo").val()==1){
                        nameFileToCall="exp_PendientesMin2018Clc";
                    }else if($("#modo").val()==2){
                        nameFileToCall="exp_PendientesMin2018LCF";
                    }else if($("#modo").val()==3){
                        nameFileToCall="exp_PendientesMin2018LCT";
                    }else if($("#modo").val()==4){
                        nameFileToCall="exp_PendientesMin2018DocMun";
                    }else if($("#modo").val()==5){
                        nameFileToCall="exp_PendientesMin2018DocLC";
                    }else if($("#modo").val()==6){
                        nameFileToCall="exp_PendientesMin2018Pdp";
                    }
                }else if($("#ejercicio").val()==8){
                    nameFileToCall="exp_PendientesMin2017Clc";
                }else if($("#ejercicio").val()==9){
                    nameFileToCall="exp_PendientesMin2016Clc";
                }
                $("#SearchExtracto").prop("action","rep/excel/tes/consulta/"+nameFileToCall+".php");
                $("#SearchExtracto").submit();
            }else{
                notify("INTRODUZCA UN RANGO DE FECHAS", "warning");
            }
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        $this->agregaTools($this->ponBoton($btnExcel));

        $ejercicio = array('0' => APP_EJERCICIO,'1' => "2025", '2' => "2024", '3' => "2023", '4' => "2022", '5' => "2021");

        if ($this->serverProduccion) {
            $ejercicio['6'] = "2020";
            $ejercicio['7'] = "2019";
            $ejercicio['8'] = "2018";
            $ejercicio['9'] = "2017";
        }

        $inputEjercicio = new stdClass();
        $inputEjercicio->opciones = $ejercicio;
        $inputEjercicio->id = 'ejercicio';
        $inputEjercicio->name = 'ejercicio';
        $inputEjercicio->mensaje = 'ejercicio';
        $inputEjercicio->enDiv = true;
        $inputEjercicio->label = 'Ejercicio';
        if ($this->prm->tipo == 3) {
            $modo = array('1' => "CLC", '2' => "PDP", '3' => "L.C ESTATAL", '4' => "MUNICIPIOS");
        } elseif ($this->prm->tipo == 1) {
            $modo = array(
                '1' => "MINISTRADO " . APP_EJERCICIO . " CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($this->prm->tipo == 2) {
            $modo = array(
                '1' => "MINISTRADO 2025 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($this->prm->tipo == 3) {
            $modo = array(
                '1' => "MINISTRADO 2024 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($this->prm->tipo == 4) {
            $modo = array(
                '1' => "MINISTRADO 2023 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($this->prm->tipo == 5) {
            $modo = array(
                '1' => "MINISTRADO 2022 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($this->prm->tipo == 6) {
            $modo = array(
                '1' => "MINISTRADO 2021 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($this->prm->tipo == 7) {
            $modo = array(
                '1' => "MINISTRADO 2020 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($this->prm->tipo == 8) {
            $modo = array(
                '1' => "MINISTRADO 2019 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } else {
            $modo = array(
                '1' => "MINISTRADO 2018 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        }
        $inputEjercicio->change = '
            comunica("' . $this->pantalla . '",{
                metodo:"cargaReporte",
                ejercicio:$(this).val()
            });
        ';

        $inputTipo = new stdClass();
        $inputTipo->opciones = $modo;
        $inputTipo->id = 'modo';
        $inputTipo->name = 'modo';
        $inputTipo->mensaje = 'modo';
        $inputTipo->enDiv = true;
        $inputTipo->label = 'REPORTE';

        $inputFechaMin = new StdClass();
        $inputFechaMin->id = 'fechaMin';
        $inputFechaMin->name = 'fechaMin';
        $inputFechaMin->placeholder = 'FECHA MÍNIMA';
        $inputFechaMin->enDiv = true;
        $inputFechaMin->label = 'FECHA MÍNIMA';
        $inputFechaMin->formato = 'fecha';

        $inputFechaMax = new StdClass();
        $inputFechaMax->id = 'fechaMax';
        $inputFechaMax->name = 'fechaMax';
        $inputFechaMax->placeholder = 'FECHA MÁXIMA';
        $inputFechaMax->enDiv = true;
        $inputFechaMax->label = 'FECHA MÁXIMA';
        $inputFechaMax->formato = 'fecha';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "buscar";
        $btnBuscar->name = "buscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->color = "gris";
        $btnBuscar->icon = "search me-2";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"pintaTabla",
                fechaMin:$("#fechaMin").val(),
                fechaMax:$("#fechaMax").val(),
                modo:$("#modo").val(),
                ejercicio:$("#ejercicio").val(),
                tipo:' . $this->prm->tipo . '
            })';
        if ($this->prm->tipo == 1) {
            $nombreTipo = "PENDIENTES DE CONCILIAR";
        } else {
            $nombreTipo = "CONCILIADAS";
        }

        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
            <div class="row mt-5">
                <legend class="col-md-12 col-xs-12">CONSULTA CLC ' . $nombreTipo . '</legend>
            </div>
            <div class="row">
                <form method="post" target="_blank" id="SearchExtracto" class="row">
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFechaMin) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponInput($inputFechaMax) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3">' . $this->ponCombo($inputEjercicio) . '</fieldset>
                        <fieldset class="col-md-3 col-xs-3" id="div_inputTipo">' . $this->ponCombo($inputTipo) . '</fieldset>
                </form>
            </div>
            <div class="row mt-2">
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="divTabla" class="col-md-12 col-xs-12">
            </div>
        ';
        $this->mixHtml($html);
    }
    private function pintaTabla()
    {
        $this->setContenedor('divTabla');
        $tabla = new stdClass();
        $tabla->id = 'tbl-Extracto';
        $tabla->titulo = 'Extracto Bancario';
        $tabla->comunica = new stdClass();
        $tabla->comunica->metodo = 'llenaTabla';
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->modo = $this->prm->modo;
        $tabla->comunica->tipo = $this->prm->tipo;
        $tabla->comunica->fechaMin = $this->prm->fechaMin;
        $tabla->comunica->fechaMax = $this->prm->fechaMax;
        $tabla->comunica->ejercicio = $this->prm->ejercicio;
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);
        $modo = $this->prm->modo;
        $tr = array();
        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CERE/CERECO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CE/CO';
        array_push($tr, $th);
        if ($modo > 3) {

            $th = new stdClass();
            $th->texto = 'AFECTACION';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'DOCUMENTO';
            array_push($tr, $th);
        }
        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA ABONO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO DE MINISTRACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO DE DOCUMENTO DE PAGO';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();
        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cere';
        array_push($trbody, $td);

        if ($modo > 3) {
            $td = new stdClass();
            $td->campoTxt = 'afe_id';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'bdo_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->campoTxt = 'importe';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cta_Cargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cta_Abono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_FechaGeneracion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tmi_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tdf_id';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTabla();
    }

    private function llenaTabla()
    {
        $params = [];
        $modo = isset($this->prm->modo) ? $this->prm->modo : "";
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        $fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : "";
        $fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : "";
        if ($fechaMin == "" || $fechaMax == "") {
            $this->mixScript('notify("INTRODUZCA UN RANGO DE FECHA PARA BÚSQUEDA ","error")');
            return;
        }
        $sql = "";
        $fechaMin = DateTime::createFromFormat('d/m/Y', $fechaMin);
        $fechaMin = $fechaMin->format('Y-m-d');
        $fechaMax = DateTime::createFromFormat('d/m/Y', $fechaMax);
        $fechaMax = $fechaMax->format('Y-m-d');
        $params = [':fmin' => $fechaMin,':fmax' => $fechaMax];
        if ($ejercicio == 1 && $tipo == 1 && $modo > 6) {
            $params[':ejer'] = APP_EJERCICIO_ANTERIOR;
        } else {
            $params[':ejer'] = APP_EJERCICIO;
        }
        if ($ejercicio == 0) { //EJERCICIO ACTUAL
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2026Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2026LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2026LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2026DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2026DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2026Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            }
        }
        if ($ejercicio == 1) { //2025
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2025Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2025LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2025LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2025DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2025DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2025Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 7:
                        $sql = consultaTesPendientesMin2025ClcSefip2025($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 8:
                        $sql = consultaTesPendientesMin2025LCFSefip2025($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 9:
                        $sql = consultaTesPendientesMin2025LCTSefip2025($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 10:
                        $sql = consultaTesPendientesMin2025DocLCSefip2025($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 11:
                        $sql = consultaTesPendientesMin2025PdpSefip2025($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            }
        }
        if ($ejercicio == 2) { //2024
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2024Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2024LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2024LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2024DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2024DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2024Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            }
        }
        if ($ejercicio == 3) { //2023
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2023Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2023LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2023LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2023DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2023DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2023Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            }
        }
        if ($ejercicio == 4) { //2022
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2022Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2022LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2022LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2022DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2022DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2022Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            } else {
            }
        }
        if ($ejercicio == 5) { //2021
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2021Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2021LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2021LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2021DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2021DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2021Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            } else {
            }
        }
        if ($ejercicio == 6) { //2020
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2020Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2020LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2020LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2020DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2020DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2020Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            } else {
            }
        }
        if ($ejercicio == 7) { //2019
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2019Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2019LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2019LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2019DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2019DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2019Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            } else {
            }
        }
        if ($ejercicio == 8) { //2018
            if ($tipo == 1) {
                switch ($modo) {
                    case 1:
                        $sql = consultaTesPendientesMin2018Clc($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 2:
                        $sql = consultaTesPendientesMin2018LCF($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 3:
                        $sql = consultaTesPendientesMin2018LCT($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 4:
                        $sql = consultaTesPendientesMin2018DocMun($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 5:
                        $sql = consultaTesPendientesMin2018DocLC($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                    case 6:
                        $sql = consultaTesPendientesMin2018Pdp($tipo, $fechaMin, $fechaMax, "Si");
                        break;
                }
            } else {
            }
        } else if ($ejercicio == 9) { //2017
            switch ($modo) {
                case 1:
                    $sql = consultaTesPendientesMin2017Clc($tipo, $fechaMin, $fechaMax, "Si");
                    break;
            }
        }

    
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();

        $nav->idTabla = "tbl-Extracto";
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
    }
    private function cargaReporte($detonar = true)
    {
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        if ($ejercicio == 0) {
            $modo = array(
                '1' => "MINISTRADO " . APP_EJERCICIO . " CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 1) {
            $modo = array(
                '1' => "MINISTRADO 2025 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
                '7' => "SEFIP 2025 MINISTRADO 2025 CLC",
                '8' => "SEFIP 2025 MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '9' => "SEFIP 2025 LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '10' => "SEFIP 2025 DOCUMENTOS - LINEA DE CAPTURA",
                '11' => "SEFIP 2025 DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 2) {
            $modo = array(
                '1' => "MINISTRADO 2024 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 3) {
            $modo = array(
                '1' => "MINISTRADO 2023 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 4) {
            $modo = array(
                '1' => "MINISTRADO 2022 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 5) {
            $modo = array(
                '1' => "MINISTRADO 2021 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 6) {
            $modo = array(
                '1' => "MINISTRADO 2020 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 7) {
            $modo = array(
                '1' => "MINISTRADO 2019 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } elseif ($ejercicio == 8) {
            $modo = array(
                '1' => "MINISTRADO 2018 CLC",
                '2' => "MINISTRADO CLC LINEA DE CAPTURA FEDERAL",
                '3' => "LINEAS DE CAPTURA PROCESADAS COMO TRANSFERENCIAS",
                '4' => "DOCUMENTO - MUNICIPIOS",
                '5' => "DOCUMENTOS - LINEA DE CAPTURA",
                '6' => "DOCUMENTOS - PDP",
            );
        } else if ($ejercicio == 9) {
            $modo = array('1' => "MINISTRADO 2017 CLC");
        } else if ($ejercicio == 10) {
            $modo = array('1' => "MINISTRADO 2016 CLC");
        }
        $inputTipo = new stdClass();
        $inputTipo->opciones = $modo;
        $inputTipo->id = 'modo';
        $inputTipo->detonar = $detonar;
        $inputTipo->name = 'modo';
        $inputTipo->mensaje = false;
        $inputTipo->enDiv = true;
        $inputTipo->label = 'REPORTE';
        $this->mixScript('
            $("#div_inputTipo").html(\'' . $this->ponCombo($inputTipo) . '\');
            $("#modo").selectpicker();
        ');
    }
}
if (isset($_SESSION[_S]['prm']['T']['1']['3']['1'])) {
    new ControladorConsultaMinistrado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
