<?php
ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';
require_once dirname(__FILE__) . '/../../ent/AuxiliarUnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorRegistroNotasEdoFin extends WebResponse
{
    private $gpo_id;
    private $ur_id;
    private $linkVideoTutorial;
    public $html;
    public $r = [];

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        if (isset($this->prm->opcion)) {
            $_SESSION[_S]['reportes'] = $this->prm->opcion->sis_id . $this->prm->opcion->men_id . $this->prm->opcion->opc_id;
        }

        // Tomamos los datos de ClasAdm para que reconozca las cargas de trabajo
        $this->gpo_id = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : false;
        $this->ur_id = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : false;
        $this->linkVideoTutorial = 'https://drive.google.com/file/d/1HU5jZfgYeOlDxKdQgUQNBwuzeNR2Gc5C/preview';

        switch ($this->metodo) {
            case 'upload':
                $this->upload_file_update_notas();
                ob_flush();
                $this->main();
                break;
            case 'mostrar':
                $this->mostrar_notas();
                break;
            case 'descargar':
                $archivo = $this->prepara_formato_base();
                $this->descarga_formato_base($archivo);
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $sql = '
            SELECT mc.msc_id AS mes, mc.ems_id, m.msc_nombre, n.archivo_notas, n.fechaSubida, u.usr_usuario AS usrSubio
            FROM cat_MesContable m
                LEFT JOIN cg_MesContable AS mc USING(msc_id)
                LEFT JOIN cg_NotasEstadosFinancieros AS n USING(gpo_id, ur_id, msc_id)
                LEFT JOIN cat_Usuario AS u ON (n.usr_id = u.usr_id)
            WHERE (mc.gpo_id = ? AND mc.ur_id = ? OR ISNULL(mc.msc_id)) AND m.msc_id BETWEEN 1 AND 12
            ORDER BY m.msc_id;
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
        $this->desconectar();

        $html = '
            <table class="table table-striped table-bordered" id="tabla-TabProv">
                <thead>
                    <tr role="row" class="warning">
                        <th role="columnheader" class="warning" style="text-align:center;">STATUS</th>
                        <th role="columnheader" class="warning">AL MES DE:</th>
                        <th role="columnheader" class="warning">ARCHIVO DE NOTAS
                            <font color="red">(PDF)</font></th>
                        <th role="columnheader" class="warning">OPCIONES</th>
                        <th role="columnheader" class="warning">FECHA SUBIDA</th>
                        <th role="columnheader" class="warning">SUBIÓ</th>
                    </tr>
                </thead>
                <tbody id="tbody-tabla-TabProv" role="alert">
        ';

        while ($r = $this->extraer()) {
            $html .= $this->dibujaLinea($r);
        }

        $html .= '
                </tbody>
            </table>
        ';

        $cabeceraUploadModal = ($_SESSION[_S]['gpo_id'] == 1 && $_SESSION[_S]['ur_id'] == 15) ? 'ADVERTENCIA' : 'SUBIR NOTAS A LOS ESTADOS FINANCIEROS';
        $contenidoUploadModal = ($_SESSION[_S]['gpo_id'] == 1 && $_SESSION[_S]['ur_id'] == 15) ? 'Tu usuario no está autorizado para subir Notas a los Estados Financieros.' : 'Una vez que hayas cargado en el sistema éste archivo, <font color="red"><strong>"NO PODRÁS VOLVER A SUBIRLO"</strong></font>, debido a que sólo se suben los archivos definitivos, previamente revisados y validados, además que el proceso cierra automáticamente la posibilidad de volverlos a subir para el mes que hayas seleccionado';

        $html .= '
            <div class="modal fade" id="uploadModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body" style="border: 5px solid red;' . ($_SESSION[_S]['gpo_id'] == 1 && $_SESSION[_S]['ur_id'] == 15 ? ' border-radius: 10px;' : '') . '">
                            <h4 class="modal-title"><center><span style="background: linear-gradient(to bottom, brown, orange); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><strong>' . $cabeceraUploadModal . '<strong></span></center></h4>
                            <br>
                            <h5 style="text-align: justify;">' . $contenidoUploadModal . '</h5>';

        if ($_SESSION[_S]['gpo_id'] == 1 && $_SESSION[_S]['ur_id'] == 15) {
            $html .= '
                            <div style="text-align: right;">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ';
        } else {
            $html .= '
                            <h6><center>¿ Confirmas que deseas subir el archivo ?</center></h6>
                            <div style="text-align: right;">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar Operación</button>
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal" id="botoncitoCargarNotas">SUBIR NOTAS DEFINITIVAS</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ';
        }

        $html .= '</div>';


        $botones = '<a id="descargar" data-placement="bottom" title="Descargar formato base para las Notas a los Estados Financieros" role="button" type="button" class="btn btn btn-outline-default btn-outline-primary me-1"><i class="fa fa-download me-2"></i>Descargar Formato Word</a>';

        $botones .= str_repeat('&nbsp;', 30);

        $botones2 = str_repeat('&nbsp;', 30) . '<a id="ayuda" data-placement="bottom" title="Visualizar el videotutorial de uso" role="button" type="button" class="btn btn btn-outline-default btn-outline-primary me-1"><i class="fas fa-chalkboard-teacher"></i>    Tutorial_Notas</a>';

        $this->agregaTools($botones . '<h3>Notas a los Estados Financieros</h3>' . $botones2);
        $this->mixHtml($html);

        $this->mixScript('
            $("#descargar").click(function() {
                comunica("' . $this->pantalla . '", { metodo:"descargar" }, false, true);                                
            });

            $("#ayuda").click(function() {
                muestraAyuda("ayuda","' . $this->linkVideoTutorial . '");
            });

            $(".launch_upload").click(function() {
                let vinculo = $(this).attr("vinculado_a");
                $("#botoncitoCargarNotas").attr("onclick", "$(" + vinculo + ").click();");
                $("#uploadModal").modal("show");
            });

            $(".upload_archivo_notas").click(function() {
                $("#progress' . isset($r['mes']) . ' .progress-bar").css("width","0%").attr("aria-valuenow",0);
            });

            $(".upload_archivo_notas").fileupload({
                dataType:"json",
                done: function(e, data) {
                    comunica("' . $this->pantalla . '");
                    let validacion = data.result.validacion;
                    notify(validacion.mensaje);
                },
                progress: function(e, data) {
                    let progress = parseInt(data.loaded / data.total * 100, 10);
                    $("#progress' . isset($r['mes']) . ' .progress-bar").css("width", progress + "%").attr("aria-valuenow",progress);
                }
            });

            $(".mostrar_notas").click(function() {
                let mes = $(this).attr("msc");
                load_viewer("vis1", "NOTAS A LOS ESTADOS FINANCIEROS DE " + $(this).attr("nm"),"F");
                comunica("' . $this->pantalla . '", { metodo: "mostrar", m: mes }, false, true);
            });

            $(".descargar_cuadros").click(function() {
                let mes = $(this).attr("msc");
                alertify.confirm("DESCARGAR CUADROS DE APOYO", "El uso de los cuadros de apoyo es opcional.   La UR deberá analizar la información y en base a su criterio decidir si los usa o los elabora manualmente.",
                    function(){
                        var form = $("<form>")
                        .attr("action", "rep/doc/scg/rep_NotasCuadrosApoyo.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "mes").attr("value", mes ));
                    $("body").append(form);
                    form.submit();
                    },
                    function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});                
            });
        ');
    }

    private function dibujaLinea(&$r)
    {
        $html = '
            <tr class="odd">
                <td class="sorting_1" style="text-align:center;">';

        if ($r['ems_id'] == 'A') {
            $html .= '<button type="button" class="btn btn-outline-success" title="Mes Abierto"><i class="fa fa-unlock"></i></button>';
        } elseif ($r['ems_id'] == 'C') {
            $html .= '<button type="button" class="btn btn-outline-danger" title="Mes Cerrado"><i class="fa fa-lock"></i></button>';
        } else {
            $html .= '<button type="button" class="btn btn-outline-secondary" title="Mes Cerrado"><i class="fa fa-lock"></i></button>';
        }

        $html .= '
                </td>
                <td class="sorting_1">
                    <label style="text-align:left; width:100%;">' . $r['msc_nombre'] . '</label>
                </td>
            ';

        $html .= '<td class="sorting_1">
            ' . $this->ponForma_ArchivoNotas($r) . '
            </td>
            <td class="sorting_1">
            ' . $this->ponOpciones($r) . '
            </td>';

        if ($r['archivo_notas'] == '') {
            $html .= '<td class="sorting_1">
                    &nbsp;
                </td>
                <td class="sorting_1">
                    &nbsp;
                </td>';
        } else {
            $html .= '<td class="sorting_1">
                    <label style="text-align:left; width:100%;">
                        ' . $r['fechaSubida'] . '
                    </label>
                </td>
                <td class="sorting_1">
                    ' . $r['usrSubio'] . '
                </td>';
        }

        $html .= '</tr>';

        return $html;
    }

    private function ponForma_ArchivoNotas(&$r)
    {
        if (trim($r['archivo_notas'] ?? '') == '') {
            // Si el mes está CERRADO, y NO han cargado el archivo
            if ($r['ems_id'] == 'C') {
                // Al subir archivos, forzamos a que sólo se muestren pdfs
                $html = '
                    <div class="controls">
                        <button vinculado_a="archivo_notas_' . $r['mes'] . '" class="btn btn btn-outline-default btn-outline-primary launch_upload" title="El archivo contendrá las notas revisadas, y se tomarán como definitivas">Subir Archivo PDF</button>
                        <input hidden type="file" id="archivo_notas_' . $r['mes'] . '" name="archivo_notas_' . $r['mes'] . '" accept="application/pdf" class="upload_archivo_notas"
                        data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","mes":"' . $r['mes'] . '"}\'>
                    </div>
                    <div id="progress' . $r['mes'] . '" class="progress progress-striped">
                        <div class="progress-bar" role="progressbar"
                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                    ';
            } else {
                $html = $r['archivo_notas'];
            }
        } else {
            $html = $r['archivo_notas'];
        }
        return $html;
    }

    private function ponOpciones($r)
    {
        $html = '';
        // Si el mes está abierto
        if ($r['ems_id'] == 'C') {
            // Sólo pueden descargar los cuadros de apoyo si aún no suben el archivo
            if (trim($r['archivo_notas'] ?? '') == '') {
                $html = '<button type="button" nm="' . $r['msc_nombre'] . '" msc="' . $r['mes'] . '" class="btn btn-outline-default btn-outline-primary me-2 descargar_cuadros" title="Descargar cuadros con datos para apoyo"><i class="fa fa-download me-1"></i>Cuadros de apoyo Excel</button>';
            }
            // Sólo pueden ver y borrar si ya subieron algo
            if (trim($r['archivo_notas'] ?? '') != '') {
                $html .= '<button type="button" nm="' . $r['msc_nombre'] . '" msc="' . $r['mes'] . '" class="btn btn-outline-default btn-outline-info me-2 mostrar_notas" title="Mostrar Notas"><i class="fa fa-eye me-1"></i></button>';
            }
        } else {
            // Si el mes está abierto, pero ya lo cargaron... sólo pueden verlo
            if (trim($r['archivo_notas'] ?? '') != '') {
                $html = '<button type="button" nm="' . $r['msc_nombre'] . '" msc="' . $r['mes'] . '" class="btn btn-outline-default btn-outline-info me-2 mostrar_notas" title="Mostrar Notas"><i class="fa fa-eye me-1"></i></button>';
            }
        }

        return $html;
    }

    // Subimos el archivo PDF de las notas del mes
    private function upload_file_update_notas()
    {
        if ($_SESSION[_S]['gpo_id'] == 1 && $_SESSION[_S]['ur_id'] == 15) {
            $this->setStatus('error');
            $respuesta = new stdClass();
            $respuesta->mensaje = 'Tu usuario no está autorizado para subir Notas a los Estados Financieros.';
            $respuesta->nombre = '';
            $this->setValidacion($respuesta);
            return false;
        }

        $ur_clave = $this->gpo_id . $this->ur_id;
        $id_campo = 'archivo_notas_' . $this->prm->mes;

        //Verificamos que no se encuentre cargado otro archivo para el mismo mes.
        $this->conectar();
        $this->setSql(
            'SELECT archivo_notas
            FROM cg_NotasEstadosFinancieros
            WHERE gpo_id = ? AND ur_id = ? AND msc_id = ?;'
        );
        $this->consultar([$this->gpo_id,$this->ur_id,$this->prm->mes]);
        $this->desconectar();

        if ($this->extraer()) {
            $this->setStatus('error');
            $respuesta = new stdClass();
            $respuesta->mensaje = 'Ya se encuentra cargado un archivo para el mes seleccionado.';
            $respuesta->nombre = '';
            $this->setValidacion($respuesta);
            return false;
        }

        $up = new FicheroSefip();
        $up->setTipos(['pdf']);
        $up->setModulo('scg');
        $up->setfile('notas_' . $ur_clave . '_' . $this->prm->mes . '.pdf');
        $up->setRuta('notas');
        $up->setUrFichero($ur_clave);
        $up->setAnioFichero($_SESSION[_S]['ejercicioActual']);

        if (isset($_FILES[$id_campo])) {
            $respuesta = new stdClass();

            $up->setFichero($_FILES[$id_campo]);

            if ($up->upload()) {
                // Actualizamos el nombre del archivo de esa UR y mes
                $this->conectar();
                $this->setSql(
                    'INSERT INTO cg_NotasEstadosFinancieros( gpo_id, ur_id, msc_id, archivo_notas, fechaSubida, usr_id ) 
                    VALUES( ?, ?, ?, "' . $up->getFile() . '", NOW(), ' . $_SESSION[_S]['usr_id'] . ' )
                    ON DUPLICATE KEY UPDATE
                        archivo_notas = "' . $up->getFile() . '", 
                        fechaSubida = NOW(), 
                        usr_id = ' . $_SESSION[_S]['usr_id'] . ';'
                );

                $this->consultar([$this->gpo_id,$this->ur_id,$this->prm->mes]);
                $this->desconectar();

                // Avisamos de carga exitosa
                $this->setStatus('success');
                $respuesta->mensaje = 'Archivo cargado con éxito';
                $respuesta->nombre = $up->getFile();
            } else {
                $this->setStatus('error');
                $respuesta->mensaje = $up->getMensaje();
                $respuesta->nombre = '';
            }
        }

        // Si quitamos ésto... regresa un undefined la notificación
        $this->setValidacion($respuesta);
    }

    private function mostrar_notas()
    {
        $fichero = '';
        $base = '';

        $mes = $this->prm->m;
        $link = 'notas/' . 'notas_' . $this->gpo_id . $this->ur_id . '_' . $mes . '.pdf';

        $tmp = explode('/', $link);
        $base = $tmp[0];
        $fichero = explode('?', $tmp[1])[0];

        $r = new RutaArchivoSefip();
        $r->setBase('scg');
        $r->setPath($base);
        $r->setAnioFichero($_SESSION[_S]['ejercicioActual']);
        $r->setUrFichero($this->gpo_id . $this->ur_id);

        $ruta = $r->getRutaMod();
        $ruta .= $fichero;

        $dest = __DIR__ . '/../../tmps/' . $fichero;

        copy($ruta, $dest);

        $this->mixScript(' $("#det_fsvis1").attr("src","lib/file_download.php?r=' . $fichero . '");');
    }

    private function eliminar()
    {
        $fichero = '';
        $base = '';

        $mes = $this->prm->m;
        $link = 'notas/' . 'notas_' . $this->gpo_id . $this->ur_id . '_' . $mes . '.pdf';

        $tmp = explode('/', $link);
        $base = $tmp[0];
        $fichero = explode('?', $tmp[1])[0];

        $r = new RutaArchivoSefip();
        $r->setBase('scg');
        $r->setPath($base);
        $r->setAnioFichero($_SESSION[_S]['ejercicioActual']);
        $r->setUrFichero($this->gpo_id . $this->ur_id);

        $ruta = $r->getRutaMod();
        $ruta .= $fichero;

        if (unlink($ruta)) {
            // desvinculamos el nombre del archivo de esa UR y mes
            $this->conectar();
            $this->setSql(
                'UPDATE cg_NotasEstadosFinancieros
                SET archivo_notas = "", 
                    fechaSubida = null
                WHERE 
                    gpo_id = ? AND ur_id = ?
                    AND msc_id = ?;'
            );

            $this->consultar([$this->gpo_id, $this->ur_id,$mes]);
            $this->desconectar();

            $this->mixScript('notify("Se ha eliminado el archivo de notas del mes solicitado", "success");');
        } else {
            $this->mixScript('notify("Ha ocurrido un error al intentar borrar el archivo", "error");');
        }
    }

    // Al archivo de word que es la base... le ponemos el nombre de la Entidad y las firmas
    private function prepara_formato_base()
    {
        $folder = __DIR__ . '/formato';

        $ur_firmas = new AuxiliarUnidadResponsable();
        $ur_firmas->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $ur_firmas->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $ur_firmas->select();

        $ur = new UnidadResponsable();
        $ur->setGpo_id($this->gpo_id);
        $ur->setUr_id($this->ur_id);
        $ur->select();

        $nombre_ente = utf8Encode($ur->getClave() . ' - ' . $ur->getNombre());

        $firmantes = [
            ['nombre' => $ur_firmas->getNombreFinancieros(), 'cargo' => $ur_firmas->getCargoFinancieros()],
            ['nombre' => $ur_firmas->getNombreAdministrativo(), 'cargo' => $ur_firmas->getCargoAdministrativo()],
            ['nombre' => $ur_firmas->getNombreTitular(), 'cargo' => $ur_firmas->getCargoTitular()],
        ];

        $firmantes = array_filter($firmantes, function ($firmante) {
            return $firmante['nombre'] !== null && $firmante['nombre'] !== '' && $firmante['cargo'] !== null && $firmante['cargo'] !== '';
        });

        $template_file_name = $folder . '/plantilla_' . count($firmantes) . '_firmantes.docx';

        $rand_no = rand(111111, 999999);
        $fileName = 'base_notas_estados_financieros_' . $rand_no . '.docx';

        $folder = __DIR__ . '/../../tmps';
        $full_path_file_tmp = $folder . '/' . $fileName;

        try {
            //Copy the Template file to the Result Directory
            copy($template_file_name, $full_path_file_tmp);

            // add calss Zip Archive
            $zip_val = new ZipArchive();

            // El tipo de archivo Docx es simplemente un zip.
            // Lo abrimos como cualquier otro archivo zip
            if ($zip_val->open($full_path_file_tmp) == true) {
                // In the Open XML Wordprocessing format content is stored.
                // In the document.xml file located in the word directory.
                $key_file_name = 'word/document.xml';
                $message = $zip_val->getFromName($key_file_name);

                // this data Replace the placeholders with actual values
                $message = str_replace('FIELD_DEPENDENCIA_FULL_NAME', $nombre_ente, $message);
                $auxContador = 1;
                foreach ($firmantes as $firmante) {
                    $message = str_replace('FIELD_FIRMA' . $auxContador, $firmante['nombre'], $message);
                    $message = str_replace('FIELD_CARGO' . $auxContador, $firmante['cargo'], $message);
                    $auxContador++;
                }

                //Replace the content with the new content created above.
                $zip_val->addFromString($key_file_name, $message);
                $zip_val->close();

                return $fileName;
            }
        } catch (Exception $exc) {
            $error_message = 'Error creating the Word Document';
            return null;
        }
    }

    private function descarga_formato_base($archivo)
    {
        if ($archivo !== null) {
            $this->mixScript('
                var form = $("<form >")
                .attr("method", "get")
                .attr("action","lib/file_download.php")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "r").attr("value", "' . $archivo . '" ))
                .append($("<input>").attr("type", "hidden").attr("name", "type_Disposition").attr("value", "attachment" ));
                $("body").append(form);
                form.submit();
                form.remove();
            ');
        } else {
            $this->mixScript('notify("Ha ocurrido un error al descargar el archivo", "error");');
        }
    }
}

if (isset($_SESSION[_S]['prm']['C']['11']['9'])) {
    new ControladorRegistroNotasEdoFin();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}

// Fin del código
