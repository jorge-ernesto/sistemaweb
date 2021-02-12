<?php
include("../config.php");
include("../functions.php");
include("../valida_sess.php");

$comb = pg_exec("SELECT
			comb.ch_nombrecombustible 
		FROM
			comb_ta_combustibles comb
, comb_ta_tanques tan where tan.ch_codigocombustible=comb.ch_codigocombustible 
and tan.ch_tanque='$cod_tanque' and tan.ch_sucursal=trim('$cod_almacen')");
$C = pg_fetch_row($comb,0);
$comb=$C[0];

$REP = sobrantesyfaltantesReporte($cod_almacen,$cod_tanque,$fechad,$fechaa);
echo $almacen."<br>";
echo $cod_tanque."<br>";
echo $fechad."<br>";
echo $fechaa."<br>";	

if($action=="exportar"){
	$titulo = "Reporte de Sobrantes y Faltantes del $fechad al $fechaa \n SUCURSAL: $cod_almacen -- Tanque: $cod_tanque -- Combustible: $comb";
	$cabecera = " ,,,,,TRANSFERENCIAS,,,,DIFERENCIA  \n FECHA,SALDO,COMPRA,MEDICION,VENTA,INGRESO,SALIDA,PARTE,VARILLA,DIARIA,ACUMULADA";
	
	pg_exec("CREATE TABLE  $user"._."sobfal  (FECHA date,SALDO varchar(50),COMPRA varchar(50)
		,MEDICION varchar(50),VENTA varchar(50),INGRESO varchar(50),SALIDA varchar(50)
		,PARTE varchar(50),VARILLA varchar(50),DIARIA varchar(50),ACUMULADA varchar(50)  ) ");
	
	
	for($i=0;$i<count($REP);$i++){
		$q = 'INSERT INTO '.$user.'_sobfal values('."'".''.$REP[$i][0].''." ' ".','.$REP[$i][1].','.$REP[$i][2].','.$REP[$i][3].','.$REP[$i][4].'
							,'.$REP[$i][5].','.$REP[$i][6].','.$REP[$i][7].','.$REP[$i][8].','.$REP[$i][9].','.$REP[$i][10].')';
		//echo $q;
		pg_exec($q);	
		
	}
	
	$q1 = "SELECT * FROM $user"._."sobfal";
	$url = sacarExcel($user,$cabecera,$q1,$titulo);
	
	//antes de mostrar el reporte borramos todo
	
	pg_exec("DROP TABLE $user"."_sobfal");
	
	?>
	<script language="JavaScript1.3" type="text/javascript">
	window.open('<?php echo $url;?>','miwin','width=10,height=35,scrollbars=yes');
	</script>
	<?php
	
}		

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
  <table width="767" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      	<td width="457">
	<button name="fm" value="" onClick="javascript:parent.location.href='../combustibles/cmb_sobyfaltantes-reporte.php?action=exportar&fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&titulo=<?php echo $titulo;?>&cod_tanque=<?php echo $cod_tanque;?>&cod_almacen=<?php echo $cod_almacen;?>';return false"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Excel</button>
	</td>
      	<td>
	<button name="fm2" value="" onClick="javascript:window.print();return false"><img src="/sistemaweb/images/icono_imprimir.gif" alt="left"/> Imprimir</button>
	</td>

    </tr>
  </table>
</form>
<div align="center"><font face="Arial, Helvetica, sans-serif">Reporte de Sobrantes 
  y Faltantes<font size="2"> <br>
  del: </font><font face="Arial, Helvetica, sans-serif"><font size="2"><?php echo $fechad; ?></font></font><font size="2"> 
  al <?php echo $fechaa;?></font></font><br>
  <div align="left"><font size="2" face="Arial, Helvetica, sans-serif">SUCURSAL: 
    <?php echo $cod_almacen;?> -- Tanque: <?php echo $cod_tanque;?> --- Combustible: <?php echo $comb;?></font> 
  </div>
</div>

<table width="771" border="1" cellpadding="0" cellspacing="0">
  <tr> 
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td colspan="2"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TRANSFERENCIAS</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td colspan="2"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DIFERENCIA</font></div></td>
  </tr>
  <tr> 
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">FECHA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">SALDO</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">COMPRA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">MEDICION</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VENTA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">INGRESO</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">SALIDA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PARTE</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VARILLA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DIARIA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">ACUMULADA</font></div></td>
  </tr>
  <!-- <?php for($i=0;$i<count($REP);$i++){ 
  		$TOT_SALDO 		= 	$TOT_SALDO + $REP[$i][1];
	$TOT_COMPRA 	= 	$TOT_COMPRA + $REP[$i][2];
	$TOT_MEDICION 	= 	$TOT_MEDICION + $REP[$i][3];
	$TOT_VENTA 		= 	$TOT_VENTA + $REP[$i][4];
	$TOT_INGRESO 	= 	$TOT_INGRESO + $REP[$i][5];
	$TOT_SALIDA 	= 	$TOT_SALIDA + $REP[$i][6];
	$TOT_PARTE 		= 	$TOT_PARTE + $REP[$i][7];
	$TOT_VARILLA 	= 	$TOT_VARILLA + $REP[$i][8];
	$TOT_DIARIA 	= 	$TOT_DIARIA + $REP[$i][9];
	$TOT_ACUMULADA 	= 	$TOT_ACUMULADA + $REP[$i][10];
  
  print '?>-->
  <tr> 
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][0].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][1].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][2].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][3].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][4].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][5].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][6].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][7].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][8].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][9].'</font></div></td>
    <td align="right"><div><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][10].'</font></div></td>
  </tr>
  <!-- <?php '; } ?>-->
  <tr> 
    <td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        TOTALES</font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_SALDO;?></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_COMPRA;?></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_MEDICION;?></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial,0 Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_VENTA;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_INGRESO;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_SALIDA;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_PARTE;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_VARILLA;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_DIARIA;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_ACUMULADA;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
  </tr>
</table>
</body>
</html>  
