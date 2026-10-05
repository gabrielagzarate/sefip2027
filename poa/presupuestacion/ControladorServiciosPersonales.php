<?php

/**
 * Description of ControladorServiciosPersonales
 *
 * @author ?
 * @update Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 02-jul-2020
 * Comment:
 * Se estandarizan estilos botones : regresar, imprimir, guardar
 * Objective: Consultar y dar de alta nuevas cedulas de Servicios Personales (Inventario Plazas, Presupuestación SP) por Unidad Responsable
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 * */

session_start();
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../ent/poa/ServiciosPersonales/ServiciosPersonales.php';
require_once __DIR__ . '/../../ent/InventarioPlazas.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';
require_once __DIR__ . '/../../srv/ClasificacionFinanciamiento.php';
require_once __DIR__ . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once __DIR__ . '/../../srv/ObjetoGasto.php';
require_once __DIR__ . '/../../lib/recuperarUe.php';
class ControladorServiciosPersonales extends WebResponse
{
    public $adm_ur;
    public $ur;
    public $adm_grupo;
    public $nvj;
    public $cierre;
    public $ue;
    public $usr_id;
    public $gpo;
    public $ur_id;
    public $adm_epg;
    public $adm_ue;
    public $estadoCosteo;
    public $escritura;
    public $lectura;

    public function __construct()
    {
        parent::__construct();

        //variables de session
        $this->nvj = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] != '') ? $_SESSION[_S]['nvj_id'] : '';
        $this->ue = (isset($_SESSION[_S]['ue_id']) && $_SESSION[_S]['ue_id'] != '') ? $_SESSION[_S]['ue_id'] : '';
        $this->usr_id = (isset($_SESSION[_S]['usr_id']) && $_SESSION[_S]['usr_id'] != '') ? $_SESSION[_S]['usr_id'] : '';
        $this->gpo = (isset($_SESSION[_S]['gpo_id']) && $_SESSION[_S]['gpo_id'] != '') ? $_SESSION[_S]['gpo_id'] : '';
        $this->ur_id = (isset($_SESSION[_S]['ur_id']) && $_SESSION[_S]['ur_id'] != '') ? $_SESSION[_S]['ur_id'] : '';
        $this->adm_grupo = (isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '') ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '') ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->adm_epg = (isset($_SESSION[_S]['clasAdm-epg']) && $_SESSION[_S]['clasAdm-epg'] != '') ? $_SESSION[_S]['clasAdm-epg'] : '';
        $this->adm_ue = (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') ? $_SESSION[_S]['clasAdm-ue'] : '';

        //Instanciando la Ur
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->adm_grupo);
        $this->ur->setUr_id($this->adm_ur);
        $this->ur->select();
        $this->cierre = $this->ur->getEur_id();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo(); // 1 = Abierto ; 2 = Cerrado

        // Asignación de permisos de lectura y escritura
        if (isset($_SESSION[_S]['prm']['A']['3']['2'][TAREA_ESCRITURA]) && ($this->nvj == 1 || $this->nvj == 2) && ($this->cierre == 4 && $this->estadoCosteo == 1)) {
            $this->escritura = true; //si tiene permisos de escritura
        } else {
            $this->escritura = false; //si no tiene permisos de escritura
        }
        if (isset($_SESSION[_S]['prm']['A']['3']['2'][TAREA_LECTURA])) {
            $this->lectura = true;
        }
        //si tiene permisos de lectura
        else {
            $this->lectura = false;
        }
        //si no tiene permisos de lectura

        switch ($this->metodo) {
            case 'addModCedula':
                $this->addModCedula();
                break;
            case 'abcInventario':
                $this->abcInventario();
                break;
            case 'abcSP':
                $this->abcSP();
                break;
            case 'seleccionaPartidaGenerica':
                $this->seleccionaPartidaGenerica();
                break;
            case 'seleccionaPartidaEspecifica':
                $this->seleccionaPartidaEspecifica();
                break;
            case 'detallePlazas':
                $this->detallePlazas();
                break;
            case 'llenaTablaConceptos':
                $this->llenaTablaConceptos();
                break;
            case 'presupuestacion':
                $this->presupuestacion();
                break;
            case 'detalleNiveles':
                $this->detalleNiveles();
                break;
            case 'programatico':
                $this->programatico();
                break;
            case 'cleanSession':
                $this->cleanSession();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    /******************* FUNCION PRINCIPAL ***********************************************************/
    public function main()
    {

        $this->agregaTools(' ');
        unset($_SESSION[_S]['cvefin']);
        $this->adm_epg = null;

        $this->mixScript('
                $("#techos").show();
                limpiaClasAdmin();
                DesactivaAtajosMenu();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

                $("#homeMenu").click( function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
                });
            ');

        if ($this->estadoCosteo == 2) {
            $this->mixHtml(
                '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>El Estado del costeo de la Unidad Responsable ha sido cerrado</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>'
            );
        }

        $cardInvetario = new stdClass();
        $cardInvetario->id = 'tel_fija';
        $cardInvetario->titulo = 'Inventario';
        $cardInvetario->img = 'img/inventarioplazas.png';
        $cardInvetario->label = 'Inventario Plazas';
        $cardInvetario->title = 'Inventario Plazas';

        $cardPlazas = new stdClass();
        $cardPlazas->id = 'tel_cel';
        $cardPlazas->titulo = 'Presupuestación';
        $cardPlazas->img = 'img/presupuestacionplazas.png';
        $cardPlazas->label = 'Presupuestación SP';
        $cardPlazas->title = 'Presupuestación SP';

        $this->mixHtml($this->ponCards([$cardInvetario, $cardPlazas]));

        $chec = '';
        $chec = new stdClass();
        $chec->id = 'afectaciones';
        $chec->name = 'afectaciones';
        $chec->checked = true;
        $chec->label = 'Servicios Personales';
        $chec->enDiv = true;
        $chec->orientacion = 'H';
        $chec->debug = false;

        $sep2 = ' $("#tel_fija").click(function(){
                    $("#SitemaActivo").hide();
                        comunica("' . $this->pantalla . '",{
                            metodo:"programatico"
                        });
                    });
                    ';
        $this->mixScript('
                $("#afectaciones").click(function(){
                    notify("click " + $(this).val(),"success" );
                });

                $("#SitemaActivo").show();
                DesactivaAtajosMenu();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#btnregresarindex").click(); e.stopPropagation(); });

                $("#tel_cel").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"presupuestacion",
                    });
                });
                ' . $sep2 . '
                $("#homeMenu").click( function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
                });
            ');
    } // fin main

    /******************* SELECCIONA PROGRAMATICO Y CLAVE DE FINANCIAMIENTO ***************************/
    public function programatico()
    {

        $clasAdmin = new stdClass();
        $clasAdmin->required = true;
        $clasAdmin->prg = false;
        $clasAdmin->spr = false;
        $clasAdmin->py = false;
        $clasAdmin->oa = false;

        $this->mixHtml(DibujaPantallaBuscarProgramaFinanciamientoHTML());
        $this->mixScript(DibujaPantallaBuscarProgramaFinanciamientoJS());
        $encLectura = $valorEpg = $nombreEpg = $valorCF = $nombreCF = $epg_id = $ecf_id = '';

        $epg_id = isset($this->adm_epg) ? $this->adm_epg : null;
        $ecf_id = isset($_SESSION[_S]['cvefin']->ecf) ? $_SESSION[_S]['cvefin']->ecf : null;

        if ($epg_id != '' && $ecf_id != '') {
            $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($epg_id);

            $valorEpg = $this->adm_grupo . $this->adm_ur . '.' . $this->adm_ue . '.' . $_SESSION[_S]['clasAdm-prg'] . '.' . $_SESSION[_S]['clasAdm-spr'] . '.' . $_SESSION[_S]['clasAdm-py'] . '.' . $_SESSION[_S]['clasAdm-oa'];

            ClasificacionFinanciamiento::PonEnVariablesEcf($ecf_id);
            $cso = isset($_SESSION[_S]['cvefin']->cso) ? $_SESSION[_S]['cvefin']->cso : '';
            $valorCF = $_SESSION[_S]['cvefin']->ter . '-' . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . $cso . '.' . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            $nombreCF = $_SESSION[_S]['cvefin']->nombre;
        }

        $parametros = new stdClass();
        $parametros->onSelect = $this->getJs();
        $parametros->cvefinBlur = $this->getJs();

        $html = '';
        $html .= CatalogoProgramaticoFinanciamiento::ponF1($parametros);
        $html .= '
                <div id="contenedorsp"></div>
            ';

        $this->mixHtml($html);
        $this->mixScript('
                var progID = "encProg";
                var progNom = "nomProg";
                var cveFID = "encCveFin";
                var cveFNom = "nomCveFin";

                $("#encCveFin").blur(function(){
                    comunica("poa/presupuestacion/ControladorServiciosPersonales.php",{metodo:"detallePlazas"});
                });
            ');
        $this->mixScript('$("#encCveFin").blur();');
        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
    } // fin programatico

    /******************* INVENTARIO DE PLAZAS ********************************************************/
    public function detallePlazas()
    {
        $this->setContenedor('contenedorsp');
        $mens = '';
        $show = '';
        if (!isset($_SESSION[_S]['cvefin']->ecf) || $_SESSION[_S]['cvefin']->ecf == '' || $this->adm_epg == '') {
            $mens = '<span style="float:left; color:#666">Se muestran todas las Plazas de la UR</span>';
        } else {
            $show = '<span id="showall" style="color:#666; padding:5px;  cursor:pointer; text-decoration:underline;">Mostrar Todas</span>';
        }

        $check = new stdClass();
        $check->id = 'editarenpantalla';
        $check->label = 'Editar Niveles en pantalla';
        $check->width = '20%';
        $check->checked = true;

        $check = $this->ponOnoff($check);
        $html = '';
        $html .= $show;
        $html .= '
                <table width="700px;" class="table table-striped" style="border:1px solid #CCDDDD; padding:5px;">
                    <tr>
                        <td colspan="7" align="center">
                            ' . $mens . '
                            <br/>
                            <h3>Inventario de plazas</h3>
                            ' . $check . '</td>
                    </tr>
                    <tr>
                        <th>EDITAR</th>
                        <th>RELACION LABORAL</th>
                        <th>DESCRIPCION</th>
                        <th style="text-align:right;">ACTIVO</th>
                        <th style="text-align:right;">VACANTE</th>
                        <th style="text-align:right;">EN LITIGIO</th>
                        <th style="text-align:right;">NUMERO DE PLAZAS</th>
                    </tr>';
        $tmp = '';
        if (isset($_SESSION[_S]['cvefin']->ecf) && $_SESSION[_S]['cvefin']->ecf != '' && $this->adm_epg != '' && !isset($this->prm->sinfiltro)) {
            $tmp = '    AND ecf_id =:ecf  AND epg_id =:epg
                        AND ue_id =:ue ';
        }
        $sql = 'SELECT * FROM cat_RelacionLaboral
                    LEFT JOIN(
                    SELECT rla_id,SUM(inp_activas) AS Activas, SUM(inp_vacantes) AS Vacantes, SUM(inp_litigio) AS Litigio
                        FROM cat_InventarioPlazas
                        INNER JOIN enc_NivelRelacionLaboral USING(enr_id)
                        WHERE gpo_id = :gpo AND ur_id =:ur
                        ' . $tmp . '
                        GROUP BY rla_id
                    )B USING (rla_id)
                    WHERE rla_id > 0
                ;';
        $parametros = [
            'gpo' => $this->adm_grupo,
            'ur'  => $this->adm_ur,
        ];

        if ($tmp != '') {
            $datosTmp = [
                'ecf' => $_SESSION[_S]['cvefin']->ecf,
                'epg' => $this->adm_epg,
                'ue'  => $this->adm_ue,
            ];

            $parametros = array_merge($parametros, $datosTmp);
        }

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $data = $this->extraer();
        $tota = $totv = $totl = 0;

        do {
            $tot = $data['Activas'] + $data['Vacantes'] + $data['Litigio'];
            $html .= '<tr>
                        <td width="20%"><input type="button" value="Editar" class="editrla buttonservicios" rla_id="' . $data['rla_id'] . '" nombre="' . $data['rla_nombre'] . '" /> <br /> <input type="button" value="Imprimir" class="printrla buttonserviciosPrint btn_margin_top" id="print_' . $data['rla_id'] . '" rla_id="' . $data['rla_id'] . '" nombre="' . $data['rla_nombre'] . '" /></td>
                        <td width="30%"> ' . $data['rla_nombre'] . '</td>
                        <td width="30%"> &nbsp;</td>
                        <td><input type="text" rla="' . $data['rla_id'] . '" tipo="activo"  value="' . $data['Activas'] . '" class="inputserviciosdisabled inputgeneral entero activo act' . $data['rla_id'] . ' " disabled="disabled"/> </td>
                        <td><input type="text" rla="' . $data['rla_id'] . '" tipo="vacante" value="' . $data['Vacantes'] . '" class="inputserviciosdisabled inputgeneral entero vacante vac' . $data['rla_id'] . '" disabled="disabled"/> </td>
                        <td><input type="text" rla="' . $data['rla_id'] . '" tipo="litigio" value="' . $data['Litigio'] . '" class="inputserviciosdisabled inputgeneral entero litigio lit' . $data['rla_id'] . '" disabled="disabled"/> </td>
                        <td><input type="text" rla="' . $data['rla_id'] . '" tipo="total"   value="' . $tot . '" class="inputserviciosdisabled entero  total totalrla' . $data['rla_id'] . '"  disabled="disabled"/> </td>
                    </tr>';
            $html .= '<tr><td colspan="7" style="padding:0px;"><div id="divrla' . $data['rla_id'] . '" style="display:none;"></div></td></tr>';
            $html .= '<tr><td colspan="7" style="padding:0px;"></td></tr>';
            $tota += $data['Activas'];
            $totv += $data['Vacantes'];
            $totl += $data['Litigio'];
        } while ($data = $this->extraer());
        $html .= '<tr>
                        <td></td>
                        <td>&nbsp;</td>
                        <td align="right"><strong>TOTAL POR TIPO</strong></td>
                        <td><input type="text" tipo="total"   value="' . $tota . '" class="inputserviciosdisabled entero"  disabled="disabled"/></td>
                        <td><input type="text" tipo="total"   value="' . $totv . '" class="inputserviciosdisabled entero"  disabled="disabled"/></td>
                        <td><input type="text" tipo="total"   value="' . $totl . '" class="inputserviciosdisabled entero"  disabled="disabled"/></td>
                        <td><input type="text" tipo="total"   value="' . ($tota + $totv + $totl) . '" class="inputserviciosdisabled entero"  disabled="disabled"/></td>
                    </tr>';
        $html .= '</table><br/><br/><br/>';
        $this->mixHtml($html);

        $this->agregaTools('
            <button class="btn btn-outline-secondary me-1" value="Regresar" id="regresar">
                <span class="fa fa-circle-left me-2"></span>Regresar
            </button>
        ');

        $this->agregaTools('
            <a id="imprimirTodo" target="_blank" class="me-1" title="Imprimir Inventario de Plazas por Unidad Ejecutora" style="padding: 0" >
                <button type="button" class="btn btn-outline-danger " >
                    <i class="fa fa-file-pdf me-2"></i> PDF
                </button>
            </a>
        ');

        $this->prm->epg_id = $this->adm_epg;
        $this->prm->ecf_id = isset($_SESSION[_S]['cvefin']->ecf) ? $_SESSION[_S]['cvefin']->ecf : null;

        $this->mixScript('
                $("#imprimirTodo").click(function(){
                let epg_id= $("#encProg").attr("enc");
                let ecf_id= $("#encCveFin").attr("enc");
                    var form = $("<form>")
                        .attr("action", "rep/doc/poa/rep_Cedula.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 20))
                        .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                        .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                $("#encProg").removeAttr("enc");
                $("#encCveFin").removeAttr("enc");
                });

                $("#showall").click(function(){
                    comunica("poa/presupuestacion/ControladorServiciosPersonales.php",{metodo:"cleanSession"});
                });
                $(".editrla").click(function(){
                    if($("#encCveFin").val()==""){
                        notify("SELECCIONE ENCUADRE PROGRAMATICO Y CLAVE DE FINANCIAMIENTO","error");
                        return false;
                    }
                    var nn = 0;
                    if ($("#editarenpantalla").prop("checked")==true){
                        nn=1;
                    }else{
                        nn=2;
                    }
                    $("#encCveFin").blur();
                    n = $(this).attr("rla_id");

                    if( $("#divrla"+n).html() =="" ){
                        comunica("' . $this->pantalla . '",{metodo:"detalleNiveles",contenedor:nn,rla_id:$(this).attr("rla_id"),nombre:$(this).attr("nombre"),ue_id:$("#comboue").find("option:selected").val()});
                    }
                });

                $("#regresar").click(function(){
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");});
            ');
    } // fin detallePlazas

    /******************* DETALLE POR NIVELES DE PLAZAS ***********************************************/
    public function detalleNiveles()
    {
        if ($_SESSION[_S]['cvefin']->ecf == '' || $this->adm_epg == '') {
            $this->mixScript('notify("SELECCIONE ENCUADRE PROGRAMATICO Y CLAVE DE FINANCIAMIENTO VALIDO","warning");');
            return;
        }

        $html = '';
        $ue = '';
        if ($this->nvj == 1) {
            $ue = $this->ue;
        } else {
            $ue = $this->adm_ue;
        }
        if ($ue == '') {
            $this->mixScript('notify("Debe elegir una Unidad Ejecutora","warning");');
            return;
        }

        $html = '<div id="remover"><table width="700px;" class="table" style="border:1px solid rgba(231,183,31,.6); padding:5px; box-shadow:1px 1px 1px #999;">';

        if (isset($this->prm->contenedor) && $this->prm->contenedor == 1) {
            $this->setContenedor('divrla' . $this->prm->rla_id);
        } else {
            $html .= '<tr>
                            <td colspan="6" align="center"><h3>Inventario de plazas</h3></td>
                        </tr><tr>';
            $html .= '<th style="text-align:right;"></th>
                            <th>RELACIÓN LABORAL :' . $this->prm->nombre . '</th>
                            <th>NIVEL</th>
                            <th>DESCRIPCION</th>
                            <th style="text-align:right;">ACTIVO</th>
                            <th style="text-align:right;">VACANTE</th>
                            <th style="text-align:right;">EN LITIGIO</th>
                            <th style="text-align:right;">NUMERO DE PLAZAS</th>
                        </tr>';
        }

        $sql = 'SELECT enr_id,nrl_id,nrl_clave,nrl_descripcion,inp_activas,inp_vacantes,inp_litigio FROM cat_NivelRelacionLaboral
                        INNER JOIN enc_TabuladorUr USING(tab_id)
                        INNER JOIN enc_NivelRelacionLaboral USING(rla_id,tab_id,nrl_id)
                        LEFT JOIN (
                            SELECT * FROM
                                cat_InventarioPlazas
                            WHERE gpo_id = :gpo AND ur_id = :ur
                                AND ecf_id = :ecf AND epg_id = :epg
                                AND ue_id = :ue
                        ) B USING(gpo_id,ur_id,enr_id)
                    WHERE rla_id= :rla AND  gpo_id = :gpo AND ur_id = :ur ;';
        $parametros = [
            'gpo' => $this->adm_grupo,
            'ur'  => $this->adm_ur,
            'ecf' => $_SESSION[_S]['cvefin']->ecf,
            'epg' => $this->adm_epg,
            'ue'  => $ue,
            'rla' => $this->prm->rla_id,
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $data = $this->extraer();
        $registros = $this->getNumeroRegistros();
        $this->desconectar();

        if ($registros == 0) { // SI NO HAY REGISTROS ENTONCES MOSTRAMOS EL MENSAJE Y REGRESAMOS  A LA PANTALLA ANTERIOR O SOLO CERRAMOS EL DIV DE DETALLE
            $this->agregaTools('
                <button class="btn btn-outline-secondary me-1" value="Regresar" id="regresar">
                    <span class="fa fa-circle-left me-2"></span>Regresar
                </button>
            ');

            $this->prm->epg_id = $this->adm_epg;
            $this->prm->ecf_id = isset($_SESSION[_S]['cvefin']->ecf) ? $_SESSION[_S]['cvefin']->ecf : null;

            $this->agregaTools('
                <a id="imprimirTodo" target="_blank" class="me-1" title="Imprimir Inventario de Plazas por Unidad Ejecutora" style="padding: 0" >
                    <button type="button" class="btn btn-outline-danger " >
                        <i class="fa fa-file-pdf me-2"></i> PDF
                    </button>
                </a>
            ');
            $this->mixHtml('<div id="nohayreg" class="alert alert-danger alert-dismissible mb-4" role="alert" align="center">
                                <strong>No hay Niveles para este tipo de Relacion Laboral</strog>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>');
            $this->mixScript('
                $("#imprimirTodo").click(function(){

                    var form = $("<form>")
                        .attr("action", "rep/doc/poa/rep_Cedula.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 20))
                        .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", "' . $this->prm->epg_id . '"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", "' . $this->prm->ecf_id . '"))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                });

                $("#regresar").click(function(){
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '");});
            ');

            if (isset($this->prm->contenedor) && $this->prm->contenedor == 1) {
                $this->mixScript('
                    $("#divrla' . $this->prm->rla_id . '").show("blind");
                    setTimeout( function() {$("#divrla' . $this->prm->rla_id . '").hide("blind");  }, 3000 );
                    setTimeout( function() { $("#remover' . $this->prm->rla_id . '").remove();$("#saveall").remove();$(this).remove();  }, 3500 );
                    ');
            } else {
                $this->mixScript('setTimeout( function() {comunica("poa/presupuestacion/ControladorServiciosPersonales.php",{metodo:"programatico"});  }, 3000 );');
            }
            return;
        }
        // EN CASO DE REGISTROS DE RELACIONES LABORALES ENTONCES CONSTRUIMOS LA TABLA DE RELACIONES Y DATOS QUE YA TENGA LA UR EN CAT_INVENTARIOPLAZAS
        $disabled = '';
        if (!$this->escritura) {
            $disabled = 'disabled="disabled"';
        }
        do {
            $html .= '<tr>';
            if ($data['nrl_descripcion'] != '' && $this->estadoCosteo == 1) {
                $html .= '<td width="5%"><input type="button" value="Guardar" class="guardaservicios buttonservicios" enr_id="' . $data['enr_id'] . '" /></td>';
            } else {
                $html .= '<td width="78%"> </td>';
            }
            $total = 0;
            $total = $data['inp_activas'] + $data['inp_vacantes'] + $data['inp_litigio'];

            $html .= '<td width="31%">' . $data['nrl_descripcion'] . '</td>
                        <td width="31%">' . $data['nrl_clave'] . '</td>
                        <td width="31%">' . ($data['nrl_descripcion'] == '' ? 'NO EXISTE DESCRIPCION' : $data['nrl_descripcion']) . '</td>
                        <td><input type="text" rla="' . $data['enr_id'] . '" tipo="activo"  value="' . ($data['inp_activas'] == '' ? '0' : $data['inp_activas']) . '"  ' . $disabled . '    class="inputservicios inputgeneral entero activo acti' . $data['enr_id'] . ' " /> </td>
                        <td><input type="text" rla="' . $data['enr_id'] . '" tipo="vacante" value="' . ($data['inp_vacantes'] == '' ? '0' : $data['inp_vacantes']) . '" ' . $disabled . '    class="inputservicios inputgeneral entero vacante vaca' . $data['enr_id'] . '" /> </td>
                        <td><input type="text" rla="' . $data['enr_id'] . '" tipo="litigio" value="' . ($data['inp_litigio'] == '' ? '0' : $data['inp_litigio']) . '"  ' . $disabled . ' class="inputservicios inputgeneral entero litigio liti' . $data['enr_id'] . '" /> </td>
                        <td><input type="text" rla="' . $data['enr_id'] . '" tipo="total"   value="' . $total . '" class="inputserviciosdisabled entero  totals"  totalrla' . $data['enr_id'] . '"     disabled="disabled"/> </td>
                        ';
            $html .= '</tr>';
        } while ($data = $this->extraer());
        $html .= '</table></div>';
        $html .= '';

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->class = 'me-1';
        $btnRegresar->color = 'gris';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->icon = 'fa fa-circle-left me-1';

        if (isset($this->prm->contenedor) && $this->prm->contenedor == 1) {
            /* SI ES EN PANTALLA PINTAMOS EL BOTON PARA CERRAR EL DIV Y EL SCRIPT DEL BOTON REGRESAR CAMBIA*/
            $html .= '<button class="btn btn-outline-danger" value="CERAR" id="cerrarniveles' . $this->prm->rla_id . '">Cerrar</button>';
            $this->mixScript('
                    $("#regresar").click(function(){
                        $("div[aria-describedby=cargaDatosProgFin]").html("");
                        comunica("' . $this->pantalla . '",{metodo:"main"});
                    });
                    $("#cerrarniveles' . $this->prm->rla_id . '").click(function(){
                        $("#divrla' . $this->prm->rla_id . '").hide("blind");
                            setTimeout( function() { $("#remover' . $this->prm->rla_id . '").remove();$("#saveall").remove();$(this).remove();  }, 500 );
                    });
                ');

            if ($this->estadoCosteo == 1) {
                $this->agregaTools($this->ponBoton($btnRegresar) . '<button class="btn btn-outline-success me-1" value="CERRAR" id="saveall"><span class="fa-solid fa-floppy-disk me-2">                </span>Guardar Todos</button> <a id="imprimirTodo" target="_blank"  title="Imprimir Cédula de Servicios Personales" style="padding: 0"><button type="button" class="btn btn-outline-danger" >
                            <i class="fa fa-file-pdf me-2"></i>PDF
                        </button></a>');
            }
        } else {
            $this->agregaTools($this->ponBoton($btnRegresar) . '<button class="btn btn-outline-success btn_margin_left_right" value="SAVE" id="saveall"><span class="fa-solid fa-floppy-disk me-2"></span>Guardar Todos</button> <a id="imprimirTodo" target="_blank"  title="Imprimir Cédula de Servicios Personales" style="padding: 0"><button type="button" class="btn btn-outline-danger" >
                            <i class="fa fa-file-pdf me-2"></i>PDF
                        </button></i></a>');
            $this->mixScript('
                $("#regresar").click(function(){
                        $("div[aria-describedby=cargaDatosProgFin]").html("");
                        comunica("' . $this->pantalla . '",{metodo:"programatico"});
                });');
        }

        $this->prm->epg_id = $this->adm_epg;
        $this->prm->ecf_id = $_SESSION[_S]['cvefin']->ecf;

        $this->mixHtml($html);
        $this->mixScript('
                $("#saveall").click(function(){
                    var datos = new Array();
                    $(".totals").each(function () {
                        var cual = $(this).attr("rla");
                        datos.push([{ "enr_id": cual }, { "activas": $(".acti" + cual).val() }, { "vacantes": $(".vaca" + cual).val() }, { "litigio": $(".liti" + cual).val() }]);
                    });
                    comunica("poa/presupuestacion/ControladorServiciosPersonales.php", {
                        metodo: "abcInventario",
                        abc: "b",
                        arreglo: datos
                    });

                    updateList();
                });

                $(".guardaservicios").click(function () {

                    var cual = $(this).attr("enr_id");
                    comunica("poa/presupuestacion/ControladorServiciosPersonales.php", {
                        metodo: "abcInventario",
                        abc: "a",
                        enr_id: cual,
                        activas: $(".acti" + cual).val(),
                        vacantes: $(".vaca" + cual).val(),
                        litigio: $(".liti" + cual).val(),
                    }, true);

                    updateList();

                });

                function updateList() {
                    // Llamamos al metodo detallePlazas para actualizar listado
                    comunica("poa/presupuestacion/ControladorServiciosPersonales.php", { metodo: "detallePlazas" });
                }

                $(".inputservicios").blur(function () {
                    var activas = 0;
                    var vacantes = 0;
                    var litigio = 0;
                    $(".totals").each(function () {

                        var cual = $(this).attr("rla");
                        var total = 0;
                        total = parseInt($(".acti" + cual).val()) + parseInt($(".vaca" + cual).val()) + parseInt($(".liti" + cual).val());
                        activas += parseInt($(".acti" + cual).val());
                        vacantes += parseInt($(".vaca" + cual).val());
                        litigio += parseInt($(".liti" + cual).val());
                        $(this).val(parseInt(total));
                    });

                    $(".act' . $this->prm->rla_id . '").val(activas);
                    $(".vac' . $this->prm->rla_id . '").val(vacantes);
                    $(".lit' . $this->prm->rla_id . '").val(litigio);
                    $(".totalrla' . $this->prm->rla_id . '").val(activas + vacantes + litigio);
                });

                $(".entero").keypress(function (event) {
                    var anularTecla = false;
                    var esPermitido = Array.indexOf([0, 8], event.which) >= 0;
                    anularTecla = ((event.which < 48 || event.which > 57) && !esPermitido);
                    if (anularTecla) event.preventDefault();
                });

                $("#divrla' . $this->prm->rla_id . '").show("blind");


                $("#print_' . $this->prm->rla_id . '").show();

                $(".printrla").click(function () {
                    var form = $("<form>")
                        .attr("action", "rep/doc/poa/rep_Cedula.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 21))
                        .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", "' . $this->prm->epg_id . '"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", "' . $this->prm->ecf_id . '"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ue_id").attr("value", "' . $ue . '"))
                        .append($("<input>").attr("type", "hidden").attr("name", "rla_id").attr("value", "' . $this->prm->rla_id . '"))

                    $("body").append(form);
                    form.submit();
                    form.remove();
                });

                $("#imprimirTodo").click(function () {
                    var form = $("<form>")
                        .attr("action", "rep/doc/poa/rep_Cedula.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 20))
                        .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", "' . $this->prm->epg_id . '"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", "' . $this->prm->ecf_id . '"))

                    $("body").append(form);
                    form.submit();
                    form.remove();
                });
            ');
    } // fin detalleNiveles

    /******************* PRESUPUESTACION   **********************************************************/
    public function presupuestacion()
    {

        $html = '';

        $btnIrPlazas = new stdClass();
        $btnIrPlazas->color = 'azul';
        $btnIrPlazas->id = 'irPlazas';
        $btnIrPlazas->name = 'IrPlazas';
        $btnIrPlazas->value = 'Ir a Plazas';
        $btnIrPlazas->color = 'azul';

        $imprimir = new stdClass();
        $imprimir->id = 'imprimir';
        $imprimir->class = 'btn btn-outline-secondary me-1';
        $imprimir->name = 'imprimir';
        $imprimir->value = 'PDF';
        $imprimir->icon = 'fa fa-print me-2';

        $this->mixHtml($html);

        $nuevo = new stdClass();
        $nuevo->value = 'Nueva';
        $nuevo->id = 'addModCedula';
        $nuevo->class = 'btn btn-outline-primary me-1';
        $nuevo->title = 'Registrar Nueva Cédula';
        $nuevo->icon = 'fa-solid fa-plus me-2';

        $botones = '<input type="search" width=50
                    id="filtroTabla"
                    style="display: inline; width:250px; height: 30px;"
                    class="form-control"
                    placeholder="Filtrar Cédulas de Servicios Personales" />';

        $tabla = new stdClass();
        $tabla->id = 'tbl-Conceptos';
        $tabla->titulo = 'Conceptos Ingresados';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConceptos';

        $tr = [];

        //Ver/Editar
        $th = new stdClass();
        $th->texto = 'Ver / Editar';
        $th->width = '60px';
        array_push($tr, $th);
        //relacion laboral
        $th = new stdClass();
        $th->texto = 'Relaci&oacute;n Laboral';
        $th->align = 'left';
        array_push($tr, $th);
        //Estatus plaza
        $th = new stdClass();
        $th->texto = 'Estado';
        $th->align = 'center';
        array_push($tr, $th);
        //partida generica
        $th = new stdClass();
        $th->texto = 'Partida';
        $th->align = 'center';
        array_push($tr, $th);
        //descripcio
        $th = new stdClass();
        $th->texto = 'Descripcion';
        $th->align = 'left';
        array_push($tr, $th);
        //Importe anual
        $th = new stdClass();
        $th->texto = 'Importe Anual';
        $th->align = 'right';
        array_push($tr, $th);

        $trbody = [];

        //contenido ver
        $td = new stdClass();
        $td->tipo = 'boton';

        if ($this->cierre != 5 && $this->escritura) {
            $td->icono = 'fa fa-pencil';
        } else {
            $td->icono = 'fa fa-eye';
        }

        $td->attr = new stdClass();
        $td->attr->ced_id = 'ced_id';
        $td->class = 'addModCedula';
        $td->name = 'ced_id';
        $td->id = 'ced_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rla_nombre';
        $td->align = 'left';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'epz_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pe_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pe_nombre';
        $td->align = 'left';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'monto';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        // Si la planeación NO está autorizada, no le permitimos modificar nada
        if ($this->cierre < 4) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                                <strong>La Planeación NO ha sido autorizada. No se permite costear</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>');
        }
        // Si el costeo está cerrado, avisamos
        if ($this->estadoCosteo == 2) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                                <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>');
        }
        // Si tiene derechos de escritura, puede registrar una nueva cédula de Servicios Personales
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

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->agregaTools('
            <button class="btn btn-outline-secondary me-1" value="Regresar" id="btnRegresar">
                <span class="fa fa-circle-left me-2"></span>Regresar
            </button>
        ');
        $this->agregaTools($ponBtnNuevo);
        $this->agregaTools('
            <a id="imprimirTodo" target="_blank" class="me-1" title="Imprimir Cédula de Servicios Personales" style="padding: 0">
                <button type="button" class="btn btn-outline-danger me-1" ><i class="fa fa-file-pdf me-2"></i>PDF
                </button>
            </a>
        ');
        $this->agregaTools($botones);
        $this->llenaTablaConceptos();
        $this->mixScript('
                $("#addModCedula").click(function(){
                    comunica("' . $this->pantalla . '",{"metodo":"addModCedula"});
                });

                $("#btnRegresar").click(function(){
                        $("div[aria-describedby=cargaDatosProgFin]").html("");
                        $("#inventarioPlazas").hide();
                            comunica("' . $this->pantalla . '",{
                            metodo:"main",

                            });
                });

                setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );

            ');
    } // fin presupuestacion

    /******************* ABC DE INVENTARIO DE PLAZAS ************************************************/
    private function abcInventario()
    {
        switch ($this->prm->abc) {
            case 'c':
                InventarioPlazas::abc($this->prm);
                $this->mixScript('alert("Registro actualizado correctamente");');
                break;
            case 'a':
                $sql = 'INSERT INTO cat_InventarioPlazas (gpo_id,ur_id,ue_id,epg_id,ecf_id,enr_id,inp_activas,inp_vacantes,inp_litigio,usr_id)
                            VALUES( :gpo, :ur, :ue, :epg, :ecf, :enr, :inpa, :inpv, :inpl, :usr)
                            ON DUPLICATE KEY UPDATE inp_activas= :inpa,inp_vacantes= :inpv, inp_litigio= :inpl, usr_id= :usr;
                    ';
                $parametros = [
                    'gpo'  => $this->adm_grupo,
                    'ur'   => $this->adm_ur,
                    'ue'   => $this->adm_ue,
                    'epg'  => $this->adm_epg,
                    'ecf'  => $_SESSION[_S]['cvefin']->ecf,
                    'enr'  => $this->prm->enr_id,
                    'inpa' => $this->prm->activas,
                    'inpv' => $this->prm->vacantes,
                    'inpl' => $this->prm->litigio,
                    'usr'  => $this->usr_id,
                ];
                $this->conectar();
                $this->setSql($sql);
                $this->consultar($parametros);
                $this->desconectar();
                $this->mixScript('notify("Registro guardado exitosamente","success");');
                break;
            case 'b':
                $stmt = 'INSERT INTO cat_InventarioPlazas (gpo_id, ur_id, ue_id, epg_id, ecf_id, enr_id, inp_activas, inp_vacantes, inp_litigio, usr_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE inp_activas  = VALUES(inp_activas), inp_vacantes = VALUES(inp_vacantes), inp_litigio  = VALUES(inp_litigio), usr_id = VALUES(usr_id)';
                $this->conectar();
                $this->setSql($stmt);
                foreach ($this->prm->arreglo as $valor) {
                    $this->consultar([
                        $this->adm_grupo,
                        $this->adm_ur,
                        $this->adm_ue,
                        $this->adm_epg,
                        $_SESSION[_S]['cvefin']->ecf,
                        $valor[0]->enr_id,
                        $valor[1]->activas,
                        $valor[2]->vacantes,
                        $valor[3]->litigio,
                        $this->usr_id,
                    ]);
                }
                $this->desconectar();
                $this->mixScript('notify("Registros guardados exitosamente","success");');
                break;
        } // fin switch
    } // fin abcInventario

    /******************* FUNCION DE LLENADO DE CONCEPTOS  *******************************************/
    private function llenaTablaConceptos()
    {
        $gpo = $ur = $sql2 = '';

        /* ES PARA PODER DEFINIR LA UNIDAD EJECUTORA */
        if ($this->nvj == 1) {
            $gpo = $this->gpo;
            $ur = $this->ur_id;
            $sql2 .= ' AND ue_id = :ue';
        } else {
            $gpo = $this->adm_grupo;
            $ur = $this->adm_ur;
        }

        $sql = 'SELECT s.ced_id, r.rla_nombre, e.epz_nombre, s.pe_id, p.pe_nombre,
            ( s.mon_ene+ s.mon_feb+ s.mon_mar+ s.mon_abr+ s.mon_may+ s.mon_jun+ s.mon_jul+ s.mon_ago+ s.mon_sep+ s.mon_oct+ s.mon_nov+ s.mon_dic ) AS monto
            FROM poa_ServiciosPersonales s
            INNER JOIN cat_RelacionLaboral r USING(rla_id)
            INNER JOIN cat_PartidaEspecifica p USING(pg_id,pe_id)
            INNER JOIN cat_EstadoPlaza e USING(epz_id)';
        $sql .= '
                    WHERE gpo_id = :gpo AND ur_id = :ur';

        $parametros = [
            'gpo' => $gpo,
            'ur'  => $ur,
        ];
        if ($sql2 != '') {
            $datosql2 = [
                'ue' => $this->ue,
            ];
            $parametros = array_merge($parametros, $datosql2);
        }

        $this->setSql($sql . $sql2);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Conceptos';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;

        $this->mixScript('
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
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 2))
                        .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                    $("body").append(form);
                    form.submit();
                    form.remove();
                });
            ');

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                $(".imprimir").hide();
                ');
            return false;
        } else {
            $this->mixScript('
                    $("#imprimir").click(function(){
                    var a = document.createElement(\'a\');
                    a.href=\'rep/doc/CedulaServiciosPersonales.php\';
                    a.target = \'_blank\';
                    document.body.appendChild(a);
                    a.click();
                    });
                ');
        }
        $this->mixScript('
                $(".addModCedula").click(function() {
                        comunica("' . $this->pantalla . '",{
                        metodo:"addModCedula",
                        ced_id:$(this).attr("ced_id")
                    });
                    return false;
                });

                $(document).ready(function() {
                    $("#tabla-tbl-Conceptos").filterTable({ // apply filterTable to all tables on this page
                        inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
                    });
                });

            ');
    } // fin llenaTablaConceptos

    /******************* NUEVA PRESUPUESTACION  *****************************************************/
    private function addModCedula()
    {

        $clave = '';
        $rla_id = '';
        $html = '';
        $valorEpg = '';
        $nombreEpg = '';
        $valorCF = '';
        $nombreCF = '';
        $encLectura = '';

        $cedula = (isset($this->prm->ced_id) ? $this->prm->ced_id : 0);

        if ($cedula == 0) {
            $this->mixHtml(DibujaPantallaBuscarProgramaFinanciamientoHTML());
            $this->mixScript(DibujaPantallaBuscarProgramaFinanciamientoJS());
            $encLectura = '';
        } else {
            $encLectura = ' disabled ';
        }

        $sp = new ServiciosPersonales();

        if ($cedula != 0) {
            $sp->setCed_id($this->prm->ced_id);
            $sp->setCcd_id(02);
            $sp->select();
            // Ponemos la clasificación administrativa registrada en la cédula
            $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($sp->getEpg_id());
            $valorEpg = $sp->getGpo_id() . $sp->getUr_id() . '.' . $sp->getUe_id() . '.' . $_SESSION[_S]['clasAdm-prg'] . '.' . $_SESSION[_S]['clasAdm-spr'] . '.' . $_SESSION[_S]['clasAdm-py'] . '.' . $_SESSION[_S]['clasAdm-oa'];
            $this->adm_ue = $sp->getUe_id();

            // Ponemos la clave de financiamiento registrada en la cédula
            ClasificacionFinanciamiento::PonEnVariablesEcf($sp->getEcf_id());
            $cso = isset($_SESSION[_S]['cvefin']->cso) ? $_SESSION[_S]['cvefin']->cso : '';
            $valorCF = $_SESSION[_S]['cvefin']->cff . '-' . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . $cso . '.' . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            $nombreCF = $_SESSION[_S]['cvefin']->nombre;
            $abc = 'c';
            $clave .= '<div align="center"><h4><strong><small><ins>' . $_SESSION[_S]['cvefin']->cveFin . '-' . $_SESSION[_S]['cvefin']->nombre . '</ins></small></strong></h1></div>';
            $rla_id = $sp->getRla_id();

            $html .= '<div class="row">
                    <fieldset class="col-md-12 col-xs-12 mb-4">
                        <legend>Encuadre Programático y Clave de Financiamiento</legend>
                        <label>Encuadre Programático</label>
                        <div class="row">
                            <fieldset class="col-md-3 col-xs-12">
                                <input type="text" value="' . $valorEpg . '" ori="' . $valorEpg . '" enc="' . $sp->getEpg_id() . '" maxlength="20" ' . $encLectura . ' placeholder="Encuadre (F1/F2)" name="encProg" id="encProg" class=" form-control F1Catalogo">
                            </fieldset>
                            <fieldset class="col-md-9 col-xs-12">
                                <input type="text" value="' . $nombreEpg . '" disabled placeholder="Nombre Obra/Actividad" name="nomProg" id="nomProg" class=" form-control">
                            </fieldset>
                        </div>
                        <label><br />Clave de Financiamiento</label>
                        <div class="row">
                            <fieldset class="col-md-3 col-xs-12">
                                <input type="text" value="' . $valorCF . '" ori="' . $valorCF . '" enc="' . $sp->getEcf_id() . '" maxlength="12" ' . $encLectura . ' placeholder="Clave (F1/F2)" name="encCveFin" id="encCveFin" class=" form-control F1Catalogo">
                            </fieldset>
                            <fieldset class="col-md-9 col-xs-12">
                                <input type="text" value="' . $nombreCF . '" disabled placeholder="Nombre de la Clave" name="nomCveFin" id="nomCveFin" class=" form-control">
                            </fieldset>
                        </div>
                    </fieldset>
                </div>';
            // Si no, es una nueva cédula
        } else {
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
            $abc = 'a';
            $clave .= '';

            $html .= CatalogoProgramaticoFinanciamiento::ponF1();
        }


        $relacion = new stdClass();
        $relacion->sql = 'select rla_id, rla_nombre from cat_RelacionLaboral where rla_id > 0;';
        $relacion->id = 'rla_id2';
        $relacion->name = 'rla_id2';
        $relacion->required = true;
        $relacion->mensaje = 'Selecciona Relacion Laboral';
        $relacion->label = 'Relaci&oacute;n Laboral';
        $relacion->selecciona = $rla_id;

        //cat_EstadoPlaza
        $estado = new stdClass();
        $estado->sql = 'select epz_id, epz_nombre from cat_EstadoPlaza;';
        $estado->id = 'epz_id';
        $estado->name = 'epz_id';
        $estado->required = true;
        $estado->mensaje = 'Selecciona Estado de la Plaza';
        $estado->label = 'Estado de la Plaza';
        $estado->selecciona = $sp->getEpz_id();

        $generica = new stdClass();

        $sql_pg = '';
        $sql_pe = '';

        if (isset($this->prm->ced_id) && $this->prm->ced_id != '') {
            $sql_pg = 'SELECT pg_id, pg_nombre FROM cat_PartidaGenerica where pg_id="' . $sp->getPg_id() . '"';
            $sql_pe = 'select pe_id, pe_nombre from cat_PartidaEspecifica where pg_id="' . $sp->getPg_id() . '" and pe_id="' . $sp->getPe_id() . '"';
        }

        $generica->sql = $sql_pg;
        $generica->id = 'pg_id2';
        $generica->name = 'pg_id2';
        $generica->required = true;
        $generica->mensaje = 'Selecciona Partida Generica';
        $generica->label = 'Partida Generica';
        $generica->selecciona = $sp->getPg_id();

        $especifica = new stdClass();
        $especifica->sql = $sql_pe;
        $especifica->id = 'pe_id2';
        $especifica->name = 'pe_id2';
        $especifica->label = 'Partida Especifica';
        $especifica->selecciona = $sp->getPe_id();

        if (isset($this->prm->ced_id) && $this->prm->ced_id != '') {
            $especifica->disabled = 'disabled';
            $relacion->disabled = 'disabled';
            $estado->disabled = 'disabled';
            $generica->disabled = 'disabled';
        }

        $eliminar = '';
        switch ($this->nvj) {
            case 1:
            case 2:
                if (isset($this->prm->ced_id) && $this->prm->ced_id != '') {
                    $btnEliminar = new stdClass();
                    $btnEliminar->id = 'deleteCedula';
                    $btnEliminar->class = 'btn btn-outline-danger me-1';
                    $btnEliminar->text = 'Eliminar';
                    $btnEliminar->value = 'Eliminar';
                    $btnEliminar->icon = 'fa-solid fa-trash me-2';
                    $eliminar = $this->ponBoton($btnEliminar);
                }
                break;
        } // fin switch

        $html .= '<hr><legend>Información del Registro</legend>
                    <div class="row ">
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponCombo($estado) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6 mb-4">
                            ' . $this->ponCombo($relacion) . '
                        </fieldset>
                    </div>
                        <legend>Objeto del Gasto</legend>
                    <div class="row">
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponCombo($generica) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponCombo($especifica) . '
                        </fieldset>
                    </div>
                    <hr/>
                    <fieldset class="col-md-12">
                        <legend>Calendarización</legend>
                            <Calendario id="arrMeses"></Calendario>
                    </fieldset>
                    <div id="contenedorCFModal"></div>
            ';

        if ($this->nvj <= 2 && $this->cierre != 5) {
            if ($this->escritura) {
                $this->agregaTools('
                    <button class="btn btn-outline-secondary me-1" value="Regresar" id="btnRegresarIndex">
                        <span class="fa fa-circle-left me-2"></span>Regresar
                    </button>
                    ');
                $this->agregaTools('
                    <button color="verde" class="btn btn-outline-success me-1" value="guardar" id="guardarSP">
                        <span class="fa-solid fa-floppy-disk me-2"></span>Guardar
                    </button>
                    ');

                $this->agregaTools($eliminar);
            } else {
                $this->agregaTools('
                    <button class="btn btn-outline-secondary me-1" value="Regresar" id="btnRegresarIndex">
                        <span class="fa fa-circle-left me-2"></span>Regresar
                    </button>
                    ');
            }

            $this->agregaTools('
                    <a id="imprimirTodo" epg_id="' . $sp->getEpg_id() . '" ecf_id="' . $sp->getEcf_id() . '" target="_blank" class="me-1" style="padding: 0" title="Imprimir Cédula de Servicios Personales">
                        <button type="button" class="btn btn-outline-danger" >
                            <i class="fa fa-file-pdf me-2"></i>PDF
                        </button>
                    </a>
                    ');
        }

        $ced_id = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? 'obj.ced_id = "' . $this->prm->ced_id . '";' : '';

        $this->mixHtml($html);
        $this->mixScript('
                var progID = "encProg";
                var progNom = "nomProg";
                var cveFID = "encCveFin";
                var cveFNom = "nomCveFin";

                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                    Mousetrap.unbind("ctrl+enter");
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                    e.stopPropagation();
                });

                $("#arrMeses").calendarioMontos({
                    montos:' . json_encode($sp->getMontos()->getArrayMeses()) . '
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
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 2))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();
                });
                $("#guardarSP").click(function(){

                    if ($("#pe_id2").val() == "") {
                        notify("Debe seleccionar la partida especifica", "error");
                        return false;
                    }

                    var obj = {"metodo":"abcSP"};
                        obj.abc = "' . $abc . '";
                        ' . $ced_id . '
                        obj.epg_id = $("#encProg").attr("enc");
                        obj.ecf_id = $("#encCveFin").attr("enc");
                        obj.ccd_id = "02";
                        obj.pg_id = $("#pg_id2").val();
                        obj.pe_id = $("#pe_id2").val();
                        obj.rla_id = $("#rla_id2").val();
                        obj.epz_id = $("#epz_id").val();
                        obj.montos = $("#arrMeses").data("wr-calendarioMontos").options.montos;

                        //validando los campos de encuadre y clave de financiamiento
                        if($("#encProg").val()=="" || $("#encProg").attr("enc")==""){
                            notify("Debe seleccionar un encuadre programatico","error");
                            return false;
                        } else if ($("#nomCveFin").val()=="") {
                            notify("Debe seleccionar una clave de financiamiento","error");
                            return false;
                        } else {
                            comunica("' . $this->pantalla . '",obj,true);
                            return false;
                        }
                });
                $("#btnRegresarIndex").click(function(){
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '",{metodo:"presupuestacion"});
                    return false;
                });
                $("#rla_id2").change(function(){
                    if($(this).find("option:selected").val() !=""){
                        comunica("' . $this->pantalla . '",{metodo:"seleccionaPartidaGenerica",relacion:$(this).find("option:selected").val()});
                    }
                });
                $("#pg_id2").change(function(){
                    if($(this).find("option:selected").val() !=""){
                        comunica("' . $this->pantalla . '",{
                                metodo:"seleccionaPartidaEspecifica",
                                pg_id:$(this).find("option:selected").val(),
                                relacion:$("#rla_id2").find("option:selected").val()
                                });
                    }
                });
                $("#deleteCedula").click(function(){
                    alertify.confirm("ELIMINAR CÉDULA DE SERVICIOS PERSONALES", "¿ Confirma que desea eliminar la cédula? <h5 style=\"display:inline\"></h5>",
                        function(){
                            var obj = {"metodo":"abcSP"};
                            obj.abc = "b";
                            ' . $ced_id . '
                            obj.ccd_id = "02";
                            comunica("' . $this->pantalla . '",obj,true);
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
                });
            ');
    } // fin addModCedula

    /******************* INTERNA DE PARTIDA GENERICA ************************************************/
    private function seleccionaPartidaGenerica()
    {
        $rla_id = $this->prm->relacion;
        $generica = new stdClass();
        $sql = 'SELECT DISTINCT(pg.pg_id), pg.pg_nombre
                    FROM cat_PartidaGenerica  pg
                    INNER JOIN enc_ObjGasRelLab o USING(pg_id)
                    INNER JOIN enc_PartGenUr p USING(pg_id)
                    WHERE o.rla_id=:rla
                    AND p.gpo_id=:gpo
                    AND p.ur_id=:ur;';
        $parametros = [
            'rla' => $rla_id,
            'gpo' => $this->adm_grupo,
            'ur'  => $this->adm_ur,
        ];
        $generica->sql = $sql;
        $generica->params = $parametros;
        $generica->id = 'pg_id2';
        $generica->name = 'pg_id2';
        $generica->required = true;
        $generica->mensaje = 'Selecciona Partida Generica';
        $generica->label = 'Partida Generica';
        $this->recargaCombo($generica);
    } // fin seleccionaPartidaGenerica

    /******************* INTERNA DE PARTIDA ESPECIFICA  *********************************************/
    private function seleccionaPartidaEspecifica()
    {
        $pg_id2 = $this->prm->pg_id;
        $rla_id = $this->prm->relacion;
        $epz_id = (isset($this->prm->epz_id)) ? $this->prm->epz_id : '';

        $condicion = '';

        if (!(isset($this->prm->ced_id) && $this->prm->ced_id != '')) {
            $ecf_id = ClasificacionFinanciamiento::getEcf();
            $epg_id = ClasificacionAdministrativa::getEpg();

            $condicion = ' !( pe_id in (
                                SELECT pe_id FROM poa_ServiciosPersonales
                                WHERE gpo_id= :gpo AND
                                    ur_id= :ur AND
                                    epg_id= :epg AND
                                    ecf_id= :ecf AND
                                    epz_id= :epz AND
                                    rla_id= :rla
                                )
                            ) AND ';
        }
        $especifica = new stdClass();
        $especifica->sql = '
            SELECT  p.pe_id, p.pe_nombre
            FROM cat_PartidaEspecifica p
            INNER JOIN  enc_ObjGasRelLab B USING(pg_id,pe_id)
            WHERE ' . $condicion . ' B.rla_id= :rla and p.pg_id= :pg';
        $parametros = [
            'rla' => $rla_id,
            'gpo' => $this->adm_grupo,
            'ur'  => $this->adm_ur,
            'epg' => $epg_id,
            'ecf' => $ecf_id,
            'epz' => $epz_id,
            'pg'  => $pg_id2,
        ];
        $especifica->params = $parametros;
        $especifica->id = 'pe_id2';
        $especifica->name = 'pe_id2';
        $especifica->label = 'Partida Especifica';
        $this->recargaCombo($especifica);
    } // fin seleccionaPartidaEspecifica

    /******************* LIMPIAR SESION  ***********************************************************/
    private function cleanSession()
    {
        unset($_SESSION[_S]['cvefin']);
        $this->adm_epg = null;

        $this->mixScript('
                $("#encProg").val("");
                $("#encCveFin").val("");
                $("#nomProg").val("");
                $("#nomCveFin").val("");
                comunica("poa/presupuestacion/ControladorServiciosPersonales.php",{ metodo:"detallePlazas","sinfiltros":"sinflitros"});
            ');
    } // fin cleanSession

    /******************* ABC DE PRESUPUESTACION  ****************************************************/
    private function abcSP()
    {
        switch ($this->prm->abc) {
            case 'c':
                ServiciosPersonales::abc($this->prm);
                $this->mixScript('
                        notify("Cedula de Servicios Personales actualizada exitosamente");
                    ');
                break;

            case 'b':
                ServiciosPersonales::abc($this->prm);
                $this->mixScript('
                        notify("Cedula eliminada exitosamente");
                    ');
                break;
            case 'a':
                $this->prm->gpo_id = $this->adm_grupo;
                $this->prm->ur_id = $this->adm_ur;

                //Recupera la UE de la obra actividad
                $this->prm->ue_id = recuperarUe(ClasificacionAdministrativa::getEpg());
                $this->prm->abc = 'a';
                $this->prm->ced_id = ServiciosPersonales::abc($this->prm);
                $this->mixScript('
                        notify("Cedula de Servicios Personales insertada exitosamente");
                    ');
                break;
        } // fin switch
        $this->mixScript('
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            comunica("' . $this->pantalla . '",{metodo:"presupuestacion"});');
    } //fin abcSP

    public function getJs()
    {
        $onSelect = <<<JS
            comunica("poa/presupuestacion/ControladorServiciosPersonales.php",{metodo:"detallePlazas"});
        JS;
        return $onSelect;
    }
} // fin Class

if (isset($_SESSION[_S]['prm']['A']['3']['2']['1'])) {
    new ControladorServiciosPersonales();
} else {
    WebResponse::sinPermisos();
}
