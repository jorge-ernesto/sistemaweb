function regresar() {
	url = 'control.php?rqst=MAESTROS.TANQUES';
    	document.getElementById('control').src = url;
    	return;
}

function BuscarProducto(almacen) {
    control.location.href="control.php?rqst=MAESTROS.TANQUES&action=Buscar&almacen=" + almacen;
}


