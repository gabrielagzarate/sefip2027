<?php

session_start();
set_time_limit(0);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

class ControladorConsultaNumeroControl extends WebResponse
{
    public function __construct()
    {
        parent::__construct();

        switch ($this->metodo) {
            case 'consultar':
                $this->consultaNcc();
                break;
            default:
                $this->pantallaInicial();
                break;
        }
        $this->returnJson();
    }

    private function pantallaInicial()
    {
        $boton = new stdClass();
        $boton->title = 'descargar archivo excel';
        $boton->icon = 'fa fa-file-excel m-2';
        $boton->id = 'exportar';
        $boton->value = ' Excel';
        $boton->class = 'btn-outline-success';
        $boton->click = '
            var form = $("<form>")
                .attr("action", "rep/excel/scp/consultas/ExportaConsultaNumControl.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "gpo_id").val( $("#gpo_id").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( $("#ur_id").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "tem_carga").val( $("#tem_carga").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "cg_primer").val( $("#cg_primer").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "cg_cuarto").val( $("#cg_cuarto").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "registro").val( $("#registro").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "fechaIni").val( $("#fechaIni").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "fechaFin").val( $("#fechaFin").val() ));
            $("body").append(form);
            form.submit();
            form.remove();
        ';
        $this->agregaTools($this->ponBoton($boton));
        $boton1 = new stdClass();
        $boton1->title = 'buscar';
        $boton1->icon = 'search';
        $boton1->id = 'buscar';
        $boton1->class = 'btn-outline-secondary';
        $boton1->click = $this->wrLlamaLlenaTabla('numerosControl');

        $carga = new stdClass();
        $carga->id = 'tem_carga';
        $carga->placeholder = 'XXX-XXXXXX-XXX-XXXXXX';
        $carga->label = 'Identificador de Carga:';
        $carga->enDiv = true;

        $gpo = new stdClass();
        $gpo->id = 'gpo_id';
        $gpo->maxlength = 1;
        $gpo->style = 'width:20px; float: left; padding: 6px 4px !IMPORTANT;';
        $gpo->class = 'form-control';
        $gpo->keyup = 'if($(this).val().length == 1){
            $("#ur_id").focus();
        }';

        $ur = new stdClass();
        $ur->id = 'ur_id';
        $ur->maxlength = 2;
        $ur->class = 'form-control';
        $ur->style = 'width:27px; float: left;  padding: 6px 4px !IMPORTANT;';
        $ur->keypress = 'if($(this).val().length == 0 && event.which == 8)$("#gpo_id").val("").focus();';

        $cuentaPrimer = new stdClass();
        $cuentaPrimer->id = 'cg_primer';
        $cuentaPrimer->maxlength = '5';
        $cuentaPrimer->label = 'primer nivel:';
        $cuentaPrimer->enDiv = true;
        $cuentaCuarto = new stdClass();
        $cuentaCuarto->id = 'cg_cuarto';
        $cuentaCuarto->maxlength = '3';
        $cuentaCuarto->label = 'cuarto nivel:';
        $cuentaCuarto->enDiv = true;
        $cuentaQuinto = new stdClass();
        $cuentaQuinto->maxlength = '4';
        $cuentaQuinto->id = 'registro';
        $cuentaQuinto->label = 'nivel registro:';
        $cuentaQuinto->enDiv = true;

        $fechaIni = new stdClass();
        $fechaIni->id = 'fechaIni';
        $fechaIni->label = 'Fecha Inicio:';
        $fechaIni->enDiv = true;
        $fechaIni->formato = 'fecha';

        $fechaFin = new stdClass();
        $fechaFin->id = 'fechaFin';
        $fechaFin->label = 'Fecha Fin:';
        $fechaFin->enDiv = true;
        $fechaFin->formato = 'fecha';

        $tabla = new stdClass();
        $tabla->id = 'numerosControl';
        $tabla->titulo = 'Numeros de Control';
        $tr = [];
        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. Control';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Cuenta Contable';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Cuenta (cuarto nivel)';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Percepciones';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Retenciones';
        array_push($tr, $th);
        $tabla->encabezado = [$tr];

        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ncc_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cuenta';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cgc_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ncc_percepcion';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ncc_retencion';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'consultar';
        $tabla->comunica->gpo_id = '{$("#gpo_id").val()}';
        $tabla->comunica->ur_id = '{$("#ur_id").val()}';
        $tabla->comunica->tem_carga = '{$("#tem_carga").val()}';
        $tabla->comunica->cg_primer = '{$("#cg_primer").val()}';
        $tabla->comunica->cg_cuarto = '{$("#cg_cuarto").val()}';
        $tabla->comunica->registro = '{$("#registro").val()}';
        $tabla->comunica->fechaIni = '{$("#fechaIni").val()}';
        $tabla->comunica->fechaFin = '{$("#fechaFin").val()}';
        $tabla->numFilas = 100;

        $this->mixHtml('
            <div class="mb-4 row warning" style ="background-color: rgba(255, 234, 168, 0.3); margin-top: -20px;">
                <fieldset class="col-md-2"><br><br>' . $this->ponInput($carga) . ' </fieldset>
                <fieldset class="col-md-1"><br><br>
                    <label>UR:</label>
                    <div>
                        ' . $this->ponInput($gpo) . '
                        ' . $this->ponInput($ur) . '
                    </div>
                </fieldset>
                <fieldset class="col-md-4">
                    <table class="table" style="background:none !important;">
                        <thead><tr><th colspan="2" style="background:none !important;">Periodo</th></tr></thead>
                        <tbody><tr><td style="background:none !important;">' . $this->ponInput($fechaIni) . '</td><td style="background:none !important;">' . $this->ponInput($fechaFin) . '</td></tr></tbody>
                    </table>
                </fieldset>
                <fieldset class="col-md-4">
                    <table class="table" style="background:none !important;">
                        <thead><tr><th colspan="3" style="background:none !important;">Cuenta Contable</th></tr></thead>
                        <tbody><tr><td width="33%" style="background:none !important;">' . $this->ponInput($cuentaPrimer) . '</td><td width="33%" style="background:none !important;">' . $this->ponInput($cuentaCuarto) . '</td><td width="34%" style="background:none !important;">' . $this->ponInput($cuentaQuinto) . '</td></tr></tbody>
                    </table>
                </fieldset>
                <fieldset class="col-md-1">
                <br><br>
                ' . $this->ponBoton($boton1) . '
                </fieldset>
            </div>
            ' . $this->wrPonTabla($tabla) . '
        ');

        $this->mixScript('
            document.getElementById("fechaIni").removeAttribute("readonly");
            document.getElementById("fechaFin").removeAttribute("readonly");
            $("#buscar").removeClass("btn-default");
            $("#exportar").removeClass("btn-default");
        ');
    }

    private function consultaNcc()
    {
        $filtro = '';
        if ($_SESSION[_S]['nvj_id'] > 2 || $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 118) {
            $filtro .= (!empty($this->prm->gpo_id)) ? ' AND gpo_id = "' . $this->prm->gpo_id . '"' : '';
            $filtro .= (!empty($this->prm->ur_id)) ? ' AND ur_id = "' . $this->prm->ur_id . '"' : '';
        } else {
            $filtro .= ' AND gpo_id = "' . $_SESSION[_S]['gpo_id'] . '"';
            $filtro .= ' AND ur_id = "' . $_SESSION[_S]['ur_id'] . '"';
        }

        $filtro .= (!empty($this->prm->tem_carga)) ? ' AND c.tem_carga = "' . $this->prm->tem_carga . '"' : '';
        if (!empty($this->prm->cg_primer)) {
            $campo = ['ger_id', 'cgp_id', 'rub_id', 'cgcu_id', 'cgsc_id'];
            foreach (str_split($this->prm->cg_primer) as $pos => $valor) {
                $filtro .= ' AND ' . $campo[$pos] . ' = ' . $valor;
            }
        }

        $filtro .= (!empty($this->prm->cg_cuarto)) ? ' AND cgc_id = "' . str_pad($this->prm->cg_cuarto, 3, '0', STR_PAD_LEFT) . '"' : '';
        $filtro .= (!empty($this->prm->registro)) ? ' AND cta_id = "' . str_pad($this->prm->registro, 4, '0', STR_PAD_LEFT) . '"' : '';
        $fecha = $this->validaDate($this->prm->fechaIni);
        $filtro .= (!empty($this->prm->fechaIni)) ? ' AND afe_fechaInicio = ' . $fecha : '';
        $fecha = $this->validaDate($this->prm->fechaFin);
        $filtro .= (!empty($this->prm->fechaFin)) ? ' AND afe_fechaFin = ' . $fecha : '';

        $this->setSql('
            SELECT CONCAT(gpo_id,ur_id) ur_clave,ncc_id,clc_id,
            CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",cta_gpo_id,cta_ur_id,".",cta_ue_id,".",cgc_id,".",cta_id) cuenta,cgc_nombre,
            afe_fechaInicio,afe_fechaFin,ncc_percepcion,ncc_retencion
            FROM scp_Contable
            JOIN scp_Afectacion a USING(gpo_id,ur_id,afe_id,clc_id)
            JOIN scp_Clc c USING(gpo_id,ur_id,clc_id)
            JOIN cg_CuentaCuarto USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id)
            INNER JOIN cat_UrAdministracion USING ( gpo_id,ur_id )
            WHERE clc_id != 0 AND ( ecl_id BETWEEN 3 AND 18 ) ' . $filtro . '
            ORDER BY cuenta
        ');

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'numerosControl';
        $nav->nfDefault = 100;
        $this->wrLlenaTabla($nav);
    }

    private function validaDate($fecha)
    {
        if (count(explode('-', $fecha)) == 3) {
            list($año, $mes, $dia) = explode('-', $fecha);
            if (checkdate($mes, $dia, $año)) {
                return '"' . $año . '-' . $mes . '-' . $dia . '"';
            } else {
                return '"' . $dia . '-' . $mes . '-' . $año . '"';
            }
        } elseif (count(explode('/', $fecha)) == 3) {
            list($año, $mes, $dia) = explode('/', $fecha);
            if (checkdate($mes, $dia, $año)) {
                return '"' . $año . '-' . $mes . '-' . $dia . '"';
            } else {
                return '"' . $dia . '-' . $mes . '-' . $año . '"';
            }
        } else {
            return 'NULL';
        }
    }
} //fin clase
if (isset($_SESSION[_S]['prm']['S']['5']['10']['1'])) {
    new ControladorConsultaNumeroControl();
} else {
    WebResponse::sinPermisos();
}
