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

function getRegistroCliente(campo){
  url = 'control.php?rqst=FACTURACION.DESCUENTOS&action=setRegistroCli&task=DESCUENTOS&codigocli='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroCliente(campo){
	var desc = document.getElementsByName('busqueda[codigo]')[0];
	desc.value=campo;
	return;
}
function check(objeto) {
		options = document.forms['frmseleccionar'].elements['chk[]'];
		if (objeto.checked) {
			for (var i = 0; i < options.length; i++){
				options[i].checked = true; 
			}
			//alert('numero '+options.length);
			document.frmseleccionar.contador.value=options.length;
		}
		else {
			for (var i = 0; i < options.length; i++){
				options[i].checked = false; 
			}
			document.frmseleccionar.contador.value=0;
		}
	
}
function anadir_contador(valor){
	conta = document.frmseleccionar.contador.value;
	if (valor.checked)
		conta = parseInt(conta) + 1;
	else
		conta = parseInt(conta) - 1;
	document.frmseleccionar.contador.value=conta;
	return ;
}
function confirmar_autorizar(){
	conta = document.frmseleccionar.contador.value;
	if(conta>0){
		if (confirm('Desea Autorizar los items seleccionados?'))
			return true;
		else return false;
	}else {
		alert('Seleccione un item para autorizar');
		return false;
	}
}
function volver_atras(){
  paginacion = document.getElementsByName('busqueda[codigo]')[0];
  campo = paginacion.value;
  url = 'control.php?rqst=FACTURACION.DESCUENTOS&action=Regresar&task=DESCUENTOS&busqueda[codigo]='+campo;
  document.getElementById('control').src = url;
  return;
}

function getRegistroDesc(campo){
  url = 'control.php?rqst=FACTURACION.DESCUENTOS&action=setRegistroDesc&task=DESCUENTOS&codigodesc='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroDesc(campo){
  txt_campo = document.getElementsByName('descuento')[0];
  txt_campo.value = campo;
  return;
}
function habilitar_caja(){
	var descuento=document.getElementsByName('descuento')[0];
  if(descuento.value!=''){
  	if (confirm('Desea Modificar el porcentaje del cliente?')){
		var codigo=document.getElementsByName('codigo')[0];
		codigo.disabled=false;
		return true;
	}else return false;
  }else{
  	alert('Ingrese un porcentaje de descuento');
  	return false;
  }
	
}