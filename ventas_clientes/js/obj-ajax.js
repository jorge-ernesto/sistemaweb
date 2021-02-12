function nuevoObj()
{
	var xmlhttp=false;
 	try {
 		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 	} catch (e) {
 		try {
 			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
 		} catch (E) {
 			xmlhttp = false;
 		}
  	}

	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
 		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}

function cargarContenido(dato, divmsg, file)
{
	contenedor = document.getElementById(divmsg);
	ajax=nuevoObj();
	ajax.open("GET", file+"?dato="+dato,true);
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		    if (ajax.status == 200) {
			contenedor.innerHTML = ajax.responseText
		     } else {
			alert('Procesando...');
		     }		
	 	}
	}
	ajax.send(null)
}