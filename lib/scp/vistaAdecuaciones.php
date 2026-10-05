<?php

require_once dirname(__FILE__) . '/vistaScp.php';
class vistaAdecuaciones extends vistasScp
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

        // SECCION UR
        $prmUR_alta = isset($_SESSION[_S]['prm']['S']['1']['1']['7']) &&
            ((
                ($_SESSION[_S]['nvj_id'] == 1 || $_SESSION[_S]['nvj_id'] == 2) &&
            ($_SESSION[_S]['prf_id'] == 'C' || $_SESSION[_S]['prf_id'] == 'A')
            ) || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'T'));
        $prmUR_validacion = isset($_SESSION[_S]['prm']['S']['1']['1']['8']) && $_SESSION[_S]['nvj_id'] == 2 &&
            ($_SESSION[_S]['prf_id'] == 'A' || $_SESSION[_S]['prf_id'] == 'T');
        $prmUR_autorizacion = isset($_SESSION[_S]['prm']['S']['1']['1']['9']) && $_SESSION[_S]['nvj_id'] == 2 &&
            $_SESSION[_S]['prf_id'] == 'T';
        //SECCION RECURSOS HUMANOS
        $prmDRH_alta = isset($_SESSION[_S]['prm']['S']['1']['1']['82']);
        $prmDRH_validacion = isset($_SESSION[_S]['prm']['S']['1']['1']['83']);
        $prmDRH_autorizacion = isset($_SESSION[_S]['prm']['S']['1']['1']['84']);
        //SECCION SEFIN
        $prmSEFIN_alta = isset($_SESSION[_S]['prm']['S']['1']['1']['14']) && $_SESSION[_S]['nvj_id'] >= 3;
        $prmSEFIN_revision = isset($_SESSION[_S]['prm']['S']['1']['1']['15']) && $_SESSION[_S]['nvj_id'] >= 3;
        $prmSEFIN_validacion = isset($_SESSION[_S]['prm']['S']['1']['1']['16']) && $_SESSION[_S]['nvj_id'] >= 3;
        $prmSEFIN_autorizacion = isset($_SESSION[_S]['prm']['S']['1']['1']['17']) && $_SESSION[_S]['nvj_id'] >= 3;

        $prmPlaneacion = '';
        $html = '';

        switch ($tipo) {
            case 'menuPrincipal':
                $cards = [];
                $tituloUr = $prmDRH_alta ? 'DRH' : 'UR';

                if ($prmUR_alta || $prmDRH_alta) {
                    $c = new stdClass();
                    $c->id = 'altafoliour';
                    $c->class = 'altafoliour';
                    $c->titulo = $tituloUr;
                    $c->img = 'img/Alta_Folio.png';
                    $c->label = 'Alta de Folios';
                    $c->title = 'Alta de Folios';
                    $cards[] = $c;
                }

                if ($prmUR_validacion || $prmDRH_validacion) {
                    $c = new stdClass();
                    $c->id = 'validafoliour';
                    $c->class = 'validafoliour';
                    $c->titulo = $tituloUr;
                    $c->img = 'img/Validacion_Folio.png';
                    $c->label = 'Validación de Folios';
                    $c->title = 'Validación de Folios';
                    $cards[] = $c;
                }

                if ($prmUR_autorizacion || $prmDRH_autorizacion) {
                    $c = new stdClass();
                    $c->id = 'autorizafoliour';
                    $c->class = 'autorizafoliour';
                    $c->titulo = $tituloUr;
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
                $nuevoFolio->class = 'me-1';
                $nuevoFolio->icon = 'fa-solid fa-plus me-2';
                $nuevoFolio->color = 'azul';
                return $nuevoFolio;
                break;
            case 'guardarFolio':
                $btnGuardarFolio = new stdClass();
                $btnGuardarFolio->id = 'btnGuardarFolio';
                $btnGuardarFolio->icon = 'fa-solid fa-floppy-disk me-2';
                $btnGuardarFolio->class = 'me-1';
                $btnGuardarFolio->color = 'verde';
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
                $btnRegresarIndex->class = 'me-1';
                $btnRegresarIndex->color = 'gris';
                $btnRegresarIndex->text = 'Regresar';
                $btnRegresarIndex->value = 'Regresar';
                return $btnRegresarIndex;
                break;
            case 'historial':
                $btnHistorial = new stdClass();
                $btnHistorial->id = 'btnHistorial';
                $btnHistorial->class = 'me-1';
                $btnHistorial->text = 'Historial';
                $btnHistorial->icon = 'fa fa-list-alt me-2';
                $btnHistorial->color = 'gris';
                $btnHistorial->value = 'Historial';
                return $btnHistorial;
                break;
            case 'nuevoMovimiento':
                $nuevaModificacion = new stdClass();
                $nuevaModificacion->id = 'addModificacion';
                $nuevaModificacion->class = 'btn-test addModificacion me-1';
                $nuevaModificacion->icon = 'fa-solid fa-plus me-2';
                $nuevaModificacion->color = 'azul';
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
                $btnEliminarMovimientos->class = 'btn btn-outline-danger me-1';
                $btnEliminarMovimientos->icon = 'fa-solid fa-trash me-2';
                $btnEliminarMovimientos->text = 'Eliminar movimientos';
                $btnEliminarMovimientos->value = 'Eliminar movimientos';
                $btnEliminarMovimientos->color = 'rojo';
                return $btnEliminarMovimientos;
                break;
            case 'validar':
                $btnValidarSEFIN = new stdClass();
                $btnValidarSEFIN->id = 'btnValidarSEFIN';
                $btnValidarSEFIN->class = 'btn-outline-secondary me-1 btnValidarSEFIN';
                $btnValidarSEFIN->icon = 'fa-solid fa-check me-2';
                $btnValidarSEFIN->color = 'verde';
                $btnValidarSEFIN->text = 'Validar';
                $btnValidarSEFIN->value = 'Validar';
                return $btnValidarSEFIN;
                break;
            case 'rechazar':
                $btnRechazarSEFIN = new stdClass();
                $btnRechazarSEFIN->id = 'btnRechazarSEFIN';
                $btnRechazarSEFIN->class = 'btn-outline-secondary me-1 btnRechazarSEFIN';
                $btnRechazarSEFIN->icon = 'fa-solid fa-xmark me-2';
                $btnRechazarSEFIN->color = 'rojo';
                $btnRechazarSEFIN->text = 'Rechazar';
                $btnRechazarSEFIN->value = 'Rechazar';
                return $btnRechazarSEFIN;
                break;
            case 'cargaMasiva':
                $btncargamasiva = new stdClass();
                $btncargamasiva->id = 'btn_cargaMasiva';
                $btncargamasiva->name = 'btn_cargaMasiva';
                $btncargamasiva->value = 'Carga Masiva';
                $btncargamasiva->text = 'Carga Masiva';
                $btncargamasiva->class = 'me-1';
                $btncargamasiva->icon = 'fa-solid fa-shuffle me-2';
                $btncargamasiva->color = 'gris';
                return $btncargamasiva;
                break;
            case 'guardarModificacion':
                $btnGuardarModificacion = new stdClass();
                $btnGuardarModificacion->id = 'btnGuardarModificacion';
                $btnGuardarModificacion->class = 'btn-test btn-outline-success me-1';
                $btnGuardarModificacion->icon = 'fasolid fa-floppy-disk me-2';
                $btnGuardarModificacion->text = 'Guardar';
                $btnGuardarModificacion->type = 'submit';
                $btnGuardarModificacion->value = 'Guardar';
                $btnGuardarModificacion->form = 'Modificaciones';
                return $btnGuardarModificacion;
                break;
            case 'exportaExcel':
                $btnExportaExcel = new stdClass();
                $btnExportaExcel->id = 'btn_excel';
                $btnExportaExcel->class = 'me-1';
                $btnExportaExcel->color = 'verde';
                $btnExportaExcel->icon = 'fa-solid fa-file-excel me-2';
                $btnExportaExcel->text = 'Excel';
                $btnExportaExcel->value = 'Excel';
                return $btnExportaExcel;
                break;
            case 'exportaPdf':
                $btnExportaPdf = new stdClass();
                $btnExportaPdf->id = 'btn_pdf';
                $btnExportaPdf->class = 'me-1';
                $btnExportaPdf->color = 'rojo';
                $btnExportaPdf->icon = 'fa fa-file-pdf me-2';
                $btnExportaPdf->text = 'Pdf';
                $btnExportaPdf->value = 'PDF';
                return $btnExportaPdf;
                break;
        }
    } // Fin vwBotones

    public function vwInput($nombre, $mod, $esUR = false)
    {
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);

        if (!isset($nombre)) {
            return false;
        }
        switch ($nombre) {
            case 'numeroFolio':
                $numeroFolio = new stdClass();
                $numeroFolio->id = 'folio';
                $numeroFolio->formato = 'entero';
                $numeroFolio->enDiv = true;
                $numeroFolio->maxlength = 6;
                $numeroFolio->label = 'Número de Folio';
                $numeroFolio->required = true;
                if ($mod->getMod_id() != '') {
                    $numeroFolio->readonly = true;
                }
                $numeroFolio->value = $mod->getMod_id();
                if (!$esUR) {
                    $numeroFolio->validaAjax = 'folio';
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
                $justificacion->maxlength = '500';
                $justificacion->required = true;
                $justificacion->texto = $mod->getConcepto();
                return $justificacion;
                break;
            case 'fecha':
                $fecha = new stdClass();
                $fecha->id = 'fechaFolio';
                $fecha->label = 'Fecha de creación';
                $fecha->enDiv = true;
                $fecha->required = true;
                $fecha->value = $mod->getFechaFolio();
                if ($mod->getFechaFolio() != '') {
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
                $oficioSolicitud->value = $mod->getOficioSolicitudSefin();
                return $oficioSolicitud;
                break;
            case 'oficioAutorizacion':
                $oficioAutorizacion = new stdClass();
                $oficioAutorizacion->id = 'OficioSefin';
                $oficioAutorizacion->label = 'Oficio de Autorización SEFIN';
                $oficioAutorizacion->enDiv = true;
                $oficioAutorizacion->maxlength = '50';
                $oficioAutorizacion->required = true;
                $oficioAutorizacion->value = $mod->getOficioSefin();
                return $oficioAutorizacion;
                break;
            case 'importeaDistribuir':
                $importeaDistribuir = new stdClass();
                $importeaDistribuir->id = 'importe';
                $importeaDistribuir->label = 'Importe a distribuir';
                $importeaDistribuir->formato = 'moneda';
                $importeaDistribuir->class = 'validaTotMovimiento';
                $importeaDistribuir->form = 'Modificaciones';
                $importeaDistribuir->required = true;
                return $importeaDistribuir;
                break;
            case 'importePorDistribuir':
                $importePorDistribuir = new stdClass();
                $importePorDistribuir->id = 'importeb';
                $importePorDistribuir->label = 'Saldo por distribuir';
                $importePorDistribuir->formato = 'moneda';
                $importePorDistribuir->disabled = 'disabled';
                $importePorDistribuir->form = 'Modificaciones';
                return $importePorDistribuir;
                break;
        }
    }

    public function vwCombo($nombre, $mod, $origenOp, $origen)
    {
        if (!isset($nombre)) {
            return false;
        }

        switch ($nombre) {
            case 'modalidadAdecuacion':
                $modalidadModificacion = new stdClass();
                $modalidadModificacion->id = 'mmd_id';
                $modalidadModificacion->label = 'Modalidad de Adecuación';
                $modalidadModificacion->selecciona = ($origenOp->getMmd_id() != '') ? $origenOp->getMmd_id() : 'E';
                $modalidadModificacion->required = true;
                $modalidadModificacion->search = true;
                $modalidadModificacion->value = $origenOp->getMmd_id();
                if ($mod->getMod_id() != '') {
                    $modalidadModificacion->disabled = true;
                }
                if ($origen == 'A') {
                    $modalidadModificacion->sql = 'SELECT mmd_id,mmd_nombre from cat_ModalidadModificacion';
                } else {
                    $modalidadModificacion->sql = 'SELECT mmd_id,mmd_nombre
                        FROM cat_ModalidadModificacion
                        WHERE mmd_id = "E"';
                }
                return $modalidadModificacion;
                break;
            case 'unidadEjecutora':
                if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {
                    $comboUe = new stdClass();
                    $comboUe->id = 'mod_ue_id';
                    $comboUe->label = 'Unidad Ejecutora';
                    $comboUe->sql = 'SELECT u.ue_id,u.ue_nombre FROM cat_UnidadEjecutora u
                        INNER JOIN presupuesto p USING(gpo_id,ur_id,ue_id)
                        WHERE u.gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' and u.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                        ' . (($mod->getMod_id() != '') ? 'AND u.ue_id = ' . $mod->getMod_ue_id() . '' : '') . '
                        GROUP BY u.gpo_id,u.ur_id,u.ue_id ORDER BY u.ue_id;';
                    $comboUe->required = true;
                } else {
                    $comboUe = '';
                }
                return $comboUe;
                break;
            case 'tipoAdecuacion':
                $tipoModificacion = new stdClass();
                $tipoModificacion->id = 'tmd_id';
                $tipoModificacion->label = 'Tipo de Adecuación';
                $tipoModificacion->camposVal = 'tmd_id';
                $tipoModificacion->camposText = 'tmd_nombre';
                $tipoModificacion->comoAtr = 'omo_id,tmd_id,mmd_id,tfm_id,enc_oop';
                $tipoModificacion->selecciona = $origenOp->getTmd_id();
                $tipoModificacion->required = true;
                $tipoModificacion->search = true;
                $tipoModificacion->value = $origenOp->getTmd_id();
                return $tipoModificacion;
                break;
            case 'tipoFolio':
                $tipoFolio = new stdClass();
                $tipoFolio->id = 'tfm_id';
                $tipoFolio->label = 'Tipo de Folio';
                $tipoFolio->camposVal = 'tfm_id';
                $tipoFolio->camposText = 'tfm_nombre';
                $tipoFolio->comoAtr = 'omo_id,tmd_id,mmd_id,tfm_id,enc_oop';
                $tipoFolio->selecciona = $origenOp->getTfm_id();
                $tipoFolio->required = true;
                $tipoFolio->search = true;
                $tipoFolio->value = $origenOp->getTfm_id();
                return $tipoFolio;
                break;
            case 'subsecretaria':
                $subsecretaria = new stdClass();
                $subsecretaria->id = 'Subsecretaria';
                $subsecretaria->label = 'Subsecretaría';
                $subsecretaria->value = $mod->getSubsecretaria();
                $subsecretaria->required = true;
                return $subsecretaria;
                break;
            case 'tipoMovimiento':
                $tipoM = new stdClass();
                $tipoM->id = 'mov';
                $tipoM->label = 'Tipo Movimiento';
                $tipoM->required = true;
                $tipoM->opciones = ['A' => 'AMPLIACIÓN', 'R' => 'REDUCCIÓN'];
                $tipoM->selecciona = $mod->getTipoMovimiento();
                return $tipoM;
                break;
        }
    } // Fin vwCombo

    public function vwToolsTablaFolios($prm)
    {
        if (!isset($prm->mostrar)) {
            return false;
        }
        if ($prm->mostrar == 'ur' && !isset($prm->parametrosExtra->objUR)) {
            return false;
        }

        switch ($prm->mostrar) {
            case 'cvefin':
                $cveFin = new stdClass();
                $cveFin->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
                $cveFin->ur_id = $_SESSION[_S]['clasAdm-ur'];
                $cveFin->filtro = 'presupuesto';
                $cveFin->ecf_id = isset($prm->parametrosExtra->prm->ecf_id) && $prm->parametrosExtra->prm->ecf_id != '' ? $prm->parametrosExtra->prm->ecf_id : '';
                $cveFin->selecciona = isset($prm->parametrosExtra->prm->ecf_id) && $prm->parametrosExtra->prm->ecf_id != '' ? $prm->parametrosExtra->prm->ecf_id : '';
                return $cveFin;
                break;
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
    } // Fin vwToolsTablaFolios

    public function vwMensajeRechazo(string $mensaje): string
    {
        if (is_string($mensaje) && !empty($mensaje)) {
            return '<div class="alert warning bottom">Mensaje de rechazo:
                ' . $mensaje . '<br/></div>';
        }
        return '';
    } // Fin vwMensajeRechazo

    public function vwCargaFormulario($tipo)
    {
        if (!isset($tipo)) {
            return false;
        }

        switch ($tipo) {
            case 'Folio':
                //Ariadna: Quité el formulario para mostrar Datos Generales del folio
                // porque al parecer no se utiliza ya que la información
                //de los folios los muestra desde el ControladorRegistroModificaciones
                break;
        }
    } // Fin vwCargaFormulario

    public function notificacion($tipo, $fechas = [])
    {
        switch ($tipo) {
            case 'bloqueo':
                $mensaje = '
                    <div class="panel panel-info">
                        <div class="panel-heading">Folio Bloqueado</div>
                        <div class="panel-body">
                            Se les comunica a los Ejecutores de gasto, que si no visualizan el botón "Enviar a revisión" y
                            muestra la leyenda "FOLIO BLOQUEADO" deberá comunicarse con su analista para solicitar
                            la apertura del mismo.
                        </div>
                    </div>';
                return $mensaje;
                break;
            case 'alerta':
                $fechaActual = date('Y-m-d');

                if (count($fechas) > 0) {
                    if (in_array($fechaActual, $fechas)) {
                        $mensaje = '
                            <div class="panel panel-info">
                                <div class="panel-heading">Vigencia de Folios de Modalidad Interna</div>
                                <div class="panel-body">
                                    Se les comunica a los Ejecutores de gasto, que los folios de modificaciones internas,
                                    deberán concluir su proceso de autorización durante el bimestre que fueron capturados,
                                    <br/>en caso contrario, deberán ser eliminados del sistema.
                                </div>
                            </div><br/>';
                        return $mensaje;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                break;
        }
    } // Fin notificacion

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
        $td->attr->mod_gpo_id = 'mod_gpo_id';
        $td->attr->mod_ur_id = 'mod_ur_id';
        $td->class = 'verUr btn-xs';
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        array_push($trbody, $td);
        //Clave ur
        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($trbody, $td);
        //Clave nombre
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);
        //Numero de folios
        // SI ES SEFIN ENTONCES MUESTRA EL NUMERO DE FOLIOS PENDIENTES DE URS
        if ($esSefin) {
            $td = new stdClass();
            $td->campoTxt = 'num_folios';
            array_push($trbody, $td);
        }
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        return $tabla;
    } // Fin vwTablaUrs

    public function vwTipoAdecuacion($quien, $prm, $mod)
    {
        $origenOperacion = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $sql = '';
        $params = [];
        switch ($quien) {
            case 'ur':
                $params = [];
                $sql = '
                    SELECT tm.tmd_id,min(tm.tmd_nombre)tmd_nombre,min(o.omo_id)omo_id,min(o.tfm_id)tfm_id,min(o.mmd_id)mmd_id,min(o.enc_oop)enc_oop
                    FROM cat_TipoModificacion tm
                    INNER JOIN enc_OrigenOperacion o USING(tmd_id)
                    WHERE o.mmd_id= :mmd
                    AND o.omo_id= :omo
                    GROUP BY tmd_id
                    ORDER BY tmd_orden;';
                $params = [':mmd' => (isset($prm->mmd_id) && !empty($prm->mmd_id) ? $prm->mmd_id : 'E'),
                    ':omo'        => (isset($prm->omo_id) && !empty($prm->omo_id) ? $prm->omo_id : 'A')];
                break;
            case 'sefin':
                $params = [];
                $sql = '
                    SELECT tm.tmd_id,tm.tmd_id,min(tm.tmd_nombre)tmd_nombre,min(o.omo_id)omo_id,min(o.tfm_id)tfm_id,min(o.mmd_id)mmd_id,min(o.enc_oop)enc_oop
                    FROM cat_TipoModificacion tm
                    INNER JOIN enc_OrigenOperacion o USING(tmd_id)
                    WHERE o.mmd_id= :mmd
                    AND o.omo_id= :omo
                    GROUP BY tmd_id
                    ORDER BY tmd_orden;';
                $params = [':mmd' => (isset($prm->mmd_id) && !empty($prm->mmd_id) ? $prm->mmd_id : 'E'),
                    ':omo'        => (isset($prm->omo_id) && !empty($prm->omo_id) ? $prm->omo_id : 'A')];
                break;
        }
        $comboTipoAdecuacion = $this->vwCombo('tipoAdecuacion', $mod, $origenOperacion, $origenOperacion->getOmo_id());
        $comboTipoAdecuacion->sql = $sql;
        $comboTipoAdecuacion->params = $params;
        if ($origenOperacion->getTmd_id() != '' && isset($prm->deshabilitado)) {
            $comboTipoAdecuacion->disabled = true;
        }
        return $comboTipoAdecuacion;
    } // Fin vwTipoAdecuacion

    public function vwTipoFolio($quien, $prm, $mod)
    {
        $sql = '';
        $params = [];
        $origenOperacion = new OrigenOperacion(['enc_oop' => $mod->getOop()]);

        switch ($quien) {
            case 'ur':
                $params = [];
                $sql = '
                    SELECT m.tfm_id,min(m.tfm_nombre) tfm_nombre,min(o.omo_id) omo_id,min(o.tmd_id) tmd_id,min(o.mmd_id) mmd_id,min(o.enc_oop) enc_oop
                    FROM cat_TipoFolioModificacion m
                    INNER JOIN enc_OrigenOperacion o USING(tfm_id)
                    WHERE o.mmd_id= :mmd
                    AND o.omo_id= :omo
                    ' . (isset($prm->tmd_id) ? '
                    AND tmd_id="' . $prm->tmd_id . '"' : '') . '
                    GROUP BY tfm_id;';
                $params = [
                    ':mmd' => (isset($prm->mmd_id) && !empty($prm->mmd_id) ? $prm->mmd_id : 'E'),
                    ':omo' => (isset($prm->omo_id) && !empty($prm->omo_id) ? $prm->omo_id : 'A'),
                ];
                break;
            case 'sefin':
                $params = [];
                $sql = '
                    SELECT m.tfm_id,min(m.tfm_nombre) tfm_nombre,min(o.omo_id) omo_id,min(o.tmd_id) tmd_id,min(o.mmd_id) mmd_id,min(o.enc_oop) enc_oop
                    FROM cat_TipoFolioModificacion m
                    INNER JOIN enc_OrigenOperacion o USING(tfm_id)
                    WHERE o.mmd_id= :mmd
                    ' . (isset($prm->tmd_id) ? '
                    AND o.tmd_id="' . $prm->tmd_id . '"' : '') . '
                    ' . (isset($prm->omo_id) ? '
                    AND omo_id="' . $prm->omo_id . '"' : '') . '
                    GROUP BY tfm_id;';
                $params = [':mmd' => (isset($prm->mmd_id) && !empty($prm->mmd_id) ? $prm->mmd_id : 'E')];
                break;
        }
        $comboTipoFolio = $this->vwCombo('tipoFolio', $mod, $origenOperacion, $origenOperacion->getOmo_id());
        $comboTipoFolio->sql = $sql;
        $comboTipoFolio->params = $params;
        return $comboTipoFolio;
    } // Fin vwTipoFolio

    public function vwSubsecretaria($quien, $prm, $mod)
    {
        $subsecretaria = '';
        $origenOperacion = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $subsecretaria = $this->vwCombo('subsecretaria', $mod, $origenOperacion, $origenOperacion->getOmo_id());

        switch ($quien) {
            case 'ur':
                $subsecretaria->opciones = [1 => 'SUBSECRETARÍA DE EGRESOS', 2 => 'SUBSECRETARÍA DE PLANEACIÓN'];
                $subsecretaria->value = $mod->getSubsecretaria();
                $subsecretaria->selecciona = $mod->getSubsecretaria();
                break;
            case 'presupuesto':
                $subsecretaria->opciones = [1 => 'SUBSECRETARÍA DE EGRESOS', 2 => 'SUBSECRETARÍA DE PLANEACIÓN'];
                $subsecretaria->value = 1;
                $subsecretaria->disabled = true;
                $subsecretaria->selecciona = $mod->getSubsecretaria() != '' ? $mod->getSubsecretaria() : 1;
                break;
            case 'planeacion':
                $subsecretaria->opciones = [2 => 'SUBSECRETARÍA DE PLANEACIÓN'];
                $subsecretaria->value = 2;
                $subsecretaria->selecciona = $mod->getSubsecretaria() != '' ? $mod->getSubsecretaria() : 2;
                break;
        }
        if ($mod->getSubsecretaria() != '' && isset($prm->deshabilitado)) {
            $subsecretaria->disabled = true;
        }
        return $subsecretaria;
    } // Fin vwSubsecretaria

    public function vwInformacionFolio($mod, $objMov = '')
    {
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);
        $html = '
            <div class="row">
                ' . ($objMov != '' ?
            '<fieldset class="row col-md-12 col-xs-12">
                    <legend>Información del Folio</legend>' : '') . '
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                    <label> Número de Folio: ' . $mod->getMod_id() . '</label>
                </fieldset>
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                    <label>Modalidad de Adecuación: ' . ($enc_oop->getMmd_id() == 'E' ? 'EXTERNAS' : 'INTERNAS') . '</label>
                </fieldset>
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                    <label>Tipo de Adecuación: ' . $tipoMod->getNombre() . '</label>
                </fieldset>
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : '') . '">
        ';
        $html .= '
                </fieldset>
                    ' . ($objMov != '' ?
            '</fieldset>' : '') . '
            </div>
            <div class="row">
                <fieldset id="Mensaje" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
            <div id="CargaMasiva"></div>
        ';
        return $html;
    } // Fin vwInformacionFolio

    public function vwInformacionFolioRegistro($mod, $objMov = '')
    {
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);

        $html = '
            <div class="row">
                ' . ($objMov != '' ?
            '<fieldset class="row col-md-12 col-xs-12">
                    <legend>Información del Folio</legend>' : '') . '
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-2 col-xs-2' : 'col-md-2 col-xs-2') . '">
                    <label> Número de Folio: ' . $mod->getMod_id() . '</label>
                </fieldset>
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-2 col-xs-2' : 'col-md-2 col-xs-2') . '">
                    <label>Modalidad de Adecuación: ' . ($enc_oop->getMmd_id() == 'E' ? 'EXTERNAS' : 'INTERNAS') . '</label>
                </fieldset>
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">
                    <label>Tipo de Adecuación: ' . $tipoMod->getNombre() . '</label>
                </fieldset>
                <fieldset class="' . ($enc_oop->getTmd_id() == 'B' ? 'col-md-3 col-xs-3' : 'col-md-3 col-xs-3') . '">';

        if ($objMov != '') {
            $html .= '<label>Tipo de movimiento: ' . $objMov . '<label>';
        } else {
            $html .= '
                <label>Tipo movimiento: ' . ($mod->getTipoMovimiento() == 'A' ? 'AMPLIACIÓN' : 'REDUCCIÓN') . '</label>';
        }

        $html .= '
                </fieldset>
                    ' . ($objMov != '' ?
            '</fieldset>' : '') . '
            </div>
            <div class="row">
                <fieldset id="Mensaje" class="col-md-12 col-xs-12">
                </fieldset>
            </div>
            <div id="CargaMasiva"></div>';
        return $html;
    } // Fin vwInformacionFolioRegistro

    public function vwTablaMovimientos($mod, $nivelRevision, $esUR = false, $planeacion = false, $presupuesto = false, $pantalla = '')
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl-Modificaciones';
        $tabla->titulo = 'Adecuaciones presupuestales ingresadas por folio';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $pantalla;
        $tabla->comunica->omo_id = $mod->getOmo_id();
        $tabla->comunica->mod_gpo_id = $mod->getMod_gpo_id();
        $tabla->comunica->mod_ur_id = $mod->getMod_ur_id();
        $tabla->comunica->mod_id = $mod->getMod_id();
        $tabla->comunica->nivelRevision = $nivelRevision;
        $tabla->comunica->metodo = 'llenaTablaModificaciones';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];
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
        $td->casos = new stdClass();
        $td->casos->campo = 'emo_id';
        $td->casos->tipoCondicion = '||';
        if ($esUR) {
            $td->casos->valor = '1,3';
        } else {
            if ($planeacion) {
                $td->casos->valor = '6,11,17';
            }
            if ($presupuesto) {
                $td->casos->valor = '8,11,17';
            }
        }
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'cielo';
        $td->casos->verdadero->icono = 'pencil';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->omo_id = 'omo_id';
        $td->casos->verdadero->attr->mod_gpo_id = 'mod_gpo_id';
        $td->casos->verdadero->attr->mod_ur_id = 'mod_ur_id';
        $td->casos->verdadero->attr->mod_id = 'mod_id';
        $td->casos->verdadero->attr->gpo_id = 'gpo_id';
        $td->casos->verdadero->attr->ur_id = 'ur_id';
        $td->casos->verdadero->attr->ue_id = 'ue_id';
        $td->casos->verdadero->attr->epg_id = 'epg_id';
        $td->casos->verdadero->attr->pg_id = 'pg_id';
        $td->casos->verdadero->attr->pe_id = 'pe_id';
        $td->casos->verdadero->attr->ecf_id = 'ecf_id';
        $td->casos->verdadero->attr->tcp_id = 'tcp_id';
        $td->casos->verdadero->class = 'addModModificacion btn-xs';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->icono = 'eye';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->omo_id = 'omo_id';
        $td->casos->falso->attr->mod_gpo_id = 'mod_gpo_id';
        $td->casos->falso->attr->mod_ur_id = 'mod_ur_id';
        $td->casos->falso->attr->mod_id = 'mod_id';
        $td->casos->falso->attr->gpo_id = 'gpo_id';
        $td->casos->falso->attr->ur_id = 'ur_id';
        $td->casos->falso->attr->ue_id = 'ue_id';
        $td->casos->falso->attr->epg_id = 'epg_id';
        $td->casos->falso->attr->pg_id = 'pg_id';
        $td->casos->falso->attr->pe_id = 'pe_id';
        $td->casos->falso->attr->ecf_id = 'ecf_id';
        $td->casos->falso->attr->tcp_id = 'tcp_id';
        $td->casos->falso->class = 'addModModificacion btn-xs';
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
        return $tabla;
    } // Fin vwTablaMovimientos

    public function vwGrafica1($pre, $meses)
    {
        $vig = $com = $dev = $data = $drilldown = $vigente = $comprometido = $devengado = '';
        $data .= "
            { name: 'Vigente Anual', y: " . $pre->getVigente() . ", drilldown: 'Vigente Anual' },
            { name: 'Comprometido Anual', y: " . ($pre->getComprometido()) . ", drilldown: 'Comprometido Anual'},
            { name: 'Devengado Anual', y: " . ($pre->getDevengado()) . ", drilldown: 'Devengado Anual'}
        ";

        $vigente = $pre->getMesVigente()->getArrayMeses();
        $comprometido = $pre->getMesComprometido()->getArrayMeses();
        $devengado = $pre->getMesDevengado()->getArrayMeses();

        //DATOS
        foreach ($vigente as $key => $val) {
            $vig .= " [ '" . $meses[$key] . "' , " . $val . ' ],';
        }
        foreach ($comprometido as $key => $val) {
            $com .= " [ '" . $meses[$key] . "' , " . $val . ' ],';
        }
        foreach ($devengado as $key => $val) {
            $dev .= " [ '" . $meses[$key] . "' , " . $val . ' ],';
        }
        //DRILLDOWN
        $drilldown = "
            { name: 'Vigente Anual',id: 'Vigente Anual', data: [ " . $vig . " ] },
            { name: 'Comprometido Anual',id: 'Comprometido Anual', data: [ " . $com . " ] },
            { name: 'Devengado Anual',id: 'Devengado Anual', data: [ " . $dev . ' ] }
        ';
        $grafica = "
            Highcharts.setOptions({
                lang: {thousandsSep: ','}
            });
            Highcharts.chart('grafica',{
                chart : { type: 'column'},
                colors : [ '#553276','#C43131B','#1F934C'],
                title : { text: '' },
                subtitle: { text: '' },
                xAxis : { type: 'category' },
                yAxis : { title: { text: 'Total Presupuesto' } },
                legend : { enabled: false },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {enabled: true, format: '$ {point.y:,.2f}' }
                    }
                },
                tooltip: {
                    headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                    pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>$ {point.y:,.2f}</b> <br/>'
                },
                series: [{
                    name: 'Importes', colorByPoint: true,
                    data: [ " . $data . ' ]
                }],
                drilldown: {series: [ ' . $drilldown . ' ]}
            });';

        return $grafica;
    } // Fin vwGrafica1

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
                    <td>' . utf8Encode($valor['mensaje']) . '</td>
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
            </table>';

        return $html;
    } // Fin vwMuestraTablaError
}
