<?php
include("../valida_sess.php");
include("config.php");
include("cpag_ayuda_orddev_support.php");
/*echo $proveedor."<br>";
echo $cod_almacen."<br>";
echo $many_alma."<br>";*/

if($busca==""){$busca="null";}
$almacen = trim($almacen);
//$L = separarCadena($lista2,"-");
//$lista = $L[0];
//$almacen_lista = $L[1]; //es el almacen de la orden de compra seleccionada
//$fecha_lista =  $L[2]; //es la fecha de la orden de compra seleccionada
//$num_guia = $L[3];

switch($accion){

	case "Seleccionar":
			$lista = "";
	
			for($k=0;$k<count($lista_ar);$k++){ //FOR1
				$lista2 = $lista_ar[$k];
				$L = separarCadena($lista2,"-");
				if($lista!=$L[0]){ //IFFOR
				$lista = $L[0];
				$almacen_lista = $L[1]; //es el almacen de la orden de compra seleccionada
				$fecha_lista =  $L[2]; //es la fecha de la orden de compra seleccionada
				$num_guia = $L[3];
				/*$proveedor; 
				$many_alma;
				$cod_almacen*/
				$orden_compra = $lista;
				$rs = ayudaOrdDev($proveedor , $almacen , $orden_compra, $many_alma, $busca);
				$q2 = "select c.mov_numero,c.art_codigo,a.art_descripcion,c.mov_cantidad
				,  c.mov_costounitario
				,c.mov_costototal, to_char(c.mov_fecha,'dd/mm/yyyy') as mov_fecha,c.com_tipo_compra 
				, c.com_serie_compra , c.com_num_compra  
				,c.tran_codigo, to_char(c.mov_fecha,'dd/mm/yyyy') as mov_fecha , c.mov_docurefe
				from inv_ta_compras_devoluciones c , int_articulos a
				where a.art_codigo = c.art_codigo and 
				mov_numero = '$lista' and mov_almacen='$almacen_lista' 
				and c.tran_codigo='05' or c.tran_codigo='22' ";
				$rs2 = pg_exec($q2);
				//echo $q2;
				
				/*Agregado para no hacer 3 pasos al seleccionar 03/10/2004*/
				$items = null;
				for($i=0;$i<pg_numrows($rs2);$i++){  
					$B = pg_fetch_array($rs2,$i);
												//Fecha - Cod. Articulo Desc. Articulo
					$art_descripcion[$i] 	=	$B[6]." - ".$B[1]." ".$B[2]; //ok
					$art_codigo[$i]			=	$B[1]; //ok
					$art_cantidad[$i]		=	$B[3]; //ok
					$art_costo_uni[$i]		=	$B[4]; //ok
					$art_costo_total[$i]	=	$B[5]; //ok

					$art_costo_uni_dol[$i]		=	round($B[4]/$tasa_cambio,4); //ok
					$art_costo_total_dol[$i]	=	round($B[5]/$tasa_cambio,2); //ok
					
					//$ord_compra,$ord_almacen,$proveedor
					$com_tipo_compra[$i]	=	$B[7]; //ok
					$com_serie_compra[$i]	=	$B[8]; //ok
					$com_num_compra[$i]		=	$B[9]; //ok
					
					$mov_fecha[$i]			=	$B[6];  //OK --NO INSERTABA POR ESTOS 2 DATOS
					$tran_codigo[$i]		=	$B[10];  //OK --NO INSERTABA POR ESTOS 2 DATOS
					
					$items[$i]				=	$i; //ok
					
					$guias[$i] 				=	$B['mov_docurefe'];
      				$fechas[$i] 			=	$B['mov_fecha'];
				
				}
				$ord_compra = $lista;
	 			$ord_almacen = $almacen_lista;
			
				$ITEMSCD_DEV = $_SESSION["ITEMSCD_DEV"];
				$ITEMSCD_DEV = registrarItems($art_descripcion,$art_codigo,$art_cantidad,	$art_costo_uni
				,$art_costo_total,	$art_costo_uni_dol
				,$art_costo_total_dol,$ord_compra,$ord_almacen,$items,$proveedor
				,$com_tipo_compra , $com_serie_compra , $com_num_compra 
				,$tran_codigo , $mov_fecha
				,$guias , $fechas
				,$ITEMSCD_DEV);
				$_SESSION["ITEMSCD_DEV"] = $ITEMSCD_DEV;
				/*Agregado para no hacer 2 pasos al seleccionar 03/10/2004*/
			
			}//IFFOR
			}//FOR1
			
			$busca="null";

			$orden_compra = "null";
			$rs = ayudaOrdDev($proveedor , $almacen , $orden_compra, $many_alma, $busca);
			
	break;
	
	case "Buscar":
			if($busca==""){$busca="null";}

			if($orden_compra==""){$orden_compra = "null";}
			$rs = ayudaOrdDev($proveedor , $almacen , $orden_compra, $many_alma, $busca);
	break;
	
	case "":
			if($orden_compra==""){$orden_compra = "null";}
			$rs = ayudaOrdDev($proveedor , $almacen , $orden_compra, $many_alma, $busca);
	break;
	case "Agregar":
		//unset($ITEMSCD);
		
		$ITEMSCD_DEV = $_SESSION["ITEMSCD_DEV"];
		$ITEMSCD_DEV = registrarItems($art_descripcion,$art_codigo,$art_cantidad,	$art_costo_uni
		,$art_costo_total,$ord_compra,$ord_almacen,$items,$proveedor
		,$com_tipo_compra , $com_serie_compra , $com_num_compra 
		,$tran_codigo , $mov_fecha
		,$guias , $fechas 
		,$ITEMSCD_DEV);
		$_SESSION["ITEMSCD_DEV"] = $ITEMSCD_DEV;
		
	break;
	
	
	case "Eliminar":
		$ITEMSCD_DEV = $_SESSION["ITEMSCD_DEV"];
		$ITEMSCD_DEV = eliminarItems($ITEMSCD_DEV,$items2);
		$_SESSION["ITEMSCD_DEV"] = $ITEMSCD_DEV;
	break;

	case "Cancelar":
	
		unset($ITEMSCD_DEV);
		$ITEMSCD_DEV = null;
		$_SESSION["ITEMSCD_DEV"] = $ITEMSCD_DEV;
		unset($ITEMSCD_DEV);
	break;
	
	case "Terminar":
		$ITEMSCD_DEV = $_SESSION["ITEMSCD_DEV"];
		$ITEMSCD_DEV = actualizarArray($ord_compra2,$cod_art2,$art_costo_uni2,$art_costo_uni_dol2,$ITEMSCD_DEV);
		$_SESSION["ITEMSCD_DEV"] = $ITEMSCD_DEV;
		?>
		<script>window.close();</script>
		<?php
	break;
	
	case "Actualizar_Costos":
		$q = "select CPAGAR_ACTUALIZAR_COSTOS(trim('$proveedor'),trim('$cod_moneda'))";
		pg_exec($q);
	
	break;
	
	
	case "Filtrar":
	
		if(trim($orden_compra)==""){$orden_compra = "null";}
		
		$rs = ayudaOrdDevFecha($proveedor , $almacen , $orden_compra, $many_alma, $busca, $fec_desde, $fec_hasta);
	
	break;
	

}
$total_dev = sumatoriaArray($ITEMSCD_DEV,"art_costo_total");
$total_dev_dol = sumatoriaArray($ITEMSCD_DEV,"art_costo_total_dol");
pasarValor($total_dev,$total_cmp,$tasa_cambio,$cod_documento);
$_SESSION["total_dev"] = $total_dev;
$_SESSION["total_dev_dol"] = $total_dev_dol;

$total_cmp = sumatoriaArray($ITEMSCD,"art_costo_total");
$total_cmp_dol = sumatoriaArray($ITEMSCD,"art_costo_total_dol");



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Devoluciones</title>

<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,cod,des){
var valor = lista.value;
//alert(valor);
//opener.document.form1.cod_proveedor.value = valor;
//alert("opener.document."+cod+".value = '"+valor+"'");
eval("opener.document."+cod+".value = '"+valor+"'");
form.submit();
}

function mandarDatos(form,opcion){

	form.accion.value = opcion;
	form.submit();
}
/*
function cuantificar(campo_cantidad, campo_precio , campo_total, campo_total_total){
	
	var cantidad 	= 	parseFloat(campo_cantidad.value);
	var precio	= 	parseFloat(campo_precio.value);
	var total	= 	parseFloat(campo_total.value);
	
	if( !(isNaN(cantidad * precio)) ){
	var total_total = campo_total_total.value;
	total_total = total_total - total;
	
	campo_total.value = (cantidad * precio).toFixed(3);
	
	total_total = total_total + (cantidad * precio);
	campo_total_total.value = total_total.toFixed(3);
	}
	
}
*/
function cuantificar(campo_cantidad, campo_precio , campo_total, campo_total_total, tcambio , tipo, campo_precio_des, campo_total_des, campo_total_total_des ){
	
	var cantidad 	= 	parseFloat(campo_cantidad.value );
	var precio	= 	parseFloat(campo_precio.value);
	var total	= 	parseFloat(campo_total.value);

	var precio_des	= 	parseFloat(campo_precio_des.value);
	var total_des	= 	parseFloat(campo_total_des.value);

	var tipo_cambio	= 	parseFloat(tcambio.value);


	if( !(isNaN(cantidad * precio)) )
	{
		var total_total = campo_total_total.value;

		total_total = total_total - total;
		total_total = total_total + (cantidad * precio);
		
		campo_total.value = (cantidad * precio).toFixed(3);
		campo_total_total.value = total_total.toFixed(3);

		if (tipo=="D")
			{
			precio_des=(precio/tipo_cambio).toFixed(3);
			}
		else
			{
			precio_des=(precio*tipo_cambio).toFixed(3);
			}
		
		
		campo_precio_des.value = precio_des;


		var total_total_des = campo_total_total_des.value;

		total_total_des = total_total_des - total_des;
		total_total_des = total_total_des + (cantidad * precio_des);
		
		campo_total_des.value = (cantidad * precio_des).toFixed(3);
		campo_total_total_des.value = total_total_des.toFixed(3);
		

	}
	
	
}




//Esta funcion seleccina todos los items de iuna lista
function seleccionarTodos(lista,form){
	
	for (i=0; i<lista.length; i++) { 
	lista.options[i].selected = true; 
	} 
	mandarDatos(form,'Seleccionar');
}

</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
<!--  -->
</head>

<body>
<form name="form1" method="post" action="">
  <input type="text" name="busca">
  Buscar Devoluciones 
  <input type="button" name="Buscar" value="Buscar" onClick="mandarDatos(form1,'Buscar')">
  <input type="hidden" name="proveedor" value="<?php echo $proveedor;?>">
  <input type="hidden" name="cod_almacen" value="<?php echo $cod_almacen;?>">
  <input type="hidden" name="many_alma" value="<?php echo $many_alma;?>">
  <input type="hidden" name="tasa_cambio" value="<?php echo $tasa_cambio;?>">
  <input type="hidden" name="cod_documento" value="<?php echo $cod_documento;?>">
  <input type="hidden" name="cod_moneda" value="<?php echo $cod_moneda;?>">
  <input type="button" name="btn_act_costos" value="Actualizar Costos" onClick="mandarDatos(form1,'Actualizar_Costos')">
  <br>
  Desde : 
  <input type="text" name="fec_desde" size="11" value="<?php echo $fec_desde;?>">
  Hasta: 
  <input type="text" name="fec_hasta" size="11" value="<?php echo $fec_hasta;?>">
  <input type="button" name="btn_filtro_fechas" value="Filtrar" onClick="mandarDatos(form1,'Filtrar')">
  <?php echo $fec_desde;?> <br>
  <select id="lista_devoluciones" name="lista_ar[]" size="7" multiple>
    <!--Esta lista desplegable manda un dato compuesto separado por guion, asi que estos son eparados mas
  arriba en la funcion separarCadena-->
    <?php if(pg_numrows($rs)==0 && $accion!= "Filtrar"){
	if($busca==""){$busca="null";}
	if($orden_compra==""){$orden_compra = "null";}
	$rs = ayudaOrdDev($proveedor , $almacen , $orden_compra, $many_alma, $busca);
}
  ?>
    <?php for($i=0;$i<pg_numrows($rs);$i++){
  	$A = pg_fetch_array($rs,$i);
	$I_TMP["ord_compra"] = $A[10];
	$I_TMP["art_codigo"] = $A["art_codigo"];
  	if(verificarRepetidos($I_TMP,$ITEMSCD_DEV)){
  	print "<option value='$A[10]-$A[2]-$A[1]-$A[14]'>$A[6]-$A[1] - $A[14] - $A[9] $A[3] - $A[4] - $A[15] - $A[16]</option>";
  	}
  } ?>
  </select>
  <br><input type="hidden" name="accion">
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="mandarDatos(form1,'Seleccionar')">
  <input type="button" name="Seleccionar_Todo" value="Seleccionar Todo" onClick="javascript:seleccionarTodos(lista_devoluciones,form1)">
  <br>
  <!--Guia Nro: <?php echo $num_guia." - ".$fecha_lista;?>  -->
  <input type="hidden" name="ord_compra" value="<?php echo $lista;?>">
  <input type="hidden" name="ord_almacen" value="<?php echo $almacen_lista;?>">
  <!--Proveedor: <?php echo $proveedor;?> Almacen: <?php echo $almacen_lista;?> -->
  <!--<br>
  <table width="721" border="0" cellspacing="0">
    <tr> 
      <td width="150"><div align="center">Articulo</div></td>
      <td width="67">Guia</td>
      <td width="67">Fecha</td>
      <td width="65"><div align="center">Cantidad</div></td>
      <td width="91"><div align="center">Costo Unitario</div></td>
      <td width="82"><div align="center">Costo Total</div></td>
      <td width="75"><div align="center">Marcar </div></td>
      <td width="94"><input type="hidden" name="accion"> <input type="button" name="Submit3" value="Agregar" onClick="mandarDatos(form1,'Agregar')"></td>
    </tr>
    
    <?php for($i=0;$i<pg_numrows($rs2);$i++){  
	$B = pg_fetch_array($rs2,$i);
	?>
    <tr> 
      <td><div align="center"> 
          <input type="text" name="art_descripcion[]" value="<?php echo $B[2]?>" size="30">
        </div></td>
      <td><input type="text" name="guias[]" value="<?php echo $B['mov_docurefe']?>" size="8"></td>
      <td><input type="text" name="fechas[]" value="<?php echo $B['mov_fecha']?>" size="8"></td>
      <td><div align="center"> 
          <input type="hidden" name="art_codigo[]" value="<?php echo $B[1]?>">
          <input type="text" name="art_cantidad[]" value="<?php echo $B[3]?>" size="7">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="art_costo_uni[]" value="<?php echo $B[4]?>" size="7">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="art_costo_total[]" value="<?php echo $B[5]?>" size="7">
        </div></td>
      <td><div align="center"> 
          <input type="hidden" name="com_tipo_compra[]" value="<?php echo $B[7];?>">
          <input type="hidden" name="com_serie_compra[]" value="<?php echo $B[8];?>">
          <input type="hidden" name="com_num_compra[]" value="<?php echo $B[9];?>">
          <input type="checkbox" name="items[]" value="<?php echo $i;?>">
        </div></td>
      <td><input type="hidden" name="tran_codigo[]" value="<?php echo $B[10];?>"> 
        <input type="hidden" name="mov_fecha[]" value="<?php echo $B[11];?>"></td>
    </tr>
     <?php } ?> 
    <tr> 
      <td colspan="3"><div align="center"> 
          <input type="button" name="Submit" value="Agregar" onClick="mandarDatos(form1,'Agregar')">
        </div></td>
      <td><div align="center"> </div></td>
      <td><div align="center"></div></td>
      <td><div align="center"></div></td>
      <td colspan="2"><div align="center"></div></td>
    </tr>
  </table>
  <br>
  Registros Hechos -->
  <table width="770" border="0">
    <tr> 
      <td width="74"><div align="center">Num. Guia</div></td>
      <td width="55"><div align="center">Fecha</div></td>
      <td width="376"><div align="center">Fecha - Cod. Articulo Desc. Articulo</div></td>
      <td width="62"><div align="center">Cantidad </div></td>
      <td width="52"><div align="center">Costo Unitario</div></td>
      <td width="60"><div align="center">Costo <br>
          Total</div></td>
      <td width="61"><input type="button" name="Submit2" value="Eliminar" onClick="mandarDatos(form1,'Eliminar')"></td>
    </tr>
    <?php for($i=0;$i<count($ITEMSCD_DEV);$i++){
	$C = $ITEMSCD_DEV[$i];
	?>
    <tr> 
      <td><div align="center"> 
          <input type="hidden" name="ord_compra2[]" value="<?php echo $C["ord_compra"];?>">
          <input type="text" name="guias2[]" size="13" value="<?php echo $C["num_guia"];?>">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="fechas2[]" size="11" value="<?php echo $C["fecha"];?>">
        </div></td>
      <td><div align="center"> 
          <input type="hidden" name="cod_art2[]" value="<?php echo $C["art_codigo"];?>">
          <input type="text" name="des_art2[]" size="80" value="<?php echo $C["art_descripcion"];?>">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="art_cantidad2[]" size="7" value="<?php echo $C["art_cantidad"];?>" id="cant<?php echo $i;?>">
        </div></td>

      <td><div align="center"> 
          <input type="text" name="art_costo_uni2[]" size="7" value="<?php echo $C["art_costo_uni"];?>" id="costo<?php echo $i;?>" onKeyUp="javascript:cuantificar( cant<?php echo $i;?>, costo<?php echo $i;?> , tot<?php echo $i;?> , total_dev , tasa_cambio, 'D', costo_dol<?php echo $i;?> , tot_dol<?php echo $i;?> , total_dev_dol   )">
        </div></td>
      <td><div align="center"> 
          <input type="text" name="art_costo_total2[]" size="7" value="<?php echo $C["art_costo_total"];?>" readonly="yes" id="tot<?php echo $i;?>">
        </div></td>
		
      <td><input type="checkbox" name="items2[]" value="<?php echo $i;?>"> <input type="hidden" name="mov_fecha2[]" value="<?php echo $C["mov_fecha"];?>" size="3">
        <input type="hidden" name="tran_codigo2[]" value="<?php echo $C["tran_codigo"];?>" size="3">
		</td>


		<td>
		<div align="center"> 
			<input type="text" name="art_costo_uni_dol2[]" size="7" value="<?php echo $C["art_costo_uni_dol"] ;?>" id="costo_dol<?php echo $i;?>" onKeyUp="javascript:cuantificar(cant<?php echo $i;?>, costo_dol<?php echo $i;?> , tot_dol<?php echo $i;?> , total_dev_dol, tasa_cambio, 'M', costo<?php echo $i;?> , tot<?php echo $i;?> , total_dev )">
		</div>
		</td>
		<td>
		<div align="center"> 
			<input type="text" name="art_costo_total_dol2[]" size="7" value="<?php echo $C["art_costo_total_dol"] ;?>" readonly="yes" id="tot_dol<?php echo $i;?>">
		</div>
		</td>


    </tr>
    <?php } ?>
    <tr> 
      <td colspan="3">Total Compras Soles: 
        <input type="text" name="total_cmp_tmp" size="15" readonly="yes" value="<?php echo $total_cmp;?>"></td>
      <td colspan="2"><div align="right">Total Devoluciones Soles:</div></td>
      <td><input type="text" name="total_dev" size="12" readonly="yes" value="<?php echo $total_dev;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><input type="text" name="total_dev_dol" id="total_dev_dol" size="12" readonly="yes" value="<?php echo $total_dev_dol;?>"></td>	  

    </tr>
    <tr> 
      <td colspan="2"><div align="center"> 
          <input type="button" name="Submit22" value="Eliminar" onClick="mandarDatos(form1,'Eliminar')">
        </div></td>
      <td><div align="center"> 
          <input type="button" name="Terminar" value="Terminar" onClick="mandarDatos(form1,'Terminar')">
        </div></td>
      <td><div align="center"> 
          <input type="button" name="Cancelar" value="Cancelar" onClick="mandarDatos(form1,'Cancelar')">
        </div></td>
      <td><div align="center"></div></td>
      <td colspan="2"><div align="center">
          <input type="button" name="Submit23" value="Reset" onClick="mandarDatos(form1,'Eliminar')">
          <input type="hidden" name="hdtotal_dev">
        </div></td>
    </tr>
  </table>
  <p>&nbsp;</p>
</form>
</body>
</html>
<?php pg_close(); ?>
