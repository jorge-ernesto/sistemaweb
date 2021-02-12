function mostrarDetalle(linea,f_desde,f_hasta,f_estacion) {
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
		control.location.href = "control.php?rqst=REPORTES.PARTICIPAVENTALINEA&action=detalle&linea=" + linea + "&f_desde=" + f_desde + "&f_hasta=" + f_hasta + "&f_estacion=" + f_estacion;
	}
	else {
		tr.style.display = "none";
		div.style.display = "none";
		img.src = "images/plus.gif";
		div.innerHTML =  "Cargando...";
	}
}
