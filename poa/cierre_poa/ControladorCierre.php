<?php

/**
 * Description of ControladorCierre
 *
 * @author Japhet Mata Arellanes
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 30-Agt-2018
 * Comment: -----
 * Objective: Obtener la diferencia entre el techo presupuestal y el presupuestado, además de cambiar el tipo de cierre.
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de lectura y escritura.
 *
 * Update 15-10-2019
 * @author @Jordy <jordy.castro@finanzasoaxaca.gob.mx>
 * Comment: Se agrego un modal al boton de cierre de poa para mostrar los encuadres programaticos que no se han
 * vinculado con una meta activdad (EP-MIR).
 * Update 27-10-2020
 * @author @Juan Antonio Gabriel Bolaños <juan.gabriel@finanzasoaxaca.gob.mx>
 * Comment: Se agregó la funcionalidad de forzar el cierre a pesar de contar con una diferencia entre
 * el Techo financiero y el Presupuesto a través del botón "Forzar Cierre".
 * */
session_start();
ini_set('max_execution_time', 300);
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../lib/poa/cierre_poa/consultaPoaCierre.php';
require_once __DIR__ . '/../../lib/constantes.php';
class ControladorCierre extends WebResponse
{
    private $permisoCierreReaperturaPoa = 'sinvalor';
    private $permisoCierreEscritura = 'sinvalor';
    private $permisoCierreForzadoEscritura = 'sinvalor';
    private $usrId;
    private $clasAdmGpo;
    private $clasAdmUr;

    public function __construct()
    {
        parent::__construct();
        /**Variables de sesion del controlador */
        $this->usrId = (isset($_SESSION[_S]['usr_id']) && ($_SESSION[_S]['usr_id'] != '')) ? $_SESSION[_S]['usr_id'] : '';
        $this->clasAdmGpo = (isset($_SESSION[_S]['clasAdm-gpo']) && ($_SESSION[_S]['clasAdm-gpo'] != '')) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->clasAdmUr = (isset($_SESSION[_S]['clasAdm-ur']) && ($_SESSION[_S]['clasAdm-ur'] != '')) ? $_SESSION[_S]['clasAdm-ur'] : '';

        /**Variables de permisos para funcionalidad del controlador */
        if ($this->permisoCierreReaperturaPoa === 'sinvalor') {
            $this->permisoCierreReaperturaPoa = (isset($_SESSION[_S]['prm']['A']['8']['1']['5']) && ($_SESSION[_S]['prm']['A']['8']['1']['5'] != '')) ? true : false;
        }
        if ($this->permisoCierreEscritura === 'sinvalor') {
            $this->permisoCierreEscritura = isset($_SESSION[_S]['prm']['A']['8']['1']['2']) && ($_SESSION[_S]['prm']['A']['8']['1']['2'] != '') ? true : false;
        } //Permiso para cierre forzado
        if ($this->permisoCierreForzadoEscritura === 'sinvalor') {
            $this->permisoCierreForzadoEscritura = isset($_SESSION[_S]['prm']['A']['8']['1']['106']) && ($_SESSION[_S]['prm']['A']['8']['1']['106'] != '') ? true : false;
        }

        switch ($this->metodo) {
            case 'cerrar':
                $this->cerrar();
                break;
            case 'cerrarForzado':
                $this->cerrarForzado();
                break;
            case 'abrir':
                $this->abrir();
                break;
            case 'screen':
                $this->screen();
                break;
            case 'Exceptuar':
                $this->Exceptuar();
                break;
            case 'vistaValidacionEpMir':
                $this->vistaValidacionEpMir();
                break;
            case 'llenaVistaValidacionEpMir':
                $this->llenaVistaValidacionEpMir();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->mixHtml('<div id="pintaPantalla"></div>');
        $this->mixScript('
            $("#techos").show();
            $("#combour").change(function () {
                $("#toggleCAx").click();
                comunica("poa/cierre_poa/ControladorCierre.php", {
                    metodo: "screen",
                    gpo_id: $(this).find("option:selected").attr("gpo_id"),
                    ur_id: $(this).find("option:selected").attr("ur_id")
                }
                );
            });
        ');
        $this->screen();
    }

    public function screen()
    {
        $this->mixScript('$("#cerrar").hide();');
        $this->mixScript('$("#abrir").hide();');
        $this->setContenedor('pintaPantalla');

        $gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : $this->clasAdmGpo;
        $ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : $this->clasAdmUr;

        $sql = '
            SELECT
                gpo_id,ur.ur_id,ur.ur_nombre,
                ter.ter_id,
                ter.ter_nombre,
                crc.crc_id,crc.crc_nombre,
                IFNULL(pre_original, 0) as pre_original
                ,tec_techoPresupuestal,
                ur_tipoCierre,
                eur.eur_id,eur.eur_nombre,ur_estadoCosteo,ur_cierreForzado
            FROM cat_CriterioCierre crc
                LEFT JOIN(
                        SELECT
                            tec.gpo_id,
                            tec.ur_id,
                            ter_id,
                            crc_id,
                            sum(tec.tec_techoPresupuestal) AS tec_techoPresupuestal
                        FROM cat_Techos tec
                            INNER JOIN cat_ClaseGasto clg USING(clg_id)
                        WHERE   tec.gpo_id = ? AND tec.ur_id = ?
                        GROUP BY ter_id,crc_id
                )C USING(crc_id)
                LEFT JOIN(
                        SELECT
                            ter_id,
                            crc_id,
                            sum(ppo.pre_original) as pre_original
                        FROM presupuesto pre
                            INNER JOIN presupuestoOriginal ppo USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                            INNER JOIN cat_PartidaEspecifica pe USING(pg_id,pe_id)
                            INNER JOIN cat_ClaseGasto clg USING(clg_id)
                            INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' sub USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                            INNER JOIN sinpres.cat_TipoRecurso ter USING(ter_id)
                        WHERE pre.gpo_id = ?
                            AND pre.ur_id = ?
                        GROUP BY ter_id,crc_id
                    ) B USING(ter_id,crc_id)
                INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
                INNER JOIN sinpres.cat_TipoRecurso ter USING(ter_id)
                INNER JOIN cat_EstadoUr eur USING(eur_id)
            HAVING ter.ter_id IS NOT NULL AND (pre_original IS NOT NULL OR tec_techoPresupuestal > 0)
            ORDER BY ter.ter_id,crc.crc_id
            ;';

        $params = [$gpo_id, $ur_id, $gpo_id, $ur_id];
        $datos = new AccesoDatos();
        $datos->setSql($sql);
        $datos->conectar();
        $datos->consultar($params);
        $datos->desconectar();

        $ur_poa = 0;
        $diferencias = false;
        $color = '';

        $reg = 0;
        $presupuestadour = $techour = 0;
        $presugft = $techogft = 0;
        $presufte = $techofte = 0;

        $html2 = $htmlur = '';
        $html = '
            <table id="tablacierre" class="table-striped mb-4" style="width:100%; border:1px solid #EAEAEA; box-shadow:#AAA 0px 1px 1px; padding:4px; border-collapse: collapse;overflow: hidden; z-index: 1;">
                <thead>
                    <tr style="background:linear-gradient(to top ,rgba(229, 210, 153, 1),rgba(255,255,255,1)); box-shadow:#AAA 0px 1px 1px;">
                        <th style="padding:6px;" scope="col">UR</th>
                        <th style="padding:6px;" scope="col">Gran Fuente</th>
                        <th style="padding:6px;" scope="col"></th>
                        <th style="padding:6px;" scope="col">Clase de Gasto</th>
                        <th style="padding:6px;" class ="uno" scope="col" align="right">Techo Financiero</th>
                        <th style="padding:6px;" scope="col" align="right">Presupuestado</th>
                        <th style="padding:6px;" scope="col" align="right">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
        ';
        $finTabla = '</tbody></table>';
        $tipocierre = $gpo_id = $ur_id = $ur_nombre = '';

        if ($row = $datos->extraer()) {
            $html2 .= '
                <tr>
                    <td></td>
                    <td colspan="7" align="left">' . $row['ter_id'] . ':' . $row['ter_nombre'] . '</td>
                </tr>';

            $gpo_id = $row['gpo_id'];
            $ur_id = $row['ur_id'];
            $ur_nombre = $row['ur_nombre'];
            $ur_poa = $row['eur_id'];
            $tipocierre = $row['ur_tipoCierre'];
            $ter_id = $row['ter_id'];
            $eur_nombre = $row['eur_nombre'];
            $ur_estadoCosteo = $row['ur_estadoCosteo'];

            do {
                $reg = $reg + 1;
                if ($ter_id <> $row['ter_id']) {
                    $tmpgft = '';
                    if (($techogft - $presugft) > .99 || ($techogft - $presugft) < 0) {
                        $tmpgft = 'color:#900;';
                        $diferencias = true;
                    } else {
                        $tmpgft = '';
                    }
                    $html2 .= ' 
                        <tr style="' . $tmpgft . '">
                            <td colspan="3"></td>
                            <td align="right">TOTAL GRAN FUENTE</td>
                            <td align="right"><span style="float:left">$</span>' . number_format($techogft, 2) . '</td>
                            <td align="right"><span style="float:left">$</span>' . number_format($presugft, 2) . '</td>
                            <td align="right"><span style="float:left">$</span>' . number_format($techogft - $presugft, 2) . '</td>
                        </tr>
                    ';
                    $html2 .= '
                        <tr>
                            <td></td>
                            <td colspan="7" align="left">' . $row['ter_id'] . ':' . $row['ter_nombre'] . '</td>
                        </tr>';

                    $techofte = 0;
                    $presufte = 0;
                    $techogft = 0;
                    $presugft = 0;
                    $ter_id = $row['ter_id'];
                }
                /********** REGISTROS INDIVIDUALES DE CADA CRITERIO DE CIERRE****************/
                $diferencia = $row['tec_techoPresupuestal'] - $row['pre_original'];
                if (($diferencia > .99 || $diferencia < 0) && $tipocierre == 'N') {
                    $color = 'color:#900;';
                    $diferencias = true;
                } else {
                    $color = '';
                }
                $html2 .= '
                    <tr style="' . $color . '">
                        <td colspan="3"></td>
                        <td>' . $row['crc_id'] . ' ' . $row['crc_nombre'] . '</td>
                        <td align="right" class="unos"><span style="float:left">$</span>' . number_format($row['tec_techoPresupuestal'], 2) . '</td>
                        <td align="right"><span style="float:left">$</span>' . number_format($row['pre_original'], 2) . '</td>
                        <td align="right"><span style="float:left">$</span>' . number_format($diferencia, 2) . '</td>
                    </tr>
                ';
                /********** FIN REGISTROS INDIVIDUALES DE CADA CRITERIO DE CIERRE*********************/

                $presupuestadour += $row['pre_original'];
                $techour += $row['tec_techoPresupuestal'];
                $presufte += $row['pre_original'];
                $techofte += $row['tec_techoPresupuestal'];
                $presugft += $row['pre_original'];
                $techogft += $row['tec_techoPresupuestal'];
            } while ($row = $datos->extraer());
            $tmpgft = '';
            if (($techogft - $presugft) > .99 || ($techogft - $presugft) < 0) {
                $tmpgft = 'color:#900;';
                $diferencias = true;
            } else {
                $tmpgft = '';
            }
            $html2 .= ' 
                <tr style="' . $tmpgft . '">
                    <td colspan="3"></td>
                    <td align="right">TOTAL GRAN FUENTE</td>
                    <td align="right"><span style="float:left">$</span>' . number_format($techogft, 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format($presugft, 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format($techogft - $presugft, 2) . '</td>
                </tr>';

            $tmpur = '';
            if (($techour - $presupuestadour) > .99 || ($techour - $presupuestadour) < 0) {
                $tmpur = 'background:repeating-linear-gradient(45deg,transparent,transparent 10px,rgba(200,0,0,.1) 10px,rgba(200,0,0,.1) 12px),linear-gradient(to bottom,rgba(200,200,200,.5),rgba(200,0,0,.2)); padding:6px; color:#900;';
            } else {
                $tmpur = 'background:repeating-linear-gradient(45deg,transparent,transparent 10px,rgba(0,200,0,.1) 10px,rgba(0,200,0,.1) 12px),linear-gradient(to bottom,rgba(200,200,200,.5),rgba(0,200,0,.2)); padding:6px; color:#090;';
            }
            $htmlur = '
                <tr>
                    <td style="' . $tmpur . '"  >' . $gpo_id . $ur_id . '</td>
                    <td style="' . $tmpur . '"  colspan="3">' . $ur_nombre . '<span style="font-size:10px;color:#666;">[' . $eur_nombre . ']</span></td>
                    <td style="' . $tmpur . '"  align="right" ><span style="float:left">$</span>' . number_format($techour, 2) . '</td>
                    <td style="' . $tmpur . '"  align="right" ><span style="float:left">$</span>' . number_format($presupuestadour, 2) . '</td>
                    <td style="' . $tmpur . '"  align="right" ><span style="float:left">$</span>' . number_format($techour - $presupuestadour, 2) . '</td>
                <tr>
            ';
        }

        $difprg = false;

        if ($difprg === true) {
            $diferencias = true;
        }

        /********************************** FIN TABLA DE PROGRAMAS DE LA UR ****************************************/
        $this->mixHtml($html . $htmlur . $html2 . $finTabla);

        if ($ur_poa < 5) {
            $jkm = '<div style="padding:10px; background-color:#2A2929; color:#BBB;">
                        <span>El tipo de Cierre es ' . ($tipocierre == 'N' ? 'Normal (Por Criterio o Clase de Gasto)' : 'por Gran Fuente') . '<span>';
            if ($ur_poa < 5) {
                $jkm .= '<span id="cambiarmodalidad"  style="text-decoration:underline; cursor:pointer;"> [Cambiar] </span>';
                $this->mixScript('
                    $("#cambiarmodalidad").click(function () {
                        alertify.confirm("CAMBIAR EL TIPO DE CIERRE ",  "' . ($tipocierre == 'N' ? 'Confirme el cambio del tipo de Cierre a \"Cierre por Gran Fuente \"' : 'Confirme el cambio del tipo de Cierre a \"Cierre por Criterio/Clase de Gasto \"') . '", 
                            function(){
                                comunica("poa/cierre_poa/ControladorCierre.php", { metodo: "Exceptuar", ex: "' . ($tipocierre == 'N' ? 'G' : 'N') . '" });
                            },
                            function(){
                                }
                        ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    });
                ');
            }

            $jkm .= '</div>';
            $this->mixHtml($jkm);
        }
        $botones = '
            <button id="printPdf" type="button" class="btn btn-outline-danger me-1" title="Imprimir Cierre POA">
                <i class="fa-solid fa-file-pdf me-2"></i> PDF
            </button>
            <button id="printExcel" type="button" class="btn btn-outline-success me-1" title="Imprimir Cierre POA en excel">
                <i class="fa-solid fa-file-excel me-2"></i> Excel
            </button>
        ';

        if ($ur_poa == 4 && $ur_estadoCosteo == 1 and $reg <> 0 && $this->permisoCierreForzadoEscritura && $diferencias != false) {

            $btnCerrarForzado = new stdClass();
            $btnCerrarForzado->id = 'cerrarForzado';
            $btnCerrarForzado->color = 'rojo';
            $btnCerrarForzado->value = 'Forzar cierre';
            $botones .= $this->ponBoton($btnCerrarForzado);

            $this->mixScript('
            $("#cerrarForzado").click(function () {
                    alertify.confirm("CERRAR EL COSTEO DE LA UR", "Confirme el cierre forzado del costeo de la Unidad Responsable <h5 style=\"display:inline\"></h5>",
                        function(){
                            comunica("poa/cierre_poa/ControladorCierre.php", { metodo: "cerrarForzado" });
                        },
                        function(){
                        }
                    ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                })
            ');

            $this->mixScript('$("#cerrarForzado").show();');
        }
        if ($ur_poa == 4 && $ur_estadoCosteo == 1 and $reg <> 0 && $this->permisoCierreEscritura && $diferencias == false) { //&& $diferencias == false, la diferencia debe ser cero para visualizar el botón de cerrado.
            $btnCerrar = new stdClass();
            $btnCerrar->id = 'cerrar';
            $btnCerrar->color = 'rojo';
            $btnCerrar->value = 'Cerrar poa';
            $botones .= $this->ponBoton($btnCerrar);

            if ($this->validaDatosEpMir()) {
                $this->mixScript('
                    $("#cerrar").click(function () {
                        load_viewer("modal1", "", "div", "", 1030, true);
                        comunica("poa/cierre_poa/ControladorCierre.php", {
                            metodo: "vistaValidacionEpMir"
                        });
                    });
                ');
            } else {
                $this->mixScript('
                    $("#cerrar").click(function () {
                        alertify.confirm("CERRAR EL COSTEO DE LA UR", "Confirme el cierre del costeo de la Unidad Responsable <h5 style=\"display:inline\"></h5>",
                            function(){
                                comunica("poa/cierre_poa/ControladorCierre.php", { metodo: "cerrar" });
                            },
                            function(){
                            }
                        ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    })
                ');
            }

            $this->mixScript('$("#cerrar").show();');
        }

        if ($ur_poa == 5 && $this->permisoCierreReaperturaPoa) {

            $btnAbrir = new stdClass();
            $btnAbrir->id = 'abrir';
            $btnAbrir->color = 'azul';
            $btnAbrir->value = 'Abrir poa';
            $botones .= $this->ponBoton($btnAbrir);

            $this->mixScript('
                $("#abrir").click(function () {
                    alertify.confirm("ABRIR EL COSTEO DE LA UR", " Confirme que desea aperturar el costeo de la Unidad Reponsable <h5 style=\"display:inline\"></h5> ",
                        function(){
                            comunica("poa/cierre_poa/ControladorCierre.php", { metodo: "abrir" });
                        },
                        function(){
                        }
                    ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });
            ');

            $this->mixScript('$("#abrir").show();');
        } elseif ($reg == 0) {
            $this->mixHtml('<div class="alert alert-warning">No hay registros para la Unidad Responsable actual</div>');
        } elseif ($diferencias != false) {
            $this->mixHtml('<div class="alert alert-warning"> No es posible cerrar porque no se cumplen los criterios establecidos</div>');
        }

        $this->agregaTools($botones);
        $this->mixHtml('<hr/>
            <div class="alert alert-info">
                a) Para el cierre es necesario que las diferencias se muestren en 0 <br/>
                b) Las partidas en Rojo no cumplen con los criterios a considerar para el cierre<br/>
                c) No se muestran las partidas  que su presupuesto  y techo financiero este en 0
            </div>
            ');
        $this->mixScript('
            $("#printPdf").click(function () {
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_PoaCierre.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 1))
                $("body").append(form);
                form.submit();
                form.remove();
            });
            $("#printExcel").click(function () {
                var form = $("<form>")
                    .attr("action", "rep/excel/poa/cierre_poa/exp_PoaCierre.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", 1))
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    public function cerrar()
    {
        self::verificarPermiso($this->permisoCierreEscritura);
        $sql1 = 'SET @bit_observacion = ? ,@usr_id = ?, @usr_ip  = sinpres.fn_get_ip();';
        $bit_observacion = 'SE CIERRA EL POA DE LA UR DESDE EL MODULO';
        $params1 = [$bit_observacion, $this->usrId];
        $sqlur = 'UPDATE cat_UnidadResponsable SET ur_estadoCosteo = ? , eur_id = ?, ur_cierreForzado = ? WHERE gpo_id = ? AND ur_id = ?;';
        $params2 = [2, 5, 1, $this->clasAdmGpo, $this->clasAdmUr];
        $sql3 = 'CALL sp_PoaPresupuesto' . APP_EJERCICIO . '();';

        $this->setSql($sql1);
        $this->conectar();
        $this->consultar($params1);
        $this->setSql($sqlur);
        $this->consultar($params2);
        $this->setSql($sql3);
        $this->consultar();
        $this->desconectar();
        $this->mixScript('
            notify("Costeo cerrado, no se podra modificar posteriormente");
            comunica("poa/cierre_poa/ControladorCierre.php",{metodo:"screen"});
        ');
    }

    public function cerrarForzado()
    {
        self::verificarPermiso($this->permisoCierreForzadoEscritura);
        $bit_observacion = 'SE FUERZA EL CIERRE POA DE LA UR DESDE EL MODULO';
        $sql1 = 'SET @bit_observacion= ?, @usr_id= ?, @usr_ip  = sinpres.fn_get_ip();';
        $params = [$bit_observacion, $this->usrId];
        $sqlur = 'UPDATE cat_UnidadResponsable SET ur_estadoCosteo = ?, eur_id = ?, ur_cierreForzado = ? WHERE gpo_id = ? AND ur_id = ?;';
        $params1 = [2, 5, 2, $this->clasAdmGpo, $this->clasAdmUr];
        $sql3 = 'CALL sp_PoaPresupuesto' . APP_EJERCICIO . '();';

        $this->setSql($sql1);
        $this->conectar();
        $this->consultar($params);
        $this->setSql($sqlur);
        $this->consultar($params1);
        $this->setSql($sql3);
        $this->consultar();
        $this->desconectar();
        $this->mixScript('
            notify("Costeo cerrado forzado, no se podra modificar posteriormente");
            comunica("poa/cierre_poa/ControladorCierre.php", { metodo: "screen" });
        ');
    }

    public function abrir(){

        self::verificarPermiso($this->permisoCierreReaperturaPoa);
        $bit_observacion = 'SE ABRE EL POA DE LA UR DESDE EL MODULO';
        $sql2 = 'SET @bit_observacion = ? , @usr_id= ? , @usr_ip  = sinpres.fn_get_ip();';
        $params = [$bit_observacion, $this->usrId];
        $sqlur = 'UPDATE cat_UnidadResponsable SET eur_id = ?, ur_estadoCosteo = ? WHERE gpo_id = ? AND ur_id = ?;';
        $params1 = [4, 1, $this->clasAdmGpo, $this->clasAdmUr];

        $this->conectar();
        $this->setSql($sql2);
        $this->consultar($params);
        $this->setSql($sqlur);
        $this->consultar($params1);
        $this->desconectar();
        $this->mixScript('
            notify("Costeo Abierto");
            comunica(
                "poa/cierre_poa/ControladorCierre.php", {
                metodo: "screen",
                gpo_id: $(this).find("option:selected").attr("gpo_id"),
                ur_id: $(this).find("option:selected").attr("ur_id")
            }
            );
        ');
    }

    private function Exceptuar()
    {    
        $bit_observacion = 'SE CIERRA EL POA DE LA UR DESDE EL MODULO';
        $sql = 'SET @bit_observacion= ? ,@usr_id = ?, @usr_ip  = sinpres.fn_get_ip();';
        $params = [$bit_observacion, $this->usrId];
        $sql2 = 'UPDATE cat_UnidadResponsable SET ur_tipoCierre = ? WHERE gpo_id = ? AND ur_id = ? ;';
        $params1 = [$this->prm->ex, $this->clasAdmGpo, $this->clasAdmUr];

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);

        $this->setSql($sql2);
        $this->consultar($params1);
        $this->desconectar();
        $this->mixScript('
            notify("Se ah actualizado el tipo de cierre de la Unidad Responsable ' . $this->clasAdmGpo . $this->clasAdmUr . '");
            comunica("poa/cierre_poa/ControladorCierre.php");
        ');
    }

    //SE VALIDA SI HAY AL MENOS UN REGISTRO QUE NO ESTE VINCULADO CON LA MIR
    private function validaDatosEpMir()
    {
        $this->conectar();
        $this->setSql(consultaEncuadreNoVinculacionMir(1));
        $params = [$this->clasAdmGpo, $this->clasAdmUr];
        $this->consultar($params);
        if ($this->getNumeroRegistros() != 0) {
            $this->desconectar();
            return true;
        } else {
            $this->desconectar();
            return false;
        }
    }

    // METODO PARA LA PARTE GRAFICA DEL MODAL DE LOS ENCUADRES QUE NO ESTA VINCULADOS CON LA MIR
    private function vistaValidacionEpMir()
    {
        $this->setContenedor('det_fsmodal1');

        $this->mixScript('
            $("#det_fsmodal1").parent().attr("edo", "m").animate({ top: "0px", left: "0px", width: "100%", height: "100%" }, 500);
        ');

        $tabla = new stdClass();
        $tabla->id = 'vistaValidacionEpMir';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/cierre_poa/ControladorCierre.php';
        $tabla->titulo = 'El Encuadre Programático no cuentan con validación EP - MIR';
        $tabla->comunica->metodo = 'llenaVistaValidacionEpMir';
        $tabla->filasAmostrar = [20, 50, 100, 250, 300];
        $tablaEncabezado = [];

        $th = new stdClass();
        $th->texto = 'Encuadre Programático';
        $th->width = '50%';
        array_push($tablaEncabezado, $th);

        $th = new stdClass();
        $th->texto = 'Concepto';
        $th->width = '50%';
        array_push($tablaEncabezado, $th);

        $tablaCuerpo = [];

        $td = new stdClass();
        $td->campoTxt = 'encuadre';
        array_push($tablaCuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        array_push($tablaCuerpo, $td);

        $tabla->encabezado = [$tablaEncabezado];
        $tabla->cuerpo = $tablaCuerpo;

        $modal = '
            <div>
                <div>
                    ' . $this->wrPonTabla($tabla) . '
                </div>
                <div>
                    <button id="cerrarPoaModal" value = "Cerrar poa"  class = "btn btn-outline-success" title="" style = "display: inline-block; "><i>CERRAR POA</i></button>
                    <button id="cancelarPoaModal" value = "Cancelar Cerrar Poa"  class = "btn btn-outline-danger" title="" style = "display: inline-block; "><i>CANCELAR</i></button>
                    <button id="imprimirPoaModal" value = "Impirmir Modal"  class = "btn btn-outline-info" title="" style = "display: inline-block; "><i >EXPORTAR A EXCEL</i></button>
                </div>
            </div>
        ';
        $this->llenaVistaValidacionEpMir();

        $this->mixScript('
            $("#cancelarPoaModal").click(function () {
                $("#fsmodal1").remove();
            });

            $("#cerrarPoaModal").click(function () {
                alertify.confirm("CERRAR EL COSTEO DE LA UR", "Confirme el cierre del costeo de la Unidad Responsable <h5 style=\"display:inline\"></h5> ",
                    function(){
                        $("#fsmodal1").remove();
                        comunica("poa/cierre_poa/ControladorCierre.php",{metodo: "cerrar"});
                    },
                    function(){
                    }
                 ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $("#imprimirPoaModal").click(function () {
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/cierre_poa/rep_PoaCierre.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 1))

                $("body").append(form);
                form.submit();
                form.remove();
            });

            $("#imprimirPoaModal").click(function () {
                var form = $("<form>")
                    .attr("action", "rep/excel/poa/cierre_poa/modal_exp_PoaCierre.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", 1))
            
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
        $this->mixHtml($modal);
    }

    //METODO PARA LLENAR LA MODAL DEL MODAL DE LOS ENCUADRES QUE NO ESTA VINCULADOS CON LA MIR
    private function llenaVistaValidacionEpMir()
    {
        $this->conectar();
        $this->setSql(consultaEncuadreNoVinculacionMir(0));
        $params = [$this->clasAdmGpo, $this->clasAdmUr];
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 10;
        $nav->idTabla = 'vistaValidacionEpMir';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("NO EXISTEN REGISTROS","warning");');
        }
    }
    static function  verificarPermiso(bool $permiso): void
    {
        if (!$permiso) {
            WebResponse::sinPermisos();
        }
    }
}

if (isset($_SESSION[_S]['prm']['A']['8']['1']['1'])) {
    new ControladorCierre();
} else {
    WebResponse::sinPermisos();
}
