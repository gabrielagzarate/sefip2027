<?php

session_start();
ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/Entidad.php';
require_once dirname(__FILE__) . '/../../ent/scp/Garantias.php';
require_once dirname(__FILE__) . '/../../lib/FicheroSefip.php';

class ControladorGarantias extends WebResponse
{
    protected $url;
    protected $server;
    protected $banderaPagina = '';

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "scp/afectaciones/ControladorGarantias.php";
        $this->url = $this->pantalla;
        //Variable para obtener el servidor actual
        $this->server = $_SERVER['SERVER_ADDR'];
        switch ($this->metodo) {
            case 'agrega':
                $this->agrega();
                break;
            case 'nvaGarantia':
                $this->nvaGarantia();
                break;
            case 'upload':
                $this->upload();
                break;
            case 'download':
                $this->download();
                break;
            case 'subtipoGarantia':
                $this->subtipoGarantia();
                break;
            case 'tipoAnexo':
                $this->tipoAnexo();
                break;
            case 'abcGarantia':
                $this->abcGarantia();
                break;
            case 'garantias':
                $this->garantias();
                break;
            case 'llenaTablaContrato':
                $this->llenaTablaContrato();
                break;
            case 'llenaTablaDocumentos':
                $this->llenaTablaDocumentos();
                break;
            case 'ContratoGarantia':
                $this->ContratoGarantia();
                break;
            case 'abcContratoGarantia':
                $this->abcContratoGarantia();
                break;
            case 'pdfs':
                $this->pdfs();
                break;
            case 'llenaPdfs':
                $this->llenaPdfs();
                break;
            case 'delfile':
                $this->delfile();
                break;
            case 'main':
                //si no esta definido el metodo ejecuta main
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public  function garantias()
    { //funcion principal / main function
         if (isset($_SESSION[_S]['banderaPagina'])) {
            $this->prm->bandera = $_SESSION[_S]['banderaPagina'];
        } else if (isset($this->prm->bandera)) {
            $this->prm->bandera = $this->prm->bandera;
        } else {
            $this->prm->bandera = 0;
        }

        $btnagrega = new stdClass();
        $btnagrega->id = "agrega";
        $btnagrega->value = "Agregar Nueva Garantia";
        $btnagrega->icon = "solid fa-plus me-2";
        $btnagrega->color = "gris";
        $btnagrega->class = "me-1";

        $btndocto = new stdClass();
        $btndocto->id = "agregadocto";
        $btndocto->value = "Agregar Archivos";
        $btndocto->icon = "solid fa-plus me-2";
        $btndocto->color = "gris";
        $btndocto->class = "me-1";

        $btnregresa = new stdClass(); //130,98,0
        $btnregresa->id = "comeback";
        $btnregresa->value = "Regresar";
        $btnregresa->icon = "solid fa-circle-left me-2";
        $btnregresa->color = "gris";
        $btnregresa->class = "me-1";

        $botones = $this->ponBoton($btnregresa) . $this->ponBoton($btnagrega) . $this->ponBoton($btndocto);

        $html = '';
        $tipo = '<select id="tga" class="form-control">';
        $tipo .= '<option value="">Selecciona una opción</option>';

        $ins = '<select id="iaf" class="form-control">';
        $ins .= '<option value="">Selecciona una opción</option>';
        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql('
            SELECT tga_id, stg_id, tga_nombre, stg_nombre  
            FROM ' . BD_SINPRES . '.cat_TipoGarantia 
            INNER JOIN ' . BD_SINPRES . '.cat_SubtipoGarantia USING(tga_id)
        ');
        $c->consultar();

        while ($d = $c->extraer()) {
            $tipo .= '<option value="' . $d['tga_id'] . '-' . $d['stg_id'] . '">' .
                $d['tga_nombre'] . '-' . $d['stg_nombre'] . '</option>';
        }

        $tipo .= '</select>';
        $c->setSql('SELECT iaf_id, iaf_nombre FROM ' . BD_SINPRES . '.cat_InstitucionAfianzadora');
        $c->consultar();

        while ($d = $c->extraer()) {
            $ins .= '<option value="' . $d['iaf_id'] . '">' . $d['iaf_nombre'] . '</option>';
        }
        $c->desconectar();
        unset($c);
        $ins .= '</select>';

        $anio = '<select id="anio" class="form-control">';
        for ($i = 2015; $i <= APP_EJERCICIO; $i++) {
            $anio .= "<option value='$i' " . (isset($this->prm->anio) && $this->prm->anio == $i ? 'selected = "selected"' : '') . ">$i</option>";
        }
        $anio .= '</select>';

        $tools = '<div class="row col-md-12 col-xs-12 mb-2">';
        $tools .= '
            <div class="form-group col-md-1 col-xs-1">
                <label class="control-label">ID </label>
                <input type="text" id="bgaid" class="form-control solonumero"/>
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-2 col-xs-2">
                <label class="control-label">Número Documento </label>
                <input type="text" id="numdocto" class="form-control" maxlength="50"/>
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-2 col-xs-2">
                <label class="control-label"> Número Contrato </label>
                <input type="text" id="numcontr" class="form-control" maxlength="50"/>
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-2 col-xs-2">
                <label class="control-label">Tipo</label>
                ' . $tipo . '
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-2 col-xs-2">
                <label class="control-label">Afianzadora</label>
                ' . $ins . '
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-2 col-xs-2">
                <label class="control-label">Año</label>
                ' . $anio . '
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-1 col-xs-1">
                <button id="buscar" class="btn btn-outline-secondary mt-3">Buscar</button>
            </div>
        ';
        $tools .= '</div>';

        $tabla = new stdClass();
        $tabla->id = "tbl_doctos";
        $tabla->titulo = "Garantias cargadas por la UR";

        $tabla->comunica = new stdClass();
        $tabla->comunica->metodo = "llenaTablaDocumentos";
        $tabla->comunica->idtabla = "tbl_doctos";
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->tools = $tools;
        $tabla->numFilas = 50;
        $tabla->filasAmostrar = array(20, 50, 100, 200, 300, 500);

        /*PARAMETROS DE BUSQUEDA */
        $tabla->comunica->bgaid = '{$("#bgaid").val()}';
        $tabla->comunica->numdocto = '{$("#numdocto").val()}';
        $tabla->comunica->numcontr = '{$("#numcontr").val()}';
        $tabla->comunica->fecha = '{$("#fecha").val()}';
        $tabla->comunica->tga = '{$("#tga").find("option:selected").val()}';
        $tabla->comunica->iaf = '{$("#iaf").find("option:selected").val()}';
        $tabla->comunica->anio = '{$("#anio").find("option:selected").val()}';

        $tabla->multiseleccionable = false;
        $tabla->prmSeleccion = array("bga_id");
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = "Contrato";
        $tabla->casoSeleccionable->valor = "NO";
        $tabla->casoSeleccionable->operador = "==";
        $tabla->casoSeleccionable->colorEnFalso = "rgba(8, 255, 18, 0.20)";

        $tr = array();
        $th = new stdClass();
        $th->texto = "Editar";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Id";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Tipo de Garantia";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Año";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Institución Afianzadora";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "N° Documento";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Fecha Emisión";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Importe";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Adjuntos";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "En Contrato";
        array_push($tr, $th);

        $tabla->encabezado = array($tr);

        $trbody = array();
        $td = new stdClass();
        $td->tipo = "boton";
        $td->icono = "pencil";
        $td->class = "btn-xs editargar";
        $td->id = "editargar";
        $td->attr = new stdClass();
        $td->attr->bga_id = "bga_id";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "bga_id";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "tipogar";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "bga_ejercicio";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Institucion";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "numDocto";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "fechaemi";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Importe";
        $td->formato = "moneda";
        $td->align = "right";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "adjuntos";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Contrato";
        array_push($trbody, $td);

        $tabla->cuerpo = $trbody;

        $html .= $this->wrPonTabla($tabla);
        $html .= '<div id="observaciones" style="padding:15px; background-color:#EAEAEA; border-radius:4px; border:1px solid #CCC;">';
        $html .= '
            <div style="display:block; font-weight:bold; border-radius:4px 4px 0px 0px;margin-left:-15px; margin-top:-15px; margin-right:-15px; padding:4px;
            border-bottom:1px dotted #777; background-color:#e8f0d8;">Observaciones a las Garantías</div>
        ';
        $html .= 'Las garantías Adjuntadas a un contrato no se pueden editar<br> ';
        $html .= '</div>';
        $html .= '
            <div id="allscreen" style="display:none; background-color:rgba(0,0,0,.4); width:100%; height:100%; position:fixed; top:0px; left:0px;z-index:10000; padding:200px;">
                <center>
                <div id="download_dialog" style="width:600px; min-height:15px; background-color:#FFF; border-radius:3px; box-shadow:1px 1px 1px #666; padding:20px; border:1px solid #555; ">
                    <center>
                        Descargando archivo
                    </center>
                    <br/>
                    <div id="download_progress" style=" transition:all ease-in-out .5s; width:0%; min-height:20px; border:1px solid #EAEAEA; border-radius:3px; background-color:#090;"></div>
                </div>
                </center>
            </div>
        ';
        $this->mixHtml($html);
        $this->agregaTools($botones);
        $this->mixScript('
            $("#bgaid").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                }else if (keynum==95) {
                    return false;
                }else if ((keynum>=65 && keynum<=90)||(keynum>=97 && keynum<=122)) {
                    return false;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                }else{
                    return false;
                }
            });

            $("#numdocto").keypress(function(e) {
                console.log("Estoy entrando a la funcion");
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                }else if (keynum==95) {
                    return false;
                }else if (keynum==45) {
                    return true;
                }else if (keynum==47) {
                    return true;
                }else if (keynum==241 || keynum==209) {
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                }else{
                    return false;
                }
            });

            $("#numcontr").keypress(function(e) {
                console.log("Estoy entrando a la funcion");
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                }else if (keynum==95) {
                    return false;
                }else if (keynum==45) {
                    return true;
                }else if (keynum==47) {
                    return true;
                }else if (keynum==241 || keynum==209) {
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                }else{
                    return false;
                }
            });

            $("#buscar").click(function() {' . $this->wrLlamaLlenaTabla('tbl_doctos') . '});
            $("#agrega").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"nvaGarantia",
                    metodoOrigen:"garantias",
                    anio:$("#anio").find("option:selected").val()
                    '.(isset($this->prm->bandera)?', bandera:"'.$this->prm->bandera.'"':'').'
                });
            });
            $("#agregadocto").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"agrega",
                    origen:"agrega",
                    metodoOrigen:"garantias",
                    anio:$("#anio").find("option:selected").val()
                    '.(isset($this->prm->bandera)?', bandera:"'.$this->prm->bandera.'"':'').'

                });
            });
            $("#comeback").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    bandera : ' . $this->prm->bandera .'
                });
            });
            ' . $this->wrLlamaLlenaTabla('tbl_doctos') . '
        ');
    } //fin main

    /***********************************************************/
    public  function main()
    {
        $botones = "";

        if (isset($this->prm->banderaPagina) && $this->prm->banderaPagina != "") {
            $_SESSION[_S]['banderaPagina'] = $this->prm->banderaPagina;
        } elseif (isset($_SESSION[_S]['banderaPagina'])) {
            unset($_SESSION[_S]['banderaPagina']);
        } else {
            $this->banderaPagina = "x";
        }

        $tabla = new stdClass();
        $tabla->id = "tbl-contratos";
        $tabla->titulo = "Contratos de la Unidad Responsable";

        $tools = '<div class="row col-md-12 col-xs-12 mb-2">';
        $tools .= '
            <div class="form-group col-md-1 col-xs-1">
                <label class="control-label">ID</label>
                <input id="ipuid" class="form-control solonumero" type="text">
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-6 col-xs-6">
                <label class="control-label">Número de Contrato</label>
                <input id="numcontr" class="form-control solonumero" type="text" maxlength="50">
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-4 col-xs-4">
                <label class="control-label">RFC del Proveedor</label>
                <input id="rfcid" class="form-control solonumero" type="text" maxlength="14">
            </div>
        ';
        $tools .= '
            <div class="form-group col-md-1 col-xs-1">
                <button id="buscar" class="btn btn-outline-secondary mt-3" type="button">Buscar</button>
            </div>
        ';
        $tools .= '</div>';

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaContrato';
        $tabla->comunica->idtabla = 'tbl-contratos';
        $tabla->comunica->ipuid = '{$("#ipuid").val()}';
        $tabla->comunica->numcontr = '{$("#numcontr").val()}';
        $tabla->comunica->rfcid = '{$("#rfcid").val()}';

        $tabla->numFilas = 20;
        $tabla->filasAmostrar = array(20, 30, 50, 100, 200, 500);
        $tabla->tools = $tools;

        $tr = array();
        $th = new stdClass();
        $th->texto = "Editar";
        $th->width = "20px;";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "#";
        $th->width = "30px;";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Numero Contrato";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Objeto ";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Fecha";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Proveedor ";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Importe Total";
        $th->width = "8%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Importe Anticipo";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Anti";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Cumpl";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "V.O.";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = "Estado";
        $th->width = "30px;";
        array_push($tr, $th);

        $trbody = array();
        $td = new stdClass();
        $td->tipo = "boton";
        $td->icono = "pencil";
        $td->class = "btn-xs btn_gara";
        $td->attr = new stdClass();
        $td->attr->ipu_id = "ipu_id";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "ipu_id";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "numCont";
        $td->ajustable = true;
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Objeto";
        $td->ajustable = true;
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "FechaC";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Proveedor";
        $td->ajustable = true;
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Importe";
        $td->formato = "moneda";
        $td->align = "right";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "ImporteA";
        $td->formato = "moneda";
        $td->align = "right";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Ant";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Cum";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = "Vio";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'eip_nombre';
        $td->tipo = "semaforo";
        $td->color = "eip_color";
        $td->lbl = "eip_nombre";
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = $this->wrPonTabla($tabla);
        $html .= '<div class="alert alert-warning">
            Las garantias de un Contrato en CLC no se pueden quitar, pero si puede agregar más al contrato.</div>
        ';
        $this->mixHtml($html);
        $this->llenaTablaContrato();

        $btnregresa = new stdClass(); //130,98,0
        $btnregresa->id = "comeback";
        $btnregresa->value = "Regresar";
        $btnregresa->icon = "solid fa-circle-left me-2";
        $btnregresa->color = "gris";
        $btnregresa->class = "me-1";

        $btndocto = new stdClass();
        $btndocto->id = "agregadocto";
        $btndocto->value = "Agregar Archivos";
        $btndocto->icon = "solid fa-plus me-2";
        $btndocto->color = "gris";
        $btndocto->class = "me-1";

        $btngarantia = new stdClass();
        $btngarantia->id = "Garantias";
        $btngarantia->value = "Listado de Garantias";
        $btngarantia->icon = "solid fa-list me-2";
        $btngarantia->color = "gris";
        $btngarantia->class = "me-1";

        $btnpdf = new stdClass();
        $btnpdf->id = "pdfs";
        $btnpdf->value = "Listado de PDFs";
        $btnpdf->icon = "solid fa-list me-2";
        $btnpdf->color = "gris";
        $btnpdf->class = "me-1";


        if (isset($_SESSION[_S]['banderaPagina']) || (isset($this->prm->bandera) && $this->prm->bandera != 0)) {
            $botones = $this->ponBoton($btnregresa);
        }

        if (isset($this->prm->bandera)) {
            # code...
            $this->prm->bandera = $this->prm->bandera;
        } else {
            $this->prm->bandera = 0;
        }



        $botones .= $this->ponBoton($btngarantia);
        $botones .= $this->ponBoton($btnpdf);

        $this->agregaTools($botones);
        $this->mixScript('
            $("#ipuid").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                }else if (keynum==95) {
                    return false;
                }else if ((keynum>=65 && keynum<=90)||(keynum>=97 && keynum<=122)) {
                    return false;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                }else{
                    return false;
                }
            });

            $("#numcontr").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                } else if (keynum==95) {
                    return false;
                } else if (keynum==45) {
                    return true;
                } else if (keynum==47) {
                    return true;
                } else if (keynum==241 || keynum==209) {
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            $("#rfc").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                } else if (keynum==95) {
                    return false;
                }else if (keynum==241 || keynum==209) {
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            $(".solonumero").keypress(function(event) {
                var anularTecla = false;
                var esPermitido = Array.indexOf([0,8], event.which) >= 0;
                anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                if (anularTecla) event.preventDefault();
            });

            $("#comeback").click(function() {
               comunica("scp/afectaciones/ControladorRegistroObra.php",{metodo:"Contratos", tarea:"'. ($_SESSION[_S]['banderaPagina'] ?? $this->prm->bandera ?? 0).'"});
            });

            $("#pdfs").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"pdfs",
                    garantia:"true",
                    origen:"main",
                    bandera:' . $this->prm->bandera . '
                });
            });

            $("#Garantias").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"garantias"
                    '.(isset($this->prm->bandera)?', bandera:"'.$this->prm->bandera.'"':'').'
                });
            });

            $("#agregadocto").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"agrega",
                    origen:"main",
                    garantia:"true",
                    bandera:' . $this->prm->bandera . '
                });
            });

            $("#buscar").click(function() {
                ' . $this->wrLlamaLlenaTabla('tbl-contratos') . '
            });
        ');
    }

    private function unicoUr($gpo_id, $ur_id)
    {
        $parametrosConsulta = [$gpo_id, $ur_id];
        $c = new AccesoDatos();
        $c->setSql('SELECT uur_id FROM cat_UnidadResponsable WHERE gpo_id = ? AND ur_id = ? ');
        $c->conectar();
        $c->consultar($parametrosConsulta);
        $d = $c->extraer();
        $uur_id = $d['uur_id'];
        $c->desconectar();
        unset($c);
        return $uur_id;
    }

    private function llenaTablaContrato()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $uur_id = $this->unicoUr($gpo_id, $ur_id);
        $parametrosConsulta = [$uur_id, $uur_id];
        $sql = '
            SELECT
                ipu_id,ipu_numeroContrato AS numCont, SUBSTRING(ipu_objetoContrato,1,100) AS Objeto,
                ipu_fechaContrato AS FechaC, CONCAT(rfc_nombre," ",rfc_paterno,"-",rfc_id) AS Proveedor, 
                ipu_importeTotal AS Importe, ipu_importeAnticipo AS ImporteA, if (Ant>0,"SI","NO") AS Ant,
                if (Cum>0,"SI","NO") AS Cum,if (Vio>0,"SI","NO") AS Vio, eip_id,eip_color,eip_nombre
            FROM ' . BD_SINPRES . '.cat_InversionPublica C
            INNER JOIN ' . BD_SINPRES . '.cat_EstadoInversion USING (eip_id)
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
            LEFT JOIN (
                SELECT 
                    ipu_id,
                    SUM(CASE WHEN tga_id="A" THEN 1 ELSE 0 END) AS Ant,
                    SUM(CASE WHEN tga_id="C" THEN 1 ELSE 0 END) AS Cum,
                    SUM(CASE WHEN tga_id="V" THEN 1 ELSE 0 END) AS Vio
                FROM ' . BD_SINPRES . '.enc_ContratoGarantia
                INNER JOIN ' . BD_SINPRES . '.cat_BancoGarantia USING(bga_id)
                INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
                WHERE uur_id = ?
                GROUP BY ipu_id
            )Anticipos USING(ipu_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_Proveedores USING(rfc_id)
            WHERE uur_id = ? ';

        if (isset($this->prm->ipuid) && $this->prm->ipuid != "") {
            array_push($parametrosConsulta, $this->prm->ipuid);
            $sql .= ' AND C.ipu_id = ? ';
        }

        if (isset($this->prm->numcontr) && $this->prm->numcontr != "") {
            array_push($parametrosConsulta, $this->prm->numcontr);
            $sql .= ' AND C.ipu_numeroContrato = ? ';
        }

        if (isset($this->prm->rfcid) && $this->prm->rfcid != "") {
            array_push($parametrosConsulta, $this->prm->rfcid);
            $sql .= ' AND C.rfc_id = ? ';
        }

        $this->setSql($sql);
        $this->setParams($parametrosConsulta);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 20;
        $nav->idTabla = "tbl-contratos";
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros para la UR","warning")');
        } else {
            $this->mixScript('
                $(".btn_gara").click(function()
                {
                    comunica("' . $this->pantalla . '",
                    {
                        metodo:"ContratoGarantia",
                        ipu_id:$(this).attr("ipu_id")
                    });
                });
            ');
        }
    }

    public function existeEsquema($tabla = "")
    {
        if (!empty($tabla)) {
            $this->conectar();
            $this->setSql('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
            $this->consultar([$tabla]);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                return true;
            }
        }
        return false;
    }

    public function ContratoGarantia()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? (int)$_SESSION[_S]['gpo_id'] : (int)$_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? (int)$_SESSION[_S]['ur_id'] : (int)$_SESSION[_S]['clasAdm-ur'];
        $abc = "a";
        $sql = '';
        $parametrosConsulta = [];

        $ejercicioHasta = 2018;
        for ($i = 2015; $i <= $ejercicioHasta; $i++) {
            $esquema = "ctrl" . $i;
            if ($this->existeEsquema($esquema)) {
                array_push($parametrosConsulta, $this->prm->ipu_id);
                array_push($parametrosConsulta, $i);
                $sql .=
                    'SELECT
                        prg_id, spr_id, py_id, oa_id, oa_nombre,
                        SUM(pre_vigente) AS Presupuesto,
                        eio_ejercicio AS ejercicio
                    FROM ' . BD_SINPRES . '.enc_InversionObras
                    INNER JOIN ' . $esquema . '.enc_Programatico USING(epg_id, prg_id, spr_id, py_id, oa_id)
                    INNER JOIN ' . $esquema . '.cat_ObraActividad USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                    INNER JOIN ' . $esquema . '.presupuesto USING(gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id)
                    WHERE ipu_id = ? AND eio_ejercicio = ?
                    GROUP BY epg_id, prg_id, spr_id, py_id, oa_id, oa_nombre, ejercicio
                    UNION ALL
                    ';
            }
        }

        //para el 2019
        $ejercicioHasta = APP_EJERCICIO;
        for ($i = 2019; $i <= $ejercicioHasta; $i++) {
            $esquema = "ctrl" . $i;
            if ($this->existeEsquema($esquema)) {
                array_push($parametrosConsulta, $this->prm->ipu_id);
                array_push($parametrosConsulta, $i);
                $sql .=
                    'SELECT
                        prg_id, spr_id, py_id, oa_id, oa_nombre,
                        SUM(pre_vigente) AS Presupuesto,
                        eio_ejercicio AS ejercicio
                    FROM ' . BD_SINPRES . '.enc_InversionObras
                    INNER JOIN ' . $esquema . '.enc_Programatico USING(epg_id, prg_id, spr_id, py_id, oa_id)
                    INNER JOIN ' . $esquema . '.cat_ObraActividad USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                    INNER JOIN ' . $esquema . '.presupuestoVigente USING(gpo_id, ur_id, epg_id)
                    WHERE ipu_id = ? AND eio_ejercicio = ?
                    GROUP BY epg_id, prg_id, spr_id, py_id, oa_id, oa_nombre, ejercicio
                    ';
                $sql .= ($i < APP_EJERCICIO) ? ' UNION ALL ' : '';
            }
        }

        $obras = '';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($parametrosConsulta);
        while ($data = $con->extraer()) {
            $obras .= '
                <div class="retencion">
                    ' . $data['prg_id'] . '-' . $data['spr_id'] . '-' . $data['py_id'] . '-' . $data['oa_id'] . ' - ' . $data['oa_nombre'] . '
                    <div style="margin-left:10px;display:inline-block;">
                    <span style="float:left; margin-right:5px;"> $</span>' . number_format($data['Presupuesto'], 2) . '
                    </div>
                    <span>Año de la Obra ' . $data['ejercicio'] . '</span>
                </div>';
        }
        $con->desconectar();
        unset($data);

        $sql = '
            SELECT
                ipu_id, ipu_numeroContrato, ipu_objetoContrato, ipu_fechaContrato,
                ipu_inicio, ipu_termino, ipu_licitacion, ipu_importeTotal, ipu_importeAnticipo,
                CONCAT(rfc_nombre, " ", rfc_paterno, "-", rfc_id) AS Proveedor, eir_ejercicio,
                tci_nombre, mdc_nombre, eip_nombre, tre_nombre, cgc_cuenta, cgc_nombre
            FROM ' . BD_SINPRES . '.cat_InversionPublica
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
            INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING(rfc_id)
            INNER JOIN ' . BD_SINPRES . '.cat_TipoContratoInversion USING(tci_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ModalidadContratacion USING(mdc_id)
            INNER JOIN ' . BD_SINPRES . '.cat_EstadoInversion USING(eip_id)
            LEFT JOIN
            (
                SELECT 
                    ipu_id, tre_nombre, cgc_cuenta, cgc_nombre, eir_ejercicio
                FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion 
                INNER JOIN ' . BD_SINPRES . '.cat_RetencionCuenta USING(tre_id, rcu_id)
                INNER JOIN ' . BD_SINPRES . '.cat_TipoRetencion USING(tre_id)
                LEFT JOIN cg_CuentaCuarto USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id)
                WHERE ipu_id = :ipu_id
            )Retenciones USING(ipu_id)
            WHERE ipu_id = :ipu_id
            AND uur_id IN(
                SELECT uur_id
                FROM ' . BD_SINPRES . '.vin_UnidadResponsable
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND vur_ejercicio = :ejercicio
            )
        ';
        $parametrosConsulta = [
            ":gpo_id" => $gpo_id,
            ":ur_id" => $ur_id,
            ":ejercicio" => APP_EJERCICIO,
            ":ipu_id" => $this->prm->ipu_id
        ];

        $con->conectar();
        $con->setSql($sql);
        $con->consultar($parametrosConsulta);
        $data = $con->extraer();

        $html = '';
        $html .= '<div class="infocontrato idcontrato"><title>Id Contrato</title>' . $data['ipu_id'] . '</div>
            <div class="infocontrato numContrato"><title>Numero Contrato</title>' . $data['ipu_numeroContrato'] . '</div>
            <div class="infocontrato idcontrato"><title>Fecha del Contrato</title>' . $data['ipu_fechaContrato'] . '</div>
            <div class="infocontrato periodoContrato"><title>Inicio - Termino</title>' . $data['ipu_inicio'] . ' -' . $data['ipu_termino'] . ' </div>
            <div class="infocontrato idcontrato"><title>Licitacion</title>' . $data['ipu_licitacion'] . '</div>
            <div class="infocontrato idcontrato"><title>Importe</title><span style="float:left;">$</span><span style="float:right;">' . number_format($data['ipu_importeTotal'], 2) . '</span></div>
            <div class="infocontrato idcontrato"><title>Anticipo</title><span style="float:left;">$</span><span style="float:right;">' . number_format($data['ipu_importeAnticipo'], 2) . '</span></div>
            <div class="infocontrato periodoContrato" style="width:80%"><title>Modalidad y Tipo </title>' . $data['mdc_nombre'] . '  -  ' . $data['tci_nombre'] . '</div><br>
            <div class="infocontrato objetoContrato" style="width:50%"><title>Objeto del Contrato</title>' . $data['ipu_objetoContrato'] . '&nbsp;</div>
            <div class="infocontrato objetoContrato" style="width:49%"><title>Obras del Contrato</title>' . $obras . '&nbsp;</div>';
        /************ retenciones */ ///
        $html .= ' <div class="infocontrato objetoContrato" style="min-height:100px; width:100%;"><title>Retenciones del contrato</title>';
        $html .= ' <div class="retencion">';
        $html .= ' <div class="retencioncontrato ret-tit">' . $data['tre_nombre'] . '&nbsp;</div>';
        $html .= ' <div class="retencioncontrato ret-cta">' . $data['cgc_cuenta'] . '&nbsp;</div>';
        $html .= ' <div class="retencioncontrato ret-ctanombre">' . $data['cgc_nombre'] . '&nbsp;</div>';
        $html .= ' <div class="retencioncontrato ret-ctanombre">' . $data['eir_ejercicio'] . '&nbsp;</div></div>';
        while ($data = $con->extraer()) {
            $html .= ' <div class="retencion">';
            $html .= ' <div class="retencioncontrato ret-tit">' . $data['tre_nombre'] . '&nbsp;</div>';
            $html .= ' <div class="retencioncontrato ret-cta">' . $data['cgc_cuenta'] . '&nbsp;</div>';
            $html .= ' <div class="retencioncontrato ret-ctanombre">' . $data['cgc_nombre'] . '&nbsp;</div>';
            $html .= ' <div class="retencioncontrato ret-ctanombre">' . $data['eir_ejercicio'] . '&nbsp;</div></div>';
        }

        $con->desconectar();
        $html .= '</div>';
        $html .= '<br/>';

        $sql = '
            SELECT
                bga_id,iaf_nombre,bga_numeroDocumento,tga_nombre,stg_nombre,
                Documentos,ipu_id,OB.ipu_id AS obr_id,bga_ejercicio,eip_id
            FROM ' . BD_SINPRES . '.cat_BancoGarantia
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
            INNER JOIN ' . BD_SINPRES . '.cat_InstitucionAfianzadora USING(iaf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_SubtipoGarantia  USING(tga_id,stg_id)
            INNER JOIN ' . BD_SINPRES . '.cat_TipoGarantia USING(tga_id)
            LEFT  JOIN ' . BD_SINPRES . '.enc_ContratoGarantia USING(bga_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_InversionPublica USING (ipu_id)
            LEFT JOIN (
                SELECT
                    bga_id,COUNT(dur_id) AS Documentos 
                FROM ' . BD_SINPRES . '.enc_GarantiaDocumento
                INNER JOIN ' . BD_SINPRES . '.cat_DocumentosUr USING(dur_id)
                INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
                WHERE uur_id IN(
                    SELECT
                        uur_id
                    FROM ' . BD_SINPRES . '.vin_UnidadResponsable 
                    WHERE gpo_id = :gpo_id AND ur_id= :ur_id
                    AND vur_ejercicio = :ejercicio
                )GROUP BY bga_id
            )B USING(bga_id)
            LEFT JOIN (
                SELECT
                    ipu_id
                FROM cat_ObraClc
                WHERE ipu_id = :ipu_id
                GROUP BY ipu_id
            ) OB USING(ipu_id)
            WHERE (ipu_id IS NULL OR ipu_id = :ipu_id)
            AND uur_id IN(
                SELECT uur_id 
                FROM ' . BD_SINPRES . '.vin_UnidadResponsable 
                WHERE gpo_id = :gpo_id  AND ur_id = :ur_id
                AND vur_ejercicio = :ejercicio
            )
        ';

        $params = [
            ":gpo_id" => $gpo_id,
            ":ur_id" => $ur_id,
            ":ejercicio" => APP_EJERCICIO,
            ":ipu_id" => $this->prm->ipu_id
        ];

        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $disponibles = $usadas = '';

        while ($gar = $con->extraer()) {
            if ($gar['ipu_id'] == "") {
                $disponibles .= '
                <li class="garantia" id="' . $gar['bga_id'] . '" style="box-shadow:1px 2px 1px #999;">
                <div id="' . $gar['bga_id'] . '" class="draggarantia" >
                    <img src="./img/garantia.png" style="display:inline-flex;"/>
                    <div style="display:inline-block;">
                        <div class="idgarantia">' . $gar['bga_id'] . '</div>
                        ' . $gar['tga_nombre'] . ' - ' . $gar['stg_nombre'] . '</div>
                        <div style="font-size:10px; display:inline-flex; color:#666;">' . ($gar['iaf_nombre'] == "NO APLICA" ? '' : $gar['iaf_nombre']) . '</div>
                        <div style="display:inline-flex; font-size:10px;color:#666;"> Archivos:' . $gar['Documentos'] . '</div>
                        <div style="display:inline-flex; font-size:10px;color:#666;"> Ejercicio:' . $gar['bga_ejercicio'] . '</div>
                </div>
                </li>';
            } else {
                $abc = "c";
                $usadas .= '<li class="garantia ' . ($gar['obr_id'] != "" || $gar['eip_id'] > 1 ? "not" : "") . '" id="' . $gar['bga_id']
                    . '" style="' . ($gar['obr_id'] == "" ? "" : "border-top:1px solid #BBB;background-color:#EAEAEA; box-shadow:1px 2px 1px #999;") . '" >
                ';
                $usadas .= '
                    <div id="' . $gar['bga_id'] . '" class="draggarantia" >
                    <img src="img/garantia.png" style="display:inline-flex;"/>
                    <div style="display:inline-block;">
                        <div class="idgarantia">' . $gar['bga_id'] . '</div>
                        ' . $gar['tga_nombre'] . ' - ' . $gar['stg_nombre'] . '</div>
                        <div style="font-size:10px; display:inline-flex; color:#666;">' . ($gar['iaf_nombre'] == "NO APLICA" ? '' : $gar['iaf_nombre']) . '</div>
                        <div style="display:inline-flex; font-size:10px;color:#666;"> Archivos:' . $gar['Documentos'] . '</div>
                        <div style="display:inline-flex; font-size:10px;color:#666;"> Ejercicio:' . $gar['bga_ejercicio'] . '</div>
                    </div></li>
                ';
            }
        }

        $html .= '<span style="font-size:14px; font-weight:bold;">Agregar / Quitar Garantías del Contrato </span><span style="color:#999;"> Izquierda: Garantías Sin usar, Derecha Garatías Usadas en este CONTRATO</span>
            <br><table style="width:100%;max-height:200px;"><tr><td width="50%"><div class="Addgarantias " style="width:100%"><title>Disponibles</title>
            <ul style=" border: 1px solid #FFF; height:300px; " id="available" class="listas">' . $disponibles . '</ul></div></td>
        ';
        $html .= '<td><div class="Addgarantias" style="width:100%"><title>Agregadas</title>
            <ul id="tosave" style="border: 1px solid #FFF; height:300px; " class="listas">' . $usadas . '</ul></div></td></tr></table>
        ';

        $btnregresa = new stdClass(); //130,98,0
        $btnregresa->id = "comeback";
        $btnregresa->value = "Regresar";
        $btnregresa->icon = "solid fa-circle-left me-2";
        $btnregresa->color = "gris";
        $btnregresa->class = "me-1";
        $botones = $this->ponBoton($btnregresa);

        $btnguarda = new stdClass(); //130,98,0
        $btnguarda->id = "guarda";
        $btnguarda->value = "Guardar";
        $btnguarda->icon = "solid fa-floppy-disk me-2";
        $btnguarda->color = "verde";
        $btnguarda->class = "me-1";
        $botones .= $this->ponBoton($btnguarda);

        $this->agregaTools($botones);
        $this->mixHtml($html);
        $this->mixScript('
            $("#guarda").click(function() {
                bga = new Array();
                $("#tosave li").each(function() {
                    bga.push($(this).attr("id"));
                });
                comunica("' . $this->pantalla . '",{
                    metodo:"abcContratoGarantia",
                    ipu_id:"' . $this->prm->ipu_id . '",
                    bgas:bga,
                    abc:"' . $abc . '"
                });
            });

            $("#available, #tosave").sortable({
                connectWith: ".listas",
                cancel:".not"
            }).disableSelection();

            $("#comeback").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"main"
                })
            });
        ');
    }

    private function abcContratoGarantia()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? (int)$_SESSION[_S]['gpo_id'] : (int)$_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? (int)$_SESSION[_S]['ur_id'] : (int)$_SESSION[_S]['clasAdm-ur'];
        $usr_id = $_SESSION[_S]['usr_id'];

        $params = [$gpo_id, $ur_id, APP_EJERCICIO, $this->prm->ipu_id];
        $sql = '
            SELECT ipu_numeroContrato,eip_id 
            FROM ' . BD_SINPRES . '.cat_InversionPublica 
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
            INNER JOIN (
                SELECT uur_id 
                FROM ' . BD_SINPRES . '.vin_UnidadResponsable
                WHERE gpo_id = ? AND ur_id = ? AND vur_ejercicio = ?
            )uur USING (uur_id)
            WHERE ipu_id = ?
        ';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        if ($con->getNumeroRegistros() == 0) {
            $this->mixScript('notify("Error en el número de contrato","error");');
            return;
        }
        $tmp = $con->extraer();
        $eip_id = $tmp['eip_id'];

        if ($eip_id > 4) {
            $this->mixScript('notify("El Contrato ya no se puede modificar","error");');
            return;
        }

        $con->desconectar();

        /* VALIDAMOS LO QUE EXISTE, LO QUE NO VIENE, LO QUE HAY QUE AGREGAR Y LO QUE HAY QUE QUITAR */
        $params = [$this->prm->ipu_id];
        $sql = ' SELECT bga_id FROM ' . BD_SINPRES . '.enc_ContratoGarantia WHERE ipu_id = ?';
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $existe = array();
        while ($ex = $con->extraer()) {
            array_push($existe, $ex['bga_id']);
        }

        $agregar = array();
        $borrar = '0';

        if (isset($this->prm->bgas) && count($this->prm->bgas) > 0) {
            for ($l = 0; $l < count($this->prm->bgas); $l++) {
                $v = $this->prm->bgas[$l];
                $o = false;
                for ($e = 0; $e < count($existe); $e++) {
                    if ($v == $existe[$e])
                        $o = true;
                }
                if (!$o)
                    array_push($agregar, $v);
            }

            for ($e = 0; $e < count($existe); $e++) {
                $v = $existe[$e];
                $o = false;

                for ($l = 0; $l < count($this->prm->bgas); $l++) {
                    if (isset($this->prm->bgas[$l])) {
                        if ($v == $this->prm->bgas[$l])
                            $o = true;
                    }
                }

                if (!$o && isset($this->prm->bgas[$e])) {
                    $borrar .= $v == $this->prm->bgas[$e] ? '' : ',' . $v;
                }
            }
        } else {
            for ($e = 0; $e < count($existe); $e++) {
                if (isset($existe[$e]))
                    $borrar .= ',' . $existe[$e];
            }
        }

        if (count($agregar) == 0 && $borrar == '0') {
            $this->mixScript('
                notify("Sin cambios detectatos","success");
            ');
            return;
        }

        if ($borrar != '0' && $eip_id > 1) {
            $this->mixScript('notify("No se pueden quitar Garantias de un Contrato Autorizado","error");');
            return;
        }

        array_push($params, $borrar);

        if ($borrar != '0') {
            $sql = 'DELETE FROM ' . BD_SINPRES . '.enc_ContratoGarantia WHERE ipu_id = ? AND bga_id IN(?)';
            $con->conectar();
            $con->setSql($sql);
            $con->consultar($params);
            $con->desconectar();
        }

        if (count($agregar) > 0) {
            $sql = 'INSERT INTO ' . BD_SINPRES . '.enc_ContratoGarantia (ipu_id, bga_id, ecg_ejercicio, usr_id) VALUES';
            $values = '';
            $params = [];
            foreach ($agregar as $bga_id) {
                if ($values == '') {
                    $values .= '(?,?,?,?)';
                    array_push($params, $this->prm->ipu_id, $bga_id, APP_EJERCICIO, $usr_id);
                } else {
                    $values .= ',(?,?,?,?)';
                    array_push($params, $this->prm->ipu_id, $bga_id, APP_EJERCICIO, $usr_id);
                }
            }
            $con->conectar();
            $con->setSql($sql . $values);
            $con->consultar($params);
            $con->desconectar();
        }

        $mensaje = '';
        if ($this->prm->abc == "a") {
            $mensaje = 'Se han agregado las Garantias de manera exitosa';
        } else {
            $mensaje = 'Se han actualizado las Garantias de manera exitosa';
        }

        $this->mixScript('
            alertify.alert(
                "Operación Exitosa!",
                "' . $mensaje . '",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"main"
                    })
                }
            );
        ');
    }
    /********************************************************/
    public  function nvaGarantia()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id']  : $_SESSION[_S]['clasAdm-ur'];
        $uur_id = $this->unicoUr($gpo_id, $ur_id);
        $html = '<legend>Garantia ' . (isset($this->prm->bga_id) ? $this->prm->bga_id : '') . '</legend>';
        $params = [];
        $paramsdoctos = [$uur_id];

        $abc = isset($this->prm->bga_id) ? "c" : "a";
        $garantia = new BancoGarantia();
        if ($abc == "c") {
            $garantia->setBga_id($this->prm->bga_id);
            $garantia->select();
        }
        $ed = '';

        $encontrato = false;
        $doctos = '';
        if ($abc == "c") {
            $params = [$this->prm->bga_id, $uur_id];
            $sql = '
                SELECT 
                    dur_id,dur_nombreOriginal,ipu_id,ipu_numeroContrato,dur_nombreGuardado,tan_id,tan_nombre,dur_tipo
                FROM ' . BD_SINPRES . '.cat_DocumentosUr
                INNER JOIN ' . BD_SINPRES . '.enc_GarantiaDocumento USING(dur_id)
                INNER JOIN ' . BD_SINPRES . '.cat_TipoAnexo USING(tan_id)
                LEFT JOIN ' . BD_SINPRES . '.enc_ContratoGarantia USING (bga_id)
                LEFT JOIN ' . BD_SINPRES . '.cat_InversionPublica USING (ipu_id,vur_id)
                INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
                WHERE bga_id = ? AND uur_id  = ?
            ';
            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql($sql);
            $con->consultar($params);
            $c = 0;
            while ($dat = $con->extraer()) {
                $c++;
                if ($doctos == "") {
                    array_push($paramsdoctos, $dat['dur_id']);
                    $doctos = '?';
                } else {
                    array_push($paramsdoctos, $dat['dur_id']);
                    $doctos .= ",?";
                }

                $ed .= ' <tr dur_id="' . $dat['dur_id'] . '" tan_id="' . $dat['tan_id'] . '" fln="' . $dat['dur_nombreGuardado'] . '" id="fil' . $c . '" ty="' . $dat['dur_tipo'] . '" class="fileinfo">';
                $ed .= ' <td class="just" style="width:5%;"><img src="img/just.png" /></td> ';
                $ed .= ' <td class="filename">' . $dat['dur_nombreOriginal'] . '</td>';
                $ed .= ' <td class="icon"><img src="img/24pxIcon/' . $dat['dur_tipo'] . '.png"/></td>';
                $ed .= ' <td class="fileid">' . $dat['dur_nombreGuardado'] . '</td>';
                $ed .= ' <td class="attach">' . $dat['tan_nombre'] . '</td>';
                $ed .= ' <td class="remove">';

                if ($dat['ipu_id'] != "") {
                    $ed .= 'X';
                    $encontrato = true;
                } else {
                    $ed .= ' <div class="quitararchivo" onClick="quitaArchivo(this.parentNode.parentNode)" tor="fil' . $c . '" >Quitar</div>';
                }
                $ed .= '</div>';
                $ed .= '<td class="verfile">
                    <div class="quitararchivo" onClick="downloadfile(\'' . $dat['dur_nombreGuardado'] . '\',\'d\')" tor="fil' . $c . '">Descargar</div>
                    </td>
                    <td class="verfile">
                        <div class="quitararchivo" onClick="downloadfile(\'' . $dat['dur_nombreGuardado'] . '\',\'v\')" tor="fil' . $c . '">Ver</div>
                    </td>
                ';
                $ed .= ' </tr>';
            }
            $con->desconectar();
        }
        $doc = '';
        if ($doctos != "") {
            $doc = ' AND dur_id NOT IN(' . $doctos . ')';
        }
        array_push($paramsdoctos, $uur_id, $uur_id);
        $sqlDoctos = '
            SELECT dur_id,dur_nombreOriginal AS flname,dur_nombreGuardado AS idf, dur_tipo as fltipo
                FROM ' . BD_SINPRES . '.cat_DocumentosUr
                INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
                WHERE dur_id NOT IN(
                    SELECT dur_id FROM ' . BD_SINPRES . '.enc_GarantiaDocumento
                    INNER JOIN ' . BD_SINPRES . '.cat_BancoGarantia USING (bga_id)
                    INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
                    WHERE   uur_id = ? ' . $doc . '
                    UNION ALL
                    SELECT dur_id 
                    FROM ' . BD_SINPRES . '.enc_InversionObras
                    INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
                    WHERE uur_id = ?
                )
                AND uur_id = ?
        ';

        $comboafianzadoras = new stdClass();
        $comboafianzadoras->id = "iaf_id";
        $comboafianzadoras->sql = 'SELECT iaf_id,iaf_nombre FROM ' . BD_SINPRES . '.cat_InstitucionAfianzadora WHERE iaf_id != 11';
        $comboafianzadoras->name = 'iaf_id';
        $comboafianzadoras->label = 'Institución Afianzadora';
        $comboafianzadoras->camposVal = 'iaf_id';
        $comboafianzadoras->enDiv = true;
        $comboafianzadoras->search = true;
        $comboafianzadoras->required = true;
        $comboafianzadoras->selecciona = $garantia->getIaf_id();

        $cbotipogars = new stdClass();
        $cbotipogars->id = "tga_id";
        $cbotipogars->sql = 'SELECT tga_id,tga_nombre FROM ' . BD_SINPRES . '.cat_TipoGarantia';
        $cbotipogars->name = 'tga_id';
        $cbotipogars->label = 'Tipo de Garantía';
        $cbotipogars->camposVal = 'tga_id';
        $cbotipogars->comoAtr = 'tga_id';
        $cbotipogars->class = "mb-4";
        $cbotipogars->enDiv = true;
        $cbotipogars->search = true;
        $cbotipogars->required = true;
        $cbotipogars->selecciona = $garantia->getTga_id();

        $cbosutigars = new stdClass();
        $cbosutigars->id = "stg_id";
        $cbosutigars->name = 'stg_id';
        $cbosutigars->label = 'Subtipo de Garantía';
        $cbosutigars->camposVal = 'tga_id';
        $cbosutigars->comoAtr = 'tga_id,stg_id';
        $cbosutigars->camposVal = 'stg_id';
        $cbosutigars->enDiv = true;
        $cbosutigars->search = true;
        $cbosutigars->required = true;
        $cbosutigars->selecciona = $garantia->getStg_id();
        if ($abc == "c") {
            $params = [$garantia->getTga_id()];
            $cbosutigars->sql = ' SELECT stg_id,stg_nombre,tga_id 
            FROM ' . BD_SINPRES . '.cat_SubtipoGarantia 
            WHERE tga_id = ? ';
        }
        $cbosutigars->params = $params;

        $inpnumdocto = new stdClass();
        $inpnumdocto->placeholder = "Numero de Documento";
        $inpnumdocto->id = "nodocto";
        $inpnumdocto->label = "Número de Documento";
        $inpnumdocto->required = true;
        $inpnumdocto->name = "requerido";
        $inpnumdocto->value = $garantia->getNumeroDocumento();

        $inpfechaemi = new stdClass();
        $inpfechaemi->placeholder = "Fecha de Emisión";
        $inpfechaemi->id = "fechaemision";
        $inpfechaemi->label = "Fecha de Emisión";
        $inpfechaemi->enDiv = true;
        $inpfechaemi->formato = "fecha";
        $inpfechaemi->required = true;
        $inpfechaemi->name = "requerido";
        $inpfechaemi->value = $garantia->getFechaEmision();

        $inpimporte = new stdClass();
        $inpimporte->placeholder = "0.00";
        $inpimporte->id = "importe";
        $inpimporte->label = "Importe de la Garantia";
        $inpimporte->enDiv = true;
        $inpimporte->formato = "moneda";
        $inpimporte->required = true;
        $inpimporte->name = "requerido";
        $inpimporte->value = $garantia->getImporteGarantia();

        $cbodoctos = new stdClass();
        $cbodoctos->id = "dur_id";
        $cbodoctos->name = 'dur_id';
        $cbodoctos->label = 'Documento';
        $cbodoctos->class = 'mb-4';
        $cbodoctos->search = true;
        $cbodoctos->camposVal = 'dur_id';
        $cbodoctos->comoAtr = 'dur_id,idf,flname,fltipo';
        $cbodoctos->enDiv = true;
        $cbodoctos->sql = $sqlDoctos;
        $cbodoctos->params = $paramsdoctos;

        $cbotipoanexo = new stdClass();
        $cbotipoanexo->id = "tan_id";
        $cbotipoanexo->name = 'tan_id';
        $cbotipoanexo->label = 'Tipo de Anexo';
        $cbotipoanexo->camposVal = 'tan_id';
        $cbotipoanexo->comoAtr = 'tan_id,taname';
        $cbotipoanexo->enDiv = true;
        $cbotipoanexo->search = true;

        $obvser = new stdClass();
        $obvser->placeholder = "Hasta 500 caracteres";
        $obvser->id = "observacion";
        $obvser->label = "Observaciones";
        $obvser->text = $garantia->getObservacion();

        $btnagref = new stdClass();
        $btnagref->id = "addpdf";
        $btnagref->value = "Agregar Fichero";
        $btnagref->icon = "solid fa-file-pdf me-2";
        $btnagref->color = "verde";
        $btnagref->class = "me-1";

        $html .= '<div class="row mb-4">';
        $html .= '<fieldset class="col-md-6 col-xs-12">';
        $html .= $this->ponCombo($cbotipogars);
        $html .= '</fieldset>';
        $html .= '<fieldset class="col-md-6 col-xs-12">';
        if ($abc == "a") {
            $html .= $this->templateCombo($cbosutigars);
        } else if ($abc == "c") {
            $html .= $this->ponCombo($cbosutigars);
        }
        $html .= '</fieldset>';
        $html .= '</div>';

        $html .= '<div id="form_rest" class="row mb-4" style="display:' . ($garantia->getStg_id() != "" && $garantia->getStg_id() == 1 ? "block" : "none") . '">';
        $html .= '<fieldset class="col-md-3 col-xs-3 mb-4">';
        $html .= $this->ponCombo($comboafianzadoras);
        $html .= '</fieldset>';
        $html .= '<fieldset class="col-md-3 col-xs-3">';
        $html .= $this->ponInput($inpnumdocto);
        $html .= '</fieldset>';
        $html .= '<fieldset class="col-md-3 col-xs-3">';
        $html .= $this->ponInput($inpfechaemi);
        $html .= '</fieldset>';
        $html .= '<fieldset class="col-md-3 col-xs-3">';
        $html .= $this->ponInput($inpimporte);
        $html .= '</fieldset><fieldset class="col-md-12 col-xs-12">';
        $html .= $this->ponTextArea($obvser);
        $html .= '</fieldset></div>';

        $html .= '<div class="row mb-4"><fieldset class="col-md-6 col-xs-6">';
        $html .= $this->ponCombo($cbodoctos);
        $html .= '</fieldset><fieldset class="col-md-3 col-xs-3">';
        $html .= $this->templateCombo($cbotipoanexo);
        $html .= '</fieldset><fieldset class="col-md-3 col-xs-3"><br/>';
        $html .= $this->ponBoton($btnagref);
        $html .= '</fieldset> </div>';
        $html .= '<fieldset class="col-md-12 col-xs-12">
            <div  style="border-radius:#EAEAEA; border:2px dashed #EAEAEA; width:100%; min-height:80px;">
            Documentos de la garantia<br/>
            <table id="documentos" class="table">
                <tr style="background:#53C7C8" style="box-shadow: inset 0 0 8px rgba(45,171,102,0.75);">
                    <th width="3%">&nbsp;</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Indentificador</th>
                    <th>Tipo Anexo</th>
                    <th width="3%">Quitar</th>
                    <th width="3%">Descargar</th>
                    <th width="3%">Ver</th>
                </tr>
            ' . $ed . '
            </table>
            </fieldset>
        ';
        //--------------------- BOTONES DE NAVEGACION  ----------------------------------------/
        $btnagrega = new stdClass();
        $btnagrega->id = "agregadocto";
        $btnagrega->value = "Agregar Nuevo Documento";
        $btnagrega->icon = "solid fa-plus me-2";
        $btnagrega->color = "gris";
        $btnagrega->class = "me-1";

        $btnregresa = new stdClass();
        $btnregresa->id = "comeback";
        $btnregresa->value = "Regresar";
        $btnregresa->icon = "solid fa-circle-left me-2";
        $btnregresa->color = "gris";
        $btnregresa->class = "me-1";

        $btnelimina = new stdClass();
        $btnelimina->id = "deleterecord";
        $btnelimina->value = "Eliminar";
        $btnelimina->color = "rojo";
        $btnelimina->class = "me-1";
        $btnelimina->icon = "solid fa-trash me-2";

        $guarda = new stdClass();
        $guarda->id = "saverecord";
        $guarda->value = "Guardar";
        $guarda->icon = "solid fa-floppy-disk me-2";
        $guarda->class = "me-1";
        $guarda->color = "verde";

        $botones = $this->ponBoton($btnregresa);
        if (!$encontrato) {
            $botones .= $this->ponBoton($guarda);
            if ($abc == "c") {
                $botones .= $this->ponBoton($btnelimina);
                $this->mixScript('
                    $("#deleterecord").click(function() {
                        comunica("' . $this->pantalla . '",{
                            metodo:"abcGarantia",
                            abc:"b",
                            bga_id:"' . $this->prm->bga_id . '",
                        })
                    });
                ');
            }
        }
        $botones .= $this->ponBoton($btnagrega);

        $this->mixHtml($html);
        $this->agregaTools($botones);
        $this->mixScript('
            var files = 0;
            function quitaArchivo(el)
            {
                el.remove();
            }

            function verpdf(n,t)
            {
                $("#verpdf").remove();
                if (t == "d")
                    //$("body").append("<iframe style=\"display:none\" id=\"verpdf\" src=\"scp/afectaciones/download_file.php?t=d&file="+ n +"\"></iframe>")
                    $("body").append("<iframe style=\"display:none;z-index:9000\" id=\"verpdf\" src=\"scp/afectaciones/download_file.php?t=d&file="+ n +"\"></iframe>")
                else {
                    n = $("<iframe/>").attr("src","scp/afectaciones/download_file.php?t=v&file="+ n).css("width","96%").css("height","100%");
                    c = $("<span/>").addClass("glyphicon glyphicon-remove-circle").css("float","right").css("font-size","30px").css("cursor","pointer").click(function() { $("#verpdf").remove(); });
                    m = $("<div/>").addClass("documento_fs").css("color","#FFF").attr("id","verpdf").append(c).append(n).appendTo("body");
                }
            }

            function downloadfile(data,e) {
                comunica("' . $this->pantalla . '",{
                    metodo:"download",
                    x:data,
                    t:e
                });
            }

            $("#tga_id").change(function() {
                if ($(this).find("option:selected").val()!="") {
                    comunica("' . $this->pantalla . '",{
                        metodo:"subtipoGarantia",
                        tga_id:$(this).find("option:selected").val()
                    });
                }
            });

            $("#stg_id").change(function() {
                if ($(this).find("option:selected").val()!="") {
                    if ($(this).find("option:selected").val()==1) {
                        $("#form_rest").fadeIn();
                    }else{
                        $("#form_rest").fadeOut();
                    }
                    comunica("' . $this->pantalla . '",{
                        metodo:"tipoAnexo",
                        tga_id:$("#tga_id").find("option:selected").val(),
                        stg_id:$(this).find("option:selected").val()
                    });
                    $(".fileinfo").remove();
                }
            });

            $("#addpdf").click(function() {
                var tipoa = $("#tan_id").find("option:selected").val();
                var durpdf = $("#dur_id").find("option:selected").val();
                if (durpdf == undefined || durpdf == "") {
                    notify("Debe Seleccionar un documento","warning");
                    return false;
                }
                
                if (typeof tipoa ==="undefined" || tipoa=="") {
                    notify("Debe seleccionar un tipo de anexo","warning");
                    return false;
                }
                data2 = {
                    flid : $("#dur_id").find("option:selected").attr("dur_id"),
                    flname : $("#dur_id").find("option:selected").attr("flname"),
                    flicon : $("#dur_id").find("option:selected").attr("fltipo"),
                    flmdfi : $("#dur_id").find("option:selected").attr("idf"),
                    fltan : $("#tan_id").find("option:selected").val(),
                    fltano : $("#tan_id").find("option:selected").attr("taname")
                    };
                var file = new createRow(data2);
                delete data2;
            });

            $("#agregadocto").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"agrega",
                    origen:"' . ((isset($this->prm->origen) ? $this->prm->origen : '')) . '",
                    metodoOrigen:"' . ((isset($this->prm->metodo) ? $this->prm->metodo : '')) . '",
                    bandera : ' . $this->prm->bandera .',
                    pantalla:"scp/afectaciones/ControladorGarantias.php"
                    ' . (isset($this->prm->anio) ? ', anio:' . $this->prm->anio : '') . '
                    //anio:"' . $this->prm->anio . '"
                });
            });

            $("#comeback").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"garantias"
                    ' . (isset($this->prm->anio) ? ',anio:' . $this->prm->anio : '') . '
                    '.(isset($this->prm->bandera)?', bandera:"'.$this->prm->bandera.'"':'').'
                });
            });
            id = 0;

            /****************************************************************/
            function createRow(data) {
                id ++;
                put = true;
                $(".fileinfo").each(function() {
                    if ($(this).attr("dur_id")==data.flid && $(this).attr("fln")==data.flmdfi) {
                        alertify.alert("Atención!","El archivo ya esa en la lista");		
                        put= false;
                    }
                });
                if (! put)
                    return false;

                this.row = $("<tr/>")
                    .attr("dur_id",data.flid)
                    .attr("tan_id",data.fltan)
                    .attr("fln",data.flmdfi)
                    .attr("id","fil"+id)
                    .attr("ty",data.flicon)
                    .addClass("fileinfo");
                this.file = $("<td/>").addClass("just").append($("<img/>").attr("src","img/just.png")).appendTo(this.row);
                this.filename=$("<td/>").addClass("filename").html(data.flname).appendTo(this.row);
                this.icon = $("<td/>").addClass("icon").append($("<img/>").attr("src","img/24pxIcon/" + data.flicon + ".png")).appendTo(this.row);
                this.id = $("<td/>").addClass("fileid").html(data.flmdfi).appendTo(this.row);
                this.tipan = $("<td/>").addClass("attach").html(data.fltano).appendTo(this.row);
                this.remove = $("<td/>").addClass("remove").append($("<div/>").addClass("quitararchivo").attr("tor","fil"+id).html("Quitar").click(function() { quitaArchivo(this.parentNode.parentNode);})					).appendTo(this.row);
                this.dow  = $("<td/>").addClass("verfile").append($("<div/>").addClass("quitararchivo").attr("tor","fil"+id).attr("tor","fil" + id).html("Descargar").click(function() { downloadfile(data.flmdfi,"d"); }) ).appendTo(this.row);
                this.view = $("<td/>").addClass("verfile").append($("<div/>").addClass("quitararchivo").attr("tor","fil"+id).attr("tor","fil" + id).html("Ver").click(function() { downloadfile(data.flmdfi,"v"); }) ).appendTo(this.row);
                $("#documentos").append(this.row);
                files ++;
            }

            /************************************************************/
            $("#saverecord").click(function() {
                nf = 0;
                dat = new Array();
                $(".fileinfo").each(function() {
                    nf++;
                    dat.push([
                        {tan_id : $(this).attr("tan_id")},
                        {dur_id : $(this).attr("dur_id")},
                        {dur_md5 : $(this).attr("fln")}
                    ]);
                });

                // Frase es el la cadena de caracteres que se tomara antes de de llenar a la base de datos 
                // y se le quitaran las comillas simpres y dobles

                var frase = $("#observacion").val();
                var frase2 = frase;
                var contador = 0;
                console.log(frase);
                do{
                    frase2 = frase2.replace("\'"," ");
                }while(frase2.indexOf("\'") != -1);
                do{
                    frase2 = frase2.replace("\""," ");
                }while(frase2.indexOf("\"") != -1);

                var frase3=frase2;

                while(frase3.indexOf(" ") != -1) {
                    console.log("me atore aqui");
                    frase3 = frase3.replace(" ","x");
                    contador++;
                }

                console.log(frase2);
                if (nf == 0) {
                    notify("No hay documentos adjuntos a la garantia","warning");
                    return false;
                }else{
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcGarantia",
                        abc:"' . $abc . '",
                        ' . ($abc == "c" ? 'bga_id:' . $this->prm->bga_id . ',' : '') . '
                        doctos:dat,
                        stg_id:$("#stg_id").find("option:selected").val(),
                        tga_id:$("#tga_id").find("option:selected").val(),
                        iaf_id:$("#iaf_id").find("option:selected").val(),
                        numeroDocumento:$("#nodocto").val(),
                        importeGarantia:parseFloat(unformatNumber($("#importe").val(),2)),
                        observacion:frase2,
                        fechaEmision:$("#fechaemision").val()
                        ' . (isset($this->prm->anio) ? ',anio:' . $this->prm->anio : '') . '
                    })
                }
            });
        ');
        if ($abc == "c") {
            $this->prm->tga_id = $garantia->getTga_id();
            $this->prm->stg_id = $garantia->getStg_id();
            $this->tipoAnexo();
        }
    }

    private function abcGarantia()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $this->prm->gpo_id = $gpo_id;
        $this->prm->ur_id = $ur_id;
        $c = 0;
        $tanes = array();
        $dures = array();

        $con = new AccesoDatos();
        $params = [$gpo_id, (int)$ur_id, APP_EJERCICIO];
        $con->setSql('SELECT vur_id FROM ' . BD_SINPRES . '.vin_UnidadResponsable WHERE gpo_id = ? AND ur_id = ? AND vur_ejercicio = ?');
        $con->conectar();
        $con->consultar($params);
        $vur =  $con->extraer();
        $con->desconectar();
        unset($con);
        $this->prm->vur_id = $vur['vur_id'];
        $this->prm->ejercicio = APP_EJERCICIO;
        $usr_id = $_SESSION[_S]['usr_id'];

        if ($this->prm->abc != "b") {
            if ($this->prm->stg_id == 1) {
                if ($this->prm->iaf_id == "") {
                    $this->mixScript('notify("No selecciono la Institucion Afianzadora","error");');
                    return false;
                }

                if (trim($this->prm->numeroDocumento) == "") {
                    $this->mixScript('notify("No capturo el número de Documento","error");');
                    return false;
                }

                if (trim($this->prm->fechaEmision) == "") {
                    $this->mixScript('notify("La fecha esta vacia","error");');
                    return false;
                }

                if (trim($this->prm->importeGarantia) == "" || !is_numeric($this->prm->importeGarantia)) {
                    $this->mixScript('notify("El importe es incorrecto o es cero","error");');
                    return false;
                }

                if (trim($this->prm->observacion) == "") {
                    $this->prm->observacion = " ";
                }
            } else {
                $this->prm->iaf_id = 11;
                $this->prm->numeroDocumento = "N/A";
                unset($this->prm->fechaEmision);
                $this->prm->importeGarantia = "0";
                $this->prm->observacion = "N/A";
            }

            $c = 0;
            $tanes = array();
            $dures = array();
            foreach ($this->prm->doctos as $dato => $valor) {
                $c++;
                array_push($tanes, $valor[0]->tan_id);
                array_push($dures, $valor[1]->dur_id);
            }

            switch ($c) {
                case 0:
                    $this->mixScript('notify("No hay ningun documento adjunto","error");');
                    return false;
                    break;
                case 1:
                    if ($this->prm->stg_id == 1) {
                        $this->mixScript('notify("La Póliza de Fianza requiere dos documentos","error");');
                        return false;
                    }
                    switch ($this->prm->stg_id) {
                        case 2:
                            if ($tanes[0] != 3) {
                                $this->mixScript('notify("El tipo de Anexo no es valido","error");');
                                return false;
                            }
                            break;
                        case 3:
                            if ($tanes[0] != 4) {
                                $this->mixScript('notify("El tipo de Anexo no es valido","error");');
                                return false;
                            }
                            break;
                        case 4:
                            if ($tanes[0] != 5) {
                                $this->mixScript('notify("El tipo de Anexo no es valido","error");');
                                return false;
                            }
                            break;
                    }
                    break;
                case 2:
                    if ($this->prm->stg_id != 1) {
                        $this->mixScript('notify("Este tipo de Garantia solo requiere un documento","error");');
                        return false;
                    }
                    switch ($tanes[0]) {
                        case 1:
                            if ($tanes[1] != 2) {
                                $this->mixScript('notify("Falta la Validación de Autenticidad ","error");');
                                return false;
                            }
                            break;
                        case 2:
                            if ($tanes[1] != 1) {
                                $this->mixScript('notify("Falta la Póliza de Fianza","error");');
                                return false;
                            }
                            break;
                        default:
                            $this->mixScript('notify("El primer documento no es valido para este tipo de Garantia","error");');
                            return false;
                            break;
                    }
                    break;
                default:
                    $this->mixScript('notify("El número de documentos excede el maximo","error");');
                    return false;
                    break;
            }
        }

        switch ($this->prm->abc) {
            case "a":
                $id = BancoGarantia::abc($this->prm);
                $sql = '';
                if ($this->prm->stg_id == 1) {
                    $sql = 'INSERT INTO ' . BD_SINPRES . '.enc_GarantiaDocumento(bga_id,dur_id,tan_id,egd_ejercicio,usr_id) VALUES';
                    $params = [$id, $dures[0], $tanes[0], APP_EJERCICIO, $usr_id, $id, $dures[1], $tanes[1], APP_EJERCICIO, $usr_id,];
                    $sql .= '(?,?,?,?,?),(?,?,?,?,?)';
                } else {
                    $sql = 'INSERT INTO ' . BD_SINPRES . '.enc_GarantiaDocumento (bga_id,dur_id,tan_id,egd_ejercicio,usr_id) VALUES';
                    $params = [$id, $dures[0], $tanes[0], APP_EJERCICIO, $usr_id];
                    $sql .= '(?,?,?,?,?)';
                }
                $cone = new AccesoDatos();
                $cone->conectar();
                $cone->setSql($sql);
                $cone->consultar($params);
                $cone->desconectar();
                break;
            case "b":
                $params = [$this->prm->bga_id];
                $sql = 'DELETE FROM ' . BD_SINPRES . '.enc_GarantiaDocumento WHERE bga_id = ?';
                $cone = new AccesoDatos();
                $cone->conectar();
                $cone->setSql($sql);
                $cone->consultar($params);
                $cone->desconectar();
                BancoGarantia::abc($this->prm);
                break;
            case "c":
                $cone = new AccesoDatos();
                $cone->conectar();
                $params = [$this->prm->bga_id];
                $sql = 'DELETE FROM ' . BD_SINPRES . '.enc_GarantiaDocumento WHERE bga_id = ? ';
                $cone->setSql($sql);
                $cone->consultar($params);
                $cone->desconectar();

                $sql2 = '';
                if ($this->prm->stg_id == 1) {
                    $sql2 = 'INSERT INTO ' . BD_SINPRES . '.enc_GarantiaDocumento (bga_id,dur_id,tan_id,egd_ejercicio,usr_id) VALUES';
                    $params = [$this->prm->bga_id, $dures[0], $tanes[0], APP_EJERCICIO, $usr_id, $this->prm->bga_id, $dures[1], $tanes[1], APP_EJERCICIO, $usr_id,];
                    $sql2 .= '(?,?,?,?,?),(?,?,?,?,?)';
                } else {
                    $sql2 = 'INSERT INTO ' . BD_SINPRES . '.enc_GarantiaDocumento (bga_id,dur_id,tan_id,egd_ejercicio,usr_id) VALUES';
                    $params = [$this->prm->bga_id, $dures[0], $tanes[0], APP_EJERCICIO, $usr_id];
                    $sql2 .= '(?,?,?,?,?)';
                }
                $cone->conectar();
                $cone->setSql($sql2);
                $cone->consultar($params);
                $cone->desconectar();
                BancoGarantia::abc($this->prm);
                break;
        }
        $this->mixScript('
            notify("El proceso se completo de manera exitosa!!","success");
            comunica("' . $this->pantalla . '",{
                metodo:"garantias"
                ' . (isset($this->prm->anio) ? ',anio:' . $this->prm->anio : '') . '
                '.(isset($this->prm->bandera)?', bandera:"'.$this->prm->bandera.'"':'').'
                }
            );
        ');
    }

    private function llenaTablaDocumentos()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id  = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $uur_id = $this->unicoUr($gpo_id, $ur_id);
        $params = [$uur_id, $uur_id];
        $sql = '
            SELECT 
                bga_id,CONCAT(tga_nombre," - ",stg_nombre)AS tipogar,
                bga_ejercicio,
                iaf_nombre AS Institucion,
                bga_numeroDocumento AS numDocto,
                bga_fechaEmision AS fechaemi,
                bga_importeGarantia AS Importe,
                adjuntos,
                if (Contrato IS NOT NULL,Contrato,"NO")AS Contrato
            FROM ' . BD_SINPRES . '.cat_BancoGarantia
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
            INNER JOIN ' . BD_SINPRES . '.cat_TipoGarantia USING (tga_id)
            INNER JOIN ' . BD_SINPRES . '.cat_SubtipoGarantia USING(tga_id,stg_id)
            INNER JOIN ' . BD_SINPRES . '.cat_InstitucionAfianzadora USING(iaf_id)
            LEFT JOIN (
                SELECT bga_id,COUNT(*)AS adjuntos FROM  
                    ' . BD_SINPRES . '.enc_GarantiaDocumento
                INNER JOIN ' . BD_SINPRES . '.cat_DocumentosUr USING (dur_id)
                INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id ) 
                WHERE uur_id = ?
                GROUP BY bga_id
            ) B USING(bga_id)
            LEFT JOIN(
                SELECT bga_id, ipu_numeroContrato AS Contrato 
                FROM  ' . BD_SINPRES . '.enc_ContratoGarantia
                INNER JOIN ' . BD_SINPRES . '.cat_InversionPublica USING (ipu_id)
                GROUP BY bga_id, Contrato
            )C USING(bga_id)
            WHERE uur_id = ?
        ';
        /* FILTROS DE LA BUSQUEDA  */

        if (isset($this->prm->anio) && $this->prm->anio != "") {
            array_push($params, $this->prm->anio);
            $sql .= ' AND bga_ejercicio = ? ';
        }

        if (isset($this->prm->bgaid) && $this->prm->bgaid != "") {
            array_push($params, $this->prm->bgaid);
            $sql .= ' AND bga_id = ? ';
        }

        if (isset($this->prm->numcontr) && $this->prm->numcontr != "") {
            array_push($params, $this->prm->numcontr);
            $sql .= ' AND Contrato = ? ';
        }

        if (isset($this->prm->numdocto) && $this->prm->numdocto != "") {
            array_push($params, $this->prm->numdocto);
            $sql .= ' AND bga_numeroDocumento = ? ';
        }

        if (isset($this->prm->iaf) && $this->prm->iaf != "") {
            array_push($params, $this->prm->iaf);
            $sql .= ' AND iaf_id = ? ';
        }

        if (isset($this->prm->tga) && $this->prm->tga != "") {
            $tg = substr($this->prm->tga, 0, 1);
            $st = substr($this->prm->tga, 2, 1);
            array_push($params, $tg, $st);
            $sql .= ' AND tga_id = ? AND stg_id = ? ';
        }

        $sql .= PHP_EOL . 'ORDER BY bga_id ';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf) ? $this->prm->nav->nf : 20);
        $nav->idTabla = 'tbl_doctos';

        /*checo el año para la bandera de pagina*/
        $band = '';
        switch ($this->prm->anio) {
            case 2015:
                $band = "1";
                break;
            case 2016:
                $band = "2";
                break;
            case 2017:
                $band = "3";
                break;
            case 2018:
                $band = "4";
                break;
            case 2019:
                $band = "5";
                break;
            case 2020:
                $band = "6";
                break;
            case 2021:
                $band = "7";
                break;
            case 2022:
                $band = "8";
                break;
            case 2023:
                $band = "9";
                break;
            case 2024:
                $band = "10";
                break;
            case 2025:
                $band = "11";
                break;
            default:
                $band = "0";
                break;
        }
        /*---------------------------------------*/
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay Garantías registradas","warning");');
            return;
        } else {
            $this->mixScript('
                $(".editargar").click(function() {
                    comunica("' . $this->pantalla . '",{
                        anio:"' . $this->prm->anio . '",
                        metodo:"nvaGarantia",
                        bga_id:$(this).attr("bga_id")
                        }
                    );
                });
            ');
        }
    }

    public function agrega()
    {
        $btnregresa = new stdClass();
        $btnregresa->id = "comeback";
        $btnregresa->value = "Regresar";
        $btnregresa->icon = "solid fa-circle-left me-2";
        $btnregresa->color = "gris";
        $btnregresa->class = "me-1";
        $botones = $this->ponBoton($btnregresa);
        $this->agregaTools($botones);
        $html = '<legend>Cargue la documentación necesaria para sus Contratos de Obra</legend>';
        $html .= '<br/>
            <div id="dropzone" class="mb-4">
                <fieldset  class="col-md-6 col-xs-6 " style="min-height:80px;">
                    Suba sus archivos uno a uno
                    <div style="width:100%; ">
                        <input type="file" name ="objetivosFile" id="nombreArchObj"  data-url="scp/afectaciones/ControladorGarantias.php" data-form-data=\'{"metodo":"upload","origen":"input"}\' >
                            <div id="progressObj" class="progress progress-striped">
                                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"     style="width: 0%;">
                                    <span class="sr-only">100% Complete</span>
                                </div>
                            </div>
                    </div>
                </fieldset>
                <fieldset  class="col-md-6 col-xs-6" style="min-height:80px; max-height:80px; overflow:auto; ">
                    <div style="width:100%;" id="listaArchivos">
                    </div>
                </fieldset>
            </div>

            <legend class="mb-4" align="center"> o Bien</legend>
            <div id="dragndrop" style=""><div style="text-align:center;"><span style="font-size:170%"> Arrastre sus archivos aqui</span></div>
                <div class="statusbar2 odd">
                    <div class="infofile filename">Nombre del Archivo</div>
                    <div class=" infofile filesize">Tamaño</div>
                    <div class="infofile  ">Tipo</div>
                    <div class="infofile bar">Progreso</div>
                    <div class="infofile abort ">Borrar Archivo</div>
                </div>
            </div>
        ';
        $html .= '<div id="observaciones" style="padding:15px; background-color:#EAEAEA; border-radius:4px; border:1px solid #CCC;">';
        $html .= '<div style="display:block; font-weight:bold; border-radius:4px 4px 0px 0px;margin-left:-15px; margin-top:-15px;
            margin-right:-15px; padding:4px;
            border-bottom:1px dotted #777;
            background-color:#e8f0d8;">Observaciones a los Ficheros a cargar</div>
        ';
        $html .= 'El tipo permitido de archivos es PDF<br> ';
        $html .= 'Puede subir un maximo de 15 Archivos al <i>arrastar y soltar</i><br> ';
        $html .= 'El tamaño maximo de un archivo es de 20 Mb<br> ';
        $html .= 'Si su documento tiene mas de una hoja al escanearlo o generar su PDF debe  estar en un solo Fichero/Archivo PDF no en multiples<br> ';
        $html .= 'Evite nombres genericos en sus archivos como <i>Escaneo001,Hoja1, Doc1,Escaneo</i> etc. Es altamente recomendado que los nombres de sus ficheros sean facilmente reconocidos por Usted je,<i> Garantia GAR/34/, Billete numero 14</i><br> ';
        $html .= '</div>';

        $this->mixHtml($html);
        $this->mixScript('
            $("#nombreArchObj").fileupload({
                add: function(e, data) {
                    var acceptFileTypes = /^image\/(gif|jpe?g|png|PDF|xls|xlsx|doc|docx)$/i;
                    var tipos =["DOC","DOCX","XLS","XLSX","JPG","PNG","GIF","PDF","XML"]
                        if (data.originalFiles[0][\'size\'] > 20971500) {
                            $("#listaArchivos").append(\'El archivo \'+ data.originalFiles[0][\'name\']+\' es muy grande<br>\');
                            return false;
                        }
                        data.submit();
                    },
                    dropZone: $("#dropzone"),
                    autoUpload:false,
                    dataType:"json",
                    done:function(e, data) {
                },
                progress: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100,10);
                    $("#progressObj .progress-bar").css("width", progress + "%").attr("aria-valuenow",progress);
                },
                success:function(data) {
                    $("#progressObj .progress-bar").css("width","0%");
                    comunicaCallback(data.comunica);	
                }
            });

            var obj = $("#dragndrop");
            obj.on("dragenter", function (e) {
                e.stopPropagation();
                e.preventDefault();
                $(this).css("border", "2px solid #AE8B1C");
            });
            obj.on("dragover", function (e)	{
                e.stopPropagation();
                e.preventDefault();
            });
            obj.on("drop", function (e) {
                $(this).css("border", "2px dotted #AE8B1C");
                e.preventDefault();
                var files = e.originalEvent.dataTransfer.files;
                console.log(files);
                if (files.length>15) {
                    notify("Intenta con una cantidad menor  a  15 archivos","warning");
                    return false;
                }
                handleFileUpload(files,obj);
            });
            /** PREVENIR QUE EL ARCHIVO QUE SE SUELTA FUERA DEL DIV NO SE CARGUE EN EL EXPLORADOR  */
            $(document).on("dragenter", function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
            $(document).on("dragover", function (e)
            {
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

            function handleFileUpload(files,obj) {
                var tipos =["PDF"]
                for (var i = 0; i < files.length; i++) {
                    var fd = new FormData();
                    fd.append("file", files[i]);
                    fd.append("metodo","upload");
                    fd.append("origen","multiple");
                    var status = new createStatusbar(obj);
                    fd.append("id",status.getId());
                    status.setFileNameSize(files[i].name,files[i].size);
                    ext = files[i].name.split(".").pop().toUpperCase();
                    status.setIcon(ext);

                    if (files[i].size> 20971500) {
                        status.statusbar.addClass("error2");
                        status.abort.html("El archivo es mayor a 20 Megabytes");
                    }else if (tipos.indexOf(ext)==-1) {
                        status.statusbar.addClass("error2");
                        status.abort.html("archivo no valido");
                    }else{
                        sendFileToServer(fd,status);
                    }
                }
            }
            var rowCount=0;
            function createStatusbar(obj) {
            rowCount++;
            var row="odd";
            if (rowCount %2 ==0) row ="even";
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
                this.setIcon = function(extension) {
                    icono = "";
                    switch(extension) {
                        case "PDF": icono = "pdf"; break;
                        case "XML": icono = "xml"; break;
                        case "DOC":case "DOCX": icono = "word"; break;
                        case "XLS":case "XLSX": icono = "excel"; break;
                        case "JPG":case "JPGE": icono = "jpg"; break;
                        case "PNG": icono = "png"; break;
                        case "MSI": icono = "verboten"; break;
                        default: icono = "verboten"; break;
                    }
                    this.icon.html("<img src=\'img/24pxIcon/"+ icono+".png \' height=\"21\" />");
                }
                this.setIdFile = function(nombre) {
                    this.idFile.html("ID:" + nombre + "<span class=\"deletefile\" id=\'"+nombre+"\'>Borrar X<span/>");
                }
                this.getFileName = function() {	return this.filename.html(); }
                this.setFileNameSize = function(name,size) {
                    var sizeStr="";
                    var sizeKB = size/1024;
                    if (parseInt(sizeKB) > 1024) {
                        var sizeMB = sizeKB/1024;
                        sizeStr = sizeMB.toFixed(2)+" MB";
                    }else{
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
                    this.abort.click(function() {
                        jqxhr.abort();
                        sb.hide();
                    });
                }
            }

            function sendFileToServer(formData,status) {
                var uploadURL ="scp/afectaciones/ControladorGarantias.php"; //Upload URL
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
                    success: function(data) {
                        status.setIdFile(data.idFile);
                    }
                }).done(function(data) {	
                    comunicaCallback(data.comunica);
                });
                status.setAbort(jqXHR);
            }
            $("#comeback").click(function() {
                comunica("' . (isset($this->prm->origen) ? $this->pantalla : "scp/afectaciones/ControladorRegistroObra.php") . '",
                {
                    metodo:"' . (isset($this->prm->metodoOrigen) ? $this->prm->metodoOrigen : "main") . '"
                    ' . (isset($this->prm->garantia) ? ', garantia:"true"' : '') . '
                    ' . (isset($this->prm->anio) ? ', anio:"' . $this->prm->anio . '"' : '') . '
                    ' . (isset($this->prm->regPagPrin) ? ', regPagPrin:"' . $this->prm->regPagPrin . '"' : '') . '
                    '.(isset($this->prm->bandera)?', bandera:"'.$this->prm->bandera.'"':'').'
                });
            });

            function download(idd,pgb,type) {
                window.URL = window.URL || window.webkitURL;
                nn = document.getElementById(pgb);
                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                            xhr.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                wid = percentComplete*100;
                                $("#"+ pgb).css("width",wid+"px");
                            }
                        }, false);
                        return xhr;
                        },
                    type:"POST",
                    url:"scp/afectaciones/download_file.php",
                    data:{file:pgb,tx:type}
                });
            }
        ');
    }

    private function upload()
    {
        $respuesta = new stdClass();
        $icono = '';
        $extp = array("PDF");
        foreach ($_FILES as $file) {

            $tmp = <<<HTML
            <div class="statusbar2 error2">
                <div class="infofile filename">{$file['name']}</div>
                <div class=" infofile filesize"></div>
                <div class="infofile "><img src="img/24pxIcon/{$icono}.png"/></div>
                <div class="infofile "></div><div class="infofile  abort">El archivo no esta permitido</div>
            </div>
            HTML;
            $escapedTmp = json_encode($tmp);
            if (!in_array(strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)), $extp)) {
                if (isset($this->prm->origen) && $this->prm->origen == "input") {

                    $this->mixScript('$("#listaArchivos").append(' . $escapedTmp . ');');
                } else {
                    $this->mixScript('
                        $("#' . $this->prm->id . '").remove();
                        $("#dragndrop").append(' . $escapedTmp . ');');
                }
                return;
            }

            $namemd5 = strtoupper(md5_file($file['tmp_name']));
            $params = [$namemd5];
            $sql = 'SELECT * FROM ' . BD_SINPRES . '.cat_DocumentosUr WHERE dur_nombreGuardado = ? ';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $reg = $this->getNumeroRegistros();
            $data = $this->extraer();
            $params = [APP_EJERCICIO];
            $this->setSql('
                SELECT vur_id FROM ' . BD_SINPRES . '.vin_UnidadResponsable 
                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND vur_ejercicio = ?;
            ');

            $this->consultar($params);
            $v = $this->extraer();
            $v = $v['vur_id'];
            $this->desconectar();
            $linkid = $this->getName(5);
            if ($reg > 0) {
                $tmp = <<<HTML
                <div class="statusbar2 error3">
                    <div class="infofile filename">{$file['name']}</div>
                    <div class="infofile abort">El archivo ya ha sido subido</div>
                    <div class="infofile"><img src="img/24pxIcon/{$icono}.png"/></div>
                    <div class="infofile abort">
                        <a target="_blank" id="{$linkid}" href="scp/afectaciones/download_file.php?file={$linkid}&x={$data['dur_tipo']}">Descargar</a>
                    </div>
                </div> 
                HTML;
                $escapedTmp = json_encode($tmp);
                if (isset($this->prm->origen) && $this->prm->origen == "input") {

                    $this->mixScript('$("#listaArchivos").append(' . $escapedTmp . ');');
                } else {
                    $this->mixScript('
                        // $("#' . $this->prm->id . '").remove();
                        $("#dragndrop").append(' . $escapedTmp . ');
                        $("#' . $linkid . '").click(function() {
                            comunica("' . $this->pantalla . '",{
                                metodo:"download",
                                id:"' . $data['dur_id'] . '",
                                x:"' . $data['dur_id'] . '"
                            });
                            $(this).remove();
                        });
                    ');
                    die('fin1');
                }
                return;
            }

            $size = $file['size'] / 1024;
            if ($size > 1024) {
                $size = number_format(($size / 1024), 2);
                $size .= ' Mb';
            } else {
                $size = number_format($size, 2);
                $size .= 'Kb';
            }

            if (isset($file) && $file['error'] == '0') {

                $up = new FicheroSefip();
                $up->setTipos(array("pdf"));
                $up->setFichero($file);
                $up->setModulo("IP");
                $up->setfile($namemd5);
                $up->setAnioFichero(APP_EJERCICIO);
                $directorio = $up->getRutaMod();

                if ($directorio === false && $up->getError() === true) {
                    $this->setStatus("error");
                    $respuesta->mensaje = $up->getMsg();
                } else {
                    if ($up->upload()) {
                        $usr = $_SESSION[_S]['usr_id'];
                        $params = [$v, $file['name'], $namemd5, strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)), $directorio . $namemd5 . '.' . strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)), APP_EJERCICIO, $usr];
                        $sql = 'INSERT INTO ' . BD_SINPRES . '.cat_DocumentosUr (vur_id,dur_nombreOriginal,dur_nombreGuardado,dur_tipo,dur_ruta,dur_ejercicio,usr_id)
                            VALUES(?,?,?,?,?,?,?);';

                        $this->conectar();
                        $this->setSql($sql);
                        $this->consultar($params);
                        $this->idFile = $this->getUltimoId();
                        $this->desconectar();
                        $this->mixScript('notify("El fichero \"' . $file['name'] . '\" fue cargado con éxito","success");');
                        $this->mixScript('
                            $("#'.$this->prm->id.'").click(function() {
                                comunica("scp/afectaciones/ControladorGarantias.php",{
                                    metodo:"delfile",
                                    fid:"'.$this->idFile.'"
                                });
                                $("#'.$this->prm->id.'").remove();
                            });
                        ');
                    } else {
                        $this->setStatus("error");
                        $respuesta->mensaje = $up->getMensaje();
                    }
                }
            } else {
                $this->setStatus("error");
                $respuesta->mensaje = "Ocurrio un error al cargar el archivo";
            }
        }
    }

    private function getName($n = 10)
    {
        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $cadena = "";
        for ($i = 0; $i < $n; $i++) {
            $cadena .= substr($caracteres, rand(0, strlen($caracteres)), 1);
        }
        return $cadena;
    }

    private function download()
    {
        $ruta = '';
        $fichero = '';
        $script = '';
        $params = [];
        if (!isset($this->prm->x)) {
            $this->mixScript('notify("Ocurrio un error al obtener el archivo- El archivo no existe","error");');
            return false;
        } else {
            $sql = '';
            if (isset($this->prm->t)) {
                array_push($params, $this->prm->x);
                $sql = '
                    SELECT
                        dur_id, dur_ejercicio, dur_ruta, dur_nombreGuardado, dur_nombreOriginal
                    FROM ' . BD_SINPRES . '.cat_DocumentosUr 
                    WHERE dur_nombreGuardado = ? ;
                ';
            } else {
                array_push($params, $this->prm->x);
                $sql = '
                    SELECT 
                        dur_id, dur_ejercicio, dur_ruta, dur_nombreGuardado, dur_nombreOriginal
                    FROM ' . BD_SINPRES . '.cat_DocumentosUr 
                    WHERE  dur_id = ? ;
                ';
            }
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $data = $this->extraer();
            $this->desconectar();
            if ($this->getNumeroRegistros() < 1) {
                $this->mixScript('notify("Ocurrio un error al obtener el archivo","error");');
                return false;
            }
            $fichero = $this->prm->x;
            $r = new RutaArchivoSefip();
            $r->setModulo("IP");
            $r->setAnioFichero(APP_EJERCICIO);
            $ruta = $r->getRutaMod();
            $ruta .= $fichero;
            $dest = __DIR__ . "/../../tmps/" . $fichero;
            copy($ruta, $dest);
            if ($data['dur_ejercicio'] == APP_EJERCICIO) {
                if (!(file_exists($ruta))) {
                    $this->mixScript('notify("Ocurrio un error al obtener el archivo- El archivo no existe","error");');
                    return false;
                } else {
                    $script = ('
                        load_viewer("' . $data['dur_id'] . '","' . $data['dur_nombreGuardado'] . '","frame");
                        $("#det_fs' . ($data['dur_id']) . '").attr("src","lib/file_download.php?r=' . $fichero . '");
                        
                    ');
                    $this->mixScript($script);
                }
            }
        }
    }

    private function subtipoGarantia()
    {
        $params = [$this->prm->tga_id];
        $sql = 'SELECT stg_id,stg_nombre,tga_id FROM ' . BD_SINPRES . '.cat_SubtipoGarantia WHERE tga_id = ?;';
        $combo = new stdclass();
        $combo->id = "stg_id";
        $combo->sql = $sql;
        $combo->params = $params;
        $combo->comoAtr = "stg_id,tga_id";
        $this->recargaCombo($combo);
    }

    private function tipoAnexo()
    {
        $where = '';
        switch ($this->prm->tga_id) {
            case "A":
                switch ($this->prm->stg_id) {
                    case 1:
                        $where = '1,2';
                        break;
                    case 2:
                        $where = '3';
                        break;
                    case 3:
                        $where = '4';
                        break;
                }
                break;
            case "C":
                switch ($this->prm->stg_id) {
                    case 1:
                        $where = '1,2';
                        break;
                    case 2:
                        $where = '3';
                        break;
                    case 3:
                        $where = '4';
                        break;
                    case 4:
                        $where = '5';
                        break;
                }
                break;
            case "V":
                switch ($this->prm->stg_id) {
                    case 1:
                        $where = '1,2';
                        break;
                    case 2:
                        $where = '3';
                        break;
                    case 3:
                        $where = '4';
                        break;
                }
                break;
        }

        $combo = new stdclass();
        $sql = 'SELECT tan_id,tan_nombre AS taname FROM ' . BD_SINPRES . '.cat_TipoAnexo WHERE tan_id IN(' . $where . ')';
        $combo->id = "tan_id";
        $combo->sql = $sql;
        $combo->comoAtr = 'tan_id,taname';
        $this->recargaCombo($combo);
    }

    private function pdfs()
    {
        $html = '';
        $html .= '<legend>Listado de Ficheros PDF que la UR ha guardado en el SISTEMA</legend>';

        $tabla = new stdClass();
        $tabla->id = "listadopdf";
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = "llenaPdfs";
        $tabla->comunica->idtabla = "listadopdf";
        $tabla->comunica->nombre = '{$("#nombrepdf").val()}';
        $tabla->comunica->dur_id = '{$("#dur_id").val()}';

        $tools = '
            <div class="row col-md-12 col-xs-12 mb-2">
                <div class="form-group col-md-3 col-xs-3">
                    <label class="control-label">Nombre de Archivo </label>
                    <input type="text" id="nombrepdf" class="form-control"/>
                </div>
                <div class="form-group col-md-3 col-xs-3">
                    <label class="control-label">Id de Archivo</label>
                    <input type="text" id="dur_id" class="form-control"/>
                </div>
                <div class="form-group col-md-1 col-xs-1">
                    <input class="btn btn-outline-secondary mt-3" type="button" id="btnBuscar" value="Buscar" />
                </div>
            </div>
        ';

        $tabla->tools = $tools;
        $tabla->filasAmostrar = array(50, 100, 150, 200, 300, 500);
        $tr = array();

        $th = new stdClass();
        $th->texto = "Borrar";
        $th->width = "3%";
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "Ver";
        $th->width = "3%";
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "ID";
        $th->width = "3%";
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "Nombre";
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "Identificador";
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "Estado";
        $th->align = "left";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "Tipo";
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "Ejercicio";
        $th->align = "center";
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = "BORRAR";
        $td->casos->valor = "SI";
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = "boton";
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->dur_id = "ID";
        $td->casos->verdadero->attr->names = "Nombre";
        $td->casos->verdadero->class = "delfile btn-xs";
        $td->casos->verdadero->icono = "remove";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->tipo = "link";
        $td->campoTxt = 'verdocumento';
        $td->align = "center";
        $td->attr = new stdClass();
        $td->attr->id = "IDENTIFICADOR";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ID';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Nombre';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'IDENTIFICADOR';
        $td->align = "center";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Estado';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'dur_tipo';
        $td->align = "center";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'dur_ejercicio';
        $td->align = "center";
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $html .= $this->wrPonTabla($tabla);

        $botones = '';

        $btnregresa = new stdClass();
        $btnregresa->id = "comeback";
        $btnregresa->value = "Regresar";
        $btnregresa->icon = "solid fa-circle-left me-2";
        $btnregresa->color = "gris";
        $btnregresa->class = "me-1";
        $botones .= $this->ponBoton($btnregresa);

        $btndocto = new stdClass();
        $btndocto->id = "agregadocto";
        $btndocto->value = "Agregar Archivos";
        $btndocto->icon = "solid fa-plus me-2";
        $btndocto->color = "gris";
        $btndocto->class = "me-1";
        $botones .= $this->ponBoton($btndocto);

        $this->mixHtml($html);
        $this->agregaTools($botones);

        $script1 = '';
        $script2 = '';
         if (isset($_SESSION[_S]['banderaPagina'])) {
            $this->prm->bandera = $_SESSION[_S]['banderaPagina'];
        } else if (isset($this->prm->bandera)) {
            $this->prm->bandera = $this->prm->bandera;
        } else {
            $this->prm->bandera = 0;
        }


        if (isset($_SESSION[_S]['banderaPagina'])) {
            $script1 = '
                comunica("' . $this->pantalla . '",{
                    metodo:"agrega",
                    origen:"' . $this->prm->metodo . '", 
                    t:"",
                    metodoOrigen:"' . $this->prm->metodo . '",
                    garantia:"true",
                    bandera:' . $this->prm->bandera . '
                });
            ';
            $script2 = '
                $("#comeback").click(function() {
                    comunica("scp/afectaciones/ControladorRegistroObra.php",
                    {
                        metodo:"Contratos", 
                        tarea:"' . $_SESSION[_S]['banderaPagina'] . '"
                    });
                });
            ';
        } else {
            $script1 = '
                comunica("' . $this->pantalla . '",
                    {
                        metodo:"agrega",
                        origen:"' . $this->prm->metodo . '", 
                        t:"",
                        metodoOrigen:"' . $this->prm->metodo . '",
                        garantia:"true",
                         bandera:' . $this->prm->bandera . '
                    }
                );
            ';
            $script2 = '
                $("#comeback").click(function() {
                    comunica("' . $this->pantalla . '",
                    {
                        metodo:"main",
                        bandera : ' . $this->prm->bandera .'
                    });
                });
            ';
        }

        $this->mixScript('

            $("#nombrepdf").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8 || keynum==32) {
                    return true;
                }else if (keynum==95) {
                    return false;
                }else if (keynum==45) {
                    return true;
                }else if (keynum==47) {
                    return true;
                }else if (keynum==241 || keynum==209) {
                    return true;
                }else if (keynum == 46) {
                    var nompdf=$("#nombrepdf").val();
                    if (nompdf.indexOf(".") != -1) {
                        return false;
                    }else{
                        return true;
                    }
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                }else{
                    return false;
                }
            });

            $("#dur_id").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                } else if (keynum==95) {
                    return false;
                } else if ((keynum>=65 && keynum<=90) || (keynum>=97 && keynum<=122)) {
                    return false;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            ' . $script2 . '

            $("#agregadocto").click(function() {
                ' . $script1 . '
            });

            //verdpf corresponde al sistema del 2017,ahora  todo este proceso se hace en download 
            function verpdf(n,t) {
                $("#verpdf").remove();
                if (t=="d")
                    $("body").append("<iframe style=\"display:none\" id=\"verpdf\" src=\"scp/afectaciones/download_file.php?t=d&file="+ n +"\"></iframe>")
                else {
                    n = $("<iframe/>").attr("src","scp/afectaciones/download_file.php?t=v&file="+ n).css("width","96%").css("height","100%");
                    c = $("<span/>").addClass("glyphicon glyphicon-remove-circle").css("float","right").css("font-size","30px").css("cursor","pointer").click(function() { $("#verpdf").remove(); });
                    m = $("<div/>").addClass("documento_fs").css("color","#FFF").attr("id","verpdf").append(c).append(n).appendTo("body");
                }
            }
        ');
        $this->llenaPdfs();
    }

    private function llenaPdfs()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $params = [$gpo_id, (int)$ur_id, APP_EJERCICIO];
        $sql = '
            SELECT
                if (bga_id IS NULL AND ipu_id IS NULL,"SI","NO")AS BORRAR,dur_ejercicio,
                dur_id AS ID,dur_nombreOriginal AS Nombre,dur_tipo,
                dur_nombreGuardado AS IDENTIFICADOR,"Descargar" AS texto,
                "Ver" AS verdocumento,
                if (bga_id IS NULL AND ipu_id IS NOT NULL,CONCAT("CONTRATO ",ipu_numeroContrato),if (bga_id IS NOT NULL AND ipu_id IS NULL,CONCAT("GARANTIA ",bga_id),"SIN USO"))AS Estado
            FROM ' . BD_SINPRES . '.cat_DocumentosUr
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
            LEFT JOIN  ' . BD_SINPRES . '.enc_GarantiaDocumento USING (dur_id)
            LEFT JOIN  ' . BD_SINPRES . '.enc_InversionObras USING (vur_id,dur_id)
            LEFT JOIN  ' . BD_SINPRES . '.cat_InversionPublica USING (ipu_id)
            WHERE uur_id IN(
                SELECT
                    uur_id FROM ' . BD_SINPRES . '.vin_UnidadResponsable
                WHERE gpo_id = ? AND ur_id = ? AND vur_ejercicio = ? 
            )
        ';

        if (isset($this->prm->nombre) && $this->prm->nombre != "") {
            array_push($params, '%' . $this->prm->nombre . '%');
            $sql .= ' AND dur_nombreOriginal LIKE ?';
        }

        if (isset($this->prm->dur_id) && $this->prm->dur_id != "") {
            array_push($params, $this->prm->dur_id);
            $sql .= ' AND dur_id = ? ';
        }

        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'listadopdf';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript("notify('No hay registros','warning');");
        } else {
            $this->mixScript('
                $("#btnBuscar").click(function(event) { ' . $this->wrLlamaLlenaTabla('listadopdf') . ' });
                $(".delfile").click(function() {
                    dur_id = $(this).attr("dur_id");
                    n = $(this).attr("names");
                    alertify.confirm(
                        "ELIMINAR PDF",
                        "Confirme la eliminación del documento "  + n,
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"delfile",
                                fid:dur_id}
                            );
                        },
                        function(){
                        }
                    ).set("defaultFocus", "cancel").set("labels",{ok:"Aceptar",cancel:"Cancelar"});
                });

                $(".linkDeTabla").click(function() {
                    x= $(this).attr("id");
                    alertify.confirm(
                        "ELEGIR EL MODO",
                        "DESEA DESCARGAR O VER EL FICHERO",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"download",
                                x:x,
                                t:"d"
                            })
                        },
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"download",
                                x:x,
                                t:"v"
                            })
                        }
                    ).set("defaultFocus").set("labels",{ok: "<i class=\'fa fa-file-pdf me-1\'></i> Descargar",cancel:"Ver"});
                    setTimeout(() => {
                        $(".btn-success")
                            .removeClass("btn-success")
                            .addClass("btn btn-outline-danger");
                        $(".btn-danger")
                            .removeClass("btn-danger")
                            .addClass("btn btn-outline-success");
                    }, 10);
                });
            ');
        }
    }

    private function delfile()
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        if (!isset($this->prm->fid) || $this->prm->fid == "") {
            $this->mixScript('notify("Error al borrar el archivo","error");');
            return;
        }

        $params = [$this->prm->fid, $gpo_id, (int)$ur_id];
        $propio = '
            SELECT COUNT(dur_id)AS Num
            FROM ' . BD_SINPRES . '.cat_DocumentosUr
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
            WHERE dur_id = ? AND uur_id  IN(
                SELECT uur_id FROM ' . BD_SINPRES . '.vin_UnidadResponsable
                WHERE gpo_id= ? AND ur_id = ?
            )';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($propio);
        $con->consultar($params);
        $data = $con->extraer();
        if ($data['Num'] == 0) {
            $this->mixScript('notify("Error al borrar el archivo, no es de la UR","error");');
            return;
        }
        $params = [$this->prm->fid];
        $uso = '
            SELECT if (ipu_id IS NOT NULL OR bga_id IS NOT NULL,"NO","SI") AS Borrar,dur_ejercicio,
                bga_id,ipu_numeroContrato,dur_nombreOriginal,dur_ruta, dur_nombreGuardado
            FROM ' . BD_SINPRES . '.cat_DocumentosUr
            LEFT JOIN ' . BD_SINPRES . '.enc_GarantiaDocumento USING(dur_id)
            LEFT JOIN ' . BD_SINPRES . '.enc_InversionObras 	USING(dur_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_InversionPublica 	USING(ipu_id)
            WHERE dur_id = ? ;';
        $con->setSql($uso);
        $con->consultar($params);
        $data = $con->extraer();
        $con->desconectar();

        $r = new RutaArchivoSefip();
        $r->setModulo("IP");
        $r->setAnioFichero(APP_EJERCICIO);
        $rutaFichero = $r->getRutaMod();
        $rutaFichero .= $data['dur_nombreGuardado'];
        if ($data['Borrar'] == "SI") {
            $params = [$this->prm->fid];
            $sql = 'DELETE FROM ' . BD_SINPRES . '.cat_DocumentosUr WHERE dur_id = ?;';
            $con->conectar();
            $con->setSql($sql);
            $con->conectar();
            $con->consultar($params);
            $ruta = $rutaFichero;
            unlink($ruta);
        } else {
            $this->mixScript('notify("Error el fichero \"' . $data['dur_nombreOriginal'] . '\" esta en uso","error");');
            $con->desconectar();
            unset($con);
            return;
        }
        $this->mixScript('notify("El fichero \"' . $data['dur_nombreOriginal'] . '\" ha sido eliminado del sistema","success");');
        $con->desconectar();
        unset($con);
        $this->llenaPdfs();
    }
}


if (isset($_SESSION[_S]['prm']['S']['2']['18']['74'])) {
    new ControladorGarantias();
} else {
    WebResponse::sinPermisos();
}
