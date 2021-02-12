$(document).ready(function() {
	$( document ).keyup(function(event){
		if(event.which == 13){// ENTER = Buscar
			getRankingVentas(1);
		}
	});

	$( '#btn-html-ranking-ventas' ).click(function() {
		getRankingVentas(1);
	});
});