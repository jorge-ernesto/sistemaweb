function PaginarRegistros(rxp, valor) {   	
	urlPagina = 'control.php?rqst=MOVIMIENTOS.STOCKTURNO&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosFecha(rxp, valor, fecha, fecha2) {   	
	urlPagina = 'control.php?rqst=MOVIMIENTOS.STOCKTURNO&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosBuscar(rxp, valor, fecha, fecha2){   	
	urlPagina = 'control.php?rqst=MOVIMIENTOS.STOCKTURNO&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}

function regresar() {
	url = 'control.php?rqst=MOVIMIENTOS.STOCKTURNO';
    	document.getElementById('control').src = url;
    	return;
}

function modificar_igv(obj){
	window.document.getElementById("igv").readonly = false; 
}

function gravo_igv(obj){
	window.document.getElementById("igv").readonly = true; 
}

function validar(e,tipo){

	tecla=(document.all)?e.keyCode:e.which;

	if (tecla==13 || tecla==8 || tecla== 0)
		return true;

	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9./]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}

	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

function showUser(str){

	if (str==""){
		document.getElementById("txtHint").innerHTML="";
		return;
	}

	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
		}
	}

	var cu = "/sistemaweb/combustibles/turno.php";

	xmlhttp.open("GET",cu+"?fecha="+str,true);
	xmlhttp.send();

}

