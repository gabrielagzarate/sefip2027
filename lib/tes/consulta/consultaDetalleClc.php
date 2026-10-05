<?php
function consultaDetalleClc($ejercicio = '', $gpo_id = '', $ur_id = '', $clc_id = '')
{
    $sql = '
        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, tdf_id, bdo_concepto,
            bdo_fechaDocumento, bdo_referencia, rfc_id, SUM(presupuestal) + SUM(percepcion) -
            SUM(retencion) AS importe, #SUM(presupuestal) AS presupuestal,
            SUM(percepcion) AS percepcion, SUM(retencion) AS retencion, ecl_id
        FROM (';

    switch ($ejercicio) {
        case 2017:
            $sql .= '
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                    presupuestal, 0 AS percepcion, 0 AS retencion
                FROM '.BD_CTRL_2017.'.scp_Presupuestal
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                UNION ALL
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                    SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                FROM '.BD_CTRL_2017.'.scp_Contable
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                )n
            INNER JOIN '.BD_CTRL_2017.'.scp_Clc USING(gpo_id, ur_id, clc_id)
            LEFT JOIN '.BD_CTRL_2017.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2018:
            $sql .= '
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                    presupuestal, 0 AS percepcion, 0 AS retencion
                FROM '.BD_CTRL_2018.'.scp_Presupuestal
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                UNION ALL
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                    SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                FROM '.BD_CTRL_2018.'.scp_Contable
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                )n
            INNER JOIN '.BD_CTRL_2018.'.scp_Clc USING(gpo_id, ur_id, clc_id)
            LEFT JOIN '.BD_CTRL_2018.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2019:
            $sql .= '
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                    presupuestal, 0 AS percepcion, 0 AS retencion
                FROM '.BD_CTRL_2019.'.scp_Presupuestal
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                UNION ALL
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                    SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                FROM '.BD_CTRL_2019.'.scp_Contable
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                )n
            INNER JOIN '.BD_CTRL_2019.'.scp_Clc USING(gpo_id, ur_id, clc_id)
            LEFT JOIN '.BD_CTRL_2019.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2020:
            $sql .= '
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                    presupuestal, 0 AS percepcion, 0 AS retencion
                FROM '.BD_CTRL_2020.'.scp_Presupuestal
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                UNION ALL
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                    SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                FROM '.BD_CTRL_2020.'.scp_Contable
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                )n
            INNER JOIN '.BD_CTRL_2020.'.scp_Clc USING(gpo_id, ur_id, clc_id)
            LEFT JOIN '.BD_CTRL_2020.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2021:
            $sql .= '
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                    presupuestal, 0 AS percepcion, 0 AS retencion
                FROM '.BD_CTRL_2021.'.scp_Presupuestal
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                UNION ALL
                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                    SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                FROM '.BD_CTRL_2021.'.scp_Contable
                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                )n
            INNER JOIN '.BD_CTRL_2021.'.scp_Clc USING(gpo_id, ur_id, clc_id)
            LEFT JOIN '.BD_CTRL_2021.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2022:
            $sql .= '
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                        presupuestal, 0 AS percepcion, 0 AS retencion
                    FROM '.BD_CTRL_2022.'.scp_Presupuestal
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    UNION ALL
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                        SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                    FROM '.BD_CTRL_2022.'.scp_Contable
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    )n
                INNER JOIN '.BD_CTRL_2022.'.scp_Clc USING(gpo_id, ur_id, clc_id)
                LEFT JOIN '.BD_CTRL_2022.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2023:
            $sql .= '
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                        presupuestal, 0 AS percepcion, 0 AS retencion
                    FROM '.BD_CTRL_2023.'.scp_Presupuestal
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    UNION ALL
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                        SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                    FROM '.BD_CTRL_2023.'.scp_Contable
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    )n
                INNER JOIN '.BD_CTRL_2023.'.scp_Clc USING(gpo_id, ur_id, clc_id)
                LEFT JOIN '.BD_CTRL_2023.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2024:
            $sql .= '
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                        presupuestal, 0 AS percepcion, 0 AS retencion
                    FROM '.BD_CTRL_2024.'.scp_Presupuestal
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    UNION ALL
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                        SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                    FROM '.BD_CTRL_2024.'.scp_Contable
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    )n
                INNER JOIN '.BD_CTRL_2024.'.scp_Clc USING(gpo_id, ur_id, clc_id)
                LEFT JOIN '.BD_CTRL_2024.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2025:
            $sql .= '
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                        presupuestal, 0 AS percepcion, 0 AS retencion
                    FROM '.BD_CTRL_2025.'.scp_Presupuestal
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    UNION ALL
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                        SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                    FROM '.BD_CTRL_2025.'.scp_Contable
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    )n
                INNER JOIN '.BD_CTRL_2025.'.scp_Clc USING(gpo_id, ur_id, clc_id)
                LEFT JOIN '.BD_CTRL_2025.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
        case 2026:
            $sql .= '
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, SUM(ncp_percepcionNeto) AS
                        presupuestal, 0 AS percepcion, 0 AS retencion
                    FROM '.BD_CTRL.'.scp_Presupuestal
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    UNION ALL
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 0 AS presupuestal,
                        SUM(ncc_percepcion) AS percepcion, SUM(ncc_retencion) AS retencion
                    FROM '.BD_CTRL.'.scp_Contable
                    GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    )n
                INNER JOIN '.BD_CTRL.'.scp_Clc USING(gpo_id, ur_id, clc_id)
                LEFT JOIN '.BD_CTRL.'.cat_BancoDocumentos USING(gpo_id, ur_id, bdo_id)';
            break;
    }

    $sql .= '
        WHERE gpo_id = :gpo
        AND ur_id = :ur
        AND clc_id = :clc
        GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
        ORDER BY gpo_id, ur_id, clc_id, afe_id, bdo_id';

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar(array(':gpo'=>$gpo_id,':ur'=>$ur_id, ':clc'=>$clc_id));
    $con->desconectar();

    $result = array();

    while ($row = $con->extraer()) {
        $dato = array(
            'afe_id' => $row['afe_id'],
            'bdo_id' => $row['bdo_id'],
            'bdo_concepto' => $row['bdo_concepto'],
            'bdo_referencia' => $row['bdo_referencia'],
            'bdo_fechaDocumento' => $row['bdo_fechaDocumento'],
            'rfc_id' => $row['rfc_id'],
            'importe' => $row['importe'],
            'percepcion' => $row['percepcion'],
            'retencion' => $row['retencion'],
        );

        array_push($result, $dato);
    }
    return $result;

}
