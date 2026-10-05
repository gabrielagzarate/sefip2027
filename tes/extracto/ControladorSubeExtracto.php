<?php

declare(strict_types=1);
session_start();

error_reporting(E_ALL);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/conciliacion/lib/procesaExtracto.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelScp.php';

error_reporting(E_ALL);
class ControladorSubeExtracto extends WebResponse
{
    private $btnregresa;
    private $lectura = false;
    private $usr_id;
    private $model;

    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION[_S])) {
            $this->mixScript('notify("Error de Sesion","error");');
            return;
        }
        $this->pantalla = "tes/extracto/ControladorSubeExtracto.php";
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->btnregresa = new stdClass();
        $this->btnregresa->id = 'btnregresa';
        $this->btnregresa->name = 'btnregresa';
        $this->btnregresa->value = 'Regresar';
        $this->btnregresa->type = 'button';
        $this->btnregresa->color = 'gris';
        $this->btnregresa->icon = "circle-left me-1";
        $this->btnregresa = $this->ponBoton($this->btnregresa);
        $this->model = new modelScp();

        switch ($this->metodo) {
            case "subeExtracto":
                $this->subeExtracto();
                break;
            case "dispersarFichero":
                $this->dispersarFichero();
                break;
            case "upload":
                $this->upload();
                break;
            case "listaFicheros":
                $this->listaFicheros();
                break;
            case "procesarFichero":
                $this->procesarFichero();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }
    public function  main()
    {
        $arriba = '';
        $abajo = '';
        $this->conectar();
        $this->agregaTools("");
        $this->setSql('
            SELECT iba_abreviatura,iba_nombre,iba_id
            FROM tesoreria.cat_InstitucionBancaria
            WHERE iba_participante ="R"
            AND iba_id IN(1)
        ');
        $this->consultar();
        $nombre = '';
        while ($res = $this->extraer()) {
            $nombre = $res['iba_abreviatura'];
            $nombre = preg_replace("/\.| |,/", "", $nombre);
            $arriba .= '
                    <div titpp="' . $res['iba_nombre'] . '" class="boton_opcion" iba_id="' . $res['iba_id'] . '" metodo="subir"
                        nombre ="' . $res['iba_abreviatura'] . '">
                        <div class="boton_opcion_titulo" >' . $res['iba_abreviatura'] . '</div>
                        <div>
                            <div>
                                <img  src="img/logoBancario/' . strtolower($nombre) . '.png"><br/>
                                <small>Subir Ficheros</small>
                            </div>
                        </div>
                    </div>
                    <div style="width:10px; display:table-cell"></div>
                    ';
            $abajo .= '
                    <div titpp="' . $res['iba_nombre'] . '" class="boton_opcion" iba_id="' . $res['iba_id'] . '" metodo="procesar"
                        nombre ="' . $res['iba_abreviatura'] . '">
                        <div class="boton_opcion_titulo" >' . $res['iba_abreviatura'] . '</div>
                        <div>
                            <div>
                                <img  src="img/logoBancario/' . strtolower($nombre) . '.png"><br/>
                                <small>Procesar Subidos</small>
                            </div>
                        </div>
                    </div>
                    <div style="width:10px; display:table-cell"></div>';
        }


        $this->mixHtml('
            <div style="width:100%; padding-bottom:120px;">
                <center>
                ' . $arriba . '
            </center>
            </div>

            <div style="width:100%; padding-bottom:120px;">
            <center>
                ' . $abajo . '
            </center></div>
        ');
        $chec = new stdClass();
        $chec->id = "afectaciones";
        $chec->name = "afectaciones";
        $chec->checked = true;
        $chec->label = "Servicios Personales";
        $chec->enDiv = true;
        $chec->orientacion = "H";
        $chec->debug = false;

        $this->mixScript('
            $(".boton_opcion").click(function(){
                op = $(this).attr("metodo");
                switch (op) {
                    case "subir":
                        comunica("' . $this->pantalla . '", {
                            metodo:"subeExtracto",
                            banco:$(this).attr("nombre"),
                            iba_id:$(this).attr("iba_id")
                        });
                        break;
                    case "procesar":
                        comunica("' . $this->pantalla . '", {
                            metodo:"listaFicheros",
                            banco:$(this).attr("nombre"),
                            iba_id:$(this).attr("iba_id"),
                            anio:$("#ejr_id").val()
                        });
                        break;
                }
            });
        ');
    }
    public function  subeExtracto()
    {
        $this->agregaTools($this->btnregresa);
        $html = '
            <legend>Cargue los ficheros de su(s) extracto(s)</legend><br/>
            <div id="dropzone" class="row">
                <fieldset  class="col-md-6 col-xs-6" style="min-height:80px;">
                    <div style="width:100%;">
                        Suba sus archivos uno a uno<br>
                        <input type="file" name ="objetivosFile" id="nombreArchObj"
                            data-url="' . $this->pantalla . '"
                            data-form-data=\'{"metodo":"upload","origen":"input","banco":"' . $this->prm->iba_id . '"}\' />
                            <div id="progressObj" class="progress progress-striped">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                        <span class="sr-only">100% Complete</span>
                                    </div>
                            </div>
                    </div>
                </fieldset>
                <fieldset  class="col-md-6 col-xs-6" style="min-height:80px; max-height:80px; overflow:auto; ">
                    <div style="width:100%;" id="listaArchivos"></div>
                </fieldset>
            </div>
            <hr/>
            <legend><center> o Bien</center></legend>
            <div id="dragndrop" style="">
                <div style="text-align:center;">
                    <span style="font-size:170%"> Arrastre sus archivos aqui (maximo 10)</span>
                </div>
                <div class="statusbar2 odd">
                    <div class="infofile filename">Nombre del Archivo</div>
                    <div class=" infofile filesize">Tamaño</div>
                    <div class="infofile  ">Tipo</div>
                    <div class="infofile bar">Progreso</div>
                    <div class="infofile abort ">Borrar Archivo</div>
                </div>
            </div>
            <div class="alert alert-info">Los extractos solo son subidos aqui, despues tiene que ir a la pantalla de procesar, para procesar su contentido</div>';
        $this->mixHtml($html);

        $this->mixScript('
            $("#btnregresa").click(function(){ 
                comunica("' . $this->pantalla . '");
            });

            $("#nombreArchObj").fileupload({
                add: function(e, data) {
                    var tipos =["TXT","ZIP","","2001007F1200001","TEXT/PLAIN"];
                    if (data.originalFiles[0][\'type\'].length && tipos.indexOf(data.originalFiles[0][\'type\'].toUpperCase())==-1) {
                            $("#listaArchivos").append(\'El archivo \'+ data.originalFiles[0][\'name\']+\' no es permitido<br>\' + data.originalFiles[0][\'type\'].toUpperCase());
                            return false;
                    }
                    if (data.originalFiles[0][\'size\'] > 20971500) {
                        $("#listaArchivos").append(\'El archivo \'+ data.originalFiles[0][\'name\']+\' es muy grande<br>\');
                        return false;
                    }
                    data.submit();

                },
                dropZone: $("#dropzone"),
                autoUpload:false,
                dataType:"json",
                done:function(e, data){},
                progress: function(e, data){
                    var progress = parseInt(data.loaded / data.total * 100,10);
                    $("#progressObj .progress-bar").css("width", progress + "%").attr("aria-valuenow",progress);
                },
                success: function(data) {
                    $("#progressObj .progress-bar").css("width","0%");
                    comunicaCallback(data.comunica);
                }
            });

            var obj = $("#dragndrop");
            obj.on("dragenter", function (e){
                e.stopPropagation();
                e.preventDefault();
                $(this).css("border", "2px solid #AE8B1C");
            });

            obj.on("dragover", function (e)    {
                    e.stopPropagation();
                    e.preventDefault();
            });
            obj.on("drop", function (e){
                $(this).css("border", "2px dotted #AE8B1C");
                e.preventDefault();
                var files = e.originalEvent.dataTransfer.files;
                console.log(files);
                if (files.length>15){
                    notify("Intenta con una cantidad menor  a  15 archivos","warning");
                    return false;
                }
                handleFileUpload(files,obj);
            });

            /** PREVENIR QUE EL ARCHIVO QUE SE SUELTA FUERA DEL DIV NO SE CARGUE EN EL EXPLORADOR  */
            $(document).on("dragenter", function (e){
                e.stopPropagation();
                e.preventDefault();
            });
            $(document).on("dragover", function (e) {
                e.stopPropagation();
                e.preventDefault();
                obj.css("border", "2px dotted #AE8B1C");
            });

            $(document).on("drop", function (e)
            {
                e.stopPropagation();
                e.preventDefault();
            });
            /** PREVENIR QUE EL ARCHIVO QUE SE SUELTA FUERA DEL DIV NO SE CARGUE EN EL EXPLORADOR  */

            function handleFileUpload (files,obj)
            {
                var tipos =["TXT","ZIP","","2001007F1200001","TEXT/PLAIN"];
                for (var i = 0; i < files.length; i++) {
                    var fd = new FormData();
                    fd.append("file", files[i]);
                    fd.append("metodo","upload");
                    fd.append("origen","multiple");
                    var status = new createStatusbar(obj);
                    fd.append("id",status.getId());
                    fd.append("banco","' . $this->prm->iba_id . '");
                    status.setFileNameSize(files[i].name, files[i].size);
                    ext = files[i].name.split(".").pop().toUpperCase();
                    status.setIcon(ext);

                    if (files[i].size> 20971500) {
                        status.statusbar.addClass("error2");
                        status.abort.html("El archivo es mayor a 20 Megabytes");
                    } else if(tipos.indexOf(ext)==-1) {
                        status.statusbar.addClass("error2");
                        status.abort.html("archivo no valido");
                    } else {
                        sendFileToServer(fd,status);
                    }
                }
            }
            var rowCount=0;
            function createStatusbar (obj) {
                rowCount++;
                var row="odd";
                if(rowCount %2 ==0) row ="even";
                this.statusbar = $("<div id=\'statusbar2"+ rowCount + "\' class=\'statusbar2 "+row+"\'></div>");
                this.filename = $("<div class=\'infofile filename\'></div>").appendTo(this.statusbar);
                this.size = $("<div class=\' infofile filesize\'></div>").appendTo(this.statusbar);
                this.icon = $("<div class=\'infofile  \'></div>").appendTo(this.statusbar);
                this.progressBar = $("<div class=\'infofile bar\'><div class=\"progressBar\"></div></div>").appendTo(this.statusbar);
                this.idFile = $("<div class=\'infofile abort  \'></div>").appendTo(this.statusbar);
                this.abort = $("<div class=\'infofile  abort\'>Abort</div>").appendTo(this.statusbar);

                obj.append(this.statusbar);
                this.getId = function() {
                    return  "statusbar2"+rowCount;
                }
                this.setIcon = function(extension){
                    icono = "";
                    switch(extension){
                        case "PDF":
                            icono = "pdf";
                            break;
                        case "TXT":
                            icono = "txt";
                            break;
                        case "XML":
                            icono = "xml";
                            break;
                        case "DOC":case "DOCX":
                            icono = "word";    
                            break;
                        case "XLS":case "XLSX":
                            icono = "excel";
                            break;
                        case "JPG":
                        case "JPGE":
                            icono = "jpg";
                            break;
                        case "PNG":         
                            icono = "png";
                            break;
                        case "MSI":
                            icono = "verboten";
                            break;
                        default:
                            icono = "verboten";
                            break;
                    }
                    this.icon.html("<img src=\'img/24pxIcon/"+ icono+".png \' height=\"21\" />");
                }
                this.setIdFile = function (nombre) {
                    this.idFile.html("ID:" + nombre + "<span class=\"deletefile\" id=\'"+nombre+"\'>Borrar X<span/>");
                }

                this.getFileName = function(){    
                    return this.filename.html();    
                }
                this.setFileNameSize = function(name,size){
                    var sizeStr="";
                    var sizeKB = size/1024;
                    if (parseInt(sizeKB) > 1024) {
                        var sizeMB = sizeKB/1024;
                        sizeStr = sizeMB.toFixed(2)+" MB";
                    } else {
                        sizeStr = sizeKB.toFixed(2)+" KB";
                    }
                    this.filename.html(name);
                    this.size.html(sizeStr);
                }

                this.setProgress = function(progress) {
                    var progressBarWidth =progress*this.progressBar.width()/ 100;
                    this.progressBar.find(\'div\').animate({ width: "100%" }, 10).html(progress + "% ").css("width",progress+"px");
                    if (parseInt(progress) >= 100) {
                        this.abort.hide();
                    }
                }
                this.setAbort = function(jqxhr) {
                    var sb = this.statusbar;
                    this.abort.click(function(){
                        jqxhr.abort();
                        sb.hide();
                    });
                }
            }

            function sendFileToServer(formData,status) {
                var uploadURL = "' . $this->pantalla . '"; //Upload URL
                var jqXHR=$.ajax({
                    xhr: function() {
                        var xhrobj = $.ajaxSettings.xhr();
                        if (xhrobj.upload) {
                            xhrobj.upload.addEventListener("progress", function(event) {
                                var percent = 0;
                                var position = event.loaded || event.position;
                                var total = event.total;
                                if (event.lengthComputable) {
                                    percent = Math.ceil(position / total * 100);
                                }
                                status.setProgress(percent);
                            }, false);
                        }
                        return xhrobj;
                    },
                    url: uploadURL,
                    type: "POST",
                    dataType:"json",
                    contentType:false,
                    processData: false,
                    cache: false,
                    data: formData,
                    success: function(data){
                        status.setIdFile(data.idFile);
                    }
                }).done(function(data){
                    comunicaCallback(data.comunica);
                });
                status.setAbort(jqXHR);
            }
        ');
    }
    private function upload()
    {
        $peso = 0;
        $this->prm->iba_id = $this->prm->banco;
        $con = new AccesoDatos();
        $usr = $_SESSION[_S]['usr_id'];
        $ip = isset($_SESSION[_S]['ses_ip']) ? $_SESSION[_S]['ses_ip'] : $_SERVER['REMOTE_ADDR'];
        $extracto = new procesaExtractoBancario(0, false, $con, $usr, $ip);
        $icono = '';
        $extp = array("TXT", "ZIP");
        $origen = "txt simple";

        foreach ($_FILES as $file) {
            switch (strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION))) {
                case "TXT" :
                    $icono = 'TXT';
                    break;
                case "ZIP" :
                    $icono = 'ZIP';
                    break;
                case "2001007F1200001" :
                    $icono = '';
                    break;
                default :
                    $icono = 'verboten';
                    break;
            }

            $tmp = '<div class=\"statusbar2 error2\"><div class=\"infofile filename\">' . $file['name']
                . '</div><div class=\" infofile filesize\"></div><div class=\"infofile \"><img src=\"img/24pxIcon/'
                . $icono . '.png\"/></div><div class=\"infofile \"></div><div class=\"infofile  abort\">'
                . 'El archivo no esta permitido</div></div>';

            if (!in_array(strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)), $extp)) {
                if (isset($this->prm->origen) && $this->prm->origen == "input") {
                    $this->mixScript('    
                        $("#listaArchivos").append("' . $tmp . '");
                    ');
                } else {
                    $this->mixScript('
                        $("#' . $this->prm->id . '").remove();
                        $("#dragndrop").append("' . $tmp . '");
                    ');
                }
                return;
            }

            $nameMD5 = strtoupper(md5_file($file['tmp_name']));

            $params = [$nameMD5 ];
            $sql = 'SELECT * FROM ' . BD_TESORERIA . '.ban_ArchivoBancario WHERE arb_nombreSistema = ? ';
            $data = $this->model->getDatos($sql, false, $params);
            $res = $data['datos'][0];
            $reg = $data['registros'];

            $fch_id = 0;
            $noinsert = false;
            /*  VERIFICAMOS SI EL FICHERO ESTA DADO DE ALTA EN LA BASE DE DATOS
                Y SI EXISTE EL FICHERO EN EL SERVIDOR
            */

            if ($data['registros'] > 0) {
                $fch = $extracto->rutaExtracto($res['iba_id'], $res['msc_id'], APP_EJERCICIO) . $nameMD5 . "." . $res['tav_id'];
                $this->idFile = $res['arb_id'];
                if (file_exists($fch)) {
                    $tmp = '<div class=\"statusbar2 error3\"><div class=\"infofile filename\">' . $file['name']
                        . '</div><div style=\"width:40%; display:inline-block\">El archivo ya ha sido subido </div></div>';
                    if ($res['sts_id'] == 2) {
                        $tmp = '<div class=\"statusbar2 error3\"><div class=\"infofile filename\">' . $file['name']
                            . '</div><div style=\"width:40%; display:inline-block\">El archivo ya ha sido subido y procesado</div></div>';
                    }
                    if (isset($this->prm->origen) && $this->prm->origen == "input") {
                        $this->mixScript('   
                            $("#listaArchivos").append("' . $tmp . '");
                        ');
                    } else {
                        $this->mixScript('
                            $("#' . $this->prm->id . '").remove();
                            $("#dragndrop").append("' . $tmp . '");
                        ');
                    }
                    return;
                }
            }
            /* SI NO EXISTE EN LA BASE DE DATOS
                O EL FICHERO NO ESTA EN DISCO DURO ENTONCES LO GUARDAMOS
            */
            $datosFichero = $extracto->getDatosBasicosFichero($file['tmp_name'], $this->prm->iba_id);

            if ($datosFichero["error"] > 0) {
                $this->mixScript('notify("Error 3:' . $datosFichero["mensaje"] . '","error",true);');
                if ($datosFichero["error"] == 1 || $datosFichero["error"] == 2) {
                    return false;
                }
                $estado = 3;
            }

            $ruta = $extracto->rutaExtracto($this->prm->iba_id, $datosFichero['mes'], $datosFichero['anio']);

            if (!move_uploaded_file($file['tmp_name'], $ruta . $nameMD5 . '.TXT')) {
                $this->mixScript('notify("Error 1 al subir el fichero, intentelo nuevamente","error",true);');
                return false;
            }
            $estado = 2;

            $peso = $datosFichero["size"];
            $fecha = $datosFichero["fecha"];
            $idTransaccion = $datosFichero["idTransaccion"];
            $fecha = $datosFichero['fecha'];
            $dia = explode("-", $datosFichero['fecha']);
            $dia = isset($dia[2]) ? $dia[2] : date('d');

            if ($reg == 0) {
                $params = [
                    ':iba_id' => $this->prm->iba_id,
                    ':ejr_id' => $datosFichero['anio'],
                    ':msc_id' => $datosFichero['mes'],
                    ':arb_dia' => $dia,
                    ':arb_nombreOriginal' => $file['name'],
                    ':arb_nombreSistema' => $nameMD5,
                    ':arb_fecha' => $datosFichero['fecha'],
                    ':arb_size' => $peso,
                    ':arb_numeroLineas' => 0,
                    ':tav_id' => strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)),
                    ':sts_id' => 1,
                    ':usr_id' => $this->usr_id
                ];
                $sql =
                    'INSERT INTO ' . BD_TESORERIA . '.ban_ArchivoBancario(
                            iba_id,ejr_id,msc_id,arb_dia,
                            arb_nombreOriginal,arb_nombreSistema,
                            arb_fecha,arb_size,arb_numeroLineas,
                            tav_id,sts_id,
                            usr_id
                        ) VALUES (
                            :iba_id, :ejr_id, :msc_id, :arb_dia, :arb_nombreOriginal, :arb_nombreSistema, :arb_fecha,
                            :arb_size, :arb_numeroLineas, :tav_id, :sts_id, :usr_id
                        )';
                $this->conectar();
                $this->setSql($sql);
                $this->consultar($params);
                $this->idFile = $this->getUltimoId();
                $fch_id = $this->getUltimoId();
                $this->desconectar();
            } else {
                $fch_id = $res['arb_id'];
            }

            if (isset($this->prm->origen) && $this->prm->origen == "input") {
                $tmp = '<div class=\"statusbar2\"><div class=\"infofile filename\">' . $file['name']
                    . '</div><div class=\" infofile filesize\">' . $peso
                    . '</div><div class=\"infofile \"><img src=\"img/24pxIcon/' . $icono
                    . '.png\"/></div><div class=\"infofile bar\"><div class=\"progressBar\">100%</div></div>'
                    . '<div class=\"infofile abort  \">'
                    . '</div><div class=\"infofile  abort\">Cargado</div></div>';
                $this->mixScript('    $("#listaArchivos").append("' . $tmp . '");');
            }

            if ($datosFichero["error"] == 0) {
                $this->mixScript(' notify("Se ha subido el fichero' . $file['name'] . ' correctamente","success"); ');
            }
        } //FIN DEL FOREACH
    }
    private function listaFicheros()
    {
        $a = (isset($this->prm->anio)) ? $this->prm->anio : date('Y');
        $selecciona = date("m");
        $iba = $this->prm->iba_id;
        $params = [$iba, $a];
        $sql =
            "SELECT
                c.*,iba_abreviatura,ex.reg,
                e.arb_clcs,
                e.arb_documentos,
                e.arb_cheques,
                e.arb_lineas,
                e.arb_propuestasManuales
            FROM  " . BD_TESORERIA . ".ban_ArchivoBancario c
            LEFT JOIN " . BD_TESORERIA . ".ban_ArchivoBancarioDatos e USING(arb_id)
            INNER JOIN " . BD_TESORERIA . ".cat_Mes USING (msc_id)
            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria USING (iba_id)
            LEFT JOIN  (
                SELECT arb_id,COUNT(arb_id) AS reg
                FROM " . BD_TESORERIA . ".ban_ExtractoBancario
                GROUP BY arb_id
            ) ex USING (arb_id)
            WHERE iba_id = ?
                AND c.ejr_id = ?" . PHP_EOL;

        if (isset($this->prm->mes)) {
            $sql .= ' AND msc_id = ? ';
            $selecciona = $this->prm->mes;
            array_push($params, $selecciona);
        } else {
            $sql .= ' AND msc_id = ? ';
            array_push($params, date("m"));
        }

        $sql .= PHP_EOL . ' ORDER BY arb_fecha';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $con->desconectar();

        $cbo_mes = new stdClass();
        $cbo_mes->id = "msc_id";
        $cbo_mes->name = "mes";
        $cbo_mes->camposVal = "msc_id";
        $cbo_mes->mensaje = "Selecciona";
        $cbo_mes->sql = "SELECT * FROM " . BD_TESORERIA . ".cat_Mes";
        $cbo_mes->search = true;
        $cbo_mes->selecciona = $selecciona;

        $ejercicio = new stdClass();
        $ejercicio->id = "ejr_id";
        $ejercicio->enDiv = true;
        $ejercicio->camposVal = "ejr_id";
        $ejercicio->mensaje = "Selecciona Ejercicio";
        $ejercicio->selecciona = $a;
        $ejercicio->sql = "SELECT * FROM " . BD_TESORERIA . ".cat_Ejercicio ORDER BY ejr_id DESC LIMIT 2;";
        $ejercicio->search = true;

        $html = '
            <legend>Ficheros del Mes</legend>
            <div class="row">
                <fieldset class="col-md-2">
                    ' . $this->ponCombo($cbo_mes) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponCombo($ejercicio) . '
                </fieldset>
                <fieldset class="col-md-4">
                    <button id="btnsearch" class="btn btn-outline-primary">Listar</button>
                </fieldset>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered " id="tabla_conceptos">
                    <tr>
                        <th class="success">ID</th>
                        <th class="success">Nombre</th>
                        <th class="success">Identificador</th>
                        <th class="success">Tamaño</th>
                        <th class="success">Lineas</th>
                        <th class="success">Banco</th>
                        <th class="success">Fecha</th>
                        <th class="success">Tipo</th>
                        <th class="success" width="10%">Procesar</th>
                        <th class="success" width="10%">CLCs</th>
                        <th class="success" width="10%">Documentos</th>
                        <th class="success" width="10%">Cheques</th>
                        <th class="success" width="10%">Lineas de Captura</th>
                        <th class="success" width="10%">Propuestas Manuales</th>
                    </tr>';
        $tablebody = '';
        $cell = 0;
        while ($datos = $con->extraer()) {
            $color = "EAEAEA";
            if ($cell == 0) {
                $color = "FFFFFF";
                $cell = 1;
            } else {
                $color = "EAEAEA";
                $cell = 0;
            }

            $tablebody .= '
                    <tr id = "tr_' . $datos['arb_id'] . '">
                        <td bgcolor="' . $color . '">' . $datos['arb_id'] . '</td>
                        <td bgcolor="' . $color . '">' . $datos['arb_nombreOriginal'] . '</td>
                        <td bgcolor="' . $color . '">' . $datos['arb_nombreSistema'] . '</td>
                        <td bgcolor="' . $color . '" align="right">' . number_format((int)$datos['arb_size'], 2) . 'Kb</td>
                        <td bgcolor="' . $color . '" id="tdli' . $datos['arb_id'] . '"align="right">' . $datos['arb_numeroLineas'] . '</td>
                        <td bgcolor="' . $color . '">' . $datos['iba_abreviatura'] . '</td>
                        <td bgcolor="' . $color . '">' . $datos['arb_fecha'] . '</td>
                        <td bgcolor="' . $color . '"><img title="Tipo de fichero subido:' . $datos['tav_id'] . '" src="img/img.php?img=' . $datos['tav_id'] . '"/></td>
                        <td bgcolor="' . $color . '" id="td_' . $datos['arb_id'] . '">';
            switch ($datos['sts_id']) {
                case 2:
                    $tablebody .= '<span>Fichero ya procesado</span>';
                    break;
                case 1:
                    $tablebody .= '<button class="btn btn-outline-default procesa" fch="' . $datos['arb_id'] . '">Procesar</button>';
                    break;
            }
            $tablebody .= '   </td>';

            // bloque de los resultado
            $tablebody .= '
                    <td id="tdclc' . $datos['arb_id'] . '">' . $datos['arb_clcs'] . '</td>
                    <td id="tddoc' . $datos['arb_id'] . '">' . $datos['arb_documentos'] . '</td>
                    <td id="tdch' . $datos['arb_id'] . '">' . $datos['arb_cheques'] . '</td>
                    <td id="tdlc' . $datos['arb_id'] . '">' . $datos['arb_lineas'] . '</td>
                    <td id="tdpm' . $datos['arb_id'] . '">' . $datos['arb_propuestasManuales'] . '</td>
                ';

            $tablebody .= '</tr>';
        }

        if ($tablebody == '') {
            $tablebody = '<tr><td align="center" colspan="100%"> Sin Registros en el mes </td></tr>';
        }

        $html .= $tablebody . '</div></table>';
        $this->mixHtml($html);
        $this->mixScript('
            $("#msc_id").change(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"listaFicheros",
                    mes:$(this).val(),
                    anio:$("#ejr_id").val(),
                    iba_id:"' . $this->prm->iba_id . '"
                });
            });

            $("#ejr_id").change(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"listaFicheros",
                    anio:$(this).val(),
                    mes:$("#msc_id").val(),
                    iba_id:"' . $this->prm->iba_id . '"
                });
            });

            $("#btnsearch").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"listaFicheros",
                    mes:$("#msc_id").val(),
                    anio:$("#ejr_id").val(),
                    iba_id:"' . $this->prm->iba_id . '"
                });
            });
            var dialog;
            function mostrar(mensaje){
                dialog = alertify.alert(
                    \'<span class="alert alert-info">\' + mensaje + \'</span>\',
                    \'<p><i class="fa fa-spin fa-spinner"></i> Procesando ...</p>\'
                ).set("label", "Aceptar");
            }

            $("#btnregresa").click(function(){
                comunica("' . $this->pantalla . '");
            });

            $(".procesa").click(function(){                
                var fch = $(this).attr("fch");
                alertify.confirm(
                    "PROCESAR FICHERO",
                    "CONFIRME EL PROCESADO DEL FICHERO",
                    function(){
                        $(".procesa").hide();
                        $("#btnregresa").hide();
                        $("#td_" + fch).append($("<img/>")
                            .addClass("removeImg")
                            .attr("src","img/loader2.gif")
                            .css("display","inline-block"));
                        mostrar("PROCESAR FICHERO");
                        comunica("' . $this->pantalla . '", {metodo:"procesarFichero", "fch":fch});
                    },
                    function(){},
                ).set("defaultFocus", "cancel")
                .set("labels", {ok:"Proceder", cancel:"Cancelar"});
            });
            $(".dispersa").click(function(){
                var fch = $(this).attr("fch");
                alertify.confirm(
                    "DISPERSAR DATOS",
                    "CONFIRME LA DISPERSIÓN DEL FICHERO SELECCIONADO",
                    function(){
                        $(".procesa").hide();
                        $("#btnregresa").hide();
                        $("#tdd_" + fch).append($("<img/>")
                            .addClass("removeImg")
                            .attr("src","img/loader2.gif")
                            .css("display","inline-block"));
                        comunica("' . $this->pantalla . '", {metodo:"dispersarFichero", "fch":fch});
                    },
                    function(){},
                ).set("defaultFocus", "cancel")
                .set("labels", {ok:"Proceder", cancel:"Cancelar"});
            });
        ');
    }
    private function procesarFichero()
    {
        if (!isset($this->prm->fch)) {
            $this->mixScript('
                $("#btnregresa").show();
                $(".procesa").show();
                notify("error","warning"); ');
            return false;
        }
        set_time_limit(0);

        $con = new AccesoDatos();
        $usr = $_SESSION[_S]['usr_id'];
        $ip = isset($_SESSION[_S]['ses_ip']) ? $_SESSION[_S]['ses_ip'] : $_SERVER['REMOTE_ADDR'];
        
        $extracto = new procesaExtractoBancario($this->prm->fch, false, $con, $usr, $ip);
        $detalle = $extracto->getDetalleFichero();

        if (!is_array($detalle)) {
            $this->mixScript(' notify("' . $detalle . '","error");');
            return false;
        }
        
        $params = [$this->prm->fch];
        $sql = 'SELECT COUNT(arb_id) AS Registros FROM ' .
            BD_TESORERITEM. '.tem_ExtractoBancario WHERE arb_id = ? ';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $datos2 = $this->extraer();

        if ($datos2['Registros'] > 0) {
            $this->mixScript('
                notify("El fichero ya fue procesado y hay ' . $datos2['registros'] . ' registros","error");
                $("#btnregresa").show();
                $(".procesa").show();
                $(".removeImg").remove();
            ');
            return false;
        }
        
        $extracto->procesaFichero($_SESSION[_S]['usr_id'], $_SERVER['REMOTE_ADDR'], $detalle['ejr_id']);
        $res = $extracto->getResultado();
        if (is_bool($res) && $res === false) {
            $this->mixScript(' notify("[' . $extracto->getErrorFichero() . ']Ocurrio un error al procesar el'
                . ' fichero, levante un ticket con el error y el siguiente dato:E1-FCH' . $this->prm->fch . '"'
                . ',"error",false);
                    $(".procesa").show();
                    $(".removeImg").remove();
                    ');
        } else {
            $n = $extracto->dispersaDatos(); // DEBUG
            $con = new AccesoDatos();
            //$extracto->reflejaMongo($con);
            $res = $n;

            $detalles = '<hr/>';
            if (is_array($n)) {
                $detalles .= $this->pintaTablaResultado($n);
            }

            if ($n) {
                $n = '$("#tdd_' . $this->prm->fch . '").html("").append($("<div/>").html("Archivo Dispersado"));';
            } else {
                $n = '
                    $("#tdd_' . $this->prm->fch . '").html("").append(
                        $("<button/>").addClass("btn btn-outline-default dispersa")
                            .attr("fch","' . $this->prm->fch . '")
                            .html("Dispersa")
                    );
                ';
            }

            $lineas = $extracto->getLineas();
            $this->mixScript($n . '
                $("#btnregresa").show();
                $(".procesa").show();
                $("#td_' . $this->prm->fch . '").html("").addClass("success").append($("<div/>").html("Archivo Procesado"));
                $("#tdli' . $this->prm->fch . '").html("' . $lineas . '");
                $("#tdclc' . $this->prm->fch . '").html("' . $res['clc'] . '");
                $("#tddoc' . $this->prm->fch . '").html("' . $res['documento'] . '");
                $("#tdch' . $this->prm->fch . '").html("' . $res['cheque'] . '");
                $("#tdlc' . $this->prm->fch . '").html("' . $res['lc'] . '");
                $("#tdpm' . $this->prm->fch . '").html("' . $res['manual'] . '");
                var snd = new Audio("' . $this->beep() . '");
                snd.play();
                dialog.setContent("Fichero procesado! Se procesaron ' . number_format($extracto->getLineas(), 0) . ' líneas ' . $detalles . '");
            ');
        }
    }
    private function pintaTablaResultado($n)
    {
        $detalles = '<table class=\'table table-bordered\'>'
            . '<tr><td class=\'success\'>Tipo</td><td class=\'success\'>Cantidad</td></tr>'
            . '<tr>'
            . '<td>CLCs</td>'
            . '<td>' . number_format($n['clc'], 0) . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td>Documentos</td>'
            . '<td>' . number_format($n['documento'], 0) . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td>Cheques</td>'
            . '<td>' . number_format($n['cheque'], 0) . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td>Líneas de Captura</td>'
            . '<td>' . number_format($n['lc'], 0) . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td>Propuestas Manuales</td>'
            . '<td>' . number_format($n['manual'], 0) . '</td>'
            . '</tr>'
            . '</table>';
        return $detalles;
    }
    private function beep()
    {
        return 'data:audio/wav;base64,//uQRAAAAWMSLwUIYAAsYkXgoQwAEaYLWfkWgAI0wWs/ItAAAGDgYtAg'
            . 'AyN+QWaAAihwMWm4G8QQRDiMcCBcH3Cc+CDv/7xA4Tvh9Rz/y8QADBwMWgQAZG/ILNAARQ4GLTcDeIIIhxGOBAuD7hOfBB3/94'
            . 'gcJ3w+o5/5eIAIAAAVwWgQAVQ2ORaIQwEMAJiDg95G4nQL7mQVWI6GwRcfsZAcsKkJvxgxEjzFUgfHoSQ9Qq7KNwqHwuB13MA4'
            . 'a1q/DmBrHgPcmjiGoh//EwC5nGPEmS4RcfkVKOhJf+WOgoxJclFz3kgn//dBA+ya1GhurNn8zb//9NNutNuhz31f////9vt///'
            . 'z+IdAEAAAK4LQIAKobHItEIYCGAExBwe8jcToF9zIKrEdDYIuP2MgOWFSE34wYiR5iqQPj0JIeoVdlG4VD4XA67mAcNa1fhzA1j'
            . 'wHuTRxDUQ//iYBczjHiTJcIuPyKlHQkv/LHQUYkuSi57yQT//uggfZNajQ3Vmz+Zt//+mm3Wm3Q576v////+32///5/EOgAAADV'
            . 'ghQAAAAA//uQZAUAB1WI0PZugAAAAAoQwAAAEk3nRd2qAAAAACiDgAAAAAAABCqEEQRLCgwpBGMlJkIz8jKhGvj4k6jzRnqasNK'
            . 'Ieoh5gI7BJaC1A1AoNBjJgbyApVS4IDlZgDU5WUAxEKDNmmALHzZp0Fkz1FMTmGFl1FMEyodIavcCAUHDWrKAIA4aa2oCgILEBu'
            . 'pZgHvAhEBcZ6joQBxS76AgccrFlczBvKLC0QI2cBoCFvfTDAo7eoOQInqDPBtvrDEZBNYN5xwNwxQRfw8ZQ5wQVLvO8OYU+mHvF'
            . 'LlDh05Mdg7BT6YrRPpCBznMB2r//xKJjyyOh+cImr2/4doscwD6neZjuZR4AgAABYAAAABy1xcdQtxYBYYZdifkUDgzzXaXn98Z'
            . '0oi9ILU5mBjFANmRwlVJ3/6jYDAmxaiDG3/6xjQQCCKkRb/6kg/wW+kSJ5//rLobkLSiKmqP/0ikJuDaSaSf/6JiLYLEYnW/+kX'
            . 'g1WRVJL/9EmQ1YZIsv/6Qzwy5qk7/+tEU0nkls3/zIUMPKNX/6yZLf+kFgAfgGyLFAUwY//uQZAUABcd5UiNPVXAAAApAAAAAE0'
            . 'VZQKw9ISAAACgAAAAAVQIygIElVrFkBS+Jhi+EAuu+lKAkYUEIsmEAEoMeDmCETMvfSHTGkF5RWH7kz/ESHWPAq/kcCRhqBt'
            . 'MdokPdM7vil7RG98A2sc7zO6ZvTdM7pmOUAZTnJW+NXxqmd41dqJ6mLTXxrPpnV8avaIf5SvL7pndPvPpndJR9Kuu8fePvui'
            . 'uhorgWjp7Mf/PRjxcFCPDkW31srioCExivv9lcwKEaHsf/7ow2Fl1T/9RkXgEhYElAoCLFtMArxwivDJJ+bR1HTKJdlEoTEL'
            . 'CIqgEwVGSQ+hIm0NbK8WXcTEI0UPoa2NbG4y2K00JEWbZavJXkYaqo9CRHS55FcZTjKEk3NKoCYUnSQ0rWxrZbFKbKIhOKPZ'
            . 'e1cJKzZSaQrIyULHDZmV5K4xySsDRKWOruanGtjLJXFEmwaIbDLX0hIPBUQPVFVkQkDoUNfSoDgQGKPekoxeGzA4DUvnn4bx'
            . 'zcZrtJyipKfPNy5w+9lnXwgqsiyHNeSVpemw4bWb9psYeq//uQZBoABQt4yMVxYAIAAAkQoAAAHvYpL5m6AAgAACXDAAAAD5'
            . '9jblTirQe9upFsmZbpMudy7Lz1X1DYsxOOSWpfPqNX2WqktK0DMvuGwlbNj44TleLPQ+Gsfb+GOWOKJoIrWb3cIMeeON6lz2'
            . 'umTqMXV8Mj30yWPpjoSa9ujK8SyeJP5y5mOW1D6hvLepeveEAEDo0mgCRClOEgANv3B9a6fikgUSu/DmAMATrGx7nng5p5ii'
            . 'mPNZsfQLYB2sDLIkzRKZOHGAaUyDcpFBSLG9MCQALgAIgQs2YunOszLSAyQYPVC2YdGGeHD2dTdJk1pAHGAWDjnkcLKFymS3'
            . 'RQZTInzySoBwMG0QueC3gMsCEYxUqlrcxK6k1LQQcsmyYeQPdC2YfuGPASCBkcVMQQqpVJshui1tkXQJQV0OXGAZMXSOEEBR'
            . 'irXbVRQW7ugq7IM7rPWSZyDlM3IuNEkxzCOJ0ny2ThNkyRai1b6ev//3dzNGzNb//4uAvHT5sURcZCFcuKLhOFs8mLAAEAt4'
            . 'UWAAIABAAAAAB4qbHo0tIjVkUU//uQZAwABfSFz3ZqQAAAAAngwAAAE1HjMp2qAAAAACZDgAAAD5UkTE1UgZEUExqYynN1qZ'
            . 'vqIOREEFmBcJQkwdxiFtw0qEOkGYfRDifBui9MQg4QAHAqWtAWHoCxu1Yf4VfWLPIM2mHDFsbQEVGwyqQoQcwnfHeIkNt9Yn'
            . 'kiaS1oizycqJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//I'
            . 'InrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+v'
            . 'pF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLq'
            . 'SX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho'
            . '1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEE'
            . 'CUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZ'
            . 'SmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40'
            . 'RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6'
            . 'qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCk'
            . 'QjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNI'
            . 'HpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBV'
            . 'moWFSJkWFxX4FFRQWR+LsS4W/rFRb///////////////////////////////////////////////////////////////////'
            . '////////////////////////////////////////////////////////////////////////////////////////////////'
            . '////////////////////////////////////////////////////////////////////////////////////////////////'
            . '////////////////////////////////////////////////////////////////////////////////////////////////'
            . '//////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNG'
            . 'h0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=';
    }
    private function procesaZip()
    {
    }
    private function dispersarFichero()
    {
        $arb_id = isset($this->prm->fch) ? $this->prm->fch : 0;
        if (!is_numeric($arb_id) || $arb_id < 1) {
            $this->mixScript(' notify("Fichero Invalido","error");');
            return false;
        }
        $con = new AccesoDatos();
        $extracto = new procesaExtractoBancario(null, false, $con);
        $extracto->setFichero($arb_id);
        $extracto->dispersaDatos();
        if ($extracto->getResultado()) {
            $this->mixScript(' notify("Fichero dispersado con exito","success");
                $("#tdd_' . $this->prm->fch . '").html("").addClass("success").append($("<div/>").html("Archivo Dispersado"));
            ');
        } else {
            $this->mixScript(' notify("Ocurrio un error ' . $extracto->getErrorFichero() . '","success");');
        }
        unset($extracto);
    }
}

new ControladorSubeExtracto();
