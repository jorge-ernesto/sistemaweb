/*

  Funciones JavaScript 
  Sistema de Contabilidad ACOSA
  @TBCA Modificado por @MATT

*/
function validarenteros(obj) {
  	txt = obj.value;
  	if(parseInt(txt) != parseFloat(txt)) {
    		alert('Solo numeros enteros');
                window.document.formul.obj.value="";
    		obj.focus();
  	}
}

function cambiarCombo(obj) {
	if(obj.value=='C'){//Combustible
		window.document.getElementById("isla1").style.display="block";
   		window.document.getElementById("isla2").style.display="none";
		window.document.getElementById("isla2").value="";
	}
	else{//Market
		window.document.getElementById("isla1").style.display="none";
   		window.document.getElementById("isla2").style.display="block";
		window.document.getElementById("isla1").value="";
	}
}

function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else{
    //document.getElementById('control').src = accionN;
   document.forms[0].action= "control.php?rqst=MAESTROS.TRABAJADORXISLA&task=TRABAJADORXISLA";
   }
}

function soloNumeros(evento)
{
  // Algunos caracteres: backspace = 8, enter = 13, '0' = 48, '9' = 57
  var nav4 = window.Event ? true : false;
  var key = nav4 ? evento.which : evento.keyCode;
  return (key <= 13 || (key >= 48 && key <= 57));
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=MAESTROS.TRABAJADORXISLA&task=TRABAJADORXISLA';
    document.getElementById('control').src = url;
    return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=MAESTROS.TRABAJADORXISLA&task=TRABAJADORXISLA&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function validar_registro_trab(){
	txtCodigo = document.getElementsByName('trab[codigo]')[0];
	txtNombre = document.getElementsByName('trab[nombre]')[0];
	txtApellidoPat = document.getElementsByName('trab[apepat]')[0];
	txtApellidoMat = document.getElementsByName('trab[apemat]')[0];
	rbSexo = document.getElementsByName('trab[sexo]')[0];
	txtDireccion = document.getElementsByName('trab[direccion]')[0];
	txtTelefono = document.getElementsByName('trab[telefono]')[0];
	txtDni= document.getElementsByName('trab[dni]')[0];
	txtFechaNac = document.getElementsByName('fechaNac')[0];
	
		
	if (txtCodigo.value==''){
		alert('Ingrese Código de Trabajador');
		return false;
	}
	if (txtNombre.value==''){
		alert('Ingrese Nombre de Trabajador');
		return false;
	}
	if (txtApellidoPat.value==''){
		alert('Ingrese Apellido Paterno del Trabajador');
		return false;
	}
	
	if (txtApellidoMat.value==''){
		alert('Ingrese Apellido Materno del Trabajador');
		return false;
	}
	
	if (rbSexo.checked == false){
		alert('Seleccione Sexo del Trabador');
		return false;
	}
	
	if (txtDireccion.value==''){
		alert('Ingrese Dirección del Trabajador');
		return false;
	}
	
	if (txtTelefono.value==''){
		alert('Ingrese Teléfono del Trabajador');
		return false;
	}
	
	if (txtDni.value==''){
		alert('Ingrese DNI del Trabajador');
		return false;
	}
	
	if (txtFechaNac.value==''){
		alert('Seleccione Fecha de Nacimiento del Trabajador');
		return false;
	}
	
	
	/*if (confirm('Desea Grabar/Actualizar el registro?')) {
		txtnumruc.disabled=false;
		return true;
	}*/
	else return true;
}


function limpiar_cajas(){
	
	var txtnumruc = document.getElementsByName('ruc[ruc]')[0];
	var txtrazsocial = document.getElementsByName('ruc[razsocial]')[0];
	
	codigo.value='';
	cuenta.value='';

	return ;
}


function copyOptions(sourceL, targetL){
  for (i=0; i<sourceL.length; i++){
    targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
  }
}

function volver_atras(){
	urlPagina = 'control.php?rqst=MAESTROS.TRABAJADORXISLA&task=TRABAJADORXISLA';
    document.getElementById('control').src = urlPagina;
}

