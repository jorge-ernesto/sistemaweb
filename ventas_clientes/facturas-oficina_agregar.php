<?php

/*
*		CAMBIOS EN LA BASE DE DATOS PARA QUE FUNQUE ESTA OPCION...
*
*		Insertar un registro que
*		ponga por defecto el numero de serie de este formulario
*		desde el int_num_documentos;
*
*		select par_valor from int_parametros where par_nombre='cliente_defecto'
*
*/


include("../valida_sess.php");
include("../functions.php");

include("store_procedures.php");
require("../clases/funciones.php");
include("inc_top.php");
include("/sistemaweb/inventarios/js/inv_addmov_support.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if($c_fec_documento==""){$c_fec_documento=date("d/m/Y");}

switch($accion){
	case "Agregar":
		$ITEMS = $_SESSION["AR_ITEMS"];
		$pasa = true;
		for($i=0;$i<count($ITEMS);$i++){
			$A = $ITEMS[$i];
			if($A["art_codigo_item"]==$c_art_codigo_item){
				$pasa = false ;
				print "<script>alert('Este articulo ya ha sido ingresado');</script>";
				break;
			}
		}
		
		if($pasa){
		$ITEM["art_codigo_item"] = $c_art_codigo_item;
		$ITEM["des_articulo_item"] = $c_des_articulo_item;
		$ITEM["cantidad_item"] = $c_cantidad_item;
		$ITEM["precio_item"] = $c_precio_item;
		$ITEM["importe_item"] = $c_importe_item;
		$ITEM["impuesto_item"] = $c_impuesto_item;
		$ITEM["descuento_item"] = $c_descuento_item;
		$ITEM["total_item"] = $c_total_item;
		$ITEM["tipo_descuento_item"] = $c_descuento;
		
		$ITEMS[count($ITEMS)] = $ITEM;
		$_SESSION["AR_ITEMS"] = $ITEMS;
		}
		
	break;
	
	case "Cancelar":
	
		$ITEMS = $_SESSION["AR_ITEMS"];
		$ITEMS = null;
		$_SESSION["AR_ITEMS"] = null;
		unset($ITEMS);
		unset($AR_ITEMS);
		echo "Cancelar ";
		
	break;
	
	
	case "Eliminar":
		$ITEMS = $_SESSION["AR_ITEMS"];
		for($i=0;$i<count($ar_items);$i++){
			$posi = $ar_items[$i];
			array_splice($ITEMS,$posi,1);
		}
		$_SESSION["AR_ITEMS"] = $ITEMS;	
	
	break;
	
	case "Terminar":
		$ITEMS = $_SESSION["AR_ITEMS"];
		echo "Ingresamos a factura cabecera " ;
		$q = "insert into fac_ta_factura_cabecera 
		(ch_fac_tipodocumento,
		ch_fac_seriedocumento,
		ch_fac_numerodocumento,
		cli_codigo,
		dt_fac_fecha,
		ch_almacen,
		ch_fac_moneda,
		nu_tipocambio,
		nu_fac_valorbruto,
		ch_factipo_descuento1,
		nu_fac_descuento1,
		ch_fac_cd_impuesto1,
		nu_fac_impuesto1,
		nu_fac_valortotal,
		ch_fac_forma_pago,
		ch_fac_anticipo)
							VALUES
		('$c_documento'			,'$c_serie'				,'$c_num_documento'
		,'$c_cod_cliente'		,to_date('$c_fec_documento','dd/mm/yyyy')
		,'$c_almacen '			,'$c_moneda '			,$c_tipo_cambio
		,$c_total_importe 		,'$c_descuento'			
		,$c_total_descuento		
		,'$c_cod_igv'
		,$c_total_igv			,$c_total_total			,'$c_forma_pago'
		,'$c_anticipado')";
		echo "<!--$q-->\n";
		pg_exec($q);
		
		
		echo "ingresamos en facturas detalle" ;
		
		for($i=0;$i<count($ITEMS);$i++){
			$A = $ITEMS[$i];
			$q = "insert into fac_ta_factura_detalle 
				(ch_fac_tipodocumento,
				ch_fac_seriedocumento,
				ch_fac_numerodocumento,
				cli_codigo,
				art_codigo,
				pre_lista_precio,
				nu_fac_cantidad,
				nu_fac_precio,
				nu_fac_importeneto,
				ch_factipo_descuento1,
				nu_fac_descuento1,
				ch_fac_cd_impuesto1,
				nu_fac_impuesto1,
				nu_fac_valortotal) 
					VALUES 
				('$c_documento'				,'$c_serie'					,'$c_num_documento'
				,'$c_cod_cliente'			,'".$A["art_codigo_item"]."'
				,'$c_lista_precios'			,".$A["cantidad_item"]."	,".$A["precio_item"]."
				,".$A["importe_item"]."		,'".$A["tipo_descuento_item"]."'
				,".$A["descuento_item"]."	,'".$c_cod_igv."'
				,".$A["impuesto_item"]."	,".$A["total_item"].")
				";
				//echo $q."<br>";
				pg_exec($q);
		}

		if(session_is_registered("ARR_COMP")){
			$COMP = $_SESSION["ARR_COMP"];
			if(trim($COMP["ruc"])!=""){
  			echo "insertamos el complemento" ;
			$q = "insert into fac_ta_factura_complemento
			(ch_fac_tipodocumento,
			ch_fac_seriedocumento,
			ch_fac_numerodocumento,
			cli_codigo,
			dt_fac_fecha,
			ch_fac_observacion1,
			ch_fac_observacion2,
			ch_fac_observacion3,
			ch_fac_ruc,
			nu_fac_direccion,
			nu_fac_complemento_direccion,
			dt_fechactualizacion)
			VALUES 
			('$c_documento',
			'$c_serie',
			'$c_num_documento',
			'$c_cod_cliente',
			to_date('$c_fec_documento','dd/mm/yyyy'),
			'".$COMP["obs1"]."',
			'".$COMP["obs2"]."',
			'".$COMP["obs3"]."',
			'".$COMP["ruc"]."',
			'".$COMP["direccion"]."',
			'".$COMP["comp_dir"]."',
			current_date)
			";
			pg_exec($q);
			}
		}
		
		if($c_anticipado=="N"){
			echo "INSERTAMOS ccob cabecera" ;
			$q = "insert into ccob_ta_cabecera 
			(cli_codigo,
			ch_tipdocumento,
			ch_seriedocumento,
			ch_numdocumento,
			ch_tipcontable,
			dt_fechaemision,
			dt_fecharegistro,
			dt_fechavencimiento,
			nu_dias_vencimiento,
			ch_moneda,
			nu_tipocambio,
			nu_importetotal,
			plc_codigo,
			ch_sucursal,
			nu_importeafecto,
			ch_tipoimpuesto1,
			nu_impuesto1)
			VALUES 
			('$c_cod_cliente',
			'$c_documento',
			'$c_serie',
			'$c_num_documento',
			util_fn_tipo_accion_contable('CC','$c_documento'),
			to_date('$c_fec_documento','dd/mm/yyyy'),
			to_date('$c_fec_documento','dd/mm/yyyy'),
			to_date('$c_fec_documento','dd/mm/yyyy')+ interval '$c_dias_pago day',
			$c_dias_pago,
			'$c_moneda',
			$c_tipo_cambio,
			$c_total_total,
			'-',
			'$c_almacen',
			$c_total_importe,
			'$c_cod_igv',
			$c_total_igv)";
			
			//echo $q."<br>";
			pg_exec($q);
			
			echo "INSERTAMOS ccob detalle --identidad 001 Y TIPO DE MOVIMIENTO 1 PUESTITO!!!" ;
						
				$q = "insert into ccob_ta_detalle
				(cli_codigo,
				ch_tipdocumento,
				ch_seriedocumento,
				ch_numdocumento,
				ch_identidad,
				ch_tipmovimiento,
				dt_fechamovimiento,
				ch_moneda,
				nu_tipocambio,
				nu_importemovimiento,
				plc_codigo,
				ch_sucursal,
				dt_fecha_actualizacion)
				VALUES
				('$c_cod_cliente',
				'$c_documento',
				'$c_serie',
				'$c_num_documento',
				'001',
				'1',
				to_date('$c_fec_documento','dd/mm/yyyy'),
				'$c_moneda',
				$c_tipo_cambio,
				$c_total_importe,
				'-',
				'$c_almacen',
				current_date
				)";
				
				//echo $q."<br>";
				pg_exec($q);
			echo "Enlazando inventarios" ;
			$CONF = inicializarVariables($c_documento,$c_almacen);
			$natu = $CONF["natu"];
			$origen  = $c_almacen;
			$destino = $CONF["alma_des"];
			for($i=0;$i<count($ITEMS);$i++)
			{
			
				$A = $ITEMS[$i];
				$costo_prom = pg_result(
				pg_exec("select util_fn_costo_promedio(to_char(to_date('$c_fec_documento','dd/mm/yyyy'),'yyyy')
				,to_char(to_date('$c_fec_documento','dd/mm/yyyy'),'mm'),'".$A["art_codigo_item"]."',lpad('$c_almacen',3,'0'))")
				,0,0);
				
				$q = "insert into inv_movialma 
				(mov_numero,
				tran_codigo,
				art_codigo,
				mov_fecha,
				mov_almacen,
				mov_almaorigen,
				mov_almadestino,
				mov_naturaleza,
				mov_entidad,
				mov_cantidad,
				mov_costounitario,
				mov_costopromedio,
				mov_costototal,
				mov_fecha_actualizacion) 
				VALUES 
				('".$c_serie.$c_num_documento."',
				'$c_documento',
				'".$A["art_codigo_item"]."',
				to_date('$c_fec_documento','dd/mm/yyyy') +current_time,
				'$c_almacen',
				'$origen',
				'$destino',
				'$natu',
				'$c_cod_cliente',
				".$A["cantidad_item"].",
				$costo_prom,
				$costo_prom,
				$costo_prom*".$A["cantidad_item"].",
				current_timestamp
				)";
				pg_exec($q);
				//echo $q."<br>";
			} 
			
		} // if de anticipado N
		echo "Avanzamos el correlativo del documento " ;
		pg_exec("select util_fn_corre_docs('$c_documento','$c_serie','insert')");
		
		echo "Destruimos los arrays de session" ;
		
		$ITEMS = $_SESSION["AR_ITEMS"];
		$ITEMS = null;
		$_SESSION["AR_ITEMS"] = null;
		unset($ITEMS);
		unset($AR_ITEMS);
		
		$COMP = $_SESSION["ARR_COMP"];
		$COMP = null;
		$_SESSION["ARR_COMP"] = null;
		unset($COMP);
		unset($ARR_COMP);
	
		//header("location: facturas-oficina_principal.php");
		//print "<script>location.href='';
		
		echo "FIN TRANSACCION DE PHP";				
        echo('<script languaje="JavaScript">');
    	echo("location.href='facturas-oficina_principal.php'; ");
    	echo('</script>');
		


	// break;
}

$ITEMS = $_SESSION["AR_ITEMS"];
$rsf = combo("series_documentos_sunat");

if($c_por_igv==""){
	$c_por_igv = por_igv();
}
if($c_cod_igv==""){
	$c_cod_igv = cod_igv();
}
?>
<script language="JavaScript" src="js/miguel.js"></script>
<!--<script language="JavaScript" src="js/validacion.js"></script>-->
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>

<script language="JavaScript">

	var series = new Array();
	<?php for($i=0;$i<pg_numrows($rsf);$i++){
	    $A = pg_fetch_array($rsf,$i);
        ?>
		var serie_<?php echo $i;?> = new Array();
		serie_<?php echo $i;?>[0] = "<?php echo $A["serie"];?>";
		serie_<?php echo $i;?>[1] = "<?php echo $A["desc_serie"];?>";
		serie_<?php echo $i;?>[2] = "<?php echo $A["cod_documento"];?>";
		serie_<?php echo $i;?>[3] = "<?php echo $A["numactual"];?>";
		series[<?php echo $i;?>] = serie_<?php echo $i;?>;
	<?php } ?>
	
	<?php $rsf=pg_exec("select substring(tab_elemento for 2 from length(tab_elemento)-1 ) as tipo_pago
	, tab_desc_breve as des_tipo_pago, trim(tab_tabla) ,cast(tab_num_01 as int) as dias
	from int_tabla_general 
	where trim(tab_tabla)='96' 
	and tab_elemento<>'000000' 
	union 
	select substring(tab_elemento for 2 from length(tab_elemento)-1 ) as tipo_pago
	, tab_desc_breve as des_tipo_pago, trim(tab_tabla) ,cast(tab_num_01 as int) as dias
	from int_tabla_general 
	where trim(tab_tabla)='05' 
	and tab_elemento<>'000000'
	");?>
	var formas_pago = new Array();
	
	<?php for($i=0;$i<pg_numrows($rsf);$i++){
		$A = pg_fetch_array($rsf,$i);
	?>
			var forma_<?php echo $i;?> = new Array();
			forma_<?php echo $i;?>[0] = "<?php echo $A["tipo_pago"];?>";
			forma_<?php echo $i;?>[1] = "<?php echo $A["des_tipo_pago"];?>";
			forma_<?php echo $i;?>[2] = "<?php echo $A[2];?>";
			forma_<?php echo $i;?>[3] = "<?php echo $A["dias"];?>";
			formas_pago[<?php echo $i;?>] = forma_<?php echo $i;?>;
	<?php } ?>
	
	<?php $rsf = combo("descuentos");?>
	var descuentos = new Array();
	<?php for($i=0;$i<pg_numrows($rsf);$i++){
		$A = pg_fetch_array($rsf,$i);
	?>
		descuentos[<?php echo $A["cod_descuento"];?>] = '<?php echo $A["por_descuento"];?>';		
	
	<?php } ?>
	
function asignar_series(form,c_serie){
	
    if(form.cerrado.value!="si"){
	removeAllOptions(c_serie) ;
	var doc = form.c_documento.value;
	for(i=0;i<series.length;i++){	
    
	    var fila = series[i];
	    if(fila[2]==doc){
		var opcion = document.createElement("OPTION");	
		if(navigator.appName=="Microsoft Internet Explorer"){	
		c_serie.options.add(opcion);
		}
		opcion.value = fila[0];
		opcion.innerText = fila[1];
		if(navigator.appName!="Microsoft Internet Explorer"){	
		c_serie.options.add(opcion);
		}
	    }
	}
	asignarNumDocumento(form);
    }
}

function asignarDiasdePago(form){
	var forma = form.c_forma_pago.value;
	var credito = form.c_tipo_credito.value;
	if(credito=='S'){
		credito = '96';
	}
	if(credito=='N'){
		credito = '05';
	}	
		
	for(i=0;i<formas_pago.length;i++){
		
		var fila = formas_pago[i];
		if(forma==fila[0] && credito==fila[2]){
			//alert("Dias "+fila[3]);
			form.c_dias_pago.value=fila[3];
			break;
		}	
			
	}
	
}

function asignarNumDocumento(form){
	if(form.cerrado.value!="si"){
		//alert("asignarNumDocumento form.cerrado.value "+form.cerrado.value);	
		var doc = form.c_documento.value;
		var serie = form.c_serie.value;
		//alert("Documento: "+doc+" Serie :"+serie);
		for(i=0;i<series.length;i++){	
			var fila = series[i];
			if(fila[2]==doc && fila[0]==serie){
				form.c_num_documento.value = fila[3];
			}
		}
		form.c_lb_serie.value=serie;
	}
}

function hasOptions(obj) {
if (obj!=null && obj.options!=null) { return true; }
return false;
}

function removeAllOptions(combo) { 
if (!hasOptions(combo)) { return; }
for (var i=(combo.options.length-1); i>=0; i--) { 
combo.options[i] = null; 
} 
combo.selectedIndex = -1; 
}

function completarCampos(tipo,campo,form){
		fecha = form.c_fec_documento.value;
		url = "facturas_iframe.php?opcion="+tipo+"&campo="+campo+"&fecha="+fecha;
		//alert(url);
		if(tipo=="Articulos"){
			url = url+"&codigo="+form.c_art_codigo_item.value+"&lista_precio="+form.c_lista_precios.value;
		}
		
		ifr1.location=url;
		
} 

function difinirTiposPago(form,c_forma_pago){

	if(form.cerrado.value!="si"){
		
		removeAllOptions(c_forma_pago) ;
		var credito = form.c_tipo_credito.value;
		if(credito=='S'){
			credito = '96';
		}
		if(credito=='N'){
			credito = '05';
		}
		//alert("Documento: "+doc+" Serie :"+serie);
		for(i=0;i<formas_pago.length;i++){	
			var fila = formas_pago[i];
			if(fila[2]==credito){
				var opcion = document.createElement("OPTION");	
				if(navigator.appName=="Microsoft Internet Explorer"){
					c_forma_pago.options.add(opcion);
				}
				opcion.value = fila[0];
				opcion.innerText = fila[1];
				
				if(navigator.appName!="Microsoft Internet Explorer"){
					c_forma_pago.options.add(opcion);
				}
			
			}
		}
		asignarDiasdePago(form);
	}
}

function mostrarAyuda2(url,cod,des,consulta,des_campo,valor){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function mostrarAyuda3(url,cod,des,consulta){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"&cod="+cod+"&des="+des+"&consulta="+consulta;
//alert(url);
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function complemento(cod_cliente){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = "forms_popup/fac_complementarios.php?cod_cliente="+cod_cliente+"&accion=Completar";
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function enviarDatos(form,tipo){
		form.accion.value=tipo;
		
		if(tipo=="Agregar"){
			form.c_lb_serie.value=form.c_serie.options[form.c_serie.selectedIndex].text;
			form.c_lb_forma_pago.value=form.c_forma_pago.options[form.c_forma_pago.selectedIndex].text;
			form.cerrado.value="si";
		}
		if(tipo=="Cancelar"){
			form.cerrado.value="";
		}
	
		
		form.submit();
}

function pasarEntero(num){

	ret = parseInt(num);
	if(isNaN(ret)){
		//alert("Valor no permitido");
		ret = 0;
	}

return ret;
}

function pasarNumerico(num){

	ret = parseFloat(num);
	if(isNaN(ret)){
		//alert("Valor no permitido");
		ret = 0;
	}

return ret;
}

/*Esta function se utiliza para cuadrar los importes del item que se ingresa*/
function cuadrarItem(form){
if(form.c_art_codigo_item.value!=""){
	var precio = pasarNumerico(form.c_precio_item.value);
	var cantidad = pasarEntero(form.c_cantidad_item.value);
	var por_igv = pasarNumerico(form.c_por_igv.value);
	var por_descuento = pasarNumerico(form.c_por_descuento.value);
	
	var imponible = cantidad * precio;
	imponible = pasarNumerico(imponible.toFixed(2));
	
	var monto_descuento = imponible*por_descuento;
	monto_descuento = pasarNumerico(monto_descuento.toFixed(2)); 
	
	var monto_igv = (imponible-monto_descuento)*por_igv;
//	alert("Impo "+imponible+" igv"+monto_igv);
	monto_igv = pasarNumerico(monto_igv.toFixed(2)); 


	var total = imponible + monto_igv;
//	alert("Total "+total);
	total = pasarNumerico(total.toFixed(2));
	
	
	form.c_total_item.value=total;
	form.c_impuesto_item.value=monto_igv;
	form.c_importe_item.value=imponible;
	form.c_descuento_item.value=monto_descuento;
	
}

}

function asignarDescuento(form,cod_descuento){
//	form.c_por_impuesto.value=descuentos[cod_descuento];
form.c_por_descuento.value=descuentos[parseInt(cod_descuento.value)];
}

function verificarCabecera(form){

	if(form.c_fec_documento.value==""){
		alert("No se ha indicado fecha para el documento");
		form.c_fec_documento.focus();
	}else{
		if(form.c_tipo_cambio.value==""){	
			completarCampos('Tipo_cambio','form1.c_tipo_cambio',form);
		}
	}
	
	if(form.c_cod_cliente.value==""){
		alert("No se ha ingresado el codigo del cliente");
		form.c_cod_cliente.focus();
	}

}

</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="form1" action="" method="post">
  <table width="797" border="0" cellpadding="1">
    <tr> 
      <td colspan="2">ALMACEN : 
      <td colspan="5"><select name="c_almacen">
          <?php $rsf = combo("almacenes");
			for($i=0;$i<pg_numrows($rsf);$i++){
				$A = pg_fetch_array($rsf,$i);
				if($cerrado!="si"){
					print "<option value='".$A["cod"]."'>".$A["cod"]." ".$A["ch_nombre_almacen"]."</option>";
				}else{
					if($c_almacen==$A["cod"]){
						print "<option value='".$A["cod"]."'>".$A["cod"]." ".$A["ch_nombre_almacen"]."</option>";
					}	
				}	
			}
		?>
        </select> <input type="button" name="gg" value="Submit" onClick="javascript:asignar_series(form1,c_serie);"> 
        <input type="button" name="gg2" value="Submit" onClick="javascript:alert('Doc -'+c_documento.value+'- '+'Serie -'+c_serie.value+'- Forma de pago '+c_forma_pago.value);"> 
        <input type="button" name="gg3" value="Tipo de Pago" onClick="javascript:difinirTiposPago(form1,c_forma_pago);"> 
    <tr> 
      <td colspan="2">FECHA 
      <td colspan="5"><input type="text" size="14" maxlength="10" onKeyUp="validarFecha(this);" name="c_fec_documento" value="<?php echo $c_fec_documento;?>" > 
        <a href="javascript:show_calendar('form1.c_fec_documento');"><img src="../images/show-calendar.gif" width="24" height="22" border="0"></a> 
    <tr> 
      <td height="26" colspan="2" align="right">
<div align="left">TIPO DOC. : </div>
      <td width="232"><select name="c_documento" onChange="javascript:asignar_series(form1,c_serie);">
          <?php $rsf = combo("documentos_sunat");
		for($i=0;$i<pg_numrows($rsf);$i++){
		    $A = pg_fetch_array($rsf,$i);
		    if($cerrado!="si"){	
			    print "<option value='".$A["cod_documento"]."'>".$A["cod_documento"]." - ".$A["desc_documento"]."</option>";
		    }else{
			if($c_documento==$A["cod_documento"]){
				print "<option value='".$A["cod_documento"]."'>".$A["cod_documento"]." - ".$A["desc_documento"]."</option>";				
			}
		    }
		}
	    ?>
        </select> 
      <td width="147">SERIE 
        <input size="4" type="text" name="c_lb_serie" value="<?php echo $c_lb_serie;?>"> 
      <td colspan="3">: 
        <select name="c_serie" onChange="javascript:asignarNumDocumento(form1);">
        <?php if($cerrado=="si"){
	   print "<option value='$c_serie'>$c_lb_serie</option>";
	}?>
	</select> 
    <tr> 
      <td width="98" align="left">&nbsp; 
      <td width="1">&nbsp; 
      <td>&nbsp; 
      <td>NUMERO 
      <td colspan="3">: 
        <input  type="text" name="c_num_documento" size="12"  readonly="true" value="<?php echo $c_num_documento; ?>"> 
    <tr> 
      <td colspan="2">MONEDA : 
      <td><select name="c_moneda">
          <?php $rsf = combo("monedas");
			for($i=0;$i<pg_numrows($rsf);$i++){
				$A = pg_fetch_array($rsf,$i);
				if($cerrado!="si"){
					if($A[0]!="000000"){print "<option value='".$A[0]."'>".$A[0]." - ".$A[1]."</option>";}
				}else{
					if($c_moneda==$A[0]){
						if($A[0]!="000000"){print "<option value='".$A[0]."'>".$A[0]." - ".$A[1]."</option>";}
					}
				}
			}
		?>
        </select> 
      <td>TIPO CAMBIO 
      <td width="316">: 
        <input  type="text" name="c_tipo_cambio" size="6" maxlength="6" value="<?php echo $c_tipo_cambio;?>" onFocus="javascript:completarCampos('Tipo_cambio','form1.c_tipo_cambio',form1);"> 
    <tr> 
      <td colspan="2">CREDITO : 
      <td><select name="c_tipo_credito" onChange="javascript:difinirTiposPago(form1,c_forma_pago);">
        <?php if($cerrado!="si"){	  
		  print "<option value='N'>NO</option>";
          print "<option value='S'>SI</option>";
		  }else{
		  		if($c_tipo_credito=="S"){print "<option value='S'>SI</option>";}
				if($c_tipo_credito=="N"){print "<option value='N'>NO</option>";}
		  }
        ?>
		</select> 
      <td>FORMA PAGO 
        <input size="7" type="text" name="c_lb_forma_pago" value="<?php echo $c_lb_forma_pago;?>"> 
      <td>: 
        <select name="c_forma_pago" onChange="javascript:asignarDiasdePago(form1);">
		<?php if($cerrado=="si"){
			print "<option value='$c_forma_pago'>$c_lb_forma_pago</option>";
		}?>
        </select> 
    <tr> 
      <td colspan="2">CLIENTE : 
      <td>
          <input type="text" size="40" maxlength="10" name="c_des_cliente" value="<?php echo $c_des_cliente;?>" >
        <img src="../images/help.gif" width="16" height="16" onClick="javascript:mostrarAyuda2('/sistemaweb/inventarios/js/lista_ayuda.php','c_cod_cliente','descliente','clientes','c_des_cliente');" onMouseOver="this.style.cursor='hand'"> 
      <td>LISTA DE PRECIOS
      <td>: 
        <select name="c_lista_precios">
          <?php $rsf = combo("lista_precios");
			for($i=0;$i<pg_numrows($rsf);$i++){
				$A = pg_fetch_array($rsf,$i);
				if($cerrado!="si"){	
					print "<option value='".$A["cod_lista"]."'>".$A["cod_lista"]." - ".$A["des_lista"]."</option>";
				}else{
					if($c_lista_precios==$A["cod_lista"]){
						print "<option value='".$A["cod_lista"]."'>".$A["cod_lista"]." - ".$A["des_lista"]."</option>";
					}
				}
			}
		?>
        </select> <tr>
      <td colspan="2">ANTICIPADO: 
      <td><select name="c_anticipado" >
      	 <?php if($cerrado!="si"){    
		  	print "<option value='N'>NO</option>";
          	print "<option value='S'>SI</option>";
		  }else{
		  	if($c_anticipado=="S"){print "<option value='S'>SI</option>";}
			if($c_anticipado=="N"){print "<option value='N'>NO</option>";}
		  }
      	?>
	    </select>
      
	  <td>DESCUENTO 
      <td>:
<select name="c_descuento" onChange="javascript:asignarDescuento(form1,this),cuadrarItem(form1);">
          <?php $rsf = combo("descuentos");
			for($i=0;$i<pg_numrows($rsf);$i++){
				$A = pg_fetch_array($rsf,$i);	
				print "<option value='".$A["cod_descuento"]."'>".$A["des_descuento"]."</option>";				
			}
		?>
        </select>
        <input  type="text" name="c_por_descuento" size="6" maxlength="6" > 
    <tr> 
      <td height="45" colspan="3">
		<input type="hidden" name="c_cod_cliente" value="<?php echo $c_cod_cliente;?>">
        <input type="hidden" name="descliente">
        <input type="hidden" name="art_stock">
        <input type="hidden" name="art_costo_uni">
        <input type="hidden" name="cerrado" value="<?php echo $cerrado;?>">
        <input type="hidden" name="accion" value="<?php echo $accion;?>">
        <input type="hidden" name="c_por_igv" value="<?php echo $c_por_igv;?>">
        <input type="hidden" name="c_cod_igv" value="<?php echo $c_cod_igv;?>">
        <input size="5" type="text" name="c_dias_pago" value="<?php echo $c_cod_igv;?>"><td>Datos Complementarios <br>
        y Observaciones 
      <td><br>
        <input type="button" name="gg22" value="Complementarios" onClick="javascript:complemento(c_cod_cliente.value);"> 
        <br>
        <input type="button" name="gg222" value="Cancelar" onClick="javascript:enviarDatos(form1,'Cancelar');"> 
    <tr> 
      <td colspan="7"><hr> 
  </table>

  <table width="792" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <th width="167">ARTICULO</th>
      <th width="225">DESCRIPCION</th>
      <th width="48">CANT.</th>
      <th width="60">PRECIO</th>
      <th width="44">NETO</th>
      <th width="50">IGV</th>
      <th width="50">DESC.</th>
      <th width="53">TOTAL</th>
    </tr>
    <tr> 
      <td><input type="text" name="c_art_codigo_item" size="18" maxlength="13"  onFocus="javascript:verificarCabecera(form1);"> 
        <img src="/sistemaweb/images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda3('/sistemaweb/maestros/ayuda/lista_ayuda-fac-oficina.php?lista_precio='+form1.c_lista_precios.value,'form1.c_art_codigo_item','form1.c_des_articulo_item','articulos3')"> 
      <td><input type="text" name="c_des_articulo_item" size="45" readonly="true" onFocus="javascript:completarCampos('Articulos','form1.c_art_codigo_item',form1);"></td>
      <th><input name="c_cantidad_item" type="text" size="7" maxlength="6" onkeyup="cuadrarItem(form1);"></th>
      <th><input name="c_precio_item" type="text" size='10' maxlength="9"> </th>
      <th><input readonly name="c_importe_item" type="text" size='8' maxlength="6" ></th>
      <th><input readonly name="c_impuesto_item" type="text" size='8' maxlength="6" ></th>
      <th><input name="c_descuento_item" type="text" size='10'  maxlength="10"></th>
      <th><input name="c_total_item" type="text" size='10'  maxlength="10" ></th>
      <th width="75"><input type="button" name="boton" value="Agregar" onClick="javascript:enviarDatos(form1,'Agregar');"></th>
    <tr> 
      <td>&nbsp; 
      <td>&nbsp;</td>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th><input type="button" name="boton2" value="Eliminar" onClick="javascript:enviarDatos(form1,'Eliminar');"></th>
    </tr>
    <?php for($i=0;$i<count($ITEMS);$i++){
		$A = $ITEMS[$i];
	?>
    <tr> 
      <td><?php echo $A["art_codigo_item"];?> 
      <td><?php echo $A["des_articulo_item"];?></td>
      <th><?php echo $A["cantidad_item"];?></th>
      <th><?php echo $A["precio_item"];?></th>
      <th><?php echo $A["importe_item"];?></th>
      <th><?php echo $A["impuesto_item"];?></th>
      <th><?php echo $A["descuento_item"];?></th>
      <th><?php echo $A["total_item"];?></th>
      <th><input type="checkbox" name="ar_items[]" value="<?php echo $i;?>"></th>
    </tr>
    <?php $total_importe = $total_importe + $A["importe_item"];
	  $total_igv = $total_igv + $A["impuesto_item"];
	  $total_descuento = $total_descuento + $A["descuento_item"];
	  $total_total = $total_total + $A["total_item"];
	
	}?>
    <tr> 
      <td height="21">&nbsp; 
      <td>&nbsp;</td>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th colspan="2"><div align="left"></div></th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
    </tr>
    <tr> 
      <td height="21">&nbsp;
      <td>&nbsp;</td>
      <th colspan="2"><strong>TOTALES</strong></th>
      <th><input readonly="true" name="c_total_importe" value="<?php echo $total_importe;?>" type="text" size='8'  maxlength="8"   ></th>
      <th><div align="left"> 
          <input readonly="true" name="c_total_igv" value="<?php echo $total_igv;?>" type="text" size='8'  maxlength="8"  >
        </div></th>
      <th><input readonly="true" name="c_total_descuento" value="<?php echo $total_descuento;?>" type="text" size='8'  maxlength="8" ></th>
      <th><input readonly="true" name="c_total_total" value="<?php echo $total_total;?>" type="text" size='8'  maxlength="8" ></th>
      <th>&nbsp;</th>
    </tr>
    <tr> 
      <td height="22">&nbsp; 
      <td>&nbsp;</td>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th colspan="2"><div align="left"></div></th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
    </tr>
    <tr> 
      <td height="22">&nbsp; 
      <td>&nbsp;</td>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th colspan="2"><div align="left"></div></th>
      <th>&nbsp;</th>
      <th><input type="button" name="boton22" value="Terminar" onClick="javascript:enviarDatos(form1,'Terminar');"></th>
    </tr>
  </table>
</form>
<iframe name="ifr1" width="5" height="5"></iframe>
<script language="JavaScript">
	asignar_series(document.form1,document.form1.c_serie); 
	difinirTiposPago(document.form1,document.form1.c_forma_pago);
</script>

</body>
</html>
<?php
pg_close($conector_id);
