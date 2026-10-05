<?php 
    /** ENTIDAD DE LA UNIDAD EJECUTORA **/
require_once dirname(__FILE__)."/Entidad.php";

class UnidadEjecutora extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $gpo_id; //cat_Grupo - Llave foranea Clave identificadora del Grupo
    protected $ur_id; // Llave foranea Clave identificadora de la UR
    protected $ue_id; //Clave identificadora de la UE
    protected $clave; //TRIGGER AFTER CONCAT( gpo_id, ur_id, ue_id )
    protected $nombre;
    protected $nombreTitular;
    protected $cargoTitular; //
    protected $objetivo; //
    protected $eue_id; //cat_EstadoUe - Llave Foranea
    protected $gpo_idAnterior; //
    protected $ur_idAnterior; //
    protected $ue_idAnterior; //
    protected $abreviaturaPlaneacion; //Abreviatura de la unidad ejecutora
    protected $titularPlaneacion; //Titular para planeacion
    protected $cargoPlaneacion; //Cargo del titular
    protected $ejerceInversionPlaneacion; //1.- Ejerce 2.- No ejerce inversiÃ³n pÃºblica
    protected $departamento; //
    protected $usr_id; //Clave identificadora del usuario que inserto el registro
    protected $telefono;
    protected $extension;
    protected $correoInstitucional;
    protected $celular;
    protected $gafeteInstitucional;
    protected $nombramiento;

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
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getClave()
    {
        return $this->clave->getVal();
    }
    public function getNombre()
    {
        return $this->nombre->getVal();
    }
    public function getNombreTitular()
    {
        return $this->nombreTitular->getVal();
    }
    public function getCargoTitular()
    {
        return $this->cargoTitular->getVal();
    }
    public function getObjetivo()
    {
        return $this->objetivo->getVal();
    }
    public function getEue_id()
    {
        return $this->eue_id->getVal();
    }
    public function getGpo_idAnterior()
    {
        return $this->gpo_idAnterior->getVal();
    }
    public function getUr_idAnterior()
    {
        return $this->ur_idAnterior->getVal();
    }
    public function getUe_idAnterior()
    {
        return $this->ue_idAnterior->getVal();
    }
    public function getAbreviaturaPlaneacion()
    {
        return $this->abreviaturaPlaneacion->getVal();
    }
    public function getTitularPlaneacion()
    {
        return $this->titularPlaneacion->getVal();
    }
    public function getCargoPlaneacion()
    {
        return $this->cargoPlaneacion->getVal();
    }
    public function getEjerceInversionPlaneacion()
    {
        return $this->ejerceInversionPlaneacion->getVal();
    }
    public function getDepartamento()
    {
        return $this->departamento->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getTelefono()
    {
        return $this->telefono->getVal();
    }
    public function getExtension()
    {
        return $this->extension->getVal();
    }
    public function getCorreoInstitucional()
    {
        return $this->correoInstitucional->getVal();
    }
    public function getCelular()
    {
        return $this->celular->getVal();
    }
    public function getGafeteInstitucional()
    {
        return $this->gafeteInstitucional->getVal();
    }
    public function getNombramiento()
    {
        return $this->nombramiento->getVal();
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
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setClave($val)
    {
        $this->clave->setVal($val);
    }
    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }
    public function setNombreTitular($val)
    {
        $this->nombreTitular->setVal($val);
    }
    public function setCargoTitular($val)
    {
        $this->cargoTitular->setVal($val);
    }
    public function setObjetivo($val)
    {
        $this->objetivo->setVal($val);
    }
    public function setEue_id($val)
    {
        $this->eue_id->setVal($val);
    }
    public function setGpo_idAnterior($val)
    {
        $this->gpo_idAnterior->setVal($val);
    }
    public function setUr_idAnterior($val)
    {
        $this->ur_idAnterior->setVal($val);
    }
    public function setUe_idAnterior($val)
    {
        $this->ue_idAnterior->setVal($val);
    }
    public function setAbreviaturaPlaneacion($val)
    {
        $this->abreviaturaPlaneacion->setVal($val);
    }
    public function setTitularPlaneacion($val)
    {
        $this->titularPlaneacion->setVal($val);
    }
    public function setCargoPlaneacion($val)
    {
        $this->cargoPlaneacion->setVal($val);
    }
    public function setEjerceInversionPlaneacion($val)
    {
        $this->ejerceInversionPlaneacion->setVal($val);
    }
    public function setDepartamento($val)
    {
        $this->departamento->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setTelefono($val)
    {
        $this->telefono->setVal($val);
    }
    public function setExtension($val)
    {
        $this->extension->setVal($val);
    }
    public function setCorreoInstitucional($val)
    {
        $this->correoInstitucional->setVal($val);
    }
    public function setCelular($val)
    {
        $this->celular->setVal($val);
    }
    public function setGafeteInstitucional($val)
    {
        $this->gafeteInstitucional->setVal($val);
    }
    public function setNombramiento($val)
    {
        $this->nombramiento->setVal($val);
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
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staClave($val)
    {
        $this->clave->staVal($val);
    }
    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }
    public function staNombreTitular($val)
    {
        $this->nombreTitular->staVal($val);
    }
    public function staCargoTitular($val)
    {
        $this->cargoTitular->staVal($val);
    }
    public function staObjetivo($val)
    {
        $this->objetivo->staVal($val);
    }
    public function staEue_id($val)
    {
        $this->eue_id->staVal($val);
    }
    public function staGpo_idAnterior($val)
    {
        $this->gpo_idAnterior->staVal($val);
    }
    public function staUr_idAnterior($val)
    {
        $this->ur_idAnterior->staVal($val);
    }
    public function staUe_idAnterior($val)
    {
        $this->ue_idAnterior->staVal($val);
    }
    public function staAbreviaturaPlaneacion($val)
    {
        $this->abreviaturaPlaneacion->staVal($val);
    }
    public function staTitularPlaneacion($val)
    {
        $this->titularPlaneacion->staVal($val);
    }
    public function staCargoPlaneacion($val)
    {
        $this->cargoPlaneacion->staVal($val);
    }
    public function staEjerceInversionPlaneacion($val)
    {
        $this->ejerceInversionPlaneacion->staVal($val);
    }
    public function staDepartamento($val)
    {
        $this->departamento->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staTelefono($val)
    {
        $this->telefono->staVal($val);
    }
    public function staExtension($val)
    {
        $this->extension->staVal($val);
    }
    public function staCorreoInstitucional($val)
    {
        $this->correoInstitucional->staVal($val);
    }
    public function staCelular($val)
    {
        $this->celular->staVal($val);
    }
    public function staGafeteInstitucional($val)
    {
        $this->gafeteInstitucional->staVal($val);
    }
    public function staNombramiento($val)
    {
        $this->nombramiento->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_UnidadEjecutora"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->ue_id = new Atributo("numero", "NO", "PRI", "ue_id");
        $this->clave = new Atributo("numero", "NO", "UNI", "ue_clave");
        $this->nombre = new Atributo("cadena", "NO", "", "ue_nombre");
        $this->nombreTitular = new Atributo("cadena", "NO", "", "ue_nombreTitular");
        $this->cargoTitular = new Atributo("cadena", "NO", "", "ue_cargoTitular");
        $this->objetivo = new Atributo("cadena", "NO", "", "ue_objetivo");
        $this->eue_id = new Atributo("numero", "NO", "MUL", "eue_id");
        $this->gpo_idAnterior = new Atributo("numero", "NO", "", "gpo_idAnterior");
        $this->ur_idAnterior = new Atributo("numero", "NO", "", "ur_idAnterior");
        $this->ue_idAnterior = new Atributo("numero", "NO", "", "ue_idAnterior");
        $this->abreviaturaPlaneacion = new Atributo("cadena", "NO", "", "ue_abreviaturaPlaneacion");
        $this->titularPlaneacion = new Atributo("cadena", "NO", "", "ue_titularPlaneacion");
        $this->cargoPlaneacion = new Atributo("cadena", "NO", "", "ue_cargoPlaneacion");
        $this->ejerceInversionPlaneacion = new Atributo("numero", "NO", "", "ue_ejerceInversionPlaneacion");
        $this->departamento = new Atributo("cadena", "NO", "", "ue_departamento");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->telefono = new Atributo("cadena", "SI", "", "ue_telefono");
        $this->extension = new Atributo("cadena", "SI", "", "ue_extension");
        $this->correoInstitucional = new Atributo("cadena", "SI", "", "ue_correoInstitucional");
        $this->celular = new Atributo("cadena", "SI", "", "ue_celular");
        $this->gafeteInstitucional = new Atributo("cadena", "SI", "", "ue_gafeteInstitucional");
        $this->nombramiento = new Atributo("cadena", "SI", "", "ue_nombramiento");
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $ue = new UnidadEjecutora();
        if (isset($p->gpo_id)) {
            $ue->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $ue->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $ue->setUe_id($p->ue_id);
        }
        if (isset($p->clave)) {
            $ue->setClave($p->clave);
        }
        if (isset($p->nombre)) {
            $ue->setNombre($p->nombre);
        }
        if (isset($p->nombreTitular)) {
            $ue->setNombreTitular($p->nombreTitular);
        }
        if (isset($p->cargoTitular)) {
            $ue->setCargoTitular($p->cargoTitular);
        }
        if (isset($p->objetivo)) {
            $ue->setObjetivo($p->objetivo);
        }
        if (isset($p->eue_id)) {
            $ue->setEue_id($p->eue_id);
        }
        if (isset($p->gpo_idAnterior)) {
            $ue->setGpo_idAnterior($p->gpo_idAnterior);
        }
        if (isset($p->ur_idAnterior)) {
            $ue->setUr_idAnterior($p->ur_idAnterior);
        }
        if (isset($p->ue_idAnterior)) {
            $ue->setUe_idAnterior($p->ue_idAnterior);
        }
        if (isset($p->abreviaturaPlaneacion)) {
            $ue->setAbreviaturaPlaneacion($p->abreviaturaPlaneacion);
        }
        if (isset($p->titularPlaneacion)) {
            $ue->setTitularPlaneacion($p->titularPlaneacion);
        }
        if (isset($p->cargoPlaneacion)) {
            $ue->setCargoPlaneacion($p->cargoPlaneacion);
        }
        if (isset($p->ejerceInversionPlaneacion)) {
            $ue->setEjerceInversionPlaneacion($p->ejerceInversionPlaneacion);
        }
        if (isset($p->departamento)) {
            $ue->setDepartamento($p->departamento);
        }
        if (isset($p->usr_id)) {
            $ue->setUsr_id($p->usr_id);
        }
        if (isset($p->telefono)) {
            $ue->setTelefono($p->telefono);
        }
        if (isset($p->telOficina)) {
            $ue->setTelefono($p->telOficina);
        }
        if (isset($p->extension)) {
            $ue->setExtension($p->extension);
        }
        if (isset($p->correoInstitucional)) {
            $ue->setCorreoInstitucional($p->correoInstitucional);
        }
        if (isset($p->correo)) {
            $ue->setCorreoInstitucional($p->correo);
        }
        if (isset($p->celular)) {
            $ue->setCelular($p->celular);
        }
        if (isset($p->telCelular)) {
            $ue->setCelular($p->telCelular);
        }
        if (isset($p->gafeteInstitucional)) {
            $ue->setGafeteInstitucional($p->gafeteInstitucional);
        }
        if (isset($p->nombramiento)) {
            $ue->setNombramiento($p->nombramiento);
        }
        switch ($p->abc) {
            case "a":$ue->insert();return $ue->getUltimoId();
                break;
            case "b":$ue->delete();
                break;
            case "c":$ue->update();
                break;
        }
    }
}
