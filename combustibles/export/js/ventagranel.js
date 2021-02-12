function regresar() {
	url = 'control.php?rqst=MOVIMIENTOS.VENTAGRANEL';
    	document.getElementById('control').src = url;
    	return;
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function mostrarAyuda(url,cod,des,xtra,consulta,des_campo,valor){
	url = url+"?cod="+cod+"&des="+des+"&xtra="+xtra+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function mostrarAyuda2(url,ruc,cod,des,xtra,consulta,des_campo,valor){
	var datox;
	var dato = document.getElementsByName('ruc')[0];
	datox = dato.value;

	url = url+"?ruc="+datox+"&cod="+cod+"&des="+des+"&xtra="+xtra+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}



function actPedido(valor, stk_actual, campopedido){ 
	var newpedido;
	var dato = document.getElementsByName(campopedido)[0];
	if(valor<=0) 
		newpedido = 0;
	else
		newpedido = valor-stk_actual;	
	if(newpedido<0)
		newpedido = 0;
	dato.value = newpedido;	
}
