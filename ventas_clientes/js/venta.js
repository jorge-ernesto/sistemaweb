function cargarLista(tipo) {
	control.location.href='control.php?rqst=REPORTES.ESPECIALES&cod=0x&action=' + tipo;
}

function cargarListaBusqueda(tipo) {
	control.location.href='control.php?rqst=REPORTES.ESPECIALES&cod='+ window.document.getElementById("cod_busca").value + '&action=' + tipo;
}

function cargarListaOrdenarCodigo(tipo) {
	control.location.href='control.php?rqst=REPORTES.ESPECIALES&cod=Codigo&action=' + tipo;
}

function cargarListaOrdenarDescripcion(tipo) {
	control.location.href='control.php?rqst=REPORTES.ESPECIALES&cod=Descripcion&action=' + tipo;
}

/* NUEVO */

function cargarListaEspeciales(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASESPECIALES&cod=0x&action=' + tipo;
}

function cargarListaBusqueda(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASESPECIALES&cod='+ window.document.getElementById("cod_busca").value + '&action=' + tipo;
}

function cargarListaOrdenarCodigo(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASESPECIALES&cod=Codigo&action=' + tipo;
}

function cargarListaOrdenarDescripcion(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASESPECIALES&cod=Descripcion&action=' + tipo;
}

/* -------------- */

function cargarLista(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASXPROVEEDOR&cod=0x&action=' + tipo;
}

function cargarListaBusqueda(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASXPROVEEDOR&cod='+ window.document.getElementById("cod_busca").value + '&action=' + tipo;
}

function cargarListaOrdenarCodigo(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASXPROVEEDOR&cod=Codigo&action=' + tipo;
}

function cargarListaOrdenarDescripcion(tipo) {
	control.location.href='control.php?rqst=REPORTES.VENTASXPROVEEDOR&cod=Descripcion&action=' + tipo;
}

function cargarListaSerie() {
	control.location.href='control.php?rqst=REPORTES.VENTASOFICIAL&action=SerieDocumento';
}

function cargarListaSerie2() {
	control.location.href='control.php?rqst=REPORTES.REGISTROVENTAS&action=SerieDocumento';
}

function ocultarObjeto(nombre) {
	var objeto = document.getElementById(nombre);    
	if (objeto) {
		objeto.style.visibility="hidden";
    	}
}

function mostrarObjeto(nombre) {
	var objeto = document.getElementById(nombre);
	if (objeto) {
		objeto.style.visibility="visible";
	}
}

function ocultar_caja(valor) {
	hasta = document.getElementsByName('c_num_hasta')[0];
	hasta.value="";
	if (valor.checked)
		hasta.style.display = "none";
	else
		hasta.style.display = "inline";
}

function validar(e,tipo) {
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9.]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
	}
	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

function manejarTipoConsulta(objeto) {
	var historico = document.getElementById("GRUPO_HISTORICO");

	if (objeto.value == "historico")
	        historico.style.display="inline";
	else
        	historico.style.display="none";
}

function archivoBonus(objeto) {
	var mostrar = document.getElementById("GRUPO_BONUS");

	if (objeto.checked)
		mostrar.style.display="inline";
	else
	        mostrar.style.display="none";
}

function completarCaracteres(objeto, numero, caracter) {
	var longitud = objeto.value.length;
	var valor = objeto.value;
	var i;

	if (longitud < numero) {
		for(i=0; i<(numero-longitud); i++) {
			valor = caracter + valor;
		}
    	}
    	objeto.value = valor;
}

function verificar_reporte() {
	var hoy = document.getElementsByName('hoy')[0].value;
	var desde = document.getElementsByName('desde')[0].value;
	var hasta = document.getElementsByName('hasta')[0].value;
	var impresion = document.getElementsByName('impresion')[0].value;

	if (Comparar_Data(desde,hoy)) {
		if (Comparar_Data(hasta,hoy)) {
			if (Comparar_Data(impresion,hoy)) {
				if (Comparar_Data(desde,hasta)) {
					return true;
				} else {
					alert('Error: La Fecha Desde es mayor a la fecha Hasta');
					return false;
				}
			} else {
				alert('Error: La Fecha de impresion es mayor a la fecha de hoy');
				return false;
			}
		} else {
			alert('Error: La Fecha Hasta es mayor a la fecha de hoy');
			return false;
		}
	} else {
		alert('Error: La Fecha Desde es mayor a la fecha de hoy');
		return false;
	}
}

function Comparar_Data(String1,String2) {
	Data1_arr = String1.split('/')
	Data2_arr = String2.split('/')
	
	String1 = Data1_arr[2] + Data1_arr[1] + Data1_arr[0]
	String2 = Data2_arr[2] + Data2_arr[1] + Data2_arr[0]
	String1 = parseFloat(String1);
	String2 = parseFloat(String2);

	if (String1 <= String2) {
		return true;
	}
	return false;
}

function Mostrar_Descontar(valor) {
	var descontar = document.getElementsByName('descontar')[0];
	if (valor=='N'){
		descontar.style.display='inline';
	} else {
		descontar.style.display='none';
	}
}

