<?php

//inicio de sesion y llamada a las librerias
session_start();

require_once dirname(__FILE__) . '/../../lib/scp/vistaAdecuaciones.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuaciones.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scp/MaestroModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/DetalleModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/Presupuesto.php';
require_once dirname(__FILE__) . '/../../ent/enc_OrigenOperacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraMaestro.php';
require_once dirname(__FILE__) . '/../../ent/scp/BitacoraDetalle.php';
require_once dirname(__FILE__) . '/../../ent/scp/TipoModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/TipoMovimiento.php';
require_once dirname(__FILE__) . '/../../ent/scp/EstadoModificacion.php';

class ControladorModificacionFoliosAutorizados extends WebResponse
{
    public $vista;
    public $model;
    public $perfil;
    public $nivelJerarquico;
    public $grupo;
    public $ur;
    public $unidadEjecutora;
    public $usuario_id;
    public $subScertaria;
    public $ip;
    public $clasAdm_gpo;
    public $clasAdm_ur;
    public $modificacion_presupuestales;
    public $reimpresion_presupuestales;
    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();
        $this->vista = new vistaAdecuaciones();
        $this->model = new modelAdecuaciones();

        // Variables de sesión
        $this->perfil = isset($_SESSION[_S]['prf_id']) ? $_SESSION[_S]['prf_id'] : false;
        $this->nivelJerarquico = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->grupo = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : false;
        $this->ur = isset($_SESSION[_S]['ur_id’']) ? $_SESSION[_S]['ur_id’'] : false;
        $this->unidadEjecutora = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : false;
        $this->usuario_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;
        $this->subScertaria = isset($_SESSION[_S]['ssc_id']) ? $_SESSION[_S]['ssc_id'] : false;
        $this->ip = isset($_SESSION[_S]['ses-ip']) ? $_SESSION[_S]['ses-ip'] : false;
        $this->clasAdm_gpo = isset($_SESSION['clasAdm-gpo']) ? $_SESSION['clasAdm-gpo'] : false;
        $this->clasAdm_ur = isset($_SESSION['clasAdm-ur']) ? $_SESSION['clasAdm-ur'] : false;
        $this->modificacion_presupuestales = isset($_SESSION[_S]['prm']['S']['1']['5']['43']) ? true : false;
        $this->reimpresion_presupuestales = isset($_SESSION[_S]['prm']['S']['1']['5']['44']) ? true : false;

        switch ($this->nivelJerarquico) {
            case 1:
            case 2: //ur, ue
                break;
            default: //sefin
                switch ($this->metodo) {
                    case 'llenaTablaFolios':
                        $this->llenaTablaFolios();
                        break;
                    case 'addModFolio':
                        $this->addModFolio();
                        break; //Metodo para agregar nuevo folio de la Dependencia, R.H Y SEFIN
                    case 'agregaTipoModificacion':
                        $this->agregaTipoModificacion();
                        break; //Metodo para agregar el tipo de modificacion respecto a la modalidad elegida
                    case 'agregaTipoFolio':
                        $this->agregaTipoFolio();
                        break; //Metodo para agregar el tipo de folio respecto a la modalidad elegida
                    case 'abcFolios':
                        $this->abcFolios();
                        break; //Metodo para mostrar informacion referente a presupuesto segun la clave presupuestal elegida.
                    default:
                        $this->tablaFolios();
                        break;
                    case 'addFirma': //Metodo que muestra las firmas
                        $this->addFirma();
                        break;
                    case 'firmas': //Muestra las firmas
                        $this->firmas(); //
                        break;
                    case 'abcFirmas': //Registra las firmas
                        $this->abcFirmas();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    //Metodo que muestra la lista de folios creados por la UR
    private function tablaFolios()
    {

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->size = 3;
        $ur->class = ' limpiar';

        $omo_id = $tmd = $inputUr = $origen = '';
        switch ($this->nivelJerarquico) {
            case 1:
            case 2:
                switch ($this->perfil) {
                    case 'S':
                        $inputUr = $this->ponInput($ur);
                        $origen = "'A','C'";
                        $omo_id = 'omo_id ="C"';
                        $tmd = 'omo_id = "C"';
                        break;
                    default:
                        $origen = "'A'";
                        $omo_id = 'omo_id = "A"';
                        $tmd = 'omo_id = "A"';
                        break;
                }
                break;
            default:
                $inputUr = $this->ponInput($ur);
                $omo_id = 'omo_id IN("B","E")';
                $tmd = 'omo_id IN ("B","E")';
                switch ($this->subScertaria) {
                    case 2:
                        $tmd = 'tmd_id not in ("D","E")';
                        break;
                    case 3:
                        break;
                }
                break;
        }

        $folio = new stdClass();
        $folio->id = 'nmod_id';
        $folio->formato = 'entero';
        $folio->label = 'Folio';
        $folio->class = ' limpiar';
        $folio->maxlength = 6;

        $cvefin = new stdClass();
        $cvefin->id = 'ecf_id2';
        $cvefin->label = 'Clave de Financiamiento';
        $cvefin->sql = 'SELECT scf.cfi_clave, scf.cfi_nombre,cf.ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf
            INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento cf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN ' . BD_CTRL . '.scp_MaestroModificacion m using(ecf_id)
            GROUP BY ecf_id,scf.cfi_clave,scf.cfi_nombre
            ORDER BY cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio;';
        $cvefin->camposText = 'cfi_clave,cfi_nombre';
        $cvefin->soloText = true;
        $cvefin->class = ' limpiar';
        $cvefin->mensaje = 'Seleccione una opcion de la lista';
        $cvefin->camposVal = 'ecf_id';
        $cvefin->search = true;

        $tipoMod = new stdClass();
        $tipoMod->id = 'tmd_id';
        $tipoMod->label = 'Tipo de Adecuación';
        $tipoMod->mensaje = 'Seleccione una opcion de la lista';
        $tipoMod->sql = 'SELECT t.tmd_id,t.tmd_nombre
            FROM ' . BD_CTRL . '.cat_TipoModificacion t
            INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op using(tmd_id)
            WHERE ' . $tmd . '
            GROUP BY tmd_id;';
        $tipoMod->class = ' limpiar';

        $tipoFolio = new stdClass();
        $tipoFolio->id = 'tfm_id';
        $tipoFolio->label = 'Tipo de Folio';
        $tipoFolio->mensaje = 'Seleccione una opcion de la lista';
        $tipoFolio->sql = 'SELECT f.tfm_id,f.tfm_nombre
            FROM ' . BD_CTRL . '.cat_TipoFolioModificacion f
            INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op using(tfm_id)
            WHERE ' . $omo_id . '
            GROUP BY tfm_id;';
        $tipoFolio->class = ' limpiar';

        $estado = new stdClass();
        $estado->id = 'emo_id';
        $estado->label = 'Estado';
        $estado->mensaje = 'Seleccione una opcion de la lista';
        $estado->sql = 'SELECT emo_id, emo_nombre FROM ' . BD_CTRL . '.cat_EstadoModificacion;';
        $estado->class = ' limpiar';
        $estado->search = true;

        $oficioSolicitud = new stdClass();
        $oficioSolicitud->id = 'mod_oficioSolicitud';
        $oficioSolicitud->label = 'Oficio de Solicitud';
        $oficioSolicitud->size = 35;
        $oficioSolicitud->class = ' limpiar';

        $importe = new stdClass();
        $importe->id = 'importe';
        $importe->label = 'Importe';
        $importe->class = ' limpiar';

        $buscar = new stdClass();
        $buscar->id = 'buscar';
        $buscar->value = 'Buscar';
        $buscar->icon = 'fa fa-search me-1';
        $buscar->color = 'gris';
        $buscar->class = 'me-2';

        $limpiar = new stdClass();
        $limpiar->id = 'Clean';
        $limpiar->value = 'Limpiar';
        $limpiar->icon = 'trash me-1';
        $limpiar->color = 'amarillo';
        $limpiar->class = 'me-2';

        $html = '';
        $html .= '
                <div class="container mt-3">
                    <div class="row">
                        <div class="col-md-3 col-xs-6 mb-4">
                            ' . $inputUr . '
                        </div>
                        <div class="col-md-3 col-xs-6 ">
                            ' . $this->ponInput($folio) . '
                        </div>
                        <div class="col-md-3 col-xs-6 ">
                            ' . $this->templateCombo($cvefin) . '
                        </div>
                        <div class="col-md-3 col-xs-6 ">
                            ' . $this->templateCombo($tipoMod) . '
                        </div>
                    </div>
                </div>
                <div class="container mt-3">
                    <div class="row">
                            <fieldset class="col-md-3 col-xs-6 mb-4">
                                ' . $this->templateCombo($tipoFolio) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-6">
                                ' . $this->templateCombo($estado) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-6">
                                ' . $this->ponInput($oficioSolicitud) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-6">
                                ' . $this->ponInput($importe) . '
                            </fieldset>
                    </div>
                </div>
                <div class="container mt-3">
                    <div class="row">
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-3 offset-md-10 mb-4">
                            ' . $this->ponBoton($buscar) . '
                            ' . $this->ponBoton($limpiar) . '
                        </fieldset>
                    </div>
                </div>
        ';
        $script = $this->getScript();

        $this->mixHtml($this->agregaTools(''));

        $tabla = new stdClass();
        $tabla->id = 'tbl-Folios';
        $tabla->titulo = 'Adecuaciones Presupuestales';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->tools = $html;
        $tabla->comunica->ur_clave = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ?
        $this->prm->ur_clave : '{$("#ur").val()}';
        $tabla->comunica->mod_id = (isset($this->prm->mod_id) && $this->prm->mod_id != '') ?
        $this->prm->mod_id : '{$("#nmod_id").val()}';
        $tabla->comunica->tmd_id = (isset($this->prm->tmd_id) && $this->prm->tmd_id != '') ?
        $this->prm->tmd_id : '{$("#tmd_id").val()}';
        $tabla->comunica->tfm_id = (isset($this->prm->tfm_id) && $this->prm->tfm_id != '') ?
        $this->prm->tfm_id : '{$("#tfm_id").val()}';
        $tabla->comunica->ecf_id2 = (isset($this->prm->ecf_id2) && $this->prm->ecf_id2 != '') ?
        $this->prm->ecf_id2 : '{$("#ecf_id2 option:selected").val()}';
        $tabla->comunica->emo_id = (isset($this->prm->emo_id) && $this->prm->emo_id != '') ?
        $this->prm->emo_id : '{$("#emo_id").val()}';
        $tabla->comunica->importe = (isset($this->prm->importe) && $this->prm->importe != '') ?
        $this->prm->importe : '{$("#importe").val()}';
        $tabla->comunica->mod_oficioSolicitud = (isset($this->prm->mod_oficioSolicitud) && $this->prm->mod_oficioSolicitud != '') ?
        $this->prm->mod_oficioSolicitud : '{$("#mod_oficioSolicitud").val()}';
        $tabla->comunica->omo_id = $origen;
        $tabla->comunica->sub = (isset($this->prm->sub) && $this->prm->sub != '') ?
        $this->prm->sub : '{$("#sub").val()}';
        $tabla->comunica->modalidad = (isset($this->prm->modalidad) && $this->prm->modalidad != '') ?
        $this->prm->modalidad : '{$("#modalidad").val()}';
        $tabla->comunica->metodo = 'llenaTablaFolios';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->width = '10px';
        $th->texto = 'Ver / Editar ';
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

        //Clave de financiamiento
        $th = new stdClass();
        $th->texto = 'Clave de financiamiento';
        array_push($tr, $th);

        //Justificación del folio
        $th = new stdClass();
        $th->texto = 'Justificación';
        array_push($tr, $th);

        //Tipo del folio
        $th = new stdClass();
        $th->texto = 'Tipo';
        array_push($tr, $th);

        //Importe del folio
        $th = new stdClass();
        $th->texto = 'Importe';
        array_push($tr, $th);

        //Estado del folio
        $th = new stdClass();
        $th->texto = 'Estado';
        array_push($tr, $th);

        $trbody = [];

        //contenido ver
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa-regular fa-eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'omo_id';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
        $td->attr->enc_oop = 'enc_oop';
        $td->attr->ecf_id = 'ecf_id';
        $td->class = 'addModFolio';
        array_push($trbody, $td);

        //UR
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['mod_gpo_id', 'mod_ur_id'];
        array_push($trbody, $td);

        //Numero de folio
        $td = new stdClass();
        $td->campoTxt = 'mod_id';
        array_push($trbody, $td);

        //Modalidad del folio
        $td = new stdClass();
        $td->campoTxt = 'mmd_id';
        array_push($trbody, $td);

        //Fecha de creación del folio
        $td = new stdClass();
        $td->campoTxt = 'mod_fechaFolio';
        $td->formato = 'fecha';
        array_push($trbody, $td);

        //Fecha de autorización del folio
        $td = new stdClass();
        $td->campoTxt = 'mod_fechaAutorizacion';
        array_push($trbody, $td);

        //Tipo de modificación
        $td = new stdClass();
        $td->campoTxt = 'tmd_nombre';
        array_push($trbody, $td);

        //Clave de financiamiento
        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($trbody, $td);

        //Justificación
        $td = new stdClass();
        $td->campoTxt = 'mod_concepto';
        array_push($trbody, $td);

        //Tipo de folio
        $td = new stdClass();
        $td->campoTxt = 'tfm_nombre';
        array_push($trbody, $td);

        //importe de folio
        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
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

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');

        $this->recargaCombo($cvefin);
        $this->recargaCombo($tipoMod);
        $this->recargaCombo($tipoFolio);
        $this->recargaCombo($estado);

        if (isset($this->prm->mostrar)) {
            $this->llenaTablaFolios();
        }

        $this->mixStyle('
            div.estado {float: left; }
            div.estado + span {
                font-size:.8em;
                margin: 0 0 0 10px;
                float:left;
            }
        ');

        $this->mixScript($script . '
            $("' . (($this->nivelJerarquico == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarIndex").click(); e.stopPropagation(); });
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
    }

    private function llenaTablaFolios()
    {
        $params = [];
        $filtro = '';
        $having = '';

        if (isset($this->prm->omo_id) && $this->prm->omo_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' omo_id= :omo_id ';
            $params[':omo_id'] = $this->prm->omo_id;
        }

        if (isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) < 3 && $this->prm->ur_clave != '') {
            $this->mixScript('alert("Numero de UR invalido"); $("#ur").focus();');
        }

        if (isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) == 3 && $this->prm->ur_clave != '') {
            $gpo_id = substr($this->prm->ur_clave, 0, 1);
            $ur_id = substr($this->prm->ur_clave, 1);
            $filtro .= 'where';
            $filtro .= ' m.mod_gpo_id= :mod_gpo_id AND m.mod_ur_id= :mod_ur_id ';
            $params[':mod_gpo_id'] = $gpo_id;
            $params[':mod_ur_id'] = $ur_id;
        }

        if (isset($this->prm->mod_id) && $this->prm->mod_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' m.mod_id= :mod_id';
            $params[':mod_id'] = $this->prm->mod_id;
        }

        if (isset($this->prm->ecf_id2) && $this->prm->ecf_id2 != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' m.ecf_id= :ecf_id ';
            $params[':ecf_id'] = $this->prm->ecf_id2;
        }
        if (isset($this->prm->tfm_id) && $this->prm->tfm_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' tfm_id= :tfm_id ';
            $params[':tfm_id'] = $this->prm->tfm_id;
        }

        if (isset($this->prm->emo_id) && $this->prm->emo_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' emo_id= :emo_id ';
            $params[':emo_id'] = $this->prm->emo_id;
        }

        if (isset($this->prm->tmd_id) && $this->prm->tmd_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' tmd_id= :tmd_id ';
            $params[':tmd_id'] = $this->prm->tmd_id;
        }

        if (isset($this->prm->importe) && $this->prm->importe != '') {
            $having .= 'having';
            $having .= ' importe= :importe ';
            $params[':importe'] = $this->prm->importe;
        }

        if (isset($this->prm->mod_oficioSolicitud) && $this->prm->mod_oficioSolicitud != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' mod_oficioSolicitudSefin= :mod_ofi ';
            $params[':mod_ofi'] = $this->prm->mod_oficioSolicitud;
        }

        if ($this->nivelJerarquico <= 2 && $this->perfil != 'S') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' m.mod_gpo_id= :mod_gpo_id and m.mod_ur_id= :mod_ur_id ';

        }

        $subsecretaria = '';
        $tcp = '';
        $gpo_id = isset($this->prm->ur_clave) ? substr($this->prm->ur_clave, 0, 1) : '';
        $ur_id = isset($this->prm->ur_clave) ? substr($this->prm->ur_clave, 1) : '';

        if ($gpo_id . $ur_id == 115 || isset($this->prm->tmd_id) == 'C' || isset($this->prm->tmd_id) == 'D' || isset($this->prm->tmd_id) == 'A') {
        } else {
            $tcp = ' and tcp_id!= :tcp_id ';
            $params[':tcp_id'] = 'P';
        }
        switch ($this->nivelJerarquico) {
            case 1:
            case 2:
                $tcp = ' and tcp_id!="P" ';
                break;
            default:
                switch ($this->subScertaria) {
                    case 2:
                        if ($filtro != '') {
                            $filtro .= ' and';
                        } else {
                            $filtro .= 'where';
                        }

                        $subsecretaria = ' mod_Subsecretaria=2 and tcp_id!="P"';
                        break;
                    case 3:
                        if ($filtro != '') {
                            $filtro .= ' and';
                        } else {
                            $filtro .= 'where';
                        }

                        $subsecretaria = ' mod_Subsecretaria=1 ';
                        break;
                }
                break;
        }

        $sql = "
            SELECT
            IF(tmd_id='A',FORMAT(SUM(dmo_importe)/2,2),FORMAT(SUM(dmo_importe),2)) importe,
            CONCAT(mod_gpo_id,mod_ur_id) AS ur,omo_id,mod_gpo_id,mod_ur_id,mod_id,
            mod_concepto, mod_fechaFolio,IF(mod_fechaAutorizacion IS NULL,'',
                DATE_FORMAT(mod_fechaAutorizacion,\"%d/%m/%Y %H:%i:%s\")) mod_fechaAutorizacion,cfi_clave,
            tmd_nombre,tfm_nombre, emo_id, emo_nombre, emo_color,mmd_id,enc_oop
            FROM
                " . BD_CTRL . '.scp_MaestroModificacion m
                INNER JOIN(
                    SELECT enc.ecf_id,cf.cfi_clave
                    FROM ' . BD_CTRL . '.enc_ClaveFinanciamiento enc
                    INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                )AS cfi USING(ecf_id)
                LEFT JOIN ' . BD_CTRL . '.scp_DetalleModificacion dm USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion o USING(enc_oop,omo_id)
                INNER JOIN ' . BD_CTRL . '.cat_TipoModificacion tm USING(tmd_id)
                INNER JOIN ' . BD_CTRL . '.cat_EstadoModificacion em USING(emo_id)
                INNER JOIN ' . BD_CTRL . ".cat_TipoFolioModificacion tfm USING(tfm_id)
                $filtro $subsecretaria $tcp
            GROUP BY omo_id,mod_gpo_id,mod_ur_id,mod_id,cfi_clave
            $having ;";
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Folios';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No se encontraron registros", "warning");');
        }

        $this->mixScript('
            $(".addModFolio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addModFolio",
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    omo_id:$(this).attr("omo_id"),
                    mod_id:$(this).attr("mod_id"),
                    enc_oop:$(this).attr("enc_oop")
                });
                return false;
            });
        ');
    }

    //Metodo para agregar nuevo folio de la Dependencia
    private function addModFolio()
    {
        //si existe el parametro mod_id es edicion
        if (isset($this->prm->mod_id)) {
            $mod = new MaestroModificacion($this->prm);
            $origenOp = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
            $abc = 'c';
        } else {
            $mod = new MaestroModificacion();
            $origenOp = new OrigenOperacion();
            $abc = 'a';
        }

        //INICIO BOTONES
        $btnRegresarTablaFolios = new stdClass();
        $btnRegresarTablaFolios->id = 'btnRegresarTablaFolios';
        $btnRegresarTablaFolios->class = 'me-2';
        $btnRegresarTablaFolios->color = 'gris';
        $btnRegresarTablaFolios->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresarTablaFolios->text = 'Regresar';
        $btnRegresarTablaFolios->value = 'Regresar';

        //FIN BOTONES
        $numeroFolio = new stdClass();
        $numeroFolio->id = 'folio';
        $numeroFolio->formato = 'entero';
        $numeroFolio->enDiv = true;
        $numeroFolio->maxlength = 6;
        $numeroFolio->label = 'Número de Folio';
        $numeroFolio->siExiste = false;

        $numeroFolio->datosExtra = new stdClass();
        $numeroFolio->datosExtra->omo_id = $this->prm->omo_id;
        $numeroFolio->mensajeValido = 'Folio válido';
        $numeroFolio->mensajeNoValido = 'El folio ya existe';
        $numeroFolio->required = true;
        if ($mod->getMod_id() != '') {
            $numeroFolio->readonly = true;
        }
        $numeroFolio->value = $mod->getMod_id();

        $numFolio = '';
        switch ($this->nivelJerarquico) {
            case 1:
            case 2:
                if ($mod->getMod_id() != '') {
                    $numFolio = '<fieldset class="col-md-2 col-xs-2">
                        ' . $this->ponInput($numeroFolio) . '
                    </fieldset>';
                }
                break;
            default:
                $numFolio = '<fieldset class="col-md-3 col-xs-3">
                                ' . $this->ponInput($numeroFolio) . '
                            </fieldset>';
                break;
        }

        $modalidadModificacion = new stdClass();
        $modalidadModificacion->id = 'mmd_id';
        $modalidadModificacion->name = 'mmd_id';
        $modalidadModificacion->label = 'Modalidad de Adecuación';
        if ($this->nivelJerarquico <= 2 && $this->prm->omo_id != 'C' && $this->prm->omo_id != 'D') { //Si es nivel ur ó ue entonces muestra las dos modalidades para agregar el folio, de lo contrario solo mostrara externas.
            $modalidadModificacion->sql = 'SELECT mmd_id,mmd_nombre FROM ' . BD_CTRL . '.cat_ModalidadModificacion';
        } else {
            $modalidadModificacion->sql = 'SELECT mmd_id,mmd_nombre FROM ' . BD_CTRL . '.cat_ModalidadModificacion';
        }
        $modalidadModificacion->vertical = true;
        $modalidadModificacion->selecciona = ($origenOp->getMmd_id() != '') ? $origenOp->getMmd_id() : 'E';
        $modalidadModificacion->required = true;
        $modalidadModificacion->value = $origenOp->getMmd_id();
        if ($origenOp->getMmd_id() != '') {
            $modalidadModificacion->disabled = true;
        }

        $justificacion = new stdClass();
        $justificacion->id = 'concepto';
        $justificacion->enDiv = true;
        $justificacion->maxlength = '600';
        $justificacion->required = true;
        $justificacion->texto = $mod->getConcepto();

        $fecha = new stdClass();
        $fecha->id = 'fechaFolio';
        $fecha->enDiv = true;
        $fecha->required = true;
        $fecha->label = 'Fecha de creación';
        $fecha->value = $mod->getFechaFolio();
        $ponFecha = '';
        if ($mod->getFechaFolio() != '') {
            $fecha->readonly = true;
            $ponFecha = '<fieldset class="col-md-4 col-xs-4">
                            ' . $this->ponInput($fecha) . '
                        </fieldset>';
        }

        $oficioSolicitud = new stdClass();
        $oficioSolicitud->id = 'oficioSolicitudSefin';
        $oficioSolicitud->enDiv = true;
        $oficioSolicitud->label = 'Oficio de Solicitud Ejecutor de gasto';
        $oficioSolicitud->maxlength = '35';
        $oficioSolicitud->required = true;
        $oficioSolicitud->value = $mod->getOficioSolicitudSefin();

        $oficioAutorizacion = new stdClass();
        $oficioAutorizacion->id = 'oficioSefin';
        $oficioAutorizacion->enDiv = true;
        $oficioAutorizacion->label = 'Oficio de Autorización SEFIN';
        $oficioAutorizacion->maxlength = '35';
        $oficioAutorizacion->required = true;
        $oficioAutorizacion->value = $mod->getOficioSefin();

        $elimina = false;
        $consecutivo = ''; //Para agregar o no el consecutivo del folio en caso de que el nivel jerarquico sea >= 3
        $botonGuardarEimprimir = '';
        $Op = ($origenOp->getOop() != '') ? $origenOp->getOop() : 0;
        $deshabilitarRadios = (!$elimina) ? ',deshabilitado:true' : ''; //deshabilitar radios de tipo modificacion y de tipo de folio.
        $usarComboUe = '';
        $leyenda = '';
        $datosUe = '';
        switch ($this->nivelJerarquico) {
            case 1:
            case 2:
                if ($this->grupo . $this->ur == 116) {
                    $comboUe = new stdClass();
                    $comboUe->id = 'mod_ue_id';
                    $comboUe->sql = 'SELECT ue_id,ue_nombre FROM ' . BD_CTRL . '.cat_UnidadEjecutora WHERE gpo_id=3 and ur_id=15 ' .
                        (($abc == 'c') ? 'and ue_id=' . $mod->getMod_ue_id() . '' : '') . ' order by ue_id;';
                    $comboUe->required = true;

                    $leyenda = '<legend>Unidad Ejecutora</legend>';

                    switch ($this->nivelJerarquico) {
                        case 1:
                            $datosUe = 'mod_ue_id:' . $this->unidadEjecutora . ',';
                            break;
                        case 2:
                            $usarComboUe = $this->ponCombo($comboUe);
                            $datosUe = 'mod_ue_id:$("#mod_ue_id").val(),';
                            break;
                    }
                }
                $justificacion->disabled = 'disabled';
                break;
            default: //SEFIN
                $this->mixScript('$("#combour").val("' . $mod->getMod_gpo_id() . $mod->getMod_ur_id() . '").change();');
                if ($this->modificacion_presupuestales == true || $this->reimpresion_presupuestales == true) {
                    $botonGuardarEimprimir = '';
                    switch ($mod->getEmo_id()) {
                        case 14:
                        case 15:

                            $btnGuardarFolio = new stdClass();
                            $btnGuardarFolio->id = 'btnGuardarFolio';
                            $btnGuardarFolio->icon = 'fa-solid fa-floppy-disk me-1';
                            $btnGuardarFolio->class = 'me-2';
                            $btnGuardarFolio->color = 'verde';
                            $btnGuardarFolio->text = 'Guardar';
                            $btnGuardarFolio->value = 'Guardar';

                            $imprimir = '';
                            if ($this->reimpresion_presupuestales == true) {

                                $btnImprimir = new stdClass();
                                $btnImprimir->id = 'btnImprimir';
                                $btnImprimir->class = 'me-2 btnImprimir';
                                $btnImprimir->color = 'rojo';
                                $btnImprimir->icon = 'file-pdf me-1';
                                $btnImprimir->text = ' Reimpresión de Folio';
                                $btnImprimir->value = ' Reimpresión de Folio';

                                $imprimir = $this->ponBoton($btnImprimir);
                            }

                            $botonGuardarEimprimir = $this->ponBoton($btnGuardarFolio) . ' ' . $imprimir;
                            break;
                        default:
                            $oficioSolicitud->readonly = true;
                            $oficioAutorizacion->readonly = true;
                            $justificacion->disabled = 'disabled';
                            break;
                    }
                } else {
                    $oficioSolicitud->readonly = true;
                    $oficioAutorizacion->readonly = true;
                    $justificacion->disabled = 'disabled';
                }

                $consecutivo = '
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $this->ponInput($oficioAutorizacion) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $this->ponInput($oficioSolicitud) . '
                    </fieldset>
                ';

                $datosConsecutivo = '
                    ,oficioSolicitudSefin:$("#oficioSolicitudSefin").val()
                    ,oficioSefin:$("#oficioSefin").val()
                ';

                if (isset($this->clasAdm_gpo) && isset($this->clasAdm_ur) && $this->clasAdm_gpo . $this->clasAdm_ur == 116) {

                    $comboUe = new stdClass();
                    $comboUe->id = 'mod_ue_id';
                    $comboUe->sql = 'SELECT ue_id,ue_nombre FROM ' . BD_CTRL . '.cat_UnidadEjecutora WHERE gpo_id=3 and ur_id=15 ' . (($abc == 'c') ? 'and ue_id=' . $mod->getMod_ue_id() . '' : '') . ' order by ue_id;';
                    $comboUe->label = 'Unidad Ejecutora';
                    $usarComboUe = $this->ponCombo($comboUe);
                    $datosUe = 'mod_ue_id:$("#mod_ue_id").val(),';
                }

                break;
        }

        $this->mixHtml('
            <form id="Folio">
                <div class="container-fluid">
                    <legend><span style = "font-size:14px;" class="badge bg-secondary">Datos Generales</span></legend>
                    <div class="row mb-4">
                            ' . $numFolio . '
                            ' . $ponFecha . '
                        <fieldset id="filaUe" class="col-md-4 col-xs-4">
                            ' . $usarComboUe . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <div class="col col-md-3 col-xs-3">
                            ' . $this->ponCombo($modalidadModificacion) . '
                        </div>
                        <div class="col col-md-3 col-xs-3" id="tmd_tfm"></div>
                        <div class="col " id="ssc"></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col col-md-3 col-xs-3" id="tf2"></div>
                    </div>
                    <div class="row mb-4">
                        <fieldset class="col col-md-6 col-xs-6">
                            ' . $this->ponTextArea($justificacion) . '
                        </fieldset>
                        ' . $consecutivo . '
                    </div>
                </div>
            </form>
        ');

        $this->agregaTools($this->ponBoton($btnRegresarTablaFolios) . ' ' . $botonGuardarEimprimir);

        $this->mixScript('

            if($("#tmd_tfm").length){
                comunica("' . $this->pantalla . '",{
                    metodo:"agregaTipoModificacion",
                    mmd_id: $(this).find("option:selected").val(),
                    enc_oop:' . $Op . '
                    ' . $deshabilitarRadios . '
                    ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : " ,omo_id:'A' ") . '
                    ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                    ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                    ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                });
            }

            $("#btnGuardarFolio").click(function(){
                var mov = (($("#mov").length) ? $("#mov").find("option:selected").val():"");
                comunica("' . $this->pantalla . '",{
                    metodo:"abcFolios",
                    abc:"c",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mov:mov,
                    mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    mod_id:"' . $mod->getMod_id() . '",
                    concepto:$("#concepto").val()
                    ' . $datosConsecutivo . '
                    },true
                );
                return false;
            });

            $("#btnImprimir").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addFirma",
                    emo_id:"' . $mod->getEmo_id() . '",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    mod_id:"' . $mod->getMod_id() . '",
                    mod_tipoMovimiento:$(this).attr("mod_tipoMovimiento"),
                });
                return false;
            });

            $("#btnRegresarTablaFolios").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    mostrar:true
                    ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : " ,omo_id:'A' ") . '
                    ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                    ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                    ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                    ' . (isset($this->prm->ur_clave) && !empty($this->prm->ur_clave) ? ',ur_clave:"' . $this->prm->ur_clave . '"' : '') . '
                });
                return false;
            });
        ');
    }

    private function agregaTipoModificacion()
    {
        $params = [];
        $this->setContenedor('tmd_tfm');
        $sql = '';
        if (isset($this->prm->enc_oop) && $this->prm->enc_oop != 0) {
            $origenOp = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        } else {
            $origenOp = new OrigenOperacion();
        }

        switch ($this->nivelJerarquico) {
            case 1:
            case 2:
                $params = [];
                $sql = '
                    SELECT t.tmd_id,t.tmd_nombre,op.omo_id,op.tfm_id,op.mmd_id,op.enc_oop
                    FROM ' . BD_CTRL . '.cat_TipoModificacion t
                    INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING(tmd_id)
                    WHERE op.mmd_id= :mmd_id AND op.omo_id= :omo_id
                    GROUP BY t.tmd_id
                    ORDER BY t.tmd_orden;';
                $params = [':mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id : 'E'),
                    ':omo_id'        => (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A')];
                break;
            default:
                switch ($this->subScertaria) {
                    case 2: //planeacion
                        $params = [];
                        $sql = '
                            SELECT t.tmd_id,t.tmd_nombre,op.omo_id,op.tfm_id,op.mmd_id,op.enc_oop
                            FROM ' . BD_CTRL . '.cat_TipoModificacion t
                            INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING(tmd_id)
                            WHERE op.mmd_id= :mmd_id AND op.omo_id= :omo_id
                            GROUP BY t.tmd_id,op.tfm_id,op.enc_oop
                            ORDER BY t.tmd_orden;';
                        $params = [':mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id : 'E'),
                            ':omo_id'        => (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A')];
                        break;
                    case 3: //egresos
                        $params = [];
                        $sql = '
                            SELECT t.tmd_id,t.tmd_nombre,op.omo_id,op.tfm_id,op.mmd_id,op.enc_oop
                            FROM ' . BD_CTRL . '.cat_TipoModificacion t
                            INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING(tmd_id)
                            WHERE op.mmd_id= :mmd AND op.omo_id= :omo
                            GROUP BY t.tmd_id,op.tfm_id,op.enc_oop
                            ORDER BY t.tmd_orden;';
                        $params = [':mmd' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id : 'E'),
                            ':omo'        => (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A')];
                        break;
                }
                break;
        }

        $tipoModificacion = new stdClass();
        $tipoModificacion->id = 'tmd_id';
        $tipoModificacion->label = 'Tipo de Adecuación';
        $tipoModificacion->camposVal = 'tmd_id';
        $tipoModificacion->camposText = 'tmd_nombre';
        $tipoModificacion->comoAtr = 'omo_id,tmd_id,mmd_id,tfm_id,enc_oop';
        $tipoModificacion->sql = $sql;
        $tipoModificacion->params = $params;
        $tipoModificacion->selecciona = $origenOp->getTmd_id();
        $tipoModificacion->required = true;
        $tipoModificacion->value = $origenOp->getTmd_id();
        if ($origenOp->getTmd_id() != '' && isset($this->prm->deshabilitado)) {
            $tipoModificacion->disabled = true;
        }

        $this->mixHtml('<fieldset  class="col ">
                            ' . $this->ponCombo($tipoModificacion) .
            '</fieldset>');

        if (isset($this->prm->deshabilitado)) {
            $this->prm->deshabilitado = $this->prm->deshabilitado;
        }

        $this->prm->omo_id = (isset($this->prm->omo_id) && !empty($this->prm->omo_id)) ? $this->prm->omo_id : 'A';
        $this->prm->enc_oop = (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop)) ? $this->prm->enc_oop : '';
        $this->prm->mmd_id = (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id)) ? $this->prm->mmd_id : 'E';
        $this->agregaTipoFolio();

        $this->mixScript('
            $("#tmd_id").change(function(){
                switch($("#mmd_id").find("option:selected").val()){
                    case "I":
                        comunica("' . $this->pantalla . '",{
                            metodo:"agregaTipoFolio",
                            tmd_id: $("#tmd_id").find("option:selected").val()
                            ' . (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop) ? ',enc_oop:"' . $this->prm->enc_oop . '"' : '') . '
                            ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : 'A') . '
                            ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                            ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                            ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                            ' . (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? ',mmd_id:"' . $this->prm->mmd_id . '"' : '') . '
                            ' . (isset($this->prm->deshabilitado) && !empty($this->prm->deshabilitado) ? ',deshabilitado:"' . $this->prm->deshabilitado . '"' : '') . '
                        });
                    break;
                    case "E":
                        comunica("' . $this->pantalla . '",{
                            metodo:"agregaTipoFolio",
                            tmd_id: $("#tmd_id").find("option:selected").val(),
                            agregaTipo:(($("#tmd_id").find("option:selected").val()=="B") ? $("#tmd_id").find("option:selected").val():0)
                            ' . (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop) ? ',enc_oop:"' . $this->prm->enc_oop . '"' : '') . '
                            ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : 'A') . '
                            ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                            ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                            ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                            ' . (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? ',mmd_id:"' . $this->prm->mmd_id . '"' : '') . '
                            ' . (isset($this->prm->deshabilitado) && !empty($this->prm->deshabilitado) ? ',deshabilitado:"' . $this->prm->deshabilitado . '"' : '') . '
                        });
                    break;
                }
                return false;
            });
            ' . (isset($this->prm->enc_oop) && ($this->prm->enc_oop != 0) && $origenOp->getTmd_id() != '' ? '$("#tmd_id").change();' : '') . '
        ');
    }

    private function agregaTipoFolio()
    {
        $sql2 = '';
        $params = [];
        $this->setContenedor('ssc');
        if (isset($this->prm->tmd_id) || (isset($this->prm->agregaTipo) && $this->prm->agregaTipo == 'B')) {
            $this->setContenedor('tf');
        }
        if (isset($this->prm->enc_oop) && $this->prm->enc_oop != 0) {
            $origenOp = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        } else {
            $origenOp = new OrigenOperacion();
        }

        switch ($this->nivelJerarquico) {
            case 1:
            case 2:
                $params = [];
                $sql2 = '
                    SELECT f.tfm_id,f.tfm_nombre,op.omo_id,op.tmd_id,op.mmd_id,op.enc_oop
                    FROM ' . BD_CTRL . '.cat_TipoFolioModificacion f
                    INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING(tfm_id)
                    WHERE op.mmd_id= :mmd_id AND op.omo_id= :omo_id
                    ' . (isset($this->prm->tmd_id) ? ' and op.tmd_id="' . $this->prm->tmd_id . '"' : '') . '
                    GROUP BY f.tfm_id,op.tmd_id;';
                $params = [':mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id : 'E'),
                    ':omo_id'        => (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A')];
                break;
            default:
                switch ($this->subScertaria) {
                    case 2:
                        $params = [];
                        $sql2 = '
                            SELECT f.tfm_id,f.tfm_nombre,op.omo_id,op.tmd_id,op.mmd_id,op.enc_oop
                            FROM ' . BD_CTRL . '.cat_TipoFolioModificacion f
                            INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING(tfm_id)
                            WHERE op.mmd_id= :mmd_id AND op.omo_id= :omo_id
                            GROUP BY f.tfm_id,op.tmd_id,op.enc_oop;';
                        $params = [':mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id : 'E'),
                            ':omo_id'        => (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A')];
                        break;
                    case 3:
                        $params = [];
                        $sql2 = '
                            SELECT f.tfm_id,f.tfm_nombre,op.omo_id,op.tmd_id,op.mmd_id,op.enc_oop
                            FROM ' . BD_CTRL . '.cat_TipoFolioModificacion f
                            INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING(tfm_id)
                            WHERE op.mmd_id= :mmd_id AND op.omo_id= :omo_id
                            GROUP BY f.tfm_id,op.tmd_id,op.enc_oop;';
                        $params = [':mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id : 'E'),
                            ':omo_id'        => (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A')];
                        break;
                }
                break;
        }
        $tipoFolio = new stdClass();
        $tipoFolio->id = 'tfm_id';
        $tipoFolio->label = 'Tipo de Folio';
        $tipoFolio->sql = $sql2;
        $tipoFolio->params = $params;
        $tipoFolio->camposVal = 'tfm_id';
        $tipoFolio->camposText = 'tfm_nombre';
        $tipoFolio->comoAtr = 'omo_id,tmd_id,mmd_id,tfm_id,enc_oop';
        $tipoFolio->selecciona = $origenOp->getTfm_id();
        $tipoFolio->required = true;
        $tipoFolio->value = $origenOp->getTfm_id();
        if ($origenOp->getTfm_id() != '' && isset($this->prm->deshabilitado)) {
            $tipoFolio->disabled = true;
        }

        $sub = '';
        if ((isset($this->prm->mmd_id) && $this->prm->mmd_id == 'E' && $this->nivelJerarquico >= 3) ||
            (isset($this->prm->mmd_id) && $this->prm->mmd_id == 'E' && $this->nivelJerarquico <= 2 && $this->perfil != 'S')
        ) {
            if (isset($this->prm->mod_id) && $this->prm->mod_id != '') {
                $maestr = new MaestroModificacion($this->prm);
                $subs = $maestr->getSubsecretaria();
            } else {
                $maestr = new MaestroModificacion();
                $subs = 1;
            }
            $subsecretaria = new stdClass();
            $subsecretaria->id = 'Subsecretaria';
            $subsecretaria->label = 'Subsecretaría';

            switch ($this->nivelJerarquico) {
                case 1:
                case 2:
                    $subsecretaria->opciones = [1 => 'SUBSECRETARÍA DE EGRESOS', 2 => 'SUBSECRETARÍA DE PLANEACIÓN'];
                    $subsecretaria->value = $maestr->getSubsecretaria();
                    $subsecretaria->selecciona = $maestr->getSubsecretaria();
                    if ($maestr->getSubsecretaria() != '' && isset($this->prm->deshabilitado)) {
                        $subsecretaria->disabled = true;
                    }
                    break;
                default:
                    switch ($this->subScertaria) {
                        case 2:
                            $subsecretaria->opciones = [2 => 'SUBSECRETARÍA DE PLANEACIÓN'];
                            $subsecretaria->value = 2;
                            $subsecretaria->selecciona = 2;
                            break;
                        case 3:
                            $subsecretaria->opciones = [1 => 'SUBSECRETARÍA DE EGRESOS', 2 => 'SUBSECRETARÍA DE PLANEACIÓN'];
                            $subsecretaria->value = $subs;
                            $subsecretaria->selecciona = $subs;
                            $subsecretaria->disabled = true;
                            break;
                    }
                    break;
            }
            $subsecretaria->required = true;
            if ($maestr->getSubsecretaria() != '' && isset($this->prm->deshabilitado)) {
                $subsecretaria->disabled = true;
            }

            $sub = '<fieldset class="col col-md-6 col-xs-6">
                        ' . $this->ponCombo($subsecretaria) .
                '</fieldset>';
        }

        $tipoMov = '';
        if (isset($this->prm->agregaTipo) && $this->prm->agregaTipo == 'B') {
            $mod = new MaestroModificacion();
            if (isset($this->prm->mod_id) && $this->prm->mod_id != '') {
                $mod = new MaestroModificacion($this->prm);
            }

            $tipoM = new stdClass();
            $tipoM->id = 'mov';
            $tipoM->label = 'Tipo Movimiento';
            $tipoM->required = true;
            $tipoM->opciones = ['A' => 'AMPLIACIÓN', 'R' => 'REDUCCIÓN'];
            $tipoM->selecciona = $mod->getTipoMovimiento();
            if ($mod->getTipoMovimiento() != '' && isset($this->prm->deshabilitado)) {
                $tipoM->disabled = true;
            }

            $tipoMov = '<fieldset class="col col-md-6 col-xs-6">
                            ' . $this->ponCombo($tipoM) . '
                        </fieldset>';
        }

        $this->mixHtml('<div class="row" id="tf">' . $tipoMov . $sub . '</div>');

        $this->setContenedor('tf2');
        $this->mixHtml('<fieldset class="col">
                            ' . $this->ponCombo($tipoFolio) . '
                        </fieldset>');
    }

    private function abcFolios()
    {

        $observacion = '';
        $mod = new MaestroModificacion($this->prm);
        $parametrosBit = (object) $mod;
        $this->prm->oop = $parametrosBit->getOop();
        $this->prm->tipoMovimiento = $parametrosBit->getTipoMovimiento();
        $this->prm->clave = $parametrosBit->getClave();
        $this->prm->ecf_id = $parametrosBit->getEcf_id();
        $this->prm->fechaFolio = $parametrosBit->getFechaFolio();
        $this->prm->oficioArchivo = $parametrosBit->getOficioArchivo();
        $this->prm->fechaAutorizacion = $parametrosBit->getFechaAutorizacion();
        $this->prm->origen = $parametrosBit->getOrigen();
        $this->prm->reporteInterno = $parametrosBit->getReporteInterno();
        $this->prm->emo_id = $parametrosBit->getEmo_id();
        $this->prm->Vobo = $parametrosBit->getVobo();
        $this->prm->carga = $parametrosBit->getCarga();

        switch ($this->prm->abc) {
            case 'c':
                $this->prm->tipoMovimiento = $this->prm->mov;
                $this->prm->concepto = str_replace(['+', '%2C', '%2F'], [' ', ',', '/'], $this->prm->concepto);
                $observacion = 'Folio ' . $this->prm->mod_id . ' actualizado ';
                $this->prm->observacion = $observacion;
                break;
        }

        if ($observacion != '') {
            MaestroModificacion::abc($this->prm);
            $this->prm->bit_observacion = 'Folio actualizado desde modulo de modificación de folios autorizados por usr ' . $this->usuario_id . '';
            $this->prm->emo_id = $mod->getEmo_id();
            $this->prm->mod_concepto = $this->prm->concepto;
            $this->prm->mod_fechaFolio = $this->prm->fechaFolio;
            $this->prm->mod_origen = $this->prm->origen;
            $this->prm->ip = ($this->ip != false && $this->ip != '::1') ?
            AccesoDatos::getIP() : '127.0.0.1';
            $this->bitacora('M', $this->prm);
            $this->mixScript('
                notify("' . $observacion . '","success",false);
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    mostrar:true,
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    enc_oop:' . $mod->getOop() . ',
                    emo_id:' . $mod->getEmo_id() . ',
                });
            ');
        }
    }
    /* MÉTODO QUE REGISTRA EN LA BITACORA DEL MAESTRO O DETALLE ENVIANDO EL PARAMETRO DEL TIPO M = MAESTRO D = DETALLE */

    private function bitacora($tipo, $prm)
    {
        $sql = '';
        if ($tipo != '') {
            if (isset($prm->mod_id) && $prm->mod_id != '') {
                $prm->abc = 'a';
                if ($tipo == 'M') {
                    BitacoraMaestro::abc($prm);
                } else {
                    BitacoraDetalle::abc($prm);
                }

                $mod_gpo_id = $prm->mod_gpo_id;
                $mod_ur_id = $prm->mod_ur_id;

                $ip = ($this->ip != false && $this->ip != '::1') ?
                $this->ip : '127.0.0.1';
                $usr_id = $this->usuario_id;

                $rutalog = 'log/AdecuacionesFolAutorizados-' . $this->usuario_id . '-' . $mod_gpo_id . $mod_ur_id . '.txt';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d H-i-s');

                foreach ($prm as $field => $value) {
                    if (is_string($value)) {
                        $tempdata .= '|' . $field . ' = ' . $value;
                    }
                }

                $tempdata .= '|IPADDRESS=' . $this->ip;
                $tempdata .= '|USRID=' . $this->usuario_id;
                if ($sql != '') {
                    $tempdata .= '|CONSULTA=' . $sql . '';
                }
                fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
                fclose($file);
                $peso = filesize($rutalog);
                $peso = ($peso / 1024) / 1024;

                if ($peso > 8) {
                    $rutamodificada = 'log/AdecuacionesFolAutorizados-' . $this->usuario_id . '-';
                    $rutamodificada .= $mod_gpo_id . $mod_ur_id . '-' . $hoy . '.txt';
                    rename($rutalog, $rutamodificada);
                    $file = fopen($rutalog, 'a');
                    fwrite($file, 'Creado:' . $hoy . PHP_EOL);
                    fclose($file);
                }
            }
        } else {
            $this->mixScript('
                notify("Hubo un problema al registrar en la bitacora","error");
            ');
            return false;
        }
    }

    //METODO QUE AGREGA LAS FIRMAS AL REPORTE DE ADECUACION PRESUPUESTAL PERFIL SEFIN
    private function addFirma()
    {
        $abc = 'a';
        $params = [];
        $btn_regresa = new stdClass();
        $btn_regresa->value = 'Regresar';
        $btn_regresa->color = 'gris';
        $btn_regresa->class = 'me-2';
        $btn_regresa->icon = 'fa-solid fa-circle-left me-1';
        $btn_regresa->id = 'btn_regresa';

        $btn_firmar = new stdClass();
        $btn_firmar->value = 'Firmar';
        $btn_firmar->color = 'verde';
        $btn_firmar->class = 'me-2';
        $btn_firmar->icon = 'fa-solid fa-floppy-disk me-1';
        $btn_firmar->id = 'btn_firmar';
        $btn_firmar->attr = new stdClass();
        $btn_firmar->attr->mmd_id = 'E';
        $btn_firmar->attr->omo_id = $this->prm->omo_id;
        $btn_firmar->attr->mod_gpo_id = $this->prm->mod_gpo_id;
        $btn_firmar->attr->mod_ur_id = $this->prm->mod_ur_id;
        $btn_firmar->attr->mod_id = $this->prm->mod_id;

        $btn_imprimir = new stdClass();
        $btn_imprimir->value = 'Reimprimir';
        $btn_imprimir->color = 'rojo';
        $btn_imprimir->class = 'me-2';
        $btn_imprimir->icon = 'file-pdf me-1';
        $btn_imprimir->id = 'btn_reporte';
        $btn_imprimir->attr = new stdClass();
        $btn_imprimir->attr->mmd_id = 'E';
        $btn_imprimir->attr->omo_id = $this->prm->omo_id;
        $btn_imprimir->attr->mod_gpo_id = $this->prm->mod_gpo_id;
        $btn_imprimir->attr->mod_ur_id = $this->prm->mod_ur_id;
        $btn_imprimir->attr->mod_id = $this->prm->mod_id;

        $cbo_firma = new stdClass();
        $cbo_firma->id = 'usr_idfirma';
        $cbo_firma->name = 'control';
        $cbo_firma->class = 'cbofirmas';
        $cbo_firma->required = true;
        $cbo_firma->enDiv = true;
        $cbo_firma->camposVal = 'usr_id';
        $cbo_firma->agrupaPor = 'usr_id';
        $cbo_firma->campoLabelAgrupacion = 'usr_nombre';
        $cbo_firma->mensaje = 'Selecciona';
        $cbo_firma->label = 'Firma 1';
        $cbo_firma->comoAtr = 'usr_id,level';
        $cbo_firma->search = true;

        if (isset($this->prm->emo_id) && $this->prm->emo_id == 14) {
            $params = [];
            $cbo_firma->sql = '
            SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
            FROM ' . BD_CTRL . '.cat_Usuario
            INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
            INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin USING (ejs_id)
            WHERE ssc_id = ' . $this->subScertaria . '
            AND nvj_id=3
            AND gpo_id = ? AND ur_id=? AND uni_id in(16,18)
            AND sts_id=?;';
            $params = [1, 15, 1];
            $cbo_firma->params = $params;
        } elseif (isset($this->prm->emo_id) && $this->prm->emo_id == 15) {

            $cbo_firma->sql = '
            SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"1" AS level
            FROM ' . BD_CTRL . '.cat_Usuario
            INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
            INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin USING (ejs_id)
            WHERE ssc_id = ?
            AND nvj_id=?
            AND gpo_id = ? AND ur_id= ? AND dir_id= ?
            AND sts_id= ?;';

            $cbo_firma->params = [$this->subScertaria, 3, 1, 15, 5, 1];
        }

        $cbo_firma = $this->ponCombo($cbo_firma);

        $firma2 = new stdClass();
        $firma2->id = 'cbofirma2';
        $firma2->name = 'control';
        $firma2->class = 'cbofirmas';
        $firma2->required = true;
        $firma2->enDiv = true;
        $firma2->camposVal = 'usr_id';
        $firma2->agrupaPor = 'usr_id';
        $firma2->campoLabelAgrupacion = 'usr_nombre';
        $firma2->mensaje = 'Selecciona';
        $firma2->label = 'Firma 2';
        $firma2->comoAtr = 'usr_id,level';
        $firma2->search = true;

        if (isset($this->prm->emo_id) && $this->prm->emo_id == 14) {
            $firma2->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM ' . BD_CTRL . '.cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = ? AND ur_id = ? AND nvj_id= ?
                AND ssc_id= ? AND dir_id= ?
                AND uni_id in(16,18)
                AND sts_id= ?';
            $firma2->params = [1, 15, 4, $this->subScertaria, 7, 1];
        } elseif (isset($this->prm->emo_id) && $this->prm->emo_id == 15) {
            $firma2->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,
                uni_id,"2" AS level
                FROM ' . BD_CTRL . '.cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = ? AND ur_id = ? AND nvj_id=?
                AND ssc_id= ? AND dir_id=?
                AND sts_id=?';
            $firma2->params = [1, 15, 4, $this->subScertaria, 5, 1];
        }

        $scri = '';
        $firma2 = $this->ponCombo($firma2);

        $firma3 = new stdClass();
        $firma3->id = 'cbofirma3';
        $firma3->name = 'control';
        $firma3->class = 'cbofirmas';
        $firma3->required = true;
        $firma3->enDiv = true;
        $firma3->camposVal = 'usr_id';
        $firma3->agrupaPor = 'usr_id';
        $firma3->campoLabelAgrupacion = 'usr_nombre';
        $firma3->mensaje = 'Selecciona';
        $firma3->label = 'Firma 3';
        $firma3->comoAtr = 'usr_id,level';
        $firma3->search = true;
        if (isset($this->prm->emo_id) && $this->prm->emo_id == 14) {
            $firma3->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"3" AS level
                FROM ' . BD_CTRL . '.cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = ? AND ur_id = ?
                AND nvj_id = ?
                AND ssc_id= ?
                AND uni_id in(16,18)
                AND sts_id = ? ';
            $firma3->params = [1, 15, 5, $this->subScertaria, 1];
        } elseif (isset($this->prm->emo_id) && $this->prm->emo_id == 15) {
            $firma3->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"3" AS level
                FROM ' . BD_CTRL . '.cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin USING (ejs_id)
                WHERE gpo_id = ? AND ur_id = ?
                AND nvj_id = ?
                AND ssc_id= ?
                AND dir_id = ?
                AND sts_id = ? ';
            $firma3->params = [1, 15, 6, $this->subScertaria, 5, 1];
        }

        $firma3 = $this->ponCombo($firma3);

        $firma4 = new stdClass();
        $firma4->id = 'cbofirma4';
        $firma4->name = 'control';
        $firma4->class = 'cbofirmas';
        $firma4->required = true;
        $firma4->enDiv = true;
        $firma4->camposVal = 'usr_id';
        $firma4->agrupaPor = 'usr_id';
        $firma4->campoLabelAgrupacion = 'usr_nombre';
        $firma4->mensaje = 'Selecciona';
        $firma4->label = 'Firma 4';
        $firma4->comoAtr = 'usr_id,level';
        $firma4->sql = ' SELECT usr_id,UPPER(CONCAT(usr_nombre," ",usr_paterno)) AS usr_nombre,uni_id,"4" AS level
                            FROM ' . BD_CTRL . '.cat_Usuario
                            INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                            INNER JOIN ' . BD_CTRL . '.enc_JerarquiaSefin USING (ejs_id)
                            WHERE gpo_id= ? AND ur_id= ? AND ?
                            AND dir_id= ? AND nvj_id= ?
                            AND sts_id= ? ;
        ';
        $firma4->params = [1, 15, $this->subScertaria, 7, 6, 1];
        $scri = '';
        $firma4 = (isset($this->prm->emo_id) && $this->prm->emo_id == 14) ? $this->ponCombo($firma4) : '';
        $scri = '$(".cbofirmas").change();';

        $html = '';
        $html .= '
                <fieldset class="col-md-12 col-xs-12">
                    <legend>ADECUACIONES PRESUPUESTALES</legend>
                </fieldset>

                <fieldset style="align:center" class="col-md-12 col-xs-12 mb-4" >
                    <legend>Firmas del Reporte</legend>
                    <div style=" border:1px solid RGBA(99,0,0,.6); padding:4px; font-size:13px; color :#666">
                            Si no selecciona alguna de las firmas, en la reimpresión se mostrarán las firmas registradas durante
                            el proceso de la adecuación presupuestal.<br/>
                            Si ve la leyenda [ sin puesto ] o [ sin titulo ], de click sobre esa leyenda para
                            editar ese texto ya que es así como aparecerá en la impresión,
                            esta información viene directamente de la base de datos.
                    </div>
                </fieldset>
                    <hr/>

                <div class="container-fluid mb-3">
                    <div class="row">
                        <fieldset style="align:center" class="col-md-4 col-xs-4" >
                            ' . $cbo_firma . '
                        </fieldset>
                        <fieldset style="align:center" class="col-md-4 col-xs-4" >
                            ' . $firma2 . '
                        </fieldset>
                        <fieldset style="align:center" class="col-md-4 col-xs-4" >
                            ' . $firma3 . '
                        </fieldset>
                    </div>
                </div>

                <div class="container-fluid mb-4">
                    <fieldset style="align:center" class="col-md-6 col-xs-6" >
                        ' . $firma4 . '
                    </fieldset>
                </div>';

        $html .= '</div>';
        $html .= '<div id="infofirmas"  style="align:center">
                    <center><legend>Firmas del Reporte</legend> ( Seleccione un usuario de firma)
                    <div class="container-fluid mb-2">
                        <div class="row">
                            <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma1">
                                ...
                            </fieldset>
                            <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma2">
                                ...
                            </fieldset>
                            <fieldset style="align:center" class="col-md-4 col-xs-4" id="firma3">
                                ...
                            </fieldset>
                        </div>
                    </div>

                    <div class="container-fluid mb-4">
                        <fieldset class="col-md-12 col-xs-12">
                            <fieldset style="align:center" class="col-md-6 col-xs-6" id="firma4">
                                ...
                            </fieldset>
                        </fieldset>
                    </div>
                </div>';

        $btnRegresa = $this->ponBoton($btn_regresa);
        $btnFirma = $this->ponBoton($btn_firmar);
        $btnImprimir = $this->ponBoton($btn_imprimir);
        $this->mixHtml($html);
        $this->mixHtml('
            <form id="formMod" method="post" action="rep/doc/scp/adecuaciones/rep_ReimpresionModificaciones.php" target="_blank">
                <input type="hidden" id="omo_id" name="omo_id" value=""/>
                <input type="hidden" id="mod_gpo_id" name="mod_gpo_id" value=""/>
                <input type="hidden" id="mod_ur_id" name="mod_ur_id" value=""/>
                <input type="hidden" id="mod_id" name="mod_id" value=""/>
                <input type="hidden" id="mod_tipoMovimiento" name="mod_tipoMovimiento" value=""/>
            </form>
        ');

        $this->agregaTools($btnRegresa . ' ' . $btnFirma . ' ' . $btnImprimir);
        $this->mixScript('
            $("#btn_firmar").hide();

            $(".cbofirmas").change(function(){
                if($(this).val() !="")
                    comunica("' . $this->pantalla . '",{
                            "metodo":"firmas",
                            "level":$(this).find("option:selected").attr("level"),
                            usr:$(this).val()})
                $("#btn_firmar").show();
            });
            ' . $scri . '
            $("#btn_regresa").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"main"});
            });

            $("#btn_firmar").click(function(){
                fm1 ={t:$("#t1").val(),n:$("#n1").val(),c:$("#c1").val()};
                fm2 ={t:$("#t2").val(),n:$("#n2").val(),c:$("#c2").val()};
                fm3 ={t:$("#t3").val(),n:$("#n3").val(),c:$("#c3").val()};
                fm4 ={t:$("#t4").val(),n:$("#n4").val(),c:$("#c4").val()};

                comunica("' . $this->pantalla . '",{
                    metodo:"abcFirmas",
                    abc:"' . $abc . '",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    emo_id:$(this).attr("emo_id"),
                    mmd_id:$(this).attr("mmd_id"),
                    f1:fm1,
                    f2:fm2,
                    f3:fm3,
                    f4:fm4,
                    dep:$("#usr_idfirma").find("option:selected").val(),
                },true);
            });

            // $("#mod_tipoMovimiento").val($(this).attr("mod_tipoMovimiento"));
            $("#impresion").submit();
            $("#btn_reporte").click(function(){
                $("#omo_id").val($(this).attr("omo_id"));
                $("#mod_gpo_id").val($(this).attr("mod_gpo_id"));
                $("#mod_ur_id").val($(this).attr("mod_ur_id"));
                $("#mod_id").val($(this).attr("mod_id"));
                $("#mod_tipoMovimiento").val($(this).attr("mod_tipoMovimiento"));
                var fecha=prompt("Ingresa la fecha y hora de impresión original","00/00/00 - 00:00:00 ");
                $("#formMod").append( $("<input/>").attr("id","reimpresion").attr("name","reimprimir").val(fecha) );
                $("#formMod").submit();
                $("#reimpresion").remove();
            });
        ');
    }
    /* METODO QUE MUESTRA LAS FIRMAS ACTUALES DEL REPORTE PARA PERFIL SEFIN */

    private function firmas()
    {
        $l = $this->prm->level;
        $this->setContenedor('firma' . $this->prm->level);
        $sql = 'SELECT usr_id, usr_titulo, usr_puesto, usr_nombre, usr_paterno, usr_materno
                FROM cat_Usuario
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
                WHERE usr_id = ?; ';
        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar([$this->prm->usr]);
        $d = $c->extraer();
        $c->desconectar();
        unset($c);
        $tit = trim($d['usr_titulo']) == '' ? '[ sin titulo ]' : $d['usr_titulo'];
        $puesto = trim($d['usr_puesto']) == '' ? '[ sin puesto ]' : $d['usr_puesto'];
        $html = '<div class="firmaslotes" style="width:100%; "> ';
        $html .= '<input id="t' . $l . '"  type="text" value="' . $tit .
            '" style="width:19%;text-align:right; display:inline-block"/>';
        $html .= '<input id="n' . $l . '"  type="text" value="' . $d['usr_nombre'] .
            ' ' . $d['usr_paterno'] . ' ' . $d['usr_materno'] .
            '" style="width:80%;text-align:left; display:inline-block"/><br/>';
        $html .= '<input id="c' . $l . '" type="text" value="' . htmlspecialchars($puesto) .
            '" style="width:100%;"/></div>';
        $this->mixHtml($html);
    }
    /* METODO QUE INSERTA LAS FIRMAS DEL REPORTE DE ADECUACION PRESUPUESTAL PERFIL SEFIN */

    private function abcFirmas()
    {
        $valido = true;
        if ($valido) {
            switch ($this->prm->abc) {
                case 'a':
                    //Firmantes del reporte SEFIN
                    switch ($this->subScertaria) {
                        case 2:
                            $this->prm->firma1 = $this->strtMayus($this->prm->f1->t == '[ sin titulo ]' ?
                                $this->prm->f1->n : $this->prm->f1->t . ' ' . $this->prm->f1->n);
                            $this->prm->cargo1 = $this->strtMayus($this->prm->f1->c == '[ sin puesto ]' ?
                                ' ' : $this->prm->f1->c);

                            $this->prm->firma2 = $this->strtMayus($this->prm->f2->t == '[ sin titulo ]' ?
                                $this->prm->f2->n : $this->prm->f2->t . ' ' . $this->prm->f2->n);

                            $this->prm->cargo2 = $this->strtMayus($this->prm->f2->c == '[ sin puesto ]' ?
                                ' ' : $this->prm->f2->c);

                            $this->prm->firma3 = $this->strtMayus($this->prm->f3->t == '[ sin titulo ]' ?
                                $this->prm->f3->n : $this->prm->f3->t . ' ' . $this->prm->f3->n);

                            $this->prm->cargo3 = $this->strtMayus($this->prm->f3->c == '[ sin puesto ]' ?
                                ' ' : $this->prm->f3->c);

                            $this->prm->firma4 = '';

                            $this->prm->cargo4 = '';
                            break;
                        case 3:
                            $this->prm->firma1 = $this->strtMayus($this->prm->f1->t == '[ sin titulo ]' ?
                                $this->prm->f1->n : $this->prm->f1->t . ' ' . $this->prm->f1->n);
                            $this->prm->cargo1 = $this->strtMayus($this->prm->f1->c == '[ sin puesto ]' ?
                                ' ' : $this->prm->f1->c);

                            $this->prm->firma2 = $this->strtMayus($this->prm->f2->t == '[ sin titulo ]' ?
                                $this->prm->f2->n : $this->prm->f2->t . ' ' . $this->prm->f2->n);

                            $this->prm->cargo2 = $this->strtMayus($this->prm->f2->c == '[ sin puesto ]' ?
                                ' ' : $this->prm->f2->c);

                            $this->prm->firma3 = $this->strtMayus($this->prm->f3->t == '[ sin titulo ]' ?
                                $this->prm->f3->n : $this->prm->f3->t . ' ' . $this->prm->f3->n);

                            $this->prm->cargo3 = $this->strtMayus($this->prm->f3->c == '[ sin puesto ]' ?
                                ' ' : $this->prm->f3->c);

                            $this->prm->firma4 = $this->strtMayus($this->prm->f4->t == '[ sin titulo ]' ?
                                $this->prm->f4->n : $this->prm->f4->t . ' ' . $this->prm->f4->n);

                            $this->prm->cargo4 = $this->strtMayus($this->prm->f4->c == '[ sin puesto ]' ?
                                ' ' : $this->prm->f4->c);
                            break;
                    }
                    $this->prm->ip = ($this->ip != false && $this->ip != '::1') ?
                    $this->ip : '127.0.0.1';
                    $this->prm->usr_id = $this->usuario_id;

                    //Registra o actualiza las firmas
                    $sql = 'INSERT INTO ' . BD_CTRL . ".scp_ModificacionFirma
                        (omo_id, mod_gpo_id, mod_ur_id, mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2, mod_cargo3, mod_firma3,
                        mod_cargo4, mod_firma4, mod_cargo5, mod_firma5, mod_cargo6, mod_firma6, mod_cargo7, mod_firma7, usr_id)
                        VALUES (:omo_id, :mod_gpo_id, :mod_ur_id, :mod_id, '', '', '', '', '', '', :cargo1, :firma1, :cargo2, :firma2,
                                :cargo3, :firma3, :cargo4, :firma4, :usr_id)
                        ON DUPLICATE KEY UPDATE
                        mod_cargo4 = :cargo1,
                        mod_firma4 = :firma1,
                        mod_cargo5 = :cargo2,
                        mod_firma5 = :firma2,
                        mod_cargo6 = :cargo3,
                        mod_firma6 = :firma3,
                        mod_cargo7 = :cargo4,
                        mod_firma7 = :firma4";

                    $firmasReportes = new AccesoDatos();
                    $firmasReportes->conectar();
                    $firmasReportes->setSql($sql);
                    $firmasReportes->consultar([':omo_id' => $this->prm->omo_id,
                        ':mod_gpo_id'                     => $this->prm->mod_gpo_id,
                        ':mod_ur_id'                      => $this->prm->mod_ur_id,
                        ':mod_id'                         => $this->prm->mod_id,
                        ':cargo1'                         => $this->prm->cargo1,
                        ':firma1'                         => $this->prm->firma1,
                        ':cargo2'                         => $this->prm->cargo2,
                        ':firma2'                         => $this->prm->firma2,
                        ':cargo3'                         => $this->prm->cargo3,
                        ':firma3'                         => $this->prm->firma3,
                        ':cargo4'                         => $this->prm->cargo4,
                        ':firma4'                         => $this->prm->firma4,
                        ':usr_id'                         => $this->usuario_id]);
                    if ($firmasReportes) {
                        $this->mixScript('notify("Se registrarón correctamente las firmas","success")');
                    } else {
                        $this->mixScript('notify("Ocurrio un error al registras las firmas, intente de nuevo","error")');
                    }
                    break;
            }
        }
    }

    //METODO QUE CONVIERTE EL TEXTO A MAYUSCULAS
    public function strtMayus($variable)
    {
        $variable = strtr(strtoupper($variable), 'àèìòùáéíóúçñäëïöü', 'ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ');
        return $variable;
    }
}

if ((isset($_SESSION[_S]['prm']['S']['1']['5']['1'])) && $_SESSION[_S]['nvj_id'] >= 3 && isset($_SESSION[_S]['ssc_id'])) { // permisos del usuario que puede ver este controlador
    new ControladorModificacionFoliosAutorizados();
} else {
    WebResponse::sinPermisos();
}
