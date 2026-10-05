<?php #3967
session_start();
if ($_SERVER['SERVER_ADDR'] == "172.17.90.17") {
    error_reporting(E_ERROR);
} else {
    error_reporting(E_ALL);
}

require_once dirname(__FILE__) . "/../../lib/constantes.php";
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaClc.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelClc.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorClcFinanzas extends WebResponse
{
    public $vista;
    public $model;
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "scp/afectaciones/ControladorClcFinanzas.php";
        $this->vista = new vistaClc();
        $this->model = new modelClc();

        if (in_array($_SESSION[_S]['nvj_id'], array(3, 4, 5))) {
            switch ($this->metodo) {
                case "DetalleRevisionClc":
                    $this->DetalleRevisionClc();
                    break;
                case "RevisionAnalistas":
                    $this->RevisionAnalistas();
                    break;
                case "RegresarClcVentanilla":
                    $this->RegresarClcVentanilla();
                    break;
            }
            $this->returnJson();
        } else {
            WebResponse::sinPermisos();
        }

    }

    public function DetalleRevisionClc()
    {
        $this->prm->gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->mpc_id = (isset($this->prm->mpc_id) && $this->prm->mpc_id != '') ? $this->prm->mpc_id : '';
        $this->prm->ecf_id = (isset($this->prm->ecf_id) && $this->prm->ecf_id != '') ? $this->prm->ecf_id : '';
        $this->prm->stc_id = (isset($this->prm->stc_id) && $this->prm->stc_id != '') ? $this->prm->stc_id : '';
        $wh = '';

        $this->setContenedor("det_fs" . $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id);
        switch ($this->prm->mpc_id) {
            case 1:
                switch ($this->prm->gpo_id . $this->prm->ur_id) {
                    case 901:
                        switch ($this->prm->tipo) {
                            case 'I':
                                switch ($this->prm->stc_id) {
                                    case 1:
                                        $wh = '
                                            INNER JOIN(
                                            SELECT bur_id,iba_id
                                            FROM cat_BancosUr
                                            WHERE gpo_id=1 AND ur_id=15 AND bur_id=0 OR gpo_id=' . $this->prm->gpo_id . ' and ur_id=' . $this->prm->ur_id . ')
                                            bur USING(bur_id)
                                        ';
                                        break;
                                    case 2:
                                        $wh = ' LEFT JOIN cat_BancosUr USING(bur_id,gpo_id,ur_id)';
                                        break;
                                }
                                break;
                            case 'C':
                                $wh = ' LEFT JOIN cat_BancosUr USING(bur_id,gpo_id,ur_id)';
                                break;
                        }
                        break;
                    default:
                        $wh = ' LEFT JOIN cat_BancosUr USING(bur_id)';
                        break;
                }
                break;
            case 2:
            case 3:
                $wh = '
                    INNER JOIN(
                    SELECT bur_id,iba_id
                    FROM cat_BancosUr
                    WHERE gpo_id=1 AND ur_id=15 AND bur_id=0)
                    bur USING(bur_id)
                ';
                break;
            case 4:
            case 5:
                $wh = ' LEFT JOIN cat_BancosUr USING(bur_id,gpo_id,ur_id)';
                break;
        }

        $this->conectar();
        $this->setSql('
            SELECT
                scp_Clc.gpo_id,scp_Clc.ur_id,clc_id,clc_concepto,
                DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y") AS clc_fechaElaboracion,
                SUM(clc_percepcion) AS clc_percepcion,SUM(clc_retencion) AS clc_retencion,
                ecl_id,tcl_id,tcl_nombre,stc_id,stc_nombre,mpc_id,mpc_nombre,iba_nombre,
                ur_nombre,MAX(cfi_clave) AS cfi_clave,MAX(cfi_nombre) AS cfi_nombre,
                bur_id,clc_beneficiario,clc_percepcion,clc_retencion,MAX(bsf_id) AS bsf_id,
                MAX(nombreSefin) AS nombreSefin, SUM(clc_percepcion) - SUM(clc_retencion)  AS importe
            FROM scp_Clc
                INNER JOIN cat_TipoClc USING (tcl_id)
                INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                INNER JOIN cat_ModoPagoClc USING (mpc_id)
                INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                ' . $wh . '
                LEFT JOIN cat_InstitucionesBancarias using(iba_id)
                INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                LEFT JOIN ( SELECT
                        cbs_id AS bsf_id,
                        iba_id,
                        cbs_nombre AS nombreSefin,
                        ecf_id
                    FROM ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin USING (cbs_id)
                        INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                    WHERE
                        ecf_id = :ecf
                )sefin USING (ecf_id)
            WHERE
                scp_Clc.gpo_id = :gpo
                AND scp_Clc.ur_id = :ur
                AND clc_id = :clc
                AND ecl_id = 4
            GROUP BY gpo_id,ur_id,clc_id,tcl_nombre,stc_nombre;
        ');
        $params = [
            ':ecf' => $this->prm->ecf_id,
            ':gpo' => $this->prm->gpo_id,
            ':ur' => $this->prm->ur_id,
            ':clc' => $this->prm->clc_id,
        ];
        $this->consultar($params);
        $this->desconectar();
        $detalle = $this->extraer();
        $clas = '';
        if ($detalle['bsf_id'] == '') {
            $this->mixScript('
                notify( "LA CLABE INTERBANCARIA NO ESTÁ VINCULADA O ESTA DESACTIVADA","warning");
                    $("#recep").html("LA CLABE INTERBANCARIA NO ESTÁ VINCULADA");
                ');
            $clas = 'style="border:2px solid red;" ';
        }

        if ($this->getNumeroRegistros() > 0) {
            $html = '
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-12 col-xs-12">
                            <h6 style="text-align:center"><b><font color="#3D362D"> REVISIÓN DE CLC POR  ' . $_SESSION[_S]['nom'] . '</font><b></h6>
                        <table class="table table-bordered">
                            <tr>
                                <th colspan="3" bgcolor="#409540" style="text-align:center"><font color="#FFFFFF">DATOS DE LA CLC</font></th>
                            </tr>
                            <tr >
                                <td   bgcolor="#FFFFFF"><b><font color="#2C2416">NÚMERO DE CLC</font></b></td>
                                <td   bgcolor="#FFFFFF"><b><font color="#2C2416">TIPO DE CLC</font></b></td>
                                <td   bgcolor="#FFFFFF"><b><font color="#2C2416">SUBTIPO DE CLC</font></b></td>
                            </tr>

                            <tr>
                                <td bgcolor="#9DE6C1"><font color="#000000">' . $detalle['clc_id'] . '</font></td>
                                <td bgcolor="#9DE6C1"><font color="#000000">' . $detalle['tcl_nombre'] . '</font></td>
                                <td bgcolor="#9DE6C1"><font color="#000000">' . $detalle['stc_nombre'] . '</font></td>
                            </tr>

                            <tr>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">MODALIDAD DE PAGO</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">CLABE INTERBANCARIA</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">CUENTA RECEPTORA</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#9DE6C1"><font color="#000000">' . $detalle['mpc_nombre'] . '</font></td>
                                <td bgcolor="#9DE6C1"><font color="#000000">' . $detalle['bur_id'] . ' - ' . $detalle['iba_nombre'] . '</font></td>
                                <td id="recep" bgcolor="#9DE6C1" ' . $clas . '><font color="#000000">' . $detalle['bsf_id'] . ' - ' . $detalle['nombreSefin'] . '</font></td>
                            </tr>
                            <tr >
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">CLAVE DE FINANCIAMIENTO</font></b></td>
                                <td  colspan="2" bgcolor="#FFFFFF"><b><font color="#2C2416">BENEFICIARIO DE LA CLC</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#9DE6C1"><font color="#000000">' . $detalle['cfi_clave'] . ' - ' . $detalle['cfi_nombre'] . '</font></td>
                                <td colspan="2" bgcolor="#9DE6C1"><font color="#000000">' . $detalle['clc_beneficiario'] . '</font></td>
                            </tr>
                            <tr >
                                <td colspan="3" bgcolor="#FFFFFF"><b><font color="#2C2416">CONCEPTO DE LA CLC</font></b></td>
                            </tr>
                            <tr >
                                <td colspan="3" bgcolor="#9DE6C1"><font color="#000000">' . $detalle['clc_concepto'] . '</font></td>
                            </tr>
                            <tr >
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">PERCEPCIÓN</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">RETENCIÓN</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">IMPORTE NETO</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#9DE6C1" style="text-align:right">
                                    <span style="float:left; font-size:14px;">$</span>
                                    <font color="#000000" size="4">' . number_format($detalle['clc_percepcion'], 2) . '</font></td>
                                <td bgcolor="#9DE6C1" style="text-align:right">
                                    <span style="float:left; font-size:14px;">$</span>
                                    <font color="#000000" size="4">' . number_format($detalle['clc_retencion'], 2) . '</font></td>
                                <td bgcolor="#9DE6C1" style="text-align:right">
                                    <span style="float:left; font-size:14px;">$</span>
                                    <font color="#000000" size="4">' . number_format($detalle['importe'], 2) . '</font></td>
                            </tr>
                            <tr >
                                <td colspan="3" style="text-align:center" bgcolor="#409540"><b><font color="#FFFFFF">UNIDAD RESPONSABLE:&nbsp;&nbsp;&nbsp;' . $detalle['gpo_id'] . $detalle['ur_id'] . ' - ' . $detalle['ur_nombre'] . '</font></b></td>
                            </tr>
                        </table>
                    </fieldset>
                <div>
            ';
        } else {
            $this->mixScript(' notify("Existe un error en la CLC ' . $this->prm->clc_id . ', favor de verificarlo con informatica ","error"); ');
        }
        $revisar = new stdClass();
        $revisar->color = "azul";
        $revisar->class = '';
        $revisar->icon = 'solid fa-check me-2';
        $revisar->id = "revisar";
        $revisar->color = "verde";
        $revisar->name = "revisar";
        $revisar->value = "Revisar";
        $revisar = $this->ponBoton($revisar);

        $Ventanila = '';
        $Ventanila = new stdClass();
        $Ventanila->icon = 'solid fa-left me-2';
        $Ventanila->color = "rojo";
        $Ventanila->id = "Ventanila";
        $Ventanila->name = "Ventanila";
        $Ventanila->value = "Regresar CLC a Ventanilla";
        $Ventanila = $this->ponBoton($Ventanila);

        $this->agregaTools($revisar . ' ' . $Ventanila . ' REVISION DE LA CLC ' . $this->prm->clc_id . ' DE LA UR ' . $this->prm->gpo_id . $this->prm->ur_id . '  ', $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id . "toolsviewer");
        $this->mixHtml($html);
        $this->mixScript('
            $("#revisar").click(function(){
                alertify.confirm("REVISAR CLC", "Confirme la Revisión de la CLC ' . $this->prm->clc_id . ' de la UR ' . $this->prm->gpo_id . $this->prm->ur_id . ' ",
                    function(){
                        $(this).attr("disabled","disabled");
                        var obj = {"metodo":"RevisionAnalistas"};
                        obj.clc_id =' . $this->prm->clc_id . ';
                        obj.gpo_id ="' . $this->prm->gpo_id . '";
                        obj.ur_id = "' . $this->prm->ur_id . '";
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        if(!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                    },
                    undefined
                ).set("defaultFocus","cancel")
                .set("labels", {
                    ok: "Aceptar", cancel: "Cancelar"
                });
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-primary");
                    btnAceptar.classList.add("btn-outline-success");
                }
                    const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                if (cancela) {
                    cancela.classList.remove("btn-danger");
                    cancela.classList.add("btn-outline-danger");
                }
            });

            $("#regresar").click(function(){
                comunica("scp/afectaciones/ControladorClc.php",{
                    metodo:"listadoClc",
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . ',
                    tareaclc:"' . $this->prm->tareaclc . '"
                });
            });

            $("#Ventanila").click(function(){
                alertify.confirm("REGRESAR CLC", "Confirme que desea regresar la CLC ' . $this->prm->clc_id . ' de la UR ' . $this->prm->gpo_id . $this->prm->ur_id . ' a Ventanila ",
                    function(){
                        var obj = {"metodo":"RegresarClcVentanilla"};
                            obj.clc_id = ' . $this->prm->clc_id . ';
                            obj.gpo_id =' . $this->prm->gpo_id . ';
                            obj.ur_id = "' . $this->prm->ur_id . '";
                            obj.tareaclc ="' . $this->prm->tareaclc . '";
                            comunica("' . $this->pantalla . '", obj,true );
                    },undefined
                ).set("defaultFocus","cancel")
                .set("labels", {
                    ok: "Aceptar", cancel: "Cancelar"
                });
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-primary");
                    btnAceptar.classList.add("btn-outline-success");
                }
                const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                if (cancela) {
                    cancela.classList.remove("btn-danger");
                    cancela.classList.add("btn-outline-danger");
                }
            });
        ');
    }

    public function RevisionAnalistas()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $this->prm->gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';

        $this->conectar();
        $this->setSql('
            SET @usr_id = ?,
            @usr_ip = INET_ATON(?),
            @bit_observacion = "CLC REVISADA EN LA VENTANILLA DESDE LA PANTALLA S-2-2";
        ');
        $params = [$user, $ip];
        $this->consultar($params);
        $this->setSql('
            UPDATE scp_Clc
            SET ecl_id="5"
            WHERE gpo_id=? AND ur_id=? AND clc_id=? AND ecl_id=4
        ');
        $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id];
        $this->consultar($params);
        $this->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha) VALUES (?,?,?,5,"CLC REVISADA POR SEFIN",INET_ATON(?),?,NOW() )
        ');
        $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id, $ip, $user];
        $this->consultar($params);
        $this->desconectar();

        $this->mixScript('
            notify("La CLC ' . $this->prm->clc_id . ' ha sido revisada  correctamente","success");
            $("#fs' . $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id . '")
            .animate({opacity:0,width:"80%",height:"80%"},300,function(){
                $("#fs' . $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id . '").remove();
                $("#tr' . $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id . '").remove();
            });
        ');
    }

    private function RegresarClcVentanilla()
    {
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->conectar();
        $this->setSql('
            SET @usr_id = ?,
            @usr_ip = INET_ATON(?),
            @bit_observacion = "CLC RECHAZA EN LA VENTANILLA DESDE LA PANTALLA S-2-2";
        ');
        $params = [$user, $ip];
        $this->consultar($params);
        if (($this->prm->clc_id != '' && $this->prm->gpo_id != '' && $this->prm->ur_id != '') || ($this->prm->tarea == "rechazarCarga")) {
            if (isset($this->prm->tarea) && $this->prm->tarea == "rechazarCarga" && $this->prm->tem_carga != '') {
                $this->setSql('
                    UPDATE scp_Clc SET ecl_id = 3
                    WHERE tem_carga= ? AND ecl_id = 4;
                ');
                $params = [$this->prm->tem_carga];
                $this->consultar($params);
                $this->desconectar();

                $this->conectar();
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    SELECT gpo_id,ur_id,clc_id,"3" as ecl_id,"CARGA MASIVA  RECHAZADA A VENTANILLA POR ERROR DE RECEPCION" as bit_observacion,?,?,NOW()
                    FROM scp_Clc
                    WHERE tem_carga = ? AND ecl_id=4
                ');
                $params = [$ip, $user, $this->prm->tem_carga];
                $this->consultar($params);
                $this->desconectar();

                $this->mixScript('
                    notify("La Carga masiva  ' . $this->prm->tem_carga . ' ha sido rechazada a Ventanilla ","success");
                    comunica("scp/afectaciones/ControladorClc.php",{
                        metodo:"listadoClc",
                        "tareaclc":"' . $this->prm->tareaclc . '"});
                ');
            } else {
                if ($this->prm->clc_id != '' && $this->prm->gpo_id != '' && $this->prm->ur_id != '') {
                    $this->conectar();
                    $this->setSql('
                        SET @usr_id = ?,
                        @usr_ip = INET_ATON(?),
                        @bit_observacion = "CLC RECHAZA DESDE VENTANILLA EN LA PANTALLA S-2-2";
                    ');
                    $params = [$user, $ip];
                    $this->consultar($params);
                    $this->setSql('
                        UPDATE scp_Clc SET ecl_id=3
                        WHERE gpo_id= ? AND  ur_id= ? AND clc_id= ? AND ecl_id=4;
                    ');
                    $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id];
                    $this->consultar($params);
                    $this->desconectar();

                    $this->conectar();
                    $this->setSql('
                        INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                        values (?,?,?,3,"CLC RECHAZADA A VENTANILLA POR ERROR DE RECEPCION",INET_ATON(?)'
                        . ',?,NOW())
                    ');
                    $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id, $ip, $user];
                    $this->consultar($params);
                    $this->desconectar();

                    $this->mixScript('
                        notify("La CLC ' . $this->prm->clc_id . ' ha sido rechazada a Ventanilla ","success");
                        $("#fs' . $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id . '")
                        .animate({opacity:0,width:"80%",height:"80%"},300,function(){
                            $("#fs' . $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id . '").remove();
                            $("#tr' . $this->prm->gpo_id . $this->prm->ur_id . $this->prm->clc_id . '").remove();
                        });
                    ');
                }
            }
        } else {
            $this->mixScript('notify("error al querer regresar la CLC a ventanilla","error"); ');
        }
    }
}
if (isset($_SESSION[_S]['prm']['S']['2']['2'])) {
    new ControladorClcFinanzas();
} else {
    WebResponse::sinPermisos();
}
