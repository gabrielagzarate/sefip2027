<?php

session_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/MensajeSeguimiento.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/HistorialMensajeSeguimiento.php';
require_once dirname(__FILE__) . '/../../srv/ControladorMensajes.php';

class ControladorObservarActividad extends WebResponse
{
    public $hoy;
    public $toa;
    public $gpo;
    public $ur ;
    public $prg;
    public $spr;
    public $py ;

    public function __construct()
    {
        parent::__construct();

        $this->hoy = date('Y-m-d H:i:s');
        $this->toa = $this->parametroJs('toa_id');
        $this->gpo = $this->parametroJs('gpo_id');
        $this->ur = $this->parametroJs('ur_id', true);
        $this->prg = $this->parametroJs('prg_id', true);
        $this->spr = $this->parametroJs('spr_id', true);
        $this->py = $this->parametroJs('py_id', true);

        if ($_SESSION[_S]['nvj_id'] > 1) {
            $this->ejecutarMetodo();
        } else {
            $this->sinPermisos();
        }
        $this->returnJson();
    }

    private function parametroJs(string $campo, bool $entreComillas = false): string
    {
        $valor = $this->prm->$campo ?? '';
        if ($valor == '') {
            return '';
        }

        return $entreComillas ? ',' . $campo . ':"' . $valor . '"' : ',' . $campo . ':' . $valor;
    }

    private function ejecutarMetodo(): void
    {
        switch ($this->metodo) {
            case 'seleccionaTrimestre':
                $this->seleccionaTrimestre();
                break;
            case 'verActividades':
                $this->verActividades();
                break;
            case 'verMetas':
                $this->verMetas();
                break;
            case 'solicitarEvidencias':
                $this->solicitarEvidencias();
                break;
            case 'verEvidencias':
                $this->verEvidencias();
                break;
            case 'Observacion':
                $this->Observacion();
                break;
            case 'nuevaObservacion':
                $this->nuevaObservacion();
                break;
            case 'ModificarMensaje':
                $this->ModificarMensaje();
                break;
            case 'Historial':
                $this->Historial();
                break;
            case 'EnviarEliminarMensaje':
                $this->EnviarEliminarMensaje();
                break;
            default:
                $this->main();
                break;
        }
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
            comunica("' . $this->pantalla . '", {
                metodo:"main"
            });
            $("#actividades").hide();
        ';
        $this->agregaTools($this->ponboton($btnregresarindex));
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
        if (($_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['dir_id'] == 7 && $_SESSION[_S]['uni_id'] == 17) && ($_SESSION[_S]['dep_id'] == 44 || $_SESSION[_S]['dep_id'] == 45 || $_SESSION[_S]['dep_id'] == 46)) {
            $this->mixScript(
                ClasificacionAdministrativa::onChangeUr('
                    comunica("' . $this->pantalla . '", {
                        metodo:"seleccionaTrimestre"
                    });
                ')
            );
        } else {
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $clasAdmin->comboPy = false;
            $clasAdmin->comboOa = false;
            $clasAdmin->change = '
                if($("#combour").val()!= ""){
                    comunica("' . $this->pantalla . '", {
                        metodo:"seleccionaTrimestre"
                    });
                }
            ';
            $this->mixScript('$("#clasAdminSeccionCombos").show();' . ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
        }
        $this->mixScript('$("#combour").change();');
    }

    private function seleccionaTrimestre()
    {
        $this->mixScript('$("#clasAdminSeccionCombos").hide();');
        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $unidadR->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $unidadR->select();
        if (isset($this->prm->ur_id) && $this->prm->ur_id != '' && isset($this->prm->pepg_id) && $this->prm->pepg_id != '') {
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $clasAdmin->comboPy = false;
            $clasAdmin->comboOa = false;
            $clasAdmin->change = '
                if($("#combosp").val()!= ""){
                    comunica("' . $this->pantalla . '", {
                    metodo:"seleccionaTrimestre"
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
        $comboTipoObra->opciones = ['' => 'Seleccione una opción de la lista', 1 => 'GASTO CORRIENTE', 2 => 'GASTO DE INVERSIÓN'];
        $comboTipoObra->width = '400px';
        $comboTipoObra->label = 'TIPO';

        $tri = '<div><h4><b>' . $unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre() . '</b></h4></div>';

        if (($_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['dir_id'] == 7 && $_SESSION[_S]['uni_id'] == 17) && ($_SESSION[_S]['dep_id'] == 44 || $_SESSION[_S]['dep_id'] == 45 || $_SESSION[_S]['dep_id'] == 46)) {
            $tri .= '<br/><br/><div style="width:100%; padding-left:30%; padding-bottom:50px;">';
        } else {
            $tri .= $this->ponCombo($comboTipoObra) . '<br/><br/><div style="width:100%; padding-left:30%; padding-bottom:50px;">';
        }

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
                    <div class="center"><br> <img src="./img/metas.png" width="64" height="64" alt=""/><br>
                    <strong><small></small></strong>
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

            $(".trim").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo: "verActividades",
                    trimestre: $(this).attr("trimestre"),
                    ue_id: $("#comboue").find(":selected").val(),
                    prg_id: $("#comboprg").find(":selected").val(),
                    spr_id: $("#combosp").find(":selected").val(),
                    gpo_id: ' . $_SESSION[_S]['clasAdm-gpo'] . ',
                    ur_id: ' . $_SESSION[_S]['clasAdm-ur'] . '
                    ' . ($comboTipoObra != '' ? ',
                        toa_id: $("#comboTipoObra").val()
                    ' : '
                    ') . '
                });
            });
        ');
    }

    private function verActividades()
    {
        $this->setContenedor('actividades');

        $fecha_limite = '';
        $titulo = '';
        $trims = [['ene', 'feb', 'mar'], ['abr', 'may', 'jun'], ['jul', 'ago', 'sep'], ['oct', 'nov', 'dic']];
        $presupuesto = '(SUM(apr_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(apr_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(apr_' . $trims[$this->prm->trimestre - 1][2] . ')) AS aprobado,
                (SUM(vig_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(vig_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(vig_' . $trims[$this->prm->trimestre - 1][2] . ')) AS vigente,
                (SUM(com_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(com_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(com_' . $trims[$this->prm->trimestre - 1][2] . ')) AS comprometido,
                (SUM(dev_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(dev_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(dev_' . $trims[$this->prm->trimestre - 1][2] . ')) AS devengado,
                (SUM(eje_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(eje_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(eje_' . $trims[$this->prm->trimestre - 1][2] . ')) AS ejercido ';
        $porcentajePresupuesto = ' round(((100*(SUM(eje_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(eje_' . $trims[$this->prm->trimestre - 1][1] . ')
                        + SUM(eje_' . $trims[$this->prm->trimestre - 1][2] . ')))/(SUM(vig_' . $trims[$this->prm->trimestre - 1][0] . ')
                        + SUM(vig_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(vig_' . $trims[$this->prm->trimestre - 1][2] . '))),2) porcentajePresupuesto ';
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
        $toa_id = (isset($this->prm->toa_id) && $this->prm->toa_id != '' ? ' AND toa_id=' . $this->prm->toa_id : '');

        $this->conectar();
        $this->setSql('
            SELECT
                gpo_id, ur_id, prg_id,spr_id, py_id, oa_id, prg_nombre, spr_nombre, oa_nombre,
                /*SUM(pre_aprobado) AS aprobado,
                SUM(pre_vigente) AS vigente_anual, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                SUM(pre_ejercido) AS ejercido, */
                ' . $presupuesto . ',
                epg_id, py_nombre, toa_id, oa_origen,metas,
                pdf_id, meta.pdi_id, ind_id, meta.pii_id, o.usr_fecha,
                -- round(((SUM(pre_devengado)/SUM(pre_vigente))*100),2) porcentajePresupuesto,
                ' . $porcentajePresupuesto . ',
                hms_mensaje, eme_id, porcentajeMeta, ind_limSupAmar, ind_limInfVer, ind_limInfAmar, ind_limSupRojo,ses_id,
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
                        AND hm1.hms_trimestre = ' . $this->prm->trimestre . ' AND tim_id = 1
                        AND eme_id IS NOT NULL
                        GROUP BY pii_id,epg_id
                    ) AS elimina ON  hm2.usr_fecha=fecha2
                    WHERE eme_id IS NOT NULL
                )hm ON cms.msj_id = hm.msj_id
                AND hm.hms_trimestre = ' . $this->prm->trimestre . " AND tim_id = 1
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
            WHERE gpo_id= :gpo_id and ur_id= :ur_id " . $filtro . $toa_id . $fecha_limite . '
            AND ((toa_id=1 AND eoa_id = 4) OR (toa_id=2 AND eoa_id=5))
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
            HAVING aprobado > 0 OR vigente > 0 OR sum_mact_original > 0 OR sum_mact_modificado > 0;
        ');
        $params = [
            ':trimestre' => $this->prm->trimestre,
            ':gpo_id'    => $this->prm->gpo_id,
            ':ur_id'     => $this->prm->ur_id,
        ];
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() == 0) {
            $titulo = 'NO SE ENCONTRARON REGISTROS CON LOS PARÁMETROS SELECCIONADOS';
        }

        $htm = '
            <legend style="text-align:center">' . $titulo . '</legend><br>
            <table  id="observarActividad" class="table table table-bordered table-hover">';

        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';
        $color = '';
        $color1 = '';
        $estilo_btn_1 = '';
        $estilo_btn_2 = $leyenda = '';
        $cambioClase = true;
        while ($row = $this->extraer()) {
            if (isset($row['eme_id'])) {
                $cambioClase = true;
                if ($row['eme_id'] == 1) {
                    $color = '#FF8000';
                    $leyenda = 'CON OBSERVACIÓN GUARDADA';
                } else {
                    $color = '#FF1E2D';
                    $leyenda = 'CON OBSERVACIÓN';
                }
                if (($row['eme_id'] == 3)) {
                    if ($row['observaciones'] > 0 && $row['eme_id'] != 3) {
                        $cambioClase = true;
                        $color = '#FF8000';
                        $leyenda = 'CON OBSERVACIÓN GUARDADA';
                    } else {
                        $cambioClase = false;
                    }
                }
            } else {
                $cambioClase = false;
            }
            $condicionEstatus = ($row['observaciones'] != '' && $row['observaciones'] > 0 && $row['eme_id'] != 3 ? "<label id='obs" . $row['epg_id'] . "' style='color:" . $color . ";'>" . $leyenda . '</label>' : "<label id='obs" . $row['epg_id'] . "' style='color: #00950A;'>SIN OBSERVACIÓN</label>");

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
                $clase = $cambioClase ? 'class="bg-conobservacion" style="background-color:#C39BD3;"' : 'class="bg-sinobservacion" style="background-color:#9CCC65;"';
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
                        <td ' . $clase . ' align="right" colspan="2">' . $row['aprobado'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['vigente'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['comprometido'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['devengado'] . '</td>
                        <td ' . $clase . ' align="right" colspan="2">' . $row['ejercido'] . '</td>
                        <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small ' . $color . '" title="Porcentaje de PRESUPUESTO ' . ($row['porcentajePresupuesto'] != null ? $row['porcentajePresupuesto'] : '0.00') . ' %" ' . $estilo_btn_1 . '></button></td>
                        <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small ' . $color1 . '" title="Porcentaje de la META ' . ($row['porcentajeMeta'] != null ? $row['porcentajeMeta'] : '0.00') . ' %" ' . $estilo_btn_2 . ' ></button></td>
                        <td ' . $clase . ' align="center" colspan="1" >
                        <label style="font-size: 21px;">
                            ' . $evidencia_solicitada . '
                        </label>
                        </td>
                        <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button" id="verMetasActividades" data-bs-toggle="collapse" data-bs-target="#collapseMetas' . $row['epg_id'] . '" pii_id="' . $row['pii_id'] . '" aria-expanded="false" aria-controls="collapseMetas' . $row['epg_id'] . '" pii_id="' . $row['pii_id'] . '" title="Ver el Indicador/Meta de la Actividad" class="verActividad btn btn-outline-secondary"  epg_id="' . $row['epg_id'] . '" pii_id="' . $row['pii_id'] . '">
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
                    <tr id="collapseMetas' . $row['epg_id'] . '" pii_id="' . $row['pii_id'] . '" class="bg-sinobservacion collapse" data-bs-parent="#observarActividad" style="background-color:#EEEEEE;">
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
                    ' . $this->toa . '
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
                    ' . $this->toa . '
                });
            });
            console.log("toa_id: " + "' . $toa_id . '");

        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->epg_id . '');

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'btnEliminar';
        $btnEliminar->type = 'button';
        $btnEliminar->title = 'Eliminar ??';
        $btnEliminar->class = 'btn-outline-danger ';
        $btnEliminar->icon = 'trash';

        $this->conectar();
        $this->setSql('
            SELECT
                gpo_id, ur_id, prg_id, spr_id, py_id, oa_id,nim_id,ind_id, ind_nombreMIR, ind_definicionMIR,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, u.umi_id as umi_id,
                u.umi_nombre as umi_nombre, /*i.umi_id1 as umi_mir,*/ u.umi_nombre as umiMir_nombre,
                if(tiv_id=1,2,tiv_id) tiv_id, if(tiv_id=1,"ACUMULADO",tiv_nombre) tiv_nombre,ses_id,ses_nombre, frm_id,frm_nombre,
                m.pii_id, m.mac_descripcion, pdi_id, epg_id, m.mac_agrupadorPresupuestal,
                mac_original, if(mt.tri_id = 1, mt.mact_original, NULL) ori_trimestre1,  if(mt.tri_id = 2, mt.mact_original, NULL) ori_trimestre2,
                if(mt.tri_id = 3, mt.mact_original, NULL) ori_trimestre3, if(mt.tri_id = 4, mt.mact_original, NULL) ori_trimestre4,
                mac_modificado, mac_alcanzado, toa_id
            FROM cat_MetaActividad m
            INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            INNER JOIN enc_Programatico using(epg_id)
            INNER JOIN cat_ObraActividad using(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_Indicador i using(nim_id,ind_id)
            INNER JOIN cat_TipoIndicador using(tpi_id)
            INNER JOIN cat_Dimension using(dim_id)
            INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
            INNER JOIN cat_TipoValor using(tiv_id)
            INNER JOIN cat_SentidoEsperado using(ses_id)
            INNER JOIN cat_FrecuenciaMedicion using(frm_id)
            WHERE gpo_id = :gpo_id and ur_id = :ur_id and epg_id = :epg_id
            AND ((toa_id=1 AND eoa_id=4) OR (toa_id=2 AND eoa_id=5)) AND tri_id = :trimestre
            GROUP BY gpo_id,ur_id,pii_id, ind_id
            ORDER BY m.pii_id;
        ');

        $params = [
            ':trimestre' => $this->prm->trimestre,
            ':gpo_id'    => $this->prm->gpo_id,
            ':ur_id'     => $this->prm->ur_id,
            ':epg_id'    => $this->prm->epg_id,
        ];

        $this->consultar($params);

        while ($ind = $this->extraer()) {
            $this->mixHtml('
                <table class="table table-hover table-bordered" >
                    <tr class="warning" ><th style="font-size: 16px; background-color: #FACC2E;" colspan=19>INDICADOR: ' . ($ind['ind_id']) . '</th></tr>
                    <tr>
                        <td>Nombre:</td>
                        <td colspan=7>' . ($ind['ind_nombreMIR']) . '</td>
                        <td style="background-color: #FF0000; text-align:center" ><B> OBSERVAR</B> </td>
                    </tr>
                    <tr>
                        <td>Definición:</td>
                        <td colspan=7>' . ($ind['ind_definicionMIR']) . '</td>
                        <td  align="center" colspan="1" rowspan="2" >
                            <button type="button" id="enviarMensaje"  icon="glyphicon-comment" title="Opción para capturar observaciones" class="verModalMensaje btn btn-outline-secondary" ind_id="' . ($ind['ind_id']) . '" epg_id = "' . $this->prm->epg_id . '"  >
                                <i class="fa fa-search" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>Método de Calculo:</td><td colspan=5>' . ($ind['ind_formulaCalculo']) . '</td>
                    </tr>
                    <tr>
                        <th>Tipo</th>
                        <th>Dimensión</th>
                        <th>Frecuencia de medición</th>
                        <th>Unidad de Medida</th>
                        <th colspan="2">Tipo de Valor</th>
                        <th colspan="3">Sentido Esperado</th>
                    </tr>
                    <tr>
                        <td>' . ($ind['tpi_nombre']) . '</td>
                        <td>' . ($ind['dim_nombre']) . '</td>
                        <td>' . ($ind['frm_nombre']) . '</td>
                        <td>' . ($ind['umiMir_nombre']) . '</td>
                        <td colspan="2">' . ($ind['tiv_nombre']) . '</td>
                        <td colspan="3">' . ($ind['ses_nombre']) . '</td>
                    </tr>
            ');
            $con = new AccesoDatos();
            $con->conectar();
            $sub_consultas = '';
            for ($i = 1; $i <= 4; $i++) {
                $sub_consultas .= '
                    (
                        select round(((mmt.mact_alcanzado / mm.mac_modificado)*100), 2) as ava' . $i . '
                        from cat_MetaActividad mm
                            inner join cat_MetaActividadTrimestre mmt USING(pii_id, epg_id)
                        where mmt.pii_id = m.pii_id and mmt.epg_id = m.epg_id and mmt.tri_id = ' . $i . '
                    ) ava' . $i . ','; //Porcentaje Avance
                $sub_consultas .= '
                    (
                        select mmt.mact_original as ori_trimestre' . $i . '
                        from cat_MetaActividad mm
                            inner join cat_MetaActividadTrimestre mmt USING(pii_id, epg_id)
                        where mmt.pii_id = m.pii_id and mmt.epg_id = m.epg_id and mmt.tri_id = ' . $i . '
                    ) ori_trimestre' . $i . ','; //Original
                $sub_consultas .= '
                    (
                        select mmt.mact_modificado as mod_trimestre' . $i . '
                        from cat_MetaActividad mm
                            inner join cat_MetaActividadTrimestre mmt USING(pii_id, epg_id)
                        where mmt.pii_id = m.pii_id and mmt.epg_id = m.epg_id and mmt.tri_id = ' . $i . '
                    ) mod_trimestre' . $i . ','; //Modificado
                $sub_consultas .= '
                    (
                        select mmt.mact_alcanzado as alc_trimestre' . $i . '
                        from cat_MetaActividad mm
                            inner join cat_MetaActividadTrimestre mmt USING(pii_id, epg_id)
                        where mmt.pii_id = m.pii_id and mmt.epg_id = m.epg_id and mmt.tri_id = ' . $i . '
                    ) alc_trimestre' . $i . ','; //Alcanzado
            }
            $con->setSql('
                SELECT
                    gpo_id,ur_id,pii_id,mac_descripcion,mac_anio,mac_valor,pii_id as mem_id,
                    ' . $sub_consultas . '
                    mac_original, mac_modificado, mac_alcanzado,mac_id,
                round(((mac_alcanzado/mac_modificado)*100),2) as porcentajeMeta
                FROM
                    cat_MetaActividad m
                    INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
                    INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                    INNER JOIN enc_Programatico USING(epg_id)
                WHERE ind_id=? and epg_id=?
                GROUP BY gpo_id,ur_id,m.pii_id;
            ');

            $con->consultar([$ind['ind_id'],$this->prm->epg_id]);

            while ($meta = $con->extraer()) {

                $texto = '';
                $botonEnviar = '';

                $this->mixHtml('
                    <tr class="warning">
                        <th></th>
                        <th colspan = "12">
                            <table class="table table-hover table-bordered">
                                <tr>
                                    <th colspan ="12">META NÚMERO: ' . $meta['mac_id'] . '</th>
                                </tr><tr>
                                    <th colspan ="12" align="right">PORCENTAJE ANUAL ACUMULADO <button type="button" class=" btn btn-small " title="">' . $meta['porcentajeMeta'] . ' %</button></th>
                                </tr><tr>
                                    <th>Descripción:</th>
                                    <th colspan ="12">' . ($meta['mac_descripcion']) . '</th>
                                </tr><tr>
                                    <td>Unidad de Medida:</td>
                                    <td colspan ="12">' . $ind['umi_nombre'] . '</td>
                                </tr><tr>
                                    <th colspan="4" align=center>AVANCE DE METAS</th>
                                </tr><tr>
                                    <th>TRIMESTRE</th>
                                    <th style="text-align:center">PROGRAMACIÓN <br>INICIAL</br></th>
                                    <th style="text-align:center">PROGRAMACIÓN <br>ACTUALIZADA</br></th>
                                    <th style="text-align:center">METAS <br>ALCANZADAS</br></th>
                                    <th style="text-align:center">PORCENTAJE DE <br>CUMPLIMIENTO</br> TRIMESTRAL</th>
                                </tr><tr>
                                </tr><tr>
                                    <th>Primero</th>
                                    <td>' . $meta['ori_trimestre1'] . '</td>
                                    <td>' . $meta['mod_trimestre1'] . '</td>
                                    <td>' . $meta['alc_trimestre1'] . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['ava1'] . '</button></td>
                                    <!--<td rowspan="5">' . $texto . '</td>
                                    <td>' . $botonEnviar . '</td>-->
                                </tr><tr>
                                    <th>Segundo</th>
                                    <td>' . $meta['ori_trimestre2'] . '</td>
                                    <td>' . $meta['mod_trimestre2'] . '</td>
                                    <td>' . $meta['alc_trimestre2'] . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['ava2'] . '</button></td>
                                    <!--<td></td>-->
                                </tr>
                                <tr>
                                    <th>Tercero</th>
                                    <td>' . $meta['ori_trimestre3'] . '</td>
                                    <td>' . $meta['mod_trimestre3'] . '</td>
                                    <td>' . $meta['alc_trimestre3'] . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['ava3'] . '</button></td>
                                </tr>
                                <tr>
                                    <th>Cuarto</th>
                                    <td>' . $meta['ori_trimestre4'] . '</td>
                                    <td>' . $meta['mod_trimestre4'] . '</td>
                                    <td>' . $meta['alc_trimestre4'] . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['ava4'] . '</button></td>
                                </tr>
                                <tr>
                                    <th>Acumulado</th>
                                    <td>' . $meta['mac_original'] . '</td>
                                    <td>' . $meta['mac_modificado'] . '</td>
                                    <td>' . $meta['mac_alcanzado'] . '</td>
                                    <td><button type="button" class=" btn btn-small " title="">' . $meta['porcentajeMeta'] . '</button></td>
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
                    trimestre:' . $this->prm->trimestre . ',
                    hms_trimestre:' . $this->prm->trimestre . ',
                    tim_id: 1,
                    eme_id: 2,
                    epg_id:"' . $this->prm->epg_id . '",
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id
                    . $this->toa . '
                });
            });

            $(".verModalMensaje").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"nuevaObservacion",
                    abc:"a",
                    epg_id:$(this).attr("epg_id"),
                                ind_id:$(this).attr("ind_id"),
                    trimestre:' . $this->prm->trimestre . ',
                    epf_id: 0
                    ' . $this->toa . '
                });
            });
        ');
    }

    private function nuevaObservacion()
    {

        $this->setContenedor('divContenedorGeneralModal');
        $trimestreSeleccionado = $this->prm->trimestre;
        $abc = $this->prm->abc;
        $hms_id = (isset($this->prm->hms_id)) ? $this->prm->hms_id : '';
        $fecha_limite = '';

        $this->conectar();
        $this->setSql('
            SELECT nim_id, m.pii_id, m.usr_id, pdi_id, ind_id,
                prg_id, prg_nombre, spr_id, spr_nombre,
                py_id, py_nombre, ind_nombre,oa_id,oa_nombre,
                CONCAT(gpo_id,ur_id,"-", ur_nombre) UR,
                (sum(mact_alcanzado)/sum(mact_modificado))*100 porcentaje
            FROM cat_MetaActividad m
            INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            INNER JOIN cat_Indicador USING(ind_id,nim_id)
            INNER JOIN enc_Programatico USING(epg_id)
            INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id, oa_id)
            INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            WHERE gpo_id = ?
            AND ur_id = ?
            AND epg_id = ?
            AND ind_id= ?
            AND nim_id=4
            GROUP BY epg_id,pii_id;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->epg_id,
            $this->prm->ind_id,
        ];

        $por = new AccesoDatos();
        $por->conectar();
        $por->setSql('
            SELECT tri_id
            From cat_MetaActividadTrimestre mt
            INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
            WHERE
            epg_id = ?
            AND ind_id=?
        ');
        $por->consultar([$this->prm->epg_id,$this->prm->ind_id]);
        $tri = $por->extraer();
        $por->desconectar();

        if ($abc == 'c') {
            $hms = new AccesoDatos();
            $hms->conectar();
            $hms->setSql('
                SELECT hms_id, hms_mensaje, eme_id, hms_mensajeRespuesta, hms_trimestre
                FROM cat_HistorialMensajeSeguimiento
                WHERE
                hms_id=?
                AND hms_trimestre=?
            ');
            $hms->consultar([$hms_id,$this->prm->trimestre]);
            $men = $hms->extraer();
            $hms->desconectar();
        }

        foreach ($tri as $v) {
            if ($tri = 1) {
                $t1 = new AccesoDatos();
                $t1->conectar();
                $t1->setSql('
                    SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                    (CASE
                        WHEN tri_id=1 THEN (mact_alcanzado*100)/IF ( mact_modificado > 0, mact_modificado, mact_original)
                    END) AS porcentaje1
                    From cat_MetaActividadTrimestre mt
                    INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                    WHERE
                    epg_id = ?
                    AND ind_id= ?
                    AND tri_id=1
                ');
                $t1->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                $dat1 = $t1->extraer();
                $t1->desconectar();

            }
            if ($tri = 2) {
                $t2 = new AccesoDatos();
                $t2->conectar();
                $t2->setSql('
                    SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                    (CASE
                        WHEN tri_id=2 THEN (mact_alcanzado*100)/mact_modificado
                    END) AS porcentaje2
                    From cat_MetaActividadTrimestre mt
                    INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                    WHERE
                    epg_id = ?
                    AND ind_id= ?
                    AND tri_id=2
                ');
                $t2->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                $dat2 = $t2->extraer();
                $t2->desconectar();

            }
            if ($tri = 3) {
                $t3 = new AccesoDatos();
                $t3->conectar();
                $t3->setSql('
                    SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                    (CASE
                        WHEN tri_id=3 THEN (mact_alcanzado*100)/mact_modificado
                    END) AS porcentaje3
                    From cat_MetaActividadTrimestre mt
                    INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                    WHERE
                    epg_id = ?
                    AND ind_id= ?
                    AND tri_id=3
                ');
                $t3->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                $dat3 = $t3->extraer();
                $t3->desconectar();
            }
            if ($tri = 4) {
                $t4 = new AccesoDatos();
                $t4->conectar();
                $t4->setSql('
                    SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                    (CASE
                        WHEN tri_id=4 THEN (mact_alcanzado*100)/mact_modificado
                    END) AS porcentaje4
                    From cat_MetaActividadTrimestre mt
                    INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                    WHERE
                    epg_id = ?
                    AND ind_id= ?
                    AND tri_id=4
                ');
                $t4->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                $dat4 = $t4->extraer();
                $t4->desconectar();
            }
        }
        $this->consultar($params);
        $this->desconectar();
        $datos = $this->extraer();


        $trims = [['ene', 'feb', 'mar'], ['abr', 'may', 'jun'], ['jul', 'ago', 'sep'], ['oct', 'nov', 'dic']];
        $presupuesto = '(SUM(apr_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(apr_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(apr_' . $trims[$this->prm->trimestre - 1][2] . ')) AS aprobado,
                (SUM(vig_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(vig_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(vig_' . $trims[$this->prm->trimestre - 1][2] . ')) AS vigente,
                (SUM(com_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(com_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(com_' . $trims[$this->prm->trimestre - 1][2] . ')) AS comprometido,
                (SUM(dev_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(dev_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(dev_' . $trims[$this->prm->trimestre - 1][2] . ')) AS devengado,
                (SUM(eje_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(eje_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(eje_' . $trims[$this->prm->trimestre - 1][2] . ')) AS ejercido ';
        $porcentajePresupuesto = ' round(((100*(SUM(eje_' . $trims[$this->prm->trimestre - 1][0] . ') + SUM(eje_' . $trims[$this->prm->trimestre - 1][1] . ')
                        + SUM(eje_' . $trims[$this->prm->trimestre - 1][2] . ')))/(SUM(vig_' . $trims[$this->prm->trimestre - 1][0] . ')
                        + SUM(vig_' . $trims[$this->prm->trimestre - 1][1] . ') + SUM(vig_' . $trims[$this->prm->trimestre - 1][2] . '))),2) porcentajePresupuesto ';
        switch ($this->prm->trimestre) {
            case 1:
                $fecha_limite = " and o.usr_fecha <= '2026-03-31' ";
                break;
            case 2:
                $fecha_limite = " and o.usr_fecha <= '2026-06-30' ";
                break;
            case 3:
                $fecha_limite = " and o.usr_fecha <= '2026-09-30' ";
                break;
            case 4:
                $fecha_limite = " and o.usr_fecha <= '2026-12-31' ";
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
        $toa_id = (isset($this->prm->toa_id) && $this->prm->toa_id != '' ? ' AND toa_id=' . $this->prm->toa_id : '');

        $this->conectar();
        $this->setSql('
            SELECT
                gpo_id, ur_id, prg_id,spr_id, py_id, oa_id, prg_nombre, spr_nombre, oa_nombre,
                /*SUM(pre_aprobado) AS aprobado,
                SUM(pre_vigente) AS vigente_anual, SUM(pre_comprometido) AS comprometido, SUM(pre_devengado) AS devengado,
                SUM(pre_ejercido) AS ejercido, */
                ' . $presupuesto . ',
                epg_id, py_nombre, toa_id, oa_origen,metas,
                pdf_id, meta.pdi_id, ind_id, meta.pii_id, o.usr_fecha,
                -- round(((SUM(pre_devengado)/SUM(pre_vigente))*100),2) porcentajePresupuesto,
                ' . $porcentajePresupuesto . ',
                hms_mensaje, eme_id, porcentajeMeta, ind_limSupAmar, ind_limInfVer, ind_limInfAmar, ind_limSupRojo,ses_id,
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
                        AND hm1.hms_trimestre = ' . $this->prm->trimestre . ' AND tim_id = 1
                        AND eme_id IS NOT NULL
                        GROUP BY pii_id,epg_id
                    ) AS elimina ON  hm2.usr_fecha=fecha2
                    WHERE eme_id IS NOT NULL
                )hm ON cms.msj_id = hm.msj_id
                AND hm.hms_trimestre = ' . $this->prm->trimestre . " AND tim_id = 1
                WHERE mt.tri_id = :trimestre
                AND ci.ind_id = :ind_id
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
            WHERE gpo_id=:gpo_id and ur_id= :ur_id " . $filtro . $toa_id . $fecha_limite . '
            AND epg_id = :epg_id AND ind_id = :ind_id
            AND ((toa_id=1 AND eoa_id = 4) OR (toa_id=2 AND eoa_id=5))
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
            HAVING aprobado > 0 OR vigente > 0 OR sum_mact_original > 0 OR sum_mact_modificado > 0;
        ');
        $params = [
            ':trimestre' => $this->prm->trimestre,
            ':gpo_id'    => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id'     => $_SESSION[_S]['clasAdm-ur'],
            ':epg_id'    => $this->prm->epg_id,
            ':ind_id'    => $this->prm->ind_id,
        ];

        $this->consultar($params);
        $this->desconectar();
        $pres = $this->extraer();


        $btnEnviar = new stdClass();
        $btnEnviar->id = 'btnEnviar';
        $btnEnviar->type = 'button';
        $btnEnviar->title = 'Enviar';
        $btnEnviar->class = 'btn btn-outline-success';
        $btnEnviar->icon = 'fa fa-paper-plane me-1';
        $btnEnviar->value = 'Enviar';

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'btnGuardar';
        $btnGuardar->type = 'button';
        $btnGuardar->title = 'Guardar mensaje';
        $btnGuardar->class = 'btn btn-outline-success';
        $btnGuardar->icon = 'fa-solid fa-floppy-disk me-1';



        if ($abc == 'a') {
            $btnGuardar->value = 'Guardar';
            $metodo = 'Observacion';
            $mensajeEstado = 'Observación guardada satisfactoriamente';
            $ponEnviar = $this->ponBoton($btnEnviar);
            $input = '<input type="checkbox" id="chb_req_evi" required>';
            $label = '<label for="chb_req_evi">Requerir evidencias</label>';
        } else {
            $btnGuardar->value = 'Actualizar';
            $metodo = 'ModificarMensaje';
            $mensajeEstado = 'Observación modificada satisfactoriamente';
            $ponEnviar = '';
            $input = '';
            $label = '';
        }

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 1000;
        $descripcion->width = '300px';
        $descripcion->height = '150px';
        if ($abc == 'c') {
            $descripcion->texto = $men['hms_mensaje'];
        }

        //Semaforizacion
        $color1 = '';
        $color2 = '';
        $color3 = '';
        $color4 = '';
        $trimestreactual = '';
        $porcentajealc = '';
        if ($dat1['porcentaje1'] >= 0 && $dat1['porcentaje1'] < 59.99) {
            $color1 = 'color="white" style="background-color: #b20000;"'; // ROjo
        }
        if ($dat1['porcentaje1'] >= 60 && $dat1['porcentaje1'] < 84.99) {
            $color1 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
        }
        if ($dat1['porcentaje1'] >= 85 && $dat1['porcentaje1'] <= 115) {
            $color1 = 'color="white" style="background-color: #35B200;"'; //verde
        }
        if ($dat1['porcentaje1'] > 115) {
            $color1 = 'color="white" style="background-color: #b20000;"'; //rojo
        }
        //tri 2
        if ($dat2['porcentaje2'] >= 0 && $dat2['porcentaje2'] < 59.99) {
            $color2 = 'color="white" style="background-color: #b20000;"'; // ROjo
        }
        if ($dat2['porcentaje2'] >= 60 && $dat2['porcentaje2'] < 84.99) {
            $color2 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
        }
        if ($dat2['porcentaje2'] >= 85 && $dat2['porcentaje2'] <= 115) {
            $color2 = 'color="white" style="background-color: #35B200;"'; //verde
        }
        if ($dat2['porcentaje2'] > 115) {
            $color2 = 'color="white" style="background-color: #b20000;"'; //rojo
        }

        //tri 3
        if ($dat3['porcentaje3'] >= 0 && $dat3['porcentaje3'] < 59.99) {
            $color3 = 'color="white" style="background-color: #b20000;"'; // ROjo
        }
        if ($dat3['porcentaje3'] >= 60 && $dat3['porcentaje3'] < 84.99) {
            $color3 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
        }
        if ($dat3['porcentaje3'] >= 85 && $dat3['porcentaje3'] <= 115) {
            $color3 = 'color="white" style="background-color: #35B200;"'; //verde
        }
        if ($dat3['porcentaje3'] > 115) {
            $color3 = 'color="white" style="background-color: #b20000;"'; //rojo
        }

        //tri 4
        if ($dat4['porcentaje4'] >= 0 && $dat4['porcentaje4'] < 59.99) {
            $color4 = 'color="white" style="background-color: #b20000;"'; // ROjo
        }
        if ($dat4['porcentaje4'] >= 60 && $dat4['porcentaje4'] < 84.99) {
            $color4 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
        }
        if ($dat4['porcentaje4'] >= 85 && $dat4['porcentaje4'] <= 115) {
            $color4 = 'color="white" style="background-color: #35B200;"'; //verde
        }
        if ($dat4['porcentaje4'] > 115) {
            $color4 = 'color="white" style="background-color: #b20000;"'; //rojo
        }
        if ($this->prm->trimestre == 1) {
            $trimestreactual = 'PRIMER TRIMESTRE';
            $porcentajealc = $dat1['porcentaje1'];
        } elseif ($this->prm->trimestre == 2) {
            $trimestreactual = 'SEGUNDO TRIMESTRE';
            if (isset($dat2['porcentaje2'])) {
                $porcentajealc = $dat2['porcentaje2'];
            } else {
                $porcentajealc = '0.00';
            }
        } elseif ($this->prm->trimestre == 3) {
            $trimestreactual = 'TERCER TRIMESTRE';
            if (isset($dat3['porcentaje3'])) {
                $porcentajealc = $dat3['porcentaje3'];
            } else {
                $porcentajealc = '0.00';
            }
            # code...
        } elseif ($this->prm->trimestre == 4) {
            $trimestreactual = 'CUARTO TRIMESTRE';
            if (isset($dat4['porcentaje4'])) {
                $porcentajealc = $dat4['porcentaje4'];
            } else {
                $porcentajealc = '0.00';
            }
        }
        $html = '
            <table>
                <table  class="table table table-bordered table-hover">
                    <tr>
                        <th class="bg-encabezado" style="text-align:center" colspan="1">PR:</th>
                        <th class="bg-encabezado" style="text-align:center" colspan="1">' . $datos['prg_id'] . '</th>
                        <th style="text-align:left" colspan="17">' . $datos['prg_nombre'] . '</th>
                    </tr>
                    <tr>
                        <th class="bg-encabezado" style="text-align:center" colspan="1">SPR:</th>
                        <th class="bg-encabezado" style="text-align:center" colspan="1">' . $datos['spr_id'] . '</th>
                        <td align="left" colspan="17">' . $datos['spr_nombre'] . '</td>
                    </tr>
                    <tr>
                        <th class="bg-encabezado" style="text-align:center" colspan="1">PYT:</th>
                        <th class="bg-encabezado" style="text-align:center" colspan="1">' . $datos['py_id'] . '</th>
                        <td align="left" colspan="5">' . $datos['py_nombre'] . '</td>
                        <th class="bg-encabezado" align="center" colspan="2">APROBADO</th>
                        <th class="bg-encabezado" align="center" colspan="2">MODIFICADO</th>
                        <th class="bg-encabezado" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="bg-encabezado" align="center" colspan="2">DEVENGADO</th>
                        <th class="bg-encabezado" align="center" colspan="2">EJERCIDO</th>
                    </tr>
                    <tr>
                        <td align="center" colspan="1"></td>
                        <td align="center" colspan="1">' . $datos['prg_id'] . '</td>
                        <td align="center" colspan="1">' . $datos['spr_id'] . '</td>
                        <td align="center" colspan="1">' . $datos['py_id'] . '</td>
                        <td align="center" colspan="1">' . $datos['oa_id'] . '</td>
                        <td align="left" colspan="2">' . $datos['oa_nombre'] . '</td>
                        <td align="right" colspan="2">' . $pres['aprobado'] . '</td>
                        <td align="right" colspan="2">' . $pres['vigente'] . '</td>
                        <td align="right" colspan="2">' . $pres['comprometido'] . '</td>
                        <td align="right" colspan="2">' . $pres['devengado'] . '</td>
                        <td align="right" colspan="2">' . $pres['ejercido'] . '</td>
                    </tr>
                </table>
                <tr>
                    <td colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="black">
                        INDICADOR: ' . $datos['ind_id'] . ' ' . $datos['ind_nombre'] . '</font></b>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color1 . '>
                            PORCENTAJE ALCANZADO DEL INDICADOR ' . $trimestreactual . ' : ' . $porcentajealc . '%</b>
                        </font>
                    </td>
                </tr><br>
            </table>
            <br>
            <table class="table table table-bordered table-hover" >
                <tr class="warning">
                    <th colspan="2">OBSERVACIÓN</th>
                </tr>
                <tr>
                    <td colspan="2"><label id="lbl_count_char"></label>' . $this->ponTextArea($descripcion) . '
                        ' . $input . '
                        ' . $label . '
                    </td>
                </tr>
                <tr>
                    <td>' . $ponEnviar . '</td>
                    <td style="text-align:right;" >' . $this->ponBoton($btnGuardar) . '</td>
                </tr>
            </table>
        ';

        $this->mixHtml('
            <div id="myModal" class="modal">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">NUEVA OBSERVACIÓN: &nbsp;&nbsp;&nbsp;&nbsp;' . $datos['UR'] . ' </h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
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

            });
            $("#lbl_count_char").text("Caracteres restantes: " + $("#descripcion").attr("maxlength"));
            $("#descripcion").keyup(function () {
                var max = $(this).attr("maxlength");
                var len = $(this).val().length;
                if((max - len) == 0) {
                    $("#lbl_count_char").html("<any style=\"color:red;\">Caracteres restantes: " + (max - len) + "</any>");
                } else {
                    $("#lbl_count_char").text("Caracteres restantes: " + (max - len));
                }
            });

            $("#btnGuardar").click(function(){
                $("#btnGuardar").attr("disabled","true");
                $("#btnEnviar").attr("disabled","true");
                comunica("' . $this->pantalla . '",{
                    metodo: "' . $metodo . '",
                    hms_mensaje: $("#descripcion").val(),
                    nim_id: ' . $datos['nim_id'] . ',
                    pii_id: ' . $datos['pii_id'] . ',
                    epg_id: "' . $this->prm->epg_id . '",
                    trimestre: ' . $this->prm->trimestre . ',
                    hms_trimestre: ' . $this->prm->trimestre . ',
                    epf_id: ' . $this->prm->epf_id . ',
                    gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                    ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                    ese_id: $("#chb_req_evi").is(":checked") ? 2 : 1,
                    hms_mensual: 0,
                    abc: "' . $abc . '",
                    hms_mensajeRespuesta: "",
                    tim_id: 1,
                    eme_id: 1,
                    mensajeEstado: "' . $mensajeEstado . '"
                    ' . $this->toa . '
                    ' . ((isset($this->prm->hms_id)) ? ',hms_id:' . $this->prm->hms_id . '' : '') . '
                });
            });
            $("#btnEnviar").click(function(){
                $("#btnEnviar").attr("disabled","true");
                $("#btnGuardar").attr("disabled","true");
                comunica("' . $this->pantalla . '",{
                    metodo: "Observacion",
                    hms_mensaje: $("#descripcion").val(),
                    nim_id: ' . $datos['nim_id'] . ',
                    pii_id: ' . $datos['pii_id'] . ',
                    epg_id: "' . $this->prm->epg_id . '",
                    trimestre: ' . $this->prm->trimestre . ',
                    hms_trimestre: ' . $this->prm->trimestre . ',
                    epf_id: ' . $this->prm->epf_id . ',
                    gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                    ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                    ese_id: $("#chb_req_evi").is(":checked") ? 2 : 1,
                    hms_mensual: 0,
                    abc: "a",
                    hms_mensajeRespuesta: " ",
                    tim_id: 1,
                    eme_id: 2,
                    mensajeEstado: "Observación enviada satisfactoriamente"
                    ' . $this->toa . '
                });
            });
            $("#close").click(function(){ $("#myModal").hide(); });
            $("#myModal").modal().show();
        ');
    }

    private function Observacion()
    {
        $this->prm->hms_mensaje = addslashes($this->prm->hms_mensaje);
        $this->prm->mensajeEstado = (isset($this->prm->mensajeEstado) ? $this->prm->mensajeEstado : '');
        $this->prm->msj_id = MensajeSeguimiento::abc($this->prm);
        if ($this->prm->ese_id == 2) {
            $this->prm->hms_fechaSolicitudEvidencia = date('Y-m-d H:i:s');
        }
        $comunica = 'comunica("' . $this->pantalla . '",{';

        $this->prm->hms_id = HistorialMensajeSeguimiento::abc($this->prm); //Guardar, Enviar o Guardar y enviar.
        $this->mixScript(
            (isset($this->prm->eme_id) && $this->prm->eme_id != '') ?
            $comunica .'
                metodo: "verActividades",
                epg_id:"' . $this->prm->epg_id . '",
                gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                trimestre:' . $this->prm->hms_trimestre . '
                ' . $this->toa . '
            });
            '. $comunica .'
                metodo: "verMetas",
                epg_id:"' . $this->prm->epg_id . '",
                pii_id:"' . $this->prm->pii_id . '",
                gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                trimestre:' . $this->prm->hms_trimestre . '
                ' . $this->toa . '
            });
            notify("' . $this->prm->mensajeEstado . ' ");
            ' :
            $comunica .'
                metodo: "verActividades",
                epg_id:"' . $this->prm->epg_id . '",
                gpo_id:' . $this->prm->gpo_id . ',
                ur_id:' . $this->prm->ur_id . ',
                trimestre:' . $this->prm->hms_trimestre . '
                ' . $this->toa . '
            });
            notify("Mensaje enviado");
            ' . '
        ');
    }

    private function ModificarMensaje()
    {

        $this->conectar();
        $this->setSql('
            UPDATE cat_HistorialMensajeSeguimiento
            set hms_mensaje = "' . $this->prm->hms_mensaje . '"
            WHERE
            hms_id = ? AND hms_trimestre = ?
        ');

        $this->mixScript('
            comunica("' . $this->pantalla . '",{
                metodo: "verActividades",
                epg_id:"' . $this->prm->epg_id . '",
                gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                trimestre:' . $this->prm->hms_trimestre . '
                ' . $this->toa . '
            });
            notify("Observación modificado satisfactoriamente");
            $("#myModal").modal().hide();
        ');

        $this->consultar([$this->prm->hms_id,$this->prm->hms_trimestre]);
    }

    private function Historial()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $trimestreSeleccionado = $this->prm->trimestre;
        $condicionEstatus = $nombreTrimestre = '';
        switch ($trimestreSeleccionado) {
            case 1:
                $nombreTrimestre = 'PRIMER TRIMESTRE';
                break;
            case 2:
                $nombreTrimestre = 'SEGUNDO TRIMESTRE';
                break;
            case 3:
                $nombreTrimestre = 'TERCER TRIMESTRE';
                break;
            case 4:
                $nombreTrimestre = 'CUARTO TRIMESTRE';
                break;
        }

        $this->ActualizaVisto();
        $this->conectar();
        $this->setSql('
            SELECT
                distinct( hms_id ),ind_id, hms_mensaje, eme_id, usr_nombre, DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha,
                TIME(hm.usr_fecha) hora, msj_id,hms_id,hms_mensajeRespuesta, hm.ese_id, ese.ese_nombre, hm.usr_fecha,
                (
                    SELECT u.usr_nombre
                    FROM cat_Usuario cu
                    INNER JOIN ctrlUsuario.cat_Usuario u USING (usr_id)
                    WHERE cu.usr_id = hm.hms_respuesta
                ) usuarioRespuesta,
                IF(hm.hms_fechaRespuesta IS NULL," ",DATE_FORMAT(hm.hms_fechaRespuesta,"%d/%m/%y")) AS fechaRespuesta,
                IF(hm.hms_fechaRespuesta IS NULL," ",TIME(hm.hms_fechaRespuesta)) AS horaRespuesta
            FROM cat_MetaActividad m
            INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
            LEFT JOIN cat_MensajeSeguimiento ms using(pii_id, epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
            LEFT JOIN cat_EstadoSolicitudEvidencia ese USING(ese_id)
            INNER JOIN cat_Usuario u  on hm.usr_id = u.usr_id
            INNER JOIN ctrlUsuario.cat_Usuario usr on u.usr_id = usr.usr_id
            WHERE epg_id = ?
                AND  hm.tim_id = 1
                AND hms_trimestre = ?
                AND eme_id!=3
            ORDER BY hm.usr_fecha DESC;
        ');

        $this->consultar([$this->prm->epg_id,$trimestreSeleccionado]);
        $this->desconectar();
        $html = '
            <table class="table table table-bordered table-hover" >
                <tr class="info">
                    <th class="warning" style="text-align:center" align="left" colspan="7">OBSERVACIONES</th>
                    <th class="warning" style="text-align:center;height: 50px;line-height: 50px;white-space: nowrap;" colspan="4" rowspan="2">EVIDENCIAS</th>
                    <th class="warning" style="text-align:center;height: 50px;line-height: 50px;white-space: nowrap;" colspan="1" rowspan="2"></th>
                </tr>
                <tr class="warning">
                    <th class="warning" style="text-align:center" align="left" colspan="4">SEFIN</th>
                    <th class="warning" style="text-align:center" align="left" colspan="3">UR</th>
                </tr>
                <tr class="">
                    <th align="center">INDICADOR</th>
                    <th align="center">MENSAJE</th>
                    <th>ESTADO</th>
                    <th>USUARIO</th>
                    <th>FECHA</th>
                    <th align="center">RESPUESTA</th>
                    <th>USUARIO</th>
                    <th>FECHA</th>
                    <th style="background-color: #F7D358;" align="center">SOLICITAR</th>
                    <th align="center">ESTADO</th>
                    <th>VER</th>
                    <th>ACCIÓN</th>
                </tr>
                <tr>
        ';

        while ($row = $this->extraer()) {
            $botones = '';
            $btnEnviar = new stdClass();
            $btnEnviar->id = 'btnEnviar';
            $btnEnviar->type = 'button';
            $btnEnviar->title = 'Enviar observación';
            $btnEnviar->class = 'btn btn-outline-success enviar';
            $btnEnviar->icon = 'fa fa-paper-plane';
            $btnEnviar->attr = new stdClass();
            $btnEnviar->attr->msj_id = $row['msj_id'];
            $btnEnviar->attr->hms_id = $row['hms_id'];
            $btnEnviar->attr->epg_id = $this->prm->epg_id;

            $btnEliminar = new stdClass();
            $btnEliminar->id = 'btnEliminar';
            $btnEliminar->type = 'button';
            $btnEliminar->title = 'Eliminar mensaje';
            $btnEliminar->color = 'rojo';
            $btnEliminar->class = 'eliminar';
            $btnEliminar->icon = 'trash';
            $btnEliminar->attr = new stdClass();
            $btnEliminar->attr->msj_id = $row['msj_id'];
            $btnEliminar->attr->hms_id = $row['hms_id'];
            $btnEliminar->attr->epg_id = $this->prm->epg_id;

            $btnEditar = new stdClass();
            $btnEditar->id = 'btnEliminar';
            $btnEditar->type = 'button';
            $btnEditar->title = 'Editar Mensaje';
            $btnEditar->color = 'gris';
            $btnEditar->class = 'editar';
            $btnEditar->icon = 'pencil';
            $btnEditar->attr = new stdClass();
            $btnEditar->attr->msj_id = $row['msj_id'];
            $btnEditar->attr->hms_id = $row['hms_id'];
            $btnEditar->attr->ind_id = $row['ind_id'];
            $btnEditar->attr->epg_id = $this->prm->epg_id;

            //Obtiene el estado de los mensajes
            switch ($row['eme_id']) {
                case 1:
                    $condicionEstatus = 'GUARDADO';
                    $botones = '<td>' . $this->ponBoton($btnEnviar) . '&nbsp;&nbsp;' . $this->ponBoton($btnEliminar) . '&nbsp;&nbsp;' . $this->ponBoton($btnEditar) . '</td>';

                    break;
                case 2:
                    $condicionEstatus = '<label>ENVIADO</label>';
                    break;
                case 3:
                    $condicionEstatus = '<label>ELIMINADO</label>';
                    break;
                case 4:
                    $condicionEstatus = '<label>RESPUESTA ENVIADA</label>';
                    break;
                case 5:
                    $condicionEstatus = '<label>LEÍDO POR UR</label>';
                    break;
                case 6:
                    $condicionEstatus = '<label>RESPUESTA LEÍDA</label>';
                    break;
            }
            //Asignación del estado de la evidencia
            $estado_evi = '<label id="estado_evi_' . $row['hms_id'] . '">' . $row['ese_nombre'] . '</label>';

            $checked_evi = $row['ese_id'] > 1 ? 'checked' : '';
            $hidden_btn_evi = $row['ese_id'] > 2 ? 'inline;' : 'none;';
            $disabled_evi = $row['ese_id'] > 1 ? 'disabled' : '';
            $estilo = $row['ese_id'] > 1 ? '' : 'style="background-color: #F7D358;"';

            //Informacion de la consulta
            $html .= '
                <td>' . $row['ind_id'] . '</td>
                <td align="justify">' . $row['hms_mensaje'] . '</td>
                <td>' . $condicionEstatus . '</td>
                <td>' . $row['usr_nombre'] . '</td>
                <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                <td>' . $row['hms_mensajeRespuesta'] . '</td>
                <td>' . $row['usuarioRespuesta'] . '</td>
                <td>' . $row['fechaRespuesta'] . ' ' . $row['horaRespuesta'] . '</td>
                <td ' . $estilo . ' #F7D358;" align="center">
                    <input type="checkbox"
                    id="chb_req_evi_' . $row['hms_id'] . '"
                    class="chb_req_evi" hms_id="' . $row['hms_id'] . '"
                    value="1" ' . $checked_evi . ' ' . $disabled_evi . '
                    title="Esta casilla no puede desactivarse">
                </td>
                <td>
                    ' . $estado_evi . '
                </td>
                <td>
                    <button class="btn btn_ver_evi" style="color: green; display: ' . $hidden_btn_evi . '"
                    id="btn_ver_evi_' . $row['hms_id'] . '"  hms_id="' . $row['hms_id'] . '">
                    <i class="fa fa-file-pdf" aria-hidden="true"></i>
                    </button>
                </td>
                ' . $botones . '
                </tr>
            ';
        }
        $html .= '<table>';

        $this->mixHtml('
            <div id="myModal" class="modal" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">OBSERVAR AVANCE DE ACTIVIDADES - ' . $nombreTrimestre . ' - HISTORIAL</h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
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


        $script = '
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });

            $(".enviar").click(function() {
                $("#btnEnviar").attr("disabled","true");
                comunica("' . $this->pantalla . '", {
                    metodo:"EnviarEliminarMensaje",
                    abc: "c",
                    hms_mensajeRespuesta: " ",
                    msj_id: $(this).attr("msj_id"),
                    hms_id: $(this).attr("hms_id"),
                    trimestre: ' . $this->prm->trimestre . ',
                    epg_id: $(this).attr("epg_id")
                    ' . $this->gpo
                    . $this->ur
                    . $this->prg
                    . $this->spr
                    . $this->py
                    . $this->toa . '
                });
            });

            $(".eliminar").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"EnviarEliminarMensaje",
                    abc: "b",
                    msj_id: $(this).attr("msj_id"),
                    hms_id: $(this).attr("hms_id"),
                    trimestre: ' . $this->prm->trimestre . ',
                    epg_id: $(this).attr("epg_id")
                    ' . $this->gpo
                    . $this->ur
                    . $this->prg
                    . $this->spr
                    . $this->py
                    . $this->toa . '
                });
            });
            $(".chb_req_evi").change(function() {
                if($(this).is(":checked")) {
                    comunica("' . $this->pantalla . '", {
                        metodo:"solicitarEvidencias",
                        abc: "c",
                        hms_id: $(this).attr("hms_id"),
                        ese_id: 2,
                        hms_fechaSolicitudEvidencia: "' . $this->hoy . '",
                        epg_id: "' . $this->prm->epg_id . '",
                        trimestre: "' . $this->prm->trimestre . '"
                        ' . $this->gpo
                        . $this->ur
                        . $this->prg
                        . $this->spr
                        . $this->py . '
                        ' . ((isset($this->prm->toa_id) && $this->prm->toa_id != '') ? ',oa_id:' . $this->prm->toa_id . '' : ',oa_id:""') . '
                    });
                }
            });
            $(".btn_ver_evi").click(function() {
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo: "verEvidencias",
                    hms_id: $(this).attr("hms_id")
                });
            });

            $(".editar").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    abc:"c",
                    metodo:"nuevaObservacion",
                    epg_id:$(this).attr("epg_id"),
                    msj_id: $(this).attr("msj_id"),
                    hms_id: $(this).attr("hms_id"),
                    ind_id: $(this).attr("ind_id"),
                    trimestre:' . $this->prm->trimestre . ',
                    epf_id: 0
                    ' . $this->toa . '
                });
            });

            $("#myModal").modal().show();
            $("#close").click(function(){ $("#myModal").remove(); });
        ';

        $this->mixScript($script);
        //Actualiza el num de mensajes
        $this->mixScript(ControladorMensajes::consultaMensajes());
    }

    private function solicitarEvidencias()
    {
        HistorialMensajeSeguimiento::abc($this->prm);

        $this->mixScript('
            notify("Se ACTIVÓ el requerimiento de Evidencias para la Observación seleccionada.");
            $("#chb_req_evi_' . $this->prm->hms_id . '").prop("disabled", true);
            $("#estado_evi_' . $this->prm->hms_id . '").text("SOLICITADA");
            console.log("#icon_' . $this->prm->gpo_id . '-' . $this->prm->ur_id . '-' . $this->prm->prg_id . '-' . $this->prm->spr_id . '-' . $this->prm->py_id . '-' . $this->prm->oa_id . '");
            $("#icon_' . $this->prm->gpo_id . '-' . $this->prm->ur_id . '-' . $this->prm->prg_id . '-' . $this->prm->spr_id . '-' . $this->prm->py_id . '-' . $this->prm->oa_id . '").addClass("fa-circle-check text-danger");
        ');
    }

    private function verEvidencias()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $sql = '
            SELECT
            rev_nombreOriginal, rev_ruta
            FROM
            vin_HistorialRespaldo vhr
            INNER JOIN
            cat_RespaldoEvidencia cre ON vhr.rev_id = cre.rev_id
            WHERE
            vhr.hms_id = ?
            ORDER BY rev_fechaAltaEvidencia DESC
        ';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->hms_id]);
        $this->desconectar();
        $html = '
            <table class="table table table-bordered table-hover" >
                <tr class="warning">
                <th align="center">ARCHIVO</th>
                <th align="center">ENLACE</th>
                </tr>
        ';
        while ($row = $this->extraer()) {
            $html .= '
                <tr>
                    <td>' . explode('.', $row['rev_nombreOriginal'])[0] . '</td>
                    <td>
                    <a href="' . $row['rev_ruta'] . '" target="_blank">
                        <i class="fa fa-file-pdf" aria-hidden="true"></i> Abrir
                    </a>
                    </td>
                </tr>
            ';
        }
        $html .= '</table>';

        $this->mixHtml('
            <div id="modal_evidencias" class="modal" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">LISTA DE ARCHIVOS SUBIDOS COMO EVIDENCIAS</h4>
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
        $script = '
            $("#modal_evidencias").modal().show();
            $(".cerrar").click(function(){ $("#modal_evidencias").remove(); });
        ';
        $this->mixScript($script);
        //actualiza el estatus del mensaje
        $sql = '
            UPDATE
            cat_HistorialMensajeSeguimiento
            SET ese_id=5
            WHERE hms_id=? AND ese_id = 3;';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->hms_id]);
        $this->desconectar();
        $this->mixScript(ControladorMensajes::consultaMensajes());
    }

    private function EnviarEliminarMensaje()
    {
        $estado = $updateFecha = $texto = $color = $mensaje = '';

        switch ($this->prm->abc) {
            case 'b': //Eliminar
                $estado = '3';
                $mensaje = 'ELIMINADO';
                $texto = 'SIN OBSERVACION';
                $color = '00950A';
                $updateFecha = '';
                break;
            case 'c': //Enviar
                $estado = '2';
                $mensaje = 'ENVIADO';
                $texto = 'CON OBSERVACION';
                $color = 'FF1E2D';
                $updateFecha = ', usr_fecha="' . $this->hoy . '"';
                break;
        }

        $this->conectar();
        $this->setSql('
            UPDATE cat_HistorialMensajeSeguimiento SET eme_id=' . $estado . $updateFecha . ' WHERE msj_id=? AND hms_id=?;
        ');
        $this->consultar([$this->prm->msj_id,$this->prm->hms_id]);
        $this->mixScript(
            '

            $("#obs' . $this->prm->epg_id . '").text("' . $texto . '");
            $("#obs' . $this->prm->epg_id . '").css({"color":"#' . $color . '"});
            '
        );

        $this->setSql('
            SELECT msj_id, pii_id, epg_id
            FROM cat_MensajeSeguimiento
            INNER JOIN cat_HistorialMensajeSeguimiento USING(msj_id)
            WHERE epg_id=? AND nim_id=4 AND tim_id=1 AND hms_trimestre=?
            AND eme_id!=3 ;
        ');

        $this->consultar([$this->prm->epg_id,$this->prm->trimestre]);
        if ($this->getNumeroRegistros() == 0) {
            $this->mixScript('
                $("#obs' . $this->prm->epg_id . '").css({"color":"#00950A"});
                $("#obs' . $this->prm->epg_id . '").text("SIN OBSERVACION");
            ');
        }
        $this->desconectar();

        $this->mixScript('
            $("#myModal").modal("hide");
            notify("El mensaje fue ' . $mensaje . ' correctamente.");
            comunica("' . $this->pantalla . '",{
            metodo:"Historial",
            trimestre:"' . $this->prm->trimestre . '",
            epg_id:"' . $this->prm->epg_id . '"
            ' . $this->toa . '
            });
            comunica("' . $this->pantalla . '",{
            metodo: "verActividades",
            epg_id:"' . $this->prm->epg_id . '",
            gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
            ur_id:"' . $_SESSION[_S]['clasAdm-ur'] . '",
            trimestre:' . $this->prm->trimestre
            . $this->gpo
            . $this->ur
            . $this->prg
            . $this->spr
            . $this->py
            . $this->toa . '
            });
        ');
    }

    private function ActualizaVisto()
    {
        $trimestreSeleccionado = $this->prm->trimestre;
        $this->conectar();
        $this->setSql('
            UPDATE
            cat_HistorialMensajeSeguimiento hms,
            ( SELECT
                msj_id
                FROM
                cat_MetaActividad m
                INNER JOIN cat_MetaActividadTrimestre mt USING(pii_id, epg_id)
                LEFT JOIN cat_MensajeSeguimiento using(pii_id, epg_id)
                LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                INNER JOIN cat_Usuario u  on hm.usr_id = u.usr_id
                WHERE epg_id = ?
                AND hm.tim_id = 1
                AND  eme_id = 4
                AND hms_trimestre = ?
            ) as m
            set hms.eme_id = 6
            where  hms.msj_id=m.msj_id;
        ');
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
}

//fin clase
if (isset($_SESSION[_S]['prm']['A']['11']['1'])) {
    new ControladorObservarActividad();
} else {
    WebResponse::sinPermisos();
}
