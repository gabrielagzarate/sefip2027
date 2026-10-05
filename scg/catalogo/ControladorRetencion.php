<?php
//Inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/Retencion.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/sp_RetencionActualizar.php';

class ControladorRetencion extends WebResponse
{
    public $lectura;
    public $escritura;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    public $nivel;
    public function __construct()
    {
        //Mandar a llamar Constructor Base
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['9']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['9']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaRetencion':$this->llenaTablaRetencion();
                break;
            case 'addRetencion':$this->addRetencion();
                break;
            case 'abcRetencion':$this->abcRetencion();
                break;
            default:$this->main();
                break;
        }
        $this->returnJson();
    }

    /************************************************* Función principal del Controlador************************************************/
    private function main()
    {
        $tablaRetencion = new stdClass();
        $tablaRetencion->id = 'tablaRetencion';
        $tablaRetencion->titulo = 'Retencion';
        $tablaRetencion->comunica = new stdClass();
        $tablaRetencion->comunica->pantalla = $this->pantalla;
        $tablaRetencion->comunica->metodo = 'llenaTablaRetencion';

        //Encabezados
        $tr = array();
        if ($this->escritura) {
            $th = new stdClass();
            $th->align = 'center';
            $th->width = '5%';
            $th->texto = 'Editar';
            array_push($tr, $th);
        }
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'RETENCION';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '30%';
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'NIVEL DE REGISTRO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '30%';
        $th->texto = 'NOMBRE';
        array_push($tr, $th);

        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        if ($this->escritura) {
            $td = new stdClass();
            $td->align = 'left';
            $td->class = 'EditaClave';
            $td->tipo = 'boton';
            $td->icono = 'pencil';
            $td->attr = new stdClass();
            $td->attr->clave = 'clave';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cgc_cuenta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cgc_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cta_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cta_nombre';
        array_push($trbody, $td);

        $tablaRetencion->encabezado = array($tr);
        $tablaRetencion->cuerpo = ($trbody);

        //Se agregan los elementos
        $btn_regresarIndex = '';
        $btn_addRetencion = '
            <button id="btn_addRetencion" type="button" class="btn btn-outline-primary me-2" title="Registrar nueva clave">
            <i class="fa-solid fa-plus me-1"></i>Nueva
            </button>';
        $btn_repRetencion = '
            <button id="btn_repRetencion" type="button" class="btn btn-outline-danger me-2" title="Imprimir Catálogo del Retencion" form="frm_retencion">
            <i class="fa-solid fa-file-pdf me-1"></i>PDF
            </button>';
        $btn_expRetencion = '
            <button id="btn_expRetencion" type="button" class="btn btn-outline-success me-2" title="Exportar a excel Catálogo del Retencion" form="frm_retencion" >
            <i class="fa-solid fa-file-excel me-1"></i>Excel
            </button>';

        //Se pintan los elementos en patalla
        if ($this->escritura) {
            $this->agregaTools($btn_regresarIndex . $btn_addRetencion . $btn_repRetencion . $btn_expRetencion);
        } else {
            $this->agregaTools($btn_regresarIndex . $btn_repRetencion . $btn_expRetencion);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaRetencion) . '</div>');
        $this->llenaTablaRetencion();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addRetencion").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addRetencion"
                    });
                });
            ');
        }
        $this->mixScript('
            $(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#tablaRetencion .tabla-footer").hide();
            $("#btn_repRetencion").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "9" ))

                $("body").append(form);
                form.submit();
            });
            $("#btn_expRetencion").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "9" ))

                $("body").append(form);
                form.submit();
            });

            $("#btn_regresarIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');
    }

    /************************************************* LLENA la Tabla de Retencion *************************/
    private function llenaTablaRetencion()
    {
        $sql = '
            SELECT cgc_cuenta, cgc_nombre, cta_id, cta_nombre,
                CONCAT(cgc_cuenta,".",cta_id) AS clave
            FROM vin_Retencion
                LEFT JOIN cg_CuentaCuarto USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id )
        ';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaRetencion';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaClave").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addRetencion",
                    clave: $(this).attr("clave")
                });
                return false;
            });

            $(".EliminaClave").click(function() {
                var partidaGenerica = $(this).attr("pg_id");
                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "Está a punto de eliminar la clave: "+"<strong>"+$(this).attr("pg_id")+"</strong>" + " ¿Desea continuar?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcRetencion",
                        abc: "b",
                        pg_id: partidaGenerica
                    });
                }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });
        ');
    }

    /************************************************* ALTA de nuevo Tipo de Documento Fuente *************************/
    private function addRetencion()
    {
        $datos = null;
        $abc = '';
        $accion = "Añadir";

        if (isset($this->prm->clave)) {
            $datos = explode(".", $this->prm->clave);
            $this->prm->ger_id = $datos[0];
            $this->prm->cgp_id = $datos[1];
            $this->prm->rub_id = $datos[2];
            $this->prm->cgcu_id = $datos[3];
            $this->prm->cgsc_id = $datos[4];
            $this->prm->cgc_id = $datos[5];
            $this->prm->cta_id = $datos[6];
            $datos = new Retencion($this->prm);
            $abc = 'c';
        } else {
            $datos = new Retencion();
            $abc = 'a';
        }

        $com_CtaContable = new stdClass();
        $com_CtaContable->id = 'com_CtaContable';
        $com_CtaContable->label = 'Cuenta Maestro';
        $com_CtaContable->enDiv = true;
        $com_CtaContable->required = true;
        $com_CtaContable->search = true;
        $com_CtaContable->comoAtr = "ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id";
        $com_CtaContable->sql = '
            SELECT cgc_cuenta, cgc_nombre ,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id
            FROM cg_CuentaCuarto
            INNER JOIN cat_CgSubcuenta USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id)
            WHERE ecu_id = 1
        ';
        if ($abc == 'c') {
            $com_CtaContable->sql .= '
                AND ger_id = ' . $this->prm->ger_id . '
                AND cgp_id = ' . $this->prm->cgp_id . '
                AND rub_id = ' . $this->prm->rub_id . '
                AND cgcu_id = ' . $this->prm->cgcu_id . '
                AND cgsc_id = ' . $this->prm->cgsc_id . '
                AND cgc_id = ' . $this->prm->cgc_id;
            $accion = "Guardar";
        }

        $inp_NombreRet = new stdClass();
        $inp_NombreRet->id = 'inp_NombreRet';
        $inp_NombreRet->label = 'Nombre de la Retención';
        $inp_NombreRet->placeholder = 'NOMBRE';
        $inp_NombreRet->enDiv = true;
        $inp_NombreRet->required = true;
        $inp_NombreRet->maxLength = 200;
        $inp_NombreRet->value = $datos->getNombre();

        $sql = '
            SELECT IF((MAX(cta_id) + 1 )=9999,0,(MAX(cta_id) + 1 )) AS maximo
            FROM vin_Retencion';
        $this->setSql($sql);
        $this->conectar();
        $this->consultar();
        $max = $this->extraer();
        $max = $max['maximo'];
        $this->desconectar();

        $inp_NivelReg = new stdClass();
        $inp_NivelReg->id = 'inp_NivelReg';
        $inp_NivelReg->label = 'Nivel de Registro';
        $inp_NivelReg->placeholder = '9001';
        $inp_NivelReg->enDiv = true;
        $inp_NivelReg->comoAtr = "cta_id";
        $inp_NivelReg->value = $abc == "a" ? $max : $datos->getCta_id();
        $inp_NivelReg->required = true;
        $inp_NivelReg->maxLength = 4;

        if ($abc == 'c') {
            $inp_NivelReg->readonly = "readonly";
        }

        //Se agregan los elementos
        $com_CtaContable = $this->ponCombo($com_CtaContable);
        $inp_NombreRet = $this->ponInput($inp_NombreRet);
        $inp_NivelReg = $this->ponInput($inp_NivelReg);

        //Se pintan los elementos en patalla
        $html = '
            <div class="row">
                <fieldset class="col-md-12 col-xs-12 mb-4">
                    ' . $com_CtaContable . '
                </fieldset>
                <fieldset class="col-md-9 col-xs-9">
                    ' . $inp_NombreRet . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 mb-4">
                    ' . $inp_NivelReg . '
                </fieldset>
            </div>
        ';

        $btnRegresaRetencion = '
            <button id="btn_regresaRetencion" type="button" class="btn btn-outline-secondary me-1" title="Regresar al Catálogo del Retencion"><i class="fa-solid fa-circle-left"></i>
                Regresar
            </button>';
        $btnGuardarRetencion = '
            <button id="btn_guardarRetencion" type="button" class="btn btn-outline-success" title="Guardar Catálogo del Retencion" form="GuardarRetencion"><i class="fa-solid fa-floppy-disk"></i>
                ' . $accion . '
            </button>';

        $this->agregaTools($btnRegresaRetencion . $btnGuardarRetencion);

        $this->mixHtml($html);
        $this->mixScript('
            $("#com_CtaContable").change( function(){
                valor = $("#com_CtaContable").find("option:selected").text();
                valor = valor.split("-");
                $("#inp_NombreRet").val(valor[1]);
            });

            $("#btn_guardarRetencion").click(function() {
                if($("#inp_NivelReg").val().charAt(0) == "9" && $("#inp_NivelReg").val().length == 4){
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcRetencion",
                        abc: "' . $abc . '",
                        ger_id: $("#com_CtaContable").find("option:selected").attr("ger_id"),
                        cgp_id: $("#com_CtaContable").find("option:selected").attr("cgp_id"),
                        rub_id: $("#com_CtaContable").find("option:selected").attr("rub_id"),
                        cgcu_id: $("#com_CtaContable").find("option:selected").attr("cgcu_id"),
                        cgsc_id: $("#com_CtaContable").find("option:selected").attr("cgsc_id"),
                        cgc_id: $("#com_CtaContable").find("option:selected").attr("cgc_id"),
                        cta_id: $("#inp_NivelReg").val(),
                        cta_nombre: $("#inp_NombreRet").val()

                    },true);
                } else {
                    notify("Nivel de Registro debe ser de 4 digitos y comenzar con 9","error");
                }
            });

            $("#btn_regresaRetencion").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');
    }

    /************************************************* ABC de Tipo de Documento Fuente *************************/
    private function abcRetencion()
    {
        if ($this->prm->cta_id >= 9999) {
            $this->mixScript('notify("La clave ' . $this->prm->cta_id . ' no puede ser utilizada","error");');
            return;
        }

        switch ($this->prm->abc) {
            case 'a':
                $con = new AccesoDatos();
                $sql = 'SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id
                        FROM vin_Retencion
                        WHERE cta_id= :cta_id;';

                $params = [':cta_id' => $this->prm->cta_id];

                $con->setSql($sql);
                $con->conectar();
                $con->consultar($params);
                $totlR = $con->getNumeroRegistros();
                $con->desconectar();

                if ($totlR > 0) {
                    $this->mixScript('notify("La clave ' . $this->prm->cta_id . ' ya está registrada en la base de datos","error");');
                    return;
                } else {
                    $sql = '
                        SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id
                            FROM vin_Retencion
                            WHERE ger_id= :ger_id
                            AND cgp_id = :cgp_id
                            AND rub_id = :rub_id
                            AND cgcu_id = :cgcu_id
                            AND cgsc_id = :cgsc_id
                            AND cgc_id = :cgc_id
                    ';

                    $param = [
                        ':ger_id' => $this->prm->ger_id,
                        ':cgp_id' => $this->prm->cgp_id,
                        ':rub_id' => $this->prm->rub_id,
                        ':cgcu_id' => $this->prm->cgcu_id,
                        ':cgsc_id' => $this->prm->cgsc_id,
                        ':cgc_id' => $this->prm->cgc_id,
                    ];

                    $this->setSql($sql);
                    $this->conectar();
                    $this->consultar($param);
                    $totalR = $this->getNumeroRegistros();
                    $this->desconectar();
                    if ($totalR > 0) {
                        $this->mixScript('notify("La clave ' . $this->prm->cgc_id . ' ya está registrada en la base de datos","error");');
                        return;
                    } else {
                        Retencion::abc($this->prm);

                        $this->mixScript('
                            notify("El registro se ha agregado correctamente","success");
                            comunica("' . $this->pantalla . '",{
                                metodo:"main"
                            });
                        ');
                    }
                }
                break;

            case 'b':
                Retencion::abc($this->prm);
                $this->mixScript('notify("El registro se ha eliminado correctamente","error");');
                $this->llenaTablaRetencion();
                break;

            case 'c':
                $sql = '
                    SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id
                    FROM vin_Retencion
                    WHERE cta_id= :cta_id';

                $parms = [':cta_id' => $this->prm->cta_id];

                $this->conectar();
                $this->setSql($sql);
                $this->consultar($parms);
                $data = $this->extraer();
                $this->desconectar();

                Retencion::abc($this->prm);
                $this->mixScript('
                    notify("El registro se actualizó exitosamente","warning");
                    comunica("' . $this->pantalla . '",{
                        metodo: "main"
                    });
                ');
                break;
            default:break;
        }

        $error = false;
        $retencionActualizar = new sp_RetencionActualizar();
        $retencionActualizar->setGer_id($this->prm->ger_id);
        $retencionActualizar->setCgp_id($this->prm->cgp_id);
        $retencionActualizar->setRub_id($this->prm->rub_id);
        $retencionActualizar->setCgcu_id($this->prm->cgcu_id);
        $retencionActualizar->setCgsc_id($this->prm->cgsc_id);
        $retencionActualizar->setCgc_id($this->prm->cgc_id);

        $retencionActualizar->setSis_id('SCG');
        $retencionActualizar->setBit_observacion('VINCULACIÓN DE LA CUENTA DE RETENCION ' .
            $this->prm->ger_id . '.' .
            $this->prm->cgp_id . '.' .
            $this->prm->rub_id . '.' .
            $this->prm->cgcu_id . '.' .
            $this->prm->cgsc_id . '.' .
            $this->prm->cgc_id . '.' . ' DESDE LA PANTALLA C_2_9'
        );
        $retencionActualizar->setEjercicio(APP_EJERCICIO);

        $retencionActualizar->execute();
        if (!$retencionActualizar->getBandera()) {
            $error = true;
            $mensaje = $retencionActualizar->getMensaje();
        }
        if ($error) {
            $this->mixScript('notify(" ' . $mensaje . '", "error");');
        }
    }
}
if (isset($_SESSION[_S]['prm']['C']['2']['9'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorRetencion();
} else {
    WebResponse::sinPermisos();
}
