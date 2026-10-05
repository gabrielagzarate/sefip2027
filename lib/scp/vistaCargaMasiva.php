<?php

class vistaCargaMasiva
{
    public function pintaDetalleCarga($ur)
    {
        $html = '';
        if ($ur == '118') {
            $html = '
                <div class = "col-md-12">
                    <table class="table table_detalle_carga">
                        <tr>
                            <th width="33.32%"><a class="lnk_detalle" nivel="1" href="#">Ver Compromiso</a></th>
                            <th width="33.32%"><a class="lnk_detalle" nivel="2" href="#">Ver Afectacion</a></th>
                            <th width="33.33%"><a class="lnk_detalle" nivel="3" href="#">Ver Clc</a></th>
                        </tr>
                        <tr>
                            <td id="det_com" width="33.32%"></td>
                            <td id="det_afe" width="33.32%"></td>
                            <td id="det_clc" width="33.32%"></td>
                        </tr>
                    </table>
                </div>';
        } else {
            $html = '
                <div class = "col-md-12">
                    <table class="table">
                        <tr>
                            <th width="49%"><a class="lnk_detalle" nivel="1" href="#">Ver Compromiso</a></th>
                            <th width="49%"><a class="lnk_detalle" nivel="2" href="#">Ver Afectacion</a></th>
                        </tr>
                        <tr>
                            <td id="det_com" width="49%"></td>
                            <td id="det_afe" width="49%"></td>
                        </tr>
                    </table>
                </div>';
        }
        return $html;
    }

    public function fn_pintaTabla($datos)
    {
        if (!is_array($datos)) {
            return '<table><tr><td>Sin datos</td></tr></table>';
        }
        if ($datos['registros'] == 0) {
            return '<table><tr><td>Sin datos</td></tr></table>';
        }
        $tabla = '<table class="table table-bordered">';
        if (isset($datos['cabecera'])) {
            $tabla .= '<tr>';
            foreach ($datos['cabecera'] as $th) {
                $tabla .= '<th class="success">' . $th . '</th>';
            }
            $tabla .= '</tr>';
        }

        if (isset($datos['datos'])) {
            $c = 1;
            foreach ($datos['datos'] as $valores) {
                $class = ($c % 2 == 1) ? 'FFFFFF' : 'EAEAEA';
                $tabla .= '<tr>';
                foreach ($valores as $td) {
                    $tabla .= '<th style="background-color:#' . $class . '">' . $td . '</th>';
                }
                $tabla .= '</tr>';
                $c++;
            }
        }
        $tabla .= '</table>';
        return $tabla;
    }

    public function fn_validaciones($val)
    {
        switch ($val) {
            case 1:
                return '<div class=" warning">
                    El usuario no est&aacute; no esta facultado para proceder
                    </div>';
                break;
            case 2:
                return '<div class=" warning">
                        Esta carga no se puede liberar o modificar
                    </div>';
                break;
            case 3:
                return '<div class=" warning">
                        Esta carga no genero datos, no es necesario hacer nada
                    </div>';
                break;
            case 4:
                return '<div class="alert error">
                        La carga no existe
                    </div>';
                break;
        }
    }

    public function tbl_ResultadoCancela($carga, $sel, $cancel, $titulo = '', $tipo = '')
    {
        $html = '
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th style="text-align:center" class="info" colspan="3">' . $titulo . ' ' . $carga . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:center" class="info" colspan="">
                                    NOMBRE
                                </th>
                                <th  style="text-align:center" class="info">
                                    SELECCIONADAS
                                </th>
                                <th style="text-align:center" class="info" colspan="">
                                    RECHAZADOS
                                </th>
                            </tr>
                            <tr class="success">
                                <td  style="text-align:center" >' . $tipo . '</td>
                                <td  style="text-align:center" >' . $sel . '</td>
                                <td  style="text-align:center" >' . $cancel . '</td>
                            </tr>
                        </table>
                    </div>
                </div>';
        return $html;
    }

    public function tbl_CompromisosNoCancelados($data)
    {
        if (!is_array($data) || $data == '') {
            return '';
        }
        $html = '
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th bgcolor="#000000" style="text-align:center" class="info" colspan="4">
                                    <font color="#FFFFFF">
                                    DETALLE DE DATOS COMPROMISOS QUE NO SE PUDIERON CANCELAR
                                    </font>
                                </th>
                            </tr>
                            <tr>
                                <th>UR</th>
                                <th>Compromiso</th>
                                <th>Concepto</th>
                                <th>Motivo</th>
                                <th>Resultado</th>
                            </tr>';
        foreach ($data as $row) {
            $html .= '
                    <tr>
                        <td>
                            ' . $row['Ur'] . '
                        </td>
                        <td>
                            ' . $row['cmp_id'] . '
                        </td>
                        <td>
                            ' . $row['cmp_concepto'] . '
                        </td>
                        <td>
                            ' . $row['motivo'] . '
                        </td><td>
                            NO CANCELADO
                        </td>
                    </tr>';
        }
        $html .= '
                </table>
            </div>
        </div>';
        return $html;
    }

    public function tbl_AfectacionNoCancelada($data)
    {
        if (!is_array($data) || $data == '') {
            return '';
        }
        $html = '
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th bgcolor="#000000" style="text-align:center" class="info" colspan="4">
                                    <font color="#FFFFFF">
                                    DETALLE DE LAS AFECTACIONES QUE NO SE PUDIERON CANCELAR
                                    </font>
                                </th>
                            </tr>
                            <tr>
                                <th>UR</th>
                                <th>Afectación</th>
                                <th>Tipo</th>
                                <th>Percepción</th>
                                <th>Retención</th>
                                <th>Motivo</th>
                                <th>Resultado</th>
                            </tr>';
        foreach ($data as $row) {
            $html .= '
                    <tr>
                        <td>' . $row['Ur'] . '</td>
                        <td>' . $row['afe_id'] . '</td>
                        <td>' . $row['tcl_nombre'] . '</td>
                        <td align="right">$ ' . (number_format($row['afe_percepcion'], 2)) . '</td>
                        <td align="right">$ ' . (number_format($row['afe_retencion'], 2)) . '</td>
                        <td>' . $row['motivo'] . '</td>
                        <td>NO CANCELADO</td>
                    </tr>';
        }
        $html .= '
                </table>
            </div>
        </div>';
        return $html;
    }

    public function tbl_ClcNoCancelada($data)
    {
        if (!is_array($data) || $data == '') {
            return '';
        }
        $html = '
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th bgcolor="#000000" style="text-align:center" class="info" colspan="4">
                                    <font color="#FFFFFF">
                                    DETALLE DE LAS CLCS QUE NO SE PUDIERON CANCELAR
                                    </font>
                                </th>
                            </tr>
                            <tr>
                                <th>UR</th>
                                <th>CLC</th>
                                <th>Concepto</th>
                                <th>Percepción</th>
                                <th>Retención</th>
                                <th>Motivo</th>
                                <th>Resultado</th>
                            </tr>';
        foreach ($data as $row) {
            $html .= '
                    <tr>
                        <td>' . $row['Ur'] . '</td>
                        <td>' . $row['clc_id'] . '</td>
                        <td>' . $row['clc_concepto'] . '</td>
                        <td align="right">$ ' . (number_format($row['clc_percepcion'], 2)) . '</td>
                        <td align="right">$ ' . (number_format($row['clc_retencion'], 2)) . '</td>
                        <td>' . $row['motivo'] . '</td>
                        <td>NO CANCELADO</td>
                    </tr>';
        }
        $html .= '
                </table>
            </div>
        </div>';
        return $html;
    }
}
