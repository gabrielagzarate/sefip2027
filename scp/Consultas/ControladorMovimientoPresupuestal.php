<?php

session_start();
date_default_timezone_set('America/Mexico_City');

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

class ControladorMovimientoPresupuestal extends WebResponse
{
    public $gpo_id;
    public $ur_id;

    public function __construct()
    {
        //Constructor
        parent::__construct();

        $this->gpo_id = '';
        $this->ur_id = '';
        $this->pantalla = 'scp/Consultas/ControladorMovimientoPresupuestal.php';
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
            $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        } else {
            $this->ur_id = $_SESSION[_S]['ur_id'];
            $this->gpo_id = $_SESSION[_S]['gpo_id'];
        }

        switch ($this->metodo) {
            case 'main':
                $this->main();
                break;
            case 'DetalleClavePresupuestal':
                $this->DetalleClavePresupuestal();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        //101- 001-10- 00- 00- 001-412- 005- EAAFB0114
        $consultar = new stdClass();
        $consultar->id = 'consultar';
        $consultar->name = 'consultar';
        $consultar->color = 'gris';
        $consultar->value = 'Consultar';
        $consultar->icon = 'search me-2';
        $consultar->class = 'me-1';

        $clean = new stdClass();
        $clean->id = 'clean';
        $clean->value = 'Limpiar';
        $clean->color = 'amarillo';
        $clean->icon = 'fa fa-trash me-2';

        $ue = new stdClass();
        $ue->id = 'ue_id';
        $ue->name = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->class = 'limpiar';
        $ue->placeholder = 'UE 001';
        $ue->required = true;
        $ue->enDiv = true;

        $programa = new stdClass();
        $programa->id = 'prg_id';
        $programa->name = 'programa';
        $programa->maxlength = 3;
        $programa->label = 'Programa';
        $programa->class = 'limpiar';
        $programa->placeholder = 'PROG. 000';
        $programa->required = true;
        $programa->enDiv = true;

        $subprograma = new stdClass();
        $subprograma->id = 'spr_id';
        $subprograma->name = 'subprograma';
        $subprograma->maxlength = 2;
        $subprograma->label = 'SubPrograma';
        $subprograma->class = 'limpiar';
        $subprograma->placeholder = 'SUBPROG 00';
        $subprograma->required = true;
        $subprograma->enDiv = true;

        $py = new stdClass();
        $py->id = 'py_id';
        $py->name = 'py';
        $py->maxlength = 3;
        $py->label = 'Proyecto';
        $py->class = 'limpiar';
        $py->placeholder = 'PY 000';
        $py->required = true;
        $py->enDiv = true;

        $oa = new stdClass();
        $oa->id = 'oa_id';
        $oa->name = 'oa';
        $oa->maxlength = 3;
        $oa->label = 'Obra Actividad';
        $oa->class = 'limpiar';
        $oa->placeholder = 'ACTIVIDAD 000';
        $oa->required = true;
        $oa->enDiv = true;

        $pg = new stdClass();
        $pg->id = 'pg_id';
        $pg->name = 'pg';
        $pg->maxlength = 3;
        $pg->label = 'Partida Generica';
        $pg->class = 'limpiar';
        $pg->placeholder = 'PG 000';
        $pg->required = true;
        $pg->enDiv = true;

        $pe = new stdClass();
        $pe->id = 'pe_id';
        $pe->name = 'pg';
        $pe->maxlength = 3;
        $pe->label = 'Partida Especifica';
        $pe->class = 'limpiar';
        $pe->placeholder = 'PE 000';
        $pe->required = true;
        $pe->enDiv = true;

        $fte = new stdClass();
        $fte->id = 'fte_id';
        $fte->name = 'fte';
        $fte->maxlength = 1;
        $fte->label = 'Fuente';
        $fte->class = 'limpiar';
        $fte->placeholder = 'FUENTE X';
        $fte->required = true;
        $fte->enDiv = true;

        $sfte = new stdClass();
        $sfte->id = 'sft_id';
        $sfte->name = 'sfte';
        $sfte->maxlength = 1;
        $sfte->label = 'SubFuente';
        $sfte->class = 'limpiar';
        $sfte->placeholder = 'SUBFUENTE X';
        $sfte->required = true;
        $sfte->enDiv = true;

        $ori = new stdClass();
        $ori->id = 'ori_id';
        $ori->name = 'ori';
        $ori->maxlength = 1;
        $ori->label = 'Origen Recurso';
        $ori->class = 'limpiar';
        $ori->placeholder = 'ORIGEN X';
        $ori->required = true;
        $ori->enDiv = true;

        $sori = new stdClass();
        $sori->id = 'sor_id';
        $sori->name = 'sori';
        $sori->maxlength = 1;
        $sori->label = 'SubOrigen Recurso';
        $sori->class = 'limpiar';
        $sori->placeholder = 'SUBORIGEN X';
        $sori->required = true;
        $sori->enDiv = true;

        $cso = new stdClass();
        $cso->id = 'cso_id';
        $cso->name = 'cso';
        $cso->maxlength = 1;
        $cso->label = 'Clasificación Suborigen';
        $cso->class = 'limpiar';
        $cso->placeholder = 'CLASIFICACIÓN X';
        $cso->required = true;
        $cso->enDiv = true;

        $referencia = new stdClass();
        $referencia->id = 'cfi_id';
        $referencia->name = 'referencia';
        $referencia->maxlength = 2;
        $referencia->label = 'Referencia';
        $referencia->class = 'limpiar';
        $referencia->placeholder = 'REFERENCIA 00';
        $referencia->required = true;
        $referencia->enDiv = true;

        $ejercicio = new stdClass();
        $ejercicio->id = 'cfi_anio';
        $ejercicio->name = 'ejercicio';
        $ejercicio->maxlength = 2;
        $ejercicio->label = 'Ejercicio';
        $ejercicio->class = 'limpiar';
        $ejercicio->placeholder = 'EJERCICIO 00';
        $ejercicio->required = true;
        $ejercicio->enDiv = true;

        $html = '';
        $html .= '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
                <legend align="center">Busqueda por:</legend>
            <div class="row">
                <fieldset class="col-md-2">
                    ' . $this->ponInput($ue) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($programa) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($subprograma) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($py) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($oa) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($pg) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-2">
                    ' . $this->ponInput($pe) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($fte) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($sfte) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($ori) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($sori) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($cso) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-2">
                    ' . $this->ponInput($referencia) . '
                </fieldset>
                <fieldset class="col-md-2">
                    ' . $this->ponInput($ejercicio) . '
                </fieldset>

                <fieldset class="col-md-3">
                    <label>Tipo de Compra</label>
                    <select class="clean form-control" id="tipo"  name="tipo"><option value="0">CD - CONSOLIDAS Y DIRECTAS</option><option value="C">C - COMPRAS CONSOLIDAS</option><option value="D">D - COMPRAS DIRECTAS</option></select>
                </fieldset>
            </div>
        ';
        $this->agregaTools('' . $this->ponBoton($consultar) . '   ' . $this->ponBoton($clean) . ' ');

        $this->mixHtml($html);
        $this->mixScript('
            $("#consultar").click(function(){
                var obj = {"metodo":"DetalleClavePresupuestal"};
                obj.ue_id = $("#ue_id").val();
                obj.prg_id = $("#prg_id").val();
                obj.spr_id = $("#spr_id").val();
                obj.py_id = $("#py_id").val();
                obj.oa_id = $("#oa_id").val();
                obj.pg_id = $("#pg_id").val();
                obj.pe_id = $("#pe_id").val();
                obj.fte_id = $("#fte_id").val();
                obj.sft_id = $("#sft_id").val();
                obj.ori_id = $("#ori_id").val();
                obj.sor_id = $("#sor_id").val();
                obj.cso_id = $("#cso_id").val();
                obj.cfi_id = $("#cfi_id").val();
                obj.cfi_anio = $("#cfi_anio").val();
                obj.tipo = $("#tipo").val();
                console.log(obj);
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });

            $("#clean").click(function(){
            $(".limpiar").val("");
            });
        ');
    }

    private function DetalleClavePresupuestal()
    {
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? $this->prm->ue_id : '';
        $prog = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? $this->prm->prg_id : '';
        $sprog = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? $this->prm->spr_id : '';
        $py = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';
        $pg = (isset($this->prm->pg_id) && $this->prm->pg_id != '') ? $this->prm->pg_id : '';
        $pe = (isset($this->prm->pe_id) && $this->prm->pe_id != '') ? $this->prm->pe_id : '';
        $fte = (isset($this->prm->fte_id) && $this->prm->fte_id != '') ? $this->prm->fte_id : '';
        $sfte = (isset($this->prm->sft_id) && $this->prm->sft_id != '') ? $this->prm->sft_id : '';
        $ori = (isset($this->prm->ori_id) && $this->prm->ori_id != '') ? $this->prm->ori_id : '';
        $sori = (isset($this->prm->sor_id) && $this->prm->sor_id != '') ? $this->prm->sor_id : '';
        $cso = (isset($this->prm->cso_id) && $this->prm->cso_id != '') ? $this->prm->cso_id : '';
        $cfi = (isset($this->prm->cfi_id) && $this->prm->cfi_id != '') ? $this->prm->cfi_id : '';
        $cfia = (isset($this->prm->cfi_anio) && $this->prm->cfi_anio != '') ? $this->prm->cfi_anio : '';

        if (isset($this->prm->tipo)) {
            if ($this->prm->tipo === '0') {
                $wf = 'and tcp_id in ("C","D")';
                $wf2 = 'and a.tcp_id in ("C","D")';
            } else {
                $wf = ' and tcp_id= "' . $this->prm->tipo . '" ';
                $wf2 = ' and a.tcp_id= "' . $this->prm->tipo . '" ';
            }
        } else {
            $wf = 'and tcp_id in ("C","D")';
            $wf2 = 'and a.tcp_id in ("C","D")';
        }

        $dethtml = '';
        $guion = '-';

        $clave = $this->gpo_id . $this->ur_id . $ue . $guion . $prog . $sprog . $py . $oa . $guion . $pg . $pe . mb_strtoupper($fte) . mb_strtoupper($sfte) . mb_strtoupper($ori) . mb_strtoupper($sori) . mb_strtoupper($cso) . $cfi . $cfia;

        $this->conectar();
        $sql = '
            SELECT pg_id,pe_id,pe_nombre
            FROM cat_PartidaEspecifica
            WHERE pg_id=? and pe_id=? 
        ';
        $this->setSql($sql);
        $this->consultar([$pg, $pe]);
        $this->desconectar();

        if ($row = $this->extraer()) {
            $dethtml = '
                <table class="table table-striped table-bordered">' .
                    '<tr>
                        <td width="20%" align="right"><b>ClAVE PRESUPUESTAL:</b></td>
                        <td><b>' . $clave . '</b></td>
                    </tr>' .
                    '<tr>
                        <td  align="right"><b>PARTIDA:</b></td>
                        <td><b>' . $row['pg_id'] . $row['pe_id'] . '-' . $row['pe_nombre'] . '</b></td>
                    </tr>' .
                '</table>';
        }

        $this->conectar();
        $sql = '
            SELECT pre_aprobado,pre_comprometido,pre_devengado,pre_ejercido,pre_vigente
            FROM presupuesto
            JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            WHERE pre_claveCompleta="' . $clave . '"  ' . $wf . ';
        ';
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        if ($row = $this->extraer()) {
            $dethtml .= '
                <table class="table table-striped table-bordered">' .
                    '<tr><th colspan="4" align="center"><b>RESUMEN DE PRESUPUESTO</b></th></tr>' .
                    '<tr>
                        <th width="10%" align="center"><b>AUTORIZADO</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_aprobado'], 2) . '</td>
                        <th align="left" width="10%"><b>MODIFICADO</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_vigente'], 2) . '</td>
                    </tr>' .
                    '<tr>
                        <th width="10%" align="center"><b>COMPROMETIDO</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_comprometido'], 2) . '</td>
                        <th align="left" width="10%"><b>POR COMPROMETER</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_vigente'] - $row['pre_comprometido'], 2) . '</td>
                    </tr>' .
                    '<tr>
                        <th width="10%" align="center"><b>DEVENGADO</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_devengado'], 2) . '</td>
                        <th align="left" width="10%"><b>POR DEVENGAR</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_vigente'] - $row['pre_devengado'], 2) . '</td>
                    </tr>' .
                    '<tr>
                        <th width="10%" align="center"><b>EJERCIDO</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_ejercido'], 2) . '</td>
                        <th align="left" width="10%"><b>POR EJERCER</b></th>
                        <td align="right" width="15%">' . number_format($row['pre_vigente'] - $row['pre_ejercido'], 2) . '</td>
                    </tr>' .
                '</table>';
        }

        /*CALENDARIO PRESUPUESTAL*/

        $dethtml .= '
            <table class="table table-striped table-bordered">' .
                '<tr>
                <th colspan="6"><b>CALENDARIO PRESUPUESTAL</b></th></tr>' .
                '<tr><th>MESES</th><th><b>MODIFICADO</b></th>
                <th><b>COMPROMETIDO</b></th>
                <th><b>DEVENGADO</b></th>
                <th><b>EJERCIDO</b></th>
                <th><b>TRANSFERIDO</b></th>
                </tr>
        ';
        $this->conectar();
        $sql = '
            SELECT vig_ene, vig_feb, vig_mar, vig_abr, vig_may, vig_jun, vig_jul, vig_ago, vig_sep, vig_oct, vig_nov, vig_dic,
            com_ene, com_feb, com_mar, com_abr, com_may, com_jun, com_jul, com_ago,com_sep, com_oct, com_nov, com_dic,
            dev_ene, dev_feb, dev_mar, dev_abr, dev_may, dev_jun, dev_jul, dev_ago,dev_sep, dev_oct, dev_nov, dev_dic,
            eje_ene, eje_feb, eje_mar, eje_abr, eje_may, eje_jun, eje_jul, eje_ago,eje_sep, eje_oct, eje_nov, eje_dic,
            min_ene, min_feb, min_mar, min_abr, min_may, min_jun, min_jul, min_ago,min_sep, min_oct, min_nov, min_dic
            FROM presupuesto
            JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            JOIN presupuestoMinistrado AS min USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            where pre_claveCompleta="' . $clave . '" ' . $wf . ';';

        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        while ($row = $this->extraer()) {
            $dethtml .= '<tr><td align="left" >ENERO</td><td align="right" >' . number_format($row['vig_ene'], 2) . '</td><td align="right" >' . number_format($row['com_ene'], 2) . '</td><td align="right" >' . number_format($row['dev_ene'], 2) . '</td><td align="right" >' . number_format($row['eje_ene'], 2) . '</td><td align="right" >' . number_format($row['min_ene'], 2) . '</td></tr>';
            $dethtml .= '<tr><td  align="left">FEBRERO</td><td align="right" >' . number_format($row['vig_feb'], 2) . '</td><td align="right" >' . number_format($row['com_feb'], 2) . '</td><td align="right" >' . number_format($row['dev_feb'], 2) . '</td><td align="right" >' . number_format($row['eje_feb'], 2) . '</td><td align="right" >' . number_format($row['min_feb'], 2) . '</td></tr>';
            $dethtml .= '<tr><td  align="left">MARZO</td><td align="right" >' . number_format($row['vig_mar'], 2) . '</td><td align="right" >' . number_format($row['com_mar'], 2) . '</td><td align="right" >' . number_format($row['dev_mar'], 2) . '</td><td align="right" >' . number_format($row['eje_mar'], 2) . '</td><td align="right" >' . number_format($row['min_mar'], 2) . '</td></tr>';
            $dethtml .= '<tr><td  align="left">ABRIL</td><td align="right" >' . number_format($row['vig_abr'], 2) . '</td><td align="right" >' . number_format($row['com_abr'], 2) . '</td><td align="right" >' . number_format($row['dev_abr'], 2) . '</td><td align="right" >' . number_format($row['eje_abr'], 2) . '</td><td align="right" >' . number_format($row['min_abr'], 2) . '</td></tr>';
            $dethtml .= '<tr><td  align="left">MAYO</td><td align="right" >' . number_format($row['vig_may'], 2) . '</td><td align="right" >' . number_format($row['com_may'], 2) . '</td><td align="right" >' . number_format($row['dev_may'], 2) . '</td><td align="right" >' . number_format($row['eje_may'], 2) . '</td><td align="right" >' . number_format($row['min_may'], 2) . '</td></tr>';
            $dethtml .= '<tr><td  align="left">JUNIO</td><td align="right" >' . number_format($row['vig_jun'], 2) . '</td><td align="right" >' . number_format($row['com_jun'], 2) . '</td><td align="right" >' . number_format($row['dev_jun'], 2) . '</td><td align="right" >' . number_format($row['eje_jun'], 2) . '</td><td align="right" >' . number_format($row['min_jun'], 2) . '</td></tr>';
            $dethtml .= '<tr><td  align="left">JULIO</td><td align="right" >' . number_format($row['vig_jul'], 2) . '</td><td align="right" >' . number_format($row['com_jul'], 2) . '</td><td align="right" >' . number_format($row['dev_jul'], 2) . '</td><td align="right" >' . number_format($row['eje_jul'], 2) . '</td><td align="right" >' . number_format($row['min_jul'], 2) . '</td></tr>';
            $dethtml .= '<tr><td align="left">AGOSTO</td><td align="right" >' . number_format($row['vig_ago'], 2) . '</td><td align="right" >' . number_format($row['com_ago'], 2) . '</td><td align="right" >' . number_format($row['dev_ago'], 2) . '</td><td align="right" >' . number_format($row['eje_ago'], 2) . '</td><td align="right" >' . number_format($row['min_ago'], 2) . '</td></tr>';
            $dethtml .= '<tr><td align="left">SEPTIEMBRE</td><td align="right" >' . number_format($row['vig_sep'], 2) . '</td><td align="right" >' . number_format($row['com_sep'], 2) . '</td><td align="right" >' . number_format($row['dev_sep'], 2) . '</td><td align="right" >' . number_format($row['eje_sep'], 2) . '</td><td align="right" >' . number_format($row['min_sep'], 2) . '</td></tr>';
            $dethtml .= '<tr><td align="left">OCTUBRE</td><td align="right" >' . number_format($row['vig_oct'], 2) . '</td><td align="right" >' . number_format($row['com_oct'], 2) . '</td><td align="right" >' . number_format($row['dev_oct'], 2) . '</td><td align="right" >' . number_format($row['eje_oct'], 2) . '</td><td align="right" >' . number_format($row['min_oct'], 2) . '</td></tr>';
            $dethtml .= '<tr><td align="left">NOVIEMBRE</td><td align="right" >' . number_format($row['vig_nov'], 2) . '</td><td align="right" >' . number_format($row['com_nov'], 2) . '</td><td align="right" >' . number_format($row['dev_nov'], 2) . '</td><td align="right" >' . number_format($row['eje_nov'], 2) . '</td><td align="right" >' . number_format($row['min_nov'], 2) . '</td></tr>';
            $dethtml .= '<tr><td align="left">DICIEMBRE</td><td align="right" >' . number_format($row['vig_dic'], 2) . '</td><td align="right" >' . number_format($row['com_dic'], 2) . '</td><td align="right" >' . number_format($row['dev_dic'], 2) . '</td><td align="right" >' . number_format($row['eje_dic'], 2) . '</td><td align="right" >' . number_format($row['min_dic'], 2) . '</td></tr>';
        }

        $dethtml .= '</table>';
        /*FIN CALENDARIO PRESUPUESTAL*/
        /*MODIFICACIONES PRESUPUESTALES*/

        $dethtml .= '
            <table class="table table-striped table-bordered">' .
                '<tr>
                    <th colspan="7"><b>MODIFICACIONES PRESUPUESTALES</b></th>
                </tr>' .
                '<tr>
                    <th><b>ORIGEN</b></th>
                    <th><b>FOLIO</b></th>
                    <th><b>FECH. FOLIO</b></th>
                    <th width="40%"><b>CONCEPTO</b></th>
                    <th><b>TIPO MOV</b></th>
                    <th><b>TIPO</b></th>
                    <th><b>IMPORTE</b></th>
                </tr>';

        $this->conectar();
        $sql = '
            SELECT if(omo_id="C" AND tfm_id="B" AND tmd_id="D","DIRECCION DE RECURSOS MATERIALES",omo_nombre) AS omo_nombre,mod_id,DATE_FORMAT( mod_fechaFolio, "%d/%m/%Y")mod_fechaFolio,mod_concepto,mov_id,mov_nombre,tcp_id,dmo_importe
            from scp_MaestroModificacion
            join scp_DetalleModificacion using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
            join presupuesto using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN cat_OrigenModificacion using(omo_id)
            JOIN enc_OrigenOperacion using(omo_id,enc_oop)
            JOIN cat_TipoMovimiento using(mov_id)
            where pre_claveCompleta= "' . $clave . '" ' . $wf . '
        ';

        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        while ($row = $this->extraer()) {
            $dethtml .= '
                <tr>
                    <td align="left">' . $row['omo_nombre'] . '</td>
                    <td align="center">' . $row['mod_id'] . '</td>
                    <td align="center">' . $row['mod_fechaFolio'] . '</td>
                    <td align="left">' . ($row['mod_concepto']) . '</td>
                    <td align="left">' . $row['mov_nombre'] . '</td><td  align="center">' . $row['tcp_id'] . '</td>
                    <td align="right">' . number_format($row['dmo_importe'], 2) . '</td>
                </tr>
            ';
        }

        $dethtml .= '</table>';

        /*FIN MODIFICACIONES PRESUPUESTALES*/

        /*ORDENES DE TRANSFERENCIA BANCARIAS*/
        $dethtml .= '
            <table class="table table-striped table-bordered">' .
                '<tr>
                    <th colspan="4"><b>CERES</b></th>
                </tr>' .
                '<tr>
                    <th ><b>NO. DE CERE</b></th>
                    <th width="40%"><b>CONCEPTO</b></th>
                    <th ><b>FECHA</b></th><th><b>IMPORTE</b></th>
                </tr>
        ';

        $this->conectar();
        $sql = '
            SELECT sca.clc_cereCo,clc_concepto,sum(clc_percepcion) - sum(clc_retencion) as importe,ecl_id,DATE_FORMAT( clc_generacionCereCo, "%d/%m/%Y") as clc_generacionCereCo
            FROM presupuesto a
            JOIN scp_Presupuestal using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN scp_Clc sc using(gpo_id,ur_id,clc_id)
            LEFT JOIN scp_ClcAuxiliar sca using(gpo_id,ur_id,clc_id,ecl_id)
            WHERE pre_claveCompleta ="' . $clave . '" ' . $wf2 . ' and clc_id!=0 and clc_tipoCereCo=1
            GROUP BY clc_id,sca.clc_cereCo,sc.clc_concepto,sc.ecl_id,sca.clc_generacionCereCo;
        ';

        //313001-00 00 00 001-113004 E A A F B 0114

        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        while ($row = $this->extraer()) {
            $dethtml .= '
                <tr>
                    <td align="center" >' . $row['clc_cereCo'] . '</td>
                    <td align="left">' . $row['clc_concepto'] . '</td>
                    <td align="center" >' . $row['clc_generacionCereCo'] . '</td>
                    <td align="right" >' . number_format($row['importe'], 2) . '</td>
                </tr>
            ';
        }

        $dethtml .= '</table>';

        /*FIN ORDENES DE TRANSFERENCIA BANCARIAS*/
        /*AFECTACIONES PRESUPUESTALES*/

        $dethtml .= '
            <table class="table table-striped table-bordered">' .
            '<tr>
                <th colspan="11"><b>AFECTACIONES PRESUPUESTALES</b></th>
            </tr>' .
            '<tr>
                <th><b>CERE</b></th>
                <th><b>CLC</b></th>
                <th><b>NO. AFECT</b></th>
                <th><b>NO. CONTROL</b></th>
                <th width="30%"><b>CONCEPTO</b></th>
                <th><b>NO. FACTURA</b></th>
                <th><b>FECHA FACT</b></th>
                <th><b>RFC BENEF</b></th>
                <th><b>IMPORTE</b></th>
                <th><b>IVA</b></th>
                <th><b>IMPORTE NETO</b></th>
            </tr>
        ';

        $this->conectar();
        $sql = '
            SELECT clc_cereCo,clc_id,afe_id,ncp_id,rfc_id,ncp_concepto,ncp_referencia,DATE_FORMAT( ncp_fechaReferencia, "%d/%m/%Y") as ncp_fechaReferencia,ncp_percepcionBruto,ncp_percepcionIva,ncp_percepcionNeto
            from presupuesto a
            JOIN scp_Presupuestal  using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN scp_Clc using(gpo_id,ur_id,clc_id)
            LEFT JOIN scp_ClcAuxiliar using(gpo_id,ur_id,clc_id,ecl_id)
            WHERE pre_claveCompleta ="' . $clave . '" ' . $wf2 . ' and clc_id!=0 and clc_tipoCereCo=1
        ';

        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        $in = '';
        $wd = '';

        while ($row = $this->extraer()) {
            $dethtml .= '
                <tr>
                    <td align="center" width="5%">' . $row['clc_cereCo'] . '</td>
                    <td align="center" width="5%">' . $row['clc_id'] . '</td>
                    <td align="center"width="5%">' . $row['afe_id'] . '</td>
                    <td align="center"width="5%">' . $row['ncp_id'] . '</td>
                    <td align="left" width="5%">' . $row['ncp_concepto'] . '</td>
                    <td align="center" width="5%">' . $row['ncp_referencia'] . '</td>
                    <td align="center" width="5%">' . $row['ncp_fechaReferencia'] . '</td>
                    <td align="center" width="5%">' . $row['rfc_id'] . '</td>
                    <td align="right" >' . number_format($row['ncp_percepcionBruto'], 2) . '</td>
                    <td align="right" >' . number_format($row['ncp_percepcionIva'], 2) . '</td>
                    <td align="right" >' . number_format($row['ncp_percepcionNeto'], 2) . '</td>
                </tr>
            ';
            $in .= $row['clc_id'] . ',';
        }
        $in = trim($in, ',');
        $dethtml .= '</table>';

        //falta resolver la suma de las contables
        /*AFECTACIONES DE RECTIFICACIONES DE CLC´S*/
        /*FIN AFECTACIONES PRESUPUESTALES*/

        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->name = 'regresar';
        $regresar->color = 'gris';
        $regresar->value = 'Regresar';
        $regresar->icon = 'circle-left me-2';

        $dethtml .= '
            <table class="table table-striped table-bordered">' .
                '<tr>
                    <th colspan="11"><b>AFECTACIONES DE RECTIFICACIÓN</b></th>
                </tr>' .
                '<tr>
                    <th ><b>NO. AFECTACION</b></th><th><b>NO. CONTROL</b></th>
                    <th><b>CLC</b></th>
                    <th ><b>FOLIO RECT.</b></th>
                    <th ><b>REFERE</b></th><th ><b>FECHA DOCTO.</b></th>
                    <th width="40%"><b>PROVEEDOR</b></th><th ><b>RFC</b></th>
                    <th ><b>IMPORTE</b></th><th ><b>IVA</b></th>
                    <th ><b>IMPORTE NETO</b></th>
                </tr>
        ';

        if ($in != '') {

            $wd = 'and clc_id in (' . $in . ')';
            $this->conectar();
            $sql = '
                SELECT afe_id,ncp_id,clc_id,rct_id,ncp_referencia,DATE_FORMAT( ncp_fechaReferencia, "%d/%m/%Y") as ncp_fechaReferencia,rfc_id,
                CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS nombre,ncp_percepcionImporteBruto,ncp_importeIva,ncp_percepcionNeto
                FROM scp_RectificacionPresupuestal
                JOIN cat_Proveedor using(rfc_id)
                JOIN ' . BD_SINPRES . '.cat_Proveedores using(rfc_id)
                where ncp_clave = ?
            ';
            $parametros = [$clave];
            $this->setSql($sql);
            $this->consultar($parametros);
            $this->desconectar();
            while ($row = $this->extraer()) {
                $dethtml .= '
                    <tr>
                        <td align="center">' . $row['afe_id'] . '</td>
                        <td align="center" >' . $row['ncp_id'] . '</td>
                        <td align="center">' . $row['clc_id'] . '</td>
                        <td align="center" >' . $row['rct_id'] . '</td>
                        <td align="center">' . $row['ncp_referencia'] . '</td>
                        <td align="center" >' . $row['ncp_fechaReferencia'] . '</td>
                        <td align="center" >' . $row['nombre'] . '</td>
                        <td align="center">' . $row['rfc_id'] . '</td>
                        <td align="right" >' . number_format($row['ncp_percepcionImporteBruto'], 2) . '</td>
                        <td align="right" >' . number_format($row['ncp_importeIva'], 2) . '</td>
                        <td align="right" >' . number_format($row['ncp_percepcionNeto'], 2) . '</td>
                    </tr>
                ';
            }
            $dethtml .= '</table></br></br>';
        }

        $this->agregaTools($this->ponBoton($regresar));
        $this->mixHtml($dethtml);
        $this->mixScript('
            $("#regresar").click(function(){
                comunica( "' . $this->pantalla . '",{
                    metodo:"main"
                });
                return false;
            });
        ');
    }
}
if (isset($_SESSION[_S]['prm']['S']['5']['16']['1'])) {
    new ControladorMovimientoPresupuestal();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
