<?php

function catalogo($gpo_id, $ur_id, $x, $y, $z, $parametroExtra = array())
{

    switch ($parametroExtra['opc_id']) {
        case 1:
            $sql = "
                    SELECT * 
                    FROM  ".BD_CTRL.".cat_MesContable
                ";
            break;
        case 2:
            $sql = "
                    SELECT agc_id AS clave, agc_nombre AS nombre, agc_descripcion as descrip
                    FROM ".BD_CTRL.".cat_AgrupacionCuentas 
                ";
            break;
        case 3:
            $sql = "
                    SELECT tdf_id AS clave, tdf_nombre AS nombre, tdf_alias AS alias
                    FROM ".BD_CTRL.".cat_TipoDocumentoFuente 
                ";
            break;
        case 4:
            $sql = "
                    SELECT adf_id AS clave, adf_nombre AS nombre, adf_alias AS alias
                    FROM ".BD_CTRL.".cat_AgrupacionDocumentoFuente 
                ";
            break;
        case 5:
            $sql = "
                    SELECT apg_id AS clave, apg_nombre AS nombre, apg_alias AS alias
                    FROM ".BD_CTRL.".cat_AgrupacionPoliza 
                ";
            break;
        case 6:
            $sql = "
                    SELECT prf_id AS clave, prf_nombre AS nombre, prf_alias AS alias
                    FROM ".BD_CTRL.".cat_PrefijoPoliza 
                ";
            break;
        case 7:
            $sql = "
                    SELECT mmc_id AS clave, mmc_nombre AS nombre, mmc_alias AS alias
                    FROM ".BD_CTRL.".cat_MomentoContable 
                    ORDER BY mmc_orden ASC 
                ";
            break;
        case 8:
            $sql = "
                SELECT pg_id, pg_nombre, cog.cog_tipo, cog.cog_cuenta, cgm.cgsc_nombre, ccc.cog_cuenta AS cog_contracuenta, cgn.cgsc_nombre AS cgm_contranombre 
                FROM ".BD_CTRL.".vin_Cog cog 
                    INNER JOIN ".BD_CTRL.".vin_CogContraCuenta ccc USING( pg_id ) 
                    INNER JOIN ".BD_CTRL.".cat_PartidaGenerica USING( pg_id ) 
                    LEFT JOIN ".BD_CTRL.".cat_CgSubcuenta cgm ON cog.ger_id = cgm.ger_id AND cog.cgp_id = cgm.cgp_id AND cog.rub_id = cgm.rub_id AND cog.cgcu_id = cgm.cgcu_id AND cog.cgsc_id = cgm.cgsc_id 
                    LEFT JOIN ".BD_CTRL.".cat_CgSubcuenta cgn ON ccc.ger_id = cgn.ger_id AND ccc.cgp_id = cgn.cgp_id AND ccc.rub_id = cgn.rub_id AND ccc.cgcu_id = cgn.cgcu_id AND ccc.cgsc_id = cgn.cgsc_id 
                ";
            break;
        case 9:
            $sql = "
                    SELECT cgc_cuenta, cgc_nombre, cta_id, cta_nombre 
                    FROM ".BD_CTRL.".vin_Retencion 
                        LEFT JOIN ".BD_CTRL.".cg_CuentaCuarto USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) 
                ";
            break;
        case 10:
            $sql = 'PROVEEDOR';
            break;
        default:
            echo "kym->reporte indefinida";
            break;
    }

    $catalogo = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $catalogo->conectar();
    $catalogo->setSql($sql);
    $catalogo->consultar();
    $catalogo->desconectar();

    $datos = array();

    while ($rowCatalogo = $catalogo->extraer()) {
        switch ($parametroExtra['opc_id']) {
            case 2:
                $datos[$rowCatalogo['clave']] = array(
                    'clave' => $rowCatalogo['clave'],
                    'nombre' => $rowCatalogo['nombre'],
                    'descrip' => $rowCatalogo['descrip']
                );
                break;
            case 8:
                $datos[$rowCatalogo['pg_id']] = array(
                    'pg_id' => $rowCatalogo['pg_id'],
                    'pg_nombre' => $rowCatalogo['pg_nombre'],
                    'cog_tipo' => $rowCatalogo['cog_tipo'],
                    'cog_cuenta' => $rowCatalogo['cog_cuenta'],
                    'cgsc_nombre' => $rowCatalogo['cgsc_nombre'],
                    'cog_contracuenta' => $rowCatalogo['cog_contracuenta'],
                    'cgm_contranombre' => $rowCatalogo['cgm_contranombre'],
                );
                break;
            case 9:
                $datos[$rowCatalogo['cgc_cuenta']] = array(
                    'cgc_cuenta' => $rowCatalogo['cgc_cuenta'],
                    'cgc_nombre' => $rowCatalogo['cgc_nombre'],
                    'cta_id' => $rowCatalogo['cta_id'],
                    'cta_nombre' => $rowCatalogo['cta_nombre']
                );
                break;
            default:
                $datos[$rowCatalogo['clave']] = array(
                    'clave' => $rowCatalogo['clave'],
                    'nombre' => $rowCatalogo['nombre'],
                    'alias' => $rowCatalogo['alias']
                );
                break;
        }
    }
    return $datos;
}