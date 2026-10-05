<?php
DEFINE("LINEAS_POR_PAGINA", 99);

if (!isset($_SESSION['_S'])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/AccesoDatos.php';

class dameProgramaFinanciamiento extends AccesoDatos
{
    public $prm;
    public function __construct()
    {
        parent::__construct();

        $paginaActual = ((isset($_POST['p']) && $_POST['p'] != "0") ? $_POST['p'] : "");
        $arrayMerge = [];
        // Si es un usuario de nivel UE, limitamos por UE
        // Si no, mostramos todas las UE
        $ejecutora = "";
        $paramsEje = [];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            // ERROR: De momento, filtramos por UE sólo cuando se trate de un usuario de nivel UE
            if ($_SESSION[_S]['nvj_id'] == 1) {
                $ejecutora = " AND ue_id = ?";
                array_push($paramsEje, $_SESSION[_S]['clasAdm-ue']);
            }
        }

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }

        $select = "";
        $sql = "";
        $joinInversion = "";

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        $obr = "";
        $paramsObr = [];
        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'enc_InversionObra':
                    $joinInversion = 'LEFT JOIN enc_InversionObra obr USING(epg_id, gpo_id, ur_id)';
                    /* El filtro ipu_id  filtra las obras por el contrato ya que en el en encuadre enc_InversionObra ahi vienen las obras que tiene cada contrato */
                    if (isset($_POST['paso']) && $_POST['paso'] != '') {
                        switch ($_POST['paso']) {
                            case 0: //otras clc´s
                                break;
                            case 1: //obra con contrato
                                if (isset($_POST['ipu_id']) && $_POST['ipu_id'] != '') {
                                    $obr = " and ipu_id= ?";
                                    array_push($paramsObr, $_POST['ipu_id']);
                                }
                                break;
                            case 2: //obra sin contrato
                                break;
                        }
                    }
                // no break
                case 'porUrClaveFinanciamiento':
                    $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 && isset($_POST['mod_ue_id'])) {
                        $ejecutora = " AND ue_id = ?";
                        array_push($params, $_POST['mod_ue_id']);
                    }
                    $sql = "
                        FROM presupuesto p
                            INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
                            INNER JOIN enc_Programatico USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                            INNER JOIN cat_ObraActividad oa USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                            INNER JOIN cat_Proyecto USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                            INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                            " . $joinInversion . "
                        WHERE p.gpo_id = ? AND p.ur_id = ?
                            AND ur.eur_id = 5
                            AND f.sts_id  = 1
                            AND ((oa.toa_id = 1 AND oa.eoa_id = 4)
                            OR (oa.toa_id = 2 AND oa.eoa_id = 5))
                            $ejecutora
                            " . (isset($_POST['ecf_id']) ? ' AND p.ecf_id = ?' : "") . "
                            " . (isset($_POST['toa_id']) ? ' AND oa.toa_id = ?' : "") . "
                            " . $obr . "
                        GROUP BY gpo_id, ur_id, epg_id, p.ecf_id, cfi_nombre";
                    isset($_POST['ecf_id']) ? array_push($params, $_POST['ecf_id']) : "";
                    isset($_POST['toa_id']) ? array_push($params, $_POST['toa_id']) : "";
                    $arrayMerge = $params;
                    break;
                case 'Reintegro':
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 && isset($_POST['mod_ue_id'])) {
                        $ejecutora = " AND ue_id = " . $_POST['mod_ue_id'] . " ";
                    }
                    $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
                    $sql = "
                        from presupuesto p
                            INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
                            INNER JOIN enc_Programatico USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                            INNER JOIN cat_ObraActividad oa USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                            INNER JOIN cat_Proyecto USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                            INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                        WHERE p.gpo_id = ?
                            AND p.ur_id = ?
                            AND ur.eur_id = 5
                            AND f.sts_id  = 1
                            AND ((oa.toa_id = 1 AND oa.eoa_id = 4) or (oa.toa_id = 2 AND oa.eoa_id = 5))
                            $ejecutora
                            " . (isset($_POST['ecf_id']) ? ' AND p.ecf_id = ?' : "") . "
                            " . (isset($_POST['toa_id']) ? ' AND oa.toa_id = ?' : "") . "
                            AND oa.oa_origen = 'R'
                            AND ((oa.oa_anticipo + oa_anticipo".APP_EJERCICIO_ANTERIOR.") - oa.oa_amortizacion) > 0
                        group by gpo_id, ur_id, epg_id, ecf_id, cfi_nombre
                    ";
                    isset($_POST['ecf_id']) ? array_push($params, $_POST['ecf_id']) : "";
                    isset($_POST['toa_id']) ? array_push($params, 2) : "";
                    $arrayMerge = array_merge($params, $paramsEje);
                    break;
            }
        } else {
            $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
            $sql = "
                FROM poa_OrigenRecurso o
                    INNER JOIN enc_Programatico p USING (epg_id, gpo_id, ur_id)
                    INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id)
                    INNER JOIN cat_Proyecto py USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                    INNER JOIN cat_ObraActividad oa ON (p.gpo_id = oa.gpo_id AND p.ur_id = oa.ur_id AND p.prg_id = oa.prg_id AND p.spr_id = oa.spr_id AND p.py_id = oa.py_id AND p.oa_id = oa.oa_id)
                    INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                WHERE o.gpo_id = ? AND o.ur_id = ?
                    AND oa.ue_id IS NOT NULL
                    AND f.sts_id = 1
                    AND ((oa.toa_id = 1 AND oa.eoa_id = 4) OR (oa.toa_id = 2 AND oa.eoa_id = 5))
                    $ejecutora
                    AND f.ecf_poa = 1
            ";
            $arrayMerge = array_merge($params, $paramsEje);
            //El estado 7 es validada por Egresos, a diferencia del 6 que solo es de planeación.
        }
        $this->conectar();
        $this->setSql("
            SELECT COUNT(epg_id) AS total
            FROM (
                SELECT epg_id
                " . $sql . "
            )p
        ");
        $this->consultar($arrayMerge);
        $this->desconectar();

        $row = $this->extraer();
        $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);

        // Obtenemos la primera página a mostrar
        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'enc_InversionObra':
                case 'porUrClaveFinanciamiento':
                    $select = "
                        SELECT
                            p.epg_id,
                            p.ecf_id,
                            CONCAT(p.gpo_id, p.ur_id, '-', ue_id, '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id) AS prog,
                            if(p.oa_id != 0,
                                oa_nombre,
                                py_nombre) AS nombre,
                            oa_nombre AS npro,
                            py_nombre AS npy,
                            CONCAT(f.cff_id, '-', f.fte_id, f.sft_id, f.ori_id, f.sor_id, '-', f.cfi_id, f.cfi_anio) AS cveF,
                            cfi_nombre AS ncve
                    ";
                    $limit = "";
                    break;
                case 'Reintegro':
                    $select = "
                        SELECT
                            p.epg_id,
                            p.ecf_id,
                            CONCAT( p.gpo_id, p.ur_id, '-', ue_id, '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id ) AS prog,
                            if(p.oa_id != 0,
                                oa_nombre,
                                py_nombre) AS nombre,
                            oa_nombre AS npro,
                            py_nombre AS npy,
                            CONCAT( f.cff_id, '-', f.fte_id, f.sft_id, f.ori_id, f.sor_id, '-', f.cfi_id, f.cfi_anio ) AS cveF,
                            cfi_nombre AS ncve
                    ";
                    $limit = "";
                    break;
                default:
                    $select = "";
                    $limit = "";
            }
        } else {
            $select = "
                SELECT
                    o.epg_id,
                    o.ecf_id,
                    CONCAT( p.gpo_id, p.ur_id, '-', IFNULL( ue_id, 'XX' ), '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id ) AS prog,
                    if(p.oa_id != 0,
                        oa_nombre,
                        py_nombre) AS nombre,
                    oa_nombre AS npro,
                    py_nombre AS npy,
                    CONCAT( f.cff_id, '-', f.fte_id, f.sft_id, f.ori_id, f.sor_id, '-', f.cfi_id, f.cfi_anio ) AS cveF,
                    cfi_nombre AS ncve
            ";
            $limit = "LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA;
        }
        $this->conectar();
        $this->setSql($select . $sql . $limit);
        $this->consultar($arrayMerge);
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {
            echo json_encode(
                array(
                    array('epg_id' => '', 'ecf_id' => '', 'prog' => '0', 'nombre' => '', 'npy' => '', 'npro' => '', 'cveF' => '', 'ncve' => ''),
                    array('epg_id' => '', 'ecf_id' => '', 'prog' => '', 'nombre' => '', 'npy' => '', 'npro' => 'No se encontró información', 'cveF' => '', 'ncve' => ''),
                )
            );
        } else {
            $data = array(array('epg_id' => '', 'ecf_id' => '', 'prog' => $nTotalPages, 'nombre' => '', 'npy' => '', 'npro' => '', 'cveF' => '', 'ncve' => ''));
            while ($row = $this->extraer()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
    }
}

new dameProgramaFinanciamiento();
