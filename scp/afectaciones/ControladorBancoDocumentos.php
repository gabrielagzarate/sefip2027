<?php

declare(strict_types=1);
$pathInPieces = explode(DIRECTORY_SEPARATOR, __FILE__);
require_once dirname(__FILE__) . '/../../lib/constantes.php';

/*--------------------------------------------------------------------------------------------------------
* ControladorBancoDocumentos
*
* Elaboró: Marcos
*
* Mostrar una relación de documentos existentes en la cat_BancoDocumentos, y permitir filtrarlos
* y adicionar nuevos.   En algunos casos se permitirá subir documentos.   Y opcionalmente -si lo
* quiere la UR- permitir cargar el XML de un CFDI para tomar datos de ahí y validarlo contra el SAT.
--------------------------------------------------------------------------------------------------------*/
/**
Fecha de última actualización .- 08-2024
Autor .- Jordy Jesus Castro Avendaño, ABN
Descripcion .- Se adecuaron las consultas para el modo estricto, cambio de varibales de sesion para 2019, identacion de codigo.
 */
require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/FicheroSefip.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';

class ControladorBancoDocumentos extends WebResponse
{
    public $carpetaSat;
    public $usarioId;
    public $clasAdmGpo;
    public $clasAdmUr;
    public $usarioNvj;
    public $usarioIp;
    public $sEjercicio;
    public $sEjercicioA;
    public $snur;
    public $lectura;
    public $escritura;

    public function __construct()
    {
        parent::__construct();
        $this->carpetaSat = 'lib/SAT';
        $this->pantalla = 'scp/afectaciones/ControladorBancoDocumentos.php';
        $this->usarioId = $_SESSION[_S]['usr_id'];
        $this->clasAdmGpo = $_SESSION[_S]['clasAdm-gpo'];
        $this->clasAdmUr = $_SESSION[_S]['clasAdm-ur'];
        $this->usarioNvj = $_SESSION[_S]['nvj_id'];
        $this->usarioIp = $_SESSION[_S]['ses_ip'];
        $this->sEjercicio = $_SESSION[_S]['ejercicio'];
        $this->sEjercicioA = $_SESSION[_S]['ejercicioActual']; #APP_EJERCICIO
        $this->snur = $_SESSION[_S]['ur_nombre'];
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, 'S_2_17');
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, 'S_2_17');
        switch ($this->metodo) {
            case 'cargarXmlCFDI':
                $this->cargarXmlCFDI();
                break;
            case 'validarXmlCFDI':
                $this->validarXmlCFDI();
                break;
            case 'grabarDocumento':
                $this->grabarDocumento();
                break;
            case 'dameNombreProveedor':
                $this->DameNombreProveedor();
                break;
            case 'modificar':
                $this->modificarDocumento();
                break;
            case 'habDeshab':
                $this->habDeshabDocumento();
                break;
            case 'eliminar':
                $this->eliminarDocumento();
                break;
            case 'cerrarDocumento':
                $this->cerrarDocumento();
                break;
            case 'llenaTablaDocumentos':
                $this->llenaTablaDocumentos();
                break;
            default:
                $this->PonBarraBotones();
                break;
        }
        $this->returnJson();
    }

    private function PonBarraBotones()
    {
        // Si llaman a la pantalla desde una opción del sinpres y pasan el RFC, que muestre esos valores por default
        $rfc = ((isset($this->prm->rfc) && $this->prm->rfc != '') ? $this->prm->rfc : null);
        $fr = ((isset($this->prm->fr) && $this->prm->fr != '') ? $this->prm->fr : null);
        /**
         * Eduardo Venegas
         * Fecha 18/12/2018
         * Comentario:  Añadi la variable tempRfc para obtener el valor del rfc que se manda cuando se llama
         *  este controlador "BancoDocumentos", desde ControladorAfectacion, para que si se quiere agregar
         *  un documento nuevo de manera automatica se establezca este mismo rfc, del cual se esta solicitando.
         */
        $esSeleccionaDocumento = $rfc !== null || $fr !== null;

        $tempRfc = '';
        if ($rfc != '') {
            $tempRfc = $rfc;
        }

        // Si llaman a la pantalla desde la vinculación por CLC, entonces cargaremos una lista de documentos
        // Le antepondremos * a lo que manden para que la busqueda lo identifique y arme el where correctamente
        $bdo = ((isset($this->prm->bdo) && $this->prm->bdo != '') ? '*' . $this->prm->bdo : '');
        $regresarAPrincipal = $rfc == '' && $fr == '' && $bdo == '';

        // Tipos de documento fuente
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            SELECT tdf_id, tdf_nombre AS nombre
            FROM cat_TipoDocumentoFuente
            ORDER BY tdf_id;
        ');
        $conexion->consultar();
        $conexion->desconectar();

        // Botones
        $tdfOptions = '';
        $strCombo = '<select name="cmbTipoDocto" id="cmbTipoDocto" class="form-select" tabindex="10">';
        while ($row = $conexion->extraer()) {
            // Marcos: 13/sep/2016
            // NO permitiremos adicionar el tipo de documento SDI (servicio de inspección 2.5)
            // ya que éste documento se manejará automáticamente
            $tdfOptions .= '
                <li class="px-2 py-1">
                    <a href=' . $row['tdf_id'] . ' class="tipoDocumento">' . strtoupper(ucfirst(strtolower($row['nombre']))) . '</a>
                </li>';
            $bolSoloUnaVez = ((trim($row['tdf_id']) == 'GRAL') ? true : false);
            if (trim($row['tdf_id']) <> 'SDI') {
                $strCombo .= '<option ' . (($bolSoloUnaVez) ? 'selected' : '') . ' value="' . $row['tdf_id'] . '">' . $row['nombre'] . '</option>';
            }
        }
        $strCombo .= '</select>';
        $btnregresar = $esSeleccionaDocumento ?
            '<button id="regresarBD" data-placement="bottom" title="Terminar y regresar a la pantalla anterior" class="btn btn-outline-secondary me-1">
                <i class="fa-solid fa-circle-left me-2"></i>REGRESAR
            </button>' : '';

        $tools = '
            <div class="row">
                <div class="col-md-12 col-xs-12 mb-2">
                    ' . $btnregresar . '
                    <div id="botonDocumentos" class="btn-group">
                        <button id="tdf" ta="" class="btn btn-default btn-test dropdown-toggle" data-bs-toggle="dropdown">
                            TODOS LOS DOCUMENTOS
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <button href="" class="btn btn-outline-secondary tipoDocumento me-1">
                                    TODOS LOS DOCUMENTOS
                                </button>
                            </li>
                            ' . $tdfOptions . '
                        </ul>
                    </div>

                    <div id="botonFiltro" class="btn-group">
                        <button type="button" class="btn btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#myModalFiltros" cat="">
                            <i class="fa-solid fa-filter me-2"></i>
                            FILTROS
                        </button>
                    </div>
                    <div id="myModalFiltros" class="modal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fa-solid fa-filter me-2"></i>Filtros de búsqueda</h5>
                                    <button type="button" class="cerrar close btn btn-xs btn-outline-danger" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                </div>
                                <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="control-label">RFC del emisor</label>
                                </div>
                                <div class="col-md-8">
                                    <label class="control-label">Nombre del emisor del documento</label>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4 mb-2">
                                    <fieldset >
                                        <input type="text" name="rfcFiltro"  id="rfcFiltro" maxlength="20" value="' . $rfc . '" class="form-control F1Catalogo limpiar" placeholder="F1: Catalogo" />
                                    </fieldset>
                                    
                                </div>
                                <div class="col-md-8 mb-2">
                                    <fieldset >
                                        <input id="nombreFiltro" type="text" class="form-control" disabled>
                                    </fieldset>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <fieldset >
                                        <input type="text" name="referenciaFiltro"  id="referenciaFiltro" maxlength="50" value="' . $bdo . '" class="form-control " placeholder="REFERENCIA" />
                                    </fieldset>
                                </div>
                                <div class="col-md-1">
                                    <fieldset >Desde</fieldset>
                                </div>
                                <div class="col-md-2">
                                    <fieldset >
                                        <input name="desdeFiltro" id="desdeFiltro" type="text" required class="form-control">
                                    </fieldset>
                                </div>
                                <div class="col-md-1">
                                    <fieldset >Hasta</fieldset>
                                </div>
                                <div class="col-md-2">
                                    <fieldset >
                                        <input name="hastaFiltro" id="hastaFiltro" type="text" required class="form-control">
                                    </fieldset>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <fieldset  style="padding-right: 1px !important;">
                                    <label class="control-label">Estado del Documento</label>
                                        <select name="estadoDocumento" id="estadoDocumento" class="form-control">
                                            <option selected value="1">Sin devengar o parcialmente devengados</option>
                                            <option value="2">Totalmente devengados</option>
                                            <option value="3">Todos SIN cancelados</option>
                                            <option value="4">Todos INCLUYENDO cancelados</option>
                                        </select>
                                    </fieldset>
                                </div>
                                <div class="col-md-3">
                                    <fieldset >
                                        <label class="control-label">$ Mayor o Igual a</label>
                                        <input name="impteDesdeFiltro" id="impteDesdeFiltro" type="text" required class="form-control" style="text-align:right">
                                    </fieldset>
                                </div>
                                <div class="col-md-3">
                                    <fieldset >
                                        <label class="control-label">$ Menor o Igual a</label>
                                        <input name="impteHastaFiltro" id="impteHastaFiltro" type="text" required class="form-control" style="text-align:right">
                                    </fieldset>
                                </div>
                            </div>

                            <div class="row">
                                <fieldset class="col-xs-12">
                                    <fieldset class="col-xs-12">
                                        <label>
                                            <input id="chkFiltroFR" type="checkbox" ' . (($fr == 'S') ? 'checked' : '') . '>Sólo documentos para recuperación de Fondo Rotatorio
                                        </label>
                                    </fieldset>
                                </fieldset>
                            </div>

                                </div>
                                <div class="modal-footer">
                                    <button id="mostrar2" data-placement="bottom" title="Buscar y mostrar los documentos solicitados" class="btn btn-outline-primary me-1 muestraPlanCuentas ladda-button" data-style="expand-left" cat=""><i class="fa-solid fa-magnifying-glass me-2"></i>
                                        Mostrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button class="btn btn-outline-secondary me-1 dropdown-toggle" data-bs-toggle="dropdown" cat="">
                            <i class="fa-solid fa-file-pdf me-1"></i>
                            <i class="fa-solid fa-file-excel"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li >
                                <button id="pdf" class="btn btn-outline-secondary impDocto">
                                    Envía selección actual a PDF
                                </button>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <button id="xls" class="btn btn-outline-secondary impDocto">
                                    Envía selección actual a Excel
                                </button>
                            </li>
                        </ul>
                    </div>' .
            (($this->escritura) ? '
                    <button id="adicionar" type="button" data-placement="bottom" title="Capturar nuevo documento" class="btn btn-outline-primary me-1" data-toggle="modal">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                
                    <button id="modificar"  data-placement="bottom" title="Modificar documento seleccionado" role="button" class="btn btn-outline-secondary me-1" data-toggle="modal">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    ' : '') . '

                    <button id="mostrar" data-placement="bottom"  role="button" name="mostrar" type="button" title="Mostrar dónde se ha usado el documento seleccionado" class="btn btn-outline-secondary me-1" data-toggle="modal">
                        <i class="fa-solid fa-eye"></i>
                    </button>

                    <button id="cerrarDocto"  data-placement="bottom"  title="Poner a status DEVENGADO aun cuando quede saldo pendiente por devengar" role="button" class="btn btn-outline-primary me-1" data-toggle="modal">
                        <i class="fa-solid fa-lock"></i>
                    </button>

                    ' . (($this->escritura) ? '
                    <button id="eliminar"  data-placement="bottom" title="Eliminar DEFINITIVAMENTE el documento actual" class="btn btn-outline-danger me-1">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>

                    <button id="habDeshab"  data-placement="bottom" title="deshabilitar/habilitar el documento actual" class="btn btn-outline-danger me-1">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>

                    <button id="xmlCFDI" type="button" data-placement="bottom" title="Cargar XML de CFDI" class="btn btn-outline-primary me-1" data-toggle="modal">
                        <i class="fa-solid fa-upload me-2"></i>
                        CFDI
                    </button>
                    ' : '') . '

                    <button id="mostrar1" data-placement="bottom" title="Buscar y mostrar los documentos solicitados" class="btn btn-outline-secondary me-1 btn-test muestraPlanCuentas ladda-button" data-style="expand-left" cat="">
                        <i class="fa fa-search me-2"></i>
                        MOSTRAR
                    </button>

                    <!-- <input id="chkImpte" name="chkImpte" type="checkbox" title="Mostrar / Ocultar importes de Afectaciones Presupuestales" data-placement="bottom" value=0></input> -->
                </div>
            </div>
        ';

        $filtroTabla =
            '   <div id="filtrarEnPantalla" class="input-group mb-2" style="width:250px">
                <input id="buscar" type="text" class="form-control" placeholder="Texto o importe a buscar ...">
            </div>';
        if (!$esSeleccionaDocumento) {
            $this->agregaTools($tools);
        }
        $tabla = $this->getTabla($esSeleccionaDocumento);
        $tabla->inputFiltro = 'buscar';
        $tabla->tools = $filtroTabla;
        $tabla->comunica->tdf = '{$("#tdf").attr("ta")}';
        $tabla->comunica->rfc = '{$("#rfcFiltro").val()}';
        $tabla->comunica->ref = '{$("#referenciaFiltro").val()}';
        $tabla->comunica->del = '{$("#desdeFiltro").val()}';
        $tabla->comunica->al = '{$("#hastaFiltro").val()}';
        $tabla->comunica->ed = '{$("#estadoDocumento").val()}';
        $tabla->comunica->id = '{$("#impteDesdeFiltro").val()}';
        $tabla->comunica->ih = '{$("#impteHastaFiltro").val()}';
        $tabla->comunica->fr = '{$("#chkFiltroFR").prop("checked")}';
        $tabla->comunica->p = '{$("#buscar").attr("ps")}';
        $tabla = $this->wrPonTabla($tabla);
        $html = '
            <div class="modal-content"><div class="modal-body">
                <div id="areaBotones">
                    ' . ($esSeleccionaDocumento ? $tools : '') . '
                    <div class="row">
                        <div class="col-md-6 col-xs-6">
                            
                        </div>
                        <div class="col-md-6 col-xs-6">
                            <div style="position:relative">
                                <div id="DocumentosPaginador"  style="float:right;position:absolute;right:0;top:0;z-index:100;margin:0">
                                </div>
                            </div>
                        </div>
                        <div>' . $tabla . '</div>
                    </div>
                </div>
                <span style="height:6px; display:block"></span>
                <div id="avisosLD"></div>
                <div id="gridDatos"></div>
                
                <div class="row">
                    <fieldset id="divContenedorModalDevengado" class="col-md-12 col-xs-12">
                    </fieldset>
                </div>
            </div>
            
        ';

        // El rfc del emisor lo separamos para que acepte el autocompletado
        $rfcEmisor = new stdClass();
        $rfcEmisor->id = 'rfcEmisor';
        $rfcEmisor->name = 'rfcEmisor';

        $rfcEmisor->autocomplete = true;
        $rfcEmisor->tabla = BD_SINPRES . '.cat_Proveedores';
        $rfcEmisor->camposText = 'rfc_nombre';
        $rfcEmisor->campoVal = 'rfc_id';

        $rfcEmisor->placeholder = 'F1: Catalogo';
        $rfcEmisor->class = 'F1Catalogo';
        $rfcEmisor->desdecaracter = 4;
        $rfcEmisor->maxlength = 13;
        $rfcEmisor->enDiv = true;
        $rfcEmisor->tabIndex = 40;

        $html .= '
            <div id="myModalDocto" class="modal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Registrar Documento</h5>
                            <button id="cerrarModalDocto" type="button" class="cerrar close btn btn-xs btn-outline-danger" data-bs-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div id="bodyModalDocto" class="modal-body">
                            <form id="AddDoc">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="control-label">Tipo de Documento</label>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="control-label">No. de Referencia</label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="control-label">Fecha del documento</label>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <fieldset >' . $strCombo . '</fieldset>
                                    </div>
                                    <div class="col-md-5">
                                        <fieldset >
                                            <input type="text" name="referencia" placeholder="No. Referencia" maxlength="50" value="" id="referencia" class="form-control" style="text-transform:none !important;" tabindex="20">
                                        </fieldset>
                                    </div>
                                    <div class="col-md-3">
                                        <fieldset >
                                            <input name="fecha" id="fecha" type="date" placeholder="ddmm" required class="form-control" tabindex="30">
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="control-label">RFC del emisor</label>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="control-label">Nombre del emisor del documento</label>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <fieldset >
                                            ' . $this->ponInput($rfcEmisor) . '
                                        </fieldset>
                                    </div>
                                    <div class="col-md-9">
                                        <fieldset >
                                            <input id="nombreEmisor" type="text" class="form-control" disabled>
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="control-label col-xs-12">Concepto del documento</label>
                                </div>

                                <div class="row mb-4">
                                    <fieldset class="col-xs-12">
                                        <input type="text" name="concepto" id="concepto" maxlength="100" value="" tabindex="50" class="form-control" placeholder="100 caracteres máximo"  style="text-transform:uppercase"/>
                                    </fieldset>
                                </div>

                                <!-- Reunión Egresos-Contabilidad-Informática 19/05/2015
                                Se toma el acuerdo de que para todos los documentos SE DESGLOSARA en la forma en que viene en la CLC -->

                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="control-label desgloceImporte">Percepciones Presupuestales</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label desgloceImporte">Percepciones Contables</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label desgloceImporte">Retenciones Contables</label>
                                    </div>
                                    <div class="col-md-4">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="control-label">Total</label>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-2">
                                        <fieldset >
                                            <input class="form-control desgloceImporte" type="text" name="percepPresup" placeholder="" maxlength="20" value="0" id="percepPresup" tabindex="52">
                                        </fieldset>
                                    </div>
                                    <div class="col-md-2">
                                        <fieldset >
                                            <input class="form-control desgloceImporte" type="text" name="percepContab" placeholder="" maxlength="20" value="0" id="percepContab" tabindex="53">
                                        </fieldset>
                                    </div>
                                    <div class="col-md-2">
                                        <fieldset >
                                            <input class="form-control desgloceImporte" type="text" name="retenContab" placeholder="" maxlength="20" value="0" id="retenContab" tabindex="54">
                                        </fieldset>
                                    </div>
                                    <div class="col-md-4">
                                        <fieldset >

                                        </fieldset>
                                    </div>
                                    <div class="col-md-2">
                                        <fieldset >
                                            <input type="text" name="total" placeholder="Total" maxlength="20" value="0" id="total" class="form-control noeditable" tabindex="100" disabled>
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <fieldset class="col-xs-12">
                                        <p style="color:green;">El importe que tomará el presupuesto es PERCEPCIONES PRESUPUESTALES.   Los demás campos están para desglosar el documento y facilitar el registro contable. </p>
                                    </fieldset>
                                </div>

                                <div class="row">
                                    <label class="control-label entradaOpcional col-xs-4">
                                        <font color="green">Importe que toma el SINPRES</font>
                                    </label>
                                </div>

                                <div class="row mb-4">
                                    <fieldset class="col-xs-4">
                                        <input class="entradaOpcional" ori="" type="text" name="importe" placeholder="Importe" maxlength="20" value="" id="importe" class="form-control" tabindex="110" disabled>
                                    </fieldset>
                                    <fieldset class="col-xs-4">
                                    </fieldset>
                                </div>

                                <div class="row mt-2">
                                    <fieldset class="col-xs-12">
                                        <div id="paraFondoRotatorio" class="row">
                                            <fieldset class="col-xs-12">
                                                <label>
                                                    <input id="chkFondoRotatorio" type="checkbox" tabindex="130" class="me-2">Se usará en una CLC para recuperación del Fondo Rotatorio
                                                </label>
                                            </fieldset>
                                        </div>
                                    </fieldset>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="grabarDocumento" class="btn btn-outline-success" tabindex="120">
                                <i class="fa-solid fa-check me-2"></i>
                                Grabar Documento
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        
            <div id="myModalInfo" class="modal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title ">Cargar XML de un CFDI</h5>
                            <button type="button" class="cerrar close btn btn-xs btn-outline-danger" data-bs-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div id="bodyModalInfo" class="modal-body">
                        </div>
                    </div>
                </div>
            </div>
        ';
        if ($esSeleccionaDocumento) {
            $this->setContenedor('bdPantallaCompleta');
        }

        $this->mixHtml($html);

        // Dibujamos la pantalla que permite buscar un proveedor

        $this->mixScript($this->ponF1Proveedores('rfcEmisor', [], 'nombreEmisor') . $this->ponF1Proveedores('rfcFiltro', [], 'nombreFiltro') .
        $this->getJsDocumentos($regresarAPrincipal, $bdo, $tempRfc));
        $this->mixScript($this->funcionesJs($esSeleccionaDocumento));

        // Si $bdo no está vacío es que venimos de la pantalla de vinculación por CLC, y debemos ocultar
        // algunas opciones.
        if ($bdo != '') {
            $this->mixScript('
                $("#botonDocumentos").hide();
                $("#botonFiltro").hide();
                $("#mostrar1").hide();
            ');
        }
    }

    /*-------- Grabamos un nuevo documento en el banco de documentos ---------------*/
    private function grabarDocumento()
    {
        // Cargamos en variables los datos a grabar ...
        $tipo = $this->prm->t;   // a = alta   c = cambio
        $tdf = strtoupper($this->prm->tdf);
        $tdfOri = isset($this->prm->tdfOri) ? strtoupper($this->prm->tdfOri) : $this->prm->tdf;
        $referencia = trim(strtoupper($this->prm->ref));
        $refOriginal = strtoupper($this->prm->ori);
        $fecha = $this->prm->fecha;
        $rfcEmisor = strtoupper($this->prm->rfcE);
        $rfcEOriginal = strtoupper($this->prm->rfcE_ori);
        $concepto = strtoupper($this->prm->c);
        $importeNeto = $this->prm->neto;

        $total = ((isset($this->prm->to) && $this->prm->to != '') ? $this->prm->to : '0');
        $percepPresup = ((isset($this->prm->tpp) && $this->prm->tpp != '') ? $this->prm->tpp : '0');
        $percepContab = ((isset($this->prm->tpc) && $this->prm->tpc != '') ? $this->prm->tpc : '0');
        $retenContab = ((isset($this->prm->trc) && $this->prm->trc != '') ? $this->prm->trc : '0');
        $tipoClc = ((isset($this->prm->clc) && $this->prm->clc != '') ? $this->prm->clc : '0');
        $fondoRotatorio = ((isset($this->prm->frt) && $this->prm->frt != '') ? $this->prm->frt : 'N');
        $uuid = strtoupper($this->prm->uu);

        // Si es un cambio y el importe original es distinto del importe actual, entonces disparamos un
        // procedimiento almacenado para que revise el status (devengado parcialmente, totalmente devengado)
        // y lo actualice en la tabla

        // PENDIENTE: No debe ser el UUID, sino la ruta el diferenciador de si es de XML o captura manual
        // ya que si tenemos el archivo xml entonces asumimos que es carga desde XML
        // Si pasan el UUID, el origen del documento es 2 (cargado desde XML)
        // si viene vacio es 1 (captura manual)
        // Igual, si ya viene el uuid es que pasó la validación del SAT

        if (trim($uuid) == '') {
            $bdo_origen = 1;
            $bdo_estadoSat = 1; // pendiente de validar
        } else {
            $bdo_origen = 2;
            $bdo_estadoSat = 2; // validado ante el SAT
        }

        // Marcos, 16/junio/2015
        // Para que no nos metan gol ... el importe neto enviado debe ser igual a (percepPresup + percepContab) - retenContab

        $importeSegunCampos = (floatval(str_replace(',', '', $percepPresup)) + floatval(str_replace(',', '', $percepContab))) - floatval(str_replace(',', '', $retenContab));
        $importeSegunCampos = (float)number_format((float)str_replace(',', '', (string) $importeSegunCampos), 2, '.', ',');

        // Volvemos a validar los campos sensibles, para evitar goles si modifican localmente las validaciones javascript
        // La cuenta no puede quedar vacía ...

        $bolError = false;
        if (trim($tdf) == '') {
            $this->mixScript('
                notify("No se especifico el tipo de  Documento", "warning");
                setTimeout(function() { $("#referencia").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        // Marcos: 13/sep/2016
        // NO se permitirá adicionar manualmente el tipo de documento SDI (servicio de inspección 2.5)
        // ya que éste se manejará automaticamente
        // Ya lo filtramos para que el combo de tipos no lo muestre... ponemos ésta validación
        // sólo por si nos quieren meter GOL modificando el DOM o haciendo una petición POST directamente

        if (trim($tdf) == 'SDI') {
            $this->mixScript('
                notify("Los documentos por SERVICIO DE INSPECCIÓN 2.5 no se dan de alta manualmente", "warning");
                setTimeout(function() { $("#referencia").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        if (trim($referencia) == '') {
            $this->mixScript('
                notify("No se especifico la referencia", "warning");
                setTimeout(function() { $("#referencia").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        // La referencia no debe contener caracteres de control (0x00-0x1F salvo tab/LF/CR).
        // Son inválidos en XML y corrompen el .xlsx al exportar (Excel lo reporta como "dañado").
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $referencia)) {
            $this->mixScript('
                notify("La referencia contiene caracteres no válidos (caracteres de control invisibles). Bórrela y captúrela de manera manual.", "warning");
                setTimeout(function() { $("#referencia").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        // El concepto tampoco debe contener caracteres de control (mismo motivo que la referencia).
        // Nota: las letras acentuadas (Á, Ó, Ñ, incluso Ò) son válidas y NO se bloquean aquí.
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $concepto)) {
            $this->mixScript('
                notify("El concepto contiene caracteres no válidos (caracteres de control invisibles). Bórrelo y captúrelo de manera manual.", "warning");
                setTimeout(function() { $("#concepto").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        // Validación de Fechas
        $date = str_replace('/', '-', $fecha);
        $d = explode('-', $date);
        $bolErrorFecha = false;

        // Checamos si viene en formato aaaa-mm-dd
        if (strlen($d[0]) == 4) {
            // esta funcion la valida en fomrato mes, dia, año
            $d[2] = substr($d[2], 0, 2);
            $bolErrorFecha = checkdate((int)$d[1], (int)$d[2], (int)$d[0]);
            // Checamos si viene en formato dd-mm-aaaa
        } else {
            $d[0] = substr($d[0], 0, 2);
            $bolErrorFecha = checkdate((int)$d[1], (int)$d[0], (int)$d[2]);
        }

        // Checamos otra vez la fecha del documento
        if (!$bolErrorFecha) {
            $this->mixScript('
                notify("La fecha del documento no es válida", "warning");
                setTimeout(function() { $("#fecha").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        if (trim($fecha) == '') {
            $this->mixScript('
                notify("No se especifico la fecha del documento", "warning");
                setTimeout(function() { $("#fecha").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        if (trim($rfcEmisor) == '') {
            $this->mixScript('
                notify("No se especifico el RFC del proveedor", "warning");
                setTimeout(function() { $("#rfcEmisor").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        if (trim($importeNeto) == '') {
            $this->mixScript('
                notify("No se especifico el importe del documento", "warning");
                setTimeout(function() { $("#percepPresup").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        if (floatval($importeNeto) < 0) {
            $this->mixScript('
                notify("El importe no puede ser menor a 0", "warning");
                setTimeout(function() { $("#percepPresup").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        if (floatval($importeNeto) == 0 && strpos('PGT.LC', $tdf) === false) {
            if ((floatval($percepPresup) + floatval($percepContab)) <= 0) {
                $this->mixScript('
                    notify("El importe no puede ser igual a 0", "warning");
                    setTimeout(function() { $("#percepPresup").select().focus() }, 100);
                    //window.espera.stop();
                ');
                return false;
            }
        }

        if ($bolError) {
            $this->mixScript('
                notify("Algunos campos obligatorios están vacíos", "warning");
                setTimeout(function() { $("#referencia").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        // Para evitar un error de foreignkey y que se quede la pantalla congelada, volvemos a validar
        // que el proveedor exista
        $this->conectar();
        $this->setSql('
            SELECT
                COUNT(*) AS veces
            FROM cat_Proveedor
            WHERE rfc_id = ?;');
        $this->consultar([mysql_escape_mimic($rfcEmisor)]);

        $row = $this->extraer();
        $existeProveedor = ($row['veces'] > 0);

        if (!$existeProveedor) {
            $this->mixScript('
                notify("El rfc no existe en la base de datos de proveedores", "error");
                setTimeout(function() { $("#rfcEmisor").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        // Marcos 20-feb-2015
        // Existe la posibilidad de cambiar la fecha siempre y cuando no venga de un XML y tenga solamente
        // afectaciones canceladas.   Sin embargo como al dibujar la pantalla ésta validación sólo habilita
        // o deshabilita el campo, pueden modificar desde el explorador el DOM y modificarla.   Por lo tanto
        // validaremos aquí otra vez que se pueda modificar la fecha y si no, ni siquiera incluimos el campo
        // en el UPDATE
        // Marcos: 14/jun/2017
        // Cambiamos los valores de comparación del WHERE a los originales, para que no nos metan gol

        if ($tipo == 'c') {
            $this->conectar();
            $parametros = [$this->clasAdmGpo, $this->clasAdmUr, trim($tdfOri), mysql_escape_mimic($refOriginal), mysql_escape_mimic($rfcEOriginal)];
            $this->setSql("
                SELECT
                    SUM(CASE WHEN eaf_id != 'C' THEN 1 ELSE 0 END) AS otras
                FROM cat_BancoDocumentos d
                INNER JOIN scp_Presupuestal p USING(bdo_id)
                INNER JOIN scp_Afectacion a ON p.gpo_id = a.gpo_id
                    AND p.ur_id = a.ur_id AND p.afe_id = a.afe_id
                WHERE d.gpo_id = ?
                    AND d.ur_id = ?
                    AND TRIM(d.tdf_id) = ?
                    AND d.bdo_referencia = ?
                    AND d.rfc_id = ?
                    AND eaf_id != 'C' ;
            ");
            $this->consultar($parametros);
            $row = $this->extraer();
            $this->desconectar();

            $nOtras = $row['otras'];
            // Permitimos modificar la fecha si sólo tiene canceladas (y en ése caso "otras" DEBE valer 0)
            $bolPermitirModificarFecha = ($nOtras == 0);

            $updateDeLaFecha = '';
            if ($bolPermitirModificarFecha) {
                $paramDeLaFecha = date_to_mysqldate($fecha);
                $updateDeLaFecha = 'bdo_fechaDocumento = ?, ';
            }

            // Si es un cambio, validamos que no haya sido usado el documento en un devengo (scp_Presupuestal)
            // y que su importe NO sea menor a lo que ya se ha devengado
            $this->conectar();

            // Marcos: 14/jun/2017
            // Cambiamos los valores de comparación del WHERE a los originales, para que no nos metan gol
            $this->setSql("
                SELECT
                    COUNT(*) AS total, IFNULL(SUM(ncp_percepcionNeto), 0) AS importeUsado
                FROM cat_BancoDocumentos d
                INNER JOIN scp_Presupuestal p USING(bdo_id)
                INNER JOIN scp_Afectacion a on p.gpo_id = a.gpo_id
                    AND p.ur_id = a.ur_id AND p.afe_id = a.afe_id
                WHERE d.gpo_id = ?
                    AND d.ur_id = ?
                    AND TRIM(d.tdf_id) = ?
                    AND d.bdo_referencia = ?
                    AND d.rfc_id = ?
                    AND eaf_id != 'C' ;
            ");
            $this->consultar($parametros);
            $row = $this->extraer();
            $importeUsado = $row['importeUsado'];
            $this->desconectar();

            // Buscamos si el documento ya fué usado en el devengado (scp_Presupuestal)
            // Esta consulta es solo para ver si ya fue usado en presupuestal y contable

            // Marcos: 14/jun/2017
            // Está mal ésta consulta, está permitiendo cambiar la referencia cuando el documento ya fué usado
            // Está mal porque está checando tdf y referencia, pero como mandan la nueva referencia, no encuentra
            // la anterior.   Para solventar ésto, se cambia $referencia por $refOriginal y se adiciona bdo_id

            $this->conectar();
            $this->setSql('
                SELECT COUNT(*) AS total
                FROM cat_BancoDocumentos d
                LEFT JOIN scp_Presupuestal p USING(bdo_id)
                LEFT JOIN scp_Contable c USING(bdo_id)
                WHERE d.gpo_id = ?
                AND d.ur_id = ?
                AND TRIM(d.tdf_id) = ?
                AND d.bdo_referencia = ?
                AND d.rfc_id = ?
                AND (p.bdo_id is not null or c.bdo_id is not null) ; 
            ');
            $this->consultar($parametros);
            $row = $this->extraer();
            $vecesUsado = $row['total'];
            $this->desconectar();

            // si ya se usó, NO pueden modificar el número de documento
            if ($vecesUsado > 0) {
                $cambiaReferencia = '';
            } else {
                $paramCambiaReferencia = [$tdf, mysql_escape_mimic($referencia), mysql_escape_mimic($rfcEmisor), date_to_mysqldate($fecha)];
                $cambiaReferencia = 'tdf_id = ?, ';
                $cambiaReferencia .= 'bdo_referencia = ?, ';
                // tambien podria cambiar el rfc emisor
                $cambiaReferencia .= 'rfc_id = ?, ';
                $cambiaReferencia .= 'bdo_fechaDocumento = ?, ';
            }
        } else {
            $importeUsado = 0;
        }

        // Si el importe que traen es menor que el que ya se ha devengado, no lo permitimos
        $importeNeto = str_replace(',', '', $importeNeto);

        // Marcos: 10/jul
        // Checamos el importe presupuestal, los demás no los consideraremos para ésta operación
        //$importeNuevo = (float) $importeNeto;
        $importeNuevo = (float) $percepPresup;

        if ($importeNuevo < $importeUsado) {
            $this->mixScript('
                notify("El importe es menor a lo que ya se ha devengado del documento", "error");
                setTimeout(function() { $("#importe").select().focus() }, 100);
                //window.espera.stop();
            ');
            return false;
        }

        // Cambio del 1ro. de julio 2015
        // Israel puntualiza que el campo bdo_Importe DEBE contener y ser igual al monto que venga en bdo_PercepPresup
        // Por lo que se ajusta en código para que lo haga, y en base de datos se corregirá lo que hoy primero haya sido
        // registrado con reglas distintas entre las 9 de la mañana y 12 del día.
        $importeNeto = $percepPresup;

        $this->conectar();
        // Adicionamos
        if ($tipo == 'a') {
            $excepcionClc = '';
            // Cambio 2016: Aquí pondremos las claves de tipo de documento fuente que llevan un control por UR
            // y que por lo tanto existan "duplicadas" las referencias, pero en diferentes URs
            $strTDFporUR = 'CERE.CH.CLC.CMP.FD.FMP.GRAL.NOTA.OTRO.RBO.REM.RCT';
            $parametros = [mysql_escape_mimic($rfcEmisor), mysql_escape_mimic($referencia)];
            if (strPos($strTDFporUR, $tdf) === 0 || strPos($strTDFporUR, $tdf) > 0) {
                $excepcionClc = ' and gpo_id = ? and ur_id = ? ';
                array_push($parametros, $this->clasAdmGpo, $this->clasAdmUr);
            }

            if ($tdf == 'RCT') {
                $excepcionClc .= " and tdf_id = 'RCT' ";
            }

            //buscamos que no esté siendo usada la misma referencia y proveedor
            $this->conectar();
            $this->setSql('
                SELECT count(*) veces, gpo_id, lpad(ur_id,2,"0") ur_id
                FROM cat_BancoDocumentos
                WHERE rfc_id = ?
                    AND bdo_referencia = ?
                    ' . $excepcionClc . '
                GROUP BY gpo_id,ur_id
            ');
            $this->consultar($parametros);
            $r = $this->extraer();
            $this->desconectar();
            if (is_array($r) && array_key_exists('veces', $r) && (int)$r['veces'] > 0) {
                $this->mixScript('
                    notify("La referencia:' . mysql_escape_mimic($referencia) . ' del proveedor:' . mysql_escape_mimic($rfcEmisor) . ' ya ha sido registrada en el Banco de Documentos por la UR:' . $r['gpo_id'] . $r['ur_id'] . '", "error");
                    setTimeout(function() { $("#rfcEmisor").select().focus() }, 300);
                    //window.espera.stop();
                ');
                return false;
            }

            $this->escribeBitacora('CAPTURA MANUAL');

            $parametros = [
                $this->clasAdmGpo,
                $this->clasAdmUr,
                $tdf,
                mysql_escape_mimic($referencia),
                mysql_escape_mimic($rfcEmisor),
                mysql_escape_mimic($concepto),
                str_replace(',', '', $total),
                str_replace(',', '', $percepPresup),
                str_replace(',', '', $percepContab),
                str_replace(',', '', $retenContab),
                $tipoClc,
                $fondoRotatorio,
                date_to_mysqldate($fecha),
                $bdo_estadoSat,
                $bdo_origen,
                $uuid,
                $this->usarioId,
            ];
            $this->setSql('
                INSERT INTO cat_BancoDocumentos(
                    gpo_id,
                    ur_id,
                    tdf_id,
                    bdo_referencia,
                    rfc_id,
                    bdo_agrupacionGlobal,
                    bdo_concepto,
                    bdo_total,
                    bdo_percepcionPresupuestal,
                    bdo_percepcionContable,
                    bdo_retencionContable,
                    bdo_limitadoTipoClc,
                    bdo_fondoRotatorio,
                    bdo_estadoDevengo,
                    bdo_fechaDocumento, sts_id, bdo_estadoPago, bdo_estadoSat, bdo_origen, bdo_uuid, usr_id
                )
                VALUES(
                ?, ?, ?,
                ?,
                ?,
                "GRAL",
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                1,
                ?,
                1,
                1,
                ?,
                ?,
                ?,
                ?
                );
            ');
        } elseif ($tipo == 'c') {

            // rfc_id = "' . mysql_escape_mimic($rfcEmisor) . '",
            // se quito porque debe ser tratada igual que la referencia a menos que no se permita el cambio de proveedor.
            // bdo_fechaDocumento = "' . date_to_mysqldate($fecha) . '",
            // la fecha del documento tampoco se debe permitir cambiar cuando ya está siendo usado el documento

            // Marcos: 09/julio, define Israel:
            // Si ya fué utilizado en al menos una afectación, sin importar si está cancelada o autorizada, NO SE
            // PERMITE modificar el fondo rotatorio

            // Marcos: 05/nov/2015
            // Cuando modificamos un documento, no cambiamos el usr_id, mantenemos el que lo dió de alta
            // Si quiero saber quién lo cambió, voy a la bitácora

            $this->escribeBitacora('MODIFICADO POR EL USUARIO.');

            if (strlen($cambiaReferencia)) {
                $parametros = $paramCambiaReferencia;
                array_push($parametros, mysql_escape_mimic($concepto));
            } else {
                $parametros = [mysql_escape_mimic($concepto)];
            }

            if (strlen($updateDeLaFecha)) {
                array_push($parametros, $paramDeLaFecha);
            }
            array_push($parametros, str_replace(',', '', $total));
            array_push($parametros, str_replace(',', '', $percepPresup));
            array_push($parametros, str_replace(',', '', $percepContab));
            array_push($parametros, str_replace(',', '', $retenContab));
            array_push($parametros, $tipoClc);
            array_push($parametros, $fondoRotatorio);
            array_push($parametros, $this->usarioId);
            array_push($parametros, $this->clasAdmGpo);
            array_push($parametros, $this->clasAdmUr);
            array_push($parametros, mysql_escape_mimic($rfcEOriginal));
            array_push($parametros, mysql_escape_mimic($refOriginal));

            $this->setSql('
                UPDATE cat_BancoDocumentos
                SET ' . $cambiaReferencia . '
                    bdo_concepto = ?,
                    ' . $updateDeLaFecha . '
                    bdo_total = ?,
                    bdo_percepcionPresupuestal = ?,
                    bdo_percepcionContable = ?,
                    bdo_retencionContable = ?,
                    bdo_limitadoTipoClc = ?,
                    bdo_fondoRotatorio = ?,
                    usr_id = ?
                WHERE gpo_id = ?
                AND ur_id = ?
                AND rfc_id = ?
                AND bdo_referencia = ?;
            ');
        }
        $this->consultar($parametros);

        // Si es un Alta, insertamos la línea en el grid
        if ($tipo == 'a') {
            $id = $this->getUltimoId(); // Obtenemos el id (bdo_id) que le tocó ...
            $this->mixScript('
                /*
                dataDocumentos.push({
                    "id": "' . $id . '",
                    "t" : "' . $tdf . '",
                    "r" : "' . $referencia . '",
                    "f" : "' . $fecha . '",
                    "e" : "' . $rfcEmisor . '",
                    "i" : "' . str_replace(',', '', $importeNeto) . '",
                    "c" : "' . $concepto . '",
                    "rct" : "0",
                    "fr" : "' . $fondoRotatorio . '",
                    "s" : 1,
                    "o" : 1,
                    "v" : 3
                    });

                gridDocumentos.updateRowCount();
                gridDocumentos.render();
                gridDocumentos.init();
                gridDocumentos.autosizeColumns(); */

                $("#tabla_documentos").tabla("llenaTabla");
                notify("Documento registrado exitosamente", "success");
                ' . (($bdo_origen != 2) ? '$("#myModalDocto").hide();' : '$("#myModalInfo").hide();') . '
            ');
        } elseif ($tipo == 'c') {
            $this->mixScript('
                /*
                var nLinActual = gridDocumentos.getSelectedRows();
                dataDocumentos[nLinActual].t = "' . $tdf . '";
                dataDocumentos[nLinActual].r = "' . $referencia . '";
                dataDocumentos[nLinActual].f  = "' . $fecha . '";
                dataDocumentos[nLinActual].e   = "' . $rfcEmisor . '";
                dataDocumentos[nLinActual].i   = "' . $importeNeto . '";
                dataDocumentos[nLinActual].c  = "' . $concepto . '";
                dataDocumentos[nLinActual].fr  = "' . $fondoRotatorio . '";
                gridDocumentos.invalidateRow(nLinActual);
                gridDocumentos.render();
                gridDocumentos.init();
                gridDocumentos.autosizeColumns(); */
                
                $("#tabla_documentos").tabla("llenaTabla");
                notify("Documento modificado exitosamente", "success");
                $("#myModalDocto").hide();
            ');
        }
        $this->desconectar();
        return false;
    }

    /*------------------- Cargamos un XML de un CFDI ------------------------*/
    private function cargarXmlCFDI()
    {
        $form = new stdClass();
        $form->id = 'validar';
        $form->controlador = $this->pantalla;
        $form->metodo = 'validarXmlCFDI';
        $formulario = new form($form);

        $html = '
            <div class="row">
                <label class="control-label col-md-12 col-xs-12">Archivo XML de un Comprobante Fiscal Digital por Internet (CFDI)</label>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    <input type="file" name="arch" id="arch" class="form-control">
                </fieldset>
            </div>
            <div class="row mt-2">
                <fieldset class="col-md-12 col-xs-12">
                    <input id="cargarValidar" type="submit" value="Cargar y Validar" class="btn btn-outline-primary ladda-button" data-style="slide-left">
                </fieldset>
            </div>
            <div id="resultadoValidacion"></div>
        ';

        $this->setContenedor('bodyModalInfo');
        $formulario->mixHtml($html);
        $this->mixhtml($formulario->ponForm());

        $this->mixScript('
            $("#myModalInfo").modal();
            $("#cargarValidar").click(function(e) {
                // Como no muestra el indicador de avance, cambiamos el texto
                $("#cargarValidar").val("Cargando ...");
            });
            $("#arch").click(function(e) {
                try {
                    //window.espera.stop();
                    $("#cargarValidar").val("Cargar y Validar");
                } catch (e) {
                    // nada
                }
            });
        ');
    }

    /*--------------- Validamos y cargamos los datos de un XML CFDI ---------------------*/
    private function validarXmlCFDI()
    {
        if (trim($_FILES['arch']['name']) == '') {
            $this->mixScript('
                notify("Especifica el archivo XML del CFDI a cargar", "warning");
                //window.espera.stop();
            ');
            return false;
        }

        if ($_FILES['arch']['error'] == 1 || $_FILES['arch']['size'] == 0) {
            $this->mixScript('
                notify("El tamaño del archivo excede límite máximo permitido", "error");
                //window.espera.stop();
            ');
            return false;
        }

        $arch = $_FILES['arch']['tmp_name'];
        $texto = file_get_contents($arch);

        // Hacemos una copia en memoria, ya que si graban el documento ésta cadena de memoria es la que se grabará como archivo XML
        $cfdiOriginal = $texto;
        // unlink($arch);

        // Quita Addenda solo valida fiscal
        $texto = preg_replace('{<Addenda.*/Addenda>}is', '<Addenda/>', $texto);

        //ALFONSO 2017-1027 Se elimina por completo la Addenda por que en CFDI 3.3 ya no la admite vacia.
        $texto = preg_replace('{<cfdi:Addenda.*/cfdi:Addenda>}is', '', $texto);

        // Cargamos el contenido del XML
        libxml_use_internal_errors(true);   // Gracias a Salim Giacoman
        $xmlCFDI = new DOMDocument();
        $ok = $xmlCFDI->loadXML($texto);
        if (!$ok) {
            $this->mixScript('
                notify("No se pudo cargar el XML, revisa que el archivo sea el correcto", "error");
                //window.espera.stop();
            ');
            return false;
        }

        libxml_use_internal_errors(true);   // Gracias a Salim Giacoman
        $xml = new DOMDocument();
        $ok = $xml->loadXML($texto);
        if (!$ok) {
            $this->display_xml_errors($texto);
            die();
        }

        // Marcos: 11/ago/2017
        // Para que reconozca la versión 3.3 del CFDI
        if (strpos($texto, 'cfdi:Comprobante') !== false) {
            $tipo = 'cfdi';
        } elseif (strpos($texto, '<Comprobante') !== false) {
            $tipo = 'cfd';
        } elseif (strpos($texto, 'retenciones:Retenciones') !== false) {
            $tipo = 'retenciones';
        } else {
            $this->mixScript('
                notify("Tipo de XML no identificado ....", "error");
                //window.espera.stop();
            ');
            return false;
        }

        //   Con el arbol DOM buscamos los atributos
        if ($tipo == 'retenciones') {
            $root = $xml->getElementsByTagName('Retenciones')->item(0);
            //ALFONSO 2017-09-12 Existia un error en el nombre de la variable ($Version).
            $version = $root->getAttribute('Version');
        } else {
            $root = $xml->getElementsByTagName('Comprobante')->item(0);
            $version = $root->getAttribute('Version');
            if ($version == null) {
                $version = $root->getAttribute('Version');
            }
        }

        $receptor = $root->getElementsByTagName('Receptor')->item(0);
        $emisor = $root->getElementsByTagName('Emisor')->item(0);
        $data['seri'] = $root->getAttribute('Serie');

        //ALFONSO 2017-09-12 En la version 3.3 del CFDI el campo Fecha comienza con mayuscula.
        $data['fecha'] = $version == '3.3' ? $root->getAttribute('Fecha') : $root->getAttribute('Fecha');
        $data['noap'] = $root->getAttribute('noAprobacion');
        $data['anoa'] = $root->getAttribute('anoAprobacion');
        $data['tipo'] = $tipo;
        $folio = '';

        if ($tipo == 'retenciones') {
            $rfc = $emisor->getAttribute('RFCEmisor');
            $data['rfc'] = utf8Decode($rfc);
            $nom = $emisor->getAttribute('NomDenRazSocE');
            $data['nom'] = utf8Decode($nom);

            $rfc = $receptor->getAttribute('RFCRecep');
            $data['rfc_receptor'] = utf8Decode($rfc);
            $nom = $receptor->getAttribute('NomDenRazSocE');
            $data['nom'] = utf8Decode($nom);

            $serie = $data['UUID'];
            $folio = '';

            $data['version'] = $root->getAttribute('Version');
            $data['no_cert'] = $root->getAttribute('NumCert');
            $data['cert'] = $root->getAttribute('Cert');
            $data['sell'] = $root->getAttribute('Sello');
            $Totales = $root->getElementsByTagName('Totales')->item(0);
            $data['total'] = $Totales->getAttribute('montoTotGrav');
        } else {
            $data['version'] = $version;
            if ($version == '3.3') { // Mayusculas
                $data['total'] = $root->getAttribute('Total');
                $data['no_cert'] = $root->getAttribute('NoCertificado');
                $data['cert'] = $root->getAttribute('Certificado');
                $data['sell'] = $root->getAttribute('Sello');

                $rfc = $emisor->getAttribute('Rfc');
                $data['rfc'] = utf8Decode($rfc);
                $nom = $emisor->getAttribute('Nombre');
                $data['nom'] = utf8Decode($nom);

                $rfc = $receptor->getAttribute('Rfc');
                $data['rfc_receptor'] = utf8Decode($rfc);
                $nom = $receptor->getAttribute('Nombre');
                $data['nom_receptor'] = utf8Decode($nom);
            } else { // NO es 3.3, es 3.2 o anterior minusculas
                $data['total'] = $root->getAttribute('Total');
                $data['no_cert'] = $root->getAttribute('NoCertificado');
                $data['cert'] = $root->getAttribute('Certificado');
                $data['sell'] = $root->getAttribute('Sello');

                $rfc = $emisor->getAttribute('Rfc');
                $data['rfc'] = utf8Decode($rfc);
                $nom = $emisor->getAttribute('Nombre');
                $data['nom'] = utf8Decode($nom);

                $rfc = $receptor->getAttribute('Rfc');
                $data['rfc_receptor'] = utf8Decode($rfc);
                $nom = $receptor->getAttribute('Nombre');
                $data['nom_receptor'] = utf8Decode($nom);
            } // version 3.3
        } // Retencion o CFDI
        $TFD = $root->getElementsByTagName('TimbreFiscalDigital')->item(0);
        if ($TFD != null) {
            $data['version_tfd'] = $TFD->getAttribute('Version');
            if ($data['version_tfd'] == '') {
                $data['version_tfd'] = $TFD->getAttribute('Version');
            }
            if ($data['version_tfd'] == '1.0') {
                $data['sellocfd'] = $TFD->getAttribute('SelloCFD');
                $data['sellosat'] = $TFD->getAttribute('SelloSAT');
                $data['no_cert_sat'] = $TFD->getAttribute('NoCertificadoSAT');
            } else {
                $data['sellocfd'] = $TFD->getAttribute('SelloCFD');
                $data['sellosat'] = $TFD->getAttribute('SelloSAT');
                $data['no_cert_sat'] = $TFD->getAttribute('NoCertificadoSAT');
            }
            $data['uuid'] = $TFD->getAttribute('UUID');
        } else {
            $data['sellocfd'] = null;
            $data['sellosat'] = null;
            $data['no_cert_sat'] = null;
            $data['uuid'] = null;
        }

        // Validamos el CFDI
        $forma = '';
        $validaSAT = '';
        $forma = $this->valida_xsd($data, $xml, $texto, $tipo); // esquema o 'sintaxis'

        //ALFONSO 2017-09-19 Se elimina la  validacion de semantica debido a que faltan tablas en
        //la base de datos, las cuales podrian crearse y descargarse del SAT, pero esto aumentaria
        //la carga de los servidores, ya que deben actualizarse diariamente, asi que no se aprecia
        //beneficio en esta validacion.
        //ALFONSO 2017-09-19 Validamos el sello del Comprobante
        $forma .= $this->valida_sello($data, $xml); // sello del comprobante
        if ($data['sellosat'] != '') {
            $forma .= $this->valida_sello_tfd($texto, $data); // sello del timbre
            if ($data['tipo'] == 'cfdi') {
                $validaSAT .= $this->valida_en_sat($data); // Por lo pronto retenciones no se valida en SAT
            }
        }

        // Ajustamos todas las variables que usaremos, y que ya debemos tener determinadas en éste punto
        // Las asignamos a las variables que se usaban, para mantener el código de guardado intacto
        // aunque con las nuevas validaciones
        $fecha = $data['fecha'];
        $fechaFormato = new DateTime($fecha);
        $fechaFormato = $fechaFormato->format('d-m-Y H:i:s');

        $serie = $data['seri'];

        // Para los documentos que se usen en Clc de Obra y Administración Directa, el importe sera subtotal + impuestos
        // para las demás será total
        //ALFONSO 2017-09-28 No se esta evaluando la version del CFDI, si es menor a 3.3 el nombre del campo inicia con mayuscula.
        if ($version == '3.3') {
            $subtotal = number_format(floatval($root->getAttribute('SubTotal')), 2, '.', ',');
            $subtotalSinComas = floatval($root->getAttribute('SubTotal'));
            $descuento = number_format(floatval(($root->getAttribute('Descuento') !== null ?
                $root->getAttribute('Descuento') : '')), 2, '.', ',');
            $descuentoSinComas = floatval(($root->getAttribute('Descuento') !== null ?
                $root->getAttribute('Descuento') : ''));
        } else {
            $subtotal = number_format(floatval($root->getAttribute('SubTotal')), 2, '.', ',');
            $subtotalSinComas = floatval($root->getAttribute('SubTotal'));
            $descuento = number_format(floatval(($root->getAttribute('Descuento') !== null ?
                $root->getAttribute('Descuento') : '')), 2, '.', ',');
            $descuentoSinComas = floatval(($root->getAttribute('Descuento') !== null ?
                $root->getAttribute('Descuento') : ''));
        }

        $total = number_format(floatval($data['total']), 2, '.', ',');
        $importeNeto = $data['total'];
        $rfcEmisor = $data['rfc'];
        $nombreEmisor = $data['nom'];

        $rfcReceptor = $data['rfc_receptor'];
        $nombreReceptor = $data['nom_receptor'];

        // Impuestos
        $trasladados = '0.00';
        $trasladadosSinComas = '0.00';
        $retenidos = '0.00';
        $retenidosSinComas = '0.00';

        $Imptos = $root->getElementsByTagName('Impuestos')->item(0);

        if ($version == '3.3') {
            //ALFONSO 2017-09-12 En la version 3.3 del CFDI los impuestos exentos ya no se incluyen,
            //ademas cada concepto tiene una etiqueta de Impuestos, asi que debemos buscar la etiqueta
            //que incluye los totales.
            $Imptos = $root->getElementsByTagName('Impuestos');
            if ($Imptos != null) {
                foreach ($Imptos as $imp) {
                    if ($imp->hasAttributes()) {
                        $impTras = $imp->getAttribute('TotalImpuestosTrasladados');
                        $impRet = $imp->getAttribute('TotalImpuestosRetenidos');
                    }
                }
            }
        } elseif ($version == '4.0') {
            $Imptos = $root->getElementsByTagName('Impuestos');
            if ($Imptos != null) {
                foreach ($Imptos as $imp) {
                    if ($imp->hasAttributes()) {
                        $impTras = $imp->getAttribute('TotalImpuestosTrasladados');
                        $impRet = $imp->getAttribute('TotalImpuestosRetenidos');
                    }
                }
            }
        } else {
            $Imptos = $root->getElementsByTagName('Impuestos')->item(0);
            $impTras = $Imptos->getAttribute('TotalImpuestosTrasladados');
            $impRet = $Imptos->getAttribute('TotalImpuestosRetenidos');
        }

        $trasladados = number_format(floatval($impTras), 2, '.', ',');
        $trasladadosSinComas = floatval($impTras);
        $retenidos = number_format(floatval($impRet), 2, '.', ',');
        $retenidosSinComas = floatval($impRet);

        // Los impuestos locales ya no vienen en el XML principal de CFDI V3.3, sino ahora es un complemento
        // pero como no lo usamos más adelante, YA NO incluiremos en el código la extracción de esos valores
        // $ImpuestosLocalesTotalRetenciones
        // $retenidosSinComas
        // $retenidos

        // Reglas emitidas el 16/junio/2015 para desglosar los importes en percepciones presupuestales y contables,
        // y retenciones contables, cuando se trate de CFDIs cargados desde el XML
        // En general será
        //     percepciones presupuestales = (subtotal - descuento) + total impuestos trasladados
        //     retenciones contables       = total impuestos retenidos
        // para clc contrato
        //		percepciones presupuestales = (subtotal + totalImpuestosTrasladados)
        //     retenciones contables       = total impuestos retenidos
        // Aquí ponemos el comporamiento "normal", ya que con javascript modificarán si quieren que sea de obra o no

        $percepPresup = ($subtotalSinComas - $descuentoSinComas) + $trasladadosSinComas;
        $percepContab = '0.00';
        $retenContab = $retenidosSinComas;
        $totalPresup = number_format(($percepPresup - $retenContab), 2, '.', ',');

        // 3/JULIO/2015
        // Derivado del problema que se presenta con facturas que incluyen el IEPS, se decidió (para xmls)
        // tomar como el total de percepciones presupuestales el tag TOTAL, y nada en retenciones (contables y presupuestales)
        // Edna y Hector vinieron a checarlo, y en reunión con contabilidad ayer 2 de julio lo ratificaron, estamos esperando
        // el ticket, pero lo programamos

        $percepPresup = number_format($percepPresup, 2, '.', ',');
        $retenContab = number_format($retenContab, 2, '.', ',');

        $percepPresup = $importeNeto;
        $totalPresup = $importeNeto;
        //$retenContab = 0.00;
        //$retenPresup = 0.00;

        // Cuando se cargue desde XML, usaremos uuid como referencia del documento
        if ($TFD != null) {
            $serie = $data['uuid'];
        }

        $uuid = $data['uuid'];

        $otroImporte = number_format($subtotalSinComas + $trasladadosSinComas, 2, '.', ',');

        // Si NO trae folio el número de factura, les permitirmos escribirlo
        // Si sí lo trae, desactivamos el campo
        if ($folio == '') {
            $capturaReferencia = '';
        } else {
            $capturaReferencia = 'disabled';
        }

        // Validamos que el proveedor exista en la base de datos
        $valProveedor = '';
        $valProveedorSCP = $this->ExisteProveedorDeFacturaSCP($rfcEmisor);
        $valProveedorSAT = $this->ExisteProveedorDeFacturaSAT($rfcEmisor);
        if ($valProveedorSCP == true) {
            $valProveedor .= 'SÍ EXISTE EN SEFIP.   ';
        } else {
            $valProveedor .= 'NO EXISTE EN SEFIP.   ';
        }
        if ($valProveedorSAT == true) {
            $valProveedor .= 'SÍ EXISTE EN LCO DEL SAT.';
        } else {
            $valProveedor .= 'NO EXISTE EN LCO DEL SAT.';
        }

        // Si hay un error, NO mostramos el botón para registrar la factura
        //ALFONSO 2017-09-08 La variable $errorEnSAT no ha sido inicializada, asumo que valida si el comprobante esta vigente en el SAT.

        $errorEnForma = !(strpos($forma, 'Esquema valido') && strpos($forma, 'Sello OK'));
        $errorEnSAT = !(strpos($validaSAT, 'S -') && strpos($validaSAT, 'Vigente'));

        // OJO OJO OJO
        // Parece que hasta hoy 06/nov/2018 el sat está haciendo cambios... por instrucciones de Israel
        // nos "saltaremos" ésta validación
        $errorEnSAT = false;

        //ALFONSO 2017-09-08 Aparentemente la variable $forma no esta siendo evaluada correctamente.
        //$forma != "Esquema 3.2 correcto. Sello Válido. Sello TFD Válido. "

        if (($errorEnSAT) || ($errorEnForma) || ($valProveedorSAT == false) || ($valProveedorSCP == false)) {
            $ponBotonGrabar = false;
            // Si pasó todas las validaciones, entonces sí
        } else {
            $ponBotonGrabar = true;
        }


        // Si pasó las validaciones ... grabamos el XML
        if ($ponBotonGrabar) {

            $nombre_archivo = $uuid . '.xml';
            $rutatem = dirname(__FILE__) . '/../../scp/tmpdoctos/';

            if (!is_dir($rutatem)) {
                if (!mkdir($rutatem, 0777, true)) {
                    echo 'No se pudo crear el directorio: ' . $rutatem;
                    die;
                }
            }

            if (!isset($_FILES['arch']) || $_FILES['arch']['error'] !== UPLOAD_ERR_OK) {
                echo 'Error al subir archivo: ' . $_FILES['arch']['error'];
                die;
            }

            $tmp_name = $_FILES['arch']['tmp_name'];

            if (!file_exists($tmp_name)) {
                $data = file_get_contents($tmp_name);
                print_r($texto);
                echo 'Archivo temporal no existe';
                die;
            }

            $name = $rutatem . $nombre_archivo;

            if (!move_uploaded_file($tmp_name, $name)) {
                echo 'Error al mover el archivo a destino';
                die;
            }

            $up = new FicheroSefip();
            $up->setTipos(['xml']);
            $up->setModulo('scp');
            $up->setfile($nombre_archivo);
            $up->setRuta('bancodocumentos/' . date('m'));
            $up->setUrFichero($this->clasAdmGpo . $this->clasAdmUr);
            $up->setAnioFichero(APP_EJERCICIO);
            $file = ['name' => $name, 'tmp_name' => $name, 'eslocal' => true];
            $up->setFichero($file);
            $up->upload();
            // Grabamos la cadena que se cargó en memoria, para mantener los XMLs
        }

        // Armamos la forma donde mostraremos los datos del CFDI y mostraremos los resultados de las
        // validaciones que haremos
        //ALFONSO 2017-09-08 Se agrego utf8_encode al nombre del Emisor y Receptor.

        $html = '
            <div class="row">
                <label class="control-label col-md-5">Referencia (UUID)</label>
                <label class="control-label col-md-3">Fecha</label>
                <label class="control-label col-md-4"><font color="green">Importe que toma SEFIP</font></label>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-5">
                    <input type="text" name="CFDIreferencia" placeholder="No. Referencia" maxlength="5" value="' . $serie . $folio . '" id="CFDIreferencia" class="form-control" style="text-transform:none !important;" tabindex="20" ' . $capturaReferencia . '>
                </fieldset>
                <fieldset class="col-md-3">
                    <input type="text" name="CFDIfecha" maxlength="50" value="' . $fechaFormato . '" id="CFDIfecha" class="form-control" style="text-transform:none !important;" tabindex="30" disabled>
                </fieldset>
                <fieldset class="col-md-4">
                    <input type="text" name="CFDIimporteNeto" maxlength="50" value="' . $total . '" id="CFDIimporteNeto" class="form-control wr-input moneda" style="text-transform:none !important; text-align:right" tabindex="40" disabled>
                </fieldset>
            </div>

            <!-- Lic. Andrea 12/feb/2015 Para Inversión y Administración Directa pide específicamente que se desglocen éstos campos
            pero se incluye para todo porque no agruparemos por tipo de CLC los documentos -->
            <div class="row">
                <label class="control-label col-md-2">Subtotal</label>
                <label class="control-label col-md-2">Descuento</label>
                <label class="control-label col-md-3">Imp. Retenidos</label>
                <label class="control-label col-md-3">Imp. Trasladados</label>
                <label class="control-label col-md-2">Total</label>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-2">
                    <input type="text" name="subtotal" placeholder="Subtotal" maxlength="20" value="' . $subtotal . '" id="subtotal" class="form-control" style="text-transform:none !important; text-align:right" tabindex="50" disabled>
                </fieldset>
                <fieldset class="col-md-2">
                    <input type="text" name="descuento" placeholder="Descuento" maxlength="20" value="' . $descuento . '" id="descuento" class="form-control" style="text-transform:none !important; text-align:right" tabindex="60" disabled>
                </fieldset>
                <fieldset class="col-md-3">
                    <input type="text" name="retenidos" placeholder="Retenidos" maxlength="20" value="' . $retenidos . '" id="retenidos" class="form-control" style="text-transform:none !important; text-align:right" tabindex="70" disabled>
                </fieldset>
                <fieldset class="col-md-3">
                    <input type="text" name="trasladados" placeholder="Trasladados" maxlength="20" value="' . $trasladados . '" id="trasladados" class="form-control" style="text-transform:none !important; text-align:right" tabindex="80" disabled>
                </fieldset>
                <fieldset class="col-md-2">
                    <input type="text" name="total" placeholder="Total" maxlength="20" value="' . $total . '" id="total" class="form-control noeditable" style="text-transform:none !important; text-align:right" tabindex="90" disabled>
                </fieldset>
            </div>

            <!-- 16/junio/2016 Egresos y Contabilidad definen un desglose en percepciones presupuestales y contables, y retenciones contables
            así que incluímos los campos para que la UR sepa cómo van a quedar -->
            <div class="row">
                <label class="control-label desgloceImporte col-md-3">Percepciones Presupuestales</label>
                <label class="control-label desgloceImporte col-md-3">Percepciones Contables</label>
                <label class="control-label desgloceImporte col-md-3">Retenciones Contables</label>
                <label class="control-label col-md-3">Total</label>
            </div>

            <div class="row mb-4">
                <fieldset class="col-md-3">
                    <input class="form-control percepcionPresupuestal percepPresupXML" type="text" name="percepPresup" placeholder="" maxlength="20" value="' . $percepPresup . '" style="text-transform:none !important; text-align:right" id="percepPresup" tabindex="52" disabled>
                </fieldset>
                <fieldset class="col-md-3">
                    <input class="form-control percepContabXML" type="text" name="percepContab" placeholder="" maxlength="20" value="' . $percepContab . '" style="text-transform:none !important; text-align:right" id="percepContab" tabindex="53" disabled>
                </fieldset>
                <fieldset class="col-md-3">
                    <input class="form-control retenContabXML" type="text" name="retenContab" placeholder="" maxlength="20" value="' . $retenContab . '" style="text-transform:none !important; text-align:right" id="retenContab" tabindex="54" disabled>
                </fieldset>
                <fieldset class="col-md-3">
                    <input type="text" name="total" placeholder="Total" maxlength="20" value="' . $totalPresup . '" id="total" class="form-control noeditable totalXML" style="text-transform:none !important; text-align:right" tabindex="100" disabled>
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-xs-12">
                <p>El importe que tomará el presupuesto es PERCEPCIONES PRESUPUESTALES.   Los demás campos están para desglosar el documento y facilitar el registro contable</p>
                </fieldset>
            </div>
            <div class="row">
                    <label class="control-label col-md-6">RFC/Nombre del emisor</label>
                    <label class="control-label col-md-6">RFC/Nombre del receptor</label>
            </div>

            <div class="row mb-4">
                <fieldset class="col-md-6">
                    <input type="text" name="CFDIEmisor"  id="CFDIEmisor" maxlength="20" value="' . $rfcEmisor . ' / ' . utf8Encode($nombreEmisor) . '" tabindex="100" class="form-control F1Catalogo" placeholder="F1: Catalogo" disabled/>
                </fieldset>
                <fieldset class="col-md-6">
                    <input type="text" name="CFDIReceptor"  id="CFDIReceptor" maxlength="20" value="' . $rfcReceptor . ' / ' . utf8Encode($nombreReceptor) . '" tabindex="110" class="form-control F1Catalogo" placeholder="F1: Catalogo" disabled/>
                </fieldset>
            </div>
            <div class="row">
                <label class="control-label col-xs-12">Concepto del documento</label>
            </div>

            <div class="row mb-4">
                <fieldset class="col-xs-12">
                    <input type="text" name="CFDIconcepto"  id="CFDIconcepto" maxlength="100" value="" tabindex="120" class="form-control" placeholder="100 caracteres máximo" style="text-transform:uppercase"/>
                </fieldset>
            </div>
            <div class="row">
                <label class="control-label col-xs-12">Validaciones</label>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-2">De forma y sintaxis</fieldset>
                <fieldset class="col-md-10">
                    <textarea name="valForma" id="valForma" rows="3" class=" form-control " style="text-transform:none !important;" tabindex="200" >' . $forma . '</textarea>
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-2">Verificación ante el SAT</fieldset>
                <fieldset class="col-md-10">
                    <input type="text" name="valSAT" id="valSAT" maxlength="100" class="form-control"  value="' . $validaSAT . '" tabindex="210"/>
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-2">Del Proveedor</fieldset>
                <fieldset class="col-md-10">
                    <input type="text" name="valRFC"  id="valRFC" maxlength="100" value="' . $valProveedor . '" tabindex="220" class="form-control" placeholder="100 caracteres máximo"/>
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-2">Uso en CLC</fieldset>
                <fieldset class="col-md-5">
                    <input type="checkbox" id="chkTipoClcCFDI" name="chkTipoClcCFDI" value="" tabindex="170"> Usar el Importe Bruto
                </fieldset>
                <fieldset class="col-md-5">
                    <input type="checkbox" id="chkFondoRotatorioXML" name="chkFondoRotatorioXML" value="" tabindex="175"> Se usará en una CLC para reposición del Fondo Rotatorio
                </fieldset>
            </div>
                
            ' . (($ponBotonGrabar == true) ? '
            <div class="row">
                <fieldset class="col-xs-12">
                    <div class="right">
                        <button id="CFDIgrabarDocumento" class="btn btn-outline-success ladda-button" data-style="slide-left" style="float:right;" tabindex="180">
                            <i class="glyphicon glyphicon-white glyphicon-ok"></i>Grabar Documento
                        </button>
                    </div>
                </fieldset>
            </div>
                ' : '') . '
            </form>
        ';

        $this->setContenedor('bodyModalInfo');
        $this->mixHtml($html);
        // No pueden editar los campos de validaciones
        //ALFONSO: 2017-09-12 El tamaño por defecto del modal es muy pequeño para el contenido, asi que establecemos overflow-y a auto.
        $this->mixScript('
            /*$("#chkTipoClcCFDI").change(function(e){
                if ($("#chkTipoClcCFDI").prop("checked")) {
                    $("#chkFondoRotatorioXML").prop("checked",false)
                }
            });
            $("#chkFondoRotatorioXML").change(function(e){
                if ($("#chkFondoRotatorioXML").prop("checked")) {
                    $("#chkTipoClcCFDI").prop("checked",false)
                }
            });*/
            $(".modal-content").css("overflow-y", "auto");
            $("#valSAT").keydown(false);   // No pueden modificar éste campo
            $("#valForma").keydown(false);   // No pueden modificar éste campo
            $("#valRFC").keydown(false);   // No pueden modificar éste campo
        ');

        // Si hubo error en la validación del SAT
        if ($errorEnSAT) { // nota: tres signos de igual.   SI NO ES VIGENTE ...
            $this->mixScript('
                $("#valSAT").removeClass("btn-outline-success");
                $("#valSAT").addClass("error");
            ');
        } else {
            $this->mixScript('
                $("#valSAT").removeClass("error");
                $("#valSAT").addClass("btn-outline-success");
                $("#valRFC").addClass("btn-outline-success");
            ');
        }
        // Si hubo error en la validación de forma/sintaxis
        if ($errorEnForma) {  //($forma != "Esquema 3.2 correcto. Sello Válido. Sello TFD Válido. ") {
            $this->mixScript('
                $("#valForma").removeClass("btn-outline-success");
                $("#valForma").addClass("error");
            ');
        } else {
            $this->mixScript('
                $("#valForma").removeClass("error");
                $("#valForma").addClass("btn-outline-success");
            ');
        }
        // Si hubo error en la validación del proveedor en SINPRES y LCO del SAT
        // Si no existe en el sinpres, pero sí existe en la LCO...
        if ($valProveedorSCP == false && $valProveedorSAT == true) {
            $this->mixScript('
                $("#valRFC").removeClass("error");
                $("#valRFC").removeClass("btn-outline-success");
                $("#valRFC").addClass("warning");
            ');
        } elseif (
            ($valProveedorSCP == false && $valProveedorSAT == false) ||
            ($valProveedorSCP == true && $valProveedorSAT == false)
        ) {
            $this->mixScript('
                $("#valRFC").removeClass("btn-outline-success");
                $("#valRFC").removeClass("warning");
                $("#valRFC").addClass("error");
            ');
        } elseif ($valProveedorSCP == true && $valProveedorSAT == true) {
            $this->mixScript('
                $("#valRFC").removeClass("error");
                $("#valRFC").removeClass("warning");
                $("#valRFC").addClass("btn-outline-success");
            ');
        }

        // Si no trae folio, el campo con el focus será el de referencia
        // Si sí lo trae, el focus irá al campo concepto
        if (trim($folio) == '') {
            $this->mixScript('setTimeout(function() { $("#CFDIreferencia").focus() }, 100);');
        } else {
            $this->mixScript('setTimeout(function() { $("#CFDIconcepto").focus() }, 100);');
        }

        // Los datos que se grabarán los ponemos como atributos del botón, por si quieren modificar el
        // DOM para alterarlos (que les cueste dos segundos más averiguarlo)
        $this->mixScript('
            try {
                //window.espera.stop();
            } catch(e) {
                // nada
            }

            $("#CFDIgrabarDocumento").attr("ref", "' . $serie . $folio . '");
            $("#CFDIgrabarDocumento").attr("fe", "' . $fecha . '");
            $("#CFDIgrabarDocumento").attr("to", "' . $total . '");
            $("#CFDIgrabarDocumento").attr("st", "' . $subtotal . '");
            $("#CFDIgrabarDocumento").attr("de", "' . $descuento . '");
            $("#CFDIgrabarDocumento").attr("ir", "' . $retenidos . '");
            $("#CFDIgrabarDocumento").attr("it", "' . $trasladados . '");
            $("#CFDIgrabarDocumento").attr("tf", "' . $total . '");
            $("#CFDIgrabarDocumento").attr("em", "' . $rfcEmisor . '");
            $("#CFDIgrabarDocumento").attr("uu", "' . $uuid . '");

            $("#CFDIgrabarDocumento").click(function(e){
                e.preventDefault();
                // Validamos campos obligatorios
                if ($("#CFDIgrabarDocumento").attr("ref").trim() == "") {
                    notify("El número de CFDI no puede quedar vacío", "warning");
                    return false;
                }
                if ($("#CFDIgrabarDocumento").attr("fe").trim() == "") {
                    notify("La fecha del CFDI no puede quedar vacía", "warning");
                    return false;
                }
                if ($("#CFDIgrabarDocumento").attr("em").trim() == "") {
                    notify("El RFC del emisor del CFDI no puede quedar vacío", "warning");
                    return false;
                }
                if ($("#CFDIgrabarDocumento").attr("to").trim() == "") {
                    notify("El importe neto del CFDI no puede quedar vacío", "warning");
                    return false;
                }
                if (parseFloat($("#CFDIgrabarDocumento").attr("to")) <= 0) {
                    notify("El importe neto del CFDI no puede ser igual o menor a cero", "warning");
                    return false;
                }
                //window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...

                // Grabamos en la base de datos ...
                comunica("' . $this->pantalla . '", {
                    "metodo" : "grabarDocumento",
                    "tdf" : "FACT",
                    "ref" : $("#CFDIgrabarDocumento").attr("ref"),
                    "fecha" : $("#CFDIgrabarDocumento").attr("fe"),
                    "rfcE" : $("#CFDIgrabarDocumento").attr("em"),
                    "c" : $("#CFDIconcepto").val(),
                    "neto" : $("#CFDIgrabarDocumento").attr("to").replace(",", ""),
                    "st" : $("#CFDIgrabarDocumento").attr("st").replace(",", ""),
                    "de" : $("#CFDIgrabarDocumento").attr("de").replace(",", ""),
                    "ir" : $("#CFDIgrabarDocumento").attr("ir").replace(",", ""),
                    "it" : $("#CFDIgrabarDocumento").attr("it").replace(",", ""),
                    "to" : $("#CFDIgrabarDocumento").attr("tf").replace(",", ""),
                    "tpp" : $(".percepPresupXML").val(),
                    "tpc" : $(".percepContabXML").val(),
                    "trc" : $(".retenContabXML").val(),
                    "clc" : (($("#chkTipoClc").prop("checked")) ? "1" : "0"),
                    "frt" : (($("#chkFondoRotatorioXML").prop("checked")) ? "S" : "N"),
                    "uu" : $("#CFDIgrabarDocumento").attr("uu"),
                    "t" : "a",
                    "ori" : $("#CFDIgrabarDocumento").attr("ref") ,
                    "rfcE_ori":$("#CFDIgrabarDocumento").attr("em")
                });
                e.stopPropagation();
                window.espera.stop();

                // Cerramos la modal
                $("#myModalInfo").modal("hide");
                return false;
            });

            // Para facturas de Clc de Obra y Administración Directa
            $("#chkTipoClcCFDI").change(function(e){
                // Si es "especial"
                if ($("#chkTipoClcCFDI").prop("checked")) {
                    $("#CFDIimporteNeto").val("' . $otroImporte . '");
                    $("#CFDIgrabarDocumento").attr("to", "' . $otroImporte . '");
                    $(".percepcionPresupuestal").val("' . $otroImporte . '");
                    $(".totalXML").val("' . $otroImporte . '");
                } else {
                    $("#CFDIimporteNeto").val("' . $total . '");
                    $("#CFDIgrabarDocumento").attr("to", "' . $total . '");
                    $(".percepcionPresupuestal").val("' . $total . '");
                    $(".totalXML").val("' . $total . '");
                }
            });
        ');
    }

    /*------- Validamos la forma y sintaxis (sólo permitimos CFDIs versión 3.2 y 3.3)------*/
    private function valida_xsd($data, $xml, $texto, $tipo)
    {
        /*
         * Todos los archivos que se requieren para hacer la validacion
         * fueron descargados del portal del SAT pero los tengo localmente
         * almacenados en mi maquina para que las validaciones sean mas rapidas.
         * Ademas el archivo prinicpal cfdv32.xsd esta 'un poco' modifcado para
         * que importe los complementos
         *
         * */

        $avisos = '';
        $ok = true;
        libxml_use_internal_errors(true);   // Gracias a Salim Giacoman
        if ($data['tipo'] == 'retenciones') {
            switch ($data['version']) {
                case '1.0':
                    $avisos .= 'Version 1.0 Retenciones. ';
                    $ok = $xml->schemaValidate('' . './lib/SAT/xsd/retencionpagov1.xsd');
                    break;
                default:
                    $ok = false;
                    $avisos .= "1-> Version invalida $tipo " . $data['version'];
            }
        } else {
            switch ($data['version']) {
                case '2.0':
                    $avisos .= 'Version 2.0 CFD. ';
                    $ok = $xml->schemaValidate('' . './lib/SAT/xsd/cfdv2complemento.xsd');
                    break;
                case '2.2':
                    $avisos .= 'Version 2.2 CFD. ';
                    $ok = $xml->schemaValidate('' . './lib/SAT/xsd/cfdv22complemento.xsd');
                    break;
                case '3.0':
                    $avisos .= 'Version 3.0 (CFDI). ';
                    $ok = $xml->schemaValidate('' . './lib/SAT/xsd/cfdv3complemento.xsd');
                    break;
                case '3.2':
                    $avisos .= 'Version 3.2 CFDI. ';
                    $ok = $xml->schemaValidate('' . './lib/SAT/xsd/cfdv32.xsd');
                    break;
                case '3.3':
                    $avisos .= 'Version 3.3 CFDI. ';
                    $ok = $xml->schemaValidate('' . '../../lib/scp/SAT/xsd/cfdv33.xsd');
                    break;
                case '4.0':
                    $avisos .= 'Version 4.0 CFDI. ';
                    $ok = $xml->schemaValidate('' . '../../lib/scp/SAT/xsd/cfdv40.xsd');
                    break;
                default:
                    $ok = false;
                    $avisos .= "2-> Version invalida $tipo " . $data['version'];
            }
        }
        if ($ok) {
            $avisos .= 'Esquema valido. ';
        } else {
            $avisos .= 'Estructura contra esquema incorrecta. ';
            $this->display_xml_errors($texto);
        }
        return $avisos;
    }

    /* ------------------ Validamos el sello ----------------------------
        Todos los archivos que se requieren para generar la cadena original
        fueron descargados del portal del SAT pero los tengo localmente
        almacenados en mi maquina para que el proceso sea mas rapido.
        Todos los archivos estan modificacion por el numero de version 2 a 1,
        para que no mande warning PHP
    --------------------------------------------------------------------*/
    private function valida_sello($data, $xml)
    {
        //$error = false;
        $avisos = '';
        $xsl = new DOMDocument();
        if ($data['tipo'] == 'retenciones') {
            switch ($data['version']) {
                case '1.0':
                    $xsl->load('' . '../../lib/scp/SAT/xslt/retenciones.xslt');
                    $algo = OPENSSL_ALGO_SHA1;
                    break;
                default:
                    //$error = true;
                    $avisos .= 'version incorrecta ' . $data['tipo'] . ' ' . $data['version'] . "\n";
                    break;
            }
        } else {
            switch ($data['version']) {
                case '2.0':
                    $xsl->load('' . '../../lib/scp/SAT/xslt/cadenaoriginal_2_0.xslt');
                    if (substr($data['fecha'], 0, 4) < 2011) {
                        $avisos .= "md5 \n";
                        $algo = OPENSSL_ALGO_MD5;
                    } else {
                        $avisos .= "sha1 \n";
                        $algo = OPENSSL_ALGO_SHA1;
                    }
                    break;
                case '2.2':
                    $avisos .= "2.2\n";
                    $xsl->load('' . '../../lib/scp/SAT/xslt/cadenaoriginal_2_2.xslt');
                    $avisos .= "sha1 \n";
                    $algo = OPENSSL_ALGO_SHA1;
                    break;
                case '3.0':
                    $xsl->load('' . '../../lib/scp/SAT/xslt/cadenaoriginal_3_0.xslt');
                    if (substr($data['fecha'], 0, 4) < 2011) {
                        $avisos .= "md5 \n";
                        $algo = OPENSSL_ALGO_MD5;
                    } else {
                        $avisos .= "sha1 \n";
                        $algo = OPENSSL_ALGO_SHA1;
                    }
                    break;
                case '3.2':
                    $avisos .= "3.2 \n";
                    $xsl->load('' . '../../lib/scp/SAT/xslt/cadenaoriginal_3_2.xslt');
                    $avisos .= "sha1 \n";
                    $algo = OPENSSL_ALGO_SHA1;
                    break;
                case '3.3':
                    $avisos .= "3.3 \n";
                    $xsl->load('' . '../../lib/scp/SAT/xslt/cadenaoriginal_3_3.xslt');

                    $avisos .= "sha256 \n";
                    $algo = OPENSSL_ALGO_SHA256;
                    break;
                case '4.0':
                    $xsl->load('' . '../../lib/scp/SAT/xslt/cadenaoriginal_4_0.xslt');
                    $avisos .= "sha256 \n";
                    $algo = OPENSSL_ALGO_SHA256;
                    break;
                default:
                    //$error = true;
                    $avisos .= 'version incorrecta ' . $data['tipo'] . ' ' . $data['version'] . "\n";
                    break;
            }
        }

        $proc = new XSLTProcessor();
        $proc->importStyleSheet($xsl);
        $cadena = $proc->transformToXML($xml);

        switch ($algo) {
            case OPENSSL_ALGO_SHA1:
                $sha1 = sha1($cadena);
                $avisos .= "hash sha1=$sha1 ";
                break;
            case OPENSSL_ALGO_SHA256:
                $sha256 = hash('sha256', $cadena);
                $avisos .= "hash sha256=$sha256 ";
                break;
            default:
                $md5 = md5($cadena);
                $avisos .= "hash md5=$md5 ";
                break;
        }

        if (!mb_check_encoding($cadena, 'utf-8')) {
            //$error = true;
            $avisos .= 'Error no esta en UTF-8! ';
        }

        /*
         * El domicilio es opcional, pero si no lo ponemos el xslt del SAT genera
         * doble pip en el pais ..., dice que el sello es correcto pero los PACs
         * que validan bien lo rechazan ...
         * */
        $doble = preg_match('/.\|\|./', $cadena);
        if ($doble === 1) {
            $avisos .= 'La cadena tiene doble pipes en medio... ';
        }

        // Primer certificado (o unico) del emisor
        // Los demas certificados es del PAC, Timbre, etc.
        // Marcos:         06/nov/2018
        // ora resulta que no funciona... :(
        if (is_array($data['cert'])) {
            $pem = (sizeof($data['cert']) <= 1) ? $data['cert'] : $data['cert'][0];
        } else {
            $pem = (isset($data['cert'])) ? $data['cert'] : '';
        }

        $pem = preg_replace("/[\n|\r|\n\r]/", '', $pem);
        $pem = preg_replace('/\s\s+/', '', $pem);
        // Si no incluye el certificado bajarlo del FTP del sat ....
        if (strlen($pem) == 0) {
            $avisos .= 'No incluye certificado interno, descargarlo del FTP del sat... ';
            $pem = $this->get_sat_cert($data['no_cert']);
        }

        $cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($pem, 64) . "-----END CERTIFICATE-----\n";
        $pubkeyid = openssl_get_publickey(openssl_x509_read($cert));
        if (!$pubkeyid) {
            $avisos .= 'Certificado interno Incorrecto, descargarlo del FTP del sat... ';
            $pem = $this->get_sat_cert($data['no_cert']);
            $cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($pem, 64) . "-----END CERTIFICATE-----\n";
            $pubkeyid = openssl_get_publickey(openssl_x509_read($cert));
        }

        $ok = openssl_verify(
            $cadena,
            base64_decode($data['sell']),
            $pubkeyid,
            $algo
        );
        if ($ok == 1) {
            $avisos .= 'Sello OK. ';
        } else {
            $error = true;
            $avisos .= 'Sello incorrecto. ';
            while ($msg = openssl_error_string()) {
                $avisos .= $msg . "\n";
            }
        }
        //openssl_free_key($pubkeyid);
        unset($pubkeyid);
        $paso = openssl_x509_parse($cert);
        $serial = $this->convierte($paso['serialNumber']);
        if ($serial != $data['no_cert']) {
            $avisos .= 'Serie reportada ' . $data['no_cert'] . " serie usada $serial ";
        }
        return $avisos;
    }

    // Convierte EL numero de serie del SAT a formato humano
    private function convierte($dec)
    {
        $hex = $this->bcdechex($dec);
        $ser = '';
        for ($i = 1; $i < strlen($hex); $i = $i + 2) {
            $ser .= substr($hex, $i, 1);
        }
        return $ser;
    }

    // Como dechex pero para numeros de precision ilimitada
    private function bcdechex($dec)
    {

        $dec = substr($dec, 2, -5);

        $last = bcmod($dec, '16');

        $remain = bcdiv(bcsub($dec, $last), '16');
        if ($remain == 0) {
            return dechex(intval($last));
        } else {
            return $this->bcdechex($remain) . dechex(intVal($last));
        }
    }

    // Valida CA (Autoridad Certificadora) certificado raiz sea del sat
    private function valida_pubkey($pubkeyid)
    {
        $ret = '';
        $ca = [realpath(__DIR__ . '' . '../../lib/scp/SAT/raiz/')];
        $ok = openssl_x509_checkpurpose($pubkeyid, X509_PURPOSE_ANY, $ca);
        if ($ok) {
            $ret .= 'Certificado emitido por el SAT';
        } else {
            $ret .= 'Certificado apocrifo, No es del SAT';
        }
        return $ret;
    }

    //ALFONSO 2017-09-11 Se pasa $texto como parametro ya que aparentemente no esta funcionando bien con globales.
    private function display_xml_errors($texto)
    {
        $lineas = explode("\n", $texto);
        $errors = libxml_get_errors();

        echo 'Validación del XML  />';
        echo '<pre>';
        foreach ($errors as $error) {
            echo $this->display_xml_error($error, $lineas);
        }
        echo '</pre>';
        libxml_clear_errors();
    }

    private function display_xml_error($error, $lineas)
    {
        $return = str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trim($error->message) .
            "\n  Linea: $error->line" .
            "\n  Columna: $error->column";
        echo "$return\n\n--------------------------------------------\n\n";
    }

    /*------------Funciones para mostrar los errores al checar la forma y sintaxis--------------*/

    private function libxml_display_errors()
    {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            return $this->libxml_display_error($error);
        }
        libxml_clear_errors();
    }

    private function libxml_display_error($error)
    {
        $return = '';
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in $error->file";
        }
        $return .= " on line $error->line\n";
        return $return;
    }

    /*--------- Validamos el sello CONTENIDO dentro de la addenda del PAC (timbreFiscalDigital) -----------*/
    //ALFONSO 2017-09-11 Se pasa $texto y $data como parametro ya que aparentemente no esta funcionando bien con globales.
    private function valida_sello_tfd($texto, $data)
    {
        if ($data['sell'] != $data['sellocfd']) {
            return 'sello Comprobante diferente que sello TFD!, manipulado? ';
        }

        // Quita la parte del CFDI
        $texto_tfd = preg_replace('{<cfdi:Comprobante.*<tfd:}is', '<tfd:', $texto);
        $texto_tfd = preg_replace('{<retenciones:Retenciones.*<tfd:}is', '<tfd:', $texto_tfd);
        $texto_tfd = trim(preg_replace('{/>.*$}is', '/>', $texto_tfd));
        // Si no tiene el namespace definido, se agrega
        if (strpos($texto_tfd, 'xmlns:tfd') === false) {
            $texto_tfd = substr($texto_tfd, 0, -2) . ' xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" />';
        }

        // Solo se quedo el tfd:
        $xml_tfd = new DOMDocument();
        $ok = $xml_tfd->loadXML($texto_tfd);

        $xsl = new DOMDocument();
        if ($data['version_tfd'] == '1.0') {
            $xsl->load('' . '../../lib/scp/SAT/xslt/cadenaoriginal_TFD_1_0.xslt');
            $alg = OPENSSL_ALGO_SHA1;
        } else {
            $xsl->load('../../lib/scp/SAT/xslt/cadenaoriginal_TFD_1_1.xslt');
            $alg = OPENSSL_ALGO_SHA256;
        }
        $proc = new XSLTProcessor();
        $proc->importStyleSheet($xsl);

        $cadena = $proc->transformToXML($xml_tfd);
        return "Cadena Original TFD: $cadena";

        if (!mb_check_encoding($cadena, 'utf-8')) {
            return 'Error no esta en UTF-8! ';
        }

        // Certificado del PAC
        $pem = $this->get_sat_cert($data);

        $cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($pem, 64) . "-----END CERTIFICATE-----\n";
        $this->valida_pubkey(openssl_x509_read($cert));
        $pubkeyid = openssl_get_publickey(openssl_x509_read($cert));
        $ok = openssl_verify(
            $cadena,
            base64_decode($data['sellosat']),
            $pubkeyid,
            $alg
        );

        if ($ok == 1) {
            $avisos = 'Sello TFD OK. ';
            return $avisos;
        } else {
            $avisos = 'Sello TFD incorrecto. ';
            while ($msg = openssl_error_string()) {
                $avisos .= $msg . '. ';
            }
        }
        openssl_free_key($pubkeyid);
        return $avisos;
    }

    /* ---------------------- Obtenemos el certificado del PAC del SAT -------------------------- */

    private function get_sat_cert($no_cert)
    {
        /*ALFONSO 2017-09-19 Se elimina el guardado de certificados, siempre se consultara en el sat*/
        // No esta en la tabla descarga del SAT
        $pem = '';
        $der = '';
        $p1 = substr($no_cert, 0, 6);
        $p2 = substr($no_cert, 6, 6);
        $p3 = substr($no_cert, 12, 2);
        $p4 = substr($no_cert, 14, 2);
        $p5 = substr($no_cert, 16, 2);
        $path2 = "ftp://ftp2.sat.gob.mx/certificados/FEA/$p1/$p2/$p3/$p4/$p5/$no_cert.cer";
        // Nuevo servidor mas rapido (menos conocido) (Gracias Rene)
        $path1 = "https://rdc.sat.gob.mx/rccf/$p1/$p2/$p3/$p4/$p5/$no_cert.cer";
        // Realiza 5 intentos para descargar el certificado
        // Gracias Rene Calderon
        // Se ignora el ceftificado del servidor rdc del sat
        $arrContextOptions = [
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ];
        $context = stream_context_create($arrContextOptions);
        $done = false;
        $x = 0;
        while (!$done) {
            // Alterna servidor en cada intento ....
            $path = (($x % 2) == 0) ? $path1 : $path2;
            $der = file_get_contents("$path", false, $context);
            if ($der) {
                $done = true;
            } else {
                usleep(100000);
            }
            if ($x == 5) {
                $done = true;
            }
            $x++;
        }
        $pem = base64_encode($der);
        return $pem;
    }

    /* --------------- Valida el XML en el servidor del SAT -----------------*/
    private function valida_en_sat($data)
    {
        // NuSOAP legacy: se silencian deprecations/warnings (strftime, utf8_decode,
        // constructores estilo PHP4) para que no corrompan la respuesta SOAP del SAT en PHP 8.3.
        require_once dirname(__FILE__) . '/lib/nusoap.php';
        error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));

        //ALFONSO 2017-11-14: Para los rfcs con & es necesario pasarlo por htmlentities
        $rfc_emisor = htmlentities($data['rfc']); //$rfcEmisor; ALFONSO - 2017-09-08 Variable no inicializada
        $rfc_receptor = $data['rfc_receptor']; //$rfcReceptor; ALFONSO - 2017-09-08 Variable no inicializada
        $impo = (float)$data['total']; //$importeNeto; ALFONSO - 2017-09-08 Variable no inicializada
        $impo = sprintf('%.6f', $impo);
        $impo = str_pad($impo, 17, '0', STR_PAD_LEFT);

        $uuid = strtoupper($data['uuid']); //$uuid); ALFONSO - 2017-09-08 Variable no inicializada

        $factura = "?re=$rfc_emisor&rr=$rfc_receptor&tt=$impo&id=$uuid";
        // En algunos blogs dicen que lo envía así.   Cuando ha tronado he intentado con ésto pero siempre
        // han sido problemas del servidor del SAT.... Lo pongo como nota solamente

        // Marcos: 07/nov/2018
        // El SAT modifica el método de invocación... :(

        $msg = <<<EOD
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
        <soapenv:Header/>
            <soapenv:Body>
            <tem:Consulta>
                <!--Optional:-->
                <tem:expresionImpresa><![CDATA[%%PRM%%]]></tem:expresionImpresa>
            </tem:Consulta>
        </soapenv:Body>
        </soapenv:Envelope>
        EOD;
        $msg = str_replace('%%PRM%%', $factura, $msg);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Content-type: text/xml;charset="utf-8"',
                    'Accept: text/xml',
                    'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
                    'cache-control: no-cache',
                    'Host: consultaqr.facturaelectronica.sat.gob.mx',
                ]
            );
            $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $ret = substr($response, $header_size);
            $codigostatus = '';
            $escancelable = '';
            $estado = '';
            $estatuscancelacion = '';
            if (preg_match("/<a:CodigoEstatus>(.*)<\/a:CodigoEstatus>/", $ret, $match)) {
                $codigostatus = $match[0];
            }
            if (preg_match("/<a:EsCancelable>(.*)<\/a:EsCancelable>/", $ret, $match)) {
                $escancelable = $match[0];
            }
            if (preg_match("/<a:Estado>(.*)<\/a:Estado>/", $ret, $match)) {
                $estado = $match[0];
            }
            if (preg_match('/<a:EstatusCancelacion>(.*)<a:EstatusCancelacion>/', $ret, $match)) {
                $estatuscancelacion = $match[0];
            }

            $codigostatus = str_replace('<a:CodigoEstatus>', '', $codigostatus);
            $codigostatus = str_replace('</a:CodigoEstatus>', '', $codigostatus);
            $estado = str_replace('<a:Estado>', '', $estado);
            $estado = str_replace('</a:Estado>', '', $estado);
            $escancelable = str_replace('<a:EsCancelable>', '', $escancelable);
            $escancelable = str_replace('</a:EsCancelable>', '', $escancelable);
            $estatuscancelacion = str_replace('<a:EstatusCancelacion>', '', $estatuscancelacion);
            $estatuscancelacion = str_replace('</a:EstatusCancelacion>', '', $estatuscancelacion);

            return 'Código : ' . $codigostatus . '.   ' .
                'Estado : ' . $estado . '.   ' .
                'Cancelable : ' . $escancelable . '.   ' .
                'Status cancelación : ' . $estatuscancelacion;
        } catch (Exception $e) {
            return 'No se pudo accesar el portal del SAT';
        }
    }

    private function ExisteProveedorDeFacturaSCP($rfc)
    {
        // Buscamos que exista el rfc, y que sea Persona Física o Moral (tcy_id = 1 o 2)
        $this->conectar();
        $this->setSql('
            SELECT COUNT(*) AS veces
            FROM cat_Proveedor
            INNER JOIN sinpres.cat_Proveedores USING(rfc_id)
            WHERE rfc_id = ? AND tcy_id BETWEEN 1 AND 2;
        ');
        $this->consultar([$rfc]);
        $this->desconectar();
        $dato = $this->extraer();
        // Si no lo encontramos regresamos false ... si existe regresamos true
        return ($dato['veces'] == 0 ? false : true);
    }

    private function ExisteProveedorDeFacturaSAT($rfc)
    {
        // Buscamos que exista el rfc en la tabla que contiene la Lista de Contribuyentes con Obligación del SAT
        $this->conectar();
        $this->setSql('
            SELECT COUNT(*) AS veces
            FROM cat_Proveedor WHERE rfc_id = ?;
        ');
        $this->consultar([$rfc]);
        $this->desconectar();
        $dato = $this->extraer();
        // Si no lo encontramos regresamos false ... si existe regresamos true
        return ($dato['veces'] == 0 ? false : true);
    }

    private function DameNombreProveedor()
    {
        $rfc = $this->prm->r;
        // Obtenemos el nombre del proveedo dado un rfc
        $this->conectar();
        $this->setSql(
            "
            SELECT
                CONCAT(rfc_nombre, ' ', rfc_paterno, ' ', rfc_materno) AS nombre
            FROM cat_Proveedor
            INNER JOIN sinpres.cat_Proveedores USING(rfc_id) WHERE rfc_id = ?;"
        );
        $this->consultar([$rfc]);
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            $dato = $this->extraer();
            $this->mixScript('
                $("#nombreEmisor").val("' . trim(mysql_escape_mimic($dato['nombre'])) . '");
            ');
        } else {
            $this->mixScript('
                $("#nombreEmisor").val("EL RFC NO EXISTE EN LA BASE DE DATOS");
            ');
        }
    }

    // Modificamos la información de un documento
    private function modificarDocumento()
    {
        $referencia = mysql_escape_mimic($this->prm->r);
        $bdo_id = $this->prm->id;

        // Si viene vacío, no hay nada que hacer
        if (trim($referencia) == '') {
            $this->mixScript('
                notify("No se ha especificado el documento a editar", "warning");
            ');
            return false;
        }

        // Buscamos el documento
        $this->conectar();
        $this->setSql(
            "
            SELECT
                CONCAT(rfc_nombre, ' ', rfc_paterno, ' ', rfc_materno, ' ', s.soc_nombre) AS nombre,
                bdo_fechaDocumento AS miFecha,
                d.bdo_origen,d.tdf_id,d.bdo_referencia,d.rfc_id,d.bdo_concepto,
                d.bdo_total,d.bdo_fondoRotatorio,d.bdo_percepcionPresupuestal, d.bdo_percepcionContable,
                d.bdo_retencionContable, d.bdo_pdp
            FROM cat_BancoDocumentos d
            INNER JOIN cat_Proveedor p USING(rfc_id)
            INNER JOIN sinpres.cat_Proveedores sp using(rfc_id)
            INNER JOIN sinpres.cat_SociedadProveedor s USING(soc_id)
            WHERE d.bdo_id = ?
                AND d.tdf_id <> 'SDI';"
        );

        // Marcos: 13/sep/2016
        // NO se permitirá modificar o adicionar tipos de documento SDI (servicio de inspección 2.5)
        // ya que éstos sólo los generará el sistema

        //se cambio de referencia a Id, para evitar pasar más parámetros(rfc,referencia)
        $this->consultar([$bdo_id]);
        $this->desconectar();
        $veces = $this->getNumeroRegistros();

        if ($veces == 0) {
            $this->mixScript('
                notify("No se ha encontrado el documento solicitado", "warning");
            ');
            return false;
        }
        $documento = $this->extraer();

        // Buscamos si el documento ya fué usado en el devengado (scp_Presupuestal)
        $this->conectar();
        $parametros = [$this->clasAdmGpo, $this->clasAdmUr, $bdo_id];
        $this->setSql(
            "
            SELECT
                COUNT(*) AS total, IFNULL(SUM(ncp_percepcionNeto), 0) AS importeUsado
            FROM cat_BancoDocumentos d
            INNER JOIN scp_Presupuestal p USING(bdo_id)
            INNER JOIN scp_Afectacion a on p.gpo_id = a.gpo_id	 and p.ur_id = a.ur_id and p.afe_id = a.afe_id
            WHERE d.gpo_id = ? and d.ur_id = ?
                AND d.bdo_id = ? AND eaf_id !='C';"
        );
        $this->consultar($parametros);
        $this->desconectar();
        $row = $this->extraer();
        $importeUsado = $row['importeUsado'];

        // Esta consulta es solo para ver si ya fue usado en presupuestal y contable
        $this->conectar();
        $this->setSql(
            '
            SELECT
                COUNT(*) AS total
                FROM cat_BancoDocumentos d
            LEFT JOIN scp_Presupuestal p USING(gpo_id, ur_id, bdo_id)
            LEFT JOIN scp_Contable c USING(gpo_id, ur_id, bdo_id)
            WHERE d.gpo_id = ?
                AND d.ur_id = ?
                AND d.bdo_id = ?
                AND (p.bdo_id is not null or c.bdo_id is not null) ; '
        );
        $this->consultar($parametros);
        $row = $this->extraer();
        $vecesUsado = $row['total'];
        $this->desconectar();

        // Esta consulta es solo para ver si ya fue usado en presupuestal y contable
        // PARA FONDO ROTATORIO, ya que aunque haya sido utilizada, si las afectaciones están canceladas
        // le permitiremos modificar el checkbox de fondo rotatorio
        $this->conectar();
        $this->setSql(
            "
            SELECT
                COUNT(*) AS total
            FROM cat_BancoDocumentos d
            LEFT JOIN scp_Presupuestal p USING(gpo_id, ur_id, bdo_id)
            LEFT JOIN scp_Afectacion a USING(gpo_id, ur_id, clc_id, afe_id)
            WHERE d.gpo_id = ?
                AND d.ur_id = ?
                AND d.bdo_id = ?
                AND (p.bdo_id is not null)
                AND eaf_id <> 'C'; "
        );
        $this->consultar($parametros);
        $row = $this->extraer();
        $vecesUsadoFR = $row['total'];
        $this->desconectar();

        // Permitiremos modificar la fecha del documento, si NO es de XML y solo tiene afectaciones canceladas
        // Así que buscamos cuántas afectaciones tiene CANCELADAS y cuántas ACTIVAS (distintas de canceladas)
        // Para que si NO tiene nada en las que no son canceladas, le permitamos modificar la fecha
        $this->conectar();
        $this->setSql(
            "
            SELECT
                SUM(CASE eaf_id WHEN 'C' THEN 1 ELSE 0 END) AS canceladas,
                SUM(CASE WHEN eaf_id != 'C' THEN 1 ELSE 0 END) AS otras
            FROM cat_BancoDocumentos d
            INNER JOIN scp_Presupuestal p USING(bdo_id)
            INNER JOIN scp_Afectacion a ON p.gpo_id = a.gpo_id AND p.ur_id = a.ur_id AND p.afe_id = a.afe_id
            WHERE d.gpo_id = ?
                AND d.ur_id = ?
                AND d.bdo_id = ?
                AND eaf_id !='C';"
        );
        //se cambio de referencia a Id, para evitar pasar más parámetros(rfc,referencia)
        $this->consultar($parametros);
        $row = $this->extraer();
        $this->desconectar();

        //$nCanceladas = $row['canceladas'];
        $nOtras = $row['otras'];
        $bolPermitirModificarFecha = ($nOtras == 0);

        // Si es desde XML, NO permitimos modificarlo
        if ($documento['bdo_origen'] == '2') {
            $this->mixScript('
                notify("Documento cargado desde XML, NO se permite su modificación", "warning");
            ');
            return false;
        }

        $bdo_estadopdo = $documento['bdo_pdp'];
        $this->mixScript('
            // llenamos los campos
            $("#cmbTipoDocto").val("' . $documento['tdf_id'] . '");
            $("#cmbTipoDocto").attr("ori", "' . $documento['tdf_id'] . '");
            $("#referencia").val("' . $documento['bdo_referencia'] . '");
            $("#referencia").attr("ori", "' . $documento['bdo_referencia'] . '");
            $("#fecha").val("' . $documento['miFecha'] . '");
            $("#rfcEmisor").val("' . $documento['rfc_id'] . '");
            $("#rfcEmisor").attr("ori", "' . $documento['rfc_id'] . '");
            $("#nombreEmisor").val("' . $documento['nombre'] . '");
            $("#concepto").val("' . mysql_escape_mimic($documento['bdo_concepto']) . '");
            $("#importe").val("' . $documento['bdo_total'] . '");

            // Guardamos el importe original ...
            $("#importe").attr("ori", "' . $documento['bdo_total'] . '");

            $("#chkFondoRotatorio").prop("checked", ' . ($documento['bdo_fondoRotatorio'] == 'S' ? 'true' : 'false') . ');

            $("#percepPresup").val("' . $documento['bdo_percepcionPresupuestal'] . '");
            $("#percepContab").val("' . $documento['bdo_percepcionContable'] . '");
            $("#retenContab").val("' . $documento['bdo_retencionContable'] . '");

            // Por la adición de más campos, originalmente era bdo_importe pero despues se cambio a bdo_total
            $("#total").val("' . $documento['bdo_total'] . '");

            $("#variosDocumentos").hide();
            $(".modal-title").text("Modificar Documento");
            $(".modal-header").css("max-height","60px");
            $(".modal-title").attr("t", "c");
            $("#myModalDocto").show();
            setTimeout(function() { $("#cmbTipoDocto").focus() }, 100);
        ');
        // si el documento se encuentra en una CLC de modalidad de pago PDP no se puede modificar
        if ($bdo_estadopdo == 'S') {
            $this->mixScript('
                $("#cmbTipoDocto").prop("disabled", true);  // Ningún dato de la llave primaria puede ser modificado
                $("#referencia").prop("disabled", true);
                $("#rfcEmisor").prop("disabled", true);
                $("#fecha").prop("disabled", true);

                $("#concepto").prop("disabled", true);
                $("#percepPresup").prop("disabled", true);
                $("#percepContab").prop("disabled", true);
                $("#retenContab").prop("disabled", true);
                $("#grabarDocumento").hide();

                $("#total").prop("disabled", true);

                $("#rfcEmisor").attr("fr", "1");
                $("#chkFondoRotatorio").prop("disabled", true);
                $("#chkFondoRotatorio").hide();
                $("#paraFondoRotatorio").hide();
                notify("El documento se encuentra vinculado a un CLC con Modaliad de Pago PDP y no puede ser modificado", "warning");
            ');
        }
        // Si ya ha sido usado el documento, no permitimos editar el numero de documento, la referencia y la fecha del documento
        elseif ($vecesUsado || $vecesUsadoFR > 0) {
            $this->mixScript('
                $("#cmbTipoDocto").prop("disabled", true);  // Ningún dato de la llave primaria puede ser modificado
                $("#referencia").prop("disabled", true);
                $("#rfcEmisor").prop("disabled", true);
                $("#fecha").prop("disabled", true);

                $("#total").prop("disabled", true);

                $("#rfcEmisor").attr("fr", "1");
                $("#chkFondoRotatorio").prop("disabled", true);
                $("#chkFondoRotatorio").hide();
                $("#paraFondoRotatorio").hide();
                //liberar si no es pdp
                $("#concepto").prop("disabled", false);
                $("#percepPresup").prop("disabled", false);
                $("#percepContab").prop("disabled", false);
                $("#retenContab").prop("disabled", false);
                $("#grabarDocumento").show();
                notify("El importe del documento NO puede ser menor a " + formatNumber(' . $importeUsado . ',"",""), "warning", false);
            ');
        } else {
            $this->mixScript('
                $("#cmbTipoDocto").prop("disabled", true);  // Ningún dato de la llave primaria puede ser modificado
                $("#referencia").prop("disabled", false);
                $("#rfcEmisor").prop("disabled", false);
                $("#fecha").prop("disabled", false);

                $("#total").prop("disabled", true);

                $("#rfcEmisor").attr("fr", "0");
                $("#chkFondoRotatorio").prop("disabled", false);
                $("#chkFondoRotatorio").show();
                $("#paraFondoRotatorio").show();
                //liberar  si no es PDP
                $("#concepto").prop("disabled", false);
                $("#percepPresup").prop("disabled", false);
                $("#percepContab").prop("disabled", false);
                $("#retenContab").prop("disabled", false);
                $("#grabarDocumento").show();
            ');
        }

        // No checamos que sea un XML (CFDI) porque eso se revisó arriba y no llegaríamos hasta aquí si es un XML
        // Si se permite modificar la fecha ...
        if ($bolPermitirModificarFecha && $bdo_estadopdo == 1) {
            $this->mixScript('
                $("#fecha").prop("disabled", false);
            ');
        }
    }

    // Sólo los usuarios de nivel UR podrán habilitar / deshabilitar  un documento
    // de la base de datos.
    // Cambio 17-feb-2015: NO se borrarán, simplemente cambiarán su status (sts_id) a 2: Deshabilitado
    // Sólo podrán deshabilitarse los documentos:
    // 1. Que no tengan ningún movimiento (y que no quieran borrarlos)
    // 2. Que todos los movimientos que tengan en scp_Presupuestal y scp_Contable estén CANCELADOS
    private function habDeshabDocumento()
    {
        // Sólo los usuarios de nivel UR podrán hacerlo
        if ($this->usarioNvj != '2') {
            $this->mixScript('
                notify("Sólo los usuarios de nivel UR pueden habilitar / deshabilitar documentos", "error");
            ');
            return false;
        }

        $referencia = mysql_escape_mimic($this->prm->r);
        $bdo_id = $this->prm->id;
        $sts_id = (isset($this->prm->s) ? $this->prm->s : 1);

        // Si viene vacío, no hay nada que hacer
        if (trim($referencia) == '') {
            $this->mixScript('
                notify("No se ha especificado el documento a habilitar / deshabilitar", "warning");
            ');
            return false;
        }

        // Si quieren DESHABILITAR, checamos que no se haya usado
        if ($sts_id == '1') {
            $nuevoStatus = '2';
            $strAccion = 'DESHABILITADO';

            // Esta consulta es solo para ver si ya fue usado en presupuestal y contable
            $this->conectar();
            $parametros = [$this->clasAdmGpo, $this->clasAdmUr, $bdo_id];
            $this->setSql(
                '
                SELECT
                    COUNT(*) AS total
                FROM cat_BancoDocumentos d
                LEFT JOIN scp_Presupuestal p USING(bdo_id)
                LEFT JOIN scp_Contable c USING(bdo_id)
                WHERE d.gpo_id = ?
                AND d.ur_id = ?
                AND d.bdo_id = ? and (p.bdo_id is not null or c.bdo_id is not null) ; '
            );
            $this->consultar($parametros);
            $row = $this->extraer();
            $vecesUsado = $row['total'];
            $this->desconectar();

            // Si ya ha sido usado al menos una vez, NO permitimos borrarlo
            if ($vecesUsado > 0) {
                // Si hay ha sido usado, checamos que TODAS sus afectaciones estén canceladas
                $this->conectar();
                $this->setSql("
                    SELECT
                        SUM(CASE WHEN eaf_id != 'C' THEN 1 ELSE 0 END) AS otras
                    FROM cat_BancoDocumentos d
                        JOIN scp_Presupuestal p USING(bdo_id)
                        JOIN scp_Afectacion a on p.gpo_id = a.gpo_id	 and p.ur_id = a.ur_id and p.afe_id = a.afe_id
                    WHERE d.gpo_id = ? and d.ur_id = ?
                        AND d.bdo_id = ?
                        AND eaf_id != 'C' ;
                ");

                $this->consultar($parametros);
                $row = $this->extraer();
                $this->desconectar();

                $nTieneActivos = $row['otras'];
                // Permitimos Deshabilitar el documento sólamente cuando TODAS sus afectaciones estén CANCELADAS
                if ($nTieneActivos > 0) {
                    $this->mixScript('
                        notify("El documento ya ha sido utilizado, no puede deshabilitarse", "error");
                    ');
                    return false;
                }
            }

            // Si quieren HABILITAR
        } else {
            $nuevoStatus = '1';
            $strAccion = 'HABILITADO';
        }

        // Habilitamos / Deshabilitamos
        $this->escribeBitacora('OPCION HABILITAR/DESHABILITAR DOCUMENTO');

        $parametros = [$nuevoStatus, $this->clasAdmGpo, $this->clasAdmUr, $bdo_id];
        $this->setSql("
            UPDATE cat_BancoDocumentos
            SET sts_id = ?
            WHERE gpo_id = ?
                AND ur_id = ?
                AND bdo_id = ?
                AND tdf_id <> 'SDI'
                ;");
        // Marcos: 13/sep/2016
        // aunque ya validamos en javascript, ponemos otro filtro aquí para que no nos metan gol
        // no permitiremos modificar el status a un documento automático (SDI)
        $this->consultar($parametros);
        $registrosAfectados = $this->getNumeroModificados();
        $this->desconectar();

        if ($registrosAfectados > 0) {
            $this->mixScript('
                notify("El documento ha sido ' . $strAccion . ' exitosamente", "success");
                ' . $this->wrLlamaLlenaTabla('tabla_documentos') . '
            ');
        } else {
            $this->mixScript('
                notify("Un problema impidió cambiar el estado del documento a ' . $strAccion . '", "warning");
            ');
        }
    }

    // Sólo los usuarios de nivel UR podrán eliminar un documento de la base de datos.
    // ***** NO TIENE QUE ESTAR USADO EN NINGUN REGISTRO APARTE DE CAT_BANCODOCUMENTOS PARA PODER BORRARLO *****
    private function eliminarDocumento()
    {
        // Sólo los usuarios de nivel UR podrán hacerlo
        if ($this->usarioNvj != '2') {
            $this->mixScript('
                notify("Sólo los usuarios de nivel UR pueden borrar documentos", "error");
            ');
            return false;
        }

        $referencia = mysql_escape_mimic($this->prm->r);
        $rfc = mysql_escape_mimic($this->prm->e);
        $bdo_id = $this->prm->id;

        // Si viene vacío, no hay nada que hacer
        if (trim($referencia) == '') {
            $this->mixScript('
                notify("No se ha especificado el documento a borrar", "warning");
            ');
            return false;
        }

        // Esta consulta es solo para ver si ya fue usado en presupuestal y contable
        $this->conectar();
        $parametros = [
            $this->clasAdmGpo,
            $this->clasAdmUr,
            mysql_escape_mimic($referencia),
            mysql_escape_mimic($rfc),
            $bdo_id,
        ];
        $this->setSql(
            '
            SELECT
                COUNT(*) AS total
            FROM cat_BancoDocumentos d
            LEFT JOIN scp_Presupuestal p USING(bdo_id)
            LEFT JOIN scp_Contable c USING(bdo_id)
            WHERE d.gpo_id = ?
                AND d.ur_id = ?
                AND d.bdo_referencia = ?
                AND d.rfc_id = ?
                AND d.bdo_id = ?
                AND (p.bdo_id is not null or c.bdo_id is not null) ; '
        );

        $this->consultar($parametros);
        $row = $this->extraer();
        $vecesUsado = $row['total'];
        $this->desconectar();

        // Si ya ha sido usado al menos una vez, NO permitimos borrarlo
        if ($vecesUsado > 0) {
            $this->mixScript('
                notify("El documento ya ha sido utilizado, no puede eliminarse", "error");
            ');
            return false;
        }

        // De todos modos la base de datos marcará error si en alguna tabla se hace referencia a él
        $this->escribeBitacora('ELIMINADO POR EL USUARIO');
        $this->setSql(
            "
            DELETE FROM cat_BancoDocumentos
            WHERE gpo_id = ?
                AND ur_id = ?
                AND bdo_referencia = ? AND rfc_id = ?
                AND bdo_id = ?
                AND tdf_id <> 'SDI';"
        );

        // Marcos: 13/sep/2016
        // NO permitiremos eliminar documentos automáticos (SDI), aunque ya validamos en javascript
        // validamos aquí tambien por si nos quieren meter gol
        $this->consultar($parametros);
        $registrosBorrados = $this->getNumeroModificados();
        $this->desconectar();

        if ($registrosBorrados > 0) {
            $this->mixScript('
                $("#tabla_documentos").tabla("llenaTabla");
                notify("El documento ha sido eliminado exitosamente", "success");
            ');
        } else {
            $this->mixScript('
                notify("Un problema impidió eliminar el documento", "warning");
            ');
        }
    }

    // Sólo los usuarios de nivel UR podrán cerrar un documento de la base de datos.
    // La idea es que un documento pueda ser TOTALMENTE DEVENGADO aún si no se a afectado la totalidad de sus
    // importes, para disparar las pólizas contables, igualar el importe a la suma total de afectaciones
    // presupuestales autorizadas, y marcarlo como totalmente devengado

    private function cerrarDocumento()
    {
        // Sólo los usuarios de nivel UR podrán hacerlo
        if ($this->usarioNvj != '2') {
            // Marcos: 09/mayo/2017
            // Usuario MARCOS.VALENCIA, para los tickets de la 118 donde nos piden cerrar el documento
            if ($this->usarioId != 2135) {
                $this->mixScript('
                    notify("Sólo los usuarios de nivel UR pueden cerrar documentos", "error");
                ');
                return false;
            }
        }

        $referencia = mysql_escape_mimic($this->prm->ref);
        $bdo_id = $this->prm->id;

        // Si viene vacío, no hay nada que hacer
        if (trim($referencia) == '') {
            $this->mixScript('
                notify("No se ha especificado el documento a cerrar", "warning");
            ');
            return false;
        }

        // Marcos: 23/mayo/2016
        // Se adiciona el filtro que NO permitirá modificar un banco de documentos
        // Si ya se ha usado en rectificaciones
        // Checado con Gerardo: con que exista un registro en las tablas
        // scp_RectificacionPresupuesto o scp_RectificacionContable ya no se permitirá
        // la edición, no se validará nada más

        // Marcos: 20/jun/2017
        // Activado hoy 20 de junio, porque se liberaron a producción las rectificaciones

        // Rectificación Presupuestal

        // @Eduardo, se comenta de momento 28/11/2018  hasta liberacion de rectificiones
        // @miguelAbad, se comenta de momento 04/10/2019  hasta liberacion de rectificiones

        // Obtenemos nuevamente el importe total afectado autorizado, y la fecha de la última autorización
        // que son los datos con los que se guardará la póliza del devengado

        $this->conectar();

        /* --NOTA: Por Eduardo, esta funcion esta comentada de por si, solo hice la observacion
       // Marcos: 10/julio
        // Con las rectificaciones, el cierre de un documento calculará importes considerando las rectificaciones
        // distribuirá los importes en bdo_PercepcionPresupuestal, bdo_PercepcionContable y bdo_RetencionContable
        // REGRESA:
        // tpp : Total Percepciones Presupuestales
        // rpp : Total Retenciones a Percepciones Presupuestales
        // tpc : Total Percepciones Contables
        // rpc : Total Retenciones a Percepciones Contables
        // trc = Total Retenciones Contables
        // rrc = Total Retenciones a Retenciones Contables
        // scp_MaestroRectificacion.rct_reintegro:
        // 1 : CON REINTEGRO DE EFECTIVO
        // 2 : SIN REINTEGRO
        // 3 : CON REINTEGRO VIRTUAL
        // Para el banco de documentos, tomaremos los que tengan el status 1 o 3 (también checacos se sea NULL, para que incluya aquellos importes que NO tengan reintegro)
        // Marcos: 26/mayo/2016
        // Quitamos toda referencia a rectificaciones, ya que para rectificar
        // es necesario que esté pagado, y para que se pague debe estar devengado al 100%
        // ergo, no se debe presentar éste escenario.
        // Checado con Israel y Gerardo
       */
        $parametros = [
            $this->clasAdmGpo,
            $this->clasAdmUr,
            $bdo_id,
            $this->clasAdmGpo,
            $this->clasAdmUr,
            $bdo_id,
        ];
        $this->setSql('
            SELECT bdo_id, SUM(presupuestal) AS presupuestal, SUM(percepcion) AS percepcion, SUM(retencion) AS retencion, tdf_id
            FROM ' . BD_CTRL . '.cat_BancoDocumentos
                INNER JOIN (
                    SELECT gpo_id, ur_id, bdo_id, COALESCE(SUM(ncp_percepcionNeto)) AS presupuestal, 0 AS percepcion, 0 AS retencion
                    FROM ' . BD_CTRL . '.scp_Afectacion a
                        LEFT JOIN ' . BD_CTRL . '.scp_Presupuestal USING(gpo_id, ur_id, afe_id)
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND bdo_id = ?
                        AND a.eaf_id = "A"
                    GROUP BY gpo_id, ur_id, bdo_id
                    UNION ALL
                    SELECT gpo_id, ur_id, bdo_id, 0 AS presupuestal, COALESCE(SUM(ncc_percepcion)) AS percepcion, COALESCE(SUM(ncc_retencion)) AS retencion
                    FROM ' . BD_CTRL . '.scp_Afectacion a
                        LEFT JOIN ' . BD_CTRL . '.scp_Contable USING(gpo_id, ur_id, afe_id)
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND bdo_id = ?
                        AND a.eaf_id = "A"
                    GROUP BY gpo_id, ur_id, bdo_id
                )n USING(bdo_id)
            GROUP BY bdo_id, tdf_id ;
        ');

        $this->consultar($parametros);
        $row = $this->extraer();
        // Se suman todos los registros  existentes con respecto a los documentos existentes

        $importe = floatval($row['presupuestal']);
        $percepPresup = floatval($row['presupuestal']);
        $percepContab = floatval($row['percepcion']);
        $retenContab = floatval($row['retencion']);

        $temTdfid = (isset($temTdfid) && $temTdfid != '') ? $temTdfid : $row['tdf_id'];
        $this->desconectar();

        // Marcos: 13/sep/2016
        // Aunque ya filtramos en el javascript, filtramos también aquí para que no nos metan gol
        // NO permitiremos cerrar documentos del tipo SDI (servicios de inspección 2.5)

        if (isset($temTdfid) && $temTdfid == 'SDI') {
            $this->mixScript('
                notify("No puedes cerrar documentos automáticos", "error");
            ');
            return false;
        }

        // Para determinar si estamos cerrando un documento sólo de afectaciones contables o no
        $bolSoloContables = false;

        // Si el total es cero, entonces ocurrió un error ...
        if (!is_numeric($importe) || $importe <= 0) {
            // Marcos: 17 / julio
            // Como permitiremos cerrar afectaciones contables, ahora puede ser cero, siempre y cuando las retenciones NO
            if (!is_numeric($percepContab) || !is_numeric($retenContab) || $percepContab < 0 || $retenContab < 0) {
                $this->mixScript('
                    notify("Error al determinar el total de afectaciones presupuestales autorizadas", "error");
                ');
                return false;
            } else {
                // Si llegamos aquí es que son sólo contables, y pueden cerrarse
                $bolSoloContables = true;
            }
        }

        // MARCOS: 10/julio
        // Con rectificaciones, también checamos que no sea menor que cero los otros importes, porque significaría que hay
        // una manera equivocada de registrarlos en el banco de documentos

        if (!is_numeric($percepContab) || $percepContab < 0) {
            $this->mixScript('
                notify("Error al determinar el total de Percepciones Contables, revisa tu documento", "error");
            ');
            return false;
        }
        if (!is_numeric($retenContab) || $retenContab < 0) {
            $this->mixScript('
                notify("Error al determinar el total de Retenciones Contables, revisa tu documento", "error");
            ');
            return false;
        }

        // @Jordy: 12-12-2018
        // Se acordo con Israel checar los documentos devengados , ejercidos, ministrados para poder proseguir con
        // el update de banco documentos. Si los los importes son iguales en el presupuesto, en el contable y en
        // la retencion pasan a 3, si los importes de los 3 aspectos son iguales a 0 se pondran en 1 y en caso de que
        // sean diferentes seran igual a 2.

        // variables para el devengado
        $temP1 = 0;
        $temC1 = 0;
        $temRc1 = 0;
        // varibales para lo ejercido
        $temP2 = 0;
        $temC2 = 0;
        $temRc2 = 0;
        // variables para lo ministrado
        $temP3 = 0;
        $temC3 = 0;
        $temRc3 = 0;
        // variables para pagado
        $temP4 = 0;
        $temC4 = 0;
        $temRc4 = 0;
        // variables para pagado contable
        $temP5 = 0;
        $temC5 = 0;
        $temRc5 = 0;

        $this->conectar();

        // Se empieza a verificar en banco documento devengado por si hubiese alguna falla, estos documento tienen
        // que pertenecer el mismo grupo, ur, id del documento, en estado de autorizado
        $parametros = [
            $this->clasAdmGpo,
            $this->clasAdmUr,
            $bdo_id,
            $temTdfid,
        ];
        $this->setSql('
            SELECT
            SUM(sd.bdd_percepcionPresupuestal) AS p1, SUM(sd.bdd_percepcionContable) AS c1 , SUM(sd.bdd_retencionContable) AS rc1
            FROM cat_BancoDocumentos cb
            INNER JOIN scp_BancoDocumentoDevengado sd USING (gpo_id, ur_id, bdo_id)
            WHERE cb.gpo_id = ?
                AND cb.ur_id = ?
                AND cb.bdo_id = ?
                AND sd.bdd_estado = "A"
                AND cb.tdf_id = ?
            GROUP BY gpo_id, ur_id, tdf_id;
        ');

        $this->consultar($parametros);
        // Se obtiene todos lo datos del documento y se guardan en las variables
        $datos = $this->extraer();

        $temP1 = (float)$datos['p1'];
        $temC1 = (float)$datos['c1'];
        $temRc1 = (float)$datos['rc1'];

        unset($datos);

        // Se empieza a verificar los datos de lo ejercido
        $this->setSql('
            SELECT
                SUM(se.bde_percepcionPresupuestal) AS p2, SUM(se.bde_percepcionContable) AS c2, SUM(se.bde_retencionContable) AS rc2
            FROM cat_BancoDocumentos cb
            INNER JOIN scp_BancoDocumentoEjercido se USING (gpo_id, ur_id, bdo_id)
            WHERE cb.gpo_id = ?
                AND cb.ur_id = ?
                AND cb.bdo_id = ?
                AND se.bde_estado = "A"
                AND cb.tdf_id = ?
            GROUP BY gpo_id, ur_id, tdf_id;
        ');

        $this->consultar($parametros);
        // Se obtiene todos lo datos del documento y se guardan en las variables
        $datos = $this->extraer();

        $temP2 = (float)$datos['p2'];
        $temC2 = (float)$datos['c2'];
        $temRc2 = (float)$datos['rc2'];

        unset($datos);

        // Se empieza a verificar los datos del ministrado
        $this->setSql('
            SELECT
                SUM(tmb.bdm_percepcionPresupuestal) AS p3, SUM(tmb.bdm_percepcionContable) AS c3, SUM(tmb.bdm_retencionContable) AS rc3
            FROM cat_BancoDocumentos cb
                INNER JOIN ' . BD_TESORERIA . '.min_BancoDocumentoMinistrado_' . APP_EJERCICIO . ' tmb USING (gpo_id, ur_id, bdo_id)
            WHERE cb.gpo_id = ?
                AND cb.ur_id = ?
                AND cb.bdo_id = ?
                AND tmb.bdm_estado = "A"
                AND cb.tdf_id = ?
            GROUP BY ur_id, bdo_id, cb.tdf_id;
        ');

        $this->consultar($parametros);
        $datos = $this->extraer();

        $temP3 = (float)$datos['p3'];
        $temC3 = (float)$datos['c3'];
        $temRc3 = (float)$datos['rc3'];

        unset($datos);

        // Se empieza a verificar los datos pagados
        $this->setSql('
            SELECT SUM(bdp.bdp_percepcionPresupuestal) AS p4, SUM(bdp.bdp_percepcionContable)  AS c4, SUM(bdp.bdp_retencionContable) AS rc4
            FROM cat_BancoDocumentos cb
                INNER JOIN cg_BancoDocumentoPagado bdp USING (gpo_id, ur_id, bdo_id)
            WHERE cb.gpo_id = ?
                AND cb.ur_id = ?
                AND cb.bdo_id = ?
                AND cb.tdf_id = ?
                AND bdp.bdp_estado = "A"
            GROUP BY gpo_id, ur_id, cb.tdf_id;
            ');
        $this->consultar($parametros);
        $datos = $this->extraer();

        $temP4 = (float)$datos['p4'];
        $temC4 = (float)$datos['c4'];
        $temRc4 = (float)$datos['rc4'];

        unset($datos);

        // Se empieza a verificar los datos pagados contables
        $this->setSql('
            SELECT
                SUM(bdpc.bdp_percepcionPresupuestal) AS p5, SUM(bdpc.bdp_percepcionContable)  AS c5, SUM(bdpc.bdp_retencionContable) AS rc5
            FROM cat_BancoDocumentos cb
                INNER JOIN cg_BancoDocumentoPagadoContable bdpc USING (gpo_id, ur_id, bdo_id)
            WHERE cb.gpo_id = ?
                AND cb.ur_id = ?
                AND cb.bdo_id = ?
                AND cb.tdf_id = ?
                AND bdpc.bdp_estado = "A"
            GROUP BY gpo_id, ur_id, cb.tdf_id;
            ');

        $this->consultar($parametros);

        $datos = $this->extraer();
        $reg = $this->getNumeroRegistros();
        $temP5 = $reg > 0 ? (float)$datos['p5'] : 0;
        $temC5 = $reg > 0 ? (float)$datos['c5'] : 0;
        $temRc5 = $reg > 0 ? (float)$datos['rc5'] : 0;

        unset($datos);
        $tipo = 0;
        $this->desconectar();
        // se verifica si son iguales las percepciones presupuestales, contables y las retenciones
        // en caso de ser iguales conforme al caso se pone en S caso contrario se pone N
        // para el devengado
        $temDevengado = (($temP1 == $percepPresup) && ($temC1 == $percepContab) && ($temRc1 == $retenContab)) ? 'S' : 'N';
        // para el ejercido
        $temEjercido = (($temP2 == $percepPresup) && ($temC2 == $percepContab) && ($temRc2 == $retenContab)) ? 'S' : 'N';
        // para el ministrado
        $temMinistrado = (($temP3 == $percepPresup) && ($temC3 == $percepContab) && ($temRc3 == $retenContab)) ? 'S' : 'N';

        // MOMENTO DEL DEVENGADO
        // en caso de ser todos iguales se pondra en stus 3 el update en caso contrario se pondra en estatus 2
        $temMomenDev = ($temDevengado == 'S') ? 3 : 2;
        switch ($temMomenDev) {
            // en caso que sean 2 se validara que si son iguales a ceros todos se pasaran a estado 1, si no
            // se deajaran en el estado 2
            case 2:
                if (($percepPresup == 0) && ($percepContab == 0) && ($retenContab == 0)) {
                    $temMomenDev = 1;
                }
                break;
        }

        // MOMENTO DEL EJERCIDO
        // en caso de ser todos iguales se pondra en stus 3 el update en caso contrario se pondra en estatus 2
        $temMomenEjer = ($temEjercido == 'S') ? 3 : 2;
        switch ($temMomenEjer) {
            // en caso que sean 2 se validara que si son iguales a ceros todos se pasaran a estado 1, si no
            // se deajaran en el estado 2
            case 2:
                if (($percepPresup == 0) && ($percepContab == 0) && ($retenContab == 0)) {
                    $temMomenEjer = 1;
                }
                break;
        }

        // MOMENTO DEL MINISTRADO
        // en caso de ser todos iguales se pondra en stus 3 el update en caso contrario se pondra en estatus 2
        $temMomenMinis = ($temMinistrado == 'S') ? 3 : 2;
        switch ($temMomenMinis) {
            // en caso que sean 2 se validara que si son iguales a ceros todos se pasaran a estado 1, si no
            // se deajaran en el estado 2
            case 2:
                if (($percepPresup == 0) && ($percepContab == 0) && ($retenContab == 0)) {
                    $temMomenMinis = 1;
                }
                break;
        }

        // se verifica si son iguales en los datos del pagado

        $valPag = ((($temP4 + $temP5) == floatval($percepPresup)) && (number_format(($temC4 + $temC5), 2) == number_format($percepContab, 2)) && (number_format(($temRc4 + $temRc5), 2) == number_format(($retenContab), 2))) ? 'S' : 'N';

        $valTemPag = ($valPag == 'S') ? 3 : 2;

        switch ($valTemPag) {
            // en caso que sean 2 se validara que si son iguales a ceros todos se pasaran a estado 1, si no
            // se deajaran en el estado 2
            case 2:
                if ((($temP4 + $temP5) == 0) && (($temC4 + $temC5) == 0) && (($temRc4 + $temRc5) == 0)) {
                    $valTemPag = 1;
                }
                break;
        }

        // En una sola llamada, actualizaremos el importe del banco de documentos para igualarlo a la suma de
        // afectaciones presupuestales autorizadas, guardar en la bitácora el movimiento, y llamar al procedimiento
        // almacenado para que dispare la póliza del devengado
        $this->conectar();

        // Ajustamos el campo BDO_TOTAL de la tabla
        if ((isset($temTdfid) && $temTdfid == 'PGT') || (isset($temTdfid) && $temTdfid == 'LC')) {
            $total = $retenContab;
        } else {
            $total = ($importe + $percepContab) - $retenContab;
        }

        // Marcos: 08/abr/2016
        // Se adiciona el cambio de estado a bdo_estadoEjercido y bdo_estadoMinistrado a 3
        // (esto se adicionó respecto a lo que se cambiaba en el 2015)
        // acordado con Israel
        $this->escribeBitacora('MODIFICACIÓN DE IMPORTE');

        // Actualizamos el importe en el banco de documentos
        $parametros = [
            $total,
            $importe,
            $percepContab,
            $retenContab,
            $temMomenDev,
            $temMomenEjer,
            $temMomenMinis,
            $valTemPag,
            $this->clasAdmGpo,
            $this->clasAdmUr,
            $bdo_id,
        ];
        $this->setSql('
            UPDATE cat_BancoDocumentos
            SET bdo_total = ?,
                bdo_percepcionPresupuestal = ?,
                bdo_percepcionContable = ?,
                bdo_retencionContable = ?,
                bdo_estadoDevengo = ?,
                bdo_estadoEjercido = ?,
                bdo_estadoMinistrado = ?,
                bdo_estadoPago = ?
            WHERE gpo_id = ?
            AND ur_id = ?
            AND bdo_id = ?;');

        $this->consultar($parametros);

        // Ejecutamos el procedimiento almacenado para actualizar el estado del devengo en el banco de documentos

        // Marcos 21/julio
        // Sólo cuando NO estemos cerrando afectaciones exclusivamente contables, porque ahí NO llamaremos al storeprocedure

        // Marcos: 08/abr/2016
        // Al revisar el store del 2015, se determina que se cambie manualmente en la consulta de arriba
        // el valor de bdo_EstadoDevengo a 3
        // acordado con Israel

        $this->desconectar();

        $this->mixScript('
            $("#tabla_documentos").tabla("llenaTabla");
            notify("El documento se ha cerrado exitosamente", "warning");
        ');
    }

    private function escribeBitacora($observacion)
    {
        $ip = ($this->getIp());
        $ip = (isset($ip) && $ip != '::1') ? $ip : '127.0.0.1';
        $this->conectar();
        $this->setSql('SET @usr_ip = INET_ATON(?), @usr_id = ?, @bit_observacion = ?');
        $this->consultar([$ip, $this->usarioId, $observacion]);
    }


    private function getTabla($esSeleccionaDocumento = false): object
    {
        $callback = $esSeleccionaDocumento ? '"seleccionaDocumento"' : 'false';
        $tabla = <<<JSON
        {
            "id":"tabla_documentos",
            "titulo":"Documentos de la UR",
            "trDblClick": {$callback},
            "esCompacta": true,
            "comunica": {
                "pantalla": "{$this->pantalla}",
                "metodo":"llenaTablaDocumentos"
            },
            "seleccionable": true,
            "encabezado":[[
                {"texto":"Tipo"},
                {"texto":"Referencia"},
                {"texto":"Fecha", "width": "7%"},
                {"texto":"RFC Emisor"},
                {"texto":"Presupuestal"},
                {"texto":"Concepto"},
                {"texto":"DEV"},
                {"texto":"..."},
                {"texto":"XML"},
                {"texto":"SAT"}
            ]],
            "cuerpo":[
                { "campoTxt" : {"campos":["t","fr","rct"]}, "align": "left", "jsCallback": "tdfFormatter"},
                { "campoTxt" : {"campos":["r","id"]}, "align": "left", "jsCallback": "idFormatter", "ajustable": true},
                { "campoTxt" : "f", "align": "left"},
                { "campoTxt" : "e", "align": "left", "jsCallback": "rfcFormatter", "ajustable": true},
                { "campoTxt" : "*", "align": "left", "jsCallback": "FormatoMoneda"},
                { "campoTxt" : {"campos":["c", "x"]}, "align": "left", "jsCallback": "FormatearTexto", "ajustable": true},
                { "campoTxt" : {"campos":["s", "x"]}, "align": "left", "jsCallback": "statusFormatter"},
                { "campoTxt" : "x", "align": "left", "jsCallback": "canceladoFormatter"},
                { "campoTxt" : "o", "align": "left", "jsCallback": "origenFormatter"},
                { "campoTxt" : "v", "align": "left", "jsCallback": "satFormatter"}
            ]
        }
        JSON;
        return json_decode(preg_replace("/[\r\n|\n|\r|\ ]+/", '', $tabla));
    }

    public function llenaTablaDocumentos()
    {
        $tdf = ((isset($this->prm->tdf) && $this->prm->tdf != '') ? $this->prm->tdf : '');
        $rfc = ((isset($this->prm->rfc) && $this->prm->rfc != '') ? $this->prm->rfc : '');
        $ref = ((isset($this->prm->ref) && $this->prm->ref != '') ? $this->prm->ref : '');
        $del = ((isset($this->prm->del) && $this->prm->del != '') ? $this->prm->del : '');
        $al = ((isset($this->prm->al) && $this->prm->al != '') ? $this->prm->al : '');
        $ed = ((isset($this->prm->ed) && $this->prm->ed != '') ? $this->prm->ed : '');
        $id = ((isset($this->prm->id) && $this->prm->id != '') ? $this->prm->id : '');
        $ih = ((isset($this->prm->ih) && $this->prm->ih != '') ? $this->prm->ih : '');
        $fr = ((isset($this->prm->fr) && $this->prm->fr != '') ? 'S' : 'N');
        $paginaActual = ((isset($this->prm->p) && $this->prm->p != '0') ? $this->prm->p : '');
        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != '') {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }

        $where = '';
        $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
        // Para el filtro por tipo de documento fuente
        // -------------------------------------------------------------------------------------------
        if ($tdf != '') {
            $where .= ' AND tdf_id = ? ';
            array_push($parametros, $tdf);
        }

        // Para filtrar documentos sólo de fondo rotatorio
        // -------------------------------------------------------------------------------------------
        if ($fr != '') {
            $where .= ' AND bdo_fondoRotatorio = ? ';
            array_push($parametros, $fr);
        }

        // Para filtrar por rfc del emisor
        // -------------------------------------------------------------------------------------------
        if ($rfc != '') {
            $where .= ' AND ( d.rfc_id = ? ) ';
            array_push($parametros, $rfc);
        }

        // Para buscar por referencia
        // -------------------------------------------------------------------------------------------
        if ($ref != '') {
            // Si es una petición de la vinculación por CLC, es uno o más documentos y usamos IN
            if (substr($ref, 0, 1) == '*') {
                $where .= ' AND ( bdo_id IN ( ? ) )';
                array_push($parametros, substr($ref, 1));
                // Si es comportamiento normal, usamos LIKE
            } else {
                $where .= ' AND ( bdo_referencia like ? ) ';
                array_push($parametros, '%' . $ref . '%');
            }
        }

        // Para filtrar por fecha DEL
        // -------------------------------------------------------------------------------------------
        if (trim($del) != '') {
            $where .= ' AND bdo_fechaDocumento >= ? ';
            array_push($parametros, $this->date_to_mysqldate($del));
        }

        // Para filtrar por fecha AL
        // -------------------------------------------------------------------------------------------
        if (trim($al) != '') {
            $where .= ' AND bdo_fechaDocumento <= ? ';
            array_push($parametros, $this->date_to_mysqldate($al));
        }

        // Para filtrar por importe del documento DESDE ...
        // -------------------------------------------------------------------------------------------
        if (trim($id) != '') {
            $where .= ' AND bdo_percepcionPresupuestal >= ? ';
            array_push($parametros, $id);
        }
        // Para filtrar por importe del documento HASTA ...
        // -------------------------------------------------------------------------------------------
        if (trim($ih) != '') {
            $where .= ' AND bdo_percepcionPresupuestal <= ? ';
            array_push($parametros, $ih);
        }

        // Para filtrar el status del documento:
        // 1 = documento sin devengar o parcialmente devengados
        // 2 = totalmente devengado
        // 3 = todos los documentos SIN cancelados
        // 4 = todos los documentos INCLUYENDO cancelados
        // 5 = SIN POLIZA
        // POR DEFAULT SERA 1
        // -------------------------------------------------------------------------------------------

        // Si venimos desde la pantalla de vinculación por clc, mostramos todos y por lo tanto no hacemos ésta validación
        if (substr($ref, 0, 1) != '*' && trim($ed) != '') {
            switch ($ed) {
                case '1':
                    // Si es sin devengar o parcialmente devengados
                    $where .= ' AND bdo_estadoDevengo  <> 3 AND sts_id = 1 ';
                    break;
                case '2':
                    // Si es totalmente devengados
                    $where .= ' AND bdo_estadoDevengo = 3 AND sts_id = 1 ';
                    break;
                case '3':
                    // Si es todos SIN cancelados
                    $where .= ' AND sts_id = 1 ';
                    break;
                case '5':
                    // SIN POLIZA
                    $where .= ' AND poliza IS NULL ';
                    break;
                case '4':
                default:
                    // Si es todos los documentos entonces NO hacemos nada ...
                    break;
            }
        }
        $this->setParams($parametros);
        $this->setSql(
            "
            SELECT
                gpo_id, ur_id, id, t, r, f, e, i, c, s, x, o, v, fr, rct,
                0 AS tpa,
                '' AS mf,
                pp, pc, rc
            FROM (
                SELECT
                    d.gpo_id, d.ur_id, bdo_id AS id, tdf_id AS t, bdo_referencia AS r, DATE_FORMAT( bdo_fechaDocumento, '%d-%m-%y' ) AS f,
                    d.rfc_id AS e,
                    bdo_percepcionPresupuestal AS i,
                    bdo_concepto AS c, bdo_estadoDevengo AS s, sts_id AS x, bdo_origen AS o, bdo_estadoSat AS v, bdo_fondoRotatorio AS fr, bdo_rectificaciones AS rct,
                    0 AS ncp_importe,
                    0 AS ncc_importe,
                    0 AS afe_id,
                    bdo_percepcionPresupuestal as pp,
                    bdo_percepcionContable as pc,
                    bdo_retencionContable as rc
                FROM cat_BancoDocumentos d
                WHERE gpo_id = ?
                    AND ur_id = ? " .
                $where . '
                GROUP BY bdo_id, afe_id
            ) AS o
            GROUP BY id '
        );
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $nav->idTabla = 'tabla_documentos';

        $res = $this->wrLlenaTabla($nav);
        if ($res == 0) {
            $this->mixScript('notify("No se encontraron documentos con los criterios proporcionados","warning");');
            return;
        }
    }

    /**
     * @deprecated
     * esta funcion es para conservar la funcionalidad pasada de pintar la tabla de documentos
     * usando slickgrid
     */
    private function jsSlickGrid()
    {
        $js = <<<JS

            /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
            function filtrarDocumentos() {
                dataFiltrado = [];
                dataFiltrado = $.grep(dataDocumentos,
                    function(element){
                        if (element.c != null && element.c != "" && element.c.toUpperCase().indexOf($("#buscar").val().toUpperCase()) == -1) {
                            if (element.i != null && element.i != "" && element.i.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                if (element.t != null && element.t != "" && element.t.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                    if (element.r != null && element.r != "" && element.r.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                        if (element.f != null && element.f != "" && element.f.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                            if (element.e != null && element.e != "" && element.e.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                                return false;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        return true;
                    });
                gridDocumentos.setData(dataFiltrado);
                gridDocumentos.render();
                gridDocumentos.init();
                gridDocumentos.autosizeColumns();
            }

            // Buscamos y cargamos los documentos que cumplan con los criterios solicitados
            function CargaDocumentos(l) {
                dataDocumentos = [];
                dataFiltrado = [];
                $("#buscar").val("");
                var miURL = "lib/dameDocumentos.php";
                if ($("#buscar").attr("tp") == "0") {
                    var bolPrimeraBusqueda = true;
                } else {
                    var bolPrimeraBusqueda = false;
                }

                $.ajax({
                    data : {
                        "tdf" : $("#tdf").attr("ta"),
                        "rfc" : $("#rfcFiltro").val(),
                        "ref" : $("#referenciaFiltro").val(),
                        "del" : $("#desdeFiltro").val(),
                        "al" : $("#hastaFiltro").val(),
                        "ed" : $("#estadoDocumento").val(),
                        "id" : $("#impteDesdeFiltro").val(),
                        "ih" : $("#impteHastaFiltro").val(),
                        "fr" : (($("#chkFiltroFR").prop("checked")) ? "S" : "N"),
                        "p" : $("#buscar").attr("ps")
                    },
                    url:  miURL,
                    type: "post",
                    success: function (response) {
                        var json = $.parseJSON(response);
                         // alert(JSON.stringify($.parseJSON(response), null, 4));
                        /* Empezamos a partir del 1 porque en la 0 traemos el total de los Documentos */
                        for (var i = 1; i < json.length; i++) {
                            dataDocumentos[i-1] = {
                                id: json[i].id,
                                t: json[i].t,
                                r: json[i].r,
                                f: json[i].f,
                                e: json[i].e,
                                i: json[i].i,
                                c: json[i].c,
                                s: json[i].s,
                                x: json[i].x,
                                o: json[i].o,
                                v: json[i].v,
                                tpa:json[i].tpa,
                                mf :json[i].mf,
                                fr :json[i].fr,
                                rct:json[i].rct,
                                pp :json[i].pp,
                                pc :json[i].pc,
                                rc :json[i].rc,
                                app:json[i].afe_pp,
                                apc:json[i].afe_pc,
                                arc:json[i].afe_rc
                            }
                        }

                        if (bolPrimeraBusqueda) {
                            $("#buscar").attr("tp", json[0].id);   // Guardamos el numero total de paginas en un campo de la pantalla
                        }

                        gridDocumentos.resizeCanvas;
                        gridDocumentos.setData(dataDocumentos);
                        gridDocumentos.render();
                        gridDocumentos.init();
                        gridDocumentos.autosizeColumns();

                        if (parseInt($("#buscar").attr("tp")) > 1) {
                            // Para que respete la paginación, definimos como página actual la última seleccionada
                            if (typeof $("#buscar").attr("upc") === "undefined") {
                                var cPage = 1
                            } else {
                                var cPage = parseInt($("#buscar").attr("upc"));
                            }
                            var options = {
                                numberOfPages: 5,
                                bootstrapMajorVersion: 2,
                                currentPage: cPage,
                                totalPages: $("#buscar").attr("tp"),
                                listContainerClass: function () { return "nav nav-tabs"; },
                                itemContainerClass: function (type, page, current) { return (page === current) ? "active" : "pointer-cursor"; },
                                itemContentClass: function (type, page, current) {
                                    return "nav-link";
                                },
                                onPageClicked: function(e,originalEvent,type,page){
                                    // Grabamos la ultima página a la que le dieron click
                                    $("#buscar").attr("upc",page);
                                    // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                                    if ($("#DocumentosPaginador").attr("last") != page) {
                                        $("#buscar").attr("ps",page);
                                        CargaDocumentos();
                                        $("#DocumentosPaginador").attr("last", page);
                                        setTimeout(function() { $("#buscar").focus() }, 100);
                                    }
                                }
                            }
                            $("#DocumentosPaginador").bootstrapPaginator(options);
                        } else {
                            $("#DocumentosPaginador").html("");
                        }
                        //window.espera.stop();
                    }
                });
            };
            

            // DATOS PARA EL MAESTRO DE LA CLC
            var gridDocumentos;
            var dataDocumentos = [];
            var columnsDocumentos = [
                {id: "t",   name: "TIPO", field: "t", minWidth: 70, resizable: false, sorter:NumericSorter, formatter: tdfFormatter },
                {id: "r",   name: "REFERENCIA", field: "r", minWidth: 150, maxWidth: 150, sortable: true, sorter:NumericSorter, formatter: idFormatter },
                {id: "f",   name: "FECHA", field: "f", minWidth: 100, maxWidth: 200, sortable: true, sorter:NumericSorter },
                {id: "e",   name: "RFC EMISOR", field: "e", minWidth: 150, maxWidth: 200, sortable: true, sorter:NumericSorter, formatter: rfcFormatter },
                {id: "i",   name: "PRESUPUESTAL", field: "i", minWidth: 120, maxWidth: 120, sortable: true, sorter:ImporteSorter, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoMoneda },
                {id: "c",   name: "CONCEPTO", field: "c", minWidth: 500, maxWidth: 500, sorter:NumericSorter, FormatearTexto },
                {id: "s",   name: "DEV", field: "s", sortable: false, minWidth: 55, maxWidth: 100, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: statusFormatter },
                {id: "x",   name: "...", field: "x", sortable: false, minWidth: 55, maxWidth: 100, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: canceladoFormatter },
                {id: "o",   name: "XML", field: "o", sortable: false, minWidth: 55, maxWidth: 100, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: origenFormatter },
                {id: "v",   name: "SAT", field: "v", sortable: false, minWidth: 55, maxWidth: 100, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: satFormatter }
            ];
            var options = {
                enableCellNavigation: true,
                enableColumnReorder: false,
                autoHeight:false
                
            };

            gridDocumentos = new Slick.Grid($("#gridDatos"), dataDocumentos, columnsDocumentos, options);
            gridDocumentos.setSelectionModel(new Slick.RowSelectionModel());

            // Función para ordenar uina columna del grid. debe existir en su definición del array columns que es sortable y el sorter
            gridDocumentos.onSort.subscribe(function(e, args) {
                sortdir = args.sortAsc ? 1 : -1;
                sortcol = args.sortCol.field;

                dataDocumentos.sort(args.sortCol.sorter, sortdir);
                args.grid.invalidateAllRows();
                args.grid.render();
            });

            // Para obtener el valor de una celda al presionar doble click en ella
            gridDocumentos.onClick.subscribe(function(e) {
                e.preventDefault();
                var cell = gridDocumentos.getCellFromEvent(e),
                row = cell.row;

                // Si no hay nada que selecionar, no hacemos nada
                if (dataDocumentos[row].n == "") { e.stopPropagation();   return false; }

                e.stopPropagation();
            });

            // Para obtener el valor de una celda al presionar doble click en ella
            gridDocumentos.onDblClick.subscribe(function(e) {
                var cell = gridDocumentos.getCellFromEvent(e),
                row = cell.row;
                col = cell.cell;
                console.log(cell);
                console.log(e);

                /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
                if ($("#buscar").val() == "") {
                    $("#" + numDocto).val(dataDocumentos[row].r);
                    $("#" + numDocto).attr("tdf", dataDocumentos[row].t);
                    $("#" + numDocto).attr("bdo", dataDocumentos[row].id);

                    $("#" + numDocto).attr("pp", dataDocumentos[row].pp);
                    $("#" + numDocto).attr("pc", dataDocumentos[row].pc);
                    $("#" + numDocto).attr("rc", dataDocumentos[row].rc);

                    // Si el rfc existente difiere del registrado para éste documento lanzamos una alerta
                    if ($("#" + rfcDocto).val()){
                        if($("#" + rfcDocto).val().trim().toUpperCase() != dataDocumentos[row].e.trim()) {
                            //notify("El RFC difiere del registrado en el Documento","error");
                            notify("RFC enviado : " + $("#" + rfcDocto).val().trim() + ", RFC BD : " + dataDocumentos[row].e.trim(), "error");
                        }
                        setTimeout(function() { $("#" + numDocto).focus() }, 50);
                        $("#regresarBD").click();
                    }

                } else {
                    $("#" + numDocto).val(dataFiltrado[row].r);
                    $("#" + numDocto).attr("tdf", dataFiltrado[row].t);
                    $("#" + numDocto).attr("bdo", dataFiltrado[row].id);

                    $("#" + numDocto).attr("pp", dataFiltrado[row].pp);
                    $("#" + numDocto).attr("pc", dataFiltrado[row].pc);
                    $("#" + numDocto).attr("rc", dataFiltrado[row].rc);

                    if($("#" + rfcDocto).val().trim() != dataFiltrado[row].e.trim()) {
                        notify("El RFC difiere del registrado en el Documento","error");
                    }
                    //setTimeout(function() { $("#" + numDocto).focus() }, 100);
                    $("#regresarBD").click();
                }
            });
        JS;

        return $js;
    }

    private function getJsDocumentos($regresarAPrincipal, $bdo, $tempRfc)
    {
        $scpe_snur = mysql_escape_mimic($this->snur);
        $bdo = ($bdo == '' ? '' : '$("#vclcPantallaCompleta").show();');
        $regresarAPrincipal = $regresarAPrincipal ? '$("nav").menusinpres("clickMenu");' : '';
        $ejercicio = substr($this->sEjercicioA . '', 0, 4);
        return <<<JS
            // $(".navbar").hide();
            // $(".cabecera").hide();
            // $(".sombra-2").hide();
            // $("#bdPantallaCompleta").show();
            $(document).on("click", "#regresarBD", function (e) {
                e.preventDefault();
                $("#bd").empty();
                $("#bdPantallaCompleta").html("");

                $(document).off("click", "#regresarBD");
                $(".navbar").show();
                $(".cabecera").show();
                $(".sombra-2").show();
                {$bdo}
                {$regresarAPrincipal}
                return false;
            });

            //$(".modal-content").height($(window).height());
            //$("#gridDatos").height($(window).height() - ($("#areaBotones").height() - 90));
            $("#gridDatos").height($(window).height());
            $("#gridDatos").on("mouseenter", function(){
                $(this).css("overflow", "initial");
             });
            $(".cerrar").click(function() {
                $("#myModalDocto").hide();
                $("#myModalInfo").hide();
            });

            $(".tipoDocumento").click(function(e) {
                $("#tdf").html($(this).text().trim() + "<span class=\"caret\"></span>");
                $("#tdf").attr("ta", $(this).attr("href"));
                e.preventDefault();
            });

            $("#referencia").blur(function(e) {
                e.preventDefault();
                // si es línea de captura, DEBE ser de 11 dígitos
                if ($("#cmbTipoDocto").val() == "LC") {
                    console.log($(this).val().trim().length);
                    // Si está vacía no lo checamos, ya que si intentan grabar les dará error
                    if ($(this).val().trim().length != 0) {
                        if ($(this).val().trim().length != 11) {
                            notify("La longitud de la línea de captura DEBE ser de 11 dígitos")
                            $(this).focus();
                        }
                    }
                }
                e.stopPropagation();
                return false;
            });

            $("#ejemploBD").click(function(){
                alertify.alert("<img src=\"img/ejemploBancoDocumentos.png\">");
            });

            // Registramos un nuevo documento
            $("#cmbTipoDocto").change(function(e) {
                if ($("#cmbTipoDocto").val() == "PGT" || $("#cmbTipoDocto").val() == "LC") {
                    $("#percepPresup").hide();
                    $("#percepContab").hide();
                    $("#retenContab").show();
                } else {
                    $("#percepPresup").show();
                    $("#percepContab").show();
                    $("#retenContab").show();
                }
            });

            // Registramos un nuevo documento
            $("#adicionar").click(function(e) {
                e.preventDefault();
                //habilitamos los campos
                $("#cmbTipoDocto").prop("disabled", false);
                $("#referencia").prop("disabled", false);
                $("#rfcEmisor").prop("disabled", false);
                $("#fecha").prop("disabled", false);
                // limpiamos los campos

                $("#cmbTipoDocto").attr("ori", "");
                $("#referencia").val("");
                $("#referencia").attr("ori", "");
                $("#fecha").val("");

                $("#rfcEmisor").val("{$tempRfc}");

                // Autor: Eduardo Venegas
                // Descripcion: Aqui si la variable tempRFC se valida si tiene algo y si si, lo
                // asigno de manera automatica al rfcEmisor y que se cargue su correspondiente nombreEmisor
                if ($("#rfcEmisor").val() != ""){
                    $("#nombreEmisor").val("");
                    comunica("{$this->pantalla}", { "metodo" : "dameNombreProveedor", "r" : $("#rfcEmisor").val() });
                } else {
                    $("#nombreEmisor").val("");
                }

                $("#rfcEmisor").attr("ori", "");
                $("#concepto").val("");

                $("#importe").val("");
                $("#subtotal").val("");
                $("#descuento").val("");
                $("#retenidos").val("");

                $("#percepPresup").val("");
                $("#percepContab").val("");
                $("#retenContab").val("");

                $("#trasladados").val("");
                $("#total").val("");

                $("#chkTipoClc").prop("checked",false)	;

                // quitamos algunos textbox para clarificarle al usuario la captura de importes
                $(".entradaOpcional").hide();
                //$("#total").prop("disabled", false);

                $(".modal-title").text("Adicionar Documento");
                $(".modal-title").attr("t", "a");

                $("#myModalDocto").show()
                setTimeout(function() { $("#cmbTipoDocto").focus() }, 100);
                e.stopPropagation();
            });

            // Cargamos un XML de un CFDI
            $("#xmlCFDI").click(function(e) {
                $(".modal-title").html("Cargar XML de un CFDI");
                $("#myModalInfo").show();
                comunica("{$this->pantalla}", { "metodo" : "cargarXmlCFDI" });
                e.stopPropagation();
            });
           /// let val = {mysql_escape_mimic($this->snur)}           // Enviamos a PDF o Excel la selección actual
            $(".impDocto").click(function(e) {
                e.preventDefault();
                var form = $("<form>")
                    .attr("action", "rep/doc/scp/rep_Reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "encabLin1").attr("value", "SISTEMA ESTATAL DE FINANZAS PUBLICAS DE OAXACA.   EJERCICIO {$ejercicio}"))
                    .append($("<input>").attr("type", "hidden").attr("name", "encabLin2").attr("value", "{$scpe_snur}"))
                    .append($("<input>").attr("type", "hidden").attr("name", "encabLin3").attr("value", "LISTADO DE DOCUMENTOS"))
                    .append($("<input>").attr("type", "hidden").attr("name", "tdf").attr("value", $("#tdf").attr("ta")))
                    .append($("<input>").attr("type", "hidden").attr("name", "rfc").attr("value", $("#rfcFiltro").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ref").attr("value", $("#referenciaFiltro").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "del").attr("value", $("#desdeFiltro").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "al").attr("value",  $("#hastaFiltro").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ed").attr("value",  $("#estadoDocumento").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "id").attr("value", $("#impteDesdeFiltro").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ih").attr("value",  $("#impteHastaFiltro").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "size").attr("value", "8"))
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", $(this).attr("id")))
                    .append($("<input>").attr("type", "hidden").attr("name", "orientacion").attr("value", "L"))
                    .append($("<input>").attr("type", "hidden").attr("name", "sql").attr("value", "listaDocto"));
                $("body").append(form);
                form.submit();
                e.stopPropagation();
            });

            $("#grabarDocumento").click(function(e){
                e.preventDefault();
                // Validamos campos obligatorios
                if ($("#referencia").val().trim() == "") {
                    notify("El número de documento de referencia no puede quedar vacío", "warning");
                    setTimeout(function() { $("#referencia").focus() }, 100);
                    return false;
                }
                if ($("#fecha").val().trim() == "") {
                    notify("La fecha del documento de referencia no puede quedar vacía", "warning");
                    setTimeout(function() { $("#fecha").focus() }, 100);
                    return false;
                }
                if ($("#referencia").val().trim() == "") {
                    notify("El número de documento de referencia no puede quedar vacío", "warning");
                    setTimeout(function() { $("#referencia").focus() }, 100);
                    return false;
                }
                //Funcionalidad F1
                if ($("#rfcEmisor").val().trim() == "") {
                    notify("El RFC del emisor del documento de referencia no puede quedar vacío", "warning");
                    setTimeout(function() { $("#rfcEmisor").focus() }, 100);
                    return false;
                }
                if ($("#importe").val().trim() == "" || $("#importe").val().trim() == "0.00") {
                    var pp = parseFloat($("#percepPresup").val().trim());
                    var pc = parseFloat($("#percepContab").val().trim());
                    var rc = parseFloat($("#retenContab").val().trim());

                    // Si la suma de percepciones presupuestales + percepciones contables es menor o igual a cero ...
                    if ($("#cmbTipoDocto").val() == "PGT" || $("#cmbTipoDocto").val() == "LC") {
                        if ((rc) <= 0) {
                            notify("El importe neto del documento de referencia no puede quedar vacío", "warning");
                            setTimeout(function() { $("#retenContab").focus() }, 100);
                            return false;
                        }
                    } else {
                        if ((pp + pc) <= 0) {
                            notify("El importe neto del documento de referencia no puede quedar vacío", "warning");
                            setTimeout(function() { $("#percepPresup").focus() }, 100);
                            return false;
                        }
                    }
                // Si no está vacío, checamos que sea mayor que cero
                } else {
                    var im = parseFloat($("#importe").val().trim());
                    if (im < 0 && ($("#cmbTipoDocto").val() != "PGT" || $("#cmbTipoDocto").val() != "LC")) {
                        notify("El importe neto del documento de referencia no puede ser menor que cero", "warning");
                        setTimeout(function() { $("#importe").focus() }, 100);
                        return false;
                    }
                }
                if (parseFloat($("#importe").val()) < 0) {
                    notify("El importe neto del documento no puede ser menor a cero", "warning");
                    setTimeout(function() { $("#importe").focus() }, 100);
                    return false;
                }
                //window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...

                // Grabamos en la base de datos ...
                comunica("{$this->pantalla}", {
                    "metodo" : "grabarDocumento",
                    "t" : $(".modal-title").attr("t"),
                    "tdf" : $("#cmbTipoDocto").val(),
                    "tdfOri" : $("#cmbTipoDocto").attr("ori"),
                    "ref" : $("#referencia").val(),
                    "ori" : $("#referencia").attr("ori"),
                    "fecha" : $("#fecha").val(),
                    "rfcE" : $("#rfcEmisor").val(),
                    "rfcE_ori" : $("#rfcEmisor").attr("ori"),
                    "c" : $("#concepto").val(),
                    "neto" : $("#importe").val(),
                    "iori" : $("#importe").attr("ori"),
                    "st" : $("#subtotal").val(),
                    "de" : $("#descuento").val(),
                    "ir" : $("#retenidos").val(),
                    "it" : $("#trasladados").val(),
                    "to" : $("#total").val(),
                    "tpp" : $("#percepPresup").val(),
                    "tpc" : $("#percepContab").val(),
                    "trc" : $("#retenContab").val(),
                    "clc" : (($("#chkTipoClc").prop("checked")) ? "1" : "0"),
                    "frt" : (($("#chkFondoRotatorio").prop("checked")) ? "S" : "N"),
                    "uu" : ""
                });
                e.stopPropagation();
                return false;
            });

            $("#desdeFiltro").blur(function(e){
                e.preventDefault();
                validaFecha($(this).val(), "desdeFiltro", false, false, ' . $this->sEjercicioA . ');
                e.stopPropagation();
                return false;
            });

            $("#hastaFiltro").blur(function(e){
                e.preventDefault();
                validaFecha($(this).val(), "hastaFiltro", false, false, ' . $this->sEjercicioA . ');
                e.stopPropagation();
                return false;
            });

            $("#chkImpte").click(function(e) {
                // Si ya está marcado, desmarcamos todo
                if ($("#chkImpte").prop("checked") == false) {
                    // Si NO está marcado, seleccionamos todo
                    var columnsDocumentos = [
                        {id: "t",   name: "TIPO", field: "t", minWidth: 70, maxWidth: 70, sortable: true, sorter:NumericSorter, formatter: tdfFormatter },
                        {id: "r",   name: "REFERENCIA", field: "r", minWidth: 200, maxWidth: 200, sortable: true, sorter:NumericSorter, formatter: idFormatter },
                        {id: "f",   name: "FECHA", field: "f", minWidth: 70, maxWidth: 70, sortable: true, sorter:NumericSorter },
                        {id: "e",   name: "RFC EMISOR", field: "e", minWidth: 120, maxWidth: 120, sortable: true, sorter:NumericSorter, formatter: rfcFormatter },
                        {id: "i",   name: "PRESUPUESTAL", field: "i", minWidth: 120, maxWidth: 120, sortable: true, sorter:ImporteSorter, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoMoneda },
                        {id: "c",   name: "CONCEPTO", field: "c", sortable: false, formatter: FormatearTexto },
                        {id: "s",   name: "DEV", field: "s", sortable: false, maxWidth: 50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: statusFormatter },
                        {id: "x",   name: "...", field: "x", sortable: false, maxWidth: 50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: canceladoFormatter },
                        {id: "o",   name: "XML", field: "o", sortable: false, maxWidth: 50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: origenFormatter },
                        {id: "v",   name: "SAT", field: "v", sortable: false, maxWidth: 50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: satFormatter }
                    ];
                } else {
                    var columnsDocumentos = [
                        {id: "t",   name: "TIPO", field: "t", minWidth: 70, maxWidth: 70, sortable: true, sorter:NumericSorter, formatter: tdfFormatter },
                        {id: "r",   name: "REFERENCIA", field: "r", minWidth: 100, maxWidth: 100, sortable: true, sorter:NumericSorter, formatter: idFormatter },
                        {id: "f",   name: "FECHA", field: "f", minWidth: 70, maxWidth: 70, sortable: true, sorter:NumericSorter },
                        {id: "e",   name: "RFC EMISOR", field: "e", minWidth: 120, maxWidth: 120, sortable: true, sorter:NumericSorter, formatter: rfcFormatter },
                        {id: "i",   name: "PRESUPUESTAL", field: "i", minWidth: 120, maxWidth: 120, sortable: true, sorter:ImporteSorter, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoMoneda },
                        {id: "c",   name: "CONCEPTO", field: "c", sortable: false, formatter: FormatearTexto },
                        {id: "pp",  name: "AFE PPresup", field: "afe_pp", minWidth: 120, maxWidth: 120, sortable: true, sorter:ImporteSorter, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoImporte },
                        {id: "pc",  name: "AFE PContab", field: "afe_pc", minWidth: 120, maxWidth: 120, sortable: true, sorter:ImporteSorter, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoImporte },
                        {id: "rc",  name: "AFE RContab", field: "afe_rc", minWidth: 120, maxWidth: 120, sortable: true, sorter:ImporteSorter, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoImporte }
                    ];
                }
                gridDocumentos.setColumns(columnsDocumentos);
                gridDocumentos.render();
                gridDocumentos.init();
                gridDocumentos.autosizeColumns();
                e.stopPropagation();
            });

            $("#rfcEmisor").focus(function(e) {
                rfcCampo = "rfcEmisor";
                rfcBeneficiario = "nombreEmisor";
            });

            // Como desglosaremos los importes que pudiera traer un comprobante (subtotal, descuentos, impuestos retenidos, impuestos trasladados)
            // cada que cambie uno de ellos aplicaremos reglas para obtener el total neto, y el importe que usa el SINPRES

            function recalculaImportes() {
                // Obtenemos los importes
                var subtotal    = parseFloat($("#subtotal").val());
                var descuento   = parseFloat($("#descuento").val());
                var retenidos   = parseFloat($("#retenidos").val());
                var trasladados = parseFloat($("#trasladados").val());

                // Si no son importes, su valor será cero
                subtotal    = (!isNaN(subtotal) ? subtotal : 0);
                descuento   = (!isNaN(descuento) ? descuento : 0);
                retenidos   = (!isNaN(retenidos) ? retenidos : 0);
                trasladados = (!isNaN(trasladados) ? trasladados : 0);

                var total = (subtotal - descuento - retenidos) + trasladados;
                var importe = 0.00;

                // Lic. Andrea (11/feb/2015) : cuando el documento sea para obra o Administración Directa, el importe que usará el simpres
                // será el subtotal + impuestos TRASLADADOS
                if ($("#chkTipoClc").prop("checked")) {
                    // Ticket 311684 de andrea, piden especificamente que sea subtotal + trasladados
                    importe = subtotal + trasladados;
                } else {
                    importe = (subtotal - descuento - retenidos) + trasladados;
                }
                $("#importe").val(importe.toFixed(2));
                $("#total").val(total.toFixed(2));
            };

            // Solicitud de Egresos y Contabilidad: que se desglosen las percepciones presupuestales y contables, y las retenciones contables
            

            /* Cada que cambie un importe aplicamos reglas para obtener el total y el Importe que usa el SINPRES
            Esto aplica para el cambio de la segunda mitad del ejercicio 2015, liberada el 1ro. de julio*/
            $("#percepPresup").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var percepPresup = parseFloat($("#percepPresup").val());
                percepPresup = (!isNaN(percepPresup) ? percepPresup : 0);
                $("#percepPresup").val(percepPresup)
                recalculaImportesDesglosados();
            });

            $("#percepContab").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var percepContab = parseFloat($("#percepContab").val());
                percepContab = (!isNaN(percepContab) ? percepContab : 0);
                $("#percepContab").val(percepContab)
                recalculaImportesDesglosados();
            });

            $("#retenContab").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var retenContab = parseFloat($("#retenContab").val());
                retenContab = (!isNaN(retenContab) ? retenContab : 0);
                $("#retenContab").val(retenContab)
                recalculaImportesDesglosados();
            });

            /* Cada que cambie un importe aplicamos reglas para obtener el total y el Importe que usa el SINPRES */
            $("#subtotal").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var subtotal    = parseFloat($("#subtotal").val());
                subtotal    = (!isNaN(subtotal) ? subtotal : 0);
                $("#subtotal").val(subtotal)
                recalculaImportes();
            });

            $("#descuento").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var descuento    = parseFloat($("#descuento").val());
                descuento    = (!isNaN(descuento) ? descuento : 0);
                $("#descuento").val(descuento)
                recalculaImportes();
            });

            $("#retenidos").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var retenidos    = parseFloat($("#retenidos").val());
                retenidos    = (!isNaN(retenidos) ? retenidos : 0);
                $("#retenidos").val(retenidos)
                recalculaImportes();
            });

            $("#trasladados").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var trasladados    = parseFloat($("#trasladados").val());
                trasladados    = (!isNaN(trasladados) ? trasladados : 0);
                $("#trasladados").val(trasladados)
                recalculaImportes();
            });

            /* Cada que cambie un importe aplicamos reglas para obtener el total y el Importe que usa el SINPRES */
            $("#total").blur(function() {
                // Si no es un importe válido, lo convertimos en cero
                var total    = parseFloat($("#total").val());
                total    = (!isNaN(total) ? total : 0);
                $("#total").val(total);
                $("#subtotal").val(total);
                recalculaImportes();
            });

            $("#chkTipoClc").change(function(e){
                recalculaImportes();
            });

            // Si no está el nombre del emisor y sí el rfc, lo ubicamos en la base de datos
            $("#rfcEmisor").blur(function(e) {
                // Si no está vacío el rfc del emisor ...
                if ($("#rfcEmisor").val().trim().length > 0) {
                    // Si es diferente el que está escrito con el original, lo buscamos
                    if ($("#rfcEmisor").val().trim() != $("#rfcEmisor").attr("ori").trim()) {
                        $("#nombreEmisor").val("");
                        comunica("{$this->pantalla}", { "metodo" : "dameNombreProveedor", "r" : $("#rfcEmisor").val() });
                    }
                }
            });

            $("#rfcFiltro").focus(function(e) {
                rfcCampo = "rfcFiltro";
                rfcBeneficiario = "nombreFiltro";
            });

            // Mostramos los documentos que cumplan con los criterios seleccionados
            $("#mostrar1, #mostrar2").click(function(e){
                e.preventDefault()

                $("#myModalFiltros").modal("hide");
                $("#avisosLD").remove();
                $("#buscar").attr("tp","0");
                $("#buscar").attr("ps","");
                $("#tabla_documentos").tabla("llenaTabla");
                setTimeout(function() { $("#buscar").focus() }, 100);
                e.stopPropagation();
                return false;
            });

            

            $("#impteDesdeFiltro").blur(function(){
                var importe = parseFloat(unformatNumber($("#impteDesdeFiltro").val()).toFixed(2));
                if (importe == 0 || isNaN(importe)) {
                    $("#impteDesdeFiltro").val("");
                } else {
                    $("#impteDesdeFiltro").val(importe);
                }
            });

            $("#impteHastaFiltro").blur(function(){
                var importe = parseFloat(unformatNumber($("#impteHastaFiltro").val()).toFixed(2));
                if (importe == 0 || isNaN(importe)) {
                    $("#impteHastaFiltro").val("");
                } else {
                    $("#impteHastaFiltro").val(importe);
                }
            });

            // Modificamos la póliza actualmente seleccionada
            $("#cerrarDocto").click(function(e) {
                const row = $("#tabla_documentos").tabla('getFilaSeleccion');
                if (!row || !row?.r) {
                    return false;
                }

                // Marcos: 13/sep/2016
                // NO permtiremos cerrar documentos automáticos
                if (row.t == "SDI") {
                    notify("NO puedes cerrar un documento AUTOMÁTICO");
                    e.stopPropagation();
                    return false;
                }

                // Si hay documento con el status 1 (parcialmente devengado)
                if (row.s == "2" || (row.s == "3" && e.ctrlKey) || (row.s == "1" && e.ctrlKey)) {
                    // Si ya tiene algún importe devengado ...
                    if (row.tpa != "0.00") {
                        // Y si el importe que trae es un número ...
                        if (!isNaN(parseFloat(row.tpa)) && isFinite(row.tpa)) {
                            // Para cerrar un documento con el status de TOTALMENTE DEVENGADO
                            if ((row.s == "3" || row.s == "1") && e.ctrlKey) {
                                alertify.confirm(
                                    "El documento ya está TOTALMENTE DEVENGADO",
                                    "¿ Juras por la Patria, que ya revisaste tu información y es necesario volver a cerrar el documento para ajustar importes que incorrectamente capturaste, y si no lo hubieras hecho que la patria os lo demande ?",
                                    function(){
                                        alertify.prompt(
                                            "Confirmar",
                                            "<h4>Cerrar un documento parcialmente devengado significa que en la realidad ya se ha devengado la TOTALIDAD del mismo, y por lo tanto DEBE marcarse como TOTALMENTE DEVENGADO y generar pólizas contables, ya que NO se harán más afectaciones que lo usen.Importe Total del Documento: $ " + formatNumber(row.i) + "Importe Total en Afectaciones Autorizadas: $ " + formatNumber(row.tpa) + (row.rfc != "0" ? " <font color=\"red\">MENOS RECTIFICACIONES</font>" : "") + "<b><u>El importe del documento se ajustará automáticamente al segundo importe.</b></u>Escribe <b>ENTERADO</b> para CERRAR DEFINITIVAMENTE y DEVENGAR el documento.</h4>",
                                            "Debes escribir ENTERADO",
                                            function(evt, value){
                                                if (value !== null) {
                                                    if (value == "ENTERADO") {
                                                        comunica("{$this->pantalla}",{
                                                            "metodo" : "cerrarDocumento",
                                                            "id"  : row.id,
                                                            "tdf" : row.t,
                                                            "ref" : row.r,
                                                            "rfc" : row.e,
                                                            "ia"  : row.i
                                                        });
                                                    } else {
                                                        if (value.length > 0) {                                                            
                                                            notify("Escribiste " + value + ".   Debes escribir ENTERADO", "warning");
                                                        }
                                                    }
                                                }
                                            },
                                            function(){},
                                        ).set("labels", {ok: "Aceptar", cancel: "Cancelar"});;
                                    },
                                    function(){},
                                ).set("labels", {ok: "¡ Lo juro !", cancel: "Cancelar"});

                            } else {
                                alertify.prompt(
                                    "Confirmar",
                                    "<h4>Cerrar un documento parcialmente devengado significa que en la realidad ya se ha devengado la TOTALIDAD del mismo, y por lo tanto DEBE marcarse como TOTALMENTE DEVENGADO y generar pólizas contables, ya que NO se harán más afectaciones que lo usen.Importe Total del Documento: $ " + formatNumber(row.i) + "Importe Total en Afectaciones Autorizadas: $ " + formatNumber(row.tpa) + (row.rfc != "0" ? " <font color=\"red\">MENOS RECTIFICACIONES</font>" : "") + "<b><u>El importe del documento se ajustará automáticamente al segundo importe.</b></u>Escribe <b>ENTERADO</b> para CERRAR DEFINITIVAMENTE y DEVENGAR el documento.</h4>",
                                    "Debes escribir ENTERADO",
                                    function(evt, value){
                                        if (value !== null) {
                                            if (value == "ENTERADO") {
                                                comunica("{$this->pantalla}",{
                                                    "metodo" : "cerrarDocumento",
                                                    "id"  : row.id,
                                                    "tdf" : row.t,
                                                    "ref" : row.r,
                                                    "rfc" : row.e,
                                                    "ia"  : row.i
                                                });
                                            } else {
                                                if (value.length > 0) {                                                    
                                                    notify("Escribiste " + value + ".   Debes escribir ENTERADO", "warning");
                                                }
                                            }
                                        }
                                    },
                                    function(){},
                                ).set("labels", {ok: "Aceptar", cancel: "Cancelar"});                                
                            }
                        } else {
                            notify("No puedo determinar el importe del documento", "error");
                        }
                    } else {
                        notify("El documento DEBE tener afectaciones autorizadas, para poder cerrarlo", "warning");
                    }
                } else {
                    notify("El documento actualmente seleccionado DEBE tener un status de PARCIALMENTE DEVENGADO (" + row.s + ")", "warning");
                }
                e.stopPropagation();
            });

            // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
            

            // Modificamos el documento actualmente seleccionado
            $("#modificar").click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                const row = $("#tabla_documentos").tabla('getFilaSeleccion');
                if (!row) {
                    return false;
                }

                if (row.t == "SDI") {
                    notify("NO puedes modificar un documento AUTOMÁTICO");
                    e.stopPropagation();
                    return false;
                }

                
                $(".entradaOpcional").hide();
                $("#total").prop("disabled", false);

                comunica("{$this->pantalla}",{
                    "metodo"  : "modificar",
                    "r"  : row.r,
                    "id": row.id
                });
                e.stopPropagation();
            });

            // Mostramos todos los registros donde el documento haya sido utilizado
            $("#mostrar").click(function(e) {
                e.preventDefault();
                const row = $("#tabla_documentos").tabla('getFilaSeleccion');
                if (!row) {
                    return false;
                }
                comunica("scp/afectaciones/ControladorMuestraUsoDocumento.php", { "metodo"  : "mostrar", "rfc" : row.e, "ref" : row.r, "id" : row.id });
                e.stopPropagation();
            });

            // Eliminamos un documento fuente siempre y cuando no haya sido usado en ninguna parte y se acepte la responsabilidad
            // de la eliminación del documento ...
            // SE NECESITARÁ SER USUARIO DE NIVEL UR PARA PODER BORRAR UN DOCUMENTO
            // Cambio el 17-feb-2015: Por solicitud de Israel, NO borraremos documento, simplemente le cambiaremos el
            // Status (sts_id) a 2: deshabilitado

            $("#habDeshab").click(function(e) {
                e.preventDefault();
                const row = $("#tabla_documentos").tabla('getFilaSeleccion');
                if (!row || !row?.r) {
                    return false;
                }

                // Marcos: 13/sep/2016
                // NO permtiremos habilitar/deshabilitar documentos automáticos
                if (row.t == "SDI") {
                    notify("NO puedes habilitar/deshabilitar un documento AUTOMÁTICO");
                    e.stopPropagation();
                    return false;
                }

                if (row.x == "2") {
                    strAccion = "HABILITAR";
                } else {
                    strAccion = "DESHABILITAR";
                }

                alertify.confirm(
                    "Habilitar/Deshabilitar documentos",
                    "¿ Confirmas que deseas " + strAccion + " el documento " + row.t + "-" + row.r + " de fecha " + row.f + " por un importe de " + row.i + " ?",
                    function(){
                        comunica("{$this->pantalla}",{
                            "metodo"  : "habDeshab",
                            "r" : row.r,
                            "id" : row.id,
                            "s" : row.x
                        });
                    },
                    function(){},
                ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                e.stopPropagation();
            });

            // Que dice mi mamá que siempre sí podremos borrar documentos.   El asunto está en que llegó una UR 703 pidiendo borrar
            // el documento, luego lo cargaron otra vez y otra vez lo borraron con autorización de egresos porque la factura
            // venía mal.   Que puedan borrar (al fin egresos a través de Andrea dijo que no veían problema)
            // ***** SOLO PUEDE BORRARSE UN DOCUMENTO CUANDO NO SE HAYA USADO EN NINGUN REGISTRO DE OTRA TABLA EN LA BASE DE DATOS *****
            // SE NECESITARÁ SER USUARIO DE NIVEL UR PARA PODER BORRAR UN DOCUMENTO

            $("#eliminar").click(function(e) {
                e.preventDefault();
                const row = $("#tabla_documentos").tabla('getFilaSeleccion');
                if (!row || !row?.r) {
                    return false;
                }

                // NO permtiremos eliminar documentos automáticos
                if (row.t == "SDI") {
                    notify("NO puedes eliminar un documento AUTOMÁTICO");
                    e.stopPropagation();
                    return false;
                }
                alertify.confirm(
                    "ELIMINAR DEFINITIVAMENTE UN DOCUMENTO",
                    "¿ Confirmas que deseas ELIMINAR DEFINITIVAMENTE el documento " + row.t + "-" + row.r + " de fecha " + row.f + " por un importe de " + row.i + " ?",
                    function(){
                        alertify.prompt(
                            "Confirmar",
                            "<h4>La eliminación de un documento es responsabilidad total de la Unidad Responsable.Se elimina siempre y cuando no haya sido utilizado en ningún lugar, y te das por enterado que si lo utilizas más adelante, deberás volver a capturarlo/cargarlo.Escribe <b>ENTERADO</b> para proceder a borrarlo.</h4>",
                            "Debes escribir ENTERADO",
                            function(evt, value){
                                if (value !== null) {
                                    if (value == "ENTERADO") {
                                        comunica("{$this->pantalla}",{
                                            "metodo" : "eliminar",
                                            "r"  : row.r,
                                            "id" : row.id,
                                            "e"  : row.e
                                        });
                                    } else {                                        
                                        if (value.length > 0) {
                                            notify("Escribiste " + value + ".   Debes escribir ENTERADO", "warning");
                                        }
                                    }
                                }
                            },
                            function(){},
                        ).set("labels", {ok: "Aceptar", cancel: "Cancelar"});;
                    },
                    function(){},
                ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});                
                e.stopPropagation();
            });

            setTimeout(function() {
                $("#filtrarEnPantalla").css({top: "2px"})
            }, 100);
            {$this->wrLlamaLlenaTabla('tabla_documentos')}
        JS;
    }

    private function funcionesJs($esSeleccionaDocumento = false)
    {
        $functionSeleccionaDocumento = '';
        if ($esSeleccionaDocumento) {
            $functionSeleccionaDocumento = <<<JS
            function seleccionaDocumento(row) {
                $("#" + numDocto).val(row.r);
                $("#" + numDocto).attr("tdf", row.t);
                $("#" + numDocto).attr("bdo", row.id);

                $("#" + numDocto).attr("pp", row.pp);
                $("#" + numDocto).attr("pc", row.pc);
                $("#" + numDocto).attr("rc", row.rc);
                if ($("#" + rfcDocto).val()){
                    if($("#" + rfcDocto).val().trim().toUpperCase() != row.e.trim()) {
                        notify("RFC enviado : " + $("#" + rfcDocto).val().trim() + ", RFC BD : " + row.e.trim(), "error");
                    }
                    setTimeout(function() { $("#" + numDocto).focus() }, 50);
                    $("#regresarBD").click();
                }
            }
            JS;
        }
        return <<<JS
            {$functionSeleccionaDocumento}
            function recalculaImportesDesglosados() {
                // Obtenemos los importes
                var percepPresup = parseFloat($("#percepPresup").val());
                var percepContab = parseFloat($("#percepContab").val());
                var retenContab = parseFloat($("#retenContab").val());

                // Si no son importes, su valor será cero
                percepPresup = (!isNaN(percepPresup) ? percepPresup : 0);
                percepContab = (!isNaN(percepContab) ? percepContab : 0);
                retenContab = (!isNaN(retenContab) ? retenContab  : 0);

                if ($("#cmbTipoDocto").val() == "PGT" || $("#cmbTipoDocto").val() == "LC") {
                    var total = retenContab;
                    $("#total").val(total.toFixed(2));
                    $("#importe").val(0);
                } else {
                    var total = (percepPresup + percepContab) - retenContab;
                    $("#total").val(total.toFixed(2));
                    $("#importe").val(total.toFixed(2));
                }
            };

            function NumericSorter(a, b) {
                var x = a[sortcol], y = b[sortcol];
                return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
            }
            function idFormatter(value, id) {
                return "<span title=\" id : " + id + "\">" + value + "</span>";
            }
            function ImporteSorter(a, b) {
                var x = parseFloat(a[sortcol]), y = parseFloat(b[sortcol]);
                return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
            }
            function origenFormatter(value) {
                return (value == "2") ? "<img src=\'css/images/file.gif\'>" : "";
            }
            function statusFormatter(value) {
                return (value == "1" || value == "2") ? "<img src='css/images/progressBar.png'>" : ((value == "3") ? "<img src='css/images/double.png'>" : "");
            }
            function canceladoFormatter(value) {
                console.log("JMA canceladorFormateter",value )
                return (value == "2") ? "<img src=\'css/images/pin.png\'>" : "";
            }
            function polizaOrden(row, cell, value, columnDef, dataContext) {
                return (value == "ORD") ? "<img src=\'css/images/punto.png\'>" : "";
            }

            function rfcFormatter(value) {
                rfc = value.trim();
                if (rfc.length == 12) {
                    return rfc.substr(0, 3) + "-" + rfc.substr(3, 6) + "-" + rfc.substr(9, 3);
                } else if (rfc.length == 13) {
                    return rfc.substr(0, 4) + "-" + rfc.substr(4, 6) + "-" + rfc.substr(10, 3);
                } else if (rfc.length == 10) {
                    return rfc.substr(0, 4) + "-" + rfc.substr(4, 6);
                } else {
                    return rfc;
                }
            }
            function satFormatter(value) {
                return (value == "2") ? "<img src='css/images/tick.png'>" : "";
            }
            function FormatearTexto(value, x) {
                // Para que al colocar el cursor sobre la celda, se vea el texto completo si no cupo al dibujarse el grid
                return (x == "2") ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
            }
            function FormatoImporte(row, cell, value, columnDef, dataContext) {
                strIni = ""
                strFin = ""
                if (value === null || value === "" || !(value != 0)) {
                    return "";
                } else {
                    var n = value,
                    c = 2,
                    d = ".",
                    t = ","
                    s = n < 0 ? "-" : "",
                    i = parseInt(n = Math.abs(+n || 0).toFixed(2)) + "",
                    j = (j = i.length) > 3 ? j % 3 : 0;
                    var impteDocto = strIni + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + strFin;
                    return impteDocto;
                }
            }

            function FormatoMoneda(row) {
                value = row.i;
                tpa = row.tpa;
                mf = row.mf;
                strIni = ""
                strFin = ""
                if (value === null || value === "" || !(value != 0)) {
                    return "";
                } else {
                    // Si ya tiene afectaciones autorizadas, mostramos en verde el importe para que se dé cuenta
                    if (tpa != "0.00") {
                        // Si el importe es el mismo lo ponemos en rojo para indicar que hay un problema
                        if (tpa == value) {
                            // Siempre y cuando su status sea diferente de 2 (devengado totalmente)
                            if (s == "2") {
                                var colorImpte = "style=\"text-align:center; color:#3a87ad;\"";
                            } else {
                                var colorImpte = "style=\"text-align:center; color:#da5959;\"";
                            }
                        } else {
                            var colorImpte = "style=\"text-align:center; color:#3a87ad;\"";
                        }
                    } else {
                        var colorImpte = "";
                    }
                    var n = value,
                    c = 2,
                    d = ".",
                    t = ","
                    s = n < 0 ? "-" : "",
                    i = parseInt(n = Math.abs(+n || 0).toFixed(2)) + "",
                    j = (j = i.length) > 3 ? j % 3 : 0;
                    var impteDocto = strIni + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + strFin;

                   // Por el total de las afectaciones presupuestales autorizadas
                    var n = tpa,
                    c = 2,
                    d = ".",
                    t = ","
                    s = n < 0 ? "-" : "",
                    i = parseInt(n = Math.abs(+n || 0).toFixed(2)) + "",
                    j = (j = i.length) > 3 ? j % 3 : 0;
                    var impteAfec = strIni + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + strFin;

                    return "<span title=\" Suma de afectaciones presupuestales Autorizadas : " + impteAfec + "   Fecha última autorización : " + (mf === null, "", mf) + "\"><label " + colorImpte + ">" + impteDocto + "</label></span>";
                }
            }

            function tdfFormatter(value, fr, rct) {
                var numDocto;
                var rfcDocto;
                if (fr != "S") {
                    if (rct == "0") {
                        return value;
                    } else {
                        if (rct.trim() != "") {
                            return value + " <font color=\"red\">r</font>";
                        }
                    }
                } else {
                    return value + " r" + "<img src=\'css/images/punto.png\'>";
                }
            }

            function fechaDevengado(row, cell, value, columnDef, dataContext){
                return (dataDocumentos[row].fDevengo === null) ? "- - -" : dataDocumentos[row].fDevengo;
            }

            function fechaCancelado(row, cell, value, columnDef, dataContext){
                return (dataDocumentos[row].fCancelado === null) ? "- - -" : dataDocumentos[row].fCancelado;
            }

        JS;
    }
}

// Si no tiene permisos de lectura, no entra a la opción
if (WebResponse::tienePermisos(TAREA_LECTURA, 'S_2_17')) {
    new ControladorBancoDocumentos();
} else {
    WebResponse::sinPermisos();
}
