function regresar() {
	url = 'control.php?rqst=REPORTES.LISTACOMPRAS';
    	document.getElementById('control').src = url;
    	return;
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function cargarLista(tipo) {
	control.location.href='control.php?rqst=REPORTES.LISTACOMPRAS&cod=0x&action=' + tipo;
}

function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	console.log(url);
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function enviadatos(){
	document.formular.submit();
}


function Mostrar(){
document.getElementById('ver').style.display = 'block';
}




