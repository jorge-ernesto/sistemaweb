<?php
include("config.php");
include("inv_enlace_cpagar_support.php");



//IMPUESTOS 
$rsi1 = combo("impuestos");
//RUBROS
$rs_rubrosinv = pg_exec("SELECT TRIM(tab_elemento),tab_descripcion,trim(tab_car_01) from int_tabla_general where tab_tabla='RCPG' and tab_elemento <>'000000'");
//DOCUMENTOS SUNAT
$rs_ds = pg_exec("select trim(tab_elemento),'ok',tab_descripcion from int_tabla_general
				where tab_tabla='08' and tab_elemento<>'000000'
				and tab_car_03 is not null ");
//echo "tasa ".$tasa_cambio;
switch($accion){
	case "Ingresar":
		//estan todas las variable menos descrip_moneda
		$ncredito=$_REQUEST['insnc'];
		insertarCpag($fecha_doc,$fecha_reg,$des_proveedor,$cod_proveedor,$des_rubro,$cod_rubro
		,$des_documento,$cod_documento,$num_documento,$des_docurefe,$cod_docurefe,$num_docurefe
		,$fecha_ven,$des_moneda,$cod_moneda,$tasa_cambio,$imp_total,$monto_imp,$igv,$serie_doc
		,$c_montos_varios,$almacen_interno_ing_inv,$percepcion,$ncredito);
		break;
	exit();
	case "cambiarTipoCambio":
		//echo "el fecha_doc--".$fecha_doc;
		//$TPC = tipoCambio($fecha_doc,$cod_moneda,$descrip_moneda);
		$rs_tp = pg_exec(" select tca_compra_oficial from int_tipo_cambio where tca_fecha=to_date('$fecha_doc','dd/mm/yyyy') ");
		//$tasa_cambio 	= $TPC["venta_banco"];
		$tasa_cambio = pg_result($rs_tp,0,0);
		$opt_tipocambio	= "<option value='$cod_moneda'>$descrip_moneda</option>";
		 
//		$monto_imp		=	$monto_imp/$tasa_cambio;	
		$monto_imp		=	$monto_imp_ori/$tasa_cambio;
//		$monto_imp_ori	=	
		$imp_total		=	(1+($por_igv)) * $monto_imp;	
		$igv			=	$por_igv * $monto_imp;
		
		$monto_imp = round($monto_imp,2);
		$imp_total = round($imp_total,2);
		$igv = round($igv,2);
		
		break;
}

$R = inicializarFormulario($CP);

		if(trim($cod_documento)==""){
		$cod_documento 		= $R["cod_documento"] ;
		}
		$num_documento		= $R["num_documento"] ;
		$cod_docurefe		= $R["cod_docurefe"] ;
		$num_docurefe		= $R["num_docurefe"] ;
		$monto_imp		= $R["monto_imp"] ;
		$des_proveedor		= $R["des_proveedor"] ;
		$des_documento		= $R["des_documento"] ;
		$cod_proveedor		= $R["cod_proveedor"];
		$serie_doc		= $R["serie_doc"] ;
		
		//*PARA LAS FECHAS LA PRIMERA VEZ*//

		if($fecha_doc==""){$fecha_doc=$hoy;}
		if($fecha_reg==""){$fecha_reg=$hoy;}
		if($fecha_ven==""){$fecha_ven=$hoy;}
		if($tasa_cambio==""){$tasa_cambio=1;}
		if($monto_imp_ori==""){$monto_imp_ori=$monto_imp;}
				
	
	$rs_a = pg_exec(" select trim(tab_elemento) as codigo,tab_descripcion as desc_doc from int_tabla_general 
			where tab_tabla='08' and tab_elemento<>'000000'
			 and trim(tab_elemento)=lpad(trim('$codigo'),6,'0')
			 and tab_car_03 is not null ");
			#$A_a = pg_fetch_array($rs_a,0);
	if(pg_numrows($rs_a)==0){
		$mostrar_ayuda_doc = true;
	}else{$mostrar_ayuda_doc = false;}
	
	

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>
var por_impuestos = new Array();
	<?php
		for($i=0;$i<pg_numrows($rsi1);$i++){
			$A = pg_fetch_array($rsi1,$i);
			print "por_impuestos['".$A[2]."'] = '".$A[0]."';";
		}
	?>
	var rubrosinv = new Array();	
	<?php
		for($i=0;$i<pg_numrows($rs_rubrosinv);$i++){
			$A = pg_fetch_array($rs_rubrosinv,$i);
			print "rubrosinv['".$A[0]."'] = '".$A[2]."';";
		}
	?>

	var docs_sunat = new Array();	
	<?php
		for($i=0;$i<pg_numrows($rs_ds);$i++){
			$A = pg_fetch_array($rs_ds,$i);
			print "docs_sunat['".$A[0]."'] = '".$A[1]."';";
		}
	?>

function mostrarAyuda(url,cod,des,consulta,des_campo){
//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo;
window.open(url,'miwin2','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function enviarDatos(form,ope){
	
		var pasa = true;
		var int_igv = parseFloat(form.igv.value);
		var int_monto_imp = parseFloat(form.monto_imp.value);
		var imponible = parseFloat(form.monto_imp.value);
		imponible = imponible.toFixed(4);
		imponible = parseFloat(imponible);
		var impuesto = parseFloat(form.igv.value);
		impuesto = impuesto.toFixed(4);
		impuesto = parseFloat(impuesto);
		var tasa = numerico(form.tasa_cambio.value);
		
		var total = parseFloat(form.monto_imp.value);
		var imponible = numerico(form.monto_imp.value);
		var imp_total = parseFloat(form.imp_total.value);
		imp_total = parseFloat(imp_total);
		var monto_imp_ori = numerico(form.monto_imp_ori.value);
		var varios = numerico(form.c_montos_varios.value);
		
	if(ope=='Ingresar'){
		
		if(form.cod_rubro.value==""){
			pasa = false;
			alert('Falta Ingresar el Rubro');
		}
		if(form.cod_moneda.value==""){
			pasa = false;
			alert('Falta seleccionar la moneda');
		}
		if(form.tasa_cambio.value=="" ){
			pasa = false;
			alert('Falta ingresar la Tasa de Cambio');
		}
		if(int_igv>int_monto_imp){
			pasa = false;
			alert('El igv es mayor que el monto imponible');
		}
		if(form.cod_documento.value==""){
			pasa = false;
			alert('Codigo de Documento incorrecto');
		}
		if(form.tasa_cambio.value==""){
			pasa = false;
			alert("Debes indicar el tipo de cambio");
		}
		
		if(pasa){
			if(form.cod_moneda.value!="01" && tasa==1.0){
				pasa = false;
				alert("ERROR: Monedas diferentes del sol no pueden tener tipo de cambio 1");
			}
		}
		
		if(pasa){
			/*if(form.cod_moneda.value=="01" && tasa!=1.0 ){
				pasa = false;
				alert("ERROR: La moneda Soles debe tener tipo de cambio 1");
			}*/
		}
		
		if(pasa){
			if(docs_sunat[form.cod_documento.value]!="ok"){
				pasa = false;
				alert("ERROR: Tipo de Documento Incorrecto !!!");
				form.des_documento.value='';
				form.cod_documento.value='';
			}
		}
		
		if(pasa){
			var tasa2 = numerico(form.tasa_cambio.value);
			if(form.cod_moneda.value=='01'){tasa2=1;}
			var dife = numerico(imponible*tasa2) + numerico(varios*tasa2) - numerico(monto_imp_ori);
			dife = numerico(dife);
			dife = Math.abs(dife);
			//alert(dife);
			if(dife>=1){
				pasa = false;
				alert('Solo se puede redondear hasta con un sol de diferencia DIF: '+dife);
				//alert("Impuesto "+impuesto+"<br>imponible "+imponible+"<br>"+"<br> imptotal "+imp_total);
			}
			
			/*if(form.descrip_moneda.value!="Soles" && form.tasa_cambio.value=="1"){
				pasa = false;
				alert("Debes indicar Tipo de cambio diferente de 1 para monedas distintas de soles");
			}*/
			
			if(form.cod_moneda.value!="01" && form.tasa_cambio.value=="1"){
				pasa = false;
				alert("Debes indicar Tipo de cambio diferente de 1 para monedas distintas de soles");
			}
			//alert('Dif '+dife);
		}
		
	} //FIN DEL IF PARA INGRESAR
	if(form.fecha_doc.value==''){
		pasa = false;
		alert('Falta ingresar la fecha del documento');
	}

if(pasa){
		form.accion.value = ope;
		//recalcularMontos(form);
		form.submit();
}

}

/////
function calcularMontos2(form,opcion){
	
	var enlaza_inventarios = true;
	var tasa = numerico(form.tasa_cambio.value);
	if(tasa==0 || form.cod_moneda.value=="01"){ tasa=1; }
	if(rubrosinv[form.cod_rubro.value]==""){enlaza_inventarios=false;}
		
	if(opcion=="total"){
		
		//var tasa_i1 = numerico(por_impuestos[form.impuesto1.options[form.impuesto1.selectedIndex].value]);
		var total = numerico(form.imp_total.value);
		var importe = numerico(form.monto_imp.value);
		var monto_imp1 = numerico(form.igv.value);
		var monto_imp2 = numerico("0.0");
		var monto_imp3 = numerico("0.0");
		
		form.monto_imp.value = importe.toFixed(2); //importe
		form.igv.value = monto_imp1.toFixed(2); //impuesto 1
		form.c_montos_varios.value = numerico(total-(importe+monto_imp1+monto_imp2+monto_imp3));
	}
	
	if(opcion=="importe"){
		
		var tasa_i1 = numerico(form.por_igv.options[form.por_igv.selectedIndex].value);
		var tasa_i2 = numerico("0.0");
		var tasa_i3 = numerico("0.0");

		var importe = numerico(form.monto_imp.value);
		var total = numerico(form.imp_total.value);
		var monto_imp1 = numerico(importe*tasa_i1);
		var monto_imp2 = numerico(importe*tasa_i2);
		var monto_imp3 = numerico(importe*tasa_i3);
				
		//form.importe_total.value = total.toFixed(2); //importe
		form.igv.value = monto_imp1.toFixed(2); //impuesto 1
		//form.monto_imp2.value = monto_imp2.toFixed(2); //impuesto 2
		//form.monto_imp3.value = monto_imp3.toFixed(2); //impuesto 3
		form.c_montos_varios.value = numerico(total-(importe+monto_imp1+monto_imp2+monto_imp3));		
	}
	
	if(opcion=="impuestos"){
	
		var tasa_i1 = numerico(por_impuestos[form.por_igv.options[form.por_igv.selectedIndex].value]);
		var tasa_i2 = numerico("0.0");
		var tasa_i3 = numerico("0.0");

		var importe = numerico(form.monto_imp.value);
		var total = numerico(form.imp_total.value);
		var monto_imp1 = numerico(form.igv.value);
		var monto_imp2 = numerico("0.0");
		var monto_imp3 = numerico("0.0");
		
		form.c_montos_varios.value = numerico(total-(importe+monto_imp1+monto_imp2+monto_imp3));
	//alert("total "+total+" importe"+importe+" imp 1 "+monto_imp1+" tasa 1 "+tasa_i1 );
	}
	
}

/////

function pasarTexto(combo, campo){
	var ind = combo.selectedIndex;
	var des = combo.options[ind].text;
	campo.value = des;
}
/*Calculadora para los importes segun el tipo de cambio*/
function recalcularMontos(form){

	var tasa_cambio		= numerico(form.tasa_cambio.value);
	if(tasa_cambio==0 || form.cod_moneda.value=="01"){ tasa_cambio=1; }	
	var monto_imp		= form1.monto_imp.value;
	var monto_imp_ori	= parseFloat(form1.monto_imp_ori.value);				
	var por_igv			= form1.por_igv.value;
	por_igv = parseFloat(por_igv);
	var imp_total = ( (1+por_igv) * monto_imp_ori ) / tasa_cambio;
	var igv		  = ( por_igv * monto_imp_ori ) / tasa_cambio;
	
	//alert(por_igv+1);
	var a = parseFloat(132.3456);
	var b = Math.floor(a.toPrecision(4));
	//alert(a.toFixed(3));

	form.imp_total.value	= imp_total.toFixed(2);
	form.igv.value 		= igv.toFixed(2);
	form.monto_imp.value	= (imp_total-igv).toFixed(2);
	
	calcularMontos2(form,'importe');
	calcularMontos2(form,'impuestos');
	
}

function chiches(ifr,chiche,codigo,campo,campo_codigo){		
			var url = "inv_enlace_cpagar_iframe.php?chiche="+chiche+"&codigo="+codigo+"&campo="+campo+"&campo_codigo="+campo_codigo;
			ifr.location = url;
}

function validarDocumentoSunat(ifr,cod_documento){
	chiches(ifr,"Documentos_Sunat",cod_documento,"des_documento","cod_documento")
}

function numerico(numero){

	var ret = parseFloat(numero);
	if(isNaN(ret)){ ret = parseFloat("0.00"); }
	ret = ret.toFixed(2);
	
	return parseFloat(ret);
}
</script>
<title>Enlazar con Cuentas por Cobrar</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../sistemaweb.css" rel="stylesheet" type="text/css">
</head>

<body>
		
<div align="center"> Registro de Compras <br>
  <form name="form1" method="post">
<input  type="hidden" name="insnc" value="<?php echo $ncredito=$_REQUEST['nc'];?>"/>
    <?php echo "Almacen ".$almacen_interno_ing_inv;?> 
    <input name="almacen_interno_ing_inv" type="hidden" id="almacen_interno_ing_inv" value="<?php echo $almacen_interno_ing_inv;?>">
    <br><!--<input type="button" onClick="javascript:alert(docs_sunat[cod_documento.value]);">-->

<?php 
	$femision = substr($femision,8,2)."/".substr($femision,5,2)."/".substr($femision,0,4);
?>

    <?php echo "Fecha Emision: ".$femision;?> 
    <!--<input name="femision" type="hidden" id="femision" value="<?php echo $femision;?>">
    <br><!--<input type="button" onClick="javascript:alert(docs_sunat[cod_documento.value]);">-->

    <table width="637" border="1">
      <tr> 
        <td width="130">Fecha de Documento</td>
        <td width="157"><div align="left"> <?php //antes $fecha_doc ?>
            <input name="fecha_doc" type="text" id="fecha_doc" readonly="yes" value="<?php echo $femision;?>" onBlur="javascript:chiches(iframe1,'Fecha_Documento',fecha_doc.value,'fecha_doc','fecha_doc');">
          </div></td>
        <td width="166">Fecha de Registro</td>
        <td width="156"><input name="fecha_reg" type="text" id="fecha_reg" readonly="yes" value="<?php echo $fecha_reg;?>"></td>
      </tr>
      <tr> 
        <td>Proveedor</td>
        <td colspan="3"><input readonly="true" name="des_proveedor" type="text" size="60" value="<?php echo $cod_proveedor.' - '.$des_proveedor;?>" onFocus="javascript:chiches(iframe1,'Fecha_Documento',fecha_doc.value,'fecha_doc','fecha_doc');"> 
          <!-- <img onMouseOver="this.style.cursor='hand'" src="../../images/help.gif" width="16" height="15" border="0" onClick="mostrarAyuda('lista_ayuda.php','cod_proveedor','des_proveedor','proveedores','des_proveedor_campo')"></img>-->
          <input name="cod_proveedor" type="hidden" id="cod_proveedor" value="<?php echo $cod_proveedor;?>"> 
          <?php if($des_proveedor_campo==""){$des_proveedor_campo = $des_proveedor;} ?>
          <input type="hidden" name="des_proveedor_campo" value="<?php echo $des_proveedor_campo;?>"></td>
      </tr>
      <tr> 
        <td>Rubro</td>
        <td><input name="des_rubro" type="text" size="14" value="<?php echo $des_rubro;?>" onFocus="javascript:chiches(iframe1,'Fecha_Documento',fecha_doc.value,'fecha_doc','fecha_doc');">
          <img onMouseOver="this.style.cursor='pointer'" src="../../images/help.gif" width="16" height="15" border="0" onClick="mostrarAyuda('lista_ayuda.php','des_rubro','descripcion_rubro','rubros_cp','des_rubro_campo');"> </img> 
        </td>
        <td id="descripcion_rubro"><?php echo $des_rubro_campo;?></td>
        <td><input name="cod_rubro" type="hidden" value="<?php echo $cod_rubro;?>">
          <input name="des_rubro_campo" type="hidden" value="<?php echo $des_rubro_campo;?>">
		</td>
      </tr>
      <tr> 
        <td>Codigo de Documento</td>
        <td><input readonly="true" name="des_documento" type="text" value="<?php echo $cod_documento.'-'.$des_documento;?>" onFocus="javascript:chiches(iframe1,'Rubros',des_rubro.value,'des_rubro','cod_rubro');">
        	<?php if(mostrar_ayuda_doc){ ?>  
		  <img onMouseOver="this.style.cursor='hand'" src="../../images/help.gif" width="16" height="15" border="0" onClick="mostrarAyuda('lista_ayuda.php','cod_documento','des_documento','documentos_sunat','des_documento')"></td>
        <?php } ?>
		<td rowspan="0" colspan="0"><!-- <img src="../../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('lista_ayuda.php','cod_documento','des_documento','documentos','des_documento');"> -->
          <input name="cod_documento" type="hidden" id="cod_documento" value="<?php echo trim($cod_documento);?>">
          Nro Documento Serie 
          <input readonly="true" name="serie_doc" type="text" id="serie_documento" value="<?php echo $serie_doc;?>" size="3" maxlength="3"> 
          <div align="right"></div></td>
        <td><input readonly="true" name="num_documento" type="text" id="num_documento" value="<?php echo $num_documento;?>"> 
          <input type="hidden" name="serie_doc" value="<?php echo $serie_doc;?>"></td>
      </tr>
      <tr> 
        <td>Documento de Referencia</td>
        <td><input  readonly="true" name="des_docurefe" type="text" id="des_docurefe" value="<?php echo $cod_docurefe;?>"></td>
        <td><input name="cod_docurefe" type="hidden" id="cod_docurefe" value="<?php echo $cod_docurefe;?>">
          Nro Referencia</td>
        <td><input readonly="true" name="num_docurefe" type="text" id="num_docurefe" value="<?php echo $num_docurefe;?>"></td>
      </tr>
      <tr> 
        <td>Fecha de Vencimiento</td>
        <td><input name="fecha_ven" type="text" id="fecha_ven" value="<?php echo $fecha_ven;?>"></td>
        <td>Moneda</td>
        <td><select name="des_moneda" onChange="cod_moneda.value = this.value , pasarTexto(this,descrip_moneda) , recalcularMontos(form1),enviarDatos(form1,'cambiarTipoCambio');">
            <?php echo $opt_tipocambio;?> 
            <?php $rs = combo("monedas");
				for($i=0;$i<pg_numrows($rs);$i++){
					$A = pg_fetch_array($rs,$i);
					print "<option value='".trim($A[0])."'>$A[1]</option>";
				}
			?>
			
			<?php if($cod_moneda==""){$cod_moneda="01";} ?>
          </select> <input name="cod_moneda" type="hidden" id="cod_moneda" value="<?php echo $cod_moneda;?>"> 
          <input type="hidden" name="descrip_moneda" value="<?php echo $descrip_moneda;?>"></td>
      </tr>
      <tr> 
        <td height="23">Tasa de Cambio</td>
        <td> <input name="tasa_cambio" type="text" value="<?php echo $tasa_cambio;?>" onKeyUp="javascript:recalcularMontos(form1);"></td>
        <td><!--<img src="../../images/calculadora.png" width="25" height="25" onClick="recalcularMontos(form1);" onMouseOver="this.style.cursor='hand'">--></td>
        <td> Monto de Inventarios&gt; 
          <input type="text" name="monto_imp_ori" value="<?php echo $monto_imp_ori;?>" size="7" readonly="true">
          (Soles) </td>
      </tr>
      <tr> 
        <td>Importe Total</td>
        <td><input name="imp_total" type="text" id="imp_total" value="<?php echo $imp_total;?>" onKeyUp="javascript:calcularMontos2(form1,'total')"></td>
        <td>Monto Imponible</td>
        <td><input name="monto_imp" type="text" id="monto_imp" value="<?php echo $monto_imp;?>" onKeyUp="javascript:calcularMontos2(form1,'importe');">
        </td>
      </tr>
      <tr> 
        <td>Importe Impuesto</td>
        <td><input name="igv" type="text" id="igv" value="<?php echo $igv;?>" onKeyUp="javascript:calcularMontos2(form1,'impuestos')"></td>
        <td>IGV</td>
        <td> <select name="por_igv">
            <?php $rsigvs  = combo("impuestos");
			for($i=0;$i<pg_numrows($rsigvs);$i++){
				$A = pg_fetch_array($rsigvs,$i);
				print "<option value='$A[0]'>$A[0]</option>";
			}
			?>
          </select></td>
      </tr>
      <tr> 
        <td>Varios</td><?php if($c_montos_varios==""){$c_montos_varios=0;} ?>
        <td><input name="c_montos_varios" type="text" id="c_montos_varios" value="<?php echo $c_montos_varios;?>"></td>
        <td>Percepcion</td>
        <td><input name="percepcion" type="text" id="percepcion" value="<?php echo $perce;?>"></td>
      </tr>
      <tr> 
        <td><input name="art_costo_uni" type="hidden"> <input name="art_stock" type="hidden" id="art_stock"  value="" size="10"></td>
        <td colspan="2"><div align="center"> 
            <input type="button" name="btn_enviar" value="Ingresar" onClick="enviarDatos(form1,'Ingresar')">
            <input name="accion" type="hidden">
          </div></td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </form>
  
</div>
<?php pg_close(); ?>
<iframe name="iframe1" height="0" width="0"></iframe>
<?php
if($accion=="cambiarTipoCambio"){
	print "<script>document.form1.tasa_cambio.focus();</script>";
	print "<script>calcularMontos2(form1,'importe');</script>";
	
}
print "<script>recalcularMontos(form1);</script>";
?>
<script language="JavaScript">chiches(iframe1,'Fecha_Documento',document.form1.fecha_doc.value,'fecha_doc','fecha_doc');</script>
<script>validarDocumentoSunat(document.iframe1,document.form1.cod_documento.value)</script>
</body>
</html>
