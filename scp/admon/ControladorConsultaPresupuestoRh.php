<?php

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
class ControladorConsultaPresupuestoRh extends WebResponse
{
    public $gpo_id;
    public $ur_id;
    public function __construct()
    {
        //Constructor
        parent::__construct();
        $this->gpo_id = ($_SESSION[_S]['nvj_id'] < 3) ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = ($_SESSION[_S]['nvj_id'] < 3) ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        switch ($this->metodo) {
            case 'cargarTabla':
                $this->cargarTabla();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $boton = new stdClass();
        $boton->id = 'exportarTodas';
        $boton->value = 'Excel';
        $boton->color = 'verde';
        $boton->icon = 'fa-solid fa-file-excel me-1';
        $boton->click = '
            var form = $("<form>").attr("action", "rep/excel/scp/admon/ControladorExportaCalendarioRHXLSGlobal.php").attr("method", "post").attr("target", "_blank");
            $("body").append(form);
            form.submit();
            form.remove();
        ';
        $this->agregaTools(' ' . $this->ponBoton($boton));
        $tabla = new stdClass();
        $tabla->id = 'presupuestoUrs';
        $tabla->titulo = 'Consulta Presupuesto';
        $tabla->tools = '<div id="botonNew" class="left"> </div>';
        $tr = [];
        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th->width = '5px';
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Exportar';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Vigente';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Devengado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Ejercido';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% Avance Financiero';
        array_push($tr, $th);
        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'cargarTabla';
        $tabla->comunica->todas = '{ $("#selectAll").children("span").is(".glyphicon-check") }';
        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa-solid fa-file-excel';
        $td->color = 'verde';
        $td->class = 'exportar';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'pre_vigente';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'pre_devengado';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'pre_ejercido';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'pocentaje';
        $td->formato = 'pocertanje';
        array_push($tabla->cuerpo, $td);
        $tabla->numFilas = 20;
        $this->mixHtml('' . $this->wrPonTabla($tabla) . '');
        $this->mixScript('$("#exportarTodas").click(function(){});');
        $this->cargarTabla();
    }
    private function cargarTabla()
    {
        $this->setSql('
            SELECT
                `gpo_id`,`ur_id`,`ur_nombre`,`ur_clave`,
                SUM(`pre_vigente`) `pre_vigente`,
                SUM(`pre_comprometido`) `pre_comprometido`,
                SUM(`pre_ejercido`) `pre_ejercido`,
                SUM(`pre_devengado`) `pre_devengado`,
                ( SUM(`pre_devengado`) / SUM(`pre_vigente`) )* 100 pocentaje
            FROM
                `presupuesto`
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN cat_PartidaEspecifica USING (pg_id,pe_id)
                JOIN `cat_UnidadResponsable` USING(`gpo_id`,`ur_id`)
            WHERE `crh_id` = ? AND `tcp_id` != ? AND `clg_id` = ?
            GROUP BY `gpo_id`,`ur_id`;
        ');
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'presupuestoUrs';
        $nav->nfDefault = 20;
        $this->setParams([1, 'P', 1]);
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".exportar").click(function(){
                var form = $("<form>").attr("action", "rep/excel/scp/admon/ControladorExportaCalendarioRHXLSGlobal.php").attr("method", "post").attr("target", "_blank");
                form.append( $("<input/>").attr("id","gpo_id").attr("name","gpo_id").val($(this).attr("gpo_id")) );
                form.append( $("<input/>").attr("id","ur_id").attr("name","ur_id").val($(this).attr("ur_id")) );
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }
}
//-------------------------------//
new ControladorConsultaPresupuestoRh();
//---------------------------------//
