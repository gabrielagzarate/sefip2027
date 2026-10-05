<?php
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/AuxiliarUnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
class ControladorRelacionBienes extends WebResponse
{
    public $gpo_id;
    public $ur_id;
    public function __construct()
    {
        $this->gpo_id = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->ur_id  = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-ur']  : $_SESSION[_S]['ur_id'];
        //mandar a llamar constructor base
        parent::__construct();
        switch ($this->metodo) {
             case 'validarTrimestre':
                $this->validarTrimestre();
                break;
            default:
                $this->index();
        }
        $this->returnJson();
    }
    public  function index()
    {
        $ur = new AuxiliarUnidadResponsable();
        $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $ur->select();

        $tipoReporte = new stdClass();
        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
                // case 4:
                $tipoReporte->opciones = array(1 => "Solo la UR actual", 2 => "Todas las UR de la Carga de Trabajo");
                break;
            default:
                $tipoReporte->opciones = array(1 => "Solo la UR actual", 2 => "Todas las UR de la Carga de Trabajo", 3 => "Limitar que incluir y que no");
                break;
        }
        $tipoReporte->id = "tipoReporte";
        $tipoReporte->label = "Tipo de Reporte";
        $tipoReporte->selecciona = 1;
        $tipoReporte->width = '150px';

        $grupos = new stdClass();
        $grupos->disabled = true;
        $grupos->id = "grupos";
        $grupos->class = "paso";
        $grupos->value = "1,2,3,7,8";
        $grupos->enDiv = true;
        $grupos->label = "Grupos a incluir";

        $excluir = new stdClass();
        $excluir->disabled = true;
        $excluir->id = "excluir";
        $excluir->enDiv = true;
        $excluir->class = "paso";
        $excluir->label = "URs a excluir";

        $nombreEspecial = new stdClass();
        $nombreEspecial->disabled = true;
        $nombreEspecial->id = "nombreEspecial";
        $nombreEspecial->enDiv = true;
        $nombreEspecial->class = "paso";
        $nombreEspecial->placeholder = "Ejem: SECTOR CENTRAL";
        $nombreEspecial->label = "Encabezado Personalizado";

        $ue = new stdClass();
        $ue->id = "ue";
        $ue->search = true;
        $ue->enDiv = true;
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND ue_id =? ;';
                $ue->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['ue_id']);
                break;
            case 2:
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? ;';
                $ue->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']);
                break;
            case 3:
            case 4:
                $this->conectar();
                $this->setSql('
                    SELECT ue_id,gpo_id FROM cat_CargaTrabajo WHERE
                    gpo_id = ?
                    AND ur_id = ?
                    AND usr_id = ? ;
                ');
                $this->consultar(array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['usr_id']));
                $this->desconectar();
                $tieneUe = $this->extraer();

                if ($tieneUe['ue_id'] != '') {
                    $ue->sql = '
                        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                        INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id,ue_id)
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND c.usr_id = ? ;';
                    $ue->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['usr_id']);
                } else if ($tieneUe['gpo_id'] != '') {
                    $ue->sql = '
                        SELECT u.ue_id,ue_nombre FROM cat_UnidadEjecutora u
                        INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id)
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND c.usr_id = ? ;';
                    $ue->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['usr_id']);
                } else {
                    $ue->sql = '
                        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                        WHERE gpo_id = ?
                        AND ur_id = ? ;';
                    $ue->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']);
                }
                break;
            default:
                $ue->sql = '
                    SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                    WHERE gpo_id = ?
                    AND ur_id = ? ;';
                $ue->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']);
                break;
        }
        $ue->label = "Unidad Ejecutora";

        $ponerCombo = '';
        if ($_SESSION[_S]['nvj_id'] >= 3) {
            $ponerCombo = $this->ponCombo($tipoReporte);
        }

        $this->conectar();
        $this->setSql($ue->sql);
        $this->consultar($ue->params);
        $countUEnum = $this->getNumeroRegistros();
        $this->desconectar();

        $btnPDF = new stdClass();
        $btnPDF->id = "pdf";
        $btnPDF->class = "btnImp btn-outline-warning me-2";
        $btnPDF->value = " PDF";
        $btnPDF->color = "rojo";
        $btnPDF->icon = 'fa-solid fa-file-pdf';
        $btnPDF->type = "submit";

        $btnEXCEL = new stdClass();
        $btnEXCEL->id = "excel";
        $btnEXCEL->class = "btnImp btn-outline-success me-2";
        $btnEXCEL->value = " Excel";
        $btnEXCEL->icon = 'btn fa-solid fa-file-excel';
        $btnEXCEL->type = "submit";

        $radcta = new StdClass();
        $radcta->id = "radcta";
        $radcta->name = "radcta";
        $radcta->values = array('A' => "Bienes Mueble", 'B' => "Bienes Inmueble");
        $radcta->cols = 2;
        $radcta->height = '20px';
        $radcta->align = 'left';
        $radcta->selecciona = 'C';

        $trimestre = new stdClass();
        $trimestre->opciones = array("" => "Seleccione el periodo", 1 => "Enero - Marzo", 2 => "Enero - Junio", 3 => "Enero - Septiembre", 4 => "Enero - Diciembre", 5 => "Cuenta Pública");
        $trimestre->id = "trimestre";
        $trimestre->label = "Periodo";
        $trimestre->width = '150px';

        $html = '
        <form id="formBienes" method="post" target="_blank">
            <input type="hidden" name="sis_id" id="sis_id" value="C">
            <input type="hidden" name="men_id" id="men_id" value="10">
            <input type="hidden" name="opc_id" id="opc_id" value="13">
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <div class="col-md-12 col-xs-12">
                        <!-- Nav tabs -->
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col col-md-6 ">
                                    <div class="row col-md-12 col-xs-12 mb-4">
                                        <fieldset class="col-md-6 col-xs-6">                                             
                                            ' . $ponerCombo . '
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">
                                            <input type="hidden" id="numUe" name="numUe"  value=' . $countUEnum . '>
                                                ' . $this->ponCombo($ue) . '
                                        </fieldset>
                                    </div>
                                </div>
                                ' . (($_SESSION[_S]['nvj_id'] >= 5) ?
                                    '<div class="row">
                                        <fieldset class="col-md-3 col-xs-3">
                                            ' . $this->ponInput($grupos) . '
                                        </fieldset>
                                        <fieldset class="col-md-3 col-xs-3">
                                            ' . $this->ponInput($excluir) . '
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">
                                            ' . $this->ponInput($nombreEspecial) . '
                                        </fieldset>
                                    </div>'
                                : '') . '
                                <div class="col col-md-6 col-xs-6">
                                    <div class="row col-md-12 col-xs-12 mb-4">
                                        <fieldset class="col-md-6 col-xs-6">
                                            '.$this->ponCombo($trimestre).'
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">            
                                           <label>Tipo de Importe</label><br/>
                                            <input type="checkbox" id="ckmiles" name="ckmiles" /> Importe en miles de pesos
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="col-md-12 col-xs-12">
                                            <legend>Consultar por:</legend>
                                            ' . $this->ponRadio($radcta) . '
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        
                </fieldset>
            </div>
        </form>
    ';

        $html = str_replace(' />', ' />&nbsp;&nbsp;', $html);

        $this->mixHtml($html);
        $this->agregaTools($this->ponBoton($btnPDF) . ' ' . $this->ponBoton($btnEXCEL));
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '",{metodo:"index"});
            ')
        );
        $this->mixScript('

        $("#trimestre").on("change", function() {
            comunica("' . $this->pantalla . '", {metodo: "validarTrimestre",id:this.value,ciclo:"si"});
        });
        

        $(".btnImp").click(function(){
        
            if($("input[name=radcta]:checked").val() == undefined){
                notify("Debe seleccionar el tipo de bien", "error");
                    return false;
            }

            if($("#trimestre option:selected").val() == ""){
                notify("Debe seleccionar el periodo", "error");
                    return false;
            }
        
            switch(this.id){
                case "pdf":
                    $("#formBienes").attr("action","rep/doc/scg/reporte/reportebienes.php");
                break;
                case "excel":
                    $("#formBienes").attr("action","rep/excel/scg/reporte/exp_RelacionBienes.php");
                break;
            }
            
            if($("#trimestre option:selected") != ""){
                $("#formBienes").submit();
            }
        });

        $("#tipoReporte").change(function(){
            var nOpcion = $(this).val();
            switch(nOpcion){
                case "1":
                    $(".paso").attr("disabled", "disabled");
                    $("#ue").removeAttr("disabled");
                    break;
                case "2":
                    $("#ue").attr("disabled",true);
                    $(".paso").attr("disabled", "disabled");
                    break;
                case "3":
                    $(".paso").removeAttr("disabled");
                    $("#ue").attr("disabled",true);
                    break;
                }
        });
    ');
    }
    public  function validarTrimestre()
    {
        switch($this->prm->id){
            case 1:
                $corte = "03";
                $concepto = "Enero - Marzo : El Periodo seleccionado no cuenta con información";
                break;
            case 2:
                $corte = "06";
                $concepto = "Enero - Junio : El Periodo seleccionado no cuenta con información";
                break;
            case 3:
                $corte = "09";
                $concepto = "Enero - Septiembre : El Periodo seleccionado no cuenta con información";
                break;
            case 4:
                $corte = "12";
                $concepto = "Enero - Diciembre : El Periodo seleccionado no cuenta con información";
                break;
            case 5:
                $corte = "12";
                $concepto = " La Cuenta Pública ".APP_EJERCICIO." : No puede ser generada por falta de información.";
                break;
            default:
                $corte = "";
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql( " 
            SELECT ems_id
            FROM cg_MesContable
            WHERE gpo_id = ? AND ur_id = ?
                AND msc_id = ?
        ");
        $parametros= [$this->gpo_id,$this->ur_id,$corte];
        $conexion->consultar($parametros);
        $conexion->desconectar();
        if($conexion->getNumeroRegistros() == 0){
            switch($this->prm->ciclo){ 
                case "si":
                    $this->mixScript('
                        notify("'.$concepto.'", "error");
                        comunica("' . $this->pantalla . '",{metodo:"index"});
                    ');
                break;
            }
        }
    }
}
new ControladorRelacionBienes();