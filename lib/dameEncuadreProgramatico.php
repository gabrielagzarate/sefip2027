<?php
DEFINE("LINEAS_POR_PAGINA", 99);

if (!isset($_SESSION['_S'])) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/AccesoDatos.php';

class dameEncuadreProgramatico extends AccesoDatos
{
    public $prm;

    public function __construct()
    {
        parent::__construct();

        $paginaActual = ((isset($_POST['p']) && $_POST['p'] != "0") ? $_POST['p']
            : "");
        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }

        $select = "";
        $sql = "";
        $ejecutora = isset($_POST['ue_id']) ? $_POST['ue_id'] : "";
        $params = [];

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        $obr = "";

        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'presupuestoValidada':
                    $params = [];
                    $sql = "
                        FROM " . BD_CTRL . ".cat_ObraActividad
                        INNER JOIN " . BD_CTRL . ".enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                        WHERE gpo_id = :gpo AND ur_id = :ur
                        AND eoa_id = :eoa AND toa_id = :toa AND oa_id!= :oa ";
                    $params[":gpo"] = $_SESSION[_S]['clasAdm-gpo'];
                    $params[":ur"] = $_SESSION[_S]['clasAdm-ur'] . $ejecutora;
                    $params[":eoa"] = "4";
                    $params[":toa"] = "1";
                    $params[":oa"] = "00";
                    break;
                case 'presupuestoValidadaPRG000':
                    $params = [];
                    $sql = "
                        FROM " . BD_CTRL . ".cat_ObraActividad
                        INNER JOIN " . BD_CTRL . ".enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                        WHERE gpo_id = :gpo AND ur_id = :ur
                        AND prg_id = :prg_id AND eoa_id = :eoa AND toa_id = :toa AND oa_id!= :oa ";
                    $params[":gpo"] = $_SESSION[_S]['clasAdm-gpo'];
                    $params[":ur"] = $_SESSION[_S]['clasAdm-ur'] . $ejecutora;
                    $params[":eoa"] = "4";
                    $params[":toa"] = "1";
                    $params[":oa"] = "00";
                    $params[":prg_id"] = "000";
                    break;
                case 'planeacionValidada':
                    $params = [];
                    $sql = "
                        FROM " . BD_CTRL . ".cat_ObraActividad
                        INNER JOIN " . BD_CTRL . ".enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                        WHERE gpo_id = :gpo AND ur_id = :ur
                        AND eoa_id = :eoa AND toa_id = :toa AND oa_id!= :oa ";
                    $params[":gpo"] = $_SESSION[_S]['clasAdm-gpo'];
                    $params[":ur"] = $_SESSION[_S]['clasAdm-ur'] . $ejecutora;
                    $params[":eoa"] = "5";
                    $params[":toa"] = "2";
                    $params[":oa"] = "00";
                    break;
            }
        } else {
            $sql = "
                FROM " . BD_CTRL . ".cat_ObraActividad
                INNER JOIN " . BD_CTRL . ".enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            ";
        }
        $this->conectar();
        $this->setSql("
            SELECT COUNT(epg_id) AS total
            FROM (
                SELECT epg_id
                " . $sql . "
            )p;
        ");
        $this->consultar($params);
        $this->desconectar();
        $limit = "";
        $row = $this->extraer();
        $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);

        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'presupuestoValidada':
                    $select = "
                        SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id) prog,
                        oa_nombre as npro
                    ";
                    break;
                case 'presupuestoValidadaPRG000':
                    $select = "
                        SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id) prog,
                        oa_nombre as npro
                    ";
                    break;
                case 'planeacionValidada':
                    $select = "
                        SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id) prog,
                        oa_nombre as npro
                    ";
                    break;
                default:
                    $select = "";
                    $limit = "";
            }
        } else {
            $select = "SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id,'-',oa_nombre) npro";
            $limit = "LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA;
        }

        $sqlRes = $select . $sql . $limit;

        $this->conectar();
        $this->setSql($sqlRes);
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() == 0) {
            echo json_encode(
                array(
                    array('epg_id' => '', 'prog' => '0', 'npro' => ''),
                    array('epg_id' => '', 'prog' => '', 'npro' => 'No se encontró información'),
                )
            );
        } else {
            $data = array(array('epg_id' => '', 'prog' => $nTotalPages, 'npro' => ''));
            while ($row = $this->extraer()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
    }
}
new dameEncuadreProgramatico();
