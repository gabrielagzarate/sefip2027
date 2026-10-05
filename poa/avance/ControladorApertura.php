<?php

session_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../lib/poa/reporte/URsConsolidadoAvanceGestion.php';

class ControladorApertura extends WebResponse
{
    public $reportemasivo;

    public function __construct()
    {
        parent::__construct();
        $this->reportemasivo = isset($_SESSION[_S]['prm']['A']['10']['2']['117']) ? true : false;
        if ($_SESSION[_S]['nvj_id'] > 1) {
            switch ($this->metodo) {
                case 'verMetas':
                    $this->verMetas();
                    break;
                case 'gestionUr':
                    $this->gestionUr();
                    break;
                case 'abrirTrimestre':
                    $this->abrirTrimestre();
                    break;
                case 'cerrarTrimestre':
                    $this->cerrarTrimestre();
                    break;
                case 'verActividades':
                    $this->verActividades();
                    break;
                case 'seleccionaTrimestre':
                    $this->seleccionaTrimestre();
                    break;
                case 'exportaReportesMasivos':
                    $this->exportaReportesMasivos();
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
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'fa-solid fa-circle-left me-1';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("nav").menusinpres("clickMenu");
        ';
        $this->agregaTools($this->ponboton($btnregresarindex));

        if ($this->reportemasivo) {
            $btnDescargaMasivaPDF = new stdClass();
            $btnDescargaMasivaPDF->id = 'pdf';
            $btnDescargaMasivaPDF->name = 'pdf';
            $btnDescargaMasivaPDF->text = 'PDF';
            $btnDescargaMasivaPDF->value = 'PDF';
            $btnDescargaMasivaPDF->type = 'button';
            $btnDescargaMasivaPDF->color = 'rojo';
            $btnDescargaMasivaPDF->icon = 'file-pdf me-2';
            $btnDescargaMasivaPDF->class = 'me-1';
            $btnDescargaMasivaPDF->click = '
                var var_tipo=$(this).attr("id");
                comunica("' . $this->pantalla . '",{
                    metodo: "exportaReportesMasivos",
                    tipo:var_tipo
                });
            ';

            $btnDescargaMasivaExcel = new stdClass();
            $btnDescargaMasivaExcel->id = 'excel';
            $btnDescargaMasivaExcel->name = 'excel';
            $btnDescargaMasivaExcel->text = 'EXCEL y CSV';
            $btnDescargaMasivaExcel->value = 'EXCEL y CSV';
            $btnDescargaMasivaExcel->type = 'button';
            $btnDescargaMasivaExcel->color = 'verde';
            $btnDescargaMasivaExcel->icon = 'file-excel me-2';
            $btnDescargaMasivaExcel->class = 'me-1';
            $btnDescargaMasivaExcel->click = '
                var var_tipo=$(this).attr("id");
                comunica("' . $this->pantalla . '",{
                    metodo: "exportaReportesMasivos",
                    tipo:var_tipo
                });
            ';

            $this->agregaTools($this->ponBoton($btnDescargaMasivaPDF));
            $this->agregaTools($this->ponBoton($btnDescargaMasivaExcel));
        }

        $boton = new stdClass();
        $boton->icon = 'fa-solid fa-file-excel me-2';
        $boton->id = 'exportar';
        $boton->value = ' Exportar';
        $boton->color = 'verde';
        $boton->click = '
            var form = $("<form>")
                .attr("action", "rep/excel/ExportaConsultaAvanceUr.php")
                .attr("method", "post")
                .attr("target", "_blank");
            $("body").append(form);
            form.submit();
            form.remove();
        ';
        $tabla = new stdClass();
        $tabla->id = 'tbl-urs';
        $tabla->numFilas = 200;
        $tabla->titulo = 'Listado de UR\'s';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMetas';

        $tabla->desplegable = new stdClass();
        $tabla->desplegable->metodo = 'seleccionaTrimestre';
        $tabla->desplegable->titulo = 'Metas';
        $tabla->desplegable->encabezado = 'Ver';
        $tabla->desplegable->enFila = false;
        $tabla->desplegable->prm = ['gpo_id', 'ur_id'];

        $tabla->accion = new stdClass();
        $tabla->accion->prm = ['gpo_id', 'ur_id', 'capturandoTri', 'capturandoTri2'];
        if (isset($_SESSION[_S]['prm']['A']['10']['2']['2'])) {
            $tabla->accion->editar = true;
        }
        $tabla->accion->metodoEditar = 'gestionUr';
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/poa/rep_Avance.php';
        $tabla->accion->excel = true;
        $tabla->accion->urlExcel = 'rep/excel/poa/exp_Avance.php';

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
        $th->texto = '1er Trimestre';
        $th->colspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '2do Trimestre';
        $th->colspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '3er Trimestre';
        $th->colspan = 2;
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '4to Trimestre';
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
        $td->campoTxt = 'porcentaje1';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ava1';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje2';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ava2';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje3';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ava3';
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
            CASE WHEN '2026-01-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre1 THEN 1
            WHEN '2026-07-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre2 THEN 2
            WHEN '2026-10-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre3 THEN 3
            WHEN '2027-01-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre4 THEN 4
            ELSE 0 END AS capturandoTri,
            FORMAT(((total1*100)/total11),2) porcentaje1,
            FORMAT(((total2*100)/total22),2) porcentaje2,
            FORMAT(((total3*100)/total33),2) porcentaje3,
            FORMAT(((total4*100)/total44),2) porcentaje4,
            FORMAT(IF(mem_modificado=0,0,((alc_trimestre1*100)/mem_modificado)),2) ava1,
            FORMAT(IF(mem_modificado=0,0,((alc_trimestre2*100)/mem_modificado)),2) ava2,
            FORMAT(IF(mem_modificado=0,0,((alc_trimestre3*100)/mem_modificado)),2) ava3,
            FORMAT(IF(mem_modificado=0,0,((alc_trimestre4*100)/mem_modificado)),2) ava4,
            FORMAT(IF(mem_modificado=0,0,((mem_alcanzado*100)/mem_modificado)),2) ava,ur_clave,ur_nombre,
            CASE WHEN NOW()<='2026-07-01 08:00:00'THEN 1
            WHEN '2026-07-01 08:00:00'<=NOW() AND NOW()<='2026-10-01 08:00:00' THEN 2
            WHEN '2026-10-01 08:00:00'<=NOW() AND NOW()<= '2026-01-01 08:00:00' THEN 3
            WHEN '2027-01-01 08:00:00'<=NOW() THEN 4
            ELSE 1 END AS capturandoTri2
            FROM (
                SELECT gpo_id,ur_id,fca_trimestre1,fca_trimestre2,fca_trimestre3,fca_trimestre4,
                SUM(contador) total,
                    SUM(IF(a.usr_fecha<='2026-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),contador,0)) total11,
                SUM(IF(a.usr_fecha<='2026-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),contador,0)) total22,
                SUM(IF(a.usr_fecha<='2026-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),contador,0)) total33,
                SUM(IF(a.usr_fecha<='2026-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),contador,0)) total44,
                SUM(IF(a.usr_fecha<='2026-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),cap_trimestre1,0)) total1,
                SUM(IF(a.usr_fecha<='2026-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),cap_trimestre2,0))total2,
                SUM(IF(a.usr_fecha<='2026-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),cap_trimestre3,0))total3,
                SUM(IF(a.usr_fecha<='2026-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),cap_trimestre4,0)) total4,
                SUM(alc_trimestre1) alc_trimestre1,SUM(alc_trimestre2) alc_trimestre2,
                SUM(alc_trimestre3) alc_trimestre3,SUM(alc_trimestre4) alc_trimestre4,
                SUM(mem_alcanzado) mem_alcanzado, SUM(mem_modificado) mem_modificado,ur_clave,ur_nombre,
                est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                MAX(cap_trimestre1) AS cap_trimestre1, MAX(cap_trimestre2) AS cap_trimestre2,
                MAX(cap_trimestre3) AS cap_trimestre3, MAX(cap_trimestre4) AS cap_trimestre4,
                SUM(mac_original) AS mac_original, SUM(pre_vigente) AS pre_vigente
                FROM (
                    SELECT pii_id,1 contador, gpo_id, ur_id, ur_clave,ur_nombre,m.usr_fecha,
                    epg_id,mac_alcanzado AS mem_alcanzado,mac_modificado AS mem_modificado,
                    alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                    mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                    mac_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                    cap_trimestre1,cap_trimestre2,cap_trimestre3,cap_trimestre4,
                    fca_trimestre1,fca_trimestre2,fca_trimestre3,fca_trimestre4,
                    est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                    SUM(vig_trimestre1) AS vig_trimestre1,SUM(vig_trimestre2) AS vig_trimestre2,
                    SUM(vig_trimestre3) AS vig_trimestre3,SUM(vig_trimestre4) AS vig_trimestre4 ,
                    mac_original,SUM(pre_vigente) AS pre_vigente
                    FROM cat_UnidadResponsable
                    INNER JOIN cat_ObraActividad USING(gpo_id,ur_id)
                    INNER JOIN enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_trimestre1,
                        MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_trimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_trimestre3,
                        MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_trimestre4
                        FROM mir_TrimestreUr
                        WHERE nim_id=4
                        GROUP BY gpo_id, ur_id
                    )est USING (gpo_id,ur_id)
                    LEFT JOIN cat_MetaActividad m USING (epg_id)
                    LEFT JOIN (
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
                    LEFT JOIN (
                        SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id, SUM(pre_vigente) AS pre_vigente,epg_id,
                        SUM(vig_ene+vig_feb+vig_mar) vig_trimestre1, SUM(vig_abr+vig_may+vig_jun) vig_trimestre2,
                        SUM(vig_jul+vig_ago+vig_sep) vig_trimestre3, SUM(vig_oct+vig_nov+vig_dic)vig_trimestre4
                        FROM presupuesto
                        INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id
                    ) AS temp USING(gpo_id,ur_id,epg_id)
                    WHERE gpo_id!=0

                    AND ((eoa_id=5 AND toa_id=2) OR (eoa_id=4 AND toa_id=1))
                    GROUP BY epg_id,pii_id
                )a
                WHERE (mac_original>0 || mem_modificado>0 ||pre_vigente>0)
                GROUP BY gpo_id,ur_id
                ORDER BY gpo_id,ur_id
            )b;
        ");
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-urs';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            formatNumber()
        ');
        $this->mixStyle('
            .tabla-contenedor table thead tr:first-child {
                position: sticky;
                top: 0;
                z-index: 10;
            }
            .tabla-contenedor table thead tr:first-child th {
                background-color: #EBD3DA !important;
                color: #7d1c3a !important;
            }

            .tabla-contenedor table thead tr:nth-child(2) {
                position: sticky;
                top: 35px;
                z-index: 9;
            }
            .tabla-contenedor table thead tr:nth-child(2) th {
                background-color: #EBD3DA !important;
                color: #7d1c3a !important;
            }
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

        $trimestre = ['Ene-Feb-Mar', 'Abr-May-Jun', 'Jul-Ago-Sep', 'Oct-Nov-Dic'];

        for ($x = 0; $x <= 3; $x++) {
            $tri .= '
                <div class="boton_opcion trim " trimestre=' . ($x + 1) . '>
                    <div class="boton_opcion_titulo">
                        <strong>
                            <span style="font-size: 20px;" class="" aria-hidden="true"></span>' . $trimestre[$x] . '</div>
                            <div><div>
                        </strong>
                    </div>
                </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
            ';
        }

        $this->mixHtml($tri . '</div><div class="row" id="actividades"></div>');
        $this->mixScript('
            $(".trim").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"verActividades",
                    trimestre:$(this).attr("trimestre"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . '
                });
            });
        ');
    }

    private function verActividades()
    {

        $this->setContenedor('actividades');

        $and = $presupuesto = '';
        switch ($this->prm->trimestre) {
            case 1:
                $and = "o.usr_fecha <= '2026-03-31' ";
                $presupuesto = 'SUM(apr_ene+apr_feb+apr_mar) AS aprobado, SUM(vig_ene+vig_feb+vig_mar) AS vigente,SUM(com_ene+com_feb+com_mar) comprometido,SUM(dev_ene+dev_feb+dev_mar) devengado,SUM(eje_ene+eje_feb+eje_mar) ejercido';
                break;
            case 2:
                $and = "o.usr_fecha <= '2026-06-30' ";
                $presupuesto = 'SUM(apr_abr+apr_may+apr_jun) AS aprobado,SUM(vig_abr+vig_may+vig_jun) AS vigente, SUM(com_abr+com_may+com_jun) comprometido,SUM(dev_abr+dev_may+dev_jun) devengado,SUM(eje_abr+eje_may+eje_jun) ejercido';
                break;
            case 3:
                $and = "o.usr_fecha <= '2026-09-30' ";
                $presupuesto = 'SUM(apr_jul+apr_ago+apr_sep) AS aprobado,SUM(vig_jul+vig_ago+vig_sep) AS vigente, SUM(com_jul+com_ago+com_sep) comprometido,SUM(dev_jul+dev_ago+dev_sep) devengado,SUM(eje_jul+eje_ago+eje_sep) ejercido';
                break;
            case 4:
                $and = "o.usr_fecha <= '2026-12-31' ";
                $presupuesto = 'SUM(apr_oct+apr_nov+apr_dic) AS aprobado, SUM(vig_oct+vig_nov+vig_dic) AS vigente, SUM(com_oct+com_nov+com_dic) comprometido,SUM(dev_oct+dev_nov+dev_dic) devengado,SUM(eje_oct+eje_nov+eje_dic) ejercido';
                break;
        }

        $this->conectar();
        $this->setSql("
            SELECT gpo_id, ur_id, prg_id,spr_id,py_id,oa_id,prg_nombre,spr_nombre,oa_nombre, SUM( aprobado) aprobado,
                SUM(vigente) vigente, SUM(comprometido) comprometido, SUM(devengado) devengado, SUM(ejercido) ejercido,
                epg_id, py_nombre, toa_id, oa_origen,metas,
                IF(cap_trimestre1 = metas,'CAPTURADO', IF(ISNULL(porcentaje1),0,FORMAT(porcentaje1,2))) porcentaje1,
                IF(cap_trimestre2 = metas,'CAPTURADO', IF(ISNULL(porcentaje2),0,FORMAT(porcentaje2,2))) porcentaje2,
                IF(cap_trimestre3 = metas,'CAPTURADO', IF(ISNULL(porcentaje3),0,FORMAT(porcentaje3,2))) porcentaje3,
                IF(cap_trimestre4 = metas,'CAPTURADO', IF(ISNULL(porcentaje4),0,FORMAT(porcentaje4,2))) porcentaje4,
                pdf_id, o.usr_fecha
            FROM cat_ObraActividad o
            INNER JOIN enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
            INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN (
                SELECT COUNT(pii_id) AS metas, gpo_id,ur_id,epg_id,
                    SUM(cap_trimestre1) cap_trimestre1,SUM(cap_trimestre2) cap_trimestre2,
                    SUM(cap_trimestre3) cap_trimestre3, SUM(cap_trimestre4) cap_trimestre4,
                    ((SUM(cap_trimestre1)*100)/COUNT(pii_id)) porcentaje1,
                    ((SUM(cap_trimestre2)*100)/COUNT(pii_id)) porcentaje2,
                    ((SUM(cap_trimestre3)*100)/COUNT(pii_id)) porcentaje3,
                    ((SUM(cap_trimestre4)*100)/COUNT(pii_id)) porcentaje4,
                    SUM(mod_trimestre1)mod_trimestre1,SUM(mod_trimestre2)mod_trimestre2,
                    SUM(mod_trimestre3)mod_trimestre3,SUM(mod_trimestre4)mod_trimestre4
                FROM cat_MetaActividad m
                INNER JOIN (
                    SELECT pii_id, epg_id, SUM(IF(tri_id=1,mact_alcanzado,NULL))  alc_trimestre1,
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
                INNER JOIN cat_ObraActividad USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN (
                    SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                    MAX(IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                    MAX(IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_trimestre1,
                    MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_trimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_trimestre3,
                    MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_trimestre4
                    FROM mir_TrimestreUr
                    WHERE nim_id=4
                    GROUP BY gpo_id, ur_id
                )est USING (gpo_id,ur_id)
                WHERE gpo_id= :gpo_id AND ur_id= :ur_id
                    AND ((eoa_id=5 AND toa_id=2) OR (eoa_id=4 AND toa_id=1))
                GROUP BY epg_id
            )meta USING(gpo_id,ur_id,epg_id)
            INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
            LEFT JOIN (SELECT  gpo_id, ur_id, epg_id,
                " . $presupuesto . "
                FROM presupuesto
                INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id
            ) p using (gpo_id, ur_id,epg_id)
            WHERE gpo_id= :gpo_id  AND ur_id= :ur_id AND $and
            AND (mod_trimestre" . $this->prm->trimestre . '>0 || vigente>0)
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id;
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
                $htm .= '<tr>
                            <th class="warning" style="text-align:center" colspan="1">PR:</th>
                            <th class="warning" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                            <th style="text-align:left" colspan="10">' . $row['prg_nombre'] . '</th>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $htm .= '<tr>
                            <th class="warning" style="text-align:center" colspan="1">SPR:</th>
                            <th class="warning" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                            <td align="left" colspan="10">' . $row['spr_nombre'] . '</td>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py) {
                $htm .= '<tr>
                            <th class="warning" style="text-align:center" colspan="1">PYT:</th>
                            <th class="warning" style="text-align:center" colspan="1">' . $row['py_id'] . '</th>
                            <td align="left" colspan="5">' . $row['py_nombre'] . '</td>
                            <th class="warning" align="center" colspan="2">APROBADO</th>
                            <th class="warning" align="center" colspan="2">MODIFICADO</th>
                            <th class="warning" align="center" colspan="2">COMPROMETIDO</th>
                            <th class="warning" align="center" colspan="2">DEVENGADO</th>
                            <th class="warning" align="center" colspan="2">EJERCIDO</th>
                            <th class="warning" align="center" colspan="2">No. INDICADORES</th>
                            <th class="warning" align="center" colspan="2">ESTATUS</th>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py || $row['oa_id'] != $oa) {
                $clase = ($cambioClase) ? 'class="warning"' : '';
                $htm .= '<tr class="warning" >
                            <td ' . $clase . ' align="center" colspan="1">
                                <button type="button" id="verActividad" icon="fa-regular fa-eye" title="Ver el Indicador/Meta de la Actividad" class="verActividad btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </td>
                            <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                            <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                            <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                            <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                            <td ' . $clase . ' align="left" colspan="2">' . $row['oa_nombre'] . '</td>
                            <td ' . $clase . ' align="right" colspan="2">' . $row['aprobado'] . '</td>
                            <td ' . $clase . ' align="right" colspan="2">' . $row['vigente'] . '</td>
                            <td ' . $clase . ' align="right" colspan="2">' . $row['comprometido'] . '</td>
                            <td ' . $clase . ' align="right" colspan="2">' . $row['devengado'] . '</td>
                            <td ' . $clase . ' align="right" colspan="2">' . $row['ejercido'] . '</td>
                            <td ' . $clase . ' align="center" colspan="2">' . $row['metas'] . '</td>
                            <td ' . ($row['porcentaje' . $this->prm->trimestre . ''] != 'CAPTURADO' ? 'title="Ø EL EJECUTOR NO REPORTÓ INFORMACIÓN SOBRE EL AVANCE DE METAS"' : '') . ' ' . $clase . ' align="center" colspan="2">' . $row['porcentaje' . $this->prm->trimestre . ''] . '</td>
                        </tr>
                        <tr class="warning">
                            <th></th>
                                <th class ="divMetas" id="metas' . $row['epg_id'] . '" colspan = 19 >
                                </th>
                        </tr>';
                $cambioClase = ($cambioClase) ? false : true;
            }
            $ur = $row['gpo_id'] . $row['ur_id'];
            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
            $py = $row['py_id'];
            $oa = $row['oa_id'];
        }

        $htm .= '</table>';

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verActividad").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    epg_id:$(this).attr("epg_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . '
                });
            });
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->epg_id . '');

        $this->conectar();

        $this->setSql('
            SELECT gpo_id, ur_id, prg_id, spr_id, py_id, oa_id,MAX(nim_id) nim_id,ind_id, ind_nombreMIR, ind_definicionMIR,
            ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, u.umi_id AS umi_id,
            u.umi_nombre AS umi_nombre, i.umv_id AS umi_mir, umir.umv_nombre AS umiMir_nombre,
            IF(tiv_id=1,2,tiv_id) tiv_id, IF(tiv_id=1,"ACUMULADO",tiv_nombre) tiv_nombre,ses_id,ses_nombre, frm_id,frm_nombre,
            pii_id,mac_descripcion, pdi_id, epg_id, mac_agrupadorPresupuestal,
            mac_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
            mac_modificado, mac_alcanzado, ((mac_alcanzado/mac_modificado) * 100) AS porcentajeMeta
            FROM cat_MetaActividad
            INNER JOIN (
                SELECT pii_id, epg_id,SUM(IF(tri_id=1,mact_original,NULL)) ori_trimestre1,
                SUM(IF(tri_id=2,mact_original,NULL)) ori_trimestre2, SUM(IF(tri_id=3,mact_original,NULL)) ori_trimestre3,
                SUM(IF(tri_id=4,mact_original,NULL)) ori_trimestre4
                FROM cat_MetaActividadTrimestre
                GROUP BY pii_id,epg_id
            )mact USING (pii_id,epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador USING (pii_id)
            INNER JOIN enc_Programatico USING(epg_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_Indicador i USING(nim_id,ind_id)
            INNER JOIN cat_TipoIndicador USING(tpi_id)
            INNER JOIN cat_Dimension USING(dim_id)
            INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
            INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
            INNER JOIN cat_TipoValor USING(tiv_id)
            INNER JOIN cat_SentidoEsperado USING(ses_id)
            INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            WHERE gpo_id = ?  AND ur_id= ? AND epg_id= ?
            GROUP BY gpo_id,ur_id,pii_id,ind_id
            ORDER BY pii_id;
        ');

        $params = [
            $this->prm->gpo_id,
            $this->prm->ur_id,
            $this->prm->epg_id,
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
                SELECT gpo_id,ur_id,pii_id,mac_descripcion,macm_anio,macm_valor,
                ((alc_trimestre1*100)/mac_modificado) ava1,
                ((alc_trimestre2*100)/mac_modificado) ava2,
                ((alc_trimestre3*100)/mac_modificado) ava3,
                ((alc_trimestre4*100)/mac_modificado) ava4,
                mod_trimestre1,mod_trimestre2,mod_trimestre3,mod_trimestre4,
                alc_trimestre1,alc_trimestre2,alc_trimestre3,alc_trimestre4,
                mac_modificado,mac_id
                FROM cat_MetaActividad m
                INNER JOIN (
                    SELECT pii_id, epg_id, SUM(IF(tri_id=1,mact_alcanzado,NULL)) alc_trimestre1,
                    SUM(IF(tri_id=2,mact_alcanzado,NULL)) alc_trimestre2,  SUM(IF(tri_id=3,mact_alcanzado,NULL)) alc_trimestre3,
                    SUM(IF(tri_id=4,mact_alcanzado,NULL)) alc_trimestre4,  SUM(IF(tri_id=1,mact_modificado,NULL)) mod_trimestre1,
                    SUM(IF(tri_id=2,mact_modificado,NULL)) mod_trimestre2, SUM(IF(tri_id=3,mact_modificado,NULL)) mod_trimestre3,
                    SUM(IF(tri_id=4,mact_modificado,NULL)) mod_trimestre4, SUM(IF(tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1
                    FROM cat_MetaActividadTrimestre
                    GROUP BY pii_id,epg_id
                )mat USING (pii_id,epg_id)
                INNER JOIN vin_ProductoIntermedioIndicador USING (pii_id)
                INNER JOIN enc_Programatico USING(epg_id)
                LEFT JOIN cat_MetaActividadMir USING(pii_id,gpo_id,ur_id)
                WHERE ind_id= ? and epg_id= ?
                GROUP BY gpo_id,ur_id,pii_id,m.macm_id;
            ');

            $params = [
                $ind['ind_id'],
                $this->prm->epg_id,
            ];

            $con->consultar($params);
            while ($meta = $con->extraer()) {
                $this->mixHtml('
                    <tr class="warning">
                        <th></th><th colspan = 7>
                            <table class="table table-hover table-bordered">
                                <tr>
                                    <th colspan = 12>META NÚMERO ' . $meta['pii_id'] . '</th>
                                </tr><tr>
                                    <th>Descripcion:</th><th colspan = 11>' . ($meta['mac_descripcion']) . '</th>
                                </tr><tr>
                                    <td>Linea de base:</td><td colspan = 5>' . ($meta['macm_anio'] . ' - ' . $meta['macm_valor']) . '</td>
                                    <td>Valor deseado:</td><td colspan = 2> ' . APP_EJERCICIO . ' - ' . ($meta['mac_modificado']) . ' </td>
                                </tr><tr>
                                    <th colspan=3>1er Trimestre</th>
                                    <th colspan=3>2er Trimestre</th>
                                    <th colspan=3>3er Trimestre</th>
                                    <th colspan=3>4er Trimestre</th>
                                </tr><tr>
                                    <th>Meta</th>
                                    <th>Avance</th>
                                    <th>Alcanzado</th>
                                    <th>Meta</th>
                                    <th>Avance</th>
                                    <th>Alcanzado</th>
                                    <th>Meta</th>
                                    <th>Avance</th>
                                    <th>Alcanzado</th>
                                    <th>Meta</th>
                                    <th>Avance</th>
                                    <th>Alcanzado</th>
                                </tr><tr>
                                    <td>' . ($meta['mod_trimestre1']) . '</td>
                                    <td>' . ($meta['alc_trimestre1']) . '</td>
                                    <td>' . ($meta['ava1']) . '</td>
                                    <td>' . ($meta['mod_trimestre2']) . '</td>
                                    <td>' . ($meta['alc_trimestre2']) . '</td>
                                    <td>' . ($meta['ava2']) . '</td>
                                    <td>' . ($meta['mod_trimestre3']) . '</td>
                                    <td>' . ($meta['alc_trimestre3']) . '</td>
                                    <td>' . ($meta['ava3']) . '</td>
                                    <td>' . ($meta['mod_trimestre4']) . '</td>
                                    <td>' . ($meta['alc_trimestre4']) . '</td>
                                    <td>' . ($meta['ava4']) . ' %</td>
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

        $this->conectar();//En el IF que tiene 2024 poner 2025-01-01 al enviar a prod
        $this->setSql("
            SELECT IF(NOW() >= '2026-05-01 00:00:00',1,0) AS cierre1,
                IF(NOW() >= '2026-08-01 00:00:00',1,0) AS cierre2,
                IF(NOW() >= '2026-11-01 00:00:00',1,0) AS cierre3,
                IF(NOW() >= '2027-02-01 00:00:00',1,0) AS cierre4,
                IF('2025-12-01 08:00:00'<NOW(),1, 0) apertura1,
                IF('2026-07-01 08:00:00'<NOW(),1, 0) apertura2,
                IF('2026-10-01 08:00:00'<NOW(),1, 0) apertura3,
                IF('2027-01-01 08:00:00'<NOW(),1, 0) apertura4,
                IF('2026-01-01 08:00:00'<=NOW(), IF(NOW()<=fca_trimestre1, 1, 0), 0) trimestre1, est_trimestre1 ,
                IF('2026-07-01 08:00:00'<=NOW(), IF(NOW()<=fca_trimestre2, 1, 0), 0) trimestre2, est_trimestre2 ,
                IF('2026-10-01 08:00:00'<=NOW(), IF(NOW()<=fca_trimestre3, 1, 0), 0) trimestre3, est_trimestre3 ,
                IF('2027-01-01 08:00:00'<=NOW(), IF(NOW()<=fca_trimestre4, 1, 0), 0) trimestre4, est_trimestre4,
                (total1*100)/total porcentaje1,
                (total2*100)/total porcentaje2,
                (total3*100)/total porcentaje3,
                (total4*100)/total porcentaje4,
                IF(((total1*100)/total)=100,1,0) AS concluido1,
                IF(((total2*100)/total)>=100,1,0) AS concluido2,
                IF(((total3*100)/total)>=100,1,0) AS concluido3,
                IF(((total4*100)/total)>=100,1,0) AS concluido4,ur_nombre
            FROM
                (SELECT SUM(contador) total,SUM(cap_trimestre1) total1,SUM(cap_trimestre2)total2,SUM(cap_trimestre3)total3,
                    SUM(cap_trimestre4) total4,est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                    fca_trimestre1, fca_trimestre2, fca_trimestre3, fca_trimestre4,ur_nombre
                FROM (
                    SELECT pii_id,1 contador, gpo_id, ur_id, prg_id,spr_id, py_id, oa_id
                        epg_id, est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                        alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                        mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                        mac_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                        cap_trimestre1,cap_trimestre2,cap_trimestre3,cap_trimestre4,fca_trimestre1,fca_trimestre2,
                        fca_trimestre3,fca_trimestre4
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
                    INNER JOIN (
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_trimestre1,
                        MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_trimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_trimestre3,
                        MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_trimestre4
                        FROM mir_TrimestreUr
                        WHERE nim_id=4
                        GROUP BY gpo_id, ur_id
                    )est USING (gpo_id,ur_id)
                    INNER JOIN (
                        SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id, SUM(pre_vigente) AS vigente
                        FROM presupuesto
                        INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        WHERE gpo_id=:gpo_id AND ur_id=:ur_id
                        GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
                        HAVING vigente >0
                    ) AS temp USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                    WHERE gpo_id=:gpo_id AND ur_id=:ur_id
                    ORDER BY pii_id
                )s
                INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            )A;
        ");

        $params = [
            ':gpo_id' => $this->prm->gpo_id,
            ':ur_id'  => $this->prm->ur_id,
        ];

        $this->consultar($params);
        $verTrimestre2 = $this->extraer();
        $this->desconectar();

        $boton = new stdClass();
        $boton->title = 'Regresar a lista de UR';
        $boton->icon = 'fa-solid fa-circle-left';
        $boton->id = 'regresar1';
        $boton->value = ' regresar';
        $boton->color = 'gris';
        $boton->click = ' comunica("' . $this->pantalla . '"); ';
        $this->agregaTools($this->ponBoton($boton));

        $tri = '<div style="width:100%; padding-left:30%; padding-bottom:50px;">';

        $trimestre = ['Ene-Feb-Mar', 'Abr-May-Jun', 'Jul-Ago-Sep', 'Oct-Nov-Dic'];
        $trimestreActivo = 1;
        $caputraTrimestre = 0;

        $boton = new stdClass();
        for ($x = 0; $x <= 3; $x++) {
            $boton->id = 'cambiaEstado' . $x;
            $tri .= '
                <div class="boton_opcion">
                    <div class="boton_opcion_titulo">
                        <strong>
                            <span style="font-size: 20px;" class="" aria-hidden="true"></span>' . $trimestre[$x] . '
                    </div>
                    <div>';
            if ($verTrimestre2['apertura' . ($x + 1) . ''] == 1) {
                if ($verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'C' || ($verTrimestre2['trimestre' . ($x + 1) . ''] == 0 && $verTrimestre2['est_trimestre' . ($x + 1) . ''] == 'A' && $verTrimestre2['concluido' . ($x + 1) . ''] == 0)) {
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
                                .css("margin","10% 40%")
                                .width("280px")
                                .attr("id","modalCarga")
                                .html(
                                    $("<div/>").addClass("modal-header")
                                    .html( $("<H4/>").addClass("modal-title").html("ABRIR TRIMESTRE") )
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
                                                    $("<span/>").addClass("fa fa-calendar")
                                                )
                                            )
                                        ).datepicker({ "keyboardNavigation":true,"language":"es" })
                                    ).append(
                                        $("<button/>").addClass("btn btn-outline-primary me-1")
                                        .html(
                                            $("<span/>").addClass("fa fa-circle-check")
                                        ).text("Aceptar").click(function(){
                                            comunica( "' . $this->pantalla . '", {
                                                metodo:"abrirTrimestre",
                                                trimestre:' . ($x + 1) . ',
                                                gpo_id:' . $this->prm->gpo_id . ',
                                                ur_id:' . $this->prm->ur_id . ',
                                                fecha:$("#fechaLimite").val()
                                            });
                                            $("#backModal,#modalModal").remove();
                                        })
                                    ).append(
                                        $("<button/>").addClass("btn btn-outline-danger")
                                        .html(
                                            $("<span/>").addClass("fa fa-times")
                                        ).text("Cancelar").click(function(){
                                            $("#backModal,#modalModal").remove();
                                        })
                                    )
                                )
                            )
                        );
                    ';
                    //si la fecha actual es igual a la fecha de cierre no tendrá opción a abrir la captura, esto segun documento de reglas de avance de gestión punto 9 ticket 194387
                    if ($verTrimestre2['cierre' . ($x + 1) . ''] == 0) {
                        $tri .= $this->ponBoton($boton);
                    } else {
                        $tri .= 'Cierre definitivo del módulo';
                    }

                } elseif ($verTrimestre2['trimestre' . ($x + 1) . ''] == 0 && $verTrimestre2['est_trimestre' . ($x + 1) . ''] == 1 && $verTrimestre2['concluido' . ($x + 1) . ''] == 1) {
                    $boton->value = 'Cerrar';
                    $boton->color = 'rojo';
                    $boton->click = ' comunica("' . $this->pantalla . '",{ trimestre: ' . ($x + 1) . ', metodo: "cerrarTrimestre", gpo_id:' . $this->prm->gpo_id . ', ur_id:' . $this->prm->ur_id . ' }); ';
                    $tri .= $this->ponBoton($boton);
                } else {
                    $tri .= 'Bloqueado';
                }

            } else {
                $tri .= 'Bloqueado';
            }

            $tri .= '<div>';
            $tri .= '</strong></div>
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

    private function cerrarTrimestre($agestion = true)
    {
        $this->conectar();
        if (isset($_SESSION[_S]['prm']['A']['10']['2']['2'])) {
            $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
            $this->setSql("SET @mip = INET_ATON('" . $ip . "');");
            $this->consultar();
            $this->setSql('
                UPDATE cat_MetaIndicadorProductoIntermedio
                INNER JOIN enc_Programatico USING(epg_id)
                SET est_trimestre' . $this->prm->trimestre . ' = "C"
                WHERE gpo_id=? AND ur_id=?;
            ');
            $this->consultar([$this->prm->gpo_id,$this->prm->ur_id]);
            $this->desconectar();
        } else {
            $this->mixScript('notify("No tiene permisos para realizar esta acción","error")');
        }
        if ($agestion) {
            $this->gestionUr();
        }
    }

    private function abrirTrimestre()
    {

        if (isset($_SESSION[_S]['prm']['A']['10']['2']['2'])) {

            $this->conectar();
            $this->setSql("
                UPDATE mir_TrimestreUr
                SET etr_id1='A',
                tru_fechaCierre='" . $this->prm->fecha . " 23:59:59'
                WHERE gpo_id=? AND ur_id=?
                AND tri_id= ?
                AND nim_id=4;
            ");

            $params = [
                $this->prm->gpo_id,
                $this->prm->ur_id,
                $this->prm->trimestre,
            ];

            $this->consultar($params);
            $this->desconectar();
        } else {
            $this->mixScript('notify("No tiene permisos para realizar esta acción","error")');
        }

        $this->gestionUr();
    }

    private function exportaReportesMasivos()
    {
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';

        if (!$this->reportemasivo) {
            $this->mixScript('notify("No tiene Permisos para generar reportes masivos","error")');
            return false;
        }

        if (empty($tipo)) {
            $this->mixScript('notify("Seleccione un reporte a generar","error")');
            return false;
        }

        $data = URsConsolidadoAvanceGestion();
        $trimestreActual = $this->trimestreActualReporteMasivo();

        if (!isset($data[0]) || $data[0] == '') {
            $this->mixScript('notify("No cuenta con URs","error")');
            return false;
        }

        $numTotalReportes = count($data);
        $reporte = '';
        $reporteCsv = $titulo = '';

        switch ($tipo) {
            case 'pdf':
                $titulo = 'PDF';
                $reporte = 'PDF';
                break;
            case 'excel':
                $titulo = 'EXCEL Y CSV';
                $reporte = 'Excel';
                $reporteCsv = '  y un total de <b> ' . count($data) . ' reportes CSV</b>';
                break;
        }

        $html = '
            <form id="formAuxiliar" method="post" target="_blank">
            </form>
        ';

        $this->mixScript('
            $(function () {
                var num_reg = ' . count($data) . ';
                if (num_reg > 0 )    {
                    alertify.confirm(
                        "<b> REPORTE CONSOLIDADO AVANCE DE GESTION ' . $titulo . ' MASIVO </b>",
                        "Se <b>generarán</b> un total de <b> ' . count($data) . ' reportes ' . $reporte . '</b>' . $reporteCsv . ', la generación de los reportes durará varios minutos ¿está seguro de continuar?",
                        function(){
                            abrirReporte();
                        },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }else {
                    alertify.alert("REPORTE CONSOLIDADO AVANCE DE GESTION ' . $titulo . ' MASIVO", "No existen URs para generar reportes.").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }
            });

            function abrirReporte() { 
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "rep/excel/poa/reporte/generaReporte.php";
                form.target = "_blank"; // abre en nueva pestaña
                // Parámetros ocultos
                var rep = document.createElement("input");
                rep.type = "hidden";
                rep.name = "tipo";
                rep.value = "' . $tipo . '";
                form.appendChild(rep);
                document.body.appendChild(form);
                var trim = document.createElement("input");
                trim.type = "hidden";
                trim.name = "capturandoTri";
                trim.value = "' . $trimestreActual . '";
                form.appendChild(trim);
                document.body.appendChild(form);
                form.submit();   
            }
        ');

        return false;

    }

    //Método que obtiene y regresa el trimestre actual
    private function trimestreActualReporteMasivo()
    {
        $fechaActual = date('Y-m-d H:i:s');
        $fechaPoa = APP_EJERCICIO_ANTERIOR . '-12-31 23:59:59';
        $mirInicial = 0;
        $fechaCierreTrimestre1 = APP_EJERCICIO . '-04-10 23:59:59';
        $trimestre1 = 1;
        $fechaCierreTrimestre2 = APP_EJERCICIO . '-07-10 23:59:59';
        $trimestre2 = 2;
        $fechaCierreTrimestre3 = APP_EJERCICIO . '-10-01 23:59:59';
        $trimestre3 = 3;
        $fechaCierreTrimestre4 = '2027-01-10 23:59:59';
        $trimestre4 = 4;
        $trimestreActual = 1;

        //Verifica en que trimestre estamos
        if ($fechaActual >= $fechaCierreTrimestre1 && $fechaActual < $fechaCierreTrimestre2) {
            $trimestreActual = $trimestre1;
        }
        if ($fechaActual >= $fechaCierreTrimestre2 && $fechaActual < $fechaCierreTrimestre3) {
            $trimestreActual = $trimestre2;
        }
        if ($fechaActual >= $fechaCierreTrimestre3 && $fechaActual < $fechaCierreTrimestre4) {
            $trimestreActual = $trimestre3;
        }
        if ($fechaActual >= $fechaCierreTrimestre4) {
            $trimestreActual = $trimestre4;
        }

        return $trimestreActual;
    }
}

//fin clase
if (isset($_SESSION[_S]['prm']['A']['10']['2'])) {
    new ControladorApertura();
} else {
    WebResponse::sinPermisos();
}
