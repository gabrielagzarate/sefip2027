<?php
require_once dirname(__FILE__) . '/../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../lib/AccesoDatos.php';
class relacionMuebleInmueble
{
    public static  $cuentaMueble =
    " IN ('1.2.4.1.1',
          '1.2.4.1.2',
          '1.2.4.1.3',
          '1.2.4.1.9',
          '1.2.4.2.1',
          '1.2.4.2.2',
          '1.2.4.2.3',
          '1.2.4.2.9',
          '1.2.4.3.1',
          '1.2.4.3.2',
          '1.2.4.4.1',
          '1.2.4.4.2',
          '1.2.4.4.3',
          '1.2.4.4.4',
          '1.2.4.4.5',
          '1.2.4.4.9',
          '1.2.4.5.0',
          '1.2.4.6.1',
          '1.2.4.6.2',
          '1.2.4.6.3',
          '1.2.4.6.4',
          '1.2.4.6.5',
          '1.2.4.6.6',
          '1.2.4.6.7',
          '1.2.4.6.9',
          '1.2.4.7.1',
          '1.2.4.7.2',
          '1.2.4.8.1',
          '1.2.4.8.2',
          '1.2.4.8.3',
          '1.2.4.8.4',
          '1.2.4.8.5',
          '1.2.4.8.6',
          '1.2.4.8.7',
          '1.2.4.8.8',
          '1.2.4.8.9')";
    public static  $cuentaInmuble =
    " IN ('1.2.3.1.0',
          '1.2.3.2.0',
          '1.2.3.3.0',
          '1.2.3.4.1',
          '1.2.3.4.2',
          '1.2.3.4.3',
          '1.2.3.4.4',
          '1.2.3.4.5',
          '1.2.3.4.6',
          '1.2.3.4.7',
          '1.2.3.4.8',
          '1.2.3.4.9',
          '1.2.3.5.1',
          '1.2.3.5.2',
          '1.2.3.5.3',
          '1.2.3.5.4',
          '1.2.3.5.5',
          '1.2.3.5.6',
          '1.2.3.5.7',
          '1.2.3.5.9',
          '1.2.3.6.1',
          '1.2.3.6.2',
          '1.2.3.6.3',
          '1.2.3.6.4',
          '1.2.3.6.5',
          '1.2.3.6.6',
          '1.2.3.6.7',
          '1.2.3.6.9',
          '1.2.3.9.0')";
    public static function getDato($gpo, $ur, $tipoBien, $trimestre, $parametroExtra)
    {
        $grupos = (isset($parametroExtra['grupos']) && $parametroExtra['grupos'] != '' ? $parametroExtra['grupos'] : '');
        $excluir = (isset($parametroExtra['excluir']) && $parametroExtra['excluir'] != '' ? $parametroExtra['excluir'] : '');
        $ue = (isset($parametroExtra['ue']) ? $parametroExtra['ue'] : "");
        $tipoReporte = (isset($parametroExtra['tipoReporte']) ? $parametroExtra['tipoReporte'] : '');
        $cuentas = $tipoBien == 'A' ? self::$cuentaMueble : self::$cuentaInmuble;
        

        $tipoRep = "";
        $params = [];
        if ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6) {
            $tipoRep = " AND t.usr_id = :usr";
            $params[':usr'] =  $_SESSION[_S]['usr_id'];
        }

        // Si limitaron por grupo o excluyeron alguna UR
        $otroWhere = "";
        if ($tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
            // Si limitaron por grupo o excluyeron alguna UR
            if ($grupos != "") {
                $otroWhere .= "  WHERE gpo_id  IN ( " . rtrim($grupos) . " ) AND ";

                // Si excluyeron a alguna UR
                if ($excluir != "") {
                    $otroWhere .= " " . ($grupos != "" ? "" : " WHERE ") . " CONCAT( gpo_id,ur_id) NOT IN ( " . rtrim($excluir) . " ) AND ";
                }
            }
        }

        switch ($trimestre) {
            case 1:
                $periodoSql = 'msc_id IN (0,1, 2, 3)';
                break;
            case 2:
                $periodoSql = 'msc_id IN (0, 1, 2, 3, 4, 5, 6)';
                break;
            case 3:
                $periodoSql = 'msc_id IN (0, 1, 2, 3, 4, 5, 6, 7, 8, 9)';
                break;
            case 4:
            case 5:
                $periodoSql = 'msc_id IN (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)';
                break;
        }
        
        $sql = "SELECT g.ger_cuenta, p.cgp_cuenta, r.rub_cuenta,sc.cgsc_cuenta AS cuenta,sc.cgsc_nombre AS nombre, pdx_debe, pdx_haber,o.nat_id as cgm_naturaleza
                    FROM (SELECT
                        x.cta_gpo_id,
                        x.cta_ur_id,
                        '' cta_ue_id,
                        x.ger_id,
                        x.cgp_id,
                        x.rub_id,
                        x.cgcu_id,
                        x.cgsc_id,
                        '' cgc_id,
                        '' cta_id,
                        x.nat_id,
                        SUM(x.pdx_debe) AS pdx_debe,
                        SUM(x.pdx_haber) AS pdx_haber,
                        5 AS nivel,
                        CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id ) AS id
                    FROM
                        " . BD_CTRL . ".cg_PolizaMaestro m
                    INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id )
                    INNER JOIN (SELECT cgsc_cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id FROM " . BD_CTRL . ".cat_CgSubcuenta ) cuenta USING( ger_id,cgp_id,rub_id,cgcu_id,cgsc_id )
                    " . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? " INNER JOIN " . BD_CTRL . ".cat_CargaTrabajo t USING(gpo_id,ur_id)
                        " : "") . " WHERE " : (($tipoReporte == 3) ? ($_SESSION[_S]['nvj_id'] < 5 ?
                        " WHERE " : $otroWhere) : "
                        WHERE gpo_id = $gpo AND ur_id = $ur  ".($ue != '' ? " AND m.ue_id = " . $ue . "" : "") . "
                            AND ")) . "
                        {$periodoSql}
                        AND m.epl_id NOT IN ( 'C', 'H' )
                        AND cgsc_cuenta {$cuentas}                        
                        $tipoRep
                    GROUP BY ger_id,cgp_id,rub_id,cgcu_id,cgsc_id
                    ) o
                    RIGHT JOIN " . BD_CTRL . ".cat_CgSubcuenta sc USING( ger_id,cgp_id,rub_id,cgcu_id,cgsc_id )
                    RIGHT JOIN " . BD_CTRL . ".cat_CgCuenta c USING( ger_id,cgp_id,rub_id,cgcu_id )
                    RIGHT JOIN " . BD_CTRL . ".cat_Rubro r USING( ger_id,cgp_id,rub_id )
                    RIGHT JOIN " . BD_CTRL . ".cat_CgGrupo p USING( ger_id,cgp_id )
                    RIGHT JOIN " . BD_CTRL . ".cat_Genero g USING(ger_id)
                    WHERE sc.cgsc_cuenta {$cuentas}
                    GROUP BY g.ger_id,'.',p.cgp_id,'.',r.rub_id,'.',c.cgcu_id,'.',sc.cgsc_id";

        $contable = new AccesoDatos();
        $contable->conectar();
        $contable->setSql($sql);
        /*echo "<pre>";
        print_r($sql);
        echo "</pre>";*/
        $contable->consultar($params);
        $contable->desconectar();

        $datos = [];

        while ($rowContable = $contable->extraer()) {
            $datos[$rowContable['cuenta']] = [
                'nombre' => $rowContable['nombre'],
                'pdx_debe' => $rowContable['pdx_debe'],
                'pdx_haber' => $rowContable['pdx_haber'],
                'cuenta' =>  $rowContable['cuenta'],
                'naturaleza' => $rowContable['cgm_naturaleza']
            ];
        }
        return $datos ?? [];
    }
    public static function GetBalanza($gpo, $ur, $tipoBien, $trimestre,  $parametroExtra)
    {
        $parametroExtra['I'] = true;
        $saldoInicial = self::getDato($gpo, $ur, $tipoBien,  $trimestre, $parametroExtra);
        $parametroExtra['I'] = false;
        $movimiento = self::getDato($gpo, $ur, $tipoBien, $trimestre, $parametroExtra);
        $suma = 0;
        $saldoFinal = [];
        foreach ($saldoInicial as $key => $cuenta) {

            if ($cuenta['naturaleza'] == 'D') {
                $suma += ($cuenta['pdx_debe'] - $cuenta['pdx_haber']);
            }

            $saldoFinal[] = [
                'nombre'      => $cuenta['nombre'],
                'saldofinal_debe'    =>  $cuenta['pdx_debe'],
                'saldofinal_haber'   => $cuenta['pdx_haber'],
                'cuenta'      => $cuenta['cuenta'],
                'naturaleza'  => $cuenta['naturaleza'],
            ];
        }
        $saldoFinal[] = [
            'nombre'      => "TOTAL",
            'saldofinal_debe'    => $suma,
            'saldofinal_haber'   => "",
            'cuenta'      => "",
            'naturaleza'  => "",
        ];
        return $saldoFinal;
    }
}
