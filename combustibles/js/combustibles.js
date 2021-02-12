function varillasUpdateSucursal(objeto) {
    control.location.href="control.php?rqst=MOVIMIENTOS.VARILLAS&ch_almacen=" + objeto.value;
}

function varillasRegresar(desde,hasta,sucursal) {
    control.location.href="control.php?rqst=MOVIMIENTOS.VARILLAS&action=Buscar&desde=" + desde + "&hasta=" + hasta + "&ch_almacen=" + sucursal;
}

function CuadreTurnoRegresar(fecha,fecha2) {
    control.location.href="control.php?rqst=MOVIMIENTOS.CUADRETURNO&action=Buscar&fecha=" + fecha + "&fecha2=" + fecha2;
}

function openHelperWindow(name) {
    window.open("/sistemaweb/helper/helper.php?action=ARTICULO&dstname=" + name, "wndHelper", "dependent,with=370,height=400,menubar=no,resizable=no,toolbar=no");
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function MostrarTabla(id){
;

document.getElementById("tabla1").style.display='block';

/*
elem = document.getElementById(id);

elem.style.display='none';

	//if(elem.style.display=='block')mostrado=1;
		
	//if(mostrado!=1)elem.style.display='block';*/

}

function Buscar(almacen){
	urlPagina = 'control.php?rqst=MOVIMIENTOS.INTERFAZQUIPU&task=INTERFAZQUIPU&action=Buscar&almacen='+almacen;
	document.getElementById('control').src = urlPagina;
}

function BuscarDataAlmacen(almacen){
	urlPagina = 'control.php?rqst=MOVIMIENTOS.CONSOLIDACION&almacen='+almacen;
	document.getElementById('control').src = urlPagina;
}

function BuscarDataAlmacenDes(almacen){
	urlPagina = 'control.php?rqst=MOVIMIENTOS.DESCONSOLIDAR&almacen='+almacen;
	document.getElementById('control').src = urlPagina;
}

function ckechDateValid() {
	var sBeing = document.getElementById('fdesde').value;
	var sEnd = document.getElementById('fhasta').value;
	sBeing = sBeing.split("/");
	sEnd = sEnd.split("/");

	sBeing = sBeing[1]+'/'+sBeing[2];
	sEnd = sEnd[1]+'/'+sEnd[2];

	console.log('sBeing: '+sBeing+', sEnd: '+sEnd);

	if(sBeing != sEnd) {
		alert('El rango de fecha debe realizarse en el mismo mes.');
    	return false;
	}
}