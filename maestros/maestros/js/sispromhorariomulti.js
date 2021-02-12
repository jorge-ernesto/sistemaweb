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
   document.forms[0].action= "control.php?rqst=PROMOCIONES.HORARIOMULTI&task=HORARIOMULTI";
   }
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
	url = 'control.php?rqst=PROMOCIONES.HORARIOMULTI&task=HORARIOMULTI';
    document.getElementById('control').src = url;
    return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=PROMOCIONES.HORARIOMULTI&task=HORARIOMULTI&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function validar_registro_horariomulti(){
	try{
	
	idCampania = document.getElementsByName('idcampania')[0];
	txtDescripcion = document.getElementsByName('horamultidescripcion')[0];
	txtDias = document.getElementsByName('horamultidias')[0];
	slcHoraIni = document.getElementsByName('horamultihoraini')[0];
	slcMinIni = document.getElementsByName('horamultiminutoini')[0]; 
	slcHoraFin = document.getElementsByName('horamultihorafin')[0];
	slcMinFin = document.getElementsByName('horamultiminutofin')[0];
	txtFactorMulti =document.getElementsByName('horamultifactor')[0];
	
		if(idCampania.value==''){
			alert('¡Seleccione una campaña!');
			return false;
		}
		else if (txtDescripcion.value==''){
			alert('¡Ingrese una Descripción!');
			return false;
		}
		else if(txtDias.value==''){
			alert('¡Ingrese cantidad de Días de Multiplicación!');
			return false;
		}
		else if(slcHoraIni.value>slcHoraFin.value){
			alert('¡Hora de Inicio no puede ser mayor a Hora Fin!');
			return false;
		}
		else if((slcHoraIni.value==slcHoraFin.value) && (slcMinIni.value>slcMinFin.value)){
			alert('¡Minutos de hora de inicio no pueden ser mayor a los de hora fin!');
			return false;
		}
		else if(txtFactorMulti.value==''){
			alert('¡Ingrese Factor de multiplicación!');
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
	urlPagina = 'control.php?rqst=PROMOCIONES.HORARIOMULTI&task=HORARIOMULTI';
    document.getElementById('control').src = urlPagina;
}

