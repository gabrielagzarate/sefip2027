<?php

// Si no llegaron aquí por AJAX ...
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    echo "Poor ...";
    return;
}

DEFINE("LINEAS_POR_PAGINA", 60);

require_once dirname(__FILE__) . "/constantes.php";
require_once dirname(__FILE__) . '/AccesoDatos.php';

if (!isset($_SESSION)) {
    session_start();
}

class dameCatalogoProveedores extends AccesoDatos
{

    public $prm;

    public function __construct()
    {
        parent::__construct();

        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if (!empty($_POST)) {
            $this->prm = json_decode(json_encode($_POST));
        } else {
            $this->prm = new stdClass();
        }

        $buscar = ((isset($this->prm->v)) ? $this->prm->v : "");
        $ue = ((isset($this->prm->e)) ? $this->prm->e : 1);
        $tB = ((isset($this->prm->tB)) ? $this->prm->tB : "rfc_buscar");
        $paginaActual = ((isset($this->prm->p) && $this->prm->p != "0") ? $this->prm->p : "");

        if ($tB == "proveedor_buscar") {
            $nombreCampo = "rfc_nombre";
        } else {
            $nombreCampo = "rfc_id";
        }

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }


        if (strlen($buscar) == 0 or strlen($buscar) < 3) {
            echo json_encode(
                array(
                    array('rfc' => 0, 'nom' => ''),
                    array('rfc' => '', 'nom' => '')
                )
            );
        } else {
            // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
            if ($paginaActual == "") {
                $this->conectar();
                
                $this->setSql("
                    SELECT
                        COUNT(cp.rfc_id) AS total
                    FROM cat_Proveedor cp
                        INNER JOIN sinpres.cat_Proveedores scp USING( rfc_id )
                        INNER JOIN sinpres.cat_SociedadProveedor scsp USING (soc_id)
                    WHERE " . $nombreCampo . " LIKE '" . $buscar . "%'
                        AND sts_id = 1;"
                );
                $this->consultar();
                $this->desconectar();
                $row = $this->extraer();
                $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);
            } else {
                $nTotalPages = 0;
            }

            // Obtenemos la primera página a mostrar
            // --------------------------------------------------------------------------------------------------------------

            $this->conectar();
            $sql = "
                SELECT
                    cp.rfc_id AS rfc, CONCAT( scp.rfc_nombre, ' ', scp.rfc_Paterno, ' ', scp.rfc_Materno ) AS nom,
                    scp.tcy_id AS tcy
                FROM cat_Proveedor cp
                    INNER JOIN sinpres.cat_Proveedores scp using( rfc_id )
                    INNER JOIN sinpres.cat_SociedadProveedor scsp  USING (soc_id)
                WHERE " . $nombreCampo . " LIKE '" . $buscar . "%'
                    AND sts_id = 1
                LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA . ";";

            $this->setSql($sql);
            $this->consultar();
            $this->desconectar();

            if ($this->getNumeroRegistros() == 0) {
                echo json_encode(
                    array(
                        array('rfc' => '0', 'nom' => ''),
                        array('rfc' => '', 'nom' => 'No se encontró información')
                    )
                );
            } else {
                $data = array(array('rfc' => $nTotalPages, 'nom' => ''),);
                while ($row = $this->extraer()) {
                    $data[] = $row;
                }
                echo json_encode($data);
            }
        }
    }
}

new dameCatalogoProveedores();
