<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . "/../lib/constantes.php";
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/ClasificacionAdministrativaB.php';
/**
 * Clasificacion de Financiamiento
 *
 * Esta clase permite mostrar/seleccionar la clave de financiamiento
 * y almacenar en una variable sesion la clave seleccionada o mostrada
 *
 * @author L.I. Anuar López Morgan <anuar.lopez@finanzasoaxaca.gob.mx>
 * @version: 1
 */

class ClasificacionFinanciamiento extends WebResponse
{
    public function __construct()
    {
        parent::__construct();

        if (isset($this->prm->metodoCveFin)) {
            $metodo = (isset($this->prm->metodoCveFin)) ? $this->prm->metodoCveFin : '';
            switch ($metodo) {
                case 'cargaComboCveFin':
                    $this->cargaComboCveFin();
                    break;
                case 'cargaCff':
                    $this->cargaCff();
                    break;
                case 'cargaFte':
                    $this->cargaFte();
                    break;
                case 'cambioCff':
                    $this->cambioCff();
                    break;
                case 'cambioFte':
                    $this->cambioFte();
                    break;
                case 'cambioSft':
                    $this->cambioSft();
                    break;
                case 'cambioOri':
                    $this->cambioOri();
                    break;
                case 'cambioSor':
                    $this->cambioSor();
                    break;
                case 'cambioCveFin':
                    $this->cambioCveFin();
                    break;
                case 'selEncProgFin':
                    $this->selEncProgFin();
                    break;
                case 'muestraCveFin':
                    $this->muestraCveFin();
                    break;
                case 'validaCveFinModal':
                    $this->validaCveFinModal();
                    break;
            }
            $this->returnJson();
        }
    }

    /**
     * Coloca la estructura y template's para seleccionar o mostrar la clave de financiamiento
     *
     * @param object      $p La funcion puede o no tener este parametro.
     * @param boolean     $p->required La funcion puede o no tener este parametro.
     * @return string    Devuelve en una cadena de caracteres el html donde muestra la clasificacion de financiamiento
     */
    public function seleccionaCombosCveFin()
    {
        $this->cargaFte();
        $this->cargaSft();
        $this->cargaOri();
        $this->cargaSor();
        $this->cargaCvefin();
    }

    private function cambioCff()
    {
        $_SESSION[_S]['cvefin']->cff = ($this->prm->cff_id != '') ? $this->prm->cff_id : null;
        $this->cargaFte();
    }

    private function cambioFte()
    {
        $_SESSION[_S]['cvefin']->fte = ($this->prm->fte_id != '') ? $this->prm->fte_id : null;
        $this->cargaSft();
    }

    private function cambioSft()
    {
        $_SESSION[_S]['cvefin']->sft = ($this->prm->sft_id != '') ? $this->prm->sft_id : null;
        $this->cargaOri();
    }

    private function cambioOri()
    {
        $_SESSION[_S]['cvefin']->ori = ($this->prm->ori_id != '') ? $this->prm->ori_id : null;
        $this->cargaSor();
    }

    private function cambioSor()
    {
        $_SESSION[_S]['cvefin']->sor = ($this->prm->sor_id != '') ? $this->prm->sor_id : null;
        $this->cargaCvefin();
    }

    private function cambioCveFin()
    {
        $_SESSION[_S]['cvefin']->cfi = (!empty($this->prm->cfi_id)) ? $this->prm->cfi_id : ((!empty($_SESSION[_S]['cvefin']->cfi)) ? $_SESSION[_S]['cvefin']->cfi : null);
        $_SESSION[_S]['cvefin']->anio = (!empty($this->prm->cfi_anio)) ? $this->prm->cfi_anio : ((!empty($_SESSION[_S]['cvefin']->anio)) ? $_SESSION[_S]['cvefin']->anio : null);

        if (!is_null($_SESSION[_S]['cvefin']->anio)) {
            $_SESSION[_S]['cvefin']->cveFin = $_SESSION[_S]['cvefin']->cff . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            $this->conectar();
            $this->setSql('
                SELECT cfi_nombre,ecf_id
                FROM enc_ClaveFinanciamiento
                JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
                USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id= :oriId AND sor_id = :sorId AND cfi_id = :cfiId AND cfi_anio = :cfiAnio;
            ');
            $this->consultar([
                ':cffId' => $_SESSION[_S]['cvefin']->cff,
                ':fteId' => $_SESSION[_S]['cvefin']->fte,
                ':sftId' => $_SESSION[_S]['cvefin']->sft,
                ':oriId' => $_SESSION[_S]['cvefin']->ori,
                ':sorId' => $_SESSION[_S]['cvefin']->sor,
                ':cfiId' => $_SESSION[_S]['cvefin']->cfi,
                ':cfiAnio' => $_SESSION[_S]['cvefin']->anio]);
            $this->desconectar();
            $cvfin = $this->extraer();
            $_SESSION[_S]['cvefin']->nombre = $cvfin['cfi_nombre'];
            $_SESSION[_S]['cvefin']->ecf = $cvfin['ecf_id'];

            $this->mixScript('desencadenaCveFin();');
        } else {
            $_SESSION[_S]['cvefin']->cveFin = null;
        }
    }

    private function cargaCff()
    {
        $combo = new stdClass();
        $combo->id = 'cff_id';
        if (!is_null($_SESSION[_S]['cvefin']->cff)) {
            $combo->selecciona = $_SESSION[_S]['cvefin']->cff;
        }

        $_SESSION[_S]['cvefin']->filtro = (isset($this->prm->filtro)) ? $this->prm->filtro : ((isset($_SESSION[_S]['cvefin']->filtro)) ? $_SESSION[_S]['cvefin']->filtro : '');

        $combo->sql = '
            SELECT DISTINCT cff_id, cff_nombre
            FROM ' . BD_SINPRES . '.cat_ClasificadorFuenteFinanciamiento;
        ';
        if (!empty($_SESSION[_S]['cvefin']->filtro)) {
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT cff_id, cff_nombre
                        FROM enc_ClaveFinanciamiento INNER JOIN ' . BD_SINPRES . '.cat_ClasificadorFuenteFinanciamiento USING(cff_id);
                    ';
                    break;
                case 'poa':
                    $epg_id = ClasificacionAdministrativa::getEpg();
                    $combo->sql = '
                        SELECT DISTINCT cff_id, cff_nombre
                        FROM poa_OrigenRecurso
                        JOIN enc_ClaveFinanciamiento USING(ecf_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_ClasificadorFuenteFinanciamiento USING(cff_id)';
                    break;
            }
        }

        $and = '';
        $params = [];

        if ($_SESSION[_S]['cvefin']->lectura) {
            $and = ' AND cff_id = :cffId ';
            $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
        }

        $combo->sql .= ' WHERE';
        switch ($_SESSION[_S]['cvefin']->filtro) {
            case 'poa':
                $combo->sql .= ' gpo_id = :clasAdmGpoId AND ur_id = :clasAdmUrId AND epg_id = :epgId ';
                $params[':clasAdmGpoId'] = $_SESSION[_S]['clasAdm-gpo'];
                $params[':clasAdmUrId'] = $_SESSION[_S]['clasAdm-ur'];
                $params[':epgId'] = $epg_id;
                break;
        }
        $combo->sql .= $and . ";";
        $combo->params = $params;

        $combo->detonar = false;

        $this->recargaCombo($combo);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['cvefin']->cff = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['cvefin']->cff = null;
        }
        $this->cargaFte();
    }

    private function cargaFte()
    {
        $combo = new stdClass();
        $combo->id = 'fte_id';
        if (!is_null($_SESSION[_S]['cvefin']->fte)) {
            $combo->selecciona = $_SESSION[_S]['cvefin']->fte;
        }

        $_SESSION[_S]['cvefin']->filtro = (isset($this->prm->filtro)) ? $this->prm->filtro : ((isset($_SESSION[_S]['cvefin']->filtro)) ? $_SESSION[_S]['cvefin']->filtro : '');

        $combo->sql = '
            SELECT DISTINCT fte_id,fte_nombre
            FROM ' . BD_SINPRES . '.cat_Fuente' . APP_EJERCICIO . '
            JOIN enc_ClaveFinanciamiento USING(cff_id, fte_id)
        ';
        if (!empty($_SESSION[_S]['cvefin']->filtro)) {
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT fte_id, fte_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_Fuente' . APP_EJERCICIO . ' USING(cff_id, fte_id)
                    ';
                    break;
                case 'poa':
                    $epg_id = ClasificacionAdministrativa::getEpg();
                    $combo->sql = '
                        SELECT DISTINCT fte_id, fte_nombre
                        FROM poa_OrigenRecurso
                        JOIN enc_ClaveFinanciamiento USING(ecf_id)
                        JOIN ' . BD_SINPRES . '.cat_Fuente' . APP_EJERCICIO . ' USING(cff_id, fte_id)
                    ';
                    break;
            }
        }

        $and = '';
        if ($_SESSION[_S]['cvefin']->lectura) {
            $and = ' AND fte_id="' . $_SESSION[_S]['cvefin']->fte . '" ';
        }

        $combo->sql .= ' WHERE cff_id ="' . $_SESSION[_S]['cvefin']->cff . '" ';
        switch ($_SESSION[_S]['cvefin']->filtro) {
            case 'poa':
                $combo->sql .= 'AND gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '" and epg_id="' . $epg_id . '" ';
                break;
        }
        $combo->sql .= $and . ";";

        $combo->detonar = false;

        $this->recargaCombo($combo);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['cvefin']->fte = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['cvefin']->fte = null;
        }
        $this->cargaSft();
    }

    private function cargaSft()
    {
        $combo = new stdClass();
        $combo->id = 'sft_id';
        if (!is_null($_SESSION[_S]['cvefin']->sft)) {
            $combo->selecciona = $_SESSION[_S]['cvefin']->sft;
        }

        $filtroSql = '';
        $combo->sql = '
            SELECT DISTINCT sft_id,sft_nombre
            FROM ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . '
            INNER JOIN enc_ClaveFinanciamiento USING(cff_id,fte_id,sft_id
        ';
        if (!empty($_SESSION[_S]['cvefin']->filtro)) {
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    $epg_id = ClasificacionAdministrativa::getEpg();
                    $combo->sql = '
                        SELECT DISTINCT sft_id,sft_nombre
                        FROM poa_OrigenRecurso
                        JOIN enc_ClaveFinanciamiento using(ecf_id)
                        JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' using(cff_id, fte_id,sft_id) ';
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT sft_id,sft_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' using(cff_id, fte_id,sft_id)
                    ';
                    $filtroSql = ' AND ecf_poa = 1 AND sts_id = 1';
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN enc_ClaveFinanciamiento
                        USING (cff_id,fte_id,sft_id)
                        JOIN presupuesto
                        USING (ecf_id)
                    ';
                    break;
                default:
                    $combo->sql = '
                        SELECT DISTINCT sft_id,sft_nombre
                        FROM ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . '
                    ';
                    break;
            }
        }

        $and = '';
        $params = [];
        if ($_SESSION[_S]['cvefin']->lectura) {
            $and = ' AND sft_id = :sftId ';
            $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
        }

        $combo->sql .= ' WHERE cff_id = :cffId AND fte_id = :fteId ' . $filtroSql;
        $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
        $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;

        switch ($_SESSION[_S]['cvefin']->filtro) {
            case 'poa':
                $combo->sql .= ' AND gpo_id = :clasAdmGpoId AND ur_id = :clasAdmUrId AND epg_id = :epgId ';
                $params[':clasAdmGpoId'] = $_SESSION[_S]['clasAdm-gpo'];
                $params[':clasAdmUrId'] = $_SESSION[_S]['clasAdm-ur'];
                $params[':epgId'] = $epg_id;
                break;
        }
        $combo->sql .= $and . ";";
        $combo->params = $params;

        $combo->detonar = false;
        $this->recargaCombo($combo);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['cvefin']->sft = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['cvefin']->sft = null;
        }
        $this->cargaOri();
    }

    private function cargaOri()
    {
        $combo = new stdClass();
        $combo->id = 'ori_id';
        if (!is_null($_SESSION[_S]['cvefin']->ori)) {
            $combo->selecciona = $_SESSION[_S]['cvefin']->ori;
        }

        $combo->sql = '
            SELECT DISTINCT ori_id,ori_nombre
            FROM ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . '
            JOIN enc_ClaveFinanciamiento USING(cff_id, fte_id,sft_id,ori_id)
        ';

        $and = '';
        $params = [];

        if (!is_null($_SESSION[_S]['cvefin']->filtro)) {
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    $epg_id = ClasificacionAdministrativa::getEpg();
                    $combo->sql = '
                        SELECT DISTINCT ori_id,ori_nombre
                        FROM poa_OrigenRecurso
                        JOIN enc_ClaveFinanciamiento USING(ecf_id)
                        JOIN ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id)
                    ';
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT ori_id,ori_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id,sft_id,ori_id)
                    ';
                    $and = ' AND ecf_poa = 1 AND sts_id = 1';
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN enc_ClaveFinanciamiento
                        USING(cff_id,fte_id,sft_id,ori_id)
                        JOIN presupuesto
                        USING(ecf_id)
                    ';
                    break;
                default:
                    $combo->sql = '
                        SELECT DISTINCT ori_id,ori_nombre
                        FROM ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . ' JOIN enc_ClaveFinanciamiento USING(cff_id, fte_id,sft_id,ori_id)
                    ';
                    break;
            }
        }
        if ($_SESSION[_S]['cvefin']->lectura) {
            $and .= ' AND ori_id = :oriId ';
            $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;
        }

        $combo->sql .= ' WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId ';
        $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
        $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
        $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;

        switch ($_SESSION[_S]['cvefin']->filtro) {
            case 'poa':
                $combo->sql .= ' AND gpo_id = :clasAdmGpoId AND ur_id = :clasAdmUrId AND epg_id = :epgId ';
                $params[':clasAdmGpoId'] = $_SESSION[_S]['clasAdm-gpo'];
                $params[':clasAdmUrId'] = $_SESSION[_S]['clasAdm-ur'];
                $params[':epgId'] = $epg_id;
                break;
        }

        $combo->sql .= $and . ";";
        $combo->params = $params;

        $combo->detonar = false;
        $this->recargaCombo($combo);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['cvefin']->ori = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['cvefin']->ori = null;
        }

        $this->cargaSor();
    }

    private function cargaSor()
    {
        $combo = new stdClass();
        $combo->id = 'sor_id';
        if (!is_null($_SESSION[_S]['cvefin']->sor)) {
            $combo->selecciona = $_SESSION[_S]['cvefin']->sor;
        }

        $combo->sql = '
            SELECT DISTINCT sor_id, sor_nombre
            FROM ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' enc_ClaveFinanciamiento
            USING (cff_id, fte_id , sft_id , ori_id , sor_id)
        ';

        $and = '';
        $params = [];

        if (!empty($_SESSION[_S]['cvefin']->filtro)) {
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    $epg_id = ClasificacionAdministrativa::getEpg();
                    $combo->sql = '
                        SELECT DISTINCT sor_id,sor_nombre FROM poa_OrigenRecurso
                        JOIN enc_ClaveFinanciamiento using(ecf_id)
                        JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id , sft_id , ori_id , sor_id) ';
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT sor_id,sor_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id , sft_id , ori_id , sor_id) ';
                    $and = ' AND ecf_poa = 1 AND sts_id = 1';
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN enc_ClaveFinanciamiento
                        USING (cff_id, fte_id,sft_id,ori_id,sor_id)
                        JOIN presupuesto
                        USING (ecf_id)
                    ';
                    break;
            }
        }

        if ($_SESSION[_S]['cvefin']->lectura) {
            $and .= ' AND sor_id = :sorId ';
            $params[':sorId'] = $_SESSION[_S]['cvefin']->sor;
        }

        $combo->sql .= ' WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId ';
        $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
        $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
        $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
        $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;

        switch ($_SESSION[_S]['cvefin']->filtro) {
            case 'poa':
                $combo->sql .= ' AND gpo_id = :clasAdmGpoId AND ur_id = :clasAdmUrId AND epg_id = :epgId ';
                $params[':clasAdmGpoId'] = $_SESSION[_S]['clasAdm-gpo'];
                $params[':clasAdmUrId'] = $_SESSION[_S]['clasAdm-ur'];
                $params[':epgId'] = $epg_id;
                break;
        }

        $combo->sql .= $and . ";";
        $combo->params = $params;

        $combo->detonar = false;
        $this->recargaCombo($combo);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['cvefin']->sor = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['cvefin']->sor = null;
        }

        $this->cargaCvefin();
    }

    private function cargaCvefin()
    {
        $combo = new stdClass();
        $combo->id = 'cfi_id';
        $combo->camposVal = 'cfi_id,cfi_anio';
        $combo->camposText = 'cfi_nombre';
        $combo->comoAtr = 'cfi_id,cfi_anio';

        if (!is_null($_SESSION[_S]['cvefin']->cfi) && !is_null($_SESSION[_S]['cvefin']->anio)) {
            $combo->selecciona = $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
        }

        $combo->sql = '
            SELECT DISTINCT cfi_id,cfi_anio,cfi_nombre
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            JOIN enc_ClaveFinanciamiento USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)';

        $and = '';
        $params = [];

        if (!empty($_SESSION[_S]['cvefin']->filtro)) {
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    $epg_id = ClasificacionAdministrativa::getEpg();
                    $combo->sql = '
                        SELECT DISTINCT cfi_id,cfi_anio,cfi_nombre
                        FROM poa_OrigenRecurso join enc_ClaveFinanciamiento using(ecf_id)
                        JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    ';
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT cfi_id,cfi_anio,cfi_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    ';
                    $and = ' AND ecf_poa = 1 AND sts_id = 1';
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN presupuesto USING (ecf_id)
                    ';
                    break;
            }
        }

        if ($_SESSION[_S]['cvefin']->lectura) {
            $and .= ' AND cfi_id = :cfiId AND cfi_anio = :cfiAnio';
            $params[':cfiId'] = $_SESSION[_S]['cvefin']->cfi;
            $params[':cfiAnio'] = $_SESSION[_S]['cvefin']->anio;
        }

        $combo->sql .= 'WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId AND sor_id = :sorId ';
        $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
        $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
        $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
        $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;
        $params[':sorId'] = $_SESSION[_S]['cvefin']->sor;
        $combo->sql .= 'AND sts_id = 1';

        switch ($_SESSION[_S]['cvefin']->filtro) {
            case 'poa':
                $combo->sql .= ' AND gpo_id = :clasAdmGpoId AND ur_id = :clasAdmUrId AND epg_id = :epgId ';
                $params[':clasAdmGpoId'] = $_SESSION[_S]['clasAdm-gpo'];
                $params[':clasAdmUrId'] = $_SESSION[_S]['clasAdm-ur'];
                $params[':epgId'] = $epg_id;
                break;
            case 'poa2':
                $combo->sql .= ' AND ecf_poa = 1 AND sts_id = 1';
                break;
        }

        $combo->sql .= $and . ";";
        $combo->params = $params;
        $combo->detonar = false;

        $this->recargaCombo($combo);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['cvefin']->cfi = substr($this->comboValorUnico, 0, -2);
            $_SESSION[_S]['cvefin']->anio = substr($this->comboValorUnico, -2, 2);
            $this->cambioCveFin();
        } else {
            $_SESSION[_S]['cvefin']->cfi = null;
            $_SESSION[_S]['cvefin']->anio = null;
        }
    }

    public static function getEcf()
    {
        if (isset($_SESSION[_S]['cvefin'])) {
            if (is_null($_SESSION[_S]['cvefin']->cff) || is_null($_SESSION[_S]['cvefin']->fte) || is_null($_SESSION[_S]['cvefin']->sft) || is_null($_SESSION[_S]['cvefin']->ori) || is_null($_SESSION[_S]['cvefin']->sor) || is_null($_SESSION[_S]['cvefin']->cfi) || is_null($_SESSION[_S]['cvefin']->anio)) {
                return 0;
            } else {
                $c = new AccesoDatos();
                $c->setSql('SELECT ecf_id FROM enc_ClaveFinanciamiento WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId AND sor_id = :sorId AND cfi_id = :cfiId AND cfi_anio = :cfiAnio;');
                $c->conectar();
                $c->consultar([
                    ':cffId' => $_SESSION[_S]['cvefin']->cff,
                    ':fteId' => $_SESSION[_S]['cvefin']->fte,
                    ':sftId' => $_SESSION[_S]['cvefin']->sft,
                    ':oriId' => $_SESSION[_S]['cvefin']->ori,
                    ':sorId' => $_SESSION[_S]['cvefin']->sor,
                    ':cfiId' => $_SESSION[_S]['cvefin']->cfi,
                    ':cfiAnio' => $_SESSION[_S]['cvefin']->anio,
                ]);
                $c->desconectar();
                $ecf = $c->extraer();
                return $ecf['ecf_id'];
            }
        }
    }

    public static function ponClasFinanciamiento()
    {
        $cve_fin = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        if (!isset($_SESSION[_S]['cvefin'])) {
            $_SESSION[_S]['cvefin'] = new stdClass();
        }

        $_SESSION[_S]['cvefin']->cff = null;
        $_SESSION[_S]['cvefin']->fte = null;
        $_SESSION[_S]['cvefin']->sft = null;
        $_SESSION[_S]['cvefin']->ori = null;
        $_SESSION[_S]['cvefin']->sor = null;
        $_SESSION[_S]['cvefin']->cfi = null;
        $_SESSION[_S]['cvefin']->anio = null;
        $_SESSION[_S]['cvefin']->filtro = (isset($cve_fin->filtro) && $cve_fin->filtro != '') ? $cve_fin->filtro : ((isset($_SESSION[_S]['cvefin']->filtro) && !is_null($_SESSION[_S]['cvefin']->filtro)) ? $_SESSION[_S]['cvefin']->filtro : null);

        $cf = new WebResponse();
        $preseleccion = '';
        $_SESSION[_S]['cvefin']->lectura = false;

        if (isset($cve_fin->ecf_id) && $cve_fin->ecf_id != "") {
            $cf->setSql('
                SELECT cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
                FROM enc_ClaveFinanciamiento
                WHERE ecf_id = :ecfId');
            $cf->conectar();
            $cf->consultar([':ecfId' => $cve_fin->ecf_id]);
            $cf->desconectar();
            if ($cf->getNumeroRegistros() > 0) {
                $r = $cf->extraer();
                $_SESSION[_S]['cvefin']->cff = $r['cff_id'];
                $_SESSION[_S]['cvefin']->fte = $r['fte_id'];
                $_SESSION[_S]['cvefin']->sft = $r['sft_id'];
                $_SESSION[_S]['cvefin']->ori = $r['ori_id'];
                $_SESSION[_S]['cvefin']->sor = $r['sor_id'];
                $_SESSION[_S]['cvefin']->cfi = $r['cfi_id'];
                $_SESSION[_S]['cvefin']->anio = $r['cfi_anio'];
            }
            if (isset($cve_fin->lectura) && $cve_fin->lectura === true) {
                $_SESSION[_S]['cvefin']->lectura = true;
            }
        }

        $whereSql = '';
        $epg = "";
        $params = [];

        $combo = new stdClass();
        $combo->sql = 'SELECT DISTINCT cff_id, cff_nombre FROM ' . BD_SINPRES . '.cat_ClasificadorFuenteFinanciamiento;';

        switch ($_SESSION[_S]['cvefin']->filtro) {
            case 'poa':
                if (ClasificacionAdministrativa::getEpg() != '') {
                    $epg_id = ClasificacionAdministrativa::getEpg();
                    $epg = 'epg_id = :epgId';
                    $params[':epgId'] = $epg_id;
                }

                $epg .= ($epg != '') ? ' AND ' : '';

                $epg .= ' gpo_id = :gpoId AND ur_id = :urId';
                $params[':gpoId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
                $params[':urId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

                $combo->sql = '
                    SELECT DISTINCT cff_id, cff_nombre
                    FROM  poa_OrigenRecurso
                        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_ClasificadorFuenteFinanciamiento USING(cff_id)
                    ';
                break;
            case 'poa2':
                $combo->sql = 'SELECT DISTINCT  cff_id, cff_nombre  FROM enc_ClaveFinanciamiento JOIN ' . BD_SINPRES . '.cat_ClasificadorFuenteFinanciamiento USING(cff_id)';
                $whereSql = 'ecf_poa = 1 AND sts_id = 1';
                break;
            case 'presupuesto':
                $combo->sql .= '
                    JOIN enc_ClaveFinanciamiento USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                    JOIN presupuesto USING(ecf_id)
                ';
                break;
            default:
                break;
        }

        //parametros para el mostrar la Fuente
        $combo->id = 'cff';
        $combo->label = 'Clasificador';
        $combo->enDiv = true;
        $combo->required = true;
        $combo->detonar = false;
        $combo->form = (isset($cve_fin->form)) ? $cve_fin->form : 'cveFinForm';

        if (isset($cve_fin->horizontal)) {
            $combo->horizontal = $cve_fin->horizontal;
        }

        $combo->mensaje = 'Seleccionar Clasificador de Financiamiento ';
        if (!is_null($_SESSION[_S]['cvefin']->cff)) {

            $combo->selecciona = $_SESSION[_S]['cvefin']->cff;

            if ($_SESSION[_S]['cvefin']->lectura === true) {
                $whereSql .= 'cff_id = :cffId';
                $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
            }
        }

        $whereSql = ($whereSql != '') ? 'WHERE ' . $whereSql . (($epg_id != '') ? ' AND ' . $epg : '') : (($epg != '') ? 'WHERE ' . $epg : '');
        $combo->errormessage = 'Seleccione El Clasificador de Financiamiento';
        $combo->sql .= $whereSql . ' ORDER BY cff_id;';
        $combo->params = $params;
        $combo->change = '
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"cambioCff",
                cff_id:$(this).val()
            });';

        $cff = $cf->ponCombo($combo);
        if (!is_null($cf->comboValorUnico)) {
            $_SESSION[_S]['cvefin']->cff = $cf->comboValorUnico;
        }

        //parametros para mostrar la Fuente
        $combo = new stdClass();
        $combo->id = 'fte_id';
        $combo->label = 'Fuente';
        $combo->enDiv = true;
        $combo->required = true;
        $combo->detonar = false;
        $combo->form = (isset($cve_fin->form)) ? $cve_fin->form : 'cveFinForm';
        $combo->mensaje = 'Seleccionar Fuente';
        $combo->change = '
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"cambioFte",
                fte_id:$(this).val()
            });';
        $params = [];

        if (isset($cve_fin->horizontal)) {
            $combo->horizontal = $cve_fin->horizontal;
        }

        if (is_null($_SESSION[_S]['cvefin']->cff)) {
            $fte = $cf->templateCombo($combo);
            $combo->sql = '
                SELECT DISTINCT fte_id,fte_nombre
                FROM ' . BD_SINPRES . '.cat_Fuente' . APP_EJERCICIO . '
            ';
        } else {
            $epg = "";
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    if (ClasificacionAdministrativa::getEpg() != '') {
                        $epg_id = ClasificacionAdministrativa::getEpg();
                        $epg .= ' AND epg_id = :epgId';
                        $params[':epgId'] = $epg_id;
                    }

                    $epg .= ' AND gpo_id = :gpoId AND ur_id = :urId';
                    $params[':gpoId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
                    $params[':urId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

                    $combo->sql = '
                        SELECT DISTINCT fte_id, fte_nombre
                        FROM poa_OrigenRecurso
                            JOIN enc_ClaveFinanciamiento using(ecf_id)
                            JOIN ' . BD_SINPRES . '.cat_Fuente' . APP_EJERCICIO . ' using(cff_id,fte_id)
                        WHERE cff_id = :cffId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT fte_id, fte_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_Fuente' . APP_EJERCICIO . ' using(cff_id, fte_id)
                        WHERE cff_id = :cffId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $whereSql = 'ecf_poa = 1 AND sts_id = 1';
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN enc_ClaveFinanciamiento USING (cff_id,fte_id)
                        JOIN presupuesto USING (ecf_id)
                        WHERE cff_id = :cffId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    break;
                default:
                    break;
            }

            if (!is_null($_SESSION[_S]['cvefin']->fte)) {
                $combo->selecciona = $_SESSION[_S]['cvefin']->fte;
                if ($_SESSION[_S]['cvefin']->lectura === true) {
                    $combo->sql .= ' AND fte_id = :fteId';
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                }
            }

            $combo->errormessage = 'Seleccione La Fuente de Financiamiento';
            $combo->sql .= $epg . ' ORDER BY fte_id;';
            $combo->params = $params;
            $fte = $cf->ponCombo($combo);
            if (!is_null($cf->comboValorUnico)) {
                $_SESSION[_S]['cvefin']->fte = $cf->comboValorUnico;
            }
        }

        //parametros para el crear el combo de la subfuente
        $combo = new stdClass();
        $combo->id = 'sft_id';
        $combo->label = 'Subfuente';
        $combo->detonar = false;
        $combo->mensaje = 'Lista de Subfuentes';
        $combo->required = true;
        $combo->errormessage = 'Seleccione La Subfuente de Financiamiento';
        $combo->sql = '';
        $combo->change = '
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"cambioSft",
                sft_id:$(this).val()
            });';
        $params = [];

        if (is_null($_SESSION[_S]['cvefin']->fte)) {
            $sft = $cf->templateCombo($combo);
        } else {
            $epg = "";
            $combo->sql = '
                SELECT DISTINCT sft_id,sft_nombre
                FROM cat_Subfuente' . APP_EJERCICIO;
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    if (ClasificacionAdministrativa::getEpg() != '') {
                        $epg_id = ClasificacionAdministrativa::getEpg();
                        $epg = ' AND epg_id = :epgId';
                        $params[':epgId'] = $epg_id;
                    }

                    $epg .= ' AND gpo_id = :gpoId AND ur_id = :urId';
                    $params[':gpoId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
                    $params[':urId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

                    $combo->sql = '
                        SELECT DISTINCT sft_id,sft_nombre
                        FROM poa_OrigenRecurso
                            INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                            INNER JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT sft_id,sft_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' using(cff_id,fte_id,sft_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN enc_ClaveFinanciamiento USING (cff_id,fte_id,sft_id)
                        JOIN presupuesto USING (ecf_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    break;
                default:
                    $combo->sql .= ' JOIN enc_ClaveFinanciamiento USING(cff_id,fte_id,sft_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    break;
            }

            if ($_SESSION[_S]['cvefin']->lectura) {
                $combo->sql .= ' AND sft_id = :sftId ';
                $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
            }

            $combo->sql .= $epg;
            $combo->params = $params;

            if (!is_null($_SESSION[_S]['cvefin']->sft)) {
                $combo->selecciona = $_SESSION[_S]['cvefin']->sft;
            }

            $sft = $cf->ponCombo($combo);
            if (!is_null($cf->comboValorUnico)) {
                $_SESSION[_S]['cvefin']->sft = $cf->comboValorUnico;
            }
        }

        //parametros para el crear el combo del origen
        $combo->id = 'ori_id';
        $combo->label = 'Origen';
        $combo->mensaje = 'Lista de Orígenes de Financiamientos';
        $combo->errormessage = 'Seleccione El Orígen de Financiamiento';
        $combo->sql = '';
        $combo->detonar = false;
        $combo->change = '
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"cambioOri",
                ori_id:$(this).val()
            });';
        $params = [];

        if (is_null($_SESSION[_S]['cvefin']->sft)) {
            $ori = $cf->templateCombo($combo);
        } else {
            $epg = "";
            $combo->sql = '
                SELECT DISTINCT ori_id, ori_nombre
                FROM ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . '
            ';
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    if (ClasificacionAdministrativa::getEpg() != '') {
                        $epg_id = ClasificacionAdministrativa::getEpg();
                        $epg = ' AND epg_id = :epgId';
                        $params[':epgId'] = $epg_id;
                    }

                    $epg .= ' AND gpo_id = :gpoId AND ur_id = :urId';
                    $params[':gpoId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
                    $params[':urId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

                    $combo->sql = '
                        SELECT DISTINCT ori_id, ori_nombre
                        FROM poa_OrigenRecurso
                            JOIN enc_ClaveFinanciamiento using(ecf_id)
                            JOIN ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT ori_id,ori_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . ' using(cff_id,fte_id,sft_id,ori_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                            JOIN enc_ClaveFinanciamiento USING (cff_id,fte_id,sft_id,ori_id)
                            JOIN presupuesto USING (ecf_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    break;
                default:
                    $combo->sql .= ' JOIN enc_ClaveFinanciamiento USING(cff_id,fte_id,sft_id,ori_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    break;
            }
            if ($_SESSION[_S]['cvefin']->lectura) {
                $combo->sql .= ' AND ori_id = :oriId ';
                $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;
            }

            $combo->sql .= $epg;
            $combo->params = $params;

            if (!is_null($_SESSION[_S]['cvefin']->ori)) {
                $combo->selecciona = $_SESSION[_S]['cvefin']->ori;
            }

            $ori = $cf->ponCombo($combo);
            if (!is_null($cf->comboValorUnico)) {
                $_SESSION[_S]['cvefin']->ori = $cf->comboValorUnico;
            }
        }

        //parametros para el crear el combo del suborigen
        $combo->id = 'sor_id';
        $combo->label = 'Suborigen';
        $combo->mensaje = 'Lista de Suborígenes de Financiamientos';
        $combo->errormessage = 'Seleccione el Suborígen de Financiamiento';
        $combo->sql = '';
        $combo->detonar = false;
        $combo->change = '
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"cambioSor",
                sor_id:$(this).val()
            });';
        $params = [];

        if (is_null($_SESSION[_S]['cvefin']->ori)) {
            $sor = $cf->templateCombo($combo);
        } else {
            $epg = "";
            $combo->sql = '
                SELECT DISTINCT sor_id,sor_nombre
                FROM ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO;
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    if (ClasificacionAdministrativa::getEpg() != '') {
                        $epg_id = ClasificacionAdministrativa::getEpg();
                        $epg = ' AND epg_id = :epgId';
                        $params[':epgId'] = $epg_id;
                    }

                    $epg .= ' AND gpo_id = :gpoId AND ur_id = :urId';
                    $params[':gpoId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
                    $params[':urId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

                    $combo->sql = '
                        SELECT DISTINCT sor_id, sor_nombre
                        FROM poa_OrigenRecurso
                        JOIN enc_ClaveFinanciamiento using(ecf_id)
                        JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id , sft_id , ori_id , sor_id )
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT sor_id, sor_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id , sft_id , ori_id , sor_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN enc_ClaveFinanciamiento USING (cff_id, fte_id,sft_id,ori_id,sor_id)
                        JOIN presupuesto USING (ecf_id)
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId
                    ';
                    $param[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $param[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $param[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    $param[':oriId'] = $_SESSION[_S]['cvefin']->ori;
                    break;
            }

            if ($_SESSION[_S]['cvefin']->lectura) {
                $combo->sql .= ' AND sor_id = :sorId ';
                $params[':sorId'] = $_SESSION[_S]['cvefin']->sor;
            }

            $combo->sql .= $epg;
            $combo->params = $params;

            if (!is_null($_SESSION[_S]['cvefin']->sor)) {
                $combo->selecciona = $_SESSION[_S]['cvefin']->sor;
            }

            $sor = $cf->ponCombo($combo);
            if (!is_null($cf->comboValorUnico)) {
                $_SESSION[_S]['cvefin']->sor = $cf->comboValorUnico;
            }
        }

        //parametros para el crear el combo de la clave de financiamiento y año
        $combo->id = 'cfi_id';
        $combo->label = 'Referencia';
        $combo->mensaje = false;
        $combo->errormessage = 'Seleccione La Referencia de Financiamiento';
        $combo->camposVal = 'cfi_id,cfi_anio';
        $combo->camposText = 'cfi_nombre';
        $combo->required = true;
        $combo->comoAtr = 'cfi_id,cfi_anio';
        $combo->detonar = (isset($cve_fin->detonar)) ? $cve_fin->detonar : true;
        $combo->sql = '';
        $combo->change = '
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"cambioCveFin",
                cfi_id:$(this).find("option:selected").attr("cfi_id"),
                cfi_anio:$(this).find("option:selected").attr("cfi_anio")
            });
        ';
        $params = [];

        if (is_null($_SESSION[_S]['cvefin']->cff)) {
            $cvefin = $cf->templateCombo($combo);
        } else {
            $epg = "";
            $combo->sql = '
                SELECT DISTINCT cfi_id,cfi_anio,cfi_nombre
                FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO;
            switch ($_SESSION[_S]['cvefin']->filtro) {
                case 'poa':
                    if (ClasificacionAdministrativa::getEpg() != '') {
                        $epg_id = ClasificacionAdministrativa::getEpg();
                        $epg = ' AND epg_id = :epgId';
                        $params[':epgId'] = $epg_id;
                    }

                    $epg .= ' AND gpo_id = :gpoId AND ur_id = :urId';
                    $params[':gpoId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
                    $params[':urId'] = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

                    $combo->sql = '
                        SELECT DISTINCT cfi_id,cfi_anio,cfi_nombre
                        FROM poa_OrigenRecurso
                        JOIN enc_ClaveFinanciamiento USING(ecf_id)
                        JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                        WHERE fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId AND sor_id = :sorId
                    ';
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;
                    $params[':sorId'] = $_SESSION[_S]['cvefin']->sor;
                    break;
                case 'poa2':
                    $combo->sql = '
                        SELECT DISTINCT cfi_id,cfi_anio,cfi_nombre
                        FROM enc_ClaveFinanciamiento
                        JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                        WHERE cff_id = :cffId AND fte_id = :fteId AND sft_id = :sftId AND ori_id = :oriId AND sor_id = :sorId
                    ';
                    $params[':cffId'] = $_SESSION[_S]['cvefin']->cff;
                    $params[':fteId'] = $_SESSION[_S]['cvefin']->fte;
                    $params[':sftId'] = $_SESSION[_S]['cvefin']->sft;
                    $params[':oriId'] = $_SESSION[_S]['cvefin']->ori;
                    $params[':sorId'] = $_SESSION[_S]['cvefin']->sor;
                    break;
                case 'presupuesto':
                    $combo->sql .= '
                        JOIN enc_ClaveFinanciamiento USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        JOIN presupuesto USING(ecf_id)
                    ';
                    break;
            }

            if ($_SESSION[_S]['cvefin']->lectura) {
                $combo->sql = ' AND cfi_id = :cfiId AND cfi_anio = :cfiAnio';
                $params[':cfiId'] = $_SESSION[_S]['cvefin']->cfi;
                $params[':cfiAnio'] = $_SESSION[_S]['cvefin']->anio;
            }

            $combo->sql .= $epg;
            $combo->params = $params;

            if (!is_null($_SESSION[_S]['cvefin']->cfi) && !is_null($_SESSION[_S]['cvefin']->anio)) {
                $combo->selecciona = $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            }

            $cvefin = $cf->ponCombo($combo);
            if (!is_null($cf->comboValorUnico)) {
                $_SESSION[_S]['cvefin']->cfi = substr($cf->comboValorUnico, 0, -2);
                $_SESSION[_S]['cvefin']->anio = substr($cf->comboValorUnico, -2, 2);
            }
        }

        $interfaz = (isset($cve_fin->interfaz)) ? $cve_fin->interfaz : '';
        $script = '';
        $script = $cf->getScript() . ' ' . $preseleccion;
        switch ($interfaz) {
            case 'modal':
                $combos = '
                    <div id="myModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; padding-top: 70px;">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">Clave de Financiamiento</h4>
                                    <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>

                                </div>
                                <div class="modal-body">
                                    <form id="cveFinForm">
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6">
                                        ' . $cff . '
                                        </fieldset>
                                    </div>
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6">
                                        ' . $fte . '
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">
                                        ' . $sft . '
                                        </fieldset>
                                    </div>
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6">
                                        ' . $ori . '
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">
                                        ' . $sor . '
                                        </fieldset>
                                    </div>
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6">
                                        ' . $cvefin . '
                                        </fieldset>
                                    </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary" id="btnAceptarCvfFin" form="">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                ';
                $script .= ' $(".cerrar").click(function(){ $("#myModal").remove(); });';
                $script .= '
                    $("#btnAceptarCvfFin").click(function(){
                        valido = true;
                        $("#cveFinForm").find(":required").each(function(){
                            valido = valido && this.checkValidity();
                            if(!this.checkValidity()){
                                $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"></div><div class="line9"></div><div class="line8"></div><div class="line7"></div><div class="line6"></div><div class="line5"></div><div class="line4"></div><div class="line3"></div><div class="line2"></div><div class="line1"></div></div></div>\');
                            }
                        });

                        $( ".formError" ).show("pulsate");
                        setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
                        setTimeout(function() { $( ".formError" ).remove(); }, 4500 );

                        if(valido){
                            ' . (isset($cve_fin->scriptAceptar) ? $cve_fin->scriptAceptar : '') . '
                            $("#myModal").remove();
                        }
                    });
                    ';
                break;
            default:
                $combos = '
                    <fieldset class="col-md-12 col-xs-12" id="cveFin">
                        <legend>Clave de Financiamiento</legend>
                            ' . $cff . '
                            ' . $fte . '
                            ' . $sft . '
                            ' . $ori . '
                            ' . $sor . '
                            ' . $cvefin . '
                    </fieldset>
                ';
                break;
        }

        $change = (isset($cve_fin->change)) ? 'console.log("Aqui detono x change de cvefin");' . $cve_fin->change : 'console.log("cambio Referencia");';
        return $combos . '<script language="javascript" >' . $script . ' function desencadenaCveFin(){ if( $("#cfi_id").val() != "" ){ ' . $change . '} }</script>';
    }

    public static function creaComboCveFin()
    {
        $parametros = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $lectura = (isset($parametros->lectura)) ? true : false;
        $cf = new WebResponse();
        $filtro = (isset($parametros->filtro)) ? $parametros->filtro : 'presupuesto';

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            //si es de nive UE debemos filtrar las claves de financiamiento
            //no lleva break porque estaba el mismo comportamiento en ambos niveles
            case 2:
                switch ($_SESSION[_S]['prf_id']) {
                    case 'S':
                    case 'U': //Direccion de recursos humanos, aplica para los folios de modificaciones presupuestales.
                        $parametros->gpo_id = (!empty($parametros->gpo_id)) ? $parametros->gpo_id : $_SESSION[_S]['clasAdm-gpo'];
                        $parametros->ur_id = (!empty($parametros->ur_id)) ? $parametros->ur_id : $_SESSION[_S]['clasAdm-ur'];
                        break;
                    default: //Ur y ue
                        $parametros->gpo_id = $_SESSION[_S]['gpo_id'];
                        $parametros->ur_id = $_SESSION[_S]['ur_id'];
                        break;
                }

                break;
            default: //Sefin
                $parametros->gpo_id = (!empty($parametros->gpo_id)) ? $parametros->gpo_id : $_SESSION[_S]['clasAdm-gpo'];
                $parametros->ur_id = (!empty($parametros->ur_id)) ? $parametros->ur_id : $_SESSION[_S]['clasAdm-ur'];
                break;
        }

        $sql = '';
        $params = [];

        //$parametros->inicial sirve para identificar si pintará el combo inicialmente o no.
        if (!isset($parametros->inicial)) {
            switch ($filtro) {
                case 'presupuesto':
                    /*
                    Esta consulta se aumento por que en el bloqueo de la CLC
                    Entonces si la consulta nos regresa la variable abierto en 0 quiere decir que existe un bloqueo y si el tipo de Recurso
                    es diferente de "C" quiere decir que solo han bloqueado un tipo de recurso y se tiene que aumentar un JOIN  con el suborigen
                    a la consulta de presupuesto  especificando el tipo de Recurso
                    "N"= Ninguno
                    "A" Estatal
                    "B" Federal
                     */
                    $where = $recurso = '';

                    $sql = '
                        SELECT cfi_clave,cfi_nombre,ecf_id
                        FROM presupuesto
                        JOIN enc_ClaveFinanciamiento USING(ecf_id,cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        ' . $recurso . '
                        WHERE gpo_id = :gpoId AND ur_id = :urId ' . $where;

                    $params[':gpoId'] = $parametros->gpo_id;
                    $params[':urId'] = $parametros->ur_id;

                    if ($lectura) {
                        $sql .= ' AND ecf_id = :ecfId';
                        $params[':ecfId'] = $parametros->ecf_id;
                    }

                    $sql .= ' GROUP BY cfi_clave,cfi_nombre,ecf_id;';

                    break;
                case 'presupuestoComprasConsolidadas':
                    $sql = '
                        SELECT cfi_clave,cfi_nombre,ecf_id
                        FROM presupuesto p
                        INNER JOIN cat_UnidadResponsable USING( gpo_id, ur_id )
                        INNER JOIN cat_PartidaEspecifica USING( pg_id, pe_id )
                        INNER JOIN enc_ClaveFinanciamiento USING( ecf_id, cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        WHERE ur_compraDirecta = 2
                        AND pe_comprasDirectas != 1
                        AND ecf_division = 1
                        AND !(
                            gpo_id = 3 AND ur_id IN (3, 4) AND pe_id = 353
                        )
                        AND ecf_id=1679
                        AND gpo_id = :gpoId AND ur_id = :urId';

                    $params[':gpoId'] = $parametros->gpo_id;
                    $params[':urId'] = $parametros->ur_id;

                    if ($lectura) {
                        $sql .= ' AND ecf_id = :ecfId';
                        $params[':ecfId'] = $parametros->ecf_id;
                    }

                    $sql .= ' GROUP BY gpo_id, ur_id, cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio;';
                    break;
                case 'ministracion':
                case 'AcuerdosMinistracion':
                    $sql = '
                        SELECT ecf.ecf_id, cf.cfi_nombre, cf.cfi_clave
                        FROM enc_ClaveFinanciamiento ecf
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        WHERE ecf.ecf_id = 015351
                        GROUP BY cf.cff_id, cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, cf.cfi_id, cf.cfi_anio,cf.cfi_nombre,cf.cfi_clave;';
                    break;
                case 'presupuestoUe':

                    $clave = 'WHERE ecf_id IS NOT NULL';
                    switch (isset($parametros->tipo)) {
                        case "K":
                            /* se actualizó las claves de financiamiento para burztilización */
                            if (isset($parametros->ecf_id) && $parametros->ecf_id > 0) {
                                $clave .= PHP_EOL . "AND ecf_id  = :ecfId";
                                $params[':ecfId'] = $parametros->ecf_id;
                            } else {
                                $claves = '
                                "AAABA0119","AAABA0219", "AAABA0319", "AAABA0419", "AAABA0519",
                                "AAABA0619","AAABA0719", "AAABA0819", "AAABB0119", "AAABB0219", "AAABB0319",
                                "AAABB0419", "AAABB0519", "AAABB0619", "AAABB0719", "AACBA0119", "AACBA0219",
                                "AACBA0319", "AACBA0419", "AACBA0519", "AACBA0619", "AACBA0719", "AACBA0S819",
                                "AAAAA0119", "AAAAA0219", "AAAAA0319", "AAAAA0419", "AAAAA0519",
                                "AAAAA0619", "AAAAA0719", "AAAAA0819", "AAAAA0919", "AAKAA0119", "AAKAA0219",
                                "AAKAA0319", "AAKAA0419", "AAKAA0519", "AAKAA0619", "AAKAA0719", "AAKAA0819",
                                "AAKAA0919"';
                                $clave .= ' AND cfi_clave IN(:claves) ';
                                $params[':claves'] = $claves;
                            }
                            break;
                    }

                    $sql = "
                        SELECT ecf_id, cfi_nombre,cfi_clave
                        FROM enc_ClaveFinanciamiento
                        JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            $clave
                        GROUP BY ecf_id,cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio,cfi_nombre,cfi_clave;
                    ";
                    break;
                case "PagosATerceros":
                    $ex = '';
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 106) {
                        $ex = ',2786';
                    }
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 411) {
                        $ex = ',218,357,705,1982,1990,2031,2073';
                    }
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 433) {
                        $ex = ',1120';
                    }
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 412) {
                        $ex = ',1290,670,1467,1276';
                    }
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 429) {
                        $ex = ',1268';
                    }
                    //20-03-2019 @Jordy Ticket #958932 clave de financiamiento ABAAA0317
                    if (($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == "539")) {
                        $ex = ',007597';
                    }

                    $sql = '
                        SELECT por.ecf_id, cfi_nombre, cfi_clave
                        FROM poa_OrigenRecurso por
                            INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        WHERE (gpo_id = :gpoId AND ur_id = :urId)
                        GROUP BY por.ecf_id, cf.cfi_nombre, cf.cfi_clave
                        UNION ALL
                        SELECT ecf_id, cfi_nombre, cfi_clave
                        FROM enc_ClaveFinanciamiento
                            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        WHERE ecf_id IN (1288, 1293, 1295, 698, 695' . $ex . ');
                    ';
                    $params[':gpoId'] = $_SESSION[_S]['gpo_id'];
                    $params[':urId'] = $_SESSION[_S]['ur_id'];
                    break;
                case "Afectaciones":
                    $ue = "";
                    $clave = "";
                    switch ($parametros->tipo) {
                        case "J":
                            if ($parametros->gpo_id == "1" && $parametros->ur_id == "16") {
                                //Deuda Publica
                                if ($parametros->ue_id == "002") {
                                    $ue = ' AND ue_id = :ueId ';
                                    $params[':ueId'] = $parametros->ue_id;
                                }
                            } elseif ($parametros->ue_id == "004") {
                                $ue = ' AND ue_id = :ueId ';
                                $params[':ueId'] = $parametros->ue_id;
                            }
                            break;
                        case "K":
                            $claves = '
                            "AAABA0119","AAABA0219", "AAABA0319", "AAABA0419", "AAABA0519",
                            "AAABA0619","AAABA0719", "AAABA0819", "AAABB0119", "AAABB0219", "AAABB0319",
                            "AAABB0419", "AAABB0519", "AAABB0619", "AAABB0719", "AACBA0119", "AACBA0219",
                            "AACBA0319", "AACBA0419", "AACBA0519", "AACBA0619", "AACBA0719", "AACBA0S819",
                            "AAAAA0119", "AAAAA0219", "AAAAA0319", "AAAAA0419", "AAAAA0519",
                            "AAAAA0619", "AAAAA0719", "AAAAA0819", "AAAAA0919", "AAKAA0119", "AAKAA0219",
                            "AAKAA0319", "AAKAA0419", "AAKAA0519", "AAKAA0619", "AAKAA0719", "AAKAA0819",
                            "AAKAA0919"';

                            if ($parametros->gpo_id == "1" && $parametros->ur_id == "16") {
                                //INGRESOS Y DERECHOS AFECTOS A FIDEICOMISOS
                                if ($parametros->ue_id == "001") {
                                    $ue = ' AND ue_id = :ueId ';
                                    $params[':ueId'] = $parametros->ue_id;
                                }
                            } elseif ($parametros->ue_id == "003") {
                                $ue = ' AND ue_id = :ueId ';
                                $params[':ueId'] = $parametros->ue_id;
                            }
                            $clave = ' AND cfi_clave IN(:claves) ';
                            $params[':claves'] = $claves;
                            break;
                        case "L":
                            if ($parametros->gpo_id == "1" && $parametros->ur_id == "16") {
                                //UNIDAD DE PLANEACIÓN FINANCIERA-PAGOS
                                //RECAUDACIÓN
                                if ($parametros->ue_id == "005" || $parametros->ue_id == "006") {
                                    $ue = ' AND ue_id = :ueId ';
                                    $params[':ueId'] = $parametros->ue_id;
                                }
                            } elseif ($parametros->ue_id == "001" || $parametros->ue_id == "002") {
                                $ue = ' AND ue_id = :ueId ';
                                $params[':ueId'] = $parametros->ue_id;
                            }
                            break;
                    }

                    $sql = "
                        SELECT cfi_clave,cfi_nombre,ecf_id,(sum(pre_vigente) - (pre_comprometido)) as 'saldo'
                        FROM presupuesto
                        JOIN enc_ClaveFinanciamiento USING(ecf_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        WHERE gpo_id = :gpoId AND ur_id = :urId
                        " . $ue . " " . $clave;
                    $params[':gpoId'] = $parametros->gpo_id;
                    $params[':urId'] = $parametros->ur_id;

                    if ($lectura) {
                        $sql .= " AND ecf_id = :ecfId";
                        $params[':ecfId'] = $parametros->ecf_id;
                    }

                    $sql .= "
                        GROUP BY gpo_id, ur_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio;";

                    if ($parametros->tipo == "K") {
                        $sql = '
                            SELECT cfi_clave,cfi_nombre,ecf_id, "0" as saldo
                            FROM enc_ClaveFinanciamiento
                            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            WHERE cfi_clave IN(:claves)
                        ';
                        $params[':claves'] = $claves;
                    }
                    if ($parametros->tipo == "M") {
                        $params = [];
                        $sql = '
                            SELECT cfi_clave,cfi_nombre,ecf_id, "0" as saldo
                            FROM enc_ClaveFinanciamiento
                            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        ';
                    }
                    break;
                case 'OrigenRecurso':
                    $federal = ((isset($parametros->federal) && $parametros->federal == 1) ? true : false);
                    $estatal = ((isset($parametros->estatal) && $parametros->estatal == 1) ? true : false);
                    $tipoRecurso = (($federal && $estatal) ? '' : ($federal ? "AND ter_id = 'B' " : ($estatal ? "AND ter_id = 'A' " : '')));
                    $tipoReporte = ((isset($parametros->tipoReporte) && $parametros->tipoReporte == 1) ? 1 : $parametros->tipoReporte);
                    $ue = (isset($parametros->ue_id) ? "AND p.ue_id = " . $parametros->ue_id . " " : '');
                    $sql = "
                        SELECT ecf_id, cfi_nombre, cfi_clave
                        FROM poa_OrigenRecurso o
                        LEFT JOIN enc_ClaveFinanciamiento ecf USING(ecf_id)
                        LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        LEFT JOIN enc_Programatico epg USING(gpo_id, ur_id, epg_id)
                        LEFT JOIN cat_ObraActividad p USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                        LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " c USING(cff_id, fte_id, sft_id, ori_id, sor_id)";

                    if ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6) {
                        $sql .= "
                        INNER JOIN cat_CargaTrabajo t ON t.gpo_id = o.gpo_id AND t.ur_id = o.ur_id
                        WHERE t.usr_id = :usrId
                        ";
                        $params[':usrId'] = $_SESSION[_S]['usr_id'];
                    } else {
                        $sql .= "
                        WHERE o.gpo_id = :gpoId AND o.ur_id = :urId
                        ";
                        $params[':gpoId'] = $parametros->gpo_id;
                        $params[':urId'] = $parametros->ur_id;
                    }

                    $sql .= "
                        $ue
                        $tipoRecurso
                        GROUP BY cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio
                    ";
                    $params[':gpoId'] = $parametros->gpo_id;
                    break;
                case 'general':
                    $wh2 = "";
                    if ($parametros->tipo . $parametros->stc_id == "M15") {
                        //Actualizar en ID en cada ejecicio
                        $wh2 = "WHERE ecf_id = 015351";
                    }
                    $sql = "
                        SELECT ecf_id,cfi_nombre,cfi_clave
                        from enc_ClaveFinanciamiento
                        JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        " . $wh2 . "
                        GROUP BY ecf_id,cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio,cfi_nombre,cfi_clave;";
                    break;
            }
        }

        $combo = new stdClass();
        $combo->id = 'cveFin_id';
        $combo->sql = $sql;
        $combo->params = $params;
        $combo->enDiv = true;
        $combo->required = true;
        $combo->soloText = true;
        $combo->label = 'Clave de Financiamiento';
        $combo->mensaje = 'Seleccione una opcion de la lista';
        $combo->camposText = 'cfi_clave,cfi_nombre';
        $combo->camposVal = 'ecf_id';
        $combo->search = true;
        $combo->comoAtr = 'ecf_id' . ($filtro == "Afectaciones" ? ',saldo,cfi_clave' : '');

        // se agrego porque en la generacion nde compromisos este tiene que detonar
        if (isset($parametros->cambio)) {
            $combo->change = (isset($parametros->change) && $parametros->change != '') ? $parametros->change : '';
        }

        $combo->selecciona = (isset($parametros->selecciona) && $parametros->selecciona != '') ? $parametros->selecciona : '';

        if (isset($parametros->inicial)) {
            $cve_fin = $cf->templateCombo($combo);
        } else {
            if ($lectura) {
                $combo->lectura = true;
            }

            $cve_fin = $cf->ponCombo($combo);
        }

        $script = '';
        $script = $cf->getScript();

        $combocvefin = '
            <fieldset class="col-md-12 col-xs-12 mb-4" id="comboCvefin">
                ' . $cve_fin . '
            </fieldset>' . '
        ';

        return $combocvefin . '<script language="javascript" >' . $script . '</script>';
    }

    private function cargaComboCveFin()
    {
        $tipo = (isset($this->prm->tipo) && !empty($this->prm->tipo)) ? $this->prm->tipo : '';
        $stc_id = (isset($this->prm->stc_id) && !empty($this->prm->stc_id)) ? $this->prm->stc_id : '';
        $tarea = (isset($this->prm->tarea) && !empty($this->prm->tarea)) ? $this->prm->tarea : '';
        $params = [];
        $and = '';

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
                switch ($_SESSION[_S]['prf_id']) {
                    case 'S':
                    case 'U': //Direccion de recursos humanos, aplica para los folios de modificaciones presupuestales.
                        $this->prm->gpo_id = (!empty($this->prm->gpo_id)) ? $this->prm->gpo_id : $_SESSION[_S]['clasAdm-gpo'];
                        $this->prm->ur_id = (!empty($this->prm->ur_id)) ? $this->prm->ur_id : $_SESSION[_S]['clasAdm-ur'];
                        break;
                    default: //Ur y ue
                        $this->prm->gpo_id = $_SESSION[_S]['gpo_id'];
                        $this->prm->ur_id = $_SESSION[_S]['ur_id'];
                        break;
                }
                break;
            default:
                $this->prm->gpo_id = (!empty($this->prm->gpo_id)) ? $this->prm->gpo_id : $_SESSION[_S]['clasAdm-gpo'];
                $this->prm->ur_id = (!empty($this->prm->ur_id)) ? $this->prm->ur_id : $_SESSION[_S]['clasAdm-ur'];
                break;
        }

        $filtro = (isset($this->prm->filtro)) ? $this->prm->filtro : 'presupuesto';

        // Para el caso de las CLC´S DE  case"A": case"I":case"C":case"D":case"E": case"M":    case"J": case"K": case"L":
        if ($tarea == 'verificar') {
            $params = [];
            $where = "where gpo_id = :gpoId and ur_id = :urId";
            $params[':gpoId'] = $this->prm->gpo_id;
            $params[':urId'] = $this->prm->ur_id;
        } else {
            if (isset($_SESSION[_S]['clasAdm-epg']) && !is_null($_SESSION[_S]['clasAdm-epg']) && $_SESSION[_S]['clasAdm-epg'] !== 0) {
                $and = 'AND epg_id = :epgId';
                $params[':epgId'] = $_SESSION[_S]['clasAdm-epg'];
            }
            $where = "WHERE gpo_id = :gpoId AND ur_id = :urId $and";
            $params[':gpoId'] = $this->prm->gpo_id;
            $params[':urId'] = $this->prm->ur_id;
        }

        /*
        Para los tipo de inversion publica debido a que no se manejan subtipos cuando
        se genere un bloqueo se va a generar por todo el tipo de CLC y nunca va a traer el subtipo de CLC ya que
        quien detona las claves de financiamiento disponibles es la seleccón del Encuadre programatico.
         */

        $wh2 = $recurso = $wh = '';

        $combo = new stdClass();
        $combo->id = 'cveFin_id';
        switch ($filtro) {
            case 'presupuestoUe':
            case 'presupuesto':
                switch ($tipo) {
                    case "K":
                        $params = [];
                        $sql = '
                            SELECT cfi_clave, cfi_nombre, ecf_id
                            FROM enc_ClaveFinanciamiento AS e
                            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)' . PHP_EOL;
                        switch ($stc_id) {
                            case "01":
                                $sql .= '
                                    WHERE cfi_clave IN ("AAABA0119","AAABA0219", "AAABA0319", "AAABA0419", "AAABA0519",
                                        "AAABA0619","AAABA0719", "AAABA0819", "AAABB0119", "AAABB0219", "AAABB0319",
                                        "AAABB0419", "AAABB0519", "AAABB0619", "AAABB0719", "AACBA0119", "AACBA0219",
                                        "AACBA0319", "AACBA0419", "AACBA0519", "AACBA0619", "AACBA0719", "AACBA0S819","AAAAA0119")'
                                    . PHP_EOL;
                                break;
                            case "02":
                                $sql .= '
                                    WHERE cfi_clave IN ( "AAAAA0219", "AAAAA0319", "AAAAA0419", "AAAAA0519",
                                        "AAAAA0619", "AAAAA0719", "AAAAA0819", "AAAAA0919", "AAKAA0119", "AAKAA0219",
                                        "AAKAA0319", "AAKAA0419", "AAKAA0519", "AAKAA0619", "AAKAA0719", "AAKAA0819",
                                        "AAKAA0919")' . PHP_EOL;
                                break;
                            default:
                                $sql .= 'WHERE cfi_clave IN("")';
                                break;
                        }
                        $sql .= ' AND e.sts_id = 1
                        GROUP BY cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio, cfi_clave, cfi_nombre';

                        break;

                    default:
                        $sql = "
                            SELECT cfi_clave,cfi_nombre,ecf_id,gpo_id, ur_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id
                            FROM presupuesto
                            JOIN enc_ClaveFinanciamiento USING(ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            " . $recurso . "
                            {$where} " . $wh . "
                            AND sts_id = 1
                            GROUP BY gpo_id, ur_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio,gpo_id, ur_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id,cfi_clave,cfi_nombre,ecf_id;";
                        break;
                }
                break;

            case 'general':
                $params = [];
                //Actualizar en ID en cada ejecicio
                if ($this->prm->tipo . $this->prm->stc_id == "M15") {
                    $wh2 = "WHERE ecf_id = 015351";
                }

                $sql = "
                    SELECT cfi_clave,cfi_nombre,ecf_id, '0' as saldo
                    from enc_ClaveFinanciamiento
                    JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    " . $recurso . "
                    " . $wh2 . "
                    GROUP BY ecf_id,cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio,cfi_clave,cfi_nombre;";
                break;
            case "PagosATerceros":
                $params = [];
                $ex = '';
                if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 306) {
                    $ex = ',2089,1349';
                }
                if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 411) {
                    $ex = ',218,357,705,1982,1990,2031,2073';
                }
                if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 433) {
                    $ex = ',1120';
                }
                if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 412) {
                    $ex = ',1290,670,1467,1276';
                }
                //20-03-2019 @Jordy Ticket #958932 clave de financiamiento ABAAA0317 con el subtipo de contrato
                if (($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 539) && (isset($this->prm->stc_id) && $this->prm->stc_id == '03')) {
                    $ex = ',007597';
                }
                $sql = '
                    SELECT
                        cfi_clave,cfi_nombre,ecf_id
                    FROM poa_OrigenRecurso
                        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    ' . $recurso . '
                    WHERE (gpo_id = :gpoId AND ur_id = :urId) ' . $wh . '
                    GROUP BY  ecf_id,cfi_nombre,cfi_clave
                    UNION ALL
                    SELECT
                        cfi_clave,cfi_nombre,ecf_id
                    FROM enc_ClaveFinanciamiento
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    ' . $recurso . '
                    WHERE ecf_id IN(1288,1293,1295,698,695' . $ex . ')  ' . $wh . ';';
                $params[':gpoId'] = $_SESSION[_S]['gpo_id'];
                $params[':urId'] = $_SESSION[_S]['ur_id'];
                break;
            case 'ministracion':
                $params = [];
                //16-01-2019 Se busco el filtro de ministracion y pareciera ser que no esta en uso
                $sql = "
                        SELECT cfi_clave,cfi_nombre,ecf_id
                        FROM enc_ClaveFinanciamiento
                        INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        " . $recurso . "
                        WHERE  ecf_id = 015351 " . $wh . "
                        GROUP BY ecf_id,cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio,cfi_clave,cfi_nombre;";
                break;

            case 'anticipo':
                $params =[];
                $sql = "
                    SELECT cfi_clave, cfi_nombre, ecf_id
                    from cat_ObraActividad
                    JOIN enc_Programatico USING(gpo_id, ur_id,prg_id, spr_id, py_id, oa_id)
                    JOIN poa_OrigenRecurso USING(gpo_id, ur_id, epg_id )
                    JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    {$where}
                    ";
                $sql .= (strlen($where) > 0) ? "AND eoa_id = 6 " : "WHERE eoa_id = 6";
                $sql .= " GROUP BY ecf_id";
                $sql .= " ORDER BY cfi_clave";
                break;
            case 'anticiposFondoRotatorio':
                $params = [];
                $sql = "
                    SELECT cf.cfi_clave, cf.cfi_nombre, ecf.ecf_id
                    FROM enc_ClaveFinanciamiento  ecf
                    INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio) ";
                if (isset($this->prm->afe) && $this->prm->afe = 1) {
                    if (isset($this->prm->ecf_id) && $this->prm->ecf_id != "") {
                        $sql .= " WHERE ecf_id = :ecfId";
                        $params[':ecfId'] = $this->prm->ecf_id;
                    }
                } else {
                    $sql .= "    WHERE  ecf_id IN (001131,001130)";
                }
                $sql .= "  GROUP BY cf.cff_id, cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, cf.cfi_id, cf.cfi_anio, cf.cfi_clave, cf.cfi_nombre;";

                break;
            case 'AcuerdosMinistracion':
                $params = [];
                $sql = "
                    SELECT cf.cfi_clave, cf.cfi_nombre, ecf.ecf_id
                    FROM enc_ClaveFinanciamiento ecf
                    INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    WHERE ecf_id = 015351
                    GROUP BY cf.cff_id, cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, cf.cfi_id, cf.cfi_anio,cf.cfi_clave,cf.cfi_nombre;
                ";

                break;
            case "Afectaciones":
                //Consulta 2019 se modifico la parte de group by para que funcionara
                $sql = "
                    SELECT cf.cfi_clave, cf.cfi_nombre, ecf_id, (sum(pre_vigente) - sum(pre_comprometido)) as 'saldo'
                    FROM presupuesto
                        INNER JOIN presupuestoComprometido USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        INNER JOIN presupuestoVigente USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    {$where}
                    GROUP BY cf.cff_id, cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id,  cf.cfi_id, cf.cfi_anio,cf.cfi_clave, cf.cfi_nombre;
                ";

                break;
            case 'OrigenRecurso':
                $params = [];
                $federal = ((isset($this->prm->federal) && $this->prm->federal == 1) ? true : false);
                $estatal = ((isset($this->prm->estatal) && $this->prm->estatal == 1) ? true : false);
                $tipoRecurso = (($federal && $estatal) ? '' : ($federal ? "AND ter_id = 'B' " : ($estatal ? "AND ter_id = 'A' " : '')));
                $tipoReporte = ((isset($this->prm->tipoReporte) && $this->prm->tipoReporte == 1) ? $this->prm->tipoReporte : 1);
                $ue = (isset($this->prm->ue_id) ? "AND p.ue_id = " . $this->prm->ue_id . " " : '');
                $sql = "
                    SELECT ecf_id, cfi_nombre, cfi_clave
                    FROM poa_OrigenRecurso o
                        LEFT JOIN enc_ClaveFinanciamiento ecf USING( ecf_id )
                        LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                        LEFT JOIN enc_Programatico epg USING( gpo_id, ur_id, epg_id )
                        LEFT JOIN cat_ObraActividad p USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
                        LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " c USING( cff_id,fte_id, sft_id, ori_id, sor_id )";

                if ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6) {
                    $sql .= "
                    INNER JOIN cat_CargaTrabajo t ON t.gpo_id = o.gpo_id AND t.ur_id = o.ur_id
                    WHERE t.usr_id = :usrId";
                    $params[':usrId'] = $_SESSION[_S]['usr_id'];
                } else {
                    $sql .= "
                    WHERE o.gpo_id = :gpoId AND o.ur_id = :urId";
                    $params[':gpoId'] = $this->prm->gpo_id;
                    $params[':urId'] = $this->prm->ur_id;
                }

                $sql .= "
                {$ue}
                {$tipoRecurso}
                GROUP BY cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio;";
                break;
        }
        $combo->camposVal = 'ecf_id';
        $combo->comoAtr = 'ecf_id' . ($filtro == "Afectaciones" ? ',saldo' : '');
        $combo->sql = $sql;
        $combo->params = $params;
        //Si no recibe el parametro de selecciona que haga el recarga combo
        if (!isset($this->prm->selecciona)) {
            $this->recargaCombo($combo);
        }
    }

    /*----------------------
    Al pasarle el valor del encuadre de la Clave de Financiamiento, pone en las variables de sesión todo lo que corresponda a dicho encuadre
    -------------------------*/
    public static function PonEnVariablesEcf($ecf)
    {
        // Obtenemos la clasificación administrativa
        $cf = new WebResponse();
        $cf->setSql('
            SELECT ter_id,so.cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,ecf_clave,cfi_nombre
            FROM enc_ClaveFinanciamiento
            JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING( cff_id, fte_id, sft_id, ori_id, sor_id )
            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            WHERE ecf_id = :ecfId;');
        $cf->conectar();
        $cf->consultar([':ecfId' => $ecf]);
        $cf->desconectar();

        if ($cf->getNumeroRegistros() > 0) {
            $row = $cf->extraer();

            if (!isset($_SESSION[_S]['cvefin'])) {
                $_SESSION[_S]['cvefin'] = new stdClass();
            }

            $_SESSION[_S]['cvefin']->ter = $row['ter_id'];
            $_SESSION[_S]['cvefin']->cff = $row['cff_id'];
            $_SESSION[_S]['cvefin']->fte = $row['fte_id'];
            $_SESSION[_S]['cvefin']->sft = $row['sft_id'];
            $_SESSION[_S]['cvefin']->ori = $row['ori_id'];
            $_SESSION[_S]['cvefin']->sor = $row['sor_id'];
            $_SESSION[_S]['cvefin']->cfi = $row['cfi_id'];
            $_SESSION[_S]['cvefin']->anio = $row['cfi_anio'];
            $_SESSION[_S]['cvefin']->anio = $row['cfi_anio'];
            $_SESSION[_S]['cvefin']->cveFin = $row['ecf_clave'];
            $_SESSION[_S]['cvefin']->nombre = $row['cfi_nombre'];
            $_SESSION[_S]['cvefin']->ecf = $ecf;
            return $row;
        }
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Provisional: el objetivo es actualizar los valores de las variables públicas... entonces hay que mover ésto a otro .php
    pero ahorita no se me ocurre ningún otro :D
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function selEncProgFin()
    {
        // Nos pasan el encuadre programático y el encuadre de la clave de financiamiento
        $encProg = (isset($this->prm->encProg) ? $this->prm->encProg : '');
        $prog = (isset($this->prm->prog) ? $this->prm->prog : '');
        $encCveFin = (isset($this->prm->encCveFin) ? $this->prm->encCveFin : '');
        $cveFin = (isset($this->prm->cveFin) ? $this->prm->cveFin : '');
        $nomFin = (isset($this->prm->nomFin) ? $this->prm->nomFin : '');
        // Parámetro opcional para indicar si deben checarse :
        // 0: ambos
        // 1: sólo encuadre programatico
        // 2: sólo clave de financiamiento
        $queChecar = (isset($this->prm->que) ? $this->prm->que : 0);

        // Si no traen la longitud esperada, tronamos
        if ($queChecar == 0 || $queChecar == 1) {
            if (strlen(trim($prog)) !== 22) {
                $this->mixScript('notify("Encuadre programático incorrecto", "warning");');
                return false;
            }
        }
        if ($queChecar == 0 || $queChecar == 2) {
            if (strlen(trim($cveFin)) !== 11) {
                $this->mixScript('notify("Encuadre de Clave de Financiamiento incorrecto", "warning");');
                return false;
            }
        }

        // Asignamos a las variables globales el valor seleccionado del encuadre programático
        if ($queChecar == 0 || $queChecar == 1) {
            $_SESSION[_S]['clasAdm-ue'] = substr($prog, 4, 3);
            $_SESSION[_S]['clasAdm-prg'] = substr($prog, 8, 3);
            $_SESSION[_S]['clasAdm-spr'] = substr($prog, 12, 2);
            $_SESSION[_S]['clasAdm-py'] = substr($prog, 15, 3);
            $_SESSION[_S]['clasAdm-oa'] = substr($prog, 19, 3);
            $_SESSION[_S]['clasAdm-epg'] = $encProg;
            $this->mixScript(ClasificacionAdministrativa::ActualizaCombos());
        }
        // Asignamos a las variables globales el valor seleccionado del encuadre de la Clave de Financiamiento
        if ($queChecar == 0 || $queChecar == 2) {
            if (!isset($_SESSION[_S]['cvefin'])) {
                $_SESSION[_S]['cvefin'] = new stdClass();
            }

            $_SESSION[_S]['cvefin']->cff = substr($cveFin, 0, 1);
            $_SESSION[_S]['cvefin']->fte = substr($cveFin, 2, 1);
            $_SESSION[_S]['cvefin']->sft = substr($cveFin, 3, 1);
            $_SESSION[_S]['cvefin']->ori = substr($cveFin, 4, 1);
            $_SESSION[_S]['cvefin']->sor = substr($cveFin, 5, 1);
            $_SESSION[_S]['cvefin']->cfi = substr($cveFin, 7, 2);
            $_SESSION[_S]['cvefin']->anio = substr($cveFin, 9, 2);
            $_SESSION[_S]['cvefin']->cveFin = substr($cveFin, 0, 1) . substr($cveFin, 2, 4) . substr($cveFin, 7, 4);
            $_SESSION[_S]['cvefin']->nombre = $nomFin;
            $_SESSION[_S]['cvefin']->ecf = $encCveFin;
            $_SESSION[_S]['cvefin']->lectura = "true";
            $_SESSION[_S]['cvefin']->filtro = "poa";
        }
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Provisional: el objetivo es actualizar los valores de las variables públicas... entonces hay que mover ésto a otro .php
    pero ahorita no se me ocurre ningún otro :D
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function muestraCveFin()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $this->setContenedor('contenedorCFModal');
        $cve_fin = new stdClass();
        $cve_fin->interfaz = 'modal';
        $cve_fin->ecf_id = (isset($_SESSION[_S]['cvefin']) && isset($_SESSION[_S]['cvefin']->ecf)) ? $_SESSION[_S]['cvefin']->ecf : '';
        $cve_fin->filtro = (isset($prm->filtro) ? $prm->filtro : 'poa');
        $cve_fin->scriptAceptar = '
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"validaCveFinModal"
            });
        ';

        $this->mixHtml(ClasificacionFinanciamiento::ponClasFinanciamiento($cve_fin));
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Tienen que estar aparte, para que cuando "arme" los valores tome lo último que se haya seleccionado
    y no lo que tenía cuando se dibujó la pantalla :(
    ------------------------------------------------------------------------------------------------------------------------------------------*/
    public function validaCveFinModal()
    {
        // Validamos que todas las variables de sesión de la clave de financiamiento tengan valores
        if ((trim($_SESSION[_S]['cvefin']->cff) == "") || (trim($_SESSION[_S]['cvefin']->fte) == "") || (trim($_SESSION[_S]['cvefin']->sft) == "") || (trim($_SESSION[_S]['cvefin']->ori) == "") || (trim($_SESSION[_S]['cvefin']->sor) == "") || (trim($_SESSION[_S]['cvefin']->cfi) == "") || (trim($_SESSION[_S]['cvefin']->anio) == "")) {
            $this->mixScript('
                notify("Falta información en la clave de financiamiento... revisa por favor", "warning")
                $("#encCveFin").val("");
                $("#encCveFin").attr("ori", "");
                $("#encCveFin").attr("enc", "");
                $("#nomCveFin").val("");
                setTimeout(function() { $("#encCveFin").focus() }, 100);
            ');
            return false;
        }

        $ecf = $_SESSION[_S]['cvefin']->cff . "-" . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . "-" . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;

        $this->mixScript('
            $("#encCveFin").val("' . $ecf . '").blur();
            $("#encCveFin").attr("ori", "' . $ecf . '");
            $("#encCveFin").attr("enc", "' . $_SESSION[_S]['cvefin']->ecf . '");
            $("#nomCveFin").val("' . $_SESSION[_S]['cvefin']->nombre . '");
            setTimeout(function() { $("#encCveFin").focus() }, 100);
        ');
    }
}

if (isset($_POST['metodoCveFin'])) {
    new ClasificacionFinanciamiento();
}
