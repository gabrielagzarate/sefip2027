<?php

require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';

class InversionPublica extends AccesoDatos
{
    /*------------------------- DATOS DE USO DEL CONTRATO EN CLCS TODOS LOS AÑOS  ( DONDE HAY CONTRATOS ) --*/
    public static function get_UsoClc($anio, $contrato)
    {
        $datos = 0;
        if ($anio == '' || $contrato == '') {
            return '<span class="danger">Error en datos de consulta</span>';
        }

        $con = new AccesoDatos();
        $con->conectar();

        switch ($anio) {
            case 2026:
            case 2025:
            case 2024:
            case 2023:
            case 2022:
            case 2021:
            case 2020:
            case 2019:
            case 2018:
            case 2017:
                $sql = '
                    SELECT  gpo_id,ur_id,clc_id,tcl_id,stc_id,clc_percepcion,clc_retencion,ecl_nombre,ecl_id 
                    FROM ctrl' . $anio . '.scp_Clc
                        INNER JOIN ctrl' . $anio . '.cat_ObraClc USING( gpo_id,ur_id,clc_id )
                        INNER JOIN ctrl' . $anio . '.cat_EstadoClc USING( ecl_id )
                    WHERE ipu_id = ?
                        ORDER BY tcl_id DESC;
                ';
                break;
                /*cat_ObraClc tiene distintos parámetros para los ejercicios 2015-2016
                en comparación al resto de ejercicios.
                */
            case 2016:
            case 2015:
                $sql = '
                    SELECT  gpo_id,ur_id,clc_id,tcl_id,stc_id,clc_percepcion,clc_retencion,ecl_nombre,ecl_id 
                    FROM ctrl' . $anio . '.scp_Clc
                    INNER JOIN ctrl' . $anio . '.cat_ObraClc USING( obr_id )
                    INNER JOIN ctrl' . $anio . '.cat_EstadoClc USING( ecl_id )
                    WHERE ipu_id = ? 
                    ORDER BY tcl_id DESC;
                ';
                break;
        }
        $con->setSql($sql);
        $con->consultar([$contrato]);
        if ($con->getNumeroRegistros() > 0) {
            $datos = [];
            while ($res = $con->extraer()) {
                array_push($datos, $res);
            }
        }
        $con->desconectar();
        return $datos;
    }
    /*----- REGRESA LOS DATOS DEL CONTRATO, COMO LA ENTIDAD PERO EN FUNCION ESTATICA  --*/
    public static function get_InversionPublica($ipu_id)
    {
        if ($ipu_id == '') {
            return 'error';
        }
        $sql = '
            SELECT * 
            FROM ' . BD_SINPRES . '.cat_InversionPublica 
            INNER JOIN ' . BD_SINPRES . '.cat_EstadoInversion USING ( eip_id )
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING ( vur_id )
            WHERE ipu_id = ?
        ';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar([$ipu_id]);
        $con->desconectar();
        $datos = $con->extraer();
        if ($con->getNumeroRegistros() == 0) {
            return 'sin registros';
        } else {
            return $datos;
        }
    }
}
