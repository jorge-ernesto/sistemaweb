<?php
extract($_REQUEST);
include "../menu_princ.php";
include("../functions.php");

$c1 = "<!--";
$c2 = "-->";

if($cod_almacen == "") {
	$cod_almacen = $_REQUEST['cod_almacen'];
	$sql = "SELECT ch_almacen ,ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' AND trim(ch_almacen)=trim('$cod_almacen') ORDER BY ch_nombre_almacen";
	$rs2 = pg_exec($sql);
	$A = pg_fetch_array($rs2,0, PGSQL_NUM);
	$sucursal_val = $A[0];
	$sucursal_dis = $A[1];

	//var_dump($sql);
} else {
	//PARA PONER LOS VALORES SELECCIONADOS COMO DEFAULT DE LOS COMBOS
	if($cod_linea != "") {
		$cod_linea = trim($cod_linea);
		if($cod_linea != "todo") { 
			$and_cod = " and trim(tab_elemento)='$cod_linea' ";
		} else {
			$and_cod = "";
		}
		$rs1 = pg_exec("SELECT tab_elemento, tab_descripcion FROM int_tabla_general WHERE tab_tabla='20' $and_cod");
		$A = pg_fetch_array($rs1,0);
	}
	if($cod_linea != "todo") {
		$linea_dis = $A[1];
	} else {
		$linea_dis = "TODAS LAS LINEAS";
	}
	$rs2 = pg_exec("SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' AND trim(ch_almacen)=trim('$cod_almacen') ORDER BY ch_nombre_almacen");
	$A = pg_fetch_array($rs2,0);
	$sucursal_val = $A[0];
	$sucursal_dis = $A[1];

	//sacamos el reporte
	$rs = reporte_ventas_mensuales($periodo,$cod_almacen,$cod_linea,$modo);
}

//PARA LOS COMBOS DE LINEAS Y DE ALMACEN
$rs1 = pg_exec("SELECT trim(tab_elemento),tab_descripcion FROM int_tabla_general WHERE tab_tabla='20'");
$rs2 = pg_exec("SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_nombre_almacen");

//Configuracion de la tabla segun cantidades valores o todo
switch($modo) {
	case "todo":
		$tabla 		= $tabla_todo;
		$fila 		= $fila_todo;
		$modo_dis 	= "Todo";
		break;
	
	case "cantidades":
		$tabla 		= $tabla_cantidad;
		$fila 		= $fila_cantidad;
		$modo_dis 	= "Solo Cantidades";
		break;
	
	case "valores":
		$tabla 		= $tabla_valor;
		$fila 		= $fila_valor;
		$modo_dis 	= "Solo Valores";
		break;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Reporte de Ventas Mensuales</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
		<h2 style="color:#336699;" align="center">REPORTE DE VENTAS MENSUALES</h2>
		<hr noshade>
		<div align="center">
			<form name="form1" method="post" action="generar_rep_ventas_mensuales.php">
			<table>
				<tr> 
					<td align="right" width="50">Almacen: </td>
					<td width="161">
						<select name="cod_almacen">
							<?php
							if($cod_almacen!="") { 
								print "<option value='$sucursal_val' selected>$sucursal_val -- $sucursal_dis</option>"; 
							}
							for($i=0;$i<pg_numrows($rs2);$i++) {		
								$B = pg_fetch_row($rs2,$i);		
								print "<option value='$B[0]' >$B[0] -- $B[1]</option>";	
							}
							?>
						</select>
					</td>
				<tr> 
					<td align="right" width="50">Periodo: </td>
					<td width="161">
						<input type="text" name="periodo" value="<?php echo $periodo;?>">
					</td>
				</tr>
				<tr> 
					<td align="right">Linea:</td>
					<td>
						<select name="cod_linea" >
							<?php
							if($cod_linea!="" && $cod_linea!="todo") {
								print "<option value='$cod_linea' selected>$cod_linea -- $linea_dis</option>";
							}
							if($cod_linea == "todo") {
								print "<option value='$cod_linea' selected>$linea_dis</option>";
							}
							print "<option value='todo' >-- TODAS LAS LINEAS --</option>";
							for($i=0;$i<pg_numrows($rs1);$i++) {		
								$B = pg_fetch_row($rs1,$i);		
								print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
							}
							?>
						</select>
					</td>
						<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="right">Mostrar:</td>
					<td>
						<select name="modo" >
							<?php
							if($modo!="") {
								print " <option value='$modo'>$modo_dis</option>";
							}
							?>
							<option value="todo">Todo</option>
							<option value="cantidades">Solo Cantidades</option>
							<option value="valores">Solo Valores</option>
						</select>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr> 
					<td>&nbsp;</td>
					<td><div align="center"> 
						<input type="submit" name="Submit" value="Buscar">
					</div></td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<table>
				<tr>
					<td width="249">
						<strong>
							<font size="2" face="Arial, Helvetica, sans-serif">
								<a href="#" onClick="javascript:window.open('generar_reporte_ventas_mensuales.php?cod_linea=<?php echo $cod_linea;?>&periodo=<?php echo $periodo;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>&modo=<?php echo $modo;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar 
									Reporte
								</a>
							</font>
						</strong>
					</td>
				</tr>
			</table>
		</div>
		<font size="1" face="Arial, Helvetica, sans-serif">Periodo: <?php echo $periodo;?></font> <br>
		<table width="700" border="1" cellpadding="0" cellspacing="0">
			<?php print $tabla;
   /*
   ?> 
    <tr> 
      <td width="200"><font size="-4" face="Arial, Helvetica, sans-serif">Codigo y Descripcion del Articulo</font></td>
      <td width="52"><font size="-4" face="Arial, Helvetica, sans-serif">can01</font></td>
      <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val01</font></td>
      <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can02</font></td>
      <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val02</font></td>
      <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">cab03</font></td>
      <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val03</font></td>
      <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can04</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val04</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can05</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val05</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can06</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val06</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can07</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val07</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can08</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val08</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can09</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val09</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can10</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val10</font></td>
      <td width="41"><font size="-4" face="Arial, Helvetica, sans-serif">can11</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val11</font></td>
      <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can12</font></td>
      <td width="43"><font size="-4" face="Arial, Helvetica, sans-serif">val12</font></td>
    </tr><!--<?php */  ?>
    <!-- <?php
	if($modo!="todo"){
	$com1=$c1;
	$com2=$c2;
	}
	for($i=0;$i<pg_numrows($rs);$i++){  
	$A = pg_fetch_array($rs,$i);  
	print '
	?> -->
	<tr> 
      <td><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[3].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[4].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[5].'</font></td>
      <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[6].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[7].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[8].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[9].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[10].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[11].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[12].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[13].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[14].'</font></td>
      '.$com1.'<td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[15].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[16].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[17].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[18].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[19].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[20].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[21].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[22].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[23].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[24].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[25].'</font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;&nbsp;'.$A[26].'</font></td>
    	'.$com2.'
	</tr>
	<!-- <?php '; } ?> -->
		</table>
		<p>&nbsp;</p>
		</form>
	</body>
</html>
<?php pg_close();?>
