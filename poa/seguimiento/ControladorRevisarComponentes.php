<?php

session_start();
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorRevisarComponentes extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        if ($_SESSION[_S]['nvj_id'] > 0) {
            switch ($this->metodo) {
                case 'verMetas':
                    $this->verMetas();
                    break;
                case 'verComponentes':
                    $this->verComponentes();
                    break;
                case 'seleccionaTrimestre':
                    $this->seleccionaTrimestre();
                    break;
                case 'Historial':
                    $this->Historial();
                    break;
                default:
                    $this->seleccionaTrimestre();
                    break;
            }
        } else {
            $this->sinPermisos();
        }

        $this->returnJson();
    }

    private function seleccionaTrimestre()
    {

        $this->agregaTools('');
        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $unidadR->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $unidadR->select();

        $tri = '<div><h4><b>' . $unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre() . '</b></h4></div>';
        $tri .= '<div style="width:100%; padding-left:30%; padding-bottom:50px;">';

        $tri .= '
            <div class="boton_opcion trim " trimestre=2>
                <div class="boton_opcion_titulo">
                    <strong>
                            <span style="font-size: 20px;" class="" aria-hidden="true"></span>Ene-Jun</div>
                            <div><div>
                    </strong>
                </div>
            </div>
            </div>
            <div style="width:10px; display:table-cell"></div>
        ';

        $tri .= '
            <div class="boton_opcion trim " trimestre=4>
                <div class="boton_opcion_titulo">
                    <strong>
                        <span style="font-size: 20px;" class="" aria-hidden="true"></span>Jul-Dic</div>
                        <div><div>
                    </strong>
                </div>
            </div>
            </div>
            <div style="width:10px; display:table-cell"></div>
        ';

        $this->mixHtml($tri . '</div><div class="row" id="componente"></div>');
        $this->mixScript('
            $(".trim").click(function(){
                comunica("' . $this->pantalla . '",{
                        metodo:"verComponentes",
                        trimestre:$(this).attr("trimestre")
                });
            });
        ');
    }

    private function verComponentes()
    {

        $this->setContenedor('componente');

        $and = '';
        switch ($this->prm->trimestre) {
            case 2:
                $and = ' and ipf_trimestre in (1,2)';
                break;
            case 4:
                //mostraria las metas de todos los trimestres
                break;
        }

        $this->conectar();
        $this->setSql("
            SELECT gpo_id, ur_id, prg_id, spr_id, prg_nombre,spr_nombre, SUM(pre_aprobado) AS aprobado,
                SUM(pre_vigente) AS vigente, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                SUM(pre_ejercido) AS ejercido, metas,
                if(mem_capturado2 = metas,'CAPTURADO', if(ISNULL(porcentaje2),0,format(porcentaje2,2))) porcentaje2,
                if(mem_capturado4 = metas,'CAPTURADO', if(ISNULL(porcentaje4),0,format(porcentaje4,2))) porcentaje4,
                pdf_id, eri_id,round(((SUM(pre_devengado)/SUM(pre_vigente))*100),2) porcentajePresupuesto,porcentajeMeta,hms_mensaje,ses_id,
                ind_limInfVer,ind_limSupVer,ind_limInfAmar,ind_limSupAmar,ind_limInfRojo,ind_limSupRojo, alc_trimestre
            FROM cat_Programa
            INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id)
            INNER JOIN (
                SELECT COUNT(epfi_id) AS metas, gpo_id,ur_id,prgs_id AS prg_id,sprs_id AS spr_id,pdf_id,epfi_id AS eri_id,
                    SUM(cap_semestre1) mem_capturado2,
                    SUM(cap_semestre2) mem_capturado4,
                    ((SUM(cap_semestre1)*100)/COUNT(epfi_id)) porcentaje2,
                    ((SUM(cap_semestre2)*100)/COUNT(epfi_id)) porcentaje4, hms_mensaje,
                    ROUND(((alc_trimestre" . $this->prm->trimestre . '/mod_trimestre' . $this->prm->trimestre . ')*100),2) porcentajeMeta,ses_id,alc_trimestre' . $this->prm->trimestre . ' AS alc_trimestre,
                    ind_limInfVer,100 AS ind_limSupVer,ind_limInfAmar,ind_limSupAmar,0 AS ind_limInfRojo,ind_limSupRojo
                FROM  (
                    SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,
                    MAX(IF (tri_id=2,mcot_alcanzado,NULL)) alc_trimestre2,
                    MAX(IF (tri_id=4,mcot_alcanzado,NULL)) alc_trimestre4,
                    MAX(IF (tri_id=2,mcot_modificado,NULL)) mod_trimestre2,
                    MAX(IF (tri_id=4,mcot_modificado,NULL)) mod_trimestre4,
                    MAX(IF (tri_id=2,mcot_capturaAvance,NULL)) cap_semestre1,
                    MAX(IF (tri_id=4,mcot_capturaAvance,NULL)) cap_semestre2
                    FROM cat_MetaComponente
                    INNER JOIN cat_MetaComponenteTrimestre USING (epfi_id,gpo_id, ur_id,prgs_id, sprs_id)
                    GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id
                )m
                INNER JOIN enc_ProductoFinalIndicador USING(epfi_id)
                INNER JOIN cat_Indicador USING(nim_id,ind_id)
                LEFT  JOIN (
                    SELECT MAX(msj_id) msj_id, nim_id, epf_id AS epfi_id,MAX(hms_mensaje) hms_mensaje,
                        gpo_id, ur_id
                    FROM cat_MensajeSeguimiento
                    INNER JOIN cat_HistorialMensajeSeguimiento USING(msj_id)
                    WHERE nim_id=3
                    AND tim_id=3 AND hms_trimestre=:tri_id
                    GROUP BY gpo_id, ur_id,epf_id
                )msj USING (epfi_id,nim_id,gpo_id, ur_id)
                WHERE gpo_id=:gpo_id AND ur_id=:ur_id  AND nim_id=3
                GROUP BY gpo_id, ur_id, prg_id, sprs_id,pdf_id,epfi_id
            )meta USING (gpo_id, ur_id,prg_id,pdf_id,spr_id)
            LEFT JOIN (
                SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id, SUM(pre_vigente) AS pre_vigente,
                SUM(pre_aprobado) AS pre_aprobado, SUM(pre_comprometido) AS pre_comprometido, SUM(pre_devengado) AS pre_devengado,
                SUM(pre_ejercido) AS pre_ejercido
                FROM presupuesto
                INNER JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido  USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
            ) pre USING (gpo_id,ur_id,prg_id,spr_id)
            WHERE gpo_id=:gpo_id AND ur_id=:ur_id
            GROUP BY gpo_id,ur_id,prg_id,spr_id,pdf_id,eri_id ;
        ');

        $params = [
            ':tri_id' => $this->prm->trimestre,
            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id'  => $_SESSION[_S]['clasAdm-ur'],
        ];

        $this->consultar($params);
        $this->desconectar();

        $htm = '<table  class="table table table-bordered table-hover">';
        if ($this->getNumeroRegistros() > 0) {
            $htm .= '
                <tr>
                    <th class="warning" style="text-align:center" colspan="1"></th>
                    <th class="warning" style="text-align:center" colspan="1"></th>
                    <th class="warning" colspan="14"></th>
                    <th class="warning" align="center" colspan="2"></th>
                    <th class="warning" align="center" colspan="2"></th>
                    <th class="warning" align="center" colspan="2"></th>
                    <th class="warning" align="center" colspan="2"></th>
                    <th class="warning" align="center" colspan="2"></th>
                    <th class="warning" align="center" colspan="2">SEMAFORIZACIÓN</th>
                    <th class="warning" align="center" colspan="1"></th>
                    <th class="warning" align="center" colspan="1"></th>
                    <th class="warning" align="center" colspan="1"></th>
                </tr>
            ';
        }

        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';
        $cambioClase = true;
        $color = '';
        $color1 = '';
        while ($row = $this->extraer()) {
            if ($row['porcentajePresupuesto'] >= 76 && $row['porcentajePresupuesto'] <= 100.00) {
                $color = 'btn-success';
            }
            if ($row['porcentajePresupuesto'] >= 41 && $row['porcentajePresupuesto'] <= 75) {
                $color = 'btn-warning';
            }
            if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] <= 40) {
                $color = 'btn-danger';
            }

            switch ($row['ses_id']) {
                case 1: //1-ASCENDENTE
                    if ($row['porcentajeMeta'] >= $row['ind_limInfVer']) {
                        $color1 = 'btn-success';
                    } elseif (($row['porcentajeMeta'] < $row['ind_limSupAmar']) && ($row['porcentajeMeta'] >= $row['ind_limInfAmar'])) {
                        $color1 = 'btn-warning';
                    } elseif ($row['porcentajeMeta'] <= $row['ind_limSupRojo']) {
                        $color1 = 'btn-danger';
                    }
                    break;
                case 2: //2-DESCENDENTE
                    if ($row['porcentajeMeta'] <= $row['ind_limInfVer']) {
                        $color1 = 'btn-success';
                    } elseif (($row['porcentajeMeta'] > $row['ind_limSupAmar']) && ($row['porcentajeMeta'] <= $row['ind_limInfAmar'])) {
                        $color1 = 'btn-warning';
                    } elseif ($row['porcentajeMeta'] >= $row['ind_limSupRojo']) {
                        $color1 = 'btn-danger';
                    }
                    break;
            }

            if ($row['prg_id'] != $prg) {
                $htm .= '
                    <tr>
                        <th class="warning" style="text-align:center" colspan="1">PR:</th>
                        <th class="warning" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                        <th style="text-align:left" colspan="14">' . $row['prg_nombre'] . '</th>
                        <th class="warning" align="center" colspan="2">APROBADO</th>
                        <th class="warning" align="center" colspan="2">MODIFICADO</th>
                        <th class="warning" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="warning" align="center" colspan="2">DEVENGADO</th>
                        <th class="warning" align="center" colspan="2">EJERCIDO</th>
                        <th class="warning" align="center" colspan="1">PRESUPUESTO (DEVENGADO/MODIFICADO)</th>
                        <th class="warning" align="center" colspan="1">ALCANCE DE METAS</th>
                        <th class="warning" align="center" colspan="1">VER META</th>
                        <th class="warning" align="center" colspan="1">ESTATUS</th>
                        <th class="warning" align="center" colspan="1">HISTORIAL</th>
                    </tr>
                ';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $clase = ($cambioClase) ? 'class="warning"' : '';
                $htm .= '
                    <tr class="warning" >
                        <th class="warning" style="text-align:center" colspan="1">SPR:</th>
                        <th class="warning" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                        <td align="left" colspan="14">' . $row['spr_nombre'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['aprobado'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['vigente'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['comprometido'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['devengado'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['ejercido'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1" >
                            <button type="button" class=" btn btn-small ' . $color . '" title="Porcentaje de PRESUPUESTO ' . $row['porcentajePresupuesto'] . ' %"></button>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >
                            <button type="button" class=" btn btn-small ' . $color1 . '" title="Porcentaje de META ' . ($row['alc_trimestre'] == null ? 0 : $row['alc_trimestre']) . '(' . ($row['porcentajeMeta'] == null ? 0 : $row['porcentajeMeta']) . ' %)" ></button>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >
                            <button type="button" id="verComponente" icon="fa fa-eye" title="Ver detalle de la meta" class="verComponente btn btn-outline-secondary" eri_id=' . $row['eri_id'] . '>
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >' . ($row['hms_mensaje'] != '' ? 'CON OBSERVACIÓN' : 'SIN OBSERVACIÓN') . '</td>
                        <td ' . $clase . ' align="center" colspan="1" >
                        ' . ($row['hms_mensaje'] != '' ? '
                            <button type="button" id="verHistorial" icon="fa-list-alt" title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary" eri_id=' . $row['eri_id'] . '>
                                <i class="fa fa-list-alt"></i>
                            </button>' : '') . '
                        </td>
                    </tr>
                    <tr class="warning">
                        <th></th>
                            <th colspan = "30" class ="divComponente" id="metas' . $row['eri_id'] . '"  >
                            </th>
                    </tr>
                ';

                $cambioClase = ($cambioClase) ? false : true;
            }

            $ur = $row['gpo_id'] . $row['ur_id'];
            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
        }

        $htm .= '</table><div id="historial" style="z-index: 3;">';

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verComponente").click(function(){
                $(".divComponente").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    eri_id:$(this).attr("eri_id"),
                    trimestre:' . $this->prm->trimestre . '
                });
            });

            $(".verHistorial").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    eri_id:$(this).attr("eri_id"),
                    gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                    ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                    trimestre:' . $this->prm->trimestre . '
                });
            });
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->eri_id . '');

        $btnEnviar = new stdClass();
        $btnEnviar->id = 'btnEnviarC';
        $btnEnviar->type = 'button';
        $btnEnviar->title = 'Enviar observación';
        $btnEnviar->class = 'me-1 ';
        $btnEnviar->color = 'gris';
        $btnEnviar->icon = 'solid fa-paper-plane';
        $btnEnviar->attr = new stdClass();
        $btnEnviar->attr->eri_id = $this->prm->eri_id;

        $descripcion = new stdClass();
        $descripcion->id = 'descripcionC';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 500;
        $descripcion->width = '300px';
        $descripcion->height = '100px';

        $this->conectar();

        $this->setSql('
            SELECT gpo_id, ur_id, prgs_id, sprs_id,ind_id,epfi_id AS eri_id, ind_nombreMIR, ind_definicionMIR,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, u.umi_id as umi_id,
                umi_nombre as umi_nombre, umv_id as umi_mir,umv_nombre as umiMir_nombre,
                if(tiv_id=1,2,tiv_id) tiv_id, if(tiv_id=1,"ACUMULADO",tiv_nombre) tiv_nombre,ses_id,ses_nombre,
                frm_id,frm_nombre,mco_descripcion AS mem_descripcion,
                mco_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                mco_modificado AS mem_modificado, mco_alcanzado AS  mem_alcanzado, ((mco_alcanzado/mco_modificado) * 100) as porcentajeMeta
            FROM enc_ProductoFinalIndicador
            INNER JOIN cat_MetaComponente USING (epfi_id)
            INNER JOIN (
                SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,
                MAX(IF (tri_id=1,mcot_original,NULL)) ori_trimestre1,
                MAX(IF (tri_id=2,mcot_original,NULL)) ori_trimestre2,
                MAX(IF (tri_id=3,mcot_original,NULL)) ori_trimestre3,
                MAX(IF (tri_id=4,mcot_original,NULL)) ori_trimestre4,
                MAX(IF (tri_id=1,mcot_alcanzado,NULL)) alc_trimestre1,
                MAX(IF (tri_id=2,mcot_alcanzado,NULL)) alc_trimestre2,
                MAX(IF (tri_id=3,mcot_alcanzado,NULL)) alc_trimestre3,
                MAX(IF (tri_id=4,mcot_alcanzado,NULL)) alc_trimestre4,
                MAX(IF (tri_id=1,mcot_modificado,NULL)) mod_trimestre1,
                MAX(IF (tri_id=2,mcot_modificado,NULL)) mod_trimestre2,
                MAX(IF (tri_id=3,mcot_modificado,NULL)) mod_trimestre3,
                MAX(IF (tri_id=4,mcot_modificado,NULL)) mod_trimestre4,
                MAX(IF (tri_id=2,mcot_capturaAvance,NULL)) cap_semestre1,
                MAX(IF (tri_id=4,mcot_capturaAvance,NULL)) cap_semestre2
                FROM cat_MetaComponenteTrimestre
                GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id
            ) m USING ( gpo_id, ur_id,prgs_id, sprs_id, epfi_id)
            INNER JOIN (
                SELECT upm_id, vpm_id, gpo_id, ur_id, prgs_id, emi_id
                FROM vin_UrProgramaMir
            )vupm USING(gpo_id,ur_id,prgs_id)
            INNER JOIN (
                SELECT gpo_id, ur_id, prg_id AS prgs_id, spr_id AS sprs_id, spr_nombre, pdf_id
                FROM cat_Subprograma
            )s USING(gpo_id,ur_id,prgs_id,sprs_id,pdf_id)
            INNER JOIN cat_Indicador i USING(nim_id,ind_id)
            INNER JOIN cat_TipoIndicador USING(tpi_id)
            INNER JOIN cat_Dimension USING(dim_id)
            INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
            INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
            INNER JOIN cat_TipoValor USING(tiv_id)
            INNER JOIN cat_SentidoEsperado USING(ses_id)
            INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            WHERE gpo_id=? AND ur_id=? AND emi_id=9
            AND epfi_id=?
            GROUP BY gpo_id,ur_id,prgs_id,sprs_id
            ORDER BY epfi_id;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->eri_id,
        ];
        $this->consultar($params);

        while ($ind = $this->extraer()) {
            $this->mixHtml('
                <table class="table table-hover table-bordered" >
                    <tr class="warning"><th colspan=19>INDICADOR ' . ($ind['ind_id']) . '</th></tr>
                    <tr><td>Nombre:</td><td colspan=7>' . ($ind['ind_nombreMIR']) . '</td></tr>
                    <tr><td>Definicion:</td><td colspan=7>' . ($ind['ind_definicionMIR']) . '</td></tr>
                    <tr><td>Método de Calculo:</td><td colspan=7>' . ($ind['ind_formulaCalculo']) . '</td></tr>
                    <tr>
                        <th>Tipo</th>
                        <th>Dimension</th>
                        <th>Frecuencia de medicion</th>
                        <th>Unidad de Medida</th>
                        <th>Tipo de Valor</th>
                        <th>Sentido Esperado</th>
                    </tr><tr>
                        <td>' . ($ind['tpi_nombre']) . '</td>
                        <td>' . ($ind['dim_nombre']) . '</td>
                        <td>' . ($ind['frm_nombre']) . '</td>
                        <td>' . ($ind['umiMir_nombre']) . '</td>
                        <td>' . ($ind['tiv_nombre']) . '</td>
                        <td>' . ($ind['ses_nombre']) . '</td>
                    </tr>
            ');
            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql('
                SELECT gpo_id,ur_id,epfi_id,mco_descripcion AS mem_descripcion,mco_lb AS mem_anio,
                    mco_lbValor AS mem_valor,mco_original AS mem_valorDeseado1,
                    ((alc_trimestre1*100)/mco_modificado) ava1, ((alc_trimestre2*100)/mco_modificado) ava2,((alc_trimestre3*100)/mco_modificado) ava3,
                    ((alc_trimestre4*100)/mco_modificado) ava4,mco_alcanzado,
                    mod_trimestre1 AS mem_modTrimestre1,mod_trimestre2,mod_trimestre3 AS mem_modTrimestre3,mod_trimestre4,
                    (mod_trimestre1+mod_trimestre2+mod_trimestre3+mod_trimestre4) mem_modificado,
                    alc_trimestre1 AS mem_alcTrimestre1,alc_trimestre2 ,alc_trimestre3 AS mem_alcTrimestre3,alc_trimestre4,
                    (ori_trimestre1+ori_trimestre2+ori_trimestre3+ori_trimestre4) mem_original,
                    ori_trimestre1 AS mem_trimestre1,ori_trimestre2 AS mem_trimestre2,ori_trimestre3 AS mem_trimestre3,ori_trimestre4 AS mem_trimestre4,
                    ROUND(((mco_alcanzado/mco_modificado)*100),2) AS porcentajeMeta, ROUND(((alc_trimestre2/mod_trimestre2)*100),2) AS porcentajeAlc1,
                    ROUND(((alc_trimestre4/mod_trimestre4)*100),2) AS porcentajeAlc2
                FROM enc_ProductoFinalIndicador
                INNER JOIN cat_MetaComponente USING (epfi_id)
                INNER JOIN (
                        SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,
                        MAX(IF (tri_id=1,mcot_original,NULL)) ori_trimestre1,
                        MAX(IF (tri_id=2,mcot_original,NULL)) ori_trimestre2,
                        MAX(IF (tri_id=3,mcot_original,NULL)) ori_trimestre3,
                        MAX(IF (tri_id=4,mcot_original,NULL)) ori_trimestre4,
                        MAX(IF (tri_id=1,mcot_alcanzado,NULL)) alc_trimestre1,
                        MAX(IF (tri_id=2,mcot_alcanzado,NULL)) alc_trimestre2,
                        MAX(IF (tri_id=3,mcot_alcanzado,NULL)) alc_trimestre3,
                        MAX(IF (tri_id=4,mcot_alcanzado,NULL)) alc_trimestre4,
                        MAX(IF (tri_id=1,mcot_modificado,NULL)) mod_trimestre1,
                        MAX(IF (tri_id=2,mcot_modificado,NULL)) mod_trimestre2,
                        MAX(IF (tri_id=3,mcot_modificado,NULL)) mod_trimestre3,
                        MAX(IF (tri_id=4,mcot_modificado,NULL)) mod_trimestre4,
                        MAX(IF (tri_id=2,mcot_capturaAvance,NULL)) cap_semestre1,
                        MAX(IF (tri_id=4,mcot_capturaAvance,NULL)) cap_semestre2
                        FROM cat_MetaComponenteTrimestre
                        GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id
                ) m USING ( gpo_id, ur_id,prgs_id, sprs_id, epfi_id)
                INNER JOIN (
                    SELECT upm_id, vpm_id, gpo_id, ur_id, prgs_id, emi_id
                    FROM vin_UrProgramaMir
                )vupm USING(gpo_id,ur_id,prgs_id)
                WHERE ind_id=? and epfi_id= ? and gpo_id= ? AND ur_id= ?
                GROUP BY gpo_id,ur_id,prgs_id,sprs_id, epfi_id;
            ');
            $params = [
                $ind['ind_id'],
                $ind['eri_id'],
                $_SESSION[_S]['clasAdm-gpo'],
                $_SESSION[_S]['clasAdm-ur'],
            ];
            $con->consultar($params);

            while ($meta = $con->extraer()) {
                $btnEnviar->attr->mem_id = $meta['epfi_id'];

                $this->mixHtml('
                    <tr class="warning">
                        <th></th><th colspan = "12">
                            <table class="table table-hover table-bordered">
                                <tr>
                                    <th colspan ="12">META ' . $meta['epfi_id'] . '</th>
                                </tr>
                                <tr>
                                    <th colspan ="12" align="right">PORCENTAJE ACUMULADO <button type="button" class=" btn btn-small " title="">' . $meta['porcentajeMeta'] . ' %</button></th>
                                </tr>
                                <tr>
                                    <th>Descripcion:</th>
                                    <th colspan ="12">' . ($meta['mem_descripcion']) . '</th>
                                </tr><tr>
                                    <td>Unidad de Medida:</td>
                                    <td colspan ="12">' . $ind['umi_nombre'] . '</td>
                                </tr><tr>
                                        <th colspan="4" align=center>AVANCE DE METAS</th>
                                </tr><tr>
                                    <th>SEMESTRE</th>
                                    <th>PROGRAMADA</th>
                                    <th>ACTUALIZADA</th>
                                    <th>ALCANZADA</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th>Primero</th>
                                    <td>' . ($meta['mem_trimestre2']) . '</td>
                                    <td>' . ($meta['mod_trimestre2']) . '</td>
                                    <td>' . ($meta['alc_trimestre2']) . '(' . ($meta['porcentajeAlc1']) . '%)</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['ava2'] . '%</button></td>
                                </tr>
                                <tr>
                                    <th>Segundo</th>
                                    <td>' . ($meta['mem_trimestre4']) . '</td>
                                    <td>' . ($meta['mod_trimestre4']) . '</td>
                                    <td>' . ($meta['alc_trimestre4']) . '(' . ($meta['porcentajeAlc2']) . '%)</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['ava4'] . '%</button></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Acumulado</th>
                                    <td>' . ($meta['mem_original']) . '</td>
                                    <td>' . ($meta['mem_modificado']) . '</td>
                                    <td>' . ($meta['mco_alcanzado']) . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['porcentajeMeta'] . '%</button></td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                ');
            }
            $con->desconectar();
            $this->mixHtml('</table>');
        }
    }

    private function Historial()
    {

        $this->setContenedor('divContenedorGeneralModal');

        $this->conectar();
        $this->setSql('
            SELECT hms_mensaje, CONCAT(usr_nombre, usr_paterno,usr_materno) usr_nombre , DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha, TIME(hm.usr_fecha) hora
            FROM cat_MensajeSeguimiento m
            INNER JOIN cat_HistorialMensajeSeguimiento hm USING (msj_id)
            INNER JOIN cat_Usuario u ON hm.usr_id = u.usr_id
            INNER JOIN ctrlUsuario.cat_Usuario us ON u.usr_id=us.usr_id
            WHERE epf_id=?
            AND tim_id=3 AND hms_trimestre=?
            AND m.gpo_id=? AND m.ur_id=?;
        ');
        $params = [
            $this->prm->eri_id,
            $this->prm->trimestre,
            $this->prm->gpo_id,
            $this->prm->ur_id,
        ];
        $this->consultar($params);
        $this->desconectar();

        $html = '
            <table class="table table-hover table-bordered" >
                <tr class="warning">
                    <th align="center">MENSAJE</th>
                    <th>ESTADO</th>
                    <th>USUARIO</th>
                    <th>FECHA</th>
                </tr><tr> ';

        while ($row = $this->extraer()) {
            $html .= '
                <td>' . $row['hms_mensaje'] . '</td>
                <td>ENVIADO</td>
                <td>' . $row['usr_nombre'] . '</td>
                <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                </tr>
            ';
        }
        $html .= '<table>';

        $this->mixHtml('
            <div id="myHistorial" class="modal ">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Historial</h4>
                            <button type="button" class="btn-close cerrar" data-dismiss="modal" aria-hidden="true"></button>
                            </div>
                                <div class="modal-body">
                                    <div class="row">' . $html . '</div>
                                    <div class="modal-footer">
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div>
                </div>
            </div>
        ');
        $this->mixScript('
            $("#myHistorial").modal();
            $("#myHistorial").on("hidden.bs.modal", function (e) {
                $("#myHistorial").remove();
            });
            $(".cerrar").click(function(){ $("#myHistorial").hide(); });
            $("#myHistorial").modal().show();
        ');
    }
}

//fin clase
if (isset($_SESSION[_S]['prm']['A']['11']['6'])) {
    new ControladorRevisarComponentes();
} else {
    WebResponse::sinPermisos();
}
