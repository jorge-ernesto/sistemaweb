function mostrarDetalle2(linea, almacen, tipo, anio, mes) {
	var nombreTr = "tr" + linea;
	var nombreDiv = "div" + linea;
	var nombreImg = "img" + linea;
	var tr = document.getElementById(nombreTr);
	var div = document.getElementById(nombreDiv);
	var img = document.getElementById(nombreImg);

	if (tr.style.display == "none") {
		tr.style.display = "";
		div.style.display = "";
		img.src = "images/minus.gif";
		control.location.href = "control.php?rqst=REPORTES.MARGENLINEA&action=detalle&linea="+linea+"&estacion="+almacen+"&tipolista="+tipo+"&anio="+anio+"&mes="+mes;
	} else {
		tr.style.display = "none";
		div.style.display = "none";
		img.src = "images/plus.gif";
		div.innerHTML =  "Cargando...";
	}
}
