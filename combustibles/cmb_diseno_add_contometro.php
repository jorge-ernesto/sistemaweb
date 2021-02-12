<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>INGRESO CONTOMETRO</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" src="miguel-funciones.js"></script>
	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
	<script language="JavaScript" type="text/JavaScript">


	$(document).ready(function(){

		$.datepicker.regional['es'] = {
			    closeText: 'Cerrar',
			    prevText: '<Ant',
			    nextText: 'Sig>',
			    currentText: 'Hoy',
			    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
			    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
			    dayNames: ['Domingo', 'Lunes', 'Martes', 'Mi�rcoles', 'Jueves', 'Viernes', 'S�bado'],
			    dayNamesShort: ['Dom','Lun','Mar','Mi�','Juv','Vie','S�b'],
			    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S�'],
			    weekHeader: 'Sm',
			    dateFormat: 'dd/mm/yy',
			    firstDay: 1,
			    isRTL: false,
			    showMonthAfterYear: false,
			    yearSuffix: ''
		};

                $.datepicker.setDefaults($.datepicker.regional['es']); 

		$( "#fecha_parte" ).datepicker({
			changeMonth: true,
			changeYear: true,
		});

		$('#id_surtidor').change(function(){

			var cod_almacen = $("#cod_almacen option:selected").val();
			var id_surtidor	= $(this).val();
                        /*$('#cargardor').css({'display':'block'});
                        $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});
*/

                        $.ajax({
				type	: "POST",
				url	: "jquery_contometro.php",
				data	: {
						accion		: 'GetPrecioSurtidor',
						cod_almacen	: cod_almacen,
						id_surtidor	: id_surtidor
					},
				success:function(xm){

					var json=eval('('+xm+')');

					$.each( json, function( key, value ) {
						if(key == 'nuprecio'){
							$('#precio_producto').val(value);
						}else if(key == 'nulecturasgalones'){
							$('#cont_inicial_gal').val(value);
						}else if(key == 'nulecturasoles'){
							$('#cont_inicial_valor').val(value);
						}
					});
					
					//$('#cargardor').css({'display':'none'});
				}
                        });
                });

	});


<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}

function llenarValor(combo,txt){

		//alert(menu.options[menu.selectedIndex].value);
		var	opcion = combo.options[combo.selectedIndex].value;
		txt.value = opcion;
		txt.focus();
		//cod_surtidor.value = var;
}

function completarFormulario(txt,txt_cambiar,txt_cambiar2, precio,form){
	vr1 = new Array(); //datos sobre el contometro inicial en galones
	vr2 = new Array(); //datos sobre el contometro inicial en valor osea lucas
	vr3 = new Array(); //datos sobre los precios
	var ind_uno = parseInt(txt.value,10);
	<?php  
		for($i=0;$i<pg_numrows($rs1);$i++){
		$A = pg_fetch_row($rs1,$i);
		print ' vr1['.$A[0].'] = "'.$A[1].'"; ';
		}
		
		for($i=0;$i<pg_numrows($rs7);$i++){
		$T = pg_fetch_row($rs7,$i);
		print ' vr2['.$T[0].'] = "'.$T[1].'"; ';
		}
		
		for($i=0;$i<pg_numrows($rs8);$i++){
		$K = pg_fetch_row($rs8,$i);
		print ' vr3['.$K[0].'] = "'.$K[1].'"; ';
		}
		
		?>
		
	txt_cambiar.value = vr1[ind_uno];
	txt_cambiar2.value = vr2[ind_uno];
	precio.value = vr3[ind_uno];
	
	form.cont_final_gal.value="";
	form.cont_final_valor.value="";
}

function cuadrarImporte(form){
		var inicial_galon = parseFloat(form.cont_inicial_gal.value);
		var final_galon = parseFloat(form.cont_final_gal.value);
		var inicial_valor = parseFloat(form.cont_inicial_valor.value);
		var afericiones = parseInt(form.afericion.value,10);
		var consumo = parseFloat(form.consumo_interno.value);		
		var precio = parseFloat(form.precio_producto.value);
		var descuen = parseFloat(form.descuentos.value);
		var final_valor = parseFloat( inicial_valor + ((final_galon - inicial_galon )*precio ) );
		if(final_galon>inicial_galon){
			form.cont_final_valor.value = final_valor; 
		}else{
			form.cont_final_valor.value = "";
		}
		
}

function checarFormulario(form){

	var cod_surtidor 	= document.getElementsByName('cod_surtidor')[0].value;
	var inicial_galon 	= document.getElementsByName('cont_inicial_gal')[0].value;
	var final_galon 	= document.getElementsByName('cont_final_gal')[0].value;
	var inicial_valor	= document.getElementsByName('cont_inicial_valor')[0].value;
	var final_valor 	= document.getElementsByName('cont_final_valor')[0].value;
	var afericion 		= document.getElementsByName('afericion')[0].value;
	var descuen 		= document.getElementsByName('descuentos')[0].value;
	var conforme = true;

	if(form.action.value == "cerrar_parte"){
		form.submit();
	}else{

		if(parseFloat(inicial_galon) > parseFloat(final_galon)){
			alert("El Contometro Final Galones no puede ser menor que el Inicial");
			conforme = false;
		}

		if(parseFloat(inicial_valor) > parseFloat(final_valor)){
			alert("El Contometro Final Soles no puede ser menor que el Inicial");
			conforme = false;
		}

		if(cod_surtidor==""){
			alert("No se ha elegido el surtidor");
			conforme = false;
		}

		if(final_galon==""){
			alert("No se ha ingresado la cantidad de galones finales");
			conforme = false;
		}

		if(final_valor=="" && final_galon!=""){
			alert("No se ha ingresado la cantidad de galones finales correcta \n los galones finales deben ser mayores que los iniciales");
			conforme = false;
		}

		if( comprobarSurtidor(cod_surtidor) ){
			alert("El c�digo de surtidor "+cod_surtidor+" ya ha sido ingresado elija otro ");
			conforme = false;
		}

		if(isNaN(final_valor)){
			alert("Hay un error con las afericiones o con el consumo interno en este formulario\n estos 2 campos seran pasados a cero para que pueda procesar el formulario \n pro favor revise estos campos antes grabar");
			conforme = false;
		
		}

		if(afericion==""){
			form.afericion.value = "0.0";
			conforme = false;
			cuadrarImporte(form);
		}

		if(descuen==""){
			form.descuento.value = "0.0";
			conforme = false;
			cuadrarImporte(form);
		}

		if(!checarAfericionConsumo(form)){
			conforme = false;
		}

		if(conforme){
			//alert(form.action.value);
			form.submit();
		}
	
	}//primer if else
}

function cambiarAction(form, txt){
form.action.value = txt;

//alert(form.action.value);
	if(txt=="cambiar_suc"){  form.submit(); }


	if(txt=="importar"){  form.submit(); }
}


function checarAfericionConsumo(form){
	var conforme = true;
	var afericion = form.afericion.value;
//	var consumo = form.consumo_interno.value;
	var afe = parseInt(afericion,10);
//	var con = parseFloat(consumo);
	var inicial_galon = form.cont_inicial_gal.value;
	var final_galon = form.cont_final_gal.value;
	
	if( ((afe*5)) > (final_galon -  inicial_galon)  ){
		alert("La suma de afericiones son mayores que los galones vendidos !");
		conforme = false;
	}else{
		conforme = true;
	}

	return conforme;

}

function comprobarSurtidor(cod_surtidor){ //esta funcion te dice si esta repetido el surtidor o no
		var vr1 = new Array();
		var valor = cod_surtidor;
		var repetido = false;
		<?php
		for($i=0;$i<pg_numrows($rs31);$i++){
		$K = pg_fetch_row($rs31,$i);
		print ' vr1['.$K[0].'] = "'.$K[0].'"; ';
		}
		?>
		
		for(i=0;i<vr1.length;i++){
			if( vr1[i]== valor){  repetido = true;   }
		}
		
return repetido;
}
//-->

function importarContometros(url,num_parte,cod_almacen){
	url = url+'?num_parte='+num_parte+'&cod_almacen='+cod_almacen;
	window.open(url,'miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');
}


</script>

</head>
<body>
<header><h2 style="color: #336699; text-align: center;">Ingreso de Parte de Venta Manual</h2></header>
<?php
//VARIABLES DE ENTRADA:
	$hoy = date("d/m/Y");
?>
<hr noshade>
<form action="cmb_add_contometro.php" method="post" name="form1">
  
      <!--<table width="766">
    <!--DWLayoutTable-->
	<table border="0" align="center">
		<input type="hidden" name="num_parte" value="<?php echo $num_parte?>">
    		<!--<tr> 
		      	<td width="191" height="21" valign="top"><!--Nro de Parte: <?php echo $num_parte;?> 
			<input type="hidden" name="num_parte" value="<?php echo $num_parte?>">				
			</td>//COMENTADO 06/08/2016-->
    		<tr>
		      	<td align="right">Almacen: 
			</td>
			<td>
				<select id="cod_almacen" name="cod_almacen" onChange="javascript:cambiarAction(form1,'cambiar_suc')">
		  		<?php
					echo $SUC; 
					for($i=0; $i<pg_numrows($rsx1); $i++){		
						$B = pg_fetch_row($rsx1,$i);		
						print "<option value='$B[0]' >$B[0] -- $B[1]</option>";	
			  		}
				?>
				</select>
			</td>
    		</tr>
    		<tr>
		      	<td align="right">Fecha Venta:
			</td>
			<td>
				<input type="text" maxlength="10" size="12" name="fecha_parte" id="fecha_parte" class="fecha_formato" value="<?php echo (empty($_REQUEST['fecha_parte']) ? $hoy : $_REQUEST['fecha_parte'])?>" />
				<!--<input type="text" name="fecha_parte" value="<?php echo $hoy;?>" onKeyUp="javascript:validarFecha(this)" maxlength="10">-->
			</td>
    		</tr>
  	</table>
	<br/>
<!--  	<table width="747" border="1" cellpadding="0" cellspacing="0">-->
	<table align="center">
		<tr> 
      			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Surtidor
			</td>
      			<!--<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Precio

			</td>-->
      			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Inicial Galones

			</td>
      			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Final Galones

			</td>
      			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Inicial Soles

			</td>
      			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Final Soles

			</td>
      			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Afericiones
			</td>

      			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Descuento Nota Despacho
			</td>
		</tr>

		<tr>
      			<td>
				<input type="hidden" id="cod_surtidor" name="cod_surtidor" onFocus="javascript:completarFormulario(cod_surtidor,form1.cont_inicial_gal,form1.cont_inicial_valor,form1.precio_producto,form1);" onKeyUp="javascript:completarFormulario(cod_surtidor,form1.cont_inicial_gal,form1.cont_inicial_valor,form1.precio_producto,form1)">
          			<select name="surtidor_help" id="id_surtidor" onChange="javascript:llenarValor(surtidor_help,form1.cod_surtidor)" >
		    			<option value="" selected>Seleccionar surtidor...</option>
		    			<?php
						for($i=0; $i<pg_numrows($rs2); $i++){ 
							$B = pg_fetch_array($rs2,$i);
							print "<option value='$B[0]'>$B[1]</option>";
						}
		  			?>
         			</select>
			</td>
		     	<!--<td>
				<input type="text" maxlength="11" size="13" id="precio_producto" name="precio_producto">
			</td>-->
      			<td>
				<input type="text" maxlength="18" size="20" id="cont_inicial_gal" name="cont_inicial_gal">
			</td>
			<td>
				<input type="text" maxlength="18" size="20" id="cont_final_gal" name="cont_final_gal" onKeyUp="javascript:validarNumeroDecimales(this) , cuadrarImporte(form1)">
			</td>
      			<td>
				<input type="text" maxlength="18" size="20" id="cont_inicial_valor" name="cont_inicial_valor">
			</td>
			<td>
				<input type="text" maxlength="18" size="20" id="cont_final_valor" name="cont_final_valor">
			</td>
			<td>
				<input type="text" name="afericion" onKeyUp="javascript:validarNumeroDecimales(this), cuadrarImporte(form1)" value="0.0" size="10">
			</td>
			<td>
				<input type="text" name="descuentos" onKeyUp="javascript:validarNumeroDecimales(this), cuadrarImporte(form1)" value="0.0" size="10">
			</td>
			<td>
	<input type="button" name="agregar" value="Agregar" onClick="javascript:( cambiarAction(form1,'grabar') , checarFormulario(form1) )">

				<input type="hidden" name="action"></td><?php if(trim($cod_almacen=="")){$cod_almacen=trim($almacen);} ?>
			
				<!--<button type="submit" value="Agregar" name="agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button>-->
			</td>
		</tr>
	</table>
	<br/>

  	<!--<table width="750"> COMENTADO: 06/08/2016
    	<!--DWLayoutTable-->
	
	<!--<table border="1">
		<tr> 
			<td width="164" height="25" valign="top">
				<input type="button" name="agregar" value="Agregar" onClick="javascript:( cambiarAction(form1,'grabar') , checarFormulario(form1) )">
				<input type="hidden" name="action"></td><?php if(trim($cod_almacen=="")){$cod_almacen=trim($almacen);} ?>
			</td>
			<!--<td width="408">
				<input type="button" name="cerrar_parte" value="Cerrar Parte" onClick="javascript:cambiarAction(form1,'cerrar_parte') , checarFormulario(form1) ;">
				<input type="button" name="btn_importar" value="Importar" onClick="javascript:importarContometros('cmb_contometros_automaticos.php','<?php echo $num_parte;?>','<?php echo $cod_almacen;?>');">


	</table>

	<table width="766" border="1" cellpadding="0" cellspacing="0">-->
	<br/>
	<table align="center" border="0">
		<h3 style="color: green; text-align: center;">Listado Lecturas de Contometro</h3>
		<tr>
			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Sutidor
			</td>
			<!--<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Precio
			</td>-->
			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Inicial Galones
			</td>
			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Final Galones
			</td>
			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Inicial Soles
			</td>
			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Contometro Final Soles
			</td>
			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Afericiones Final Soles
			</td>
			<td align="center" class="grid_cabecera"><p style='font-size:1.2em; color:white;'><b>
				Descuento Nota Despacho
			</td>
		</tr>

    		<!-- <?php for($i=0;$i<pg_num_rows($rs31);$i++){
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$H = pg_fetch_array($rs31,$i);
			print ' -->
		<tr> 
		      <td align="center" class='.$color.'>'.$H[0].'</font></div></td>
		      <td align="right" class='.$color.'>'.$H[1].'</font></div></td>
		      <td align="right" class='.$color.'>'.$H[2].'</font></div></td>
		      <td align="right" class='.$color.'>'.$H[3].'</font></div></td>
		      <td align="right" class='.$color.'>'.$H[4].'</font></div></td>
		      <td align="right" class='.$color.'>'.$H[5].'</font></div></td>
		      <td align="right" class='.$color.'>'.$H[6].'</font></div></td>
		</tr>
		<!-- '; } ?> -->
	</table>

	<p>&nbsp;</p>

</form>
</body>
</html>
