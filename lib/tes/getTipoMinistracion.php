<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__) . '/../AccesoDatos.php';

class getTipoMinistracion extends AccesoDatos
{

    public function validaTMI($gpo_id = 0, $ur_id = 0, $clc_id = 0, $anio = APP_EJERCICIO, $Panvincula = 0)
    {
        if ($gpo_id == 0 || $ur_id == 0 || $clc_id == 0) {
            return false;
        }
        $ur = $gpo_id . str_pad($ur_id, 2, 0, STR_PAD_LEFT);
        if ($anio == 2016) {
            $urs = array(128, 129, 116);
        } else {
            $urs = array(901, 902, 116);
        }
        $sql = '';
        $params = array($gpo_id, $ur_id, $clc_id);

        switch ($anio) {
            case 2026:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2025:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2025 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2025 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2024:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2024 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2024 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2023:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2023 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2023 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2022:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2022 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2022 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2021:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2021 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2021 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2020:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2020 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2020 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2019:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2019 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2019 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2018:
                $sql = 'SELECT
                        a.gpo_id,
                        a.ur_id,
                        a.clc_id,
                        a.bur_id,
                        b.ucb_id,
                        a.clc_recursoFederal,
                        a.clc_fondoRotatorio,
                        a.tcl_id,
                        a.stc_id,
                        a.mpc_id,
                        CONCAT(b.gpo_id, LPAD(b.ur_id, 2, 0)) AS urb,
                        a.clc_concepto
                    FROM
                        ' . BD_CTRL_2018 . '.scp_Clc a
                            INNER JOIN
                        ' . BD_CTRL_2018 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE
                        a.gpo_id = ? AND a.ur_id = ?
                            AND a.clc_id =?;';
                break;
            case 2017:

                $sql = '
                    SELECT a.gpo_id,a.ur_id,a.clc_id,
                            a.bur_id, b.ucb_id,a.clc_recursoFederal,a.clc_fondoRotatorio,
                            a.tcl_id,a.stc_id,a.mpc_id,
                            CONCAT(b.gpo_id,LPAD(b.ur_id,2,0)) AS urb,
                            a.clc_concepto
                    FROM ' . BD_CTRL_2017 . '.scp_Clc a
                            INNER JOIN ' . BD_CTRL_2017 . '.cat_BancosUr b ON a.bur_id = b.bur_id
                            WHERE a.gpo_id = ? AND a.ur_id = ? AND a.clc_id = ?;';
                break;
            case 2016:
                $sql = '
                    SELECT
                            a.gpo_id, a.ur_id,
                            a.clc_id,a.bur_id,b.ucb_id,
                            a.clc_recursoFederal,a.clc_fondoRotatorio,
                            a.tcl_id,a.stc_id,a.mpc_id,
                            CONCAT(b.gpo_id,LPAD(b.ur_id,2,0)) AS urb,
                            a.clc_concepto
                    FROM ctrl2016.scp_Clc a
                            INNER JOIN ctrl2016.cat_BancosUr b ON a.bur_id = b.bur_id
                    WHERE a.gpo_id = ? AND a.ur_id = ? AND a.clc_id = ?;';
                break;
        }

        $con = new AccesoDatos();
        $con->setSql($sql);
        $con->conectar();
        $con->consultar($params);
        $res = $con->extraer();
        $total = $con->getNumeroRegistros();
        $con->desconectar();

        $return = new stdClass();
        $return->valido = true;
        $return->concepto = $res['clc_concepto'];

        if ($total > 0) {
            if ($res['clc_recursoFederal'] == 1) {
                $return->$tmi = 2;
                return $return;
            }

            if ($res['ucb_id'] == 4) {
                $return->tmi_id = 4;
                return $return;
            }
            if ($res['mpc_id'] == 4) {
                $return->tmi_id = 4;
                return $return;
            }
            if ($res['mpc_id'] == 3) {
                $return->tmi_id = 5;
                return $return;
            }
            if ($res['mpc_id'] == 2) {
                $return->tmi_id = 9;
                return $return;
            }
            if (in_array($res['gpo_id'] . $res['ur_id'], $urs)) {
                $return->tmi_id = 8;
                return $return;
            }
            if ($ur == 118) {
                if ($res['tcl_id'] == 'C') {
                    $return->tmi_id = 6;
                    return $return;
                } elseif ($ur == $res['urb']) {
                    $return->tmi_id = 1;
                    return $return;
                }
            }
            if ($ur == $res['urb'] && ($res['ucb_id'] == 1 || $res['ucb_id'] == 2)) {
                $return->tmi_id = 1;
                return $return;
            }
            if ($res['tcl_id'] == "A") {
                if ($res['stc_id'] == 02 && $res['urb'] == 801) {
                    $return->tmi_id = 7;
                    return $return;
                }
                if ($gpo_id == 1) {
                    if ($res['stc_id'] == 01 || $res['stc_id'] == 03 || $res['stc_id'] == 05 || $res['stc_id'] == 12 || $res['stc_id'] == 06) {
                        $return->tmi_id = 9;
                        return $return;
                    }
                } else {
                    if ($ur == $res['urb']) {
                        $return->tmi_id = 1;
                        return $return;
                    } else {
                        if ($res['stc_id'] == 01 || $res['stc_id'] == 03 || $res['stc_id'] == 05 || $res['stc_id'] == 12 || $res['stc_id'] == 06) {
                            $return->tmi_id = 9;
                            return $return;
                        } else {
                            $return->tmi_id = 8;
                            return $return;
                        }
                    }
                }
            } else {
                $return->tmi_id = 8;
                return $return;
            }

            $return->tmi_id = 8;

            return $return;
        } else {
            if ($Panvincula == 1) {
                $return->valido = 0;
                $return->error = "CLC NO ENCONTRADA " . $clc_id . " UR " . $gpo_id . $ur_id;
                return $return;
            } else {
                echo "clc no encontrada" . $gpo_id . $ur_id . $clc_id;
            }
        }
    }
}
