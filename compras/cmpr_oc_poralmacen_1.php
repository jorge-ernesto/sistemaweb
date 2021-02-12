<?php
include("../valida_sess.php");
//include("../menu_princ.php");
include("../config.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name2" method="post" >
<input type="hidden" name="v_fecha_desde" value="<?php echo $v_fecha_desde;?>">
<input type="hidden" name="v_fecha_hasta" value="<?php echo $v_fecha_hasta;?>">
<input type="hidden" name="v_almacen" value="<?php echo $v_almacen;?>">
<input type="hidden" name="chk1" value="<?php echo $chk1;?>">
<?php 
// Para saber la cantidad de registros sin el bddsql para el paginador


// Pregunta por almacen
if ( trim($v_almacen)=="")
	{$v_cond_almacen=" ";	}
else
	{$v_cond_almacen=" and CAB.COM_CAB_ALMACEN='$v_almacen' ";	}

if ( trim($v_proveedor)=="")
	{$v_cond_proveedor=" ";	}
else
	{$v_cond_proveedor=" and CAB.PRO_CODIGO='$v_proveedor' ";	}

if ( trim($v_articulo)=="")
	{$v_cond_articulo=" ";	}
else
	{$v_cond_articulo=" and CAB.COM_CAB_NUMORDEN IN (select COM_CAB_NUMORDEN from COM_DETALLE where ART_CODIGO='$v_articulo' ) ";	}

$v_sql1="select CAB.NUM_TIPDOCUMENTO,
			CAB.NUM_SERIEDOCUMENTO,
			CAB.COM_CAB_ALMACEN,
			CAB.COM_CAB_NUMORDEN,
			CAB.PRO_CODIGO,
			PRO.PRO_RAZSOCIAL,
			CAB.COM_CAB_FECHAORDEN,
			CAB.COM_CAB_MONEDA, 
			CAB.COM_CAB_TIPCAMBIO,
			CAB.COM_CAB_IMPORDEN,
			CAB.COM_CAB_ESTADO 
			from COM_CABECERA CAB, INT_PROVEEDORES PRO 
			where CAB.PRO_CODIGO=PRO.PRO_CODIGO 
				and CAB.COM_CAB_FECHAORDEN between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' 
				and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' 
			".$v_cond_almacen."
			".$v_cond_proveedor."
			".$v_cond_articulo."			
			order by  COM_CAB_ALMACEN, COM_CAB_NUMORDEN ";
	
$v_xsql1=pg_exec($conector_id,$v_sql1);
$v_ilimit1=pg_numrows($v_xsql1);

if($v_ilimit1>0) {		$numeroRegistros=$v_ilimit1;	}
$chk='checked';
$v_irow1=0;

$var_pers="v_fecha_desde=".$v_fecha_desde."&v_fecha_hasta=".$v_fecha_hasta."&v_almacen=".$v_almacen."&v_proveedor=".$v_proveedor."&v_articulo=".$v_articulo;
include("../maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";

$v_sql1="select CAB.NUM_TIPDOCUMENTO,
			CAB.NUM_SERIEDOCUMENTO,
			CAB.COM_CAB_ALMACEN,
			CAB.COM_CAB_NUMORDEN,
			CAB.PRO_CODIGO,
			PRO.PRO_RAZSOCIAL,
			CAB.COM_CAB_FECHAORDEN,
			CAB.COM_CAB_MONEDA, 
			CAB.COM_CAB_TIPCAMBIO,
			CAB.COM_CAB_IMPORDEN,
			CAB.COM_CAB_ESTADO 
			from COM_CABECERA CAB, INT_PROVEEDORES PRO 
			where CAB.PRO_CODIGO=PRO.PRO_CODIGO 
				and CAB.COM_CAB_FECHAORDEN between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' 
				and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' 
			".$v_cond_almacen."
			".$v_cond_proveedor."
			".$v_cond_articulo."			
			order by  COM_CAB_ALMACEN, COM_CAB_NUMORDEN 
			".$bddsql."  ";


$v_xsql1=pg_exec($conector_id,$v_sql1);
$v_ilimit1=pg_numrows($v_xsql1);


?>
	<table border="1" cellpadding="0" cellspacing="0">
		<tr>
			<?php
			if ( $chk0=="S" ){
				$chk00='checked';
				}
			else{
				$chk00=' ';
				}
			echo "<td><input type='checkbox' name='chk0' value='S' onclick='document.f_name2.submit()' $chk00 ></td>";
			?>
			<th>ALMACEN</th>
			<th>ORDEN COMPRA</th>
			<th>PROVEEDOR</th>
			<th>NOMBRE</th>
			<th>FECHA</th>
			<th>MONEDA</th>
			<th>T.CAMBIO</th>
			<th>IMPORTE</th>
			<th>ESTADO</th>
		</tr>
<?php




if($v_ilimit1>0) {
	while($v_irow1<$v_ilimit1) {

		// primero cargamos los resultados a variables
		$c_tipdocumento=pg_result($v_xsql1,$v_irow1  ,'NUM_TIPDOCUMENTO');
		$c_seriedocumento=pg_result($v_xsql1,$v_irow1  ,'NUM_SERIEDOCUMENTO');
		$c_almacen =pg_result($v_xsql1,$v_irow1  ,'COM_CAB_ALMACEN');
		$c_numorden=pg_result($v_xsql1,$v_irow1,'COM_CAB_NUMORDEN');
		$c_procodigo=pg_result($v_xsql1,$v_irow1,'PRO_CODIGO');
		$c_prorazon=pg_result($v_xsql1,$v_irow1,'PRO_RAZSOCIAL');
		$c_fechaorden=pg_result($v_xsql1,$v_irow1,'COM_CAB_FECHAORDEN');
		$c_moneda=pg_result($v_xsql1,$v_irow1,'COM_CAB_MONEDA');
		$c_tipcambio=pg_result($v_xsql1,$v_irow1,'COM_CAB_TIPCAMBIO');
		$c_imporden=pg_result($v_xsql1,$v_irow1,'COM_CAB_IMPORDEN');
		$c_estado=pg_result($v_xsql1,$v_irow1,'COM_CAB_ESTADO');
		
		// cargamos el checkbox de regreso con el valor a buscar
			if ($chk0=='S' )
				{
				$checkbox[$v_irow1]=$c_almacen.$c_numorden;
				}
			if ( $checkbox[$v_irow1]==$c_almacen.$c_numorden  ){
				$chk='checked';
				$checkbox[$v_irow1]=$c_almacen.$c_numorden;
				}
			else{
				$chk=' ';
				}
	    echo '<tr>';
		echo "<td><input type='checkbox' name='checkbox[$v_irow1]' value='".$c_almacen.$c_numorden."' onclick='document.f_name2.submit()' $chk ></td>";
		echo "<td>&nbsp;".$c_almacen."</td>";
		echo "<td>&nbsp;".$c_numorden."</td>";
		echo "<td>&nbsp;".$c_procodigo."</td>";
		echo "<td>&nbsp;".$c_prorazon."</td>";
		echo "<td>&nbsp;".$c_fechaorden."</td>";
		echo "<td>&nbsp;".$c_moneda."</td>";
		echo "<td align='right'>&nbsp;".$c_tipcambio."</td>";
		echo "<td align='right'>&nbsp;".$c_imporden."</td>";
		$v_sql="select  TAB_ELEMENTO, 
						TAB_DESC_BREVE 
						from INT_TABLA_GENERAL 
						where TAB_TABLA='ESTO' and TAB_ELEMENTO='$c_estado' ";
		$v_xsql=pg_query($conector_id,$v_sql);
		if(pg_numrows($v_xsql)>0)	{	$v_descri=pg_result($v_xsql,0,1);	}
		echo "<td>&nbsp;".$v_descri."</td>";
//		echo "<td>&nbsp;".$c_estado."</td>";
		echo "</tr>";
		if ( $checkbox[$v_irow1]==$c_almacen.$c_numorden ){
			$v_sql2="select DET.ART_CODIGO, 
						ART.ART_DESCRIPCION,
						DET.COM_DET_CANTIDADPEDIDA,
						DET.COM_DET_CANTIDADATENDIDA,							
						DET.COM_DET_PRECIO,
						DET.COM_DET_DESCUENTO1,
						DET.COM_DET_IMPUESTO1,
						DET.COM_DET_IMPARTICULO,
						DET.COM_DET_ESTADO
					from COM_DETALLE DET, INT_ARTICULOS ART 
					where DET.ART_CODIGO=ART.ART_CODIGO and  
						DET.NUM_TIPDOCUMENTO='$c_tipdocumento' and
						DET.NUM_SERIEDOCUMENTO='$c_seriedocumento' and
						DET.COM_CAB_NUMORDEN='$c_numorden' and
						DET.PRO_CODIGO='$c_procodigo' 
					" ;

			$v_xsql2=pg_exec($conector_id,$v_sql2);
			$v_ilimit2=pg_numrows($v_xsql2);
			$v_irow2=0;
			if($v_ilimit2>0) {
				echo "<tr>";
				echo "<th>&nbsp;</th>";
				echo "<th>&nbsp;</th>";
				echo "<th>DESCRIPCION</th>";
				echo "<th>CNT.PEDIDA</th>";
				echo "<th>CNT.ATENDIDA</th>";
				echo "<th>PRECIO</th>";
				echo "<th>DESCUENTO</th>";
				echo "<th>IMPUESTO</th>";
				echo "<th>IMPORTE ART</th>";
				echo "<th>ESTADO</th>";
				
				echo "</tr>";

				while($v_irow2<$v_ilimit2){
					$c_articulo= pg_result($v_xsql2,$v_irow2,'ART_CODIGO');
					$c_descripcion= pg_result($v_xsql2,$v_irow2,'ART_DESCRIPCION');
					$c_cantpedida=  pg_result($v_xsql2,$v_irow2,'COM_DET_CANTIDADPEDIDA');
					$c_cantatendida=pg_result($v_xsql2,$v_irow2,'COM_DET_CANTIDADATENDIDA');
					$c_precio=      pg_result($v_xsql2,$v_irow2,'COM_DET_PRECIO');
					$c_descuento1=  pg_result($v_xsql2,$v_irow2,'COM_DET_DESCUENTO1');
					$c_impuesto1=   pg_result($v_xsql2,$v_irow2,'COM_DET_IMPUESTO1');
					$c_imparticulo= pg_result($v_xsql2,$v_irow2,'COM_DET_IMPARTICULO');
					$c_estado=      pg_result($v_xsql2,$v_irow2,'COM_DET_ESTADO');
					echo "<tr>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					//echo "<td>&nbsp;$c_descripcion</td>";
					echo '<td bgcolor="#CCCC99" onMouseOver="window.status=\'Codigo de Item\'; overlib(\'-'.$c_articulo.'-\'); return true;" onMouseOut="window.status=\'\'; nd(); return true;"> ';
					echo '&nbsp;'.$c_descripcion;
					echo '</td>';
					echo "<td align='right'>&nbsp;$c_cantpedida</td>";
					echo "<td align='right'>&nbsp;$c_cantatendida</td>";
					echo "<td align='right'>&nbsp;$c_precio</td>";
					echo "<td align='right'>&nbsp;$c_descuento1</td>";
					echo "<td align='right'>&nbsp;$c_impuesto1</td>";
					echo "<td align='right'>&nbsp;$c_imparticulo</td>";
					$v_sql="select  TAB_ELEMENTO, 
									TAB_DESC_BREVE 
									from INT_TABLA_GENERAL 
									where TAB_TABLA='ESTO' and TAB_ELEMENTO='$c_estado' ";
					$v_xsql=pg_query($conector_id,$v_sql);
					if(pg_numrows($v_xsql)>0)	{	$v_descri=pg_result($v_xsql,0,1);	}
					echo "<td>&nbsp;".$v_descri."</td>";
					echo "</tr>";
					$v_irow2++;
					}
				}
			}
		$v_irow1++;
		}
	}
?>
	<tr> 
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>


</form>
</html>