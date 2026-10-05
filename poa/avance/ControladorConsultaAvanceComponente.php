<?php

session_start();
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorConsultaAvanceComponente extends WebResponse
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
                case 'seleccionaSemestre':
                    $this->seleccionaSemestre();
                    break;
                case 'gestionUr':
                    $this->gestionUr();
                    break;
                case 'abrirSemestre':
                    $this->abrirSemestre();
                    break;
                case 'cerrarSemestre':
                    $this->cerrarSemestre();
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
        $btnregresarindex->class = 'btn btn-outline-secondary ';
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
        $tabla->desplegable->metodo = 'seleccionaSemestre';
        $tabla->desplegable->titulo = 'Metas';
        $tabla->desplegable->encabezado = 'Ver';
        $tabla->desplegable->enFila = false;
        $tabla->desplegable->prm = ['gpo_id', 'ur_id'];

        $tabla->accion = new stdClass();
        $tabla->accion->prm = ['gpo_id', 'ur_id', 'capturandoTri', 'capturandoTri2'];
        if (isset($_SESSION[_S]['prm']['A']['10']['6']['2'])) {
            $tabla->accion->editar = true;
        }
        $tabla->accion->metodoEditar = 'gestionUr';
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/poa/avance/rep_AvanceComponente.php';

        $th = new stdClass();
        $th->texto = 'Clave';
        $th->width = '5px';
        $tr = [];
        $th = new stdClass();
        $th->texto = '2do Sem';
        $th->rowspan = 2;
        array_push($tr, $th);
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
        $th->texto = 'Alcanzado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Capturado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Alcanzado';
        array_push($tr, $th);
        array_push($tabla->encabezado, $tr);

        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->semestre = 'semestre2';
        $td->attr->capturandoTri2 = 'capturandoTri2';
        $td->tipo = 'boton';
        $td->icono = 'print';
        $td->class = 'imp';
        array_push($tabla->cuerpo, $td);
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
            SELECT gpo_id,ur_id, 1 capturandoTri,2 semestre2,
                FORMAT(((total2*100)/total11),2) porcentaje2,
                FORMAT(((total4*100)/total),2) porcentaje4,
                FORMAT(if(mem_modificado=0,0,((mem_alcTrimestre2*100)/mem_modificado)),2) ava2,
                FORMAT(if(mem_modificado=0,0,((mem_alcTrimestre4*100)/mem_modificado)),2) ava4,
                FORMAT(if(mem_modificado=0,0,((mem_alcanzado*100)/mem_modificado)),2) ava,ur_clave,ur_nombre,
                CASE WHEN ((total2*100)/total) AND est_semestre1 = 0  THEN 1
                WHEN ((total4*100)/total) AND est_semestre2 = 0  THEN 2
                ELSE 1 END AS capturandoTri2
            FROM (
                SELECT gpo_id,ur_id,fca_mirTrimestre1,fca_mirTrimestre2,fca_mirTrimestre3,fca_mirTrimestre4,
                SUM(contador) total,SUM(IF(a.usr_fecha<='2026-06-30',contador,NULL)) total11,
                SUM(IF(a.usr_fecha<='2026-06-30',cap_semestre1,0))total2,
                SUM(IF(a.usr_fecha<='2026-12-31',cap_semestre2,1)) total4,
                SUM(alc_trimestre1) mem_alcTrimestre1,SUM(alc_trimestre2) mem_alcTrimestre2,
                SUM(alc_trimestre3) mem_alcTrimestre3,SUM(alc_trimestre4) mem_alcTrimestre4,
                SUM(mem_alcanzado) mem_alcanzado,
                SUM(mem_modificado) mem_modificado,ur_clave,ur_nombre, est_semestre1, est_semestre2
                FROM (
                    SELECT 1 AS mem_id,1 contador, gpo_id, ur_id, ur_clave,ur_nombre,m.usr_fecha,
                    mco_alcanzado AS mem_alcanzado,mco_modificado AS mem_modificado,
                    alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                    mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                    mco_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                    cap_semestre1, cap_semestre2,tru_fechaApertura1, tru_fechaApertura4,
                    fca_mirTrimestre1,fca_mirTrimestre2,fca_mirTrimestre3,fca_mirTrimestre4,
                    est_semestre1, est_semestre2
                    FROM enc_ProductoFinalIndicador
                    INNER JOIN cat_MetaComponente USING (epfi_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,MAX(usr_fecha) usr_fecha,
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
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS est_semestre1, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS est_semestre2, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_mirTrimestre1,
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
            $(".imp").click(function(){
                var gpo= $(this).attr("gpo_id");
                var ur= $(this).attr("ur_id");
                var cap1= $(this).attr("semestre");
                var cap2= $(this).attr("capturandoTri2");
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/avance/rep_AvanceComponente.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "gpo_id").val(gpo))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").val(ur))
                    .append($("<input>").attr("type", "hidden").attr("name", "capturandoTri").val(cap1))
                    .append( $("<input>").attr("type", "hidden").attr("name", "capturandoTri2").val(cap2));
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    private function seleccionaSemestre()
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
                $and = ' and mco_trimestreCierre in (0,1,2)';
                break;
            case 4:
                //mostraria las metas de todos los trimestres
                break;
        }

        $this->conectar();
        $this->setSql("
            SELECT  gpo_id, ur_id, prgs_id AS prg_id,sprs_id AS spr_id,prg_nombre,spr_nombre, SUM(pre_aprobado) AS aprobado,
                SUM(pre_vigente) AS vigente, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                SUM(pre_ejercido) AS ejercido, metas,
                if(mem_capturado2 = metas,'CAPTURADO', if(ISNULL(porcentaje2),0,format(porcentaje2,2))) porcentaje2,
                if(mem_capturado4 = metas,'CAPTURADO', if(ISNULL(porcentaje4),0,format(porcentaje4,2))) porcentaje4,
                pdf_id,epfi_id AS eri_id,emi_id
            FROM (
                SELECT gpo_id, ur_id, prg_id AS prgs_id, spr_id AS sprs_id, spr_nombre, pdf_id
                FROM cat_Subprograma)sp
                INNER JOIN enc_UrSubprogramas USING (gpo_id,ur_id,prgs_id, sprs_id)
                INNER JOIN (
                    SELECT COUNT(epfi_id) AS metas, gpo_id,ur_id,prgs_id, pdf_id,MAX(epfi_id) epfi_id,
                        SUM(cap_semestre1) mem_capturado2,
                        SUM(cap_semestre2) mem_capturado4,
                        ((SUM(cap_semestre1)*100)/COUNT(epfi_id)) porcentaje2,
                        ((SUM(cap_semestre2)*100)/COUNT(epfi_id)) porcentaje4, MAX(emi_id) emi_id
                    FROM enc_ProductoFinalIndicador
                    INNER JOIN cat_MetaComponente USING (epfi_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,MAX(usr_fecha) usr_fecha,
                        MAX(IF (tri_id=2,mcot_capturaAvance,NULL)) cap_semestre1,
                        MAX(IF (tri_id=4,mcot_capturaAvance,NULL)) cap_semestre2
                        FROM cat_MetaComponenteTrimestre
                        GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id
                    ) m USING ( gpo_id, ur_id,prgs_id, sprs_id, epfi_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS est_semestre1, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS est_semestre2, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_mirTrimestre1,
                        MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_mirTrimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_mirTrimestre3,
                        MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_mirTrimestre4, MAX(IF(tri_id=1,tru_fechaApertura,NULL)) tru_fechaApertura1,
                        MAX(IF(tri_id=2,tru_fechaApertura,NULL)) tru_fechaApertura2, MAX(IF(tri_id=3,tru_fechaApertura,NULL)) tru_fechaApertura3,
                        MAX(IF(tri_id=4,tru_fechaApertura,NULL)) tru_fechaApertura4
                        FROM mir_TrimestreUr
                        WHERE nim_id=3
                        GROUP BY gpo_id, ur_id
                    ) f USING (gpo_id,ur_id,nim_id)
                    INNER JOIN vin_UrProgramaMir USING (gpo_id,ur_id,prgs_id)
                    WHERE gpo_id=:gpo_id AND ur_id= :ur_id AND nim_id=3 " . $and . '
                    GROUP BY prgs_id, pdf_id
                )meta USING (gpo_id,ur_id,prgs_id,pdf_id)
                INNER JOIN (
                    SELECT gpo_id, ur_id, prg_id AS prgs_id, prg_nombre, usr_id, usr_fecha
                    FROM cat_Programa
                ) pgr USING (gpo_id,ur_id,prgs_id)
                LEFT JOIN (
                    SELECT gpo_id,ur_id,spr_id AS sprs_id, prg_id AS prgs_id, SUM(pre_vigente) AS pre_vigente,
                    SUM(pre_aprobado) AS pre_aprobado, SUM(pre_comprometido) AS pre_comprometido, SUM(pre_devengado) AS pre_devengado,
                    SUM(pre_ejercido) AS pre_ejercido
                    FROM presupuesto
                    INNER JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoEjercido  USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    GROUP BY gpo_id, ur_id, prg_id, spr_id
                ) pre USING (gpo_id,ur_id,prgs_id,sprs_id)
            WHERE gpo_id=:gpo_id AND ur_id=:ur_id
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id;
        ');
        $params = [
            ':gpo_id' => $this->prm->gpo_id,
            ':ur_id'  => $this->prm->ur_id,
        ];
        $this->consultar($params);
        $this->desconectar();

        $htm = '<table  class="table table table-bordered table-hover">';

        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';
        $cambioClase = true;

        while ($row = $this->extraer()) {

            if ($row['prg_id'] != $prg) {
                $htm .= '
                    <tr>
                        <th class="warning" style="text-align:center" colspan="1">PR:</th>
                        <th class="warning" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                        <th style="text-align:left" colspan="14">' . $row['prg_nombre'] .
                        ($row['emi_id'] != 9 ? '<br><font color="red">MIR DEL PROGRAMA NO VALIDADA</font>' : '') . '</th>
                        <th class="warning" align="center" colspan="2">APROBADO</th>
                        <th class="warning" align="center" colspan="2">MODIFICADO</th>
                        <th class="warning" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="warning" align="center" colspan="2">DEVENGADO</th>
                        <th class="warning" align="center" colspan="2">EJERCIDO</th>
                        <th class="warning" align="center" colspan="2">No. METAS</th>
                        <th class="warning" align="center" colspan="2">ESTATUS</th>
                    </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $clase = ($cambioClase) ? 'class="warning"' : '';
                $htm .= '
                    <tr class="warning" >
                        <td ' . $clase . ' align="center" colspan="1">
                            <button type="button" id="verComponente" icon="eye-open" title="Ver el Indicador/Meta del componente" class="verComponente btn btn-outline-secondary" eri_id=' . $row['eri_id'] . ' pdf_id=' . $row['pdf_id'] . '>
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                        <th class="warning" style="text-align:center" colspan="1">SPR:</th>
                        <th class="warning" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                        <td align="left" colspan="13">' . $row['spr_nombre'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['aprobado'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['vigente'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['comprometido'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['devengado'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['ejercido'], 2) . '</td>
                        <td ' . $clase . ' align="center" colspan="2">' . $row['metas'] . '</td>
                        <td ' . $clase . ' align="center" colspan="2">' . $row['porcentaje' . $this->prm->trimestre . ''] . '</td>
                    </tr>
                    <tr >
                        <th></th>
                        <th class ="divComponente" id="metas' . $row['eri_id'] . '" colspan = 30 >
                        </th>
                    </tr>';

                $cambioClase = ($cambioClase) ? false : true;
            }

            $ur = $row['gpo_id'] . $row['ur_id'];
            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
        }

        $htm .= '</table>';

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
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->eri_id . '');

        $this->conectar();

        $this->setSql('
            SELECT gpo_id, ur_id, prgs_id, s.sprs_id,ind_id,epfi_id AS eri_id, ind_nombreMIR, ind_definicionMIR,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, u.umi_id as umi_id,
                umi_nombre as umi_nombre, umv_id as umi_mir,umv_nombre as umiMir_nombre,
                tiv_id,tiv_nombre,ses_id,ses_nombre,
                frm_id,frm_nombre,mco_descripcion AS mem_descripcion,
                mco_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                mco_modificado AS mem_modificado, mco_alcanzado AS  mem_alcanzado, ((mco_alcanzado/mco_modificado) * 100) as porcentajeMeta
            FROM enc_ProductoFinalIndicador
            INNER JOIN cat_MetaComponente USING (epfi_id)
            INNER JOIN (
                SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,MAX(usr_fecha) usr_fecha,
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
            INNER JOIN (SELECT upm_id, vpm_id, gpo_id, ur_id, prgs_id, emi_id, sts_id
            FROM vin_UrProgramaMir)vpm USING (gpo_id,ur_id,prgs_id)
            INNER JOIN (
                SELECT gpo_id, ur_id, prg_id AS prgs_id, spr_id AS sprs_id, spr_nombre, pdf_id, usr_id, usr_fecha
                FROM cat_Subprograma
            )s USING(gpo_id,ur_id,prgs_id,sprs_id,pdf_id)
            INNER JOIN cat_Indicador i USING(nim_id,ind_id)
            INNER JOIN cat_TipoIndicador USING(tpi_id)
            INNER JOIN cat_Dimension USING(dim_id)
            INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
            INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
            LEFT JOIN cat_TipoValor USING(tiv_id)
            INNER JOIN cat_SentidoEsperado USING(ses_id)
            INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            WHERE gpo_id=? AND ur_id=?
            AND pdf_id=?
            GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id,ind_id
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
                    <tr class="warning"><th colspan=8>INDICADOR ' . ($ind['ind_id']) . '</th></tr>
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
                    mod_trimestre1,mod_trimestre2,mod_trimestre3,mod_trimestre4,
                    alc_trimestre1,alc_trimestre2,alc_trimestre3,alc_trimestre4
                FROM enc_ProductoFinalIndicador
                INNER JOIN cat_MetaComponente USING (epfi_id)
                INNER JOIN (
                    SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,MAX(usr_fecha) usr_fecha,
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
                INNER JOIN vin_UrProgramaMir USING (gpo_id,ur_id,prgs_id)
                WHERE ind_id=? and epfi_id=? and gpo_id =? and ur_id=?
                GROUP BY gpo_id,ur_id,prgs_id, m.sprs_id, epfi_id;
            ');
            $params = [
                $ind['ind_id'],
                $ind['eri_id'],
                $this->prm->gpo_id,
                $this->prm->ur_id,
            ];
            $con->consultar($params);

            while ($meta = $con->extraer()) {
                if ($meta['mod_trimestre2'] != 0 && $meta['mod_trimestre4'] != 0) {
                    switch ($ind['umi_id']) {
                        case '2':
                            $porcentaje1 = (($meta['alc_trimestre2'] / $meta['mod_trimestre2']) * 100);
                            $porcentaje2 = (($meta['alc_trimestre4'] / $meta['mod_trimestre4']) * 100);
                            break;
                        case '3':
                        case '4':
                            # code...
                            $porcentaje1 = ($meta['alc_trimestre2'] / $meta['mod_trimestre2']);
                            $porcentaje2 = ($meta['alc_trimestre4'] / $meta['mod_trimestre4']);
                            break;
                        case '5':
                            # code...
                            $porcentaje1 = (($meta['alc_trimestre2'] / $meta['mod_trimestre2']) - 1) * 100;
                            $porcentaje2 = (($meta['alc_trimestre4'] / $meta['mod_trimestre4']) - 1) * 100;
                            break;
                        default:
                            # code...
                            $porcentaje1 = $meta['alc_trimestre2'];
                            $porcentaje2 = $meta['alc_trimestre4'];
                            break;
                    }
                } else {
                    $porcentaje1 = '0.00';
                    $porcentaje2 = '0.00';
                }

                if ($ind['tiv_id'] == 1 && $this->prm->trimestre == 2) {
                    $totalsemestre = $meta['alc_trimestre2'];
                } elseif ($ind['tiv_id'] == 2 && $this->prm->trimestre == 2) {
                    $totalsemestre = $meta['alc_trimestre2'];
                } if ($ind['tiv_id'] == 1 && $this->prm->trimestre == 4) {
                    $totalsemestre = $meta['alc_trimestre2'] + $meta['alc_trimestre4'];
                } elseif ($ind['tiv_id'] == 2 && $this->prm->trimestre == 4) {
                    $totalsemestre = $meta['alc_trimestre4'];
                }

                $this->mixHtml('
                    <tr class="warning">
                        <th></th><th colspan = 7>
                            <table class="table table-hover table-bordered">
                                <tr>
                                    <th colspan = 12>META </th>
                                </tr><tr>
                                    <th>Descripcion:</th><th colspan = 11>' . ($meta['mem_descripcion']) . '</th>
                                </tr><tr>
                                    <td>Linea de base:</td><td colspan = 5>' . ($meta['mem_anio'] . ' - ' . $meta['mem_valor']) . '</td>
                                    <td>Valor deseado:</td><td colspan = 2> ' . APP_EJERCICIO . ' - ' . ($meta['mem_valorDeseado1']) . ' </td>
                                </tr><tr>
                                    <th colspan=6>1er Semestre</th>
                                    <th colspan=6>2do Semestre</th>
                                </tr><tr>
                                    <th colspan=2>Meta</th>
                                    <th colspan=2>Avance</th>
                                    <th colspan=2>Alcanzado</th>
                                    <th colspan=2>Meta</th>
                                    <th colspan=2>Avance</th>
                                    <th colspan=2>Alcanzado</th>
                                    <th colspan=2>Total</th>

                                </tr><tr>
                                    <td colspan=2>' . (number_format($meta['mod_trimestre2'], 2)) . '</td>
                                    <td colspan=2>' . (number_format($meta['alc_trimestre2'], 2)) . '</td>
                                    <td colspan=2>' . (number_format($porcentaje1, 2)) . ($ind['umi_id'] = 2 ? ' %' : '') . '</td>
                                    <td colspan=2>' . (number_format($meta['mod_trimestre4'], 2)) . '</td>
                                    <td colspan=2>' . (number_format($meta['alc_trimestre4'], 2)) . '</td>
                                    <td colspan=2>' . (number_format($porcentaje2, 2)) . ($ind['umi_id'] = 2 ? ' %' : '') . '</td>
                                    <td colspan=2>' . (number_format($totalsemestre, 2)) . '</td>
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

    private function gestionUr()
    {

        $this->conectar();
        $this->setSql('
            SELECT gpo_id,ur_id,
                IF(tru_fechaApertura2<NOW(),1, 0) apertura1,
                IF(tru_fechaApertura4<NOW(),1, 0) apertura2,
                estadoSemestre1,estadoSemestre2,
                IF(tru_fechaApertura2<=NOW(), IF(NOW()<=fechaCierre1, 1, 0), 0) semestre1,
                IF(CONCAT(tru_fechaApertura4)<=NOW(), IF(NOW()<=fechaCierre2, 1, 0), 0) semestre2,
                IF(((total1*100)/total)=100,1,0) AS concluido1, IF(((total2*100)/total)>=100,1,0) AS concluido2,
                (total1*100)/total porcentaje1,(total2*100)/total porcentaje2
            FROM (
                SELECT gpo_id,ur_id,
                    fca_mirTrimestre2 fechaCierre1,fca_mirTrimestre4 fechaCierre2,est_semestre1 estadoSemestre1,est_semestre2 estadoSemestre2,
                SUM(contador) total,SUM(cap_semestre1) total1,SUM(cap_semestre2)total2,tru_fechaApertura2,tru_fechaApertura4
                FROM(
                    SELECT 1 contador,gpo_id,ur_id,cap_semestre1,cap_semestre2,mco_modificado AS mem_modificado,
                        mco_alcanzado AS mem_alcanzado,
                        fca_mirTrimestre2,fca_mirTrimestre4,est_semestre1,est_semestre2,
                        tru_fechaApertura2,tru_fechaApertura4
                        FROM enc_ProductoFinalIndicador
                        INNER JOIN cat_MetaComponente USING (epfi_id)
                        INNER JOIN (
                            SELECT gpo_id, ur_id,prgs_id, sprs_id,
                            MAX(IF (tri_id=2,mcot_capturaAvance,NULL)) cap_semestre1,
                            MAX(IF (tri_id=4,mcot_capturaAvance,NULL)) cap_semestre2
                            FROM cat_MetaComponenteTrimestre
                            GROUP BY gpo_id,ur_id,prgs_id,sprs_id
                        ) m USING ( gpo_id, ur_id,prgs_id, sprs_id)
                        INNER JOIN (
                            SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                            MAX(IF(tri_id=2,etr_id1,NULL)) AS est_semestre1, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                            MAX(IF(tri_id=4,etr_id1,NULL)) AS est_semestre2, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_mirTrimestre1,
                            MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_mirTrimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_mirTrimestre3,
                            MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_mirTrimestre4, MAX(IF(tri_id=1,tru_fechaApertura,NULL)) tru_fechaApertura1,
                            MAX(IF(tri_id=2,tru_fechaApertura,NULL)) tru_fechaApertura2, MAX(IF(tri_id=3,tru_fechaApertura,NULL)) tru_fechaApertura3,
                            MAX(IF(tri_id=4,tru_fechaApertura,NULL)) tru_fechaApertura4
                            FROM mir_TrimestreUr
                            WHERE nim_id=3
                            GROUP BY gpo_id, ur_id
                        ) f USING (gpo_id,ur_id,nim_id)
                        INNER JOIN vin_UrProgramaMir USING (gpo_id,ur_id,prgs_id)
                        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                        WHERE gpo_id=? AND ur_id=? AND emi_id=9 AND nim_id=3
                )b
                GROUP BY gpo_id,ur_id
            )a;
        ');
        $params = [
            $this->prm->gpo_id,
            $this->prm->ur_id,
        ];
        $this->consultar($params);
        $verTrimestre2 = $this->extraer();
        $this->desconectar();

        $boton = new stdClass();
        $boton->title = 'Regresar a lista de UR';
        $boton->icon = 'circle-left';
        $boton->id = 'regresar1';
        $boton->value = ' Regresar';
        $boton->color = 'gris';
        $boton->click = ' comunica("' . $this->pantalla . '"); ';
        $this->agregaTools($this->ponBoton($boton));

        $tri = '<div style="width:100%; padding-left:30%; padding-bottom:50px;">';

        $trimestre = ['Ene-Jun', 'Jul-Dic'];
        $trimestreActivo = 1;

        $boton = new stdClass();
        for ($x = 0; $x <= 1; $x++) {
            $boton->id = 'cambiaEstado' . $x;
            $tri .= '
                <div class="boton_opcion">
                    <div class="boton_opcion_titulo">
                    <strong>
                        <span style="font-size: 20px;" class="" aria-hidden="true"></span>' . $trimestre[$x] . '
                    </div>
                <div>';
            if ($verTrimestre2['apertura' . ($x + 1) . ''] == 1) {
                if ($verTrimestre2['estadoSemestre' . ($x + 1) . ''] == 'C' || ($verTrimestre2['semestre' . ($x + 1) . ''] == 0 && $verTrimestre2['estadoSemestre' . ($x + 1) . ''] == 'A' && $verTrimestre2['concluido' . ($x + 1) . ''] == 0)) {
                    $boton->value = 'Abrir';
                    $boton->color = 'verde';
                    $boton->click = '
                        $("#divContenedorGeneralModal").html(
                            $("<div/>").addClass("modal-backdrop in").attr("id","backModal")
                        ).append(
                            $("<div/>").addClass("modal")
                                .css("display","block")
                                .attr("id","modalModal")
                                .html(
                                    $("<div/>").addClass("modal-content")
                                    .css("margin","30% 40%")
                                    .css("margin-top","5%")
                                    .width("280px")
                                    .attr("id","modalCarga")
                                    .html(
                                        $("<div/>").addClass("modal-header")
                                        .html( $("<H4/>").addClass("modal-title").html("ABRIR SEMESTRE") )
                                    ).append(
                                        $("<div/>").addClass("modal-body")
                                        .html($("<label/>").text("Fecha limite para captura:"))
                                        .append(
                                            $("<div/>").addClass("input-group date")
                                            .attr("data-date-format","yyyy-mm-dd")
                                            .html(
                                                $("<input/>").addClass("form-control fecha").attr("id","fechaLimite")
                                            ).append(
                                                $("<span/>").addClass("input-group-btn")
                                            .html(
                                                $("<button/>").addClass("btn btn-outline-secondary")
                                            .html(
                                                $("<span/>").addClass("fa fa-calendar-days")
                                            )
                                                                                                                )
                                            ).datepicker({ "keyboardNavigation":true,"language":"es" })
                                                ).append(
                                                    $("<button/>").addClass("btn btn-outline-primary me-1")
                                                    .html(
                                                        $("<span/>").addClass("glyphicon glyphicon-ok")
                                                    ).text("Aceptar").click(function(){
                                                        comunica( "' . $this->pantalla . '", { metodo:"abrirSemestre", semestre:' . ($x + 1) . ', gpo_id:' . $this->prm->gpo_id . ', ur_id:' . $this->prm->ur_id . ',fecha:$("#fechaLimite").val() });
                                                        $("#backModal,#modalModal").remove();
                                                    })
                                            ).append(
                                                $("<button/>").addClass("btn btn-outline-danger")
                                                .html(
                                                    $("<span/>").addClass("glyphicon glyphicon-remove")
                                                ).text("Cancelar").click(function(){
                                                    $("#backModal,#modalModal").remove();
                                                })
                                        )
                                    )
                                )
                        );
                    ';
                    $tri .= $this->ponBoton($boton);
                } elseif ($verTrimestre2['semestre' . ($x + 1) . ''] == 0 && $verTrimestre2['estadoSemestre' . ($x + 1) . ''] == 'A' && $verTrimestre2['concluido' . ($x + 1) . ''] == 1) {
                    $boton->value = 'Cerrar';
                    $boton->color = 'rojo';
                    $boton->click = ' comunica("' . $this->pantalla . '",{ semestre: ' . ($x + 1) . ', metodo: "cerrarSemestre", gpo_id:' . $this->prm->gpo_id . ', ur_id:' . $this->prm->ur_id . ' }); ';
                    $tri .= $this->ponBoton($boton);
                } else {
                    $tri .= 'Bloqueado';
                }
            } else {
                $tri .= 'Bloqueado';
            }
            $tri .= '<div>';
            $tri .= '
                </strong></div>
                </div>
                </div><div style="width:10px; display:table-cell"></div>
            ';
        }

        $this->mixHtml($tri);

        if (isset($this->prm->capturandoTri)) {

            $this->mixScript('
                $(".printPdf").click(function(){
                    var form = $("<form>")
                        .attr("action", "rep/doc/rep_Avance.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append( $("<input>").attr("type", "hidden").attr("name", "capturandoTri").val( "' . $this->prm->capturandoTri . '" ) )
                        .append($("<input>").attr("type", "hidden").attr("name", "gpo_id").val( "' . $this->prm->gpo_id . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( "' . $this->prm->ur_id . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "completo").val( true ));
                    $("body").append(form);
                    form.submit();
                    form.remove();
                });
            ');
        }
    }

    private function cerrarSemestre($agestion = true)
    {
        $this->conectar();

        if (isset($_SESSION[_S]['prm']['A']['10']['6']['2'])) {
            $ip = ($this->getIp() != '::1') ? $this->getIp() : '127.0.0.1';

            $this->setSql('
                SET 	@mip = INET_ATON( "' . $ip . '"),
                @usr_id = ' . $_SESSION[_S]['usr_id'] . ',
                @bit_observacion = "CERRADO DESDE SISTEMA "');

            $this->consultar();
            $this->setSql('
                UPDATE mir_TrimestreUr
                SET etr_id1="C"
                WHERE gpo_id=? AND ur_id=?
                AND nim_id=3 AND tri_id=?;
            ');
            $params = [
                $this->prm->gpo_id,
                $this->prm->ur_id,
                ($this->prm->semestre * 2),
            ];
            $this->consultar($params);
            $this->desconectar();
        } else {
            $this->mixScript('notify("No tiene permisos para realizar esta acción","error");');
        }
        if ($agestion) {
            $this->gestionUr();
        }

    }

    private function abrirSemestre()
    {
        if (isset($_SESSION[_S]['prm']['A']['10']['6']['2'])) {

            $this->conectar();
            $this->setSql('
                UPDATE mir_TrimestreUr
                SET tru_fechaCierre  = "' . $this->prm->fecha . ' 23:59:59",
                    etr_id1="A"
                WHERE gpo_id=? AND ur_id=?
                AND nim_id=3 AND tri_id=?;
            ');

            $params = [
                $this->prm->gpo_id,
                $this->prm->ur_id,
                ($this->prm->semestre * 2),
            ];

            $this->consultar($params);
            $ip = ($this->getIp() != '::1') ? $this->getIp() : '127.0.0.1';
            $this->conectar();
            $this->setSql('
                SET 	@mip = INET_ATON(?),
                @usr_id = ?,
                @bit_observacion = "ABIERTO DESDE SISTEMA "
            ');
            $paramsb = [
                $ip,
                $_SESSION[_S]['usr_id'],
            ];
            $this->consultar($paramsb);
            $this->desconectar();
        } else {
            $this->mixScript('notify("No tiene permisos para realizar esta acción","error");');
        }
        $this->gestionUr();
    }
}

//fin clase
if (isset($_SESSION[_S]['prm']['A']['10']['6'])) {
    new ControladorConsultaAvanceComponente();
} else {
    WebResponse::sinPermisos();
}
