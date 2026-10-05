<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/scp/vistaAdecuacionMetas.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuacionMetas.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scp/ModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/DetalleModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraDetalleModificacionMeta.php';
require_once dirname(__FILE__) . '/../../ent/scp/EstadoModificacion.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaActividadMir.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoMetaActividadMir.php';

class ControladorConsultaFoliosModfMetas extends WebResponse
{
    public vistaAdecuacionMetas $vista;
    public modelAdecuacionMetas $model;
    public $gpo;
    public $ur;
    public $nivel;
    public $perfil;
    public $subsec;
    public $ue;
    public $presupuesto;
    public $consulta;
    public $nivelRevision;
    public $tri_id;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->vista = new vistaAdecuacionMetas();
        $this->model = new modelAdecuacionMetas();

        // variables de sesión
        $this->gpo = $_SESSION[_S]['gpo_id'];
        $this->ur = $_SESSION[_S]['ur_id'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->perfil = $_SESSION[_S]['prf_id'];
        $this->subsec = $_SESSION[_S]['ssc_id'];
        $this->ue = $_SESSION[_S]['nvj_id'];
        $this->presupuesto = (isset($_SESSION[_S]['ssc_id']) && $_SESSION[_S]['ssc_id'] == 3 ? true : false);
        $this->consulta = isset($_SESSION[_S]['prm']['S']['5']['5']['46']) ? true : false;
        $this->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '' ? $this->prm->nivelRevision : '');

        $hoy = date('Y-m-d');
        if ($hoy <= APP_EJERCICIO . '-03-31') {
            # code...
            $this->tri_id = 1;
        } elseif ($hoy <= APP_EJERCICIO . '-06-30') {
            $this->tri_id = 2;
        } elseif ($hoy <= APP_EJERCICIO . '-09-30') {
            $this->tri_id = 3;
        } elseif ($hoy <= APP_EJERCICIO . '-12-31') {
            $this->tri_id = 4;
        }

        switch ($this->metodo) {
            case 'llenaTablaFolios':
                $this->llenaTablaFolios();
                break;
            case 'addModFolio':
                $this->addModFolio();
                break;
            case 'tablaModificaciones': //Metodo para mostrar las modificaciones o movimientos
                $this->tablaModificaciones();
                break;
            case 'llenaTablaModificaciones':
                $this->llenaTablaModificaciones($mme = '');
                break;
            case 'modificarMetas':
                $this->modificarMetas();
                break;
            case 'listaMetas':
                $this->listaMetas();
                break;
            case 'metas':
                $this->metas();
                break;
            case 'tablaHistorialFolio':
                $this->tablaHistorialFolio();
                break; // Tabla de historial del folios
            default:
                $this->tablaFolios();
                break;
        }
        $this->returnJson();
    }// fin construct

    //Metodo que muestra la lista de folios creados por la UR
    private function tablaFolios()
    {
        $mme = new ModificacionMeta($this->prm); //INSTANCIANDO LA ENTIDAD MODIFICACION META

        $this->agregaTools('');
        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->size = 3;
        $ur->class = ' limpiar';

        $inputUr = $origen = '';
        switch ($this->nivel) {
            case 1: case 2:
                switch ($this->perfil) {
                    case 'S':
                        $inputUr = '<legend>Ur</legend>' . $this->ponInput($ur);
                        $origen = "'A','C'";
                        break;
                    default:
                        $origen = "'A','F'";
                        break;
                }
                break;
            default:
                $inputUr = '<legend>Ur</legend>' . $this->ponInput($ur);
                break;
        }

        $cvefin = new stdClass();
        $modalidad = new stdClass();
        $modalidad->sql = '';

        //Num. Folio
        $folio = new stdClass();
        $folio->id = 'mme_id';
        $folio->formato = 'entero';
        $folio->maxlength = 6;
        $folio->class = ' limpiar';
        $tipoAdecuacion = $this->vista->vwCombo('tipoAdecuacion', $mme);
        $tipoAdecuacion = is_object($tipoAdecuacion) ? $this->ponCombo($tipoAdecuacion) : '';

        $tipoFolio = $this->vista->vwCombo('tipoFolio', $mme);
        $tipoFolio = is_object($tipoFolio) ? $this->ponCombo($tipoFolio) : '';

        $estado = new stdClass();
        $estado->id = 'emo_id';
        $estado->mensaje = 'Seleccione una opcion de la lista';
        $estado->sql = 'select emo_id, emo_nombre from cat_EstadoModificacion;';
        $estado->search = true;
        $estado->class = ' limpiar';

        $oficioSolicitud = new stdClass();
        $oficioSolicitud->id = 'mod_OficioSolicitudSefin';
        $oficioSolicitud->size = 35;
        $oficioSolicitud->class = ' limpiar';

        $subsecretaria = new stdClass();
        $subsecretaria->id = 'sub';
        $subsecretaria->class = ' limpiar';

        $buscar = new stdClass();
        $buscar->id = 'buscar';
        $buscar->value = 'Buscar';
        $buscar->class = 'btn-outline-secondary';
        $buscar->icon = 'fa fa-search me-2';
        $buscar->text = 'Buscar';

        $limpiar = new stdClass();
        $limpiar->id = 'Clean';
        $limpiar->value = 'Limpiar';
        $limpiar->class = 'btn btn-outline-info me-2';
        $limpiar->text = 'Limpiar';
        $limpiar->icon = 'solid fa-trash-can me-2';
        $limpiar->color = 'amarillo';

        switch ($this->nivel) {
            case 1: case 2:
                switch ($this->perfil) {
                    case 'S':

                        break;
                    default:
                        break;
                }
                break;
            default:
                if ($this->consulta) {
                    $subsecretaria->opciones = [
                        '' => 'Seleccione una opcion de la lista',
                        1  => 'SUBSECRETARÍA DE EGRESOS',
                        2  => 'SUBSECRETARÍA DE PLANEACIÓN',
                    ];
                } else {
                    $subsecretaria->opciones = [1 => 'SUBSECRETARÍA DE EGRESOS'];
                }
                break;
        }

        $html = '';

        switch ($this->nivel) {
            case 1:
            case 2:
                $html .= '
                    <div class="row">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $inputUr . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                            <legend>Folio</legend>
                            ' . $this->ponInput($folio) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                            <legend>Tipo de Adecuación</legend>
                            ' . $tipoAdecuacion . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo de folio</legend>
                            ' . $tipoFolio . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-3">
                        <legend>Estado</legend>
                            ' . $this->templateCombo($estado) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-3">
                        <legend>Oficio de solicitud</legend>
                            ' . $this->ponInput($oficioSolicitud) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-3">
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                        <legend>&nbsp;</legend>
                            ' . $this->ponBoton($buscar) . '
                            ' . $this->ponBoton($limpiar) . '
                        </fieldset>
                    </div><br>
                    ';
                break;
            default:
                switch ($this->subsec) {
                    case 2:
                        $html .= '
                            <div class="row">
                                <fieldset class="col-md-3 col-xs-6">
                                    ' . $inputUr . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                    <legend>Folio</legend>
                                    ' . $this->ponInput($folio) . '
                                </fieldset>
                                <!--<fieldset class="col-md-3 col-xs-6">
                                    <legend>Clave de Financiamiento</legend>
                                    ' . $this->templateCombo($cvefin) . '
                                </fieldset>-->
                                <fieldset class="col-md-3 col-xs-6">
                                    <legend>Tipo de Adecuación</legend>
                                    ' . $tipoAdecuacion . '
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-3 col-xs-3">
                                    <legend>Tipo de folio</legend>
                                        ' . $tipoFolio . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-3">
                                    <legend>Estado</legend>
                                        ' . $this->templateCombo($estado) . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-3">
                                    <legend>Oficio de solicitud</legend>
                                        ' . $this->ponInput($oficioSolicitud) . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-3">
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-3 col-xs-6">
                                    
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                            
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                            
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                <legend>&nbsp;</legend>
                                    ' . $this->ponBoton($buscar) . '
                                    ' . $this->ponBoton($limpiar) . '
                                </fieldset>
                            </div><br>
                        ';
                        break;
                    case 3:
                    case 5:
                        $html .= '
                            <div class="row">
                                <fieldset class="col-md-3 col-xs-6">
                                    ' . $inputUr . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                    <legend>Tipo</legend>
                                    ' . $this->ponCombo($subsecretaria) . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                    
                                    <legend>Folio</legend>
                                    ' . $this->ponInput($folio) . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-3 col-xs-6">
                                    <legend>Tipo de Adecuación</legend>
                                    ' . $tipoAdecuacion . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                    <legend>Tipo de folio</legend>
                                    ' . $tipoFolio . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                    <legend>Estado</legend>
                                    ' . $this->templateCombo($estado) . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-6">
                                    <legend>Oficio de solicitud</legend>
                                    ' . $this->ponInput($oficioSolicitud) . '
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-3 col-xs-3">
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-3">
                                </fieldset>

                                <fieldset class="col-md-6 col-xs-3" >
                                <legend>&nbsp;</legend>
                                    ' . $this->ponBoton($buscar) . '
                                    ' . $this->ponBoton($limpiar) . '
                                </fieldset>
                            </div><br>
                        ';
                        break;
                }
                break;
        }
        $script = $this->getScript();

        $tabla = new stdClass();
        $tabla->id = 'tbl-Folios';
        $tabla->titulo = 'Adecuaciones de Metas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->tools = $html;
        $tabla->comunica->ur_clave = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '{$("#ur").val()}';
        $tabla->comunica->mme_id = (isset($this->prm->mme_id) && $this->prm->mme_id != '') ? $this->prm->mme_id : '{$("#mme_id").val()}';
        $tabla->comunica->tmd_id = (isset($this->prm->tmd_id) && $this->prm->tmd_id != '') ? $this->prm->tmd_id : '{$("#tmd_id").val()}';
        $tabla->comunica->tfm_id = (isset($this->prm->tfm_id) && $this->prm->tfm_id != '') ? $this->prm->tfm_id : '{$("#tfm_id").val()}';
        $tabla->comunica->emo_id = (isset($this->prm->emo_id) && $this->prm->emo_id != '') ? $this->prm->emo_id : '{$("#emo_id").val()}';
        $tabla->comunica->oficioSolicitud = (isset($this->prm->oficioSolicitud) && $this->prm->oficioSolicitud != '') ? $this->prm->oficioSolicitud : '{$("#mod_OficioSolicitudSefin").val()}';
        $tabla->comunica->omo_id = $origen;
        $tabla->comunica->sub = (isset($this->prm->sub) && $this->prm->sub != '') ? $this->prm->sub : '{$("#sub").val()}';
        $tabla->comunica->modalidad = (isset($this->prm->modalidad) && $this->prm->modalidad != '') ? $this->prm->modalidad : '{$("#modalidad").val()}';
        $tabla->comunica->metodo = 'llenaTablaFolios';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->width = '10px';
        $th->texto = 'Ver / Editar ';
        array_push($tr, $th);

        //Imprimir folio
        $th = new stdClass();
        $th->texto = 'Imprimir';
        array_push($tr, $th);

        //Ur
        $th = new stdClass();
        $th->texto = 'Ur';
        array_push($tr, $th);

        //Número de folio
        $th = new stdClass();
        $th->texto = 'Folio';
        array_push($tr, $th);

        //Modalidad del folio
        $th = new stdClass();
        $th->texto = 'Modalidad';
        array_push($tr, $th);

        //Fecha
        $th = new stdClass();
        $th->texto = 'Fecha';
        array_push($tr, $th);

        //Fecha Aut
        $th = new stdClass();
        $th->texto = 'Fecha Aut';
        array_push($tr, $th);
        //Tipo de modificacion
        $th = new stdClass();
        $th->texto = 'Tipo Adecuación';
        array_push($tr, $th);

        //Justificación del folio
        $th = new stdClass();
        $th->texto = 'Justificación';
        array_push($tr, $th);

        //Tipo del folio
        $th = new stdClass();
        $th->texto = 'Tipo';
        array_push($tr, $th);

        //Estado del folio
        $th = new stdClass();
        $th->texto = 'Estado';
        array_push($tr, $th);

        //Detalle del folio (movimientos agregados)
        $th = new stdClass();
        $th->texto = 'Detalle';
        array_push($tr, $th);

        $trbody = [];

        //contenido ver
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->attr->mme_id = 'mme_id';
        $td->class = 'addModFolio btn-xs';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa-solid fa-file-pdf';
        $td->attr = new stdClass();
        $td->attr->mmd_id = 'mmd_id';
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->attr->mme_id = 'mme_id';
        $td->attr->emo_id = 'emo_id';
        $td->attr->prg_cero = 'prg_cero';
        $td->class = 'imprimir btn btn-outline-danger btn-xs';
        array_push($trbody, $td);

        //UR
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['mme_gpo_id', 'mme_ur_id'];
        array_push($trbody, $td);

        //Numero de folio
        $td = new stdClass();
        $td->campoTxt = 'mme_id';
        $td->formato = 'numfolio';
        $td->attr = new stdClass();
        $td->attr->mmd_id = 'mmd_id';
        $td->attr->emo_id = 'emo_id';
        $td->attr->omo_id = 'omo_id';
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->attr->mme_id = 'mme_id';
        array_push($trbody, $td);

        //Modalidad del folio
        $td = new stdClass();
        $td->campoTxt = 'mmd_id';
        array_push($trbody, $td);

        //Fecha de creación del folio
        $td = new stdClass();
        $td->campoTxt = 'mme_fechaFolio';
        $td->formato = 'fecha';
        array_push($trbody, $td);

        //Fecha de autorización del folio
        $td = new stdClass();
        $td->campoTxt = 'mme_fechaAutorizacion';
        array_push($trbody, $td);

        //Tipo de adecuación
        $td = new stdClass();
        $td->campoTxt = 'tmd_nombre';
        array_push($trbody, $td);

        //Justificación
        $td = new stdClass();
        $td->campoTxt = 'mme_concepto';
        array_push($trbody, $td);

        //Tipo de folio
        $td = new stdClass();
        $td->campoTxt = 'tfm_nombre';
        array_push($trbody, $td);

        //Estado de folio
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'emo_color';
        $td->color = 'emo_color';
        $td->campoTxt = 'emo_id';
        $td->lbl = 'emo_nombre';
        $td->title = 'emo_nombre';
        array_push($trbody, $td);

        //Ir a detalle de folio
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->color = 'cielo';
        $td->icono = 'eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'origenModificacion';
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->attr->mme_id = 'mme_id';
        $td->class = 'detalleFolio btn-xs';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div class="col-md-12 col-xs-12">
                ' . $this->wrPonTabla($tabla) . '
            </div>
            <div id="archivos" class="col-md-12 col-xs-12">
            </div>
        ');

        $this->mixScript('
            $("#buscar").removeClass("btn-default");
            $("#Clean").removeClass("btn-default");
            $("label").remove();
        ');

        $this->recargaCombo($estado);
        $this->recargaCombo($modalidad);
        $this->mixHtml('
            <form id="impresion" method="post" action="" target="_blank">
                <input type="hidden" id="mme2_id" name="mme_id" value=""/>
                <input type="hidden" id="omo_id" name="omo_id" value=""/>
                <input type="hidden" id="mme_gpo_id" name="mme_gpo_id" value=""/>
                <input type="hidden" id="mme_ur_id" name="mme_ur_id" value=""/>
                <input type="hidden" id="emo_id2" name="emo_id" value=""/>
            </form>
        ');

        $this->mixHtml('
            <form id="exportar" method="post" action="rep/excel/scp/adecuaciones/ExportaRepFolio.php" target="_blank">
                <input type="hidden" id="repomo_id" name="repomo_id" value=""/>
                <input type="hidden" id="repmod_gpo_id" name="repmod_gpo_id" value=""/>
                <input type="hidden" id="repmod_ur_id" name="repmod_ur_id" value=""/>
                <input type="hidden" id="repmod_id" name="repmod_id" value=""/>
                <input type="hidden" id="mod_tipoMovimiento" name="mod_tipoMovimiento" value=""/>
            </form>
        ');

        if (isset($this->prm->regresar) && $this->prm->regresar != '') {
            $this->llenaTablaFolios();
        }

        $this->mixScript($script . '
            $("' . (($this->nivel == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
            
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); e.stopPropagation(); });
            
            $("#Clean").click(function(){
                $(".limpiar").val("");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    });
                return false;
            });
            
            $("#buscar").click(function(){
                ' . $this->wrLlamaLlenaTabla('tbl-Folios') . '
                return false;
            })
        ');
    }// fin function tablaFolios

    private function llenaTablaFolios()
    {
        $filtro = '';
        $ur_id = $gpo_id = '';

        //Valida la UR
        if (isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) < 3 && $this->prm->ur_clave != '') {
            $this->mixScript('notify("Número de UR inválido","warning");$("#ur").focus();');
        }

        //GRUPO Y UR
        if (isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) == 3 && $this->prm->ur_clave != '') {
            $gpo_id = substr($this->prm->ur_clave, 0, 1);
            $ur_id = substr($this->prm->ur_clave, 1);
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.mme_gpo_id = "' . $gpo_id . '" AND m.mme_ur_id ="' . $ur_id . '" ';
        }

        //NUMERO DE FOLIO
        if (isset($this->prm->mme_id) && $this->prm->mme_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.mme_id="' . $this->prm->mme_id . '"';
        }

        //TIPO DE FOLO DE MODIFICACION
        if (isset($this->prm->tfm_id) && $this->prm->tfm_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.tfm_id="' . $this->prm->tfm_id . '" ';
        }

        //ESTADO DE LA MODIFICACION
        if (isset($this->prm->emo_id) && $this->prm->emo_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.emo_id="' . $this->prm->emo_id . '" ';
        }

        //TIPO DE MODIFICACION
        if (isset($this->prm->tmd_id) && $this->prm->tmd_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.tmd_id="' . $this->prm->tmd_id . '" ';
        }

        //OFICION SOLICITUD SEFIN
        if (isset($this->prm->oficioSolicitud) && $this->prm->oficioSolicitud != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' mme_oficioSolicitudSefin="' . $this->prm->oficioSolicitud . '" ';
        }


        //VALIDA SI EL NIVEL JERARQUICO ES MENOR A 2 Y SI EL PERFIL ES DIFERENTE A SUPERVISOR
        if ($this->nivel <= 2 && $this->perfil != 'S') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.mme_gpo_id = ' . $this->gpo . ' and m.mme_ur_id = ' . $this->ur . ' ';
        }

        $subsecretaria = '';
        if (isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) == 3 && $this->prm->ur_clave != '') {
            $gpo_id = substr($this->prm->ur_clave, 0, 1);
            $ur_id = substr($this->prm->ur_clave, 1);
        }

        //DEPENDIENTO DEL NIVEL JERARQUICO SE AGREGAN FILTROS
        switch ($this->nivel) {
            case 1:
            case 2:
                $subsecretaria = '';
                //VERIFICA SI EL USUARIO EN SESIÓN TIENE PERFIL DE SUPERVISOR
                if ($this->perfil == 'S') {
                    if ($filtro != '') {
                        $filtro .= ' and';
                    } else {
                        $filtro .= 'where';
                    }
                    $filtro .= ' (ur_rh = 1 OR (m.mod_gpo_id in ( 1,5,6,7,8) 
                            and concat(mod_gpo_id,mod_ur_id) 
                            not in (105, 115, 116, 530, 531, 540, 553 )) ) ';
                }

                break;
            default:
                if (!$this->consulta) { //Si no tiene permisos de consulta general
                    switch ($this->subsec) {
                        case 2:
                            if ($filtro != '') {
                                $filtro .= ' and ';
                            } else {
                                $filtro .= 'where';
                            }
                            $subsecretaria = ' mme_Subsecretaria=2';
                            $filtro .= $subsecretaria;
                            break;
                        case 3:
                        case 5:
                            if ($filtro != '') {
                                $filtro .= ' and ';
                            } else {
                                $filtro .= 'where';
                            }
                            $subsecretaria = ' mme_Subsecretaria=1';
                            $filtro .= $subsecretaria;
                            break;
                    }

                } else { //Si tiene el permiso de consulta general.
                    if (isset($this->prm->sub) && $this->prm->sub != '') {
                        if ($filtro != '') {
                            $filtro .= ' and ';
                        } else {
                            $filtro .= 'where';
                        }
                        $filtro .= ' mme_Subsecretaria="' . $this->prm->sub . '" ';
                    }
                    if (isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) == 3 && $this->prm->ur_clave != '') {
                        $gpo_id = substr($this->prm->ur_clave, 0, 1);
                        $ur_id = substr($this->prm->ur_clave, 1);
                    }

                }

                break;
        }

        $sql = "
                SELECT omo_id origenModificacion ,mme_gpo_id,mme_ur_id, mme_id,mmd_id, prg_cero, mme_fechaFolio,
                    if(mme_fechaAutorizacion is null,'',DATE_FORMAT(mme_fechaAutorizacion,'%d/%m/%Y %H:%i:%s')) mme_fechaAutorizacion,mme_Subsecretaria,
                    mme_concepto,emo_id,emo_color,emo_nombre,'ADECUACION DE METAS' AS tmd_nombre
                FROM scp_ModificacionMeta m
                INNER JOIN cat_EstadoModificacion e using(emo_id)
                INNER JOIN cat_ModalidadModificacion c using(mmd_id)
                INNER JOIN cat_OrigenModificacion o using(omo_id)
                $filtro;
            ";

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Folios';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $(".addModFolio").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo:"addModFolio",
                        mme_gpo_id:$(this).attr("mme_gpo_id"),
                        mme_ur_id:$(this).attr("mme_ur_id"),
                        omo_id:$(this).attr("omo_id"),
                        mme_id:$(this).attr("mme_id")
                        ' . (($this->nivelRevision != '') ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                    });
                    return false;
                });

                $(".detalleFolio").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaModificaciones",
                        omo_id:$(this).attr("omo_id"),
                        mme_gpo_id:$(this).attr("mme_gpo_id"),
                        mme_ur_id:$(this).attr("mme_ur_id"),
                        mme_id:$(this).attr("mme_id")
                        ' . (($this->nivelRevision != '') ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . ',
                    });
                    return false;
                });

                $(".imprimir").click(function(){
                    console.log($(this).attr("mme_id"));
                    let prg = $(this).attr("prg_cero");
                    if (prg == 1) {
                    $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesMetasPRG000.php");
                    } else{
                        $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesConsultaMetas.php");
                        }
                    $("#mme2_id").val($(this).attr("mme_id"));
                    $("#omo_id").val($(this).attr("omo_id"));
                    $("#mme_gpo_id").val($(this).attr("mme_gpo_id"));
                    $("#mme_ur_id").val($(this).attr("mme_ur_id"));
                    $("#emo_id2").val($(this).attr("emo_id"));
                    $("#impresion").submit();
                });
            ');

    }//fin llenaTablaFolios

    /*METODO MOSTRAR EL DETALLE DEL FOLIO*/
    private function addModFolio()
    {
        $esUR = ($this->ue || $this->ur) ? true : false;
        //Inicialización de variables
        $unidadResponsable = '';

        $mme = new ModificacionMeta($this->prm);//INSTANCIANDO LA ENTIDAD MODIFICACION META
        $mme->select();
        //BOTONES
        $btnRegresar = $this->vista->vwBotones('regresar');
        $btnHistorial = $this->vista->vwBotones('historial');
        $btnRegresar = $this->ponBoton($btnRegresar);
        $btnHistorial = $this->ponBoton($btnHistorial);

        //INPUT
        $numeroFolio = $this->vista->vwInput('numeroFolio', $mme, $esUR);
        $fecha = $this->vista->vwInput('fecha', $mme, $esUR);
        $fecha->readonly = true;
        $justificacion = $this->vista->vwInput('justificacionM', $mme, $esUR);
        $oficioSolicitud = $this->vista->vwInput('oficioSolicitudM', $mme, $esUR);
        $oficioAutorizacion = $this->vista->vwInput('oficioAutorizacionM', $mme, $esUR);


        //COMBOS
        $unidadEjecutora = $this->vista->vwCombo('unidadEjecutora', $mme);
        if ($this->ue && is_object($unidadEjecutora)) {
            $unidadEjecutora->selecciona = $_SESSION[_S]['ue_id'];
            $unidadEjecutora->disabled = true;
        }
        $modalidadModificacion = $this->vista->vwCombo('modalidadAdecuacion', $mme);
        $tipoAdecuacion = $this->vista->vwCombo('tipoAdecuacion', $mme);
        $tipoFolio = $this->vista->vwCombo('tipoFolio', $mme);
        $subsecretaria = $this->vista->vwCombo('subsecretaria', $mme);

        //Verifica si es sefin
        if ($this->nivel >= 3) {
            //PARA MOSTRAR LA UNIDAD RESPONSABLE DE LA QUE SE ESTÁ CAPTURANDO EL FOLIO
            $objUr = new UnidadResponsable();
            $objUr->setGpo_id($this->prm->mme_gpo_id);
            $objUr->setUr_id($this->prm->mme_ur_id);
            $objUr->select();

            $prmUR = new stdClass();
            $prmUR->mostrar = 'ur';
            $prmUR->parametrosExtra = new stdClass();
            $prmUR->parametrosExtra->prm = $this->prm;
            $prmUR->parametrosExtra->objUR = $objUr;
            $unidadResponsable = $this->vista->vwToolsTablaFolios($prmUR);
        }

        // FORMULARIO
        $numeroFolio = is_object($numeroFolio) ? $this->ponInput($numeroFolio) : '';
        $fecha = is_object($fecha) ? $this->ponInput($fecha) : '';
        $justificacion = is_object($justificacion) ? $this->ponTextArea($justificacion) : '';
        $oficioSolicitud = is_object($oficioSolicitud) ? $this->ponInput($oficioSolicitud) : '';
        $oficioAutorizacion = is_object($oficioAutorizacion) ? $this->ponInput($oficioAutorizacion) : '';
        $modalidadModificacion = is_object($modalidadModificacion) ? $this->ponCombo($modalidadModificacion) : '';
        $unidadEjecutora = is_object($unidadEjecutora) ? $this->ponCombo($unidadEjecutora) : '';
        $tipoAdecuacion = is_object($tipoAdecuacion) ? $this->ponCombo($tipoAdecuacion) : '';
        $tipoFolio = is_object($tipoFolio) ? $this->ponCombo($tipoFolio) : '';
        $subsecretaria = is_object($subsecretaria) ? $this->ponCombo($subsecretaria) : '';

        $formulario = ' 
            <div class = "row">
                ' . $unidadResponsable . '
            </div>
            <form id = "Folio">
                <legend><span style = "font-size:14px;" class="label label-default">Datos Generales</span></legend>
                <div class = "row">
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $numeroFolio . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $fecha . '
                    </fieldset>
                    <fieldset id = "filaUe" class="col-md-4 col-xs-4">
                        ' . $unidadEjecutora . '
                    </fieldset> 
                </div>
                <div class = "row">
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $modalidadModificacion . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $tipoAdecuacion . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $tipoFolio . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $subsecretaria . '
                    </fieldset>
                </div>
                <div class = "row">
                    <fieldset class = "col-md-6 col-xs-6">
                        ' . $justificacion . '
                    </fieldset>
                    <fieldset class = "col-md-3 col-xs-3">
                        ' . $oficioAutorizacion . '
                    </fieldset>
                    <fieldset class = "col-md-3 col-xs-3">
                        ' . $oficioSolicitud . '
                    </fieldset>
                </div>
            </form>
            '
        ;

        $this->mixHtml($formulario);

        // BOTONES EN TOOLS
        $this->agregaTools($btnRegresar . ' ' . $btnHistorial);

        $this->mixScript('
            $("#btnRegresar").removeClass("btn-default");
            $("#btnHistorial").removeClass("btn-default");
            $("#btnRegresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    regresar:1
                    ' . (isset($this->prm->mme_id) && !empty($this->prm->mme_id) ? ',mme_id:"' . $this->prm->mme_id . '"' : '') . '
                    ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->mme_gpo_id) && isset($this->prm->mme_ur_id) ? ',ur_clave:' . $this->prm->mme_gpo_id . $this->prm->mme_ur_id . '' : ',ur_clave:' . $this->prm->mme_gpo_id . $this->prm->mme_ur_id . '') . '
                });
                return false;
            });

            $("#btnHistorial").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaHistorialFolio"
                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->mme_gpo_id) ? ',mme_gpo_id:' . $this->prm->mme_gpo_id . '' : '') . '
                    ' . (isset($this->prm->mme_ur_id) ? ',mme_ur_id:' . $this->prm->mme_ur_id . '' : '') . '
                    ' . (isset($this->prm->mme_id) ? ',mme_id:' . $this->prm->mme_id . '' : '') . '
                });
            });
        ');

    }//fin de getFolioMetas

    //Metodo para mostrar las modificaciones o movimientos agregados al folio por la UR
    private function tablaModificaciones()
    {
        //INCIALIZACIÓN DE VARIABLES DE LOS BOTONES
        $btnRegresar = '';
        $esUR = $this->ur || $this->ue;
        //BLOQUE CLASIFICACION ADMINISTRATIVA
        $script = 'bloquearClasAdmin();';
        $this->pintaScript($script);
        //INSTANCIA DE LAS ENTIDADES
        $mme = new ModificacionMeta($this->prm);

        //BOTONES
        $btnRegresar = $this->vista->vwBotones('regresar');
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"tablaFolios",
                omo_id:"' . $mme->getOmo_id() . '",
                mme_gpo_id:' . $mme->getMme_gpo_id() . ',
                mme_ur_id:"' . $mme->getMme_ur_id() . '"
                ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
            });
            return false;
        ';

        $btnRegresar = is_object($btnRegresar) ? $this->ponBoton($btnRegresar) : '';
        $html = $this->vista->vwInformacionFolio($mme);
        //DEFINICIÓN DE LA TABLA DE OBRAS/ACTIVIDADES DEL FOLIO
        $pantalla = $this->pantalla;
        $tabla = $this->vista->vwTablaObrasActividades($mme, $esUR, $this->presupuesto, $pantalla, $this->nivelRevision);
        $tabla->tools = $html;

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');
        $this->llenaTablaModificaciones($mme);

        //SE AGREGAN LOS BOTONES
        $this->agregaTools($btnRegresar);
        $this->mixScript('
            $("#btnRegresar").removeClass("btn-default");
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaFolios").click(); e.stopPropagation(); }); 
        ');

    }//fin tablaModificaciones

    /* MÉTODO QUE VA A HACER UN MIXSCRIPT SEGÚN LO ENVIADO */
    private function pintaScript($s)
    {
        if (isset($s)) {
            $this->mixScript('' . $s . '');
        }
    }

    /* METODO QUE LLENA LA TABLA DE LAS OBRAS/ACTIVIDADES DEL FOLIO */
    private function llenaTablaModificaciones($mme)
    {
        $sql = '';
        $esUR = $this->ur || $this->ue;
        if ($esUR) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('ur', $mme);
        }

        if ($this->presupuesto) {
            $sql = $this->model->getConsultaTablaMovimientosFolio('sefin', $mme);
        }

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Modificaciones';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('  
            $(".addMmeModificacion").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"' . ($esUR ?
                            'addMmeModificacion' : 'addModPrevision') . '",
                    omo_id:$(this).attr("omo_id"),
                    mme_id:$(this).attr("mme_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    gpo_id:$(this).attr("gpo_id"),
                    ur_id:$(this).attr("ur_id"),
                    ue_id:$(this).attr("ue_id"),
                    epg_id:$(this).attr("epg_id"),
                    pg_id:$(this).attr("pg_id"),
                    pe_id:$(this).attr("pe_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    tcp_id:$(this).attr("tcp_id"),
                    prev:$(this).attr("prev")
                    ' . ($this->nivelRevision == 'revisionSEFIN' ?
                            ',prevision:true' : '') . '
                    ' . ($this->nivelRevision != '' ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });
            $(".addModModificacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"modificarMetas",
                    omo_id:$(this).attr("omo_id"),
                    mme_gpo_id:$(this).attr("mme_gpo_id"),
                    mme_ur_id:$(this).attr("mme_ur_id"),
                    mme_id:$(this).attr("mme_id"),
                    epg_id:$(this).attr("epg_id")
                    ' . (($this->nivelRevision != '') ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : '') . '
                });
                return false;
            });
        ');
    }

    private function modificarMetas()
    {
        $omo_id = $this->prm->omo_id;
        $mme_gpo_id = $this->prm->mme_gpo_id;
        $mme_ur_id = $this->prm->mme_ur_id;
        $mme_id = $this->prm->mme_id;
        $botonRegresar = '';
        $botonEliminar = '';

        $mme = new ModificacionMeta($this->prm);
        $mme->select();

        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->class = 'btn btn-outline-secondary';
        $regresar->value = 'Regresar';
        $regresar->icon = 'circle-left me-2';
        $botonRegresar = $this->ponBoton($regresar);

        $this->agregaTools($botonRegresar);
        $this->conectar();
        $this->setSql('
            SELECT epg_id,prg_id,spr_id,py_id,oa_id,oa_nombre 
            FROM scp_ModificacionMeta
            INNER JOIN scp_DetalleModificacionMeta USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN enc_Programatico  USING(epg_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            
            WHERE omo_id = ?
            AND mme_gpo_id = ?
            AND mme_ur_id = ?
            AND mme_id = ?
            AND epg_id = ?
            GROUP BY epg_id; 
        ');
        $params = [$omo_id, $mme_gpo_id, $mme_ur_id, $mme_id, $this->prm->epg_id];
        $this->consultar($params);
        $this->desconectar();

        $htm = '';
        $htm .= '<table class="table table table-bordered table-hover">';
        $htm .= '
            <tr>
                <th class="warning" style="text-align:center" colspan="1">Programa</th>
                <th class="warning" style="text-align:center" colspan="1">Subprograma</th>
                <th class="warning" style="text-align:center" colspan="1">Proyecto</th>
                <th class="warning" style="text-align:center" colspan="1">Obra/Actividad</th>
                <th class="warning" style="text-align:center" colspan="8">Nombre</th>
                <th class="warning" style="text-align:center" colspan="1">Ver metas</th>
            </tr>
        ';

        $cambioClase = true;
        while ($row = $this->extraer()) {
            $clase = ($cambioClase) ? 'class="warning"' : '';
            $htm .= '<tr>
                <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                <td ' . $clase . ' align="center" colspan="8">' . $row['oa_nombre'] . '</td>
                <td ' . $clase . ' align="center" colspan="1" >
                    <button type="button" id="verMetas" icon="eye" title="Opción para ver metas de la actividad" class="verMetas btn btn-outline-secondary" epg_id=' . $row['epg_id'] . '>
                        <i class="fa fa-eye"></i>
                    </button>
                </td>
            </tr>
            <tr class="warning">
                    <th></th>
                <th colspan = "12" class ="divMetas" id="oa' . $row['epg_id'] . '"></th>
            </tr>
            ';

            $cambioClase = ($cambioClase) ? false : true;
        }

        $htm .= '</table>';
        $this->mixHtml($htm);

        $this->mixScript('
            $("#regresar").removeClass("btn-default");
            $(".verMetas").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"listaMetas",
                    omo_id:"' . $omo_id . '",
                    mme_gpo_id:"' . $mme_gpo_id . '",
                    mme_ur_id:"' . $mme_ur_id . '",
                    mme_id:' . $mme_id . ',
                    epg_id:$(this).attr("epg_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                    
                });
            });
                    
            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones"
                    ' . (isset($omo_id) ? ',omo_id:"' . $omo_id . '"' : '') . '
                    ' . (isset($mme_gpo_id) ? ',mme_gpo_id:"' . $mme_gpo_id . '"' : '') . '
                    ' . (isset($mme_ur_id) ? ',mme_ur_id:"' . $mme_ur_id . '"' : '') . '
                    ' . (isset($mme_id) ? ',mme_id:"' . $mme_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            });
        ');

    }// fin modificarMetas

    //METODO QUE ENLISTARA LAS METAS POR ACTIVIDAD PROGRAMÁTICA
    private function listaMetas()
    {
        $cambioClase = true;
        $this->setContenedor('oa' . $this->prm->epg_id);
        $m = new ModificacionMeta($this->prm);

        $consulta = ' LEFT JOIN ';
        $botonRegresar = '';

        //VALIDA SI ES LECTURA
        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->class = 'btn btn-outline-secondary';
        $regresar->value = 'Regresar';
        $regresar->icon = 'circle-left me-2';

        $botonRegresar = $this->ponBoton($regresar);

        //AGREGA LOS BOTONES A LA BARRA DE HERRAMIENTAS
        $this->agregaTools($botonRegresar);

        //CONSTRUYE LA CONSULTA PARA LAS METAS DE LA ACTIVIDAD
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre, pdi_mir,
                pdi_nombre,ctInd.umv_id,ctInd.umv_nombre, umi_nombre, tiv_nombre,
                cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
                mac_descripcion,epg_id,mac_original,mac_modificado vigente, macm_id
            FROM enc_Programatico enc
            JOIN cat_MetaActividad ma USING(epg_id)
            ' . $consulta . '  (
                SELECT omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,
                CASE WHEN MAX(tri_id) =1 THEN
                SUM(mact_modificado)
                ELSE 0.00 END AS mod_trimestre1,
                CASE WHEN MAX(tri_id) =2 THEN
                SUM(mact_modificado)
                ELSE 0.00 END AS mod_trimestre2,
                CASE WHEN MAX(tri_id) =3 THEN
                SUM(mact_modificado)
                ELSE 0.00 END AS mod_trimestre3,
                CASE WHEN MAX(tri_id) =4 THEN
                SUM(mact_modificado)
                ELSE 0.00 END AS mod_trimestre4
                FROM ' . BD_TEMPORAL . '.tem_ModificacionMetaTrimestral
                WHERE  epg_id = :epg_id
                AND omo_id = :omo_id 
                AND mme_gpo_id = :mme_gpo_id 
                AND mme_ur_id = :mme_ur_id
                AND mme_id = :mme_id
                GROUP BY omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id
            ) b using (pii_id,epg_id)
            JOIN cat_Subprograma cs USING(gpo_id,ur_id,prg_id,spr_id)
            JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            JOIN cat_Indicador i USING(ind_id,nim_id)
            JOIN cat_ProductoIntermedio USING(pdi_id)
            JOIN cat_UnidadMedidaVariable ctInd USING(umv_id)
            JOIN cat_UnidadMedidaIndicador USING(umi_id)
            JOIN cat_TipoValor tv ON tv.tiv_id = i.tiv_id 
            WHERE epg_id = :epg_id
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
        $params = [
            ':omo_id'     => $this->prm->omo_id,
            ':epg_id'     => $this->prm->epg_id,
            ':mme_gpo_id' => $this->prm->mme_gpo_id,
            ':mme_ur_id'  => $this->prm->mme_ur_id,
            ':mme_id'     => $this->prm->mme_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();

        // ENCABEZADO DE LA TABLA DE LAS METAS DE LA ACTIVIDAD
        $htm = '';
        $htm .= '<table  class="table table table-bordered table-hover">';
        $htm .= '
            <tr>
                <th class="warning" style="text-align:center" colspan="2">Actividad MIR</th>
                <th class="warning" style="text-align:center" colspan="2">Indicador</th>
                <th class="warning" style="text-align:center" colspan="1">Ver</th>
            </tr>
        ';

        while ($row = $this->extraer()) {
            $clase = ($cambioClase) ? 'class="warning"' : '';
            //INFORMACIÓN DE METAS
            $htm .= '
                <tr>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['pdi_id'] . ' - ' . $row['pdi_mir'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['ind_id'] . ' - ' . $row['ind_nombre'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="1" >
                        <button type="button" id="verMetasDet" icon="eye-open" title="Opción para ver metas de la actividad" class="verMetasDet btn btn-outline-secondary" epg_id=' . $row['epg_id'] . ' pii_id = ' . $row['pii_id'] . ' macm_id=' . $row['macm_id'] . '>
                            <i class="fa -regular fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th class="warning" style="text-align:center" colspan="2">Unidad Medida Variable</th>
                    <th class="warning" style="text-align:center" colspan="2">Descripción</th>
                </tr>
                <tr>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['umv_nombre'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['ind_definicion'] . '</td>
                </tr>
                <tr>
                    <th class="warning" style="text-align:center" colspan="2">Unidad Medida Indicador</th>
                    <th class="warning" style="text-align:center" colspan="2">Tipo Valor</th>
                </tr>
                <tr>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['umi_nombre'] . '</td>
                    <td style="background: rgb(255,255,255)" align="center" colspan="2">' . $row['tiv_nombre'] . '</td>
                </tr>
                <tr class="warning">
                    <th colspan = "12" class ="divMetasDet" id="oa' . $row['epg_id'] . $row['pii_id'] . '"></th>
                </tr>
            ';
            $cambioClase = ($cambioClase) ? false : true;
        }// fin while

        $htm .= '</table>';
        $this->mixHtml($htm);

        $this->mixScript('
            $("#regresar").removeClass("btn-default");
            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones"
                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->mme_gpo_id) ? ',mme_gpo_id:"' . $this->prm->mme_gpo_id . '"' : '') . '
                    ' . (isset($this->prm->mme_ur_id) ? ',mme_ur_id:"' . $this->prm->mme_ur_id . '"' : '') . '
                    ' . (isset($this->prm->mme_id) ? ',mme_id:"' . $this->prm->mme_id . '"' : '') . '
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            });
            $(".verMetasDet").click(function(){
                $(".divMetasDet").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"metas",
                    omo_id:"' . $this->prm->omo_id . '",
                    mme_gpo_id:"' . $this->prm->mme_gpo_id . '",
                    mme_ur_id:"' . $this->prm->mme_ur_id . '",
                    mme_id:"' . $this->prm->mme_id . '",
                    pii_id:$(this).attr("pii_id"),
                    epg_id:$(this).attr("epg_id"),
                    macm_id:$(this).attr("macm_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                });
            });
        ');

    }// fin listaMetas

    //MUESTRA LA INFORMACION CALENDARIZADA DE LA META
    private function metas()
    {
        $this->setContenedor('oa' . $this->prm->epg_id . $this->prm->pii_id);
        $m = new ModificacionMeta($this->prm);
        $consulta = ' LEFT JOIN ';

        $this->prm->gpo_id = $this->prm->mme_gpo_id;
        $this->prm->ur_id = $this->prm->mme_ur_id;
        $this->prm->eri_id = $this->prm->pii_id;

        $sql = '
        SELECT emo_id
        FROM scp_ModificacionMeta
        WHERE omo_id = :omo_id
        AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id
        AND mme_id = :mme_id
    ';
        $params = [
            ':gpo_id' => $this->prm->mme_gpo_id,
            ':ur_id'  => $this->prm->mme_ur_id,
            ':omo_id' => $this->prm->omo_id,
            ':mme_id' => $this->prm->mme_id,
        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();
        $datos = $conexion->extraer();

        if ($datos['emo_id'] == 14) {
            $filtro = BD_CTRL . '.aux_HistoricoModificacionMeta';
            $variableVD = 'mac_valorDeseado';
        } else {
            $filtro = BD_TEMPORAL . '.tem_ModificacionMetaTrimestral';
            $variableVD = 'macm_ValorDeseado';
        }

        $sql = '
            SELECT pii_id as pii, gpo_id,ur_id,epg_id as epg, mact_alcanzado as alcanzado,
                    SUM(IF(mat.tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1,
                    SUM(IF(mat.tri_id=2,mact_capturaAvance,NULL)) cap_trimestre2,
                    SUM(IF(mat.tri_id=3,mact_capturaAvance,NULL)) cap_trimestre3,
                    SUM(IF(mat.tri_id=4,mact_capturaAvance,NULL)) cap_trimestre4
                FROM cat_MetaActividad mac
                INNER JOIN ( SELECT pii_id, epg_id, tri_id, mact_original, mact_modificado, mact_alcanzado,
                    mact_capturaAvance, 4 AS nim_id
                    FROM cat_MetaActividadTrimestre
                ) mat USING (pii_id,epg_id)
                INNER JOIN enc_Programatico USING(epg_id)
                INNER JOIN mir_TrimestreUr USING (gpo_id, ur_id,tri_id,nim_id)
            WHERE epg_id = :epg
            AND pii_id = :pii;
        ';
        $params = [':epg' => $this->prm->epg_id, ':pii' => $this->prm->pii_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $capturaAv = $this->extraer();

        //CONSTRUYE LA CONSULTA PARA OBTENER LA MODIFICACION DE METAS
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre,
            pdi_nombre,ctInd.umv_id,umi_nombre, "S" AS tiv,
            cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
            mac_descripcion,epg_id,mac_original, macm_id, i.umi_id, ' . $variableVD . ' as valorDeseado, tri_id,
            mact_modificado,tmf_id,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id=4 AND tri_id=1) AS est_trimestre1,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id=4 AND tri_id=2) AS est_trimestre2,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id = 4 AND tri_id = 3) AS est_trimestre3,
                (SELECT etr_id1 FROM ' . BD_CTRL . '.mir_TrimestreUr
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id = 4 AND tri_id = 4) AS est_trimestre4,
                i.tiv_id,tv.tiv_nombre
                FROM ' . BD_CTRL . '.enc_Programatico enc
                JOIN ' . BD_CTRL . '.cat_MetaActividad ma USING(epg_id)
                ' . $consulta . '  (
                    SELECT omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id,mact_modificado, ' . $variableVD . ', tmf_id, tri_id
                    FROM ' . $filtro . '
                    WHERE  epg_id = :epg
                    AND pii_id = :pii
                    AND omo_id= :omo and mme_gpo_id= :gpo and mme_ur_id=:ur and mme_id= :mme
                    GROUP BY omo_id,mme_gpo_id,mme_ur_id,mme_id,pii_id,epg_id
                ) b using (pii_id,epg_id)
                JOIN cat_Subprograma cs USING(gpo_id,ur_id,prg_id,spr_id)
                JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                JOIN cat_Indicador i USING(ind_id,nim_id)
                JOIN cat_ProductoIntermedio USING(pdi_id)
                JOIN cat_UnidadMedidaVariable ctInd USING(umv_id)
                JOIN cat_UnidadMedidaIndicador USING(umi_id)
                JOIN cat_TipoValor tv ON tv.tiv_id = i.tiv_id 
            WHERE epg_id = :epg
            AND pii_id = :pii
            AND i.nim_id=4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ma.usr_fecha;
        ';
        $params = [':gpo' => $this->prm->mme_gpo_id, ':ur' => $this->prm->mme_ur_id, ':epg' => $this->prm->epg_id, ':pii' => $this->prm->pii_id, ':omo' => $this->prm->omo_id, ':mme' => $this->prm->mme_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $datos = $this->extraer();
        $this->prm->tri_id = isset($datos['tri_id']) ? $datos['tri_id'] : $this->tri_id;

        $metaActividad = new MetaActividadMir($this->prm);
        $historicoMeta = new HistoricoMetaActividadMir($this->prm);

        $trimestres = '';
        $div = '<div class="row row-cols-md-4">';
        $datosMeta = '
            <form id="calendario">
                <table class="table table-hover table-bordered" >
                    <tr>
                        <td colspan=12 >
                        <input type="hidden" id="tiv_id" value="' . $datos['tiv_id'] . '" />
                        <input type="hidden" id="vigente" value="' . $datos['tiv_nombre'] . '"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=12 >
        ';

        $umi = $datos['umi_id'];
        $prgs_id = $datos['prg_id'];
        $sprs_id = $datos['spr_id'];

        //***********************LINEA BASE***************************************//
        $macLb = new stdClass();
        $macLb->id = 'lb';
        $macLb->readonly = true;
        $macLb->enDiv = true;
        $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;
        $macLb->maxlength = 4;
        $macLb->formato = 'entero';

        $macLbValor = new stdClass();
        $macLbValor->readonly = true;
        $macLbValor->id = 'lbValor';
        $macLbValor->enDiv = true;
        $macLbValor->value = $metaActividad->getLbValor();
        $macLbValor->formato = 'numero';

        $macLbIndicador = new stdClass();
        $macLbIndicador->readonly = true;
        $macLbIndicador->id = 'lbIndicador';
        $macLbIndicador->enDiv = true;
        $macLbIndicador->value = $metaActividad->getLbIndicador();
        $macLbIndicador->formato = 'numero';

        //***********************AÑO ANTERIOR***************************************//
        $macAnterior = new stdClass();
        $macAnterior->id = 'anterior';
        $macAnterior->enDiv = true;
        $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;
        $macAnterior->maxlength = 4;
        $macAnterior->formato = 'entero';

        $macAnteriorValor = new stdClass();
        $macAnteriorValor->id = 'anteriorValor';
        $macAnteriorValor->enDiv = true;
        $macAnteriorValor->value = $historicoMeta->getAnteriorValor();
        $macAnteriorValor->formato = 'numero';

        $macOriginal = new stdClass();
        $macOriginal->required = true;
        $macOriginal->id = 'macm_original';
        $macOriginal->enDiv = true;
        $macOriginal->decimales = 4;
        $macOriginal->value = $datos['valorDeseado'] != 0 ? $datos['valorDeseado'] : $historicoMeta->getOriginal();
        $macOriginal->formato = 'numero';

        $macAnteriorIndicador = new stdClass();
        $macAnteriorIndicador->id = 'anteriorIndicador';
        $macAnteriorIndicador->enDiv = true;
        $macAnteriorIndicador->value = $historicoMeta->getAnteriorIndicador();
        $macAnteriorIndicador->formato = 'numero';

        //****************************CALENDARIO 1 GENERAL*************************************//
        $trimestre1 = new stdClass();
        $trimestre1->id = 'ori_trimestre1';
        $trimestre1->enDiv = true;
        $trimestre1->label = '1° Trimestre';
        $trimestre1->formato = 'numero';

        $trimestre2 = new stdClass();
        $trimestre2->id = 'ori_trimestre2';
        $trimestre2->enDiv = true;
        $trimestre2->label = '2° Trimestre';
        $trimestre2->formato = 'numero';

        $trimestre3 = new stdClass();
        $trimestre3->id = 'ori_trimestre3';
        $trimestre3->enDiv = true;
        $trimestre3->label = '3° Trimestre';
        $trimestre3->formato = 'numero';

        $trimestre4 = new stdClass();
        $trimestre4->id = 'ori_trimestre4';
        $trimestre4->enDiv = true;
        $trimestre4->label = '4° Trimestre';
        $trimestre4->formato = 'numero';

        $total = new stdClass();
        $total->disabled = true;
        $total->id = 'ori_total';
        $total->enDiv = true;
        $total->label = 'Total';

        //****************************CALENDARIO 1*************************************//
        $trimestre1Cal1 = new stdClass();
        $trimestre1Cal1->id = 'numA_trimestre1';
        $trimestre1Cal1->enDiv = true;
        $trimestre1Cal1->label = '1° Trimestre';
        $trimestre1Cal1->formato = 'numero';
        $trimestre1Cal1->readonly = true;

        $trimestre2Cal1 = new stdClass();
        $trimestre2Cal1->id = 'numA_trimestre2';
        $trimestre2Cal1->enDiv = true;
        $trimestre2Cal1->label = '2° Trimestre';
        $trimestre2Cal1->formato = 'numero';
        $trimestre2Cal1->readonly = true;

        $trimestre3Cal1 = new stdClass();
        $trimestre3Cal1->id = 'numA_trimestre3';
        $trimestre3Cal1->enDiv = true;
        $trimestre3Cal1->label = '3° Trimestre';
        $trimestre3Cal1->formato = 'numero';
        $trimestre3Cal1->readonly = true;

        $trimestre4Cal1 = new stdClass();
        $trimestre4Cal1->id = 'numA_trimestre4';
        $trimestre4Cal1->enDiv = true;
        $trimestre4Cal1->label = '4° Trimestre';
        $trimestre4Cal1->formato = 'numero';
        $trimestre4Cal1->readonly = true;

        $totalCal1 = new stdClass();
        $totalCal1->disabled = true;
        $totalCal1->id = 'num_total';
        $totalCal1->enDiv = true;
        $totalCal1->label = 'Total';

        //****************************CALENDARIO 2*************************************//
        $trimestre1Cal2 = new stdClass();
        $trimestre1Cal2->id = 'denA_trimestre1';
        $trimestre1Cal2->enDiv = true;
        $trimestre1Cal2->label = '1° Trimestre';
        $trimestre1Cal2->formato = 'numero';
        $trimestre1Cal2->readonly = true;

        $trimestre2Cal2 = new stdClass();
        $trimestre2Cal2->id = 'denA_trimestre2';
        $trimestre2Cal2->enDiv = true;
        $trimestre2Cal2->label = '2° Trimestre';
        $trimestre2Cal2->formato = 'numero';
        $trimestre2Cal2->readonly = true;

        $trimestre3Cal2 = new stdClass();
        $trimestre3Cal2->id = 'denA_trimestre3';
        $trimestre3Cal2->enDiv = true;
        $trimestre3Cal2->label = '3° Trimestre';
        $trimestre3Cal2->formato = 'numero';
        $trimestre3Cal2->readonly = true;

        $trimestre4Cal2 = new stdClass();
        $trimestre4Cal2->id = 'denA_trimestre4';
        $trimestre4Cal2->enDiv = true;
        $trimestre4Cal2->label = '4° Trimestre';
        $trimestre4Cal2->formato = 'numero';
        $trimestre4Cal2->readonly = true;

        $totalCal2 = new stdClass();
        $totalCal2->disabled = true;
        $totalCal2->id = 'den_total';
        $totalCal2->enDiv = true;
        $totalCal2->label = 'Total';
        $totalCal2->formato = 'numero';

        //********************************LINEA BASE Numerador Denominador****************************************************//
        $macNumerador = new stdClass();
        $macNumerador->readonly = true;
        $macNumerador->id = 'numerador';
        $macNumerador->enDiv = true;
        $macNumerador->label = 'Numerador';
        $macNumerador->formato = 'numero';

        $macDenominador = new stdClass();
        $macDenominador->readonly = true;
        $macDenominador->id = 'denominador';
        $macDenominador->enDiv = true;
        $macDenominador->label = 'Denominador';
        $macDenominador->formato = 'numero';

        //********************************VALOR DESEADO Y PROGRAMADO (RAZON Y TASA) *********************//
        $macDeseadoNumerador = new stdClass();
        $macDeseadoNumerador->id = 'deseadoNumerador';
        $macDeseadoNumerador->enDiv = true;
        $macDeseadoNumerador->label = 'Numerador';
        $macDeseadoNumerador->formato = 'numero';
        $macDeseadoNumerador->disabled = true;

        $macDeseadoDenominador = new stdClass();
        $macDeseadoDenominador->id = 'deseadoDenominador';
        $macDeseadoDenominador->enDiv = true;
        $macDeseadoDenominador->label = 'Denominador';
        $macDeseadoDenominador->formato = 'numero';
        $macDeseadoDenominador->disabled = true;

        //********************************RESULTADO PARCIAL *********************//
        $resParcialT1 = new stdClass();
        $resParcialT1->id = 'rpar_trimestre1';
        $resParcialT1->enDiv = true;
        $resParcialT1->label = '1° Trimestre';
        $resParcialT1->disabled = true;

        $resParcialT2 = new stdClass();
        $resParcialT2->id = 'rpar_trimestre2';
        $resParcialT2->enDiv = true;
        $resParcialT2->label = '2° Trimestre';
        $resParcialT2->disabled = true;

        $resParcialT3 = new stdClass();
        $resParcialT3->id = 'rpar_trimestre3';
        $resParcialT3->enDiv = true;
        $resParcialT3->label = '3° Trimestre';
        $resParcialT3->disabled = true;

        $resParcialT4 = new stdClass();
        $resParcialT4->id = 'rpar_trimestre4';
        $resParcialT4->enDiv = true;
        $resParcialT4->label = '4° Trimestre';
        $resParcialT4->disabled = true;

        $resParcialTo = new stdClass();
        $resParcialTo->id = 'totalParcialA';
        $resParcialTo->enDiv = true;
        $resParcialTo->label = 'Total';
        $resParcialTo->disabled = true;
        //********************************FIN RESULTADO PARCIAL *********************//

        $macT0 = new stdClass();
        $macT0->id = 't0';
        $macT0->enDiv = true;
        $macT0->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;
        $macT0->maxlength = 4;
        $macT0->formato = 'entero';
        $macT0->label = 'AÑO T0';
        $macT0->disabled = true;

        $macT0Valor = new stdClass();
        $macT0Valor->required = true;
        $macT0Valor->id = 't0Valor';
        $macT0Valor->enDiv = true;
        $macT0Valor->value = $metaActividad->getAnteriorValor();
        $macT0Valor->formato = 'numero';
        $macT0Valor->label = 'VALOR AÑO T0';
        $macT0Valor->disabled = true;

        $macT1 = new stdClass();
        $macT1->id = 't1';
        $macT1->enDiv = true;
        $macT1->value = !empty($metaActividad->getAnio()) ? $metaActividad->getAnio() : APP_EJERCICIO;
        $macT1->maxlength = 4;
        $macT1->formato = 'entero';
        $macT1->label = 'AÑO T1';
        $macT1->disabled = true;

        $macT1Valor = new stdClass();
        $macT1Valor->id = 't1Valor';
        $macT1Valor->enDiv = true;
        $macT1Valor->formato = 'numero';
        $macT1Valor->label = 'VALOR AÑO T1';
        $macT1Valor->disabled = true;

        //*********************************************************************************************//
        //semestral
        $trimestre1->value = $historicoMeta->getModificadoT1();
        $trimestre2->value = $historicoMeta->getModificadoT2();
        $trimestre3->value = $historicoMeta->getModificadoT3();
        $trimestre4->value = $historicoMeta->getModificadoT4();

        if ($datos['tri_id'] == 1 || $this->tri_id = 1) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1() != 0 ? $historicoMeta->getModificadoT1() : $historicoMeta->getNumeradorT1();
            $trimestre2Cal1->value = $historicoMeta->getNumeradorT2();
            $trimestre3Cal1->value = $historicoMeta->getNumeradorT3();
            $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($datos['tri_id'] == 2 || $this->tri_id = 2) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2() != 0 ? $historicoMeta->getModificadoT2() : $historicoMeta->getNumeradorT2();
            $trimestre3Cal1->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal1->value = $historicoMeta->getDenominadorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($datos['tri_id'] == 3 || $this->tri_id == 3) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2();
            $trimestre3Cal1->value = $historicoMeta->getModificadoT3() != 0 ? $historicoMeta->getModificadoT3() : $historicoMeta->getNumeradorT3();
            $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($datos['tri_id'] == 4 || $this->tri_id == 4) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2();
            $trimestre3Cal1->value = $historicoMeta->getModificadoT3();
            $trimestre4Cal1->value = $historicoMeta->getModificadoT4() != 0 ? $historicoMeta->getModificadoT4() : $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT4();
        }

        $anteriorIndicador = '';
        $original = '';

        $deseadoNumerador = '';
        $deseadoDenominador = '';
        $t0 = '';
        $t0Valor = '';
        $t1 = '';

        $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
        $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';

        $tipoM = new stdClass();
        $tipoM->id = 'mov1';
        $tipoM->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 1) {
            $tipoM->disabled = true;
            $tipoM->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 1) {
            $tipoM->disabled = true;
            $tipoM->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
        } else {
            $tipoM->disabled = true;
            $tipoM->opciones = [
                '' => '',
            ];
            $tipoM->selecciona = '';  // Aquí indicamos que la opción "A" es la seleccionada
        }
        $comboMovimiento = $this->ponCombo($tipoM);

        $tipoM1 = new stdClass();
        $tipoM1->id = 'mov2';
        $tipoM1->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 2) {
            $tipoM1->disabled = true;
            $tipoM1->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM1->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 2) {
            $tipoM1->disabled = true;
            $tipoM1->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM1->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
        } else {
            $tipoM1->disabled = true;
            $tipoM1->opciones = [
                '' => '',
            ];
            $tipoM1->selecciona = '';  // Aquí indicamos que la opción "A" es la seleccionada
        }
        $comboMovimiento1 = $this->ponCombo($tipoM1);

        $tipoM2 = new stdClass();
        $tipoM2->id = 'mov3';
        $tipoM2->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 3) {
            $tipoM2->disabled = true;
            $tipoM2->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM2->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 3) {
            $tipoM2->disabled = true;
            $tipoM2->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM2->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
        } else {
            $tipoM2->disabled = true;
            $tipoM2->opciones = [
                '' => '',
            ];
            $tipoM2->selecciona = '';  // Aquí indicamos que la opción "A" es la seleccionada
        }
        $comboMovimiento2 = $this->ponCombo($tipoM2);

        $tipoM3 = new stdClass();
        $tipoM3->id = 'mov4';
        $tipoM3->label = 'Tipo Movimiento';
        if ($datos['tmf_id'] == 'A' && $datos['tri_id'] == 3) {
            $tipoM3->disabled = true;
            $tipoM3->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM3->selecciona = 'A';  // Aquí indicamos que la opción "A" es la seleccionada
        } elseif ($datos['tmf_id'] == 'R' && $datos['tri_id'] == 4) {
            $tipoM3->disabled = true;
            $tipoM3->opciones = [
                ''  => 'Seleccione una opción',
                'A' => 'AMPLIACIÓN',
                'R' => 'REDUCCIÓN',
            ];
            $tipoM3->selecciona = 'R';  // Aquí indicamos que la opción "A" es la seleccionada
        } else {
            $tipoM3->disabled = true;
            $tipoM3->opciones = [
                '' => '',
            ];
            $tipoM3->selecciona = '';  // Aquí indicamos que la opción "A" es la seleccionada
        }
        $comboMovimiento3 = $this->ponCombo($tipoM3);

        switch ($umi) {
            case 2:
            case 3:
            case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                if ($trimestre1Cal1->value != null && $trimestre2Cal1->value != null && $trimestre3Cal1->value != null && $trimestre4Cal1->value != null && $trimestre1Cal2->value != null &&
                    $trimestre2Cal2->value != null && $trimestre3Cal2->value != null && $trimestre4Cal2->value != null) {

                    if ($umi == 2) {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format((($trimestre1Cal1->value / $trimestre1Cal2->value) * 100), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format((($trimestre2Cal1->value / $trimestre2Cal2->value) * 100), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format((($trimestre3Cal1->value / $trimestre3Cal2->value) * 100), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format((($trimestre4Cal1->value / $trimestre4Cal2->value) * 100), 4) : '0');
                    } else {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format(($trimestre1Cal1->value / $trimestre1Cal2->value), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format(($trimestre2Cal1->value / $trimestre2Cal2->value), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format(($trimestre3Cal1->value / $trimestre3Cal2->value), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format(($trimestre4Cal1->value / $trimestre4Cal2->value), 4) : '0');
                    }

                    if ($datos['tiv_id'] == 1) {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($macOriginal->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($macOriginal->value), 4);
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        }
                    } else {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4);
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        }

                    }
                } else {
                    if ($umi == 2) {
                        $resParcialT1->value = '0%';
                        $resParcialT2->value = '0%';
                        $resParcialT3->value = '0%';
                        $resParcialT4->value = '0%';
                        $resParcialTo->value = '0%';
                        $totalCal1->value = '0';
                        $totalCal2->value = '0';
                    } else {
                        $resParcialT1->value = '0';
                        $resParcialT2->value = '0';
                        $resParcialT3->value = '0';
                        $resParcialT4->value = '0';
                        $resParcialTo->value = '0';
                        $totalCal1->value = '0';
                        $totalCal2->value = '0';
                    }
                }

                $legend = 'VALOR DESEADO';
                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;

                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor LB';
                $macLbIndicador->disabled = true;
                $macOriginal->label = APP_EJERCICIO;
                $macOriginal->disabled = true;

                $macNumerador->value = $historicoMeta->getNumerador();
                $macDenominador->value = $historicoMeta->getDenominador();
                $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();

                $deseadoNumerador = '<td>' . $this->ponInput($macDeseadoNumerador) . '</td>';
                $deseadoDenominador = '<td>' . $this->ponInput($macDeseadoDenominador) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';

                break;
            case 5: // TASA

                if ($trimestre1Cal1->value != null && $trimestre2Cal1->value != null && $trimestre3Cal1->value != null && $trimestre4Cal1->value != null && $trimestre1Cal2->value != null &&
                    $trimestre2Cal2->value != null && $trimestre3Cal2->value != null && $trimestre4Cal2->value != null) {

                    $resParcialT1->value = (($trimestre1Cal1->value != 0 || $trimestre1Cal2->value != 0) ? number_format(((($trimestre1Cal1->value / $trimestre1Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format(((($trimestre2Cal1->value / $trimestre2Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT3->value = (($trimestre3Cal1->value != 0 || $trimestre3Cal2->value != 0) ? number_format(((($trimestre3Cal1->value / $trimestre3Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format(((($trimestre4Cal1->value / $trimestre4Cal2->value) - 1) * 100), 4) : '0');

                    if ($datos['tiv_id'] == 1) {
                        $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                        $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        $resParcialTo->value = (($totalCal1->value != 0 || $totalCal2->value != 0) ? number_format(((($totalCal1->value / $totalCal2->value) - 1) * 100), 4) . '%' : '0%');
                        $macT0Valor->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                    } else {
                        $resParcialTo->value = $resParcialT4->value . '%';
                        $totalCal1->value = (number_format(($trimestre4Cal1->value), 4));
                        $totalCal2->value = (number_format(($trimestre4Cal2->value), 4));
                        $macT0Valor->value = (number_format(($trimestre4Cal2->value), 4));
                    }
                    $resParcialT1->value .= '%';
                    $resParcialT2->value .= '%';
                    $resParcialT3->value .= '%';
                    $resParcialT4->value .= '%';
                } else {
                    $resParcialT1->value = '0%';
                    $resParcialT2->value = '0%';
                    $resParcialT3->value = '0%';
                    $resParcialT4->value = '0%';
                    $resParcialTo->value = '0%';
                    $totalCal1->value = '0';
                    $totalCal2->value = '0';
                }
                $legend = 'VALORES PROGRAMADOS';

                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;
                $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;

                $macLb->label = 'AÑO T-1';
                $macLbValor->label = 'VALOR AÑO T-1';
                $macAnterior->label = 'AÑO T0';
                $macAnteriorValor->label = 'VALOR AÑO T0';
                $macLbIndicador->label = 'VALOR INDICADOR';
                $macAnteriorIndicador->label = 'VALOR INDICADOR';
                $macOriginal->label = 'VALOR AÑO T1';

                $macLbIndicador->disabled = true;
                $macOriginal->disabled = true;
                $macAnteriorIndicador->disabled = true;

                $anteriorIndicador = '<td>' . $this->ponInput($macAnteriorIndicador) . '</td>';

                $t0 = '<td>' . $this->ponInput($macT0) . '</td>';
                $t0Valor = '<td>' . $this->ponInput($macT0Valor) . '</td>';
                $t1 = '<td>' . $this->ponInput($macT1) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';
                break;
            default:
                if ($trimestre1->value != null && $trimestre2->value != null && $trimestre3->value != null && $trimestre4->value != null) {
                    $resParcialT1->value = number_format(($trimestre1->value), 4);
                    $resParcialT2->value = number_format(($trimestre2->value), 4);
                    $resParcialT3->value = number_format(($trimestre3->value), 4);
                    $resParcialT4->value = number_format(($trimestre4->value), 4);

                    if ($datos['tiv_id'] == 1) {
                        $totalCal1->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                    } else {
                        $totalCal1->value = number_format(($resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT4->value), 4);
                    }
                    $resParcialT1->value .= '%';
                    $resParcialT2->value .= '%';
                    $resParcialT3->value .= '%';
                    $resParcialT4->value .= '%';
                } else {
                    $resParcialT1->value = '0%';
                    $resParcialT2->value = '0%';
                    $resParcialT3->value = '0%';
                    $resParcialT4->value = '0%';
                    $resParcialTo->value = '0%';
                    $total->value = '0%';
                }

                $legend = 'Valor Programado Anual';
                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor';

                $macOriginal->disabled = true;
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($total) . '</td>';

                break;
        }

        $trimestres .= '
            <fieldset class="col-md-8 col-xs-8">
                <legend><small>' . $legend . '</small></legend>
                <table class="table table-bordered table-striped ">
                    <tbody>
                        <tr>
                            ' . $deseadoNumerador . $deseadoDenominador . $t0 . $t0Valor . $t1 . $original . $anteriorIndicador . '
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <td colspan="12">
                            <div class="row row-cols-md-5">
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">1° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim1Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim1Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial1 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">2° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim2Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim2Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial2 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">3° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim3Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim3Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial3 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">4° Trimestre</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10">' . $comboMovimiento3 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim4Cal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $trim4Cal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $resParcial4 . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">TOTALES PARCIALES</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tbody>
                                                    <tr class="d-flex">
                                                        <td class="col-10"><br><br><br></td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td class="col-10">NUMERADOR</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $tCal1 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">DENOMINADOR</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $tCal2 . '</td>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <th class="col-10">RESULTADO PARCIAL</th>
                                                    </tr>
                                                    <tr class="d-flex">
                                                        <td>' . $totalParcial . '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
        ';

        $div .= $trimestres . '</div>';
        $datosMeta .= $div;
        $datosMeta .= '</td>
            </tr> </table>
        ';

        $this->mixHtml($datosMeta);

    }// fin metas

    public function tablaHistorialFolio()
    {
        $body = '';
        $this->setContenedor('divContenedorGeneralModal');

        $mod = new ModificacionMeta($this->prm);

        $sql = " 
            SELECT 
                DATE_FORMAT(b.usr_fecha,'%d/%m/%Y') fecha, time(b.usr_fecha) hora,mme_id, emo_nombre, 
                if(b.emo_id=3 or b.emo_id=5 or b.emo_id=7 or b.emo_id=9 or b.emo_id=11 or b.emo_id=13,  trim(replace(bit_observacion,'\"',' ')),'') as bit_observacion, b.usr_id,usr_nombre,b.emo_id,usr_paterno,usr_materno
            FROM 
                " . BD_BITACORA . ".bit_ModificacionMeta b
            INNER JOIN cat_EstadoModificacion using(emo_id)
            INNER JOIN scp_ModificacionMeta m using(omo_id,mme_gpo_id,mme_ur_id,mme_id)
            INNER JOIN cat_Usuario u on b.usr_id=u.usr_id
            INNER JOIN ctrlUsuario.cat_Usuario us ON u.usr_id=us.usr_id
            WHERE m.omo_id = ?
            AND m.mme_gpo_id = ? 
            AND m.mme_ur_id = ? 
            AND m.mme_id = ?
            AND bit_observacion NOT IN ('MOVIMIENTO AGREGADO','MOVIMIENTO ACTUALIZADO','MOVIMIENTO ELIMINADO')
            ORDER BY b.usr_fecha desc, bit_mme
        ";
        $params = [
            $mod->getOmo_id(),
            $mod->getMme_gpo_id(),
            $mod->getMme_ur_id(),
            $mod->getMme_id(),
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();

        while ($row = $this->extraer()) {
            $body .= '
            <tr>
                <td>' . $row['fecha'] . '</td>
                <td>' . $row['hora'] . '</td>
                <td>' . $row['emo_id'] . ' - ' . $row['emo_nombre'] . '</td>
                <td>' . $row['bit_observacion'] . '</td>
                <td>' . $row['usr_id'] . ' - ' . $row['usr_nombre'] . ' ' . $row['usr_paterno'] . ' ' . $row['usr_materno'] . '</td>
            </tr>';
        }// fin while

        $ur = new UnidadResponsable();
        $ur->setGpo_id($mod->getMme_gpo_id());
        $ur->setUr_id($mod->getMme_ur_id());
        $ur->select();

        $modalHistorial = '
            <div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"></h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <table  class="table table table-bordered table-hover"> 
                                    <tbody>
                                        <tr>
                                            <th width="15%">Unidad Responsable </th>
                                            <td colspan="5">' . $mod->getMme_gpo_id() . substr(str_repeat(0, 2) . $mod->getMme_ur_id(), - 2) . ' - ' . $ur->getNombre() . '</td>
                                        </tr>
                                        <tr>
                                            <th>Folio</th>
                                            <td width="20%">' . $mod->getMme_id() . '</td>
                                            <th width="10%">Tipo de Adecuación</th>
                                            <td width="28%">ADECUACION DE METAS</td>
                                            <th width="15%">Modalidad de adecuación</th>
                                            <td width="28%">EXTERNA</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <table  class="table table table-bordered table-hover"> 
                                    <thead> 
                                        <tr class="success"><th style="text-align:center;" colspan="5" style="">Historial del folio</th></tr>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Situación</th>
                                            <th>Comentario</th>
                                            <th>Clave única</th>
                                        </tr>
                                    </thead> 
                                    <tbody>' . $body . '</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cerrar" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ';

        $this->mixHtml($modalHistorial);
        $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
        ');

    }// fin tablaHistorialFolio

}// fin clase

/* **** FIN FUNCIONES ********************** */
if (isset($_SESSION[_S]['prm']['S']['5']['26']['1'])) {
    new ControladorConsultaFoliosModfMetas();
} else {
    WebResponse::sinPermisos();
}
