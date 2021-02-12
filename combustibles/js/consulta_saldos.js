
function regresar(estacion) {
	url = 'control.php?rqst=MOVIMIENTOS.CONSULTA&action=Consulta&estacion='+estacion;
    	document.getElementById('control').src = url;
    	return;
}





