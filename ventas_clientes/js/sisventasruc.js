/*

  Funciones JavaScript 
  Sistema de Contabilidad ACOSA
  @TBCA Modificado por @MATT

*/

function ventanaSecundaria (URL){
   window.open(URL,"ventana1","width=120,height=300,scrollbars=NO");
}

function comprueba_extension(formulario, archivo) {
   extensiones_permitidas = new Array(".gif", ".jpg", ".doc", ".pdf");
   mierror = "";
   if (!archivo) {
      //Si no tengo archivo, es que no se ha seleccionado un archivo en el formulario
       mierror = "No has seleccionado ningún archivo";
   }else{
      //recupero la extensión de este nombre de archivo
      extension = (archivo.substring(archivo.lastIndexOf("."))).toLowerCase();
      //alert (extension);
      //compruebo si la extensión está entre las permitidas
      permitida = false;
      for (var i = 0; i < extensiones_permitidas.length; i++) {
         if (extensiones_permitidas[i] == extension) {
         permitida = true;
         break;
         }
      }
      if (!permitida) {
         mierror = "Comprueba la extensión de los archivos a subir. \nSólo se pueden subir archivos con extensiones: " + extensiones_permitidas.join();
       }else{
          //submito!
         alert ("Todo correcto. Voy a submitir el formulario.");
         formulario.submit();
         return 1;
       }
   }
   //si estoy aqui es que no se ha podido submitir
   alert (mierror);
   return 0;
} 

function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else
    document.getElementById('control').src = accionN;
	
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=MAESTROS.RUC&task=RUC';
    document.getElementById('control').src = url;
    return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=MAESTROS.RUC&task=RUC&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function getRegistro(campo){//OBSERVACI?N DANIEL
  url = 'control.php?rqst=MAESTROS.RUC&action=setRegistro&task=TARJMAGDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function validar_registro_ruc(){
	txtnumruc = document.getElementsByName('ruc[ruc]')[0];
	txtrazsocial = document.getElementsByName('ruc[razsocial]')[0];
	
		
	if (txtnumruc.value==''){
		alert('Ingrese N?mero de Ruc');
		return false;
	}
	if (txtrazsocial.value==''){
		alert('Ingrese Raz?n Social');
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
	urlPagina = 'control.php?rqst=MAESTROS.RUC&task=RUC';
    document.getElementById('control').src = urlPagina;
}
