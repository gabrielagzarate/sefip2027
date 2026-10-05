<?php

declare (strict_types = 1);
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/Cog.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/sp_CogActualizar.php';

class ControladorCOG extends WebResponse
{
    public $lectura;
    public $escritura;
    public $gpo_id;
    public $ur_id;
    public $nivel;
    public $ue_id;
    public function __construct()
    {
        //Mandar a llamar Constructor Base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['8']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['8']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaCOG':
                $this->llenaTablaCOG();
                break;
            case 'addCOG':
                $this->addCOG();
                break;
            case 'abcCOG':
                $this->abcCOG();
                break;
            case 'agregaContraCOG':
                $this->agregaContraCOG();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }
    /*     * *********************************************** Función principal del Controlador*********************************************** */
    private function main()
    {
        $tablaCOG = new stdClass();
        $tablaCOG->id = 'tablaCOG';
        $tablaCOG->titulo = 'COG';
        $tablaCOG->comunica = new stdClass();
        $tablaCOG->comunica->pantalla = $this->pantalla;
        $tablaCOG->comunica->metodo = 'llenaTablaCOG';

        //Encabezados
        $tr = array();
        if ($this->escritura) {
            $th = new stdClass();
            $th->align = 'center';
            $th->width = '5%';
            $th->texto = 'Editar';
            array_push($tr, $th);
            $th = new stdClass();
            $th->align = 'center';
            $th->width = '5%';
            $th->texto = 'Eliminar';
            array_push($tr, $th);
        }
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'COG';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '20%';
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'TIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'CUENTA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '20%';
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'CUENTA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '20%';
        $th->texto = 'NOMBRE';
        array_push($tr, $th);

        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        if ($this->escritura) {
            $td = new stdClass();
            $td->align = 'center';
            $td->class = 'EditaClave';
            $td->tipo = 'boton';
            $td->icono = 'pencil';
            $td->attr = new stdClass();
            $td->attr->pg_id = 'pg_id';
            $td->attr->ger_id = 'ger_id';
            $td->attr->cgp_id = 'cgp_id';
            $td->attr->rub_id = 'rub_id';
            $td->attr->cgcu_id = 'cgcu_id';
            $td->attr->cgsc_id = 'cgsc_id';

            $td->attr->ger_idC = 'ger_idC';
            $td->attr->cgp_idC = 'cgp_idC';
            $td->attr->rub_idC = 'rub_idC';
            $td->attr->cgcu_idC = 'cgcu_idC';
            $td->attr->cgsc_idC = 'cgsc_idC';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->align = 'center';
            $td->class = 'EliminaClave';
            $td->color = 'rojo';
            $td->tipo = 'boton';
            $td->icono = 'trash';
            $td->attr = new stdClass();
            $td->attr->pg_id = 'pg_id';
            $td->attr->ger_id = 'ger_id';
            $td->attr->cgp_id = 'cgp_id';
            $td->attr->rub_id = 'rub_id';
            $td->attr->cgcu_id = 'cgcu_id';
            $td->attr->cgsc_id = 'cgsc_id';

            $td->attr->ger_idC = 'ger_idC';
            $td->attr->cgp_idC = 'cgp_idC';
            $td->attr->rub_idC = 'rub_idC';
            $td->attr->cgcu_idC = 'cgcu_idC';
            $td->attr->cgsc_idC = 'cgsc_idC';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'pg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'pg_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cog_tipo';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cog_cuenta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cgsc_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cog_contracuenta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cgm_contranombre';
        array_push($trbody, $td);

        $tablaCOG->encabezado = array($tr);
        $tablaCOG->cuerpo = ($trbody);
        $btn_regresarIndex = '';
        $btn_addCOG = '
            <button id="btn_addCOG" type="button" class="btn btn-outline-primary nuevo me-2" title="Registrar nueva Clave">
                <i class="fa-solid fa-plus me-1"></i>Nuevo
            </button>';
        $btn_repCOG = '
            <button id="btn_repCOG" type="button" form="ImprimeCat" class="btn btn-outline-danger me-2"  title="Imprimir catálogo del COG">
                <i class="fa-solid fa-file-pdf me-1"></i>PDF
            </button>';
        $btn_expCOG = '
            <button id="btn_expCOG" type="button" form="MostrarHistorial" class="btn btn-outline-success me-2"  title="Exportar a excel catálogo del COG">
                <i class="fa-solid fa-file-excel me-1"></i>Excel
            </button>';
        if ($this->escritura) {
            $this->agregaTools($btn_regresarIndex . $btn_addCOG . $btn_repCOG . $btn_expCOG);
        } else {
            $this->agregaTools($btn_regresarIndex . $btn_repCOG . $btn_expCOG);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaCOG) . '</div>');
        $this->llenaTablaCOG();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addCOG").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addCOG",
                        pg_id: $(this).attr("pg_id") ,
                        ger_id: $(this).attr("ger_id"),
                        cgp_id: $(this).attr("cgp_id"),
                        rub_id: $(this).attr("rub_id") ,
                        cgcu_id: $(this).attr("cgcu_id"),
                        cgsc_id: $(this).attr("cgsc_id"),
                        ger_idC: $(this).attr("ger_idC"),
                        cgp_idC: $(this).attr("cgp_idC"),
                        rub_idC: $(this).attr("rub_idC") ,
                        cgcu_idC: $(this).attr("cgcu_idC"),
                        cgsc_idC: $(this).attr("cgsc_idC")
                    });
                });
            ');
        }
        $this->mixScript('$(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#btn_repCOG").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "8" ))
                $("body").append(form);
                form.submit();
            });
            $("#btn_expCOG").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "8" ))
                $("body").append(form);
                form.submit();
            });
            $("#btn_regresarIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');
    }
    /************************************************* LLENA la Tabla de Tipo de Documento Fuente *************************/
    private function llenaTablaCOG()
    {
        $sql = "
            SELECT pg.pg_id, pg.pg_nombre, cog.cog_tipo, cog.cog_cuenta, cgm.cgsc_nombre, ccc.cog_cuenta AS cog_contracuenta, cgn.cgsc_nombre AS cgm_contranombre,
            cog.ger_id, cog.cgp_id, cog.rub_id, cog.cgcu_id, cog.cgsc_id,
            ccc.ger_id AS ger_idC, ccc.cgp_id AS cgp_idC, ccc.rub_id AS rub_idC, ccc.cgcu_id AS cgcu_idC, ccc.cgsc_id AS cgsc_idC
            FROM vin_Cog cog
            INNER JOIN vin_CogContraCuenta ccc USING( pg_id )
            INNER JOIN cat_PartidaGenerica pg USING( pg_id )
            LEFT JOIN cat_CgSubcuenta cgm ON cog.ger_id = cgm.ger_id AND cog.cgp_id = cgm.cgp_id AND cog.rub_id = cgm.rub_id AND cog.cgcu_id = cgm.cgcu_id AND cog.cgsc_id = cgm.cgsc_id
            LEFT JOIN cat_CgSubcuenta cgn ON ccc.ger_id = cgn.ger_id AND ccc.cgp_id = cgn.cgp_id AND ccc.rub_id = cgn.rub_id AND ccc.cgcu_id = cgn.cgcu_id AND ccc.cgsc_id = cgn.cgsc_id";

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaCOG';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaClave").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addCOG",
                    pg_id: $(this).attr("pg_id") ,
                    ger_id: $(this).attr("ger_id"),
                    cgp_id: $(this).attr("cgp_id"),
                    rub_id: $(this).attr("rub_id") ,
                    cgcu_id: $(this).attr("cgcu_id"),
                    cgsc_id: $(this).attr("cgsc_id"),
                    ger_idC: $(this).attr("ger_idC"),
                    cgp_idC: $(this).attr("cgp_idC"),
                    rub_idC: $(this).attr("rub_idC") ,
                    cgcu_idC: $(this).attr("cgcu_idC"),
                    cgsc_idC: $(this).attr("cgsc_idC")
                });
                return false;
            });

            $(".EliminaClave").click(function() {
                var partidaGenerica = $(this).attr("pg_id");
                var partidaGenericager_id = $(this).attr("ger_id");
                var partidaGenericacgp_id = $(this).attr("cgp_id");
                var partidaGenericarub_id = $(this).attr("rub_id");
                var partidaGenericacgcu_id = $(this).attr("cgcu_id");
                var partidaGenericacgsc_id = $(this).attr("cgsc_id");
                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "Está a punto de eliminar la clave: "+"<strong>"+$(this).attr("pg_id")+"</strong>" + " ¿Desea continuar?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcCOG",
                        abc: "b",
                        pg_id: partidaGenerica,
                        ger_id: partidaGenericager_id,
                        cgp_id: partidaGenericacgp_id,
                        rub_id: partidaGenericarub_id,
                        cgcu_id: partidaGenericacgcu_id,
                        cgsc_id: partidaGenericacgsc_id
                        });
                }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });
        ');
    }
    /************************************************* ALTA de nuevo Tipo de Documento Fuente *************************/
    private function addCOG()
    {
        $datos = null;
        $abc = '';
        if (isset($this->prm->pg_id)) {
            $datos = new COG($this->prm);
            $genero = $this->prm->ger_id;
            $grupo = $this->prm->cgp_id;
            $rubro = $this->prm->rub_id;
            $cuenta = $this->prm->cgcu_id;
            $subcuenta = $this->prm->cgsc_id;

            $generoC = $this->prm->ger_idC;
            $grupoC = $this->prm->cgp_idC;
            $rubroC = $this->prm->rub_idC;
            $cuentaC = $this->prm->cgcu_idC;
            $subcuentaC = $this->prm->cgsc_idC;
            $abc = 'c';
        } else {
            $datos = new COG();
            $abc = 'a';
        }

        $com_PartidaGenerica = new stdClass();
        $com_PartidaGenerica->id = 'com_PartidaGenerica';
        $com_PartidaGenerica->label = 'Partida Generica';
        $com_PartidaGenerica->enDiv = true;
        if ($abc == 'c') {
            $com_PartidaGenerica->sql = '
                SELECT pg.pg_id, pg.pg_nombre
                FROM cat_PartidaGenerica AS pg
                INNER JOIN vin_Cog AS c ON pg.pg_id = c.pg_id
                WHERE c.pg_id ="' . $this->prm->pg_id . '";';
        } else {
            $com_PartidaGenerica->sql = '
                SELECT pg.pg_id, pg.pg_nombre
                FROM cat_PartidaGenerica AS pg
                LEFT JOIN vin_Cog AS c ON pg.pg_id = c.pg_id
                WHERE c.pg_id IS NULL;';
        }
        $com_CtaContable = new stdClass();
        $com_CtaContable->id = 'com_CtaContable';
        $com_CtaContable->label = 'Clasificador por Objeto del Gasto';
        $com_CtaContable->search = true;
        $com_CtaContable->comoAtr = 'ger_id,cgp_id,rub_id,cgcu_id,cgsc_id';
        $com_CtaContable->seleccionar = 'ger_id,cgp_id,rub_id,cgcu_id,cgsc_id';
        if ($abc == 'c') {
            $com_CtaContable->selecciona = $genero . "." . $grupo . "." . $rubro . "." . $cuenta . "." . $subcuenta;
        }
        $com_CtaContable->sql = 'SELECT cgsc_cuenta, cgsc_nombre ,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id FROM cat_CgSubcuenta;';

        $com_ContraCtaContable = new stdClass();
        $com_ContraCtaContable->id = 'com_ContraCtaContable';
        $com_ContraCtaContable->label = 'Contra Cuenta del Clasificador del Objeto del Gasto';
        $com_ContraCtaContable->search = true;
        $com_ContraCtaContable->comoAtr = "ger_id,cgp_id,rub_id,cgcu_id,cgsc_id";
        if ($abc == 'c') {
            $com_ContraCtaContable->selecciona = $generoC . "." . $grupoC . "." . $rubroC . "." . $cuentaC . "." . $subcuentaC;
        }
        $com_ContraCtaContable->sql = 'SELECT cgsc_cuenta, cgsc_nombre ,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id FROM cat_CgSubcuenta;';

        $btnVincCoGCta = new stdClass();
        $btnVincCoGCta->id = 'btnVincCoGCta';
        $btnVincCoGCta->icon = "paper-plane me-2";
        $btnVincCoGCta->class = 'VincCoGCta';
        $btnVincCoGCta->value = 'Vincular COG-Cta';
        $btnVincCoGCta->text = '';
        $btnVincCoGCta->color = 'azul';
        $btnVincCoGCta->title = 'Vincular COG. a Cta. Cont.';

        $btnVinCoGContraCta = new stdClass();
        $btnVinCoGContraCta->id = 'btnVinCoGContraCta';
        $btnVinCoGContraCta->icon = "paper-plane me-2";
        $btnVinCoGContraCta->class = 'VincCoGCta';
        $btnVinCoGContraCta->value = '  Vincular COG-ContraCta';
        $btnVinCoGContraCta->text = '';
        $btnVinCoGContraCta->color = 'azul';
        $btnVinCoGContraCta->title = 'Vincular COG. a Contra Cta. Cont.';

        $com_PartidaGenerica = $this->ponCombo($com_PartidaGenerica);
        $com_CtaContable = $this->ponCombo($com_CtaContable);
        $com_ContraCtaContable = $this->ponCombo($com_ContraCtaContable);
        $btnVincCoGCta = $this->ponBoton($btnVincCoGCta);
        $btnVinCoGContraCta = $this->ponBoton($btnVinCoGContraCta);

        $html = '
            <fieldset class="col-md-12 col-xs-12 mb-4">
                ' . $com_PartidaGenerica . '
            </fieldset>
            <div class="row mb-4">
                <fieldset class="col-md-9 col-xs-9">
                    ' . $com_CtaContable . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 mt-3">
                    ' . $btnVincCoGCta . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-9 col-xs-9">
                    ' . $com_ContraCtaContable . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 mt-3">
                    ' . $btnVinCoGContraCta . '
                </fieldset>
            </div>
        ';

        $btn_regresaCOG = '
            <button id="btn_regresaCOG" type="button" class="btn btn-outline-secondary" title="Regresar al Catálogo del COG">
                <i class="fa-solid fa-circle-left me-2"></i>Regresar
            </button>';

        $this->agregaTools($btn_regresaCOG);

        $this->mixHtml($html);
        $this->mixScript('
            $("#btnVincCoGCta").click(function() {
                if($("#com_PartidaGenerica").val() && $("#com_CtaContable").val()){
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcCOG",
                        abc: "' . $abc . '",
                        pg_id: $("#com_PartidaGenerica").val(),
                        ger_id: $("#com_CtaContable").find("option:selected").attr("ger_id"),
                        cgp_id: $("#com_CtaContable").find("option:selected").attr("cgp_id"),
                        rub_id: $("#com_CtaContable").find("option:selected").attr("rub_id"),
                        cgcu_id: $("#com_CtaContable").find("option:selected").attr("cgcu_id"),
                        cgsc_id: $("#com_CtaContable").find("option:selected").attr("cgsc_id"),
                        cog_cuenta:$("#com_CtaContable").val()
                    },true);
                } else {
                    notify("Debe rellenar los campos","error");
                }
            });

            $("#btnVinCoGContraCta").click(function() {
                if($("#com_PartidaGenerica").val() && $("#com_ContraCtaContable").val()){
                    comunica("' . $this->pantalla . '",{
                        metodo: "agregaContraCOG",
                        abc: "' . $abc . '",
                        pg_id: $("#com_PartidaGenerica").val(),
                        ger_id: $("#com_ContraCtaContable").find("option:selected").attr("ger_id"),
                        cgp_id: $("#com_ContraCtaContable").find("option:selected").attr("cgp_id"),
                        rub_id: $("#com_ContraCtaContable").find("option:selected").attr("rub_id"),
                        cgcu_id: $("#com_ContraCtaContable").find("option:selected").attr("cgcu_id"),
                        cgsc_id: $("#com_ContraCtaContable").find("option:selected").attr("cgsc_id"),
                        cog_tipo: $("#com_PartidaGenerica").val(),
                        cog_cuenta:$("#com_ContraCtaContable").val()
                    },true);
                } else {
                    notify("Debe rellenar los campos","error");
                }
            });

            $("#btn_regresaCOG").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');
    }
    /************************************************* ABC de Tipo de Documento Fuente ************************ */
    private function abcCOG()
    {
        switch ($this->prm->abc) {
            case 'a':
                $combo = new stdClass();
                if ($this->prm->ger_id == 5) {
                    $this->prm->cog_tipo = "R";
                } else {
                    $this->prm->cog_tipo = "B";
                }
                $comboSql = "SELECT pg_id FROM vin_Cog WHERE pg_id = :pg_id";
                $parms = [':pg_id' => $this->prm->pg_id];
                $this->setSql($comboSql);
                $this->conectar();
                $this->consultar($parms);
                $data = $this->extraer();
                $this->desconectar();
                if ($data != '') {
                    $this->mixScript('notify("La clave ' . $this->prm->pg_id . ' ya está registrada en la base de datos","error");');
                    $combo->id = 'com_PartidaGenerica';
                    $combo->id = 'com_CtaContable';
                    $this->recargaCombo($combo);
                } else {
                    COG::abc($this->prm);
                    $this->mixScript('
                        notify("El registro se ha agregado correctamente","success");
                    ');
                }
                break;
            case 'b':
                $sql = 'DELETE FROM vin_CogContraCuenta' . ' WHERE  pg_id="' . $this->prm->pg_id . '";';
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar();

                $sql1 = 'DELETE FROM vin_Cog' . ' WHERE   pg_id="' . $this->prm->pg_id . '";';
                $conexion->setSql($sql1);
                $conexion->consultar();
                $conexion->desconectar();
                $this->mixScript('notify("El registro se ha eliminado correctamente","error");');
                $this->llenaTablaCOG();
                break;
            case 'c':

                $cuenta = $this->prm->ger_id . "." . $this->prm->cgp_id . "." . $this->prm->rub_id . "." .
                $this->prm->cgcu_id . "." . $this->prm->cgsc_id . "." . $this->prm->pg_id;
                $sql = 'UPDATE vin_Cog '
                . 'SET  ger_id = ' . $this->prm->ger_id . ' , cgp_id = ' . $this->prm->cgp_id . ' , rub_id = ' .
                $this->prm->rub_id . ' , cgcu_id = ' . $this->prm->cgcu_id . ' , cgsc_id = ' . $this->prm->cgsc_id . ',
                    cog_cuenta="' . $cuenta . '"
                    WHERE  pg_id="' . $this->prm->pg_id . '";';
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar();
                $conexion->desconectar();
                $this->mixScript('
                    notify("El registro se actualizó exitosamente","warning");
                    comunica("' . $this->pantalla . '",{
                        metodo: "main"
                    });
                ');
                break;
        }

        $error = false;
        $cogActualizar = new sp_CogActualizar();
        $cogActualizar->setPg_id($this->prm->pg_id);

        $cogActualizar->setSis_id('SCG');
        $cogActualizar->setBit_observacion(
            'VINCULACIÓN COG DE LA PARTIDA GENERICA ' .
            $this->prm->pg_id . ' DESDE LA PANTALLA C_2_8'
        );
        $cogActualizar->setEjercicio(APP_EJERCICIO);

        $cogActualizar->execute();
        if (!$cogActualizar->getBandera()) {
            $error = true;
            $mensaje = $cogActualizar->getMensaje();
        }

        if ($error) {
            $this->mixScript('notify(" ' . $mensaje . '", "error");');
        }
    }
    /*     * *********************************************** agregaContraCOG agrega contracuentas a la tabla vin_CogContraCuenta ************************ */
    private function agregaContraCOG()
    {
        if ($this->prm->abc != 'c') {
            $combo = new stdClass();
            $comboSql = "SELECT pg_id FROM vin_CogContraCuenta WHERE pg_id = " . $this->prm->pg_id;
            $this->conectar();
            $this->setSql($comboSql);
            $this->consultar();
            $data = $this->extraer();
            $this->desconectar();
            if ($data != '') {
                $this->mixScript('notify("La clave ' . $this->prm->pg_id . ' ya está registrada en la base de datos","error");');
                $combo->id = 'com_PartidaGenerica';
                $combo->id = 'com_ContraCtaContable';
                $this->recargaCombo($combo);
            } else {
                if ($this->prm->ger_id == 5) {
                    $this->prm->cog_tipo = "R";
                } else {
                    $this->prm->cog_tipo = "B";
                }
                $sql = 'INSERT INTO vin_CogContraCuenta ( pg_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cog_tipo, cog_cuenta, usr_id) VALUES(';
                $sql .= '"' . strtoupper($this->prm->pg_id) . '",';
                $sql .= '"' . strtoupper($this->prm->ger_id) . '",';
                $sql .= '"' . strtoupper($this->prm->cgp_id) . '",';
                $sql .= '"' . strtoupper($this->prm->rub_id) . '",';
                $sql .= '"' . strtoupper($this->prm->cgcu_id) . '",';
                $sql .= '"' . strtoupper($this->prm->cgsc_id) . '",';
                $sql .= '"' . $this->prm->cog_tipo . '",';
                $sql .= '"",';
                $sql .= '' . $_SESSION[_S]['usr_id'] . '';
                $sql .= ') ';
                $this->conectar();
                $this->setSql($sql);
                $this->consultar();
                $this->desconectar();

                $this->mixScript('
                    notify("El registro se ha agregado correctamente","success");
                ');
            }
        } else {
            $cuenta = $this->prm->ger_id . "." . $this->prm->cgp_id . "." . $this->prm->rub_id . "." .
            $this->prm->cgcu_id . "." . $this->prm->cgsc_id . "." . $this->prm->pg_id;
            $sql = 'UPDATE vin_CogContraCuenta '
            . 'SET  ger_id = ' . $this->prm->ger_id . ' , cgp_id = ' . $this->prm->cgp_id . ' , rub_id = ' .
            $this->prm->rub_id . ' , cgcu_id = ' . $this->prm->cgcu_id . ' , cgsc_id = ' . $this->prm->cgsc_id . ',
                        cog_cuenta="' . $cuenta . '"
                WHERE  pg_id="' . $this->prm->pg_id . '";';
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar();
            $conexion->desconectar();
            $this->mixScript('
                notify("El registro se actualizó exitosamente","warning");
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
            ');
        }

        $error = false;
        $cogActualizar = new sp_CogActualizar();
        $cogActualizar->setPg_id($this->prm->pg_id);

        $cogActualizar->setSis_id('SCG');
        $cogActualizar->setBit_observacion(
            'VINCULACIÓN COG DE LA PARTIDA GENERICA ' .
            $this->prm->pg_id . ' DESDE LA PANTALLA C_2_8'
        );
        $cogActualizar->setEjercicio(APP_EJERCICIO);

        $cogActualizar->execute();
        if (!$cogActualizar->getBandera()) {
            $error = true;
            $mensaje = $cogActualizar->getMensaje();
        }

        if ($error) {
            $this->mixScript('notify(" ' . $mensaje . '", "error");');
        }
    }
}

if (isset($_SESSION[_S]['prm']['C']['2']['8'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorCOG();
} else {
    WebResponse::sinPermisos();
}
