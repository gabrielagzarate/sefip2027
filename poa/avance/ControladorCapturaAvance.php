<?php

session_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/avance/MetaActividad.php';
require_once dirname(__FILE__) . '/../../ent/poa/avance/sp_ActualizaAlcanzadoTrimestre.php';

class ControladorCapturaAvance extends WebResponse
{
    private $cerrado = false;
    private $cerradoEnFecha = false;
    private $trimestre;
    private $hoy;
    public $params;
    public $estadoCap;

    public function __construct()
    { //Constructor
        parent::__construct();
        $params = [];
        $this->hoy = date('Y-m-d H:i:s');
        $this->trimestre = '';
        if ($this->hoy < '2026-07-01 00:00:00') {
            $this->trimestre = 1;
        } elseif ($this->hoy < '2026-10-01 00:00:00') {
            $this->trimestre = 2;
        } elseif ($this->hoy < '2027-01-01 00:00:00') {
            $this->trimestre = 3;
        } else {
            $this->trimestre = 4;
        }

        $this->estadoCap = 'NO CAPTURADO';

        switch ($this->metodo) {
            default:
                $this->main();
                break;
            case 'filtrocap':
                $this->filtrocap();
                break;
            case 'muestraActividades':
                $this->muestraActividades();
                break;
            case 'muestraIndicadorMeta':
                $this->muestraIndicadorMeta();
                break;
            case 'muestraMetaIndicadorProductoIntermedio':
                $this->muestraMetaIndicadorProductoIntermedio();
                break;
            case 'guardarAvance':
                $this->guardarAvance();
                break;
            case 'cerrarCaptura':
                $this->cerrarCaptura();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {

        $clasAdmin = new stdClass();
        $clasAdmin->required = true;
        $clasAdmin->comboPy = false;
        $clasAdmin->comboOa = false;

        $clasAdmin->change = '
            if($("#combosp").val()!= ""){
                comunica("' . $this->pantalla . '", {
                    metodo:"muestraActividades"
                });
            } else if($("#comboprg").val()!= "") {
                comunica("' . $this->pantalla . '", {
                    metodo:"filtrocap"
                });
            }
        ';

        $this->mixScript('$("#clasAdminSeccionCombos").show();' . ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
    }

    private function filtrocap()
    {
        $comboEstado = new stdClass();
        $comboEstado->id = 'comboEstado';
        $comboEstado->opciones = ['' => 'Seleccione una opción de la lista', 1 => $this->estadoCap, 2 => 'CAPTURADO'];
        $comboEstado->width = '400px';
        $comboEstado->label = 'ESTADO';
        $comboEstado->change = '
            if($(this).val() !=""){
                comunica("' . $this->pantalla . '", {
                    metodo:"muestraActividades",
                    estado:$("#comboEstado").val()-1
                });
            }
        ';

        $tri = $this->ponCombo($comboEstado) . '<br/><br/><div style="width:100%; padding-left:30%; padding-bottom:50px;">';

        $this->mixHtml($tri);
    }

    private function muestraActividades()
    {

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'fa-solid fa-circle-left me-1';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("nav").menusinpres("clickOpcion");
            $("#clasAdminSeccionCombos").show();
        ';
        $filtroEstado = '';
        if (isset($this->prm->estado)) {
            $filtroEstado = 'AND cap_trimestre' . $this->trimestre . '=' . $this->prm->estado;
        }
        $existespr = '';
        if ($_SESSION[_S]['clasAdm-spr'] != '') {
            $existespr = 'AND spr_id=' . $_SESSION[_S]['clasAdm-spr'] . '';
        }

        $this->setSql("
            SELECT gpo_id, ur_id, prg_id, spr_id, py_id, oa_id,REPLACE(oa_nombre,'','') AS oa_nombre,
                aprobado,  vigente,comprometido1,devengado1,ejercido1, comprometido2,devengado2, 
                ejercido2,comprometido3, devengado3,ejercido3, comprometido4,devengado4,ejercido4,
                epg_id, py_nombre, toa_id, oa_origen,metas, IF(cap_trimestre1 = metas,'CAPTURADO',
                IF(ISNULL(porcentaje1),0,FORMAT(porcentaje1,2))) porcentaje1,
                IF(cap_trimestre2 = metas,'CAPTURADO', IF(ISNULL(porcentaje2),0,FORMAT(porcentaje2,2))) porcentaje2,
                IF(cap_trimestre3 = metas,'CAPTURADO', IF(ISNULL(porcentaje3),0,FORMAT(porcentaje3,2))) porcentaje3,
                IF(cap_trimestre4 = metas,'CAPTURADO', IF(ISNULL(porcentaje4),0,FORMAT(porcentaje4,2))) porcentaje4,
                pii_id AS mem_id
            FROM cat_ObraActividad
            INNER JOIN enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
            INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN (
                SELECT COUNT(DISTINCT pii_id) AS metas, gpo_id,ur_id,epg_id,MAX(pii_id) AS pii_id,
                    SUM(IF(mat.tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1,
                    SUM(IF(mat.tri_id=2,mact_capturaAvance,NULL)) cap_trimestre2,
                    SUM(IF(mat.tri_id=3,mact_capturaAvance,NULL)) cap_trimestre3,
                    SUM(IF(mat.tri_id=4,mact_capturaAvance,NULL)) cap_trimestre4,
                    SUM(IF(mat.tri_id=1,mact_modificado,NULL)) mod_trimestre1,
                    SUM(IF(mat.tri_id=2,mact_modificado,NULL)) mod_trimestre2,
                    SUM(IF(mat.tri_id=3,mact_modificado,NULL)) mod_trimestre3,
                    SUM(IF(mat.tri_id=4,mact_modificado,NULL)) mod_trimestre4,
                    ((SUM(IF(mat.tri_id=1,mact_capturaAvance,NULL))*100)/COUNT(pii_id)) porcentaje1,
                    ((SUM(IF(mat.tri_id=2,mact_capturaAvance,NULL))*100)/COUNT(pii_id)) porcentaje2,
                    ((SUM(IF(mat.tri_id=3,mact_capturaAvance,NULL))*100)/COUNT(pii_id)) porcentaje3,
                    ((SUM(IF(mat.tri_id=4,mact_capturaAvance,NULL))*100)/COUNT(pii_id)) porcentaje4
                FROM cat_MetaActividad mac
                INNER JOIN ( SELECT pii_id, epg_id, tri_id, mact_original, mact_modificado, mact_alcanzado,
                    mact_capturaAvance, 4 AS nim_id
                    FROM cat_MetaActividadTrimestre
                ) mat USING (pii_id,epg_id)
                INNER JOIN enc_Programatico USING(epg_id)
                INNER JOIN mir_TrimestreUr USING (gpo_id, ur_id,tri_id,nim_id)
                GROUP BY epg_id
            )meta USING(gpo_id,ur_id,epg_id)
            INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
            LEFT JOIN (
                SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id, SUM(pre_vigente) AS pre_vigente,epg_id,
                    SUM(pre_aprobado) AS aprobado, SUM(pre_vigente) AS vigente,
                    SUM(vig_ene+vig_feb+vig_mar) vig_trimestre1, SUM(vig_abr+vig_may+vig_jun) vig_trimestre2,
                    SUM(vig_jul+vig_ago+vig_sep) vig_trimestre3, SUM(vig_oct+vig_nov+vig_dic)vig_trimestre4,
                    SUM(com_ene+com_feb+com_mar) comprometido1,SUM(dev_ene+dev_feb+dev_mar) devengado1,
                    SUM(eje_ene+eje_feb+eje_mar) ejercido1,SUM(com_abr+com_may+com_jun) comprometido2,
                    SUM(dev_abr+dev_may+dev_jun) devengado2,SUM(eje_abr+eje_may+eje_jun) ejercido2,
                    SUM(com_jul+com_ago+com_sep) comprometido3,SUM(dev_jul+dev_ago+dev_sep) devengado3,
                    SUM(eje_jul+eje_ago+eje_sep) ejercido3,SUM(com_oct+com_nov+com_dic) comprometido4,
                    SUM(dev_oct+dev_nov+dev_dic) devengado4,SUM(eje_oct+eje_nov+eje_dic) ejercido4
                FROM presupuesto
                INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id
            )p USING(gpo_id,ur_id,prg_id, spr_id, py_id,oa_id,epg_id)
            WHERE gpo_id=? AND ur_id=? AND
                ue_id=? AND
                prg_id=? " . $existespr . '
                AND ((eoa_id=5 AND toa_id=2) OR (eoa_id=4 AND toa_id=1))
                AND (mod_trimestre' . $this->trimestre . ' >0 || vig_trimestre' . $this->trimestre . '>0)
                ' . $filtroEstado . '
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $_SESSION[_S]['clasAdm-ue'],
            $_SESSION[_S]['clasAdm-prg'],
        ];
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        } else {
            $this->mixScript('$("#clasAdminSeccionCombos").hide();');
        }
        // Se agrega filtro por estado de captura
        $comboEstado = new stdClass();
        $comboEstado->id = 'comboEstado';
        $comboEstado->opciones = ['' => 'Seleccione una opción de la lista', 1 => $this->estadoCap, 2 => 'CAPTURADO'];
        $comboEstado->width = '400px';
        $comboEstado->label = 'ESTADO';
        $comboEstado->change = '
            if($(this).val() !=""){
                comunica("' . $this->pantalla . '", {
                    metodo:"muestraActividades",
                    estado:$("#comboEstado").val()-1
                });
            }
        ';

        $verTrimestre2 = $this->mesAbierto();
        $tri = $this->ponCombo($comboEstado) . '<br/><br/><div style="width:100%; padding-left:30%; padding-bottom:50px;">';
        $trimestre = ['Ene-Feb-Mar', 'Abr-May-Jun', 'Jul-Ago-Sep', 'Oct-Nov-Dic'];
        $trimestreActivo = 1;
        $caputraTrimestre = 0;
        $trimestreMostrar = 0;


        $boton = new stdClass();
        $boton->icon = 'glyphicon2 glyphicon2-excel';
        $boton->id = 'exportar';
        $boton->value = ' Exportar';
        $boton->color = 'verde';
        $boton->click = '
            var form = $("<form>")
                .attr("action", "rep/excel/ExportaAvance.php")
                .attr("method", "post")
                .attr("target", "_blank");
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        $btnPrint = new stdClass();
        $btnPrint->id = 'printAvance';
        $btnPrint->value = '';
        $btnPrint->color = 'azul';
        $btnPrint->type = 'button';
        $btnPrint->icon = 'print';
        $btnPrint->class = 'imprimirTrimestre';
        $btnPrint->attr = new stdClass();


        for ($x = 0; $x <= 3; $x++) {
            $btnPrint->attr->trimestre = ($x + 1);
            $tri .= '
                <div class="boton_opcion">
                    <div class="boton_opcion_titulo">
                        <strong>
                            <span style="font-size: 20px;" class="" aria-hidden="true"></span>' . $trimestre[$x] . '

                    </div>
                    <div>' .
                        ((($verTrimestre2['trimestre' . ($x + 1) . ''] == 1 && $verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'A') ? '
                            <strong><span style="font-size: 40px;" class="fa fa-' .
                        (($verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'C') ? (($verTrimestre2['trimestre' . ($x + 1) . ''] == 0) ? 'lock' : 'ban') : 'ban') . '" aria-hidden="true"></span>
                        ' :
                        ($verTrimestre2['concluido' . ($x + 1) . ''] == 1 && $verTrimestre2['trimestre' . ($x + 1) . ''] == 1 && $trimestreActivo == 1)) ? '' : '<strong><span style="font-size: 40px;" class="fa fa-lock" aria-hidden="true"></span>') . '</strong>
                    <div>
            ';
            //si la captura esta concluidad o el trimestre esta en el periodo
            if ($verTrimestre2['concluido' . ($x + 1) . ''] == 1 && $verTrimestre2['trimestre' . ($x + 1) . ''] == 1 && $trimestreActivo == 1 && $verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'A') {
                if ($trimestreMostrar == 0) {
                    $trimestreMostrar = ($x + 1);
                }

                $caputraTrimestre = $verTrimestre2['trimestre' . ($x + 1) . ''];
                $trimestreActivo = 0;
                $boton = new stdClass();
                $boton->value = ' Cerrar Captura';
                $boton->id = 'closeCapture';
                $boton->color = 'verde';
                $boton->icon = 'lock';
                $boton->click = '
                    alertify.confirm("CERRAR CAPTURA","¿Confirma que desea cerrar la captura del trimestre ' . ($x + 1) . ' ?",
                        function() {
                            comunica("' . $this->pantalla . '",{ 
                                metodo: "cerrarCaptura",
                                trimestre:' . ($x + 1) . '
                            });
                        },
                        function(){}
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                ';
                $tri .= '<p>' . ($verTrimestre2['porcentaje' . ($x + 1) . '']) . ' % Capturado</p>' . $this->ponBoton($boton) . '';
            } else {
                if ($trimestreActivo == 1 && $verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'A') {
                    $trimestreActivo = 0;
                    if ($trimestreMostrar == 0) {
                        $trimestreMostrar = ($x + 1);
                    }
                }
                if ($verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'A' && $verTrimestre2['concluido' . ($x + 1) . ''] == 0) {
                    $tri .= '<p>' . ($verTrimestre2['porcentaje' . ($x + 1) . '']) . ' % Capturado</p>';
                    $trimestreActivo = 1;
                } elseif ($verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'C' && $verTrimestre2['concluido' . ($x + 1) . ''] == 1) {
                    $caputraTrimestre = $verTrimestre2['trimestre' . ($x + 1) . ''];
                    $tri .= '<small>Captura Finalizada</small> <br>' . $this->ponBoton($btnPrint);
                } else {
                    $tri .= '<small>Captura Bloqueada</small>';
                }
            }

            $tri .= '</strong></div>
                    </div>
                </div><div style="width:10px; display:table-cell"></div>
            ';
        }

        $this->agregaTools($this->ponBoton($btnregresarindex));

        if ($trimestreMostrar == 0) {
            if ($verTrimestre2['est_trimestre1'] == 'C' && $verTrimestre2['porcentaje1'] != 0) {
                $trimestreMostrar = 1;
            } elseif ($verTrimestre2['est_trimestre2'] == 'C' && $verTrimestre2['porcentaje2'] != 0) {
                $trimestreMostrar = 2;
            } elseif ($verTrimestre2['est_trimestre3'] == 'C' && $verTrimestre2['porcentaje3'] != 0) {
                $trimestreMostrar = 3;
            } elseif ($verTrimestre2['est_trimestre4'] == 'C' && $verTrimestre2['porcentaje4'] != 0) {
                $trimestreMostrar = 4;
            }
        }

        if ($verTrimestre2['porcentaje1'] != 0) {
            $por = 1;
        }

        if ($trimestreMostrar == 0) {
            $trimestreMostrar = 1;
        }

        $por = $trimestreMostrar;

        $this->mixHtml($tri . '</div><br/>
            <table  class="table table table-bordered table-hover">
        ');

        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';
        $htm = '';
        $cambioClase = true;
        while ($proyectos = $this->extraer()) {
            if ($proyectos['prg_id'] != $prg || $proyectos['spr_id'] != $spr || $proyectos['py_id'] != $py) {
                $htm .= '<tr>
                        <th class="warning" style="text-align:center" colspan="1">PYT:</th>
                        <th class="warning" style="text-align:center" colspan="1">' . $proyectos['py_id'] . '</th>
                        <td align="left" colspan="5">' . $proyectos['py_nombre'] . '</td>
                        <th class="warning" align="center" colspan="2">APROBADO</th>
                        <th class="warning" align="center" colspan="2">MODIFICADO</th>
                        <th class="warning" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="warning" align="center" colspan="2">DEVENGADO</th>
                        <th class="warning" align="center" colspan="2">EJERCIDO</th>
                        <th class="warning" align="center" colspan="2">No. METAS</th>
                        <th class="warning" align="center" colspan="2">ESTATUS</th>
                    </tr>';
            }
            if ($proyectos['prg_id'] != $prg || $proyectos['spr_id'] != $spr || $proyectos['py_id'] != $py || $proyectos['oa_id'] != $oa) {
                $clase = ($cambioClase) ? 'class="warning"' : '';
                $htm .= '<tr class="warning" >
                    <td ' . $clase . ' align="center" colspan="1">
                        <button type="button" id="verActividad" icon="fa fa-eye" title="Ver el Indicador/Meta de la Actividad" class="verActividad btn btn-outline-secondary" prg_id=' . $proyectos['prg_id'] . ' spr_id=' . $proyectos['spr_id'] . ' py_id=' . $proyectos['py_id'] . ' oa_id=' . $proyectos['oa_id'] . ' toa_id=' . $proyectos['toa_id'] . ' epg_id=' . $proyectos['epg_id'] . ' oa_nombre="' . str_replace('"', '', $proyectos['oa_nombre']) . '">
                            <i class="fa fa-eye"></i>
                        </button>
                    </td>
                    <td ' . $clase . ' align="center" colspan="1">' . $proyectos['prg_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $proyectos['spr_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $proyectos['py_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $proyectos['oa_id'] . '</td>
                    <td ' . $clase . ' align="left" colspan="2">' . $proyectos['oa_nombre'] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . $proyectos['aprobado'] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . $proyectos['vigente'] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . $proyectos['comprometido' . $trimestreMostrar . ''] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . $proyectos['devengado' . $trimestreMostrar . ''] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . $proyectos['ejercido' . $trimestreMostrar . ''] . '</td>
                    <td ' . $clase . ' align="center" colspan="2">' . $proyectos['metas'] . '</td>
                    <td ' . $clase . ' align="center" colspan="2">' . ($proyectos['porcentaje' . $por . ''] == 'CAPTURADO' ? 'CAPTURADO' : '<font color="#FE0000"> ' . $this->estadoCap . ' </font>') . '</td>
                </tr>';
                $cambioClase = ($cambioClase) ? false : true;
            }

            $ur = $proyectos['gpo_id'] . $proyectos['ur_id'];
            $prg = $proyectos['prg_id'];
            $spr = $proyectos['spr_id'];
            $py = $proyectos['py_id'];
            $oa = $proyectos['oa_id'];
        }
        $htm .= ' </table> ';
        $this->mixHtml($htm);

        $this->mixScript('
            $(".verActividad").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"muestraIndicadorMeta",
                    prg_id:$(this).attr("prg_id"),
                    spr_id:$(this).attr("spr_id"),
                    py_id:$(this).attr("py_id"),
                    oa_id:$(this).attr("oa_id"),
                    oa_nombre:$(this).attr("oa_nombre"),
                    epg_id:$(this).attr("epg_id"),
                    toa_id:$(this).attr("toa_id")
                });
            });

            $(".imprimirTrimestre").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Avance.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "capturandoTri").val( $(this).attr("trimestre") ))
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    private function muestraIndicadorMeta()
    {

        //regresar a muestraActividades
        $regresar = new stdClass();
        $regresar->id = 'regresar';
        $regresar->icon = 'fa-solid fa-circle-left me-1';
        $regresar->color = 'gris';
        $regresar->class = 'me-1';
        $regresar->value = 'Regresar';

        $this->agregaTools($this->ponBoton($regresar));

        $and = '';

        $this->setSql('
            SELECT gpo_id, ur_id, prg_id, spr_id, py_id, oa_id,nim_id,ind_id, ind_nombreMIR, ind_definicionMIR,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, umi_id AS umi_id,
                umi_nombre, umv_id AS umi_mir, umv_nombre AS umiMir_nombre, tiv_id,
                tiv_nombre,ses_id,ses_nombre, frm_id,frm_nombre,
                pii_id,mac_descripcion,pdf_id,pdi_id, epg_id,
                ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                mac_original, mac_modificado, mac_alcanzado, ((mac_alcanzado/mac_modificado) * 100) AS porcentajeMeta,
                mac_agrupadorPresupuestal
            FROM cat_MetaActividad m
            INNER JOIN (
                SELECT pii_id, epg_id,SUM(IF(tri_id=1,mact_original,NULL)) ori_trimestre1,
                    SUM(IF(tri_id=2,mact_original,NULL)) ori_trimestre2, SUM(IF(tri_id=3,mact_original,NULL)) ori_trimestre3,
                    SUM(IF(tri_id=4,mact_original,NULL)) ori_trimestre4
                FROM cat_MetaActividadTrimestre
                GROUP BY pii_id,epg_id
            )mat USING (pii_id,epg_id)
            INNER JOIN (
                SELECT  pii_id,nim_id,ind_id,pdi_id
                FROM vin_ProductoIntermedioIndicador
            ) e USING(pii_id)
            INNER JOIN enc_Programatico USING(epg_id)
            INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN (
                SELECT umi_id,umv_id,nim_id,ind_id,tpi_id,dim_id,frm_id,tiv_id,
                ses_id,ind_nombreMIR,ind_definicionMIR,ind_formulaCalculo
                FROM cat_Indicador
            )i USING(nim_id,ind_id)
            INNER JOIN cat_TipoIndicador USING(tpi_id)
            INNER JOIN cat_Dimension USING(dim_id)
            INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
            INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
            INNER JOIN cat_TipoValor USING(tiv_id)
            INNER JOIN cat_SentidoEsperado USING(ses_id)
            INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            WHERE gpo_id = ? AND ur_id=? AND
                prg_id = ? AND spr_id=?
                AND py_id=? AND oa_id=?
            AND ((eoa_id=5 AND toa_id=2) OR (eoa_id=4 AND toa_id=1))
            ORDER BY pii_id;
        ');

        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->prg_id,
            $this->prm->spr_id,
            $this->prm->py_id,
            $this->prm->oa_id,
        ];

        $this->conectar();
        $this->consultar($params);
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {
            $this->mixHtml('<div class="alert alert-info alert-dismissable" align="center"><strong>Aún no cuenta con Indicador / Meta</strong>');
        }

        $verEditarMeta = new stdClass();
        $verEditarMeta->id = 'verEditarMeta';
        $verEditarMeta->icon = 'pencil';
        $verEditarMeta->color = 'gris';
        $verEditarMeta->class = 'me-1';
        $verEditarMeta->title = 'Ver o Editar la meta de la Actividad';

        //verifica si ya existe una actividad marcada y ya no podran seleccionar otra meta para vincular
        $existeVin = new AccesoDatos();
        $existeVin->conectar();
        $sqlVin = '
            SELECT mac_agrupadorPresupuestal
            FROM cat_MetaActividad WHERE epg_id=' . $this->prm->epg_id . ' and mac_agrupadorPresupuestal = 1;';
        $existeVin->setSql($sqlVin);
        $existeVin->consultar();
        $existeVin->desconectar();

        $ban = 0;
        if ($existeVin->getNumeroRegistros() == 1 || $this->prm->prg_id == 000) {
            $ban = 1;
        }

        $vinculado = 0;
        if (isset($this->prm->vinculado) && $this->prm->vinculado != 0 || $ban == 1) {
            $vinculado = 1;
        }

        $html = '';

        $html = '
            <br/>
            <div>
                <fieldset id="verMetaIndicadorProductoIntermedio"></fieldset>
                    <table class="table table-condensed">
                        <tr>
                            <td colspan=4 class="success">
                                <strong><font size=3>Nombre Obra/Actividad: "' . trim($this->prm->oa_nombre) . '"</font></strong>
                            </td>
                        </tr>
        ';
        $numMeta = 0;
        $epg = 0;
        while ($row = $this->extraer()) {
            $numMeta++;


            $html .= '
                    <tr class="warning">
                        <td rowspan="1"><strong><font size=3>Datos del indicador ' . $row['ind_id'] . ' / Meta ' . $numMeta . '</font> </strong></td>
                        ' . ($ban != 1 ? '
                            <td><strong>Agrupador presupuestal: </strong><input type="radio" id="ckeckMeta" name="ckeckMeta" class="vincularMeta" ind_id="' . $row['ind_id'] . '"  pdf_id="' . $row['pdf_id'] . '" pdi_id="' . $row['pdi_id'] . '" epg_id="' . $row['epg_id'] . '" noMeta=' . $numMeta . ' descripcion="' . $row['mac_descripcion'] . '" gpo_id=' . $row['gpo_id'] . ' ur_id="' . $row['ur_id'] . '" prg_id="' . $row['prg_id'] . '" pii_id=' . $row['pii_id'] . ' value="' . $row['pii_id'] . '  "/></td>
                        ' : '
                            <td><strong>Agrupador presupuestal: </strong><input type="radio" id="ckeckMeta" ' . ($row['mac_agrupadorPresupuestal'] == 1 || $this->prm->prg_id == 000 ? 'checked "' : '') . ' disabled="disabled name="ckeckMeta" class="vincularMeta" ind_id="' . $row['ind_id'] . '" pdf_id="' . $row['pdf_id'] . '" pdi_id="' . $row['pdi_id'] . '" epg_id="' . $row['epg_id'] . '" noMeta=' . $numMeta . ' descripcion="' . $row['mac_descripcion'] . '" gpo_id=' . $row['gpo_id'] . ' ur_id="' . $row['ur_id'] . '" prg_id="' . $row['prg_id'] . '" mem_id=' . $row['pii_id'] . ' value="' . $row['pii_id'] . '  "/></td>
                        ') . '
                        <td><strong>Porcentaje: ' . $row['porcentajeMeta'] . ' %</strong></td>
                        <td></td>
                    </tr>
                    <tr><td colspan=3><strong>Nombre del Indicador: </strong>' . $row['ind_nombreMIR'] . '</td></tr>
                    <tr><td colspan=3><strong>Definición del Indicador: </strong>' . $row['ind_definicionMIR'] . '</td></tr>
                    <tr><td colspan=3><strong>Método de cálculo: </strong>' . $row['ind_formulaCalculo'] . '</td>
                        <td class="success"><strong >REGISTRO DE AVANCE DE METAS</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Tipo de indicador: </strong>' . $row['tpi_id'] . ' - ' . $row['tpi_nombre'] . '</td>
                        <td><strong>Dimensión: </strong>' . $row['dim_id'] . ' - ' . $row['dim_nombre'] . '</td>
                        <td><strong>Unidad de medida: </strong>' . $row['umi_mir'] . ' - ' . $row['umiMir_nombre'] . '</td>
                        <td>
                            <button type="button" id="EditarMeta' . $row['pii_id'] . '" pii_id=' . $row['pii_id'] . ' ind_id=' . $row['ind_id'] . ' epg_id="' . $row['epg_id'] . '" class="btn btn-outline-secondary verEditarMeta act" title="Ver o Editar la meta de la Actividad">
                                <i class="fa fa-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tipo de valor: </strong>' . $row['tiv_id'] . ' - ' . $row['tiv_nombre'] . '</td>
                        <td><strong>Sentido esperado: </strong>' . $row['ses_id'] . ' - ' . $row['ses_nombre'] . '</td>
                        <td><strong>Frecuencia de medición: </strong>' . $row['frm_id'] . ' - ' . $row['frm_nombre'] . '</td>
                    </tr>

                    <tr>
                        <td class="success"><strong>Descripción de la Meta</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Definición de la variable Meta: </strong>' . $row['mac_descripcion'] . '</td>
                        <td><strong>Unidad de medida: </strong>' . $row['umi_id'] . ' - ' . $row['umi_nombre'] . '</td>
                    </tr>

                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th>Primer Trimestre</th>
                                <th>Segundo Trimestre</th>
                                <th>Tercer Trimestre</th>
                                <th>Cuarto Trimestre</th>
                                <th>Anual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>' . $row['ori_trimestre1'] . '</td>
                                <td>' . $row['ori_trimestre2'] . '</td>
                                <td>' . $row['ori_trimestre3'] . '</td>
                                <td>' . $row['ori_trimestre4'] . '</td>
                                <td>' . $row['mac_original'] . '</td>
                            </tr>
                            <tr class="warning">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
            ';
            $epg = $row['epg_id'];
            if ($row['epg_id'] != $epg) {
                $numMeta = 0;
            }
        }

        $html .= '
                        </tbody>
                    </table>
                </table>
            </div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#regresar").click(function(){
                $("#toolsPantalla").html("");
                $("nav").menusinpres("quitaBreadcrumbs");
                comunica("' . $this->pantalla . '",{
                    metodo:"muestraActividades"
                });
                desbloquearClasAdmin();
                return false;
            });

            $(".verEditarMeta.act").each(function(){
                var mac = $(this).prop("id");
                $(this).prop("pii_id",mac.substring(10,mac.length));
                $(this).prop("ind_id",mac.substring(10,mac.length));
            });

            $(".verEditarMeta").click(function(){
                console.log( $(this).prop("pii_id"));
                var p = {
                    metodo:"muestraMetaIndicadorProductoIntermedio",
                    pii_id: $(this).attr("pii_id"),
                    epg_id: $(this).attr("epg_id")
                };
                comunica("' . $this->pantalla . '",p);
            });
        ');
    }

    private function muestraMetaIndicadorProductoIntermedio()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //semaforizacion
        $por = new AccesoDatos();
        $por->conectar();
        $por->setSql('
            SELECT tri_id
            from cat_MetaActividadTrimestre mt 
            where
            pii_id = ?
            AND epg_id = ?
        ');
        $por->consultar([$this->prm->pii_id,$this->prm->epg_id]);
        $tri = $por->extraer();
        $por->desconectar();
        $color1 = '';
        $color2 = '';
        $color3 = '';
        $color4 = '';

        foreach ($tri as $v) {
            if ($tri = 1) {
                $t1 = new AccesoDatos();
                $t1->conectar();
                $t1->setSql('
                    SELECT *,
                    (CASE
                        WHEN tri_id=1 THEN (mact_alcanzado*100)/IF ( mact_modificado > 0, mact_modificado, mact_original)
                    END) AS porcentaje1
                    from cat_MetaActividadTrimestre mt 
                    where
                    pii_id = ?
                    AND epg_id = ?
                    AND tri_id=1
                ');
                $t1->consultar([$this->prm->pii_id,$this->prm->epg_id]);
                $dat1 = $t1->extraer();
                $t1->desconectar();
            }
            if ($tri = 2) {
                $t2 = new AccesoDatos();
                $t2->conectar();
                $t2->setSql('
                    SELECT *,
                    (CASE
                        WHEN tri_id=2 THEN (mact_alcanzado*100)/ IF ( mact_modificado > 0, mact_modificado, mact_original)
                    END) AS porcentaje2
                    from cat_MetaActividadTrimestre mt 
                    where
                    pii_id = ?
                    AND epg_id = ?
                    AND tri_id=2
                ');
                $t2->consultar([$this->prm->pii_id,$this->prm->epg_id]);
                $dat2 = $t2->extraer();
                $t2->desconectar();
            }
            if ($tri = 3) {
                $t3 = new AccesoDatos();
                $t3->conectar();
                $t3->setSql('
                    SELECT *,
                    (CASE
                        WHEN tri_id=3 THEN (mact_alcanzado*100)/IF ( mact_modificado > 0, mact_modificado, mact_original)
                    END) AS porcentaje3
                    from cat_MetaActividadTrimestre mt 
                    where
                    pii_id = ?
                    AND epg_id = ?
                    AND tri_id=3
                ');
                $t3->consultar([$this->prm->pii_id,$this->prm->epg_id]);
                $dat3 = $t3->extraer();
                $t3->desconectar();
            }
            if ($tri = 4) {
                $t4 = new AccesoDatos();
                $t4->conectar();
                $t4->setSql('
                    SELECT *,
                    (CASE
                        WHEN tri_id=4 THEN (mact_alcanzado*100)/IF ( mact_modificado > 0, mact_modificado, mact_original)
                    END) AS porcentaje4
                    from cat_MetaActividadTrimestre mt 
                    where
                    pii_id = ?
                    AND epg_id = ?
                    AND tri_id=4
                ');
                $t4->consultar([$this->prm->pii_id,$this->prm->epg_id]);
                $dat4 = $t4->extraer();
                $t4->desconectar();
            }
        }

        if ($dat1['porcentaje1'] >= 0 && $dat1['porcentaje1'] < 59.99) {
            $color1 = 'style="color: #b20000;"';// ROjo
        }
        if ($dat1['porcentaje1'] >= 60 && $dat1['porcentaje1'] < 84.99) {
            $color1 = 'color="black" style="color: #f1ee0b;"';// amarillo
        }
        if ($dat1['porcentaje1'] >= 85 && $dat1['porcentaje1'] <= 115) {
            $color1 = 'style="color: #35B200;"'; //verde
        }
        if ($dat1['porcentaje1'] > 115) {
            $color1 = 'style="color: #b20000;"'; //rojo
        }
        //tri 2
        if ($dat2['porcentaje2'] >= 0 && $dat2['porcentaje2'] < 59.99) {
            $color2 = 'style="color: #b20000;"';// ROjo
        }
        if ($dat2['porcentaje2'] >= 60 && $dat2['porcentaje2'] < 84.99) {
            $color2 = 'style="color: #f1ee0b;"';// amarillo
        }
        if ($dat2['porcentaje2'] >= 85 && $dat2['porcentaje2'] <= 115) {
            $color2 = 'style="color: #35B200;"'; //verde
        }
        if ($dat2['porcentaje2'] > 115) {
            $color2 = 'style="color: #b20000;"'; //rojo
        }
        //tri 3
        if ($dat3['porcentaje3'] >= 0 && $dat3['porcentaje3'] < 59.99) {
            $color3 = 'style="color: #b20000;"';// ROjo
        }
        if ($dat3['porcentaje3'] >= 60 && $dat3['porcentaje3'] < 84.99) {
            $color3 = 'style="color: #f1ee0b;"';// amarillo
        }
        if ($dat3['porcentaje3'] >= 85 && $dat3['porcentaje3'] <= 115) {
            $color3 = 'style="color: #35B200;"'; //verde
        }
        if ($dat3['porcentaje3'] > 115) {
            $color3 = 'style="color: #b20000;"'; //rojo
        }
        //tri 4
        if ($dat4['porcentaje4'] >= 0 && $dat4['porcentaje4'] < 59.99) {
            $color4 = 'style="color: #b20000;"';// ROjo
        }
        if ($dat4['porcentaje4'] >= 60 && $dat4['porcentaje4'] < 84.99) {
            $color4 = 'style="color: #f1ee0b;"';// amarillo
        }
        if ($dat4['porcentaje4'] >= 85 && $dat4['porcentaje4'] <= 115) {
            $color4 = 'style="color: #35B200;"'; //verde
        }
        if ($dat4['porcentaje4'] > 115) {
            $color4 = 'style="color: #b20000;"'; //rojo
        }

        $meta = new MetaActividad($this->prm);

        $trimestres = '';
        $trimestreLabel = ['Primer', 'Segundo', 'Tercer', 'Cuarto'];
        $trimestreOriginal = [$meta->getOriTrimestre1(), $meta->getOriTrimestre2(), $meta->getOriTrimestre3(), $meta->getOriTrimestre4()];

        $trimestreModificado = [$meta->getModTrimestre1(), $meta->getModTrimestre2(), $meta->getModTrimestre3(), $meta->getModTrimestre4()];
        $trimestreAvance = [$meta->getAlcTrimestre1(), $meta->getAlcTrimestre2(), $meta->getAlcTrimestre3(), $meta->getAlcTrimestre4()];
        $estadoCapturado = [$meta->getEstadoCapTrim1(), $meta->getEstadoCapTrim2(), $meta->getEstadoCapTrim3(), $meta->getEstadoCapTrim4()];
        $verTrimestre = $this->metaAbierta();
        $formActivo = true;
        $activado = '';
        $trimes = '';
        for ($x = 0; $x < 5; $x++) {

            $trimestres .= ($x == 0 || $x == 2) ? '<div class="col">' : '';
            $form = new stdClass();
            $form->id = 'MetaIndicadorProductoIntermedio' . $x;
            $form->controlador = $this->pantalla;
            $form->metodo = 'guardarAvance';
            $form->datosExtra = new stdClass();
            $form->datosExtra->epg_id = $this->prm->epg_id;
            $form->datosExtra->pii_id = $this->prm->pii_id;
            $form = new form($form);

            if ($verTrimestre['trimestre' . ($x < 4 ? ($x + 1) : $x) . ''] == 1 && $verTrimestre['est_trimestre' . ($x < 4 ? ($x + 1) : $x) . ''] == 'A' && $formActivo == true) {

                $formActivo = false;
                $avance = new stdClass();
                $avance->required = true;
                $avance->id = (($x == 4) ? 'alcanzado' : 'alcTrimestre') . ($x + 1);
                $avance->name = (($x == 4) ? 'alcanzado' : 'alcTrimestre');
                $avance->enDiv = true;
                $avance->value = $trimestreAvance[$x];
                $avance->formato = 'numero';
                $avance->class = 'trim';
                if ($estadoCapturado[$x] == 0) {
                    $avance->label = $this->estadoCap;
                    $avance->style = 'color:red;';
                }
                $avance = $this->ponInput($avance);
                $activado = 'warning';

                $trim = new stdClass();
                $trim->required = true;
                $trim->id = 'trimestre';
                $trim->enDiv = true;
                $trim->value = ($x + 1);
                $trim->formato = 'numero';
                $trim->type = 'hidden';
                $trimes = $this->ponInput($trim);

                $boton = new stdClass();
                $boton->id = 'guardar';
                $boton->value = 'Guardar';
                $boton->type = 'submit';
                $boton->color = 'verde';
                $boton->icon = 'fa-solid fa-floppy-disk me-1';

                $boton = $this->ponBoton($boton);
            } else {
                if ($x == 4) {
                    $avance = $meta->getAlcanzado();
                } else {
                    $avance = $trimestreAvance[$x];
                }

                $activado = 'info';
                $boton = '';
            }
            $estilo_th = '';
            switch ($x) {
                case 0:
                    $estilo_th = $color1;
                    break;
                case 1:
                    $estilo_th = $color2;
                    break;
                case 2:
                    $estilo_th = $color3;
                    break;
                case 3:
                    $estilo_th = $color4;
                    break;
            }

            $tituloTrimestre = ($x == 4) ? 'Anualizada' : $trimestreLabel[$x] . ' Trimestre ';
            $siguienteTrimestre = ($x < 4) ? ($x + 1) : $x;
            $claveCapturado = 'cap_trimestre' . $siguienteTrimestre;
            $estaCapturado = ($verTrimestre[$claveCapturado] != 0) ? '(Capturado)' : '';

            $trimestre = '
                <div class="panel panel-' . $activado . ' ">
                    <div class="panel-heading"> <h3 class="panel-title">' . $tituloTrimestre . $estaCapturado  . '</h3> </div>
                    <div class="panel-body">
                        <table class="table table-condensed">
                            <thead> <tr> <th>PROGRAMADA</th> <th>ACTUALIZADA</th> <th ' . $estilo_th . '>ALCANZADA</th> </tr> </thead>
                            <tbody> 
                                <tr>
                                    <td >' . ($x == 4 ? $meta->getOriginal() : $trimestreOriginal[$x]) . '</td>
                                    <td  id="actualizada' . ($x < 4 ? ($x + 1) : $x) . '">' . ($x == 4 ? $meta->getModificado() : $trimestreModificado[$x]) . '</td>
                                    <td  id="' . ($x == 4 ? 'total' : '') . '">' . $avance . '</td>
                                </tr> 
                            </tbody>
                        </table>
                        ' . $trimes . '
                        ' . $boton . '
                    </div>
                </div>
            ';
            $form->mixHtml($trimestre);
            $trimestres .= $form->ponForm();

            $trimestres .= ($x == 1 || $x == 3) ? '</div>' : '';
        }
        $ponsema = '
            <div class="panel panel-info col-md-6">
                <div class="panel-heading"><h3 class="panel-title"></h3></div>
                <div class="panel-body">
                    <table class="table table-condensed">
                        <thead></thead>
                        <tbody>
                            <tr><td><strong ' . $color1 . '>PORCENTAJE ALCANZADO DEL INDICADOR PRIMER TRIMESTRE: ' . $dat1['porcentaje1'] . ' %</strong></td></tr>
                            <tr><td><strong ' . $color2 . '>PORCENTAJE ALCANZADO DEL INDICADOR SEGUNDO TRIMESTRE: ' . $dat2['porcentaje2'] . ' %</strong></td></tr>
                            <tr><td><strong ' . $color3 . '>PORCENTAJE ALCANZADO DEL INDICADOR TERCER TRIMESTRE: ' . $dat3['porcentaje3'] . ' %</strong></td></tr>
                            <tr><td><strong ' . $color4 . '>PORCENTAJE ALCANZADO DEL INDICADOR CUARTO TRIMESTRE: ' . $dat4['porcentaje4'] . ' %</strong></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        ';

        $this->mixHtml('
            <div id="myModal" class="modal " tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl" style="width: 100% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Meta Actividad</h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row row-cols-md-2">
                                ' . $trimestres . '
                                ' . $ponsema . '
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").on("hidden.bs.modal", function (e) { $("#myModal").remove(); });
            $("#close").click(function(){ $("#myModal").hide(); });
            $("#cancelaPt").click(function(){ $("#myModal").remove(); });
            $("#myModal").modal().show();

            ' . (isset($this->prm->mem_id) && $this->prm->mem_id != '' ? '
                $("#meta_id").html("' . $this->prm->mem_id . '");
            ' : '') . '
        ');
    }

    private function mesAbierto()
    {

        $trimAbierto = new AccesoDatos();
        $trimAbierto->setSql("
            SELECT
                IF(tru_fechaApertura1<=NOW(), IF(NOW()<=fca_trimestre1, 1, 0), 0) trimestre1, est_trimestre1 ,
                IF(tru_fechaApertura2<=NOW(), IF(NOW()<=fca_trimestre2, 1, 0), 0) trimestre2, est_trimestre2 ,
                IF(tru_fechaApertura3<=NOW(), IF(NOW()<=fca_trimestre3, 1, 0), 0) trimestre3, est_trimestre3 ,
                IF(tru_fechaApertura4<=NOW(), IF(NOW()<=fca_trimestre4, IF(YEAR(NOW())=YEAR(fca_trimestre4),1,0), 0), 0 ) trimestre4, est_trimestre4,
                (total1*100)/total11 porcentaje1,(total2*100)/total22 porcentaje2,(total3*100)/total33 porcentaje3,(total4*100)/total44 porcentaje4,
                IF(((total1*100)/total11)=100,1,0) AS concluido1, IF(((total2*100)/total22)>=100,1,0) AS concluido2,
                IF(((total3*100)/total33)>=100,1,0) AS concluido3,IF(((total4*100)/total44)>=100,1,0) AS concluido4
            FROM(
                SELECT SUM(contador) total,
                SUM(IF(s.usr_fecha<='2026-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),contador,0)) total11,
                SUM(IF(s.usr_fecha<='2026-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),contador,0)) total22,
                SUM(IF(s.usr_fecha<='2026-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),contador,0)) total33,
                SUM(IF(s.usr_fecha<='2026-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),contador,0)) total44,
                SUM(IF(s.usr_fecha<='2026-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),cap_trimestre1,0)) total1,
                SUM(IF(s.usr_fecha<='2026-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),cap_trimestre2,0))total2,
                SUM(IF(s.usr_fecha<='2026-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),cap_trimestre3,0))total3,
                SUM(IF(s.usr_fecha<='2026-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),cap_trimestre4,0)) total4,
                est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                fca_trimestre1, fca_trimestre2, fca_trimestre3, fca_trimestre4,
                tru_fechaApertura1,tru_fechaApertura2,tru_fechaApertura3,tru_fechaApertura4
                FROM (
                    SELECT pii_id,1 contador, gpo_id, ur_id, prg_id, spr_id,
                        py_id, oa_id, m.usr_fecha,
                        epg_id, est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                        alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                        mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                        mac_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                        cap_trimestre1,cap_trimestre2, cap_trimestre3, cap_trimestre4,
                        fca_trimestre1, fca_trimestre2, fca_trimestre3, fca_trimestre4,
                        tru_fechaApertura1,tru_fechaApertura2,tru_fechaApertura3,tru_fechaApertura4,
                        vig_trimestre1,vig_trimestre2,vig_trimestre3,vig_trimestre4
                    FROM cat_MetaActividad m
                    INNER JOIN (
                        SELECT pii_id, epg_id, SUM(IF(tri_id=1,mact_alcanzado,NULL)) alc_trimestre1,
                            SUM(IF(tri_id=2,mact_alcanzado,NULL)) alc_trimestre2, SUM(IF(tri_id=3,mact_alcanzado,NULL)) alc_trimestre3,
                            SUM(IF(tri_id=4,mact_alcanzado,NULL)) alc_trimestre4,  SUM(IF(tri_id=1,mact_modificado,NULL)) mod_trimestre1,
                            SUM(IF(tri_id=2,mact_modificado,NULL)) mod_trimestre2, SUM(IF(tri_id=3,mact_modificado,NULL)) mod_trimestre3,
                            SUM(IF(tri_id=4,mact_modificado,NULL)) mod_trimestre4, SUM(IF(tri_id=1,mact_original,NULL)) ori_trimestre1,
                            SUM(IF(tri_id=2,mact_original,NULL)) ori_trimestre2, SUM(IF(tri_id=3,mact_original,NULL)) ori_trimestre3,
                            SUM(IF(tri_id=4,mact_original,NULL)) ori_trimestre4, SUM(IF(tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1,
                            SUM(IF(tri_id=2,mact_capturaAvance,NULL)) cap_trimestre2, SUM(IF(tri_id=3,mact_capturaAvance,NULL)) cap_trimestre3,
                            SUM(IF(tri_id=4,mact_capturaAvance,NULL)) cap_trimestre4
                        FROM cat_MetaActividadTrimestre
                        GROUP BY pii_id,epg_id
                    )mat USING (pii_id,epg_id)
                    INNER JOIN enc_Programatico USING(epg_id)
                    INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                            MAX(IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                            MAX(IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_trimestre1,
                            MAX(IF(tri_id=1,tru_fechaApertura,NULL)) tru_fechaApertura1,  MAX(IF(tri_id=2,tru_fechaApertura,NULL)) tru_fechaApertura2,
                            MAX(IF(tri_id=3,tru_fechaApertura,NULL)) tru_fechaApertura3,  MAX(IF(tri_id=4,tru_fechaApertura,NULL)) tru_fechaApertura4,                                
                            MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_trimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_trimestre3,
                            MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_trimestre4
                        FROM mir_TrimestreUr
                        WHERE nim_id=4
                        GROUP BY gpo_id, ur_id
                    )est USING (gpo_id,ur_id)
                    LEFT JOIN (
                        SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id, SUM(pre_vigente) AS vigente,
                        SUM(vig_ene+vig_feb+vig_mar) vig_trimestre1, SUM(vig_abr+vig_may+vig_jun) vig_trimestre2,
                        SUM(vig_jul+vig_ago+vig_sep) vig_trimestre3, SUM(vig_oct+vig_nov+vig_dic)vig_trimestre4
                        FROM presupuesto
                        INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        WHERE gpo_id= :gpo_id AND ur_id=:ur_id
                        GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
                    ) AS temp USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                    WHERE gpo_id= :gpo_id and ur_id= :ur_id AND
                    ((eoa_id=5 AND toa_id=2) OR (eoa_id=4 AND toa_id=1))
                    AND (mod_trimestre" . $this->trimestre . ' >0 || vig_trimestre' . $this->trimestre . '>0)
                ) AS s
            ) M;
        ');
        $params = [
            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id'  => $_SESSION[_S]['clasAdm-ur'],
        ];
        $trimAbierto->conectar();
        $trimAbierto->consultar($params);
        $trimAbierto->desconectar();

        return $trimAbi = $trimAbierto->extraer();
    }

    private function guardarAvance()
    {
        $meta = new MetaActividad($this->prm);
        $trimestre = $this->prm->trimestre;
        $alcTrimestre = $this->prm->alcTrimestre;
        switch ($trimestre) {
            case 1:
                $alcanzado = $alcTrimestre + $meta->getAlcTrimestre2() + $meta->getAlcTrimestre3() + $meta->getAlcTrimestre4();
                break;
            case 2:
                $alcanzado = $meta->getAlcTrimestre1() + $alcTrimestre + $meta->getAlcTrimestre3() + $meta->getAlcTrimestre4();
                break;
            case 3:
                $alcanzado = $meta->getAlcTrimestre1() + $meta->getAlcTrimestre2() + $alcTrimestre + $meta->getAlcTrimestre4();
                break;
            case 4:
                $alcanzado = $meta->getAlcTrimestre1() + $meta->getAlcTrimestre2() + $meta->getAlcTrimestre3() + $alcTrimestre;
                break;
        }

        $actualizaAlcanzado = new sp_ActualizaAlcanzadoTrimestre();
        $actualizaAlcanzado->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $actualizaAlcanzado->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $actualizaAlcanzado->setPii_id($this->prm->pii_id);
        $actualizaAlcanzado->setEpg_id($this->prm->epg_id);
        $actualizaAlcanzado->setTri_id($this->prm->trimestre);
        $actualizaAlcanzado->setMac_alcanzado($alcanzado);
        $actualizaAlcanzado->setMact_alcanzado($alcTrimestre);
        $actualizaAlcanzado->setBit_observacion('Módulo Avance de Gestión Trimestre:' . $this->prm->trimestre);
        $actualizaAlcanzado->setSis_id('POA');
        $actualizaAlcanzado->setEjercicio(APP_EJERCICIO);
        $actualizaAlcanzado->execute();
        $this->mixScript('notify("' . $actualizaAlcanzado->getMensaje() . '","success");');
        $this->muestraMetaIndicadorProductoIntermedio();

        //Actualizar valor en aux_HistoricoMetaActividadMir macm_modificadoT desde CAPTURA AVANCE
        $sqlAuxH = $sqlAuxHTrimSig = '';
        switch ($trimestre) {
            case 1:
                $sqlAuxH .='SET macm_modificadoT1 = ' . $alcTrimestre;
                $sqlAuxHTrimSig .= 'SET macm_modificadoT1 = ' . $alcTrimestre;
                break;
            case 2:
                $sqlAuxH .='SET macm_modificadoT2 = '. $alcTrimestre;
                $sqlAuxHTrimSig .= 'SET macm_modificadoT2 = '. $alcTrimestre;
                break;
            case 3:
                $sqlAuxH .= 'SET macm_modificadoT3 = ' . $alcTrimestre;
                $sqlAuxHTrimSig .= 'SET macm_modificadoT3 = ' . $alcTrimestre;
                break;
            case 4:
                $sqlAuxH .= 'SET macm_modificadoT4 = ' . $alcTrimestre;
                break;
        }

        $sql ='
            SELECT
                macm_id 
            FROM ' . BD_CTRL .' .cat_MetaActividad
            WHERE pii_id = ? AND epg_id = ?;
        ';
    
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $parametro = [$this->prm->pii_id, $this->prm->epg_id];
        $conexion->consultar($parametro);
        $conexion->desconectar();
        $datos = $conexion->extraer();

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $this->conectar();
        $bit_observacion = 'ACTUALIZACIÓN DE macm_modificadoT' . $trimestre . 'DESDE CAPTURAAVANCE';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $this->setSql('SET @usr_ip =INET_ATON("' . $ip . '"), @usr_id="' . $bit_usr_id . '" , @bit_observacion ="' . $bit_observacion . '"');
        $this->consultar();
        $this->setSql('
            UPDATE aux_HistoricoMetaActividadMir
            ' . $sqlAuxH . '
            WHERE pii_id = ? AND macm_id = ? AND tri_id = ?;
        ');
        $params = [$this->prm->pii_id, $datos['macm_id'], $this->prm->trimestre];
        $this->consultar($params);

        $this->setSql('
            UPDATE aux_HistoricoMetaActividadMir
            ' . $sqlAuxHTrimSig . '
            WHERE pii_id = ? AND macm_id = ? AND tri_id = ?;
        ');
        $tri_id = $this->prm->trimestre + 1;
        $paramsSig = [$this->prm->pii_id, $datos['macm_id'], $tri_id];
        $this->consultar($paramsSig);

        $this->desconectar();
    }

    private function cerrarCaptura()
    {
        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $this->conectar();
        $bit_observacion = 'CIERRE TRIMESTRE ' . $this->prm->trimestre . ' MODULO AVANCE DE GESTION';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $etr_id = 'C';
        $this->setSql(' SET @usr_ip =INET_ATON("' . $ip . '"), @usr_id="' . $bit_usr_id . '" , @bit_observacion ="' . $bit_observacion . '", @etr_id1="' . $etr_id . '";');
        $this->consultar();
        $this->setSql("SET @mip = INET_ATON('" . $ip . "');");
        $this->consultar();
        $this->setSql("
            UPDATE mir_TrimestreUr
            SET etr_id1='C'
            WHERE gpo_id=? AND ur_id=?
            AND tri_id=?
            AND nim_id=4;
        ");
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->trimestre,
        ];
        $this->consultar($params);
        $this->desconectar();
        $this->mixScript('
            notify("Captura concluida");
            comunica("' . $this->pantalla . '",{
                metodo:"muestraActividades"
            });
        ');
    }

    private function metaAbierta()
    {
        $metaAbierta = new AccesoDatos();
        $metaAbierta->conectar();
        $metaAbierta->setSql('
            SELECT gpo_id,ur_id,
                IF(tru_fechaApertura1 <=NOW(), IF(NOW()<=fca_trimestre1, 1, 0), 0) trimestre1, est_trimestre1,
                IF(tru_fechaApertura2 <=NOW(), IF(NOW()<=fca_trimestre2, 1, 0), 0) trimestre2, est_trimestre2 ,
                IF(tru_fechaApertura3 <=NOW(), IF(NOW()<=fca_trimestre3, 1, 0), 0) trimestre3, est_trimestre3 ,
                IF(tru_fechaApertura4 <=NOW(), IF(NOW()<=fca_trimestre4, IF(YEAR(NOW())=YEAR(fca_trimestre4),1,0), 0), 0 ) trimestre4, est_trimestre4 ,
                mod_trimestre1,mod_trimestre2,mod_trimestre3,mod_trimestre4,
                cap_trimestre1,cap_trimestre2,cap_trimestre3,cap_trimestre4
            FROM(
                SELECT gpo_id, ur_id,
                    MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,  MAX( IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2,
                    MAX( IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,  MAX( IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4,
                    SUM(IF(tri_id=1,mact_modificado,NULL)) mod_trimestre1,	SUM(IF(tri_id=2,mact_modificado,NULL)) mod_trimestre2,
                    SUM(IF(tri_id=3,mact_modificado,NULL)) mod_trimestre3, SUM(IF(tri_id=4,mact_modificado,NULL)) mod_trimestre4,
                    MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_trimestre1, MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_trimestre2,
                    MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_trimestre3, MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_trimestre4,
                    MAX(IF(tri_id=1,tru_fechaApertura,NULL)) tru_fechaApertura1,  MAX(IF(tri_id=2,tru_fechaApertura,NULL)) tru_fechaApertura2,
                    MAX(IF(tri_id=3,tru_fechaApertura,NULL)) tru_fechaApertura3,  MAX(IF(tri_id=4,tru_fechaApertura,NULL)) tru_fechaApertura4,
                    SUM(IF(tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1, SUM(IF(tri_id=2,mact_capturaAvance,NULL)) cap_trimestre2,
                    SUM(IF(tri_id=3,mact_capturaAvance,NULL)) cap_trimestre3, SUM(IF(tri_id=4,mact_capturaAvance,NULL)) cap_trimestre4
                FROM cat_MetaActividad
                INNER JOIN enc_Programatico USING(epg_id)
                INNER JOIN cat_MetaActividadTrimestre USING(epg_id,pii_id)
                INNER JOIN mir_TrimestreUr USING (gpo_id,ur_id,tri_id)
                WHERE pii_id=? AND nim_id=4
                AND epg_id=? 
                GROUP BY gpo_id,ur_id,pii_id
            )AS r;
        ');

        $metaAbierta->consultar([$this->prm->pii_id,$this->prm->epg_id]);
        $metaAbierta->desconectar();
        return $m = $metaAbierta->extraer();
    }
}

//-------------------------------//
if (isset($_SESSION[_S]['prm']['A']['10']['1'])) {
    new ControladorCapturaAvance();
} else {
    WebResponse::sinPermisos();
}

//---------------------------------//
