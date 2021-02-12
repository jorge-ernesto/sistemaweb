
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
   document.forms[0].action= "control.php?rqst=MAESTROS.LADOS&task=LADOS";
   }
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=MAESTROS.LADOS&task=LADOS';
    document.getElementById('control').src = url;
    return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=MAESTROS.LADOS&task=LADOS&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function getRegistro(campo){//OBSERVACI?N DANIEL
  url = 'control.php?rqst=MAESTROS.LADOS&action=setRegistro&task=TARJMAGDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function validar_registro_lado(){
	txtLado = document.getElementsByName('lado[txtlado]')[0];

	cbxProd1 = document.getElementsByName('lado[cbxprod1]')[0];
	cbxProd2 = document.getElementsByName('lado[cbxprod2]')[0];
	cbxProd3 = document.getElementsByName('lado[cbxprod3]')[0];
	cbxProd4 = document.getElementsByName('lado[cbxprod4]')[0];

	txtNDCantidad = document.getElementsByName('lado[txtndcantidad]')[0];
	txtNDPrecio = document.getElementsByName('lado[txtndprecio]')[0];
	txtNDImporte = document.getElementsByName('lado[txtndimporte]')[0];
	txtNDCantidadContometro = document.getElementsByName('lado[txtndcantidadcontometro]')[0];
	txtNDImporteContometro = document.getElementsByName('lado[txtndimportecontometro]')[0];

	cbxIdInterfase = document.getElementsByName('lado[cbxidinterfase]')[0];
	txtLadoInterfase = document.getElementsByName('lado[txtladointerfase]')[0];
	
		
	if (txtLado.value==''){
		alert('Ingrese CСdigo de Lado');
		return false;
	}
	
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
	urlPagina = 'control.php?rqst=MAESTROS.LADOS&task=LADOS';
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