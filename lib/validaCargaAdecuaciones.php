<?php
/**
 * Description of ControladorRegistroModificaciones
 *
 * @author ?
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2018
 * Comment: -----
 * Objective:
Realiza las validaciones sobre los registro de cada campo del excel
 * Regla de Negocio: -----
 * Permits: Permisos para carga masiva.
 * */
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_id() == PHP_SESSION_NONE && !isset($_SESSION[_S])) {
    session_start();
}
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../lib/scp/modelAdecuaciones.php';
require_once dirname(__FILE__) . '/../ent/scp/OrigenOperacion.php';

class validaCargaAdecuaciones extends WebResponse
{
    private $model;
    private $nivelJerarquico;
    private $perfil;
    private $grupo;
    private $ur;
    private $unidadEjecutora;
    private $usuario_id;
    private $adm_grupo;
    private $adm_ur;

    public function __construct()
    {
        parent::__construct();

        $this->model = new modelAdecuaciones();

        $this->nivelJerarquico = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id']
        : "";
        $this->perfil = isset($_SESSION[_S]['prf_id']) ? $_SESSION[_S]['prf_id']
        : "";
        $this->grupo = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id']
        : "";
        $this->ur = isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : "";
        $this->unidadEjecutora = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id']
        : "";
        $this->usuario_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id']
        : "";
        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo']
        : "";
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur']
        : "";
    }

    public function validaDatosExcel($data = '', $tem_carga = '')
    {
        $this->actualizaCargaMasiva(35, "INICIA VALIDACIÓN");
        $error = $registros = $errorPartida = $mov_idError = $campos_Error = '';
        $omo_idError = $mod_gpo_idError = $mod_ur_idError = $mod_idError = '';
        $importeError = $ueError = $gpoError = $urError = '';
        $fecha = date('d') . date('m') . date('Y') . date('H') . date('i') . date('s');
        $reiniR = true;
        $y = 2;
        $registro = ''; //contiene los registros a insertarse en tmp_DetalleModificacion_CargaMasiva
        $contadorRegistroP = 0; //contiene el numero de registros que $sqlP tiene registrados
        //columnas a insertar en la tabla temporal
        $insert = '
            INSERT INTO '. BD_TEMPORAL.'.`tem_DetalleModificacion`(omo_id,mod_gpo_id,mod_ur_id,mod_id,gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,pg_id,pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,ecf_id,tcp_id,mov_id,dmo_importe,dmo_ene,dmo_feb,dmo_mar,dmo_abr,dmo_may,dmo_jun,dmo_jul,dmo_ago,dmo_sep,dmo_oct,dmo_nov,dmo_dic,tem_carga,usr_id) VALUES ';

        $objJson = json_decode($data, true);

        for ($i = 0; $i < count($objJson['Data']); $i++) {

            $omo_id = $objJson['Data'][$i]["origen_folio"];
            $mod_gpo_id = $objJson['Data'][$i]["modificacion_grupo"];
            $mod_ur_id = $objJson['Data'][$i]["modificacion_ur"];
            $mod_id = $objJson['Data'][$i]["folio"];
            $gpo_id = $objJson['Data'][$i]["grupo"];
            $ur_id = $objJson['Data'][$i]["ur"];
            $ue_id = $objJson['Data'][$i]["ue"];
            $prg_id = $objJson['Data'][$i]["programa"];
            $spr_id = $objJson['Data'][$i]["subprograma"];
            $py_id = $objJson['Data'][$i]["proyecto"];
            $oa_id = $objJson['Data'][$i]["obra_actividad"];
            $pg_id = $objJson['Data'][$i]["partida_generica"];
            $pe_id = $objJson['Data'][$i]["partida_especifica"];
            $cff_id = $objJson['Data'][$i]["clasificacion_financiamiento"];
            $fte_id = $objJson['Data'][$i]["fuente_financiamiento"];
            $sft_id = $objJson['Data'][$i]["subfuente_financiamiento"];
            $ori_id = $objJson['Data'][$i]["origen_recurso"];
            $sor_id = $objJson['Data'][$i]["suborigen_recurso"];
            $cfi_id = $objJson['Data'][$i]["clave_financiamiento"];
            $cfi_anio = $objJson['Data'][$i]["financiamiento_anio"];
            $tcp_id = $objJson['Data'][$i]["tipo_compra"];
            $mov_id = $objJson['Data'][$i]["tipo_movimiento"];

            $importe = $objJson['Data'][$i]["importe"];
            $ene = $objJson['Data'][$i]["enero"];
            $feb = $objJson['Data'][$i]["febrero"];
            $mar = $objJson['Data'][$i]["marzo"];
            $abr = $objJson['Data'][$i]["abril"];
            $may = $objJson['Data'][$i]["mayo"];
            $jun = $objJson['Data'][$i]["junio"];
            $jul = $objJson['Data'][$i]["julio"];
            $ago = $objJson['Data'][$i]["agosto"];
            $sep = $objJson['Data'][$i]["septiembre"];
            $oct = $objJson['Data'][$i]["octubre"];
            $nov = $objJson['Data'][$i]["noviembre"];
            $dic = $objJson['Data'][$i]["diciembre"];

            $mod = new MaestroModificacion(array("omo_id" => $omo_id, "mod_gpo_id" => $mod_gpo_id,
                "mod_ur_id" => $mod_ur_id, "mod_id" => $mod_id));

            $enc_oop = new OrigenOperacion(array("enc_oop" => $mod->getOop()));
            $domo_id = $mod->getOmo_id();
            $dmod_gpo_id = $mod->getMod_gpo_id();
            $dmod_ur_id = $mod->getMod_ur_id();
            $dmod_id = $mod->getMod_id();

            if (strcmp($domo_id, $_POST["omo_id"]) != 0) {
                $omo_idError .= (!mb_strlen($omo_idError)) ? '' : ',';
                $omo_idError .= ' Origen folio en la fila:' . $y;
            }
            if (strcmp($dmod_gpo_id, $_POST["mod_gpo_id"]) != 0) {
                $mod_gpo_idError .= (!mb_strlen($mod_gpo_idError)) ? '' : ',';
                $mod_gpo_idError .= ' Grupo folio en la fila:' . $y;
            }
            if (strcmp($dmod_ur_id, $_POST["mod_ur_id"]) != 0) {
                $mod_ur_idError .= (!mb_strlen($mod_ur_idError)) ? '' : ',';
                $mod_ur_idError .= ' Ur folio en la fila:' . $y;
            }
            if (strcmp($mod_id, $_POST["mod_id"]) != 0) {
                $mod_idError .= (!mb_strlen($mod_idError)) ? '' : ',';
                $mod_idError .= ' folio en la fila:' . $y;
            }
            if (strcmp($this->nivelJerarquico, '2') <= 0 && $this->perfil != 'S') {
                if (strcmp($mod_gpo_id . $mod_ur_id, $this->grupo . $this->ur) != 0) {
                    $urError .= (!mb_strlen($urError)) ? '' : ',';
                    $urError .= 'ur en la fila:' . $y;
                }
            } else {
                if (strcmp($mod_gpo_id . $mod_ur_id,
                    $this->adm_grupo . $this->adm_ur) != 0) {
                    $urError .= (!mb_strlen($urError)) ? '' : ',';
                    $urError .= 'ur en la fila:' . $y;
                }
            }
            if (strcmp($this->nivelJerarquico, '1') == 0) {
                if (strcmp($ue_id, $this->unidadEjecutora) != 0) {
                    $ueError .= (!mb_strlen($ueError)) ? '' : ',';
                    $ueError .= 'ue en la fila:' . $y;
                }
            }
            if ($enc_oop->getTmd_id() == 'B' && strcmp($mod->getTipoMovimiento(),
                $mov_id) != 0) {
                $mov_idError .= (!mb_strlen($mov_idError)) ? '' : ',';
                $mov_idError .= ' Tipo movimiento en la fila:' . $i;
            }
            if (bccomp(Math::redondear($ene + $feb + $mar + $abr + $may + $jun + $jul
                + $ago + $sep + $oct + $nov + $dic, 2),
                Math::redondear($importe, 2), 2) != 0) {
                $importeError .= (!mb_strlen($importeError)) ? '' : ',';
                $importeError .= 'suma en la fila:' . $y;
            }
            if (!mb_strlen($error)) {
                $registro .= (!$reiniR) ? ', ' : '';
                $registro .= '
                    ("' . $omo_id . '",' . $mod_gpo_id . ',' . $mod_ur_id . ',' . $mod_id . ',' . $gpo_id . ',' . $ur_id . ',' . $ue_id . ',' . $prg_id . ',' . $spr_id . ',' . $py_id . ',' . $oa_id . ',0,' . $pg_id . ',' . $pe_id . ',"' . $cff_id . '","' . $fte_id . '","' . $sft_id . '","' . $ori_id . '","' . $sor_id . '","' . $cfi_id . '","' . $cfi_anio . '",0,"' . $tcp_id . '","' . $mov_id . '",' . $importe . ',' . $ene . ',' . $feb . ',' . $mar . ',' . $abr . ',' . $may . ',' . $jun . ',' . $jul . ',' . $ago . ',' . $sep . ',' . $oct . ',' . $nov . ',' . $dic . ',"' . $tem_carga . '",' . $this->usuario_id . ')
                ';

                $reiniR = false;
                $contadorRegistroP++;

                if ($contadorRegistroP > 3000) {
                    $this->conectar();
                    $this->setSql($insert . $registro);
                    $this->consultar();
                    $this->desconectar();
                    $registro = '';
                    $contadorRegistroP = 0;
                    $reiniR = true;

                    $this->conectar();
                    $this->setSql('
                        UPDATE '.BD_TEMPORAL.'.tem_DetalleModificacion a
                        JOIN enc_Programatico b
                        USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                        SET a.epg_id = b.epg_id
                        WHERE gpo_id = ? AND ur_id = ? and tem_carga=?;
                    ');
                    $parametros = [
                        $gpo_id,
                        $ur_id,
                        $tem_carga
                    ];
                    $this->consultar($parametros);

                    $this->setSql('
                        UPDATE '.BD_TEMPORAL.'.tem_DetalleModificacion a
                        JOIN enc_ClaveFinanciamiento b using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                        SET a.ecf_id = b.ecf_id
                        WHERE gpo_id = ? AND ur_id = ? and tem_carga= ?;
                    ');
                    $parametros = [
                        $gpo_id,
                        $ur_id,
                        $tem_carga
                    ];
                    $this->consultar($parametros);
                    $this->desconectar();
                } //fin if $contadorRegistroP
            } // fin if !mb_strlen($error)
            $y++;

            //para validar los identificadores de partida de cada tipo de folio
            if ($this->nivelJerarquico <= 2) {
                $this->conectar();
                $sql = '
                    select pg_id, pe_id, idp_id, clg_id
                    from cat_PartidaEspecifica where pg_id= ? and pe_id= ?
                ';
                $parametros = [
                    $pg_id,
                    $pe_id
                ];
                $this->setSql($sql);
                $this->consultar($parametros);
                $this->desconectar();

                switch ($enc_oop->getTfm_id()) {
                    case 'A': //servicios personales
                        $idp = '';
                        $clg_id = '';
                        while ($row = $this->extraer()) {
                            $idp .= ($idp != '') ? ", " : "";
                            $clg_id .= ($clg_id != '') ? ", " : "";
                            $idp .= $row['idp_id'];
                            $clg_id .= $row['clg_id'];
                            if ($clg_id == 1) {
                                $errorPartida .= '';
                            } else {
                                $errorPartida .= (!mb_strlen($errorPartida)) ? ''
                                : ',';
                                $errorPartida .= ' La partida: ' . $pg_id . ' - ' . $pe_id . ' no corresponde al tipo de folio. Fila:' . $i;
                            }
                        }
                        break;
                    case 'B': //gastos de operación
                        $idp = '';
                        $clg_id = '';
                        while ($row = $this->extraer()) {
                            $idp .= ($idp != '') ? ", " : "";
                            $idp .= $row['idp_id'];
                            $clg_id .= ($clg_id != '') ? ", " : "";
                            $clg_id .= $row['clg_id'];
                            if ($clg_id != 1 && $clg_id != 7 && $clg_id != 8) {
                                $errorPartida .= '';
                            } else {
                                $errorPartida .= (!mb_strlen($errorPartida)) ? ''
                                : ',';
                                $errorPartida .= ' La partida: ' . $pg_id . ' - ' . $pe_id . ' no corresponde al tipo de folio. Fila:' . $i;
                            }
                        }
                        break;
                    case 'C': // Inversión pública
                        $idp = '';
                        $clg_id = '';
                        switch ($enc_oop->getMmd_id() . $enc_oop->getTfm_id()) {
                            case 'IC':
                                while ($row = $this->extraer()) {
                                    $idp .= ($idp != '') ? ", " : "";
                                    $idp .= $row['idp_id'];
                                    $clg_id .= ($clg_id != '') ? ", " : "";
                                    $clg_id .= $row['clg_id'];
                                    if (($clg_id == 7 || $clg_id == 8) && $idp != 'V') {
                                        $errorPartida .= '';
                                    } else {
                                        $errorPartida .= (!mb_strlen($errorPartida))
                                        ? '' : ',';
                                        $errorPartida .= ' La partida: ' . $pg_id . ' - ' . $pe_id . ' no corresponde al tipo de folio. Fila:' . $y;
                                    }
                                }
                                break;
                            default:
                                while ($row = $this->extraer()) {
                                    $idp .= ($idp != '') ? ", " : "";
                                    $idp .= $row['idp_id'];
                                    $clg_id .= ($clg_id != '') ? ", " : "";
                                    $clg_id .= $row['clg_id'];
                                    if ($clg_id == 7 || $clg_id == 8) {
                                        $errorPartida .= '';
                                    } else {
                                        $errorPartida .= (!mb_strlen($errorPartida))
                                        ? '' : ',';
                                        $errorPartida .= ' La partida: ' . $pg_id . ' - ' . $pe_id . ' no corresponde al tipo de folio. Fila:' . $y;
                                    }
                                }
                                break;
                        } //fin switch
                        break;
                }
            } // if if niveljerarquico
        } //FIN FOR
        $error .= $gpoError . $urError . $ueError . $importeError . $omo_idError . $mod_gpo_idError . $mod_ur_idError . $mod_idError . $mov_idError . $errorPartida;

        if (!mb_strlen($error)) {

            if (mb_strlen($registro)) {
                $this->actualizaCargaMasiva(10, "CARGA EN TEMPORAL");
                $this->conectar();
                $this->setSql($insert . $registro);
                $this->consultar();
                $this->desconectar();

                $this->conectar();
                $this->setSql('
                    UPDATE '.BD_TEMPORAL.'.tem_DetalleModificacion a
                    JOIN enc_Programatico b
                    USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                    SET a.epg_id = b.epg_id
                    WHERE gpo_id = ? AND ur_id =? and tem_carga=?;
                ');
                $parametros = [
                    $gpo_id,
                    $ur_id,
                    $tem_carga
                ];
                $this->consultar($parametros);

                $this->setSql('
                    UPDATE '.BD_TEMPORAL.'.tem_DetalleModificacion a
                    JOIN enc_ClaveFinanciamiento b using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    SET a.ecf_id = b.ecf_id
                    WHERE gpo_id = ? AND ur_id = ? and tem_carga= ?;
                ');
                $parametros = [
                    $gpo_id,
                    $ur_id,
                    $tem_carga
                ];

                $this->consultar($parametros);
                $this->desconectar();
                $this->actualizaCargaMasiva(35, "TERMINA CARGA TEMPORAL");
            }
            return false;
        } else {
            $this->mixScript('console.log("' . $error . '");');
            $result = $this->mensaje_errores($omo_idError, $mod_gpo_idError,
                $mod_ur_idError, $mod_idError, $gpoError, $ueError,
                $importeError, $mov_idError, $errorPartida);
            return $result;
        }
    }
// fin validaDatosExcel

    public function mensaje_errores($omo_idError = '', $mod_gpo_idError = '',
        $mod_ur_idError = '', $mod_idError = '',
        $gpoError = '', $ueError = '',
        $importeError = '', $mov_idError = '',
        $errorPartida = '') {

        $error = '';
        if (mb_strlen($omo_idError)) {
            $error .= "No coincide el origen folio del archivo con el origen folio en el que intentas cargar \r\n";
        }
        if (mb_strlen($mod_gpo_idError)) {
            $error .= "No coincide la modificacion grupo del archivo con la modificacion grupo en el que intentas cargar \r\n";
        }
        if (mb_strlen($mod_ur_idError)) {
            $error .= "No coincide la modificacion ur del archivo con la modificacion ur en el que intentas cargar \r\n";
        }
        if (mb_strlen($mod_idError)) {
            $error .= "No coincide el folio del archivo con el folio en el que intentas cargar \r\n";
        }
        if (mb_strlen($gpoError)) {
            $error .= "No coincide la UR del archivo con tu UR \r\n";
        }
        if (mb_strlen($ueError)) {
            $error .= "No coincide la UE del archivo con tu UR \r\n";
        }
        if (mb_strlen($importeError)) {
            $error .= "La suma calendarizada no coincide con el importe \r\n";
        }
        if (mb_strlen($mov_idError)) {
            $error .= "El tipo de movimiento no coincide con el capturado en el folio \r\n";
        }
        if (mb_strlen($errorPartida)) {
            $error .= "Las partidas no corresponden al tipo de folio \r\n";
        }
        return $error;
    }

    public function validaDatosCA($casoB, $mod, $tem_carga)
    {
        $error = '';
        $datos = array(0, "");
        $this->actualizaCargaMasiva(21, "INICIA CLAVE PRESUPUESTAL");
        $datos = $this->model->getValidaFolio($mod, true, $tem_carga);

        if ($casoB == 1) {
            //Revisando que las claves no esten siendo utilizadas en otro folio no autorizado
            $sql = "
                SELECT t.*
                FROM (
                    SELECT d.gpo_id, d.ur_id, ue_id, epg_id, pg_id, pe_id, d.ecf_id, d.tcp_id
                    FROM scp_MaestroModificacion m
                        JOIN scp_DetalleModificacion d using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                    WHERE m.mod_gpo_id = :gpo_id AND m.mod_ur_id = :ur_id
                    AND emo_id not in (14,15,16)
                    group by gpo_id,ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id
                ) AS e
                JOIN (
                    SELECT
                        gpo_id, ur_id, ue_id, prg_id,spr_id,py_id,oa_id,epg_id, pg_id, pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,ecf_id, tcp_id
                    FROM ".BD_TEMPORAL.".tem_DetalleModificacion
                    WHERE omo_id = :omo_id AND mod_gpo_id = :gpo_id AND mod_ur_id = :ur_id AND mod_id = :mod_id AND tem_carga= :tem_carga
                ) AS t using( gpo_id,ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                order by gpo_id,ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id;
            ";
            $parametros = [
                ':gpo_id' => $this->prm->mod_gpo_id,
                ':ur_id' => $this->prm->mod_ur_id,
                ':omo_id' => $this->prm->omo_id,
                ':mod_id' => $this->prm->mod_id,
                ':tem_carga' => $tem_carga
            ];
            $this->setSql($sql);
            $this->conectar();
            $this->consultar($parametros);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $x = '';
                while ($row = $this->extraer()) {

                    $x = $row['gpo_id'] . $row['ur_id'] . $row['ue_id'] . $row['prg_id'] . $row['spr_id'] . $row['py_id'] . $row['oa_id'] . $row['pg_id'] . $row['pe_id'] . $row['cff_id'] . $row['fte_id'] . $row['sft_id'] . $row['ori_id'] . $row['sor_id'] . $row['cfi_id'] . $row['cfi_anio'];
                    array_push($datos,
                        array("numero" => 46, "mensaje" => " La clave : $x ya está siendo usada en otro Folio, favor de eliminar de la carga Masiva. "));
                }
            }
        } // fin if casoB

        $sql = "
            SELECT gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id, b.ecf_id, pg_id,pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,tcp_id
            FROM scp_MaestroModificacion a
            INNER JOIN ".BD_TEMPORAL.".tem_DetalleModificacion b using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
            WHERE omo_id = ? and mod_gpo_id = ? and mod_ur_id = ? and mod_id = ? and b.tem_carga=?;
        ";
        $parametros = [
            $this->prm->omo_id,
            $this->prm->mod_gpo_id,
            $this->prm->mod_ur_id,
            $this->prm->mod_id,
            $tem_carga
        ];
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($parametros);
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            $clave = '';
            while ($row = $this->extraer()) {
                $this->prm->gpo_id = $row['gpo_id'];
                $this->prm->ur_id = $row['ur_id'];
                $this->prm->ue_id = $row['ue_id'];
                $this->prm->epg_id = (empty($row['epg_id']) ? 0 : $row['epg_id']);
                $this->prm->ecf_id = $row['ecf_id'];
                $this->prm->pg_id = $row['pg_id'];
                $this->prm->pe_id = $row['pe_id'];
                $this->prm->tcp_id = $row['tcp_id'];

                $pres = new AccesoDatos();
                $sql2 = "
                    SELECT pre_claveCompleta
                    FROM presupuesto
                    INNER JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    WHERE gpo_id = :gpo_id and ur_id = :ur_id and ue_id= :ue_id
                    " . (isset($row['epg_id']) && $row['epg_id'] != "" ? "" : 'and epg_id=:epg_id'). " and ecf_id= :ecf_id and pg_id= :pg_id
                    and pe_id= :pe_id and tcp_id= :tcp_id;
                ";
                if(isset($row['epg_id']) && $row['epg_id'] != ''){
                    $parametros['epg_id'] = $row['epg_id'];
                } else {
                    $parametros['epg_id'] = '';
                }
                $parametros = [
                    ':gpo_id' => $this->prm->gpo_id,
                    ':ur_id' => $row['ur_id'],
                    ':ue_id' => $row['ue_id'],
                    ':ecf_id' => $row['ecf_id'],
                    ':pg_id' => $row['pg_id'],
                    ':pe_id' => $row['pe_id'],
                    ':tcp_id' => $row['tcp_id']
                ];
                $pres->conectar();
                $pres->setSql($sql2);
                $pres->consultar($parametros);
                $pres->desconectar();

                if ($this->prm->epg_id == 0) {
                    $error .= "Existe error en sus datos \r\n";
                } else if ($pres->getNumeroRegistros() <= 0) {
                    $clave = $row['gpo_id'] . str_pad($row['ur_id'], 2, '0',
                        STR_PAD_LEFT) . str_pad($row['ue_id'], 3, '0',
                        STR_PAD_LEFT) . ' - ' . str_pad($row['prg_id'], 3, '0',
                        STR_PAD_LEFT) . str_pad($row['spr_id'], 2, '0',
                        STR_PAD_LEFT) . str_pad($row['py_id'], 3, '0',
                        STR_PAD_LEFT) . str_pad($row['oa_id'], 3, '0',
                        STR_PAD_LEFT) . ' - ' . $row['pg_id'] . str_pad($row['pe_id'],
                        3, '0', STR_PAD_LEFT) . $row['cff_id'] . $row['fte_id'] . $row['sft_id'] . $row['ori_id'] . $row['sor_id'] . str_pad($row['cfi_id'],
                        2, '0', STR_PAD_LEFT) . str_pad($row['cfi_anio'], 2,
                        '0', STR_PAD_LEFT) . ' - ' . $row['tcp_id'];
                    array_push($datos,
                        array("numero" => 47, "mensaje" => " La clave presupuestal: $clave no existe. "));
                }
            }
        } // fin if numeroRegistros

        $this->actualizaCargaMasiva(22, "TERMINA CLAVE PRESUPUESTAL");

        if (mb_strlen($error) || count($datos) > 0) {
            if (count($datos) > 0) {

                foreach ($datos as $key => $valor) {
                    $error .= "Error " . $valor['numero'] . ". " . $valor["mensaje"] . "\r\n";
                }
            } else {
                if (mb_strlen($error)) {
                    $error .= $datos[1]['numero'] . ". " . $datos[1]['mensaje'] . " \r\n";
                } else {
                    $error .= "Error " . $datos[1]['numero'] . ". " . $datos[1]['mensaje'] . " \r\n";
                }
            }
            return $error;
        } else {
            return 'success';
        }
    }

    private function borrarTemporales($omo_id, $mod_gpo_id, $mod_ur_id, $mod_id,
        $tem_carga) {
        $this->conectar();
        $this->setSql('
            DELETE FROM '.BD_TEMPORAL.'.`tem_DetalleModificacion` 
            WHERE  omo_id= ? and  mod_gpo_id= ? and  mod_ur_id= ?
            and mod_id= ? and tem_carga= ?;
        ');
        $parametros = [
            $omo_id,
            $mod_gpo_id,
            $mod_ur_id,
            $mod_id,
            $tem_carga
        ];
        $this->consultar($parametros);
        $this->desconectar();
    }

    public function actualizaCargaMasiva($estado, $observacion)
    {
        $conex = new AccesoDatos();
        $conex->conectar();
        $sql = '
            SET @usr_id =?,
            @usr_ip = INET_ATON(?),
            @bit_observacion=?;
        ';
        $parametrosbit = [
            $this->usuario_id,
            $_SESSION[_S]['tem_usr_ip'],
            $observacion
        ];
        $conex->setSql($sql);
        $conex->consultar($parametrosbit);
        $sql = '
            UPDATE cat_CargaMasiva
            SET ecr_id = ' . $estado . '
            WHERE gpo_id = ? AND ur_id = ?
            AND tcr_id = ?
            AND tem_Carga = ?;
        ';
        $parametros = [
            $this->adm_grupo,
            $this->adm_ur,
            3,
            $_SESSION[_S]['tem_carga']
        ];
        $conex->setSql($sql);
        $conex->consultar($parametros);
        $conex->desconectar();
    }
}
