<?php

session_start();
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/MensajeSeguimiento.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/HistorialMensajeSeguimiento.php';

class ControladorObservarComponente extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        if ($_SESSION[_S]['nvj_id'] > 1) {
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
                case 'Observacion':
                    $this->Observacion();
                    break;
                case 'Historial':
                    $this->Historial();
                    break;
                default:
                    $this->main();
                    break;
            }
        } else {
            $this->sinPermisos();
        }

        $this->returnJson();
    }

    private function main()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-1 ';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'fa-solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
                $("nav").menusinpres("clickMenu");
        ';
        $this->agregaTools($this->ponboton($btnregresarindex));

        $tabla = new stdClass();
        $tabla->id = 'tbl-urs';
        $tabla->numFilas = 200;
        $tabla->titulo = 'Listado de UR\'s';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMetas';
        $tabla->cabeceraFija = true;

        $tabla->desplegable = new stdClass();
        $tabla->desplegable->metodo = 'seleccionaTrimestre';
        $tabla->desplegable->titulo = 'Metas';
        $tabla->desplegable->encabezado = 'Metas';
        $tabla->desplegable->enFila = false;
        $tabla->desplegable->prm = ['gpo_id', 'ur_id'];

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Clave';
        $th->width = '5px';
        $tr = [];
        $th = new stdClass();
        $th->texto = 'Clave';
        $th->rowspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Unidad Responsable';
        $th->rowspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '1er Semestre';
        $th->colspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '2do Semestre';
        $th->colspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Total Alcanzado';
        $th->rowspan = 2;
        array_push($tr, $th);
        $tabla->encabezado = [$tr];
        $tr = [];
        $th = new stdClass();
        $th->texto = 'Capturado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Metas Alcanzadas';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Capturado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Metas Alcanzadas';
        array_push($tr, $th);
        array_push($tabla->encabezado, $tr);

        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje2';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ava2';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje4';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ava4';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ava';
        array_push($tabla->cuerpo, $td);
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaUrs();
    }

    private function llenaTablaUrs()
    {

        $this->setSql("
            SELECT gpo_id,ur_id,
                CASE WHEN '2026-04-19 15:00:00'<=NOW() AND NOW()<=fca_mirTrimestre1 THEN 1
                WHEN '2026-07-01 08:00:00'<=NOW() AND NOW()<=fca_mirTrimestre2 THEN 2
                WHEN '2026-10-01 08:00:00'<=NOW() AND NOW()<=fca_mirTrimestre3 THEN 3
                WHEN '2027-01-01 08:00:00'<=NOW() AND NOW()<=fca_mirTrimestre4 THEN 4
                ELSE 0 END AS capturandoTri,
                FORMAT(((total2*100)/total),2) porcentaje2,
                FORMAT(((total4*100)/total),2) porcentaje4,
                FORMAT(IF(mem_modificado=0,0,((mem_alcTrimestre1*100)/mem_modificado)),2) ava1,
                FORMAT(IF(mem_modificado=0,0,((mem_alcTrimestre2*100)/mem_modificado)),2) ava2,
                FORMAT(IF(mem_modificado=0,0,((mem_alcTrimestre3*100)/mem_modificado)),2) ava3,
                FORMAT(IF(mem_modificado=0,0,((mem_alcTrimestre4*100)/mem_modificado)),2) ava4,
                FORMAT(IF(mem_modificado=0,0,((mem_alcanzado*100)/mem_modificado)),2) ava,ur_clave,ur_nombre,
                CASE
                WHEN ((total2*100)/total) AND mem_estadoTrimestre2 = 0  THEN 2
                WHEN ((total4*100)/total) AND mem_estadoTrimestre4 = 0  THEN 4
                ELSE 0 END AS capturandoTri2
            FROM
            (
                SELECT  gpo_id,ur_id,fca_mirTrimestre1,fca_mirTrimestre2,fca_mirTrimestre3,fca_mirTrimestre4,
                    SUM(contador) total,
                    SUM(IF(a.usr_fecha<='2026-06-30',cap_semestre1,1))total2,
                    SUM(IF(a.usr_fecha<='2026-12-31',cap_semestre2,1)) total4,
                    SUM(mem_alcTrimestre1) mem_alcTrimestre1,SUM(mem_alcTrimestre2) mem_alcTrimestre2,SUM(mem_alcTrimestre3) mem_alcTrimestre3,SUM(mem_alcTrimestre4) mem_alcTrimestre4,SUM(mem_alcanzado) mem_alcanzado,
                    SUM(mem_modificado) mem_modificado,ur_clave,ur_nombre, mem_estadoTrimestre1, mem_estadoTrimestre2, mem_estadoTrimestre3, mem_estadoTrimestre4
                FROM (
                    SELECT 1 AS mem_id,1 contador, gpo_id, ur_id, ur_clave,ur_nombre,m.usr_fecha,
                    mco_alcanzado AS mem_alcanzado,mco_modificado AS mem_modificado,
                    mem_alcTrimestre1, mem_alcTrimestre2, mem_alcTrimestre3, mem_alcTrimestre4,
                    mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                    mco_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                    cap_semestre1, cap_semestre2,tru_fechaApertura1, tru_fechaApertura4,
                    fca_mirTrimestre1,fca_mirTrimestre2,fca_mirTrimestre3,fca_mirTrimestre4,
                    mem_estadoTrimestre1, mem_estadoTrimestre2,mem_estadoTrimestre3,mem_estadoTrimestre4
                    FROM enc_ProductoFinalIndicador
                    INNER JOIN cat_MetaComponente USING (epfi_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,MAX(usr_fecha) usr_fecha,
                        MAX(IF (tri_id=1,mcot_original,NULL)) ori_trimestre1,
                        MAX(IF (tri_id=2,mcot_original,NULL)) ori_trimestre2,
                        MAX(IF (tri_id=3,mcot_original,NULL)) ori_trimestre3,
                        MAX(IF (tri_id=4,mcot_original,NULL)) ori_trimestre4,
                        MAX(IF (tri_id=1,mcot_alcanzado,NULL)) mem_alcTrimestre1,
                        MAX(IF (tri_id=2,mcot_alcanzado,NULL)) mem_alcTrimestre2,
                        MAX(IF (tri_id=3,mcot_alcanzado,NULL)) mem_alcTrimestre3,
                        MAX(IF (tri_id=4,mcot_alcanzado,NULL)) mem_alcTrimestre4,
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
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS mem_estadoTrimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS mem_estadoTrimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS mem_estadoTrimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS mem_estadoTrimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_mirTrimestre1,
                        MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_mirTrimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_mirTrimestre3,
                        MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_mirTrimestre4, MAX(IF(tri_id=1,tru_fechaApertura,NULL)) tru_fechaApertura1,
                        MAX(IF(tri_id=2,tru_fechaApertura,NULL)) tru_fechaApertura2, MAX(IF(tri_id=3,tru_fechaApertura,NULL)) tru_fechaApertura3,
                        MAX(IF(tri_id=4,tru_fechaApertura,NULL)) tru_fechaApertura4
                        FROM mir_TrimestreUr
                        WHERE nim_id=3
                        GROUP BY gpo_id, ur_id
                    ) f USING (gpo_id,ur_id,nim_id)
                    INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    WHERE gpo_id!=0 and nim_id=3
                )a
            GROUP BY gpo_id,ur_id
            )b;
        ");

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-urs';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            formatNumber()
        ');
    }

    private function seleccionaTrimestre()
    {

        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($this->prm->gpo_id);
        $unidadR->setUr_id($this->prm->ur_id);
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
                        trimestre:$(this).attr("trimestre"),
                        gpo_id:' . $this->prm->gpo_id . ',
                        ur_id:' . $this->prm->ur_id . '
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
            SELECT gpo_id,ur_id,prg_id,prg_nombre,spr_id,spr_nombre, metas,SUM(pre_aprobado) AS aprobado,
                    SUM(pre_vigente) AS vigente, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                    SUM(pre_ejercido) AS ejercido,
                IF(mem_capturado2 = metas,'CAPTURADO', IF(ISNULL(porcentaje2),0,FORMAT(porcentaje2,2))) porcentaje2,
                IF(mem_capturado4 = metas,'CAPTURADO', IF(ISNULL(porcentaje4),0,FORMAT(porcentaje4,2))) porcentaje4,
                pdf_id,eri_id,ROUND(((SUM(pre_devengado)/SUM(pre_vigente))*100),2)  porcentajePresupuesto, porcentajeMeta,
                hms_mensaje,ses_id, ind_limInfVer,ind_limSupVer,ind_limInfAmar,ind_limSupAmar,ind_limInfRojo,ind_limSupRojo,
                alc_trimestre
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
            ':gpo_id' => $this->prm->gpo_id,
            ':ur_id'  => $this->prm->ur_id,
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
                $htm .= '<tr>
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
                            <th class="warning" align="center" colspan="1">OBSERVAR/VER META</th>
                            <th class="warning" align="center" colspan="1">ESTATUS</th>
                            <th class="warning" align="center" colspan="1">HISTORIAL</th>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $clase = ($cambioClase) ? 'class="warning"' : '';
                $htm .= '<tr class="warning" >
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
                                    <button type="button" id="verComponente" icon="fa-solid fa-message"" title="Opción para capturar observaciones" class="verComponente btn btn-outline-secondary" eri_id=' . $row['eri_id'] . ' pdf_id=' . $row['pdf_id'] . '>
                                        <i class="fa-solid fa-message"></i>
                                    </button>
                            </td>
                            <td ' . $clase . ' align="center" colspan="1" >' . ($row['hms_mensaje'] != '' ? 'ENVIADO' : 'SIN OBSERVACIÓN') . '</td>
                            <td ' . $clase . ' align="center" colspan="1" >
                                    <button type="button" id="verHistorial" icon="fa fa-list-alt" title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary" eri_id=' . $row['eri_id'] . ' pdf_id=' . $row['pdf_id'] . '>
                                            <i class="fa fa-list-alt"></i>
                                    </button>
                            </td>
                        </tr>
                        <tr class="warning">
                            <th></th>
                            <th colspan = "30" class ="divComponente" id="metas' . $row['eri_id'] . '"  ></th>
                        </tr>';

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
                    pdf_id:$(this).attr("pdf_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . ',
                    trimestre:' . $this->prm->trimestre . '
                });
            });

            $(".verHistorial").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    eri_id:$(this).attr("eri_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . ',
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
        $btnEnviar->icon = 'solid fa-paper-plane ';
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
            AND pdf_id=?
            GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id
            ORDER BY epfi_id;
        ');

        $params = [
            $this->prm->gpo_id,
            $this->prm->ur_id,
            $this->prm->pdf_id,
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
                WHERE ind_id=? and epfi_id=? and gpo_id =? and ur_id=?
                GROUP BY gpo_id,ur_id,prgs_id,sprs_id, epfi_id;
            ');

            $params = [
                $ind['ind_id'],
                $ind['eri_id'],
                $this->prm->gpo_id,
                $this->prm->ur_id,
            ];

            $con->consultar($params);

            while ($meta = $con->extraer()) {

                $btnEnviar->attr->epf_id = $meta['epfi_id'];

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
                                        <th>%</th>
                                        <th>OBSERVACIÓN</th>
                                        <th></th>
                                </tr>
                                <tr>
                                        <th>Primero</th>
                                        <td>' . ($meta['mem_trimestre2']) . '</td>
                                        <td>' . ($meta['mod_trimestre2']) . '</td>
                                        <td>' . ($meta['alc_trimestre2']) . ' (' . ($meta['porcentajeAlc1']) . '%)</td>
                                        <td><button type="button" class=" btn btn-small " title="">' . $meta['ava2'] . '</button></td>
                                        <td rowspan="5">' . $this->ponTextArea($descripcion) . '</td>
                                        <td>' . $this->ponBoton($btnEnviar) . '</td>
                                </tr>
                                <tr>
                                        <th>Segundo</th>
                                        <td>' . ($meta['mem_trimestre4']) . '</td>
                                        <td>' . ($meta['mod_trimestre4']) . '</td>
                                        <td>' . ($meta['alc_trimestre4']) . ' (' . ($meta['porcentajeAlc2']) . '%)</td>
                                        <td><button type="button" class=" btn btn-small " title="">' . $meta['ava4'] . '</button></td>
                                        <td></td>
                                </tr>
                                <tr>
                                        <th>Acumulado</th>
                                        <td>' . ($meta['mem_original']) . '</td>
                                        <td>' . ($meta['mem_modificado']) . '</td>
                                        <td>' . ($meta['mco_alcanzado']) . '</td>
                                        <td><button type="button" class=" btn btn-small " title="">' . $meta['porcentajeMeta'] . '</button></td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                ');
            }
            $con->desconectar();
            $this->mixHtml('</table>');

            $this->mixScript('
                $("#btnEnviarC").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"Observacion",
                        hms_mensaje:$("#descripcionC").val(),
                        eri_id:$(this).attr("eri_id"),
                        trimestre:' . $this->prm->trimestre . ',
                        hms_trimestre:' . $this->prm->trimestre . ',
                        hms_tipo: 1,
                        epf_id:$(this).attr("epf_id"),
                        nim_id:3,
                        hms_mensual: 0,
                        eme_id: 2,
                        ese_id:1,
                        gpo_id:' . $this->prm->gpo_id . ',
                        ur_id:' . $this->prm->ur_id . '
                    });
                });
            ');
        }
    }

    private function Observacion()
    {

        $this->prm->abc = 'a';
        $this->prm->pii_id = '0000';
        $this->prm->epg_id = '0000';
        $this->prm->tim_id = 3; // cat_TipoMensaje

        $this->prm->msj_id = MensajeSeguimiento::abc($this->prm);

        HistorialMensajeSeguimiento::abc($this->prm);

        $this->mixScript('
            comunica("' . $this->pantalla . '",{
                metodo:"verComponentes",
                gpo_id:' . $this->prm->gpo_id . ',
                ur_id:' . $this->prm->ur_id . ',
                trimestre:' . $this->prm->trimestre . '
            });
            notify("Mensaje enviado");
        ');
    }

    private function Historial()
    {

        $this->setContenedor('historial');

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
            <div id="myHistorial" class="modal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Historial</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $html . '</div>
                            <div class="modal-footer">
                            <button type="button" id="quitarModal" class="btn btn-outline-secondary">Cerrar</button>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixScript('
            $("#myHistorial").modal();
            $("#myHistorial").on("hidden.bs.modal", function (e) {
                $("#myHistorial").remove();
            });
            $("#quitarModal").click(function(){
                $("#myHistorial").remove();
                $(".modal-backdrop").remove();
            });
            $("#myHistorial").modal().show();
        ');
    }
}

//fin clase
if (isset($_SESSION[_S]['prm']['A']['11']['2'])) {
    new ControladorObservarComponente();
} else {
    WebResponse::sinPermisos();
}
