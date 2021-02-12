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
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' and ch_almacen='".$almacen."' order by cod");
$nombre_almacen=pg_result($v_xsqlalma,0,1);

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
	{
	$v_fecha_desde=date("d/m/Y");
	$v_fecha_hasta=date("d/m/Y");
	}
$v_ilimit=0;
	$v_sqlprn="select es,pump,ch_nombrebreve,dia,fecha,veloc,lineas,trans from pos_ta_afericiones, comb_ta_combustibles 
			where trim(pos_ta_afericiones.codigo)=trim(comb_ta_combustibles.ch_codigocombustible) and dia between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."'  	
			order by es, pump, codigo, dia, fecha ";
	$v_xsqlprn=pg_exec($conector_id,$v_sqlprn);
	$v_ilimit=pg_numrows($v_xsqlprn);

?>


<html>
<head>
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

SISTEMA INTEGRADO - <?php echo $nombre_almacen;?>
<div align="center"><font face="Arial, Helvetica, sans-serif">

	Control de Afericiones  
	<font size="2"> <br>
	del: </font><font face="Arial, Helvetica, sans-serif"><font size="2"><?php echo $v_fecha_desde; ?></font></font><font size="2"> 
	al <?php echo $v_fecha_hasta;?></font></font><br>
</div>


<?php
	echo "<table width='990' border='1' height='81'>";
	echo "<tr>";
	echo "<th colspan=8 align='center'>DETALLE DE AFERICIONES	</th>";
	echo "</tr>";
	echo "<tr>";
	echo "	<td width='85'>Lado</td>";
	echo "	<td width='106' align='center'>Producto</td>";
	echo "	<td width='247'>Fecha / Hora</td>";
	echo "	<td width='69' align='center'>Control</td>";
	echo "</tr>";

	$v_irow=0;
	$v_tot_cli_cant_doc=0;
	$v_tot_cli_impo=0;
	$v_cli_cant_doc=0;
	$v_cli_impo=0;	
	
	$v_clave=" ";
	
	if($v_ilimit>0) 
		{
		while($v_irow<$v_ilimit)
			{
			$a0=pg_result($v_xsqlprn,$v_irow,0);
			$a1=pg_result($v_xsqlprn,$v_irow,1);
			$a2=pg_result($v_xsqlprn,$v_irow,2);
			$a3=pg_result($v_xsqlprn,$v_irow,3);
			$a4=pg_result($v_xsqlprn,$v_irow,4);
			
			echo "<tr>";
			echo "<th align='left' >".$a1." </th>"; 
			echo "<th align='left' >".$a2." </th>";
			echo "<th align='left' >".$a3." ".$a4." </th>";
			echo "<td>";
			
			$v_clave=$a1.$a2;
			
			while ($v_irow<$v_ilimit and $v_clave==$a1.$a2)
				{
				$a5=pg_result($v_xsqlprn,$v_irow,5);
				$a6=pg_result($v_xsqlprn,$v_irow,6);
				$a7=pg_result($v_xsqlprn,$v_irow,7);
				
				echo " ".$a5." ".$a6." (".$a7.") ";
				
				$v_irow++;
				//antes de regresar al bucle tiene que comprobar el dato
				if ($v_irow<$v_ilimit )
					{
					$a1=pg_result($v_xsqlprn,$v_irow,1);
					$a2=pg_result($v_xsqlprn,$v_irow,2);
					}
				}
			
			echo "</td>";
			echo "</tr>";
			
			}
			
		echo "</table>";

		echo "<br>";
		
		echo "<table width='990' border='1' height='81'>";
		echo "<tr>";
		echo "<th colspan=20 align='center'>RESUMEN DE AFERICION</th>";
		echo "</tr>";
		echo "<tr>";
		echo "<td width='85'>Lado</td>";
		echo "<td width='106' colspan=2 align='center'>84 Oct</td>";
		echo "<td width='106' colspan=2 align='center'>90 Oct</td>";
		echo "<td width='106' colspan=2 align='center'>95 Oct</td>";
		echo "<td width='106' colspan=2 align='center'>97 Oct</td>";
		echo "<td width='106' colspan=2 align='center'>D2</td>";
		echo "<td width='106' colspan=2 align='center'>D1</td>";
		echo "<td width='106' colspan=2 align='center'>GLP</td>";
		echo "<td width='247' colspan=2>Total</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td width='85'>Lado</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "<td width='90' align='center'>Cant</td>";
		echo "<td width='90' align='center'>Val</td>";
		echo "</tr>";

		$clado=6;
		
		$v_sqlprn="select es, pump, trim(codigo), sum(cantidad) as cant, sum(importe) as impo  
							from pos_ta_afericiones  
							where dia between '".$funcion->date_format($v_fecha_desde,'YYYY-MM-DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY-MM-DD')."' 
							group by es, pump, codigo
							order by es, pump, codigo";
							
		$v_xsqlprn=pg_exec($conector_id,$v_sqlprn);
		$v_ilimit=pg_numrows($v_xsqlprn);
		$v_irow=0;

		while($v_irow<$v_ilimit){
			$a0=pg_result($v_xsqlprn,$v_irow,0);
			$a1=pg_result($v_xsqlprn,$v_irow,1);
			
			$col01=0;
			$col02=0;
			$col03=0;
			$col04=0;
			$col05=0;
			$col06=0;
			$col07=0;
			$vol01=0;
			$vol02=0;
			$vol03=0;
			$vol04=0;
			$vol05=0;
			$vol06=0;
			$vol07=0;

			$coltot01=0;
			$coltot02=0;
			$coltot03=0;
			$coltot04=0;
			$coltot05=0;
			$coltot06=0;
			$coltot07=0;
			$voltot01=0;
			$voltot02=0;
			$voltot03=0;
			$voltot04=0;
			$voltot05=0;
			$voltot06=0;
			$voltot07=0;
			

			$v_codigo=$a1;
			$v_clave=$a0.$a1;
			while ($v_irow<$v_ilimit and $v_clave==$a0.$a1){
				$a2=pg_result($v_xsqlprn,$v_irow,2);
				$a3=pg_result($v_xsqlprn,$v_irow,3);
				$a4=pg_result($v_xsqlprn,$v_irow,4);
				switch($a2){
					case "11620301":
						$col01=$a3;
						$vol01=$a4;
						$coltot01= $coltot01+$a3;
						$voltot01= $voltot01+$a4;
						break;
					case "11620302":
						$col02=$a3;
						$vol02=$a4;
						$coltot02= $coltot02+$a3;
						$voltot02= $voltot02+$a4;
						break;
					case "11620303":
						$col03=$a3;
						$vol03=$a4;
						$coltot03= $coltot03+$a3;
						$voltot03= $voltot03+$a4;
						break;
					case "11620304":
						$col04=$a3;
						$vol04=$a4;
						$coltot04= $coltot04+$a3;
						$voltot04= $voltot04+$a4;
						break;
					case "11620305":
						$col05=$a3;
						$vol05=$a4;
						$coltot05= $coltot05+$a3;
						$voltot05= $voltot05+$a4;
						break;
					case "11620306":
						$col06=$a3;
						$vol06=$a4;
						$coltot06= $coltot06+$a3;
						$voltot06= $voltot06+$a4;
						break;
					case "11620307":
						$col07=$a3;
						$vol07=$a4;
						$coltot07= $coltot07+$a3;
						$voltot07= $voltot07+$a4;
						break;
					}

				
				$v_irow++;
				//antes de regresar al bucle tiene que comprobar el dato
				if ($v_irow<$v_ilimit )
					{
					$a0=pg_result($v_xsqlprn,$v_irow,0);
					$a1=pg_result($v_xsqlprn,$v_irow,1);
					}
				}
				
			$colto=$col01+$col02+$col03+$col04+$col05+$col06+$col07;
			$volto=$vol01+$vol02+$vol03+$vol04+$vol05+$vol06+$vol07;
				
			echo "<tr>";
			echo "<td align='left' >".$v_codigo." </td>"; 
			echo "<td align='right' width='90'>".number_format($col01, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($vol01, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($col02, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($vol02, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($col03, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($vol03, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($col04, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($vol04, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($col05, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($vol05, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($col06, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($vol06, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($col07, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($vol07, 2, '.', '')." </td>";

			echo "<td align='right' width='90'>".number_format($colto, 2, '.', '')." </td>";
			echo "<td align='right' width='90'>".number_format($volto, 2, '.', '')." </td>";
			echo "</tr>";
				
			}
		$coltot=$coltot01+$coltot02+$coltot03+$coltot04+$coltot05+$coltot06+$coltot07;
		$voltot=$voltot01+$voltot02+$voltot03+$voltot04+$voltot05+$voltot06+$voltot07;
					
		echo "<tr>";
		echo "<td align='left' >TOTAL </td>"; 
		echo "<td align='right' width='90'>".number_format($coltot01, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot01, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($coltot02, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot02, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($coltot03, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot03, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($coltot04, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot04, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($coltot05, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot05, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($coltot06, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot06, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($coltot07, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot07, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($coltot, 2, '.', '')." </td>";
		echo "<td align='right' width='90'>".number_format($voltot, 2, '.', '')." </td>";
		echo "</tr>";
		}
	
	echo "</table>";
	?>
	




</body>
</html>



<?php 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);
// restaura el control de errores original
$clase_error->_error();
