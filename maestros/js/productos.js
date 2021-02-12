function regresar() {
	url = 'control.php?rqst=MAESTROS.CONFIGPRODUCTO';
    	document.getElementById('control').src = url;
    	return;
}

function AgregarProducto(fecha,fecha2) {
    control.location.href="control.php?rqst=MAESTROS.PRODUCTO&action=Agregar&fecha=" + fecha + "&fecha2=" + fecha2;
}

function validar(e,tipo){
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9.]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}
	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

