/*
  Funciones JavaScript 
  Sistema 
  @DPC Modificado por @FAP
*/

function autocompleteBridge(type) {
  if (type == 0) {
    //new tipo 0
    var No_Producto = $("#txt-No_Producto");
    if(No_Producto.val() !== undefined){
      autocompleteProducto(No_Producto);
    }
  } else if (type == 2) {
    //buscar tipo 2
    var No_Proveedor = $("#txt-No_Proveedor");
    if(No_Proveedor.val() !== undefined){
      autocompleteProveedor(No_Proveedor);
    }

  } else if (type == 3) {
    var prod = $("#descripcion");
    if (prod !== undefined){
      generalAutocomplete('#descripcion', '#codigo', 'getProductXByCodeOrName', []);
    }
  } else if (type == 4) {
    var No_Proveedor_Transportista = $("#txt-No_Transportista_Proveedor");
    if(No_Proveedor_Transportista.val() !== undefined){
      autocompleteTransportistaProveedor(No_Proveedor_Transportista);
    }
  }
}

function validar(e,tipo){

  tecla=(document.all)?e.keyCode:e.which;

  if (tecla==13 || tecla==8 || tecla== 0)
    return true;

  switch(tipo){
    /*letras y numeros, puntos */
    case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
    /*solo numeros enteros */
    case 2: patron=/[0-9]/;break;
    /*solo numeros dobles*/
    case 3: patron=/[0-9./]/;break;
    /*solo letras*/
    case 4: patron=/[A-Z a-z]/;break;
    /*telefonos y faxes*/
    case 5: patron=/[0-9/-]/;break;
  }

  teclafinal=String.fromCharCode(tecla);
  return patron.test(teclafinal);
}

function autocompleteSerieCompraCeros(){
    var Nu_Serie_Compra = null;
    Nu_Serie_Compra = $( "#serie" ).val();
    $( "#serie" ).val(('0000' + Nu_Serie_Compra).slice(-4));
}

function autocompleteNumeroCompraCeros(){
    var Nu_Numero_Compra = null;
    Nu_Numero_Compra = $( "#numero" ).val();
    $( "#numero" ).val(('00000000' + Nu_Numero_Compra).slice(-8));
}

function salirInventario(formOrdenCompra, arrOrdenCompra){
  $( "#dialog" ).dialog( "close" );
}

function Marcar_Desmacar_Todo(checked){
  if ($( "#check-todo" ).prop("checked")) {
    $( ".check-hijo" ).prop('checked', true);
  } else {
    if (false == $( "#check-todo" ).prop("checked")) { //if this item is unchecked
      $( ".check-hijo" ).prop('checked', false); //change "select all" checked status to false
    }
  }
}

function generarInventario(formOrdenCompra, arrOrdenCompra){
  _arrOrdenCompra = new Array();

  if(formOrdenCompra[arrOrdenCompra].length > 0){
    for (var i = 0, total = formOrdenCompra[arrOrdenCompra].length; i < total; i++){
      if (formOrdenCompra[arrOrdenCompra][i].checked){
        _arrOrdenCompra[_arrOrdenCompra.length] = formOrdenCompra[arrOrdenCompra][i].value;
      }
    }
  } else {
    if (formOrdenCompra[arrOrdenCompra].checked) {
      _arrOrdenCompra[0] = formOrdenCompra[arrOrdenCompra].value;
    }
  }

  if (_arrOrdenCompra.length === 0) {
    alert('Seleccionar orden compra');
  } else {
    $( "#dialog" ).dialog( "open" );
    $( "#txt-arrOrdenCompra" ).val(_arrOrdenCompra);
    /* Valores para BUSCAR */
    $( "#txt-almacen" ).val($( "#txt-iAlmacen" ).val());
    $( "#txt-fechaInicio" ).val($( "#txt-dInicio" ).val());
    $( "#txt-fechaFinal" ).val($( "#txt-dFinal" ).val());
  }
}

function saveInventario(){
  /* Valores para BUSCAR */
  var iAlmacen = $( "#txt-almacen" ).val();
  var dFechaInicio = $( "#txt-fechaInicio" ).val();
  var dFechaFinal = $( "#txt-fechaFinal" ).val();

  var arrOrdenCompra = $( "#txt-arrOrdenCompra" ).val();
  var iTipoInventario = $( "#cbo-tipo-inventario" ).val();
  var iTipoDocumento = $( "#cbo-tipo-documento" ).val();
  var sSerie = $( "#serie" ).val();
  var iNumero = $( "#numero" ).val();
  var dFechaEmision = $( "#txt-dFechaEmision" ).val();

  if (sSerie.length === 0) {
    alert('Ingresar serie');
  } else if (iNumero.length === 0) {
    alert('Ingresar número');
  } else {
    var url = "control.php?rqst=MOVIMIENTOS.ORDENCOMPRA&action=GENERAR_INVENTARIO&arrOrdenCompra=" + arrOrdenCompra + "&iTipoInventario=" + iTipoInventario + "&iTipoDocumento=" + iTipoDocumento + "&sSerie=" + sSerie + "&iNumero=" + iNumero + "&dFechaEmision=" + dFechaEmision + "&iAlmacenB=" + iAlmacen + "&dFechaInicioB=" + dFechaInicio + "&dFechaFinalB=" + dFechaFinal;
    control.location.href = url;
    $( "#serie" ).val('');
    $( "#numero" ).val('');
    $( "#dialog" ).dialog( "close" );
  }
}

function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else{
    //document.getElementById('control').src = accionN;
   document.forms[0].action= "control.php?rqst=MOVIMIENTOS.ORDENCOMPRA&task=ORDENCOMPRA";
   }
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}


function regresar(){
  url = 'control.php?rqst=MOVIMIENTOS.ORDENCOMPRA';
  document.getElementById('control').src = url;
  /*Recargamos desde caché*/
  location.reload();
  /*Forzamos la recarga*/
  location.reload(true);
  return;
}



function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=MOVIMIENTOS.ORDENCOMPRA&task=ORDENCOMPRA&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function validar_registro_productocanje(){
	try{
	txtCodArticulo = document.getElementsByName('itemarticulo')[0];
	txtDescItem = document.getElementsByName('itemdescripcion')[0];
	txtFechaVen = document.getElementsByName('itemfechaven')[0];
	txtAccion = document.getElementsByName('accion')[0];
	
	
	if (txtCodArticulo.value==''){
		alert('¡Ingrese un Artículo!');
		return false;
	}	
	else if(txtDescItem.value==''){
		alert('¡Ingrese Descripción de Producto!');
		return false;
	}
	else if(txtFechaVen.value!=''){
		if(txtAccion.value=='actualizaritem'){	
			
			txtFechaCrea = document.getElementsByName('itemfechacrea')[0];
			
			var comparacion =compara(txtFechaCrea.value,txtFechaVen.value);
			if(comparacion=='0'){
				alert('¡Fecha de Vencimiento no puede ser menor a Fecha de Creación!');
			return false;	
			}
		}
		else{
			txtFecServer = document.getElementsByName('fecServer')[0];
			var comparacion =compara(txtFecServer.value,txtFechaVen.value);
			if(comparacion=='0'){
				alert('¡Fecha de Vencimiento no puede ser menor a la Fecha Actual!');
			return false;	
			}
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

function soloNumeros(evento)
{
  // Algunos caracteres: backspace = 8, enter = 13, '0' = 48, '9' = 57
  var nav4 = window.Event ? true : false;
  var key = nav4 ? evento.which : evento.keyCode;
  return (key <= 13 || (key >= 48 && key <= 57));
}

function mostrarAyuda(url,cod,des,consulta){

url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=280,scrollbars=yes,menubar=no,left=390,top=20');
}

function mostrarAyuda2(url,cod,des,uni,consulta){

url = url+"?cod="+cod+"&des="+des+"&uni="+uni+"&consulta="+consulta;
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

function volver_atras(){
	urlPagina = 'control.php?rqst=MOVIMIENTOS.ORDENCOMPRA&task=ORDENCOMPRA';
    document.getElementById('control').src = urlPagina;
}

function activaGlosa(campo, glosa, glosatext){
  if(campo.checked == true){
    glosa.style.display = 'block';
    glosatext.disabled = false;         
  }else{
    glosa.style.display = 'none';
    document.getElementById("glosa").value = '';
    glosatext.disabled = true;   
  }
}

function activaFlete(campo, flete, fletetext){
  if(campo.checked == true){
    flete.style.display = 'block';
    fletetext.disabled = false;         
  }else{
    flete.style.display = 'none';
    document.getElementById("flete").value = '';
    fletetext.disabled = true;   
  }
}

/*function activaPedido(campo, pedido, pedidotext){
  if(campo.checked == true){
    pedido.style.display = 'block';
    pedidotext.disabled = false;         
  }else{
    pedido.style.display = 'none';
    document.getElementById("pedido").value = '';
    pedidotext.disabled = true;   
  }
}*/

function activaPer(campo, glosa, glosatext)
{



    if(campo.checked == true)
    {
        glosa.style.display = 'block';
        document.getElementById("percepcion").value = '1';     
      	
        
    }else{
        glosa.style.display = 'none';
        document.getElementById("percepcion").value = '';
        
   }
}

function activaGlosa2(campo, glosa, glosatext)
{



    if(campo.checked == true)
    {
        
		glosa.style.display = 'block';        
        	document.getElementById("xpercepcion").value='1';
        	perx=(document.getElementById("xpercepcion").value/100)+1;
    		val_per=(document.getElementById("totalx").value * perx); //-document.getElementById("totalx").value
    		document.getElementById("valor_percep").value = val_per.toFixed(2);
    		val_per_i=(document.getElementById("totalx").value * perx)-document.getElementById("totalx").value;
    		document.getElementById("valor_percep_i").value = val_per_i.toFixed(2);   	
        	       
        
    }else{
    
    		glosa.style.display = 'none';      
        	document.getElementById("xpercepcion").value='';
        	document.getElementById("valor_percep").value ='0.00';
        	document.getElementById("valor_percep_i").value ='0.00';
       
        
   }
}



function Subtotal()
{
    if(document.getElementById("cantidad").value == '' || document.getElementById("precio").value == '')
    {
       document.getElementById("subtotal").value = '0.00';
    }else{
	document.getElementById("cantidad").value = (parseFloat(document.getElementById("cantidad").value).toFixed(2)).toString().split(". ");
	document.getElementById("precio").value = (parseFloat(document.getElementById("precio").value).toFixed(4)).toString().split(". ");
	if(document.getElementById("descuento").value == '')
        {
		document.getElementById("subtotal").value = (parseFloat(document.getElementById("cantidad").value * document.getElementById("precio").value).toFixed(2)).toString().split(". ");
	}
	else
	{
		document.getElementById("descuento").value = (parseFloat(document.getElementById("descuento").value).toFixed(2)).toString().split(". ");
		document.getElementById("subtotal").value = (parseFloat((document.getElementById("cantidad").value * document.getElementById("precio").value) - document.getElementById("descuento").value).toFixed(2)).toString().split(". ")
	}
    }
}

function percepcionx()
{

    if(document.getElementById("xpercepcion").value == '' || document.getElementById("xpercepcion").value == '0')
    {
       document.getElementById("valor_percep").value = '0.00';
       document.getElementById("valor_percep_i").value ='0.00';
    }else{
    	perx=(document.getElementById("xpercepcion").value/100)+1;
    	val_per=(document.getElementById("totalx").value * perx); //-document.getElementById("totalx").value
    	document.getElementById("valor_percep").value = val_per.toFixed(2);
    	val_per_i=(document.getElementById("totalx").value * perx)-document.getElementById("totalx").value;
    	document.getElementById("valor_percep_i").value = val_per_i.toFixed(2);
	
    }
}


function Inhabilitar()
{
	document.getElementById("cantidad").disabled=true;
}

function cambiarCombo(obj) {
	if(obj=='N'){//Contado
   		window.document.getElementById("fpago1").style.display="none";
		window.document.getElementById("fpago2").style.display="block";
	}
	else{//Credito
		window.document.getElementById("fpago1").style.display="block";
   		window.document.getElementById("fpago2").style.display="none";
	}
}
