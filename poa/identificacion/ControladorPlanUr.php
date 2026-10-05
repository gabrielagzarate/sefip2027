<?php

/**
 * Description of ControladorPlanUr
 *
 * @author ?
 * Collaborators : Marcos,@JaphetMata
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Migrate: 06-Mayo-2026
 * Comment: -----
 * Objective: Registrar la Planificación Institucional de la Unidad Responsable.
 * Regla de Negocio:
 * Permits:
1. lo que tengan permiso a ésta opción podrá verlos.
2. Cualquier usuario de nivel UR, o cualquier usuario que tenga permisos de escritura en ésta pantalla, podrá grabar cambios
3. Un usuario de nivel >= 4 (Analista) Y QUE TENGA permisos de Validación (3) podrá autorizar en finanzas la Planeación UR
4. Permisos de Acceso a la Opción
 * Lectura: El permiso minimo que deben tener todos los usuario para que por lo menos puedan instanciar la clase.
 * */

ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../lib/poa/identificacion/ValidacionContactoUrUe.php';

class ControladorPlanUr extends WebResponse
{
    // Claves de UR (CONCAT(gpo_id,ur_id)) exentas de campos obligatorios en la
    // identificación institucional — Ticket #SDS-559336 (116, 901 y 902).
    const UR_EXCEPCION_OBLIGATORIOS = [116, 901, 902];
    protected $nvj_id;
    protected $clasAdm_gpo;
    protected $clasAdm_ur;
    protected $gpo_id;
    protected $ur_id;
    protected $usr_id;
    protected $ur;
    protected $escritura;
    protected $autorizacion;
    protected $CambiosActivos;
    protected $exceptaObligatoriosUe;
    public $sql;
    public $params;
    public $sql2;

    public function __construct()
    {
        parent::__construct();

        $this->nvj_id = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->clasAdm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : false;
        $this->clasAdm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : false;
        $this->gpo_id = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : false;
        $this->ur_id = isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : false;
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;

        // Excepción Ticket #SDS-559336: las UR de municipios/normativa (116, 901, 902)
        // están exentas de la información obligatoria de la UE. La clave de UR es la
        // concatenación gpo_id.ur_id (igual que CONCAT(gpo_id,ur_id)).
        $this->exceptaObligatoriosUe = in_array((int) ($this->clasAdm_gpo . $this->clasAdm_ur), self::UR_EXCEPCION_OBLIGATORIOS, true);

        //Instanciando la Ur
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->clasAdm_gpo);
        $this->ur->setUr_id($this->clasAdm_ur);
        $this->ur->select();

        // Si es un usuario de nivel UR, o tiene permisos de escritura, puede grabar cambios a ésta pantalla
        $this->escritura = (($this->nvj_id == 2) or (isset($_SESSION[_S]['prm']['A']['1']['1']['2'])));

        // SI EL USUARIO ES DE NIVEZL JERARQUICO >= 4 (ANALISTA) Y TIENE PERMISOS DE VALIDACIÓN (3) ENTONCES
        // PODRÁ AUTORIZAR LA PLANIFICACIÓN UR
        $this->autorizacion = (($this->nvj_id >= 3) && (isset($_SESSION[_S]['prm']['A']['1']['1']['3'])));

        // AUTORIZACIÓN PARA MODIFICAR LA PLANEACIÓN UR
        // VALIDAMOS PARA USUARIOS DE NIVEL UE Y UR
        if (($this->nvj_id < 2)) {
            $this->CambiosActivos = (isset($_SESSION[_S]['prmUR']['A']['1']['1']));
        } else {
            // USUARIOS DE ANALISTAS PARA ARRIBA SIEMPRE TENDRÁN HABILITADOS LOS CAMBIOS
            $this->CambiosActivos = true;
        }

        switch ($this->metodo) {
            case 'upload':
                if ($this->CambiosActivos) {
                    $this->upload();
                }

                break;
            case 'enviar':
                if ($this->CambiosActivos) {
                    $this->enviar();
                }

                break;
            case 'guardar':
                $this->guardar();
                break;
            case 'autorizar':
                if ($this->CambiosActivos) {
                    $this->autorizar();
                }

                break;
            case 'rechazar':
                if ($this->CambiosActivos) {
                    $this->rechazar();
                }

                break;
            case 'desvalidar':
                if ($this->CambiosActivos) {
                    $this->desvalidar();
                }

                break;
            case 'addComentario':
                if ($this->CambiosActivos) {
                    $this->addComentario();
                }

                break;
            case 'verHistorial':
                $this->verHistorial();
                break;
            case 'llenaHistorial':
                $this->llenaHistorial();
                break;
            case 'uploadFirma':
                $this->uploadFirma();
                break;
            case 'verHistorialArchivo':
                $this->verHistorialArchivo();
                break;
            case 'download':
                $this->download();
                break;
            case 'upload':
                $this->upload();
                break;
            case 'validaMandatoMisional':
                $this->validaMandatoMisional();
                break;
            case 'bloquearUsuario':
                $this->bloquearUsuario();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Función principal.   Dibujado de pantalla
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function main()
    {
        $this->mixScript('$("#desvalida").hide()');
        $this->mixStyle('
            legend{font-size:18px!important;margin-bottom:10px!important}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:white; opacity: 0.8;};
        ');
        $this->mixScript('
        $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
        $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );');
        $clasAdminScript = ClasificacionAdministrativa::onChangeUr('comunica( "' . $this->pantalla . '");');
        if ($_SESSION[_S]['prm']['A']['1']['1']) {
            $this->mixScript('$("#techos").hide();');
        }
        $this->mixScript($clasAdminScript);
        // Obtenemos el status de la UR
        $sql = 'SELECT ur.eur_id, eur.eur_nombre, eur.eur_color FROM ' . BD_CTRL . '.cat_UnidadResponsable ur
            INNER JOIN ' . BD_CTRL . '.cat_EstadoUr eur USING( eur_id )
            WHERE ur.gpo_id = ?
            AND ur.ur_id = ? ;';
        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $aStatus = $this->extraer();
        $this->desconectar();

        $status = $aStatus['eur_id'];
        $nombreStatus = $aStatus['eur_nombre'];
        $colorStatus = $aStatus['eur_color'];
        $botones = '';
        $desvalidar = '';

        // CHECAR: permite guardar cuando el status sea mayor o igual a 3
        // ¿ no debería ser igual a 3 solamente ?

        // SI el status es 0, 1 o 3 mostramos el botón grabar
        // Marcos 02/mar/2015 :   El status >= 3 porque en cualquier momento pueden cambiar el nombre de los titulares
        // Por lo tanto el botón de GUARDAR siempre debe estar presente.
        if ($status == 0 || $status == 1 || $status >= 3) {
            // Siempre y cuando tenga permisos de escritura ...
            if ($this->escritura == true) {
                // Y aún este abierta la opción
                // Marcos: 13/jul/2018
                // Según ticket #451486, sólo la UR debe ver el botón Guardar
                if ($this->nvj_id <= 2) {
                    $botones .= <<<HTML
                        <button id="btnguardarplanur" type="button" class="btn btn-outline-success me-1"
                                title="Guardar la Identificación Institucional de la Unidad Responsable">
                            <i class="fa fa-floppy-disk me-2"></i>Guardar
                        </button>
                        HTML;
                }
            }
        }

        // SI el status es 1 mostramos el botón de Enviar a Finanzas para validación
        if ($status == 1) {
            // Siempre y cuando tenga permisos de escritura ...
            if ($this->escritura == true) {
                // Y aún este abierta la opción
                if ($this->CambiosActivos == true) {
                    // Marcos: 13/jul/2018
                    // Según ticket #451486, sólo la UR debe ver el botón Guardar
                    if ($this->nvj_id <= 2) {
                        $botones .= <<<HTML
                            <button id="btnEnviar" type="button" class="btn btn-outline-success me-1" title="Enviar la Identificación Institucional para su validación">
                                <i class="fa fa-paper-plane me-2"></i>Enviar
                            </button>
                            HTML;
                    }
                }
            }
        }

        // SI el status es 2 le permitimos al usuario revisor que autorice la planeacion
        if ($status == 2) {
            // Siempre y cuando tenga permisos de validacion ...
            if ($this->autorizacion == true) {
                // Y aún este abierta la opción
                if ($this->CambiosActivos == true) {
                    $botones .= <<<HTML
                        <button id="autorizarPlanUR" type="button" class="btn btn-outline-success me-1"
                            title="Validar la Identificación Institucional de la Unidad Responsable">
                            <i class="fa fa-circle-check me-2"></i>Validar
                        </button>
                        <button id="rechazarPlanUR"  type="button" class="btn btn-outline-danger me-1"  title="Rechazar la Identificación Institucional de la Unidad Responsable">
                            <i class="fa fa-xmark me-2"></i>Rechazar
                        </button>
                    HTML;
                }
            }
        }

        // SI el status es >= 4 ( autorizada ), ponemos para los validadores un botón para desvalidar
        if ($status >= 4) {
            // Siempre y cuando tenga permisos de validacion ...
            if ($this->autorizacion == true) {
                // Y aún este abierta la opción
                if ($this->CambiosActivos == true) {
                    $desvalidar = <<<HTML
                        <button id="desvalidarPlanUR" type="button" class="btn btn-outline-secondary me-1" title="Desvalidar la Identificación Institucional de la Unidad Responsable">
                            <i class="fa fa-pencil me-2"></i>Desvalidar
                        </button>
                    HTML;
                }
            }
        }
        // Si el status es 2 (enviada para validacion) o >= 4 (autorizada), nadie puede modificar nada
        if ($status == 2 || $status >= 4) {
            $this->escritura = false;
        } else {
            // Y aún este abierta la opción
            if ($this->CambiosActivos == true) {
                $this->escritura = true;
            } else {
                $this->escritura = false;
            }
        }

        // Todos pueden imprimir
        $botones .= '
            <a id="imprimirplanur" href="rep/doc/poa/planeacionInstitucional/rep_PlaneacionUr.php" target="_blank">
                <button class="btn btn-outline-danger me-1" type="button" title="Imprimir la Identificación Institucional de la Unidad Responsable">
                    <i class="fa-solid fa-file-pdf me-2"></i>PDF
                </button>
            </a>';

        // Todos pueden ver el historial
        $botones .= '<button id="verHistorial" type="button" class="btn btn-outline-secondary me-1" title="Ver historial de la '
            . 'Identificación Institucional UR"><i class="fa fa-list-alt"></i></button>';

        // Ponemos el status actual de la Planeación UR
        $botones .= '<span type="text" id="status"
            style="background-color:#' . $colorStatus . '" class="btn me-1 ">' . $nombreStatus . '</span>';

        $this->agregaTools($botones . $desvalidar);

        $tabs = <<<HTML
            <div id="page-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="nav-item first active">
                        <button class="nav-link active" href="ii">Identificación Institucional</button>
                    </li>
                    <li role="presentation" class="nav-item last">
                        <button class="nav-link" href="dt">
                            Domicilio UR </a><div class="last"></div>
                        </button>
                    </li>
                    <li role="presentation" class="nav-item">
                        <button class="nav-link" href="tu">Titular UR</button>
                    </li>
                    <li role="presentation" class="nav-item">
                        <button class="nav-link" href="ta">Titular Administrativo</button>
                    </li>
                    <li role="presentation" class="nav-item">
                        <button class="nav-link" href="tp">Titular Planeación</button>
                    </li>
                    <li role="presentation" class="nav-item">
                        <button class="nav-link" href="te">Responsable de Seguimiento</button>
                    </li>
                </ul>
            </div>
            HTML;

        $this->mixHtml($tabs);
        $ur = $this->ur;
        $inicioPeriodicoOficial = new stdClass();
        $inicioPeriodicoOficial->id = 'inicioPeriodicoOficial';
        $inicioPeriodicoOficial->name = 'inicioPeriodicoOficial';
        $inicioPeriodicoOficial->label = 'Documento legal de creación *';
        $inicioPeriodicoOficial->enDiv = true;
        $inicioPeriodicoOficial->maxlength = '20';
        $inicioPeriodicoOficial->value = $ur->getInicioPeriodicoOficial();

        $inicioFecha = new stdClass();
        $inicioFecha->id = 'inicioFecha';
        $inicioFecha->name = 'inicioFecha';
        $inicioFecha->label = 'Fecha de publicación *';
        $inicioFecha->class = 'datepicker';
        $inicioFecha->enDiv = true;
        $inicioFecha->formato = 'fecha';
        $inicioFecha->value = $ur->getInicioFecha();

        $internoPeriodico = new stdClass();
        $internoPeriodico->id = 'internoPeriodico';
        $internoPeriodico->name = 'internoPeriodico';
        $internoPeriodico->label = 'Periódico oficial *';
        $internoPeriodico->labelClass = '_32 txtL';
        $internoPeriodico->enDiv = true;
        $internoPeriodico->maxlength = '20';
        $internoPeriodico->value = $ur->getInternoPeriodico();

        $modificacionPeriodico = new stdClass();
        $modificacionPeriodico->id = 'modificacionPeriodico';
        $modificacionPeriodico->name = 'modificacionPeriodico';
        $modificacionPeriodico->label = 'Periódico oficial de la última modificación o reforma *';
        $modificacionPeriodico->labelClass = '_42 txtL';
        $modificacionPeriodico->enDiv = true;
        $modificacionPeriodico->maxlength = '30';
        $modificacionPeriodico->value = $ur->getModificacionPeriodico();

        $modificacionFecha = new stdClass();
        $modificacionFecha->id = 'modificacionFecha';
        $modificacionFecha->name = 'modificacionFecha';
        $modificacionFecha->label = 'Fecha de publicación de la última modificación o reforma *';
        $modificacionFecha->labelClass = '_42 txtL';
        $modificacionFecha->enDiv = true;
        $modificacionFecha->formato = 'fecha';
        $modificacionFecha->value = $ur->getModificacionFecha();

        $planeacionFecha = new stdClass();
        $planeacionFecha->id = 'planeacionFecha';
        $planeacionFecha->name = 'planeacionFecha';
        $planeacionFecha->label = 'Fecha de publicación *';
        $planeacionFecha->labelClass = '_42 txtL';
        $planeacionFecha->enDiv = true;
        $planeacionFecha->formato = 'fecha';
        $planeacionFecha->value = $ur->getPlaneacionFecha();

        $validacionFecha = new stdClass();
        $validacionFecha->id = 'validacionFecha';
        $validacionFecha->name = 'validacionFecha';
        $validacionFecha->label = 'Fecha de validación *';
        $validacionFecha->labelClass = '_42 txtL';
        $validacionFecha->enDiv = true;
        $validacionFecha->formato = 'fecha';
        $validacionFecha->value = $ur->getValidacionFecha();

        $domicilioUR = new stdClass();
        $domicilioUR->id = 'domicilioUR';
        $domicilioUR->name = 'domicilioUR';
        $domicilioUR->label = 'Domicilio *';
        $domicilioUR->class = 'input-xxlarge';
        $domicilioUR->enDiv = true;
        $domicilioUR->maxlength = '200';
        $domicilioUR->value = $ur->getDomicilio();

        $telefonoUR = new stdClass();
        $telefonoUR->id = 'telefonoUR';
        $telefonoUR->name = 'telefonoUR';
        $telefonoUR->label = 'Teléfono *';
        $telefonoUR->class = 'input-xxlarge';
        $telefonoUR->enDiv = true;
        $telefonoUR->maxlength = '10';
        $telefonoUR->value = $ur->getTelefono();

        // Marcos: 27/jun/2017
        // Para el 2018, piden adicionar éstos dos campos a la planeación institucional,
        // lo hace Javier Guzmán en el ticket #147351
        $paginaWeb = new stdClass();
        $paginaWeb->id = 'paginaWeb';
        $paginaWeb->name = 'paginaWeb';
        $paginaWeb->label = 'Página Web *';
        $paginaWeb->class = 'input-xxlarge';
        $paginaWeb->enDiv = true;
        $paginaWeb->maxlength = '200';
        $paginaWeb->value = $ur->getPaginaWeb();

        $correoElectronicoContacto = new stdClass();
        $correoElectronicoContacto->id = 'correoElectronicoContacto';
        $correoElectronicoContacto->name = 'correoElectronicoContacto';
        $correoElectronicoContacto->label = 'Correo Electrónico *';
        $correoElectronicoContacto->class = 'input-xxlarge validarEmail';
        $correoElectronicoContacto->enDiv = true;
        $correoElectronicoContacto->maxlength = '100';
        $correoElectronicoContacto->style = 'text-transform: none;';
        $correoElectronicoContacto->value = $ur->getCorreoElectronicoContacto();

        $municipio = new stdClass();
        $municipio->id = 'municipio';
        $municipio->name = 'municipio';
        $municipio->enDiv = true;
        $municipio->mensaje = 'Selecciona una opción';
        $municipio->label = 'Municipio *';
        $municipio->search = true;
        $municipio->sql = 'SELECT mun_id, mun_nombre FROM cat_Municipio WHERE mun_nombre NOT like "%cobertura%" ORDER BY mun_id; ';
        $municipio->selecciona = $ur->getMun_id();
        $municipio->comoAtr = 'mun_id';

        $codigoPostal = new stdClass();
        $codigoPostal->id = 'codigoPostal';
        $codigoPostal->name = 'codigoPostal';
        $codigoPostal->label = 'Código Postal *';
        $codigoPostal->class = 'input-xxlarge';
        $codigoPostal->enDiv = true;
        $codigoPostal->maxlength = '5';
        $codigoPostal->value = ($ur->getCodigoPostal() == 0) ? '' : $ur->getCodigoPostal();

        // Si no es un usuario de nivel UR, o no tiene permisos de escritura, todos los campos estarán marcados como de sólo lectura
        if (! $this->escritura) {

            $inicioFecha->readonly = 'true';
            $modificacionFecha->readonly = 'true';

            $inicioPeriodicoOficial->readonly = 'true';
            $modificacionPeriodico->readonly = 'true';
            $planeacionFecha->readonly = 'true';
            $validacionFecha->readonly = 'true';

            $domicilioUR->readonly = 'true';
            $telefonoUR->readonly = 'true';

            $paginaWeb->readonly = 'true';
            $correoElectronicoContacto->readonly = 'true';
            $municipio->disabled = 'true';
            $codigoPostal->readonly = 'true';

            $pdfDecreto = '
            <div class="control-group">
                <label class="control-label">Documento digital * (formato PDF máximo de 6MB)</label>
                <div class="controls">
                    <a id="decretoArchivo" class="lnk_ext" tabindex=-1 lnk="decreto/' . $ur->getClave() . '_decreto.pdf?npi='
            . rand(1, 99999) . '" target="_blank">' . $ur->getDecretoArchivo() . '</a>
                </div>
            </div>
            ';
            $pdfOrganigrama = '
            <div class="control-group">
                <label class="control-label"> Documento digital (formato PDF máximo de 6MB)*</label>
                <div class="controls">
                    <a id="organigramaArchivo" class="lnk_ext" tabindex=-1 lnk="estructuraorganica/' . $ur->getClave()
            . '_estructuraorganica.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getOrganigramaArchivo() . '</a>
                </div>
            </div>
            ';
            $pdfInterno = '
            <div class="control-group">
                <label class="control-label"> Documento digital (formato PDF máximo de 6MB)*</label>
                <div class="controls">
                    <a id="internoArchivo" class="lnk_ext" tabindex=-1 lnk="interno/' . $ur->getClave() . '_interno.pdf?npi='
            . rand(1, 99999) . '" target="_blank">' . $ur->getInternoArchivo() . '</a>
                </div>
            </div>
            ';
            $pdfLegal = '
            <div class="control-group">
                <label class="control-label"> Documento digital  (formato PDF máximo de 6MB)*</label>
                <div class="controls">

                    <a id="legalArchivo" class="lnk_ext" tabindex=-1 lnk="legal/' . $ur->getClave()
            . '_legal.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getInternoPeriodico() . '</a>
                </div>
            </div>
            ';
        } else {
            // SI ES UN USUARIO DE NIVEL UR, O TIENE PERMISOS DE ESCRITURA ...

            $inicioFecha->class = 'fecha';
            $modificacionFecha->class = 'fecha';

            $pdfDecreto = '
            <div class="control-group">
                <label class="control-label">Documento digital *</label>
                <div class="controls">
                    <input type="file" name ="decretoFile" id="nombreArchDecreto" accept="application/pdf,.pdf"
                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"decretoFile"}\'
                    >
                    <a id="decretoArchivo" class="lnk_ext" tabindex=-1 lnk="decreto/' . $ur->getClave() . '_decreto.pdf?npi='
            . rand(1, 99999) . '" target="_blank">' . $ur->getDecretoArchivo() . '</a>
                </div>
            </div>
             <div id="progressDec" class="progress progress-striped mt-2" style="height: 14px;">
                <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">0% Complete</span>
                </div>
            </div>
            <div id="estadoUploadDec" class="upload-status mt-1" style="display:none; font-size:12px; color:#0d6efd;">
                <i class="fa fa-circle-notch fa-spin me-1"></i><span class="upload-status-text">Preparando archivo...</span>
            </div>
            <div id="overlayUploadDec" class="upload-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:1080; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:18px 24px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:12px; min-width:280px;">
                    <i class="fa fa-circle-notch fa-spin" style="font-size:22px; color:#0d6efd;"></i>
                    <div>
                        <div style="font-weight:600;" class="upload-overlay-title">Subiendo archivo...</div>
                        <div class="upload-overlay-text" style="font-size:12px; color:#555;">Por favor espere, no cierre esta ventana.</div>
                    </div>
                </div>
            </div>';

            $pdfOrganigrama = '
            <div class="control-group">
                <label class="control-label"> Documento digital *</label>
                <div class="controls">
                    <input type="file" name ="estructuraorganicaFile" id="nombreArchEstructuraorganica"
                    accept="application/pdf,.pdf" data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"estructuraorganicaFile"}\'
                    >
                    <a id="organigramaArchivo" class="lnk_ext" tabindex=-1 lnk="estructuraorganica/' . $ur->getClave()
            . '_estructuraorganica.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getOrganigramaArchivo() . '</a>
                </div>
            </div>
            <div id="progressOrg" class="progress progress-striped mt-2" style="height: 14px;">
                <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">0% Complete</span>
                </div>
            </div>
            <div id="estadoUploadOrg" class="upload-status mt-1" style="display:none; font-size:12px; color:#0d6efd;">
                <i class="fa fa-circle-notch fa-spin me-1"></i><span class="upload-status-text">Preparando archivo...</span>
            </div>
            <div id="overlayUploadOrg" class="upload-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:1080; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:18px 24px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:12px; min-width:280px;">
                    <i class="fa fa-circle-notch fa-spin" style="font-size:22px; color:#0d6efd;"></i>
                    <div>
                        <div style="font-weight:600;" class="upload-overlay-title">Subiendo archivo...</div>
                        <div class="upload-overlay-text" style="font-size:12px; color:#555;">Por favor espere, no cierre esta ventana.</div>
                    </div>
                </div>
            </div>';

            $pdfInterno = '
            <div class="control-group">
                <label class="control-label"> Documento digital * (formato PDF máximo de 6MB)</label>
                <div class="controls">
                    <input type="file" name ="internoFile" id="nombreArchInterno" accept="application/pdf,.pdf"
                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"internoFile"}\'
                    >
                    <a id="internoArchivo" class="lnk_ext" tabindex=-1 lnk="interno/' . $ur->getClave() . '_interno.pdf?npi='
            . rand(1, 99999) . '" target="_blank">' . $ur->getInternoArchivo() . '</a>
                </div>
            </div>
            <div id="progressRI" class="progress progress-striped mt-2" style="height: 14px;">
                <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">0% Complete</span>
                </div>
            </div>
            <div id="estadoUploadRI" class="upload-status mt-1" style="display:none; font-size:12px; color:#0d6efd;">
                <i class="fa fa-circle-notch fa-spin me-1"></i><span class="upload-status-text">Preparando archivo...</span>
            </div>
            <div id="overlayUploadRI" class="upload-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:1080; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:18px 24px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:12px; min-width:280px;">
                    <i class="fa fa-circle-notch fa-spin" style="font-size:22px; color:#0d6efd;"></i>
                    <div>
                        <div style="font-weight:600;" class="upload-overlay-title">Subiendo archivo...</div>
                        <div class="upload-overlay-text" style="font-size:12px; color:#555;">Por favor espere, no cierre esta ventana.</div>
                    </div>
                </div>
            </div>';
            $pdfLegal = '
            <div class="control-group">
                <label class="control-label"> Documento digital *</label>
                <div class="controls">
                    <input type="file" name ="legalFile" id="nombreArchLegal" accept="application/pdf,.pdf"
                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"legalFile"}\'
                    >
                    <a id="legalArchivo" class="lnk_ext" tabindex=-1 lnk="legal/' . $ur->getClave()
            . '_legal.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getInternoPeriodico() . '</a>
                </div>
            </div>
            <div id="progressLeg" class="progress progress-striped mt-2" style="height: 14px;">
                <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">0% Complete</span>
                </div>
            </div>
            <div id="estadoUploadLeg" class="upload-status mt-1" style="display:none; font-size:12px; color:#0d6efd;">
                <i class="fa fa-circle-notch fa-spin me-1"></i><span class="upload-status-text">Preparando archivo...</span>
            </div>
            <div id="overlayUploadLeg" class="upload-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:1080; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:18px 24px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:12px; min-width:280px;">
                    <i class="fa fa-circle-notch fa-spin" style="font-size:22px; color:#0d6efd;"></i>
                    <div>
                        <div style="font-weight:600;" class="upload-overlay-title">Subiendo archivo...</div>
                        <div class="upload-overlay-text" style="font-size:12px; color:#555;">Por favor espere, no cierre esta ventana.</div>
                    </div>
                </div>
            </div>';
        }

        $html = '<div id="modalComentario"></div><br />
            <form role ="form">
                <div id="seccion1">
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <legend>Identificación Institucional</legend>
                        </div>
                        <fieldset class="col-md-4 col-xs-12 mb-4">
                        ' . $this->ponInput($inicioPeriodicoOficial) . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-6 mb-4">
                        ' . $this->ponInput($inicioFecha) . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-12 mb-4">
                        ' . $pdfLegal . '
                        </fieldset>
                    </div>

                    <div class="row">
                        <fieldset class="col-md-4 col-xs-12 mb-4">
                        ' . $this->ponInput($modificacionPeriodico) . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-12 mb-4">
                        ' . $this->ponInput($modificacionFecha) . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-12 mb-4">
                        ' . $pdfDecreto . '
                        </fieldset>
                    </div>

                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <legend> Reglamento interno </legend>
                        </div>
                        <fieldset class="col-md-4 col-xs-12 mb-4">
                        ' . $pdfInterno . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-12 mb-4">
                        ' . $this->ponInput($planeacionFecha) . '
                        </fieldset>

                    </div>

                    <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <legend> Estructura Orgánica </legend>
                            </div>
                            <fieldset class="col-md-4 col-xs-12 mb-4">
                            ' . $pdfOrganigrama . '
                            </fieldset>
                            <fieldset class="col-md-4 col-xs-12 mb-4">
                            ' . $this->ponInput($validacionFecha) . '
                            </fieldset>
                        </fieldset>
                    </div>
                </div>


                ' . $this->dameHtmlPersonasDocumentos($status) . '


                <div id="seccion2" style="display: none;">
                    <div class="row">
                        <div class="col-md-12 col-xs-12 ">
                            <legend>Domicilio y teléfono de la UR</legend>
                        </div>
                        <fieldset class="col-md-6 col-xs-12 mb-4">
                        ' . $this->ponInput($domicilioUR) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-12 mb-4">
                        ' . $this->ponInput($telefonoUR) . '
                        </fieldset>
                    </div>

                    <div class="row">
                        <fieldset class="col-md-6 col-xs-12">
                            ' . $this->ponCombo($municipio) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6 col-xs-12">
                            ' . $this->ponInput($codigoPostal) . '
                        </fieldset>
                    </div>

                    <div class="row m-t-10">
                        <div class="col-md-12 col-xs-12">
                            <legend>Página Web y Correo Electrónico de Contacto</legend>
                        </div>
                        <fieldset class="col-md-6 col-xs-12">
                            ' . $this->ponInput($paginaWeb) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-12">
                            ' . $this->ponInput($correoElectronicoContacto) . '
                        </fieldset>
                    </div>
                </div>
            </form>';

        $this->mixHtml($html);
        $dominiosPermitidos = [];
        $sql = 'SELECT dmi_dominio, dmi_activo from ' . BD_SINPRES . '.cat_DominioInstitucional
        WHERE dmi_activo = ?;';
        $param = [1];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($param);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            while ($data = $this->extraer()) {
                $dominiosPermitidos[] = $data['dmi_dominio'];
            }
        }
        $script = '
            // Pagina arriba y abajo permitirán cambiarse entre los Tabuladores
            Mousetrap.bind("shift+pagedown", function(e) {
                e.preventDefault;
                var actual = $("#page-tabs .active a").attr("href");
                switch (actual) {
                    case "ii":
                        $("a[href*=\"dt\"]").click();
                        break;
                    case "dt":
                        $("a[href*=\"tu\"]").click();
                        break;
                    case "tu":
                        $("a[href*=\"ta\"]").click();
                        break;
                    case "ta":
                        $("a[href*=\"tp\"]").click();
                        break;
                    case "tp":
                        $("a[href*=\"te\"]").click();
                        break;
                    case "te":
                        $("a[href*=\"ii\"]").click();
                        break;
                }
                e.stopPropagation;
            });
            Mousetrap.bind("shift+pageup", function(e) {
                e.preventDefault;
                var actual = $("#page-tabs .active a").attr("href");
                switch (actual) {
                    case "ii":
                        $("a[href*=\"te\"]").click();
                        break;
                    case "dt":
                        $("a[href*=\"ii\"]").click();
                        break;
                    case "tu":
                        $("a[href*=\"dt\"]").click();
                        break;
                    case "ta":
                        $("a[href*=\"tu\"]").click();
                        break;
                    case "tp":
                        $("a[href*=\"ta\"]").click();
                        break;
                    case "te":
                        $("a[href*=\"tp\"]").click();
                        break;
                }
                e.stopPropagation;
            });


            $("#regresarUR").click(function(){
                $("nav").menusinpres("clickMenu");
            });

            $("#nombreArchLegal").click(function(){
                $("#progressLeg .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });
            $("#nombreArchEstructuraorganica").click(function(){
                $("#progressOrg .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });
            $("#nombreArchDecreto").click(function(){
                $("#progressDec .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });

            $("#nombreArchInterno").click(function(){
                $("#progressRI .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });           
                        
            $("#codigoPostal").keypress(function(event){
                let tecla = String.fromCharCode(event.which);
                let regex = /^[0-9]$/;
                if (!regex.test(tecla)) {
                    event.preventDefault();
                }
            });
        ';

        $script .= '
            function inicializarUpload(config) {
                var $input = $(config.input);
                var $progressWrap = $(config.progress);
                var $bar = $progressWrap.find(".progress-bar");
                var $estado = $(config.estado);
                var $estadoTxt = $estado.find(".upload-status-text");
                var $overlay = $(config.overlay);
                var $overlayTxt = $overlay.find(".upload-overlay-text");
                var $overlayTitle = $overlay.find(".upload-overlay-title");
                var $archivoTexto = $(config.archivoTexto);
                var procesandoTimer = null;                
                function setEstado(texto, color) {
                    $estadoTxt.text(texto);
                    $estado.css("color", color || "#0d6efd").show();
                }
                function ocultarEstado() {
                    $estado.hide();
                }
                function bloquearUI() {
                    $input.prop("disabled", true);
                    $overlay.css("display", "flex");
                }
                function liberarUI() {
                    $input.prop("disabled", false);
                    $overlay.hide();
                    if (procesandoTimer) {
                        clearTimeout(procesandoTimer);
                        procesandoTimer = null;
                    }
                }
                function pintarBarra(pct, claseExtra) {
                    $bar.css("width", pct + "%").attr("aria-valuenow", pct);
                    $bar.removeClass("bg-success bg-danger progress-bar-animated");
                    if (claseExtra) {
                        $bar.addClass(claseExtra);
                    }
                }
                $input.on("click", function () {
                    pintarBarra(0);
                    ocultarEstado();
                });
                $input.fileupload({
                    dataType: "json",
                    add: function (e, data) {
                        var archivo = data.files?.[0] || null;
                        if (!archivo) {
                            notify("No se seleccionó ningún archivo.","error");
                            return false;
                        }
                        var nombre = (archivo.name || "").toLowerCase();
                        var tipo = (archivo.type || "").toLowerCase();
                         var esPdf = nombre.lastIndexOf(".pdf") === nombre.length - 4
                                    && (tipo === "" || tipo === "application/pdf");
                        if (!esPdf) {
                            notify("Solo se permiten archivos PDF.","error");
                            $input.val("");
                            return false;
                        }
                        /*var maxBytes = 6 * 1024 * 1024;
                        if (archivo.size > maxBytes) {
                            notify("El archivo excede el tamaño máximo permitido de 6MB.","error");
                            $input.val("");
                            return false;
                        }*/
                        $overlayTitle.text("Subiendo \"" + archivo.name + "\"");
                        $overlayTxt.text("Por favor espere, no cierre esta ventana.");
                        data.submit();
                    },
                     start: function() {
                        bloquearUI();
                        pintarBarra(0, "progress-bar-animated");
                        setEstado("Iniciando carga...", "#0d6efd");
                    },
                    progress: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        pintarBarra(progress, "progress-bar-animated");
                        if (progress < 100) {
                            setEstado("Subiendo archivo... " + progress + "%", "#0d6efd");
                            $overlayTxt.text("Enviando datos al servidor (" + progress + "%)...");
                        } else {
                            setEstado("Procesando archivo...","#0d6efd");
                            $overlayTxt.text("Procesando archivo...");
                        }
                    },
                    done: function (e, data) {
                        var resultado = data.result || {};
                        var validacion = resultado.validacion || {};
                        var estado = resultado.status || "success";
                        if (validacion.nombre) {
                            $archivoTexto.text(validacion.nombre);
                        }
                        if (estado === "error") {
                            pintarBarra(100, "bg-danger");
                            setEstado(validacion.mensaje || "Error al cargar archivo.", "#dc3545");
                            notify(validacion.mensaje || "Error al cargar archivo.", "error");
                        } else {
                            pintarBarra(100, "bg-success");
                            setEstado(validacion.mensaje || "Archivo cargado con éxito.", "#198754");
                            notify(validacion.mensaje || "Archivo cargado con éxito.", "success");
                        }
                        liberarUI();
                        setTimeout(ocultarEstado, 4000);
                    },
                    fail: function () {
                        pintarBarra(100, "bg-danger");
                        setEstado("No fue posible cargar el archivo.", "#dc3545");
                        notify("No fue posible cargar el archivo.", "error");
                        liberarUI();
                    },
                    always: function () {
                        liberarUI();
                    }
                });
            }
            inicializarUpload({
                input: "#nombreArchLegal",
                progress: "#progressLeg",
                estado: "#estadoUploadLeg",
                overlay: "#overlayUploadLeg",
                archivoTexto: "#legalArchivo"
            });
            inicializarUpload({
                input: "#nombreArchDecreto",
                progress: "#progressDec",
                estado: "#estadoUploadDec",
                overlay: "#overlayUploadDec",
                archivoTexto: "#decretoArchivo"
            });
            inicializarUpload({
                input: "#nombreArchInterno",
                progress: "#progressRI",
                estado: "#estadoUploadRI",
                overlay: "#overlayUploadRI",
                archivoTexto: "#internoArchivo"
            });
            inicializarUpload({
                input: "#nombreArchEstructuraorganica",
                progress: "#progressOrg",
                estado: "#estadoUploadOrg",
                overlay: "#overlayUploadOrg",
                archivoTexto: "#organigramaArchivo"
            });
        ';

        $script .= '
        $("#selector").selectpicker();
            $(".lnk_ext").click(function(){
                lnk = $(this).attr("lnk");
                tit = $(this).html();

                if (tit != "Sin archivo") {
                    load_viewer("vis1",tit,"F");
                    comunica("' . $this->pantalla . '",{metodo:"download",lnk:lnk},false,true);
                }
            });
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            // Atajos de ésta pantalla
            Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); $("#btnguardarplanur").click(); });

            setTimeout( function() { $( "#inicioPeriodicoOficial" ).focus() }, 200 );

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
                location.reload();
            });

            // Para la validación del email
            function validateEmail(email) {
                var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.'
        . '[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
            // Para la validación del teléfono
            function validatePhone(number) {
                var re = /^\+?\d{1,3}?[- .]?\(?(?:\d{2,3})\)?[- .]?\d\d\d[- .]?\d\d\d\d$/;
                return re.test(number);
            }


            // -------------------------------------------------------------------------------------
            $("#page-tabs button").click( function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs button").each(function (index) { $(this).removeClass("active"); });
                seleccionado.addClass("active");

                $("#seccion1").hide();
                $("#seccion2").hide();
                $("#seccion3").hide();
                $("#seccion4").hide();
                $("#seccion5").hide();
                $("#seccion6").hide();

                switch ( seleccionado.attr("href") ) {
                    case "ii":
                        $("#desvalida").hide();
                        $("#seccion1").show();
                        setTimeout( function() { $( "#inicioPeriodicoOficial" ).focus() }, 1 );
                        break;
                    case "dt":
                        $("#desvalida").hide();
                        $("#seccion2").show();
                        setTimeout( function() { $( "#domicilioUR" ).focus() }, 1 );
                        break;
                    case "tu":
                        $("#desvalida").hide();
                        $("#seccion3").show();
                        setTimeout( function() { $( "#tituloTitular" ).focus() }, 1 );
                        break;
                    case "ta":
                        $("#desvalida").hide();
                        $("#seccion4").show();
                        setTimeout( function() { $( "#tituloAdministrativo" ).focus() }, 1 );
                        break;
                    case "tp":
                        $("#desvalida").hide();
                        $("#seccion5").show();
                        setTimeout( function() { $( "#tituloPlaneacion" ).focus() }, 1 );
                        break;
                    case "te":
                        $("#desvalida").hide();
                        $("#seccion6").show();
                        setTimeout( function() { $( "#tituloEvaluacion" ).focus() }, 1 );
                        break;
                }
                e.stopPropagation();
                return false;
            });

            // Validamos el teléfono
            $("#telefonoUR").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if ( validatePhone( valor ) ) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                        notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                    }
                }
            });
           
            // Validamos el email Contacto
            $("#correoElectronicoContacto").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if ( validateEmail( valor ) ) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                        notify("El correo electrónico no es válido", "error");
                    }
                }
            });
            // Validamos el email
            $(".validarEmail").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if (validateEmail(valor)) {
                        let dominiosPermitidos = ' . json_encode($dominiosPermitidos) . ';
                        const correo = valor.trim().toLowerCase();
                        const regexCorreo =
                            /^[a-zA-Z0-9]([a-zA-Z0-9._-]*[a-zA-Z0-9])?@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+$/;

                        // validar formato
                        if (!regexCorreo.test(correo)) {
                            $(this).css("color", "red");
                            notify("El correo electrónico no es válido", "error");
                            return;
                        }

                        // obtener dominio
                        let dominio = correo.split("@")[1] ?? "";
                        // validar dominios permitidos
                        let dominioPermitido =
                            dominiosPermitidos.some(d =>
                                dominio.endsWith(d.toLowerCase())
                            );

                        if (dominioPermitido) {
                            $(this).css("color", "green");
                        } else {
                            $(this).css("color", "red");
                            notify("Favor de agregar un correo válido y/o institucional","error");
                        }
                    } else {
                        $(this).css("color", "red");
                        notify("El correo electrónico no es válido", "error");
                    }
                }
            });
            $(".validarTelefono").on("blur", function () {
                let telefono = $(this).val();
                if (telefono == "") {
                    return;
                }
                const numeroLimpio = telefono.replace(/\D/g, "");
                const telefonoValido =
                    /^\d{10}$/.test(numeroLimpio)
                    && !/^(\d)\1{9}$/.test(numeroLimpio)
                    && numeroLimpio[0] !== "0"
                    && numeroLimpio[0] !== "1"
                    && !/(.)\1{4,}/.test(numeroLimpio);
                if (!telefonoValido) {
                    $(this).css("color", "red");
                    let idCampo = $(this).attr("id");
                    let tipo = idCampo.startsWith("celular") ? "celular" : "telefónico de oficina";
                    notify("Ingrese un número " + tipo + " válido de 10 dígitos", "error");
                } else {
                    $(this).css("color", "green");
                }

            });
            function validarTelefonosDiferentes(idCampo1, idCampo2, campo, seccion) {
                $(idCampo1).on("blur", function () {
                    var valor1 = $(idCampo1).val().trim();
                    var valor2 = $(idCampo2).val().trim();
                    var mensajeCelular = seccion + ": Ingrese un número de celular diferente al teléfono de oficina";
                    var mensajeoficina = seccion + ": Ingrese un número de oficina diferente al teléfono celular";
                    mensaje = (campo == "celular") ? mensajeCelular : mensajeoficina;
                    if (valor1 !== "" && valor2 !== "" && valor1 === valor2) {
                        $(idCampo1).css("color", "red");
                        notify(mensaje, "error");
                    }
                });
            }
            validarTelefonosDiferentes(
                "#celularTitular",
                "#telefonoTitular",
                "celular",
                "Titular UR"
            );
            validarTelefonosDiferentes(
                "#telefonoTitular",
                "#celularTitular",
                "oficina",
                "Titular UR"
            );
            validarTelefonosDiferentes(
                "#celularAdministrativo",
                "#telefonoAdministrativo",
                "celular",
                "Titular Administrativo"
            );
            validarTelefonosDiferentes(
                "#telefonoAdministrativo",
                "#celularAdministrativo",
                "oficina",
                "Titular Administrativo"
            );
            validarTelefonosDiferentes(
                "#celularPlaneacion",
                "#telefonoPlaneacion",
                "celular",
                "Titular Planeación"
            );
            validarTelefonosDiferentes(
                "#telefonoPlaneacion",
                "#celularPlaneacion",
                "oficina",
                "Titular Planeación"
            );
            validarTelefonosDiferentes(
                "#celularEvaluacion",
                "#telefonoEvaluacion",
                "celular",
                "Responsable de Seguimiento"
            );
            validarTelefonosDiferentes(
                "#telefonoEvaluacion",
                "#celularEvaluacion",
                "oficina",
                "Responsable de Seguimiento"
            );
            // Validamos la extension Titular
            $("#extensionTitular").on("input", function () {
                this.value = this.value
                    .replace(/^\s+|\s+$/g, "")
                    .replace(/\s+/g, "")
                    .replace(/[^0-9]/g, "");
            });
            // Validamos el codigo postal
            $("#codigoPostal").on("input", function () {
                this.value = this.value
                    .replace(/^\s+|\s+$/g, "")
                    .replace(/\s+/g, "")
                    .replace(/[^0-9]/g, "")
                    .slice(0, 5);
            });

            $("#codigoPostal").on("blur", function () {
                let codigo = $(this).val();
                if (codigo == "") {
                    return;
                }
                let regex = /^\d{5}$/;
                if (!regex.test(codigo)) {
                    notify("Ingrese un código postal válido de 5 dígitos", "error");
                }
            });
            // Validamos la extension Administrativo
            $("#extensionAdministrativo").on("input", function () {
                this.value = this.value
                    .replace(/^\s+|\s+$/g, "")
                    .replace(/\s+/g, "")
                    .replace(/[^0-9]/g, "");
            });
            // Validamos la extension Planeacion 
            $("#extensionPlaneacion").on("input", function () {
                this.value = this.value
                    .replace(/^\s+|\s+$/g, "")
                    .replace(/\s+/g, "")
                    .replace(/[^0-9]/g, "");
            });
            // Validamos la extension Evaluacion
            $("#extensionEvaluacion").on("input", function () {
                this.value = this.value
                    .replace(/^\s+|\s+$/g, "")
                    .replace(/\s+/g, "")
                    .replace(/[^0-9]/g, "");
            });

            function validaCamposVacios(){
                var bandera = 0;
                var campos = "";
                var banderaII = 0;
                var camposII = "<h6>Apartado: Identificación Institucional</h6>- Campo(s):";
                var banderaDUR = "";
                var camposDUR = "<br><h6>Apartado: Domicilio UR</h6>- Campo(s):";
                var camposTUR = "<br><h6>Apartado: Titular UR</h6>- Campo(s):";
                var banderaTUR = "";
                var camposTA = "<br><h6>Apartado: Titular Administrativo</h6>- Campo(s):";
                var banderaTA = "";
                var camposTP = "<br><h6>Apartado: Titular Planeación</h6>- Campo(s):";
                var banderaTP = "";
                var camposRS = "<br><h6>Apartado: Responsable de Seguimiento</h6>- Campo(s):";
                var banderaRS = "";
                if($("#inicioPeriodicoOficial").val().length < 1) {
                    banderaII = 1; 
                    camposII += " Documento legal de creación,";
                }
                if($("#inicioFecha").val().length < 1){
                    banderaII = 1;
                    camposII += " Fecha de publicación del documento legal de creación,";
                }
                if($("#legalArchivo").text().trim() == ""){
                    banderaII = 1;
                    camposII += " Documento digital del documento legal de creación,";
                }
                if($("#modificacionPeriodico").val().length < 1){
                    banderaII = 1;
                    camposII += " Periódico oficial de la última modificación o reforma,";
                }
                if($("#modificacionFecha").val().length < 1){
                    banderaII = 1;
                    camposII += " Fecha de publicación de la última modificación o reforma ,";
                }
                if($("#decretoArchivo").text().trim() == ""){
                    banderaII = 1;
                    camposII += " Documento digital del periódico oficial de la última modificación o reforma,";
                }
                if($("#internoArchivo").text().trim() == ""){
                    banderaII = 1;
                    camposII += " Documento digital del reglamento interno,";
                }
                if($("#planeacionFecha").val().length < 1){
                    banderaII = 1;
                    camposII += " Fecha de publicación del reglamento interno,";
                }
                if($("#organigramaArchivo").text().trim() == ""){
                    banderaII = 1;
                    camposII += " Documento digital de la estructura orgánica,";
                }
                if($("#validacionFecha").val().length < 1){
                    banderaII = 1;
                    camposII += " Fecha de validación de la estructura orgánica,";
                }
                if (banderaII == 1) {
                    camposII = camposII.replace(/,\s*$/, "");
                    camposII += "<br>";
                } else {
                    camposII = "";
                }

                if($("#domicilioUR").val().length < 1){
                    banderaDUR = 1;
                    camposDUR += " Domicilio,";
                }
                if($("#telefonoUR").val().length < 1){
                    banderaDUR = 1;
                    camposDUR += " Teléfono,";
                }
                if($("#municipio").val().length < 1){
                    banderaDUR = 1;
                    camposDUR += " Municipio,";
                }
                if($("#codigoPostal").val().length < 1){
                    banderaDUR = 1;
                    camposDUR += " Código Postal,";
                }
                if($("#paginaWeb").val().length < 1){
                    banderaDUR = 1;
                    camposDUR += " Página Web,";
                }
                if($("#correoElectronicoContacto").val().length < 1){
                    banderaDUR = 1;
                    camposDUR += " Correo Electrónico,";
                }
                if (banderaDUR == 1) {
                    camposDUR = camposDUR.replace(/,\s*$/, "");
                    camposDUR += "<br>";
                } else {
                    camposDUR = "";
                }                
                if($("#tituloTitular").val().length < 1){
                    banderaTUR = 1;
                    camposTUR += " Título,";
                }
                if($("#nombreTitular").val().length < 1){
                    banderaTUR = 1;
                    camposTUR += " Nombre,";
                }
                if($("#cargoTitular").val().length < 1){
                    banderaTUR = 1;
                    camposTUR += " Cargo,";
                }
                if($("#emailTitular").val().length < 1){
                    banderaTUR = 1;
                    camposTUR += " Correo Electrónico,";
                }
                if($("#celularTitular").val().length < 1){
                    banderaTUR = 1;
                    camposTUR += " Teléfono Celular,";
                }
                if($("#telefonoTitular").val().length < 1){
                    banderaTUR = 1;
                     camposTUR += " Teléfono de Oficina,";
                }
                if($("#extensionTitular").val().length < 1){
                    banderaTUR = 1;
                    camposTUR += " Extensión,";
                }
                if($("#nameFilenamenombramientoTitular").text().trim() == "Sin archivo"){
                    banderaTUR = 1;
                    camposTUR += " Nombramiento o documento que lo acredite,";
                }
                if($("#nameFilenameidentificacionTitular").text().trim() == "Sin archivo"){
                    banderaTUR = 1;
                    camposTUR += " Identificación oficial,";
                }
                if($("#nameFilenamefirmaTitular").text().trim() == "Sin archivo"){
                    banderaTUR = 1;
                    camposTUR += " Formato de legalización de firmas,";
                }
                if (banderaTUR == 1) {
                    camposTUR = camposTUR.replace(/,\s*$/, "");
                    camposTUR += "<br>";
                } else {
                    camposTUR = "";
                }
                
                if($("#tituloAdministrativo").val().length < 1){
                    banderaTA = 1;
                    camposTA += " Título,";
                }
                if($("#nombreAdministrativo").val().length < 1){
                    banderaTA = 1;
                    camposTA += " Nombre,";
                }
                if($("#cargoAdministrativo").val().length < 1){
                    banderaTA = 1;
                    camposTA += " Cargo,";
                }
                if($("#emailAdministrativo").val().length < 1){
                    banderaTA = 1;
                    camposTA += " Correo Electrónico,";
                }
                if($("#celularAdministrativo").val().length < 1){
                    banderaTA = 1;
                    camposTA += " Teléfono Celular,";
                }
                if($("#telefonoAdministrativo").val().length < 1){
                    banderaTA = 1;
                     camposTA += " Teléfono de Oficina,";
                }
                if($("#extensionAdministrativo").val().length < 1){
                    banderaTA = 1;
                    camposTA += " Extensión,";
                }
                if($("#nameFilenamenombramientoAdministrativo").text().trim() == "Sin archivo"){
                    banderaTA = 1;
                    camposTA += " Nombramiento o documento que lo acredite,";
                }
                if($("#nameFilenameidentificacionAdministrativo").text().trim() == "Sin archivo"){
                    banderaTA = 1;
                    camposTA += " Identificación oficial,";
                }
                if($("#nameFilenamefirmaAdministrativo").text().trim() == "Sin archivo"){
                    banderaTA = 1;
                    camposTA += " Formato de legalización de firmas,";
                }             
                if (banderaTA == 1) {
                    camposTA = camposTA.replace(/,\s*$/, "");
                    camposTA += "<br>";
                } else {
                    camposTA = "";
                }         

                if($("#tituloPlaneacion").val().length < 1){
                    banderaTP = 1;
                    camposTP += " Título,";
                }
                if($("#nombrePlaneacion").val().length < 1){
                    banderaTP = 1;
                    camposTP += " Nombre,";
                }
                if($("#cargoPlaneacion").val().length < 1){
                    banderaTP = 1;
                    camposTP += " Cargo,";
                }
                if($("#emailPlaneacion").val().length < 1){
                    banderaTP = 1;
                    camposTP += " Correo Electrónico,";
                }
                if($("#celularPlaneacion").val().length < 1){
                        banderaTP = 1;
                    camposTP += " Teléfono Celular,";
                }
                if($("#telefonoPlaneacion").val().length < 1){
                    banderaTP = 1;
                     camposTP += " Teléfono de Oficina,";
                }
                if($("#extensionPlaneacion").val().length < 1){
                    banderaTP = 1;
                    camposTP += " Extensión,";
                }
                if($("#nameFilenamenombramientoPlaneacion").text().trim() == "Sin archivo"){
                    banderaTP = 1;
                    camposTP += " Nombramiento o documento que lo acredite,";
                }
                if($("#nameFilenameidentificacionPlaneacion").text().trim() == "Sin archivo"){
                    banderaTP = 1;
                    camposTP += " Identificación oficial,";
                }
                if($("#nameFilenamefirmaPlaneacion").text().trim() == "Sin archivo"){
                    banderaTP = 1;
                    camposTP += " Formato de legalización de firmas,";
                }
                if (banderaTP == 1) {
                    camposTP = camposTP.replace(/,\s*$/, "");
                    camposTP += "<br>";
                } else {
                    camposTP = "";
                }

                if($("#tituloEvaluacion").val().length < 1){
                    banderaRS = 1;
                    camposRS += " Título,";
                }
                if($("#nombreEvaluacion").val().length < 1){
                    banderaRS = 1;
                    camposRS += " Nombre,";
                }
                if($("#cargoEvaluacion").val().length < 1){
                    banderaRS = 1;
                    camposRS += " Cargo,";
                }
                if($("#emailEvaluacion").val().length < 1){
                    banderaRS = 1;
                    camposRS += " Correo Electrónico,";
                }
                if($("#celularEvaluacion").val().length < 1){
                    banderaRS = 1;
                    camposRS += " Teléfono Celular,";
                }
                if($("#telefonoEvaluacion").val().length < 1){
                    banderaRS = 1;
                     camposRS += " Teléfono de Oficina,";
                }
                if($("#extensionEvaluacion").val().length < 1){
                    banderaRS = 1;
                    camposRS += " Extensión,";
                }
                if($("#nameFilenamenombramientoEvaluacion").text().trim() == "Sin archivo"){
                    banderaRS = 1;
                    camposRS += " Nombramiento o documento que lo acredite,";
                }
                if($("#nameFilenameidentificacionEvaluacion").text().trim() == "Sin archivo"){
                    banderaRS = 1;
                    camposRS += " Identificación oficial,";
                }
                if($("#nameFilenamefirmaEvaluacion").text().trim() == "Sin archivo"){
                    banderaRS = 1;
                    camposRS += " Formato de legalización de firmas,";
                }
                if (banderaRS == 1) {
                    camposRS = camposRS.replace(/,\s*$/, "");
                    camposRS += "<br>"; 
                } else {
                    camposRS = "";
                }
                if (banderaII == 1 || banderaDUR == 1 || banderaTUR == 1 || banderaTA == 1 || banderaTP == 1 || banderaRS == 1) {
                    bandera = 1;
                    campos = camposII + camposDUR + camposTUR + camposTA + camposTP + camposRS;
                    campos = campos.replace(/<br>\s*$/, "");
                    alertify.alert("Atención", "Falta capturar información o cargar documentos obligatorios en el " + campos);
                    return false;
                }
                return (bandera == 1) ? false : true;

            }

            $("#btnguardarplanur").click(function(e){
                e.preventDefault();
                var validaTelefono = /^\d{10}$/;
                var msg = "El número telefónico no es válido, debe ser de 10 dígitos";
                if(!validaTelefono.test($("#telefonoTitular").val().trim()) && $("#telefonoTitular").val().trim() != ""){
                    notify("Titular UR: " + msg, "error");
                }else if(!validaTelefono.test($("#telefonoAdministrativo").val().trim()) && $("#telefonoAdministrativo").val().trim() != ""){
                    notify("Titular Administrativo: " + msg, "error");
                }else if (!validaTelefono.test($("#telefonoPlaneacion").val().trim()) && $("#telefonoPlaneacion").val().trim() != ""){
                    notify("Titular Planeación: " + msg, "error");
                }else if(!validaTelefono.test($("#telefonoEvaluacion").val().trim()) && $("#telefonoEvaluacion").val().trim() != ""){
                    notify("Responsable de Seguimiento: " + msg, "error");
                }else if(!validaTelefono.test($("#telefonoUR").val().trim()) && $("#telefonoUR").val().trim() != ""){
                    notify("Domicilio UR: " + msg, "error");
                }else{
                    comunica
                    (
                        "' . $this->pantalla . '",
                        {
                            metodo:"guardar",

                            ' . (($this->CambiosActivos == true && ($status == 0 || $status == 1 || $status == 3)) ? '
                                inicioPeriodicoOficial:$("#inicioPeriodicoOficial").val().trim(),
                                inicioFecha:$("#inicioFecha").val().trim(),
                                modificacionPeriodico:$("#modificacionPeriodico").val().trim(),
                                modificacionFecha:$("#modificacionFecha").val().trim(),
                                planeacionFecha:$("#planeacionFecha ").val().trim(),
                                validacionFecha:$("#validacionFecha").val().trim(),
                            ' : '') . '
                            tituloTitular:$("#tituloTitular").val().toUpperCase().trim(),
                            nombreTitular:$("#nombreTitular").val().toUpperCase().trim(),
                            cargoTitular:$("#cargoTitular").val().toUpperCase().trim(),
                            correoElectronicoTitular:$("#emailTitular").val().trim(),
                            telefonoTitular:$("#telefonoTitular").val().trim(),
                            celularTitular:$("#celularTitular").val().trim(),
                            extensionTitular:$("#extensionTitular").val().trim(),
                            
                            tituloAdministrativo:$("#tituloAdministrativo").val().toUpperCase().trim(),
                            nombreAdministrativo:$("#nombreAdministrativo").val().toUpperCase().trim(),
                            cargoAdministrativo:$("#cargoAdministrativo").val().toUpperCase().trim(),
                            correoElectronicoAdministrativo:$("#emailAdministrativo").val().trim(),
                            telefonoAdministrativo:$("#telefonoAdministrativo").val().trim(),
                            celularAdministrativo:$("#celularAdministrativo").val().trim(),
                            extensionAdministrativo:$("#extensionAdministrativo").val().trim(),
                            
                            tituloPlaneacion:$("#tituloPlaneacion").val().toUpperCase().trim(),
                            nombrePlaneacion:$("#nombrePlaneacion").val().toUpperCase().trim(),
                            cargoPlaneacion:$("#cargoPlaneacion").val().toUpperCase().trim(),
                            correoElectronicoPlaneacion:$("#emailPlaneacion").val().trim(),
                            telefonoPlaneacion:$("#telefonoPlaneacion").val().trim(),
                            celularPlaneacion:$("#celularPlaneacion").val().trim(),
                            extensionPlaneacion:$("#extensionPlaneacion").val().trim(),

                            tituloEvaluacion:$("#tituloEvaluacion").val().toUpperCase().trim(),
                            nombreEvaluacion:$("#nombreEvaluacion").val().toUpperCase().trim(),
                            cargoEvaluacion:$("#cargoEvaluacion").val().toUpperCase().trim(),
                            correoElectronicoEvaluacion:$("#emailEvaluacion").val().trim(),
                            telefonoEvaluacion:$("#telefonoEvaluacion").val().trim(),
                            celularEvaluacion:$("#celularEvaluacion").val().trim(),
                            extensionEvaluacion:$("#extensionEvaluacion").val().trim(),

                            domicilio:$("#domicilioUR").val().toUpperCase().trim(),
                            telefono:$("#telefonoUR").val().toUpperCase().trim(),
                            municipio:$("#municipio").val().toUpperCase().trim(),
                            codigoPostal:$("#codigoPostal").val().toUpperCase().trim(),
                            paginaWeb:$("#paginaWeb").val().toUpperCase().trim(),
                            correoElectronicoContacto:$("#correoElectronicoContacto").val().trim(),
                            eur_id : ' . (($status != 0) ? $status : 1) . '
                        }
                    );}
                e.stopPropagation();
            });

            $("#verHistorial").click(function(){
                comunica( "' . $this->pantalla . '", { metodo:"verHistorial" } );
            });
            $("#autorizarPlanUR").click(function(){
                comunica( "' . $this->pantalla . '", { metodo:"autorizar", eur_id:4 } );
            });
            $("#desvalidarPlanUR").click(function(){
                comunica( "' . $this->pantalla . '", { metodo:"desvalidar" } );
            });

            $("#rechazarPlanUR").click(function(){
                comunica( "' . $this->pantalla . '", { metodo:"addComentario" } );
            });
        ';

        $script .= "
        $('#btnEnviar').click(function(){
            // Marcos: 08/oct/2015
            // Pedimos al usuario confirmación para proceder, ya que no podrán hacer nada mientras Finanzas no responda
            //if(validaCamposVacios()){
                comunica( ' $this->pantalla  ', { metodo:'validaMandatoMisional' } );
            //}
        });";
        $this->mixScript($script);
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Guardamos la planeación institucional
    OJO: Sólo usuarios de nivel 3 (UR) o los que tengan permisos de escritura
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function guardar()
    {
        // Volvemos a validar que tenga permisos de guardar, por si nos quieren hacer gol desde la consola firebug
        if ($this->escritura) {

            // Marcos: 21/octubre/2015
            // Cuando la planeación ya tenga el status de AUTORIZADA (4) ya no cambiaremos su status al guardar
            // Cuando no, sigue el comportamiento "normal" de poa

            // Obtenemos el status de la UR
            $sql = 'SELECT ur.eur_id, eur.eur_nombre, eur.eur_color
                FROM ' . BD_CTRL . '.cat_UnidadResponsable ur
                INNER JOIN ' . BD_CTRL . '.cat_EstadoUr eur USING( eur_id )
                WHERE ur.gpo_id = ?
                AND ur.ur_id = ? ;';
            $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $aStatus = $this->extraer();
            $this->desconectar();

            $status = $aStatus['eur_id'];

            $this->autorizacion = (($this->nvj_id >= 3));
            $this->prm->abc = 'c';
            // Marcos: 9 de abril.
            // NO CAMBIARA el status al cambiar algo de la planeación UR arrancado el ejercicio, porque o estan
            // registrando firmas, o estan cambiando al titular

            // AQUI
            // Marcos: 07/10/2015
            // Esto hay que comentarlo en el ejercicio, para que no cambie el status al darle el botón guardar
            // Se hará cuando se cierre toooooodo el POA
            // Marcos: 21/octubre/2015
            // Cuando la planeación ya tenga el status de AUTORIZADA (4) ya no cambiaremos su status al guardar
            // Cuando no, sigue el comportamiento "normal" de poa
            if ($status < 4) {
                $this->prm->eur_id = '1';
            }

            $this->prm->gpo_id = $this->clasAdm_gpo;
            $this->prm->ur_id = $this->clasAdm_ur;
            $this->prm->bit_observacion = 'EL USUARIO GUARDO LA PLANEACION UR';
            $this->prm->municipio = empty($this->prm->municipio) ? 0 : $this->prm->municipio;
            $this->prm->codigoPostal = empty($this->prm->codigoPostal) ? 0 : $this->prm->codigoPostal;
            if (isset($this->prm->codigoPostal) && !empty($this->prm->codigoPostal) && !$this->esCodigoPostalValido($this->prm->codigoPostal)) {
                $this->mixScript('notify("Domicilio UR: Ingrese un código postal válido de 5 dígitos", "error");');
                return;
            }
            if (isset($this->prm->telefonoUR) && !$this->esTelefonoValido($this->prm->telefonoUR)) {
                $this->mixScript('notify("Domicilio UR: El número telefónico no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->correoElectronicoContacto) && !$this->esCorreoDominioInstitucional($this->prm->correoElectronicoContacto)) {
                $this->mixScript('notify("Domicilio UR: Favor de agregar un correo válido y/o institucional", "error");');
                return;
            }
            if (isset($this->prm->correoElectronicoTitular) && !$this->esCorreoDominioInstitucional($this->prm->correoElectronicoTitular)) {
                $this->mixScript('notify("Titular UR: Favor de agregar un correo válido y/o institucional", "error");');
                return;
            }
            if (isset($this->prm->celularTitular) && !$this->esTelefonoValido($this->prm->celularTitular)) {
                $this->mixScript('notify("Titular UR: El número de celuar no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->telefonoTitular) && !$this->esTelefonoValido($this->prm->telefonoTitular)) {
                $this->mixScript('notify("Titular UR: El número telefónico de oficina no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->celularTitular) && isset($this->prm->telefonoTitular) && $this->telefonosIguales($this->prm->celularTitular, $this->prm->telefonoTitular)) {
                $this->mixScript('notify("Titular UR: El teléfono de oficina coincide con el teléfono celular, no pueden ser iguales", "error");');
                return;
            }
            if (isset($this->prm->extensionTitular) && !$this->esExtensionValida($this->prm->extensionTitular)) {
                $this->mixScript('notify("Titular UR: La extensión solo debe contener números", "error");');
                return;
            }
            if (isset($this->prm->correoElectronicoAdministrativo) && !$this->esCorreoDominioInstitucional($this->prm->correoElectronicoAdministrativo)) {
                $this->mixScript('notify("Titular Administrativo: Favor de agregar un correo válido y/o institucional", "error");');
                return;
            }
            if (isset($this->prm->celularAdministrativo) && !$this->esTelefonoValido($this->prm->celularAdministrativo)) {
                $this->mixScript('notify("Titular Administrativo: El número de celuar no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->telefonoAdministrativo) && !$this->esTelefonoValido($this->prm->telefonoAdministrativo)) {
                $this->mixScript('notify("Titular Administrativo: El número telefónico de oficina no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->celularAdministrativo) && isset($this->prm->telefonoAdministrativo) && $this->telefonosIguales($this->prm->celularAdministrativo, $this->prm->telefonoAdministrativo)) {
                $this->mixScript('notify("Titular Administrativo: El teléfono de oficina coincide con el teléfono celular, no pueden ser iguales", "error");');
                return;
            }
            if (isset($this->prm->extensionAdministrativo) && !$this->esExtensionValida($this->prm->extensionAdministrativo)) {
                $this->mixScript('notify("Titular Administrativo: La extensión solo debe contener números", "error");');
                return;
            }
            if (isset($this->prm->correoElectronicoPlaneacion) && !$this->esCorreoDominioInstitucional($this->prm->correoElectronicoPlaneacion)) {
                $this->mixScript('notify("Titular Planeación: Favor de agregar un correo válido y/o institucional", "error");');
                return;
            }
            if (isset($this->prm->celularPlaneacion) && !$this->esTelefonoValido($this->prm->celularPlaneacion)) {
                $this->mixScript('notify("Titular Planeación: El número de celuar no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->telefonoPlaneacion) && !$this->esTelefonoValido($this->prm->telefonoPlaneacion)) {
                $this->mixScript('notify("Titular Planeación: El número telefónico de oficina no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->celularPlaneacion) && isset($this->prm->telefonoPlaneacion) && $this->telefonosIguales($this->prm->celularPlaneacion, $this->prm->telefonoPlaneacion)) {
                $this->mixScript('notify("Titular Planeación: El teléfono de oficina coincide con el teléfono celular, no pueden ser iguales", "error");');
                return;
            }
            if (isset($this->prm->extensionPlaneacion) && !$this->esExtensionValida($this->prm->extensionPlaneacion)) {
                $this->mixScript('notify("Titular Planeación: La extensión solo debe contener números", "error");');
                return;
            }
            if (isset($this->prm->correoElectronicoEvaluacion) && !$this->esCorreoDominioInstitucional($this->prm->correoElectronicoEvaluacion)) {
                $this->mixScript('notify("Responsable de Seguimiento: Favor de agregar un correo válido y/o institucional", "error");');
                return;
            }
            if (isset($this->prm->celularEvaluacion) && !$this->esTelefonoValido($this->prm->celularEvaluacion)) {
                $this->mixScript('notify("Responsable de Seguimiento: El número de celuar no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->telefonoEvaluacion) && !$this->esTelefonoValido($this->prm->telefonoEvaluacion)) {
                $this->mixScript('notify("Responsable de Seguimiento: El número telefónico de oficina no es válido, debe ser de 10 dígitos", "error");');
                return;
            }
            if (isset($this->prm->celularEvaluacion) && isset($this->prm->telefonoEvaluacion) && $this->telefonosIguales($this->prm->celularEvaluacion, $this->prm->telefonoEvaluacion)) {
                $this->mixScript('notify("Responsable de Seguimiento: El teléfono de oficina coincide con el teléfono celular, no pueden ser iguales", "error");');
                return;
            }
            if (isset($this->prm->extensionEvaluacion) && !$this->esExtensionValida($this->prm->extensionEvaluacion)) {
                $this->mixScript('notify("Responsable de Seguimiento: La extensión solo debe contener números", "error");');
                return;
            }

            $contactos = ValidacionContactoUrUe::contactosDesdePrmUr($this->prm);
            if (count($contactos) > 0) {
                $validacionDuplicados = ValidacionContactoUrUe::validarLista(
                    $contactos,
                    [],
                    [],
                    [
                        'gpo_id'  => $this->clasAdm_gpo,
                        'ur_id'   => $this->clasAdm_ur,
                        'alcance' => 'ur_y_ues',
                    ]
                );
                if (!$validacionDuplicados['ok']) {
                    $this->notifyErrorPlanUR($validacionDuplicados['mensaje']);

                    return;
                }
            }

            UnidadResponsable::abc($this->prm);

            // Todos los status de la Unidad Ejecutora serán los mismos que los de la UR
            // Marcos: 9 de abril.
            // Misma razón que la comentada arriba

            $ur = new UnidadResponsable();
            $ur->setGpo_id($this->clasAdm_gpo);
            $ur->setUr_id($this->clasAdm_ur);
            $ur->select();

            $this->conectar();
            // AQUI:
            // Igual, comentar éstas dos líneas
            // Marcos: 21/octubre/2015
            // Cuando la planeación ya tenga el status de AUTORIZADA (4) ya no cambiaremos su status al guardar
            // Cuando no, sigue el comportamiento "normal" de poa
            if ($status < 4) {
                $sql = 'UPDATE ' . BD_CTRL . '.cat_UnidadEjecutora SET eue_id = ? WHERE gpo_id = ? AND ur_id = ? ;';
                $params = [$this->prm->eur_id, $this->clasAdm_gpo, $this->clasAdm_ur];
                $this->setSql($sql);
                $this->consultar($params);
            }

            $this->mixScript('
                notify("Identificación Institucional actualizada exitosamente");
                comunica( "' . $this->pantalla . '" );');

            //Cuando se detecte una modificación en el nombre del Titular UR o Titular Administrativo, el sistema emita una alerta indicando al usuario que se detectaron modificaciones en los nombres registrados y que su usuario será bloqueado
            $nombreTitular = $this->ur->getNombreTitular();
            $nombreTitularUpdate = $this->prm->nombreTitular;
            $correoElectronicoTU = $this->ur->getCorreoElectronicoTitular();
            $celularTU = $this->ur->getCelularTitular();
            $nombreAdmvo = $this->ur->getNombreAdministrativo();
            $nombreAdmvoUpdate = $this->prm->nombreAdministrativo;
            $correoElectronicoTA = $this->ur->getCorreoElectronicoAdministrativo();
            $celularTA = $this->ur->getCelularAdministrativo();
            $mensaje = 'Se detectaron modificaciones en el nombre del';
            $mismaPersona = false;
            $cambioTitular = false;
            $cambioAdmvo = false;

            if (!empty($nombreTitular) || !empty($nombreAdministrativo)) {
                if (!empty($nombreTitular) && !empty($nombreTitularUpdate)) {
                    $cambioTitular = $this->huboCambio($nombreTitular, $nombreTitularUpdate);
                }
                if (!empty($nombreAdmvo) && !empty($nombreAdmvoUpdate)) {
                    $cambioAdmvo = $this->huboCambio($nombreAdmvo, $nombreAdmvoUpdate);
                }
                if ($cambioTitular || $cambioAdmvo) {
                    //Verificamos si el Titular UR y el Titular Administrativo es la misma persona
                    $mismaPersona = $this->mismaPersona($nombreTitular, $nombreAdmvo);
                    $usuarioTitular = $this->obtineUsuario($this->clasAdm_gpo, $this->clasAdm_ur, $correoElectronicoTU, $celularTU);
                    $usuarioAdmvo = $this->obtineUsuario($this->clasAdm_gpo, $this->clasAdm_ur, $correoElectronicoTA, $celularTA);
                    $usr_idTitular = 0;
                    $usr_usuarioTitular = '';
                    $usr_idAdmvo = 0;
                    $usr_usuarioAdmvo = '';
                    $banderaMensajeUsuario = false;

                    if ($cambioTitular) {
                        if (!empty($usuarioTitular)) {
                            $banderaMensajeUsuario = true;
                            $mensaje .= ' Titular UR';
                            $usr_idTitular = $usuarioTitular['usr_id'];
                            $usr_usuarioTitular = $usuarioTitular['usr_usuario'];
                        }
                    }

                    if ($cambioAdmvo) {
                        if (!empty($usuarioAdmvo)) {
                            $mensaje .= ($banderaMensajeUsuario) ? ' y' : '';
                            $mensaje .= ' Titular Administrativo';
                            $banderaMensajeUsuario = true;
                            $usr_idAdmvo = $usuarioAdmvo['usr_id'];
                            $usr_usuarioAdmvo = $usuarioAdmvo['usr_usuario'];
                        }
                    }

                    if ($banderaMensajeUsuario) {
                        $mensaje .= ', su usuario será bloqueado.';
                        $this->mixScript('
                            alertify.alert(
                                "",
                                "<h5>' . $mensaje . '</h5>",
                                function () {
                                    comunica("' . $this->pantalla . '",{
                                        metodo:"bloquearUsuario",
                                        titular:' . $usr_idTitular . ',
                                        titularNombre:"' . $usr_usuarioTitular . '",
                                        administrativo:' . $usr_idAdmvo . ',
                                        administrativoNombre:"' . $usr_usuarioAdmvo . '",
                                        
                                     });
                                }
                            );
                        ');
                    }
                }
            }

        } else {
            $this->mixScript('notify("Poor Cyrus...", "error");');
        }
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Guardamos la planeación institucional
    OJO: Sólo usuarios de nivel 3 (UR) o los que tengan permisos de escritura
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function enviar()
    {

        $this->prm->abc = 'c';
        $this->prm->eur_id = 2;
        $this->prm->gpo_id = $this->clasAdm_gpo;
        $this->prm->ur_id = $this->clasAdm_ur;
        $this->prm->bit_observacion = 'IDENTIFICACIÓN INSTITUCIONAL UR ENVIADA PARA SU VALIDACIÓN';
        UnidadResponsable::abc($this->prm);

        // Todos los status de la Unidad Ejecutora serán los mismos que los de la UR
        $sql = 'UPDATE ' . BD_CTRL . '.cat_UnidadEjecutora SET eue_id = "2" WHERE gpo_id = ? AND ur_id = ?;';
        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();

        $this->mixScript(
            '
            notify("Identificación Institucional enviada a Finanzas para validación");
            comunica( "' . $this->pantalla . '" );'
        );
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Autorizamos la planeación institucional
    OJO: Sólo usuarios de nivel >= 4 (de Analista para arriba ) Y que tengan permisos de autorizacion
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function autorizar()
    {

        $this->prm->abc = 'c';

        // AQUI: Cambiar ésto porque se aplica en el ejercicio

        // 10 de enero 2017
        // Juan Cabrera de Egresos viene personalmente a autorizar éste movimiento a partir de hoy a las 12:09 del día
        // posteriormente mandará el ticket

        // Marcos: 24/feb/2016
        // Por órdenes de Zuli, y bajo amenaza de que amanezca sin apendice,
        // se dejará el status 5
        // ( vivo con miedo desde la operación )

        // Obtenemos el estado del costeo, se comentan las lineas 1086-1093, 08-03-2017 Ticket 143254
        $sql = 'SELECT ur.ur_estadoCosteo FROM ' . BD_CTRL . '.cat_UnidadResponsable ur WHERE ur.gpo_id = ? AND ur.ur_id = ? ;';
        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $datoCosteo = $this->extraer();
        $this->desconectar();

        $status = $datoCosteo['ur_estadoCosteo'];
        if ($status == 1) {
            $this->prm->eur_id = 4;
        }

        if ($status == 2) {
            $this->prm->eur_id = 5;
        }

        // Marcos: 11/jul/2018
        // Que dice mi mamá que el estado es 4, no 5, porque aún no estamos en el ejercicio

        $this->prm->gpo_id = $this->clasAdm_gpo;
        $this->prm->ur_id = $this->clasAdm_ur;
        $this->prm->bit_observacion = 'SE AUTORIZÓ LA IDENTIFICACIÓN INSTITUCIONAL UR';
        UnidadResponsable::abc($this->prm);

        // Todos los status de la Unidad Ejecutora serán 6 = planeación validada
        $sql2 = 'UPDATE ' . BD_CTRL . '.cat_UnidadEjecutora SET eue_id = "6" WHERE gpo_id = ? AND ur_id = ? ;';
        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $this->conectar();
        $this->setSql($sql2);
        $this->consultar($params);
        $this->desconectar();
        $this->mixScript(
            '
            notify("Se ha autorizado la Planeación Institucional UR");
            comunica( "' . $this->pantalla . '" );'
        );
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Desvalidamos la planeación institucional
    OJO: Sólo usuarios de nivel >= 4 (de Analista para arriba ) Y que tengan permisos de autorizacion
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function desvalidar()
    {
        $this->prm->abc = 'c';
        $this->prm->eur_id = '1';
        $this->prm->gpo_id = $this->clasAdm_gpo;
        $this->prm->ur_id = $this->clasAdm_ur;
        $this->prm->bit_observacion = 'SE DESVALIDÓ LA IDENTIFICACIÓN INSTITUCIONAL UR PREVIAMENTE VALIDADA';
        UnidadResponsable::abc($this->prm);

        // Todos los status de la Unidad Ejecutora serán los mismos que los de la UR
        $sql = 'UPDATE ' . BD_CTRL . '.cat_UnidadEjecutora SET eue_id = "1" WHERE gpo_id = ? AND ur_id = ? ;';
        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $this->mixScript(
            '
            notify("Se ha desvalidado la Identificación Institucional UR");
            comunica( "' . $this->pantalla . '" );'
        );
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Rechazar la planeación institucional
    OJO: Sólo usuarios de nivel >= 4 (de Analista para arriba ) Y que tengan permisos de autorizacion
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function rechazar()
    {

        $this->prm->abc = 'c';
        $this->prm->eur_id = '3';
        $this->prm->gpo_id = $this->clasAdm_gpo;
        $this->prm->ur_id = $this->clasAdm_ur;
        $this->prm->bit_observacion = strtoupper($this->prm->m);
        UnidadResponsable::abc($this->prm);

        // Todos los status de la Unidad Ejecutora serán los mismos que los de la UR
        $sql = 'UPDATE ' . BD_CTRL . '.cat_UnidadEjecutora SET eue_id = "3" WHERE gpo_id = ? AND ur_id = ? ;';
        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->mixScript(
            '
            notify("Se ha Rechazado la Identificación Institucional UR", "danger");
            comunica( "' . $this->pantalla . '" );'
        );
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Cuando rechacen la planeación institucional, deben indicar porqué ha sido rechazada
    OJO: Sólo usuarios de nivel >= 4 (de Analista para arriba ) Y que tengan permisos de autorizacion
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function addComentario()
    {

        $this->setContenedor('modalComentario');

        $textArea = new stdClass();
        $textArea->id = 'rP';
        $textArea->label = 'Motivo de rechazo:';
        $textArea->required = true;
        $textArea->enDiv = true;
        $textArea->maxlength = 500;
        $textArea->width = '300px';

        $textArea->height = '100px';

        $modal = '<div id="cerrarMotivoRechazo" class="modal in modal-al40porciento" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #9d2449;">
                        <h4 class="modal-title" id="myModalLabel">Agregar motivo de rechazo</h4>
                        <button type="button" class="btn-close cerrar" id="btnCerrarMotivoRechazo" data-dismiss="modal" aria-label="Close" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                        <form id="Comentario">
                        ' . $this->ponTextArea($textArea) . '
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-danger" id="btnGuardarObs" form=""><i class="fa-solid fa-trash me-2"></i>Rechazar Identificación Institucional UR</button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelarMotivoRechazo"><i class="fa fa-times me-2"></i>Cancelar</button>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);

        $this->mixScript('

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                $("#cerrarMotivoRechazo").remove();
                e.stopPropagation();
            });

            $("#btnCerrarMotivoRechazo, #cancelarMotivoRechazo")
                .on("click", function() { $("#cerrarMotivoRechazo").remove(); });
            $("#btnGuardarObs").click(function(){
                // El campo no puede quedar vacío
                if ( $("#rP" ).val().trim().length == 0 ) {
                    notify( "El motivo del rechazo no puede quedar vacío", "warning" );
                    setTimeout( function() { $( "#rP" ).focus() }, 200 );
                    return false;
                } else {
                    comunica( "' . $this->pantalla . '", { metodo:"rechazar", m: $("#rP" ).val() } );
                    return false;
                }
            });

            setTimeout( function() { $( "#rP" ).focus() }, 200 );
        ');
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Mostramos el historial de los cambios a la Planeación Institucional
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function verHistorial()
    {

        $tabla = new stdClass();
        $tabla->id = 'tblHistorial';
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaHistorial';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Estado';
        $th->width = '200px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Usuario';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Observaciones';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha';
        $th->width = '100px';
        array_push($tr, $th);

        $trbody = [];
        //contenido clave
        $td = new stdClass();
        $td->campoTxt = 'eur_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'bit_observacion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_fecha';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->setContenedor('modalComentario');

        $this->mixHtml('
            <div id="myModalMeta" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #9d2449;">
                            <h4 class="modal-title" id="myModalLabel">Historial de la Identificación Institucional UR</h4>
                            <button type="button" class="btn btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $this->wrPonTabla($tabla) . '</div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript(
            '
            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                $("#myModalMeta").remove();
                e.stopPropagation();
            });
            $(".cerrar").click(function(){ $("#myModalMeta").remove(); });
            $("#myModalMeta").modal().show();
            '
        );

        $this->llenaHistorial();
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Llenamos la tabla del historial
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function llenaHistorial()
    {
        $sql = "SELECT e.eur_nombre, u.usr_usuario, b.bit_observacion, DATE_FORMAT(b.usr_fecha,'%d/%b/%y') AS usr_fecha
            FROM " . BD_BITACORA . '.bit_UnidadResponsable b
            INNER JOIN ' . BD_CTRL . '.cat_Usuario  u USING (usr_id)
            INNER JOIN ' . BD_CTRL . '.cat_EstadoUr e USING (eur_id)
            WHERE b.gpo_id= ? AND b.ur_id= ?
            ORDER BY b.usr_fecha
            ;';

        $parametros = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $this->setParams($parametros);

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblHistorial';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Subir el archivo de Decreto (PDF)
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function upload()
    {
        $ur_clave = $this->gpo_id . $this->ur_id;
        $name = $this->prm->tipo;
        $folder = str_replace('File', '', $name);
        $respuesta = new stdClass();

        if (!in_array($name, ['internoFile', 'estructuraorganicaFile','legalFile','decretoFile'], true)) {
            $this->setStatus('error');
            $respuesta->mensaje = 'No tienes permisos para realizar ésta operación';
            $this->setValidacion($respuesta);
            return;
        }

        if (!isset($_FILES[$name]) || $_FILES[$name]['error'] != 0) {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrió un error al cargar el archivo';
            $this->setValidacion($respuesta);
            return;
        }

        // Validamos la extensión y el tipo MIME ANTES de tocar el sistema de archivos
        // para no renombrar el PDF previo cuando el nuevo archivo no es válido.
        $extension = strtolower((string) pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION));
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = (string) finfo_file($finfo, $_FILES[$name]['tmp_name']);
                finfo_close($finfo);
            }
        }
        if ($extension !== 'pdf' || ($mime !== '' && $mime !== 'application/pdf')) {
            $this->setStatus('error');
            $respuesta->mensaje = 'El tipo de archivo no es permitido. Solo se aceptan archivos PDF.';
            $this->setValidacion($respuesta);
            return;
        }

        // Validamos el tamaño máximo (6MB declarado en la etiqueta del formulario)
        $tamMaximo = 6 * 1024 * 1024;
        if ((int) $_FILES[$name]['size'] > $tamMaximo && $this->prm->tipo == "internoFile") {
            $this->setStatus('error');
            $respuesta->mensaje = 'El archivo excede el tamaño máximo permitido de 6MB.';
            $this->setValidacion($respuesta);
            return;
        }

        $up = new FicheroSefip();
        $up->setTipos(['pdf']);
        $up->setModulo('poa');
        $up->setfile($ur_clave . '_' . $folder . '.pdf');
        $up->setRuta($folder);
        $up->setUrFichero($ur_clave);
        $up->setAnioFichero(APP_EJERCICIO);

        if (isset($_FILES[$name])) {
            $up->setFichero($_FILES[$name]);

            if ($up->upload()) {
                $this->setStatus('success');
                switch ($folder) {
                    case 'decreto':
                        $this->ur->setDecretoArchivo($up->getFile());
                        break;
                    case 'interno':
                        $this->ur->setInternoArchivo($up->getFile());
                        break;
                    case 'estructuraorganica':
                        $this->ur->setOrganigramaArchivo($up->getFile());
                        break;
                    case 'firma':
                        $this->ur->setOrganigramaArchivo($up->getFile());
                        break;
                    case 'legal':
                        $this->ur->setInternoPeriodico($up->getFile());
                        break;
                }
                $this->ur->update();
                $this->setStatus('success');
                $respuesta->mensaje = 'Archivo cargado con éxito';
                $respuesta->nombre = $up->getFile();
            } else {
                $this->setStatus('error');
                $respuesta->mensaje = $up->getMensaje();
            }
        }
        $this->setValidacion($respuesta);
    }

    private function uploadFirma()
    {
        $ur_clave = $this->gpo_id . $this->ur_id;
        $name = 'archivoFirma';
        $respuesta = new stdClass();
        $docFirma = isset($this->prm->firma) ? $this->prm->firma : '';
        $columnasFirmasPermitidas = [
            'nombramientoTitular', 'identificacionTitular', 'firmaTitular', 'gafeteTitular', 
            'nombramientoAdministrativo', 'identificacionAdministrativo', 'firmaAdministrativo',
            'gafeteAdministrativo', 'nombramientoPlaneacion', 'identificacionPlaneacion',
            'firmaPlaneacion', 'gafetePlaneacion', 'nombramientoEvaluacion',
            'identificacionEvaluacion', 'firmaEvaluacion', 'gafeteEvaluacion',
            'nombramientoDelegatorioTitular', 'identificacionDelegatorioTitular',
            'firmaDelegatorioTitular', 'acuerdoDelegatorioTitular',
            'nombramientoDelegatorioAdministrativo', 'identificacionDelegatorioAdministrativo',
            'firmaDelegatorioAdministrativo', 'acuerdoDelegatorioAdministrativo'
        ];

        if (!in_array($docFirma, $columnasFirmasPermitidas, true)) {
            $this->setStatus('error');
            $respuesta->mensaje = 'No tienes permisos para realizar ésta operación';
            $this->setValidacion($respuesta);
            return;
        }

        if (!isset($_FILES[$name]) || $_FILES[$name]['error'] != 0) {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrió un error al cargar el archivo';
            $this->setValidacion($respuesta);
            return;
        }

        // Validamos la extensión y el tipo MIME ANTES de tocar el sistema de archivos
        // para no renombrar el PDF previo cuando el nuevo archivo no es válido.
        $extension = strtolower((string) pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION));
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = (string) finfo_file($finfo, $_FILES[$name]['tmp_name']);
                finfo_close($finfo);
            }
        }
        if ($extension !== 'pdf' || ($mime !== '' && $mime !== 'application/pdf')) {
            $this->setStatus('error');
            $respuesta->mensaje = 'El tipo de archivo no es permitido. Solo se aceptan archivos PDF.';
            $this->setValidacion($respuesta);
            return;
        }

        $up = new FicheroSefip();
        $up->setTipos(['pdf']);
        $up->setFichero($_FILES[$name]);
        $up->setModulo('poa');
        $up->setfile($ur_clave . '_' . $this->prm->firma . '.pdf');
        $up->setUrFichero($ur_clave);
        $up->setAnioFichero(APP_EJERCICIO);
        $up->setRuta('firmas');

        $nomDir = $up->getRutaMod();

        if ($nomDir === false && $up->getError() === true) {
            $this->setStatus('error');
            $respuesta->mensaje = $up->getMsg();
        } else {
            $nomFile = $ur_clave . '_' . $this->prm->firma;
            $nomFileDb = $_FILES[$name]['name'];
            $countArchivos = 0;

            foreach (glob($nomDir . $nomFile . '*.pdf') as $nombre_fichero) {
                $countArchivos++;
            }

            if ($countArchivos > 0) {
                $bolrename = rename($nomDir . $nomFile . '.pdf', $nomDir . $nomFile . '-' . $countArchivos . '.pdf');
            }
            if ($up->upload()) {
                $sql = ' UPDATE ' . BD_CTRL . '.cat_UnidadResponsableFirma SET urf_' . $this->prm->firma . ' = ? WHERE gpo_id = ? AND ur_id = ? ;';
                $params = [$nomFileDb, $this->clasAdm_gpo, $this->clasAdm_ur];
                $this->conectar();
                $this->setSql($sql);
                $this->consultar($params);
                $this->desconectar();
                $this->setStatus('success');

                $respuesta->mensaje = ' Archivo cargado con éxito';
                $respuesta->nombre = $nomFileDb;
            } else {
                $this->setStatus('error');
                $respuesta->mensaje = $up->getMensaje();
            }
        }
        $this->setValidacion($respuesta);
    }

    private function verHistorialArchivo()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $this->mixHtml('
            <div id="myModalMeta" class="modal in modal-al40porciento" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog shadow-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close btn btn-xs btn-danger cerrar" data-dismiss="modal" aria-hidden="true">x</button>
                            <h4 class="modal-title" id="myModalLabel">Lista de Archivos Subidos para : ' . $this->prm->archivo . '</h4>
                        </div>
                        <div class="modal-body">
        ');
        foreach (glob(dirname(__FILE__) . '/firmas/' . $this->clasAdm_gpo . $this->clasAdm_ur . '_' . $this->prm->archivo . '*.pdf') as $nombre_fichero) {
            $nombre_fichero = str_replace('/var/www/sinpres2015/poa/firmas/', '', $nombre_fichero);
            $this->mixHtml('<a tabindex=-1 href="poa/firmas/' . $nombre_fichero . '?npi=' . rand(1, 99999) . '" target="_blank">' . $nombre_fichero . '</a><br><br>');
        }
        $this->mixHtml('</div>
                        <div class="modal-footer"></div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixSCript('
            $(".cerrar").click(function(){ $("#myModalMeta").remove(); });
            $("#myModalMeta").modal().show();
        ');
    }
    /*
    PONEMOS EN CICLOS LA GENERACIÓN DEL HTML PARA PEDIR LOS DATOS DE PERSONAS Y SUS FIRMAS, YA
    QUE SON MUCHOS CAMPOS QUE TIENEN LOS MISMOS DATOS, ASÍ QUE LO PONEMOS EN UN FOR{}
    EL CAMBIO ÉSTE AÑO ES QUE ÉSTOS VALORES ESTÁN EN UN ARRAY, Y NO EN PEDIR EN UNA CONSULA LA
    ESTRUCTURA DE LA TABLA USANDO EL COMANDO DESC
     */
    private function dameHtmlPersonasDocumentos($status)
    {

        $secciones = ['seccion3', 'seccion4', 'seccion5', 'seccion6'];
        $personas = ['Titular', 'Administrativo', 'Planeacion', 'Evaluacion'];
        $tituloSeccion = ['Titular', 'Administrativo', 'Planeación', 'Seguimiento'];
        $documentos = [
            'nombramiento', 'identificacion', 'firma', 'gafete', 'nombramientoDelegatorio', 'identificacionDelegatorio', 'firmaDelegatorio', 'acuerdoDelegatorio',
        ];
        $labelDocumentos = [
            'Nombramiento o documento que lo acredite *',
            'Identificación oficial *',
            'Formato de legalización de firmas *',
            'Gafete Institucional',
            'Nombramiento o documento que lo acredite',
            'Identificación oficial',
            'Formato de legalización de firmas',
            'Ejemplar de la publicación del acuerdo delegatorio',
        ];

        $strHtml = '';

        $strReadOnly = '';
        if (! $this->escritura) {
            if ($status < 4) {
                $strReadOnly = ' readonly="true" ';
            }
        }

        $numeroPersonas = count($personas);
        for ($p = 0; $p < $numeroPersonas; $p++) {

            // VALORES DE LAS ENTIDADES
            $nombre = '';
            $cargo = '';
            $correoElectronico = '';
            $telefono = '';
            $titulo = '';
            $celular = '';
            $extension = '';
            switch ($p) {
                case 0:
                    // TITULAR
                    $nombre = $this->ur->getNombreTitular();
                    $cargo = $this->ur->getCargoTitular();
                    $correoElectronico = $this->ur->getCorreoElectronicoTitular();
                    $telefono = $this->ur->getTelefonoTitular();
                    $titulo = $this->ur->getTituloTitular();
                    $celular = $this->ur->getCelularTitular();
                    $extension = $this->ur->getExtensionTitular();
                    break;
                case 1:
                    // ADMINISTRATIVO
                    $nombre = $this->ur->getNombreAdministrativo();
                    $cargo = $this->ur->getCargoAdministrativo();
                    $correoElectronico = $this->ur->getCorreoElectronicoAdministrativo();
                    $telefono = $this->ur->getTelefonoAdministrativo();
                    $titulo = $this->ur->getTituloAdministrativo();
                    $celular = $this->ur->getCelularAdministrativo();
                    $extension = $this->ur->getExtensionAdministrativo();
                    break;
                case 2:
                    // PLANEACIÓN
                    $nombre = $this->ur->getNombrePlaneacion();
                    $cargo = $this->ur->getCargoPlaneacion();
                    $correoElectronico = $this->ur->getCorreoElectronicoPlaneacion();
                    $telefono = $this->ur->getTelefonoPlaneacion();
                    $titulo = $this->ur->getTituloPlaneacion();
                    $celular = $this->ur->getCelularPlaneacion();
                    $extension = $this->ur->getExtensionPlaneacion();
                    break;
                case 3:
                    // EVLUACION
                    $nombre = $this->ur->getNombreEvaluacion();
                    $cargo = $this->ur->getCargoEvaluacion();
                    $correoElectronico = $this->ur->getCorreoElectronicoEvaluacion();
                    $telefono = $this->ur->getTelefonoEvaluacion();
                    $titulo = $this->ur->getTituloEvaluacion();
                    $celular = $this->ur->getCelularEvaluacion();
                    $extension = $this->ur->getExtensionEvaluacion();
                    break;
            }

            $strHtml .= '
                <div id="' . $secciones[$p] . '" style="display: none;">
                    <fieldset class="row m-b-10" >
                        <div class="col-md-12 col-xs-12">
                            <h4>' . $tituloSeccion[$p] . '</h4>
                        </div>
                        <fieldset class="col-md-2 col-xs-12 mb-4">
                            <label class="control-label" for="titulo' . $personas[$p] . '">Título *</label>
                            <input class="form-control input-xxlarge" id="titulo' . $personas[$p] . '" name="titulo' . $personas[$p] . '" maxlength="200" value="' . $titulo . '" ' . $strReadOnly . ' type="text" size="200">
                        </fieldset>
                        <fieldset class="col-md-5 col-xs-12 mb-4">
                            <label class="control-label" for="nombre' . $personas[$p] . '">Nombre *</label>
                            <input class="form-control input-xxlarge" id="nombre' . $personas[$p] . '" name="nombre' . $personas[$p] . '" maxlength="200" value="' . $nombre . '" ' . $strReadOnly . ' type="text" size="200">
                        </fieldset>
                        <fieldset class="col-md-5 col-xs-12 mb-4">
                            <label class="control-label" for="cargo' . $personas[$p] . '">Cargo *</label>
                            <input class="form-control input-xxlarge" id="cargo' . $personas[$p] . '" name="cargo' . $personas[$p] . '" maxlength="200" value="' . $cargo . '" ' . $strReadOnly . ' type="text" size="200">
                        </fieldset>
                    </fieldset>

                    <fieldset class="row m-b-10">
                        <fieldset class="col-md-5 col-xs-12 mb-4">
                            <label class="control-label" for="email' . $personas[$p] . '">Correo electrónico *</label>
                            <input class="form-control input-xxlarge validarEmail" id="email' . $personas[$p] . '" name="email' . $personas[$p] . '" maxlength="100" value="' . $correoElectronico . '" ' . $strReadOnly . ' type="text" size="100" style="text-transform: none;">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-12 mb-4">
                            <label class="control-label" for="celular' . $personas[$p] . '">Teléfono Celular *</label>
                            <input class="form-control input-xxlarge validarTelefono" id="celular' . $personas[$p] . '" name="celular' . $personas[$p] . 'itular" maxlength="10" value="' . $celular . '" ' . $strReadOnly . ' type="text" size="10">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-12 mb-4">
                            <label class="control-label" for="telefono' . $personas[$p] . '">Teléfono de Oficina *</label>
                            <input class="form-control input-xxlarge validarTelefono" id="telefono' . $personas[$p] . '" name="telefono' . $personas[$p] . 'itular" maxlength="10" value="' . $telefono . '" ' . $strReadOnly . ' type="text" size="10">
                        </fieldset>
                        <fieldset class="col-md-1 col-xs-12 mb-4">
                            <label class="control-label" for="extension' . $personas[$p] . '">Extensión *</label>
                            <input class="form-control input-xxlarge" id="extension' . $personas[$p] . '" name="extension' . $personas[$p] . 'itular" maxlength="05" value="' . $extension . '" ' . $strReadOnly . ' type="text" size="5">
                        </fieldset>
                    </fieldset>
                    <fieldset class="row m-b-10">
            ';

            // PEDIMOS LOS DATOS DE LAS FIRMAS... ÉSTO DEBE DESAPARECER CUANDO YA
            // ESTEMOS CONSUMIENDO LAS ENTIDADES COMO DEBE SER
            $sql = 'SELECT
                    urf_nombramientoTitular,
                    urf_identificacionTitular,
                    urf_firmaTitular,
                    urf_gafeteTitular,

                    urf_nombramientoDelegatorioTitular,
                    urf_identificacionDelegatorioTitular,
                    urf_firmaDelegatorioTitular,
                    urf_acuerdoDelegatorioTitular,

                    urf_nombramientoAdministrativo,
                    urf_identificacionAdministrativo,
                    urf_firmaAdministrativo,
                    urf_gafeteAdministrativo,

                    urf_nombramientoDelegatorioAdministrativo,
                    urf_identificacionDelegatorioAdministrativo,
                    urf_firmaDelegatorioAdministrativo,
                    urf_acuerdoDelegatorioAdministrativo,

                    urf_nombramientoPlaneacion,
                    urf_identificacionPlaneacion,
                    urf_firmaPlaneacion,
                    urf_gafetePlaneacion,

                    urf_nombramientoEvaluacion,
                    urf_identificacionEvaluacion,
                    urf_firmaEvaluacion,
                    urf_gafeteEvaluacion

                FROM cat_UnidadResponsableFirma
                WHERE gpo_id = ? AND ur_id = ? ;';

            $params = [$this->clasAdm_gpo, $this->clasAdm_ur];

            $this->conectar();
            $this->setSql($sql);

            // ERROR: QUITAR del select EL " AS " en urf_ de planeación, ya que no estaban las tablas 2017 se puso así

            $this->consultar($params);
            $firmasUr = $this->extraer();

            $numeroDocumentos = count($documentos);
            for ($d = 0; $d < $numeroDocumentos; $d++) {
                if ($d == 0) {
                    $strHtml .= '
                    <div class="col-md-12 col-xs-12 mb-3">
                        <h4>Registro de Firma</h4>
                    </div>
                    ';
                }

                // SI SON LOS DATOS DE PLANEACIÓN, ÉSTE NO LLEVA ACUERDO DELEGATORIO,
                // PORQUE NO PUEDE DELEGAR SU FIRMA, ASÍ QUE TERMINAMOS AQUÍ
                if ($d == 4) {
                    if ($p == 2 || $p == 3) {
                        $strHtml .= '</fieldset><fieldset class="row m-b-10">';
                        break;
                    } else {
                        $strHtml .= '</fieldset>
                            <fieldset class="row">
                            <div class="col-md-12 col-xs-12 mb-4">
                                <h4>Acuerdo delegatorio</h4>
                            </div>';
                    }
                }

                if ($d == 7) {
                    $strHtml .= '</fieldset><fieldset class="row mb-4">';
                }

                $id = $documentos[$d] . $personas[$p];

                $link = '<a style="color:blue;" class="lnk_ext m-l-5" id="nameFilename' . $id . '" tabindex=-1 lnk="firmas/' . $this->clasAdm_gpo . $this->clasAdm_ur . '_' . $id . '.pdf?npi=' . rand(1, 99999) . '" target="_blank" class="left">';
                $link .= (empty($firmasUr['urf_' . $id])) ? 'Sin archivo' : $firmasUr['urf_' . $id];
                $link .= '</a>';

                // Para los niveles mayores a UR ( o sea los de finanzas ) mostraremos un
                // botón para ver el historial de cambios en el archivo
                if ($this->nvj_id > 2) {
                    $btnHistorial = '';
                } else {
                    $btnHistorial = '';
                }

                $strHtml .= '
                    <div class="col-md-4 col-xs-12 mb-4" attr="' . $d . '">
                        <label class="control-label" for="' . $id . '">' . $btnHistorial . $labelDocumentos[$d] . '</label>
                ';

                #Si la planeacion esta enviada a validacion o el usuario es de nivel superior a la UR no se puede subir archivos y solamente se vera el link en caso de que ya se haya subido alguno
                if ($status == 2 || $this->nvj_id > 2) {
                    $strHtml .= $link;
                } else {

                    $strHtml .= '
                        <input type="file" name ="archivoFirma" id="' . $id . '" 
                            accept="application/pdf,.pdf" data-url="' . $this->pantalla . '"
                            data-form-data=\'{"metodo":"uploadFirma", "firma":"' . $id . '"}\'/>
                        ' . $link . '
                        <div id="progress' . $id . '" class="progress progress-striped mt-2" style="height: 14px;">
                            <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                <span class="sr-only">0% Complete</span>
                            </div>
                        </div>
                        <div id="estadoUpload' . $id . '" class="upload-status mt-1" style="display:none; font-size:12px; color:#0d6efd;">
                            <i class="fa fa-circle-notch fa-spin me-1"></i><span class="upload-status-text">Preparando archivo...</span>
                        </div>
                        <div id="overlayUpload' . $id . '" class="upload-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:1080; align-items:center; justify-content:center;">
                            <div style="background:#fff; padding:18px 24px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:12px; min-width:280px;">
                                <i class="fa fa-circle-notch fa-spin" style="font-size:22px; color:#0d6efd;"></i>
                                <div>
                                    <div style="font-weight:600;" class="upload-overlay-title">Subiendo archivo...</div>
                                    <div class="upload-overlay-text" style="font-size:12px; color:#555;">Por favor espere, no cierre esta ventana.</div>
                                </div>
                            </div>
                        </div>
                    ';

                    $this->mixScript('
                        (function(){
                            var $input = $("#' . $id . '");
                            var $progressWrap = $("#progress' . $id . '");
                            var $bar = $progressWrap.find(".progress-bar");
                            var $estado = $("#estadoUpload' . $id . '");
                            var $estadoTxt = $estado.find(".upload-status-text");
                            var $overlay = $("#overlayUpload' . $id . '");
                            var $overlayTxt = $overlay.find(".upload-overlay-text");
                            var $overlayTitle = $overlay.find(".upload-overlay-title");
                            var procesandoTimer = null;

                            function setEstado(texto, color) {
                                $estadoTxt.text(texto);
                                $estado.css("color", color || "#0d6efd").show();
                            }
                            function ocultarEstado() {
                                $estado.hide();
                            }
                            function bloquearUI() {
                                $input.prop("disabled", true);
                                $overlay.css("display", "flex");
                            }
                            function liberarUI() {
                                $input.prop("disabled", false);
                                $overlay.hide();
                                if (procesandoTimer) {
                                    clearTimeout(procesandoTimer);
                                    procesandoTimer = null;
                                }
                            }
                            function pintarBarra(pct, claseExtra) {
                                $bar.css("width", pct + "%").attr("aria-valuenow", pct);
                                $bar.removeClass("bg-success bg-danger progress-bar-animated");
                                if (claseExtra) { $bar.addClass(claseExtra); }
                            }

                            $input.on("click", function(){
                                pintarBarra(0);
                                ocultarEstado();
                            });

                            $input.fileupload({
                                dataType: "json",
                                add: function(e, data) {
                                    var archivo = data.files && data.files[0] ? data.files[0] : null;
                                    if (!archivo) {
                                        notify("No se seleccionó ningún archivo.", "error");
                                        return false;
                                    }
                                    var nombre = (archivo.name || "").toLowerCase();
                                    var tipo = (archivo.type || "").toLowerCase();
                                    var esPdf = nombre.lastIndexOf(".pdf") === nombre.length - 4
                                        && (tipo === "" || tipo === "application/pdf");
                                    if (!esPdf) {
                                        notify("Solo se permiten archivos PDF.", "error");
                                        $input.val("");
                                        return false;
                                    }
                                    /*var maxBytes = 6 * 1024 * 1024;
                                    if (archivo.size > maxBytes) {
                                        notify("El archivo excede el tamaño máximo permitido de 6MB.", "error");
                                        $input.val("");
                                        return false;
                                    }*/
                                    $overlayTitle.text("Subiendo \"" + archivo.name + "\"");
                                    $overlayTxt.text("Por favor espere, no cierre esta ventana.");
                                    data.submit();
                                },
                                start: function() {
                                    bloquearUI();
                                    pintarBarra(0, "progress-bar-animated");
                                    setEstado("Iniciando carga...", "#0d6efd");
                                },
                                progress: function(e, data) {
                                    var progress = parseInt(data.loaded / data.total * 100, 10);
                                    pintarBarra(progress, "progress-bar-animated");
                                    if (progress < 100) {
                                        setEstado("Subiendo archivo... " + progress + "%", "#0d6efd");
                                        $overlayTxt.text("Enviando datos al servidor (" + progress + "%)...");
                                    } else {
                                        setEstado("Procesando archivo en el servidor, espere un momento...", "#0d6efd");
                                        $overlayTxt.text("Procesando archivo en el servidor, espere un momento...");
                                        if (!procesandoTimer) {
                                            procesandoTimer = setTimeout(function(){
                                                $overlayTxt.text("El servidor sigue procesando el archivo, esto puede tardar unos segundos...");
                                            }, 5000);
                                        }
                                    }
                                },
                                done: function(e, data) {
                                    var resultado = data.result || {};
                                    var validacion = resultado.validacion || {};
                                    var estado = resultado.status || "success";
                                    if (validacion.nombre) {
                                        $("#nameFilename' . $id . '").text(validacion.nombre);
                                    }
                                    if (estado === "error") {
                                        pintarBarra(100, "bg-danger");
                                        setEstado(validacion.mensaje || "Ocurrió un error al cargar el archivo.", "#dc3545");
                                        notify(validacion.mensaje || "Ocurrió un error al cargar el archivo.", "error");
                                    } else {
                                        pintarBarra(100, "bg-success");
                                        setEstado(validacion.mensaje || "Archivo cargado con éxito.", "#198754");
                                        notify(validacion.mensaje || "Archivo cargado con éxito.", "success");
                                    }
                                    liberarUI();
                                    setTimeout(ocultarEstado, 4000);
                                },
                                fail: function(e, data) {
                                    var mensajeError = "No fue posible cargar el archivo.";
                                    if (data && data.jqXHR && data.jqXHR.responseJSON
                                        && data.jqXHR.responseJSON.validacion
                                        && data.jqXHR.responseJSON.validacion.mensaje) {
                                        mensajeError = data.jqXHR.responseJSON.validacion.mensaje;
                                    } else if (data && data.errorThrown) {
                                        mensajeError = data.errorThrown;
                                    }
                                    pintarBarra(100, "bg-danger");
                                    setEstado(mensajeError, "#dc3545");
                                    notify(mensajeError, "error");
                                    liberarUI();
                                    setTimeout(ocultarEstado, 5000);
                                },
                                always: function() {
                                    liberarUI();
                                }
                            });
                        })();
                    ');
                }
                $strHtml .= '</div>';
            }

            $strHtml .= '</div>';
        }

        return $strHtml;
    }

    private function download()
    {
        $fichero = '';
        $base = '';
        if (isset($this->prm->lnk)) {
            $tmp = explode('/', $this->prm->lnk);
            $base = $tmp[0];
            $fichero = explode('?', $tmp[1])[0];
        }

        $r = new RutaArchivoSefip();
        $r->setBase('POA');
        $r->setPath($base);
        $r->setAnioFichero(APP_EJERCICIO);
        $r->setUrFichero($this->clasAdm_gpo . $this->clasAdm_ur);
        $ruta = $r->getRutaMod();
        $ruta .= $fichero;
        if (!file_exists($ruta) && !is_file($ruta)) {
            $this->mixScript('
                notify("El archivo solicitado no se encuentra disponible. Es posible que aún no haya sido cargado.", "error");
            ');
            return;
        }

        $dest = __DIR__ . '/../../tmps/' . $fichero;
        if (!@copy($ruta, $dest)) {
            $this->mixScript('notify("No fue posible abrir el archivo en este momento.", "error");');
            return;
        }
        $this->mixScript(' $("#det_fsvis1").attr("src","lib/file_download.php?r=' . $fichero . '");');
    }

    private function esCodigoPostalValido($codigoPostal)
    {
        $codigoPostal = trim((string) $codigoPostal);
        if ($codigoPostal === '') {
            return true;
        }

        return preg_match('/^\d{5}$/', $codigoPostal) === 1;
    }

    private function esExtensionValida($extension)
    {
        $extension = trim((string) $extension);
        if ($extension === '') {
            return true;
        }

        return preg_match('/^\d{1,5}$/', $extension) === 1;
    }

    private function esTelefonoValido($telefono)
    {
        $telefono = preg_replace('/\D/', '', (string) $telefono);
        if ($telefono === '') {
            return true;
        }

        return preg_match('/^\d{10}$/', $telefono) === 1;
    }

    private function obtenerDominiosInstitucionales()
    {
        $dominiosPermitidos = [];
        $sql = 'SELECT dmi_dominio FROM ' . BD_SINPRES . '.cat_DominioInstitucional WHERE dmi_activo = ?;';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([1]);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            while ($data = $this->extraer()) {
                $dominiosPermitidos[] = $data['dmi_dominio'];
            }
        }

        return $dominiosPermitidos;
    }

    private function esCorreoDominioInstitucional($correo, $dominiosPermitidos = null)
    {
        $correo = trim((string) $correo);
        if ($correo === '') {
            return true;
        }
        if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+$/', $correo)) {
            return false;
        }
        $partes = explode('@', $correo);
        if (count($partes) < 2) {
            return false;
        }
        $dominio = strtolower($partes[1]);
        if ($dominiosPermitidos === null) {
            $dominiosPermitidos = $this->obtenerDominiosInstitucionales();
        }
        foreach ($dominiosPermitidos as $d) {
            $d = strtolower((string) $d);
            if ($dominio === $d || substr($dominio, -strlen($d)) === $d) {
                return true;
            }
        }

        return false;
    }

    private function notifyErrorPlanUR($mensaje)
    {
        $mensajeJs = json_encode($mensaje, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->mixScript('
            notify(' . $mensajeJs . ', "error");
        ');
    }

    private function validaMandatoMisional()
    {
        $datosCompletosInformacionUR = false;
        $datosCompletosMisional = false;
        $datosCompletosUEs = false;
        $uesFaltantes = '';

        //Instanciando la Ur
        $ur = new UnidadResponsable();
        $ur->setGpo_id($this->clasAdm_gpo);
        $ur->setUr_id($this->clasAdm_ur);
        $ur->select();

        $sql = 'SELECT
                urf_nombramientoTitular,
                urf_identificacionTitular,
                urf_firmaTitular,
                urf_gafeteTitular,
                urf_nombramientoAdministrativo,
                urf_identificacionAdministrativo,
                urf_firmaAdministrativo,
                urf_gafeteAdministrativo,
                urf_nombramientoPlaneacion,
                urf_identificacionPlaneacion,
                urf_firmaPlaneacion,
                urf_gafetePlaneacion,
                urf_nombramientoEvaluacion,
                urf_identificacionEvaluacion,
                urf_firmaEvaluacion,
                urf_gafeteEvaluacion
            FROM cat_UnidadResponsableFirma
            WHERE gpo_id = ? AND ur_id = ? ;';

        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $firmasUr = $this->extraer();

        $campos = "";
        $banderaII = false;
        $camposII = "<h6>Apartado: Identificación Institucional</h6>- Campo(s):";
        $banderaDUR = false;
        $camposDUR = "<br><h6>Apartado: Domicilio UR</h6>- Campo(s):";
        $camposTUR = "<br><h6>Apartado: Titular UR</h6>- Campo(s):";
        $banderaTUR = false;
        $camposTA = "<br><h6>Apartado: Titular Administrativo</h6>- Campo(s):";
        $banderaTA = false;
        $camposTP = "<br><h6>Apartado: Titular Planeación</h6>- Campo(s):";
        $banderaTP = false;
        $camposRS = "<br><h6>Apartado: Responsable de Seguimiento</h6>- Campo(s):";
        $banderaRS = false;
        
        //Identificación Institucional
        if ($this->esCampoVacio($ur->getInicioPeriodicoOficial())) {
            $banderaII = true; 
            $camposII .= " Documento legal de creación,";
        }
        if ($this->esCampoVacio($ur->getInicioFecha())) {
            $banderaII = true;
            $camposII .= " Fecha de publicación del documento legal de creación,";
        }
        if ($this->esCampoVacio($ur->getInternoPeriodico())) {
            $banderaII = true;
            $camposII .= " Documento digital del documento legal de creación,";
        }
        if ($this->esCampoVacio($ur->getModificacionPeriodico())) {
            $banderaII = true;
            $camposII .= " Periódico oficial de la última modificación o reforma,";
        }
        if ($this->esCampoVacio($ur->getModificacionFecha())) {
            $banderaII = true;
            $camposII .= " Fecha de publicación de la última modificación o reforma ,";
        }
        if ($this->esCampoVacio($ur->getDecretoArchivo())) {
            $banderaII = true;
            $camposII .= " Documento digital del periódico oficial de la última modificación o reforma,";
        }
        if ($this->esCampoVacio($ur->getInternoArchivo())) {
            $banderaII = true;
            $camposII .= " Documento digital del reglamento interno,";
        }
        if ($this->esCampoVacio($ur->getPlaneacionFecha())) {
            $banderaII = true;
            $camposII .= " Fecha de publicación del reglamento interno,";
        }
        if ($this->esCampoVacio($ur->getOrganigramaArchivo())) {
            $banderaII = true;
            $camposII .= " Documento digital de la estructura orgánica,";
        }
        if ($this->esCampoVacio($ur->getValidacionFecha())) {
            $banderaII = true;
            $camposII .= " Fecha de validación de la estructura orgánica,";
        }
        //Verifica si encontro un campo vacio
        if ($banderaII) {
            $camposII = rtrim($camposII, ',');
            $camposII .= "<br>";
        } else {
            $camposII = "";
        }

        //Domicilio UR
        if ($this->esCampoVacio($ur->getDomicilio())) {
            $banderaDUR = true;
            $camposDUR .= " Domicilio,";
        }
        if ($this->esCampoVacio($ur->getTelefono())) {
            $banderaDUR = true;
            $camposDUR .= " Teléfono,";
        }
        if ($this->esCampoVacio($ur->getMun_id())) {
            $banderaDUR = true;
            $camposDUR .= " Municipio,";
        }
        if ($this->esCampoVacio($ur->getCodigoPostal())) {
            $banderaDUR = true;
            $camposDUR .= " Código Postal,";
        }
        if ($this->esCampoVacio($ur->getPaginaWeb())) {
            $banderaDUR = true;
            $camposDUR .= " Página Web,";
        }
        if ($this->esCampoVacio($ur->getCorreoElectronicoContacto())) {
            $banderaDUR = true;
            $camposDUR .= " Correo Electrónico,";
        }
        //Verifica si encontro un campo vacio
        if ($banderaDUR) {
            $camposDUR = rtrim($camposDUR, ',');
            $camposDUR .= "<br>";
        } else {
            $camposDUR = "";
        }

        //Titular UR
        if($this->esCampoVacio($ur->getTituloTitular())) {
            $banderaTUR = true;
            $camposTUR .= " Título,";
        }
        if ($this->esCampoVacio($ur->getNombreTitular())) {
            $banderaTUR = true;
            $camposTUR .= " Nombre,";
        }
        if ($this->esCampoVacio($ur->getCargoTitular())) {
            $banderaTUR = true;
            $camposTUR .= " Cargo,";
        }
        if ($this->esCampoVacio($ur->getCorreoElectronicoTitular())) {
            $banderaTUR = true;
            $camposTUR .= " Correo Electrónico,";
        }
        if ($this->esCampoVacio($ur->getCelularTitular())) {
            $banderaTUR = true;
            $camposTUR .= " Teléfono Celular,";
        }
        if ($this->esCampoVacio($ur->getTelefonoTitular())) {
            $banderaTUR = true;
             $camposTUR .= " Teléfono de Oficina,";
        }
        if ($this->esCampoVacio($ur->getExtensionTitular())) {
            $banderaTUR = true;
            $camposTUR .= " Extensión,";
        }
        if ($this->esCampoVacio($firmasUr['urf_nombramientoTitular'])){
            $banderaTUR = true;
            $camposTUR .= " Nombramiento o documento que lo acredite,";
        }
        if ($this->esCampoVacio($firmasUr['urf_identificacionTitular'])){
            $banderaTUR = true;
            $camposTUR .= " Identificación oficial,";
        }
        if ($this->esCampoVacio($firmasUr['urf_firmaTitular'])){
            $banderaTUR = true;
            $camposTUR .= " Formato de legalización de firmas,";
        }
        //Verifica si encontro un campo vacio
        if ($banderaTUR) {
            $camposTUR = rtrim($camposTUR, ',');
            $camposTUR .= "<br>";
        } else {
            $camposTUR = "";
        }

        //Titular Administrativo
        if($this->esCampoVacio($ur->getTituloAdministrativo())) {
            $banderaTA = true;
            $camposTA .= " Título,";
        }
        if ($this->esCampoVacio($ur->getNombreAdministrativo())) {
            $banderaTA = true;
            $camposTA .= " Nombre,";
        }
        if ($this->esCampoVacio($ur->getCargoAdministrativo())) {
            $banderaTA = true;
            $camposTA .= " Cargo,";
        }
        if ($this->esCampoVacio($ur->getCorreoElectronicoAdministrativo())) {
            $banderaTA = true;
            $camposTA .= " Correo Electrónico,";
        }
        if ($this->esCampoVacio($ur->getCelularAdministrativo())) {
            $banderaTA = true;
            $camposTA .= " Teléfono Celular,";
        }
        if ($this->esCampoVacio($ur->getTelefonoAdministrativo())) {
            $banderaTA = true;
             $camposTA .= " Teléfono de Oficina,";
        }
        if ($this->esCampoVacio($ur->getExtensionAdministrativo())) {
            $banderaTA = true;
            $camposTA .= " Extensión,";
        }
        if ($this->esCampoVacio($firmasUr['urf_nombramientoAdministrativo'])){
            $banderaTA = true;
            $camposTA .= " Nombramiento o documento que lo acredite,";
        }
        if ($this->esCampoVacio($firmasUr['urf_identificacionAdministrativo'])){
            $banderaTA = true;
            $camposTA .= " Identificación oficial,";
        }
        if ($this->esCampoVacio($firmasUr['urf_firmaAdministrativo'])){
            $banderaTA = true;
            $camposTA .= " Formato de legalización de firmas,";
        }
        //Verifica si encontro un campo vacio
        if ($banderaTA) {
            $camposTA = rtrim($camposTA, ',');
            $camposTA .= "<br>";
        } else {
            $camposTA = "";
        }

        //Titular Planeación
        if($this->esCampoVacio($ur->getTituloPlaneacion())) {
            $banderaTP = true;
            $camposTP .= " Título,";
        }
        if ($this->esCampoVacio($ur->getNombrePlaneacion())) {
            $banderaTP = true;
            $camposTP .= " Nombre,";
        }
        if ($this->esCampoVacio($ur->getCargoPlaneacion())) {
            $banderaTP = true;
            $camposTP .= " Cargo,";
        }
        if ($this->esCampoVacio($ur->getCorreoElectronicoPlaneacion())) {
            $banderaTP = true;
            $camposTP .= " Correo Electrónico,";
        }
        if ($this->esCampoVacio($ur->getCelularPlaneacion())) {
            $banderaTP = true;
            $camposTP .= " Teléfono Celular,";
        }
        if ($this->esCampoVacio($ur->getTelefonoPlaneacion())) {
            $banderaTP = true;
             $camposTP .= " Teléfono de Oficina,";
        }
        if ($this->esCampoVacio($ur->getExtensionPlaneacion())) {
            $banderaTP = true;
            $camposTP .= " Extensión,";
        }
        if ($this->esCampoVacio($firmasUr['urf_nombramientoPlaneacion'])){
            $banderaTP = true;
            $camposTP .= " Nombramiento o documento que lo acredite,";
        }
        if ($this->esCampoVacio($firmasUr['urf_identificacionPlaneacion'])){
            $banderaTP = true;
            $camposTP .= " Identificación oficial,";
        }
        if ($this->esCampoVacio($firmasUr['urf_firmaPlaneacion'])){
            $banderaTP = true;
            $camposTP .= " Formato de legalización de firmas,";
        }
        //Verifica si encontro un campo vacio
        if ($banderaTP) {
            $camposTP = rtrim($camposTP, ',');
            $camposTP .= "<br>";
        } else {
            $camposTP = "";
        }

        //Responsable de Seguimiento
        if($this->esCampoVacio($ur->getTituloEvaluacion())) {
            $banderaRS = true;
            $camposRS .= " Título,";
        }
        if ($this->esCampoVacio($ur->getNombreEvaluacion())) {
            $banderaRS = true;
            $camposRS .= " Nombre,";
        }
        if ($this->esCampoVacio($ur->getCargoEvaluacion())) {
            $banderaRS = true;
            $camposRS .= " Cargo,";
        }
        if ($this->esCampoVacio($ur->getCorreoElectronicoEvaluacion())) {
            $banderaRS = true;
            $camposRS .= " Correo Electrónico,";
        }
        if ($this->esCampoVacio($ur->getCelularEvaluacion())) {
            $banderaRS = true;
            $camposRS .= " Teléfono Celular,";
        }
        if ($this->esCampoVacio($ur->getTelefonoEvaluacion())) {
            $banderaRS = true;
             $camposRS .= " Teléfono de Oficina,";
        }
        if ($this->esCampoVacio($ur->getExtensionEvaluacion())) {
            $banderaRS = true;
            $camposRS .= " Extensión,";
        }
        if ($this->esCampoVacio($firmasUr['urf_nombramientoEvaluacion'])){
            $banderaRS = true;
            $camposRS .= " Nombramiento o documento que lo acredite,";
        }
        if ($this->esCampoVacio($firmasUr['urf_identificacionEvaluacion'])){
            $banderaRS = true;
            $camposRS .= " Identificación oficial,";
        }
        if ($this->esCampoVacio($firmasUr['urf_firmaEvaluacion'])){
            $banderaRS = true;
            $camposRS .= " Formato de legalización de firmas,";
        }
        //Verifica si encontro un campo vacio
        if ($banderaRS) {
            $camposRS = rtrim($camposRS, ',');
            $camposRS .= "<br>";
        } else {
            $camposRS = "";
        }

        if ($banderaII || $banderaDUR || $banderaTUR
            || $banderaTA  || $banderaTP || $banderaRS) {
            $campos = $camposII.$camposDUR.$camposTUR.$camposTA.$camposTP.$camposRS;
            $campos = $campos = rtrim($campos, ',');
            $this->mixScript('
                alertify.alert(
                    "Atención", 
                    "Falta capturar información o cargar documentos obligatorios '.$campos.'"
                );
            ');
            return;
        }
        //Instanciando la Ur
        $mandatoMisonal = new UnidadResponsable();
        $mandatoMisonal->setGpo_id($this->clasAdm_gpo);
        $mandatoMisonal->setUr_id($this->clasAdm_ur);
        $mandatoMisonal->select();
        $bandera = 0;
        $mision = $this->esCampoVacio($mandatoMisonal->getMision());
        $vision = $this->esCampoVacio($mandatoMisonal->getVision());
        $objGeneral = $this->esCampoVacio($mandatoMisonal->getObjetivoGeneral());
        $objEstrategico = $this->esCampoVacio($mandatoMisonal->getObjetivoEstrategico());
        $productoInstitucional = $this->esCampoVacio($mandatoMisonal->getProductoInstitucional());
        if (!$mision && !$vision && !$objGeneral && !$objEstrategico && !$productoInstitucional) {
            $datosCompletosMisional = true;
        }

        //Validar las unidads ejecutoras
        // Excepción Ticket #SDS-559336: para las UR 116/901/902 la información de la UE
        // no es obligatoria, por lo que no se valida que las UE tengan campos pendientes.
        $uesFaltantes = ['ues' => null];
        if ($this->exceptaObligatoriosUe) {
            $datosCompletosUEs = true;
        } else {
            $this->conectar();
            $this->setSql("
                SELECT GROUP_CONCAT(ue_id SEPARATOR ', ') AS ues
                FROM
                (
                    SELECT ue_id, ue_nombre, ue_nombreTitular,
                        ue_cargoTitular, ue_objetivo,ue_telefono,
                        ue_celular,ue_extension,ue_gafeteInstitucional,
                        ue_correoInstitucional,ue_nombramiento
                    FROM cat_UnidadEjecutora
                    WHERE gpo_id = ? AND ur_id = ?
                    AND sts_id = 1
                ) AS ues
                WHERE ue_nombre IS NULL OR ue_nombre = ''
                OR ue_nombreTitular IS NULL OR ue_nombreTitular = '' OR ue_nombreTitular = '--'
                OR ue_cargoTitular IS NULL OR ue_cargoTitular = '' OR ue_cargoTitular = '--'
                OR ue_nombramiento IS NULL OR ue_nombramiento = '' OR ue_nombramiento = '--'
                OR ue_correoInstitucional IS NULL OR ue_correoInstitucional = ''
                OR ue_telefono IS NULL OR ue_telefono = ''
                OR ue_extension IS NULL OR ue_extension = ''
                OR ue_celular IS NULL OR ue_celular = '';
            ");
            $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
            $this->consultar($params);
            $uesFaltantes = $this->extraer();
            if ($uesFaltantes['ues'] == null) {
                $datosCompletosUEs = true;
            }
        }

        if ($datosCompletosMisional && $datosCompletosUEs) {
            $this->mixScript("
                alertify.confirm('ENVIAR A VALIDACIÓN LA IDENTIFICACIÓN INSTITUCIONAL', 'Enviar a validación la Información <b>UR</b> también incluye la validación de Información "
                . '<b>UE</b> y Planeación '
                . '<b>UR</b>.<br /><br />Recuerda que mientras Finanzas no responda, no podrás modificar nada en'
                . " las pantallas de Información UR, UE y Planeación UR.<br /><br />¿ Confirmas que deseas enviar a validación la Identificación Institucional ?',
                function(){ comunica( ' $this->pantalla  ', { metodo:'enviar' } );}, function(){ }).set('defaultFocus', 'cancel').set('labels',{ok:'ENVIAR A VALIDACIÓN',cancel:'Cancelar'});
                
                const btnAceptar = document.querySelector('.ajs-button.btn.btn-primary');

                if (btnAceptar) {
                    btnAceptar.classList.remove('btn-primary');
                    btnAceptar.classList.add('btn-success');
                }

            ");
        } else {
            $mensaje = 'Existe información pendiente por capturar en el apartado ';
            if (!$datosCompletosMisional) {
                $mensaje .= '\"Mandato Misional\"';
            }
            if (!$datosCompletosUEs) {
                $mensaje .= (!$datosCompletosMisional) ? ' y en ' : '';
                $mensaje .= '\"Información UE\" en la(s) UE(s): ' . $uesFaltantes['ues'] . '.';
            }
            $this->mixScript('notify("' . $mensaje . '", "error");');
        }
    }

    private function esCampoVacio($campo)
    {
        $vacio = false;
        $campo = trim($campo ?? '');
        if (empty($campo) || $campo == '-') {
            $vacio = true;
        }
        return $vacio;
    }

    private function huboCambio($original, $nuevo)
    {
        if (trim((string)$original) === '') {
            return false;
        }

        return ValidacionContactoUrUe::normalizarNombre($original)
            !==
            ValidacionContactoUrUe::normalizarNombre($nuevo);
    }

    private function mismaPersona($titularUR, $titularAdmvo)
    {
        if (trim((string)$titularUR) === '') {
            return false;
        }

        return ValidacionContactoUrUe::normalizarNombre($titularUR)
            ==
            ValidacionContactoUrUe::normalizarNombre($titularAdmvo);
    }

    private function obtineUsuario($gpo_id, $ur_id, $correo, $celular)
    {
        //Obtenemos el ID del usuario
        $sql = 'SELECT uu.usr_id,uu.usr_usuario
            FROM cat_Usuario u
            INNER JOIN ' . BD_USUARIO . '.cat_Usuario uu USING(usr_id)
            WHERE u.gpo_id = ?
            AND u.ur_id = ?
            AND uu.usr_correo = ?
            AND uu.usr_telefono = ?
            AND uu.sts_id = 1;';
        $params = [$gpo_id,$ur_id,$correo,$celular];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $usuario = $this->extraer();
        $this->desconectar();
        return ($this->getNumeroRegistros() > 0) ? $usuario : [];
    }

    private function bloquearUsuario()
    {
        $titular = $this->prm->titular;
        $titularNombre = $this->prm->titularNombre;
        $administrativo = $this->prm->administrativo;
        $administrativoNombre = $this->prm->administrativoNombre;
        $banderaTitular = false;
        $mensaje = 'El usuario ';
        $observacion = 'USUARIO BLOQUEDO DESDE PANTALL A_1_1';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [':observacion' => $observacion,':ip' => $ip, ':usr' => $_SESSION[_S]['usr_id']];
        $this->conectar();
        $this->setSql('
            SET @bit_observacion = :observacion, 
            @usr_ip = INET_ATON(:ip), @usr_id = :usr;
        ');
        $this->consultar($params);
        if ($titular > 0) {
            $banderaTitular = true;
            $mensaje .= $titularNombre . ' del Titular UR';
            $this->setSql('
                UPDATE ' . BD_USUARIO . '.cat_Usuario u
                INNER JOIN ' . BD_USUARIO . '.cat_VigenciaUsuario vg USING(usr_id)
                SET
                    u.sts_id = 2,
                    vg.sts_id = 2
                WHERE u.usr_id = ? AND u.sts_id = 1 AND vg.sts_id = 1;
            ');
            $this->consultar([$titular]);
        }

        if ($administrativo > 0 && ($administrativo != $titular)) {
            $mensaje .= ($banderaTitular) ? ' y el usuario ' : '';
            $mensaje .= $administrativoNombre . ' del Titular Administrativo';
            $this->conectar();
            $this->setSql('
                UPDATE ' . BD_USUARIO . '.cat_Usuario u
                INNER JOIN ' . BD_USUARIO . '.cat_VigenciaUsuario vg USING(usr_id)
                SET
                    u.sts_id = 2,
                    vg.sts_id = 2
                WHERE u.usr_id = ? AND u.sts_id = 1 AND vg.sts_id = 1
            ');
            $this->consultar([$administrativo]);
        }
        $mensaje .= ' ha sido bloquedo, deberá realizar una nueva solicitud de usuario para continuar utilizando el sistema';
        $this->mixScript('
            alertify.alert().setting({
                "onok": null
            });
            alertify.alert(
                "",
                "<h5>' . $mensaje . '</h5>"
            );
        ');
        $this->desconectar();
    }

    private function telefonosIguales($celular, $telefono)
    {
        $celular = trim((string)$celular);
        $telefono = trim((string)$telefono);
        if ($celular === '' || $telefono === '') {
            return false;
        }

        return $celular === $telefono;
    }
}
if (isset($_SESSION[_S]['prm']['A']['1']['1'])) {
    new ControladorPlanUr();
} else {
    WebResponse::sinPermisos();
}
