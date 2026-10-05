<?php

/**
 * Description of ControladorMensajesSistema
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 21-ago-2019
 * Comment: -----
 * Objective: Dar de alta los mensajes que el sistema que se muestra al inicio.
 * Regla de Negocio: -----
 *
 **/

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../ent/Mensajes.php';
set_time_limit(0);

class ControladorMensajesSistema extends WebResponse
{
    public $usr_id;
    public $ip;

    public function __construct()
    {
        // mandar a llamar constructor base
        parent::__construct();
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ip = $_SESSION[_S]['ses_ip'];

        switch ($this->metodo) {
            case 'guardaMensaje':
                $this->abcMensaje();
                break;
            case 'llenaTablaMensajes':
                $this->llenaTablaMensajes();
                break;
            case 'addModMensaje':
                $this->addModMensaje();
                break;
            case 'modalArchivo':
                $this->modalArchivo();
                break;
            case 'uploadArchivo':
                $this->uploadArchivo();
                break;
            case 'modalEjemplo':
                $this->modalEjemplo();
                break;
            case 'llenaTabla':
                $this->llenaTabla();
                break;
            default:
                $this->main();
                break; //main
        }

        $this->returnJson();
    } // fin __construct

    public function main()
    {
        $this->mixScript('           
            comunica("' . $this->pantalla . '", {
                    metodo:"llenaTabla",
                    "tabla":$(this).attr("nombre"),
                    "registros": $(this).attr("registros")
                });
                
            $( "#toggleCA" ).on( "click", function() {
                $("#tbl-mensajes thead").css("z-index", "0");
            });
        ');
    } // fin main

    public function llenaTabla()
    {
        $boton = new stdClass();
        $boton->id = 'nuevoMensaje';
        $boton->value = ' Nuevo Mensaje';
        $boton->icon = 'solid fa-plus';
        $boton->clase = 'me-1 ';
        $boton->color = 'azul';
        $boton->click = 'comunica("' . $this->pantalla . '",{metodo:"addModMensaje"})';
        $this->agregaTools($this->ponBoton($boton));

        $tabla = new stdClass();
        $tabla->id = 'tbl-mensajes';
        $tabla->titulo = 'Mensajes del Sistema';
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMensajes';
        $tabla->numFilas = 50;

        $tr = [];

        $th = new stdClass();
        $th->texto = 'Editar';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Mensaje';
        $th->width = '350px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Fecha Inicial';
        $th->width = '30px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Fecha Final';
        $th->width = '30px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Sistema';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Estado';
        $th->width = '5px';
        array_push($tr, $th);
        $trbody = [];

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'edit';

        $td->attr = new stdClass();
        $td->attr->sme_id = 'sme_id';
        $td->attr->inicio = 'sme_fechaInicial';
        $td->attr->fin = 'sme_fechaFinal';
        $td->class = 'editMensaje';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sme_Mensaje';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sme_fechaInicial';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sme_fechaFinal';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sme_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color';
        $td->color = 'color';
        $td->campoTxt = 'visible';
        $td->lbl = 'visible';
        $td->title = 'visible';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaMensajes();

    }

    private function llenaTablaMensajes()
    {
        $params = [];
        if ($this->usr_id == 181) {
            $user = ' a.usr_id IS NOT NULL';
        } else {
            $user = ' a.usr_id = ? ';
            $params = [$this->usr_id];
        }
        $this->conectar();
        $this->setSql('
            SELECT sme_id,sme_Mensaje,sme_fechaInicial,sme_fechaFinal,sme_sistema,sme_nombre,
            if( now() between sme_fechaInicial AND sme_fechaFinal,"006400","FF0000" ) color,
            if( now() between sme_fechaInicial AND sme_fechaFinal,"visible","vencido" ) visible
            FROM ' . BD_SINPRES . '.sis_Mensaje a
            INNER JOIN ' . BD_SINPRES . '.cat_SistemaMensaje using(sme_sistema)
            WHERE ' . $user . '
            ORDER BY a.usr_fecha DESC, sme_fechaFinal DESC
        ');

        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-mensajes';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".editMensaje").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo : "addModMensaje",
                    sme_id : $(this).attr("sme_id"),
                    inicio : $(this).attr("inicio"),
                    fin : $(this).attr("fin")
                });
            });
        ');
    } // fin llenaTablaMensajes

    public function addModMensaje()
    {
        $abc = 'a';
        $mensaje = new Mensajes();
        $links = '';
        if (isset($this->prm->sme_id)) {
            $abc = 'c';
            $mensaje->setSme_id($this->prm->sme_id);
            $mensaje->select();

            $this->conectar();
            $this->setSql('
                SELECT sam_nombre,sam_tipo,sam_descripcion,sam_rutaArchivo
                FROM ' . BD_SINPRES . '.sis_ArchivoMensaje
                WHERE sme_id = :sme_id ; ');
            $params = [
                ':sme_id' => $this->prm->sme_id,
            ];
            $this->consultar($params);
            $this->desconectar();
            while ($a = $this->extraer()) {
                switch ($a['sam_tipo']) {
                    case 'txt':
                        $tipo = 'txt';
                        break;
                    case 'pdf':
                        $tipo = 'pdf';
                        break;
                    case 'doc':case 'docx':
                        $tipo = 'doc';
                        break;
                    case 'xls':case 'xlsx':
                        $tipo = 'xls';
                        break;
                    default:
                        $tipo = 'img';
                        break;
                }

                $links .= '<li class="list-group-item primary me-1"><a target="_blank" href="' .
                    (($a['sam_rutaArchivo']) == '' ? 'archivos/adjuntosMensajes/' . $a['sam_nombre'] . '.' . $a['sam_tipo']
                    : $a['sam_rutaArchivo']) . '"><img src="img/' . $tipo . '.png" width="30px" />'
                    . $a['sam_descripcion'] . ' <span class="fa-solid fa-file-arrow-down"></span></a></li> ';
            }
        }

        $regresar = new stdClass();
        $regresar->id = 'back';
        $regresar->color = 'gris';
        $regresar->class = 'me-1';
        $regresar->value = ' Regresar';
        $regresar->icon = 'solid fa-circle-left me-2';
        $regresar->click = '
            comunica("' . $this->pantalla . '");
        ';

        $guardar = new stdClass();
        $guardar->id = 'guardar';
        $guardar->class = 'me-1';
        $guardar->value = ' Guardar Mensaje';
        $guardar->icon = 'solid fa-floppy-disk me-2';
        $guardar->color = 'verde';
        $guardar->click = '
            if ( $("#tipoMensaje").val()=="" ) {
                alert("Selecciona el tipo de Mensaje");
                return false;
            }
            if ( $("#color").val()=="" ) {
                alert("Selecciona el color del encabezado del mensaje");
                return false;
            }
            if ( $("#sistema").val()=="" ) {
                alert("Selecciona el ejercicio o el sistema ");
                return false;
            }
            if ( $("#asunto").val()=="" ) {
                alert("Captura el asunto del mensaje ");
                return false;
            }

            if ( $("#inicioFecha").val()=="" ) {
                alert("Captura la fecha de inicio del mensaje  ");
                return false;
            }

            if ( $("#finFecha").val()=="" ) {
                alert("Captura la fecha de termino  del mensaje  ");
                return false;
            }

            var archivos =[];
            $("#archivos li").each(function(){
                archivo = {};
                archivo.nombre = $(this).children("a").attr("nombre");
                archivo.tipo = $(this).children("a").attr("tipo");
                archivo.tamaño = $(this).children("a").attr("tamaño");
                archivos.push(archivo);
            });
            comunica("' . $this->pantalla . '",{
                metodo:"guardaMensaje",
                asunto:$("#asunto").val(),
                mensaje: $("#mensaje").html(),
                fechaInicial: $("#inicioFecha").val(),
                horaInicio: $("#horaInicio").val(),
                fechaFinal: $("#finFecha").val(),
                horaFin: $("#horaFin").val(),
                color: $("#color").val(),
                sistema: $("#sistema").val(),
                tipo: $("#tipoMensaje").val(),
                ' . (($abc == 'c') ? 'sme_id : ' . $mensaje->getSme_id() . ',' : '') . '
                archivos: archivos,
                abc: "' . $abc . '"
            },true);
        ';

        $color = new stdClass();
        $color->id = 'color';
        $color->class = 'limpiar';
        $color->form = 'enviaPost';
        $color->sql = '
            SELECT "1" AS sme_color, "VERDE" AS color_nombre
            UNION ALL
            SELECT "2" AS sme_color, "AMARILLO" AS color_nombre
            UNION ALL
            SELECT "3" AS sme_color, "ROJO" AS color_nombre;';
        $color->label = 'Seleccionar el color  ';
        $color->enRow = 1;
        $color->selecciona = $mensaje->getColor();
        $color = $this->ponCombo($color);

        $botonAchivo = new stdClass();
        $botonAchivo->id = 'subirArchivo';
        $botonAchivo->color = 'amarillo';
        $botonAchivo->value = ' Subir Archivo';
        $botonAchivo->icon = 'cloud-upload';
        $botonAchivo->click = '
            comunica("' . $this->pantalla . '",{metodo:"modalArchivo"})
        ';

        $this->agregaTools('
            ' . $this->ponBoton($regresar) . '
            ' . $this->ponBoton($guardar) . '
        ');

        $inicioFecha = new stdClass();
        $inicioFecha->id = 'inicioFecha';
        $inicioFecha->class = 'datepicker';
        $inicioFecha->enDiv = true;
        $inicioFecha->required = true;
        $inicioFecha->label = 'Fecha de Inicio';
        $inicioFecha->formato = 'fecha';
        $inicioFecha->value = $mensaje->getFechaInicial();

        $hfin = $hinicio = '';
        if ($abc == 'c') {
            if (isset($this->prm->inicio) && $this->prm->inicio != '') {
                $hinicio = substr($this->prm->inicio, 11, 5);
                $this->mixScript(' $("#horaInicio").val("' . $hinicio . '"); ');
            }

            if (isset($this->prm->fin) && $this->prm->fin != '') {
                $hfin = substr($this->prm->fin, 11, 5);
                $this->mixScript(' $("#horaFin").val("' . $hfin . '"); ');
            }
        }

        $finFecha = new stdClass();
        $finFecha->id = 'finFecha';
        $finFecha->class = 'datepicker';
        $finFecha->enDiv = true;
        $finFecha->required = true;
        $finFecha->label = 'Fecha de Fin';
        $finFecha->formato = 'fecha';
        $finFecha->value = $mensaje->getFechaFinal();

        $boton = new stdClass();
        $boton->class = 'btn-xs formato';
        $boton->color = 'cielo';

        $boton->id = 'bold';
        $boton->icon = 'bold';
        $boton->click = 'formatDoc("bold");';
        $bBold = $this->ponBoton($boton);

        $boton->id = 'italic';
        $boton->icon = 'italic';
        $boton->click = 'formatDoc("italic");';
        $bItalic = $this->ponBoton($boton);

        $boton->id = 'aleft';
        $boton->icon = 'align-left';
        $boton->click = 'formatDoc("justifyleft");';
        $bAleft = $this->ponBoton($boton);

        $boton->id = 'acenter';
        $boton->icon = 'align-center';
        $boton->click = 'formatDoc("justifycenter");';
        $bAcenter = $this->ponBoton($boton);

        $boton->id = 'aright';
        $boton->icon = 'align-right';
        $boton->click = 'formatDoc("justifyright");';
        $bAright = $this->ponBoton($boton);

        $boton->id = 'ileft';
        $boton->icon = 'indent';
        $boton->click = 'formatDoc("outdent");';
        $bIleft = $this->ponBoton($boton);

        $boton->id = 'iright';
        $boton->icon = 'outdent';
        $boton->click = 'formatDoc("indent");';
        $bIright = $this->ponBoton($boton);

        $sistema = new stdClass();
        $sistema->id = 'sistema';
        $sistema->class = 'limpiar';
        $sistema->form = 'enviaPost';
        $sistema->sql = 'SELECT sme_sistema,sme_nombre FROM sinpres.cat_SistemaMensaje';
        $sistema->label = 'Seleccionar Ejercicio/Sistema ';
        $sistema->enRow = 1;
        $sistema->selecciona = $mensaje->getSistema();
        $sistema = $this->ponCombo($sistema);

        $tipoMensaje = new stdClass();
        $tipoMensaje->id = 'tipoMensaje';
        $tipoMensaje->class = 'limpiar';
        $tipoMensaje->form = 'enviaPost';
        $tipoMensaje->sql = 'SELECT "N" AS sme_tipo, "NORMAL" AS sme_nombre UNION ALL SELECT "M" AS sme_tipo, "MODAL" AS sme_nombre;';
        $tipoMensaje->label = 'Seleccionar el tipo de Mensaje ';
        $tipoMensaje->enRow = 1;
        $tipoMensaje->selecciona = $mensaje->getTipo();
        $tipoMensaje = $this->ponCombo($tipoMensaje);

        $asunto = new StdClass();
        $asunto->id = 'asunto';
        $asunto->label = 'Asunto del Mensaje';
        $asunto->placeholder = 'ASUNTO DEL MENSAJE';
        $asunto->enDiv = true;
        $asunto->value = $mensaje->getAsunto();

        $botonEjemplo = new stdClass();
        $botonEjemplo->id = 'botonEjemplo';
        $botonEjemplo->color = 'verde';
        $botonEjemplo->value = ' Ejemplo';
        $botonEjemplo->icon = 'eye';
        $botonEjemplo->click = '
            comunica("' . $this->pantalla . '",{metodo:"modalEjemplo"})
        ';
        $html = '';
        $html .= '
            <div class="row col-md-12 col-xs-12">
                <legend>Estructura del Mensaje</legend>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $tipoMensaje . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $color . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $sistema . '
                </fieldset>
            </div>
            <br>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponInput($asunto) . '
                </fieldset>
            </div>
            <br>
            <div class="col-md-12 col-xs-12">
            <fieldset class="col-md-12 col-xs-12">
                ' . $bBold . ' ' . $bItalic . ' ' . $bAleft . ' ' . $bAcenter . ' ' . $bAright . ' ' . $bIleft . ' ' . $bIright . '
            </fieldset>
            </div>
            <br/>
            <div id = "mensajeEjemplo" class="panel panel-primary mb-4">
                <div class="form-control mb-4" contentEditable="true" id = "mensaje">' . (($abc == 'a') ? 'Este es el cuerpo del mensaje' : $mensaje->getMensaje()) . '</div>
                <!-- List group -->
                <ul class="list-group list-group-numbered" id="archivos">' . $links . '</ul>
            </div>
            <div class="row col-md-12 col-xs-12">
                <legend>Vigencia del Mensaje</legend>
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($inicioFecha) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    <label>Hora de Inicio</label>
                    <select   style=" width: 50% !important;" class="clean form-control" id="horaInicio" name="horaI">
        ';
        for ($i = 0; $i < 24; $i++) {
            $h1 = sprintf('%02d', $i) . ':00';
            $h2 = sprintf('%02d', $i) . ':30';
            $html .= '<option value="' . $h1 . '">' . $h1 . '</option><option value="' . $h2 . '">' . $h2 . '</option>';
        }
        $html .= '
            </select>
            </fieldset>
            <fieldset class="col-md-2 col-xs-2">
                ' . $this->ponInput($finFecha) . '
            </fieldset>
            <fieldset class="col-md-3 col-xs-3">
                <label>Hora Final</label>
                <select style=" width: 50% !important;" class="clean form-control" id="horaFin" name="horaF">
        ';
        for ($i = 0; $i < 24; $i++) {
            $h1 = sprintf('%02d', $i) . ':00';
            $h2 = sprintf('%02d', $i) . ':30';
            $html .= '<option value="' . $h1 . '">' . $h1 . '</option> <option value="' . $h2 . '">' . $h2 . '</option>';
        }
        $html .= '
                    </select>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <label>Subir/adjuntar archivo</label>
                    ' . $this->ponBoton($botonAchivo) . '
                </fieldset>
            </div>
            <br>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-12 col-xs-12" id="ModalExample" style="z-index:999999;">
                </fieldset>
            </div>
            <br/>
        ';

        $this->mixHtml($html);
        $this->mixScript(' initDoc("mensaje"); ');
    } // fin addModMensaje

    private function abcMensaje()
    {
        $asunto = '';
        $asunto = strtoupper($this->prm->asunto);

        $asunto = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', "'"], ['&aacute;', '&eacute;', '&iacute', '&oacute', '&uacute', '&ntilde', '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute;', '&Ntilde', '&#39;'], $asunto);
        $this->prm->mensaje = str_replace("'", '"', $this->prm->mensaje);

        $seg = ':00';
        $this->prm->fechaInicial = isset($this->prm->fechaInicial) ? $this->date_to_mysqldate($this->prm->fechaInicial) : '';
        $this->prm->fechaFinal = isset($this->prm->fechaFinal) ? $this->date_to_mysqldate($this->prm->fechaFinal) : '';
        $horaInicio = isset($this->prm->horaInicio) ? $this->prm->horaInicio . $seg : '00:00:00';
        $horaFin = isset($this->prm->horaFin) ? $this->prm->horaFin . $seg : '00:00:00';
        $ip = (isset($this->ip) && $this->ip != '::1') ? $this->ip : '127.0.0.1';
        $user = $this->usr_id;

        switch ($this->prm->abc) {
            case 'a':
                $this->conectar();
                $this->setSql('
                    INSERT INTO ' . BD_SINPRES . '.sis_Mensaje(sme_asunto,sme_mensaje,sme_fechaInicial,sme_fechaFinal,sme_tipo,sme_sistema,sme_color,usr_id,usr_fecha)
                    VALUES(:asunto,:mensaje,:fechainicial,:fechafinal,:tipo,:sistema,:color,:user,NOW());
                ');
                $params = [
                    ':asunto'       => $asunto,
                    ':mensaje'      => $this->prm->mensaje,
                    ':fechainicial' => $this->prm->fechaInicial . ' ' . $horaInicio,
                    ':fechafinal'   => $this->prm->fechaFinal . ' ' . $horaFin,
                    ':tipo'         => $this->prm->tipo,
                    ':sistema'      => $this->prm->sistema,
                    ':color'        => $this->prm->color,
                    ':user'         => $user,
                ];

                $this->consultar($params);
                $this->desconectar();
                $sme_id = $this->getUltimoId();

                if (isset($this->prm->archivos)) {
                    $sql = ' INSERT INTO ' . BD_SINPRES . '.sis_ArchivoMensaje ( sme_id,sam_nombre,sam_tipo,sam_tamanio,sam_descripcion,usr_id,sam_rutaArchivo ) VALUES (:sme_id,:nombrearchivo,:tipo,:tamanio,:nombre,:user,:ruta)';
                    foreach ($this->prm->archivos as $archivo) {
                        $rutaArchivo = $_SERVER['HTTP_REFERER'] . 'archivos/adjuntosMensajes/' . md5($archivo->nombre) . '.' . $archivo->tipo;
                        $params = [
                            ':sme_id'        => $sme_id,
                            ':nombrearchivo' => md5($archivo->nombre),
                            ':tipo'          => $archivo->tipo,
                            ':tamanio'       => $archivo->tamaño,
                            ':nombre'        => $archivo->nombre,
                            ':user'          => $user,
                            ':ruta'          => $rutaArchivo,
                        ];
                        $sql = trim($sql, ',');
                        $this->conectar();
                        $this->setSql($sql);
                        $this->consultar($params);
                        $this->desconectar();
                    }
                }
                $this->mixScript(' notify("Mensaje Guardado"); ');
                break;
            case 'c':
                $this->conectar();
                $this->setSql('
                    UPDATE ' . BD_SINPRES . '.sis_Mensaje
                    SET
                    sme_asunto = :asunto,
                    sme_mensaje = :mensaje,
                    sme_fechaInicial = :fechainicial,
                    sme_fechaFinal = :fechafinal,
                    sme_tipo = :tipo,
                    sme_sistema = :sistema,
                    sme_color = :color,
                    usr_id = :user,
                    usr_fecha = NOW()
                    WHERE sme_id = :sme_id;
                ');

                $params = [
                    ':asunto'       => $asunto,
                    ':mensaje'      => $this->prm->mensaje,
                    ':fechainicial' => $this->prm->fechaInicial . ' ' . $horaInicio,
                    ':fechafinal'   => $this->prm->fechaFinal . ' ' . $horaFin,
                    ':tipo'         => $this->prm->tipo,
                    ':sistema'      => $this->prm->sistema,
                    ':color'        => $this->prm->color,
                    ':user'         => $user,
                    ':sme_id'       => $this->prm->sme_id,
                ];

                $this->consultar($params);
                $this->desconectar();

                if (isset($this->prm->archivos)) {
                    $sql = ' INSERT INTO ' . BD_SINPRES . '.sis_ArchivoMensaje ( sme_id,sam_nombre,sam_tipo,sam_tamanio,sam_descripcion,usr_id,sam_rutaArchivo ) VALUES (:sme_id,:nombrearchivo,:tipo,:tamanio,:nombre,:user,:ruta)';
                    foreach ($this->prm->archivos as $archivo) {
                        $rutaArchivo = $_SERVER['HTTP_REFERER'] . 'archivos/adjuntosMensajes/' . md5($archivo->nombre) . '.' . $archivo->tipo;
                        $params = [
                            ':sme_id'        => $this->prm->sme_id,
                            ':nombrearchivo' => md5($archivo->nombre),
                            ':tipo'          => $archivo->tipo,
                            ':tamanio'       => $archivo->tamaño,
                            ':nombre'        => $archivo->nombre,
                            ':user'          => $user,
                            ':ruta'          => $rutaArchivo,
                        ];
                        $sql = trim($sql, ',');
                        $this->conectar();
                        $this->setSql($sql);
                        $this->consultar($params);
                        $this->desconectar();
                    }
                }

                $this->mixScript(' notify("Mensaje Actualizado"); ');
                break;
        }
        $this->main();
    } // fin abcMensaje

    private function modalArchivo()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $this->mixHtml('
            <div id="myModal" class="modal" style="display: block;" >
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Subir Archivo</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-outline-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>

                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <form>
                                    <fieldset class="col-md-12 col-xs-12">
                                        <input type="file" name ="adjunto" id="nombreArchProblema" data-url="mga/ControladorMensajesSistema.php" data-form-data=\'{"metodo":"uploadArchivo"}\'/>
                                        <div id="progressPrb" class="progress progress-striped">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                                <span class="sr-only">100% Complete</span>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").modal();
            $("#myModal").on(function (e) { $("#myModal").remove(); });
            $(".cerrar").click(function(){ $("#myModal").remove(); });
            $("#nombreArchProblema").click(function(){
                $("#progress .progress-bar").css("width","0%" ).attr("aria-valuenow",0);
            });

            $("#nombreArchProblema").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    if(validacion.error){
                        $("#progress .progress-bar").css("width","0%" ).attr("aria-valuenow",0);
                        notify(validacion.mensaje);
                    }else{
                        notify(validacion.mensaje);
                        $("#archivos").append(

                            $("<li/>").addClass("list-group-item me-2").html(
                                $("<a/>").attr("target","_blank")
                                    .attr("nombre",validacion.nombre)
                                    .attr("tamaño",validacion.tamaño)
                                    .attr("tipo",validacion.tipo)
                                    .attr("href","archivos/adjuntosMensajes/"+validacion.nombre)
                                    .html(validacion.nombre)
                                    .append( $("<span/>").addClass("fa-solid fa-circle-arrow-down") )

                            )
                        );
                        $("#myModal").hide();
                    }
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressPrb .progress-bar").css("width", progress + "%" ).attr("aria-valuenow",progress);
                }
            });
        ');
    } // fin modalArchivo

    private function modalEjemplo()
    {
        $this->setContenedor('ModalExample');
        $html = '';
        $html .= '
            <div id="modalMensajes" class="modal fade">
                <div class="modal-dialog">
                    <br><br><br>
                    <div class="modal-content">

                        <div class="modal-header success ">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title">Asunto de la modal ejemplo (Circular)</h4>
                        </div>
                        <div class="modal-body">
                        <b>Cuerpo del mensaje  Ejemplo<br><br>
                            <p>
                                Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem.'
                                . 'Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum '
                                . 'semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a,
                            </p>
                        </b></div>
                        <div class="modal-footer">
                            <ul class="list-group">links de los documentos que se suben</ul>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#modalMensajes").modal();
            $("#modalMensajes").on("hidden.bs.modal", function (e) {
                $("#modalMensajes").remove();
            })
        ');
    } // fin modalEjemplo

    private function uploadArchivo()
    {
        $respuesta = new stdClass();
        $allowed = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'txt', 'png', 'jpg'];

        if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] == 0) {
            $extension = pathinfo($_FILES['adjunto']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $allowed)) {
                $this->setStatus('error');
                $respuesta->error = true;
                $respuesta->mensaje = 'La extensión del archivo no esta permitida';
            } else {
                $nomDir = dirname(__FILE__) . '/../archivos/adjuntosMensajes/';
                $respuesta->nombre = $_FILES['adjunto']['name'];
                $respuesta->tamaño = $_FILES['adjunto']['size'];
                $respuesta->tipo = $extension;

                if (file_exists($nomDir)) {
                    move_uploaded_file($_FILES['adjunto']['tmp_name'], $nomDir . md5($respuesta->nombre) . '.' . $extension);
                    $this->setStatus('success');
                    $respuesta->mensaje = 'Archivo cargado con éxito';
                } else {
                    mkdir($nomDir);
                    move_uploaded_file($_FILES['adjunto']['tmp_name'], $nomDir . md5($respuesta->nombre) . '.' . $extension);
                    $this->setStatus('success');
                    $respuesta->mensaje = 'Archivo cargado con éxito';
                }
            }
        }
        $this->setValidacion($respuesta);
    } // fin uploadArchivo
} // fin class

new ControladorMensajesSistema();
