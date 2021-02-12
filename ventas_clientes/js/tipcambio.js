function PaginarRegistrosBuscar(rxp, valor, fecha, fecha2){   	
	urlPagina = 'control.php?rqst=MAESTROS.TIPODECAMBIO&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}
function PaginarRegistros(rxp, valor) {   	
	urlPagina = 'control.php?rqst=MAESTROS.TIPODECAMBIO&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosFecha(rxp, valor, fecha, fecha2) {   	
	urlPagina = 'control.php?rqst=MAESTROS.TIPODECAMBIO&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}

function isNumberKey(evt){
var charCode = (evt.which) ? evt.which : event.keyCode

	if (charCode > 31 && (charCode < 48 || charCode > 57))
	return false;

return true;
}

function validar(e,tipo){

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
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}
	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

function CalcularTotales(){

		cantidad = document.getElementsByName('m_cantidadpedida')[0].value;
		precio = document.getElementsByName('preciouni')[0].value;

		total = (parseFloat(cantidad) * parseFloat(precio));

		document.getElementsByName('m_precio')[0].value = total.toFixed(2);

}

function Buscar(almacen){
	urlPagina = 'control.php?rqst=MAESTROS.SERIEDOCUMENTO&action=Buscar&almacen='+almacen;
	document.getElementById('control').src = urlPagina;
}

function confirmarLink(pregunta, accionY, accionN, target) {  

  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;

}
