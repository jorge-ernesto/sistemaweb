/*

  Funciones JavaScript 
  Sistema 
  @DPC Modificado por @DPC

*/


function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else{
    //document.getElementById('control').src = accionN;
   document.forms[0].action= "control.php?rqst=PROMOCIONES.CANJEITEM&task=CANJEITEM";
   }
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=PROMOCIONES.CANJEITEM&task=CANJEITEM';
    document.getElementById('control').src = url;
    return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=PROMOCIONES.CANJEITEM&task=CANJEITEM&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function validar_registro_productocanje(){
	try{
	txtCodArticulo = document.getElementsByName('itemarticulo')[0];
	txtDescItem = document.getElementsByName('itemdescripcion')[0];
	txtFechaVen = document.getElementsByName('itemfechaven')[0];
	txtAccion = document.getElementsByName('accion')[0];
	
	
	if (txtCodArticulo.value==''){
		alert('¡Ingrese un Artículo!');
		return false;
	}	
	else if(txtDescItem.value==''){
		alert('¡Ingrese Descripción de Producto!');
		return false;
	}
	else if(txtFechaVen.value!=''){
		if(txtAccion.value=='actualizaritem'){	
			
			txtFechaCrea = document.getElementsByName('itemfechacrea')[0];
			
			var comparacion =compara(txtFechaCrea.value,txtFechaVen.value);
			if(comparacion=='0'){
				alert('¡Fecha de Vencimiento no puede ser menor a Fecha de Creación!');
			return false;	
			}
		}
		else{
			txtFecServer = document.getElementsByName('fecServer')[0];
			var comparacion =compara(txtFecServer.value,txtFechaVen.value);
			if(comparacion=='0'){
				alert('¡Fecha de Vencimiento no puede ser menor a la Fecha Actual!');
			return false;	
			}
		}
	}
	else return true;
	}
	catch(e){
		alert(e);
		}
	
}


function copiar(valor,campo){

document.getElementsByName(campo)[0].value=valor.value;

}

function soloNumeros(evento)
{
  // Algunos caracteres: backspace = 8, enter = 13, '0' = 48, '9' = 57
  var nav4 = window.Event ? true : false;
  var key = nav4 ? evento.which : evento.keyCode;
  return (key <= 13 || (key >= 48 && key <= 57));
}

function mostrarAyuda(url,cod,des,consulta){

url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=280,scrollbars=yes,menubar=no,left=390,top=20');
}
function devuelve_fecha(fecha){

fecha = fecha.replace(/[-]/g, "/");
fecha = new Date(fecha);
return fecha
}

function compara(f1,f2){

//var fechanow = new Date("dd/mm/yyyy");
var fcrea = devuelve_fecha(f1);
var fven = devuelve_fecha(f2);

if(fven < fcrea){
	return '0';
}else{
	return '1';
	}

}


function copyOptions(sourceL, targetL){
  for (i=0; i<sourceL.length; i++){
    targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
  }
}

function volver_atras(){
	urlPagina = 'control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION';
    document.getElementById('control').src = urlPagina;
}

