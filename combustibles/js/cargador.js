function enlaceajax(){

$(document).ready(function(){
	$("#enlaceajax").on('click',function(evento){
		evento.preventDefault();
		$("#cargando").css("display", "inline");
		$('#BOTON').attr('disabled','-1');
		$("#destino").load("#", function(){
			$("#cargando").css("display", "none");
			$('#BOTON').removeAttr('disabled');
		});
	});
})


}
