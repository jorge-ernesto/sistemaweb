<?php
if (!is_null($v_xsqlareq_det) )
	{
	echo "<p><br>";
	echo "REGISTRO DE PEDIDOS PROCESADO : $v_fecha_proceso ";
	echo "STOCK A LA FECHA: $v_fecha_stock ";
	echo "VENTA DESDE $v_fecha_inicial HASTA $v_fecha_final";
	echo "</p>";
	
	echo "<table width='1000' height='20' border='0'>";
	echo '<tr bgcolor="#FFFFCC">';
	echo '<td width="200">';
	echo '<table >';
	echo '<td width="100"><font size="2">NOMB PROD</font></td>';
	echo '<td width="40" ><font size="2">Stk<br>Ped</font></td>';
	echo '<td width="40" ><font size="2">Vta<br>Tra</font></td>';
	echo '</table>';
	echo '</td>';
	// $v_xsql_alma=pg_exec($conector_id, $v_sql_alma);	
	for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
		{
		// echo '<td width="25"><font size="2">'.substr(pg_result($v_xsql_alma,$x,0),1,2).'</font></td>';
		// echo '<td width="25"><font size="2">'.substr(pg_result($v_xsql_alma,$x,1),0,2).'</font></td>';
		echo '<td width="55" colspan=2 align="center" ondblclick="cuadrito()"><font size="2">'.substr(pg_result($v_xsql_alma,$x,0),1,3).'<br>'.substr(pg_result($v_xsql_alma,$x,1),0,3).'</font></td>';
		}
	echo '<td width="55" colspan=2 align="center"><font size="2">TOT</font></td>';
	// echo '<td width="25"><font size="2">&nbsp;</font></td>';
	echo '</tr>';
	
	// array de total final
	$total_rep=array();
	for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
		{
		array_push($total_rep, array(pg_result($v_xsql_alma,$x,0),0,0,0,0) );
		}
		
	for($y=0; $y < pg_numrows($v_xsqlareq_det); $y++){
  		$a_ped_oc = pg_fetch_row($v_xsqlareq_det,$y);
		$v_art_nombre=substr($a_ped_oc[15],0,19);
		$v_art_nombre2= $a_ped_oc[16] ;
		$pos = strpos ($v_art_nombre2, "'");
		if ($pos === false) 
			{ 
			// nota: tres signos igual
			// no encontrado ...
			}
		else
			{$v_art_nombre2= substr_replace($v_art_nombre2,'',$pos ,1);}

		$v_art_codigo=$a_ped_oc[3];
		$tot1=0;
		$tot2=0;
		$tot3=0;
		$tot4=0;
		
		echo '<tr bgcolor="#FFFFCC">';
		echo '<td width="200" onMouseOver="window.status=\'Codigo Item\'; overlib(\'-'.$v_art_codigo.'\.<br>'.$v_art_nombre2.'-\'); return true;" onMouseOut="window.status=\'\'; nd(); return true;" ><font size="2">'.$v_art_nombre.'</font>';
		echo '</td>';
		$k=$y;
		for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
			{
			$v_art_codigo=$a_ped_oc[2];
			$stk=0;
			$ven=0;
			if ($k<pg_numrows($v_xsqlareq_det))
				{
				$a_ped_oc = pg_fetch_row($v_xsqlareq_det,$k);
				$a_almacen = pg_fetch_row($v_xsql_alma,$x);
				if ( substr($a_almacen[0],0,3) == $a_ped_oc[1] and $a_ped_oc[2]==$v_art_codigo )
					{
					$k++;
					$stk=$a_ped_oc[5];
					$ven=$a_ped_oc[6];
					// echo '<td width="55"><font size="2">'.round($stk,0).'..'.round($ven,0).'</font></td>';
					echo '<td width="25" align="right"><font size="2">'.round($stk,0).'</font></td>';
					echo '<td width="25" align="right"><font size="2">'.round($ven,0).'</font></td>';
					$tot1=$tot1+round($stk,0);
					$tot2=$tot2+round($ven,0);
					}
				else
					{
					// echo '<td width="55"><font size="2">.. ..</font></td>';
					echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
					echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
					}
				}
			else
				{
				// echo '<td width="55"><font size="2">.. ..</font></td>';
				echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
				echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
				
				}
			$total_rep[$x][1]= $total_rep[$x][1]+round($stk,0);
			$total_rep[$x][2]= $total_rep[$x][2]+round($ven,0);
			}
		// echo '<td width="55"><font size="2">'.$tot1.'..'.$tot2.'</font></td>';
		echo '<td width="25" align="right"><font size="2">'.$tot1.'</font></td>';
		echo '<td width="25" align="right"><font size="2">'.$tot2.'</font></td>';
		echo '</tr>';

		echo '<tr bgcolor="#FFFFCC">';
		echo '<td width="200"><font size="2">&nbsp;</font></td>';
		$k=$y;
		for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
			{
			$v_art_codigo=$a_ped_oc[2];
			$ped=0;
			$tra=0;
			if ($k<pg_numrows($v_xsqlareq_det))
				{
				$a_ped_oc = pg_fetch_row($v_xsqlareq_det,$k);
				$a_almacen = pg_fetch_row($v_xsql_alma,$x);
				if ( substr($a_almacen[0],0,3) == $a_ped_oc[1] and $a_ped_oc[2]==$v_art_codigo )
					{
					$k++;
					$ped=$a_ped_oc[7];
					$tra=$a_ped_oc[9];
					// echo'<td width="55"><font size="2">'.round($ped,0).'..'.round($tra,0).'</font></td>';
					echo '<td width="25" align="right"><font size="2">'.round($ped,0).'</font></td>';
					echo '<td width="25" align="right"><font size="2">'.round($tra,0).'</font></td>';
					$tot3=$tot3+round($ped,0);
					$tot4=$tot4+round($tra,0);
					}
				else
					{
					// echo '<td width="55"><font size="2">.. ..</font></td>';
					echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
					echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
					}
				}
			else
				{
				// echo '<td width="55"><font size="2">.. ..</font></td>';
				echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
				echo '<td width="25" align="right"><font size="2">&nbsp;</font></td>';
				}
			$total_rep[$x][3]= $total_rep[$x][3]+round($ped,0);
			$total_rep[$x][4]= $total_rep[$x][4]+round($tra,0);
			}
		if ($k>$y){ $y=--$k; } 
		// echo '<td width="55"><font size="2">'.$tot3.'..'.$tot4.'</font></td>';
		echo '<td width="25" align="right"><font size="2">'.$tot3.'</font></td>';
		echo '<td width="25" align="right"><font size="2">'.$tot4.'</font></td>';
		echo '</tr>';
		}

	echo '<tr bgcolor="#FFFFCC">';
	echo '<td width="200"><font size="2">TOTAL :</font></td>';
	$tot5=0;
	$tot6=0;
	for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
		{
		// echo '<td width="85"><font size="2">';
		// echo $total_rep[$x][1].'..'.$total_rep[$x][2];
		// echo '</font></td>';
		echo '<td width="25" align="right"><font size="2">'.round($total_rep[$x][1],0).'</font></td>';
		echo '<td width="25" align="right"><font size="2">'.round($total_rep[$x][2],0).'</font></td>';
		$tot5=$tot5+round($total_rep[$x][1],0);
		$tot6=$tot6+round($total_rep[$x][2],0);
		}
	// echo '<td width="55"><font size="2">'.$tot5.'..'.$tot6.'</font></td>';
	echo '<td width="25" align="right"><font size="2">'.$tot5.'</font></td>';
	echo '<td width="25" align="right"><font size="2">'.$tot6.'</font></td>';
	echo '</tr>';
	echo '<tr bgcolor="#FFFFCC">';
	echo '<td width="200"><font size="2">&nbsp;</font></td>';
	$tot5=0;
	$tot6=0;
	for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
		{
		//echo '<td width="85"><font size="2">';
		//echo $total_rep[$x][3].'..'.$total_rep[$x][4];
		//echo '</font></td>';
		echo '<td width="25" align="right"><font size="2">'.round($total_rep[$x][3],0).'</font></td>';
		echo '<td width="25" align="right"><font size="2">'.round($total_rep[$x][4],0).'</font></td>';
		$tot5=$tot5+round($total_rep[$x][3],0);
		$tot6=$tot6+round($total_rep[$x][4],0);
		}
	// echo '<td width="55"><font size="2">'.$tot5.'..'.$tot6.'</font></td>';
	echo '<td width="25" align="right"><font size="2">'.$tot5.'</font></td>';
	echo '<td width="25" align="right"><font size="2">'.$tot6.'</font></td>';
	echo '</tr>';


		
	echo "</table>";
	}

