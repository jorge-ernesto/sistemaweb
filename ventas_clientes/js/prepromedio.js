function getRegistroCli(campo) {
  url = 'control.php?rqst=MAESTROS.PRECIOS&action=setRegistroCli&task=PROMEDIO&codigocli='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroCli(campo){
  //var articulo = document.getElmentsByName('art_codigo')[0];
  txt_campo = document.getElementsByName('cli_codigo')[0];
  txt_campo.value = campo;
  //articulo.focus();
  return;
}

function getRegistroArticulo(campo){
  url = 'control.php?rqst=MAESTROS.PRECIOS&action=setRegistroArt&task=PROMEDIO&codigoart='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroArticulo(campo){
	if (!document.getElementsByName('art_codigo')[0]){
		txt_articulo = document.getElementsByName('busqueda[codigo]')[0];
	}else txt_articulo = document.getElementsByName('art_codigo')[0];
	txt_articulo.value = campo;
	return;
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

function verificar_completo(){

	var cliente = document.getElementsByName('cli_codigo')[0];
	var articulo = document.getElementsByName('art_codigo')[0];
	var precio = document.getElementsByName('precio')[0];
	
	if (cliente.value == '' || articulo.value=='' || precio.value == ''){
		alert('Faltan datos !!!');
		return false;
	}else{
		if (precio.value<1){
			alert('El precio debe ser mayor a cero');
		}else{
			if (confirm('Desea registrar el precio para el cliente?')){
				return true;
			}else return false;
		}
	}
	return true;
}

function volver_a_detalle(){
	paginacion = document.getElementsByName('busqueda[codigo]')[0];
	radio = document.getElementsByName('busqueda[radio]')[0];
    campo = paginacion.value;
    campo2 = radio.value;
    url = 'control.php?rqst=MAESTROS.PRECIOS&action=Regresar&task=PROMEDIO&busqueda[codigo]='+campo+'&busqueda[radio]='+campo2;
    document.getElementById('control').src = url;
    return;
}
