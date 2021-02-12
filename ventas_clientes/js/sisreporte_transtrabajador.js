/*

  Funciones JavaScript 
  Sistema 
  @DPC Modificado por @JB

*/

$( window ).load(function(){

	$( document ).ready(function() {

		$.datepicker.regional['es'] = {
			    closeText: 'Cerrar',
			    prevText: '<Ant',
			    nextText: 'Sig>',
			    currentText: 'Hoy',
			    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
			    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
			    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
			    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
			    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
			    weekHeader: 'Sm',
			    dateFormat: 'dd/mm/yy',
			    firstDay: 1,
			    isRTL: false,
			    showMonthAfterYear: false,
			    yearSuffix: ''
		};

		$.datepicker.setDefaults($.datepicker.regional['es']); 

		$("#txtnofechaini").datepicker({
			changeMonth: true,
			changeYear: true,
		});

		$("#txtnofechafin").datepicker({
			changeMonth: true,
			changeYear: true,
		});

	});

});

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function PaginarRegistros(rxp, valor,fechainicio,fechafin)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=PROMOCIONES.REPORTE_NUMTRANSFIDELIZA&task=REPORTE_NUMTRANSFIDELIZA&rxp='+rxp+'&pagina='+valor+'&action=Consultar&fechainicio='+fechainicio+'&fechafin='+fechafin;
    document.getElementById('control').src = urlPagina;
}
function validarFechas(){
	try{
		txtFechaInicio = document.getElementsByName('fechainicio')[0];
		txtFechaFin = document.getElementsByName('fechafin')[0];
	
	if(txtFechaInicio.value==''){
		alert('�Seleccione Fecha de Inicio!');
		return false;
	}
	else if(txtFechaFin.value==''){
		alert('�Seleccione Fecha de Fin!');
		return false;
	
	}else if(txtFechaFin.value!=''){
			var comparacion =compara(txtFechaInicio.value,txtFechaFin.value);
			if(comparacion=='0'){
				alert('�Fecha Fin no puede ser menor a Fecha Inicio!');
			return false;	
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

function soloNumeros(evento){
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
