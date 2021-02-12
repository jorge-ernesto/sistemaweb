/*

  Funciones JavaScript 
  Sistema de Contabilidad ACOSA
  @TBCA Modificado por @MATT

*/

function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else{
    //document.getElementById('control').src = accionN;
   document.forms[0].action= "control.php?rqst=MAESTROS.TRABAJADOR&task=TRABAJADOR";
   }
}

function confirmarLink_Intelec(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else{
    //document.getElementById('control').src = accionN;
   //document.forms[0].action= "control.php?rqst=MAESTROS.TRABAJADOR&task=TRABAJADOR";
   }
}


function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=MAESTROS.TRABAJADOR&task=TRABAJADOR';
    document.getElementById('control').src = url;
    return;
}

function regresar_Intelec(){
	url = 'control.php?rqst=MAESTROS.INTELEC&task=INTELEC';
    document.getElementById('control').src = url;
    return;
}


function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=MAESTROS.TRABAJADOR&task=TRABAJADOR&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function getRegistro(campo){//OBSERVACI?N DANIEL
  url = 'control.php?rqst=MAESTROS.TRABAJADOR&action=setRegistro&task=TARJMAGDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function PaginarRegistros_Intelec(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=MAESTROS.INTELEC&task=INTELEC&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function getRegistro_Intelec(campo){//OBSERVACI?N DANIEL
  url = 'control.php?rqst=MAESTROS.INTELEC&action=setRegistro&task=TARJMAGDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}


function validar_registro_trab(){
	txtCodigo = document.getElementsByName('trab[codigo]')[0];
	txtNombre = document.getElementsByName('trab[nombre]')[0];
	txtApellidoPat = document.getElementsByName('trab[apepat]')[0];
	txtApellidoMat = document.getElementsByName('trab[apemat]')[0];
	rbSexoM = document.getElementsByName('trab[sexo]')[0];
	rbSexoF = document.getElementsByName('trab[sexo]')[1];
	txtDireccion = document.getElementsByName('trab[direccion]')[0];
	txtTelefono = document.getElementsByName('trab[telefono]')[0];
	txtDni= document.getElementsByName('trab[dni]')[0];
	txtFechaNac = document.getElementsByName('fechaNac')[0];
	
		
	if (txtCodigo.value==''){
		alert('Ingrese C�digo de Trabajador');
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
	
	if (rbSexoM.checked == false && rbSexoF.checked == false ){
		alert('Seleccione Sexo del Trabador');
		return false;
	}
		
	
	
	if (txtDireccion.value==''){
		alert('Ingrese Direcci�n del Trabajador');
		return false;
	}
	
	if (txtTelefono.value==''){
		alert('Ingrese Tel�fono del Trabajador');
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


function validar_registro_trab_Intelec(){
	txtDispositivo = document.getElementsByName('trab[dispositivo]')[0];
	txtTipo = document.getElementsByName('trab[tipo]')[0];
	txtSleep = document.getElementsByName('trab[sleep]')[0];
	txtMaxSleep = document.getElementsByName('trab[maxsleep]')[0];

	if (txtDispositivo.value==''){
		alert('Ingrese el Nombre del Dispositivo');
		return false;
	}
	if (txtTipo.value==''){
		alert('Ingrese el Tipo del Dispositivo');
		return false;
	}
	if (txtSleep.value==''){
		alert('Ingrese Sleep del Dispositivo');
		return false;
	}
	
	if (txtMaxSleep.value==''){
		alert('Ingrese MaxSleep del Dispositivo');
		return false;
	}

	return true;
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
	urlPagina = 'control.php?rqst=MAESTROS.TRABAJADOR&task=TRABAJADOR';
    document.getElementById('control').src = urlPagina;
}

function volver_atras_Intelec(){
	urlPagina = 'control.php?rqst=MAESTROS.INTELEC&task=INTELEC';
    document.getElementById('control').src = urlPagina;
}

function f_sexo(s){
	var sex = s;
	var sexRad = document.getElementsByName('trab[sexo]')[0];
	
	if (sex.value == 'M')
	    sexRad[0].checked = 'checked'
	else
	    sexRad[1].checked = 'checked'

}