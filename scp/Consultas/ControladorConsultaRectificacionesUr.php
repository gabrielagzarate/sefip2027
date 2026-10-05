<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';

class ControladorConsultaRectificacionesUr extends WebResponse
{
    protected UnidadResponsable $ur;
    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();

        //Instanciando la Ur para verificar lo del cierre del poa $_SESSION[_S][_S]
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        switch ($this->metodo) {
            case 'llenaTablaRectificaciones':
                $this->llenaTablaRectificaciones();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    public function main()
    {
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

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->color = 'amarillo';
        $clean->icon = 'trash me-2';

        $radios = '';
        $radios .= '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
            <legend align="center">Busqueda por:</legend>
                <div class="row mb-3">
                    <fieldset class="col-md-3">
                        ' . $this->ponInput($rectificacion) . '
                    </fieldset>
                    <fieldset class="col-md-2">
                        ' . $this->ponInput($numeroClc) . '
                    </fieldset>
                    <fieldset class="col-md-3 pt-3">
                    ' . $this->ponBoton($buscar) . '   ' . $this->ponBoton($clean) . '
                    </fieldset>
                </div>

            </form>
        ';

        $tabla = new stdClass();
        $tabla->id = 'tbl-rect';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaRectificaciones';
        $tabla->comunica->nombre = '{$("#nombre").val()}';
        $tabla->comunica->factura = '{$("#factura").val()}';
        $tabla->comunica->numeroClc = '{$("#numeroClc").val()}';
        $tabla->comunica->rectificacion = '{$("#rectificacion").val()}';
        $tabla->numFilas = 5;
        $tabla->filasAmostrar = [5, 10, 15, 20, 50];
        $tabla->accion = new stdClass();
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
        $th->texto = 'FECHA ELAB.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        //IMPORTE
        $th = new stdClass();
        $th->texto = 'IMPORTE';
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
        $td->campoTxt = 'rct_fechaElaboracion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->mixScript('
            $("#buscar").click(function(){
                ' . $this->wrLlamaLlenaTabla('tbl-rect') . '
            });

            $("#clean").click(function(){
                $(".rad_afectacion:checked").removeAttr("checked");
                $(".limpiar").val("");
                comunica("' . $this->pantalla . '",{
                metodo:"main",
                });
                return false;
            });

        ');
        $this->mixScript('
            $("#btnregresarindex").click(function(){
                document.location.href = "../sinpres2014";
            });
        ');

        $this->llenaTablaRectificaciones();
    }

    private function llenaTablaRectificaciones()
    {
        $wf = '';

        $wf .= ($wf == '') ? 'WHERE gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND  ur_id = ' . $_SESSION[_S]['ur_id'] . ' ' :
            ' AND gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND  ur_id = ' . $_SESSION[_S]['ur_id'] . ' ';

        if (isset($this->prm->rectificacion) && $this->prm->rectificacion != '') {
            $wf .= ($wf == '') ? 'WHERE rct_id = ' . $this->prm->rectificacion . ' ' : ' AND rct_id = ' . $this->prm->rectificacion ;
        }

        if (isset($this->prm->numeroClc) && $this->prm->numeroClc != '') {
            $wf .= ($wf == '') ? 'WHERE clc_id = ' . $this->prm->numeroClc . ' ' : ' AND clc_id = ' . $this->prm->numeroClc ;
        }

        $this->conectar();
        $sql = '
            SELECT
                gpo_id,ur_id,rct_id,clc_id,rct_justificacion,DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") rct_fechaAutorizacion,
                DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") rct_fechaElaboracion ,
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
                if(rct_reintegro = 1,
                    "CON REINTEGRO",
                    "SIN REINTEGRO") AS reintegro,
                    b.tcl_id
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
        $nav->idTabla = 'tbl-rect';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }

        $this->mixScript('
            $("#btnregresarindex").click(function(){
                document.location.href = "../sinpres2017";
            });
        ');
    }
}
if (isset($_SESSION[_S]['prm']['S']['5']['18']['1'])) {
    new ControladorConsultaRectificacionesUr();
} else {
    WebResponse::sinPermisos();
}
