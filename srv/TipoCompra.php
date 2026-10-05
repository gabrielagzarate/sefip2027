<?php
require_once dirname(__FILE__) . '/../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
}
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../ent/UnidadResponsable.php';

class TipoCompra extends WebResponse
{
    public $adm_grupo;
    public $adm_ur;
    public $adm_unidadEjecutora;
    public $adm_encuadreProgramatico;

    public function __construct()
    { //Constructor
        parent::__construct();

        // Variables de sesión
        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo']
        : false;
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur']
        : false;
        $this->adm_unidadEjecutora = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue']
        : false;
        $this->adm_encuadreProgramatico = isset($_SESSION[_S]['clasAdm-epg']) ? $_SESSION[_S]['clasAdm-epg']
        : false;

        if (isset($this->prm->metodoTipoCompra)) {
            $metodo = (isset($this->prm->metodoTipoCompra)) ? $this->prm->metodoTipoCompra
            : '';
            switch ($metodo) {
                case 'cargaTipoCompra':
                    $this->cargaTipoCompra();
                    break;
            }
            $this->returnJson();
        }
    }

    public function comboSeleccionaTipoCompra()
    {
        $this->cargaTipoCompra();
    }

    public static function getTipoCompra()
    {
        $tc = $tc = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : 
        (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $form = '';
        $tcp = new WebResponse();
        $mod = new MaestroModificacion($tcp->prm);
        $enc_oop = new OrigenOperacion(array("enc_oop" => $mod->getOop()));
        $preseleccion = '';
        $required = true;
        if (isset($tc->form)) {
            $form = $tc->form;
            $required = isset($tc->required);
        }

        $combo = new stdClass();
        $combo->id = 'tcp_id';
        $combo->sql = '';

        $combo->enDiv = true;
        if ($form != '') {
            $combo->form = 'Seleccionar partida especifica';
        }

        if (isset($tcp->prm->tcp_id)) {
            $preseleccion = new TipoCompra();
            $preseleccion->comboSeleccionaTipoCompra();
            $preseleccion = $preseleccion->getScript();
        }

        $combo->required = $required;
        $combo->label = 'Seleccionar tipo de compra';
        $combo->mensaje = 'Seleccionar partida especifica';
        $combo->errormessage = 'Sin registros';
        $tipoCompra = $tcp->templateCombo($combo);

        return '
            <legend>Tipo de compra</legend>
                ' . $tipoCompra . '
            <script language="javascript" >
                $("#tcp_id").change(function(){
                    if($("#tcp_id").val()!=""){
                        comunica("scp/adecuaciones/ControladorRegistroModificaciones.php",{
                            metodo:"muestraPresupuesto",
                            tcp_id:$("#tcp_id").val(),
                            tmd_id:"' . $enc_oop->getTmd_id() . '",
                            mmd_id:"' . $enc_oop->getMmd_id() . '",
                            ecf_id:"' . $mod->getEcf_id() . '",
                            pg_id:$("#pg_id").val(),
                            pe_id:$("#pe_id").val(),
                        });
                    }
                });
                ' . $tcp->getScript() . '
                ' . $preseleccion . '
            </script>
        ';
    }

    private function cargaTipoCompra()
    {
        $combo = new stdClass();
        $combo->id = 'tcp_id';
        $combo->detonar = (isset($this->prm->tcp_id)) ? false : true;
        //validacion para que verifique si la ur tiene tipo de compra consolidada / directa
        if (UnidadResponsable::excentaComprasConsolidadas(array("gpo_id" => $this->adm_grupo,
            "ur_id" => $this->adm_ur, "pe_id" => $this->prm->pe_id, "ecf_id" => $this->prm->ecf_id))) {
            $this->prm->tcp_id = "D";
        }
        if (isset($this->prm->tcp_id) && !empty($this->prm->tcp_id)) {
            $sql = 'select tcp_id,tcp_nombre from cat_TipoCompra where tcp_id="' . $this->prm->tcp_id . '"';
        } else {
            $where1 = 'WHERE gpo_id=' . $this->adm_grupo . ' AND ur_id=' . $this->adm_ur . '';
            $where2 = 'WHERE m.mod_gpo_id=' . $this->adm_grupo . ' AND m.mod_ur_id=' . $this->adm_ur . '';

            $where1 .= ($this->adm_unidadEjecutora != false && $this->adm_unidadEjecutora
                != 0) ?
            ' AND ue_id=' . $this->adm_unidadEjecutora . '' : '';
            $where1 .= ($this->adm_encuadreProgramatico != false && $this->adm_encuadreProgramatico
                != 0) ?
            ' AND epg_id=' . $this->adm_encuadreProgramatico . '' : '';
            $where1 .= (isset($this->prm->pg_id) && $this->prm->pg_id != "") ?
            ' AND pg_id=' . $this->prm->pg_id . '' : '';
            $where1 .= (isset($this->prm->pe_id) && $this->prm->pe_id != "") ?
            ' AND pe_id=' . $this->prm->pe_id . '' : '';
            $where1 .= ($this->prm->ecf_id != "") ? ' AND ecf_id=' . $this->prm->ecf_id . ''
            : '';
            $where1 .= (isset($this->prm->tcp_id2) && $this->prm->tcp_id2 != "")
            ?
            ' AND tcp_id="' . $this->prm->tcp_id2 . '" ' : '';

            $where2 .= ($this->adm_unidadEjecutora != false && $this->adm_unidadEjecutora
                != 0) ? ' AND ue_id=' . $this->adm_unidadEjecutora . ' ' : '';
            $where2 .= ($this->adm_encuadreProgramatico != false && $this->adm_encuadreProgramatico
                != 0) ?
            ' AND epg_id=' . $this->adm_encuadreProgramatico . ' ' : '';
            $where2 .= (isset($this->prm->pg_id) && $this->prm->pg_id != "") ?
            ' AND pg_id=' . $this->prm->pg_id . ' ' : '';
            $where2 .= (isset($this->prm->pe_id) && $this->prm->pe_id != "") ?
            ' AND pe_id=' . $this->prm->pe_id . ' ' : '';
            $where2 .= ($this->prm->ecf_id != "") ?
            ' AND m.ecf_id=' . $this->prm->ecf_id . '' : '';
            $where2 .= (isset($this->prm->tcp_id2) && $this->prm->tcp_id2 != "")
            ? ' AND tcp_id="' . $this->prm->tcp_id2 . '"' : '';

            $sql = "
                SELECT DISTINCT(p.tcp_id) tcp_id, c.tcp_nombre, p.pe_id
                    FROM
                        (
                            SELECT
                                DISTINCT gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id,
                                ecf_id, tcp_id
                            FROM presupuesto
                            $where1
                        ) as p
                        INNER JOIN cat_TipoCompra c USING(tcp_id)
                        LEFT JOIN
                        (
                            SELECT
                                DISTINCT m.mod_gpo_id, m.mod_ur_id, d.gpo_id, d.ur_id, d.ue_id, d.epg_id, d.pg_id, d.pe_id, m.ecf_id, d.tcp_id
                            FROM scp_MaestroModificacion m
                                INNER JOIN scp_DetalleModificacion d USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                            $where2 AND m.emo_id NOT IN (14,15,16)
                        ) as t USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                        WHERE t.pe_id IS NULL ORDER BY  p.pe_id
            ";
        }
        $combo->sql = $sql;
        $combo->mensaje = true;
        $this->recargaCombo($combo);
    }
}
if (isset($_POST['metodoTipoCompra'])) {
    new TipoCompra();
}