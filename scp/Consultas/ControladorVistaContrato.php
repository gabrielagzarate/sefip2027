<?php

if (!session_id()) {
    session_start();
}
ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/InversionPublica.php';

class ControladorVistaContrato extends WebResponse
{
    protected $admGpo_id;
    protected $admUr_id;
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/Consultas/ControladorVistaContrato.php';

        $this->admGpo_id = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->admUr_id = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';

        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                switch ($this->metodo) {
                    case 'VistaContrato':
                        $this->VistaContrato();
                        break;
                    case 'llenaTablaContrato':
                        $this->llenaTablaContrato();
                        break;
                    case 'CargaPdf':
                        $this->CargaPdf();
                        break;
                    case 'validacontrato':
                        $this->validacontrato();
                        break;
                    case 'integrity':
                        $this->integrity();
                        break;
                    case 'revisa':
                        $this->fn_revisa();
                        break;
                    case 'DetalleClc':
                        $this->enClc();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $ejercicio = isset($this->prm->ejercicio) && $this->prm->ejercicio != '' ? $this->prm->ejercicio : APP_EJERCICIO;
        $tipo = false;
        if (isset($this->prm->tipo) && $this->prm->tipo) {
            $tipo = $this->prm->tipo;
        }
        $ur = '';
        $ipu = '';
        $clc = '';
        $rfc = '';
        $con = '';

        if (isset($this->prm->ur)) {
            $ur = $this->prm->ur;
        }

        if (isset($this->prm->ipu)) {
            $ipu = $this->prm->ipu;
        }

        if (isset($this->prm->clc)) {
            $clc = $this->prm->clc;
        }

        if (isset($this->prm->con)) {
            $con = $this->prm->con;
        }

        if (isset($this->prm->rfc)) {
            $rfc = $this->prm->rfc;
        }

        $tools = '<div class="row mb-3">';
        $tools .= '<fieldset class="col-md-3 d-flex flex-row">';
        $tools .= '<input class="form-control solonumero me-1" type="text" id="urbusca" style="width:60%;" maxlength="3" value="' . $ur . '" placeholder="UR" />';
        $tools .= '<input class="form-control me-1" type="text" id="numobra" maxlength="14" placeholder="Obra(prg-spr-py-oa)" />';
        $tools .= '</fieldset>';
        $tools .= '<fieldset class="col-md-3 d-flex flex-row">';
        $tools .= '<input class="form-control me-1" type="text" id="contrato" value="' . $con . '" placeholder="Numero Contrato" />';
        $tools .= '<input class="form-control" type="text" id="rfc_id" value="' . $rfc . '" placeholder="Proveedor (RFC)" />';
        $tools .= '</fieldset>';
        $tools .= '<fieldset class="col-md-3 d-flex flex-row">';
        $tools .= '<input class="form-control solonumero me-1" type="text" id="ipu_id" value="' . $ipu . '" placeholder="Id Contrato (#)" />';
        $tools .= '<input class="form-control solonumero" type="text" id="clc" maxlength="5" value="' . $clc . '" placeholder="CLC" />';
        $tools .= '</fieldset>';
        $tools .= '<div class="col-md-3 d-flex flex-row">';
        $tools .= '<button type="button" id="btnbusca" class="btn btn-outline-secondary btn-sm me-1"><i class="fa fa-search me-2"></i>Buscar</button>';
        $tools .= '<button type="button" id="btnreset" class="btn btn-outline-primary btn-sm"><i class="fa fa-refresh me-2"></i>Quitar Filtros</button>';
        $tools .= '</div>';
        $tools .= '</div>';

        $tabla = new stdClass();
        $tabla->id = 'tbl-contratos';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaContrato';
        $tabla->comunica->idtabla = 'tbl-contratos';
        $tabla->comunica->ur = '{ $("#urbusca").val()}';
        $tabla->comunica->NoContrato = '{ $("#contrato").val()}';
        $tabla->comunica->rfc = '{ $("#rfc_id").val()}';
        $tabla->comunica->ipu = '{ $("#ipu_id").val()}';
        $tabla->comunica->clc = '{ $("#clc").val()}';
        $tabla->comunica->obra = '{ $("#numobra").val()}';
        $tabla->comunica->ejercicio = '{ $("#ejercicio").val()}';
        $tabla->comunica->tipo = '{ $("#finiquitado").is(":checked")}';
        $tabla->filasAmostrar = [50, 100, 200, 300, 500];
        $tabla->numFilas = 100;

        $tabla->tools = $tools;
        $tr = [];
        $th = new stdClass();
        $th->texto = 'Ver';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '#';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Numero Contrato';
        $th->width = '12%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Proveedor ';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Importe Total';
        $th->width = '10%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Importe Anticipo';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Anti';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Cumpl';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'V.O.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Obras';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Edo.';
        array_push($tr, $th);

        $trbody = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        $td->class = 'btn-xs btn_gara';
        $td->attr = new stdClass();
        $td->attr->ipu_id = 'ipu_id';
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ipu_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'numCont';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'FechaC';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Proveedor';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Importe';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ImporteA';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Ant';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Cum';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Vio';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Obras';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'eip_nombre';
        $td->tipo = 'semaforo';
        $td->color = 'eip_color';
        $td->lbl = 'eip_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $html = '';

        $html .= $this->wrPonTabla($tabla);
        $this->mixHtml($html);

        $btnregresa = new stdClass();
        $btnregresa->id = 'comeback';
        $btnregresa->value = 'Regresar';
        $btnregresa->color = 'gris';
        $btnregresa->icon = 'solid fa-circle-left me-2';

        $btnF = new stdClass();
        $btnF->id = 'ejercicio';
        $btnF->selecciona = $ejercicio;
        $btnF->enDiv = false;
        $btnF->sql = '
            SELECT 1 AS id, 2026 AS nombre
        UNION ALL
            SELECT 2 AS id, 2025 AS nombre
        UNION ALL
            SELECT 3 AS id, 2024 AS nombre
        UNION ALL
            SELECT 4 AS id, 2023 AS nombre
        UNION ALL
            SELECT 5 AS id, 2022 AS nombre
        UNION ALL
            SELECT 6 AS id, 2021 AS nombre
        UNION ALL
            SELECT 7 AS id, 2020 AS nombre
        UNION ALL
            SELECT 8 AS id, 2019 AS nombre
        UNION ALL
            SELECT 9 AS id, 2018 AS nombre
        UNION ALL
            SELECT 10 AS id, 2017 AS nombre
        UNION ALL
            SELECT 11 AS id, 2016 AS nombre
        UNION ALL
            SELECT 12 AS id, 2015 AS nombre
        ';
        $btnF->camposVal = 'nombre';

        $fin = new stdClass();
        $fin->id = 'finiquitado';
        $fin->orientacion = 'H';
        $fin->label = 'Finiquitados';
        $fin->enDiv = false;
        $fin->seleccionado = $tipo;
        $fin->debug = false;
        $fin->onClick = $this->wrLlamaLlenaTabla('tbl-contratos');

        $botones = '
            <fieldSet class="col-md-2 col-xs-2 me-3">
                ' . $this->ponCombo($btnF) . '
            </fieldSet>
            <fieldSet class="col-md-2 col-xs-2">
                ' . $this->ponCheck2($fin) . '
            </fieldSet>
        ';

        $this->agregaTools($botones);

        $this->mixScript('
            ' . $this->wrLlamaLlenaTabla('tbl-contratos') . '

            $("#ejercicio").change(function() {
                ' . $this->wrLlamaLlenaTabla('tbl-contratos') . '
            });

            $("#btn_revisa").click(function() {
                comunica("' . $this->pantalla . '",{metodo:"integrity"});
            });

            $("#btnbusca").click(function() {
                ' . $this->wrLlamaLlenaTabla('tbl-contratos') . '
            });

            $("#btnreset").click(function() {
                $("#urbusca").val("");
                $("#contrato").val("");
                $("#rfc_id").val("");
                $("#ipu_id").val("");
                $("#clc").val("");
                $("#numobra").val("");
                ' . $this->wrLlamaLlenaTabla('tbl-contratos') . '
            });

            $("#urbusca").keyup(function(e) {
                if ($(this).val().length==3) {
                    $("#numobra").focus();
                }
            });
            $("#numobra").keyup(function(e) {
                if ($(this).val().length==0 && e.which==8) {
                    $("#urbusca").focus();
                }
            });

            $("#numobra").keypress(function(e) {
                if (e.which>47 && e.which <58) {
                    switch ($(this).val().length){
                        case 3: $("#numobra").val($("#numobra").val()+"-");
                        break;
                        case 6: $("#numobra").val($("#numobra").val()+"-");
                        break;
                        case 10: $("#numobra").val($("#numobra").val()+"-");
                        break;
                    }
                } else if (e.which!=0 && e.which!=8) {
                    return false;
                }
            });

            $(".solonumero").keypress(function(event){
                var anularTecla = false;
                var esPermitido = ([0,8].indexOf(event.which)) >= 0;
                anularTecla = ((event.which < 48 || event.which > 57) && !esPermitido) ;
                if( anularTecla ) event.preventDefault();
            });

            $("#comeback").click(function(){
                ' . ($tipo == 'N' && $ejercicio == 2016 ? ' $("nav").menusinpres("clickMenu"); ' : ' comunica("' . $this->pantalla . '",{"metodo":"main"});') . '
            });
        ');
    }

    private function llenaTablaContrato()
    {
        $gpour = $rfc_id = $contrato = $ipu = $gpo_id = $ur_id = '';
        $obra = $clc = '';
        $tipo = false;
        if (isset($this->prm->tipo) && $this->prm->tipo) {
            $tipo = $this->prm->tipo;
        }

        if (isset($this->prm->ur) && $this->prm->ur != '' && strlen($this->prm->ur) == 3) {
            $gpo_id = substr($this->prm->ur, 0, 1);
            $ur_id = substr($this->prm->ur, 1, 2);
            $gpour = ' AND gpo_id = ' . $gpo_id . ' AND ur_id = ' . $ur_id;
        } elseif (isset($this->prm->ur) && $this->prm->ur != '' && strlen($this->prm->ur) != 3) {
            $this->mixScript('  notify("La busqueda por UR require una unidad Responsable valida","warning")');
            return false;
        }
        /* ------------ SI BUSCA POR NUMERO DE OBRA PRG,SPR,PY Y/O OA ----------------*/

        $ejercicio = ' ipu_ejercicio = ' . (isset($this->prm->ejercicio) && $this->prm->ejercicio != '' ? $this->prm->ejercicio : 2016);

        /* ---------------------- SI BUSCA POR CLC -------------------------*/
        if (isset($this->prm->clc) && $this->prm->clc != '' && $gpour == '') {
            $this->mixScript('
                notify("La busqueda por CLC require la Unidad Responsable","warning")
            ');
            return false;
        } elseif (isset($this->prm->clc) && $this->prm->clc != '' && $gpour != '') {
            $clc = ' INNER JOIN (
                        SELECT ipu_id FROM cat_ObraClc
                        INNER JOIN scp_Clc USING( gpo_id,ur_id,clc_id )
                        WHERE gpo_id =' . $gpo_id . ' AND ur_id = ' . $ur_id . ' AND clc_id = ' . $this->prm->clc . '
                    )CLC USING( ipu_id )
            ';
            $ejercicio = ' ipu_id IS NOT NULL ';
            $gpour = '';
        }

        /* -------- SI BUSCA POR NUMERO DE OBRA PRG,SPR,PY Y/O OA ---------*/
        if (isset($this->prm->obra) && strlen($this->prm->obra) <= 14 && strlen($this->prm->obra) >= 2) {
            $partesObras = explode('-', $this->prm->obra);
            $wherep1 = 'WHERE gpo_id AND ur_id';

            if ($gpo_id != '' && $ur_id != '') {
                $wherep1 = 'WHERE gpo_id = ' . $gpo_id . ' AND ur_id = ' . $ur_id;
            }

            $obra = '
                INNER JOIN (
                        SELECT gpo_id,ur_id,epg_id FROM
                        enc_InversionObra
                        INNER JOIN enc_Programatico USING( gpo_id,ur_id,epg_id)
                        ' . $wherep1;
            switch (sizeof($partesObras)) {
                case 4:
                    $obra .= ' AND prg_id = ' . $partesObras[0];
                    $obra .= ' AND spr_id = ' . $partesObras[1];
                    $obra .= ' AND py_id  = ' . $partesObras[2];
                    $obra .= ' AND oa_id  = ' . $partesObras[3];
                    $this->mixScript('
                            notify("Busqueda por: programa - ' . $partesObras[0] . ', sub-programa - ' . $partesObras[1] . ', proyecto - ' . $partesObras[2] . ', obra/actividad - ' . $partesObras[3] . '","warning")
                        ');
                    break;
                case 3:
                    $obra .= ' AND prg_id = ' . $partesObras[0];
                    $obra .= ' AND spr_id = ' . $partesObras[1];
                    $obra .= ' AND py_id  = ' . $partesObras[2];
                    $this->mixScript('
                            notify("Busqueda por: programa - ' . $partesObras[0] . ', sub-programa - ' . $partesObras[1] . ', proyecto - ' . $partesObras[2] . '","warning")
                        ');
                    break;
                case 2:
                    $obra .= ' AND prg_id = ' . $partesObras[0];
                    $obra .= ' AND spr_id = ' . $partesObras[1];
                    $this->mixScript('
                            notify("Busqueda por: programa - ' . $partesObras[0] . ', sub-programa - ' . $partesObras[1] . '","warning")
                        ');
                    break;
                case 1:
                    $obra .= ' AND prg_id = ' . $partesObras[0];
                    $this->mixScript('
                            notify("Busqueda por: programa - ' . $partesObras[0] . ',"warning")
                        ');
                    break;
            }
            $obra .= ' )EPG USING( gpo_id,ur_id )
            ';
        } elseif (isset($this->prm->obra) && strlen($this->prm->obra) < 2 && $gpour != '' && $this->prm->obra != '') {
            $this->mixScript('
                notify("El numero de obra es incorrecto","warning")
            ');
            return false;
        }
        /* ---------------------- SI BUSCA POR NUMERO DE CONTRATO ---------------------------*/
        if (isset($this->prm->NoContrato) && $this->prm->NoContrato != '') {
            $contrato = ' AND ipu_numeroContrato = "' . $this->prm->NoContrato . '"';
        }
        /* -------------------- SI BUSCA POR PROVEEDOR -------------------------*/
        if (isset($this->prm->rfc) && $this->prm->rfc != '') {
            $rfc_id = ' AND rfc_id ="' . $this->prm->rfc . '"';
        }
        /* ------------------ SI BUSCA ID DE CONTRATO -----------------------*/
        if (isset($this->prm->ipu) && $this->prm->ipu != '') {
            $rfc_id = ' AND ipu_id =' . $this->prm->ipu . '';
        }

        /* ------------------- CONSULTA GENERAL ------------------------*/

        $sql = '
            SELECT gpo_id,ur_id,
                CONCAT(gpo_id,LPAD(ur_id,2,0))AS ur, ipu_id,ipu_numeroContrato AS numCont,
                ipu_fechaContrato AS FechaC,
                CONCAT(rfc_id," : ",rfc_nombre," ",rfc_paterno) AS Proveedor,ipu_importeTotal AS Importe, ipu_importeAnticipo AS ImporteA
                ,IF(Ant>0,"SI","NO") AS Ant,IF(Cum>0,"SI","NO") AS Cum,IF(Vio>0,"SI","NO") AS Vio,IF (Obras IS NULL,0,Obras) AS Obras
                ,eip_id,eip_nombre,
                eip_color
            FROM ' . BD_SINPRES . '.cat_InversionPublica
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING  ( vur_id )
            INNER JOIN ' . BD_SINPRES . '.cat_EstadoInversion USING ( eip_id )
                ' . $clc . $obra . '
            LEFT JOIN (
                SELECT ipu_id,COUNT(*)AS Obras
                FROM ' . BD_SINPRES . '.enc_InversionObras
                GROUP BY ipu_id
            )O USING( ipu_id )
            LEFT JOIN (
                SELECT ipu_id,
                    SUM(CASE WHEN tga_id="A" THEN 1 ELSE 0 END) AS Ant,
                    SUM(CASE WHEN tga_id="C" THEN 1 ELSE 0 END) AS Cum,
                    SUM(CASE WHEN tga_id="V" THEN 1 ELSE 0 END) AS Vio
                FROM ' . BD_SINPRES . '.enc_ContratoGarantia
                INNER JOIN ' . BD_SINPRES . '.cat_BancoGarantia USING( bga_id )
                WHERE "a" = "a"
                ' . $ipu . '
                GROUP BY ipu_id
            )Anticipos USING( ipu_id )
            INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING ( rfc_id )
            WHERE ' . $ejercicio . '
                ';
        if (!$tipo) {
            $sql .= ' AND eip_id IN ( 2, 3, 4 ) ';
        } else {
            $sql .= ' AND eip_id = 5 ';
        }

        $sql .= $gpour . $contrato . $ipu . $rfc_id . '';
        $sql .= '
            GROUP BY gpo_id,ur_id,ipu_id
            ORDER BY ipu_id,ur ASC';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $nav->idTabla = 'tbl-contratos';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros para la UR","warning",true)');
        }
        $this->mixScript('
            $(".btn_gara").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo : "VistaContrato",
                    gpo_id : $(this).attr("gpo_id"),
                    ur_id : $(this).attr("ur_id"),
                    ipu_id : $(this).attr("ipu_id"),
                    "tipo" : "' . $tipo . '",
                    ipu : $("#ipu_id").val(),
                    ur : $("#urbusca").val(),
                    clc : $("#clc").val(),
                    rfc : $("#rfc_id").val(),
                    con : $("#contrato").val()
                })
            });
        ');
    }
    /***********************************************************************************************************************************************/
    private function VistaContrato()
    {
        $gpo_id = $this->prm->gpo_id;
        $ur_id = $this->prm->ur_id;
        $ipu_id = $this->prm->ipu_id;

        $edocontrato = 0;

        /* ------------------ OBRAS DEL CONTRATO  ----------------------------------*/
        $obras = $this->obras($gpo_id, $ur_id, $ipu_id);
        if (!$obras) {
            $obras = '<div style="padding: 5px; text-align: center;" class="alert alert-info">NO HAY OBRAS VINCULADAS A ESTE CONTRATO </div>';
        }

        /* ------------------ RETENCIONES DEL CONTRATO  ----------------------------*/
        $retenciones = $this->retenciones($gpo_id, $ur_id, $ipu_id);
        if ($retenciones == false) {
            $retenciones = '<div style="padding: 5px; text-align: center;" class="alert alert-info">NO hay retenciones registradas para este contrato.</div>';
        }

        /* -------------------------------------------------------------------------*/

        $sql = '
            SELECT  vur_nombre,
                gpo_id,ur_id,vur_nombre AS ur_nombre,ipu_id,ipu_numeroContrato,ipu_objetoContrato,ipu_fechaContrato,ipu_inicio,ipu_termino,ipu_licitacion,ipu_importeTotal,ipu_importeAnticipo,
                CONCAT(rfc_nombre," ",rfc_paterno) AS Proveedor,ipu_porcentajeAnticipo AS porAnt,rfc_id,
                tci_nombre,mdc_nombre,eip_nombre,ipu_ejercicio,
                eip_id
            FROM ' . BD_SINPRES . '.cat_InversionPublica
            INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING ( vur_id )
            INNER  JOIN ' . BD_SINPRES . '.cat_Proveedores USING( rfc_id )
            INNER JOIN  ' . BD_SINPRES . '.cat_TipoContratoInversion USING( tci_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ModalidadContratacion USING(mdc_id)
            INNER JOIN ' . BD_SINPRES . '.cat_EstadoInversion USING(eip_id)
            WHERE ipu_id = ?
        ';

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar([$ipu_id]);
        $data = $conexion->extraer();

        $edocontrato = $data['eip_id'];
        $ejercicio = $data['ipu_ejercicio'];

        $html = '';
        if ($edocontrato == 3) {
            $html .= '<div class="alert alert-info" id="mensaje">';
            $html .= 'El contrato ha sido marcado como finiquitado por parte de la Unidad Responsable';
            $html .= '</div>';
        }
        if ($edocontrato == 5) {
            $html .= '<div class="alert alert-success" id="mensaje">';
            $html .= 'El finiquito del contrato es valido  segun la Secretaría de Finanzas';
            $html .= '</div>';
        }
        if ($edocontrato == 4) {
            $html .= '<div class="alert alert-warning" id="mensaje">';
            $html .= 'El finiquito del contrato es valido  segun la Secretaría de Finanzas';
            $html .= '</div>';
        }

        $html .= '<span style="color:#000; font-weight:bold; font-size:16px;">' . $data['gpo_id'] . sprintf('%02d', $data['ur_id']) . ' - ' . $data['ur_nombre'] . '</span><br/>';
        $html .= '<span style="color:#000; font-weight:bold; font-size:16px;"> CONTRATO NÚMERO : ' . $this->prm->ipu_id . '-' . $data['ipu_numeroContrato'] . '</span><br/>';
        $html .= '<div style="padding:3px 6px; border: 1px solid #000">';
        $html .= '<span style="color:#' . ($data['eip_id'] == 4 ? '000' : '000') . '; font-weight:bold; font-size:16px;"> ESTADO:' . strtoupper($data['eip_nombre']) . '</span>';
        $html .= '<span style="margin-left:20px;"> ' . ($data['ipu_ejercicio'] == 2019 ? 'CONTRATO CAPTURADO EN EL AÑO ACTUAL' : 'CONTRATO CAPTURADO EN ' . $data['ipu_ejercicio']) . '';
        $html .= '</span></div>';

        $html .= '<div class="infocontrato idcontrato"><title>Id Contrato</title>' . $data['ipu_id'] . '</div>';
        $html .= '<div class="infocontrato numContrato"><title>Numero Contrato</title>' . $data['ipu_numeroContrato'] . '</div>';
        $html .= '<div class="infocontrato idcontrato"><title>Fecha del Contrato</title>' . $data['ipu_fechaContrato'] . '</div>';
        $html .= '<div class="infocontrato periodoContrato"><title>Inicio - Termino</title>' . $data['ipu_inicio'] . ' -' . $data['ipu_termino'] . ' </div>';

        $html .= '<div class="infocontrato idcontrato" style="width:auto;"><title>Importe</title><span style="float:left;">$</span><span style="float:right;">' . number_format($data['ipu_importeTotal'], 2) . '</span></div>';
        $html .= '<div class="infocontrato idcontrato" style="width:auto;"><title>Anticipo - ' . round($data['porAnt'], 0) . '%</title><span style="float:left;">$</span><span style="float:right;">' . number_format($data['ipu_importeAnticipo'], 2) . '</span></div>';

        $html .= '<div class="infocontrato periodoContrato" style="max-width:250px;"><title>Tipo y Modalidad </title>' . $data['tci_nombre'] . '<br/>';
        $html .= '' . $data['mdc_nombre'] . '</div>';
        $html .= '<div class="infocontrato periodoContrato mb-2" style="max-width:550px; overflow:auto;"><title>Contratista ' . $data['rfc_id'] . ' </title>' . $data['Proveedor'] . '</div>';

        $html .= '<br/><div class="infocontrato objContrato" style="height:auto; min-height:10px; width:98%; max-height:500px; overflow:initial !important;"><title>Objeto del Contrato</title><div class="div_ajustable">' . $data['ipu_objetoContrato'] . '</div></div>';

        /* RETENCIONES  QUE TIENE EL CONTRATO  */

        $html .= '<hr/> <div class="contrato_generales" ste="close" style="height: 35px;">
                    ' . $retenciones . '';
        $html .= '  </div><br/>';

        /* OBRAS VINCULADAS AL CONTRATO 2015 , 2016 , 2017, 2018, 2019  */
        $html .= ' <div class="contrato_generales" ste="close" style="height: 50px;" >
                    ' . $obras . '';
        $html .= ' </div><br/>';

        /* ------------------ GARANTIAS DEL CONTRATO ---------------------------- */
        $gar = $this->garantias($gpo_id, $ur_id, $ipu_id);

        if ($gar->vacio) {
            $html .= '<legend style="font-size:16px;">El contrato no tiene Garantías</legend>';
        } else {
            $html .= '<legend style="font-size:16px;">Garantías del Contrato</legend>';

            if ($gar->anticipo != '') {
                $html .= '<div class="infocontrato  contratoanimate " style="width:98%; min-height:18px; overflow:hidden;">
                                <div class="contratitle">Garantías de Anticipo</div>
                                <span class="collapsewarranty" id="anticipos">Expandir</span>
                                <div id="cont_anticipos" style="width:100%">' . $gar->anticipo . '</div>
                            </div>';
            }
            if ($gar->cumplimiento != '') {
                $html .= '<div class="infocontrato  contratoanimate " style="width:98%" max-height:300px;  min-height:18px; overflow:hidden;">
                        <div class="contratitle">Garantías de Cumplimiento</div>
                            <span class="collapsewarranty" id="cumplimiento">Expandir</span>
                            <div id="cont_cumplimiento" style="width:100%">' . $gar->cumplimiento . '    </div>
                        </div>';
            }
            if ($gar->vicios != '') {
                $html .= '<div class="infocontrato  contratoanimate " style="width:98%" max-height:300px;  min-height:18px; overflow:hidden;">
                        <div class="contratitle">Garantías de Vicios Ocultos</div>
                            <span class="collapsewarranty" id="vicios">Expandir</span>
                            <div id="cont_vicios" style="width:100%">' . $gar->vicios . '    </div>
                        </div>';
            }
        }

        /** --------------------- CLC QUE HAN USADO ESTE CONTRATO -------------------------- */
        $html .= '<hr/><br/>
            <legend id="detalleclc" style="font-size:16px; cursor:pointer;">
                <a href="#">Ver Uso del Contrato en CLC</a>
            </legend>';
        $html .= '<script type="text/javascript" id="scri_pdf" />';

        $this->mixHtml($html);

        $btnregresa = new stdClass();
        $btnregresa->id = 'comeback';
        $btnregresa->value = 'Regresar';
        $btnregresa->color = 'gris';
        $btnregresa->icon = 'solid fa-circle-left me-2';

        $btnvalida = new stdClass();
        $btnvalida->id = 'validacontrato';
        $btnvalida->value = 'Validar Finiquito';
        $btnvalida->icon = 'ok';
        $btnvalida->color = 'verde';

        $btnrechaza = new stdClass();
        $btnrechaza->id = 'rechazacontrato';
        $btnrechaza->value = 'Rechazar';
        $btnrechaza->icon = 'ok';
        $btnrechaza->color = 'rojo';

        $botones = $this->ponBoton($btnregresa);
        if ($edocontrato == 3) {
            $botones .= $this->ponBoton($btnvalida);
            $botones .= $this->ponBoton($btnrechaza);
        }

        $this->agregaTools($botones);
        $this->mixScript('
            $("#comeback").click(function(){
                comunica("' . $this->pantalla . '",{
                    "tipo":"' . $this->prm->tipo . '",
                    ejercicio:"' . $ejercicio . '"
                    ' . (isset($this->prm->ipu) && $this->prm->ipu != '' ? ',ipu:"' . $this->prm->ipu . '"' : '') . '
                    ' . (isset($this->prm->ur) && $this->prm->ur != '' ? ',ur :"' . $this->prm->ur . '"' : '') . '
                    ' . (isset($this->prm->clc) && $this->prm->clc != '' ? ',clc:"' . $this->prm->clc . '"' : '') . '
                    ' . (isset($this->prm->rfc) && $this->prm->rfc != '' ? ',rfc:"' . $this->prm->rfc . '"' : '') . '
                    ' . (isset($this->prm->con) && $this->prm->con != '' ? ',con:"' . $this->prm->con . '"' : '') . '
                });    });


            $(".button_contrato").click(function(){
                m = $(this).parent().parent().parent().parent().parent().attr("id");
                n =  document.getElementById(m).offsetHeight;

                if ($("#"+m).parent().attr("ste") == "close") {
                    $("#"+m).parent().css("height",n+"px");
                    $("#"+m).parent().attr("ste","open")
                } else {
                    $("#"+m).parent().css("height","30px");
                    $("#"+m).parent().attr("ste","close")
                }
            });

            $("#validacontrato").click(function() {
                alertify.confirm("VALIDAR FINIQUITO DE CONTRATO", "El contrato no será viable para nuevas CLCs ¿Confirma que desea Validar el finiquito del Contrato?", function(){
                    $("#validacontrato").remove();
                    $("#rechazacontrato").remove();
                    $("#mensaje").html("EL FINIQUITO DEL CONTRATO HA SIDO VALIDADO POR SEFIN").removeAttr("class").addClass("alert alert-success");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"validacontrato",
                        "mov":"V",
                        "ipu_id":"' . $ipu_id . '"
                    });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $("#rechazacontrato").click(function(){
                alertify.confirm("RECHAZAR EL FINIQUITO DE CONTRATO", "¿Confirme que desea rechazar el finiquito del Contrato?", function(){
                    $("#validacontrato").remove();
                    $("#rechazacontrato").remove();
                    $("#mensaje").html("EL FINIQUITO DEL CONTRATO HA SIDO RECHAZADO POR SEFIN").removeAttr("class").addClass("alert alert-danger");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"validacontrato",
                        "mov":"R",
                        "ipu_id":"' . $ipu_id . '"
                    });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".collapsewarranty").click(function() {
                if ($(this).parent().css("height")=="24px") {
                    id  = $(this).attr("id");
                    he = document.getElementById("cont_"+id ).offsetHeight;
                    he = he + 30;
                    $(this).parent().css("height",he+"px");
                } else {
                    $(this).parent().css("height","24px");
                }

            });

            $(".doctos_contrato").click(function(){
                m = $(this).attr("id");
                if ($("#fs"+m).length != 1) {
                    load_viewer(m,$(this).html(),"frame");
                    comunica("' . $this->pantalla . '",{
                        metodo:"CargaPdf",
                        dur_id:$(this).attr("id")
                    });
                }
            });
            $("#detalleclc").click(function() {
                load_viewer(' . $this->prm->ipu_id . ',"Uso de Contrato en CLC ","d");
                comunica("' . $this->pantalla . '",{
                    metodo:"DetalleClc",
                    ipu_id:"' . $this->prm->ipu_id . '"
                });
            });
        ');
    }
    /***********************************************************************************************************************************************/
    private function CargaPdf()
    {

        $this->setContenedor('fs' . $this->prm->dur_id);
        $sql = '
            SELECT * FROM ' . BD_SINPRES . '.cat_DocumentosUr WHERE dur_id= ?
        ';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar([$this->prm->dur_id]);
        $dat = $con->extraer();
        $con->desconectar();
        $miniScript = '';

        // Comentario de Jordy Jesus Castro Avendano
        // De momento para el 2015, 2016, 2017 se mostraran los archivos desde la ruta https://egresos.finanzasoaxaca.gob.mx + su ruta
        // ejemplo https://egresos.finanzasoaxaca.gob.mx/sinpres2017/scp/doctosUr/algo.pdf
        // sera esto de manera provisional hasta que se defina bien esta parte

        switch ($dat['dur_ejercicio']) {
            case 2026:
                $fichero = $dat['dur_nombreGuardado'];
                $r = new RutaArchivoSefip();
                $r->setModulo('IP');
                $r->setAnioFichero(APP_EJERCICIO);
                $ruta = $r->getRutaMod();
                $ruta .= $fichero;
                $dest = __DIR__ . '/../../tmps/' . $fichero;
                copy($ruta, $dest);
                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","lib/file_download.php?r=' . $fichero . '")';
                break;
            case 2025:
            case 2024:
            case 2023:
                $fichero = basename($dat['dur_ruta']); // con extensión
                $rutaBase = $_SERVER['DOCUMENT_ROOT'] . dirname($dat['dur_ruta']) . '/';

                $rutaConExt = $rutaBase . $fichero;
                $rutaSinExt = $rutaBase . pathinfo($fichero, PATHINFO_FILENAME);

                if (file_exists($rutaConExt)) {
                    $rutaOrigen = $rutaConExt;
                } elseif (file_exists($rutaSinExt)) {
                    $rutaOrigen = $rutaSinExt;
                } else {
                    $this->mixScript('notify("El archivo no se encuentra disponible en el servidor","error",true);');
                    return;
                }

                $dest = __DIR__ . '/../../tmps/' . $fichero;
                copy($rutaOrigen, $dest);

                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","lib/file_download.php?r=' . $fichero . '");';
                break;
            case 2022:
                $otro = 'https://sefip.finanzasoaxaca.gob.mx/2022/';
                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","' . $otro . 'scp/' . $dat['dur_ruta'] . '");';
                break;
            case 2021:
                $otro = 'https://sefip.finanzasoaxaca.gob.mx/2021/';
                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","' . $otro . 'scp/' . $dat['dur_ruta'] . '");';
                break;
            case 2020:
                $otro = 'https://sefip.finanzasoaxaca.gob.mx/2020/';
                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","' . $otro . 'scp/' . $dat['dur_ruta'] . '");';
                break;
            case 2019:
                $otro = 'https://sefip.finanzasoaxaca.gob.mx/2019/';
                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","' . $otro . 'scp/' . $dat['dur_ruta'] . '");';
                break;
            case 2018:
                $otro = 'https://sefip.finanzasoaxaca.gob.mx/';
                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","' . $otro . 'scp/' . $dat['dur_ruta'] . '");';
                break;
            case 2015:
            case 2016:
            case 2017:
                $otro = 'https://egresos.finanzasoaxaca.gob.mx/sinpres' . $dat['dur_ejercicio'] . '/';
                $miniScript = '$("#det_fs' . $this->prm->dur_id . '").attr("src","' . $otro . 'scp/' . $dat['dur_ruta'] . '");';
                break;
        }

        $this->mixScript($miniScript);
    }

    private function validacontrato()
    {

        if ($this->prm->mov == 'R') {
            $sql = 'UPDATE ' . BD_SINPRES . '.cat_InversionPublica SET eip_id = 4 WHERE ipu_id = ?';
        } else {
            $sql = 'UPDATE ' . BD_SINPRES . '.cat_InversionPublica SET eip_id = 5 WHERE ipu_id = ?';
        }

        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar([$this->prm->ipu_id]);
        $c->desconectar();
        unset($c);

        if ($this->prm->mov == 'R') {
            $this->mixScript('notify("Contrato rechazado","warning");');
        } else {
            $this->mixScript('notify("Contrato validado","success");');
        }
    }
    /***********************************************************************************************************************************************/
    private function obras($gpo_id, $ur_id, $ipu_id)
    {
        //Se esta modificando la consulta conforme al modelo claveFinanciamiento2020 en la bd de sinpres
        $con = new AccesoDatos();
        $sql = '
            SELECT LPAD(prg_id,3,0) AS prg_id,
                LPAD(spr_id,2,0) AS spr_id,
                LPAD(py_id,3,0)  AS py_id,
                LPAD(oa_id,3,0)  AS oa_id,oa_nombre,SUM(pv.pre_vigente)AS Presupuesto,
                dur_ruta,
                dur_nombreOriginal,
                dur_id,ecf_clave,
                MAX(cfi_nombre) AS cfi_nombre,
                eio_ejercicio
            FROM ' . BD_SINPRES . '.enc_InversionObras a
                INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento USING ( ecf_id )
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id,ori_id,sor_id,cfi_id,cfi_anio )
                INNER JOIN ' . BD_SINPRES . '.cat_DocumentosUr USING( dur_id )
                INNER JOIN ' . BD_CTRL . '.enc_Programatico USING( epg_id,prg_id,spr_id,py_id,oa_id )
                INNER JOIN ' . BD_CTRL . '.cat_ObraActividad USING(  gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                INNER JOIN ' . BD_CTRL . '.presupuesto USING( gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id ,cff_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN ' . BD_CTRL . '.presupuestoVigente pv USING( gpo_id,ur_id,epg_id,pg_id,pe_id,tcp_id)
            WHERE ipu_id = ?
            AND eio_ejercicio = ' . APP_EJERCICIO . '
            GROUP BY epg_id,a.ecf_id,dur_id
        ';

        // para que este muy saturado las letras text-shadow:1px 1px 2px #000;
        $obras = '
            <table id="obras_contrato" cellpadding="5" width="100%"
                style="margin-bottom:5px;">
                <tr>
                    <td bgcolor="FFFFFF" colspan="8" style="border-bottom:1px solid #f6d28c">
                        <div class="contrato_generales_titulo">OBRAS DE CONTRATO
                        <span class="button_contrato" style="color:black; box-shadow:1px 1px 1px #999;">VER</span>
                    </td>
                </tr>
                <tr style="background-color:#FAFAFA; border-bottom:1px solid #BBB; font-size:10px; text-shadow:1px 1px 2px;">
                <th></th>
                    <th>NÚMERO</th>
                    <th>NOMBRE DE LA OBRA</th>
                    <th>CVEFIN</th>
                    <th>DESCRIPCIÓN CVEFIN</th>
                    <th style="width:160px; text-align:center;">PRESUPUESTO</th>
                    <th>OFICIO DE AUT. SPIP</th>
                    <th>AÑO DE LA OBRA</th>
                </tr>
        ';

        $con->conectar();
        $con->setSql($sql);
        $con->consultar([$ipu_id]);

        if ($con->getNumeroRegistros() == 0) {
            $obras .= '
            <tr>
                <td style="border-bottom:1px solid #BBB; font-size:10px; text-shadow:1px 1px 2px;" colspan="7" align="center">
                    NO HAY OBRAS VINCULADAS A ESTE CONTRATO
                </td>
            </tr>';
        }

        while ($data = $con->extraer()) {
            $obras .= '
            <tr class="info" style="background-color:#FFF;">
                <td></td>
                <td style ="border-left:1px dotted #333;">
                    ' . $data['prg_id'] . '-' . $data['spr_id'] . '-' . $data['py_id'] . '-' . $data['oa_id'] . '
                </td>
                <td style ="border-left:1px dotted #333;">
                    <div class="div_ajustable">' . $data['oa_nombre'] . '</div>
                </td>
                <td style ="border-left:1px dotted #333;">
                    ' . $data['ecf_clave'] . '
                </td>
                <td style ="border-left:1px dotted #333;">
                    ' . $data['cfi_nombre'] . '
                </td>
                <td style ="border-left:1px dotted #333; text-align:right;">
                    <span style="float:left; margin-right:5px;"> $</span>' . number_format($data['Presupuesto'], 2) . '
                </td>
                <td style ="border-left:1px dotted #333;">
                    <div id="' . $data['dur_id'] . '" title="' . $data['dur_nombreOriginal'] . '" class="doctos_contrato">' . $data['dur_nombreOriginal'] . '</div>
                </td>
                <td>
                    ' . $data['eio_ejercicio'] . '
                </td>
            </tr>';
        }
        $obras .= '</table>';
        $con->desconectar();
        unset($data);
        unset($conexion);
        return $obras;
    }
    /***********************************************************************************************************************************************/
    private function garantias($gpo_id, $ur_id, $ipu_id)
    {

        $return = new stdClass();
        $return->vacio = false;

        $sql = '
            SELECT
                tga_id,stg_id,stg_nombre,bga_id,iaf_id,iaf_nombre,bga_importeGarantia,bga_fechaEmision,bga_numeroDocumento,
                tan_nombre,dur_id,dur_nombreOriginal,bga_ejercicio
                FROM ' . BD_SINPRES . '.enc_ContratoGarantia
                INNER JOIN ' . BD_SINPRES . '.cat_BancoGarantia USING( bga_id )
                INNER JOIN ' . BD_SINPRES . '.cat_InstitucionAfianzadora USING( iaf_id )
                INNER JOIN ' . BD_SINPRES . '.cat_SubtipoGarantia USING(tga_id,stg_id)
                INNER JOIN ' . BD_SINPRES . '.cat_TipoGarantia USING(tga_id)
                INNER JOIN ' . BD_SINPRES . '.enc_GarantiaDocumento USING( bga_id )
                INNER JOIN ' . BD_SINPRES . '.cat_TipoAnexo USING( tan_id)
                INNER JOIN ' . BD_SINPRES . '.cat_DocumentosUr USING( dur_id)
            WHERE ipu_id = ?
            ORDER BY  bga_id,tga_id,stg_id';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar([$ipu_id]);
        $data = $conexion->extraer();
        if ($conexion->getNumeroRegistros() == 0) {
            $return->vacio = true;
            $conexion->desconectar();
            return $return;
        }

        $anticipo = '';
        $cumplimi = '';
        $viciosoc = '';
        $detalle = '';
        $bga = $data['bga_id'];
        $tga = $data['tga_id'];

        $garantia = '
            <table cellpadding="2" cellspacing="0" style="background-color:#FFF;width:100%; border:1px solid #999; border-radius:4px; box-shadow:1px 2px 3px #888; font-size:12px;">
            <tr style="background-color:#FFF">
                <td  width="40" rowspan="' . ($data['stg_id'] == 1 ? '4' : '2') . '" align="center" style="padding:15px; border:1px solid #999; background-color:rgb(229,210,153)">
                    <span style="font-size:18px;color:black; text-shadow: 1px 1px 2px #000;font-weight: bold;">' . $data['bga_id'] . '</span>
                </td>
                <td style="color:black; text-shadow: 1px 1px 2px #000;font-weight: bold;" align="center" ' . ($data['stg_id'] == 1 ? 'colspan="4"' : '') . '><strong>' . $data['stg_nombre'] . '</strong>  -  GARANTIA ' . $data['bga_ejercicio'] . ' </td>
            </tr>';

        if ($data['stg_id'] == 1) {
            $garantia .= '
                <tr style="background-color:#EAEAEA;"><td>Institucion Afianzadora</td><td>Num Docto</td><td>Fecha de emisión</td><td>Importe</td></tr>
                <tr>
                    <td style="border-bottom:2px dotted #999">' . $data['iaf_nombre'] . '</td>
                    <td style="border-bottom:2px dotted #999">' . $data['bga_numeroDocumento'] . '</td>
                    <td style="border-bottom:2px dotted #999">' . $data['bga_fechaEmision'] . '</td>
                    <td style="border-bottom:2px dotted #999">$' . number_format($data['bga_importeGarantia'], 2) . '</td>
                </tr>';
        }

        $garantia .= '<tr><td ' . ($data['stg_id'] == 1 ? 'colspan="4"' : '') . '>';
        $detalle .= '<div id="' . $data['dur_id'] . '" class="doctos_contrato">' . $data['tan_nombre'] . ' - ' . $data['dur_nombreOriginal'] . ' </div>';
        $fin = '</td></tr></table>';

        while ($data = $conexion->extraer()) {
            if ($bga != $data['bga_id']) {
                switch ($tga) {
                    case 'A':
                        $anticipo .= $garantia . $detalle . $fin;
                        break;
                    case 'C':
                        $cumplimi .= $garantia . $detalle . $fin;
                        break;
                    case 'V':
                        $viciosoc .= $garantia . $detalle . $fin;
                        break;
                }
                $garantia = '
                <table cellpadding="2" cellspacing="0" style="background-color:#FFF;width:100%; border:1px solid #999; border-radius:4px; box-shadow:1px 2px 3px #888; font-size:12px;">
                <tr style="background-color:#FFF">
                    <td  width="40" rowspan="' . ($data['stg_id'] == 1 ? '4' : '2') . '" align="center" style="padding:15px; border:1px solid #999;background-color:rgb(229,210,153)">
                        <span style="font-size:18px; color:black; text-shadow: 1px 1px 2px #000; font-weight: bold;">' . $data['bga_id'] . '</span></td>
                    <td style="color:black; text-shadow: 1px 1px 2px #000;font-weight: bold;" align="center" ' . ($data['stg_id'] == 1 ? 'colspan="4"' : '') . ' ><strong>' . $data['stg_nombre'] . '</strong>';
                $garantia .= ' -  GARANTIA ' . $data['bga_ejercicio'];
                $garantia .= '</td></tr>';

                if ($data['stg_id'] == 1) {
                    $garantia .= '
                    <tr style="background-color:#EAEAEA;"><td>Institucion Afianzadora</td><td>Num Docto</td><td>Fecha</td><td>Importe</td></tr>
                    <tr>
                        <td style="border-bottom:2px dotted #999">' . $data['iaf_nombre'] . '</td>
                        <td style="border-bottom:2px dotted #999">' . $data['bga_numeroDocumento'] . '</td>
                        <td style="border-bottom:2px dotted #999">' . $data['bga_fechaEmision'] . '</td>
                        <td style="border-bottom:2px dotted #999">$' . number_format($data['bga_importeGarantia'], 2) . '</td>
                    </tr>';
                }
                $garantia .= '<tr><td ' . ($data['stg_id'] == 1 ? 'colspan="4"' : '') . '>';
                $detalle = '';
                $bga = $data['bga_id'];
                $tga = $data['tga_id'];
            }
            $detalle .= '<div id="' . $data['dur_id'] . '" class="doctos_contrato">' . $data['tan_nombre'] . ' - ' . $data['dur_nombreOriginal'] . '</div>';
        }
        switch ($tga) {
            case 'A':
                $anticipo .= $garantia . $detalle . $fin;
                break;
            case 'C':
                $cumplimi .= $garantia . $detalle . $fin;
                break;
            case 'V':
                $viciosoc .= $garantia . $detalle . $fin;
                break;
        }
        $return->anticipo = $anticipo;
        $return->cumplimiento = $cumplimi;
        $return->vicios = $viciosoc;
        return $return;
    }

    private function retenciones($gpo_id, $ur_id, $ipu_id)
    {
        $con = new AccesoDatos();
        $con->conectar();
        $sql = '
            SELECT gpo_id_2015 AS gpo_id, ur_id_2015 AS ur_id FROM ' . BD_SINPRES . '.vin_UnidadResponsable_2015_2016
            WHERE gpo_id_2016 = ? AND ur_id_2016 = ?
        ';
        $con->setSql($sql);
        $con->consultar([$gpo_id, $ur_id]);
        $ant = $con->extraer();

        $con->desconectar();

        $sql = '
            SELECT ipu_id,tre_nombre,eir_descripcion,cgc_cuenta,cgc_nombre,eir_ejercicio
            FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
            INNER JOIN ' . BD_SINPRES . '.cat_RetencionCuenta USING( tre_id,rcu_id )
            INNER JOIN ' . BD_SINPRES . '.cat_TipoRetencion USING( tre_id)
            LEFT JOIN cg_CuentaCuarto USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id)
            WHERE ipu_id = ?
            ';

        $obras = '<table id="retenciones_contrato" cellpadding="5" width="100%"
                    style="margin-bottom:5px; font-size:11px;">
                    <tr>
                        <td bgcolor="FFFFFF" colspan="6" style="border-bottom:1px solid #AAA">
                        <div class="contrato_generales_titulo">
                            RETENCIONES DEL CONTRATO
                            <span class="button_contrato" style="color:black; box-shadow:1px 1px 1px #999;">VER</span>
                        </div>
                        </td>
                    </tr>
                    <tr style="background-color:#FFFFFF; border-bottom:1px solid #BBB; font-size:10px; text-shadow:1px 1px 2px;">
                    <th></th>
                        <th style ="">NÚMERO</th>
                        <th style ="">TIPO RETENCIÓN</th>
                        <th style ="">EJERCICIO</th>
                    </tr>';

        $con->conectar();
        $con->setSql($sql);
        $con->consultar([$ipu_id]);

        if ($con->getNumeroRegistros() == 0) {
            $con->desconectar();
            unset($con);
            return false;
        }

        $i = 0;
        while ($data = $con->extraer()) {
            $i++;
            $obras .= '<tr class="info" style="background-color:#FFF;"><td></td>';
            $obras .= '<td style ="border-left:1px solid #BBB;">' . $i . '</td>';
            $obras .= '<td style ="border-left:1px solid #BBB;">' . $data['tre_nombre'];

            if ($data['tre_nombre'] == 'OTRAS') {
                $obras .= ' - ' . $data['eir_descripcion'];
            }
            $obras .= '</td>';

            $obras .= '<td>' . $data['eir_ejercicio'] . '</td></tr>';
        }
        $obras .= '</table>';
        $con->desconectar();
        unset($data);
        unset($conexion);

        return $obras;
    }

    private function enClc()
    {
        $ipu_id = $this->prm->ipu_id;
        $this->setContenedor('det_fs' . $ipu_id);
        $anticipo = 0;
        $percepciones = 0;
        $contrato = InversionPublica::get_InversionPublica($ipu_id);
        $html = '<div style="background-color:#f4eee7; padding:5px; border-radius:3px; margin-bottom:5px; font-size:16px;">
            <strong>Contrato: ' . $contrato['ipu_numeroContrato'] . '
                estado: ' . $contrato['eip_nombre'] . '
                de la  UR ' . $contrato['vur_nombre'] . '
            </strong>
            </div>';
        for ($i = 2021; $i <= APP_EJERCICIO; $i++) {
            $datos = InversionPublica::get_UsoClc($i, $ipu_id);
            if ($datos != 0) {
                $f = new stdClass();
                $g = new stdClass();
                $h = new stdClass();
                $nn = '
                    <table style="width: 100%" class="table table-striped table-bordered">
                        <tr style="border-bottom:1px dotted #999">
                            <td align="center" class="warning"  width="10%"><strong>CLC</strong></td>
                            <td align="right"  class="warning"  width="25%"><strong>Percepcion</strong></td>
                            <td align="right"  class="warning"  width="25%"><strong>Retencion</strong></td>
                            <td align="right"  class="warning"  width="25%"><strong>Diferencia</strong></td>
                            <td align="center" class="warning"  width="15%"><strong>Estado</strong></td>
                        </tr>';
                $f->html = $nn;
                $g->html = $nn;
                $h->html = $nn;

                $f->percepcion = 0;
                $f->clcs = 0;
                $f->retencion = 0;
                $g->percepcion = 0;
                $g->clcs = 0;
                $g->retencion = 0;
                $h->percepcion = 0;
                $h->clcs = 0;
                $h->retencion = 0;

                foreach ($datos as $data) {
                    $per = $data['clc_percepcion'];
                    $ret = $data['clc_retencion'];
                    switch ($data['tcl_id']) {
                        case 'F':
                            $f->html .= '
                                <tr>
                                    <td align="center">' . $data['clc_id'] . '</td>
                                    <td align="right"> $ ' . number_format($per, 2) . '</td>
                                    <td align="right"> $ ' . number_format($ret, 2) . '</td>
                                    <td align="right"> $ ' . number_format($per - $ret, 2) . '</td>
                                    <td align="center">' . $data['ecl_id'] . '-' . $data['ecl_nombre'] . '</td>
                                </tr>';
                            $f->percepcion += $per;
                            $percepciones += $per;
                            $f->retencion += $ret;
                            $f->clcs++;
                            break;
                        case 'G':
                            $g->html .= '
                                <tr>
                                    <td align="center">' . $data['clc_id'] . '</td>
                                    <td align="right"> $ ' . number_format($per, 2) . '</td>
                                    <td align="right"> $ ' . number_format($ret, 2) . '</td>
                                    <td align="right"> $ ' . number_format($per - $ret, 2) . '</td>
                                    <td align="center">' . $data['ecl_id'] . '-' . $data['ecl_nombre'] . '</td>
                                </tr>';
                            $g->percepcion += $per;
                            $percepciones += $per;
                            $g->retencion += $ret;
                            $g->clcs++;
                            break;
                        case 'H':
                            $h->html .= '
                                <tr>
                                    <td align="center">' . $data['clc_id'] . '</td>
                                    <td align="right"> $ ' . number_format($per, 2) . '</td>
                                    <td align="right"> $ ' . number_format($ret, 2) . '</td>
                                    <td align="right"> $ ' . number_format($per - $ret, 2) . '</td>
                                    <td align="center">' . $data['ecl_id'] . '-' . $data['ecl_nombre'] . '</td>
                                </tr>';
                            $h->percepcion += $data['clc_percepcion'];
                            $anticipo += $data['clc_percepcion'];
                            $h->retencion += $data['clc_retencion'];
                            $h->clcs++;
                            break;
                    }
                }
                $f->html .= '
                        <tr>
                            <td class="warning" style="color:black"><strong>Totales</strong></td>
                            <td class="success"  align="right">$' . number_format($f->percepcion, 2) . '</td>
                            <td class="success"  align="right">$' . number_format($f->retencion, 2) . '</td>
                            <td class="success" align="right">$' . number_format(($f->percepcion - $f->retencion), 2) . '</td>
                            <td class="danger"></td>
                        </tr></table>';
                $g->html .= '
                        <tr>
                            <td class="warning" style="color:black"><strong>Totales</strong></td>
                            <td class="success" align="right">$' . number_format($g->percepcion, 2) . '</td>
                            <td class="success" align="right">$' . number_format($g->retencion, 2) . '</td>
                            <td class="success" align="right">$' . number_format(($g->percepcion - $g->retencion), 2) . '</td>
                            <td class="danger"></td>
                        </tr></table>';
                $h->html .= '
                        <tr>
                            <td class="warning" style="color:black"><strong>Totales</strong></td>
                            <td class="success" align="right">$' . number_format($h->percepcion, 2) . '</td>
                            <td class="success" align="right">$' . number_format($h->retencion, 2) . '</td>
                            <td class="success" align="right">$' . number_format(($h->percepcion - $h->retencion), 2) . '</td>
                            <td class="danger"></td>
                        </tr></table>';

                $html .= '<div style="background-color:#FFF; padding:4px; border-radius:3px; margin-bottom:10px;">
                    <h5>CLCS DEL EJERCICIO ' . $i . '</h5>
                        ' . ($f->clcs > 0 ? '<div><span style="text-shadow:1px 2px 3px #333;">CONTRATO</span>' . $f->html . '</div>' : '') . '
                        ' . ($g->clcs > 0 ? '<div><span style="text-shadow:1px 2px 3px #333;">ADMINISTRACION DIRECTA</span>' . $g->html . '</div>' : '') . '
                        ' . ($h->clcs > 0 ? '<div><span style="text-shadow:1px 2px 3px #333;">ANTICIPO DE OBRA</span>' . $h->html . '</div>' : '');

                $html .= '</div>';
            } else {
                $html .= '<div style="background-color:#FFF; padding:4px; border-radius:3px; margin-bottom:5px;">
                NO HAY CLCS PARA EL EJERCICIO ' . $i . '</div>';
            }
        }

        $imp = $contrato['ipu_importeTotal'] - $percepciones;
        $ant = $contrato['ipu_importeAnticipo'] - $anticipo;
        $mensaje = '';
        if ($imp == 0 && $ant == 0 && $contrato['eip_id'] != 5) {
            $mensaje = 'El contrato deberia estar Finiquitado';
        }

        if ($imp < 0 && $ant < 0 && $contrato['eip_id'] != 5) {
            $mensaje = 'Los importes de la CLCs han superado los importes del Contrato';
        }

        if ($imp >= 0 && $ant < 0 && $contrato['eip_id'] != 5) {
            $mensaje = 'Los importes de la CLCs de Anticipo han superado el importe del anticipo del Contrato';
        }

        if ($imp > 0 && $ant == 0) {
            $mensaje = 'El contrato no debería aceptar mas CLCs de Anticipo';
        }

        $comparacion = '
        <div style="background-color:#FFF; padding:4px; border-radius:3px; margin-bottom:5px;">
            <h4>Analisis: del Contrato ' . $this->prm->ipu_id . ' ' . $contrato['ipu_numeroContrato'] . '</h4>
            <table class="table table-striped table-bordered">
                <tr>
                    <td class="warning" width="10%"><strong>Rubro</strong></td>
                    <td class="warning"><strong>A = Importes del Contrato</strong></td>
                    <td class="warning"><strong>B = Importes de CLC</strong></td>
                    <td class="warning"><strong><strong>C= A-B</strong></td>
                </tr>
                <tr>
                    <td class="warning" style="color:black;"><strong>Importe</strong></td>
                    <td class="warning" style="color:black;" align="right">
                        $' . number_format($contrato['ipu_importeTotal'], 2) . '
                    </td>
                    <td class="warning" style="color:black;" align="right">
                        $' . number_format($percepciones, 2) . '
                    </td>
                    <td class="warning" style="color:black;" align="right">
                        $' . number_format($imp, 2) . '
                    </td>
                </tr>
                <tr>
                    <td class="warning"><strong>Anticipo</strong></td>
                    <td class="warning" align="right">$' . number_format($contrato['ipu_importeAnticipo'], 2) . '</td>
                    <td class="warning" align="right">$' . number_format($anticipo, 2) . '</td>
                    <td class="warning" align="right">$' . number_format($ant, 2) . '</td>
                </tr>
                <tr>
                    <td colspan="4" style="background-color:#eff28d; color:#9d2449;"><strong>Sugerencia: ' . $mensaje . '</strong>
                    </td>
                </tr>
                </table>
            </div>
            ';

        $this->mixHtml($html . $comparacion);
    }

    private function integrity()
    {
        $html = '';

        $btrevisa = new stdClass();
        $btrevisa->id = 'btrgresa';
        $btrevisa->value = 'Regresar';
        $btrevisa->icon = 'ok';
        $this->agregaTools($this->ponBoton($btrevisa));

        $sql = '
            SELECT gpo_id,ur_id,ur_nombre,COUNT(*)AS Doctos FROM cat_UnidadResponsable
                INNER JOIN  scp_DocumentosUr USING (gpo_id,ur_id)
                GROUP BY gpo_id,ur_id
        ';
        $con = new AccesoDatos();
        $con->setSql($sql);
        $con->conectar();
        $con->consultar();
        $con->desconectar();

        $html .= '<table class="table table-bordered">
                    <tr>
                        <th class="warning">Unidad Responsable</th>
                        <th class="warning">Documentos</th>
                        <th class="warning">Revisar</th>
                    </tr>';
        $cell = 0;
        while ($datos = $con->extraer()) {

            $btrevisa = '<button class="btn btn-xs bt_revisa" g="' . $datos['gpo_id'] . '" u = "' . $datos['ur_id'] . '">Revisar</button>';

            if ($cell == 0) {
                $html .= '<tr id="tr_' . $datos['gpo_id'] . $datos['ur_id'] . '" class ="tr_gral">';
                $cell = 1;
            } else {
                $html .= '<tr id="tr_' . $datos['gpo_id'] . $datos['ur_id'] . '" class ="tr_gral" bgcolor ="#EAEAEA">';
                $cell = 0;
            }
            $html .= '    <td>' . $datos['gpo_id'] . $datos['ur_id'] . ' - ' . $datos['ur_nombre'] . '</td>
                        <td>' . $datos['Doctos'] . '</td>
                        <td>' . $btrevisa . '</td>
                    </tr>
                    <tr id="tra_' . $datos['gpo_id'] . $datos['ur_id'] . '" style="display:none">
                        <td id="td_' . $datos['gpo_id'] . $datos['ur_id'] . '" colspan="3"></td>
                    </tr>
                    ';
        }

        $html .= '</table>';
        $this->mixHtml($html);
        $this->mixScript('
            $("#btrgresa").click(function(){
                comunica("' . $this->pantalla . '");
            });
            $(".bt_revisa").click(function(){
                g = $(this).attr("g");
                u = $(this).attr("u");
                if( $(this).html() =="Revisar" ){
                    $(this).html("Cerrar");

                    $(".tr_gral").css("display","none");
                    $("#td_"+g + u ).parent().css("display","");
                    $("#tr_"+g + u ).css("display","");
                    $("#tra_"+g + u ).css("display","");

                    if ( $("#td_"+g + u ).html() =="" )
                        comunica("' . $this->pantalla . '",{metodo:"revisa",g:g,u:u});

                }else{
                    $(this).html("Revisar");
                    $(".tr_gral").css("display","");
                    $("#tra_"+g + u ).css("display","none");
                }
            });
        ');
    }

    private function fn_revisa()
    {

        $this->setContenedor('td_' . $this->prm->g . $this->prm->u);
        $sql = '
            SELECT * FROM scp_DocumentosUr 
            WHERE gpo_id = ?
            AND ur_id = ?
        ';
        $con = new AccesoDatos();
        $con->setSql($sql);
        $con->conectar();
        $con->consultar([$this->prm->g, $this->prm->u]);
        $html = '
            <table style="width:100%"><tr>
                <th class="success"  style="width:45%">Nombre Original</th>
                <th class="success"  Style="width:20%">Nombre de sistema</th>
                <th class="success"  Style="width:5%">Tipo</th>
                <th class="success"  Style="width:10%">Fecha Subida</th>
                <th class="success"  Style="width:5%;">Ejericio</th>
                <th class="success"  Style="width:10%">Estado</th>
                <th class="success"  Style="width:5%">VER</th>
            </tr>
        ';
        $alto = 0;
        $cell = 0;
        while ($d = $con->extraer()) {

            $fh = explode(' ', $d['usr_fecha']);
            $f = explode('-', $fh[0]);
            $fecha = $f[2] . '/' . $f[1] . '/' . $f[0] . ' ' . $fh[1];

            $alto += 30;
            $ruta = '';
            switch ($d['dur_ejercicio']) {
                case 2015:
                    $ruta = '../../sinpres2015/scp/';
                    break;
                case 2016:
                    $ruta = '';
                    break;
            }
            if ($cell == 0) {
                $html .= '<tr>';
                $cell = 1;
            } else {
                $html .= '<tr bgcolor="EAEAEA">';
                $cell = 0;
            }
            $html .= '
            <td class="table-bordered" ><div class="div_ajustable">' . $d['dur_nombreOriginal'] . '</td>
            <td class="table-bordered" ><div class="div_ajustable">' . $d['dur_nombreGuardado'] . '</td>
            <td class="table-bordered" ><div class="div_ajustable">' . $d['dur_tipo'] . '</td>
            <td class="table-bordered" ><div class="div_ajustable">' . $fecha . '</td>
            <td class="table-bordered" ><div class="div_ajustable">' . $d['dur_ejercicio'] . '</td>

            ';
            if (file_exists($ruta . $d['dur_ruta'])) {
                $html .= '<td class="success" style="display:table-cell"> OK</td>';
                $html .= '<td style="display:table-cell"><a class="link_ctr" dur="' . $d['dur_id'] . '">ver</a></td>';
            } else {
                $html .= '<td class="error" style="display:table-cell"> NO EXISTE</td>';
                $html .= '<td style="display:table-cell"></td>';
            }

            $html .= '</tr>';
        }
        $html .= '</table>';

        $fin = '<div style="max-height:400px; ' . ($alto > 480 ? 'overflow:scroll;' : '') . '">';
        $this->mixHtml($fin . $html . '</div>');
        $this->mixScript('
            $(".link_ctr").click(function(){
                $("body").append("<div class=\"documento_fs\" id=\"fs" + $(this).attr("dur") + "\" ></div>");
                $("#fs"+$(this).attr("dur") ).resizable();
                $("#fs"+$(this).attr("dur") ).draggable();
                comunica("' . $this->pantalla . '",{
                    metodo:"CargaPdf",
                    dur_id:$(this).attr("dur")
                });
            });
            function cerrarPdf(pdf){
                $("#fs"+pdf).remove();
                $("#fs"+pdf).draggable();
            }
        ');
    }
}

if (isset($_SESSION[_S]['prm']['S']['5']['20']['1'])) {
    // permisos del usuario que puede ver este controlador
    new ControladorVistaContrato();
} else {
    WebResponse::sinPermisos();
}
