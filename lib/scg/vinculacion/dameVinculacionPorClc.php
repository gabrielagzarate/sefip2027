<?php

// Si no llegaron aquí por AJAX ...
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    echo "Poor Cyrus ...";
    return;
}

DEFINE("LINEAS_POR_PAGINA", 60);
require_once dirname(__FILE__) . "/../../constantes.php";
require_once dirname(__FILE__) . '/../../AccesoDatos.php';

if (!isset($_SESSION)) {
    session_start();
}

// Marcos: 10/mayo/2016
// Para emitir el reporte de CLC y sus respectivas pólizas, usaremos esta misma consulta
// Para no cuidad en más de un lugar el código si es que se modifica/adiciona/elimina algo
// sólo con un nuevo parámetro para que regrese en un sólo campo separado por comas la lista
// de CLCs a considera

// Marcos: 08/feb/2018
// El campo cso_id de enc_ClaveFinanciamiento cambipo ahora es cff_id y va al principio

class dameDocumentos extends AccesoDatos
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

        $msc = ((isset($this->prm->msc) && $this->prm->msc != "") ? $this->prm->msc : '');
        $tip = ((isset($this->prm->tip) && $this->prm->tip != "" && $this->prm->tip != "0") ? $this->prm->tip : '');
        $edo = ((isset($this->prm->edo) && $this->prm->edo != "" && $this->prm->edo != "0") ? $this->prm->edo : '');
        $paginaActual = ((isset($this->prm->p) && $this->prm->p != "0") ? $this->prm->p : "");

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
            } elseif ($tip == "OA" || $tip == "OB") {
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
                case "3":
                    $estadoClc = " AND ( ( ecl_id BETWEEN 3 AND 16 ) OR ecl_id = 19 )";
                    break;
                default:
                    $estadoClc = " AND ecl_id = " . $edo . " ";
                    break;
            }
        }

        // Marcos: 31/marzo/2016
        // La ur 801 DEBERÁ ver las clcs de otras URs, cuando se cumplan éstas condiciones:
        // tipo clc = servicios personales ( tcl_id = 'A' )
        // subtipo de clc = aportacion patronal pensiones ( stc_id = 2 )
        // OR el tipo de ministración sea pensiones ( ing_BancoDocumentoMinistrado.tmi_id = 7 )

        // Marcos: 11/mayo/2016
        // Que dice mi mamá que siempre no, que se deshabilite ésta característica, y TODAS
        // verán solamente sus CLCs
        // Definido por Israel.
        $where = " ( gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND ur_id = " . $_SESSION[_S]['clasAdm-ur'] . "
                AND ecl_id >= 2 AND MONTH( clc_fechaEnvio ) = " . $msc . $tipoClc . $estadoClc . " ) ";

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        if ($soloGrupoCLCs == "0" && $paginaActual == "") {

            // Determinamos cuántas páginas utilizaremos para mostrar el catálogo solicitado
            // -----------------------------------------------------------------------------------------------------------------------
            $this->conectar();
            $this->setSql(
                "
                SELECT COUNT(*) AS total FROM
                (
                    SELECT clc_id
                    FROM
                        (
                            SELECT c.*, COUNT( p.cmp_id ) AS cmp_id FROM scp_Clc c
                            LEFT JOIN scp_Presupuestal p USING( gpo_id, ur_id, clc_id )
                            WHERE $where GROUP BY gpo_id, ur_id, clc_id
                        ) base
                    JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    INNER JOIN ".BD_SINPRES.".cat_SuborigenRecurso" . APP_EJERCICIO . "  USING( cff_id, fte_id, sft_id, ori_id, sor_id)
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
            $campos = "tcl_id AS tcl, clc_id AS clc, LEFT( MAX(ter_nombre), 1 ) AS ori, MAX(cfi_clave) as cf, bur_id as ci,
                clc_concepto AS con,
                case when ( ( ecl_id BETWEEN 3 AND 16 ) OR ecl_id = 19 ) then 3 else ecl_id end as edo,
                mpc_nombre AS mp, ( clc_percepcion - clc_retencion ) as imp,
                base.cmp_id AS cmp, CONCAT( gpo_id, ur_id ) AS ur";
            // Si piden sólo la lista de CLCs a incluir en el reporte...
        } else {
            // Marcos:
            // $campos = "GROUP_CONCAT( DISTINCT CONCAT( case when ( ecl_id BETWEEN 3 AND 16 ) OR ecl_id = 19 then 3/jun/ else ecl_id end ) ORDER BY clc_id ) AS clc";
            // Estaba la línea anterior, pero se necesitan clc_id, y regresaba ecl_id y pos
            // no salía nada en el reporte... regresamos la línea a como estaba el año anterior
            $campos = "GROUP_CONCAT( DISTINCT CONCAT( clc_id ) ORDER BY clc_id ) AS clc";
        }

        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $sql = "SELECT $campos
                FROM
                    (SELECT c.*, COUNT( p.cmp_id ) AS cmp_id FROM scp_Clc c
                    LEFT JOIN scp_Presupuestal p USING( gpo_id, ur_id, clc_id )
                    WHERE $where GROUP BY gpo_id, ur_id, clc_id
                    ) base
                    JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    INNER JOIN ".BD_SINPRES.".cat_SuborigenRecurso" . APP_EJERCICIO . " USING( cff_id, fte_id, sft_id, ori_id, sor_id)
                    JOIN ".BD_SINPRES.".cat_TipoRecurso USING ( ter_id )
                    JOIN cat_ModoPagoClc USING( mpc_id )
                    LEFT JOIN scp_Presupuestal p USING( gpo_id, ur_id, clc_id ) "
            . $oriRec . "
                GROUP BY gpo_id, ur_id, clc_id
                ORDER BY clc_id, gpo_id, ur_id" .
            (($soloGrupoCLCs == "0") ? " LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA : "") .
            ";";

        // Agrupamos por gpo_id + ur_id + clc_id por la 801, para que muestre correctamente las suyas
        // y las de las otras URs, porque si agrupamos sólo por CLC, se pierden cuando tengan el mismo
        // número de clc.
        // Se ordena por clc + gpo + ur para que se respete el orden de las clcs

        $this->conectar();
        $this->setSql($sql);

        $this->consultar();
        $this->desconectar();

        if ($soloGrupoCLCs == "0") {
            if ($this->getNumeroRegistros() == 0) {
                echo json_encode(
                    array(
                        array('tcl' => '0', 'clc' => '', 'ori' => '', 'cf' => '', 'ci' => '', 'con' => '', 'ecl' => '', 'imp' => '', 'mp' => '', 'ur' => '', 'edo' => ''),
                        array('tcl' => '', 'clc' => '', 'ori' => '', 'cf' => '', 'ci' => '', 'con' => '', 'ecl' => '', 'imp' => '', 'mp' => '', 'ur' => '' , 'edo' => ''),
                    )
                );
            } else {
                $data = array(array('tcl' => $nTotalPages, 'clc' => '', 'ori' => '', 'cf' => '', 'ci' => '', 'con' => '', 'ecl' => '', 'imp' => '', 'mp' => '', 'ur' => '' , 'edo' => ''));
                while ($row = $this->extraer()) {
                    $data[] = $row;
                }
                echo json_encode($data);
            }
        } else {
            $clcs = [];
            while ($row = $this->extraer()) {
               $clcs[] = $row['clc'];
            }
            echo implode(",", $clcs);
        }
    }
}

new dameDocumentos();
