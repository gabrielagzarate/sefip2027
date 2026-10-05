<?php

/**
 * Description of function consultaProgramacionClcsPendientes
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 31-Oct-2018
 * Comment: -----
 * Objective: Consultar en la base de datos las Clcs pendientes.
 * Regla de Negocio: -----
 *
 * */

function consultaProgramacionClcsPendientes($prm)
{
    $extraSql = "";
    $conexion = new AccesoDatos();
    switch ($prm->modo) {
        case 5: //Caso proveedores
            switch ($prm->anio) {
                case 2017:
                    $metodoConsulta = new clcs2017();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2018:
                    $metodoConsulta = new clcs2018();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2019:
                    $metodoConsulta = new clcs2019();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2020:
                    $metodoConsulta = new clcs2020();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2021:
                    $metodoConsulta = new clcs2021();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2022:
                    $metodoConsulta = new clcs2022();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2023:
                    $metodoConsulta = new clcs2023();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2024:
                    $metodoConsulta = new clcs2024();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2025:
                    $metodoConsulta = new clcs2025();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2025:
                    $metodoConsulta = new clcs2025();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
                case 2026:
                    $metodoConsulta = new clcs2026();
                    $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($prm);
                    break;
            }
            break;
        default: //Demas casos
            switch ($prm->anio) {
                case 2017:
                    $metodoConsulta = new clcs2017();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2018:
                    $metodoConsulta = new clcs2018();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2019:
                    $metodoConsulta = new clcs2019();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2020:
                    $metodoConsulta = new clcs2020();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2021:
                    $metodoConsulta = new clcs2021();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2022:
                    $metodoConsulta = new clcs2022();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2023:
                    $metodoConsulta = new clcs2023();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2024:
                    $metodoConsulta = new clcs2024();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2025:
                    $metodoConsulta = new clcs2025();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
                case 2026:
                    $metodoConsulta = new clcs2026();
                    $sql = $metodoConsulta->sqlLlenaTablaClc($prm);
                    break;
            }
            break;
    }

    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();

    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato['IDCLC'] = $row['IDCLC'];
        $dato['FECHA_ELABORACION'] = $row['FECHA_ELABORACION'];
        $dato['DIFERENCIA'] = $row['DIFERENCIA'];
        $dato['MODO'] = $row['MODO'];
        $dato['UR'] = $row['UR'];
        $dato['UR_ID'] = $row['UR_ID'];
        $dato['GPO_ID'] = $row['GPO_ID'];
        $dato['CLC'] = $row['CLC'];
        $dato['FOLIO'] = $row['FOLIO'];
        $dato['TIPO'] = $row['TIPO'];
        $dato['NOMBRE_TIPO'] = $row['NOMBRE_TIPO'];
        $dato['NOMBRE_SUBTIPO'] = $row['NOMBRE_SUBTIPO'];
        $dato['RESPONSABLE'] = $row['RESPONSABLE'];
        $dato['BENEFICIARIO'] = $row['BENEFICIARIO'];
        $dato['CONCEPTO'] = $row['CONCEPTO'];
        $dato['CARGO'] = $row['CARGO'];
        $dato['DESTINO'] = $row['DESTINO'];
        $dato['IMPORTE'] = $row['IMPORTE'];
        $dato['CVE_FIN'] = $row['CVE_FIN'];
        $dato['CVE_NOMBRE'] = $row['CVE_NOMBRE'];
        array_push($datos, $dato);
    }
    if (count($datos) == 0) {
        $dato = array(
            'IDCLC' => '',
            'FECHA_ELABORACION' => '',
            'DIFERENCIA' => '',
            'MODO' => '',
            'UR' => '',
            'UR_ID' => '',
            'GPO_ID' => '',
            'CLC' => '',
            'FOLIO' => '',
            'TIPO' => '',
            'NOMBRE_TIPO' => '',
            'NOMBRE_SUBTIPO' => '',
            'RESPONSABLE' => '',
            'BENEFICIARIO' => '',
            'CONCEPTO' => '',
            'CARGO' => '',
            'DESTINO' => '',
            'IMPORTE' => '',
            'CVE_FIN' => '',
            'CVE_NOMBRE' => '',
        );
        array_push($datos, $dato);
    }

    return $datos;
}
