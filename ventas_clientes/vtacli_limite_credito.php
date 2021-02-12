<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}
	
if($boton=="Imprimir" or $boton=="Print") 
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='limite_credito_reporte.php' " );
	echo('</script>');
	}

if(strlen($v_periodo)==0 )
	{
	$v_periodo=date("Ym");
	}



if($boton=="Consultar" or strlen($v_periodo)>0 ) 
	{
	$sqladd=" and ch_periodo='".$v_periodo."' and ch_sucursal='".$almacen."' ";
	$v_sql="select LC.ch_sucursal, LC.ch_periodo, LC.ch_codigo_trabajador, TRA.ch_apellido_paterno||' '||TRA.ch_apellido_materno||' '||TRA.ch_nombre1||' '||TRA.ch_nombre2 , LC.nu_importe_credito, LC.nu_importe_consumo 
				from PLA_TA_LIMITE_CREDITO LC, PLA_TA_TRABAJADORES TRA
				where LC.ch_codigo_trabajador=TRA.ch_codigo_trabajador  ".$sqladd." ".$bddsql." ";
	$v_xsql=pg_query($conector_id,$v_sql);
	$v_ilimit=pg_num_rows($v_xsql);
	if($v_ilimit>0) { $numeroRegistros=$v_ilimit; }
	}
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>acosa.css" type="text/css">
<head> <title>SISTEMAWEB</title>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script> 
function activa(){
	// carga de frente el formulario con el foco en diad
	document.f_name.v_periodo.select()
	document.f_name.v_periodo.focus()
}
function cargavalor(valor){
	// carga de frente el formulario con el foco en diad
	document.f_name.m_clave.value = valor;
}

</script> 
</head>

<body onfocus="mueveReloj('f_name.reloj'); activa()"> 
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name" action="" method="post">

LIMITES DE CREDITO PERIODO <?php echo $v_periodo ; ?>  <BR>
<?php
$v_sql="select int_ta_sucursales.ch_sucursal, int_ta_sucursales.ch_nombre_sucursal from int_ta_sucursales , inv_ta_almacenes  where inv_ta_almacenes.ch_almacen like '%".trim($almacen)."%' and 
			inv_ta_almacenes.ch_clase_almacen='1' and inv_ta_almacenes.ch_sucursal=int_ta_sucursales.ch_sucursal ";
//echo $v_sql;
$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)	{	$v_sucursal=pg_result($v_xsql,0,0); $v_descsucu=pg_result($v_xsql,0,1); 	}
?>

SUCURSAL <?php echo $v_sucursal;?> <?php echo $v_descsucu; ?> <input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_name.reloj.blur()" > 

<input type="hidden" name="m_clave" size="16" maxlength="30" value='<?php echo $m_clave ; ?>'  tabindex="1"   >

<hr noshade>

	<table border="1">
	<tr> 
		<th colspan="5">CONSULTAR POR PERIODOS </th>
	</tr>
	<tr> 
		<th>PERIODO :</th>
		<th>
		<p>
		<input type="text" name="v_periodo" size="16" maxlength="10" value='<?php echo $v_periodo ; ?>'  tabindex="1"   >
<!--
		<a href="javascript:show_calendar('f_name.v_periodo');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
-->		
		</p>
		</th>
			
		<th><input type="submit" name="boton" value="Consultar"></th>
	</tr>
	</table>


<?php 
$var_pers="v_periodo=".$v_periodo;
include("../maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
?>


<table border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Imprimir"></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>

	</tr>
	<tr>
		<th>E/S</th>
		<th>PERIODO</th>
		<th>CODIGO</th>
		<th>TRABAJADOR</th>
		<th>IMPORTE CREDITO</th>
		<th>IMPORTE CONSUMIDO</th>
	</tr>
    <?php
	if(strlen($v_periodo)==0 )
		{
		$v_periodo="200407";
		}
	$sqladd=" and ch_periodo='".$v_periodo."' and ch_sucursal='".$almacen."' ";
	
	$v_sql="select LC.ch_sucursal, LC.ch_periodo, LC.ch_codigo_trabajador, TRA.ch_apellido_paterno||' '||TRA.ch_apellido_materno||' '||TRA.ch_nombre1||' '||TRA.ch_nombre2 , LC.nu_importe_credito, LC.nu_importe_consumo 
				from PLA_TA_LIMITE_CREDITO LC, PLA_TA_TRABAJADORES TRA
				where LC.ch_codigo_trabajador=TRA.ch_codigo_trabajador  ".$sqladd." ".$bddsql." ";

	$v_xsql=pg_exec($conector_id,$v_sql);
	$ilimit=pg_numrows($v_xsql);
	$irow2=0;
	
	if($ilimit>0) 
		{
		while($irow2<$ilimit) 
			{
			$a0=pg_result($v_xsql,$irow2,0);
			$a1=pg_result($v_xsql,$irow2,1);
			$a2=pg_result($v_xsql,$irow2,2);
			$a3=pg_result($v_xsql,$irow2,3);
			$a4=pg_result($v_xsql,$irow2,4);
			$a5=pg_result($v_xsql,$irow2,5);
					
			echo "<tr><td>".$a0."</td>";
			echo "<td>".$a1."</td>";
			echo "<td>".$a2."</td>";
			echo "<td>".$a3."</td>";
			echo "<td>".$a4."</td>"; 
			echo "<td>".$a5."</td></tr>";
			$irow2++;
			}
		}
	?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Imprimir"></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>



</form>
</body>
</html>
<?php

// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
//$clase_error->_error();

