<?php
require_once dirname(__FILE__).'/../../calculaPeso.php'; 

require_once dirname(__FILE__) . '/../../../lib/constantes.php';
// ------------------------------------------------------------------------------------------------------
// Las consultas para obtener todos los catálogos CONAC ( género, grupo, rubro, cuenta y subcuenta )
// ------------------------------------------------------------------------------------------------------
function dameCatalogoCONAC($seccion, $filtro)
{

    // Si mandan un filtro de búsqueda que esté activo, lo respetamos en el reporte
    $where = "";
    $sql = "";
    if (trim($filtro) != "")
            // Si es numérico el primero y último caracter... asumimos que es una cuenta contable
            if ( is_numeric( substr( $filtro, 0, 1 ) ) ) {
                $where = 'WHERE '.$sql.'_cuenta LIKE "'.$filtro.'%"';
            // si no, es porque es descripción...
            } else {
                $where = 'WHERE '.$sql.'_nombre LIKE "%'.$filtro.'%"';
            }

    switch ($seccion) {
        case "GÉNERO":

            // Si es numérico el primero y último caracter... asumimos que es una cuenta contable
            if ( is_numeric( substr( $filtro, 0, 1 ) ) ) {
                $where = 'WHERE ger_cuenta LIKE "'.$filtro.'%"';
            // si no, es porque es descripción...
            } else {
                $where = 'WHERE ger_nombre LIKE "%'.$filtro.'%"';
            }

            $sql = "SELECT ger_id AS clave, ger_nombre AS nombre,ger_descripcion as descrip,'' AS nat, '' AS str, '' AS t, '' AS p  FROM cat_Genero ".$where." ORDER BY ger_id;";
            break;
        case "GRUPO":
            // Si es numérico el primero y último caracter... asumimos que es una cuenta contable
            if ( is_numeric( substr( $filtro, 0, 1 ) ) ) {
                $where = 'WHERE cgp_cuenta LIKE "'.$filtro.'%"';
            // si no, es porque es descripción...
            } else {
                $where = 'WHERE cgp_nombre LIKE "%'.$filtro.'%"';
            }

            $sql = "SELECT cgp_cuenta AS clave, cgp_nombre AS nombre,cgp_descripcion as descrip,'' AS nat, '' AS str, '' AS t, '' AS p FROM cat_CgGrupo ".$where." ORDER BY ger_id, cgp_id;";
            break;
        case "RUBRO":
            // Si es numérico el primero y último caracter... asumimos que es una cuenta contable
            if ( is_numeric( substr( $filtro, 0, 1 ) ) ) {
                $where = 'WHERE rub_cuenta LIKE "'.$filtro.'%"';
            // si no, es porque es descripción...
            } else {
                $where = 'WHERE rub_nombre LIKE "%'.$filtro.'%"';
            }

            $sql = "SELECT rub_cuenta AS clave, rub_nombre AS nombre,rub_descripcion as descrip,'' AS nat, '' AS str, '' AS t, '' AS p  FROM cat_Rubro ".$where." ORDER BY ger_id, cgp_id, rub_id;";
            break;
        case "CUENTA":
            // Si es numérico el primero y último caracter... asumimos que es una cuenta contable
            if ( is_numeric( substr( $filtro, 0, 1 ) ) ) {
                $where = 'WHERE cgcu_cuenta LIKE "'.$filtro.'%"';
            // si no, es porque es descripción...
            } else {
                $where = 'WHERE cgcu_nombre LIKE "%'.$filtro.'%"';
            }

            $sql = "SELECT cgcu_cuenta AS clave, cgcu_nombre AS nombre,cgcu_descripcion as descrip,'' AS nat, '' AS str, '' AS t, '' AS p  FROM cat_CgCuenta ".$where." ORDER BY ger_id, cgp_id, rub_id, cgcu_id;";
            break;
        case "SUBCUENTA":
            // Si es numérico el primero y último caracter... asumimos que es una cuenta contable
            if ( is_numeric( substr( $filtro, 0, 1 ) ) ) {
                $where = 'WHERE cgsc_cuenta LIKE "'.$filtro.'%"';
            // si no, es porque es descripción...
            } else {
                $where = 'WHERE cgsc_nombre LIKE "%'.$filtro.'%"';
            }
            $sql = "SELECT cgsc_cuenta AS clave, cgsc_nombre AS nombre,cgsc_descripcion as descrip,nat_id AS nat, ecu_id AS str, tcu_id AS t, pcu_id AS p FROM cat_CgSubcuenta ".$where." ORDER BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id;";
            break;
    }

    // Ajustamos el nombre del campo si estamos en el 2012, ya que el nombre era diferente
    $sql = ( APP_EJERCICIO == "2012" ) ? str_replace("_nombre",
            "_descripcion", $sql) : $sql;
    $catalogo = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $catalogo->conectar();
    $catalogo->setSql($sql);
    $catalogo->consultar();
    $catalogo->desconectar();

    $datos = array();
    while ($rowCatalogo = $catalogo->extraer()) {
        // Repetimos 'clave' para que sea también el KEY de cada línea a imprimir... OJO: debe ser un diferenciador, ya
        // que si se duplica un KEY, "desaparecerían" líneas.
        $datos[$rowCatalogo['clave']] = array(
            'clave' => $rowCatalogo['clave'],
            'nombre' => $rowCatalogo['nombre'],
            'descrip' => $rowCatalogo['descrip'],
            'nat' => $rowCatalogo['nat'],
            'str' => $rowCatalogo['str'],
            't' => $rowCatalogo['t'],
            'p' => $rowCatalogo['p']
        );
    }
    return $datos;
}

// ------------------------------------------------------------------------------------------------------
// Obtenemos el catálogo maestro
// ------------------------------------------------------------------------------------------------------
function dameCatalogoMaestro($seccion, $filtro, $mayor, $desde, $hasta)
{

    switch ($seccion) {

        case "impTodo":
        case "expTodo":
            $sql = "SELECT CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id) AS CUENTA, cgsc_nombre AS NOMBRE
                     FROM cat_CgSubcuenta
			INNER JOIN cat_EstructuraCuenta USING(ecu_id)
			INNER JOIN cat_TipoCuenta USING(tcu_id)
                        INNER JOIN cat_PosicionCuenta USING(pcu_id)
                UNION 
                    SELECT c.cgc_cuenta AS CUENTA, c.cgc_nombre AS NOMBRE
                    FROM cg_CuentaCuarto c INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                        INNER JOIN cat_EstructuraCuenta USING(ecu_id)
			INNER JOIN cat_TipoCuenta USING(tcu_id)
			INNER JOIN cat_PosicionCuenta USING(pcu_id)
                UNION
                    SELECT q.cgq_cuenta AS CUENTA, q.cgq_nombre AS NOMBRE
                        
                    FROM cg_CuentaQuinto q INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                        INNER JOIN cat_EstructuraCuenta USING(ecu_id)
			INNER JOIN cat_TipoCuenta USING(tcu_id)
			INNER JOIN cat_PosicionCuenta USING(pcu_id)
                UNION ALL           
                SELECT ger_id AS CUENTA, ger_nombre AS NOMBRE FROM cat_Genero
                UNION ALL
                SELECT CONCAT( ger_id, '.', cgp_id ) AS CUENTA, cgp_nombre AS NOMBRE FROM cat_CgGrupo
                UNION ALL
                SELECT CONCAT( ger_id, '.', cgp_id, '.', rub_id ) AS CUENTA, rub_nombre AS NOMBRE FROM cat_Rubro
                UNION ALL
                SELECT CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id ) AS CUENTA, cgcu_nombre AS NOMBRE FROM cat_CgCuenta
                ORDER BY CUENTA;";
                $parametros = [];
            break;

        case "impActual":
        case "expActual":
            if ($filtro != "") {
                $where = ' AND $campo LIKE "%'.$filtro.'%" ';
            }else{
                $where = '';
            }
            $cuentaMayor = $mayor;
            $arrCta = explode(".", $cuentaMayor);

            // Para efectos de validación, obtenemos también cuántos elementos tra
            $elementos = count($arrCta);
            if ($elementos == 5) {
                $genero = $arrCta[0];
                $grupo = $arrCta[1];
                $rubro = $arrCta[2];
                $cuenta = $arrCta[3];
                $subcuenta = $arrCta[4];
            } else {
                echo "Favor de verificar Cuenta de MAYOR contenga puntos";
                die();
            }

            $sql = "SELECT
                        CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id) AS CUENTA, cgsc_nombre AS NOMBRE
                            FROM cat_CgSubcuenta
                                INNER JOIN cat_EstructuraCuenta USING(ecu_id)
                                INNER JOIN cat_TipoCuenta USING(tcu_id)
                                INNER JOIN cat_PosicionCuenta USING(pcu_id)
                            WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?".str_replace('$campo',
                    'cgm_nombre', $where)."
                    UNION
                        SELECT c.cgc_cuenta AS CUENTA, c.cgc_nombre AS NOMBRE
                            FROM cg_CuentaCuarto c INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                                INNER JOIN cat_EstructuraCuenta USING(ecu_id)
                                INNER JOIN cat_TipoCuenta USING(tcu_id)
                                INNER JOIN cat_PosicionCuenta USING(pcu_id)
                            WHERE m.ger_id = ? AND m.cgp_id = ? AND m.rub_id = ? AND m.cgcu_id = ? AND m.cgsc_id = ?".str_replace('$campo',
                    'c.cgc_nombre', $where)."
                    UNION
                        SELECT q.cgq_cuenta AS CUENTA, q.cgq_nombre AS NOMBRE
                            FROM cg_CuentaQuinto q INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                                INNER JOIN cat_EstructuraCuenta USING(ecu_id)
                                INNER JOIN cat_TipoCuenta USING(tcu_id)
                                INNER JOIN cat_PosicionCuenta USING(pcu_id)
                            WHERE q.ger_id = ? AND q.cgp_id = ? AND q.rub_id = ? AND q.cgcu_id = ? AND q.cgsc_id = ?".str_replace('$campo',
                    'q.cgq_nombre', $where)."
                    ORDER BY CUENTA;";
                $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $genero, $grupo, $rubro, $cuenta, $subcuenta, $genero, $grupo, $rubro, $cuenta, $subcuenta];
            break;

        case "impRango":
        case "expRango":
            $sql = "SELECT
                        CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id)  AS CUENTA, cgsc_nombre AS NOMBRE
                            FROM cat_CgSubcuenta
                                INNER JOIN cat_EstructuraCuenta USING(ecu_id)
                                INNER JOIN cat_TipoCuenta USING(tcu_id)
                                INNER JOIN cat_PosicionCuenta USING(pcu_id)
                             WHERE CONCAT( ger_id,'.' , cgp_id,'.' , rub_id,'.' , cgcu_id,'.' , cgsc_id ) BETWEEN '".$desde."' AND '".$hasta."'
                    UNION
                        SELECT c.cgc_cuenta AS CUENTA, c.cgc_nombre AS NOMBRE
                            FROM cg_CuentaCuarto c INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                                INNER JOIN cat_EstructuraCuenta USING(ecu_id)
                                INNER JOIN cat_TipoCuenta USING(tcu_id)
                                INNER JOIN cat_PosicionCuenta USING(pcu_id)
                            WHERE CONCAT( m.ger_id,'.' , m.cgp_id,'.' , m.rub_id,'.' , m.cgcu_id,'.' , m.cgsc_id ) BETWEEN '".$desde."' AND '".$hasta."'
                UNION
                        SELECT q.cgq_cuenta AS CUENTA, q.cgq_nombre AS NOMBRE
                            FROM cg_CuentaQuinto q INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                                INNER JOIN cat_EstructuraCuenta USING(ecu_id)
                                INNER JOIN cat_TipoCuenta USING(tcu_id)
                                INNER JOIN cat_PosicionCuenta USING(pcu_id)
                            WHERE CONCAT( q.ger_id,'.' , q.cgp_id,'.' , q.rub_id,'.' , q.cgcu_id,'.' , q.cgsc_id ) BETWEEN '".$desde."' AND '".$hasta."'
                ORDER BY CUENTA;";
                $parametros = [];
            break;
    }

    $catalogo = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $catalogo->conectar();
    $catalogo->setSql($sql);
    $catalogo->consultar($parametros);
    $catalogo->desconectar();

    $datos = array();
    while ($rowCatalogo = $catalogo->extraer()) {
        // Repetimos 'clave' para que sea también el KEY de cada línea a imprimir... OJO: debe ser un diferenciador, ya
        // que si se duplica un KEY, "desaparecerían" líneas.
        $datos[$rowCatalogo['CUENTA']] = array(
            'cuenta' => $rowCatalogo['CUENTA'],
            'nombre' => $rowCatalogo['NOMBRE']
        );
    }

    return $datos;
}

// ------------------------------------------------------------------------------------------------------
// Obtenemos el Plan de Cuentas
// ------------------------------------------------------------------------------------------------------
function damePlanDeCuentas($seccion, $filtro, $mayor, $desde, $hasta, $filtroUE)
{

    switch ($seccion) {

        case "planActual":
            $sql = DameSqlPlanCuentas("actual", $mayor, $desde, $hasta, $filtro,
                $filtroUE);
            break;

        case "planTodo":
            $sql = DameSqlPlanCuentas("todo", $mayor, $desde, $hasta, $filtro,
                $filtroUE);
            break;

        case "planRango":
            $sql = DameSqlPlanCuentas("rango", $mayor, $desde, $hasta, $filtro,
                $filtroUE);
            break;
    }

    $catalogo = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $catalogo->conectar();
    $catalogo->setSql($sql);
    $catalogo->consultar();
    $catalogo->desconectar();

    $datos = array();
    $n = 0;
    $NUMREG = $catalogo->getNumeroRegistros();

        $columnas = 20;


$peso = calcularPeso($NUMREG, $columnas);

if ($peso < 6 ){
    $peso =7;
}
ini_set('memory_limit', ($peso * 20)."M");
    while ($rowCatalogo = $catalogo->extraer()) {
        // Repetimos 'clave' para que sea también el KEY de cada línea a imprimir... OJO: debe ser un diferenciador, ya
        // que si se duplica un KEY, "desaparecerían" líneas.
        $datos[$rowCatalogo['CUENTA'].$rowCatalogo['ue']] = array(
            'cuenta' => $rowCatalogo['CUENTA'],
            'nombre' => $rowCatalogo['NOMBRE'],
            'nat' => $rowCatalogo['NAT'],
            'tipo' => $rowCatalogo['TIPO'],
            'est' => $rowCatalogo['ESTR'],
            'pos' => $rowCatalogo['POS'],
            'ue' =>  ( ($rowCatalogo['ue']!="")?str_pad( $rowCatalogo['ue'], 3, "0", STR_PAD_LEFT):"")
        );
    }
    return $datos;
}

// Regresamos la consulta para el Plan de Cuentas
// $tipo puede ser : actual (Solo una cuenta), "todo" o "rango"
// $filtro es el texto dentro del nombre a filtrar
// ------------------------------------------------------------------------------------------------------
function DameSqlPlanCuentas($tipo, $mayor, $desde, $hasta, $filtro, $ue)
{

    // Si estamos filtrando por cuenta ... o por rango de cuenta ...
    $filtroCta = "";
    $filtroUE = "";
    // Si estan pidiendo la seleccion actual, checamos la cuenta Mayor
    if ($tipo == "actual") {

         $cuentaMayor = $mayor;
            $arrCta = explode(".", $cuentaMayor);

            // Para efectos de validación, obtenemos también cuántos elementos tra
            $elementos = count($arrCta);
            if ($elementos == 5) {
                $genero = $arrCta[0];
                $grupo = $arrCta[1];
                $rubro = $arrCta[2];
                $cuenta = $arrCta[3];
                $subcuenta = $arrCta[4];
            } else {
                echo "Favor de verificar Cuenta de MAYOR contenga puntos";
                die();
            }

        $filtroCta = " AND ger_id = ".$genero." AND cgp_id = ".$grupo." AND rub_id = ".$rubro." AND cgcu_id = ".$cuenta." AND cgsc_id = ".$subcuenta." ";
    }

    // El filtro por UE se aplica siempre, porque puede ser un usuario de nivel UE
    if ($ue != "") {
        $filtroUE = ' AND cta_ue_id = '.$ue.' ';
    }

    if ($tipo == "rango") {
        // Si están pidiendo por rango (lo sabemos porque viene la variable $hasta )
        if ($hasta != "") {
            $filtroCta = " AND CONCAT( ger_id,'.', cgp_id,'.',  rub_id,'.',  cgcu_id,'.',  cgsc_id ) BETWEEN '".$desde."' AND '".$hasta."' ";
        }
    }

    // Si están pidiendo un filtro de texto al nombre de la cuenta ...
    $filtroNombre = "";
    if ($filtro != "") {
        $filtroNombre = " AND cta_nombre LIKE '%".trim($filtro)."%' ";
    }

    return "SELECT CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id) AS CUENTA, cgsc_nombre AS NOMBRE,
            CASE nat_id WHEN 'D' THEN 'Deudora' ELSE 'Acreedora' END AS NAT,CONCAT(LEFT(tcu_nombre,1),LOWER(SUBSTRING(tcu_nombre,2))) AS TIPO,
            CONCAT(LEFT(ecu_nombre,1),LOWER(SUBSTRING(ecu_nombre,2)))AS ESTR,CONCAT(LEFT(pcu_nombre,1),LOWER(SUBSTRING(pcu_nombre,2))) AS POS, ue
            FROM 
            ( 
            SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, '' AS ue FROM cg_PlanCuentas
            WHERE cta_gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND cta_ur_id = ".$_SESSION[_S]['clasAdm-ur']." ".$filtroCta.$filtroNombre.$filtroUE."
            GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
            ) AS base
            INNER JOIN cat_CgSubcuenta USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            iNNER JOIN cat_EstructuraCuenta USING(ecu_id)
            iNNER JOIN cat_TipoCuenta USING(tcu_id)
            iNNER JOIN cat_PosicionCuenta USING(pcu_id)
            UNION

            SELECT cgc_cuenta AS CUENTA, cgc_nombre AS NOMBRE, CASE nat_id WHEN 'D' THEN 'Deudora' ELSE 'Acreedora' END AS NAT,
            CONCAT(LEFT(tcu_nombre,1),LOWER(SUBSTRING(tcu_nombre,2))) AS TIPO,
            CONCAT(LEFT(ecu_nombre,1),LOWER(SUBSTRING(ecu_nombre,2)))AS ESTR,CONCAT(LEFT(pcu_nombre,1),LOWER(SUBSTRING(pcu_nombre,2))) AS POS,cta_ue_id
            FROM 
            ( SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, '' cta_ue_id FROM cg_PlanCuentas
            WHERE cta_gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND cta_ur_id = ".$_SESSION[_S]['clasAdm-ur']." ".$filtroCta.$filtroNombre.$filtroUE."
            GROUP BY  ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) AS base
            INNER JOIN cat_CgSubcuenta USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            iNNER JOIN cat_EstructuraCuenta USING(ecu_id)
            iNNER JOIN cat_TipoCuenta USING(tcu_id)
            iNNER JOIN cat_PosicionCuenta USING(pcu_id)
            INNER JOIN cg_CuentaCuarto USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id)

            UNION

            SELECT cgq_cuenta AS CUENTA, cgq_nombre AS NOMBRE, CASE nat_id WHEN 'D' THEN 'Deudora' ELSE 'Acreedora' END AS NAT,
            CONCAT(LEFT(tcu_nombre,1),LOWER(SUBSTRING(tcu_nombre,2))) AS TIPO,
            CONCAT(LEFT(ecu_nombre,1),LOWER(SUBSTRING(ecu_nombre,2)))AS ESTR,CONCAT(LEFT(pcu_nombre,1),LOWER(SUBSTRING(pcu_nombre,2))) AS POS,cta_ue_id
            FROM
            ( SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, '' cta_ue_id FROM cg_PlanCuentas
            WHERE cta_gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND cta_ur_id = ".$_SESSION[_S]['clasAdm-ur']." ".$filtroCta.$filtroNombre.$filtroUE."
            GROUP BY  ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) AS base
            INNER JOIN cat_CgSubcuenta USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            iNNER JOIN cat_EstructuraCuenta USING(ecu_id)
            iNNER JOIN cat_TipoCuenta USING(tcu_id)
            iNNER JOIN cat_PosicionCuenta USING(pcu_id)
            INNER JOIN cg_CuentaQuinto USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id)

            UNION

            SELECT cta_Cuenta AS cuenta, cta_Nombre AS nombre, CASE nat_id WHEN 'D' THEN 'Deudora' ELSE 'Acreedora' END AS nat,
            CONCAT(LEFT(tcu_nombre,1),LOWER(SUBSTRING(tcu_nombre,2))) AS TIPO,
            CONCAT(LEFT(ecu_nombre,1),LOWER(SUBSTRING(ecu_nombre,2)))AS ESTR,CONCAT(LEFT(pcu_nombre,1),LOWER(SUBSTRING(pcu_nombre,2))) AS POS,cta_ue_id
            FROM cg_PlanCuentas 
            INNER JOIN cat_CgSubcuenta USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            iNNER JOIN cat_EstructuraCuenta USING(ecu_id)
            iNNER JOIN cat_TipoCuenta USING(tcu_id)
            iNNER JOIN cat_PosicionCuenta USING(pcu_id)
            WHERE cta_gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND cta_ur_id = ".$_SESSION[_S]['clasAdm-ur']." ".$filtroCta.$filtroNombre.$filtroUE."

            ORDER BY cuenta;
            ";
}
