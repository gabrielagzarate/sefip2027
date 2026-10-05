<?php

// Si no llegaron aquí por AJAX ...
if (!( isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' )) {
    echo "Poor Cyrus ...";
    return;
}

DEFINE("LINEAS_POR_PAGINA", 60);

require_once dirname(__FILE__) . "/../constantes.php";
require_once dirname(__FILE__) . '/../AccesoDatos.php';

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

        $cuenta = ( ( isset($this->prm->v) ) ? $this->prm->v : "" );
        $cuarto = ( ( isset($this->prm->cto) ) ? trim($this->prm->cto) : "" );

        $paginaActual = ( ( isset($this->prm->p) && $this->prm->p != "0" ) ? $this->prm->p : "" );

        // Marcos: 11/mayo/2016
        // Si no pasan la UE, y es un usuario de nivel UE, tomaremos su ue
        // si no es de nivel UE, seguimos el comportamiento normal
        // ( Ocasionaba error en caso de un usuario de nivel UE, ya que fijo lo mandaba a la 1 si no pasaban ue)
        if (isset($this->prm->e)) {
            //$ue = $this->prm->e;
            $ue = 5;
        } else {
            if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
                //$ue = $_SESSION[_S]['clasAdm-ue'];
                $ue = 5;
            } else {
                $ue = 5;
            }
        }

        // Si pidieron el cuarto nivel ... filtramos
        $cuartoNivel = "";
        if ($cuarto != "") {
            $cuartoNivel = " AND cgc_id = '" . $cuarto . "' ";
        }

        // Si es un usuario de nivel UE, limitamos por UE
        // Si no, mostramos todas las UE
        
        $ejecutora = "";
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $ejecutora = " AND cta_ue_id = 005 ";
            echo $ejecutora;
        }

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = ( ( $paginaActual - 1 ) * LINEAS_POR_PAGINA );
        }

        // 09/ene/2018
        // Para el ejercicio 2018, los niveles de rubro y subcuenta pueden ser de dos dígitos
        // Aquí determinamos si la cuenta trae 5 dígitos, es una cuenta "normal" y seguimos obteniendo sus niveles
        // como antes... si no entonces CADA nivel debe estar separado por un punto
        // A nivel de mayor puede ser:
        // 1.1.1.1.1 -> 5 dígitos
        // 1.1.11.1.1 / 1.1.1.1.11 -> 6 dígitos 
        // 1.1.11.1.11 -> 7 dígitos
        if ( strlen( trim( $cuenta ) ) == 5 ) {
            $genero = substr($cuenta, 0, 1);
            $grupo = substr($cuenta, 1, 1);
            $rubro = substr($cuenta, 2, 1);
            $cta = substr($cuenta, 3, 1);
            $scta = substr($cuenta, 4, 1);
        } else {
            $arrCta = explode(".", $cuenta);
            $genero = $arrCta[0];
            $grupo = $arrCta[1];
            $rubro = $arrCta[2];
            $cta = $arrCta[3];
            $scta = $arrCta[4];
        }

        // Al llegar aquí, todos los niveles de la cuenta DEBEN ser números
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cta) || !is_numeric($scta)) {
            echo json_encode(
                    array(
                        array('cta' => 0, 'nom' => '', 'nat' => '', 'ue' => ''),
                        array('cta' => '', 'nom' => '', 'nat' => '', 'ue' => '')
                    )
            );
        } else if (strlen($cuenta) == 0 or strlen($cuenta) < /* != */ 5) {
            echo json_encode(
                    array(
                        array('cta' => 0, 'nom' => '', 'nat' => '', 'ue' => ''),
                        array('cta' => '', 'nom' => '', 'nat' => '', 'ue' => '')
                    )
            );
        } else {

            // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
            if ($paginaActual == "") {

                $this->conectar();
                $this->setSql("SELECT COUNT(*) AS total FROM cg_PlanCuentas
					WHERE ger_id = $genero AND cgp_id = $grupo AND rub_id = $rubro AND cgcu_id = $cta AND cgsc_id = $scta 
					$cuartoNivel
					AND cta_gpo_id = 1 AND cta_ur_id = 16
					$ejecutora;"
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
            /*$this->setSql("SELECT CONCAT(SUBSTRING(REPLACE(cta_cuenta,'.',''),1,5),'.',SUBSTRING(REPLACE(cta_cuenta,'.',''),6,3),'.',SUBSTRING(REPLACE(cta_cuenta,'.',''),9)) AS cta, c.cta_nombre AS nom, m.nat_id AS nat, c.cta_ue_id AS ue
                FROM cg_PlanCuentas c INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                WHERE c.ger_id = $genero AND c.cgp_id = $grupo AND c.rub_id = $rubro AND c.cgcu_id = $cta AND c.cgsc_id = $scta 
                $cuartoNivel
                AND cta_gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND cta_ur_id = " . $_SESSION[_S]['clasAdm-ur'] . "
                $ejecutora
                LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA . ";"
            );*/
            $this->setSql("SELECT CONCAT(SUBSTRING(REPLACE(cta_cuenta,'.',''),1,5),'.',SUBSTRING(REPLACE(cta_cuenta,'.',''),6,3),'.',SUBSTRING(REPLACE(cta_cuenta,'.',''),9)) AS cta, c.cta_nombre AS nom, m.nat_id AS nat, c.cta_ue_id AS ue,
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id
                FROM cg_PlanCuentas c INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                WHERE c.ger_id = $genero AND c.cgp_id = $grupo AND c.rub_id = $rubro AND c.cgcu_id = $cta AND c.cgsc_id = $scta 
                $cuartoNivel
                AND cta_gpo_id = 1 AND cta_ur_id = 16
                $ejecutora
                #LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA . ";"
            );
            
            //echo "<br /><br />".$this->getSql()."<br /><br />";
            //die();
            
            $this->consultar();
            $this->desconectar();

            if ($this->getNumeroRegistros() == 0) {
                echo json_encode(
                        array(
                            array('cta' => '0', 'nom' => '', 'nat' => '', 'ue' => ''),
                            array('cta' => '', 'nom' => 'No se encontró información',
                                'nat' => '', 'ue' => '')
                        )
                );
            } else {
                $data = array(array('cta' => $nTotalPages, 'nom' => '', 'nat' => '',
                        'ue' => ''),);
                while ($row = $this->extraer()) {
                    $data[] = $row;
                }
                echo json_encode($data);
            }
        }
    }

}

new dameCatalogoCuentas();
?>