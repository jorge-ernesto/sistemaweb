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
   document.forms[0].action= "control.php?rqst=MOVIMIENTOS.TARGPROMOCION&task=TARGPROMOCION";
   }
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION';
    document.getElementById('control').src = url;
    return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function validar_registro_cuenta(){
	
	txtIdCuenta = document.getElementsByName('idcuenta')[0];
	txtNumero = document.getElementsByName('cuentanumero')[0];
	txtNombre = document.getElementsByName('cuentanombres')[0];
	
	//alert("('"+txtIdCuenta.value+"')");
	/*if(txtIdCuenta.value==''){
		alert('No se ha registrado una Cuenta, registre una para poder ingresar tarjetas');
		return false;
	}*/
	if (txtNumero.value==''){
		alert('¡Ingrese Número de cuenta!');
		return false;
	}	
	else if(txtNombre.value==''){
		alert('¡Ingrese Nombre de titular de cuenta!');
		return false;
	}
	else return true;
	
}
function validar_registro_tarjeta(nu_original){
	
try{
		
	txtIdCuenta   = document.getElementsByName('idcuenta')[0];
	txtNroTarjeta = document.getElementsByName('tarjetanumero')[0];
	txtDescTarjeta = document.getElementsByName('tarjetadescripcion')[0];
	txtPlacTarjeta = document.getElementsByName('tarjetaplaca')[0];
	dtpFechaVenc	= document.getElementsByName('tarjetafechaven')[0];
	txtAccion = document.getElementsByName('accion')[0];
	txtMotivoCambio = document.getElementsByName('motivocambio')[0];
	cboMotivoDuplicada = document.getElementsByName('motivoduplicada')[0];
	
	if(txtIdCuenta.value==''){
		alert('¡No se ha registrado una Cuenta, registre una para poder ingresar tarjetas!');
		return false;
	}else if(txtNroTarjeta.value==''){
		alert('¡Debe ingresar Nº de Tarjeta!');
		return false;
	}else if(txtNroTarjeta.value!=nu_original && txtMotivoCambio!=null && txtMotivoCambio.value=='' && cboMotivoDuplicada!=null && cboMotivoDuplicada.value=='') {
		alert('Ha cambiado el numero de tarjeta. Debe ingresar un motivo para el cambio');
		return false;
	}else if(txtDescTarjeta.value==''){
		alert('¡Debe ingresar una descripción para la Tarjeta!');
		return false;	
	}else if(txtPlacTarjeta.value==''){
		alert('¡Debe ingresar la placa para la Tarjeta!');
		return false;	
	
	}else if(dtpFechaVenc.value==''){
		alert('¡Debe seleccionar la fecha de vencimiento de la Tarjeta!');
		return false;	
	
	}
	else{
		if(txtAccion.value=='actualizartarjeta'){
			txtFecCreacion = document.getElementsByName('auxfechacreacion')[0];
			var comparacion =compara(txtFecCreacion.value,dtpFechaVenc.value);
			if(comparacion=='0'){
				alert('¡Fecha de Vencimiento no puede ser menor a Fecha de Creación!');
			return false;	
			}
			
		}else{
			txtFecServer = document.getElementsByName('fecServer')[0];
			var comparacion =compara(txtFecServer.value,dtpFechaVenc.value);
			
			if(comparacion=='0'){
				alert('¡Fecha de Vencimiento no puede ser menor a la Fecha Actual!');
			return false;	
			}
		}
		//  Adicionar Tarjeta	
		//alert(url);
		var url = "control.php?rqst=PROMOCIONES.TARGPROMOCION&task=TARGPROMOCION&action=Adicionar Tarjeta";
		//document.getElementById('control').src = url;
		document.forms[0].action=url;
		document.forms[0].submit();
	}
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

function verificaCambioTarjeta(original) {
	if (original=="")
		return;
	fe_actual = document.getElementsByName("tarjetanumero")[0];
	nu_actual = fe_actual.value;
	if (nu_actual==original) {
		document.getElementById("filamotivocambio1").style.visibility = "hidden";
		document.getElementById("filamotivocambio2").style.visibility = "hidden";
		return;
	}
	document.getElementById("filamotivocambio1").style.visibility = "visible";
	document.getElementById("filamotivocambio2").style.visibility = "visible";
}

