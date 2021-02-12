function ubicaciones(){

	almacen = document.getElementById('estacion').value;

	url = 'control.php?rqst=REPORTES.FORMATOFISICO&action=SetUbicaciones&almacen='+almacen;
  	document.getElementById('control').src = url;
  	return;

}
