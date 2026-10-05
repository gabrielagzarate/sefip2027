<?php
// Si no llegaron aquí por AJAX ...
if (!( isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])
    === 'xmlhttprequest' )) {
    echo "Poor Cyrus ...";
    return;
}

DEFINE("LINEAS_POR_PAGINA", 60);

require_once dirname(__FILE__)."/constantes.php";
require_once dirname(__FILE__).'/AccesoDatos.php';

if (!isset($_SESSION)) {
    session_start();
}

class dameLibroDiario extends AccesoDatos
{
    public $prm;

    public function __construct()
    {

        parent::__construct();

        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if (!empty($_POST)) $this->prm = json_decode(json_encode($_POST));
        else $this->prm = new stdClass();

        $msc_id = ( ( isset($this->prm->m) ) ? $this->prm->m : '99' );
        $tipo = ( ( isset($this->prm->t) ) ? $this->prm->t : 'tp_D' );
        $orden = ( ( isset($this->prm->o) ) ? $this->prm->o : 'or_N' );
        $numero = ( ( isset($this->prm->n) && $this->prm->n != "" ) ? $this->prm->n
                : 0 );
        $ue = ( ( isset($this->prm->ue) && $this->prm->ue != "" ) ? $this->prm->ue
                : "" );
        $usuario = ( ( isset($this->prm->u) ) ? $this->prm->u : "" );
        $textoBuscar = ( ( isset($this->prm->tb) ) ? $this->prm->tb : "" );
        $paginaActual = ( ( isset($this->prm->p) && $this->prm->p != "0" ) ? $this->prm->p
                : "" );
        $polizasOrden = ( ( isset($this->prm->po) ) ? $this->prm->po : "1" );   // Por default incluimos las pólizas que sólo contengan cuentas de orden
        $rfc = ( ( isset($this->prm->rfc) ) ? $this->prm->rfc : '' );

        // Marcos: 06/abr/2017
        // Se agrega el filtro para Agrupación de Póliza Global
        $apg = ( ( isset($this->prm->apg) ) ? $this->prm->apg : '' );

        // Para la vinculación ( pdx_*_origen )
        $clc_v = trim(( ( isset($this->prm->clc_v) ) ? $this->prm->clc_v : ''));
        $afe_v = trim(( ( isset($this->prm->afe_v) ) ? $this->prm->afe_v : ''));
        $cmp_v = trim(( ( isset($this->prm->cmp_v) ) ? $this->prm->cmp_v : ''));
        $bdo_v = trim(( ( isset($this->prm->bdo_v) ) ? $this->prm->bdo_v : ''));

        // 2017: Se agrega el filtro por ejercicio
        $eje_v = trim(( ( isset($this->prm->eje_v) ) ? $this->prm->eje_v : ''));

        // Para la vinculación ( pdx_*_vinculacion )
        $clc_o = trim(( ( isset($this->prm->clc_o) ) ? $this->prm->clc_o : ''));
        $afe_o = trim(( ( isset($this->prm->afe_o) ) ? $this->prm->afe_o : ''));
        $cmp_o = trim(( ( isset($this->prm->cmp_o) ) ? $this->prm->cmp_o : ''));
        $bdo_o = trim(( ( isset($this->prm->bdo_o) ) ? $this->prm->bdo_o : ''));

        $vinClc = trim(( ( isset($this->prm->vinClc) ) ? $this->prm->vinClc : '0'));

        // La referencia sólo es una
        $ref_v = trim(( ( isset($this->prm->ref_v) ) ? $this->prm->ref_v : ''));
        $ecf   = trim(( ( isset($this->prm->ecf) ) ? $this->prm->ecf : ''));
        $andOr = trim(( ( isset($this->prm->andOr) ) ? $this->prm->andOr : '0'));
        $andOr = ( $andOr == "0" ? "AND" : "OR" );

        $filtroTexto = trim(( ( isset($this->prm->txt) ) ? $this->prm->txt : ''));

        // Bandera para indicar si pidieron filtros por vinculación, ya que en éstos casos
        // NO limitamos por tipo de póliza
        if ($clc_v != "" || $afe_v != "" || $cmp_v != "" || $bdo_v != "" || $clc_o
            != "" || $afe_o != "" || $cmp_o != "" || $bdo_o != "" || $ref_v != ""
            || $eje_v != "") {
            $bolPidieronVinculacion = true;
        } else {
            $bolPidieronVinculacion = false;
        }

        // Si pidieron el bdo_v o bdo_o ( documento fuente ) por referencia, primero determinamos
        // su ID
        if ($vinClc == "0") {
            if ($bdo_o != "" || $bdo_v != "") {
                $this->conectar();

                // Obtenemos el de vinculación
                // total es para asegurarnos que nos regrese un registro
                $this->setSql("SELECT bdo_id, count(*) AS total FROM cat_BancoDocumentos
                    WHERE gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND ur_id = ".$_SESSION[_S]['clasAdm-ur']."
                        AND TRIM( bdo_referencia ) = '".$bdo_v."';"
                );
                $this->consultar();
                $row = $this->extraer();
                if ($row['total'] > 0) {
                    $bdo_v = $row['bdo_id'];
                }

                // Obtenemos el de origen
                // total es para asegurarnos que nos regrese un registro
                $this->setSql("SELECT bdo_id, count(*) AS total FROM cat_BancoDocumentos
                    WHERE gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND ur_id = ".$_SESSION[_S]['clasAdm-ur']."
                        AND TRIM( bdo_referencia ) = '".$bdo_o."';"
                );
                $this->consultar();
                $row = $this->extraer();
                if ($row['total'] > 0) {
                    $bdo_o = $row['bdo_id'];
                }
            }
        }

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != "") {
            $nInicial = ( ( $paginaActual - 1 ) * LINEAS_POR_PAGINA );
        }

        // El orden que pueden escoger es: or_N: por numero de póliza   or_F: por fecha   or_I: por importe
        // -------------------------------------------------------------------------------------------
        switch ($orden) {
            case 'or_F':
                $order = ' ORDER BY pma_fecha';
                break;
            case 'or_I':
                $order = ' ORDER BY pma_debe';
                break;
            case 'or_U':
                $order = ' ORDER BY usr_usuario';
                break;
            default:
                if ($vinClc == "0") {
                    $order = ' ORDER BY pot_id, prf_id, pma_id, m.ue_id';
                } else {
                    $order = ' ORDER BY pot_id, prf_id, numero, ue';
                }
                break;
        }

        $where = "";

        // Si estàn pidiendo ver sólo una ue ... filtramos
        // -------------------------------------------------------------------------------------------
        if ($ue != "" && $ue != "%") {
            $where = " AND m.ue_id = ".$ue." ";
        }

        // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
        // -------------------------------------------------------------------------------------------
        $opcion = substr($tipo, 0, 2);
        $valor = substr($tipo, 3, 1);
        switch ($opcion) {
            case "tp":
                // Si filtran por vinculacion presupuestal, no pelamos éste filtro
                if ($bolPidieronVinculacion == false) {
                    $where .= " AND pot_id = '".$valor."' ";
                }
                break;
            case "mc":
                // Si es de pagos a terceros, hay una validación extra
                if ($valor == "O") {
                    $where .= " AND ( mmc_id = 'P' AND tmc_id = 'C' ) ";
                    // Y por lo tanto el Pagado Pagado normal también
                } else if ($valor == "P") {
                    $where .= " AND ( mmc_id = '".$valor."' AND tmc_id <> 'C' ) ";
                } else {
                    $where .= " AND mmc_id = '".$valor."' ";
                }
                break;
            case "st":
                $where .= " AND epl_id = '".$valor."' ";
                break;
        }

        // Para el filtro por nùmero de pòliza, mostramos las que sean iguales o mayores al numero pedido
        // -------------------------------------------------------------------------------------------
        if ($numero != 0) {
            $where .= ' AND pma_id >= "'.$numero.'" ';
        }

        // Si desean mostrar sólo las pólizas que incluyan determinado texto en el concepto ...
        // -------------------------------------------------------------------------------------------
        if (trim($textoBuscar) != "") {
            $where .= " AND pma_concepto like '%".$textoBuscar."%' ";
        }

        // Marcos: 06/abr/2017
        // Se adiciona el filtro por Agrupación de Póliza Global
        // -------------------------------------------------------------------------------------------
        if (trim($apg) != "") {
            $where .= " AND apg_id = '".$apg."' ";
        }

        // Para filtrar si un usuario en especial registro o modifico una póliza
        // -------------------------------------------------------------------------------------------
        if (trim($usuario) != "") {
            $where .= " AND ( m.usr_id = '".$usuario."' ) ";
        }

        // Limitamos mostrar o no aquellas pólizas que sólo contengan cuentas de orden
        if ($polizasOrden == "0") {
            $where .= " AND  apg_id != 'ORD' ";
        }

        // Para filtrar por RFC (en las pólizas del devengado se guarda el RFC en el maestro)
        // -------------------------------------------------------------------------------------------
        if ($rfc != "") {
            $where .= ' AND rfc_id = "'.$rfc.'" ';
        }

        // Marcos: 05/abr/2016
        // Adicionamos filtros para los campos *_origen y *_vinculacion de la tabla
        // cg_PolizaDetalleAuxiliar
        // Marcos: 23/mayo/2016
        // sólo validamos el campo ORIGEN, VINCULACION ya no

        if ($bolPidieronVinculacion == true) $where .= " AND (";

        if ($vinClc == "0") {

            if ($clc_v != "")
                    $where .= " $andOr d.pdx_clc_id_vinculacion = '$clc_v' ";
            if ($afe_v != "")
                    $where .= " $andOr d.pdx_afe_id_vinculacion = '$afe_v' ";
            if ($cmp_v != "")
                    $where .= " $andOr d.pdx_cmp_id_vinculacion IN ( $cmp_v )";
            if ($bdo_v != "")
                    $where .= " $andOr d.pdx_bdo_id_vinculacion = '$bdo_v' ";

            if ($clc_o != "")
                    $where .= " $andOr d.pdx_clc_id_origen = '$clc_o' ";
            if ($afe_o != "")
                    $where .= " $andOr d.pdx_afe_id_origen = '$afe_o' ";
            if ($cmp_o != "")
                    $where .= " $andOr d.pdx_cmp_id_origen IN ( $cmp_o ) ";
            if ($bdo_o != "")
                    $where .= " $andOr d.pdx_bdo_id_origen = '$bdo_o' ";

            if ($eje_v != "") $where .= " $andOr d.pdx_ejercicio = '$eje_v' ";

            // Marcos: 25/mayo/2016
            // Cuando el libro diario se llame desde la pantalla de vinculación por CLC, debemos
            // enviará en bdo_origen, y debemos buscar en los agrupadores de documentos de cada momento
            // contable para llegar correctamente a las pólizas
        } else {
            // $bdo_o = str_replace( ",", "','", $bdo_o );
            // if( $bdo_o != "" ) $where .= " AND d.pdx_agrupador IN ( '$bdo_o' ) ";
            // Nop, buscaremos por clc_id_origen.   Israel revisará que incluya todo menos el comprometido
            // ya que el comprometido usa una consulta aparte
            if ($clc_o != "") $where .= " AND d.pdx_clc_id_origen = $clc_o ";
        }

        // El campo de REFERENCIA, si lo escriben tal y como se imprime... es tdf_id-tdf_referencia
        // así que lo separamos
        if ($ref_v != "") {
            $arrDocto = explode("-", $ref_v);
            // DEBE ser un array de 2 posiciones.   En caso contrario dejamos vacío
            if (count($arrDocto) == 2) {
                $where .= " $andOr d.tdf_id = '".$arrDocto[0]."' $andOr d.pdx_referencia = '".$arrDocto[1]."' ";
            }
        }

        if ($bolPidieronVinculacion == true) $where .= " )";

        // Marcos: 21/marzo/2017
        // Adicionamos el filtro por texto en el cuadro de busqueda
        if ($filtroTexto != "") {
            $where = " AND m.pma_concepto LIKE '%".$filtroTexto."%' ";
        }

        // Aunque hasta abril del 2016 el mes era campo fijo, si pedimos filtros por
        // vinculación presupuestal, no pelaremos el mes
        if ($bolPidieronVinculacion == false) {
            $where .= ' AND msc_id = "'.$msc_id.'" ';
        } else {
            // Sin embargo, si piden de la vinculación sólo el ejercicio, y todo lo demás está vacío
            // entonces sí lo activamos
            if ($clc_v == "" || $afe_v == "" || $cmp_v == "" || $bdo_v == "" || $clc_o
                == "" || $afe_o == "" || $cmp_o == "" || $bdo_o == "" || $ref_v == ""
                || $eje_v != "") {
                $where .= ' AND msc_id = "'.$msc_id.'" ';
            }
        }

        // Para corregir un error en la lógica del armado
        $where = str_replace("AND ( AND ", "AND ( ", $where);
        $where = str_replace("AND ( OR ", "AND ( ", $where);

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        if ($paginaActual == "") {

            $sql = "SELECT msc_id, pot_id, prf_id, pma_id FROM cg_PolizaMaestro m
                    JOIN cg_PolizaDetalleAuxiliar d USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND ur_id = ".$_SESSION[_S]['clasAdm-ur'].
                $where."
                    GROUP BY m.msc_id, m.pot_id, m.prf_id, m.pma_id, m.ue_id";

            if ($vinClc == "1") {

                $strBusca = " AND ( d.pdx_clc_id_origen = $clc_o ";

                $sqlCmp = str_replace(
                    $strBusca,
                    " AND ( d.pdx_cmp_id_origen IN ( $cmp_o ) AND mmc_id = 'C' ",
                    $sql);

                $sql .= " UNION ALL ".$sqlCmp;
            }

            $sql = "SELECT COUNT(*) AS total FROM
                ( ".
                $sql.
                " ) d;";

            // Determinamos cuántas páginas utilizaremos para mostrar el catálogo solicitado
            // -----------------------------------------------------------------------------------------------------------------------
            $this->conectar();
            $this->setSql($sql);
            $this->consultar();
            $this->desconectar();

            $row = $this->extraer();
            $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);
        } else {

            $nTotalPages = 0;
        }

        // Marcos: cuando se trate de la UR 116, SIN unidades ejecutoras, tendrá un comportamiento especial
        // diferente del estándar para todas las URs, ya que es un usuario de tesorería
        if (( $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'] == "116" )
            &&
            (!isset($_SESSION[_S]['clasAdm-ue']) || ( isset($_SESSION[_S]['clasAdm-ue'])
            && $_SESSION[_S]['clasAdm-ue'] == "005" ) )
        ) {
            $campoVinculacion = "max(d.pdx_clc_cereco_vinculacion)";
        } else {
            $campoVinculacion = "''";
        }

        $cvefinSql = (( $ecf != '' ) ? " AND ecf_id = ".$ecf." " : "" );

        // Marcos: 26/mayo/2016
        // Para el comprometido, volvemos a ejecutar la misma consulta comparándo ahora sobre
        // los cmp_id, pero limitando al momento contable del comprometido.
        $sql = "SELECT m.pot_id, m.prf_id, m.pma_id AS numero, DAY( m.pma_fecha ) AS dia,
            IF( msc_id = 0 or msc_id = 13, msc_id, MONTH( m.pma_fecha ) ) AS mes,
            m.pma_concepto AS c, m.pma_debe AS d, m.pma_haber AS h,
            CONCAT( m.mmc_id, m.tmc_id ) AS mc, ".$campoVinculacion." AS v, m.epl_id AS e, m.ue_id AS ue, m.apg_id AS apg, ems_id AS em, usr_usuario AS u
            FROM cg_PolizaMaestro m
                LEFT JOIN cg_PolizaDetalleAuxiliar d USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                LEFT JOIN cg_MesContable USING( gpo_id, ur_id, msc_id )
                LEFT JOIN cat_Usuario u ON ( m.usr_id = u.usr_id )
            WHERE m.gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND m.ur_id = ".$_SESSION[_S]['clasAdm-ur'].
            $where.$cvefinSql."
            GROUP BY m.pot_id, m.prf_id, m.pma_id, m.ue_id
            ";

        if ($vinClc == "1") {

            $strBusca = " AND ( d.pdx_clc_id_origen = $clc_o ";

            $sqlCmp = str_replace(
                $strBusca,
                " AND ( d.pdx_cmp_id_origen IN ( $cmp_o ) AND mmc_id = 'C' ",
                $sql);

            $sql .= " UNION ALL ".$sqlCmp;
        }

        $sql .= $order." LIMIT ".$nInicial.", ".LINEAS_POR_PAGINA.";";

        // Cambio 11-marzo-2015
        // Marcos: Se fusionan los campos mmc_id + tmc_id.
        // tmc_id indica si es un movimiento N= Normal o de C=Cancelación.   Si es normal sólo mostraremos mmc_id en pantalla
        // si es de cancelación, entonces lo mostraremos en rojo y con un comentario (title) para que sepan que corresponde
        // a la cancelación de un compromiso realizado en diferente mes
        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        // Marcos: 21/abr/2016
        // Se le puso LEFT JOIN a cg_PolizaDetalleAuxiliar (tenia JOIN) porque "blanquearon" polizas manualmente
        // aquí en informática finanzas, y sin el LEFT no los muestra.   Se les muestra para que reutilicen el número
        $this->conectar();
        $this->setSql($sql);

        $this->consultar();
        $this->desconectar();


        if ($this->getNumeroRegistros() == 0) {
            echo json_encode(
                array(
                    array('pot_id' => 0, 'prf_id' => '', 'numero' => '', 'd' => '',
                        'c' => '', 'i' => '', 'h' => '', 'mc' => '', 'v' => '', 'e' => '',
                        'ue' => '', 'apg' => '', 'em' => '', 'u' => '', 'mes' => ''),
                    array('pot_id' => '', 'prf_id' => '', 'numero' => '', 'd' => '',
                        'c' => '', 'i' => '', 'h' => '', 'mc' => '', 'v' => '', 'e' => '',
                        'ue' => '', 'apg' => '', 'em' => '', 'u' => '', 'mes' => '')
                )
            );
        } else {
            $data = array(array('pot_id' => $nTotalPages, 'prf_id' => '', 'numero' => '',
                    'd' => '', 'c' => '', 'i' => '', 'h' => '', 'mc' => '', 'v' => '',
                    'e' => '', 'ue' => '', 'apg' => '', 'em' => '', 'u' => ''),);
            while ($row = $this->extraer()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
    }
}
new dameLibroDiario();