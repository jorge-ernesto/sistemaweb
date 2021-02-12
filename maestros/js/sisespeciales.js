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

function anadir_contador(valor){
	conta = document.frmseleccionar.contador.value;
	if (valor.checked)
		conta = parseInt(conta) + 1;
	else
		conta = parseInt(conta) - 1;
	document.frmseleccionar.contador.value=conta;
	return ;
}

function getRegistro(campo){
  url = 'control.php?rqst=FACTURACION.AUTORIZAR&action=setCodigo&task=PRECIOSDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function getRegistronuevo(campo){
  url = 'control.php?rqst=MAESTROS.ESPECIALES&action=setCodigo&task=ESPECIALESDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function getClientes(campo){
  url = 'control.php?rqst=FACTURACION.AUTORIZAR&action=setCodigo&task=PRECIOSDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistro(campo){
 if (document.forms['frmbuscar']){
  		txt_campo = document.getElementsByName('busqueda[codigo]')[0];
  }
  else{
 		txt_campo = document.getElementsByName('datos[ch_codigo_cliente_grupo]')[0];
 		articulo=document.getElementsByName('datos[art_codigo]')[0];
 		articulo.focus();
  }
  txt_campo.value = campo;
  return;
}

function getArticuloNuevo(campo){
  url = 'control.php?rqst=FACTURACION.AUTORIZAR&action=setArticulo&task=PRECIOSDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function getArticulo(campo){
  url = 'control.php?rqst=MAESTROS.ESPECIALES&action=setArticulo&task=ESPECIALESDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function setArticulo(campo){
  txt_campo = document.getElementsByName('datos[art_codigo]')[0];
  precio=document.getElementsByName('datos[nu_preciopactado]')[0];
  precio.focus();
  txt_campo.value = campo;
  return;
}

function deshabilitar_check(){
	check = document.getElementsByName('busqueda[todos]')[0];
	check.value='N';
	return ;
}


function clearCarta(valor){
	carta = document.getElementsByName('datos[ch_cartaref]')[0];
	if (valor=='credito')
    	carta.style.display='inline';
    else 
    	carta.style.display='none';
    
}

function volver_a_registro(){
  paginacion = document.getElementsByName('paginacion[codigo]')[0];
  campo = paginacion.value;
  url = 'control.php?rqst=MAESTROS.ESPECIALES&action=Regresar&task=ESPECIALES&paginacion='+campo;
  document.getElementById('control').src = url;
  return;
}

function volver_a_registro_autorizar(){
  paginacion = document.getElementsByName('paginacion[codigo]')[0];
  campo = paginacion.value;
  url = 'control.php?rqst=FACTURACION.AUTORIZAR&action=Regresar&task=PRECIOS&paginacion='+campo;
  document.getElementById('control').src = url;
  return;
}

function validar_guardar(){
	codigo = document.getElementsByName('datos[ch_codigo_cliente_grupo]')[0];
	fecha = document.getElementsByName('datos[dt_fecha_inicio]')[0];
	articulo = document.getElementsByName('datos[art_codigo]')[0];
	precio = document.getElementsByName('datos[nu_preciopactado]')[0];
	carta = document.getElementsByName('datos[ch_cartaref]')[0];
	fecha_fin = document.getElementsByName('datos[dt_fecha_fin]')[0];
	
	if (codigo.value != '' && fecha.value != '' && articulo.value != '' && precio.value != ''){
		if (precio.value > 0){
			if (confirm('Confirma que desea guardar los datos?')){
				paginacion = document.getElementsByName('paginacion[codigo]')[0];
				paginacion.value=codigo.value;
				return true;
			}
			else return false;
		}else{
			alert('El precio debe ser mayor a cero !!!');
			return false;
		}
	}else{
		alert('Falta ingresar un dato !!!');
		return false;
	}
}

function redireccionar_reportes(){
  codigo=document.getElementsByName('busqueda[codigo]')[0];
  url = 'control.php?rqst=MAESTROS.ESPECIALES&task=ESPECIALES&paginacion[codigo]=' + codigo.value;
  document.getElementById('control').src = url;
  return;
}

function redireccionar_desde_autorizacion(){
	codigo=document.getElementsByName('busqueda[codigo]')[0];
	url = '../maestros/control.php?rqst=MAESTROS.ESPECIALES&task=ESPECIALES&paginacion[codigo]=' + codigo.value;
	document.getElementById('control').src = url;
    return;
}

function confirmar_eliminacion(){
	if (confirm('Desea Eliminar el registro?')){
		return true;
	}else return false;
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

function procesar(e){
	tecla=(document.all)?e.keycode:e.which;
	if (tecla==13) {
		//alert('enter');
		document.forms[0].submit();
		return true;
	}
	else return false;
}