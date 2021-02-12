function regresar() {
	url = 'control.php?rqst=REPORTES.PEDIDOCOMPRAS';
    	document.getElementById('control').src = url;
    	return;
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function cargarLista(tipo) {
	control.location.href='control.php?rqst=REPORTES.PEDIDOCOMPRAS&cod=0x&action=' + tipo;
}

function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	alert('URL: '+url);
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function mostrarAyudita(url,cod,des,mes3,mes2,mes1,actual,mini,maxi,canti,consulta,valor) {
	url = url+"?cod="+cod+"&des="+des+"&mes3="+mes3+"&mes2="+mes2+"&mes1="+mes1+"&actual="+actual+"&mini="+mini+"&maxi="+maxi+"&canti="+canti+"&consulta="+consulta+"&valor="+valor;
	window.open(url,'miwin','width=450,height=260,scrollbars=yes,menubar=no,left=390,top=20');
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
