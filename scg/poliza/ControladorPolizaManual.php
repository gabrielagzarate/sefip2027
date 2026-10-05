<?php

/**
 * Captura manual de una póliza.
 * Permitir pre-cargar desde la tabla temporal una póliza buscándola por su id temporal.
 * Permitir pre-cargar desde la contabilidad cualquier póliza buscándola por su número de póliza y mes
 * Debe limitar las cuentas que pueden usar, de acuerdo a las definiciones de contabilidad
 * Debe -mediante clave- permitirles capturar todas las cuentas, para excepciones
 */

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';

class ControladorPolizaManual extends WebResponse
{
    private const PANTALLA_LIBRO_DIARIO = 'LibroDiario';
    private const PANTALLA_BANCO_DOCUMENTOS = 'bancoDocumentos';
    private const PREFIJO_TEMPORAL = '.tem_';

    private $lectura;
    private $escritura;
    private $ejercicioActual;
    private $esFondos;
    //Constructor
    public function __construct()
    {
        // Determinamos si el usuario actual tiene permisos de lectura / escritura en la base de datos
        $this->lectura = isset($_SESSION[_S]['prm']['C']['3']['1']['1']) ? 1 : 0;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['3']['1']['2']) ? 1 : 0;

        // Traemos el ejercicio actual de las variables de sesión para no tener que volver a cambiar ejercicio después.
        $this->ejercicioActual = $_SESSION[_S]['ejercicioActual'];

        // Marcos: 03/jul/2017
        // Para saber rápidamente si es la contabilidad del subsistema de fondos ( 116 005 )
        $this->esFondos = false;
        $pasoUR = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        if ($pasoUR == '116') {
            // Si no tiene UE definida, o si tiene definida la UE 005, entonces sí se toma como fondos
            if (!(isset($_SESSION[_S]['clasAdm-ue']))) {
                $this->esFondos = true;
                // o si tiene definida la UE 005, entonces sí se toma como fondos
            } else {
                if ($_SESSION[_S]['clasAdm-ue'] == '005') {
                    $this->esFondos = true;
                }
            }
        }

        parent::__construct();

        // Si no tiene permisos de escritura, no puede dar de alta pólizas y simplemente les avisamos
        if (!$this->escritura) {
            $this->sinPermisos();
        } else {
            switch ($this->metodo) {
                case 'guardarEncabezadoPoliza':
                    $this->guardarEncabezadoPoliza();
                    break;
                case 'guardarLineaPoliza':
                    $this->guardarLineaPoliza();
                    break;
                case 'guardarPoliza':
                    $this->guardarPoliza();   // Grabación Final de la Póliza
                    break;
                case 'eliminarLinea':
                    $this->eliminarLinea();
                    break;
                case 'modificarLinea':
                    $this->modificarLinea();
                    break;
                case 'actualizarEncabezado':
                    $this->actualizarEncabezado();
                    break;
                case 'SeleccionaTabulador':
                    $this->seleccionaTabulador();
                    break;
                case 'dibujaSeccionEncabezado':
                    $this->dibujaSeccionEncabezado();
                    break;
                    // case 'dibujaSeccionDetalle':
                    //     $this->dibujaSeccionDetalle();
                    //break;
                case 'ValidaCuenta':
                    $this->ValidaCuenta();
                    break;
                case 'cargaPoliza':
                    $this->cargaPoliza();
                    break;
                case 'cargaPolizaTemporal':
                    $this->cargaPolizaTemporal();
                    break;
                case 'dameTotales':
                    $this->dameTotales();
                    break;
                case 'obtenerDetallePolizaTemporal':
                    $this->obtenerDetallePolizaTemporal();
                    break;
                case 'buscaPipet':
                    $this->buscaPipet();
                    break;
                default:
                    $this->main();
                    break;
            }
        }
        $this->returnJson();
    }

    private function main()
    {

        // DETERMINAMOS SI VIENE DESDE EL LIBRO DIARIO (Y POR LO TANTO ES UN CAMBIO)
        $msc_id = $this->getPrm('m', '');
        $pot_id = $this->getPrm('p', '');
        $prf_id = $this->getPrm('r', '');
        $numero = $this->getPrm('n', '');
        $ue = $this->getPrm('ue', '');
        // Si es modificación, saber qué pantalla lo llamó para actualizar su visualización al terminar la edición
        $pantallaOrigen = (string) $this->getPrm('ori', '');

        // Si trae mes es un cambio .. entonces primero verificamos que el mes esté abierto ...
        if ($msc_id != '') {

            // Marcos: 08/abr/2015
            // Por el filtro en el libro diario por clc, afectacion, etc., es probable que el
            // mes de la línea de la póliza sea diferente al mes en el que están trabajando (combo)
            $mesPolizaActual = ((isset($this->prm->mpa) && $this->prm->mpa != '')
                ? $this->prm->mpa : '99');  // Numero inexistente de mes en BD para cuando venga vacío y así nunca lo encuentre
            // Si el mes de la línea de la póliza es diferente al que tienen seleccionado en
            // el combo, el de la línea toma prioridad
            if ($mesPolizaActual != 99 && $mesPolizaActual != $msc_id) {
                $msc_id = $mesPolizaActual;
            }

            $params = [
                getSession('clasAdm-gpo'),
                getSession('clasAdm-ur'),
                $msc_id,
            ];
            $this->conectar();
            $this->setSql('SELECT ems_id FROM cg_MesContable
                    WHERE gpo_id = ? and ur_id = ? AND msc_id = ? ;');
            $this->consultar($params);
            $this->desconectar();
            $row = $this->extraer();

            if ($row['ems_id'] != 'A') {
                $this->mixScript('
                        notify( "El mes de ' . MesNombre($msc_id) . ' está cerrado", "warning" );
                    ');
                return false;
            }
        }


        // Si pasan un parámetro de altaLD ... es un alta desde el libro diario
        // Se comporta igual que una captura manual, pero con el botón regresar en lugar de Menu
        $altaLD = (isset($this->prm->altaLD) && $this->prm->altaLD === 'true');

        $bolEsAlta = ($pot_id === '' && $prf_id === '' && $numero === '');

        // Si es un alta, averiguamos la UE que usaremos
        if ($bolEsAlta) {
            // Determinamos la UE que se usara: Si es un usuario de nivel UE usaremos la UE asignada... si no usaremos la UE contable de la UR.
            $ue = '';
            if (
                isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue']
                != ''
            ) {
                $ue = $_SESSION[_S]['clasAdm-ue'];
            } else {
                $this->setSql('SELECT ur_ueContable FROM cat_UnidadResponsable WHERE gpo_id = ? AND ur_id = ?;');
                $this->conectar();
                $this->consultar([
                    getSession('clasAdm-gpo'),
                    getSession('clasAdm-ur'),
                ]);
                $this->desconectar();
                $row = $this->extraer();
                $ue = $row['ur_ueContable'];
            }
            // Si es un cambio, ya nos la pasan desde la llamada
        }

        // Para ajustar un poco los espacios ... porque los veo muy separados
        $css = <<<CSS
            .ui-widget-overlay {
                background: #aaaaaa url(images/ui-bg_flat_0_aaaaaa_40x100.png) 50% 50% repeat-x !important;
                opacity: 100 !important;
                filter:Alpha(Opacity=100) !important;
            }
            .form-group{margin-bottom:12px!important}
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .form-control:focus { border-color: rgba(82, 168, 236, 1); box-shadow: 0 1px 1px }
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:white; opacity: 0.8;};
        CSS;
        $this->mixstyle($css);

        // Tabuladores
        $this->mixHtml('

                            <span style="height:6px; display:block"></span>

                            <div id="page-tabs"><ul class="nav nav-tabs">
                                <li class="nav-item"><a class="nav-link active" href="1"> Encabezado </a></li>
                                <li class="nav-item me-auto"><a class="nav-link" href="2">Detalle</a></li>

                                <input type="text" class="form-control soloDetalle me-1" style="width:12em; text-align: right; height: 95%; display:none;" id="sumaDebe" name="sumaDebe"  disabled value="" style="text-align:right"  placeholder="Suma Debe" />
                                <input type="text" class="form-control soloDetalle" style="width:12em; text-align: right; height: 95%; display:none;" id="sumaHaber" name="sumaHaber" disabled value="" style="text-align:right" placeholder="Suma Haber"/>

                                </ul>
                            </div>
                        </div>

                        <div id="EncabezadoPoliza" class="mb-4" style="overflow-y: auto; overflow-x: hidden; visibility: none;"></div>
                        <div id="DetallePoliza"    style="overflow-y: auto; overflow-x: hidden;"></div>

            ');

        switch ($pantallaOrigen) {
            case self::PANTALLA_LIBRO_DIARIO:
                $tipo = 'button';
                $hidden = '';
                $class = 'btn btn-outline-secondary me-2';
                $i = '<i class="fa-solid fa-circle-left me-2"></i>Regresar';
                break;
            default:
                $tipo = 'input';
                $hidden = 'hidden';
                $class = '';
                $i = '';
                break;
        }

        $botones = '
                <div>
                            <' . $tipo . ' id="homeMenuPM" type="' . $hidden . '" class="' . $class . '">
                                ' . $i . '
                            </' . $tipo . '>
                    ' . (($bolEsAlta) ? '
                            <button id="recuperarPoliza" title="Recupera una póliza temporal" class="btn btn-outline-warning me-2"><i class="fa fa-undo me-2"></i>Recuperar Póliza</button>
                            <button id="grabarPoliza" title="Registrar la Póliza" class="btn btn-outline-success me-2 ladda-button" style="display:none"><i class="fa fa-file me-2"></i>Grabar Póliza</button>' : '') . '
                            <button id="eliminarLinea" title="Eliminar la línea seleccionada" class="btn btn-outline-danger me-2" style="display:none"><i class="fa fa-trash me-1"></i></button>
                            <button id="modificarLinea" title="Modificar la línea seleccionada" class="btn btn-outline-secondary me-2" style="display:none"><i class="fa fa-pencil me-1"></i></button>
                            <button id="adicionarLinea" title="Adicionar una nueva línea" class="btn btn-outline-success me-2"  style="display:none"><i class="fa fa-plus"></i></button>
                            <button id="esFlujoEfectivo" style="float: right; display:none;" class="btn btn-outline-lime me-2 disabled">Flujo de Efectivo</button>
                            <button id="idPolizaTemporal" style="float: right; display:none;" class="btn btn-outline-danger me-2 disabled"># Temporal</button>
                            <button id="EsChequeCancelado" style="float: right; display:none;" class="btn btn-outline-danger me-2 disabled">Cheque Cancelado</button>
                            <input type="hidden" id="idPip" name="idPip" />
                            <input type="hidden" id="totalPip" name="totalPip" />
                </div>
            ';

        $this->agregaTools($botones);


        // Dibujamos la pantalla que permite buscar un documennto
        // --------------------------------------------------------------------------------------------------------------
        $this->mixHtml(DibujaPantallaBuscarDocumentoHTML());
        $this->mixScript(DibujaPantallaBuscarDocumentoJS());
        // Dibujamos la pantalla que permite buscar una cuenta
        // --------------------------------------------------------------------------------------------------------------
        $this->mixHtml(DibujaPantallaBuscarCuentaHTML());
        $this->mixScript(DibujaPantallaBuscarCuentaJS());

        // Dibujamos la pantalla que permite buscar un proveedor
        // --------------------------------------------------------------------------------------------------------------
        $this->mixHtml(DibujaPantallaBuscarProveedorHTML());
        $this->mixScript(DibujaPantallaBuscarProveedorJS());

        // Dibujamos la pantalla que permite buscar y seleccionar Claves de Financiamiento
        // --------------------------------------------------------------------------------------------------------------
        $this->mixHtml(DibujaPantallaBuscarCgClaveFinaciamientoHTML(
            false,
            $this->esFondos
        ));
        $this->mixScript(DibujaPantallaBuscarCgClaveFinaciamientoJS());

        $this->mixScript('
                // Grabamos la UE que usaremos ...
                $("#EncabezadoPoliza").attr( "ue", "' . $ue . '" );
            ');

        // Armamos el código para el botón regresar cueando se llama desde otra consulta (Libro Diario por ejemplo)
        $jsOrigen = json_encode((string) $pantallaOrigen);
        $jsLibroDiario = json_encode(self::PANTALLA_LIBRO_DIARIO);
        $jsBancoDocumentos = json_encode(self::PANTALLA_BANCO_DOCUMENTOS);
        $strActualizaOrigen = '';
        $jsIf = static function ($condicion, $cuerpo) {
            return 'if ( ' . $condicion . ' ) { ' . $cuerpo . ' }';
        };

        if (!$altaLD) {
            if ($pantallaOrigen == self::PANTALLA_LIBRO_DIARIO) {
                $strActualizaOrigen = ' $("#mostrar2").trigger("click"); ';
            }
            if ($pantallaOrigen == self::PANTALLA_BANCO_DOCUMENTOS) {
                $strActualizaOrigen = '$("#bdPantallaCompleta").show();';
            } else {
                $strActualizaOrigen = '/* de momento nada */';
            }
            // Si capturaron una nueva póliza desde una consulta ... volvemos a cargar dicha consulta por si la nueva póliza
            // entra dentro de su rango de visualización
        } elseif ($altaLD) {
            $strActualizaOrigen = $jsIf(
                $jsOrigen . ' == ' . $jsLibroDiario,
                '$("#mostrar2").click();'
            );
        }

        $jsActualizaSiConsulta = $jsIf(
            $jsOrigen . ' == ' . $jsLibroDiario . ' || ' . $jsOrigen . ' == ' . $jsBancoDocumentos,
            $strActualizaOrigen
        );
        $jsMuestraNavbar = $jsIf(
            $jsOrigen . ' != ' . $jsBancoDocumentos,
            '$(".navbar").show();'
        );

        // Para indicar que es un Alta ...
        if ($bolEsAlta) {
            $this->mixScript('
                    // Para indicar que es un ALta
                    $("#homeMenuPM").attr( "op", "A" );
                ');
        }


        // Si es un alta, el menú regresar vuelve a la pantalla principal
        // Pero si es un alta desde el libro diario ... entonces no lo hacemos
        if ($bolEsAlta && !$altaLD) {
            $this->mixScript('
                    $(document).on("click", "#homeMenuPM", function (e) {
                        e.preventDefault();
                        $("nav").show();
                        $(".navbar").show();
                        $(".modulos").show();
                        $("#sticky-anchor").hide();
                        $("#sticky").hide();
                        ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
                            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                        $("#mainContainer").css( "margin-top","40px" );

                        // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                        $("div[aria-describedby=cargaDatosCuentas]").html("");
                        $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                        $("div[aria-describedby=cargaDatosProveedor]").html("");
                            $("div[aria-describedby=cargaDatosProgFin]").html("");
                            $("div[aria-describedby=cargaDatosDocumentos]").html("");

                        $("#ayuda").addClass("gris");
                        $("#tip").addClass("gris");

                        $("#contacto").text( $("#contacto").attr("original") );

                        $("#cgPantallaCompleta").empty();
                        $("#cgPantallaCompleta").html("");
                        $("#cgPantallaCompleta").hide();

                        $("nav").menusinpres("clickMenu");

                        $(document).off("click", "#homeMenuPM" );
                        ' . $jsActualizaSiConsulta . '
                        $("#cargaDatosCgCveFin").parent().remove();
                        switch("' . $pantallaOrigen . '"){
                            case "' . self::PANTALLA_LIBRO_DIARIO . '":
                                comunica("scg/consulta/Controlador' . $pantallaOrigen . '.php", {
                                    metodo: "main",
                                    mesActual: "",
                                    clc : "",
                                    n:"",
                                    t:"",
                                    manual : "no"
                                });
                            break;
                        }
                        return false;
                    });

                    // Para indicar el grid desde el que se llamó la edición de pólizas (en la ALTA queda vacío)
                    $("#homeMenuPM").attr( "gO", "" );

                    // Para recuperar una póliza
                    $("#recuperarPoliza").click( function(e) {
                        alertify.prompt("Captura Póliza Manual","Escribe el número de póliza TEMPORAL a cargar",
                            "",
                            function(evt, numPol) {
                                if (numPol == ""){
                                    return false;
                                } else {
                                    if (numPol !== null) {

                                        numPol = numPol.replace( "-", "" ).toUpperCase();
                                        tipo = numPol.substring( 0, 1 );
                                        prefijo = numPol.substring( 1, 2).toString();
                                        numero = numPol.substring( 2 );
                                        // Revisamos que el tipo sea D, I o E
                                        tiposValidos = "DIE";

                                        if ( tiposValidos.indexOf( tipo ) < 0 ) {
                                            notify( "Los tipos de póliza válidos son: D, E, I", "warning" );
                                            return false;
                                        }
                                        // checamos que el número sea ... númerico ...
                                        if ( isNaN( parseInt( numero ) ) ) {
                                            notify( "El número de póliza TEMPORAL (" + numPol + ") está mal escrito", "warning" );
                                            return false;
                                        // Si pasó ... entonces intentamos cargarla
                                        } else {
                                            comunica("scg/poliza/ControladorPolizaManual.php", { metodo : "cargaPolizaTemporal",
                                                p : tipo,
                                                r : prefijo,
                                                n : numero,
                                                ue: $("#EncabezadoPoliza").attr("ue")
                                            });
                                        }
                                    }
                                }
                            }, undefined
                        ).set("labels", {ok: "Aceptar", cancel: "Cancelar"});

                    });
                ');
            // Si es un cambio, sólo cerramos la ventana
        } else {
            $this->mixScript('
                    $(document).on("click", "#homeMenuPM", function (e) {
                        console.log("homeMenuPM");
                        // Si la póliza no está cuadrada, pedimos confirmación
                        if ( ( $("#sumaDebe").val() != $("#sumaHaber").val() ) && $("#chequeC").prop("checked") == false  ) {
                            console.log("shoul be an aler");
                            alertify.confirm("POLIZA NO CUADRA",
                                "La póliza no está cuadrada<br /><br />¿ Deseas salir de la Póliza ?",
                                function() {
                                    ' . $jsMuestraNavbar . '

                                        // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                                        $("div[aria-describedby=cargaDatosCuentas]").html("");
                                        $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                                        $("div[aria-describedby=cargaDatosProveedor]").html("");
                                        $("div[aria-describedby=cargaDatosProgFin]").html("");
                                        $("div[aria-describedby=cargaDatosDocumentos]").html("");

                                        $(document).off("click", "#homeMenuPM" );
                                        $("#cargaDatosCgCveFin").parent().remove();
                                        // Dependiendo de la pantalla que llamó a la edición de la póliza, actualizamos su registro en pantalla
                                        ' . $jsActualizaSiConsulta . '
                                        $("#cgPantallaCompleta").empty();
                                        $("#cgPantallaCompleta").html("");
                                        $("#cgPantallaCompleta").hide();

                                        /********************************/
                                        switch("' . $pantallaOrigen . '"){
                                            case "' . self::PANTALLA_LIBRO_DIARIO . '":
                                                comunica("scg/consulta/Controlador' . $pantallaOrigen . '.php", {
                                                    metodo: "main",
                                                    mesActual: "' . $msc_id . '",
                                                    clc : "",
                                                    n:"' . $this->prm->n . '",
                                                    t:"tp_' . $this->prm->p . '",
                                                    manual : "si"
                                                });
                                            break;
                                        }
                                },
                                undefined
                            ).set("labels", {ok: "Regresar", cancel: "Cancelar"});

                        } else {
                            ' . $jsMuestraNavbar . '

                            // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                            $("div[aria-describedby=cargaDatosCuentas]").html("");
                            $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                            $("div[aria-describedby=cargaDatosProveedor]").html("");
                            $("div[aria-describedby=cargaDatosProgFin]").html("");
                            $("div[aria-describedby=cargaDatosDocumentos]").html("");

                            $(document).off("click", "#homeMenuPM" );
                            $("#cargaDatosCgCveFin").parent().remove();
                            // Dependiendo de la pantalla que llamó a la edición de la póliza, actualizamos su registro en pantalla
                            ' . $jsActualizaSiConsulta . '
                            $("#cgPantallaCompleta").empty();
                            $("#cgPantallaCompleta").html("");
                            $("#cgPantallaCompleta").hide();

                            /********************************/
                            switch("' . $pantallaOrigen . '"){
                                case "' . self::PANTALLA_LIBRO_DIARIO . '":
                                    comunica("scg/consulta/Controlador' . $pantallaOrigen . '.php", {
                                        metodo: "main",
                                        mesActual: "' . $msc_id . '",
                                        clc : "",
                                        n:"' . $this->prm->n . '",
                                        t:"tp_' . $this->prm->p . '",
                                        manual : "si"
                                    });
                                break;
                            }
                        }
                    });
                ');
        }


        // Marcos, 16/junio/2015
        // Las llamadas que se hacían antes con otro comunica() ahora las haremos directamente desde aquí
        // para disminuir las llamadas al servidor y el uso de internet, ya que en URs donde la velocidad
        // está del nabo, no dibuja bien la sección de detalle de la póliza
        $this->dibujaSeccionEncabezado((($bolEsAlta) ? 'si' : 'no'));
        $this->DibujaSeccionDetalle(
            $ue,
            (($bolEsAlta) ? 'si' : 'no'),
            ((!$bolEsAlta) ? $pot_id : ''),
            ((!$bolEsAlta) ? $prf_id : ''),
            ((!$bolEsAlta) ? $msc_id : ''),
            ((!$bolEsAlta) ? $numero : '')
        );

        $this->mixScript('
                $(".navbar").hide();
                $(".tabla-footer").hide();

                $("#page-tabs a").click( function(e) {
                    e.preventDefault();
                    ' . (($bolEsAlta) ? '
            // Si quieren seleccionar la pestaña de detalle pero aún no graban el encabezado, avisamos
                    if ( $(this).attr("href") == "2" ) {
                        if( $("#grabarEncabezado").css("display") !== "none") {
                            notify("Debes grabar el encabezado de la póliza para pasar a su Detalle","warning");
                            return false;
                        }
                    }' : '') . '
                    $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                    $(this).closest("a").addClass("active");
                    comunica("scg/poliza/ControladorPolizaManual.php",{ metodo : "SeleccionaTabulador", "seccion" : $(this).attr("href") });
                    return false;
                });

                $("#homeMenuPM").attr("ue", "' . $ue . '");

            ');


        // Después de preparar la pantalla ... si es un cambio ... cargamos los datos de la póliza
        // --------------------------------------------------------------------------------------------------------------
        $this->mixScript('
                   $("#registroCtas tbody").empty();
                    $("#homeMenuPM").prop("md", "1")
                    $("#divEdicionLineaPoliza").hide();

                    // Buscamos un LibroDiario en la pantalla de selección
                    // ===========================================================================
                    function CargaDetallePoliza( _pot, _prf, _num, _mes, _ue, _epl ) {
                        //
                        const tbody = $("#registroCtas tbody");

                        dataDetallePoliza = [];
                        var c = 1;
                        $.ajax({
                            data : { "p": _pot, "r": _prf, "n": _num, "m": _mes, "ue": _ue },
                            url:  "lib/scg/poliza/dameDetallePoliza.php",
                            type: "post",
                            success: function ( response ) {
                                var json = $.parseJSON(response);
                                for (var i = 0; i < json.length; i++) {
                                    //
                                    const formatoMoneda = new Intl.NumberFormat("es-MX", {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });

                                    var d = (dataDetallePoliza[i] = {});
                                        cta = json[i].cta;
                                        nom = json[i].nom;
                                        d =   (json[i].d == 0) ? "" : formatoMoneda.format(json[i].d);
                                        h =   (json[i].h == 0) ? "" : formatoMoneda.format(json[i].h);
                                        df =  json[i].df;
                                        nL =  json[i].nL;
                                        ue =  json[i].ue;

                                        tbody.append(
                                            "<tr class=\'seleccionable\'><td style = \' text-align: center; \'>"
                                                + c + "</td><td style = \' white-space: pre-wrap\'>"
                                                + cta + "</td><td style = \' white-space: pre-wrap\'>"
                                                + nom + "</td><td style = \' text-align: right; white-space: pre-wrap\'>"
                                                + d + "</td><td style = \' text-align: right; white-space: pre-wrap\'>"
                                                + h + "</td><td>"
                                                + df + "</td><td style = \' display:none;\'>"
                                                + nL + "</td><td style = \' display:none;\'>"
                                                + ue + "</td></tr>");
                                    c++;
                                }

                                /********************************/
                                $(document).ready(function (){
                                    $(".seleccionable").click(function() {
                                        $(".seleccionable").css("font-weight","normal");
                                        $(".seleccionable").removeClass("seleccionado");
                                        $(this).addClass("seleccionado");
                                        $(this).css("font-weight","bold");

                                        $(".seleccionado").dblclick(function(){
                                            $("#modificarLinea").click();
                                            $("#bdo_Clc").val("");
                                            $("#bdo_CereCo").val("");
                                            //Recalculamos la suma del debe y del haber
                                            recalcularSumas( 1 );
                                        });

                                    });

                                    $("#registroCtas tbody tr").hover(
                                        function() {
                                            $(this).addClass("coloreaCtas");
                                        },
                                        function() {
                                            $(this).removeClass("coloreaCtas");
                                        }
                                    );
                                });
                            }
                        });
                    };

                ');
    }

    // Activamos la seccion de captura de póliza que hayan seleccionado en el tab
    // ----------------------------------------------------------------------------------------------------------------
    private function seleccionaTabulador($strSeccion = 'nada')
    {

        // Si no pasan en la funcion el valor, viene desde un comunica()
        if ($strSeccion == 'nada') {
            $seccion = (isset($this->prm->seccion) ? $this->prm->seccion : '1');
        } else {
            $seccion = (isset($strSeccion) ? $strSeccion : '1');
        }


        if ($seccion == '1') {
            $this->mixScript('
                setTimeout( function() { $("#EncabezadoPoliza").height( $(window).height() - $("#areaBotones").height() - 95 ) }, 100 );
                $("#EncabezadoPoliza").attr("style", "visibility: visible");
                $("#DetallePoliza").attr("style", "display: none");
                $("#eliminarLinea").attr("style", "display: none");
                $("#modificarLinea").attr("style", "display: none");
                $("#adicionarLinea").attr("style", "display: none");
                $("#sumaDebe").css("display", "none" );
                $("#sumaHaber").css("display", "none" );
                ');
        } else {
            $this->mixScript('
                // Sólo permitimos ver el detalle una vez grabado los datos del encabezado
                    setTimeout( function() {
                        $("#DetallePoliza").height( $(window).height() - $("#areaBotones").height() - 95 )
                    }, 100 );
                    $("#eliminarLinea").attr("style", "visibility: visible");
                    $("#modificarLinea").attr("style", "visibility: visible");
                    if ( $("#divEdicionLineaPoliza").css("display") == "none" ) {
                        $("#adicionarLinea").click();

                    } else {
                        $("#adicionarLinea").attr("style", "display: none");
                    }
                    $("#DetallePoliza").attr("style", "visibility: visible");
                    $("#EncabezadoPoliza").attr("style", "display: none");
                    $("#myGridDetallePoliza").height( $(window).height() - $("#areaBotones").height() - $("#divEdicionLineaPoliza").height() - $("#page-tabs").height() - 115 );
                    $("#sumaDebe").css("display", "inline" );
                    $("#sumaHaber").css("display", "inline" );
                    setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                    // Recalculamos la suma del debe y del haber
                    recalcularSumas( 0 );
            ');
        }
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Dibujamos la pantalla del encabezado de la póliza
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function dibujaSeccionEncabezado($strAlta = null)
    {

        // Parámetro que indica si es un alta o un cambio ...
        $bolEsAlta = ((isset($strAlta) && $strAlta == 'si') ? true : false);

        $this->setContenedor('EncabezadoPoliza');
        $fecha = new stdClass();
        $fecha->id = 'fecha';
        $fecha->name = 'control';
        $fecha->readonly = true;
        $fecha->class = 'fecha';
        $fecha->placeholder = 'Fecha';
        $fecha->required = true;
        $fecha->formato = 'fecha';
        $fecha->labelClass = '_32 txtL';
        $fecha->enDiv = true;
        // Para un cambio no necesitamos saber si está abierto o cerrado ... de hecho si está cerrado el mes
        // jamás deberíamos llegar hasta aquí ...
        if ($bolEsAlta) {
            // Determinamos los meses abiertos (para poder capturar una póliza manual, el mes DEBE estar abierto)
            $this->conectar();
            $this->setSql('SELECT msc_id FROM cg_MesContable
                WHERE gpo_id = ? AND ur_id = ? AND ems_id = "A";');

            $this->consultar([getSession('clasAdm-gpo'), getSession('clasAdm-ur')]);
            $this->desconectar();
            $mesesAbiertos = '';

            while ($row = $this->extraer()) {
                $mesesAbiertos .= MesNombre($row['msc_id']) . ', ';
            }
            if ($mesesAbiertos <> '') {
                $mesesAbiertos = substr($mesesAbiertos, 0, -2);
            }
        }

        // Si es un alta ... cargamos las opciones en los combos ...
        // ------------------------------------------------------------------------------------------------------------------------
        // ------------------------------------------------------------------------------------------------------------------------
        if ($bolEsAlta) {
            $this->mixHtml('
            <br />
            <form action="" class="" id="formaEncabezadoPoliza">
                <div class="row">
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Agrupación</legend>
                        <select name="apg_id" id="apg_id" tabindex="105" class="form-select form-control">');

            // Marcos: 06/abr/2017
            // Para los nuevos registros en cat_AgrupacionPolizaGlobal, todas verán solamente GRAL
            // La 116 además verá SAP
            $whereapg = " WHERE apg_id = 'GRAL' OR apg_id = 'FE' ";
            if (claveClasAdm() == 116) {
                $whereapg .= " OR apg_id = 'SAP' ";
            }

            $this->conectar();
            $this->setSql('SELECT apg_id, apg_nombre FROM cat_AgrupacionPoliza ' . $whereapg);
            $this->consultar();
            $this->desconectar();
            $this->mixHtml('<option value="">-- Seleccione Agrupación --</option>');
            while ($row = $this->extraer()) {
                $background = $row['apg_id'] == 'FE' ? 'style="background-color: lime;"' : 'style="background-color: white;"';
                $this->mixHtml('<option value="' . $row['apg_id'] . '" ' . $background . '>' . $row['apg_id'] . ' - ' . $row['apg_nombre'] . '</option>');
            }

            $this->mixHtml('</select>
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Prefijo</legend>
                        <select name="prf_id" id="prf_id" tabindex="110" class="form-select form-control">');
            $this->conectar();
            $this->setSql('SELECT prf_id, prf_nombre FROM cat_PrefijoPoliza');
            $this->consultar();
            $this->desconectar();
            while ($row = $this->extraer()) {
                $this->mixHtml('<option value="' . $row['prf_id'] . '" >' . $row['prf_id'] . ' - ' . $row['prf_nombre'] . '</option>');
            }

            $this->mixHtml('</select>
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Elaboró</legend>
                        <input type="text" class="form-control"  placeholder="Nombre del Usuario que elaboró la póliza" value="' . $_SESSION[_S]['usu'] . '" disabled>
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Cheque Cancelado</legend>
                        <div class="form-check">
                            <input id="chequeC" class="form-check-input" type="checkbox" tabindex="115">
                            <label class="form-check-label" for="chequeC">Cheque Cancelado</label>
                        </div>
                    </fieldset>
                </div>
                <br />

                <div class="row mb-4">
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo Póliza</legend>
                        <select name="pot_id" id="pot_id" tabindex="120" class="form-select form-control">');
            $this->conectar();
            $this->setSql('SELECT pot_id, pot_nombre FROM cat_TipoPoliza');
            $this->consultar();
            $this->desconectar();
            $tdo = '';
            while ($row = $this->extraer()) {
                $this->mixHtml('<option value="' . $row['pot_id'] . '" >' . $row['pot_id'] . ' - ' . $row['pot_nombre'] . '</option>');
            }

            $this->mixHtml('</select>

                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Fecha</legend>
                        <div class="form-group ">' . $this->ponInput($fecha) . '</div>
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                        <legend>Meses Abiertos</legend>
                        <input type="text" class="form-control" value="' . $mesesAbiertos . '"disabled>
                    </fieldset>
                </div>

                <div class="row mb-4">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Descripción de la Póliza</legend>
                        <textarea name="concepto" id="concepto" rows="3" required class=" form-control " style="resize: none;" tabindex="140" ></textarea>
                    </fieldset>
                </div>

                <div class="right mb-4">
                    <button id="grabarEncabezado" class="btn btn-outline-success me-1 ladda-button" data-style="slide-left" style="float:right;" tabindex="150"><i class="fa-solid fa-plus me-2"></i>Grabar Encabezado</button>
                </div>

            </form>
            ');
            // Si es un cambio ... sólo mostraremos en ésta sección de encabezado los datos que ya trae la póliza
            // OJO: En ésta pantalla NO modificaremos los datos del encabezado
            // ------------------------------------------------------------------------------------------------------------------------
            // ------------------------------------------------------------------------------------------------------------------------
        } else {
            $this->mixHtml('
                <div id="polizaTemporalData"
                    data-t="' . $this->prm->r . '"
                    data-p="' . $this->prm->p . '"
                    data-n="' . $this->prm->n . '"
                    data-m="' . $this->prm->m . '"
                    style="display:none;">
                </div>
            ');
            $this->mixHtml('
            <form action="" class="mt-4" id="formaEncabezadoPoliza">
                <div class="row mb-4">
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Agrupación</legend>
                        <select name="apg_id" id="apg_id" tabindex="105" class="form-select form-control">');
            $whereapg = claveClasAdm() == 116 ? " OR apg_id = 'SAP' " : '';

            $this->conectar();
            $this->setSql("SELECT apg_id, apg_nombre FROM cat_AgrupacionPoliza WHERE apg_id = 'GRAL' OR apg_id = 'FE' $whereapg");
            $this->consultar();
            $this->desconectar();
            $this->mixHtml('<option value="">-- Seleccione Agrupación --</option>');
            while ($row = $this->extraer()) {
                $background = $row['apg_id'] == 'FE' ? 'style="background-color: lime;"' : 'style="background-color: white;"';
                $this->mixHtml('<option value="' . $row['apg_id'] . '" ' . $background . '>' . $row['apg_id'] . ' - ' . $row['apg_nombre'] . '</option>');
            }
            $this->mixHtml('
                        </select>
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Prefijo</legend>
                        <input type="text" name="prf_id" id="prf_id" class="form-control" value="" disabled>
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Elaboró</legend>
                        <input type="text" name="elaboro" id="elaboro" class="form-control"  value="" disabled>
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Cheque Cancelado</legend>
                        <div class="form-check">
                            <input id="chequeC" class="form-check-input" type="checkbox" disabled>
                            <label class="form-check-label" for="chequeC">Cheque Cancelado</label>
                        </div>
                    </fieldset>
                </div>
                <div class="row mb-4">
                    <fieldset class="col-md-2 col-xs-2">
                        <legend>Tipo Póliza</legend>
                        <input type="text" name="pot_id" id="pot_id" class="form-select form-control" value="" disabled>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        <legend>Fecha</legend>
                        <div class="form-group ">
                            <input name="fecha" id="fecha" type="text" class="form-control" disabled>
                        </div>
                    </fieldset>
                    <fieldset class="col-md-1 col-xs-1">
                        <legend>Día</legend>
                        <div class="form-group ">
                            <input name="dia" id="dia" type="text" maxlength="2"  class="form-control" tabindex="135">
                        </div>
                    </fieldset>

                </div>
                <div class="row mb-4">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Descripción de la Póliza</legend>
                        <textarea name="concepto" id="concepto" rows="3" class=" form-control " style="text-transform:none !important; resize: none;" tabindex="140"></textarea>
                    </fieldset>
                </div>

                <div class="right mb-4">
                    <button id="actualizarEncabezado" class="btn btn-outline-success me-1" style="float:right;" tabindex="150"><i class="fa fa-white me-2"></i>Actualizar Encabezado</button>
                </div>

            </form>
            ');
        }

        // El código JS es para controlar el registro .. y como aquí no permitiremos modificar los datos del
        // encabezado, no se utilizará
        // ------------------------------------------------------------------------------------------------------------------------
        // ------------------------------------------------------------------------------------------------------------------------
        if ($bolEsAlta) {
            $this->mixScript('
                $("#concepto").blur(function(e){
                    str=$("#concepto").val();
                    str=str.replace(/\n/gi," ");
                    $("#concepto").val(str);
                });
                $("#apg_id").change(function( e ){
                    $("#apg_id option[value=\'\']").remove();
                    e.preventDefault();
                    if($("#apg_id").val() == "FE") {
                        $("#apg_id").css("background-color","lime");
                        $("#esFlujoEfectivo").css("display", "inline");
                    }
                    else {
                        $("#apg_id").css("background-color","white");
                        $("#esFlujoEfectivo").css("display", "none");
                    }
                    return false;
                });
                $("#grabarEncabezado").click(function( e ){
                    e.preventDefault();

                    // VALIDACION
                    // Que hayan seleccionado agrupación
                    if ( $("#apg_id").val() == "" ) {
                        notify("Seleccione la agrupación de la póliza","warning");
                        setTimeout( function() { $( "#apg_id" ).focus() }, 100 );
                        return false;
                    }
                    // Que la fecha no esté vacía
                    if ( $("#fecha").val().trim() == "" ) {
                        notify("La fecha de la póliza no puede quedar vacía","warning");
                        setTimeout( function() { $( "#fecha" ).focus() }, 100 );
                        return false;
                    }
                    // El concepto no puede quedar vacío
                    if ( $("#concepto").val().trim() == "" ) {
                        notify("El concepto de la póliza no puede quedar vacío","warning");
                        setTimeout( function() { $( "#concepto" ).focus() }, 100 );
                        return false;
                    }

                    window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...

                    comunica( "scg/poliza/ControladorPolizaManual.php", {
                        "metodo" : "guardarEncabezadoPoliza",
                        "ue"  : $("#EncabezadoPoliza").attr("ue"),
                        "apg_id" : $("#apg_id").val(),
                        "prf_id" : $("#prf_id").val(),
                        "poe_id" : $("#poe_id").val(),
                        "chkCan" : $("#chequeC").prop("checked"),
                        "pot_id" : $("#pot_id").val(),
                        "fecha"  : $("#fecha").val(),
                        "descr"  : $("#concepto").val(),
                        "npTo"   : $("#idPolizaTemporal").attr("npTo")
                    });
                    e.stopPropagation();
                    return false;
                });
                $("#chequeC").change(function(e){
                    if ( $("#pot_id" ).val().substring( 0, 1 ) != "E" ) {
                        if($(this).is(":checked"))
                            notify("Solo las polizas de EGRESOS pueden ser de CHEQUE CANCELADO", "warning");
                        $("#chequeC").prop("checked", false);
                    } else {
                        if( $(this).is(":checked") ) {
                            $("#EsChequeCancelado").css("display", "inline" );
                        } else {
                            $("#EsChequeCancelado").css("display", "none" );
                        }
                    }
                });
                $("#pot_id").change(function(e){
                    /* Si no es de egresos, anulamos cheque cancelado si es que esta marcado */
                    if ( $("#pot_id" ).val() != "E" ) {
                        if( $("#chequeC").is(":checked") ) {
                            notify( "Solo las polizas de EGRESOS pueden ser de CHEQUE CANCELADO", "warning" );
                            $("#chequeC").prop("checked", false);
                            $("#EsChequeCancelado").css("display", "none" );
                        }
                    }
                });

                setTimeout( function() { $( "#pot_id" ).focus() }, 100 );

            ');
            // Si es un cambio ...
        } else {
            $this->mixScript('

                // $("#bdo_Clc").hide();

                setTimeout( function() { $( "#concepto" ).focus() }, 100 );

                function daysInMonth(month, year) {
                    return new Date(year, month, 0).getDate();
                }

                // Checamos que el día sea válido para el mes
                $("#dia").blur(function(e){
                    e.preventDefault();
                    // Permitimos el día vacío, que significa que simplemente NO quieren cambiarlo
                    if ( $("#dia").val().trim() != "" ) {
                        var d = $.datepicker.parseDate("dd-mm-yy", $("#fecha").val() );
                        var dias = daysInMonth( $.datepicker.formatDate( "m", d), $.datepicker.formatDate( "yy", d) );
                        if ( $.isNumeric( $("#dia").val() ) ) {
                            if ( parseInt(  $("#dia").val() ) > dias ) {
                                notify( "El día del mes definido en la póliza debe estar entre 1 y " + dias, "warning" );
                                setTimeout( function() { $( "#dia" ).focus() }, 100 );
                            }
                        } else {
                            notify( "Escribe un día válido", "warning" );
                            setTimeout( function() { $( "#dia" ).focus() }, 100 );
                        }
                    }
                    e.stopPropagation();
                    return false;
                });

            $("#actualizarEncabezado").click(function(e){
                e.preventDefault();
                window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
                var temp = $("#polizaTemporalData");
                // Grabamos en la base de datos ...
                comunica( "scg/poliza/ControladorPolizaManual.php", {
                    "metodo" : "actualizarEncabezado",
                    "ue"     : $("#EncabezadoPoliza").attr("ue"),
                    "pot_id" : temp.data("p"),
                    "prf_id" : temp.data("t"),
                    "n"      : temp.data("n"),
                    "m"      : temp.data("m"),
                    "d"      : $("#dia").val(),
                    "c"      : $("#concepto").val(),
                    "apg"    : $("#apg_id").val()
                });
                e.stopPropagation();
                return false;
            });


            ');
        }
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Dibujamos la pantalla donde modifican/adicionar un registro a la póliza
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function DibujaSeccionDetalle(
        $ue,
        $strAlta,
        $strPot,
        $strPrf,
        $strMsc,
        $strNum
    ) {
        // Parámetro que indica si es un alta o un cambio ...
        $bolEsAlta = ((isset($strAlta) && $strAlta == 'si') ? true : false);

        $ue = (isset($ue)) ? $ue : '001';  // Unidad Ejecutora
        // DETERMINAMOS SI VIENE DESDE EL LIBRO DIARIO (Y POR LO TANTO ES UN CAMBIO)
        $msc_id = ((isset($strMsc) && $strMsc != '') ? $strMsc : '');
        $pot_id = ((isset($strPot) && $strPot != '') ? $strPot : '');
        $prf_id = ((isset($strPrf) && $strPrf != '') ? $strPrf : '');
        $numero = ((isset($strNum) && $strNum != '') ? $strNum : '');
        $ue = ((isset($ue) && $ue != '') ? $ue : '');   // ¿ porqué lo repito ?


        $this->setContenedor('DetallePoliza');
        $fechaDoctoFuente = new stdClass();
        $fechaDoctoFuente->id = 'fechaDoctoFuente';
        $fechaDoctoFuente->name = 'fechaDoctoFuente';
        $fechaDoctoFuente->readonly = true;
        $fechaDoctoFuente->class = 'fecha';
        $fechaDoctoFuente->placeholder = 'Fecha';
        $fechaDoctoFuente->required = true;
        $fechaDoctoFuente->formato = 'fecha';
        $fechaDoctoFuente->labelClass = '_32 txtL';
        $fechaDoctoFuente->enDiv = true;
        $gpo_idP = (isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo']
            : $_SESSION[_S]['gpo_id']);
        $ur_idP = (isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur']
            : $_SESSION[_S]['ur_id']);
        $comboUrPoliza = new stdClass();
        if ($gpo_idP . $ur_idP == 116) {
            $comboUrPoliza->sql = 'SELECT CONCAT(gpo_id,ur_id) as id,ur_nombre,gpo_id,ur_id
            FROM cat_UnidadResponsable WHERE gpo_id!=0 AND CONCAT(gpo_id,ur_id)!=115 ';
            $comboUrPoliza->id = 'urOrigen';
            $comboUrPoliza->label = 'Unidad Responsable Origen';
            $comboUrPoliza->comoAtr = 'gpo_id,ur_id';
            $comboUrPoliza->value = 'id';
            $comboUrPoliza->required = true;
        } else {
            $comboUrPoliza->sql = "SELECT CONCAT(gpo_id,ur_id) as id,ur_nombre,gpo_id,ur_id
            FROM cat_UnidadResponsable WHERE gpo_id=$gpo_idP AND ur_id=$ur_idP";
            $comboUrPoliza->id = 'urOrigen';
            $comboUrPoliza->label = 'Unidad Responsable Origen';
            $comboUrPoliza->comoAtr = 'gpo_id,ur_id';
            $comboUrPoliza->value = 'id';
            $comboUrPoliza->required = true;
            $comboUrPoliza->class = 'nover';
        }
        $this->mixStyle('.nover{display:none;}');
        $this->mixHtml('<div class="well" id="divEdicionLineaPoliza" style="min-height: 20px;padding: 19px;margin-bottom: 20px;background-color: #f5f5f5;border: 1px solid #e3e3e3;border-radius: 4px;box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);">
        <a class="boxclose" id="boxclose"></a>
        <form action="" class="" id="formaDetallePoliza">' . ($this->esFondos ? '
            <div class="row">
                            <div class="col-md-3 col-xs-3">
                                ' . $this->ponCombo($comboUrPoliza) . '
                            </div>
                        </div>' : '') . '
            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-3">
                    <legend>Cuenta<div style="float: right;" id="ue_cta"></div></legend>
                    <input type="text" onBlur="ValidaCuenta();" name="cuenta"  id="cuenta" maxlength="30" value="" tabindex="210" class="form-control F1Catalogo" placeholder="F1: Catalogo"/>
                </fieldset>

                <fieldset class="col-md-3 col-xs-3">
                    <legend>Descripción</legend>
                    <input type="text" id="nombreCuenta" value="" disabled class="form-control" tabindex="-1"/>
                </fieldset>

                <fieldset class="col-md-2 col-xs-2">
                    <legend>Debe</legend>
                    <input type="text" id="debe" name="debe" class="form-control wr-input moneda valida42210" value="" style="text-align:right" tabindex="215"/>
                </fieldset>

                <fieldset class="col-md-2 col-xs-2">
                    <legend>Haber</legend>
                    <input type="text" id="haber" name="haber" style="text-align:right" class="form-control valida42210" value="" tabindex="220"/>
                </fieldset>

                <fieldset class="col-md-2 col-xs-2">
                    <legend># Docto Fuente</legend>
                    <input id="numeroDoctoFuente" name="numeroDoctoFuente" type="text" class="form-control F1Catalogo"  value="" tabindex="225"/>
                </fieldset>
            </div>

            <div class="row mb-12">

                <fieldset id="detalleDocumentoFuente" class="row mb-12" style="display:none;">

                <fieldset class="col-md-2 col-xs-2">
                    <legend>Tipo</legend>
                    <select name="tdf_id" id="tdf_id" class="form-control valida42210" tabindex="230">');

        $this->conectar();
        $this->setSql('SELECT tdf_id, tdf_nombre FROM cat_TipoDocumentoFuente');
        $this->consultar();
        $this->desconectar();
        $tdo = '';
        while ($row = $this->extraer()) {
            $this->mixHtml('<option value="' . $row['tdf_id'] . '" >' . $row['tdf_id'] . ' - ' . $row['tdf_nombre'] . '</option>');
        }

        $this->mixHtml('</select>

                </fieldset>

                <fieldset class="col-md-2 col-xs-2">
                    <legend>Fecha</legend>
                    <div class="form-group ">
                        ' . $this->ponInput($fechaDoctoFuente) . '
                    </div>
                </fieldset>

                <fieldset class="col-md-2 col-xs-2">
                    <legend>RFC</legend>
                    <input type="text" name="rfcDoctoFuente"  id="rfcDoctoFuente" maxlength="13" value="" tabindex="240" class="form-control F1Catalogo valida42210" placeholder="F1: Catalogo" />
                </fieldset>

                <fieldset class="col-md-4 col-xs-4">
                    <legend>Observaciones</legend>
                    <input name="beneficiario" id="beneficiario" type="text" value="" class="form-control valida42210" tabindex="245"/>
                </fieldset>

                <fieldset class="col-md-2 col-xs-2">
                    <legend>Grupo Docto Fuente</legend>
                    <div class="form-group ">
                        <select name="adf_id" id="adf_id" tabindex="250" class="form-select">');

        $this->conectar();
        // Marcos: 17/ene/2018
        // Israel solicita que sólo se muestre GRAL en ésta opción... ergo quitaremos PRES para todos
        $where = ' WHERE ';
        if (claveClasAdm() == 16) {
            $where .= " adf_id <> 'PRES' ";
        } else {
            $where .= " ( adf_id <> 'SAP' AND adf_id <> 'PRES' ) ";
        }
        $this->setSql("SELECT adf_id, adf_nombre FROM cat_AgrupacionDocumentoFuente {$where} ORDER BY IF( adf_id='GRAL', 0, adf_id );");
        $this->consultar();
        $this->desconectar();
        $tdo = '';
        while ($row = $this->extraer()) {
            $this->mixHtml('<option value="' . $row['adf_id'] . '" >' . $row['adf_id'] . ' - ' . $row['adf_nombre'] . '</option>');
        }

        $this->mixHtml('</select>
                    </div>
                </fieldset>
            </div>');

        $ore_idMml = new stdClass();
        $ore_idMml->placeholder = 'PIPET';
        $params = [$gpo_idP, $ur_idP];
        $ore_idMml->sql = 'SELECT p_or.ore_idMml
                            FROM poa_OrigenRecurso p_or
                            LEFT JOIN enc_Programatico e ON e.gpo_id = p_or.gpo_id AND e.ur_id = p_or.ur_id AND e.epg_id = p_or.epg_id
                            LEFT JOIN cat_ObraActividad oa ON e.gpo_id = oa.gpo_id AND e.ur_id = oa.ur_id AND e.prg_id = oa.prg_id AND e.spr_id = oa.spr_id AND e.py_id = oa.py_id AND e.oa_id = oa.oa_id
                            WHERE p_or.ore_idMml <> "" AND p_or.gpo_id= ? AND p_or.ur_id= ?';
        $ore_idMml->params = $params;
        $ore_idMml->id = 'ore_idMml';
        $ore_idMml->enDiv = true;
        $ore_idMml->camposVal = 'ore_idMml';
        $ore_idMml->search = true;
        $ore_idMml->change = '
            $("#numObra").val("");
            $("#nomObra").val("");
            if ($(this).val() != "") {
                $.ajax({
                    url : "scg/poliza/ControladorPolizaManual.php",
                    type : "POST",
                    dataType : "json",
                    data : {
                        metodo : "buscaPipet",
                        ore_idMml : $("#ore_idMml option:selected").val()
                    },
                    success : function(datos){
                        $.each(datos, function(index, row){
                            $("#numObra").val(row.oa_numObra);
                            $("#nomObra").val(row.oa_nombre);
                        });
                    }
                });
            }';

        $this->mixHtml('
            <div class="row">
                <fieldset id="pipet" class="col-md-2 col-xs-2" style="display:none;">
                    <legend>PIPET</legend>
                        ' . $this->ponCombo($ore_idMml) . '
                </fieldset>
                <fieldset id="pipet2" class="col-md-2 col-xs-2" style="display:none; color:#3DF019";" data-toggle="tooltip" data-placement="top" title="El PIPET seleccionado inicialmente será utilizado para todos los movimientos de las cuentas 1235 - Construcciones en Proceso en Bienes de Dominio Público o  1236 - Bienes Propios de esta póliza. Si requiere un PIPET diferente, primero deberá eliminar el o los movimientos anteriormente capturados. ">
                    <legend>PIPET</legend>
                        <input id="numPipet" name="numPipet" class="form-control" type="text" disabled value="" />
                </fieldset>
                <fieldset id="numerObra" class="col-md-2 col-xs-2" style="display:none;">
                    <legend>Número Obra</legend>
                    <input id="numObra" name="numObra" class="form-control" type="text" disabled value="" />
                </fieldset>
                <fieldset id="nombreObra" class="col-md-8 col-xs-8" style="display:none;">
                    <legend>Nombre de la Obra</legend>
                    <input id="nomObra" name="nomObra" class="form-control" type="text" disabled value="" />
                </fieldset>
            </div>
            <div class="mb-4"></div>

            <div class="row">
                <fieldset class="col-md-3 col-xs-3">
                    <input id="cveFin" name="cveFin" type="text" class="form-control F1Catalogo" placeholder="Clave de Financiamiento" value=""  tabindex="256"/>
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    <input id="nomCveFin" name="nomCveFin" class="form-control" type="text" disabled value="" />
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <input id="bdo_Clc" name="bdo_Clc" type="text" class="form-control" placeholder="# CLC" value=""  tabindex="232"/>
                </fieldset>

                    ' . (!($this->esFondos) ? '
                <fieldset class="col-md-1 col-xs-1 ">
                    <input id="urOrigen" name="urOrigen" type="hidden"  gpo_id= ' . $gpo_idP . '  ur_id=' . $ur_idP . ' class="form-control" placeholder="# UR" value="" tabindex="233" maxlength="3" "disabled"  />
                </fieldset>' : '') . '
                <fieldset class="col-md-1 col-xs-1  ">
                    <input id="bdo_CereCo" name="bdo_CereCo" type="' . ($this->esFondos
            ? 'text' : 'hidden') . '" class="form-control" placeholder="# CereCo" value=""  tabindex="234"/>
                </fieldset>
                <div class="right">
                    <button id="grabarLinea" class="btn btn-outline-success me-1 ladda-button" data-style="slide-left" style="float:right;" tabindex="255"><i class="fa-solid fa-plus me-2"></i>Grabar Línea</button>
                </div>
            </div>

        </form>
        <br />
        <br />
        </div>
        <div id="myGridDetallePoliza" class="tabla-contenedor col-md-12" style="display:none;">
            <table id="registroCtas" class="table table-striped table-bordered">
				<thead>
					<tr role="row" class="warning" style="text-align:center;">
						<th role="columnheader" class="warning">Lin</th>
						<th role="columnheader" class="warning">Cuenta Contable</th>
                        <th role="columnheader" class="warning">Nombre</th>
                        <th role="columnheader" class="warning">Debe</th>
                        <th role="columnheader" class="warning">Haber</th>
                        <th role="columnheader" class="warning">Docto Fte</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
        </div>
        <br />
        ');

        $this->mixStyle('
            .tabla-contenedor table > tbody > tr.coloreaCtas > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');


        if ($bolEsAlta) {
            $this->mixScript('
                // Por default, el tipo de documento fuente será GENERAL
                $("#tdf_id").val("GRAL");
            ');
        }


        $this->mixScript('
            $("#boxclose").click(function(e){
                $("#divEdicionLineaPoliza").hide();
                $("#adicionarLinea").attr("style", "visibility: visible");
                                ' . (($this->esFondos) ? '
                                $("#urOrigen").removeAttr("disabled");' : '') . '
                $("#DetallePoliza").height( $(window).height() - $("#areaBotones").height() - 95 );
                $("#myGridDetallePoliza").height( $(window).height() - $("#areaBotones").height() - $("#divEdicionLineaPoliza").height() - $("#page-tabs").height() - 95 );
            });

            ' . (($this->esFondos) ? '' : '

            $(".valida42210").focus(function(e){
                var tdf = $("#tdf_id").val();
                var cta = $("#cuenta").val().substring( 0, 9 ).trim();
                if ( tdf == "CLC" && cta == "4.2.2.1.0" ) {
                    $("#bdo_Clc").show();
                } else {
                    $("#bdo_Clc").hide();
                }
            });
            ') . '

            $("#bdo_Clc").blur( function(e) {
                if ( !$.isNumeric( $("#bdo_Clc").val() ) ) {
                    $(this).val("");
                }
            });

            // Suma el debe y el haber...
            function recalcularSumas( intGrabaEnMaestro ){
                var nTotalDebe = 0;
                var nTotalHaber = 0;

                // Preguntamos en la base de datos
                comunica( "scg/poliza/ControladorPolizaManual.php", {
                    "metodo" : "dameTotales",
                    "ue"     : $("#EncabezadoPoliza").attr("ue"),
                    "p"      : $("#idPolizaTemporal").attr( "t" ),
                    "r"      : $("#idPolizaTemporal").attr( "p" ),
                    "n"      : $("#idPolizaTemporal").attr( "n" ),
                    "m"      : $("#idPolizaTemporal").attr( "m" ),
                    "op"     : $("#homeMenuPM").attr("op"),
                    "gM"     : intGrabaEnMaestro
                });
                return false;
            }

            function ValidaCuenta(){
                $("#cuenta").val( $("#cuenta").val().toUpperCase() );
                $("#cuenta").val( preparaCuentaContable( $("#cuenta").val(), 3 ) ).change();
                if( $("#cuenta").val() != "" && $("#cuenta").val().length >= 5){
                    comunica("scg/poliza/ControladorPolizaManual.php",{ metodo: "ValidaCuenta", cta_id: $("#cuenta").val(), ue: $("#nombreCuenta").attr("deUE"), ueCont: $("#homeMenuPM").attr("ue") });
                } else {
                    if( $("#cuenta").val() == "" ) {
                    } else {
                        $("#nombreCuenta").val("");
                        notify( "La cuenta no es valida", "error" );
                    }
                }
            };

            /* En el campo cuenta contable, F1 llama al catálogo */
            $("#cuenta").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                /* F1 llama a la pantalla para mostrar el catalogo de cuentas */
                if ( key == 112 ) {
                    e.preventDefault();
                    dlgC.dialog("open");
                }
            });

            $("#cuenta").focus( function(e) {
                ctaCuenta = "cuenta";
                ctaNombre = "nombreCuenta";
            });

            /* F1 para clave de financiamiento */
            $("#cveFin").focus( function(e) {
                cveFinID = "cveFin";
                cveFinNom = "nomCveFin";
            });
            $("#cveFin").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                /* F1 llama a la pantalla para mostrar el catalogo de cuentas */
                if ( key == 112 ) {
                    e.preventDefault();
                    dlgCveFin.dialog("open");
                }
            });

            $("#numeroDoctoFuente").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                /* F1 llama a la pantalla para mostrar el catalogo de documentos */
                if ( key == 112 ) {
                    e.preventDefault();
                    dlgD.dialog("open");
                    e.stopPropagation;
                }
            });

            $("#numeroDoctoFuente").focus( function(e) {
                docId  = "numeroDoctoFuente";
                docRef = "numeroDoctoFuente";
            });

            $("#fechaDoctoFuente").blur(function(e){
                e.preventDefault();
                validaFecha( $(this).val(), "fechaDoctoFuente", false, false )
                e.stopPropagation();
                return false;
            });

            $("#rfcDoctoFuente").focus( function(e) {
                rfcCampo = "rfcDoctoFuente";
                rfcBeneficiario = "beneficiario";
            });

            ' . $this->ponF1Proveedores('rfcDoctoFuente', [], 'beneficiario') . ';

            /* Al esribir en el debe inhabilitamos el haber ... si vaciamos el debe habilitamos el haber */
            $("#debe").keyup( function() {
                if ( $("#debe").val().length > 0 ) {
                    $("#haber").val("");
                    $("#haber").prop("disabled",true);
                } else {
                    $("#debe").val("");
                    $("#haber").prop("disabled",false);
                }
            });
            /* Al esribir en el haber inhabilitamos el debe ... si vaciamos el haber habilitamos el debe */
            $("#haber").keyup( function() {
                if ( $("#haber").val().length > 0 ) {
                    $("#debe").val("");
                    $("#debe").prop("disabled",true);
                } else {
                    $("#haber").val("");
                    $("#debe").prop("disabled",false);
                }
            });
            /* Si escriben el numero del documento fuente ... mostramos los campos para sus datos ... si no hay numero ... lo ocultamos */
            $("#numeroDoctoFuente").keyup( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;

                if ( $("#numeroDoctoFuente").val().length > 0 ) {
                        $("#detalleDocumentoFuente").show();
                } else {
                        $("#detalleDocumentoFuente").hide();
                }
            });

            $("#debe").keypress(function(event){
                // + cuadra la póliza
                if ( event.which == 43 ) {
                    $(this).val( parseFloat( unformatNumber($("#sumaHaber").val()) ) - parseFloat( unformatNumber($("#sumaDebe").val()) ) );
                    return false;
                }
                var anularTecla = false;
                var esPermitido =jQuery.inArray(event.which, [0,8]) >= 0;
                if(event.which == 46)  anularTecla = $(this).val().indexOf(".") >= 0;
                else anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                if(event.which == 45){
                    if( $(this).val() == "" ){
                        anularTecla = false;
                    }else if( ($(this).val().indexOf("-") >= 0 && $(this).val().length > 1) || $(this).val().length > 1)
                        $(this).val( $(this).val() * -1 );
                }
                if( anularTecla ) event.preventDefault();
            });
            $("#debe").blur(function(event){
                if ( $.isNumeric( $(this).val() ) ) {
                    $(this).val( formatNumber($(this).val(),"","") );
                } else {
                    $(this).val("");
                }
            });
            $("#debe").focus(function(event){
                $(this).val( unformatNumber($(this).val()) );
                if( $(this).val() == 0 ) $(this).val("");
            });

            $("#haber").keypress(function(event){
                // + cuadra la póliza
                if ( event.which == 43 ) {
                    $(this).val( parseFloat( unformatNumber($("#sumaDebe").val()) ) - parseFloat( unformatNumber($("#sumaHaber").val()) ) );
                    return false;
                }
                var anularTecla = false;
                var esPermitido = jQuery.inArray(event.which, [0,8]) >= 0;
                if(event.which == 46)  anularTecla = $(this).val().indexOf(".") >= 0;
                else anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                if(event.which == 45){
                    if( $(this).val() == "" ){
                        anularTecla = false;
                    }else if( ($(this).val().indexOf("-") >= 0 && $(this).val().length > 1) || $(this).val().length > 1)
                        $(this).val( $(this).val() * -1 );
                }
                if( anularTecla ) event.preventDefault();
            });
            $("#haber").blur(function(event){
                if ( $.isNumeric( $(this).val() ) ) {
                    $(this).val( formatNumber($(this).val(),"","") );
                } else {
                    $(this).val("");
                }
            });
            $("#haber").focus(function(event){
                $(this).val( unformatNumber($(this).val()) );
                if( $(this).val() == 0 ) $(this).val("");
            });

            $("#grabarPoliza").click(function(e){

                e.preventDefault();

                window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...

                // Grabamos en la base de datos ...
                comunica( "scg/poliza/ControladorPolizaManual.php", {
                    "metodo" : "guardarPoliza",
                    "ue"     : $("#EncabezadoPoliza").attr("ue"),
                    "pot_id" : $("#idPolizaTemporal").attr( "t" ),
                    "prf_id" : $("#idPolizaTemporal").attr( "p" ),
                    "n"      : $("#idPolizaTemporal").attr( "n" ),
                    "m"      : $("#idPolizaTemporal").attr( "m" ),
                    "d"      : unformatNumber($("#sumaDebe").val()),
                    "h"      : unformatNumber($("#sumaHaber").val())

                });
                e.stopPropagation();
            });

            $("#grabarLinea").click(function(e){
                e.preventDefault();

                // VALIDACIONES
                // La cuenta contable no puede quedar vacía
                if( $("#cuenta").val().trim() == "" ){
                    notify( "La cuenta contable no puede quedar vacía", "warning" )
                    setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                    return false;
                }
                // Debe ser una cuenta válida
                if( $("#nombreCuenta").val().trim() == "" ){
                    notify( "Escribe una cuenta contable válida", "warning" )
                    setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                    return false;
                }
                // Si no es póliza de cheque cancelado, debe contener importes
                if( parseFloat( $("#debe").val() ) == 0 && parseFloat( $("#haber").val() ) == 0 ){
                    if( $("#EsChequeCancelado").css("display") == "none" ){
                        notify( "El registro debe contener importe", "warning" )
                        setTimeout( function() { $( "#debe" ).focus() }, 100 );
                        return false;
                    }
                }
                //Grupo Docto Fuente vacio
                if( $("#adf_id").val()== null)
                {
                    notify( "Debe seleccionar un Documento fuente", "warning" )
                    setTimeout( function() { $( "#adf_id" ).focus() }, 100 );
                    return false;
                }

                var cta = $("#cuenta").val();
                /*switch(cta.substring(0, 7)){
                    case "1.2.3.5":
                    case "1.2.3.6":
                        if($("#idPip").val() == ""){
                            if( $("#ore_idMml").val() == "")
                            {
                                notify( "Debe seleccionar el Pipet para la cuenta " + cta, "warning" )
                                setTimeout( function() { $( "#ore_idMml" ).focus() }, 100 );
                                return false;
                            }else{
                                var pipet = $("#ore_idMml").val();
                            }
                        }else{
                            var pipet = $("#idPip").val();
                        }
                    break;
                }*/

                /**************************************************/

                $("#registroCtas tbody tr").each(function(index, row) {
                    const numeroRegistro = index + 1;
                    $(this).attr("id", index);
                    $(row).find("td:first").text(numeroRegistro);
                });
                // Recalculamos la suma del debe y del haber
                recalcularSumas( 1 );

                /**********************************************/

                var $btn = $("#idPolizaTemporal");
                var $div = $("#polizaTemporalData");

                var pot_id = $btn.attr("t") || $div.data("t");
                var prf_id = $btn.attr("p") || $div.data("p");
                var n      = $btn.attr("n") || $div.data("n");
                var m      = $btn.attr("m") || $div.data("m");

                window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
                // Grabamos en la base de datos ...
                /*switch(cta.substring(0, 7)){
                    case "1.2.3.5":
                    case "1.2.3.6":
                    try {
                        if($("#idPip").val() != ""){
                            if($("#totalPip").val() == "sin registro"){
                                var msj = "Una vez seleccionado el <b>PIPET</b>  para las cuentas <b> 1235 - Construcciones en Proceso en Bienes de Dominio Público o  1236 - Bienes Propios,</b> <br />será utilizado para todos los movimientos relacionados con éstas cuentas.<br/>";
                                alertify.confirm(
                                    "<b>CONFIRMAR PIPET</b>",
                                        msj + "¿Desea continuar con la captura?",
                                            function() {
                                                comunica( "scg/poliza/ControladorPolizaManual.php", {
                                                    "metodo"    : "guardarLineaPoliza",
                                                    "ue"        : $("#EncabezadoPoliza").attr("ue"),
                                                    "pot_id"    : pot_id,
                                                    "prf_id"    : prf_id,
                                                    "n"         : n,
                                                    "m"         : m,
                                                    "chkCan"    : $("#chequeC").prop("checked"),
                                                    "cta"       : $("#cuenta").val(),
                                                    "ctaUE"     : $("#nombreCuenta").attr("deue"),
                                                    "ctaUEOri"  : $("#nombreCuenta").attr("deueori"),
                                                    "ori"       : $("#cuenta").attr("ori"),
                                                    "natOri"    : $("#cuenta").attr("natOri"),
                                                    "nom"       : $("#nombreCuenta").val(),
                                                    "d"         : unformatNumber( $("#debe").val() ),
                                                    "h"         : unformatNumber( $("#haber").val() ),
                                                    "n_pipet"   : pipet,
                                                    "n_df"      : $("#numeroDoctoFuente").val(),
                                                    "t_df"      : $("#tdf_id").val(),
                                                    "f_df"      : $("#fechaDoctoFuente").val(),
                                                    "r_df"      : $("#rfcDoctoFuente").val(),
                                                    "b_df"      : $("#beneficiario").val(),
                                                    "a_df"      : $("#adf_id").val(),
                                                    "clc"       : $("#bdo_Clc").val(),
                                                    "cereCo"    : $("#bdo_CereCo").val(),
                                                    "cf"        : $("#cveFin").val(),
                                                    "op"        : $("#homeMenuPM").attr("op"),
                                                    "modo"      : $("#grabarLinea").attr("modo"),
                                                    "l"         : $("#grabarLinea").attr("l"),
                                                    // "ur"     : $("#urOrigen").val(),
                                                    ' . (($this->esFondos) ? '
                                                        "gpo_idP"       : $("option:selected", "#urOrigen").attr("gpo_id"),
                                                        "ur_idP"    : $("option:selected", "#urOrigen").attr("ur_id")' : '
                                                        "gpo_idP"       : $("#urOrigen").attr("gpo_id"),
                                                        "ur_idP"    : $("#urOrigen").attr("ur_id")
                                                    ') . '
                                                });

                                                // Limpiamos los campos
                                                $("#cuenta").val("");
                                                $("#nombreCuenta").val("");
                                                $("#debe").val("");
                                                $("#haber").val("");
                                                $("#debe").prop("disabled",false);
                                                $("#haber").prop("disabled",false);
                                                $("#numeroDoctoFuente").val("");
                                                $("#fechaDoctoFuente").val("");
                                                $("#rfcDoctoFuente").val("");
                                                $("#beneficiario").val("");
                                                $("#tdf_id").val("GRAL");
                                                $("#pipet").hide();
                                                $("#ore_idMml").val("");
                                                $("#pipet2").hide()
                                                $("#numPipet").val("");
                                                $("#numerObra").hide();
                                                $("#numObra").val("");
                                                $("#nombreObra").hide();
                                                $("#nomObra").val("");
                                                $("#cveFin").val(""),
                                                $("#nomCveFin").val(""),
                                                $("#bdo_Clc").val(""),
                                                $("#bdo_CereCo").val(""),
                                                $("#detalleDocumentoFuente").hide();

                                                // Reinicializamos la UE
                                                $("#nombreCuenta").attr("deue",$("#homeMenuPM").attr("ue"));
                                                $("#ue_cta").html("");

                                                // Limpiamos valores extras en atributos
                                                $("#numeroDoctoFuente").attr("tdf","");
                                                $("#numeroDoctoFuente").attr("tdf","");
                                                $("#cveFin").attr("enc","");
                                                $("#cveFin").attr("ori","");

                                                e.stopPropagation();
                                                setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                                            },
                                            undefined
                                ).set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                            }else{
                                comunica( "scg/poliza/ControladorPolizaManual.php", {
                                    "metodo"    : "guardarLineaPoliza",
                                    "ue"        : $("#EncabezadoPoliza").attr("ue"),
                                    "pot_id"    : pot_id,
                                    "prf_id"    : prf_id,
                                    "n"         : n,
                                    "m"         : m,
                                    "chkCan"    : $("#chequeC").prop("checked"),
                                    "cta"       : $("#cuenta").val(),
                                    "ctaUE"     : $("#nombreCuenta").attr("deue"),
                                    "ctaUEOri"  : $("#nombreCuenta").attr("deueori"),
                                    "ori"       : $("#cuenta").attr("ori"),
                                    "natOri"    : $("#cuenta").attr("natOri"),
                                    "nom"       : $("#nombreCuenta").val(),
                                    "d"         : unformatNumber( $("#debe").val() ),
                                    "h"         : unformatNumber( $("#haber").val() ),
                                    "n_pipet"   : pipet,
                                    "n_df"      : $("#numeroDoctoFuente").val(),
                                    "t_df"      : $("#tdf_id").val(),
                                    "f_df"      : $("#fechaDoctoFuente").val(),
                                    "r_df"      : $("#rfcDoctoFuente").val(),
                                    "b_df"      : $("#beneficiario").val(),
                                    "a_df"      : $("#adf_id").val(),
                                    "clc"       : $("#bdo_Clc").val(),
                                    "cereCo"    : $("#bdo_CereCo").val(),
                                    "cf"        : $("#cveFin").val(),
                                    "op"        : $("#homeMenuPM").attr("op"),
                                    "modo"      : $("#grabarLinea").attr("modo"),
                                    "l"         : $("#grabarLinea").attr("l"),
                                    // "ur"     : $("#urOrigen").val(),
                                    ' . (($this->esFondos) ? '
                                        "gpo_idP"       : $("option:selected", "#urOrigen").attr("gpo_id"),
                                        "ur_idP"    : $("option:selected", "#urOrigen").attr("ur_id")' : '
                                        "gpo_idP"       : $("#urOrigen").attr("gpo_id"),
                                        "ur_idP"    : $("#urOrigen").attr("ur_id")
                                    ') . '
                                });

                                // Limpiamos los campos
                                $("#cuenta").val("");
                                $("#nombreCuenta").val("");
                                $("#debe").val("");
                                $("#haber").val("");
                                $("#debe").prop("disabled",false);
                                $("#haber").prop("disabled",false);
                                $("#numeroDoctoFuente").val("");
                                $("#fechaDoctoFuente").val("");
                                $("#rfcDoctoFuente").val("");
                                $("#beneficiario").val("");
                                $("#tdf_id").val("GRAL");
                                $("#pipet").hide();
                                $("#ore_idMml").val("");
                                $("#pipet2").hide()
                                $("#numPipet").val("");
                                $("#numerObra").hide();
                                $("#numObra").val("");
                                $("#nombreObra").hide();
                                $("#nomObra").val("");
                                $("#cveFin").val(""),
                                $("#nomCveFin").val(""),
                                $("#bdo_Clc").val(""),
                                $("#bdo_CereCo").val(""),
                                $("#detalleDocumentoFuente").hide();

                                // Reinicializamos la UE
                                $("#nombreCuenta").attr("deue",$("#homeMenuPM").attr("ue"));
                                $("#ue_cta").html("");

                                // Limpiamos valores extras en atributos
                                $("#numeroDoctoFuente").attr("tdf","");
                                $("#numeroDoctoFuente").attr("tdf","");
                                $("#cveFin").attr("enc","");
                                $("#cveFin").attr("ori","");

                                e.stopPropagation();
                                setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                                return false;
                            }
                        }else{
                            if($("#totalPip").val() == "sin registro"){
                                 var msj = "Una vez seleccionado el <b>PIPET</b>  para las cuentas <b> 1235 - Construcciones en Proceso en Bienes de Dominio Público o  1236 - Bienes Propios,</b> <br />será utilizado para todos los movimientos relacionados con éstas cuentas.<br/>";
                                alertify.confirm(
                                    "<b>CONFIRMAR PIPET</b>",
                                        msj + "¿Desea continuar con la captura?",
                                            function() {
                                                comunica( "scg/poliza/ControladorPolizaManual.php", {
                                                    "metodo"    : "guardarLineaPoliza",
                                                    "ue"        : $("#EncabezadoPoliza").attr("ue"),
                                                    "pot_id"    : pot_id,
                                                    "prf_id"    : prf_id,
                                                    "n"         : n,
                                                    "m"         : m,
                                                    "chkCan"    : $("#chequeC").prop("checked"),
                                                    "cta"       : $("#cuenta").val(),
                                                    "ctaUE"     : $("#nombreCuenta").attr("deue"),
                                                    "ctaUEOri"  : $("#nombreCuenta").attr("deueori"),
                                                    "ori"       : $("#cuenta").attr("ori"),
                                                    "natOri"    : $("#cuenta").attr("natOri"),
                                                    "nom"       : $("#nombreCuenta").val(),
                                                    "d"         : unformatNumber( $("#debe").val() ),
                                                    "h"         : unformatNumber( $("#haber").val() ),
                                                    "n_pipet"   : pipet,
                                                    "n_df"      : $("#numeroDoctoFuente").val(),
                                                    "t_df"      : $("#tdf_id").val(),
                                                    "f_df"      : $("#fechaDoctoFuente").val(),
                                                    "r_df"      : $("#rfcDoctoFuente").val(),
                                                    "b_df"      : $("#beneficiario").val(),
                                                    "a_df"      : $("#adf_id").val(),
                                                    "clc"       : $("#bdo_Clc").val(),
                                                    "cereCo"    : $("#bdo_CereCo").val(),
                                                    "cf"        : $("#cveFin").val(),
                                                    "op"        : $("#homeMenuPM").attr("op"),
                                                    "modo"      : $("#grabarLinea").attr("modo"),
                                                    "l"         : $("#grabarLinea").attr("l"),
                                                    // "ur"     : $("#urOrigen").val(),
                                                    ' . (($this->esFondos) ? '
                                                        "gpo_idP"       : $("option:selected", "#urOrigen").attr("gpo_id"),
                                                        "ur_idP"    : $("option:selected", "#urOrigen").attr("ur_id")' : '
                                                        "gpo_idP"       : $("#urOrigen").attr("gpo_id"),
                                                        "ur_idP"    : $("#urOrigen").attr("ur_id")
                                                    ') . '
                                                });

                                                // Limpiamos los campos
                                                $("#cuenta").val("");
                                                $("#nombreCuenta").val("");
                                                $("#debe").val("");
                                                $("#haber").val("");
                                                $("#debe").prop("disabled",false);
                                                $("#haber").prop("disabled",false);
                                                $("#numeroDoctoFuente").val("");
                                                $("#fechaDoctoFuente").val("");
                                                $("#rfcDoctoFuente").val("");
                                                $("#beneficiario").val("");
                                                $("#tdf_id").val("GRAL");
                                                $("#pipet").hide();
                                                $("#ore_idMml").val("");
                                                $("#pipet2").hide()
                                                $("#numPipet").val("");
                                                $("#numerObra").hide();
                                                $("#numObra").val("");
                                                $("#nombreObra").hide();
                                                $("#nomObra").val("");
                                                $("#cveFin").val(""),
                                                $("#nomCveFin").val(""),
                                                $("#bdo_Clc").val(""),
                                                $("#bdo_CereCo").val(""),
                                                $("#detalleDocumentoFuente").hide();

                                                // Reinicializamos la UE
                                                $("#nombreCuenta").attr("deue",$("#homeMenuPM").attr("ue"));
                                                $("#ue_cta").html("");

                                                // Limpiamos valores extras en atributos
                                                $("#numeroDoctoFuente").attr("tdf","");
                                                $("#numeroDoctoFuente").attr("tdf","");
                                                $("#cveFin").attr("enc","");
                                                $("#cveFin").attr("ori","");

                                                e.stopPropagation();
                                                setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                                            },
                                            undefined
                                ).set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                            }else{
                                comunica( "scg/poliza/ControladorPolizaManual.php", {
                                    "metodo"    : "guardarLineaPoliza",
                                    "ue"        : $("#EncabezadoPoliza").attr("ue"),
                                    "pot_id"    : pot_id,
                                    "prf_id"    : prf_id,
                                    "n"         : n,
                                    "m"         : m,
                                    "chkCan"    : $("#chequeC").prop("checked"),
                                    "cta"       : $("#cuenta").val(),
                                    "ctaUE"     : $("#nombreCuenta").attr("deue"),
                                    "ctaUEOri"  : $("#nombreCuenta").attr("deueori"),
                                    "ori"       : $("#cuenta").attr("ori"),
                                    "natOri"    : $("#cuenta").attr("natOri"),
                                    "nom"       : $("#nombreCuenta").val(),
                                    "d"         : unformatNumber( $("#debe").val() ),
                                    "h"         : unformatNumber( $("#haber").val() ),
                                    "n_pipet"   : pipet,
                                    "n_df"      : $("#numeroDoctoFuente").val(),
                                    "t_df"      : $("#tdf_id").val(),
                                    "f_df"      : $("#fechaDoctoFuente").val(),
                                    "r_df"      : $("#rfcDoctoFuente").val(),
                                    "b_df"      : $("#beneficiario").val(),
                                    "a_df"      : $("#adf_id").val(),
                                    "clc"       : $("#bdo_Clc").val(),
                                    "cereCo"    : $("#bdo_CereCo").val(),
                                    "cf"        : $("#cveFin").val(),
                                    "op"        : $("#homeMenuPM").attr("op"),
                                    "modo"      : $("#grabarLinea").attr("modo"),
                                    "l"         : $("#grabarLinea").attr("l"),
                                    // "ur"     : $("#urOrigen").val(),
                                    ' . (($this->esFondos) ? '
                                        "gpo_idP"       : $("option:selected", "#urOrigen").attr("gpo_id"),
                                        "ur_idP"    : $("option:selected", "#urOrigen").attr("ur_id")' : '
                                        "gpo_idP"       : $("#urOrigen").attr("gpo_id"),
                                        "ur_idP"    : $("#urOrigen").attr("ur_id")
                                    ') . '
                                });

                                // Limpiamos los campos
                                $("#cuenta").val("");
                                $("#nombreCuenta").val("");
                                $("#debe").val("");
                                $("#haber").val("");
                                $("#debe").prop("disabled",false);
                                $("#haber").prop("disabled",false);
                                $("#numeroDoctoFuente").val("");
                                $("#fechaDoctoFuente").val("");
                                $("#rfcDoctoFuente").val("");
                                $("#beneficiario").val("");
                                $("#tdf_id").val("GRAL");
                                $("#pipet").hide();
                                $("#ore_idMml").val("");
                                $("#pipet2").hide()
                                $("#numPipet").val("");
                                $("#numerObra").hide();
                                $("#numObra").val("");
                                $("#nombreObra").hide();
                                $("#nomObra").val("");
                                $("#cveFin").val(""),
                                $("#nomCveFin").val(""),
                                $("#bdo_Clc").val(""),
                                $("#bdo_CereCo").val(""),
                                $("#detalleDocumentoFuente").hide();

                                // Reinicializamos la UE
                                $("#nombreCuenta").attr("deue",$("#homeMenuPM").attr("ue"));
                                $("#ue_cta").html("");

                                // Limpiamos valores extras en atributos
                                $("#numeroDoctoFuente").attr("tdf","");
                                $("#numeroDoctoFuente").attr("tdf","");
                                $("#cveFin").attr("enc","");
                                $("#cveFin").attr("ori","");

                                e.stopPropagation();
                                setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                                return false;
                            }
                        }
                    } catch(e) {
                        alert( e );
                    }
                    break;
                    default:*/
                        comunica( "scg/poliza/ControladorPolizaManual.php", {
                            "metodo"    : "guardarLineaPoliza",
                            "ue"        : $("#EncabezadoPoliza").attr("ue"),
                            "pot_id"    : pot_id,
                            "prf_id"    : prf_id,
                            "n"         : n,
                            "m"         : m,
                            "chkCan"    : $("#chequeC").prop("checked"),
                            "cta"       : $("#cuenta").val(),
                            "ctaUE"     : $("#nombreCuenta").attr("deue"),
                            "ctaUEOri"  : $("#nombreCuenta").attr("deueori"),
                            "ori"       : $("#cuenta").attr("ori"),
                            "natOri"    : $("#cuenta").attr("natOri"),
                            "nom"       : $("#nombreCuenta").val(),
                            "d"         : unformatNumber( $("#debe").val() ),
                            "h"         : unformatNumber( $("#haber").val() ),
                            //"n_pipet"   : pipet,
                            "n_df"      : $("#numeroDoctoFuente").val(),
                            "t_df"      : $("#tdf_id").val(),
                            "f_df"      : $("#fechaDoctoFuente").val(),
                            "r_df"      : $("#rfcDoctoFuente").val(),
                            "b_df"      : $("#beneficiario").val(),
                            "a_df"      : $("#adf_id").val(),
                            "clc"       : $("#bdo_Clc").val(),
                            "cereCo"    : $("#bdo_CereCo").val(),
                            "cf"        : $("#cveFin").val(),
                            "op"        : $("#homeMenuPM").attr("op"),
                            "modo"      : $("#grabarLinea").attr("modo"),
                            "l"         : $("#grabarLinea").attr("l"),
                            // "ur"     : $("#urOrigen").val(),
                            ' . (($this->esFondos) ? '
                                "gpo_idP"       : $("option:selected", "#urOrigen").attr("gpo_id"),
                                "ur_idP"    : $("option:selected", "#urOrigen").attr("ur_id")' : '
                                "gpo_idP"       : $("#urOrigen").attr("gpo_id"),
                                "ur_idP"    : $("#urOrigen").attr("ur_id")
                            ') . '
                        });

                        // Limpiamos los campos
                        $("#cuenta").val("");
                        $("#nombreCuenta").val("");
                        $("#debe").val("");
                        $("#haber").val("");
                        $("#debe").prop("disabled",false);
                        $("#haber").prop("disabled",false);
                        $("#numeroDoctoFuente").val("");
                        $("#fechaDoctoFuente").val("");
                        $("#rfcDoctoFuente").val("");
                        $("#beneficiario").val("");
                        $("#tdf_id").val("GRAL");
                        //$("#pipet").hide();
                        //$("#ore_idMml").val("");
                        //$("#pipet2").hide()
                        //$("#numPipet").val("");
                        //$("#numerObra").hide();
                        //$("#numObra").val("");
                        //$("#nombreObra").hide();
                        //$("#nomObra").val("");
                        $("#cveFin").val(""),
                        $("#nomCveFin").val(""),
                        $("#bdo_Clc").val(""),
                        $("#bdo_CereCo").val(""),
                        $("#detalleDocumentoFuente").hide();

                        // Reinicializamos la UE
                        $("#nombreCuenta").attr("deue",$("#homeMenuPM").attr("ue"));
                        $("#ue_cta").html("");

                        // Limpiamos valores extras en atributos
                        $("#numeroDoctoFuente").attr("tdf","");
                        $("#numeroDoctoFuente").attr("tdf","");
                        $("#cveFin").attr("enc","");
                        $("#cveFin").attr("ori","");

                        e.stopPropagation();
                        setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                        return false;
                    //break;
                //}
            });


            function DameNumeroLinea(row, cell, value, columnDef, dataContext) {
                return row + 1;
            };

            function FormatoMoneda(row, cell, value, columnDef, dataContext) {

                if (value === null || value === "" || !(value != 0)) {
                    return "";
                } else {
                        var n = value,
                    c = 2,
                    d = ".",
                    t = ","
                    s = n < 0 ? "-" : "",
                    i = parseInt(n = Math.abs(+n || 0).toFixed(2)) + "",
                    j = (j = i.length) > 3 ? j % 3 : 0;
                    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
                }

            }
            // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
            // --------------------------------------------------------------------------------------------------------------
            function NumericSorter(a, b) {
                var x = a[sortcol], y = b[sortcol];
                return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
            }

            // Borramos una línea de la póliza ( si es una ALTA borramos de tem_PolizaDetalle, si es un CAMBIO borramos de cg_PolizaDetalle )
            $("#eliminarLinea").click(function(e){
                $("#registroCtas tbody tr").each(function(index, row) {
                    const numeroRegistro = index + 1;
                    $(this).attr("id", index);
                    $(row).find("td:first").text(numeroRegistro);
                });

                var idT =  $(".seleccionado").attr( "id" );
                var cta = $(".seleccionado td:nth-child(2)").text();
                var nom = $(".seleccionado td:nth-child(3)").text();
                var d = $(".seleccionado td:nth-child(4)").text();
                var h = $(".seleccionado td:nth-child(5)").text();
                var nL = $(".seleccionado td:nth-child(7)").text();

                if(d == "") {
                   d = "0.00";
                }

                if(h == "") {
                   h = "0.00";
                }

                if(d != "" && h == "0.00"){
                   var importe = d;
                }else{
                    var importe = h;
                }
                // Si es un valor editable ...
                if (typeof cta != "undefined" && cta.trim() != "" ) {
                    try {
                        alertify.confirm(
                            "<b>ELIMINAR LINEA DE LA PÓLIZA</b>",
                            "Se eliminará DEFINITIVAMENTE la afectación a la cuenta <br /><h4>" + cta + " : " + nom + "</h4><br />por un importe de <h4>" + importe + "</h4><br /><br />¿ Confirmas que deseas eliminarla ?",
                            function() {
                                comunica("scg/poliza/ControladorPolizaManual.php",{"metodo":"eliminarLinea",
                                    "ue": $("#EncabezadoPoliza").attr("ue"),
                                    "p" : $("#idPolizaTemporal").attr("t"),
                                    "r" : $("#idPolizaTemporal").attr("p"),
                                    "n" : $("#idPolizaTemporal").attr("n"),
                                    "m" : $("#idPolizaTemporal").attr("m"),
                                    "c" : cta,
                                    "d" : d,
                                    "h" : h,
                                    "l" : nL,
                                    "op": $("#homeMenuPM").attr("op"),
                                    "t": idT
                                });
                            },
                            undefined
                        ).set("labels", {ok: "ELIMINAR", cancel: "Cancelar Operación"});
                    } catch(e) {
                        alert( e );
                    }
                } else {
                    e.preventDefault();
                    return false;
                }
            });


            // Adicionamos una línea de la póliza ( si es una ALTA insertamos en tem_PolizaDetalle, si es un CAMBIO insertamos en cg_PolizaDetalle )
            $("#adicionarLinea").click(function(e) {

                $("#divEdicionLineaPoliza").show();
                $("#DetallePoliza").height( $(window).height() - $("#areaBotones").height() - 95 );
                $("#myGridDetallePoliza").height( $(window).height() - $("#areaBotones").height() - $("#divEdicionLineaPoliza").height() - $("#page-tabs").height() - 95 );
                $("#myGridDetallePoliza").on("mouseenter", function() {
                    $(this).css("overflow", "initial");
                });

                // Limpiamos los campos
                $("#cuenta").val("");
                $("#cuenta").attr("ori", "");
                $("#nombreCuenta").val("");
                $("#debe").val("");
                $("#haber").val("");
                $("#debe").prop("disabled",false);
                $("#haber").prop("disabled",false);
                $("#numeroDoctoFuente").val("");
                $("#fechaDoctoFuente").val("");
                $("#rfcDoctoFuente").val("");
                $("#beneficiario").val("");
                $("#tdf_id").val("GRAL");
                $("#detalleDocumentoFuente").hide();
                //$("#pipet").hide();
                //$("#ore_idMml").val("");
                //$("#numerObra").hide();
                //$("#numObra").val("");
                //$("#nombreObra").hide();
                //$("#nomObra").val("");

                // Modo ALTA
                $("#grabarLinea").attr("modo", "A");

                // Como es alta, no tiene # de línea a modificar
                $("#grabarLinea").attr("l", "");

                setTimeout( function() { $( "#cuenta" ).focus() }, 100 );

            });

            // Modificamos una línea de la póliza ( si es una ALTA el UPDATE va contra tem_PolizaDetalle, si es un CAMBIO el UPDATE va contra cg_PolizaDetalle )
            $("#modificarLinea").click(function(e){
                var cta = $(".seleccionado td:nth-child(2)").text();
                var nL = $(".seleccionado td:nth-child(7)").text();

                // Si es un valor editable ...
                if (typeof cta != "undefined" && cta.trim() != "" ) {
                    try {

                        window.espera = Ladda.create(this);   // Mostramos icono de espera ...

                        comunica("scg/poliza/ControladorPolizaManual.php",{"metodo":"modificarLinea",
                            "ue": $("#EncabezadoPoliza").attr("ue"),
                            "p" : $("#idPolizaTemporal").attr("t"),
                            "r" : $("#idPolizaTemporal").attr("p"),
                            "n" : $("#idPolizaTemporal").attr("n"),
                            "m" : $("#idPolizaTemporal").attr("m"),
                            "c" : cta,
                            "l" : nL,
                            "op": $("#homeMenuPM").attr("op")
                        });

                        setTimeout( function() { $("#tdf_id").change() }, 100 );

                    } catch(e) {
                        alert( e );
                    }
                } else {
                    e.preventDefault();
                    return false;
                }
            });
        ');

        // Después de preparar la pantalla ... si es un cambio ... cargamos los datos de la póliza
        // --------------------------------------------------------------------------------------------------------------
        if (!$bolEsAlta) {
            $this->mixScript('comunica("scg/poliza/ControladorPolizaManual.php", { metodo : "cargaPoliza",
                    m : "' . $msc_id . '",
                    p : "' . $pot_id . '",
                    r : "' . $prf_id . '",
                    n : "' . $numero . '",
                    ue: "' . $ue . '"
                });
            ');
        }
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Guardamos en encabezado de la póliza en la tabla TEMPORAL
    // Si llegamos hasta aqui es porque ya pasó la validación de todos los campos
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function guardarEncabezadoPoliza()
    {
        $mmc_id = 'L';  // Captura Manual
        $epl_id = (isset($this->prm->chkCan) && $this->prm->chkCan == 'true') ? 'H' : 'N';  // status de la poliza H=Cheque Cancelado   N=Normal
        $msc_id = date('n', strtotime(date_to_mysqldate($this->prm->fecha)));
        $ue = $this->getPrm('ue', 1);

        if (!$this->validarFechaEncabezadoPoliza()) {
            return false;
        }

        if (!$this->validarMesContableAbierto($msc_id)) {
            return false;
        }

        /*08/05/2026 MARCO RAMIREZ: Se modifica la consulta que graba la cabecera de la poliza, si encuentra registro con el id de poliza
        temporal actualiza el registro sino inserta uno nuevo,  evitando que cada que se recupere una poliza se genere
        un nuevo numero de poliza temporal y se inserte nuevamente los detalles de la poloza.
        */
        // Grabamos en el maestro TEMPORAL
        $this->conectar();
        $this->setSql('INSERT INTO ' . BD_TEMPORAL . self::PREFIJO_TEMPORAL . "PolizaMaestro
            (tem_pma,tem_carga, gpo_id,ur_id, ue_id, pot_id, prf_id, msc_id, pma_debe,pma_haber, pma_concepto, pma_fecha, epl_id,mmc_id, apg_id, pma_id, usr_id )
        VALUES(?,'', ?, ?, ? , ?, ?, ? , 0, 0, ?, ?, ?, ?, ?, 0, ? )
        ON DUPLICATE KEY UPDATE
            tem_pma = LAST_INSERT_ID(tem_pma),
            msc_id = VALUES(msc_id),
            pma_concepto = VALUES(pma_concepto),
            pma_fecha = VALUES(pma_fecha),
            epl_id = VALUES(epl_id),
            mmc_id = VALUES(mmc_id),
            apg_id = VALUES(apg_id),
            usr_id = VALUES(usr_id)");

        $npTo = isset($this->prm->npTo) ? $this->prm->npTo : 0;

        $params = [
            $npTo,
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
            $ue,
            $this->prm->pot_id,
            $this->prm->prf_id,
            $msc_id,
            mysql_escape_mimic(strtoupper($this->prm->descr)),
            date_to_mysqldate($this->prm->fecha),
            $epl_id,
            $mmc_id,
            $this->prm->apg_id,
            getSession('usr_id'),
        ];
        $this->consultar($params);
        $polNumero = $this->getUltimoId();    // Obtenemos el número temporal que le tocó ...
        // Si nos pasan un número original de póliza temporal, entonces estamos recuperando una póliza temporal
        // así que aquí cargaremos las líneas de la póliza temporal que quieren recuperar a la nueva póliza
        // temporal que le asignó la base de datos

        $this->mixScriptDetallePolizaTemporal($this->prm->fecha, $npTo, $ue);
        $this->desconectar();

        $this->mixScript('
            $("#EncabezadoPoliza *").prop("disabled",true);
            $("#homeMenuPM").prop("md",1);
            $("#idPolizaTemporal").text( "Temporal : ' . $this->prm->pot_id . '-' . $this->prm->prf_id . $polNumero . '" ).css("display", "inline");
            $("#idPolizaTemporal").attr( "t", "' . $this->prm->pot_id . '" );
            $("#idPolizaTemporal").attr( "p", "' . $this->prm->prf_id . '" );
            $("#idPolizaTemporal").attr( "n", ' . $polNumero . ');
            $("#idPolizaTemporal").attr( "m", "' . $msc_id . '" );
            $("#grabarEncabezado").hide();
            $("#recuperarPoliza").hide();
            $("#page-tabs a[href=\"2\"]").click();
            $("#formaDetallePoliza").css("display", "inline" );
            $("#myGridDetallePoliza").css("display", "inline" );
            setTimeout( function() { $( "#cuenta" ).focus(); window.espera?.stop() }, 500 );
            $("#registroCtas tbody").empty();

        ');

        return false;
    }

    private function validarFechaEncabezadoPoliza()
    {
        $fechaMysql = date_to_mysqldate($this->prm->fecha);
        $anio = $this->ejercicioActual;
        $mensaje = '';
        $focus = '#fecha';

        if ($fechaMysql < $this->ejercicioActual . '/01/01') {
            $mensaje = 'Favor de verificar la fecha';
        } elseif ($anio == date('Y') && $fechaMysql > $this->ejercicioActual . '/' . date('m') . '/' . date('d')) {
            $mensaje = 'La fecha no puede ser mayor a la fecha actual';
        } elseif ($anio != date('Y') && $fechaMysql > $this->ejercicioActual . '/12/31') {
            $mensaje = 'La fecha no puede ser mayor a  31 de Diciembre del ' . $this->ejercicioActual;
        } else {
            $date = str_replace('/', '-', $this->prm->fecha);
            $d = explode('-', $date);
            if (!checkdate($d[1], $d[0], $d[2])) {
                $mensaje = 'La fecha de la póliza no es válida';
                $focus = '#referencia';
            }
        }

        if ($mensaje === '') {
            return true;
        }

        $this->mixScript('
            notify( "' . $mensaje . '", "warning" );
            setTimeout( function() { $( "' . $focus . '" ).select().focus() }, 100 );
            window.espera.stop();
        ');
        return false;
    }

    private function validarMesContableAbierto($msc_id)
    {
        $this->setSql('SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? AND msc_id = ? ;');
        $this->conectar();
        $this->consultar([getSession('clasAdm-gpo'), getSession('clasAdm-ur'), $msc_id]);
        $this->desconectar();
        $row = $this->extraer();
        $valido = ($this->getNumeroRegistros() > 0 && $row['ems_id'] == 'A');

        if (!$valido) {
            $this->mixScript('
                notify( "El mes de ' . MesNombre($msc_id) . ' está cerrado", "warning" );
                setTimeout( function() { $( "#fecha" ).select().focus() }, 100 );
            ');
            return false;
        }

        return true;
    }

    private function mixScriptDetallePolizaTemporal($fecha, $npTo, $ue)
    {
        $this->mixScript('

            $.ajax({

                url : "scg/poliza/ControladorPolizaManual.php",

                type : "POST",

                dataType : "json",

                data : {
                    metodo : "obtenerDetallePolizaTemporal",
                    fecha  : "' . $fecha . '",
                    npTo   : "' . $npTo . '",
                    ue     : "' . $ue . '"
                },

                success : function(datos){

                    console.log(datos);

                    $("#registroCtas tbody").empty();

                    $.each(datos, function(index, row){
                        var nuevaFila = "<tr class=\'seleccionable\'>";
                            nuevaFila += "<td></td>";
                            nuevaFila += "<td style=\'white-space: pre-wrap\'>" + row.cta + "</td>";
                            nuevaFila += "<td style=\'white-space: pre-wrap\'>" + row.nom + "</td>";
                            nuevaFila += "<td style=\'text-align:right; white-space: pre-wrap\'>" + FormatoMoneda(0,0,row.pdx_debe,0,0) + "</td>";
                            nuevaFila += "<td style=\'text-align:right; white-space: pre-wrap\'>" + FormatoMoneda(0,0,row.pdx_haber,0,0) + "</td>";
                            nuevaFila += "<td style=\'white-space: pre-wrap\'>" + row.pdx_referencia + "</td>";
                            nuevaFila += "<td style=display:none>" + row.tem_pdx + "</td>";
                            nuevaFila += "<td style=display:none>" + row.ue_id + "</td>";
                        nuevaFila += "</tr>";
                        $("#registroCtas tbody").append(nuevaFila);
                    });

                    /********************************/
                    $(document).ready(function (){
                        $(".seleccionable").click(function() {
                            $(".seleccionable").css("font-weight","normal");
                            $(".seleccionable").removeClass("seleccionado");
                            $(this).addClass("seleccionado");
                            $(this).css("font-weight","bold");

                            $(".seleccionado").dblclick(function(){
                                $("#modificarLinea").click();
                                $("#bdo_Clc").val("");
                                $("#bdo_CereCo").val("");
                            });
                        });

                        $("#registroCtas tbody tr").hover(
                            function() {
                                $(this).addClass("coloreaCtas");
                            },
                            function() {
                                $(this).removeClass("coloreaCtas");
                            }
                        );
                    });

                    $("#registroCtas tbody tr").each(function(index, row){
                        const numeroRegistro = index + 1;
                        $(this).attr("id", index);
                        $(row).find("td:first").text(numeroRegistro);
                        $(row).find("td:first").css("text-align","center");
                    });

                    recalcularSumas(1);

                },

                error : function(xhr){

                    console.log(xhr.responseText);

                }

            });

        ');
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Guardamos una línea de la póliza
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function guardarLineaPoliza()
    {

        // Cargamos en variables los datos a grabar ...
        $epl_id = (isset($this->prm->chkCan) && $this->prm->chkCan == 'true') ? 'H'
            : 'N';  // status de la poliza H=Cheque Cancelado   N=Normal
        $cta = trim($this->prm->cta);
        $ctaUE = trim($this->prm->ctaUE);
        $ori = $this->prm->ori;
        $nom = $this->prm->nom;
        $pot_id = $this->prm->pot_id;
        $prf_id = $this->prm->prf_id;
        $msc_id = $this->prm->m;
        $n = $this->prm->n;
        $d = ($this->prm->d == '') ? 0 : $this->prm->d;
        $h = ($this->prm->h == '') ? 0 : $this->prm->h;
        $nPipet = (isset($this->prm->n_pipet) && $this->prm->n_pipet != '') ? $this->prm->n_pipet
            : null;
        $numDF = $this->prm->n_df;
        $tipoDF = $this->prm->t_df;
        $fechaDF = $this->prm->f_df;
        $rfcDF = $this->prm->r_df;
        $benDF = trim($this->prm->b_df);
        $gpoDF = $this->prm->a_df;
        $clc = (isset($this->prm->clc) && $this->prm->clc != '') ? $this->prm->clc
            : null;
        $cereCo = (isset($this->prm->cereCo) && $this->prm->cereCo != '') ? $this->prm->cereCo
            : null;
        $ue = (isset($this->prm->ue)) ? $this->prm->ue : 1;
        $mmc_id = 'L';  // Captura Manual
        $nat = ($d != 0) ? 'D' : 'H';
        $nL = (isset($this->prm->l) && $this->prm->l != '') ? $this->prm->l : 0;
        $gpo_idP = (isset($this->prm->gpo_idP) && $this->prm->gpo_idP != '') ? $this->prm->gpo_idP
            : '';
        $ur_idP = (isset($this->prm->ur_idP) && $this->prm->ur_idP != '') ? $this->prm->ur_idP
            : '';

        // Marcos: 28/marzo/2016
        // Adicionamos el registro opcional de la Clave de Financiamiento para cada línea de la póliza
        $cveFin = (isset($this->prm->cf) && $this->prm->cf != '') ? $this->prm->cf
            : '';

        // Si no nos indican... asumimos que estamos dando de alta una nueva póliza
        $op = (isset($this->prm->op) && $this->prm->op != '') ? $this->prm->op : 'A';
        $modo = (isset($this->prm->modo) && $this->prm->modo != '') ? $this->prm->modo
            : 'A';

        // Marcos: 10/ene/2018
        // TODOS los niveles de la cuenta (incluyenlo los niveles de mayor-conac) DEBEN venir separados por un punto
        // El mínimo son 6 : 1.1.1.3.1.001.0001
        if (substr_count($cta, '.') < 6) {
            $this->mixScript('notify("La cuenta DEBE separar cada uno de sus niveles con un punto", "error");window.espera.stop()');
            return false;
        }
        if ($gpo_idP == '' || $ur_idP == '') {
            $this->mixScript('notify("Es necesario seleccionar Unidad Responsable", "error");window.espera.stop()');
            return false;
        }


        // Marcos: 26/mayo/2016
        // Apareció una póliza con una línea de captura manual donde el debe y el haber es cero
        // Como no pude replicar el comportamiento, cerraremos con una validación aquí ésta posibilidad
        // En teoría, ésta validación no es necesaria, ya que filtramos más abajo... pero pasó :(
        if ($epl_id == 'N') {

            $pasoDebe = (float) $d;
            $pasoHaber = (float) $h;

            if ($pasoDebe = 0 and $pasoHaber = 0) {
                $this->mixScript('
                    notify( "El debe y el haber no pueden ser 0", "warning" );
                    setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                    window.espera.stop();
                ');
                return false;
            }
        }

        // Marcos, 30/dic/2015
        // Al desaparecer el campo usr_idModificado para el 2016, quitaremos ésto
        // Si estamos entrando desde el menú de Captura Manual, la tabla es tem_
        if ($op == 'A') {
            $prefijoTabla = BD_TEMPORAL . self::PREFIJO_TEMPORAL;
            $fieldIDAuxiliar = 'tem_pdx';

            // Si estamos editando desde el libro diario, las operaciones son sobre cg_
        } else {
            $prefijoTabla = 'cg_';
            $fieldIDAuxiliar = 'pdx_id';
        }

        // Convertimos a array la cuenta
        $arrCta = explode('.', $cta);
        $ger_id = (!empty($arrCta) ? $arrCta[0] : '');
        $cgp_id = (count($arrCta) > 1 ? $arrCta[1] : '');
        $rub_id = (count($arrCta) > 2 ? $arrCta[2] : '');
        $cgcu_id = (count($arrCta) > 3 ? $arrCta[3] : '');
        $cgsc_id = (count($arrCta) > 4 ? $arrCta[4] : '');

        $cgc = (count($arrCta) > 5 ? $arrCta[5] : '');
        $cta_id = (count($arrCta) > 6 ? $arrCta[6] : '');

        // Marcos: 28/abr/201600  -> actualizado el 10/ene/2018
        // Si tiene un array de 8 elementos, entonces es una cuenta de seis niveles y
        // debemos juntar los niveles 6 y 7 para igualar el valor del campo cta_id
        if (count($arrCta) == 8) {
            $cta_id .= $arrCta[7];
        }

        $ctaMayor = $ger_id . $cgp_id . $rub_id . $cgcu_id . $cgsc_id;
        /// Codigo para validar si existeun bloqueo en la cuenta contable
        $tieneBloqueo = false;

        // Marcos: 28/marzo/2016
        // Si escribieron la clave de financiamiento, ubicamos su encuadre (ecf_id)
        $updateEcf = '';
        $insertEcf = '';

        if ($cveFin != '') {
            $cveFin = strtoupper(str_replace('-', '', $cveFin));
            $this->conectar();
            $this->setSql('SELECT ecf_id FROM enc_ClaveFinanciamiento WHERE ecf_clave = ?');
            $this->consultar([$cveFin]);
            $row = $this->extraer();

            // Si lo encontramos...
            if ($this->getNumeroRegistros() > 0 && $row['ecf_id'] <> '' && $row['ecf_id'] != 'NUL') {
                $updateEcf = 'ecf_id = ' . $row['ecf_id'] . ', ';
                $insertEcf = $row['ecf_id'];
            }
        }
        // Marcos: 03/jul/2017
        // Si escribieron la UR Origen, buscamos que exista
        $gpo_urOrigen = 'NULL';
        $ur_urOrigen = 'NULL';

        if ($gpo_idP . $ur_idP != '') {
            $this->conectar();
            $this->setSql('SELECT count(*) as veces FROM cat_UnidadResponsable WHERE gpo_id = ? AND ur_id = ? ;');
            $this->consultar([$gpo_idP, $ur_idP]);
            $row = $this->extraer();

            // Si no lo encontramos, ponemos vacía la cadena, para evitar que se guarden datos inconsistentes...
            if ($row['veces'] <= 0) {
                $this->mixScript('notify("Es necesario seleccionar Unidad Responsable Valida", "error");window.espera.stop()');
                return false;
            }
            $gpo_urOrigen = $gpo_idP;
            $ur_urOrigen = $ur_idP;
        }

        $estrucCta = 0;
        $this->conectar();
        $this->setSql('SELECT ecu_id from cat_CgSubcuenta
            WHERE ger_id = ? and cgp_id = ? and rub_id = ?
                and cgcu_id = ? and cgsc_id = ? ');
        $this->consultar([$ger_id, $cgp_id, $rub_id, $cgcu_id, $cgsc_id]);
        $row = $this->extraer();

        // Si lo encontramos...
        if ($this->getNumeroRegistros() > 0 && $row['ecu_id'] <> '' && $row['ecu_id'] != 'NULL') {
            $estrucCta = $row['ecu_id'];
        }
        $this->desconectar();

        /*
        Marcos: 25/agosto
        Por solicitud de la CP Hilda, no pueden modificar más que el nivel de registro en la cuenta 56110 automática
        Como las pólizas automáticas que usen ésta cuenta apuntan a la 56110.ur.ue.001 ... validamos que cuando modifiquen
        la cuenta hasta el cuarto nivel de la original y la nueva sean iguales
        Validado con Israel, porque sí permitimos dar de alta nuevas líneas con una cuenta 56110 con un cuarto nivel distinto
        si lo tuvieran, pero no modificar más que su nivel de registro
        Ticket #717765 de la CP Hilda
        Marcos: 14/septiembre
        Quieren corregir el cuarto nivel de 001 a 632 o 631
        Así que Israel autoriza que YA NO verifiquemos que no se modifique a cuarto nivel, sino que simplemnete a
        tercer nivel.
        Marcos: 27/sep/2016
        Israel solicita (ya que hay un ticket de Hilda) que también la cuenta 55490 tenga el
        mismo comportamiento
        Si la nueva es
        */
        if ($estrucCta == 2 && ($ctaMayor == '56110' || $ctaMayor == '55490')) {
            // Marcos: 29/jun/2016
            // para que al adicionar una línea desde la modificación de la póliza, lo permite
            // Marcos: 01/jul/2016
            // Para que sólo cuando sea un cambio, y estén cambiando una cuenta, se valide la regla
            // ya que si es una nueva línea o estamos en una alta de póliza, debe guardarse tal cual
            if (($op == 'C') && $modo == 'C') {
                // DEBE hasta el cuarto nivel ser la misma cuenta ( 9 dígitos : 11111.111 )
                if (substr($ori, 0, 9) != substr($cta, 0, 9)) {
                    $this->mixScript('
                        notify( "La cuenta 5.6.1.1.0 / 5.5.4.9.0 sólo puede modificarse a partir de su cuarto nivel", "warning" );
                        setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                        window.espera.stop();');
                    return false;
                }
            } else {
                $this->mixScript('
                    notify( "No puedes usar cuentas de gasto presupuestal en la captura manual", "warning" );
                    setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                    window.espera.stop();');
                return false;
            }
        }

        // Para evitar goles, validamos aquí también que no graben cuentas de orden presupuestales ( 82 ) en la captura manual
        if ($ger_id == 8 and $cgp_id == 2) {
            $this->mixScript('
                notify( "No puedes usar cuentas de orden presupuestal en la captura manual", "warning" );
                setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                window.espera.stop();
            ');
            return false;
        }

        // Volvemos a validar los campos sensibles, para evitar goles si modifican localmente las validaciones javascript
        // La cuenta no puede quedar vacía ...
        $bolError = false;
        if (trim($this->prm->cta) == '') {
            $bolError = true;
        }   // La cuenta no puede estar vacía
        if ($epl_id != 'H' && ($d == 0 && $h == 0)) {
            $bolError = true;
        }   // el importe no ser cero si no es cheque cancelado

        if ($bolError) {
            $this->mixScript('
                notify( "Escribe cuenta e importes válidos", "warning" );
                setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                window.espera.stop();
            ');
            return false;
        }


        // Si la fecha del documento fuente está vacía o no es válida, grabamos null en la tabla
        $fechaDF = str_replace('-', '/', $fechaDF);
        $trozos = explode('/', $fechaDF, 3);
        $strFechaDF = '';
        if ($fechaDF == '' || count($trozos) != 3) {
            $strFechaDF = 'NULL';
        } else {
            if (strlen($trozos[0]) == 2) {
                // si viene como dia, mes, año
                $fechaValida = checkdate($trozos[1], $trozos[0], $trozos[2]);
            } else { /* si ya viene en formato año, mes, dia */
                // si viene como año, mes, dia
                $fechaValida = checkdate($trozos[1], $trozos[2], $trozos[0]);
            }

            if ($fechaValida === true) {
                $strFechaDF = '"' . date_to_mysqldate($fechaDF) . '"';
            } else {
                $strFechaDF = 'NULL';
            }
        }

        // Marcos: 30/05/2018
        // Apareció en la base de datos un registro con pdx_fechaReferencia = '0000-00-00' así que haremos
        // un parche "tonto" para corregirlo, porque en teoría el código de arriba debió interceptarlo
        if (
            $strFechaDF == '0000-00-00' || $strFechaDF == '0000/00/00' || trim($strFechaDF) == ''
        ) {
            $strFechaDF = 'NULL';
        }

        // Grabamos en el detalle TEMPORAL
        $this->conectar();

        // ==============================================================================================================
        // Si estamos ADICIONANDO UNA NUEVA LINEA
        // ==============================================================================================================
        if ($modo == 'A') {
            // Si estamos grabando a las tablas definitivas, ponemos los valores adecuados para la bitácora
            if ($prefijoTabla == 'cg_') {
                $this->setObservacion('CAPTURA MANUAL, NUEVA LÍNEA');
            }

            // Insertamos la línea solicitada
            // Marcos: 07/sep/2016
            // A partir del 7 de septiembre, se pasa a procedimientos almacenados el control entre las tablas
            // cg_PolizaDetalle y cg_PolizaDetalle auxiliar, por lo que para pdt_id, le pasaremos 0 solamente
            $pdtValue = $prefijoTabla == 'cg_' ? ':pdt, ' : '';
            $ecfValue = $insertEcf != '' ? ':ecf, ' : '';
            $isTemporal = strpos($prefijoTabla, 'tem_') !== false;

            $camposPDT = $prefijoTabla == 'cg_' ? 'pdt_id, ' : '';
            $valoresPDT = $prefijoTabla == 'cg_' ? ':pdt, ' : '';

            $camposECF = $insertEcf != '' ? 'ecf_id, ' : '';
            $valoresECF = $insertEcf != '' ? ':ecf, ' : '';

            $this->setSql("
                INSERT INTO {$prefijoTabla}PolizaDetalleAuxiliar
                (
                    " . ($isTemporal ? 'tem_pma,' : '') . "
                    gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, {$camposPDT}
                    ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,
                    cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id,
                    pdx_debe, pdx_haber, {$camposECF}
                    nat_id, pdx_concepto, rfc_id, pdx_referencia,
                    pdx_fechaReferencia, tdf_id, adf_id, usr_id,
                    pdx_clc_id_origen, pdx_clc_cereCo_vinculacion,
                    pdx_gpo_id_origen, pdx_ur_id_origen,
                    pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion,
                    tem_carga #, ore_idMml
                )
                VALUES (
                    " . ($isTemporal ? ':tem_pma,' : '') . "
                    :gpo, :ur, :ue, :pot, :prf, :msc, :n, {$valoresPDT}
                    :ger, :cgp, :rub, :cgcu, :cgsc,
                    :gpo2, :ur2, :ctaUE, :cgcid, :cta,
                    :d, :h, {$valoresECF}
                    :nat, :ben, :rfcdf, :numdf,
                    {$strFechaDF}, :tipoDF, :gpoDF, :usr,
                    :clc, :cereCo,
                    :gpo_urOrigen, :ur_urOrigen,
                    :gpo_urOrigen2, :ur_urOrigen2,
                    '' #, :n_pipet
                )
            ");
            $params = [
                ':gpo'           => getSession('clasAdm-gpo'),
                ':ur'            => getSession('clasAdm-ur'),
                ':ue'            => $ue,
                ':pot'           => $pot_id,
                ':prf'           => $prf_id,
                ':msc'           => $msc_id,
                ':n'             => $n,
                ':ger'           => $ger_id,
                ':cgp'           => $cgp_id,
                ':rub'           => $rub_id,
                ':cgcu'          => $cgcu_id,
                ':cgsc'          => $cgsc_id,
                ':gpo2'          => getSession('clasAdm-gpo'),
                ':ur2'           => getSession('clasAdm-ur'),
                ':ctaUE'         => $ctaUE,
                ':cgcid'         => $cgc,
                ':cta'           => $cta_id,
                ':d'             => $d,
                ':h'             => $h,
                ':nat'           => $nat,
                ':ben'           => mysql_escape_mimic($benDF),
                ':rfcdf'         => mysql_escape_mimic($rfcDF),
                ':numdf'         => mysql_escape_mimic($numDF),
                ':tipoDF'        => $tipoDF,
                ':gpoDF'         => $gpoDF,
                ':usr'           => getSession('usr_id'),
                ':clc'           => $clc,
                ':cereCo'        => $cereCo,
                ':gpo_urOrigen'  => $gpo_urOrigen,
                ':ur_urOrigen'   => $ur_urOrigen,
                ':gpo_urOrigen2' => $gpo_urOrigen,
                ':ur_urOrigen2'  => $ur_urOrigen,
                ':n_pipet'       => $nPipet,
            ];
            if ($pdtValue) {
                $params[':pdt'] = '0';
            }
            if ($ecfValue) {
                $params[':ecf'] = $insertEcf;
            }
            if ($isTemporal) {
                $params[':tem_pma'] = $n;
            }
            $this->consultar($params);
            $this->desconectar();
        } else {
            // ==============================================================================================================
            // Si estamos MODIFICANDO UNA LINEA EXISTENTE
            // ==============================================================================================================

            // Iniciamos transacción
            // ------------------------------------------------------------------------------------------------
            $this->setSql('START TRANSACTION;');
            $this->consultar();
            $pdt_id_porNuevaCreacion = '';
            $params = [
                ':ger_id'            => $ger_id,
                ':cgp_id'            => $cgp_id,
                ':rub_id'            => $rub_id,
                ':cgcu_id'           => $cgcu_id,
                ':cgsc_id'           => $cgsc_id,
                ':cta_gpo_id'        => getSession('clasAdm-gpo'),
                ':cta_ur_id'         => getSession('clasAdm-ur'),
                ':cta_ue_id'         => $ctaUE,
                ':cgc_id'            => $cgc,
                ':cta_id'            => $cta_id,
                ':pdx_debe'          => $d,
                ':pdx_haber'         => $h,
                ':nat_id'            => $nat,
                ':pdx_concepto'      => mysql_escape_mimic($benDF),
                ':rfc_id'            => mysql_escape_mimic($rfcDF),
                ':pdx_referencia'    => mysql_escape_mimic($numDF),
                ':tdf_id'            => $tipoDF,
                ':adf_id'            => $gpoDF,
                ':pdx_gpo_id_origen' => $gpo_urOrigen,
                ':pdx_ur_id_origen'  => $ur_urOrigen,
                ':gpo'               => getSession('clasAdm-gpo'),
                ':ur'                => getSession('clasAdm-ur'),
                ':ue'                => $ue,
                ':pot_id'            => $pot_id,
                ':prf_id'            => $prf_id,
                ':msc_id'            => $msc_id,
                ':n'                 => $n,
                ':n_pipet'           => $nPipet,
            ];
            $sql = "UPDATE  {$prefijoTabla}PolizaDetalleAuxiliar SET
                    ger_id = :ger_id,
                    cgp_id = :cgp_id,
                    rub_id = :rub_id,
                    cgcu_id = :cgcu_id,
                    cgsc_id = :cgsc_id,
                    cta_gpo_id = :cta_gpo_id,
                    cta_ur_id = :cta_ur_id,
                    cta_ue_id = :cta_ue_id,
                    cgc_id = :cgc_id,
                    cta_id = :cta_id,
                    pdx_debe = :pdx_debe,
                    pdx_haber = :pdx_haber,
                    nat_id = :nat_id,
                    {$updateEcf}
                    pdx_concepto =  :pdx_concepto,
                    rfc_id =  :rfc_id,
                    pdx_referencia =  :pdx_referencia,
                    pdx_fechaReferencia = {$strFechaDF},
                    tdf_id =  :tdf_id,
                    adf_id =  :adf_id,
                    pdx_gpo_id_origen =  :pdx_gpo_id_origen,
                    pdx_ur_id_origen =  :pdx_ur_id_origen
                    #ore_idMml = :n_pipet
                    {$pdt_id_porNuevaCreacion}
                WHERE gpo_id = :gpo and ur_id = :ur AND ue_id = :ue
                AND pot_id = :pot_id AND prf_id = :prf_id AND msc_id = :msc_id AND pma_id = :n  AND {$fieldIDAuxiliar} = {$nL}";

            $this->setObservacion('MODIFICADO POR EL USUARIO');
            $this->setSql($sql);
            $this->consultar($params);
            $this->setSql('COMMIT;');
            $this->consultar();
            // ------------------------------------------------------------------------------------------------
        }

        // SI ES UN ALTA ... Insertamos la línea en el grid
        if ($modo == 'A') {
            $nLinea = $this->getUltimoId();   // Obtenemos el número de linea (pdx_id) que le tocó ...
            $this->mixScript('
                /********************************/
                $(document).ready(function (){
                    $(".seleccionable").click(function() {
                        $(".seleccionable").css("font-weight","normal");
                        $(".seleccionable").removeClass("seleccionado");
                        $(this).addClass("seleccionado");
                        $(this).css("font-weight","bold");

                        $(".seleccionado").dblclick(function(){
                            $("#modificarLinea").click();
                            $("#bdo_Clc").val("");
                            $("#bdo_CereCo").val("");
                        });
                    });

                    $("#registroCtas tbody tr").hover(
                        function() {
                            $(this).addClass("coloreaCtas");
                        },
                        function() {
                            $(this).removeClass("coloreaCtas");
                        }
                    );
                });


                    $("#bdo_Clc").val("");
                    $("#bdo_CereCo").val("");

                    var nuevaFila = "<tr class=seleccionable>";
                        nuevaFila += "<td></td>";
                        nuevaFila += "<td style = \' white-space: pre-wrap\'>' . implode('.', $arrCta) . '</td>";
                        nuevaFila += "<td style = \' white-space: pre-wrap\'>' . mysql_escape_mimic($nom) . '</td>";
                        nuevaFila += "<td style = \' text-align: right; white-space: pre-wrap\'>" + FormatoMoneda(0, 0, ' . $d . ', 0, 0) + "</td>";
                        nuevaFila += "<td style = \' text-align: right; white-space: pre-wrap\'>" + FormatoMoneda(0, 0, ' . $h . ', 0, 0) + "</td>";
                        nuevaFila += "<td style = \' white-space: pre-wrap\'>' . $numDF . '</td>";
                        nuevaFila += "<td style=display:none>' . $nLinea . '</td>";
                        nuevaFila += "<td style=display:none>' . $ue . '</td>";
                        nuevaFila += "</tr>";
                    $("#registroCtas tbody").append(nuevaFila);

                    $("#registroCtas tbody tr").each(function(index, row) {
                        const numeroRegistro = index + 1;
                        $(this).attr("id", index);
                        $(row).find("td:first").text(numeroRegistro);
                        $(row).find("td:first").css("text-align","center");
                    });

                    //Recalculamos la suma del debe y del haber
                    recalcularSumas( 1 );

            ');
        } else {
            $this->mixScript('
                /********************************/
                $(document).ready(function (){
                    $(".seleccionable").click(function() {
                        $(".seleccionable").css("font-weight","normal");
                        $(".seleccionable").removeClass("seleccionado");
                        $(this).addClass("seleccionado");
                        $(this).css("font-weight","bold");

                        $(".seleccionado").dblclick(function(){
                            $("#modificarLinea").click();
                            $("#bdo_Clc").val("");
                            $("#bdo_CereCo").val("");
                        });
                    });

                    $("#registroCtas tbody tr").hover(
                        function() {
                            $(this).addClass("coloreaCtas");
                        },
                        function() {
                            $(this).removeClass("coloreaCtas");
                        }
                    );
                });

                /*******************************************/
                var nuevaFila = "<tr class=seleccionable>";
                    nuevaFila += "<td ></td>";
                    nuevaFila += "<td>' . implode('.', $arrCta) . '</td>";
                    nuevaFila += "<td>' . $nom . '</td>";
                    nuevaFila += "<td style = \' text-align: right; white-space: pre-wrap\'>" + FormatoMoneda(0, 0, ' . $d . ', 0, 0) + "</td>";
                    nuevaFila += "<td style = \' text-align: right; white-space: pre-wrap\'>" + FormatoMoneda(0, 0, ' . $h . ', 0, 0) + "</td>";
                    nuevaFila += "<td>' . $numDF . '</td>";
                    nuevaFila += "<td style = \' display:none;\'>' . $nL . '</td>";
                    nuevaFila += "<td style = \' display:none;\'>' . $ue . '</td>";
                    nuevaFila += "</tr>";
                $(".seleccionado").replaceWith(nuevaFila);


                $("#registroCtas tbody tr").each(function(index, row) {
                    const numeroRegistro = index + 1;
                    $(this).attr("id", index);
                    $(row).find("td:first").text(numeroRegistro);
                    $(row).find("td:first").css("text-align","center");
                });

                // Recalculamos la suma del debe y del haber
                recalcularSumas( 1 )

                // Modo ALTA ( despues de modificar una línea, se queda el DIV en pantalla, así que como queda vacío hay que ponerlo en modo ALTA )
                $("#grabarLinea").attr("modo", "A");

            ');
        }

        return false;
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Guardamos la póliza en las tablas definitivas
    // Pasamos de la tabla temporal a la tabla definitiva
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function guardarPoliza()
    {

        $ue = $this->getPrm('ue', 1);
        $pot_id = $this->getPrm('pot_id', '');
        $prf_id = $this->getPrm('prf_id', '');
        $n = $this->getPrm('n', '');
        $msc_id = $this->getPrm('m', '');
        $d = $this->getPrm('d', '');
        $h = $this->getPrm('h', '');

        // Ultima validación, por si con javascript quieren meternos goles
        $this->setSql('SELECT m.epl_id, SUM( pdx_debe ) AS totDebe, SUM( pdx_haber ) AS totHaber, COUNT(*) AS lineas
            FROM ' . BD_TEMPORAL . '.tem_PolizaDetalleAuxiliar d
            JOIN ' . BD_TEMPORAL . '.tem_PolizaMaestro m
                ON ( d.gpo_id = m.gpo_id AND d.ur_id = m.ur_id AND d.ue_id = m.ue_id AND d.msc_id = m.msc_id
                    AND d.pot_id = m.pot_id AND d.prf_id = m.prf_id AND d.pma_id = m.tem_pma )
            WHERE d.gpo_id = ? AND d.ur_id = ? AND d.pma_id = ?');

        $this->conectar();
        $this->consultar([getSession('clasAdm-gpo'), getSession('clasAdm-ur'), $n]);
        $this->desconectar();
        $row = $this->extraer();

        // Si en este punto no regresa nada ... lo más probable es que hayan movido manualmente los valores en la página
        // es decir, movieron en el HTML de la pagina los valores que guardamos durante la captura de la póliza
        if ($this->getNumeroRegistros() == 0) {
            $this->mixScript('
                notify( "No se encuentra la póliza que se estaba editando ... reporta éste problema a Finanzas", "error" );
                setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                window.espera.stop();
            ');
            return false;
        }
        // La suma de movimientos deudores y acreedores ... debe cuadrar excepto cuando sea de cheque cancelado
        if ($row['totDebe'] != $row['totHaber'] && $row['epl_id'] != 'H') {
            $this->mixScript('
                notify( "En Base de Datos las sumas son : " + formatNumber( ' . $row['totDebe'] . ',"","") + " / " + formatNumber( ' . $row['totHaber'] . ',"",""), "error" );
                setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                window.espera.stop();
            ');
            return false;
        }
        // Deben ser mínimo dos líneas ... excepto cuando se trate de cheques cancelados
        if ($row['lineas'] < 2 && $row['epl_id'] != 'H') {
            $this->mixScript('
                notify( "La póliza debe consistir en al menos dos líneas.   Actualmente tiene ' . $row['lineas'] . '" , "warning" );
                setTimeout( function() { $( "#cuenta" ).select().focus() }, 100 );
                window.espera.stop();
            ');
            return false;
        }

        // Pasamos a la tabla definitiva el MAESTRO
        $this->conectar();

        // El maestro y detalle están dentro de una transacción
        // ------------------------------------------------------------------------------------------------
        $this->setSql('START TRANSACTION;');
        $this->consultar();
        $this->setObservacion('POR CAPTURA MANUAL');
        // Pasamos a la tabla definitiva el MAESTRO
        $this->setSql('INSERT INTO cg_PolizaMaestro( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, pma_debe, pma_haber, pma_concepto, pma_fecha, mmc_id, epl_id, apg_id, tem_pma_id, usr_id )
                SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, 0 AS tem_pma, 0 AS debe, 0 AS haber, pma_concepto, pma_fecha, mmc_id, epl_id, apg_id, pma_id, usr_id
                FROM ' . BD_TEMPORAL . '.tem_PolizaMaestro
                WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?
                    AND pot_id = ? AND prf_id = ? AND msc_id = ? AND tem_pma = ?;');
        $this->consultar([
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
            $ue,
            $pot_id,
            $prf_id,
            $msc_id,
            $n,
        ]);

        // Obtenemos el número de póliza DEFINITIVO que le tocó ...
        $this->setSql('SELECT @pma_id AS pma_id');
        $this->consultar();
        $row = $this->extraer();
        $polNumero = $row['pma_id'];

        // 1. Pasar todo ésto a procedimiento almacenado
        // 2. Checar que se respete el orden de la llave, y usar esa llave en USING
        // 3. En select de detalle agregar toda la llave, para usarla en el using
        // Como pdt_id es autoincrementable.. obtenemos la relación entre cuentas a nivel de mayor
        // y su pdt_id qu le tocó, para que ésos ids se graben en el auxiliar
        $this->bindParams([
            ':gpo' => getSession('clasAdm-gpo'),
            ':ur'  => getSession('clasAdm-ur'),
            ':ue'  => $ue,
            ':pot' => $pot_id,
            ':prf' => $prf_id,
            ':msc' => $msc_id,
            ':pol' => $polNumero,
            ':n'   => $n,
        ]);
        $this->setSql('UPDATE ' . BD_TEMPORAL . '.tem_PolizaDetalleAuxiliar da
                JOIN (
                    SELECT
                        ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, nat_id, pdt_id
                    FROM ' . BD_CTRL . '.cg_PolizaDetalle
                    WHERE
                        gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue
                        AND pot_id = :pot AND prf_id = :prf AND msc_id = :msc
                        AND pma_id = :pol
                    GROUP BY
                        ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, nat_id, pdt_id
                ) d ON
                    da.ger_id = d.ger_id AND
                    da.cgp_id = d.cgp_id AND
                    da.rub_id = d.rub_id AND
                    da.cgcu_id = d.cgcu_id AND
                    da.cgsc_id = d.cgsc_id AND
                    da.nat_id = d.nat_id
                SET
                    da.pdt_id = d.pdt_id
                WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue
                AND pot_id = :pot AND prf_id = :prf AND msc_id = :msc AND pma_id = :n');
        $this->consultar();

        $this->setObservacion('POR CAPTURA MANUAL');
        $params = [
            ':pol' => $polNumero,
            ':gpo' => getSession('clasAdm-gpo'),
            ':ur'  => getSession('clasAdm-ur'),
            ':ue'  => $ue,
            ':pot' => $pot_id,
            ':prf' => $prf_id,
            ':msc' => $msc_id,
            ':n'   => $n,

        ];
        // Pasamos a la tabla definitiva el DETALLEAUXILIAR
        $this->setSql('INSERT INTO cg_PolizaDetalleAuxiliar ( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id, pdt_id,
                    ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id,
                    pdx_debe, pdx_haber, nat_id, pdx_concepto,
                    rfc_id, pdx_referencia, pdx_fechaReferencia, tdf_id, adf_id,
                    epg_id, pg_id, pe_id, ecf_id, pdx_ejercicio,
                    pdx_gpo_id_origen, pdx_ur_id_origen, pdx_clc_id_origen, pdx_afe_id_origen, pdx_cmp_id_origen, pdx_bdo_id_origen,
                    pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, pdx_clc_id_vinculacion ,pdx_afe_id_vinculacion, pdx_cmp_id_vinculacion, pdx_bdo_id_vinculacion,
                    pdx_agrupador, pdx_tipoPresupuestal, tem_carga, tem_pma_id, usr_id, pdx_clc_cereCo_vinculacion )
                SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, :pol AS pma_id, pdt_id,
                    ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id,
                    pdx_debe, pdx_haber, nat_id, pdx_concepto,
                    rfc_id, pdx_referencia, pdx_fechaReferencia, tdf_id, adf_id,
                    epg_id, pg_id, pe_id, ecf_id, pdx_ejercicio,
                    pdx_gpo_id_origen, pdx_ur_id_origen, pdx_clc_id_origen, pdx_afe_id_origen, pdx_cmp_id_origen, pdx_bdo_id_origen,
                    pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, pdx_clc_id_vinculacion ,pdx_afe_id_vinculacion, pdx_cmp_id_vinculacion, pdx_bdo_id_vinculacion,
                    pdx_agrupador, pdx_tipoPresupuestal, tem_carga, tem_pma, usr_id, pdx_clc_cereCo_vinculacion
                FROM ' . BD_TEMPORAL . '.tem_PolizaDetalleAuxiliar
                WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue
                AND pot_id = :pot AND prf_id = :prf AND msc_id = :msc AND pma_id = :n ');
        $this->consultar($params);
        $this->setSql('COMMIT;');
        $this->consultar();
        $this->desconectar();

        // ------------------------------------------------------------------------------------------------
        $this->mixScript('
            // Limpiamos
            $("#fecha").val("");
            $("#concepto").val("");

            $("#EncabezadoPoliza *").prop("disabled",false);
            $("#homeMenuPM").prop("md",0);
            $("#idPolizaTemporal").text( "" ).css("display", "none");
            $("#idPolizaTemporal").attr( "t", "" );
            $("#idPolizaTemporal").attr( "p", "" );
            $("#idPolizaTemporal").attr( "n", "" );
            $("#idPolizaTemporal").attr( "m", "" );
            $("#grabarEncabezado").show();
            $("#recuperarPoliza").show();
            $("#grabarPoliza").hide();
            $("#page-tabs a[href=\"1\"]").click();
            $("#formaDetallePoliza").css("display", "none" );
            $("#myGridDetallePoliza").css("display", "none" );

            alertify.alert("POLIZA GUARDADA", "Se ha guardado la póliza ' . $pot_id . '-' . $prf_id . $polNumero . '");

            $("#chequeC").prop("checked", false);
            $("#EsChequeCancelado").css("display", "none" );

            setTimeout( function() { $( "#pot_id" ).focus(); }, 500 );

            window.espera.stop();

        ');

        return false;
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Permitimos actualizar el día de la fecha de la póliza, y el concepto en el caso de la modificación del encabezado, solamente
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function actualizarEncabezado()
    {

        $ue = $this->getPrm('ue', 1);
        $pot_id = $this->getPrm('pot_id', '');
        $prf_id = $this->getPrm('prf_id', '');
        $n = $this->getPrm('n', '');
        $msc_id = $this->getPrm('m', '');
        $d = $this->getPrm('d', '');
        $c = strtoupper((string)$this->getPrm('c', ''));
        $apg = $this->getPrm('apg', '');

        // Validamos aquí
        if (trim($c) == '') {
            $this->mixScript('
                notify( "El concepto de la póliza no puede quedar vacío", "warning" );
                setTimeout( function() { $( "#concepto" ).select().focus() }, 100 );
                window.espera.stop();
            ');
            return false;
        }
        // y el mes debe estar abierto
        $this->conectar();
        $this->setSql('SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? AND msc_id = ?');
        $this->consultar([getSession('clasAdm-gpo'), getSession('clasAdm-ur'), $msc_id]);
        $row = $this->extraer();
        $valido = ($this->getNumeroRegistros() > 0 && $row['ems_id'] == 'A');

        if (!$valido) {
            $this->desconectar();
            $this->mixScript('
                notify( "El mes de ' . MesNombre($msc_id) . ' está cerrado", "warning" );
                setTimeout( function() { $( "#fecha" ).select().focus() }, 100 );
                window.espera.stop();
            ');
            return false;
        }

        $strCampoFecha = '';
        // Sólo reemplazamos el día de la póliza si pasaron el dato de día
        if (trim((string)$d) != '') {
            $strCampoFecha = 'pma_fecha =
                CASE
                    ISNULL( DATE( CONCAT( YEAR( pma_fecha ), "-", MONTH( pma_fecha ), "-", "' . $d . '" ) ) )
                    WHEN TRUE THEN pma_fecha
                    ELSE DATE( CONCAT( YEAR( pma_fecha ), "-", MONTH( pma_fecha ), "-", "' . $d . '" ) ) END, ';
        }

        // Actualizamos el encabezado de la póliza
        // Reemplazamos la fecha siempre y cuando el día sea válido
        $this->setObservacion('USUARIO MODIFICA EL DIA DE LA PÓLIZA');
        $this->setSql("UPDATE cg_PolizaMaestro
            SET {$strCampoFecha} pma_concepto = :concepto, apg_id =:apg
            WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue
                AND pot_id = :pot AND prf_id = :prf AND msc_id = :msc AND pma_id = :n ;");
        $this->consultar([
            ':concepto' => mysql_escape_mimic(trim($c)),
            ':apg'      => mysql_escape_mimic(trim((string)$apg)),
            ':gpo'      => getSession('clasAdm-gpo'),
            ':ur'       => getSession('clasAdm-ur'),
            ':ue'       => $ue,
            ':pot'      => $pot_id,
            ':prf'      => $prf_id,
            ':msc'      => $msc_id,
            ':n'        => $n,
        ]);
        $this->desconectar();

        $dateFormatted = join('-', [
            str_pad((string)$d, 2, '0', STR_PAD_LEFT),
            str_pad((string)$msc_id, 2, '0', STR_PAD_LEFT),
            substr($_SESSION[_S]['ejercicio'], 0, 4),
        ]);

        $jsExtra = (trim((string)$d) != '' ? ' $("#fecha").val("' . $dateFormatted . '");' : '');
        $this->mixScript('
            notify( "Se han actualizado los datos del encabezado", "success" );
            ' . $jsExtra . '
            setTimeout( function() { $( "#concepto" ).focus(); }, 500 );
            window.espera.stop();
        ');

        return false;
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Validamos la cuenta contable que pongan en el campo de cuenta
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function ValidaCuenta()
    {

        $ue = (isset($this->prm->ue) ? $this->prm->ue : '');
        $ue = trim($ue);
        $bolModifiqueCuenta = false;

        // Marcos: 08/abr/2016
        // Para determinar qué UE manejaremos
        // Si es de nivel UR, es la ue contable
        // si es de nivel UE, es la asignada al usuario
        $ueContable = (isset($this->prm->ueCont) ? $this->prm->ueCont : '');

        // Entonces, si no escribieron la UE... y sí tiene un valor la ueContable, tomaremos ésa
        if ($ue == '' && $ueContable != '') {
            $ue = $ueContable;
        }

        $cta = (isset($this->prm->cta_id) ? trim($this->prm->cta_id) : '');

        // Para éste punto... la validación YA debe haberle puesto los puntos, así
        // que validamos la longitud para ver si podemos supone que antepusieron la UE
        if (strpos($cta, '.') !== false) {
            // si la cuenta mide 15 caracters, asumimos que antepusieron la UE
            // 0011.1.1.3.1.001.0001
            if (strlen($cta) == 21) {
                // le ponemos los puntos siguiendo el formato 11111.000.1111
                $ue = substr($cta, 0, 3);
                $cta = substr($cta, 3);
                $ctaMod = $cta;
                $bolModifiqueCuenta = true;
            }
        }


        // Marcos: 10/ene/2018
        // TODOS los niveles de la cuenta (incluyenlo los niveles de mayor-conac) DEBEN venir separados por un punto
        // El mínimo son 6 : 1.1.1.3.1.001.0001
        if (substr_count($cta, '.') < 6) {
            $this->mixScript('notify("La cuenta DEBE separar cada uno de sus niveles con un punto", "error");');
            return false;
        }

        // Convertimos a array la cuenta
        $arrCta = explode('.', $cta);

        if (strlen($this->prm->cta_id) > 5) {

            $ger_id = (!empty($arrCta) ? $arrCta[0] : '');
            $cgp_id = (count($arrCta) > 1 ? $arrCta[1] : '');
            $rub_id = (count($arrCta) > 2 ? $arrCta[2] : '');
            $cgcu_id = (count($arrCta) > 3 ? $arrCta[3] : '');
            $cgsc_id = (count($arrCta) > 4 ? $arrCta[4] : '');

            $cgc = (count($arrCta) > 5 ? $arrCta[5] : '');
            $cta = (count($arrCta) > 6 ? $arrCta[6] : '');

            // Marcos: 28/abr/201600  -> actualizado el 10/ene/2018
            // Si tiene un array de 8 elementos, entonces es una cuenta de seis niveles y
            // debemos juntar los niveles 6 y 7 para igualar el valor del campo cta_id
            if (count($arrCta) == 8) {
                $cta .= $arrCta[7];
            }

            // Todos los elementos DEBEN ser números, si no truena el SQL
            if (
                !is_numeric($ger_id) || !is_numeric($cgp_id) || !is_numeric($rub_id)
                || !is_numeric($cgcu_id) || !is_numeric($cgsc_id)
            ) {
                $this->mixScript('notify("Todos los elementos de la cuenta de mayor DEBEN ser números", "warning");');
                return false;
            }

            $this->conectar();
            $ueValue = (getSession('clasAdm-ue', true) ? getSession('clasAdm-ue') : (strlen($ue) == 3 ? $ue : null));
            $ueField = ($ueValue ? ' AND cta_ue_id = :ue ' : '');

            $sql =
                "SELECT ecu_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id, cta_cuenta, cta_nombre
                    FROM cg_PlanCuentas
                    INNER JOIN cat_CgSubcuenta USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                    WHERE ger_id = :ger AND cgp_id = :cgp AND rub_id = :rub AND cgcu_id = :cgcu AND cgsc_id = :cgsc
                        AND cta_gpo_id = :gpo AND cta_ur_id = :ur {$ueField}
                        AND cgc_id = :cgc
                        AND cta_id = :cta
                ";

            $params = [
                ':ger'  => $ger_id,
                ':cgp'  => $cgp_id,
                ':rub'  => $rub_id,
                ':cgcu' => $cgcu_id,
                ':cgsc' => $cgsc_id,
                ':gpo'  => getSession('clasAdm-gpo'),
                ':ur'   => getSession('clasAdm-ur'),
                ':cgc'  => $cgc,
                ':cta'  => $cta,

            ];
            if ($ueValue) {
                $params[':ue'] = $ueValue;
            }

            $this->setSql($sql);
            $this->consultar($params);
            $this->desconectar();

            if (!($row = $this->extraer())) {
                $this->mixScript('
                    $("#nombreCuenta").val("");
                    //$("#pipet").hide();
                    //$("#ore_idMml").val("");
                    //$("#numerObra").hide();
                    //$("#numObra").val("");
                    //$("#nombreObra").hide();
                    //$("#nomObra").val("");
                    notify( "La cuenta no existe en el Plan de Cuentas", "error" );
                ');
            } else {
                if (
                    $row['ecu_id'] == 2 &&
                    ($ger_id . $cgp_id . $rub_id . $cgcu_id . $cgsc_id != 56110) &&
                    !($ger_id . $cgp_id . $rub_id . $cgcu_id . $cgsc_id == 55490 &&
                        claveClasAdm() == 511)
                ) {
                    // Marcos: 27/sep/2016
                    // En el 2014 estaba liberada para CEVI, así que sólo ajustaremos que la 55490 la pueda usar en captura manual la ur 511
                    $this->mixScript('
                        notify("No puedes usar cuentas presupuestales en la captura manual", "warning" );
                        setTimeout( function() { $( "#cuenta" ).val("").focus() }, 100 );
                    ');
                } else {
                    // Si le pusimos puntos a la cuenta, la ponemos en el DOM
                    if ($bolModifiqueCuenta) {
                        $this->mixScript('$("#cuenta").val("' . $ctaMod . '");');
                    }
                    // Ponemos la descripción de la cuenta
                    // y la UE que regresó la consulta
                    $this->mixScript('
                        $("#ue_cta").html("<font color=\"red\">UE : ' . mysql_escape_mimic($row['cta_ue_id']) . '</font>");
                        $("#nombreCuenta").val("' . mysql_escape_mimic($row['cta_nombre']) . '");
                        $("#nombreCuenta").attr("deUE", "' . $row['cta_ue_id'] . '")
                    ');
                }
            }
        } else {
            $this->mixScript('notify("Por favor, escribe una cuenta contable válida", "warning");');
        }
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // Cuando se trata de un cambio ... cargamos la póliza que quieren modificar
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    private function cargaPoliza()
    {
        $msc_id = $this->getPrm('m', '');
        $pot_id = $this->getPrm('p', '');
        $prf_id = $this->getPrm('r', '');
        $numero = $this->getPrm('n', '');
        $ue = $this->getPrm('ue', '');

        $params = [
            ':msc'    => $this->getPrm('m', ''),
            ':pot'    => $this->getPrm('p', ''),
            ':prf'    => $this->getPrm('r', ''),
            ':numero' => $this->getPrm('n', ''),
            ':ue'     => $this->getPrm('ue', ''),
            ':gpo'    => getSession('clasAdm-gpo'),
            ':ur'     => getSession('clasAdm-ur'),
        ];

        // Obtenemos los datos del maestro
        // ----------------------------------------------------------------------------------------------------------------
        $this->conectar();
        $this->setSql(
            'SELECT m.apg_id, ap.apg_nombre, prf.prf_nombre,
            CASE TRIM( CONCAT( cu.usr_nombre, " ", cu.usr_paterno, " ", cu.usr_materno ) ) WHEN "" THEN u.usr_usuario ELSE CONCAT( cu.usr_nombre, " ", cu.usr_paterno, " ", cu.usr_materno ) END AS elaboro,
            m.epl_id, pot.pot_nombre, DATE_FORMAT( m.pma_fecha, "%d-%m-%Y" ) AS fecha, m.pma_concepto, m.pma_debe
            FROM cg_PolizaMaestro m
                INNER JOIN cat_AgrupacionPoliza ap USING( apg_id )
                INNER JOIN cat_PrefijoPoliza prf USING( prf_id )
                INNER JOIN cat_TipoPoliza pot USING( pot_id )
                INNER JOIN cat_Usuario u ON m.usr_id = u.usr_id
                INNER JOIN  ' . BD_USUARIO . '.cat_Usuario cu ON u.usr_id = cu.usr_id
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur AND m.ue_id = :ue
                AND m.pot_id = :pot AND m.prf_id = :prf AND m.msc_id = :msc AND m.pma_id = :numero'
        );
        $this->bindParams($params);
        $this->consultar();
        $row = $this->extraer();
        $this->desconectar();
        if (!$row) {
            return false;
        }
        $this->mixScript('
            // Para indicar que es un cambio
            $("#homeMenuPM").attr( "op", "C" );

            $("#boxclose").click();
            $("#adicionarLinea").attr("style", "display: none");   // Porque la función de la linea de arriba lo activa...

            // Cargamos en Maestro
            if("' . $row['apg_id'] . '" != "FE" && "' . $row['apg_id'] . '" != "GRAL") {
                let apgId = $("#apg_id");
                apgId.empty();
                apgId.prop("disabled", true);
                apgId.css({
                    "background-color": "#eee",
                    "opacity": "0.6",
                    "pointer-events": "none"
                });
                let option = $("<option></option>").attr("value", "' . mysql_escape_mimic($row['apg_id']) . '").text("' . mysql_escape_mimic($row['apg_id']) . ' - ' . mysql_escape_mimic($row['apg_nombre']) . '");
                apgId.append(option);
            }
            $("#apg_id").val("' . mysql_escape_mimic($row['apg_id']) . '");
            $("#prf_id").val("' . mysql_escape_mimic($row['prf_nombre']) . '");
            $("#elaboro").val("' . mysql_escape_mimic($row['elaboro']) . '");
            $("#chequeC").prop("checked", ' . (($row['epl_id'] == 'H') ? 'true' : 'false') . ');
            if( $("#chequeC").prop("checked") == true ) {
                $("#EsChequeCancelado").css("display", "inline" );
            } else {
                $("#EsChequeCancelado").css("display", "none" );
            }
            if($("#apg_id").val() == "FE") {
                $("#apg_id").css("background-color","lime");
                $("#esFlujoEfectivo").css("display", "inline");
            }
            else {
                $("#apg_id").css("background-color","white");
                $("#esFlujoEfectivo").css("display", "none");
            }

            $("#chequeC").change();
            $("#pot_id").val("' . $row['pot_nombre'] . '");
            $("#fecha").val("' . $row['fecha'] . '");
            $("#concepto").val("' . mysql_escape_mimic($row['pma_concepto']) . '");

            // Cargamos el detalle
            CargaDetallePoliza( "' . $pot_id . '", "' . $prf_id . '", ' . $numero . ', ' . (($msc_id
            == '') ? 99 : $msc_id) . ', "' . $ue . '", "' . $row['epl_id'] . '" );

            $("#idPolizaTemporal").text( "Póliza : ' . $pot_id . '-' . $prf_id . $numero . '" ).css("display", "inline");
            $("#idPolizaTemporal").attr( "t", "' . $pot_id . '" );
            $("#idPolizaTemporal").attr( "p", "' . $prf_id . '" );
            $("#idPolizaTemporal").attr( "n", ' . $numero . ');
            $("#idPolizaTemporal").attr( "m", "' . $msc_id . '" );
            $("#formaDetallePoliza").css("display", "inline" );
            $("#myGridDetallePoliza").css("display", "inline" );

            $("#grabarEncabezado").hide();
            $("#recuperarPoliza").hide();
            $("#page-tabs a[href=\"2\"]").click();
            $("#formaDetallePoliza").css("display", "inline" );
            $("#myGridDetallePoliza").css("display", "inline" );
            $("#apg_id").change(function( e ){
                e.preventDefault();
                if($("#apg_id").val() == "FE") {
                    $("#apg_id").css("background-color","lime");
                    $("#esFlujoEfectivo").css("display", "inline");
                }
                else {
                    $("#apg_id").css("background-color","white");
                    $("#esFlujoEfectivo").css("display", "none");
                }
                return false;
            });
            setTimeout( function() { $( "#cuenta" ).focus(); }, 500 );
        ');
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // Cuando se trata de un alta, pueden cargar una póliza temporal ...
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    private function cargaPolizaTemporal()
    {
        $pot_id = $this->getPrm('p', '');
        $prf_id = $this->getPrm('r', '');
        $numero = $this->getPrm('n', '');

        $params = [
            ':pot_id' => $this->getPrm('p', ''),
            ':prf_id' => $this->getPrm('r', ''),
            ':numero' => $this->getPrm('n', ''),
            ':ue'     => $this->getPrm('ue', ''),
            ':gpo'    => getSession('clasAdm-gpo'),
            ':ur'     => getSession('clasAdm-ur'),
        ];
        // Obtenemos los datos del maestro
        // ----------------------------------------------------------------------------------------------------------------
        $this->conectar();
        $this->setSql('SELECT tem_pma,apg_id, prf_id, epl_id, pot_id, msc_id, DATE_FORMAT( pma_fecha, "%d/%m/%Y" ) AS fecha, pma_concepto
            FROM ' . BD_TEMPORAL . '.tem_PolizaMaestro
            WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue
            AND pot_id = :pot_id AND prf_id = :prf_id AND tem_pma = :numero');
        $this->consultar($params);
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {

            $row = $this->extraer();
            $this->mixScript('
                // Cargamos en Maestro
                $("#apg_id").val("' . $row['apg_id'] . '");
                if ($("#apg_id").val() == "FE") {
                    $("#apg_id").css("background-color","lime");
                    $("#esFlujoEfectivo").css("display", "inline");
                }
                $("#prf_id").val("' . $row['prf_id'] . '");
                $("#chequeC").prop("checked", ' . (($row['epl_id'] == 'H') ? 'true' : 'false') . ');
                if( $("#chequeC").prop("checked") == true ) {
                    $("#EsChequeCancelado").css("display", "inline" );
                } else {
                    $("#EsChequeCancelado").css("display", "none" );
                }

                $("#pot_id").val("' . $row['pot_id'] . '");
                $("#fecha").val("' . $row['fecha'] . '");
                $("#concepto").val("' . $row['pma_concepto'] . '");

                // Guardamos el número de póliza temporal original ... que usaremos para cargar el detalle
                // Cuando activen el botónd de guardarEncabezado
                $("#idPolizaTemporal").attr( "npTo", "' . $numero . '" );

                setTimeout( function() { $("#chequeC").change(); }, 500 );

            ');
        } else {
            $this->mixScript('notify("No existe la Póliza TEMPORAL ' . $pot_id . '-' . $prf_id . $numero . '", "warning" );');
        }
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // Eliminamos una línea de la póliza contable
    // si es ALTA eliminamos de tem_PolizaDetalle ... si es CAMBIO eliminamos de cg_PolizaDetalle
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    private function eliminarLinea()
    {

        $ue = $this->getPrm('ue', '');
        $p = $this->getPrm('p', '');
        $r = $this->getPrm('r', '');
        $n = $this->getPrm('n', '');
        $m = $this->getPrm('m', '');
        $c = $this->getPrm('c', '');
        $l = $this->getPrm('l', '');
        $op = $this->getPrm('op', '');
        $d = $this->getPrm('d', '');
        $h = $this->getPrm('h', '');
        $nat = ($d != 0) ? 'D' : 'H';
        // Si la cuenta es de estructura 2, no podemos modificarla ...
        // 82400.802.001.632.EABBA0114 => 23 dígitos ...
        // Convertimos a array la cuenta
        $arrCta = explode('.', (string)$c);

        // Desagregamos la cuenta contable en partes para grabarla en la bd
        if (count($arrCta) >= 5) {
            $ctaMayor = $arrCta[0] . $arrCta[1] . $arrCta[2] . $arrCta[3] . $arrCta[4];
            $ger_id = ($arrCta[0]);
            $cgp_id = ($arrCta[1]);
            $rub_id = ($arrCta[2]);
            $cgcu_id = ($arrCta[3]);
            $cgsc_id = ($arrCta[4]);
        } else {
            $ctaMayor = $arrCta[0];
            $ger_id = substr($arrCta[0], 0, 1);
            $cgp_id = substr($arrCta[0], 1, 1);
            $rub_id = substr($arrCta[0], 2, 1);
            $cgcu_id = substr($arrCta[0], 3, 1);
            $cgsc_id = substr($arrCta[0], 4, 2);
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $estrucCta = 0;
        $this->conectar();
        $this->setSql('SELECT ecu_id from cat_CgSubcuenta WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND  cgsc_id = ?');
        $this->consultar([$ger_id, $cgp_id, $rub_id, $cgcu_id, $cgsc_id]);
        $row = $this->extraer();

        // Si lo encontramos...
        if ($this->getNumeroRegistros() > 0 && $row['ecu_id'] <> '' && $row['ecu_id'] != 'NULL') {
            $estrucCta = $row['ecu_id'];
        }
        $this->desconectar();

        if ($estrucCta == 2) {
            $this->mixScript('notify("No puedes eliminar cuentas de estructura Presupuestal", "warning" );');
            return false;
        }

        if (strlen(trim((string)$c)) == 23) {
            $this->mixScript('notify("No puedes eliminar cuentas de estructura Presupuestal", "warning" );');
            return false;
        }

        // Determinamos la tabla a afectar
        if ($op == 'A') {
            $prefijoTabla = BD_TEMPORAL . self::PREFIJO_TEMPORAL;
            $fieldIDAuxiliar = 'tem_pdx';
        } else {
            $prefijoTabla = 'cg_';
            $fieldIDAuxiliar = 'pdx_id';
        }

        // 1. Determinamos si ésta línea que estoy modificando es la única que usa ésta cuenta
        // a nivel de mayor, con ésta naturaleza en cgPOlizaDetalleAuxiliar
        // Y QUE SEA DIFERENTE A LA LINEA QUE MODIFICAMOS
        $sql = "SELECT COUNT(*) AS veces FROM {$prefijoTabla}PolizaDetalleAuxiliar
            WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue
                AND pot_id = :pot_id
                AND prf_id = :prf_id
                AND msc_id = :msc_id
                AND pma_id = :pma_id
                AND ger_id  = :ger_id
                AND cgp_id  = :cgp_id
                AND rub_id  = :rub_id
                AND cgcu_id = :cgcu_id
                AND cgsc_id = :cgsc_id
                AND nat_id = :nat_id
                AND {$fieldIDAuxiliar} <> {$l};";
        $params = [
            ':gpo'     => getSession('clasAdm-gpo'),
            ':ur'      => getSession('clasAdm-ur'),
            ':ue'      => $ue,
            ':pot_id'  => $p,
            ':prf_id'  => $r,
            ':msc_id'  => $m,
            ':pma_id'  => $n,
            ':ger_id'  => $ger_id,
            ':cgp_id'  => $cgp_id,
            ':rub_id'  => $rub_id,
            ':cgcu_id' => $cgcu_id,
            ':cgsc_id' => $cgsc_id,
            ':nat_id'  => $nat,
        ];
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $row = $conexion->extraer();
        $nVecesEnAuxiliar = $row['veces'];

        if ($prefijoTabla == 'cg_') {
            $conexion->setObservacion('LINEA ELIMINADA POR EL USUARIO');
        }

        $params = [
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
            $ue,
            $p,
            $r,
            $m,
            $n,
        ];
        $sql = "DELETE FROM {$prefijoTabla}PolizaDetalleAuxiliar
            WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?
                AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ?
                AND {$fieldIDAuxiliar} = {$l}";
        $conexion->setObservacion('SIN OBSERVACION');
        $conexion->setSql($sql);
        $conexion->consultar($params);

        $conexion->desconectar();

        $this->mixScript('
            $("#' . $this->prm->t . '") .remove ();
            // Recalculamos la suma del debe y del haber
            recalcularSumas( 1 );
            $("#registroCtas tbody tr").each(function(index, row) {
                const numeroRegistro = index + 1;
                $(this).attr("id", index);
                $(row).find("td:first").text(numeroRegistro);
            });
            notify("Línea eliminada", "warning" );
        ');

        return false;
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // Modificamos una línea de la póliza contable
    // si es ALTA cargamos la info de la línea desde tem_PolizaDetalle ... si es CAMBIO desde de cg_PolizaDetalle
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    private function modificarLinea()
    {

        $ue = $this->getPRm('ue', '');
        $p = $this->getPRm('p', '');
        $r = $this->getPRm('r', '');
        $n = $this->getPRm('n', '');
        $m = $this->getPRm('m', '');
        $c = $this->getPRm('c', '');
        $l = $this->getPRm('l', 0);
        $op = $this->getPRm('op', '');
        $id = $this->getPRm('id', '');

        // Determinamos la tabla a afectar
        if ($op == 'A') {
            $prefijoTabla = ' ' . BD_TEMPORAL . self::PREFIJO_TEMPORAL;
            $fieldIDAuxiliar = 'tem_pdx';
            $InnerJoinMaestro = ' ';
            $columna = '';
        } else {
            $prefijoTabla = 'cg_';
            $fieldIDAuxiliar = 'pdx_id';
            $InnerJoinMaestro = ' INNER JOIN cg_PolizaMaestro  USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id) ';
            $columna = '  ,mmc_id ';
        }

        $sql =
            "SELECT pdx_ejercicio,
            CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', RIGHT(cgsc_id,1), '.', cgc_id, '.', cta_id ) AS cta,
            p.cta_nombre AS nom, pdx_debe AS d, pdx_haber AS h, nat_id AS nat,
            pdx_referencia AS df, DATE_FORMAT   (pdx_fechaReferencia, \"%d/%m/%Y\") AS dfF, rfc_id AS rfc, pdx_concepto AS dfC,
            tdf_id, adf_id, pdx_id AS nL, pdx_clc_id_origen AS clc, cta_ue_id AS ue,
            ecf_id, pdx_gpo_id_origen, pdx_clc_cereCo_vinculacion as cereCo, pdx_ur_id_origen  {$columna}
        FROM {$prefijoTabla}PolizaDetalleAuxiliar
            {$InnerJoinMaestro}
            INNER JOIN cg_PlanCuentas p USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id )
            WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue AND pot_id = :pot AND prf_id = :prf
                AND msc_id = :msc AND pma_id = :pma  AND {$fieldIDAuxiliar}  = {$l}";
        $params = [
            ':gpo' => getSession('clasAdm-gpo'),
            ':ur'  => getSession('clasAdm-ur'),
            ':ue'  => $ue,
            ':pot' => $p,
            ':prf' => $r,
            ':msc' => $m,
            ':pma' => $n,
        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);

        if ($conexion->getNumeroRegistros() > 0) {
            $row = $conexion->extraer();
            $cf = '';
            $reg = '';

            // Si trae ecf_id... obtenemos la clave de financiamiento
            if (trim($row['ecf_id']) != '' && $row['ecf_id'] != 'NULL') {
                $conexion->setSql('SELECT COUNT(ecf_clave) AS noreg FROM enc_ClaveFinanciamiento WHERE ecf_id = ' . $row['ecf_id'] . ';');
                $conexion->consultar();
                $reg = $conexion->extraer();
                if ($reg['noreg'] == 0) {

                    $regpmas = '';
                    $conexion->setSql('SELECT COUNT(ecf_clave) AS noregpmas FROM ctrl' . $row['pdx_ejercicio'] . '.enc_ClaveFinanciamiento WHERE ecf_id = ' . $row['ecf_id'] . ';');
                    $conexion->consultar();
                    $regpmas = $conexion->extraer();
                    if ($regpmas['noregpmas'] == 0) {
                        $conexion->setSql('SELECT ecf_clave FROM ' . BD_CTRL . '.enc_ClaveFinanciamiento WHERE ecf_id = ' . $row['ecf_id'] . ';');
                        $conexion->consultar();
                        $cf = $conexion->extraer();
                        if ($cf['ecf_clave'] != '' || $cf['ecf_clave'] != 'NULL') {
                            $cf = $cf['ecf_clave'];
                        }
                    } else {
                        $conexion->setSql('SELECT ecf_clave FROM ctrl' . $row['pdx_ejercicio'] . '.enc_ClaveFinanciamiento WHERE ecf_id = ' . $row['ecf_id'] . ';');
                        $conexion->consultar();
                        $cf = $conexion->extraer();
                        if ($cf['ecf_clave'] != '' || $cf['ecf_clave'] != 'NULL') {
                            $cf = $cf['ecf_clave'];
                        }
                    }
                } else {
                    $conexion->setSql('SELECT ecf_clave FROM enc_ClaveFinanciamiento WHERE ecf_id = ' . $row['ecf_id'] . ';');
                    $conexion->consultar();
                    $cf = $conexion->extraer();
                    if ($cf['ecf_clave'] != '' || $cf['ecf_clave'] != 'NULL') {
                        $cf = $cf['ecf_clave'];
                    }
                }
            }

            if ($row['pdx_gpo_id_origen'] != null) {
                $urOrigen = $row['pdx_gpo_id_origen'] . $row['pdx_ur_id_origen'];
            } else {
                $urOrigen = '';
            }


            // Marcos: 09/jun/2017
            // Estaba el tipo de documento fuente SAP para todas las URs, hoy se filtró para que lo tuviera sólo la
            // 116.   Pero ya existen registros grabados de otras URs con ésta agrupación.
            // Por lo tanto, si NO es la 116 y tiene ésta agrupación, se modificará a GRAL
            if (claveClasAdm() != 116) {
                // Forzamos a que sea GRAL si es SAP
                if ($row['adf_id'] == 'SAP') {
                    $row['adf_id'] = 'GRAL';
                }
            }

            // Llenamos los campos con la información de la línea SeleccionaTabulador
            $this->mixScript('

                $("#divEdicionLineaPoliza").show();
                $("#DetallePoliza").height( $(window).height() - $("#areaBotones").height() - 95 );

                $("#cuenta").val("' . $row['cta'] . '");
                $("#cuenta").attr("ori", "' . $row['cta'] . '");
                $("#nombreCuenta").val("' . mysql_escape_mimic($row['nom']) . '");
                $("#nombreCuenta").attr("deUEOri", "' . $row['ue'] . '");
                $("#nombreCuenta").attr("deUE", "' . $row['ue'] . '");
                $("#numeroDoctoFuente").val("' . $row['df'] . '");
                $("#fechaDoctoFuente").val("' . $row['dfF'] . '");
                $("#rfcDoctoFuente").val("' . $row['rfc'] . '");
                $("#beneficiario").val("' . mysql_escape_mimic($row['dfC']) . '");
                $("#tdf_id").val("' . $row['tdf_id'] . '");
                $("#adf_id").val("' . $row['adf_id'] . '");
                $("#bdo_Clc").val("' . $row['clc'] . '");
                $("#bdo_CereCo").val("' . $row['cereCo'] . '");
                $("#cveFin").val("' . $cf . '");
                $("#urOrigen").val("' . $urOrigen . '");
                $("#urOrigen option[value=\"' . $urOrigen . '\"]").attr("selected",true);
                $("#urOrigen").change();
                ' . ((isset($row['mmc_id']) && $row['mmc_id'] == 'L' && $this->esFondos) ? '' : ' $("#urOrigen").attr("disabled", "disabled");')
                . (($op == 'A' && $this->esFondos) ? '' : '$("#urOrigen").attr("disabled", "disabled");')
                . '$("#cuenta").attr("natOri", "' . $row['nat'] . '");
                $("#detalleDocumentoFuente").hide();
                $("#ue_cta").html("<font color=\"red\">UE : ' . mysql_escape_mimic($row['ue']) . '</font>");

                var cta = "' . $row['cta'] . '";
                /*switch(cta.substring(0, 7)){
                    case "1.2.3.5":
                    case "1.2.3.6":
                        var pip = $("#idPip").val();
                        if(pip != ""){
                            $.ajax({
                                url : "scg/poliza/ControladorPolizaManual.php",
                                type : "POST",
                                dataType : "json",
                                data : {
                                    metodo : "buscaPipet",
                                    ore_idMml : pip
                                },
                                success : function(datos){
                                    $.each(datos, function(index, row){
                                        $("#pipet").hide()
                                        $("#pipet2").show();
                                        $("#numPipet").val(pip);
                                        $("#numerObra").show();
                                        $("#numObra").val(row.oa_numObra);
                                        $("#nombreObra").show()
                                        $("#nomObra").val(row.oa_nombre);
                                    });
                                }
                            });
                        }else{
                            $("#pipet").show(); $("#pipet2").hide(); $("#numPipet").val(""); $("#numerObra").show(); $("#nombreObra").show();
                        }
                    break;
                    default:
                       $("#pipet").hide();
                       $("#ore_idMml").val("");
                       $("#pipet2").hide();
                       $("#numPipet").val("");
                       $("#numerObra").hide();
                       $("#numObra").val("");
                       $("#nombreObra").hide();
                       $("#nomObra").val("");
                    break;
                }*/

                // Modo CAMBIO
                $("#grabarLinea").attr("modo", "C");
                $("#grabarLinea").attr("l", ' . $l . ');

                setTimeout( function() { $( "#cuenta" ).focus() }, 100 );
                window.espera.stop();
            ');

            if ($row['nat'] == 'D') {
                $this->mixScript('
                    $("#debe").val("' . $row['d'] . '");
                    $("#haber").val("");
                    $("#debe").prop("disabled",false);
                    $("#haber").prop("disabled",true);
                ');
            } else {
                $this->mixScript('
                    $("#debe").val("");
                    $("#haber").val("' . $row['h'] . '");
                    $("#debe").prop("disabled",true);
                    $("#haber").prop("disabled",false);
                ');
            }
            // Si tiene número de documento fuente, mostramos los datos
            if ($row['df'] != '') {
                $this->mixScript('$("#detalleDocumentoFuente").show();');
            } else {
                $this->mixScript('$("#detalleDocumentoFuente").hide();');
            }
        }
        $conexion->desconectar();

        return false;
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // Obtenemos los totales generales ( si es captura de una póliza nueva, vamos a la tabla tem_
    // si es un cambio, vamos a la tabla cg_ )
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    private function dameTotales()
    {

        $ue = $this->getPrm('ue', '');
        $p = $this->getPrm('p', '');
        $r = $this->getPrm('r', '');
        $n = $this->getPrm('n', '');
        $m = $this->getPrm('m', '');
        $op = $this->getPrm('op', '');

        $fieldIDAuxiliar = '';
        // Determinamos la tabla a afectar
        if ($op == 'A') {
            $prefijoTabla = ' ' . BD_TEMPORAL . self::PREFIJO_TEMPORAL;
            $fieldIDAuxiliar = 'tem_pdx';
        } else {
            $prefijoTabla = 'cg_';
            $fieldIDAuxiliar = 'pdx_id';
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();

        // Obtenemos la suma del debe y haber de la póliza solicitada
        // Si es un alta NO EXISTE TEM_POLIZADETALLEORDEN ...
        $sql = "SELECT SUM(lineas) AS lineas, IFNULL( SUM( debe ), 0 ) AS debe, IFNULL( SUM( haber ), 0 ) AS haber FROM (
            SELECT '1' AS o, COUNT(*) AS lineas, SUM( pdx_debe ) AS debe, SUM( pdx_haber ) AS haber
                FROM {$prefijoTabla}PolizaDetalleAuxiliar
            WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue
                AND pot_id = :pot AND prf_id = :prf AND msc_id = :msc AND pma_id = :pma
            ) AS origen;";
        $params = [
            ':gpo' => getSession('clasAdm-gpo'),
            ':ur'  => getSession('clasAdm-ur'),
            ':ue'  => $ue,
            ':pot' => $p,
            ':prf' => $r,
            ':msc' => $m,
            ':pma' => $n,
        ];

        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();

        // Obtenemos la variable del número de líneas que hay en el grid ...
        if ($conexion->getNumeroRegistros() > 0) {
            $row = $conexion->extraer();
            // Llenamos los campos con la información de la línea seleccionada
            $this->mixScript('
                nTotalDebe = ' . $row['debe'] . ';
                nTotalHaber = ' . $row['haber'] . ';
                nRows  = ' . $row['lineas'] . ';

                $("#sumaDebe").val( formatNumber(nTotalDebe,"","") );
                $("#sumaHaber").val( formatNumber(nTotalHaber,"","") );

                // Si las sumas son diferentes, remarcamos y en rojo
                if ( formatNumber(nTotalDebe,"","") != formatNumber(nTotalHaber,"","") ) {
                    $("#sumaDebe").css({ "color":"red", "font-weight":"bold"});
                    $("#sumaHaber").css({ "color":"red", "font-weight":"bold"});
                } else {
                    $("#sumaDebe").css({ "color":"black", "font-weight":"normal"});
                    $("#sumaHaber").css({ "color":"black", "font-weight":"normal"});
                }

                // LOGICA PARA PONER EL BOTON DE GRABAR POLIZA
                // 1. Si es póliza de cheque cancelado, basta con que la póliza tenga una línea
                if( $("#EsChequeCancelado").css("display") != "none" ){
                    if ( nRows >= 1 ) $("#grabarPoliza").show();
                } else {
                    // Si la suma de movimientos deudores y acreedores son iguales ... ponemos el botón de grabar
                    // si tiene al menos dos líneas (no importa que sume cero)
                    if (($("#sumaDebe").val() == $("#sumaHaber").val()) && (nRows >= 2)) {
                        $("#grabarPoliza").show();
                    } else {
                        $("#grabarPoliza").hide();
                    }
                }
            ');
        }
        $this->mixScript('window.espera?.stop();');
        return false;
    }
    private function obtenerDetallePolizaTemporal()
    {
        $msc_id = date('n', strtotime(date_to_mysqldate($this->prm->fecha)));
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $sql = "SELECT  pdx_debe,pdx_haber, pdx_referencia,p.cta_nombre AS nom,
            CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', RIGHT(cgsc_id,1), '.', cgc_id, '.', cta_id ) AS cta,tem_pdx,ue_id
            FROM " . BD_TEMPORAL . '.tem_PolizaDetalleAuxiliar
            INNER JOIN ' . BD_TEMPORAL . '.tem_PolizaMaestro  USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, tem_pma)
            INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas p USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id )
            WHERE gpo_id = ? AND ur_id = ? AND ue_id = ? AND msc_id = ? AND tem_pma = ?;
        ';
        $conexion->setSql($sql);
        $conexion->consultar([
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
            $this->prm->ue,
            $msc_id,
            $this->prm->npTo,
        ]);
        $conexion->desconectar();

        $datos = [];
        while ($row = $conexion->extraer()) {
            $datos[] = $row;
        }
        echo json_encode($datos);
        exit;
    }

    private function buscaPipet()
    {
        $pipet = $this->prm->ore_idMml;
        $gpo_idP = getSession('clasAdm-gpo');
        $ur_idP = getSession('clasAdm-ur');

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $sql = ' SELECT oa.oa_numObra oa_numObra,oa.oa_nombre oa_nombre
                FROM poa_OrigenRecurso p_or
                LEFT JOIN enc_Programatico e ON e.gpo_id = p_or.gpo_id AND e.ur_id = p_or.ur_id AND e.epg_id = p_or.epg_id
                LEFT JOIN cat_ObraActividad oa ON e.gpo_id = oa.gpo_id AND e.ur_id = oa.ur_id AND e.prg_id = oa.prg_id AND e.spr_id = oa.spr_id AND e.py_id = oa.py_id AND e.oa_id = oa.oa_id
                WHERE p_or.ore_idMml= ? AND p_or.gpo_id= ? AND p_or.ur_id= ? ';

        $conexion->setSql($sql);
        $conexion->consultar([$pipet, $gpo_idP, $ur_idP]);
        $conexion->desconectar();

        $datos = [];
        while ($row = $conexion->extraer()) {
            $datos[] = $row;
        }
        echo json_encode($datos);
        exit;

    }
}

if (isset($_SESSION[_S]['prm']['C']['3']['1'])) {
    new ControladorPolizaManual();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
?>

