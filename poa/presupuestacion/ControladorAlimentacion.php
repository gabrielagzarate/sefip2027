<?php

/**
 * Description of ControladorAlimentacion
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se estandarizan estilos botones : regresar, imprimir, guardar
 * Objective: Consultar y dar de alta nuevas cedulas de Alimentación por Unidad Responsable
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 * */

//inicio de sesion y llamada a las librerias
session_start();
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../ent/poa/Alimentacion/Alimentacion.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';
require_once __DIR__ . '/../../srv/ClasificacionFinanciamiento.php';
require_once __DIR__ . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once __DIR__ . '/../../srv/ObjetoGasto.php';
require_once __DIR__ . '/../../lib/recuperarUe.php';

class ControladorAlimentacion extends WebResponse
{
    protected $nvj;
    protected $clasAdm_gpo;
    protected $clasAdm_ur;
    protected $gpo_id;
    protected $ur_id;
    protected $usr_id;
    protected $ur;
    protected $escritura;
    protected $cierre;
    protected $estadoCosteo;
    protected $lectura;
    public $sql;
    public $params;
    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();

        //variables de session
        $this->nvj = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] != '') ? $_SESSION[_S]['nvj_id'] : '';

        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->cierre = $this->ur->getEur_id();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo(); //1 = Abierto ; 2 = Cerrado
        $estadoUR = $this->ur->getEur_id();

        //Asignación de permisos de lectura y escritura
        if (isset($_SESSION[_S]['prm']['A']['3']['1']['2']) && ($this->nvj == 1 || $this->nvj == 2) && ($this->cierre == 4 && $this->estadoCosteo == 1)) {
            $this->escritura = true; //si tiene permisos de escritura
        } else {
            $this->escritura = false; //si no tiene permisos de escritura
        }
        if (isset($_SESSION[_S]['prm']['A']['3']['1']['1'])) {
            $this->lectura = true;
        } //si tiene permisos de lectura
        else {
            $this->lectura = false;
        } //si no tiene permisos de lectura

        switch ($this->metodo) {
            case 'llenaTablaConceptos':
                $this->llenaTablaConceptos();
                break;
            case 'abcCedula':
                $this->abcCedula();
                break;
            case 'addModCedula':
                $this->addModCedula();
                break;
            default:
                $this->mainAlimentacion();
                break;
        }
        $this->returnJson();
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function mainAlimentacion()
    {
        $this->mixStyle('
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:black; opacity: 0.8;};
        ');

        //Cuando cambie la ur, refrescamos la pantalla
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {"metodo":""} );
            ')
        );
        $this->mixScript(
            '$("#techos").show();
            limpiaClasAdmin();
        '
        );
        $this->agregaTools(' ');
        $botones = '';

        //Si la planeación NO está autorizada, no le permitimos modificar nada
        if ($this->cierre < 4) {
            $this->mixHtml(
                '<div class = "alert alert-danger alert-dismissible fade show mb-4" role="alert" align = "center">
                    <strong>La Planeación NO ha sido autorizada. No se permite costear</strong>
                    <button type="button" class = "btn-close" data-bs-dismiss = "alert" aria-label="Close"></button>
                </div>'
            );
        }
        //Si el costeo está cerrado, avisamos
        if ($this->estadoCosteo == 2) {
            $this->mixHtml(
                '<div class = "alert alert-danger alert-dismissible fade show mb-4" role="alert" align = "center">
                    <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
                    <button type="button" class = "btn-close" data-bs-dismiss = "alert" aria-label="Close"></button>
                </div>'
            );
        }

        //Si tiene derechos de escritura, puede registrar una nueva cédula de alimentación
        if ($this->escritura) {
            $botones .= '
                <button id = "nuevo" type="button" class = "btn btn-outline-primary me-1" title="Registrar Nueva Cédula">
                    <i class = "fa-solid fa-plus me-2"></i>
                    Nueva
                </button>';
        } elseif ($this->lectura) {
            $this->mixHtml(
                '<div class = "alert alert-danger alert-dismissible fade show mb-4" role="alert" align = "center">
                    <strong>Solo tiene permiso de lectura</strong>
                    <button type="button" class = "btn-close" data-bs-dismiss = "alert" aria-label="Close"></button>
                </div>'
            );
        }

        //Todos pueden exportar a excel

        //Cuadro de búsqueda (filtra las Unidades Responsables a mostrar)
        $botones .= '
            <a id = "imprimirTodo" target="_blank" title="Imprimir Cédula de Alimentación" style="padding: 0">
                <button type="button" class = "btn btn-outline-danger me-1" >
                    <i class = "fa fa-file-pdf me-2"></i>
                    PDF
                </button>
            </a>
            <input type="search" width=50
                id = "filtroTabla"
                style = "display: inline; width:250px; height: 30px;"
                class = "form-control"
                placeholder = "Filtrar Cédulas de Alimentación" />';

        $this->agregaTools($botones);

        //Dibujamos la tabla con la información de las cédulas de alimentación registradas por la UR actual
        $tabla = new stdClass();
        $tabla->id = 'tbl-Conceptos';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConceptos';

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->texto = 'Ver / Editar';
        $th->width = '60px';
        array_push($tr, $th);

        //Datos del Programa, Subprograma, Proyecto, Obra/Actividad
        $th = new stdClass();
        $th->texto = 'Programático';
        array_push($tr, $th);

        //Datos de la Clave de Financiamiento
        $th = new stdClass();
        $th->texto = 'Clave Financiamiento';
        array_push($tr, $th);

        //Numero beneficiarios
        $th = new stdClass();
        $th->texto = 'Número de beneficiarios';
        $th->width = '100px';
        array_push($tr, $th);

        //Grupo beneficiario
        $th = new stdClass();
        $th->texto = 'Grupo beneficiario';
        array_push($tr, $th);

        //Area de servicio
        $th = new stdClass();
        $th->texto = 'Área de servicio';
        $th->width = '250px';
        array_push($tr, $th);

        //Total
        $th = new stdClass();
        $th->texto = 'Importe Anual';
        $th->align = 'right';
        array_push($tr, $th);

        $trbody = [];

        //contenido ver
        $td = new stdClass();
        $td->tipo = 'boton';
        if ($this->cierre != 5 && $this->escritura) {
            $td->icono = 'pencil';
        } else {
            $td->icono = 'eye';
        }
        $td->class = 'ver';
        $td->attr = new stdClass();
        $td->attr->ced_id = 'ced_id';
        $td->attr->ccd_id = 'ccd_id';
        $td->name = $td->id = 'ced_id';
        array_push($trbody, $td);

        //Datos del Programa, Subprograma, Proyecto, Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'programatico';
        array_push($trbody, $td);

        //Datos de la Clave de Financiamiento
        $td = new stdClass();
        $td->campoTxt = 'cf';
        array_push($trbody, $td);

        //Numero beneficiarios
        $td = new stdClass();
        $td->campoTxt = 'ali_beneficiarios';
        $td->width = '100px';
        $td->align = 'center';
        array_push($trbody, $td);

        //Grupo beneficiario
        $td = new stdClass();
        $td->campoTxt = 'gbf_nombre';
        array_push($trbody, $td);

        //Area de servicio
        $td = new stdClass();
        $td->campoTxt = 'asr_nombre';
        array_push($trbody, $td);

        //Total
        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaConceptos();

        $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";

            $("#nuevo").click(function(){
                comunica("' . $this->pantalla . '",{"metodo":"addModCedula"} );
            });

            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            //Quitamos la info de una paginación que no usaremos en ésta pantalla
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
                $("nav").menusinpres("clickMenu");
            });

            //Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });

            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );

            $("#imprimirTodo").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 1))

                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Llenamos la tabla con las cédulas de Alimentación registradas por la UR
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function llenaTablaConceptos()
    {
        //Si es un usuario de nivel UE, limitamos por UE
        //Si no, mostramos todas las UE
        $ejecutora = '';
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            //ERROR: De momento, filtramos por UE sólo cuando se trate de un usuario de nivel UE
            if ($this->nvj == 1) {
                $ejecutora = ' AND ue_id = ' . $_SESSION[_S]['clasAdm-ue'] . ' ';
            }
        }

        $sql = '
            SELECT a.ced_id, a.ccd_id,
            CONCAT( a.gpo_id, a.ur_id, "-", a.ue_id, "-", ep.prg_id, "-", ep.spr_id, "-", ep.py_id, "-", ep.oa_id ) AS programatico,
            cf.ecf_clave AS cf,
            ali_beneficiarios,
            IF(g.gbf_id = 9, a.ali_beneficiarioOtros, g.gbf_nombre) AS gbf_nombre, asr_nombre,
            a.mon_ene + a.mon_feb + a.mon_mar + a.mon_abr + a.mon_may + a.mon_jun + a.mon_jul + a.mon_ago + a.mon_sep + a.mon_oct + a.mon_nov + a.mon_dic AS total
            FROM poa_Alimentacion a
            JOIN cat_GrupoBeneficiario g USING(gbf_id)
            JOIN cat_AreaServicio s USING(asr_id)
            JOIN enc_Programatico ep USING(epg_id )
            JOIN enc_ClaveFinanciamiento cf USING(ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            WHERE a.gpo_id = ? and a.ur_id = ? ;';
        $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'] . $ejecutora];
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Conceptos';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".ver").click(function()
            {
                comunica("' . $this->pantalla . '",{
                metodo:"addModCedula",
                ced_id:$(this).attr("ced_id") });
                return false;
            });

            $(document).ready(function()
            {
                $("#tabla-tbl-Conceptos").filterTable({ //apply filterTable to all tables on this page
                    inputSelector: "#filtroTabla" //use the existing input instead of creating a new one
                });
            });

        ');
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Dibujamos la pantalla para registrar una nueva cédula ... y aquí es donde escogeremos la Clasificación Administrativa
    y la Clave de Finaciamiento
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function addModCedula()
    {

        //Si pasaron el número de cédula, es un cambio.. si no, le ponemos 0 como número
        $cedula = (isset($this->prm->ced_id) ? $this->prm->ced_id : 0);

        //Si es una nueva cédula, podrán elegir el encuadre programático y la clave de financiamiento
        if ($cedula == 0) {
            $this->mixHtml(DibujaPantallaBuscarProgramaFinanciamientoHTML());
            $this->mixScript(DibujaPantallaBuscarProgramaFinanciamientoJS());
            $encLectura = '';
            //Si no, sólo podrán ver lo que está registrado en la base de datos
        } else {
            $encLectura = ' disabled ';
        }

        $botones = '
            <button id = "regresar" type="button" class = "btn btn-outline-secondary me-1" title="Regresar al listado de cédulas de alimentación">
                <i class = "fa-solid fa-circle-left me-2"></i>
                Regresar
            </button>';

        //Sólo los usuarios de nivel UE y UR podrán guardar y eliminar
        if ($this->escritura) {
            $botones .= '
                <button id = "guardar" type="button" class = "btn btn-outline-success me-1" title="Guardar nueva cédula de alimentación">
                    <i class = "fa-solid fa-floppy-disk me-2"></i>
                    Guardar
                </button>';
            //Sólo si es un cambio podrán eliminarla
            if ($cedula != 0) {
                $botones .= '
                    <button id = "deleteCedula" type="button" class = "btn btn-outline-danger me-1" title="Eliminar Cédula">
                        <i class = "fa-solid fa-trash me-2"></i>
                        Eliminar
                    </button>';
            }
        }

        //Todos pueden imprimir
        //Si nos pasaron el dato del número de la cédula, es un cambio
        $valorEpg = '';
        $nombreEpg = '';
        $valorCF = '';
        $nombreCF = '';
        $ali = new Alimentacion();
        if ($cedula != 0) {
            $ali->setCcd_id(1); //1 en cat_Cedula es igual a ALIMENTACION
            $ali->setCed_id($this->prm->ced_id);
            $ali->select();

            //Ponemos la clasificación administrativa registrada en la cédula
            $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($ali->getEpg_id());
            $valorEpg = $ali->getGpo_id() . $ali->getUr_id() . '.' . $ali->getUe_id() . '.' . $_SESSION[_S]['clasAdm-prg'] . '.' . $_SESSION[_S]['clasAdm-spr'] . '.' . $_SESSION[_S]['clasAdm-py'] . '.' . $_SESSION[_S]['clasAdm-oa'];
            $_SESSION[_S]['clasAdm-ue'] = $ali->getUe_id();

            //Ponemos la clave de financiamiento registrada en la cédula
            ClasificacionFinanciamiento::PonEnVariablesEcf($ali->getEcf_id());
            $valorCF = $_SESSION[_S]['cvefin']->ter . '-' . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . (isset($_SESSION[_S]['cvefin']->cso) ? $_SESSION[_S]['cvefin']->cso : '') . '.' . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            $nombreCF = $_SESSION[_S]['cvefin']->nombre;

            $abc = 'c';

            $this->mixScript('
                $("#comoSeUsa").click(function(){
                    bootbox.dialog({
                        message: "<h5>Cuando te coloques sobre los campos de <b>Encuadre Programático</b> y <b>Clave de Financiamiento</b> verás que el campo cambia de color a verde.   Esto significa que puedes presionar la tecla <b>F1</b> o <b>F2</b> para seleccionar los encuadres.<br /><br /><b>Método 1</b>:<br/><br/>Si presionas F1 verás la lista de todas las combinaciones de encuadre programático y claves de financiamiento existentes.<br/><br />Selecciona con doble click la que quieras utilizar y se rellenarán ambos cambos (programático y clave de financiamiento).<br /><br /><b>Método 2:</b><br/><br/>Si presionas F2 activarás la seleccion por combos.<br /><br />En éste caso debes colocarte sobre el campo de <b>Encuadre Programático</b> y presionar F2 para seleccionar cada combo del encuadre.<br/><br/>Cuando hayas seleccionado todo el encuadre (hasta Obra/Actividad) cierra la ventana y colócate en el campo de <b>Clave de Financiamiento</b>.   Presiona F2 para mostrar la ventana que te permite seleccionar la Clave de Financiamento.</h5>",
                        title: "Cómo seleccionar encuadres...",
                    });
                });
            ');
            $encuadre = '<fieldset class = "col-md-12 col-xs-12">
                        <legend>Encuadre Programático y Clave de Financiamiento   <span id = "comoSeUsa" style="color:red; cursor: pointer; text-decoration: underline;">¿ cómo se usa ?</span></legend>
                        <label>Encuadre Programático</label>
                        <div class = "row mb-4">
                            <fieldset class = "col-md-3 col-xs-3">
                                <input type="text" value="' . $valorEpg . '" ori="' . $valorEpg . '" enc = "' . $ali->getEpg_id() . '" maxlength="20" ' . $encLectura . ' placeholder="Encuadre (F1/F2)" name="encProg" id = "encProg" class = " form-control F1Catalogo" required = "true">
                            </fieldset>
                            <fieldset class = "col-md-9 col-xs-9">
                                <input type="text" value="' . $nombreEpg . '" disabled placeholder="Nombre Obra/Actividad" name="nomProg" id = "nomProg" class = " form-control">
                            </fieldset>
                        </div>
                        <label>Clave de Financiamiento</label>
                        <div class = "row mb-4">
                            <fieldset class = "col-md-3 col-xs-3">
                                <input type="text" value="' . $valorCF . '" ori="' . $valorCF . '" enc = "' . $ali->getEcf_id() . '" maxlength="12" ' . $encLectura . ' placeholder="Clave (F1/F2)" name="encCveFin" id = "encCveFin" class = " form-control F1Catalogo" required = "true">
                            </fieldset>
                            <fieldset class = "col-md-9 col-xs-9">
                                <input type="text" value="' . $nombreCF . '" disabled placeholder="Nombre de la Clave" name="nomCveFin" id = "nomCveFin" class = " form-control">
                            </fieldset>
                        </div>

                    </fieldset>';
            //Si no, es un alta
        } else {
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $clasAdmin->filtro = 'poa';

            $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));

            $ali->setCed_id(0);
            $abc = 'a';

            $encuadre = CatalogoProgramaticoFinanciamiento::ponF1();
        }

        $botones .= '
            <a id = "imprimir" epg_id = "' . $ali->getEpg_id() . '" ecf_id = "' . $ali->getEcf_id() . '" target="_blank"
                title="Imprimir Cédula de Alimentación" style="padding: 0">
                <button type="button" class = "btn btn-outline-danger me-1" >
                <i class = "fa fa-file-pdf me-2">
                </i>PDF</button>
            </a>
        ';

        $this->agregaTools($botones);

        //cat_GrupoBeneficiario
        $gbf_id = new stdClass();
        $gbf_id->sql = 'select gbf_id,gbf_nombre from  cat_GrupoBeneficiario; ';
        $gbf_id->id = 'gbf_id';
        $gbf_id->name = 'gbf_id';
        $gbf_id->required = true;
        $gbf_id->mensaje = 'Selecciona Grupo Beneficiario';
        $gbf_id->label = 'Grupo beneficiario';
        $gbf_id->selecciona = $ali->getGbf_id();

        //Otro grupo de beneficiarios
        $beneficiarioOtros = new stdClass();
        $beneficiarioOtros->id = 'beneficiarioOtros';
        $beneficiarioOtros->name = 'beneficiarioOtros';
        $beneficiarioOtros->label = 'Especificar grupo beneficiario';
        $beneficiarioOtros->placeholder = 'Especificar grupo beneficiario';
        $beneficiarioOtros->required = true;
        $beneficiarioOtros->enDiv = true;
        $beneficiarioOtros->value = $ali->getBeneficiarioOtros();

        //Area en la que se presta el servicio ::::: cat_AreaServicio
        $asr_id = new stdClass();
        $asr_id->sql = 'select asr_id,asr_nombre from cat_AreaServicio ;';
        $asr_id->id = 'asr_id';
        $asr_id->name = 'asr_id';
        $asr_id->required = true;
        $asr_id->mensaje = 'Selecciona Area';
        $asr_id->label = '&Aacute;rea en la que se presta el servicio';
        $asr_id->selecciona = $ali->getAsr_id();

        //Número de beneficiarios
        $beneficiarios = new stdClass();
        $beneficiarios->id = 'beneficiarios';
        $beneficiarios->name = 'beneficiarios';
        $beneficiarios->label = 'No. Beneficiarios';
        $beneficiarios->placeholder = 'Número';
        $beneficiarios->style = 'text-align:right';
        $beneficiarios->maxLength = 15;
        $beneficiarios->required = true;
        $beneficiarios->enDiv = true;
        $beneficiarios->formato = 'entero';
        $beneficiarios->width = '200px';
        $beneficiarios->value = $ali->getBeneficiarios();

        //Cuota diaria
        $cuotaDiaria = new stdClass();
        $cuotaDiaria->id = 'cuotaDiaria';
        $cuotaDiaria->name = 'cuotaDiaria';
        $cuotaDiaria->label = 'Cuota diaria';
        $cuotaDiaria->placeholder = 'Cuota diaria';
        $cuotaDiaria->maxLength = 15;
        $cuotaDiaria->style = 'text-align:right';
        $cuotaDiaria->required = true;
        $cuotaDiaria->enDiv = true;
        $cuotaDiaria->formato = 'moneda';
        $cuotaDiaria->value = $ali->getCuotaDiaria();

        //Número de dias anual que se presta el servicio
        $diasOtorgados = new stdClass();
        $diasOtorgados->id = 'diasOtorgados';
        $diasOtorgados->name = 'diasOtorgados';
        $diasOtorgados->label = 'D&iacute;as al año que se otorga';
        $diasOtorgados->placeholder = 'Días al año';
        $diasOtorgados->maxLength = 3;
        $diasOtorgados->style = 'text-align:right';
        $diasOtorgados->required = true;
        $diasOtorgados->enDiv = true;
        $diasOtorgados->formato = 'entero';
        $diasOtorgados->maxlength = '3';
        $diasOtorgados->value = $ali->getDiasOtorgados();

        $objGto = new stdClass();
        $objGto->ccd_id = 1;
        $objGto->cve_id = 1;
        $objGto->filtro = 'costeo';
        if ($cedula != 0) {
            $objGto->lectura = true;
            $objGto->pg_id = $ali->getPg_id();
            $objGto->pe_id = $ali->getPe_id();
        }

        $cveFin = new stdClass();
        $cveFin->horizontal = 3;
        $cveFin->lectura = true;
        $cveFin->ecf_id = $ali->getEcf_id();

        $this->mixHtml('
            <form role="form" id = "frmAlimentacion" name="frmAlimentacion" action = "' . $this->pantalla . '">
                <div class = "row mb-4">
                    ' . $encuadre . '
                </div>

                <div class = "row mb-4">
                    <fieldset class = "col-md-12 col-xs-12">
                    ' . ObjetoGasto::ponObjetoGasto($objGto) . '
                    </fieldset>
                </div>

                <fieldset class = "col-md-12">
                    <legend>Informaci&oacute;n del registro
                    <span style="float: right;">
                    <input width=50
                    id = "saldoDisponible"
                    style="display: inline; width:300px; height: 30px; text-align:right;"
                    class = "form-control poaSaldoDisponible"
                    tabindex = "-1"
                    placeholder="Techo Presupuestal Disponible" />
                    </span>
                    </legend>

                    <div class = "row mb-4">
                    <fieldset class = "col-md-4 col-xs-4">
                    ' . $this->ponCombo($asr_id) . '
                    </fieldset>
                    <fieldset class = "col-md-4 col-xs-4">
                    ' . $this->ponCombo($gbf_id) . '
                    </fieldset>
                    <fieldset class = "col-md-4 col-xs-4">
                    ' . $this->ponInput($beneficiarioOtros) . '
                    </fieldset>
                    </div>

                    <div class = "row mb-4">
                    <fieldset class = "col-md-2 col-xs-2">
                    ' . $this->ponInput($beneficiarios) . '
                    </fieldset>
                    <fieldset class = "col-md-2 col-xs-2">
                    ' . $this->ponInput($cuotaDiaria) . '
                    </fieldset>
                    <fieldset class = "col-md-4 col-xs-4">
                    ' . $this->ponInput($diasOtorgados) . '
                    </fieldset>
                    </div>
                </fieldset>

                <Calendario id = "alimentacion"></Calendario> ');

        $this->mixHtml(
            '
            </div>
            </form>
            <div id = "contenedorCFModal"></div>'
        );

        $ced_id = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? $this->prm->ced_id : '';

        $this->mixScript('
            function validarPresupuesto() {
                // Obtener valores del DOM
                let saldo = $("#saldoDisponible").val();
                let total = $(".totm").val();

                // Limpiar y convertir los valores a número
                let totalCosteo = parseFloat(total.replace("$", "").replace(/\s/g, "").replace(/,/g, ""));
                let saldoDisponible = parseFloat(saldo.replace(/[^0-9.]/g, ""));

                // Validar si se excede el techo presupuestal
                if (totalCosteo > saldoDisponible) {
                    notify("Se excede el techo presupuestal", "warning");
                    return false;
                }

                // Todo OK
                return true;
            }
        ');

        $this->mixScript('
            $("#beneficiarioOtros").parent().hide();
            $("#gbf_id").change(function () {
                switch ($(this).val()) {
                    case \'9\': $("#beneficiarioOtros").parent().show(); $("#beneficiarioOtros").removeAttr("disabled"); break;
                    default: $("#beneficiarioOtros").parent().hide(); $("#beneficiarioOtros").attr("disabled", "disabled"); break;
                }
            });

            $("#gbf_id").trigger("change");

            $("#beneficiarios,#cuotaDiaria").blur(function () {
                if (unformatNumber($(this).val()) < 0) {
                    $(this).val("0");
                    $(this).blur();
                }
                $("#alimentacion").calendario({ unitario: ($("#beneficiarios").val() * unformatNumber($("#cuotaDiaria").val())) });
            });

            $("#diasOtorgados").blur(function () {
                if (unformatNumber($(this).val()) <= 0) {
                    $("#alimentacion").calendario({ unidades: [{ "mes": "ene", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "feb", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "mar", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "abr", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "may", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "jun", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "jul", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "ago", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "sep", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "oct", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "nov", "value": 0 }] });
                    $("#alimentacion").calendario({ unidades: [{ "mes": "dic", "value": 0 }] });
                }
            });

            $("#alimentacion").calendario({
                unidades: ' . json_encode($ali->getUnidades()->getArrayMeses()) . ',
                montos: ' . json_encode($ali->getMontos()->getArrayMeses()) . ',
                unitario: ' . ($ali->getCuotaDiaria() * $ali->getBeneficiarios()) . ',
                etiquetaUni: \'Num. de dias\'
                });

            $(".mes.mesu").blur(function () {
                mes_indice = { "ene": 31, "feb": 28, "mar": 31, "abr": 30, "may": 31, "jun": 30, "jul": 31, "ago": 31, "sep": 30, "oct": 31, "nov": 30, "dic": 31 };
                var mes = $(this).attr("mes");
                var valor = mes_indice[$(this).attr("mes")];
                if (unformatNumber($(this).val()) > mes_indice[$(this).attr("mes")])
                    $("#alimentacion").calendario({ unidades: [{ "mes": mes, "value": valor }] });
                if (unformatNumber($(this).val()) < 0)
                    $("#alimentacion").calendario({ unidades: [{ "mes": mes, "value": 0 }] });
            });

            $(".mes.mesu").keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });

            Mousetrap.bind("esc esc", function (e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function (e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                Mousetrap.unbind("ctrl+enter");
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            //Atajos de ésta pantalla
            Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); $("#guardar").click(); });

            $("#regresar").click(function(e){
                //Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            $("#guardar").click(function () {
                var obj = { "metodo": "abcCedula" };
                obj.abc = "' . $abc . '";
                obj.ced_id = "' . $ced_id . '";
                obj.ccd_id = "1";
                obj.ecf_id = $("#encCveFin").attr("enc");
                obj.gbf_id = $("#gbf_id").val();
                if (obj.gbf_id == 9) obj.beneficiarioOtros = $("#beneficiarioOtros").val();
                obj.asr_id = $("#asr_id").val();
                obj.beneficiarios = $("#beneficiarios").val();
                obj.cuotaDiaria = unformatNumber($("#cuotaDiaria").val());
                obj.diasOtorgados = $("#diasOtorgados").val();
                obj.montos = $("#alimentacion").data("wr-calendario").options.montos;
                obj.unidades = $("#alimentacion").data("wr-calendario").options.unidades;
                obj.pg_id = $("#pg_id").val();
                obj.pe_id = $("#pe_id").val();

                //Sumamos los días que definieron en el calendario...
                sumDias = 0;
                $.each( obj.unidades, function() { sumDias += parseFloat( this ) || 0; } );

                //Marcos: 06/oct/2017
                //Validamos el total de días otorgados con el total de días definidos mes con mes
                //Si NO coinciden los días otorgados con la suma de días definidos en el calendario, pos no lo grabamos
                if ( obj.diasOtorgados != sumDias ) {
                    notify( "Los días otorgados DEBEN coincidir con los días definidos en el calendario", "error");
                } else {
                    //Marcos: 26/08/2016
                    //En el 2017 el acceso a datos regresa un JSON cuando ocurra un error, así que ésta
                    //función no aplica.   se sustituye por ejecutar el comunica y ejecutar inmediatamente
                    //después el removeattr para disabled.   De lo contrario al marcar un error de base de
                    //datos se deshabilitaba el botón de guardar y se quedaba deshabilitado
                    if (!validarPresupuesto()) {
                    e.preventDefault(); // Detiene el envío
                    }
                    comunica("' . $this->pantalla . '", obj, true );
                }

                //validando los campos de encuadre y clave de financiamiento
                if ($("#nomProg").val() == "") {
                    notify("Debe seleccionar un encuadre programático", "error");
                }
                if ($("#nomCveFin").val() == "") {
                    notify("Debe seleccionar una clave de financiamiento", "error");
                }
                if ($("#beneficiarios").val() == "") {
                    document.getElementById("beneficiarios").style.border = "#F00 1px solid";
                }
                if ($("#cuotaDiaria").val() == "") {
                    document.getElementById("cuotaDiaria").style.border = "#F00 1px solid";
                }
                if ($("#diasOtorgados").val() == "") {
                    document.getElementById("diasOtorgados").style.border = "#F00 1px solid";
                }
                $(this).removeAttr("disabled");
                return false;
            });

        $("#saldoDisponible").keydown(false);

        $("#encProg, #encCveFin, button[data-id=pg_id], button[data-id=pe_id]").blur(function () {
            comunica("lib/ControladorFunciones.php", {
                metodo: "muestraDisponible",
                ecf: $("#encCveFin").attr("enc"),
                pg: $("#pg_id").val(),
                pe: $("#pe_id").val()
            });
        });

        $("#SitemaActivo").hide();
        setTimeout(function () { $("#encProg").focus() }, 100);

        $("#imprimir").click(function () {
            epg_id = $(this).attr("epg_id");
            ecf_id = $(this).attr("ecf_id");
            var form = $("<form>")
                .attr("action", "rep/doc/poa/rep_Cedula.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", ' . $objGto->ccd_id . '))
            $("body").append(form);
            form.submit();
            form.remove();
        });
        ');

        $this->mixScript("$('#deleteCedula').click(function(){
                    alertify.confirm('ELIMINAR CÉDULA DE ALIMENTACIÓN','¿ Confirma que desea eliminar la cédula <h5 style=\"display:inline\"></h5> ?',
                    function(){ comunica('" . $this->pantalla . "',{
                                        metodo: 'abcCedula',
                                        abc:'b',
                                        'ced_id':'" . ($ali->getCed_id()) . "',
                                        'ccd_id':'1'
                                    });  }
                , function(){ }

                    ).set('labels', {ok:'Eliminar', cancel:'Cancelar'});
                });");

        if ($cedula != 0) {
            $this->mixScript('
                $("#encProg").blur();
            ');
        }
    }

    public function abcCedula()
    {
        $cveFin = new stdClass();

        switch ($this->prm->abc) {
            case 'a':
                $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
                $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
                $this->prm->ue_id = recuperarUe(ClasificacionAdministrativa::getEpg());
                $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
                $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
                if ($this->prm->gbf_id != 9) {
                    $this->prm->beneficiarioOtros = '';
                }
                Alimentacion::abc($this->prm);
                $this->mixScript('
                    notify("Cédula de Alimentación insertada exitosamente");
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                ');

                break;
            case 'c':
                $cveFin->ecf_id = ClasificacionFinanciamiento::getEcf();
                if ($this->prm->gbf_id != 9) {
                    $this->prm->beneficiarioOtros = '';
                }
                Alimentacion::abc($this->prm);
                $this->mixScript('
                    notify("Cédula de Alimentación actualizada exitosamente");
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                ');

                break;
            case 'b':
                $cveFin->ecf_id = ClasificacionFinanciamiento::getEcf();
                $this->prm->ced_id;
                Alimentacion::abc($this->prm);
                $this->mixScript('
                    notify("Cédula de Alimentación eliminada exitosamente");
                    //Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                ');
                break;
        }
    }
}

if (isset($_SESSION[_S]['prm']['A']['3']['1']['1'])) {
    new ControladorAlimentacion();
} else {
    WebResponse::sinPermisos();
}
