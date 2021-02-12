/*

  Funciones JavaScript 
  Sistema 
  @FAP Modificado por @FAP

*/


function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else{
    //document.getElementById('control').src = accionN;
   document.forms[0].action= "control.php?rqst=PROMOCIONES.PUNTOSFIDELIZAMANUAL&task=PUNTOSFIDELIZAMANUAL";
   }
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=PROMOCIONES.PUNTOSFIDELIZAMANUAL&task=PUNTOSFIDELIZAMANUAL';
    document.getElementById('control').src = url;
    return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=PROMOCIONES.PUNTOSFIDELIZAMANUAL&task=PUNTOSFIDELIZAMANUAL&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function validar_registro_horariomulti(){
	try{
	
	idPunto = document.getElementsByName('idpunto')[0];
	txtDescripcion = document.getElementsByName('puntos')[0];
	
		if(idPunto.value==''){
			alert('�Seleccione un Registro!');
			return false;
		}
		else if (txtDescripcion.value==''){
			alert('�Ingrese un puntaje!');
			return false;
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
  //alert(key);
  return (key <= 13 || (key >= 48 && key <= 57));
}
function soloNumerosDec(evento)
{
  // Algunos caracteres: backspace = 8, enter = 13, '0' = 48, '9' = 57
  var nav4 = window.Event ? true : false;
  var key = nav4 ? evento.which : evento.keyCode;
  //alert(key);
  return (key <= 13 || (key >= 48 && key <= 57) || key == 46 );
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
var fini = devuelve_fecha(f1);
var fven = devuelve_fecha(f2);
	if(fven < fini){
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
	urlPagina = 'control.php?rqst=PROMOCIONES.PUNTOSFIDELIZAMANUAL&task=PUNTOSFIDELIZAMANUAL';
    document.getElementById('control').src = urlPagina;
}

