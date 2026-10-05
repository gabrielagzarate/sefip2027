<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelScp.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaScp.php';

/**
 * Autor @Eduardo Venegas              Fecha de Creación:  19/12/2018
 * Se actualizaron las variables de sesion de s18 a s19
 * Se valida los permisos de lectura para poder acceder a Consulta CLC.
Ultima Fecha de actualización 19/12/18
 */

class ControladorConsultaClcUr extends WebResponse
{
    public $ur;
    public $gpo_id;
    public $ur_id;
    public $model;
    public $vista;
    public function __construct()
    {
        parent::__construct();
        $this->ur = ($_SESSION[_S]['nvj_id'] < 3) ? $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id']
        : $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];

        $this->gpo_id = ($_SESSION[_S]['nvj_id'] < 3) ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = ($_SESSION[_S]['nvj_id'] < 3) ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $this->model = new modelscp();
        $this->vista = new vistasScp();
        $this->pantalla = 'scp/Consultas/ControladorConsultaClcUr.php';

        if (isset($_SESSION[_S]['prm']['S']['5']['8']['1'])) {
            switch ($_SESSION[_S]['nvj_id']) {
                case 1:case 2:case 3:case 4:case 5:case 6:case 7:
                    switch ($this->metodo) {
                        case 'llenaTablaClc':$this->llenaTablaClc();
                            break;
                        default:$this->main();
                            break;
                    }
            }
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools('');
        $this->mixScript('
            function validaTip(){
                var s = "";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida")){s =$(this).attr("id");}
                });
                if(s ==""){
                    return false
                }else return s;
            }
        ');

        $bxClc = new stdClass();
        $bxClc->id = 'bxClc';
        $bxClc->name = 'bxClc';
        $bxClc->label = 'Número de Clc';
        $bxClc->class = 'limpiar';
        $bxClc->placeholder = 'Ingresa el número de CLC';
        $bxClc->enDiv = true;

        $bxEstado = new stdClass();
        $bxEstado->id = 'bxEstado';
        $bxEstado->width = '400px';
        $bxEstado->class = 'limpiar';
        $bxEstado->sql = 'select ecl_id,ecl_nombre from cat_EstadoClc where ecl_id in (1,2,3,4,17)';
        $bxEstado->label = 'SELECCIONA EL ESTADO DE LA CLC';
        $bxEstado->enRow = 1;

        $buscar = new stdClass();
        $buscar->value = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->color = 'gris';
        $buscar->icon = 'fa fa-search me-1';

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->color = 'cielo';
        $clean->icon = 'fa fa-refresh me-1';

        $datos = $this->model->getTiposClc();
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $radios = $this->vista->vwTipoClc($datos, $this->prm->tipo);
        //Se quito el boton
        $radios .= '
        <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
        <legend align="center">Busqueda por:</legend>
        <div class="row">
            <fieldset class="col-md-2 mb-4">
                ' . $this->ponInput($bxClc) . '
            </fieldset>
            <fieldset class="col-md-4">
                ' . $this->templateCombo($bxEstado) . '
            </fieldset>
            <fieldset class="col-md-2">
            <br>
            ' . $this->ponBoton($buscar) . '
            </fieldset>
        </div>

        </form>
        ';

        $tabla = new stdClass();
        $tabla->id = 'tbl-ceres';
        $tabla->titulo = 'Consultar CLC';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClc';
        $tabla->comunica->bxClc = '{$("#bxClc").val()}';
        $tabla->comunica->tipo = '{validaTip()}';
        $tabla->numFilas = 150;

        $tr = [];

        $tabla->accion = new stdClass();
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/rep_clc.php';
        $tabla->accion->excel = true;
        $tabla->accion->urlExcel = 'rep/excel/scp/afectaciones/repClc.php';

        //UR
        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        //NUMERO DE CLC
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        //CONCEPTO DE LA CLC
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        //FECHA
        $th = new stdClass();
        $th->texto = 'CERE(CO)';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NUMERO';
        array_push($tr, $th);
        //FECHA
        $th = new stdClass();
        $th->texto = 'FECHA ELAB.';
        array_push($tr, $th);

        //TIPO DE CLC
        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        //SUBTIPO DE CLC
        $th = new stdClass();
        $th->texto = 'SUBTIPO';
        array_push($tr, $th);

        //SUBTIPO DE CLC
        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '10%';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($trbody, $td);

        //numero de clc
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        //concepto
        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cereco';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        array_push($trbody, $td);

        //fecha
        $td = new stdClass();
        $td->campoTxt = 'clc_fechaElaboracion';
        array_push($trbody, $td);

        //tipo de clc
        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        //subtipo de clc
        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
        array_push($trbody, $td);

        //Estado de la Clc
        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->recargaCombo($bxEstado);

        $this->mixScript('
            function observarBotonAlertify() {
                const observer = new MutationObserver((mutations, obs) => {
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                        btnAceptar.textContent = "ACEPTAR";
                        obs.disconnect(); // deja de observar
                    }
                });

                // Observar el body o el contenedor de Alertify si lo conoces (como #alertify)
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }

            $("#bxClc").blur(function(){
                if( $("#bxClc").val().length <1 ){
                    observarBotonAlertify(); // <- iniciar el observer antes
                    notify("Debe escribir al menos 1 digito", "warning")
                    
                }
            });

            $("#buscar").click(function(){
                var obj = {"metodo":"llenaTablaClc"};
                obj.bxClc = $("#bxClc").val();
                obj.bxEstado = $("#bxEstado").val();
                obj.tipo=validaTip();
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });

            function show(id,id_name){
                if(!$("#"+id).hasClass("tcl_ida")){
                    $(".tcl_idfont").removeClass("tcl_ida");
                    $(".tcl_idfont").removeClass("loadingclas");
                    $(".tcl_idfont").addClass("tcl_idn");
                    $(".tcl_idfont").removeAttr("checked");
                    $("#"+ id).removeClass("tcl_idn");
                    $("#"+id).addClass("loadingclas");
                    if (comunica("' . $this->pantalla . '",{metodo:"llenaTablaClc",tipo:id,nombre:id_name},false,false)){
                        $("#"+id).addClass("tcl_ida");
                    }
                }
            }

            $(".tcl_idfont").click(function(){
                if($(this).hasClass("tcl_idb")){    notify("Este tipo de CLC no esta disponible","error");    }
                else{    show($(this).attr("id"),$(this).attr("nombre"));    }
            });

            $("#clean").click(function(){
                $(".rad_afectacion:checked").removeAttr("checked");

                $(".limpiar").val("");

                comunica("scp/ControladorConsultaClcUr.php",{
                metodo:"main",
                });
                return false;
            });

            $("#btnregresarindex").click(function(){
                document.location.href = "../sinpres2014";
            });
        ');

    }
    private function llenaTablaClc()
    {
        $wf = '';
        $parametros = [];
        $ue = isset($_SESSION[_S]['ue_id']) && $_SESSION[_S]['ue_id'] != '' ? $_SESSION[_S]['ue_id'] : '001';

        $wf .= ' AND gpo_id= :gpo_id AND ur_id= :ur_id ' . PHP_EOL;
        $parametros += [
            'gpo_id' => $_SESSION[_S]['gpo_id'],
            'ur_id'  => $_SESSION[_S]['ur_id'],
        ];

        if (isset($this->prm->tipo) and $this->prm->tipo != '') {
            $wf .= 'AND  tcl_id= :tcl_id' . PHP_EOL;
            $parametros += [
                'tcl_id' => $this->prm->tipo,
            ];
        }

        if (isset($this->prm->bxClc) and $this->prm->bxClc != '') {
            $wf .= 'AND clc_id= :clc_id ' . PHP_EOL;
            $parametros += [
                'clc_id' => $this->prm->bxClc,
            ];
        }
        if (isset($this->prm->bxEstado) and $this->prm->bxEstado != '') {
            if ($this->prm->bxEstado != 4) {
                $wf .= 'AND  ecl_id= :ecl_id ' . PHP_EOL;
                $parametros += [
                    'ecl_id' => $this->prm->bxEstado,
                ];
            }
        }

        if ($_SESSION[_S]['nvj_id'] == 1) {
            $wf .= 'AND  scp_Clc.ue_id="' . ($ue) . '"' . PHP_EOL;
        }

        $this->conectar();

        $sql = '
            SELECT gpo_id,ur_id,clc_id,clc_concepto,DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,tcl_nombre,stc_nombre,
                CASE
                WHEN ecl_id=1 then "GENERADA POR LA UR"
                WHEN ecl_id=2 then "AUTORIZADA POR LA UR"
                WHEN ecl_id=3 then "ENVIADA A FINANZAS"
                WHEN ecl_id between 4 and 16 THEN "RECIBIDA POR FINANZAS"
                WHEN clc_recursoFederal=2 AND ecl_id=17 THEN "PAGO TRANSFERIDO"
                WHEN clc_recursoFederal=2 AND ecl_id=18 THEN "CERE O CERECO PAGADA"
                WHEN clc_recursoFederal=1 AND ecl_id=17 THEN "RECURSO FEDERAL TRANSFERIDO"
                WHEN clc_recursoFederal=1 AND ecl_id=18 THEN "RECURSO FEDERAL PAGADO"
                WHEN ecl_id=21 THEN "CLC CANCELADA"
                WHEN ecl_id=20 THEN "CLC INHABILITADA"
            END as ecl_nombre,
            if(clc_tipocereCo=1 AND clc_generacionCereCo IS NOT NULL,"CERE",if( clc_tipocereCo=2  AND clc_generacionCereCo IS NOT NULL, "CERECO","SIN CERE(CO)"))cereco,
            if(clc_generacionCereCo IS  NOT  NULL,clc_cereCo,"")clc_cereCo,
            TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2) as importe, "SI" AS consulta
            FROM scp_Clc
            LEFT JOIN scp_ClcAuxiliar b USING (gpo_id,ur_id,clc_id,ecl_id)
            LEFT JOIN cat_TipoClc USING(tcl_id)
            LEFT JOIN cat_SubtipoClc USING(tcl_id,stc_id)
            LEFT JOIN cat_ModoPagoClc USING(mpc_id)
            LEFT JOIN cat_EstadoClc USING(ecl_id)
            WHERE clc_id != 0
        ' . $wf;
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ceres';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 150;

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("NO HAY REGISTROS","warning");');
        }
        $this->mixScript('

            $(".detalle").click(function() {
                comunica("' . $this->pantalla . '",{
                metodo:"DetalleCere",
                gpo_id:$(this).attr("gpo_id"),
                ur_id:$(this).attr("ur_id"),
                clc_id:$(this).attr("clc_id"),
                mpc_id:$(this).attr("mpc_id"),
                tipo:$(this).attr("tcl_id"),
                stc_id:$(this).attr("stc_id") });
                return false;
            });

            $(".imprimir").click(function(){
                $("#gpo_id").val( $(this).attr("gpo_id"));
                $("#ur_id").val( $(this).attr("ur_id"));
                $("#clc_id").val( $(this).attr("clc_id"));
                $("#consulta").val("si");
                $("#imprep").append( $("<input/>").attr("id","reimprecion").attr("name","reimprimir"));
                $("#clc_ajuste").val( $(this).attr("clc_ajuste"));
                $("#imprep").submit();
                $("#reimprecion").remove();
                }
            );

            $(".excel").click(function(){
                $("#excel_gpo").val( $(this).attr("gpo_id"));
                $("#excel_ur").val( $(this).attr("ur_id"));
                $("#excel_clc").val( $(this).attr("clc_id"));
                $("#excel").submit();
            }
            );
        ');

    }
}

if (isset($_SESSION[_S]['prm']['S']['5']['8']['1'])) {
    // PERMISOS DEL USUARIO QUE PUEDE VER ESTE CONTROLADOR
    new ControladorConsultaClcUr();
} else {
    WebResponse::sinPermisos();
}
