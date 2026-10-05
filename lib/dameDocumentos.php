<?php
// Si no llegaron aquí por AJAX ...
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
)) {
    echo "Poor Cyrus ...";
    return;
}

DEFINE( "LINEAS_POR_PAGINA", 30);

require_once dirname(__FILE__)."/constantes.php";
require_once dirname(__FILE__).'/AccesoDatos.php';
require_once dirname(__FILE__).'/funciones.php';

if (!isset($_SESSION)) {
    session_start();
}

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
        
        $tdf = ( ( isset( $this->prm->tdf ) && $this->prm->tdf != "" ) ? $this->prm->tdf : '' );
        $rfc = ( ( isset( $this->prm->rfc ) && $this->prm->rfc != "" ) ? $this->prm->rfc : '' );
        $ref = ( ( isset( $this->prm->ref ) && $this->prm->ref != "" ) ? $this->prm->ref : '' );
        $del = ( ( isset( $this->prm->del ) && $this->prm->del != "" ) ? $this->prm->del : '' );
        $al  = ( ( isset( $this->prm->al  ) && $this->prm->al  != "" ) ? $this->prm->al  : '' );
        $ed  = ( ( isset( $this->prm->ed  ) && $this->prm->ed  != "" ) ? $this->prm->ed  : '' );
        $id  = ( ( isset( $this->prm->id  ) && $this->prm->id  != "" ) ? $this->prm->id  : '' );
        $ih  = ( ( isset( $this->prm->ih  ) && $this->prm->ih  != "" ) ? $this->prm->ih  : '' );
        $fr  = ( ( isset( $this->prm->fr  ) && $this->prm->fr  != "" ) ? $this->prm->fr  : '' );
        $paginaActual = ( ( isset( $this->prm->p ) && $this->prm->p != "0" ) ? $this->prm->p : "" );
        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = ( ( $paginaActual - 1 ) * LINEAS_POR_PAGINA );
        }

        $where = "";
        $parametros = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']];
        // Para el filtro por tipo de documento fuente
        // -------------------------------------------------------------------------------------------
        if ($tdf != "") {
            $where .= ' AND tdf_id = ? ';
            array_push($parametros, $tdf);
        }

        // Para filtrar documentos sólo de fondo rotatorio
        // -------------------------------------------------------------------------------------------
        if ($fr != "") {
            $where .= ' AND bdo_fondoRotatorio = ? ';
            array_push($parametros, $fr);
        }

        // Para filtrar por rfc del emisor
        // -------------------------------------------------------------------------------------------
        if ($rfc != "") {
            $where .= " AND ( d.rfc_id = ? ) ";
            array_push($parametros, $rfc);
        }

        // Para buscar por referencia
        // -------------------------------------------------------------------------------------------
        if ($ref != "") {
            // Si es una petición de la vinculación por CLC, es uno o más documentos y usamos IN
            if (substr( $ref, 0, 1 ) == "*" ) {
                $where .= " AND ( bdo_id IN ( ? ) )";
                array_push($parametros, substr($ref, 1));
            // Si es comportamiento normal, usamos LIKE
            } else {
                $where .= " AND ( bdo_referencia like ? ) ";
                array_push($parametros, '%' . $ref . '%');
            }
        }

        // Para filtrar por fecha DEL
        // -------------------------------------------------------------------------------------------
        if (trim( $del ) != "" ){
            $where .= " AND bdo_fechaDocumento >= ? ";
            array_push($parametros, $this->date_to_mysqldate($del));
        }

        // Para filtrar por fecha AL
        // -------------------------------------------------------------------------------------------
        if (trim( $al ) != "" ){
            $where .= " AND bdo_fechaDocumento <= ? ";
            array_push($parametros, $this->date_to_mysqldate($al));
        }

        // Para filtrar por importe del documento DESDE ...
        // -------------------------------------------------------------------------------------------
        if (trim( $id ) != "" ){
            $where .= " AND bdo_percepcionPresupuestal >= ? ";
            array_push($parametros, $id);
        }
        // Para filtrar por importe del documento HASTA ...
        // -------------------------------------------------------------------------------------------
        if (trim( $ih ) != "" ){
            $where .= " AND bdo_percepcionPresupuestal <= ? ";
            array_push($parametros, $ih);
        }

        // Para filtrar el status del documento:
        // 1 = documento sin devengar o parcialmente devengados
        // 2 = totalmente devengado
        // 3 = todos los documentos SIN cancelados
        // 4 = todos los documentos INCLUYENDO cancelados
        // 5 = SIN POLIZA
        // POR DEFAULT SERA 1
        // -------------------------------------------------------------------------------------------

        // Si venimos desde la pantalla de vinculación por clc, mostramos todos y por lo tanto no hacemos ésta validación
        if (substr($ref, 0, 1) != "*") {
            if (trim($ed) != "") {
                switch ($ed) {
                    case '1':
                        // Si es sin devengar o parcialmente devengados
                        $where .= " AND bdo_estadoDevengo  <> 3 AND sts_id = 1 ";
                        break;
                    case '2':
                        // Si es totalmente devengados
                        $where .= " AND bdo_estadoDevengo = 3 AND sts_id = 1 ";
                        break;
                    case '3':
                        // Si es todos SIN cancelados
                        $where .= " AND sts_id = 1 ";
                        break;
                    case '5':
                        // SIN POLIZA
                        $where .= " AND poliza IS NULL ";
                        break;
                    case '4':
                    default:
                        // Si es todos los documentos entonces NO hacemos nada ...
                        break;
                }
            }
        }

        // Marcos: 25/mayo/2015
        // agregados los campos tpa y mf ( total de percepciones PRESUPUESTALES autorizadas, y la máxima fecha o la fecha más reciente en que se autorizó la última de ellas)
        // lo anterior para permitir a las URs consultar visualmente la diferencia entre el importe del documento y el importe totalmente afectado
        // ( sólo consideramos lo presupuestal -checado con Israel- )

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        if ($paginaActual == "") {

            // Determinamos cuántas páginas utilizaremos para mostrar el catálogo solicitado
            // -----------------------------------------------------------------------------------------------------------------------
            $this->conectar();

            // ESTO ESTABA despues de cat_BancoDocumentos d
            // INNER JOIN scp_Presupuestal p USING(gpo_id,ur_id,bdo_id) INNER JOIN scp_Afectacion a USING( gpo_id, ur_id, afe_id )           
            $this->setSql( "
                SELECT
                    COUNT(*) AS total
                FROM cat_BancoDocumentos d
                WHERE gpo_id = ?
                    AND ur_id = ? " .
                    $where . ";"
            );
            $this->consultar($parametros);
            $this->desconectar();

            $row = $this->extraer();
            $nTotalPages = ceil( $row['total'] / LINEAS_POR_PAGINA );
        } else {
            $nTotalPages = 0;
        }

        // Regla 07/jun/2015 checado con Israel
        // Cuando se trate de documentos que vayan a utilizarse sólo en afectaciones contables, el importe debe ir en bdo_PercepcionContable
        // lo que hará esta consulta es checar si bdo_importe vale 0.   Si no, lo pone.
        // Si vale 0, hace la operación de ( percepciones - retenciones ) y pone dicho valor

        // Marcos: 10/julio
        // Agregamos el campo bdo_rectificaciones para saber si el documento ha sido rectificado

        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $this->conectar();

        // Marcos: 17/julio
        // Adicionamos la facilidad de cerrar documentos sólo con afectaciones contables

        // Incluye tablas del PRESUPUESTO, descomentarla cuando ya queden
        // AQUI        
        $this->setSql( "
            SELECT
                gpo_id, ur_id, id, t, r, f, e, i, c, s, x, o, v, fr, rct,
                0 AS tpa,
                '' AS mf,
                pp, pc, rc
            FROM (
                SELECT
                    d.gpo_id, d.ur_id, bdo_id AS id, tdf_id AS t, bdo_referencia AS r, DATE_FORMAT( bdo_fechaDocumento, '%d-%m-%y' ) AS f,
                    d.rfc_id AS e,
                    bdo_percepcionPresupuestal AS i,
                    bdo_concepto AS c, bdo_estadoDevengo AS s, sts_id AS X, bdo_origen AS o, bdo_estadoSat AS v, bdo_fondoRotatorio AS fr, bdo_rectificaciones AS rct,
                    0 AS ncp_importe,
                    0 AS ncc_importe,
                    0 AS afe_id,
                    bdo_percepcionPresupuestal as pp,
                    bdo_percepcionContable as pc,
                    bdo_retencionContable as rc
                FROM cat_BancoDocumentos d
                WHERE gpo_id = ?
                    AND ur_id = ? " .
                    $where . "
                GROUP BY bdo_id, afe_id
            ) AS o
            GROUP BY id
            HAVING gpo_id IS NOT NULL
            LIMIT " . $nInicial . ", " . LINEAS_POR_PAGINA . ";"
        );

        $this->consultar($parametros);
        $this->desconectar();

        $nTotalRegistros = $this->getNumeroRegistros();
        if ($nTotalRegistros == 0) {
            echo json_encode(
                array(
                    array('id'=>'0', 't'=>'', 'r'=>'', 'f'=>'', 'e'=>'', 'i'=>'', 'c'=>'', 's'=>'', 'x'=>'', 'o'=>'', 'v'=>'', 'tpa'=>'', 'mf'=>'', 'fr'=>'', 'rct'=>'', 'pp'=>'', 'pc'=>'', 'rc'=>''),
                    array('id'=>'', 't'=>'', 'r'=>'', 'f'=>'', 'e'=>'', 'i'=>'', 'c'=>'', 's'=>'', 'x'=>'', 'o'=>'', 'v'=>'', 'tpa'=>'', 'mf'=>'', 'fr'=>'', 'rct'=>'', 'pp'=>'', 'pc'=>'', 'rc'=>''),
                )
            );
        } else {
            $data = array(array('id'=>$nTotalPages, 't'=>'', 'r'=>'', 'f'=>'', 'e'=>'', 'i'=>'', 'c'=>'', 's'=>'', 'x'=>'', 'o'=>'', 'v'=>'', 'tpa'=>'', 'mf'=>'', 'fr'=>'', 'rct'=>'', 'pp'=>'', 'pc'=>'', 'rc'=>''));
            while ($row=$this->extraer()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
    }
}
new dameDocumentos();