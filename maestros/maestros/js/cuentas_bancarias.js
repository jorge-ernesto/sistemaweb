function PaginarRegistros(rxp, valor) {   	
	urlPagina = 'control.php?rqst=REPORTES.CUENTASBANCARIAS&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosFecha(rxp, valor, fecha, fecha2) {   	
	urlPagina = 'control.php?rqst=REPORTES.CUENTASBANCARIAS&action=Buscar&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosBuscar(rxp, valor, fecha, fecha2){   	
	urlPagina = 'control.php?rqst=REPORTES.CUENTASBANCARIAS&action=Buscar&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function regresar() {
	url = 'control.php?rqst=REPORTES.CUENTASBANCARIAS';
    	document.getElementById('control').src = url;
    	return;
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function validar(e,tipo){

	tecla=(document.all)?e.keyCode:e.which;

	if (tecla==13 || tecla==8 || tecla== 0)
		return true;

	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9./]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}

	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}



