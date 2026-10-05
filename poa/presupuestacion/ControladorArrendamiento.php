<?php
/**
 * Description of ControladorArrendamiento
 *
 * @author ?
 * @Collaborators Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se estandarizan estilos botones : regresar, imprimir, guardar
 * Objective: Consultar y dar de alta nuevas cedulas de Arrendamiento Terrenos, Edificios y Seguros/Fianzas por Unidad Responsable
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 */

//Inicio de sesion y llamada a las librerias
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../srv/ClasificacionFinanciamiento.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';
require_once __DIR__ . '/../../srv/ObjetoGasto.php';
require_once __DIR__ . '/../../ent/poa/Arrendamiento/Arrendamiento.php';
require_once __DIR__ . "/../../ent/poa/Arrendamiento/SegurosFianzas.php";
require_once __DIR__ . "/../../srv/CatalogoProgramaticoFinanciamiento.php";
require_once __DIR__ . '/../../lib/recuperarUe.php';

class ControladorArrendamiento extends WebResponse
{
    public int $nvj;
    public UnidadResponsable $ur;
    public int $cierre;
    public int $estadoCosteo;
    public bool $escritura;
    public bool $lectura;

    public function __construct()
    {
        parent::__construct();
        //Variables de session
        $this->nvj = getSession('nvj_id') ?? '';
        $gpoSesion = getSession('clasAdm-gpo') ?? '';
        $urSesion = getSession('clasAdm-ur') ?? '';

        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($gpoSesion);
        $this->ur->setUr_id($urSesion);
        $this->ur->select();
        $this->cierre = (int) $this->ur->getEur_id();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo(); // 1 = Abierto ; 2 = Cerrado
        $this->lectura = tienePermiso('A.3.5.1'); //Permiso de lectura, si existe el permiso retorna true, si no, false
        $this->escritura = tienePermiso('A.3.5.2'); //Permiso de escritura, si existe el permiso retorna true, si no, false
        $nvjId = getSession('nvj_id') ?? '';
        if (tienePermiso('A.3.5.1') && tienePermiso('A.3.5.2')
            && ($nvjId == 1 || $nvjId == 2)
            && ($this->cierre == 4 && $this->estadoCosteo == 1)
        ) {
            $this->escritura = true;
        } else {
            $this->escritura = false;
        }
        
        switch ($this->metodo) {
            case 'mainCedula':
                $this->mainCedula();
                break;
            case 'addModCedula':
                $this->addModCedula();
                break;
            case 'abcArrendamiento':
                $this->abcArrendamiento();
                break;
            case 'llenaTablaConceptos':
                $this->llenaTablaConceptos();
                break;
            default:
                $this->mainArrendamiento();
                break;
        }
        $this->returnJson();
    }

    public function mainArrendamiento()
    {
        $this->mixScript('$("#techos").show();');
        $this->agregaTools(" ");

        if ($this->estadoCosteo == 2) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" align="center">
                <strong>El Estado del costeo de la Unidad Responsable ha sido cerrado</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        $cardTerrenos = new stdClass();
        $cardTerrenos->id = 'terrenos';
        $cardTerrenos->titulo = 'Arrendamiento';
        $cardTerrenos->img = 'img/terrenos.png';
        $cardTerrenos->label = 'TERRENOS';
        $cardTerrenos->title = 'Arrendamiento de Terrenos';

        $cardEdificios = new stdClass();
        $cardEdificios->id = 'edificios';
        $cardEdificios->titulo = 'Arrendamiento';
        $cardEdificios->img = 'img/edificios.png';
        $cardEdificios->label = 'EDIFICIOS';
        $cardEdificios->title = 'Arrendamiento de Edificios';

        $cardFianzas = new stdClass();
        $cardFianzas->id = 'fianzas';
        $cardFianzas->titulo = 'Seguros y Fianzas';
        $cardFianzas->img = 'img/segurosedificios.png';
        $cardFianzas->label = 'EDIFICIOS';
        $cardFianzas->title = 'Seguros y Fianzas de Edificios';

        $this->mixHtml($this->ponCards([$cardTerrenos, $cardEdificios, $cardFianzas]));

        $this->mixScript('
            $("#terrenos").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"mainCedula",
                    cedula:"arrendamiento",
                    cve_id:2
                });
            });

            $("#edificios").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"mainCedula",
                    cedula:"arrendamiento",
                    cve_id:1
                });
            });

            $("#fianzas").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"mainCedula",
                    cedula:"seguros",
                    sef_tipo:1,
                    cve_id:1
                });
            });
        ');
    }

    public function mainCedula()
    {
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {"metodo":""} );
            ')
        );

        $html = '';
        $nuevo = '';
        //Si la planeación NO está autorizada, no le permitimos modificar nada
        if ($this->cierre < 4) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" align="center">
                <strong>La Planeación NO ha sido autorizada. No se permite costear</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }
        // Si el costeo está cerrado, avisamos
        if ($this->estadoCosteo == 2) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" align="center">
                <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></div>');
        }

        //AQUI VAMOS A ARMAR UN SWITCH PARA DEFINIR LOS TITULOS DE ARRENDAMIENTO Y SEGURO DE FIANZAS Y SI SE VAN A
        $filtroBarra = '';

        switch ($this->prm->cedula) {
            case "arrendamiento":
                switch ($this->prm->cve_id) {
                    case 1:
                        $filtroBarra = '
                            <input type="search"
                            width=50
                            id="filtroTabla"
                            style="display: inline; width:250px; height: 30px;"
                            class="form-control"
                            placeholder=" Arrendamiento de Edificios"/>
                        ';
                        break;
                    case 2:
                        $filtroBarra = '
                            <input type="search"
                            width=50
                            id="filtroTabla"
                            style="display: inline; width:250px; height: 30px;"
                            class="form-control"
                            placeholder="Arrendamiento de Terrenos"/>
                        ';
                        break;
                }
                break;
            case "seguros":
                switch ($this->prm->cve_id) {
                    case 1:
                        $filtroBarra = '
                            <input type="search"
                            width=50
                            id="filtroTabla"
                            style="display: inline; width:250px; height: 30px;"
                            class="form-control"
                            placeholder="Seguros y Fianzas de Edificios"/>
                        ';
                        break;
                    case 2:
                        $filtroBarra = '
                            <input type="search"
                            width=50
                            id="filtroTabla"
                            style="display: inline; width:250px; height: 30px;"
                            class="form-control"
                            placeholder="Seguros y Fianzas de Terrenos"/>
                        ';
                        break;
                }
                break;
        }
        $html .= '';

        // Si tiene derechos de escritura, puede registrar una nueva cédula de Arrendamiento
        if ($this->escritura) {
            $nuevo = new stdClass();
            $nuevo->value = 'Nuevo';
            $nuevo->id = 'addModCedula';
            $nuevo->class = 'me-1';
            $nuevo->color = 'azul';
            $nuevo->icon = 'solid fa-plus me-2';
        }

        $tabla = new stdClass();
        $tabla->id = 'tbl-Conceptos';
        $tabla->cabeceraFija = true;
        $tabla->titulo = 'Conceptos Ingresados';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->cve_id = (isset($this->prm->cve_id) && !empty($this->prm->cve_id)) ? $this->prm->cve_id : '';
        $tabla->comunica->cedula = (isset($this->prm->cedula) && !empty($this->prm->cedula)) ? $this->prm->cedula : '';
        $tabla->comunica->metodo = 'llenaTablaConceptos';

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->texto = 'Ver / Editar';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Programatico';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave Financiamiento';
        array_push($tr, $th);

        //campos divididos dependiendo de la cedula
        if ($this->prm->cedula == "arrendamiento") {
            $th = new stdClass();
            $th->texto = 'Uso del inmueble';
            array_push($tr, $th);
        } else {
            $th = new stdClass();
            $th->texto = 'Bien que requiere el seguro';
            array_push($tr, $th);
        }

        //Periodo de Contrato inicio
        $th = new stdClass();
        $th->texto = 'Periodo inicio';
        array_push($tr, $th);

        //Periodo de Contrato fin
        $th = new stdClass();
        $th->texto = 'Periodo termino';
        array_push($tr, $th);

        //Importe unidades
        if ($this->prm->cedula == "arrendamiento") {
            $th = new stdClass();
            $th->texto = 'Total unidades';
            array_push($tr, $th);
        } else {
            $th = new stdClass();
            $th->texto = 'Importe';
            array_push($tr, $th);
        }

        if ($this->prm->cedula == "seguros") {
            $th = new stdClass();
            $th->texto = 'Tipo';
            array_push($tr, $th);
        }

        if ($this->prm->cedula == "arrendamiento") {
            //Importe anual
            $th = new stdClass();
            $th->texto = 'Importe Anual';
            array_push($tr, $th);
        } else {
            $th = new stdClass();
            $th->texto = 'Total';
            array_push($tr, $th);
        }

        $trbody = [];
        //contenido ver
        $td = new stdClass();
        $td->tipo = "boton";
        if ($this->escritura) {
            $td->icono = "pencil";
        } else {
            $td->icono = "eye";
        }

        $td->attr = new stdClass();
        $td->attr->ced_id = "ced_id";
        $td->attr->ccd_id = "ccd_id";
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

        if ($this->prm->cedula == "arrendamiento") {
            $td = new stdClass();
            $td->campoTxt = 'arr_uso';
            array_push($trbody, $td);

            //Periodo de contrato inicio
            $td = new stdClass();
            $td->campoTxt = 'arr_inicioPeriodo';
            array_push($trbody, $td);

            //Periodo de contrato fin
            $td = new stdClass();
            $td->campoTxt = 'arr_terminoPeriodo';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'unidades';
            //$td->formato ='moneda';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'monto';
            $td->formato = 'moneda';
            array_push($trbody, $td);

        } else {
            $td = new stdClass();
            $td->campoTxt = 'sef_concepto';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'sef_inicioPeriodo';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'sef_terminoPeriodo';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'importe';
            $td->formato = 'moneda';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'sef_tipoPago';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'total';
            $td->formato = 'moneda';
            array_push($trbody, $td);
        }

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $ponBtnNuevo = '';
        if ($this->escritura) {
            $ponBtnNuevo = $this->ponBoton($nuevo);
        } elseif ($this->lectura) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>Solo tiene permiso de lectura</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            ');
        }

        $this->mixHtml($html);

        $this->agregaTools('
            <button class="btn btn-outline-secondary me-1" value="Regresar" name="btnRegresarIndex" id="btnRegresarIndex">
                <span class="fa-solid fa-circle-left me-2"></span>Regresar
            </button>');
        $this->agregaTools($ponBtnNuevo);
        if ($this->escritura) {
            $this->mixScript('
                $("#addModCedula").removeClass("btn btn-default");
                $("#addModCedula").addClass("btn btn-outline-primary me-1");
            ');
        }

        if (isset($this->prm->cedula) && $this->prm->cedula == 'seguros') {
            $this->agregaTools('
                <a id="imprimirTodo2" target="_blank" class="mr-3" title="Imprimir Cédula de Seguros" style="padding: 0" >
                    <button type="button" class="btn btn-outline-danger me-1 mr-4" >
                        <i class="fa fa-file-pdf me-2"></i>PDF
                    </button>
                </a>
            ');
        } else {
            $this->agregaTools('
                <a id="imprimirTodo" target="_blank" class="btn btn" title="Imprimir Cédula de Arrendamiento" style="padding: 0" >
                    <button type="button" class="btn btn-outline-danger me-1" >
                        <i class="fa fa-file-pdf me-2"></i>PDF
                    </button>
                </a>
            ');
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');

        if ($this->prm->cedula == "arrendamiento") {
            $this->agregaTools('' . $filtroBarra . ' ');
        } else {
            $this->agregaTools('' . $filtroBarra . ' ');
        }

        $this->llenaTablaConceptos();

        $param = '';
        $param = (isset($this->prm->cedula) && $this->prm->cedula == 'seguros') ? ' ,sef_tipo:"' . $this->prm->sef_tipo . '" ' : '';
        $this->mixScript('

            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";

            $("#addModCedula").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addModCedula",
                    cve_id:"' . $this->prm->cve_id . '",
                    cedula:"' . $this->prm->cedula . '"
                    ' . $param . '
                },false);
                return false;
            });

            $("#btnRegresarIndex").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "mainArrendamiento"
                });
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
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 5))
                    .append($("<input>").attr("type", "hidden").attr("name", "cve_id").attr("value", ' . $this->prm->cve_id . '))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();

            });

            $("#imprimirTodo2").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");
                firma= $(this).attr("firma");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 6))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();

            });

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
        ');
    } //Fin mainCedula

    private function llenaTablaConceptos()
    {
        $gpoId = getSession('clasAdm-gpo') ?? '';
        $urId = getSession('clasAdm-ur') ?? '';
        $ueId = getSession('clasAdm-ue') ?? '';

        $parametros = [':gpo_id' => $gpoId, ':ur_id' => $urId];

        $filtroUe = '';
        if ($this->nvj == 1) {
            $filtroUe = ' and a.ue_id = :ue_id ';
            $parametros[':ue_id'] = $ueId;
        }

        $filtro = '';
        switch ($this->prm->cedula) {
            case 'arrendamiento':
                switch ($this->prm->cve_id) {
                    case 1:
                        $filtro = ' and cve_id = :cve_id ';
                        $parametros[':cve_id'] = 1;
                        break;
                    case 2:
                        $filtro = ' and cve_id = :cve_id ';
                        $parametros[':cve_id'] = 2;
                        break;
                }
                break;
            case 'seguros':
                switch ($this->prm->cve_id) {
                    case 1:
                        $filtro = ' and cve_id = :cve_id and sef_tipo = :sef_tipo ';
                        $parametros[':cve_id'] = 1;
                        $parametros[':sef_tipo'] = 1;
                        break;
                    case 2:
                        $filtro = ' and cve_id = :cve_id and sef_tipo = :sef_tipo ';
                        $parametros[':cve_id'] = 2;
                        $parametros[':sef_tipo'] = 2;
                        break;
                }
                break;
        }

        switch ($this->prm->cedula) {
            case 'arrendamiento':
                $sql = '
                    SELECT a.ced_id,a.ccd_id,
                    CONCAT( a.gpo_id, a.ur_id, "-", a.ue_id, "-", ep.prg_id, "-", ep.spr_id, "-", ep.py_id, "-", ep.oa_id ) AS programatico,
                    CONCAT( cf.cff_id, "-", cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, "-", cf.cfi_id, cf.cfi_anio ) AS cf,
                    arr_inicioPeriodo,arr_terminoPeriodo,arr_uso,
                    (a.uni_ene + a.uni_feb + a.uni_mar + a.uni_abr + a.uni_may + a.uni_jun + a.uni_jul + a.uni_ago + a.uni_sep + a.uni_oct + a.uni_nov + a.uni_dic) unidades,
                    (a.mon_ene + a.mon_feb + a.mon_mar + a.mon_abr + a.mon_may + a.mon_jun + a.mon_jul + a.mon_ago + a.mon_sep + a.mon_oct + a.mon_nov + a.mon_dic) monto
                    FROM poa_Arrendamiento a
                    JOIN enc_Programatico ep USING( epg_id,gpo_id,ur_id )
                    JOIN enc_ClaveFinanciamiento cf using (ecf_id)
                    JOIN enc_PartEspCed pe USING(ccd_id,pg_id,pe_id)
                    JOIN enc_PartGenUr pg using(gpo_id,ur_id)
                    WHERE a.gpo_id = :gpo_id and a.ur_id = :ur_id ' . $filtroUe . $filtro . '
                    GROUP BY ccd_id,ced_id,a.gpo_id,a.ur_id,ue_id
                ';
                break;

            case 'seguros':
                $sql = '
                    SELECT a.ced_id, a.ccd_id,
                    CONCAT( a.gpo_id, a.ur_id, "-", a.ue_id, "-", ep.prg_id, "-", ep.spr_id, "-", ep.py_id, "-", ep.oa_id ) AS programatico,
                    CONCAT( cf.cff_id, "-", cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, "-", cf.cfi_id, cf.cfi_anio ) AS cf,
                    a.sef_concepto,a.sef_inicioPeriodo,a.sef_terminoPeriodo,a.sef_tipoPago,
                    if(sef_tipoPago = "A", sef_mensualidad, mon_ene) as importe,
                    a.mon_ene + a.mon_feb + a.mon_mar + a.mon_abr + a.mon_may + a.mon_jun + a.mon_jul + a.mon_ago + a.mon_sep + a.mon_oct + a.mon_nov + a.mon_dic as total
                    FROM poa_SeguroFianza a
                    JOIN enc_Programatico ep USING( epg_id,gpo_id,ur_id )
                    JOIN enc_ClaveFinanciamiento cf using (ecf_id)
                    JOIN enc_PartEspCed pe USING(ccd_id,pg_id,pe_id)
                    JOIN enc_PartGenUr pg using(gpo_id,ur_id)
                    where a.gpo_id = :gpo_id and a.ur_id = :ur_id ' . $filtroUe . $filtro . '
                    GROUP BY ccd_id,ced_id,a.gpo_id,a.ur_id,ue_id
                ';
                break;
        } //Fin switch
        $this->setParams($parametros);
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Conceptos';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;

        if ($this->wrLlenaTabla($nav) > 50) {
            $this->mixScript('
                $(".panel-heading.header").show();
                $(".panel-footer").show();
            ');
        } else {
            $this->mixScript('
                //Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").hide();
                $(".panel-footer").hide();
            ');
        }

        $param = '';
        $param = (isset($this->prm->cedula) && $this->prm->cedula == 'seguros') ? ' ,sef_tipo:"' . $this->prm->sef_tipo . '" ' : '';
        $this->mixScript('
            $(".addModCedula").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addModCedula",
                    ced_id:$(this).attr("ced_id"),
                    ccd_id:$(this).attr("ccd_id"),
                    cve_id:' . $this->prm->cve_id . ',
                    cedula:"' . $this->prm->cedula . '"
                    ' . $param . '
                    },false);
                return false;
            });

            $(document).ready(function() {
                $("#tabla-tbl-Conceptos").filterTable({ // apply filterTable to all tables on this page
                    inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
                });
            });
        ');
    } //Fin llenaTablaConceptos

    private function addModCedula()
    {
        //Este parametro solo es para saber que tipo de cedula viene si es de seguros o fianzas
        $this->prm->cedula = (isset($this->prm->cedula)) ? $this->prm->cedula : '';
        // Si pasaron el número de cédula, es un cambio si no, le ponemos 0 como número
        $cedula = (isset($this->prm->ced_id) ? $this->prm->ced_id : 0);

        // Si es una nueva cédula, podrán elegir el encuadre programático y la clave de financiamiento
        if ($cedula == 0) {
            //Dibujamos la pantalla que permite buscar una cuenta
            $this->mixHtml(CatalogoProgramaticoFinanciamiento::ponF1());
            //Si no, sólo podrán ver lo que está registrado en la base de datos
        }

        $objGto = new stdClass();
        switch ($this->prm->cedula) {
            case "arrendamiento":
                $arrendamiento = new Arrendamiento();
                if ($cedula != 0) {
                    $arrendamiento->setCed_id($this->prm->ced_id);
                    $arrendamiento->select();
                    $_SESSION[_S]['clasAdm-ue'] = $arrendamiento->getUe_id();
                    //Ponemos la clave de financiamiento registrada en la cédula
                    ClasificacionFinanciamiento::PonEnVariablesEcf($arrendamiento->getEcf_id());
                    $abc = "c";
                } else {
                    $abc = "a";
                    $clasAdmin = new stdClass();
                    $clasAdmin->required = true;
                    $clasAdmin->filtro = 'poa';
                    $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
                }

                $objGto = new stdClass();
                $objGto->ccd_id = 5;
                $objGto->cve_id = $this->prm->cve_id;
                $objGto->filtro = "costeo";
                if ($cedula != 0) {
                    $objGto->lectura = true;
                    $objGto->pg_id = $arrendamiento->getPg_id();
                    $objGto->pe_id = $arrendamiento->getPe_id();
                }

                $cveFin = new stdClass();
                $cveFin->horizontal = 3;
                $cveFin->lectura = true;
                $cveFin->ecf_id = $arrendamiento->getEcf_id();
                break;
            case "seguros":
                $sef = new SeguroFianza();
                if ($cedula != 0) {
                    $sef->setCed_id($this->prm->ced_id);
                    $sef->select();
                    //Ponemos la clasificación administrativa registrada en la cédula
                    $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($sef->getEpg_id());
                    $_SESSION[_S]['clasAdm-ue'] = $sef->getUe_id();

                    // Ponemos la clave de financiamiento registrada en la cédula
                    ClasificacionFinanciamiento::PonEnVariablesEcf($sef->getEcf_id());
                    $abc = "c";
                    //Si no, es una nueva cédula
                } else {
                    $abc = "a";
                    $clasAdmin = new stdClass();
                    $clasAdmin->filtro = 'poa';
                    $clasAdmin->required = true;
                    $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
                }

                $objGto = new stdClass();
                $objGto->ccd_id = 6;
                $objGto->cve_id = $this->prm->cve_id;
                $objGto->filtro = "costeo";
                if ($sef->getCed_id() != '') {
                    $objGto->lectura = true;
                    $objGto->pe_id = $sef->getPe_id();
                    $objGto->pg_id = $sef->getPg_id();
                }

                $cveFin = new stdClass();
                $cveFin->horizontal = 3;
                $cveFin->lectura = true;
                $cveFin->ecf_id = $sef->getEcf_id();
                break;
        } //Fin switch

        /*******************************************************
        CEDULA DE ARRENDAMIENTO
        *******************************************************/

        if ($this->prm->cedula == 'arrendamiento') {
            //Uso del inmueble
            $uso_inmueble = new stdClass();
            $uso_inmueble->id = 'uso';
            $uso_inmueble->label = 'Uso del Inmueble';
            $uso_inmueble->enDiv = true;
            $uso_inmueble->maxlength = '300';
            $uso_inmueble->required = true;
            $uso_inmueble->value = $arrendamiento->getUso();

            //Ubicacion del inmueble
            $ubi_inmueble = new stdClass();
            $ubi_inmueble->id = 'ubi';
            $ubi_inmueble->label = 'Ubicación del Inmueble';
            $ubi_inmueble->enDiv = true;
            $ubi_inmueble->maxlength = '300';
            $ubi_inmueble->required = true;
            $ubi_inmueble->value = $arrendamiento->getUbicacion();

            //Uso del inmueble
            $inicio = new stdClass();
            $inicio->id = 'inicio';
            $inicio->class = 'fecha';
            $inicio->label = 'Inicio';
            $inicio->enDiv = true;
            $inicio->formato = 'fecha';
            $inicio->labelClass = '_32 txtL';
            $inicio->required = true;
            $inicio->value = $arrendamiento->getInicioPeriodo();

            //Fecha de termino
            $termino = new stdClass();
            $termino->id = 'termino';
            $termino->class = 'fecha';
            $termino->label = 'Termino';
            $termino->enDiv = true;
            $termino->formato = 'fecha';
            $termino->labelClass = '_32 txtL';
            $termino->required = true;
            $termino->value = $arrendamiento->getTerminoPeriodo();

            //Renta mensual
            $rentaMensual = new stdClass();
            $rentaMensual->id = 'rentaMensual';
            $rentaMensual->label = 'Renta Mensual';
            $rentaMensual->enDiv = true;
            $rentaMensual->formato = 'moneda';
            $rentaMensual->required = true;
            $rentaMensual->value = $arrendamiento->getRentaMensual();

            //Observaciones
            $observaciones = new stdClass();
            $observaciones->id = 'observaciones';
            $observaciones->label = 'Observaciones';
            $observaciones->width = '300px';
            $observaciones->height = '100';
            $observaciones->enDiv = true;
            $observaciones->maxlength = '300';
            $observaciones->texto = $arrendamiento->getObservaciones();
        }

        if ($this->nvj <= 2) {
            $btnGuardarArrendamiento = new stdClass();
            $btnGuardarArrendamiento->id = 'btnGuardarArrendamiento';
            $btnGuardarArrendamiento->icon = 'fa-solid fa-floppy-disk me-2';
            $btnGuardarArrendamiento->text = 'Guardar';
            $btnGuardarArrendamiento->type = 'submit';
            $btnGuardarArrendamiento->value = 'Guardar';
        }

        /*******************************************************
        CEDULA DE SEGUROS Y FIANZAS
        ********************************************************/

        if ($this->prm->cedula == 'seguros') {
            //Bien que requiere el seguro
            $sef_concepto = new stdClass();
            $sef_concepto->id = 'sef_concepto';
            $sef_concepto->name = 'sef_concepto';
            $sef_concepto->width = '300px';
            $sef_concepto->height = '100';
            $sef_concepto->maxLength = 300;
            $sef_concepto->label = 'Bien que requiere el seguro';
            $sef_concepto->placeholder = "Bien que requiere el seguro";
            $sef_concepto->required = true;
            $sef_concepto->enDiv = true;
            $sef_concepto->text = $sef->getConcepto();

            //Inicio del periodo
            $inicio = new stdClass();
            $inicio->id = 'inicio';
            $inicio->class = 'fecha';
            $inicio->label = 'Del';
            $inicio->placeholder = "Fecha inicial";
            $inicio->enDiv = true;
            $inicio->formato = 'fecha';
            $inicio->labelClass = '_32 txtL';
            $inicio->required = true;
            $inicio->value = $sef->getInicioPeriodo();

            //Termino del periodo
            $termino = new stdClass();
            $termino->id = 'termino';
            $termino->class = 'fecha';
            $termino->label = 'Al';
            $termino->placeholder = "Fecha final";
            $termino->enDiv = true;
            $termino->formato = 'fecha';
            $termino->labelClass = '_32 txtL';
            $termino->required = true;
            $termino->value = $sef->getTerminoPeriodo();

            //Importe Mensual
            $importe = new stdClass();
            $importe->id = 'importe';
            $importe->name = 'importe';
            $importe->label = 'Importe';
            $importe->placeholder = "Importe ";
            $importe->maxLength = 15;
            $importe->required = true;
            $importe->enDiv = true;
            $importe->formato = 'moneda';
            $importe->value = $sef->getMensualidad();

            $tipopago = new stdClass();
            $tipopago->id = 'tipopago';
            $tipopago->opciones = ['M' => 'Mensual', 'A' => 'Anual'];
            $tipopago->label = 'Tipo de pago';
            $tipopago->enDiv = true;
            $tipopago->required = true;
            $tipopago->selecciona = $sef->getTipoPago();
        }

        $eliminar = '';
        $btnGuardar = '';
        switch ($this->nvj) {
            case 1:case 2:
                if ($this->escritura) {
                    if (isset($this->prm->ced_id) && $this->prm->ced_id != '') {
                        $btnEliminar = new stdClass();
                        $btnEliminar->id = 'deleteCedula';
                        $btnEliminar->text = 'Eliminar';
                        $btnEliminar->value = 'Eliminar';
                        $btnEliminar->icon = 'solid fa-trash me-2';

                        $eliminar = $this->ponBoton($btnEliminar);
                        $this->mixScript('
                            $("#deleteCedula").removeClass("btn btn-default");
                            $("#deleteCedula").addClass("btn btn-outline-danger me-1");
                        ');
                    }
                    $btnGuardar .= $this->ponBoton($btnGuardarArrendamiento);
                }
                break;
            case 3:case 4:case 5:case 6:case 7:case 8:
                if ($this->prm->cedula == 'seguros') {
                    $sef_concepto->readonly = true;
                    $inicio->readonly = true;
                    $termino->readonly = true;
                    $importe->readonly = true;
                    $tipopago->readonly = true;
                } elseif ($this->prm->cedula == 'arrendamiento') {
                    $uso_inmueble->readonly = true;
                    $ubi_inmueble->readonly = true;
                    $inicio->readonly = true;
                    $termino->readonly = true;
                    $rentaMensual->readonly = true;
                    $observaciones->readonly = true;
                }
                $btnGuardar .= '';
                break;
        } //Fin switch

        $botones = $html = '';

        $html .= '<form id="Arrendamiento">';
        $html .= '
        <br/>
        ';
        $html .= '
            <div class="row">
                <fieldset class="col-md-12 col-xs-12 mb-4">
                ' . ObjetoGasto::ponObjetoGasto($objGto) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                <span style="float:  right; right">
                    <input width=50
                        id="saldoDisponible"
                        style="display: inline; width:300px; height: 30px; text-align:right;"
                        class="form-control poaSaldoDisponible"
                        tabindex="-1"
                        placeholder="Techo Presupuestal Disponible" />
                </fieldset>
                </span>
            </div>
        ';

        $imprimir = '';
        switch ($this->prm->cedula) {
            case 'arrendamiento':
                $this->prm->ced_id = ($abc == 'a') ? '' : $this->prm->ced_id;
                $imprimir = '<a id="imprimirTodo" epg_id="' . $arrendamiento->getEpg_id() . '" ecf_id="' . $arrendamiento->getEcf_id() . '" cve_id="' . $this->prm->cve_id . '" ced_id="' . $this->prm->ced_id . '" target="_blank" class="btn btn-test" title="Imprimir Cédula de Arrendamiento" firma="0" style="padding: 0"><button type="button" class="btn btn-outline-danger me-1" ><i class="fa fa-file-pdf me-2"></i>PDF</button></a>';
                $html .= '
                    <div class="row mb-4">
                        <legend>Información del registro</legend>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($uso_inmueble) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($ubi_inmueble) . '
                        </fieldset>
                    </div>
                ';
                $html .= '
                    <div class="row">
                        <fieldset class="col-md-2 col-xs-2">
                            ' . $this->ponInput($inicio) . '
                        </fieldset>
                        <fieldset class="col-md-2 col-xs-2">
                            ' . $this->ponInput($termino) . '
                        </fieldset>
                        <fieldset class="col-md-2 col-xs-2">
                            ' . $this->ponInput($rentaMensual) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponTextArea($observaciones) . '
                        </fieldset>
                    </div>
                ';
                $html .= '
                    <legend>Calendarización</legend>
                    <div class="col-md-12 col-xs-12">
                        <fieldset class="col-md-12 col-xs-12">
                            <Calendario id="arr"></Calendario>
                        </fieldset>
                    </div>
                    <br><br>
                ';
                break;
            case 'seguros':
                $this->prm->ced_id = ($abc == 'a') ? '' : $this->prm->ced_id;
                $imprimir = '<a id="imprimirTodo2" epg_id="' . $sef->getEpg_id() . '" ecf_id="' . $sef->getEcf_id() . '" ced_id="' . $this->prm->ced_id . '" target="_blank" class="btn btn" title="Imprimir Cédula de Seguros" firma="0" style="padding: 0" ><button type="button" class="btn btn-outline-danger me-1" ><i class="fa fa-file-pdf me-2"></i>PDF</button></a>';
                $html .= '
                    <legend>Información del registro</legend>
                    <div class="row mb-4">
                        <fieldset class="col-md-12 col-xs-12 mb-4">
                            ' . $this->ponTextArea($sef_concepto) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($inicio) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($termino) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponCombo($tipopago) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($importe) . '
                        </fieldset>
                    </div>
                ';
                $html .= '
                    <legend>Calendarización</legend>
                    <div class="col-md-12 col-xs-12">
                        <fieldset class="col-md-12 col-xs-12">
                            <Calendario id="fianza"></Calendario>
                        </fieldset>
                    </div>
                    <br><br>
                ';
                break;
        } //Fin switch

        $html .= '
            <div id="contenedorCFModal"></div>
        ';
        
        $botones .= '
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <button class="btn btn-outline-secondary me-1" value="Regresar" id="btnRegresarIndex"><span class="fa-solid fa-circle-left me-2"></span>Regresar</button>
                    ' . $btnGuardar . '
                    ' . $eliminar . '
                    ' . $imprimir . '
                </fieldset>
            </div>
        ';

        $html .= '</form>';
        $this->agregaTools($botones);
        $this->mixHtml($html);
        if ($btnGuardar != "") {
            $this->mixScript('
                $("#btnGuardarArrendamiento").removeClass("btn btn-default");
                $("#btnGuardarArrendamiento").addClass("btn btn-outline-success me-1");
            ');
        }
        $this->mixScript('
            $("#imprimirTodo").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");
                cve_id= $(this).attr("cve_id");
                ced_id= $(this).attr("ced_id");
                firma= $(this).attr("firma");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "cve_id").attr("value", cve_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ced_id").attr("value", ced_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 5))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();

            });

            $("#imprimirTodo2").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");
                ced_id= $(this).attr("ced_id");
                firma= $(this).attr("firma");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ced_id").attr("value", ced_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 6))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
        
        if ($this->prm->cedula == "arrendamiento") {
            $mon = $arrendamiento->getMontos()->getArrayMeses();
            $ced_id = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? 'ced_id:' . $this->prm->ced_id . ',' : '';

            $cedula = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? $this->prm->ced_id : '';
            $bloqueo = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? 'bloquearClasAdmin();' : '';

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
                ' . $bloqueo . '

                $("#arr").calendarioMontos({
                    montos:' . (json_encode($mon)) . ',
                    unitario:parseFloat(unformatNumber($("#rentaMensual").val()).toFixed(2))
                });

                //MARCOS: Le cambié para que cada que CAMBIE haga el recalculo (porque estaba en el blur)
                $("#rentaMensual").change(function(){
                    var meses = $("#arr").data("wr-calendarioMontos").options.mes;
                    var mon = new Array();
                    var importe = parseFloat(unformatNumber($("#rentaMensual").val()).toFixed(2));
                    $.each(meses,function(index,value){
                    console.log(index);
                        var inicio = $("#inicio").val();
                        var mesI = inicio.split("/")[1];
                        var termino = $("#termino").val();
                        var mesT = termino.split("/")[1];
                        if( index >= (mesI-1) && index <= (mesT-1) ){
                            mon.push( {"mes":value,"value":importe});
                            $(this).removeAttr("disabled");
                            $(".mes").removeAttr("disabled");
                        }else{
                            $(".mes").prop("disabled",true);
                            $(".mes").val("0.00");
                        }
                    });
                    $("#arr").calendarioMontos({montos:mon});
                    $(".mes").blur();
                });
                // MARCOS: y en el blur solamente dejamos la parte que habilita los meses para costeo
                $("#rentaMensual").blur(function(){
                    var meses = $("#arr").data("wr-calendarioMontos").options.mes;
                    $.each(meses,function(index,value){
                        var inicio = $("#inicio").val();
                        var mesI = inicio.split("/")[1];
                        var termino = $("#termino").val();
                        var mesT = termino.split("/")[1];
                        if( index >= (mesI-1) && index <= (mesT-1) ){
                            $(".mes").removeAttr("disabled");
                        }else{
                            $(".mes").prop("disabled",true);
                        }
                    });
                });

                //Solo cuando se mueve la fecha de Inicio
                $("#inicio").change(function(){
                    var meses = $("#arr").data("wr-calendarioMontos").options.mes;
                    var mon = new Array();
                    var importe = parseFloat(unformatNumber($("#rentaMensual").val()).toFixed(2));
                    $.each(meses,function(index,value){
                    console.log(index);
                        var inicio = $("#inicio").val();
                        var mesI = inicio.split("/")[1];
                        var termino = $("#termino").val();
                        var mesT = termino.split("/")[1];
                        if( index >= (mesI-1) && index <= (mesT-1) ){
                            mon.push( {"mes":value,"value":importe});
                            $(this).removeAttr("disabled");
                            $(".mes").removeAttr("disabled");
                        }else{
                            $(".mes").prop("disabled",true);
                            $(".mes").val("0.00");
                        }
                    });
                    $("#arr").calendarioMontos({montos:mon});
                    $(".mes").blur();
                });

                //Solo cuando se mueve la fecha de Termino
                $("#termino").change(function(){
                    var meses = $("#arr").data("wr-calendarioMontos").options.mes;
                    var mon = new Array();
                    var importe = parseFloat(unformatNumber($("#rentaMensual").val()).toFixed(2));
                    $.each(meses,function(index,value){
                    console.log(index);
                        var inicio = $("#inicio").val();
                        var mesI = inicio.split("/")[1];
                        var termino = $("#termino").val();
                        var mesT = termino.split("/")[1];
                        if( index >= (mesI-1) && index <= (mesT-1) ){
                            mon.push( {"mes":value,"value":importe});
                            $(this).removeAttr("disabled");
                            $(".mes").removeAttr("disabled");
                        }else{
                            $(".mes").prop("disabled",true);
                            $(".mes").val("0.00");
                        }
                    });
                    $("#arr").calendarioMontos({montos:mon});
                    $(".mes").blur();
                });
                

                $("#btnGuardarArrendamiento").click(function(){
                    //validando los campos de encuadre y clave de financiamiento
                    if($("#encProg").val()=="" || $("#encProg").attr("enc")==""){
                        notify("Debe seleccionar un encuadre programatico","error");
                        return false;
                    } else if ($("#nomCveFin").val()=="") {
                        notify("Debe seleccionar una clave de financiamiento","error");
                        return false;
                    }

                    if (!validarPresupuesto()) {
                        e.preventDefault(); // Detiene el envío
                    }

                    //validando fechas
                    if(!validarFechas($("#inicio").val(), $("#termino").val())){
                            notify("La fecha de termino no puede ser menor a la fecha de inicio", "error");
                            return false;
                    } else {
                        // Marcos: lo desactivé porque si hay un error, ya lo deja inhabilitado
                        var unidades = new Array(1,1,1,1,1,1,1,1,1,1,1,1);
                        for (i = 0; i < 12; i++) {
                            if ( $("#arr").data("wr-calendarioMontos").options.montos[i].trim() == "" || $("#arr").data("wr-calendarioMontos").options.montos[i].trim() == "0" ) {
                                unidades[i] = 0;
                            }
                        }

                        var objArr =
                        {
                            metodo:"abcArrendamiento",
                            abc: "' . $abc . '",
                            cedula:"' . $this->prm->cedula . '",
                            cve_id:"' . $this->prm->cve_id . '",
                            ccd_id:5,
                            ' . $ced_id . '
                            pg_id:$("#pg_id").val(),
                            pe_id:$("#pe_id").val(),
                            uso:$("#uso").val(),
                            ubicacion:$("#ubi").val(),
                            inicioPeriodo:$("#inicio").val(),
                            terminoPeriodo:$("#termino").val(),
                            rentaMensual:unformatNumber($("#rentaMensual").val()),
                            observaciones:$("#observaciones").val(),
                            unidades:unidades,
                            montos:$("#arr").data("wr-calendarioMontos").options.montos
                        }
                        comunica("' . $this->pantalla . '", objArr,true );
                        return false;
                    }
                });

                $("#encCveFin").keydown( function(event) {
                    var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;

                    //F1 llama a la pantalla alternar para escoger encuadres
                    if ( key == 112 ) {
                            event.preventDefault();
                            dlgC.dialog("open");
                    }else if( key == 113 ) {//F2 llama a la pantalla modal para escoger clave de financiamiento
                        //Solo seleccionamos éste encuadre cuando YA exista el programatico
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

                // Atajos de ésta pantalla
                Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); $("#btnGuardarArrendamiento").click(); });

                $("#btnRegresarIndex").click(function(e){
                    // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    e.stopPropagation();
                    comunica("' . $this->pantalla . '",{
                        metodo: "mainCedula",
                        "cedula":"' . $this->prm->cedula . '",
                        "cve_id":"' . $this->prm->cve_id . '"
                    });
                });

                $("#deleteCedula").on("click", function(){
                    alertify.confirm("ELIMINAR CÉDULA DE ARRENDAMIENTO", "¿Confirma que desea eliminar la cédula?", function(){ 
                        comunica("' . $this->pantalla . '",{
                            metodo: "abcArrendamiento",
                            abc:"b",
                            "ced_id":"' . ($arrendamiento->getCed_id()) . '",
                            "cedula":"' . $this->prm->cedula . '",
                            "cve_id":"' . $this->prm->cve_id . '",
                            "ccd_id":"5"
                        });
                    }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });

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


                $(".mesm").attr("disabled","disabled");
                function validarFechas(fechaInicio, fechaTermino){
                    var arregloFechaInicio = fechaInicio.split("/");var arregloFechaTermino = fechaTermino.split("/");
                    var anioFechaInicio = arregloFechaInicio[2];var mesFechaInicio = arregloFechaInicio[1];
                    var diaFechaInicio = arregloFechaInicio[0];var anioFechaTermino = arregloFechaTermino[2];
                    var mesFechaTermino = arregloFechaTermino[1];var diaFechaTermino = arregloFechaTermino[0];
                    if(anioFechaInicio < anioFechaTermino){return true;}else if(anioFechaInicio > anioFechaTermino){
                    return false;}else{if(mesFechaInicio < mesFechaTermino){return true;}else if(mesFechaInicio > mesFechaTermino){
                    return false;}else{if(diaFechaInicio < diaFechaTermino){return true;}else{return false;}}}}
            ');
        } else {
            $ced_id = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? $this->prm->ced_id : '';
            $bloqueo = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? 'bloquearClasAdmin();' : '';
            $this->mixScript('
                ' . $bloqueo . '
                
                var unidades = new Array(0,0,0,0,0,0,0,0,0,0,0,0);
                var opcionTipoPago = "";

                $("#fianza").calendarioMontos({
                    montos:' . json_encode($sef->getMontos()->getArrayMeses()) . ',
                    unitario:$("#importe").val()
                });

                $("#tipopago").change(function(){
                    $(".totm").val("$ 0.00");
                    $("#importe").val("");
                    if( $("#tipopago").val()=="A"){
                        opcionTipoPago = "A";
                        $(".mesm").each(function (){
                            $(this).val("$ 0.00");
                            $(this).removeAttr("disabled");
                        });
                    } else {
                        opcionTipoPago = "M";
                        $(".mesm").each(function (){
                            $(this).val("$ 0.00");
                        });
                        $( "#importe" ).unbind("blur");
                        $("#importe").blur(function(){

                            if(opcionTipoPago == "M"){
                                unidades = new Array(1,1,1,1,1,1,1,1,1,1,1,1);
                                var meses = $("#fianza").data("wr-calendarioMontos").options.mes;
                                var mon = new Array();
                                var importes = parseFloat(unformatNumber($("#importe").val()).toFixed(2));
                                $.each(meses,function(index,value){
                                    console.log(value);
                                    mon.push( {"mes":value,"value":importes});
                                    $("."+value).attr("disabled","disabled");
                                    $(".mes").removeAttr("disabled");
                                });
                                console.log(mon);
                                $("#fianza").calendarioMontos({montos:mon});

                                $(".mes").blur();
                            }
                        });
                    }
                });

                $("#importe").change(function(){
                    if($("#tipopago").val() == "M"){
                        unidades = new Array(1,1,1,1,1,1,1,1,1,1,1,1);
                        var meses = $("#fianza").data("wr-calendarioMontos").options.mes;
                        var mon = new Array();
                        var importes = parseFloat(unformatNumber($("#importe").val()).toFixed(2));
                        $.each(meses,function(index,value){
                            console.log(value);
                            mon.push( {"mes":value,"value":importes});
                            $("."+value).attr("disabled","disabled");
                            $(".mes").removeAttr("disabled");
                        });
                        console.log(mon);
                        $("#fianza").calendarioMontos({montos:mon});

                        $(".mes").blur();
                    }
                });

                $(".mesm").focus( function(){
                    if ($("#tipopago").val() == "A") { //CAMBIO anteriormentre la variable no tenia valor 02/09/19
                        unidades = new Array(0,0,0,0,0,0,0,0,0,0,0,0);

                        var meses = $("#fianza").data("wr-calendarioMontos").options.mes;
                        var mon = new Array();
                        $.each(meses,function(index,value){
                            mon.push( {"mes":value,"value": 0});
                        });
                        $("#fianza").calendarioMontos({montos:mon});

                        if (($("#importe").val()) || parseFloat(unformatNumber($("#importe").val()).toFixed(2)) != 0) {
                            var valor = parseFloat(unformatNumber($(this).val()).toFixed(2));
                            $(".mesm").each(function (){
                                $(this).val("0.00");
                            });
                            $(this).val( unformatNumber($("#importe").val()).toFixed(2) );
                            $(".totm").val( "$ " + unformatNumber($("#importe").val()).toFixed(2) );
                            indice = $(".mesm").index(this);
                            unidades[indice] = 1;
                        } else {
                            //notify("Se debe capturar primero importe mensual", "warning");
                            $(this).val("0.00");
                        }
                    }
                });

                ' . (($ced_id == "") ? '$("#tipopago").change();' : '') . ' //cambio 02/09/19
                $( "#importe" ).val(' . $sef->getMensualidad() * 1 . ').blur();
                $(".mesm.dic").val( unformatNumber($(".mesm.dic").val()));
                $(".mesm.dic").blur();
                
                $("#encCveFin").keydown( function(event) {
                    var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;

                    //F1 llama a la pantalla alternar para escoger encuadres
                    if ( key == 112 ) {
                        event.preventDefault();
                        dlgC.dialog("open");
                    }else if( key == 113 ) {//F2 llama a la pantalla modal para escoger clave de financiamiento
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

                //Atajos de ésta pantalla
                Mousetrap.bind("ctrl+enter", function(e) { e.preventDefault(); $("#btnGuardarArrendamiento").click(); });

                $("#btnRegresarIndex").click(function(e){
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    e.stopPropagation();
                    comunica("' . $this->pantalla . '",{
                        metodo: "mainCedula",
                        "cedula":"' . $this->prm->cedula . '",
                        "sef_tipo":"' . $this->prm->sef_tipo . '",
                        "cve_id":"' . $this->prm->cve_id . '"
                    });
                });

                $("#btnGuardarArrendamiento").click(function(){
                    var cont = 0;
                    if(!validarFechas($("#inicio").val(), $("#termino").val())){
                        notify("La fecha de termino no puede ser menor a la fecha de inicio", "warning");
                        return false;
                    }
                     if (!validarPresupuesto()) {
                        e.preventDefault(); // Detiene el envío
                    }    
                    if ($("#tipopago").val() == "M" ) {//Se agrega validación para los importes
                        $(".mesm").each(function (){
                            if(unformatNumber($("#importe").val()).toFixed(2) != unformatNumber($(this).val()).toFixed(2) && unformatNumber($(this).val()).toFixed(2)!=0 ) {
                                cont++;
                            }
                        });
                        console.log("aqui"+cont);
                        if (cont > 0 ){
                            notify("El importe del calendario mensual no coincide con el importe asignado", "warning");
                            return false;
                        }
                    }
                    if (opcionTipoPago == "A"  && unformatNumber($("#importe").val()).toFixed(2) != unformatNumber($(".totm").val()).toFixed(2) ){
                        notify("El total  calendarizado no coincide con el importe", "warning");
                    } else {
                        //$(this).attr("disabled","disabled");
                        var obj = {"metodo":"abcArrendamiento"};
                        obj.abc = "' . $abc . '";
                        obj.cedula = "' . $this->prm->cedula . '";
                        obj.cve_id = "' . $this->prm->cve_id . '";
                        obj.tipo = "' . $this->prm->sef_tipo . '";
                        obj.ced_id= "' . $ced_id . '";
                        obj.concepto = $("#sef_concepto").val();
                        obj.mensualidad = unformatNumber($("#importe").val());
                        obj.inicioPeriodo = $("#inicio").val();
                        obj.terminoPeriodo = $("#termino").val();
                        obj.montos = $("#fianza").data("wr-calendarioMontos").options.montos;
                        obj.unidades=unidades;
                        obj.tipoPago = $("#tipopago").val();
                        obj.pg_id = $("#pg_id").val();
                        obj.pe_id = $("#pe_id").val();
                        console.log(obj);
                        if(!comunica("' . $this->pantalla . '", obj,true ))
                            $(this).removeAttr("disabled");
                        return false;
                    }

                });

                $("#deleteCedula").on("click", function(){
                    alertify.confirm("ELIMINAR CÉDULA DE ARRENDAMIENTO", "¿Confirma que desea eliminar la cédula?", function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "abcArrendamiento",
                            abc:"b",
                            "ced_id":"' . ($sef->getCed_id()) . '",
                            "cedula":"' . $this->prm->cedula . '",
                            "cve_id":"' . $this->prm->cve_id . '",
                            "sef_tipo":"' . $this->prm->sef_tipo . '",
                            "ccd_id":"6"
                        });
                    }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar",cancel:"Cancelar"});
                });

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

                function validarFechas(fechaInicio, fechaTermino){
                    var arregloFechaInicio = fechaInicio.split("/");
                    var arregloFechaTermino = fechaTermino.split("/");

                    var anioFechaInicio = arregloFechaInicio[2];
                    var mesFechaInicio = arregloFechaInicio[1];
                    var diaFechaInicio = arregloFechaInicio[0];

                    var anioFechaTermino = arregloFechaTermino[2];
                    var mesFechaTermino = arregloFechaTermino[1];
                    var diaFechaTermino = arregloFechaTermino[0];

                    if(anioFechaInicio < anioFechaTermino){
                        return true;
                    }else if(anioFechaInicio > anioFechaTermino){
                        return false;
                    }else{
                        if(mesFechaInicio < mesFechaTermino){
                            return true;
                        }else if(mesFechaInicio > mesFechaTermino){
                            return false;
                        }else{
                            if(diaFechaInicio < diaFechaTermino){
                                return true;
                            }else{
                                return false;
                            }
                        }
                    }

                }
            ');
        }

        if ($cedula != 0) {
            $this->mixScript('
                $("#encProg").blur();
            ');

        }
    } //Fin addModCedula

    private function abcArrendamiento()
    {
        $this->prm->gpo_id = getSession('clasAdm-gpo');
        $this->prm->ur_id = getSession('clasAdm-ur');

        //Recupera la UE de la obra actividad
        $this->prm->ue_id = recuperarUe(ClasificacionAdministrativa::getEpg());
        $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
        $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();

        switch ($this->prm->abc) {
            case 'c':
                $this->prm->bit_observacion = 'REGISTRO MODIFICADO DESDE PANTALLA';
                switch ($this->prm->cedula) {
                    case "arrendamiento":
                        $this->prm->totalUnidades = 1;
                        Arrendamiento::abc($this->prm);
                        $this->mixScript('
                            notify("Cédula de Arrendamiento actualizada exitosamente");
                            $("div[aria-describedby=cargaDatosProgFin]").html("");
                            comunica("' . $this->pantalla . '",{
                                metodo: "mainCedula",
                                "cedula":"' . $this->prm->cedula . '",
                                "cve_id":"' . $this->prm->cve_id . '"
                            });
                        ');
                        break;
                    case "seguros":
                        SeguroFianza::abc($this->prm);
                        $this->mixScript('
                            notify("Cédula de Seguros y Fianza actualizada exitosamente");
                            $("div[aria-describedby=cargaDatosProgFin]").html("");
                            comunica("' . $this->pantalla . '",{
                                metodo: "mainCedula",
                                "cedula":"' . $this->prm->cedula . '",
                                "sef_tipo":"' . $this->prm->tipo . '",
                                "cve_id":"' . $this->prm->cve_id . '"
                            });
                        ');
                        break;
                }
                break;
            case 'b':
                $this->prm->bit_observacion = 'REGISTRO ELIMINADO DESDE PANTALLA';
                switch ($this->prm->cedula) {
                    case "arrendamiento":
                        Arrendamiento::abc($this->prm);
                        $this->mixScript('
                            notify("Cédula de Arrendamiento eliminada exitosamente");
                            //Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                            $("div[aria-describedby=cargaDatosProgFin]").html("");
                            comunica("' . $this->pantalla . '",{
                                metodo: "mainCedula",
                                "cedula":"' . $this->prm->cedula . '",
                                "cve_id":"' . $this->prm->cve_id . '"
                            });
                        ');
                        break;
                    case "seguros":
                        SeguroFianza::abc($this->prm);
                        $this->mixScript('
                            notify("Cédula de Seguros y Fianza eliminada exitosamente");
                            //Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                            $("div[aria-describedby=cargaDatosProgFin]").html("");
                                comunica("' . $this->pantalla . '",{
                                    metodo: "mainCedula",
                                    "cedula":"' . $this->prm->cedula . '",
                                    "sef_tipo":"' . $this->prm->sef_tipo . '",
                                    "cve_id":"' . $this->prm->cve_id . '"
                                });
                        ');
                        break;
                }
                break;
            case 'a':
                $this->prm->bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA';
                switch ($this->prm->cedula) {
                    case "arrendamiento":
                        $this->prm->totalUnidades = 1;
                        $this->prm->ced_id = Arrendamiento::abc($this->prm);
                        $this->mixScript('
                                notify("Cédula de Arrendamiento insertada exitosamente");
                                $("div[aria-describedby=cargaDatosProgFin]").html("");
                                comunica("' . $this->pantalla . '",{
                                metodo: "mainCedula",
                                "cedula":"' . $this->prm->cedula . '",
                                "cve_id":"' . $this->prm->cve_id . '"
                            });
                            ');
                        break;
                    case "seguros":
                        //Si el tipo de Pago es Anual guarda el importe anual en el mes que elija el usuario
                        if ($this->prm->tipoPago == 'A') {
                            $idx = array_search(1, $this->prm->unidades);
                            if ($idx) {
                                $this->prm->montos[$idx] = $this->prm->mensualidad;
                            }
                        }

                        SeguroFianza::abc($this->prm);
                        $this->mixScript('
                                notify("Cédula de Seguros y Fianza insertada exitosamente");
                                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                                $("div[aria-describedby=cargaDatosProgFin]").html("");
                                comunica("' . $this->pantalla . '",{
                                    metodo: "mainCedula",
                                    "cedula":"' . $this->prm->cedula . '",
                                    "sef_tipo":"' . $this->prm->tipo . '",
                                    "cve_id":"' . $this->prm->cve_id . '"
                                });
                            ');
                        break;
                }
                break;
        } //Fin switch
    } //Fin abcArrendamiento
} //Fin class

if (tienePermiso('A.3.5')) {
    new ControladorArrendamiento();
} else {
    WebResponse::sinPermisos();
}
