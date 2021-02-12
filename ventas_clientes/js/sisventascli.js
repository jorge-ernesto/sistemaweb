/*

  Funciones JavaScript 
  Sistema 
  @TBCA Modificado por @MATT

*/

function PaginarRegistrosTarjetasMagneticas(rxp, valor){
	codigo = document.getElementsByName('busqueda[codigo]')[0].value;
  	urlPagina = 'control.php?rqst=MAESTROS.TARJMAG&task=TARJMAG&action=Buscar&rxp='+rxp+'&pagina='+valor+'&pagiBusqueda='+codigo;
   	document.getElementById('control').src = urlPagina;
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

function checkNuevaTarjeta(valor){
	NroTarj = valor.value;
	urlValidaNroTarj = 'control.php?rqst=MAESTROS.TARJMAG&action=ValidarNroTar&task=TARJMAGDET&NroTarj='+NroTarj;
	document.getElementById('control').src = urlValidaNroTarj;
}

function checkNuevaPlaca(valor){
	Placa = valor.value;
	urlValidaPlaca = 'control.php?rqst=MAESTROS.TARJMAG&action=ValidarPlaca&task=TARJMAGDET&Placa='+Placa;
	document.getElementById('control').src = urlValidaPlaca;
}

function regresar(){
	url = 'control.php?rqst=MAESTROS.TARJMAG&task=TARJMAG';
    document.getElementById('control').src = url;
    return;
}

function bloquea(valor1,valor2){
   var periodo = document.getElementsByName('tarjeta[ch_tipo_periodo_acumular]')[0];
   var dia = document.getElementsByName('tarjeta[ch_dia_de_corte]')[0];
   	
   if(valor1.value != '' && valor1.value > 0){
   	  valor2.value='';
      valor2.disabled=true;
      periodo.style.display='inline';
      dia.style.display='inline';
   }else{
      valor2.disabled=false;
      periodo.style.display='none';
      dia.style.display='none';
   }
}

function getRegistro(campo, buscar_todos = 0){  
  url = `control.php?rqst=MAESTROS.TARJMAG&action=setRegistro&task=TARJMAGDET&codigo=${campo}&buscar_todos=${buscar_todos}`;
  console.log(campo);
  console.log(url);
  document.getElementById('control').src = url;
  return;
}

function validar_registro_tarjetas(){
	txtcodigo = document.getElementsByName('tarjeta[codcli]')[0];
	txtcuenta = document.getElementsByName('tarjeta[codcue]')[0];
	txttarjeta = document.getElementsByName('tarjeta[numtar]')[0];
	txtusuario = document.getElementsByName('tarjeta[nomusu]')[0];
	txtplaca = document.getElementsByName('tarjeta[numpla]')[0];
	txtvence = document.getElementsByName('tarjeta[ventar]')[0];
	txtbloqueada = document.getElementsByName('tarjeta[estblo]')[0];
	txtsegres = document.getElementsByName('tarjeta[segres]')[0];
	importe = document.getElementsByName('tarjeta[nu_limite_importe]')[0];
	galones = document.getElementsByName('tarjeta[nu_limite_galones]')[0];
		
	if (txtcodigo.value==''){
		alert('Ingrese Codigo del Cliente');
		return false;
	}
	if (txtcuenta.value==''){
		alert('Ingrese Codigo de Cuenta');
		return false;
	}
	if (txttarjeta.value==''){
		alert('Ingrese Numero de Tarjeta');
		return false;
	}
	if (txtusuario.value==''){
		alert('Ingrese el nombre del usuario');
		return false;
	}
	if (txtplaca.value==''){
		alert('Ingrese el numero de placa');
		return false;
	}
	
	if (confirm('Desea Grabar/Actualizar el registro?')) {
		txtcodigo.disabled=false;
		return true;
	}
	else return false;
}

function ocultar_market(valor){
	var galon = document.getElementsByName('tarjeta[nu_limite_galones]')[0];
	var importe = document.getElementsByName('tarjeta[nu_limite_importe]')[0];
	var periodo = document.getElementsByName('tarjeta[ch_tipo_periodo_acumular]')[0];
	var dia = document.getElementsByName('tarjeta[ch_dia_de_corte]')[0];
	if (valor=='M'){
		galon.style.display='none';
	}
	else{
		galon.style.display='inline';
	}
	
	galon.value='';	
	importe.value='';
	periodo.style.display='none';
	dia.style.display='none';
	//return ;
}

function limpiar_cajas(){
	var codigo = document.getElementsByName('tarjeta[codcli]')[0];
	var cuenta = document.getElementsByName('tarjeta[codcue]')[0];
	var tarjeta = document.getElementsByName('tarjeta[numtar]')[0];
	var usuario = document.getElementsByName('tarjeta[nomusu]')[0];
	var placa = document.getElementsByName('tarjeta[numpla]')[0];
	var vencimiento = document.getElementsByName('tarjeta[ventar]')[0];
	var bloqueado = document.getElementsByName('tarjeta[estblo]')[0];
	codigo.value='';
	cuenta.value='';
	tarjeta.value='';
	usuario.value='';
	placa.value='';
	vencimiento.value='';
	//bloqueado.value='';
	return ;
}



function setRegistro(campo,tarjeta,grupo){
  txt_campo = document.getElementsByName('tarjeta[codcli]')[0];
  txtcuenta = document.getElementsByName('tarjeta[codcue]')[0];
  txtventar = document.getElementsByName('tarjeta[ventar]')[0];
  txttarjeta = document.getElementsByName('tarjeta[numtar]')[0];
  txtusuario = document.getElementsByName('tarjeta[nomusu]')[0];
  txtplaca = document.getElementsByName('tarjeta[numpla]')[0];
  auxilio = document.getElementsByName('auxilio')[0];
  cbtipo = document.getElementsByName('tarjeta[ch_tipo_producto]')[0];
  txtlimitegalones = document.getElementsByName('tarjeta[nu_limite_galones]')[0];
  txtlimiteimportes = document.getElementsByName('tarjeta[nu_limite_importe]')[0];
  txtperiodo = document.getElementsByName('tarjeta[ch_tipo_periodo_acumular]')[0];
  txtdia = document.getElementsByName('tarjeta[ch_dia_de_corte]')[0];
  lblcuenta = document.getElementById('MensajeValidacion');
  var gr = document.getElementsByName('grupo')[0];
  var vales = document.getElementsByName('vales')[0];
  gr.value=grupo;
  vales.checked=false;
  
  if (tarjeta=='NO_GRUPO'){
  	txttarjeta.value='';
  	lblcuenta.innerHTML='El cliente no tiene grupo asignado';
  }
  else  {
  	if (tarjeta!='NO_EXISTE'){
  		if (tarjeta=='EXCEDIO_LIMITE'){
  			txttarjeta.value = '';
  			auxilio.value = '';
  			lblcuenta.innerHTML='Se excedio el limite de tarjetas del grupo';
  		}else{
  			txttarjeta.value = tarjeta;
  			auxilio.value = tarjeta;
  			lblcuenta.innerHTML='';
  		}
   	}
  }
  	
  if (tarjeta=='NO_EXISTE'){
  	txtcuenta.value='';
  	txtventar.value='';
  	txttarjeta.value='';
  	cbtipo.value="";
  	txtlimitegalones.value='';
    txtlimiteimportes.value='';
    txtplaca.value="";
    txtlimiteimportes.disabled=false;
    txtlimitegalones.disabled=false;
    txtusuario.value="";
    txtperiodo.style.display='none';
    txtdia.style.display='none';
    //vales.style.display='none'
  	txt_campo.focus();
  }  	
  else{
  	txtcuenta.value=campo;
  	txtventar.value='mm/aa';
  	//vales.style.display='none'
  	txt_campo.value = campo;
  	txtusuario.focus();
  }
  
  cbtipo.value="";
  txtlimitegalones.value='';
  txtlimiteimportes.value='';
  txtlimiteimportes.disabled=false;
  txtlimitegalones.disabled=false;
  txtusuario.value="";
  txtperiodo.style.display='none';
  txtdia.style.display='none';
  txtplaca.value="";
  //txt_campo.value = campo;
  
  
  return;
}

function cambiar_vales(){
	var vales = document.getElementsByName('vales')[0];
	var grupo = document.getElementsByName('grupo')[0];
	var tarjeta = document.getElementsByName('tarjeta[numtar]')[0];
	var auxilio = document.getElementsByName('auxilio')[0];
	var usuario = document.getElementsByName('tarjeta[nomusu]')[0];
	var placa = document.getElementsByName('tarjeta[numpla]')[0];
	var txtlimitegalones = document.getElementsByName('tarjeta[nu_limite_galones]')[0];
    var txtlimiteimportes = document.getElementsByName('tarjeta[nu_limite_importe]')[0];
    var txtperiodo = document.getElementsByName('tarjeta[ch_tipo_periodo_acumular]')[0];
    var txtdia = document.getElementsByName('tarjeta[ch_dia_de_corte]')[0];
    txtlimitegalones.value='';
	txtlimiteimportes.value='';
	txtlimitegalones.disabled=false;
	txtlimiteimportes.disabled=false;
	txtperiodo.style.display='none';
	txtdia.style.display='none';
	//alert(grupo.value);
	if (vales.checked){
		tarjeta.value = '7055'+grupo.value+'000';
		usuario.value = 'VALES';
		placa.value = 'VALES';
		txtlimitegalones.style.display='none';
		txtlimiteimportes.style.display='none';
		
	}else{
		tarjeta.value = auxilio.value;
		usuario.value = '';
		placa.value='';
		txtlimitegalones.style.display='inline';
		txtlimiteimportes.style.display='inline';
		
	}
}

function copyOptions(sourceL, targetL){
  for (i=0; i<sourceL.length; i++){
    targetL[i] = new Option(sourceL[i].text, sourceL[i].value);
  }
}

function cambiarDias(selec){
	 var selec = selec.options;
	 //var combo = document.formular.new_nro_dia.options;
	 //var combo = document.getElementById('ch_dia_de_corte').options;
	 var combo = document.getElementById('tarjeta[ch_dia_de_corte]').options;
	 combo.length = null;

	 document.getElementById('tarjeta[ch_dia_de_corte]').style.display = 'none';
	 document.getElementById('span-sNameDays').style.display = 'none';

	 if (selec[1].selected == true){
	 	document.getElementById('tarjeta[ch_dia_de_corte]').style.display = 'block';
	 	document.getElementById('span-sNameDays').style.display = 'block';

		combo[0] = new Option("Domingo", 0,"",""); 
		combo[1] = new Option("Lunes", 1,"",""); 
		combo[2] = new Option("Martes", 2,"",""); 
		combo[3] = new Option("Miercoles", 3,"",""); 
		combo[4] = new Option("Jueves", 4,"",""); 
		combo[5] = new Option("Viernes", 5,"",""); 
		combo[6] = new Option("Sabado", 6,"",""); 
	 }

	 /*
	 if (selec[1].selected == true){
    	var i=1;
		var texto;	    
			while(i<28)	{
	       		 
				 texto = '0' + i.toString();
				 texto = texto.substring(texto.length - 2);
				 
				//  alert(texto);
				 
	       		combo[i-1] = new Option(texto,texto,"","");
	       		i++;
	    	}
	 }
	 */
}

function volver_atras(){
	urlPagina = 'control.php?rqst=MAESTROS.TARJMAG&task=TARJMAG';
    document.getElementById('control').src = urlPagina;
}
