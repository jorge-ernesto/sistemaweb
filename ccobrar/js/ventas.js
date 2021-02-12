function cargarListaSerie2() {
	control.location.href='control.php?rqst=REPORTES.GENERALES&action=SerieDocumento';
}

function display_cod_cliente(opcion) {
	var buscar = document.getElementsByName('busqueda[codigo]')[0];
	if(opcion.value == 'N') {
		buscar.style.display = 'inline';
	} else {
		buscar.style.display = 'none';
	}
}

function display_id_cliente(opcion) {
	var buscar = document.getElementsByName('codcliente')[0];
	buscar.style.display = 'inline';
	if(opcion.value == 'N') {
		buscar.style.display = 'inline';
	} else {
		buscar.style.display = 'none';
	}
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

