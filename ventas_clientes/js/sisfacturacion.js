function getFechaVencimiento(){
	$.datepicker.regional['es'] = {
	    closeText: 'Cerrar',
	    prevText: '<Ant',
	    nextText: 'Sig>',
	    currentText: 'Hoy',
	    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
	    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
	    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
	    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
	    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
	    weekHeader: 'Sm',
	    dateFormat: 'dd/mm/yy',
	    firstDay: 1,
	    isRTL: false,
	    showMonthAfterYear: false,
	    yearSuffix: ''
	};

	$.datepicker.setDefaults($.datepicker.regional['es']);

	//Modo de seleccionar un rango de fechas mediante dos Datepicker asociados
	$( "#fe_vencimiento" ).datepicker({
		changeMonth: true,
		changeYear: true,
		minDate: $("#dt_fac_fecha").val(),
		onClose: function (selectedDate) {
			$("#dt_fac_fecha").datepicker("option", "maxDate", selectedDate);
		}
	});

	$( "#fecha_replicacion" ).datepicker({changeMonth: true, changeYear: true});

	$('.cbo-Nu_Tipo_Pago').hide();
	$('#fe_vencimiento').hide();
	
	if($('[name="datos[nu_tipo_pago]"]').val() == '06'){
		$('.cbo-Nu_Tipo_Pago').show();
		$('#fe_vencimiento').show();
	}else{
		$( '#desc_forma_pago' ).text('');
	}

	$( '[id="datos[nu_tipo_pago]"]' ).change(function(){

		$('.cbo-Nu_Tipo_Pago').hide();
		$('#fe_vencimiento').hide();

		$( '#desc_forma_pago' ).text('');
		$( '[id="datos[ch_fac_credito]"]' ).val('N');
		$( '[id="datos[ch_fac_forma_pago]"]' ).val($(this).val());

		var Nu_Codigo_Cliente = $( '[id="datos[cli_codigo]"]' ).val();

		if ($( '[id="datos[cli_codigo]"]' ).val().length === 0){
			alert('Debe seleccionar un cliente');
		}else{
			if($(this).val() == '06'){
				$('.cbo-Nu_Tipo_Pago').show();
				$('#fe_vencimiento').show();
				$( '[id="datos[ch_fac_credito]"]' ).val('S');
				$.post( "../assets/helper.php", {
	            	accion 				: 'getClientesDiasCredito',
			        Nu_Codigo_Cliente 	: Nu_Codigo_Cliente,
				}, function(response){
					console.log('codigo int_tabla_general dias de vencimiento ->' + response.data["nu_codigo_dias_vencimiento"]);
					$( '[id="datos[ch_fac_forma_pago]"]' ).val(response.data["nu_codigo_dias_vencimiento"]);
					$( '#desc_forma_pago' ).text(response.data["tab_descripcion"]);
					
					/* Calcular dias de Vencimiento */
					var Fe_Emision_Venta = $(' #dt_fac_fecha' ).val();

				    Fe_Emision_Venta = Fe_Emision_Venta.split('/');
				    Fe_Emision_Venta = Fe_Emision_Venta[2] + '/' + Fe_Emision_Venta[1] + '/' + Fe_Emision_Venta[0];

					var Fe_Emision_Venta_New = Fe_Emision_Venta;

					var Fe_Actual = new Date(Fe_Emision_Venta_New);

					day 			= Fe_Actual.getDate();
					month 			= Fe_Actual.getMonth()+1;
					year 			= Fe_Actual.getFullYear();

					/* Dia Actual */
					tiempo 			= Fe_Actual.getTime();
					milisegundos 	= parseInt(response.data["nu_dias_vencimiento"] * 24 * 60 * 60 * 1000);
					total 			= Fe_Actual.setTime(tiempo+milisegundos);
					day 			= Fe_Actual.getDate();
					month 			= Fe_Actual.getMonth()+1;
					year 			= Fe_Actual.getFullYear();

					if(month.toString().length < 2)
						month = "0".concat(month);

					if(day.toString().length<2)
						day = "0".concat(day);

					/* Fecha de Vencimiento Registro Compras */
					var Fe_Vencimiento = day + '/' + month + '/' + year;	
					$(' #fe_vencimiento' ).val(Fe_Vencimiento);

				}, 'JSON');
			}
		}
	});
}

function Regresar(url){
	$(location).attr('href',url); 
}

function VerTipoCambio(moneda){

	var fecha;

	fecha = document.getElementById('dt_fac_fecha').value;

	url = 'control.php?rqst=FACTURACION.FACTURAS&action=TipoCambio&task=FACTURASDET&fecha='+fecha+'&moneda='+moneda;
	document.getElementById('control').src = url;
	
}

function OcultarMostrarFila(valor){

	if(valor == "S"){
		dis			= 'none';
		fila			= document.getElementById("ch_fac_tiporecargo2");
		fila2			= document.getElementById("ch_fac_tiporecargo2A");
		fila3			= document.getElementById("ch_fac_cd_impuesto3");
		fila4			= document.getElementById("ch_fac_cd_impuesto3A");
		fila.style.display	= dis;
		fila2.style.display	= dis;
		fila3.style.display	= dis;
		fila4.style.display	= dis;
	}else if(valor == "N"){
		dis			= '';
		fila			= document.getElementById("ch_fac_tiporecargo2");
		fila2			= document.getElementById("ch_fac_tiporecargo2A");
		fila3			= document.getElementById("ch_fac_cd_impuesto3");
		fila4			= document.getElementById("ch_fac_cd_impuesto3A");
		fila.style.display	= dis;
		fila2.style.display	= dis;
		fila3.style.display	= dis;
		fila4.style.display	= dis;
	}


}
	
function maximaLongitud(texto,maxlong) {
	var tecla, in_value, out_value;
	if (texto.value.length>maxlong) {
		in_value=texto.value;
		out_value=in_value.substring(0,maxlong);
		texto.value=out_value;
		return false;
	}
	return true;
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

function displaybanco(campo, valor) {
	if(campo.checked==true) {
	        valor.style.display = 'block';
    	}else{
        	valor.style.display = 'none';
    	}
}

function displaygrid(valor) {
	if(valor.style.display=='none') {
        	valor.style.display = 'block';
    	}else{
        	valor.style.display = 'none';
    	}
}

function win_complemento(modificar) {
	cod_cliente = document.getElementsByName('datos[cli_codigo]')[0];
	registroid = document.getElementsByName('registroid')[0];
	var type = document.getElementsByName('datos[ch_fac_tipodocumento]')[0];
	url = "forms_popup/fac_complementarios.php?cod_cliente="+cod_cliente.value+"&accion=Completar&registroid="+registroid.value+"&modificar="+modificar+"&type="+type.value;
	window.open(url,'miwin','width=700,height=370,scrollbars=yes,menubar=no,left=290,top=20');
}

function ClearSerieAlmacen(valor) {
	campo = document.getElementsByName('datos[ch_fac_seriedocumento]')[0];
	campo.value = '';
    
	texto = document.getElementById('desc_series_doc');
	texto.innerHTML = '';
    
	texto = document.getElementById('Numero');
	texto.innerHTML = '';
   
	texto = document.getElementById('Almacen');
	texto.innerHTML = '';
    
	texto = document.getElementById('desc_cliente');
	texto.innerHTML = '';
    
	texto = document.getElementById('desc_lista_precios');
	texto.innerHTML = '';
    
	texto = document.getElementById('desc_descuento');
	texto.innerHTML = '';
    
	cliente = document.getElementsByName('datos[cli_codigo]')[0];
	cliente.value='';
    
	lista = document.getElementsByName('articulos[pre_lista_precio]')[0];
	lista.value='';
    
	//dscto = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	//dscto.value='';
    
	anticipo = document.getElementsByName('datos[ch_fac_anticipo]')[0];
	if (valor=='10' || valor=='35'){
	    	anticipo.style.display = 'inline';
	}else {
	    	anticipo.style.display = 'none';
	}
}

function Mostrar_Liquidacion(valor) {
	texto = document.getElementsByName('datos[ch_liquidacion]')[0];
	texto.value='';
	if (valor=='S') {
		texto.style.display = 'inline';
	} else {
		texto.style.display = 'none';
	}
	
	anticipo=document.getElementsByName('datos[ch_fac_anticipo]')[0];
	if (valor=='S'){
	    	anticipo.style.display = 'none';
	} else {
	    	anticipo.style.display = 'inline';
    	}
    	anticipo.value='N';    
	return;
}

function ClearCredito() {
	campo = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
	campo.value = '';
	texto = document.getElementById('desc_forma_pago');
	texto.innerHTML = '';    
}

function displayTipoPersona(campo, activa, inactiva) {
	if(campo.checked==true) {
	        activa.style.display   = 'block';
        	inactiva.style.display = 'none'
    	} else {
        	activa.style.display   = 'none';
        	inactiva.style.display = 'block';
    	}
}

function confirmarLink(pregunta, accionY, accionN, target) {
	var tipo_eliminar = document.getElementsByName('forma_eliminar')[0].value;
  
	if (tipo_eliminar==0) {
	  	alert ('Seleccione si desea que suelte la liquidacion de los vales');
	} else {
  		if(confirm(pregunta))
		    	document.getElementById('control').src = accionY+'&forma_eliminar='+tipo_eliminar;  		
  	}  
}

function confirmarLink22(pregunta, accionY, accionN, target) {
  	if(confirm(pregunta))
	    	document.getElementById('control').src = accionY;	
    	else
  		document.getElementById('control').src = accionN;
}

/**
 * Reporte para la representacion impresa (formato interno)
 */
function _generarDocumentoLV(tipodocumento, serie, numeroDocumento, codCliente) {
	//alert('_generarDocumentoLV!');
	var _url = 'generar_lv_print.php?tipoDocumento='+tipodocumento+'&serie='+serie+'&documento='+numeroDocumento+'&codCliente='+codCliente+'&isUIGV=0';
	location.href=_url;
}

function documentoInterno(num, nomTipoDocumento, tipoDocumento, serie, numeroDocumento, codCliente, regCod, ch_fac_anulado, dt_fac_fecha, codalmacen, _id) {
	var url = 'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&action=DOCUMENTOINTERNO&ch_fac_tipodocumento='+tipoDocumento+'&serie='+serie+'&numeroDocumento='+numeroDocumento+'&codCliente='+codCliente+'&registroid='+regCod+'&ch_fac_anulado='+ch_fac_anulado+'&dt_fac_fecha='+dt_fac_fecha+'&codalmacen='+codalmacen+'&_id='+_id;
	console.log('url'+url);
	//location.href = url;
	window.open(url, '_blank');
}

/**
 * 
 */
function enviarDocumentoSunat(num, nomTipoDocumento, tipoDocumento, serie, numeroDocumento, codCliente, regCod, ch_fac_anulado, dt_fac_fecha, codalmacen, _id) {
	console.log('enviarDocumentoSunat');
	if (confirm('¿Deseas enviar: '+nomTipoDocumento+' - '+serie+' - '+numeroDocumento+'?')) {
		//Confirmado para envìar a sunat
		$('.document-'+num+'-'+tipoDocumento+'-'+serie+'-'+numeroDocumento+'-'+codCliente).css('display', 'none');
		console.log('clase: .document-'+num+'-'+tipoDocumento+'-'+serie+'-'+numeroDocumento+'-'+codCliente);
		//control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&action=Complete&codigo=&f_desde=01/01/2018&f_hasta=19/01/2018&buscar_tipo=TODOS&turno=0&status=0&ch_fac_anulado=&codalmacen=001&registroid=20F020000036020481555702&dt_fac_fecha=08/01/2018&ch_fac_tipodocumento=20
		//[IMPORTANTE] validar todos los parametros
		var params = {
			rqst: 'FACTURACION.FACTURAS',
			task: 'FACTURAS',
			action: '_complete',
			ch_fac_tipodocumento: tipoDocumento,
			serie: serie,
			numeroDocumento: numeroDocumento,
			codCliente: codCliente,
			registroid: regCod,
			ch_fac_anulado: ch_fac_anulado,
			dt_fac_fecha: dt_fac_fecha,
			codalmacen: codalmacen,
			_id: _id,
		};
		$.ajax({
			method: 'POST',
			dataType: 'json',
			url: 'control.php',
			data: params
		}).done(function( data ) {
			console.log(data);
			if (data.error) {
				alert(data.message);
			} else {
				alert(data.message);
				$('.document-'+num+'-'+tipoDocumento+'-'+serie+'-'+numeroDocumento+'-'+codCliente).css('display', 'none');
				window.location = 'fact_facturas.php';
			}
			//alert( "Data Saved: " + data );
		});
	}
}

function emularEnviarDocumentoSunat(num, nomTipoDocumento, tipoDocumento, serie, numeroDocumento, codCliente) {

}

function confirmarForm(pregunta, form) {
	if(confirm(pregunta)) 
		return true;
	return false;
}

function bloquea(valor1,valor2) {
	if(valor1.value != '' || valor1.value > 0) {
		valor2.disabled=true;
	} else {
      		valor2.disabled=false;
   	}
}

function PaginarRegistros(rxp, valor) {
	send = document.getElementsByName('task')[0].value;
	urlPagina = 'control.php?rqst=FACTURACION.'+send+'&task='+send+'&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;
}

function getRegistro(campo) {
	tdoc = document.getElementsByName('datos[ch_fac_tipodocumento]')[0].value;
	url = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistro&task=FACTURASDET&codigo='+campo+'&tdoc='+tdoc;
	document.getElementById('control').src = url;
	return;
}

function getRegistroSerie(campo) {
	tdoc = document.getElementsByName('datos[ch_fac_tipodocumento]')[0].value;
	url = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistroSerie&task=FACTURASDET&codigo='+campo+'&tdoc='+tdoc;
	document.getElementById('control').src = url;
	return;
}

function setRegistro(campo, hidden1, hidden2, hidden3) {
	txt_campo = document.getElementsByName('datos[ch_fac_seriedocumento]')[0];
	txt_campo.value = campo;
  
	campo_hidden = document.getElementsByName('datos[ch_fac_numerodocumento]')[0];
	campo_hidden.value = hidden1;
  
	campo_hidden2 = document.getElementsByName('datos[ch_almacen]')[0];
	campo_hidden2.value = hidden2;
  
	campo_hidden3 = document.getElementsByName('referencia_fecha')[0];
	campo_hidden3.value = hidden3;
  
	numero = document.getElementById('Numero');
	numero.innerHTML = hidden1;
  
	almacen = document.getElementById('Almacen');
	almacen.innerHTML = hidden2;
  
	cliente=document.getElementsByName('datos[cli_codigo]')[0];
	cliente.focus();

	return;
}

function getRegistroFP(campo) {
/*
	$('#cbo-Nu_Tipo_Pago').hide();
	$('#fe_vencimiento').hide();
	if(campo == '06'){
		$('#cbo-Nu_Tipo_Pago').show();
		$('#fe_vencimiento').show();
	}
*/
	fcred = document.getElementsByName('datos[ch_fac_credito]')[0].value;
	nu_codigo_cliente = document.getElementsByName('datos[cli_codigo]')[0].value;

	//url   = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistroFP&task=FACTURASDET&codigofp='+campo+'&fcred='+fcred+'&nu_codigo_cliente='+nu_codigo_cliente;
	url   = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistroFP&task=FACTURASDET&codigofp=S&fcred=S&nu_codigo_cliente='+nu_codigo_cliente;
	document.getElementById('control').src = url;
	return;
}

function setRegistroFP(campo, hidden) {
	txt_campo 	   = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
	txt_campo.value    = campo;
	campo_hidden 	   = document.getElementsByName('c_dias_pago')[0];
	campo_hidden.value = hidden;
	l_precios 	   = document.getElementsByName('articulos[pre_lista_precio]')[0];
	l_precios.focus();
	return;
}

function getRegistroLPRE(campo) {
	url = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistroLPRE&task=FACTURASDET&codigolpre='+campo;
	document.getElementById('control').src = url;
	return;
}

function setRegistroLPRE(campo) {
	txt_campo 	= document.getElementsByName('articulos[pre_lista_precio]')[0];
	txt_campo.value = campo;
	limpiar_articulos();
	det_cantidad 	= document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0];
	det_cantidad.focus();
	return;
}

function limpiar_articulos() {
	objetocodigo=document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
	objetocodigo.value='';
}

function limpiar_articulos2() {
	objetocodigo=document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0];
	objetocodigo.value='';
	objetocodigo=document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
	objetocodigo.value='';
}

function getRegistroCli(campo) {
	$('#desc_cliente').html('');
	url = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistroCli&task=FACTURASDET&codigocli='+campo;
	document.getElementById('control').src = url;
	setTimeout(function(){ validarAnticipo(); }, 1000);
	return;
}

function setRegistroCli(campo,recargo,lista,desc1, porc, desc2, desc3, estado) {
	var objetocodigo = document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0]; 
	var checking     = document.getElementById('checking');
	txt_campo 	 = document.getElementsByName('datos[cli_codigo]')[0];
	txt_campo.value  = campo;  
	var tipodoc 	 = document.getElementsByName('datos[ch_fac_tipodocumento]')[0];

	if (tipodoc.value!='20') {
		txt_campo = document.getElementsByName('articulos[pre_lista_precio]')[0];
		txt_campo.value = lista;
		txt_campo = document.getElementById('desc_lista_precios');
		txt_campo.innerHTML = desc1;
		//txt_campo = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
		txt_campo.value = porc;
		txt_campo = document.getElementById('desc_descuento');
		txt_campo.innerHTML = desc2;
		txt_campo = document.getElementsByName('datos[nu_fac_descuento1]')[0];
		txt_campo.value = desc3;
		txt_campo2 = document.getElementsByName('porce_recargo')[0];
		txt_campo2.value = recargo;

		if (estado=='0') {
		  	objetocodigo.disabled=true;
		  	checking.innerHTML='<blink>EL DESCUENTO NO AUTORIZADO !!!!</blink>';
		} else {
	  		objetocodigo.disabled=false;
	  		checking.innerHTML='<blink>ACTUALIZAR STOCK SI/NO? ==></blink>';
	  	}
	  	CalcularValores();
	  	limpiar_articulos();
  	} else {
		txt_campo = document.getElementsByName('articulos[pre_lista_precio]')[0];
		txt_campo.value = lista;
		txt_campo = document.getElementById('desc_lista_precios');
		txt_campo.innerHTML = desc1;
	  	//txt_campo = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
		//txt_campo.value = '01';
		txt_campo = document.getElementById('desc_descuento');
		txt_campo.innerHTML = 'Sin Descuento';
		txt_campo = document.getElementsByName('datos[nu_fac_descuento1]')[0];
		txt_campo.value = 0;
	}
	f_pago = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
	f_pago.focus();
	return;
}

function getRegistroDesc(campo) {
	url = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistroDesc&task=FACTURASDET&codigodesc='+campo;
	document.getElementById('control').src = url;
	return;
}

//function setRegistroDesc(campo, hidden) {
function setRegistroDesc(hidden) {
	//txt_campo 	   = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	//txt_campo.value    = campo;
	campo_hidden 	   = document.getElementsByName('datos[nu_fac_descuento1]')[0];
	campo_hidden.value = hidden;
	return;
}

function copyOptions(sourceL, targetL) {
	for (i=0; i<sourceL.length; i++){
		targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
	}
}

function getRegistroArt(campo) {
	lprec = document.getElementsByName('articulos[pre_lista_precio]')[0].value;
	url = 'control.php?rqst=FACTURACION.FACTURAS&action=setRegistroArt&task=FACTURASDET&codigoart='+campo+'&lprec='+lprec;
	document.getElementById('control').src = url;
	return;
}

function setRegistroArt(campo, descripcion, precio, editable) {
	/*DETERMINAR EL FACTOR DEPENDIENDO DE LA MONEDA Y LA LISTA DE PRECIOS*/
	des  		 = document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0];
	prec 		 = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
	pre_lista_precio = document.getElementsByName('articulos[pre_lista_precio]')[0].value;	
	moneda 		 = document.getElementsByName('datos[ch_fac_moneda]')[0].value;
	tipo_cambio 	 = document.getElementsByName('datos[nu_tipocambio]')[0].value;

	if (pre_lista_precio=='01' && moneda=='02') {
		factor = 1/tipo_cambio;
	} else {
		if(pre_lista_precio=='60' && moneda=='01')
			factor=tipo_cambio;
		else
			factor=1;
	}

	/*if (editable=='') {
		des.disabled = true;
		prec.disabled = false;
	}else{
		des.disabled = false;
		prec.disabled = true;
	}*/

	codigo   = document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0].value = campo;
	descrip  = document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0].value = descripcion;
	precio   = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0].value = Math.round(precio*factor*100)/100;
	cantidad = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].value='';
	cantidad = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].focus();

	return;
}

function CalcularValores2() {
	var precio      = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
	var cantidad    = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0];
	var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
	var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
	var porc_igv    = document.getElementsByName('porce_fac_impuesto1')[0];
	var cant_dscto  = document.getElementsByName('datos[nu_fac_descuento1]')[0];
	var dscto       = document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0];
	var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
    
	calc_neto = precio.value * cantidad.value;
    
	if(cant_dscto.value!='' || cant_dscto.value>0) {
		calc_dscto = calc_neto * cant_dscto.value;
		calc_neto_dscto = calc_neto - calc_dscto;
	} else {
		calc_dscto = 0;
		calc_neto_dscto = calc_neto;
	}
	calc_igv    = calc_neto_dscto * porc_igv.value;
	neto.value  = calc_neto.toFixed(2);
	igv.value   = calc_igv.toFixed(2);
	dscto.value = calc_dscto.toFixed(2);
	calc_total  = calc_neto_dscto + calc_igv;
	total.value = calc_total.toFixed(2);
}

function CalcularValores(exonerado) {

	var exonerado 	= document.getElementsByName('datos[nuexonerado]')[0].value;

	var precio      = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
	var cantidad    = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0];
	var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
	var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
	var porc_igv    = document.getElementsByName('porce_fac_impuesto1')[0];
	var dscto       = document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0];
	var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
	var total2 		= document.getElementsByName('interface[dato_articulo][total_articulo2][]')[0];
	var calc_igv 	= 0;
	var calc_dscto 	= 0;

	if(exonerado == 'S'){
		calc_total 	 		= precio.value * cantidad.value;
		calc_total2 	 	= ((precio.value * cantidad.value) - dscto.value);
		neto.value 	 		= calc_total.toFixed(2);
		igv.value   	 	= 0.00;
		total.value  	 	= calc_total.toFixed(2);
		total2.value  	 	= (calc_total.toFixed(2) - calc_dscto.toFixed(2));
	}else{
		calc_total 	 		= precio.value * cantidad.value;
		calc_total2 	 	= ((precio.value * cantidad.value) - dscto.value);
		calc_total_dscto 	= calc_total;
		calc_igv 	 		= calc_total_dscto.toFixed(2) - (calc_total_dscto.toFixed(2) / 1.18).toFixed(2);
		neto.value 	 		= (calc_total_dscto.toFixed(2) / 1.18).toFixed(2);
		igv.value   	 	= calc_igv.toFixed(2);
		total.value  	 	= calc_total_dscto.toFixed(2);
		total2.value  	 	= calc_total2.toFixed(2);
	}

}

function CalcularValoresC() {

	var precio      = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
	var cantidad    = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0];
	var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
	var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
	var porc_igv    = document.getElementsByName('porce_fac_impuesto1')[0];
	var dscto       = document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0];
	var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
	var calc_igv 	= 0;

	calc_cantidad = (total.value / precio.value);
	cantidad.value	= calc_cantidad;

	calc_igv 		= total.toFixed(2) - (total.toFixed(2) / 1.18).toFixed(2);
	neto.value 	 	= (total.toFixed(2) / 1.18).toFixed(2);
	igv.value   	= calc_igv.toFixed(2);
	dscto.value 	= calc_dscto.toFixed(2);

	
}

function AgregaArticulo() {

	var numero 		= document.getElementsByName('datos[ch_fac_numerodocumento]')[0];
	var codigo      = document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0].value;
	var descripcion = document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0].value;
	var precio      = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0].value;
	var cantidad    = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].value;
	var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0].value;
	var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0].value;
	var dscto       = document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0].value;
	var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0].value;
	var total2      = document.getElementsByName('interface[dato_articulo][total_articulo2][]')[0].value;
	var recargo 	= document.getElementsByName('porce_recargo')[0].value;
	var dato 		= document.form_facturas.acumulado;
	var sw			= 0;
	var options 	= document.forms['form_facturas'].elements['cod_articulo[]'];
	var cuenta 		= 0;
	var isc 		= "";

	if (numero.value=='') {
		alert('Deben de escribir la Serie para que muestre el Numero');
		return false;
	}else{
		if ( (precio == 0.00 || cantidad == 0.00) || (precio == '0.00' || cantidad == '0.00') ) {
			alert('El precio / cantidad no puede ser cero');
		} else {

			if (descripcion!='' && codigo!='') {

				if(options != undefined) {
		    		isc = document.getElementsByName('datos[nu_fac_impuesto2]')[0].value;
	    			cuenta = options.length;

	    			if (cuenta != undefined) {
		    			for (var i = 0; i < cuenta; i++) {
							if (options[i].value == codigo) {
								sw=1;
								break;
							}
						}
	    			} else {
		    			var auxiliar = document.getElementsByName('cod_articulo[]')[0].value;
		    			if (auxiliar == codigo) 
						sw=1;
					}
				}

				if (sw == 0) {
					url = 'control.php?rqst=FACTURACION.FACTURAS&action=AgregaArticulo&task=FACTURASDET&codigo='+codigo+'&descripcion='+encodeURIComponent(descripcion)+'&precio='+precio+'&cantidad='+cantidad+'&neto='+neto+'&igv='+igv+'&dscto='+dscto+'&total='+total+'&recargo='+recargo+'&datos[nu_fac_impuesto2]='+isc+'&total2='+total2;
			    	document.getElementById('control').src = url;
					dato.value=parseInt(dato.value)+1;
					document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][total_articulo][]')[0].value='';
					document.getElementsByName('interface[dato_articulo][total_articulo2][]')[0].value='';
					habilitar(false);
				} else {
					alert('El Item ya ha sido registrado!!!');
				}

			} else {
				alert('Ingrese el articulo correctamente, faltan datos !!!');
			}
		}
	}
}

function verificar_completo() {
	dato 	= document.form_facturas.acumulado;
	numero 		= document.getElementsByName('datos[ch_fac_numerodocumento]')[0];
	serie 		= document.getElementsByName('datos[ch_fac_seriedocumento]')[0];
	fecha 		= document.getElementsByName('dt_fac_fecha')[0];
	cliente 	= document.getElementsByName('datos[cli_codigo]')[0];
	formapago 	= document.getElementsByName('datos[ch_fac_forma_pago]')[0];
	lista 		= document.getElementsByName('articulos[pre_lista_precio]')[0];
	tipocambio 	= document.getElementsByName('datos[nu_tipocambio]')[0];
//	dscto 		= document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	isc 		= document.getElementsByName('datos[nu_fac_impuesto2]')[0];
	bruto 		= document.getElementsByName('datos[nu_fac_valorbruto]')[0];
	check 		= document.getElementsByName('datos[ch_descargar_stock]')[0];
	
	//if (serie.value=='' || fecha.value=='' || cliente.value=='' || formapago.value=='' || lista.value=='' || tipocambio.value=='' || dscto.value=='') {
	if (numero.value=='' || serie.value=='' || fecha.value=='' || cliente.value=='' || formapago.value=='' || lista.value=='' || tipocambio.value=='') {
		alert('Los datos de la cabecera estan incompletos !!!');
		return false;
	} else {
		if (parseInt(dato.value) < 1) { 
			alert('Debe ingresar articulos a la factura !!!');
			return false;
		} else {
			if (parseInt(isc.value) >= parseInt(bruto.value)) {
				alert('El Monto del ISC es demasiado alto, verifiquelo !');
				return false;
			} else {
				if (confirm('¿Desea guardar los datos de la factura?')) {
					habilitar(true)
				} else {
					return false;
				}
			}
		}
	}
	return true;
}

function habilitar(valor) {
	tipodoc 		= document.getElementsByName('datos[ch_fac_tipodocumento]')[0];
	serie 			= document.getElementsByName('datos[ch_fac_seriedocumento]')[0];
	fecha 			= document.getElementsByName('dt_fac_fecha')[0];
	cliente 		= document.getElementsByName('datos[cli_codigo]')[0];
	credito 		= document.getElementsByName('datos[ch_fac_credito]')[0];
	formapago 		= document.getElementsByName('datos[ch_fac_forma_pago]')[0];
	anticipado 		= document.getElementsByName('datos[ch_fac_anticipo]')[0];
	lista 			= document.getElementsByName('articulos[pre_lista_precio]')[0];
	moneda 			= document.getElementsByName('datos[ch_fac_moneda]')[0];
	tipocambio 		= document.getElementsByName('datos[nu_tipocambio]')[0];
	//dscto 			= document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	descargar 		= document.getElementsByName('datos[ch_descargar_stock]')[0];	
	recargo 		= document.getElementsByName('datos[nu_fac_recargo2]')[0];
	isc 			= document.getElementsByName('datos[nu_fac_impuesto2]')[0];
	tipodoc.disabled 	= !valor;
	serie.disabled 		= !valor;
//	fecha.disabled 		= !valor;//COMENTADO EL 03/08/2016
	cliente.disabled 	= !valor;
	formapago.disabled 	= !valor;
	anticipado.disabled 	= !valor;
	lista.disabled 		= !valor;
	tipocambio.disabled 	= !valor;
	dscto.disabled 		= !valor;
	descargar.disabled 	= !valor;
	credito.disabled 	= !valor;
	anticipado.disabled 	= !valor;
	moneda.disabled 	= !valor;
	recargo.disabled 	= !valor;
	isc.disabled 		= !valor;
}

function EliminarArticulo(valor,total) {
	var dato    = document.form_facturas.acumulado;
	var codigo  = document.getElementsByName('registroid')[0].value;
	var recargo = document.getElementsByName('porce_recargo')[0].value;
	var isc     = document.getElementsByName('datos[nu_fac_impuesto2]')[0].value;

	if (confirm('Confirma Eliminar el item '+valor+'?'+parseInt(dato.value))) {
    	url = 'control.php?rqst=FACTURACION.FACTURAS&action=AgregaArticulo&task=FACTURASDET&dato_elimina='+valor+'&registroid='+codigo+'&recargo='+recargo+'&datos[nu_fac_impuesto2]='+isc;
		document.getElementById('control').src = url;
		
		dato.value=parseInt(dato.value)-1;
	}
}

function ActualizarIGV(tipo, serie, numero, cliente, valor, desde, hasta, codigo, tipo_doc, turno) {

    	if (confirm('Deseas pasar el documento sin IGV?')) {
		url = 'control.php?rqst=FACTURACION.FACTURAS&action=ActualizarIGV&task=FACTURASDET&tipo='+tipo+'&serie='+serie+'&numero='+numero+'&cliente='+cliente+'&idigv='+valor+'&desde='+desde+'&hasta='+hasta+'&codigo='+codigo+'&tipo_doc='+tipo_doc+'&turno='+turno;
		document.getElementById('control').src = url;
    	}

}

function pasarfechas() {
	var fecha_ini = document.getElementsByName('fecha_ini')[0].value;
	var fecha_fin = document.getElementsByName('fecha_fin')[0].value;
	
	var objeto_fecha_ini = document.getElementsByName('busqueda[fecha_ini]')[0];
	var objeto_fecha_fin = document.getElementsByName('busqueda[fecha_fin]')[0];
	
	objeto_fecha_ini.value = fecha_ini;
	objeto_fecha_fin.value = fecha_fin;
	
	return true;
}


/**
 * Validar documento anticipo - cliente anticipo
 * IMPORTANTE: Ejecutar tambien cuando se cambie de cliente
 */
function validarAnticipo() {
	console.log($('.valor-anticipo').val());
	console.log($('.bpartner_dm').val() +' && '+ $('#find-bpartner-ok').html()  +' && '+ $('.valor-anticipo').val());
	if ($('.bpartner_dm').val() != '' && $('#find-bpartner-ok').length && $('.valor-anticipo').val() == 'S') {
		console.log('Cumple');
		var bpartner = $('.bpartner_dm').val();

		var params = {
			bpartner: bpartner,
			Accion: 'validarClienteAnticipo',
		};

		$.ajax({
			url: '/sistemaweb/assets/autocomplete.php',
			type: 'POST',
			dataType: 'json',
			data: params,
			success: function(data) {
				console.log(data);
				if (!data.error) {
					if (!data.esAnticipo) {
						alert('El Cliente selecionado no puede tener Anticipos.');
						$('.bpartner_dm').val('');
						$('#desc_cliente').html('');
						$('.btn-save-invoice').css('display', 'none');
						//window location?
					}
				} else {
					alert('Error al realizar la consulta!');
					//window location?
				}
			}
		});
	} else {
		$('.btn-save-invoice').css('display', 'inline');
		console.log('No cumple');
	}
}

