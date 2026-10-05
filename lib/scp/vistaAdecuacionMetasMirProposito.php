<?php
require_once dirname(__FILE__) . '/vistaScp.php';

class vistaAdecuacionMetasMirProposito extends vistasScp
{

    public function vwBotones($tipo = '', $mod = array(), $nivelRevision = "")
    {
        if (!isset($tipo)) {
            return false;
        }

        if (isset($_SESSION[_S]['nvj_id']) && !IS_NULL($_SESSION[_S]['nvj_id'])) {
            $nvj_id = $_SESSION[_S]['nvj_id'];
        } else {
            return false;
        }

        //SECCION UR
        $prmUR_alta = isset($_SESSION[_S]['prm']['S']['1']['9']['112']) &&
            ((($_SESSION[_S]['nvj_id'] == 1 || $_SESSION[_S]['nvj_id'] == 2) &&
            ($_SESSION[_S]['prf_id'] == 'C' || $_SESSION[_S]['prf_id'] == 'A')
        ) || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'T'));
        $prmUR_validacion = isset($_SESSION[_S]['prm']['S']['1']['9']['113']) && $_SESSION[_S]['nvj_id']
            == 2 &&
            ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T');
        $prmUR_autorizacion = isset($_SESSION[_S]['prm']['S']['1']['9']['114'])
            && $_SESSION[_S]['nvj_id'] == 2 &&
            $_SESSION[_S]['prf_id'] == 'T';

        //SECCION SEFIN
        $prmSEFIN_alta = isset($_SESSION[_S]['prm']['S']['1']['9']['108']) && $_SESSION[_S]['nvj_id'] >= 2;
        $prmSEFIN_revision = isset($_SESSION[_S]['prm']['S']['1']['9']['109']) && $_SESSION[_S]['nvj_id'] >= 2;
        $prmSEFIN_validacion = isset($_SESSION[_S]['prm']['S']['1']['9']['110']) && $_SESSION[_S]['nvj_id'] >= 2;
        $prmSEFIN_autorizacion = isset($_SESSION[_S]['prm']['S']['1']['9']['111']) && $_SESSION[_S]['nvj_id'] >= 2;

        $prmPlaneacion = $html = '';
        switch ($tipo) {

            case 'menuPrincipal':
                $cards = [];

                if ($prmUR_alta) {
                    $c = new stdClass();
                    $c->id = 'altafoliour';
                    $c->class = 'altafoliour';
                    $c->titulo = 'UR';
                    $c->img = 'img/Alta_Folio.png';
                    $c->label = 'Alta de Folios';
                    $c->title = 'Alta de Folios';
                    $cards[] = $c;
                }
                if ($prmUR_validacion) {
                    $c = new stdClass();
                    $c->id = 'validafoliour';
                    $c->class = 'validafoliour';
                    $c->titulo = 'UR';
                    $c->img = 'img/Validacion_Folio.png';
                    $c->label = 'Validación de Folios';
                    $c->title = 'Validación de Folios';
                    $cards[] = $c;
                }
                if ($prmUR_autorizacion) {
                    $c = new stdClass();
                    $c->id = 'autorizafoliour';
                    $c->class = 'autorizafoliour';
                    $c->titulo = 'UR';
                    $c->img = 'img/Autoriza_Folio.png';
                    $c->label = 'Autorización de Folios';
                    $c->title = 'Autorización de Folios';
                    $cards[] = $c;
                }
                if ($prmSEFIN_alta) {
                    $c = new stdClass();
                    $c->id = 'altafoliosefin';
                    $c->class = 'altafoliosefin';
                    $c->titulo = 'SEFIN';
                    $c->img = 'img/Alta_Folio.png';
                    $c->label = 'Alta de Folios';
                    $c->title = 'Alta de Folios';
                    $cards[] = $c;
                }
                if ($prmSEFIN_revision) {
                    $c = new stdClass();
                    $c->id = 'revisafoliosefin';
                    $c->class = 'revisafoliosefin';
                    $c->titulo = 'SEFIN';
                    $c->img = 'img/Revision_Folio.png';
                    $c->label = 'Revisión de Folios';
                    $c->title = 'Revisión de Folios';
                    $cards[] = $c;
                }
                if ($prmSEFIN_validacion) {
                    $c = new stdClass();
                    $c->id = 'validafoliosefin';
                    $c->class = 'validafoliosefin';
                    $c->titulo = 'SEFIN';
                    $c->img = 'img/Validacion_Folio.png';
                    $c->label = 'Validación de Folios';
                    $c->title = 'Validación de Folios';
                    $cards[] = $c;
                }
                if ($prmSEFIN_autorizacion) {
                    $c = new stdClass();
                    $c->id = 'autorizafoliosefin';
                    $c->class = 'autorizafoliosefin';
                    $c->titulo = 'SEFIN';
                    $c->img = 'img/Autoriza_Folio.png';
                    $c->label = 'Autorización de Folios';
                    $c->title = 'Autorización de Folios';
                    $cards[] = $c;
                }

                return $this->ponCards($cards);
                break;
            case 'nuevoFolio':
                $nuevoFolio = new stdClass();
                $nuevoFolio->value = 'Nuevo';
                $nuevoFolio->id = 'addFolio';
                $nuevoFolio->class = 'btn btn-outline-primary me-1';
                $nuevoFolio->icon = 'fa-solid fa-plus me-2';
                return $nuevoFolio;
                break;
            case 'guardarFolio':
                $btnGuardarFolio = new stdClass();
                $btnGuardarFolio->id = 'btnGuardarFolio';
                $btnGuardarFolio->icon = 'fa-solid fa-floppy-disk me-2';
                $btnGuardarFolio->class = 'btn btn-outline-success me-1';
                $btnGuardarFolio->text = 'Guardar';
                $btnGuardarFolio->type = 'submit';
                $btnGuardarFolio->value = 'Guardar';
                return $btnGuardarFolio;
                break;
            case 'eliminarMovimiento':
            case 'eliminarFolio':
                $btnEliminarFolio = new stdClass();
                $btnEliminarFolio->id = 'btnEliminarFolio';
                $btnEliminarFolio->class = 'btn btn-outline-danger me-1';
                $btnEliminarFolio->icon = 'fa-solid fa-trash me-2';
                $btnEliminarFolio->text = 'Eliminar';
                $btnEliminarFolio->type = 'submit';
                $btnEliminarFolio->value = 'Eliminar';
                return $btnEliminarFolio;
                break;
            case 'regresar':
                $btnRegresarIndex = new stdClass();
                $btnRegresarIndex->id = 'btnRegresar';
                $btnRegresarIndex->icon = 'fa-solid fa-circle-left me-2';
                $btnRegresarIndex->class = 'btn btn-outline-secondary me-1';
                $btnRegresarIndex->text = 'Regresar';
                $btnRegresarIndex->value = 'Regresar';
                return $btnRegresarIndex;
                break;
            case 'historial':
                $btnHistorial = new stdClass();
                $btnHistorial->id = 'btnHistorial';
                $btnHistorial->class = 'btn btn-outline-secondary me-1';
                $btnHistorial->text = 'Historial';
                $btnHistorial->icon = 'fa fa-list-alt me-2';
                $btnHistorial->value = 'Historial';
                return $btnHistorial;
                break;
            case 'nuevoMovimiento':
                $nuevaModificacion = new stdClass();
                $nuevaModificacion->id = 'addModificacion';
                $nuevaModificacion->class = 'btn btn-outline-primary me-1 addModificacion';
                $nuevaModificacion->icon = 'fa-solid fa-plus me-2';
                return $nuevaModificacion;
                break;
            case 'VoBo':
                $btnVobo = new stdClass();
                $btnVobo->id = 'btnVistoBueno';
                $btnVobo->class = 'btn-test btnVistoBueno';
                $btnVobo->icon = 'glyphicon glyphicon-ok';
                $btnVobo->text = 'VoBo.';
                $btnVobo->value = 'VoBo.';
                return $btnVobo;
                break;
            case 'rechazarVoBo':
                $btnRechazarVobo = new stdClass();
                $btnRechazarVobo->id = 'btnRechazarVobo';
                $btnRechazarVobo->class = 'btn-test btnRechazarVobo';
                $btnRechazarVobo->icon = 'fa-solid fa-xmark me-2';
                $btnRechazarVobo->text = 'Rechazar';
                $btnRechazarVobo->color = 'rojo';
                $btnRechazarVobo->value = 'Rechazar';
                return $btnRechazarVobo;
                break;
            case 'eliminarMovimientos':
                $btnEliminarMovimientos = new stdClass();
                $btnEliminarMovimientos->id = 'btnEliminarMovimientos';
                $btnEliminarMovimientos->class = 'btn btn-outline-danger me-1 btnEliminarMovimientos';
                $btnEliminarMovimientos->icon = 'fa-solid fa-trash me-2';
                $btnEliminarMovimientos->text = 'Eliminar movimientos';
                $btnEliminarMovimientos->value = 'Eliminar movimientos';
                return $btnEliminarMovimientos;
                break;
            case 'validar':
                $btnValidarSEFIN = new stdClass();
                $btnValidarSEFIN->id = 'btnValidarSEFIN';
                $btnValidarSEFIN->class = 'btn btn-outline-success me-1 btnValidarSEFIN';
                $btnValidarSEFIN->icon = 'fa-solid fa-check me-2';
                $btnValidarSEFIN->text = 'Validar';
                $btnValidarSEFIN->value = 'Validar';
                return $btnValidarSEFIN;
                break;
            case 'rechazar':
                $btnRechazarSEFIN = new stdClass();
                $btnRechazarSEFIN->id = 'btnRechazarSEFIN';
                $btnRechazarSEFIN->class = 'btn btn-outline-danger me-1 btnRechazarSEFIN';
                $btnRechazarSEFIN->icon = 'fa-solid fa-xmark me-2';
                $btnRechazarSEFIN->text = 'Rechazar';
                $btnRechazarSEFIN->value = 'Rechazar';
                return $btnRechazarSEFIN;
                break;
            case 'guardarModificacion':
                $btnGuardarModificacion = new stdClass();
                $btnGuardarModificacion->id = 'btnGuardarModificacion';
                $btnGuardarModificacion->class = 'btn btn-outline-success me-1';
                $btnGuardarModificacion->icon = 'fa-solid fa-floppy-disk me-2';
                $btnGuardarModificacion->text = 'Guardar';
                $btnGuardarModificacion->type = 'submit';
                $btnGuardarModificacion->value = 'Guardar';
                $btnGuardarModificacion->form = 'Modificaciones';
                return $btnGuardarModificacion;
                break;
            case 'guardarMeta':
                $btnGuardarModificacionM = new stdClass();
                $btnGuardarModificacionM->id = 'btnGuardarModificacion';
                $btnGuardarModificacionM->class = 'btn btn-outline-success me-1';
                $btnGuardarModificacionM->icon = 'fa-solid fa-floppy-disk me-2';
                $btnGuardarModificacionM->text = 'Guardar';
                $btnGuardarModificacionM->type = 'submit';
                $btnGuardarModificacionM->value = 'Guardar';
                return $btnGuardarModificacionM;
                break;
            
        }
    }

// fin vwBotones

    public function vwTablaUrs($nivelRevision = '', $esSefin = false, $pantalla = '')
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl-Ur';
        $tabla->titulo = 'Unidades Responsables';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $pantalla;
        $tabla->comunica->metodo = 'llenaTablaUrs';
        $tabla->comunica->nivelRevision = ($nivelRevision != '') ?
        $nivelRevision : '';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);
        $tr = array();

        //Seleccionar ur
        $th = new stdClass();
        $th->width = '10px';
        $th->texto = 'Sel';
        array_push($tr, $th);
        //clave de ur
        $th = new stdClass();
        $th->texto = 'Ur';
        array_push($tr, $th);
        //Nombre de la UR
        $th = new stdClass();
        $th->texto = 'Unidad Responsable';
        array_push($tr, $th);
        //Número de folios por UR
        if ($esSefin) {
            $th = new stdClass();
            $th->texto = 'Número de folios';
            array_push($tr, $th);
        }

        $trbody = array();

        $td = new stdClass();
        $td->attr = new stdClass();
        $td->attr->omo_id = "omo_id";
        $td->attr->mme_gpo_id = "mme_gpo_id";
        $td->attr->mme_ur_id = "mme_ur_id";
        $td->class = "verUr btn-xs";
        $td->tipo = "boton";
        $td->icono = "pencil";
        array_push($trbody, $td);
        //Clave ur
        $td = new stdClass();
        $td->campoTxt = "ur_clave";
        array_push($trbody, $td);
        //nombre UR
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);
        //Numero de folios
        // SI ES SEFIN ENTONCES MUESTRA EL NUMERO DE FOLIOS PENDIENTES DE URS
        if ($esSefin) {
            $td = new stdClass();
            $td->campoTxt = 'numFolios';
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        return $tabla;
    }

// fin vwTablaUrs

    public function vwToolsTablaFolios($prm)
    {
        if (!isset($prm->mostrar)) {
            return false;
        }
        if ($prm->mostrar == 'ur' && !isset($prm->parametrosExtra->objUR)) {
            return false;
        }
        switch ($prm->mostrar) {
            case 'ur':
                $unidadResponsable = 'Unidad Responsable: ' . $prm->parametrosExtra->objUR->getClave() . ' - ' . $prm->parametrosExtra->objUR->getNombre();
                $tools = '
                    <fieldset class="col-md-12 col-xs-12">
                        <label>' . $unidadResponsable . '</label>
                    </fieldset>
                ';
                return $tools;
                break;
        }
    }

// fin vwToolsTablaFolios

    public function vwInput($nombre, $mme, $esUR = false)
    {
        if (!isset($nombre)) {
            return false;
        }
        switch ($nombre) {

            case 'numeroFolio':
                $numeroFolio = new stdClass();
                $numeroFolio->id = 'folio';
                $numeroFolio->class = 'mb-4';
                $numeroFolio->formato = 'entero';
                $numeroFolio->enDiv = true;
                $numeroFolio->maxlength = 6;
                $numeroFolio->label = 'Número de Folio';
                $numeroFolio->required = true;
                if ($mme->getMme_id() != '') {
                    $numeroFolio->disabled = true;
                }
                $numeroFolio->value = $mme->getMme_id();
                if (!$esUR) {
                    $numeroFolio->validaAjax = 'folioAdecuacionMeta';
                    $numeroFolio->siExiste = 0;
                    $numeroFolio->datosExtra = new stdClass();
                    $numeroFolio->mensajeValido = "Folio válido";
                    $numeroFolio->mensajeNoValido = "El folio ya existe";
                }
                return $numeroFolio;
                break;
            case 'justificacion':
                $justificacion = new stdClass();
                $justificacion->id = 'concepto';
                $justificacion->label = 'Justificación';
                $justificacion->enDiv = true;
                $justificacion->maxlength = '300';
                $justificacion->required = true;
                $justificacion->texto = $mme->getConcepto();
                return $justificacion;
                break;
            case 'fecha':
                $fecha = new stdClass();
                $fecha->id = 'fechaFolio';
                $fecha->label = 'Fecha de creación';
                $fecha->enDiv = true;
                $fecha->required = true;
                if ($mme->getFechaFolio() != '') {
                    $fecha->value = $mme->getFechaFolio();
                    $fecha->readonly = true;
                }
                return $fecha;
                break;
            case 'oficioSolicitud':
                $oficioSolicitud = new stdClass();
                $oficioSolicitud->id = 'oficioSolicitudSefin';
                $oficioSolicitud->label = 'Oficio de Solicitud Ejecutor de gasto';
                $oficioSolicitud->enDiv = true;
                $oficioSolicitud->maxlength = '35';
                $oficioSolicitud->required = true;
                $oficioSolicitud->value = $mme->getOficioSolicitudSefin();
                return $oficioSolicitud;
                break;
            case 'oficioAutorizacion':
                $oficioAutorizacion = new stdClass();
                $oficioAutorizacion->id = 'OficioSefin';
                $oficioAutorizacion->label = 'Oficio de Autorización SEFIN';
                $oficioAutorizacion->enDiv = true;
                $oficioAutorizacion->maxlength = '35';
                $oficioAutorizacion->required = true;
                $oficioAutorizacion->value = $mme->getOficioSefin();
                return $oficioAutorizacion;
                break;
        } //fin casos
    }

// fin vwInput

    public function vwCombo($nombre, $mme)
    {
        if (!isset($nombre)) {
            return false;
        }

        switch ($nombre) {
            case 'modalidadAdecuacion':
                $modalidadModificacion = new stdClass();
                $modalidadModificacion->id = 'mmd_id';
                $modalidadModificacion->class = 'mb-4';
                $modalidadModificacion->label = 'Modalidad de Adecuación';
                $modalidadModificacion->opciones = array("E" => "EXTERNAS");
                $modalidadModificacion->selecciona = "E";
                $modalidadModificacion->required = true;
                $modalidadModificacion->disabled = true;
                return $modalidadModificacion;
                break;
            case 'unidadEjecutora':
                if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {
                    $comboUe = new stdClass();
                    $comboUe->id = 'mme_ue_id';
                    $comboUe->label = 'Unidad Ejecutora';
                    $comboUe->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                    INNER JOIN presupuesto USING(gpo_id,ur_id,ue_id)
                    WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                    ' . (($mme->getMme_id() != '') ? 'AND ue_id = ' . $mme->getMme_ue_id() . ''
                        : "") . '
                    GROUP BY gpo_id,ur_id,ue_id ORDER BY ue_id;';
                    $comboUe->required = true;
                } else {
                    $comboUe = '';
                }
                return $comboUe;
                break;
            case 'tipoFolio':
                $tipoFolio = new stdClass();
                $tipoFolio->id = 'tfm_id';
                $tipoFolio->label = 'Tipo de Folio';
                $tipoFolio->required = true;
                $tipoFolio->opciones = array("P" => "ADECUACIÓN DE METAS MIR PROPÓSITO");
                return $tipoFolio;
                break;
            case 'tipoAdecuacion':
                $tipoModificacion = new stdClass();
                $tipoModificacion->id = 'tmd_id';
                $tipoModificacion->label = 'Tipo de Adecuación';
                $tipoModificacion->opciones = array("P" => "ADECUACIÓN DE METAS MIR PROPÓSITO");
                $tipoModificacion->required = true;
                return $tipoModificacion;
                break;
            case 'subsecretaria':
                $subsecretaria = new stdClass();
                $subsecretaria->id = 'Subsecretaria';
                $subsecretaria->label = 'Subsecretaría';
                $subsecretaria->opciones = array(2 => "SUBSECRETARÍA DE PLANEACION");
                $subsecretaria->required = true;
                return $subsecretaria;
                break;
            case 'subprograma':
                $subprograma = new stdClass();
                $subprograma->id = "subprograma";
                $subprograma->search = true;
                $subprograma->required = true;
                return $subprograma;
                break;
        }
    }

// fin vwCombo

    public function vwInformacionFolio($mme, $objMov = '')
    {
        $html = '
            <div class="row">
                ' . ($objMov != '' ?
            '<fieldset class="col-md-12 col-xs-12">
                    <legend>Información del Folio</legend>' : "") . '
                <fieldset class="' . ($mme->getTmd_id() == 'M' ?
            'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                    <label> Número de Folio: ' . $mme->getMme_id() . '</label>
                </fieldset>
                <fieldset class="' . ($mme->getTmd_id() == 'M' ?
            'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                    <label>Modalidad de Adecuación: ' . ($mme->getMmd_id() == "E"
            ? 'EXTERNAS' : 'INTERNAS') . '</label>
                </fieldset>
                <fieldset class="' . ($mme->getTmd_id() == 'M' ?
            'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                    <label>Tipo de Adecuación: ADECUACIÓN DE METAS</label>
                </fieldset>
                <fieldset class="' . ($mme->getTmd_id() == 'M' ?
            'col-md-3 col-xs-3' : '') . '">
        ';

        $html .= '</fieldset>
            ' . ($objMov != '' ?
            '</fieldset>' : "") . '
            </div>
            <div class="row">
                <fieldset id="Mensaje" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
        ';
        return $html;
    }

    //TABLA QUE MUESTRA LAS OBRAS/ACTIVIDADES INGRESADAS POR FOLIO
    public function vwTablaObrasActividades($mme, $esUR, 
        $presupuesto, $pantalla, $nivelRevision) {
            $tabla = new stdClass();
            $tabla->id = 'tbl-Modificaciones';
            $tabla->titulo = 'Metas Proposito';
            $tabla->comunica = new stdClass();
            $tabla->comunica->pantalla = $pantalla;
            $tabla->comunica->omo_id = $mme->getOmo_id();
            $tabla->comunica->mod_gpo_id = $mme->getMme_gpo_id();
            $tabla->comunica->mod_ur_id = $mme->getMme_ur_id();
            $tabla->comunica->mod_id = $mme->getMme_id();
            $tabla->comunica->nivelRevision = $nivelRevision;
            $tabla->comunica->metodo = 'llenaTablaModificaciones';
            $tabla->numFilas = 100;
            $tabla->filasAmostrar = array(5, 10, 50, 100, 150);
            $tr = array();
    
            //Ver/Editar
            $th = new stdClass();
            $th->width = '10px';
            $th->texto = 'Ver / Editar';
            array_push($tr, $th);
            //Partida presupuestal agregada al folio
            $th = new stdClass();
            $th->texto = 'Programa';
            array_push($tr, $th);
            //Descripcion de la partida presupuestal
            $th = new stdClass();
            $th->texto = 'Indicador';
            array_push($tr, $th);

            $th = new stdClass();
            $th->width = '10px';
            $th->texto = 'Eliminar';
            array_push($tr, $th);

            $trbody = array();
    
            //contenido ver
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = "emo_id";
            $td->casos->tipoCondicion = "||";
            if ($esUR) {
                $td->casos->valor = "1,3";
            } else if ($presupuesto) {
                $td->casos->valor = "6,11,17";
            }
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = "boton";
            $td->casos->verdadero->color = "cielo";
            $td->casos->verdadero->icono = "pencil";
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->omo_id = "omo_id";
            $td->casos->verdadero->attr->mme_gpo_id = "mme_gpo_id";
            $td->casos->verdadero->attr->mme_ur_id = "mme_ur_id";
            $td->casos->verdadero->attr->mme_id = "mme_id";
            $td->casos->verdadero->attr->mpr_id = "mpr_id";
            $td->casos->verdadero->attr->pmi_id = "pmi_id";
            $td->casos->verdadero->attr->tri_id = "tri_id";
            $td->casos->verdadero->attr->ind_id = "ind_id";
            $td->casos->verdadero->attr->prg_id = "prg_id";
            $td->casos->verdadero->class = "addModModificacion btn-xs";
            $td->casos->falso = new stdClass();
            $td->casos->falso->tipo = "boton";
            $td->casos->falso->icono = "eye";
            $td->casos->falso->attr = new stdClass();
            $td->casos->falso->attr->omo_id = "omo_id";
            $td->casos->falso->attr->mme_gpo_id = "mme_gpo_id";
            $td->casos->falso->attr->mme_ur_id = "mme_ur_id";
            $td->casos->falso->attr->mme_id = "mme_id";
            $td->casos->falso->attr->mpr_id = "mpr_id";
            $td->casos->falso->attr->pmi_id = "pmi_id";
            $td->casos->falso->attr->tri_id = "tri_id";
            $td->casos->falso->attr->ind_id = "ind_id";
            $td->casos->falso->attr->prg_id = "prg_id";
            $td->casos->falso->class = "addModModificacion btn-xs";
            array_push($trbody, $td);
            //Partida Presupuestal
            $td = new stdClass();
            $td->campoTxt = 'programa';
            array_push($trbody, $td);
            //Nombre de la partida presupuestal
            $td = new stdClass();
            $td->campoTxt = 'indicador';
            array_push($trbody, $td);

            if ($nivelRevision == "alta") {
                $td = new stdClass();
                $td->class = "deleteMeta btn-xs";
                $td->color = "rojo";
                $td->tipo = "boton";
                $td->icono = "trash";
                $td->attr = new stdClass();
                $td->attr->omo_id = "omo_id";
                $td->attr->mme_gpo_id = "mme_gpo_id";
                $td->attr->mme_ur_id = "mme_ur_id";
                $td->attr->mme_id = "mme_id";
                $td->attr->mpr_id = "mpr_id";
                $td->attr->pmi_id = "pmi_id";
                $td->attr->tri_id = "tri_id";
                $td->attr->ind_id = "ind_id";
                $td->attr->prg_id = "prg_id";
                $td->attr->lectura = 1;
                array_push($trbody, $td);
            }

            $tabla->encabezado = array($tr);
            $tabla->cuerpo = $trbody;
            return $tabla;
    }

    public function vwSubprograma($prm)
    {

        $sql = '';
        if (isset($prm->ind)) {
            $sql ='
                SELECT nim_id,ind_id, pmi_id AS eri_id, umi_id, ind_nombre,
                mpr_id, pmi_id
                FROM vin_ProgramaMirIndicador v
                INNER JOIN (SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id
                    FROM vin_ProgramasMir
                    UNION
                    SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id
                    FROM vin_UrProgramaMir ur
                    INNER JOIN vin_ProgramasMir USING(vpm_id)
                )v USING (vpm_id)
                INNER JOIN cat_Indicador USING(ind_id,nim_id)
                INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                INNER JOIN cat_MetaProposito USING(pmi_id)
                WHERE gpo_id = ' . $prm->gpo_id .' AND ur_id = ' . $prm->ur_id . '
                AND prgs_id = ' . $prm->prg_id . '
                AND nim_id = 2 AND v.sts_id=1
                AND ind_id NOT IN('.$prm->ind.');
            ';
        } else {
            $sql ='
                SELECT nim_id,ind_id,pmi_id AS eri_id,umi_id, ind_nombre,
                mpr_id, pmi_id
                FROM vin_ProgramaMirIndicador v
                INNER JOIN (SELECT vpm_id, prgs_id, mir_id, gpo_id, ur_id
                    FROM vin_ProgramasMir
                    UNION
                    SELECT vpm_id, ur.prgs_id, mir_id, ur.gpo_id, ur.ur_id
                    FROM vin_UrProgramaMir ur
                    INNER JOIN vin_ProgramasMir USING(vpm_id)
                )v USING (vpm_id)
                INNER JOIN cat_Indicador USING(ind_id,nim_id)
                INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
                INNER JOIN cat_MetaProposito USING(pmi_id)
                WHERE gpo_id = ' . $prm->gpo_id .' AND ur_id = ' . $prm->ur_id . '
                AND prgs_id = ' . $prm->prg_id . '
                AND nim_id = 2 AND v.sts_id=1;
            ';
        }

        $subprograma = $this->vwCombo('subprograma', '');
        $subprograma->sql = $sql;
        $subprograma->comoAtr = "ind_id,umi_id,eri_id,mpr_id,pmi_id";
        $subprograma->camposText = "ind_id,ind_nombre";

        return $subprograma;
    } // Fin vwTipoAdecuacion


    public function vwMuestraTablaError($datos)
    {
        if (!is_array($datos)) {
            return false;
        }

        $td = '';
        foreach ($datos as $key => $valor) {
            $td .= '
                <tr>
                    <td style="text-align:center">' . $valor['numero'] . '</td>
                    <td>' . mb_convert_encoding($valor['mensaje'], 'UTF-8', 'ISO-8859-1') . '</td>
                </tr>
            ';
        }

        $html = '
            <table style="width:100%" class="table table-striped table-bordered">
                <tr class="warning" role="row">
                    <thead>
                        <tr>
                            <th style="text-align:center">Error</th>
                            <th style="text-align:center">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $td . '
                    </tbody>
                </tr>
            </table>
        ';

        return $html;
    }
}
// fin de la clase
