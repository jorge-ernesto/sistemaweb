function PaginarRegistros(rxp, valor) {   	
	urlPagina = 'control.php?rqst=REPORTES.VARILLAS&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosFecha(rxp, valor, fecha, fecha2) {   	
	urlPagina = 'control.php?rqst=REPORTES.VARILLAS&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosBuscar(rxp, valor, fecha, fecha2){   	
	urlPagina = 'control.php?rqst=REPORTES.VARILLAS&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}

function regresar() {
	url = 'control.php?rqst=REPORTES.VARILLAS';
    	document.getElementById('control').src = url;
    	return;
}

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function validar(e,tipo){

	tecla=(document.all)?e.keyCode:e.which;

	if (tecla==13 || tecla==8 || tecla== 0)
		return true;

	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9./]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}

	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

function suma2(){


	var sum1 = document.getElementsByName('surtidor_m3')[0];
	var sum2 = document.getElementsByName('tot_cantidad')[0];
	resultado = parseFloat(sum1.value) - parseFloat(sum2.value);
	document.getElementsByName('mermas')[0].value= resultado;

}

function contometros(){


	var sum1 = document.getElementById('cnt_final');
	var sum2 = document.getElementById('cnt_inicial');
	var sum3 = document.getElementById('surtidor_m3');

	resultado = parseFloat(sum1.value) - parseFloat(sum2.value);
	merma = parseFloat(sum3.value) - resultado;

	document.getElementsByName('tot_cantidad')[0].value= resultado.toFixed(2);
	document.getElementsByName('mermas')[0].value= merma.toFixed(2);

}



function Guardar(){

	ch_almacen		= document.getElementsByName('ch_almacen')[0].value;
	dt_fecha		= document.getElementsByName('dt_fecha')[0].value;
	surtidor_soles		= document.getElementsByName('surtidor_soles')[0].value;
	surtidor_m3		= document.getElementsByName('surtidor_m3')[0].value;
	cnt_inicial		= document.getElementsByName('cnt_inicial')[0].value;
	cnt_final		= document.getElementsByName('cnt_final')[0].value;
	tot_cantidad		= document.getElementsByName('tot_cantidad')[0].value;
	tot_venta		= document.getElementsByName('tot_venta')[0].value;
	mermas			= document.getElementsByName('mermas')[0].value;
	tot_abono		= document.getElementsByName('tot_abono')[0].value;
	tot_afericion		= document.getElementsByName('tot_afericion')[0].value;
	tot_cli_credito		= document.getElementsByName('tot_cli_credito')[0].value;
	tot_cli_anticipo	= document.getElementsByName('tot_cli_anticipo')[0].value;
	tot_tar_credito		= document.getElementsByName('tot_tar_credito')[0].value;
	tot_descuentos		= document.getElementsByName('tot_descuentos')[0].value;
	tot_trab_faltantes	= document.getElementsByName('tot_trab_faltantes')[0].value;
	tot_trab_sobrantes	= document.getElementsByName('tot_trab_sobrantes')[0].value;
	tot_soles		= document.getElementsByName('tot_soles')[0].value;
	tot_dolares		= document.getElementsByName('tot_dolares')[0].value;

	if(surtidor_soles == ''){
		surtidor_soles = 0.00;
	}

	if(surtidor_m3 == ''){
		surtidor_m3 = 0.00;
	}

	if(cnt_inicial == ''){
		cnt_inicial = 0.00;
	}

	if(cnt_final == ''){
		cnt_final = 0.00;
	}

	if(tot_cantidad == ''){
		tot_cantidad = 0.00;
	}

	if(tot_venta == ''){
		tot_venta = 0.00;
	}

	if(mermas == ''){
		mermas = 0.00;
	}

	if(tot_abono == ''){
		tot_abono = 0.00;
	}

	if(tot_afericion == ''){
		tot_afericion = 0.00;
	}

	if(tot_cli_credito == ''){
		tot_cli_credito = 0.00;
	}

	if(tot_cli_anticipo == ''){
		tot_cli_anticipo = 0.00;
	}

	if(tot_tar_credito== ''){
		tot_tar_credito = 0.00;
	}

	if(tot_descuentos == ''){
		tot_descuentos = 0.00;
	}

	if(tot_trab_faltantes == ''){
		tot_trab_faltantes = 0.00;
	}

	if(tot_trab_sobrantes == ''){
		tot_trab_sobrantes = 0.00;
	}

	if(tot_soles == ''){
		tot_soles = 0.00;
	}

	if(tot_dolares == ''){
		tot_dolares = 0.00;
	}

	url = 'control.php?rqst=REPORTES.VARILLAS&action=Guardar&ch_almacen='+ch_almacen+'&dt_fecha='+dt_fecha+'&surtidor_soles='+surtidor_soles+'&surtidor_m3='+surtidor_m3+'&cnt_inicial='+cnt_inicial+'&cnt_final='+cnt_final+'&tot_cantidad='+tot_cantidad+'&tot_venta='+tot_venta+'&mermas='+mermas+'&tot_abono='+tot_abono+'&tot_afericion='+tot_afericion+'&tot_cli_anticipo='+tot_cli_anticipo+'&tot_tar_credito='+tot_tar_credito+'&tot_descuentos='+tot_descuentos+'&tot_trab_faltantes='+tot_trab_faltantes+'&tot_trab_sobrantes='+tot_trab_sobrantes+'&tot_soles='+tot_soles+'&tot_dolares='+tot_dolares+'&tot_cli_credito='+tot_cli_credito;
  	document.getElementById('control').src = url;

}

function Actualizar(){

	ch_almacen		= document.getElementsByName('ch_almacen')[0].value;
	dt_fecha		= document.getElementsByName('dt_fecha')[0].value;
	surtidor_soles		= document.getElementsByName('surtidor_soles')[0].value;
	surtidor_m3		= document.getElementsByName('surtidor_m3')[0].value;
	cnt_inicial		= document.getElementsByName('cnt_inicial')[0].value;
	cnt_final		= document.getElementsByName('cnt_final')[0].value;
	tot_cantidad		= document.getElementsByName('tot_cantidad')[0].value;
	tot_venta		= document.getElementsByName('tot_venta')[0].value;
	mermas			= document.getElementsByName('mermas')[0].value;
	tot_abono		= document.getElementsByName('tot_abono')[0].value;
	tot_afericion		= document.getElementsByName('tot_afericion')[0].value;
	tot_cli_credito		= document.getElementsByName('tot_cli_credito')[0].value;
	tot_cli_anticipo	= document.getElementsByName('tot_cli_anticipo')[0].value;
	tot_tar_credito		= document.getElementsByName('tot_tar_credito')[0].value;
	tot_descuentos		= document.getElementsByName('tot_descuentos')[0].value;
	tot_trab_faltantes	= document.getElementsByName('tot_trab_faltantes')[0].value;
	tot_trab_sobrantes	= document.getElementsByName('tot_trab_sobrantes')[0].value;
	tot_soles		= document.getElementsByName('tot_soles')[0].value;
	tot_dolares		= document.getElementsByName('tot_dolares')[0].value;

	if(surtidor_soles == ''){
		surtidor_soles = 0.00;
	}

	if(surtidor_m3 == ''){
		surtidor_m3 = 0.00;
	}

	if(cnt_inicial == ''){
		cnt_inicial = 0.00;
	}

	if(cnt_final == ''){
		cnt_final = 0.00;
	}

	if(tot_cantidad == ''){
		tot_cantidad = 0.00;
	}

	if(tot_venta == ''){
		tot_venta = 0.00;
	}

	if(mermas == ''){
		mermas = 0.00;
	}

	if(tot_abono == ''){
		tot_abono = 0.00;
	}

	if(tot_afericion == ''){
		tot_afericion = 0.00;
	}

	if(tot_cli_credito == ''){
		tot_cli_credito = 0.00;
	}

	if(tot_cli_anticipo == ''){
		tot_cli_anticipo = 0.00;
	}

	if(tot_tar_credito== ''){
		tot_tar_credito = 0.00;
	}

	if(tot_descuentos == ''){
		tot_descuentos = 0.00;
	}

	if(tot_trab_faltantes == ''){
		tot_trab_faltantes = 0.00;
	}

	if(tot_trab_sobrantes == ''){
		tot_trab_sobrantes = 0.00;
	}

	if(tot_soles == ''){
		tot_soles = 0.00;
	}

	if(tot_dolares == ''){
		tot_dolares = 0.00;
	}

	url = 'control.php?rqst=REPORTES.VARILLAS&action=Actualizar&ch_almacen='+ch_almacen+'&dt_fecha='+dt_fecha+'&surtidor_soles='+surtidor_soles+'&surtidor_m3='+surtidor_m3+'&cnt_inicial='+cnt_inicial+'&cnt_final='+cnt_final+'&tot_cantidad='+tot_cantidad+'&tot_venta='+tot_venta+'&mermas='+mermas+'&tot_abono='+tot_abono+'&tot_afericion='+tot_afericion+'&tot_cli_anticipo='+tot_cli_anticipo+'&tot_tar_credito='+tot_tar_credito+'&tot_descuentos='+tot_descuentos+'&tot_trab_faltantes='+tot_trab_faltantes+'&tot_trab_sobrantes='+tot_trab_sobrantes+'&tot_soles='+tot_soles+'&tot_dolares='+tot_dolares+'&tot_cli_credito='+tot_cli_credito;
  	document.getElementById('control').src = url;

}

















