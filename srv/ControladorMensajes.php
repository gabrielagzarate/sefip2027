<?php
/* *
 * Description of ControladorMensajes
 *
 * @author ?
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Modif: 24-Ene-2020
 * Comment: -----
 * Objective:
- Consultar los mensajes
- Notifica mensajes enviados y recibidos
 * Regla de Negocio: Se mostrara los mensajes y notificaciones dependiendo de otros controladores
 * Permits: Dependiendo de los controladores.
 * */
set_time_limit(0);
error_reporting(E_ALL);

require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/ClasificacionAdministrativaB.php';

class ControladorMensajes extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();

        $metodo = (isset($this->prm->metodoMensajes)) ? $this->prm->metodoMensajes : '';

        switch ($metodo) {
            case 'notificaMensajes':
                $this->notificaMensajes();
                break;
            case 'notificaRespuesta':
                $this->notificaRespuesta();
                break;
        }
    }

    public static function consultaMensajes()
    {
        $ejecuta = new WebResponse();
        $_SESSION[_S]['clasAdm-ue'] = null;
        $_SESSION[_S]['clasAdm-prg'] = null;
        $_SESSION[_S]['clasAdm-spr'] = null;
        $_SESSION[_S]['clasAdm-py'] = null;
        $_SESSION[_S]['clasAdm-oa'] = null;
        $_SESSION[_S]['clasAdm-epg'] = 0;
        $proceso = "";
        //verifica los permisos del usuario
        if ((isset($_SESSION[_S]['prm']['A']['11']['11']) || isset($_SESSION[_S]['prm']['A']['11']['5'])) && $_SESSION[_S]['nvj_id'] <= 2) {
            $proceso =
                'var stoptimer="";
                    function consulta()
                    {
                        $.ajax({
                            async:	true,
                            type: "POST",
                            url: "srv/ControladorMensajes.php",
                            data: {metodoMensajes:"notificaMensajes"},
                            dataType: "json",
                            success:
                                function(data)
                                {
                                    $("#avisos").text(data.contador);
                                    $("#avisos").text(data.contevi);
                                    $("#avisos").text(data.contotal);
                                    $("#detalleavisos").text(data.contador);
                                    $("#detalleavisos").html("");
                                    for (var l in data.info) {
                                        if (data.info[l].tim_id == 1 ) { // verifica el tipo de mensaje si es 1 o 2
                                            $("#detalleavisos").append(
                                                "<li onClick = \'consultamens1(" +
                                                data.info[l].hms_trimestre + "," +
                                                data.info[l].gpo_id + "," +
                                                data.info[l].ur_id + "," +
                                                data.info[l].epg_id +
                                                    ");\'><a href=\'# \'><b>" +
                                                data.info[l].analista +
                                                " &mdash; " + data.info[l].usr_fecha +
                                                "</b><br>" +
                                                data.info[l].hms_mensaje  +
                                                "</a></li>"
                                            );
                                        }
                                        else{
                                            $("#detalleavisos").append(
                                                "<li onClick = \'consultamens2(" +
                                                data.info[l].numero + "," +
                                                data.info[l].gpo_id + "," +
                                                data.info[l].ur_id + "," +
                                                data.info[l].periodo + "," +
                                                data.info[l].epg_id +
                                                    ");\'><a href=\'# \'><b>" +
                                                data.info[l].analista +
                                                " &mdash; " + data.info[l].usr_fecha +
                                                "</b><br>" +
                                                data.info[l].hms_mensaje  +
                                                "</a></li>"
                                            );
                                        }
                                    }
                                    //Controlador de solicitud de evidencias
                                    $("#notificaevidencias").html("");
                                    for (var l in data.evi) {
                                        if(data.evi[l].tim_id==1){
                                            $("#notificaevidencias").append(
                                                "<li onClick = \'consultamensevi(" +
                                                data.evi[l].hms_trimestre + "," +
                                                data.evi[l].gpo_id + "," +
                                                data.evi[l].ur_id + "," +
                                                data.evi[l].epg_id + "," +
                                                data.evi[l].hms_id +
                                                    ");\'><a href=\'# \'><b>" +
                                                data.evi[l].analista +
                                                " &mdash; " + data.evi[l].usr_fecha +
                                                "</b><br>" +
                                                data.evi[l].hms_mensaje  +
                                                "</a></li>"
                                            );
                                        }
                                    }
                                    //verifica si existen mensajes
                                    if (data.contador == 0) {
                                        $("#detalleavisos").append("No tiene mensajes");
                                    }
                                     //verifica si solicitan evidencias
                                    if (data.contevi == 0) {
                                        $("#notificaevidencias").append("No solicitan evidencias");
                                    }
                                     //Anota numero de msj y evidencias solicitadas
                                    $("#mnsnue").html("");
                                    $("#mnsnue").append(data.contotal);
                                }
                        });
                    }
                    // cuando el mesaje es tipo 2 genera esta llamada
                    function  consultamens2(varnumero, vargrupo_id, varur_id, varperiodo, varepg_id)
                    {
                        var urclas="";
                        var opcion = opcion1 = metodoact = "";

                        if (varur_id > 9) {
                            urclas = varur_id;
                        } else {
                            urclas = "0" + varur_id;
                        }

                        if(varperiodo == 1) {
                            opcion = "mes";
                            opcion1 = "mensual";
                            metodoact = "verActividadesMens";
                        }
                        else if(varperiodo == 2) {
                            opcion = "trimestre";
                            opcion1 = "trimestral";
                            metodoact = "verActividades";
                        }

                        comunica(
                            "poa/seguimiento/ControladorRevisarSeguimientoGastosOperacion.php",
                            {
                                metodo:"seleccionaperiodo",
                                opc_id : varperiodo
                            }
                        );
                        comunica(
                            "poa/seguimiento/ControladorRevisarSeguimientoGastosOperacion.php",
                            {
                                metodo:metodoact,
                                gpo_id:vargrupo_id,
                                ur_id:urclas,
                                pepg_id:varepg_id,
                                [opcion]:varnumero
                            }
                        );

                        comunica(
                            "poa/seguimiento/ControladorRevisarSeguimientoGastosOperacion.php",
                            {
                                metodo:"Historial",
                                epg_id:varepg_id,
                                [opcion]:varnumero,
                                tipo:opcion1
                            }
                        );
                    }
                    //cuando el mesaje es tipo 1 genera esta llamada
                    function  consultamens1(vartrimestre, vargrupo_id, varur_id, varepg_id)
                    {
                        var urclas="";

                        if (varur_id > 9) {
                            urclas = varur_id;
                        } else {
                            urclas = "0" + varur_id;
                        }

                        comunica(
                            "poa/seguimiento/ControladorRevisarActividad.php",
                            {metodo:"seleccionaTrimestre",
                                gpo_id:vargrupo_id,
                                ur_id:urclas,
                                pepg_id:varepg_id}
                        );

                        comunica(
                            "poa/seguimiento/ControladorRevisarActividad.php",
                            {
                                metodo:"verActividades",
                                trimestre:vartrimestre,
                                gpo_id:vargrupo_id,
                                ur_id:urclas
                            }
                        );

                        comunica(
                            "poa/seguimiento/ControladorRevisarActividad.php",
                            {
                                metodo:"Historial",
                                epg_id:varepg_id,
                                trimestre:vartrimestre,
                            }
                        );
                    }
                    //Función que se ejecuta cuando solicitan evidencias
                    function  consultamensevi(vartrimestre, vargrupo_id, varur_id, varepg_id,varhms_id)
                    {
                        var urclas="";

                        if (varur_id > 9) {
                            urclas = varur_id;
                        } else {
                            urclas = "0" + varur_id;
                        }

                        comunica(
                            "poa/seguimiento/ControladorRevisarActividad.php",
                            {metodo:"seleccionaTrimestre",
                                gpo_id:vargrupo_id,
                                ur_id:urclas,
                                pepg_id:varepg_id}
                        );

                        comunica(
                            "poa/seguimiento/ControladorRevisarActividad.php",
                            {
                                metodo:"verActividades",
                                trimestre:vartrimestre,
                                gpo_id:vargrupo_id,
                                ur_id:urclas
                            }
                        );

                        comunica(
                            "poa/seguimiento/ControladorRevisarActividad.php",
                            {
                                metodo:"HistorialEvidencia",
                                epg_id:varepg_id,
                                trimestre:vartrimestre,
                                hms_id:varhms_id
                            }
                        );
                    }
                    consulta();
                    stoptimer=setInterval(consulta, 100000);
                    function StopFunction(){
                        clearInterval(stoptimer);
                    }'
            ;
        } //verifica los permisos del usuario
        elseif ((isset($_SESSION[_S]['prm']['A']['11']['10']) || isset($_SESSION[_S]['prm']['A']['11']['1'])) && $_SESSION[_S]['nvj_id'] > 2) { //<i class=\"fa fa-envelope-open-o\"></i>
            $proceso = '
                    var stoptimer="";
                    function consulta() {
                        $.ajax({
                            async:	true,
                            type: "POST",
                            url: "srv/ControladorMensajes.php",
                            data:{metodoMensajes:"notificaRespuesta"},
                            dataType: "json",
                            success: function(data)
                            {
                                $("#avisos").text(data.contador);
                                $("#avisos").text(data.contevi);
                                $("#avisos").text(data.contotal);
                                $("#detalleavisos").text(data.contador);
                                $("#detalleavisos").html("");
                                for (var l in data.info) {
                                    if(data.info[l].tim_id==1){
                                        $("#detalleavisos").append(
                                            "<li onClick = \'consultamens1(" +
                                            data.info[l].hms_trimestre + "," +
                                            data.info[l].gpo_id + "," +
                                            data.info[l].ur_id + "," +
                                            data.info[l].epg_id +
                                            ");\'><a href=\'# \'><b>" +
                                            data.info[l].gpo_id + data.info[l].ur_id +
                                            " &mdash; " + data.info[l].usr_fecha +
                                            "</b><br>" +
                                            data.info[l].hms_mensaje  +
                                            "</a></li>"
                                        );
                                    }else{
                                        $("#detalleavisos").append(
                                        "<li onClick = \'consultamens2(" +
                                        data.info[l].numero + "," +
                                        data.info[l].gpo_id + "," +
                                        data.info[l].ur_id + "," +
                                        data.info[l].periodo + "," +
                                        data.info[l].epg_id +
                                        ");\'><a href=\'# \'><b>" +
                                        data.info[l].gpo_id + data.info[l].ur_id +
                                        " &mdash; " + data.info[l].usr_fecha +
                                        "</b><br>" +
                                        data.info[l].hms_mensaje  +
                                        "</a></li>"
                                        );
                                    }
                                }
                                //Controlador de solicitud de evidencias
                                $("#notificaevidencias").html("");
                                for (var l in data.evi) {
                                    if(data.evi[l].tim_id==1){
                                        $("#notificaevidencias").append(
                                            "<li onClick = \'consultamens1(" +
                                            data.evi[l].hms_trimestre + "," +
                                            data.evi[l].gpo_id + "," +
                                            data.evi[l].ur_id + "," +
                                            data.evi[l].epg_id + "," +
                                            data.evi[l].hms_id +
                                                ");\'><a href=\'# \'><b>" +
                                            data.evi[l].analista +
                                            " &mdash; " + data.evi[l].usr_fecha +
                                            "</b><br>" +
                                            data.evi[l].hms_mensaje  +
                                            "</a></li>"
                                        );
                                    }
                                }
                                //verifica si existen mensajes
                                if (data.contador == 0) {
                                    $("#detalleavisos").append("No tiene mensajes");
                                }
                                 //verifica si solicitan evidencias
                                if (data.contevi == 0) {
                                    $("#notificaevidencias").append("No solicitan evidencias");
                                }
                                 //Anota numero de msj y evidencias solicitadas
                                $("#mnsnue").html("");
                                $("#mnsnue").append(data.contotal);
                            }
                        });
                    }
                    //cuando el mesaje es tipo 2 genera esta llamada
                    function  consultamens2( varnumero, vargrupo_id, varur_id, varperiodo, varepg_id) {
                        var urclas="";
                        var opcion = opcion1 = metodoact = "";

                        if (varur_id > 9) {
                            urclas=varur_id;
                        } else {
                            urclas = "0" + varur_id;
                        }

                        if(varperiodo == 1) {
                            opcion = "mes";
                            opcion1 = "mensual";
                            metodoact = "verActividadesMens";
                        }
                        else if(varperiodo == 2) {
                            opcion = "trimestre";
                            opcion1 = "trimestral";
                            metodoact = "verActividades";
                        }

                        comunica(
                            "poa/seguimiento/ControladorObservarSeguimientoGastos.php",
                            {
                                metodo:"seleccionaperiodo",
                                opc_id : varperiodo
                            }
                        );

                        comunica(
                            "poa/seguimiento/ControladorObservarSeguimientoGastos.php",
                            {
                                metodo: metodoact,
                                gpo_id:vargrupo_id,
                                ur_id:varur_id,
                                epg_id:varepg_id,
                                [opcion]:varnumero
                            }
                        );

                        comunica(
                            "poa/seguimiento/ControladorObservarSeguimientoGastos.php",
                            {
                                metodo:"Historial",
                                epg_id:varepg_id,
                                [opcion]:varnumero,
                                tipo:opcion1
                            }
                        );
                    }
                    //cuando el mesaje es tipo 1 genera esta llamada
                    function  consultamens1( vartrimestre, vargrupo_id, varur_id, varepg_id) {
                        var urclas="";
                        if (varur_id > 9) {
                            urclas=varur_id;
                        } else {
                            urclas = "0" + varur_id;
                        }

                        comunica("poa/seguimiento/ControladorObservarActividad.php",{
                                metodo:"seleccionaTrimestre",
                                gpo_id:vargrupo_id,
                                ur_id:urclas,
                                pepg_id:varepg_id
                            }
                        );

                        comunica("poa/seguimiento/ControladorObservarActividad.php",{
                                metodo:"verActividades",
                                trimestre:vartrimestre,
                                gpo_id:vargrupo_id,
                                ur_id:varur_id
                            }
                        );

                        comunica("poa/seguimiento/ControladorObservarActividad.php",{
                                metodo:"Historial",
                                epg_id:varepg_id,
                                trimestre:vartrimestre
                            }
                        );
                    }
                    consulta();
                    stoptimer=setInterval(consulta, 100000);
                    function StopFunction(){
                        clearInterval(stoptimer);
                    }'
            ;
        }

        return $proceso;
    }

    /*
     * Metodo que muestra los mensajes de SEFIN dirigidos a la UR
     */
    private function notificaMensajes()
    {
        $informacion = array();
        $evidencias = array();
        $num = 0;
        $and = '';
        $nvj = (isset($_SESSION[_S]['nvj_id'])) ? $_SESSION[_S]['nvj_id'] : 3;

        if ($nvj <= 2) {
            if (isset($_SESSION[_S]['prm']['A']['11']['11'])) {
                $and .= " AND (tim_id=2 ";
            }
            if (isset($_SESSION[_S]['prm']['A']['11']['5'])) {
                if ($and != '') {
                    $and .= ' OR ';
                } else {
                    $and .= ' AND (';
                }

                $and .= "  tim_id=1 ";
            }
            //Consulta y proceso que determina si existen mensajes
            //Esta consulta regresa un listado de mensajes pendientes por contestar.
            $this->conectar();
            $this->setSql(
                'SELECT
                    SUBSTRING(hms_mensaje,1,17) AS hms_mensaje,
                    hms_trimestre, Ep.gpo_id AS gpo_id, Ep.ur_id AS ur_id,
                    Ep.prg_id AS prg_id, Ep.spr_id AS spr_id,
                    CAST(Ep.epg_id AS UNSIGNED) AS epg_id,
                    eme_id, DATE_FORMAT(Hi.usr_fecha ,"%e-%b") AS usr_fecha,
                    Us.usr_nombre as analista_sefin,tim_id,
                    CASE
                        WHEN Hi.hms_trimestre != "" THEN
                            2
                        WHEN Hi.hms_mensual != "" THEN
                            1
                        ELSE 0
                        END AS periodo,
                        CASE
                        WHEN Hi.hms_trimestre != "" THEN
                                Hi.hms_trimestre
                        WHEN Hi.hms_mensual != "" THEN
                                Hi.hms_mensual
                        ELSE 0
                        END AS numero
                FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                    INNER JOIN ctrlUsuario.cat_Usuario  Us ON Us.usr_id=Hi.usr_id
                    WHERE
                    Ep.gpo_id = ? AND Ep.ur_id = ?
                    AND (Ep.gpo_id,Ep.ur_id) NOT IN ((1,15))
                    AND eme_id=2 ' . $and . ')
                    ORDER BY usr_fecha DESC
                LIMIT 10 ;'
            );
            //Se ejecuta la consulta
            $this->consultar([$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']]);
            // se guarda la información en un arreglo para enviarlo al Main
            while ($datos = $this->extraer()) {
                $informacion[] = array(
                    'hms_mensaje' => $datos['hms_mensaje'],
                    'hms_trimestre' => $datos['hms_trimestre'],
                    'usr_fecha' => $datos['usr_fecha'],
                    'gpo_id' => $datos['gpo_id'],
                    'ur_id' => $datos['ur_id'],
                    'prg_id' => $datos['prg_id'],
                    'spr_id' => $datos['spr_id'],
                    'epg_id' => $datos['epg_id'],
                    'periodo' => $datos['periodo'],
                    'numero' => $datos['numero'],
                    'analista' => $datos['analista_sefin'],
                    'tim_id' => $datos['tim_id'],
                );
            }
            //consulta que devuelve el num de mensajes pendientes
            $this->setSql(
                'SELECT
                    COUNT(*) AS num
                    FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                WHERE
                    Ep.gpo_id = ? AND Ep.ur_id = ?
                    AND (Ep.gpo_id,Ep.ur_id) NOT IN ((1,15))
                    AND eme_id=2 ' . $and . ');');
            $this->consultar([$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']]);
            while ($contMensajes = $this->extraer()) {
                $num = $contMensajes['num'];
            }
            //La segunda consulta busca si solicitan evidencias
            $this->setSql(
                'SELECT
                    COUNT(*) AS num
                FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                WHERE
                    Ep.gpo_id = ? AND Ep.ur_id = ?
                    AND (Ep.gpo_id,Ep.ur_id) NOT IN ((1,15))
                    AND ese_id = 2 ' . $and . ');');
            //segunda consulta
            $this->consultar([$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']]);
            while ($contEvidencias = $this->extraer()) {
                $numevi = $contEvidencias['num'];
            }
            //Esta consulta regresa un listado de solicitudes de evidencias pendientes por contestar.
            $this->conectar();
            $this->setSql(
                'SELECT
                    concat("SOLICITAN EVIDENCIAS ",SUBSTRING(hms_mensaje,1,15)) AS hms_mensaje,
                    hms_trimestre, Ep.gpo_id AS gpo_id, Ep.ur_id AS ur_id,
                    Ep.prg_id AS prg_id, Ep.spr_id AS spr_id,
                    CAST(Ep.epg_id AS UNSIGNED) AS epg_id,
                    eme_id, DATE_FORMAT(Hi.usr_fecha ,"%e-%b") AS usr_fecha,
                    Us.usr_nombre as analista_sefin,Hi.tim_id,hms_id
                FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                    -- INNER JOIN cat_Usuario AS Us ON Us.usr_id=Hi.usr_id
                    INNER JOIN ctrlUsuario.cat_Usuario  Us ON Us.usr_id=Hi.usr_id
                WHERE
                    Ep.gpo_id= ? AND Ep.ur_id= ?
                    AND (Ep.gpo_id,Ep.ur_id) NOT IN ((1,15))
                    AND ese_id=2 ' . $and . ')
                    ORDER BY usr_fecha DESC
                LIMIT 10 ;'
            );
            //Se ejecuta la consulta
            $this->consultar([$_SESSION[_S]['gpo_id'],$_SESSION[_S]['ur_id']]);
            // se guarda la información en un arreglo para enviarlo al Main
            while ($datos = $this->extraer()) {
                $evidencias[] = array(
                    'hms_mensaje' => $datos['hms_mensaje'],
                    'hms_trimestre' => $datos['hms_trimestre'],
                    'usr_fecha' => $datos['usr_fecha'],
                    'gpo_id' => $datos['gpo_id'],
                    'ur_id' => $datos['ur_id'],
                    'prg_id' => $datos['prg_id'],
                    'spr_id' => $datos['spr_id'],
                    'hms_id' => $datos['hms_id'],
                    'epg_id' => $datos['epg_id'],
                    'analista' => $datos['analista_sefin'],
                    'tim_id' => $datos['tim_id'],
                );
            }
            $this->desconectar();
        }

        //Arreglos de valores
        $respuesta['info'] = $informacion;
        $respuesta['contador'] = $num;
        $respuesta['evi'] = $evidencias;
        $respuesta['contevi'] = $numevi;
        $respuesta['contotal'] = $num + $numevi;
        echo json_encode($respuesta);
    }

    /*
     * Metodo que muestra los mensajes de SEFIN dirigidos a la UR que fueron respondidos
     */
    private function notificaRespuesta()
    {
        $informacion = array();
        $num = 0;
        $and = "";
        $nvj = (isset($_SESSION[_S]['nvj_id'])) ? $_SESSION[_S]['nvj_id'] : 0;
        if ($nvj > 2) {
            if (isset($_SESSION[_S]['prm']['A']['11']['10'])) {

                $and .= " AND ( tim_id=2 ";
            }
            if (isset($_SESSION[_S]['prm']['A']['11']['1'])) {
                if ($and != '') {
                    $and .= ' OR ';
                } else {
                    $and .= ' AND (';
                }

                $and .= "  tim_id=1 ";
            }
            $this->conectar();
            $this->setSql(
                'SELECT
                    SUBSTRING(hms_mensaje,1,17) AS hms_mensaje,
                    hms_trimestre, Ep.gpo_id AS gpo_id, Ep.ur_id AS ur_id,
                    Ep.prg_id AS prg_id, Ep.spr_id AS spr_id,
                    CAST(Ep.epg_id AS UNSIGNED) AS epg_id,
                    eme_id, DATE_FORMAT(Hi.usr_fecha ,"%e-%b") AS usr_fecha,
                    Us.usr_nombre as analista_sefin,tim_id,
                    CASE
                        WHEN Hi.hms_trimestre != "" THEN
                            2
                        WHEN Hi.hms_mensual != "" THEN
                            1
                        ELSE 0
                    END AS periodo,
                    CASE
                        WHEN Hi.hms_trimestre != "" THEN
                                Hi.hms_trimestre
                        WHEN Hi.hms_mensual != "" THEN
                                Hi.hms_mensual
                        ELSE 0
                        END AS numero
                FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                    INNER JOIN ctrlUsuario.cat_Usuario  Us ON Us.usr_id=Hi.usr_id
                    WHERE
                    (Ep.gpo_id,Ep.ur_id) NOT IN ((1,15))
                    AND eme_id=4 ' . $and . ')
                    ORDER BY usr_fecha DESC
                LIMIT 10 ;'
            );
            //Se ejecuta la consulta
            $this->consultar();
            // se guarda la información en un arreglo para enviarlo al Main
            while ($datos = $this->extraer()) {
                $informacion[] = array(
                    'hms_mensaje' => $datos['hms_mensaje'],
                    'hms_trimestre' => $datos['hms_trimestre'],
                    'usr_fecha' => $datos['usr_fecha'],
                    'gpo_id' => $datos['gpo_id'],
                    'ur_id' => $datos['ur_id'],
                    'prg_id' => $datos['prg_id'],
                    'spr_id' => $datos['spr_id'],
                    'epg_id' => $datos['epg_id'],
                    'periodo' => $datos['periodo'],
                    'numero' => $datos['numero'],
                    'analista' => $datos['analista_sefin'],
                    'tim_id' => $datos['tim_id'],

                );
            }
            //consulta que devuelve el num de mensajes pendientes
            $this->setSql(
                'SELECT
                    COUNT(*) AS num
                    FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                WHERE
                    (Ep.gpo_id,Ep.ur_id) NOT IN ((1,15))
                    AND eme_id=4 ' . $and . ');');
            $this->consultar();
            while ($contMensajes = $this->extraer()) {
                $num = $contMensajes['num'];
            }
            //La segunda consulta busca si solicitan evidencias
            $this->setSql(
                'SELECT
                    COUNT(*) AS num
                FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                WHERE
                    ese_id = 3 ' . $and . ');');
            //segunda consulta
            $this->consultar();
            while ($contEvidencias = $this->extraer()) {
                $numevi = $contEvidencias['num'];
            }
            //Esta consulta regresa un listado de solicitudes de evidencias pendientes por contestar.
            $this->conectar();
            $this->setSql(
                'SELECT
                    concat("EVIDENCIA ANEXADA ",SUBSTRING(hms_mensaje,1,15)) AS hms_mensaje,
                    hms_trimestre, Ep.gpo_id AS gpo_id, Ep.ur_id AS ur_id,
                    Ep.prg_id AS prg_id, Ep.spr_id AS spr_id,
                    CAST(Ep.epg_id AS UNSIGNED) AS epg_id,
                    eme_id, DATE_FORMAT(Hi.usr_fecha ,"%e-%b") AS usr_fecha,
                    Us.usr_nombre as analista_sefin,Hi.tim_id,hms_id
                FROM cat_HistorialMensajeSeguimiento Hi
                    INNER JOIN cat_MensajeSeguimiento Msj ON Msj.msj_id=Hi.msj_id
                    INNER JOIN vin_ProductoIntermedioIndicador Mtp ON Mtp.pii_id= Msj.pii_id
                    INNER JOIN enc_Programatico Ep USING(epg_id)
                    INNER JOIN ctrlUsuario.cat_Usuario  Us ON Us.usr_id=Hi.usr_id
                    WHERE
                    ese_id=3 ' . $and . ')
                    ORDER BY usr_fecha DESC
                LIMIT 10 ;'
            );
            //Se ejecuta la consulta
            $this->consultar();
            // se guarda la información en un arreglo para enviarlo al Main
            while ($datos = $this->extraer()) {
                $evidencias[] = array(
                    'hms_mensaje' => $datos['hms_mensaje'],
                    'hms_trimestre' => $datos['hms_trimestre'],
                    'usr_fecha' => $datos['usr_fecha'],
                    'gpo_id' => $datos['gpo_id'],
                    'ur_id' => $datos['ur_id'],
                    'prg_id' => $datos['prg_id'],
                    'spr_id' => $datos['spr_id'],
                    'hms_id' => $datos['hms_id'],
                    'epg_id' => $datos['epg_id'],
                    'analista' => $datos['analista_sefin'],
                    'tim_id' => $datos['tim_id'],
                );
            }
            $this->desconectar();
        }
        //Arreglos de valores
        $respuesta['info'] = $informacion;
        $respuesta['contador'] = $num;
        $respuesta['evi'] = $evidencias;
        $respuesta['contevi'] = $numevi;
        $respuesta['contotal'] = $num + $numevi;
        echo json_encode($respuesta);
    }
}
if (isset($_POST['metodoMensajes'])) {
    new ControladorMensajes();
}
