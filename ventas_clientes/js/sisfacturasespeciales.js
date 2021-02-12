/*

  Funciones JavaScript 
  Sistema de Contabilidad ACOSA
  @TBCA Modificado por @MATT

*/
function maximaLongitud(texto,maxlong){
	var tecla, in_value, out_value;
	if (texto.value.length>maxlong){
		in_value=texto.value;
		out_value=in_value.substring(0,maxlong);
		texto.value=out_value;
		return false;
	}
	return true;
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


function displaybanco(campo, valor)
{
    if(campo.checked==true)
    {
        valor.style.display = 'block';
    }else{
        valor.style.display = 'none';
    }
}


function displaygrid(valor)
{
    if(valor.style.display=='none')
    {
        valor.style.display = 'block';
    }else{
        valor.style.display = 'none';
    }
}

function win_complemento(modificar)
{
    cod_cliente = document.getElementsByName('datos[cli_codigo]')[0];
    registroid = document.getElementsByName('registroid')[0];
    url = "forms_popup/tmp_fac_complementarios.php?cod_cliente="+cod_cliente.value+"&accion=Completar&registroid="+registroid.value+"&modificar="+modificar;
    window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function ClearSerieAlmacen(valor)
{
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
    
    dscto = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
    dscto.value='';
    
    anticipo = document.getElementsByName('datos[ch_fac_anticipo]')[0];
    if (valor=='10' || valor=='35'){
    	anticipo.style.display='inline';
    }else {
    	anticipo.style.display='none';
    }
}

function Mostrar_Liquidacion(valor){
	/*texto = document.getElementsByName('datos[ch_liquidacion]')[0];
	texto.value='';
	if (valor=='N'){
		texto.style.display='inline';
	}else{
		texto.style.display='none';
	}*/
	
    anticipo=document.getElementsByName('datos[ch_fac_anticipo]')[0];
    if (valor=='S'){
    	anticipo.style.display='none';
    }else{
    	anticipo.style.display='inline';
    }
    anticipo.value='N';
    
	return;
}

function ClearCredito()
{
    campo = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
    campo.value = '';
    texto = document.getElementById('desc_forma_pago');
    texto.innerHTML = '';
    
}

function displayTipoPersona(campo, activa, inactiva)
{
    if(campo.checked==true)
    {
        activa.style.display = 'block';
        inactiva.style.display = 'none'
    }else{
        activa.style.display = 'none';
        inactiva.style.display = 'block';
    }
}
function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
  else
    document.getElementById('control').src = accionN;
}

function confirmarForm(pregunta, form)
{
  if(confirm(pregunta)) 
    return true;
  return false;
}


function bloquea(valor1,valor2)
{
   if(valor1.value != '' || valor1.value > 0)
   {
      valor2.disabled=true;
   }else{
      valor2.disabled=false;
   }
}
function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   send = document.getElementsByName('task')[0].value;
   urlPagina = 'control.php?rqst=FACTURACION.'+send+'&task='+send+'&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}


function getRegistro(campo){
  tdoc = document.getElementsByName('datos[ch_fac_tipodocumento]')[0].value;
  url = 'control.php?rqst=FACTURACION.ESPECIALES&action=setRegistro&task=ESPECIALESDET&codigo='+campo+'&tdoc='+tdoc;
  document.getElementById('control').src = url;
  return;
}

function setRegistro(campo, hidden1, hidden2){
  txt_campo = document.getElementsByName('datos[ch_fac_seriedocumento]')[0];
  txt_campo.value = campo;
  
  campo_hidden = document.getElementsByName('datos[ch_fac_numerodocumento]')[0];
  campo_hidden.value = hidden1;
  
  campo_hidden2 = document.getElementsByName('datos[ch_almacen]')[0];
  campo_hidden2.value = hidden2;
  
  numero = document.getElementById('Numero');
  numero.innerHTML = hidden1;
  
  almacen = document.getElementById('Almacen');
  almacen.innerHTML = hidden2;
  
  check = document.getElementsByName('datos[ch_descargar_stock]')[0];
  check.checked=0;
  if (campo=='001' || campo=='501')
  	check.style.visibility="visible";
  else
  	check.style.visibility="hidden";
  
  cliente=document.getElementsByName('datos[cli_codigo]')[0];
  cliente.focus();
  return;
}


function getRegistroFP(campo){
  fcred = document.getElementsByName('datos[ch_fac_credito]')[0].value;
  url = 'control.php?rqst=FACTURACION.ESPECIALES&action=setRegistroFP&task=ESPECIALESDET&codigofp='+campo+'&fcred='+fcred;
  document.getElementById('control').src = url;
  return;
}

function setRegistroFP(campo, hidden){
  txt_campo = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
  txt_campo.value = campo;
  campo_hidden = document.getElementsByName('c_dias_pago')[0];
  campo_hidden.value = hidden;
  l_precios = document.getElementsByName('articulos[pre_lista_precio]')[0];
  l_precios.focus();
  return;
}

function getRegistroLPRE(campo){
  url = 'control.php?rqst=FACTURACION.ESPECIALES&action=setRegistroLPRE&task=ESPECIALESDET&codigolpre='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroLPRE(campo){
  txt_campo = document.getElementsByName('articulos[pre_lista_precio]')[0];
  txt_campo.value = campo;
  limpiar_articulos();
  det_cantidad = document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0];
  det_cantidad.focus();
  return;
}

function limpiar_articulos(){
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

function limpiar_articulos2(){
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

function getRegistroCli(campo){
  url = 'control.php?rqst=FACTURACION.ESPECIALES&action=setRegistroCli&task=ESPECIALESDET&codigocli='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroCli(campo,recargo,lista,desc1, porc, desc2, desc3, estado){
  var objetocodigo=document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0]; 
  var checking = document.getElementById('checking');
  txt_campo = document.getElementsByName('datos[cli_codigo]')[0];
  txt_campo.value = campo;
  
  var tipodoc = document.getElementsByName('datos[ch_fac_tipodocumento]')[0];
  if (tipodoc.value!='20'){
	  txt_campo = document.getElementsByName('articulos[pre_lista_precio]')[0];
	  txt_campo.value = lista;
	  txt_campo = document.getElementById('desc_lista_precios');
	  txt_campo.innerHTML = desc1;
	  txt_campo = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	  txt_campo.value = porc;
	  txt_campo = document.getElementById('desc_descuento');
	  txt_campo.innerHTML = desc2;
	  txt_campo = document.getElementsByName('datos[nu_fac_descuento1]')[0];
	  txt_campo.value = desc3;
	  txt_campo2 = document.getElementsByName('porce_recargo')[0];
	  txt_campo2.value = recargo;
	  CalcularValores();
	  limpiar_articulos();
  }else{
  	txt_campo = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	txt_campo.value = '01';
	txt_campo = document.getElementById('desc_descuento');
	txt_campo.innerHTML = 'Sin Descuento';
	txt_campo = document.getElementsByName('datos[nu_fac_descuento1]')[0];
	txt_campo.value = 0;
  }
  f_pago = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
  f_pago.focus();
  return;
}

function getRegistroDesc(campo){
  url = 'control.php?rqst=FACTURACION.ESPECIALES&action=setRegistroDesc&task=ESPECIALESDET&codigodesc='+campo;
  document.getElementById('control').src = url;
  return;
}

function volver_para_autorizar(campo){
	 url = 'control.php?rqst=FACTURACION.ESPECIALES&action=Autorizar&task=ESPECIALES&registroid='+campo;
  	document.getElementById('control').src = url;
  	return;
}

function setRegistroDesc(campo, hidden){
  txt_campo = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
  txt_campo.value = campo;

  campo_hidden = document.getElementsByName('datos[nu_fac_descuento1]')[0];
  campo_hidden.value = hidden;
  return;
}

function copyOptions(sourceL, targetL){
  for (i=0; i<sourceL.length; i++){
    targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
  }
}

function getRegistroArt(campo){
  lprec = document.getElementsByName('articulos[pre_lista_precio]')[0].value;
  url = 'control.php?rqst=FACTURACION.ESPECIALES&action=setRegistroArt&task=ESPECIALESDET&codigoart='+campo+'&lprec='+lprec;
  document.getElementById('control').src = url;
  return;
}

function setRegistroArt(campo, descripcion, precio, editable){
  /*DETERMINAR EL FACTOR DEPENDIENDO DE LA MONEDA Y LA LISTA DE PRECIOS*/
  des = document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0];
  prec = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
  pre_lista_precio = document.getElementsByName('articulos[pre_lista_precio]')[0].value;	
  moneda = document.getElementsByName('datos[ch_fac_moneda]')[0].value;
  tipo_cambio = document.getElementsByName('datos[nu_tipocambio]')[0].value;
  if (pre_lista_precio=='01' && moneda=='02')
  	factor = 1/tipo_cambio;
  else {
  	if(pre_lista_precio=='60' && moneda=='01')
  		factor=tipo_cambio;
  	else
  		factor=1;
  }

  /*if (editable==''){
  	des.disabled = true;
  	prec.disabled = true;
  }else{
  	des.disabled = false;
  	prec.disabled=false;
  }*/
  codigo = document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0].value = campo;
  descrip = document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0].value = descripcion;
  precio = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0].value = Math.round(precio*factor*100)/100;
  cantidad = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].value='';
  cantidad = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].focus();

  return;
}

function CalcularValores2()
{
   /* var precio      = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
    var cantidad    = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0];
    var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
    var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
    var porc_igv    = document.getElementsByName('porce_fac_impuesto1')[0];
    var cant_dscto  = document.getElementsByName('datos[nu_fac_descuento1]')[0];
    var dscto       = document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0];
    var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
    
    calc_neto = precio.value * cantidad.value;
    
    if(cant_dscto.value!='' || cant_dscto.value>0)
    {
        calc_dscto = calc_neto * cant_dscto.value;
        calc_neto_dscto = calc_neto - calc_dscto;
        
    }else{
        calc_dscto = 0;
        calc_neto_dscto = calc_neto;
    }
    calc_igv  = calc_neto_dscto * porc_igv.value;
    neto.value  = calc_neto.toFixed(2);
    igv.value   = calc_igv.toFixed(2);
    dscto.value = calc_dscto.toFixed(2);
    calc_total  = calc_neto_dscto + calc_igv;
    total.value = calc_total.toFixed(2);*/
}

function CalcularValores()
{
	var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
	var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
	var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
    var porc_igv    = document.getElementsByName('porce_fac_impuesto1')[0];
    neto.value = (total.value/porc_igv.value).toFixed(2);
    igv.value = (neto.value*(porc_igv.value-1)).toFixed(2);
	/*var precio      = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0];
    var cantidad    = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0];
    var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0];
    var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0];
    var porc_igv    = document.getElementsByName('porce_fac_impuesto1')[0];
    var cant_dscto  = document.getElementsByName('datos[nu_fac_descuento1]')[0];
    var dscto       = document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0];
    var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0];
    var calc_neto = 0;
    var calc_igv = 0;
   
    calc_total = precio.value * cantidad.value;
    
    if(cant_dscto.value!='' || cant_dscto.value > 0)
    {
        calc_dscto = calc_total * cant_dscto.value;
    }else{
        calc_dscto = 0;
    }
    calc_total_dscto = calc_total - calc_dscto;
    calc_neto  = calc_total_dscto/porc_igv.value;
    calc_igv = (porc_igv.value-1)*calc_neto;
    neto.value = calc_neto.toFixed(2);
    total.value  = calc_total_dscto.toFixed(2);
    igv.value   = calc_igv.toFixed(2);
    dscto.value = calc_dscto.toFixed(2);*/
 
}

function AgregaArticulo()
{
	
    var codigo      = document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0].value;
    var descripcion = document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0].value;
    var precio      = document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0].value;
    var cantidad    = document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].value;
    var neto        = document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0].value;
    var igv         = document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0].value;
    var dscto       = document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0].value;
    var total       = document.getElementsByName('interface[dato_articulo][total_articulo][]')[0].value;
    //var isc 		= document.getElementsByName('datos[nu_fac_impuesto2]')[0].value;
    var recargo = document.getElementsByName('porce_recargo')[0].value;
    var dato = document.form_facturas.acumulado;
    var sw=0;
    var options = document.forms['form_facturas'].elements['cod_articulo[]'];
    var cuenta = 0;
    var isc = "";
    if (precio>0 && cantidad>0 && descripcion!='' && codigo!='' && precio>0 && cantidad > 0 && neto>0 && igv>0 && total>0){ 
    	
    	/*verificar que ya existe en el detalle*/
    	if(options != undefined){
    		isc = document.getElementsByName('datos[nu_fac_impuesto2]')[0].value;
    		cuenta = options.length;
    		if (cuenta != undefined){
    			for (var i = 0; i < cuenta; i++){
					if (options[i].value == codigo) {
						sw=1;
						break;
					}
				}
    		}else {
    			var auxiliar = document.getElementsByName('cod_articulo[]')[0].value;
    			if (auxiliar == codigo) sw=1;
			}
    	}
    	if (sw==0){
			
			url = 'control.php?rqst=FACTURACION.ESPECIALES&action=AgregaArticulo&task=ESPECIALESDET&codigo='+codigo+'&descripcion='+descripcion+'&precio='+precio+'&cantidad='+cantidad+'&neto='+neto+'&igv='+igv+'&dscto='+dscto+'&total='+total+'&recargo='+recargo+'&datos[nu_fac_impuesto2]='+isc;
	    	document.getElementById('control').src = url;
		    dato.value=parseInt(dato.value)+1;
		  	   	     
		    /*Limpiando los objetos enviados*/
		    document.getElementsByName('interface[dato_articulo][cod_articulo][]')[0].value='';
		    document.getElementsByName('interface[dato_articulo][desc_articulo][]')[0].value='';
		    document.getElementsByName('interface[dato_articulo][precio_articulo][]')[0].value='';
		    document.getElementsByName('interface[dato_articulo][cant_articulo][]')[0].value='';
		    document.getElementsByName('interface[dato_articulo][neto_articulo][]')[0].value='';
		    document.getElementsByName('interface[dato_articulo][igv_articulo][]')[0].value='';
		    document.getElementsByName('interface[dato_articulo][dscto_articulo][]')[0].value='';
		    document.getElementsByName('interface[dato_articulo][total_articulo][]')[0].value='';
		    /*-------------------------------*/
		    habilitar(false);
    	}else alert('El Item ya ha sido registrado!!!');
    }else alert('Ingrese el articulo correctamente, faltan datos !!!');
}

function verificar_completo(){

	var dato = document.form_facturas.acumulado;
	serie = document.getElementsByName('datos[ch_fac_seriedocumento]')[0];
	fecha = document.getElementsByName('datos[dt_fac_fecha]')[0];
	cliente = document.getElementsByName('datos[cli_codigo]')[0];
	formapago = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
	lista = document.getElementsByName('articulos[pre_lista_precio]')[0];
	tipocambio = document.getElementsByName('datos[nu_tipocambio]')[0];
	dscto = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	isc = document.getElementsByName('datos[nu_fac_impuesto2]')[0];
	igv = document.getElementsByName('datos[nu_fac_impuesto1]')[0];
	bruto = document.getElementsByName('datos[nu_fac_valorbruto]')[0];
	total = document.getElementsByName('datos[nu_fac_valortotal]')[0];
	check = document.getElementsByName('datos[ch_descargar_stock]')[0];
	
	if (serie.value=='' || fecha.value=='' || cliente.value=='' || formapago.value=='' || lista.value=='' || tipocambio.value=='' || dscto.value==''){
		alert('Los datos de la cabecera estan incompletos !!!');
		return false;
	}else {
		if (dato.value=1){
			alert('Debe ingresar articulos a la factura probando!!!');
			return false;
		}else {
			if (bruto.value>0 && igv.value>0 && total.value>0){
				if (parseInt(isc.value) >= parseInt(bruto.value)){
					alert('El Monto del ISC es demasiado alto, verifiquelo !!!');
					return false;
				}else{
					if (verificar_items_de_factura()){
						if (confirm('Desea guardar los datos de la factura?')){
						habilitar(true)
						}else return false;	
					}else{
						alert('faltan datos en el detalle de la factura');
						return false;
					}
				}
			}else{
				alert('El valor bruto y del igv debe ser mayor a 0');
				return false;
			}
		}
	}
	return true;
}

function verificar_items_de_factura(){
	sw=true;
	/* verifica los campos codigo, etc.*/
	options = document.forms['form_facturas'].elements['cod_articulo[]'];
	if (options.length==undefined){
		if (options.value == ''){
			sw = false; 
		}
	}else{
		for (var i = 0; i < options.length; i++){
			if (options[i].value == ''){
				sw = false; 
			}
		}
	}
	options = document.forms['form_facturas'].elements['desc_articulo[]'];
	if (options.length==undefined){
		if (options.value == ''){
			sw = false; 
		}
	}else{
		for (var i = 0; i < options.length; i++){
			if (options[i].value == ''){
				sw = false; 
			}
		}
	}
	options = document.forms['form_facturas'].elements['cant_articulo[]'];
	if (options.length==undefined){
		if (options.value == ''){
			sw = false; 
		}
	}else{
		for (var i = 0; i < options.length; i++){
			if (options[i].value == ''){
				sw = false; 
			}
		}
	}
	options = document.forms['form_facturas'].elements['precio_articulo[]'];
	if (options.length==undefined){
		if (options.value == ''){
			sw = false; 
		}
	}else{
		for (var i = 0; i < options.length; i++){
			if (options[i].value == ''){
				sw = false; 
			}
		}
	}
	options = document.forms['form_facturas'].elements['neto_articulo[]'];
	if (options.length==undefined){
		if (options.value == ''){
			sw = false; 
		}
	}else{
		for (var i = 0; i < options.length; i++){
			if (options[i].value == ''){
				sw = false; 
			}
		}
	}
	options = document.forms['form_facturas'].elements['igv_articulo[]'];
	if (options.length==undefined){
		if (options.value == ''){
			sw = false; 
		}
	}else{
		for (var i = 0; i < options.length; i++){
			if (options[i].value == ''){
				sw = false; 
			}
		}
	}
	options = document.forms['form_facturas'].elements['total_articulo[]'];
	if (options.length==undefined){
		if (options.value == ''){
			sw = false; 
		}
	}else{
		for (var i = 0; i < options.length; i++){
			if (options[i].value == ''){
				sw = false; 
			}
		}
	}
	return sw;
}

function habilitar(valor){
	tipodoc = document.getElementsByName('datos[ch_fac_tipodocumento]')[0];
	serie = document.getElementsByName('datos[ch_fac_seriedocumento]')[0];
	fecha = document.getElementsByName('datos[dt_fac_fecha]')[0];
	cliente = document.getElementsByName('datos[cli_codigo]')[0];
	credito = document.getElementsByName('datos[ch_fac_credito]')[0];
	formapago = document.getElementsByName('datos[ch_fac_forma_pago]')[0];
	anticipado = document.getElementsByName('datos[ch_fac_anticipo]')[0];
	lista = document.getElementsByName('articulos[pre_lista_precio]')[0];
	moneda = document.getElementsByName('datos[ch_fac_moneda]')[0];
	tipocambio = document.getElementsByName('datos[nu_tipocambio]')[0];
	dscto = document.getElementsByName('datos[ch_factipo_descuento1]')[0];
	descargar = document.getElementsByName('datos[ch_descargar_stock]')[0];	
	recargo = document.getElementsByName('datos[nu_fac_recargo2]')[0];
	isc = document.getElementsByName('datos[nu_fac_impuesto2]')[0];
	tipodoc.disabled = !valor;
	credito.disabled = !valor;
	moneda.disabled = !valor;
	serie.disabled = !valor;
	fecha.disabled = !valor;
	cliente.disabled = !valor;
	formapago.disabled = !valor;
	anticipado.disabled = !valor;
	lista.disabled = !valor;
	tipocambio.disabled = !valor;
	dscto.disabled = !valor;
	descargar.disabled = !valor;
	anticipado.disabled = !valor;
	
	recargo.disabled = !valor;
	isc.disabled = !valor;
}

function EliminarArticulo(valor,total)
{
	var dato = document.form_facturas.acumulado;
    var codigo = document.getElementsByName('registroid')[0].value;
    var recargo = document.getElementsByName('porce_recargo')[0].value;
    var isc = document.getElementsByName('datos[nu_fac_impuesto2]')[0].value;
    if (confirm('Confirma Eliminar el item '+valor+'?')){
    	url = 'control.php?rqst=FACTURACION.ESPECIALES&action=AgregaArticulo&task=ESPECIALESDET&dato_elimina='+valor+'&registroid='+codigo+'&recargo='+recargo+'&datos[nu_fac_impuesto2]='+isc;
    	document.getElementById('control').src = url;
    	if (total==1) habilitar(true);
    	dato.value=parseInt(dato.value)-1;
    }
}
