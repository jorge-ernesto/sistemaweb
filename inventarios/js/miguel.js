function mostrarAyuda(url,cod,des,consulta){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}


function mostrarAyudaOrdenCompra(url,prov,alma,many_alma,cod,fm){
url = url+"?proveedor="+prov+"&cod_almacen="+alma+"&many_alma="+many_alma+"&cod="+cod+"&fm="+fm; 
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function abrirVentana(url){
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
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

function iniciarFormulario(check){
	ingdir = check.value;
	if(ingdir==""){
		ocultarFila('ord_compra');
		check.value="SI";
	}
	if(ingdir=="SI"){
		check.value="SI";
	}
	if(ingdir=="NO"){
		check.value="NO";
	}
	
	
}

function validarIngreso(pedido,atendido,ingresado){
	var ped = pedido.value;
	var ate = atendido.value;
	var ing = ingresado.value;
	var ret = true;
	ped = parseFloat(ped);
	ate = parseFloat(ate);
	ing = parseFloat(ing);

	
	var saldo = ped - ate;
	
	
	if(saldo < ing){
		alert('No se puede hacer un ingreso mayor al saldo del pedido,\nPedido -------------------------------------------> '+ped+' \nIngresado hasta la Fecha --------------> '+ate+'\nMAXIMO PUEDES INGRESAR ----->'+saldo);
		ingresado.value = saldo;
		ingresado.focus();
		ret = false
	}
	return ret;
}

function reflejarData(dato, reflejo){

	reflejo.value = dato.value;

}

function escribirCelda(){
	document.all("texto").innerText = 'Por la grandisima';
}

function select(form){
	cod = form.combo.selectedIndex;
	des = form.combo.options[cod].text;
	//alert(cod + '---'+ des);
	
	form.submit();
	form.combo.value=cod+1;
	}
	

function hacerDesaparecer(id){
	id.style.display='none';
}

function hacerAparecer(id){
	id.style.display='';
}
