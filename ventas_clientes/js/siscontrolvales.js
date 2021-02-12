function getRegistroCli(campo){
  var tipo = document.getElementsByName('busqueda[radio]')[0];
  if (tipo.value=='0' && tipo.checked){
  	 url = 'control.php?rqst=FACTURACION.VALES&action=setRegistroCli&task=LISTADO&codigocli='+campo;
  	 document.getElementById('control').src = url;
  	 return;
  }
}

function limpiar_caja_busqueda(){
	var codigo = document.getElementsByName('busqueda[codigo]')[0];
	var tipo = document.getElementsByName('busqueda[radio]')[0];
	var desc = document.getElementById('desc_cliente');
	var grid = document.getElementById('resultados_grid');
	var error = document.getElementById('error_body');
	codigo.value = '';
	tipo.value = '0';
	tipo.checked;
	desc.innerHTML = '';
	grid.innerHTML = '';
	error.innerHTML = '';
	codigo.focus();
}

function setRegistroCli(campo){
  var codigo = document.getElementsByName('busqueda[codigo]')[0];
  codigo.value=campo;
  //txt_campo.value = campo;
}

function getRegistro(campo){
  url = 'control.php?rqst=FACTURACION.VALES&action=setRegistro&task=LISTADO&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function getRegistroVale(campo){
  url = 'control.php?rqst=FACTURACION.VALES&action=setRegistroVale&task=LISTADO&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroVale(campo,importe,inicial){
  txt_campo = document.getElementsByName('vales[ch_tipovale]')[0];
  txtimporte = document.getElementsByName('importe')[0];
  txtinicio = document.getElementsByName('vales[ch_numero_inicio]')[0];
  txtfin = document.getElementsByName('vales[ch_numero_fin]')[0];
  if (campo!=''){
  	txt_campo.value = campo;
  	txtfin.value='';
  	txtfin.focus();
  }else{
  	txt_campo.focus();
  }
  txtimporte.value = importe;
  txtinicio.value=inicial;
  return ;
 }

 function quitar_grid(){
 	var grid = document.getElementById('resultados_grid');
 	grid.innerHTML = '';
 }
 
function setRegistro(campo, grupo){
  txt_campo = document.getElementsByName('vales[ch_cliente]')[0];
  txttipo = document.getElementsByName('vales[ch_tipovale]')[0];
  txttarjeta = document.getElementsByName('vales[ch_tarjeta]')[0];
  txtimporte = document.getElementsByName('importe')[0];
  txtincio = document.getElementsByName('vales[ch_numero_inicio]')[0];
  txtfin = document.getElementsByName('vales[ch_numero_fin]')[0];
  desc = document.getElementById('desc_vales');
    
  if (grupo!='NO_GRUPO'){
  	if (grupo!='NO_TARJETA'){
  		txttarjeta.value=grupo;
  		txttipo.focus();
  	}else{
  		txttarjeta.value='';
  	}
  }else{
  	txttarjeta.value='';
  }
  txt_campo.value = campo;
  txttipo.value='';
  txtimporte.value='';
  txtinicio.value='';
  txtfin.value='';
  desc.innerHTML='';
  return ;
  
 }
 
 function validar_registro_vales(){
 	var codigo = document.getElementsByName('vales[ch_cliente]')[0];
 	var tarjeta = document.getElementsByName('vales[ch_tarjeta]')[0];
 	var tipo = document.getElementsByName('vales[ch_tipovale]')[0];
 	var importe = document.getElementsByName('importe')[0];
 	var inicio = document.getElementsByName('vales[ch_numero_inicio]')[0];
 	var fin = document.getElementsByName('vales[ch_numero_fin]')[0];
 	if (codigo.value.length!=6){
 		alert('El codigo de cliente esta errado');
 		return false;
 	}
 	if (tarjeta.value==''){
 		alert('El numero de la tarjeta esta errado');
 		return false;
 	}
 	if (tipo.value.length!=2){
 		alert('El tipo de vale esta errado');
 		return false;
 	}
 	if (importe.value==''){
 		alert('El importe del vale esta errado');
 		return false;
 	}
 	if (fin.value.length!=10){
 		alert('El valor final del vale esta errado');
 		return false;
 	}
 	
 	if (inicio.value>fin.value){
 		alert('El valor final del vale es menor al inicial');
 		return false;
 	}
 	
 	if (confirm('Desea Confirmar los cambios?')) return true;
 	else return false;
 }
 
function validar(e,tipo){
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9.]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
	}
	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

function regresar_a_lista(){
	 url = 'control.php?rqst=FACTURACION.VALES&task=LISTADO';
    document.getElementById('control').src = url;
    return;
}