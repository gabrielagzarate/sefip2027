<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorCambiosFinanciera extends WebResponse
{
    public $sis_id;
    public $men_id;
    public $opc_id;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        $this->sis_id = $_POST['opcion']['sis_id'];
        $this->men_id = $_POST['opcion']['men_id'];
        $this->opc_id = $_POST['opcion']['opc_id'];

        if (isset($this->prm->opcion)) {
            $_SESSION[_S]['reportes'] = $this->prm->opcion->sis_id . $this->prm->opcion->men_id . $this->prm->opcion->opc_id;
        }

        switch ($this->metodo) {
            case 'main':$this->main();
                break;
            case 'mesHasta':$this->mesHasta();
                break;
            default:$this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {

        $ur = new UnidadResponsable();
        $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $ur->select();

        $tipoReporte = new stdClass();

        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
                // case 4:
                $tipoReporte->opciones = [1 => 'Solo la UR actual', 2 => 'Todas las UR de la Carga de Trabajo'];
                break;
            default:
                $tipoReporte->opciones = [1 => 'Solo la UR actual', 2 => 'Todas las UR de la Carga de Trabajo', 3 => 'Limitar que incluir y que no'];
                break;
        }
        $tipoReporte->id = 'tipoReporte';
        $tipoReporte->label = 'Tipo de Reporte';
        $tipoReporte->selecciona = 1;
        $tipoReporte->width = '150px';

        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = 'SELECT msc_id, msc_nombre FROM cat_MesContable;';
        $comboMesDesde->id = 'comboMesDesde';
        $comboMesDesde->label = 'Desde:';
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = 1;
        $comboMesDesde->enDiv = true;
        $comboMesDesde->search = true;

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = 'SELECT msc_id, msc_nombre FROM cat_MesContable;';
        $comboMesHasta->id = 'comboMesHasta';
        $comboMesHasta->width = '150px';
        $comboMesHasta->label = 'Hasta:';
        $comboMesHasta->selecciona = 1;
        $comboMesHasta->search = true;

        $grupos = new stdClass();
        $grupos->disabled = true;
        $grupos->id = 'grupos';
        $grupos->class = 'paso';
        $grupos->value = '1,2,3,7,8';
        $grupos->enDiv = true;
        $grupos->label = 'Grupos a incluir';

        $excluir = new stdClass();
        $excluir->disabled = true;
        $excluir->id = 'excluir';
        $excluir->enDiv = true;
        $excluir->class = 'paso';
        $excluir->label = 'URs a excluir';

        $firmante1 = new stdClass();
        $firmante1->id = 'firmante1';
        $firmante1->enDiv = true;
        $firmante1->value = $ur->getNombreFinancieros();
        $firmante1->label = 'Firmante 1';

        $firmante2 = new stdClass();
        $firmante2->id = 'firmante2';
        $firmante2->enDiv = true;
        $firmante2->value = $ur->getNombreAdministrativo();
        $firmante2->label = 'Firmante 2';

        $firmante3 = new stdClass();
        $firmante3->id = 'firmante3';
        $firmante3->enDiv = true;
        $firmante3->value = $ur->getNombreTitular();
        $firmante3->label = 'Firmante 3';

        $cargo1 = new stdClass();
        $cargo1->id = 'cargo1';
        $cargo1->enDiv = true;
        $cargo1->value = $ur->getCargoFinancieros();
        $cargo1->label = 'Cargo 1';

        $cargo2 = new stdClass();
        $cargo2->id = 'cargo2';
        $cargo2->enDiv = true;
        $cargo2->value = $ur->getCargoAdministrativo();
        $cargo2->label = 'Cargo 2';

        $cargo3 = new stdClass();
        $cargo3->id = 'cargo3';
        $cargo3->enDiv = true;
        $cargo3->value = $ur->getCargoTitular();
        $cargo3->label = 'Cargo 3';

        $nombreEspecial = new stdClass();
        $nombreEspecial->disabled = true;
        $nombreEspecial->id = 'nombreEspecial';
        $nombreEspecial->enDiv = true;
        $nombreEspecial->class = 'paso';
        $nombreEspecial->placeholder = 'Ejem: SECTOR CENTRAL';
        $nombreEspecial->label = 'Encabezado Personalizado';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->search = true;
        $ue->enDiv = true;
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND ue_id =?;';
                $ue->params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['ue_id']];
                break;
            case 2:
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? ;';
                $ue->params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']];
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
                $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['usr_id']]);
                $this->desconectar();
                $tieneUe = $this->extraer();

                if ($tieneUe['ue_id'] != '') {
                    $ue->sql = '
                        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                        INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id,ue_id)
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND c.usr_id = ? ;';
                    $ue->params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['usr_id']];
                } elseif ($tieneUe['gpo_id'] != '') {
                    $ue->sql = '
                        SELECT u.ue_id,ue_nombre FROM cat_UnidadEjecutora u
                        INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id)
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND c.usr_id = ? ;';
                    $ue->params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$_SESSION[_S]['usr_id']];
                } else {
                    $ue->sql = '
                        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                        WHERE gpo_id = ?
                        AND ur_id = ? ;';
                    $ue->params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']];
                }
                break;
            default:
                $ue->sql = '
                    SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
                    WHERE gpo_id = ?
                    AND ur_id = ? ;';
                $ue->params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']];
                break;
        }
        $ue->label = 'Unidad Ejecutora';

        $btnPDF = new stdClass();
        $btnPDF->id = 'pdf';
        $btnPDF->class = 'btnImp btn-outline-danger me-2';
        $btnPDF->value = 'PDF';
        $btnPDF->icon = 'fa-solid fa-file-pdf me-2';
        $btnPDF->type = 'submit';
        $btnPDF->form = 'formEdo';

        $btnEXCEL = new stdClass();
        $btnEXCEL->id = 'excel';
        $btnEXCEL->class = 'btnImp btn-outline-success';
        $btnEXCEL->icon = 'fa-solid fa-file-excel me-2';
        $btnEXCEL->value = 'Excel';
        $btnEXCEL->type = 'submit';
        $btnEXCEL->form = 'formEdo';

        $this->conectar();
        $this->setSql($ue->sql);
        $this->consultar($ue->params);
        $countUEnum = $this->getNumeroRegistros();
        $this->desconectar();

        $html = '
            <legend align="center"></legend>
            <form id="formEdo" method="post" target="_blank">
                <input type="hidden" id="sis_id" name="sis_id" value="' . $this->sis_id . '">
                <input type="hidden" id="men_id" name="men_id" value="' . $this->men_id . '">
                <input type="hidden" id="opc_id" name="opc_id" value="' . $this->opc_id . '">

                <div class="row">
                    <fieldset class="col-md-2 col-xs-2">
                        ' . $this->ponCombo($comboMesDesde) . '
                    </fieldset>
                    <fieldset id="mhasta" class="col-md-2 col-xs-2">
                        ' . $this->ponCombo($comboMesHasta) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        <input type="hidden" id="numUe" name="numUe"  value=' . $countUEnum . '>
                            ' . ($_SESSION[_S]['nvj_id'] >= 3 ? $this->ponCombo($tipoReporte) : '') . '
                            ' . $this->ponCombo($ue) . '
                    </fieldset >
                    <fieldset class="col-md-2 col-xs-2">
                        <label>Tipo de Importe:</label><br/>
                        <input type="checkbox" value="1" name="ckmiles" id="ckmiles"/> En miles de pesos<br/>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2" hidden>
                        <label>Leyenda:</label><br/>
                        <input type="checkbox" value="1" name="leyenda" id="leyenda" checked/> Con leyenda
                    </fieldset>
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
                <div class="row mb-4">
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($firmante1) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($cargo1) . '
                    </fieldset>
                </div>
                <div class="row mb-4">
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($firmante2) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($cargo2) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($firmante3) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($cargo3) . '
                    </fieldset>
                </div>
                <br />
                <div class="row row-cols-2">
                    <fieldset class="col-xs-6" style="padding-right: 1px !important;">
                        <legend>Niveles a Incluir</legend>
                        <input id="checkNivel1" name="checkNivel1" type="checkbox" value=1 readonly="readonly" checked onclick="javascript: return false;">&nbsp;1</input>&nbsp;&nbsp;&nbsp;
                        <input id="checkNivel2" name="checkNivel2" type="checkbox" value=1 checked>&nbsp;2</input>&nbsp;&nbsp;&nbsp;
                        <input id="checkNivel3" name="checkNivel3" type="checkbox" value=1 checked>&nbsp;3</input>&nbsp;&nbsp;&nbsp;
                        <input id="checkNivel4" name="checkNivel4" type="checkbox" value=1>&nbsp;4</input>&nbsp;&nbsp;&nbsp;
                        <input id="checkNivel5" name="checkNivel5" type="checkbox" value=1>&nbsp;5</input>&nbsp;&nbsp;&nbsp;
                    </fieldset>
                    <fieldset class="col-xs-6" style="padding-right: 1px !important;">
                        <legend>Generación de Reporte</legend>
                        <span hidden>
                        <input id="ceros" name="ceros" type="checkbox" value=1 checked>&nbsp;Cuentas sin importe</input>&nbsp;&nbsp;&nbsp;
                        </span>
                        <input id="incCta" name="incCta" type="checkbox" value=1 data-placement="bottom" title="Incluye la cuenta contable y separa líneas con sombreado">&nbsp;Modo Validación</input>&nbsp;&nbsp;&nbsp;
                        Interlineado : <input id="intLin" name="intLin" type="text" value="2.5" data-placement="bottom" title="Interlineado" size=3 />&nbsp;&nbsp;&nbsp;
                    </fieldset>
                </div><br/>
                <div class="row">
                    <fieldset class="col-md-3 col-xs-6">
                    </fieldset>
                </div>
            </form>
        ';

        $this->mixHtml($html);

        $this->agregaTools($this->ponBoton($btnPDF) . ' ' . $this->ponBoton($btnEXCEL));

        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '",{metodo:"main"});
            ')
        );
        if ($this->men_id == '11') { // Cuenta Pública
            $this->mixScript('
                $("#pdf").click(function(){
                    $("#formEdo").attr("action","rep/doc/scg/cuentaPublica/reporte.php");
                    $("#formEdo").submit();
                });
            ');
        } else { // Estados financieros
            $this->mixScript('
                $("#pdf").click(function(){
                    $("#formEdo").attr("action","rep/doc/scg/cuentaPublica/rep_CambiosFinanciera.php");
                    $("#formEdo").submit();
                });
            ');
        }

        $this->mixScript('
            $("#comboMesDesde").change(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "mesHasta",
                    mes: $(this).val()
                });
            });

            $("#leyenda").click(function(){
                if($("#leyenda").is(":checked"))
                    $("#leyenda").val("1");
                else
                    $("#leyenda").val("");
            });

            $("#excel").click(function(){
                $("#formEdo").attr("action","rep/excel/scg/cuentaPublica/exp_CambiosFinanciera.php");
                $("#formEdo").submit();
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

    private function mesHasta()
    {
        $this->setContenedor('mhasta');
        if (isset($this->prm->mes)) {
            $mes = $this->prm->mes;

            $comboMesHasta = new stdClass();
            $comboMesHasta->sql = 'SELECT msc_id, msc_nombre FROM cat_MesContable WHERE msc_id >= ? ;';
            $comboMesHasta->params = [$mes];
            $comboMesHasta->id = 'comboMesHasta';
            $comboMesHasta->width = '150px';
            $comboMesHasta->label = 'hasta';
            $comboMesHasta->selecciona = $mes;

            $this->mixHtml($this->ponCombo($comboMesHasta));
        }
    }

}

if ((isset($_SESSION[_S]['prm']['C']['11']['4']) || isset($_SESSION[_S]['prm']['C']['9']['4']))) {
    new ControladorCambiosFinanciera();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}

// Fin del código
