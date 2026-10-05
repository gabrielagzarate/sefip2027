<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/scp/vistaAdecuaciones.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuaciones.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';
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
require_once dirname(__FILE__) . '/../../ent/PartidaEspecifica.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../../srv/TipoCompra.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaActividadMir.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/HistoricoMetaActividadMir.php';

class ControladorConsultaFolios extends WebResponse
{
    public $vista;
    public $model;
    public $gpo;
    public $ur;
    public $nivel;
    public $perfil;
    public $subsec;
    public $consulta;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->vista = new vistaAdecuaciones();
        $this->model = new modelAdecuaciones();

        // variables de sesión
        $this->gpo = $_SESSION[_S]['gpo_id'];
        $this->ur = $_SESSION[_S]['ur_id'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->perfil = $_SESSION[_S]['prf_id'];
        $this->subsec = $_SESSION[_S]['ssc_id'];
        $this->consulta = isset($_SESSION[_S]['prm']['S']['5']['5']['46']) ? true : false;

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
            case 'tablaModificaciones':
                $this->tablaModificaciones();
                break; //Metodo para mostrar las modificaciones o movimientos agregados al folio por la UR, D.R.H Y SEFIN
            case 'llenaTablaModificaciones':
                $this->llenaTablaModificaciones();
                break;
            case 'addModModificacion':
                $this->addModModificacion();
                break; //Metodo para agregar modificaciones o movimientos al folio por la UR Y RH.
            case 'addModPrevision':
                $this->addModPrevision();
                break; //Metodo que agrega modificaciones al folio  de origen A,B,C en SEFIN, asi como la compensacion del folio si es necesario.
            case 'muestraPresupuesto':
                $this->muestraPresupuesto();
                break; //Metodo para mostrar informacion referente a presupuesto segun la clave presupuestal elegida.
            case 'mostrarArchivos':
                $this->mostrarArchivos();
                break; //Metodo para mostrar archivos cargados de folios internos autorizados.
            case 'tablaHistorialFolio':
                $this->tablaHistorialFolio();
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
            default:
                $this->tablaFolios();
                break;
        }
        $this->returnJson();
    }

    //Metodo que muestra la lista de folios creados por la UR
    private function tablaFolios()
    {
        $this->agregaTools('');
        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->size = 3;
        $ur->class = ' limpiar';

        $omo_id = $tmd = $inputUr = $origen = '';
        switch ($this->nivel) {
            case 1:
            case 2:
                switch ($this->perfil) {
                    case 'S':
                        $inputUr = '<legend>Ur</legend>' . $this->ponInput($ur);
                        $origen = "'A','C'";
                        $omo_id = 'omo_id ="C"';
                        $tmd = 'omo_id = "C"';
                        break;
                    default:
                        $origen = "'A','F'";
                        $omo_id = 'omo_id IN ("A","F")';
                        $tmd = 'omo_id IN ("A","F")';
                        break;
                }
                break;
            default:
                $inputUr = '<legend>Ur</legend>' . $this->ponInput($ur);
                $omo_id = 'omo_id IN("B","E")';
                $tmd = 'omo_id IN ("B","E")';
                switch ($this->subsec) {
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
        $folio->maxlength = 6;
        $folio->class = ' limpiar';

        $cvefin = new stdClass();
        $cvefin->id = 'ecf_id2';
        $cvefin->sql = 'SELECT cfi_clave, cfi_nombre,ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            INNER JOIN enc_ClaveFinanciamiento USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN scp_MaestroModificacion USING(ecf_id)
            GROUP BY ecf_id,cfi_clave,cfi_nombre
            ORDER BY cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio;';
        $cvefin->camposText = 'cfi_clave,cfi_nombre';
        $cvefin->soloText = true;
        $cvefin->mensaje = 'Seleccione una opcion de la lista';
        $cvefin->camposVal = 'ecf_id';
        $cvefin->search = true;
        $cvefin->class = ' limpiar';

        $tipoMod = new stdClass();
        $tipoMod->id = 'tmd_id';
        $tipoMod->mensaje = 'Seleccione una opcion de la lista';
        $tipoMod->sql = 'select tmd_id,tmd_nombre
            from cat_TipoModificacion
            INNER JOIN enc_OrigenOperacion using(tmd_id)
            where ' . $tmd . '
            GROUP BY tmd_id;';
        $tipoMod->search = true;
        $tipoMod->class = ' limpiar';

        $tipoFolio = new stdClass();
        $tipoFolio->id = 'tfm_id';
        $tipoFolio->mensaje = 'Seleccione una opcion de la lista';
        $tipoFolio->sql = 'select tfm_id,tfm_nombre
            from cat_TipoFolioModificacion
            INNER JOIN enc_OrigenOperacion using(tfm_id)
            where ' . $omo_id . '
            GROUP BY tfm_id;';
        $tipoFolio->search = true;
        $tipoFolio->class = ' limpiar';

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

        $importe = new stdClass();
        $importe->id = 'importe';
        $importe->class = ' limpiar';

        $modalidad = new stdClass();
        $modalidad->id = 'modalidad';
        $modalidad->mensaje = 'Seleccione una opcion de la lista';
        $modalidad->sql = 'select mmd_id, mmd_nombre from cat_ModalidadModificacion;';
        $modalidad->search = true;
        $modalidad->class = ' limpiar';

        $subsecretaria = new stdClass();
        $subsecretaria->id = 'sub';
        $subsecretaria->search = true;
        $subsecretaria->class = ' limpiar';

        $buscar = new stdClass();
        $buscar->id = 'buscar';
        $buscar->value = 'Buscar';
        $buscar->class = 'me-1';
        $buscar->icon = 'fa fa-search me-2';
        $buscar->text = 'Buscar';
        $buscar->color = 'azul';

        $limpiar = new stdClass();
        $limpiar->id = 'Clean';
        $limpiar->value = 'Limpiar';
        $limpiar->class = 'me-1';
        $limpiar->text = 'Limpiar';
        $limpiar->color = 'amarillo';
        $limpiar->icon = 'fa fa-trash m-1';

        $mod = '';
        switch ($this->nivel) {
            case 1:
            case 2:
                switch ($this->perfil) {
                    case 'S':

                        break;
                    default:
                        $mod = '
                            <legend>Modalidad</legend>' . $this->templateCombo($modalidad) . '
                        ';
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
                $html .= '<div class="row">
                            <!--<h4>Buscar por:</h4>-->
                            <fieldset class="col-md-3 col-xs-6">
                                ' . $inputUr . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-6">
                                <legend>Folio</legend>
                                ' . $this->ponInput($folio) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-6">
                                <legend>Clave de Financiamiento</legend>
                                ' . $this->templateCombo($cvefin) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-6">
                                <legend>Tipo de Adecuación</legend>
                                ' . $this->templateCombo($tipoMod) . '
                            </fieldset>
                        </div>
                        <div class="row">
                            <fieldset class="col-md-3 col-xs-3 mb-4">
                            <legend>Tipo de folio</legend>
                                ' . $this->templateCombo($tipoFolio) . '
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
                            <legend>Importe $</legend>
                                ' . $this->ponInput($importe) . '
                            </fieldset>
                        </div>
                        <div class="row mb-4">
                            <fieldset class="col-md-3 col-xs-6">
                                ' . $mod . '
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
                        </div>
                        ';
                break;
            default:
                switch ($this->subsec) {
                    case 2:
                        $html .= '<div class="row">
                                    <!--<h4>Buscar por:</h4>-->
                                    <fieldset class="col-md-3 col-xs-6">
                                        ' . $inputUr . '
                                    </fieldset>
                                    <fieldset class="col-md-3 col-xs-6">
                                        <legend>Folio</legend>
                                        ' . $this->ponInput($folio) . '
                                    </fieldset>
                                    <fieldset class="col-md-3 col-xs-6">
                                        <legend>Clave de Financiamiento</legend>
                                        ' . $this->templateCombo($cvefin) . '
                                    </fieldset>
                                    <fieldset class="col-md-3 col-xs-6">
                                        <legend>Tipo de Adecuación</legend>
                                        ' . $this->templateCombo($tipoMod) . '
                                    </fieldset>
                                </div>
                                <div class="row">
                                    <fieldset class="col-md-3 col-xs-3 mb-4">
                                    <legend>Tipo de folio</legend>
                                        ' . $this->templateCombo($tipoFolio) . '
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
                                    <legend>Importe $</legend>
                                        ' . $this->ponInput($importe) . '
                                    </fieldset>
                                </div>
                                <div class="row mb-4">
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
                                </div>
                            ';
                        break;
                    case 3:
                    case 5:
                        $html .= '
                                <div class="row">
                                    <!--<h4>Buscar por:</h4>-->
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
                                        <legend>Clave de Financiamiento</legend>
                                        ' . $this->templateCombo($cvefin) . '
                                    </fieldset>
                                </div>
                                <div class="row">
                                    <fieldset class="col-md-3 col-xs-6 mb-4">
                                        <legend>Tipo de Adecuación</legend>
                                        ' . $this->templateCombo($tipoMod) . '
                                    </fieldset>
                                    <fieldset class="col-md-3 col-xs-6">
                                        <legend>Tipo de folio</legend>
                                        ' . $this->templateCombo($tipoFolio) . '
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
                                <div class="row mb-4">
                                    <fieldset class="col-md-3 col-xs-3">
                                        <legend>Importe $</legend>
                                        ' . $this->ponInput($importe) . '
                                    </fieldset>
                                    <fieldset class="col-md-3 col-xs-3">
                                        <legend>Modalidad</legend>
                                        ' . $this->templateCombo($modalidad) . '
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-3" >
                                    <legend>&nbsp;</legend>
                                        ' . $this->ponBoton($buscar) . '
                                        ' . $this->ponBoton($limpiar) . '
                                    </fieldset>
                                </div>
                            ';
                        break;
                }
                break;
        }
        $script = $this->getScript();

        $tabla = new stdClass();
        $tabla->id = 'tbl-Folios';
        $tabla->titulo = 'Adecuaciones Presupuestales';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->tools = $html;
        $tabla->comunica->ur_clave = (isset($this->prm->ur_clave) && $this->prm->ur_clave
            != '') ? $this->prm->ur_clave : '{$("#ur").val()}';
        $tabla->comunica->mod_id = (isset($this->prm->mod_id) && $this->prm->mod_id
            != '') ? $this->prm->mod_id : '{$("#nmod_id").val()}';
        $tabla->comunica->tmd_id = (isset($this->prm->tmd_id) && $this->prm->tmd_id
            != '') ? $this->prm->tmd_id : '{$("#tmd_id").val()}';
        $tabla->comunica->tfm_id = (isset($this->prm->tfm_id) && $this->prm->tfm_id
            != '') ? $this->prm->tfm_id : '{$("#tfm_id").val()}';
        $tabla->comunica->ecf_id2 = (isset($this->prm->ecf_id2) && $this->prm->ecf_id2
            != '') ? $this->prm->ecf_id2 : '{$("#ecf_id2 option:selected").val()}';
        $tabla->comunica->emo_id = (isset($this->prm->emo_id) && $this->prm->emo_id
            != '') ? $this->prm->emo_id : '{$("#emo_id").val()}';
        $tabla->comunica->importe = (isset($this->prm->importe) && $this->prm->importe
            != '') ? $this->prm->importe : '{$("#importe").val()}';
        $tabla->comunica->oficioSolicitud = (isset($this->prm->oficioSolicitud) && $this->prm->oficioSolicitud
            != '') ? $this->prm->oficioSolicitud : '{$("#mod_OficioSolicitudSefin").val()}';
        $tabla->comunica->omo_id = $origen;
        $tabla->comunica->sub = (isset($this->prm->sub) && $this->prm->sub != '')
        ? $this->prm->sub : '{$("#sub").val()}';
        $tabla->comunica->modalidad = (isset($this->prm->modalidad) && $this->prm->modalidad
            != '') ? $this->prm->modalidad : '{$("#modalidad").val()}';
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

        //Exportar folio
        $th = new stdClass();
        $th->texto = 'Exportar';
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
        $td->attr->omo_id = 'omo_id';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
        $td->attr->enc_oop = 'enc_oop';
        $td->attr->ecf_id = 'ecf_id';
        $td->class = 'addModFolio btn-xs';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-file-pdf m-1';
        $td->color = 'rojo';

        $td->attr = new stdClass();
        $td->attr->mmd_id = 'mmd_id';
        $td->attr->omo_id = 'omo_id';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
        $td->class = 'imprimir btn-xs';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-file-excel m-1';
        $td->color = 'verde';
        $td->attr = new stdClass();
        $td->attr->repomo_id = 'omo_id';
        $td->attr->repmod_gpo_id = 'mod_gpo_id';
        $td->attr->repmod_ur_id = 'mod_ur_id';
        $td->attr->repmod_id = 'mod_id';
        $td->class = 'exportar btn-xs';
        array_push($trbody, $td);

        //UR
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['mod_gpo_id', 'mod_ur_id'];
        array_push($trbody, $td);

        //Numero de folio
        $td = new stdClass();
        $td->campoTxt = 'mod_id';
        $td->formato = 'numfolio';
        $td->attr = new stdClass();
        $td->attr->mmd_id = 'mmd_id';
        $td->attr->emo_id = 'emo_id';
        $td->attr->omo_id = 'omo_id';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
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

        //Tipo de adecuación
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

        //Ir a detalle de folio
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->color = 'cielo';
        $td->icono = 'eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'omo_id';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
        $td->attr->enc_oop = 'enc_oop';
        $td->class = 'detalleFolio btn-xs';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>
                        <div id="archivos" class="col-md-12 col-xs-12"></div>');

        $this->recargaCombo($cvefin);
        $this->recargaCombo($tipoMod);
        $this->recargaCombo($tipoFolio);
        $this->recargaCombo($estado);
        $this->recargaCombo($modalidad);

        $this->mixHtml('
            <form id="impresion" method="post" action="" target="_blank">
                <input type="hidden" id="omo_id" name="omo_id" value=""/>
                <input type="hidden" id="mod_gpo_id" name="mod_gpo_id" value=""/>
                <input type="hidden" id="mod_ur_id" name="mod_ur_id" value=""/>
                <input type="hidden" id="mod_id" name="mod_id" value=""/>
                <input type="hidden" id="mod_tipoMovimiento" name="mod_tipoMovimiento" value=""/>
            </form>
        ');

        $this->mixHtml('
            <form id="exportar" method="post" action="rep/excel/scp/adecuaciones/exp_RepFolio.php" target="_blank">
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
            $("#Clean").removeClass("btn-default");
            $("#Clean").click(function(){
                $(".limpiar").val("");
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    });
                return false;
            });

            $("#buscar").removeClass("btn-default");
            $("#buscar").click(function(){
                ' . $this->wrLlamaLlenaTabla('tbl-Folios') . '
                return false;
            })

            $("#importe").blur(function(){
                if (  $.trim($(this).val()) != ""){
                    $("#importe").val(formatNumber($(this).val()));
                }

            });
        ');
    }

    private function llenaTablaFolios()
    {
        $filtro = $having = '';
        $ur_id = $gpo_id = '';
        $parametros = [];
        (isset($this->prm->tmd_id) && $this->prm->tmd_id == 'null') ? $this->prm->tmd_id = '' : $this->prm->tmd_id;
        (isset($this->prm->tfm_id) && $this->prm->tfm_id == 'null') ? $this->prm->tfm_id = '' : $this->prm->tfm_id;
        (isset($this->prm->ecf_id2) && $this->prm->ecf_id2 == 'null') ? $this->prm->ecf_id2 = '' : $this->prm->ecf_id2;
        (isset($this->prm->emo_id) && $this->prm->emo_id == 'null') ? $this->prm->emo_id = '' : $this->prm->emo_id;

        if (isset($this->prm->regresar) && $this->prm->regresar == 1) {
            if (isset($this->prm->omo_id) && $this->prm->omo_id != '') {
                if ($filtro != '') {
                    $filtro .= ' ';
                } else {
                    $filtro .= 'where';
                }
                $filtro .= ' omo_id in("' . $this->prm->omo_id . '") ';
            }
        } else {
            //ORIGEN DE LA MODIFICACION
            if (isset($this->prm->omo_id) && $this->prm->omo_id != '') {
                if ($filtro != '') {
                    $filtro .= ' ';
                } else {
                    $filtro .= 'where';
                }
                $filtro .= ' omo_id in(' . $this->prm->omo_id . ') ';
            }
        }
        //Valida la UR
        if (
            isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) < 3 && $this->prm->ur_clave
            != ''
        ) {
            $this->mixScript('alert("Numero de UR invalido"); $("#ur").focus();');
        }
        //GRUPO Y UR
        if (
            isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) == 3 && $this->prm->ur_clave
            != ''
        ) {
            $gpo_id = substr($this->prm->ur_clave, 0, 1);
            $ur_id = substr($this->prm->ur_clave, 1);
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.mod_gpo_id= :gpo_id AND m.mod_ur_id= :ur_id ';
            $parametros += [
                'gpo_id' => $gpo_id,
                'ur_id'  => $ur_id,
            ];
        }

        //NUMERO DE FOLIO
        if (isset($this->prm->mod_id) && $this->prm->mod_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.mod_id= :mod_id';
            $parametros += [
                'mod_id' => $this->prm->mod_id,
            ];
        }

        //ENCUADRE CLAVE DE FINANCIAMIENTO
        if (isset($this->prm->ecf_id2) && $this->prm->ecf_id2 != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.ecf_id= :ecf_id ';
            $parametros += [
                'ecf_id' => $this->prm->ecf_id2,
            ];
        }

        //TIPO DE FOLO DE MODIFICACION
        if (isset($this->prm->tfm_id) && $this->prm->tfm_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' tfm_id= :tfm_id ';
            $parametros += [
                'tfm_id' => $this->prm->tfm_id,
            ];
        }

        //ESTADO DE LA MODIFICACION
        if (isset($this->prm->emo_id) && $this->prm->emo_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }

            $filtro .= ' emo_id= :emo_id ';
            $parametros += [
                'emo_id' => $this->prm->emo_id,
            ];
        }
        //TIPO DE MODIFICACION
        if (isset($this->prm->tmd_id) && $this->prm->tmd_id != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' tmd_id= :tmd_id ';
            $parametros += [
                'tmd_id' => $this->prm->tmd_id,
            ];
        }

        //IMPORTE
        if (isset($this->prm->importe) && $this->prm->importe != '') {
            if ($filtro != '') {
                $filtro .= ' ';
            } else {
                $filtro .= 'where';
            }

            $having .= 'having';
            if ($this->prm->importe == 0) {
                $having .= ' importe IS NULL';
            } else {
                $having .= ' importe= :importe ';
                $parametros += [
                    'importe' => $this->prm->importe,
                ];
            }
        }

        //OFICION SOLICITUD SEFIN
        if (isset($this->prm->oficioSolicitud) && $this->prm->oficioSolicitud != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' mod_oficioSolicitudSefin= :oficioSolicitudSefin ';
            $parametros += [
                'oficioSolicitudSefin' => $this->prm->oficioSolicitud,
            ];
        }

        //MODALIDAD DE LA MODIFICACION
        if (isset($this->prm->modalidad) && $this->prm->modalidad != '') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' mmd_id= :mmd_id ';
            $parametros += [
                'mmd_id' => $this->prm->modalidad,
            ];
        }

        //VALIDA SI EL NIVEL JERARQUICO ES MENOR A 2 Y SI EL PERFIL ES DIFERENTE A SUPERVISOR
        if ($this->nivel <= 2 && $this->perfil != 'S') {
            if ($filtro != '') {
                $filtro .= ' and ';
            } else {
                $filtro .= 'where';
            }
            $filtro .= ' m.mod_gpo_id= :mod_gpo_id and m.mod_ur_id= :mod_ur_id ';
            $parametros += [
                'mod_gpo_id' => $this->gpo,
                'mod_ur_id'  => $this->ur,
            ];
        }

        $subsecretaria = '';
        if (
            isset($this->prm->ur_clave) && strlen($this->prm->ur_clave) == 3 && $this->prm->ur_clave
            != ''
        ) {
            $gpo_id = substr($this->prm->ur_clave, 0, 1);
            $ur_id = substr($this->prm->ur_clave, 1);
        }

        //VALIDA SI EL GRUPO Y LA UR ES= 115 Y SI EL TIPO DE MODIFICACION ES UNA RECALENDARIZACION, AMPLIACIÓN O TRASPASO INTRAINSTITUCIONAL
        if (
            $gpo_id . $ur_id == 115 || isset($this->prm->tmd_id) && $this->prm->tmd_id
            == 'C' ||
            isset($this->prm->tmd_id) && $this->prm->tmd_id == 'D' ||
            isset($this->prm->tmd_id) && $this->prm->tmd_id == 'A'
        ) {
        } else {
            if ($filtro == 'where') {
                $filtro .= ' ';
                $filtro .= ' (tcp_id!="P" or tcp_id is null) ';
            } elseif ($filtro != '') {
                $filtro .= ' and';
                $filtro .= ' (tcp_id!="P" or tcp_id is null) ';
            }
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

                            $subsecretaria = ' mod_Subsecretaria=2';
                            break;
                        case 3:
                        case 5:
                            if ($filtro != '') {
                                $filtro .= ' and ';
                            } else {
                                $filtro .= 'where';
                            }

                            $subsecretaria = ' mod_Subsecretaria=1';
                            break;
                    }
                } else { //Si tiene el permiso de consulta general.
                    if (isset($this->prm->sub) && $this->prm->sub != '') {
                        if ($filtro != '') {
                            $filtro .= ' and ';
                        } else {
                            $filtro .= 'where';
                        }
                        $filtro .= ' mod_Subsecretaria= :mod_Subsecretaria ';
                        $parametros += [
                            'mod_Subsecretaria' => $this->gpo,
                        ];
                    }
                    if (
                        isset($this->prm->ur_clave) && strlen($this->prm->ur_clave)
                        == 3 && $this->prm->ur_clave != ''
                    ) {
                        $gpo_id = substr($this->prm->ur_clave, 0, 1);
                        $ur_id = substr($this->prm->ur_clave, 1);
                    }
                }
                break;
        }
        $sql = "
            select
                if(tmd_id='A',FORMAT(sum(dmo_importe)/2,2),FORMAT(sum(dmo_importe),2)) importe,
                concat(mod_gpo_id,mod_ur_id) as ur,omo_id,mod_gpo_id,mod_ur_id,mod_id,
                mod_concepto, mod_fechaFolio,if(mod_fechaAutorizacion is null,'',DATE_FORMAT(mod_fechaAutorizacion,\"%d/%m/%Y %H:%i:%s\")) mod_fechaAutorizacion,cfi_clave,
                tmd_nombre,tfm_nombre, emo_id, emo_nombre, emo_color,mmd_id,enc_oop
            from
                scp_MaestroModificacion m
                INNER JOIN(
                    SELECT ecf_id,cfi_clave from enc_ClaveFinanciamiento
                    INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . " using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                )as cfi using(ecf_id)
                left join scp_DetalleModificacion using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                INNER JOIN cat_UnidadResponsable u ON u.gpo_id = m.mod_gpo_id AND u.ur_id = m.mod_ur_id
                inner join enc_OrigenOperacion using(enc_oop,omo_id)
                inner join cat_TipoModificacion using(tmd_id)
                inner join cat_EstadoModificacion using(emo_id)
                inner join cat_TipoFolioModificacion using(tfm_id)
                $filtro $subsecretaria
            group by omo_id,mod_gpo_id,mod_ur_id,mod_id,cfi_clave
            $having ;";

        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Folios';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;

        $this->wrLlenaTabla($nav) == 0;

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
            $(".detalleFolio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    omo_id:$(this).attr("omo_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    mod_id:$(this).attr("mod_id"),
                    enc_oop:$(this).attr("enc_oop")
                });
                return false;
            });

            $(".imprimir").click(function()
                {
                    if($(this).attr("mmd_id")=="E"){
                        $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesPresupuestalesExternas.php");
                    }else{
                        $("#impresion").attr("action","rep/doc/scp/adecuaciones/rep_ModificacionesPresupuestalesInternas.php");
                    }
                    $("#omo_id").val($(this).attr("omo_id"));
                    $("#mod_gpo_id").val($(this).attr("mod_gpo_id"));
                    $("#mod_ur_id").val($(this).attr("mod_ur_id"));
                    $("#mod_id").val($(this).attr("mod_id"));
                    $("#mod_tipoMovimiento").val($(this).attr("mod_tipoMovimiento"));
                    $("#impresion").submit();
                }
            );

            $(".exportar").click(function()
                {
                    $("#repomo_id").val($(this).attr("repomo_id"));
                    $("#repmod_gpo_id").val($(this).attr("repmod_gpo_id"));
                    $("#repmod_ur_id").val($(this).attr("repmod_ur_id"));
                    $("#repmod_id").val($(this).attr("repmod_id"));
                    $("#mod_tipoMovimiento").val($(this).attr("mod_tipoMovimiento"));
                    $("#exportar").submit();
                }
            );

            $(".numfolio").click(function(){
                if($(this).attr("mmd_id")=="I" && $(this).attr("emo_id")==16){
                    comunica("' . $this->pantalla . '",{
                        metodo:"mostrarArchivos",
                        omo_id:$(this).attr("omo_id"),
                        mod_gpo_id:$(this).attr("mod_gpo_id"),
                        mod_ur_id:$(this).attr("mod_ur_id"),
                        mod_id:$(this).attr("mod_id")
                    });
                }
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
            $this->mixScript('bloquearClasAdmin();');
        } else {
            $mod = new MaestroModificacion();
            $origenOp = new OrigenOperacion();
            $abc = 'a';
            $this->mixScript('desbloquearClasAdmin();');
        }

        //INICIO BOTONES

        $btnRegresarTablaFolios = new stdClass();
        $btnRegresarTablaFolios->id = 'btnRegresarTablaFolios';
        $btnRegresarTablaFolios->class = 'regresar';
        $btnRegresarTablaFolios->icon = 'circle-left me-2';
        $btnRegresarTablaFolios->text = 'Regresar';
        $btnRegresarTablaFolios->value = 'Regresar';

        //FIN BOTONES

        $numeroFolio = new stdClass();
        $numeroFolio->id = 'folio';
        $numeroFolio->formato = 'entero';
        $numeroFolio->enDiv = true;
        $numeroFolio->maxlength = 6;
        $numeroFolio->validaAjax = 'folio';

        $btnHistorial = new stdClass();
        $btnHistorial->id = 'btnHistorial';
        $btnHistorial->class = 'historial';
        $btnHistorial->text = 'Historial';
        $btnHistorial->icon = 'fa fa-list-alt me-2';
        $btnHistorial->value = 'Historial';

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

        $numeroOperacion = new stdClass();
        $numeroOperacion->id = 'folio';
        $numeroOperacion->formato = 'entero';
        $numeroOperacion->enDiv = true;
        $numeroOperacion->maxlength = 6;
        $numeroOperacion->value = $mod->getOperacion();
        $numeroOperacion->readonly = true;

        $modalidadModificacion = new stdClass();
        $modalidadModificacion->id = 'mmd_id';
        $modalidadModificacion->label = 'Modalidad de Adecuación';
        $modalidadModificacion->name = 'mmd_id';
        if (
            $this->nivel <= 2 && $this->prm->omo_id != 'C' && $this->prm->omo_id
            != 'D'
        ) { //Si es nivel ur ó ue entonces muestra las dos modalidades para agregar el folio, de lo contrario solo mostrara externas.
            $modalidadModificacion->sql = 'select mmd_id,mmd_nombre from cat_ModalidadModificacion';
        } else {
            $modalidadModificacion->sql = 'select mmd_id,mmd_nombre from cat_ModalidadModificacion';
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
        $justificacion->label = 'Justificación';
        $justificacion->enDiv = true;
        $justificacion->maxlength = '300';
        $justificacion->required = true;
        $justificacion->texto = $mod->getConcepto();
        if ($mod->getConcepto() != '') {
            $justificacion->disabled = 'disabled';
        }

        $fecha = new stdClass();
        $fecha->id = 'fechaFolio';
        $fecha->enDiv = true;
        $fecha->required = true;
        $fecha->value = $mod->getFechaFolio();
        $ponFecha = '';
        if ($mod->getFechaFolio() != '') {
            $fecha->readonly = true;
            $ponFecha = '<fieldset class="col-md-4 col-xs-4">
                            <legend style="font-size:medium;">Fecha de creación</legend>
                            ' . $this->ponInput($fecha) . '
                        </fieldset>';
        }

        //consecutivo de folio
        $oficioSolicitud = new stdClass();
        $oficioSolicitud->id = 'oficioSolicitudSefin';
        $oficioSolicitud->label = 'Oficio de Solicitud Ejecutor de gasto';
        $oficioSolicitud->enDiv = true;
        $oficioSolicitud->maxlength = '35';
        $oficioSolicitud->required = true;
        $oficioSolicitud->value = $mod->getOficioSolicitudSefin();

        $oficioAutorizacion = new stdClass();
        $oficioAutorizacion->id = 'OficioSefin';
        $oficioAutorizacion->label = 'Oficio de Autorización SEFIN';
        $oficioAutorizacion->enDiv = true;
        $oficioAutorizacion->maxlength = '35';
        $oficioAutorizacion->required = true;
        $oficioAutorizacion->value = $mod->getOficioSefin();
        //CONSULTA PARA VERIFICAR SI SE PUEDE ELIMINAR EL FOLIO
        $elimina = $this->model->getEliminaFolio($mod);

        $consecutivo = ''; //Para agregar o no el consecutivo del folio en caso de que el nivel jerarquico sea >= 3
        $Op = ($origenOp->getOop() != '') ? $origenOp->getOop() : 0; //enviar al filtro de el tipo de modificacion
        $deshabilitarRadios = (!$elimina['registros']) ? ',deshabilitado:true' : ''; //deshabilitar radios de tipo modificacion y de tipo de folio.
        $usarComboUe = ''; // para la 116 en el caso de usuarios de nivel 2 o mayor >= 3
        $leyenda = ''; //titulo de ue para la 116
        $datosUe = ''; //requerido para la 116
        $estado = '';
        switch ($this->nivel) {
            case 1:
            case 2:
                if ($this->gpo . $this->ur == 116) {
                    $comboUe = new stdClass();
                    $comboUe->id = 'mod_ue_id';
                    $comboUe->label = 'Unidad Ejecutora';
                    $comboUe->sql = 'select ue_id,ue_nombre from cat_UnidadEjecutora where gpo_id=1 and ur_id=16 ' . (($abc
                        == 'c') ? 'and ue_id=' . $mod->getMod_ue_id() . '' : '') . ' order by ue_id;';
                    $comboUe->required = true;

                    $leyenda = '<legend>Unidad Ejecutora</legend>';

                    switch ($this->nivel) {
                        case 1:
                            $datosUe = 'mod_ue_id:' . $_SESSION[_S]['ue_id'] . ',';

                            break;
                        case 2:
                            $usarComboUe = $this->ponCombo($comboUe);
                            $datosUe = 'mod_ue_id:$("#mod_ue_id").val(),';
                            break;
                    }
                }
                if ($mod->getEmo_id() != '') {
                    $historialFolio = $this->ponBoton($btnHistorial);
                }

                break;
            default: //SEFIN

                $consecutivo = '
                                <fieldset class="col-md-3 col-xs-3">
                                    ' . $this->ponInput($oficioAutorizacion) . '
                                </fieldset>
                                <fieldset class="col-md-3 col-xs-3">
                                    ' . $this->ponInput($oficioSolicitud) . '
                                </fieldset>
                                ';

                if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {

                    $comboUe = new stdClass();
                    $comboUe->id = 'mod_ue_id';
                    $comboUe->sql = 'select ue_id,ue_nombre from cat_UnidadEjecutora where gpo_id=1 and ur_id=16 ' . (($abc
                        == 'c') ? 'and ue_id=' . $mod->getMod_ue_id() . '' : '') . ' order by ue_id;';
                    $comboUe->label = 'Unidad Ejecutora';
                    $usarComboUe = $this->ponCombo($comboUe);
                    $datosUe = 'mod_ue_id:$("#mod_ue_id").val(),';
                }
                if ($mod->getEmo_id() != '') {
                    $historialFolio = $this->ponBoton($btnHistorial);
                }

                break;
        }

        $this->mixHtml('
            <form id="Folio">
                <legend><span style = "font-size:14px;" class="label label-default">Datos Generales</span></legend>
                <div class="row">
                    <fieldset id="filaUe" class="col-md-4 col-xs-4">
                        ' . $usarComboUe . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-4 col-xs-6 mb-4">
                        <legend style="font-size:medium;">Número de folio</legend>
                        ' . $this->ponInput($numeroFolio) . '
                    </fieldset>
                    ' . (($mod->getOperacion() != 0) ? '
                        <fieldset class="col-md-4 col-xs-6 mb-4">
                            <legend style="font-size:medium;">Número de operación</legend>
                            ' . $this->ponInput($numeroOperacion) . '
                        </fieldset>
                    ' : '') . '
                </div>
                <div class="row">
                    <fieldset class="col-md-3 col-xs-3 mb-4">
                        ' . $this->ponCombo($modalidadModificacion) . '
                    </fieldset>
                    <fieldset id="tmd_tfm" class="col-md-3 col-xs-3">
                    </fieldset>
                    <fieldset id="tf" class="col-md-3 col-xs-3">
                    </fieldset>
                    <fieldset id="subf" class="col-md-3 col-xs-3">
                    </fieldset>
                    <fieldset id="tipfol" class="col-md-3 col-xs-3 mb-4">
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 mb-4">
                        ' . $this->ponTextArea($justificacion) . '
                    </fieldset>
                    ' . $ponFecha . '

                </div>
                <div class="row mb-4">
                ' . $consecutivo . '
                </div>
            </form>
            <div class="row">
                <fieldset id="historial" class="col-md-12 col-xs-12">

                </fieldset>
            </div>

        ');

        $this->agregaTools($this->ponBoton($btnRegresarTablaFolios) . ' ' . $historialFolio);

        $this->modificarMetas();

        $this->mixScript('
            $("#btnRegresarTablaFolios").removeClass("btn btn-default");
            $("#btnRegresarTablaFolios").addClass("btn btn-outline-secondary me-1");
            $("#btnHistorial").removeClass("btn btn-default");
            $("#btnHistorial").addClass("btn btn-outline-secondary me-1");

            if($("#tmd_tfm").length){
                comunica("' . $this->pantalla . '",{
                    metodo:"agregaTipoModificacion",
                    mmd_id: $(this).find("option:selected").val(),
                    enc_oop:' . $Op . '
                    ' . $deshabilitarRadios . '
                    ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"'
            : " ,omo_id:'A' ") . '
                    ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"'
            : '') . '
                    ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"'
            : '') . '
                    ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"'
            : '') . '
                });
            }

            $("#btnRegresarTablaFolios").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    regresar:1,
                    tmd_id:"",
                    tfm_id:"",
                    ecf_id2:"",
                    emo_id:""
                    ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"'
            : '') . '
                    ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"'
            : '') . '
                    ' . (isset($this->prm->mod_gpo_id) && isset($this->prm->mod_ur_id) ? ',ur_clave:' . $this->prm->mod_gpo_id . $this->prm->mod_ur_id . ''
            : ',ur_clave:' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '') . '

                    });
                return false;
            });

            $("#btnHistorial").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaHistorialFolio"
                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                    ' . (isset($this->prm->mod_gpo_id) ? ',mod_gpo_id:' . $this->prm->mod_gpo_id . ''
            : '') . '
                    ' . (isset($this->prm->mod_ur_id) ? ',mod_ur_id:' . $this->prm->mod_ur_id . '' : '') . '
                    ' . (isset($this->prm->mod_id) ? ',mod_id:' . $this->prm->mod_id . '' : '') . '
                    ' . (($origenOp->getOop() != '') ? ',enc_oop:' . $this->prm->enc_oop . '' : '') . '
                });
            });
        ');
    }

    private function agregaTipoModificacion()
    {
        $this->setContenedor('tmd_tfm');

        if (isset($this->prm->enc_oop) && $this->prm->enc_oop != 0) {
            $origenOp = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        } else {
            $origenOp = new OrigenOperacion();
        }

        $parametros = [];
        switch ($this->nivel) {
            case 1:
            case 2:
                $sql = '
                SELECT tmd_id,tmd_nombre,omo_id,tfm_id,mmd_id,enc_oop
                FROM cat_TipoModificacion
                INNER JOIN enc_OrigenOperacion USING(tmd_id)
                WHERE mmd_id= :mmd_id AND omo_id= :omo_id
                GROUP BY tmd_id,tfm_id,enc_oop
                ORDER BY tmd_orden;';
                $parametros = [
                    'mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id
                    : 'E'),
                    'omo_id' => (isset($this->prm->omo_id) && !empty($this->prm->omo_id)
                    ? $this->prm->omo_id : 'A'),
                ];
                break;
            default:
                switch ($this->subsec) {
                    case 2: //planeacion
                        $sql = '
                            SELECT tmd_id,tmd_nombre,omo_id,tfm_id,mmd_id,enc_oop
                            FROM cat_TipoModificacion
                            INNER JOIN enc_OrigenOperacion USING(tmd_id)
                            WHERE mmd_id= :mmd_id AND omo_id= :omo_id
                            GROUP BY tmd_id,tfm_id,enc_oop
                            ORDER BY tmd_orden;';
                        $parametros = [
                            'mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id)
                            ? $this->prm->mmd_id : 'E'),
                            'omo_id' => (isset($this->prm->omo_id)
                            && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A'),
                        ];
                        break;
                    case 3: //egresos
                        $sql = '
                            SELECT tmd_id,tmd_nombre,omo_id,tfm_id,mmd_id,enc_oop
                            FROM cat_TipoModificacion
                            INNER JOIN enc_OrigenOperacion USING(tmd_id)
                            WHERE mmd_id= :mmd_id AND omo_id= :omo_id
                            GROUP BY tmd_id,tfm_id,enc_oop
                            ORDER BY tmd_orden;';
                        $parametros = [
                            'mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id)
                            ? $this->prm->mmd_id : 'E'),
                            'omo_id' => (isset($this->prm->omo_id)
                            && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A'),
                        ];
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
        $tipoModificacion->params = $parametros;
        $tipoModificacion->selecciona = $origenOp->getTmd_id();
        $tipoModificacion->required = true;
        $tipoModificacion->value = $origenOp->getTmd_id();
        if ($origenOp->getTmd_id() != '' && isset($this->prm->deshabilitado)) {
            $tipoModificacion->disabled = true;
        }

        $this->mixHtml($this->ponCombo($tipoModificacion));

        if (isset($this->prm->deshabilitado)) {
            $this->prm->deshabilitado = $this->prm->deshabilitado;
        }

        $this->prm->omo_id = (isset($this->prm->omo_id) && !empty($this->prm->omo_id))
        ? $this->prm->omo_id : 'A';
        $this->prm->enc_oop = (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop))
        ? $this->prm->enc_oop : '';
        $this->prm->mmd_id = (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id))
        ? $this->prm->mmd_id : 'E';
        $this->agregaTipoFolio();

        $this->mixScript('
            $("#tmd_id").change(function(){
                switch($("#mmd_id").find("option:selected").val()){
                    case "I":
                        comunica("' . $this->pantalla . '",{
                            metodo:"agregaTipoFolio",
                            tmd_id: $("#tmd_id").find("option:selected").val()
                            ' . (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop) ? ',enc_oop:"' . $this->prm->enc_oop . '"'
            : '') . '
                            ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"'
            : 'A') . '
                            ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"'
            : '') . '
                            ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"'
            : '') . '
                            ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"'
            : '') . '
                            ' . (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? ',mmd_id:"' . $this->prm->mmd_id . '"'
            : '') . '
                            ' . (isset($this->prm->deshabilitado) && !empty($this->prm->deshabilitado)
            ? ',deshabilitado:"' . $this->prm->deshabilitado . '"' : '') . '
                        });
                    break;
                    case "E":
                        comunica("' . $this->pantalla . '",{
                            metodo:"agregaTipoFolio",
                            tmd_id: $("#tmd_id").find("option:selected").val(),
                            agregaTipo:(($("#tmd_id").find("option:selected").val()=="B") ? $("#tmd_id").find("option:selected").val():0)
                            ' . (isset($this->prm->enc_oop) && !empty($this->prm->enc_oop) ? ',enc_oop:"' . $this->prm->enc_oop . '"'
            : '') . '
                            ' . (isset($this->prm->omo_id) && !empty($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"'
            : 'A') . '
                            ' . (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"'
            : '') . '
                            ' . (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"'
            : '') . '
                            ' . (isset($this->prm->mod_id) && !empty($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"'
            : '') . '
                            ' . (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? ',mmd_id:"' . $this->prm->mmd_id . '"'
            : '') . '
                            ' . (isset($this->prm->deshabilitado) && !empty($this->prm->deshabilitado)
            ? ',deshabilitado:"' . $this->prm->deshabilitado . '"' : '') . '
                        });
                    break;
                }
                return false;
            });

            ' . (isset($this->prm->enc_oop) && ($this->prm->enc_oop != 0) && $origenOp->getTmd_id()
            != '' ? '$("#tmd_id").change();' : '') . '
        ');
    }

    private function agregaTipoFolio()
    {

        if (isset($this->prm->tmd_id) || (isset($this->prm->agregaTipo) && $this->prm->agregaTipo
            == 'B')) {
            $this->setContenedor('tf');
        }
        $this->setContenedor('tf');
        if (isset($this->prm->enc_oop) && $this->prm->enc_oop != 0) {
            $origenOp = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        } else {
            $origenOp = new OrigenOperacion();
        }

        $parametros2 = [];
        switch ($this->nivel) {
            case 1:
            case 2:
                $sql2 = '
                SELECT tfm_id,tfm_nombre,omo_id,tmd_id,mmd_id,enc_oop
                FROM cat_TipoFolioModificacion
                INNER JOIN enc_OrigenOperacion USING(tfm_id)
                WHERE mmd_id= :mmd_id AND omo_id= :omo_id
                ' . (isset($this->prm->tmd_id) ? ' and tmd_id="' . $this->prm->tmd_id . '"' : '') . '
                GROUP BY tfm_id,tmd_id,enc_oop;';
                $parametros2 = [
                    'mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id) ? $this->prm->mmd_id
                    : 'E'),
                    'omo_id' => (isset($this->prm->omo_id) && !empty($this->prm->omo_id)
                    ? $this->prm->omo_id : 'A'),
                ];
                break;
            default:
                switch ($this->subsec) {
                    case 2:
                        $sql2 = '
                            SELECT tfm_id,tfm_nombre,omo_id,tmd_id,mmd_id,enc_oop
                            FROM cat_TipoFolioModificacion
                            INNER JOIN enc_OrigenOperacion USING(tfm_id)
                            WHERE mmd_id= :mmd_id AND omo_id= :omo_id
                            GROUP BY tfm_id,tmd_id,enc_oop;';
                        $parametros2 = [
                            'mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id)
                            ? $this->prm->mmd_id : 'E'),
                            'omo_id' => (isset($this->prm->omo_id)
                            && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A'),
                        ];
                        break;
                    case 3:
                        $sql2 = '
                            SELECT tfm_id,tfm_nombre,omo_id,tmd_id,mmd_id,enc_oop
                            FROM cat_TipoFolioModificacion
                            INNER JOIN enc_OrigenOperacion USING(tfm_id)
                            WHERE mmd_id= :mmd_id AND omo_id= :omo_id
                            GROUP BY tfm_id,tmd_id,enc_oop;';
                        $parametros2 = [
                            'mmd_id' => (isset($this->prm->mmd_id) && !empty($this->prm->mmd_id)
                            ? $this->prm->mmd_id : 'E'),
                            'omo_id' => (isset($this->prm->omo_id)
                            && !empty($this->prm->omo_id) ? $this->prm->omo_id : 'A'),
                        ];
                        break;
                }
                break;
        }

        $tipoFolio = new stdClass();
        $tipoFolio->id = 'tfm_id';
        $tipoFolio->label = 'Tipo de Folio';
        $tipoFolio->sql = $sql2;
        $tipoFolio->params = $parametros2;
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
        if ((isset($this->prm->mmd_id) && $this->prm->mmd_id == 'E' && $this->nivel
            >= 3) ||
            (isset($this->prm->mmd_id) && $this->prm->mmd_id == 'E' && $this->nivel
                <= 2 && $this->perfil != 'S')
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

            switch ($this->nivel) {
                case 1:
                case 2:
                    $subsecretaria->opciones = [
                        1 => 'SUBSECRETARÍA DE EGRESOS',
                        2 => 'SUBSECRETARÍA DE PLANEACIÓN',
                    ];
                    $subsecretaria->value = $maestr->getSubsecretaria();
                    $subsecretaria->selecciona = $maestr->getSubsecretaria();
                    if ($maestr->getSubsecretaria() != '' && isset($this->prm->deshabilitado)) {
                        $subsecretaria->disabled = true;
                    }
                    break;
                default:
                    switch ($this->subsec) {
                        case 2:
                            $subsecretaria->opciones = [2 => 'SUBSECRETARÍA DE PLANEACIÓN'];
                            $subsecretaria->value = 2;
                            $subsecretaria->selecciona = 2;
                            break;
                        case 3:
                            $subsecretaria->opciones = [
                                1 => 'SUBSECRETARÍA DE EGRESOS',
                                2 => 'SUBSECRETARÍA DE PLANEACIÓN',
                            ];
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

            $sub = $this->ponCombo($subsecretaria);
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

            $tipoMov = $this->ponCombo($tipoM);
        }
        $tipoFolio = $this->ponCombo($tipoFolio);
        $this->mixHtml($tipoMov);
        $this->setContenedor('subf');
        $this->mixHtml($sub);
        $this->setContenedor('tipfol');
        $this->mixHtml($tipoFolio);
    }

    //Metodo para mostrar las modificaciones o movimientos agregados al folio por la UR
    private function tablaModificaciones()
    {

        $btnRegresarTablaFolios = new stdClass();
        $btnRegresarTablaFolios->id = 'btnRegresarTablaFolios';
        $btnRegresarTablaFolios->class = 'btn-test btnRegresarTablaFolios';
        $btnRegresarTablaFolios->icon = 'circle-left me-2';
        $btnRegresarTablaFolios->text = 'Regresar';
        $btnRegresarTablaFolios->value = 'Regresar';

        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);

        $folio = new stdClass();
        $folio->id = 'folio';
        $folio->label = 'Número de Folio';
        $folio->enDiv = true;
        $folio->formato = 'entero';
        $folio->maxlength = 6;
        $folio->value = (int) $mod->getMod_id();
        $folio->disabled = true;

        $modalidadModificacion = new stdClass();
        $modalidadModificacion->id = 'modalidadMod';
        $modalidadModificacion->label = 'Modalidad de Adecuación';
        $modalidadModificacion->enDiv = true;
        $modalidadModificacion->maxlength = 1;
        $modalidadModificacion->value = (($enc_oop->getMmd_id() == 'E') ? 'EXTERNAS' : 'INTERNAS');
        $modalidadModificacion->disabled = true;

        $tipoFolio = new stdClass();
        $tipoFolio->id = 'tipoFolio';
        $tipoFolio->label = 'Tipo de Adecuación';
        $tipoFolio->enDiv = true;
        $tipoFolio->maxlength = 30;
        $tipoFolio->value = $tipoMod->getNombre();
        $tipoFolio->disabled = true;

        $tipoMovimiento = new stdClass();
        $tipoMovimiento->id = 'tipoMov';
        $tipoMovimiento->label = 'Tipo Movimiento';
        $tipoMovimiento->enDiv = true;
        $tipoMovimiento->maxlength = 30;
        if ($mod->getTipoMovimiento() == 'A') {
            $tipoMovimiento->value = 'AMPLIACIÓN';
        }

        if ($mod->getTipoMovimiento() == 'R') {
            $tipoMovimiento->value = 'REDUCCIÓN';
        }

        $tipoMovimiento->disabled = true;

        $html = '<div class="row mb-4">
                    <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-4 col-xs-4') . '">
                        ' . $this->ponInput($folio) . '
                    </fieldset>
                    <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-4 col-xs-4') . '">
                        ' . $this->ponInput($modalidadModificacion) . '
                    </fieldset>
                    <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-4 col-xs-4') . '">
                        ' . $this->ponInput($tipoFolio) . '
                    </fieldset>
                    ' . ($enc_oop->getTmd_id() == 'B' ? '
                    <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : '') . '">
                        ' . $this->ponInput($tipoMovimiento) . '
                    </fieldset>
                    ' : '') . '
                </div>
                <div class="row">
                    <fieldset id="Mensaje" class="col-md-12 col-xs-12">

                    </fieldset>
                </div>
                ';

        $tabla = new stdClass();
        $tabla->id = 'tbl-Modificaciones';
        $tabla->tools = $html;
        $tabla->pie = $this->totalFolio($this->prm);
        $tabla->titulo = 'Adecuaciones Presupuestales ingresadas por folio';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->omo_id = (isset($this->prm->omo_id) && !empty($this->prm->omo_id))
        ? $this->prm->omo_id : '';
        $tabla->comunica->mod_gpo_id = (isset($this->prm->mod_gpo_id) && !empty($this->prm->mod_gpo_id))
        ? $this->prm->mod_gpo_id : '';
        $tabla->comunica->mod_ur_id = (isset($this->prm->mod_ur_id) && !empty($this->prm->mod_ur_id))
        ? $this->prm->mod_ur_id : '';
        $tabla->comunica->mod_id = (isset($this->prm->mod_id) && !empty($this->prm->mod_id))
        ? $this->prm->mod_id : '';
        $tabla->comunica->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision
            != '') ? $this->prm->nivelRevision : '';
        $tabla->comunica->metodo = 'llenaTablaModificaciones';

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->texto = 'Ver / Editar';
        array_push($tr, $th);

        //Partida presupuestal agregada al folio
        $th = new stdClass();
        $th->texto = 'Partida Presupuestal';
        array_push($tr, $th);

        //tipo de clave presupuestal C ó D
        $th = new stdClass();
        $th->texto = 'Tipo';
        array_push($tr, $th);

        //Descripcion de la partida presupuestal
        $th = new stdClass();
        $th->texto = 'Descripción';
        array_push($tr, $th);

        //Importe de la modificacion presupuestal
        $th = new stdClass();
        $th->texto = 'Importe';
        array_push($tr, $th);

        //Tipo de movimiento realizado en la modificacion A, R ó C
        $th = new stdClass();
        $th->texto = 'Tipo de Movimiento';
        array_push($tr, $th);

        $trbody = [];

        //contenido ver
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'eye';
        $td->attr = new stdClass();
        $td->attr->omo_id = 'omo_id';
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->attr->mod_id = 'mod_id';
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->ue_id = 'ue_id';
        $td->attr->epg_id = 'epg_id';
        $td->attr->pg_id = 'pg_id';
        $td->attr->pe_id = 'pe_id';
        $td->attr->ecf_id = 'ecf_id';
        $td->attr->tcp_id = 'tcp_id';
        $td->class = 'addModModificacion';
        array_push($trbody, $td);

        //Partida Presupuestal
        $td = new stdClass();
        $td->campoTxt = 'pre_claveCompleta';
        array_push($trbody, $td);

        //Tipo de clave presupuestal
        $td = new stdClass();
        $td->campoTxt = 'tcp_id';
        array_push($trbody, $td);

        //Nombre de la partida presupuestal
        $td = new stdClass();
        $td->campoTxt = 'pe_nombre';
        array_push($trbody, $td);

        //Importe del movimiento
        $td = new stdClass();
        $td->campoTxt = 'dmo_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        //Movimiento realizado
        $td = new stdClass();
        $td->campoTxt = 'mov_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');
        $this->llenaTablaModificaciones();

        $this->agregaTools($this->ponBoton($btnRegresarTablaFolios));
        $this->mixScript('
            // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });

            // Nombres de campo necesarios para la pantalla de selección de encuadres
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";

            desbloquearClasAdmin();
            $("' . (($this->nivel == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();

            $("#btnRegresarTablaFolios").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    ecf_id:"' . $mod->getEcf_id() . '",
                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    omo_id:"\'' . $mod->getOmo_id() . '\'",
                    regresar:1,
                    tmd_id:"",
                    tfm_id:"",
                    ecf_id2:"",
                    emo_id:""
                    ' . (isset($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                });
                return false;
            });
            $(".tablepie").addClass("col-md-12 col-xs-12");
            $("#btnRegresarTablaFolios").removeClass("btn btn-default");
            $("#btnRegresarTablaFolios").addClass("btn btn-outline-secondary me-1");
        ');
    }

    private function llenaTablaModificaciones()
    {
        $sql = 'select omo_id, mod_gpo_id, mod_ur_id, mod_id, gpo_id, ur_id, ue_id, epg_id,
            pg_id,pe_id,ecf_id, tcp_id,
            pe_nombre, mov_nombre, dmo_importe,emo_id, pre_claveCompleta
            from scp_DetalleModificacion
                INNER JOIN presupuesto using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN scp_MaestroModificacion using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                INNER JOIN cat_PartidaEspecifica using(pg_id,pe_id)
                INNER JOIN cat_TipoMovimiento using(mov_id)
                ' . (isset($this->prm->omo_id) ? 'WHERE omo_id= :omo_id ' : '') . '
                ' . (isset($this->prm->mod_gpo_id) ? ' AND mod_gpo_id= :mod_gpo_id'
            : '') . '
                ' . (isset($this->prm->mod_ur_id) ? ' AND mod_ur_id= :mod_ur_id'
            : '') . '
                ' . (isset($this->prm->mod_id) ? ' AND mod_id= :mod_id' : '') . '
                ' . (($this->perfil == 'S') ? " AND tcp_id='D'" : '') . '
                ' . (($this->perfil == 'U') ? " AND tcp_id='C'" : '') . '
                ' . (($this->nivel <= 2) ? " AND tcp_id!='P'" : '') . ';';

        $parametros = [
            'omo_id'     => $this->prm->omo_id,
            'mod_gpo_id' => $this->prm->mod_gpo_id,
            'mod_ur_id'  => $this->prm->mod_ur_id,
            'mod_id'     => $this->prm->mod_id,
        ];
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Modificaciones';
        $nav->nf = 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".addModModificacion").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"' . (($this->nivel <= 2) ? 'addModModificacion' : 'addModPrevision') . '",
                    omo_id:$(this).attr("omo_id"),
                    mod_id:$(this).attr("mod_id"),
                    mod_gpo_id:$(this).attr("mod_gpo_id"),
                    mod_ur_id:$(this).attr("mod_ur_id"),
                    gpo_id:$(this).attr("gpo_id"),
                    ur_id:$(this).attr("ur_id"),
                    ue_id:$(this).attr("ue_id"),
                    epg_id:$(this).attr("epg_id"),
                    pg_id:$(this).attr("pg_id"),
                    pe_id:$(this).attr("pe_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    tcp_id:$(this).attr("tcp_id")
                });
                return false;
            });
        ');
    }

    //Metodo para agregar modificaciones o movimientos al folio por la UR.
    private function addModModificacion()
    {
        $valorEpg = '';
        $nombreEpg = '';
        $html = '';
        $script = '';

        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $modClave = new DetalleModificacion($this->prm);
        $abc = 'a';
        if (
            $modClave->getOmo_id() != '' && $modClave->getMod_gpo_id() != '' && $modClave->getMod_ur_id()
            != '' && $modClave->getMod_id() != '' && $modClave->getGpo_id() != ''
            && $modClave->getUr_id() != '' && $modClave->getUe_id() != '' && $modClave->getEpg_id()
            != '' && $modClave->getPg_id() != '' && $modClave->getPe_id() != '' && $modClave->getEcf_id()
            != '' && $modClave->getTcp_id() != ''
        ) {

            $abc = 'c';
            if (($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A')
                || $enc_oop->getTmd_id() == 'D'
            ) {
                $modClave->setTcp_id('');
            }
        }
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);

        $infPre = new stdClass();
        $infPre->gpo_id = $mod->getMod_gpo_id();
        $infPre->ur_id = $mod->getMod_ur_id();
        $infPre->ue_id = $modClave->getUe_id();
        $infPre->epg_id = $modClave->getEpg_id();
        $infPre->pg_id = $modClave->getPg_id();
        $infPre->pe_id = $modClave->getPe_id();
        $infPre->ecf_id = $modClave->getEcf_id();

        //Determinamos si se debe o no mostrar el tipo de Compra, dependiendo del tipo modificacion y si la UR esta excenta o no.
        if ((($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A')
            || $enc_oop->getTmd_id() == 'D') && !UnidadResponsable::excentaComprasConsolidadas($infPre)) {
            $infPre->tcp_id = '';
        } else {
            $infPre->tcp_id = $modClave->getTcp_id();
        }

        $pre = new Presupuesto($infPre);
        $mod_ue_id = '';
        $clasAdmin = new stdClass();
        $clasAdmin->gpo_id = $mod->getMod_gpo_id();
        $clasAdmin->ur_id = $mod->getMod_ur_id();
        if ($this->gpo . $this->ur == 116) {
            $clasAdmin->ue_id = $mod->getMod_ue_id();
            $mod_ue_id = $mod->getMod_ue_id();
        } else {
            $clasAdmin->ue_id = $modClave->getUe_id();
        }
        $clasAdmin->ecf_id = $mod->getEcf_id();
        $clasAdmin->form = 'Modificaciones';
        $clasAdmin->toa_id = $mod->getSubsecretaria();
        $clasAdmin->filtro = 'porUrClaveFinanciamiento';
        if ($abc == 'c') {
            $this->conectar();
            $this->setSql('SELECT prg_id,spr_id,py_id,oa_id from enc_Programatico where epg_id= :epg_id');
            $parametros = [
                'epg_id' => $modClave->getEpg_id(),
            ];
            $this->consultar($parametros);
            $this->desconectar();
            $epg = $this->extraer();
            $clasAdmin->prg_id = $epg['prg_id'];
            $clasAdmin->spr_id = $epg['spr_id'];
            $clasAdmin->py_id = $epg['py_id'];
            $clasAdmin->oa_id = $epg['oa_id'];
            $clasAdmin->lectura = true;

            // Ponemos la clasificación administrativa registrada
            $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($modClave->getEpg_id());
            $valorEpg = $mod->getMod_gpo_id() . $mod->getMod_ur_id() . '.' . $modClave->getUe_id() . '.' . $_SESSION[_S]['clasAdm-prg'] . '.' . $_SESSION[_S]['clasAdm-spr'] . '.' . $_SESSION[_S]['clasAdm-py'] . '.' . $_SESSION[_S]['clasAdm-oa'];
            $encLectura = ' disabled ';
        }

        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));

        $objGasto = new stdClass();
        $objGasto->filtro = 'modificaciones';
        $objGasto->tmd_id = $enc_oop->getTmd_id();
        $objGasto->tfm_id = $enc_oop->getTfm_id();
        $objGasto->mmd_id = $enc_oop->getMmd_id();
        $objGasto->ecf_id = $mod->getEcf_id();
        $objGasto->tipoMovimiento = $mod->getTipoMovimiento();

        switch ($this->perfil) {
            case 'U':
                $objGasto->tcp_id = 'C';
                break;
            case 'S':
                $objGasto->tcp_id = 'D';
                break;
        }
        if ($abc == 'c') {
            $objGasto->pg_id = $modClave->getPg_id();
            $objGasto->pe_id = $modClave->getPe_id();
            $objGasto->lectura = true;
        }

        switch ($enc_oop->getTmd_id()) {
            default: //Si el tipo de modificacion es diferente de ampliacion.
                $tipoCompra = new stdClass();
                if ($abc == 'c') {
                    $tipoCompra->tcp_id = $modClave->getTcp_id();
                } else {
                    $objGasto->changePe = '
                        if($("#pg_id").val()!="" && $("#pe_id").val()!=""){
                            comunica("srv/TipoCompra.php",{
                                metodoTipoCompra:"cargaTipoCompra",
                                omo_id:"' . $enc_oop->getOmo_id() . '",
                                mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                                mod_id:' . $mod->getMod_id() . ',
                                tmd_id:"' . $enc_oop->getTmd_id() . '",
                                pg_id:$("#pg_id").val(),
                                pe_id:$("#pe_id").val(),
                                ecf_id:"' . $mod->getEcf_id() . '"
                                ' . (($this->perfil == 'S') ? ',tcp_id2:"D"' : '') . '
                                ' . (($this->perfil == 'U') ? ',tcp_id2:"C"' : '') . '
                            });
                        }
                    ';
                }
                break;
            case 'B': //Traspaso interinstitucional
                switch ($mod->getTipoMovimiento()) {
                    case 'A': // Ampliación
                        if ($abc == 'a') {
                            $objGasto->changePe = '
                                comunica("' . $this->pantalla . '",{
                                    metodo:"muestraPresupuesto",
                                    tmd_id:"' . $enc_oop->getTmd_id() . '",
                                    tipoMovimiento:"' . $mod->getTipoMovimiento() . '",
                                    pg_id:$("#pg_id").val(),
                                    pe_id:$("#pe_id").val(),
                                    ecf_id:"' . $mod->getEcf_id() . '"
                                });
                            ';
                        }
                        break;
                    case 'R': // Reducción
                        $tipoCompra = new stdClass();
                        if ($abc == 'c') {
                            $tipoCompra->tcp_id = $modClave->getTcp_id();
                        } else {
                            $objGasto->changePe = '
                                if($("#pg_id").val()!="" && $("#pe_id").val()!=""){
                                    comunica("srv/TipoCompra.php",{
                                        metodoTipoCompra:"cargaTipoCompra",
                                        omo_id:"' . $enc_oop->getOmo_id() . '",
                                        mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                                        mod_ur_id:"' . $mod->getMod_ur_id() . '",
                                        mod_id:' . $mod->getMod_id() . ',
                                        tmd_id:"' . $enc_oop->getTmd_id() . '",
                                        pg_id:$("#pg_id").val(),
                                        pe_id:$("#pe_id").val(),
                                        ecf_id:"' . $mod->getEcf_id() . '"
                                        ' . (($this->perfil == 'S') ? ',tcp_id2:"D"' : '') . '
                                        ' . (($this->perfil == 'U') ? ',tcp_id2:"C"' : '') . '
                                    });
                                }
                            ';
                        }
                        break;
                }
                break;
            case 'D':
                if ($abc == 'a') {
                    $objGasto->changePe = '
                        comunica("' . $this->pantalla . '",{
                            metodo:"muestraPresupuesto",
                            tmd_id:"' . $enc_oop->getTmd_id() . '",
                            tipoMovimiento:"' . $mod->getTipoMovimiento() . '",
                            pg_id:$("#pg_id").val(),
                            pe_id:$("#pe_id").val(),
                            ecf_id:"' . $mod->getEcf_id() . '"
                        });
                    ';
                }
                break;
        }

        $cveFin = new stdClass();
        $cveFin->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $cveFin->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $cveFin->ecf_id = $mod->getEcf_id();
        $cveFin->filtro = (($this->perfil == 'U') ? 'presupuestoComprasConsolidadas'
            : 'presupuesto');
        $cveFin->lectura = true;
        $cveFin->controlador = 'modificaciones';

        $btnRegresarTablaModificacion = new stdClass();
        $btnRegresarTablaModificacion->id = 'btnRegresarTablaModificacion';
        $btnRegresarTablaModificacion->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresarTablaModificacion->class = 'me-1';
        $btnRegresarTablaModificacion->color = 'gris';
        $btnRegresarTablaModificacion->text = 'Regresar';
        $btnRegresarTablaModificacion->value = 'Regresar';

        $folio = new stdClass();
        $folio->id = 'folio';
        $folio->formato = 'entero';
        $folio->enDiv = true;
        $folio->value = (int) $mod->getMod_id();
        $folio->required = true;
        $folio->disabled = true;

        $modalidad = new stdClass();
        $modalidad->id = 'modalidadM';
        $modalidad->enDiv = true;
        $modalidad->value = ($enc_oop->getMmd_id() == 'E' ? 'EXTERNAS' : 'INTERNAS');
        $modalidad->required = true;
        $modalidad->disabled = true;

        $tipoModificacion = new stdClass();
        $tipoModificacion->id = 'tmd_id';
        $tipoModificacion->enDiv = true;
        $tipoModificacion->value = $tipoMod->getNombre();
        $tipoModificacion->required = true;
        $tipoModificacion->disabled = true;

        $objMov = new stdClass();
        $objMov->omo_id = $mod->getOmo_id();
        $objMov->mod_gpo_id = $mod->getMod_gpo_id();
        $objMov->mod_ur_id = $mod->getMod_ur_id();
        $objMov->mod_id = $mod->getMod_id();
        $objMov->mov_id = $modClave->getMov_id();
        $objMov->tcp_id = $modClave->getTcp_id();

        /* Input importe del movimiento */
        $importeaDistribuir = new stdClass();
        $importeaDistribuir->id = 'importe';
        $importeaDistribuir->label = 'Importe a distribuir';
        $importeaDistribuir->formato = 'moneda';
        $importeaDistribuir->class = 'validaTotMovimiento';
        $importeaDistribuir->required = true;
        $importeaDistribuir->value = $modClave->getImporte();
        if ($enc_oop->getTmd_id() == 'C') {
            $importeaDistribuir->value = $pre->getVigente();
            $importeaDistribuir->disabled = true;
        }
        /* Input importe por dist del movimiento */
        $importePorDistribuir = new stdClass();
        $importePorDistribuir->id = 'importeb';
        $importePorDistribuir->label = 'Saldo por distribuir';
        $importePorDistribuir->formato = 'moneda';
        $importePorDistribuir->disabled = 'disabled';

        $form = new stdClass();
        $form->id = 'Modificaciones';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcModificacion';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->mod_gpo_id = $mod->getMod_gpo_id();
        $form->datosExtra->mod_ur_id = $mod->getMod_ur_id();
        $form->datosExtra->omo_id = $mod->getOmo_id();
        $form->datosExtra->mod_id = $mod->getMod_id();
        $form->datosExtra->ecf_id = $mod->getEcf_id();
        if ($abc == 'c') {
            $form->datosExtra->tcp_id = $modClave->getTcp_id();
        }

        $form->datosExtra->meses = '"{ valueMeses();}"';
        $form->datosExtra->importe = '"{ $("#importe").val();}"';
        if (isset($this->prm->nivelRevision)) {
            $form->datosExtra->nivelRevision = $this->prm->nivelRevision;
        }

        $form = new form($form);

        $html = '
            <form id="Modificaciones">
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Enc. Programático <span id="comoSeUsa" style="text-decoration: underline; color:red; cursor: pointer;">¿ cómo se usa ?</span></legend>
                    <label>Encuadre Programático</label><br />
                    <fieldset class="col-md-3 col-xs-3">
                        <input type="text" value="' . $valorEpg . '" maxlength="20" ' . $encLectura . ' placeholder="Encuadre (F1/F2)" name="encProg" id="encProg" class=" form-control F1Catalogo" required="true">
                    </fieldset>
                    <fieldset class="col-md-9 col-xs-9">
                        <input type="text" value="' . $nombreEpg . '" disabled placeholder="Nombre Obra/Actividad" name="nomProg" id="nomProg" class=" form-control">
                    </fieldset>
                </fieldset>
                <br/>
                <fieldset class="col-md-12 col-xs-12">
                    ' . ObjetoGasto::ponObjetoGasto($objGasto) . '
                </fieldset>
            </div>
            <div id="contenedorCFModal"></div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <fieldset class="col-md-6 col-xs-6">
                        ' . ClasificacionFinanciamiento::creaComboCveFin($cveFin) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
            ';

        if (!(($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A')
            || $enc_oop->getTmd_id() == 'D')) {
            $html .= TipoCompra::getTipoCompra($tipoCompra);
        }
        $html .= '</fieldset>
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <div id = "grafica" name = "grafica" style="position:inherid; height:200px;"/>
                </fieldset>
            </div><br/><br/>
            <div class="row">
                <fieldset class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Folio</legend>
                        ' . $this->ponInput($folio) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Modalidad</legend>
                        ' . $this->ponInput($modalidad) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo de adecuación</legend>
                        ' . $this->ponInput($tipoModificacion) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo de movimiento</legend>
                        ' . $this->ponRadio(TipoMovimiento::objMovimiento($objMov)) . '
                    </fieldset>
                </fieldset>
            </div>
            <div class="row"><!--calendario-->
                <fieldset class="col-md-12 col-xs-12">
                    <div class="panel panel-default">
                        <!-- Default panel contents -->
                        <div class="panel-heading"><b style="font-size:17px;">Calendario presupuestado</b></div>
                            <div class="panel-body">
                                <div class="row">
                                    <fieldset class="row col-md-12 col-xs-12">
                                        <fieldset class="col-md-6 col-xs-6">
                                            ' . $this->ponInput($importeaDistribuir) . '
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">
                                            ' . $this->ponInput($importePorDistribuir) . '
                                        </fieldset>
                                    </fieldset>
                                </div>
                            </div>
                        <!-- Table -->
                        <table id="pres" class=" table table-striped" style="border-collapse:inherit !important">
                        <thead>
                            <tr id="encabezado" >
                                <th style="width:200px">Mes</th>
                                <th style="width:250px">Importe Actual</th>
                                <th style="width:250px">Importe Devengado</th>
                                <th style="display:none"></th>
                                <th style="width:250px">Importe Disponible (Trimestre)</th>
                                <th style="width:250px align:center">Distribución</th>
                            </tr>
                            </thead>
                            <tbody id="tbody-tabla-tablaPresupuesto"></tbody>
                        </table>
                    </div>
                </fieldset>
            </div>
            </form>
            <!--fin calendario-->';

        $form->mixHtml($html);

        $this->agregaTools($this->ponBoton($btnRegresarTablaModificacion));
        $this->mixScript('
            function validaTotMovimiento(field)
            {
                valido = true;
                var importe = 0;
                var total = 0;
                importe = parseFloat( unformatNumber( $(field).val() ).toFixed(2) );
                console.log("importe: "+importe);
                $(".modificacion").each(function(){ total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 ); });
                console.log("total: "+total);
                if( importe.toFixed(2) != total.toFixed(2) ){
                    error(field,"El importe no coincide con la suma de los meses. Favor de verificar");
                    valido = false;
                }else if( importe.toFixed(2)<= 0 ){
                    error(field,"El importe no debe ser 0");
                    valido = false;
                }
                return valido;
            }

            function validaModificacion(mov,mod){
                if(mov != "" && mov != undefined  && mod !="" && mod != undefined ){
                    if(mod =="INTERNAS")
                        mod = "I";
                    else if(mod== "EXTERNAS")
                        mod = "E";
                }else{
                    if ( $("#modalidadM").val() =="INTERNAS"){
                        mod = "I";
                    }else{
                        mod = "E";
                    }
                    $("input:radio[name=mov_id]").each(function(){
                        if( $(this).is(":checked")){
                            mov = $(this).val();
                        }
                    });
                }

                vigente = 0;
                calendario = 0;
                mes=0;

                // VALIDA EL DISPONIBLE POR MES
                $(".vigente").each(function(){
                    fechaactual  = new Date();
                    fechaactual  = fechaactual.getMonth();

                    valido = true;

                    vig =  unformatNumber($(this).text());
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mesNombre");
                    nmes = $(this).attr("nmes");
                    mes= mes.replace("_disp","");
                    dev =  unformatNumber($("#"+ mes +"_dev").text());
                    disp = vig - dev;
                    cap = ( $("#modificacion-" +  mes ).val());
                    cap = unformatNumber(cap);
                    dispos = unformatNumber($("#"+ mes +"_dispo").text());
                    n = mes.charAt(0).toUpperCase() + mes.slice(1);

                    switch (mov){

                        case "C":

                            if ( cap > 0 && cap < dev ){
                                notify("Error en el mes de "+ mesnombre + " deja sin cobertura el devengado","error");
                                console.log("modificacion-mes"+$("#modificacion-"+n));
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                valido = false;
                                return false;

                            }else{
                                $("#modificacion-"+n).css("background-color","#FFF !important");
                                valido = true;
                            }
                        break;
                        case "R":
                            if ( cap > dispos ){

                                notify("Error en el mes de "+ mesnombre + " no puede reducir mas alla del disponible por reducir","error");
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                valido = false;
                                return false;
                            }else
                                $("#modificacion-"+n).css("background-color","#FFF !important");
                            switch (mod){
                                case "I":
                                    if(nmes > fechaactual){
                                        if(cap !=0){
                                            notify("No puede  reducir en el mes de  " + mes,"error" );
                                            $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                            valido = false;
                                            return false;
                                        }else
                                            $("#modificacion-"+n).css("background-color","#FFF !important");
                                    }
                                break;
                                case "E":

                                break;
                                default:
                                    notify("error en  la modalidad de la modificacion","error");
                                    valido = false;
                                    return false;
                                break;
                            }
                        break;
                        case "A":
                                if(mod=="I" && nmes < fechaactual){
                                    if( cap != 0 ){
                                        notify("No puede ampliar en el mes de  "+ mesnombre + " ya que es un mes pasado","error");
                                        $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                        return false;
                                        valido = false;
                                        return;
                                    }else
                                        $("#modificacion-"+n).css("background-color","#FFF !important");

                                }
                        break;
                    }

                    vigente += vig;
                    calendario  += unformatNumber($("#modificacion-"+ mes ).val());

                });

                if ( vigente != calendario  && mov =="C" ){
                    notify("El calendario no coincide con el vigente","error");
                    valido = false;
                    return false;
                }
                console.log("importe total"+importe);
                console.log("calendario"+calendario+"sum cal."+$("#modificacion-"+ mes ).val());

                var total = 0;
                importe = parseFloat( unformatNumber( $("#importe").val() ).toFixed(2) );
                $(".modificacion").each(function(){
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mes");
                    mes.replace("modificacion-","");
                    total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 );

                    if ( unformatNumber($(this).val()) < 0 ){
                        notify("El importe del mes " + mesnombre + " no puede ser negativo ","error");
                        valido = false;
                        return false;
                    }
                });
                if( importe.toFixed(2) != total.toFixed(2) ){
                    notify("El importe no coincide con la suma de los meses. Favor de verificar","error");
                    valido = false;
                    return false;
                }
                return valido;
            }


        ');
        $this->mixHtml($form->ponForm());
        $this->mixScript('
            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
                $("nav").menusinpres("clickMenu");
            });

            $("#encProg").blur(function() {
                if($("#encProg").val() != ""){
                    comunica("srv/ObjetoGasto.php",{
                        metodoObjGto:"cargaPg",
                        filtro:"modificaciones",
                        ' . (($this->perfil == 'U') ? 'tcp_id:"C",' : '') . '
                        ' . (($this->perfil == 'S') ? 'tcp_id:"D",' : '') . '
                        tmd_id:"' . $enc_oop->getTmd_id() . '",
                        tipoMovimiento:"' . $mod->getTipoMovimiento() . '",
                        tfm_id:"' . $enc_oop->getTfm_id() . '",
                        ecf_id:"' . $mod->getEcf_id() . '"
                    });
                }
                return false;
            });

            $("#SitemaActivo").hide();
            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
        ');

        $this->mixScript('
            $.template( "tmpl-tbody-tabla-tablaPresupuesto",
            "<tr class=\'odd\'><td class=\'sorting_1\'>${mes}</td>' .
            '<td id=\'${mes}_disp\' nmes = \'${nmes}\' class=\'sorting_1 moneda  vigente ${mes}\' mesNombre=\'${mes}\' >${vigente}</td>' .
            '<td id=\'${mes}_dev\' class=\'sorting_1 moneda devengado ${mes}\'>${devengado}</td>' .
            '<td style=\'display:none\' id=\'${mes}_dispo\' class=\'sorting_1 moneda disponible ${mes}\'>${disponible}</td>' .
            '<td valign=\'left\' id=\'${mes}_dis\' class=\'sorting_1 moneda disponible2 ${mes}\'>${disponible2}</td>' .
            '<td class=\'sorting_1\'>' .
            '<input value=\'${modificacion}\' type=\'text\' style=\'width:100px;\' ' .
            'class=\'modificacion mes moneda validaModificacion \' onBlur = \'validaModificacion()\' ' .
            'id=\'modificacion-${mes}\' name=\'meses\' mes=\'${mes}\' data-prompt-position=\'topRight:0,-10\' >' .
            '</td></tr>");

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                Mousetrap.unbind("ctrl+enter");
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            $("#btnRegresarTablaModificacion").click(function(e){
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                e.stopPropagation();
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    ecf_id:"' . $mod->getEcf_id() . '",
                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    enc_oop:"' . $mod->getOop() . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                : '') . '
                });
                return false;
            });

            function valueMeses(){
                meses = new Array();
                $(".modificacion").each(function(index,val){
                        meses[index]= unformatNumber($(this).val());
                    });
                return meses;
            }



            $("input:radio[name=mov_id]").change(function(){
                validaModificacion($(this).val(),$("#modalidadM").val());
            });

        ');

        if ($abc == 'c') {
            $this->mixScript('bloquearClasAdmin();');
            $this->muestraPresupuesto(['presupuesto' => $pre, 'modificacion' => $modClave]);
        }
    }

    private function addModPrevision()
    { //Metodo que agrega previsiones para el caso de la compensacion de folios en la fase
        //de revision y agrega nuevos movimientos al folio de origen A,B,C,D.
        /* se instancia el folio de modificacion para tener la informacion disponible */
        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $modClave = new DetalleModificacion($this->prm);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);

        $abc = 'a';
        $sql = '';
        if (
            $modClave->getOmo_id() != '' && $modClave->getMod_gpo_id() != '' && $modClave->getMod_ur_id()
            != '' &&
            $modClave->getMod_id() != '' && $modClave->getGpo_id() != '' && $modClave->getUr_id()
            != '' && $modClave->getUe_id() != '' && $modClave->getEpg_id() != ''
            && $modClave->getPg_id() != '' && $modClave->getPe_id() != '' && $modClave->getEcf_id()
            != '' && $modClave->getTcp_id() != ''
        ) {

            $abc = 'c';

            $sql = 'SELECT ' .
                'gpo_id, ur_id, ue_id, epg_id,pg_id, pe_id,' .
                "ecf_id, tcp_id, pre_clave,pre_claveCompleta, pe_nombre ,pre_exenta
                FROM presupuesto p
                JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
                JOIN cat_PartidaEspecifica pe USING (pg_id,pe_id)
                WHERE
                    gpo_id = {$modClave->getGpo_id()} AND ur_id = {$modClave->getUr_id()} " .
                " AND ue_id = {$modClave->getUe_id()} AND epg_id = {$modClave->getEpg_id()} " .
                " AND pg_id = {$modClave->getPg_id()} AND pe_id = '{$modClave->getPe_id()}' " .
                " AND ecf_id = {$modClave->getEcf_id()} AND tcp_id = '{$modClave->getTcp_id()}' ";
        }

        $btnRegresarTablaModificacion = new stdClass();
        $btnRegresarTablaModificacion->id = 'btnRegresarTablaModificacion';
        $btnRegresarTablaModificacion->icon = 'circle-left';
        $btnRegresarTablaModificacion->text = 'Regresar';
        $btnRegresarTablaModificacion->value = '&nbsp;&nbsp;Regresar';

        $folio = new stdClass();
        $folio->id = 'folio';
        $folio->enDiv = true;
        $folio->value = $mod->getMod_id();
        $folio->required = true;
        $folio->disabled = true;

        $modalidad = new stdClass();
        $modalidad->id = 'modalidadM';
        $modalidad->enDiv = true;
        $modalidad->value = ($enc_oop->getMmd_id() == 'E' ? 'EXTERNAS' : 'INTERNAS');
        $modalidad->required = true;
        $modalidad->disabled = true;

        $tipoModificacion = new stdClass();
        $tipoModificacion->id = 'tmd_id';
        $tipoModificacion->enDiv = true;
        $tipoModificacion->value = $tipoMod->getNombre();
        $tipoModificacion->required = true;
        $tipoModificacion->disabled = true;

        $objMov = new stdClass();
        $objMov->omo_id = $mod->getOmo_id();
        $objMov->mod_gpo_id = $mod->getMod_gpo_id();
        $objMov->mod_ur_id = $mod->getMod_ur_id();
        $objMov->mod_id = $mod->getMod_id();
        $objMov->mov_id = $modClave->getMov_id();
        $objMov->tcp_id = (isset($this->prm->prev) && $this->prm->prev == true ? 'P'
            : $modClave->getTcp_id());
        if (isset($this->prm->nivelRevision) && $this->prm->nivelRevision == 'alta') {
            $objMov->altaSefin = true;
        }

        //Parametro para identificar cuando sefin agrega un movimiento y mostrar ampliacion o reduccion segun sea el caso.

        /* Input importe del movimiento */
        $importeaDistribuir = new stdClass();
        $importeaDistribuir->id = 'importe';
        $importeaDistribuir->label = 'Importe a distribuir';
        $importeaDistribuir->formato = 'moneda';
        $importeaDistribuir->required = true;
        $importeaDistribuir->class = 'validaTotMovimiento';
        $importeaDistribuir->value = $modClave->getImporte();
        if ($enc_oop->getTmd_id() == 'C') {
            $importeaDistribuir->disabled = true;
        }

        /* Input importe por dist del movimiento */
        $importePorDistribuir = new stdClass();
        $importePorDistribuir->id = 'importeb';
        $importePorDistribuir->label = 'Saldo por distribuir';
        $importePorDistribuir->formato = 'moneda';
        $importePorDistribuir->disabled = 'disabled';

        $infPre = new stdClass();
        if ($modClave->getTcp_id() == 'P') {
            /* cuando se edita una previsión, muestra la información del detalle */
            $infPre->gpo_id = $modClave->getGpo_id();
            $infPre->ur_id = $modClave->getUr_id();
        } else {
            $infPre->gpo_id = $mod->getMod_gpo_id();
            $infPre->ur_id = $mod->getMod_ur_id();
        }
        if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {
            $infPre->ue_id = $mod->getMod_ue_id();
        } else {
            $infPre->ue_id = $modClave->getUe_id();
        }
        $infPre->epg_id = $modClave->getEpg_id();
        $infPre->pg_id = $modClave->getPg_id();
        $infPre->pe_id = $modClave->getPe_id();
        $infPre->ecf_id = $modClave->getEcf_id();
        $infPre->tcp_id = $modClave->getTcp_id();

        $pre = new Presupuesto($infPre);

        $cboPrev = new stdClass();
        $cboPrev->id = 'cboPre';
        $cboPrev->sql = $sql;
        $cboPrev->soloText = true;
        $cboPrev->comoAtr = 'pre_clave,pre_claveCompleta,tcp_id,gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,pre_exenta';
        $cboPrev->camposVal = 'pre_clave';
        $cboPrev->camposText = 'pre_claveCompleta,tcp_id,pe_nombre';
        $cboPrev->required = true;

        if ($abc != 'c') {
            $cboPrev->change = '
            comunica("' . $this->pantalla . '",{
                metodo:"muestraPresupuesto",
                prevision:true,
                gpo_id:$("#cboPre :selected").attr("gpo_id"),
                ur_id:$("#cboPre :selected").attr("ur_id"),
                ue_id:$("#cboPre :selected").attr("ue_id"),
                epg_id:$("#cboPre :selected").attr("epg_id"),
                pg_id:$("#cboPre :selected").attr("pg_id"),
                pe_id:$("#cboPre :selected").attr("pe_id"),
                ecf_id:$("#cboPre :selected").attr("ecf_id"),
                tcp_id:$("#cboPre :selected").attr("tcp_id"),
                tmd_id:"' . $enc_oop->getTmd_id() . '",
                tipoMovimiento:"' . $mod->getTipoMovimiento() . '"
            });';
        }

        $form = new stdClass();
        $form->id = 'ModificacionesSefin';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcModificacion';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->omo_id = $mod->getOmo_id();
        $form->datosExtra->mod_gpo_id = $mod->getMod_gpo_id();
        $form->datosExtra->mod_ur_id = $mod->getMod_ur_id();
        $form->datosExtra->mod_id = $mod->getMod_id();
        $form->datosExtra->meses = '"{ valueMeses();}"';
        $form->datosExtra->importe = '"{ $("#importe").val();}"';
        $form->datosExtra->gpo_id = '"{( $("#cboPre :selected").attr("gpo_id") !="" ) ? $("#cboPre :selected").attr("gpo_id") : ""}"';
        $form->datosExtra->ur_id = '"{( $("#cboPre :selected").attr("ur_id") !="" ) ? $("#cboPre :selected").attr("ur_id"):""}"';
        $form->datosExtra->ue_id = '"{( $("#cboPre :selected").attr("ue_id") !="" ) ? $("#cboPre :selected").attr("ue_id"):""}"';
        $form->datosExtra->epg_id = '"{( $("#cboPre :selected").attr("epg_id") !="") ? $("#cboPre :selected").attr("epg_id"):""}"';
        $form->datosExtra->pg_id = '"{( $("#cboPre :selected").attr("pg_id") !="" ) ? $("#cboPre :selected").attr("pg_id"):""}"';
        $form->datosExtra->pe_id = '"{( $("#cboPre :selected").attr("pe_id") !=""  ) ? $("#cboPre :selected").attr("pe_id"):""}"';
        $form->datosExtra->ecf_id = '"{( $("#cboPre :selected").attr("ecf_id")!=""  ) ? $("#cboPre :selected").attr("ecf_id"):""}"';
        $form->datosExtra->tcp_id = '"{( $("#cboPre :selected").attr("tcp_id") !="" ) ? $("#cboPre :selected").attr("tcp_id"):""}"';
        $form->datosExtra->tipoMovimiento = $mod->getTipoMovimiento();
        if (isset($this->prm->nivelRevision)) {
            $form->datosExtra->nivelRevision = $this->prm->nivelRevision;
        }

        $form = new form($form);

        $html = '
                <form id="ModificacionesSefin">
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Clave Presupuestal</legend>
                        <div class="row col-md-12 col-xs-12">
                            ' . $this->ponCombo($cboPrev) . '
                        </div>
                    </fieldset>
                </div>
                <div class="row col-md-12 col-xs-12">
                    <div id = "grafica" name = "grafica" style="position:inherid; height:200px;"/>
                </div><br/><br/>
                <div class="row">
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Folio</legend>
                        ' . $this->ponInput($folio) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Modalidad</legend>
                        ' . $this->ponInput($modalidad) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo de adecuación</legend>
                        ' . $this->ponInput($tipoModificacion) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        <legend>Tipo de movimiento</legend>
                        ' . $this->ponRadio(TipoMovimiento::objMovimiento($objMov)) . '
                    </fieldset>
                </div>
                <div class="row"><!--calendario-->
                    <div class="panel panel-default">
                        <!-- Default panel contents -->
                        <div class="panel-heading"><h2>Calendario presupuestado</h2></div>
                            <div class="panel-body">
                               <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        ' . $this->ponInput($importeaDistribuir) . '
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        ' . $this->ponInput($importePorDistribuir) . '
                                    </fieldset>
                                </div>
                            </div>
                        <!-- Table -->
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Importe Actual</th>
                                    <th>Importe Devengado</th>
                                    <th>Importe Disponible</th>
                                    <th>Distribución</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-tabla-tablaPresupuesto"></tbody>
                        </table>
                    </div>
                </div><!--fin calendario-->
                </form>';
        $form->mixHtml($html);

        $this->mixHtml($form->ponForm());
        $this->agregaTools($this->ponBoton($btnRegresarTablaModificacion));
        $this->mixScript('
            $("#btnRegresarTablaModificacion").removeClass("btn btn-default");
            $("#btnRegresarTablaModificacion").addClass("btn btn-outline-secondary me-1");
        ');

        $this->mixScript('

            function validaModificacion(mov,mod){
                if(mov != "" && mov != undefined  && mod !="" && mod != undefined ){
                    if(mod =="INTERNAS")
                        mod = "I";
                    else if(mod== "EXTERNAS")
                        mod = "E";
                }else{
                    if ( $("#modalidadM").val() =="INTERNAS"){
                        mod = "I";
                    }else{
                        mod = "E";
                    }
                    $("input:radio[name=mov_id]").each(function(){
                        if( $(this).is(":checked")){
                            mov = $(this).val();
                        }
                    });
                }

                vigente = 0;
                calendario = 0;


                //VALIDA EL DISPONIBLE POR MES
                $(".vigente").each(function(){
                    fechaactual  = new Date();
                    fechaactual  = fechaactual.getMonth();

                    valido = true;

                    vig =  unformatNumber($(this).text());
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mesNombre");
                    nmes = $(this).attr("nmes");
                    mes= mes.replace("_disp","");
                    dev =  unformatNumber($("#"+ mes +"_dev").text());
                    disp = vig - dev;
                    cap = ( $("#modificacion-" +  mes ).val());
                    cap = unformatNumber(cap);
                    dispos = unformatNumber($("#"+ mes +"_dispo").text());
                    n = mes.charAt(0).toUpperCase() + mes.slice(1);

                    switch (mov){

                        case "C":

                            if ( cap > 0 && cap < dev ){
                                notify("Error en el mes de "+ mesnombre + " deja sin cobertura el devengado","error");
                                console.log("modificacion-mes"+$("#modificacion-"+n));
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                valido = false;
                                return false;

                            }else{
                                $("#modificacion-"+n).css("background-color","#FFF !important");
                                valido = true;
                            }
                        break;
                        case "R":
                            if ( cap > dispos ){

                                notify("Error en el mes de "+ mesnombre + " no puede reducir mas alla del disponible por reducir","error");
                                $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                valido = false;
                                return false;
                            }else
                                $("#modificacion-"+n).css("background-color","#FFF !important");
                            switch (mod){
                                case "I":
                                    if(nmes > fechaactual){
                                        if(cap !=0){
                                            notify("No puede  reducir en el mes de  " + mes,"error" );
                                            $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                            valido = false;
                                            return false;
                                        }else
                                            $("#modificacion-"+n).css("background-color","#FFF !important");
                                    }
                                break;
                                case "E":

                                break;
                                default:
                                    notify("error en  la modalidad de la modificacion","error");
                                    valido = false;
                                    return false;
                                break;
                            }
                        break;
                        case "A":
                            if(mod=="I" && nmes < fechaactual){
                                if( cap != 0 ){
                                    notify("No puede ampliar en el mes de  "+ mesnombre + " ya que es un mes pasado","error");
                                    $("#modificacion-"+n).css("background-color","rgba(200,0,0,.5) !important");
                                    return false;
                                    valido = false;
                                    return;
                                }else
                                    $("#modificacion-"+n).css("background-color","#FFF !important");

                            }
                        break;
                    }

                    vigente += vig;
                    calendario  += unformatNumber($("#modificacion-"+ mes ).val());

                });

                if ( vigente != calendario  && mov =="C" ){
                    notify("El calendario no coincide con el vigente","error");
                    valido = false;
                    return false;
                }
                console.log("importe total"+importe);
                console.log("calendario"+calendario+"sum cal."+$("#modificacion-"+ mes ).val());

                var total = 0;
                importe = parseFloat( unformatNumber( $("#importe").val() ).toFixed(2) );
                $(".modificacion").each(function(){
                    mes = $(this).attr("id");
                    mesnombre = $(this).attr("mes");
                    mes.replace("modificacion-","");
                    total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 );

                    if ( unformatNumber($(this).val()) < 0 ){
                        notify("El importe del mes " + mesnombre + " no puede ser negativo ","error");
                        valido = false;
                        return false;
                    }
                });
                if( importe.toFixed(2) != total.toFixed(2) ){
                    notify("El importe no coincide con la suma de los meses. Favor de verificar","error");
                    valido = false;
                    return false;
                }
                return valido;
            }

        ');

        $this->mixScript('
            $.template( "tmpl-tbody-tabla-tablaPresupuesto",
            "<tr class=\'odd\'><td class=\'sorting_1\'>${mes}</td>' .
            '<td id=\'${mes}_disp\' nmes = \'${nmes}\' class=\'sorting_1 moneda  vigente ${mes}\' mesNombre=\'${mes}\' >${vigente}</td>' .
            '<td id=\'${mes}_dev\' class=\'sorting_1 moneda devengado ${mes}\'>${devengado}</td>' .
            '<td style=\'display:none\' id=\'${mes}_dispo\' class=\'sorting_1 moneda disponible ${mes}\'>${disponible}</td>' .
            '<td valign=\'left\' id=\'${mes}_dis\' class=\'sorting_1 moneda disponible2 ${mes}\'>${disponible2}</td>' .
            '<td class=\'sorting_1\'>' .
            '<input value=\'${modificacion}\' type=\'text\' style=\'width:100px;\' ' .
            'class=\'modificacion mes moneda validaModificacion \' onBlur = \'validaModificacion()\' ' .
            'id=\'modificacion-${mes}\' name=\'meses\' mes=\'${mes}\' data-prompt-position=\'topRight:0,-10\' >' .
            '</td></tr>");

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnRegresarTablaModificacion").click(); e.stopPropagation(); });

            $("#btnRegresarTablaModificacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaModificaciones",
                    ecf_id:"' . $mod->getEcf_id() . '",
                    mod_gpo_id:"' . $mod->getMod_gpo_id() . '",
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    enc_oop:"' . $mod->getOop() . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
                : '') . '
                });
                return false;
            });

            function valueMeses(){
                meses = new Array();
                $(".modificacion").each(function(index,val){
                        meses[index]= unformatNumber($(this).val());
                    });
                return meses;
            }

            $("input:radio[name=mov_id]").change(function(){
                validaModificacion($(this).val(),$("#modalidadM").val());
            });

        ');

        if ($abc == 'c') {
            $this->muestraPresupuesto(['presupuesto' => $pre, 'modificacion' => $modClave]);
        }
    }

    private function muestraPresupuesto()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        if (is_null($prm)) {
            $prm = $this->prm;
        }

        $meses = [
            'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep',
            'oct', 'nov', 'dic',
        ];
        $mesLargo = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];
        $tablaPresupuesto = [];
        $ban = true;
        $script = $grafica = '';
        $modificacion = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        //VERIFICAMOS SI ES UNA PREVISIÓN
        $prevision = isset($prm->prevision) && $prm->prevision != '' ? true : false;
        //VERIFICAMOS SI ES UNA INSTANCIA DE PRESUPUESTO
        $instanciaPresupuesto = isset($prm->presupuesto) ? $prm->presupuesto : '';
        $instanciaPresupuesto = $instanciaPresupuesto instanceof Presupuesto;

        $instanciaDetalleMod = isset($prm->modificacion) && $prm->modificacion instanceof DetalleModificacion;

        $noEsAmpliacion = isset($prm->tmd_id) && !(($prm->tmd_id == 'B' && isset($prm->tipoMovimiento)
            && $prm->tipoMovimiento == 'A') || $prm->tmd_id == 'D');

        $pre = new stdClass();
        if (!$instanciaPresupuesto) {
            if ($prevision) {
                //PREVISION
                $pre->gpo_id = $prm->gpo_id;
                $pre->ur_id = $prm->ur_id;
                $pre->ue_id = $prm->ue_id;
                $pre->epg_id = $prm->epg_id;
                $pre->pg_id = $prm->pg_id;
                $pre->pe_id = $prm->pe_id;
                $pre->ecf_id = $prm->ecf_id;
                $pre->tcp_id = $prm->tcp_id;
            } else {
                //CLAVE NORMAL
                $pre->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
                $pre->ur_id = $_SESSION[_S]['clasAdm-ur'];
                $pre->ue_id = $_SESSION[_S]['clasAdm-ue'];
                $pre->epg_id = $_SESSION[_S]['clasAdm-epg'];
                $pre->pg_id = $prm->pg_id;
                $pre->pe_id = $prm->pe_id;
                $pre->ecf_id = $prm->ecf_id;
            }
            //SI NO ES UNA AMPLIACION
            if ($noEsAmpliacion) {
                $tipoCompra = isset($prm->tcp_id) ? $prm->tcp_id : '';
                if ($tipoCompra != '') {
                    $pre->tcp_id = $prm->tcp_id;
                }
            }
            //INSTANCIAMOS PRESUPUESTO
            $prm->presupuesto = new Presupuesto($pre);
            //SI ES UNA CALENDARIZACIÓN
            if ($prm->tmd_id == 'C') {
                $script .= '
                    $("#importe").val( formatNumber("' . $prm->presupuesto->getVigente() . '") );
                ';
            }
        }
        //ES UNA INSTANCIA DE PRESUPUESTO
        $instanciaPresupuesto = $prm->presupuesto instanceof Presupuesto;
        if ($instanciaPresupuesto) {

            $script .= $this->vista->vwGrafica1($prm->presupuesto, $meses);
            //PARA MOSTRAR LA COLUMNA DE DISTRIBUCIÓN, A PARTIR DE LO CAPTURADO O SI ES UN CALENDARIO MOSTRAR EL CALENDARIO DE PRESUPUESTO
            if ($instanciaDetalleMod) {
                $modificacion = $prm->modificacion->getMes()->getArrayMeses();
            } elseif (isset($prm->tmd_id) && $prm->tmd_id == 'C') {
                $modificacion = $prm->presupuesto->getMesVigente()->getArrayMeses();
            }

            $pre->gpo_id = $prm->presupuesto->getGpo_id();
            $pre->ur_id = $prm->presupuesto->getUr_id();
            $pre->ue_id = $prm->presupuesto->getUe_id();
            $pre->epg_id = $prm->presupuesto->getEpg_id();
            $pre->pg_id = $prm->presupuesto->getPg_id();
            $pre->pe_id = $prm->presupuesto->getPe_id();
            $pre->ecf_id = $prm->presupuesto->getEcf_id();
            if ($prm->presupuesto->getTcp_id() != '') {
                $pre->tcp_id = $prm->presupuesto->getTcp_id();
            }
            //ENVIAMOS LOS PARAMETROS A LA FUNCIÓN PARA OBTENER LOS VALORES DE LA COLUMNA DEL CALENDARIO PRESUPUESTARIO
            $presupuesto = $this->model->getPresupuestoModificaciones(
                $pre,
                '',
                $modificacion
            );
            //CREAMOS LA TABLA DE PRESUPUESTO CON LOS VALORES DEVUELTOS DE LA FUNCION
            foreach ($presupuesto as $key => $mesCorto) {

                $mes = [
                    'mes'          => $mesCorto->mes,
                    'vigente'      => $mesCorto->vigente,
                    'devengado'    => $mesCorto->devengado,
                    'disponible'   => $mesCorto->disp,
                    'modificacion' => $mesCorto->modificacion,
                    'nmes'         => $mesCorto->nmes,
                ];
                array_push($tablaPresupuesto, $mes);
            }
        } else {
            //NO SE MOSTRARÁ NADA
            $script .= '
                $("#tbody-tabla-tablaPresupuesto").html("");
                $("#importe").val(0.00);
                $("#importeb").val(0.00);
            ';
        }

        $script .= '
            function paradistribuir(){
                var importe = 0;
                var total = 0;
                importe = parseFloat( unformatNumber($("#importe").val() ).toFixed(2) );
                $(".mes").each(function(){
                    total = total + parseFloat( unformatNumber( $(this).val() ).toFixed(2) * 1 );
                });
                $("#importeb").val(formatNumber((importe-total).toFixed(2)));

            };
            $("#tbody-tabla-tablaPresupuesto").html("");
        ';
        $script .= ($instanciaPresupuesto ? '$.tmpl( "tmpl-tbody-tabla-tablaPresupuesto", ' . json_encode($tablaPresupuesto) . ' ).appendTo("#tbody-tabla-tablaPresupuesto");'
            : '');
        $script .= '
            $(".moneda").each(function(){
                $(this).text( formatNumber( $(this).text(),"$ "));
            });

            $(".modificacion").each(function(){
                $(this).val(formatNumber($(this).val(),"$ "));
            });

            $(".modificacion").blur(paradistribuir);

            paradistribuir();

            $(".modificacion").blur(function(){

            $(this).val(formatNumber($(this).val()));
                paradistribuir();
            });

            $("#importe").blur(function(){
                $("#importe").val(formatNumber($(this).val()));
                paradistribuir();
            });
        ';

        $this->mixScript($script);
    }

    private function totalFolio()
    {

        //Obteniendo el total del folio
        if (
            isset($this->prm->omo_id) && isset($this->prm->mod_gpo_id) && isset($this->prm->mod_ur_id)
            && isset($this->prm->mod_id)
        ) {
            $mod = new MaestroModificacion($this->prm);
            $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        }

        $sqlDetalle = '
            select
                dmo_importe, mov_id, mov_nombre
                from scp_DetalleModificacion d
                join cat_PartidaEspecifica using(pe_id, pg_id)
                join cat_TipoMovimiento using(mov_id)
                where omo_id = :omo_id and mod_gpo_id = :mod_gpo_id and mod_ur_id = :mod_ur_id and mod_id = :mod_id';

        $parametros = [
            'omo_id'     => $mod->getOmo_id(),
            'mod_gpo_id' => $mod->getMod_gpo_id(),
            'mod_ur_id'  => $mod->getMod_ur_id(),
            'mod_id'     => $mod->getMod_id(),
        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sqlDetalle);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $detalleFolio = '';
        $ampliaciones = $reducciones = $calendario = 0.00;
        while ($row = $conexion->extraer()) {
            switch ($row['mov_id']) {
                case 'A':
                    $ampliaciones += $row['dmo_importe'];
                    break;
                case 'R':
                    $reducciones += $row['dmo_importe'];
                    break;
                case 'C':
                    $calendario += $row['dmo_importe'];
                    break;
            }
        }
        switch ($enc_oop->getTmd_id()) {
            case 'A': //traspaso intrainstitucional
            case 'D': //Ampliacion liq
            case 'E': //Reduccion liq
                $detalleFolio .= '
                        <div class="row">
                            <fieldset class="col-md-4 col-xs-4"></fieldset>
                            <fieldset class="col-md-2 col-xs-2">AMPLIACIONES:&nbsp;<strong>' . number_format(
                    $ampliaciones,
                    2,
                    '.',
                    ','
                ) . '</strong></fieldset>
                            <fieldset class="col-md-2 col-xs-2">REDUCCIONES:&nbsp;<strong>' . number_format(
                    $reducciones,
                    2,
                    '.',
                    ','
                ) . '</strong></fieldset>
                            <fieldset class="col-md-4 col-xs-4">SALDO POR DISTRIBUIR:&nbsp;<strong>' . number_format(Math::redondear($ampliaciones
                     - $reducciones, 2), 2, '.', ',') . '</strong></fieldset>
                        </div>';
                break;
            case 'B': //Traspaso interinstitucional
                if ($this->nivel <= 2) {
                    if ($mod->getTipoMovimiento() == 'A') {
                        $detalleFolio .= '
                                <div class="row">
                                    <fieldset class="col-md-4 col-xs-4"></fieldset>
                                    <fieldset class="col-md-2 col-xs-2"><strong></strong></fieldset>
                                    <fieldset class="col-md-2 col-xs-2"><strong></strong></fieldset>
                                    <fieldset class="col-md-4 col-xs-4">TOTAL AMPLIACIONES:&nbsp;<strong>' . number_format(
                            $ampliaciones,
                            2,
                            '.',
                            ','
                        ) . '</strong></fieldset>
                                </div>';
                    }
                    if ($mod->getTipoMovimiento() == 'R') {
                        $detalleFolio .= '
                                <div class="row">
                                    <fieldset class="col-md-4 col-xs-4"></fieldset>
                                    <fieldset class="col-md-4 col-xs-4">TOTAL REDUCCIONES:&nbsp;<strong>' . number_format(
                            $reducciones,
                            2,
                            '.',
                            ','
                        ) . '</strong></fieldset>
                                    <fieldset class="col-md-2 col-xs-2"><strong></strong></fieldset>
                                </div>';
                    }
                } else {
                    $detalleFolio .= '
                            <div class="row">
                                <fieldset class="col-md-4 col-xs-4"></fieldset>
                                <fieldset class="col-md-2 col-xs-2">AMPLIACIONES:&nbsp;<strong>' . number_format(
                        $ampliaciones,
                        2,
                        '.',
                        ','
                    ) . '</strong></fieldset>
                                <fieldset class="col-md-2 col-xs-2">REDUCCIONES:&nbsp;<strong>' . number_format(
                        $reducciones,
                        2,
                        '.',
                        ','
                    ) . '</strong></fieldset>
                                <fieldset class="col-md-4 col-xs-4">SALDO POR DISTRIBUIR:&nbsp;<strong>' . number_format(Math::redondear($ampliaciones
                         - $reducciones, 2), 2, '.', ',') . '</strong></fieldset>
                            </div>';
                }
                break;
            default: //calendario
                $detalleFolio .= '
                    <div class="row">
                        <fieldset class="col-md-4 col-xs-4">
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-4">
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-4">
                            Importe Total: &nbsp;<strong>' . number_format(Math::redondear(
                    $calendario,
                    2
                ), 2, '.', ',') . '</strong>
                        </fieldset>
                    </div>';
                break;
        }
        $detalleFolio .= '&nbsp&nbsp&nbsp';
        return $detalleFolio;
        //////////fin total del folio/////////
    }
    /* METODO QUE DETERMINA SI ES MODO LECTURA */

    private function lectura($mod, $esUR)
    {
        $estadosUR = [1, 3];
        $estadosSEFIN = [6, 8, 11, 17];
        $lectura = false;
        if ($mod->getMod_id() != '') {
            if ($esUR && !in_array($mod->getEmo_id(), $estadosUR)) {
                $lectura = true;
            } elseif (!$esUR && !in_array($mod->getEmo_id(), $estadosSEFIN)) {
                $lectura = true;
            }
        } else {
            $lectura = false;
        }

        return $lectura;
    }

    private function mostrarArchivos()
    {
        $this->setContenedor('archivos');
        if (
            isset($this->prm->omo_id) && isset($this->prm->mod_gpo_id) &&
            isset($this->prm->mod_ur_id) && isset($this->prm->mod_id)
        ) {
            $scp_Maestro = new MaestroModificacion();
            $scp_Maestro->setOmo_id($this->prm->omo_id);
            $scp_Maestro->setMod_gpo_id($this->prm->mod_gpo_id);
            $scp_Maestro->setMod_ur_id($this->prm->mod_ur_id);
            $scp_Maestro->setMod_id($this->prm->mod_id);
            $scp_Maestro->select();

            $and1 = 'AND bit_observacion = "SE AGREGA OFICIO DE AUTORIZACION DESDE PANTALLA S_1_1"';
            $carpeta1 = 'oficiosAutorizacion';
            $fichero1 = 'I-' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '-' . $scp_Maestro->getMod_id() . '.pdf';

            $and2 = 'AND bit_observacion = "SE AGREGA REPORTE INTERNO DESDE PANTALLA S_1_1"';
            $carpeta2 = 'rep_ModificacionesInternas';
            $fichero2 = 'reporte_I-' . $scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id() . '-' . $scp_Maestro->getMod_id() . '.pdf';

            $this->conectar();
            $this->setSql('
                (Select usr_fecha
                FROM ' . BD_BITACORA . '.bit_MaestroModificacion
                WHERE omo_id= :omo_id
                AND mod_gpo_id = :mod_gpo_id
                AND mod_ur_id = :mod_ur_id
                AND mod_id = :mod_id
                ' . $and1 . '
                ORDER BY usr_fecha DESC
                LIMIT 1)

            UNION ALL

                (Select usr_fecha
                FROM ' . BD_BITACORA . '.bit_MaestroModificacion
                WHERE omo_id= :omo_id
                AND mod_gpo_id = :mod_gpo_id
                AND mod_ur_id = :mod_ur_id
                AND mod_id = :mod_id
                ' . $and2 . '
                ORDER BY usr_fecha DESC
                LIMIT 1
                );');

            $parametros = [
                'omo_id'     => $this->prm->omo_id,
                'mod_gpo_id' => $this->prm->mod_gpo_id,
                'mod_ur_id'  => $this->prm->mod_ur_id,
                'mod_id'     => $this->prm->mod_id,
            ];
            $this->consultar($parametros);
            $this->desconectar();
            $bit = $this->extraer();
            $bit2 = $this->extraer();

            $mes = substr($bit['usr_fecha'], 5, 2);

            $rutatemp = __DIR__ . '/../../tmps/';

            $exists = is_dir($rutatemp);
            if (!$exists) {
                mkdir($rutatemp, 0777, true);
            }

            $r = new RutaArchivoSefip();
            $r->setBase('SCP');
            $r->setPath('adecuaciones/' . $mes . '/' . $carpeta1);
            $r->setAnioFichero(APP_EJERCICIO);
            $r->setUrFichero($scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id());
            $ruta = $r->getRutaMod();
            $ruta .= $fichero1;

            if (is_file($ruta)) {
                copy($ruta, $rutatemp . $fichero1);
            } else {
                $this->mixScript('notify("No se encontró el archivo ' . $carpeta1 . '","error");');
                return false;
            }

            $r2 = new RutaArchivoSefip();
            $r2->setBase('SCP');
            $r2->setPath('adecuaciones/' . $mes . '/' . $carpeta2);
            $r2->setAnioFichero(APP_EJERCICIO);
            $r2->setUrFichero($scp_Maestro->getMod_gpo_id() . $scp_Maestro->getMod_ur_id());
            $ruta2 = $r2->getRutaMod();
            $ruta2 .= $fichero2;

            if (is_file($ruta2)) {
                copy($ruta2, $rutatemp . $fichero2);
            } else {
                $this->mixScript('notify("No se encontró el archivo ' . $carpeta2 . '","error");');
                return false;
            }

            $verArchivo = '
                <div id="myModal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                    <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Documentos digitales cargados</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-bs-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <fieldset class="col-md-12 col-xs-12">
                                <legend>Oficio de autorización</legend>
                                <a id="decretoArchivo" href="lib/file_download.php?r=' . $fichero1 . '" target="_blank">' . $scp_Maestro->getOficioArchivo() . '</a>
                            </fieldset>
                            <fieldset class="col-md-12 col-xs-12">
                                <legend>Reporte de adecuación interna</legend>
                                <a id="repArchivo" href="lib/file_download.php?r=' . $fichero2 . '" target="_blank">' . $scp_Maestro->getReporteInterno() . '</a>
                            </fieldset>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-outline-success" data-bs-dismiss="modal" id="aceptar" form="">Aceptar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
                </div>';
            $this->mixHtml($verArchivo);

            $this->mixScript('
                    $("#myModal").modal("show");
                    $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
                    $("#aceptar").click(function(){ $("#myModal").modal().remove(); });
                ');
        }
    }

    public function modificarMetas()
    {

        $lectura = (!isset($this->prm->lectura) ? false : true);

        $omo_id = $this->prm->omo_id;
        $mod_gpo_id = $this->prm->mod_gpo_id;
        $mod_ur_id = $this->prm->mod_ur_id;
        $mod_id = $this->prm->mod_id;

        $maestro = new MaestroModificacion($this->prm);

        if (!$lectura) {

            $regresar = new stdClass();
            $regresar->id = 'regresar';
            $regresar->value = 'Cancelar';
            $regresar->icon = 'hand-left';
        } else {
            $this->setContenedor('metas');
        }
        $this->conectar();
        $this->setSql('
            SELECT epg_id,prg_id,spr_id,py_id,oa_id,oa_nombre FROM scp_MaestroModificacion
            INNER JOIN scp_DetalleModificacion USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
            INNER JOIN enc_Programatico  USING(gpo_id,ur_id,epg_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
            WHERE omo_id = :omo_id AND mod_gpo_id = :mod_gpo_id AND mod_ur_id = :mod_ur_id AND mod_id = :mod_id
            GROUP BY epg_id;
        ');
        $parametros = [
            'omo_id'     => $omo_id,
            'mod_gpo_id' => $mod_gpo_id ,
            'mod_ur_id'  => $mod_ur_id,
            'mod_id'     => $mod_id,
        ];
        $this->consultar($parametros);
        $this->desconectar();

        $htm = '';
        $htm .= '<table  class="table table table-bordered table-hover">';
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
                        <button type="button" id="verMetas" icon="eye-open" title="Opción para ver metas de la actividad" class="verMetas btn btn-outline-secondary" epg_id=' . $row['epg_id'] . '>
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
            $(".verMetas").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"listaMetas",
                    omo_id:"' . $omo_id . '",
                    mod_gpo_id:"' . $mod_gpo_id . '",
                    mod_ur_id:"' . $mod_ur_id . '",
                    mod_id:' . $mod_id . ',
                    epg_id:$(this).attr("epg_id"),
                    lectura:true
                });
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaFolios",
                    ecf_id:"' . $maestro->getEcf_id() . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                });
            });
        ');
    }

    //METODO QUE ENLISTARA LAS METAS POR ACTIVIDAD PROGRAMÁTICA
    private function listaMetas()
    {
        $cambioClase = true;
        $lectura = $this->prm->lectura;
        $this->setContenedor('oa' . $this->prm->epg_id);
        $m = new MaestroModificacion($this->prm);
        $enc = new OrigenOperacion(['enc_oop' => $m->getOop()]);

        $sql = '
            SELECT emo_id
            FROM scp_MaestroModificacion
            WHERE omo_id = :omo_id
            AND mod_gpo_id = :gpo_id AND mod_ur_id = :ur_id
            AND mod_id = :mod_id
        ';

        $params = [
            ':gpo_id' => $this->prm->mod_gpo_id,
            ':ur_id'  => $this->prm->mod_ur_id,
            ':omo_id' => $this->prm->omo_id,
            ':mod_id' => $this->prm->mod_id,
        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();
        $datos = $conexion->extraer();

        if ($datos['emo_id'] == 14) {
            $filtro = BD_CTRL . '.aux_HistoricoMeta';
            $variableVD = 'macm_valorDeseado';
        } else {
            $filtro = BD_TEMPORAL . '.tem_ModificacionMetas';
            $variableVD = 'macm_valorDeseado';
        }

        $consulta = ' INNER JOIN ';
        //VALIDA SI ES LECTURA
        if (!$lectura) {
            //BOTON CANCELAR, CANCELA LA MODIFICACION DE METAS
            $regresar = new stdClass();
            $regresar->id = 'regresar';
            $regresar->value = 'Cancelar';
            $regresar->icon = 'hand-left';
            //BOTON FINALIZA LA MODIFICACION DE METAS
            $endMetas = new stdClass();
            $endMetas->id = 'endMetas';
            $endMetas->value = 'Finalizar modificación';
            $endMetas->icon = 'ok';
            //AGREGA LOS BOTONES A LA BARRA DE HERRAMIENTAS
            $this->agregaTools($this->ponBoton($regresar) . ' ' . $this->ponBoton($endMetas));
        }
        //CONSTRUYE LA CONSULTA PARA LAS METAS DE LA ACTIVIDAD
        $sql = '
            SELECT pii_id,pdi_id,ind_id,ind_definicion,i.ind_nombre, pdi_mir,
                pdi_nombre,ctInd.umv_id,ctInd.umv_nombre, umi_nombre, tiv_nombre,
                cs.pdf_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,pii_id AS pii, epg_id AS epg,
                mac_descripcion,epg_id,mac_original,mac_modificado vigente, macm_id
            FROM enc_Programatico enc
            JOIN cat_MetaActividad ma USING(epg_id)
            ' . $consulta . '  (
                SELECT omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id,
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
                FROM ' . $filtro . '
                WHERE  epg_id = :epg_id
                AND omo_id = :omo_id 
                AND mod_gpo_id = :mod_gpo_id 
                AND mod_ur_id = :mod_ur_id
                AND mod_id = :mod_id
                GROUP BY omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id
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
        $parametros = [
            'epg_id'     => $this->prm->epg_id,
            'omo_id'     => $this->prm->omo_id,
            'mod_gpo_id' => $this->prm->mod_gpo_id,
            'mod_ur_id'  => $this->prm->mod_ur_id,
            'mod_id'     => $this->prm->mod_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
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
            if($("#endMetas").is(":disabled"))
                $("#endMetas").attr("disabled","disabled");

            $("#endMetas").click(function(){
                alertify.confirm("FINALIZAR MODIFICACIÓN DE METAS", "¿ Confirma que desea finalizar la modificación de metas ?. <p>Esta acción modificará las metas temporalmente, viendose los cambios reflejados cuando el folio se autorice</p>",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                    metodo: "revisarFolio"
                                    ' . (isset($this->prm->omo_id) ? ',omo_id:"' . $this->prm->omo_id . '"' : '') . '
                                    ' . (isset($this->prm->mod_gpo_id) ? ',mod_gpo_id:"' . $this->prm->mod_gpo_id . '"' : '') . '
                                    ' . (isset($this->prm->mod_ur_id) ? ',mod_ur_id:"' . $this->prm->mod_ur_id . '"' : '') . '
                                    ' . (isset($this->prm->mod_id) ? ',mod_id:"' . $this->prm->mod_id . '"' : '') . '
                                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : '') . '
                                });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Si", cancel:"No"});
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"borrarDatosModificados",
                    omo_id:"' . $this->prm->omo_id . '",
                    mod_gpo_id:"' . $this->prm->mod_gpo_id . '",
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:"' . $this->prm->mod_id . '",
                    ecf_id:"' . $m->getEcf_id() . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                });
            });

            $(".verMetasDet").click(function(){
                $(".divMetasDet").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"metas",
                    omo_id:"' . $this->prm->omo_id . '",
                    mod_gpo_id:"' . $this->prm->mod_gpo_id . '",
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:"' . $this->prm->mod_id . '",
                    pii_id:$(this).attr("pii_id"),
                    epg_id:$(this).attr("epg_id"),
                    macm_id:$(this).attr("macm_id")
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"'
            : '') . '
                    ' . ((!$lectura) ? ',lectura:false' : ',lectura:true') . '
                });
            });
        ');
    }

    //MUESTRA LA INFORMACION CALENDARIZADA DE LA META
    private function metas()
    {
        $this->setContenedor('oa' . $this->prm->epg_id . $this->prm->pii_id);
        $m = new MaestroModificacion($this->prm);
        $enc = new OrigenOperacion(['enc_oop' => $m->getOop()]);
        $lectura = $this->prm->lectura;
        $consulta = ' LEFT JOIN ';

        $this->prm->gpo_id = $this->prm->mod_gpo_id;
        $this->prm->ur_id = $this->prm->mod_ur_id;
        $this->prm->eri_id = $this->prm->pii_id;

        $sql = '
            SELECT emo_id
            FROM scp_MaestroModificacion
            WHERE omo_id = :omo_id
            AND mod_gpo_id = :gpo_id AND mod_ur_id = :ur_id
            AND mod_id = :mod_id
        ';

        $params = [
            ':gpo_id' => $this->prm->mod_gpo_id,
            ':ur_id'  => $this->prm->mod_ur_id,
            ':omo_id' => $this->prm->omo_id,
            ':mod_id' => $this->prm->mod_id,
        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();
        $datos = $conexion->extraer();

        if ($datos['emo_id'] == 14) {
            $filtro = BD_CTRL . '.aux_HistoricoMeta';
            $variableVD = 'macm_valorDeseado';
        } else {
            $filtro = BD_TEMPORAL . '.tem_ModificacionMetas';
            $variableVD = 'macm_valorDeseado';
        }

        //Captura de Avance
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
            mac_descripcion,epg_id,mac_original, macm_id, i.umi_id, macm_valorDeseado, tri_id,
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
                    SELECT omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id,mact_modificado, macm_valorDeseado, tmf_id, tri_id
                    FROM ' . $filtro . '
                    WHERE  epg_id = :epg
                    AND pii_id = :pii
                    AND omo_id= :omo and mod_gpo_id= :gpo and mod_ur_id=:ur and mod_id= :mod_id
                    GROUP BY omo_id,mod_gpo_id,mod_ur_id,mod_id,pii_id,epg_id
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

        $parametros = [
            'epg'    => $this->prm->epg_id,
            'pii'    => $this->prm->pii_id,
            'omo'    => $this->prm->omo_id,
            'gpo'    => $this->prm->mod_gpo_id,
            'ur'     => $this->prm->mod_ur_id,
            'mod_id' => $this->prm->mod_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();

        $datos = $this->extraer();
        $this->prm->tri_id = $datos['tri_id'];

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
        $macOriginal->value = $datos['macm_valorDeseado'] != 0 ? $datos['macm_valorDeseado'] : $historicoMeta->getOriginal();
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

        if ($datos['tri_id'] == 1) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1() != 0 ? $historicoMeta->getModificadoT1() : $historicoMeta->getNumeradorT1();
            $trimestre2Cal1->value = $historicoMeta->getNumeradorT2();
            $trimestre3Cal1->value = $historicoMeta->getNumeradorT3();
            $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($datos['tri_id'] == 2) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2() != 0 ? $historicoMeta->getModificadoT2() : $historicoMeta->getNumeradorT2();
            $trimestre3Cal1->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal1->value = $historicoMeta->getDenominadorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($datos['tri_id'] == 3) {
            # code...
            $trimestre1Cal1->value = $historicoMeta->getModificadoT1();
            $trimestre2Cal1->value = $historicoMeta->getModificadoT2();
            $trimestre3Cal1->value = $historicoMeta->getModificadoT3() != 0 ? $historicoMeta->getModificadoT3() : $historicoMeta->getNumeradorT3();
            $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();

            $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
            $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
            $trimestre3Cal2->value = $datos['mact_modificado'] != '' ? $datos['mact_modificado'] : $historicoMeta->getDenominadorT3();
            $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();
        } elseif ($datos['tri_id'] == 4) {
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

        if ($datos['tiv_id'] == 1) {
            $macDeseadoNumerador->value = number_format($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value, 4);
            $macDeseadoDenominador->value = number_format($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value, 4);
        } elseif ($datos['tiv_id'] == 2) {
            $macDeseadoNumerador->value = number_format($trimestre4Cal1->value, 4);
            $macDeseadoDenominador->value = number_format($trimestre4Cal2->value, 4);
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
        $this->setContenedor('historial');

        $mod = new MaestroModificacion($this->prm);
        $enc_oop = new OrigenOperacion(['enc_oop' => $this->prm->enc_oop]);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);

        // se modifica el historial ya que existen registros repetidos
        $sql = "
            SELECT
                DATE_FORMAT(MAX(b.usr_fecha),'%d/%m/%Y') fecha, time(max(b.usr_fecha)) hora,b.mod_id, MAX(em.emo_nombre) emo_nombre,
                if(b.emo_id=3 or b.emo_id=5 or b.emo_id=7 or b.emo_id=9 or b.emo_id=11 or b.emo_id=13,  trim(replace(b.bit_observacion,'\"',' ')),'') as observacion, u.usr_id,u.usr_nombre,b.emo_id,u.usr_paterno,u.usr_materno,
                MAX(b.bit_mod) bit_mod
            FROM " . BD_BITACORA . '.bit_MaestroModificacion b
            INNER JOIN cat_EstadoModificacion em using(emo_id)
            INNER JOIN scp_MaestroModificacion m using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
            INNER JOIN ' . BD_USUARIO . ".cat_Usuario u on b.usr_id=u.usr_id
            WHERE m.omo_id='{$mod->getOmo_id()}'
            AND m.mod_gpo_id={$mod->getMod_gpo_id()}
            AND m.mod_ur_id={$mod->getMod_ur_id()}
            AND m.mod_id={$mod->getMod_id()}
            AND b.bit_observacion not in ('MOVIMIENTO AGREGADO','MOVIMIENTO ACTUALIZADO','MOVIMIENTO ELIMINADO')
            GROUP BY mod_id,emo_id,observacion, u.usr_id,u.usr_nombre,b.emo_id,u.usr_paterno,u.usr_materno
            ORDER BY MAX(b.usr_fecha) desc, bit_mod; ";

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $body = '';
        while ($row = $this->extraer()) {
            $body .= '
                <tr>
                    <td>' . $row['fecha'] . '</td>
                    <td>' . $row['hora'] . '</td>
                    <td>' . $row['emo_id'] . ' - ' . $row['emo_nombre'] . '</td>
                    <td>' . $row['observacion'] . '</td>
                    <td>' . $row['usr_id'] . ' - ' . $row['usr_nombre'] . ' ' . $row['usr_paterno'] . ' ' . $row['usr_materno'] . '</td>
                </tr>';
        }

        $ur = new UnidadResponsable();
        $ur->setGpo_id($mod->getMod_gpo_id());
        $ur->setUr_id($mod->getMod_ur_id());
        $ur->select();

        $modalHistorial = '
        <div id="myModal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog shadow-lg modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel"></h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table  class="table table table-bordered table-hover">
                                <tbody>
                                        <tr>
                                            <th width="15%">Unidad Responsable </th>
                                            <td colspan="5">' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . ' - ' . $ur->getNombre() . '</td>
                                        </tr>
                                        <tr>
                                            <th>Folio</th>
                                            <td width="20%">' . $mod->getMod_id() . '</td>
                                            <th width="10%">Tipo de Adecuación</th>
                                            <td width="28%">' . $tipoMod->getNombre() . '</td>
                                            <th width="15%">Modalidad de adecuación</th>
                                            <td width="28%">' . (($enc_oop->getMmd_id() == 'E') ? 'EXTERNAS' : 'INTERNAS') . '</td>
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
        </div>';

        $this->mixHtml($modalHistorial);

        $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            });

        ');
    }
}
/* * *** FIN FUNCIONES ********************** */
if (isset($_SESSION[_S]['prm']['S']['5']['5']['1'])) {
    new ControladorConsultaFolios();
} else {
    WebResponse::sinPermisos();
}
