<?php
session_start();
ini_set('max_execution_time', 3000);
set_time_limit(0);
//error_reporting(E_ALL);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/PHPExcel/IOFactory.php';
require_once dirname(__FILE__) . '/../../lib/bootstrap_spreadsheet.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class ControladorMarcadoMinGeneral extends WebResponse
{
    public $tem_carga;
    public $tipo_carga;
    public function __construct()
    {
        parent::__construct();
        switch ($this->metodo) {
            case "stepTransfer":
                $this->stepTransfer();
                break;
            case "copiarArchivo":
                $this->copiarArchivo();
                break;
            case "listaTemporal":
                $this->listaTemporal();
                break;
            case "listaTemporalDoc":
                $this->listaTemporalDoc();
                break;
            case 'llenaTabla':
                $this->llenaTabla();
                break;
            case 'llenaTablaDoc':
                $this->llenaTablaDoc();
                break;
            case "marcadoMinistrado":
                $this->marcadoMinistrado();
                break;
            case 'listaResultado':
                $this->listaResultado();
                break;
            case 'llenaTablaResultado';
                $this->llenaTablaResultado();
                break;
            case 'cancelar':
                $this->main();
                break;
            default:
                $this->listaResultado();
        }

        $this->returnJson();
    }
    public function main()
    {
        $Tipo = new stdClass();
        $Tipo->id = 'type_carga';
        $Tipo->opciones = array("MINISTRADOCLC" => "Clc´s", "MINISTRADODOC" => "Documento");
        $Tipo->label = 'Tipo';
        $Tipo->enDiv = false;
        $Tipo->require = true;
        $Tipo->selecciona = '';

        $boton = new stdClass();
        $boton->id = 'btnFormato';
        $boton->title = 'Descargar Formato para Carga Masiva';
        $boton->icon = 'file-excel';
        $boton->style = 'font-size: 15px;';
        $boton->color = 'verde';

        $this->agregaTools('<a href="archivos/formatos/FormatoMarcadoMinistrado.zip" target="_blank">' . $this->ponBoton($boton) . '</a>');
        $this->mixHtml('
            <ul class="list-group" id="listaCarga">
                    <h4>
                        <dd class="file-js">
                        <div class="box">
                            <form method="post" id="export_excel">
                            <div class = "row">
                            <div class = "col-md-3">
                            ' . $this->ponCombo($Tipo) . '
                            </div>
                            <div class = "col-md-3">
                                <input type="file" name="file-1" id="file-1" class="inputCargaMasiva inputCargaMasiva-1"/>
                                    <label for="file-1" style="font-size: 1rem !important; margin-top: 16px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17">
                                            <path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/>
                                        </svg><span>Seleccionar archivo&hellip;</span>
                                    </label>
                                    </div>
                                    </div>
                            </form>
                        </div>
                        <div id="name_xls"> </div>
                        <div id="loading_file" class="parent_loading">
                            <img  src="img/loading_xls.gif" class="child_loading" alt="loading" />
                        </div>
                        </dd>
                    </h4>

                <li class="list-group-item" id="botonesCarga"></li>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td class="warning" colspan="4">INDICACIONES</td></tr>
                        <tr>
                            <td>
                                1. Antes de realizar la carga masiva asegurese de descargar el formato del ejercicio actual. </br>
                                2. No utilizar formulas en el archivo  </br>
                                3. Respetar el nombre las hojas dentro del archivo que se proporciona </br>
                            </td>
                        </tr>
                        </tbody>
                </table>
            </ul>
        ');

        $this->mixScript('

            (function(e,t,n){var r=e.querySelectorAll("dd")[0];r.className=r.className.replace(/(^|\s)file-js(\s|$)/,"$1js$2")})(document,window,0);
                $("#loading_file").hide();
                $("#file-1").change(function(){
                    setTimeout(function(){
                        $("#export_excel").submit();
                    }, 300);
                });
                $(\'#export_excel\').on(\'submit\', function(event){
                    $("#loading_file").show();
                    event.preventDefault();
                    $.ajax({
                        url:"lib/_class_carga_masiva/analyzer.php",
                        method:"POST",
                        data:new FormData(this),
                        contentType:false, processData:false,
                        success:function(data){
                            var message = "";
                            var type = "error";
                            var parsed = JSON.parse(data);
                            if(parsed.success != undefined && parsed.success != ""){
                                type = "success";
                                message = parsed.success;
                                comunica("' . $this->pantalla . '",{metodo:"stepTransfer",file_tmp:parsed.xls,tem_last: parsed.tem_last});
                            }else if(parsed.errores != undefined && parsed.errores != ""){
                                message = parsed.errores;
                                if(parsed.txt != undefined){
                                }
                            } else {
                                message = "Ocurrio un error al retornar respuesta, intente de nuevo.";
                            }
                            $("#loading_file").hide();
                            notify(message,type);

                        }
                    });
                });
        ');
    }

    private function stepTransfer()
    {
        $file_name = explode(".", $this->prm->file_tmp);
        $this->prm->tipo = $file_name[1];

        $_SESSION[_S]['tem_carga'] = $file_name[0];
        $_SESSION[_S]['tem_usr_ip'] = ($_SERVER['REMOTE_ADDR'] == "::1" ? "127.0.0.1" : $_SERVER['REMOTE_ADDR']);
        $_SESSION[_S]['tem_carga_id'] = $this->prm->tem_last;

        $this->mixScript('
            $("#export_excel").hide();
            $(\'#name_xls\').html("<h4 style=\'color: #44658a;\'><label>Archivo: </label>  <b>' . $_SESSION[_S]['tem_carga'] . "." . $this->prm->tipo . '</b></h4>");
        ');

        $boton = new stdClass();
        $boton->id = 'btnSeleccionArchivo';
        $boton->title = 'Buscar Archivo';
        $boton->color = 'gris';
        $boton->value = 'copiar';
        $boton->icon = 'exchange me-2';
        $boton->click = '
            $(this).attr("disabled","disabled").css("background-color","#348fe2")
                .css("border-color","#858585")
                .append($("<img/>").attr("src","img/loader2.gif"));
            comunica("' . $this->pantalla . '",{metodo:"copiarArchivo",tipo:"' . $this->prm->tipo . '"});
            ';
        $this->setContenedor('botonesCarga');
        $this->mixHtml($this->ponBoton($boton));
        $this->mixScript('$(".datoCarga").remove();');

        $datetime1 = new DateTime('now');
        $this->actualizaCarga(7, 'CARGANDO LOS DATOS', $_SESSION['tipo']);
        $this->setterBitacora($datetime1, 4, "La Carga del Archivo tardo");
    }

    private function actualizaCarga($estado, $observacion, $tipoCarga = '')
    {
        $paramUpCarga = [];
        $tcr_id = $tipoCarga ? ',tcr_id = :tcr_id' : '';
        if ($tcr_id != '') {
            $paramUpCarga += [
                'tcr_id' => $tipoCarga,
            ];
        }
        $c = new AccesoDatos();
        $c->conectar();
        $sql = 'SET @usr_id = :usr_id , @usr_ip = INET_ATON(:usr_ip), @bit_observacion= :bit_observacion;';
        $paramSetcarga = [
            'usr_id' => $_SESSION[_S]['usr_id'],
            'usr_ip' => $_SESSION[_S]['tem_usr_ip'],
            'bit_observacion' => $observacion,
        ];
        $c->setSql($sql);
        $c->consultar($paramSetcarga);
        $sql = '
            UPDATE cat_CargaMasiva
            SET ecr_id = :ecr_id ' . $tcr_id . '
            WHERE
            gpo_id = :gpo_id AND ur_id = :ur_id
            AND tem_Carga = :tem_Carga
            ';
        $paramUpCarga += [
            'ecr_id' => $estado,
            'gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            'ur_id' => $_SESSION[_S]['clasAdm-ur'],
            'tem_Carga' => $_SESSION[_S]['tem_carga'],
        ];
        $c->setSql($sql);
        $c->consultar($paramUpCarga);
        $c->desconectar();
    }

    private function setterBitacora($datetime1, $estadoCarga, $observacion)
    {
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora($estadoCarga, $observacion . " " . $tiempo);
    }

    private function bitacora($edo, $observacion)
    {
        $c = new AccesoDatos();
        $c->conectar();
        $sql = '
            INSERT INTO ' . BD_BITACORA . '.bit_CargaMasiva
                SET bit_observacion= :bit_observacion,
                crm_id = :crm_id,
                tem_carga = :tem_carga,
                ecr_id = :ecr_id,
                tcr_id = :tcr_id,
                usr_id = :usr_id,
                usr_ip = INET_ATON(:usr_ip)
                ;
            ';
        $paramBitacora = [
            'bit_observacion' => $observacion,
            'crm_id' => $_SESSION[_S]["tem_carga_id"],
            'tem_carga' => $_SESSION[_S]["tem_carga"],
            'ecr_id' => $edo,
            'tcr_id' => $_SESSION['tipo'],
            'usr_id' => $_SESSION[_S]['usr_id'],
            'usr_ip' => $_SESSION[_S]['tem_usr_ip'],
        ];
        $c->setSql($sql);
        $c->consultar($paramBitacora);
        $c->desconectar();
    }

    private function copiarArchivo()
    {
        $this->actualizaCarga(10, "CARGA EN TEMPORAL");

        if ($this->prm->tipo == 'xls') {
            $ext = 'Xls';
        } elseif ($this->prm->tipo == 'xlsx') {
            $ext = 'Xlsx';
        }
        switch ($_SESSION['tipo']) {
            case 7:
                $directory = "marcadoministradoclc";
                break;
            case 8:
                $directory = "marcadoministradodoc";
                break;
            default:
                die('Tipo Incorrecto' . $_SESSION['tipo']);
                break;
        }

        $fichero = '';
        $base = '';
        $this->prm->lnk = $directory . '/' . date('m') . '/Carga_Masiva/' . $_SESSION[_S]['tem_carga'] . "." . $this->prm->tipo;

        if (isset($this->prm->lnk)) {
            $tmp = explode("/", $this->prm->lnk);
            $base = $tmp[0] . "/" . $tmp[1] . "/" . $tmp[2];
            $fichero = $tmp[3];
        }

        $r = new RutaArchivoSefip();
        $r->setBase("TES");
        $r->setPath($base);
        $r->setAnioFichero(APP_EJERCICIO);
        $r->setUrFichero($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur']);
        $routeXLS = $r->getRutaMod();
        $routeXLS .= $fichero;
        $contenido = file_get_contents($routeXLS);

        if ($contenido === false) {
            $this->mixScript('notify("No fue posible recuperar el archivo","warning");');
            return false;
        }

        $reader = IOFactory::createReader($ext);
        $spreadsheet = $reader->load($routeXLS);
        $sheetNames = $spreadsheet->getSheetNames();

        if ($sheetNames) {
            switch ($_SESSION['tipo']) {
                case 7:
                    $result = $this->insertMarcadoMinistradoClc($spreadsheet->getSheetByName('MINISTRADOCLC'));
                    $observacion = 'MARCADO POR CLC ';
                    break;
                case 8:
                    $result = $this->insertMarcadoMinistradoDoc($spreadsheet->getSheetByName('MINISTRADODOC'));
                    $observacion = 'MARCADO POR DOCUMENTO ';
                    break;
                default:
                    break;
            }
            if ($result) {
                $result;
                $observacion .= $this->mes($result);
                if ($_SESSION['tipo'] == 7) {
                    $this->mixScript('comunica("' . $this->pantalla . '",{metodo:"listaTemporal", Observacion: "' . $observacion . '"})');
                } else {
                    $this->mixScript('comunica("' . $this->pantalla . '",{metodo:"listaTemporalDoc", Observacion: "' . $observacion . '"})');
                }
            }
        }
    }

    public function insertMarcadoMinistradoClc($sheetNames)
    {
        $sql = 'INSERT INTO '.BD_TESORERITEM.'.`tem_MarcadoCarga` ( `ejercicio`, `ur`, `clc`, `tipo`,`spei`,`fecha`, `tem_carga`) VALUES ';
        $rowStart = 2;
        $values = '';
        $flag = false;
        while ($sheetNames->getCell('A' . $rowStart)->getValue() != '') {
            $ejercicio = $sheetNames->getCell('A' . $rowStart);
            $ur = $sheetNames->getCell('B' . $rowStart);
            $clc = $sheetNames->getCell('C' . $rowStart);
            $tipo = $sheetNames->getCell('D' . $rowStart);
            $spei = $sheetNames->getCell('E' . $rowStart);
            $fecha = $sheetNames->getCell('F' . $rowStart);
            $tem_carga = $_SESSION[_S]['tem_carga'];
            $values = '(
                ' . $ejercicio . ',
                ' . $ur . ',
                ' . $clc . ',' .
                "'$tipo'" . ',' .
                "'$spei'" . ',' .
                "'$fecha'" . ',' .
                "'$tem_carga'" . '
            ),';
            $sql .= $values;

            $rowStart++;
        }

        $sql = substr($sql, 0, -1);

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $flag = $fecha;
        $this->mixScript('notify("Se cargo de manera correcta la informacion a la tabla temporal","success");');

        return $flag;
    }

    public function insertMarcadoMinistradoDoc($sheetNames)
    {
        $sql = 'INSERT INTO '.BD_TESORERITEM.'.tem_MarcadoCarga( ejercicio, ur, clc, afectacion,documento,tipo,spei,fecha, tem_carga) VALUES ';
        $rowStart = 2;
        $values = '';
        $flag = false;
        while ($sheetNames->getCell('A' . $rowStart)->getValue() != '') {
            $ejercicio = $sheetNames->getCell('A' . $rowStart);
            $ur = $sheetNames->getCell('B' . $rowStart);
            $clc = $sheetNames->getCell('C' . $rowStart);
            $afectacion = $sheetNames->getCell('D' . $rowStart);
            $documento = $sheetNames->getCell('E' . $rowStart);
            $tipo = $sheetNames->getCell('F' . $rowStart);
            $spei = $sheetNames->getCell('G' . $rowStart);
            $fecha = $sheetNames->getCell('H' . $rowStart);
            $tem_carga = $_SESSION[_S]['tem_carga'];
            $values = '(
                ' . $ejercicio . ',
                ' . $ur . ',
                ' . $clc . ',
                ' . $afectacion . ',
                ' . $documento . ',' .
                "'$tipo'" . ',' .
                "'$spei'" . ',' .
                "'$fecha'" . ',' .
                "'$tem_carga'" . '
            ),';
            $sql .= $values;
            $rowStart++;
        }
        $sql = substr($sql, 0, -1);
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $flag = $fecha;
        $this->mixScript('notify("Se cargo de manera correcta la informacion a la tabla temporal","success");');

        return $flag;
    }

    public function listaTemporal()
    {
        $tablaConsulta = new stdClass();
        $tablaConsulta->id = 'tablaMin';
        $tablaConsulta->titulo = "REGISTROS " . $this->prm->Observacion;
        $tablaConsulta->comunica = new stdClass();
        $tablaConsulta->comunica->pantalla = $this->pantalla;
        $tablaConsulta->comunica->metodo = 'llenaTabla';
        $tablaConsulta->filasAmostrar = array(5, 10, 20, 50, 100, 120, 200, 250);
        $tablaConsulta->numFilas = 50;
        $tr = array();
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->aling = 'left';
        $th->width = '5%';
        $th->texto = 'CLC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'TIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '25%';
        $th->texto = 'SPEI';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'FECHA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '25%';
        $th->texto = 'TEM_CARGA';
        array_push($tr, $th);
        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ejercicio';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'clc';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tipo';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'spei';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'fecha';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tem_carga';
        array_push($trbody, $td);

        $tablaConsulta->encabezado = array($tr);
        $tablaConsulta->cuerpo = ($trbody);
        $resp = $this->validaDatos();
        $this->mixHtml('
            <div id="lista" class="modal fade"  data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title"> Id: ' . $_SESSION[_S]['tem_carga'] . '</h5>
                    <button type="button" id = "close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ' . $this->wrPonTabla($tablaConsulta) . '
                        ' . $resp . '
                    </div>
                    <div class="modal-footer">
                    <button type="button" id = "cancelar"class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnProcesa" disabled  class="btn btn-primary">Procesar</button>
                    </div>0
                </div>
                </div>
            </div>
        ');

        $this->mixScript("
        $( document ).ready(function() {
            $('#lista').modal('toggle');
        })
        $('#cancelar, #close').click(function(){
            comunica('" . $this->pantalla . "', {
                metodo: 'main'
            });
        });
        ");
        if (!$resp) {
            $this->mixScript('
            $("#btnProcesa").removeAttr("disabled");
            $("#btnProcesa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "marcadoMinistrado" , Observacion: "' . $this->prm->Observacion . '"
                });
            });

        ');
            $this->llenaTabla();
        }
    }

    public function listaResultado()
    {
        $tablaConsulta = new stdClass();
        $tablaConsulta->id = 'tablaResultado';
        $tablaConsulta->titulo = "RESULTADO DE LA CARGA";
        $tablaConsulta->comunica = new stdClass();
        $tablaConsulta->comunica->pantalla = $this->pantalla;
        $tablaConsulta->comunica->metodo = 'llenaTablaResultado';
        $tablaConsulta->filasAmostrar = array(5, 10, 20, 50, 100, 120, 200, 250);
        $tablaConsulta->numFilas = 50;
        $tr = array();
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->aling = 'left';
        $th->width = '5%';
        $th->texto = 'CLC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'TIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '20%';
        $th->texto = 'SPEI';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'FECHA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '15%';
        $th->texto = 'TEM_CARGA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '25%';
        $th->texto = 'OBSERVACION';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'ESTATUS';
        array_push($tr, $th);

        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'clc_ejercicio';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ur_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tdf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'bdm_referencia';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'bdm_fechaReferencia';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tem_carga';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'bdm_observacion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'bdm_procesado';
        array_push($trbody, $td);

        $tablaConsulta->encabezado = array($tr);
        $tablaConsulta->cuerpo = ($trbody);
        $id = $_SESSION[_S]['tem_carga'] ?? 0;
        $this->mixHtml('
            <div class="alert alert-success" role="alert">
                Carga masiva en progreso <a href="#" class="alert-link">ID : ' . $id . '</a>
            </div>
            <div>' . $this->wrPonTabla($tablaConsulta) . '</div>');
        $this->mixScript('
            $(".modal-backdrop").remove();
        ');
        $this->llenaTablaResultado();
    }
    public function listaTemporalDoc()
    {
        $tablaConsulta = new stdClass();
        $tablaConsulta->id = 'tablaMinDoc';
        $tablaConsulta->titulo = "REGISTROS " . $this->prm->Observacion;
        $tablaConsulta->comunica = new stdClass();
        $tablaConsulta->comunica->pantalla = $this->pantalla;
        $tablaConsulta->comunica->metodo = 'llenaTablaDoc';
        $tablaConsulta->filasAmostrar = array(5, 10, 20, 50, 100, 120, 200, 250);
        $tablaConsulta->numFilas = 50;
        $tr = array();
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'EJERCICIO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->aling = 'left';
        $th->width = '5%';
        $th->texto = 'CLC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'AFECTACION';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'DOCUMENTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'TIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '25%';
        $th->texto = 'SPEI';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'FECHA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '25%';
        $th->texto = 'TEM_CARGA';
        array_push($tr, $th);
        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ejercicio';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'clc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'afectacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'documento';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tipo';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'spei';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'fecha';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tem_carga';
        array_push($trbody, $td);

        $tablaConsulta->encabezado = array($tr);
        $tablaConsulta->cuerpo = ($trbody);
        $resp = $this->validaDatos();

        $this->mixHtml('
            <div id="lista" class="modal fade"  data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title">Id: ' . $_SESSION[_S]['tem_carga'] . '</h5>
                    <button type="button"  id = "close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                ' . $this->wrPonTabla($tablaConsulta) . '
                ' . $resp . '
                    </div>
                    <div class="modal-footer">
                    <button type="button" id = "cancelar" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnProcesa"  disabled class="btn btn-primary">Procesar</button>
                    </div>0
                </div>
                </div>
            </div>
        ');
        $this->mixScript("
            $( document ).ready(function() {
                $('#lista').modal('toggle')
            })
            $('#cancelar, #close').click(function(){
                comunica('" . $this->pantalla . "', {
                    metodo: 'cancelar'
                });
            });
        ");

        if (!$resp) {
            $this->mixScript('
                $("#btnProcesa").removeAttr("disabled");
                $("#btnProcesa").click(function(){
                    comunica("' . $this->pantalla . '", {
                        metodo: "marcadoMinistrado", Observacion: "' . $this->prm->Observacion . '"
                    });
                });

            ');
            $this->llenaTablaDoc();
        }
    }

    public function llenaTabla()
    {
        $id = $_SESSION[_S]['tem_carga'];
        $sql = "SELECT ejercicio,ur, clc,tipo,spei,fecha,tem_carga from ".BD_TESORERITEM.".tem_MarcadoCarga where tem_carga = ?";
        $params = [$id];
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaMin';
        $nav->conexion = 'sefip'.APP_EJERCICIO;
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    public function llenaTablaResultado()
    {
        $data = $this->validaProcesoCarga();
        if ($data) {
            $tabla = 'tem_MarcadoBdo';
            if ($data['tcr_id'] == 7) {
                $tabla = 'tem_MarcadoClc';
            }
            $id = $data['tem_carga'];
            $sql = "SELECT clc_ejercicio,ur_id, clc_id,tdf_id,bdm_referencia,bdm_fechaReferencia,tem_carga,bdm_observacion, 
            CASE WHEN bdm_procesado = 1 THEN  'Procesado'  WHEN bdm_procesado = 2 THEN  'En progreso' END as bdm_procesado
                    FROM ".BD_TESORERITEM.".". $tabla . " where tem_carga = ? ORDER by bdm_procesado DESC";
            $paramPc = [$id];
            $this->setSql($sql);
            $this->setParams($paramPc);
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
            $nav->idTabla = 'tablaResultado';
            $nav->conexion = 'sefip'.APP_EJERCICIO;
            $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
            $this->wrLlenaTabla($nav);
        } else {
            $this->mixScript('
            comunica("' . $this->pantalla . '",{metodo:"cancelar"});
      ');
        }
    }

    public function llenaTablaDoc()
    {
        $id = $_SESSION[_S]['tem_carga'];
        $sql = "SELECT ejercicio,ur, clc,tipo,spei,afectacion, documento,fecha,tem_carga from ".BD_TESORERITEM.".tem_MarcadoCarga where tem_carga = ?";
        $paramTDoc = [$id];
        $this->setSql($sql);
        $this->setParams($paramTDoc);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaMinDoc';
        $nav->conexion = 'sefip'.APP_EJERCICIO;
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    public function marcadoMinistrado()
    {
        $msg = 'Empezo el proceso de marcado, se le notificara via correo cuado haya finalizado';
        $this->procesaSegundoPlano();
        $this->mixScript('notify("' . $msg . '","success");');
        $this->mixScript('
            comunica("' . $this->pantalla . '",{metodo:"listaResultado"});
      ');
    }

    public function procesaSegundoPlano()
    {
        $path = __DIR__ . '/ProcesaMarcadoSegundoPlano.php';
        $tem = $_SESSION[_S]['tem_carga'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $tipo_carga = $_SESSION['tipo'];
        $observacion = $this->prm->Observacion;
        $usuario = $_SESSION[_S]['usr_id'];
        $comand = 'php ' . $path . ' ' . $tem . ' ' . $tipo_carga . ' "' . $observacion . '" ' . $ip . ' ' . $usuario . ' > /dev/null 2>&1 &';
        exec($comand);
        sleep(5);
        return true;
    }
    public function validaDatos()
    {
        $team = $_SESSION[_S]['tem_carga'];
        $sql = "
            SELECT CONCAT('La CLC : ', temp.clc, ' no es valida') as mensaje
            FROM
                ".BD_TESORERITEM.".tem_MarcadoCarga temp
            LEFT JOIN " . BD_CTRL . ".scp_Clc clc ON
                temp.clc = clc.clc_id
                and temp.ur = CONCAT(clc.gpo_id, clc.ur_id)
            WHERE temp.tem_carga = :tem_carga AND clc.clc_id IS NULL
            UNION
            SELECT mensaje
            FROM ( SELECT
                        CASE
                            WHEN count(x.ejercicio) = 1
                                    THEN NULL
                            ELSE CONCAT ('Solo se permite marcado del ejercicio actual')
                        END AS mensaje
                FROM
                    (
                    SELECT temp.ejercicio
                    FROM ".BD_TESORERITEM.".tem_MarcadoCarga temp
                    WHERE temp.tem_carga = :tem_carga
                    GROUP BY temp.ejercicio ) x) y
            WHERE
                y.mensaje IS NOT NULL
            UNION
            SELECT CONCAT('La CLC : ', temp.clc, ' no es valido para el  marcado->', clc.ecl_id) as mensaje
            FROM
                ".BD_TESORERITEM.".tem_MarcadoCarga temp
            LEFT JOIN " . BD_CTRL . ".scp_Clc clc ON
                temp.clc = clc.clc_id
                and temp.ur = CONCAT(clc.gpo_id, clc.ur_id)
            WHERE temp.tem_carga = :tem_carga AND clc.clc_id
            AND clc.ecl_id <> 16
       ";
       $paramVDatos = ['tem_carga' => $team];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($paramVDatos);
        $html = '';
        if ($this->getNumeroRegistros() > 1) {
            while ($res = $this->extraer()) {
                $html .= '
             <div class="alert alert-danger d-flex align-items-center" role="alert">
                    ' . $res['mensaje'] . '
             </div>';
            }
        }
        return $html;
    }

    private function getEstadoCarga($carga)
    {
        if ($carga) {
            $c = new AccesoDatos();
            $c->conectar();
            $sql = '
            SELECT ecr_id FROM cat_CargaMasiva
            WHERE   gpo_id = :gpo_id
                AND ur_id  = :ur_id
                AND tem_Carga = :tem_Carga
            ';
            $paramEcarga = [
                'gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
                'ur_id' => $_SESSION[_S]['clasAdm-ur'],
                'tem_Carga' => $carga,
            ];
            $c->setSql($sql);
            $c->consultar($paramEcarga);
            $data = $c->extraer();
            $c->desconectar();
            if ($c->getNumeroRegistros() == 0) {
                return false;
            } else {
                return $data['ecr_id'];
            }
        }
    }

    private function validaProcesoCarga()
    {
        $c = new AccesoDatos();
        $c->conectar();
        $sql = 'SELECT tem_carga, tcr_id, ecr_id FROM cat_CargaMasiva  WHERE  gpo_id = :gpo_id and ur_id = :ur_id and tcr_id in (7,8)  and ecr_id = 50 ORDER by crm_id DESC limit 1';
        $parampCarga = [
            'gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            'ur_id' => $_SESSION[_S]['clasAdm-ur'],
        ];
        $c->setSql($sql);
        $c->consultar($parampCarga);
        $data = $c->extraer();
        $c->desconectar();
        if ($c->getNumeroRegistros() == 0) {
            return false;
        } else {
            return $data;
        }
    }

    public function mes($date)
    {
        $anio = date('Y', strtotime($date));
        $mes = date('m', strtotime($date));
        switch ($mes) {
            case 1:
                $nombre = "ENERO";
                break;
            case 2:
                $nombre = "FEBRERO";
                break;
            case 3:
                $nombre = "MARZO";
                break;
            case 4:
                $nombre = "ABRIL";
                break;
            case 5:
                $nombre = "MAYO";
                break;
            case 6:
                $nombre = "JUNIO";
                break;
            case 7:
                $nombre = "JULIO";
                break;
            case 8:
                $nombre = "AGOSTO";
                break;
            case 9:
                $nombre = "SEPTIEMBRE";
                break;
            case 10:
                $nombre = "OCTUBRE";
                break;
            case 11:
                $nombre = "NOVIEMBRE";
                break;
            case 12:
                $nombre = "DICIEMBRE";
                break;
            default:
                $nombre = "Mes inválido";
        }
        return $nombre . ' ' . $anio;
    }
}
if ($_SESSION[_S]['prm']['T']['4']['6']) {
    new ControladorMarcadoMinGeneral();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
