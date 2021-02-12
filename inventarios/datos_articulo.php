<?php
include("../functions.php");
include("js/funciones.php");
require("../clases/funciones.php");
include("store_procedures.php");
$funcion = new class_funciones;
$coneccion = $funcion->conectar("","","","","");

include("js/inv_addmov_support.php");
$resultado = 0;
$flag = 0;
$CI = pg_exec("select a.art_descripcion as descripcion,tg.tab_descripcion as linea, util_fn_precio_articulo('".$articulo."') as precio, tg.tab_num_01 as margen from int_articulos a left join int_tabla_general tg on tg.tab_tabla='20' and tg.tab_elemento = a.art_linea where a.art_codigo = '".$articulo."';");
$AI = pg_fetch_array($CI,0);
$descripcion = $AI["descripcion"];
$linea = $AI["linea"];
$precio_actual = $AI["precio"];
$margen = $AI["margen"];

switch($_POST['cambiar']) {
	case "Cambiar Precio":
		$flag = 1;
		$nprecio = number_format($precio_sug, 2, '.', ',');
		$resultado = pg_exec("update fac_lista_precios set pre_precio_act1=".number_format($precio_sug, 2, '.', ',')." where art_codigo= '".$articulo."' and pre_lista_precio= (select trim(par_valor) from int_parametros where trim(par_nombre)='lista precio');");
		echo "Se guardo el precio";
	break;
}
?>

<style>
#mensaje{
	font-family: Tahoma, Verdana, Arial;
	font-size: 11px;
	color: #707070;
	background-color: #FFFFFF;
	border-width:0;
}
</style>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Datos de Articulo</title>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
<script language='JavaScript' type='text/javascript'>

	function cumpleReglas(simpleTexto) {
		//la pasamos por una poderosa expresión regular
		var expresion = new RegExp("^(|([0-9]{1,4}(\\.([0-9]{1,2})?)?))$");
		//si pasa la prueba, es válida
		if(expresion.test(simpleTexto))
			return true;
		return false;
        }

    	//ESTA FUNCIÓN REVISA QUE TODO LO QUE SE ESCRIBA ESTÉ EN ORDEN
   	function revisaCadena(textItem) {

		var textoAnterior = '';
		//si comienza con un punto, le agregamos un cero
		if(textItem.value.substring(0,1) == '.') 
			textItem.value = '0' + textItem.value;

            	//si no cumples las reglas, no te dejo escribir
            	if(!cumpleReglas(textItem.value)){
                	textItem.value = textoAnterior;
		}else if(textItem.value < document.getElementsByName('vali')[0].value){ //Precio nuevo tiene que ser mayor al actual
			alert('El nuevo precio sugerido debe ser mayor al actual');
                	textItem.value = document.getElementsByName('vali')[0].value;
            	}else{ //todo en orden
               	 	textoAnterior = textItem.value;
		}

        }

	/*function Guardar(){
		document.getElementById("accion").value = 'Cambiar Precio';
		document.getElementById("form").submit();
		document.getElementById("form").submit();
		document.getElementById("form").submit();
		location.reload();
		document.getElementById("mensaje").value = '*** Datos almacenados correctamente ***';
	}*/
</script>
</head>
<body>
<center>
<h3 style="font-weight:bold; color:blue">Actualizar precio de venta de acuerdo a Margen</h3>
<br/>
<form action="" method="post" name="form" id="form">
<table border="1">
<tr>
	<td width="230" style="font-weight:bold; color:blue">Nro. Ingreso por Compra</td>
	<td width="230" style="font-weight:bold; color:blue" align="right"><?php echo $orden;?></td>
</tr>
<tr>
	<td style="font-weight:bold;">Codigo</td>
	<td style="font-weight:bold;" align="right"><?php echo $articulo;?></td>
</tr>
<tr>
	<td style="font-weight:bold;">Producto</td>
	<td style="font-weight:bold;" align="right"><?php echo $descripcion;?></td>
</tr>
<tr>
	<td style="font-weight:bold;">Linea </td>
	<td style="font-weight:bold;" align="right"><?php echo $linea;?></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
        <?php $CI = pg_exec("select floor(util_fn_igv()) as igv");
	    $AI = pg_fetch_array($CI,0);
	    $c_porcentaje_igv = $AI["igv"];
	?> 
<tr>
	<td style="font-weight:bold">Costo con IGV</td>
	<td style="font-weight:bold" align="right"><?php echo number_format($costo * (1 + $c_porcentaje_igv/100), 2, '.', ',');?></td>
</tr>
<tr>
	<td>Costo sin IGV</td>
	<td align="right"><?php echo number_format($costo, 2, '.', ',');?></td>
</tr>
<tr>
	<td>Cantidad</td>
	<td align="right"><?php echo number_format($cant, 2, '.', ',');?></td>
</tr>
<tr>
	<td>Sub-Total</td>
	<td align="right"><?php echo number_format($cant * $costo * (1 + $c_porcentaje_igv/100), 2, '.', ',');?></td>
</tr>
<tr>
	<td style="font-weight:bold" BGCOLOR="yellow" >Precio Actual</td>
	<td style="font-weight:bold" BGCOLOR="yellow" align="right"><?php echo number_format($precio_actual, 2, '.', ',');?></td>
</tr>
<tr>
	<td>Margen</td>
	<td align="right"><?php echo number_format($margen, 2, '.', ',');?></td>
</tr>
<tr>
	<td style="font-weight:bold; color:blue" BGCOLOR="yellow" >Precio Sugerido</td>
	<?php if ($flag==0) { ?>
	<td align="right" BGCOLOR="yellow" ><input style="text-align: right; font-weight:bold; color:blue" type="text" name="precio_sug" value="<?php echo number_format($costo * (1 + $c_porcentaje_igv/100) * (1 + $margen/100), 2, '.', ',');?>" onblur="revisaCadena(this)">
	<input type="hidden" name="vali" id="vali" value="<?php echo number_format($costo * (1 + $c_porcentaje_igv/100) * (1 + $margen/100), 2, '.', ',');?>" /></td><!--onKeyUp-->
	<?php } else { ?>
	<td align="right" BGCOLOR="yellow" >
	<input style="text-align: right; font-weight:bold; color:blue" type="text" id="precio_sug" name="precio_sug" value="<?php echo $nprecio;?>" onblur="revisaCadena(this)" />
	<input type="hidden" name="vali" id="vali" value="<?php echo $nprecio;?>" /></td><!--onKeyUp-->
	<?php } ?>

</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<th><input type="submit" name="cambiar" value="Cambiar Precio"></th>
	<th><input type="button" name="salir" value="Cancelar" onclick="window.close();"></th>
</tr>
</table>
<br/>
<input type="text" class="input" id="mensaje" name="mensaje" style="text-align: center; font-weight:bold; color:blue" size="50">
</form>
</center>
</body>
</html>
