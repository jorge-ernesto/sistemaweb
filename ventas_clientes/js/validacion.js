// JavaScript Document



function llenarValor(combo,txt){

		var	opcion = combo.options[combo.selectedIndex].value;
		txt.value = opcion;
		txt.focus();
		/*
		<select name="surtidor_help" onChange="javascript:llenarValor(surtidor_help,form1.cod_surtidor)" >
		*/

}

function validarNumeroEntero(campo){
	var dato = campo.value;
	var long = dato.length;
	var c = dato.charAt(long-1);
	var entero = parseInt(c); 
	var respuesta = true;
	if(isNaN(entero)){
	 
	 if(campo.value!=""){
	 alert ("Solo se permite ingresar nmeros enteros"); 
	 var ret = dato.substr(0,long-1);
	 campo.value = ret;
	 respuesta = false;
	 }

	}
	return respuesta;
}

function validarNumeroDecimales(campo){
	var dato = campo.value;
	var long = dato.length;
	var c = dato.charAt(long-1);
	var entero = parseInt(c);
	var respuesta = true;
	if(isNaN(entero)){
		if(c=="."){
		//nada
		}else{
		if(campo.value!=""){
		alert ("Solo se permite ingresar nmeros");
	 	var ret = dato.substr(0,long-1);
	 	campo.value = ret;
	 	respuesta = false;
		}
		}
	}
	return respuesta;
}

function validarFecha(fecha){
	var dato = fecha.value;
	var long = dato.length;
	var c = dato.charAt(long-1);
	var ret = "";

	if(long==2){
		if(c=="/"){
		alert("Los dias deben ser de 2 digitos, el dia 2 es 02 y el dia 20 es 20");
		}
		var tmp = parseInt(dato.substr(0,2));
		if(tmp > 31){
			alert("El campo dia solo puede llegar hasta 31 ");
			ret = dato.substr(0,3);
			fecha.value = ret;
		}
	}
	if(long<=2){
		validarNumeroEntero(fecha);
	}

	if(long==3){
		if(c!="/"){
		alert("El formato de la fecha es dd/mm/a�, ejemplo \n Navidad Seria 25/12/2004");
		ret = dato.substr(0,long-1);
		fecha.value = ret;
		}
	}
	if(long > 3 && long < 6){
		if(isNaN(c)){
		alert("Solo puedes ingresar nmeros"); 
		ret = dato.substr(0,long-1);
		fecha.value = ret;
		}		
	}
	if(long == 5){
		var tmp1 = parseInt(dato.substr(3,5));
		if(tmp1 > 12){
			alert("En el campo de meses solo puedes ingresar 01 a 12 ");
			ret = dato.substr(0,3); 
			fecha.value = ret;	
		}
		if(c=="/"){
		alert("Los meses deben ser de 2 digitos, el mes 4 es 04 y diciembre seria 12");
		}
	}
	if(long==6){
		if(c!="/"){
		alert("El formato de la fecha es dd-mm-a�, ejemplo \n Navidad Seria 25-12-2004");
		ret = dato.substr(0,long-1);
		fecha.value = ret;
		}
	}
	if(long > 6 && long < 11){
		if(isNaN(c)){
		alert("Solo puedes ingresar nmeros");
		ret = dato.substr(0,long-1);
		fecha.value = ret;
		}

	}

	}

	function validarDia(campo){
	var dato = campo.value;
	if(dato.length==2){
		if(dato > 31){
			alert("Solo se pueden ingresar dias entre 01 y 31");
			campo.value = "";
		}
	}

	}


function validarMes(campo){
	var dato = campo.value;
	if(dato.length==2){
		if(dato > 12){
			alert("Solo se pueden ingresar meses entre 01 y 12");
			campo.value = "";
		}
	}

	}

function validarYear(campo)
{
	var dato = campo.value;
	if(dato.length==4){
		if(dato < 2002){
			alert("Solo se pueden ingresar a�s desde 2002 en adelante");
			campo.value = "";
		}
	}
	
}

function Mostrar(){
	document.getElementById('ver').style.display = 'block';
}