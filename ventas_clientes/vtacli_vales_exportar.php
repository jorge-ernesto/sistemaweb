<?php
include("../valida_sess.php");
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
// carga los almacenes en un dropdown 
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}
$v_ilimit=0;
$v_par_almacen=" and c.ch_sucursal='".$almacen."' ";
if ( strlen($v_cliente)>0)
	{
	$v_par_cliente=" and c.ch_cliente='".$v_cliente."' ";
	}
	
if ( strlen($v_trabajador)>0)
	{
	$v_par_trabajador=" and c.ch_planilla='".$v_trabajador."' ";
	}


$v_sqlprn="select c.ch_cliente, cli.cli_razsocial, 
				c.ch_placa, c.nu_odometro, 
				c.dt_fecha, c.ch_documento, 
				d.ch_articulo, art.art_descripcion, 
				d.nu_cantidad,  d.nu_importe,  
				c.nu_importe   
		from val_ta_cabecera c, val_ta_detalle d, int_clientes cli, int_articulos art 
		where c.ch_sucursal=d.ch_sucursal and c.dt_fecha=d.dt_fecha and c.ch_documento=d.ch_documento 
				and c.ch_cliente=cli.cli_codigo and d.ch_articulo=art.art_codigo and c.dt_fecha
				between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'  	
				".$v_par_trabajador.$v_par_cliente.$v_par_almacen."
		order by c.ch_cliente,c.ch_placa,c.dt_fecha";
$v_xsqlprn=pg_exec($conector_id,$v_sqlprn);
$v_ilimit=pg_numrows($v_xsqlprn);

?>


<html>
<head>
<title>SISTEMAWEB</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

<form method='post' name="form2">
  <table width="767" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
    </tr>
  </table>
</form>
<div align="center"><font face="Arial, Helvetica, sans-serif">
	Detalle de Consumo al Credito  
	<font size="2"> <br>
	del: </font><font face="Arial, Helvetica, sans-serif"><font size="2"><?php echo $v_fecha_desde; ?></font></font><font size="2"> 
	al <?php echo $v_fecha_hasta;?></font></font><br>
	<font size="2"> <br>
	Cliente: </font><font face="Arial, Helvetica, sans-serif"><font size="2"><?php echo $v_cliente; ?></font></font><br>
	<font size="2"> <br>
	Trabajador: </font><font face="Arial, Helvetica, sans-serif"><font size="2"><?php echo $v_trabajador; ?></font></font><br>
</div>



<table width="771" border="1" cellpadding="0" cellspacing="0">
	<tr> 
		<td width="85"><font size='-4' face='Arial, Helvetica, sans-serif'>Fecha</font></td>
		<td width="106" align="center"><font size='-4' face='Arial, Helvetica, sans-serif'>Nota Despacho</font></td>
		<td width="247"><font size='-4' face='Arial, Helvetica, sans-serif'>Codigo/Nombre Articulo</font></td>
		<td width="69" align="center"><font size='-4' face='Arial, Helvetica, sans-serif'>Trabajador</font></td>
		<td width="97" align="right"><font size='-4' face='Arial, Helvetica, sans-serif'>Cantidad</font></td>
		<td width="85" align="right"><font size='-4' face='Arial, Helvetica, sans-serif'>Importe</font></td>
		<td width="135" align="right"><font size='-4' face='Arial, Helvetica, sans-serif'>Numeros deVales</font></td>
		<td width="114" align="right"><font size='-4' face='Arial, Helvetica, sans-serif'>Total</font></td>
	</tr>

	<?php
	$v_irow=0;
	$v_tot_cli_cant_doc=0;
	$v_tot_cli_impo=0;
	$v_cli_cant_doc=0;
	$v_cli_impo=0;	
	
	$v_antes=" ";

	
	if($v_ilimit>0) 
		{
		while($v_irow<$v_ilimit)
			{
			$a0=pg_result($v_xsqlprn,$v_irow,0);
			$a1=pg_result($v_xsqlprn,$v_irow,1);
			$a2=pg_result($v_xsqlprn,$v_irow,2);
			$a3=pg_result($v_xsqlprn,$v_irow,3);
			echo "<tr>";
			echo "<th align='left' colspan=3><font size='-4' face='Verdana, Helvetica, sans-serif'>CLIENTE: ".$a0." - ".$a1."   </font></th>"; 
			echo "<th align='left' colspan=2><font size='-4' face='Verdana, Helvetica, sans-serif'>Placa:".$a2." </font></th>";
			echo "<th align='left' colspan=5><font size='-4' face='Verdana, Helvetica, sans-serif'>Odometro:".$a3."</font></th>";
			echo "</tr>";
			$v_antes=$a0.$a2;
			while ($v_irow<$v_ilimit and $v_antes==$a0.$a2)
				{
				$a0=pg_result($v_xsqlprn,$v_irow,0);
				$a1=pg_result($v_xsqlprn,$v_irow,1);
				$a2=pg_result($v_xsqlprn,$v_irow,2);
				$a3=pg_result($v_xsqlprn,$v_irow,3);
				$a4=pg_result($v_xsqlprn,$v_irow,4);
				$a5=pg_result($v_xsqlprn,$v_irow,5);
				$a6=pg_result($v_xsqlprn,$v_irow,6);
				$a7=pg_result($v_xsqlprn,$v_irow,7);
				$a8=pg_result($v_xsqlprn,$v_irow,8);
				$a9=pg_result($v_xsqlprn,$v_irow,9);
				$a10=pg_result($v_xsqlprn,$v_irow,10);

				echo "<tr>";
				echo "<td><font size='-4' face='Arial, Helvetica, sans-serif'>".$a4."</font></td>";
				echo "<td><font size='-4' face='Arial, Helvetica, sans-serif'>".$a5."</font></td>";
				echo "<td><font size='-4' face='Arial, Helvetica, sans-serif'>".$a6." - ".$a7."</font></td>";
				echo "<td>&nbsp;</td>";
				echo "<td align='right'><font size='-4' face='Arial, Helvetica, sans-serif'>".number_format($a8, 2, '.', '')."</font></td>";
				echo "<td align='right'><font size='-4' face='Arial, Helvetica, sans-serif'>".number_format($a9, 2, '.', '')."</font></td>";
				echo "<td><font size='-4' face='Arial, Helvetica, sans-serif'>Vales Varios &nbsp; </font></td>";
				echo "<td align='right'><font size='-4' face='Arial, Helvetica, sans-serif'>".number_format($a10, 2, '.', '')."</font></td>";
				echo "</tr>";
				$v_cli_cant_doc++;
				$v_cli_impo=$v_cli_impo+$a9;
				$v_irow++;
				//antes de regresar al bucle tiene que comprobar el dato
				if ($v_irow<$v_ilimit )
					{
					$a0=pg_result($v_xsqlprn,$v_irow,0);
					$a2=pg_result($v_xsqlprn,$v_irow,2);
					}
				}
			echo "<tr>";
			echo "<td>&nbsp;</td>";
			echo "<th align='left' colspan=2><font size='-4' face='Verdana, Helvetica, sans-serif'> TOTAL CLIENTE:</font></th>";
			echo "<th><font size='-4' face='Verdana, Helvetica, sans-serif'>Doc:".$v_cli_cant_doc."</font></th>";
			echo "<td colspan=2>&nbsp;</td>";
			echo "<th align='right'><font size='-4' face='Verdana, Helvetica, sans-serif'>--></font></th>";
			echo "<th align='right'><font size='-4' face='Verdana, Helvetica, sans-serif'>".number_format($v_cli_impo, 2, '.', '')."</font></th>";
			echo "</tr>";
			$v_tot_cli_cant_doc=$v_tot_cli_cant_doc+$v_cli_cant_doc;
			$v_tot_cli_impo=$v_tot_cli_impo+$v_cli_impo;
			$v_cli_cant_doc=0;
			$v_cli_impo=0	;
			
			}
		echo "<tr>";
		echo "<td>&nbsp;</td>";
		echo "<th align='left' colspan=2><font size='-4' face='Verdana, Helvetica, sans-serif'>TOTAL GENERAL : </font></th>";
		echo "<th align='left' colspan=3><font size='-4' face='Verdana, Helvetica, sans-serif'>Doc:".$v_tot_cli_cant_doc."</font></th>";
		echo "<th align='right'><font size='-4' face='Verdana, Helvetica, sans-serif'>=========></font></th>";
		echo "<th align='right'><font size='-4' face='Verdana, Helvetica, sans-serif'>".number_format($v_tot_cli_impo, 2, '.', '')."</font></th>";
		echo "</tr>";
		}
	?>
	
</table>
</body>
</html>



<?php 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
