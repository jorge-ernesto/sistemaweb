
function PaginarRegistros(rxp, valor) {   	
	urlPagina = 'control.php?rqst=REPORTES.IMPORTARCOMPRA&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosFecha(rxp, valor, fecha, fecha2) {
	urlPagina = 'control.php?rqst=REPORTES.IMPORTARCOMPRA&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}

function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function Mostrar(){
document.getElementById('ver').style.display = 'block';
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


function Procesar(filename, codproveedor, fecha, tipo, serie, numero, codmoneda, fvencimientoday, fvencimiento){

	var i;
	var len;
	var almacen;
	var rubro;
	var contabilizar;
	var fperiodo;
	var base;
	var impuesto;
	var total;
	var perce;
	var correlativo;
	var perce;
	var tipo_formulario;
	var cuentaspagar;

	len 		= document.form.tipo_formulario.length;
	almacen		= document.getElementsByName('almacen')[0].value;
	rubro		= document.getElementsByName('rubro')[0].value;
	contabilizar	= document.getElementsByName('contabilizar')[0].value;
	fperiodo	= document.getElementsByName('fperiodo')[0].value;
	base		= document.getElementsByName('base')[0].value;
	impuesto	= document.getElementsByName('impuesto')[0].value;
	total		= document.getElementsByName('total')[0].value;
	perce		= document.getElementsByName('perce')[0].value;
	correlativo	= document.getElementsByName('correlativo')[0].value;
	fsystem		= document.getElementsByName('fsystem')[0].value;
	cuentaspagar	= document.form.cuentaspagar.checked;

	for (i = 0; i < len; i++) {

		if ( document.form.tipo_formulario[i].checked ) {
			tipo_formulario = document.form.tipo_formulario[i].value;
			break;
		} else {
			tipo_formulario = false;
		}

	}

	if (tipo_formulario == "" || tipo_formulario == false){
		alert('No ha seleccionado un tipo de formulario');
	} else {
		url = 'control.php?rqst=REPORTES.IMPORTARCOMPRA&action=Actualizar&filename='+filename+'&almacen='+almacen+'&tipo_formulario='+tipo_formulario+'&codproveedor='+codproveedor+'&fecha='+fecha+'&tipo='+tipo+'&serie='+serie+'&numero='+numero+'&rubro='+rubro+'&contabilizar='+contabilizar+'&fperiodo='+fperiodo+'&base='+base+'&impuesto='+impuesto+'&total='+total+'&correlativo='+correlativo+'&fsystem='+fsystem+'&perce='+perce+'&codmoneda='+codmoneda+'&cuentaspagar='+cuentaspagar+'&fvencimientoday='+fvencimientoday+'&fvencimiento='+fvencimiento;
  		document.getElementById('control').src = url;
  		return;
	}

}

function MostrarCintillo(formulario,tf,fecha,numcompra){

	url = 'control.php?rqst=REPORTES.IMPORTARCOMPRA&action=Cintillo&formulario='+formulario+'&tf='+tf+'&fecha='+fecha+'&numcompra='+numcompra;
  	document.getElementById('control').src = url;
  	return;

}

function Totales(base){

	var act;
	var act2;
	var impuesto;
	var total;
	var baseact;

	act = ((parseFloat(document.getElementsByName('vali')[0].value)) + parseFloat(document.getElementsByName('limit')[0].value));
	act2 = ((parseFloat(document.getElementsByName('vali')[0].value)) - parseFloat(document.getElementsByName('limit')[0].value));

	if(base > act){

		alert('Solo puedes aumentar 0.02 a la base imponible');

		baseact = parseFloat(document.getElementsByName('vali')[0].value);
		impuestoact = (parseFloat(baseact) * 0.18);
		totalact = (parseFloat(baseact) + parseFloat(impuestoact));

		document.getElementsByName('base')[0].value = baseact.toFixed(2);
		document.getElementsByName('impuesto')[0].value = impuesto.toFixed(2);
		document.getElementsByName('total')[0].value = total.toFixed(2);

	}else if(base < act2){

		alert('Solo puedes quitar 0.02 a la base imponible');

		baseact = parseFloat(document.getElementsByName('vali')[0].value);
		impuestoact = (parseFloat(baseact) * 0.18);
		totalact = (parseFloat(baseact) + parseFloat(impuestoact));

		document.getElementsByName('base')[0].value = baseact.toFixed(2);
		document.getElementsByName('impuesto')[0].value = impuesto.toFixed(2);
		document.getElementsByName('total')[0].value = total.toFixed(2);

	}else{

		impuesto = (base * 0.18);
		total = (parseFloat(base) + parseFloat(impuesto));

		document.getElementsByName('base')[0].value = base.toFixed(2);
		document.getElementsByName('impuesto')[0].value = impuesto.toFixed(2);
		document.getElementsByName('total')[0].value = total.toFixed(2);
	}

}

function CalcularTotales(base){

	var impuesto;
	var total;
	
	impuesto = (parseFloat(base) * 0.18);
	total = (parseFloat(base) + parseFloat(impuesto));

	document.getElementsByName('base')[0].value = base;
	document.getElementsByName('impuesto')[0].value = impuesto.toFixed(2);
	document.getElementsByName('total')[0].value = total.toFixed(2);

}

function CuentasPagar(){

	var esto;

	esto='RegistroCompra';

	vista = document.getElementById(esto).style.display;

	if (vista=='none'){
		vista='block';
		dis='';
		fila=document.getElementById("celda");
		fila1=document.getElementById("celda1");
		fila2=document.getElementById("celda2");
		fila3=document.getElementById("celda3");
		fila5=document.getElementById("celda5");
		fila6=document.getElementById("celda6");
		fila7=document.getElementById("celda7");
		fila8=document.getElementById("celda8");
		fila.style.display=dis;
		fila1.style.display=dis;
		fila2.style.display=dis;
		fila3.style.display=dis;
		fila5.style.display=dis;
		fila6.style.display=dis;
		fila7.style.display=dis;
		fila8.style.display=dis;
	}else{
		vista='none';
		fila=document.getElementById("celda");
		fila1=document.getElementById("celda1");
		fila2=document.getElementById("celda2");
		fila3=document.getElementById("celda3");
		fila5=document.getElementById("celda5");
		fila6=document.getElementById("celda6");
		fila7=document.getElementById("celda7");
		fila8=document.getElementById("celda8");
		fila.style.display=vista;
		fila1.style.display=vista;
		fila2.style.display=vista;
		fila3.style.display=vista;
		fila5.style.display=vista;
		fila6.style.display=vista;
		fila7.style.display=vista;
		fila8.style.display=vista;
	}

	document.getElementById(esto).style.display = vista;

}

function Contabilizar(valor){

	if(valor=="N"){
		dis='none';
		fila=document.getElementById("celda4");
		fila.style.display=dis;
	}else if(valor=="S"){
		dis='';
		fila=document.getElementById("celda4");
		fila.style.display=dis;
	}

}


