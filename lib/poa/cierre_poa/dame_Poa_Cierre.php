<?php
require_once dirname(__FILE__) . '/../../AccesoDatos.php';

class damePoaCierre extends AccesoDatos
{
    public function obtenerEncuadresNoVinculadosMirModal($clasAdmGpo, $clasAdmUr)
    {
        $datosNoVinculados = array();
        $this->conectar();
        $this->setSql('
            SELECT
                CONCAT(gpo_id,ur_id,"-",prg_id,"-",spr_id,"-",py_id,"-",oa_id) AS encuadre,
                IF(oa_nombre IS NULL
                    , "SIN DESCRIPCIÓN"
                    , oa_nombre) AS concepto
            FROM (SELECT epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,oa_nombre,
                        (SELECT evi_id
                            FROM cat_MetaActividad 
                            WHERE epg_id = e.epg_id 
                            LIMIT 1
                        ) AS evi_id 
                    FROM cat_ObraActividad 
                        INNER JOIN enc_Programatico e USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                    WHERE oa_id != 0
                        AND gpo_id = ? 
                        AND ur_id = ?
                        AND ((eoa_id = 5 AND toa_id = 2) OR (eoa_id = 4 AND toa_id = 1))
                        AND prg_id <= 899
                     ) AS t
            LEFT JOIN cat_EstadoVinculacion USING (evi_id)
            WHERE evi_id in (1,2,3,4,5,6,7);
        ');
        $params = [$clasAdmGpo, $clasAdmUr];
        $this->consultar($params);
        while ($row = $this->extraer()) {
            $dato = array(
                'encuadre' => $row['encuadre'],
                'concepto' => $row['concepto']
            );
            array_push($datosNoVinculados, $dato);
        }
        $this->desconectar();

        return $datosNoVinculados;
    }
}
