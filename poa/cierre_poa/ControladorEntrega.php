<?php

declare(strict_types=1);
/**
 * Description of ControladorEntrega
 *
 * @author Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 30-ago-2018
 * Comment:
 * Objective: Consultar y dar de alta nuevas cedulas de Alimentación por Unidad Responsable
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 * */

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/Minuta.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . "/../../lib/constantes.php";

class ControladorEntrega extends WebResponse
{
    public $clasAdmUr;
    public $clasAdmGpo;
    public $usrId;
    public UnidadResponsable $ur;
    public function __construct()
    {
        parent::__construct();
        $this->usrId = (isset($_SESSION[_S]['usr_id']) && ($_SESSION[_S]['usr_id'] != '')) ? $_SESSION[_S]['usr_id'] : '';
        $this->clasAdmGpo = (isset($_SESSION[_S]['clasAdm-gpo']) && ($_SESSION[_S]['clasAdm-gpo'] != '')) ?  $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->clasAdmUr = (isset($_SESSION[_S]['clasAdm-ur']) && ($_SESSION[_S]['clasAdm-ur'] != '')) ? $_SESSION[_S]['clasAdm-ur'] : '';

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->clasAdmGpo);
        $this->ur->setUr_id($this->clasAdmUr);
        $this->ur->select();

        switch ($this->metodo) {
            case 'guarda':
                $this->guarda();
                break;
            default:
                $this->entrega();
                break;
        }
        $this->returnJson();
    }

    public function entrega()
    {
        $this->agregaTools("");
        $min_id = '';
        $entrega = '';
        $recibe = '';
        $observaciones = '';
        $formatos = '';
        $ban = false;
        $paso = true;

        $guardar = new stdClass();
        $guardar->id = 'guardar';
        $guardar->icon = 'fa solid fa-floppy-disk me-2';
        $guardar->color = "verde";
        $guardar->text = 'Guardar';
        $guardar->type = 'submit';
        $guardar->value = 'Guardar';

        $value = 0;

        $sela = "";
        $sele = "";
        $selar = "";
        $seler = "";
        $this->conectar();
        $this->setSql('SELECT min_id FROM poa_Minuta WHERE gpo_id = ? AND ur_id = ?');
        $params = [$this->clasAdmGpo, $this->clasAdmUr];
        $this->consultar($params);
        $this->desconectar();
        $row = $this->extraer();

        $lae = '';
        $ele = '';
        $lar = '';
        $elr = '';

        $selecionados = array();
        if ($this->getNumeroRegistros() >= 1) {

            $ban = true;
            $paso = false;
            $minuta = new Minuta();
            $minuta->setUr_id($this->clasAdmUr);
            $minuta->setGpo_id($this->clasAdmGpo);
            $minuta->setMin_id($row['min_id']);
            $minuta->select();
            $min_id = $minuta->getMin_id();
            $entrega = substr($minuta->getEntrega(), 6);
            $recibe = substr($minuta->getRecibe(), 6);
            $ele = substr($minuta->getEntrega(), 0, 2);
            $lae = substr($minuta->getEntrega(), 0, 2);
            $elr = substr($minuta->getRecibe(), 0, 2);
            $lar = substr($minuta->getRecibe(), 0, 2);
            $observaciones = $minuta->getObservacion();
            $formatos = $minuta->getFormato();

            $actualizar = new stdClass();
            $actualizar->id = 'actualizar';
            $actualizar->icon = 'fa solid fa-floppy-disk me-2';
            $actualizar->color = "verde";
            $actualizar->text = 'Actualizar';
            $actualizar->type = 'submit';
            $actualizar->value = 'Actualizar';

            if ($formatos != '') {
                $selecionados = explode(",", $formatos);
            }
        }

        $p = new stdClass();
        $checks = '';
        $p->value = $value;

        $ch = new stdClass();
        $ninguno = '';
        $ch->value = 0;
        $values = array(
            'POA 01 - IDENTIFICACIÓN INSTITUCIONAL',
            'POA 02 - PLANEACIÓN INSTITUCIONAL',
            'POA 03 - MATRIZ DE INDICADORES PARA RESULTADOS',
            'POA 04 - ESTRUCTURA PROGRAMÁTICA',
            'POA 05 - VINCULACIÓN MATRIZ DE INDICADORES PARA RESULTADOS - ESTRUCTURA PROGRAMÁTICA',
            'POA 06 - CLASIFICACIÓN FUNCIONAL',
            'POA 07 - CLASIFICACIÓN PROGRAMÁTICA',
            'POA 08 - RESUMEN PRESUPUESTAL POR EJE - TEMA - OBJETIVO',
            'POA 09 - PROGRAMA - SUBPROGRAMA - UE - PROYECTO - ACTIVIDAD',
            'POA 10 - PROGRAMA - FUENTE DE FINANCIAMIENTO',
            'POA 11 - RESUMEN PRESUPUESTAL POR IDENTIFICADOR DE PARTIDA',
            'POA 12 - UR - UE - PROGRAMA - SUBPROGRAMA - PROYECTO - OBRA ACTIVIDAD - PARTIDA',
            'POA 13 - INVENTARIO DE PLAZAS',
            'POA 14 - DESPLIEGUE POR RELACIÓN LABORAL',
            'POA 15 - MODALIDAD DE CONTRATACIÓN - PARTIDA ESPECÍFICA',
            'POA 16 - SERVICIOS DE ALIMENTACIÓN (COMEDOR Y VÍVERES)',
            'POA 17 - SERVICIOS DE TELEFONÍA (CELULAR Y FIJA)',
            'POA 18 - INVENTARIO DE VEHÍCULOS POR UR',
            'POA 10 - SERVICIOS DE ARRENDAMIENTO (TERRENOS Y EDIFICIOS)',
            'POA 20 - SEGUROS Y FIANZAS DE BIENES INMUEBLES',
            'POA 21 - SERVICIOS DE VIGILANCIA',
            'POA 22 - OTRAS PARTIDAS',
            'POA 23 - UR - CAPÍTULO - IDENTIFICADOR DE PARTIDA - PARTIDA'
        );
        $names = array('poa1', 'poa2', 'poa3', 'poa4', 'po5', 'poa6', 'poa7', 'poa8', 'poa9', 'poa10', 'poa11', 'poa12', 'poa13', 'poa14', 'poa15', 'poa16', 'poa17', 'poa18', 'poa19', 'poa20', 'poa21', 'poa22', 'poa23');
        $selecionados = array_filter($selecionados, "strlen");
        $p = new stdClass();
        $p->class = 'me-2 docuPoa';
        $p->selecionados = $selecionados;
        $p->names = $names;
        $p->values = $values;
        $p->vertical = true;

        $checks .= '<tr><td colspan="2"> ' . $this->ponCheck($p) . '</td></tr>';

        $ninguno .= '';

        $this->setSql(
            '
            SELECT CONCAT(usr_nombre," ",usr_paterno," ",usr_materno) AS nombre
                FROM `cat_Usuario`
                INNER JOIN ' . BD_USUARIO . '.cat_Usuario USING (usr_id)
                WHERE usr_id = ?;'
        );
        $params = [$this->usrId];
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $row = $this->extraer();

        /**Entrega por parte de la Unidad Responsable */
        if ($ele != '' && $ele == 'EL') {
            $sele  = "selected";
        }
        if ($lae != '' && $lae == 'LA') {
            $sela  = "selected";
        }
        /** Recibe por parte de la Secretar&iacute;a de Finanzas*/
        if ($elr != '' && $elr == 'EL') {
            $seler = "selected";
        }
        if ($lar != '' && $lar == 'LA') {
            $selar = "selected";
        }

        $this->mixHtml('
            <form id="minuta" enctype="multipart/form-data" method="post" action="rep/doc/poa/rep_minuta.php" target="_blank">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th align="center" colspan="2" class="success">ENTREGA POA ' . APP_EJERCICIO . ' </th>
                    </tr>
                    <tbody>
                        <tr>
                            <td width="30%"><strong style="position: relative;top: 30%;">Entrega por parte de la Unidad Responsable </strong></td>
                            <td colspan="2">
                                <div class = "row">
                                    <div class = "col-md-1"  style="width: 11.6%;">
                                        <select class="form-select form-select-sm"  id="trato1"  name="trato1" aria-label="Default select example">
                                            <option selected value="EL" ' . $sele . '>EL</option>
                                            <option  value="LA" ' . $sela . '>LA</option>
                                        </select>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="input-group input-group-sm mb-3">
                                           <span class="input-group-text" id="basic-addon1">C .</span>
                                           <input type="text" id="entregaNombre" name="entrega" size="70" value="' . $entrega . '" class="form-control"  aria-label="Username" aria-describedby="basic-addon1">
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="position: relative;top: 30%;"><strong >Recibe por parte de la Secretar&iacute;a de Finanzas </strong></td>
                            <td colspan="2">
                                <div class = "row">
                                    <div class = "col-md-1" style="width: 11.6%;">
                                        <select class="form-select form-select-sm"  id="trato2"  name="trato2" aria-label="Default select example">
                                            <option selected value="EL" ' . $seler . '>EL</option>
                                            <option  value="LA" ' . $selar . '>LA</option>
                                        </select>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="input-group input-group-sm mb-3">
                                        <span class="input-group-text" id="basic-addon1">C .</span>
                                        <input type="text" id="recibeNombre" name="recibe" size="70" value="' . $recibe . '" class="form-control"  aria-label="Username" aria-describedby="basic-addon1">
                                        </div>
                                    </div>
                                </div>
                        </tr>
                        <tr><td>' . $checks . '<td></tr>
                        <tr>
                            <td colspan="2"><strong>Observaciones:</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2"> 
                                <textarea class="form-control anulaenter "height: 70px;" name="observaciones" id="observaciones">' . $observaciones . '</textarea>
                            </td>
                        </tr>
                        <tr>
        ');

        if ($paso == true) {
            $this->mixHtml('<tr><td colspan="2">' . $this->ponBoton($guardar) . '</td></tr>');
        }
        if ($ban == true) {
            $this->mixHtml('<tr><td colspan="2">' . $this->ponBoton($actualizar) . '</td></tr>');
        }

        $this->mixHtml('
                    </tbody>
                </table>
                <input id="fe" name="fe" type="hidden" size="70" value="" />
                <input id="min_id" name="min_id" type="hidden" size="70" value="' . $min_id . '" />
            </form>
        ');

        if ($ban == true) {
            $click = '
                $("#actualizar").click(function () {
                    docuPoa = "";
                    $(".docuPoa:checked").each(function () {
                        docuPoa += $(this).val() + ",";
                    });
                    $("#fe").val(docuPoa);
                    entrega = $("#trato1").val() + " C. " + $("#entregaNombre").val();
                    recibe = $("#trato2").val() + " C. " + $("#recibeNombre").val();
                    min_id = ($("#min_id").val()) ? $("#min_id").val() : 0;
                    observaciones = $("#observaciones").val();
                    comunica(
                        "' . $this->pantalla . '",
                        {
                            "metodo": "guarda",
                            "min_id": min_id,
                            "entrega": entrega,
                            "recibe": recibe,
                            "entregan": docuPoa,
                            "observaciones": observaciones
                        }
                    );
                });
            ';
        } else {
            $click = '
                $("#guardar").click(function () {
                    docuPoa = "";
                    $(".docuPoa:checked").each(function () {
                        docuPoa += $(this).val() + ",";
                    });
                    $("#fe").val(docuPoa);
                    entrega = $("#trato1").val() + " C. " + $("#entregaNombre").val();
                    recibe = $("#trato2").val() + " C. " + $("#recibeNombre").val();
                    min_id = ($("#min_id").val()) ? $("#min_id").val() : 0;
                    observaciones = $("#observaciones").val();
                    comunica(
                        "' . $this->pantalla . '", {
                        "metodo": "guarda",
                        "min_id": min_id,
                        "entrega": entrega,
                        "recibe": recibe,
                        "entregan": docuPoa,
                        "observaciones": observaciones
                    }
                    );
                });
            ';
        }
        $this->mixScript('
            limpiaClasAdmin();
            $("#techos").show();
            $("#todos").click(function() {
                if ($("#todos:checked").length == 1) {
                    $(".docuPoa").attr("checked", "checked");
                } else $(".docuPoa").removeAttr("checked");
            });
            ' . $click . '
        ');
    }

    public function guarda()
    {
        if ($this->prm->entregan == "") {
            $this->mixScript('notify("Debe seleccionar minimamente un reporte de la lista", "error");');
        } else {
            if ($this->prm->observaciones == "") {
                $this->prm->observaciones = " ";
            }
            $minuta = new Minuta();
            $minuta->setUr_id($this->clasAdmUr);
            $minuta->setGpo_id($this->clasAdmGpo);
            $minuta->setEntrega($this->prm->entrega);
            $minuta->setRecibe($this->prm->recibe);
            $minuta->setFormato($this->prm->entregan);
            $minuta->setFecha(date("Y-m-d"));
            $minuta->setHora(date("Y-m-d H:i:s"));
            $minuta->setObservacion($this->prm->observaciones);

            if ($this->prm->min_id != 0) {
                $minuta->setMin_id($this->prm->min_id);
                $minuta->update();
                $this->mixScript('notify("Entrega actualizada Correctamente", "success");');
            } else {
                $minuta->insert();
                $this->mixScript('notify("Entrega guardada Correctamente", "success");');
            }

            $this->entrega();
        }
    }
}

if (isset($_SESSION[_S]['prm']['A']['8']['2']['1'])) {
    new ControladorEntrega();
} else {
    WebResponse::sinPermisos();
}
