<?php

if($eliminar=="Eliminar") { 
	include("../functions.php");
	$pk = cortarCadena($pk_med_dia);
	$cod_tanque = $pk[1];
	$fecha_medicion = $pk[2];
	$ch_sucursal = $pk[0];
	pg_exec("DELETE FROM comb_ta_mediciondiaria WHERE ch_sucursal='$ch_sucursal' AND ch_tanque='$cod_tanque' AND dt_fechamedicion=to_date('$fecha_medicion','DD-MM-YYYY') ");
	header("Location: cmb_medvarilla.php");
	exit;
}

if ($action!="guardar") {
	include("../menu_princ.php");
}
include("../functions.php");

if($modificar=="Modificar") {  
	$pk = cortarCadena($pk_med_dia);
	$cod_tanque = $pk[1];
	$fecha_medicion = $pk[2];
	$ch_sucursal = $pk[0];
	
	/*echo $pk."<br>";
	echo $cod_tanque."<br>";
	echo $fecha_medicion."<br>";
	echo $ch_sucursal."<br>";*/
}

if($action == "guardar") {
    	$sqljc = "UPDATE
    			comb_ta_mediciondiaria 
    		SET
			ch_tanque='$cod_tanque',
			dt_fechamedicion=to_date('$fecha_medicion','DD-MM-YYYY')
			,nu_medicion=$cantidad
			,ch_responsable='$responsable' 
		WHERE 
			ch_sucursal='$sucursal' 
			AND ch_tanque='$cod_tanque' 
			AND dt_fechamedicion=to_date('$fecha_medicion','DD-MM-YYYY')";
	//echo $sqljc."<br>";
	pg_exec($coneccion,$sqljc);
	header("Location: cmb_medvarilla.php");
	exit;
}

//para el combo
$rs1 = pg_exec($coneccion,"SELECT 
				a.ch_tanque,
				a.ch_tanque  || '--' || b.ch_nombrecombustible 
			FROM 
				comb_ta_tanques a, 
				comb_ta_combustibles b  
			WHERE 
				a.ch_codigocombustible=b.ch_codigocombustible
				AND a.ch_sucursal='$ch_sucursal' ");

//para el formulario
$rs2 = pg_exec(" SELECT 
			a.ch_sucursal,
			a.ch_tanque,
			a.ch_tanque ||'--' ||  c.ch_nombrecombustible, 
			to_char(a.dt_fechamedicion,'dd-mm-yyyy'), 
			a.nu_medicion, 
			a.ch_responsable 
		FROM 
			comb_ta_mediciondiaria a,
			comb_ta_tanques b, 
			comb_ta_combustibles c  
		WHERE 
			a.ch_tanque=b.ch_tanque 
			AND b.ch_codigocombustible=c.ch_codigocombustible 
			AND a.ch_sucursal=b.ch_sucursal
			AND a.ch_tanque=trim('$cod_tanque') 
			AND a.ch_sucursal='$ch_sucursal' 
			AND a.dt_fechamedicion=to_date('$fecha_medicion','dd-mm-yyyy')");

$B = pg_fetch_row($rs2,0);

pg_close();

?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  	if (init==true) with (navigator) {
  		if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    			document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; 
    		}
    	} else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) 
  		location.reload();
}
MM_reloadPage(true);
//-->
</script>
</head>

<body>
<marquee>CONFIGURACION DE TANQUES</marquee>
<hr noshade>
<br>
<form name="form3" action="cmb_medvarilla-edit.php?action=guardar" method="post">
<table width="90%" border="1">
	<tr> 
      		<td width="19%">Sucursal</td>
      		<td width="21%"><input type="text" name="sucursal" value="<?php echo $B[0];?>" readonly="true" ></td>
      		<td width="60%">Codigo Tanque 
        	<select name="cod_tanque">
         	<?php 
		print '<option value ='.$B[1].' selected>'.$B[2].'</option>';
		for($i=0;$i<pg_numrows($rs1);$i++){
			$A = pg_fetch_row($rs1,$i);
			print "<option value='$A[0]'>$A[1]</option>";
		}
		?>
        	</select></td>
    	</tr>
    	<tr> 
      		<td>Fecha de Medicion</td>
      		<td><input type="text" name="fecha_medicion" value="<?php echo $B[3];?>"></td>
      		<td>&nbsp;</td>
    	</tr>
    	<tr> 
      		<td>Cantidad</td>
      		<td><input type="text" name="cantidad" value="<?php echo $B[4];?>"></td>
      		<td>&nbsp;</td>
    	</tr>
    	<tr> 
      		<td height="25">Responsable</td>
      		<td><input type="text" name="responsable" value="<?php echo $B[5];?>"></td>
      		<td>&nbsp;</td>
    	</tr>
    	<tr> 
      		<td>&nbsp;</td>
      		<td> <div align="center"> 
          	<input type="submit" name="Submit" value="Guardar Cambios">
        	</div></td>
      		<td>&nbsp;</td>
    	</tr>
</table>
</form>
</body>
</html>  
<?php include("../close_connect.php"); ?>
