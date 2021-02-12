function getRegistroCli(campo){
  	url = 'control.php?rqst=MOVIMIENTOS.SALDITOS&action=setRegistroCli&task=SALDITOS&codigocli='+campo;
  	document.getElementById('control').src = url;
  	return;
}

function setRegistroCli(campo){
  	txt_campo = document.getElementsByName('busqueda[codigo]')[0];
  	txt_campo.value = campo;
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

function volver_atras(valor){
  	url = 'control.php?rqst=MOVIMIENTOS.SALDITOS&task=SALDITOS&busqueda[codigo]='+valor;
  	document.getElementById('control').src = url;
  	return;
}

function verificar_cancelacion(){
	var saldo = document.getElementsByName('saldo')[0];
	var monto = document.getElementsByName('monto')[0];
	var emision = document.getElementsByName('fechaemision')[0].value;
	var fec = document.getElementsByName('fecha')[0].value;
	if (parseFloat(saldo.value)<parseFloat(monto.value)){
		alert('Error: El monto a cancelar es mayor al saldo');
		return false;
	}else{
		if (parseFloat(monto.value)<=0 || monto.value=='') {
			alert('Error: El monto a cancelar debe se mayor a 0');
			return false;
		} else {
			if (Comparar_Data2(fec,emision)){
				alert('Error: La Fecha de cancelacion:'+fec+' es menor a la de la emision: '+emision);
				return false;
			}else{
				if (confirm('Desea cancelar el Saldo?')) return true;
				else return false;
			}
		}
	}
}

function verificar_interfaz(){
	fec = document.getElementsByName('fecha')[0].value;
	hoy = document.getElementsByName('hoy')[0].value;
	if (Comparar_Data(fec,hoy)){
		return true;
	}else{
		alert('La fecha de aplicacion: '+fec+' es mayor a la fecha actual: '+hoy);
		return false;
	}
}

function Comparar_Data(String1,String2) {
    	Data1_arr = String1.split('/')
	Data2_arr = String2.split('/')
	String1 = Data1_arr[2] + Data1_arr[1] + Data1_arr[0]
	String2 = Data2_arr[2] + Data2_arr[1] + Data2_arr[0]
	String1 = parseFloat(String1);
	String2 = parseFloat(String2);
	if (String1 <= String2) {
		return true;
	}
	return false;
}

function Comparar_Data2(String1,String2) {
    	Data1_arr = String1.split('/')
	Data2_arr = String2.split('/')
	String1 = Data1_arr[2] + Data1_arr[1] + Data1_arr[0]
	String2 = Data2_arr[2] + Data2_arr[1] + Data2_arr[0]
	String1 = parseFloat(String1);
	String2 = parseFloat(String2);
	if (String1 < String2) {
		return true;
	}
	return false;
}

