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

