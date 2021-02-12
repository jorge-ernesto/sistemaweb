function mostrarAyuda(url,cod,des,consulta){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<? echo $fechad;?>&fechaa=<? echo $fechaa;?>&cod_almacen=<? echo $cod_almacen;?>&almacen_dis=<?echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function mostrarAyudaOrdenCompra(url,prov,alma,many_alma){
url = url+"?proveedor="+prov+"&cod_almacen="+alma+"&many_alma="+many_alma; 
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