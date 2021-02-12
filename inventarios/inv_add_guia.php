<?php
include("../functions.php");
include("js/funciones.php");
include("store_procedures.php");
require("../clases/funciones.php");
include("../menu_princ.php");
require("inv_add_guia_support.php");
$funcion = new class_funciones;
$coneccion=$funcion->conectar("","","","","");
// crea la clase para controlar errores
//$clase_error = new OpensoftError;
// conectar con la base de datos
if($serie==""){$serie="01";}
$correlativo_doc = correlativo_documento('09',$serie,"select");
$correlativo_doc = str_pad($correlativo_doc,10,"0",STR_PAD_LEFT);

switch($accion){

	case "Agregar":

		if($tipo_guia=="I"){
			$art_codigo = $cod_item;
		}else{$art_codigo = "null";}
		
		if($tipo_guia=="A"){
			$ch_codigo_activo = $cod_item;
		}else{$ch_codigo_activo = "null";}
		
		$ARR = $_SESSION["AR"];
		$ARR = agregarItems($ARR,$tipo_docu,$serie_guia,$num_guia,$art_codigo,$ch_codigo_activo
				,$des_item_campo,$tipo_guia,$cantidad_item,$cod_cliente,$cod_proveedor,$tipo_transa
				,$tipo_entidad
				,$_SESSION["almacen"] , $alma_ori , $alma_des
				,$glosa1 , $glosa2 , $mov_numero
				,$mov_naturaleza	,$valorizado	,$cod_item);
		$_SESSION["AR"] = $ARR;
		
	break;

	case "Terminar":

		$ARR = $_SESSION["AR"];
		if(count($ARR)>0 || $tipo_guia=="V"){
			if($tipo_guia=="V"){
				$art_codigo = "null";
				$ch_codigo_activo = "null";
				$cantidad_item = 0;
				$ARR = agregarItems($ARR,$tipo_docu,$serie_guia,$num_guia,$art_codigo,$ch_codigo_activo
				,$des_item_campo,$tipo_guia,$cantidad_item,$cod_cliente,$cod_proveedor,$tipo_transa
				,$tipo_entidad
				,$_SESSION["almacen"] , $alma_ori , $alma_des
				,$glosa1 , $glosa2 , $mov_numero
				,$mov_naturaleza	, $valorizado	,$cod_item);
				grabarDatos($ARR);
			}else{
				grabarDatos($ARR);
			}
		?>
		<script language="JavaScript">
			window.location.href='/sistemaweb/inventarios/inv_add_guia.php?accion=Cancelar';
		</script>
		<?php
		}else{ ?>
			<script language="JavaScript">
			alert('No hay ningun item registrado <?php echo $tipo_guia;?>');
			</script>
			<?php
		}
	break;

	
	case "Cancelar":
		
		$ARR = null;
		$_SESSION["AR"] = $ARR;
		unset($AR);
		session_unregister("AR");
	break;


	case "Eliminar":
		$ARR = $_SESSION["AR"];
		$ARR = eliminarItems($ARR,$items);
		$_SESSION["AR"] = $ARR;
		
	break;
}

if($fecha_inicio==""){
	$fecha_proceso=fecha_aprosys();
	$fecha_inicio=$fecha_proceso;
}

$ARR = $_SESSION["AR"];
//echo "El asrray ARR tiene ".count($ARR);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Ingreso de Guia de Remision</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">

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

function hacerDesaparecer(id){
	var cmd = id+".style.display='none'";
	eval(cmd);
}

function hacerAparecer(id){
	var cmd = id+".style.display=''";
	eval(cmd);
}

function cceros(v_var,v_lon,k_var)
	{
	var v_var2= v_var.value.replace(/^\s*|\s*$/g,"");
	var lon1  = v_var.value.length;
	var lon2  = v_lon-lon1
	
	for(i=0;i<lon2;i++)
	{
		v_var2='0'+v_var2;
	}
	eval("document.form1."+k_var+".value=v_var2");



	}
	
function inicializarFormulario(form,tipo_guia){
	
	switch(tipo_guia){
	
		case "I":
			ocultarFila('fila_glosa2');
			hacerDesaparecer('fila_cliente');
			hacerDesaparecer('fila_proveedor');
			
			hacerDesaparecer('fila_ayuda_activos');
			hacerAparecer('fila_ayuda_articulos');
			//document.form1.tipo_de_item.value = 'I';

			//alert(tipo_guia);
		break;
	
		case "A":
			ocultarFila("fila_glosa2");
			hacerDesaparecer('fila_cliente');
			hacerDesaparecer('fila_proveedor');
			
			hacerDesaparecer('fila_ayuda_articulos');
			hacerAparecer('fila_ayuda_activos');
			//document.form1.tipo_de_item.value = 'A';
			//alert(tipo_guia);
		break;
		
		case "V":
			//alert(tipo_guia);
			mostrarFila("fila_glosa2");
			hacerDesaparecer('fila_cliente');
			hacerDesaparecer('fila_proveedor');
			ocultarFila("fila_btn_agregar");
			
			hacerDesaparecer('fila_btn_agregar');
		break;
	}	
	var index = form.tipo_guia.selectedIndex;
	
	form.combo_valor.value = form.tipo_guia.value;
	form.tipo_guia.options[index].selected = true;
	form.combo_index.value=index;
	
}

function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function adicional(tipo_ayuda){
	var tipo_transa = document.form1.tipo_transa.value;
	
	switch(tipo_ayuda){
	
		case "guias_tipo_transa":
			
			var url="/sistemaweb/inventarios/inv_add_guia_iframe.php?accion=Filtrar&codigo="+tipo_transa;
			
			document.iframe1.location=url;
		break;
		
		case "clientes":
			var cod_cliente = document.form1.cod_cliente.value;
			var url="/sistemaweb/inventarios/inv_add_guia_iframe.php?accion=Destino&tipo_ayuda=clientes&codigo="+tipo_transa+"&cod_cliente="+cod_cliente;
			//alert(url);
			document.iframe1.location=url;
		break;
		
		case "proveedores":
			var cod_proveedor = document.form1.cod_proveedor.value;
			var url="/sistemaweb/inventarios/inv_add_guia_iframe.php?accion=Destino&tipo_ayuda=proveedores&codigo="+tipo_transa+"&cod_proveedor="+cod_proveedor;
			//alert(url);
			document.iframe1.location=url;
		break;
		
	}
}

function enviarDatos(form, tipo){

	if(validarFormulario(form,tipo)){
		form.accion.value = tipo;
		form.submit();
	}
	
}

function validarFormulario(form,tipo){

	var ret = true;
	var cod_item = form.cod_item.value;
	var cantidad_item = form.cantidad_item.value;
	
	switch (tipo){
	
		case "Agregar":
			
			if(cod_item==""){
				ret = false;
				alert('Codigo de articulo invalido');
			}
			if(cantidad_item==""){
				ret = false;
				alert('Cantidad no ingresada');
			}
			
		break;
	
	}
	

return ret;

}

function hacerRO(campo){
	//campo.disabled = true;
}

function correlativoGuia(serie,combo){
	var url = "/sistemaweb/inventarios/inv_add_guia_iframe.php?accion=Correlativo_Guia&serie="+serie;
	document.form1.serie_guia_index.value=combo.selectedIndex;
	document.iframe1.location=url;
}

function mostrarAyudaAlmacenes(url,flag,cod,des,des_campo){
/*El flag es de origen y destino para que en inv_ta_almacenes pueda ubicar la clase y por eso listar*/
url = url+'?flag='+flag+'&cod='+cod+'&des='+des+'&des_campo='+des_campo;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function chiche(chiche,tipo_guia,codigo){
	var url = "";
	var redir = false;
switch(chiche){	
	case "completar_items":
		if(document.form1.cantidad_item.value==""){
		url = "/sistemaweb/inventarios/inv_add_guia_iframe.php?accion="+chiche+"&codigo="+codigo+"&tipo_guia="+tipo_guia;
		redir = true;
		}
	break;
}


	if(redir){document.iframe1.location=url;}

}
</script>
</head>

<body>
<strong>Ingreso de Guias</strong> 
<form name="form1" method="post" action="">
  <table width="772" border="0">
    <tr> 
      <td height="25"> <div align="right"> 
          <input type="hidden" name="accion">
          Serie:</div></td>
      <td colspan="2"><select name="serie_guia" onChange="javascript:correlativoGuia(serie_guia.value,this);">
          <?php if($serie_guia!=""){$w = "and num_seriedocumento='$serie_guia'";}
		  $rs10 = pg_exec(" select num_seriedocumento from int_num_documentos where num_tipdocumento='09' $w order by num_seriedocumento ");
	  	for($i=0;$i<pg_numrows($rs10);$i++){
		$A = pg_fetch_array($rs10,$i);
	  ?>
          <option value="<?php echo $A[0];?>"><?php echo $A[0];?></option>
          <?php } ?>
        </select> 
        <?php if($serie_guia_index==""){$serie_guia_index=0;} ?>
        <input type="hidden" name="serie_guia_index" value="<?php echo $serie_guia_index;?>" readonly="true"></td>
      <td width="145">Numero: 
        <input type="text" name="num_guia" value="<?php echo $correlativo_doc;?>" size="13" maxlength="10" readonly="false"></td>
      <td width="1">&nbsp;</td>
      <td width="99">&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td width="120"><div align="right">Tipo de Guia:</div></td>
      <td colspan="2"><select name="tipo_guia" onChange="javascript:inicializarFormulario(form1,form1.tipo_guia.value),hacerRO(this);">
        <?php $TIPOS_GUIA["I"] = "Inventarios";
			$TIPOS_GUIA["A"] = "Activos";
			$TIPOS_GUIA["V"] = "Varios";
		if($tipo_guia==""){ ?>	  
		  <option value="I">Inventarios</option>
          <option value="A">Activos</option>
          <option value="V">Varios</option>
        <?php }else{ ?>
			<option value="<?php echo $tipo_guia;?>"><?php echo $TIPOS_GUIA[$tipo_guia];?></option>
		<?php } ?>
		</select> <input type="hidden" name="combo_index" value="<?php echo $combo_index;?>" readonly="true"> 
        <?php if($combo_seleccionado==""){$combo_seleccionado="no";} ?>
      </td>
      <td colspan="3"><div align="right"> 
          <input type="hidden" name="combo_valor" value="<?php echo $combo_valor;?>" readonly="true">
          <input type="hidden" name="combo_seleccionado" value="<?php echo $combo_seleccionado;?>" readonly="true">
          Fecha Traslado: 
          <input type="text" name="fecha_inicio" readonly="yes" size="13" value="<?php echo $fecha_inicio;?>">
        </div></td>
      <td width="98">&nbsp;</td>
      <td width="135"><input type="hidden" name="tipo_docu" value="09" readonly="true"> 
        <input type="hidden" name="tipo_entidad" value="<?php echo $tipo_entidad;?>" readonly="true">
		<input type="hidden" name="mov_numero" value="<?php echo $mov_numero;?>" readonly="true">
        <input type="hidden" name="mov_naturaleza" value="<?php echo $mov_naturaleza;?>" readonly="true">
        <input type="hidden" name="valorizado" value="<?php echo $valorizado;?>" readonly="true"> 
      </td>	
	</tr>
    <tr> 
      <td><div align="right">Movimiento:</div></td>
      <td colspan="2"><input type="text" name="tipo_transa" value="<?php echo $tipo_transa;?>" size="7"> 
        <img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('guias/lista_ayuda.php','tipo_transa','desc_tipotransa','guias_tipo_transa','desc_tipo_transa');"> 
        <input type="hidden" name="desc_tipo_transa" value="<?php echo $desc_tipo_transa;?>" readonly="true"> 
      </td>
      <td colspan="2" align="left"><div id="desc_tipotransa"></div></td>
      <td>&nbsp;</td>
      <td><input type="hidden" name="art_stock" value="<?php echo $art_stock;?>" readonly="true"> 
        <input type="hidden" name="art_costo_uni" value="<?php echo $art_costo_uni;?>" readonly="true"></td>
      <td>&nbsp;</td>
    </tr>
    <tr id="fila_almaori"> 
      <td><div align="right">Almacen Origen:</div></td>
      <td width="39"><input type="text" name="alma_ori" size="6" value="<?php echo $alma_ori;?>" <?php echo $read_ori;?> > 
      </td><?php $flag_ori="3";?>
      <td width="101" id="fila_ayuda_almaori"><img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyudaAlmacenes('js/ayuda_almacenes.php','<?php echo $flag_ori;?>','alma_ori','almaori','alma_ori_campo');"></td>
      <td colspan="3" id="almaori"><?php if($alma_ori_campo!=""){echo $alma_ori_campo;} ?></td>
      <td><input type="hidden" name="alma_ori_campo" value="<?php echo $alma_ori_campo;?>"></td>
      <td>&nbsp;</td>
    </tr>
    <tr id="fila_almades"> 
      <td><div align="right">Almacen Destino:</div></td>
      <td><input type="text" name="alma_des" size="6" value="<?php echo $alma_des;?>" <?php echo $read_des;?>> 
      </td><?php $flag_des = "3";?>
      <td id="fila_ayuda_almades"><img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyudaAlmacenes('js/ayuda_almacenes.php','<?php echo $flag_des;?>','alma_des','almades','alma_des_campo');"></td>
      <td colspan="3" id="almades">
        <?php if($alma_des_campo!=""){echo $alma_des_campo;} ?>
      </td>
      <td><input type="hidden" name="alma_des_campo" value="<?php echo $alma_des_campo;?>"></td>
      <td>&nbsp;</td>
    </tr>
    <tr id="fila_cliente"> 
      <td><div align="right">Clientes:</div></td>
      <td colspan="2"><input type="text" name="cod_cliente" value="<?php echo $cod_cliente;?>" size="15"> 
        <img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyuda('guias/lista_ayuda.php','cod_cliente','desc__cliente','clientes','desc_cliente');"> 
        <input type="hidden" name="desc_cliente" value="<?php echo $desc_cliente;?>" readonly="true"> 
      </td>
      <td colspan="2" id="desc__cliente">
        <?php if($desc_cliente!=""){echo $desc_cliente;} ?>
      </td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr id="fila_proveedor"> 
      <td><div align="right">Proveedores:</div></td>
      <td colspan="2"><input type="text" name="cod_proveedor" value="<?php echo $cod_proveedor;?>" size="15"> 
        <img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onClick="javascript:mostrarAyuda('guias/lista_ayuda.php','cod_proveedor','desc__proveedor','proveedores','desc_proveedor');"> 
        <input type="hidden" name="desc_proveedor" value="<?php echo $desc_proveedor;?>" readonly="true"> 
      </td>
      <td colspan="2" id="desc__proveedor">
        <?php if($desc_proveedor!=""){echo $desc_proveedor;} ?>
      </td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right">Destino:</div></td>
      <td colspan="5"><input type="text" name="destino" value="<?php echo $destino;?>" size="60"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right">Punto de Llegada:</div></td>
      <td colspan="5"><input type="text" name="punto_llegada" value="<?php echo $punto_llegada;?>" size="60"> 
      </td>
      <td><input type="button" name="btn_terminar" value="Terminar" onClick="javascript:enviarDatos(form1,'Terminar');"></td>
      <td>&nbsp;</td>
    </tr>
    <tr id="fila_glosa1"> 
      <td><div align="right">Glosa1:</div></td>
      <td colspan="5"><input type="text" name="glosa1" size="60" value="<?php echo $glosa1;?>"></td>
      <td><input type="button" name="btn_cancelar" value="Cancelar" onClick="javascript:enviarDatos(form1,'Cancelar');"></td>
      <td>&nbsp;</td>
    </tr>
    <tr id="fila_glosa2"> 
      <td><div align="right">Glosa2: </div></td>
      <td colspan="5"><input type="text" name="glosa2" size="60" value="<?php echo $glosa2;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <div id="fila_btn_agregar">Agregar Items 
    <table width="770" border="1">
      <tr> 
        <td colspan="2">Codigo: </td>
        <td width="256" id="des_item"><div align="center">Descripcion 
            <input type="hidden" name="des_item_campo" value="<?php echo $des_item_campo;?>" readonly="true" >
          </div></td>
        <td><div align="center">Cantidad</div></td>
        <td><div align="right">Marcar</div></td>
        <td>&nbsp;</td>
      </tr>
      <tr> 
        <td width="86"> <input type="text" name="cod_item" value="<?php echo "";?>" size="18" ></td>
        <td width="18"> <div id="fila_ayuda_activos"><img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('guias/lista_ayuda.php','cod_item','celda_item','activo_fijo','des_item_campo','<?php echo $valor;?>');"></div>
          <div id="fila_ayuda_articulos"><img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('guias/lista_ayuda.php','cod_item','celda_item','articulos2','des_item_campo','<?php echo $valor;?>');"></div></td>
        <td id="celda_item">&nbsp;</td>
        <td width="77"><div align="center"> 
            <input type="text" name="cantidad_item" size="7" onFocus="javascript:chiche('completar_items',tipo_guia.value,cod_item.value);">
          </div></td>
        <td width="118"><div align="right">
            <input type="button" name="btn_agregar" value="Agregar" onClick="javascript:enviarDatos(form1,'Agregar');">
          </div></td>
        <td width="175"><input type="button" name="btn_elimininar" value="Eliminar" onClick="javascript:enviarDatos(form1,'Eliminar');"></td>
      </tr>
      <?php for($i=0;$i<count($ARR);$i++){
	  	$fila = $ARR[$i];
	  ?>
      <tr> 
        <td height="22" colspan="2"> 
          <?php if($tipo_guia=="I"){echo $fila["art_codigo"];}else{echo $fila["ch_codigo_activo"];} ?>
        </td>
        <td> 
          <div align="center"><?php echo $fila["des_item_campo"];?></div></td>
        <td align="center"><?php echo $fila["cantidad_item"];?> </td>
        <td><div align="right">
            <input type="checkbox" name="items[]" value="<?php echo $i;?>">
          </div>
          <div align="right"></div></td>
        <td></td>
      </tr>
      <?php } ?>
    </table>
  </div>
  <br>
  <script language="JavaScript">
inicializarFormulario(form1,form1.tipo_guia.value);
//alert('---> '+form1.tipo_guia.value);
var combo_seleccionado = form1.combo_seleccionado.value;
var serie_guia_index = form1.serie_guia_index.value; 
 if(combo_seleccionado=="si"){
 	var index = form1.combo_index.value;
 		
	form1.tipo_guia.options[index].selected = true;
	form1.tipo_guia.disabled = true;
	
	
 }
 //form1.serie_guia.options[serie_guia_index].selected = true;
 document.form1.serie_guia.options[0].selected = true;
</script>

<?php
	//switch para hacer ciertos chiches en el formulario
	
	switch($accion){
	
		case "Agregar":

			?>
				<script>
					document.form1.cod_item.focus();
				</script>
			<?php

		break;
	
	}
?>


</form>
<iframe name="iframe1" width="0" height="0"></iframe>
</body>
</html>
<?php pg_close();?>