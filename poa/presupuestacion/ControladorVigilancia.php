<?php

/**
 * Description of ControladorVigilancia
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se estandarizan estilos botones : regresar, imprimir, guardar
 * Objective: Consultar y dar de alta nuevas cedulas de Vigilancia por Unidad Responsable
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos20
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 **/

//inicio de sesion y llamada a las librerias
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';
require_once __DIR__ . '/../../srv/ClasificacionFinanciamiento.php';
require_once __DIR__ . '/../../srv/ObjetoGasto.php';
require_once __DIR__ . '/../../ent/poa/ServicioVigilancia/ServicioVigilancia.php';
require_once __DIR__ . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once __DIR__ . '/../../lib/recuperarUe.php';

class ControladorVigilancia extends WebResponse
{
    protected int $nvj;
    protected int $adm_gpo;
    protected int $adm_ur;
    protected int $adm_ue;
    protected int $adm_prg;
    protected int $adm_spr;
    protected int $adm_py;
    protected int $adm_oa;
    protected UnidadResponsable $ur;
    protected int $cierre;
    protected int $estadoCosteo;
    public bool $lectura;
    public bool $escritura;
    public function __construct()
    {
        parent::__construct();
        //variables de session
        $this->nvj     = (int) (getSession('nvj_id') ?? '');
        $this->adm_gpo = (int) (getSession('clasAdm-gpo') ?? '');
        $this->adm_ur  = (int) (getSession('clasAdm-ur') ?? '');
        $this->adm_ue  = (int) (getSession('clasAdm-ue') ?? '');
        $this->adm_prg = (int) (getSession('clasAdm-prg') ?? '');
        $this->adm_spr = (int) (getSession('clasAdm-spr') ?? '');
        $this->adm_py  = (int) (getSession('clasAdm-py') ?? '');
        $this->adm_oa  = (int) (getSession('clasAdm-oa') ?? '');

        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->adm_gpo);
        $this->ur->setUr_id($this->adm_ur);
        $this->ur->select();
        $this->cierre = (int) $this->ur->getEur_id();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo(); // 1 = Abierto ; 2 = Cerrado

        // Asignación de permisos de lectura y escritura
        $this->escritura = tienePermiso('A.3.7.1') && tienePermiso('A.3.7.2')
            && ($this->nvj == 1 || $this->nvj == 2)
            && ($this->cierre == 4 && $this->estadoCosteo == 1);

        $this->lectura = tienePermiso('A.3.7.1');

        switch ($this->metodo) {
            case 'addModCedula':
                $this->addModCedula();
                break;
            case 'abcVigilancia':
                $this->abcVigilancia();
                break;
            case 'llenaTablaConceptos':
                $this->llenaTablaConceptos();
                break;
            default:
                $this->mainVigilancia();
                break;
        } // fin switch
        $this->returnJson();
    } // fin __construct

    public function mainVigilancia()
    {
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {"metodo":""} );
            ')
        );

        // Si la planeación NO está autorizada, no le permitimos modificar nada
        if ($this->cierre < 4) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>La Planeación NO ha sido autorizada. No se permite costear</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>'
            );
        }
        // Si el costeo está cerrado, avisamos
        if ($this->estadoCosteo == 2) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>'
            );
        }

        $html = '';
        // Si tiene derechos de escritura, puede registrar una nueva cédula de Vigilancia
        if ($this->escritura) {
            $nuevo = new stdClass();
            $nuevo->value = 'Nuevo';
            $nuevo->id = 'addModCedula';
            $nuevo->class = 'nuevo me-1';
            $nuevo->icon = 'fa-solid fa-plus me-2';
            $nuevo->color = 'azul';
        } elseif ($this->lectura) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>Solo tiene permiso de lectura</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            ');
        }

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->class = 'regresar';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->icon = 'circle-left me-2';

        $tabla = new stdClass();
        $tabla->id = 'tbl-Conceptos';
        $tabla->titulo = 'Conceptos Ingresados';
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConceptos';

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->texto = 'Ver / Editar';
        array_push($tr, $th);

        //Uso del inmueble
        $th = new stdClass();
        $th->texto = 'Programatico';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave Financiamiento';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Concepto';
        array_push($tr, $th);

        //Periodo de Contrato inicio
        $th = new stdClass();
        $th->texto = 'Costo mensual';
        array_push($tr, $th);

        //Periodo de Contrato fin
        $th = new stdClass();
        $th->texto = 'Tipo contrato';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Cantidad';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Importe anual';
        array_push($tr, $th);

        $trbody = [];

        //contenido ver
        $td = new stdClass();
        $td->tipo = "boton";
        if ($this->cierre == 4 && $this->escritura) {
            $td->icono = "pencil";
        } else {
            $td->icono = "eye";
        }
        $td->attr = new stdClass();
        $td->attr->ced_id = "ced_id";
        $td->attr->ccd_id = "ccd_id";
        $td->attr->pg_id = "pg_id";
        $td->attr->pe_id = "pe_id";
        $td->class = "addModCedula";
        $td->name = "ced_id";
        $td->id = "ced_id";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'programatico';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cf';
        array_push($trbody, $td);

        //AREA
        $td = new stdClass();
        $td->campoTxt = 'svg_area';
        array_push($trbody, $td);

        //COSTO MENSUAL
        $td = new stdClass();
        $td->campoTxt = 'tct_costoMensual';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        //TIPO DEL CONTRATO
        $td = new stdClass();
        $td->campoTxt = 'tct_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cantidad';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($html);
        $this->mixScript('
            $("#tituloPantalla").html("");
            $("#tituloPantalla").text("Servicios de vigilancia");
        ');

        if ($this->escritura) {
            $ponBtnNuevo = $this->ponBoton($nuevo);
        } else {
            $ponBtnNuevo = '';
        }

        $this->mixHtml($this->wrPonTabla($tabla));

        $this->agregaTools($ponBtnNuevo);
        $this->agregaTools('
            <a id="imprimirTodo" target="_blank" title="Imprimir Cédula de Vigilancia"style="padding: 0">
                <button type="button" class="btn btn-outline-danger me-2" >
                    <i class="fa fa-file-pdf me-2"></i>PDF
                </button>
            </a>'
        );
        $this->agregaTools('
            <input type="search" width=50
                id="filtroTabla"
                style="display: inline; width:250px; height: 30px;"
                class="form-control me-2"
                placeholder="Filtrar Cédulas de Vigilancia" />
        ');
        $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";

            $("#addModCedula").click(function(){
                comunica("' . $this->pantalla . '",{"metodo":"addModCedula"},false);
            });
            $("#imprimirTodo").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");
                firma= $(this).attr("firma");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 7))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();

            });
            $("#btnRegresarIndex").click(function(){
                $("nav").menusinpres("clickMenu");
            });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
                //location.reload();
                $("nav").menusinpres("clickMenu");
            });

                // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });

            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );
        ');

        $this->llenaTablaConceptos();
    } // fin mainVigilancia

    private function llenaTablaConceptos()
    {
        $parametros = [':gpo_id' => $this->adm_gpo, ':ur_id' => $this->adm_ur];

        $filtroUe = '';
        if ($this->nvj == 1) {
            $filtroUe = ' and a.ue_id = :ue_id ';
            $parametros[':ue_id'] = $this->adm_ue;
        }

        $sql = '
            SELECT a.ced_id, a.ccd_id,
                CONCAT( a.gpo_id, a.ur_id, "-", a.ue_id, "-", ep.prg_id, "-", ep.spr_id, "-", ep.py_id, "-", ep.oa_id ) AS programatico,
                CONCAT( cf.cff_id, "-", cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, "-", cf.cfi_id, cf.cfi_anio ) AS cf,
                t.tct_id, t.tct_nombre, t.tct_costoMensual, a.svg_area,
            FORMAT((a.uni_ene+ a.uni_feb+ a.uni_mar+ a.uni_abr+ a.uni_may+ a.uni_jun+ a.uni_jul+ a.uni_ago+ a.uni_sep+ a.uni_oct+ a.uni_nov+ a.uni_dic),0) AS cantidad,
                (a.uni_ene+ a.uni_feb+ a.uni_mar+ a.uni_abr+ a.uni_may+ a.uni_jun+ a.uni_jul+ a.uni_ago+ a.uni_sep+ a.uni_oct+ a.uni_nov+ a.uni_dic)*tct_costoMensual AS total, pg_id,pe_id
            FROM poa_ServicioVigilancia a
                JOIN enc_Programatico ep USING( epg_id )
                JOIN enc_ClaveFinanciamiento cf using (ecf_id)
                JOIN cat_TipoContrato t USING(tct_id)
            where a.gpo_id = :gpo_id and a.ur_id = :ur_id ' . $filtroUe;
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Conceptos';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        if ($this->wrLlenaTabla($nav) > 50) {
            $this->mixScript('
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
            $(".addModCedula").click(function() {
                comunica("' . $this->pantalla . '",{
                metodo:"addModCedula",
                ced_id:$(this).attr("ced_id"),
                ccd_id:$(this).attr("ccd_id")
                },false);
                return false;
            });

            $(document).ready(function() {
                $("#tabla-tbl-Conceptos").filterTable({ // apply filterTable to all tables on this page
                    inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
                });
            });
        ');
    } // fin llenaTablaConceptos

    private function addModCedula()
    {
        $this->prm->cedula = (isset($this->prm->cedula)) ? $this->prm->cedula : '';
        // Si pasaron el número de cédula, es un cambio.. si no, le ponemos 0 como número
        $cedula = (isset($this->prm->ced_id) ? $this->prm->ced_id : 0);
        // Si es una nueva cédula, podrán elegir el encuadre programático y la clave de financiamiento
        if ($cedula == 0) {
            // Dibujamos la pantalla que permite buscar una cuenta
            $this->mixHtml(CatalogoProgramaticoFinanciamiento::ponF1());
            $encLectura = '';
            // Si no, sólo podrán ver lo que está registrado en la base de datos
        } else {
            $encLectura = ' disabled ';
        }

        $nombreCF = $valorCF = $nombreEpg = $valorEpg = $clave = '';

        $vigilancia = new ServicioVigilancia();

        if ($cedula != 0) {
            $vigilancia->setCed_id($this->prm->ced_id);
            $vigilancia->select();
            // Ponemos la clasificación administrativa registrada en la cédula
            $this->adm_ue = $vigilancia->getUe_id();

            // Ponemos la clave de financiamiento registrada en la cédula
            ClasificacionFinanciamiento::PonEnVariablesEcf($vigilancia->getEcf_id());
            $abc = "c";
            $clave .= '<div align="center"><h4><strong><small><ins>' . $_SESSION[_S]['cvefin']->cveFin . '-' . $_SESSION[_S]['cvefin']->nombre . '</ins></small></strong></h1></div>';

            $this->mixScript("
                $('#comoSeUsa').click(function(){
                    alertify.alert('Cómo seleccionar encuadres...','<h5>Cuando te coloques sobre los campos de <b>Encuadre Programático</b> y <b>Clave de Financiamiento</b> verás que el campo cambia de color a verde.   Esto significa que puedes presionar la tecla <b>F1</b> o <b>F2</b> para seleccionar los encuadres.<b>Método 1</b>:Si presionas F1 verás la lista de todas las combinaciones de encuadre programático y claves de financiamiento existentes.Selecciona con doble click la que quieras utilizar y se rellenarán ambos cambos (programático y clave de financiamiento).<b>Método 2:Si presionas F2 activarás la seleccion por combos.En éste caso debes colocarte sobre el campo de <b>Encuadre Programático</b> y presionar F2 para seleccionar cada combo del encuadre.Cuando hayas seleccionado todo el encuadre (hasta Obra/Actividad) cierra la ventana y colócate en el campo de <b>Clave de Financiamiento</b>.   Presiona F2 para mostrar la ventana que te permite seleccionar la Clave de Financiamento.</h5>',function(){  });
                });
            ");
            // Si no, es una nueva cédula
        } else {
            $abc = "a";
            $clave .= '';
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $clasAdmin->filtro = "poa";
            $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
        }

        $objGto = new stdClass();
        $objGto->ccd_id = 7;
        $objGto->filtro = 'costeo';

        if ($vigilancia->getCed_id() != '') {
            $objGto->lectura = true;
            $objGto->pe_id = $vigilancia->getPe_id();
            $objGto->pg_id = $vigilancia->getPg_id();
        }
        if (isset($this->prm->pg_id) && $this->prm->pg_id != '') {
            $objGto->lectura = true;
            $objGto->pg_id = $this->prm->pg_id;
            $objGto->pe_id = $this->prm->pe_id;
        }

        $cveFin = new stdClass();
        $cveFin->ecf_id = ClasificacionFinanciamiento::getEcf();
        $cveFin->horizontal = 3;
        $cveFin->lectura = true;

        $svg_area = new stdClass();
        $svg_area->id = 'svg_area';
        $svg_area->placeholder = "Area de vigilancia";
        $svg_area->label = "Area de vigilancia";
        $svg_area->width = '300px';
        $svg_area->height = '100';
        $svg_area->maxLength = 150;
        $svg_area->enDiv = true;
        $svg_area->required = true;
        $svg_area->texto = str_replace('"', '&quot;', (string) $vigilancia->getArea());

        $tct_id = new stdClass();
        $tct_id->sql = 'SELECT tct_id, tct_nombre,tct_costoMensual FROM cat_TipoContrato';
        $tct_id->id = "tct_id";
        $tct_id->name = 'tct_id';
        $tct_id->label = 'Tipo de contrataci&oacute;n';
        $tct_id->mensaje = 'Selecciona un tipo de contrataci&oacute;n';
        $tct_id->required = true;
        $tct_id->comoAtr = 'tct_costoMensual';
        $tct_id->selecciona = $vigilancia->getTct_id();

        $tct_costoMensual = new stdClass();
        $tct_costoMensual->id = 'tct_costoMensual';
        $tct_costoMensual->placeholder = "Costo mensual";
        $tct_costoMensual->required = true;
        $tct_costoMensual->readonly = true;
        $tct_costoMensual->maxLength = 15;
        $tct_costoMensual->enDiv = true;
        $tct_costoMensual->label = "Costo mensual";
        $tct_costoMensual->formato = "moneda";

        $mesu = '';

        if ($this->nvj > 2) {
            $svg_area->readonly = true;
            $tct_id->readonly = true;
            $tct_costoMensual->readonly = true;
            $mesu = '$(".mesu").attr("disabled","disabled");';
        }

        $eliminar = '';

        switch ($this->nvj) {
            case 1:case 2:
                if ($this->cierre == 4 && $this->escritura && isset($this->prm->ced_id) && $this->prm->ced_id != '') {
                    $btnEliminar = new stdClass();
                    $btnEliminar->id = 'deleteCedula';
                    $btnEliminar->class = 'deleteCedula me-1';
                    $btnEliminar->color = 'rojo';
                    $btnEliminar->text = 'Eliminar';
                    $btnEliminar->value = 'Eliminar';
                    $btnEliminar->icon = 'solid fa-trash me-2';

                    $eliminar = $this->ponBoton($btnEliminar);

                } else {
                    $eliminar = "";
                }
                break;
        } // fin switch

        if ($this->nvj <= 2) {
            $btnGuardarVigilancia = new stdClass();
            $btnGuardarVigilancia->id = 'btnGuardarVigilancia';
            $btnGuardarVigilancia->text = 'Guardar';
            $btnGuardarVigilancia->type = 'button';
            $btnGuardarVigilancia->value = 'Guardar';
            $btnGuardarVigilancia->icon = 'fa fa-floppy-disk me-2';
            $btnGuardarVigilancia->class = "Guardar me-1";
            $btnGuardarVigilancia->color = "verde";
        }

        $botones = $html = '';

        $html .= '
            <form id="vigilancia">
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . ObjetoGasto::ponObjetoGasto($objGto) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    <span style="float:  right; right">
                        <input width=50
                            id="saldoDisponible"
                            style="display: inline; width:300px; height: 30px; text-align:right;"
                            class="form-control poaSaldoDisponible"
                            tabindex="-1"
                            placeholder="Techo Presupuestal Disponible" />
                    </span>
                </fieldset>
            </div>
        ';

        $html .= '
            <legend>Información del registro</legend>
                <div class="row mb-4">
                    <fieldset class="col-md-4 col-xs-4">
                        ' . $this->ponCombo($tct_id) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $this->ponInput($tct_costoMensual) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponTextArea($svg_area) . '
                    </fieldset>
                </div>
        ';
        $html .= '
            <fieldset class="col-md-12">
                <legend>Calendarizacion</legend>
                <Calendario id="arr"></Calendario>
            </fieldset>
        ';
        $html .= '<div id="contenedorCFModal"></div>';

        $botones .= '
            <button class="btn btn-outline-secondary me-1" value="Regresar" id="btnRegresarIndex">
                <span class="fa fa-circle-left me-2"></span>Regresar
            </button>';

        if ($this->nvj <= 2 && $this->cierre == 4 && $this->escritura) {
            $botones .= ' ' . $this->ponBoton($btnGuardarVigilancia) . ' ';
        }

        $botones .= '	   ' . $eliminar . ' ';
        $botones .= '<a id="imprimirTodo" epg_id="' . $vigilancia->getEpg_id() . '" ecf_id="' . $vigilancia->getEcf_id() . '" target="_blank"  title="Imprimir Cédula de Vigilancia" style="padding: 0"><button type="button" class="btn btn-outline-danger me-2" ><i class="fa fa-file-pdf me-2"></i>PDF</button></a>';

        $this->mixHtml($html);
        $this->agregaTools($botones);

        $uni = $vigilancia->getUnidades()->getArrayMeses();
        $mon = $vigilancia->getMontos()->getArrayMeses();

        $ced_id = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? 'ced_id:"' . $this->prm->ced_id . '",' : '';
        $cedula = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? $this->prm->ced_id : '';

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

            $("#imprimirTodo").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");
                firma= $(this).attr("firma");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 7))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();

            });
            //bloquearClasAdmin();
            $("#arr").calendario({etiquetaUni:"Elementos",
            etiquetaMon:"Importe",
            unidades:' . (json_encode($uni)) . ',montos:' . (json_encode($mon)) . ',
            unitario:$("#tct_costoMensual").val()});

            $("#tct_id").change(function(){
                if( $("#tct_id").val().length)
                    $("#tct_costoMensual").val($(this).find("option:selected").attr("tct_costoMensual"));
                else $("#tct_costoMensual").val(0);
                $("#tct_costoMensual").blur();

            });

            $("#tct_costoMensual").blur(function(){
                $("#arr").calendario({
                    unitario:parseFloat(unformatNumber($("#tct_costoMensual").val() ).toFixed(2))
                });
            });

            ' . $mesu . '

            $("#tct_id").change();


            $("#btnGuardarVigilancia").click(function(){
                //$(this).attr("disabled","disabled");

                //validaciónde encuadre p. y clave f.
                if($("#encProg").val()=="" || $("#encProg").attr("enc")==""){
                    notify("Debe seleccionar un encuadre programatico","error");
                    return false;
                } else if ($("#nomCveFin").val()=="") {
                    notify("Debe seleccionar una clave de financiamiento","error");
                    return false;
                }

                // checamos que hayan costeado al menos un mes:
                if ( ( $(".totu.entero").val().trim() == "0" || $(".totu.entero").val().trim() == "" ) ) {
                    notify( "Hay informacion sin rellenar, Debes costear al menos un mes", "error");
                    return false;
                }
                
                if (!validarPresupuesto()) {
                    e.preventDefault(); // Detiene el envío
                }

                var unidades = new Array();
                $(".mesu").each(function(index,val){
                    unidades[index]= unformatNumber($(this).val());
                });

                var montos = new Array();
                $(".mesm").each(function(index,val){
                    montos[index]= unformatNumber($(this).val());
                });

                var objArr = {
                    metodo:"abcVigilancia",
                    abc: "' . $abc . '",
                    ' . $ced_id . '
                    pg_id:$("#pg_id").val(),
                    pe_id:$("#pe_id").val(),
                    tct_id:$("#tct_id").val(),
                    tct_id:$("#tct_id").val(),
                    area:$("#svg_area").val(),
                    unidades:unidades,
                    montos:montos
                }

                if(!comunica("' . $this->pantalla . '", objArr,true ))
                    $(this).removeAttr("disabled");
                return false;
            });

            $("#encCveFin").keydown( function(event) {
                var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;

                /* F1 llama a la pantalla alternar para escoger encuadres */
                if ( key == 112 ) {
                    event.preventDefault();
                    dlgC.dialog("open");
                }else if( key == 113 ) {/* F2 llama a la pantalla modal para escoger clave de financiamiento */
                    // Solo seleccionamos éste encuadre cuando YA exista el programatico
                    if ( $("#encProg").val() != "" ) {
                        event.preventDefault();
                        comunica( "srv/ClasificacionFinanciamiento.php", {
                            metodoCveFin: "muestraCveFin"
                        });

                    } else {
                        notify("Debes seleccionar primero el Encuadre Programático", "warning");
                        setTimeout( function() { $( "#encProg" ).focus() }, 100 );
                    }
                } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                    notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                    e.preventDefault();
                    return false;

                }
            });

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                Mousetrap.unbind("ctrl+enter");
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            $("#btnRegresarIndex").click(function(e){
                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");
                e.stopPropagation();
            });

            // Atajos de ésta pantalla
            Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); $("#btnGuardarArrendamiento").click(); });

            $("#saldoDisponible").keydown(false);

            $("#encProg, button[data-id=pg_id], button[data-id=pe_id]").blur(function() {
                comunica("lib/ControladorFunciones.php",{
                    metodo:"muestraDisponible",
                    ecf: $("#encCveFin").attr("enc"),
                    pg: $("#pg_id").val(),
                    pe: $("#pe_id").val()
                });
            });

            $("#SitemaActivo").hide();
            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
        ');

        $this->mixScript("
            $('#deleteCedula').click(function(){
                ced_id = '". ($vigilancia->getCed_id()) . "';
                alertify.confirm('ELIMINAR CÉDULA DE VIGILANCIA','¿ Confirma que desea eliminar la cédula ' + ced_id +'?',
                function(){ comunica('" . $this->pantalla . "',{
                                        metodo: 'abcVigilancia',
                                        abc:'b',
                                        'ced_id':'" . ($vigilancia->getCed_id()) . "',
                                        'ccd_id':'7'
                                    });  }
                , function(){ }

                    ).set('labels', {ok:'Eliminar', cancel:'Cancelar'});
            });
        ");

        if ($cedula != 0) {
            $this->mixScript('
                $("#encProg").blur();
            ');
        }
    } // fin addModCedula

    //Falta que los filtros regresen a la pantalla correcta
    private function abcVigilancia()
    {
        switch ($this->prm->abc) {
            case 'c':
                $this->prm->bit_observacion = 'REGISTRO MODIFICADO DESDE PANTALLA A.3.7.1';
                ServicioVigilancia::abc($this->prm);
                $this->mixScript('
                    notify("Cédula de Vigilancia actualizada exitosamente");
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                ');
                break;
            case 'b':
                $this->prm->bit_observacion = 'REGISTRO ELIMINADO DESDE PANTALLA A.3.7.1';
                ServicioVigilancia::abc($this->prm);
                $this->mixScript('
                    notify("Cédula de Vigilancia eliminada exitosamente");
                    // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                ');
                break;
            case 'a':
                $this->prm->gpo_id = $this->adm_gpo;
                $this->prm->ur_id = $this->adm_ur;
                $this->prm->ue_id = recuperarUe(ClasificacionAdministrativa::getEpg());
                $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
                $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
                $this->prm->abc = 'a';
                $this->prm->bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA A.3.7.1';
                $this->prm->ced_id = ServicioVigilancia::abc($this->prm);

                $this->mixScript('
                    notify("Cédula de Vigilancia insertada exitosamente");
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                ');

                break;
        } // fin switch
    } //fin abcVigilancia
} // fin class

if (tienePermiso('A.3.7.1')) {
    new ControladorVigilancia();
} else {
    WebResponse::sinPermisos();
}
