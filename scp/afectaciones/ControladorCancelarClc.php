<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
class ControladorCancelarClc extends WebResponse
{
    protected $noavailable;
    protected $urnoavalilable;
    protected $usrUni;
    protected $usrId;
    protected $usrNvj;
    protected $usrIp;
    protected $usrUsu;

    public function __construct()
    {
        parent::__construct();
        $this->noavailable = ['Z'];
        $this->urnoavalilable = [000];
        $this->usrUni = isset($_SESSION[_S]['uni_id']) ? $_SESSION[_S]['uni_id'] : '';
        $this->usrId = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '';
        $this->usrNvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '';
        $this->usrIp = isset($_SESSION[_S]['ses_ip']) ? $_SESSION[_S]['ses_ip'] : '';
        $this->usrUsu = isset($_SESSION[_S]['usu']) ? $_SESSION[_S]['usu'] : '';
        if (isset($_SESSION[_S]['prm']['S']['2']['13']['40'])) {
            switch ($_SESSION[_S]['nvj_id']) {
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                    switch ($this->metodo) {
                        case 'DetalleClc':
                            $this->DetalleClc();
                            break;
                        case 'llenaTablaClc':
                            $this->llenaTablaClc();
                            break;
                        case 'CancelarCLC':
                            $this->CancelarCLC();
                            break;
                        case 'consultarPagadoras':
                            $this->consultarPagadoras();
                            break;
                        case 'AgregarConceptoCancelacion':
                            $this->AgregarConceptoCancelacion();
                            break;
                        case 'uploadOficioCancelacion':
                            $this->uploadOficioCancelacion();
                            break;
                        default:
                            $this->main();
                            break;
                    }
                    break;
            }
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->prm->gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->agregaTools('&nbsp;');

        $this->mixScript('
            function validaTip(){
                var s = "";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida")){s =$(this).attr("id");}
                });
                if(s == ""){
                    notify("Debe seleccionar un tipo de CLC!!","warning");
                    return false
                }else return s;
            }
        ');

        $bxClc = new stdClass();
        $bxClc->id = 'bxClc';
        $bxClc->name = 'bxClc';
        $bxClc->label = 'Número de Clc';
        $bxClc->class = 'limpiar';
        $bxClc->placeholder = 'Ingresa el número de CLC';
        $bxClc->enDiv = true;

        $bxUr = new stdClass();
        $bxUr->id = 'bxUr';
        $bxUr->name = 'bxUr';

        if ($this->prm->gpo_id != '' && $this->prm->ur_id != '') {
            $this->prm->ur_id = str_pad($this->prm->ur_id, 2, '0', STR_PAD_LEFT);
            $bxUr->value = $this->prm->gpo_id . $this->prm->ur_id;
        }
        $bxUr->label = 'UR';
        $bxUr->class = 'limpiar';
        $bxUr->placeholder = 'Ingresa la UR';
        $bxUr->enDiv = true;
        $buscar = new stdClass();
        $buscar->value = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->text = 'Buscar';
        $buscar->color = 'gris';
        $buscar->icon = 'search me-2';

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->name = 'Limpiar';
        $clean->color = 'cielo';
        $clean->icon = 'refresh me-2';

        $radios = '';

        $this->conectar();
        $this->setSql('
            select tcl_id,tcl_nombre
            from cat_TipoClc
            group by tcl_id
            order by tcl_id;
        ');
        $this->consultar();
        $this->desconectar();
        $ns = $this->getNumeroRegistros();
        if ($ns != 0) {
            $por = 100 / $ns;
        }

        $vars = $this->extraer();
        if (isset($this->prm->tipo) && isset($this->prm->nombre)) {
            $tipo = $this->prm->tipo;
            $this->mixScript('show("' . $tipo . '","' . $this->prm->nombre . '");');
        } else {
            $tipo = '';
        }
        $radios .= '<form id="myForm"><div class="d-flex justify-content-start flex-nowrap tcl-tipos-v2">';
        $checked = $extra = '';
        do {
            if ($vars['tcl_id'] == $tipo) {
                $checked = 'tcl_ida';
            } elseif (in_array($vars['tcl_id'], $this->noavailable) && in_array($this->prm->gpo_id . $this->prm->ur_id, $this->urnoavalilable)) {
                $checked = 'tcl_idb';
            } else {
                $checked = 'tcl_idn';
            }
            $radios .= '<div name="tcl_id" tabindex="0" class="tcl_idfont ' . $checked . '" id="' . $vars['tcl_id'] . '" nombre="' . $vars['tcl_nombre'] . '">';
            $radios .= '<label>' . $vars['tcl_id'] . '</label>';
            $radios .= '<span class="tcl_nombre">' . $vars['tcl_nombre'] . '</span>';
            $radios .= $extra . '</div>';

        } while ($vars = $this->extraer());

        $radios .= '</div></form>';

        $radios .= '
            <div class="panel-body">
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $this->ponInput($bxUr) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $this->ponInput($bxClc) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-3 mt-3 mb-4">
                        ' . $this->ponBoton($buscar) . ' ' . $this->ponBoton($clean) . '
                    </fieldset>
                </div>
            </div>
        ';

        $tabla = new stdClass();
        $tabla->id = 'tbl-ceres';
        $tabla->titulo = 'CANCELACIÓN DE CLC';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClc';
        $tabla->comunica->bxUr = '{$("#bxUr").val()}';
        $tabla->comunica->bxClc = '{$("#bxClc").val()}';
        $tabla->comunica->tipo = '{validaTip()}';
        $tabla->numFilas = 150;

        $tr = [];

        $th = new stdClass();
        $th->texto = 'VER';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SUBTIPO';
        array_push($tr, $th);

        $trbody = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-eye';
        $td->class = 'detalle';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->clc_id = 'clc_id';
        $td->attr->stc_id = 'stc_id';
        $td->attr->mpc_id = 'mpc_id';
        $td->attr->tcl_id = 'tcl_id';
        $td->attr->ecf_id = 'ecf_id';
        $td->attr->clc_cereCo = 'clc_cereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_fechaElaboracion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));

        $this->mixScript('
            $("#bxUr").blur(function(){
                if( $("#bxUr").val().length < 3 ){
                    notify( "Debes  escribir la UR con 3 digitos", "error");
                }
            return false;
            });

            $("#bxClc").blur(function(){
                if( $("#bxClc").val().length <1 ){
                notify( "Debes  escribir almenos  1 digito", "error");

                }
            });

            $("#buscar").click(function(){
                var s="";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida")){s =$(this).attr("id");}
                });
                var obj = {"metodo":"llenaTablaClc"};
                obj.bxUr = $("#bxUr").val();
                obj.bxClc = $("#bxClc").val();
                obj.tipo=s;
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });

            $(".tcl_idfont").click(function(){
                if($(this).hasClass("tcl_idb")){    notify("Este tipo de CLC no esta disponible","error");    }
                else{    show($(this).attr("id"),$(this).attr("nombre"));    }
            });

            function show(id,id_name){
                if(!$("#"+id).hasClass("tcl_ida")){
                    $(".tcl_idfont").removeClass("tcl_ida");
                    $(".tcl_idfont").removeClass("loadingclas");
                    $(".tcl_idfont").addClass("tcl_idn");
                    $(".tcl_idfont").removeAttr("checked");
                    $("#"+ id).removeClass("tcl_idn");
                    $("#"+id).addClass("loadingclas");
                    if (comunica("' . $this->pantalla . '",{metodo:"llenaTablaClc",tipo:id,nombre:id_name},false,false)){
                        $("#"+id).addClass("tcl_ida");
                    }
                }
            }

            $("#clean").click(function(){
                $(".limpiar").val("");
                $(".tcl_id").removeAttr("checked");
                $(".selected-afe").removeClass("selected");
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                });
                return false;
            });

            ');

        if ($this->prm->gpo_id != '' && $this->prm->ur_id != '') {
            $this->prm->bxUr = $this->prm->gpo_id . $this->prm->ur_id;
            $this->llenaTablaClc();
        }
    }

    private function llenaTablaClc()
    {
        $this->prm->gpo_id = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id)) ? $this->prm->ur_id : '';
        $wf = '';
        $params = [];

        if (isset($this->prm->tipo) and $this->prm->tipo != '') {
            array_push($params, $this->prm->tipo);
            $wf .= ' AND tcl_id = ? ';
        }

        if (isset($this->prm->bxUr) and $this->prm->bxUr != '') {
            $ur = substr($this->prm->bxUr, 1, 2);
            $gpo_id = substr($this->prm->bxUr, 0, 1);
            array_push($params, $gpo_id, $ur);
            $wf .= ' AND gpo_id = ? AND  ur_id = ? ';
        }

        if (isset($this->prm->bxClc) and $this->prm->bxClc != '') {
            array_push($params, $this->prm->bxClc);
            $wf .= ' AND clc_id = ? ';
        }

        if (isset($this->prm->gpo_id) and $this->prm->gpo_id != '') {
            array_push($params, $this->prm->gpo_id);
            $wf .= ' AND gpo_id = ? ';
        }
        if (isset($this->prm->ur_id) and $this->prm->ur_id != '') {
            array_push($params, $this->prm->ur_id);
            $wf .= ' AND ur_id = ? ';
        }
        $unidad = '';
        if (isset($this->usrUni) && $this->usrUni == 17) {
            // UNIDAD CORRESPONDIENTE A JACINTO
            $unidad = ' AND dep_id in(44,45,46) AND !(gpo_id=5 AND ur_id=31 AND pg_id=441 AND pe_id=449 AND ecf_id = 4238 )';
        } elseif (isset($this->usrUni) && $this->usrUni == 18) {
            //UNIDAD CORRESPONDIETE A martha
            $unidad = ' AND dep_id in (48,49,50)';
        }
        $this->conectar();
        $sql = '
            SELECT gpo_id,ur_id,clc_id,clc_concepto,DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,
            SUM(clc_percepcion),SUM(clc_retencion),ecl_id,tcl_id,tcl_nombre,stc_id,stc_nombre,mpc_id,mpc_nombre,ecf_id,clc_cereCo
            FROM scp_Clc
            INNER JOIN scp_ClcAuxiliar using(gpo_id,ur_id,clc_id,ecl_id)
            INNER JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,tcl_id,stc_id,ecf_id)
            LEFT JOIN scp_Presupuestal USING(gpo_id,ur_id,clc_id,afe_id,ecf_id)
            JOIN cat_TipoClc USING(tcl_id)
            JOIN cat_SubtipoClc USING(tcl_id,stc_id)
            JOIN cat_ModoPagoClc USING(mpc_id)
            JOIN enc_FiltroClc USING(gpo_id, ur_id, tcl_id,stc_id)
            WHERE ecl_id IN (10) ' . $wf . ' ' . $unidad . '
            GROUP BY gpo_id,ur_id,clc_id
            ORDER BY gpo_id,ur_id,clc_id;';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ceres';
        if ($this->wrLlenaTabla($nav) == 0) {
            if ($this->prm->tipo != '' && $this->prm->gpo_id != '' && $this->prm->ur_id != '') {
                $this->mixScript('$("#buscar").click();');
            }
        }
        $this->mixScript('
            $(".detalle").click(function() {
                comunica("' . $this->pantalla . '",{
                metodo:"DetalleClc",
                gpo_id:$(this).attr("gpo_id"),
                ur_id:$(this).attr("ur_id"),
                clc_id:$(this).attr("clc_id"),
                mpc_id:$(this).attr("mpc_id"),
                tipo:$(this).attr("tcl_id"),
                ecf_id:$(this).attr("ecf_id"),
                stc_id:$(this).attr("stc_id"),
                cereco:$(this).attr("clc_CereCo")
                });
                return false;
            });

            $(".tcl_ida").removeClass("loadingclas");
        ');
    }

    public function DetalleClc()
    {
        $this->prm->gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->mpc_id = (isset($this->prm->mpc_id) && $this->prm->mpc_id != '') ? $this->prm->mpc_id : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $this->prm->ecf_id = (isset($this->prm->ecf_id) && $this->prm->ecf_id != '') ? $this->prm->ecf_id : '';
        $this->prm->cereco = (isset($this->prm->cereco) && $this->prm->cereco != '') ? $this->prm->cereco : '';
        $wh = '';
        $params = [];
        switch ($this->prm->mpc_id) {
            case 1:
                switch ($this->prm->gpo_id . $this->prm->ur_id) {
                    case 901:
                        array_push($params, $this->prm->gpo_id, $this->prm->ur_id);
                        $wh = '
                            JOIN(
                            SELECT bur_id,iba_id
                            FROM cat_BancosUr
                            WHERE gpo_id=1 AND ur_id=15 AND bur_id=0 OR gpo_id = ? and ur_id = ? )
                            bur USING(bur_id)
                            ';
                        break;
                    default:
                        $wh = ' LEFT JOIN cat_BancosUr USING(bur_id)';
                        break;
                }
                break;
            case 2:
            case 3:
                $wh = '
                    JOIN(
                    SELECT bur_id,iba_id
                    FROM cat_BancosUr
                    WHERE gpo_id=1 AND ur_id=15 AND bur_id=0)
                    bur USING(bur_id)
                    ';
                break;
            case 4:
                $wh = ' LEFT JOIN cat_BancosUr USING(bur_id,gpo_id,ur_id)';
                break;
        }

        array_push($params, $this->prm->ecf_id, $this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id);
        $where = 'WHERE clc_consolidada=0 AND scp_Clc.gpo_id = ? and scp_Clc.ur_id = ? and clc_id = ? ';
        $this->conectar();
        $this->setSql('
            SELECT
            scp_Clc.gpo_id,scp_Clc.ur_id,clc_id,clc_concepto,DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,
            clc_percepcion,clc_retencion,ecl_id,tcl_id,tcl_nombre,
            stc_id,stc_nombre,mpc_id,mpc_nombre,MAX(iba_nombre) AS iba_nombre,
            ur_nombre,MAX(cfi_clave) AS cfi_clave ,MAX(cfi_nombre) AS cfi_nombre ,bur_id,clc_beneficiario,
            clc_percepcion,clc_retencion,MAX(bsf_id) AS bsf_id,MAX(nombreSefin) nombreSefin, SUM(clc_percepcion)-SUM(clc_retencion) AS importe,
            clc_cereCo
            FROM scp_Clc
            INNER JOIN scp_ClcAuxiliar using (gpo_id,ur_id,clc_id,ecl_id)
            INNER JOIN cat_TipoClc USING(tcl_id)
            INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
            INNER JOIN cat_ModoPagoClc USING(mpc_id)
            INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            ' . $wh . '
            JOIN enc_ClaveFinanciamiento USING(ecf_id)
            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN (
            SELECT
            cbs_id AS bsf_id ,cbs_nombre AS nombreSefin,ecf_id, iba_id
            FROM tesoreria.vin_SefinClaveFinanciamiento
            INNER JOIN tesoreria.ban_CuentaBancariaSefin USING ( cbs_id )
            INNER JOIN cat_InstitucionesBancarias USING ( iba_id )
            WHERE ecf_id = ?
            ) sefin USING (ecf_id)
            LEFT JOIN cat_InstitucionesBancarias i ON i.iba_id = sefin.iba_id
            ' . $where . ' AND ecl_id in(10,11)
            GROUP BY gpo_id,ur_id,clc_id;
        ');
        $this->consultar($params);
        $this->desconectar();
        $detalle = $this->extraer();
        if ($this->getNumeroRegistros() > 0) {
            $clas = '';
            if ($detalle['bsf_id'] == '') {
                $this->mixScript('
                    alert( "La clabe interbancaria no está vinculada o esta desactivada");
                        $("#recep").html("LA CLABE INTERBANCARIA NO ESTÁ VINCULADA");
                    ');
                $clas = 'style="border:2px solid red;" ';
            }

            $html = '
                <div class="row col-md-12 col-xs-12">
                    <div class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-12 col-xs-12" id="motivo" style="z-index:999999;">
                        </fieldset>
                    </div>
                    <fieldset class="col-md-12 col-xs-12">
                            <h6 style="text-align:center"><b><font color="#3D362D">CANCELACIÓN DE CLC </font><b></h6>
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th colspan="3" bgcolor="#006400" style="text-align:center"><font> CLC NÚMERO  ' . $detalle['clc_id'] . '  </font></th>
                            </tr>
                            <tr >
                                <td><b><font color="#2C2416">NÚMERO DE CLC</font></b></td>
                                <td><b><font color="#2C2416">TIPO DE CLC</font></b></td>
                                <td><b><font color="#2C2416">SUBTIPO DE CLC</font></b></td>
                            </tr>

                            <tr>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['clc_id'] . '</font></td>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['tcl_nombre'] . '</font></td>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['stc_nombre'] . '</font></td>
                            </tr>

                            <tr>
                                <td><b><font color="#2C2416">MODALIDAD DE PAGO</font></b></td>
                                <td><b><font color="#2C2416">CLABE INTERBANCARIA</font></b></td>
                                <td><b><font color="#2C2416">CUENTA RECEPTORA</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['mpc_nombre'] . '</font></td>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['bur_id'] . ' - ' . $detalle['iba_nombre'] . '</font></td>
                                <td id="recep" bgcolor="#DAF7A6" ' . $clas . '><font color="#000000">' . $detalle['bsf_id'] . ' - ' . $detalle['nombreSefin'] . '</font></td>
                            </tr>
                            <tr >
                                <td><b><font color="#2C2416">CLAVE DE FINANCIAMIENTO</font></b></td>
                                <td><b><font color="#2C2416">CONCEPTO DE LA CLC</font></b></td>
                                <td><b><font color="#2C2416">BENEFICIARIO DE LA CLC</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['cfi_clave'] . ' - ' . $detalle['cfi_nombre'] . '</font></td>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['clc_concepto'] . '</font></td>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . $detalle['clc_beneficiario'] . '</font></td>
                            </tr>
                            <tr >
                                <td><b><font color="#2C2416">PERCEPCIÓN</font></b></td>
                                <td><b><font color="#2C2416">RETENCIÓN</font></b></td>
                                <td><b><font color="#2C2416">IMPORTE NETO</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#DAF7A6"><font color="#000000">' . number_format($detalle['clc_percepcion'], 2) . '</font></td>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . number_format($detalle['clc_retencion'], 2) . '</font></td>
                                <td bgcolor="#DAF7A6"><font color="#000000">' . number_format($detalle['importe'], 2) . '</font></td>
                            </tr>
                            <tr >
                                <td colspan="3" style="text-align:center" bgcolor="#006400"><b>
                                    <font color="#444444">
                                    UNIDAD RESPONSABLE:' . $detalle['gpo_id'] . $detalle['ur_id'] . ' - ' . $detalle['ur_nombre'] . '
                                    </font></b></td>
                            </tr>
                        </table>
                    </fieldset>
                <div>
            ';
        } else {
            $this->mixScript(' notify("Existe un error en la CLC ' . $this->prm->clc_id . ', favor de verificarlo con informatica ","error"); ');
        }

        $cancelar = new stdClass();
        $cancelar->color = 'rojo';
        $cancelar->icon = 'solid fa-circle-xmark me-2';
        $cancelar->id = 'cancelar';
        $cancelar->name = 'cancelar';
        $cancelar->value = 'Cancelar CLC';
        $cancelar = $this->ponBoton($cancelar);

        $regresar = new stdClass();
        $regresar->class = 'me-1';
        $regresar->icon = 'solid fa-circle-left me-2';
        $regresar->color = 'gris';
        $regresar->id = 'regresar';
        $regresar->name = 'regresar';
        $regresar->value = 'Regresar';
        $regresar = $this->ponBoton($regresar);

        $this->mixHtml($html);
        $this->agregaTools(' ' . $regresar . '  ' . $cancelar . ' ');

        $this->mixScript('

            $("#cancelar").click(function(){
                var obj = {"metodo":"consultarPagadoras"};
                obj.clc_id = ' . $this->prm->clc_id . ';
                obj.gpo_id = ' . $this->prm->gpo_id . ';
                obj.ur_id = ' . $this->prm->ur_id . ';
                obj.cereco ="' . $this->prm->cereco . '";
                if(!comunica("' . $this->pantalla . '", obj,true ))
                return false;
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"main",
                gpo_id: ' . $this->prm->gpo_id . ',
                ur_id:' . $this->prm->ur_id . ',
                tipo:"' . $this->prm->tipo . '"

                });
            });

        ');

    }

    public function consultarPagadoras()
    {
        $this->prm->gpo_id = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id)) ? $this->prm->ur_id : '';
        $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
        //consultamos datos de la(s) clc pagadora
        // 19/02/2019 se verifica si el numero de control pertenece al que amortiza o paga
        $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id];
        $sql = '
            SELECT n.gpo_id, n.ur_id, n.clc_id, a.ecl_id, a.clc_cereCo
            FROM `scp_Clc` c
            INNER JOIN scp_ClcAuxiliar a USING( gpo_id, ur_id, clc_id )
            INNER JOIN `scp_Contable` n USING( gpo_id, ur_id, clc_id )
            WHERE n.ncc_gpo = ? AND n.ncc_ur = ?
            AND n.ncc_clc = ?
            AND a.ecl_id NOT IN ( 15, 17 )
            GROUP BY gpo_id, ur_id, clc_id;
        ';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        //Si tiene clcs pagadoras que no se encuentren canceladas, enviamos el mensaje.
        $mensaje = '';
        if ($this->getNumeroRegistros() > 0) {
            // La CLC a cancelar tiene una o mas pagadoras que no han sido canceladas
            $this->prm->cancelacionCadena = true;
        }
        //Si no tiene clcs pagadoras activas, enviamos al metodo para agregar concepto y proceder a la cancelación.

        $this->AgregarConceptoCancelacion();
        $this->mixScript('
            ' . $mensaje . '
        ');

    }

    private function AgregarConceptoCancelacion()
    {
        $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
        $this->prm->cereco = (isset($this->prm->cereco)) ? $this->prm->cereco : '';
        $this->prm->gpo_id = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id)) ? $this->prm->ur_id : '';
        $cancelCadena = (isset($this->prm->cancelacionCadena) ? $this->prm->cancelacionCadena : false);

        $this->setContenedor('divContenedorGeneralModal');

        $valueBoton = (isset($this->prm->cancelacionCadena) ? 'Cancelación en cadena' : 'Aceptar');
        $alerta = '';
        if ($cancelCadena) {
            $alerta = '
                <div class="alert alert-warning">
                    <strong>Advertencia!</strong>
                    No es posible cancelar la CLC de origen, debido a que tiene otras Clc\'s vinculadas.
                </div>
            ';
        }

        $file = 'scp/afectaciones/OficiosCancelacionCLC/' . $this->prm->gpo_id . '-' . str_pad($this->prm->ur_id, 2, 0, STR_PAD_LEFT) . '-' . $this->prm->clc_id . '.pdf';
        $hrefFile = $file;
        if (!file_exists($file)) {
            $file = '';
        }

        $conceptoCancelacion = new stdClass();
        $conceptoCancelacion->id = 'conceptoCancelacion';
        $conceptoCancelacion->required = true;
        $conceptoCancelacion->enDiv = true;
        $conceptoCancelacion->label = 'Concepto de cancelación';
        $conceptoCancelacion->maxlength = 100;
        $conceptoCancelacion->width = '300px';
        $conceptoCancelacion->height = '100px';

        $modal = '
            <div class="modal" id="modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Oficio de cancelación</h4>
                        <button type="button" class="btn-close close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <fieldset class="col-md-12 col-xs-12">
                            ' . $alerta . '
                            <div class="control-group">
                                <label class="control-label"> Documento Digital Oficio de cancelación</label></p>
                                <b><a id="ofcCancelacion" tabindex=-1 href="' . $file . '?npi=' . rand(1, 99999) . '" target="_blank">' . $file . '</a></b>
                                <div class="controls">
                                <input type="file" name ="oficioCancelacionFile" id="oficioCancelacion" required="1"
                                    data-url="' . $this->pantalla . '" data-form-data=\'{"metodo":"uploadOficioCancelacion","gpo_id":' . $this->prm->gpo_id . ',"ur_id":' . $this->prm->ur_id . ',"clc_id":' . $this->prm->clc_id . '}\'>
                                    <input type="hidden" id="inputValida" value=""/>
                                </div>
                            </div>
                            <div id="progressOrg" class="progress progress-striped">
                                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                    <span class="sr-only">100% Complete</span>
                                </div>
                            </div>
                            <div class="control-group">
                                ' . $this->ponTextArea($conceptoCancelacion) . '
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-outline-success cancelar">' . $valueBoton . '</button>
                        <button type="button" class="btn btn-outline-danger cerrar" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                    </div>
                </div>
            </div>
        ';

        $this->mixHtml($modal);
        $this->mixScript('
            $("#modal").show();
            $(".cerrar").click(function(){
                $("#modal").animate({"opacity":0},300,function(){ $("#modal").remove();$(".modal-backdrop").remove();} );
            });

            $("#inputValida").val("' . $this->prm->gpo_id . '-' . str_pad($this->prm->ur_id, 2, 0, STR_PAD_LEFT) . '-' . $this->prm->clc_id . '");

            $("#oficioCancelacion").click(function(){
                $("#progressOrg .progress-bar").css("width","0%" )
                    .attr("aria-valuenow",0);
            });

            $("#oficioCancelacion").fileupload({
                dataType:"json",
                done:function(e, data){
                    var validacion = data.result.validacion;
                    $("#ofcCancelacion").text(validacion.nombre);
                    $("#ofcCancelacion").attr("href","' . $hrefFile . '");
                    notify(validacion.mensaje);
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressOrg .progress-bar").css("width", progress + "%" )
                    .attr("aria-valuenow",progress);
                }
            });

            $(".cancelar").click(function(){
                if ($("#ofcCancelacion").text().trim()=="") {
                    notify("Favor de adjuntar el oficio de cancelación correspondiente","error",false);
                    return false;
                }

                if ($("#conceptoCancelacion").val().trim()=="") {
                    notify("Favor de indicar el concepto de la cancelación","error",false);
                    return false;
                }
                alertify.confirm(
                    "CANCELAR CLC",
                    "Confirme la CANCELACIÓN del CERE/CO ' . $this->prm->cereco . '",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"CancelarCLC",
                            clc_id:"' . $this->prm->clc_id . '",
                            gpo_id:"' . $this->prm->gpo_id . '",
                            ur_id:"' . $this->prm->ur_id . '",
                            cereco:"' . $this->prm->cereco . '",
                            concepto:$("#conceptoCancelacion").val()+" "+$("#ofcCancelacion").text()
                        });
                        $("#modal").animate({"opacity":0},300,function(){ $("#modal").remove();$(".modal-backdrop").remove();} );
                    },
                    function(){},
                ).set("defaultFocus", "cancel")
                .set("labels",{ok:"Aceptar",cancel:"Cancelar"});
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

    public function uploadOficioCancelacion()
    {
        $gpo_id = $this->prm->gpo_id;
        $ur_id = $this->prm->ur_id;
        $clc_id = $this->prm->clc_id;
        $name = 'oficioCancelacionFile';
        $respuesta = new stdClass();

        $allowed = ['pdf'];
        if (isset($_FILES[$name]) && $_FILES[$name]['error'] == 0) {
            $extension = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $allowed)) {
                $this->setStatus('error');
                $respuesta->mensaje = 'La extensión del archivo no esta permitida';
                $respuesta->valido = false;

            } else {
                $nomDir = dirname(__FILE__) . '/OficiosCancelacionCLC/';
                $nomFile = $gpo_id . '-' . str_pad($ur_id, 2, 0, STR_PAD_LEFT) . '-' . $clc_id;
                $nomFileDb = $_FILES[$name]['name'];

                $countArchivos = 0;
                foreach (glob($nomDir . $nomFile . '*.pdf') as $nombre_fichero) {
                    $countArchivos++;
                }

                $bolrename = false;
                if ($countArchivos > 0) {
                    $bolrename = rename($nomDir . $nomFile . '.pdf', $nomDir . $nomFile . '-' . $countArchivos . '.pdf');
                }

                $bolUpdate = move_uploaded_file($_FILES[$name]['tmp_name'], $nomDir . $nomFile . '.pdf');
                $borrado = '';

                if ($bolrename || $bolUpdate) {
                    $this->setStatus('success');
                    $respuesta->mensaje = $borrado . ' Archivo cargado con éxito';
                    $respuesta->valido = true;
                } else {
                    $this->setStatus('warning');
                    $respuesta->mensaje = $borrado . ' Houston, tenemos un problema...';
                    $respuesta->valido = false;
                }
                $respuesta->nombre = $nomFileDb;
            }
        } else {
            $this->setStatus('error');
            $respuesta->mensaje = 'Ocurrio un error al cargar el archivo';
        }
        $this->setValidacion($respuesta);
    }

    public function CancelarCLC()
    {
        error_reporting(E_ALL);
        $user = $this->usrId;
        $nvj = $this->usrNvj;

        if ($nvj < 3 || $nvj > 4) {
            $this->mixScript(' notify("ACCESO NEGADO AL PROCESO, SU NIVEL NO LE PERMITE ESTA ACCIÓN","error");');
            $this->logCancelacion('ACCESO NEGADO AL PROCESO, SU NIVEL NO LE PERMITE ESTA ACCIÓN ', $this->prm);
            return false;
        }

        $ip = (isset($this->usrIp) && $this->usrIp != '::1') ? $this->usrIp : '127.0.0.1';
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $concepto = (isset($this->prm->concepto) && $this->prm->concepto != '') ? $this->prm->concepto : '';

        $this->conectar();
        $bit_observacion = 'CANCELACION DE CLC DESDE S_2_13';
        $params = [$ip, $user, $bit_observacion];
        $sqlDatos = '
            SET @usr_ip = INET_ATON(?),
            @usr_id = ?,
            @bit_observacion = ?;
        ';
        $this->setSql($sqlDatos);
        $this->consultar($params);
        $params = [$gpo_id, $ur_id, $clc_id, $concepto, $ip, $user];
        $this->setSql(' CALL sp_CancelarCLC' . APP_EJERCICIO . '(?,?,?,?,INET_ATON(?),?) ');
        $this->consultar($params);

        $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
        $this->consultar();
        $rowmensaje = $this->extraer();
        $this->desconectar();

        if ($rowmensaje['bandera'] != 1) {
            $this->mixScript(' notify(" ' . $rowmensaje['mensaje'] . ' ","error",false); ');
            $this->logCancelacion('NO SE PUDO CANCELAR LA CLC {' . $rowmensaje['mensaje'] . '}', $this->prm);
        } else {
            $this->mixScript(' notify( "La Clc ' . $clc_id . ' ha sido cancelada  correctamente:' . $rowmensaje['mensaje'] . ' ","success",false); ');
            $this->logCancelacion('SE CANCELO LA CLC {' . $rowmensaje['mensaje'] . '}', $this->prm);
        }

        $this->mixScript('
            comunica("' . $this->pantalla . '",{
                metodo:"main",
                clc_id:' . $clc_id . ',
                tipo:"' . $this->prm->tipo . '",
                gpo_id:"' . $gpo_id . '",
                ur_id:"' . $ur_id . '"
            });
        ');
    }

    private function logCancelacion($mensaje, $prm)
    {
        $datos = '';
        foreach ($prm as $nombre => $valor) {
            if (is_object($valor) || is_array($valor)) {
                foreach ($valor as $m => $n) {
                    $datos .= $m . ':' . $n . ',';
                }
            } else {
                $datos .= $nombre . ':' . $valor . ',';
            }
        }

        $ip = (isset($this->usrIp) && $this->usrIp != '::1') ? $this->usrIp : $_SERVER['REMOTE_ADDR'];
        $registro = '33::' . (date('YmdHis')) . ' OBSERVACION[' . $mensaje . ']';
        $registro .= 'USUARIO[' . $this->usrId . '-' . $this->usrUsu . ']';
        $registro .= 'IP[' . $ip . ']';
        $registro .= PHP_EOL . '86:: DATOS[' . $datos . ']';

        $file = fopen('./log/LogCancelaClc.log', 'a');
        fwrite($file, $registro . PHP_EOL);
        fclose($file);
        $peso = filesize('./log/LogCancelaClc.log');
        $peso = intval(($peso / 1024) / 1024);
        $hoy = date('d-m-Y');

        if ($peso > 8) {
            $rutamodificada = './log/LogCancelaClc' . $hoy . '.log';
            rename('./log/LogCancelaClc', $rutamodificada);
            $file = fopen('./log/LogCancelaClc.log', 'a');
            fwrite($file, 'CREADO:' . $hoy . PHP_EOL);
            fclose($file);
        }
        unset($file);
    }
}
if (isset($_SESSION[_S]['prm']['S']['2']['13'])) {
    new ControladorCancelarClc();
} else {
    WebResponse::sinPermisos();
}
