function mostrarAyuda(url,cod,des,consulta){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function mostrarAyudaOrdenCompra(url,prov,alma,many_alma,tasa_cambio,cod_documento,cod_moneda
,rubrosinv){

var enlaza_inventarios = true;
if(rubrosinv==""){enlaza_inventarios=false;}

if(enlaza_inventarios){
	var correcto = true;
	var msg = "";
	if(prov==""){
		msg = "Debes de indicar el proveedor";
		correcto = false;
		
	}
	if(alma==""){
		msg = "Debes de indicar la Unidad Contable";
		correcto = false;
		
	}
	if(cod_documento==""){
		msg = "Debes de indicar el Documento";
		correcto = false;
		
	}
	if(cod_moneda==""){
		msg = "Debes de indicar la Moneda";
		correcto = false;	
	}
	if(correcto)
	{
		url = url+"?proveedor="+prov+"&cod_almacen="+alma+"&many_alma="+many_alma+"&tasa_cambio="+tasa_cambio+"&cod_documento="+cod_documento+"&cod_moneda="+cod_moneda; 
		window.open(url,'miwin','width=800,height=500,scrollbars=yes,menubar=no,left=0,top=0'); 
	}else{
	alert(msg);
	}
	
}

}

function validarInclusionFacturas(form){

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

function cambiarValue(campo, valor){
if(campo.value==""){
campo.value = valor; 
}else{
campo.value = "";
}
}


function getObj(name, nest) {
if (document.getElementById){
return document.getElementById(name).style;
}else if (document.all){
return document.all[name].style;
}else if (document.layers){
if (nest != ''){
return eval('document.'+nest+'.document.layers["'+name+'"]');
}
}else{
return document.layers[name];
}
}

//Hide/show layers functions
function showLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "visible";
}

function hideLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "hidden";
}

function mostrarFila(fila){
showLayer(fila);
}

function ocultarFila(fila){
hideLayer(fila);
}
function hacerDesaparecer(id){
	id.style.display='none';
}

function hacerAparecer(id){
	id.style.display='';
}
