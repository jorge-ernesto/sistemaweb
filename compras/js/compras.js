function myFunction(){

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


	$( "#fperiodo" ).datepicker({
		changeMonth: true,
		changeYear: true,
	});

	$( "#femision" ).datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect:function(fecha,obj){

  			$('#cargardor').css({'display':'block'});
   			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$('#Almacen').prop("disabled", false);
			$('#btn-GuardarCompra').prop("disabled", false);
			$('#rubro').prop("disabled", false);

   			var nu_almacen 	= $('#estacion').val();
			var fecha 		= $('#femision').val();
			var tipomoneda 	= $(".tiposmoneda").val();

			$.ajax({
			  	type: "POST",
			  	dataType: "JSON",
		    	url: "/sistemaweb/inventarios/forms/fecha.php",
		    	data:{
					accion 				: 'verifyConsolidacion',
					nu_almacen_destino 	: nu_almacen,
					fecha 				: fecha,
		    	},
		    	success:function(response){
					if(response.status == 'danger'){
						$('#Almacen').prop("disabled", true);
						$('#btn-GuardarCompra').prop("disabled", true);
						$('#rubro').prop("disabled", true);
						$("#error").text(response.message);
					}else{
						$("#error").html('');
						$.ajax({
						  	type: "POST",
					    	url: "/sistemaweb/combustibles/reportes/c_descuentos_especiales.php",
					    	data:{
								accion:'TipoCambioCompra',
								fecha:fecha,
								tipomoneda:tipomoneda,
					    	},
					    	success:function(xm){
								$(".valormoneda").val(xm);
							}
						});

						$.ajax({
						  	type: "POST",
						    	url: "/sistemaweb/combustibles/reportes/c_descuentos_especiales.php",
						    	data:{
								accion:'Correlativo',
								fecha:fecha,
						    	},
						    	success:function(xm){
								$(".correlativo").val(xm);
								$("#numerator").val(xm);
							}
						});
					}
				}
			});
		}
	});

}

function PaginarRegistros(rxp, valor, fecha, fecha2, estacion, proveedorb, documento, tdocu, tmoneda) {
	var e 		= document.getElementById("tmoneda");
    var tmoneda = e.options[e.selectedIndex].value;

	urlPagina = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=Buscar&rxp='+rxp+'&pagina='+valor+'&fecha='+fecha+'&fecha2='+fecha2+'&estacion='+estacion+'&proveedorb='+proveedorb+'&documento='+documento+'&tdocu='+tdocu+'&tmoneda='+tmoneda;
	document.getElementById('control').src = urlPagina;	
}

function confirmarLink(pregunta, accionY, accionN, target) {  

  	if(confirm(pregunta))
    		document.getElementById('control').src = accionY;

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

function CalcularFecha(days){

	femision = document.getElementsByName('femision')[0].value;

	var nday = femision.substr(0,2);
	var nmes = femision.substr(3,2);
	var nano = femision.substr(6,4);

	newfecha = nano+"/"+nmes+"/"+nday;

	fecha=new Date(newfecha);

	day=fecha.getDate();
	month=fecha.getMonth()+1;
	year=fecha.getFullYear();

	/*DIA ACTUAL*/

	tiempo=fecha.getTime();
	milisegundos=parseInt(days*24*60*60*1000);
	total=fecha.setTime(tiempo+milisegundos);
	day=fecha.getDate();
	month=fecha.getMonth()+1;
	year=fecha.getFullYear();

	if(month.toString().length<2){
		month="0".concat(month);        
	}    

	if(day.toString().length<2){
		day="0".concat(day);        
	}

	/*FECHA VENCIMIENTO*/

	fvencimiento = day+"/"+month+"/"+year;	

	document.getElementsByName('fvencimiento')[0].value = fvencimiento;
}

function Contabilizar(valor){

	if(valor=="N"){
		dis='none';
		fila=document.getElementById("celda1");
		fila.style.display=dis;
	}else if(valor=="S"){
		dis='';
		fila=document.getElementById("celda1");
		fila.style.display=dis;
	}


}


function Mostrar(valor){

	if(valor=="20"){
		dis='';
		fila=document.getElementById("tiporef");
		fila2=document.getElementById("tiporef2");
		fila3=document.getElementById("serieref");
		fila4=document.getElementById("serieref2");
		fila5=document.getElementById("documentoref");
		fila6=document.getElementById("documentoref2");
		fila.style.display=dis;
		fila2.style.display=dis;
		fila3.style.display=dis;
		fila4.style.display=dis;
		fila5.style.display=dis;
		fila6.style.display=dis;
	}else{
		dis='none';
		fila=document.getElementById("tiporef");
		fila2=document.getElementById("tiporef2");
		fila3=document.getElementById("serieref");
		fila4=document.getElementById("serieref2");
		fila5=document.getElementById("documentoref");
		fila6=document.getElementById("documentoref2");
		fila.style.display=dis;
		fila2.style.display=dis;
		fila3.style.display=dis;
		fila4.style.display=dis;
		fila5.style.display=dis;
		fila6.style.display=dis;
	}

}

function Inafecto(valor){

	if(valor=="N"){
		celda=document.getElementById("celda2");
		celda.style.display='none';
	}else if (valor=="S"){
		celda=document.getElementById("celda2");
		celda.style.display='';
	}
	
}


function Rubro(r,fecha,fecha2, rxp, pagina, almacen, pro, doc, tdocu){

	var x = document.getElementById("rubro").selectedIndex;
	var y = document.getElementById("rubro").options;

	rubro = y[x].text;

	document.Agregar.Regresar.disabled=false;

	if(/\s/.test(r)){
		document.Agregar.Almacen.disabled=true;
		var valval = 0.00;
		url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=VerTotales&fecha='+fecha+'&fecha2='+fecha2+'&rubro='+rubro+'&rxp='+rxp+'&pagina='+pagina+'&almacen='+almacen+'&pro='+pro+'&doc='+doc+'&tdocu='+tdocu;
		document.getElementById('control').src = url;
		return;
	}else{
		document.Agregar.Almacen.disabled=false;
	}

}

function hallarSubTotal(fn,cantidad,valor,tipo,fecha,fecha2,f,cual, rxp, pagina, almacen, pro, doc, tdocu, tmoneda){

	var x = document.getElementById("rubro").selectedIndex;
	var y = document.getElementById("rubro").options;

	rubro = y[x].text;

	var newvalor;
	var newcantidad;
	var newbase;
	var newimp;
	var newtot;

	var valval = document.getElementsByName('subtotal')[0];
	var valcan = document.getElementById('cantidad');

	var costo = 0;
	var cadena;

	var ver;

	ver = document.getElementById(fn).checked;

	if (document.getElementById(fn).checked){
		if(tipo==05){
			newvalor = parseFloat(valval.value) - parseFloat(valor);
			newcantidad = parseFloat(valcan.value) - parseFloat(cantidad);
		}else{

			newvalor = parseFloat(valval.value) + parseFloat(valor);
			newcantidad = parseFloat(valcan.value) + parseFloat(cantidad);
			document.getElementById('actualcan').value = newcantidad;
			document.getElementById('actualtot').value = newvalor;
		}

	}else if(document.getElementById(fn).checked == false){
		if(tipo==05){
			ncanti = document.getElementById('actualcan').value;
			nvalor = document.getElementById('actualtot').value;
			newcantidad = parseFloat(ncanti);
			newvalor = parseFloat(nvalor);
		}else{
			newvalor = parseFloat(valval.value) - parseFloat(valor);
			newcantidad = parseFloat(valcan.value) - parseFloat(cantidad);
		}
	}

	valval.value = Math.round(newvalor*100)/100;
	valcan.value = Math.round(newcantidad*100)/100;

	/* CAPTURAR TODOS LOS ID PARA ACTUALIZAR POR FILA */

	todos = new Array();

	for (var i = 0, total = f[cual].length; i < total; i++){
		if (f[cual][i].checked){
			todos[todos.length] = f[cual][i].value;
		}
	}

	url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&action=hallarSubTotal&task=APLICACIONESDET&base='+valval.value+'&rubro='+rubro+'&id='+todos.join(",")+'&rxp='+rxp+'&pagina='+pagina+'&almacen='+almacen+'&pro='+pro+'&doc='+doc+'&tdocu='+tdocu+'&tmoneda='+tmoneda;
//	url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&action=hallarSubTotal&task=APLICACIONESDET&base='+valval.value+'&fecha='+fecha+'&fecha2='+fecha2+'&rubro='+rubro+'&id='+todos.join(",")+'&correlativo='+correlativo;
	document.getElementById('control').src = url;
	return;

}

function ir(id){

	/* VALIDACIONES */

	if(document.getElementsByName('proveedor')[0].value == ""){
		alert("Falta ingresar Proveedor");
	}else if(document.getElementsByName('tipo')[0].value == ""){
		alert("Falta ingresar Tipo Documento");
	}else if(document.getElementsByName('serie')[0].value == ""){
		alert("Falta ingresar Serie Documento");
	}else if(document.getElementsByName('documento')[0].value == ""){
		alert("Falta ingresar Numero Documento");
	}else if(document.getElementsByName('dvec')[0].value == ""){
		alert("Falta ingresar Dias de Vencimiento");
	}else if(document.getElementsByName('tc')[0].value == ""){
		alert("Falta ingresar Tipo de Cambio");
	}else{

		/* VALORES DE REGISTROS */

//		correlativo	= document.getElementsByName('correlativo')[0].value;

		estacion		= document.getElementsByName('estacion')[0].value;
		femision		= document.getElementsByName('femision')[0].value;
		proveedor		= document.getElementsByName('proveedor')[0].value;
		rubro			= document.getElementsByName('rubro')[0].value;
		tipo			= document.getElementsByName('tipo')[0].value;
		serie			= document.getElementsByName('serie')[0].value;
		documento		= document.getElementsByName('documento')[0].value;
		dvec			= document.getElementsByName('dvec')[0].value;
		fvencimiento	= document.getElementsByName('fvencimiento')[0].value;
		tc				= document.getElementsByName('tc')[0].value;
		moneda			= document.getElementsByName('moneda')[0].value;
		tiporef			= document.getElementsByName('tiporef')[0].value;
		serieref		= document.getElementsByName('serieref')[0].value;
		documentoref	= document.getElementsByName('documentoref')[0].value;
//		contabilizar	= document.getElementsByName('contabilizar')[0].value;
		fperiodo		= document.getElementsByName('fperiodo')[0].value;
		txt_glosa		= document.getElementsByName('txt_glosa')[0].value;

		rxp			= document.getElementsByName('rxp')[0].value;
		pagina		= document.getElementsByName('pagina')[0].value;
		fecha		= document.getElementsByName('fecha')[0].value;
		fecha2		= document.getElementsByName('fecha2')[0].value;
		rubros		= document.getElementsByName('rubros')[0].value;
		codalmacen	= document.getElementsByName('codalmacen')[0].value;
		pro			= document.getElementsByName('pro')[0].value;
		doc			= document.getElementsByName('doc')[0].value;
		tdocu		= document.getElementsByName('tdocu')[0].value;
		tmoneda		= document.getElementsByName('tmoneda')[0].value;

		/* TOTALES */

		base 		= document.getElementsByName('base')[0].value;
		impuesto	= document.getElementsByName('impuesto')[0].value;
		total 		= document.getElementsByName('total')[0].value;
		perce 		= document.getElementsByName('perce')[0].value;
		inafecto	= document.getElementsByName('inafecto')[0].value;

		url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=Guardar&estacion='+estacion+'&femision='+femision+'&proveedor='+proveedor+'&rubro='+rubro+'&tipo='+tipo+'&serie='+serie+'&documento='+documento+'&dvec='+dvec+'&fvencimiento='+fvencimiento+'&tc='+tc+'&moneda='+moneda+'&base='+base+'&impuesto='+impuesto+'&total='+total+'&perce='+perce+'&tiporef='+tiporef+'&serieref='+serieref+'&documentoref='+documentoref+'&fecha='+fecha+'&fecha2='+fecha2+'&rubros='+rubros+'&id='+id+'&inafecto='+inafecto+'&fperiodo='+fperiodo+'&codalmacen='+codalmacen+'&pro='+pro+'&doc='+doc+'&tdocu='+tdocu+'&rxp='+rxp+'&pagina='+pagina+'&tmoneda='+tmoneda+'&txt_glosa='+txt_glosa;
//		url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=Guardar&estacion='+estacion+'&femision='+femision+'&proveedor='+proveedor+'&rubro='+rubro+'&tipo='+tipo+'&serie='+serie+'&documento='+documento+'&dvec='+dvec+'&fvencimiento='+fvencimiento+'&tc='+tc+'&moneda='+moneda+'&base='+base+'&impuesto='+impuesto+'&total='+total+'&perce='+perce+'&tiporef='+tiporef+'&serieref='+serieref+'&documentoref='+documentoref+'&fecha='+fecha+'&fecha2='+fecha2+'&rubros='+rubros+'&id='+id+'&correlativo='+correlativo+'&inafecto='+inafecto+'&contabilizar='+contabilizar+'&fperiodo='+fperiodo+'&codalmacen='+codalmacen+'&pro='+pro+'&doc='+doc+'&tdocu='+tdocu+'&rxp='+rxp+'&pagina='+pagina;
	  	document.getElementById('control').src = url;
	  	return;

	}

}

function irOtros(){

	/* VALIDACIONES */

	if(document.getElementsByName('proveedor')[0].value == ""){
		alert("Falta ingresar Proveedor");
	}else if(document.getElementsByName('tipo')[0].value == ""){
		alert("Falta ingresar Tipo Documento");
	}else if(document.getElementsByName('serie')[0].value == ""){
		alert("Falta ingresar Serie Documento");
	}else if(document.getElementsByName('documento')[0].value == ""){
		alert("Falta ingresar Numero Documento");
	}else if(document.getElementsByName('dvec')[0].value == ""){
		alert("Falta ingresar Dias de Vencimiento");
	}else if(document.getElementsByName('tc')[0].value == ""){
		alert("Falta ingresar Tipo de Cambio");
	}else{

		/* VALORES DE REGISTROS */

		estacion		= document.getElementsByName('estacion')[0].value;
		femision		= document.getElementsByName('femision')[0].value;
		proveedor		= document.getElementsByName('proveedor')[0].value;
		rubro			= document.getElementsByName('rubro')[0].value;
		tipo			= document.getElementsByName('tipo')[0].value;
		serie			= document.getElementsByName('serie')[0].value;
		documento		= document.getElementsByName('documento')[0].value;
		dvec			= document.getElementsByName('dvec')[0].value;
		fvencimiento	= document.getElementsByName('fvencimiento')[0].value;
		tc				= document.getElementsByName('tc')[0].value;
		moneda			= document.getElementsByName('moneda')[0].value;
		tiporef			= document.getElementsByName('tiporef')[0].value;
		serieref		= document.getElementsByName('serieref')[0].value;
		documentoref	= document.getElementsByName('documentoref')[0].value;
		fperiodo		= document.getElementsByName('fperiodo')[0].value;
		txt_glosa		= document.getElementsByName('txt_glosa')[0].value;

		rxp			= document.getElementsByName('rxp')[0].value;
		pagina		= document.getElementsByName('pagina')[0].value;
		fecha		= document.getElementsByName('fecha')[0].value;
		fecha2		= document.getElementsByName('fecha2')[0].value;
		rubros		= document.getElementsByName('rubros')[0].value;
		codalmacen	= document.getElementsByName('codalmacen')[0].value;
		pro			= document.getElementsByName('pro')[0].value;
		doc			= document.getElementsByName('doc')[0].value;
		tdocu		= document.getElementsByName('tdocu')[0].value;
		tmoneda		= document.getElementsByName('tmoneda')[0].value;

		/* TOTALES */

		base 		= document.getElementsByName('base')[0].value;
		impuesto	= document.getElementsByName('impuesto')[0].value;
		total 		= document.getElementsByName('total')[0].value;
		perce 		= document.getElementsByName('perce')[0].value;
		inafecto	= document.getElementsByName('inafecto')[0].value;

		url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=GuardarOtros&estacion='+estacion+'&femision='+femision+'&proveedor='+proveedor+'&rubro='+rubro+'&tipo='+tipo+'&serie='+serie+'&documento='+documento+'&dvec='+dvec+'&fvencimiento='+fvencimiento+'&tc='+tc+'&moneda='+moneda+'&base='+base+'&impuesto='+impuesto+'&total='+total+'&perce='+perce+'&tiporef='+tiporef+'&serieref='+serieref+'&documentoref='+documentoref+'&fecha='+fecha+'&fecha2='+fecha2+'&rubros='+rubros+'&inafecto='+inafecto+'&fperiodo='+fperiodo+'&codalmacen='+codalmacen+'&pro='+pro+'&doc='+doc+'&tdocu='+tdocu+'&rxp='+rxp+'&pagina='+pagina+'&tmoneda='+tmoneda+'&txt_glosa='+txt_glosa;
	  	document.getElementById('control').src = url;
	  	return;

	}

}

function getRegistroProB(campo){
  	url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=setRegistroProB&proveedorb='+campo;
  	document.getElementById('control').src = url;
  	return;
}

function setRegistroProB(campo){
  	txt_campo = document.getElementsByName('proveedorb')[0];
  	txt_campo.value = campo;
}

function getRegistroPro(campo){
  	url = 'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=setRegistroPro&proveedor='+campo;
  	document.getElementById('control').src = url;
  	return;
}

function setRegistroPro(campo,days,rubro){

  	txt_campo = document.getElementsByName('proveedor')[0];
  	txt_campo.value = campo;
  	txt_days = document.getElementsByName('dvec')[0];
  	txt_days.value = days;

	/* CALCULAR FECHA DE VENCIMIENTO*/
	femision = document.getElementsByName('femision')[0].value;

	var nday = femision.substr(0,2);
	var nmes = femision.substr(3,2);
	var nano = femision.substr(6,4);

	newfecha = nano+"/"+nmes+"/"+nday;

	fecha=new Date(newfecha);

	day=fecha.getDate();
	month=fecha.getMonth()+1;
	year=fecha.getFullYear();

	/*DIA ACTUAL*/

	tiempo=fecha.getTime();
	milisegundos=parseInt(days*24*60*60*1000);
	total=fecha.setTime(tiempo+milisegundos);
	day=fecha.getDate();
	month=fecha.getMonth()+1;
	year=fecha.getFullYear();

	if(month.toString().length<2){
		month="0".concat(month);        
	}    

	if(day.toString().length<2){
		day="0".concat(day);        
	}

	/*FECHA VENCIMIENTO*/

	fvencimiento = day+"/"+month+"/"+year;	

	document.getElementsByName('fvencimiento')[0].value = fvencimiento;

	/* SELECCIONAR RUBRO */
	document.getElementById('rubro').value = rubro;

  	return;
}

function Totales(base){

	act = ((parseFloat(document.getElementsByName('vali')[0].value)) + parseFloat(document.getElementsByName('limit')[0].value));
	act2 = ((parseFloat(document.getElementsByName('vali')[0].value)) - parseFloat(document.getElementsByName('limit')[0].value));

	if(base > act){

		alert('Solo puedes aumentar 0.02 a la base imponible');

		baseact = parseFloat(document.getElementsByName('vali')[0].value);
		impuestoact = (baseact * 0.18);
		totalact = (parseFloat(baseact) + parseFloat(impuestoact));

		document.getElementsByName('base')[0].value = baseact.toFixed(2);
		document.getElementsByName('impuesto')[0].value = impuesto.toFixed(2);
		document.getElementsByName('total')[0].value = total.toFixed(2);

	}else if(base < act2){

		alert('Solo puedes quitar 0.02 a la base imponible');

		baseact = parseFloat(document.getElementsByName('vali')[0].value);
		impuestoact = (baseact * 0.18);
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

function CalcularTotales(base, igv){

		var total = (parseFloat(base) * parseFloat(igv));
		var impuesto = total - base;

		document.getElementsByName('base')[0].value = base;
		document.getElementsByName('impuesto')[0].value = impuesto.toFixed(2);
		document.getElementsByName('total')[0].value = total.toFixed(2);
}

function cceros(v_var,v_lon,k_var) {

	var v_var2= v_var.value.replace(/^\s*|\s*$/g,"");
	var lon1  = v_var.value.length;
	var lon2  = v_lon-lon1
	
	for(i=0;i<lon2;i++) {
		v_var2='0'+v_var2;
	}

	eval("document.Agregar."+k_var+".value=v_var2");

}

function cceros2(v_var,v_lon,k_var) {

	var v_var2= v_var.value.replace(/^\s*|\s*$/g,"");
	var lon1  = v_var.value.length;
	var lon2  = v_lon-lon1
	
	for(i=0;i<lon2;i++) {
		v_var2='0'+v_var2;
	}

	eval("document.Actualizar."+k_var+".value=v_var2");

}

function cambiarDisplay(id,can){  

	if (!document.getElementById) return false;
		for(var i=0;i<can;i++){
 			if(('row'+i)==id){
				fila = document.getElementById('row'+i);
				if (fila.style.display != "none") {
					fila.style.display = "none"; //ocultar fila
				}else{
					fila.style.display = ""; //mostrar fila
				}
			}else{
 				fila = document.getElementById('row'+i);
			 	fila.style.display = "none";
			}  
		} 
}

function Excel(){

	fecha 		= document.getElementsByName('fecha')[0].value;
	fecha2 		= document.getElementsByName('fecha2')[0].value;
	estacion 	= document.getElementsByName('estacion')[0].value;
	proveedor 	= document.getElementsByName('proveedorb')[0].value;
	documento 	= document.getElementsByName('documento')[0].value;
	tdocu 		= document.getElementsByName('tdocu')[0].value;
	tmoneda 	= document.getElementsByName('tmoneda')[0].value;

	var type_ple = null;
	type_ple2	= document.getElementsByName('pletype');

	for (var i = 0; i < type_ple2.length; i++){
		if (type_ple2[0].checked)
			type_ple = 'RC';
		else if (type_ple2[1].checked)
			type_ple = 'RCD';
		else
			type_ple = 'RCS';
	}

	url = 'excel_registro_compras.php?fecha='+fecha+'&fecha2='+fecha2+'&estacion='+estacion+'&proveedor='+proveedor+'&documento='+documento+'&tdocu='+tdocu+'&tmoneda='+tmoneda+'&type_ple='+type_ple;
	document.getElementById('excel').src = url;
    return;
}






