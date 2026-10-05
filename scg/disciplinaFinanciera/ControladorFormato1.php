<?php

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/AuxiliarUnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorFomarto1 extends WebResponse
{
    private $lectura;
    private $escritura;
    private $gpoId;
    private $urId;

    public function __construct()
    {
        parent::__construct();
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, $_POST);
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, $_POST);

        $this->gpoId = $_SESSION[_S]['gpo_id'];
        $this->urId = $_SESSION[_S]['ur_id'];

        $metodos = [
            'generarPdf' => 'generarPdf',
            'generarExcel' => 'generarExcel',
            'validarTrimestre' => 'validarTrimestre'
        ];

        $metodo = $metodos[$this->metodo] ?? 'main';
        $this->$metodo();

        $this->returnJson();
    }

    public function main()
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
                $ue->params = [
                    ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur' => $_SESSION[_S]['clasAdm-ur'],
                    ':unidad' => $_SESSION[_S]['ue_id']
                ];
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :unidad ;';
                break;
            case 2:
                $ue->params = [
                    ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur' => $_SESSION[_S]['clasAdm-ur']
                ];
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur ;';
                break;
            case 3:
            case 4:
                $parametro = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['usr_id']];
                $this->conectar();
                $this->setSql('
                    SELECT ue_id,gpo_id FROM cat_CargaTrabajo WHERE
                    gpo_id = ?
                    AND ur_id = ?
                    AND usr_id = ? ;
                ');
                $this->consultar($parametro);
                $this->desconectar();
                $tieneUe = $this->extraer();

                $ue->params = [
                    ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur' => $_SESSION[_S]['clasAdm-ur'],
                    ':us' => $_SESSION[_S]['usr_id']
                ];

                if ($tieneUe['ue_id'] != '') {
                    $ue->sql = '
                        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                        INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id,ue_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND c.usr_id = :us ;';
                } else if ($tieneUe['gpo_id'] != '') {
                    $ue->sql = '
                        SELECT u.ue_id,ue_nombre FROM cat_UnidadEjecutora u
                        INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id)
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur
                        AND c.usr_id = :us ;';
                } else {
                    $ue->sql = '
                        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                        WHERE gpo_id = :gpo
                        AND ur_id = :ur ;';
                }
                break;
            default:
                $ue->params = [
                    ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur' => $_SESSION[_S]['clasAdm-ur']
                ];
                $ue->sql = '
                    SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                    WHERE gpo_id = :gpo
                    AND ur_id = :ur ;';
                break;
        }
        $ue->label = "Unidad Ejecutora";

        $btnPDF = new stdClass();
        $btnPDF->id = "pdf";
        $btnPDF->class = "btnImp btn-outline-danger me-2";
        $btnPDF->value = "PDF";
        $btnPDF->icon = 'file-pdf me-2';

        $btnEXCEL = new stdClass();
        $btnEXCEL->id = "excel";
        $btnEXCEL->class = "btnImp btn-outline-success me-2";
        $btnEXCEL->icon = 'file-excel me-2';
        $btnEXCEL->value = "Excel";

        $ponerCombo = '';
        if ($_SESSION[_S]['nvj_id'] >= 3) {
            $ponerCombo = $this->ponCombo($tipoReporte);
        }

        $this->conectar();
        $this->setSql($ue->sql);
        $this->consultar($ue->params);
        $countUEnum = $this->getNumeroRegistros();
        $this->desconectar();

        $this->mixScript((($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : ''));

        $trimestre = new stdClass();
        $trimestre->opciones = array("" => "Seleccione el periodo", 1 => "Enero - Marzo", 2 => "Enero - Junio", 3 => "Enero - Septiembre", 4 => "Enero - Diciembre");
        $trimestre->id = "T1";
        $trimestre->label = "Periodo";
        $trimestre->width = '150px';

        $html = '
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4">
                        '.$this->ponCombo($trimestre).'
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        <input type="hidden" id="numUe" name="numUe"  value=' . $countUEnum . '>
                            ' . $ponerCombo . '
                            ' . $this->ponCombo($ue) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                        <label>Tipo de Importe:</label><br/>
                        <input type="checkbox" value="" id="ckmiles" name="ckmiles" /> Importe en miles de pesos<br/>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2" hidden>
                        <label>Leyenda:</label><br/>
                        <input type="checkbox" value="1" name="leyenda" id="leyenda" checked/> Con leyenda
                    </fieldset >
                    <br/>
                </div><br/>
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
        ';

        $this->agregaTools($this->ponBoton($btnPDF) . ' ' . $this->ponBoton($btnEXCEL));

        $this->mixHtml($html);
        

        $this->reportesTrimestrales();

        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '",{metodo:"main"});
            '));
        
        $this->mixScript('
            $("#pdf").attr("disabled","disabled");
            $("#excel").attr("disabled","disabled");

            comunica("' . $this->pantalla . '", {metodo: "validarTrimestre",id:$("#T1 option:selected").val(),ciclo:"no"});

            $("#T1").on("change", function() {
                comunica("' . $this->pantalla . '", {metodo: "validarTrimestre",id:this.value,ciclo:"si"});
            });

            $("#leyenda").click(function(){
                if($("#leyenda").is(":checked"))
                    $("#leyenda").val("1");
                else
                    $("#leyenda").val("");
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

    private function reportesTrimestrales()
    {
        $this->mixScript("
            function abrirPdf(event) {           
                var repId = 1;
                var trimestre = $('#T1 option:selected').val(); 
                if ($('#tipoReporte').length > 0) {
                    var tipoReporte = $('#tipoReporte option:selected').val();
                }else{
                    var tipoReporte = '';
                }
                var ue = $('#ue option:selected').val();                
                var ckmiles = $('#ckmiles').prop('checked');
                if ($('#grupos').length > 0) {
                    var grupos = $('#grupos').val();
                }else{
                    var grupos = '';
                }
                if ($('#excluir').length > 0) {
                    var excluir = $('#excluir').val();
                }else{
                    var excluir = '';
                }
                if ($('#nombreEspecial').length > 0) {
                    var nombrEspecial = $('#nombreEspecial').val();
                }else{
                    var nombrEspecial = '';
                }
                comunica('{$this->pantalla}', {
                    metodo: 'generarPdf',
                    rep_id: repId,
                    msc_trimestre: trimestre,      
                    tipo_reporte: tipoReporte,
                    unidad_ejecutora: ue,
                    isChecked: ckmiles,
                    gpos: grupos,
                    excluir: excluir,
                    nombre_especial: nombrEspecial
                });
            }

            function abrirExcel(event) {
                var repId = 1;
                var trimestre = $('#T1 option:selected').val(); 
                if ($('#tipoReporte').length > 0) {
                    var tipoReporte = $('#tipoReporte option:selected').val();
                }else{
                    var tipoReporte = '';
                }
                var ue = $('#ue option:selected').val();                
                var ckmiles = $('#ckmiles').prop('checked');
                if ($('#grupos').length > 0) {
                    var grupos = $('#grupos').val();
                }else{
                    var grupos = '';
                }
                if ($('#excluir').length > 0) {
                    var excluir = $('#excluir').val();
                }else{
                    var excluir = '';
                }
                if ($('#nombreEspecial').length > 0) {
                    var nombrEspecial = $('#nombreEspecial').val();
                }else{
                    var nombrEspecial = '';
                }
                comunica('{$this->pantalla}', {
                    metodo: 'generarExcel',
                    rep_id: repId,
                    msc_trimestre: trimestre,
                    tipo_reporte: tipoReporte,
                    unidad_ejecutora: ue,
                    isChecked: ckmiles,
                    gpos: grupos,
                    excluir: excluir,
                    nombre_especial: nombrEspecial
                });
            }

            $('#pdf').click(function(){
                if($('#T1 option:selected').val() == ''){
                    notify('Debe seleccionar el periodo', 'error');
                       return false;
                } 
                if($('#T1 option:selected') != ''){
                    abrirPdf();
                }             
            });
            
            $('#excel').click(function(){
                if($('#T1 option:selected').val() == ''){
                    notify('Debe seleccionar el periodo', 'error');
                        return false;
                }
                if($('#T1 option:selected') != ''){
                    abrirExcel
                }
            });
        ");
    }
    

    private function generarReporte($tipo)
    {
        $repId = $this->prm->rep_id;
        $trimestre = $this->prm->msc_trimestre;
        $tipoReporte = $this->prm->tipo_reporte;
        $ue = $this->prm->unidad_ejecutora;
        $ckmiles = $this->prm->isChecked;
        $grupos = $this->prm->gpos;
        $excluir = $this->prm->excluir;
        $nombrEspecial = $this->prm->nombre_especial;

        if (!in_array($trimestre, range(1, 4))) {
            $this->mixScript('notify("El periodo seleccionado no es válido","error");');
            return;
        }       

        $reportes = [
            1 => [
                'pdf' => 'rep/doc/scg/disciplinaFinanciera/rep_SituacionFinancieraDetallado.php',
                'excel' => 'rep/excel/scg/disciplinaFinanciera/exp_SituacionFinancieraDetallado.php'
            ]
        ];

        if (!isset($reportes[$repId][$tipo])) {
            $this->mixScript('notify("El reporte o el tipo de reporte seleccionado no es válido","error");');
            return;
        }

        $reporte = $reportes[$repId][$tipo];

        $propiedades = [
            'msc_trimestre' => $trimestre,
            'gpo_id' => $this->gpoId,
            'ur_id' => $this->urId,
            'tipo' => $tipo,
            'rep_id' => $repId,
            'tipoReporte' => $tipoReporte,
            'ue' => $ue,
            'ckmiles' => $ckmiles,
            'grupos' => $grupos,
            'excluir' => $excluir,
            'nombrEspecial' => $nombrEspecial
        ];

        $listaPropiedadesForm = implode('', array_map(function ($name, $value) {
            return ".append($('<input>').attr('type', 'hidden').attr('name', '{$name}').attr('value', '{$value}'))";
        }, array_keys($propiedades), $propiedades));

        $this->mixScript("
            let form = $('<form>')
                .attr('method', 'post')
                .attr('action', '{$reporte}')
                .attr('target', '_blank')
                {$listaPropiedadesForm}
            $('body').append(form);
            form.submit();
        ");
    }

    public function generarPdf()
    {
        $this->generarReporte('pdf');
    }

    public function generarExcel()
    {
        $this->generarReporte('excel');
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
        $parametros= [$this->gpoId,$this->urId,$corte];
        $conexion->consultar($parametros);
        $conexion->desconectar();
        if($conexion->getNumeroRegistros() == 0){ 
            switch($this->prm->ciclo){ 
                case "si":
                $this->mixScript('
                    notify("'.$concepto.'", "error");
                        comunica("' . $this->pantalla . '",{metodo:"main"});
                 ');
                break;
            }

        }else{              
            $this->mixScript('$("#pdf").removeAttr("disabled"); $("#excel").removeAttr("disabled");');
        }
    }
}

if (isset($_SESSION[_S]['prm']['C']['18']['1'][TAREA_LECTURA])) {
    new ControladorFomarto1();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
