<?php
session_start();
ini_set('memory_limit', '150M');
set_time_limit(0);
require_once dirname(__FILE__).'/../../../../lib/helpers.php';
/* TODA LA LA PARATE DE ESTA PANTALLA ES CASI LO MISMO QUE EL REPORTE CON ALGUNAS EXCEPCIONES QUE SE COMENTARA PERO EL PROCESAMIENTO DE LA INFROMACION
A IMPRIMIR ES LA MISMA QUE EL RESPORTE EN PDF. */
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/Math.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';

/* ESTA ES LA PARTE IMPORTANTE PARA QUE PUEDA DECIRLE QUE SE VA A GENERAR EN EXCEL. SE LE DICE LA RUTA O NOMBRE QUE TENDRA EL ARCHIVO,
QUE TIPO DE VERSIONDE EXCEL SE CREARA. */
$extension = ".xls";
$ruta = utf8Decode("AUXILIAR" . $extension);
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");
/* FIN */
$params = [];
$p = [];
$gpo_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
$ur_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
$ue_id = '';
if ($_SESSION[_S]['nvj_id'] == 1) {
    $ue_id = $_SESSION[_S]['ue_id'];
} else {
    $ue_id = (($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116
        && isset($_SESSION[_S]['clasAdm-ue']) && !is_null($_SESSION[_S]['clasAdm-ue']))
        ? $_SESSION[_S]['clasAdm-ue'] : '%');
}

$ca = new AccesoDatos();
// Si en cat_CargaTrabajo tiene ue_id == null entonces NO está limitado por UE
// Pero si sí está definido un valor en ése campo, quiere decir que sí debemos limitarlo a dichas UEs
$ca->setSql("SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = ? ");
$ca->conectar();
$ca->consultar(array( $_SESSION[_S]['usr_id']));
$numRegistros = $ca->getNumeroRegistros();
$row = $ca->extraer();
$ca->desconectar();

/*
18/05/2016 se agregó la validación por carga de trabajo.
 */
$ct = '';
if ($numRegistros > 0) {
    if (is_null($row['ue_id'])) {
        $ct = '';
    } else {
        $ct = 'INNER JOIN ( SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = :usr_id ) ct USING( ue_id )';
        $params[':usr_id'] = $_SESSION[_S]['usr_id'];
    }
}

$ur = new UnidadResponsable();
$ur->setGpo_id($gpo_id);
$ur->setUr_id($ur_id);
$ur->select();
$contt = '';
$masivo = isset($_POST['masivo']) ? $_POST['masivo'] : 0; //Informacion del tipo de busqueda
$cuenta2 = isset($_POST['ctareg_id']) ? $_POST['ctareg_id'] : ""; //Informacion del tipo de busqueda
$busqueda = isset($_POST['radcta']) ? $_POST['radcta'] : -1; //Informacion del tipo de busqueda
$mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde'] : ""; //Informacion de acuerdo al mes
$mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : ""; //Informacion de acuerdo al mes
$cuentaDesde = isset($_POST['comboDesde']) ? $_POST['comboDesde'] : ""; //Informacion de acuerdo a la cuenta
$cuentaHasta = isset($_POST['comboHasta']) ? $_POST['comboHasta'] : ""; //Informacion de acuerdo a la cuenta
$cta2rango = isset($_POST['cta2']) ? $_POST['cta2'] : ""; //Informacion de acuerdo a la cuenta
$cuenta = isset($_POST['cta']) ? $_POST['cta'] : ""; // busqueda por cuenta
$adf = isset($_POST['adf_id']) ? $_POST['adf_id'] : ""; //cat_agrupacion
$tipo = isset($_POST['pot_id']) ? $_POST['pot_id'] : ""; //tipo de poliza
$cjaconep = (isset($_POST['ccm']) && $_POST['ccm'] != '') ? $_POST['ccm'] : ""; //conceptos
$cjaMmc = (isset($_POST['mmc-id']) && count($_POST['mmc-id'])) ? $_POST['mmc-id']
: array(); //Momentos contables
$orden = isset($_POST['ofe']) ? $_POST['ofe'] : ""; // ordenar la consulta
$fe = isset($_POST['fe']) ? "on" : ""; //Flujo de Efectivo 
$pc = isset($_POST['pc_']) ? $_POST['pc_'] : ""; //Pólizas Canceladas 
$deue = ((isset($_POST['comboue']) && $_POST['comboue'] != "") ? $_POST['comboue']
    : "");
$claveFinanciamiento = isset($_POST['cfi']) ? $_POST['cfi'] : ""; // busqueda por clave de financiamiento
$claveFinanciamientoclave = isset($_POST['cfi_clave']) ? $_POST['cfi_clave'] : ""; // busqueda por clave de financiamiento
$claveFinanciamiento = ($claveFinanciamiento != "") ? " AND cta_id = '" . $claveFinanciamiento . "'"
: "";
$nivelusu = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : -1;
$hoy = date("Y-m-d H:i:s");
$busquedaRangoRegistro = "";
if ($busqueda == "N") {
    $busqueda = "R";
    $busquedaRangoRegistro = "N";
    $cuentaDesde = $cuenta;
    $cuentaHasta = $cta2rango;
}

if ($masivo == 1) {
    $busqueda = "C";
    $cuenta = $cuenta2;
    $cuentaDesde = '';
    $cuentaHasta = '';
}
$col_rep = 0;
$df = array();
$sql_df = "";
if (isset($_POST['df_tdf'])) {
    $df['tdf_id'] = $_POST['df_tdf']; //tipo doct fuente
    $sql_df = ",tdf_id";
    $col_rep++;
}

if (isset($_POST['df_id'])) {
    $df['pdx_bdo_id_origen'] = $_POST['df_id']; //id docto fuente
    $sql_df .= ",pdx_bdo_id_origen";
    $col_rep++;
}

if (isset($_POST['df_rfc'])) {
    $df['rfc_id'] = $_POST['df_rfc']; //rfc docto fuente
    $sql_df .= ",rfc_id";
    $col_rep++;
}

if (isset($_POST['df_ref'])) {
    $df['pdx_referencia'] = $_POST['df_ref']; //referencia doct fuente
    $sql_df .= ",pdx_referencia";
    $col_rep++;
}

if (isset($_POST['df_fref'])) {
    $df['pdx_fechaReferencia'] = $_POST['df_fref']; //fecha referencia docto fuente
    $sql_df .= ",pdx_fechaReferencia";
    $col_rep++;
}

$cve_pre = array();
$sql_cvepre = '';
$join_cvepre = '';
if (isset($_POST['cve_ur'])) {
    $cve_pre['pdx_cve_ur_origen'] = $_POST['cve_ur']; //grupo de la clave_presupuestal
    $sql_cvepre .= ",concat(pdx_gpo_id_origen,lpad(pdx_ur_id_origen,2,0)) cve_ur";
    $col_rep++;
}

if (isset($_POST['cve_epg'])) {
    $cve_pre['epg_id'] = $_POST['cve_epg']; //EPG de la clave_presupuestal
    $sql_cvepre .= ",e.epg_clave , oa_nombre, oa.oa_numObra oa_numObra, p_or.ore_oficioAut ore_oficioAut, oc.obr_contrato obr_contrato ";

    $join_cvepre .= 'LEFT JOIN enc_Programatico e on d.epg_id = e.epg_id ';
    $join_cvepre .= 'LEFT JOIN poa_OrigenRecurso p_or on e.gpo_id = p_or.gpo_id AND e.ur_id = p_or.ur_id AND e.epg_id = p_or.epg_id AND  p_or.ecf_id = d.ecf_id ';
    $join_cvepre .= 'LEFT JOIN cat_ObraActividad oa on e.gpo_id = oa.gpo_id and e.ur_id = oa.ur_id and e.prg_id = oa.prg_id and e.spr_id = oa.spr_id and e.py_id = oa.py_id and e.oa_id = oa.oa_id ';
    $join_cvepre .= 'LEFT JOIN scp_Clc c on d.pdx_gpo_id_origen = c.gpo_id AND d.pdx_ur_id_origen = c.ur_id AND d.pdx_clc_id_origen = c.clc_id ';
    $join_cvepre .= 'LEFT JOIN cat_ObraClc oc on c.gpo_id  = oc.gpo_id
        and  c.ur_id  = oc.ur_id and c.clc_id  = oc.clc_id  ';

    $col_rep += 5;

    if (isset($_POST['cve_ue'])) {
        $cve_pre['ue_id'] = $_POST['cve_ue']; //UE de la clave_presupuestal
        $sql_cvepre .= ",oa.ue_id oa_ue_id  ";
        $col_rep++;
    }
}

if (isset($_POST['cve_pg'])) {
    $cve_pre['pg_id'] = $_POST['cve_pg']; //PG de la clave_presupuestal
    $sql_cvepre .= ",pg_id";
    $col_rep++;
}

if (isset($_POST['cve_pe'])) {
    $cve_pre['pe_id'] = $_POST['cve_pe']; //PE de la clave_presupuestal
    $sql_cvepre .= ",pe_id";
    $col_rep++;
}

if (isset($_POST['cve_cfi'])) {
    $cve_pre['ecf_clave'] = $_POST['cve_cfi']; //CVE_FIN de la clave_presupuestal
    $sql_cvepre .= ",ecf_clave";
    $join_cvepre .= 'LEFT JOIN enc_ClaveFinanciamiento ON d.ecf_id = enc_ClaveFinanciamiento.ecf_id';
    $col_rep++;
}

$ori = array();
$sql_ori = '';
$sqloriwhere = '';
$sqlclcori = '';
if (isset($_POST['ori_ur'])) {
    $ori['ori_ur'] = $_POST['ori_ur']; //grupo del documento origen
    $sql_ori .= ",concat(pdx_gpo_id_origen,lpad(pdx_ur_id_origen,2,0)) ori_ur";
    if (isset($_POST['ori_ur_id']) && $_POST['ori_ur_id'] != '') {
        $sqloriwhere .= " AND concat(pdx_gpo_id_origen,pdx_ur_id_origen)=" . $_POST['ori_ur_id'] . " ";
    }
    $col_rep++;
}

if (isset($_POST['ori_clc'])) {
    $ori['pdx_clc_id_origen'] = $_POST['ori_clc']; //CLC del documento origen
    $sql_ori .= ",pdx_clc_id_origen";

    if (isset($_POST['oriclc_id']) && $_POST['oriclc_id'] != '') {
        $sqloriwhere .= " AND pdx_clc_id_origen =" . $_POST['oriclc_id'] . " ";
    }
    $col_rep++;
}

if (isset($_POST['ori_afe'])) {
    $ori['pdx_afe_id_origen'] = $_POST['ori_afe']; //AFE del documento origen
    $sql_ori .= ",pdx_afe_id_origen";
    $col_rep++;
}

if (isset($_POST['ori_cmp'])) {
    $ori['pdx_cmp_id_origen'] = $_POST['ori_cmp']; //CMP del documento origen
    $sql_ori .= ",pdx_cmp_id_origen ";
    $col_rep++;
}

if (isset($_POST['ori_cereco'])) {
    $ori['clc_cereCo'] = $_POST['ori_cereco']; //CMP del documento origen
    $sql_ori .= ",axuclc.clc_cereCo  as clc_cereCo";
    $sqlclcori .= " LEFT JOIN scp_ClcAuxiliar axuclc on d.pdx_gpo_id_origen = axuclc.gpo_id AND d.pdx_ur_id_origen = axuclc.ur_id AND d.pdx_clc_id_origen = axuclc.clc_id ";
    $col_rep++;
}

$vin = array();
$sql_vin = "";
if (isset($_POST['vin_ur'])) {
    $vin['vin_ur'] = $_POST['vin_ur']; //grupo, Ur del documento vinculacion
    $sql_vin .= ",concat(pdx_gpo_id_vinculacion,lpad(pdx_ur_id_vinculacion,2,0)) vin_ur";
    $col_rep++;
}

if (isset($_POST['vin_clc'])) {
    $vin['pdx_clc_id_vinculacion'] = $_POST['vin_clc']; //CLC del documento vinculacion
    $sql_vin .= ",pdx_clc_id_vinculacion";
    $col_rep++;
}

if (isset($_POST['vin_afe'])) {
    $vin['pdx_afe_id_vinculacion'] = $_POST['vin_afe']; //AFE del documento vinculacion
    $sql_vin .= ",pdx_afe_id_vinculacion";
    $col_rep++;
}

if (isset($_POST['vin_cmp'])) {
    $vin['pdx_cmp_id_vinculacion'] = $_POST['vin_cmp']; //CMP del documento vinculacion
    $sql_vin .= ",pdx_cmp_id_vinculacion";
    $col_rep++;
}

if (isset($_POST['vin_id'])) {
    $vin['pdx_bdo_id_vinculacion'] = $_POST['vin_id']; //BDO_ID del documento vinculacion
    $sql_vin .= ",pdx_bdo_id_vinculacion ";
    $col_rep++;
}

if (isset($_POST['vin_cereco'])) {
    $vin['pdx_clc_cereCo_vinculacion'] = $_POST['vin_cereco']; //CLC del documento origen
    $sql_ori .= ",pdx_clc_cereCo_vinculacion ";
    $col_rep++;
}

//se comienza a  formar el encabezado
echo '<table align="center" width="100%">';
echo '<tr>';
echo '<td colspan="' . (11 + $col_rep) . '" align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="' . (11 + $col_rep) . '" align="CENTER" ><b>PRESUPUESTO DE EGRESOS ' . substr($_SESSION[_S]['ejercicio'],
    0, 4) . '</b></td>';
echo '</tr>';

if ($nivelusu >= 3) {
    $texto = "DIRECCION DE CONTABILIDAD GUBERNAMENTAL: " . $ur->getClave() . " - " . utf8Decode($ur->getNombre());

    echo '<tr>';
    echo '<td colspan="' . (11 + $col_rep) . '" align="CENTER" ><b>' . $texto . '</b></td>';
    echo '</tr>';
} else {
    $texto = "UR: " . $ur->getClave() . " - " . utf8Decode($ur->getNombre());
    echo '<tr>';
    echo '<td colspan="' . (11 + $col_rep) . '" align="CENTER" ><b>' . $texto . '</b></td>';
    echo '</tr>';
}

$nivel = '';
$selectsap = "";
$joinsap = "";

if ($gpo_id . $ur_id == 116) {
    $selectsap = ", case  when cgsc_cuenta='1.1.1.2.1' then cta_sap
                           when cgsc_cuenta='1.1.1.4.1' then cta_sap
                    else ''
                        end as cta_sap
                    ";
    $joinsap = "  LEFT JOIN (
                        SELECT cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,cgc_id, cta_id, GROUP_CONCAT(cta_sap) AS cta_sap
                        FROM ".BD_TESORERIA.".sap_PlanCuentas
                        GROUP BY cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,cgc_id, cta_id
                    ) AS spc USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,cgc_id, cta_id ) ";

}
if ($cuentaDesde != "" && $cuentaHasta != "") {
    $arrayCuentas = explode('.', $cuentaDesde);
    $ger = $arrayCuentas[0];
    $cgp = $arrayCuentas[1];
    $rub = $arrayCuentas[2];
    $cgcu = $arrayCuentas[3];
    $cgsc = $arrayCuentas[4];

    $titucta = $cuentaDesde . ' A LA ' . $cuentaHasta;
} else {

    $titucta = "";

    $arrayCuenta = explode('.', $cuenta);
    $cgm = $arrayCuenta[0] . $arrayCuenta[1] . $arrayCuenta[2] . $arrayCuenta[3] . $arrayCuenta[4];
    $ger = substr($cgm, 0, 1);
    $cgp = substr($cgm, 1, 1);
    $rub = substr($cgm, 2, 1);
    $cgcu = substr($cgm, 3, 1);
    $cgsc = substr($cgm, 4);
    $cgc = '';
    $cgq = '';
    $cta = '';
    if (isset($arrayCuenta[5])) {
        $cgc = substr($arrayCuenta[5], 0);
    }

    if (isset($arrayCuenta[6])) {
        $cgq = substr($arrayCuenta[6], 0);
    }

    if (strlen($cgq) > 2) {
        $cta = $cgq;
    }

    if (isset($arrayCuenta[7])) {
        $cta = substr($arrayCuenta[7], 0);
    }

    if (strlen($cgq . $cta) == 6) {
        $cta = $cgq . $cta;
    }

    if (strlen($cta) >= 4) {
        $p = [];
        $sql = '
                SELECT cta_nombre AS cuenta, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id
                    FROM cg_PlanCuentas
                    WHERE cta_gpo_id = :gpo_id
                    AND cta_ur_id = :ur_id
                    ' . (($ue_id != "" && $ue_id != "%") ? ' AND cta_ue_id = :ue_id' : '') . '
                    AND ger_id = :ger
                    AND cgp_id = :cgp
                    AND rub_id = :rub
                    AND cgcu_id = :cgcu
                    AND cgsc_id = :cgsc
                    AND cgc_id = :cgc
                    AND cta_id = :cta';
        $p[':gpo_id'] = $gpo_id;
        $p[':ur_id'] = $ur_id;
        $p[':ger'] = $ger;
        $p[':cgp'] = $cgp;
        $p[':rub'] = $rub;
        $p[':cgcu'] = $cgcu;
        $p[':cgsc'] = $cgsc;
        $p[':cgc'] = $cgc;
        $p[':cta'] = $cta;
        if ($ue_id != "" && $ue_id != "%") {
            $p[':ue_id'] = $ue_id;
        }
        $nivel = 8;
    } elseif (strlen($cgq) == 2) {
        $p = [];
        $sql = '
            SELECT cgq_nombre AS cuenta, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgq_id
                FROM cg_CuentaQuinto
                WHERE ger_id = :ger
                AND cgp_id = :cgp
                AND rub_id = :rub
                AND cgcu_id = :cgcu
                AND cgsc_id = :cgsc
                AND cgc_id = :cgc
                AND cgq_id = :cgq
                ';
        $p[':ger'] = $ger;
        $p[':cgp'] = $cgp;
        $p[':rub'] = $rub;
        $p[':cgcu'] = $cgcu;
        $p[':cgsc'] = $cgsc;
        $p[':cgc'] = $cgc;
        $p[':cgq'] = $cgq;
        $nivel = 7;
    } elseif (strlen($cgc) == 3) {
        $p = [];
        $sql = '
            SELECT cgc_nombre AS cuenta, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id
                FROM cg_CuentaCuarto
                WHERE ger_id = :ger
                AND cgp_id = :cgp
                AND rub_id = :rub
                AND cgcu_id = :cgcu
                AND cgsc_id = :cgsc
                AND cgc_id = :cgc
                ';
        $p[':ger'] = $ger;
        $p[':cgp'] = $cgp;
        $p[':rub'] = $rub;
        $p[':cgcu'] = $cgcu;
        $p[':cgsc'] = $cgsc;
        $p[':cgc'] = $cgc;       
        $nivel = 6;
    } elseif (strlen($cgm) == 5) {
        $p = [];
        $sql = '
            SELECT cgc_nombre AS cuenta, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id
                FROM cg_CuentaCuarto
                WHERE ger_id = :ger
                AND cgp_id = :cgp
                AND rub_id = :rub
                AND cgcu_id = :cgcu
                AND cgsc_id = :cgsc
                ';
        $p[':ger'] = $ger;
        $p[':cgp'] = $cgp;
        $p[':rub'] = $rub;
        $p[':cgcu'] = $cgcu;
        $p[':cgsc'] = $cgsc;
        $nivel = 5;
    }
    $consultarH = new AccesoDatos();
    $consultarH->conectar();
    $consultarH->setSql($sql);
    $consultarH->consultar($p);
    $consultarH->desconectar();
    $row = $consultarH->extraer();

    if (isset($cuenta) && $cuenta != '') {
        $titucta = $cuenta . '-' . $row['cuenta'];
    }

}

if ($mesDesde == 0 and $mesHasta == 0) {
    echo '<tr>';
    echo '<td colspan="' . (11 + $col_rep) . '"  align="left" style="font-size: 10px;"><b>LIBRO AUXILIAR CORRESPONDIENTE A LA CUENTA ' . utf8Decode($titucta) . '  DEL MES DE APERTURA ' . substr($_SESSION[_S]['ejercicio'],
        0, 4) . '<b/></td>';
    echo '</tr>';
} else if ($mesHasta == $mesDesde) {
    echo '<tr>';
    echo '<td colspan="' . (11 + $col_rep) . '"  align="center" style="font-size: 12px;"><b>LIBRO AUXILIAR CORRESPONDIENTE A LA CUENTA ' . utf8Decode($titucta) . '  DEL MES DE ' . MesNombre($mesDesde) . ' DEL ' . substr($_SESSION[_S]['ejercicio'],
        0, 4) . ' </b></td>';
    echo '</tr>';
} else {
    echo '<tr>';
    echo '<td  colspan="' . (11 + $col_rep) . '" align="CENTER" ><b>LIBRO AUXILIAR CORRESPONDIENTE A LA CUENTA ' . utf8Decode($titucta) . '  DEL PERIODO DE ' . MesNombre($mesDesde) . '-' . MesNombre($mesHasta) . ' DEL ' . substr($_SESSION[_S]['ejercicio'],
        0, 4) . '</b> </td>';
    echo '</tr>';
}

if ($claveFinanciamiento != "") {
    echo '<tr>';
    echo '<td colspan="' . (11 + $col_rep) . '"  align="center" style="font-size: 12px;"><b> ' . utf8Decode($claveFinanciamientoclave) . '</b> </td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan=' . (11 + $col_rep) . '></td>';
echo '</tr>';

if (count($df) > 1 || count($cve_pre) > 1 || count($ori) > 1 || count($vin) > 1) {
    echo '<tr>';
    echo '<td  colspan="5" style="font-size: 12.5; background-color: #003366; color: white;" ></td>';

    echo '<td colspan="" style="font-size: 12.5; background-color: #003366; color: white;"></td>';

    if (count($df)) {
        echo '<td align="center" colspan="' . count($df) . '" style="font-size: 12.5; background-color: #003366; color: white;"><b>DOCTO FUENTE</b></td>';
    }

    if (count($cve_pre)) {

        echo '<td align="center" colspan="' . (count($cve_pre) + (isset($cve_pre['epg_id'])
            ? 4 : 0)) . '" style="font-size: 12.5; background-color: #003366; color: white;"><b>CVE PRESUPUESTAL</b></td>';
    }

    if (count($ori)) {
        echo '<td align="center" colspan="' . count($ori) . '" style="font-size: 12.5; background-color: #003366; color: white;"><b>ORIGEN</b></td>';
    }

    if (count($vin)) {
        echo '<td align="center" colspan="' . count($vin) . '" style="font-size: 12.5; background-color: #003366; color: white;"><b>VINCULACION</b></td>';
    }

    echo '<td colspan="5" style="font-size: 12.5; background-color: #003366; color: white;"></td>';
    echo '</tr>';
}

echo ' <tr>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>POLIZA</td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>UE</td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>FECHA</td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>CUENTA CONTABLE</td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>NOMBRE DE LA CUENTA</td>';

echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>COMENTARIO</b></b></td>';

if (count($df)) {
    if (isset($df['tdf_id'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>TIPO DF</b></td>';
    }

    if (isset($df['pdx_bdo_id_origen'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>ID DF</b></td>';
    }

    if (isset($df['rfc_id'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>RFC</b></td>';
    }

    if (isset($df['pdx_referencia'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>NUMERO DF</b></td>';
    }

    if (isset($df['pdx_fechaReferencia'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>FECHA DF</b></td>';
    }

}

if (count($cve_pre)) {
    if (isset($cve_pre['pdx_cve_ur_origen'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>UR.</b></td>';
    }

    if (isset($cve_pre['ue_id'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>UE</b></td>';
    }

    if (isset($cve_pre['epg_id'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>ENC. PROG</b></td>';
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>OBRA:</b></td>';
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>OF. DE AUT.:</b></td>';
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>N&ordm; OBRA SSPP:</b></td>';
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>OBRA POR CONTRATO N&ordm;:</b></td>';
    }

    if (isset($cve_pre['pg_id'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>PG</b></td>';
    }

    if (isset($cve_pre['pe_id'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>PE</b></td>';
    }

    if (isset($cve_pre['ecf_clave'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>CVE. FINANC.</b></td>';
    }

}

if (count($ori)) {
    if (isset($ori['ori_ur'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>UR.</b></td>';
    }

    if (isset($ori['pdx_clc_id_origen'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>CLC</b></td>';
    }

    if (isset($ori['pdx_afe_id_origen'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>AFECTACION</b></td>';
    }

    if (isset($ori['pdx_cmp_id_origen'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>COMPROMISO</b></td>';
    }

    if (isset($ori['clc_cereCo'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>CERE-CO</b></td>';
    }

}

if (count($vin)) {
    if (isset($vin['vin_ur'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>UR.</b></td>';
    }

    if (isset($vin['pdx_clc_id_vinculacion'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>CLC</b></td>';
    }

    if (isset($vin['pdx_afe_id_vinculacion'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>AFECTACION</b></td>';
    }

    if (isset($vin['pdx_cmp_id_vinculacion'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>COMPROMISO</b></td>';
    }

    if (isset($vin['pdx_bdo_id_vinculacion'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>ID DOCUMENTO</b></td>';
    }

    if (isset($vin['pdx_clc_cereCo_vinculacion'])) {
        echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>CERE-CO</b></td>';
    }

}

echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>DEBE</b></td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>HABER</b></td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>SALDO</b></td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>MOMENTO C.</b></td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>EDO</b></td>';
if ($gpo_id . $ur_id == 116) {
    echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white;"><b>CUENTA SAP</b></td>';
}
echo '</tr>';

//EN ESTA PARTE SOLO VERIFICAMOS SI ELIGIERON UN TIPO DE POLIZA PARA REALIZAR EL FILTRO SI NO SE MUESTRAN TODOS LOS TIPOS DE POLIZAS
$poliza = ($tipo != '') ? "AND pot_id = '" . $tipo . "'" : "";
$periodo = "AND d.msc_id <= " . $mesHasta;
$periodo2 = ($mesDesde == $mesHasta) ? "AND d.msc_id = " . $mesDesde : "AND ( d.msc_id BETWEEN $mesDesde AND $mesHasta )";

//esto es solo para la UR 315 que en el 2012 era la 329 ellos pueden hacer los filtros por UR
// if ( isset( $_POST['soloUR'] ) && $_POST['soloUR'] != "" )
// {
//     $gpo_id = substr( $_POST['soloUR'],0,1);
//     $ur_id = substr( $_POST['soloUR'],1,2);
// }
// Ordenado por poliza (default) o por fecha
$ordenar = '';
if ($orden == 'on') {
    $ordenar = "ORDER BY pma_fecha";
} else {
    $ordenar = "ORDER BY pot_id, prf_id, pma_id";
}

// Si pidieron un grupo de cuentas
// ---------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------
// es para saber que tipo de busqueda viene  si es R es por rango si es C es por una cuenta....
$cuentas = '';

if ($busqueda == "R") {
    $params = [];
    if ($cuentaDesde == $cuentaHasta) {
        $cuentas = ' AND ger_id= :ger  AND cgp_id= :cgp  AND rub_id= :rub  AND cgcu_id= :cgcu  AND cgsc_id= :cgsc '. $cuentas;
        $params[':ger'] = $ger;
        $params[':cgp'] = $cgp;
        $params[':rub'] = $rub;
        $params[':cgcu'] = $cgcu;
        $params[':cgsc'] = $cgsc;
        
    } else {
        $cuentas = " AND ( cgsc_cuenta BETWEEN '$cuentaDesde' AND '$cuentaHasta' )";
    }
    if ($busquedaRangoRegistro == "N") {
        $cuentas = " AND ( cgsc_cuenta BETWEEN '$cuentaDesde' AND '$cuentaHasta' )";
    }
}

// Si pidieron UNA SOLA CUENTA
// ---------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------
else if ($busqueda == "C" && isset($cuenta) && $cuenta != '') {
    switch ($nivel) {
        case 8:
            $params = [];
            $cuentas = 'AND ger_id = :ger_id AND cgp_id = :cgp_id AND rub_id = :rub_id AND cgcu_id = :cgcu_id AND cgsc_id = :cgsc_id AND cgc_id = :cgc_id AND cta_id = :cta_id';
            $params[':ger_id'] = $ger;
            $params[':cgp_id'] = $cgp;
            $params[':rub_id'] = $rub;
            $params[':cgcu_id'] = $cgcu;
            $params[':cgsc_id'] = $cgsc;
            $params[':cgc_id'] = $cgc;
            $params[':cta_id'] = $cta;
            break;
        case 7:
            $params = [];
            $cuentas = 'AND ger_id = :ger_id AND cgp_id = :cgp_id AND rub_id = :rub_id AND cgcu_id = :cgcu_id AND cgsc_id = :cgsc_id AND cgc_id = :cgc_id
                    AND cta_id like "' . $cgq . '%" AND LENGTH(cta_id) = 6 ';
            $params[':ger_id'] = $ger;
            $params[':cgp_id'] = $cgp;
            $params[':rub_id'] = $rub;
            $params[':cgcu_id'] = $cgcu;
            $params[':cgsc_id'] = $cgsc;
            $params[':cgc_id'] = $cgc;
            break;
        case 6:
            $params = [];
            $cuentas = 'AND ger_id = :ger_id AND cgp_id = :cgp_id AND rub_id = :rub_id AND cgcu_id = :cgcu_id AND cgsc_id = :cgsc_id AND cgc_id = :cgc_id';
            $params[':ger_id'] = $ger;
            $params[':cgp_id'] = $cgp;
            $params[':rub_id'] = $rub;
            $params[':cgcu_id'] = $cgcu;
            $params[':cgsc_id'] = $cgsc;
            $params[':cgc_id'] = $cgc;
            break;
        case 5:
            $params = [];
            $cuentas = 'AND ger_id = :ger_id AND cgp_id = :cgp_id AND rub_id = :rub_id AND cgcu_id = :cgcu_id AND cgsc_id = :cgsc_id';
            $params[':ger_id'] = $ger;
            $params[':cgp_id'] = $cgp;
            $params[':rub_id'] = $rub;
            $params[':cgcu_id'] = $cgcu;
            $params[':cgsc_id'] = $cgsc;
            break;
    }
}

$mmc = "";
if (count($cjaMmc)) {
    if (count($cjaMmc) > 1) {
        $mmc = "AND mmc_id in(";
        foreach ($cjaMmc as $key => $value) {
            $mmc .= "'$value',";
        }
        $mmc = substr($mmc, 0, -1);
        $mmc .= ")";
    } else {
        $mmc = "AND mmc_id = '{$cjaMmc[0]}'";
    }

}

if ($deue != "" && $ue_id == '%') {
    $ue_id = $deue;
}

//Flujo de Efectivo
$opFe = '';
if ($fe == 'on') {
    $opFe =  " AND m.apg_id LIKE '%FE%' ";
}

//Pólizas Canceladass
$opCI = '';
$opCT = '';
if ($pc == 'on') {
    $opCI = " AND m.epl_id IN ('C','H') ";
    $opCT = " AND m.epl_id IN ('C','H') ";
}else{
    $opCI = " AND m.epl_id NOT IN ('C','H') ";
}

/* consulta actualizada del saldo inicial de la cuenta */
$consultar = new AccesoDatos();
$consultar->conectar();
$consultar->setSql("
        SELECT
           -- gpo_id, ur_id, cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id,
           -- d.msc_id msc_id, pot_id, c.nat_id, --d.nat_id,
           min(gpo_id) as gpo_id, min(ur_id) as ur_id, cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id,
             min(d.msc_id)  msc_id, min(pot_id) as pot_id, min(c.nat_id) as nat_id, -- d.nat_id,
            sum(if(ISNULL(d.msc_id), 0, if(d.msc_id < $mesDesde, pdx_debe, 0)) ) InicialDebe,
            sum(if(ISNULL(d.msc_id), 0, if(d.msc_id < $mesDesde, pdx_haber, 0)) ) InicialHaber,
           --  mmc_id, cta_cuenta, cta_nombre, cgsc_cuenta
            MAX(mmc_id) as mmc_id, cta_cuenta, cta_nombre, c.cgsc_cuenta
        FROM cg_PolizaDetalleAuxiliar d
            INNER JOIN cg_PolizaMaestro m using(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
            INNER JOIN cg_PlanCuentas p USING(cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
            INNER JOIN cat_CgSubcuenta c USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)
            $ct
        WHERE cta_gpo_id = $gpo_id and cta_ur_id = $ur_id " .
    ($ue_id != '%' ? "AND cta_ue_id = $ue_id" : "") . "
            $cuentas
            $periodo $poliza $mmc $opFe
            $opCI
            " . $claveFinanciamiento . $sqloriwhere . "
        GROUP BY cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id
	ORDER BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id, cta_ur_id, cta_gpo_id, cta_ue_id
        ;
    ");
//HASTA ESTA PARTE SOLO SE SACAN LAS CUENTAS POR RANGOS

$consultar->consultar($params);

$consultar->desconectar();

$cgp_id = '';
$cgcu_id = '';
$cgsc_id = '';
$gpo = '';
$ue = '';
$cgc = '';
$cta = '';
$numcuenta = '';
$docNumero = '';
$concep = '';
$debe = 0;
$haber = 0;
$naturaleza = '';

if ($consultar->getNumeroRegistros() == 0) {
    echo ' <tr>';
    echo '<td align="center" ><b></b></td>';
    echo '<td align="center" ></td>';
    echo '<td align="center" ></td>';
    echo '<td align="center" ></td>';
    echo '<td align="center" ><b></b></td>';
    echo '<td align="center" ><b>SIN REGISTRO</b></td>';
    echo '<td align="RIGHT" ><b></b></td>';
    echo '<td align="RIGHT" ><b></b></td>';
    echo '<td align="center" ></b></td>';
    echo '<td align="center" ></b></td>';
    echo '<td align="center" ></b></td>';
    echo '</tr>';
}

$inicial = true;
while ($datos = $consultar->extraer()) {

    if ($datos['nat_id'] == "D") {
        $saldo = $datos['InicialDebe'] - $datos['InicialHaber'];
    } else if ($datos['nat_id'] == "H") {
        $saldo = $datos['InicialHaber'] - $datos['InicialDebe'];
    }

    $naturaleza = $datos['nat_id'];

    if ($datos['ger_id'] != '' && $datos['cgp_id'] != '' && $datos['rub_id'] != ''
        && $datos['cgcu_id'] != '' && $datos['cgsc_id'] != '' && $datos['gpo_id']
        != '' && $datos['ur_id'] != '' && $datos['cta_ue_id'] != '' && $datos['cgc_id']
        != '' && $datos['cta_id'] != '') {
        $gpo = $datos['cta_gpo_id'];
        $ur = $datos['cta_ur_id'];
        $ue = $datos['cta_ue_id'];
        $ger_id = $datos['ger_id'];
        $cgp_id = $datos['cgp_id'];
        $rub_id = $datos['rub_id'];
        $cgcu_id = $datos['cgcu_id'];
        $cgsc_id = $datos['cgsc_id'];
        $cgc = $datos['cgc_id'];
        $cta = $datos['cta_id'];
    }

    $cuentas = 'AND ger_id=' . $ger_id . ' AND cgp_id=' . $cgp_id . ' AND rub_id=' . $rub_id . ' AND cgcu_id=' . $cgcu_id . ' AND cgsc_id=' . $cgsc_id
        . ' AND cgc_id = "' . $cgc . '" AND cta_id = "' . $cta . '"';

    $nombre = $ger_id . $cgp_id . $rub_id . $cgcu_id . $cgsc_id . $gpo . $ur . $ue . $cgc . $cta;

    if (!$inicial) {
        echo '<tr><td></td></tr>';
    }
    $inicial = false;

    echo ' <tr>';

    echo '<td align="right" colspan="2"  style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b>&nbsp;' . $datos['cta_ue_id'] . '</b></td>';
    echo '<td align="right" colspan="2"  style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b>' . $datos['cta_cuenta'] . '</b></td>';
    echo '<td align="center" colspan="2" style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b>' . utf8Decode($datos['cta_nombre']) . '</b></b></td>';

    $cols = count($df) + count($cve_pre) + (isset($cve_pre['epg_id']) ? 4 : 0) + count($ori)
     + count($vin);
    if ($cols) {
        echo '<td align="center" colspan="' . $cols . '" style="font-size: 12.5; background-color: #99CC99; color: white; "></td>';
    }

    echo '<td align="center" style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b></b></td>';
    echo '<td align="center" style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b></b></td>';
    echo '<td align="center" style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b>' . number_format($saldo,
        2) . '</b></td>';
    echo '<td align="center" style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b></b></td>';
    echo '<td align="center" style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b></b></td>';
    if ($gpo_id . $ur_id == 116) {
        echo '<td align="center" style="font-size: 12.5; background-color: #99CC99; color: #404040; "><b></b></td>';
    }
    echo '</tr>';

    $consultar2 = new AccesoDatos();
    $consultar2->conectar();
    $consultar2->setSql("
             SELECT
                d.gpo_id, d.ur_id ,d.cta_ue_id ,pot_id ,prf_id ,d.msc_id ,pma_id ,pdt_id ,pdx_id ,
                cta_gpo_id ,cta_ur_id ,cta_ue_id ,ger_id ,cgp_id ,rub_id ,cgcu_id ,cgsc_id ,cgc_id ,cta_id,
                pdx_debe ,pdx_haber ,d.nat_id ,pdx_concepto
                " . $sql_df . "
                " . $sql_cvepre . "
                " . $sql_ori . "
                " . $sql_vin . "
                ,d.ecf_id ecf_id, pdx_bdo_id_origen
                ,mmc_id ,epl_id, pma_concepto, if( pdx_concepto = '',pma_concepto, pdx_concepto ) concepto
                ,pma_fecha
                ,cta_cuenta ,cta_nombre ,d.nat_id ,cgsc_cuenta ,cgsc_nombre" . $selectsap . "
            FROM cg_PolizaDetalleAuxiliar d
                INNER JOIN cg_PolizaMaestro m using(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
                INNER JOIN cg_PlanCuentas p USING(cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                INNER JOIN cat_CgSubcuenta  USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)
                " . $join_cvepre . $joinsap . $sqlclcori . "
            WHERE cta_gpo_id = $gpo and cta_ur_id = $ur AND cta_ue_id = $ue
                $cuentas
                $periodo2 $poliza $mmc $opFe
                $opCT
                " . $claveFinanciamiento . $sqloriwhere . "
                $ordenar;
        ");

    $consultar2->consultar();

    $consultar2->desconectar();

    $saldoTotal = Math::redondear($saldo, 2);
    $saldo1 = $saldoTotal;
    while ($datos2 = $consultar2->extraer()) {
        //son los diferentes casos para los detalles de la cuenta

        if ($datos2['epl_id'] != 'C' && $datos2['epl_id'] != 'H') { //son los diferentes casos para los detalles de la cuenta
            if ($naturaleza == "D" && $datos2['nat_id'] == "D") {
                $saldoTotal = Math::redondear($saldoTotal, 2) + Math::redondear($datos2['pdx_debe'],
                    2) + Math::redondear($datos2['pdx_haber'], 2);
                $saldo1 = Math::redondear($saldoTotal, 2);
            } else if ($naturaleza == "D" && $datos2['nat_id'] == "H") {
                $saldoTotal = Math::redondear($saldoTotal, 2) - Math::redondear($datos2['pdx_debe'],
                    2) - Math::redondear($datos2['pdx_haber'], 2);
                $saldo1 = Math::redondear($saldoTotal, 2);
            } else if ($naturaleza == "H" && $datos2['nat_id'] == "D") {
                $saldoTotal = Math::redondear($saldoTotal, 2) - Math::redondear($datos2['pdx_debe'],
                    2) - Math::redondear($datos2['pdx_haber'], 2);
                $saldo1 = Math::redondear($saldoTotal, 2);
            } else if ($naturaleza == "H" && $datos2['nat_id'] == "H") {
                $saldoTotal = Math::redondear($saldoTotal, 2) + Math::redondear($datos2['pdx_debe'],
                    2) + Math::redondear($datos2['pdx_haber'], 2);
                $saldo1 = Math::redondear($saldoTotal, 2);
            }
        }

        //SE IMPRIME EL DETALLE DE LA CUENTA
        echo '<tr>';
        echo '<td valign="top" >' . $datos2['pot_id'] . '-' . $datos2['prf_id'] . $datos2['pma_id'] . ' </td>';
        echo '<td valign="top" >&nbsp;' . $datos2['cta_ue_id'] . ' </td>';
        if (isset($datos2['pma_fecha']) && $datos2['pma_fecha'] != '') {
            $year = substr($datos2['pma_fecha'], 0, 3);
            $mes = substr($datos2['pma_fecha'], 5, 2);
            $dia = substr($datos2['pma_fecha'], 8, 2);
        }
        echo '<td valign="top" >' . $dia . '-' . MesNombre($mes) . ' </td>';
        echo '<td valign="top" >' . $datos2['cta_cuenta'] . ' </td>';
        echo '<td valign="top" >' . utf8Decode($datos2['cta_nombre']) . ' </td>';

        echo '<td align="LEFT" >' . utf8Decode($datos2['concepto']) . ' </td>';

        //Si viene el detalle del documento
        if (count($df)) {
            if (isset($df['tdf_id'])) {
                echo '<td valign="top" >' . $datos2['tdf_id'] . ' </td>';
            }

            if (isset($df['pdx_bdo_id_origen'])) {
                echo '<td valign="top" >' . $datos2['pdx_bdo_id_origen'] . ' </td>';
            }

            if (isset($df['rfc_id'])) {
                echo '<td valign="top" >' . $datos2['rfc_id'] . ' </td>';
            }

            if (isset($df['pdx_referencia'])) {
                echo '<td valign="top" >' . $datos2['pdx_referencia'] . ' </td>';
            }

            if (isset($df['pdx_fechaReferencia'])) {
                echo '<td valign="top" >' . $datos2['pdx_fechaReferencia'] . ' </td>';
            }

        }

        if (count($cve_pre)) {
            if (isset($cve_pre['pdx_cve_ur_origen'])) {
                echo '<td valign="top" >' . $datos2['cve_ur'] . ' </td>';
            }

            if (isset($cve_pre['ue_id'])) {
                echo '<td valign="top" >' . $datos2['oa_ue_id'] . ' </td>';
            }

            if (isset($cve_pre['epg_id'])) {
                echo '<td valign="top" >&nbsp;' . $datos2['epg_clave'] . ' </td>';
                echo '<td valign="top" >' . $datos2['oa_nombre'] . ' </td>';
                echo '<td valign="top" >' . $datos2['ore_oficioAut'] . ' </td>';
                echo '<td valign="top" >' . $datos2['oa_numObra'] . ' </td>';
                echo '<td valign="top" >' . $datos2['obr_contrato'] . ' </td>';
            }

            if (isset($cve_pre['pg_id'])) {
                echo '<td valign="top" >' . $datos2['pg_id'] . ' </td>';
            }

            if (isset($cve_pre['pe_id'])) {
                echo '<td valign="top" >' . $datos2['pe_id'] . ' </td>';
            }

            if (isset($cve_pre['ecf_clave'])) {
                echo '<td valign="top" >' . $datos2['ecf_clave'] . ' </td>';
            }

        }

        if (count($ori)) {
            if (isset($ori['ori_ur'])) {
                echo '<td valign="top" >' . $datos2['ori_ur'] . ' </td>';
            }

            if (isset($ori['pdx_clc_id_origen'])) {
                echo '<td valign="top" >' . $datos2['pdx_clc_id_origen'] . ' </td>';
            }

            if (isset($ori['pdx_afe_id_origen'])) {
                echo '<td valign="top" >' . $datos2['pdx_afe_id_origen'] . ' </td>';
            }

            if (isset($ori['pdx_cmp_id_origen'])) {
                echo '<td valign="top" >' . $datos2['pdx_cmp_id_origen'] . ' </td>';
            }

            if (isset($ori['clc_cereCo'])) {
                echo '<td valign="top" >' . $datos2['clc_cereCo'] . ' </td>';
            }

        }

        if (count($vin)) {
            if (isset($vin['vin_ur'])) {
                echo '<td valign="top" >' . $datos2['vin_ur'] . ' </td>';
            }

            if (isset($vin['pdx_clc_id_vinculacion'])) {
                echo '<td valign="top" >' . $datos2['pdx_clc_id_vinculacion'] . ' </td>';
            }

            if (isset($vin['pdx_afe_id_vinculacion'])) {
                echo '<td valign="top" >' . $datos2['pdx_afe_id_vinculacion'] . ' </td>';
            }

            if (isset($vin['pdx_cmp_id_vinculacion'])) {
                echo '<td valign="top" >' . $datos2['pdx_cmp_id_vinculacion'] . ' </td>';
            }

            if (isset($vin['pdx_bdo_id_vinculacion'])) {
                echo '<td valign="top" >' . $datos2['pdx_bdo_id_vinculacion'] . ' </td>';
            }

            if (isset($vin['pdx_clc_cereCo_vinculacion'])) {
                echo '<td valign="top" >' . $datos2['pdx_clc_cereCo_vinculacion'] . ' </td>';
            }

        }

        if ($datos2['pdx_debe'] == 0) {
            echo '<td align="RIGHT" ></td>';
        } else {
            echo '<td valign="top" align="RIGHT" >' . number_format($datos2['pdx_debe'],
                2) . ' </td>';
        }
        if ($datos2['pdx_haber'] == 0) {
            echo '<td align="RIGHT" ></td>';
        } else {
            echo '<td valign="top" align="RIGHT" >' . number_format($datos2['pdx_haber'],
                2) . '</td>';
        }
        echo '<td valign="top" align="RIGHT" >' . number_format($saldo1, 2) . ' </td>';
        echo '<td valign="top" align="CENTER" >' . $datos2['mmc_id'] . ' </td>';
        echo '<td valign="top" align="CENTER" >' . $datos2['epl_id'] . ' </td>';
        if ($gpo_id . $ur_id == 116) {
            echo '<td valign="top" align="CENTER">&nbsp;' . $datos2['cta_sap'] . '</td>';
        }
        echo '</tr>';
        $debe = (($datos2['epl_id'] != 'C' && $datos2['epl_id'] != 'H') ? $debe + $datos2['pdx_debe']
            : $debe);
        $haber = (($datos2['epl_id'] != 'C' && $datos2['epl_id'] != 'H') ? $haber
             + $datos2['pdx_haber'] : $haber);
    }

    //se adquiere el valor de lo que trae la cuenta
    $numcuenta = $nombre;
    if (isset($datos2['cta_cuenta']) == '1.1.1.2.1.001.0194') {
        $contt = $consultar->getSql();
    }
} // del primer while

echo ' <tr>';
echo '<td align="center" colspan="5" style="font-size: 12.5; background-color: #003366; color: white; "></td>';

if (count($df)) {
    echo '<td align="center" colspan="' . (count($df)) . '" style="font-size: 12.5; background-color: #003366; color: white; "></td>';
}

$cols = 1 + count($cve_pre) + (isset($cve_pre['epg_id']) ? 4 : 0) + count($ori) + count($vin);
echo '<td align="center" colspan="' . $cols . '" style="font-size: 12.5; background-color: #003366; color: white; "><b>Total</b></td>';

echo '<td align="RIGHT" style="font-size: 12.5; background-color: #003366; color: white; "><b>' . number_format($debe,
    2) . '</b></td>';
echo '<td align="RIGHT" style="font-size: 12.5; background-color: #003366; color: white; "><b>' . number_format($haber,
    2) . '</b></td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white; "></b></td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white; "></b></td>';
echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white; "></b></td>';
if ($gpo_id . $ur_id == 116) {
    echo '<td align="center" style="font-size: 12.5; background-color: #003366; color: white; "></b></td>';
}
echo '<tr>';
echo '<td>';
echo $contt;
echo '</td>';
echo '</tr>';
echo '</table>'
?>
