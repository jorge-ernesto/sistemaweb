<?php
/*Este archivo sirve para todos los tipos de formularios y lee su configuracion de 
inv_tipotransa basado en la variable fm que le llega, cmopara fm con tran_codigo en esa tabla*/
include("config.php");
include("../inc_top.php");
include("inv_addmov_support.php");

switch($accion){
case "Ingresar": /*Aqui estamos insertando un item de un array*/
$ITEMS = agregarItems($ITEMS,$fm,$almacen_interno,$alma_ori,$alma_des,$cod_proveedor,$tipo_doc,$serie_doc,$num_doc,$cod_articulo,$des_art_campo,$art_cantidad,$art_costo_uni,$nro_mov,$valor,$naturaleza,$des_pro,$des_doc);
session_register("ITEMS");
break;

case "TerminarIngreso": /*Insertamos en la tabla inv_movialma*/
	insertarRegistros($ITEMS);
	$CP = $ITEMS;
	$ITEMS = null;
	
	unset($ITEMS);
	session_register("ITEMS");
	session_register("CP");
	session_register("saludo");
break;

case "Eliminar": /*Eliminamos determinados registros del array*/
	$ITEMS = eliminarItems($ITEMS,$items);
	session_register("ITEMS");
break;

case "CancelarIngreso": /*Eliminamos todos los registros del array para comenzar denuevo*/
	$ITEMS=null;
	unset($ITEMS);
	session_register("ITEMS");
break;
}

/*Esto solo inicializa el formulario nada mas*/
$V = inicializarVariables($fm,trim($almacen));
$natu 	= $V["natu"];
$valor 	= $V["valor"];
$enti	= $V["enti"]; 
$ref	= $V["ref"];
$nro_mov= $V["nro_mov"];
$des_form=$V["des_form"];
$flag_ori=$V["flag_ori"];
$flag_des=$V["flag_des"];

$fecha_actual=date("Y-m-d");
$readonly_alma_des=$V["readonly_alma_des"];
$readonly_alma_ori=$V["readonly_alma_ori"];
$ayuda_alma_des = $V["ayuda_alma_des"];
$ayuda_alma_ori = $V["ayuda_alma_ori"];

$readonly_costo_uni = $V["readonly_costo_uni"];
/*Fin de la inicializacion del formulario*/

/*Por default deben de aparecer los almacenes de origen y destino de inv_tipo_transa y hacer
algunos filtros*/
	if($alma_ori=="" ||  $alma_des==""){
	$DEF = almacenesDefault($fm);
	$alma_ori = $DEF["tran_origen"];
	$alma_des = $DEF["tran_destino"];
	$alma_ori_campo = $DEF["tran_origen_campo"];
	$alma_des_campo = $DEF["tran_destino_campo"];
	
	if($alma_ori!=""){$read_ori="readonly='yes'";}
	if($alma_des!=""){$read_des="readonly='yes'";}
	}
/*Hasta aqui los filtros para la primera vez que se llama a esta pagina*/
	
/*Te regresa si tu almacen esta en blanco*/
if(trim($almacen)==""){
	?><script language="JavaScript">
		alert('No se ha registrado el almacen debes volver a entrar');
		location.href='/sistemaweb/login.php';
	</script><?php
}

/*Saca el numero de Orden solo para mostrar*/
$numero_orden = numeroOrden("01",trim($almacen),"select");
$numero_orden = completarCeros($numero_orden,8,"0");

/*Existe una variable autocompletar la cual se pone a yes y se manda cuando 
se quiere autocompletar algun campo*/
	if($autocompletar=="yes"){
		$AUTO = autocompletarDatos($cod_articulo,$valor,trim($almacen));
		//$cod_articulo = $R["cod_art"];
		$descripcion_articulo= $AUTO["des_art"];
		$art_costo_uni = $AUTO["costo_uni"];
	}
pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">
function mostrarAyudaAlmacenes(url,flag,cod,des,des_campo){
/*El flag es de origen y destino para que en inv_ta_almacenes pueda ubicar la clase y por eso listar*/
url = url+'?flag='+flag+'&cod='+cod+'&des='+des+'&des_campo='+des_campo;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function mandarDatos(form,accion){
	form.accion.value= accion;
	if(validarFormulario(form,accion)){form.submit();}
}

function mostrarAyudaOrdenCompra(url,prov,alma,many_alma,cod,fm){
url = url+"?proveedor="+prov+"&cod_almacen="+alma+"&many_alma="+many_alma+"&cod="+cod+"&fm="+fm; 
//alert(url);
window.open(url,'miwin','width=800,height=350,scrollbars=yes,menubar=no,left=0,top=20'); 
}


function getObj(name, nest) {
if (document.getElementById){
return document.getElementById(name).style;
}else if (document.all){
return document.all[name].style;
}else if (document.layers){
if (nest != ''){
return eval('document.'+nest+'.document.layers["'+name+'"]');
}
}else{
return document.layers[name];
}
}

//Hide/show layers functions
function showLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "visible";
}

function hideLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "hidden";
}

function mostrarFila(fila){
showLayer(fila);
}

function ocultarFila(fila){
hideLayer(fila);
}

function completarArticulo(campo,form){
	var long_campo = campo.value.length;
	if(long_campo==13){
		form.autocompletar.value = 'yes';
		form.submit();
	}
}

function validarFormulario(form,tipo){
	var bol = true;
	if(tipo=="Ingresar"){
	
	var msg = "";
	if(form.alma_ori.value==""){			bol=false;			msg = "El almacen de Origen esta en blanco";}
	if(form.alma_des.value==""){			bol=false;			msg = "El almacen de Destino esta en blanco";}	
	if(form.cod_proveedor.value==""){		bol=false;			msg = "Codigo de Proveedor en blanco";}
	if(form.tipo_doc.value==""){			bol=false;			msg = "Tipo de Documento en blanco";}
	if(form.serie_doc.value==""){			bol=false;			msg = "Serie del Documento en blanco";}
	if(form.num_doc.value==""){				bol=false;			msg = "Numero del documento en blanco";}
	if(form.cod_articulo.value==""){		bol=false;			msg = "Articulo no selecccionado";}
	if(form.art_cantidad.value==""){		bol=false;			msg = "Cantidad de Articulo no ingresada";}
	if(form.art_costo_uni.value==""){		bol=false;			msg = "Costo untario no ingresado";}

	}
	
	if(!bol){
		alert(msg);
	}
return bol;
}
</script>
<title>Ingreso x Compras</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form action="" method="post" name="form1">
  <table width="734" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="217"><div align="left">FORMULARIO</div></td>
      <td colspan="2"><?php echo $fm;?> - <?php echo $des_form;?> <input type="hidden" name="fm" value="<?php echo $fm;?>"><?php echo "CARAJO LA NATURALEZA ES!!!!!".$natu;?></td>
      <td width="128">&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="left">N&deg; FORMULARIO</div></td>
      <td colspan="2"><?php echo $nro_mov;?> <input type="hidden" name="nro_mov" value="<?php echo $nro_mov;?>">
        <input type="hidden" name="valor" value="<?php echo $valor;?>">
        <input type="hidden" name="naturaleza" value="<?php echo $natu;?>"></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="21"> <div align="left">FECHA</div></td>
      <td colspan="2"><?php echo $fecha_actual;?></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="24"> 
        <div align="left">ALMACEN ORIGEN</div></td>
      <td width="154"><input type="text" name="alma_ori" size="6" value="<?php echo $alma_ori;?>" <?php echo $read_ori;?> > 
        <?php if($alma_ori==""){ ?>
        <img src="../../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyudaAlmacenes('ayuda_almacenes.php','<?php echo $flag_ori;?>','alma_ori','almaori','alma_ori_campo');"> 
        <?php } ?>
        <input type="hidden" name="almacen_interno" value="<?php echo trim($almacen);?>"></td>
      <td width="252" id="almaori"><?php echo $alma_ori_campo;?> </td>
      <td><input type="text" name="alma_ori_campo" value="<?php echo $alma_ori_campo;?>"></td>
    </tr>
    <tr> 
      <td><div align="left">ALMACEN DESTINO</div></td>
      <td><input type="text" name="alma_des" size="6" value="<?php echo $alma_des;?>" <?php echo $read_des;?>> 
        <?php if($alma_des==""){ ?>
        <img src="../../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyudaAlmacenes('ayuda_almacenes.php','<?php echo $flag_des;?>','alma_des','almades','alma_des_campo');"></td>
      <?php } ?>
      <td id="almades"><?php echo $alma_des_campo;?></td>
      <td><input type="text" name="alma_des_campo" value="<?php echo $alma_des_campo;?>"></td>
    </tr>
    <!-- <?php /*PIDE O NO PROVEEDORES*/if($enti=="P"){ ?> -->
    <tr> 
      <td><div align="left">PROVEEDOR</div></td>
      <td><input type="text" name="cod_proveedor" size="12" value="<?php echo $cod_proveedor;?>"> 
        <img src="../../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('lista_ayuda.php','cod_proveedor','despro','proveedores','des_pro');"></td>
      <td id="despro"><?php echo $des_pro;?></td>
      <td><input type="text" name="des_pro" value="<?php echo $des_pro;?>"></td>
    </tr>
    <!-- <?php  } ?> -->
    <!-- <?php /*PIDE O NO TIPO Y # DE DOCUMENTO*/ 
  if($ref=="S"){ ?> -->
    <tr> 
      <td><div align="left">TIPO Y No DE DOCUMENTO</div></td>
      <td><input type="text" name="tipo_doc" size="12" value="<?php echo $tipo_doc;?>"> 
        <img src="../../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('lista_ayuda.php','tipo_doc','desdoc','documentos','des_doc');"><br> 
        <input type="text" name="serie_doc" size="3" value="<?php echo $serie_doc;?>" maxlength="3">
        - 
        <input type="text" name="num_doc" size="10" value="<?php echo $num_doc;?>" maxlength="7"></td>
      <td id="desdoc"><?php echo $des_doc;?></td>
      <td><input type="text" name="des_doc" value="<?php echo $des_doc;?>"></td>
    </tr>
    <!--<?php  } ?>-->
    <!-- <?php /*PIDE O NO INGRESO DIRECTO NO */
  if($natu=="2"){ ?> -->
    <tr> 
      <td height="21"> 
        <div align="left">INGRESO DIRECTO ?</div></td>
      <td>SI 
        <input type="radio" name="ing_dir" value="SI" onClick="javascript:ocultarFila('ord_compra') ,ingdir.value='SI' , mostrarFila('numero_orden') ;" <?php if($ingdir=="SI"){echo "checked";} ?>>
        | NO 
        <input type="radio" name="ing_dir" value="NO" onClick="javascript:mostrarFila('ord_compra') , ingdir.value='NO' , ocultarFila('numero_orden');" <?php if($ingdir=="NO"){echo "checked";} ?>></td>
      <td id="ord_compra">Orden de Compra 
        <input name="imgalmac04" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="javascript:mostrarAyudaOrdenCompra('cpag_ayuda_orddev.php',cod_proveedor.value,alma_des.value,'uno','form1.orden_compra','<?php echo $fm;?>')"></td>
      <td><input type="hidden" name="ingdir" value="<?php echo $ingdir;?>">
        <input type="hidden" name="orden_compra" value="<?php echo $orden_compra;?>"></td>
    </tr>
    <!-- <?php  } ?> -->
  </table>
  <br><div id="numero_orden"><?php if($fm=="01"){ echo "Numero de Orden ".$numero_orden;} ?>  </div>
  <table width="723" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="140" height="22">CODIGO </td>
      <td width="126">DESCRIPCION </td>
      <td width="100">CANTIDAD </td>
      <td colspan="2">COSTO UNITARIO</td>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td height="26"> <input type="text" name="cod_articulo" onKeyUp="completarArticulo(this,form1)" value="<?php echo $cod_articulo;?>"> 
        <img src="../../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('lista_ayuda.php','cod_articulo','des_articulo','articulos','des_art_campo','<?php echo $valor;?>');"> 
        <input type="hidden" name="des_art_campo"></td>
      <td id="des_articulo"><?php echo $descripcion_articulo;?></td>
      <td><input type="text" name="art_cantidad" size="20"></td>
      <td colspan="2"> <input type="text" name="art_costo_uni" value="<?php echo $art_costo_uni;?>" <?php echo $readonly_costo_uni;?>></td>
      <td colspan="2"> <input name="btn_ingresar" type="button" value="Ingresar" onClick="javascript:mandarDatos(form1,'Ingresar');"></td>
    </tr>
    <tr> 
      <td>CODIGO 
        <input type="hidden" name="autocompletar"></td>
      <td>DESCRIPCION </td>
      <td>CANTIDAD </td>
      <td width="94">COSTO UNITARIO</td>
      <td width="78">TOTAL</td>
      <td width="97">ORD. DE COMPRA</td>
      <td width="72">Borrar</td>
    </tr>
    <?php for($i=0;$i<count($ITEMS);$i++){ 
	$A = $ITEMS[$i];
	$total = ($A[10] * $A[11]);
	$sum_total = $sum_total + $total;
	$last_total_total = $sum_total;
	$_SESSION["last_total_total"]=$last_total_total;
	?>
    <tr> 
      <td><?php echo $A[8];?></td>
      <td><?php echo $A[9];?></td>
      <td><?php echo $A[10];?></td>
      <td><?php echo $A[11];?></td>
      <td><?php echo $total;?></td>
      <td><?php echo $numero_orden;?><?php echo "desdoc ".$A[15];?><?php echo "despro ".$A[16];?></td>
      <td><input type="checkbox" name="items[]" value="<?php echo $i;?>"></td>
    </tr>
    <?php } ?>
    <tr> 
      <td height="22">TOTALES</td>
      <td>&nbsp;</td>
      <td>&nbsp; </td>
      <td>&nbsp;</td>
      <td><input type="hidden" name="total_total" value="<?php echo $sum_total;?>">
        <?php echo $sum_total;?></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><input name="btn_eliminar" type="submit" id="btn_eliminar" value="Eliminar" onClick="javascript:mandarDatos(form1,'Eliminar');"></td>
      <td><input type="button" name="btn_mod_cabe" value="Cancelar Ingreso" onClick="javascript:mandarDatos(form1,'CancelarIngreso');"></td>
      <td><input type="submit" name="btn_regresar" value="Regresar"></td>
      <td colspan="2"><input type="button" name="btn_term_ing" value="Terminar Ingreso" onClick="javascript:mandarDatos(form1,'TerminarIngreso');"> 
        <input type="hidden" name="accion"></td>
      <td colspan="2">&nbsp;</td>
    </tr>
  </table>
</form>
<?php //para mostrar u ocultar la ayuda para ordenes de compras en caso de ingresos no directos
	if($ing_dir==""){
		?><script language="JavaScript">
			ocultarFila('ord_compra');
			ocultarFila('numero_orden');
		</script> <?php
	}
?>
<div align="center"></div>
</body>
</html>
