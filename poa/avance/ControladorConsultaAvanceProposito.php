<?php

session_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorConsultaAvanceProposito extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        if ($_SESSION[_S]['nvj_id'] > 1) {
            switch ($this->metodo) {
                case 'verMetas':
                    $this->verMetas();
                    break;
                case 'verProposito':
                    $this->verProposito();
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
                case 'tablaUrs':
                    $this->tablaUrs();
                    break;
                case 'llenaTablaUrs':
                    $this->llenaTablaUrs();
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
        $this->tablaUrs();
    }

    private function tablaUrs()
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl-urs';
        $tabla->cabeceraFija = true;
        $tabla->titulo = 'Listado de UR\'s';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaUrs';

        $tabla->desplegable = new stdClass();
        $tabla->desplegable->metodo = 'seleccionaSemestre';
        $tabla->desplegable->titulo = 'Metas';
        $tabla->desplegable->encabezado = 'Ver';
        $tabla->desplegable->enFila = false;
        $tabla->desplegable->prm = ['gpo_id', 'ur_id'];

        $tabla->accion = new stdClass();
        $tabla->accion->prm = ['gpo_id', 'ur_id', 'capturandoTri', 'capturandoTri2'];
        if (isset($_SESSION[_S]['prm']['A']['10']['7']['2'])) {
            $tabla->accion->editar = true;
        }
        $tabla->accion->metodoEditar = 'gestionUr';
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/poa/avance/rep_AvanceProposito.php';
        $tabla->accion->excel = false;
        $tabla->accion->urlExcel = 'rep/excel/ExportaAvance.php';
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
        array_push($tabla->encabezado, $tr);

        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
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

        $this->setSql('
            SELECT gpo_id,ur_id,
            CASE WHEN tru_fechaCierre <=NOW() AND NOW() <= tru_fechaApertura THEN 2
            ELSE 0 END capturandoTri,FORMAT(((total4*100)/total),2) porcentaje4,
            FORMAT(((total4*100)/total),2) porcentaje4,
            FORMAT(IF(mem_modificado=0,0,((mem_alcTrimestre4*100)/mem_modificado)),2) ava4,
            FORMAT(IF(mem_modificado=0,0,((mem_alcanzado*100)/mem_modificado)),2) ava,ur_clave,ur_nombre,
            CASE WHEN ((total4*100)/total) AND etr_id1 = 0  THEN 2
            ELSE 1 END AS capturandoTri2
            FROM(
                SELECT gpo_id,ur_id,tru_fechaApertura,tru_fechaCierre,
                    SUM(contador) total,
                    SUM(IF(mpr_capturaAvance=1,1,0)) total4, SUM(alc_trimestre4) mem_alcTrimestre4,
                    SUM(mpr_alcanzado) mem_alcanzado, SUM(mpr_modificado)mem_modificado,
                    ur_clave,ur_nombre,etr_id1
                FROM (
                    SELECT mpr_id, 1 contador,gpo_id, ur_id, ur_clave,ur_nombre,
                    mpr_alcanzado,mpr_modificado,mprt_alcanzado AS alc_trimestre4,
                    mpr_modificado AS mod_trimestre4, mpr_original,
                    mprt_original AS ori_trimestre4,mpr_capturaAvance,tru_fechaApertura,
                    tru_fechaCierre,etr_id1
                    FROM mir_TrimestreUr m
                    INNER JOIN vin_ProgramasMir USING (gpo_id,ur_id)
                    INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    INNER JOIN vin_ProgramaMirIndicador v USING(vpm_id,nim_id)
                    INNER JOIN cat_MetaProposito USING(pmi_id)
                    INNER JOIN cat_MetaPropositoTrimestre USING (pmi_id,mpr_id)
                    WHERE gpo_id!=0 AND nim_id=2 AND m.tri_id=4
                )a
            GROUP BY gpo_id,ur_id)b
            ORDER BY gpo_id,ur_id;
        ');
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $nav->idTabla = 'tbl-urs';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            formatNumber()
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
            <div class="boton_opcion trim " trimestre=4>
                <div class="boton_opcion_titulo">
                    <strong>
                        <span style="font-size: 20px;" class="" aria-hidden="true"></span>Ene-Dic</div>
                        <div><div>
                    </strong>
                </div>
            </div>
            </div>
            <div style="width:10px; display:table-cell"></div>
        ';

        $this->mixHtml($tri . '</div><div class="row" id="proposito"></div>');
        $this->mixScript('
            $(".trim").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"verProposito",
                    trimestre:$(this).attr("trimestre"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . '
                });
            });
        ');
    }

    private function verProposito()
    {

        $this->setContenedor('proposito');

        $params = [
            'gpo' => $this->prm->gpo_id,
            'ur'  => $this->prm->ur_id,
        ];

        $this->conectar();
        $this->setSql("
            SELECT  gpo_id, ur_id, prg_id,prg_nombre,
                SUM(pre_aprobado) AS aprobado,
                SUM(pre_vigente) AS vigente, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                SUM(pre_ejercido) AS ejercido, metas,
                IF(mem_capturado = metas,'CAPTURADO', IF(ISNULL(porcentaje4),0,FORMAT(porcentaje4,2))) porcentaje4,
                pmi_id,vpm_id
            FROM cat_Programa
            INNER JOIN (
                SELECT COUNT(mpr_id) AS metas,gpo_id,ur_id,prgs_id AS prg_id,pmi_id,vpm_id,
                SUM(IF(mpr_capturaAvance=1,1,0)) mem_capturado,
                ((SUM(IF(mpr_capturaAvance=1,1,0))*100)/COUNT(mpr_id)) porcentaje4
                FROM mir_TrimestreUr m
                INNER JOIN vin_ProgramasMir USING (gpo_id,ur_id)
                INNER JOIN vin_UrProgramaMir USING (vpm_id,gpo_id,ur_id,prgs_id)
                INNER JOIN vin_ProgramaMirIndicador v USING(vpm_id,nim_id)
                INNER JOIN cat_MetaProposito USING(pmi_id)
                INNER JOIN cat_MetaPropositoTrimestre USING (pmi_id,mpr_id)
                WHERE gpo_id = :gpo and ur_id = :ur AND nim_id = 2 and emi_id = 9
                AND m.tri_id=4
                GROUP BY vpm_id,pmi_id
            )meta USING (gpo_id,ur_id,prg_id)
            LEFT JOIN presupuesto USING(gpo_id, ur_id, prg_id)
            LEFT JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            WHERE gpo_id = :gpo and ur_id = :ur
            GROUP BY gpo_id, ur_id, prg_id,pmi_id;
        ");

        $this->consultar($params);
        $this->desconectar();

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql('
            SELECT COUNT(mpr_id) AS metas,gpo_id,ur_id,prgs_id AS prg_id,pmi_id,vpm_id,
            SUM(IF(mpr_capturaAvance=1,1,0)) mem_capturado,
            ((SUM(IF(mpr_capturaAvance=1,1,0))*100)/COUNT(mpr_id)) porcentaje4
            FROM mir_TrimestreUr m
            INNER JOIN vin_ProgramasMir USING (gpo_id,ur_id)
            INNER JOIN vin_UrProgramaMir USING (vpm_id,gpo_id,ur_id,prgs_id)
            INNER JOIN vin_ProgramaMirIndicador v USING(vpm_id,nim_id)
            INNER JOIN cat_MetaProposito USING(pmi_id)
            INNER JOIN cat_MetaPropositoTrimestre USING (pmi_id,mpr_id)
            WHERE gpo_id = :gpo and ur_id = :ur  AND nim_id = 2 and emi_id = 9
                AND m.tri_id=4
            GROUP BY vpm_id,pmi_id
        ');
        $con->consultar($params);

        $htm = '<table  class="table table table-bordered table-hover">';

        $prg = '';
        $cambioClase = true;
        $pmi = '';

        while ($row = $this->extraer()) {
            if ($row['prg_id'] != $prg) {
                $htm .= '
                    <tr>
                        <th class="warning" style="text-align:center" colspan="1" >PR:</th>
                        <th class="warning" style="text-align:center" colspan="1" rowspan="2"><br/>' . $row['prg_id'] . '</th>
                        <th style="text-align:justify" colspan="14" rowspan="2"><br/>' . $row['prg_nombre'] . '</th>
                        <th class="warning" align="center" colspan="2">APROBADO</th>
                        <th class="warning" align="center" colspan="2">MODIFICADO</th>
                        <th class="warning" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="warning" align="center" colspan="2">DEVENGADO</th>
                        <th class="warning" align="center" colspan="2">EJERCIDO</th>
                        <th class="warning" align="center" colspan="2">No. METAS</th>
                        <th class="warning" align="center" colspan="2">ESTATUS</th>
                    </tr>';
                $clase = ($cambioClase) ? 'class="warning"' : '';
                $htm .= '
                    <tr class="warning" >
                        <td ' . $clase . ' align="center" colspan="1">
                            <button type="button" id="verProp" icon="eye-open" title="Ver el Indicador/Meta de Propósito" class="verProp btn btn-outline-secondary" eps_id=' . $row['vpm_id'] . ' eri_id=' . $row['pmi_id'] . '>
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['aprobado'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['vigente'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['comprometido'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['devengado'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['ejercido'] . '</td>
                        <td ' . $clase . ' align="center" colspan="2">' . $row['metas'] . '</td>
                        <td ' . $clase . ' align="center" colspan="2">' . $row['porcentaje' . $this->prm->trimestre . ''] . '</td>
                    </tr>
                    <tr >';
            }
            if ($row['pmi_id'] != $pmi) {
                $htm .= '
                    <tr>
                    <th></th>
                        <th class ="divProposito" id="metas' . $row['vpm_id'] . '" colspan = 30 ></th>
                    </tr>';

                $cambioClase = ($cambioClase) ? false : true;
            }

            $prg = $row['prg_id'];
            $pmi = $row['pmi_id'];
        }

        $htm .= '</table>';

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verProp").click(function(){
                $(".divProposito").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    eps_id:$(this).attr("eps_id"),
                    eri_id:$(this).attr("eri_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . '
                });
            });
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->eps_id . '');

        $this->conectar();

        $this->setSql('
            SELECT  gpo_id, ur_id, ind_id,pmi_id, ind_nombreMIR, ind_definicionMIR,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, umi_id,
                umi_nombre, umv_nombre, umv_id,IF(tiv_id=1,2,tiv_id) tiv_id,
                IF(tiv_id=1,"ACUMULADO",tiv_nombre) tiv_nombre,ses_id,ses_nombre,
                frm_id,frm_nombre,mpr_id, mpr_descripcion,mpr_original,
                mprt_original AS ori_trimestre4, mpr_modificado, mpr_alcanzado,
                ((mpr_alcanzado/mpr_modificado)*100) AS porcentajeMeta
            FROM cat_MetaProposito
                INNER JOIN cat_MetaPropositoTrimestre USING (pmi_id,mpr_id)
                INNER JOIN vin_ProgramaMirIndicador USING(pmi_id)
                INNER JOIN vin_UrProgramaMir USING(vpm_id)
                INNER JOIN cat_Indicador USING(nim_id,ind_id)
                INNER JOIN cat_TipoIndicador USING(tpi_id)
                INNER JOIN cat_Dimension USING(dim_id)
                INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
                INNER JOIN cat_UnidadMedidaVariable v USING(umv_id)
                INNER JOIN cat_TipoValor USING(tiv_id)
                INNER JOIN cat_SentidoEsperado USING(ses_id)
                INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            WHERE gpo_id = :gpo and ur_id = :ur and emi_id = 9
                AND vpm_id = :vpm AND tri_id=4
                GROUP BY gpo_id,ur_id,ind_id,mpr_id,pmi_id
                ORDER BY mpr_id;
        ');
        $params = [
            'gpo' => $this->prm->gpo_id,
            'ur'  => $this->prm->ur_id,
            'vpm' => $this->prm->eps_id,
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
                    </tr>
                    <tr>
                        <td>' . ($ind['tpi_nombre']) . '</td>
                        <td>' . ($ind['dim_nombre']) . '</td>
                        <td>' . ($ind['frm_nombre']) . '</td>
                        <td>' . ($ind['umi_nombre']) . '</td>
                        <td>' . ($ind['tiv_nombre']) . '</td>
                        <td>' . ($ind['ses_nombre']) . '</td>
                    </tr>
            ');
            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql('
                SELECT gpo_id,ur_id,mpr_id, mpr_descripcion,mpr_lb AS mem_anio,
                    CASE WHEN umi_id IN(2,4,5) THEN mpr_lbIndicador ELSE mpr_original END AS mem_valor,
                    mpr_anteriorIndicador AS mem_valorDeseado1,((mpr_alcanzado*100)/mpr_modificado) ava4,
                    mpr_modificado, mpr_alcanzado
                FROM cat_MetaProposito
                    INNER JOIN cat_MetaPropositoTrimestre USING (pmi_id,mpr_id)
                    INNER JOIN vin_ProgramaMirIndicador USING(pmi_id)
                    INNER JOIN vin_UrProgramaMir USING(vpm_id)
                    INNER JOIN cat_Indicador USING(nim_id,ind_id)
                WHERE ind_id = :ind and pmi_id = :pmi
                    and gpo_id = :gpo and ur_id = :ur
                GROUP BY gpo_id,ur_id,mpr_id;
            ');
            $params = [
                'ind' => $ind['ind_id'],
                'pmi' => $ind['pmi_id'],
                'gpo' => $this->prm->gpo_id,
                'ur'  => $this->prm->ur_id,
            ];
            $con->consultar($params);

            while ($meta = $con->extraer()) {
                $this->mixHtml('
                    <tr class="warning">
                        <th></th>
                        <th colspan = 7>
                            <table class="table table-hover table-bordered">
                                <tr>
                                    <th colspan = 12>META ' . $meta['mpr_id'] . '</th>
                                </tr>
                                <tr>
                                    <th>Descripcion:</th><th colspan = 11>' . ($meta['mpr_descripcion']) . '</th>
                                </tr>
                                <tr>
                                    <td>Linea de base:</td><td colspan = 5>' . ($meta['mem_anio'] . ' - ' . $meta['mem_valor']) . '</td>
                                </tr>
                                <tr>
                                    <td>Valor deseado:</td><td colspan = 5> 2019 - ' . ($meta['mem_valorDeseado1']) . ' </td>
                                </tr>
                                <tr>
                                    <th colspan=6>2do Semestre 2019</th>
                                </tr>
                                <tr>
                                    <th colspan=2>Meta</th>
                                    <th colspan=2>Avance</th>
                                    <th colspan=2>Alcanzado</th>
                                </tr>
                                <tr>
                                    <td colspan=2>' . ($meta['mpr_modificado']) . '</td>
                                    <td colspan=2>' . ($meta['mpr_alcanzado']) . '</td>
                                    <td colspan=2>' . ($meta['ava4']) . '</td>
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

    private function obtenerDatosSemestre()
    {
        $this->conectar();
        $this->setSql("
            SELECT  gpo_id, ur_id, IF(tru_fechaCierre <= NOW(),1,0) apertura2,
                IF(tru_fechaCierre <= NOW(), IF(NOW() <= tru_fechaApertura,1,0),0) semestre2,
                IF(estadoSemestre = 'A',1,0) estadoSemestre2, mpr_capturaAvance,(total2*100)/total porcentaje2,
                IF(((total2*100)/total)>=100,1,0) AS concluido2,
                FORMAT(IF(mpr_modificado=0,0,((mpr_alcanzado*100)/mpr_modificado)),2) ava,mpr_modificado,mpr_alcanzado,total
            FROM (
                SELECT  gpo_id, ur_id, MAX(mpr_capturaAvance) mpr_capturaAvance,SUM(mpr_modificado) mpr_modificado,
                SUM(mpr_alcanzado) mpr_alcanzado,
                tru_fechaApertura,tru_fechaCierre,etr_id1 AS estadoSemestre,
                SUM(contador) total, SUM(IF(mpr_capturaAvance=1,1,0))total2
                FROM(
                    SELECT 1 contador, gpo_id, ur_id, mpr_capturaAvance, mpr_modificado, mpr_alcanzado,
                        tru_fechaApertura,tru_fechaCierre, etr_id1
                    FROM mir_TrimestreUr m
                        INNER JOIN vin_ProgramasMir USING (gpo_id,ur_id)
                        INNER JOIN vin_UrProgramaMir USING(vpm_id,gpo_id,ur_id)
                        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                        INNER JOIN vin_ProgramaMirIndicador v USING(vpm_id,nim_id)
                        INNER JOIN cat_MetaProposito USING(pmi_id)
                        INNER JOIN cat_MetaPropositoTrimestre USING (pmi_id,mpr_id)
                    WHERE gpo_id = :gpo AND ur_id = :ur and emi_id = 9 and nim_id = 2
                    AND m.tri_id=4
                )b
                GROUP BY gpo_id,ur_id
            )a;
        ");
        $params = [
            'gpo' => $this->prm->gpo_id,
            'ur'  => $this->prm->ur_id,
        ];
        $this->consultar($params);
        $verTrimestre2 = $this->extraer();
        $this->desconectar();

        return $verTrimestre2;
    }

    private function gestionUr()
    {
        $verTrimestre2 = $this->obtenerDatosSemestre();

        $boton = new stdClass();
        $boton->title = 'Regresar a lista de UR';
        $boton->icon = 'circle-left me-2';
        $boton->id = 'regresar1';
        $boton->value = 'Regresar';
        $boton->color = 'gris';
        $boton->click = ' comunica("' . $this->pantalla . '"); ';
        $this->agregaTools($this->ponBoton($boton));

        $tri = '<div style="width:100%; padding-left:30%; padding-bottom:50px;">';

        $trimestre = ['Ene-Dic'];

        $boton = new stdClass();
        $boton->id = 'cambiaEstado2';
        $tri .= '
            <div class="boton_opcion">
                <div class="boton_opcion_titulo">
                    <strong>
                        <span style="font-size: 20px;" class="" aria-hidden="true"></span>' . $trimestre[0] . '
                </div>
            <div>';
        if ($verTrimestre2['apertura2'] == 1) {
            if ($verTrimestre2['estadoSemestre2'] == 0 || ($verTrimestre2['semestre2'] == 0 && $verTrimestre2['estadoSemestre2'] == 1 && $verTrimestre2['concluido2'] == 0)) {
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
                                            metodo:"abrirSemestre",
                                            semestre:2,
                                            gpo_id:' . $this->prm->gpo_id . ',
                                            ur_id:' . $this->prm->ur_id . ',
                                            fecha:$("#fechaLimite").val()
                                        });
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
            } elseif ($verTrimestre2['semestre2'] == 0 && $verTrimestre2['estadoSemestre2'] == 1 && $verTrimestre2['concluido2'] == 1) {
                $boton->value = 'Cerrar';
                $boton->color = 'rojo';
                $boton->click = ' comunica("' . $this->pantalla . '",{ semestre: 2, metodo: "cerrarSemestre", gpo_id:' . $this->prm->gpo_id . ', ur_id:' . $this->prm->ur_id . ' }); ';
                $tri .= $this->ponBoton($boton);
            } else {
                $tri .= 'Bloqueado';
            }

        } else {
            $tri .= 'Bloqueado';
        }

        $tri .= '<div>';
        $tri .= '
            </strong>
            </div>
            </div>
            </div><div style="width:10px; display:table-cell"></div>
        ';
        $this->mixHtml($tri);

        if (isset($this->prm->capturandoTri)) {

            $this->mixScript('
                $(".printPdf").click(function(){
                    var form = $("<form>")
                    .attr("action", "rep/doc/poa/avance/rep_Avance.php")
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
            $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
            $this->conectar();
            $this->setSql('SET @mip =INET_ATON(:ip);');
            $this->consultar(['ip' => $ip]);
            $this->setSql('
                UPDATE mir_TrimestreUr
                SET etr_id1 = "C"
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id = 2 AND tri_id = :tri;
            ');
            $params = [
                'gpo' => $this->prm->gpo_id,
                'ur'  => $this->prm->ur_id,
                'tri' => ($this->prm->semestre * 2),
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
            $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
            $this->conectar();
            $this->setSql('SET @mip =INET_ATON(:ip);');
            $this->consultar(['ip' => $ip]);
            $this->setSql('
                UPDATE mir_TrimestreUr
                SET tru_fechaCierre = :fecha,
                etr_id1="A"
                WHERE gpo_id = :gpo AND ur_id = :ur
                AND nim_id = 2 AND tri_id = 4;
            ');
            $params = [
                'fecha' => $this->prm->fecha . ' 23:59:59',
                'gpo'   => $this->prm->gpo_id,
                'ur'    => $this->prm->ur_id,
            ];
            $this->consultar($params);
            $this->desconectar();
        } else {
            $this->mixScript('notify("No tiene permisos para realizar esta acción","error");');
        }

        $this->gestionUr();
    }
}

//fin clase
if (isset($_SESSION[_S]['prm']['A']['10']['7'])) {
    $controlador = new ControladorConsultaAvanceProposito();
} else {
    WebResponse::sinPermisos();
}
