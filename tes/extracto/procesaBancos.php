<?php

	/* ------------------------------------------------------------------------------------------------------------- */
    // File name   : procesaBancos.php
    // Version     : 1.0.096
    // Begin       : 2017-06-19
    // Last Update : pending
    // -------------------------------------------------------------------
	// This class, processes all FTP files of the banks, obtain the details and record this details into a database, 
    // for its easy comprehension and its use on SINPRES and Tesoreria systems.
    /* ************************************************************************************************************* */
    /*  @autor LI Japhet Mata Arellanes CodeJMA®  Class Protected by 9,565,767 US patent 
    /* ----   Copyright (C) 2002-2014 Japhet Mata - jmacorp.onion LTD ---------------------------------------------- */
    /* ----   External Clases Rights are property of their respective owners --------------------------------------- */
    /* ----   CodeJMA is an international brand of JMGlobal Corporation -------------------------------------------- */
    /* ---------------------------------    Notes for internal purpose only ---------------------------------------- */
    /* ---------------------------------    19/06/2017 ------------------------------------------------------------- */
	/* ------------------------------------------------------------------------------------------------------------- */
	/* ************************************************************************************************************* */
    /* NOTES: External database access is provided by an external class for that purpose 
        PERSONALIZAR LOS MENSAJES DE ERROR EN LAS CONFIGURACIONES _ERR_[N]
    
        debug are not allowed !!
            
        GARANTIA: SI LO ROMPES PUEDES CONSERVAR LOS PEDAZOS
        <VERSION:>
            -TODO SE MANDA A UNA CARPETA TEMPORAL Y DESPUES SE MUEVE A LA CARPETA FINAL
            -SE PRE PROCESA EL FICHERO PARA SACAR FECHA Y DATOS BASICOS DEL FICHERO 
        </VERSION:>
    */
	session_start();
	set_time_limit(0); 
	ini_set('memory_limit', '100M');
	require_once dirname(__FILE__).'/configBancos.php';
	require_once dirname(__FILE__).'/../../lib/AccesoDatos.php';
	require_once dirname(__FILE__).'/../../lib/tes/extracto/procesaExtracto.php';
	require_once dirname(__FILE__).'/../../lib/tes/extracto/RutaExtracto.php';
    class ControladorFTPBanco extends AccesoDatos
    {   
        public function __construct()
        {
            /* NOTHING */
            parent::__construct();
            $fecha = null;
            $fechaFinal = null;
            $banco = null;
			$fechas= null;
            
            if(isset($_GET['fecha']))
                $fecha = $_GET['fecha'];
			
			if(isset($_GET['ff'])){
                $fechaFinal = $_GET['ff'];
				$fechas = $this->arregloFechas($fecha,$fechaFinal); 
			}
			
			if(isset($_GET['b']))
                $banco = $_GET['b'];
            if(is_array($fechas)){
				foreach($fechas as $n) {
					$this->main($n,$banco);
				}
			} else
				$this->main($fecha,$banco);
        }
        public function main($fecha = null,$banco = null )
        {    
            /* CALL TO ALL PROCESSES */
            
            global $meses,$arr_bancos;
            $datetime1  = new DateTime('now');
            $this->logftpBanco(__FUNCTION__,__FUNCTION__);
            
            $RES = null;
            $nuevafecha = 0;
            $bancos = 0;
			if($banco != null){
				if(isset($arr_bancos[$banco]))
					$arr_bancos = array($arr_bancos[$banco]);
			}
			
			
            foreach ($arr_bancos as $ban) {
                $bancos ++;
                $this->logftpBanco(__FUNCTION__,$ban['nombre']);
                
                $fechaFichero = null;
                $d = date("D");
                $tmpf = date('Y-m-d');
                $mes = ucfirst(strtolower($meses[intval(date("m",strtotime($tmpf)))]));
                $mesnumerico = intval(date("m",strtotime($tmpf)));
                
                if ($fecha != null){
                    $xx = strtotime($fecha);
                    $tmpf = date('Y-m-d',$xx);
                    $d = date("D",strtotime($tmpf));
                    $mesnumerico = intval(date("m",strtotime($tmpf)));
                    $mes = ucfirst(strtolower($meses[intval(date("m",strtotime($tmpf)))]));
                }
                $this->logftpBanco(__FUNCTION__,"DIA A OBTENER:".$d);
                $this->logftpBanco(__FUNCTION__,"PARAMETROS DE FECHA:".$ban['datePrm']);
                
                switch ($d) {
                    case "Mon":
                        /*  LOS LUNES LLEGA EL EXTRACTO DEL VIERNES, POR TANTO COMO LLEGA CON LA FECHA
                            DE LOS MOVIMIENTOS, ENTONCES TENEMOS QUE BUSCAR CON -3 DIAS A LA FECHA DE HOY */
                        $nuevafecha = strtotime ( '-3 day' , strtotime ( $tmpf ) ) ;
                        $fechaFichero = date ( $ban['datePrm'] , ($nuevafecha) );
                        
                        break;
                    case "Sun":
                    case "Sat":
                        /*  SABADOS Y DOMINGO S NO DEBE DE LLEGAR NINGUN EXTRACTO POR ESO PONEMOS LA FECHA NORMAL
                            DE TODOS MODOS EL LOG SOLO MARCARA QUE NO EXISTE FICHERO PARA LA FECHA DADA */
                        $fechaFichero = date ($ban['datePrm'],strtotime($tmpf));
                        break;
                    default:
                        /*  TODOS LOS DIAS HABILES, EL EXTRACTO DEL DIA ES DEL DIA ANTERIOR POR ESO 
                            SOLO RESTAMOS UN DIA A LA FECHA ACTUAL PARA BUSCAR EL FICHERO */
                        $nuevafecha = strtotime ( '-1 day' , strtotime ( $tmpf ) ) ;
                        $fechaFichero = date ( $ban['datePrm'] , ($nuevafecha) );
                        break;
                }

                $this->logftpBanco(__FUNCTION__,"FECHA:".$fechaFichero);
                $nombreFichero = $ban['nombreFichero'].$fechaFichero.$ban['extension'];
                $pars = array("iba_id"=>$ban["iba_id"],
                    "nombreFichero"=>$nombreFichero,
                    "bancoDestino"=>$ban['bancoDestino'],
                    "fecha" =>$fecha
                );
                
                /* LLAMAR A LA FUNCION QUE EXTRAE EL FICHERO DEL FTP */
                $RES = $this->getFtpFile($pars);

                if (is_string($RES) || !is_array($RES)) 
                    $this->logftpBanco(__FUNCTION__,_ERR_6.$RES);
                else {
                    /* REGISTRAR EL FICHERO EN LA BASE DE DATOS */
                    $ins = false;
                    $ins = $this->registraFicheroBD($RES);
                    
                    if (is_string($ins) || !is_array($ins)) {
                        $this->logftpBanco(__FUNCTION__,_ERR_7.$ins);
                    }else {
                        if ($ins["error"] == true || $ins['id'] < 1){
                            $this->logftpBanco(__FUNCTION__,_ERR_7.$ins['mensaje'].$ins['error'].$ins['id']);
                        } else {
                            /* LEER Y PROCESAR EL FICHERO  */
                            $proc = $this->procesaFichero($ins);
                            
                            if ( $proc["error"]==true){
                                $this->logftpBanco(__FUNCTION__,_ERR_10.$proc['mensaje']);
                            } else{
                                $this->logftpBanco(__FUNCTION__,_SUCCESS_3);
                            }
                        }
                    }
                }
            }
            $datetime2  = new DateTime('now');
            $interval   = date_diff($datetime1, $datetime2);
            $tiempo     =" :: EL PROCESO TARDO ".( $interval->format('%i minutos %s segundos') );
            $this->logftpBanco(__FUNCTION__,$bancos." PROCESADOS");
            $this->logftpBanco(__FUNCTION__,$tiempo);
        }
        public function getFtpFile($prm)
        {
            /*  OBTIENE EL FICHERO DESDE LA RED ATRAVEZ DEL PROTOCOLO FTP CON LA EXCEPCION 
                DE LOS EXTRACTOS DE INTERACCIONES YA QUE ELLOS MANEJAN MULTIPLES FICHEROS 
                PARA LOS EXTRACTOS BANCARIOS ( UNO POR CUENTA ) 
                
                "iba_id"=>"iba_id",
                "nombreFichero"=>$nombreFichero,
                "bancoDestino"=>$ban['bancoDestino'],
                "fecha" =>$fecha  */
            
            if (!is_array($prm)) {
                $this->logftpBanco(__FUNCTION__,_ERR_1);
                return false;
            }
            /* VARIABLES */
            $nombreFichero = '';  // NOMBRE DEL FICHERO QUE EL BANCO ENTREGA
            $carpetaDestino = './tmpExtracto/';  // LUGAR DONDE SE GUARDARA EL FICHERO
            $fichero = '';  // LUGAR DONDE SE GUARDARA EL FICHERO
            $bancoDestino = '';  // CARPETA DE DONDE SE EXTRAE EL FICHERO EN EL FTP
            
            /* ORIGEN DEL FICHERO DENTRO DEL FTP */
            if (!isset($prm['bancoDestino'])) {
                $this->logftpBanco(__FUNCTION__,_ERR_2);
				return false;
            } else {
                $bancoDestino = $prm['bancoDestino'];
            }

            /* OBTENER FICHERO A BUSCAR */
            if (!isset($prm['nombreFichero'])) {
                $this->logftpBanco(__FUNCTION__,_ERR_3);
				return false;
            } else {
                $nombreFichero = $prm['nombreFichero'];
            }
            
            /* PROCESAMOS TODO LO QUE NO SEA DE INTERACCIONES POR QUE SON UN UNICO FICHERO */
            if ($prm['iba_id'] !="8") {
                /* CONEXION AL SERVIDOR */
                $this->logftpBanco(__FUNCTION__,$prm['iba_id']);
                $ftp_conn = null;
                
                if(!$ftp_conn = ftp_connect(_FTP_SERVER,_FTP_PORT)){
                    $this->logftpBanco(__FUNCTION__,_ERR_5);
                    return false;
                }
                $this->logftpBanco(__FUNCTION__,"CONEXION CORRECTA AL FTP SERVER");
                
                /* LOGUEO */
                if ($login = ftp_login($ftp_conn, _FTP_USER,_FTP_PASSWORD) ){
                    $this->logftpBanco(__FUNCTION__,"AUTENTICACIÓN CORRECTA AL SERVIDOR");
                }else{
                    $this->logftpBanco(__FUNCTION__,"ERROR EN EL INICIO DE SESIÓN");
                    return false;
                }
                
                ftp_pasv($ftp_conn,true);
                $this->logftpBanco(__FUNCTION__,"MODO PASIVO ACTIVADO");
                
                /* IR A LA CARPETA DEL BANCO */ 
                if (ftp_chdir($ftp_conn ,"bancos/".$bancoDestino )) {
                    $this->logftpBanco(__FUNCTION__,"ENTRANDO A DIRECTORIO bancos/".$bancoDestino);
                } else {
                    $this->logftpBanco(__FUNCTION__,"NO SE ENCUENTRA EL DIRECTORIO EN EL SERVIDOR FTP [bancos/".$bancoDestino."]");
                }
                
                /* OBTENER LA POSIBLE LISTA DE ARCHIVOS DEL DIA */
                $files = ftp_nlist($ftp_conn,$nombreFichero);
                
                if (count($files) >0) {
                    /* SI SOLO HAY UNO QUE COINCIDE CON LO REQUERIDO ESE ES NUESTRO ARCHIVO */
                    $fichero = $files[0];
                    /* OBTENEMOS EL FICHERO Y LO RENOMBRAMOS A MD5_FILE */
                    if (ftp_get($ftp_conn, $carpetaDestino."/".$fichero.".txt", $fichero,  FTP_BINARY)) {
                        $this->logftpBanco(__FUNCTION__,"FICHERO OBTENIDO = [".$fichero."]");
                        $final  = strtoupper(md5_file($carpetaDestino."/".$fichero.".txt"));
                        rename($carpetaDestino."/".$fichero.".txt",$carpetaDestino."/".$final.".txt");
                        ftp_close($ftp_conn);
                        $this->logftpBanco(__FUNCTION__,_SUCCESS_1.":: ".$fichero);
                        return array(
                            "destino"=>$carpetaDestino."/".$final.".txt",
                            "nombreOriginal"=>$fichero,
                            "nombre"=>$final,
                            "nombreFichero"=>$final.".txt",
                            "peso"=>filesize($carpetaDestino."/".$final.".txt"),
                            "extension"=>"txt",
                            "iba_id"=>$prm["iba_id"]
                        );
                    } else {
                        $this->logftpBanco(__FUNCTION__,_ERR_13);
                        ftp_close($ftp_conn);
                        return false;
                    }
                } else {
                    $this->logftpBanco(__FUNCTION__,_WAR_2.$nombreFichero);
                    ftp_close($ftp_conn);
                    return false;
                }
            }else{
                /*  PARA EL PROCESO DE INTERACCIONES USAMOS UNA FUNCION DIFERENTE
                    PORQUE SE UNIFICAN TODOS LOS FICHERSO DE TODAS LAS CUENTAS Y SUS DETALLES EN UN 
                    SOLO FICHERO QUE LA LIBRERIA DE EXTRACTOS PUEDA PROCESAR DE UNA SOLA VEZ
                */ 
                $ficheroGenerado = $this->getFtpInteracciones($prm["fecha"]);
                if ( !is_array($ficheroGenerado) ) {
                    $this->logftpBanco(__FUNCTION__,_ERR_13);
                    return false;
                } else {
                    rename($ficheroGenerado['ruta'],$carpetaDestino."/".$ficheroGenerado['archivo']);
                    return array($carpetaDestino."/".$ficheroGenerado['archivo'],
                            $ficheroGenerado['nombre'],
                            $ficheroGenerado['nombre'],
                            $ficheroGenerado['archivo'],
                            $ficheroGenerado['peso'],
                            $prm['iba_id'],
                            "TXT"
                        );
                }
            }
        }
        private function registraFicheroBD( $prm)
        {
            /* LA FUNCION BASICAMENTE RECIBE LOS DATOS DEL FICHERO DESCARGADO DEL H2H
                Y OBTIENE LOS DATOS BASICOS PARA SU REGISTRO EN LA BASE DE DATOS Y SU POSTERIOR PROCESO 
                EN LA TABLA DE DETALLE DE EXTRACTO
                array(
                "destino"=>$carpetaDestino."/".$final.".txt",
                "nombreOriginal"=>$fichero,
                "nombre"=>$final,
                "nombreFichero"=>$final.".txt",
                "peso"=>filesize($carpetaDestino."/".$final.".txt"),
                "extension"=>"txt",
                "iba_id"=>$prm["iba_id"]
            */
            
            $ruta = ''; //LA RUTA DEL FICHERO DESCARGADO
            $nombreOriginal = ''; //LA RUTA DEL FICHERO DESCARGADO
            $nombre = ''; // EL ID DE LA INSTUCION BANCARIA A LA CUAL PERTENECE EL EXTRACTO
            $nombreFichero = ''; // EL ID DE LA INSTUCION BANCARIA A LA CUAL PERTENECE EL EXTRACTO
            $peso = ''; // EL ID DE LA INSTUCION BANCARIA A LA CUAL PERTENECE EL EXTRACTO
            $iab_id = ''; // EL ID DE LA INSTUCION BANCARIA A LA CUAL PERTENECE EL EXTRACTO
            $iba_id = 0; // EL ID DE LA INSTUCION BANCARIA A LA CUAL PERTENECE EL EXTRACTO
            $ext = ''; // EL ID DE LA INSTUCION BANCARIA A LA CUAL PERTENECE EL EXTRACTO
            $estado = '2'; //  INDICA EL ESTADO DEL FICHERO 2 POR PROCESAR,1 PROCESADO 3:CON ERROR
            
            if (!isset($prm['destino']) || $prm['destino'] =="" ) {
                $this->logftpBanco(__FUNCTION__,_ERR_1."0");
                return _ERR_1;
            }
            $ruta = $prm['destino'];
            
            if (!isset($prm['nombreOriginal']) || $prm['nombreOriginal'] == "") {
                $this->logftpBanco(__FUNCTION__,_ERR_1."1");
                return _ERR_1;
            }
            $nombreOriginal = $prm['nombreOriginal'];
            
            if (!isset($prm['nombre'])  || $prm['nombre'] == "") {
                $this->logftpBanco(__FUNCTION__,_ERR_1."2");
                return _ERR_1;
            }
            $nombre = $prm['nombre'];
            
            if (!isset($prm['nombreFichero']) || $prm['nombreFichero'] == "") {
                $this->logftpBanco(__FUNCTION__,_ERR_1."3");
                return _ERR_1;
            }
            $nombreFichero = $prm['nombreFichero'];
            
            if (!isset($prm['peso']) || $prm['peso'] == "") {
                $this->logftpBanco(__FUNCTION__,_ERR_1."4");
                $peso = 0;
            } else {
                $peso = $prm['peso'];
            }
            
            if (!isset($prm['iba_id']) || $prm['iba_id'] == "") {
                $this->logftpBanco(__FUNCTION__,_ERR_1."_iba_id");
            }
            $iba_id = $prm['iba_id'];

            if (!isset($prm['extension'])  || $prm['extension'] == "") {
                $this->logftpBanco(__FUNCTION__,_ERR_1."6");
                return _ERR_1;
            }
            $ext = strtoupper($prm['extension']);
            
            if (!file_exists($ruta)){
                $this->logftpBanco(__FUNCTION__,_ERR_8.__DIR__."/".$ruta);
                return _ERR_8;
            }
            
            $existe = $this->registroExiste($nombre);
            
            if ($existe["existe"] == "SI") {
                $this->logftpBanco(__FUNCTION__,_WAR_3);
                if ($existe['id'] > 0)
                    return array("id"=>$existe['id'],"error"=>false,"estado"=>$estado,"mensaje"=>"EL FICHERO YA EXISTE::".$existe['id']);
                else {
                    $this->logftpBanco(__FUNCTION__,_ERR_11);
                    return _ERR_11;
                }
            }
            
            /* OBTENEMOS LOS DATOS BASICOS DEL FICHERO */
            $procesa = new procesaExtracto();
            $datosFichero = $procesa->getDatosBasicosFichero($ruta,$iba_id);
            
            /* CONTROLAR LOS ERRORRES  */
            if ($datosFichero["error"] > 0) {
                if ($datosFichero["error"] == 1 || $datosFichero["error"]==2) {
                    $this->logftpBanco(__FUNCTION__,_ERR_9.$datosFichero["mensaje"]);
                    return _ERR_9;
                } else {
                    $this->logftpBanco(__FUNCTION__,_WAR_1.$datosFichero["mensaje"]);
                    $estado = 3;
                }
            }
            $peso = $datosFichero["size"];
            $anio = $datosFichero["anio"];
            $mes = $datosFichero["mes"];
            $fecha = $datosFichero["fecha"];
            $idTransaccion = $datosFichero["idTransaccion"];
            $fecha = $datosFichero['fecha'];
            $dia = explode("-",$datosFichero['fecha']);
            $dia = isset($dia[2]) ? $dia[2] :date('d');
            
            /*  INSERTAMOS EL REGISTRO DEL FICHERO EN LA BASE DE DATOS Y REGRESAMOS LOS DATOS DLE FICHERO */
            $sql ='	
               INSERT INTO tesoreria.ban_ArchivoBancario(
                    iba_id,ejr_id,msc_id,arb_dia,
                    arb_nombreOriginal,arb_nombreSistema,
                    arb_fecha,arb_size,arb_numeroLineas,
                    tav_id,sts_id,
                    usr_id
                ) VALUES (
                    '.$iba_id.','.$anio.','.$mes.','.$dia.'
                    "'.$nombreOriginal.'","'.$nombre.'",
                    "'.$fecha.'",'.$peso.',0,
                    "'.$ext.'",1,
                    1
                );';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar(false);
            if ($this->getErrno() >0 ) {
                $this->logftpBanco(__FUNCTION__,_ERR_7.' '.$this->getError());
                $this->desconectar();
                return  array("id"=>0,"error"=>true,"estado"=>3,"mensaje"=>"ok");
            }
            $arb_id = $this->getUltimoId();
            $this->desconectar();
            $this->logftpBanco(__FUNCTION__,_SUCCESS_2.$fch_id);
            return array("id"=>$arb_id,"error"=>false,"estado"=>$estado,"mensaje"=>"ok");
        }
        private function logftpBanco($evento,$data)
        {
            if(strlen($evento)<20){
				while (strlen($evento)<20){
					$evento.=":";
				}
			}
			$rutalog ='./log/RegistroBanco'.date("Ymd").'.txt';
            if(!file_exists('./log/'))
                mkdir('./log/');
            
			$file 		= fopen($rutalog, "a");
			$hoy 		= date("Y/m/d H:i:s").' segundos y '.substr((string)microtime(), 1, 8)." microsegundos";	
			fwrite($file, '[Evento]:'.$evento.'[Hora]:'.$hoy.'[Msg]'.$data.chr(13));
			fclose($file);
        }
        private function procesaFichero($parametros)
        {
            /*  BASICAMENTE ENVIA EL FICHERO A LA LIBRERIA DE DESMEMBRAMIENTO DE EXTRACTOS BANCARIOS
                PARA REGISTRAR LOS DETALLES DE MOVIMIENTOS DE LOS DIFERENTES EXTRACTOS EN LA BASE DE DATOS 
            */
            
            if (!isset($parametros['id']) || $parametros['id']== 0) {
                $this->logftpBanco(__FUNCTION__,_ERR_1);
                return array(__ERROR => true,__MENSAJE => _ERR_1);
            }
            
            $fch_id = $parametros["id"];
            $this->logftpBanco(__FUNCTION__,"PROCESAR FH_ID".$fch_id);
            
            $sql = 'SELECT iab_id,fch_nombreOriginal,fch_idTransaccion 
                FROM Tesoreria.cat_Ficheros 
                WHERE fch_id = '.$fch_id.'';
            
            $this->conectar();
            $this->setSql($sql);
            if(!$this->consultar(false)) {
                $this->logftpBanco(__FUNCTION__,"error en la consulta::".$sql);
                return array(__ERROR => true,__MENSAJE =>"error en la consulta::".$sql);
            }
            $reg = $this->getNumeroRegistros();
            $datos = $this->extraer();
            $this->desconectar();
            
            if ($reg == 0) {
                $this->logftpBanco(__FUNCTION__,_ERR_8);
                return array(__ERROR => true,__MENSAJE => _ERR_8);
                
            }
			$this->conectar();
			$this->setSql(' SELECT COUNT(scm_id)AS Registros 
                FROM Tesoreria.sap_clcMinistrada 
                WHERE scm_transaccion ="'.$datos['fch_idTransaccion'].'" ');
			$this->consultar();
			$datos2 = $this->extraer();
			$this->desconectar();
			
			if ($datos2['Registros']>0) {
				$this->logftpBanco(__FUNCTION__,_WAR_5);
                return array(__ERROR=>true,__MENSAJE=>_WAR_5);
			}
			
            $res = null;
            switch ($datos['iab_id']) {
                case 1:
                    $res = getBanamex($fch_id);
                    break;
				case 8:
				case 17:
                    $res = getBanorte($fch_id);
                    break;
				case 12:
                    $res = getScotia($fch_id);
                    break;
				case 5:
                    $res = getHsbc($fch_id);
                    break;
				case 3:
                    $res = getSantander($fch_id);
                    break;
                case 2:
                    $res = getBancomer($fch_id);
                    break;
                case 10:
                    $res = getIntereacciones($fch_id);
                    break;
				default:
                    $this->logftpBanco(__FUNCTION__,_ERR_2);
                    return array(__ERROR=>true,__MENSAJE=>_ERR_2);
					break;
            }
            
            if (!setAnioSimpres($fch_id)) {
                $this->logftpBanco(__FUNCTION__,_WAR_4);
            }
            if (!$res)
                $this->logftpBanco(__FUNCTION__,_WAR_4);
            else {
                $this->logftpBanco(__FUNCTION__,_SUCCESS_3);
                return array(__ERROR => false,__MENSAJE => _SUCCESS_3);
            }
            
        }
        private function registroExiste($nombre)
        {
            if ( empty($nombre)){
                $this->logftpBanco(__FUNCTION__,_ERR_1);
                return array(__ERROR=>true,__MENSAJE => _ERR_1,"existe"=>false);
            }
            $this->logftpBanco(__FUNCTION__,"VERIFICAR SI EXISTE YA EN LA BASE DE DATOS EL FICHERO:".$nombre);
            
            $sql = ' SELECT fch_id FROM  Tesoreria.cat_Ficheros WHERE fch_nombreSistema = "'.$nombre.'" ';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar();
            $this->desconectar();
            if ($this->getNumeroRegistros()==0) {
                $this->logftpBanco(__FUNCTION__,$nombre." NO EXISTE");
                return array("existe"=>"NO",__ERROR=>false);
            }else {
                $this->logftpBanco(__FUNCTION__,$nombre." SI EXISTE");
                $res = $this->extraer();
                return array("existe"=>"SI",__ERROR=>false,"id" => $res['fch_id']);
            }
        }
        private function logProceso($file)
        {
            $res = file_get_contents($file); 
            return $res;
        }
        private function getFtpInteracciones($fechaprm = null )
        {
            /* PARA AHORRARNOS LOS MULTIPLES ARCHIVOS EN LA BASE DE DATOS
                LO QUE HAREMOS ES UN SOLO ARCHIVO DE TODAS LAS CUENTAS DEL BUZON DEL DIA
                Y LO PROCESAREMOS COMO LOS DEMAS BANCOS 
                ASÍ GUARDAMOS UN SOLO ARCHIVO DE MOVIMIENTOS JUNTANDO LA CABECERAS CON EL DETALLE
            */
            
            /* CONEXION AL SERVIDOR   14 del archivo */
            global $meses;
            $ftp_conn = null;
            $tmpDir = "./tmpExtracto";
            if(!file_exists($tmpDir))
                mkdir($tmpDir,777,true);
            
            $cabeceras = array(); // ARCHIVOS CABECERAS
            $detalles = array();  // ARCHIVOS DETALLE
            
            $fecha = null;
            $d = date("D");
            $tmpf = date('Y-m-j');
            
            if ($fechaprm != null){
                $xx = strtotime($fechaprm);
                $tmpf = date('Y-m-d',$xx);
                $d = date("D",strtotime($tmpf));
                $mesnumerico = intval(date("m",strtotime($tmpf)));
                $mes = ucfirst(strtolower($meses[intval(date("m",strtotime($tmpf)))]));
            }
            
            switch ($d) {
                case "Mon":
                    /*  LOS LUNES LLEGA EL EXTRACTO DEL VIERNES, POR TANTO COMO LLEGA CON LA FECHA
                        DE LOS MOVIMIENTOS, ENTONCES TENEMOS QUE BUSCAR CON -3 DIAS A LA FECHA DE HOY */
                    $nuevafecha = strtotime ( '-3 day' , strtotime ( $tmpf ) ) ;
                    $fecha = date ( 'ymd' , $nuevafecha );
                    break;
                case "Sun":
                case "Sat":
                    /*  SABADOS Y DOMINGO S NO DEBE DE LLEGAR NINGUN EXTRACTO POR ESO PONEMOS LA FECHA NORMAL
                        DE TODOS MODOS EL LOG SOLO MARCARA QUE NO EXISTE FICHERO PARA LA FECHA DADA */
                    $fecha = date ( 'ymd');
                    break;
                default:
                    /*  TODOS LOS DIAS HABILES, EL EXTRACTO DEL DIA ES DEL DIA ANTERIOR POR ESO 
                        SOLO RESTAMOS UN DIA A LA FECHA ACTUAL PARA BUSCAR EL FICHERO */
                    $nuevafecha = strtotime ( '-1 day' , strtotime ( $tmpf ) ) ;
                    $fecha = date ( 'ymd' , $nuevafecha );
                    break;
            }
            
            /* LOS NOMBRES BASICOS DE LOS FICHEROS  */
            $nombreCabecera   = "MCBAN037".$fecha."*.TXT";
            $nombreDetalle    = "MDBAN037".$fecha;
            $archivoTemporal = "./tmpExtracto/tmp_".$fecha.".txt";
            $this->logftpBanco(__FUNCTION__,"SE BUSCA ".$nombreCabecera);
            $this->logftpBanco(__FUNCTION__,"SE BUSCA DETALLE ".$nombreDetalle  );
            
            
            if (!$ftp_conn = ftp_connect(_FTP_SERVER,_FTP_PORT)) {
				$this->logftpBanco(__FUNCTION__,_ERR_5);
				return false;
			}
            
            /*  INICIO DE SESIÓN CON EL SERVIDOR   */
            $login = ftp_login($ftp_conn, _FTP_USER,_FTP_PASSWORD);
            ftp_pasv($ftp_conn,true);
            
            /* IR A LA CARPETA INTERACCIONES */ 
            if ( ! ftp_chdir ($ftp_conn ,"bancos/interacciones") ) {
                $this->logftpBanco(__FUNCTION__,"EL DIRECTORIO bancos/interacciones NO EXISTE EN EL SERVIDOR");
                return false;
            }
            
            /*  OBTENER LA POSIBLE LISTA DE ARCHIVOS DEL DIA 
                DE TODAS LAS CABECERAS Y DETALLES USAMOS SU CONTENIDO Y LO GUARDAMOS EN UN SOLO FICHERO PARA PROCESARLO
                COMO SI FUERA UNO SOLO 
            */
            $files = ftp_nlist($ftp_conn,$nombreCabecera);
            if (count($files) == 0) {
                $this->logftpBanco(__FUNCTION__,"NO SE ENCONTRARON FICHEROS PARA PROCESAR");
                ftp_close($ftp_conn);
                return;
            } else {
                foreach ($files as $cab) {
                    $detalle = $nombreDetalle.substr($cab,14,strlen($cab));
                    
                    if (ftp_get($ftp_conn, $tmpDir."/".$cab, $cab,  FTP_BINARY)) {
                        $this->logftpBanco(__FUNCTION__,"SE OBTIENE ".$cab);
                        $temporal = fopen($archivoTemporal,'a');
                        $cabecera = file_get_contents($tmpDir."/".$cab);
                        fwrite($temporal,$cabecera);
                        unset($cabecera);
                       
                        /* YA QUE OBTUVIMOS LA CABECERA DE LA CUENTA, BUSCAMOS EL DETALLE DE MOVIMIENTOS */
                        if (ftp_get($ftp_conn, $tmpDir."/".$detalle, $detalle,  FTP_BINARY)) {
                            $this->logftpBanco(__FUNCTION__,"SE OBTIENE ".$detalle);
                            $detalle = file_get_contents($tmpDir."/".$detalle);
                            fwrite($temporal,$detalle);
                            unset($detalle);
                        } else {
                            $this->logftpBanco(__FUNCTION__,"NO HAY DETALLE DE ".$cab);
                        }
                        
                        fclose($temporal);
                    } else {
                        $this->logftpBanco(__FUNCTION__,"ERROR AL OBTENER ".$cab.$detalle);
                        ftp_close($ftp_conn);
                        return false;
                    } //FIN ELSE FTP_GET
                } //FIN FOREACH
            } //FIN DEL ELSE
            
            ftp_close($ftp_conn);
            
            /*  APLICAMOS EL MISMO PROCESO QUE A LOS DEMAS EXTRACTOS SOBRE EL CONTENIDO DEL FICHERO
                Y ENVIAMOS DE REGRESO LAS PROPIEDADES NECESARIAS DEL FICHERO PARA SU PROCESAMIENTO  */
                
            $MD5 = strtoupper(md5_file($archivoTemporal));
            rename($archivoTemporal,$tmpDir."/".$MD5.".TXT");
            
            return array(
                "ruta" => $tmpDir."/".$MD5.".TXT",
                "nombre" => $MD5,
                "archivo" => $MD5.".TXT",
                "peso" => filesize($tmpDir."/".$MD5.".TXT"));
        }
		private function arregloFechas($fi,$ff)
		{	
			$fechaInicial = explode("-",$fi);
			$fechafinal = explode("-",$ff);
			$ret = array();
			for ($i = intval($fechaInicial[2]); $i<=intval($fechafinal[2]);$i++) {
				array_push($ret,$fechaInicial[0]."-".$fechaInicial[1]."-".str_pad($i,2,"0",STR_PAD_LEFT));
			}
			return $ret;
		}
	}
	new ControladorFTPBanco();
?>