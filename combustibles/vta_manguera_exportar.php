<?php
include("../valida_sess.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}


// carga los almacenes en un dropdown 
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' and ch_almacen='".$almacen."' order by cod");
$nombre_almacen=pg_result($v_xsqlalma,0,1);

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	$v_turno_desde=0;
	$v_turno_hasta=0;
	}
$v_ilimit=0;


	$v_sqlprn="select trim(tmplado), trim(tmpsurt),tmpprod, tmpprec, tmpcanttik, tmpimpotik, tmpprei, tmppref, tmpcini, tmpcfin, tmpcantcon, tmpimpocon, tmpcantdif, tmpimpodif 
					from tempo order by tmplado, tmpsurt";
	$v_xsqlprn=pg_exec($v_sqlprn);
	$v_ilimit=pg_numrows($v_xsqlprn);

?>
<html>
<head>
<title>sistemaweb</title>
<title>sistemaweb</title>
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
<div align="left"><font face="Arial, Helvetica, sans-serif">
SISTEMA INTEGRADO - <?php echo $nombre_almacen;?>
</div>
<font size=1>

<?php
	echo "<table width='720' border='2' cellspacing=0 height='81'>";
	echo "<tr>";
	echo "<th colspan=18 align='center'><font size=3> <BR>
	REPORTE COMPARATIVO VENTA MANGUERAS Desde: ".$v_fecha_desde." Turno: ".$v_turno_desde." 
	Hasta: ".$v_fecha_hasta." Turno: ".$v_turno_hasta." <P></P>	</th>";
	echo "</tr>";
	echo "<tr>";
	echo "	<th width='40'><font size=1>Lado</td>";
	echo "	<th width='40' align='center'><font size=1>Mang</td>";
	echo "	<th width='50' align='center'><font size=1>Producto</td>";
	echo "	<th width='50' align='center'><font size=1>Precio Tickets</td>";
	echo "	<th width='80' align='center'><font size=1>Cantidad Tickets</td>";	
	echo "	<th width='80' align='center'><font size=1>Importe Tickets</td>";	
	echo "	<th width='50' align='center'><font size=1>Precio Inicial</td>";
	echo "	<th width='50' align='center'><font size=1>Precio Final</td>";
	echo "	<th width='80' align='center'><font size=1>Contometro Cant. Inicial</td>";
	echo "	<th width='80' align='center'><font size=1>Contometro Cant. Final</td>";
	echo "	<th width='80' align='center'><font size=1>Cantidad Contometro</td>";	
	echo "	<th width='80' align='center'><font size=1>Importe Contometro</td>";	
	echo "	<th width='80' align='center'><font size=1>Diferencia x Cantidad</td>";	
	echo "	<th width='80' align='center'><font size=1>Diferencia x Soles</td>";
	
	//echo "	<td width='100'>Cont.S/.Inicial</td>";	
	//echo "	<td width='100'>Cont.S/.Final</td>";
	echo "</tr>";

	
	$v_clave=" ";
	
	$tcanttik=0;
	$timpotik=0;
	$tcantcon=0;
	$timpocon=0;			
	$tcantdif=0;
	$timpodif=0;			
	$stcanttik=0;
	$stimpotik=0;
	$stcantcon=0;
	$stimpocon=0;			
	$stcantdif=0;
	$stimpodif=0;			
	
	if($v_ilimit>0) 
		{
		$v_irow=0;
		while($v_irow<$v_ilimit)
			{
			$a0=pg_result($v_xsqlprn,$v_irow,0);
			$a1=pg_result($v_xsqlprn,$v_irow,1);
			$v_clave=$a0;
			while($v_irow<$v_ilimit and $v_clave==$a0 )
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
				$a11=pg_result($v_xsqlprn,$v_irow,11);
				$a12=pg_result($v_xsqlprn,$v_irow,12);
				$a13=pg_result($v_xsqlprn,$v_irow,13);
				
				echo "<tr>";
				echo "<td align='left' ><font size=1>&nbsp;".$a0." </td>"; 
				echo "<td align='center' ><font size=1>&nbsp;".$a1." </td>"; 
				echo "<td align='left' ><font size=1>&nbsp;".$a2." </td>";
				echo "<td align='center' ><font size=1>&nbsp;".number_format($a3, 2, '.', '')." </td>";
				echo "<td align='right' ><font size=1>&nbsp;".number_format($a4, 2, '.', '')." </td>";
				echo "<td align='right' ><font size=1>&nbsp;".number_format($a5, 2, '.', '')." </td>";
				echo "<td align='center' ><font size=1>&nbsp;".number_format($a6, 2, '.', '')." </td>";
				echo "<td align='center' ><font size=1>&nbsp;".number_format($a7, 2, '.', '')." </td>";
				echo "<td align='right' ><font size=1>&nbsp;".number_format($a8, 2, '.', '')." </td>";
				echo "<td align='right' ><font size=1>&nbsp;".number_format($a9, 2, '.', '')." </td>";
				echo "<td align='right' ><font size=1>&nbsp;".number_format($a10, 2, '.', '')." </td>";
				echo "<td align='right' ><font size=1>&nbsp;".number_format($a11, 2, '.', '')." </td>";
				echo "<td align='center' ><font size=1>&nbsp;".number_format($a12, 2, '.', '')." </td>";
				echo "<td align='center' ><font size=1>&nbsp;".number_format($a13, 2, '.', '')." </td>";
				echo "</tr>";
				$stcanttik=$stcanttik+$a4;
				$stimpotik=$stimpotik+$a5;
				$stcantcon=$stcantcon+$a10;
				$stimpocon=$stimpocon+$a11;
				$stcantdif=$stcantdif+$a12;
				$stimpodif=$stimpodif+$a13;

				$tcanttik=$tcanttik+$a4;
				$timpotik=$timpotik+$a5;
				$tcantcon=$tcantcon+$a10;
				$timpocon=$timpocon+$a11;
				$tcantdif=$tcantdif+$a12;
				$timpodif=$timpodif+$a13;


				
				$v_irow++;
				if ($v_irow<$v_ilimit )
					{
					$a0=pg_result($v_xsqlprn,$v_irow,0);
					$a1=pg_result($v_xsqlprn,$v_irow,1);
					}
				}
			
			echo "<tr>";
			echo "<th align='left' >&nbsp;</td>"; 
			echo "<th align='center' >&nbsp;</td>"; 
			echo "<th align='left' ><font size=1>&nbsp;SUBTOTAL</td>";
			echo "<th align='center' >&nbsp;</td>";
			echo "<th align='right' ><font size=1>&nbsp;".number_format($stcanttik, 2, '.', '')." </td>";
			echo "<th align='right' ><font size=1>&nbsp;".number_format($stimpotik, 2, '.', '')." </td>";
			echo "<th align='center' >&nbsp;</td>";
			echo "<th align='center' >&nbsp;</td>";
			echo "<th align='right' >&nbsp;</td>";
			echo "<th align='right' >&nbsp;</td>";
			echo "<th align='right' ><font size=1>&nbsp;".number_format($stcantcon, 2, '.', '')." </td>";
			echo "<th align='right' ><font size=1>&nbsp;".number_format($stimpocon, 2, '.', '')." </td>";
			echo "<th align='center' ><font size=1>&nbsp;".number_format($stcantdif, 2, '.', '')." </td>";
			echo "<th align='center' ><font size=1>&nbsp;".number_format($stimpodif, 2, '.', '')." </td>";
			echo "</tr>";
			$stcanttik=0;
			$stimpotik=0;
			$stcantcon=0;
			$stimpocon=0;			
			$stcantdif=0;
			$stimpodif=0;			
			}
		echo "<tr>";
		echo "<font size=4>";
		echo "<th align='left' >&nbsp;</td>"; 
		echo "<th align='center' >&nbsp;</td>"; 
		echo "<th align='left' ><font size=1>&nbsp;TOTAL</td>";
		echo "<th align='center' >&nbsp;</td>";
		echo "<th align='right' ><font size=1>&nbsp;".number_format($tcanttik, 2, '.', '')." </td>";
		echo "<th align='right' ><font size=1>&nbsp;".number_format($timpotik, 2, '.', '')." </td>";
		echo "<th align='center' >&nbsp;</td>";
		echo "<th align='center' >&nbsp;</td>";
		echo "<th align='right' >&nbsp;</td>";
		echo "<th align='right' >&nbsp;</td>";
		echo "<th align='right' ><font size=1>&nbsp;".number_format($tcantcon, 2, '.', '')." </td>";
		echo "<th align='right' ><font size=1>&nbsp;".number_format($timpocon, 2, '.', '')." </td>";
		echo "<th align='center' ><font size=1>&nbsp;".number_format($tcantdif, 2, '.', '')." </td>";
		echo "<th align='center' ><font size=1>&nbsp;".number_format($timpodif, 2, '.', '')." </td>";
		echo "</font>";
		echo "</tr>";
		$tcanttik=0;
		$timpotik=0;
		$tcantcon=0;
		$timpocon=0;			
		$tcantdif=0;
		$timpodif=0;			
		}
	echo "</table>";

	echo "<br>";
	
?>
	
</font>	
<br>
<br>


</form>
</body>
</html>
<?php 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
