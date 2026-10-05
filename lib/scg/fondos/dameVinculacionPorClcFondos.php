<?php

// Si no llegaron aquí por AJAX ...
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    echo "Poor Cyrus ...";
    return;
}   

DEFINE( "LINEAS_POR_PAGINA", 20000);
require_once dirname(__FILE__)."/../../constantes.php"; 
require_once dirname(__FILE__).'/../../AccesoDatos.php';

if (!isset($_SESSION)) { session_start(); }

// Marcos: 02/abr/2018
// Para la vinculación por CLC de Fondos
// Se usa un código separado, para no llenar de IFs y quede más claro
    
class dameDocumentos extends AccesoDatos
{
    public $prm;
    
    public function __construct() {
        parent::__construct();
        
        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if( !empty($_POST) )$this->prm = json_decode( json_encode($_POST) );
        else $this->prm = new stdClass();
                
        $msc = ((isset($this->prm->msc) && $this->prm->msc != "") ? $this->prm->msc : '');
        $tip = ((isset($this->prm->tip) && $this->prm->tip != "" && $this->prm->tip != "0") ? $this->prm->tip : '');
        $edo = ((isset($this->prm->edo) && $this->prm->edo != "" && $this->prm->edo != "0") ? $this->prm->edo : '');
        $paginaActual = ((isset($this->prm->p) && $this->prm->p != "0") ? $this->prm->p : "");

        $ur = ((isset($this->prm->ur) && $this->prm->ur != "") ? $this->prm->ur : '');
        $buscar = ((isset($this->prm->buscar) && $this->prm->buscar != "") ? $this->prm->buscar : '');

        $soloGrupoCLCs = (isset($this->prm->gpoClc) ? $this->prm->gpoClc : "0");
        
        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }

        // Si quieren filtrar por Tipo de Clc
        // Aunque aquí meteremos también el origen del recurso y el modo de pago
        $tipoClc = "";
        $oriRec = "";
        if ($tip != "") {
            // Los numéricos se refieren al modo de pago
            if (is_numeric($tip)) {
                $tipoClc = " AND mpc_id = " . $tip . " ";
            // Para filtrar por Origen del Recurso (Federal o Estatal) tenemos 2 tipos específicos
            } else if ($tip == "OA" || $tip == "OB") {
                $oriRec = " WHERE ter_id = '" . substr($tip, -1) . "' ";
            // Finalmente, sí validamos por tipo de clc
            } else {
                $tipoClc = " AND tcl_id = '" . $tip . "'";
            }

        }
        
        // Si quieren filtrar por el Estado de la Clc
        $estadoClc = "";
        if ($edo != "") {
            switch ($edo) {
                case 'EC_A':
                    $estadoClc = " AND ecl_id BETWEEN 7 AND 9 ";
                    break;
                case 'EC_B':
                    $estadoClc = " AND ecl_id BETWEEN 10 AND 15 ";
                    break;
                case 'EC_C':
                    $estadoClc = " AND ecl_id = 16 ";
                    break;
                case 'EC_D':
                    $estadoClc = " AND ecl_id BETWEEN 17 AND 18 ";
                    break;
                case 'EC_E':
                    $estadoClc = " AND ecl_id = 19 ";
                    break;
                case 'EC_F':
                    $estadoClc = " AND ecl_id = 20 ";
                    break;
                case 'EC_G':
                    $estadoClc = " AND ecl_id = 21 ";
                    break;
                // En teoría... jamás debería llegar aquí
                default:
                    $estadoClc = " AND ecl_id = 666 ";
                    break;
            }            
        }

        // Si quieren filtrar por UR
        $filtroUR = "";
        if ($ur != "") {
            $filtroUR = " AND CONCAT(gpo_id, ur_id) = " . $ur . " ";
        }

        // El texto les permitirá filtrar por cualquier texto contenido en:
        // clave de financiamiento
        // clave interbancaria
        // concepto
        // importe
        $filtroTexto = "";
        if ($buscar != "") {
            $buscar = trim($buscar);
            $filtroTexto = " AND ( bur_id LIKE '%$buscar%' OR clc_concepto LIKE '%$buscar%' ) ";
        }

        // Marcos: 11/mayo/2018
        // Delimitamos específicamente los estados de CLC a mostrar, para que si crean otro
        // no se incluya autmáticamente al ser mayor o igual a 7
        $where = " ( ( ecl_id BETWEEN 7 AND 21 ) AND MONTH( clc_fechaEnvio ) = " . $msc . $tipoClc . $estadoClc .  $filtroUR . $filtroTexto . " ) ";

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        if ( $soloGrupoCLCs == "0" && $paginaActual == "" ) {
        
            // Determinamos cuántas páginas utilizaremos para mostrar el catálogo solicitado
            // -----------------------------------------------------------------------------------------------------------------------
            $this->conectar();
            $this->setSql( "
                SELECT COUNT(*) AS total FROM 
                (
                    SELECT clc_id 
                    FROM 
                        ( 
                            SELECT c.* FROM scp_Clc c
                            LEFT JOIN scp_Presupuestal p USING( gpo_id, ur_id, clc_id )         
                            WHERE $where GROUP BY gpo_id, ur_id, clc_id 
                        ) base
                    JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento".APP_EJERCICIO." USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    INNER JOIN ".BD_SINPRES.".cat_SuborigenRecurso".APP_EJERCICIO." USING( cff_id, fte_id, sft_id, ori_id, sor_id)
                    JOIN ".BD_SINPRES.".cat_TipoRecurso USING ( ter_id )
                    JOIN cat_ModoPagoClc USING( mpc_id ) "
                    . $oriRec . "           
                    GROUP BY gpo_id, ur_id, clc_id
                ) AS o;"
            );

            $this->consultar();
            $this->desconectar();
            
            $row = $this->extraer();
            $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);
        } else {
            $nTotalPages = 0;
        }

        // Marcos: 10/mayo/2016
        // Si es la consulta normal para mostrar la lista en pantalla de vinculación por clc ...
        if ($soloGrupoCLCs == "0") {
            $campos = "tcl_id AS tcl, clc_id AS clc, LEFT(  MAX(ter_nombre), 1 ) AS ori, MAX(cfi_clave) as cf, bur_id as ci,
                clc_concepto AS con, ecl_id as edo, mpc_nombre AS mp, ( clc_percepcion - clc_retencion ) as imp,
                CONCAT( gpo_id, ur_id ) AS ur";
        // Si piden sólo la lista de CLCs a incluir en el reporte... 
        } else {
            $campos = "GROUP_CONCAT( DISTINCT CONCAT( clc_id ) ORDER BY clc_id ) AS clc";
        }


        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $sql = "SELECT $campos 
            FROM 
                ( 
                    SELECT c.* FROM scp_Clc c
                    LEFT JOIN scp_Presupuestal p USING( gpo_id, ur_id, clc_id )         
                    WHERE $where GROUP BY gpo_id, ur_id, clc_id 
                ) base
            JOIN enc_ClaveFinanciamiento USING(ecf_id)
            JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento".APP_EJERCICIO." USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN ".BD_SINPRES.".cat_SuborigenRecurso".APP_EJERCICIO." USING( cff_id, fte_id, sft_id, ori_id, sor_id)
            JOIN ".BD_SINPRES.".cat_TipoRecurso USING ( ter_id )
            JOIN cat_ModoPagoClc USING( mpc_id )
            LEFT JOIN scp_Presupuestal p USING( gpo_id, ur_id, clc_id ) "
            . $oriRec . "
            GROUP BY gpo_id, ur_id, clc_id 
            ORDER BY clc_id, gpo_id, ur_id" . 
            (($soloGrupoCLCs == "0") ? " LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA : "" ) . 
            "";

        // Si quieren la lista de CLCs, va algo antes y después de ésta consulta
        if ($soloGrupoCLCs != "0") {
            $sql = "SELECT GROUP_CONCAT( DISTINCT CONCAT( clc ) ORDER BY clc ) AS clc FROM ( " . $sql . " ) AS base;";
        } else {
            $sql .= ";";
        }
        
        $this->conectar( );
        $this->setSql($sql);

        $this->consultar();
        $this->desconectar();
        
        if ($soloGrupoCLCs == "0") {
            if ($this->getNumeroRegistros() == 0) {
                echo json_encode(
                    array(
                        array('tcl' => '0', 'clc' => '', 'ori' => '', 'cf' => '', 'ci' => '', 'con' => '', 'ecl' => '', 'imp' => '', 'mp' => '', 'mp' => '', 'ur' => ''),
                        array('tcl' => '',  'clc' => '', 'ori' => '', 'cf' => '', 'ci' => '', 'con' => '', 'ecl' => '', 'imp' => '', 'mp' => '', 'mp' => '', 'ur' => '')
                ) 
            );
            } else {
                $data = array(array('tcl' => $nTotalPages, 'clc' => '', 'ori' => '', 'cf' => '', 'ci' => '', 'con' => '', 'ecl' => '', 'imp' => '', 'mp' => '', 'mp' => '', 'ur' => ''));
                while ($row = $this->extraer()) {
                    // Convertimos ecl_id al estado que se muestra en pantalla
                    $row["edo"] = $this->dameNombreEstadoClc($row["edo"]);
                    $data[] = $row;
                }
                echo json_encode($data);
            }
        } else {
            $row = $this->extraer();
            echo $row['clc'];
        }
        
    }


    // Marcos: 11/mayo/2018
    // Como los estados de la clc definen "rangos" de estados y se les asigna un nombre
    // específico para mostrar en pantalla... pasamos el ecl_id de la base de datos al
    // estado que le corresponde
    // Marcos: 08/junio/2018
    // Nueva definición de Israel en el ticket #304875
    // Marcos: 14/junio/2018
    // Ajuste solicitado verbalmente por Israel, que para los estados 17 y 18 diga B
    public function dameNombreEstadoClc($ecl_id) {
        switch ($ecl_id) {
            case 7: 
            case 8: 
            case 9:
                return "G1";
                break;
            case 10: 
            case 11: 
            case 12: 
            case 13: 
            case 14: 
            case 15:
                return "G2";
                break;
            case 16:
                return "16";
                break;
            case 17: 
            case 18:
                return "B";
                break;            
            // cuando no entran en cualquier supuesto anterior, regresamos su ecl_id tal cual
            default:
                return $ecl_id;
                break;
        }

    }
}
    
new dameDocumentos();