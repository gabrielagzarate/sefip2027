<?php

session_start();

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';

class ControladorConsultaRectificaciones extends WebResponse
{
    protected UnidadResponsable $ur;
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/Consultas/ControladorConsultaRectificaciones.php';
        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                switch ($this->metodo) {
                    case 'llenaTablaRectificaciones':
                        $this->llenaTablaRectificaciones();
                        break;
                    default:
                        $this->main();
                        break;
                }
        }
        $this->returnJson();
    }

    public function main()
    {

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->name = 'ur';
        $ur->label = 'Unidad Responsable';
        $ur->class = 'limpiar';
        $ur->placeholder = 'Ingresa la UR';
        $ur->enDiv = true;

        $rectificacion = new stdClass();
        $rectificacion->id = 'rectificacion';
        $rectificacion->name = 'rectificacion';
        $rectificacion->label = 'Número de Rectificación';
        $rectificacion->class = 'limpiar';
        $rectificacion->placeholder = 'Ingresa el número de Rectificación';
        $rectificacion->enDiv = true;

        $numeroClc = new stdClass();
        $numeroClc->id = 'numeroClc';
        $numeroClc->name = 'numeroClc';
        $numeroClc->label = 'No de CLC';
        $numeroClc->class = 'limpiar';
        $numeroClc->placeholder = 'Ingresa No de CLC';
        $numeroClc->enDiv = true;

        $buscar = new stdClass();
        $buscar->value = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->color = 'gris';
        $buscar->icon = 'search me-2';

        $radios = '';
        $radios .= '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
            <legend align="center">Busqueda por:</legend>
            <div class="row mb-3">
                <fieldset class="col-md-2">
                    ' . $this->ponInput($ur) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($rectificacion) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($numeroClc) . '
                </fieldset>
                <fieldset class="col-md-2 mt-3">
                ' . $this->ponBoton($buscar) . '
                </fieldset>
            </div>

            </form>
            ';

        $tabla = new stdClass();
        $tabla->id = 'tbl-recti';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaRectificaciones';
        $tabla->comunica->ur = '{$("#ur").val()}';
        $tabla->comunica->nombre = '{$("#nombre").val()}';
        $tabla->comunica->factura = '{$("#factura").val()}';
        $tabla->comunica->numeroClc = '{$("#numeroClc").val()}';
        $tabla->comunica->rectificacion = '{$("#rectificacion").val()}';
        $tabla->accion = new stdClass();
        $tabla->accion->prm = ['gpo_id', 'ur_id', 'clc_id', 'rct_id', 'rct_gpo', 'rct_ur', 'tcl_id', 'rct_reintegro'];
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/scp/afectaciones/rep_RectificacionClc.php';

        $tr = [];

        //UR
        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        //NUMERO DE CLC
        $th = new stdClass();
        $th->texto = 'RCT';
        array_push($tr, $th);

        //CONCEPTO DE LA CLC
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        //FECHA
        $th = new stdClass();
        $th->texto = 'JUSTIFIACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);
        //FECHA
        $th = new stdClass();
        $th->texto = 'FECHA APLIC.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        //IMPORTE
        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        //TIPO DE CLC
        $th = new stdClass();
        $th->texto = 'REIMPRESIÓN';
        array_push($tr, $th);

        $trbody = [];

        /* UR*/
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rct_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rct_justificacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rct_fechaAutorizacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($trbody, $td);
        $td = new stdClass();

        $td->campoTxt = 'total';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'print';
        $td->color = 'cielo';
        $td->class = 'reimprimir';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->clc_id = 'clc_id';
        $td->attr->rct_id = 'rct_id';
        $td->attr->rct_gpo = 'rct_gpo';
        $td->attr->rct_ur = 'rct_ur';
        $td->attr->tipo = 'tcl_id';
        $td->attr->rct_reintegro = 'rct_reintegro';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        //para exportar el pdf
        $this->mixHtml(
            '<form  id="imprep" action="rep/doc/scp/afectaciones/rep_RectificacionClc.php" method="post" target="_blank">
                <input id="gpo_id" name="gpo_id" type="hidden" ">
                <input id="ur_id" name="ur_id" type="hidden" ">
                <input id="clc_id" name="clc_id" type="hidden" ">
                <input id="rct_id" name="rct_id" type="hidden" ">
                <input id="rct_gpo" name="rct_gpo" type="hidden" ">
                <input id="rct_ur" name="rct_ur" type="hidden" ">
                <input id="tcl_id" name="tcl_id" type="hidden" ">
                <input id="reimprimir" name="reimprimir" value type="hidden" ">
                <input id="rct_reintegro" name="rct_reintegro" type="hidden" ">
            </form>'
        );

        $this->mixScript('
            $("#buscar").click(function(){
                ' . $this->wrLlamaLlenaTabla('tbl-recti') . '
            });

            $("#clean").click(function(){
                $(".limpiar").val("");
                comunica("scp/ControladorConsultaClc.php",{
                    metodo:"main",
                });
                return false;
            });

            $("#btnRegresarIndex").click (function(){
                comunica("scp/ControladorConsultaRectificaciones",{metodo:"main"});
                return false;
            });
        ');
    }
    private function llenaTablaRectificaciones()
    {

        $wf = '';
        if (isset($this->prm->ur) and $this->prm->ur != '') {
            $ur = substr($this->prm->ur, 1, 2);
            $gpo_id = substr($this->prm->ur, 0, 1);
            $wf .= ($wf == '') ? 'WHERE gpo_id = ' . $gpo_id . ' AND  ur_id = "' . $ur . '"' : ' AND gpo_id = ' . $gpo_id . ' AND  ur_id = "' . $ur . '" ';
        }
        if (isset($this->prm->rectificacion) and $this->prm->rectificacion != '') {
            $wf .= ($wf == '') ? 'WHERE rct_id = ' . $this->prm->rectificacion . ' ' : ' AND rct_id = ' . $this->prm->rectificacion . '';
        }

        if (isset($this->prm->numeroClc) and $this->prm->numeroClc != '') {
            $wf .= ($wf == '') ? 'WHERE clc_id = ' . $this->prm->numeroClc . '' : ' AND clc_id = ' . $this->prm->numeroClc . '';
        }

        $this->conectar();
        $sql = '
            SELECT
                gpo_id,ur_id,rct_id,clc_id,rct_justificacion,DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
                DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion ,
                rct_percepcion AS percepciones,rct_retencion AS retenciones, rct_percepcion - rct_retencion AS total,tcl_nombre,
                CASE
                    WHEN
                        a.ecl_id = 1
                    THEN
                        "GENERADA POR LA UR"
                    WHEN
                        a.ecl_id = 2
                    THEN
                        "AUTORIZADA POR LA UR"
                    WHEN
                        a.ecl_id = 3
                    THEN
                        "ENVIADA A FINANZAS"
                    WHEN
                        a.ecl_id = 4
                    THEN
                        "RECIBIDA POR FINANZAS"
                    WHEN
                        a.ecl_id = 5 AND b.clc_recursoFederal = 2
                    THEN
                        "REVISADA POR FINANZAS"
                    WHEN
                        a.ecl_id = 5 AND b.clc_recursoFederal = 1
                    THEN
                        "EN PROCESO DE SER APLICADA"
                    WHEN
                        a.ecl_id = 6 AND b.ecl_id = 18 AND b.clc_recursoFederal = 1
                    THEN
                        "APLICADA POR LA UR "
                    WHEN
                        a.ecl_id = 6 AND b.ecl_id = 18 AND b.clc_recursoFederal = 2
                    THEN
                        "APLICADA POR FINANZAS"
                END AS ecl_nombre,
                if(rct_reintegro = 1,"CON REINTEGRO","SIN REINTEGRO") reintegro,
                b.tcl_id,rct_reintegro,rct_gpo,rct_ur
            FROM scp_MaestroRectificacion a
                INNER JOIN cat_EstadoClc using(ecl_id)
                INNER JOIN scp_Clc b USING(gpo_id,ur_id,clc_id)
                INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
                INNER JOIN cat_TipoClc USING(tcl_id)
            ' . $wf . '
            GROUP BY gpo_id,ur_id,rct_id
            ORDER BY gpo_id,ur_id,rct_id
        ';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-recti';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros","warning");
            ');
        }
        $this->mixScript('

            $(".reimprimir").click(function(){
                $("#gpo_id").val( $(this).attr("gpo_id"));
                $("#ur_id").val( $(this).attr("ur_id"));
                $("#clc_id").val( $(this).attr("clc_id"));
                $("#rct_id").val( $(this).attr("rct_id"));
                $("#rct_reintegro").val( $(this).attr("rct_reintegro"));
                $("#tipo").val( $(this).attr("tipo"));
                alertify.prompt("Consulta Rectificaciones","Ingresa la fecha y hora de impresión original",
                    "00/00/00 - 00:00:00",
                    function(evt, fecha) {
                        if ( (fecha == "") || (fecha == "00/00/00 - 00:00:00")){
                            notify("Ingrese una fecha y hora de impresión válida","warning");
                        } else {
                            if (fecha !== null) {
                                $("#imprep").append( $("<input/>").attr("id","reimpresion").attr("name","reimprimir").val(fecha) );
                                $("#imprep").submit();
                                $("#reimpresion").remove();
                            }
                        }
                    }, undefined
                ).set("labels", {ok: "Aceptar", cancel: "Cancelar"});
            });
            ');
    }
}

if (isset($_SESSION[_S]['prm']['S']['5']['17']['56'])) {
    new ControladorConsultaRectificaciones();
} else {
    WebResponse::sinPermisos();
}
