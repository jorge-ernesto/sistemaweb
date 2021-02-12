function cargarListaSerie2() {
	control.location.href='control.php?rqst=REPORTES.GENERALES&action=SerieDocumento';
}

function display_cod_cliente(opcion) {

	var buscar = document.getElementsByName('busqueda[codigo]')[0];

	if(opcion.value == 'N') {
		buscar.style.display = 'none';
	} else {
		buscar.style.display = 'inline';
	}
	buscar.value='';
}

function display_id_cliente(opcion) {
	var buscar = document.getElementsByName('codcliente')[0];
	if(opcion.value == 'N') {
		buscar.style.display = 'none';
	} else {
		buscar.style.display = 'inline';
	}
	buscar.value='';
}


