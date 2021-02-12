<?php
session_start();
include("../config.php");
include("../functions.php");
include("../valida_sess.php");

$fecha 	= getdate();
$dia 	= $fecha['mday'];
$mes 	= $fecha['mon'];
$year 	= $fecha['year'];
$hoy 	= $dia.'-'.$mes.'-'.$year;

if($consultar=="Consultar") {
	$fechad = $anod."-".$mesd."-".$diad;
	$fechaa = $anoa."-".$mesa."-".$diaa;
} else {
	$diad = "01";
	$anod = $year;
	$mesd = $mes;

	$diaa = "29";
	$anoa = $year;
	$mesa = $mes;
}

// crear tabla temporal para el reporte
if($action == "exportar") {
	$tabla_temp 	= $user.'_tmp';
	$archivo_csv 	= $user.'_exp';
	$cabecera 	= "FECHA".','."TANQUE".','."MEDIDA".','."SUCURSAL";

	pg_exec("create table $tabla_temp as $q1");
	pg_exec("copy $tabla_temp to '/var/www/html/sistemaweb/tmp/$tabla_temp.csv' with delimiter as ','");
	pg_exec("drop table $tabla_temp");
	
	if($titulo!="") {
		exec("echo -e '$titulo\n' > /var/www/html/sistemaweb/tmp/$tabla_temp.txt");  
	}
	
	exec("echo '$cabecera' >> /var/www/html/sistemaweb/tmp/$tabla_temp.txt");
	exec("rm -Rf /var/www/html/sistemaweb/tmp/$tabla_temp2.csv");
	exec("cat /var/www/html/sistemaweb/tmp/$tabla_temp.csv >> /var/www/html/sistemaweb/tmp/$tabla_temp2.csv");
	exec("cat /var/www/html/sistemaweb/tmp/$tabla_temp2.csv >>/var/www/html/sistemaweb/tmp/$tabla_temp.txt" );
	exec("cat /var/www/html/sistemaweb/tmp/$tabla_temp.txt >/var/www/html/sistemaweb/tmp/$archivo_csv.csv" );
	exec("rm -Rf /var/www/html/sistemaweb/tmp/$tabla_temp.txt");
	
	?>
	<script language="JavaScript1.3" type="text/javascript">
	window.open('../tmp/<?php echo $archivo_csv;?>.csv','miwin','width=10,height=35,scrollbars=yes');
	</script>
	<?php
}

//para la tabla de busqueda por fechas
$query = "SELECT 
		to_char(a.dt_fechamedicion,'dd-mm-yyyy'), 
		a.ch_tanque || '--' ||c.ch_nombrecombustible as tanque, 
		a.nu_medicion,
		a.ch_sucursal 
	FROM 
		comb_ta_mediciondiaria a, 
		comb_ta_tanques b, 
		comb_ta_combustibles c 
	WHERE 
		a.dt_fechamedicion>='$fechad' 
		AND a.dt_fechamedicion <='$fechaa' 
		AND a.ch_tanque=b.ch_tanque 
		AND b.ch_codigocombustible=c.ch_codigocombustible
		AND a.ch_sucursal=trim('$cod_almacen') 
		AND a.ch_sucursal=b.ch_sucursal ";
//echo $query;
$rs2 = pg_exec($query);

$q1 = $query;
session_register("q1");
pg_close();

?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->

var miPopup 
function abririPopup(){
	miPopup = window.open("prueba.php","miwin","width=500,height=400,scrollbars=yes") 
	miPopup.focus() 
	}

</script>
</head>

<body>
<form action='cmb_medvarilla-edit.php' method='post' name="form2">
<table width="731" border="1" cellpadding="0" cellspacing="0">
	<tr> 
      		<td colspan="2"><a href="cmb_medvarilla-reporte.php?action=exportar&titulo=MEDIDA DIARIA DE VARILLA" >Exportar a Excel</a></td>
      		<td><div align="center"><strong> 
          	<UL>MEDIDA DIARIA DE VARILLA</UL>
          	<br>
          	</strong></div></td>
      		<td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
    	</tr>
    	<tr> 
      		<td width="96" height="7"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><strong>FECHA</strong></font></div></td>
      		<td width="204" height="7"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><strong>TANQUE</strong></font></div></td>
      		<td width="186" height="7"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><strong>MEDIDA</strong></font></div></td>
      		<td width="235" height="7"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><strong>SUCURSAL</strong></font></div></td>
    	</tr>
    	<?php for($i=0;$i<pg_numrows($rs2);$i++) {
    		$B = pg_fetch_row($rs2,$i);	
		?>
    		<tr> 
      		<?php
	 	print '<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$B[0].'</font></div></td>
      			<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$B[1].'</font></div></td>
      			<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.$B[2].'</font></div></td>
	  		<td width="232"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$B[3].'</font></div></td>
    			</tr> ';	
	}
	?>
</table>
</form>
<br>
</body>
</html>  
<?php include("../close_connect.php"); ?>
