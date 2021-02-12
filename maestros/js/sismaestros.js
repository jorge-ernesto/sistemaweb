// Solo Números y Letras
function setNumerosLetras(id_input) {
  document.getElementById(id_input).value = document.getElementById(id_input).value.replace(/[^a-zA-Z0-9\-]/g,'');
}


function teclaEnter() {
	if (window.event.keyCode == 13) {	
		window.event.keyCode = 06;
		window.event.returnValue = false;
    	}
} 

function DevuelveCodManual(campo, valor) {
	if(campo.checked==true) {
        	url = 'control.php?rqst=MAESTROS.ITEMS&action=AgregarCod&check=si';
        	document.getElementById('control').src = url;
    	} else {
        	valor.value = '';
    	}
}

function PaginarRegistrosBuscar(rxp, valor, fecha, fecha2){   	
	urlPagina = 'control.php?rqst=MAESTROS.TIPODECAMBIO&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}


function setDevuelveCodManual(campo) {
	txt_campo = document.getElementsByName('codigo')[0];
	txt_campo.value = campo;
	return;
}

function displaybanco(campo, valor) {
	if(campo.checked==true) {
        	valor.style.display = 'block';
    	} else {
        	valor.style.display = 'none';
    	}
}

function displayTipoPersona(campo, activa, inactiva) {
	if(campo.checked==true) {
        	activa.style.display = 'block';
        	inactiva.style.display = 'none'
    	} else {
        	activa.style.display = 'none';
        	inactiva.style.display = 'block';
    	}
}

function confirmarLink(pregunta, accionY, accionN, target) {
	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;
  	else
    		document.getElementById('control').src = accionN;
}

function confirmarForm(pregunta, form) {
	if(confirm(pregunta)) 
    		return true;
  	return false;
}

function checkRuc(valor) {
	Codigo = valor.value;
    	urlValida = 'control.php?rqst=MAESTROS.PROVEEDOR&action=ValidarRuc&task=PROVEEDORDET&CodigoRuc='+Codigo;
    	document.getElementById('control').src = urlValida;
}

function checkCodigo(valor) {
	Codigo = valor.value;
    	urlValida = 'control.php?rqst=MAESTROS.PROVEEDOR&action=ValidarCodigo&task=PROVEEDORDET&Codigo='+Codigo;
    	document.getElementById('control').src = urlValida;
}

function bloquea(valor1,valor2) {
	if(valor1.value != '' || valor1.value > 0) {
      		valor2.disabled=true;
   	} else {
      		valor2.disabled=false;
   	}
}

function PaginarRegistros(rxp, valor) {
	urlPagina = 'control.php?rqst=MAESTROS.PROVEEDOR&task=PROVEEDOR&rxp='+rxp+'&pagina='+valor;
    	document.getElementById('control').src = urlPagina;
}

function getRegistro(campo) {
	url = 'control.php?rqst=MAESTROS.PROVEEDOR&action=setRegistro&task=PROVEEDORDET&codigo='+campo;
  	document.getElementById('control').src = url;
  	return;
}

function setRegistro(campo) {
	txt_campo = document.getElementsByName('datos[pro_ciiu]')[0];
  	txt_campo.value = campo;
  	return;
}

function getRegistroFP(campo) {
	url = 'control.php?rqst=MAESTROS.PROVEEDOR&action=setRegistroFP&task=PROVEEDORDET&codigofp='+campo;
	document.getElementById('control').src = url;
  	return;
}

function setRegistroFP(campo) {
	txt_campo = document.getElementsByName('datos[pro_forma_pago]')[0];
	txt_campo.value = campo;
	return;
}

function getRegistroDist(campo) {
	url = 'control.php?rqst=MAESTROS.PROVEEDOR&action=setRegistroDist&task=PROVEEDORDET&codigodist='+campo;
	document.getElementById('control').src = url;
  	return;
}

function setRegistroDist(campo) {
	txt_campo = document.getElementsByName('datos[pro_distrito]')[0];
  	txt_campo.value = campo;
  	return;
}

function getRegistroRub(campo) {
  url = 'control.php?rqst=MAESTROS.PROVEEDOR&action=setRegistroRub&task=PROVEEDORDET&codigorub='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroRub(campo){
  txt_campo = document.getElementsByName('datos[pro_grupo]')[0];
  txt_campo.value = campo;
  return;
}

function copyOptions(sourceL, targetL){
  for (i=0; i<sourceL.length; i++){
    targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
  }
}

function addBancos() {
	document.getElementById('descrip_codigo').appendChild(document.getElementsByName('interface[dato_bancario][cod_banco][]')[0].cloneNode(true));
	document.getElementById('descrip_codigo').appendChild(document.createElement('br'));
  
	var span = document.getElementById('descrip_cuenta').childNodes[3].cloneNode(true);
  	document.getElementById('descrip_cuenta').appendChild(span);
  	document.getElementById('descrip_cuenta').appendChild(document.createTextNode(' '));
  	document.getElementById('descrip_cuenta').appendChild(document.getElementsByName('interface[dato_bancario][nro_cuenta][]')[0].cloneNode(true));
  	document.getElementById('descrip_cuenta').appendChild(document.createElement('br'));

  	var span2 = document.getElementById('descrip_tip_cta').childNodes[3].cloneNode(true);
  	document.getElementById('descrip_tip_cta').appendChild(span2);
  	document.getElementById('descrip_tip_cta').appendChild(document.createTextNode(' '));
  	document.getElementById('descrip_tip_cta').appendChild(document.getElementsByName('interface[dato_bancario][tipo_cuenta][]')[0].cloneNode(true));
  	document.getElementById('descrip_tip_cta').appendChild(document.createElement('br'));
}

function getRegistroCodCta(campo) {
	url = 'control.php?rqst=MAESTROS.PROVEEDOR&action=setRegistroCodCta&task=PROVEEDORDET&codigocta='+campo;
	document.getElementById('control').src = url;
	return;
}

function setRegistroCodCta(campo,campo_desc) {
	txt_campo = document.getElementsByName('interface[dato_bancario][cod_banco][]')[0];
	txt_campo.value = campo;
  
  txt_campo_desc = document.getElementsByName('interface[dato_bancario][desc_cta][]')[0];
  //txt_campo_desc.readonly = true;
  txt_campo_desc.value = campo_desc;

  return;
}

function getRegistroTipoCtaBan(campo){
  //var campo2 = campo2;
  url = 'control.php?rqst=MAESTROS.PROVEEDOR&action=setRegistroTipoCtaBan&task=PROVEEDORDET&codigotipoctaban='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroTipoCtaBan(campo,campo_desc)
{
  //pendiente----(para pasar los valores)
  txt_campo = document.getElementsByName('interface[dato_bancario][tipo_cuenta][]')[0];
  txt_campo.value = campo;
  
  txt_campo_desc = document.getElementsByName('interface[dato_bancario][desc_tipoctaban][]')[0];
  //txt_campo_desc.readonly = 'block';
  txt_campo_desc.value = campo_desc;

  return;
}

function AgregaCuenta(valor, valor2, valor3,valor4,valor5)
{
    valor = valor.value;
    valor2 = valor2.value;
    valor3 = valor3.value;
    valor4 = valor4.value;
    valor5 = valor5.value;
    //alert(valor2);
    urlValida = 'control.php?rqst=MAESTROS.PROVEEDOR&action=AgregaCuenta&task=PROVEEDORDET&valor='+valor+'&valor2='+valor2+'&valor3='+valor3+'&valor4='+valor4+'&valor5='+valor5;
    document.getElementById('control').src = urlValida;
}

function EliminarCuenta(valor,codigo)
{
    codigo = codigo.value;
    urlValida = 'control.php?rqst=MAESTROS.PROVEEDOR&action=AgregaCuenta&task=PROVEEDORDET&dato_elimina='+valor+'&registro_id='+codigo;
    document.getElementById('control').src = urlValida;
}

function TipoCambioRegresar(fecha,fecha2) {
    control.location.href="control.php?rqst=MAESTROS.TIPODECAMBIO&action=Buscar&fecha=" + fecha + "&fecha2=" + fecha2;
}

function CierreDiaRegresar(fecha,fecha2,campo) {
    control.location.href="control.php?rqst=MAESTROS.DIACIERRE&action=Buscar&fecha=" + fecha + "&fecha2=" + fecha2 + "&campo=" + campo;
}

function Validar_Campos(){

	if(document.getElementsByName('ruc')[0].value == ""){
		alert("Falta ingresar Ruc");
	}
 	if(document.getElementsByName('codanexo')[0].value == ""){
		alert("Falta ingresar Codigo Anexo");
	}

}

function validar(e,tipo){
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9/:]/;
			//alert("Solo numeros");
			break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9.]/;
			//alert("Solo decimales o numeros");
			break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}
	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);
}

function agregar_cierre() {

	if(document.getElementsByName('hora_inicial')[0].value == ""){
		alert("Falta ingresar hora inicia");
	}

 	if(document.getElementsByName('hora_final')[0].value == ""){
		alert("Falta ingresar hora final");
	}

	/*if(document.getElementsByName('hora_inicial')[0].value > document.getElementsByName('hora_final')[0].value){
		alert('La hora inicial debe ser menor a la hora final');
	}*/

	if(document.getElementsByName('hora_inicial')[0].value == document.getElementsByName('hora_final')[0].value){
		alert('La hora inicial no puede ser igual a la hora final');
	}	

}


function Mostrar(){
	document.getElementById('ver').style.display = 'block';
}


