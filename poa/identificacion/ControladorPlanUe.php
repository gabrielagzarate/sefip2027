<?php

/*--------------------------------------------------------------------------
    Objetivo: Consultar y dar de alta Unidades Responsables y Unidades Ejecutoras
    Elaboró : ?
    Colaboradores : GEMMA JARQUÍN/ SOLEDAD LÓPEZ
    Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
    Migrate: 02-Julio-2020

    PENDIENTES: Cuando funcione la clasificación administrativa, amarrar el
        comportamiento de
    $("#comboUr").change(function(){
    para que responda al cambio de UR en lo que quede (que parece que es un textbox)

    MARCOS:
    1.- Le cambie todo donde apuntaba a s22GPO y s22UR para que use la clasificación administrativa
    2.- Hay una condición para el nivel jerarquico 10 ... ¿ es PROGRAMADOR ?

    MODIFICADO 09/FEB/2015
    En cualquier momento un usuario de nivel 2 (UR) que tenga acceso al poa,
    podrá modificar el nombre del titular UE

    MIGRACIÓN 2019 : @jpho 30/04/2018
    PSR-2
    */
//inicio de sesion y llamada a las librerias

ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../lib/poa/identificacion/ValidacionContactoUrUe.php';

class ControladorPlanUe extends WebResponse
{
    // Claves de UR (CONCAT(gpo_id,ur_id)) exentas de campos obligatorios en la
    // identificación institucional — Ticket #SDS-559336 (116, 901 y 902).
    const UR_EXCEPCION_OBLIGATORIOS = [116, 901, 902];
    protected $nvj;
    protected $adm_grupo;
    protected $adm_ur;
    protected $UE;
    protected $ur;
    protected $ue;
    protected $escritura;
    protected $CambiosActivos;
    protected $nombreStatus;
    protected $edicion;
    protected $exceptaObligatoriosUe;
    public $sql;
    public $params;
    public function __construct()
    {
        parent::__construct();

        // iniciando la variable de session
        $this->nvj = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] != '') ? $_SESSION[_S]['nvj_id'] : '';
        $this->adm_grupo = (isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '') ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '') ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->UE = (isset($_SESSION[_S]['ue_id']) && $_SESSION[_S]['ue_id'] != '') ? $_SESSION[_S]['ue_id'] : '';

        // Excepción Ticket #SDS-296092: para las UR de municipios (901/902) los campos de
        // identificación institucional NO son obligatorios, por solicitud
        // La clave de UR es la concatenación gpo_id.ur_id (igual que CONCAT(gpo_id,ur_id)).
        $this->exceptaObligatoriosUe = in_array((int) ($this->adm_grupo . $this->adm_ur), self::UR_EXCEPCION_OBLIGATORIOS, true);

        //INSTANCIANDO LA UR
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->adm_grupo);
        $this->ur->setUr_id($this->adm_ur);
        $this->ur->select();

        // SI ES UN USUARIO DE NIVEL UR, O TIENE PERMISOS DE ESCRITURA, PUEDE GRABAR CAMBIOS A ÉSTA PANTALLA
        $this->escritura = (($this->nvj == 2) or (isset($_SESSION[_S]['prm']['A']['1']['2']['2'])));

        // SIN EMBARGO, SÓLO CON LOS ESTADOS 0, 1, 3 (SI PLANEACION, GUARDADA Y RECHAZADA) PUEDE MODIFICAR INFORMACIÓN
        $estadoUR = $this->ur->getEur_id();
        if (!($estadoUR == 0 || $estadoUR == 1 || $estadoUR == 3 || $estadoUR >= 4)) {
            $this->escritura = false;
        }

        // AUTORIZACIÓN PARA MODIFICAR LA PLANEACIÓN UE VALIDAMOS PARA USUARIOS DE NIVEL UE Y UR
        if (($this->nvj < 2)) {
            $this->CambiosActivos = (isset($_SESSION[_S]['prm']['A']['1']['2']));
        } else {
            // USUARIOS DE ANALISTAS PARA ARRIBA SIEMPRE TENDRÁN HABILITADOS LOS CAMBIOS
            $this->CambiosActivos = true;
        }

        // SI YA ESTÁ CERRADA LA AUTORIZACIÓN PARA MODIFICARLA, NO TIENEN PERMISOS DE ESCRITURA
        if (!$this->CambiosActivos) {
            $this->escritura = false;
        }

        $this->edicion = false;

        switch ($this->metodo) {
            case 'uploadDocumento':
                // Aseguramos que la respuesta sea JSON limpio (evita warnings/notices PHP
                // que rompan el JSON.parse del plugin jquery.fileupload en cliente)
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                ob_start();
                if ($this->CambiosActivos) {
                    $this->uploadDocumento();
                } else {
                    $this->setStatus('error');
                    $respuestaSinPermisos = new stdClass();
                    $respuestaSinPermisos->mensaje = 'No tiene permisos para cargar archivos en este momento.';
                    $this->setValidacion($respuestaSinPermisos);
                }
                ob_end_clean();
                header('Content-Type: application/json; charset=utf-8');
                $this->returnJson();

                return;
            case 'download':
                $this->download();
                $this->returnJson();

                return;
        }

        switch ($this->nvj) {
            case 1:
                $this->ue = new UnidadEjecutora();
                $this->ue->setGpo_id($this->adm_grupo);
                $this->ue->setUr_id($this->adm_ur);
                $this->ue->setUe_id($this->UE);

                $this->ue->select();
                switch ($this->metodo) {
                    case 'guardar':
                        //PARA NIVEL UE, SOLO DEBE MOSTRAR LOS DATOS QUE LE CORRESPONDE.
                        // QUE DICE MI MAMÁ QUE SIEMPRE NO: QUE EN CUALQUIER MOMENTO PUEDEN CAMBIAR EL NOMBRE
                        $this->guardar();
                        break;
                    default:
                        $this->PintaPantalla();
                        break;
                }
                break;
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                switch ($this->metodo) {
                    case 'llenaTablaUes':
                        $this->llenaTablaUes();
                        break;
                    case 'PintaPantalla':
                        $this->ue = new UnidadEjecutora();
                        $this->ue->setGpo_id($this->adm_grupo);
                        $this->ue->setUr_id($this->adm_ur);
                        $this->ue->setUe_id($this->prm->ue_id);
                        $this->ue->select();
                        $this->PintaPantalla();
                        break;
                    case 'guardar':
                        // EN CUALQUIER MOMENTO SE PODRA HACER
                        $this->guardar();
                        break;
                    case 'PintaPantalla':
                        $this->PintaPantalla();
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
        $this->setContenedor('contenedor');
        $this->agregaTools(' ');
        // OBTENEMOS EL STATUS DE LA PLANEACIÓN UE (QUE POR PROGRAMACIÓN ES EL MISMO QUE PLANEACIÓN UR)

        $this->conectar();
        $sql = 'SELECT eur_id, eur_nombre FROM ' . BD_CTRL . '.cat_UnidadResponsable INNER JOIN ' . BD_CTRL . '.cat_EstadoUr USING( eur_id )
        WHERE gpo_id = ? AND ur_id = ?;';
        $params = [$this->adm_grupo,$this->adm_ur];
        $this->setSql($sql);
        $this->consultar($params);
        $aStatus = $this->extraer();
        $this->desconectar();
        $this->nombreStatus = 'Estado : ' . $aStatus['eur_nombre'];

        $tabla = new stdClass();
        $tabla->titulo = $this->nombreStatus;
        $tabla->id = 'tbl-Ues';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaUes';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];

        //VER
        $th = new stdClass();
        $th->texto = 'Ver/Editar';
        array_push($tr, $th);

        //CLAVE
        $th = new stdClass();
        $th->texto = 'Clave';
        $th->width = '60px';
        array_push($tr, $th);

        //NOMBRE
        $th = new stdClass();
        $th->texto = 'Unidad Ejecutora';
        array_push($tr, $th);

        $trbody = [];
        // EN CUALQUIER MOMENTO SE PODRÁ CAMBIAR EL NOMBRE DEL TITULAR UE  SI ES EDITABLE, EL ICONO ES LAPIZ.   SI NO ES UN OJO
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        $td->class = 'ver';
        $td->name = 'ue_id';
        $td->id = 'ue_id';
        $td->attr = new stdClass();
        $td->attr->ue_id = 'ue_id';
        array_push($trbody, $td);

        //CONTENIDO CLAVE
        $td = new stdClass();
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);

        //CONTENIDO NOMBRE
        $td = new stdClass();
        $td->campoTxt = 'ue_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaUes();

        $this->mixScript('
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });            

            // QUITAMOS LA INFO DE UNA PAGINACIÓN QUE NO USAREMOS EN ÉSTA PANTALLA
            
            $(".dataTables_length").remove();
            $(".panel-footer").remove();
            
            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
                location.reload();
            });

            // ESTO ES PARA CUANDO CAMBIE LA CLASIFICACIÓN ADMINISTRATIVA
            $(".claveUr").change(function(){
                comunica("' . $this->pantalla . '",{metodo:"TablaUes"});
            });');

        if ($_SESSION[_S]['prm']['A']['1']['2']) {
            $this->mixScript('$("#techos").hide();');
        }

        $this->mixScript(ClasificacionAdministrativa::onChangeUr("comunica('{$this->pantalla}',{ metodo:'TablaUes'});"));
    }

    private function llenaTablaUes()
    {
        $sql = 'SELECT * FROM cat_UnidadEjecutora where gpo_id= ? and ur_id= ? ;';
        $params = [$this->adm_grupo,$this->adm_ur];
        $this->setContenedor('contenedor');

        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Ues';
        // $nav->nf = '999';   // No usaremos paginación en ésta pantalla
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;

        $this->wrLlenaTabla($nav);
        //no es necesario el boton regresar ya que regresa a la pagina principal del sistema
        $this->mixScript('
            $(".ver").click(function() {
                comunica("' . $this->pantalla . '",{
                metodo:"PintaPantalla",
                ue_id:$(this).attr("ue_id") },false,true);
                return false;
            });
        ');
    }

    private function obtenerDominiosInstitucionales()
    {
        $dominiosPermitidos = [];
        $sql = 'SELECT dmi_dominio FROM ' . BD_SINPRES . '.cat_DominioInstitucional WHERE dmi_activo = ?;';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([1]);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            while ($data = $this->extraer()) {
                $dominiosPermitidos[] = $data['dmi_dominio'];
            }
        }

        return $dominiosPermitidos;
    }

    private function esParteLocalCorreoValida($local)
    {
        $local = (string) $local;
        if ($local === '' || strpos($local, '..') !== false) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9._-]*[a-zA-Z0-9])?$/', $local) === 1;
    }

    private function esCorreoDominioInstitucional($correo, $dominiosPermitidos = null)
    {
        $correo = trim((string) $correo);
        if ($correo === '') {
            return true;
        }
        if (!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9._-]*[a-zA-Z0-9])?@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+$/', $correo)) {
            return false;
        }
        $partes = explode('@', $correo);
        if (count($partes) < 2 || !$this->esParteLocalCorreoValida($partes[0])) {
            return false;
        }
        $dominio = strtolower($partes[1]);
        if ($dominiosPermitidos === null) {
            $dominiosPermitidos = $this->obtenerDominiosInstitucionales();
        }
        foreach ($dominiosPermitidos as $d) {
            $d = strtolower((string) $d);
            if ($dominio === $d || substr($dominio, -strlen($d)) === $d) {
                return true;
            }
        }

        return false;
    }

    private function esTelefonoValido($telefono)
    {
        $telefono = preg_replace('/\D/', '', (string) $telefono);
        if ($telefono === '') {
            return true;
        }

        return preg_match('/^\d{10}$/', $telefono) === 1;
    }

    private function esExtensionValida($extension)
    {
        $extension = trim((string) $extension);
        if ($extension === '') {
            return true;
        }

        return preg_match('/^\d{1,5}$/', $extension) === 1;
    }

    private function obtenerUeOperacion()
    {
        $ue = new UnidadEjecutora();
        $ue->setGpo_id($this->adm_grupo);
        $ue->setUr_id($this->adm_ur);
        if ($this->nvj == 1) {
            $ue->setUe_id($this->UE);
        } elseif (isset($this->prm->ue_id)) {
            $ue->setUe_id($this->prm->ue_id);
        }
        $ue->select();

        return $ue;
    }

    private function dameHtmlDocumentoUe($documento, $etiqueta, $nombreArchivo, $ueClave, $ueId)
    {
        $id = $documento;
        $textoEnlace = (trim((string) $nombreArchivo) === '') ? 'Sin archivo' : $nombreArchivo;
        $lnk = 'firmas/' . $ueClave . '/' . $ueClave . '_' . $id . '.pdf?npi=' . rand(1, 99999);
        $enlace = '<a style="color:blue;" class="lnk_ext m-l-5" id="nameFilename' . $id . '" tabindex="-1" lnk="' . $lnk
            . '" target="_blank">' . htmlspecialchars($textoEnlace, ENT_QUOTES, 'UTF-8') . '</a>';

        $html = '
                <fieldset class="col-md-6 col-xs-12 my-2">
                    <label class="control-label" for="' . $id . '">' . $etiqueta
            . ' (formato PDF máximo de 6MB)</label>';

        if (!$this->escritura || !$this->edicion) {
            $html .= '<div>' . $enlace . '</div>';
        } else {
            $html .= '
                    <input type="file" name="archivoDocumento" id="' . $id . '" accept="application/pdf,.pdf" data-url="' . $this->pantalla . '"
                        data-form-data=\'{"metodo":"uploadDocumento","documento":"' . $id . '","ue_id":"' . $ueId . '"}\'/>
                    ' . $enlace . '
                    <div id="progress' . $id . '" class="progress progress-striped mt-2" style="height: 14px;">
                        <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            <span class="sr-only">0% Complete</span>
                        </div>
                    </div>
                    <div id="estadoUpload' . $id . '" class="upload-status mt-1" style="display:none; font-size:12px; color:#0d6efd;">
                        <i class="fa fa-circle-notch fa-spin me-1"></i><span class="upload-status-text">Preparando archivo...</span>
                    </div>
                    <div id="overlayUpload' . $id . '" class="upload-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:1080; align-items:center; justify-content:center;">
                        <div style="background:#fff; padding:18px 24px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:12px; min-width:280px;">
                            <i class="fa fa-circle-notch fa-spin" style="font-size:22px; color:#0d6efd;"></i>
                            <div>
                                <div style="font-weight:600;" class="upload-overlay-title">Subiendo archivo...</div>
                                <div class="upload-overlay-text" style="font-size:12px; color:#555;">Por favor espere, no cierre esta ventana.</div>
                            </div>
                        </div>
                    </div>';
            $this->mixScript('
                (function(){
                    var $input = $("#' . $id . '");
                    var $progressWrap = $("#progress' . $id . '");
                    var $bar = $progressWrap.find(".progress-bar");
                    var $estado = $("#estadoUpload' . $id . '");
                    var $estadoTxt = $estado.find(".upload-status-text");
                    var $overlay = $("#overlayUpload' . $id . '");
                    var $overlayTxt = $overlay.find(".upload-overlay-text");
                    var $overlayTitle = $overlay.find(".upload-overlay-title");
                    var procesandoTimer = null;

                    function setEstado(texto, color) {
                        $estadoTxt.text(texto);
                        $estado.css("color", color || "#0d6efd").show();
                    }
                    function ocultarEstado() {
                        $estado.hide();
                    }
                    function bloquearUI() {
                        $input.prop("disabled", true);
                        $overlay.css("display", "flex");
                    }
                    function liberarUI() {
                        $input.prop("disabled", false);
                        $overlay.hide();
                        if (procesandoTimer) {
                            clearTimeout(procesandoTimer);
                            procesandoTimer = null;
                        }
                    }
                    function pintarBarra(pct, claseExtra) {
                        $bar.css("width", pct + "%").attr("aria-valuenow", pct);
                        $bar.removeClass("bg-success bg-danger progress-bar-animated");
                        if (claseExtra) { $bar.addClass(claseExtra); }
                    }

                    $input.on("click", function(){
                        pintarBarra(0);
                        ocultarEstado();
                    });

                    $input.fileupload({
                        dataType: "json",
                        add: function(e, data) {
                            var archivo = data.files && data.files[0] ? data.files[0] : null;
                            if (!archivo) {
                                notify("No se seleccionó ningún archivo.", "error");
                                return false;
                            }
                            var nombre = (archivo.name || "").toLowerCase();
                            var tipo = (archivo.type || "").toLowerCase();
                            var esPdf = nombre.lastIndexOf(".pdf") === nombre.length - 4
                                && (tipo === "" || tipo === "application/pdf");
                            if (!esPdf) {
                                notify("Solo se permiten archivos PDF.", "error");
                                $input.val("");
                                return false;
                            }
                            var maxBytes = 6 * 1024 * 1024;
                            if (archivo.size > maxBytes) {
                                notify("El archivo excede el tamaño máximo permitido de 6MB.", "error");
                                $input.val("");
                                return false;
                            }
                            $overlayTitle.text("Subiendo \"" + archivo.name + "\"");
                            $overlayTxt.text("Por favor espere, no cierre esta ventana.");
                            data.submit();
                        },
                        start: function() {
                            bloquearUI();
                            pintarBarra(0, "progress-bar-animated");
                            setEstado("Iniciando carga...", "#0d6efd");
                        },
                        progress: function(e, data) {
                            var progress = parseInt(data.loaded / data.total * 100, 10);
                            pintarBarra(progress, "progress-bar-animated");
                            if (progress < 100) {
                                setEstado("Subiendo archivo... " + progress + "%", "#0d6efd");
                                $overlayTxt.text("Enviando datos al servidor (" + progress + "%)...");
                            } else {
                                setEstado("Procesando archivo en el servidor, espere un momento...", "#0d6efd");
                                $overlayTxt.text("Procesando archivo en el servidor, espere un momento...");
                                if (!procesandoTimer) {
                                    procesandoTimer = setTimeout(function(){
                                        $overlayTxt.text("El servidor sigue procesando el archivo, esto puede tardar unos segundos...");
                                    }, 5000);
                                }
                            }
                        },
                        done: function(e, data) {
                            var resultado = data.result || {};
                            var validacion = resultado.validacion || {};
                            var estado = resultado.status || "success";
                            if (validacion.nombre) {
                                $("#nameFilename' . $id . '").text(validacion.nombre);
                            }
                            if (estado === "error") {
                                pintarBarra(100, "bg-danger");
                                setEstado(validacion.mensaje || "Ocurrió un error al cargar el archivo.", "#dc3545");
                                notify(validacion.mensaje || "Ocurrió un error al cargar el archivo.", "error");
                            } else {
                                pintarBarra(100, "bg-success");
                                setEstado(validacion.mensaje || "Archivo cargado con éxito.", "#198754");
                                notify(validacion.mensaje || "Archivo cargado con éxito.", "success");
                            }
                            liberarUI();
                            setTimeout(ocultarEstado, 4000);
                        },
                        fail: function(e, data) {
                            var mensajeError = "No fue posible cargar el archivo.";
                            if (data && data.jqXHR && data.jqXHR.responseJSON
                                && data.jqXHR.responseJSON.validacion
                                && data.jqXHR.responseJSON.validacion.mensaje) {
                                mensajeError = data.jqXHR.responseJSON.validacion.mensaje;
                            } else if (data && data.errorThrown) {
                                mensajeError = data.errorThrown;
                            }
                            pintarBarra(100, "bg-danger");
                            setEstado(mensajeError, "#dc3545");
                            notify(mensajeError, "error");
                            liberarUI();
                            setTimeout(ocultarEstado, 5000);
                        },
                        always: function() {
                            liberarUI();
                        }
                    });
                })();
            ');
        }

        $html .= '
                </fieldset>';

        return $html;
    }

    private function uploadDocumento()
    {
        $ue = $this->obtenerUeOperacion();
        $urClave = $this->adm_grupo . $this->adm_ur;
        $ueClave = $ue->getClave();
        $name = 'archivoDocumento';
        $documento = isset($this->prm->documento) ? $this->prm->documento : '';
        $respuesta = new stdClass();

        if (!in_array($documento, ['gafete', 'nombramiento'], true)) {
            $this->setStatus('error');
            $respuesta->mensaje = 'Tipo de documento no válido';
            $this->setValidacion($respuesta);

            return;
        }

        if (!isset($_FILES[$name]) || $_FILES[$name]['error'] != 0) {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrió un error al cargar el archivo';
            $this->setValidacion($respuesta);

            return;
        }

        // Validamos la extensión y el tipo MIME ANTES de tocar el sistema de archivos
        // para no renombrar el PDF previo cuando el nuevo archivo no es válido.
        $extension = strtolower((string) pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION));
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = (string) finfo_file($finfo, $_FILES[$name]['tmp_name']);
                finfo_close($finfo);
            }
        }
        if ($extension !== 'pdf' || ($mime !== '' && $mime !== 'application/pdf')) {
            $this->setStatus('error');
            $respuesta->mensaje = 'El tipo de archivo no es permitido. Solo se aceptan archivos PDF.';
            $this->setValidacion($respuesta);

            return;
        }

        // Validamos el tamaño máximo (6MB declarado en la etiqueta del formulario)
        $tamMaximo = 6 * 1024 * 1024;
        if ((int) $_FILES[$name]['size'] > $tamMaximo) {
            $this->setStatus('error');
            $respuesta->mensaje = 'El archivo excede el tamaño máximo permitido de 6MB.';
            $this->setValidacion($respuesta);

            return;
        }

        $up = new FicheroSefip();
        $up->setTipos(['pdf']);
        $up->setFichero($_FILES[$name]);
        $up->setModulo('poa');
        $up->setfile($ueClave . '_' . $documento . '.pdf');
        $up->setUrFichero($urClave);
        $up->setAnioFichero(APP_EJERCICIO);
        // Cada UE tiene su propia subcarpeta dentro de "firmas" para mejor organización
        $up->setRuta('firmas/' . $ueClave);

        $nomDir = $up->getRutaMod();

        if ($nomDir === false && $up->getError() === true) {
            $this->setStatus('error');
            $respuesta->mensaje = $up->getMsg();
            $this->setValidacion($respuesta);

            return;
        }

        $nomFile = $ueClave . '_' . $documento;
        $nomFileDb = $_FILES[$name]['name'];
        $countArchivos = 0;

        $coincidencias = glob($nomDir . $nomFile . '*.pdf');
        if (is_array($coincidencias)) {
            $countArchivos = count($coincidencias);
        }

        if ($countArchivos > 0 && file_exists($nomDir . $nomFile . '.pdf')) {
            @rename($nomDir . $nomFile . '.pdf', $nomDir . $nomFile . '_' . $countArchivos . '.pdf');
        }

        if ($up->upload()) {
            if ($documento === 'gafete') {
                $ue->setGafeteInstitucional($nomFileDb);
            } else {
                $ue->setNombramiento($nomFileDb);
            }
            $ue->update();
            $this->setStatus('success');
            $respuesta->mensaje = 'Archivo cargado con éxito';
            $respuesta->nombre = $nomFileDb;
        } else {
            $this->setStatus('error');
            $respuesta->mensaje = $up->getMensaje() !== '' ? $up->getMensaje() : 'No fue posible guardar el archivo.';
        }
        $this->setValidacion($respuesta);
    }

    private function download()
    {
        $fichero = '';
        $subRuta = '';
        if (isset($this->prm->lnk)) {
            // Quitamos el query string (?npi=...) si lo trae
            $lnkLimpio = preg_replace('/\?.*$/', '', (string) $this->prm->lnk);
            $segmentos = array_values(array_filter(explode('/', $lnkLimpio), function ($s) {
                return $s !== '';
            }));
            if (count($segmentos) >= 2) {
                // El último segmento es el nombre del archivo, los demás forman la ruta
                $fichero = array_pop($segmentos);
                // Sanitizamos cada segmento para evitar path traversal
                $segmentosLimpios = [];
                foreach ($segmentos as $seg) {
                    $segLimpio = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $seg);
                    if ($segLimpio !== '') {
                        $segmentosLimpios[] = $segLimpio;
                    }
                }
                $subRuta = implode('/', $segmentosLimpios);
            }
        }

        $fichero = basename((string) $fichero);

        if ($subRuta === '' || $fichero === '') {
            $this->mixScript('notify("No fue posible localizar el archivo solicitado.", "error");');

            return;
        }

        // Buscamos el archivo: primero en la nueva estructura firmas/<ueClave>/...
        // y si no existe, en la estructura anterior firmas/... (compatibilidad)
        $rutaArchivo = $this->resolverRutaArchivoFirma($subRuta, $fichero);

        if ($rutaArchivo === null) {
            $this->mixScript('
                notify("El archivo solicitado no se encuentra disponible. Es posible que aún no haya sido cargado.", "error");
            ');

            return;
        }

        $dest = __DIR__ . '/../../tmps/' . $fichero;
        if (!@copy($rutaArchivo, $dest)) {
            $this->mixScript('notify("No fue posible abrir el archivo en este momento.", "error");');

            return;
        }

        $this->mixScript('$("#det_fsvis1").attr("src","lib/file_download.php?r=' . rawurlencode($fichero) . '");');
    }

    /**
     * Resuelve la ruta física del archivo de firma. Primero busca en la nueva
     * estructura firmas/<ueClave>/<archivo> y si no existe, hace fallback a la
     * estructura anterior firmas/<archivo> para mantener compatibilidad con
     * archivos cargados antes del cambio de organización de carpetas.
     */
    private function resolverRutaArchivoFirma($subRuta, $fichero)
    {
        $candidatos = [$subRuta];
        // Fallback: si la sub-ruta es "firmas/<algo>", probamos también con sólo "firmas/"
        $partes = explode('/', $subRuta);
        if (count($partes) >= 2) {
            $candidatos[] = $partes[0];
        }

        foreach ($candidatos as $candidato) {
            $r = new RutaArchivoSefip();
            $r->setBase('POA');
            $r->setPath($candidato);
            $r->setAnioFichero(APP_EJERCICIO);
            $r->setUrFichero($this->adm_grupo . $this->adm_ur);
            $rutaDir = $r->getRutaMod();
            if ($rutaDir === false) {
                continue;
            }
            $rutaArchivo = $rutaDir . $fichero;
            if (file_exists($rutaArchivo) && is_file($rutaArchivo)) {
                return $rutaArchivo;
            }
        }

        return null;
    }

    public function PintaPantalla()
    {
        $dominiosPermitidos = $this->obtenerDominiosInstitucionales();
        $urActual = $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'];
        $botones = '<button id="btnregresarlistaues" 
            type="button" class="btn btn-outline-secondary me-2" title="Regresar al listado de Unidades Responsables">
                <i class="fa fa-circle-left me-2"></i>Regresar</button>';

        // SI EL ESTADO UR ES 2 (ENVIADA PARA VALIDACIÓN) O >= 4 (AUTORIZADA)
        // EN CUALQUIER MOMENTO SE PODRÁ CAMBIAR EL NOMBRE DEL TITULAR UE
        // SIEMPRE Y CUANDO SEA UN USUARIO DE NIVEL 2
        if ($this->nvj == 1) {
            if (!($this->ur->getEur_id() == 2 || $this->ur->getEur_id() >= 4)) {
                if ($this->escritura) {
                    // SIEMPRE Y CUANDO LOS STATUS DE LA UR SEAN 0, 1 O 3 ( SIN PLANEACION, GUARDADA O RECHAZADA )
                    $botones .= '<button type="button" class="btn btn-outline-success btnguardarplanue" 
                        id="btnguardarplanue"  >
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>';
                    $this->edicion = true;
                }
            }
        } else {
            if ($urActual != '115') {
                if ($this->escritura) {
                    $botones .= '<button type="button" class="btn btn-outline-success btnguardarplanue" ' .
                        'id="btnguardarplanue"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>';
                    $this->edicion = true;
                }
            }
        }

        $this->agregaTools($botones);

        // Excepción Ticket #SDS-296092: para municipios (901/902) no se marca (*) como
        // obligatorio en las etiquetas de los campos de identificación.
        $req = $this->exceptaObligatoriosUe ? '' : ' *';

        $ur = $this->ur;
        $ue = $this->ue;

        $ur_nombre = new stdClass();
        $ur_nombre->id = 'ur_nombre';
        $ur_nombre->name = 'ur_nombre';
        $ur_nombre->label = 'Unidad responsable';
        $ur_nombre->enDiv = true;
        $ur_nombre->maxlength = '100';
        $ur_nombre->value = $ur->getNombre();
        $ur_nombre->readonly = 'true';

        $nombre = new stdClass();
        $nombre->id = 'nombre';
        $nombre->name = 'nombre';
        $nombre->label = 'Unidad ejecutora';
        $nombre->enDiv = true;
        $nombre->maxlength = '100';
        $nombre->value = $ue->getNombre();
        $nombre->readonly = 'true';

        $correo = new stdClass();
        $correo->id = 'correo';
        $correo->name = 'correo';
        $correo->label = 'Correo Electrónico' . $req;
        $correo->enDiv = true;
        $correo->maxlength = '200';
        $correo->style = 'text-transform: none;';
        $correo->value = $ue->getCorreoInstitucional();

        $telefonoCelular = new stdClass();
        $telefonoCelular->id = 'telCelular';
        $telefonoCelular->name = 'telCelular';
        $telefonoCelular->label = 'Teléfono Celular' . $req;
        $telefonoCelular->enDiv = true;
        $telefonoCelular->maxlength = '10';
        $telefonoCelular->class = 'input-xxlarge';
        $telefonoCelular->value = $ue->getCelular();

        $extension = new stdClass();
        $extension->id = 'extension';
        $extension->name = 'extension';
        $extension->label = 'Extensión' . $req;
        $extension->enDiv = true;
        $extension->maxlength = '5';
        $extension->value = $ue->getExtension();

        $telefonoOficina = new stdClass();
        $telefonoOficina->id = 'telOficina';
        $telefonoOficina->name = 'telOficina';
        $telefonoOficina->label = 'Teléfono Oficina' . $req;
        $telefonoOficina->enDiv = true;
        $telefonoOficina->maxlength = '10';
        $telefonoOficina->class = 'input-xxlarge';
        $telefonoOficina->value = $ue->getTelefono();

        $nombreTitular = new stdClass();
        $nombreTitular->id = 'nombreTitular';
        $nombreTitular->name = 'nombreTitular';
        $nombreTitular->label = 'Nombre del titular de la unidad ejecutora' . $req;
        $nombreTitular->enDiv = true;
        // EN CUALQUIER MOMENTO SE PODRÁ CAMBIAR EL NOMBRE DEL TITULAR UE
        // DE NIVEL UR PARA ARRIBA LOS USUARIOS PODRÁN CAMBIARLO SIEMPRE
        if ($this->nvj == 1) {
            if (!$this->escritura) {
                $nombreTitular->readonly = 'true';
                $correo->readonly = 'true';
                $telefonoCelular->readonly = 'true';
                $telefonoOficina->readonly = 'true';
                $extension->readonly = 'true';
            }
        }
        if (!$this->escritura) {
            $correo->readonly = 'true';
            $telefonoCelular->readonly = 'true';
            $telefonoOficina->readonly = 'true';
            $extension->readonly = 'true';
        }

        $nombreTitular->maxlength = '100';
        $nombreTitular->value = $ue->getNombreTitular();

        $cargoTitular = new stdClass();
        $cargoTitular->id = 'cargoTitular';
        $cargoTitular->name = 'cargoTitular';
        $cargoTitular->label = 'Cargo del titular de la unidad ejecutora' . $req;
        $cargoTitular->enDiv = true;
        $cargoTitular->maxlength = '100';
        $cargoTitular->value = $ue->getCargoTitular();
        if (!$this->escritura) {
            $cargoTitular->readonly = 'true';
        }

        $objetivo = new stdClass();
        $objetivo->id = 'objetivo';
        $objetivo->name = 'objetivo';
        $objetivo->label = 'Objetivo de la unidad ejecutora' . $req;
        $objetivo->enDiv = true;
        $objetivo->class = 'anulaenter';
        $objetivo->rows = '10';
        $objetivo->style = 'width:100%; min-height:220px; resize:vertical;';
        $objetivo->maxlength = '800';
        $objetivo->texto = $ue->getObjetivo();
        if (!$this->escritura || $this->ur->getEur_id() >= 4) {
            $objetivo->readonly = 'true';
        }

        //PERMISOS DE LECTURA
        if (false) {
            $nombreTitular->readonly = 'true';
            $cargoTitular->readonly = 'true';
            $objetivo->readonly = 'true';
        }

        $ueClave = $ue->getClave();
        $htmlDocumentos = $this->dameHtmlDocumentoUe(
            'gafete',
            'Gafete Institucional',
            $ue->getGafeteInstitucional(),
            $ueClave,
            $ue->getUe_id()
        );
        $htmlDocumentos .= $this->dameHtmlDocumentoUe(
            'nombramiento',
            'Nombramiento o documento que lo acredite' . $req,
            $ue->getNombramiento(),
            $ueClave,
            $ue->getUe_id()
        );

        $html = '
        <form>
            <fieldset class="row">
                <input type="hidden"  id="ue_id" name="ue_id" value="' . $ue->getUe_id() . '">
                <fieldset class="col-md-12 col-xs-12 my-2">
                ' . $this->ponInput($ur_nombre) . '
                </fieldset>
                <fieldset class="col-md-12 col-xs-12 my-2">
                ' . $this->ponInput($nombre) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-12 my-2">
                ' . $this->ponInput($nombreTitular) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-12 my-2">
                ' . $this->ponInput($cargoTitular) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-6 my-1">
                ' . $this->ponInput($correo) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-6 my-1">
                ' . $this->ponInput($telefonoCelular) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-6 my-1">
                ' . $this->ponInput($telefonoOficina) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-6 my-1">
                ' . $this->ponInput($extension) . '
                </fieldset>
                <fieldset class="col-md-12 col-xs-12 my-3">
                    <legend class="fs-6 mb-2">Documentos</legend>
                    <div class="row">
                    ' . $htmlDocumentos . '
                    </div>
                </fieldset>
                <fieldset class="col-md-12 col-xs-12 my-2">
                ' . $this->ponTextarea($objetivo) . '
                </fieldset>
            </fieldset>
        </form>';

        $this->mixHtml($html);
        $this->mixScript('
            var htmlToolsPlanUe = $("#toolsPantalla").html();

            var dominiosPermitidos = ' . json_encode($dominiosPermitidos) . ';
            // Excepción Ticket #SDS-296092: municipios (901/902) sin campos obligatorios.
            var exceptaObligatorios = ' . ($this->exceptaObligatoriosUe ? 'true' : 'false') . ';

            function esParteLocalCorreoValida(local) {
                if (local === "" || local.indexOf("..") !== -1) {
                    return false;
                }
                return /^[a-zA-Z0-9]([a-zA-Z0-9._-]*[a-zA-Z0-9])?$/.test(local);
            }

            function esCorreoInstitucionalValido(correo) {
                correo = correo.trim();
                if (correo === "") {
                    return true;
                }
                var partes = correo.split("@");
                if (partes.length < 2 || !esParteLocalCorreoValida(partes[0])) {
                    return false;
                }
                if (!/^[a-zA-Z0-9]([a-zA-Z0-9._-]*[a-zA-Z0-9])?@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+$/.test(correo)) {
                    return false;
                }
                var dominio = partes[1].toLowerCase();
                return dominiosPermitidos.some(function(d) {
                    d = d.toLowerCase();
                    return dominio === d || dominio.endsWith(d);
                });
            }

            function validatePhone10(number) {
                var numero = number.replace(/\D/g, "");
                return numero === "" || /^\d{10}$/.test(numero);
            }

            $("#telCelular, #telOficina").on("input", function() {
                var valor = $(this).val().replace(/\D/g, "").substring(0, 10);
                if ($(this).val() !== valor) {
                    $(this).val(valor);
                }
            });

            $("#telOficina").blur(function() {
                var valor = $(this).val().trim();
                if (valor === "") {
                    if (!exceptaObligatorios && !$(this).prop("readonly")) {
                        $(this).css("color", "red");
                        notify("El teléfono de oficina es obligatorio", "error");
                    } else {
                        $(this).css("color", "");
                    }
                } else if (validatePhone10(valor)) {
                    var celular = $("#telCelular").val().trim();
                    if (valor === celular) {
                        $(this).css("color", "red");
                        notify("Ingrese un número de oficina diferente al teléfono celular", "error");
                            $(this).focus();
                    } else {
                        $(this).css("color", "green");
                    }
                } else {
                    $(this).css("color", "red");
                    notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                    $(this).focus();
                }
            });

            function validateExtension(valor) {
                valor = valor.replace(/\D/g, "");
                return valor === "" || /^\d{1,5}$/.test(valor);
            }

            $("#extension").on("input", function() {
                var valor = $(this).val().replace(/\D/g, "").substring(0, 5);
                if ($(this).val() !== valor) {
                    $(this).val(valor);
                }
            });

            $("#extension").blur(function() {
                var valor = $(this).val().trim();
                if (valor === "") {
                    if (!exceptaObligatorios && !$(this).prop("readonly")) {
                        $(this).css("color", "red");
                        notify("El número de extensión es obligatorio", "error");
                    } else {
                        $(this).css("color", "");
                    }
                } else if (validateExtension(valor)) {
                    $(this).css("color", "green");
                } else {
                    $(this).css("color", "red");
                    notify("La extensión solo debe contener números", "error");
                    $(this).focus();
                }
            });

            $("#correo").blur(function() {
                var valor = $(this).val().trim();
                if (valor === "") {
                    if (!exceptaObligatorios && !$(this).prop("readonly")) {
                        $(this).css("color", "red");
                        notify("El correo electrónico es obligatorio", "error");
                    } else {
                        $(this).css("color", "");
                    }
                } else if (esCorreoInstitucionalValido(valor)) {
                    $(this).css("color", "green");
                } else {
                    $(this).css("color", "red");
                    notify("El correo electrónico no es válido", "error");
                    $(this).focus();
                }
            });

            $("#telCelular").blur(function() {
                var valor = $(this).val().trim();
                if (valor === "") {
                    if (!exceptaObligatorios && !$(this).prop("readonly")) {
                        $(this).css("color", "red");
                        notify("El teléfono celular es obligatorio", "error");
                    } else {
                        $(this).css("color", "");
                    }
                } else if (validatePhone10(valor)) {
                    var telOficina = $("#telOficina").val().trim();
                    if (valor === telOficina) {
                        $(this).css("color", "red");
                        notify("Ingrese un número de celular diferente al teléfono de oficina", "error");
                            $(this).focus();
                    } else {
                        $(this).css("color", "green");
                    }
                } else {
                    $(this).css("color", "red");
                    notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                    $(this).focus();
                }
            });

            $("#nombreTitular").blur(function() {
                var valor = $(this).val().trim();
                if (valor === "") {
                    if (!exceptaObligatorios && !$(this).prop("readonly")) {
                        $(this).css("color", "red");
                        notify("El nombre del titular de la unidad ejecutora es obligatorio", "error");
                    } else {
                        $(this).css("color", "");
                    }
                } else {
                    $(this).css("color", "green");
                }
            });

            $("#cargoTitular").blur(function() {
                var valor = $(this).val().trim();
                if (valor === "") {
                    if (!exceptaObligatorios && !$(this).prop("readonly")) {
                        $(this).css("color", "red");
                        notify("El cargo del titular de la unidad ejecutora es obligatorio", "error");
                    } else {
                        $(this).css("color", "");
                    }
                } else {
                    $(this).css("color", "green");
                }
            });

            $("#objetivo").blur(function() {
                var valor = $(this).val().trim();
                if (valor === "") {
                    if (!exceptaObligatorios && !$(this).prop("readonly")) {
                        $(this).css("color", "red");
                        notify("El objetivo de la unidad ejecutora es obligatorio", "error");
                    } else {
                        $(this).css("color", "");
                    }
                } else {
                    $(this).css("color", "green");
                }
            });

            function ejecutarGuardarPlanUe() {
                var correoValor = $("#correo").val().trim();
                if (!$("#correo").prop("readonly")) {
                    if (!exceptaObligatorios && correoValor === "") {
                        $("#correo").css("color", "red");
                        notify("El correo electrónico es obligatorio", "error");
                        $("#correo").focus();
                        return false;
                    }
                    if (correoValor !== "" && !esCorreoInstitucionalValido(correoValor)) {
                        $("#correo").css("color", "red");
                        notify("El correo electrónico no es válido", "error");
                        $("#correo").focus();
                        return false;
                    }
                }
                var telCelularValor = $("#telCelular").val().trim();
                if (!$("#telCelular").prop("readonly")) {
                    if (!exceptaObligatorios && telCelularValor === "") {
                        $("#telCelular").css("color", "red");
                        notify("El teléfono celular es obligatorio", "error");
                        $("#telCelular").focus();
                        return false;
                    }
                    if (telCelularValor !== "" && !validatePhone10(telCelularValor)) {
                        $("#telCelular").css("color", "red");
                        notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                        $("#telCelular").focus();
                        return false;
                    }
                }
                var telOficinaValor = $("#telOficina").val().trim();
                if (telOficinaValor !== "" && !validatePhone10(telOficinaValor)) {
                    $("#telOficina").css("color", "red");
                    notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                    $("#telOficina").focus();
                    return false;
                }
                var extensionValor = $("#extension").val().trim();
                if (extensionValor !== "" && !validateExtension(extensionValor)) {
                    $("#extension").css("color", "red");
                    notify("La extensión solo debe contener números", "error");
                    $("#extension").focus();
                    return false;
                }
                var documentoNombramiento = $("#nameFilenamenombramiento").text().trim();
                if (!exceptaObligatorios && documentoNombramiento == "Sin archivo") {
                    notify("Nombramiento o documento que lo acredite es obligatorio", "error");
                    $("#nameFilenamenombramiento").focus();
                    return false;
                }
                comunica("' . $this->pantalla . '",{
                    metodo:"guardar",    
                    abc:"c",
                    nombreTitular:$("#nombreTitular").val(), 
                    ' . ($this->escritura ? '
                    cargoTitular:$("#cargoTitular").val(),
                    objetivo:$("#objetivo").val(),
                    correo:correoValor,
                    telCelular:telCelularValor,
                    telOficina:telOficinaValor,
                    extension:extensionValor,
                    ' : '') . '
                    ue_id:$("#ue_id").val()            
                });
                return false;
            }

            $(document).off("click.planUe", "#btnguardarplanue").on("click.planUe", "#btnguardarplanue", function(e) {
                e.preventDefault();
                ejecutarGuardarPlanUe();
                return false;
            });
        
            Mousetrap.bind("esc esc", function(e) { 
                e.preventDefault(); $("#btnregresarlistaues").click(); e.stopPropagation(); 
            });
        
            // ATAJOS DE ÉSTA PANTALLA
            Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); ejecutarGuardarPlanUe(); });            
        
            $(document).off("click.planUe", "#btnregresarlistaues").on("click.planUe", "#btnregresarlistaues", function(e) {
                e.preventDefault();
                if (' . $this->nvj . '==1){
                    document.location.href = "../sinpres2115";
                } else {
                    $("#toolsPantalla").html("");
                    Mousetrap.unbind("ctrl+enter");
                    comunica("' . $this->pantalla . '",{ metodo:"tablaUes"});
                }
                return false;
            });
        
            $("#comboUr").change(function(){
                comunica("' . $this->pantalla . '",{ metodo:"TablaUes" });
            });

            $(".lnk_ext").click(function(){
                var lnk = $(this).attr("lnk");
                var tit = $(this).html();
                if (tit != "Sin archivo") {
                    load_viewer("vis1", tit, "F");
                    comunica("' . $this->pantalla . '", {metodo:"download", lnk:lnk}, false, true);
                }
            });

            setTimeout( function() { $( "#nombreTitular" ).focus() }, 200 );
        ');
    }

    private function notifyErrorPlanUe($mensaje)
    {
        $mensajeJs = json_encode($mensaje, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->mixScript('
            if (typeof htmlToolsPlanUe !== "undefined" && htmlToolsPlanUe !== "") {
                $("#toolsPantalla").html(htmlToolsPlanUe);
            }
            notify(' . $mensajeJs . ', "error");
        ');
    }

    private function datosUeGuardadosParaValidacion()
    {
        $ueId = isset($this->prm->ue_id) ? $this->prm->ue_id : $this->UE;
        if ($ueId === '' || $ueId === null) {
            return null;
        }
        $ue = new UnidadEjecutora();
        $ue->setGpo_id($this->adm_grupo);
        $ue->setUr_id($this->adm_ur);
        $ue->setUe_id($ueId);
        $ue->select();
        if ($ue->getUe_id() === '' || $ue->getUe_id() === null) {
            return null;
        }

        return [
            'nombreTitular' => $ue->getNombreTitular(),
            'correo'        => $ue->getCorreoInstitucional(),
            'telCelular'    => $ue->getCelular(),
        ];
    }

    public function guardar()
    {
        $ue = new UnidadEjecutora();
        $ue->setGpo_id($this->adm_grupo);
        $ue->setUr_id($this->adm_ur);
        $ue->setUe_id($this->prm->ue_id);
        $ue->select();

        if ($this->exceptaObligatoriosUe) {
            // Municipios (901/902): los campos no son obligatorios; solo validamos el
            // formato de los valores que el usuario haya capturado.
            if (isset($this->prm->correo) && trim((string) $this->prm->correo) !== ''
                && !$this->esCorreoDominioInstitucional($this->prm->correo)) {
                $this->notifyErrorPlanUe('El correo electrónico no es válido');

                return;
            }
            if (isset($this->prm->telCelular) && trim((string) $this->prm->telCelular) !== ''
                && !$this->esTelefonoValido($this->prm->telCelular)) {
                $this->notifyErrorPlanUe('El teléfono celular debe ser de 10 dígitos');

                return;
            }
            if (isset($this->prm->telOficina) && trim((string) $this->prm->telOficina) !== ''
                && !$this->esTelefonoValido($this->prm->telOficina)) {
                $this->notifyErrorPlanUe('El teléfono de oficina debe ser de 10 dígitos');

                return;
            }
            if (isset($this->prm->extension) && trim((string) $this->prm->extension) !== ''
                && !$this->esExtensionValida($this->prm->extension)) {
                $this->notifyErrorPlanUe('La extensión solo debe contener números');

                return;
            }
        } elseif (isset($this->prm->nombreTitular) || isset($this->prm->cargoTitular) || isset($this->prm->correo) || isset($this->prm->telCelular) || isset($this->prm->telOficina) || isset($this->prm->extension) || isset($this->prm->objetivo)) {
            if (!isset($this->prm->nombreTitular) || trim((string) $this->prm->nombreTitular) === '') {
                $this->notifyErrorPlanUe('El nombre del titular de la unidad ejecutora es obligatorio');

                return;
            }
            if (!isset($this->prm->cargoTitular) || trim((string) $this->prm->cargoTitular) === '') {
                $this->notifyErrorPlanUe('El cargo del titular de la unidad ejecutora es obligatorio');

                return;
            }
            if (!isset($this->prm->correo) || trim((string) $this->prm->correo) === '') {
                $this->notifyErrorPlanUe('El correo electrónico es obligatorio');

                return;
            }
            if (!$this->esCorreoDominioInstitucional($this->prm->correo)) {
                $this->notifyErrorPlanUe('El correo electrónico no es válido');

                return;
            }
            if (!isset($this->prm->telCelular) || trim((string) $this->prm->telCelular) === ''
                || !$this->esTelefonoValido($this->prm->telCelular)) {
                $this->notifyErrorPlanUe('El teléfono celular es obligatorio y debe ser de 10 dígitos');

                return;
            }
            if (!isset($this->prm->telOficina) || trim((string) $this->prm->telOficina) === ''
                || !$this->esTelefonoValido($this->prm->telOficina)) {
                $this->notifyErrorPlanUe('El teléfono de oficina es obligatorio y debe ser de 10 dígitos');

                return;
            }
            if (!isset($this->prm->extension) || trim((string) $this->prm->extension) === "" || !$this->esExtensionValida($this->prm->extension)) {
                $this->notifyErrorPlanUe('El número de extensión es obligatorio y solo debe contener números');

                return;
            }
            if (!isset($this->prm->objetivo) || trim((string) $this->prm->objetivo) === '') {
                $this->notifyErrorPlanUe('El objetivo de la unidad ejecutora es obligatorio');

                return;
            }
        }

        if (isset($this->prm->telCelular) && isset($this->prm->telOficina) && $this->telefonosIguales($this->prm->telCelular, $this->prm->telOficina)) {
            $this->notifyErrorPlanUe('El teléfono de oficina coincide con el teléfono celular, no pueden ser iguales');

            return;
        }

        if (!$this->exceptaObligatoriosUe && $ue->getNombramiento() === '') {
            $this->notifyErrorPlanUe('Nombramiento o documento que lo acredite es obligatorio');

            return;
        }

        $datosUeGuardados = $this->datosUeGuardadosParaValidacion();
        $contactos = ValidacionContactoUrUe::contactosDesdePrmUe($this->prm, $datosUeGuardados);
        if (count($contactos) > 0) {
            $validacionDuplicados = ValidacionContactoUrUe::validarLista(
                $contactos,
                [],
                [
                    'gpo_id' => $this->adm_grupo,
                    'ur_id'  => $this->adm_ur,
                    'ue_id'  => isset($this->prm->ue_id) ? $this->prm->ue_id : $this->UE,
                ],
                [
                    'gpo_id'  => $this->adm_grupo,
                    'ur_id'   => $this->adm_ur,
                    'alcance' => 'ur_y_ues',
                ]
            );
            if (!$validacionDuplicados['ok']) {
                $this->notifyErrorPlanUe($validacionDuplicados['mensaje']);

                return;
            }
        }
        $this->prm->abc = 'c';
        $this->prm->gpo_id = $this->adm_grupo;
        $this->prm->ur_id = $this->adm_ur;
        UnidadEjecutora::abc($this->prm);
        $this->mixScript('$("#toolsPantalla").html("");
            notify("Identificación Institucional actualizada exitosamente");
            comunica ("' . $this->pantalla . '",{metodo:"tablaUes"});        
        ');
    }

    private function telefonosIguales($celular, $telefono)
    {
        $celular = trim((string)$celular);
        $telefono = trim((string)$telefono);
        if ($celular === '' || $telefono === '') {
            return false;
        }

        return $celular === $telefono;
    }
}
if (isset($_SESSION[_S]['prm']['A']['1']['2']['1'])) {
    new ControladorPlanUe();
} else {
    WebResponse::sinPermisos();
}
