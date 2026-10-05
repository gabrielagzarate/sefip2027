<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

/**
 *Autor @Eduardo Venegas              Fecha de Creación:  02/01/2019
 *Se actualizo las consultas, por el modo estricto que se emplea en la base de datos.
 *Se actualizo la ruta del reporte generado en excel.
 *Se actualio referencias a fechas 2018 a 2019, como variables de sesion.
Ultima Fecha de actualización 03/01/2019
 */

class ControladorConsultaProveedores extends WebResponse
{
    public UnidadResponsable $ur;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        if (isset($_SESSION[_S]['prm']['S']['5']['14']['1'])) {
            switch ($this->metodo) {
                case 'llenaTablaProveedores':
                    $this->llenaTablaProveedores();
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
        $rfc = new stdClass();
        $rfc->id = 'rfc';
        $rfc->name = 'rfc';
        $rfc->label = 'RFC del Proveedor';
        $rfc->class = 'limpiar';
        $rfc->placeholder = 'Ingresa el RFC';
        $rfc->enDiv = true;

        $nombre = new stdClass();
        $nombre->id = 'nombre';
        $nombre->name = 'nombre';
        $nombre->label = 'Nombre del Proveedor';
        $nombre->class = 'limpiar';
        $nombre->placeholder = 'Ingresa el nombre del Proveedore';
        $nombre->enDiv = true;

        $factura = new stdClass();
        $factura->id = 'factura';
        $factura->name = 'factura';
        $factura->label = 'No de Factura';
        $factura->class = 'limpiar';
        $factura->placeholder = 'Ingresa No de Factura';
        $factura->enDiv = true;

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
        $buscar->class = 'me-1';
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
            <div class="row mb-4">
                <fieldset class="col-md-2">
                    ' . $this->ponInput($rfc) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($nombre) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($factura) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($numeroClc) . '
                </fieldset>
                <fieldset class="col-md-4 pt-3">
                ' . $this->ponBoton($buscar) . '   ' . $this->ponBoton($clean) . '
                </fieldset>
            </div>
            </form>
        ';

        $tabla = new stdClass();
        $tabla->id = 'tbl-prov';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaProveedores';
        $tabla->comunica->rfc = '{$("#rfc").val()}';
        $tabla->comunica->nombre = '{$("#nombre").val()}';
        $tabla->comunica->factura = '{$("#factura").val()}';
        $tabla->comunica->numeroClc = '{$("#numeroClc").val()}';
        $tabla->numFilas = 150;

        $tr = [];

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLAVE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FACTURA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA REF.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'N° BANCO DOC.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'N° CONTROL';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'AFECTACIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CERE(CO)';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚMERO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PERCEPCIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'RETENCIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'rfc_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'concepto';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clave';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'referencia';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'fechaReferencia';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'bdo_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'numcontrol';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cereco';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'percepciones';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'retenciones';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'total';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $ListadoExcel = '';
        $ListadoExcel = new stdClass();
        $ListadoExcel->id = 'ListadoExcel';
        $ListadoExcel->color = 'verde';
        $ListadoExcel->class = '';
        $ListadoExcel->icon = 'file-excel me-2';
        $ListadoExcel->value = 'Excel';
        $ListadoExcel->click = '
            var form = $("<form>")
            .attr("action", "rep/excel/scp/consultas/rep_ConsultaProveedores.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            .append($("<input>").attr("type", "hidden").attr("name", "rfc").val( $("#rfc").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "nombre").val( $("#nombre").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "factura").val( $("#factura").val()))
            .append($("<input>").attr("type", "hidden").attr("name", "numeroClc").val( $("#numeroClc").val()));
            $("body").append(form);
            form.submit();
            form.remove();
        ';
        $ListadoExcel = $this->ponBoton($ListadoExcel);

        $tabla->comunica->rfc = '{$("#rfc").val()}';
        $tabla->comunica->nombre = '{$("#nombre").val()}';
        $tabla->comunica->factura = '{$("#factura").val()}';
        $tabla->comunica->numeroClc = '{$("#numeroClc").val()}';

        $this->agregaTools($ListadoExcel);
        $this->mixScript('
            $("#buscar").click(function(){
                var obj = {"metodo":"llenaTablaProveedores"};
                obj.rfc = $("#rfc").val();
                obj.nombre = $("#nombre").val();
                obj.factura = $("#factura").val();
                obj.numeroClc = $("#numeroClc").val();
                console.log(obj);
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });

            $("#clean").click(function(){
                $(".limpiar").val("");
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    });
                return false;
            });
        ');
        $this->mixScript('
            $(".nuevo").click(function(){
                comunica("' . $this->pantalla . '",{
                "metodo":"TipoPersona"
                });
            });
        ');
        $this->llenaTablaProveedores();
    }

    private function llenaTablaProveedores()
    {
        $nombre = $rfc = $clc = $facturanp = $facturanc = $factura = $np = $and = $wh = $wf = '';
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $wf = 'WHERE  gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '  and  ur_id =' . $_SESSION[_S]['clasAdm-ur'] . ' ';
            $wh = 'WHERE  gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '  and  ur_id =' . $_SESSION[_S]['clasAdm-ur'] . ' ';
        } else {
            $wf = 'WHERE  gpo_id=' . $_SESSION[_S]['gpo_id'] . '  and  ur_id =' . $_SESSION[_S]['ur_id'] . ' ';
            $wh = 'WHERE  gpo_id=' . $_SESSION[_S]['gpo_id'] . '  and  ur_id =' . $_SESSION[_S]['ur_id'] . ' ';
        }

        if (isset($this->prm->rfc) and $this->prm->rfc != '') {
            $rfc = 'and rfc_id like "%' . $this->prm->rfc . '%" ';
        }

        if (isset($this->prm->nombre) and $this->prm->nombre != '') {
            //$this->prm->nombre = str_replace(['Ñ','ñ'], 'n', str_replace(['Ñ','ñ'], 'n', $this->prm->nombre));
            $this->prm->nombre = strtr($this->prm->nombre, [
                'Á' => 'A','É' => 'E','Í' => 'I','Ó' => 'O','Ú' => 'U',
                'á' => 'a','é' => 'e','í' => 'i','ó' => 'o','ú' => 'u',
                'Ñ' => 'N','ñ' => 'n',
            ]);
            $np = 'AND CAST(rfc_nombre AS CHAR CHARACTER SET utf8mb4) LIKE CAST("%' . $this->prm->nombre . '%" AS CHAR CHARACTER SET utf8mb4) ';
            $nombre = 'AND CAST(nombre AS CHAR CHARACTER SET utf8mb4) LIKE CAST("%' . $this->prm->nombre . '%" AS CHAR CHARACTER SET utf8mb4) ';
        }

        if (isset($this->prm->factura) and $this->prm->factura != '') {
            $factura = 'and referencia = "' . $this->prm->factura . '" ';
            $facturanp = 'and ncp_referencia = "' . $this->prm->factura . '" ';
            $facturanc = 'and ncc_referencia = "' . $this->prm->factura . '" ';
        }
        if (isset($this->prm->numeroClc) and $this->prm->numeroClc != '') {
            if (!is_numeric($this->prm->numeroClc)) {
                $this->mixScript('
                    notify("Número de CLC erróneo","warning");
                ');
                return false;
            }
            $clc = 'and clc_id=' . $this->prm->numeroClc . ' ';
        }

        $this->conectar();
        $sql = '
            SELECT gpo_id,ur_id,afe_id,clc_id,bdo_id,clave,clave_nombre,tipo_Movimiento,tipo_numcontrol,numcontrol,concepto,rfc_id,nombre, referencia,fechaReferencia,perBruto,iva_id,percepcion_iva,percepciones,retenciones,total,
            if(clc_tipocereCo=1  ,"CERE",if( clc_tipocereCo=2, "CERECO","SIN CERE(CO)"))cereco,clc_cereCo,programatico
            FROM scp_Clc
            LEFT JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
            LEFT JOIN(
            SELECT gpo_id,ur_id,afe_id,clc_id,ncp_id numcontrol,CONCAT(pre_claveCompleta) clave,pe_nombre clave_nombre,
                ncp_referencia referencia,ncp_fechaReferencia fechaReferencia,ncp_percepcionBruto perBruto,ncp_percepcionIva percepcion_iva,
                ncp_percepcionNeto percepciones, 0 retenciones,"+" tipo_Movimiento,rfc_id,CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS nombre,
                "P" tipo_numcontrol,iva_id AS iva_id,ncp_concepto AS concepto,rfc_nombre,ABS(sum(ncp_percepcionNeto) - sum(0)) as total,
                CONCAT( gpo_id,ur_id, "-",ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,bdo_id
            FROM scp_Presupuestal detalle
                INNER JOIN presupuesto USING(gpo_id,ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
                INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING(rfc_id)
                INNER JOIN cat_Proveedor USING(rfc_id)
                ' . $wf . ' ' . $rfc . '  and clc_id!=0
            GROUP BY gpo_id,ur_id,afe_id,clc_id,ncp_id,ncp_referencia
            UNION
            SELECT gpo_id,ur_id,afe_id,clc_id,ncc_id numcontrol, cta_cuenta clave, cta_nombre clave_nombre,
                ncc_referencia referencia,ncc_fechaReferencia fechaReferencia,0 perBruto, 0 percepcion_iva,
                ncc_percepcion percepciones,ncc_retencion retenciones,
                ncc_tipoMovimiento tipo_Movimiento,rfc_id,CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS nombre,
                "C" tipo_numcontrol,0 AS iva_id,ncc_concepto AS concepto,rfc_nombre,ABS(sum(ncc_percepcion) - sum(ncc_retencion)) as total,
                "" programatico,bdo_id
            FROM scp_Contable detalle
                INNER JOIN cg_PlanCuentas USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
                INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING(rfc_id)
                INNER JOIN cat_Proveedor USING(rfc_id)
            ' . $wf . ' ' . $rfc . '  and clc_id!=0
            GROUP BY gpo_id,ur_id,afe_id,clc_id,ncc_id,ncc_referencia
            )nc using(gpo_id,ur_id,clc_id)
            ' . $wh . ' ' . $rfc . ' ' . $nombre . ' ' . $factura . ' ' . $clc . ' and clc_id!=0 and ecl_id!=20 and ecl_id!=21
            GROUP BY gpo_id, ur_id, afe_id, clc_id, numcontrol, rfc_id, referencia,nc.bdo_id,nc.clave,nc.clave_nombre,nc.tipo_Movimiento,nc.tipo_numcontrol,nc.concepto,nc.nombre,
            nc.fechaReferencia,nc.perBruto,nc.iva_id,nc.percepcion_iva,nc.percepciones,nc.retenciones,nc.total,nc.programatico;
        ';
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-prov';
        $nav->nf = 150;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }
}
if (isset($_SESSION[_S]['prm']['S']['5']['14']['1'])) {
    // PERMISOS DEL USUARIO QUE PUEDE VER ESTE CONTROLADOR
    new ControladorConsultaProveedores();
} else {
    WebResponse::sinPermisos();
}
