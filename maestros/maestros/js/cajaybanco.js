function PaginarRegistros(rxp, valor) {   	
	urlPagina = 'control.php?rqst=REPORTES.CAJAYBANCO&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosFecha(rxp, valor, fecha, fecha2,estacion) {   	
	urlPagina = 'control.php?rqst=REPORTES.CAJAYBANCO&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2+'&estacion='+estacion;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosBuscar(rxp, valor, fecha, fecha2,estacion){   	
	urlPagina = 'control.php?rqst=REPORTES.CAJAYBANCO&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2+'&estacion='+estacion;
	document.getElementById('control').src = urlPagina;	
}

