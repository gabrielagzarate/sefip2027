<?php

require_once dirname(__FILE__) . '/modelScp.php';

class modelAfectacion extends modelscp
{
    public function eliminaDetalleAfe($gpo_id = 0, $ur_id = 0, $afe_id = 0)
    {
        if (!isset($_SESSION[_S]['usr_id'])) {
            return "VERBOTEN!!";
        }

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                $res = $this->fn_valUs(array(
                    "gpo_id" => $gpo_id,
                    "ur_id" => $ur_id,
                    "afe_id" => $afe_id,
                    "usr_id" => $_SESSION[_S]['usr_id'],
                ));
                if ($res === false) {
                    return 'HAY NUMEROS DE CONTROL QUE NO LE PERTENECEN';
                }

                $res = $this->fn_elDeAfe(array(
                    "gpo_id" => $gpo_id,
                    "ur_id" => $ur_id,
                    "afe_id" => $afe_id,
                    "usr_id" => $_SESSION[_S]['usr_id'],
                ));
                if ($res !== true) {
                    return $res;
                }
                break;
            case 2:
                break;
            default:
                if ($_SERVER['SERVER_ADDR'] == "172.17.90.17") {
                    return "SU NIVEL JERARQUICO NO LE PERMITE BORRAR ESTOS DETALLES";
                }

                break;
        }
        return $this->fn_elDeAfe(array("gpo_id" => $gpo_id, "ur_id" => $ur_id, "afe_id" => $afe_id));
    }

    private function fn_elDeAfe($args)
    {
        /* ELIMINA LOS DETALLES DE LA AFECTACION */
        $gpo_id = $args['gpo_id'];
        $ur_id = $args['ur_id'];
        $afe_id = $args['afe_id'];

        $this->conectar();
        $sql = '
            SELECT eaf_id,clc_id
            FROM scp_Afectacion
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id
            AND (eaf_id IN("A","C") OR clc_id > 0 )';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe_id
        ];
        $this->setSql($sql);
        $this->consultar($params);

        /* SI LA AFECTACION ESTA AUTORIZADA O EN CLC NO SE PUEDE ELIMINAR NADA */
        if ($this->getNumeroRegistros() > 0) {
            return "LA AFECTACION NO ESTA EN EL ESTADO CORRECTO O ESTA EN UNA CLC";
        }

        $sql = '
            SELECT eaf_id,clc_id
            FROM ' . BD_BITACORA . '.bit_Afectacion
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND afe_id = :afe_id
                AND eaf_id ="A"';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe_id
        ];
        $this->setParams($params);
        $this->setSql($sql);

        /* SI LA AFECTACION FUE AUTORIZADA Y AHORA ESTA CANCELADA NO SE LE PUEDE BORRAR EL DETALLE */
        if ($this->getNumeroRegistros() > 0) {
            return "LA AFECTACION YA PASO POR EL PROCESO DE AUTORIZACION " .
                "POR LO CUAL NO SE PUEDE ELIMINAR SU DETALLE";
        }

        /* ELIMINAR LOS DETALLES */
        $sql = '
            DELETE FROM scp_Presupuestal
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe_id
        ];
        $this->setSql($sql);
        $this->consultar($params);
        $sql = '
            DELETE FROM scp_Contable
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe_id
        ];
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();

        return true;
    }

    private function fn_valUs($args)
    {
        /*   PARA USUARIOS DE NIVEL 1 VALIDA QUE LO QUE VA A BORRAR NO  TENGA REGISTROS DE OTRO USUARIO */
        $gpo_id = $args['gpo_id'];
        $ur_id = $args['ur_id'];
        $afe_id = $args['afe_id'];
        $usr_id = $args['usr_id'];

        $sql = '
            SELECT *
            FROM scp_Presupuestal
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
            AND afe_id = :afe_id
            AND usr_id != :usr';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe_id,
            ':usr' => $usr_id
        ];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        if ($this->getNumeroRegistros() > 0) {
            return false;
        }

        $sql = '
            SELECT *
            FROM scp_Contable
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND afe_id = :afe_id
                AND usr_id != :usr';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe_id,
            ':usr' => $usr_id
        ];
        $this->setSql($sql);
        $this->consultar($params);
        if ($this->getNumeroRegistros() > 0) {
            return false;
        }
        return true;
    }

    public function fn_infAfectacion($args)
    {
        $select = '
            gpo_id,ur_id,afe_id,clc_id, tcl_id,tcl_nombre,tno_id,stc_id,stc_nombre, n,
            MONTH(afe_fechaElaboracion) as Mes,MONTH(now()) as Actual, DAY(now())as DiaActual,eaf_id,
            cfi_clave,cfi_nombre,afe_fechaInicio, afe_fechaFin, epg_id,ecf_id,ue_id,eaf_nombre,
            afe_fondoRotatorio,a.usr_id ';

        $join = '';

        $tipos = array("I", "D", "E", "H", "C");
        if (!in_array($args['tipo'], $tipos)) {
            $select .= ',tcp_id,tcp_nombre,tno_nombre, mno_id,mno_nombre';
            $join = '
                    INNER JOIN cat_TipoCompra USING(tcp_id)
                    LEFT JOIN cat_TipoNomina USING(tno_id)
                    LEFT JOIN cat_ModalidadNomina USING( mno_id )';
        }
        //Se actualizo la consulta para 2019
        $sql =
        'SELECT
            ' . $select . '
        FROM scp_Afectacion a
        INNER JOIN cat_EstadoAfectacion USING (eaf_id)
        INNER JOIN cat_TipoClc USING   ( tcl_id )
        INNER JOIN cat_SubtipoClc USING( tcl_id,stc_id )
        INNER JOIN enc_ClaveFinanciamiento  USING( ecf_id )
        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING( cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio )
        LEFT JOIN(
            SELECT bb.gpo_id, bb.ur_id, bb.afe_id, COUNT(*) AS n
            FROM (
                SELECT gpo_id,ur_id,afe_id,ncc_id as ncc_id
                FROM scp_Contable
                WHERE gpo_id = ? AND ur_id = ? AND afe_id = ?
                UNION ALL
                SELECT gpo_id,ur_id,afe_id,ncp_id
                FROM scp_Presupuestal
                WHERE gpo_id = ? AND ur_id = ? AND afe_id = ?
            )bb GROUP BY gpo_id, ur_id, afe_id, ncc_id
        )det  USING ( gpo_id,ur_id,afe_id)
        ' . $join . '
        WHERE gpo_id = ? AND ur_id = ?
        AND afe_id = ?';
        $params = [$args['gpo_id'],$args['ur_id'],$args['afe_id'],$args['gpo_id'],$args['ur_id'],$args['afe_id'],$args['gpo_id'],$args['ur_id'],$args['afe_id']];
        $res = $this->getDatos($sql,false,$params);
        return $res['datos'][0];
    }

    public function getNumerosControlAfe($gpo_id, $ur_id, $afe_id)
    {
        $sql = '
        SELECT COUNT(afe_id)AS Numeros
        FROM (
            SELECT afe_id
            FROM scp_Presupuestal
            WHERE gpo_id= ? AND ur_id = ?
                AND afe_id = ?
            UNION ALL
            SELECT afe_id
            FROM scp_Contable
            WHERE gpo_id= ? AND ur_id = ?
                AND afe_id = ?
        )v';
        $params = [$gpo_id,$ur_id,$afe_id,$gpo_id,$ur_id,$afe_id];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'][0]['Numeros'];
    }

    public function getDetalleAfectacion($epg)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
        SELECT
            prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,
            oa_id,oa_nombre,
            SUM(pre_vigente) AS Presupuesto,SUM(pre_comprometido) AS Comprometido,
            SUM(pre_devengado) AS Devengado
        FROM enc_Programatico
        INNER JOIN presupuesto USING (epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        INNER JOIN presupuestoVigente USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN presupuestoComprometido USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN presupuestoDevengado USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN cat_ObraActividad USING ( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        INNER JOIN cat_Proyecto USING ( gpo_id,ur_id,prg_id,spr_id,py_id)
        INNER JOIN cat_Subprograma USING ( gpo_id,ur_id,prg_id,spr_id)
        INNER JOIN cat_Programa USING ( gpo_id,ur_id,prg_id)
        WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND epg_id = :epg_id;
        ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':epg_id' => $epg
        ];
        return $this->getDatos($sql,false,$params)['datos'][0];
    }

    public function getDetalleContable($num)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
            SELECT
                msc_nombre,afe_id,COUNT(ncc_id) AS Registros,
                cta_nombre,agc_id,cta_cuenta,
            SUM(ncc_percepcion) AS per, SUM(ncc_retencion) AS ret
            FROM
            (    SELECT gpo_id,ur_id,
                    cta_gpo_id,cta_ur_id,cta_ue_id,
                    ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,
                    cgc_id,cta_id,
                    cta_nombre,agc_id,cta_cuenta
                FROM scp_Contable
                INNER JOIN cg_PlanCuentas USING ( cta_gpo_id,cta_ur_id,cta_ue_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id,cta_id  )
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND ncc_id = :ncc_id
            )O
            LEFT JOIN scp_Contable USING (gpo_id,ur_id,cta_gpo_id,cta_ur_id,cta_ue_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id,cta_id )
            LEFT JOIN scp_Afectacion v USING (gpo_id,ur_id,clc_id,afe_id)
            LEFT JOIN cat_MesContable b ON MONTH(afe_fechaAutorizado) = b.msc_id
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND eaf_id = "A"
            GROUP BY MONTH(afe_fechaAutorizado),b.msc_nombre,afe_id,O.cta_nombre,O.agc_id,O.cta_cuenta;
            ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':ncc_id' => $num
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'];
    }

    public function getDetalleClavePresupuestal($num)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        /**
         * Autor: Eduardo Venegas
         * Fecha: 11:59 am 22/11/18
         * Comentario: Se agrego el Group by para la actualizacion del modo estricto
         */
        $sql = '
                SELECT pre_comprometido,pre_vigente,pre_devengado,
                    dev_ene,dev_feb,dev_mar,dev_abr,
                    dev_may,dev_jun,dev_jul,dev_ago,
                    dev_sep,dev_oct,dev_nov,dev_dic,
                    vig_ene,vig_feb,vig_mar,vig_abr,
                    vig_may,vig_jun,vig_jul,vig_ago,
                    vig_sep,vig_oct,vig_nov,vig_dic,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 1 THEN 1 ELSE 0 END ) AS ene,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 2 THEN 1 ELSE 0 END ) AS feb,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 3 THEN 1 ELSE 0 END ) AS mar,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 4 THEN 1 ELSE 0 END ) AS abr,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 5 THEN 1 ELSE 0 END ) AS may,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 6 THEN 1 ELSE 0 END ) AS jun,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 7 THEN 1 ELSE 0 END ) AS jul,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 8 THEN 1 ELSE 0 END ) AS ago,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 9 THEN 1 ELSE 0 END ) AS sep,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 10 THEN 1 ELSE 0 END ) AS oct,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 11 THEN 1 ELSE 0 END ) AS nov,
                    SUM( CASE MONTH( afe_fechaAutorizado ) WHEN 12 THEN 1 ELSE 0 END ) AS dic,
                    pre_ClaveCompleta,pe_nombre,cfi_nombre,pe_id,cfi_clave
                FROM presupuesto
                INNER JOIN presupuestoVigente USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN cat_PartidaEspecifica USING ( pg_id ,pe_id )
                INNER JOIN ' . BD_SINPRES .'.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING ( cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio  )
                INNER JOIN (
                    SELECT gpo_id,ur_id,p.ue_id,p.epg_id,pg_id,pe_id,p.ecf_id,p.tcp_id,MONTH( afe_fechaElaboracion ) AS Mes
                    FROM scp_Presupuestal p
                    INNER JOIN scp_Afectacion USING ( gpo_id,ur_id,clc_id,afe_id )
                    WHERE gpo_id  = :gpo_id AND ur_id = :ur_id AND ncp_id = :ncp_id
                )X USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                LEFT JOIN scp_Presupuestal USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                LEFT JOIN scp_Afectacion USING ( gpo_id,ur_id,clc_id,afe_id )
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND eaf_id != "C"
                GROUP BY pre_comprometido,pre_vigente,pre_devengado,
                    dev_ene,dev_feb,dev_mar,dev_abr,
                    dev_may,dev_jun,dev_jul,dev_ago,
                    dev_sep,dev_oct,dev_nov,dev_dic,
                    vig_ene,vig_feb,vig_mar,vig_abr,
                    vig_may,vig_jun,vig_jul,vig_ago,
                    vig_sep,vig_oct,vig_nov,vig_dic,
                    pre_ClaveCompleta,pe_nombre,cfi_nombre,pe_id,cfi_clave;';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':ncp_id' => $num
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'][0];
    }

    public function getInfoNumPresAfe($p)
    {
        $params = [];
        //Se le cambio de sinpres.cat_ClaveFinanciamiento2018 al 2019
        $sql = '
            SELECT gpo_id,ur_id,ecf_id,afe_id,clc_id, tcl_id,tcl_nombre,tno_id,stc_id,stc_nombre, MONTH(afe_fechaElaboracion) as Mes,
                MONTH(now()) as Actual,eaf_id,day(now())as DiaActual,cfi_anio,
                cfi_clave,cfi_nombre,afe_fechaInicio, afe_fechaFin, epg_id,ecf_id,ue_id,tcp_id,
                afe_fondoRotatorio,tcp_nombre,tno_nombre, mno_id,mno_nombre,
                rfc_id,rfc_nombre,rfc_paterno,rfc_materno,cmp_id
                FROM scp_Afectacion
            INNER JOIN cat_TipoClc USING ( tcl_id )
            INNER JOIN cat_SubtipoClc USING ( tcl_id,stc_id )
            INNER JOIN enc_ClaveFinanciamiento USING( ecf_id )
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio )
            LEFT JOIN cat_TipoCompra USING( tcp_id )
            LEFT JOIN cat_TipoNomina USING ( tno_id )
            LEFT JOIN cat_ModalidadNomina USING( mno_id )
            LEFT JOIN scp_Presupuestal C USING( gpo_id,ur_id,clc_id,afe_id,ue_id,epg_id,ecf_id,tcp_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_Proveedores USING (  rfc_id )
            WHERE afe_id = :afe_id
            AND gpo_id = :gpo_id
            AND ur_id = :ur_id
        ';

        $params +=[
            'afe_id' => $p['afe_id'],
            'gpo_id' => $p['gpo_id'],
            'ur_id' =>$p['ur_id']
        ];

        if ($p['abc'] == "c") {
            $sql .= ' AND ncp_id = :ncp_id ';
            $params += ['ncp_id' => $p['ncp_id']];
        }
        
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'][0];
    }

    public function presupuestoClaveFin($p)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
            SELECT sum(pre_vigente) AS Modificado, sum(pre_devengado) AS Devengado, sum(pre_ejercido)AS Ejercido ,
                vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
                com_ene,com_feb,com_mar,com_abr,com_may,com_jun,com_jul,com_ago,com_sep,com_oct,com_nov,com_dic,
                dev_ene,dev_feb,dev_mar,dev_abr,dev_may,dev_jun,dev_jul,dev_ago,dev_sep,dev_oct,dev_nov,dev_dic,
                eje_ene,eje_feb,eje_mar,eje_abr,eje_may,eje_jun,eje_jul,eje_ago,eje_sep,eje_oct,eje_nov,eje_dic,
                C.cfi_nombre,C.cfi_clave
            FROM presupuesto
                INNER JOIN presupuestoComprometido USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN presupuestoVigente USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN presupuestoDevengado USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN presupuestoEjercido USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' C USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE gpo_id = :gpo_id
                AND ur_id = :ur_id
                AND ecf_id = :ecf_id
            GROUP BY vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
                com_ene,com_feb,com_mar,com_abr,com_may,com_jun,com_jul,com_ago,com_sep,com_oct,com_nov,com_dic,
                dev_ene,dev_feb,dev_mar,dev_abr,dev_may,dev_jun,dev_jul,dev_ago,dev_sep,dev_oct,dev_nov,dev_dic,
                eje_ene,eje_feb,eje_mar,eje_abr,eje_may,eje_jun,eje_jul,eje_ago,eje_sep,eje_oct,eje_nov,eje_dic,
                C.cfi_nombre,C.cfi_clave
            HAVING Modificado IS NOT NULL';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':ecf_id' => $p['ecf_id']
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos;
    }

    public function importeAcuerdos($p)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
        SELECT ecf_id,sum(ncc_percepcion)as TOTAL
        FROM scp_Clc
            JOIN scp_Contable USING(gpo_id,ur_id,clc_id)
        WHERE ecf_id= :ecf_id AND tcl_id ="E"
            AND stc_id=01 AND clc_id >0 AND ecl_id NOT IN(1,14,15,17)
            AND gpo_id = :gpo_id and ur_id = :ur_id';
        $params = [
            ':ecf_id' => $p['ecf_id'],
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'][0];
    }

    public function presupuestoObra($p)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $sql = '
            SELECT
                SUM(pre_vigente) AS Modificado,SUM(pre_comprometido) AS Comprometido,
                SUM(pre_devengado) AS Devengado, SUM(pre_ejercido)AS Ejercido,
                P.prg_id,P.spr_id,P.py_id,P.oa_id,oa_nombre
            FROM  presupuesto pre
                INNER JOIN presupuestoComprometido pc USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN presupuestoVigente pg USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN presupuestoDevengado pd USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN presupuestoEjercido pe USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
                INNER JOIN enc_Programatico P USING ( gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id )
                INNER JOIN cat_ObraActividad USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
            WHERE gpo_id = :gpo_id
                AND ur_id = :ur_id
                AND epg_id = :epg_id
                AND ecf_id = :ecf_id';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':epg_id' => $p['epg_id'],
            ':ecf_id' => $p['ecf_id']
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos;
    }

    public function anticipoObraSolicitado($p)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
            SELECT epg_id,sum(ncc_percepcion)AS TOTAL
            FROM scp_Clc
                JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,ecf_id)
                JOIN scp_Contable USING(gpo_id,ur_id,clc_id,afe_id)
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND scp_Afectacion.tcl_id="H"
                AND epg_id = :epg_id AND ecl_id NOT IN( 1,2,15,17 )
                AND ecf_id = :ecf_id
                AND eaf_id !="C"
            GROUP BY ecf_id,epg_id';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':epg_id' => $p['epg_id'],
            ':ecf_id' => $p['ecf_id']
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'][0];
    }

    public function anticipoObraGenerado($p)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
            SELECT afe_id,SUM(ncc_percepcion) AS total
            FROM scp_Contable
            INNER JOIN scp_Afectacion USING ( gpo_id,ur_id,afe_id )
            WHERE eaf_id ="G" AND gpo_id = :gpo_id AND ur_id = :ur_id
            AND tcl_id="H"
            AND epg_id = :epg_id
            GROUP BY gpo_id,ur_id,afe_id';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':epg_id' => $p['epg_id']
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'];
    }

    public function anticipoObraRectificado($p)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
            SELECT #SUM(A.ncc_percepcion)
                0 AS rectificacion
            ';
        $datos = $this->getDatos($sql);
        return $datos['datos'][0];
    }

    public function presupuestoClave($data, $t)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
            SELECT
                gpo_id,ur_id,ue_id,P.prg_id,P.spr_id,P.py_id,P.oa_id,pg_id,pe_id,
                C.cff_id,C.fte_id,C.sft_id,C.ori_id,C.sor_id,C.cfi_id,C.cfi_anio,tcp_id,
                pre_mov,
                pre_comprometido AS Comprometido,
                vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
                com_ene,com_feb,com_mar,com_abr,com_may,com_jun,com_jul,com_ago,com_sep,com_oct,com_nov,com_dic,
                dev_ene,dev_feb,dev_mar,dev_abr,dev_may,dev_jun,dev_jul,dev_ago,dev_sep,dev_oct,dev_nov,dev_dic,
                eje_ene,eje_feb,eje_mar,eje_abr,eje_may,eje_jun,eje_jul,eje_ago,eje_sep,eje_oct,eje_nov,eje_dic,
                pre_Vigente AS ModificadoAnual,
                (vig_' . $t->mes1 . ' + vig_' . $t->mes2 . ' + vig_' . $t->mes3 . ') AS Modificado,
                (dev_' . $t->mes1 . ' + dev_' . $t->mes2 . ' + dev_' . $t->mes3 . ') AS Devengado,
                (eje_' . $t->mes1 . ' + eje_' . $t->mes2 . ' + eje_' . $t->mes3 . ') AS Ejercido,
                vig_' . $t->mes1 . ',  vig_' . $t->mes2 . ', vig_' . $t->mes3 . ',
                dev_' . $t->mes1 . ',  dev_' . $t->mes2 . ',  dev_' . $t->mes3 . ',
                eje_' . $t->mes1 . ',  eje_' . $t->mes2 . ',  eje_' . $t->mes3 . '
            FROM  presupuesto
            INNER JOIN presupuestoComprometido USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            INNER JOIN presupuestoVigente USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            INNER JOIN presupuestoDevengado USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            INNER JOIN presupuestoEjercido USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            INNER JOIN enc_Programatico P USING( gpo_id,ur_id,epg_id )
            INNER JOIN enc_ClaveFinanciamiento C USING( ecf_id )
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND ecf_id = :ecf_id
                AND epg_id = :epg_id
                AND pg_id  = :pg_id AND pe_id = :pe_id
                AND tcp_id = :tcp_id AND ue_id = :ue_id';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':ecf_id' => $data->ecf_id,
            ':epg_id' => $data->epg_id,
            ':pg_id' => $data->pg_id,
            ':pe_id' => $data->pe_id,
            ':tcp_id' => $data->tcp_id,
            ':ue_id' => $data->ue_id
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos;
    }

    public function presupuestoGenerado($data)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql3 = '
            SELECT gpo_id,ur_id,afe_id,SUM(ncp_percepcionNeto)AS Importe
            FROM scp_Presupuestal
            INNER JOIN scp_Afectacion USING( gpo_id,ur_id,clc_id,afe_id,ecf_id,epg_id,tcp_id,ue_id )
            WHERE clc_id = 0 AND eaf_id ="G"
                AND gpo_id = :gpo_id 
                AND ur_id = :ur_id 
                AND ecf_id = :ecf_id
                AND epg_id = :epg_id
                AND pg_id  = :pg_id
                AND pe_id = :pe_id
                AND tcp_id = :tcp_id
                AND ue_id = :ue_id
            GROUP BY gpo_id,ur_id,afe_id ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':ecf_id' => $data->ecf_id,
            ':epg_id' => $data->epg_id,
            ':pg_id' => $data->pg_id,
            ':pe_id' => $data->pe_id,
            ':tcp_id' => $data->tcp_id,
            ':ue_id' => $data->ue_id
        ];
        $datos = $this->getDatos($sql3,false,$params);

        return $datos;
    }

    public function getTrimestre($mes)
    {
        $meses = array('ENERO-FEBRERO-MARZO', 'ABRIL-MAYO-JUNIO', 'JULIO-AGOSTO-SEPTIEMBRE', 'OCTUBRE-NOVIEMBRE-DICIEMBRE');
        $r = new stdClass();
        switch ($mes) {
            case 1:
            case 2:
            case 3:
                $r->bim = explode("-", $meses[0]);
                $r->meses = array(1, 2, 3);
                $r->bm = 1;
                break;
            case 4:
            case 5:
            case 6:
                $r->bim = explode("-", $meses[1]);
                $r->bm = 2;
                $r->meses = array(4, 5, 6);
                break;
            case 7:
            case 8:
            case 9:
                $r->bim = explode("-", $meses[2]);
                $r->meses = array(7, 8, 9);
                $r->bm = 3;
                break;
            case 10:
            case 11:
            case 12:
                $r->bim = explode("-", $meses[3]);
                $r->meses = array(10, 11, 12);
                $r->bm = 4;
                break;
        }
        $r->mes1 = strtolower(substr($r->bim[0], 0, 3));
        $r->mes2 = strtolower(substr($r->bim[1], 0, 3));
        $r->mes3 = strtolower(substr($r->bim[2], 0, 3));
        $r->tri = $meses[($r->bm) - 1];
        return $r;
    }

    public function fnGeneraCompromisoAfectacion($prm, $afectacion)
    {
        $data = new stdClass();
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $data->epg_id = $prm->epg_id;
        $data->ecf_id = $prm->ecf_id;
        $data->pg_id = $prm->pg_id;
        $data->pe_id = $prm->pe_id;
        $data->ue_id = $afectacion['ue_id'];
        $data->tcp_id = $afectacion['tcp_id'];
        $data->tcl_id = $afectacion['tcl_id'];
        $data->stc_id = $afectacion['stc_id'];
        $data->fechaInicio = $afectacion['afe_fechaInicio'];
        $data->fechaFin = $afectacion['afe_fechaFin'];
        $data->concepto = str_replace("'", "\'", $prm->concepto);
        $data->importe = $prm->percepcionNeto;
        $data->iva_id = $prm->iva_id;
        $data->rfc_id = $prm->rfc_id;
        $data->tpd_id = $prm->tpd_id;
        if ($afectacion['tcl_id'] == "A") {
            $data->tno_id = $afectacion['tno_id'];
            $data->mno_id = $afectacion['mno_id'];
            $data->smn_id = $afectacion['smn_id'];
        }
        $data->numOrden = $prm->referencia;
        $data->fechaOrden = $prm->fechaReferencia;
        $data->fechaAutorizado = date("Y-m-d");
        $data->tipoMovimiento = '+';
        $data->abc = "a";

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ?
        $_SESSION[_S]['ses_ip'] : '192.168.1.254';

        $user = $_SESSION[_S]['usr_id'];
        $cmp_id = 0;
        $data->gpo_id = $gpo_id;
        $data->ur_id = $ur_id;
        $cmp_id = Compromiso::abc($data);

        $this->conectar();
        $this->setSql('
            SET @usr_ip = INET_ATON(:ip),
                @usr_id = :usr,
                @bit_observacion = "AUTORIZACION DEL COMPROMISO DESDE LA AFECTACION -CAPTURA MANUAL "');
        $params = [
            ':ip' => $ip,
            ':usr' => $user
        ];
        $this->consultar($params);
        $this->setSql(' CALL sp_AutorizarCompromiso' . APP_EJERCICIO . '(:gpo_id,:ur_id,:cmp_id,INET_ATON(:ip),:usr) ');
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':cmp_id' => $cmp_id,
            ':ip' => $ip,
            ':usr' => $user
        ];
        $this->consultar($params);
        $this->setSql('SELECT @banderaPresupuesto AS bandera,@banderaContabilidad AS banderaC,@mensajeContabilidad AS mensajeC, @mensajePresupuesto AS mensaje');
        $this->consultar();
        $res = $this->extraer();
        if ($res['bandera'] != 1) {
            $this->setSql('DELETE FROM scp_Compromiso WHERE cmp_id= ?');
            $params = [$cmp_id];
            $this->consultar($params);
        } else {
            $sqlb = '
                INSERT INTO ' . BD_BITACORA . '.bit_Compromiso
                SET gpo_id = :gpo_id,
                    ur_id = :ur_id,
                    cmp_id = :cmp_id,
                    ecm_id = "G",
                    bit_observacion = "Compromiso generado desde Afectacion Presupuestal",
                    usr_ip = INET_ATON(:ip),
                    usr_id = :usr';
            $params = [
                ':gpo_id' => $gpo_id,
                ':ur_id' => $ur_id,
                ':cmp_id' => $cmp_id,
                ':ip' => $ip,
                ':usr' => $user
            ];
            $this->setSql($sqlb);
            $this->consultar($params);
        }
        $this->desconectar();

        $r = new stdClass();
        $r->cmp_id = $cmp_id;
        $r->bandera = $res['bandera'];
        $r->mensaje = $res['mensaje'];
        $r->banderaC = $res['banderaC'];
        $r->mensajeC = $res['mensajeC'];
        return $r;
    }

    public function getInfoContableAfe($abc, $afe_id, $ncc_id)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $params = [];
        $sql = '
            SELECT
            ecf_id,afe_id,clc_id, tcl_id,tcl_nombre,tno_id,stc_id,stc_nombre, MONTH(afe_fechaElaboracion) as Mes,MONTH(now()) AS Actual,
            eaf_id,DAY(NOW()) AS DiaActual,cfi_anio,cfi_clave,cfi_nombre,afe_fechaInicio, afe_fechaFin, epg_id,ecf_id,ue_id,tcp_id,
            afe_fondoRotatorio,tcp_nombre,tno_nombre,mno_id,mno_nombre';

        if ($abc == "c") {
            $sql .= ',CONCAT(ger_id,cgp_id,rub_id,cgcu_id,C.cgsc_id,".",cta_gpo_id,cta_ur_id'
                . ',".",cta_ue_id,".",cgc_id,".",cta_id)
            AS cta_cuenta,cta_nombre,rfc_id,rfc_nombre,rfc_paterno,rfc_materno,soc_nombre
            ,C.usr_id';
        }

        $sql .= '
            FROM scp_Afectacion A
            INNER JOIN cat_TipoClc USING ( tcl_id )
            INNER JOIN cat_SubtipoClc USING ( tcl_id,stc_id )
            INNER JOIN enc_ClaveFinanciamiento USING( ecf_id )
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING ( cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio )
            LEFT JOIN cat_TipoCompra USING( tcp_id )
            LEFT JOIN cat_TipoNomina USING ( tno_id )
            LEFT JOIN cat_ModalidadNomina USING( mno_id ) ';
        if ($abc == "c") {
            $sql .= ' LEFT JOIN scp_Contable C USING( gpo_id,ur_id,clc_id,afe_id)
                    INNER JOIN cat_Proveedor USING ( rfc_id )
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING ( rfc_id )
                    INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor  USING ( soc_id )
                    LEFT JOIN cg_PlanCuentas P USING( ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id )';
        }

        $sql .= '
            WHERE A.gpo_id = :gpo_id 
            AND A.ur_id = :ur_id
            AND afe_id = :afe_id
        ';
        if ($abc == "c") {
            $sql .= '
                AND ncc_id = :ncc_id
            ';
            $params +=[
                ':ncc_id' => $ncc_id,
            ];
        }
        $params += [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe_id
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'][0];
    }

    public function getAutorizaAfectaciones($afes)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $sql = '
        SELECT A.gpo_id,A.ur_id,A.afe_id,A.eaf_id,
            A.ue_id,oa_nombre AS AAC,ecf_clave AS clavefin,
            A.afe_percepcion,A.afe_retencion,A.afe_fechaElaboracion,
            MONTH(A.afe_fechaElaboracion) AS MES,A.tem_carga,
            tcl_id,stc_id,ecf_id,
            Detalle,Percepcion,Retencion,ecr_id
        FROM scp_Afectacion  A
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
        LEFT JOIN enc_Programatico USING(gpo_id,ur_id,epg_id)
        LEFT JOIN cat_ObraActividad  USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id,afe_id,SUM(percepcion) AS Percepcion, SUM(Retencion) AS Retencion,COUNT(Det.afe_id) AS Detalle
            FROM(
                SELECT gpo_id,ur_id,afe_id,ncp_percepcionNeto AS Percepcion,0 as Retencion
                FROM scp_Presupuestal
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id IN (' . $afes . ')
                UNION ALL
                SELECT gpo_id,ur_id,afe_id,ncc_percepcion AS Percepcion,ncc_retencion as Retencion
                FROM scp_Contable
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id IN (' . $afes . ')
            )Det
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id IN (' . $afes . ')
            GROUP BY gpo_id,ur_id,afe_id
        )Num  USING ( gpo_id,ur_id,afe_id)
        LEFT JOIN cat_CargaMasiva m ON A.tem_carga = m.tem_carga
        WHERE A.gpo_id = :gpo_id AND A.ur_id = :ur_id AND afe_id IN (' . $afes . ')
        ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id];
        $datos = $this->getDatos($sql,false,$params);
        return $datos;
    }

    public function getPreDevDisponible($afe, $tri)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
            SELECT
                gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,
                pre_ClaveCompleta,
                SUM(ncp_percepcionNeto) AS Percepcion,
                (vig_' . $tri->mes1 . '+vig_' . $tri->mes2 . ' + vig_' . $tri->mes3 . ') AS Modificado,
                (dev_' . $tri->mes1 . ' + dev_' . $tri->mes2 . ' + dev_' . $tri->mes3 . ') AS Devengado,
                (com_ene+com_feb+com_mar+com_abr+com_may+com_jun+com_jul+com_ago+com_sep+com_oct+com_nov+com_dic) AS Comprometido,
                pre_mov
            FROM scp_Presupuestal
            INNER JOIN presupuesto USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            INNER JOIN presupuestoComprometido USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            INNER JOIN presupuestoVigente USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            INNER JOIN presupuestoDevengado USING( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND afe_id = :afe_id
            GROUP BY gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,pre_ClaveCompleta
            HAVING Percepcion > (Modificado - Devengado) OR pre_mov ="M" OR (Devengado+Percepcion)>Comprometido
        ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':afe_id' => $afe['afe_id']
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos;
    }

    public function importesValidaCompromiso($cmp, $afe)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $sql = '
        SELECT cmp_id,ecm_id,cmp_importe,Devengado,aDevengar,Ajuste
        FROM scp_Compromiso
        LEFT JOIN (
            SELECT gpo_id,ur_id,cmp_id,SUM(ncp_percepcionNeto) AS Devengado
            FROM scp_Presupuestal
            INNER JOIN scp_Afectacion USING(  gpo_id,ur_id,clc_id,afe_id)
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND cmp_id = :cmp_id AND afe_id != :afe_id
            AND eaf_id ="A"
        )Dev  USING ( gpo_id,ur_id,cmp_id )
        LEFT JOIN (
            SELECT
                gpo_id,ur_id,cmp_cmp_id AS cmp_id ,
                SUM(IF(cmp_tipoCompromiso="-",cmp_importe*-1,cmp_importe)) AS Ajuste
            FROM scp_Compromiso
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                AND cmp_tipoCompromiso="A"
                AND cmp_cmp_id = :cmp_id
                AND ecm_id ="A"
        )Aj USING ( gpo_id,ur_id,cmp_id )
        LEFT JOIN (
            SELECT gpo_id,ur_id,cmp_id,SUM(ncp_percepcionNeto) AS aDevengar
            FROM scp_Presupuestal
            INNER JOIN scp_Afectacion USING(  gpo_id,ur_id,clc_id,afe_id)
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND cmp_id = :cmp_id AND afe_id = :afe_id
            AND eaf_id ="A"
        )AD USING ( gpo_id,ur_id,cmp_id )
        WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND cmp_id = :cmp_id ';
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id' => $ur_id,
            ':cmp_id' => $cmp,
            ':afe_id' => $afe
        ];
        $datos = $this->getDatos($sql,false,$params);
        return $datos['datos'][0];
    }
}