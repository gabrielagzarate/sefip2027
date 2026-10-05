<?php

/**
 * Description of ControladorVehiculos
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se estandarizan estilos botones : regresar, imprimir, guardar
 * Objective: Consultar y dar de alta nuevas cedulas de Vehiculos por Unidad Responsable
Cargar Vehiculos 2018 a 2019 a partir del Enc. Programático y Clave de Financiamiento.
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 *@update Jordy Castro
 *Created: 05-06-2019
 *Objective: Consultar y dar de alta nuevas cedulas de Vehiculos por Unidad Responsable
 *Cargar Vehiculos 2019 a 2020 a partir del Enc. Programático y Clave de Financiamiento.
 * */

//Modo estricto
declare(strict_types=1);

//inicio de sesion y llamada a las librerias
session_start();
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../ent/poa/Vehiculos/ParqueVehicular2026.php';
require_once __DIR__ . '/../../ent/poa/Vehiculos/ParqueVehicular2027.php';
require_once __DIR__ . '/../../ent/poa/Vehiculos/VehiculoMaquina.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';
require_once __DIR__ . '/../../srv/ClasificacionFinanciamiento.php';
require_once __DIR__ . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once __DIR__ . '/../../srv/ObjetoGasto.php';
require_once __DIR__ . '/../../lib/recuperarUe.php';

class ControladorVehiculos extends WebResponse
{
    public $nvj;
    public $ue;
    public $usr_id;
    public $ur;
    public $cierre;
    public $estadoCosteo;
    public $escritura;
    public $lectura;
    public $masiva;
    public $usrNvj;
    public $clasAdmUe;
    public $clasAdmGpo;
    public $clasAdmUr;
    public $clasAdmPrg;
    public $clasAdmSpr;
    public $clasAdmPy;
    public $clasAdmOa;
    public $claveFinanciamiento;
    public $parque2026;
    public $parque2027;
    public $vehiculo;
    public $parque2025;
    public $parqueVehicular;
    public $metodo;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Instanciando la Ur para verificar lo del cierre del poa

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->cierre = $this->ur->getEur_id(); //modificar cuando se suba a produccion.
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo(); // 1 = Abierto ; 2 = Cerrado

        // Asignación de permisos de lectura y escritura
        if (isset($_SESSION[_S]['prm']['A']['3']['4'][TAREA_ESCRITURA]) && ($_SESSION[_S]['nvj_id'] == 1 ||
            $_SESSION[_S]['nvj_id'] == 2) && ($this->cierre == 4 && $this->estadoCosteo == 1)) {
            $this->escritura = true; //si tiene permisos de escritura
        } else {
            $this->escritura = false; //si no tiene permisos de escritura
        }

        if (isset($_SESSION[_S]['prm']['A']['3']['4'][TAREA_LECTURA])) {
            $this->lectura = true; //si tiene permisos de lectura
        } else {
            $this->lectura = false; //si no tiene permisos de lectura
        }

        if (isset($_SESSION[_S]['prm']['A']['3']['4']['23'])) {
            $this->masiva = true; //si tiene permisos de carga masiva
        } else {
            $this->masiva = false; //si no tiene permisos de carga masiva
        }

        //05-06-2019 Variables del usuario
        $this->usrNvj = $_SESSION[_S]['nvj_id'];

        //05-06-2019 Variables de la calsifiacion administrativa
        $this->clasAdmUe = isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '' ? $_SESSION[_S]['clasAdm-ue'] : '';
        $this->clasAdmGpo = isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '' ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->clasAdmUr = isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '' ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->clasAdmPrg = isset($_SESSION[_S]['clasAdm-prg']) && $_SESSION[_S]['clasAdm-prg'] != '' ? $_SESSION[_S]['clasAdm-prg'] : '';
        $this->clasAdmSpr = isset($_SESSION[_S]['clasAdm-spr']) && $_SESSION[_S]['clasAdm-spr'] != '' ? $_SESSION[_S]['clasAdm-spr'] : '';
        $this->clasAdmPy = isset($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] != '' ? $_SESSION[_S]['clasAdm-py'] : '';
        $this->clasAdmOa = isset($_SESSION[_S]['clasAdm-oa']) && $_SESSION[_S]['clasAdm-oa'] != '' ? $_SESSION[_S]['clasAdm-oa'] : '';

        //05-06-2019 Variable de clave de financiamineto
        $this->claveFinanciamiento = isset($_SESSION[_S]['cvefin']->ecf) ? $_SESSION[_S]['cvefin']->ecf : null;

        switch ($this->metodo) {
            case 'cargarVehiculo':
                $this->cargarVehiculo();
                break;
            // 11-06-2019 ----- INICIO -----
            // Se tiene que dar sentido a los anios que corresponden para la migracion correspondiente al ejercicio
            // con el anio anterior y el anio presente en los nombres de los metodos
            // el ejercicio prestente - 2020, ejercicio anterior - 2019
            case 'llenaTablaVehiculos2026':
                $this->llenaTablaVehiculos2026(); //anio de ejercicio anterior
                break;
            case 'tablaVehiculos2027':
                $this->tablaVehiculos2027(); //anio del presente ejercicio
                break;
            case 'llenatablaVehiculos2027':
                $this->llenatablaVehiculos2027(); //anio del presente ejercicio
                break;
            case 'addModVehiculos2027':
                $this->addModVehiculos2027(); //anio del presente ejercicio
                break;
            case 'guardarVehiculo2026': //anio de ejercicio anterior
                $this->guardarVehiculo2026(); //anio de ejercicio anterior
                $this->llenaTablaVehiculos2026(); //anio de ejercicio anterior
                $this->llenatablaVehiculos2027(); //anio del presente ejercicio
                break;
            case 'tipoVehiculoIngreso':
                $this->tipoVehiculoIngreso();
                break;
            case 'confirmaIngreso':
                $this->confirmaIngreso();
                break;
            case 'guardarVehiculo2027':
                $this->guardarVehiculo2027(); //anio del presente ejercicio
                break;
            case 'eliminarVehiculo2027':
                $this->eliminarVehiculo2027(); //anio del presente ejercicio
                break;
            // 11-06-2019 -------- FIN ----
            case 'menuCosteo':
                $this->menuCosteo();
                break;
            case 'determinaCosteo':
                $this->determinaCosteo();
                break;
            case 'menuMantenimiento':
                $this->menuMantenimiento();
                break;
            case 'abcCedula':
                $this->abcCedula();
                break;
            default:
                $this->mainVehiculos();
                break;
        }
        $this->returnJson();
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function mainVehiculos()
    {
        $this->mixStyle('
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:black; opacity: 0.8;};
        ');

        // Cuando cambie la ur, refrescamos la pantalla
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {
                    "metodo":""
                });
            ')
        );

        // Si la planeación NO está autorizada, no le permitimos modificar nada
        if ($this->cierre < 4) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>La Planeación NO ha sido autorizada. No se permite costear</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            ');
        }
        // Si el costeo está cerrado, avisamos
        if ($this->estadoCosteo == 2) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            ');
        }

        // En la pantalla principal, puede "cargar" vehículos 2016 al 2020 indicando el encuadre programático y fuente de financiamiento
        $botones = '
            <button id="cargarVehiculo" type="button" class=" btn btn-outline-primary cargar me-1" title="Cargar vehículo del parque vehicular 2027">
                <i class="fa-solid fa-paper-plane me-2"></i>Cargar Vehículos
            </button>';

        // Si tiene derechos de escritura, puede registrar una nueva cédula de Vehiculos
        if ($this->escritura) {
            $botones .= '
                <button id="btnNuevoVehiculo" type="button" class="btn btn-outline-primary btnNuevoVehiculo me-1" title="Registrar nuevo Vehículo al Parque Vehicular 2027">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo
                </button>';
        } elseif ($this->lectura) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>Solo tiene permiso de lectura</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            ');
        }

        $botones .= '
            <a id="imprimirTodo" target="_blank" class="btn btn-test me-1" title="Imprimir Cédula de Vehículo" firma="0" style="padding: 0"><button type="button" class="btn btn-outline-danger me-1" >
                <i class="fa fa-file-pdf me-2"></i>PDF</button>
            </a>
            <input type="search" width=50 id="filtroTabla" style="display: inline; width:250px; height: 30px;" class="form-control me-1"  placeholder="Filtrar Vehículos por página" />';

        if ($this->masiva) {
            $botones .= '
                <button id="btnMasiva" type="button" class="btn btn-outline-primary btnNuevoVehiculo me-1" title="Carga Masiva">
                    <i class="fa-solid fa-paper-plane me-2"></i>Carga Masiva
                </button>';
        }

        $this->agregaTools($botones);

        // Dibujamos la tabla con la información de las cédulas de Vehículos registradas por la UR actual
        $tabla = new stdClass();
        $tabla->id = 'tbl-mainVehiculos';
        $tabla->cabeceraFija = true;
        $tabla->titulo = 'Vehículos Registrados ' . APP_EJERCICIO;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablamainVehiculos';

        $tr = [];

        // Datos del Programa, Subprograma, Proyecto, Obra/Actividad
        $th = new stdClass();
        $th->texto = 'Programático';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Obra / Actividad';
        array_push($tr, $th);

        // Datos de la Clave de Financiamiento
        $th = new stdClass();
        $th->texto = 'Clave Financiamiento';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de la Clave de Financiamiento';
        array_push($tr, $th);

        //Total
        $th = new stdClass();
        $th->texto = 'Vehículos';
        $th->align = 'right';
        array_push($tr, $th);

        $trbody = [];

        // Datos del Programa, Subprograma, Proyecto, Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'programatico';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);

        // Datos de la Clave de Financiamiento
        $td = new stdClass();
        $td->campoTxt = 'cf';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_nombre';
        array_push($trbody, $td);

        //Total
        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->tipo = 'botonSmall';
        $td->class = 'btn btn-outline-secondary totales';
        $td->align = 'right';
        $td->title = 'Vehículos asignados a éste encuadre';
        $td->attr = new stdClass();
        $td->attr->epg_id = 'epg_id';
        $td->attr->ecf_id = 'ecf_id';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablamainVehiculos();

        $this->mixScript('
            // Nombres de campo necesarios para la pantalla de selección de encuadres
                    var progID = "encProg";
        var progNom = "nomProg";
        var cveFID = "encCveFin";
        var cveFNom = "nomCveFin";

        $("#techos").show();
        DesactivaAtajosMenu();
        Mousetrap.bind("esc esc", function (e) {
            e.preventDefault();
            $("#homeMenu").click();
            e.stopPropagation();
        });

        // Quitamos la info de una paginación que no usaremos en ésta pantalla
        $(".panel-heading.header").remove();
        $(".panel-footer").remove();

        $("#homeMenu").click(function (e) {
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            ActivaAtajosMenu();
            location.reload();
        });

        // Cargamos vehículo del 2027
        $("#cargarVehiculo").click(function (e) {
            e.preventDefault();
            console.log("Cargar Vehiculos 2027");
            comunica("' . $this->pantalla . '", {
                metodo: "cargarVehiculo"
            });
            e.stopPropagation();
        });

        // Mostramos los vehículos del encuadre actual
        $(".totales").click(function (e) {
            e.preventDefault();
            console.log("Cargar Vehiculos 2027");
            comunica("' . $this->pantalla . '", {
                metodo: "tablaVehiculos2027",
                epg_id: $(this).attr("epg_id"),
                ecf_id: $(this).attr("ecf_id")
            });
            e.stopPropagation();
        });

        //para agregar un nuevo vehiculo
        $("#btnNuevoVehiculo").click(function () {
            console.log("Agregar un Vehiculo");
            comunica("' . $this->pantalla . '", {
                metodo: "addModVehiculos2027",
                pqv_id: ""
            });
            return false;
        });

        //para agregar un nuevo vehiculo
        $("#btnMasiva").click(function () {
            console.log("Carga Masiva");
            comunica("poa/ControladorVehiculoCarga.php", {
                metodo: "main"
            });
            return false;
        });

        $("#imprimirTodo").click(function () {
            epg_id = $(this).attr("epg_id");
            ecf_id = $(this).attr("ecf_id");
            firma = $(this).attr("firma");

            var form = $("<form>")
                .attr("action", "rep/doc/poa/rep_Cedula.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 4))
                .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

            $("body").append(form);
            form.submit();
            form.remove();

        });

        $("#cmC_PV").click(function () {
            comunica("poa/ControladorCargaMasivaParqueVehicular2020.php");
        });
        $("#cmC_CV").click(function () {
            comunica("poa/ControladorCargaMasivaCedulaVehiculos.php");
        });

        setTimeout(function () {
            $("#filtroTabla").focus()
        },
            300
        );

        $(document).ready(function () {
            $("table").filterTable({ // apply filterTable to all tables on this page
                inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
            });
        });
        ');
    }
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Llenamos la tabla con los vehículos registrados por UR, encuadre programático y clave de financiamiento
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function llenaTablamainVehiculos()
    {
        $parametros = [];
        $filtroUe = '';
        if ($this->usrNvj == 1) {
            $filtroUe = ' AND a.ue_id = :ue';
            $parametros += [
                'ue' => $this->clasAdmUe,
            ];
        }

        $sql = "
            SELECT
                epg_id,
                ecf_id,
                CONCAT( a.gpo_id, a.ur_id, '.', a.ue_id, '.', ep.prg_id, '.', ep.spr_id, '.', ep.py_id, '.', ep.oa_id ) AS programatico,
                oa_nombre,
                CONCAT( cf.cff_id , '-', cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, '-', cf.cfi_id, cf.cfi_anio ) AS cf,
                cfi_nombre,
                COUNT(*) AS total
            FROM cat_ParqueVehicular a
                INNER JOIN enc_Programatico ep USING( epg_id )
                INNER JOIN enc_ClaveFinanciamiento cf USING( ecf_id )
                INNER JOIN cat_ObraActividad oa ON ( ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id )
                INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . " so USING( cff_id, fte_id, sft_id, ori_id, sor_id )
            WHERE a.gpo_id = :gpo
                AND a.ur_id = :ur
                $filtroUe
            GROUP BY programatico, cf, epg_id, ecf_id, cfi_nombre
            ORDER BY programatico, cf
        ";
        $parametros += [
            'gpo' => $this->clasAdmGpo,
            'ur'  => $this->clasAdmUr,
        ];

        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-mainVehiculos';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        if ($this->wrLlenaTabla($nav) > 50) {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").show();
                $(".panel-footer").show();
            ');
        } else {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").hide();
                $(".panel-footer").hide();
            ');
        }
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Mostramos el parque vehicular 2018, para cargar un vehículo indicando encuadre programático y clave de financiamiento
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function cargarVehiculo()
    {
        $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";
            $("#viewer_extra").append(
                $("#clasAdminSeccionCombos")
            );
            $("#close_jma_viewer, #addcompromisoclose_jma_viewer").click(function(){
                $(".cabecera").append($("#clasAdminSeccionCombos"))
                limpiaClasAdmin();
            });
        ');

        $valorEpg = isset($this->prm->prog) ? $this->prm->prog : '';
        $nombreEpg = isset($this->prm->npro) ? $this->prm->npro : '';
        $valorCF = isset($this->prm->cveF) ? $this->prm->cveF : '';
        $nombreCF = isset($this->prm->ncve) ? $this->prm->ncve : '';
        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin());
        $this->mixHtml(CatalogoProgramaticoFinanciamiento::ponF1());

        $botones = '
            <button id="regresar" type="button" class="btn btn-outline-secondary me-1" title="Regresar al listado principal">
                <i class="fa-solid fa-circle-left me-2"></i>Regresar
            </button>';
        $this->agregaTools($botones);

        $botones = '<input type="search" width=50 id="filtroTabla" style="display: inline; width:250px; height: 30px;" class="form-control"  placeholder="Filtrar Vehículos por página" /> ';

        $this->agregaTools($botones);



        $tabla = new stdClass();
        $tabla->id = 'tbl-Vehiculos16';
        $tabla->cabeceraFija = true;
        $tabla->titulo = 'Parque Vehicular ' . APP_EJERCICIO;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaVehiculos2026'; // 18-06-2019 es para el anio anterior del ejercicio actual

        $tr = [];

        $th = new stdClass();
        $th->texto = 'Tipo';
        $th->width = '10%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Uso';
        $th->width = '10%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Ubicaci&oacute;n';
        $th->width = '15%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Placas';
        $th->width = '10%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Modelo';
        $th->width = '7%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. de Serie';
        $th->width = '20%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. de Cilindros';
        $th->width = '10%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Ingresar ' . APP_EJERCICIO;
        $th->width = '10%';
        $th->align = 'right';
        array_push($tr, $th);

        $trbody = [];

        //contenido tipo
        $td = new stdClass();
        $td->campoTxt = 'pqv_tipo';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido uso
        $td = new stdClass();
        $td->campoTxt = 'usv_nombre';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido ubicacion
        $td = new stdClass();
        $td->campoTxt = 'ubv_nombre';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido placas
        $td = new stdClass();
        $td->campoTxt = 'pqv_numeroPlacas';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido modelo
        $td = new stdClass();
        $td->campoTxt = 'pqv_modelo';
        $td->align = 'right';
        array_push($trbody, $td);
        //contenido numero de serie
        $td = new stdClass();
        $td->campoTxt = 'pqv_id';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido numero de cilindros
        $td = new stdClass();
        $td->campoTxt = 'civ_nombre';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido ingresar2019
        if ($this->escritura) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'fa-solid fa-paper-plane';
            $td->class = 'cargarDesde16';
            $td->name = 'pqv_id';
            $td->id = 'pqv_id';
            $td->color = 'verde';
            $td->attr = new stdClass();
            $td->attr->pqv_tipo = 'pqv_tipo';
            $td->attr->usv_nombre = 'usv_id';
            $td->attr->ubv_nombre = 'ubv_id';
            $td->attr->pqv_numeroPlacas = 'pqv_numeroPlacas';
            $td->attr->pqv_modelo = 'pqv_modelo';
            $td->attr->pqv_id = 'pqv_id';
            $td->attr->civ_nombre = 'civ_id';
            array_push($trbody, $td);
        } else {
            $td = new stdClass();
            $td->campoTxt = 'guion';
            array_push($trbody, $td);
        }

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaVehiculos2026(); // 18-06-2019 para el anio anterior del ejercicio actual

        $this->mixScript('

            $("#encCveFin").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;

                /* F1 llama a la pantalla alternar para escoger encuadres */
                if ( key == 112 ) {
                    e.preventDefault();
                    dlgC.dialog("open");
                }else if( key == 113 ) {/* F2 llama a la pantalla modal para escoger clave de financiamiento */
                    // Solo seleccionamos éste encuadre cuando YA exista el programatico
                    if ( $("#encProg").val() != "" ) {
                        e.preventDefault();
                        console.log( $("#encProg").val() );
                        comunica( "srv/ClasificacionFinanciamiento.php", {
                            metodoCveFin: "muestraCveFin"
                        });

                    } else {
                        notify("Debes seleccionar primero el Encuadre Programático", "warning");
                        setTimeout( function() { $( "#encProg" ).focus() }, 100 );
                    }
                } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                    notify("F1 para mostrar catálogo y doble click para seleccionar, o F2 para combos", "warning");
                    e.preventDefault();
                    return false;
                }
            });

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#homeMenu").click();
                    e.stopPropagation();
                });
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            $("#regresar").click(function(e){
                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            $("#comoSeUsaa").click(function(){
                alertify.alert("Cómo seleccionar encuadres...", "<h5>Cuando te coloques sobre los campos de <b>Encuadre Programático</b> y <b>Clave de Financiamiento</b> verás que el campo cambia de color a verde.   Esto significa que puedes presionar la tecla <b>F1</b> o <b>F2</b> para seleccionar los encuadres.<br /><br /><b>Método 1</b>:<br/><br/>Si presionas F1 verás la lista de todas las combinaciones de encuadre programático y claves de financiamiento existentes.<br/><br />Selecciona con doble click la que quieras utilizar y se rellenarán ambos cambos (programático y clave de financiamiento).<br /><br /><b>Método 2:</b><br/><br/>Si presionas F2 activarás la seleccion por combos.<br /><br />En éste caso debes colocarte sobre el campo de <b>Encuadre Programático</b> y presionar F2 para seleccionar cada combo del encuadre.<br/><br/>Cuando hayas seleccionado todo el encuadre (hasta Obra/Actividad) cierra la ventana y colócate en el campo de <b>Clave de Financiamiento</b>.   Presiona F2 para mostrar la ventana que te permite seleccionar la Clave de Financiamento.</h5>"
                );
            });
        ');
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Cargamos el parque vehicular 2020, para permitirle asignarlo en el 2020
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function llenaTablaVehiculos2026()
    { // 18-06-2019 metodo para anio anterior
        $sql = "
            SELECT
                '-' AS guion,
                usv_nombre,
                ubv_nombre,
                civ_nombre,
                pqv_id,
                gpo_id,
                ur_id,
                pqv_tipo,
                pqv_modelo,
                pqv_numeroPlacas,
                civ_id,
                ubv_id,
                usv_id,
                sts_id
            FROM cat_ParqueVehicularAnterior
                LEFT JOIN cat_UsoVehiculo USING (usv_id)
                LEFT JOIN cat_UbicacionVehiculo USING (ubv_id)
                LEFT JOIN cat_CilindrosVehiculo USING (civ_id)
            WHERE gpo_id = :gpo
                AND ur_id = :ur
                AND sts_id = 1
            ORDER BY pqv_tipo, usv_nombre, ubv_nombre, pqv_id, pqv_numeroPlacas
        ";

        $parametros = [
            'gpo' => $this->clasAdmGpo,
            'ur'  => $this->clasAdmUr,
        ];
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Vehiculos16';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;

        if ($this->wrLlenaTabla($nav) > 50) {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").show();
                $(".panel-footer").show();
            ');
        } else {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").hide();
                $(".panel-footer").hide();
            ');
        }

        $this->mixScript('
            $(".cargarDesde16").click(function(){
                // Revisamos que tenga ya un encuadre seleccionado
                if ( $("#encProg").val().trim() != "" && $("#encCveFin").val().trim() != "" ) {
                    // Y que no lo hayan cambiado manualmente
                    if ( $("#encProg").val().trim() == $("#encProg").attr("ori").trim() && $("#encCveFin").val().trim() == $("#encCveFin").attr("ori").trim() ) {
                        // si llega hasta aquí, es que hay un encuadre válido y no ha sido modificado manualmente
                        // así que continuamos ...
                        console.log( $("#encProg").val().trim() );
                    } else {
                        notify("No puedes modificar manualmente el encuadre ni la clave de financiamiento", "warning");
                        return false;
                    }
                } else {
                    notify("Selecciona Encuadre y Clave de Financiamiento ' . APP_EJERCICIO_ANTERIOR . ' donde se cargará", "warning");
                    return false;
                }
                comunica( "' . $this->pantalla . '", {
                    metodo:"tipoVehiculoIngreso",
                    tipo:$(this).attr("pqv_tipo"),
                    usv_id:$(this).attr("usv_nombre"),
                    ubv_id:$(this).attr("ubv_nombre"),
                    numeroPlacas:$(this).attr("pqv_numeroPlacas"),
                    modelo:$(this).attr("pqv_modelo"),
                    pqv_id:$(this).attr("pqv_id"),
                    civ_id:$(this).attr("civ_nombre")
                });
                return false;
            });

            $(document).ready(function() {
                $("table").filterTable({ // apply filterTable to all tables on this page
                    inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
                });
            });

            // Ocultamos el encuadre programático
            $("#SitemaActivo").hide();
            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
        ');
    }

    public function tipoVehiculoIngreso()
    {
        $this->mixScript('
                const contenido = `
                    Seleccione el tipo de vehículo para ingresar al 2027:
                    <form id="formOpciones">
                        <br>
                        <label><input type="radio" name="motivo" value="1" checked>Patrimonio del Estado</label><br>
                        <label><input type="radio" name="motivo" value="2">Arrendado</label>
                `;
                alertify.confirm("SELECCIONAR TIPO DE VEHÍCULO", contenido,
                    function(){
                        // Obtener el valor del radio button seleccionado
                        var motivoSeleccionado = $(`input[name="motivo"]:checked`).val();
                        comunica( "' . $this->pantalla . '", {
                            metodo:"confirmaIngreso",
                            tipo: "' . $this->prm->tipo . '",
                            usv_id: "' . $this->prm->usv_id . '",
                            ubv_id: "' . $this->prm->ubv_id . '",
                            numeroPlacas: "' . $this->prm->numeroPlacas . '",
                            modelo: "' . $this->prm->modelo . '",
                            pqv_id: "' . $this->prm->pqv_id . '",
                            civ_id: "' . $this->prm->civ_id . '",
                            motivo: motivoSeleccionado
                        });
                        return false;
                    },
                    function(){}
                )
                .set("defaultFocus", "cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ');
    }

    public function confirmaIngreso()
    {
        $this->mixScript('
            var tipo;
            if(' . $this->prm->motivo . '==1){
                tipo = "Patrimonio del Estado";
            }
            else{
                tipo = "Arrendado";
            }
            alertify.confirm("CONFIRMAR INGRESO A 2027","¿Está seguro de ingresar a 2027 el vehículo con número de serie ' . $this->prm->pqv_id . ' al tipo "+ tipo +"?" ,
                function(){
                    comunica( "' . $this->pantalla . '", {
                        metodo:"guardarVehiculo2026",
                        tipo: "' . $this->prm->tipo . '",
                        usv_id: "' . $this->prm->usv_id . '",
                        ubv_id: "' . $this->prm->ubv_id . '",
                        numeroPlacas: "' . $this->prm->numeroPlacas . '",
                        modelo: "' . $this->prm->modelo . '",
                        pqv_id: "' . $this->prm->pqv_id . '",
                        civ_id: "' . $this->prm->civ_id . '",
                        motivo: "' . $this->prm->motivo . '"
                    });
                },
                function(){}
            )
            .set("defaultFocus", "cancel")
            .set("labels", {ok:"Confirmar", cancel:"Cancelar"});
        ');
    }

    // Método para agregar
    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Agregamos el vehículo del parque vehícular 2020 al 2023 eliminandolo del 2020.
    ( bueno, no lo eliminamos, le ponemos el status (sts_id) a 2 )
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function guardarVehiculo2026()
    { //18-06-2019 metodo del ejercicio anterior

        $this->parque2026 = new ParqueVehicular2026();
        $this->parque2026->setPqv_id($this->prm->pqv_id);
        $this->parque2026->select();

        if ($this->parque2026->getNumeroRegistros() > 0) {
            $this->mixScript('
                notify("El vehículo con número de serie: ' . $this->prm->pqv_id . ' ya existe en el Encuadre Programático seleccionado.","warning");
        ');
        } else {

            $epg_id = ClasificacionAdministrativa::getEpg();
            $ecf_id = ClasificacionFinanciamiento::getEcf();

            $v16 = new stdClass();
            $v16->abc = 'c';
            $v16->sts_id = 2;
            $v16->pqv_id = $this->prm->pqv_id;
            ParqueVehicular2026::abc($v16);

            $this->prm->abc = 'a';
            $this->prm->gpo_id = $this->clasAdmGpo;
            $this->prm->ur_id = $this->clasAdmUr;
            $this->prm->ue_id = $this->clasAdmUe;
            $this->prm->epg_id = $epg_id;
            $this->prm->ecf_id = $ecf_id;
            $this->prm->tfv_id = $this->prm->motivo; // Vehículo no asignado ejercicio anterior
            ParqueVehicular2026::abc($this->prm);

            $this->mixScript('notify("Vehículo agregado al Parque Vehicular ' . APP_EJERCICIO . ' exitosamente");');
        }
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Mostramos todos los vehículos 2019 a los que se les puede modificar su información, y asignar costeos
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function tablaVehiculos2027()
    { // 18-06-2019 metodo del ejercicio actual

        // Los parámetros serán enviados explícitamente ...
        $epg_id = (isset($this->prm->epg_id)) ? $this->prm->epg_id : 0;
        $ecf_id = (isset($this->prm->ecf_id)) ? $this->prm->ecf_id : 0;
        ClasificacionAdministrativa::PonEnVariablesEpg($this->prm->epg_id);

        $botones = '
            <button id="regresar" epg_id="' . $epg_id . '" ecf_id="' . $ecf_id . '" type="button" class="btn btn-outline-secondary me-1" title="Regresar al listado principal">
                <i class="fa-solid fa-circle-left me-2"></i>Regresar
            </button>';

        $botones .= '
            <a id="imprimir" ccd_id = "4" epg_id="' . $epg_id . '" ecf_id="' . $ecf_id . '" target="_blank" class="me-1" title="Imprimir Parque Vehícular ' . APP_EJERCICIO_ANTERIOR . '" style="padding: 0">
                <button type="button" class="btn btn-outline-danger me-1" >
                    <i class="fa fa-file-pdf me-2"></i>PDF
                </button>
            </a>';

        $botones .= '<input type="search" width=50 id="filtroTabla" style="display: inline; width:250px; height: 30px;" class="form-control me-1"  placeholder="Filtrar vehiculo por página" />';

        $this->agregaTools($botones);

        $tabla = new stdClass();
        $tabla->id = 'tbl-Vehiculos17';
        $tabla->titulo = 'Parque Vehicular ' . APP_EJERCICIO;
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenatablaVehiculos2027';
        $tabla->comunica->nserie17 = '{$("#nserie16").val()}';
        $tabla->comunica->placas17 = '{$("#placas17").val()}';
        $tabla->comunica->epg_id = $epg_id;
        $tabla->comunica->ecf_id = $ecf_id;

        $tr = [];

        $th = new stdClass();
        $th->texto = 'Ver/Editar';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Tipo';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Uso';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Ubicaci&oacute;n';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Placas';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Modelo';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. de Serie';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. de Cilindros';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Costeo';
        array_push($tr, $th);

        $trbody = [];

        //contenido ver
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        $td->class = 'ver17';
        $td->name = 'pqv_id';
        $td->id = 'pqv_id';
        $td->attr = new stdClass();
        $td->attr->pqv_id = 'pqv_id';
        $td->align = 'left';
        array_push($trbody, $td);

        //contenido tipo
        $td = new stdClass();
        $td->campoTxt = 'pqv_tipo';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido uso
        $td = new stdClass();
        $td->campoTxt = 'usv_nombre';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido ubicacion
        $td = new stdClass();
        $td->campoTxt = 'ubv_nombre';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido placas
        $td = new stdClass();
        $td->campoTxt = 'pqv_numeroPlacas';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido modelo
        $td = new stdClass();
        $td->campoTxt = 'pqv_modelo';
        $td->align = 'right';
        array_push($trbody, $td);
        //contenido numero de serie
        $td = new stdClass();
        $td->campoTxt = 'pqv_id';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido numero de cilindros
        $td = new stdClass();
        $td->campoTxt = 'civ_nombre';
        $td->align = 'left';
        array_push($trbody, $td);
        //contenido costeo
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'usd';
        $td->class = 'costear';
        $td->name = 'pqv_id';
        $td->id = 'pqv_id';
        $td->attr = new stdClass();
        $td->attr->pqv_id = 'pqv_id';
        $td->attr->civ_id = 'civ_id';
        $td->align = 'left';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->prm->epg_id = $epg_id;
        $this->prm->ecf_id = $ecf_id;
        $this->llenatablaVehiculos2027(); // 18-06-2019 metodo del ejercicio actual

        $this->mixScript('
            $("#imprimir").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 4))

                $("body").append(form);
                form.submit();
                form.remove();
            });

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#homeMenu").click();
                    e.stopPropagation();
                });
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            $("#regresar").click(function(e){
                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            setTimeout( function() {
                    $( "#filtroTabla" ).focus()
                },
                300
            );
        ');
    }

    /*--------------------------------------------------------------------------------------------------------------------------
    Llenamos la tabla del parque vehícular 2020.   A partir de aquí editamos y costeamos
    -----------------------------------------------------------------------------------------------------------------------------*/
    private function llenatablaVehiculos2027()
    { // 18-06-2019 metodo del ejercicio actual
        // Los parámetros serán enviados explícitamente ...
        $epg_id = (isset($this->prm->epg_id)) ? $this->prm->epg_id : 0;
        $ecf_id = (isset($this->prm->ecf_id)) ? $this->prm->ecf_id : 0;

        // Marcos: puse la variable de sesión ejercicio para pruebas de multianualidad del código (si funcionó)
        $sql = '
            SELECT pqv_id, gpo_id, ur_id, pqv_modelo, pqv_tipo, civ_id, ubv_id, usv_id, sts_id, usv_nombre,
                ubv_nombre, pqv_numeroPlacas, civ_nombre
            FROM cat_ParqueVehicular
                LEFT JOIN cat_UsoVehiculo USING (usv_id)
                LEFT JOIN cat_UbicacionVehiculo USING (ubv_id)
                LEFT JOIN cat_CilindrosVehiculo USING (civ_id)
            WHERE gpo_id = :gpo
                AND ur_id = :ur
                AND epg_id = :epg
                AND ecf_id = :ecf
        ';

        $parametros = [
            'gpo' => $this->clasAdmGpo,
            'ur'  => $this->clasAdmUr,
            'epg' => $epg_id,
            'ecf' => $ecf_id,
        ];
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Vehiculos17';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;

        if ($this->wrLlenaTabla($nav) > 50) {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").show();
                $(".panel-footer").show();
            ');
        } else {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").hide();
                $(".panel-footer").hide();
            ');
        }

        $this->mixScript('
            $(".ver17").click(function() {
                console.log("Ver Vehiculo");
                comunica("' . $this->pantalla . '",{
                    metodo: "addModVehiculos2027",
                    pqv_id: $(this).attr("pqv_id"),
                    epg_id: $("#regresar").attr("epg_id"),
                    ecf_id: $("#regresar").attr("ecf_id")
                });
                return false;
            });

            $(".costear").click(function() {
                console.log("Menu de Costeo");
                comunica("' . $this->pantalla . '",{
                    metodo: "menuCosteo",
                    pqv_id: $(this).attr("pqv_id"),
                    civ_id: $(this).attr("civ_id"),
                    epg_id: $("#regresar").attr("epg_id"),
                    ecf_id: $("#regresar").attr("ecf_id")
                });
                return false;
            });

            $(document).ready(function() {
                $("table").filterTable({ // apply filterTable to all tables on this page
                    inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
                });
            });
        ');
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Método para pintar los campos para actualizar e insertar un vehículo al parque vehícular 2020
    manda llamar al metodo guardarVehiculo2022
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function addModVehiculos2027()
    { //18-06-2019 metodo del ejercicio actual
        $epg_id = ClasificacionAdministrativa::getEpg();
        $ecf_id = ClasificacionFinanciamiento::getEcf();

        // Los parámetros serán enviados explícitamente ...
        // Si pasaron el número de cédula, es un cambio.. si no, le ponemos 0 como número
        $pqv_id = (isset($this->prm->pqv_id) ? $this->prm->pqv_id : '');
        // Si es una nueva cédula, podrán elegir el encuadre programático y la clave de financiamiento
        if ($pqv_id == '') {

            $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";
            $("#viewer_extra").append(
                $("#clasAdminSeccionCombos")
            );
            $("#close_jma_viewer, #addcompromisoclose_jma_viewer").click(function(){
                $(".cabecera").append($("#clasAdminSeccionCombos"))
                limpiaClasAdmin();
            });
        ');


            $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin());
            $this->mixHtml(CatalogoProgramaticoFinanciamiento::ponF1());
            $encLectura = '';

            // Si no, sólo podrán ver lo que está registrado en la base de datos
        } else {
            $encLectura = ' disabled ';
        }

        $boton = ''; //variable donde se van a cargar los botones de guardar, actualizar y eliminar.
        $this->vehiculo = new ParqueVehicular2027();

        // Si nos pasaron el dato del número de la cédula, es un cambio
        $valorEpg = '';
        $nombreEpg = '';
        $valorCF = '';
        $nombreCF = '';
        $tieneCosteo = '';
        if ($pqv_id != '') {

            $tipoguardado = 'actualizar';
            $boton .= '
            <button id="btnregresarvehiculos" type="button" class="btn btn-outline-secondary me-1" title="Regresar a la página principal">
                <i class="fa-solid fa-circle-left me-2"></i>Regresar
            </button>
            <button id="btnguadarVehiculo" tipoguardado="actualizar" type="button" class="btn btn-outline-success btnguadarVehiculo me-1" title="Actualizar vehículo en parque vehícular 2019">
                <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
            </button>
            <button id="btneliminarVehiculo" type="button" class="btn btn-outline-danger me-1" title="Eliminar vehículo">
                <i class="fa-solid fa-trash me-2"></i>Eliminar
            </button>
            <a id="imprimirTodo" epg_id="' . $epg_id . '" ecf_id="' . $ecf_id . '" target="_blank" class="me-1" title="Imprimir Cédula de Vehiculos"style="padding: 0">
                <button type="button" class="btn btn-outline-danger me-1" >
                    <i class="fa fa-file-pdf me-2"></i>PDF
                </button>
            </a>';

            $this->vehiculo->setPqv_id($this->prm->pqv_id);
            $this->vehiculo->select();

            // Ponemos la clasificación administrativa registrada en la cédula
            $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($this->vehiculo->getEpg_id());
            $valorEpg = $this->vehiculo->getGpo_id() . $this->vehiculo->getUr_id() . '.'
                . $this->vehiculo->getUe_id() . '.' . $this->clasAdmPrg . '.' . $this->clasAdmSpr . '.'
                . $this->clasAdmPy . '.' . $this->clasAdmOa;

            $_SESSION[_S]['clasAdm-ue'] = $this->vehiculo->getUe_id();

            // Ponemos la clave de financiamiento registrada en la cédula
            ClasificacionFinanciamiento::PonEnVariablesEcf($this->vehiculo->getEcf_id());
            $valorCF = $_SESSION[_S]['cvefin']->cff . '-' . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft
                . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . '-' . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            $nombreCF = $_SESSION[_S]['cvefin']->nombre;
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $clasAdmin->filtro = 'poa';
            $clasAdmin->epg_id = $epg_id;
            $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
            $this->mixHtml(CatalogoProgramaticoFinanciamiento::ponF1());
            $valorCF_esc = addslashes($valorCF);
            $nombreCF_esc = addslashes($nombreCF);

            $this->mixScript('
                $("#encProg").val("' . $valorEpg . '");
                $("#encProg").attr("ori", "' . $valorEpg . '");
                $("#encProg").prop("disabled", true);
                $("#nomProg").val("' . $nombreEpg . '");
                $("#encCveFin").prop("disabled", true);

                setTimeout(function() {
                    console.log("SetTimeout ejecutado...");
                    $("#encCveFin").val("' . $valorCF_esc . '").prop("disabled", true);
                    $("#nomCveFin").val("' . $nombreCF_esc . '");
                    console.log("Valor encCveFin (con delay):", $("#encCveFin").val());
                }, 500);

                console.log($("#encCveFin").length, $("#nomCveFin").length);

                $("#comoSeUsa").click(function(){
                    alertify.alert("Cómo seleccionar encuadres...", "<h5>Cuando te coloques sobre los campos de <b>Encuadre Programático</b> y <b>Clave de Financiamiento</b> verás que el campo cambia de color a verde.   Esto significa que puedes presionar la tecla <b>F1</b> o <b>F2</b> para seleccionar los encuadres.<br /><br /><b>Método 1</b>:<br/><br/>Si presionas F1 verás la lista de todas las combinaciones de encuadre programático y claves de financiamiento existentes.<br/><br />Selecciona con doble click la que quieras utilizar y se rellenarán ambos cambos (programático y clave de financiamiento).<br /><br /><b>Método 2:</b><br/><br/>Si presionas F2 activarás la seleccion por combos.<br /><br />En éste caso debes colocarte sobre el campo de <b>Encuadre Programático</b> y presionar F2 para seleccionar cada combo del encuadre.<br/><br/>Cuando hayas seleccionado todo el encuadre (hasta Obra/Actividad) cierra la ventana y colócate en el campo de <b>Clave de Financiamiento</b>.   Presiona F2 para mostrar la ventana que te permite seleccionar la Clave de Financiamento.</h5>"
                    );
                });
            ');

            $this->conectar();
            $sql = '
            SELECT
                pqv_id
            FROM poa_VehiculoMaquina
            WHERE gpo_id = :gpo
                AND ur_id = :ur
                AND pqv_id = :pqv
        ';
            $parametros = [
                'gpo' => $this->clasAdmGpo,
                'ur'  => $this->clasAdmUr,
                'pqv' => $this->prm->pqv_id,
            ];
            $this->setSql($sql);
            $this->consultar($parametros);
            $tieneCosteo = $this->getNumeroRegistros();
            $this->desconectar();
        } else { // Si no, es un alta
            $tipoguardado = 'insertar';
            $boton .= '<button id="btnregresarvehiculos" type="button" class="btn btn-outline-secondary me-1" title="Regresar a la página principal"><i class="fa-solid fa-circle-left me-2"></i>Regresar</button>
                <button id="btnguadarVehiculo" tipoguardado="insertar" type="button" class="btn btn-outline-success btnguadarVehiculo me-1" title="Guardar vehículo en parque vehícular ' . APP_EJERCICIO . '"><i class="fa fa-floppy-disk me-2"></i>Guardar</button>';
            $this->prm->pqv_id = '';
        }

        if ($pqv_id != '') {
            $this->mixScript('
                $("#encProg").blur();
            ');
        }

        //numero de serie
        $pqv_id = new stdClass();
        $pqv_id->id = 'pqv_id';
        $pqv_id->name = 'pqv_id';
        $pqv_id->label = 'Número de Serie';
        $pqv_id->enDiv = true;
        $pqv_id->maxlength = '100';
        $pqv_id->required = true;
        //tipo
        $tipo = new stdClass();
        $tipo->id = 'tipo';
        $tipo->name = 'tipo';
        $tipo->label = 'Tipo';
        $tipo->enDiv = true;
        $tipo->maxlength = '100';
        $tipo->required = true;
        //Uso
        $usv_id = new stdClass();
        $usv_id->sql = 'SELECT usv_id, usv_nombre FROM cat_UsoVehiculo';
        $usv_id->id = 'usv_id';
        $usv_id->name = 'usv_id';
        $usv_id->required = true;
        $usv_id->mensaje = 'Selecciona Uso';
        $usv_id->label = 'Uso';
        //Ubicacion
        $ubv_id = new stdClass();
        $ubv_id->sql = 'SELECT ubv_id, ubv_nombre FROM cat_UbicacionVehiculo';
        $ubv_id->id = 'ubv_id';
        $ubv_id->name = 'ubv_id';
        $ubv_id->required = true;
        $ubv_id->mensaje = 'Selecciona Ubicacion';
        $ubv_id->label = 'Ubicación';
        //numero de placas
        $numeroPlacas = new stdClass();
        $numeroPlacas->id = 'numeroPlacas';
        $numeroPlacas->name = 'numeroPlacas';
        $numeroPlacas->label = 'Placas';
        $numeroPlacas->enDiv = true;
        $numeroPlacas->maxlength = '7';
        $numeroPlacas->required = true;
        //numero de cilindros
        $civ_id = new stdClass();
        $civ_id->sql = 'SELECT civ_id,civ_nombre FROM cat_CilindrosVehiculo WHERE civ_id != 0 ';
        $civ_id->id = 'civ_id';
        $civ_id->name = 'civ_id';
        $civ_id->soloText = true;
        $civ_id->required = true;
        $civ_id->mensaje = 'Selecciona Cilindros';
        $civ_id->label = 'Cilindros';
        //modelo
        $modelo = new stdClass();
        $modelo->id = 'modelo';
        $modelo->name = 'modelo';
        $modelo->label = 'Modelo';
        $modelo->enDiv = true;
        $modelo->maxlength = '100';
        $modelo->formato = 'entero';
        $modelo->required = true;

        //tipo vehiculo
        $tipoVehiculo = new stdClass();
        $tipoVehiculo->id = 'tfv_id';
        $tipoVehiculo->opciones = ['1' => 'PATRIMONIO DEL ESTADO', '2' => 'ARRENDADO'];
        $tipoVehiculo->name = 'tfv_id';
        $tipoVehiculo->label = 'Tipo de VehÍculo';
        $tipoVehiculo->required = true;
        $modelo->enDiv = true;
        $tipoVehiculo->mensaje = 'Seleccione Tipo de VehÍculo';

        if ($tipoguardado == 'actualizar') {
            $tipo->value = $this->vehiculo->getTipo();
            $usv_id->selecciona = $this->vehiculo->getUsv_id();
            $ubv_id->selecciona = $this->vehiculo->getUbv_id();
            $pqv_id->value = $this->vehiculo->getPqv_id();
            $pqv_id->readonly = 'true';
            $numeroPlacas->value = $this->vehiculo->getNumeroPlacas();
            $civ_id->selecciona = $this->vehiculo->getCiv_id();
            $modelo->value = $this->vehiculo->getModelo();
            $tipoVehiculo->selecciona = $this->vehiculo->getTfv_id();
            if ($this->escritura) {
                $this->agregaTools($boton);
            } else {
                $numeroPlacas->readonly = 'true';
                $tipo->readonly = 'true';
                $modelo->readonly = 'true';
            }
        } else {
            if ($this->escritura) {
                $this->agregaTools($boton);
            } else {
                $numeroPlacas->readonly = 'true';
                $tipo->readonly = 'true';
                $modelo->readonly = 'true';
            }
        }

        $html = '
            <fieldset class="col-md-12 mb-4">
                    <legend class="mb-4 ">Informaci&oacute;n del registro</legend>
                    <div class="row mb-4">
                            <fieldset class="col-md-3 col-xs-3">
                                    ' . $this->ponInput($pqv_id) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                    ' . $this->ponInput($numeroPlacas) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                    ' . $this->ponInput($tipo) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                    ' . $this->ponInput($modelo) . '
                            </fieldset>
                    </div>
                    <div class="row">
                            <fieldset class="col-md-4 col-xs-4">
                                    ' . $this->ponCombo($civ_id) . '
                            </fieldset>
                            <fieldset class="col-md-4 col-xs-4">
                                    ' . $this->ponCombo($usv_id) . '
                            </fieldset>
                            <fieldset class="col-md-4 col-xs-4">
                                    ' . $this->ponCombo($ubv_id) . '
                            </fieldset>
                            
                    </div>
                    <div class="row mt-2">
                        <fieldset class="col-md-4 col-xs-4">
                                    ' . $this->ponCombo($tipoVehiculo) . '
                        </fieldset>
                    </div>
            </fieldset>
            <div id="contenedorCFModal" class="mb-4 "></div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#imprimirTodo").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 4))

                $("body").append(form);
                form.submit();
                form.remove();
            });



            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#homeMenu").click();
                    e.stopPropagation();
                });
                Mousetrap.unbind("ctrl+enter");
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVehiculos2027",
                    epg_id: "' . $epg_id . '",
                    ecf_id: "' . $ecf_id . '"
                });
                e.stopPropagation();
            });

            $("#btnregresarvehiculos").click(function(e){
                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVehiculos2027",
                    epg_id: "' . $epg_id . '",
                    ecf_id: "' . $ecf_id . '"
                });
                e.stopPropagation();
            });

            Mousetrap.bind("ctrl+enter", function(e) {
                e.preventDefault(); $("#btnguadarVehiculo").click();
            });

            $("#btnguadarVehiculo").click(function(){
            if ($("#encProg").val().trim() === "") { 
            notify("El campo  Encuadre Programático  de Serie es obligatorio", "warning");
            return false;
           }
            
            if ($("#pqv_id").val().trim() === "") { 
            notify("El campo Número de Serie es obligatorio", "warning");
            return false;
           }
            if ($("#numeroPlacas").val().trim() === "") { 
            notify("El campo Placas es obligatorio", "warning");
            return false;
           }
            if ($("#tipo").val().trim() === "") { 
            notify("El campo Tipo es obligatorio", "warning");
            return false;
           }
            if ($("#civ_id").val().trim() === "") { 
            notify("El campo Cilindros es obligatorio", "warning");
            return false;
           }
            if ($("#usv_id").val().trim() === "") { 
            notify("El campo Uso es obligatorio", "warning");
            return false;
           }

            if ($("#ubv_id").val().trim() === "") { 
            notify("El campo Ubicación es obligatorio", "warning");
            return false;
           }

           
            if ($("#tfv_id").val().trim() === "") { 
            notify("El campo Tipo de VehÍculo es obligatorio", "warning");
            return false;
           }

            if("' . $tieneCosteo . '"> 0 && $("#tfv_id").val() != "' . $this->vehiculo->getTfv_id() . '"){
            notify("Para realizar el cambio de tipo de vehÍculo es necesario eliminar el costeo", "warning");
            return false;
            }else {
                comunica("' . $this->pantalla . '",{
                    metodo: "guardarVehiculo2027",
                    tipoguardado: $(this).attr("tipoguardado"),
                    pqv_id: $("#pqv_id").val(),
                    numeroPlacas: $("#numeroPlacas").val(),
                    tipo: $("#tipo").val(),
                    modelo: $("#modelo").val(),
                    civ_id: $("#civ_id").val(),
                    usv_id: $("#usv_id").val(),
                    ubv_id: $("#ubv_id").val(),
                    tfv_id: $("#tfv_id").val(),
                    epg_id: "' . $epg_id . '",
                    ecf_id: "' . $ecf_id . '"
                });
            }
            });

            $("#btneliminarVehiculo").click(function(){
                alertify.confirm("ELIMINAR VEHÍCULO", "¿ Confirma que desea eliminar el vehiculo con numero de serie: <h5 style=\"display:inline\"><b>' . $this->prm->pqv_id . '</b></h5> ?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "eliminarVehiculo2027",
                            pqv_id:$("#pqv_id").val()
                        });
                    },
                    function(){},
                ).set("defaultFocus","cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });

            if("' . $tipoguardado . '" == "actualizar"){
                setTimeout( function() {
                        $( "#numeroPlacas" ).focus()
                    },
                    100
                );
            }else{
                setTimeout( function() {
                        $( "#encProg" ).focus()
                    },
                    100
                );
            }
        ');
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Método para agregar y actalizar un vehículo del parque vehícular 2020, desde el botón nuevo y boton ver/editar.
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function guardarVehiculo2027()
    { //18-06-2019 metodo del ejercicio actual
        $epg_id = ClasificacionAdministrativa::getEpg();
        $ecf_id = ClasificacionFinanciamiento::getEcf();

        $this->parque2026 = new ParqueVehicular2026();
        $this->parque2026->setPqv_id($this->prm->pqv_id);
        $this->parque2026->select();
        $this->parque2027= new ParqueVehicular2027();
        $this->parque2027->setPqv_id($this->prm->pqv_id);
        $this->parque2027->select();

        if ($this->prm->tipoguardado == 'insertar') {
            if ($this->parque2027->getNumeroRegistros() == 1) {

                $this->mixScript('
                    notify("El vehiculo con numero de serie: ' . $this->prm->pqv_id . ' ya existe. Verifique su información.","warning");
                ');
            } elseif ($this->parque2026->getNumeroRegistros() == 1) {
                if ($this->parque2026->getGpo_id() == $this->clasAdmGpo && $this->parque2026->getUr_id() == $this->clasAdmUr) {
                    $this->prm->abc = 'a';
                    $this->prm->gpo_id = $this->clasAdmGpo;
                    $this->prm->ur_id = $this->clasAdmUr;
                    $this->prm->ue_id = $this->clasAdmUe;
                    $this->prm->epg_id = $epg_id;
                    $this->prm->ecf_id = $ecf_id;

                    ParqueVehicular2027::abc($this->prm);
                    $this->mixScript('
                        notify("Vehículo agregado al Parque Vehicular ' . APP_EJERCICIO . ' exitosamente");
                        comunica("' . $this->pantalla . '",{
                            metodo: "tablaVehiculos2027",
                            epg_id: "' . $epg_id . '",
                            ecf_id: "' . $ecf_id . '"
                        });
                    ');
                } else {
                    $this->mixScript('
                        notify("El vehiculo con numero de serie: ' . $this->prm->pqv_id . ' NO corresponde a su UR. Verifique su información.","warning");
                    ');
                }
            } else {
                $this->prm->abc = 'a';
                $this->prm->gpo_id = $this->clasAdmGpo;
                $this->prm->ur_id = $this->clasAdmUr;
                $this->prm->ue_id = $this->clasAdmUe;
                $this->prm->epg_id = $epg_id;
                $this->prm->ecf_id = $ecf_id;

                ParqueVehicular2027::abc($this->prm);
                $this->mixScript('
                    notify("Vehículo agregado al Parque Vehicular ' . APP_EJERCICIO . ' exitosamente");
                    comunica("' . $this->pantalla . '",{
                        metodo: "tablaVehiculos2027",
                        epg_id: "' . $epg_id . '",
                        ecf_id: "' . $ecf_id . '"
                    });
                ');
            }
        } else {
            $this->prm->abc = 'c';
            $this->prm->gpo_id = $this->clasAdmGpo;
            $this->prm->ur_id = $this->clasAdmUr;
            $this->prm->ue_id = $this->clasAdmUe;
            $this->prm->epg_id = $epg_id;
            $this->prm->ecf_id = $ecf_id;
            ParqueVehicular2027::abc($this->prm);
            $this->mixScript('
                notify("Vehículo actualizado exitosamente");
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVehiculos2027",
                    epg_id: "' . $epg_id . '",
                    ecf_id: "' . $ecf_id . '"
                });
            ');
        }
    }

    /*----------------------------------------------------------------------------------------------------
    Método para eliminar un vehículo del parque vehícular 2020
    --------------------------------------------------------------------------------------------*/
    public function eliminarVehiculo2027()
    { //18-06-2019 metodo del ejercicio actual
        $epg_id = ClasificacionAdministrativa::getEpg();
        $ecf_id = ClasificacionFinanciamiento::getEcf();

        $this->conectar();
        $sql = '
            SELECT
                pqv_id
            FROM poa_VehiculoMaquina
            WHERE gpo_id = :gpo
                AND ur_id = :ur
                AND pqv_id = :pqv
        ';
        $parametros = [
            'gpo' => $this->clasAdmGpo,
            'ur'  => $this->clasAdmUr,
            'pqv' => $this->prm->pqv_id,
        ];
        $this->setSql($sql);
        $this->consultar($parametros);

        if ($this->getNumeroRegistros() > 0) {
            $this->mixScript('
                    notify("Este vehículo no puede ser eliminado porque ya ha sido costeado");
                    comunica("' . $this->pantalla . '",{
                        metodo: "tablaVehiculos2027",
                        epg_id: "' . $epg_id . '",
                        ecf_id: "' . $ecf_id . '"
                    });
                ');
        } else {
            $this->prm->abc = 'b';
            ParqueVehicular2027::abc($this->prm);
            $this->mixScript('
                notify("Vehículo eliminado exitosamente");
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVehiculos2027",
                    epg_id: "' . $epg_id . '",
                    ecf_id: "' . $ecf_id . '"
                });
            ');
        }
    }

    /** Menú de costeos del Vehiculo muestra las opciones de IMPUESTOS - SEGUROS - MANTENIMIENTO - COMBUSTIBLES**/
    public function menuCosteo()
    {
        $this->agregaTools('<button id="btnregresar" type="button" class="btn btn-outline-secondary me-1" title="Regresar a la tabla ' . APP_EJERCICIO_ANTERIOR . '"><i class="fa-solid fa-circle-left me-2"></i>Regresar</button>');

        $this->parqueVehicular = new ParqueVehicular2027();
        $this->parqueVehicular->setPqv_id($this->prm->pqv_id);
        $this->parqueVehicular->select();

        ClasificacionFinanciamiento::PonEnVariablesEcf($this->parqueVehicular->getEcf_id());

        $valorCF = $_SESSION[_S]['cvefin']->cff . '-' . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft
            . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . '-' . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;

        $nombreCF = $_SESSION[_S]['cvefin']->nombre;

        $html = '';

        $this->mixHtml('
            <legend class="col-md-4 col-xs-4"> Serie:' . $this->parqueVehicular->getPqv_id() . ' - Placas:' . $this->parqueVehicular->getNumeroPlacas() . '</legend>
            <legend class="row-md-4 " align="right">Clave de fin.: ' . $valorCF . ' - ' . $nombreCF . '</legend>
        ');

        if ($this->parqueVehicular->getTfv_id() === 1) {
            $html = ('<div style=" padding-left:20%; padding-bottom:50px; display:table-cell" class="row mb-4">
                <div class="boton_opcion dvvehiculos" cve_id="1" pve_id="1" titpp="Tenencia, verificaci&oacute;n">
                    <div class="boton_opcion_titulo ">Impuestos</div>
                    <div>
                        <div class="center"><img src="img/impuestos.png" alt="" /><br/>
                            <strong>
                                    <small>Tenencia, verificaci&oacute;n</small>
                            </strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                ' . ($this->prm->civ_id != 5 ? '
                <div class="boton_opcion dvvehiculos" cve_id="2" pve_id="1" titpp="Seguros">
                    <div class="boton_opcion_titulo">Seguro Vehicular</div>
                    <div>
                        <div class="center"><img src="img/seguros.png" alt=""  /><br>
                            <strong><small>P&oacute;lizas</small></strong>
                        </div>
                    </div>
                </div>
            ' : '
                <div class="boton_opcion dvvehiculos" cve_id="3" pve_id="1" titpp="Seguros">
                    <div class="boton_opcion_titulo">Seguro A&eacute;reo</div>
                    <div >
                        <div class="center"> <img src="img/seguros.png" alt=""/><br/>
                            <strong><small>P&oacute;lizas</small> </strong>
                        </div>
                    </div>
                </div>
            ') . '
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="4" titpp="Afinaci&oacute;n, lubricaci&oacute;n">
                    <div class="boton_opcion_titulo">Mantenimiento</div>
                    <div>
                        <div class="center"><img src="img/matenimiento.png" alt=""/><br/>
                            <strong> <small>Afinaci&oacute;n, lubricaci&oacute;n</small> </strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="5" pve_id="1" titpp="Gasolina, diesel">
                    <div class="boton_opcion_titulo">Combustible</div>
                    <div >
                        <div class="center"> <img src="img/combustible.png" alt=""/><br/>
                            <strong> <small>Gasolina, diesel</small> </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div id="div_cedula" style="margin-top:20px;"/>');
        } elseif ($this->parqueVehicular->getTfv_id() === 2) {
            $html = '
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="4" titpp="Afinaci&oacute;n, lubricaci&oacute;n">
                    <div class="boton_opcion_titulo">Mantenimiento</div>
                    <div>
                        <div class="center"><img src="img/matenimiento.png" alt=""/><br/>
                            <strong> <small>Afinaci&oacute;n, lubricaci&oacute;n</small> </strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="5" pve_id="1" titpp="Gasolina, diesel">
                    <div class="boton_opcion_titulo">Combustible</div>
                    <div >
                        <div class="center"> <img src="img/combustible.png" alt=""/><br/>
                            <strong> <small>Gasolina, diesel</small> </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div id="div_cedula" style="margin-top:20px;"/>
            ';
        }

        $this->mixHtml($html);
        $this->mixStyle('
            .boton_opcion{
                cursor: pointer;
            }
        ');

        $this->mixScript('
            $(".boton_opcion").click(function() {
                $(this).addClass("active").siblings().removeClass("active");
            });

            $(".dvvehiculos").click( function(){
                console.log("Costeo");
                comunica("' . $this->pantalla . '",{
                    metodo: "determinaCosteo",
                    pqv_id: "' . $this->parqueVehicular->getPqv_id() . '",
                    civ_id: ' . $this->parqueVehicular->getCiv_id() . ',
                    pve_id: $(this).attr("pve_id"),
                    cve_id: $(this).attr("cve_id"),
                    ecf_id: "' . $this->parqueVehicular->getEcf_id() . '",
                    epg_id: "' . $this->parqueVehicular->getEpg_id() . '"
                });
            });

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault(); $("#btnregresar").click();
                e.stopPropagation();
            });

            $("#btnregresar").click(function(){
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVehiculos2027",
                    epg_id: "' . $this->parqueVehicular->getEpg_id() . '",
                    ecf_id: "' . $this->parqueVehicular->getEcf_id() . '"
                })
            });
        ');
    }

    /* Muestra el desglose del submenu de mantenimiento
    Adquisición de llantas - Lubricación - Transmisión, diferencial - Suspención y frenos - Motor
    Afinación de motor  - Otros
     */
    public function menuMantenimiento()
    {
        $this->agregaTools('<button id="regresar" type="button" class="btn btn-outline-secondary me-1" title="Regresar al menú del costeo"><i class="fa-solid fa-circle-left me-2"></i>Regresar</button>');

        $this->parqueVehicular = new ParqueVehicular2027();
        $this->parqueVehicular->setPqv_id($this->prm->pqv_id);
        $this->parqueVehicular->select();

        ClasificacionFinanciamiento::PonEnVariablesEcf($this->parqueVehicular->getEcf_id());
        $valorCF = $_SESSION[_S]['cvefin']->cff . '-' . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft
            . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . '-' . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
        $nombreCF = $_SESSION[_S]['cvefin']->nombre;

        $this->mixHtml('
            <legend class="col-md-4 col-xs-4">Serie:' . $this->parqueVehicular->getPqv_id() . ' - Placa:' . $this->parqueVehicular->getNumeroPlacas() . '</legend>
            <legend class="col-md-8" align="right">Clave de fin.: ' . $valorCF . ' - ' . $nombreCF . '</legend>
            <div style="width:100%; padding-left:5%; padding-bottom:50px; display:table-cell">
                <div class="boton_opcion dvvehiculos" cve_id="6" pve_id="1" titpp="Adquisici&oacute;n de llantas">
                    <div class="boton_opcion_titulo">Adquisici&oacute;n de llantas</div>
                    <div>
                        <div class="center"> <img src="img/llantas.png" width="32" height="32" alt=""/><br>
                            <strong><small>Compra</small></strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="5" pve_id="2" titpp="Lubricaci&oacute;n">
                    <div class="boton_opcion_titulo">Lubricaci&oacute;n</div>
                    <div>
                        <div class="center"> <br> <img src="img/lubricacion.png" width="32" height="32" alt=""/><br>
                            <strong><small>Aceites, aditivos</small></strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="6" pve_id="2" titpp="Transmisi&oacute;n, diferencial">
                    <div class="boton_opcion_titulo">Transmisi&oacute;n, diferencial</div>
                    <div>
                        <div class="center"> <img src="img/transmision.png" width="32" height="32" alt=""/> <br>
                            <strong><small>Ajustes, matenimiento</small></strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="6" pve_id="3" titpp="Suspensi&oacute;n y frenos">
                    <div class="boton_opcion_titulo">Suspensi&oacute;n y frenos</div>
                    <div>
                        <div class="center"><img src="img/suspension.png" width="32" height="32" alt=""/> <br>
                            <strong><small>Ajustes</small> </strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="4" pve_id="1" titpp="Motor">
                    <div class="boton_opcion_titulo">Motor</div>
                    <div>
                        <div class="center"><br><img src="img/motor.png" width="32" height="32" alt=""/> <br>
                            <strong><small>Adquisici&oacute;n</small> </strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="4" pve_id="2" titpp="Afinaci&oacute;n de motor">
                    <div class="boton_opcion_titulo">Afinaci&oacute;n de motor</div>
                    <div>
                        <div class="center"><br><img src="img/afi_motor.png" width="32" height="32" alt=""/> <br>
                            <strong><small>Ajustes, matenimiento</small></strong>
                        </div>
                    </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
                <div class="boton_opcion dvvehiculos" cve_id="6" pve_id="4" titpp="Otros">
                    <div class="boton_opcion_titulo">Otros</div>
                    <div>
                        <div class="center"><br> <img src="img/autootros.png" width="32" height="32" alt=""/><br>
                            <strong><small>Otros</small> </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div id="div_cedula" style="margin-top:20px;"/>
        ');

        $this->mixScript('
            $(".boton_opcion").click(function() {
                $(this).addClass("active").siblings().removeClass("active");
            });

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                $("#regresar").click();
                e.stopPropagation();
            });

            $("#regresar").click( function(){
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "menuCosteo",
                    pqv_id: "' . $this->prm->pqv_id . '",
                    civ_id: ' . $this->prm->civ_id . ',
                    ecf_id: "' . $this->prm->ecf_id . '",
                    epg_id: "' . $this->prm->epg_id . '"
                });
            });

            $(".dvvehiculos").click( function(){
                $(".dvvehiculos").each(function(){
                    $(this).removeClass("ph_sel");
                });

                $(this).addClass("ph_sel");

                comunica("' . $this->pantalla . '",{
                    metodo: "determinaCosteo",
                    pqv_id: "' . $this->prm->pqv_id . '",
                    civ_id: ' . $this->prm->civ_id . ',
                    cve_id: $(this).attr("cve_id"),
                    pve_id: $(this).attr("pve_id"),
                    ecf_id: "' . $this->prm->ecf_id . '",
                    epg_id: "' . $this->prm->epg_id . '"
                });
            });
        ');
    }

    public function determinaCosteo()
    {
        $prm = new stdClass();
        $prm->pqv_id = isset($this->prm->pqv_id) ? $this->prm->pqv_id : ''; //Numero de serie
        $prm->pve_id = isset($this->prm->pve_id) ? $this->prm->pve_id : ''; //cedula de costeo
        $prm->cve_id = isset($this->prm->cve_id) ? $this->prm->cve_id : ''; //detalle de la cedula de costeo
        $prm->civ_id = isset($this->prm->civ_id) ? $this->prm->civ_id : ''; //detalle de la cedula de costeo
        $prm->ecf_id = isset($this->prm->ecf_id) ? $this->prm->ecf_id : ''; //encuadre de la clave de financiamiento
        $prm->epg_id = isset($this->prm->epg_id) ? $this->prm->epg_id : ''; //encuadre programatico

        switch ($prm->cve_id) {
            case 1:
            case 2:
            case 3:
                $this->addModCedula($prm);
                break;
            case 4:
                switch ($prm->pve_id) {
                    case 1:
                    case 2:
                        $this->addModCedula($prm);
                        break;
                    default:
                        $this->menuMantenimiento($prm);
                        break;
                }
                break;
            case 5:

                switch ($prm->pve_id) {
                    case 1:
                    case 2:
                        $this->addModCedula($prm);
                        break;
                    default:
                        echo 'kym';
                        break;
                }
                break;
            case 6:

                switch ($prm->pve_id) {
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                        $this->addModCedula($prm);
                        break;
                    default:
                        echo 'kym';
                        break;
                }
                break;
            default:
                echo 'kym';
                break;
        }
    }

    public function addModCedula()
    {
        $this->setContenedor('div_cedula');

        $vmq = new stdClass();
        $vmq->gpo_id = $this->clasAdmGpo;
        $vmq->ur_id = $this->clasAdmUr;
        $vmq->pqv_id = $this->prm->pqv_id;
        $vmq->cve_id = $this->prm->cve_id;
        $vmq->pve_id = $this->prm->pve_id;

        //instanciando la cedula, ya sea para editar o grabar nuevo
        $vehiculo = new VehiculoMaquina($vmq);

        //si la cedula es diferente de 0, significa que ya existe
        if ($vehiculo->getCed_id() != 0) {
        } else {

            $vehiculo->setCed_id(0);
            $vehiculo->setEpg_id($this->prm->epg_id);
            $vehiculo->setEcf_id($this->prm->ecf_id);
        }

        $objGto = new stdClass();
        $objGto->ccd_id = 4;
        $objGto->cve_id = $vehiculo->getCve_id(); //solo se definio para vehiculo, arrendamiento y telefonia
        $objGto->pve_id = $vehiculo->getPve_id(); //solo se definio para vehiculo, arrendamiento y telefonia
        $objGto->filtro = 'costeo';

        if ($vehiculo->getCed_id() != 0) {
            $objGto->lectura = true;
            $objGto->pg_id = $vehiculo->getPg_id();
            $objGto->pe_id = $vehiculo->getPe_id();
        }

        $cveFin = new stdClass();
        $cveFin->horizontal = 3;
        $cveFin->lectura = true;
        $cveFin->ecf_id = $vehiculo->getEcf_id();

        switch ($vehiculo->getCve_id()) {
            case 1:
                $this->prm->vehiculo = new stdClass();
                $this->prm->vehiculo->tenencia = $vehiculo->getCostoUnitario();
                $this->prm->vehiculo->engomado = $vehiculo->getImporte();
                $this->prm->vehiculo->otrosImpuestos = $vehiculo->getOtrosImportes();
                $this->prm->vehiculo->pqv_id = $vehiculo->getPqv_id();
                $this->prm->vehiculo->cve_id = $vehiculo->getCve_id();
                $this->prm->vehiculo->pve_id = $vehiculo->getPve_id();
                $this->prm->vehiculo->ced_id = $vehiculo->getCed_id();
                $variable = $this->detalleImpuestos($this->prm);
                break;
            case 2:
            case 3:
                $this->prm->vehiculo = new stdClass();
                $this->prm->vehiculo->aseguradora = $vehiculo->getAseguradora();
                $this->prm->vehiculo->poliza = $vehiculo->getPoliza();
                $this->prm->vehiculo->pqv_id = $vehiculo->getPqv_id();
                $this->prm->vehiculo->cve_id = $vehiculo->getCve_id();
                $this->prm->vehiculo->pve_id = $vehiculo->getPve_id();
                $this->prm->vehiculo->ced_id = $vehiculo->getCed_id();
                $variable = $this->detalleSeguros($this->prm);
                break;
            case 4:
                $this->prm->vehiculo = new stdClass();
                $this->prm->vehiculo->costoUnitario = $vehiculo->getCostoUnitario();
                $this->prm->vehiculo->pqv_id = $vehiculo->getPqv_id();
                $this->prm->vehiculo->cve_id = $vehiculo->getCve_id();
                $this->prm->vehiculo->pve_id = $vehiculo->getPve_id();
                $this->prm->vehiculo->ced_id = $vehiculo->getCed_id();
                $variable = $this->detalleMantenimiento($this->prm);
                break;
            case 5:
                switch ($vehiculo->getPve_id()) {
                    case 1:
                        $this->prm->vehiculo = new stdClass();
                        $this->prm->vehiculo->tco_id = $vehiculo->getTco_id();
                        $this->prm->vehiculo->costoUnitario = $vehiculo->getCostoUnitario();
                        $this->prm->vehiculo->pqv_id = $vehiculo->getPqv_id();
                        $this->prm->vehiculo->cve_id = $vehiculo->getCve_id();
                        $this->prm->vehiculo->pve_id = $vehiculo->getPve_id();
                        $this->prm->vehiculo->civ_id = $this->prm->civ_id;
                        $this->prm->vehiculo->ced_id = $vehiculo->getCed_id();
                        $variable = $this->detalleCombustible($this->prm);
                        break;
                    case 2: //case 3:
                        $this->prm->vehiculo = new stdClass();
                        $this->prm->vehiculo->tco_id = $vehiculo->getTco_id();
                        $this->prm->vehiculo->costoUnitario = $vehiculo->getCostoUnitario();
                        $this->prm->vehiculo->pqv_id = $vehiculo->getPqv_id();
                        $this->prm->vehiculo->cve_id = $vehiculo->getCve_id();
                        $this->prm->vehiculo->pve_id = $vehiculo->getPve_id();
                        $this->prm->vehiculo->civ_id = $this->prm->civ_id;
                        $this->prm->vehiculo->ced_id = $vehiculo->getCed_id();
                        $variable = $this->detalleMantenimiento($this->prm);
                        break;
                    default:
                        echo 'kym';
                        break;
                }
                break;
            case 6:
                $this->prm->vehiculo = new stdClass();
                $this->prm->vehiculo->costoUnitario = $vehiculo->getCostoUnitario();
                $this->prm->vehiculo->pqv_id = $vehiculo->getPqv_id();
                $this->prm->vehiculo->cve_id = $vehiculo->getCve_id();
                $this->prm->vehiculo->pve_id = $vehiculo->getPve_id();
                $this->prm->vehiculo->ced_id = $vehiculo->getCed_id();
                $variable = $this->detalleMantenimiento($this->prm);
                break;
            default:
                echo 'kym';
                break;
        }

        $this->agregaTools('
            ' . ($vehiculo->getCve_id() == 4 || $vehiculo->getCve_id() == 6 || ($vehiculo->getCve_id() == 5 && $vehiculo->getCve_id() == 2) ? '
            <button id="regresarM" epg_id="' . $vehiculo->getEpg_id() . '" ecf_id="' . $vehiculo->getEcf_id() . '" type="button" class="btn btn-outline-secondary regresar me-1" title="Regresar al menú mantenimiento"><i class="fa-solid fa-circle-left me-2"></i>Regresar</button>
            ' : '<button id="regresar" epg_id="' . $vehiculo->getEpg_id() . '" ecf_id="' . $vehiculo->getEcf_id() . '" type="button" class="btn btn-outline-secondary regresar me-1" title="Regresar al menú"><i class="fa-solid fa-circle-left me-2"></i>Regresar</button>') . '
            ' . ($this->escritura ? '<button id="guardar" epg_id="' . $vehiculo->getEpg_id() . '" ecf_id="' . $vehiculo->getEcf_id() . '" type="button" class="btn btn-outline-success me-1" title="Guardar costeo"><i class="fa fa-floppy-disk me-2"></i>Guardar</button>' : '') . '
            ' . ($this->escritura ? ($vehiculo->getCed_id() != 0 ? '<button id="eliminar" epg_id="' . $vehiculo->getEpg_id() . '" ecf_id="' . $vehiculo->getEcf_id() . '" type="button" class="btn btn-outline-danger me-1" title="Eliminar Costeo"><i class="fa-solid fa-trash me-2"></i>Eliminar</button>' : '') : '') . '
        ');
        $html = '';
        $html .= '
            <form role="form" id="frmVehiculo" name="frmVehiculo" action="' . $this->pantalla . '">
                <div class="row mb-4">
                    <fieldset class="col-md-12 col-xs-12">' . ObjetoGasto::ponObjetoGasto($objGto) . '</fieldset>
                </div>
                <div>
                    <fieldset class="col-md-12">
                    ' . $variable['html'] . '
                    </fieldset>
                </div>
                <Calendario id="vehiculo"></Calendario>
                <br /><br />
            </form>
        ';

        $this->mixHtml($html);
        $this->mixScript($variable['script']);

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
            switch( ' . $vehiculo->getCve_id() . ' ){
                case 1: case 2: case 3:
                    $("#vehiculo").calendarioMontos({
                        montos: ' . json_encode($vehiculo->getMontos()->getArrayMeses()) . '
                    });

                    if( unformatNumber( $(".totm").val() ) != 0 )
                        $("#totAnual").val( $(".totm").val() );

                    if( ' . $vehiculo->getCed_id() . ' == "0" )
                        $(".mes").attr("disabled","disabled");

                    break;

                case 4: case 6:
                    if(' . $vehiculo->getCve_id() . ' == 6 && ' . $vehiculo->getPve_id() . ' == 1)
                        $("#vehiculo").calendario({
                        unidades: ' . json_encode($vehiculo->getUnidades()->getArrayMeses()) . ',
                        montos: ' . json_encode($vehiculo->getMontos()->getArrayMeses()) . ',
                        unitario: unformatNumber( $("#costoUnitario").val() ),
                        formato:  "real",
                        etiquetaUni: \'Núm. de Llantas\'
                    });

                    else
                        $("#vehiculo").calendario({
                        unidades: ' . json_encode($vehiculo->getUnidades()->getArrayMeses()) . ',
                        montos: ' . json_encode($vehiculo->getMontos()->getArrayMeses()) . ',
                        unitario: unformatNumber( $("#costoUnitario").val() ),
                        formato: "real",
                        etiquetaUni: \'Núm. de Servicios\'
                        });

                    if( unformatNumber( $(".totu").val() ) != 0 )
                        $("#totAnual").val( $(".totu").val() );

                    if( ' . $vehiculo->getCed_id() . ' == "0" )
                        $(".mes.mesu").attr("disabled","disabled");

                    break

                case 5:
                    switch( ' . $vehiculo->getPve_id() . ' ){
                        case 2:
                            var etiquetaU = \'Núm. de Servicios\';
                            break;
                        default:
                            var etiquetaU = \'Litros\';
                            break;
                    }
                    $("#vehiculo").calendario({
                        unidades: ' . json_encode($vehiculo->getUnidades()->getArrayMeses()) . ',
                        montos: ' . json_encode($vehiculo->getMontos()->getArrayMeses()) . ',
                        unitario: unformatNumber( $("#costoUnitario").val() ),
                        etiquetaUni: etiquetaU
                    });

                    $("#costoUnitario").blur( function() {
                        $("#vehiculo").calendario({
                            unitario: unformatNumber( $("#costoUnitario").val() )
                        });
                    });

                    if( unformatNumber( $(".totu").val() ) != 0 )
                        $("#totAnual").val( $(".totu").val() );

                    if( ' . $vehiculo->getCed_id() . ' == "0" )
                        $(".mes").attr("disabled","disabled");

                    switch( ' . $vehiculo->getPve_id() . ' ){
                        case 1:
                            $(".mes.mesu").blur( function(){
                                $(".mes.mesu").each(function(){
                                    n = unformatNumber($(this).val());
                                    $(this).val(n);
                                })

                                if( ' . $this->prm->civ_id . ' != 5 ){
                                    if(' . $this->clasAdmGpo . $this->clasAdmUr . ' != 101 && ' . $this->clasAdmGpo . $this->clasAdmUr . ' != 568){
                                        if( unformatNumber( $(this).val() ) >= ' . $this->litrosCombustible($this->prm) . '/12 )
                                        $(this).val( ' . $this->litrosCombustible($this->prm) . '/12 );
                                    }
                                    if( unformatNumber( $(this).val() ) < 0 )
                                    $(this).val(0);
                                }else{
                                    // if( unformatNumber( $(this).val() ) >= $("#totAnual").val()/12 )
                                        // $(this).val( $("#totAnual").val()/12 );

                                    if( unformatNumber( $(this).val() ) < 0 )
                                        unformatNumber( $(this).val(0) );
                                }

                                $(".totu").val( unformatNumber( $(".mes.mesu.ene").val() ) + unformatNumber( $(".mes.mesu.feb").val() ) + unformatNumber( $(".mes.mesu.mar").val() ) + unformatNumber( $(".mes.mesu.abr").val() ) + unformatNumber( $(".mes.mesu.may").val() ) + unformatNumber( $(".mes.mesu.jun").val() ) + unformatNumber( $(".mes.mesu.jul").val() ) + unformatNumber( $(".mes.mesu.ago").val() ) + unformatNumber( $(".mes.mesu.sep").val() ) + unformatNumber( $(".mes.mesu.oct").val() ) + unformatNumber( $(".mes.mesu.nov").val() ) + unformatNumber( $(".mes.mesu.dic").val() ) );
                                $(".mes.mesm.ene").val( formatNumber( unformatNumber ( $(".mes.mesu.ene").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.feb").val( formatNumber( unformatNumber ( $(".mes.mesu.feb").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.mar").val( formatNumber( unformatNumber ( $(".mes.mesu.mar").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.abr").val( formatNumber( unformatNumber ( $(".mes.mesu.abr").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.may").val( formatNumber( unformatNumber ( $(".mes.mesu.may").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.jun").val( formatNumber( unformatNumber ( $(".mes.mesu.jun").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.jul").val( formatNumber( unformatNumber ( $(".mes.mesu.jul").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.ago").val( formatNumber( unformatNumber ( $(".mes.mesu.ago").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.sep").val( formatNumber( unformatNumber ( $(".mes.mesu.sep").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.oct").val( formatNumber( unformatNumber ( $(".mes.mesu.oct").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.nov").val( formatNumber( unformatNumber ( $(".mes.mesu.nov").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".mes.mesm.dic").val( formatNumber( unformatNumber ( $(".mes.mesu.dic").val() ) * unformatNumber( $("#costoUnitario").val() ), "$ " ) );
                                $(".totm").val( formatNumber( unformatNumber( $(".mes.mesm.ene").val() ) + unformatNumber( $(".mes.mesm.feb").val() ) + unformatNumber( $(".mes.mesm.mar").val() ) + unformatNumber( $(".mes.mesm.abr").val() ) + unformatNumber( $(".mes.mesm.may").val() ) + unformatNumber( $(".mes.mesm.jun").val() ) + unformatNumber( $(".mes.mesm.jul").val() ) + unformatNumber( $(".mes.mesm.ago").val() ) + unformatNumber( $(".mes.mesm.sep").val() ) + unformatNumber( $(".mes.mesm.oct").val() ) + unformatNumber( $(".mes.mesm.nov").val() ) + unformatNumber( $(".mes.mesm.dic").val() ), "$ " ) );

                            });
                        break
                    }
                break
            }

            Mousetrap.bind("ctrl+enter", function(e) {
                e.preventDefault();
                $("#guardar").click();
            });

            $("#guardar").click( function(){
                var obj = {"metodo":"abcCedula"};
                if( ' . $vehiculo->getCed_id() . ' != 0 ){
                    console.log("Actualizar " + ' . $vehiculo->getCed_id() . ' );
                    obj.abc = "c";
                }else{
                    console.log("Insertar " + ' . $vehiculo->getCve_id() . ' );
                    obj.abc = "a";
                }

                obj.pqv_id = "' . $vehiculo->getPqv_id() . '";
                obj.pve_id = ' . $vehiculo->getPve_id() . ';
                obj.cve_id = ' . $vehiculo->getCve_id() . ';
                obj.pg_id = $("#pg_id").val();
                obj.pe_id = $("#pe_id").val();
                obj.ced_id = ' . ($vehiculo->getCed_id() * 1) . ';
                obj.civ_id = ' . $this->prm->civ_id . ';
                obj.epg_id = $("#guardar").attr("epg_id");
                obj.ecf_id = $("#guardar").attr("ecf_id");

                if ($("#pg_id").val() == "") {
                    notify("Debe seleccionar la partida generica", "error");
                    return false;
                } else if ($("#pe_id").val() == "") {
                    notify("Debe seleccionar la partida especifica", "error");
                    return false;
                }

                switch( ' . $vehiculo->getCve_id() . ' ){
                    case 1:
                        console.log("Impuestos");
                        obj.costoUnitario = unformatNumber( $("#tenencia").val() );
                        obj.importe = unformatNumber( $("#engomado").val() );
                        obj.otrosImportes = unformatNumber( $("#otrosImpuestos").val() );
                        obj.unidades = ["1", "1", "1", "1", "1", "1", "1", "1", "1", "1", "1", "1"];
                        obj.montos = $("#vehiculo").data("wr-calendarioMontos").options.montos;

                        if( parseFloat( obj.costoUnitario + obj.importe + obj.otrosImportes ).toFixed(2) != parseFloat( unformatNumber( $(".totm").val() ) ).toFixed(2) ){
                            notify("El total de impuestos no coincide con el total calendarizado", "error");
                            return false;
                        }

                        if( unformatNumber( $(".totm").val() ) == 0 ){
                                notify("Hay informacion sin capturar, No hay montos calendarizados", "error");
                                return false;
                        }
                        break

                    case 2: case 3:
                        console.log("Seguro");
                        obj.aseguradora = $("#nombreAseguradora").val();
                        obj.poliza = $("#numeroPoliza").val();
                        obj.unidades = ["1", "1", "1", "1", "1", "1", "1", "1", "1", "1", "1", "1"];
                        obj.montos = $("#vehiculo").data("wr-calendarioMontos").options.montos;
                        obj.costoUnitario = parseFloat( unformatNumber( $("#totAnual").val() ) ).toFixed(2);

                        if( parseFloat( unformatNumber( $("#totAnual").val() ) ).toFixed(2) != parseFloat( unformatNumber( $(".totm").val() ) ).toFixed(2) ){
                            notify("El total de la poliza anual no coincide con el total calendarizado", "error");
                            return false;
                        }

                        if( unformatNumber( $(".totm").val() ) == 0 ){
                            notify("Hay informacion sin capturar, No hay montos calendarizados", "error");
                            return false;
                        }
                        break

                    case 4: case 6:
                        console.log("Mantenimiento");
                        obj.costoUnitario = unformatNumber( $("#costoUnitario").val() );
                        obj.unidades = $("#vehiculo").data("wr-calendario").options.unidades;
                        obj.montos = $("#vehiculo").data("wr-calendario").options.montos;

                        if( ' . $vehiculo->getPve_id() . ' == 1 ){
                            if( parseFloat( unformatNumber( $("#totAnual").val() ) ).toFixed(2) != parseFloat( unformatNumber( $(".totu").val() ) ).toFixed(2) ){
                                notify("El total de llantas anuales no coincide con el total calendarizado", "error");
                                return false;
                            }
                        }
                        else{
                            if( parseFloat( unformatNumber( $("#totAnual").val() ) ).toFixed(2) != parseFloat( unformatNumber( $(".totu").val() ) ).toFixed(2) ){
                                notify("El total de los servicios anuales no coincide con el total calendarizado", "error");
                                return false;
                            }
                        }

                        if( unformatNumber( $(".totu").val() ) == 0 ){
                            notify("No hay servicios calendarizados", "error");
                            return false;
                        }
                        break

                    case 5:
                        console.log("Combustible " + $("#tco_id").val() );

                        $(".mes.mesu").blur();
                        $(".mes.mesm").blur();

                        obj.costoUnitario = unformatNumber( $("#costoUnitario").val() );
                        obj.unidades = $("#vehiculo").data("wr-calendario").options.unidades;
                        obj.montos = $("#vehiculo").data("wr-calendario").options.montos;

                        switch( ' . $vehiculo->getPve_id() . ' ){
                            case 1:
                                obj.tco_id = $("#tco_id").val();
                                obj.combustible = $("#combustible").val();

                                
                                if(' . $this->clasAdmGpo . $this->clasAdmUr . ' != 101 && ' . $this->clasAdmGpo . $this->clasAdmUr . ' != 568 ){      
                                    if( unformatNumber( $(".totu").val() ) == 0 ){
                                        notify("No hay litros calendarizados", "error");
                                        return false;
                                    }
                                    if( parseFloat( unformatNumber( $("#totAnual").val() ) ).toFixed(2) != parseFloat( unformatNumber( $(".totu").val() ) ).toFixed(2) ){
                                        notify("El total de litros anuales no coincide con el total calendarizado", "error");
                                        return false;
                                    }
                                }
                                break
                            case 2:
                                obj.tco_id = $("#tco_id").val();
                                obj.combustible = $("#combustible").val();

                                if( parseFloat( unformatNumber( $("#totAnual").val() ) ).toFixed(2) != parseFloat( unformatNumber( $(".totu").val() ) ).toFixed(2) ){
                                    notify("El total de servicios anuales no coincide con el total calendarizado", "error");
                                    return false;
                                }

                                if( unformatNumber( $(".totu").val() ) == 0 ){
                                    notify("No hay servicios calendarizados", "error");
                                    return false;
                                }
                            break
                        }
                    break
                }

                if (!validarPresupuesto()) {
                    e.preventDefault(); // Detiene el envío
                }

                console.log( obj );
                comunica("' . $this->pantalla . '",
                    obj
                );
            });

            if( "' . $vehiculo->getCed_id() . '" != "0" ){
                $("#eliminar").click( function(){
                    alertify.confirm("ELIMINAR CÉDULA DE VEHÍCULOS", "¿ Confirma que desea eliminar la cédula de costeo <h5 style=\"display:inline\"></h5>?",
                        function(){
                            var obj = {"metodo":"abcCedula"};
                            obj.abc = "b";
                            obj.ced_id = ' . ($vehiculo->getCed_id() * 1) . ';
                            obj.pqv_id = "' . $vehiculo->getPqv_id() . '";
                            obj.pve_id = ' . $vehiculo->getPve_id() . ';
                            obj.cve_id = ' . $vehiculo->getCve_id() . ';
                            obj.civ_id = ' . $this->prm->civ_id . ';
                            obj.epg_id = $("#eliminar").attr("epg_id");
                            obj.ecf_id = $("#eliminar").attr("ecf_id");
                            comunica("' . $this->pantalla . '",
                            obj
                            );
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
                });
            }

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                $(".regresar").click();
                e.stopPropagation();
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "menuCosteo",
                    pqv_id: "' . $vehiculo->getPqv_id() . '",
                    pve_id: ' . $vehiculo->getPve_id() . ',
                    cve_id: ' . $vehiculo->getCve_id() . ',
                    civ_id: ' . $this->prm->civ_id . ',
                    ecf_id: "' . $vehiculo->getEcf_id() . '",
                    epg_id: "' . $vehiculo->getEpg_id() . '"
                });
            });

            $("#regresarM").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "menuMantenimiento",
                    pqv_id: "' . $vehiculo->getPqv_id() . '",
                    pve_id: ' . $vehiculo->getPve_id() . ',
                    cve_id: ' . $vehiculo->getCve_id() . ',
                    civ_id: ' . $this->prm->civ_id . ',
                    ecf_id: "' . $vehiculo->getEcf_id() . '",
                    epg_id: "' . $vehiculo->getEpg_id() . '"
                });
            });
        ');
    }

    // ** Formularios de los costeos ** //
    private function detalleImpuestos()
    {
        if (func_num_args() > 0) {
            if (is_object(func_get_arg(0))) {
                $prm = func_get_arg(0);
            } elseif (is_array(func_get_arg(0))) {
                $prm = (object) func_get_arg(0);
            } else {
                $prm = null;
            }
        } else {
            $prm = new stdClass();
        }

        // *** Generamos los elementos a utilizar *** //
        $inputTenencia = new StdClass();
        $inputTenencia->id = 'tenencia';
        $inputTenencia->name = 'tenencia';
        $inputTenencia->value = '$ ' . ($prm->vehiculo->tenencia != 0 ? $prm->vehiculo->tenencia : '0.00');
        $inputTenencia->placeholder = 'Tenencia';
        $inputTenencia->formato = 'moneda';
        $inputTenencia->positivo = true;
        $inputTenencia->enDiv = true;
        $inputTenencia->label = 'Tenencia';

        $inputEngomado = new StdClass();
        $inputEngomado->id = 'engomado';
        $inputEngomado->name = 'engomado';
        $inputEngomado->value = '$ ' . ($prm->vehiculo->engomado != 0 ? $prm->vehiculo->engomado : '0.00');
        $inputEngomado->placeholder = 'Engomado';
        $inputEngomado->formato = 'moneda';
        $inputEngomado->positivo = true;
        $inputEngomado->enDiv = true;
        $inputEngomado->label = 'Engomado, placas y tarjeta de circulaci&oacute;n';

        $inputOtrosImpuestos = new StdClass();
        $inputOtrosImpuestos->id = 'otrosImpuestos';
        $inputOtrosImpuestos->name = 'otrosImpuestos';
        $inputOtrosImpuestos->value = '$ ' . ($prm->vehiculo->otrosImpuestos != 0 ? $prm->vehiculo->otrosImpuestos : '0.00');
        $inputOtrosImpuestos->placeholder = 'Otros impuestos';
        $inputOtrosImpuestos->formato = 'moneda';
        $inputOtrosImpuestos->positivo = true;
        $inputOtrosImpuestos->enDiv = true;
        $inputOtrosImpuestos->label = 'Otros impuestos';

        $inputTotalAnual = new StdClass();
        $inputTotalAnual->id = 'totAnual';
        $inputTotalAnual->name = 'totAnual';
        $inputTotalAnual->placeholder = 'Importe total anual';
        $inputTotalAnual->enDiv = true;

        $inputTotalAnual->label = 'Importe total anual';
        $inputTotalAnual->readonly = true;
        $inputTotalAnual->disabled = true;

        if (!$this->escritura) { //Con tarea de solo lectura los campos deben ir readonly y deshabilitados
            $inputTenencia->readonly = true;
            $inputTenencia->disabled = true;
            $inputEngomado->readonly = true;
            $inputEngomado->disabled = true;
            $inputOtrosImpuestos->readonly = true;
            $inputOtrosImpuestos->disabled = true;
            $script = '';
        } else { //para la tarea de escritura debe calcularse de forma automatica el total
            $script = '
                $("#saldoDisponible").keydown(false);

                $("#tenencia, button[data-id=pg_id], button[data-id=pe_id]").blur( function() {
                    comunica("lib/ControladorFunciones.php",{
                        metodo: "muestraDisponible",
                        ecf: "' . $this->prm->ecf_id . '",
                        pg: $("#pg_id").val(),
                        pe: $("#pe_id").val()
                    },false,false);

                    var suma = unformatNumber( $("#tenencia").val() ) * 1 + unformatNumber( $("#engomado").val() ) * 1 + unformatNumber( $("#otrosImpuestos").val() ) * 1;
                    $("#totAnual").val( formatNumber( parseFloat( suma ).toFixed(2), "$ " ) );

                    if( unformatNumber( $("#tenencia").val() ) != 0 || unformatNumber( $("#engomado").val() ) != 0 || unformatNumber( $("#otrosImpuestos").val() ) != 0 ){
                        $(".mes").removeAttr("disabled");
                    }else{
                        $(".mes").attr("disabled","disabled");
                    }

                });

                $("#engomado").blur( function() {
                    var suma = unformatNumber( $("#tenencia").val() ) * 1 + unformatNumber( $("#engomado").val() ) * 1 + unformatNumber( $("#otrosImpuestos").val() ) * 1;
                    $("#totAnual").val( formatNumber( parseFloat( suma ).toFixed(2), "$ " ) );

                    if( unformatNumber( $("#tenencia").val() ) != 0 || unformatNumber( $("#engomado").val() ) != 0 || unformatNumber( $("#otrosImpuestos").val() ) != 0 ){
                        $(".mes").removeAttr("disabled");
                    }else{
                        $(".mes").attr("disabled","disabled");
                    }

                });

                $("#otrosImpuestos").blur( function() {
                    var suma = unformatNumber( $("#tenencia").val() ) * 1 + unformatNumber( $("#engomado").val() ) * 1 + unformatNumber( $("#otrosImpuestos").val() )* 1;
                    $("#totAnual").val( formatNumber( parseFloat( suma ).toFixed(2), "$ " ) );

                    if( unformatNumber( $("#tenencia").val() ) != 0 || unformatNumber( $("#engomado").val() ) != 0 || unformatNumber( $("#otrosImpuestos").val() ) != 0 ){
                        $(".mes").removeAttr("disabled");
                    }else{
                        $(".mes").attr("disabled","disabled");
                    }

                });

                setTimeout( function() {
                        $("button[data-id=pe_id]").blur();
                        $( "#tenencia" ).focus()
                    },
                    100
                );
            ';
        }

        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
            <legend>Información del registro
                <span style="float:  right;">
                    <input width=50 id="saldoDisponible" style="display: inline; width:300px; height: 30px; text-align:right;" class="form-control poaSaldoDisponible"  tabindex="-1" placeholder="Techo Presupuestal Disponible" />
                </span>
            </legend>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-12" id="objGto">
                    ' . $this->ponInput($inputTenencia) . '
                </fieldset>

                <fieldset class="col-md-6 col-xs-12" id="objGto">
                    ' . $this->ponInput($inputEngomado) . '
                </fieldset>
            </div>
            <div class="row mb-4">
            <fieldset class="col-md-6 col-xs-6" id="objGto">
                ' . $this->ponInput($inputOtrosImpuestos) . '
            </fieldset>

            <fieldset class="col-md-6 col-xs-6" id="objGto">
                ' . $this->ponInput($inputTotalAnual) . '
            </fieldset>
            </div>
            <legend class="mb-4 ">Calendarizaci&oacute;n</legend>
        ';

        $variable['html'] = isset($html) ? $html : '';
        $variable['script'] = isset($script) ? $script : '';

        return $variable;
    }

    private function detalleSeguros()
    {
        if (func_num_args() > 0) {
            if (is_object(func_get_arg(0))) {
                $prm = func_get_arg(0);
            } elseif (is_array(func_get_arg(0))) {
                $prm = (object) func_get_arg(0);
            } else {
                $prm = null;
            }
        } else {
            $prm = new stdClass();
        }
        // *** Generamos los elementos a utilizar *** //
        $inputAseguradora = new StdClass();
        $inputAseguradora->id = 'nombreAseguradora';
        $inputAseguradora->name = 'nombreAseguradora';
        $inputAseguradora->value = trim(isset($prm->vehiculo->aseguradora) && $prm->vehiculo->aseguradora != '' ? $prm->vehiculo->aseguradora : '') != '' ? $prm->vehiculo->aseguradora : '';
        $inputAseguradora->placeholder = 'Nombre de la aseguradora';
        $inputAseguradora->required = true;
        $inputAseguradora->enDiv = true;
        $inputAseguradora->label = 'Aseguradora';

        $inputNumeroPoliza = new StdClass();
        $inputNumeroPoliza->id = 'numeroPoliza';
        $inputNumeroPoliza->name = 'numeroPoliza';
        $inputNumeroPoliza->value = trim(isset($prm->vehiculo->poliza) && $prm->vehiculo->poliza != '' ? $prm->vehiculo->poliza : '') != '' ? $prm->vehiculo->poliza : '';
        $inputNumeroPoliza->placeholder = 'N&uacute;mero de P&oacute;liza';
        $inputNumeroPoliza->required = true;
        $inputNumeroPoliza->enDiv = true;
        $inputNumeroPoliza->label = 'N&uacute;mero de P&oacute;liza';

        $inputTotalAnual = new StdClass();
        $inputTotalAnual->id = 'totAnual';
        $inputTotalAnual->name = 'totAnual';

        $inputTotalAnual->placeholder = 'Importe Total Anual';
        $inputTotalAnual->formato = 'moneda';
        $inputTotalAnual->positivo = true;
        $inputTotalAnual->required = true;
        $inputTotalAnual->enDiv = true;
        $inputTotalAnual->maxlength = 15;
        $inputTotalAnual->label = 'Importe Total Anual';

        if (!$this->escritura) { //Con tarea de solo lectura los campos deben ir readonly y deshabilitados
            $inputAseguradora->readonly = true;
            $inputAseguradora->disabled = true;
            $inputNumeroPoliza->readonly = true;
            $inputNumeroPoliza->disabled = true;
            $inputTotalAnual->readonly = true;
            $inputTotalAnual->disabled = true;
        } else {
            $script = '
                $("#saldoDisponible").keydown(false);

                $("#nombreAseguradora, button[data-id=pg_id], button[data-id=pe_id]").blur( function() {
                    comunica("lib/ControladorFunciones.php",{
                        metodo:"muestraDisponible",
                        ecf: "' . $this->prm->ecf_id . '",
                        pg: $("#pg_id").val(),
                        pe: $("#pe_id").val()
                    },false,false);
                });

                $("#totAnual").blur( function() {
                    if( unformatNumber( $("#totAnual").val() ) != 0 ){
                        $(".mes").removeAttr("disabled");
                    }else{
                        $(".mes").attr("disabled","disabled");
                    }
                });
                setTimeout( function() {
                        $("button[data-id=pe_id]").blur();
                        $( "#nombreAseguradora" ).focus()
                    },
                    100
                );
            ';
        }

        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
            <legend class="mb-4 ">Información del registro
                <span style="float:  right;">
                    <input width=50 id="saldoDisponible" style="display: inline; width:300px; height: 30px; text-align:right;" class="form-control poaSaldoDisponible"  tabindex="-1" placeholder="Techo Presupuestal Disponible" />
                </span>
            </legend>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-12" id="objGto">
                    ' . $this->ponInput($inputAseguradora) . '
                </fieldset>

                <fieldset class="col-md-6 col-xs-12" id="objGto">
                    ' . $this->ponInput($inputNumeroPoliza) . '
                </fieldset>
            </div>
            <fieldset class="col-md-6 col-xs-6 mb-4" id="objGto">
                ' . $this->ponInput($inputTotalAnual) . '
            </fieldset>
            <legend class="mb-4 ">Calendarizaci&oacute;n</legend>
        ';

        $variable['html'] = isset($html) ? $html : '';
        $variable['script'] = isset($script) ? $script : '';

        return $variable;
    }

    private function detalleMantenimiento()
    {

        if (func_num_args() > 0) {
            if (is_object(func_get_arg(0))) {
                $prm = func_get_arg(0);
            } elseif (is_array(func_get_arg(0))) {
                $prm = (object) func_get_arg(0);
            } else {
                $prm = null;
            }
        } else {
            $prm = new stdClass();
        }
        // *** Generamos los elementos a utilizar *** //
        $inputServicios = new StdClass();
        $inputServicios->id = 'totAnual';
        $inputServicios->name = 'totAnual';
        $inputServicios->width = '200px';
        $inputServicios->formato = 'entero';
        $inputServicios->required = true;
        $inputServicios->enDiv = true;

        $servicios = '';
        switch ($prm->cve_id) {
            case 5:
                switch ($prm->pve_id) {
                    case 2:
                        $servicios = ' por servicio';
                        $inputServicios->placeholder = 'Total Anual de Servicios';
                        $inputServicios->label = 'N&uacute;mero Anual de Servicios';
                        break;
                    default:
                        $inputServicios->placeholder = 'Total Anual de Servicios';
                        $inputServicios->label = 'N&uacute;mero de Servicios';
                        break;
                }
                break;
            case 6:
                switch ($prm->pve_id) {
                    case 1:
                        $servicios = ' por servicio';
                        $inputServicios->placeholder = 'Total Anual de llantas';
                        $inputServicios->label = 'N&uacute;mero de llantas';
                        break;
                    default:
                        $inputServicios->placeholder = 'Total Anual de Servicios';
                        $inputServicios->label = 'N&uacute;mero de Servicios';
                        break;
                }
                break;
            default:
                $inputServicios->placeholder = 'Total Anual de Servicios';
                $inputServicios->label = 'N&uacute;mero de Servicios';
                break;
        }

        $inputCostoUnitario = new StdClass();
        $inputCostoUnitario->id = 'costoUnitario';
        $inputCostoUnitario->name = 'costoUnitario';
        $inputCostoUnitario->value = '$ ' . ($prm->vehiculo->costoUnitario != 0 ? $prm->vehiculo->costoUnitario : '0.00');
        $inputCostoUnitario->placeholder = 'Costo Unitario';
        $inputCostoUnitario->width = '200px';
        $inputCostoUnitario->formato = 'moneda';
        $inputCostoUnitario->positivo = true;
        $inputCostoUnitario->required = true;
        $inputCostoUnitario->enDiv = true;
        $inputCostoUnitario->label = 'Costo Unitario';

        if (!$this->escritura) { //Con tarea de solo lectura los campos deben ir readonly
            $inputCostoUnitario->disabled = true;
            $inputCostoUnitario->readonly = true;
            $inputServicios->disabled = true;
            $inputServicios->readonly = true;
        } else {
            $script = '
                $("#saldoDisponible").keydown(false);

                $("#totAnual, button[data-id=pg_id], button[data-id=pe_id]").blur( function() {
                    comunica("lib/ControladorFunciones.php",{
                        metodo:"muestraDisponible",
                        ecf: "' . $this->prm->ecf_id . '",
                        pg: $("#pg_id").val(),
                        pe: $("#pe_id").val()
                    },false,false);

                    if( unformatNumber( $("#totAnual").val() ) != 0 && unformatNumber( $("#costoUnitario").val() ) != 0 ){
                        $(".mes.mesu").removeAttr("disabled");
                    }else{
                        $(".mes.mesu").attr("disabled","disabled");
                    }
                });

                $("#costoUnitario").blur( function() {
                    if( unformatNumber( $("#costoUnitario").val() ) != 0 ){
                        $("#vehiculo").calendario({
                            unitario: unformatNumber( $("#costoUnitario").val() )
                        });
                    }

                    if( unformatNumber( $("#totAnual").val() ) != 0 && unformatNumber( $("#costoUnitario").val() ) != 0 ){
                        $(".mes.mesu").removeAttr("disabled");
                    }else{
                        $(".mes.mesu").attr("disabled","disabled");
                    }
                });

                setTimeout( function() {
                        $("button[data-id=pe_id]").blur();
                        $( "#totAnual" ).focus()
                    },
                    100
                );
            ';
        }

        $html = '
            <legend class="mb-4 ">Información del registro' . $servicios . '
                <span style="float:  right;">
                    <input width=50 id="saldoDisponible" style="display: inline; width:300px; height: 30px; text-align:right;" class="form-control poaSaldoDisponible"  tabindex="-1" placeholder="Techo Presupuestal Disponible" />
                </span>
            </legend>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-12" id="objGto">
                    ' . $this->ponInput($inputServicios) . '
                </fieldset>

                <fieldset class="col-md-6 col-xs-12" id="objGto">
                    ' . $this->ponInput($inputCostoUnitario) . '
                </fieldset>
            </div>
            <legend class="mb-4 ">Calendarizaci&oacute;n</legend>
        ';

        $variable['html'] = isset($html) ? $html : '';
        $variable['script'] = isset($script) ? $script : '';

        return $variable;
    }

    private function detalleCombustible()
    {
        if (func_num_args() > 0) {
            if (is_object(func_get_arg(0))) {
                $prm = func_get_arg(0);
            } elseif (is_array(func_get_arg(0))) {
                $prm = (object) func_get_arg(0);
            } else {
                $prm = null;
            }
        } else {
            $prm = new stdClass();
        }
        // *** Generamos los elementos a utilizar *** //

        $cboTcombustible = new StdClass();
        $cboTcombustible->sql = '
            SELECT tco_id, tco_nombre, tco_costo
            FROM cat_TipoCombustible
            ' . ($this->prm->civ_id == 5 ? 'WHERE tco_id = 5' : 'WHERE tco_id NOT IN ( 0, 5 ) ');

        $cboTcombustible->id = 'tco_id';
        $cboTcombustible->name = 'tco_id';
        $cboTcombustible->comoAtr = 'tco_costo';
        $cboTcombustible->selecciona = $prm->vehiculo->tco_id;
        $cboTcombustible->required = true;
        $cboTcombustible->enDiv = true;
        $cboTcombustible->class = 'mb-4 ';
        $cboTcombustible->label = 'Tipo de Combustible';

        $inputCombustible = new StdClass();
        $inputCombustible->id = 'combustible';
        $inputCombustible->name = 'combustible';
        $inputCombustible->placeholder = 'Especifique el tipo de Combustible';
        $inputCombustible->required = true;
        $inputCombustible->enDiv = true;
        $inputCombustible->label = 'Nombre del Combustible';

        $inputTotAnualLts = new StdClass();
        $inputTotAnualLts->id = 'totAnual';
        $inputTotAnualLts->name = 'totAnual';
        $inputTotAnualLts->placeholder = 'Litros anuales';
        $inputTotAnualLts->formato = 'entero';
        $inputTotAnualLts->required = true;
        $inputTotAnualLts->enDiv = true;
        $inputTotAnualLts->label = 'Cantidad Anual de Litros';

        $inputCostoCombustible = new StdClass();
        $inputCostoCombustible->id = 'costoUnitario';
        $inputCostoCombustible->name = 'costoUnitario';
        $inputCostoCombustible->placeholder = 'Capture el precio por Litro';
        $inputCostoCombustible->value = ($prm->vehiculo->costoUnitario != 0 ? $prm->vehiculo->costoUnitario : '0.00');
        $inputCostoCombustible->formato = 'moneda';
        $inputCostoCombustible->positivo = true;
        $inputCostoCombustible->required = true;
        $inputCostoCombustible->enDiv = true;
        $inputCostoCombustible->label = 'Costo Unitario';
        $inputCostoCombustible->readonly = true;
        $inputCostoCombustible->disabled = true;

        if (!$this->escritura) { //Con tarea de solo lectura los campos deben ir readonly
            $cboTcombustible->readonly = true;
            $cboTcombustible->disabled = true;
            $inputCombustible->readonly = true;
            $inputCombustible->disabled = true;
            $inputTotAnualLts->readonly = true;
            $inputTotAnualLts->disabled = true;
        } else {
            $script = '
                $("#saldoDisponible").keydown(false);

                $("#totAnual, button[data-id=pg_id], button[data-id=pe_id]").blur( function() {
                    comunica(
                        "lib/ControladorFunciones.php",
                        {
                        metodo:"muestraDisponible",
                        ecf: "' . $this->prm->ecf_id . '",
                        pg: $("#pg_id").val(),
                        pe: $("#pe_id").val()
                        },
                        false,
                        false);

                    if( ' . $this->prm->civ_id . ' != 5 ){
                        if( $("#totAnual").val() > ' . $this->litrosCombustible($prm) . ' ){
                            $("#totAnual").val( ' . $this->litrosCombustible($prm) . ' )
                        }
                    }else{
                        $("#totAnual").val();
                    }

                    if( unformatNumber( $("#totAnual").val() ) != 0 && unformatNumber( $("#costoUnitario").val() )*1 != 0 ){
                        $(".mes.mesu").removeAttr("disabled");
                    }else{
                        $(".mes.mesu").attr("disabled","disabled");
                    }

                    precio = $("#tco_id").find("option:selected").attr("tco_costo");

                    if( unformatNumber( $("#totAnual").val() ) != 0 && precio != 0 ){
                        $(".mes.mesu").removeAttr("disabled");
                    }else{
                        $(".mes.mesu").attr("disabled","disabled");
                    }

                    if( precio != 0 ){
                        $("#costoUnitario").val( precio );
                        $("#costoUnitario").blur();
                    }

                });

                $("#costoUnitario").blur( function(){
                    if( unformatNumber( $("#costoUnitario").val() ) != 0 ){
                        $("#vehiculo").calendario( {
                            unitario: unformatNumber( $(this).val() )
                        });
                    }

                    if( unformatNumber( $("#totAnual").val() ) != 0 ){
                        $(".mes.mesu").removeAttr("disabled");
                    }else{
                        $(".mes.mesu").attr("disabled","disabled");
                    }
                });

                $("#tco_id").change( function(){
                    precio = $(this).find("option:selected").attr("tco_costo");
                    if( unformatNumber( $("#totAnual").val() ) != 0 && precio != 0 ){
                        $(".mes.mesu").removeAttr("disabled");
                    }else{
                         if(' . ($this->clasAdmGpo . $this->clasAdmUr) . '!=101 && ' . ($this->clasAdmGpo . $this->clasAdmUr) . ' != 568){
                             $(".mes.mesu").attr("disabled","disabled");
                         }else{
                            $(".mes.mesu").prop("disabled", false);
                            $(".mes.mesu").removeAttr("disabled"); // Por si acaso     
                            setTimeout(function() { $(".mes.mesu").prop("disabled", false);}, 50);
                        }
                    }

                    if( precio != 0 ){
                        $("#costoUnitario").val( precio );
                        $("#costoUnitario").blur();
                    }
                });

                setTimeout( function() {
                        $("button[data-id=pe_id]").blur();
                        $( "#totAnual" ).focus()
                    },
                    100
                );
            ';
        }

        $html = '
            <legend class="mb-4 ">Información del registro
                <span style="float:  right;">
                    <input width=50 id="saldoDisponible" style="display: inline; width:300px; height: 30px; text-align:right;" class="form-control poaSaldoDisponible"  tabindex="-1" placeholder="Techo Presupuestal Disponible" />
                </span>
            </legend>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-12" id="objGto">
                    ' . $this->ponCombo($cboTcombustible) . '

                    ' . $this->ponInput($inputCostoCombustible) . '
                </fieldset>

        ';

        if (($this->clasAdmGpo . $this->clasAdmUr) != '101' && ($this->clasAdmGpo . $this->clasAdmUr) != '568') {
            $html .= '
            <fieldset class="col-md-6 col-xs-12" id="objGto">
                ' . $this->ponInput($inputTotAnualLts) . '
            </fieldset>
            </div>    ';
        } else {
            $html .= ' </div> ';
        }
        $html .= ' <legend class="mb-4 ">Calendarizaci&oacute;n</legend> ';
        $variable['html'] = isset($html) ? $html : '';
        $variable['script'] = isset($script) ? $script : '';

        return $variable;
    }

    private function litrosCombustible()
    {
        if (func_num_args() > 0) {
            if (is_object(func_get_arg(0))) {
                $prm = func_get_arg(0);
            } elseif (is_array(func_get_arg(0))) {
                $prm = (object) func_get_arg(0);
            } else {
                $prm = null;
            }
        } else {
            $prm = new stdClass();
        }

        $ccb_litrosMaximos = 0;
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            SELECT
                cu.ccb_litrosMaximos
            FROM cat_ParqueVehicular p
                INNER JOIN cat_CilindrosVehiculo c USING( civ_id )
                INNER JOIN cat_UsoVehiculo u USING( usv_id )
                INNER JOIN cat_CuotaCombustible cu USING( civ_id, usv_id )
            WHERE p.pqv_id = :pqv ' . ($prm->civ_id == 5 ? '
                AND civ_id = 5' : '') . '
        ');
        $parametros = [
            'pqv' => $prm->pqv_id,
        ];
        $conexion->consultar($parametros);

        $conexion->desconectar();
        if ($row = $conexion->extraer()) {
            $ccb_litrosMaximos = $row['ccb_litrosMaximos'];
        }
        return $ccb_litrosMaximos * 12;
    }

    // ** Guarda, Modifica y Elimina la cedula ** //
    public function abcCedula()
    {
        $this->prm->abc = (isset($this->prm->abc)) ? $this->prm->abc : 'a';
        $this->prm->gpo_id = $this->clasAdmGpo;
        $this->prm->ur_id = $this->clasAdmUr;

        $this->prm->ue_id = recuperarUe(ClasificacionAdministrativa::getEpg());
        $this->prm->epg_id = (isset($this->prm->epg_id)) ? $this->prm->epg_id : 0;
        $this->prm->ecf_id = (isset($this->prm->ecf_id)) ? $this->prm->ecf_id : 0;

        VehiculoMaquina::abc($this->prm);
        if ($this->prm->abc != 'b') {
            $this->mixScript('
                notify("Costeo guardado exitosamente");
            ');
        } else {
            $this->mixScript('
                notify("Costeo eliminado exitosamente");
            ');
        }
        $this->determinaCosteo();
    }
}

if (isset($_SESSION[_S]['prm']['A']['3']['4']['1'])) {
    new ControladorVehiculos();
} else {
    WebResponse::sinPermisos();
}
