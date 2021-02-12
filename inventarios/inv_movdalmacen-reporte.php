<?php
//session_start();
include("../config.php");
//include("../combustibles/inc_top.php");
include("../functions.php");
include("../valida_sess.php");
if($cod_almacen==""){$cod_almacen=$almacen;}
		
$q1="select m.mov_numero AS FORMULARIO,m.mov_fecha AS FECHA,m.mov_numero AS No_OC
, m.mov_tipdocuref || '-' || m.mov_docurefe AS No_DOCREF,m.mov_almaorigen AS ORIGEN ,m.mov_almadestino AS DESTINO, m.mov_almacen AS ALMACEN
,m.art_codigo AS COD_ARTICULO, m.mov_cantidad AS CANTIDAD,m.mov_costounitario AS COSTO_UNI
,a.art_descripcion AS DESCRIPCION from inv_movialma m,int_articulos a
 where m.tran_codigo='$fm' and m.art_codigo=a.art_codigo 
 and m.mov_fecha between '" . $_REQUEST['fechad'] . "' and '" . $_REQUEST['fechaa'] . "' ";
$rs1 = pg_exec($q1);

if($action=="exportar"){
	  
	$titulo="MOVIMIENTO DE ALMACEN DEL: $fechad AL: $fechaa \n SUCURSAL: $cod_almacen";
	$cabecera = "FORMULARIO , FECHA , No OC , No DOC REF , ORIGEN , DESTINO, ALMACEN , COD. ARTICULO , CANTIDAD , COSTO UNIT , DESCRIPCION";
	
	$url = sacarExcel($user,$cabecera,$q1,$titulo);
		 //echo $q1;
	//echo $B[0];
	
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
      <td width="457"><a href="inv_movdalmacen-reporte.php?action=exportar&fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&fm=<?php echo $fm;?>" >Exportar 
        a Excel</a></td>
      <td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
    </tr>
  </table>
</form>
<div align="center"><font size="2" face="Arial, Helvetica, sans-serif">MOVIMIENTO 
  DE ALMACEN DEL:<?php echo $fechad; ?> al <?php echo $fechaa;?></font><br>
  <div align="left"><font size="2" face="Arial, Helvetica, sans-serif">SUCURSAL: <?php echo $cod_almacen;?>
    </font></div>
</div>
<table width="769" height="36" border="1" cellpadding="0" cellspacing="0">
  <tr> 
    <th><font size="-4" face="Arial, Helvetica, sans-serif">FORMULARIO</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">FECHA</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">No O/C</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">No DOC. REF</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">ORI</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">DEST</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">ALM</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">COD ART</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">CANTIDAD</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">COSTO UNIT.</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">DESCRIPCION ART</font></th>
  </tr>
  <!-- <?php for($i=0;$i<pg_numrows($rs1);$i++){
  $A = pg_fetch_array($rs1,$i); 
  print '
  ?> -->
  <tr> 
    <th width="65" height="21"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[0].'</font></th>
    <th width="75"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></th>
    <th width="46"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></th>
    <th width="74"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[3].'</font></th>
    <th width="47"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[4].'</font></th>
    <th width="54"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></th>
    <th width="54"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></th>
    <th width="70"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></th>
    <th width="71"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[8].'</font></th>
    <th width="75"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[9].'</font></th>
    <th width="114"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[10].'</font></th>
  </tr>
 <tr></tr>
<!-- <?php ';} ?>-->
</table>
<br>
<p>&nbsp;</p>
</body>
</html>  