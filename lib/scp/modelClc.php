<?php

require_once dirname(__FILE__) . '/modelScp.php';

class modelClc extends modelscp
{
    public $c_gpo_id;
    public $c_ur_id;

    public function __construct()
    {
        parent::__construct();
        $this->c_gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id']
        : $_SESSION[_S]['clasAdm-gpo'];
        $this->c_ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id']
        : $_SESSION[_S]['clasAdm-ur'];
    }

    private function f_get_tcl_cta($tipo, $stc_id)
    {
        switch ($tipo) {
            case 'C':
                $subtipos = $this->g_get_stc_clc_cta($stc_id);
                return ' AND tcl_id = "' . $subtipos . '"';
                break;
            case 'D':
                return ' AND tcl_id IN ("A","B","F")';
                break;
            case 'E':
                return ' AND tcl_id IN ("A","B")';
                break;
            default:
                return ' AND tcl_id="' . $tipo . '"';
                break;
        }
    }

    private function g_get_stc_clc_cta($stc_id)
    {
        switch ($stc_id) {
            case 1:
                return 'A';
                break;
            case 2:
                return 'B';
                break;
            case 3:
                return 'F';
                break;
            case 4:
                return 'G';
                break;
            default:
                return 'A';
                break;
        }
    }

    public function getSqlCuenta($prm)
    {
        $tclfiltro = $this->f_get_tcl_cta($prm['tipo'], $prm['stc_id']);
        $p = [
            'ChequeSpei' => $prm['ChequeSpei'], 'tclfiltro' => $tclfiltro,
            'ucb_id'     => $prm['ucb_id'],
        ];

        $r = $this->f_getfiltroUcb($p);

        $filtro = $r['filtro'];
        //@Jordy Se agrega la variable ebaId solamente para los casos cuando se utiliza la variable
        //$filtro
        $ebaId = isset($r['filtro']) && $r['filtro'] != '' ? 'AND eba_id = 6' : 'WHERE eba_id = 6';
        $left = $r['left'];
        $ucb_id = $prm['ucb_id'];
        $sql = '';
        $ur = $this->c_gpo_id . $this->c_ur_id;
        switch ($prm['mpc_id']) { //Modalidad de Pago
            case 1:
                switch ($ur) { //En la modalidad de pago tradicional se hace una diferencia con la UR 128
                    case '901': // antes ur 801
                        switch ($prm['tipo']) {
                            case 'C':
                                if ($ucb_id != 3) {
                                    $tclfiltro = ' AND tcl_id="I" ';
                                }
                                $sql = '
                                    SELECT
                                        bur_id,bur_alias,iba_nombre,bur_CuentaBancaria
                                    FROM cat_BancosUr
                                        INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                                        LEFT JOIN vin_BancosUrTipoClc USING (bur_id)
                                    WHERE  ucb_id = ' . $ucb_id . '
                                        ' . $tclfiltro . '
                                        AND eba_id = 6 ';
                                break;
                            case 'I':
                                $com_1 = '';
                                switch ($prm['stc_id']) {
                                    case 01:
                                    case 03:
                                        $com_1 = ' OR (bur_id=0) ';
                                        break;
                                }
                                $sql = '
                                    SELECT
                                        bur_id,bur_alias,iba_nombre,bur_CuentaBancaria
                                    FROM cat_BancosUr
                                        INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                                        LEFT JOIN vin_BancosUrTipoClc USING (bur_id)
                                    ' . $filtro . '
                                    ' . $com_1 . '
                                    ' . $ebaId . ' ';
                                break;
                        }
                        break;
                    case '902':
                        if ($prm['tipo'] == 'I' and $prm['stc_id'] == '04') {
                            $sql = '
                                SELECT
                                    bur_id,bur_alias,iba_nombre,bur_CuentaBancaria
                                FROM cat_BancosUr
                                    INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                                    ' . $left . ' JOIN vin_BancosUrTipoClc USING (bur_id)
                                ' . $filtro . '
                                    ' . (!empty($prm['ue_id']) ? ' AND ue_id = ' . $prm['ue_id']
                                : ' AND FALSE') . '
                                    ' . $ebaId . ' ';
                        }
                        break;

                    default:
                        $sql = '
                            SELECT
                                bur_id,bur_alias,iba_nombre,bur_CuentaBancaria
                            FROM cat_BancosUr
                                INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                                ' . $left . 'JOIN vin_BancosUrTipoClc USING (bur_id)
                            ' . $filtro . '
                                ' . $ebaId . ' ';

                        break;
                } //Termina switch Por Unidad Responsable
                break;
            case 2:
            case 3:
                $sql = '
                    SELECT
                        bur_id,bur_alias,iba_nombre,bur_CuentaBancaria
                    FROM cat_BancosUr
                        INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                    WHERE bur_id = 0
                        AND eba_id = 6 ';
                break;
                // @JORDY 26-10-2018 debido a que la modalidad de pago de cadena productiva es igual que al pago directo por PDP
                // se agrega en la misma consulta que el pago directo a proveedor ticket 638123
            case 4:
            case 5:
                $sql = '
                    SELECT
                        bur_id,bur_alias,iba_nombre,bur_CuentaBancaria
                    FROM cat_BancosUr
                        INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                        ' . $left . '
                        INNER JOIN vin_BancosUrTipoClc USING (bur_id)
                        INNER JOIN vin_BancosUrProveedor USING (bur_id)
                    ' . $filtro . '
                        ' . $ebaId . ' ';
                break;
        }
        return $sql;
    }

    private function f_getfiltroUcb($prm)
    {
        $exp116 = '';
        $ChequeSpei = $prm['ChequeSpei'];
        $tclfiltro = $prm['tclfiltro'];
        $ur = $this->c_gpo_id . $this->c_ur_id;
        $UnidadResponsable = ' gpo_id=' . $this->c_gpo_id . ' AND  ur_id= ' . $this->c_ur_id . ' ';
        $UrCompartida = ' bur_gpo_id=' . $this->c_gpo_id . ' AND  bur_ur_id= ' . $this->c_ur_id . ' ';
        $filtro = '';
        $left = '';

        if (($ChequeSpei == 1) || ($ChequeSpei == '')) {
            switch ($prm['ucb_id']) {
                case 1:
                    if ($ur == 116) {
                        $exp116 = ' OR  (bur_id=0)';
                        $left = 'LEFT ';
                    }
                    $filtro = ' WHERE ucb_id=1  AND eba_id=6 ' . $tclfiltro . ' AND ' . $UnidadResponsable . ' ' . $exp116 . ' ';
                    break;
                case 2:
                    $filtro = ' WHERE ucb_id=2 ' . $tclfiltro . ' AND eba_id=6
                        AND ((' . $UnidadResponsable . ') OR (' . $UrCompartida . ')) ';
                    break;
                case 3:
                    $filtro = ' WHERE ucb_id=3 AND eba_id=6 AND bur_id!=0 ';
                    $left = ' LEFT ';
                    break;
                case 4:
                    $filtro = ' WHERE ucb_id=4 AND eba_id=6 ' . $tclfiltro . '';
                    break;
            }
        } else {
            $filtro = ' WHERE bur_id=0';
            $left = ' LEFT ';
        }
        return ['filtro' => $filtro, 'left' => $left];
    }

    public function f_validaPdp($prm)
    {
        // 28-01-2019 se consulta el importe total utilizado de los documento utilizados en la
        //  clc por medio del IMPORTE_AFECTACION
        $sql = '
            SELECT
                gpo_id,ur_id,clc_id,bdo_id,
                IMPORTE_NUM_CONTROL AS IMPORTE_AFECTACION,
                IMPORTE_DOC, rfc_id,clc_beneficiario,
                CONCAT(rfc_nombre,
                    CASE TRIM(IFNULL(rfc_paterno,""))
                        WHEN ""
                            THEN ""
                        ELSE
                        CONCAT(" ",rfc_paterno ) END,
                    CASE TRIM(IFNULL(rfc_materno,""))
                        WHEN ""
                            THEN ""
                        ELSE
                            CONCAT(" ",rfc_materno ) END,
                    CASE TRIM(IFNULL(soc_nombre,""))
                        WHEN ""
                            THEN ""
                        ELSE
                            CONCAT(" ",soc_nombre ) END
                ) AS rfc_nombre
            FROM scp_Clc
            LEFT JOIN (
                SELECT
                    gpo_id,ur_id,clc_id,bdo_id,rfc_id,SUM(C) AS C,
                    ((bdo_percepcionPresupuestal + bdo_percepcionContable ) - bdo_retencionContable ) * 1 AS IMPORTE_DOC,
                    SUM(IMPORTE_NUM_CONTROL) AS  IMPORTE_NUM_CONTROL
                FROM (
                    SELECT
                        gpo_id,ur_id,clc_id,bdo_id,a.rfc_id,bdo_id AS C,
                        IF(ncc_tipoMovimiento = "-" ,ncc_retencion * -1,ncc_percepcion) as IMPORTE_NUM_CONTROL
                    FROM scp_Contable a
                    INNER JOIN cat_BancoDocumentos b USING ( gpo_id,ur_id,bdo_id )
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND clc_id = ?
                    UNION ALL
                    SELECT
                        gpo_id,ur_id,clc_id,bdo_id,a.rfc_id,bdo_id AS C,
                        ncp_percepcionNeto AS IMPORTE_NUM_CONTROL
                    FROM scp_Presupuestal a
                    INNER JOIN cat_BancoDocumentos b USING ( gpo_id,ur_id,bdo_id )
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND clc_id = ?
                )v
                INNER JOIN cat_BancoDocumentos b1 USING ( gpo_id,ur_id,bdo_id,rfc_id )
                GROUP BY gpo_id,ur_id,clc_id,bdo_id,rfc_id
            )N USING ( gpo_id,ur_id,clc_id )
            INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING(rfc_id)
            INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor using(soc_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                AND clc_id = ?
            GROUP BY gpo_id,ur_id,clc_id,bdo_id,rfc_id ;';
        $params = [$prm['gpo_id'],$prm['ur_id'],$prm['clc_id'],$prm['gpo_id'],$prm['ur_id'],$prm['clc_id'],$prm['gpo_id'],$prm['ur_id'],$prm['clc_id']];
        return $this->getDatos($sql, false, $params);
    }
}
