function getRegistroCli(campo) {
	url = 'control.php?rqst=MOVIMIENTOS.APLICACIONES&action=setRegistroCli&task=APLICACIONES&codigocli='+campo;
	document.getElementById('control').src = url;
	return;
}

function getRegistroCli2(campo) {
	url = 'control.php?rqst=MOVIMIENTOS.ANTICIPOS&action=setRegistroCli&task=ANTICIPOS&codigocli='+campo;
	document.getElementById('control').src = url;
	return;
}

function getRegistroCli3(campo) {
	url = 'control.php?rqst=MOVIMIENTOS.ELIMINACION&action=setRegistroCli&task=ELIMINACION&codigocli='+campo;
	document.getElementById('control').src = url;
	return;
}

function getRegistroCli4(campo) {
	url = 'control.php?rqst=MOVIMIENTOS.INCLUSION&action=setRegistroCli&task=INCLUSION&codigocli='+campo;
	document.getElementById('control').src = url;
	return;
}

function getRegistroCli5(campo) {
	url = 'control.php?rqst=MOVIMIENTOS.PRECANCELACION&action=setRegistroCli&task=PRECANCELADO&codigocli='+campo;
	document.getElementById('control').src = url;
	return;
}

function setRegistroCli(campo) {
	txt_campo = document.getElementsByName('busqueda[codigo]')[0];
	txt_campo.value = campo;
	return;
}

function getRegistro(campo) {
	tdoc = document.getElementsByName('datos[ch_tipdocumento]')[0].value;
	url = 'control.php?rqst=MOVIMIENTOS.PRECANCELACION&action=setRegistro&task=PRECANCELADODET&codigo='+campo;
	document.getElementById('control').src = url;
	return;
}

function setRegistro(campo) {
	txt_campo = document.getElementsByName('datos[ch_sucursal_precancelado]')[0];
	txt_campo.value = campo;
	return;
}

function validar(e,tipo) {
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo) {
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

function confirmarLink(pregunta, accionY, accionN, target) {
	if(confirm(pregunta))
		document.getElementById('control').src = accionY;
	else
		document.getElementById('control').src = accionN;
}

function confirmarForm(pregunta, form) {
	if(confirm(pregunta)) 
		return true;
	return false;
}

function PaginarRegistros(rxp, valor) {
	send = document.getElementsByName('task')[0].value;
	urlPagina = 'control.php?rqst=MOVIMIENTOS.'+send+'&task='+send+'&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;
}

function setCalcularAplicaciones(montos) {
	ts_abono = document.getElementsByName('TotalSaldoAbono')[0].value;
	total_saldo = document.getElementsByName('nu_importesaldo')[0].value;
	checknuevo = document.getElementsByName('chkpormontonota')[0];
	monton = document.getElementsByName('monto')[0];
	var calcular = document.forms['form_aplicaciones'].elements['calcular[]'];
	if(montos.checked == true) {
	    	if (checknuevo.checked) {
			if (calcular.length == undefined) {
				if (!calcular.checked){
					calcular.disabled=true;
				}
			} else {
				for (var i = 0; i < calcular.length; i++){
					if (!calcular[i].checked) {
						calcular[i].disabled = true;
					}
				}
			}
	       	}
		url = 'control.php?rqst=MOVIMIENTOS.APLICACIONES&action=setCalcularAplicaciones&task=APLICACIONESDET&operacion=sumar&montos='+montos.value+'&total_saldo_abono='+ts_abono+'&total_import_saldo='+total_saldo;
		document.getElementById('control').src = url;
		return;
	}else{
		if (checknuevo.checked) {
    			if (calcular.length == undefined) {
				if (!calcular.checked){
					calcular.disabled=false;
				}
			} else {
				for (var i = 0; i < calcular.length; i++) {
					if (!calcular[i].checked){
						calcular[i].disabled=false;
					}
				}
			}
			monton.value='0.00';
		}
		url = 'control.php?rqst=MOVIMIENTOS.APLICACIONES&action=setCalcularAplicaciones&task=APLICACIONESDET&operacion=restar&montos='+montos.value+'&total_saldo_abono='+ts_abono+'&total_import_saldo='+total_saldo;
	        document.getElementById('control').src = url;
		return;
	}
}

function setCalcularAnticipos(montos) {
	ts_abono = document.getElementsByName('TotalSaldoAbono')[0].value;
	total_saldo = document.getElementsByName('nu_importesaldo')[0].value;

	if(montos.checked==true) {
	        url = 'control.php?rqst=MOVIMIENTOS.ANTICIPOS&action=setCalcularAnticipos&task=ANTICIPOSDET&operacion=sumar&montos='+montos.value+'&total_saldo_abono='+ts_abono+'&total_import_saldo='+total_saldo;
        	document.getElementById('control').src = url;
    		return;
    	} else {
	        url = 'control.php?rqst=MOVIMIENTOS.ANTICIPOS&action=setCalcularAnticipos&task=ANTICIPOSDET&operacion=restar&montos='+montos.value+'&total_saldo_abono='+ts_abono+'&total_import_saldo='+total_saldo;
        	document.getElementById('control').src = url;
    		return;
    	}
}

function validar_aplicacion() {
	total_saldo = document.getElementsByName('nu_importesaldo')[0].value;
	ts_abono = document.getElementsByName('TotalSaldoAbono')[0].value;
	chk = document.getElementsByName('chkpormonto')[0];
	fec = document.getElementsByName('fecha')[0].value;
	checknuevo = document.getElementsByName('chkpormontonota')[0];
	emision = document.getElementsByName('datos[dt_fechaemision]')[0].value;

	if (Comparar_Data(emision,fec)) {
		if (parseFloat(total_saldo)<parseFloat(ts_abono) && !checknuevo.checked) {
			alert('No puede aplicar los documentos debido a que el saldo es mayor !!!');
			return false;
		}else {
			if (ts_abono == 0 && !chk.checked) {
				if (checknuevo && checknuevo.checked) 
					alert('Seleccione el documento a aplicar'); 
				else 
					alert('Seleccione la/las documentos a aplicar !!!');
				return false;
			}
		}		
	} else {
		alert('La fecha de aplicacion: '+fec+' es menor a la fecha de emision del documento: '+emision+', corrija la fecha de aplicacion');
		return false;
	}
	if (chk.checked) {
		var monto  = document.getElementsByName('monto')[0];
		var oculto = document.getElementsByName('monto_oculto')[0];

		if (parseFloat(monto.value)>0) {
			if (parseFloat(monto.value)>parseFloat(total_saldo)) {
				alert('El importe a aplicar debe ser menor o igual al saldo del documento a ser aplicado');
 				return false;
			} else {
				if (parseFloat(monto.value)>parseFloat(oculto.value)) {
					alert('Solo puede aplicar hasta :'+oculto.value+' con la moneda especificada');
					return false;
				}
			}
		}else{
			alert('El monto a aplicar debe ser mayor a 0');
			return false;
		}
	}
	sw = 0;

	var moneda_cargo = document.getElementsByName('monedac')[0].value;
	var ocultos = document.forms['form_aplicaciones'].elements['oculto[]'];
	var calcular = document.forms['form_aplicaciones'].elements['calcular[]'];
		
	if (calcular.length == undefined) {
		if (calcular.checked) {
			if (ocultos.value!=moneda_cargo) {
				sw = 1;
			}
		}
	} else {
		for (var i = 0; i < calcular.length; i++) {
			if (calcular[i].checked) {
				if (ocultos[i].value!=moneda_cargo) {
					sw = 1;
				}
			}
		}
	}
	if (sw == 1) {
		alert('Hay documentos con diferente moneda, no los puede aplicar, revise el problema !!!');
		return false;
	}
	if (checknuevo && checknuevo.checked) {
		if (ts_abono == 0){
			alert('Seleccione el documento a aplicar !!!');
			return false;
		}
		var monto  = document.getElementsByName('monto')[0];
		var oculto = document.getElementsByName('monto_oculto')[0];

		if (parseFloat(monto.value) > 0) {
			if (parseFloat(monto.value)>parseFloat(ts_abono)) {
				alert('El importe a aplicar debe ser menor o igual al saldo del documento seleccionado');
				return false;
			} else {
				if (parseFloat(monto.value)>parseFloat(oculto.value)){
					alert('Solo puede aplicar hasta :'+oculto.value+' con la moneda especificada');
					return false;
				}
			}
		} else {
			alert('El monto a aplicar debe ser mayor a 0');
			return false;
		}
	}
	if (confirm('Desea aplicar los documentos?')) 
		return true;
	else 
		return false;
}

function verificar_interfaz() {
	fec = document.getElementsByName('fecha')[0].value;
	hoy = document.getElementsByName('hoy')[0].value;
	if (Comparar_Data(fec,hoy)){
		return true;
	}else{
		alert('La fecha de aplicacion: '+fec+' es mayor a la fecha actual: '+hoy);
		return false;
	}
}

function verificar_check(objeto,cadena){
//alert(objeto + 'cad'+ cadena);
	options = document.forms['form_aplicaciones'].elements['calcular[]'];
	monto = document.getElementsByName('monto')[0];
	checknuevo = document.getElementsByName('chkpormontonota')[0];
	total_saldo = document.getElementsByName('nu_importesaldo')[0].value;

	if (cadena=='ANTICIPOS') 
		url = 'control.php?rqst=MOVIMIENTOS.ANTICIPOS&action=setCalcularAnticipos&task=ANTICIPOSDET&operacion=sumar&montos=0&total_saldo_abono=0&total_import_saldo='+total_saldo;
	else 
		url = 'control.php?rqst=MOVIMIENTOS.APLICACIONES&action=setCalcularAplicaciones&task=APLICACIONESDET&operacion=sumar&montos=0&total_saldo_abono=0&total_import_saldo='+total_saldo;

	monto.value='0.0';

	if (objeto.checked) {		
		if (options.length==undefined) {
			options.checked = false;
			options.disabled = true;			
		} else {
			for (var i = 0; i < options.length; i++){
				options[i].checked=false; 
				options[i].disabled=true;
			}
		}
		//alert('monto:'+monto.disabled+'len:'+options.length);
		if (cadena=='APLICACIONES') 
			checknuevo.disabled=true;		
		monto.disabled=false;
	} else {
		if (options.length == undefined){
			options.disabled=false;
		} else {
			for (var i = 0; i < options.length; i++){
				options[i].disabled=false;
			}
		}
		if (cadena=='APLICACIONES') 
			checknuevo.disabled=false;
		monto.disabled=true;
	}

	document.getElementById('control').src = url;
	return;
}

function aplicar_por_monto_nota() {
	var cheque = document.getElementsByName('chkpormontonota')[0];
	var chec1 = document.getElementsByName('chkpormonto')[0];
	var monto = document.getElementsByName('monto')[0];
	ts_abono = document.getElementsByName('TotalSaldoAbono')[0].value;
    	total_saldo = document.getElementsByName('nu_importesaldo')[0].value;

	chec1.checked=false; 
	if (cheque.checked) {
		chec1.disabled=true;
		monto.disabled=false;
	} else {
		chec1.disabled=false;
		monto.disabled=true;
	}
	monto.value='0.00';
	
/*
	var calcular = document.forms['form_aplicaciones'].elements['calcular[]'];
	if (calcular==undefined) {
		calcular.disabled=false;
		calcular.checked = false;
	} else {
		for (var i = 0; i < calcular.length; i++) {
			calcular[i].disabled = false;
			calcular[i].checked  = false;
		}
	}
*/
	url = 'control.php?rqst=MOVIMIENTOS.APLICACIONES&action=setCalcularAplicaciones&task=APLICACIONESOTROS&operacion=restar&montos='+ts_abono+'&total_saldo_abono='+ts_abono+'&total_import_saldo='+total_saldo;
	document.getElementById('control').src = url;
	return;	
}

function mostrarCliente(valor) {
	var cliente = document.getElementsByName('txtcliente')[0];
	if (valor=='S') {
	    	cliente.style.display='none';
	} else {
		cliente.style.display='inline';
	}
	cliente.value='';
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
	}
	return false;
}

function verificarPrecancelar() {
	var sucursal = document.getElementsByName('datos[ch_sucursal_precancelado]')[0];
	var informo = document.getElementsByName('datos[ccob_informo]')[0];
	var serie = document.getElementsByName('datos[ch_seriedocumento]')[0];
	var numero = document.getElementsByName('datos[ch_numdocumento]')[0];
	var fechasaldo = document.getElementsByName('fechasaldo')[0];
	var fec = document.getElementsByName('datos[dt_fecha_precancelado]')[0];
	
	if (sucursal.value!='' && informo.value!='') {
		if (Comparar_Data(fechasaldo.value,fec.value)) {
			if (confirm('Desea Precancelar el Documento?')) {
				serie.disabled = false;
				numero.disabled = false;
				return true;
			} else {
				return false;	
			}
		} else {
			alert('La fecha de precancelacion ['+fec.value+'] es menor a la fecha del saldo ['+fechasaldo.value+']');
			return false;
		}
	} else {
		alert('Falta ingresar algunos datos !!!');
		return false;
	}
}

function volver_a_detalle() {
	paginacion = document.getElementsByName('busqueda[codigo]')[0];
    	campo = paginacion.value;
    	url = 'control.php?rqst=MOVIMIENTOS.PRECANCELACION&action=Regresar&task=PRECANCELADO&busqueda[codigo]='+campo;
    	document.getElementById('control').src = url;
    	return;
}
