function PaginarRegistros(rxp, valor, desde, hasta) {   	
	urlPagina = 'control.php?rqst=REPORTES.AFERICIONES&action=Reporte&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function regresar() {
	url = 'control.php?rqst=REPORTES.AFERICIONES';
    	document.getElementById('control').src = url;
    	return;
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

