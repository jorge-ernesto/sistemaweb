function getRegistroCliente(campo){
  url = 'control.php?rqst=FACTURACION.CONTROL&action=setRegistroCli&task=CLIENTE&codigocli='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroCliente(campo){
	var desc = document.getElementsByName('busqueda[codigo]')[0];
	desc.value=campo;
	return;
}

function setRegistroCli(campo){
	var comb = document.getElementsByName('datos[tipo_combustible]')[0];
	comb.focus();
	var desc = document.getElementsByName('datos[cliente]')[0];
	desc.value=campo;
	return;
}

function getRegistroCli(campo){
  url = 'control.php?rqst=FACTURACION.CONTROL&action=setRegistroCli2&task=CLIENTE&codigocli='+campo;
  document.getElementById('control').src = url;
  return;
}

function getArticulo(campo){
  url = 'control.php?rqst=FACTURACION.CONTROL&action=setArticulo&task=CLIENTE&codigoart='+campo;
  document.getElementById('control').src = url;
  return;
}

function setArticulo(campo){
	var lim = document.getElementsByName('datos[lim_importe]')[0];
	lim.focus();
	var desc = document.getElementsByName('datos[tipo_combustible]')[0];
	desc.value=campo;
	return;
}

function volver_a_registro(){
	var campo = document.getElementsByName('busqueda[codigo]')[0].value;
	url = 'control.php?rqst=FACTURACION.CONTROL&action=Buscar&task=CLIENTE&busqueda[codigo]='+campo;
  	document.getElementById('control').src = url;
  	return;
}

function validar_guardar(){
	var codigo = document.getElementsByName('datos[cliente]')[0];
	var combustible = document.getElementsByName('datos[tipo_combustible]')[0];
	var limite_galon = document.getElementsByName('datos[lim_galones]')[0];
	var sal_galon = document.getElementsByName('datos[sal_galones]')[0];
	var limite_importe = document.getElementsByName('datos[lim_importe]')[0];
	var sal_importe = document.getElementsByName('datos[sal_importe]')[0];
	var fec_ini = document.getElementsByName('fec_inicio')[0];
	var fec_fin = document.getElementsByName('fec_fin')[0];
	if (codigo.value==''){
		alert('Error: Ingrese un codigo de Cliente !!!');
		return false;
	}
	
	if (limite_galon.value=='' && limite_importe.value==''){
		alert('Error: Ingrese un limite ya sea de importe o galonaje !!!');
		return false;
	}
	if (fec_fin.value==''){
		alert('Error: Ingrese una fecha de Fin !!!');
		return false;
	}
	if (limite_galon.value!=''){
		if (parseFloat(limite_galon.value)<parseFloat(sal_galon.value)){
			alert('Error: El saldo de galones, debe ser menor o igual al limite !!!');
			return false;
		}
	}
	if (limite_importe.value!=''){
		if (parseFloat(limite_importe.value)<parseFloat(sal_importe.value)){
			alert('Error: El saldo de importe, debe ser menor o igual al limite !!!');
			return false;
		}
	}
	if (!Comparar_Data(fec_ini.value,fec_fin.value)){
		alert('Error: La Fecha de fin es menor a la fecha de inicio !!!');
		return false;
	}
	if (confirm('Desea Grabar el registro?')){
		codigo.disabled=false;
		combustible.disabled=false;
		limite_galon.disabled=false;
		sal_galon.disabled=false;
		limite_importe.disabled=false;
		sal_importe.disabled=false;
		fec_ini.disabled=false;
		fec_fin.disabled=false;
		return true;
	}else{
		return false;
	}
}

function clearAll(){
	var limite_galon = document.getElementsByName('datos[lim_galones]')[0];
	var sal_galon = document.getElementsByName('datos[sal_galones]')[0];
	var limite_importe = document.getElementsByName('datos[lim_importe]')[0];
	var sal_importe = document.getElementsByName('datos[sal_importe]')[0];
	var fec_fin = document.getElementsByName('fec_fin')[0];
	limite_galon.value="";
	limite_importe.value="";
	sal_galon.value="";
	sal_importe.value="";
	fec_fin.value="";
	limite_galon.disabled=false;
	sal_galon.disabled=false;
	limite_importe.disabled=false;
	sal_importe.disabled=false;
}

function validar(e,tipo){
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8) return true;
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

function bloquea(valor1,valor2, valor3, valor4){
   valor4.value=valor1.value;
   if(valor1.value != '' && valor1.value > 0){
   	  valor2.value='';
      valor2.disabled=true;
      valor3.value='';
      valor3.disabled=true;
   }else{
      valor2.disabled=false;
      valor3.disabled=false;
   }
}

function Comparar_Data(String1,String2) {
    //alert(String1+'otro'+String2);
	Data1_arr = String1.split('/')
	Data2_arr = String2.split('/')
	
	String1 = Data1_arr[2] + Data1_arr[1] + Data1_arr[0]
	String2 = Data2_arr[2] + Data2_arr[1] + Data2_arr[0]
	String1 = parseFloat(String1);
	String2 = parseFloat(String2);
	//alert('numero 1:'+String1+' numero 2:'+String2);
	if (String1 <= String2) {
		return true;
	}else{
		return false;
	}
	
}