function hola(){
alert('holha');
}

function mandarDatos(form,ope){
    if(ope!="Cancelar"){

	var ok = validarInclusionFacturas(form);
	//ok=true;
	//alert(ok);
	//if(ok){
	    var int_igv = parseFloat(form.monto_imp1.value);
	    var int_monto_imp = parseFloat(form.monto_imp.value);
	    if(int_igv>int_monto_imp){
		ok = false;
		alert('El igv es mayor que el monto imponible.');
	    } else
		ok = validarImportes(form);
	//}

	//if(ok){
	    form.accion.value = ope;
	    form.submit();
	//}
    }else{
	form.accion.value = ope;
	form.submit();
    }
}

function validarInclusionFacturas(form){
alert('hola');
var ok = true;
var msg = "";
	if(ok){
		if(form.fec_docu.value==""){ 
		var ok1 = false;
		msg = "No se ha ingresado la Fecha del Documento";
		form.fec_docu.style.backgroundColor='Yellow';
		}else{	var ok1 = true;
			form.fec_docu.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.fec_reg.value==""){ 
		var ok2 = false;
		msg = "No se ha ingresado la Fecha de Registro";
		form.fec_reg.style.backgroundColor='Yellow';
		}else{	var ok2 = true;
		form.fec_reg.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.num_registro.value==""){ 
		var ok3 = false;
		msg = "No se ha ingresado el Numero de Registro";
		form.num_registro.style.backgroundColor='Yellow';
		}else{	var ok3 = true;
		form.num_registro.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.cod_proveedor.value==""){ 
		var ok4 = false;
		msg = "No se ha ingresado el proveedor";
		form.cod_proveedor.style.backgroundColor='Yellow';
		}else{	var ok4 = true;
		form.cod_proveedor.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.cod_rubro.value==""){ 
		var ok5 = false;
		msg = "No se ha ingresado el rubro";
		form.cod_rubro.style.backgroundColor='Yellow';
		}else{	var ok5 = true;
		form.cod_rubro.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.cod_documento.value==""){ 
		var ok6 = false;
		msg = "No se ha ingresado el documento";
		form.cod_documento.style.backgroundColor='Yellow';
		}else{	var ok6 = true;
		form.cod_documento.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.serie_doc.value==""){ 
		var ok7 = false;
		msg = "No se ha ingresado la serie del documento";
		form.serie_doc.style.backgroundColor='Yellow';
		}else{	var ok7 = true;
		form.serie_doc.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.num_documento.value==""){ 
		var ok8 = false;
		msg = "No se ha ingresado el numero del documento";
		form.num_documento.style.backgroundColor='Yellow';
		}else{	var ok8 = true;
		form.num_documento.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.cod_docref.value==""){ 
		var ok9 = false;
		msg = "No se ha ingresado el codigo de referencia";
		form.cod_docref.style.backgroundColor='Yellow';
		}else{	var ok9 = true;
		form.cod_docref.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.num_docurefe.value==""){ 
		var ok10 = false;
		msg = "No se ha ingresado el numero del documento de referencia";
		form.num_docurefe.style.backgroundColor='Yellow';
		}else{	var ok10 = true;
		form.num_docurefe.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.fecha_ven.value==""){ 
		var ok11 = false;
		msg = "No se ha ingresado la fehca de vencimiento";
		form.fecha_ven.style.backgroundColor='Yellow';
		}else{	var ok11 = true;
		form.fecha_ven.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.cod_unidad.value==""){ 
		var ok12 = false;
		msg = "No se ha ingresado la unidad contable";
		form.cod_unidad.style.backgroundColor='Yellow';
		}else{	var ok12 = true;
		form.cod_unidad.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.cod_moneda.value==""){ 
		var ok13 = false;
		msg = "No se ha ingresado el codigo de la moneda";
		form.cod_moneda.style.backgroundColor='Yellow';
		}else{	var ok13 = true;
		form.cod_moneda.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.tasa_cambio.value==""){ 
		var ok14 = false;
		msg = "No se ha ingresado la tasa de cambio";
		form.tasa_cambio.style.backgroundColor='Yellow';
		}else{	var ok14 = true;
		form.tasa_cambio.style.backgroundColor='White';
		}
	}
	if(ok){
		if(form.monto_imp.value==""){ 
		var ok15 = false;
		msg = "No se ha ingresado el monto";
		form.monto_imp.style.backgroundColor='Yellow';
		}else{	var ok15 = true;
		form.monto_imp.style.backgroundColor='White';
		}
	}
	/*if(ok){
		if(form.importe_total.value==""){ 
		var ok16 = false;
		msg = "No se ha ingresado importe total";
		form.importe_total.style.backgroundColor='Yellow';
		}else{	var ok16 = true;
		form.importe_total.style.backgroundColor='White';
		}
	}*/
	
	/*if(ok){
		if(form.redondeo.value=="mal"){ 
		var ok17 = false;
		msg = "Redondeo mal efectuado";
		form.importe_total.style.backgroundColor='Yellow';
		}else{	var ok17 = true;
		form.importe_total.style.backgroundColor='White';
		}
	}*/
	if(ok){
	    if(form.prov_cta_detracc.value=="CtaPendiente"){ 
	    var ok19 = false;
	    msg = "Antes debe agregar una cuenta de DETRACCION al proveedor seleccionado.";
	    form.cod_proveedor.style.backgroundColor='Yellow';
	    document.getElementsByName('cod_proveedor')[0].focus();
	    }else{	
	    var ok19 = true;
	    form.cod_proveedor.style.backgroundColor='White';
	    }
	}
	var ok18 = true;
	
	/*Verificamos todos los campos*/
	if(ok1 && ok2 && ok3 && ok4 && ok5 && ok6 && ok7 && ok8 && ok9 && ok10 && ok11 && ok12 && ok13 && ok14 && ok15 && ok18 && ok19){
		return true;
	}else{
	   if(msg && ok19==false)
	   {
	       alert(msg);
	       return false;
	   }
	   alert("Existen campos necesarios que no han sido llenados, estan marcados con amarillo para que los llenes :-)");
	   return false;
	}
	
	
}

