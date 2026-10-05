<?php
declare (strict_types = 1);
class form 
{
	private $id;
	private $controlador;
	private $metodoValidador;
	private $datosExtra;
	private $class;
	private $funcCall = array();
	private $html='';
	public function __construct($p)
	{
		$this->id 			   	  		   = ( isset($p->id) ) 				?$p->id 			 : '';
		$this->controlador 	   	  		   = ( isset($p->controlador) ) 	?$p->controlador 	 : '';
		$this->class 	   	  		   = ( isset($p->class) ) 	?$p->class 	 : '';
		$this->datosExtra				   = ( isset($p->datosExtra) ) 		?$p->datosExtra 	 : new stdClass();
		$this->datosExtra->metodo		   = ( isset($p->metodo) ) 			?$p->metodo 		 : '';
		$this->datosExtra->metodoValidador = ( isset($p->metodoValidador) ) ?$p->metodoValidador : '';
	}
	public function mixHtml($html)
	{ 
		$this->html .= $html; 
	}
	public function getHtml()
	{
		return $this->html;
	}
	public function addFuncCall($val)
	{
		array_push( $this->funcCall, $val );
	}
	public function ponForm($tipoRespuesta = 'json', $contenedor = 'contenedor')
	{
			// echo json_encode($this->datosExtra);
			$extra='';
			foreach($this->datosExtra as $key => $value){
				$value = (string)($value ?? '');
				if(trim( $value ) == "" || strpos($value, '"{') === false ) {
					$value = '"'.$value.'"';
				} else {
					$value = str_replace(array('"{','}"'),'',$value);
				}

				$extra .= 'var datoScript = '.$value.'; formData.append("'.$key.'",datoScript);';
			}
			$de = json_encode($this->datosExtra);
			$de = str_replace(array('"{','}"'),'',$de);
			if( $tipoRespuesta != 'json' ){
				$contex = ',context: $("#'.$contenedor.'")}).done(function ( data ) { this.html(data);';
			}else
				$contex = ' }).done(function ( data ) { comunicaCallback(data.comunica);';
			$ifFunciones1 = '';
			$ifFunciones2 = '';
			$detonacionFunciones = '';
			if( count($this->funcCall) > 0 ){
				$funciones = '';
				foreach($this->funcCall as $nombreFuncion){
					$funciones .= '
						$(".'.$nombreFuncion.'").each(
							function(){
								valido = valido && '.$nombreFuncion.'(this); 
							});
					';
					$detonacionFunciones .= '
					$( document ).off( "blur", ".'.$nombreFuncion.'" );
					$( document ).on( "blur", ".'.$nombreFuncion.'", function() { console.log(this);'.$nombreFuncion.'(this); });
					';
				}
				$ifFunciones1 = '
					var valido = true;
					'.$funciones.'
					if(valido){
				';
				$ifFunciones2 = '}';
			}
			if( !isset($_SESSION[_S]['scriptFormulario']) ) $_SESSION[_S]['scriptFormulario'] = '';
			$_SESSION[_S]['scriptFormulario'] .= '
				$("[form='.$this->id.'][type=submit]").click(function(){
					// var validacionElementos = validarRequeridos("[form='.$this->id.'],#'.$this->id.' [required]");					
					if(!validarRequeridos("[form='.$this->id.'],#'.$this->id.' [required]")){ return false; }
				});
				$("#'.$this->id.'").submit(function(e) {
                    let html =`
					<div id="loading" style="background-color:rgba(0,0,0,.4); position:fixed;top:0px; left:0px; width:100%; height:100%; z-index:100001">
						<div style="background-color:#FFF; display:flex; justify-content: center; align-items:center; flex-direction:column;
							border-bottom: 3px solid #EAEAEA;border-top: 3px solid #EAEAEA;min-height:150px; box-shadow:#666 0 5px 16px;
							width:100%; position:fixed;left:0px; top: 50vh;transform: translate(0px, -80%);font-family:\'UniviaPro-Regular\'">
							<img src=\'img/sefip-min.png\' width="56px" height="56px" alt="Sefip Logo" style="margin-bottom:10px;" />
							<div> P R O C E S A N D O</div>
						</div>
					</div>`;
				$("body").append(html);
                    
                    
					e.preventDefault();
					'.$ifFunciones1.'
						$(".entero,.moneda,.numero,.decimal").each(function(){
							$(this).val( unformatNumber( $(this).val() ) );
						});
						var formData = new FormData(this);
						'.$extra.'
						$.ajax({
							url: "'.$this->controlador.'",
							data: formData,
							type: "POST",
							dataType: "'.$tipoRespuesta.'",
							contentType: false,
							processData:false
						'.$contex.'
						}).fail(function(data) {
							switch (data.status) {
								case 400:
									mm = JSON.parse(data.responseText);
									comunicaCallback(mm.comunica);
                                    break;
                                default:
                                    errorCallback(data);
                                    notify("ocurrio un Error","error");
                                    break;
                            }
                            $("#progressbar").remove();
                            $("#loading").remove();
						}).always(function(){
                            $("#progressbar").remove();
                            $("#loading").remove();
                        });
					'.$ifFunciones2.'
				});
				'.$detonacionFunciones.'
				$("#'.$this->id.' input.validaAjax").blur( function(){
					p = {};
					p["metodoValidador"] = "'.$this->metodoValidador.'";
					p[$(this).attr("name")] =  $(this).val();
					comunica( "'.$this->controlador.'",p);
				});
				function error(field,msg){
					$(field).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  margin-top: -35px; opacity: 0.87; position: static; "><div class="formErrorContent">\'+msg+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');						
					$( ".formError" ).show("pulsate");					
					setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
					setTimeout(function() { $( ".formError" ).remove(); }, 4500 );
				}
			';
			return ' <form id="'.$this->id.'" class="'.$this->class.'" action="'.$this->controlador.'" enctype="multipart/form-data">'.$this->html.'</form> ';
		}
}
