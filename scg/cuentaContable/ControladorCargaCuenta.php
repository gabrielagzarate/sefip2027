<?php
/*
Controlar los catálogos de la CONAC, que arman la cuenta de mayor de una cuenta contable:
GENERO + GRUPO + RUBRO + CUENTA + SUBCUENTA
 */

//inicio de sesion y llamada a las librerias
declare (strict_types = 1);

session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/bootstrap_spreadsheet.php';

class ControladorCargaCuenta extends WebResponse
{
    public $cargaMasiva;
    //Constructor
    public function __construct()
    {
        // Determinamos si el usuario actual tiene permisos de cargaMasiva en la base de datos
        $this->cargaMasiva = isset($_SESSION[_S]['prm']['C']['1']['4']['23'])
        ? 1 : 0;

        //mandar a llamar constructor base
        parent::__construct();

        if (isset($this->prm->metodo)) {
            $this->metodo = $this->prm->metodo;
        } else {
            if (isset($_POST["action"])) {
                $this->metodo = "cargaArchivo";
            }
        }

        switch ($this->metodo) {

            case "dibujaPantalla":
                $this->dibujaPantalla();
                break;
            case "cargaArchivo":
                $this->cargaArchivo();
                break;
            case "resultadoCarga":
                $this->resultadoCarga();
                break;
            case "revisar":
                $this->revisar();
                break;
            case "cargar":
                $this->cargar();
                break;
            default:
                $this->PonBarraBotones();
                break;
        }

        $this->returnJson();
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function PonBarraBotones()
    {

        $botones = '
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <a id="descargar" target="_blank" href="scg/cuentaContable/formato/formato.rar" data-placement="bottom" title="Descargar archivo base para carga masiva en Excel" role="button" type="button" class="btn btn-outline-success me-1"><i class="fa-solid fa-download me-2"></i>Descargar Formato</a>
                    <a id="csvReporteGrid" data-placement="bottom" title="Exporta el resultado de la carga masiva" role="button" type="button" class="btn btn-outline-primary me-1"><i class="fa-solid fa-share-from-square me-2"></i>Exportar Resultado</a>
                </div>
                <div class="col-md-2 col-xs-1" id="descargarTXT" style="display:none" title="Error de la carga masiva" tabindex="20" ></div>
            </div>
            <span style="height:6px; display:block"></span>' .
            (($this->cargaMasiva) ?
            '<div class="row">
                <form id="data" enctype="multipart/form-data">
                    <div class="d-flex justify-content-start">
                        <div class="col-md-5" style="display:inline">
                            <input id="action" name="action" type="hidden" value=" " />
                            <input type="hidden" name="type_carga" id="type_carga" value="CUENTAS" />
                            <input id="file-1" name="file-1" type="file" class="btn  btn-test" title="Buscar Archivo">
                        </div>
                        <div class="col-auto">
                            <button id="copiar" type="submit" data-placement="bottom" title="Comenzar el proceso de Carga Masiva" class="btn btn-outline-warning btn-test ladda-button" data-style="slide-left" cat="" tabindex="20"><i class="fa-solid fa-upload me-2"></i>Copiar</button>
                        </div>
                        <div class="col-auto">
                            <button id="revisar" type="submit" style="display:none" data-placement="bottom" title="Revisar que las cuentas sean válidas" class="btn btn-outline-success me-1" data-style="slide-left" cat="" tabindex="20"><i class="fa-solid fa-check me-2"></i>Revisar</button>
                        </div>
                        <div class="col-auto">
                            <button id="cargar" type="submit" style="display:none" data-placement="bottom" title="Incorporar a la contabilidad las cuentas válidas" class="btn btn-outline-success btn-test ladda-button" data-style="slide-left" cat="" tabindex="20"><i class="fa-solid fa-upload me-2"></i>Cargar</button>
                        </div>
                    </div>
                </form>
                <div id="neon" class="d-flex justify-content-end"><h1>Se cargarán en la UR ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '</h1>
                </div>
            </div>
        </div>
        <span style="height:32px; display:block"></span>
            ' : '<h4>No tienes los permisos necesarios para realizar cargas masivas de cuentas contables</h4>') .
        '
        ';

        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        $this->mixScript('
            $("nav").hide();
            $(".modulos").hide();'
            . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
            $("#sticky-anchor").show();
            $("#sticky").show();
            $("#mainContainer").css("margin-top", "");

            if ( $( ".container" ).css( "max-width" ) == "1170px" ) {
                $( ".container" ).css( "max-width", "100%" );
            }
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );

            // Exportamos el resultado de la carga a un CSV
            $("#csvReporteGrid").click( function(e) {
                downloadCSV({ objetoAExportar: dataCarga, filename: "cargaMasivaCuentas.xls" });
            });
            ' .
            (($this->cargaMasiva) ?
                '
                // PENDIENTE: Esto no funciona en CHROME
                $("form#data").submit(function(e){
                    setLoading();
                    var formData = new FormData($(this)[0]);
                    $.ajax({
                        url: "lib/_class_carga_masiva/analyzer.php",
                        method: "POST",
                        data: formData,
                        contentType: false, processData: false,
                        success: function (data) {
                            var message = "";
                            var type = "error";
                            var parsed = JSON.parse(data);
                            console.log(parsed);
                            if (parsed.success != undefined && parsed.success!="") {
                                type = "success";
                                message = parsed.success;
                                var nombrearchivo=parsed.xls;
                                var temcarga  = nombrearchivo.split(".");
                                console.log(temcarga[0]);
                                // Avisamos que vamos a empezar la carga ...
                                $("#action").attr( "value", temcarga[0] );
                                var formElement = document.getElementById("data");
                                formData = new FormData(formElement);
                                // Avisamos que vamos a empezar la carga ...
                                dataCarga = [];
                                dataCarga.push( {
                                    "a": "COPIANDO",
                                    "i"  : "Subiendo el archivo para su revisión.   ID de Carga Temporal " + $("#action").attr("value")
                                } );
                                
                                var nuevaFila = $("<tr>")
                                    .append($("<td>").text("COPIANDO").css("color", "green"))
                                    .append($("<td>").text("Subiendo el archivo para su revisión.   ID de Carga Temporal " + $("#action").attr("value")).css("color", "green"));
                                $("#tabla-cargaCuentasContables").find("tbody").append(nuevaFila);

                                $.ajax({
                                    url: "scg/cuentaContable/ControladorCargaCuenta.php",
                                    type: "post",
                                    data: formData,
                                    async: false,
                                    success: function (data, status, object) {
                                        try { 
                                            window.espera.stop(); 
                                        } catch(err) { 
                                            /* nada */ 
                                        };
                                        console.table( data );
                                        // Checamos si regreso lo "normal", que es cuando el proceso pasó ...
                                        if ( data.indexOf("validacion") != -1 ) {
                                            // Revisamos en la base de datos el resultado de la carga
                                            comunica("scg/cuentaContable/ControladorCargaCuenta.php",{"metodo":"resultadoCarga", "tc" : $("#action").attr("value")});
                                        } else {
                                            dataCarga.push( {
                                                "a": "ERROR",
                                                "i"  : "No pudo completarse el proceso de carga"
                                            } );
                                                                                     
                                            var nuevaFila = $("<tr>")
                                                .append($("<td>").text("ERROR").css("color", "red"))
                                                .append($("<td>").text("No pudo completarse el proceso de carga").css("color", "red"));
                                            $("#tabla-cargaCuentasContables").find("tbody").append(nuevaFila);
                                            // Si es por duplicadas ... mostramos un mensaje corto y no todo el INSERT que falló
                                            if ( data.indexOf("Duplicate entry") != -1 ) {
                                                alertify.alert("ERROR AL CARGAR ARCHIVO", "Al menos una cuenta en la carga masiva ya existe en el archivo Temporal<br />Revisa que tu archivo no tenga cuentas duplicadas").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                                            } else {
                                                 alertify.alert("ERROR AL CARGAR ARCHIVO", data).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                                            }
                                            $("#copiar").attr("style", "visibility: visible");
                                            $("#revisar").attr("style", "display: none");
                                        }

                                    },
                                    cache: false,
                                    contentType: false,
                                    processData: false
                                });
                            } else if (parsed.errores != undefined) {
                                message = parsed.errores;
                                alertify.confirm("ERROR AL CARGAR ARCHIVO",
                                    function(){
                                        $("#descargarTXT").html("<a class=\'btn btn-xs btn-danger\' id=\'download_file\'  href=\'archivos/failedCargaMasiva/cuentascontable/' . date('m') . '/' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '/"+parsed.txt+".txt\' download>Descargar TXT de Error</a>");
                                        if (parsed.txt != undefined){
                                            $("#descargarTXT").show();
                                        }
                                    }
                                ).set("labels",{ok:"Aceptar",cancel:"Cancelar Operación"});
                                
                                $("#copiar").attr("style", "visibility: visible");
                                $("#revisar").attr("style", "display: none");
                            } else {
                                message = "Ocurrio un error al retornar respuesta, intente de nuevo.";
                            }
                        }
                    });
                    return false;
                });

                $("#download_file").click(function(){
                    var now = new Date().toString();
                    this.href = "data:text/plain;charset=UTF-8,"  + encodeURIComponent(now);
                });
                // ------------------------------------------------
                // Revisar que la información cargada en la póliza temporal sea correcta, antes de cargarla a producción
                $("#revisar").click( function(e) {
                    e.preventDefault();
                    window.espera = Ladda.create(this);   window.espera.start();
                    comunica( "scg/cuentaContable/ControladorCargaCuenta.php", {
                        "metodo" : "revisar",
                        "tc" : $("#action").attr("value")
                    });
                    e.stopPropagation();
                    return false;
                });

                // Si ya pasó la revisión ... cargamos a la contabilidad
                $("#cargar").click( function(e) {
                    e.preventDefault();
                    window.espera = Ladda.create(this);   window.espera.start();
                    comunica( "scg/cuentaContable/ControladorCargaCuenta.php", {
                        "metodo" : "cargar",
                        "tc" : $("#action").attr("value")
                    });
                    e.stopPropagation();
                    return false;
                });

                $("#file-1").change(function(e){
                    $("#action").attr( "value", "" );

                    $("#copiar").attr("style", "visibility: visible");
                    $("#revisar").attr("style", "display: none");
                    $("#cargar").attr("style", "display: none");
                    $("#descargarTXT").attr("style", "display: none");
                    $("#descargarTXT").attr("href", "");

                    try { window.espera.stop(); } catch(err) { /* nada */ };
                    
                    $("#tabla-cargaCuentasContables tbody tr").remove();
                });' : ''
            ) . '
        ');
        $this->dibujaPantalla();
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dibujaPantalla()
    {

        $this->setContenedor("contenedor");

        $this->mixScript('$("#tituloPantalla").hide();');

        // Si no tiene permisos de cargaMasiva para cargas masivas, no dibujamos el grid
        if (!$this->cargaMasiva) {
            return false;
        }

        $tabla = new stdClass();
        $tabla->id = 'cargaCuentasContables';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tr = array();
        $th = new stdClass();
        $th->texto = 'ACCIÓN';
        $th->width = '200px';
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'INFORMACIÓN';
        $th->align = "center";
        array_push($tr, $th);

        $trbody = array();

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->mixScript('  $(".input-group-addon").css("padding","2px 12px"); $(".tabla-footer").remove();');
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Copiamos el archivo al servidor + cargamos su información en la tabla temporal
// NOTA: COMO NO PUDE HACER QUE ESTA FUNCION SE COMUNIQUE CON EL AJAX QUE LO LLAMA, SE ME OCURRIO LA BRILLANTE IDEA DE
// GRABAR EN tem_PlanCuentas LOS MENSAJES, SIMPLEMENTE GRABANDO EN EL CAMPO tem_error el valor 9 y la cuenta fija
// 11131.XXX.YYY.999.9999 ... así al regresar de ésta función la que la llama buscará estos datos
// Obviamente para insertar debe decir     WHERE tem_error <> 9
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function cargaArchivo()
    {

        // Obtenemos el número de carga temporal asignado...
        $tem_carga = $this->prm->action;
        // Checamos que sea texto plano ...

        if ($_FILES["file-1"]["type"] != "application/vnd.ms-excel") {

            // Mensaje de error "patito"
            $conexion = new AccesoDatos();
            $conexion->conectar();

            $conexion->setSql("INSERT INTO ".BD_TEMPORAL.".tem_PlanCuentas(
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id, cta_nombre, tem_error, tem_carga, usr_id )
                VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );");
            $p1 = [1,1,1,3,1,$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],'001','999','999','EL TIPO DE ARCHIVO NO ES VÁLIDO',9,$tem_carga,$_SESSION[_S]['usr_id']];
            $conexion->consultar($p1);
            $conexion->desconectar();
            return false;
        }

        $filename = $_FILES["file-1"]["tmp_name"];

        $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
        $Polizas = $objPHPExcel->getSheetByName("CUENTAS");
        $ultimaColumn = "E";
        $ultimafila = $Polizas->getHighestRow();
        $numeroColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ultimaColumn);

        $datosCuentas = array();
        $indiceData = 0;

        for ($fila = 4; $fila <= $ultimafila; $fila++) {
            if ($Polizas->getCell('A' . $fila)->getValue() != '') {
                $columna = "A";
                for ($j = 0; $j < $numeroColumna; $j++) {
                    $datosCuentas[$indiceData][$j] = $Polizas->getCell($columna . $fila)->getValue();
                    $columna++;
                }
            }
            $indiceData++;
        } // end for

        if (count($datosCuentas) == 0) {
            // Mensaje de error "patito"
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql("INSERT INTO ".BD_TEMPORAL.".tem_PlanCuentas(
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id, cta_nombre, tem_error, tem_carga, usr_id )
                VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
            $p2 = [1,1,1,3,1, $_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],'001','999','9999','NO FUÉ POSIBLE LEER EL ARCHIVO',9,$tem_carga,$_SESSION[_S]['usr_id']];
            $conexion->consultar($p2);
            $conexion->desconectar();
            return false;
        }

        $sqlC = ''; //contiene los registros a insertarse en tem_PlanCuentas
        $contadorRegistroC = 0; //contiene el numero de registros que $sqlC tiene registrados
        $maxRegistrosPorCarga = 2000; // Registros que intentará insertar cada que ejecute INSERT ...
        $reiniC = true;

        // Obtehemos el número de registros
        $registros = $datosCuentas;
        $nVeces = sizeof($registros);
        $nTotalRegistros = sizeof($registros);
        $nCuentasCargadas = 0;

        for ($i = 0; $i < $nVeces; $i++) {
            $data[$i] = $registros[$i];
            // Sólo consideramos las líneas que traigan cuenta y nombre
            if (isset($data[$i][0]) && isset($data[$i][3])) {
                $mayor = preg_replace(sprintf('~[^%s]++~i',
                    '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ', $data[$i][0]);
                $mayor = preg_replace('/\s\s+/', ' ', $mayor);

                $maestro = preg_replace(sprintf('~[^%s]++~i',
                    '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ', $data[$i][1]);
                $maestro = preg_replace('/\s\s+/', ' ', $maestro);

                $registro = preg_replace(sprintf('~[^%s]++~i',
                    '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ', $data[$i][2]);
                $registro = preg_replace('/\s\s+/', ' ', $registro);

                if (mb_detect_encoding($data[$i][3], "auto") == "ISO-8859-1") {
                    $nombre = $data[$i][3];
                    $nombre = preg_replace('/\s\s+/', ' ', $nombre);
                } else {
                    $nombre = preg_replace(sprintf('~[^%s]++~i',
                        '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ',
                        utf8Decode($data[$i][3]));
                    $nombre = preg_replace('/\s\s+/', ' ', $nombre);

                }
                $nombre = utf8Decode($nombre);
                $ue = preg_replace(sprintf('~[^%s]++~i',
                    '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ', $data[$i][4]);
                $ue = preg_replace('/\s\s+/', ' ', $ue);

                // Excel ... si incluye comas el nombre ... lo encierra entre comillas ... asi que aqui checamos si tiene comillas al
                // principio y final de la cadena, se lo quitamos ...
                $nombre = trim($nombre);
                if (substr($nombre, 0, 1) == '"' && substr($nombre, -1) == '"') {
                    $nombre = substr($nombre, 1); // Le quitamos la primera
                    $nombre = substr($nombre, 0, strlen($nombre) - 1); // Le quitamos la última
                }
                // El tamaño máximo en la base de datos sinpres para el nombre, son 200 caracteres
                $nombre = str_replace('"', '', $nombre);
                $nombre = str_replace(',', '', $nombre);
                $nombre = str_replace("'", "", $nombre);
                $nombre = substr($nombre, 0, 200);

                // Validaciones básicas por nivel
                $mayor = trim($mayor);
                $maestro = trim($maestro);
                $registro = trim($registro);
                $nombre = strtoupper(trim($nombre));
                $ue = trim($ue);

                $bolProcesa = true;

                if (strlen($mayor) <= 8 && strlen($mayor) > 12) {
                    $bolProcesa = false;
                }
                if (strlen($maestro) != 3 && strlen($maestro) != 5) {
                    $bolProcesa = false;
                }
                if (strlen($registro) != 4 && strlen($registro) != 6) {
                    $bolProcesa = false;
                }
                if (strlen($ue) != 3) {
                    $bolProcesa = false;
                }

                if ($bolProcesa) {
                    // Validamos si la cuenta tiene una longitud válida
                    $cuenta = $maestro . $registro;
                    $nAncho = strlen($cuenta);
                    // Cuentas válidas
                    // 1.2.3.4.5    111 1111             16
                    // 1.2.31.4.5   111 1111             17
                    // 1.2.31.41.5  111 1111             18
                    // 1.2.31.41.11 111 1111             19
                    // 1.2.3.4.5    111 11 1111            18
                    // 1.2.31.4.5   111 11 1111            19
                    // 1.2.31.41.5  111 11 1111            20
                    // 1.2.31.41.11 111 11 1111         21
                    // La línea de encabezado no la pelamos :)
                    if (trim(strtoupper($mayor)) !== "MAYOR") {
                        $arrCta = explode(".", $mayor);
                        // Para efectos de validación, obtenemos también cuántos elementos tra
                        $elementos = count($arrCta);
                        if ($elementos == 5) {
                            $ger_id = $arrCta[0];
                            $cgp_id = $arrCta[1];
                            $rub_id = $arrCta[2];
                            $cgcu_id = $arrCta[3];
                            $cgsc_id = $arrCta[4];
                            // Solo las cuentas que tengan un ancho válido y que traigan nombre
                            if (($nAncho == 7 || $nAncho == 9) && strlen($nombre)
                                != 0 && is_numeric($ger_id) && is_numeric($cgp_id)
                                && is_numeric($rub_id) && is_numeric($cgcu_id)
                                && is_numeric($cgsc_id)) {
                                // Marcos, 05/feb/2016
                                // Israel pide quitar la UR del archivo de carga masiva, y define que será
                                // la UR de la clasificación administrativa la que se usará para grabar el registro
                                $cta_gpo_id = $_SESSION[_S]['clasAdm-gpo'];
                                $cta_ur_id = $_SESSION[_S]['clasAdm-ur'];
                                $cta_ue_id = trim($ue);
                                // Si sólo tienen el cuarto nivel como agrupamiento ...
                                if (strlen($maestro) == 3) {
                                    $cgc_id = $maestro;
                                    $cta_id = $registro;
                                } else {
                                    $cgc_id = substr($maestro, 0, 3);
                                    $cta_id = substr($maestro, 3, 2) . $registro; // Si tuviera cinco niveles se "junta" en éste campo, así que aquí va el "resto" de la cuenta
                                }
                                // Sólo insertamos aquellas cuentas que pertenezcan a la UR actual ... por si están tratando de cargar de otra UR
                                // Marcos: 05/feb/2016
                                // Israel pide quitar la UR, así que ya no validamos ésto (ver comentario líneas arriba)
                                $sqlCuentas = "INSERT INTO ".BD_TEMPORAL.".tem_PlanCuentas(
                                    ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,
                                    cta_gpo_id, cta_ur_id, cta_ue_id,
                                    cgc_id, cta_id,
                                    cta_nombre, tem_error, tem_carga, usr_id
                                    )VALUES
                                ";

                                $sqlC .= (!$reiniC) ? ', ' : '';
                                $sqlC .= "( $ger_id, $cgp_id, $rub_id, $cgcu_id, $cgsc_id, $cta_gpo_id, $cta_ur_id, $cta_ue_id, $cgc_id, '$cta_id', '$nombre', 0, '$tem_carga', " . $_SESSION[_S]['usr_id'] . " )";
                                $reiniC = false;
                                $contadorRegistroC++;
                                $nCuentasCargadas++;
                            }
                        }
                    }
                } // fin $bolProcesa
            }
            //para realizar un insert cuando llegue al máximo de registros que diga la variable $maxRegistrosPorCarga ( 2,000 originalmente )
            // o cuando se acaben los registros a insertar si es menor a esa cantidad

            if (($contadorRegistroC > $maxRegistrosPorCarga) || (($i + 1) == $nTotalRegistros)) {
                // Siempre y cuando haya algo por cargar ...
                if ($contadorRegistroC > 0) {
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql($sqlCuentas . $sqlC);
                    $conexion->consultar();
                    $conexion->desconectar();
                    $sqlC = '';
                    $contadorRegistroC = 0;
                    $reiniC = true;
                }
            }
        }
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Checamos en la tabla tem_PlanCuentas que se hayan cargado cuentas realmente ... y buscamos mensajes de error "patito"
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function resultadoCarga()
    {

        // Obtenemos el número de carga temporal asignado...
        $tem_carga = $this->prm->tc;
        // Revisamos que la cuenta exista a nivel de mayor
        // ------------------------------------------------------------------------------------------------------------------------
        $sql = 'SELECT cta_nombre AS error, "---> ERROR DE CARGA" AS cuenta FROM '.BD_TEMPORAL.'.tem_PlanCuentas WHERE tem_carga = ? AND tem_error = ?;';
        $p4= [$tem_carga,9];
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($p4);
        $this->desconectar();

        $numErrores = $this->getNumeroRegistros();

        // Si hubo errores ... avisamos ...
        if ($numErrores > 0) {
            while ($row = $this->extraer()) {
                $this->mixScript('dataCarga.push( { "a": "' . $row["cuenta"] . '", "i": "' . $row["error"] . '" } );
                                    var nuevaFila = $("<tr>")
                                        .append($("<td>").text("' . $row["cuenta"] . '").css("color", "red"))
                                        .append($("<td>").text("' . $row["error"] . '").css("color", "red"));
                                    $("#tabla-cargaCuentasContables").find("tbody").append(nuevaFila);
                ');
            }
            $this->mixScript('
                $("#copiar").attr("style", "visibility: visible");
                $("#revisar").attr("style", "display: none");
                try { window.espera.stop(); } catch(err) { /* nada */ };
            ');
            // Si NO hubo errores, checamos que realmente exista algo para cargar ...
        } else {
            // Revisamos que realmente existan cuentas listas para la revisión
            // ------------------------------------------------------------------------------------------------------------------------
            $sql = 'SELECT COUNT(*) AS total FROM '.BD_TEMPORAL.'.tem_PlanCuentas WHERE cta_gpo_id = ? AND cta_ur_id = ? AND tem_carga = ? AND tem_error <> ?;';
            $p5 = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$tem_carga,9];
            $this->setSql($sql);
            $this->conectar();
            $this->consultar($p5);
            $this->desconectar();
            $row = $this->extraer();

            // Si el proceso dejó al menos una cuenta lista para revisar ...
            if ($row['total'] > 0) {
                $this->mixScript('
                    dataCarga.push( {
                        "a": "COPIADO !!!",
                        "i"  : "Listo para su revisión"
                    } );
                    var nuevaFila = $("<tr>")
                        .append($("<td>").text("COPIADO!!!").css("color", "green"))
                        .append($("<td>").text("Listo para su revisión").css("color", "green"));
                    $("#tabla-cargaCuentasContables").find("tbody").append(nuevaFila);
                    $("#revisar").attr("style", "visibility: visible");
                    $("#copiar").attr("style", "display: none");
                    try { window.espera.stop(); } catch(err) { /* nada */ };
                ');
                // Si no ... algo pasó ...
            } else {
                $this->mixScript('
                    dataCarga.push( {
                        "a": "ERROR",
                        "i"  : "NINGUNA cuenta se procesó ... revisa tu archivo O REVISA QUE LA UR ACTUAL SEA LA CORRECTA."
                    } );
                    var nuevaFila = $("<tr>")
                        .append($("<td>").text("ERROR").css("color", "red"))
                        .append($("<td>").text("NINGUNA cuenta se procesó ... revisa tu archivo O REVISA QUE LA UR ACTUAL SEA LA CORRECTA.").css("color", "red"));
                    $("#tabla-cargaCuentasContables").find("tbody").append(nuevaFila);
                    $("#copiar").attr("style", "visibility: visible");
                    $("#revisar").attr("style", "display: none");
                    try { window.espera.stop(); } catch(err) { /* nada */ };
                ');
            }
        }
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Revisamos las cuentas cargadas en la tabla temporal ...
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function revisar()
    {

        // Obtenemos el número de carga temporal asignado...
        $tem_carga = $this->prm->tc;

        // Revisamos que la cuenta exista a nivel de mayor
        $sql1 = 'SELECT CONCAT( "La cuenta ", CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id ), " no existe a nivel de mayor" ) AS error,
            CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id ) AS cuenta
            FROM '.BD_TEMPORAL.'.tem_PlanCuentas t
            LEFT JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            WHERE t.cta_gpo_id = :gpo AND t.cta_ur_id = :ur AND t.tem_carga = :carga AND t.tem_error <> :error AND m.cgsc_id IS NULL ';

        // Marcos: 08/feb/2016
        // Este año no pediremos UR en la carga, se tomará la de la clasificación administrativa
        // por definición de Israel

        // Que la UE de la cuenta contable sea una UE válida para la UR actual
        $sql2 = 'SELECT CONCAT( "La UE ", t.cta_ue_id, " no existe para la UR Actual" ) AS error,
            CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id ) AS cuenta
            FROM '.BD_TEMPORAL.'.tem_PlanCuentas t
            LEFT JOIN cat_UnidadEjecutora e ON ( t.cta_gpo_id = e.gpo_id AND t.cta_ur_id = e.ur_id AND t.cta_ue_id = e.ue_id )
            WHERE t.cta_gpo_id = :gpo AND t.cta_ur_id = :ur AND t.tem_carga = ":carga" AND t.tem_error <> :error AND e.ue_id IS NULL';

        // Revisamos que los cuartos niveles estén autorizados para la cuenta de mayor
        $sql3 = 'SELECT CONCAT( "No existe el cuarto nivel ", t.cgc_id, " en la cuenta ", CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id ) ) AS error,
            CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id ) AS cuenta
            FROM '.BD_TEMPORAL.'.tem_PlanCuentas t
            LEFT JOIN cg_CuentaCuarto c USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id )
            WHERE t.cta_gpo_id = :gpo AND t.cta_ur_id = :ur AND t.tem_carga = ":carga" AND t.tem_error <> :error AND c.cgsc_id IS NULL';

        // Revisamos que los quintos niveles ( para las cuentas de estructura 3 ) estén autorizados para la cuenta de mayor
        $sql4 = 'SELECT CONCAT( "No existe el quinto nivel ", cgq_id, " en la cuenta ", CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id ) ) AS error,
            CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cgq_id, cta_id ) AS cuenta
            FROM (
            SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, SUBSTRING( cta_id, 1, 2 ) AS cgq_id, SUBSTRING( cta_id, 3, 4 ) AS cta_id, cta_nombre
            FROM '.BD_TEMPORAL.'.tem_PlanCuentas t
            WHERE t.cta_gpo_id = :gpo  AND t.cta_ur_id = :ur AND t.tem_carga = ":carga" AND tem_error <> :error AND LENGTH( cta_id ) = 6
            )t
            LEFT JOIN cg_CuentaQuinto q USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cgq_id )
            WHERE q.cgq_id IS NULL';

        // Verificamos que el nivel de registro sea válido ( 4 y 6 debe medir para las cuentas de estructura 1 y 3 *** NO PERMITIMOS INSERTAR MASIVAMENTE CUENTAS PRESUPUESTALES *** )
        $sql5 = 'SELECT CONCAT( "El nivel de registro ", cta_id, " no tiene una longitud válidas" ) AS error,
            CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id ) AS cuenta
            FROM '.BD_TEMPORAL.'.tem_PlanCuentas t
            WHERE t.cta_gpo_id = :gpo AND t.cta_ur_id = :ur AND t.tem_carga = ":carga" AND t.tem_error <> :error AND LENGTH( cta_id ) != 6 AND LENGTH( cta_id ) != 4';

        $sql6 = ' SELECT CONCAT( "El nivel de registro ", cta_id, " no tiene una longitud válida" ) AS error,
            CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id ) AS cuenta
            FROM '.BD_TEMPORAL.'.tem_PlanCuentas t
            LEFT JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            WHERE t.cta_gpo_id = :gpo AND t.cta_ur_id = :ur
                AND t.tem_carga = ":carga" AND t.tem_error <> :error AND
                LENGTH( cta_id ) = 6 AND !(  t.cta_gpo_id = 1 AND t.cta_ur_id = 16  AND  ecu_id =3 )';

        if ($_SESSION[_S]['nvj_id'] == 1) {
            $sql7 = " UNION ALL " . 'SELECT CONCAT( "La UE ", t.cta_ue_id, " de la carga masiva no corresponde a la UE del usuario ", "' . $_SESSION[_S]['usu'] . ' " ) AS error,
                CONCAT( t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id ) AS cuenta
                FROM '.BD_TEMPORAL.'.tem_PlanCuentas t
                JOIN cat_Usuario u ON (t.usr_id = u.usr_id AND t.cta_gpo_id = u.gpo_id AND t.cta_ur_id = u.ur_id )
                WHERE t.usr_id = ' . $_SESSION[_S]['usr_id'] . ' AND t.cta_gpo_id = :gpo AND t.cta_ur_id = :ur AND t.tem_carga = ":carga" AND t.tem_error <> :error AND t.cta_ue_id != u.ue_id';
        } else {
            $sql7 = '';
        }

        // Armamos la consulta (las separamos para claridad y comentarios solamente)
        $sql = $sql1 . " UNION ALL " . $sql2 . " UNION ALL " . $sql3 . " UNION ALL " . $sql5 . " UNION ALL " . $sql6 . $sql7;
             
        $this->conectar();
        $parUnion =([
            ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur' => $_SESSION[_S]['clasAdm-ur'],
            ':carga'=> $tem_carga,
            ':error'=> 9
        ]);
        // Validamos ...        
        $this->setSql($sql);
        $this->consultar($parUnion);
        $this->desconectar();

        $numErrores = $this->getNumeroRegistros();

        // Si no hubo errores ... se habilita el botón para que lo carguen ...
        if ($numErrores > 0) {

            while ($row = $this->extraer()) {
                $this->mixScript('dataCarga.push( { "a": "' . $row["cuenta"] . '", "i": "' . $row["error"] . '" } );
                                    var nuevaFila = $("<tr>")
                                        .append($("<td>").text("' . $row["cuenta"] . '").css("color", "red"))
                                        .append($("<td>").text("' . $row["error"] . '").css("color", "red"));
                                    $("#tabla-cargaCuentasContables").find("tbody").append(nuevaFila);
                ');
            }
            $this->mixScript('
                $("#cargar").attr("style", "display: none");
                try { window.espera.stop(); } catch(err) { /* nada */ };
            ');

            // Si hubo errores les avisamos ...
        } else {
            $this->mixScript('
                dataCarga.push( { "a": "¡¡¡ REVISION TERMINADA !!!", "i": "Puedes cargar las cuentas a tu Catálogo de Cuentas." } );
                var nuevaFila = $("<tr>")
                    .append($("<td>").text("¡¡¡ REVISION TERMINADA !!!").css("color", "green"))
                    .append($("<td>").text("Puedes cargar las cuentas a tu Catálogo de Cuentas.").css("color", "green"));
                $("#tabla-cargaCuentasContables").find("tbody").append(nuevaFila);
                $("#revisar").attr("style", "display: none");
                $("#cargar").attr("style", "visibility: visible");
                try { window.espera.stop(); } catch(err) { /* nada */ };
            ');
        }
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Cargamos a la base de datos la información de cuentas ya validadas
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function cargar()
    {

        // Obtenemos el número de carga temporal asignado...
        $tem_carga = $this->prm->tc;
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1")
        ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $this->conectar();
        $this->setSql('SET @bit_observacion = "CARGA MASIVA DE CUENTAS"');
        $this->consultar();
        $this->setSql('SET @usr_ip = INET_ATON( "' . $ip . '" )');
        $this->consultar();
        $this->setSql('SET @usr_id = ' . $_SESSION[_S]['usr_id']);
        $this->consultar();

        $sql = "INSERT INTO
            cg_PlanCuentas( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id, cta_nombre, agc_id, usr_id )
            SELECT
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id, cta_nombre, 'GRAL' AS agc_id, " . $_SESSION[_S]['usr_id'] . " AS usr_id
            FROM ".BD_TEMPORAL.".tem_PlanCuentas tem
            WHERE cta_gpo_id = ?
                AND cta_ur_id = ?
                AND tem_carga = ?
                AND ( LENGTH( cta_id ) = 4 OR LENGTH( cta_id ) = 6 )
            ON DUPLICATE KEY UPDATE cta_nombre = IF( TRIM( cg_PlanCuentas.cta_nombre ) = '', tem.cta_nombre, tem.cta_nombre )";
        // Insertamos
        // -----------------------------------------------------------------------------------
        $this->setSql($sql);
        $this->consultar(array($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $tem_carga));

        // http://stackoverflow.com/questions/12623778/count-how-many-rows-inserted-from-last-sql-query
        // según que si es ésta...
        $insertados = $this->getNumeroModificados();

        $this->desconectar();

        $this->mixScript('
            alertify.alert("CARGA MASIVA TERMINADA", "Se cargaron ' . $insertados . ' cuentas al Catálogo de Cuentas").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            comunica("scg/cuentaContable/ControladorCargaCuenta.php", {});
            try { window.espera.stop(); } catch(err) { /* nada */ };
            ');
    }
}
new ControladorCargaCuenta();
