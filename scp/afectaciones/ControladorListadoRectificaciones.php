<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
class ControladorListadoRectificaciones extends WebResponse
{
    protected $ur;
    protected $usrNvj;
    protected $usrDep;
    protected $usrId;
    protected $usrIp;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "scp/afectaciones/ControladorListadoRectificaciones.php";
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->usrNvj = $_SESSION[_S]['nvj_id'];
        $this->usrDep = $_SESSION[_S]['dep_id'];
        $this->usrId = $_SESSION[_S]['usr_id'];
        $this->usrIp = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';;

        switch ($this->usrNvj) {
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                switch ($this->metodo) {
                    case 'GeneraListadoRectificaciones':
                        $this->GeneraListadoRectificaciones();
                        break;
                    case 'TablaListadoGenerados':
                        $this->TablaListadoGenerados();
                        break;
                    case 'llenaTablaListadoGenerados':
                        $this->llenaTablaListadoGenerados();
                        break;
                    case 'PantallaInicial':
                        $this->PantallaInicial();
                        break;
                    case 'llenaTablaRct':
                        $this->llenaTablaRct();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools('');

        $cardConsulta = new stdClass();
        $cardConsulta->id = 'consulta';
        $cardConsulta->titulo = 'Consulta de Rectificaciones';
        $cardConsulta->img = 'img/img.php?img=clc_generacion';
        $cardConsulta->label = 'CONSULTA';
        $cardConsulta->title = 'Consulta de Rectificaciones';

        $cardGeneracion = new stdClass();
        $cardGeneracion->id = 'generacion';
        $cardGeneracion->titulo = 'Listado de Rectificaciones';
        $cardGeneracion->img = 'img/img.php?img=clc_generacion';
        $cardGeneracion->label = 'LISTADO';
        $cardGeneracion->title = 'Listado de Rectificaciones';

        $this->mixHtml($this->ponCards([$cardConsulta, $cardGeneracion]));
        $this->mixScript('
            $("#consulta").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo: "TablaListadoGenerados",
                    tareaclc: "consulta"
                });
                return false;
            });
        
            $("#generacion").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo: "PantallaInicial",
                    tareaclc: "generacion"
                });
                return false;
            });
        ');
    }

    public function PantallaInicial()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresarIndex';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';
        $btnregresarindex->click = '
            comunica("'.$this->pantalla.'",{
                metodo : "main"
            });
        ';
        $btnregresarindex = $this->ponBoton($btnregresarindex);
        $this->agregaTools($btnregresarindex);

        $script = '';
        $tipoRct = new StdClass();
        $tipoRct->class = "radio habilitar me-2";
        $tipoRct->titulo = 'TIPO DE LISTA A GENERAR';
        $tipoRct->name = "tipoRct";
        $tipoRct->values = array(1 => "REINTEGRO", 2 => "SIN REINTEGRO");
        $tipoRct->cols = 3;
        $tipoRct->required = true;
        $this->ponRadio($tipoRct);

        $comboMes = new stdClass();
        $params = [(date(" m") - 1)];
        $comboMes->sql = "
            SELECT
                msc_id,
                msc_nombre
            FROM
                ".BD_CTRL.".cat_MesContable
            WHERE
                msc_id >= ?
                AND msc_id <= 12";

        $comboMes->params = $params;
        $comboMes->id = "comboMes";
        $comboMes->label = "Mes :";
        $comboMes->comoAtr = 'msc_nombre';
        $comboMes->selecciona = date("m");

        $ur_id = new stdClass();
        $ur_id->id = 'ur_id';
        $ur_id->label = 'Numero de UR';
        $ur_id->enDiv = true;
        $ur_id->maxlength = 6;
        $this->ponInput($ur_id);

        $generar = new stdClass();
        $generar->value = 'Excel';
        $generar->id = 'generar';
        $generar->class = 'btn btn-outline-success me-1';
        $generar->name = 'generar';
        $generar->icon = 'solid fa-file-excel me-2';

        $busqueda = '';
        $bandera = false;

        if ($this->usrDep == 48 || $this->usrDep == 49 || $this->usrDep == 50) {
            $bandera = true;
            $busqueda = '
                <div class="col-md-2">
                    ' . $this->ponInput($ur_id) . '
                </div>
            ';
        }

        $radios = '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
                <div class="row col-md-12 col-xs-12">
                    <div class="col-md-4 col-xs-4" mb-4>
                        ' . $this->ponRadio($tipoRct) . '
                    </div>
                    <div class="col-md-3 col-xs-3">
                        ' . $this->ponCombo($comboMes) . '
                    </div>
                    ' . $busqueda . '
                    <div class="col-md-3 col-xs-3">
                        <div class="mt-3">
                        ' . $this->ponBoton($generar) . '
                        </div>
                    </div>
                </div>
            </form>
        ';

        $scriptFecha = $this->getScript();
        $tabla = new stdClass();
        $tabla->id = 'tbl-rct';
        $tabla->titulo = 'Listado de Rectificaciones';
        $tabla->class = 'mb-4';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaRct';
        $tabla->comunica->fechacreacion = '{$("#comboMes").val()}';

        if ($bandera == true) {
            $tabla->comunica->ur_id = '{$("#ur_id").val()}';
        }

        $tabla->comunica->tipoRct = '{$("input[name=tipoRct]:checked").val()}';
        $tabla->numFilas = 5;
        $tabla->filasAmostrar = array(5, 10, 15, 20, 50);

        $tr = array();
        //UR
        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        //NUMERO DE CLC
        $th = new stdClass();
        $th->texto = 'RECTIFICACIÓN';
        array_push($tr, $th);

        //NUMERO DE CLC
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        //CONCEPTO DE LA CLC
        $th = new stdClass();
        $th->texto = 'JUSTIFICACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        //FECHA
        $th = new stdClass();
        $th->texto = 'FECHA APLICACIÓN.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $trbody = array();

        /* UR*/
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = array('gpo_id', 'ur_id');
        array_push($trbody, $td);

        //numero de clc
        $td = new stdClass();
        $td->campoTxt = 'rct_id';
        array_push($trbody, $td);

        //numero de clc
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        //concepto
        $td = new stdClass();
        $td->campoTxt = 'rct_justificacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rct_fechaAutorizacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rct_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        //para exportar el pdf
        $validarur = $envio = '';

        if ($bandera == true) {
            $envio = 'obj.ur_id=$("#ur_id").val();';
            $validarur = '
                if($("#ur_id").val()==""){
                    notify("CAPTURA LA UR","warning");
                    return false;
                }
            ';
        }

        $script = $scriptFecha;
        $script .= '
            $("#comboMes").prop("disabled", false);
            $("#comboMes").attr("disabled", false);
            $("input[name=tipoRct]").click(function(){
                if($("#fechacreacion").val()==""){
                    alert("Elige la fecha de generación");
                    return false;
                }
                ' . $validarur . '
                ' . $this->wrLlamaLlenaTabla('tbl-rct') . '
                $("#ur_id").blur
            });

            $("#ur_id").blur(function(){
                if( $("#ur_id").val().length < 3 ){
                    notify("Debes  escribir la UR con 3 digitos","warning");
                }else if($("#fechacreacion").val()==""){
                    alert("Selecciona la fecha");
                }else if(! $("input[name=tipoRct]").is(":checked")){
                    notify("Selecciona el tipo de listado","warning");
                }else{
                    ' . $this->wrLlamaLlenaTabla('tbl-rct') . '
                }
                return false;
            });
        ';

        $script .= '
            $("#generar").click(function(){
                if($("input[name=tipoRct]:checked").val()==""){
                    alert("Elige el tipo de Lista a Generar");
                    return false;
                }
                ' . $validarur . '
                var obj = {"metodo":"GeneraListadoRectificaciones"};
                obj.tipoRct=$("input[name=tipoRct]:checked").val();
                obj.fechacreacion=$("#comboMes").val();
                ' . $envio . '
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
                
            });
        ';
        $this->mixScript($script);
    }

    private function llenaTablaRct()
    {
        $wf = '';
        $params = [];
        if (isset($this->prm->tipoRct) && $this->prm->tipoRct != '') {
            switch ($this->prm->tipoRct) {
                case 1:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro = 1' : ' AND  rct_reintegro = 1 ';
                    break;
                case 2:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro IN (2,3)' : ' AND  rct_reintegro IN (2,3) ';
                    break;
            }
            if (isset($this->prm->fechacreacion) && $this->prm->fechacreacion <> "") {
                array_push($params, $this->prm->fechacreacion);
                $wf .= ' AND  month(rct_fechaAutorizacion) = ?  ';
            }
            if (isset($this->prm->ur_id) && $this->prm->ur_id <> "") {
                array_push($params, $this->prm->ur_id);
                $wf .= ' AND  concat(gpo_id,ur_id) = (?) ';
            }
        }

        $this->conectar();
        array_push($params, $this->usrDep);
        $sql = '
            SELECT gpo_id,
                ur_id,
                clc_id,
                tcl_id,
                tcl_nombre,
                rct_id,
                rct_justificacion,
                rct_reintegro,
                DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
                a.ecl_id,
                ecl_nombre,
                rct_importe
            FROM '.BD_CTRL.'.scp_MaestroRectificacion a
            LEFT JOIN '.BD_CTRL.'.scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
            INNER JOIN '.BD_CTRL.'.scp_Clc b USING(gpo_id,ur_id,clc_id)
            INNER JOIN '.BD_CTRL.'.enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
            INNER JOIN enc_ClaveFinanciamiento using (ecf_id)
            INNER JOIN '. BD_SINPRES .'.cat_ClaveFinanciamiento'. APP_EJERCICIO .' using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN '.BD_CTRL.'.cat_TipoClc using (tcl_id)
            INNER JOIN cat_EstadoClc e  on  a.ecl_id = e.ecl_id
            ' . $wf . '  
                AND dep_id = ?  
                AND (a.ecl_id = 6 AND b.ecl_id = 18 AND b.clc_recursoFederal = 2)
                AND lrct.lrct_dia is null 
                AND lrct.lrct_mes is null 
                AND lrct.lrct_anio is null 
            GROUP BY gpo_id,ur_id,rct_id,clc_id
        ';

        $tempPertence = "f";
        $tempDiferente = "f";
        if ($this->usrDep == 48 || $this->usrDep == 49 || $this->usrDep == 50) {
            $tempPertence = "v";

            if (isset($this->prm->ur_id) && $this->prm->ur_id != "") {
                switch ($this->usrDep) {
                    case 48:
                        if (!(($this->prm->ur_id == 530) || ($this->prm->ur_id == 531))) {
                            $tempDiferente = "v";
                        }
                        break;
                    case 49:
                        if (!(($this->prm->ur_id == 540) || ($this->prm->ur_id == 553))) {
                            $tempDiferente = "v";
                        }
                        break;
                    case 50:
                        if (!(($this->prm->ur_id == 540) || ($this->prm->ur_id == 553)
                            || ($this->prm->ur_id == 530) || ($this->prm->ur_id == 531))) {
                            $tempDiferente = "v";
                        }
                        break;
                }
            }
        }

        if (($tempPertence == "v" && $tempDiferente == "f") || ($tempPertence == "f")) {
            $this->setSql($sql);
            $this->setParams($params);
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
            $nav->nf = isset($this->prm->nav->nf)  ? $this->prm->nav->nf : 5;
            $nav->idTabla = 'tbl-rct';

            if ($this->wrLlenaTabla($nav) == 0) {
                $this->mixScript('
                    notify("NO HAY REGISTROS","warning");
                ');
            }
        } else {
            $this->mixScript('
                notify("No tiene acceso a la información de esta UR","error");
            ');
        }

        $this->desconectar();
        $this->mixScript('
            $("#btnregresarindex").click(function(){
                document.location.href = "../sefip'. APP_EJERCICIO .'";
            });
        ');
    }

    public function GeneraListadoRectificaciones()
    {
        $mesgeneracion  = $d = $m = $mes = $dia = $fecha = $wf  = '';
        $listado = $numListado = '';
        $numero = 0;

        //comienza la transaccion
        $this->conectar();
        $this->setSql('
            START TRANSACTION
        ');

        $this->consultar();

        if (isset($this->prm->fechacreacion) && $this->prm->fechacreacion < date("m")) {
            $this->prm->fechacreacion = date('d-m-Y', strtotime('last day of previous month'));

        } else {
            $this->prm->fechacreacion = date("d-m-Y");
        }
        if (isset($this->prm->tipoRct) && $this->prm->tipoRct != '') {
            switch ($this->prm->tipoRct) {
                case 1:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro = 1' : ' AND  rct_reintegro = 1 ';
                    $listado = ' "CR" ';

                    $this->setSql('
                        SELECT 
                            if(max(lrct_listado_id)is null,
                                1,
                                max(lrct_listado_id)+1) AS lrct_listado_id
                        FROM  '.BD_CTRL.'.scp_MaestroRectificacion 
                            INNER JOIN '.BD_CTRL.'.scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
                        WHERE lrct.lrct_reintegro = "CR" ;
                    ');
                    $this->consultar();
                    $row = $this->extraer();
                    $numListado = $row['lrct_listado_id'];
                    break;
                case 2:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro IN (2,3)' : ' AND  rct_reintegro IN (2,3) ';
                    $listado = ' "SR" ';
                    $this->setSql('
                        SELECT 
                            if(max(lrct_listado_id)is null, 1 ,max(lrct_listado_id)+1) AS lrct_listado_id
                        FROM  '.BD_CTRL.'.scp_MaestroRectificacion
                            INNER JOIN '.BD_CTRL.'.scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
                        WHERE lrct_reintegro = "SR" ;
                    ');
                    $this->consultar();
                    $row = $this->extraer();
                    $numListado = $row['lrct_listado_id'];
                    break;
            }

            if (isset($this->prm->fechacreacion) && $this->prm->fechacreacion != "") {
                $fecha .= ' "' . $this->date_to_mysqldate($this->prm->fechacreacion) . '" ';
                $dia .= ' "' . (substr($this->prm->fechacreacion, 0, 2)) . '"  ';
                $mes .= ' "' . (substr($this->prm->fechacreacion, 3, 2)) . '"  ';
                $mesgeneracion = '"' . (substr($this->prm->fechacreacion, 3, 2)) . '"  ';

                $d = '' . (substr($this->prm->fechacreacion, 0, 2)) . '';
                $m = '' . (substr($this->prm->fechacreacion, 3, 2)) . '';
                $y = 20;
            }
        }

        $porUrs = '';
        $tempPertence = "f";
        $tempDiferente = "f";

        if ($this->usrDep == 48 || $this->usrDep == 49 || $this->usrDep == 50) {
            if (isset($this->prm->gpo_id) && $this->prm->gpo_id != "" && isset($this->prm->ur_id) && $this->prm->ur_id != "") {
                $porUrs = ' AND concat(gpo_id,ur_id) = ('. $this->prm->ur_id .') ';
                $tempPertence = "v";
                switch ($this->usrDep) {
                    case 48:
                        if (!(($this->prm->ur_id == 530) || ($this->prm->ur_id == 531))) {
                            $tempDiferente = "v";
                        }
                        break;
                    case 49:
                        if (!(($this->prm->ur_id == 540) || ($this->prm->ur_id == 553))) {
                            $tempDiferente = "v";
                        }
                        break;
                    case 50:
                        if (!(($this->prm->ur_id == 540) || ($this->prm->ur_id == 553)
                            || ($this->prm->ur_id == 530) || ($this->prm->ur_id == 531))) {
                            $tempDiferente = "v";
                        }
                        break;
                }
            }
        }

        //se agrego la variable numero para enviar el numero de lote que se va a genera
        $numero = str_pad($numListado, 3, "0", STR_PAD_LEFT);
        $this->conectar();

        $p = [$this->usrIp, $this->usrId];
        $this->setSql('
            SET @bit_observacion = "REGISTRO DESDE LA PANTALLA S_2_16_55", 
                @usr_ip = inet_aton(?), 
                @usr_id = ?;
        ');

        $this->consultar($p);

        $this->setSql('
            INSERT INTO '.BD_CTRL.'.scp_ListadoRectificacion 
                (gpo_id,ur_id,rct_id,lrct_dia, lrct_mes, lrct_anio, lrct_reintegro, lrct_fechaListado, lrct_listado_id,usr_id,usr_fecha)
            SELECT 
                a.gpo_id, a.ur_id, a.rct_id,' . $dia . ' AS lrct_dia, ' . $mes . ' AS lrct_mes, "25" AS lrct_anio, 
                ' . $listado . ' AS lrct_reintegro, ' . $fecha . ' AS lrct_fechaListado, ' . $numListado . ' AS lrct_listado_id, 
                ' . $this->usrId . ' AS usr_id, NOW() AS usr_fecha
            FROM '.BD_CTRL.'.scp_MaestroRectificacion a
            LEFT JOIN (
                    SELECT gpo_id as gpo , ur_id as ur, rct_id as rct
                    FROM '.BD_CTRL.'.scp_ListadoRectificacion
                    ) lrct ON lrct.gpo = a.gpo_id AND lrct.ur = a.ur_id AND lrct.rct = a.rct_id
            INNER JOIN '.BD_CTRL.'.scp_Clc c USING(gpo_id,ur_id,clc_id) 
            INNER JOIN '.BD_CTRL.'.enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id) 
            INNER JOIN '.BD_CTRL.'.cat_TipoClc using (tcl_id) 
            INNER JOIN cat_EstadoClc e  on  a.ecl_id = e.ecl_id
            ' . $wf . '
                AND dep_id = '. $this->usrDep .'
                AND (a.ecl_id = 6 AND c.ecl_id = 18 AND clc_recursoFederal = 2)
                AND month(rct_fechaAutorizacion) = '. $mesgeneracion .'
                AND lrct.rct IS NULL
                ' . $porUrs . ';
            ');

        if (($tempPertence == "v" && $tempDiferente == "f") || ($tempPertence == "f")) {
            $this->consultar();
            $this->setSql('
                COMMIT
            ');
            $this->consultar();
        }

        $this->desconectar();

        if (($tempPertence == "v" && $tempDiferente == "f") || ($tempPertence == "f")) {
            $this->mixScript('
                notify("La lista de rectificación ha sido generada correctamente");
                form = $("<form>")	.attr("action","rep/doc/scp/afectaciones/RepListadoRectificaciones.php")
                    .attr("method","post")
                    .attr( "target","_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "tipoRct").val( "' . $this->prm->tipoRct . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "listado").val( "' . $numero . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "meses").val( "' . $m . '" ));
                $("body").append(form);
                form.submit();
                form.remove();
                comunica("' . $this->pantalla . '", {
                    metodo:"llenaTablaRct",
                    tipoRct: "' . $this->prm->tipoRct . '",
                    fechacreacion: "' . $this->prm->fechacreacion . '"
                });
            ');
        } else {
            $this->mixScript('
                notify("No se puede generar el listado","error");
            ');
        }
    }

    public function TablaListadoGenerados()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresarIndex';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';
        $btnregresarindex->click = '
            comunica("'.$this->pantalla.'",{
                metodo : "main"
            });
        ';
        $btnregresarindex = $this->ponBoton($btnregresarindex);
        $this->agregaTools($btnregresarindex);

        $tipoRct = new StdClass();
        $tipoRct->class = "me-2";
        $tipoRct->titulo = 'TIPO DE LOTE A CONSULTAR';
        $tipoRct->name = "tipoRct";
        $tipoRct->values = array(1 => "REINTEGRO", 2 => "SIN REINTEGRO");
        $tipoRct->cols = 3;;
        $tipoRct->required = true;
        $this->ponRadio($tipoRct);

        $meses = new stdClass();
        $meses->id = 'meses';
        $meses->name = 'meses';
        $meses->width = "400px";
        $meses->required = true;
        $meses->sql = 'SELECT msc_id,msc_nombre FROM '.BD_CTRL.'.cat_MesContable WHERE msc_id BETWEEN 1 AND 12';
        //$meses->label = 'Seleccione el mes del Lote';
        $meses->enRow = 1;

        $ur_id        = new stdClass();
        $ur_id->id    = 'ur_id';
        $ur_id->label = 'Numero de UR';
        $ur_id->class = 'mt-3';
        $ur_id->enDiv    = true;
        $ur_id->maxlength = 6;
        $this->ponInput($ur_id);

        if ($this->usrDep != 48 && $this->usrDep != 49 && $this->usrDep != 50) {
            $reimprimir = new stdClass();
            $reimprimir->value = 'PDF';
            $reimprimir->id = 'reimprimir';
            $reimprimir->color = 'rojo';
            $reimprimir->class = 'me-1';
            $reimprimir->name = 'reimprimir';
            $reimprimir->icon = 'solid fa-file-pdf me-2';
            $reimprimir = $this->ponBoton($reimprimir);

            $exportar = new stdClass();
            $exportar->value = 'Excel';
            $exportar->id = 'exportar';
            $exportar->color = 'verde';
            $exportar->class = 'me-1';
            $exportar->name = 'exportar';
            $exportar->icon = 'solid fa-file-excel me-2';
            $exportar = $this->ponBoton($exportar);
        } else {
            $reimprimir = '';
            $exportar = '';
        }

        $buscar = new stdClass();
        $buscar->value = 'Buscar';
        $buscar->id = 'buscar';
        $buscar->name = 'buscar';
        $buscar->color = 'azul';
        $buscar->class = 'me-1';
        $buscar->icon = 'search me-2';

        $busqueda2 = '';
        $paso = false;
        if ($this->usrDep == 48 || $this->usrDep == 49 || $this->usrDep == 50) {
            $paso = true;
            $busqueda2 = '
                <div class="col-md-1 col-xs-1">
                    <div class="mb-3">
                    </div>
                    ' . $this->ponInput($ur_id) . '
                </div>';
        }

        $radios = '';
        $radios .= '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
                <div class="row">
                    <div class="col-md-4 col-xs-4 mb-4">
                        <div class = "mb-4">
                            <h4>Tipo de Lista a consultar</h4>
                        </div> 
                            ' . $this->ponRadio($tipoRct) . '
                    </div>
                    <div class="col-md-3 col-xs-3">
                        <div class="mb-5">
                            <h4>Mes de la lista</h4>
                         </div>
                        ' . $this->ponCombo($meses) . '
                    </div>
                    ' . $busqueda2 . '
                    <div class="col-md-4 col-xs-4">
                        <div class="mb-5 p-2">
                            <h4></h4>
                        </div>
                    ' . $this->ponBoton($buscar) . ' ' . $reimprimir . ' ' . $exportar . '
                    </div>
                </div>
            </form>
        ';

        $tabla = new stdClass();
        $tabla->id = 'tbl-lote';
        $tabla->titulo = 'Listado de  Rectificaciones';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaRct';
        $tabla->comunica->tipoRct = '{$("input[name=tipoRct]:checked").val()}';
        if ($paso == true) {
            $tabla->comunica->ur_id = '{$("#ur_id").val()}';
        }
        $tabla->comunica->meses = '{$("#meses").val()}';

        /*Esta opcion es para el grupo del Contador jacinto */
        if ($this->usrDep != 48 && $this->usrDep != 49 && $this->usrDep != 50) {
            $tr = array();

            $th = new stdClass();
            $th->texto = 'UR';
            array_push($tr, $th);

            //NUMERO DE CLC
            $th = new stdClass();
            $th->texto = 'RECTIFICACIÓN';
            array_push($tr, $th);

            //NUMERO DE CLC
            $th = new stdClass();
            $th->texto = 'CLC';
            array_push($tr, $th);

            //CONCEPTO DE LA CLC
            $th = new stdClass();
            $th->texto = 'JUSTIFICACIÓN';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'TIPO';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'ESTADO';
            array_push($tr, $th);

            //FECHA
            $th = new stdClass();
            $th->texto = 'FECHA APLICACIÓN.';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'IMPORTE';
            array_push($tr, $th);

            $trbody = array();

            /* UR*/
            $td = new stdClass();
            $td->campoTxt = new stdClass();
            $td->campoTxt->campos = array('gpo_id', 'ur_id');
            array_push($trbody, $td);

            //numero de clc
            $td = new stdClass();
            $td->campoTxt = 'rct_id';
            array_push($trbody, $td);

            //numero de clc
            $td = new stdClass();
            $td->campoTxt = 'clc_id';
            array_push($trbody, $td);

            //concepto
            $td = new stdClass();
            $td->campoTxt = 'rct_justificacion';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'tcl_nombre';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'ecl_nombre';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'rct_fechaAutorizacion';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'rct_importe';
            $td->formato = 'moneda';
            array_push($trbody, $td);
        } else {
            /* PARA EL GRUPO DE LAS PRINCESAS */
            $tr = array();

            $th = new stdClass();
            $th->texto = 'LISTADO';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'IMPRIMIR';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'EXPORTAR';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'FECHA APLICACIÓN.';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'IMPORTE';
            array_push($tr, $th);

            $trbody = array();

            $td = new stdClass();
            $td->campoTxt = 'listado';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'print me-2';
            $td->color = "azul";
            $td->class = 'me-1 impresion';
            $td->attr = new stdClass();
            $td->attr->lrct_listado_id = 'lrct_listado_id';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'print me-2';
            $td->color = "verde";
            $td->class = 'me-1 exportar';
            $td->attr = new stdClass();
            $td->attr->lrct_listado_id = 'lrct_listado_id';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'rct_fechaAutorizacion';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'importe';
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->recargaCombo($meses);

        $validarur = $envio = '';
        if ($paso == true) {
            $envio = 'obj.ur_id=$("#ur_id").val();';
            $validarur = ' if($("#ur_id").val()==""){
                notify("CAPTURA LA UR","warning");
                return false;
            }';
        }

        $this->mixScript('
            $("#buscar").click(function(){
                ' . $validarur . '
                var obj = {"metodo":"llenaTablaListadoGenerados"};
                obj.tipoRct = $("input[name=tipoRct]:checked").val();
                obj.meses = $("#meses").val();
                ' . $envio . '
                console.log(obj);
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });
        ');
    }

    private function llenaTablaListadoGenerados()
    {
        $wf = '';
        if (isset($this->prm->tipoRct) && $this->prm->tipoRct != '') {
            switch ($this->prm->tipoRct) {
                case 1:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro = 1' : ' AND  rct_reintegro = 1 ';
                    break;
                case 2:
                    $wf .= ($wf == '') ? 'WHERE rct_reintegro IN (2,3)' : ' AND  rct_reintegro IN (2,3) ';
                    break;
            }
        }
        $params = [];
        if (isset($this->prm->ur_id) && $this->prm->ur_id != "") {
            $wf .= ' AND  CONCAT(gpo_id,ur_id) = (?) ';
            array_push($params, $this->prm->ur_id);
        }

        $this->conectar();

        array_push($params, $this->usrDep, $this->prm->meses);

        if (isset($this->prm->ur_id) && $this->prm->ur_id != "") {
            $sql = '
                SELECT 
                    CONCAT(lrct_dia,lrct_mes,lrct_anio,lrct_reintegro,lrct_listado_id) AS listado,
                    MAX(rct_reintegro) AS rct_reintegro,
                    DATE_FORMAT(MAX(rct_fechaAutorizacion),"%d/%m/%Y") AS rct_fechaAutorizacion,
                    sum(rct_importe) AS importe,
                    lrct_listado_id
                FROM '.BD_CTRL.'.scp_MaestroRectificacion a 
                    INNER JOIN '.BD_CTRL.'.scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
                    INNER JOIN '.BD_CTRL.'.scp_Clc b USING(gpo_id,ur_id,clc_id) 
                    INNER JOIN '.BD_CTRL.'.enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id) 
                    INNER JOIN '.BD_CTRL.'.cat_TipoClc using (tcl_id) 
                    INNER JOIN cat_EstadoClc e on a.ecl_id = e.ecl_id 
                ' . $wf . '  
                    AND dep_id = ? 
                    AND (a.ecl_id = 6 AND b.ecl_id = 18 AND clc_recursoFederal = 2)
                    AND lrct_mes = ? 
                GROUP BY lrct_dia,lrct_mes,lrct_anio,lrct_reintegro,lrct_listado_id;
            ';
        } else {
            $sql = '
                SELECT 
                    gpo_id,
                    ur_id,
                    clc_id,
                    tcl_id,
                    tcl_nombre,
                    rct_id,
                    rct_justificacion,
                    rct_reintegro,
                    DATE_FORMAT(rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
                    ecl_nombre,
                    rct_importe,
                    lrct_listado_id
                FROM '.BD_CTRL.'.scp_MaestroRectificacion a
                    INNER JOIN '.BD_CTRL.'.scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
                    INNER JOIN '.BD_CTRL.'.scp_Clc b  USING(gpo_id,ur_id,clc_id)
                    INNER JOIN '.BD_CTRL.'.enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
                    INNER JOIN '.BD_CTRL.'.cat_TipoClc using (tcl_id)
                    INNER JOIN cat_EstadoClc e  on  a.ecl_id = e.ecl_id
                ' . $wf . ' 
                    AND dep_id = ? 
                    AND (a.ecl_id = 6 AND b.ecl_id = 18 AND clc_recursoFederal = 2)
                    AND lrct.lrct_mes = ?  
                GROUP BY gpo_id,ur_id,rct_id,clc_id
            ';
        }

        $this->setParams($params);
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-lote';
        $nav->nf = 100;

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript("alertify.alert('', 'NO HAY REGISTROS',
                function(){
                // Agregar codigo en caso de ser necesario si no omitir la funcion, ya que solo es una alerta
                }
                ).set('labels', {ok:'Aceptar', cancel:'Cancelar'});
                const btnAceptar = document.querySelector('.ajs-button.btn.btn-primary');
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove('btn-primary');
                                        btnAceptar.classList.add('btn-success');
                                    }

            ");
        }

        $this->mixScript('
            $("#reimprimir,.impresion").unbind( "click" );
            $("#reimprimir,.impresion").click(function(){
                var form = $("<form>")
                .attr("action", "rep/doc/scp/afectaciones/RepListadoRectificaciones.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "tipoRct").val( "' . $this->prm->tipoRct . '"))
                ' . ((isset($this->prm->ur_id) && $this->prm->ur_id != '') ? '.append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( "' . $this->prm->ur_id . '"))'
            : '') . '
                .append($("<input>").attr("type", "hidden").attr("name", "meses").val("' . $this->prm->meses . '" ));
                if( $(this).is(".impresion"))
                form.append($("<input>").attr("type", "hidden").attr("name", "listado").val( $(this).attr("lrct_listado_id") ));
                $("body").append(form);
                form.submit();
                form.remove();
            });
            $("#exportar,.exportar").unbind( "click" );
            $("#exportar,.exportar").click(function(){
                var form = $("<form>")
                .attr("action", "rep/excel/scp/afectaciones/RepListadoRectificaciones.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "tipoRct").val( "' . $this->prm->tipoRct . '"))
                ' . ((isset($this->prm->ur_id) && $this->prm->ur_id != '') ? '.append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( "' . $this->prm->ur_id . '"))'
            : '') . '
                .append($("<input>").attr("type", "hidden").attr("name", "meses").val("' . $this->prm->meses . '" ));
                if( $(this).is(".exportar"))
                    form.append($("<input>").attr("type", "hidden").attr("name", "listado").val( $(this).attr("lrct_listado_id") ));
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }
}
if (isset($_SESSION[_S]['prm']['S']['2']['16']['55'])) {
    new ControladorListadoRectificaciones();
} else {
    WebResponse::sinPermisos();
}