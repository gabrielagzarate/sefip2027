<?php

require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../lib/FicheroSefip.php';

class ControladorDocumentos extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'srv/ControladorDocumentos.php';
        switch ($this->metodo) {
            case 'main':
                $this->main();
                break;
            case 'muestraCatalogo':
                $this->muestraCatalogo();
                break;
            case 'llenaTablaDocNormativos':
                $this->llenaTablaDocNormativos();
                break;
            case 'documentosN':
                $this->documentosN();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->titulo('Documentos normativos y catálogos');
        $this->mixHtml('
            <div id="page-tabs">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item"><a id="catalogo" class="nav-link" role="tab" data-bs-toggle="tab">CATÁLOGOS</a></li>
                </ul>
            </div>
        ');
        $this->mixScript('
            $("#catalogo").click(function(){
                comunica("srv/ControladorDocumentos.php",{
                    metodo : "documentosN"
                });
                return false;
            });
        ');
    }

    private function documentosN()
    {

        $this->mixHtml('
            <div id="page-tabs">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item"><a id="catalogo" class="nav-link active" role="tab" data-bs-toggle="tab">CATÁLOGOS</a></li>
                </ul>
            </div>
        ');

        $tabla = new stdClass();

        $tabla->id = 'tbl_docnormativos';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDocNormativos';
        $tabla->numFilas = 150;
        $tabla->filasAmostrar = [20,50,100,150,300,400,500];
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Ver';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Título de Catálogo';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'eye';
        $td->class = 'btn-xs';

        $td->attr = new stdClass();
        $td->attr->dn_id = 'dn_id';
        $td->class = 'verCatalogo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'dn_tituloCatalogo';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaDocNormativos();

        $this->mixScript('
            $(".verCatalogo").click(function(){
                comunica("srv/ControladorDocumentos.php",{
                    metodo : "muestraCatalogo",
                    dn_id : $(this).attr("dn_id")
                });
                return false;
            });
            $("#catalogo").click(function(){
                comunica("srv/ControladorDocumentos.php",{
                    metodo : "documentosN"
                });
                return false;
            });
        ');
    }

    private function llenaTablaDocNormativos()
    {

        $this->conectar();
        $sql = '
            SELECT dn_id, dn_tituloCatalogo, dn_descripcion
            FROM cat_DocumentosNormativos
            WHERE sts_id = 1';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_docnormativos';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros ","warning");');
        }

    }

    private function muestraCatalogo()
    {
        $ruta = '';
        $script = '';

        if (!isset($this->prm->dn_id)) {
            $this->mixScript('notify("No existen registros","error");');
            return false;
        } else {
            $sql = '';
            $sql = '
                SELECT
                    dn_id,ur_id, dn_nombreGuardado, dn_nombreOriginal, dn_ruta, dn_ejercicio
                FROM cat_DocumentosNormativos 
                WHERE dn_id = ?;
            ';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->prm->dn_id]);
            $data = $this->extraer();
            $this->desconectar();
            if ($this->getNumeroRegistros() < 1) {
                $this->mixScript('notify("ocurrio un error al obtener el archivo","error");');
                return false;
            }

            $archi = $data['dn_nombreGuardado'];
            $base = 'DocumentosNormativos/Catalogos';
            $ur = $data['ur_id'];

            $r = new RutaArchivoSefip();
            $r->setModulo('MGA');
            $r->setPath($base);
            $r->setAnioFichero(APP_EJERCICIO);
            $r->setUrFichero($ur);
            $ruta = $r->getRutaMod();
            $ruta .= $archi;

            $dest = realpath(__DIR__ . '/../tmps/');
            if (file_exists($dest)) {

                copy($ruta, $dest . '/' . $archi);

                if ($data['dn_ejercicio'] == APP_EJERCICIO) {
                    if (!(file_exists($ruta))) {
                        $this->mixScript('notify("Ocurrio un error al obtener el archivo- El archivo no existe","error");');
                        return false;
                    } else {
                        $script = ('
                            function ver_archivo(){
                                var form = $("<form >")
                                .attr("method", "get")
                                .attr("action","lib/file_download.php")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "r").attr("value", "' . $archi . '" ))
                                $("body").append(form);
                                form.submit();
                                form.remove();
                            }
                            $(function(){ver_archivo();});
                        ');
                        $this->mixScript($script);
                    }
                }
            } else {
                $rutatemp = __DIR__ . '/../tmps/';
                mkdir($rutatemp);
                $this->muestraCatalogo();
            }
        }
    }
}
new ControladorDocumentos();
