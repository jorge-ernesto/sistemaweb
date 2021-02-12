function validarSiNumero(numero){

	if (!/^([0-9])*$/.test(numero))
	alert("El valor " + numero + " no es un número");

}

// Solo Números y Letras
function setNumerosLetras(id_input) {
  document.getElementById(id_input).value = document.getElementById(id_input).value.replace(/[^a-zA-Z0-9\-]/g,'');
}

function displaybanco(campo, valor) {
	if(campo.checked==true) {
	        valor.style.display = 'block';
	}else{
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

function confirmarForm(pregunta, form)
{
  if(confirm(pregunta)) 
    return true;
  return false;
}

function checkRuc(valor)
{
  Codigo = valor.value;
    urlValida = 'control.php?rqst=MAESTROS.CLIENTE&action=ValidarRuc&task=CLIENTEDET&CodigoRuc='+Codigo;
    document.getElementById('control').src = urlValida;
}

function checkCodigo(valor)
{
  Codigo = valor.value;
    urlValida = 'control.php?rqst=MAESTROS.CLIENTE&action=ValidarCodigo&task=CLIENTEDET&Codigo='+Codigo;
    document.getElementById('control').src = urlValida;
}

function checkCodigoShell(valor)
{
    Codigo = valor.value;
    regid = document.getElementsByName('registroid')[0].value;
    urlValida = 'control.php?rqst=MAESTROS.CLIENTE&action=ValidarCodigoShell&task=CLIENTEDET&CodigoShell='+Codigo+'&registroid='+regid;
    document.getElementById('control').src = urlValida;
}

function bloquea(valor1,valor2)
{
   //valor2 = document.getElementById('nu_limite_importe');
   if(valor1.value != '' || valor1.value > 0)
   {
      valor2.disabled=true;
   }else{
      valor2.disabled=false;
   }
}

function PaginarRegistrosCli(rxp, valor){
  urlPagina = 'control.php?rqst=MAESTROS.CLIENTE&task=CLIENTE&rxp='+rxp+'&pagina='+valor;
  document.getElementById('control').src = urlPagina;
}

function PaginarRegistrosPost(rxp, valor)
{
   send = document.getElementsByName('task')[0].value;
   rqst = document.getElementsByName('rqst')[0].value;
   
   //Asignando valores a los hidden
   regxpag = document.getElementsByName('rxp')[0];
   pagina = document.getElementsByName('pagina')[0];
   
   pagina.value=valor;
   regxpag.value=rxp;
   document.forms[0].submit();
}

function getRegistro(campo){
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistro&task=CLIENTEDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistro(campo){
  txt_campo = document.getElementsByName('datos[cli_ciiu]')[0];
  txt_campo.value = campo;
  return;
}

function setRegistroDesc(campo){
  txt_campo = document.getElementsByName('datos[cli_descuento]')[0];
  txt_campo.value = campo;
  return;
}

function getRegistroDesc(campo){
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistroDesc&task=CLIENTEDET&codigo='+campo;
  document.getElementById('control').src = url;
  return;
}

function getRegistroFP(campo){
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistroFP&task=CLIENTEDET&codigofp='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroFP(campo){
  txt_campo = document.getElementsByName('datos[cli_fpago_credito]')[0];
  txt_campo.value = campo;
  return;
}

function getRegistroLPRE(campo){
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistroLPRE&task=CLIENTEDET&codigolpre='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroLPRE(campo){
  txt_campo = document.getElementsByName('datos[cli_lista_precio]')[0];
  txt_campo.value = campo;
  return;
}

function getRegistroDistrito(valor){
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=CLIENTEDISTRITO&task=LISTARDISTRITO&distrito='+valor;
  document.getElementById('control').src = url;
  return;
}

function setRegistroDistrito(valor){
  txt_campo = document.getElementsByName('form[cli_distrito]')[0];
  txt_campo.value = valor;
  return;
}

function reporte_clientes() {
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=REPORTE&task=SINTAREA';
  document.getElementById('control').src = url;
  return;
}

function volver_a_buscar_maestro_clientes(){
	url = 'control.php?rqst=MAESTROS.CLIENTE';
	document.getElementById('control').src = url;
	return;
}

function getRegistroDist(campo){
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistroDist&task=CLIENTEDET&codigodist='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroDist(campo){
  txt_campo = document.getElementsByName('datos[cli_distrito]')[0];
  txt_campo.value = campo;
  return;
}

function getRegistroRub(campo){
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistroRub&task=CLIENTEDET&codigorub='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroRub(campo){
  txt_campo = document.getElementsByName('datos[cli_grupo]')[0];
  txt_campo.value = campo;
  return;
}

function copyOptions(sourceL, targetL){
  for (i=0; i<sourceL.length; i++){
    targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
  }
}

function addBancos()
{
  //agregar 
  document.getElementById('descrip_codigo').appendChild(document.getElementsByName('interface[dato_bancario][cod_banco][]')[0].cloneNode(true));
  //document.getElementById('descrip_codigo').appendChild(document.getElementById('desc_cta[]')[0].cloneNode(true));
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

function getRegistroCodCta(campo){
  //var campo2 = campo2;
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistroCodCta&task=CLIENTEDET&codigocta='+campo;
  document.getElementById('control').src = url;
  return;
}

function setRegistroCodCta(campo,campo_desc)
{
  txt_campo = document.getElementsByName('interface[dato_bancario][cod_banco][]')[0];
  txt_campo.value = campo;
  txt_campo_desc = document.getElementsByName('interface[dato_bancario][desc_cta][]')[0];
  txt_campo_desc.value = campo_desc;
  return;
}

function getRegistroTipoCtaBan(campo){
  //var campo2 = campo2;
  url = 'control.php?rqst=MAESTROS.CLIENTE&action=setRegistroTipoCtaBan&task=CLIENTEDET&codigotipoctaban='+campo;
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
    urlValida = 'control.php?rqst=MAESTROS.CLIENTE&action=AgregaCuenta&task=CLIENTEDET&valor='+valor+'&valor2='+valor2+'&valor3='+valor3+'&valor4='+valor4+'&valor5='+valor5;
    document.getElementById('control').src = urlValida;
}

function EliminarCuenta(valor,codigo)
{
    codigo = codigo.value;
    urlValida = 'control.php?rqst=MAESTROS.CLIENTE&action=AgregaCuenta&task=CLIENTEDET&dato_elimina='+valor+'&registro_id='+codigo;
    document.getElementById('control').src = urlValida;
}

function PaginarRegistros(rxp, valor, ch_tipo_consulta, tm0, tm1, tm2, td0, td1, td2, Bonus, ch_almacen, ch_lado, ch_caja, ch_turno, ch_periodo, ch_mes, ch_dia_desde, ch_dia_hasta, art_codigo, ruc, cuenta, tarjeta, ch_tipo){   	
	urlPagina = 'control.php?rqst=FACTURACION.TICKETSPOS&action=Buscar&rxp='+rxp+'&pagina='+valor+'&ch_tipo_consulta='+ch_tipo_consulta+'&tm[0]='+tm0+'&tm[1]='+tm1+'&tm[2]='+tm2+'&td[0]='+td0+'&td[1]='+td1+'&td[2]='+td2+'&Bonus='+Bonus+'&ch_almacen='+ch_almacen+'&ch_lado='+ch_lado+'&ch_caja='+ch_caja+'&ch_turno='+ch_turno+'&ch_periodo='+ch_periodo+'&ch_mes='+ch_mes+'&ch_dia_desde='+ch_dia_desde+'&ch_dia_hasta='+ch_dia_hasta+'&art_codigo='+art_codigo+'&ruc='+ruc+'&tarjeta='+tarjeta+'&ch_tipo='+ch_tipo;
	document.getElementById('control').src = urlPagina;	
}

/*function PaginarRegistros(rxp, valor) {   	
	urlPagina = 'control.php?rqst=MAESTROS.TIPODECAMBIO&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;	
}

function PaginarRegistrosFecha(rxp, valor, fecha, fecha2) {   	
	urlPagina = 'control.php?rqst=MAESTROS.TIPODECAMBIO&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2;
	document.getElementById('control').src = urlPagina;	
}

function regresar() {
	url = 'control.php?rqst=MAESTROS.TIPODECAMBIO';
    	document.getElementById('control').src = url;
    	return;
}*/

function confirmarLink(pregunta, accionY, accionN, target) {  
  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;  
}

function validar(e,tipo){
	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9.]/;break;
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

function validar_form_clientes(){
	var codigo = document.getElementsByName('datos[cli_codigo]')[0];
	var razsocial = document.getElementsByName('datos[cli_razsocial]')[0];
	var razsocbreve = document.getElementsByName('datos[cli_rsocialbreve]')[0];
	//var ruc = document.getElementsByName('datos[cli_ruc]')[0];
	var direccion = document.getElementsByName('datos[cli_direccion]')[0];
	var distrito = document.getElementsByName('datos[cli_distrito]')[0];
	var fpago = document.getElementsByName('datos[cli_fpago_credito]')[0];
	var lprecios = document.getElementsByName('datos[cli_lista_precio]')[0];
	
	if (codigo.value == '' || razsocial.value == '' || razsocbreve.value=='' || direccion.value == '' || distrito.value=='' || fpago.value=='' || lprecios.value==''){
		alert('Falta llenar un/unos campo(s) obligatorio(s)');
		return false;
	}else{
		if (confirm('Desea agregar/modificar el registro nuevo?'))
			return true;
		else
			return false;
	}
}

function volver_a_maestro_clientes(){
	 paginacion = document.getElementsByName('paginacion[codigo]')[0];
  	campo = paginacion.value;
  	url = 'control.php?rqst=MAESTROS.CLIENTE&task=CLIENTE&action=Regresar&paginacion[codigo]='+campo;
  	document.getElementById('control').src = url;
  	return;
}
/*
function TipoCambioRegresar(fecha,fecha2) {
    control.location.href="control.php?rqst=MAESTROS.TIPODECAMBIO&action=Buscar&fecha=" + fecha + "&fecha2=" + fecha2;
}*/

function cceros(v_lon) {

	var v_var = document.getElementsByName('datos[cli_grupo]')[0];

	var v_var2= v_var.value.replace(/^\s*|\s*$/g,"");
	var lon1  = v_var.value.length;
	var lon2  = v_lon-lon1
	
	for(i=0;i<lon2;i++) {
		v_var2='0'+v_var2;
	}

	eval("document.getElementsByName('datos[cli_grupo]')[0].value =v_var2");

}


