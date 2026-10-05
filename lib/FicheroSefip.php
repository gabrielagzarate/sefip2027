<?php

declare (strict_types=1);

require_once dirname(__FILE__) . '/sessionSafeStart.php';
sessionSafeStart();
/* ------------------------------------------------------------------------------------------------------------------ */
// File name   : FicheroSefip.php
// Version     : 1.0.0
// Begin       : 2018-04-26
// Last Update : 2022/07/01
// -------------------------------------------------------------------
/* ****************************************************************************************************************** */
/*  @autor LI Japhet Mata Arellanes CodeJMA®  Class Protected by 9,565,768 US patent
/* ----   Copyright (C) 2002-2014 Japhet Mata - jmacorp.onion LTD ----------------------------------------------------- */
/* ----   External Clases Rights are property of their respective owners -------------------------------------------- */
/* ----   CodeJMA is an international brand of JMGlobal Corporation ------------------------------------------------- */
/* ---------------------------------    Notes for internal purpose only -------------------------------------------- */
/* ---------------------------------    --/04/2018 ------------------------------------------------------------------ */
/* ****************************************************************************************************************** */
/* NOTES:  */
require_once 'RutaArchivoSefip.php';

class FicheroSefip extends RutaArchivoSefip
{
    /* VARIABLES DE LA CLASE */
    private $fichero;   /* resource $_FILE */
    private $file;      /* variable para el nombre del fichero */
    private $mensaje;   /* MENSAJE DEL PROCESO */
    private $tipos = [];     /* resource $_FILE */

    public function __construct($prm = null)
    {
        parent::__construct($prm);
        if (is_array($prm)) {
            $this->setParametros($prm);
        }
        if (isset($prm['fichero']) && is_array($prm['fichero'])) {
            $this->fichero = $prm['fichero'];
        }
    }
    /* METODOS GET*/
    public function getFichero()
    {
        return $this->fichero;
    }
    public function getFile()
    {
        return $this->file;
    }
    public function getMensaje()
    {
        return $this->mensaje;
    }
    public function getRuta()
    {
        return $this->getPath();
    }
    public function getTipos()
    {
        return $this->tipos;
    }
    /* METODOS SET */
    public function setFichero($val)
    {
        $this->fichero = $val;
    }
    public function setFile($val)
    {
        $this->file = $val;
    }
    public function setMensaje($val)
    {
        $this->mensaje = (string) $val;
    }
    public function setParametros($val)
    {
        if (is_array($val)) {
            $this->setPrm($val);
        }
    }
    public function setRuta($val)
    {
        $this->setPath($val);
    }
    public function setTipos($val)
    {
        $arr = (array) $val;
        foreach ($arr as $v) {
            array_push($this->tipos, strtoupper($v));
        }
    }
    /* METODOS DE GESTION  */
    public function toString()
    {
        return json_encode($this);
    }
    public function upload($prm = null)
    {
        //METODO PARA SUBIR EL ARCHIVO EN LA RUTA SOLICITADA
        if (is_array($prm)) {
            $this->setParametros($prm);
        }
        if (!$this->validaDatos()) {
            return false;
        }
        if (!$this->validaPrm()) {
            $this->setMensaje($this->getMsg());
            return false;
        }
        if (isset($this->getFichero()['eslocal']) && $this->getFichero()['eslocal']) {
            return rename(
                $this->getFichero()['tmp_name'],
                $this->getRutaMod() . $this->getFile()
            );
        } else {
            return move_uploaded_file(
                $this->getFichero()['tmp_name'],
                $this->getRutaMod() . $this->getFile()
            );
        }
    }
    public function download()
    {
        //DESCARGA EL ARCHIVO CON EL NOMBRE DESDE LA CARPETA
    }
    private function validaDatos()
    {
        if (!is_array($this->getTipos())) {
            $this->setError(true);
            $this->setMensaje('NO SE ESPECIFICARON LOS TIPOS ACEPTADOS');
            return false;
        }

        if ($this->getFile() == '' || empty($this->getFile())) {
            $this->setError(true);
            $this->setMensaje('NO SE ESPECIFICO EL NOMBRE DEL FICHERO A GUARDAR');
            return false;
        }

        if ($this->getFichero() == '' || empty($this->getFichero())) {
            $this->setError(true);
            $this->setMensaje('NO SE ESPECIFICO EL ARCHIVO');
            return false;
        }
        if (isset($this->getFichero()['error']) && $this->getFichero()['error'] != 0) {
            $this->setError(true);
            $this->setMensaje('ERROR EN EL FICHERO');
            return false;
        }
        $extension = pathinfo($this->getFichero()['name'], PATHINFO_EXTENSION);
        $extension = strtoupper($extension);
        if (!in_array($extension, $this->getTipos())) {
            $this->setError(true);
            $this->setMensaje('EL TIPO DE ARCHIVO NO ES PERMITIDO [' . $extension . ']');
            return false;
        }
        if ($this->getModulo() == '' || empty($this->getModulo())) {
            $this->setError(true);
            $this->setMensaje('NO SE INDICO EL MODULO');
            return false;
        }
        $aceptados = ['poa', 'scp', 'mga', 'tes', 'scg', 'programador', 'IP'];
        if (!in_array($this->getModulo(), $aceptados)) {
            $this->setError(true);
            $this->setMensaje('EL MODULO NO ES ACEPTADO');
            return false;
        }
        if ($this->getAnioFichero() == '' || empty($this->getAnioFichero())) {
            $this->setAnioFichero(APP_EJERCICIO);
        }
        return true;
    }
}
