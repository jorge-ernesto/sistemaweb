/*

  Funciones JavaScript 
  Sistema 
  @DPC Modificado por @DPC

*/


function confirmarLink(pregunta, accionY, accionN, target){
	if(confirm(pregunta))
		document.getElementById('control').src = accionY;
	else {
		//document.getElementById('control').src = accionN;
		document.forms[0].action= "control.php?rqst=PROMOCIONES.CAMPANIAFIDE&task=CAMPANIAFIDE";
	}
}

function confirmarForm(pregunta, form) {
	if (confirm(pregunta))
		return true;
	return false;
}


function regresar() {
	url = 'control.php?rqst=PROMOCIONES.CAMPANIAFIDE&task=CAMPANIAFIDE';
	document.getElementById('control').src = url;
	return;
}



function PaginarRegistros(rxp, valor) {
	//rxp = rxp.value;
	urlPagina = 'control.php?rqst=PROMOCIONES.CAMPANIAFIDE&task=CAMPANIAFIDE&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;
}

function validar_registro_campaniafide() {
	try {
		txtDescripcion = document.getElementsByName('campaniadescripcion')[0];
		txtFechaIni = document.getElementsByName('campaniafechaini')[0];
		txtFechaFin = document.getElementsByName('campaniafechafin')[0];
		txtDiasVen = document.getElementsByName('campaniadiasven')[0];
		chbTipoCli = document.getElementsByName('campaniatiposcli[]');
		txtRepeticiones = document.getElementsByName('campaniarepeticiones')[0];
		//alert(chbTipoCli.value); 
		//alert(chbTipoCli.length);
		txtAccion = document.getElementsByName('action')[0];

		//Validar Descripcion
		if (txtDescripcion.value=='') {
			alert('¡Ingrese una Descripción!');
			return false;
		}

		//Validar días de Vencimiento
		if(txtDiasVen.value=='') {
			alert("¡Ingrese los días de vencimiento!");
			return false;
		}

		//Validar Fechas de Inicio/Fin, sólo si ambos campos existen
		if (txtFechaFin!=null) {
			var comparacion='';
			txtFecServer = document.getElementsByName('fecServer')[0];

			if (txtFechaFin.value=='') {
				alert('¡Ingrese Fecha de Fin!');
				return false;
			}

			//Comparar Fecha de Fin con Fecha de Servidor
			comparacion = compara(txtFecServer.value,txtFechaFin.value);
			if (comparacion=='0') {
				alert('¡Fecha de Fin no puede ser menor a la Fecha Actual!');
				return false;	
			}

			if (txtFechaIni!=null) {
				if (txtFechaIni.value=='') {
					alert('¡Ingrese Fecha de Inicio!');
					return false;
				}

				//Comparar fecha de Inicio con Fecha de Servidor
				//comparacion = compara(txtFecServer.value,txtFechaIni.value);
				//if (comparacion=='0') {
				//	alert('¡Fecha de Inicio no puede ser menor a la Fecha Actual!');
				//	return false;					
				//}

				//Comparar Fecha de Inicio con fecha de Fin
				comparacion = compara(txtFechaIni.value,txtFechaFin.value);
				if (comparacion=='0') {
					alert('¡Fecha de Fin no puede ser menor a la Fecha de Inicio!');
					return false;	
				}		
			}
		}


		//Validar que haya al menos un tipo de cuenta, si es que existen los campos
		if (chbTipoCli!=null && chbTipoCli.length>0) {
			for(i=0;i<=chbTipoCli.length-1;i++)
				if(chbTipoCli[i].checked==true)
					break;

			if(i==chbTipoCli.length) {
				alert("¡Debe seleccionar al menos un Tipo de Cliente!");
				return false;
			}
		}

		if (confirm("¿Seguro que desea crear la Campaña?")==true)
			return true;
		else
			return false;
//		return false;
	} catch(e) {
		alert(e);
	}
	return false;
}


function copiar(valor,campo) {
	document.getElementsByName(campo)[0].value=valor.value;
}

function soloNumeros(evento) {
	// Algunos caracteres: backspace = 8, enter = 13, '0' = 48, '9' = 57
	var nav4 = window.Event ? true : false;
	var key = nav4 ? evento.which : evento.keyCode;
	//alert(key);
	return (key <= 13 || (key >= 48 && key <= 57));
}

function soloNumerosDec(evento) {
	// Algunos caracteres: backspace = 8, enter = 13, '0' = 48, '9' = 57
	var nav4 = window.Event ? true : false;
	var key = nav4 ? evento.which : evento.keyCode;
	//alert(key);
	return (key <= 13 || (key >= 48 && key <= 57) ||key == 46 );
}

function mostrarAyuda(url,cod,des,consulta) {
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
	window.open(url,'miwin','width=500,height=280,scrollbars=yes,menubar=no,left=390,top=20');
}

function compara(fec0,fec1){
	var bRes = '1';  
	var sDia0 = fec0.substr(0, 2);  
	var sMes0 = fec0.substr(3, 2);  
	var sAno0 = fec0.substr(6, 4);  
	var sDia1 = fec1.substr(0, 2);  
	var sMes1 = fec1.substr(3, 2);  
	var sAno1 = fec1.substr(6, 4);  
	if (sAno0 > sAno1) bRes = '0';  
	else {
		if (sAno0 == sAno1) {
			if (sMes0 > sMes1)
				bRes = '0';  
			else {
				if (sMes0 == sMes1)
					if (sDia0 > sDia1)
						bRes = '0';
			}
		}
	}  
	return bRes;  
}


function copyOptions(sourceL, targetL){
	for (i=0; i<sourceL.length; i++)
		targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
}

function volver_atras() {
	urlPagina = 'control.php?rqst=PROMOCIONES.CAMPANIAFIDE&task=CAMPANIAFIDE';
	document.getElementById('control').src = urlPagina;
}

