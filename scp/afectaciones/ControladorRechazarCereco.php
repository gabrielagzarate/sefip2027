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

class ControladorRechazarCereco extends WebResponse
{
    protected UnidadResponsable $ur;
    protected $noavailable;
    protected $urnoavalilable;

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->pantalla = 'scp/afectaciones/ControladorRechazarCereco.php';
        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->noavailable = ['Z'];
        $this->urnoavalilable = [000];

        if (isset($_SESSION[_S]['prm']['S']['2']['5']['28'])) {
            switch ($_SESSION[_S]['nvj_id']) {
                case 3:case 4:case 5:case 6:
                case 7:
                    switch ($this->metodo) {
                        case 'DetalleCereco':
                            $this->DetalleCereco();
                            break;
                        case 'llenaTablaCerecos':
                            $this->llenaTablaCerecos();
                            break;
                        case 'RechazarCereco':
                            $this->RechazarCereco();
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
        $this->prm->gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->agregaTools('');

        $this->mixScript('
            function validaTip() {
                var s = "";
                $(".tcl_idfont").each(function() {
                    if ($(this).hasClass("tcl_ida")) {
                        s =$(this).attr("id");
                    }
                });
                if (s == "") {
                    notify("Debe seleccionar un tipo de CLC!!","warning");
                    return false;
                } else {
                    return s;
                }
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
            WHERE tcl_id IN ("F","C","D","E","H","I","J","K","M")
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
            $radios .= '</div>';
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
        $tabla->titulo = 'RECHAZAR CERECO';
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
                if ($("#bxUr").val().length < 3) {
                    notify( "Debes  escribir la UR con 3 digitos", "error");
                }
                return false;
            });

            $("#bxClc").blur(function() {
                if ($("#bxClc").val().length < 1) {
                    notify( "Debes  escribir almenos  1 digito", "error");
                }
            });

            $("#buscar").click(function() {
                var s="";
                $(".tcl_idfont").each(function() {
                    if ($(this).hasClass("tcl_ida")) {
                        s = $(this).attr("id");
                    }
                });
                var obj = {"metodo":"llenaTablaCerecos"};
                obj.bxUr = $("#bxUr").val();
                obj.bxClc = $("#bxClc").val();
                obj.tipo = s;
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });

            $(".tcl_idfont").click(function() {
                if ($(this).hasClass("tcl_idb")) {
                    notify("Este tipo de CLC no esta disponible","error");
                } else {
                    show($(this).attr("id"),$(this).attr("nombre"));
                }
            });

            function show(id,id_name) {
                if (!$("#"+id).hasClass("tcl_ida")) {
                    $(".tcl_idfont").removeClass("tcl_ida");
                    $(".tcl_idfont").removeClass("loadingclas");
                    $(".tcl_idfont").addClass("tcl_idn");
                    $(".tcl_idfont").removeAttr("checked");
                    $("#"+ id).removeClass("tcl_idn");
                    $("#"+id).addClass("loadingclas");
                    if (comunica("' . $this->pantalla . '",{
                            metodo:"llenaTablaCerecos",
                            tipo:id,
                            nombre:id_name
                        },false,false)) {
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
        $params = [];
        if (isset($this->prm->tipo) && $this->prm->tipo != '') {
            array_push($params, $this->prm->tipo);
            $wf .= ' and tcl_id = ? ';
            switch ($this->prm->tipo) {
                case 'I':case 'J':case 'K':
                    $wf .= 'AND stc_id = 02';
                    break;
                default:break;
            }
        } else {
            $wr = 'AND (
                    tcl_id IN ("C","D","E","H","M")
                    OR (tcl_id IN ("I","J","K")  AND stc_id in(02) )
                    OR (tcl_id="M" AND stc_id=03)
                )
            ';
        }

        if (isset($this->prm->bxUr) && $this->prm->bxUr != '') {
            $ur = substr($this->prm->bxUr, 1, 2);
            $gpo_id = substr($this->prm->bxUr, 0, 1);
            array_push($params, $gpo_id, $ur);
            $wf .= ' and gpo_id = ? and  ur_id = ? ';
        }

        if (isset($this->prm->bxClc) && $this->prm->bxClc != '') {
            array_push($params, $this->prm->bxClc);
            $wf .= ' and clc_id = ? ';
        }

        if (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') {
            array_push($params, $this->prm->gpo_id);
            $wf .= ' and gpo_id = ? ';
        }
        if (isset($this->prm->ur_id) && $this->prm->ur_id != '') {
            array_push($params, $this->prm->ur_id);
            $wf .= ' and ur_id = ? ';
        }

        $unidad = '';
        if ($_SESSION[_S]['uni_id'] == 17) {
            $unidad = ' AND dep_id in(44,45,46) OR (dep_id = 49 and gpo_id = 4 and ur_id = 31 and ecl_id = 6)';
        } else {
            $unidad = ' AND dep_id not in (44,45,46)';
        }

        $this->conectar();
        $sql = '
            SELECT
                gpo_id,ur_id,
                clc_id,clc_concepto,
                DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y") AS clc_fechaElaboracion,
                sum(clc_percepcion),sum(clc_retencion),
                ecl_id,tcl_id,tcl_nombre,
                stc_id,stc_nombre,mpc_id,
                mpc_nombre,ecf_id
            FROM scp_Clc
                INNER JOIN cat_TipoClc USING (tcl_id)
                INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                INNER JOIN cat_ModoPagoClc USING (mpc_id)
                INNER JOIN enc_FiltroClc USING (gpo_id, ur_id, tcl_id,stc_id)
            WHERE
                ecl_id = 8
                ' . $wf . '
                ' . $wr . '
                ' . $unidad . '
                AND clc_tipoCereCo = 2
            GROUP BY gpo_id,ur_id,clc_id
            ORDER BY gpo_id,ur_id,clc_id;
        ';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ceres';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 10;

        if (($this->wrLlenaTabla($nav) == 0) && ($this->prm->tipo != '' && $this->prm->gpo_id != '' && $this->prm->ur_id != '')) {
            $this->mixScript('$("#buscar").click();');
        }

        $this->mixScript('
            $(".detalle").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"DetalleCereco",
                    gpo_id:$(this).attr("gpo_id"),
                    ur_id:$(this).attr("ur_id"),
                    clc_id:$(this).attr("clc_id"),
                    mpc_id:$(this).attr("mpc_id"),
                    tipo:$(this).attr("tcl_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    stc_id:$(this).attr("stc_id")
                });
                return false;
            });
            $(".tcl_ida").removeClass("loadingclas");
        ');
    }

    public function DetalleCereco()
    {
        $this->prm->gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->mpc_id = (isset($this->prm->mpc_id) && $this->prm->mpc_id != '') ? $this->prm->mpc_id : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $this->prm->ecf_id = (isset($this->prm->ecf_id) && $this->prm->ecf_id != '') ? $this->prm->ecf_id : '';
        $wh = '';
        $params = [];
        switch ($this->prm->mpc_id) {
            case 1:
                switch ($this->prm->gpo_id . $this->prm->ur_id) {
                    case 901:
                        array_push($params, $this->prm->gpo_id, $this->prm->ur_id);
                        $wh = 'INNER JOIN (SELECT
                                    bur_id,iba_id
                                FROM cat_BancosUr
                                WHERE
                                    gpo_id = 1
                                    AND ur_id = 15
                                    AND bur_id = 0
                                    OR gpo_id = ?
                                    AND ur_id = ?
                            ) bur USING (bur_id)';
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

        array_push($params, $this->prm->ecf_id, $this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id);
        $where = '
            WHERE
                clc_consolidada = 0
                AND scp_Clc.gpo_id = ?
                AND scp_Clc.ur_id = ?
                AND clc_id = ? ';
        $this->conectar();
        $this->setSql('
            SELECT
                scp_Clc.gpo_id,scp_Clc.ur_id,
                clc_id,clc_concepto,
                DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") AS clc_fechaElaboracion,
                clc_percepcion,clc_retencion,
                ecl_id,tcl_id,tcl_nombre,
                stc_id,stc_nombre,mpc_id,mpc_nombre,iba_nombre,
                ur_nombre,max(cfi_clave) AS cfi_clave,
                max(cfi_nombre) AS cfi_nombre,bur_id,
                clc_beneficiario,clc_percepcion,clc_retencion,
                max(cbs_id) AS cbs_id,
                max(nombreSefin) AS nombreSefin,
                sum(clc_percepcion)- sum(clc_retencion) AS importe
            FROM scp_Clc
                INNER JOIN cat_TipoClc USING(tcl_id)
                INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
                INNER JOIN cat_ModoPagoClc USING(mpc_id)
                INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                ' . $wh . '
                LEFT JOIN cat_InstitucionesBancarias using(iba_id)
                INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                LEFT JOIN (SELECT
                            ecf_id,cbs_id,
                            iba_id,
                            iba_nombre AS nombreSefin
                        FROM tesoreria.vin_SefinClaveFinanciamiento
                            INNER JOIN tesoreria.ban_CuentaBancariaSefin USING (cbs_id)
                            INNER JOIN tesoreria.cat_InstitucionBancaria USING (iba_id)
                        WHERE
                            ecf_id = ?
                            AND sts_id = 1
                )sefin USING (ecf_id)
            ' . $where . '
                AND ecl_id = 8
                AND clc_tipoCereCo = 2
            GROUP BY gpo_id,ur_id,clc_id;
        ');
        $this->consultar($params);
        $this->desconectar();
        $detalle = $this->extraer();

        if ($this->getNumeroRegistros() > 0) {
            $clas = '';
            if ($detalle['cbs_id'] == '') {
                $this->mixScript('
                    notify( "La clabe interbancaria no está vinculada o esta desactivada","alert-danger");
                        $("#recep").html("LA CLABE INTERBANCARIA NO ESTÁ VINCULADA");
                    ');
                $clas = 'style="border:2px solid red;" ';
            }

            $html = '
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-12 col-xs-12">
                            <h6 style="text-align:center"><b><font color="#3D362D">RECHAZO DE CERECO POR ' . $_SESSION[_S]['nom'] . '</font><b></h6>
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th colspan="3" style="background-color:#006400; text-align:center"><font color="#FFFFFF">DATOS DE LA CLC</font></th>
                            </tr>
                            <tr >
                                <td><b><font color="#2C2416">NÚMERO DE CLC</font></b></td>
                                <td><b><font color="#2C2416">TIPO DE CLC</font></b></td>
                                <td><b><font color="#2C2416">SUBTIPO DE CLC</font></b></td>
                            </tr>

                            <tr>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['clc_id'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['tcl_nombre'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['stc_nombre'] . '</font></td>
                            </tr>

                            <tr>
                                <td><b><font color="#2C2416">MODALIDAD DE PAGO</font></b></td>
                                <td><b><font color="#2C2416">CLABE INTERBANCARIA</font></b></td>
                                <td><b><font color="#2C2416">CUENTA RECEPTORA</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['mpc_nombre'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['bur_id'] . ' - ' . $detalle['iba_nombre'] . '</font></td>
                                <td id="recep" bgcolor="#C6E6C6" ' . $clas . '><font color="#000000">' . $detalle['cbs_id'] . ' - ' . $detalle['nombreSefin'] . '</font></td>
                            </tr>
                            <tr >
                                <td><b><font color="#2C2416">CLAVE DE FINANCIAMIENTO</font></b></td>
                                <td><b><font color="#2C2416">CONCEPTO DE LA CLC</font></b></td>
                                <td><b><font color="#2C2416">BENEFICIARIO DE LA CLC</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['cfi_clave'] . ' - ' . $detalle['cfi_nombre'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['clc_concepto'] . '</font></td>
                                <td bgcolor="#C6E6C6"><font color="#000000">' . $detalle['clc_beneficiario'] . '</font></td>
                            </tr>
                            <tr>
                                <td><b><font color="#2C2416">PERCEPCIÓN</font></b></td>
                                <td><b><font color="#2C2416">RETENCIÓN</font></b></td>
                                <td><b><font color="#2C2416">IMPORTE NETO</font></b></td>
                            </tr>
                            <tr>
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
            $this->mixScript(' notify("Existe un error en la CLC ' . $this->prm->clc_id . ', favor de verificarlo con informatica ","alert-danger"); ');
        }

        $rechazar = new stdClass();
        $rechazar->color = 'rojo';
        $rechazar->class = 'me-1';
        $rechazar->icon = 'times me-2';
        $rechazar->id = 'rechazar';
        $rechazar->name = 'rechazar';
        $rechazar->value = 'Rechazar';
        $rechazar = $this->ponBoton($rechazar);

        $regresar = new stdClass();
        $regresar->class = 'me-1';
        $regresar->color = 'gris';
        $regresar->icon = 'solid fa-circle-left me-2';
        $regresar->id = 'regresar';
        $regresar->name = 'regresar';
        $regresar->value = 'Regresar';
        $regresar = $this->ponBoton($regresar);

        $this->mixHtml($html);
        $this->agregaTools(' ' . $regresar . ' ' . $rechazar . ' ');

        $this->mixScript('

            $("#rechazar").click(function(){
                alertify.confirm("RECHAZAR CERECO", "Realmente deseas rechazar la CLC ' . $this->prm->clc_id . ' ?", 
                function(){
                    comunica("' . $this->pantalla . '",{
                    metodo:"RechazarCereco",
                    clc_id: "' . $this->prm->clc_id . '",
                    gpo_id: "' . $this->prm->gpo_id . '",
                    ur_id:  "' . $this->prm->ur_id . '",
                    tipo:   "' . $this->prm->tipo . '",
                    tipo:   "' . $this->prm->tipo . '"
                });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Rechazar", cancel:"Cancelar"});
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

    public function RechazarCereco()
    {
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $gpo_id = (isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? $this->prm->gpo_id : '';
        $ur_id = (isset($this->prm->ur_id) && $this->prm->ur_id != '') ? $this->prm->ur_id : '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';

        $this->conectar();
        $bit_observacion = 'RECHAZO DE CLC DESDE S_2_12';
        $params = [$ip, $user, $bit_observacion];
        $sqlDatos = '
            SET @usr_ip = INET_ATON(?),
            @usr_id = ?,
            @bit_observacion = ?;
        ';
        $this->setSql($sqlDatos);
        $this->consultar($params);

        $params = [$gpo_id, str_pad($ur_id, 2, '0', STR_PAD_LEFT), $clc_id, $ip, $user];
        $this->setSql(' CALL sp_RechazarCLC' . APP_EJERCICIO . '(?,?,?,INET_ATON(?),?) ');
        $this->consultar($params);

        $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
        $this->consultar();
        $rowmensaje = $this->extraer();
        if ($rowmensaje['bandera'] != 1) {
            $this->mixScript(' notify(" ' . $rowmensaje['mensaje'] . ' ","alert-danger"); ');
        } else {
            $this->mixScript('
                notify("' . $rowmensaje['mensaje'] . ' - CLC ' . $clc_id . ' ", "alert-success");
            ');
        }

        $this->desconectar();
        $this->mixScript('
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ');
    }
}

if (isset($_SESSION[_S]['prm']['S']['2']['5']['28'])) {
    new ControladorRechazarCereco();
} else {
    WebResponse::sinPermisos();
}
