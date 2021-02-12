function PaginarRegistros(rxp, valor, desde, hasta) {   	
	urlPagina = 'control.php?rqst=PROMOCIONES.DESCUENTOSFIDE&action=Reporte&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function regresar() {
	url = 'control.php?rqst=PROMOCIONES.DESCUENTOSFIDE';
    	document.getElementById('control').src = url;
    	return;
}


