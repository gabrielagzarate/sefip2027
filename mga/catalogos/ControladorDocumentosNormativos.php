<?php

session_start();
require_once __DIR__ . '/../../lib/constantes.php';
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/Entidad.php';
require_once __DIR__ . '/../../ent/DocumentosNormativos.php';
require_once __DIR__ . '/../../lib/FicheroSefip.php';

class ControladorDocumentosNormativos extends WebResponse
{
    protected $nvj_id;
    protected $gpo_id;
    protected $ur_id;
    protected $usr_id;
    protected $ip;

    public function __construct()
    {
        parent::__construct();

        $this->nvj_id = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->gpo_id = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : false;
        $this->ur_id = isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : false;
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->pantalla = 'mga/catalogos/ControladorDocumentosNormativos.php';

        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
            case 4:
                switch ($this->metodo) {
                    case 'main':
                        $this->main();
                        break;
                    case 'cambiaEstado':
                        $this->cambiaEstado();
                        break;
                    case 'edit':
                        $this->edit();
                        break;
                    case 'abcDocNormativos':
                        $this->abcDocNormativos();
                        break;
                    case 'llenaTablaDocNormativos':
                        $this->llenaTablaDocNormativos();
                        break;
                    case 'nuevo':
                        $this->nuevo();
                        break;
                    case 'uploadArchivo':
                        $this->uploadArchivo();
                        break;
                    case 'generaTablaDocNormativos':
                        $this->generaTablaDocNormativos();
                        // no break
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    public function main()
    {

        $html = $this->generaTablaDocNormativos();
        $this->mixHtml($html);
        $this->llenaTablaDocNormativos();



    }

    private function generaTablaDocNormativos()
    {
        $btnnuevo = new stdClass();
        $btnnuevo->id = 'btnnuevo';
        $btnnuevo->name = 'btnnuevo';
        $btnnuevo->class = 'Nuevo';
        $btnnuevo->text = 'Nuevo';
        $btnnuevo->value = 'Nuevo';
        $btnnuevo->color = 'azul';
        $btnnuevo->icon = 'fa-solid fa-plus me-2';
        $botones = $this->ponBoton($btnnuevo);
        $this->agregaTools($botones);

        $tabla = new stdClass();

        $tabla->id = 'tbl_docnormativos';
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDocNormativos';
        $tabla->numFilas = 150;
        $tabla->filasAmostrar = [20,50,100,150,300,400,500];
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Editar';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'id Catalogo';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Título de Catálogo';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Descripción';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Habilitar/Deshabilitar';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        $td->class = 'btn-xs';

        $td->attr = new stdClass();
        $td->attr->dn_id = 'dn_id';
        $td->class = 'editCatalogo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'dn_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'dn_tituloCatalogo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'dn_descripcion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'sts_id';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'verde';
        $td->casos->verdadero->icono = 'check';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->dn_id = 'dn_id';
        $td->casos->verdadero->attr->status = '1';
        $td->casos->verdadero->class = 'habilitaDeshabilita';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->color = 'rojo';
        $td->casos->falso->icono = 'xmark';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->dn_id = 'dn_id';
        $td->casos->falso->attr->status = '2';
        $td->casos->falso->class = 'habilitaDeshabilita';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        return $this->wrPonTabla($tabla);

    }

    private function llenaTablaDocNormativos()
    {
        $this->conectar();
        $sql = 'SELECT dn_id, dn_tituloCatalogo, dn_descripcion, sts_id
                FROM cat_DocumentosNormativos
                GROUP BY dn_id';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_docnormativos';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros ","warning");');
        }
        $this->mixScript('
            $("#btnnuevo").click(function () {
                comunica("' . $this->pantalla . '", {
                    metodo: "nuevo"
                });
            });
            $(".editCatalogo").click(function () {
                comunica("' . $this->pantalla . '", {
                    metodo: "edit",
                    dn_id: $(this).attr("dn_id"),
                });
            });
            $(".habilitaDeshabilita").click(function () {
                var texto = "HABILITAR EL CATÁLOGO ";
                var status = $(this).attr("status");
                var dn_id = $(this).attr("dn_id");
                var clase = "btn-outline-success";
                var titulo = "Habilitar";
                if (status == 1) {
                    texto = "DESHABILITAR EL CATÁLOGO";
                    titulo = "Deshabilitar";
                    alertify.confirm(
                        "HABILITAR Y DESHABILITAR",
                        "¿ CONFIRMA QUE DESEA  " + texto + "?",
                        function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "cambiaEstado",
                                dn_id: dn_id,
                                status: status,
                                ' . ((isset($this->prm->dn_id) && $this->prm->dn_id != '') ? 'dn_id: ' . $this->prm->dn_id . ', ' : '') . '
                            }
                            );
                        },
                        function () { }
                    ).set("labels", { ok: titulo, cancel: "Cancelar" });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-success");
                    }
                    return false;
                } else {
                    texto = "HABILITAR EL CATÁLOGO";
                    titulo = "Habilitar";
                    alertify.confirm(
                        "HABILITAR Y DESHABILITAR",
                        "¿ CONFIRMA QUE DESEA  " + texto + "?",
                        function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "cambiaEstado",
                                dn_id: dn_id,
                                status: status,
                                ' . ((isset($this->prm->dn_id) && $this->prm->dn_id != '') ? 'dn_id: ' . $this->prm->dn_id . ', ' : '') . '
                            }
                            );
                        },
                        function () { }
                    ).set("labels", { ok: titulo, cancel: "Cancelar" });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-success");
                    }
                    return false;
                }
            });
        ');
    }

    private function edit()
    {
        $abc = 'c';
        $documentoN = new DocumentosNormativos();
        $documentoN->setDn_id($this->prm->dn_id);
        $documentoN->select();

        $prm = [$this->prm->dn_id];
        $this->conectar();
        $this->setSql('
            SELECT dn_tituloCatalogo,dn_descripcion
            FROM cat_DocumentosNormativos 
            WHERE dn_id = ? ');
        $this->consultar($prm);
        $this->desconectar();

        $btnregresar = new stdClass();
        $btnregresar->id = 'btnregresar';
        $btnregresar->name = 'btnregresarindex';
        $btnregresar->class = 'regresar';
        $btnregresar->text = 'Regresar';
        $btnregresar->value = 'Regresar';
        $btnregresar->color = 'gris';
        $btnregresar->icon = 'circle-left me-2';

        $guardar = new stdClass();
        $guardar->id = 'guardar';
        $guardar->name = 'guardar';
        $guardar->class = 'regresar';
        $guardar->text = 'Guardar';
        $guardar->color = 'verde';
        $guardar->value = 'Guardar';
        $guardar->icon = 'floppy-disk me-2';
        $guardar->type = 'submit';

        $botones = '';
        $botones .= $this->ponBoton($btnregresar);
        $botones .= $this->ponBoton($guardar);

        $tituloCatalogo = new stdClass();
        $tituloCatalogo->id = 'titulo';
        $tituloCatalogo->enDiv = true;
        $tituloCatalogo->label = 'Título de cátalogo:';
        $tituloCatalogo->value = $documentoN->getTituloCatalogo();

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->enDiv = true;
        $descripcion->label = 'Descripción:';
        $descripcion->text = $documentoN->getDescripcion();

        $labelarchivo = new stdClass();
        $labelarchivo->id = 'archivoNombre2';
        $labelarchivo->disabled = true;
        $labelarchivo->label = 'Nombre de archivo guardado';
        $labelarchivo->value = $documentoN->getNombreOriginal();

        $archivo = new stdClass();
        $archivo->id = 'Catálogo';
        $archivo->label = 'Cargar catálogo:';

        $this->mixHtml('
            <h2>Modifica Documentos Normativos</h2><br>
            <div class="row mb-4">
                <div class="col-md-6 col-xs-6">
                    ' . $this->ponInput($tituloCatalogo) . '
                </div>
                <div class="col-md-6 col-xs-6">
                    ' . $this->ponTextarea($descripcion) . '
                </div>
                <div class="col-md-6 col-xs-6">
                    ' . $this->ponInput($labelarchivo) . '
                </div>
                <div class="col-md-5 col-xs-5">
                <input id="archivoNombre" style="display:none"/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5 col-xs-5 mb-4">
                    <label>Cargar nuevo archivo: </label>
                    <input type="file" name ="cat" id="nombreCatalogo" accept="application/pdf"
                        data-url="mga/catalogos/ControladorDocumentosNormativos.php" data-form-data=\'{"metodo":"uploadArchivo"}\'/>
                    <div id="progressPrb" class="progress progress-striped">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->mixScript('

            document.getElementById("nombreCatalogo").onchange = function() {
                document.getElementById("archivoNombre").value = document.getElementById("nombreCatalogo").files[0].name;
            }

            $("#nombreCatalogo").fileupload({
                dataType:"json",
            done:function(e, data){
                    var validacion = data.result.validacion;
            $("#nombreCatalogo").text(validacion.nombre);
            alertify.alert("Carga de documentos", validacion.mensaje);
                },
            progress: function(e, data){
            var progress = parseInt(data.loaded / data.total * 100,10);
            $("#progressPrb .progress-bar").css("width", progress + "%")
            .attr("aria-valuenow",progress);
                }
            });

            $("#btnregresar").click(function(){
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '", {metodo:"main"});
            });

            $("#guardar").click(function(){
                var valido = true;
            if(valido == true){
                $("#editacatalogo").submit();
                    comunica("' . $this->pantalla . '",{
                metodo:"abcDocNormativos",
            tituloCatalogo:$("#titulo").val(),
            descripcion: $("#descripcion").val(),
            nombreArchivo: $("#archivoNombre").val(),
                        ' . (($abc == 'c') ? 'dn_id : ' . $documentoN->getDn_id() . ',' : '') . '
            abc: "' . $abc . '"
                    }, true);
                } else {
                alertify.alert("Documentos Normativos", "Existen campos vacios");
                }
            });
        ');
        $this->setContenedor('toolsPantalla');
        $this->mixHtml($botones);
    }

    private function abcDocNormativos()
    {
        $ur_clave = $this->gpo_id . $this->ur_id;
        $sts = 1;
        $tipo = 'PDF';
        $ejercicio = APP_EJERCICIO;
        $titulo = strtoupper($this->prm->tituloCatalogo);
        $descripcion = strtoupper($this->prm->descripcion);
        $this->prm->ur = $_SESSION[_S]['ur_id'];
        $namemd5 = md5($this->prm->nombreArchivo);
        $nameM = strtoupper($namemd5) . '.pdf';
        $ruta = 'Catalogos/' . $nameM;

        switch ($this->prm->abc) {
            case 'a':
                $sql = 'INSERT INTO cat_DocumentosNormativos(ur_id,dn_tituloCatalogo,dn_nombreOriginal,dn_nombreGuardado,dn_descripcion,dn_tipo,dn_ruta,dn_ejercicio,sts_id)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $prm = [$ur_clave, $titulo, $this->prm->nombreArchivo, $nameM, $descripcion, $tipo, $ruta, $ejercicio, $sts];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($prm);

                $sql = 'INSERT INTO ' . BD_BITACORA . '.bit_DocumentosNormativos (bit_observacion, gpo_id, ur_id, usr_ip, usr_id)
                        values (?, ?, ?, INET_ATON(?), ?);';
                $prm = ['REGISTRO DE NUEVO CATALOGO', $this->gpo_id, $this->ur_id, $this->ip, $this->usr_id];
                $conexion->setSql($sql);
                $conexion->consultar($prm);
                $conexion->desconectar();
                $this->mixScript(' notify("Registro Guardado"); ');
                break;
            case 'c':
                if ($this->prm->nombreArchivo != '') {
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql('
                        UPDATE cat_DocumentosNormativos
                        SET
                        ur_id = ?,
                        dn_tituloCatalogo = ?,
                        dn_nombreOriginal = ?,
                        dn_nombreGuardado = ?,
                        dn_descripcion = ?,
                        dn_ruta = ?
                        WHERE dn_id = ?
                    ');
                    $prm = [$ur_clave, $titulo, $this->prm->nombreArchivo, $nameM, $descripcion, $ruta, $this->prm->dn_id];
                    $conexion->consultar($prm);

                    $sql = 'INSERT INTO ' . BD_BITACORA . '.bit_DocumentosNormativos (bit_observacion,gpo_id,ur_id,usr_ip,usr_id)
                    values (?, ?, ?, INET_ATON(?), ?);';
                    $prm = ['ACTUALIZACIÓN DE CATALOGO ' . $this->prm->dn_id, $this->gpo_id, $this->ur_id, $this->ip, $this->usr_id];

                    
                    $conexion->setSql($sql);
                    $conexion->consultar($prm);
                    $conexion->desconectar();
                    $this->mixScript(' notify("Registro Actualizado"); ');
                } else {
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql('
                        UPDATE cat_DocumentosNormativos
                        SET
                        ur_id = ?,
                        dn_tituloCatalogo = ?,
                        dn_descripcion = ?
                        WHERE dn_id = ?;
                    ');
                    $prm = [$ur_clave, $titulo, $descripcion, $this->prm->dn_id];
                    $conexion->consultar($prm);

                    $sql = 'INSERT INTO ' . BD_BITACORA . '.bit_DocumentosNormativos (bit_observacion,gpo_id,ur_id,usr_ip,usr_id)
                    values (?, ?, ?, INET_ATON(?), ?);';
                    $prm = ['ACTUALIZACIÓN DE CATALOGO ' . $this->prm->dn_id, $this->gpo_id, $this->ur_id, $this->ip, $this->usr_id];
                    $conexion->setSql($sql);
                    $conexion->consultar($prm);
                    $conexion->desconectar();
                    $this->mixScript(' notify("Registro Actualizado"); ');
                }
                break;
        }
        $this->main();

    }

    private function nuevo()
    {
        $this->prm->tituloCatalogo = (isset($this->prm->tituloCatalogo)) ? $this->prm->tituloCatalogo : '';
        $abc = 'a';

        $btnregresar = new stdClass();
        $btnregresar->id = 'btnregresarindex';
        $btnregresar->name = 'btnregresarindex';
        $btnregresar->class = 'regresar';
        $btnregresar->text = 'Regresar';
        $btnregresar->value = 'Regresar';
        $btnregresar->color = 'gris';
        $btnregresar->icon = 'circle-left me-2';

        $guardar = new stdClass();
        $guardar->id = 'btnguardar';
        $guardar->name = 'guardar';
        $guardar->class = 'regresar';
        $guardar->text = 'Guardar';
        $guardar->color = 'verde';
        $guardar->value = 'Guardar';
        $guardar->icon = 'floppy-disk me-2';
        $guardar->type = 'submit';

        $botones = '';
        $botones .= $this->ponBoton($btnregresar);
        $botones .= $this->ponBoton($guardar);
        $this->agregaTools($botones);

        $tituloCatalogo = new stdClass();
        $tituloCatalogo->id = 'tituloCatalogo';
        $tituloCatalogo->label = 'Título de cátalogo:';
        $tituloCatalogo->maxlength = '100';
        $tituloCatalogo->required = true;

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->label = 'Descripción:';
        $descripcion->maxlength = '500';
        $descripcion->required = true;

        $archivo = new stdClass();
        $archivo->id = 'catalogo';
        $archivo->name = 'catalogo';
        $archivo->label = 'Cargar catálogo: ';
        $archivo->accept = 'application/pdf';

        $html = '';
        $html .= '<form>';
        $html .= '
        <div class="form-group ">
            <h2>Agrega Documentos Normativos</h2><br>
            <div class="row mb-4">
                <div class="col-md-5 col-xs-5">
                    ' . $this->ponInput($tituloCatalogo) . '
                </div>
                <div class="col-md-5 col-xs-5">
                    ' . $this->ponTextarea($descripcion) . '
                </div>
                <div class="col-md-5 col-xs-5">
                <input id="archivoNombre" style="display:none"/>
                </div>
            </div>
            <div class="row">
                <label>Cargar archivo: </label>
            </div>
            <div class="row">
                <div class="col-md-5 col-xs-5">
                    <input type="file" name ="cat" id="nombreCatalogo" accept="application/pdf"
                    data-url="mga/catalogos/ControladorDocumentosNormativos.php" data-form-data=\'{"metodo":"uploadArchivo"}\'/>
                    <label id="archivoNombrew"></label>
                    <div id="progressPrb" class="progress progress-striped">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        $html .= '
                    </table>
                </fieldset>
            </div>
        ';
        $html .= '
        <br>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            document.getElementById("nombreCatalogo").onchange = function () {
                document.getElementById("archivoNombre").value = document.getElementById("nombreCatalogo").files[0].name;
            }

            $("#nombreCatalogo").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#nombreCatalogo").text(validacion.nombre);
                    alertify.alert("Documentos Normativos", validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt(data.loaded / data.total * 100,10);
                    $("#progressPrb .progress-bar").css("width", progress + "%")
                    .attr("aria-valuenow",progress);
                }
            });

            $("#btnguardar").click(function(){
                var obj = {"metodo":"abcDocNormativos"};
                obj.abc = "' . $abc . '";
                obj.tituloCatalogo = $("#tituloCatalogo").val();
                obj.descripcion = $("#descripcion").val();
                obj.nombreArchivo = $("#archivoNombre").val();
                if(!comunica("' . $this->pantalla . '", obj,true))
                $(this).removeAttr("disabled");
                return false;
            });
            $("#btnregresarindex").click(function(){
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{metodo:"main"})
            });
        ');
    }

    private function uploadArchivo()
    {
        $ur_clave = $this->gpo_id . $this->ur_id;
        $name = 'cat';
        $namemd5 = md5($_FILES[$name]['name']);
        $nameM = strtoupper($namemd5);
        $respuesta = new stdClass();

        if (isset($_FILES[$name]) && $_FILES[$name]['error'] == '0') {
            $up = new FicheroSefip();
            $up->setTipos(['pdf']);
            $up->setFichero($_FILES[$name]);
            $up->setModulo('mga');
            $up->setfile($nameM . '.pdf');
            $up->setUrFichero($ur_clave);
            $up->setAnioFichero(APP_EJERCICIO);
            $up->setRuta('DocumentosNormativos/Catalogos');
            $nomDir = $up->getRutaMod();

            if ($nomDir === false && $up->getError() === true) {
                $this->setStatus('error');
                $respuesta->mensaje = $up->getMsg();

            } else {
                if ($up->upload()) {
                    $respuesta->mensaje = ' Archivo cargado con éxito';
                } else {
                    $this->setStatus('error');
                    $respuesta->mensaje = $up->getMensaje();
                }
            }
        } else {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrio un error al cargar el archivo';
        }
        $this->setValidacion($respuesta);
    }

    private function cambiaEstado()
    {
        $this->prm->status = isset($this->prm->status) ? $this->prm->status : '';

        if ($this->prm->status == 1) {
            $sts_id = ' sts_id = 2 ';
            $observacion = ' SE INHABILITO EL CATÁLOGO ' . $this->prm->dn_id;
            $mensaje = 'inhabilitado ';
        } else {
            $sts_id = ' sts_id = 1 ';
            $observacion = ' SE HABILITO EL CATÁLOGO ' . $this->prm->dn_id;
            $mensaje = 'habilitado ';
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            UPDATE cat_DocumentosNormativos 
            SET ' . $sts_id . '
            WHERE dn_id = ?;
        ');
        $prm = [$this->prm->dn_id];
        $conexion->consultar($prm);

        $conexion->setSql(' 
            INSERT INTO ' . BD_BITACORA . '.bit_DocumentosNormativos (bit_observacion,gpo_id,ur_id,usr_ip,usr_id)
            VALUES(?, ?, ?, INET_ATON(?), ?)
            ');
        $prm = [$observacion, $this->gpo_id, $this->ur_id, $this->ip, $this->usr_id];
        $conexion->consultar($prm);
        $conexion->desconectar();
        $this->mixScript(' comunica("' . $this->pantalla . '",{metodo:"main"})
            notify("El catálogo ' . $this->prm->dn_id . ' ha sido ' . $mensaje . '  correctamente ","success"); ');
    }
}
if (isset($_SESSION[_S]['prm']['M']['6']['5'])) {
    new ControladorDocumentosNormativos();
} 
