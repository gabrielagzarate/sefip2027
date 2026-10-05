<?php
session_start();
set_time_limit(0);
//error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/MensajeSeguimiento.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/HistorialMensajeSeguimiento.php';
require_once dirname(__FILE__) . "/../../srv/ControladorMensajes.php";

class ControladorRevisarActividad extends WebResponse
{
    private $adm_grupo;
    private $adm_ur;
    private $rev_id;

    public function __construct()
    {
        parent::__construct();
        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';

        if ($_SESSION[_S]['nvj_id'] > 1) {
            switch ($this->metodo) {
                case 'verMetas':
                    $this->verMetas();
                    break;
                case 'verActividades':
                    $this->verActividades();
                    break;
                case 'seleccionaTrimestre':
                    $this->seleccionaTrimestre();
                    break;
                case 'Historial':
                    $this->Historial();
                    break;
                case 'HistorialEvidencia':
                    $this->HistorialEvidencia();
                    break;
                case 'upload':
                    $this->upload();
                    break;
                case 'verResponder':
                    $this->verResponder();
                    break;
                case 'enviarRespuesta':
                    $this->enviarRespuesta();
                    break;
                case 'metasObservadas':
                    $this->metasObservadas();
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
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "fa-solid fa-circle-left me-1";
        $btnregresarindex->click = '
            comunica("' . $this->pantalla . '",{
                metodo : "main"
            });
            $("#actividades").hide();
            $("#contenedor").hide();
        ';

        $btnobservaciones = new stdClass();
        $btnobservaciones->id = 'btnobservaciones';
        $btnobservaciones->name = 'btnobservaciones';
        $btnobservaciones->text = 'Observaciones';
        $btnobservaciones->value = 'Observaciones';
        $btnobservaciones->type = 'button';
        $btnobservaciones->color = "rojo";
        $btnobservaciones->icon = "fa fa-list-alt me-1";
        $btnobservaciones->click = '
            comunica("' . $this->pantalla . '",{
                metodo : "seleccionaTrimestre",
                tipo: 2
            });
            $("#clasAdminSeccionCombos").hide();
            $("#contenedor").show();
            document.getElementById("btnobservaciones").style.display = "block";
        ';

        $this->agregaTools($this->ponboton($btnregresarindex));
        $this->agregaTools($this->ponboton($btnobservaciones));

        $boton = new stdClass();
        $boton->icon = 'glyphicon2 glyphicon2-excel';
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

        $clasAdmin = new stdClass();
        $clasAdmin->required = true;
        $clasAdmin->comboPy = false;
        $clasAdmin->comboOa = false;
        $clasAdmin->change = '
            if($("#combosp").val()!= ""){
                comunica("' . $this->pantalla . '", {
                    metodo:"seleccionaTrimestre",
                    tipo:1
                });
            }
        ';

        $this->mixScript('$("#clasAdminSeccionCombos").show();' . ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
    }

    private function seleccionaTrimestre()
    {

        $this->mixScript('$("#clasAdminSeccionCombos").hide();');
        $this->mixScript('$("#contenedor").show();');

        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $unidadR->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $unidadR->select();
        if (isset($this->prm->ur_id) && $this->prm->ur_id != "" && isset($this->prm->pepg_id) && $this->prm->pepg_id != "") {
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $clasAdmin->comboPy = false;
            $clasAdmin->comboOa = false;
            $clasAdmin->change = '
                if($("#combosp").val()!= ""){
                    comunica("' . $this->pantalla . '", {
                        metodo:"seleccionaTrimestre",
                    });
                }
            ';
            ClasificacionAdministrativa::ponEnVariablesEpg($this->prm->pepg_id);
            $_SESSION[_S]['clasAdm-ur'] = $this->prm->ur_id;
            $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
            $unidadR->setGpo_id($this->prm->gpo_id);
            $unidadR->setUr_id($this->prm->ur_id);
            $unidadR->select();
        }
        $comboTipoObra = new stdClass();
        $comboTipoObra->id = 'comboTipoObra';
        $comboTipoObra->opciones = array("" => "Seleccione una opción de la lista", 1 => "GASTO CORRIENTE", 2 => "GASTO DE INVERSIÓN");
        $comboTipoObra->width = '400px';
        $comboTipoObra->label = 'TIPO';

        $tri = '<div><h4><b>' . $unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre() . '</b></h4></div>';
        $tri .= $this->ponCombo($comboTipoObra) . '<br/><br/><div style="width:100%; padding-left:30%; padding-bottom:50px;">';

        $trimestre = array('Ene-Feb-Mar', 'Abr-May-Jun', 'Jul-Ago-Sep', 'Oct-Nov-Dic');

        for ($x = 0; $x <= 3; $x++) {
            $tri .= '
                <div class="boton_opcion trim " trimestre=' . ($x + 1) . '>
                    <div class="boton_opcion_titulo">
                        <strong>
                            <span style="font-size: 20px;" class="" aria-hidden="true"></span>' . $trimestre[$x] . '</div>
                            <div><div>
                        </strong>
                    </div>
                    <div class="center"><br> <img src="./img/metas.png" width="64" height="64" alt=""/><br>
                        <strong><small></small> </strong>
                    </div>
                </div>
                </div>
                <div style="width:10px; display:table-cell"></div>
            ';
        }

        $this->mixHtml($tri . '</div><div class="row" id="actividades"></div>');
        $this->mixScript('
            $(".boton_opcion").click(function() {
                $(this).addClass("active").siblings().removeClass("active");
            });

            if('.$this->prm->tipo.' == 1) {
                $(".trim").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividades",
                        trimestre:$(this).attr("trimestre"),
                        gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id:' . $_SESSION[_S]['clasAdm-ur'] . '
                        ' . ($comboTipoObra != "" ? ', toa_id: $("#comboTipoObra").val()' : '') . '
                    });
                });
                document.getElementById("btnobservaciones").style.display = "none";
            }
            if('.$this->prm->tipo.' == 2) {
                $(".trim").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"metasObservadas",
                        trimestre:$(this).attr("trimestre"),
                        gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id:' . $_SESSION[_S]['clasAdm-ur'] . '
                    });
                });
            }
        ');
    }

    private function verActividades()
    {
        $this->setContenedor('actividades');

        $fecha_limite = '';
        $titulo = '';
        switch ($this->prm->trimestre) {
            case 1:
                $fecha_limite = " AND o.usr_fecha <= '2026-03-31' ";
                $titulo = 'PRIMER TRIMESTRE';
                $presupuesto = "
                    (SUM(apr_ene)+ SUM(apr_feb)+ SUM(apr_mar)) AS aprobado,
                    (SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar)) AS vigente,
                    (SUM(com_ene) + SUM(com_feb) + SUM(com_mar)) AS comprometido,
                    (SUM(dev_ene) + SUM(dev_feb) + SUM(dev_mar)) AS devengado,
                    (SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)) AS ejercido ";
                $porcentajePresupuesto = " round(((100*(SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)))/(SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar))),2) porcentajePresupuesto ";
                break;
            case 2:
                $fecha_limite = " AND o.usr_fecha <= '2026-06-30' ";
                $titulo = 'SEGUNDO TRIMESTRE';
                $presupuesto = "
                    (SUM(apr_abr)+SUM(apr_may)+ SUM(apr_jun)) AS aprobado,
                    (SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun)) AS vigente,
                    (SUM(com_abr) + SUM(com_may) + SUM(com_jun)) AS comprometido,
                    (SUM(dev_abr) + SUM(dev_may) + SUM(dev_jun)) AS devengado,
                    (SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun)) AS ejercido ";
                $porcentajePresupuesto = " round(((100*(SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun))/(SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun)))),2) porcentajePresupuesto ";
                break;
            case 3:
                $fecha_limite = " AND o.usr_fecha <= '2026-09-30' ";
                $titulo = 'TERCER TRIMESTRE';
                $presupuesto = "
                    (SUM(apr_jul)+SUM(apr_ago)+ SUM(apr_sep)) AS aprobado,
                    (SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep)) AS vigente,
                    (SUM(com_jul) + SUM(com_ago) + SUM(com_sep)) AS comprometido,
                    (SUM(dev_jul) + SUM(dev_ago) + SUM(dev_sep)) AS devengado,
                    (SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)) AS ejercido ";
                $porcentajePresupuesto = " round(((100*(SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)))/(SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep))),2) porcentajePresupuesto ";
                break;
            case 4:
                $fecha_limite = " AND o.usr_fecha <= '2026-12-31' ";
                $titulo = 'CUARTO TRIMESTRE';
                $presupuesto = "
                    (SUM(apr_oct) + SUM(apr_nov) + SUM(apr_dic)) AS aprobado,
                    (SUM(vig_oct) + SUM(vig_nov) + SUM(vig_dic)) AS vigente,
                    (SUM(com_oct) + SUM(com_nov) + SUM(com_dic)) AS comprometido,
                    (SUM(dev_oct) + SUM(dev_nov) + SUM(dev_dic )) AS devengado,
                    (SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic)) AS ejercido ";
                $porcentajePresupuesto = " round(((100*(SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic))/(SUM(vig_oct) + SUM(vig_nov) + SUM(vig_dic)))),2) porcentajePresupuesto ";
                break;
        }

        $filtro = '';
        if (($_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['dir_id'] == 7 && $_SESSION[_S]['uni_id'] == 17) && ($_SESSION[_S]['dep_id'] == 44 || $_SESSION[_S]['dep_id'] == 45 || $_SESSION[_S]['dep_id'] == 46)) {
            $filtro = ' and toa_id=1';
        } else {
            if (isset($_SESSION[_S]['clasAdm-ue'])) {
                $filtro .= ' AND ue_id = ' . $_SESSION[_S]['clasAdm-ue'];
            }
            if (isset($_SESSION[_S]['clasAdm-prg'])) {
                $filtro .= ' AND prg_id = ' . $_SESSION[_S]['clasAdm-prg'];
            }
            if (isset($_SESSION[_S]['clasAdm-spr'])) {
                $filtro .= ' AND spr_id = ' . $_SESSION[_S]['clasAdm-spr'];
            }
        }
        /*             * *******************************TOA_ID****************************** */
        $toa_id = (isset($this->prm->toa_id) && $this->prm->toa_id != "" ? ' AND toa_id=' . $this->prm->toa_id : '');

        $this->conectar();

        $this->setSql("
            SELECT
                gpo_id, ur_id, prg_id,spr_id, py_id, oa_id, prg_nombre, spr_nombre, oa_nombre,
                /*SUM(pre_aprobado) AS aprobado,
                SUM(pre_vigente) AS vigente_anual, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                SUM(pre_ejercido) AS ejercido, */
                " . $presupuesto . ",
                epg_id, py_nombre, toa_id, oa_origen,metas,
                pdf_id, meta.pdi_id, ind_id, meta.pii_id, o.usr_fecha,
                -- round(((SUM(pre_devengado)/SUM(pre_vigente))*100),2) porcentajePresupuesto,
                " . $porcentajePresupuesto . ",
                hms_mensaje, eme_id, porcentajeMeta, ind_limInfVer, ind_limInfAmar, ind_limSupRojo,ses_id,
                SUBSTRING(ore_oficioAut,1, LENGTH(ore_oficioAut)-1) AS ore_oficioAut, evi_sol,
                sum_mact_original, sum_mact_modificado
            FROM
                cat_ObraActividad o
                INNER JOIN enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN (
                    SELECT
                        COUNT(DISTINCT m.pii_id) AS metas, epg.gpo_id,epg.ur_id, SUM(mact_original) AS sum_mact_original,
                        SUM(mact_modificado) AS sum_mact_modificado,
                        m.epg_id,MAX(pdi_id)pdi_id,MAX(ci.ind_id)ind_id, MAX(m.pii_id) pii_id,
                        MAX(hms_mensaje) hms_mensaje,  MAX(eme_id) eme_id,
                        ROUND((SUM(mact_alcanzado)/SUM(mact_modificado))*100,2) porcentajeMeta,
                        MAX(ind_limSupAmar)ind_limSupAmar,MAX(ind_limInfVer)ind_limInfVer, MAX(ind_limInfAmar )ind_limInfAmar,
                        MAX(ind_limSupRojo)ind_limSupRojo, MAX(ses_id)ses_id,
                        SUM(IF(hm.ese_id > 1, 1, 0)) AS evi_sol
                    FROM
                        cat_MetaActividad m
                        INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
                        INNER JOIN vin_ProductoIntermedioIndicador pii USING(pii_id)
                        INNER JOIN cat_Indicador ci USING(nim_id,ind_id)
                        INNER JOIN enc_Programatico epg ON m.epg_id = epg.epg_id
                        LEFT JOIN cat_MensajeSeguimiento cms ON epg.epg_id = cms.epg_id
                        LEFT JOIN cat_HistorialMensajeSeguimiento hm ON cms.msj_id = hm.msj_id
                            AND hm.hms_trimestre = " . $this->prm->trimestre . " AND tim_id = 1  AND eme_id IN (2,4,5,6)
                    WHERE mt.tri_id = :trimestre
                    GROUP BY m.epg_id
                ) AS meta USING(gpo_id,ur_id,epg_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                LEFT JOIN presupuesto p USING(gpo_id, ur_id, ue_id, epg_id, prg_id, spr_id, py_id, oa_id)
                LEFT JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN (
                    SELECT por.epg_id, group_concat(distinct ore_oficioAut,'') AS ore_oficioAut
                    FROM poa_OrigenRecurso por
                    GROUP BY por.epg_id
                ) AS of1 USING(epg_id)
            WHERE gpo_id= :gpo_id and ur_id= :ur_id " . $filtro . $toa_id . $fecha_limite . "
            AND ((toa_id=1 AND eoa_id = 4) OR (toa_id=2 AND eoa_id=5))
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
            HAVING aprobado > 0 OR vigente > 0 OR sum_mact_original > 0 OR sum_mact_modificado > 0;
            ");

            $params = [
                ':trimestre' => $this->prm->trimestre,
                ':gpo_id' => $this->prm->gpo_id,
                ':ur_id' => $this->prm->ur_id
            ];

        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() == 0) {
            $titulo = 'NO SE ENCONTRARON REGISTROS CON LOS PARÁMETROS SELECCIONADOS';
        }

        $htm = '
            <legend style="text-align:center">' . $titulo . '</legend><br>
            <table  class="table table table-bordered table-hover">';

        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';
        $cambioClase = true;

        while ($row = $this->extraer()) {
            if (isset($row['eme_id'])) {
                $cambioClase = true;

            } else {
                $cambioClase = false;
            }
            $color = '';
            $color1 = '';
            $estilo_btn_1 = '';
            $estilo_btn_2 = '';
            /* SEMAFORIZACIÓN DE PRESUPUESTO SEGUN EL TRIMESTRE */
            switch ($this->prm->trimestre) {
                case 1:
                    if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] < 49) {
                        $estilo_btn_1 = 'style="background-color: #b20000;"'; // ROjo
                    }
                    if ($row['porcentajePresupuesto'] >= 50 && $row['porcentajePresupuesto'] < 79) {

                        $estilo_btn_1 = 'style="background-color: #f1ee0b;"'; // amarillo
                    }
                    if ($row['porcentajePresupuesto'] >= 80 && $row['porcentajePresupuesto'] <= 100) {
                        $color = 'btn-succes';
                        $estilo_btn_1 = 'style="background-color: #35B200;"'; //verde
                    }
                    break;
                case 2:
                    if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] < 49) {
                        $estilo_btn_1 = 'style="background-color: #b20000;"'; // ROjo
                    }
                    if ($row['porcentajePresupuesto'] >= 50 && $row['porcentajePresupuesto'] < 79) {

                        $estilo_btn_1 = 'style="background-color: #f1ee0b;"'; // amarillo
                    }
                    if ($row['porcentajePresupuesto'] >= 80 && $row['porcentajePresupuesto'] <= 100) {
                        $color = 'btn-succes';
                        $estilo_btn_1 = 'style="background-color: #35B200;"'; //verde
                    }
                    break;
                case 3:
                    if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] < 49) {
                        $estilo_btn_1 = 'style="background-color: #b20000;"'; // ROjo
                    }
                    if ($row['porcentajePresupuesto'] >= 50 && $row['porcentajePresupuesto'] < 79) {

                        $estilo_btn_1 = 'style="background-color: #f1ee0b;"'; // amarillo
                    }
                    if ($row['porcentajePresupuesto'] >= 80 && $row['porcentajePresupuesto'] <= 100) {
                        $color = 'btn-succes';
                        $estilo_btn_1 = 'style="background-color: #35B200;"'; //verde
                    }
                    break;
                case 4:
                    if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] < 49) {
                        $estilo_btn_1 = 'style="background-color: #b20000;"'; // ROjo
                    }
                    if ($row['porcentajePresupuesto'] >= 50 && $row['porcentajePresupuesto'] < 79) {

                        $estilo_btn_1 = 'style="background-color: #f1ee0b;"'; // amarillo
                    }
                    if ($row['porcentajePresupuesto'] >= 80 && $row['porcentajePresupuesto'] <= 100) {
                        $color = 'btn-succes';
                        $estilo_btn_1 = 'style="background-color: #35B200;"'; //verde
                    }
                    break;
            }

            switch ($row['ses_id']) {
                case 1: //1-ASCENDENTE
                    if ($row['porcentajeMeta'] != null) {
                        if ($row['porcentajeMeta'] >= 0 && $row['porcentajeMeta'] <= 59) {
                            $color1 = 'btn-danger'; // Rojo
                        } else if ($row['porcentajeMeta'] >= 60 && $row['porcentajeMeta'] <= 84) {
                            $color1 = 'btn-warning'; // Verde
                        } else if ($row['porcentajeMeta'] >= 85 && $row['porcentajeMeta'] <= 100) {
                            $color1 = 'btn-success';
                            $estilo_btn_2 = 'style="background-color: #35B200;"';
                        } else if ($row['porcentajeMeta'] > 100) {
                            $color1 = 'btn-danger'; // Rojo
                        }
                    } else {
                        $color1 = 'btn-danger'; // Negro
                    }
                    break;
                case 2: //2-DESCENDENTE
                    if ($row['porcentajeMeta'] != null) {
                        if ($row['porcentajeMeta'] <= $row['ind_limInfVer']) {
                            $color1 = 'btn-success';
                            $estilo_btn_2 = 'style="background-color: #35B200;"';
                        } else if (($row['porcentajeMeta'] > $row['ind_limSupAmar']) && ($row['porcentajeMeta'] <= $row['ind_limInfAmar'])) {
                            $color1 = 'btn-warning';
                        } else if ($row['porcentajeMeta'] >= $row['ind_limSupRojo']) {
                            $color1 = 'btn-danger';
                        }
                    } else {
                        $color1 = 'btn-danger';
                    }
                    break;
            }

            if ($row['prg_id'] != $prg) {
                $htm .= '<tr style="background-color:#9CCC65;">
                            <th class="bg-encabezado" style="text-align:center" colspan="1">PR:</th>
                            <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                            <th style="text-align:left" colspan="10">' . $row['prg_nombre'] . '</th>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $htm .= '<tr style="background-color:#9CCC65;">
                            <th class="bg-encabezado" style="text-align:center" colspan="1">SPR:</th>
                            <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                            <td align="left" colspan="15">' . $row['spr_nombre'] . '</td>
                            <th style="background-color: #5ED800; text-align:center;" style="text-align:center" align="left" colspan="2"><label>SEMAFORIZACIÓN</label></th>
                            <th class="default" style="background-color: #F300E3; text-align:center" align="left" colspan="1"><label>EVIDENCIAS</label></th>
                            <th class="info" style="background-color: #1C61EC; text-align:center" align="left" colspan="3"><label>OBSERVACIONES</label></th>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py) {
                $htm .= '<tr style="background-color:#9CCC65;">
                            <th class="bg-encabezado" style="text-align:center" colspan="1">PYT:</th>
                            <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['py_id'] . '</th>
                            <td align="left" colspan="5">' . $row['py_nombre'] . '</td>
                            <th class="bg-encabezado" align="center" colspan="2">APROBADO</th>
                            <th class="bg-encabezado" align="center" colspan="2">MODIFICADO</th>
                            <th class="bg-encabezado" align="center" colspan="2">COMPROMETIDO</th>
                            <th class="bg-encabezado" align="center" colspan="2">DEVENGADO</th>
                            <th class="bg-encabezado" align="center" colspan="2">EJERCIDO</th>
                            <th class="bg-encabezado" align="center" colspan="1">PRESUPUESTO</th>
                            <th class="bg-encabezado" align="center" colspan="1">METAS</th>
                            <th class="bg-encabezado" align="center" colspan="1">SOLICITADAS</th>
                            <th class="bg-encabezado" align="center" colspan="1">VER</th>
                            <th class="bg-encabezado" align="center" colspan="1">OBSERVAR</th>
                            <th class="bg-encabezado" align="center" colspan="1">ESTATUS</th>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py || $row['oa_id'] != $oa) {
                $clase = $cambioClase ? 'class="bg-conobservacion" style="background-color:#C39BD3;"' : 'class="bg-sinobservacion" style="background-color:#9CCC65;"';
                $condicionEstatus = ($row['eme_id'] != "" ? "<b style='color: #FF1E2D;'>CON OBSERVACIÓN</b>" : "<b style='color: #00950A;'>SIN OBSERVACIÓN</b>");
                $evidencia_solicitada = $row['evi_sol'] ? '<i class="fa fa-circle-check text-danger" aria-hidden="true"></i>' : '<i class="fa fa-times text-primary" aria-hidden="true"
                            id="icon_' . $row['gpo_id'] . '-' . $row['ur_id'] . '-' . $row['prg_id'] . '-' . $row['spr_id'] . '-' . $row['py_id'] . '-' . $row['oa_id'] . '"></i>';
                $htm .= '
                    <tr ' . $clase . ' >
                        <td ' . $clase . ' align="center" colspan="1"></td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                        <td ' . $clase . ' align="left" colspan="2">' . $row['oa_nombre'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['aprobado'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['vigente'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['comprometido'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['devengado'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['ejercido'], 2) . '</td>
                        <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small ' . $color . '" title="Porcentaje de PRESUPUESTO ' . $row['porcentajePresupuesto'] . ' %" ' . $estilo_btn_1 . '></button></td>
                        <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small ' . $color1 . '" title="Porcentaje de la META ' . ($row['porcentajeMeta'] != null ? $row['porcentajeMeta'] : '0.00') . ' %" ' . $estilo_btn_2 . '></button>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >
                            <label style="font-size: 21px;">
                                ' . $evidencia_solicitada . '
                            </label>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >
                            <button type="button" id="verActividad" icon="glyphicon-eye-open" title="Ver detalle de la meta" class="verActividad btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >' . $condicionEstatus . '</td>
                        <td ' . $clase . ' align="center" colspan="1" >
                            ' . ($row['eme_id'] != "" ? '
                                <button type="button" id="verHistorial" icon="glyphicon-th-list" title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                                    <i class="fa fa-list-alt"></i>
                                </button>
                            ' : "") . '
                        </td>
                    </tr>
                    <tr class="bg-sinobservacion" style="background-color:#9CCC65;">
                        <th></th>
                        <th class ="divMetas" id="metas' . $row['epg_id'] . '" colspan = "20" ></th>
                    </tr>';
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
            $("#myModal").modal("hide");
            $(".verActividad").click(function(){
                $(".divMetas").html("");

                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    epg_id:$(this).attr("epg_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . ',
                    trimestre:' . $this->prm->trimestre . '
                });
            });

            $(".verHistorial").click(function(){
                $(".divMetas").html("");

                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    epg_id:$(this).attr("epg_id"),
                    trimestre:' . $this->prm->trimestre . '
                });
            });

            $(".verHistorialEvi").click(function(){
                $(".divMetas").html("");

                comunica("' . $this->pantalla . '",{
                    metodo:"HistorialEvidencia",
                    epg_id:$(this).attr("epg_id"),
                    hms_id:$(this).attr("hms_id"),
                    trimestre:' . $this->prm->trimestre . '
                });
            });
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->epg_id . '');

        $btnEnviar = new stdClass();
        $btnEnviar->id = 'btnEnviar';
        $btnEnviar->type = 'button';
        $btnEnviar->title = 'Enviar observación';
        $btnEnviar->color = "gris";
        $btnEnviar->icon = "send";
        $btnEnviar->attr = new stdClass();

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'btnEliminar';
        $btnEliminar->type = 'button';
        $btnEliminar->title = 'Eliminar ??';
        $btnEliminar->color = "gris ";
        $btnEliminar->icon = "trash";

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 500;
        $descripcion->width = '300px';
        $descripcion->height = '100px';

        $this->conectar();
        $this->setSql('
            SELECT
                gpo_id, ur_id, prg_id, spr_id, py_id, oa_id,nim_id,ind_id, ind_nombreMIR, ind_definicionMIR,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, u.umi_id AS umi_id,
                u.umi_nombre AS umi_nombre, u.umi_nombre AS umiMir_nombre,
                IF(tiv_id=1,2,tiv_id) tiv_id, IF(tiv_id=1,"ACUMULADO",tiv_nombre) tiv_nombre,ses_id,ses_nombre, frm_id,frm_nombre,
                m.pii_id, m.mac_descripcion, pdi_id, epg_id, m.mac_agrupadorPresupuestal,
                mac_original, IF(mt.tri_id = 1, mt.mact_original, NULL) ori_trimestre1,  IF(mt.tri_id = 2, mt.mact_original, NULL) ori_trimestre2,
                IF(mt.tri_id = 3, mt.mact_original, NULL) ori_trimestre3, IF(mt.tri_id = 4, mt.mact_original, NULL) ori_trimestre4,
                mac_modificado, mac_alcanzado, toa_id
            FROM
                cat_MetaActividad m
                INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
                INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                INNER JOIN enc_Programatico USING(epg_id)
                INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                INNER JOIN cat_Indicador i USING(nim_id,ind_id)
                INNER JOIN cat_TipoIndicador USING(tpi_id)
                INNER JOIN cat_Dimension USING(dim_id)
                INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
                INNER JOIN cat_TipoValor USING(tiv_id)
                INNER JOIN cat_SentidoEsperado USING(ses_id)
                INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            WHERE gpo_id = ? and ur_id= ? and epg_id= ?
            AND ((toa_id=1 AND eoa_id=4) OR (toa_id=2 AND eoa_id=5)) AND tri_id = ?
            GROUP BY gpo_id,ur_id,pii_id,ind_id
            ORDER BY m.pii_id;
        ');

        $params = [
            $this->prm->gpo_id,
            $this->prm->ur_id,
            $this->prm->epg_id,
            $this->prm->trimestre
        ];

        $this->consultar($params);

        while ($ind = $this->extraer()) {
            $this->mixHtml('
                <table class="bg-sinobservacion" >
                    <tr class="bg-sinobservacion"><th colspan=19>INDICADOR ' . ($ind['ind_id']) . '</th></tr>
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
            $con->setSql("
                SELECT gpo_id,ur_id,pii_id,mac_descripcion,mac_anio,mac_valor,
                    ((SUM(IF(mt.tri_id = 1, mt.mact_alcanzado, 0))*100)/mac_modificado) ava1,
                    ((SUM(IF(mt.tri_id = 2, mt.mact_alcanzado, 0))*100)/mac_modificado) ava2,
                    ((SUM(IF(mt.tri_id = 3, mt.mact_alcanzado, 0))*100)/mac_modificado) ava3,
                    ((SUM(IF(mt.tri_id = 4, mt.mact_alcanzado, 0))*100)/mac_modificado) ava4,
                    SUM(IF(mt.tri_id = 1, mt.mact_modificado, NULL)) mod_trimestre1,
                    SUM(IF(mt.tri_id = 2, mt.mact_modificado, NULL)) mod_trimestre2,
                    SUM(IF(mt.tri_id = 3, mt.mact_modificado, NULL)) mod_trimestre3,
                    SUM(IF(mt.tri_id = 4, mt.mact_modificado, NULL)) mod_trimestre4,
                    SUM(IF(mt.tri_id = 1, mt.mact_alcanzado, NULL)) alc_trimestre1,
                    SUM(IF(mt.tri_id = 2, mt.mact_alcanzado, NULL)) alc_trimestre2,
                    SUM(IF(mt.tri_id = 3, mt.mact_alcanzado, NULL)) alc_trimestre3,
                    SUM(IF(mt.tri_id = 4, mt.mact_alcanzado, NULL)) alc_trimestre4,
                    SUM(IF(mt.tri_id = 1, mt.mact_original, NULL)) ori_trimestre1,
                    SUM(IF(mt.tri_id = 2, mt.mact_original, NULL)) ori_trimestre2,
                    SUM(IF(mt.tri_id = 3, mt.mact_original, NULL)) ori_trimestre3,
                    SUM(IF(mt.tri_id = 4, mt.mact_original, NULL)) ori_trimestre4,
                    mac_original, mac_modificado, mac_alcanzado,
                    ROUND(((mac_alcanzado/mac_modificado)*100),2) AS porcentajeMeta
                FROM
                    cat_MetaActividad m
                    INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
                    INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                    INNER JOIN enc_Programatico USING(epg_id)
                WHERE ind_id=? and epg_id=?
                GROUP BY gpo_id,ur_id,m.pii_id;
            ");

            $con->consultar([$ind['ind_id'],$this->prm->epg_id]);
            while ($meta = $con->extraer()) {
                $btnEnviar->attr->mac_id = $meta['pii_id'];

                $this->mixHtml('
                    <tr class="bg-sinobservacion">
                        <th></th><th colspan = "12">
                            <table class="table table-hover table-bordered">
                                <tr>
                                    <th colspan ="12">META ' . $meta['pii_id'] . '</th>
                                </tr><tr>
                                    <th>Descripcion:</th>
                                    <th colspan ="12">' . ($meta['mac_descripcion']) . '</th>
                                </tr><tr>
                                    <td>Unidad de Medida:</td>
                                    <td colspan ="12">' . $ind['umi_nombre'] . '</td>
                                </tr><tr>
                                    <th colspan="4" align=center>AVANCE DE METAS</th>
                                </tr><tr>
                                    <th>TRIMESTRE</th>
                                    <th>PROGRAMADA</th>
                                    <th>ACTUALIZADA</th>
                                    <th>ALCANZADA</th>
                                    <th>%</th>
                                </tr><tr>
                                </tr><tr>
                                    <th>Primero</th>
                                    <td>' . (number_format($meta['ori_trimestre1'], 2)) . '</td>
                                    <td>' . (number_format($meta['mod_trimestre1'], 2)) . '</td>
                                    <td>' . (number_format($meta['alc_trimestre1'], 2)) . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . number_format($meta['ava1'], 2) . '</button></td>
                                </tr><tr>
                                    <th>Segundo</th>
                                    <td>' . (number_format($meta['ori_trimestre2'], 2)) . '</td>
                                    <td>' . (number_format($meta['mod_trimestre2'], 2)) . '</td>
                                    <td>' . (number_format($meta['alc_trimestre2'], 2)) . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . number_format($meta['ava2'], 2) . '</button></td>
                                </tr><tr>
                                    <th>Tercero</th>
                                    <td>' . (number_format($meta['ori_trimestre3'], 2)) . '</td>
                                    <td>' . (number_format($meta['mod_trimestre3'], 2)) . '</td>
                                    <td>' . (number_format($meta['alc_trimestre3'], 2)) . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . number_format($meta['ava3'], 2) . '</button></td>
                                </tr><tr>
                                    <th>Cuarto</th>
                                    <td>' . (number_format($meta['ori_trimestre4'], 2)) . '</td>
                                    <td>' . (number_format($meta['mod_trimestre4'], 2)) . '</td>
                                    <td>' . (number_format($meta['alc_trimestre4'], 2)) . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . number_format($meta['ava4'], 2) . '</button></td>
                                </tr><tr>
                                    <th>Acumulado</th>
                                    <td>' . (number_format($meta['mac_original'], 2)) . '</td>
                                    <td>' . (number_format($meta['mac_modificado'], 2)) . '</td>
                                    <td>' . (number_format($meta['mac_alcanzado'], 2)) . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . number_format($meta['porcentajeMeta'], 2) . '</button></td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                ');
            }
            $con->desconectar();
            $this->mixHtml('</table>');
        }

        $this->mixScript('
            $("#btnEnviar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"Observacion",
                    mensaje:$("#descripcion").val(),
                    mem_id:$(this).attr("mem_id"),
                    trimestre:' . $this->prm->trimestre . ',
                    epg_id:"' . $this->prm->epg_id . '",
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . '
                });
            });
        ');
    }

    private function Historial()
    {

        $this->setContenedor('divContenedorGeneralModal');

        $trimestreSeleccionado = $this->prm->trimestre;
        $this->ActualizaVisto();

        switch ($trimestreSeleccionado) {
            case 1:
                $fechas = " AND hm.hms_trimestre=1";
                $titulo = " PRIMER TRIMESTRE";
                break;
            case 2:
                $fechas = " AND hm.hms_trimestre=2";
                $titulo = " SEGUNDO TRIMESTRE";
                break;
            case 3:
                $fechas = " AND hm.hms_trimestre=3";
                $titulo = " TERCER TRIMESTRE";
                break;
            case 4:
                $fechas = " AND hm.hms_trimestre=4";
                $titulo = " CUARTO TRIMESTRE";
                break;
        }

        $this->conectar();
        $this->setSql('
            SELECT hms_mensaje, usr_nombre, DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha, TIME(hm.usr_fecha) hora,hm.usr_fecha,
                if(ssc_id =3 and dir_id=7 and uni_id=17 and (dep_id=44 or dep_id=45 or dep_id=46), "PRESUPUESTARIO", "DE METAS") tipoSeguimiento,
                eme_id,eme_nombre, msj_id, eme_id, hms_mensajeRespuesta, hms_FechaRespuesta,ese_id,ese_nombre,epg_id,hms_id
            FROM vin_ProductoIntermedioIndicador
            LEFT JOIN cat_MensajeSeguimiento using(pii_id)
            LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
            LEFT JOIN cat_EstadoMensajeSeguimiento USING(eme_id)
            LEFT JOIN cat_EstadoSolicitudEvidencia USING(ese_id)
            INNER JOIN cat_Usuario u  on hm.usr_id = u.usr_id
            INNER JOIN ctrlUsuario.cat_Usuario us ON us.usr_id=u.usr_id
            INNER JOIN enc_JerarquiaSefin USING(ejs_id)
            WHERE epg_id= ? ' . $fechas . '
            AND hm.tim_id=1
            AND (eme_id=2 OR eme_id=4 OR eme_id=5 OR eme_id=6 )
            ORDER BY hm.usr_fecha DESC;
        ');

        $this->consultar([$this->prm->epg_id]);
        $this->desconectar();

        $html = '
            <table class="table table-hover table-bordered" >
                <tr>
                    <th class="bg-conobservacion" style="text-align:center" align="left" colspan="3">SEFIN</th>
                    <th class="default" style="background-color: #F300E3; text-align:center" align="left" colspan="2"><label>SUBIR EVIDENCIAS</label></th>
                    <th class="info" style="background-color: #1C61EC; text-align:center" align="left" colspan="4"><label>RESPONDER OBSERVACION</label></th>
                </tr>
                    <th align="center">MENSAJE</th>
                    <th width="140px">USUARIO</th>
                    <th width="140px">FECHA</th>
                    <th width="70px" colspan="1">ESTATUS</th>
                    <th width="70px" colspan="1">OPCION</th>
                    <th align="center">MENSAJE</th>
                    <th width="140px">FECHA</th>
                    <th width="95px">ESTADO</th>
                    <th width="90px">RESPONDER</th>
                </tr><tr> ';

        while ($row = $this->extraer()) {

            $estado = $row['eme_nombre'];
            if ($row['eme_id'] == 2) {
                $disabled = "";
            } elseif ($row['eme_id'] == 5) {
                $disabled = "";
            } elseif ($row['eme_id'] == 4) {
                $disabled = "disabled";
            } elseif ($row['eme_id'] == 6) {
                $disabled = "disabled";
            } else {
                $disabled = "";
            }
            if ($_SESSION[_S]['nvj_id'] > 2) {
                $disabled = "";
            }
            //deshabilitar cuando no soliciten evidencias
            if ($row['ese_id'] == 1) {
                $disabledEvi = "disabled";
            } elseif ($row['ese_id'] > 1) {
                $disabledEvi = "";
            }

            if ($row['tipoSeguimiento'] == "PRESUPUESTARIO") {
                $color = 'btn-test';
            } else {
                $color = 'btn-info';
            }

            $html .= '
                <td>' . $row['hms_mensaje'] . '</td>
                <td>' . $row['usr_nombre'] . '</td>
                <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                <td>' . $row['ese_nombre'] . '</td>
                <td>' . ($row['ese_nombre'] != "" ? '
                    <button type="button" id="verHistorialEvi" icon="glyphicon-th-list" title="Opción para ver evidencia mensaje" class="verHistorialEvi btn btn-outline-secondary" epg_id=' . $row['epg_id'] . ' hms_id=' . $row['hms_id'] . ' ' . $disabledEvi . ' >
                        <i class="fa fa-list-alt"></i>
                    </button>
                ' : "") . '</td>
                <td>' . $row['hms_mensajeRespuesta'] . '</td>
                <td>' . $row['hms_FechaRespuesta'] . '</td>
                <td>' . $estado . '</td>
                <td  align="center" colspan="1" >
                    ' . ($row['hms_mensaje'] != "" ? '
                        <button type="button" id="solventacion" icon="glyphicon-th-list" title="Opción para responder mensaje" class="solventacion btn btn-outline-secondary" msj_id=' . $row['msj_id'] . ' ' . $disabled . '>
                            <i class="fa fa-list-alt"></i>
                        </button>
                    ' : "") . '
                </td>
                </tr>
                <tr class="bg-conobservacion">
                        <th class ="divResponder" id="responder' . $row['msj_id'] . '" colspan = "8" >
                        </th>
                </tr>
            ';
        }
        $html .= '<table>';

        $this->mixHtml('
            <div id="myModal" class="modal" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">REVISAR AVANCE DE ACTIVIDADES.-' . $titulo . '.-HISTORIAL</h4>
                            <button  id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $html . '</div>
                            <div class="modal-footer">
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixScript('
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });

            $(".solventacion").click(function(){
                $(".divResponder").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verResponder",
                    msj_id:$(this).attr("msj_id"),
                    epg_id:"' . $this->prm->epg_id . '",
                    trimestre:' . $this->prm->trimestre . '
                });
            });

            $(".verHistorialEvi").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"HistorialEvidencia",
                    epg_id:$(this).attr("epg_id"),
                    hms_id:$(this).attr("hms_id"),
                    trimestre:' . $this->prm->trimestre . '
                });
            });
            $("#myModal").modal().show();
            $("#close").click(function(){ $("#myModal").remove(); });
        ');
        //recarga función de mensajes
        $this->mixScript(ControladorMensajes::consultaMensajes());
    }

    //Este metodo muestra el historial de evifencias
    private function HistorialEvidencia()
    {

        $this->setContenedor('divContenedorGeneralModal');

        $trimestreSeleccionado = $this->prm->trimestre;
        $this->ActualizaVisto();

        switch ($trimestreSeleccionado) {
            case 1:
                $fechas = " AND hm.hms_trimestre=1";
                $titulo = " PRIMER TRIMESTRE";
                break;
            case 2:
                $fechas = " AND hm.hms_trimestre=2";
                $titulo = " SEGUNDO TRIMESTRE";
                break;
            case 3:
                $fechas = " AND hm.hms_trimestre=3";
                $titulo = " TERCER TRIMESTRE";
                break;
            case 4:
                $fechas = " AND hm.hms_trimestre=4";
                $titulo = " CUARTO TRIMESTRE";
                break;
        }

        $this->conectar();
        $this->setSql('
            SELECT hms_mensaje, usr_nombre, DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha, TIME(hm.usr_fecha) hora,
                if(ssc_id =3 and dir_id=7 and uni_id=17 and (dep_id=44 or dep_id=45 or dep_id=46), "PRESUPUESTARIO", "DE METAS") tipoSeguimiento,hms_fechaSolicitudEvidencia as fechaevi,
                ese_nombre, msj_id, hms_mensajeRespuesta, hms_FechaRespuesta,ifnull(hms_id,0) as hms_id,ese_id
            FROM vin_ProductoIntermedioIndicador
            LEFT JOIN cat_MensajeSeguimiento using(pii_id)
            LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
            LEFT JOIN cat_EstadoSolicitudEvidencia USING(ese_id)
            INNER JOIN (
                SELECT usr_id, usr_nombre,ssc_id,dir_id,uni_id,dep_id
                FROM cat_Usuario
                INNER JOIN ctrlUsuario.cat_Usuario USING(usr_id)
                INNER JOIN enc_JerarquiaSefin USING (ejs_id)
            ) u  on hm.usr_id = u.usr_id
            WHERE epg_id=? ' . $fechas . '
            AND hms_id=?;
        ');
        $this->consultar([$this->prm->epg_id,$this->prm->hms_id]);
        $this->desconectar();

        $html = '
            <table class="table table-hover table-bordered" >
                <tr>
                    <th class="bg-conobservacion" style="text-align:center" align="left" colspan="3">SEFIN</th>
                    <th class="bg-conobservacion" style="text-align:center" align="left" colspan="2">RESPUESTA</th>
                    <th class="bg-conobservacion" style="text-align:center" align="left" colspan="2">EVIDENCIAS</th>
                </tr>
                <th align="center">MENSAJE</th>
                    <th width="140px">USUARIO</th>
                    <th width="140px">FECHA</th>
                    <th width="140px">MENSAJE</th>
                    <th width="140px">FECHA</th>
                    <th width="95px">ESTADO</th>
                    <th width="140px">FECHA</th>
                </tr><tr> ';

        while ($row = $this->extraer()) {
            //variable de eviid
            $this->prm->hmsid = $row['hms_id'];

            if ($row['tipoSeguimiento'] == "PRESUPUESTARIO") {
                $color = 'btn-test';
            } else {
                $color = 'btn-info';
            }

            $html .= '
                <td>' . $row['hms_mensaje'] . '</td>
                    <td>' . $row['usr_nombre'] . '</td>
                    <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                    <td>' . $row['hms_mensajeRespuesta'] . '</td>
                    <td>' . $row['hms_FechaRespuesta'] . '</td>
                    <td>' . $row['ese_nombre'] . '</td>
                    <td>' . $row['fechaevi'] . '</td>
                </tr>
                <tr class="success">
                        <th class ="divResponder" id="responder' . $row['msj_id'] . '" colspan = "7" >
                        </th>
                </tr>
                ' . ($row['ese_id'] == 2 || $row['ese_id'] == 4 ? '
                    <tr>
                        <td class ="divAddEvi" colspan="7">
                            <div class="row" id="dropzone">
                                <fieldset  class="col-md-6 col-xs-6" style="min-height:80px;">
                                    <div style="width:100%;">
                                        Suba sus archivos uno a uno
                                        <input type="file" name ="evidenciasFile" id="nombreArchObj"  data-url="poa/seguimiento/ControladorRevisarActividad.php"	 data-form-data=\'{"metodo":"upload","origen":"input","hmsid":' . $row['hms_id'] . '}\' >
                                        <div id="progressObj" class="progress progress-striped">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                                <span class="sr-only">100% Complete</span>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset  class="col-md-6 col-xs-6" style="min-height:80px; max-height:100px; overflow:auto; "><div style="width:100%;" id="listaArchivos"></div></fieldset>
                            </div>
                            <!--<br/><legend> o Bien</legend>
                            <div id="dragndrop" style=""><div style="text-align:center;"><span style="font-size:170%"> Arrastre sus archivos aqui</span></div>
                                <div class="statusbar2 odd">
                                    <div class="infofile filename">Nombre del Archivo</div>
                                    <div class=" infofile filesize">Tamaño</div>
                                    <div class="infofile  ">Tipo</div>
                                    <div class="infofile bar">Progreso</div>
                                    <div class="infofile abort ">Borrar Archivo</div>
                                </div>
                            </div>
                            -->
                        </td>
                    </tr>
                ' : "") . '
            ';
            if ($row['ese_id'] == 3) {
                $this->conectar();
                $this->setSql('
                    SELECT rev_nombreOriginal,rev_nombreGuardado,rev_ruta,tar_nombre,rev_fechaAltaEvidencia
                    FROM vin_HistorialRespaldo
                    LEFT JOIN cat_RespaldoEvidencia using(rev_id)
                    INNER JOIN cat_TipoArchivo USING(tar_id)
                    WHERE hms_id=?;
                ');
                $this->consultar([$row['hms_id']]);
                $icono = "";
                $html .= '
                    <tr class="bg-conobservacion"><th colspan=7><center>Archivos anexados</center></th></tr>
                        <tr class="bg-conobservacion">
                            <th colspan="2"></th>
                            <th>Nombre Archivo</th>
                            <th>Fecha en que se subio</th>
                            <th colspan="3"></th>
                        </tr>';
                while ($evidencias = $this->extraer()) {
                    switch (strtoupper($evidencias['tar_nombre'])) {
                        case "DOC":case "DOCX":$icono = 'word';
                            break;
                        case "XLSX":case "XLS":$icono = 'excel';
                            break;
                        case "PDF":$icono = 'PDF';
                            break;
                        case "JPG":$icono = 'jpg';
                            break;
                        case "PNG":$icono = 'png';
                            break;
                        default:$icono = 'verboten';
                            break;
                    }
                    $tmp = '<div class=\"statusbar2\"><div class=\"infofile filename\">' . $evidencias['rev_nombreOriginal'] . '</div><div class=\" infofile filesize\">descarga</div><div class=\"infofile \"><img src=\"img/24pxIcon/' . $icono . '.png\"/></div></div>';
                    $this->mixScript('	$("#listaEvidencias").append("' . $tmp . '");');
                    $html .= '
                        <tr class="bg-sinobservacion">
                            <td colspan="2"></td>
                            <td>
                                <a href="' . $evidencias['rev_ruta'] . '" target="_blank">
                                    <img src="img/24pxIcon/' . $icono . '.png"/> ' . $evidencias['rev_nombreOriginal'] . '
                                </a>
                            </td>
                            <td>' . $evidencias['rev_fechaAltaEvidencia'] . '</td>
                            <th colspan="3"></th>
                        </tr>';
                }
                $this->desconectar();
            }
        }
        $html .= '<table>';

        $this->mixHtml('
            <div id="myModal" class="modal " role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">REVISAR AVANCE DE ACTIVIDADES.-' . $titulo . '.-HISTORIAL</h4>
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
        ');
        $this->mixScript('
            $("#myModal").modal();
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });

            $(".solventacion").click(function(){
                $(".divResponder").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verAddEvi",
                    hms_id:$(this).attr("hms_id"),
                });
            });
            $(".cerrar").click(function(){ $("#myModal").remove(); });
            $("#myModal").modal().show();
        ');
        /* Marcamos la evidencia como vista por la UR */
        $this->conectar();
        $this->setSql('
            UPDATE
                cat_HistorialMensajeSeguimiento
            SET ese_id=4
            WHERE hms_id=? AND ese_id = 2;');
        $this->consultar([$this->prm->hms_id]);
        $this->desconectar();
        /*             * ******************************************* */
        //script para subir los archivos
        $this->mixScript('
            $("#nombreArchObj").fileupload({
                add: function(e, data) {
                    var acceptFileTypes = /^image\/(gif|jpe?g|png|PDF|xls|xlsx|doc|docx)$/i;
                    var tipos =["DOC","DOCX","XLS","XLSX","JPG","PNG","GIF","PDF","XML"]
                    if(data.originalFiles[0][\'size\'] > 20971500) {
                        $("#listaArchivos").append(\'El archivo \'+ data.originalFiles[0][\'name\']+\' es muy grande<br>\');
                        return false;
                    }
                    data.submit();
                },
                dropZone: $("#dropzone"),
                autoUpload:false,
                dataType:"json",
                done:function(e, data){
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    $("#progressObj .progress-bar").css("width", progress + "%" ).attr("aria-valuenow",progress);
                },
                success:function(data){
                    $("#progressObj .progress-bar").css("width","0%" );
                    comunicaCallback(data.comunica);
                }
            });

            var obj = $("#dragndrop");
            obj.on("dragenter", function (e){
                e.stopPropagation();
                e.preventDefault();
                $(this).css("border", "2px solid #AE8B1C");
            });
            obj.on("dragover", function (e)	{
                e.stopPropagation();
                e.preventDefault();
            });
            obj.on("drop", function (e){
                $(this).css("border", "2px dotted #AE8B1C");
                e.preventDefault();
                var files = e.originalEvent.dataTransfer.files;
                console.log(files);
                if (files.length>15){
                    notify("Intenta con una cantidad menor  a  15 archivos","warning");
                    return false;
                }
                handleFileUpload(files,obj);
            });
            /** PREVENIR QUE EL ARCHIVO QUE SE SUELTA FUERA DEL DIV NO SE CARGUE EN EL EXPLORADOR  */
            $(document).on("dragenter", function (e){
                e.stopPropagation();
                e.preventDefault();
            });
            $(document).on("dragover", function (e) {
                e.stopPropagation();
                e.preventDefault();
                obj.css("border", "2px dotted #AE8B1C");
            });
            $(document).on("drop", function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
            /** PREVENIR QUE EL ARCHIVO QUE SE SUELTA FUERA DEL DIV NO SE CARGUE EN EL EXPLORADOR  */
            function handleFileUpload(files,obj){
                var tipos =["PDF"]
                for (var i = 0; i < files.length; i++){
                    var fd = new FormData();
                    fd.append("file", files[i]);
                    fd.append("metodo","upload");
                    fd.append("origen","multiple");
                    var status = new createStatusbar(obj);
                    fd.append("id",status.getId());
                    status.setFileNameSize(files[i].name,files[i].size);
                    ext = files[i].name.split(".").pop().toUpperCase();
                    status.setIcon(ext);

                    if(files[i].size> 20971500){
                        status.statusbar.addClass("error2");
                        status.abort.html("El archivo es mayor a 20 Megabytes");
                    }else if(tipos.indexOf(ext)==-1){
                        status.statusbar.addClass("error2");
                        status.abort.html("archivo no valido");
                    }else{
                        sendFileToServer(fd,status);
                    }
                }
            }
            var rowCount=0;
            function createStatusbar(obj){
                rowCount++;
                var row="odd";
                if(rowCount %2 ==0) row ="even";
                this.statusbar = $("<div id=\'statusbar2"+ rowCount + "\' class=\'statusbar2 "+row+"\'></div>");
                this.filename = $("<div class=\'infofile filename\'></div>").appendTo(this.statusbar);
                this.size = $("<div class=\' infofile filesize\'></div>").appendTo(this.statusbar);
                this.icon = $("<div class=\'infofile  \'></div>").appendTo(this.statusbar);
                this.progressBar= $("<div class=\'infofile bar\'><div class=\"progressBar\"></div></div>").appendTo(this.statusbar);
                this.idFile = $("<div class=\'infofile abort  \'></div>").appendTo(this.statusbar);
                this.abort = $("<div class=\'infofile  abort\'>Abort</div>").appendTo(this.statusbar);

                obj.append(this.statusbar);
                this.getId = function(){
                    return  "statusbar2"+rowCount;
                }
                this.setIcon = function(extension){
                    icono = "";
                    switch(extension){
                        case "PDF": icono = "pdf"; break;
                        case "XML": icono = "xml"; break;
                        case "DOC":case "DOCX": icono = "word"; break;
                        case "XLS":case "XLSX": icono = "excel"; break;
                        case "JPG":case "JPGE": icono = "jpg"; break;
                        case "PNG": icono = "png"; break;
                        case "MSI": icono = "verboten"; break;
                        default: icono = "verboten"; break;
                    }
                    this.icon.html("<img src=\'img/24pxIcon/"+ icono+".png \' height=\"21\" />");
                }
                this.setIdFile = function(nombre){
                    this.idFile.html("ID:" + nombre + "<span class=\"deletefile\" id=\'"+nombre+"\'>Borrar X<span/>");
                }
                this.getFileName = function(){ return this.filename.html();	}
                this.setFileNameSize = function(name,size){
                    var sizeStr="";
                    var sizeKB = size/1024;
                    if(parseInt(sizeKB) > 1024){
                        var sizeMB = sizeKB/1024;
                        sizeStr = sizeMB.toFixed(2)+" MB";
                    }else{
                        sizeStr = sizeKB.toFixed(2)+" KB";
                    }
                    this.filename.html(name);
                    this.size.html(sizeStr);
                }
                this.setProgress = function(progress){
                    var progressBarWidth =progress*this.progressBar.width()/ 100;
                    this.progressBar.find(\'div\').animate({ width: "100%" }, 10).html(progress + "% ").css("width",progress+"px");
                    if(parseInt(progress) >= 100){
                        this.abort.hide();
                    }
                }
                this.setAbort = function(jqxhr){
                    var sb = this.statusbar;
                    this.abort.click(function(){
                        jqxhr.abort();
                        sb.hide();
                    });
                }
            }

            function sendFileToServer(formData,status){
                var uploadURL ="poa/seguimiento/ControladorRevisarActividad.php"; //Upload URL
                var jqXHR=$.ajax({
                    xhr: function() {
                        var xhrobj = $.ajaxSettings.xhr();
                        if (xhrobj.upload) {
                            xhrobj.upload.addEventListener("progress", function(event) {
                                var percent = 0;
                                var position = event.loaded || event.position;
                                var total = event.total;
                                if (event.lengthComputable) {
                                    percent = Math.ceil(position / total * 100);
                                }
                                status.setProgress(percent);
                            }, false);
                        }
                        return xhrobj;
                    },
                    url: uploadURL,
                    type: "POST",
                    dataType:"json",
                    contentType:false,
                    processData: false,
                    cache: false,
                    data: formData,
                    success: function(data){
                        status.setIdFile(data.idFile);
                    }
                }).done(function(data){
                    comunicaCallback(data.comunica);
                });
                status.setAbort(jqXHR);
            }
            function download(idd,pgb,type){
                window.URL = window.URL || window.webkitURL;
                nn = document.getElementById(pgb);
                $.ajax({
                    xhr: function(){
                        var xhr = new window.XMLHttpRequest();
                        xhr.addEventListener("progress", function(evt){
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                wid = percentComplete*100;
                                $("#"+ pgb).css("width",wid+"px");
                            }
                        }, false);
                        return xhr;
                    },
                    type:"POST",
                    url:"poa/download_file.php",
                    data:{file:pgb,tx:type}
                });
            }
        ');
    }

    private function upload()
    {
        //Recepción de parámetros
        $this->prm->hmsid = isset($this->prm->hmsid) ? $this->prm->hmsid : '';
        $ruta = "doctosEvi/";
        $ruta2 = "./doctosEvi/";
        $icono = '';
        $tar_id = '';
        //validación para saber si existe la carpeta
        if (!file_exists("./doctosEvi/")) {
            mkdir("./doctosEvi/", 0775, true);
        }
        //declaración del archivo
        $archivo = (isset($_FILES['evidenciasFile'])) ? $_FILES['evidenciasFile'] : null;
        //$extp =array("DOC","DOCX","XLS","XLSX","JPG","PNG","GIF","PDF","XML","MSI");
        $extp = array("PDF");
        //recorre el arreglo de archivos y selecciona el icono segun la extención del archivo a subir
        foreach ($_FILES as $file) {
            switch (strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION))) {
                case "DOC":case "DOCX":$icono = 'word';
                    $tar_id = 1;
                    break;
                case "XLSX":case "XLS":$icono = 'excel';
                    $tar_id = 2;
                    break;
                case "PDF":$icono = 'PDF';
                    $tar_id = 3;
                    break;
                case "JPG":$icono = 'jpg';
                    $tar_id = 4;
                    break;
                case "PNG":$icono = 'png';
                    $tar_id = 5;
                    break;
                default:$icono = 'verboten';
                    $tar_id = 6;
                    break;
            }
            //Variable temporal que se usara para mostrar los archivos
            $tmp = '<div class=\"statusbar2 error2\"><div class=\"infofile filename\">' . $file['name'] . '</div><div class=\" infofile filesize\"></div><div class=\"infofile \"><img src=\"img/24pxIcon/' . $icono . '.png\"/></div><div class=\"infofile \"></div><div class=\"infofile  abort\">El archivo no esta permitido</div></div>';
            //Valida que el archivo sea de la extención permitida, caso contrario anexa archivo como error y no permite subir el archivo
            if (!in_array(strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)), $extp)) {
                if (isset($this->prm->origen) && $this->prm->origen == "input") {
                    $this->mixScript('	$("#listaArchivos").append("' . $tmp . '");');
                } else {
                    $this->mixScript('
                    $("#' . $this->prm->id . '").remove();
                    $("#dragndrop").append("' . $tmp . '");');
                }
                return;
            }
            //este bloque consulta en la BD si el archivo existe
            $namemd5 = strtoupper(md5_file($file['tmp_name']));
            $sql = 'SELECT * FROM cat_RespaldoEvidencia WHERE rev_nombreGuardado =?';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$namemd5]);
            $reg = $this->getNumeroRegistros();
            $data = $this->extraer();
            $linkid = '';
            //Condición para saber si el archivo ya existe en sistema
            if ($reg > 0) {
                $tmp = '<div class=\"statusbar2 error3\"><div class=\"infofile filename\">' . $file['name'] . '</div><div class=\"infofile  abort\">El archivo ya ha sido subido</div><div class=\"infofile \"><img src=\"img/24pxIcon/' . $icono . '.png\"/></div><div class=\"infofile  abort\"><a target=\"_blank\" id=\"' . $linkid . '\" href=\"poa/' . $ruta . $namemd5 . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) . '\">Descargar</a></div></div>';
                if (isset($this->prm->origen) && $this->prm->origen == "input") {
                    $this->mixScript('	$("#listaArchivos").append("' . $tmp . '");');
                } else {
                    $this->mixScript('
                        $("#' . $this->prm->id . '").remove();
                        $("#dragndrop").append("' . $tmp . '");
                        $("#' . $linkid . '").click(function(){
                            comunica("' . $this->pantalla . '",{metodo:"download",id:"' . $linkid . '",x:"' . $data['hms_id'] . '"});
                            $(this).remove();
                        });
                    ');
                }
                return;
            }

            //Sube archivo al servidor
            if ($archivo) {
                $ruta_destino_archivo = $ruta . $namemd5 . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                //Se sube el archivo, se valida que se pueda subir
                if (!move_uploaded_file($archivo['tmp_name'], "./" . $ruta_destino_archivo)) {
                    $this->mixScript('notify("Error 1 al subir el fichero, intentelo nuevamente","error");');
                    return false;
                }
                //Se pasa el tamaño del archivo
                $size = $file['size'] / 1024;
                if ($size > 1024) {
                    $size = number_format(($size / 1024), 2);
                    $size .= ' Mb';
                } else {
                    $size = number_format($size, 2);
                    $size .= 'Kb';
                }

                //temp con datos del archivo que se subio
                if (isset($this->prm->origen) && $this->prm->origen == "input") {
                    $tmp = '<div class=\"statusbar2\"><div class=\"infofile filename\"><a target=\"_blank\" href=\"poa/seguimiento/' . $ruta . $namemd5 . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) . '\">' . $archivo['name'] . '</a></div><div class=\"infofile size\">' . $size . '</div><div class=\"infofile \"><img src=\"img/24pxIcon/' . $icono . '.png\"/></div><div class=\"infofile bar\"><div class=\"progressBar\">100%</div></div><div class=\"infofile  abort\">Cargado</div></div>';
                    $this->mixScript('	$("#listaArchivos").append("' . $tmp . '");');
                    //Se guarda el registro en la BD
                    $usr = $_SESSION[_S]['usr_id'];
                    $sql = 'INSERT INTO cat_RespaldoEvidencia (rev_nombreOriginal,rev_nombreGuardado,tar_id,rev_ruta,rev_fechaAltaEvidencia,usr_id,usr_fecha )
                            VALUES("' . $archivo['name'] . '","' . $namemd5 . '"
                        ,' . $tar_id . ',"poa/seguimiento/' . $ruta . $namemd5 . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) . '",now(),' . $usr . ',now());';
                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar();
                    $this->rev_id = $this->getUltimoId();
                    $this->desconectar();
                    $sql = 'INSERT INTO vin_HistorialRespaldo(hms_id,rev_id,usr_id,usr_fecha)
                            VALUES(' . $this->prm->hmsid . ',' . $this->rev_id . ',' . $usr . ',now());';
                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar();
                    $this->desconectar();
                    $sql = 'update cat_HistorialMensajeSeguimiento set ese_id=3 where hms_id=? AND ese_id = 4';
                    $this->conectar();
                    $this->setSql($sql);
                    $this->consultar([$this->prm->hmsid]);
                    $this->idFile = $this->getUltimoId();
                    $this->desconectar();
                    //Actualiza el num de mensajes
                    $this->mixScript(ControladorMensajes::consultaMensajes());
                }
            }
        }
    }

    private function verResponder()
    {
        $this->setContenedor('responder' . $this->prm->msj_id . '');
        $botones = '';
        $btnEnviar = new stdClass();
        $btnEnviar->id = 'btnEnviar';
        $btnEnviar->type = 'button';
        $btnEnviar->title = 'Enviar respuesta';
        $btnEnviar->class = "btn btn-outline-success enviar";
        $btnEnviar->icon = "fa fa-paper-plane";
        $btnEnviar->attr = new stdClass();
        $botones = '<td>&nbsp;&nbsp;' . $this->ponBoton($btnEnviar) . '</td>';

        $this->mixHtml('
            <table class="table table-hover table-bordered" >
                <tr class="warning">
                <th>RESPONDER</th>
                </tr>
                <tr>
                    <td colspan=3>
                        <label id="lbl_count_char"></label>
                        <textarea id="respuesta" name="respuesta" cols="120" maxlength="1000"></textarea>
                    </td>
                </tr>
                <tr>' . $botones . '</tr>
            </table>
        ');
        $this->mixScript('
            $("#btnEnviar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"enviarRespuesta",
                    msj_id:' . $this->prm->msj_id . ',
                    epg_id:"' . $this->prm->epg_id . '",
                    trimestre:' . $this->prm->trimestre . ',
                    respuesta: $("#respuesta").val().toUpperCase()
                });
            });

            $("#lbl_count_char").text("Caracteres restantes: " + $("#respuesta").attr("maxlength"));
            $("#respuesta").keyup(function () {
                var max = $(this).attr("maxlength");
                var len = $(this).val().length;
                if((max - len) == 0) {
                    $("#lbl_count_char").html("Caracteres restantes: <any style=\"color:red;\">" + (max - len) + "</any>");
                } else {
                    $("#lbl_count_char").text("Caracteres restantes: " + (max - len));
                }
            });
        ');
    }

    private function enviarRespuesta()
    {
        $this->prm->respuesta = addslashes($this->prm->respuesta);
        $this->conectar();
        $this->setSql('
            UPDATE cat_HistorialMensajeSeguimiento SET hms_mensajeRespuesta="' . $this->prm->respuesta . '",
            hms_FechaRespuesta=now(), hms_Respuesta=' . $_SESSION[_S]['usr_id'] . ',
            eme_id=4
            WHERE msj_id=? AND (eme_id=2 OR eme_id=5 ) ;
        ');
        $this->consultar([$this->prm->msj_id]);
        $registrosAfectados = $this->getNumeroModificados();
        $this->desconectar();
        if ($registrosAfectados > 0) {
            $this->mixScript('
                notify("La respuesta ha sido enviada");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    epg_id:"' . $this->prm->epg_id . '",
                    trimestre:' . $this->prm->trimestre . '
                });

            ');
        } else {
            $this->mixScript('
                notify("La respuesta NO ha sido enviada");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    epg_id:"' . $this->prm->epg_id . '",
                    trimestre:' . $this->prm->trimestre . '
                });

            ');
        }
    }

    private function ActualizaVisto()
    {
        $trimestreSeleccionado = $this->prm->trimestre;
        $this->conectar();
        $this->setSql('
            UPDATE cat_HistorialMensajeSeguimiento Uhi,(
                SELECT msj_id
                FROM vin_ProductoIntermedioIndicador
                LEFT JOIN cat_MensajeSeguimiento using(pii_id)
                LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                INNER JOIN cat_Usuario u  on hm.usr_id = u.usr_id
                WHERE epg_id=?
                AND hm.tim_id=1
                AND eme_id=2 and hms_trimestre=?
            ) as m
            set eme_id=5
            where  Uhi.msj_id=m.msj_id;');
        $this->consultar([$this->prm->epg_id,$trimestreSeleccionado]);
        $registrosAfectados = $this->getNumeroModificados();
        $this->desconectar();
        if ($registrosAfectados < 0) {
            $this->mixScript('
                notify("Estatus de Mensaje no actualizados");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    epg_id:"' . $this->prm->epg_id . '",
                    trimestre:' . $this->prm->trimestre . '
                });
            ');
        }
    }

    //Esta función genera un nombre al azar
    private function getName($n = 10)
    {
        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $cadena = "";
        for ($i = 0; $i < $n; $i++) {
            $cadena .= substr($caracteres, rand(0, strlen($caracteres)), 1);
        }
        return $cadena;
    }



    private function metasObservadas()
    {
        $this->setContenedor('actividades');

        $fecha_limite = '';
        $titulo = '';
        $trims = array(['ene', 'feb', 'mar'], ['abr', 'may', 'jun'], ['jul', 'ago', 'sep'], ['oct', 'nov', 'dic']);
        $presupuesto = "(SUM(apr_" . $trims[$this->prm->trimestre - 1][0] . ") + SUM(apr_" . $trims[$this->prm->trimestre - 1][1] . ") + SUM(apr_" . $trims[$this->prm->trimestre - 1][2] . ")) AS aprobado,
                (SUM(vig_" . $trims[$this->prm->trimestre - 1][0] . ") + SUM(vig_" . $trims[$this->prm->trimestre - 1][1] . ") + SUM(vig_" . $trims[$this->prm->trimestre - 1][2] . ")) AS vigente,
                (SUM(com_" . $trims[$this->prm->trimestre - 1][0] . ") + SUM(com_" . $trims[$this->prm->trimestre - 1][1] . ") + SUM(com_" . $trims[$this->prm->trimestre - 1][2] . ")) AS comprometido,
                (SUM(dev_" . $trims[$this->prm->trimestre - 1][0] . ") + SUM(dev_" . $trims[$this->prm->trimestre - 1][1] . ") + SUM(dev_" . $trims[$this->prm->trimestre - 1][2] . ")) AS devengado,
                (SUM(eje_" . $trims[$this->prm->trimestre - 1][0] . ") + SUM(eje_" . $trims[$this->prm->trimestre - 1][1] . ") + SUM(eje_" . $trims[$this->prm->trimestre - 1][2] . ")) AS ejercido ";
        $porcentajePresupuesto = " round(((100*(SUM(eje_" . $trims[$this->prm->trimestre - 1][0] . ") + SUM(eje_" . $trims[$this->prm->trimestre - 1][1] . ")
                        + SUM(eje_" . $trims[$this->prm->trimestre - 1][2] . ")))/(SUM(vig_" . $trims[$this->prm->trimestre - 1][0] . ")
                        + SUM(vig_" . $trims[$this->prm->trimestre - 1][1] . ") + SUM(vig_" . $trims[$this->prm->trimestre - 1][2] . "))),2) porcentajePresupuesto ";
        switch ($this->prm->trimestre) {
            case 1:
                $fecha_limite = " and o.usr_fecha <= '2026-03-31' ";
                $titulo = 'PRIMER TRIMESTRE';
                break;
            case 2:
                $fecha_limite = " and o.usr_fecha <= '2026-06-30' ";
                $titulo = 'SEGUNDO TRIMESTRE';
                break;
            case 3:
                $fecha_limite = " and o.usr_fecha <= '2026-09-30' ";
                $titulo = 'TERCER TRIMESTRE';
                break;
            case 4:
                $fecha_limite = " and o.usr_fecha <= '2026-12-31' ";
                $titulo = 'CUARTO TRIMESTRE';
                break;
        }

        /*             * *******************************TOA_ID****************************** */
        $toa_id = (isset($this->prm->toa_id) && $this->prm->toa_id != "" ? ' AND toa_id=' . $this->prm->toa_id : '');

        $this->conectar();
        $this->setSql("
            SELECT
                gpo_id, ur_id, prg_id,spr_id, py_id, oa_id, prg_nombre, spr_nombre, oa_nombre,
                /*SUM(pre_aprobado) AS aprobado,
                SUM(pre_vigente) AS vigente_anual, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                SUM(pre_ejercido) AS ejercido, */
                " . $presupuesto . ",
                epg_id, py_nombre, toa_id, oa_origen,metas,
                pdf_id, meta.pdi_id, ind_id, meta.pii_id, o.usr_fecha,
                -- round(((SUM(pre_devengado)/SUM(pre_vigente))*100),2) porcentajePresupuesto,
                " . $porcentajePresupuesto . ",
                hms_mensaje, eme_id, porcentajeMeta, ind_limInfVer, ind_limInfAmar, ind_limSupRojo,ses_id,
                ore_oficioAut, evi_sol, observaciones,
                sum_mact_original, sum_mact_modificado
            FROM cat_ObraActividad o
            INNER JOIN enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
            INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN (
                SELECT
                    COUNT(DISTINCT m.pii_id) AS metas, epg.gpo_id,epg.ur_id, SUM(mact_original) AS sum_mact_original, SUM(mact_modificado) AS sum_mact_modificado,
                    m.epg_id, 	MAX(pdi_id)pdi_id,MAX(ci.ind_id)ind_id, MAX(m.pii_id) pii_id,
                    MAX(hms_mensaje)hms_mensaje,  MIN(eme_id) eme_id,
                    ROUND((SUM(mact_alcanzado)/SUM(mact_modificado))*100,2) porcentajeMeta,
                    MAX(ind_limSupAmar)ind_limSupAmar,MAX(ind_limInfVer)ind_limInfVer, MAX(ind_limInfAmar )ind_limInfAmar,
                    MAX(ind_limSupRojo)ind_limSupRojo, MAX(ses_id)ses_id,
                    SUM(IF(hm.ese_id > 1, 1, 0)) AS evi_sol, SUM(observaciones)observaciones
                FROM cat_MetaActividad m
                INNER JOIN cat_MetaActividadTrimestre mt using(pii_id, epg_id)
                INNER JOIN vin_ProductoIntermedioIndicador pii USING(pii_id)
                INNER JOIN cat_Indicador ci USING(nim_id,ind_id)
                INNER JOIN enc_Programatico epg  USING(epg_id)
                LEFT JOIN cat_MensajeSeguimiento cms USING(epg_id)
                LEFT JOIN (
                    SELECT  hms_id, hms_mensaje, eme_id, hms_mensajeRespuesta, hms_trimestre,
                    hms_mensual, tim_id, msj_id, hms_respuesta, hms_fechaRespuesta, hms_fechaSolicitudEvidencia,
                    ese_id, usr_id, usr_fecha, pii_id, epg_id, fecha2, observaciones
                    FROM cat_HistorialMensajeSeguimiento  hm2
                    INNER  JOIN (
                        SELECT pii_id,epg_id,MAX(hm1.usr_fecha) fecha2, SUM(IF(eme_id!=3,1,0))observaciones
                        FROM cat_MensajeSeguimiento cms1
                        INNER JOIN cat_HistorialMensajeSeguimiento hm1 ON cms1.msj_id = hm1.msj_id
                        AND hm1.hms_trimestre = " . $this->prm->trimestre . " AND tim_id = 1
                        AND eme_id IS NOT NULL
                        GROUP BY pii_id,epg_id
                    ) AS elimina ON  hm2.usr_fecha=fecha2
                    WHERE eme_id IS NOT NULL
                )hm ON cms.msj_id = hm.msj_id
                AND hm.hms_trimestre = " . $this->prm->trimestre . " AND tim_id = 1
                WHERE mt.tri_id = :trimestre
                GROUP BY m.epg_id
            ) AS meta USING(gpo_id,ur_id,epg_id)
            INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
            LEFT JOIN presupuesto p USING(gpo_id, ur_id, ue_id, epg_id, prg_id, spr_id, py_id, oa_id)
            LEFT JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            LEFT JOIN (
                SELECT por.epg_id, group_concat(distinct ore_oficioAut,'') AS ore_oficioAut
                FROM poa_OrigenRecurso por
                GROUP BY por.epg_id
            ) AS of1 USING(epg_id)
            WHERE gpo_id= :gpo_id and ur_id= :ur_id" . $toa_id . $fecha_limite . "
            AND ((toa_id=1 AND eoa_id = 4) OR (toa_id=2 AND eoa_id=5)) AND IF(observaciones > 0,1,0)
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
            HAVING aprobado > 0 OR vigente > 0 OR sum_mact_original > 0 OR sum_mact_modificado > 0;
        ");
        $params = [
            ':trimestre' => $this->prm->trimestre,
            ':gpo_id' => $this->prm->gpo_id,
            ':ur_id' => $this->prm->ur_id
        ];
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() == 0) {
            $titulo = 'NO SE ENCONTRARON REGISTROS CON LOS PARÁMETROS SELECCIONADOS';
        }

        $htm = '
            <legend style="text-align:center">' . $titulo . '</legend><br>
            <table  class="table table table-bordered table-hover">';

        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';
        $cambioClase = true;
        while ($row = $this->extraer()) {
            if (isset($row['eme_id'])) {
                $cambioClase = true;
                if ($row['eme_id'] == 1) {
                    $color = "#FF8000";
                    $leyenda = 'CON OBSERVACIÓN GUARDADA';
                } else {
                    $color = "#FF1E2D";
                    $leyenda = 'CON OBSERVACIÓN';
                }
                if (($row['eme_id'] == 3)) {
                    if ($row['observaciones'] > 0 && $row['eme_id'] != 3) {
                        $cambioClase = true;
                        $color = "#FF8000";
                        $leyenda = 'CON OBSERVACIÓN GUARDADA';
                    } else {
                        $cambioClase = false;
                    }
                }
            } else {
                $cambioClase = false;
            }
            $condicionEstatus = ($row['observaciones'] != "" && $row['observaciones'] > 0 && $row['eme_id'] != 3 ? "<label id='obs" . $row['epg_id'] . "' style='color:" . $color . ";'>" . $leyenda . "</label>" : "<label id='obs" . $row['epg_id'] . "' style='color: #00950A;'>SIN OBSERVACIÓN</label>");
            $color = '';
            $color1 = '';
            $estilo_btn_1 = '';
            $estilo_btn_2 = '';
            /* SEMAFORIZACIÓN DE PRESUPUESTO SEGUN EL TRIMESTRE */

            if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] < 50) {

                $estilo_btn_1 = 'style="background-color: #b20000;"'; // ROjo
            }
            if ($row['porcentajePresupuesto'] >= 50 && $row['porcentajePresupuesto'] < 80) {

                $estilo_btn_1 = 'style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($row['porcentajePresupuesto'] >= 80 && $row['porcentajePresupuesto'] <= 100) {
                $color = 'btn-succes';
                $estilo_btn_1 = 'style="background-color: #35B200;"'; //verde
            }

            switch ($row['ses_id']) {
                case 1: //1-ASCENDENTE
                    if ($row['porcentajeMeta'] != null) {
                        if ($row['porcentajeMeta'] >= 0 && $row['porcentajeMeta'] < 60) {
                            $color1 = 'btn-danger'; // Rojo
                        } elseif ($row['porcentajeMeta'] >= 60 && $row['porcentajeMeta'] < 85) {
                            $color1 = 'btn-warning'; // Verde
                        } elseif ($row['porcentajeMeta'] >= 85 && $row['porcentajeMeta'] <= 100) {
                            $color1 = 'btn-success';
                            $estilo_btn_2 = 'style="background-color: #35B200;"';
                        } elseif ($row['porcentajeMeta'] > 100) {
                            $color1 = 'btn-danger'; // Rojo
                        }
                    } else {
                        $color1 = 'btn-danger'; // Negro
                    }
                    break;
                case 2: //2-DESCENDENTE
                    if ($row['porcentajeMeta'] != null) {
                        if ($row['porcentajeMeta'] <= $row['ind_limInfVer']) {
                            $color1 = 'btn-success';
                            $estilo_btn_2 = 'style="background-color: #35B200;"';
                        } elseif (($row['porcentajeMeta'] > $row['ind_limSupAmar']) && ($row['porcentajeMeta'] <= $row['ind_limInfAmar'])) {
                            $color1 = 'btn-warning';
                        } elseif ($row['porcentajeMeta'] >= $row['ind_limSupRojo']) {
                            $color1 = 'btn-danger';
                        }
                    } else {
                        $color1 = 'btn-danger';
                    }
                    break;
            }
            if ($row['prg_id'] != $prg) {
                $htm .= '<tr style="background-color:#9CCC65;">
                        <th class="bg-encabezado" style="text-align:center" colspan="1">PR:</th>
                        <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['prg_nombre'] . '</th>
                    </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $htm .= '<tr style="background-color:#9CCC65;">
                            <th class="bg-encabezado" style="text-align:center" colspan="1">SPR:</th>
                            <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                            <td align="left" colspan="17">' . $row['spr_nombre'] . '</td>
                            <th style="background-color: #5ED800; text-align:center;" style="text-align:center" align="left" colspan="2"><label>SEMAFORIZACIÓN</label></th>
                            <th class="default" style="background-color: #F300E3; text-align:center" align="left" colspan="1"><label>EVIDENCIAS</label></th>
                            <th class="info" style="background-color: #1C61EC; text-align:center" align="left" colspan="4"><label>OBSERVACIONES</label></th>
                        </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py) {
                $htm .= '<tr style="background-color:#9CCC65;">
                            <th class="bg-encabezado" style="text-align:center" colspan="1">PYT:</th>
                            <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['py_id'] . '</th>
                            <td align="left" colspan="5">' . $row['py_nombre'] . '</td>
                            <th class="bg-encabezado" align="center" colspan="2">OFICIO DE AUTORIZACIÓN</th>
                            <th class="bg-encabezado" align="center" colspan="2">APROBADO</th>
                            <th class="bg-encabezado" align="center" colspan="2">MODIFICADO</th>
                            <th class="bg-encabezado" align="center" colspan="2">COMPROMETIDO</th>
                            <th class="bg-encabezado" align="center" colspan="2">DEVENGADO</th>
                            <th class="bg-encabezado" align="center" colspan="2">EJERCIDO</th>
                            <th class="bg-encabezado" align="center" colspan="1">PRESUPUESTO</th>
                            <th class="bg-encabezado" align="center" colspan="1">METAS</th>
                            <th class="bg-encabezado" align="center" colspan="1">SOLICITADAS</th>
                            <th class="bg-encabezado" align="center" colspan="1">VER</th>
                            <th class="bg-encabezado" align="center" colspan="1">INDICADORES</th>
                            <th class="bg-encabezado" align="center" colspan="1">ESTATUS</th>
                            <th class="bg-encabezado" align="center" colspan="1">HISTORIAL</th>
                        </tr>';
            }

            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py || $row['oa_id'] != $oa) {
                $clase = 'class="bg-conobservacion" style="background-color:#C39BD3;"';
                $descBotonHist = $row['observaciones'] > 0 && $row['eme_id'] != 3 ? '' : 'disabled';
                $evidencia_solicitada = $row['evi_sol'] ? '<i class="fa fa-circle-check text-danger" aria-hidden="true"></i>' : '<i class="fa fa-times text-primary" aria-hidden="true"
                            id="icon_' . $row['gpo_id'] . '-' . $row['ur_id'] . '-' . $row['prg_id'] . '-' . $row['spr_id'] . '-' . $row['py_id'] . '-' . $row['oa_id'] . '"></i>';
                $htm .= '<tr id="eli" ' . $clase . ' >
                        <td ' . $clase . ' align="center" colspan="1"></td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                        <td ' . $clase . ' align="left" colspan="2">' . $row['oa_nombre'] . '</td>
                        <td ' . $clase . ' align="left" colspan="2">' . $row['ore_oficioAut'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['aprobado'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['vigente'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['comprometido'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['devengado'], 2) . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . number_format($row['ejercido'], 2) . '</td>
                        <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small ' . $color . '" title="Porcentaje de PRESUPUESTO ' . ($row['porcentajePresupuesto'] != null ? $row['porcentajePresupuesto'] : '0.00') . ' %" ' . $estilo_btn_1 . '></button></td>
                        <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small ' . $color1 . '" title="Porcentaje de la META ' . ($row['porcentajeMeta'] != null ? $row['porcentajeMeta'] : '0.00') . ' %" ' . $estilo_btn_2 . ' ></button></td>
                        <td ' . $clase . ' align="center" colspan="1" >
                        <label style="font-size: 21px;">
                            ' . $evidencia_solicitada . '
                        </label>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button" id="verMetasActividades" data-bs-toggle="collapse" data-bs-target="#collapseMetas" aria-expanded="false" aria-controls="collapseMetas" title="Ver el Indicador/Meta de la Actividad" class="verActividad btn btn-outline-secondary"  epg_id="' . $row['epg_id'] . '" pii_id="' . $row['pii_id'] . '">
                            <i class="fa fa-eye"></i>
                        </button>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >	<font size="3">' . $row['metas'] . '</font>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >' . $condicionEstatus . '</td>
                        <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button" ' . $descBotonHist . ' id="verHistorial" icon="fa fa-list-alt"
                            title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary"
                            epg_id=' . $row['epg_id'] . ' gpo_id="' . $row['gpo_id'] . '" ur_id="' . $row['ur_id'] . '"
                            prg_id="' . $row['prg_id'] . '" spr_id="' . $row['spr_id'] . '" py_id="' . $row['py_id'] . '"
                            oa_id="' . $row['oa_id'] . '">
                            <i class="fa fa-list-alt"></i>
                        </button>
                        </td>
                    </tr>
                    <tr id="collapseMetas" class="bg-sinobservacion collapse" style="background-color:#9CCC65;">
                        <th></th>
                        <th class ="divMetas" id="metas' . $row['epg_id'] . '" colspan = "20" >
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
            $("#myModal").modal("hide");
            $(".verActividad").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    epg_id: $(this).attr("epg_id"),
                    pii_id: $(this).attr("pii_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . ',
                    trimestre:' . $this->prm->trimestre . '
                    ' . ((isset($this->prm->toa_id) && $this->prm->toa_id != "") ? ',toa_id:' . $this->prm->toa_id . '' : '') . '
                });
            });

            $(".verHistorial").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    trimestre:' . $this->prm->trimestre . ',
                    epg_id:$(this).attr("epg_id"),
                    gpo_id: $(this).attr("gpo_id"),
                    ur_id: $(this).attr("ur_id"),
                    prg_id: $(this).attr("prg_id"),
                    spr_id: $(this).attr("spr_id"),
                    py_id: $(this).attr("py_id"),
                    oa_id: $(this).attr("oa_id")
                    ' . ((isset($this->prm->toa_id) && $this->prm->toa_id != "") ? ',toa_id:' . $this->prm->toa_id . '' : '') . '
                });
            });
            console.log("toa_id: " + "' . $toa_id . '");

        ');
    }
}

//fin clase
if (isset($_SESSION[_S]['prm']['A']['11']['5'])) {
    new ControladorRevisarActividad();
} else {
    WebResponse::sinPermisos();
}
