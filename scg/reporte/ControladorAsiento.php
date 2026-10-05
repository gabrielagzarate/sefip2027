<?php
//inicio de sesion y llamada a las librerias
//Hazael Hernandez Febrero 2018
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorAsiento extends WebResponse
{
    private $nivel;

    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();

        switch ($this->metodo) {
            case 'mesHasta':$this->mesHasta();
                break;
            default:$this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $unidadResponsable = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];

        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable";
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = 1;
        $comboMesDesde->width = '150px';

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable;";
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->width = "150px";
        $comboMesHasta->label = "Hasta";
        $comboMesHasta->selecciona = 1;

        $btnEXCEL = new stdClass();
        $btnEXCEL->id = "excel";
        $btnEXCEL->class = "btnImp btn-success";
        $btnEXCEL->text='Excel';
        $btnEXCEL->value='&nbsp;&nbsp;Excel';
        $btnEXCEL->icon = "fa fa-btn fa-solid fa-file-excel";
        $btnEXCEL->type = "submit";

        $html = '
            <form id="formAsiento" method="post" target="_blank" action="rep/excel/scg/reporte/exp_Asiento.php">
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend id ="leyenda">Reporte Asiento </legend>
                        <fieldset class="col-md-12 col-xs-12">
                            <legend>Periodo</legend>
                            <div class="row"
                                <div class="col-md-12 col-xs-12">
                                    <div class="col-md-3">
                                        ' . $this->ponCombo($comboMesDesde) . '
                                    </div>
                                    <div id="mhasta" class="col-md-3">
                                        ' . $this->ponCombo($comboMesHasta) . '
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>
                </div>
            </form>
        ';

        $this->mixHtml($html);

        $this->agregaTools($this->ponBoton($btnEXCEL));

        $this->mixScript('
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#excel").click(function(){
                $("#formAsiento").submit();
            });

            $("#comboMesDesde").change(function(){
                if($(this).val()){
                    comunica("' . $this->pantalla . '", {
                        metodo: "mesHasta",
                        mes: $(this).val()
                    });
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
            $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable WHERE msc_id >= ? ;";
            $comboMesHasta->params = array($mes);
            $comboMesHasta->id = "comboMesHasta";
            $comboMesHasta->width = "150px";
            $comboMesHasta->label = "hasta";
            $comboMesHasta->selecciona = $mes;

            $this->mixHtml($this->ponCombo($comboMesHasta));
        }
    }

}

//aqui
if (isset($_SESSION[_S]['prm']['C']['10']['12']['1'])) {
    new ControladorAsiento();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
