<?php
/**
 * Controlar los catálogos de la CONAC, que arman la cuenta de mayor de una cuenta contable:
 * GENERO + GRUPO + RUBRO + CUENTA + SUBCUENTA
 */

ini_set('max_execution_time', 1500); //1500 seconds = 25 minutes
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';
require_once dirname(__FILE__) . '/../../lib/bootstrap_spreadsheet.php';

class ControladorCargaPoliza extends WebResponse
{
    private $cargaMasiva;
    private $ejercicioActual;

    //Constructor
    public function __construct()
    {
        // Determinamos si el usuario actual tiene permisos de lectura / escritura en la base de datos
        $this->cargaMasiva = isset($_SESSION[_S]['prm']['C']['3']['2']['23']) ? 1 : 0;
        $this->ejercicioActual = $_SESSION[_S]['ejercicioActual'];

        //mandar a llamar constructor base
        parent::__construct();

        $this->metodo = "main";
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
            case "main":
                $this->PonBarraBotones();
                break;
            case "limpiaCarga":
                $this->limpiaCarga();
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
                break;
        }
        $this->returnJson();
    }

    private function PonBarraBotones()
    {
        $botones = '
            <div class="row">
                <div class="row gap-1">
                    <div class="col-md-12 col-xs-12">
                        <div class="col-md-12 col-xs-12 gap-1">
                            <a id="descargar" target="_blank" href="scg/poliza/formato/formato.rar" data-placement="bottom" title="Descargar archivo base para carga masiva en Excel" role="button" type="button" class="btn btn btn-outline-success me-1"><i class="fa fa-download me-2"></i>Descargar Formato</a>
                            <a id="csvReporteGrid" data-placement="bottom" title="Exporta el resultado de la carga masiva" role="button" type="button" class="btn btn-outline-success me-1"><i class="fa fa-cloud-download me-2"></i>Exportar Resultado</a>
                        </div>
                        <div class="col-md-1 col-xs-1" id="descargarTXT" style="display:none"   title="Error de la carga masiva" tabindex="20" ></div>
                    </div>
                </div>
                <span style="height:6px; display:block"></span>' .
            (($this->cargaMasiva) ?
            '<form id="data" enctype="multipart/form-data" class="row">
                        <div class="col-md-5">
                            <input id="action" name="action" type="hidden" value="upload" />
                            <input type="hidden" name="type_carga" id="type_carga" value="POLIZA" />
                            <input id="file-1" name="file-1" type="file" class="form-control" title="Buscar Archivo">
                        </div>
                        <div class="col-md-7">
                            <button id="copiar" type="submit" data-placement="bottom" title="Comenzar el proceso de Carga Masiva" class="btn btn btn-outline-warning ladda-button me-1" data-style="slide-left" cat="" tabindex="20"><i class="fa fa-upload me-2"></i>Copiar</button>
                            <button id="revisar" type="submit" style="display:none" data-placement="bottom" title="Revisar que las pólizas sean válidas" class="btn btn-outline-success me-1" data-style="slide-left" cat="" tabindex="20"><i class="fa fa-check me-2"></i>Revisar</button>
                            <button id="cargar" type="submit" style="display:none" data-placement="bottom" title="Incorporar a la contabilidad las pólizas válidas" class="btn btn-outline-success ladda-button me-1" data-style="slide-left" cat="" tabindex="20"><i class="fa fa-check me-2"></i>Cargar</button>
                        </div>
                    </form>
            </div>

                <span style="height:32px; display:block"></span>
                ' : '<h4>No tienes los permisos necesarios para realizar cargas masivas de Pólizas</h4>') . '';

        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);
        $this->mixScript(' $("nav").hide(); $(".modulos").hide();'
            . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
                $("#sticky-anchor").show();
                $("#sticky").show();
                $("#mainContainer").css("margin-top", "");

                if ( $( ".container" ).css( "max-width" ) == "1170px" ) {
                    $( ".container" ).css( "max-width", "100%" );
                }

                // ------------------------------------------------
                // Exportamos el resultado de la carga a un CSV
                $("#csvReporteGrid").click( function(e) {
                    downloadCSV({ objetoAExportar: dataCarga, filename: "cargaMasivaPolizas.xls" });
                });

                $(document).on("click", "#homeMenu", function (e) {
                    e.preventDefault();
                    $("nav").show();
                    $(".modulos").show();
                    $("#sticky-anchor").hide();
                    $("#sticky").hide();'
            . (($_SESSION[_S]['nvj_id'] < 3) ?
                '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
                    $("#mainContainer").css( "margin-top","40px" );

                    $("#ayuda").addClass("gris");
                    $("#tip").addClass("gris");

                    $("#contenedor").html("");

                    $("nav").menusinpres("clickMenu");

                    $(document).off("click", "#homeMenu" );

                    return false;
                });' .
            (($this->cargaMasiva) ? '
                $("form#data").submit(function (e) {
                    $("#copiar").attr("style", "visibility: hidden");
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
                            console.log(formData);
                            
                            dataCarga = [];
                            dataCarga.push({
                                    "a": "COPIANDO",
                                    "i": "Subiendo el archivo para su revisión.   ID de Carga Temporal " + $("#action").attr("value")
                                });

                            var nuevaFila = $("<tr>")
                                    .append($("<td>").text("COPIANDO").css("color", "green"))
                                    .append($("<td>").text("Subiendo el archivo para su revisión.   ID de Carga Temporal " + $("#action").attr("value")).css("color", "green"));
                            $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);

                                $.ajax({
                                    url: "scg/poliza/ControladorCargaPoliza.php",
                                    type: "post",
                                    data: formData,
                                    async: false,
                                    success: function (data, status, object) {
                                        try {
                                            window.espera.stop();
                                        } catch (err) { console.info(err) };

                                        // Checamos si regreso lo "normal", que es cuando el proceso pasó ...
                                        if (data.indexOf("validacion") != -1) {

                                            // Revisamos en la base de datos el resultado de la carga
                                            comunica("scg/poliza/ControladorCargaPoliza.php", {"metodo": "resultadoCarga", "tc": $("#action").attr("value")});

                                        } else {

                                            dataCarga.push({
                                                "a": "ERROR",
                                                "i": "No pudo completarse el proceso de carga"
                                            });

                                            var nuevaFila = $("<tr>")
                                                .append($("<td>").text("ERROR").css("color", "red"))
                                                .append($("<td>").text("No pudo completarse el proceso de carga").css("color", "red"));
                                            $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);
                                            
                                            // Si es por duplicadas ... mostramos un mensaje corto y no todo el INSERT que falló
                                            if (data.indexOf("Duplicate entry") != -1) {
                                                alertify.alert("ERROR AL CARGAR ARCHIVO", "Al menos una póliza en la carga masiva ya existe en el archivo Temporal<br />Revisa que tu archivo no tenga pólizas duplicadas");
                                            } else {
                                                alertify.alert("ERROR AL CARGAR ARCHIVO", data);
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
                                alertify.alert(
                                    "ERROR AL CARGAR ARCHIVO",
                                    message,
                                    function () {
                                        $("#descargarTXT").html("<a class=\'btn btn-xs btn-danger\' id=\'download_file\'  href=\'archivos/failedCargaMasiva/poliza/' . date('m') . '/' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '/"+parsed.txt+".txt\' download>Descargar TXT de Error</a>");
                                        if (parsed.txt){
                                            $("#descargarTXT").show();
                                        }
                                    },
                                );

                                var nuevaFila = $("<tr>")
                                        .append($("<td>").text("COPIANDO").css("color", "green"))
                                        .append($("<td>").text("Subiendo el archivo para su revisión.   ID de Carga Temporal " + $("#action").attr("value")).css("color", "green"));
                                $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);

                                $("#copiar").attr("style", "visibility: visible");
                                $("#revisar").attr("style", "display: none");
                            } else {
                                message = "Ocurrio un error al retornar respuesta, intente de nuevo.";
                            }
                        }
                    }).always(function(){
                        hideLoading();
                    });
                    return false;
                });

                $("#download_file").click(function(e) {
                    var now = new Date().toString();
                    this.href = "data:text/plain;charset=UTF-8,"  + encodeURIComponent(now);
                });

                // ------------------------------------------------
                // Revisar que la información cargada en la póliza temporal sea correcta, antes de cargarla a producción
                $("#revisar").click( function(e) {
                    e.preventDefault();
                    window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
                    comunica( "scg/poliza/ControladorCargaPoliza.php", { "metodo" : "revisar", "tc" : $("#action").attr("value") });
                    e.stopPropagation();
                    return false;
                });

                // ------------------------------------------------
                // Si ya pasó la revisión ... cargamos a la contabilidad
                $("#cargar").click( function(e) {
                    e.preventDefault();
                    window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
                    comunica( "scg/poliza/ControladorCargaPoliza.php", { "metodo" : "cargar", "tc" : $("#action").attr("value") } );
                    e.stopPropagation();
                    return false;
                });

                $("#file-1").change(function(e){

                    // Limpiamos cualquier intento previo de carga de esta ur + fecha + usuario
                    comunica( "scg/poliza/ControladorCargaPoliza.php", { "metodo" : "limpiaCarga", "tc" : $("#action").attr("value") });

                    $("#action").attr( "value", "" );
                    $("#copiar").attr("style", "visibility: visible");
                    $("#revisar").attr("style", "display: none");
                    $("#cargar").attr("style", "display: none");
                    $("#descargarTXT").attr("style", "display: none");
                    $("#descargarTXT").attr("href", "");

                    $("#tabla-cargaMasiva tbody tr").remove();

                    try { window.espera.stop(); } catch(err) { /* nada */ };

                });' : '') . '

                comunica("scg/poliza/ControladorCargaPoliza.php",{"metodo":"dibujaPantalla"});

            ');
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function dibujaPantalla()
    {
        $this->setContenedor("contenedor");
        $this->mixScript('$("#tituloPantalla").hide();');

        // Si no tiene permisos de cargas masivas, no dibujamos el grid
        if (!$this->cargaMasiva) {
            return false;
        }

        $tabla = new stdClass();
        $tabla->id = 'cargaMasiva';
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

        $this->mixScript('$(".tabla-footer").remove();');
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Copiamos el archivo al servidor + cargamos su información en la tabla temporal
    // NOTA: COMO NO PUDE HACER QUE ESTA FUNCION SE COMUNIQUE CON EL AJAX QUE LO LLAMA, SE ME OCURRIO LA BRILLANTE IDEA DE
    // GRABAR EN tem_PolizaMaestro LOS MENSAJES, SIMPLEMENTE GRABANDO EN EL CAMPO pol_numero el valor 99999 y epl_id = 'C'  ...
    // así al regresar de ésta función la que la llama buscará estos datos
    // Obviamente para insertar debe decir     WHERE pol_numero <> 99999 AND epl_id <> 'C'
    //
    // DISTRIBUCION DE LAS COLUMNAS
    // ------------------     DATOS DEL ENCABEZADO     ------------------                                    -------- DATOS DEL DOCUMENTO FUENTE -------   ------------------- DATOS QUE USA FONDOS -----------------
    // UR | UE | MES | TIPO | PREFIJO | NUMERO | CONCEPTO | DIA | CHEQUE_CANCELADO | CUENTA | UE cuenta | DEBE | HABER | TIPO | NUMERO | FECHA | RFC | OBSERVACIONES | CLAVE_FINANCIAMIENTO | UR_ORIGEN | CLC_ORIGEN | AFE_ORIGEN | BDO_ORIGEN
    // 0  | 1  | 2   | 3    | 4       | 5      | 6        | 7   | 8                | 9      | 10        | 11   | 12    | 13   | 14     | 15    | 16  | 17            | 18                   | 19          | 20          | 21         | 22
    //
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
            $conexion->setSql("INSERT INTO ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro
                (gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, pma_debe, pma_haber, pma_concepto, mmc_id, epl_id, apg_id, tem_carga, usr_id )
                VALUES
                ( :gpo, :ur, '001', 'D', '0', 1, 99999, 0, 0, 'EL TIPO DE ARCHIVO NO ES VÁLIDO', 'V', 'C', 'GRAL', :temcarga, :usr );");
            $conexion->consultar([
                ':gpo' => getSession('clasAdm-gpo'),
                ':ur' => getSession('clasAdm-ur'),
                ':temcarga' => $tem_carga,
                ':usr' => getSession('usr_id'),
            ]);
            $conexion->desconectar();
            return false;
        }

        $filename = $_FILES["file-1"]["tmp_name"];
        $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
        $Polizas = $objPHPExcel->getSheetByName("POLIZA");
        $ultimaColumn = "W";
        $ultimafila = $Polizas->getHighestRow();
        $numeroColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ultimaColumn);

        $datosPolizas = array();
        $indiceData = 0;
        for ($fila = 5; $fila <= $ultimafila; $fila++) {
            if ($Polizas->getCell('A' . $fila)->getValue() != '') {
                $columna = "A";
                for ($j = 0; $j < $numeroColumna; $j++) {
                    $datosPolizas[$indiceData][$j] = $Polizas->getCell($columna . $fila)->getValue();
                    $columna++;
                }
            }
            $indiceData++;
        }
        // Si no pudimos leer el archivo ...
        if (count($datosPolizas) == 0) {
            // Mensaje de error "patito"
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql("INSERT INTO ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro
            ( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, pma_debe, pma_haber, pma_concepto, mmc_id, epl_id, apg_id, tem_carga, usr_id )
            VALUES( :gpo, :ur, '001', 'D', '0', 1, 99999, 0, 0, 'NO FUÉ POSIBLE LEER EL ARCHIVO', 'V', 'C', 'GRAL', :temcarga, :usr );");

            $conexion->consultar([
                ':gpo' => getSession('clasAdm-gpo'),
                ':ur' => getSession('clasAdm-ur'),
                ':temcarga' => $tem_carga,
                ':usr' => getSession('usr_id'),
            ]);
            $conexion->desconectar();
            return false;
        }

        $sqlC = ''; //contiene los registros a insertarse en tem_PolizaDetalle
        $contadorRegistroC = 0; //contiene el numero de registros que $sqlC tiene registrados
        $maxRegistrosPorCarga = 2000; // Registros que intentará insertar cada que ejecute INSERT ...
        $reiniC = true;

        // Obtenemos el número de registros
        $registros = $datosPolizas;
        $nVeces = sizeof($registros);
        $nTotalRegistros = sizeof($registros);

        $gpo_id = 0;
        $ur_id = 0;
        $ue_id = 0;
        $msc_id = 0;
        $prf_id = "";
        $pot_id = "";
        $pol_numero = 0;
        $pol_concepto = "";
        $pol_fecha = 0;
        $epl_id = "";
        $cuenta = "";
        $debe = 0;
        $haber = 0;
        $df_tipo = "";
        $df_numero = "";
        $df_fecha = "";
        $df_rfc = "";
        $df_observaciones = "";
        $cf = "";
        $URorigen = "";

        $bolZonaDatos = false;

        $sqlPolizas = "
            INSERT INTO ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar(
            gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, tem_pdt,
            ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id,
            pdx_debe, pdx_haber, nat_id,
            pdx_concepto, rfc_id, pdx_referencia, pdx_fechaReferencia, tdf_id, adf_id,
            ecf_id, pdx_gpo_id_origen, pdx_ur_id_origen,
            pdx_clc_id_origen, pdx_afe_id_origen, pdx_bdo_id_origen,
            tem_carga, usr_id, tem_pma ) VALUES
        ";

        for ($i = 0; $i < $nVeces; $i++) {
            $data[$i] = $registros[$i];
            // SABEMOS QUE DEBE VENIR LA LINEA CON EL TEXTO "UR" EN EL ARRAY 0 ... Y EL TEXTO "UE" EN EL ARRAY 1 ..
            // Antes de encontrar ésas líneas no tomamos la información, ya que corresponde a lo que está antes del encabezado y los datos
            // Checamos que sea una línea de datos ... ya que si viene al final o en medio una línea vacía, regresa un array de 1 posición
            // Y el registro debe contener 23 posiciones para considerarse línea de datos
            if (count($data[$i]) >= 22) {
                // Si la cuenta no esta vacía
                if (trim($data[$i][10])) {
                    // Si cambia la UE, el mes, el tipo, el prefijo ó número de póliza, es que ... bueno ... es una nueva póliza :D
                    if (
                        $ue_id != $data[$i][1] || $msc_id != $data[$i][2] || $pot_id
                        != $data[$i][3] || $prf_id != $data[$i][4] || $pol_numero
                        != $data[$i][5]
                    ) {

                        // Si la línea viene vacía, pero ya hay datos del encabezado de la póliza, es que no están repitiendo esos datos en cada línea
                        // Checamos sólo el campo UR [0]
                        if (trim($data[$i][0]) != "") {

                            // Guardamos los datos en variables ...
                            $gpo_id = $_SESSION[_S]['clasAdm-gpo']; // NO PELAMOS LO QUE VENGA EN EL EXCEL
                            $ur_id = $_SESSION[_S]['clasAdm-ur']; // USAMOS LO QUE DIGA LA CLASIFICACION ADMINISTRATIVA
                            $ue_id = $data[$i][1];

                            $msc_id = $data[$i][2];

                            $pot_id = $data[$i][3];
                            $prf_id = $data[$i][4];
                            $pol_numero = $data[$i][5];
                            $pol_concepto = utf8Encode($data[$i][6]);
                            $pol_concepto = str_replace(
                                '""',
                                '"',
                                $pol_concepto
                            );
                            $pol_concepto = strtoupper($pol_concepto);

                            $pol_concepto = preg_replace(
                                sprintf(
                                    '~[^%s]++~i',
                                    '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'
                                ),
                                ' ',
                                $data[$i][6]
                            );
                            $pol_concepto = preg_replace(
                                '/\s\s+/',
                                ' ',
                                $pol_concepto
                            );
                            $pol_concepto = utf8Encode($pol_concepto);
                            $pol_concepto = iconv(
                                'UTF-8',
                                'UTF-8',
                                $pol_concepto
                            ); // Increíble pero cierto, convertir a lo mismo ... para que funcione :(

                            $pol_fecha = $this->ejercicioActual . '-' . str_pad(
                                $data[$i][2],
                                2,
                                "0",
                                STR_PAD_LEFT
                            ) . "-" . str_pad(
                                $data[$i][7],
                                2,
                                "0",
                                STR_PAD_LEFT
                            );
                            //$pol_fecha = date_to_mysqldate($pol_fecha);
                            // Marcos: 18/ene/2018
                            // Me dá error el INSERT si va 'NULL' con comillas, por lo que haremos un justito
                            // aquí para ponerle ésas comillas sólo cuando la fecha no regrese NULL
                            if ($pol_fecha != "NULL") {
                                $pol_fecha = $pol_fecha;
                            }

                            $epl_id = (($data[$i][8] == "1") ? "H" : "N"); // Si la columna trae 1 es Cheque cancelado ... si no es Normal
                            // Excel ... si incluye comas la descripción ... lo encierra entre comillas ... asi que aqui checamos si tiene comillas al
                            // principio y final de la cadena, se lo quitamos ...
                            $pol_concepto = trim($pol_concepto);
                            if (substr($pol_concepto, 0, 1) == '"' && substr(
                                $pol_concepto,
                                -1
                            ) == '"') {
                                $pol_concepto = substr($pol_concepto, 1); // Le quitamos la primera
                                $pol_concepto = substr(
                                    $pol_concepto,
                                    0,
                                    strlen($pol_concepto) - 1
                                ); // Le quitamos la última
                            }

                            $apg_id = (($data[$i][9] == "1") ? "FE" : "GRAL");
                            if ($gpo_id . $ur_id == 116) {
                                $buscar = strpos(
                                    $pol_concepto,
                                    'REGISTRO DE MOVIMIENTOS BANCARIOS'
                                );
                                if ($buscar !== false) {
                                    $apg_id = 'SAP';
                                }
                            }
                            // y lo insertamos en el maestro temporal
                            $conexion = new AccesoDatos();
                            $conexion->conectar();
                            $sql = "INSERT INTO ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro
                            (gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, pma_debe, pma_haber,pma_concepto, pma_fecha, mmc_id, epl_id, apg_id, tem_carga, usr_id )
                            VALUES(:gpo, :ur, :ue, :pot, :prf, :msc, :pol_numero, 0, 0, :concepto, :fecha,
                                'V', :epl, :apg, :temcarga, :usr)";
                            $conexion->setSql($sql);
                            $conexion->consultar([
                                ':gpo' => $gpo_id, ':ur' => $ur_id, ':ue' => $ue_id,
                                ':pot' => $pot_id, ':prf' => $prf_id, ':msc' => $msc_id, ':pol_numero' => $pol_numero,
                                ':concepto' => mysql_escape_mimic($pol_concepto),
                                ':fecha' => $pol_fecha, ':epl' => $epl_id, ':apg' => $apg_id,
                                ':temcarga' => $tem_carga, ':usr' => getSession('usr_id'),
                            ]);
                            $conexion->desconectar();
                            $totalDebe = 0;
                            $totalHaber = 0;
                        }
                    }

                    // Tomamos los datos de la línea de la póliza
                    $cuenta = trim($data[$i][10]);

                    // La longitud de la cuenta DEBE ser de 18, 21 o 23 dígitos solamente
                    // 1 = 1.1.1.3.1.001.0001               18 dígitos, 6 puntos
                    // 2 = 1.1.1.3.1.001.01.0001             21 dígitos, 7 puntos
                    // 3 = 1.1.1.3.1.432.123456789          23 dígitos, 6 puntos

                    if (
                        strlen($cuenta) == 18 || strlen($cuenta) == 21 || strlen($cuenta)
                        == 23
                    ) {

                        // Marcos: 17/ene/2018
                        // La clasificación administrativa NO vendrá dentro de la cuenta contable
                        $cta_gpo_id = $gpo_id;
                        $cta_ur_id = $ur_id;
                        $cta_ue_id = $data[$i][11]; // La UE de la cuenta contable tendrá su propia columna
                        // Convertimos a array la cuenta
                        $arrCta = explode(".", $cuenta);

                        $ger_id = (count($arrCta) >= 1 ? $arrCta[0] : "");
                        $cgp_id = (count($arrCta) >= 2 ? $arrCta[1] : "");
                        $rub_id = (count($arrCta) >= 3 ? $arrCta[2] : "");
                        $cgcu_id = (count($arrCta) >= 4 ? $arrCta[3] : "");
                        $cgsc_id = (count($arrCta) >= 5 ? $arrCta[4] : "");

                        $cgc_id = (count($arrCta) >= 6 ? $arrCta[5] : "");

                        // Si es de 21 dígitos, entonces tiene un nivel más que hay que juntar los dos últimos niveles
                        if (count($arrCta) == 8) {
                            $cta_id = $arrCta[6] . $arrCta[7];
                        } else {
                            $cta_id = (count($arrCta) >= 6 ? $arrCta[6] : "");
                        }

                        if (trim($data[$i][12]) != "") {
                            $debe = (is_numeric(floatval($data[$i][12])) ? $data[$i][12]
                                : 0);
                        } else {
                            $debe = 0;
                        }

                        if (trim($data[$i][13]) != "") {
                            $haber = (is_numeric(floatval($data[$i][13])) ? $data[$i][13]
                                : 0);
                        } else {
                            $haber = 0;
                        }

                        $df_tipo = ((isset($data[$i][14]) && trim($data[$i][14])
                            != "") ? $data[$i][14] : "GRAL");
                        $df_numero = $data[$i][15];
                        $pasoFecha = date_to_mysqldate($data[$i][16]);

                        // Marcos: 18/abr/2017
                        // La fecha no pasa bien
                        $df_fecha = str_replace("-", "/", $pasoFecha);
                        $df_fecha = date_to_mysqldate($df_fecha);
                        // Marcos: 18/ene/2018
                        // Me dá error el INSERT si va 'NULL' con comillas, por lo que haremos un justito
                        // aquí para ponerle ésas comillas sólo cuando la fecha no regrese NULL
                        if ($df_fecha != "NULL") {
                            $df_fecha = "'" . $df_fecha . "'";
                        }

                        $df_rfc = $data[$i][17];

                        $df_observaciones = utf8Encode($data[$i][18]);
                        $df_observaciones = trim(str_replace(
                            '""',
                            '"',
                            $df_observaciones
                        ));

                        $df_observaciones = preg_replace(
                            sprintf(
                                '~[^%s]++~i',
                                '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%|'
                            ),
                            ' ',
                            $data[$i][18]
                        );
                        $df_observaciones = preg_replace(
                            '/\s\s+/',
                            ' ',
                            $df_observaciones
                        );
                        $df_observaciones = iconv(
                            'UTF-8',
                            'UTF-8',
                            $df_observaciones
                        ); // Increíble pero cierto, convertir a lo mismo ... para que funcione :(
                        // Excel ... si incluye comas la descripción ... lo encierra entre comillas ... asi que aqui checamos si tiene comillas al
                        // principio y final de la cadena, se lo quitamos ...
                        $df_observaciones = trim($df_observaciones);
                        if (substr($df_observaciones, 0, 1) == '"' && substr(
                            $df_observaciones,
                            -1
                        ) == '"') {
                            $df_observaciones = substr($df_observaciones, 1); // Le quitamos la primera
                            $df_observaciones = substr(
                                $df_observaciones,
                                0,
                                strlen($df_observaciones) - 1
                            ); // Le quitamos la última
                        }

                        $cf = trim($data[$i][19]);
                        $URorigen = $data[$i][20];

                        // La clave de financiamiento DEBE existir, para poder obtener
                        // su id y ése ID es el que debemos grabar en la base de datos
                        // Para que funcione, la clave de financiamiento debe venir en
                        // formato AAAAA0115
                        if (trim($cf) != "") { // Para que entren sólo si hay algo que buscar
                            $ecf_id = $this->dameEcf_id($cf);
                        } else {
                            $ecf_id = "NULL";
                        }

                        $gpo_id_origen = "NULL";
                        $ur_id_origen = "NULL";
                        // La UR debe ser de 3 posiciones, numérica, y existir
                        if (preg_match("/[0-9]*$/", $URorigen) == 1) {
                            // Si cumple las reglas, validamos que sea de 3 posiciones
                            if (strlen(trim($URorigen)) == 3) {
                                // Validamos que exista en la base de datos
                                if ($this->validaUR($URorigen) == $URorigen) {
                                    $gpo_id_origen = substr($URorigen, 0, 1);
                                    $ur_id_origen = substr($URorigen, 1, 2);
                                }
                            }
                        }

                        // Marcos: 15/jun/2016
                        // Se adicionan las columnas CLC y AFECTACION origen
                        $clc_origen = $data[$i][21];
                        $afe_origen = $data[$i][22];

                        if (trim($clc_origen) == "") {
                            $clc_origen = "NULL";
                        }

                        if (trim($afe_origen) == "") {
                            $afe_origen = "NULL";
                        }

                        // Si escriben el tipo + referencia + rfc del documento fuente
                        // ubicamos su bdo_id para guardarlo en la base de datos
                        if (
                            trim($df_tipo) != "" && trim($df_numero) != "" && trim($df_rfc)
                            != ""
                        ) {
                            $bdo_id = $this->dameBdoId(
                                $df_tipo,
                                $df_numero,
                                $df_rfc
                            );
                        } else {
                            $bdo_id = "NULL";
                        }

                        // Marcos: 17/ene/2018
                        // Se adiciona el campo BDO_ID PARA FONDOS
                        $bdo_origen = $data[$i][23];

                        if (trim($bdo_origen) == "") {
                            $bdo_origen = "NULL";
                        }

                        if (!is_numeric($bdo_origen)) {
                            $bdo_origen = "NULL";
                        } else {
                            $bdo_id = $this->validaBdoId(
                                $gpo_id_origen,
                                $ur_id_origen,
                                $bdo_origen
                            );
                        }

                        // Sólo insertamos aquellas pólizas que pertenezcan a la UR actual ... por si están tratando de cargar de otra UR
                        if (
                            $cta_gpo_id == $_SESSION[_S]['clasAdm-gpo'] and $cta_ur_id == $_SESSION[_S]['clasAdm-ur']
                        ) {

                            $sqlC .= (!$reiniC) ? ', ' : '';
                            $sqlC .= "( $gpo_id, $ur_id, $ue_id, '$pot_id', '$prf_id', $msc_id, $pol_numero, 0, " .
                                "$ger_id, $cgp_id, $rub_id, $cgcu_id, $cgsc_id, $cta_gpo_id, $cta_ur_id, $cta_ue_id, '$cgc_id', '$cta_id', $debe, $haber, " .
                                (($debe != 0 && $haber == 0) ? "'D'" : "'H'") . ", '$df_observaciones', '$df_rfc', '$df_numero', " . $df_fecha . ", '$df_tipo', 'GRAL', " .
                                $ecf_id . "    , " . $gpo_id_origen . ", " . $ur_id_origen . ", " .
                                $clc_origen . ", " . $afe_origen . ", " . $bdo_id . ", " .
                                "'$tem_carga', " . $_SESSION[_S]['usr_id'] . ", 0 )";
                            $reiniC = false;
                            $contadorRegistroC++;
                        }

                        //para realizar un insert cuando llegue al máximo de registros que diga la variable $maxRegistrosPorCarga ( 2,000 originalmente )
                        // o cuando se acaben los registros a insertar si es menor a esa cantidad
                        if (($contadorRegistroC > $maxRegistrosPorCarga) || ($i == $nTotalRegistros)) {

                            // Siempre y cuando haya algo por cargar ...
                            if ($contadorRegistroC > 0) {
                                $conexion = new AccesoDatos();
                                $conexion->conectar();
                                $conexion->setSql($sqlPolizas . $sqlC);
                                $conexion->consultar();
                                $conexion->desconectar();
                                $sqlC = '';
                                $contadorRegistroC = 0;
                                $reiniC = true;
                            }
                        }
                    }
                }
            }

            // Cuando encontremos la línea que termina el encabezado, empezamos a tomar información

            // Si vienen al final líneas en blanco ... nunca entrará a grabar los registros
            // Aquí verificamos si hay algo pendiente de grabar y -si lo hay - lo grabamos
            if ($reiniC == false && $sqlC != "") {
                // Siempre y cuando haya algo por cargar ...
                if ($contadorRegistroC > 0) {
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql($sqlPolizas . $sqlC);
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
    // Checamos en la tabla tem_PolizaMaestro que se hayan cargado pólizas realmente ... y buscamos mensajes de error "patito"
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function resultadoCarga()
    {
        // Obtenemos el id de carga temporal
        $tc = $_POST['tc'];

        // Obtenemos los errores "patito" del proceso de carga
        
        $sql = "
            SELECT pma_concepto AS error, '---> ERROR DE CARGA' AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro
            WHERE gpo_id = :gpo AND ur_id = :ur AND tem_carga = :tc
                AND pma_id = 99999 AND epl_id = 'C';
        ";
        $params = [
            ':gpo' => getSession('clasAdm-gpo'),
            ':ur' => getSession('clasAdm-ur'),
            ':tc' => $tc,
        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();

        $numErrores = $conexion->getNumeroRegistros();

        // Si hubo errores ... avisamos ...
        if ($numErrores > 0) {

            while ($row = $conexion->extraer()) {
                $this->mixScript(' dataCarga.push( { "a": "' . $row["accion"] . '", "i": "' . $row["error"] . '" } );
                                        var nuevaFila = $("<tr>")
                                                .append($("<td>").text("' . $row["accion"] . '").css("color", "red"))
                                                .append($("<td>").text("' . $row["error"] . '").css("color", "red"));
                                        $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);
                                ');
            }
            $this->mixScript('
                    $("#copiar").attr("style", "visibility: visible");
                    $("#revisar").attr("style", "display: none");
                    try { window.espera.stop(); } catch(err) { /* nada */ };
                ');

            // Si NO hubo errores, checamos que realmente exista algo para cargar ...
        } else {
            // Revisamos que realmente existan pólizas listas para la revisión
            $sql = "SELECT COUNT(*) AS total
                FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro
                WHERE gpo_id = ? AND ur_id = ? AND tem_carga = ? ";

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar([getSession('clasAdm-gpo'), getSession('clasAdm-ur'), $tc]);
            $conexion->desconectar();

            $row = $conexion->extraer();
            $numPolizasCargadas = $row['total'];

            // Si el proceso dejó al menos una cuenta lista para revisar ...
            if ($numPolizasCargadas > 0) {
                $this->mixScript('
                        dataCarga.push( {
                            "a": "COPIADO !!!",
                            "i"  : "Listo para su revisión"
                        } );

                        var nuevaFila = $("<tr>")
                            .append($("<td>").text("COPIADO !!!").css("color", "green"))
                            .append($("<td>").text("Listo para su revisión").css("color", "green"));
                        $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);

                        $("#revisar").attr("style", "visibility: visible");
                        $("#copiar").attr("style", "display: none");
                        try { window.espera.stop(); } catch(err) { /* nada */ };
                    ');

                // Si no ... algo pasó ...
            } else {
                $this->mixScript('
                        dataCarga.push( {
                            "a": "ERROR",
                            "i"  : "NINGUNA póliza se procesó ... revisa tu archivo."
                        } );

                         var nuevaFila = $("<tr>")
                            .append($("<td>").text("ERROR").css("color", "red"))
                            .append($("<td>").text("NINGUNA póliza se procesó ... revisa tu archivo.").css("color", "red"));
                        $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);

                        $("#copiar").attr("style", "visibility: visible");
                        $("#revisar").attr("style", "display: none");
                        try { window.espera.stop(); } catch(err) { /* nada */ };
                    ');
            }
        }
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Checamos en la tabla tem_PolizaMaestro que se hayan cargado pólizas realmente ... y buscamos mensajes de error "patito"
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function limpiaCarga()
    {
        // Borramos las cargas del grupo ... y usuario actual
        $tc1 = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . "-";
        $tc2 = $_SESSION[_S]['usr_id'];

        // Marcos: 29/abr/2016
        // Algunos usr_id son de más de 3 dígitos, y la consulta buscaba fijo 3 dígitos
        // Así que ahora será dinámico para ajustarse al ancho real
        $ancho = strlen((string) $tc2);

        // Borramos una carga previa
        $conexion = new AccesoDatos();
        $conexion->conectar();

        // Borramos del maestro
        //TESTEAR ESTA PARTE POR  =Y EL SEGUNDO PARAMETRO DEL SUBSTRING
        $conexion->setSql("
            DELETE FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro
            WHERE gpo_id = ? AND ur_id = ?
                AND tem_carga LIKE ?
                AND SUBSTRING( tem_carga, 12, ? ) = ?;");
        $params = [getSession('clasAdm-gpo'), getSession('clasAdm-ur'), "%{$tc1}%", $ancho, $tc2];
        $conexion->consultar($params);

        // Borramos del detalle
        $conexion->setSql("
            DELETE FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar
            WHERE gpo_id = ? AND ur_id = ? AND tem_carga LIKE ?
                AND SUBSTRING( tem_carga, 12, ? ) = ?
        ");
        $conexion->consultar($params);

        $conexion->desconectar();
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Revisamos las pólizas cargadas en la tabla temporal ...
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function revisar()
    {

        // Obtenemos el id de carga temporal
        $tc = $_POST['tc'];
        $params = [
            ':gpo' => getSession('clasAdm-gpo'),
            ':ur' => getSession('clasAdm-ur'),
            ':tc' => $tc,
        ];

        // Revisamos que la Unidad Ejecutorá exista para la UR que se está revisando
        $sql1 = "SELECT CONCAT( 'La Unidad Ejecutora ', m.ue_id, ' no es valida' ) AS error, m.ue_id AS accion
                FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
                LEFT JOIN cat_UnidadEjecutora e USING( gpo_id, ur_id, ue_id )
                WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                    AND e.ue_id IS NULL";

        // Revisamos que el Tipo de Póliza exista en el catálogo cat_TipoPoliza
        $sql2 = "SELECT CONCAT( 'El tipo de poliza ', m.pot_id, ' no es valido' ) AS error, m.pot_id AS accion
                FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
                LEFT JOIN cat_TipoPoliza t USING( pot_id )
                WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                    AND t.pot_id IS NULL";

        // Revisamos que el Prefijo de Póliza exista en el catálogo cat_PrefijoPoliza
        $sql3 = "SELECT CONCAT( 'El prefijo ', m.prf_id, ' no es valido' ) AS error, m.prf_id AS accion
                FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
                LEFT JOIN cat_PrefijoPoliza f USING( prf_id )
                WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                    AND f.prf_id IS NULL";

        // Revisamos que el mes exista en el catálogo cat_MesContable
        $sql4 = "
            SELECT CONCAT('El mes ', m.msc_id, ' no es valido' ) AS error, m.msc_id AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
            LEFT JOIN cat_MesContable mes USING( msc_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND mes.msc_id IS NULL
            UNION
            SELECT CONCAT('La fecha ', m.pma_fecha, ' no es válida' ) AS error, m.pma_fecha AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND (DATE_FORMAT(pma_fecha, '%Y-%m-%d') = '{$this->ejercicioActual}-00-00' OR pma_fecha IS NULL)
        ";

        // Si la póliza NO es de cheque cancelado, debe tener un importe en el debe o en el haber
        $sql5 = "SELECT 'La póliza NO es de cheque cancelado ... por lo tanto debe tener un importe en el cargo o el abono' AS error,
            'importes en cero' AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
            INNER JOIN ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND  m.epl_id != 'H' AND d.pdx_debe = 0 AND d.pdx_haber = 0";

        // Si la póliza ES de cheque cancelado, NO DEBE tener importes
        $sql6 = "
            SELECT 'La póliza ES de cheque cancelado ... por lo tanto NO DEBE tener importes' AS error,
            d.pdx_Debe + d.pdx_Haber  AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
            INNER JOIN ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND m.epl_id = 'H' AND d.pdx_debe <> 0 AND d.pdx_haber <> 0";

        // Revisamos que la póliza tenga detalles
        // Checamos contra el campo CTA_ID que es de la tabla detalle, y que siempre siempre debe tener algo
        $sql7 = "
            SELECT 'La póliza no tiene ninguna línea de detalle' AS error,
            CONCAT( 'Póliza ', m.pot_id, '-', m.prf_id, m.pma_id, ' sin detalles' )  AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
            LEFT JOIN ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND d.cta_id IS NULL";

        // Revisamos que la póliza tenga encabezado (Maestro)
        // Checamos contra el campo POL_CONCEPTO que es de la tabla Maestro, y que siempre siempre debe tener algo
        $sql8 = "
            SELECT 'Faltan los datos generales de la póliza (Encabezado)' AS error, 'Falta el Maestro'  AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
            RIGHT JOIN ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND m.pma_concepto IS NULL";

        // Revisamos que los importes de debe y haber coincidan
        // ( no validamos que la suma del debe y haber del detalle coincida con la del maestro porque la hacemos al iniciar éste proceso )
        $sql9 = "
            SELECT CONCAT( 'debe = ', d.pdx_debe, ' / haber = ', d.pdx_haber ) AS error,
            CONCAT( 'Suma Debe/Haber no coinciden en ', m.pot_id, '-', m.prf_id, m.pma_id )  AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
                INNER JOIN (
                    SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, SUM( pdx_debe ) AS pdx_debe, SUM( pdx_haber ) AS pdx_haber
                    FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar
                    WHERE gpo_id = :gpo AND ur_id = :ur AND tem_carga = :tc
                    GROUP BY gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id
                )d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND ( d.pdx_debe != d.pdx_haber )";

        // Revisamos que las cuentas usadas existan en el catálogo de cuentas
        $sql10 = "
            SELECT 'La cuenta no existe en el catalogo de cuentas' AS error,
            CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, '.', cta_gpo_id, cta_ur_id, '.', cta_ue_id, '.', cgc_id, '.', cta_id ) AS accion
                FROM cg_PlanCuentas
                RIGHT JOIN (
                    SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id
                    FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d
                    WHERE gpo_id = :gpo AND ur_id = :ur AND tem_carga = :tc
                )d USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id )
                WHERE cta_cuenta IS NULL
                GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id";

        // Marcos, 13/oct/2015
        // Se adiciona la validación de que el mes esté abierto para poder cargar información
        $sql11 = "
            SELECT CONCAT( 'El mes ', m.msc_id, ' no está abierto (', ems_id, ') ' ) AS error, m.msc_id AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m
            JOIN cg_MesContable USING (gpo_id, ur_id, msc_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND m.tem_carga = :tc
                AND ems_id <> 'A' ";

        // Marcos, 16/oct/2015
        // Se adiciona la validación de tipo de documento fuente
        $sql12 = "
            SELECT CONCAT( 'El tipo de documento fuente [', d.tdf_id, '] no existe en el catálogo' ) AS error, d.tdf_id AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d
            JOIN cat_TipoDocumentoFuente t USING (tdf_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND d.tem_carga = :tc
                AND t.tdf_id IS NULL ";

        // Marcos, 15/junio/2016
        // Se adiciona la validación del RFC en cat_Proveedores, cuando no venga vacío
        $sql13 = "
            SELECT 'El RFC no existe en el catálogo de Personas' AS error, CONCAT( 'El RFC ', d.rfc_id , ' no existe' )  AS accion
            FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d
            LEFT JOIN cat_Proveedor p USING( rfc_id )
            WHERE d.gpo_id = :gpo AND d.ur_id = :ur AND d.tem_carga = :tc
                AND TRIM( d.rfc_id ) <> '' AND p.rfc_id IS NULL ";

        $sql14 = "";
        if (claveClasAdm() == 116) {
            $sql14 = " UNION ALL ";
            $sql14 .= "
                SELECT 'FALTA UR DE ORIGEN' AS error, CONCAT( 'UR DE ORIGEN', ' ES OBLIGATORIO' )  AS accion
                FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar d
                LEFT JOIN cat_Proveedor p USING( rfc_id )
                WHERE d.gpo_id = :gpo AND d.ur_id = :ur AND d.tem_carga = :tc
                AND ((pdx_gpo_id_origen is null) OR (pdx_ur_id_origen IS NULL))
            ";
        }
        // Armamos la consulta (las separamos para claridad y comentarios solamente)
        $sql = $sql1 . " UNION ALL " . $sql2 . " UNION ALL " . $sql3 . " UNION ALL " . $sql4 . " UNION ALL " . $sql5 . " UNION ALL " . $sql6 . " UNION ALL " . $sql7 . " UNION ALL " . $sql8 . " UNION ALL " . $sql9 . " UNION ALL " . $sql10 . " UNION ALL " . $sql11 . " UNION ALL " . $sql12 . " UNION ALL " . $sql13 . " " . $sql14;

        $conexion = new AccesoDatos();
        $conexion->conectar();
        // Aprovechamos para sumarizar todos los detalles en cada póliza del maestro

        $sqlSuma = "
            UPDATE ctrl{$this->ejercicioActual}Temporal.tem_PolizaMaestro m INNER JOIN (
                SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, SUM( pdx_debe ) AS debe, SUM( pdx_haber ) AS haber
                FROM ctrl{$this->ejercicioActual}Temporal.tem_PolizaDetalleAuxiliar
                WHERE gpo_id = :gpo AND ur_id = :ur AND tem_carga = :tc
                GROUP BY gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id
            ) AS d USING ( gpo_id, ur_id, ue_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            SET m.pma_debe = d.debe, m.pma_haber = d.haber;
        ";
        $conexion->setSql($sqlSuma);
        $conexion->consultar($params);

        // Validamos ...
        
        $conexion->setSql($sql);
        $conexion->bindParams($params);
        $conexion->consultar();

        $numErrores = $conexion->getNumeroRegistros();

        // Terminamos
        
        $conexion->desconectar();

        // Si no hubo errores ... se habilita el botón para que lo carguen ...
        if ($numErrores > 0) {

            while ($row = $conexion->extraer()) {
                $this->mixScript('dataCarga.push( { "a": "' . $row["accion"] . '", "i": "' . $row["error"] . '" } );
                                    var nuevaFila = $("<tr>")
                                        .append($("<td>").text("' . $row["accion"] . '").css("color", "red"))
                                        .append($("<td>").text("' . $row["error"] . '").css("color", "red"));
                                    $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);
                ');
            }
            $this->mixScript('
                    $("#cargar").attr("style", "display: none");
                    try { window.espera.stop(); } catch(err) { /* nada */ };
                ');

            // Si hubo errores les avisamos ...
        } else {
            $this->mixScript('dataCarga.push( { "a": "¡¡¡ REVISION TERMINADA !!!", "i": "Puedes cargar las pólizas a la contabilidad." } );
                    var nuevaFila = $("<tr>")
                                    .append($("<td>").text("¡¡¡ REVISION TERMINADA !!!").css("color", "green"))
                                    .append($("<td>").text("Puedes cargar las pólizas a la contabilidad.").css("color", "green"));
                    $("#tabla-cargaMasiva").find("tbody").append(nuevaFila);

                    $("#revisar").attr("style", "display: none");
                    $("#cargar").attr("style", "visibility: visible");
                    try { window.espera.stop(); } catch(err) { /* nada */ };
                ');
        }
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Cargamos a la base de datos la información de pólizas ya validadas
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function cargar()
    {
        // Obtenemos el id de carga temporal
        $tc = $_POST['tc'];

        // Insertamos en el MAESTRO
        // -----------------------------------------------------------------------------------
        $conexion = new AccesoDatos();
        $conexion->conectar();

        // $conexion->setSql('START TRANSACTION;' );
        // $conexion->consultar();

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1")
        ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';

        $conexion->setSql('SET @usr_ip = INET_ATON( "' . $ip . '" )');
        $conexion->consultar();
        $conexion->setSql('SET @usr_id = ' . $_SESSION[_S]['usr_id']);
        $conexion->consultar();
        $conexion->setSql('SET @bit_observacion = "CARGA MASIVA"');
        $conexion->consultar();

        // Se insertan importes en ceros porque un trigger los calcula
        $conexion->setSql("
            INSERT INTO cg_PolizaMaestro(
                gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_debe, pma_haber, pma_concepto, pma_fecha, epl_id, mmc_id, apg_id, tem_pma_id, tem_carga, usr_id )
            SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, 0, 0, pma_concepto, pma_fecha, epl_id, 'V', apg_id, pma_id, tem_carga, usr_id
            FROM ctrl" . $this->ejercicioActual . "Temporal.tem_PolizaMaestro
            WHERE gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . "
                AND ur_id = " . $_SESSION[_S]['clasAdm-ur'] . "
                AND tem_carga = '" . $tc . "'
                AND pma_id <> 99999 AND epl_id <> 'C'
        ");
        $conexion->consultar();

        $insertados = $conexion->getNumeroModificados();

        // Ya que el maestro tiene el número definitivo, ponemos ése número definitivo en las tablas
        // temporales, para que los siguientes INSERT a tablas definitivas ya lo lleven
        $conexion->setSql("
            UPDATE ctrl" . $this->ejercicioActual . "Temporal.tem_PolizaDetalleAuxiliar da
            JOIN cg_PolizaMaestro m ON
            ( da.gpo_id = m.gpo_id AND da.ur_id = m.ur_id AND da.ue_id = m.ue_id AND da.msc_id = m.msc_id AND da.pot_id = m.pot_id AND da.prf_id = m.prf_id
            AND da.pma_id = m.tem_pma_id AND da.tem_carga = m.tem_carga )
            SET da.tem_pma = m.pma_id
            WHERE da.gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND
                da.ur_id = " . $_SESSION[_S]['clasAdm-ur'] . " AND
                da.tem_carga = '" . $tc . "';
        ");
        $conexion->consultar();

        $conexion->setSql('SET @usr_ip = INET_ATON( "' . $ip . '" )');
        $conexion->consultar();
        $conexion->setSql('SET @usr_id = ' . $_SESSION[_S]['usr_id']);
        $conexion->consultar();
        $conexion->setSql('SET @bit_observacion = "CARGA MASIVA"');
        $conexion->consultar();

        // Insertamos en el DETALLE para aquellas que no empiecen con 82 ... ya que esas son de orden presupuestal y van en otra tabla :D
        // -----------------------------------------------------------------------------------
        $conexion->setSql("
            INSERT INTO cg_PolizaDetalleAuxiliar(
                gpo_id, ur_id, ue_id,pot_id, prf_id, msc_id, pma_id, pdt_id,
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id,
                pdx_debe, pdx_haber, nat_id,
                pdx_concepto, rfc_id, pdx_referencia, pdx_fechaReferencia, tdf_id, adf_id,
                ecf_id, pdx_gpo_id_origen, pdx_ur_id_origen,
                pdx_clc_id_origen, pdx_afe_id_origen, pdx_bdo_id_origen,
                tem_carga, usr_id
            )
            SELECT gpo_id, ur_id, ue_id,pot_id, prf_id, msc_id, tem_pma, tem_pdt,
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id,
                pdx_debe, pdx_haber, nat_id,
                pdx_concepto, rfc_id, pdx_referencia, CASE pdx_fechaReferencia WHEN 0000-00-00 THEN NULL ELSE pdx_fechaReferencia END AS pdx_fechaReferencia, tdf_id, adf_id,
                ecf_id, pdx_gpo_id_origen, pdx_ur_id_origen,
                pdx_clc_id_origen, pdx_afe_id_origen, pdx_bdo_id_origen,
                tem_carga, usr_id
            FROM ctrl" . $this->ejercicioActual . "Temporal.tem_PolizaDetalleAuxiliar
            WHERE gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND ur_id = " . $_SESSION[_S]['clasAdm-ur'] . " AND tem_carga = '" . $tc . "'
        ");
        $conexion->consultar();
        $conexion->desconectar();

        $this->mixScript('
            alertify.alert("CARGA MASIVA TERMINADA", "Se cargaron ' . number_format($insertados) . ' pólizas");
            comunica("scg/poliza/ControladorCargaPoliza.php", {});
            try { window.espera.stop(); } catch(err) { /* nada */ };
        ');
    }

    // Obtenemos el bdo_id de un documento... y si no existe regresamos NULL
    private function dameBdoId($tdf_id, $bdo_referencia, $rfc_id)
    {
        $id = "NULL";
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT bdo_id
                FROM cat_BancoDocumentos
                WHERE
                    gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND
                    ur_id = " . $_SESSION[_S]['clasAdm-ur'] . " AND
                    tdf_id = '" . $tdf_id . "' AND
                    bdo_referencia = '" . $bdo_referencia . "' AND
                    rfc_id = '" . $rfc_id . "'
                ;");
        $conexion->consultar();
        $conexion->desconectar();

        $numRegistros = $conexion->getNumeroRegistros();

        // Si encontramos algo ...
        if ($numRegistros != 0) {
            $row = $conexion->extraer();
            $id = $row['bdo_id'];
        }

        return $id;
    }

    // Validamos que exista un bdo_id en la base de datos
    // Regresa TRUE si el bdo_id existe para esa UR... FALSE si no
    private function validaBdoId($gpo_id, $ur_id, $bdo_id)
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("
            SELECT bdo_id FROM cat_BancoDocumentos
            WHERE
            gpo_id = " . $gpo_id . " 
            AND ur_id = " . $ur_id . " 
            AND bdo_id = '" . $bdo_id . "'
        ;");

        $conexion->consultar();
        $conexion->desconectar();
        $numRegistros = $conexion->getNumeroRegistros();

        return ($numRegistros != 0);
    }

    // Obtenemos el ecf_id para determinar el ID de una clave de financiamiento dada
    private function dameEcf_id($cf)
    {
        $id = "NULL";

        // Para no gastar tiempo... si por casualidad (error) llega hasta aquí y viene
        // vacío, no hacemos búsqueda en la base de datos
        if (trim($cf) == "") {
            return $id;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("
            SELECT ecf_id
            FROM sinpres.enc_ClaveFinanciamiento
            WHERE
                ecf_clave = '" . $cf . "';
        ");
        $conexion->consultar();
        $conexion->desconectar();
        $numRegistros = $conexion->getNumeroRegistros();

        // Si encontramos algo ...
        if ($numRegistros != 0) {
            $row = $conexion->extraer();
            $id = $row['ecf_id'];
        }
        return $id;
    }

    // Si la UR existe, regresamos el valor original, si no existe, regresamos una cadena vacía
    private function validaUR($ur)
    {
        $gpo_id = substr($ur, 0, 1);
        $ur_id = substr($ur, 1, 2);

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("
            SELECT count(*) AS veces
            FROM cat_UnidadResponsable
            WHERE
            gpo_id = " . $gpo_id . " AND
            ur_id  = " . $ur_id . ";
        ");
        $conexion->consultar();
        $conexion->desconectar();

        $row = $conexion->extraer();
        if ($row['veces'] > 0) {
            return $ur;
        } else {
            return "";
        }
    }
}
new ControladorCargaPoliza();
