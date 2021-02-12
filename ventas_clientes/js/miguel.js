function mostrarAyuda(url,cod,des,consulta) {
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function mostrarAyudaCD(url,cod,des,consulta) {
	des = formular.new_codigo.value;
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function mostrarAyudaSD(url,cod,consulta) {
	url = url+"?cod="+cod+"&consulta="+consulta;
	window.open(url,'miwin2','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function mostrarProcesar(url,cod,des,consulta) {
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
	window.open(url,'miwin','width=10,height=10,scrollbars=yes,menubar=no,left=2000,top=1000');
}

function mostrarProcesarServicio(url,cod,des,consulta,adicional) {
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&adicional="+adicional;
	window.open(url,'miwin','width=10,height=10,scrollbars=yes,menubar=no,left=2000,top=1000');
}

function mostrarAyudaOrdenCompra(url,prov,alma,many_alma) {
	url = url+"?proveedor="+prov+"&cod_almacen="+alma+"&many_alma="+many_alma;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function cambiarValue(campo, valor) {
	if(campo.value == "") {
		campo.value = valor;
	} else {
		campo.value = "";
	}
}

function getObj(name, nest) {
	if (document.getElementById) {
		return document.getElementById(name).style;
	} else if (document.all) {
		return document.all[name].style;
	} else if (document.layers) {
		if (nest != '') {
			return eval('document.'+nest+'.document.layers["'+name+'"]');
		}
	} else {
		return document.layers[name];
	}
}

function showLayer(layerName, nest) {
	var x = getObj(layerName, nest);
	x.visibility = "visible";
}

function hideLayer(layerName, nest) {
	var x = getObj(layerName, nest);
	x.visibility = "hidden";
}

function mostrarFila(fila) {
	showLayer(fila);
}

function ocultarFila(fila) {
	hideLayer(fila);
}
