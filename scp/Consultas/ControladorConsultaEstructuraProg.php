<?php

session_start();

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorConsultaEstructuraProg extends WebResponse
{
    public function __construct()
    {
        //Constructor
        parent::__construct();
        switch ($this->metodo) {
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        //Agrega el boton a la barra de herramientas
        $this->agregaTools('');
        $this->mixScript(ClasificacionAdministrativa::onChangeUr(' comunica( "' . $this->pantalla . '" ); '));
        $this->conectar();
        $params = [];
        $sql = '
            SELECT
                gpo_id,ur_id,
                ur_nombre,prg_id,
                spr_id,py_id,oa_id,
                prg_nombre,spr_nombre,
                py_nombre,oa_nombre,"-" gui,
                sum(pre_aprobado) AS pre_aprobado,
                sum(pre_vigente) AS pre_vigente,
                sum(pre_comprometido) AS pre_comprometido,
                sum(pre_ejercido) AS pre_ejercido
            FROM enc_Programatico
                INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                INNER JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
                INNER JOIN cat_ObraActividad USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                LEFT JOIN (SELECT
                            epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id, pre_aprobado, pre_vigente, pre_comprometido, pre_ejercido
                        FROM presupuesto
                            INNER JOIN presupuestoAprobado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                            INNER JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                            INNER JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                            INNER JOIN presupuestoEjercido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                ) AS pre USING (epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            WHERE
            ';
        if ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') {
            $sql .= '(ur_rh = 1 OR (gpo_id in ( 1,5,6,7,8) and concat(gpo_id,ur_id) not in (105, 115, 116, 530, 531, 540,553 ))) ';
        } else {
            $sql .= 'gpo_id = ? AND ur_id = ? ';
            $params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']];
        }
        $sql .= '
            AND eoa_id IN (4,5)
            GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id,oa_id;';
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
        $this->mixHtml('
            <table class="table table table-bordered table-hover">
        ');
        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';
        $htm = '';
        $cambioClase = true;
        while ($epg = $this->extraer()) {
            if ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S' && $epg['gpo_id'] . $epg['ur_id'] != $ur) {
                $htm .= '<tr class="success">
                    <th class="" style="text-align:center; " colspan="1">UR:</th>
                    <th class="" style="text-align:center" colspan="1">' . $epg['gpo_id'] . $epg['ur_id'] . '</th>
                    <th style="text-align:left" colspan="10" colspan="2">' . $epg['ur_nombre'] . '</th>
                </tr>';
            }
            if ($epg['prg_id'] != $prg) {
                $htm .= '<tr>
                    <th class="warning" style="text-align:center" colspan="1">PR:</th>
                    <th class="warning" style="text-align:center" colspan="1">' . $epg['prg_id'] . '</th>
                    <th style="text-align:left" colspan="10">' . $epg['prg_nombre'] . '</th>
                </tr>';
            }
            if ($epg['prg_id'] != $prg || $epg['spr_id'] != $spr) {
                $htm .= '<tr>
                    <th class="warning" style="text-align:center" colspan="1">SPR:</th>
                    <th class="warning" style="text-align:center" colspan="1">' . $epg['spr_id'] . '</th>
                    <td align="left" colspan="10">' . $epg['spr_nombre'] . '</td>
                </tr>';
            }
            if ($epg['prg_id'] != $prg || $epg['spr_id'] != $spr || $epg['py_id'] != $py) {
                $htm .= '<tr>
                        <th class="warning" style="text-align:center" colspan="1">PYT:</th>
                        <th class="warning" style="text-align:center" colspan="1">' . $epg['py_id'] . '</th>
                        <td align="left" colspan="5">' . $epg['py_nombre'] . '</td>
                        <th class="warning" align="center" colspan="2">APROBADO</th>
                        <th class="warning" align="center" colspan="2">MODIFICADO</th>
                        <th class="warning" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="warning" align="center" colspan="2">EJERCIDO</th>
                    </tr>';
            }
            if ($epg['prg_id'] != $prg || $epg['spr_id'] != $spr || $epg['py_id'] != $py || $epg['oa_id'] != $oa) {
                $clase = ($cambioClase) ? 'class="warning"' : '';
                $htm .= '<tr class="warning" >
                    <td ' . $clase . ' align="center" colspan="1"></td>
                    <td ' . $clase . ' align="center" colspan="1">' . $epg['prg_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $epg['spr_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $epg['py_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $epg['oa_id'] . '</td>
                    <td ' . $clase . ' align="left" colspan="2">' . $epg['oa_nombre'] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($epg['pre_aprobado'] ?? 0, 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($epg['pre_vigente'] ?? 0, 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($epg['pre_comprometido'] ?? 0, 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($epg['pre_ejercido'] ?? 0, 2) . '</td>
                </tr>';
                $cambioClase = ($cambioClase) ? false : true;
            }
            $ur = $epg['gpo_id'] . $epg['ur_id'];
            $prg = $epg['prg_id'];
            $spr = $epg['spr_id'];
            $py = $epg['py_id'];
            $oa = $epg['oa_id'];
        }
        $htm .= ' </table> ';
        $this->mixHtml($htm);
    }
}

if (isset($_SESSION[_S]['prm']['S']['5']['13']['1'])) {
    new ControladorConsultaEstructuraProg();
} else {
    WebResponse::sinPermisos();
}
