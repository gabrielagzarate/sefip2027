<?php
    /**
     * Description of ControladorMuestraTechos
     *
     * @author ?
     * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>, ABN
     * Created: 23-Oct-2018
     * updated: may 2024
     * Comment: -----
     * Objective: Mostrar graficamente el techos por gran fuente o clase de gasto.
     * Regla de Negocio: -----
     * Permits: Esta pantalla tiene permisos de lectura.
     * */
ini_set("display_errors", "0");
//require_once dirname(__FILE__)."/lib/constantes.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../lib/WebResponse.php';

Class ControladorMuestraTechos extends WebResponse
{
    protected $clasAdmGpo;
    protected $clasAdmUr;
    public function __construct()
    {
        parent::__construct();
        $this->clasAdmGpo = isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '' ? $_SESSION[_S]['clasAdm-gpo'] : "";
        $this->clasAdmUr = isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '' ? $_SESSION[_S]['clasAdm-ur'] : "";
        switch ($this->metodo) {
            case 'consulta0':
                $this->main();
                break;
            case 'consulta1':
                $this->porFuente();
                break;
            case 'consulta2':
                $this->porPartidaFuente();
                break;
            case 'consulta3':
                $this->Techos();
                break;
            case 'consulta4':
                $this->ClasePartida();
                break;
            case 'consultaPartida':
                $this->consultaPartida();
                break;
            default: 
                $this->main();
                $this->mixScript("$('#muestraTechos').show();");
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $this->setSql('
            SELECT clg_id, clg_nombre, sum(tec_techoPresupuestal) AS techo, 
                if(isnull(costeo),0,costeo) AS costeo, 
                sum(tec_techoPresupuestal) - if(isnull(costeo),0,costeo) AS diferencia
            FROM cat_Techos
                INNER JOIN cat_ClaseGasto USING (clg_id)
                LEFT JOIN(
                    SELECT gpo_id, ur_id, clg_id, sum(pre_original) AS costeo
                    FROM presupuesto
                        INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        INNER JOIN cat_PartidaEspecifica USING ( pg_id,pe_id )
                    WHERE  gpo_id = :adm_gpo
                        AND ur_id  = :adm_ur
                    GROUP BY clg_id
                ) AS pres USING (gpo_id,ur_id,clg_id)
            WHERE  gpo_id = :adm_gpo
                AND ur_id = :adm_ur
                AND tec_techoPresupuestal > 0
            GROUP BY clg_id;
        ');
        
        $this->conectar();
        $this->consultar($prm);
        $this->desconectar();
        $categoria = array();
        $costeado = array();
        $porCostear = array();
        $min = 0;

        while ($row = $this->extraer()) {
            $diferencia = floatval($row['diferencia']);
            array_push($categoria, $row['clg_id'] . ' - ' . $row['clg_nombre']);
            $montoCosteado = floatval($row['costeo']);
            
            if ($diferencia < 0) {
                $montoCosteado = $montoCosteado * -1;
                $diferencia = 0;
                $min = ( $min > $montoCosteado) ? $montoCosteado : $min;
            };
            array_push($costeado, $montoCosteado);
            array_push($porCostear, $diferencia);
        }

        $this->setContenedor('muestraTechos');
        $combo = new stdClass();
        $combo->opciones = array('Identificador de Techos', 'Techos por gran Fuente', 'Techos por gran Fuente - Identificador de Techos', 'Gran Fuente - Identificador de Techos', 'Clase de Gasto- Partida');
        $combo->id = 'consulta';
        $combo->change = '
            $("#botones").empty();
            comunica("srv/ControladorMuestraTechos.php",{ metodo:"consulta" + $(this).val() });
        ';
        $this->mixHtml('
            <div class="modal-content ">
                <div class="modal-header">
                    ' . $this->ponCombo($combo) . '
                    <button id="cierraTechos" type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div id="botones"> </div>
                    <div id="contenedorTechos" style="min-width: 310px; height: 350px; margin: 0 auto"></div>
                </div>
                <div class="modal-body"> 
                    <div id="contenedorTechosDos"></div>
                </div>
            </div>
        ');

        $this->mixScript("
            $(function () {
                $('#cierraTechos').click( function(){ $('#muestraTechos').empty();$('#muestraTechos').hide(); } );
                $('#contenedorTechos').highcharts({
                    chart: {
                        type: 'bar',
                        spacingRight: 80
                    },
                    title: {
                        text: 'Techos presupuestales'
                    },
                    xAxis: {
                        categories: " . json_encode($categoria) . "
                    },
                    yAxis: {
                        min: " . $min . ",
                        title: {
                            text: 'Techo'
                        },
                        stackLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'bold',
                                color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                            }
                        }
                    },
                    legend: {
                        backgroundColor: '#FFFFFF',
                        reversed: true
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },
                        series: [{
                        name: 'Por Costear',
                        data: " . json_encode($porCostear) . ",
                        color: '#BDBA07'
                    }, {
                        name: 'Costeado',
                        data: " . json_encode($costeado) . ",
                        color: '#088A08'
                    }]
                });
            });
        ");
    }

    private function porFuente()
    {
        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $this->setSql('
            SELECT ter_id, ter_nombre, sum(tec_techoPresupuestal) AS techo, 
                if(isnull(costeo),0,costeo) AS costeo, 
                sum(tec_techoPresupuestal) - if(isnull(costeo),0,costeo) AS diferencia
            FROM cat_Techos
                INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso USING(ter_id)
                LEFT JOIN(
                    SELECT gpo_id, ur_id, ter_id, sum(pre_original) AS costeo
                    FROM presupuesto
                        INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        INNER JOIN  ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id,fte_id,sft_id,ori_id, sor_id)	
                    WHERE  gpo_id = :adm_gpo
                        AND ur_id = :adm_ur
                    GROUP BY ter_id
                ) AS pres USING (gpo_id,ur_id,ter_id)
            WHERE  gpo_id = :adm_gpo
                AND ur_id = :adm_ur
                AND tec_techoPresupuestal > 0
            GROUP BY ter_id;
        ');

        $this->conectar();
        $this->consultar($prm);
        $this->desconectar();
        $categoria = array();
        $costeado = array();
        $porCostear = array();
        $min = 0;

        while ($row = $this->extraer()) {
            $diferencia = floatval($row['diferencia']);
            array_push($categoria, $row['ter_id'] . ' - ' . $row['ter_nombre']);
            $montoCosteado = floatval($row['costeo']);
            ;
            if ($diferencia < 0) {
                $montoCosteado = $montoCosteado * -1;
                $diferencia = 0;
                $min = ( $min > $montoCosteado) ? $montoCosteado : $min;
            };
            array_push($costeado, $montoCosteado);
            array_push($porCostear, $diferencia);
        }

        $this->mixScript("
            $(function () {
                $('#cierraTechos').click( function(){ $('#muestraTechos').empty(); $('#muestraTechos').hide(); } );
                $('#contenedorTechos').highcharts({
                    chart: {
                        type: 'bar',
                        spacingRight: 80
                    },
                    title: {
                        text: 'Techos presupuestales'
                    },
                    xAxis: {
                        categories: " . json_encode($categoria) . "
                    },
                    yAxis: {
                        min: " . $min . ",
                        title: {
                            text: 'Techo'
                        },
                        stackLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'bold',
                                color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                            }
                        }
                    },
                    legend: {
                        backgroundColor: '#FFFFFF',
                        reversed: true
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },
                        series: [{
                        name: 'Por Costear',
                        data: " . json_encode($porCostear) . ",
                        color: '#BDBA07'
                    }, {
                        name: 'Costeado',
                        data: " . json_encode($costeado) . ",
                        color: '#088A08'
                    }]
                });
            });
        ");
    }

    private function porPartidaFuente()
    {
        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $this->conectar();
        $this->setSql('
            SELECT DISTINCT ter_id, ter_nombre
            FROM cat_Techos
                INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso USING (ter_id)
            WHERE gpo_id = :adm_gpo
                AND ur_id = :adm_ur 
                AND tec_techoPresupuestal > 0; 
        ');
        $this->consultar($prm);
        $primer_fte = '';
        $idps = '';

        while ($row = $this->extraer()) {
            $boton = new stdClass();
            $boton->value = '&nbsp;' . $row['ter_id'] . ' - ' . $row['ter_nombre'];
            $boton->title = $row['ter_nombre'];
            $boton->id = 'ter_' . $row['ter_id'];
            $boton->class = 'ter';
            if ($primer_fte == '')
                $boton->color = 'cielo';
            $idps .= $this->ponBoton($boton);
            $this->mixScript("$('#ter_" . $row['ter_id'] . "').attr('ter_id','" . $row['ter_id'] . "');");
            $primer_fte = ( $primer_fte == '' ) ? $row['ter_id'] : $primer_fte;
        }

        $this->setContenedor('botones');
        $this->mixHtml('
            <h4>Tipo de recurso :</h4>
                ' . $idps);
        $this->mixScript("
            $('.ter').click(function(){
                if( $(this).is('.btn-outline-default') ){
                    comunica('srv/ControladorMuestraTechos.php',{ metodo:'consultaPartida', ter_id: $(this).attr('ter_id') });
                    $('.btn-outline-info').removeClass('btn-outline-info').addClass('btn-outline-default');
                    $(this).addClass('btn-outline-info').removeClass('btn-outline-default');
                }
            });
        ");
        $this->prm->ter_id = $primer_fte;
        $this->consultaPartida();
    }

    private function consultaPartida()
    {
        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr, 'ter_id' => $this->prm->ter_id ];
        $this->setSql('
            SELECT clg_id, clg_nombre,
                sum(tec_techoPresupuestal) AS techo, 
                if(isnull(costeo),0,costeo) AS costeo, 
                sum(tec_techoPresupuestal) - if(isnull(costeo),0,costeo) AS diferencia
            FROM cat_Techos 
                JOIN cat_ClaseGasto USING (clg_id)
            LEFT JOIN(
                SELECT 
                    gpo_id,
                    ur_id,
                    clg_id,
                    sum(pre_original) AS costeo
                FROM presupuesto
                    INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN cat_PartidaEspecifica USING (pg_id,pe_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id,fte_id,sft_id,ori_id, sor_id)	
                WHERE gpo_id = :adm_gpo 
                    AND ur_id = :adm_ur 
                    AND ter_id = :ter_id
                GROUP BY clg_id
            ) AS pres USING (gpo_id,ur_id,clg_id)
            WHERE gpo_id = :adm_gpo
                AND ur_id = :adm_ur
                AND ter_id = :ter_id
                AND tec_techoPresupuestal > 0
            GROUP BY clg_id;
        '); 
        $this->conectar();
        $this->consultar($prm);
        $this->desconectar();
        $categoria = array();
        $costeado = array();
        $porCostear = array();
        $min = 0;

        while ($row = $this->extraer()) {
            $diferencia = floatval($row['diferencia']);
            array_push($categoria, $row['clg_id'] . ' - ' . $row['clg_nombre']);
            $montoCosteado = floatval($row['costeo']);
            if ($diferencia < 0) {
                $montoCosteado = $montoCosteado * -1;
                $diferencia = 0;
                $min = ( $min > $montoCosteado) ? $montoCosteado : $min;
            };
            array_push($costeado, $montoCosteado);
            array_push($porCostear, $diferencia);
        }

        $this->mixScript("
            $('#cierraTechos').click( function(){ 
                $('#muestraTechos').empty(); 
                $('#muestraTechos').hide();
            });
            $('#contenedorTechos').highcharts({
                chart: {
                    type: 'bar',
                    spacingRight: 80
                },
                title: {
                    text: 'Techos presupuestales'
                },
                xAxis: {
                    categories: " . json_encode($categoria) . "
                },
                yAxis: {
                    min: " . $min . ",
                    title: {
                        text: 'Techo'
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                        }
                    }
                },
                legend: {
                    backgroundColor: '#FFFFFF',
                    reversed: true
                },
                plotOptions: {
                    series: {
                        stacking: 'normal'
                    }
                },
                    series: [{
                    name: 'Por Costear',
                    data: " . json_encode($porCostear) . ",
                    color: '#BDBA07'
                }, {
                    name: 'Costeado',
                    data: " . json_encode($costeado) . ",
                    color: '#088A08'
                }]
            });
        ");
    }

    // Muestra los Techos Financieros
    private function Techos()
    {
        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];

        $sql = '
            SELECT ter_id, clg_id, clg_nombre, SUM(techo) AS techo, SUM(costeo) AS costeo 
            FROM (
                SELECT ter_id, clg_id, SUM(tec_techoPresupuestal) AS techo, 0 AS costeo 
                FROM cat_Techos 
                WHERE gpo_id = :adm_gpo
                    AND ur_id = :adm_ur
                    AND tec_techoPresupuestal <> 0 
                GROUP BY ter_id,clg_id 
                UNION ALL 
                SELECT ter_id, clg_id, 0 AS techo, SUM(pre_original) AS costeo 
                FROM presupuesto 
                    INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN cat_PartidaEspecifica USING (pg_id,pe_id) 
                    INNER JOIN  ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id,fte_id,sft_id,ori_id, sor_id)
                WHERE gpo_id = :adm_gpo 
                    AND ur_id = :adm_ur
                GROUP BY ter_id, clg_id
            ) AS datos
                INNER JOIN cat_ClaseGasto USING ( clg_id )
            GROUP BY ter_id,clg_id 
            ORDER BY ter_id,clg_id;
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($prm);
        $this->desconectar();

        $this->setContenedor('contenedorTechos');
        $html = '
            <div id="datos" class="panel panel-default" style="min-width: 500px; height: 400px; overflow: scroll;">
            <table id="TextoNormal" width="500px;" class="table table-striped table-hover table-bordered">
                <tr>
                    <th class="info" colspan="6">
                        <h6 align="center"><strong>TIPO DE RECURSO - CLASE DE GASTO</strong></h6>
                    </th>
                </tr>
                <tr>
                    <td class="info" align="center"><b>ID GASTO</b></td>
                    <td class="info" align="center"  ><b>NOMBRE</b></td>
                    <td class="info" align="center" ><b>TECHO</b></td>
                    <td class="info" align="center" ><b>COSTEO</b></td>
                    <td class="info" align="center" ><b>DIFERENCIA</b></td>
                </tr>
        ';

        $this->mixHtml($html);
        $ter_id = "";
        $paso = false;
        $tot_ter = 0;
        $tot_gral = 0;
        $tot_tercos = 0;
        $tot_gralcos = 0;
        $ter_nombre = "";

        while ($row = $this->extraer()) {
            if ($ter_id <> $row['ter_id']) {
                if ($paso) {
                    $this->mixHtml('
                        <tr>
                            <td width="40%" colspan=2 align="right"><B>TOTAL ' . $ter_nombre . '</B></td>
                            <td width="20%" colspan=1 align="right"><B>' . number_format($tot_ter, 2) . '</B></td>
                            <td width="20%" colspan=1 align="right"><B>' . number_format($tot_tercos, 2) . '</B></td>
                            <td width="20%" colspan=1 align="right"><B>' . number_format($tot_ter - $tot_tercos, 2) . '</B></td>
                        </tr>
                        <tr>
                            <td colspan=4 align="right"></td>
                        </tr>
                    ');
                    $tot_ter = 0;
                    $tot_tercos = 0;
                }
                $ter_nombre = ( $row['ter_id'] == "A" ) ? "ESTATAL" : "FEDERAL";

                $this->mixHtml('
                    <tr>
                        <th colspan=5>' . $ter_nombre . '</th>
                    </tr>
                ');

                $ter_id = $row['ter_id'];
                $paso = true;
            }

            $this->mixHtml('
                <tr style="padding:1px 1px 1px 1px;" >
                    <td width="10%">' . $row['clg_id'] . '</td>
                    <td width="30%"><small><b>' . $row['clg_nombre'] . '</b></small></td>
                    <td width="20%" align="right">' . number_format($row['techo'], 2) . '</td>
                    <td width="20%" align="right">' . number_format($row['costeo'], 2) . '</td>
                    <td width="20%" align="right">' . number_format($row['techo'] - $row['costeo'], 2) . '</td>
                </tr>
            ');
            $tot_ter += $row['techo'];
            $tot_gral += $row['techo'];

            $tot_tercos += $row['costeo'];
            $tot_gralcos += $row['costeo'];
        }

        $this->mixHtml('
                <tr>
                    <td width="40%" colspan=2 align="right"><B>TOTAL ' . $ter_nombre . '</B></td>
                    <td width="20%" colspan=1 align="right"><B>' . number_format($tot_ter, 2) . '</B></td>
                    <td width="20%" colspan=1 align="right"><B>' . number_format($tot_tercos, 2) . '</B></td>
                    <td width="20%" colspan=1 align="right"><B>' . number_format($tot_ter - $tot_tercos, 2) . '</B></td>
                </tr>
                <tr>
                    <td width="40%" colspan=2 align="right"><B>TOTAL GENERAL</B></td>
                    <td width="20%" colspan=1 align="right"><B>' . number_format($tot_gral, 2) . '</B></td>
                    <td width="20%" colspan=1 align="right"><B>' . number_format($tot_gralcos, 2) . '</B></td>
                    <td width="20%" colspan=1 align="right"><B>' . number_format($tot_gral - $tot_gralcos, 2) . '</B></td>
                </tr>
            </table>
            </div>
        ');
    }

    private function ClasePartida()
    {
        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];

        $sql = '
            SELECT clg_id, clg_nombre, pg_id,pe_id, pe_nombre, ter_id, sum(pre_original) as original
            FROM presupuesto 
                INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN cat_PartidaEspecifica USING (pg_id,pe_id) 
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id,fte_id,sft_id,ori_id, sor_id)
                INNER JOIN cat_ClaseGasto USING (clg_id)
            WHERE gpo_id = :adm_gpo 
                AND ur_id = :adm_ur
            GROUP BY ter_id,clg_id,pg_id,pe_id
            HAVING original <> 0
            ORDER BY ter_id,clg_id,pg_id,pe_id;
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($prm);
        $this->desconectar();

        $this->setContenedor('contenedorTechos');
        $html = '
            <div id="datos" class="panel panel-default" style="min-width: 500px; height: 400px; overflow: scroll;">
            <table id="TextoNormal" width="500px;" class="table table-striped table-hover table-bordered">
                <tr>
                    <th class="info" colspan="5"><h6 align="center"><strong>CLASE DE GASTO - PARTIDA</strong></h6></th>
                </tr>
                <tr>
                    <td class="info" align="center"><b>CLASE DE GASTO</b></td>
                    <td class="info" align="center"  ><b>NOMBRE CLASE DE GASTO</b></td>
                    <td class="info" align="center" ><b>PARTIDA</b></td>
                    <td class="info" align="center" ><b>NOMBRE PARTIDA</b></td>
                    <td class="info" align="center" ><b>MONTO PARTIDA</b></td>
                </tr>
        ';

        $this->mixHtml($html);

        while ($row = $this->extraer()) {
            $this->mixHtml('
                <tr style="padding:1px 1px 1px 1px;" >
                    <td >' . $row['clg_id'] . '</td>
                    <td>' . $row['clg_nombre'] . '</td>
                    <td  width="100px;">' . $row['pg_id'] . ' - ' . $row['pe_id'] . '</td>
                    <td >' . $row['pe_nombre'] . '</td>
                    <td >' . number_format($row['original'], 2) . '</td>
                </tr>
            ');
        }
        $this->mixHtml('
            </table>
            </div>
        ');
    }
}
new ControladorMuestraTechos();