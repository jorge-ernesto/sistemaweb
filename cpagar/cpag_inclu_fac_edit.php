<?php
//include("../menu_princ.php");
include("../valida_sess.php");
include_once("../include/dbsqlca.php");
include_once("config.php");
include_once("inc_top_cpagar.php");
include_once("cpag_inclu_fac_support.php");
include_once("cpag_ayuda_orddev_support.php");
$funcion = new class_funciones;
if(trim($num_registro)==""){$num_registro=0;}

$rs_rubrosinv = pg_exec("SELECT TRIM(tab_elemento),tab_descripcion,trim(tab_car_01) 
                         FROM int_tabla_general 
                         WHERE tab_tabla='RCPG' 
                         AND tab_elemento <>'000000'");

$rs_rubrosinv = pg_exec("SELECT trim(ch_codigo_rubro), ch_descripcion, trim(ch_tipo_item) 
                         FROM cpag_ta_rubros " );

						 

$para_limite_cpag=pg_result( pg_exec(" select par_valor from int_parametros where par_nombre='limite_cpag' limit 1") ,0,0) ;

switch($accion){
    case "Actualizar":
	$AR_DEV = $_SESSION["ITEMSCD_DEV"];
	$AR_ORD = $_SESSION["ITEMSCD"];
	//echo "<!--NORDEN : $AR_ORD -->";
	//este si inserta en los cpag_Cabecera  y detalle
	incluirFacturasEdicion($AR_ORD);
	
	//esta funcion solo actualiza en los com_cabecera y com_detalle y en compras_devoluciones
	incluirFacturas1($AR_ORD,$AR_DEV,$cod_documento,$serie_doc,$num_documento); 

	$_SESSION["ITEMSCD_DEV"]=null;
	$_SESSION["ITEMSCD"]=null;
	session_unregister("ITEMSCD_DEV");
	session_unregister("ITEMSCD");
	unset($ITEMSCD_DEV);
	unset($ITEMSCD);
	unset($cal);
	unset($_SESSION["ITEMSDEL"]);
	?>
	<script language="JavaScript">
	//location.href = 'cpag_inclu_fac_edit.php?accion=Cancelar';
	window.close();
	</script>
	<?php
    break;
	
    case "Cancelar" :
	$ITEMSCD_DEV = null;
	$ITEMSCD = null;
	$AR_DEV = null;
	$AR_ORD = null;
	$total_cmp = null;
	
	unset($ITEMSCD_DEV);
	unset($ITEMSCD);
	unset($AR_DEV);
	unset($AR_ORD);
	unset($total_cmp);
	unset($_SESSION["ITEMSDEL"]);

	$cal = "";
	?>
	<script language="JavaScript">
	 //location.href = 'cpag_doc_por_pagar.php';
	 window.close();
	</script>
	<?php
    break;
	
}

/*Inicializamos el Formulario esta comentado porque lo hago con javascript
cogiendo el valor del primer registro de la moneda para sacar su tipo de cambio y ponerlo en tasa cambio*/
	if($cod_moneda==""){$cod_moneda="01";}
	$tasa_cambio = tipoCambio($cod_moneda,$fec_doc);
	if($tasa_cambio==""){$tasa_cambio=1;}
/*Fin de Inicializacion del Formulario*/
$monto_imp = $monto_imp+0;

//PARA EL TIPO DE ALMACEN OFICINA O ESTACION
$tipoAlmacen = getTipoAlmacen($_SESSION["almacen"]);

//IMPUESTOS 
$rsi1 = combo("impuestos");
//IMPUESTOS

/*
$rs_rubrosinv = pg_exec("SELECT TRIM(tab_elemento),tab_descripcion,trim(tab_car_01) 
                         FROM int_tabla_general 
                         WHERE tab_tabla='RCPG' 
                         AND tab_elemento <>'000000'");
*/

?>
<html>
<head>
<title>Inclusion de Facturas</title>
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<link rel="stylesheet" href="/sistemaweb/css/formulario.css" type="text/css">

<script language="JavaScript" src="js/miguel.js"></script>

<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script language="JavaScript">
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
</script>
<script language="JavaScript">


function abreprov() {
    miPopup = window.open("../maestros/escogeproveedor.php?k_variable=form.proveedor","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
	
function mandarCombo(combo,form,iframe1){
    var index = combo.selectedIndex;
    form.cod_moneda_index.value = index;
    //chiche(iframe1,'Tasa_cambio',combo.value,'tasa_cambio','cod_moneda');
    chiche(iframe1,'Tasa_cambio',document.form1.fec_docu.value,'tasa_cambio','cod_moneda');
    //form.submit();
}
	
function calcularMontos(form){
		//alert('calcularMontos');
var enlaza_inventarios = true;

if(rubrosinv[form.cod_rubro.value]==""){enlaza_inventarios=false;}

    var cal = parseFloat(form.cal.value);
    if(!enlaza_inventarios){cal = numerico(form.monto_imp.value);}
    var imponible = parseFloat(form.monto_imp.value);
    var tasa_cambio = parseFloat(form.tasa_cambio.value);
    if(isNaN(imponible)){imponible = 0;}
    if(isNaN(tasa_cambio)){tasa_cambio = 1;}
    
    if(form.cod_moneda.value=="01"){tasa_cambio=1;}
    /*Igualamos el imponible*/
    var monto_imp = (cal/tasa_cambio);		
    //var monto_imp = cal;
    form.monto_imp.value = monto_imp.toFixed(2);
    //form.monto_imp.value = parseFloat(monto_imp);
    /*Necesitamos los impuestos coregidos*/
    
    importeImpuesto(form.monto_imp1,form.impuesto1,form.monto_imp);
    importeImpuesto(form.monto_imp2,form.impuesto2,form.monto_imp);
    importeImpuesto(form.monto_imp3,form.impuesto3,form.monto_imp);

    /*Con los impuestos corregidos reemplazamos el monto total*/

    var imp1 = parseFloat(form.monto_imp1.value);
    var imp2 = parseFloat(form.monto_imp2.value);
    var imp3 = parseFloat(form.monto_imp3.value);
    //alert(imp1+10);

    form.importe_total.value = (monto_imp+imp1+imp2+imp3).toFixed(2);

    calcularMontos2(form,"total");

}

//calcularMontos2
function calcularMontos2(form,opcion){
	
    var enlaza_inventarios = true;
    /*-----Percepción-----*/
    objGroupPer = document.getElementById('percepcion').style;
    objpercepcion = document.getElementsByName('percepcion')[0];
    montopercepcion = document.getElementsByName('mnt_apli_percepcion')[0].value;
    /*-------------------*/
    
    /*-----Detracción-----*/
    objGroupDet = document.getElementById('detraccion').style;
    objdetraccion = document.getElementsByName('detraccion')[0];
    detraccion_tipo = document.getElementsByName('detraccion_tipo')[0].value;
    montodetraccion = document.getElementsByName('mnt_apli_detraccion')[0].value;
    /*--------------------*/
    
    /*-----Retención-----*/
    objGroupRet = document.getElementById('retencion').style;
    objretencion = document.getElementsByName('retencion')[0];
    montoretencion = document.getElementsByName('mnt_apli_retencion')[0].value;
    /*--------------------*/
    
    /*-----Importe Final de acuerdo al caso aplicado-----*/
    importefinal = document.getElementsByName('importe_final')[0];
    /*---------------------------------------------------*/
    if(rubrosinv[form.cod_rubro.value]==""){enlaza_inventarios=false;}
	    
    if(opcion=="total"){
	    
	//var tasa_i1 = numerico(por_impuestos[form.impuesto1.options[form.impuesto1.selectedIndex].value]);
	var total = numerico(form.importe_total.value);
	var importe = numerico(form.monto_imp.value);
	var monto_imp1 = numerico(form.monto_imp1.value);
	var monto_imp2 = numerico(form.monto_imp2.value);
	var monto_imp3 = numerico(form.monto_imp3.value);
	
	if(objGroupPer.display == 'block')
	{
	    //alert('El objeto esta activo...'+total);
	    percepcion = (total*montopercepcion);
	    objpercepcion.value=numerico(percepcion).toFixed(2);
	    importefinal.value = numerico(total+percepcion).toFixed(2);
	}

	if(objGroupDet.display == 'block')
	{
	    //alert('El objeto esta activo...');
	    if(detraccion_tipo=='2' && total>=700)
	    {
		detraccion = (total*montodetraccion);
		objdetraccion.value=numerico(detraccion).toFixed(2);
		importefinal.value = numerico(total+detraccion).toFixed(2);
	    }else if(detraccion_tipo=='1' && total<=700 || total>=700){
		detraccion = (total*montodetraccion);
		objdetraccion.value=numerico(detraccion);
		importefinal.value = numerico(total+detraccion).toFixed(2);
	    }else{
		detraccion = numerico(0);
		objdetraccion.value=numerico(detraccion).toFixed(2);
		importefinal.value = numerico(total).toFixed(2);
	    }
	}

	if(objGroupRet.display == 'block')
	{
	    //alert('El objeto esta activo...');
	    retencion = (total*montoretencion);
	    objretencion.value=numerico(retencion).toFixed(2);
	    importefinal.value = numerico(total+retencion).toFixed(2);
	}

	form.monto_imp.value = importe.toFixed(2); //importe
	form.monto_imp1.value = monto_imp1.toFixed(2); //impuesto 1
	var dif = numerico(total-(importe+monto_imp1+monto_imp2+monto_imp3));
	dif = numerico(dif);
	dif = Math.abs(dif);
	form.c_montos_varios.value = dif;
	//alert("DIF="+numerico(total-(importe+monto_imp1+monto_imp2+monto_imp3))+", total="+total+" importe "+importe+" imp1 "+monto_imp1+" imp2 "+monto_imp2+" imp3 "+monto_imp3);
    }
    
    if(opcion=="importe"){
	    
	var tasa_i1 = numerico(por_impuestos[form.impuesto1.options[form.impuesto1.selectedIndex].value]);
	var tasa_i2 = numerico(por_impuestos[form.impuesto2.options[form.impuesto2.selectedIndex].value]);
	var tasa_i3 = numerico(por_impuestos[form.impuesto3.options[form.impuesto3.selectedIndex].value]);

	var importe = numerico(form.monto_imp.value);
	var total = numerico(form.importe_total.value);
	var monto_imp1 = importe*tasa_i1;
	var monto_imp2 = importe*tasa_i2;
	var monto_imp3 = importe*tasa_i3;
	
	
	
	//form.importe_total.value = total.toFixed(2); //importe
	form.monto_imp1.value = monto_imp1.toFixed(2); //impuesto 1
	form.monto_imp2.value = monto_imp2.toFixed(2); //impuesto 2
	form.monto_imp3.value = monto_imp3.toFixed(2); //impuesto 3
	form.c_montos_varios.value = numerico(total-(importe+monto_imp1+monto_imp2+monto_imp3));
    
    }
    
    if(opcion=="impuestos"){
    
	    var tasa_i1 = numerico(por_impuestos[form.impuesto1.options[form.impuesto1.selectedIndex].value]);
	    var tasa_i2 = numerico(por_impuestos[form.impuesto2.options[form.impuesto2.selectedIndex].value]);
	    var tasa_i3 = numerico(por_impuestos[form.impuesto3.options[form.impuesto3.selectedIndex].value]);

	    var importe = numerico(form.monto_imp.value);
	    var total = numerico(form.importe_total.value);
	    var monto_imp1 = numerico(form.monto_imp1.value);
	    var monto_imp2 = numerico(form.monto_imp2.value);
	    var monto_imp3 = numerico(form.monto_imp3.value);
	    
	    form.c_montos_varios.value = numerico(total-(importe+monto_imp1+monto_imp2+monto_imp3));
    //alert("total "+total+" importe"+importe+" imp 1 "+monto_imp1+" tasa 1 "+tasa_i1 );
    }
	
}
	//P
function recalcularImpuestos(form,opcion){
    var tasa_i1 = parseFloat(form.impuesto1.options[form.impuesto1.selectedIndex].value);
    var tasa_i2 = form.impuesto2.options[form.impuesto2.selectedIndex].value;
    var tasa_i3 = form.impuesto3.options[form.impuesto3.selectedIndex].value;
    
    

    if(isNaN(tasa_i1)){tasa_i1 = parseFloat(0.0);}

    if(opcion=="total"){
	    var importe_total = parseFloat(form.importe_total.value);
	    var imponible = importe_total - (importe_total*tasa_i1);
	    imponible = parseFloat(imponible);
	    form.monto_imp1.value = imponible*tasa_i1;
    }

}
	
	
function chekar(n){
    if(n==""){
    return parseFloat(0);
    }else{
	    return parseFloat(n);
    }
}
	
function importeImpuesto(monto_imp,combo,imp_imponible){
    /*importe pal impuesto , el combo que trae el % y el imponible sobre el que se saca*/
    //var str_impuesto 	= combo.value;
    var index = combo.selectedIndex;
    
    var str_impuesto = combo.options[index].text;
    str_impuesto = str_impuesto.substring(0,4);
    //alert(str_impuesto);
    var str_imponible	= imp_imponible.value;
    
    if(str_impuesto==""  || str_impuesto=="-- E"){ str_impuesto = 0;}
    if(str_imponible=="" || str_imponible=="-- E"){ str_imponible = 0;}
    
    var impuesto = parseFloat(str_impuesto);
    var imponible  = parseFloat(str_imponible);
    
    monto_imp.value = (imponible * impuesto).toFixed(2);  
}
	
function saludar(msg){
	alert('hola '+msg);
}

function mandarDatos(form,ope){

    if(ope!="Cancelar"){
	var ok = validarInclusionFacturas(form);
	if(ok){
	    var int_igv = parseFloat(form.monto_imp1.value);
	    var int_monto_imp = parseFloat(form.monto_imp.value);
	    if(int_igv>int_monto_imp){
		ok = false;
		alert('El igv es mayor que el monto imponible.');
	    } else
		ok = validarImportes(form);
	}

	if(ok){
	    form.accion.value = ope;
	    form.submit();
	}
    }else{
	form.accion.value = ope;
	form.submit();
    }
}

function mostrarAyudaDoc(ayuda_doc,cod_documento,show_ayuda_doc){
	
	if(cod_documento.value!=""){
		hacerDesaparecer(ayuda_doc);
		show_ayuda_doc.value = "no";
	}
}

/*ifr es el iframe , el chiche indica para que es, codigo es el codigo de busqueda
, campo_codigo es el campo que tiene el codigo de busqueda
y campo es el campo que se va a completar*/
function chiche(ifr,chiche,codigo,campo,campo_codigo){
    opcional = document.getElementsByName('cod_proveedor')[0].value;
    var url = "cpag_inclu_fac_iframe.php?chiches="+chiche+"&codigo="+codigo+"&campo="+campo+"&campo_codigo="+campo_codigo+"&opcional="+opcional;
    ifr.location = url;
}

function verificarIntegridad(iframe1,cod_proveedor,cod_documento,serie_doc,num_documento){
	
	var ok = true;
	var mensaje = "";	
	if(cod_proveedor.value==""){
		mensaje = "Codigo de Proveedor en blanco";
		ok = false;
		cod_proveedor.focus();
	}
	if(cod_documento.value==""){
		mensaje = "Codigo de Documento en blanco";
		ok = false;
		cod_documento.focus();
	}
	if(serie_doc.value==""){
		mensaje = "Serie de Documento en blanco";
		ok = false;
		serie_doc.focus();
	}
	if(num_documento.value==""){
		mensaje = "Numero de Documento en blanco";
		ok = false;
		num_documento.focus();
	}

	

	if(ok){	
		chiche(iframe1,'Integridad',cod_proveedor.value+'-'+cod_documento.value+'-'+serie_doc.value+'-'+num_documento.value,'num_documento','num_documento');
	}else{
		alert(mensaje);
	}
	
}
	
function cceros(v_var,v_lon,k_var,alerta){
	//La variable v_var, v_lon longitud final,k_var el nombre de la variable entre ''
	//, alerta es el mensaje de alerta si no completa el campo
	var v_var2= v_var.value.replace(/^\s*|\s*$/g,"");
	var lon1  = v_var.value.length;
	var lon2  = v_lon-lon1
	
	if(lon1>0){
	
		for(i=0;i<lon2;i++){
			v_var2='0'+v_var2;
		}
		eval("document.form1."+k_var+".value=v_var2");
	}else{
		alert(alerta);
		//v_var.focus();
	}
}

function validarImporteTotal(form,rubrosinv){

var enlaza_inventarios = true;
if(rubrosinv==""){enlaza_inventarios=false;}

if(enlaza_inventarios){
	var importe_total = parseFloat(form.importe_total.value);
	var monto_imponible = parseFloat(form.monto_imp.value);
	var impuesto1 = parseFloat(form.monto_imp1.value);
	var impuesto2 = parseFloat(form.monto_imp2.value);
	var impuesto3 = parseFloat(form.monto_imp3.value);
	var varios = numerico(form.c_montos_varios.value);
	var cal = numerico(form.cal.value);
	var tasa_cambio = numerico(form.tasa_cambio.value);
	if(tasa_cambio==0 || form.cod_moneda.value=="01"){tasa_cambio=1;}
	//var dif = importe_total - (monto_imponible+impuesto1+impuesto2+impuesto3);
	var dif = varios + monto_imponible - (cal/tasa_cambio);
	var limite_cpag=(<?php echo $para_limite_cpag?>/tasa_cambio);
	dif = Math.abs(dif);
	

	
	/*if(dif>= limite_cpag ){
		alert('Solo se permite redondear hasta de '+limite_cpag+'\n'+'Imponible :'+monto_imponible+'\n'+'Impuesto1 :'+impuesto1+'\n'+'Impuesto2 :'+impuesto2+'\n'+'Impuesto3 :'+impuesto3+'\n'+'Varios   :'+varios+'\n'+'Diferencia :'+dif+'\n' );
		form.redondeo.value='mal';
		form.importe_total.value = (monto_imponible+impuesto1+impuesto2+impuesto3).toFixed(2);
		form.importe_total.focus();
	}else{
		m_var = Math.abs(varios);
		if(m_var>=limite_cpag){
			alert('Revise Diversos o Importe Total Regenerado');
			form.redondeo.value='mal';
			form.importe_total.value = (monto_imponible+impuesto1+impuesto2+impuesto3).toFixed(2);
			form.importe_total.focus();
			}
		else{
			form.redondeo.value='ok';
		}
	}*/
	
}else{ //no enlaza a inventarios

	var importe_total = numerico(form.importe_total.value);
	var monto_imponible = numerico(form.monto_imp.value);
	var tasa_cambio = numeric(form.tasa_cambio.value);
	if(form.cod_moneda.value=="01"){tasa_cambio=1;}
	
	var pasa = true;
	var msg = "";
	if(pasa){
		if(monto_imponible>importe_total){
			pasa=false;
			msg = "ERROR: El importe imponible es mayor al total";
		}
	}
	if(pasa){
		if(form.cod_moneda.value!='01' && tasa_cambio==1){
			pasa=false;
			msg="ERROR: Tipo de cambio=1 con una moneda diferente al sol";
		}
	}
	
	if(pasa){
		form.redondeo.value='ok';
	}else{alert(msg);}

}	
	
}


function validarImportes(form){
	
	var pasa = true;
	var imp1 = numerico(form.monto_imp1.value);
	var imp2 = numerico(form.monto_imp2.value);
	var imp3 = numerico(form.monto_imp3.value);
	var monto_imp = numerico(form.monto_imp.value);
	var importe_total = numerico(form.importe_total.value);
	var cal = numerico(form.cal.value);
	var tasa_cambio = numerico(form.tasa_cambio.value);
	if(tasa_cambio==0 || form.cod_moneda.value=="01"){tasa_cambio=1;}
	var c_tasa_cambio = numerico(form.tasa_cambio.value);
	if(c_tasa_cambio==0) {c_tasa_cambio=1;}
	var enlaza_inventarios = true;
	var varios = numerico(form.c_montos_varios.value);
	if(rubrosinv[form.cod_rubro.value]==""){enlaza_inventarios=false;}
	if(tasa_cambio==0 || form.cod_moneda.value=="01"){c_tasa_cambio=1;}

	//var dif = importe_total-(imp1+imp2+imp3+monto_imp);
	var dif = numerico(monto_imp*c_tasa_cambio)+numerico(varios*c_tasa_cambio) - (cal);
	dif = numerico(dif);
	dif = parseFloat(dif);
	var tasa_cambio = numerico(form.tasa_cambio.value);
	if(form.cod_moneda.value=="01"){tasa_cambio=1;}
	//form.c_montos_varios.value = dif;
	dif = Math.abs(dif);
	//dif = dif.toFixed(0);
	//alert("imponible "+monto_imp+" cal "+cal+" c_tasa_cambio "+c_tasa_cambio+" varios "+varios+" dif "+((monto_imp+varios)-cal/c_tasa_cambio) );
	if(dif>=1.0 && enlaza_inventarios){
			pasa = false;
			alert("Imponible + Varios - Valor Acumulado >= 1, DIF "+dif);
	}
	
	if(form.cod_moneda.value!="01" && tasa_cambio==1){
		pasa = false;
		alert("ERROR: Monedas distintas al sol no pueden tener tasa de cambio 1");
	}
	
	if(form.cod_moneda.value=="01" && tasa_cambio!=1){
		pasa = false;
		alert("ERROR: La moneda Soles solo puede tener tasa de cambio 1");
	}
	
	if(monto_imp>=importe_total){
		pasa = false;
		alert("ERROR: El imponible no puede ser mayor o igual al total de la factura");
	}
	//pasa = false;
	return pasa;
	
}

function numerico(numero){

	var ret = parseFloat(numero);
	if(isNaN(ret)){ ret = parseFloat("0.0000"); }
	ret = ret.toFixed(4);
	
	return parseFloat(ret);
}

function confirmarForm(pregunta, form, Accion){
  
  var mensaje = document.forms[0].mensaje.value;
  //document.getElementsByName('cod_rubro').;
  
  if(mensaje>0)
    pregunta = 'Se han eliminado '+mensaje+' articulo(s) !!!\n'+pregunta;
  else
    pregunta = pregunta;

  if(confirm(pregunta)){
    mandarDatos(form1, Accion);
    return true;
  }else{
    return false;
  }

}
</script>
<title>Inclusion de Facturas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:250;"></div>
<?php
  $query = "SELECT ".
		  "to_char(cab.pro_cab_fecharegistro,'DD/MM/YYYY') as pro_cab_fecharegistro, ".
		  "to_char(cab.pro_cab_fechaemision,'DD/MM/YYYY') as pro_cab_fechaemision, ".
		  "to_char(cab.pro_cab_fechavencimiento,'DD/MM/YYYY') as pro_cab_fechavencimiento, ".
		  "cab.pro_cab_almacen, ".
		  "alm.ch_nombre_almacen, ".
		  "cab.pro_cab_tipdocumento as tipdocumento, ".
		  "lpad(trim(cab.pro_cab_tipdocumento),6,'0') as pro_cab_tipdocumento, ".
		  "(SELECT tab_descripcion FROM int_tabla_general WHERE tab_tabla='08' AND trim(tab_elemento) = lpad(trim(cab.pro_cab_tipdocumento),6,'0')) as doc_descrip, ".
		  "cab.pro_cab_seriedocumento, ".
		  "cab.pro_cab_numdocumento, ".
		  "cab.pro_codigo, ".
		  "pro.pro_razsocial, ".
		  "cab.pro_cab_imptotal, ".
		  "cab.pro_cab_impto1, ".
		  "cab.pro_cab_impto2, ".
		  "cab.pro_cab_impto3, ".
		  "cab.pro_cab_impsaldo, ".
		  "cab.pro_cab_moneda, ".
		  "cab.com_cab_numorden, ".
		  "(SELECT tca_compra_oficial FROM int_tipo_cambio WHERE tca_fecha=cab.pro_cab_fecharegistro AND tca_moneda='02') as pro_cab_tcambio, ".
		  "cab.pro_cab_rubrodoc, ".
		  "rub.ch_descripcion, ".
		  "to_char(cab.pro_cab_fechavencimiento, 'DD/MM/YYYY') as pro_cab_fechavencimiento, ".
		  "cab.pro_cab_impafecto, ".
		  "lpad(trim(cab.pro_cab_tipdocreferencia),6,'0') as pro_cab_tipdocreferencia, ".
		  "(SELECT tab_descripcion FROM int_tabla_general WHERE tab_tabla='08' AND trim(tab_elemento) = lpad(trim(cab.pro_cab_tipdocreferencia),6,'0')) as docref_descrip, ".
		  "cab.pro_cab_numdocreferencia ".
	  "FROM cpag_ta_cabecera cab, ".
	       "int_proveedores pro, ".
	       "inv_ta_almacenes alm, ".
	       "cpag_ta_rubros rub ".
	  "WHERE cab.pro_codigo = pro.pro_codigo ".
	  "AND cab.pro_cab_almacen = alm.ch_almacen ".
	  "AND cab.pro_cab_rubrodoc = rub.ch_codigo_rubro ".
	  "AND cab.pro_cab_tipdocumento||cab.pro_cab_seriedocumento||cab.pro_cab_numdocumento||cab.pro_codigo = '".$_REQUEST['regid']."'";
  
  $rs=pg_exec($query);
  for($i=0;$i<pg_numrows($rs);$i++){
    $DatosCab = pg_fetch_array($rs,$i);
  }

  $cod_proveedor?$cod_proveedor=$cod_proveedor:$cod_proveedor=trim($DatosCab['pro_codigo']);
  $cod_rubro?$cod_rubro=$cod_rubro:$cod_rubro=trim($DatosCab['pro_cab_rubrodoc']);
  //print "<script>form1.desc_rubro.focus();</script>";

  $cod_documento?$cod_documento=$cod_documento:$cod_documento=trim($DatosCab['pro_cab_tipdocumento']);
  $serie_doc?$serie_doc=$serie_doc:$serie_doc=trim($DatosCab['pro_cab_seriedocumento']);
  $num_documento?$num_documento=$num_documento:$num_documento=trim($DatosCab['pro_cab_numdocumento']);
  $cod_docref?$cod_docref=$cod_docref:$cod_docref=trim($DatosCab['pro_cab_tipdocreferencia']);
  $num_docurefe?$num_docurefe=$num_docurefe:$num_docurefe=trim($DatosCab['pro_cab_numdocreferencia']);
  $fecha_ven?$fecha_ven=$fecha_ven:$fecha_ven=trim($DatosCab['pro_cab_fechavencimiento']);
  $cod_unidad?$cod_unidad=$cod_unidad:$cod_unidad=trim($DatosCab['pro_cab_almacen']);
  $tasa_cambio=trim($DatosCab['pro_cab_tcambio']);
  $importe_total?$importe_total=$importe_total:$importe_total=trim($DatosCab['pro_cab_imptotal']);
  $monto_imp?$monto_imp=$monto_imp:$monto_imp=trim($DatosCab['pro_cab_impafecto']);
  $monto_imp1?$monto_imp1=$monto_imp1:$monto_imp1=trim($DatosCab['pro_cab_impto1']);
  $monto_imp2?$monto_imp2=$monto_imp2:$monto_imp2=trim($DatosCab['pro_cab_impto2']);
  $monto_imp3?$monto_imp3=$monto_imp3:$monto_imp3=trim($DatosCab['pro_cab_impto3']);

  $desc_proveedor?$desc_proveedor=$desc_proveedor:$desc_proveedor=trim($DatosCab['pro_razsocial']);
  $desc_rubro?$desc_rubro=$desc_rubro:$desc_rubro=trim($DatosCab['ch_descripcion']);
  $des_unidad?$des_unidad=$des_unidad:$des_unidad=trim($DatosCab['ch_nombre_almacen']);
  $des_documento?$des_documento=$des_documento:$des_documento=trim($DatosCab['doc_descrip']);
  $des_docref?$des_docref=$des_docref:$des_docref=trim($DatosCab['docref_descrip']);
  

   $ord_compra           = $DatosCab['com_cab_numorden'];
   $ord_almacen           = $DatosCab['pro_cab_almacen'];
   $proveedor           = $DatosCab['pro_codigo'];
   
  /* INICIO :: AGREGAR DATOS DE ORDENES COMPRAS */
  $query = "SELECT  ".
                    "a.art_descripcion, ".
                    "a.art_codigo, ".
                    "to_char(c.mov_fecha, 'DD/MM/YYYY') as mov_fecha_go, ".
                    "c.* ".
           "FROM inv_ta_compras_devoluciones c, ".
                "int_articulos a ".
           "WHERE c.art_codigo=a.art_codigo ".
           "AND c.tran_codigo='01' ".
           //"AND trim(cpag_tipo_pago)||trim(cpag_serie_pago)||trim(cpag_num_pago) = '".trim($DatosCab['tipdocumento']).trim($serie_doc).trim($num_documento)."'";
           "AND trim(cpag_tipo_pago) = '".trim($DatosCab['tipdocumento'])."' ".
           "AND trim(cpag_serie_pago) = '".trim($serie_doc)."' ".
           "AND trim(cpag_num_pago) = '".trim($num_documento)."'; ";
  
  echo "<!-- QUERY COM : $query -->\n";
  
  $rs=pg_exec($query);
  if(pg_numrows($rs)>0)
  {
    $cal=trim($DatosCab['pro_cab_impafecto']);
    //$monto_compras?$monto_compras=$monto_compras:$monto_compras=trim($DatosCab['pro_cab_impafecto']);
  //}
    for($i=0;$i<pg_numrows($rs);$i++)
    {
  
      $DatosOrdCom = pg_fetch_array($rs,$i);
      $DatosOrdCom_ar[] = $DatosOrdCom;
      //$DivF = explode(" ", $DatosOrdCom['mov_fecha']);
      //$DatosOrdCom['mov_fecha'] = $DivF[0];
      
      $art_descripcion[$i]      = $DatosOrdCom['mov_fecha_go']." - ".$DatosOrdCom['mov_docurefe']." - ".$DatosOrdCom['art_codigo']." - ".$DatosOrdCom['art_descripcion'];
      $art_codigo[$i]           = $DatosOrdCom['art_codigo'];
      $art_cantidad[$i]         = $DatosOrdCom['mov_cantidad'];
      $art_costo_uni[$i]        = $DatosOrdCom['mov_costounitario'];
      $art_costo_total[$i]      = $DatosOrdCom['mov_costototal'];
      $art_costo_uni_dol[$i]    = round($DatosOrdCom['mov_costounitario']/$DatosCab['pro_cab_tcambio'],2);
      $art_costo_total_dol[$i]  = round($DatosOrdCom['mov_costototal']/$DatosCab['pro_cab_tcambio'],2);
      $com_tipo_compra[$i]      = $DatosOrdCom['com_tipo_compra'];
      $com_serie_compra[$i]     = $DatosOrdCom['com_serie_compra'];
      $com_num_compra[$i]       = $DatosOrdCom['com_num_compra'];
      $tran_codigo[$i]          = $DatosOrdCom['tran_codigo'];
      $mov_fecha[$i]            = $DatosOrdCom['mov_fecha_go'];
      $items[$i] = $i;
      $ord_compra_new[$i]           = $DatosOrdCom['com_num_compra'];
      $monto_compras += $DatosOrdCom['mov_costototal'];
    }
  //echo "COMP : $monto_compras<br>";
    echo "<!--";
    //echo "QUERY DEV : $query2 \n";
    //print_r($art_codigo);
    echo "-->";
  
    unset($_SESSION["ITEMSCD"]);
    $ITEMSCD = $_SESSION["ITEMSCD"];
    $ITEMSCD = registrarItemsEdit($art_descripcion,$art_codigo,$art_cantidad,$art_costo_uni
    ,$art_costo_total,	$art_costo_uni_dol
    ,$art_costo_total_dol,$ord_compra_new,$ord_almacen,$items,$proveedor
    ,$com_tipo_compra , $com_serie_compra , $com_num_compra 
    ,$tran_codigo , $mov_fecha
    ,$X , $Y
    ,$ITEMSCD);
    $_SESSION["ITEMSCD"] = $ITEMSCD;
   }
  /* FIN :: AGREGAR DATOS DE ORDENES COMPRAS */

  /* INICIO :: AGREGAR DATOS DE DEVOLUCIONES */
  $query2 = "SELECT  ".
                    "a.art_descripcion, ".
                    "a.art_codigo, ".
                    "to_char(c.mov_fecha, 'DD/MM/YYYY') as mov_fecha_go, ".
                    "c.* ".
           "FROM inv_ta_compras_devoluciones c, ".
                "int_articulos a ".
           "WHERE c.art_codigo=a.art_codigo ".
           "AND c.tran_codigo='05' ".
           "AND trim(cpag_tipo_pago) = '".trim($DatosCab['tipdocumento'])."' ".
           "AND trim(cpag_serie_pago) = '".trim($serie_doc)."' ".
           "AND trim(cpag_num_pago) = '".trim($num_documento)."'; ";
  echo "<!--";
  echo "QUERY DEV : $query2 \n";
  echo "-->";
  $rs2=pg_exec($query2);
  if(pg_numrows($rs2)>0)
  {
  for($i=0;$i<pg_numrows($rs2);$i++)
  {

    $DatosDev = pg_fetch_array($rs2,$i);
    //$DivF = explode(" ", $DatosOrdCom['mov_fecha']);
    //$DatosOrdCom['mov_fecha'] = $DivF[0];
    
    $art_descripcion2[$i]      = $DatosDev['mov_fecha_go']." - ".$DatosDev['mov_docurefe']." - ".$DatosDev['art_codigo']." - ".$DatosDev['art_descripcion'];
    $art_codigo2[$i]           = $DatosDev['art_codigo'];
    $art_cantidad2[$i]         = $DatosDev['mov_cantidad'];
    $art_costo_uni2[$i]        = $DatosDev['mov_costounitario'];
    $art_costo_total2[$i]      = $DatosDev['mov_costototal'];
    $art_costo_uni_dol2[$i]    = round($DatosDev['mov_costounitario']/$DatosCab['pro_cab_tcambio'],2);
    $art_costo_total_dol2[$i]  = round($DatosDev['mov_costototal']/$DatosCab['pro_cab_tcambio'],2);
    $com_tipo_compra2[$i]      = $DatosDev['com_tipo_compra'];
    $com_serie_compra2[$i]     = $DatosDev['com_serie_compra'];
    $com_num_compra2[$i]       = $DatosDev['com_num_compra'];
    $tran_codigo2[$i]          = $DatosDev['tran_codigo'];
    $mov_fecha2[$i]            = $DatosDev['mov_fecha_go'];
    $items2[$i]                = $i;
    $guias2[$i]                = $DatosDev['mov_docurefe'];
    $fechas2[$i]               = $DatosDev['mov_fecha_go'];
    $ord_compra_new[$i]        = $DatosDev['mov_docurefe'];
    
    $monto_devoluciones += $DatosDev['mov_costototal'];
  }
  //echo "<br><br>DEVOLU : $monto_devoluciones <br>";
  unset($_SESSION["ITEMSCD_DEV"]);
  $ITEMSCD_DEV = $_SESSION["ITEMSCD_DEV"];
  $ITEMSCD_DEV = registrarItemsEdit($art_descripcion2,$art_codigo2,$art_cantidad2,$art_costo_uni2
  ,$art_costo_total2,$art_costo_uni_dol2
  ,$art_costo_total_dol2,$ord_compra_new,$ord_almacen,$items2,$proveedor
  ,$com_tipo_compra2 , $com_serie_compra2 , $com_num_compra2 
  ,$tran_codigo2 , $mov_fecha2
  ,$guias2 , $fechas2
  ,$ITEMSCD_DEV);
  $_SESSION["ITEMSCD_DEV"] = $ITEMSCD_DEV;
  
  echo "<!--";
  //print_r($_SESSION["ITEMSCD_DEV"]);
  echo "-->";
  
  }
  /* FIN :: AGREGAR DATOS DE DEVOLUCIONES */

?>
<form name="form1" method="post" action="">
  <!--<div align="center">
   <caption class="form_title">INCLUSI&Oacute;N DE FACTURAS</caption>
  </div>--><!--alert(rubrosinv[cod_rubro.value]);-->
  <!--<input type="button" name="Submit" value="Submit" onClick="alert(parseFloat(ee.value));">
  <input type="text" name="ee" value="" size="10">-->
  <br>
 <div class="form" align="center">
  <table border="0" class="form_body" cellpadding="5" cellspacing="1">
  <caption class="form_title">INCLUSI&Oacute;N DE FACTURAS</caption>
  <tbody>
  <tr>
  <td colspan="1" class="form_group">
    <table border="0" align="center" cellpadding="3" cellspacing="3">
     <tbody>
	<tr>
	    <td class="form_label">Fecha Dcmto.</td>
	    
	    <td> : 
	    <input size="12" class="form_input" type="text" name="fec_docu" value="<?php echo $DatosCab['pro_cab_fecharegistro']?>" onBlur="javascript:chiche(iframe1,'Fecha_Documento',fec_docu.value,'fec_docu','fec_docu');">
	    </td>
	    
	    <td>
	    <a href="javascript:show_calendar('form1.fec_docu');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" > 
	    <img src="../images/show-calendar.gif" width="24" height="22" border="0"> 
	    </a>
	    </td>
	    <td>
		<table border="0">
		<tr>
		    <td class="form_label">&nbsp;&nbsp;Fecha Registro 
		    <input type="hidden" name="fec_reg" value="<?php echo $DatosCab['pro_cab_fecharegistro']?>">
		    <input type="hidden" name="num_registro" value="<?php echo $num_registro;?>" maxlength="10">
		    </td>
		    <td class="form_label">: <?php echo $DatosCab['pro_cab_fecharegistro']?></td>
		<tr>
		</table>
	    </td>
	</tr>
	<tr>
	    <td class="form_label">Proveedor</td>
	    <td>:<input size="12" class="form_input" type="text" name="cod_proveedor" value="<?php echo $cod_proveedor;?>"  onFocus="javascript:chiche(iframe1,'Fecha_Documento',fec_docu.value,'fec_docu','fec_docu');">
	    </td>
	    <td>
	    <img src="../images/help.gif" onMouseOver="this.style.cursor='hand'" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','form1.cod_proveedor','form1.desc_proveedor','proveedores')"> 
	    </td>
	    <td>
	    <input class="form_input" type="text" name="desc_proveedor"  size="40" readonly="true" value="<?php echo $desc_proveedor;?>" onFocus="javascript:chiche(iframe1,'Proveedores',cod_proveedor.value,'desc_proveedor','cod_proveedor'),cod_rubro.focus();">
	    </td>
	</tr>

	<tr>
	    <td class="form_label">Rubro</td>
	    <td class="form_label">:<input size="12" class="form_input" type="text" name="cod_rubro" value="<?php echo $cod_rubro;?>" >
	    </td>
	    <td>
	    <img src="../images/help.gif" onMouseOver="this.style.cursor='hand'" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','form1.cod_rubro','form1.desc_rubro','rubros_cp')"> 
	    </td>
	    <td>
            <input class="form_input" type="text" name="desc_rubro" size="40" readonly="true" value="<?php echo $desc_rubro;?>" onFocus="javascript:chiche(iframe1,'Rubros',cod_rubro.value,'desc_rubro','cod_rubro'),cod_documento.focus();">
	    </td>
	</tr>
        <tr><td colspan="5"><hr></td></tr>
	<tr>
	    <td class="form_label">Cod. Documento
	    <input type="hidden" name="show_ayuda_doc" value="<?php echo $show_ayuda_doc;?>" >
	    </td>
	    <td class="form_label">:<input size="12" class="form_input" type="text" name="cod_documento"  value="<?php echo $cod_documento;?>">
	    </td>
	    <td>
	    <?php if($show_ayuda_doczzzz!="no"){?>
            <img id="ayuda_doc" src="../images/help.gif" onMouseOver="this.style.cursor='hand'" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','form1.cod_documento','form1.des_documento','documentos_sunat')"> 
            <?php } ?>	    
            </td>
	    <td>
            <input class="form_input" type="text" name="des_documento" size="40" value="<?php echo $des_documento;?>" onFocus="javascript:chiche(iframe1,'Documentos_Sunat',cod_documento.value,'des_documento','cod_documento'),serie_doc.focus();">
	    </td>
	    <td>
	       <table>
	        <tr>
	         <td class="form_label">Serie</td>
	         <td class="form_label">:<input class="form_input_numeric" type="text" name="serie_doc" size="3" maxlength="3" value="<?php echo $serie_doc;?>" onBlur="javascript:cceros(serie_doc,3,'serie_doc','Debes ingresar la serie');"></td>
	         <td class="form_label">Nro</td>
	         <td><input class="form_input_numeric" size="12" type="text" name="num_documento" maxlength="7" value="<?php echo $num_documento;?>" onBlur="javascript:cceros(num_documento,7,'num_documento','Debes numero de Documento');"></td>
	        </tr>
	       </table>
	    </td>
	</tr>


	<tr>
	    <td class="form_label">Doc. Referencia
	    <input type="hidden" name="show_ayuda_doc" value="<?php echo $show_ayuda_doc;?>" >
	    </td>
	    <td class="form_label">:<input size="12" class="form_input" type="text" name="cod_docref"  value="<?php echo $cod_docref;?>"  onBlur="javascript:cceros(num_documento,7,'num_documento','Debes numero de Documento');">
	    </td>
	    <td>
	    <img src="../images/help.gif" onMouseOver="this.style.cursor='hand'" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','form1.cod_docref','form1.des_docref','documentos')"> 
            </td>
	    <td>
            <input class="form_input" type="text" name="des_docref" size="40" readonly="yes" value="<?php echo $des_docref;?>" onFocus="javascript:chiche(iframe1,'Documentos',cod_docref.value,'des_docref','cod_docref'),num_docurefe.focus();">
	    </td>
	    <td>
	       <table>
	        <tr>
	         <td class="form_label">Nro Referencia</td>
	         <td class="form_label">:<input class="form_input_numeric" type="text" name="num_docurefe" value="<?php echo $num_docurefe;?>" size="12" onBlur="javascript:cceros(num_docurefe,10,'num_docurefe','Numero de Referencia no ingresado !');">
	        </tr>
	       </table>
	    </td>
	</tr>

	<tr>
	    <td class="form_label">Fecha de Venc.</td>
	    
	    <td>:<input size="12" class="form_input" type="text" name="fecha_ven" readonly="yes" value="<?php echo $fecha_ven;?>" onFocus="javascript:chiche(iframe1,'Vencimientos',cod_proveedor.value+'-'+fec_docu.value,'fecha_ven','fec_docu'),cod_unidad.focus();">
	    </td>
	    
	    <td>
	    <a href="javascript:show_calendar('form1.fecha_ven');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" > 
            <img src="../images/show-calendar.gif" border="0" width="24" height="22" > 
            </a>
	    </td>
	    <td>
	    </td>
	</tr>

	<tr>
	    <td class="form_label">Unidad  Contable</td>
	    <td class="form_label">:<input class="form_input_numeric" type="text" name="cod_unidad" value="<?php echo $cod_unidad;?>" size="4" maxlength="3">
	    </td>
	    <td>
	    <img src="../images/help.gif" onMouseOver="this.style.cursor='hand'" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','form1.cod_unidad','form1.des_unidad','almacenes')"> 
	    </td>
	    <td>
	    <input class="form_input" type="text" name="des_unidad" size="40" value="<?php echo $des_unidad;?>" onFocus="javascript:chiche(iframe1,'Almacenes',cod_unidad.value,'des_unidad','cod_unidad'),cod_moneda.focus();">
	    </td>
	</tr>
	
        <tr><td colspan="5"><hr></td></tr>

	<tr>
	    <td class="form_label">Moneda</td>
	    <td class="form_label">:<select class="form_combo" name="cod_moneda" onChange=" mandarCombo(this,form1,iframe1) , btn_ordencompra.focus(),calcularMontos(form1);">
		<?php $rs_tc = combo("monedas");
		  for($i=0;$i<pg_numrows($rs_tc);$i++){
		    $A = pg_fetch_array($rs_tc,$i);
		    if($A[0]>0 && $A[0] == $DatosCab['pro_cab_moneda']){
		      echo "<option value='".$A[0]."' selected>".$A[0]." - ".$A[1]."</option>\n";
		    }elseif($A[0]>0 && $A[0] != $DatosCab['pro_cab_moneda']){
		      echo "<option value='".$A[0]."'>".$A[0]." - ".$A[1]."</option>\n";
		    }
		  }
		?>
		 </select>
	         <input type="hidden" name="cod_moneda_index" value="<?php echo $cod_moneda_index;?>" >
	    </td>
	    <td></td>
	    <td>
	       <table>
	        <tr>
	         <td class="form_label">Tasa de Cambio</td>
	         <td class="form_label">:<input size="8" class="form_input_numeric" type="text" name="tasa_cambio" value="<?php echo $tasa_cambio;?>" onKeyUp="javascript:calcularMontos(form1);"></td>
	        </tr>
	       </table>
	    </td>
	    <td>
	       <table>
	        <tr>
	         <td><input type="checkbox" name="many_alma" onClick="javascript:cambiarValue(many_alma,'many_alma')" value=""> </td>
	         <td class="form_label">Varios Almacenes</tr>
	       </table>
	    </td>
	</tr>

        <tr><td colspan="5"><hr></td></tr>

	<tr>
	    <td><input class="form_button" type="button" name="btn_ordencompra" value="Ordenes de Compra" onClick="javascript:mostrarAyudaOrdenCompra('cpag_ayuda_orddev.php',cod_proveedor.value,cod_unidad.value,many_alma.value,tasa_cambio.value,cod_documento.value,cod_moneda.value,rubrosinv[form.cod_rubro.value]) , mostrarAyudaDoc(ayuda_doc,cod_documento,show_ayuda_doc);"></td>
	    <td><input class="form_input" type="text" name="monto_compras" value="<?php echo $monto_compras?>" size="8" readonly="yes" onFocus="javascript:btn_devoluciones.focus();"></td>
	    <td></td>
	    <td>
	       <table>
	        <tr>
	         <td><input class="form_button" type="button" name="btn_devoluciones" value="Devoluciones" onClick="javascript:mostrarAyudaOrdenCompra('cpag_ayuda_orddevDev.php',cod_proveedor.value,cod_unidad.value,many_alma.value,tasa_cambio.value,cod_documento.value,cod_moneda.value,rubrosinv[form.cod_rubro.value]) , mostrarAyudaDoc(ayuda_doc,cod_documento,show_ayuda_doc);"></td>
	         <td><input class="form_input" type="text" name="monto_devoluciones" value="<?php echo $monto_devoluciones?>" size="8" readonly="yes" onFocus="javascript:many_alma.focus();"></td>
	        </tr>
	       </table>
	    </td>
	    <td>
	       <table>
	        <tr>
	         <td class="form_label">Acum. Ord. y Devol.</td>
	         <td><input class="form_input" type="text" name="cal" value="<?php echo $cal;?>" size="10" maxlength="10" readonly="yes"></td>
                </tr>
	       </table>
	    </td>
	</tr>

        <tr><td colspan="5"><hr></td></tr>

	<tr>
	    <td class="form_label">Importe Total</td>
	    <td class="form_label">:<input size="10" class="form_input_numeric" type="text" name="importe_total" value="<?php echo $importe_total;?>"  onBlur="javascript:validarImporteTotal(form1,rubrosinv[form.cod_rubro.value]);calcularMontos2(form1,'total'),redondeo.value='mal';">
	    </td>
	    <td></td>
	    <td>
	       <table>
	        <tr>
	         <td class="form_label">Monto Imponible</td>
	         <td class="form_label">:<input size="10" class="form_input_numeric" type="text" name="monto_imp" value="<?php echo $monto_imp;?>" onKeyUp="javascript:calcularMontos2(form1,'importe'),tipeado.value='true'" onBlur="javascript:validarImporteTotal(form1,rubrosinv[form.cod_rubro.value]);calcularMontos2(form1,'total'),redondeo.value='mal';">
                     <input type="hidden" name="tipeado">
	         </td>
	        </tr>
	       </table>
	    </td>
	    <td>
	    </td>
	</tr>

        <tr>
         <td colspan="5">
         <fieldset class="form_group" id="impuestos">
         <legend class="form_group_title">IMPUESTOS</legend>
	    <table border="0" cellpadding="3" cellspacing="3">
	    
	    <tr>
		<td class="form_label">Tipo Impuesto 1</td>
		<td class="form_label">:
		<select class="form_combo" name="impuesto1" onChange="impuesto1_index.value=this.selectedIndex , importeImpuesto(monto_imp1,this,monto_imp) , calcularMontos(form1);">
		<option value="">-- Elija Uno --</option>
		<?php $rsi1 = combo("impuestos");
			for($i=0;$i<pg_numrows($rsi1);$i++){
				$A = pg_fetch_array($rsi1,$i);
				print "<option value='$A[2]'>$A[0]-$A[1]</option>";
			} ?>
		</select>
                <input type="hidden" name="impuesto1_index" value="<?php echo $impuesto1_index;?>">
		</td>
		<td><input class="form_input_numeric" size="10" type="text" name="monto_imp1" onKeyUp="javascript:calcularMontos2(form1,'impuestos');" value="<?php echo $monto_imp1;?>">
		</td>
	    </tr>
	    
	    <tr>
		<td class="form_label">Tipo Impuesto 2</td>
		<td class="form_label">:
		<select class="form_combo" name="impuesto2" onChange="impuesto2_index.value=this.selectedIndex , importeImpuesto(monto_imp2,this,monto_imp);" >
		<option value=''>-- Elija Uno --</option>
		<?php if($tipoAlmacen=="O"){
				$rsi1 = combo("impuestos");
					for($i=0;$i<pg_numrows($rsi1);$i++){
					$A = pg_fetch_array($rsi1,$i);
					print "<option value='$A[2]'>$A[0]-$A[1]</option>";
					}
			} ?>
		</select>
		<input type="hidden" name="impuesto2_index"  value="<?php echo $impuesto2_index;?>">
		</td>
		<td>
		<?php if($tipoAlmacen=="E"){$monto_imp2=0.0;} ?>
		<input class="form_input_numeric" size="10" type="text" name="monto_imp2" value="<?php echo $monto_imp2;?>" <?php if($tipoAlmacen=="E"){echo "readonly=''";} ?>>
		</td>
	    </tr>

	    <tr>
		<td class="form_label">Tipo Impuesto 3</td>
		<td class="form_label">:
		<select class="form_combo" name="impuesto3" onChange="impuesto3_index.value=this.selectedIndex , importeImpuesto(monto_imp3,this,monto_imp);">
		<option value=''>-- Elija Uno --</option>
		<?php if($tipoAlmacen=="O"){
				$rsi1 = combo("impuestos");
					for($i=0;$i<pg_numrows($rsi1);$i++){
					$A = pg_fetch_array($rsi1,$i);
					print "<option value='$A[2]'>$A[0]-$A[1]</option>";
				}
			} ?>
		</select>
		<input type="hidden" name="impuesto3_index"  value="<?php echo $impuesto3_index;?>">
		</td>
		<td>
		<?php if($tipoAlmacen=="E"){$monto_imp3=0.0;} ?>
                <input class="form_input_numeric" size="10" type="text" name="monto_imp3" value="<?php echo $monto_imp3;?>" <?php if($tipoAlmacen=="E"){echo "readonly=''";} ?>>
		</td>
	    </tr>

	    </table>
         </fieldset>
         </td>
        </tr>

        <tr>
         <td colspan="5">
         <fieldset class="form_group" id='gruposunat' style="display:none;">
         <legend class="form_group_title">ADICIONALES SUNAT</legend>
	   <table border="0" cellpadding="3" cellspacing="3">
	    <tr>
		<td id="percepcion" style="display:none;" class="form_label">
		Percepci&oacute;n : <input class="form_input_numeric" size="10" type="text" name="percepcion" value="" readonly="true">
		<input type="hidden" id="mnt_apli_percepcion" name="mnt_apli_percepcion"  value="">
		</td>
		<td id="detraccion" style="display:none;" class="form_label">
		Detracci&oacute;n : <input class="form_input_numeric" size="10" type="text" name="detraccion" value="" readonly="true">
		<input type="hidden" id="mnt_apli_detraccion" name="mnt_apli_detraccion"  value="">
		<input type="hidden" id="detraccion_tipo" name="detraccion_tipo"  value="">
		</td>
		<td id="retencion" style="display:none;" class="form_label">
		Retenci&oacute;n : <input class="form_input_numeric" size="10" type="text" name="retencion" value="" readonly="true">
		<input type="hidden" id="mnt_apli_retencion" name="mnt_apli_retencion"  value="">
		</td>
		<td class="form_label">
		&nbsp;&nbsp;&nbsp;Importe Final : <input class="form_input_numeric" size="10" type="text" name="importe_final" value="" readonly="true">
		</td>
	    </tr>
	   </table>
        </fieldset>
        </td>
        </tr>
        
        <tr><td colspan="5"><hr></td></tr>
        <tr>
         <td class="form_label">Diversos</td>
         <td>
	    <?php if($c_montos_varios==""){$c_montos_varios=0.0;} ?>
	    <input size="10" type="text" name="c_montos_varios" readonly="true" value="<?php echo $c_montos_varios;?>">
         </td>
         <td>
	    <input type="hidden" name="accion"  value="<?php echo $accion;?>">
	    <input type="hidden" name="voucher" value="<?php echo $voucher;?>">
	    <input type="hidden" name="glosa" value="<?php echo $glosa;?>">
	    <input type="hidden" name="emisor" value="<?php echo $emisor;?>">
	    <input type="hidden" name="grupo" value="<?php echo $grupo;?>">
	    <input type="hidden" name="redondeo" value="ok">
	    <input type="hidden" name="regid" value="<?php echo $_REQUEST['regid']?>">
	    <input type="hidden" id="prov_cta_detracc" name="prov_cta_detracc"  value="">
	    <input type="hidden" name="mensaje">

         </td>
         <td></td>
         <td></td>
        </tr>
        <?php
        /*foreach($ITEMSDEL as $llave => $valor){
          $mensaje .= $valor."\n";
          
          echo "<!-- MENSAJE : $mensaje-->\n";
        }*/
        ?>
         <tr>
	    <td colspan="5">
	    <fieldset class="form_group" id="buttons">
	    <legend class="form_group_title"></legend>
		<table border="0" cellpadding="3" cellspacing="3">
		<tr>
		<td><input class="form_button" type="button" name="Actualizar" value="Actualizar" onClick="confirmarForm('Desea realmente Actualizar ?','','Actualizar');"></td>
		<td><input class="form_button" type="button" name="Cancelar" value="Cancelar" onClick="confirmarForm('Desea realmente cancelar ?','','Cancelar');"></td>
		</tr>
		</table>
	    </fieldset>
	    </td>
         </tr>
         
     </tbody>
    </table>
 
 </td>
 </tr>
 </tbody>
 </table>
</div>

</form>

<?php
  print "<script>form1.cod_rubro.select();</script>";
?>
<script language="JavaScript">
		if(form1.impuesto1.selectedIndex==0){
			form1.impuesto1.selectedIndex=1;
		}
</script>
<script language="JavaScript">
    var index1 = document.form1.impuesto1_index.value;
    var index2 = document.form1.impuesto2_index.value;
    var index3 = document.form1.impuesto3_index.value;

    var index_cod_mon = document.form1.cod_moneda_index.value;
    var show_ayuda_doc = document.form1.show_ayuda_doc.value;
    
    if(index1!=""){
	document.form1.impuesto1.selectedIndex=index1;
    }
    if(index2!=""){
	document.form1.impuesto2.selectedIndex=index2;
    }
    if(index3!=""){
	document.form1.impuesto3.selectedIndex=index3;
    }
    if(index_cod_mon!=""){
	document.form1.cod_moneda.selectedIndex=index_cod_mon;
	//document.form1.tasa_cambio.value=document.form1.cod_moneda.value;		
    }
    if(show_ayuda_doc=="no"){
	    //hacerDesaparecer(ayuda_doc);
    }
	
</script>
<iframe name="iframe1" width="5" height="5"></iframe>
<script>chiche(document.iframe1,'Fecha_Documento',form1.fec_docu.value,'fec_docu','fec_docu');</script>
</body>
</html>
<?php pg_close();?>
