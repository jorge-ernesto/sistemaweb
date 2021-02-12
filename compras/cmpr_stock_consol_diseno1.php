<html>
<link rel="stylesheet" href="<?php echo $v_path_url; ?>jch.css" type="text/css">
<head>

<title>sistemaweb</title>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script>
function enviadatos(){
	document.f_name.submit()
	}
function act_inicio(form){
	var inicial_fec = form.v_fecha_final.value;
	var dias_dif = form.v_dias_venta.value;
	RestaFechas(form);
	}
	
function RestaFechas (formu) {
	//Obtiene los datos del formulario
	var DiasMenos=formu.v_dias_venta.value;
	var CadenaFecha1 = formu.v_fecha_final.value
   
	//Obtiene dia, mes y año
	var fecha1 = new fecha( CadenaFecha1 )
	//Obtiene objetos Date
	var now = new Date( fecha1.anio,fecha1.mes-1,fecha1.dia,12)
	var newtimems=now.getTime()-(DiasMenos*24*60*60*1000);
	now.setTime(newtimems);
	var year = now.getYear()
	if(year<1000) year+=1900;
	var mes= (now.getMonth()+1) ;
	var dia= now.getDate() ;

	var cmes = new String( mes )
	var cdia = new String( dia )
	if ( cmes.length==1)  cmes='0'+cmes;
	if ( cdia.length==1)  cdia='0'+cdia;
	formu.v_fecha_inicial.value=  cdia  +  "/" + cmes +  "/" + year
	//formu.v_fecha_inicial.value= now.toLocaleString();
	//return dias
	}

function fecha( cadena ) {

   //Separador para la introduccion de las fechas
   var separador = "/"

   //Separa por dia, mes y año
   if ( cadena.indexOf( separador ) != -1 ) {
        var posi1 = 0
        var posi2 = cadena.indexOf( separador, posi1 + 1 )
        var posi3 = cadena.indexOf( separador, posi2 + 1 )
        this.dia = cadena.substring( posi1, posi2 )
        this.mes = cadena.substring( posi2 + 1, posi3 )
        this.anio = cadena.substring( posi3 + 1, cadena.length )
		}
	else 
		{
        this.dia = 0
        this.mes = 0
        this.anio = 0   
		}
	}

function ltrim ( s )
	{
	return s.replace( /^\s*/, "" );
	}

function rtrim ( s )
	{
	return s.replace( /\s*$/, "" );
	}


function trim ( s )
	{
	return rtrim(ltrim(s));
	}


	
	
	

</script>

</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_name">

<table border="1">
	<tr> 
		<th width="500"><p align='center'>STOCK CONSOLIDADO EN UNIDADES AL: <?php echo $v_fecha_inicial ?> CON UNIDADES VENDIDAS  <br></p></th>
	</tr>
</table>


	<table width="600" border="1"> 
		<th>ALMACEN:<select name="v_almacen" tabindex="2">
			<?php 
			echo "<option value='' selected >&nbsp;&nbsp;&nbsp;&nbsp; -- TODOS </option>";	
			for($i=0;$i<pg_numrows($v_xsqlalma);$i++){		
				$k_alma1 = pg_result($v_xsqlalma,$i,0);	
				$k_alma2 = substr(pg_result($v_xsqlalma,$i,1),0,15);
				if (trim($k_alma1)==trim($v_almacen)) { 
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} 
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
		  		}
			?>
        </select>
		</th>
		<th>LINEA:<select name="v_linea" tabindex="3"  >
			<?php 
			$v_xsqlline=pg_exec("select trim(tab_elemento) as cod,tab_descripcion from int_tabla_general  where tab_tabla='20' and tab_elemento!='000000' order by cod");
			for($i=0;$i<pg_numrows($v_xsqlline);$i++){		
				$k_line1 = pg_result($v_xsqlline,$i,0);	
				$k_line2 = substr( pg_result($v_xsqlline,$i,1),0,15 ) ;
				if ($k_line1==$v_linea) { 
					echo "<option value='".$k_line1."' selected >".$k_line1." -- ".$k_line2." </option>";	
					} 
				else {
					echo "<option value='".$k_line1."' >".$k_line1." -- ".$k_line2." </option>";	
					}
		  		}
			?>
        </select>
		</th>

		
	</table>
	
	<table width="600" border="1">
		<th width="150">Fecha Proceso:</th>
		<td><input type="text" name="v_fecha_proceso" size="16" maxlength="10" value='<?php echo $v_fecha_proceso ; ?>'  tabindex="1" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" >
		<a href="javascript:show_calendar('f_name.v_fecha_proceso');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</td>
		<th width="150">Fecha Stock:</th>
		<td><input type="text" name="v_fecha_stock" size="16" maxlength="10" value='<?php echo $v_fecha_stock ; ?>'  tabindex="1" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" >
		<a href="javascript:show_calendar('f_name.v_fecha_stock');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</td>
	</table>
	
	<table width="600" border="1">
		<th width="150">Fecha Inicio Venta :</th>
		<td><input name="v_fecha_inicial" type="text" value="<?php echo $v_fecha_inicial; ?>" size="16" maxlength="10" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_name.v_fecha_inicial');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</td>
		<th width="150">Fecha Final Venta :</th>
		<td ><input name="v_fecha_final" type="text" value="<?php echo $v_fecha_final; ?>" size="16" maxlength="10" onKeyUp="javascript:validarFecha(this)"  onblur="javascript:validarFecha(this)">
		<a href="javascript:show_calendar('f_name.v_fecha_final');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</td>
	</table>

<input type="submit" name="boton" value="Enviar">
<input type="submit" name="boton" value="Regresar">

<?php
if (!is_null($v_xsqlareq_det) )
	{
	echo "<p><br>";
	echo "REGISTRO DE PEDIDOS PROCESADO : $v_fecha_proceso ";
	echo "STOCK A LA FECHA: $v_fecha_stock ";
	echo "VENTA DESDE $v_fecha_inicial HASTA $v_fecha_final";
	echo "</p>";
	echo "<table height='20' border='0'>";
	echo '<tr bgcolor="#FFFFCC">';
	echo '<td width="200">';
	echo '<table>';
	echo '<td width="100"><font size="2">NOMB PROD</font></td>';
	echo '<td width="30" ><font size="2">Stk<br>Ped</font></td>';
	echo '<td width="30" ><font size="2">Vta<br>Tra</font></td>';
	echo '</table>';
	echo '</td>';
	// $v_xsql_alma=pg_exec($conector_id, $v_sql_alma);	
	for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
		{
		echo '<td width="55"><font size="2">'.substr(pg_result($v_xsql_alma,$x,0),0,3).'<BR>'.substr(pg_result($v_xsql_alma,$x,1),0,3).'</font></td>';
		}
	echo '<td width="55"><font size="2">Total</font></td>';
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
					echo'<td width="55"><font size="2">'.round($stk,0).'..'.round($ven,0).'</font></td>';
					$tot1=$tot1+round($stk,0);
					$tot2=$tot2+round($ven,0);
					}
				else
					{
					echo '<td width="55"><font size="2">.. ..</font></td>';
					}
				}
			else
				{
				echo '<td width="55"><font size="2">.. ..</font></td>';
				}
			$total_rep[$x][1]= $total_rep[$x][1]+round($stk,0);
			$total_rep[$x][2]= $total_rep[$x][2]+round($ven,0);
			}
		echo '<td width="55"><font size="2">'.$tot1.'..'.$tot2.'</font></td>';
		echo '</tr>';
		echo '<tr bgcolor="#FFFFCC">';
		echo '<td width="85"><font size="2">&nbsp;</font></td>';
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
					echo'<td width="55"><font size="2">'.round($ped,0).'..'.round($tra,0).'</font></td>';
					$tot3=$tot3+round($ped,0);
					$tot4=$tot4+round($tra,0);
					}
				else
					{
					echo '<td width="55"><font size="2">.. ..</font></td>';
					}
				}
			else
				{
				echo '<td width="55"><font size="2">.. ..</font></td>';
				}
			$total_rep[$x][3]= $total_rep[$x][3]+round($ped,0);
			$total_rep[$x][4]= $total_rep[$x][4]+round($tra,0);
			}
		if ($k>$y){ $y=--$k; } 
		echo '<td width="55"><font size="2">'.$tot3.'..'.$tot4.'</font></td>';
		echo '</tr>';
		}
	echo '<tr bgcolor="#FFFFCC">';
	echo '<th width="85"><font size="2">TOTAL :</font></th>';
	$tot5=0;
	$tot6=0;
	for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
		{
		echo '<td width="85"><font size="2">';
		echo $total_rep[$x][1].'..'.$total_rep[$x][2];
		echo '</font></td>';
		$tot5=$tot5+round($total_rep[$x][1],0);
		$tot6=$tot6+round($total_rep[$x][2],0);
		}
	echo '<td width="55"><font size="2">'.$tot5.'..'.$tot6.'</font></td>';
	echo '</tr>';
	
	echo '<tr bgcolor="#FFFFCC">';
	echo '<td width="85"><font size="2">&nbsp;</font></td>';
	$tot5=0;
	$tot6=0;
	for($x=0; $x<pg_numrows($v_xsql_alma);$x++)
		{
		echo '<td width="85"><font size="2">';
		echo $total_rep[$x][3].'..'.$total_rep[$x][4];
		echo '</font></td>';
		$tot5=$tot5+round($total_rep[$x][3],0);
		$tot6=$tot6+round($total_rep[$x][4],0);
		}
	echo '<td width="55"><font size="2">'.$tot5.'..'.$tot6.'</font></td>';
	echo '</tr>';


		
	echo "</table>";
	}

?>



</form>
</body>
</html>