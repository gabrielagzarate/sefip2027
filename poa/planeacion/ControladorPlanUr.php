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

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorPlanUr extends WebResponse
{
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

        //Instanciando la Ur
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->clasAdm_gpo);
        $this->ur->setUr_id($this->clasAdm_ur);
        $this->ur->select();

        // Si es un usuario de nivel UR, o tiene permisos de escritura, puede grabar cambios a ésta pantalla
        $this->escritura = (($this->nvj_id == 2) or (isset($_SESSION[_S]['prm']['A']['12']['1']['2'])));

        // SI EL USUARIO ES DE NIVEZL JERARQUICO >= 4 (ANALISTA) Y TIENE PERMISOS DE VALIDACIÓN (3) ENTONCES
        // PODRÁ AUTORIZAR LA PLANIFICACIÓN UR
        $this->autorizacion = (($this->nvj_id >= 3) && (isset($_SESSION[_S]['prm']['A']['12']['1']['3'])));

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
            case 'guardar':
                $this->guardar();
                break;
            case 'uploadFirma':
                $this->uploadFirma();
                break;
            case 'download':
                $this->download();
                break;
            case 'upload':
                $this->upload();
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
        if (isset($_SESSION[_S]['prm']['A']['12']['1']) && $_SESSION[_S]['prm']['A']['12']['1']) {
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
                                title="Guardar la Planeación Institucional de la Unidad Responsable">
                            <i class="fa fa-floppy-disk me-2"></i>Guardar
                        </button>
                        HTML;
                }
            }
        }

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



        // Ponemos el status actual de la Planeación UR
        $botones .= '<span type="text" id="status"
            style="background-color:#' . $colorStatus . '" class="btn me-1 ">' . $nombreStatus . '</span>';

        $this->agregaTools($botones);

        $tabs = <<<HTML
            <div id="page-tabs">
                <ul class="nav nav-tabs" role="tablist">

                    <li role="presentation" class="nav-item">
                        <button class="nav-link" href="mm">Mandato Misional</button>
                    </li>
                   
                </ul>
            </div>
            HTML;

        $this->mixHtml($tabs);
        $ur = $this->ur;
        $inicioPeriodicoOficial = new stdClass();
        $inicioPeriodicoOficial->id = 'inicioPeriodicoOficial';
        $inicioPeriodicoOficial->name = 'inicioPeriodicoOficial';
        $inicioPeriodicoOficial->label = 'Documento legal de creación';
        $inicioPeriodicoOficial->enDiv = true;
        $inicioPeriodicoOficial->maxlength = '20';
        $inicioPeriodicoOficial->value = $ur->getInicioPeriodicoOficial();

        $inicioFecha = new stdClass();
        $inicioFecha->id = 'inicioFecha';
        $inicioFecha->name = 'inicioFecha';
        $inicioFecha->label = 'Fecha de publicación';
        $inicioFecha->class = 'datepicker';
        $inicioFecha->enDiv = true;
        $inicioFecha->formato = 'fecha';
        $inicioFecha->value = $ur->getInicioFecha();

        $internoPeriodico = new stdClass();
        $internoPeriodico->id = 'internoPeriodico';
        $internoPeriodico->name = 'internoPeriodico';
        $internoPeriodico->label = 'Periódico oficial';
        $internoPeriodico->labelClass = '_32 txtL';
        $internoPeriodico->enDiv = true;
        $internoPeriodico->maxlength = '20';
        $internoPeriodico->value = $ur->getInternoPeriodico();

        $modificacionPeriodico = new stdClass();
        $modificacionPeriodico->id = 'modificacionPeriodico';
        $modificacionPeriodico->name = 'modificacionPeriodico';
        $modificacionPeriodico->label = 'Periódico oficial de la última modificación o reforma';
        $modificacionPeriodico->labelClass = '_42 txtL';
        $modificacionPeriodico->enDiv = true;
        $modificacionPeriodico->maxlength = '30';
        $modificacionPeriodico->value = $ur->getModificacionPeriodico();

        $modificacionFecha = new stdClass();
        $modificacionFecha->id = 'modificacionFecha';
        $modificacionFecha->name = 'modificacionFecha';
        $modificacionFecha->label = 'Fecha de publicación de la última modificación o reforma';
        $modificacionFecha->labelClass = '_42 txtL';
        $modificacionFecha->enDiv = true;
        $modificacionFecha->formato = 'fecha';
        $modificacionFecha->value = $ur->getModificacionFecha();

        $btnDecreto = new stdClass();
        $btnDecreto->class = 'guardar';
        $btnDecreto->id = 'cargarDecreto';
        $btnDecreto->value = 'Cargar';
        $btnDecreto->title = 'Importar archivo';
        $btnDecreto->text = 'Cargar Decreto';

        $domicilioUR = new stdClass();
        $domicilioUR->id = 'domicilioUR';
        $domicilioUR->name = 'domicilioUR';
        $domicilioUR->label = 'Domicilio';
        $domicilioUR->class = 'input-xxlarge';
        $domicilioUR->enDiv = true;
        $domicilioUR->maxlength = '200';
        $domicilioUR->value = $ur->getDomicilio();

        $telefonoUR = new stdClass();
        $telefonoUR->id = 'telefonoUR';
        $telefonoUR->name = 'telefonoUR';
        $telefonoUR->label = 'Teléfono';
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
        $paginaWeb->label = 'Página Web';
        $paginaWeb->class = 'input-xxlarge';
        $paginaWeb->enDiv = true;
        $paginaWeb->maxlength = '200';
        $paginaWeb->value = $ur->getPaginaWeb();

        $correoElectronicoContacto = new stdClass();
        $correoElectronicoContacto->id = 'correoElectronicoContacto';
        $correoElectronicoContacto->name = 'correoElectronicoContacto';
        $correoElectronicoContacto->label = 'Correo Electrónico';
        $correoElectronicoContacto->class = 'input-xxlarge';
        $correoElectronicoContacto->enDiv = true;
        $correoElectronicoContacto->maxlength = '200';
        $correoElectronicoContacto->style = 'text-transform: none;';
        $correoElectronicoContacto->value = $ur->getCorreoElectronicoContacto();

        $mision = new stdClass();
        $mision->id = 'mision';
        $mision->name = 'mision';
        $mision->width = '400px';
        $mision->class = 'anulaenter input-block-level';
        $mision->rows = 5;
        $mision->height = '100px';
        $mision->maxlength = '6500';
        $mision->texto = $ur->getMision();

        $vision = new stdClass();
        $vision->id = 'vision';
        $vision->name = 'vision';
        $vision->width = '400px';
        $vision->rows = 5;
        $vision->class = 'anulaenter input-block-level';
        $vision->height = '100px';
        $vision->maxlength = '6500';
        $vision->texto = $ur->getVision();

        $objetivoGeneral = new stdClass();
        $objetivoGeneral->id = 'objetivoGeneral';
        $objetivoGeneral->name = 'objetivoGeneral';
        $objetivoGeneral->label = 'Objetivo general de la unidad responsable';
        $objetivoGeneral->class = 'anulaenter input-block-level';
        $objetivoGeneral->enDiv = true;
        $objetivoGeneral->width = '400px';
        $objetivoGeneral->height = '100px';
        $objetivoGeneral->maxlength = '6500';
        $objetivoGeneral->texto = $ur->getObjetivoGeneral();

        $objetivoEstrategico = new stdClass();
        $objetivoEstrategico->id = 'objetivoEstrategico';
        $objetivoEstrategico->name = 'objetivoEstrategico';
        $objetivoEstrategico->label = 'Objetivo estratégico';
        $objetivoEstrategico->class = 'anulaenter input-block-level';
        $objetivoEstrategico->enDiv = true;
        $objetivoEstrategico->width = '400px';
        $objetivoEstrategico->height = '100px';
        $objetivoEstrategico->maxlength = '6500';
        $objetivoEstrategico->texto = $ur->getObjetivoEstrategico();

        $productosInstitucionales = new stdClass();
        $productosInstitucionales->id = 'productoInstitucional';
        $productosInstitucionales->name = 'productoInstitucional';
        $productosInstitucionales->label = 'Producto(s) Institucional(es)';
        $productosInstitucionales->class = 'anulaenter input-block-level';
        $productosInstitucionales->enDiv = true;
        $productosInstitucionales->width = '400px';
        $productosInstitucionales->height = '100px';
        $productosInstitucionales->maxlength = '700';
        $productosInstitucionales->texto = $ur->getProductoInstitucional();

        // Si no es un usuario de nivel UR, o no tiene permisos de escritura, todos los campos estarán marcados como de sólo lectura
        if (! $this->escritura) {

            $inicioFecha->readonly = 'true';
            $modificacionFecha->readonly = 'true';

            $inicioPeriodicoOficial->readonly = 'true';
            $modificacionPeriodico->readonly = 'true';
            $btnDecreto->readonly = 'true';

            $domicilioUR->readonly = 'true';
            $telefonoUR->readonly = 'true';

            $paginaWeb->readonly = 'true';
            $correoElectronicoContacto->readonly = 'true';

            $mision->readonly = 'true';
            $vision->readonly = 'true';
            $objetivoGeneral->readonly = 'true';
            $objetivoEstrategico->readonly = 'true';
            $productosInstitucionales->readonly = 'true';
            $pdfDecreto = '
            <div class="control-group">
                <label class="control-label">Documento digital (formato PDF máximo de 6MB)</label>
                <div class="controls">
                    <a id="decretoArchivo" class="lnk_ext" tabindex=-1 lnk="decreto/' . $ur->getClave() . '-decreto.pdf?npi='
                . rand(1, 99999) . '" target="_blank">' . $ur->getDecretoArchivo() . '</a>
                </div>
            </div>
            ';
            $pdfOrganigrama = '
            <div class="control-group">
                <label class="control-label"> Documento digital (formato PDF máximo de 6MB)</label>
                <div class="controls">
                    <a id="organigramaArchivo" class="lnk_ext" tabindex=-1 lnk="organigrama/' . $ur->getClave()
                . '-organigrama.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getOrganigramaArchivo() . '</a>
                </div>
            </div>
            ';
            $pdfInterno = '
            <div class="control-group">
                <label class="control-label"> Documento digital (formato PDF máximo de 6MB)</label>
                <div class="controls">
                    <a id="internoArchivo" class="lnk_ext" tabindex=-1 lnk="interno/' . $ur->getClave() . '-interno.pdf?npi='
                . rand(1, 99999) . '" target="_blank">' . $ur->getInternoArchivo() . '</a>
                </div>
            </div>
            ';
            $pdfLegal = '
            <div class="control-group">
                <label class="control-label"> Documento digital  (formato PDF máximo de 6MB)</label>
                <div class="controls">

                    <a id="legalArchivo" class="lnk_ext" tabindex=-1 lnk="legal/' . $ur->getClave()
                . '-legal.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getInternoPeriodico() . '</a>
                </div>
            </div>
            ';
        } else {
            // SI ES UN USUARIO DE NIVEL UR, O TIENE PERMISOS DE ESCRITURA ...

            $inicioFecha->class = 'fecha';
            $modificacionFecha->class = 'fecha';

            $pdfDecreto = '
            <div class="control-group">
                <label class="control-label">Documento digital</label>
                <div class="controls">
                    <input type="file" name ="decretoFile" id="nombreArchDecreto"
                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"decretoFile"}\'
                    >
                    <a id="decretoArchivo" class="lnk_ext" tabindex=-1 lnk="decreto/' . $ur->getClave() . '-decreto.pdf?npi='
                . rand(1, 99999) . '" target="_blank">' . $ur->getDecretoArchivo() . '</a>
                </div>
            </div>
            <div id="progress" class="progress progress-striped">
                <div class="progress-bar" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">100% Complete</span>
                </div>
            </div>
            ';

            $pdfOrganigrama = '
            <div class="control-group">
                <label class="control-label"> Documento digital</label>
                <div class="controls">
                    <input type="file" name ="organigramaFile" id="nombreArchOrganigrama"
                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"organigramaFile"}\'
                    >
                    <a id="organigramaArchivo" class="lnk_ext" tabindex=-1 lnk="organigrama/' . $ur->getClave()
                . '-organigrama.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getOrganigramaArchivo() . '</a>
                </div>
            </div>
            <div id="progressOrg" class="progress progress-striped">
                <div class="progress-bar" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">100% Complete</span>
                </div>
            </div>';

            $pdfInterno = '
            <div class="control-group">
                <label class="control-label"> Documento digital (formato PDF máximo de 6MB)</label>
                <div class="controls">
                    <input type="file" name ="internoFile" id="nombreArchInterno"
                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"internoFile"}\'
                    >
                    <a id="internoArchivo" class="lnk_ext" tabindex=-1 lnk="interno/' . $ur->getClave() . '-interno.pdf?npi='
                . rand(1, 99999) . '" target="_blank">' . $ur->getInternoArchivo() . '</a>
                </div>
            </div>
            <div id="progressRI" class="progress progress-striped">
                <div class="progress-bar" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">100% Complete</span>
                </div>
            </div>';
            $pdfLegal = '
            <div class="control-group">
                <label class="control-label"> Documento digital</label>
                <div class="controls">
                    <input type="file" name ="legalFile" id="nombreArchLegal"
                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"upload","tipo":"legalFile"}\'
                    >
                    <a id="legalArchivo" class="lnk_ext" tabindex=-1 lnk="legal/' . $ur->getClave()
                . '-legal.pdf?npi=' . rand(1, 99999) . '" target="_blank">' . $ur->getInternoPeriodico() . '</a>
                </div>
            </div>
            <div id="progressLeg" class="progress progress-striped">
                <div class="progress-bar" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">100% Complete</span>
                </div>
            </div>';
        }

        $html = '<div id="modalComentario"></div><br />
            <form role ="form">
                <div id="seccion1" style="display: none;">
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

                    </div>

                    <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <legend> Organigrama </legend>
                            </div>
                            <fieldset class="col-md-4 col-xs-12 mb-4">
                            ' . $pdfOrganigrama . '
                            </fieldset>
                        </fieldset>
                    </div>
                </div>


                <div id="seccion2">
                    <div class="row">
                        <fieldset class="col-md-6 col-xs-12">
                            <legend>Misi&oacute;n</legend>
                            ' . $this->ponTextarea($mision) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            <legend>Visi&oacute;n</legend>
                            ' . $this->ponTextarea($vision) . '
                        </fieldset>
                    </div>

                    &nbsp;<br />
                    <fieldset >
                        <legend> Objetivos </legend>
                        <br />
                        ' . $this->ponTextarea($objetivoGeneral) . '
                        <br />
                        ' . $this->ponTextarea($objetivoEstrategico) . '
                        <br />
                        ' . $this->ponTextarea($productosInstitucionales) . '
                    </fieldset>

                </div>

                ' . $this->dameHtmlPersonasDocumentos($status) . '


                <div id="seccion7" style="display: none;">
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
        $script = '
            // Pagina arriba y abajo permitirán cambiarse entre los Tabuladores
            Mousetrap.bind("shift+pagedown", function(e) {
                e.preventDefault;
                var actual = $("#page-tabs .active a").attr("href");
                switch (actual) {
                    case "ii":
                        $("a[href*=\"mm\"]").click();
                        break;
                    case "mm":
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
                        $("a[href*=\"dt\"]").click();
                        break;
                    case "dt":
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
                        $("a[href*=\"dt\"]").click();
                        break;
                    case "mm":
                        $("a[href*=\"ii\"]").click();
                        break;
                    case "tu":
                        $("a[href*=\"mm\"]").click();
                        break;
                    case "ta":
                        $("a[href*=\"tu\"]").click();
                        break;
                    case "tp":
                        $("a[href*=\"ta\"]").click();
                        break;
                    case "dt":
                        $("a[href*=\"tp\"]").click();
                        break;
                }
                e.stopPropagation;
            });


            $("#regresarUR").click(function(){
                $("nav").menusinpres("clickMenu");
            }    );

            $("#nombreArchLegal").click(function(){
                $("#progressLeg .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });
            $("#nombreArchOrganigrama").click(function(){
                $("#progressOrg .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });
            $("#nombreArchDecreto").click(function(){
                $("#progress .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });

            $("#nombreArchLegal").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#legalArchivo").text(validacion.nombre);
                    notify(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressLeg .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });

            $("#nombreArchOrganigrama").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#organigramaArchivo").text(validacion.nombre);
                    notify(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressOrg .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });

            $("#nombreArchInterno").click(function(){
                $("#progressRI .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });
            $("#nombreArchInterno").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#internoArchivo").text(validacion.nombre);
                    notify(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressRI .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });


            $("#nombreArchDecreto").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#decretoArchivo").text(validacion.nombre);
                    notify(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progress .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
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
                $("#seccion7").hide();

                switch ( seleccionado.attr("href") ) {
                    case "ii":
                        $("#desvalida").hide();
                        $("#seccion1").show();
                        setTimeout( function() { $( "#inicioPeriodicoOficial" ).focus() }, 1     );
                        break;
                    case "mm":
                        $("#desvalida").show();
                        $("#seccion2").show();
                        setTimeout( function() { $( "#mision" ).focus() }, 1 );
                        break;
                    case "tu":
                        $("#desvalida").hide();
                        $("#seccion3").show();
                        setTimeout( function() { $( "#nombreTitular" ).focus() }, 1 );
                        break;
                    case "ta":
                        $("#desvalida").hide();
                        $("#seccion4").show();
                        setTimeout( function() { $( "#nombreAdministrativo" ).focus() }, 1 );
                        break;
                    case "tp":
                        $("#desvalida").hide();
                        $("#seccion5").show();
                        setTimeout( function() { $( "#nombrePlaneacion" ).focus() }, 1 );
                        break;
                    case "te":
                        $("#desvalida").hide();
                        $("#seccion6").show();
                        setTimeout( function() { $( "#nombreEvaluacion" ).focus() }, 1 );
                        break;
                    case "dt":
                        $("#desvalida").hide();
                        $("#seccion7").show();
                        setTimeout( function() { $( "#domicilioUR" ).focus() }, 1 );
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
                        $(this).focus();
                    }
                }
            });
            //Validamos el telefono Evaluacion
            $("#telefonoEvaluacion").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if ( validatePhone( valor ) ) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                        notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                        $(this).focus();
                    }
                }
            });
            //Validamos el telefono Planeacion
            $("#telefonoPlaneacion").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if ( validatePhone( valor ) ) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                        notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                        $(this).focus();
                    }
                }
            });
            //Validamos el telefono Administrativo
            $("#telefonoAdministrativo").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if ( validatePhone( valor ) ) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                        notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                        $(this).focus();
                    }
                }
            });
            //Validamos el telefono Titular
            $("#telefonoTitular").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if ( validatePhone( valor ) ) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                        notify("El número telefónico no es válido, debe ser de 10 dígitos", "error");
                        $(this).focus();
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
                            $(this).focus();
                        }
                    }
                });
            // Validamos el email Evaluacion
                $("#emailEvaluacion").blur(function(){
                    valor = $(this).val().trim();
                    if ( valor != "" ) {
                        if ( validateEmail( valor ) ) {
                            $(this).css("color", "green");
                        } else {
                            $(this).css("color", "red");
                            notify("El correo electrónico no es válido", "error");
                            $(this).focus();
                        }
                    }
                });
            // Validamos el email Planeacion
                $("#emailPlaneacion").blur(function(){
                    valor = $(this).val().trim();
                    if ( valor != "" ) {
                        if ( validateEmail( valor ) ) {
                            $(this).css("color", "green");
                        } else {
                            $(this).css("color", "red");
                            notify("El correo electrónico no es válido", "error");
                            $(this).focus();
                        }
                    }
                });
            // Validamos el email Administrativo
                $("#emailAdministrativo").blur(function(){
                    valor = $(this).val().trim();
                    if ( valor != "" ) {
                        if ( validateEmail( valor ) ) {
                            $(this).css("color", "green");
                        } else {
                            $(this).css("color", "red");
                            notify("El correo electrónico no es válido", "error");
                            $(this).focus();
                        }
                    }
                });
            // Validamos el email Titular
                $("#emailTitular").blur(function(){
                    valor = $(this).val().trim();
                    if ( valor != "" ) {
                        if ( validateEmail( valor ) ) {
                            $(this).css("color", "green");
                        } else {
                            $(this).css("color", "red");
                            notify("El correo electrónico no es válido", "error");
                            $(this).focus();
                        }
                    }
                });


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
                                inicioPeriodicoOficial:$("#inicioPeriodicoOficial").val(),
                                inicioFecha:$("#inicioFecha").val(),
                                modificacionPeriodico:$("#modificacionPeriodico").val(),
                                modificacionFecha:$("#modificacionFecha").val(),
                                mision:$("#mision").val().toUpperCase().trim(),
                                vision:$("#vision").val().toUpperCase().trim(),
                                objetivoGeneral:$("#objetivoGeneral").val().toUpperCase().trim(),
                                objetivoEstrategico:$("#objetivoEstrategico").val().toUpperCase().trim(),
                                productoInstitucional:$("#productoInstitucional").val().toUpperCase().trim(),
                            ' : '') . '
                            nombreTitular:$("#nombreTitular").val().toUpperCase().trim(),
                            cargoTitular:$("#cargoTitular").val().toUpperCase().trim(),
                            correoElectronicoTitular:$("#emailTitular").val().trim(),
                            telefonoTitular:$("#telefonoTitular").val().trim(),
                            nombreAdministrativo:$("#nombreAdministrativo").val().toUpperCase().trim(),
                            cargoAdministrativo:$("#cargoAdministrativo").val().toUpperCase().trim(),
                            correoElectronicoAdministrativo:$("#emailAdministrativo").val().trim(),
                            telefonoAdministrativo:$("#telefonoAdministrativo").val().trim(),
                            nombrePlaneacion:$("#nombrePlaneacion").val().toUpperCase().trim(),
                            cargoPlaneacion:$("#cargoPlaneacion").val().toUpperCase().trim(),
                            correoElectronicoPlaneacion:$("#emailPlaneacion").val().trim(),
                            telefonoPlaneacion:$("#telefonoPlaneacion").val().trim(),

                            nombreEvaluacion:$("#nombreEvaluacion").val().toUpperCase().trim(),
                            cargoEvaluacion:$("#cargoEvaluacion").val().toUpperCase().trim(),
                            correoElectronicoEvaluacion:$("#emailEvaluacion").val().trim(),
                            telefonoEvaluacion:$("#telefonoEvaluacion").val().trim(),

                            domicilio:$("#domicilioUR").val().toUpperCase().trim(),
                            telefono:$("#telefonoUR").val().toUpperCase().trim(),
                            paginaWeb:$("#paginaWeb").val().toUpperCase().trim(),
                            correoElectronicoContacto:$("#correoElectronicoContacto").val().trim()
                        }
                    );}
                e.stopPropagation();
            });

            $("#verHistorial").click(function(){
                comunica( "' . $this->pantalla . '", { metodo:"verHistorial" } );
            });

        ';

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

            $this->autorizacion = (($this->nvj_id >= 3));
            $this->prm->abc = 'c';

            $this->prm->gpo_id = $this->clasAdm_gpo;
            $this->prm->ur_id = $this->clasAdm_ur;
            $this->prm->bit_observacion = 'EL USUARIO GUARDO LA PLANEACION UR';

            UnidadResponsable::abc($this->prm);

            $ur = new UnidadResponsable();
            $ur->setGpo_id($this->clasAdm_gpo);
            $ur->setUr_id($this->clasAdm_ur);
            $ur->select();

            $this->conectar();

            $this->mixScript('
                notify("Planeación actualizada exitosamente");
                comunica( "' . $this->pantalla . '" );');
        }
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
        $up = new FicheroSefip();
        $up->setTipos(['pdf']);
        $up->setModulo('poa');
        $up->setfile($ur_clave . '-' . $folder . '.pdf');
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
                    case 'organigrama':
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

        if (isset($_FILES[$name]) && $_FILES[$name]['error'] == '0') {
            $up = new FicheroSefip();
            $up->setTipos(['pdf']);
            $up->setFichero($_FILES[$name]);
            $up->setModulo('poa');
            $up->setfile($ur_clave . '-' . $this->prm->firma . '.pdf');
            $up->setUrFichero($ur_clave);
            $up->setAnioFichero(APP_EJERCICIO);
            $up->setRuta('firmas');

            $nomDir = $up->getRutaMod();

            if ($nomDir === false && $up->getError() === true) {
                $this->setStatus('error');
                $respuesta->mensaje = $up->getMsg();
            } else {
                $nomFile = $ur_clave . '-' . $this->prm->firma;
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
        } else {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrio un error al cargar el archivo';
        }
        $this->setValidacion($respuesta);
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
        $documentos = [
            'nombramiento',
            'identificacion',
            'firma',
            'nombramientoDelegatorio',
            'identificacionDelegatorio',
            'firmaDelegatorio',
            'acuerdoDelegatorio',
        ];
        $labelDocumentos = [
            'Nombramiento o documento que lo acredite',
            'Identificación oficial',
            'Formato de legalización de firmas',
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
            switch ($p) {
                case 0:
                    // TITULAR
                    $nombre = $this->ur->getNombreTitular();
                    $cargo = $this->ur->getCargoTitular();
                    $correoElectronico = $this->ur->getCorreoElectronicoTitular();
                    $telefono = $this->ur->getTelefonoTitular();
                    break;
                case 1:
                    // ADMINISTRATIVO
                    $nombre = $this->ur->getNombreAdministrativo();
                    $cargo = $this->ur->getCargoAdministrativo();
                    $correoElectronico = $this->ur->getCorreoElectronicoAdministrativo();
                    $telefono = $this->ur->getTelefonoAdministrativo();
                    break;
                case 2:
                    // PLANEACIÓN
                    $nombre = $this->ur->getNombrePlaneacion();
                    $cargo = $this->ur->getCargoPlaneacion();
                    $correoElectronico = $this->ur->getCorreoElectronicoPlaneacion();
                    $telefono = $this->ur->getTelefonoPlaneacion();
                    break;
                case 3:
                    // EVLUACION
                    $nombre = $this->ur->getNombreEvaluacion();
                    $cargo = $this->ur->getCargoEvaluacion();
                    $correoElectronico = $this->ur->getCorreoElectronicoEvaluacion();
                    $telefono = $this->ur->getTelefonoEvaluacion();
                    break;
            }

            $strHtml .= '
                <div id="' . $secciones[$p] . '" style="display: none;">
                    <fieldset class="row m-b-10" >
                        <div class="col-md-12 col-xs-12">
                            <h4>' . $personas[$p] . '</h4>
                        </div>
                        <fieldset class="col-md-6 col-xs-12 mb-4">
                            <label class="control-label" for="nombre' . $personas[$p] . '">Nombre</label>
                            <input class="form-control input-xxlarge" id="nombre' . $personas[$p] . '" name="nombre' . $personas[$p] . '" maxlength="200" value="' . $nombre . '" ' . $strReadOnly . ' type="text" size="200">
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-12 mb-4">
                            <label class="control-label" for="cargo' . $personas[$p] . '">Cargo</label>
                            <input class="form-control input-xxlarge" id="cargo' . $personas[$p] . '" name="cargo' . $personas[$p] . '" maxlength="200" value="' . $cargo . '" ' . $strReadOnly . ' type="text" size="200">
                        </fieldset>
                    </fieldset>

                    <fieldset class="row m-b-10">
                        <fieldset class="col-md-6 col-xs-12 mb-4">
                            <label class="control-label" for="email' . $personas[$p] . '">Correo electrónico</label>
                            <input class="form-control input-xxlarge" id="email' . $personas[$p] . '" name="email' . $personas[$p] . '" maxlength="50" value="' . $correoElectronico . '" ' . $strReadOnly . ' type="text" size="200" style="text-transform: none;">
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-12 mb-4">
                            <label class="control-label" for="telefono' . $personas[$p] . '">Teléfono</label>
                            <input class="form-control input-xxlarge" id="telefono' . $personas[$p] . '" name="telefono' . $personas[$p] . 'itular" maxlength="50" value="' . $telefono . '" ' . $strReadOnly . ' type="text" size="200">
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

                    urf_nombramientoDelegatorioTitular,
                    urf_identificacionDelegatorioTitular,
                    urf_firmaDelegatorioTitular,
                    urf_acuerdoDelegatorioTitular,

                    urf_nombramientoAdministrativo,
                    urf_identificacionAdministrativo,
                    urf_firmaAdministrativo,

                    urf_nombramientoDelegatorioAdministrativo,
                    urf_identificacionDelegatorioAdministrativo,
                    urf_firmaDelegatorioAdministrativo,
                    urf_acuerdoDelegatorioAdministrativo,

                    urf_nombramientoPlaneacion,
                    urf_identificacionPlaneacion,
                    urf_firmaPlaneacion,

                    urf_nombramientoEvaluacion,
                    urf_identificacionEvaluacion,
                    urf_firmaEvaluacion

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
                if ($d == 3) {
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

                if ($d == 6) {
                    $strHtml .= '</fieldset><fieldset class="row mb-4">';
                }

                $id = $documentos[$d] . $personas[$p];

                $link = '<a style="color:blue;" class="lnk_ext m-l-5" id="nameFilename' . $id . '" tabindex=-1 lnk="firmas/' . $this->clasAdm_gpo . $this->clasAdm_ur . '-' . $id . '.pdf?npi=' . rand(1, 99999) . '" target="_blank" class="left">';
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
                        <input type="file" name ="archivoFirma" id="' . $id . '" data-url="' . $this->pantalla . '"
                            data-form-data=\'{"metodo":"uploadFirma", "firma":"' . $id . '"}\'/>
                        ' . $link . '<br/><br/>
                        <div id="progress' . $id . '" class="progress progress-striped">
                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                <span class="sr-only">100% Complete</span>
                            </div>
                        </div>
                    ';

                    $this->mixScript('
                        $("#' . $id . '").click(function(){ $("#progress' . $id . ' .progress-bar").css("width","0%" ).attr("aria-valuenow",0); });
                        $("#' . $id . '").fileupload({
                            dataType:"json",
                            done: function(e, data) {
                                var validacion = data.result.validacion;
                                $("#nameFilename' . $id . '").text(validacion.nombre);
                                notify(validacion.mensaje);
                            },
                            progress: function(e, data){
                                var progress = parseInt( data.loaded / data.total * 100,10);
                                $("#progress' . $id . ' .progress-bar").css("width", progress + "%" )
                                .attr("aria-valuenow",progress);
                            },
                            fail: function(e, data) {
                                notify( data.errorThrown );
                            }
                        });
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
        $dest = __DIR__ . '/../../tmps/' . $fichero;
        copy($ruta, $dest);
        $this->mixScript(' $("#det_fsvis1").attr("src","lib/file_download.php?r=' . $fichero . '");');
    }
}
if (isset($_SESSION[_S]['prm']['A']['12']['1'])) {
    new ControladorPlanUr();
} else {
    WebResponse::sinPermisos();
}
