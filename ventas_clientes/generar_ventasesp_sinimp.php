<?php

include("../valida_sess.php");
include("config.php");
include("inc_top.php");
include("../functions.php");
//include("store_procedures.php");
include("funciones.php");

if($action=="reporte"){
	pg_exec(" truncate tmp_ventas_especiales");
	switch ($ordx){
		case "total":
			$cho1="checked";
		break;
		case "almacen":
			$cho2="checked";
		break;
		case "producto":
			$cho3="checked";
		break;
	}
	
	switch ($cond){
		case "linea":
			$chc1="checked";
		break;
		case "tipo":
			$chc2="checked";
		break;
		case "codigo":
			$chc3="checked";
		break;
	}
	
	switch($detres){
		case "resumido": 
			$chdr1 = "checked";
			$com_art1 ="<!--";
			$com_art2 = "-->";
		break;
		case "detallado":
			$chdr2 = "checked";
			
		break;
	}
	
	if($conigv=="conigv") {
			$campo_valor = "total";
			$ch_conigv = "checked";
	}else{  $campo_valor = "importe"; }

	switch($cond){
		case "linea":
			$AR = $linea;
		break;
		case "tipo":
			$AR = $tipo;
		break;
		case "codigo":
			$AR = $codigo;
		break;
	}
	
	$fechad = $diad."/".$mesd."/".$anod;
	$fechaa = $diaa."/".$mesa."/".$anoa;
}
if($xdia=="xdia"){$chkxdia="checked";}

if($ordx!="almacen"){
	$cod_almacen="";
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">

function filtrarLista(condicion) {
    control.location.href="control.php?rqst=REPORTES.ESPECIALES&action=" + condicion;
}

function ocultarArea(nombre) {
    var object = document.getElementById(nombre);
    
    if (object) {
	object.style.visibility="hidden";
    }
}

function mostrarArea(nombre) {
    var object = document.getElementById(nombre);

    if (object) {
	object.style.visibility="visible";
    }
}

</script>
<title>Ventas Especiales Sin Impuesto</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="generar_ventasesp_sinimp.php?action=reporte">
  <div align="center"><strong>Ventas Especiales Sin Impuesto</strong><br>
  </div>
  <table width="854" border="1" bordercolor="#cccc99">
    <tr> 
      <td width="196" height="41"> <div align="right">Desde: 
          <input type="text" name="diad" size="4" maxlength="2" onKeyUp="javascript:( validarNumeroEntero(this) , validarDia(diad) )" value='<?php echo $diad;?>'>
          / 
          <input type="text" name="mesd" size="4" maxlength="2" onKeyUp="javascript:validarNumeroEntero(this) , validarMes(this)" value='<?php echo $mesd;?>'>
          / 
          <input type="text" name="anod" size="6" maxlength="4" onKeyUp="javascript:validarNumeroEntero(this) , validarYear(this)" value='<?php echo $anod;?>'>
          <strong><br>
          dia / mes / a&ntilde;o</strong> </div></td>
      <td colspan="3">Hasta: 
        <input type="text" name="diaa" size="4" maxlength="2" onKeyUp="javascript:validarNumeroEntero(this) , validarDia(this)" value='<?php echo $diaa;?>'>
        / 
        <input type="text" name="mesa" size="4" maxlength="2" onKeyUp="javascript:validarNumeroEntero(this) , validarMes(this)" value='<?php echo $mesa;?>'>
        / 
        <input type="text" name="anoa" size="6" maxlength="4" onKeyUp="javascript:validarNumeroEntero(this), validarYear(this)" value='<?php echo $anoa;?>'> 
        <br> <strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dia 
        / mes / a&ntilde;o</strong> </td>
      <td width="373">&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right">Detallado por d&iacute;a :</div></td>
      <td colspan="3"><input type="checkbox" name="xdia" value="xdia" <?php echo $chkxdia;?>></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right">Totales con IGV :</div></td>
      <td colspan="3"><input type="checkbox" name="conigv" value="conigv" <?php echo $ch_conigv;?>></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right"> Forma del Reporte :</div></td>
      <td colspan="3"> <input type="radio" name="detres" value="resumido" <?php echo $chdr1;?>>
        Resumido 
        <input type="radio" name="detres" value="detallado" <?php echo $chdr2;?>>
        Detallado </td>
      <td>&nbsp;</td>
    </tr>
    <tr > 
      <td><div align="right"><strong>Ordenado por:</strong></div></td>
      <td width="71" bordercolor="#006666"><div align="left"> 
          <input type="radio" name="ordx" value="total" onClick="javascript:ocultarArea('almacen');" <?php echo $cho1;?> >
          Total </div></td>
      <td width="94" bordercolor="#006666"><input type="radio" name="ordx" value="almacen" onClick="javascript:mostrarArea('almacen');" <?php echo $cho2;?>>
        Almacen</td>
      <td width="86" bordercolor="#006666"><input type="radio" name="ordx" value="producto" onClick="javascript:ocultarArea('almacen');" <?php echo $cho3;?>>
        Producto</td>
      <td><div id="almacen" style="visibility:hidden"><input type="hidden" name="cond_last_select" value="algo">
        <select name="cod_almacen">
		<?php $rs = combo("almacenes"); 
		for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		print "<option value='$A[0]'>$A[1]</option>";
		}
		?>
        </select></div></td>
    </tr>
    <tr> 
      <td><div align="right"><strong>Condici&oacute;n:</strong></div></td>
      <td bordercolor="#006666"><input type="radio" name="cond" value="linea" onClick="javascript: filtrarLista('LINEA');" <?php echo $chc1;?>>
        Linea </td>
      <td bordercolor="#006666"><input type="radio" name="cond" value="tipo"  onClick="javascript: filtrarLista('TIPO');" <?php echo $chc2;?>>
        Tipo</td>
      <td bordercolor="#006666"><input type="radio" name="cond" value="codigo" onClick="javascript: filtrarLista('ARTICULO');" <?php echo $chc3;?>>
        C&oacute;digo </td>
      <td><input type="submit" name="Submit" value="Aceptar"></td>
    </tr>
    <tr> 
      <td colspan="5">
        <div id="space" align="center">&nbsp;</div>
      </td>
    </tr>
  </table>
  <!--AQUI EMPIEZA LA TABLA--><?php if($action=="reporte"){?>
  Del <?php echo htmlentities($fechad); ?> hasta echo <?php echo htmlentities($fechaa); ?> <?php echo $cod_almacen;?>
  <br>
  <table  border="1" bordercolor="#cccc99">
    <tr><?php $rs_alma = combo("almacenes");?>
      <td width="96" height="17" bordercolor="#006666"><strong><font size="-4" face="Arial, Helvetica, sans-serif">Descripci&oacute;n:</font></strong></td>
	 <?php  for($i=0;$i<pg_numrows($rs_alma);$i++){
	 $A = pg_fetch_array($rs_alma,$i);
	 $almacen = $A[1];
	print '
      <td colspan="2" bordercolor="#006666" ><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$almacen.'</font></div></td>
      ';} ?>
      <td colspan="2" bordercolor="#006666"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL:</font></td>
    </tr>
    <tr bordercolor="#006666"> 
      <td height="17"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <?php
	  for($i=0;$i<pg_numrows($rs_alma);$i++){
	  print '
	 
      <td width="50"> <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">cantidad</font></div></td>
      <td width="71"> <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$campo_valor.'</font></div></td>
      ';} ?>
      <td width="38" bordercolor="#006666"><font size="-4" face="Arial, Helvetica, sans-serif">cantidad</font></td>
      <td width="33" bordercolor="#006666"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $campo_valor;?></font></div></td>
    </tr>
    <?php $rs_linea = lineas_venesp($cod_almacen,$ordx,$cond,$AR,$fechad,$fechaa,$xdia);  
	for($j=0;$j<pg_numrows($rs_linea);$j++){   
	 $L = pg_fetch_array($rs_linea,$j);
	 $cod_linea = $L[0];
	 $linea = $L[1];
	 $rs_art = articulos_venesp($cod_linea,$cod_almacen,$ordx,$xdia);
	 ?>
    <tr bordercolor="#006666"> 
      <td height="15"><font color="#0033CC" size="-4" face="Arial, Helvetica, sans-serif"><strong>Linea 
        :&nbsp;<?php echo $cod_linea.": ".$linea;?></strong></font></td>
      <td colspan="2" bordercolor="#006666"> <div align="right"></div></td>
      <td colspan="2" bordercolor="#006666"></td>
    </tr>
   
    <?php // BEGIN ARTICULO
	for($n=0;$n<pg_numrows($rs_art);$n++){
	 $ART = pg_fetch_array($rs_art,$n);
	 $articulo = $ART[1];
	 $cod_articulo = $ART[0];
	 if($xdia=="xdia"){ if($ordx==""){$art_fecha = $ART["dt_fac_fecha"];}else{$art_fecha = $ART["dt_fac_fecha"];} }
	 $TTL_cant = 0;
	 $TTL_val  = 0;
		
	?> <?php echo $com_art1;?>
    <tr bordercolor="#006666"> 
      <td height="21"><font size="-4" face="Arial, Helvetica, sans-serif">articulo: 
        <?php echo $articulo;?><?php echo "<br>".$art_fecha;?></font></td>
    
      <?php // BEGIN ARTICULO CAN IMP
	  $_total_cant = 0 ;
		  $_total_val = 0;
	   for($k=0;$k<pg_numrows($rs_alma);$k++){
	$AL = pg_fetch_array($rs_alma,$k);
	$alma_cod = $AL[0];
	$alma_des = $AL[1];
	$rs_val = valor_venesp($cod_linea,$cod_articulo,$alma_cod,$campo_valor,$xdia,$art_fecha);
	if(pg_numrows($rs_val) > 0){$VAL = pg_fetch_array($rs_val,0);
	$cant = $VAL[0];
	$val = $VAL[1]; 
	}else{
	$cant = 0;
	$val = 0;
	}
	$_total_cant = $_total_cant + $cant;
	$_total_val = $_total_val + $val;
	$TL_cant[$k]=$TL_cant[$k] + $cant;
	$TL_val[$k]=$TL_val[$k] + $val;
	$TTL_cant = $TL_cant[$k] + $TTL_cant;
	$TTL_val  = $TL_val[$k]  + $TTL_val;
	?> 
      <td> <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $cant;?> 
          </font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $val;?></font></div></td>
       <?php } // FIN ARTICULO CAN IMP ?>
  
      <td bordercolor="#006666"><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $_total_cant;?></font></div></td>
      <td bordercolor="#006666"><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $_total_val;?></font></div></td>
    </tr>
     <?php //ESTE EXTRA PERMITE CONOCER LA FECHA SIGUIENTE A LA QUE ESTA SALIENDO  
	 if(pg_numrows($rs_art)!=$n+1){
	 	$X=pg_fetch_array($rs_art,$n+1);
			if($ordx==""){$art_sigfecha = $X[2];}else{$art_sigfecha = $X[3];}
		} 
	 	else{$art_sigfecha = "qaz";
			$art_sigfecha = "qaz";
		}
	 ?> 
    <?php echo $com_art2;?>
	<?php if($xdia=="xdia" && $art_fecha != $art_fecha2 && $art_fecha!=$art_sigfecha){ $art_fecha2=$art_fecha; ?>	 
    <tr bordercolor="#006666"> 
      <td height="17" bgcolor="#cccccc"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;Total 
        Fecha <?php echo $art_fecha;?>: </font></td>
      <!-- BEGIN FILA2 <?php
	  for($i=0;$i<pg_numrows($rs_alma);$i++){
	  print '
	  ?>
	  -->
      <td width="50" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
      <td width="71" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
      <!-- END FILA2 <?php ';
	  
	  }
	  ?>-->
      <td width="38" bordercolor="#006666" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
      <td width="33" bordercolor="#006666" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    </tr>
	<?php } //aqui se incluye las fechas ?>
	<?php } //if del detalle de fechas?>
	<!-- Fin del detalle por fecha-->
	<!-- -->
	<!-- experimental-->
    <tr bordercolor="#006666"> 
      <td height="17" bgcolor="#cccccc"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;Total 
        Linea <?php echo $cod_linea;?>: </font></td>
      <!-- BEGIN FILA2 <?php
	  for($i=0;$i<pg_numrows($rs_alma);$i++){
	  print '
	  ?>
	  -->
      <td width="50" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.$TL_cant[$i].'</font></div></td>
      <td width="71" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.$TL_val[$i].'</font></div></td>
      <!-- END FILA2 <?php ';
	    $TG_cant[$i] = $TG_cant[$i] + $TL_cant[$i];
		$TG_val[$i] = $TG_val[$i]   + $TL_val[$i];
		$TL_cant[$i]=0;
		$TL_val[$i]=0;
	  }
	  ?>-->
      <td width="38" bordercolor="#006666" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $TTL_cant;?></font></div></td>
      <td width="33" bordercolor="#006666" bgcolor="#cccccc"> 
        <div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $TTL_val;?></font></div></td>
    </tr>
    <!-- experimental-->
    <!-- <?php 
	$TTG_cant = $TTG_cant + $TTL_cant;
	$TTG_val  = $TTG_val + $TTL_val;	
	} ?>-->
    <!-- FIN DE LINEAS-->
    <td bordercolor="#006666" bgcolor="#CCCCCC"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL 
      GENERAL: </font></td>
    <!--BEGIN TOTAL GENERAL CAN IMP--><!--
    <?php	for($p=0;$p<pg_numrows($rs_alma);$p++){ print '?> -->
    <td bordercolor="#006666" bgcolor="#CCCCCC"> <div align="right"><strong><em><font size="-4" face="Arial, Helvetica, sans-serif">'.$TG_cant[$p].' 
        </font></em></strong></div></td>
    <td bordercolor="#006666" bgcolor="#CCCCCC"><div align="right"><strong><em><font size="-4" face="Arial, Helvetica, sans-serif">'.$TG_val[$p].'</font></em></strong></div></td>
     <!-- <?php  ';}?>-->
    <!-- END TOTAL GENERAL CAN IMP--> 
    <td bordercolor="#006666" bgcolor="#CCCCCC"><div align="right"><strong><em><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $TTG_cant;?></font></em></strong></div></td>
    <td bordercolor="#006666" bgcolor="#CCCCCC"><div align="right"><strong><em><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $TTG_val;?></font></em></strong></div></td>
  </table>
  <!--FIN DE LA TABLA -->
  <?php } ?>
  <br>
  <?php
  
  ?>
</form>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.ESPECIALES" width="10" height="10" frameborder="1"></iframe>
</body>
</html>
