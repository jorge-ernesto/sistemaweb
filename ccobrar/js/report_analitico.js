function display_cod_cliente(valor) {

	if(valor=="01"){
		dis			= 'none';
		fila			= document.getElementById("celda1");
		fila.style.display	= dis;
	}else{
		dis			= 'inline';
		fila			= document.getElementById("celda1");
		fila.style.display	= dis;
	}

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
