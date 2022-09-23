function PaginarRegistros(rxp, valor, almacen, dia1, dia2, tipodoc, art_codigo, art_cliente, serie, numero) {   	
	urlPagina = 'control.php?rqst=MOVIMIENTOS.MOVIMIENTOVENTAS&action=Buscar&rxp='+rxp+'&pagina='+valor+'&almacen='+almacen+'&dia1='+dia1+'&dia2='+dia2+'&tipodoc='+tipodoc+'&art_codigo='+art_codigo+'&art_cliente='+art_cliente+'&serie='+serie+'&numero='+numero;
	document.getElementById('control').src = urlPagina;	
}

function regresar() {
	url = 'control.php?rqst=MOVIMIENTOS.MOVIMIENTOVENTAS';
    	document.getElementById('control').src = url;
    	return;
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

