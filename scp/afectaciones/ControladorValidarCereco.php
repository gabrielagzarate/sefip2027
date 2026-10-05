<?php

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';

class ControladorValidarCereco extends WebResponse
{
    public UnidadResponsable $ur;
    public $noavailable;
    public $urnoavalilable;

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->pantalla = 'scp/afectaciones/ControladorValidarCereco.php';
        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->noavailable = ['Z'];
        $this->urnoavalilable = [000];

        if (isset($_SESSION[_S]['prm']['S']['2']['7']['30'])) {
            switch ($_SESSION[_S]['nvj_id']) {
                case 3:case 4:case 5:case 6:
                case 7:
                    switch ($this->metodo) {
                        case 'detalleCereco':
                            $this->detalleCereco();
                            break;
                        case 'llenaTablaCerecos':
                            $this->llenaTablaCerecos();
                            break;
                        case 'validarCerecos':
                            $this->validarCerecos();
                            break;
                        case 'rechazarCereco':
                            $this->rechazarCereco();
                            break;
                        default:
                            $this->main();
                            break;
                    }
                    // no break
                default:break;
            }
        }
        $this->returnJson();
    }

    public function main()
    {
        $extra = '';
        $this->prm->gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->agregaTools('&nbsp;');

        $this->mixScript('
            function validaTip(){
                var s = "";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida")){s =$(this).attr("id");}
                });
                if(s ==""){
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
        $buscar->class = 'me-1';
        $buscar->value = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->value = 'Buscar';
        $buscar->text = 'Buscar';
        $buscar->color = 'gris';
        $buscar->icon = 'search me-2';

        $clean = new stdClass();
        $clean->class = 'me-1';
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->name = 'Limpiar';
        $clean->color = 'cielo';
        $clean->icon = 'refresh me-2';

        $radios = '';

        $this->conectar();
        $this->setSql('
            SELECT tcl_id,tcl_nombre
            FROM cat_TipoClc
            WHERE tcl_id IN ("C","D","E","H","I","J","K","M")
            GROUP BY tcl_id
            ORDER BY tcl_id;
        ');
        $this->consultar();
        $this->desconectar();
        $ns = $this->getNumeroRegistros();
        if ($ns != 0) {
            $por = 100 / $ns;
        }

        $vars = $this->extraer();
        if (isset($this->prm->tipo)) {
            $tipo = $this->prm->tipo;
            $this->mixScript('show("' . $tipo . '","");');
        } else {
            $tipo = '';
        }
        $radios .= '<form id="myForm"><div class="d-flex justify-content-start flex-nowrap tcl-tipos-v2 mb-4">';
        $checked = '';
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
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($bxUr) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($bxClc) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 mt-3 mb-4">
                ' . $this->ponBoton($buscar) . '' . $this->ponBoton($clean) . '
                </fieldset>
            </div>
        ';
        $tabla = new stdClass();
        $tabla->id = 'tbl-ceres';
        $tabla->titulo = 'Validar CERECO';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaCerecos';
        $tabla->comunica->bxUr = '{$("#bxUr").val()}';
        $tabla->comunica->bxClc = '{$("#bxClc").val()}';
        $tabla->comunica->tipo = '{validaTip()}';
        $tabla->numFilas = 150;
        $tabla->filasAmostrar = [10, 50, 100, 150];

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
        $td->color = 'verde';
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
                    notify( "Debes escribir la UR con 3 digitos", "error");
                }
            return false;
            });

            $("#bxClc").blur(function(){
                if( $("#bxClc").val().length <1 ){
                notify( "Debes escribir almenos  1 digito", "error");

                }
            });

            $("#buscar").click(function(){
                var s="";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida")){s =$(this).attr("id");}
                });
                var obj = {"metodo":"llenaTablaCerecos"};
                obj.bxUr = $("#bxUr").val();
                obj.bxClc = $("#bxClc").val();
                obj.tipo=s;
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });

            $(".tcl_idfont").click(function(){
                if($(this).hasClass("tcl_idb")){notify("Este tipo de CLC no esta disponible","error");	}
                else{	show($(this).attr("id"),$(this).attr("nombre"));	}
            });

            function show(id,id_name){
                if(!$("#"+id).hasClass("tcl_ida")){
                    $(".tcl_idfont").removeClass("tcl_ida");
                    $(".tcl_idfont").removeClass("loadingclas");
                    $(".tcl_idfont").addClass("tcl_idn");
                    $(".tcl_idfont").removeAttr("checked");
                    $("#"+ id).removeClass("tcl_idn");
                    $("#"+id).addClass("loadingclas");
                    if (comunica("' . $this->pantalla . '",{metodo:"llenaTablaCerecos",tipo:id,nombre:id_name},false,false)){
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
            $this->llenaTablaCerecos();
        }
    }

    private function llenaTablaCerecos()
    {
        $this->prm->gpo_id = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id)) ? $this->prm->ur_id : '';

        $wr = $wf = '';
        if (isset($this->prm->tipo) && $this->prm->tipo != '') {
            $wf .= ' and tcl_id= "' . $this->prm->tipo . '" ';
            switch ($this->prm->tipo) {
                case 'I':case 'J':case 'K':
                    $wf .= 'AND stc_id=02';
                    break;
                default:break;
            }
        } else {
            $wr = 'AND (
                    tcl_id IN ("C","D","E","H","M")
                    OR (tcl_id IN ("I","J","K")  AND stc_id in(02) )
                    OR (tcl_id="M" AND stc_id= 03)
                )
            ';
        }

        if (isset($this->prm->bxUr) && $this->prm->bxUr != '') {
            $ur = substr($this->prm->bxUr, 1, 2);
            $gpo_id = substr($this->prm->bxUr, 0, 1);
            $wf .= ' and gpo_id=' . $gpo_id . ' and  ur_id="' . $ur . '" ';
        }

        if (isset($this->prm->bxClc) && $this->prm->bxClc != '') {
            $wf .= ' and clc_id= ' . $this->prm->bxClc . ' ';
        }

        if (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') {
            $wf .= ' and gpo_id= ' . $this->prm->gpo_id . ' ';
        }
        if (isset($this->prm->ur_id) && $this->prm->ur_id != '') {
            $wf .= ' and ur_id= ' . $this->prm->ur_id . ' ';
        }

        $unidad = '';
        if ($_SESSION[_S]['uni_id'] == 17) {
            //$unidad = ' AND dep_id in(44,45,46) ';
            $unidad = ' AND dep_id = ' . $_SESSION[_S]['dep_id'];
        } else {
            $unidad = ' AND dep_id in (48,49)';
        }

        $this->conectar();
        $sql = '
            SELECT
                gpo_id,ur_id,clc_id,clc_concepto,
                DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") AS clc_fechaElaboracion,
                sum(clc_percepcion),sum(clc_retencion),
                ecl_id,tcl_id,tcl_nombre,
                stc_id,stc_nombre,mpc_id,
                mpc_nombre,ecf_id,clc_cereCo
            FROM scp_Clc
                INNER JOIN scp_ClcAuxiliar using (gpo_id,ur_id,clc_id,ecl_id)
                INNER JOIN cat_TipoClc USING(tcl_id)
                INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
                INNER JOIN cat_ModoPagoClc USING(mpc_id)
                INNER JOIN enc_FiltroClc USING(gpo_id, ur_id, tcl_id,stc_id)
            WHERE
                ecl_id = 7
                ' . $wf . '
                ' . $wr . '
                ' . $unidad . '
                AND clc_tipoCereCo = 2
            GROUP BY gpo_id,ur_id,clc_id
            ORDER BY gpo_id,ur_id,clc_id;
        ';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ceres';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 10;
        if (($this->wrLlenaTabla($nav) == 0) && ($this->prm->tipo != '' && $this->prm->gpo_id != '' && $this->prm->ur_id != '')) {
            $this->mixScript('$("#buscar").click();');
        }
        $this->mixScript('
            $(".detalle").click(function() {
                comunica("' . $this->pantalla . '",{
                metodo:"detalleCereco",
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

    public function detalleCereco()
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

        switch ($this->prm->mpc_id) {
            case 1:
                switch ($this->prm->gpo_id . $this->prm->ur_id) {
                    case 901:
                        $wh = ' INNER JOIN (SELECT
                                        bur_id,iba_id
                                    FROM cat_BancosUr
                                    WHERE
                                        gpo_id = 1
                                        AND ur_id = 15
                                        AND bur_id = 0
                                        OR gpo_id = ' . $this->prm->gpo_id . '
                                        AND ur_id = ' . $this->prm->ur_id . '
                                ) bur USING(bur_id)';
                        break;
                    default:
                        $wh = ' LEFT JOIN cat_BancosUr USING(bur_id)';
                        break;
                }
                break;
            case 2:case 3:
            case 4:
                $wh = 'INNER JOIN (SELECT
                            bur_id,iba_id
                        FROM cat_BancosUr
                        WHERE
                            gpo_id = 1
                            AND ur_id = 15
                            AND bur_id = 0
                    ) bur USING (bur_id)';
                break;
            case 5:
                $wh = ' LEFT JOIN cat_BancosUr USING(bur_id)';
                break;
            default:break;
        }

        $where = 'WHERE
                clc_consolidada = 0
                AND scp_Clc.gpo_id = ' . $this->prm->gpo_id . '
                AND scp_Clc.ur_id = ' . $this->prm->ur_id . '
                AND clc_id = ' . $this->prm->clc_id . ' ';
        $this->conectar();
        $this->setSql('
            SELECT
                scp_Clc.gpo_id,scp_Clc.ur_id,clc_id,clc_concepto,
                DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,
                clc_percepcion,clc_retencion,ecl_id,tcl_id,tcl_nombre,stc_id,stc_nombre,
                mpc_id,mpc_nombre,iba_nombre,ur_nombre,max(cfi_clave) as cfi_clave,max(cfi_nombre) cfi_nombre,
                bur_id,clc_beneficiario,clc_percepcion,clc_retencion,max(bsf_id) as bsf_id,max(nombreSefin) as nombreSefin,
                sum(clc_percepcion)- sum(clc_retencion) as importe,clc_cereCo
            FROM scp_Clc
                INNER JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id,ecl_id)
                INNER JOIN cat_TipoClc USING (tcl_id)
                INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                INNER JOIN cat_ModoPagoClc USING (mpc_id)
                INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                ' . $wh . '
                LEFT JOIN cat_InstitucionesBancarias USING (iba_id)
                INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                LEFT JOIN (SELECT
                            cbs_id AS bsf_id ,iba_id,cbs_nombre AS nombreSefin,ecf_id
                        FROM tesoreria.vin_SefinClaveFinanciamiento
                            INNER JOIN tesoreria.ban_CuentaBancariaSefin USING (cbs_id)
                            INNER JOIN cat_InstitucionesBancarias USING (iba_id)
                        WHERE
                            ecf_id = ' . $this->prm->ecf_id . '
                )sefin USING (ecf_id)'
            . $where . '
                AND ecl_id = 7
                AND clc_tipoCereCo = 2
            GROUP BY gpo_id,ur_id,clc_id;');

        $this->consultar();
        $this->desconectar();
        $detalle = $this->extraer();
        $bandera = true;

        if ($this->getNumeroRegistros() > 0) {
            $clas = '';
            if ($detalle['bsf_id'] == '') {
                $this->mixScript('
                        notify("La clabe interbancaria no está vinculada o esta desactivada","warning");
                        $("#recep").html("LA CLABE INTERBANCARIA NO ESTÁ VINCULADA");
                    ');
                $clas = 'style="border:2px solid red;" ';
                $bandera = false;
            }

            $html = '
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-12 col-xs-12">
                            <h6 style="text-align:center"><b><font color="#3D362D">VALIDACIÓN DE  DE CERECO </font><b></h6>
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th colspan="3" style="background-color:#006400; text-align:center"><font color="#FFFFFF">
                                    CERECO NÚMERO  ' . $detalle['clc_cereCo'] . '</font>
                                </th>
                            </tr>
                            <tr >
                                <td   bgcolor="#FFFFFF"><b><font color="#2C2416">NÚMERO DE CLC</font></b></td>
                                <td   bgcolor="#FFFFFF"><b><font color="#2C2416">TIPO DE CLC</font></b></td>
                                <td   bgcolor="#FFFFFF"><b><font color="#2C2416">SUBTIPO DE CLC</font></b></td>
                            </tr>

                            <tr>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['clc_id'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['tcl_nombre'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['stc_nombre'] . '</font></td>
                            </tr>

                            <tr>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">MODALIDAD DE PAGO</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">CLABE INTERBANCARIA</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">CUENTA RECEPTORA</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['mpc_nombre'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['bur_id'] . ' - ' . $detalle['iba_nombre'] . '</font></td>
                                <td id="recep" bgcolor="#C6E6C6" ' . $clas . '><font color="#000000">' . $detalle['bsf_id'] . ' - ' . $detalle['nombreSefin'] . '</font></td>
                            </tr>
                            <tr >
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">CLAVE DE FINANCIAMIENTO</font></b></td>
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">CONCEPTO DE LA CLC</font></b></td>
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">BENEFICIARIO DE LA CLC</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['cfi_clave'] . ' - ' . $detalle['cfi_nombre'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['clc_concepto'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['clc_beneficiario'] . '</font></td>
                            </tr>
                            <tr >
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">PERCEPCIÓN</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">RETENCIÓN</font></b></td>
                                <td  bgcolor="#FFFFFF"><b><font color="#2C2416">IMPORTE NETO</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#C6E6C6"><font color="#000000">' . number_format($detalle['clc_percepcion'], 2) . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . number_format($detalle['clc_retencion'], 2) . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . number_format($detalle['importe'], 2) . '</font></td>
                            </tr>
                            <tr >
                                <td colspan="3" style="background-color:#006400; text-align:center"><b><font color="#FFFFFF">UNIDAD RESPONSABLE: ' . $detalle['gpo_id'] . $detalle['ur_id'] . ' - ' . $detalle['ur_nombre'] . '</font></b></td>
                            </tr>
                        </table>
                    </fieldset>
                <div>
            ';
        } else {
            $this->mixScript(' notify("Existe un error en la CLC ' . $this->prm->clc_id . ', favor de verificarlo con informatica ","error"); ');
            return false;
        }

        $validar = new stdClass();
        $validar->class = 'me-1';
        $validar->color = 'verde';
        $validar->icon = 'solid fa-plus me-2';
        $validar->id = 'validar';
        $validar->name = 'validar';
        $validar->value = 'Validar';
        $validar = $this->ponBoton($validar);

        $rechazar = new stdClass();
        $rechazar->color = 'rojo';
        $rechazar->class = 'me-1';
        $rechazar->icon = 'times me-2';
        $rechazar->id = 'rechazar';
        $rechazar->name = 'rechazar';
        $rechazar->value = 'Rechazar';
        $rechazar = $this->ponBoton($rechazar);

        $regresar = new stdClass();
        $regresar->color = 'gris';
        $regresar->icon = 'solid fa-circle-left me-2';
        $regresar->id = 'regresar';
        $regresar->name = 'regresar';
        $regresar->value = 'Regresar';
        $regresar = $this->ponBoton($regresar);

        $this->mixHtml($html);
        $this->agregaTools(' ' . $regresar . ' ' . $validar . ' ' . $rechazar . ' ');

        $this->mixScript('
            $("#validar").click(function(){
                 if("' . $bandera . '"){
                    alertify.confirm("VALIDAR CERECO", "Confirme la  validación del CERECO ' . $this->prm->cereco . '", 
                    function(){
                        comunica("' . $this->pantalla . '",{
                        metodo: "validarCerecos",
                        clc_id: "' . $this->prm->clc_id . '",
                        gpo_id: "' . $this->prm->gpo_id . '",
                        ur_id:  "' . $this->prm->ur_id . '",
                        tipo:   "' . $this->prm->tipo . '",
                        cereco: "' . $this->prm->cereco . '"
                    });
                    }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Validar", cancel:"Cancelar"});
                    }else {
                        notify("La clabe interbancaria no esta vinculada o esta desactivada","alert-warning");
                    }
            });

            $("#rechazar").click(function(){
                alertify.confirm("RECHAZAR CERECO", "Confirma que desea rechazar el CERECO ' . $this->prm->cereco . '", 
                function(){
                    comunica("' . $this->pantalla . '",{
                    metodo: "rechazarCereco",
                    clc_id: "' . $this->prm->clc_id . '",
                    gpo_id: "' . $this->prm->gpo_id . '",
                    ur_id:  "' . $this->prm->ur_id . '",
                    tipo:   "' . $this->prm->tipo . '",
                    cereco: "' . $this->prm->cereco . '"
                    });
                }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Rechazar", cancel:"Cancelar"});
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

    public function validarCerecos()
    {
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $cereco = (isset($this->prm->cereco) && $this->prm->cereco != '') ? $this->prm->cereco : '';

        $this->conectar();

        $this->setSql('
            SET @usr_id = ?,
            @usr_ip = INET_ATON(?),
            @bit_observacion = "CERECO VALIDADA POR EGRESOS";
        ');
        $this->consultar([$user, $ip]);

        $this->setSql('
            UPDATE scp_ClcAuxiliar
            SET ecl_id = 8
            WHERE gpo_id = ?
            AND ur_id = ?
            AND clc_id = ?
            AND ecl_id = 7;
        ');
        $this->consultar([$gpo_id, $ur_id, $clc_id]);

        $this->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_ClcAuxiliar(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
            VALUES (?, ?, ?, 8, "CLC CON CERE VALIDADA", INET_ATON(?), ?, NOW());
        ');
        $this->consultar([$gpo_id, $ur_id, $clc_id, $ip, $user]);

        $this->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_CereCo (gpo_id,ur_id,clc_id,clc_tipoCereco,clc_cereCo,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
            VALUES (?, ?, ?, 2, ?, 8, "CERECO VALIDADA POR EGRESOS", INET_ATON(?), ?, NOW());
        ');
        $this->consultar([$gpo_id, $ur_id, $clc_id, $cereco, $ip, $user]);
        $this->desconectar();

        $this->mixScript('
            notify( "La Cereco ' . $cereco . ' ha sido validada correctamente","alert-success");
            comunica("' . $this->pantalla . '",{
                metodo:"main",
                clc_id:' . $clc_id . ',
                gpo_id:' . $gpo_id . ',
                ur_id:' . $ur_id . ',
                tipo:"' . $this->prm->tipo . '"
            });
        ');
    }

    public function rechazarCereco()
    {
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $cereco = (isset($this->prm->cereco) && $this->prm->cereco != '') ? $this->prm->cereco : '';

        $this->conectar();

        $bit_observacion = 'RECHAZO DE CLC DESDE S_2_8';
        $sqlDatos = '
            SET @usr_ip = INET_ATON(?),
            @usr_id = ?,
            @bit_observacion = ?;
        ';
        $this->setSql($sqlDatos);
        $this->consultar([$ip, $user, $bit_observacion]);

        $this->setSql('CALL sp_RechazarCLC' . APP_EJERCICIO . '(?, ?, ?, INET_ATON(?), ?);');
        $this->consultar([$gpo_id, $ur_id, $clc_id, $ip, $user]);

        $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
        $this->consultar();
        $rowmensaje = $this->extraer();
        if ($rowmensaje['bandera'] != 1) {
            $this->mixScript(' notify(" ' . $rowmensaje['mensaje'] . ' ","alert-danger"); ');
        } else {
            $this->mixScript('
                notify( "El cereco  ' . $cereco . ' ha sido rechazado  correctamente", "alert-danger");
            ');
        }

        $this->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_CereCo (gpo_id,ur_id,clc_id,clc_tipoCereco,clc_cereCo,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
            values (?, ?, ?, 2, ?, 8, "CERECO RECHAZADA POR EGRESOS", INET_ATON(?), ?, NOW());');
        $this->consultar([$gpo_id, $ur_id, $clc_id, $cereco, $ip, $user]);
        $this->desconectar();

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
}
new ControladorValidarCereco();
