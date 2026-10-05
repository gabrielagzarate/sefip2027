<?php

// Si no llegaron aquí por AJAX ...
if (!( isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' )) {
    echo "Poor Cyrus ...";
    return;
}

DEFINE("LINEAS_POR_PAGINA", 60);

require_once dirname(__FILE__) . "/constantes.php";
require_once dirname(__FILE__) . '/AccesoDatos.php';

if (!isset($_SESSION)) {
    session_start();
}

class dameCatalogoCuentas extends AccesoDatos
{

    public $prm;

    public function __construct()
    {
        parent::__construct();

        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if (!empty($_POST))
            $this->prm = json_decode(json_encode($_POST));
        else
            $this->prm = new stdClass();

        $ref = ( ( isset($this->prm->r) ) ? trim($this->prm->r) : "" );
        $bdo = ( ( isset($this->prm->b) ) ? trim($this->prm->b) : "" );
        $rfc = ( ( isset($this->prm->f) ) ? trim($this->prm->f) : "" );
        $con = ( ( isset($this->prm->c) ) ? trim($this->prm->c) : "" );


        $paginaActual = ( ( isset($this->prm->p) && $this->prm->p != "0" ) ? $this->prm->p : "" );

        // FILTROS
        $where = "";

        // Referencia. OJO: Usaremos LIKE para que muestre cualquiera que contenga la cadena
        // Darle seguimiento si ésto pega al rendimiento de la aplicación o al servidor
        if ($ref != "") {
            $where .= " AND bdo_referencia LIKE '%" . $ref . "%' ";
        }

        // bdo_id
        if ($bdo != "") {
            $where .= " AND bdo_id = " . $bdo . " ";
        }

        // rfc
        if ($rfc != "") {
            $where .= " AND rfc_id = '" . $rfc . "' ";
        }

        // Concepto: OJO, también usaremos LIKE... mismas observaciones que la Referencia
        if ($con != "") {
            $where .= " AND bdo_concepto LIKE '%" . $con . "%' ";
        }


        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = ( ( $paginaActual - 1 ) * LINEAS_POR_PAGINA );
        }

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        if ($paginaActual == "") {

            $this->conectar();
            $this->setSql("SELECT COUNT(*) AS total FROM cat_BancoDocumentos 
                WHERE gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND ur_id = " . $_SESSION[_S]['clasAdm-ur'] . "
                $where 
                ;"
            );
            $this->consultar();
            $this->desconectar();

            $row = $this->extraer();
            $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);
        } else {

            $nTotalPages = 0;
        }

        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $this->conectar();
        $this->setSql("SELECT bdo_id as bdo, tdf_id as tdf, bdo_referencia as ref, 
            DATE_FORMAT( bdo_fechaDocumento, '%d-%m-%y' ) AS fec, 
            rfc_id as rfc, bdo_percepcionPresupuestal as imp, bdo_concepto as con
            FROM cat_BancoDocumentos 
            WHERE gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND ur_id = " . $_SESSION[_S]['clasAdm-ur'] . "
            $where 
            LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA . ";"
        );
         // echo "<br /><br />".$this->getSql()."<br /><br />";
         //  die(); 
        $this->consultar();
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {
            echo json_encode(
                    array(
                        array('tdf' => '0', 'ref' => '', 'fec' => '', 'rfc' => '', 'imp' => '', 'con' => '', 'bdo' => ''),
                        array('tdf' => '0', 'ref' => 'NO SE ENCONTRÓ INFORMACIÓN', 'fec' => '', 'rfc' => '', 'imp' => '', 'con' => '', 'bdo' => '')
                    )
            );
        } else {
            $data = array(array('tdf' => $nTotalPages, 'ref' => '', 'fec' => '', 'rfc' => '', 'imp' => '', 'con' => '', 'bdo' => ''),);
            while ($row = $this->extraer()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }

    }

}

new dameCatalogoCuentas();
?>