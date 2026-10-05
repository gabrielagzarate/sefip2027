<?php

require_once dirname(__FILE__) . '/vistaScp.php';

class vistaAdecuacionMetas extends vistasScp
{
    public function vwBotones($tipo = '', $mod = [], $nivelRevision = '')
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
        $prmUR_alta = isset($_SESSION[_S]['prm']['S']['1']['7']['7']) &&
            ((
                ($_SESSION[_S]['nvj_id'] == 1 || $_SESSION[_S]['nvj_id'] == 2) &&
            ($_SESSION[_S]['prf_id'] == 'C' || $_SESSION[_S]['prf_id'] == 'A')
            ) || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'T'));
        $prmUR_validacion = isset($_SESSION[_S]['prm']['S']['1']['7']['8']) && $_SESSION[_S]['nvj_id']
            == 2 &&
            ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T');
        $prmUR_autorizacion = isset($_SESSION[_S]['prm']['S']['1']['7']['9'])
            && $_SESSION[_S]['nvj_id'] == 2 &&
            $_SESSION[_S]['prf_id'] == 'T';

        //SECCION SEFIN
        $prmSEFIN_alta = isset($_SESSION[_S]['prm']['S']['1']['7']['14']) && $_SESSION[_S]['nvj_id'] >= 3;
        $prmSEFIN_revision = isset($_SESSION[_S]['prm']['S']['1']['7']['15']) && $_SESSION[_S]['nvj_id'] >= 3;
        $prmSEFIN_validacion = isset($_SESSION[_S]['prm']['S']['1']['7']['16']) && $_SESSION[_S]['nvj_id'] >= 3;
        $prmSEFIN_autorizacion = isset($_SESSION[_S]['prm']['S']['1']['7']['17']) && $_SESSION[_S]['nvj_id'] >= 3;

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
                $nuevoFolio->class = 'btn btn-outline-primary me-2';
                $nuevoFolio->icon = 'fa-solid fa-plus me-1';
                return $nuevoFolio;
                break;
            case 'guardarFolio':
                $btnGuardarFolio = new stdClass();
                $btnGuardarFolio->id = 'btnGuardarFolio';
                $btnGuardarFolio->icon = 'fa-solid fa-floppy-disk me-1';
                $btnGuardarFolio->class = 'btn btn-outline-success me-2';
                $btnGuardarFolio->text = 'Guardar';
                $btnGuardarFolio->type = 'submit';
                $btnGuardarFolio->value = 'Guardar';
                return $btnGuardarFolio;
                break;
            case 'eliminarMovimiento':
            case 'eliminarFolio':
                $btnEliminarFolio = new stdClass();
                $btnEliminarFolio->id = 'btnEliminarFolio';
                $btnEliminarFolio->class = 'btn btn-outline-danger me-2';
                $btnEliminarFolio->icon = 'fa-solid fa-trash me-1';
                $btnEliminarFolio->text = 'Eliminar';
                $btnEliminarFolio->type = 'submit';
                $btnEliminarFolio->value = 'Eliminar';
                return $btnEliminarFolio;
                break;
            case 'regresar':
                $btnRegresarIndex = new stdClass();
                $btnRegresarIndex->id = 'btnRegresar';
                $btnRegresarIndex->icon = 'fa-solid fa-circle-left me-1';
                $btnRegresarIndex->class = 'btn btn-outline-secondary me-2';
                $btnRegresarIndex->text = 'Regresar';
                $btnRegresarIndex->value = 'Regresar';
                return $btnRegresarIndex;
                break;
            case 'historial':
                $btnHistorial = new stdClass();
                $btnHistorial->id = 'btnHistorial';
                $btnHistorial->class = 'btn btn-outline-secondary me-2';
                $btnHistorial->text = 'Historial';
                $btnHistorial->icon = 'fa fa-list-alt me-2';
                $btnHistorial->value = 'Historial';
                return $btnHistorial;
                break;
            case 'nuevoMovimiento':
                $nuevaModificacion = new stdClass();
                $nuevaModificacion->id = 'addModificacion';
                $nuevaModificacion->class = 'btn btn-outline-primary me-2 addModificacion';
                $nuevaModificacion->icon = 'fa-solid fa-plus me-2';
                return $nuevaModificacion;
                break;
            case 'eliminarMovimientos':
                $btnEliminarMovimientos = new stdClass();
                $btnEliminarMovimientos->id = 'btnEliminarMovimientos';
                $btnEliminarMovimientos->class = 'btn-outline-danger me-2 btnEliminarMovimientos';
                $btnEliminarMovimientos->icon = 'fa-solid fa-trash me-1';
                $btnEliminarMovimientos->text = 'Eliminar movimientos';
                $btnEliminarMovimientos->value = 'Eliminar movimientos';
                return $btnEliminarMovimientos;
                break;
            case 'validar':
                $btnValidarSEFIN = new stdClass();
                $btnValidarSEFIN->id = 'btnValidarSEFIN';
                $btnValidarSEFIN->class = 'btn btn-outline-success me-2 btnValidarSEFIN';
                $btnValidarSEFIN->icon = 'fa-solid fa-check me-2';
                $btnValidarSEFIN->text = 'Validar';
                $btnValidarSEFIN->value = 'Validar';
                return $btnValidarSEFIN;
                break;
            case 'rechazar':
                $btnRechazarSEFIN = new stdClass();
                $btnRechazarSEFIN->id = 'btnRechazarSEFIN';
                $btnRechazarSEFIN->class = 'btn btn-outline-danger me-2 btnRechazarSEFIN';
                $btnRechazarSEFIN->icon = 'fa-solid fa-xmark me-2';
                $btnRechazarSEFIN->text = 'Rechazar';
                $btnRechazarSEFIN->value = 'Rechazar';
                return $btnRechazarSEFIN;
                break;
            case 'guardarModificacion':
                $btnGuardarModificacion = new stdClass();
                $btnGuardarModificacion->id = 'btnGuardarModificacion';
                $btnGuardarModificacion->class = 'btn btn-outline-success me-2';
                $btnGuardarModificacion->icon = 'btn glyphicon-floppy-saved me-2';
                $btnGuardarModificacion->text = 'Guardar';
                $btnGuardarModificacion->type = 'submit';
                $btnGuardarModificacion->value = 'Guardar';
                $btnGuardarModificacion->form = 'Modificaciones';
                return $btnGuardarModificacion;
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
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];
        $tr = [];

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

        $trbody = [];

        $td = new stdClass();
        $td->attr = new stdClass();
        $td->attr->omo_id = 'omo_id';
        $td->attr->mme_gpo_id = 'mme_gpo_id';
        $td->attr->mme_ur_id = 'mme_ur_id';
        $td->class = 'btn btn-outline-secondary verUr btn-xs';
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        array_push($trbody, $td);
        //Clave ur
        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
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

        $tabla->encabezado = [$tr];
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
                    $numeroFolio->mensajeValido = 'Folio válido';
                    $numeroFolio->mensajeNoValido = 'El folio ya existe';
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
            case 'justificacionM':
                $justificacionM = new stdClass();
                $justificacionM->id = 'concepto';
                $justificacionM->label = 'Justificación';
                $justificacionM->enDiv = true;
                $justificacionM->maxlength = '300';
                $justificacionM->disabled = true;
                $justificacionM->texto = $mme->getConcepto();
                return $justificacionM;
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
            case 'oficioSolicitudM':
                $oficioSolicitudM = new stdClass();
                $oficioSolicitudM->id = 'oficioSolicitudSefin';
                $oficioSolicitudM->label = 'Oficio de Solicitud Ejecutor de gasto';
                $oficioSolicitudM->enDiv = true;
                $oficioSolicitudM->disabled = true;
                $oficioSolicitudM->value = $mme->getOficioSolicitudSefin();
                return $oficioSolicitudM;
                break;
            case 'oficioAutorizacionM':
                $oficioAutorizacionM = new stdClass();
                $oficioAutorizacionM->id = 'OficioSefin';
                $oficioAutorizacionM->label = 'Oficio de Autorización SEFIN';
                $oficioAutorizacionM->enDiv = true;
                $oficioAutorizacionM->disabled = true;
                $oficioAutorizacionM->value = $mme->getOficioSefin();
                return $oficioAutorizacionM;
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
                $modalidadModificacion->opciones = ['E' => 'EXTERNAS'];
                $modalidadModificacion->selecciona = 'E';
                $modalidadModificacion->required = true;
                $modalidadModificacion->disabled = true;
                return $modalidadModificacion;
                break;
            case 'unidadEjecutora':
                if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {
                    $comboUe = new stdClass();
                    $comboUe->id = 'mme_ue_id';
                    $comboUe->label = 'Unidad Ejecutora';
                    $comboUe->sql = '
                        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                        INNER JOIN presupuesto USING(gpo_id,ur_id,ue_id)
                        WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                        ' . (($mme->getMme_id() != '') ? 'AND ue_id = ' . $mme->getMme_ue_id() . ''
                        : '') . '
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
                $tipoFolio->opciones = ['M' => 'ADECUACIÓN DE METAS'];
                return $tipoFolio;
                break;
            case 'tipoAdecuacion':
                $tipoModificacion = new stdClass();
                $tipoModificacion->id = 'tmd_id';
                $tipoModificacion->label = 'Tipo de Adecuación';
                $tipoModificacion->opciones = ['M' => 'ADECUACIÓN DE METAS'];
                $tipoModificacion->required = true;
                return $tipoModificacion;
                break;
            case 'subsecretaria':
                $subsecretaria = new stdClass();
                $subsecretaria->id = 'Subsecretaria';
                $subsecretaria->label = 'Subsecretaría';
                $subsecretaria->opciones = [1 => 'SUBSECRETARÍA DE EGRESOS'];
                $subsecretaria->required = true;
                return $subsecretaria;
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
                    <legend>Información del Folio</legend>' : '') . '
                    <fieldset class="' . ($mme->getTmd_id() == 'M' ?
                        'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                        <label> Número de Folio: ' . $mme->getMme_id() . '</label>
                    </fieldset>
                    <fieldset class="' . ($mme->getTmd_id() == 'M' ?
                        'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                        <label>Modalidad de Adecuación: ' . ($mme->getMmd_id() == 'E'
                            ? 'EXTERNAS' : 'INTERNAS') . '</label>
                    </fieldset>
                    <fieldset class="' . ($mme->getTmd_id() == 'M' ?
                        'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                        <label>Tipo de Adecuación: ADECUACIÓN DE METAS</label>
                    </fieldset>
                    <fieldset class="' . ($mme->getTmd_id() == 'M' ?
                        'col-md-3 col-xs-3' : '') . '">
        ';

        $html .= '  </fieldset>
                ' . ($objMov != '' ?
                '</fieldset>' : '') . '
            </div>
            <div class="row">
                <fieldset id="Mensaje" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
        ';
        return $html;
    }

    //TABLA QUE MUESTRA LAS OBRAS/ACTIVIDADES INGRESADAS POR FOLIO
    public function vwTablaObrasActividades(
        $mme,
        $esUR,
        $presupuesto,
        $pantalla,
        $nivelRevision
    ) {
        $tabla = new stdClass();
        $tabla->id = 'tbl-Modificaciones';
        $tabla->titulo = 'Obras/Actividades ingresadas por folio';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $pantalla;
        $tabla->comunica->omo_id = $mme->getOmo_id();
        $tabla->comunica->mme_gpo_id = $mme->getMme_gpo_id();
        $tabla->comunica->mme_ur_id = $mme->getMme_ur_id();
        $tabla->comunica->mme_id = $mme->getMme_id();
        $tabla->comunica->nivelRevision = $nivelRevision;
        $tabla->comunica->metodo = 'llenaTablaModificaciones';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];

        //Numero de programa
        $th = new stdClass();
        $th->texto = 'Programa';
        array_push($tr, $th);
        //Número de subprograma
        $th = new stdClass();
        $th->texto = 'Subprograma';
        array_push($tr, $th);
        //Número de proyecto
        $th = new stdClass();
        $th->texto = 'Proyecto';
        array_push($tr, $th);
        //Número de obra actividad
        $th = new stdClass();
        $th->texto = 'Obra/Actividad';
        array_push($tr, $th);
        //Nombre de la obra/actividad
        $th = new stdClass();
        $th->texto = 'Obra/Actividad';
        array_push($tr, $th);
        //Opcion para ver las metas de la obra/actividad
        $th = new stdClass();
        $th->texto = 'Ver/Editar Metas';
        array_push($tr, $th);

        $trbody = [];

        //Número de programa
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        //Número de subprograma
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        //Número de proyecto
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        //Número de obra actividad
        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);
        //Nombre de la obra/actividad
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);

        //Ver
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';
        $td->casos->tipoCondicion = '||';
        if ($esUR) {
            $td->casos->valor = '1,3';
        } elseif ($presupuesto) {
            $td->casos->valor = '8,11,17';
        }
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'gris';
        $td->casos->verdadero->icono = 'fa fa-pencil';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'omo_id';
        $td->casos->verdadero->attr->mme_gpo_id = 'mme_gpo_id';
        $td->casos->verdadero->attr->mme_ur_id = 'mme_ur_id';
        $td->casos->verdadero->attr->mme_id = 'mme_id';
        $td->casos->verdadero->attr->epg_id = 'epg_id';
        $td->casos->verdadero->class = 'addModModificacion btn-xs';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->icono = 'fa fa-eye';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->omo_id = 'omo_id';
        $td->casos->falso->attr->mme_gpo_id = 'mme_gpo_id';
        $td->casos->falso->attr->mme_ur_id = 'mme_ur_id';
        $td->casos->falso->attr->mme_id = 'mme_id';
        $td->casos->falso->attr->epg_id = 'epg_id';
        $td->casos->falso->class = 'addModModificacion btn-xs';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        return $tabla;
    }

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
