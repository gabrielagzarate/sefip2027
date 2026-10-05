<?php

require_once dirname(__FILE__) . '/../Entidad.php';
class Clc extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $gpo_id; //Identificador del Grupo de la CLC. cat_UnidadResponsable
    protected $ur_id; //Calve Indentificadora de la Unidad Resposable. Foranea cat_UnidadResponsable
    protected $clc_id; //Numero indentificador de la CLC por Unidad Responsable
    protected $ue_id; //Identificador de la Unidad Ejecutora. Foranea de cat_UnidadEjecutora
    protected $tcp_id; //Identificador del tipo de Compra. Foranea dependiente de cat_TipoCompra
    protected $mpc_id; //Identificador del modo de pago de la CLC, Foranea dependiente de cat_modoPagoClc
    protected $bur_id; //CLABE interbancaria  para el manejo de la CLC, foranea dependiente de cat_BancosUr
    protected $ocl_id; //Indicador del Origen de la CLC, Foranea dependiente de cat_OrigenClc
    protected $ecf_id; //Identificador del Encuadre de Clave de Finaciamiento. Foranea dependiente de enc_ClaveFinanciamiento
    protected $tcl_id; //Identificador del  Tipo de CLC, Foranea dependiente de cat_SubtipoClc
    protected $stc_id; //Identificador del  Subtiipo de CLC, Foranea dependiente de cat_SubtipoClc
    protected $tno_id; //
    protected $smn_id; //Identificador de la submodalidad de Nomina de la CLC. Foranea dependiente de cat_SubmodalidadNomina
    protected $mno_id; //Identificador de la modalidad de Nomina de la CLC. Foranea dependiente de cat_SubmodalidadNomina
    protected $ecl_id; //Indentificador del estado de la CLC, foranea dependiente de cat_EstadoClc
    protected $clc_clave; //Clave de la CLC  se Compone por gpo_id,ur_id,clc_id clc_id con hasta 6 0 a la izquierda.
    protected $clc_concepto; //Concepto de pago de la CLC
    protected $clc_fechaElaboracion; //Fecha en la que la CLC es elaboarada o registrada
    protected $clc_fechaEnvio; //Fecha en la que la CLC es enviada a Finanzas
    protected $clc_fechaAutorizacion; //Fecha en la que la CLC se autoriza la CLC
    protected $clc_fechaCancelacion; //Fecha en la que se cancela la CLC en caso de ser cancelada.
    protected $msc_id; //Mes contable de la CLC 1-12
    protected $clc_tipoCereCo; //Indica el tipo de CERE o CERECO1 = CERE2 = CERECO
    protected $clc_percepcion; //Monto total de las percepciones de la CLC
    protected $clc_retencion; //Monto total de las Retenciones de la CLC
    protected $clc_lineaCaptura; //Linea de captura con la que se pagara la CLC, si existiera una
    protected $clc_beneficiario; //Nombre o datos del beneficiario de la CLC
    protected $clc_firmas; //Indica el número de firmas que llevara la CLC
    protected $clc_cuentaProductiva; //Indica la cuenta productiva para la CLC
    protected $clc_nombreAdministrativo; //Guarda el nombre del Jefe Administrativo para la UR 802 guarda el nombre del presidente municipal
    protected $clc_cargoAdministrativo; //Guarda el cargo del Jefe Administrativo, para la UR  802  el cargo (Presiente Muncipal)
    protected $clc_nombreTitular; //Guarda el nombre del titual de la Dependencia para la UR 802 guarda el nombre del tesorero
    protected $clc_cargoTitular; //Guarda el cargo del Titular de la Dependencia, para la ur 802 el cargo (Tesorero Municipal)
    protected $clc_nombreRecursos; //Guarda el nombre del Director de Recursos Humanos
    protected $clc_nombreAdministracion; //Guarda el Nombre del Secretario de Administracion
    protected $clc_formaPago; //Indica la forma de pago de la CLC SPEI CHEQUE Indefinido
    protected $clc_recursoFederal; //1 indica que la CLC es del proceso de recurso federal 2 indica que la clc es normal
    protected $clc_fondoRotatorio; //S -> fondo Rotatorio N-> normal
    protected $clc_rectificacion; //Indica el numero de rectificación.
    protected $usr_id; //

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getClc_id()
    {
        return $this->clc_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getTcp_id()
    {
        return $this->tcp_id->getVal();
    }
    public function getMpc_id()
    {
        return $this->mpc_id->getVal();
    }
    public function getBur_id()
    {
        return $this->bur_id->getVal();
    }
    public function getOcl_id()
    {
        return $this->ocl_id->getVal();
    }
    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }
    public function getTcl_id()
    {
        return $this->tcl_id->getVal();
    }
    public function getStc_id()
    {
        return $this->stc_id->getVal();
    }
    public function getTno_id()
    {
        return $this->tno_id->getVal();
    }
    public function getSmn_id()
    {
        return $this->smn_id->getVal();
    }
    public function getMno_id()
    {
        return $this->mno_id->getVal();
    }
    public function getEcl_id()
    {
        return $this->ecl_id->getVal();
    }
    public function getElectronica()
    {
        return $this->electronica->getVal();
    }
    public function getEstadoElectronico()
    {
        return $this->estadoElectronico->getVal();
    }
    public function getClave()
    {
        return $this->clc_clave->getVal();
    }
    public function getConcepto()
    {
        return $this->clc_concepto->getVal();
    }
    public function getFechaElaboracion()
    {
        return $this->clc_fechaElaboracion->getVal();
    }
    public function getFechaEnvio()
    {
        return $this->clc_fechaEnvio->getVal();
    }
    public function getFechaAutorizacion()
    {
        return $this->clc_fechaAutorizacion->getVal();
    }
    public function getFechaCancelacion()
    {
        return $this->clc_fechaCancelacion->getVal();
    }
    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getTipoCereCo()
    {
        return $this->clc_tipoCereCo->getVal();
    }
    public function getPercepcion()
    {
        return $this->clc_percepcion->getVal();
    }
    public function getRetencion()
    {
        return $this->clc_retencion->getVal();
    }
    public function getLineaCaptura()
    {
        return $this->clc_lineaCaptura->getVal();
    }
    public function getBeneficiario()
    {
        return $this->clc_beneficiario->getVal();
    }
    public function getFirmas()
    {
        return $this->clc_firmas->getVal();
    }
    public function getCuentaProductiva()
    {
        return $this->clc_cuentaProductiva->getVal();
    }
    public function getNombreAdministrativo()
    {
        return $this->clc_nombreAdministrativo->getVal();
    }
    public function getCargoAdministrativo()
    {
        return $this->clc_cargoAdministrativo->getVal();
    }
    public function getNombreTitular()
    {
        return $this->clc_nombreTitular->getVal();
    }
    public function getCargoTitular()
    {
        return $this->clc_cargoTitular->getVal();
    }
    public function getNombreRecursos()
    {
        return $this->clc_nombreRecursos->getVal();
    }
    public function getNombreAdministracion()
    {
        return $this->clc_nombreAdministracion->getVal();
    }
    public function getFormaPago()
    {
        return $this->clc_formaPago->getVal();
    }
    public function getRecursoFederal()
    {
        return $this->clc_recursoFederal->getVal();
    }
    public function getFondoRotatorio()
    {
        return $this->clc_fondoRotatorio->getVal();
    }
    public function getRectificacion()
    {
        return $this->clc_rectificacion->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /* ESTA CONSULTA SE HIZO PARA  DEVOLVER EL USO DE LA CUENTA BANCARIA YA QUE NO ES UN CAMPO QUE ESTE EN LA TABLA scp_Clc */
    public function getUcb_id()
    {
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql('
            select ucb_id
            from cat_BancosUr
            where bur_id = ? ;
        ');
        $params = [(($this->getBur_id() == '') ? null : $this->getBur_id())];
        $con->consultar($params);
        $con->desconectar();
        if ($con->getNumeroRegistros() > 0) {
            $row = $con->extraer();
            return $row['ucb_id'];
        } else {
            return '';
        }
    }
    /* ESTA CONSULTA DEVUELVE EL ENCUADRE PROGRAMATICO QUE SE HA SELECCIONADO PARA LAS CLC´S DE OBRA  */
    public function getEpg_id()
    {
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql('
            SELECT epg_id
            FROM cat_ObraClc
            WHERE gpo_id= :gpo_id
            AND  ur_id= :ur_id
            AND clc_id = :clc_id;
        ');
        $params = [
            ':gpo_id' => (($this->getGpo_id() == '') ? 'NULL' : $this->getGpo_id()),
            ':ur_id'  => (($this->getUr_id() == '') ? 'NULL' : $this->getUr_id()),
            ':clc_id' => (($this->getClc_id() == '') ? 'NULL' : $this->getClc_id()),
        ];
        $con->consultar($params);
        $con->desconectar();
        if ($con->getNumeroRegistros() > 0) {
            $row = $con->extraer();
            return $row['epg_id'];
        } else {
            return '';
        }
    }

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setClc_id($val)
    {
        $this->clc_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setTcp_id($val)
    {
        $this->tcp_id->setVal($val);
    }
    public function setMpc_id($val)
    {
        $this->mpc_id->setVal($val);
    }
    public function setBur_id($val)
    {
        $this->bur_id->setVal($val);
    }
    public function setOcl_id($val)
    {
        $this->ocl_id->setVal($val);
    }
    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }
    public function setTcl_id($val)
    {
        $this->tcl_id->setVal($val);
    }
    public function setStc_id($val)
    {
        $this->stc_id->setVal($val);
    }
    public function setTno_id($val)
    {
        $this->tno_id->setVal($val);
    }
    public function setSmn_id($val)
    {
        $this->smn_id->setVal($val);
    }
    public function setMno_id($val)
    {
        $this->mno_id->setVal($val);
    }
    public function setEcl_id($val)
    {
        $this->ecl_id->setVal($val);
    }
    public function setClave($val)
    {
        $this->clc_clave->setVal($val);
    }
    public function setConcepto($val)
    {
        $this->clc_concepto->setVal($val);
    }
    public function setFechaElaboracion($val)
    {
        $this->clc_fechaElaboracion->setVal($val);
    }
    public function setFechaEnvio($val)
    {
        $this->clc_fechaEnvio->setVal($val);
    }
    public function setFechaAutorizacion($val)
    {
        $this->clc_fechaAutorizacion->setVal($val);
    }
    public function setFechaCancelacion($val)
    {
        $this->clc_fechaCancelacion->setVal($val);
    }
    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setTipoCereCo($val)
    {
        $this->clc_tipoCereCo->setVal($val);
    }
    public function setPercepcion($val)
    {
        $this->clc_percepcion->setVal($val);
    }
    public function setRetencion($val)
    {
        $this->clc_retencion->setVal($val);
    }
    public function setLineaCaptura($val)
    {
        $this->clc_lineaCaptura->setVal($val);
    }
    public function setBeneficiario($val)
    {
        $this->clc_beneficiario->setVal($val);
    }
    public function setFirmas($val)
    {
        $this->clc_firmas->setVal($val);
    }
    public function setCuentaProductiva($val)
    {
        $this->clc_cuentaProductiva->setVal($val);
    }
    public function setNombreAdministrativo($val)
    {
        $this->clc_nombreAdministrativo->setVal($val);
    }
    public function setCargoAdministrativo($val)
    {
        $this->clc_cargoAdministrativo->setVal($val);
    }
    public function setNombreTitular($val)
    {
        $this->clc_nombreTitular->setVal($val);
    }
    public function setCargoTitular($val)
    {
        $this->clc_cargoTitular->setVal($val);
    }
    public function setNombreRecursos($val)
    {
        $this->clc_nombreRecursos->setVal($val);
    }
    public function setNombreAdministracion($val)
    {
        $this->clc_nombreAdministracion->setVal($val);
    }
    public function setFormaPago($val)
    {
        $this->clc_formaPago->setVal($val);
    }
    public function setRecursoFederal($val)
    {
        $this->clc_recursoFederal->setVal($val);
    }
    public function setFondoRotatorio($val)
    {
        $this->clc_fondoRotatorio->setVal($val);
    }
    public function setRectificacion($val)
    {
        $this->clc_rectificacion->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staClc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staTcp_id($val)
    {
        $this->tcp_id->staVal($val);
    }
    public function staMpc_id($val)
    {
        $this->mpc_id->staVal($val);
    }
    public function staBur_id($val)
    {
        $this->bur_id->staVal($val);
    }
    public function staOcl_id($val)
    {
        $this->ocl_id->staVal($val);
    }
    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }
    public function staTcl_id($val)
    {
        $this->tcl_id->staVal($val);
    }
    public function staStc_id($val)
    {
        $this->stc_id->staVal($val);
    }
    public function staTno_id($val)
    {
        $this->tno_id->staVal($val);
    }
    public function staSmn_id($val)
    {
        $this->smn_id->staVal($val);
    }
    public function staMno_id($val)
    {
        $this->mno_id->staVal($val);
    }
    public function staEcl_id($val)
    {
        $this->ecl_id->staVal($val);
    }
    public function staClave($val)
    {
        $this->clc_clave->staVal($val);
    }
    public function staConcepto($val)
    {
        $this->clc_concepto->staVal($val);
    }
    public function staFechaElaboracion($val)
    {
        $this->clc_fechaElaboracion->staVal($val);
    }
    public function staFechaEnvio($val)
    {
        $this->clc_fechaEnvio->staVal($val);
    }
    public function staFechaAutorizacion($val)
    {
        $this->clc_fechaAutorizacion->staVal($val);
    }
    public function staFechaCancelacion($val)
    {
        $this->clc_fechaCancelacion->staVal($val);
    }
    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }

    public function staTipoCereCo($val)
    {
        $this->clc_tipoCereCo->staVal($val);
    }
    public function staPercepcion($val)
    {
        $this->clc_percepcion->staVal($val);
    }
    public function staRetencion($val)
    {
        $this->clc_retencion->staVal($val);
    }
    public function staLineaCaptura($val)
    {
        $this->clc_lineaCaptura->staVal($val);
    }
    public function staBeneficiario($val)
    {
        $this->clc_beneficiario->staVal($val);
    }
    public function staFirmas($val)
    {
        $this->clc_firmas->staVal($val);
    }
    public function staCuentaProductiva($val)
    {
        $this->clc_cuentaProductiva->staVal($val);
    }
    public function staNombreAdministrativo($val)
    {
        $this->clc_nombreAdministrativo->staVal($val);
    }
    public function staCargoAdministrativo($val)
    {
        $this->clc_cargoAdministrativo->staVal($val);
    }
    public function staNombreTitular($val)
    {
        $this->clc_nombreTitular->staVal($val);
    }
    public function staCargoTitular($val)
    {
        $this->clc_cargoTitular->staVal($val);
    }
    public function staNombreRecursos($val)
    {
        $this->clc_nombreRecursos->staVal($val);
    }
    public function staNombreAdministracion($val)
    {
        $this->clc_nombreAdministracion->staVal($val);
    }
    public function staFormaPago($val)
    {
        $this->clc_formaPago->staVal($val);
    }
    public function staRecursoFederal($val)
    {
        $this->clc_recursoFederal->staVal($val);
    }
    public function staFondoRotatorio($val)
    {
        $this->clc_fondoRotatorio->staVal($val);
    }
    public function staRectificacion($val)
    {
        $this->clc_rectificacion->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(['tabla' => 'scp_Clc']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->clc_id = new Atributo('numero', 'NO', 'PRI', 'clc_id');
        $this->ue_id = new Atributo('numero', 'YES', '', 'ue_id');
        $this->tcp_id = new Atributo('cadena', 'YES', 'MUL', 'tcp_id');
        $this->mpc_id = new Atributo('numero', 'NO', 'MUL', 'mpc_id');
        $this->bur_id = new Atributo('numero', 'NO', 'MUL', 'bur_id');
        $this->ocl_id = new Atributo('numero', 'NO', 'MUL', 'ocl_id');
        $this->ecf_id = new Atributo('numero', 'NO', 'MUL', 'ecf_id');
        $this->tcl_id = new Atributo('cadena', 'NO', 'MUL', 'tcl_id');
        $this->stc_id = new Atributo('numero', 'NO', '', 'stc_id');
        $this->tno_id = new Atributo('numero', 'YES', 'MUL', 'tno_id');
        $this->smn_id = new Atributo('numero', 'NO', '', 'smn_id');
        $this->mno_id = new Atributo('numero', 'NO', 'MUL', 'mno_id');
        $this->ecl_id = new Atributo('numero', 'NO', 'MUL', 'ecl_id');
        $this->clc_clave = new Atributo('cadena', 'NO', '', 'clc_clave');
        $this->clc_concepto = new Atributo('cadena', 'NO', '', 'clc_concepto');
        $this->clc_fechaElaboracion = new Atributo('fecha', 'NO', '', 'clc_fechaElaboracion');
        $this->clc_fechaEnvio = new Atributo('fecha', 'YES', '', 'clc_fechaEnvio');
        $this->clc_fechaAutorizacion = new Atributo('fecha', 'YES', '', 'clc_fechaAutorizacion');
        $this->clc_fechaCancelacion = new Atributo('fecha', 'YES', '', 'clc_fechaCancelacion');
        $this->msc_id = new Atributo('numero', 'NO', '', 'msc_id');
        $this->clc_tipoCereCo = new Atributo('numero', 'NO', 'MUL', 'clc_tipoCereCo');
        $this->clc_percepcion = new Atributo('numero', 'NO', '', 'clc_percepcion');
        $this->clc_retencion = new Atributo('numero', 'NO', '', 'clc_retencion');
        $this->clc_lineaCaptura = new Atributo('cadena', 'NO', '', 'clc_lineaCaptura');
        $this->clc_beneficiario = new Atributo('cadena', 'NO', '', 'clc_beneficiario');
        $this->clc_firmas = new Atributo('numero', 'NO', '', 'clc_firmas');
        $this->clc_cuentaProductiva = new Atributo('cadena', 'NO', '', 'clc_cuentaProductiva');
        $this->clc_nombreAdministrativo = new Atributo('cadena', 'NO', '', 'clc_nombreAdministrativo');
        $this->clc_cargoAdministrativo = new Atributo('cadena', 'NO', '', 'clc_cargoAdministrativo');
        $this->clc_nombreTitular = new Atributo('cadena', 'NO', '', 'clc_nombreTitular');
        $this->clc_cargoTitular = new Atributo('cadena', 'NO', '', 'clc_cargoTitular');
        $this->clc_nombreRecursos = new Atributo('cadena', 'NO', '', 'clc_nombreRecursos');
        $this->clc_nombreAdministracion = new Atributo('cadena', 'NO', '', 'clc_nombreAdministracion');
        $this->clc_formaPago = new Atributo('cadena', 'NO', '', 'clc_formaPago');
        $this->clc_recursoFederal = new Atributo('numero', 'NO', '', 'clc_recursoFederal');
        $this->clc_fondoRotatorio = new Atributo('cadena', 'NO', '', 'clc_fondoRotatorio');
        $this->clc_rectificacion = new Atributo('numero', 'YES', '', 'clc_rectificacion');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $clc = new Clc();
        if (isset($p->gpo_id)) {
            $clc->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $clc->setUr_id($p->ur_id);
        }

        if (isset($p->clc_id)) {
            $clc->setClc_id($p->clc_id);
        }

        if (isset($p->ue_id)) {
            $clc->setUe_id($p->ue_id);
        }

        if (isset($p->tcp_id)) {
            $clc->setTcp_id($p->tcp_id);
        }

        if (isset($p->mpc_id)) {
            $clc->setMpc_id($p->mpc_id);
        }

        if (isset($p->bur_id)) {
            $clc->setBur_id($p->bur_id);
        }

        if (isset($p->ocl_id)) {
            $clc->setOcl_id($p->ocl_id);
        }

        if (isset($p->ecf_id)) {
            $clc->setEcf_id($p->ecf_id);
        }

        if (isset($p->tcl_id)) {
            $clc->setTcl_id($p->tcl_id);
        }

        if (isset($p->stc_id)) {
            $clc->setStc_id($p->stc_id);
        }

        if (isset($p->tno_id)) {
            $clc->setTno_id($p->tno_id);
        }

        if (isset($p->smn_id)) {
            $clc->setSmn_id($p->smn_id);
        }

        if (isset($p->mno_id)) {
            $clc->setMno_id($p->mno_id);
        }

        if (isset($p->ecl_id)) {
            $clc->setEcl_id($p->ecl_id);
        }

        if (isset($p->clave)) {
            $clc->setClave($p->clave);
        }

        if (isset($p->concepto)) {
            $clc->setConcepto($p->concepto);
        }

        if (isset($p->clc_fechaElaboracion)) {
            $clc->setFechaElaboracion($p->clc_fechaElaboracion);
        }

        if (isset($p->fechaEnvio)) {
            $clc->setFechaEnvio($p->fechaEnvio);
        }

        if (isset($p->fechaAutorizacion)) {
            $clc->setFechaAutorizacion($p->fechaAutorizacion);
        }

        if (isset($p->fechaCancelacion)) {
            $clc->setFechaCancelacion($p->fechaCancelacion);
        }

        if (isset($p->msc_id)) {
            $clc->setMsc_id($p->msc_id);
        }

        if (isset($p->tipoCereCo)) {
            $clc->setTipoCereCo($p->tipoCereCo);
        }

        if (isset($p->percepcion)) {
            $clc->setPercepcion($p->percepcion);
        }

        if (isset($p->retencion)) {
            $clc->setRetencion($p->retencion);
        }

        if (isset($p->lineaCaptura)) {
            $clc->setLineaCaptura($p->lineaCaptura);
        }

        if (isset($p->beneficiario)) {
            $clc->setBeneficiario($p->beneficiario);
        }

        if (isset($p->firmas)) {
            $clc->setFirmas($p->firmas);
        }

        if (isset($p->cuentaProductiva)) {
            $clc->setCuentaProductiva($p->cuentaProductiva);
        }

        if (isset($p->nombreAdministrativo)) {
            $clc->setNombreAdministrativo($p->nombreAdministrativo);
        }

        if (isset($p->cargoAdministrativo)) {
            $clc->setCargoAdministrativo($p->cargoAdministrativo);
        }

        if (isset($p->nombreTitular)) {
            $clc->setNombreTitular($p->nombreTitular);
        }

        if (isset($p->cargoTitular)) {
            $clc->setCargoTitular($p->cargoTitular);
        }

        if (isset($p->nombreRecursos)) {
            $clc->setNombreRecursos($p->nombreRecursos);
        }

        if (isset($p->nombreAdministracion)) {
            $clc->setNombreAdministracion($p->nombreAdministracion);
        }

        if (isset($p->formaPago)) {
            $clc->setFormaPago($p->formaPago);
        }

        if (isset($p->recursoFederal)) {
            $clc->setRecursoFederal($p->recursoFederal);
        }

        if (isset($p->fondoRotatorio)) {
            $clc->setFondoRotatorio($p->fondoRotatorio);
        }

        if (isset($p->rectificacion)) {
            $clc->setRectificacion($p->rectificacion);
        }

        if (isset($p->usr_id)) {
            $clc->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : $_SERVER['REMOTE_ADDR'];

        $sql = '';
        if (isset($p->observacion)) {
            $sql = ' SET @bit_observacion="ENT:' . $p->observacion . '",';
        } else {
            $sql = ' SET @bit_observacion="REGISTRO AFECTADO DESDE LA ENTIDAD DE CLC",';
        }

        $sql .= '@usr_id= :usr,@usr_ip=INET_ATON(:ip)';

        $params = [
            ':usr' => $_SESSION[_S]['usr_id'],
            ':ip'  => $ip,
        ];
        $clc->conectar();
        $clc->setSql($sql);
        $clc->consultar($params);

        switch ($p->abc) {
            case 'a':
                $clc->insert(false);
                $clc->setSql('SELECT @clc_id as clc_id;');
                $clc->consultar();
                $clc_id = $clc->extraer();
                $clc->desconectar();
                return $clc_id['clc_id'];
                break;
            case 'b':
                $clc->delete(false);
                break;
            case 'c':
                $clc->update(false);
                break;
        }
    }
}
