function regresar() {
	url = 'control.php?rqst=MOVIMIENTOS.VENTAGRANEL';
    	document.getElementById('control').src = url;
    	return;
}

function fecha(){

	if(document.getElementsByName('desde')[0].value > document.getElementsByName('hasta')[0].value){
		alert("La fecha de inicio debe ser menor a la fecha final");

	}
}


function agregar_ruta() {

	if(document.getElementsByName('dirfiscal')[0].value == ""){
		alert("Este cliente no tiene direccion fiscal seleccionar otro cliente");
	}

	if(document.getElementsByName('ruc')[0].value == ""){
		alert("Falta ingresar Ruc");
	}
 	if(document.getElementsByName('codanexo')[0].value == ""){
		alert("Falta ingresar Codigo Anexo");
	}
	if(document.getElementsByName('galones')[0].value == ""){
		alert("Falta ingresar Galones");
	}
	if(document.getElementsByName('precio')[0].value == ""){
		alert("Falta ingresar Precio");
	}
	if(document.getElementsByName('scop')[0].value == ""){
		alert("Falta ingresar Scop");
	}
	if(document.getElementsByName('diascredito')[0].value == ""){
		alert("Falta ingresar Dias de Credito");
	}
	if(document.getElementsByName('distrito')[0].value == ""){
		alert("Falta ingresar Codigo Distrito");
	}

}

function validar_tipo(opcion) {

	/*if(opcion.value == '1') {
		document.getElementById("producto").value = "GLP-KLS";
	}else if(opcion.value == '0'){
		document.getElementById("producto").value = "GLP-GLN";
	}*/

	if(document.getElementsByName('distrito')[0].value == "0" || document.getElementsByName('distrito')[0].value == 0){
		alert("GLP-KLS");
	
	}
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

function mostrarPlaca(url,cod,des,xtra,consulta,des_campo,valor){
	var posicion_x; 
	var posicion_y; 
	posicion_x=(screen.width/2)-(100/2); 
	posicion_y=(screen.height/2)-(220/2); 
	
	url = url+"?cod="+cod+"&des="+des+"&xtra="+xtra+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	window.open(url,'miwin','width=250,height=220,left='+posicion_x+',top='+posicion_y+',scrollbars=no,menubar=no');
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

function cambiarDisplay(id,can){  

	if (!document.getElementById) return false;
		for(var i=0;i<can;i++){
 			if(('row'+i)==id){
				fila = document.getElementById('row'+i);
				if (fila.style.display != "none") {
					fila.style.display = "none"; //ocultar fila
				}else{
					fila.style.display = ""; //mostrar fila
				}
			}else{
 				fila = document.getElementById('row'+i);
			 	fila.style.display = "none";
			}  
		} 
}

function abrir(programa,janela)
{
   if(janela=="") janela = "janela";
	var posicion_x; 
	var posicion_y; 
	posicion_x=(screen.width/2)-(350/2); 
	posicion_y=(screen.height/2)-(350/2); 
	
   window.open(programa,janela,'height=350,width=350,left='+posicion_x+',top='+posicion_y);
}

function validar(e,tipo){
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;
			//alert("Solo numeros");
			break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9.]/;
			//alert("Solo decimales o numeros");
			break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}
	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

