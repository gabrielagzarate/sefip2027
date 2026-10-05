// Forma de adicionar una clase a la línea de la tabla, de tal manera que indiquemos visual y programáticamente que 
// con ésa línea de datos quieren trabajar
(function($) {

    $.fn.jTableCheckbox = function() {

        // Capturamos el click que presionen sobre cada columna 
        $('tr',this).on('click',function(event){
		
			// quitamos todos los actualmente seleccionados
		    $(this).siblings().removeClass('lineaActual');
            
			// Nos aseguramos que sea una línea del tbody 
			if( $(this).parent().get(0).tagName == 'TBODY' ){

				// Y le ponemos - quitamos la clase 'lineaActual' para indicar que es la actualmente seleccionada
				$(this).toggleClass('lineaActual');

			}
        });

    }

}(jQuery));