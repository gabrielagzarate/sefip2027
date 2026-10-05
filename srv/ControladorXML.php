<?php

ini_set('display_errors', '0');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
class ControladorXml extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->main();
        $this->returnJson();
    }

    public function main()
    {
        $html = '';
        $tablaPresupuesto = '
            <!--<tr>
                <td>
                    <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="P" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"  style="display:none" id="enviar" value="P" />
                </td>
                <td><strong>Tabla presupuesto </strong></td>
                <td><strong>Información de Presupuesto </strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button  form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="P-D" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"  style="display:none" id="enviar" value="P-D" />
                </td>
                <td><strong>Tabla presupuesto con detalle </strong></td>
                <td><strong>Información de Presupuesto con detalle </strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="R-C" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"   style="display:none" id="enviar" value="R-C" />
                </td>
                <td><strong>Tabla scp_Clc</strong></td>
                <td><strong>Información de Clcs por Unidad Responsable</strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-C" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"   style="display:none" id="enviar" value="M-C" />
                </td>
                <td><strong>Tabla scp_Afectacion</strong></td>
                <td><strong>Información de Afectaciones por Clc</strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button form="reportes" type="buttom"  name="enviar" class="btn btn-outline-secondary btn-xs xml" value="D-C" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"  style="display:none" id="enviar" value="D-C" />
                </td>
                <td><strong>Tabla scp_Contable y scp_Presupuestal </strong></td>
                <td><strong>Información de números de control por afectación y por Clc </strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-T" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar" style="display:none" id="enviar" value="M-T" />
                </td>
                <td><strong>Tabla scp_MaestroModificacion </strong></td>
                <td><strong>Información del folio</strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="D-T" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"  style="display:none" id="enviar" value="D-T" />
                </td>
                <td><strong>Tabla scp_DetalleModificacion </strong></td>
                <td><strong>Detalle del folio de modificación</strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="C-PE" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"  style="display:none" id="enviar" value="C-PE" />
                </td>
                <td><strong>Tabla cat_PartidaEspecifica </strong></td>
                <td><strong>Información de Partida Especifica</strong></td>
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </tr>
            <tr>
                <td>
                    <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="C-CF" >
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <input name="enviar"  style="display:none" id="enviar" value="C-CF" />
                </td>
                <td><strong>Tabla cat_ClaveFinanciamiento2027 y enc_ClaveFinanciamiento </strong></td>
                <td><strong>Información de Clave Financiamiento</strong></td>
                <input id="accion" name="accion" type="hidden" value="upload" />
            </tr>-->
        ';
        if (isset($_SESSION[_S]['prm']['A']['12']['2']['122'])) {
            $tablaPresupuesto .=
            '<tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="C-CH" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="C-CH" />
                    </td>
                    <td><strong>Vinculación Programática Presupuestal por UR</strong></td>
                    <td><strong>Información de Vinculación Programática Presupuestal por UR</strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload" />
                </tr>
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="C-ALL" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="C-ALL" />
                    </td>
                    <td><strong>Vinculación Programática Presupuestal</strong></td>
                    <td><strong>Información de Vinculación Programática Presupuestal</strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload" />
                </tr>
        ';
        }

        $tablaCatalogos = '
            <fieldset class="col-md-6">
                <tr class="tabla-scg"><th style="text-align:center;" colspan="3" style="">Catálogos</th></tr>
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="D-H" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="D-H" />
                    </td>
                    <td><strong>Catálogo del Objeto del Gasto </strong></td>
                    <td><strong>Información del Objeto del Gasto </strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload"  />
                </tr>
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-HA" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="M-HA" />
                    </td>
                    <td><strong>Catálogo de Unidades Ejecutoras </strong></td>
                    <td><strong>Información de Unidades Ejecutoras </strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload"  />
                </tr>
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-HB" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="M-HB" />
                    </td>
                    <td><strong>Catálogo de Programas </strong></td>
                    <td><strong>Información de Programas </strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload"  />
                </tr>
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-HC" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="M-HC" />
                    </td>
                    <td><strong>Catálogo de SubProgramas </strong></td>
                    <td><strong>Información de SubProgramas </strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload"  />
                </tr>
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-HD" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar" style="display:none" id="enviar" value="M-HD" />
                    </td>
                    <td><strong>Catálogo de Proyectos </strong></td>
                    <td><strong>Información de Proyectos </strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload"  />
                </tr>
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-HE" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="M-HE" />
                    </td>
                    <td><strong>Catálogo de Obra Actividad </strong></td>
                    <td><strong>Información de Obra Actividad </strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload"  />
                </tr>
            
        ';
        if (isset($_SESSION[_S]['prm']['A']['1']['1']['120'])) { // permisos al reporte de directorio UR
        $tablaCatalogos .='
                <tr>
                    <td>
                        <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="M-UR" >
                        <i class="fa-solid fa-eye"></i>
                        </button>
                        <input name="enviar"  style="display:none" id="enviar" value="M-UR" />
                    </td>
                    <td><strong>Catálogo de Directorio UR</strong></td>
                    <td><strong>Información de las Unidades Responsables </strong></td>
                    <input id="accion" name="accion" type="hidden" value="upload"  />
                </tr>';
        }
        $tablaCatalogos .='</fieldset>';

        $html .= '
            <form id="reportes" action="srv/GuardaConsultaXML.php" method="post" enctype="multipart/form-data">
                <input name="enviar" type="text" style="display:none" id="enviar" value="" />
                <input name="prg_id" type="text" style="display:none" id="prg_id" value="" />
                <input id="accion" name="accion" type="hidden" value="upload"  />
            </form>
            <table id="tabla-scg" class="table table-bordered table-hover">
            <div class="row">
                <fieldset class="col-md-6">
                    <tr class="tabla-scg">
                        <th style="text-align:center;" colspan="3" style="">Información</th>
                    </tr>
                    <tr class="tabla-scg">
                        <th>VER</th> <th>NOMBRE</th> <th>DESCRIPCIÓN</th>
                    </tr>
                    ' . $tablaPresupuesto . '
                <!--Hasta aca-->
                ' . $tablaCatalogos;
        if (isset($_SESSION[_S]['prm']['A']['12']['2']['98'])) {
            $html .= '
                <fieldset class="col-md-6">
                    <tr class="tabla-scg-w">
                        <th style="text-align:center;" colspan="3" style="">Reporte MIR </th>
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="R-M" >
                            <i class="fa-solid fa-eye"></i>
                            </button>
                            <input name="enviar" style="display:none" id="enviar" value="R-M" />
                        </td>
                        <td><strong>Reporte MIR</strong></td>
                        <td><strong>Reporte de control MIR</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload"  />
                    </tr>
                </fieldset>
            ';
        }
        if (isset($_SESSION[_S]['prm']['A']['12']['2']['98'])) {
            $html .= '
                <fieldset class="col-md-6">
                    <tr class="tabla-scg-w">
                        <th style="text-align:center;" colspan="3">Catálogos MIR </th>
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="C-PF" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="C-PF" />
                        </td>
                        <td><strong>Productos Finales</strong></td>
                        <td><strong>Información del catálogo de productos finales </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="C-PI" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="C-PI" />
                        </td>
                        <td><strong>Productos Intermedios</strong></td>
                        <td><strong>Información del catálogo de productos intermedios </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-1" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-1" />
                        </td>
                        <td><strong>Indicadores</strong></td>
                        <td><strong>Información de los Indicadores </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-2" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-2" />
                        </td>
                        <td><strong>Productos Finales del Indicador</strong></td>
                        <td><strong>Información de los Productos Finales del Indicador</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-3" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-3" />
                        </td>
                        <td><strong>Productos Intermedios del Indicador</strong></td>
                        <td><strong>Información de los Productos Intermedios del Indicador </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-6" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-6" />
                        </td>
                        <td><strong>Nivel fin con metas</strong></td>
                        <td><strong>Reporte nivel Fin (PED) con metas</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-7" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-7" />
                        </td>
                        <td><strong>Nivel propósito con metas</strong></td>
                        <td><strong>Reporte nivel Propósito (PES) con metas</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-4" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-4" />
                        </td>
                        <td><strong>Productos finales con información MIR </strong></td>
                        <td><strong>Reporte Productos finales con información MIR </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>';
            // Aquí agregamos la condición específica para el valor P-5
            if (isset($_SESSION[_S]['prm']['A']['12']['2']['98'])) {
                $html .= '
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-5" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-5" />
                        </td>
                        <td><strong>Productos Intermedios con información MIR </strong></td>
                        <td><strong>Reporte Productos Intermedios con información MIR </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>';
            } elseif (isset($_SESSION[_S]['prm']['A']['12']['12']['116'])) {
                $html .= '
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-11" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-11" />
                        </td>
                        <td><strong>Productos Intermedios con información MIR </strong></td>
                        <td><strong>Reporte Productos Intermedios con información MIR </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>';
            }

            // Resto del HTML
            $html .= '
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-8" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-8" />
                        </td>
                        <td><strong>Seguimiento Productos Finales con metas</strong></td>
                        <td><strong>Reporte de Seguimiento Productos Finales con metas </strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-10" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-10" />
                        </td>
                        <td><strong>Seguimiento Productos Intermedios con metas</strong></td>
                        <td><strong>Reporte de Seguimiento Productos Intermedios con metas</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                </fieldset>
            ';
        }

        if (isset($_SESSION[_S]['prm']['A']['12']['12']['116'])) {

            $html .= '
                <fieldset class="col-md-6">
                    <tr class="tabla-scg-w">
                        <th style="text-align:center;" colspan="3" style="">Seguimiento Program&aacute;tico Financiero </th>
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="R-PFN" >
                            <i class="fa-solid fa-eye"></i>
                            </button>
                            <input name="enviar" style="display:none" id="enviar" value="R-PFN" />
                        </td>
                        <td><strong>Reporte de Avance Financiero</strong></td>
                        <td><strong>Reporte de Avance Program&aacute;tico Financiero</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload"  />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="R-PFS" >
                            <i class="fa-solid fa-eye"></i>
                            </button>
                            <input name="enviar" style="display:none" id="enviar" value="R-PFS" />
                        </td>
                        <td><strong>Reporte de Avance Fisico</strong></td>
                        <td><strong>Reporte de Avance Program&aacute;tico F&iacute;sico</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload"  />
                    </tr>
                </fieldset>
            ';

        }

        if (isset($_SESSION[_S]['prm']['A']['12']['12']['116'])) {
            # code...
            $html .= '
                <fieldset class="col-md-6">
                    <tr class="tabla-scg-w">
                        <th style="text-align:center;" colspan="3" style="">Seguimiento MIR</th>
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-86" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-86" />
                        </td>
                        <td><strong>Seguimiento a Propósitos</strong></td>
                        <td><strong>Reporte de Seguimiento a Propósitos a Detalle</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-85" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-85" />
                        </td>
                        <td><strong>Seguimiento a Componentes (PF)</strong></td>
                        <td><strong>Reporte de Seguimiento a Componentes (PF) a Detalle</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="P-84" ><i class="fa-solid fa-eye"></i></button>
                            <input name="enviar" style="display:none" id="enviar" value="P-84" />
                        </td>
                        <td><strong>Seguimiento a Actividades (PI)</strong></td>
                        <td><strong>Reporte de Seguimiento a Actividades (PI) a Detalle</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload" />
                    </tr>
                </fieldset>
            ';
        }

        if (isset($_SESSION[_S]['prm']['S']['1']['9']['118'])) {

            $html .= '
                <fieldset class="col-md-6">
                    <tr class="tabla-scg-w">
                        <th style="text-align:center;" colspan="3" style="">Segumiento Adecuación Metas MIR Componente</th>
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar" class="btn btn-outline-secondary btn-xs xml" value="T-FMC" >
                            <i class="fa-solid fa-eye"></i>
                            </button>
                            <input name="enviar" style="display:none" id="enviar" value="T-FMC" />
                        </td>
                        <td><strong>Tabla scp_ModificacionMeta</strong></td>
                        <td><strong>Informaci&oacute;n del folio</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload"  />
                    </tr>
                    <tr>
                        <td>
                            <button form="reportes" type="buttom" name="enviar"  class="btn btn-outline-secondary btn-xs xml" value="T-DFMC" >
                            <i class="fa-solid fa-eye"></i>
                            </button>
                            <input name="enviar" style="display:none" id="enviar" value="T-DFMC" />
                        </td>
                        <td><strong>Tabla scp_DetalleModificacionMetaComponente</strong></td>
                        <td><strong>Detalle del folio de modificaci&oacute;n</strong></td>
                        <input id="accion" name="accion" type="hidden" value="upload"  />
                    </tr>
                </fieldset>
            ';

        }

        $html .= '
            </div>
            </table>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $(".xml").click(function() {
                var valor = $(this).val();
                if (valor == "P-10") {
                    $("#divContenedorGeneralModal").html(
                        $("<div/>").css("display","block").attr("tabindex","-1").addClass("modal").attr("id","myModal")
                            .html(
                                $("<div/>").addClass("modal-dialog modal-lg")
                                    .html(
                                        $("<div/>").addClass("modal-content")
                                            .html(
                                                $("<div/>").addClass("modal-header")
                                                    .html( $("<h4/>").addClass("modal-title").text("Reporte Seguimiento Productos Intermedios con metas") )
                                                    .append( $("<button/>").addClass("btn-close").attr("type","button").text("").click(function(){ $("#myModal").remove(); return false;}) )

                                            ).append(
                                                $("<div/>").addClass("modal-body")
                                                    .html( $("<div/>").addClass("from-group")
                                                        .html( $("<label/>").attr("for","prg").text("Numero de Programa:") )
                                                        .append( $("<input/>").attr("name","prg").attr("id","prg").attr("maxlength","3").attr("required","1").addClass("form-control").css("width","80px") )
                                                    )
                                            ).append( $("<div/>").addClass("modal-footer")
                                                .html( $("<button/>").attr("id","ok").addClass("btn btn-outline-success").text("Aceptar")
                                                    .click(function(){
                                                        if($("#prg").val()!="" && $("#prg").val().length ==3)
                                                        {
                                                            $("#enviar").val(valor);
                                                            $("#prg_id").val($("#prg").val());
                                                            $("#reportes").submit();
                                                        }
                                                        $("#myModal").remove();
                                                        return false;
                                                    })
                                                )
                                                .append( $("<button/>").addClass("btn btn-outline-secondary").text("Cancelar").click(function(){ $("#myModal").remove(); return false;}) )
                                            )
                                    )
                            )
                    );
                    return false;
                } else if (valor == "P-11") {
                    $("#divContenedorGeneralModal").html(
                        $("<div/>").css("display","block").attr("tabindex","-1").addClass("modal").attr("id","myModal")
                            .html(
                                $("<div/>").addClass("modal-dialog modal-lg")
                                    .html(
                                        $("<div/>").addClass("modal-content")
                                            .html(
                                                $("<div/>").addClass("modal-header")
                                                    .html( $("<h4/>").addClass("modal-title").text("Reporte Seguimiento Productos Intermedios con metas") )
                                                    .append( $("<button/>").addClass("btn-close").attr("type","button").text("").click(function(){ $("#myModal").remove(); return false;}) )
                                            ).append(
                                                $("<div/>").addClass("modal-body")
                                                    .html( $("<div/>").addClass("form-group")
                                                        .html( $("<label/>").attr("for","estado").text("Estado:") )
                                                        .append(
                                                            $("<select/>").attr("name","estado").attr("id","estado").addClass("form-control")
                                                                .append($("<option/>").attr("value","P-5").text("Habilitado"))
                                                                .append($("<option/>").attr("value","P-51").text("Inhabilitado"))
                                                                .append($("<option/>").attr("value","P-52").text("Ambos"))
                                                        )
                                                    )
                                            ).append( $("<div/>").addClass("modal-footer")
                                                .html( $("<button/>").attr("id","ok").addClass("btn btn-outline-success").text("Aceptar")
                                                    .click(function(){
                                                        let estado = $("#estado").val();
                                                        // Procesar la selección del estado aquí
                                                        $("#enviar").val(estado);
                                                        $("#reportes").submit();
                                                        $("#myModal").remove();
                                                        return false;
                                                    })
                                                )
                                                .append( $("<button/>").addClass("btn btn-outline-secondary").text("Cancelar").click(function(){ $("#myModal").remove(); return false;}) )
                                            )
                                    )
                            )
                    );
                    return false;
                } else {
                    console.log("Valor: " + valor);
                    $("#enviar").val(valor);
                }
            });
        ');
    }
}
new ControladorXml();
