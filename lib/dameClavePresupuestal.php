<?php
/**
 * Description of ControladorRegistroModificaciones
 *
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 14-Feb-2019
 * Comment: -----
 * Objective:
- Consulta que muestra las  clave de presupuesto
 * Regla de Negocio: -----
 * */
// Si no llegaron aquí por AJAX ...
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])
    === 'xmlhttprequest')) {
    echo "Poor Cyrus ...";
    return;
}

DEFINE("LINEAS_POR_PAGINA", 60);

require_once dirname(__FILE__) . "/constantes.php";
require_once dirname(__FILE__) . '/AccesoDatos.php';

if (!isset($_SESSION)) {
    session_start();
}

class dameClavePresupuestal extends AccesoDatos
{
    public $prm;

    public function __construct()
    {
        parent::__construct();

        if (!empty($_POST)) {
            $this->prm = json_decode(json_encode($_POST));
        } else {
            $this->prm = new stdClass();
        }

        $paginaActual = ((isset($this->prm->p) && $this->prm->p != "0") ? $this->prm->p : "");

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;

        if ($paginaActual != "") {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }

        if ($paginaActual == "") {
            $this->conectar();
            $this->setSql("
                SELECT
                CONCAT(p.gpo_id, p.ur_id, ue_id, '-', p.prg_id, p.spr_id, p.py_id, p.oa_id,'-',p.pg_id,p.pe_id,MIN(CONCAT(p.cff_id, p.fte_id, p.sft_id, p.ori_id, p.sor_id, p.cfi_id, p.cfi_anio )) ) AS prog,
                if(p.oa_id != 0,
                oa_nombre,
                py_nombre) AS nombre,
                oa_nombre as npro,
                py.py_nombre AS npy,
                MIN(CONCAT(p.cff_id, '-', p.fte_id, p.sft_id, p.ori_id, p.sor_id, '-', p.cfi_id, p.cfi_anio)) AS cveF,
                MIN(cfi_nombre) AS ncve
                FROM presupuesto p
                INNER JOIN scp_DetalleModificacion d using(gpo_id, ur_id, ue_id,ecf_id,epg_id)
                INNER JOIN enc_Programatico using(epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_ObraActividad oa using(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                INNER JOIN cat_Proyecto py using(gpo_id, ur_id, prg_id, spr_id, py_id)
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                WHERE d.gpo_id= :gpo AND d.ur_id = :ur AND p.oa_id <> 0
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,p.pg_id,p.pe_id
                UNION
                SELECT
                CONCAT(p.gpo_id, p.ur_id, ue_id, '-', p.prg_id, p.spr_id, p.py_id, p.oa_id,'-',p.pg_id,p.pe_id,MIN(CONCAT(p.cff_id, p.fte_id, p.sft_id, p.ori_id, p.sor_id, p.cfi_id, p.cfi_anio )) ) AS prog,
                if(p.oa_id != 0,
                oa_nombre,
                py_nombre) AS nombre,
                oa_nombre as npro,
                py.py_nombre AS npy,
                MIN(CONCAT(p.cff_id, '-', p.fte_id, p.sft_id, p.ori_id, p.sor_id, '-', p.cfi_id, p.cfi_anio)) AS cveF,
                MIN(cfi_nombre) AS ncve
                FROM presupuesto p
                INNER JOIN scp_DetalleModificacion d using(gpo_id, ur_id, ue_id,ecf_id,epg_id)
                INNER JOIN enc_Programatico using(epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_ObraActividad oa using(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                INNER JOIN cat_Proyecto py using(gpo_id, ur_id, prg_id, spr_id, py_id)
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                WHERE d.gpo_id= :gpo AND d.ur_id = :ur AND p.oa_id = 0
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,p.pg_id,p.pe_id
                ORDER BY prog;");

            $parametros = [
                'gpo' => $_SESSION[_S]['gpo_id'],
                'ur' => $_SESSION[_S]['ur_id'],
            ];
            $this->consultar($parametros);
            $this->desconectar();

            $c = 0;
            while ($row = $this->extraer()) {
                $c++;
            }
            $nTotalPages = ceil($c / LINEAS_POR_PAGINA);
        } else {
            $nTotalPages = 0;
        }

        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $this->conectar();
        $this->setSql("
            SELECT
                CONCAT(p.gpo_id, p.ur_id, ue_id, '-', p.prg_id, p.spr_id, p.py_id, p.oa_id,'-',p.pg_id,p.pe_id,MIN(CONCAT(p.cff_id, p.fte_id, p.sft_id, p.ori_id, p.sor_id, p.cfi_id, p.cfi_anio )) ) AS prog,
                if(p.oa_id != 0,
                oa_nombre,
                py_nombre) AS nombre,
                oa_nombre as npro,
                py.py_nombre AS npy,
                MIN(CONCAT(p.cff_id, '-', p.fte_id, p.sft_id, p.ori_id, p.sor_id, '-', p.cfi_id, p.cfi_anio)) AS cveF,
                MIN(cfi_nombre) AS ncve
            FROM presupuesto p
            INNER JOIN scp_DetalleModificacion d using(gpo_id, ur_id, ue_id,ecf_id,epg_id)
            INNER JOIN enc_Programatico using(epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
            INNER JOIN cat_ObraActividad oa using(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
            INNER JOIN cat_Proyecto py using(gpo_id, ur_id, prg_id, spr_id, py_id)
            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE d.gpo_id= :gpo AND d.ur_id = :ur AND p.oa_id <> 0
            GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,p.pg_id,p.pe_id
            UNION
            SELECT
                CONCAT(p.gpo_id, p.ur_id, ue_id, '-', p.prg_id, p.spr_id, p.py_id, p.oa_id,'-',p.pg_id,p.pe_id,MIN(CONCAT(p.cff_id, p.fte_id, p.sft_id, p.ori_id, p.sor_id, p.cfi_id, p.cfi_anio )) ) AS prog,
                if(p.oa_id != 0,
                oa_nombre,
                py_nombre) AS nombre,
                oa_nombre as npro,
                py.py_nombre AS npy,
                MIN(CONCAT(p.cff_id, '-', p.fte_id, p.sft_id, p.ori_id, p.sor_id, '-', p.cfi_id, p.cfi_anio)) AS cveF,
                MIN(cfi_nombre) AS ncve
            FROM presupuesto p
            INNER JOIN scp_DetalleModificacion d using(gpo_id, ur_id, ue_id,ecf_id,epg_id)
            INNER JOIN enc_Programatico using(epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
            INNER JOIN cat_ObraActividad oa using(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
            INNER JOIN cat_Proyecto py using(gpo_id, ur_id, prg_id, spr_id, py_id)
            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE d.gpo_id= :gpo AND d.ur_id = :ur AND p.oa_id = 0
            GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,p.pg_id,p.pe_id
            ORDER BY prog
            LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA . ";");

        $parametros2 = [
            'gpo' => $_SESSION[_S]['gpo_id'],
            'ur' => $_SESSION[_S]['ur_id'],
        ];
        $this->consultar($parametros2);
        $this->desconectar();
        if ($this->getNumeroRegistros() == 0) {
            echo json_encode(
                array(
                    array('nombre' => '', 'prog' => '', 'npy' => '', 'cveF' => ''),
                    array('nombre' => 'NO SE ENCONTRÓ INFORMACIÓN', 'prog' => '',
                        'npy' => '', 'cveF' => ''),
                )
            );
        } else {
            $data = array(array('nombre' => $nTotalPages, 'prog' => '', 'npy' => '',
                'cveF' => ''));
            while ($row = $this->extraer()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
    }
}
new dameClavePresupuestal();
